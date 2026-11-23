<?php
/**
 * What the WPZOOM AI Connect connection may do on this site.
 *
 * The connection is an application password named "WPZOOM AI Connect", approved by a user in WordPress and kept by
 * the WPZOOM connector (mcp.wpzoom.com). Requests made with it get a reduced copy of that user's permissions, so
 * even an administrator's connection only works on content, media, menus, design settings and the site's abilities.
 * Whatever happens on the connector's side, through this password it cannot:
 *   - install, update, activate, deactivate or delete plugins and themes, or switch themes;
 *   - create, edit, promote or delete users (itself included), or add and remove application passwords;
 *   - change site options (the Settings screens), languages, privacy tools or multisite settings;
 *   - edit theme or plugin files, or save unfiltered HTML and uploads (content goes through the same filters as
 *     an editor's). Additional CSS is the one exception: allowed unless the site owner switches it off on the
 *     AI Connect screen, and still subject to WordPress's own edit_css rules;
 *   - reach REST routes outside WordPress core (wp/v2), the Abilities API and oEmbed, or use XML-RPC;
 *   - read or change a custom post type beyond what the site owner chose on the AI Connect screen
 *     (WPZOOM_AI_Post_Types: closed unless they open it, except WPZOOM's own types).
 * The user's own sessions and their other application passwords are not affected.
 * Site owners can adjust the route rule with the `wpzoom_ai_connect_allowed_route` filter.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Scope {

	/** The application password name the connector asks WordPress to create. */
	const APP_NAME = 'WPZOOM AI Connect';

	/** Capabilities the connection never has. */
	const DENIED_CAPS = array(
		'install_plugins',
		'delete_plugins',
		'edit_plugins',
		'resume_plugins',
		'install_themes',
		'delete_themes',
		'edit_themes',
		'switch_themes',
		'resume_themes',
		'update_core',
		'install_languages',
		'update_languages',
		'create_users',
		'add_users',
		'edit_users',
		'delete_users',
		'promote_users',
		'remove_users',
		'edit_files',
		'unfiltered_html',
		'unfiltered_upload',
		'import',
		'manage_privacy_options',
		'erase_others_personal_data',
		'export_others_personal_data',
		'view_site_health_checks',
		'setup_network',
		'manage_network',
		'manage_sites',
		'manage_network_users',
		'manage_network_plugins',
		'manage_network_themes',
		'manage_network_options',
		'upgrade_network',
		'create_sites',
		'delete_sites',
		'delete_site',
	);

	/** Per-user meta capabilities the connection never has, even for the connected user's own account. */
	const DENIED_USER_CAPS = array( 'edit_user', 'delete_user', 'promote_user', 'remove_user' );

	/** Capabilities the connection has for reading only (GET and HEAD): the plugin list and the Settings values. */
	const READ_ONLY_CAPS = array( 'activate_plugins', 'manage_options' );

	/**
	 * Abilities the connection may run although they check manage_options to make a change: clearing the page cache,
	 * and the WPZOOM → Theme Options panel (the plugin's ability and the themes' own). Only while one of them runs,
	 * the connection keeps manage_options for writes; Settings and every other admin write stay read-only.
	 */
	const OPTIONS_WRITE_ABILITIES = array( 'wpzoom/clear-cache', 'wpzoom/update-theme-options' );

	/** Whether the current request was authenticated with the connection (remembered once it is known). */
	private static $active = false;

	/** Whether the current request runs one of OPTIONS_WRITE_ABILITIES (set in pre_dispatch). */
	private static $options_ability = false;

	/** When an AI last used the connection (epoch seconds), for the AI Connect screen's "waiting for your AI" state. */
	const LAST_AI_CALL_OPTION = 'wpzoom_ai_connect_last_ai_call';

	/** On how many different days an AI used the connection, and since when; the review notice waits for real use. */
	const AI_DAYS_OPTION = 'wpzoom_ai_connect_ai_days';

	public static function init() {
		add_filter( 'map_meta_cap', array( __CLASS__, 'map_meta_cap' ), 10, 4 );
		add_filter( 'rest_pre_dispatch', array( __CLASS__, 'pre_dispatch' ), 10, 3 );
		// Other plugins' abilities on store data (WooCommerce orders, products) follow the AI Connect screen's choice.
		add_filter( 'wp_register_ability_args', array( 'WPZOOM_AI_Post_Types', 'gate_ability_args' ), 20, 2 );
		add_action( 'wp_authenticate_application_password_errors', array( __CLASS__, 'refuse_xmlrpc' ), 10, 3 );
	}

	/** Whether the current request is authenticated with the WPZOOM AI Connect application password. */
	public static function is_connector_request(): bool {
		if ( self::$active ) {
			return true;
		}
		if ( ! function_exists( 'rest_get_authenticated_app_password' ) || ! class_exists( 'WP_Application_Passwords' ) ) {
			return false;
		}
		$uuid    = rest_get_authenticated_app_password();
		$user_id = get_current_user_id();
		if ( ! $uuid || ! $user_id ) {
			return false;
		}
		$password     = WP_Application_Passwords::get_user_application_password( $user_id, $uuid );
		self::$active = is_array( $password ) && self::is_connector_password( $password );
		return self::$active;
	}

	/** Whether an application password record is the connection. */
	private static function is_connector_password( array $item ): bool {
		return 0 === strcasecmp( (string) ( $item['name'] ?? '' ), self::APP_NAME );
	}

	/** Whether the current request only reads (the override `_method` only turns a POST into another write). */
	private static function is_read_request(): bool {
		$method = isset( $_SERVER['REQUEST_METHOD'] ) ? strtoupper( sanitize_key( wp_unslash( $_SERVER['REQUEST_METHOD'] ) ) ) : 'GET';
		return in_array( $method, array( 'GET', 'HEAD', 'OPTIONS' ), true );
	}

	/**
	 * The permission ceiling. Adding `do_not_allow` to the required primitives denies the capability to everyone,
	 * multisite super admins included (they skip the user_has_cap filter).
	 *
	 * @param string[] $caps    Primitive capabilities required.
	 * @param string   $cap     Capability asked for.
	 * @param int      $user_id User the check is about.
	 * @param array    $args    Context (object ids).
	 * @return string[]
	 */
	public static function map_meta_cap( $caps, $cap, $user_id, $args ) {
		if ( (int) $user_id !== get_current_user_id() || ! self::is_connector_request() ) {
			return $caps;
		}
		$caps = (array) $caps;
		if ( 'edit_css' === $cap ) {
			// Additional CSS: WordPress maps edit_css to the unfiltered_html primitive, which the connection never has
			// for posts. The site owner decides for CSS (AI Connect screen, on by default); core's own rules still
			// apply (DISALLOW_UNFILTERED_HTML, super admins only on multisite) because the mapped caps are kept.
			if ( ! WPZOOM_AI_CSS::allowed_for_connection() && ! in_array( 'do_not_allow', $caps, true ) ) {
				$caps[] = 'do_not_allow';
			}
			return $caps;
		}
		$denied = in_array( $cap, self::DENIED_CAPS, true ) || in_array( $cap, self::DENIED_USER_CAPS, true ) || array_intersect( $caps, self::DENIED_CAPS );
		if ( ! $denied && ! self::is_read_request() ) {
			$read_only = self::$options_ability ? array_diff( self::READ_ONLY_CAPS, array( 'manage_options' ) ) : self::READ_ONLY_CAPS;
			$denied    = in_array( $cap, $read_only, true ) || array_intersect( $caps, $read_only );
		}
		if ( ! $denied ) {
			$denied = WPZOOM_AI_Post_Types::denies_cap( $cap, (array) $args, self::is_read_request() );
		}
		if ( $denied && ! in_array( 'do_not_allow', $caps, true ) ) {
			$caps[] = 'do_not_allow';
		}
		return $caps;
	}

	/**
	 * Whether the connection may call a REST route: WordPress core's own API (wp/v2), the Abilities API, oEmbed and
	 * the index. Inside wp/v2, plugins, themes, users and settings are read-only, and application passwords and the
	 * block directory are off limits. A custom post type's routes follow the owner's choice for that type, under its
	 * own namespace too. Everything else (other plugins' namespaces, batch, site health, the block editor's helpers)
	 * is not reachable through the connection.
	 */
	public static function route_allowed( string $route, string $method ): bool {
		$route = '/' . ltrim( $route, '/' );
		$read  = in_array( strtoupper( $method ), array( 'GET', 'HEAD', 'OPTIONS' ), true );
		$mode  = WPZOOM_AI_Post_Types::mode_for_route( $route );
		if ( in_array( $route, array( '/', '/wp/v2', '/wp-abilities/v1', '/oembed/1.0' ), true ) ) {
			$allowed = true;
		} elseif ( 0 === strpos( $route, '/wp-abilities/v1/' ) ) {
			$allowed = true; // Each ability checks its own permissions, under the ceiling above.
		} elseif ( 0 === strpos( $route, '/oembed/1.0/' ) ) {
			$allowed = $read;
		} elseif ( 0 === strpos( $route, '/wp/v2/' ) ) {
			$rest = substr( $route, 7 );
			$head = strtok( $rest, '/' );
			if ( false !== strpos( $rest, 'application-passwords' ) || 'block-directory' === $head ) {
				$allowed = false;
			} elseif ( in_array( $head, array( 'plugins', 'themes', 'users', 'settings' ), true ) ) {
				$allowed = $read;
			} else {
				$allowed = null === $mode || WPZOOM_AI_Post_Types::mode_allows( $mode, $read );
			}
		} else {
			$allowed = null !== $mode && WPZOOM_AI_Post_Types::mode_allows( $mode, $read );
		}
		/**
		 * Filters whether the WPZOOM AI Connect connection may call a REST route.
		 *
		 * @param bool   $allowed Whether the route is allowed by the rules above.
		 * @param string $route   The route, e.g. "/wp/v2/plugins".
		 * @param string $method  The HTTP method.
		 */
		return (bool) apply_filters( 'wpzoom_ai_connect_allowed_route', $allowed, $route, $method );
	}

	/**
	 * Notes that an AI used the connection. Pairing only reads the index and the connected user (to check the new
	 * password), so those two do not count. Written at most every five minutes.
	 */
	private static function note_ai_call( string $route ): void {
		$route = '/' . trim( $route, '/' );
		if ( '/' === $route || '/wp/v2/users/me' === $route ) {
			return;
		}
		if ( time() - (int) get_option( self::LAST_AI_CALL_OPTION, 0 ) > 300 ) {
			update_option( self::LAST_AI_CALL_OPTION, time(), false );
			self::note_ai_day();
		}
	}

	/** Counts the day of an AI call once (site time zone). Runs only when the last-call time is written. */
	private static function note_ai_day(): void {
		$today = wp_date( 'Y-m-d' );
		$days  = self::ai_days();
		if ( $days['last'] === $today ) {
			return;
		}
		$days['last']  = $today;
		$days['count'] = $days['count'] + 1;
		update_option( self::AI_DAYS_OPTION, $days, false );
	}

	/**
	 * AI use so far: the first day counted (epoch seconds), the last day counted (Y-m-d) and how many days.
	 * Counting started with the review notice, so earlier use is not in it.
	 *
	 * @return array{first:int, last:string, count:int}
	 */
	public static function ai_days(): array {
		$days = get_option( self::AI_DAYS_OPTION );
		if ( ! is_array( $days ) ) {
			return array( 'first' => time(), 'last' => '', 'count' => 0 );
		}
		return array(
			'first' => (int) ( $days['first'] ?? time() ),
			'last'  => (string) ( $days['last'] ?? '' ),
			'count' => (int) ( $days['count'] ?? 0 ),
		);
	}

	/**
	 * When an AI last used the connection, 0 if never. Connections made before 0.12.2 count as used when their
	 * password was used on a later day than it was created (WordPress records that at most once a day).
	 */
	public static function last_ai_call( array $connections ): int {
		$last = (int) get_option( self::LAST_AI_CALL_OPTION, 0 );
		if ( $last ) {
			return $last;
		}
		foreach ( $connections as $c ) {
			if ( $c['last_used'] && $c['last_used'] - $c['created'] > DAY_IN_SECONDS ) {
				return (int) $c['last_used'];
			}
		}
		return 0;
	}

	/** Refuses routes outside the connection's scope before any handler runs. */
	public static function pre_dispatch( $result, $server, $request ) {
		if ( null !== $result || ! ( $request instanceof WP_REST_Request ) || ! self::is_connector_request() ) {
			return $result;
		}
		if ( self::route_allowed( $request->get_route(), $request->get_method() ) ) {
			self::note_ai_call( $request->get_route() );
			self::$options_ability = (bool) preg_match( '#^/wp-abilities/v1/abilities/(.+)/run$#', '/' . ltrim( $request->get_route(), '/' ), $m )
				&& in_array( $m[1], self::OPTIONS_WRITE_ABILITIES, true );
			return $result;
		}
		if ( null !== WPZOOM_AI_Post_Types::mode_for_route( '/' . ltrim( $request->get_route(), '/' ) ) ) {
			return new WP_Error(
				'wpzoom_ai_connect_post_type_closed',
				sprintf(
					/* translators: 1: HTTP method, 2: REST route */
					__( 'The site owner has not given the WPZOOM AI Connect connection this access to this post type (%1$s %2$s). They can change it on the AI Connect screen in WordPress.', 'wpzoom-ai-connect' ),
					$request->get_method(),
					$request->get_route()
				),
				array( 'status' => 403 )
			);
		}
		return new WP_Error(
			'wpzoom_ai_connect_out_of_scope',
			sprintf(
				/* translators: 1: HTTP method, 2: REST route */
				__( 'Not available through the WPZOOM AI Connect connection (%1$s %2$s): it is limited to content, media, menus, design settings and the site\'s abilities. Ask the site owner to do this in the WordPress dashboard.', 'wpzoom-ai-connect' ),
				$request->get_method(),
				$request->get_route()
			),
			array( 'status' => 403 )
		);
	}

	/** The connection works through the REST API only: XML-RPC refuses it. */
	public static function refuse_xmlrpc( $error, $user, $item ) {
		if ( defined( 'XMLRPC_REQUEST' ) && XMLRPC_REQUEST && $error instanceof WP_Error && is_array( $item ) && self::is_connector_password( $item ) ) {
			$error->add( 'wpzoom_ai_connect_rest_only', __( 'The WPZOOM AI Connect connection works through the REST API only.', 'wpzoom-ai-connect' ) );
		}
	}
}
