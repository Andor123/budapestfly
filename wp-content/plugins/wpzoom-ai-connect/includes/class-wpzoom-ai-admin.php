<?php
/**
 * AI Connect → the welcome screen: pick your AI, connect this site in one click, learn what to say.
 * The plugin has no settings of its own; this page is onboarding plus status, written for site owners,
 * in tabs: Get started, What it can do, Site checkup, Settings and Developers.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Admin {

	const CONNECTOR_ORIGIN = 'https://mcp.wpzoom.com';
	const CONNECTOR_URL    = 'https://mcp.wpzoom.com/mcp';
	/** WPZOOM in Claude's connector directory and in ChatGPT's plugin directory: the way to add it, with no address to paste. */
	const CLAUDE_DIRECTORY_URL = 'https://claude.ai/directory/wpzoom';
	const CHATGPT_PLUGIN_URL = 'https://chatgpt.com/plugins/plugin_asdk_app_6aedb19b4eb081919d3c14e622ad8e01';
	/** The setup video (also on the readme and wpzoom.com/ai-connect/); change the length with it. */
	const VIDEO_URL    = 'https://www.youtube.com/watch?v=sj3ZhG-rFlI';
	const VIDEO_LENGTH = '1:25';
	/** The application-password name the connector creates; how we recognise a connection. */
	const APP_NAME = 'WPZOOM AI Connect';
	const PAGE     = 'wpzoom-ai-connect';
	const TRANSIENT_ACTIVATED = 'wpzoom_ai_connect_activated';

	/** WPZOOM AI mark, box-free, one fill: WordPress recolours it for the admin menu. */
	const MENU_ICON = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024"><path fill="black" d="M53.8 590.8 L192 1024 L240.4 1024 L376.3 713 L511.5 1024 L559.9 1024 L695.8 590.8 L749.6 590.8 L749.6 513.3 L534.5 513.3 L534.5 590.8 L603.6 590.8 L526.8 856.6 L401.7 562.4 L355.6 562.4 L230.4 856.6 L148.2 590.8 L215 590.8 L215 513.3 L0 513.3 L0 590.8 Z M830 0 Q860.4 159.6 1020 190 Q860.4 220.4 830 380 Q799.6 220.4 640 190 Q799.6 159.6 830 0 Z M560 10 Q575.2 89.8 655 105 Q575.2 120.2 560 200 Q544.8 120.2 465 105 Q544.8 89.8 560 10 Z"/></svg>';
	/** WPZOOM AI Connect icon: white tile with a grey border, the WPZOOM W in blue, one sparkle. Same file as assets/admin/wpzoom-ai-icon.svg (and the wordpress.org icon). */
	const MARK = '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1024 1024" role="img" aria-label="WPZOOM AI Connect"><rect x="12" y="12" width="1000" height="1000" rx="212" fill="#ffffff" stroke="#d9dce0" stroke-width="24"/><path transform="translate(212.4 330.5) scale(8.3)" fill="#2858d1" d="M5.1 8.3H0V0.8H20.7V8.3H14.3L22.2 33.9L34.3 5.6H38.7L50.8 33.9L58.2 8.3H51.5V0.8H72.2V8.3H67L53.9 50H49.2L36.2 20L23.1 50H18.4L5.1 8.3Z"/><path transform="translate(800 228) scale(7.763) translate(-69.85 -39.5)" fill="#3496ff" stroke="#ffffff" stroke-width="3.34" stroke-linejoin="round" paint-order="stroke" d="M67.8041 54.0231C68.3961 54.4432 69.1045 54.6682 69.8304 54.6666C70.5583 54.67 71.2694 54.4425 71.8625 54.0212C72.4577 53.583 72.9084 52.9768 73.1566 52.2805L74.4709 48.2328C74.7893 47.279 75.3268 46.4111 76.0362 45.6983C76.743 44.9887 77.6064 44.4548 78.5569 44.1396L82.6733 42.8016C83.3541 42.5589 83.9421 42.1123 84.3583 41.5225C84.7758 40.9331 85 40.2286 85 39.5063C85 38.7839 84.7758 38.0794 84.3583 37.49C83.9084 36.8631 83.2698 36.398 82.5352 36.1638L78.4828 34.8494C77.5293 34.5336 76.6626 33.9996 75.9518 33.2899C75.241 32.5802 74.7056 31.7143 74.3883 30.7612L73.0487 26.6495C72.8095 25.9687 72.3646 25.3806 71.7749 24.9644C71.1807 24.5534 70.4754 24.3333 69.7529 24.3333C69.0305 24.3333 68.3252 24.5534 67.7309 24.9644C67.1244 25.3907 66.6694 25.999 66.4335 26.7017L65.0889 30.8371C64.7714 31.7624 64.2489 32.604 63.5602 33.2988C62.8716 33.9936 62.0348 34.5237 61.1123 34.8494L57.0027 36.1806C56.3152 36.4233 55.7221 36.8749 55.3042 37.4715C54.9907 37.9169 54.7855 38.4295 54.705 38.9683C54.6246 39.5071 54.6711 40.0573 54.8408 40.5749C55.0105 41.0926 55.2987 41.5635 55.6825 41.95C56.0663 42.3366 56.5351 42.6282 57.0515 42.8016L61.0972 44.1177C62.0533 44.4381 62.9216 44.977 63.6331 45.6916C64.3456 46.4019 64.8807 47.2702 65.195 48.226L66.5279 52.3226C66.766 53.0084 67.2122 53.6029 67.8041 54.0231Z"/></svg>';

	public static function init(): void {
		add_action( 'admin_menu', array( __CLASS__, 'add_page' ) );
		add_action( 'admin_init', array( __CLASS__, 'maybe_redirect' ) );
		add_action( 'admin_post_wpzoom_ai_disconnect', array( __CLASS__, 'handle_disconnect' ) );
		add_action( 'admin_post_wpzoom_ai_css_setting', array( __CLASS__, 'handle_css_setting' ) );
		add_action( 'admin_post_wpzoom_ai_post_types', array( __CLASS__, 'handle_post_types' ) );
		add_action( 'admin_post_wpzoom_ai_access', array( __CLASS__, 'handle_access' ) );
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		add_filter( 'plugin_action_links_' . plugin_basename( WPZOOM_AI_CONNECT_FILE ), array( __CLASS__, 'action_links' ) );
	}

	public static function url( array $args = array() ): string {
		return add_query_arg( $args, admin_url( 'admin.php?page=' . self::PAGE ) );
	}

	/** The screen's tabs, in order: id => label. */
	public static function tabs(): array {
		return array(
			'start'      => __( 'Get started', 'wpzoom-ai-connect' ),
			'can'        => __( 'What it can do', 'wpzoom-ai-connect' ),
			'checkup'    => __( 'Site checkup', 'wpzoom-ai-connect' ),
			'settings'   => __( 'Settings', 'wpzoom-ai-connect' ),
			'developers' => __( 'Developers', 'wpzoom-ai-connect' ),
		);
	}

	/** A tab's address, with an optional #anchor inside it. */
	public static function tab_url( string $tab, string $anchor = '', array $args = array() ): string {
		return self::url( array_merge( array( 'tab' => $tab ), $args ) ) . ( '' !== $anchor ? '#' . $anchor : '' );
	}

	public static function add_page(): void {
		if ( ! WPZOOM_AI_Access::can_see() ) {
			return; // another administrator chose to keep AI Connect to themselves (For developers)
		}
		add_menu_page(
			__( 'AI Connect', 'wpzoom-ai-connect' ),
			__( 'AI Connect', 'wpzoom-ai-connect' ),
			'manage_options',
			self::PAGE,
			array( __CLASS__, 'render' ),
			'data:image/svg+xml;base64,' . base64_encode( self::MENU_ICON ),
			59
		);
	}

	/** Old Settings-menu links and the post-activation redirect both land on the welcome screen. */
	public static function maybe_redirect(): void {
		global $pagenow;
		if ( wp_doing_ajax() ) {
			return;
		}
		self::remember_source();
		if ( 'options-general.php' === $pagenow && isset( $_GET['page'] ) && self::PAGE === $_GET['page'] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			wp_safe_redirect( self::url() );
			exit;
		}
		if ( get_transient( self::TRANSIENT_ACTIVATED ) ) {
			delete_transient( self::TRANSIENT_ACTIVATED );
			if ( ! isset( $_GET['activate-multi'] ) && ! is_network_admin() && WPZOOM_AI_Access::can_see() ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				wp_safe_redirect( self::url( array( 'welcome' => 1 ) ) );
				exit;
			}
		}
	}

	/**
	 * A link to this screen from Inspiro Starter Sites or the theme says where the user came from (?ref=…). That
	 * last touch replaces the install source, and goes along with "Connect this site".
	 */
	private static function remember_source(): void {
		if ( ! isset( $_GET['page'], $_GET['ref'] ) || self::PAGE !== $_GET['page'] || ! current_user_can( 'manage_options' ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return;
		}
		$ref = wpzoom_ai_connect_clean_source( sanitize_key( wp_unslash( $_GET['ref'] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( '' !== $ref && get_option( WPZOOM_AI_CONNECT_SOURCE_OPTION ) !== $ref ) {
			update_option( WPZOOM_AI_CONNECT_SOURCE_OPTION, $ref, false );
		}
	}

	public static function action_links( array $links ): array {
		if ( ! WPZOOM_AI_Access::can_see() ) {
			return $links;
		}
		array_unshift( $links, '<a href="' . esc_url( self::url() ) . '">' . esc_html__( 'Get started', 'wpzoom-ai-connect' ) . '</a>' );
		return $links;
	}

	public static function enqueue( string $hook ): void {
		if ( 'toplevel_page_' . self::PAGE !== $hook ) {
			return;
		}
		wp_enqueue_style( 'wpzoom-ai-admin', plugins_url( 'assets/admin/wpzoom-ai.css', WPZOOM_AI_CONNECT_FILE ), array(), WPZOOM_AI_CONNECT_VERSION );
		wp_enqueue_script( 'wpzoom-ai-admin', plugins_url( 'assets/admin/wpzoom-ai.js', WPZOOM_AI_CONNECT_FILE ), array(), WPZOOM_AI_CONNECT_VERSION, true );
		wp_localize_script( 'wpzoom-ai-admin', 'wzaiL10n', array(
			'copied' => __( 'Copied', 'wpzoom-ai-connect' ),
			'copy'   => __( 'Copy', 'wpzoom-ai-connect' ),
			'open_claude'  => __( 'Open Claude', 'wpzoom-ai-connect' ),
			'open_chatgpt' => __( 'Open ChatGPT', 'wpzoom-ai-connect' ),
			'open_other'   => __( 'Open your AI app', 'wpzoom-ai-connect' ),
			'guide_url'    => self::tab_url( 'start', 'wzai-guide' ),
			'close'        => __( 'Close', 'wpzoom-ai-connect' ),
			'feedback'     => array(
				'ajaxUrl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => wp_create_nonce( WPZOOM_AI_Deactivation::FEEDBACK_ACTION ),
				'sending' => __( 'Sending…', 'wpzoom-ai-connect' ),
				'send'    => __( 'Send feedback', 'wpzoom-ai-connect' ),
				'soon'    => __( 'You just sent one. Please wait a minute before sending another.', 'wpzoom-ai-connect' ),
				'failed'  => __( 'It could not be sent. Please try again in a minute, or write to us on the support forum.', 'wpzoom-ai-connect' ),
			),
			'tabs'         => array(
				'wzai-start'      => self::tab_url( 'start' ),
				'wzai-guide'      => self::tab_url( 'start', 'wzai-guide' ),
				'wzai-can'        => self::tab_url( 'can' ),
				'wzai-checkups'   => self::tab_url( 'checkup' ),
				'wzai-post-types' => self::tab_url( 'settings', 'wzai-post-types' ),
				'wzai-access'     => self::tab_url( 'settings', 'wzai-access' ),
				'wzai-dev'        => self::tab_url( 'developers' ),
			),
		) );
	}

	/* ---------- connections (application passwords named after the plugin) ---------- */

	/**
	 * Every "WPZOOM AI Connect" application password on the site, with its user.
	 *
	 * @return array<int, array{user_id:int, user:string, uuid:string, created:int, last_used:int, account:string}>
	 */
	public static function connections(): array {
		if ( ! class_exists( 'WP_Application_Passwords' ) ) {
			return array();
		}
		$out   = array();
		$users = get_users( array(
			'meta_key' => WP_Application_Passwords::USERMETA_KEY_APPLICATION_PASSWORDS, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'fields'   => array( 'ID', 'display_name' ),
			'number'   => 200,
		) );
		foreach ( $users as $user ) {
			foreach ( WP_Application_Passwords::get_user_application_passwords( (int) $user->ID ) as $password ) {
				if ( 0 !== strcasecmp( (string) ( $password['name'] ?? '' ), self::APP_NAME ) ) {
					continue;
				}
				$out[] = array(
					'user_id'   => (int) $user->ID,
					'user'      => $user->display_name,
					'uuid'      => (string) $password['uuid'],
					'created'   => (int) ( $password['created'] ?? 0 ),
					'last_used' => (int) ( $password['last_used'] ?? 0 ),
					'account'   => wpzoom_ai_connect_account_of( (int) $user->ID, (string) $password['uuid'] ),
				);
			}
		}
		usort( $out, static function ( $a, $b ) { return $b['last_used'] <=> $a['last_used'] ?: $b['created'] <=> $a['created']; } );
		return $out;
	}

	public static function handle_disconnect(): void {
		check_admin_referer( 'wpzoom_ai_disconnect' );
		$user_id = isset( $_POST['user_id'] ) ? (int) $_POST['user_id'] : 0;
		$uuid    = isset( $_POST['uuid'] ) ? sanitize_text_field( wp_unslash( $_POST['uuid'] ) ) : '';
		if ( ! $user_id || ! $uuid || ! WPZOOM_AI_Access::can_see() || ( get_current_user_id() !== $user_id && ! current_user_can( 'edit_user', $user_id ) ) ) {
			wp_die( esc_html__( 'You are not allowed to remove this connection.', 'wpzoom-ai-connect' ) );
		}
		$password = WP_Application_Passwords::get_user_application_password( $user_id, $uuid );
		if ( $password && 0 === strcasecmp( (string) $password['name'], self::APP_NAME ) ) {
			WP_Application_Passwords::delete_application_password( $user_id, $uuid );
		}
		wp_safe_redirect( self::url( array( 'wzai_disconnected' => 1 ) ) );
		exit;
	}

	/** Lets the site owner allow or stop the connection editing Additional CSS. */
	public static function handle_css_setting(): void {
		check_admin_referer( 'wpzoom_ai_css_setting' );
		if ( ! WPZOOM_AI_Access::can_see() ) {
			wp_die( esc_html__( 'You are not allowed to change this setting.', 'wpzoom-ai-connect' ) );
		}
		update_option( WPZOOM_AI_CSS::OPTION, empty( $_POST['allow_css'] ) ? '0' : '1', false );
		wp_safe_redirect( self::tab_url( 'settings', 'wzai-css', array( 'wzai_css_saved' => 1 ) ) );
		exit;
	}

	/** Saves which custom post types the connection may read or change. */
	public static function handle_post_types(): void {
		check_admin_referer( 'wpzoom_ai_post_types' );
		if ( ! WPZOOM_AI_Access::can_see() ) {
			wp_die( esc_html__( 'You are not allowed to change this setting.', 'wpzoom-ai-connect' ) );
		}
		$posted = isset( $_POST['post_types'] ) && is_array( $_POST['post_types'] ) ? map_deep( wp_unslash( $_POST['post_types'] ), 'sanitize_key' ) : array(); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- sanitized by map_deep; save() also checks every type and value against its own lists.
		WPZOOM_AI_Post_Types::save( $posted );
		wp_safe_redirect( self::tab_url( 'settings', 'wzai-post-types', array( 'wzai_types_saved' => 1 ) ) );
		exit;
	}

	/** Saves who sees AI Connect: every administrator, or only the ones ticked (always including whoever saves). */
	public static function handle_access(): void {
		check_admin_referer( 'wpzoom_ai_access' );
		if ( ! WPZOOM_AI_Access::can_see() || WPZOOM_AI_Access::locked() ) {
			wp_die( esc_html__( 'You are not allowed to change this setting.', 'wpzoom-ai-connect' ) );
		}
		$only = isset( $_POST['access'] ) && 'chosen' === sanitize_key( wp_unslash( $_POST['access'] ) );
		$ids  = $only && isset( $_POST['admins'] ) && is_array( $_POST['admins'] ) ? array_map( 'absint', wp_unslash( $_POST['admins'] ) ) : array();
		WPZOOM_AI_Access::save( $only ? array_merge( $ids, array( get_current_user_id() ) ) : array() );
		wp_safe_redirect( self::tab_url( 'settings', 'wzai-access', array( 'wzai_access_saved' => 1 ) ) );
		exit;
	}

	/* ---------- what this site can do ---------- */

	private static function abilities(): array {
		if ( ! function_exists( 'wp_get_abilities' ) ) {
			return array();
		}
		$out = array();
		foreach ( wp_get_abilities() as $ability ) {
			if ( 0 !== strpos( $ability->get_name(), 'wpzoom/' ) ) {
				continue;
			}
			$annotations = $ability->get_meta_item( 'annotations' );
			$out[]       = array(
				'name'        => $ability->get_name(),
				'label'       => $ability->get_label(),
				'description' => $ability->get_description(),
				'readonly'    => is_array( $annotations ) && ! empty( $annotations['readonly'] ),
			);
		}
		return $out;
	}

	public static function is_local( string $url ): bool {
		$host = (string) wp_parse_url( $url, PHP_URL_HOST );
		return in_array( $host, array( 'localhost', '127.0.0.1', '[::1]' ), true ) || preg_match( '/\.(localhost|test|local)$/', $host );
	}

	/** Everything the screen needs, computed once. */
	private static function state(): array {
		$theme    = wp_get_theme();
		$home     = home_url( '/' );
		$https    = 0 === strpos( $home, 'https://' ) || self::is_local( $home );
		$app_pw   = function_exists( 'wp_is_application_passwords_available' ) && wp_is_application_passwords_available();
		$theme_ok = class_exists( 'WPZOOM_AI_Theme_Settings' ) && WPZOOM_AI_Theme_Settings::is_available();

		$settings_total = 0;
		if ( $theme_ok ) {
			try {
				$all            = WPZOOM_AI_Theme_Settings::all( array( 'include_values' => false ) );
				$settings_total = (int) ( $all['total'] ?? 0 );
			} catch ( Throwable $e ) {
				$settings_total = 0;
			}
		}
		$patterns = class_exists( 'WPZOOM_AI_Patterns' ) ? count( WPZOOM_AI_Patterns::all() ) : 0;
		$starter  = class_exists( 'WPZOOM_AI_Starter_Sites' ) && WPZOOM_AI_Starter_Sites::available();
		$premium  = class_exists( 'WPZOOM_AI_Starter_Sites' ) && WPZOOM_AI_Starter_Sites::premium_importer();
		$demos    = $starter ? WPZOOM_AI_Starter_Sites::demo_count() : 0;
		$imported = (string) get_option( 'inspiro_starter_sites_imported_demo_id', '' );
		$content  = array(
			'forms'     => class_exists( 'WPZOOM_AI_Content' ) && WPZOOM_AI_Content::has_forms(),
			'portfolio' => class_exists( 'WPZOOM_AI_Content' ) && WPZOOM_AI_Content::has_portfolio(),
			'recipes'   => class_exists( 'WPZOOM_AI_Content' ) && WPZOOM_AI_Content::has_recipes(),
		);
		$diagnosis = array( 'summary' => array( 'warnings' => 0, 'verdict' => '' ), 'checks' => array() );
		if ( class_exists( 'WPZOOM_AI_Diagnostics' ) ) {
			try {
				$diagnosis = WPZOOM_AI_Diagnostics::diagnose();
			} catch ( Throwable $e ) {
				// The page must render even if a check throws.
			}
		}
		$other = array();
		foreach ( array( 'vibe-ai/vibe-ai.php' => 'WPVibe', 'easy-mcp-ai/easy-mcp-ai.php' => 'Easy MCP AI', 'mcp-adapter/mcp-adapter.php' => 'MCP Adapter' ) as $file => $name ) {
			if ( is_plugin_active( $file ) ) {
				$other[] = $name;
			}
		}

		$connections = self::connections();
		$source      = wpzoom_ai_connect_clean_source( (string) get_option( WPZOOM_AI_CONNECT_SOURCE_OPTION, '' ) );
		$connect     = array( 'site_url' => untrailingslashit( home_url() ), 'return' => self::url() );
		if ( '' !== $source ) {
			$connect['ref'] = $source;
		}

		return array(
			'has_api'        => function_exists( 'wp_register_ability' ),
			'https'          => $https,
			'app_passwords'  => $app_pw,
			'can_connect'    => $https && $app_pw,
			'theme'          => $theme,
			'theme_ok'       => $theme_ok,
			'settings_total' => $settings_total,
			'patterns'       => $patterns,
			'starter'        => $starter,
			'premium_demos'  => $premium,
			'demos'          => $demos,
			'imported'       => $imported,
			'content'        => $content,
			'diagnosis'      => $diagnosis,
			'other'          => $other,
			'connections'    => $connections,
			'last_ai_call'   => WPZOOM_AI_Scope::last_ai_call( $connections ),
			'abilities'      => self::abilities(),
			'site_label'     => preg_replace( '#^https?://#', '', untrailingslashit( $home ) ),
			'connect_url'    => add_query_arg( array_map( 'rawurlencode', $connect ), self::CONNECTOR_ORIGIN . '/connect' ),
		);
	}

	/** Prompts worth trying on this particular site. */
	private static function prompts( array $s ): array {
		$prompts   = array( __( 'Show me my site overview', 'wpzoom-ai-connect' ) );
		$prompts[] = $s['diagnosis']['summary']['warnings'] > 0
			? __( 'Run a checkup of my site and fix what you can', 'wpzoom-ai-connect' )
			: __( 'Run a checkup of my site', 'wpzoom-ai-connect' );
		if ( $s['starter'] && ! $s['premium_demos'] && ! $s['imported'] ) {
			$prompts[] = __( 'Which starter site would fit a photography studio?', 'wpzoom-ai-connect' );
		}
		if ( $s['theme_ok'] ) {
			$prompts[] = __( 'Switch my site to a darker color palette', 'wpzoom-ai-connect' );
		}
		if ( $s['patterns'] ) {
			$prompts[] = __( 'Create an About page with a team section and a contact form', 'wpzoom-ai-connect' );
		}
		if ( $s['content']['forms'] ) {
			$prompts[] = __( 'Create a contact form with name, email, budget and message', 'wpzoom-ai-connect' );
		}
		if ( $s['content']['portfolio'] ) {
			$prompts[] = __( 'Add a portfolio item for my latest project', 'wpzoom-ai-connect' );
		}
		if ( $s['content']['recipes'] ) {
			$prompts[] = __( 'Create a recipe post for banana bread with a recipe card', 'wpzoom-ai-connect' );
		}
		return array_slice( $prompts, 0, 5 );
	}

	/* ---------- markup helpers ---------- */

	/** What the screen's own markup may contain when echoed: post HTML plus the inline SVG icons and copy buttons. */
	private static function allowed_html(): array {
		$svg = array(
			'xmlns'           => true,
			'viewbox'         => true,
			'fill'            => true,
			'stroke'          => true,
			'stroke-width'    => true,
			'stroke-linecap'  => true,
			'stroke-linejoin' => true,
			'role'            => true,
			'aria-hidden'     => true,
			'aria-label'      => true,
		);
		$tags = wp_kses_allowed_html( 'post' );
		$tags['svg']    = $svg;
		$tags['path']   = array( 'd' => true, 'fill' => true, 'transform' => true, 'stroke' => true, 'stroke-width' => true, 'stroke-linejoin' => true, 'paint-order' => true );
		$tags['rect']   = array( 'x' => true, 'y' => true, 'width' => true, 'height' => true, 'rx' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true );
		$tags['circle'] = array( 'cx' => true, 'cy' => true, 'r' => true );
		$tags['button'] = array( 'type' => true, 'class' => true, 'data-copy' => true, 'aria-label' => true );
		return $tags;
	}

	private static function icon( string $name ): string {
		$paths = array(
			'copy'     => '<rect x="9" y="9" width="11" height="11" rx="2"/><path d="M5 15V5h10"/>',
			'check'    => '<path d="M5 12l5 5L20 7"/>',
			'warn'     => '<path d="M12 5v9M12 18h.01"/>',
			'info'     => '<path d="M12 8h.01M12 11v6"/>',
			'arrow'    => '<path d="M5 12h14M13 6l6 6-6 6"/>',
			'terminal' => '<path d="M4 17l6-5-6-5M12 19h8"/>',
			'home'     => '<path d="M4 11l8-7 8 7v9H4z"/><path d="M10 20v-6h4v6"/>',
			'palette'  => '<circle cx="12" cy="12" r="9"/><circle cx="8.5" cy="10" r="1.2"/><circle cx="12" cy="7.5" r="1.2"/><circle cx="15.5" cy="10" r="1.2"/>',
			'layout'   => '<rect x="3" y="4" width="18" height="16" rx="2"/><path d="M3 10h18M9 10v10"/>',
			'wand'     => '<path d="M5 19l4-4M14 4l6 6-9 9H5v-6z"/>',
			'form'     => '<rect x="4" y="5" width="16" height="14" rx="2"/><path d="M8 10h8M8 14h5"/>',
			'shield'   => '<path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7z"/><path d="M9 12l2 2 4-4"/>',
			'lock'     => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V7a4 4 0 018 0v4"/>',
			'code'     => '<path d="M8 8l-4 4 4 4M16 8l4 4-4 4"/>',
			'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21c0-4 4-6 8-6s8 2 8 6"/>',
			'brush'    => '<path d="M14 4l6 6-8 8H6v-6z"/><path d="M4 20l2-2"/>',
			'grid'     => '<rect x="4" y="4" width="7" height="7" rx="1"/><rect x="13" y="4" width="7" height="7" rx="1"/><rect x="4" y="13" width="7" height="7" rx="1"/><rect x="13" y="13" width="7" height="7" rx="1"/>',
			'chevron'  => '<path d="M9 6l6 6-6 6"/>',
			'play'     => '<path d="M8 5.5v13l11-6.5z" fill="currentColor"/>',
			'message'  => '<path d="M4 5h16v11H9l-5 4z"/>',
			'shield-check' => '<path d="M12 3l8 4v5c0 5-3.5 8-8 9-4.5-1-8-4-8-9V7z"/><path d="M9 12l2 2 4-4"/>',
		);
		$bold  = in_array( $name, array( 'check', 'warn', 'info' ), true );
		$width = $bold ? 3 : ( in_array( $name, array( 'copy', 'chevron', 'arrow', 'terminal' ), true ) ? 2 : 1.6 );
		return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="' . $width . '" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">' . ( $paths[ $name ] ?? '' ) . '</svg>';
	}

	/** Brand marks (Simple Icons, CC0) for the assistants the guide covers. */
	private static function logo( string $name ): string {
		$logos = array(
			'claude'  => array( '#D97757', 'm4.7144 15.9555 4.7174-2.6471.079-.2307-.079-.1275h-.2307l-.7893-.0486-2.6956-.0729-2.3375-.0971-2.2646-.1214-.5707-.1215-.5343-.7042.0546-.3522.4797-.3218.686.0608 1.5179.1032 2.2767.1578 1.6514.0972 2.4468.255h.3886l.0546-.1579-.1336-.0971-.1032-.0972L6.973 9.8356l-2.55-1.6879-1.3356-.9714-.7225-.4918-.3643-.4614-.1578-1.0078.6557-.7225.8803.0607.2246.0607.8925.686 1.9064 1.4754 2.4893 1.8336.3643.3035.1457-.1032.0182-.0728-.164-.2733-1.3539-2.4467-1.445-2.4893-.6435-1.032-.17-.6194c-.0607-.255-.1032-.4674-.1032-.7285L6.287.1335 6.6997 0l.9957.1336.419.3642.6192 1.4147 1.0018 2.2282 1.5543 3.0296.4553.8985.2429.8318.091.255h.1579v-.1457l.1275-1.706.2368-2.0947.2307-2.6957.0789-.7589.3764-.9107.7468-.4918.5828.2793.4797.686-.0668.4433-.2853 1.8517-.5586 2.9021-.3643 1.9429h.2125l.2429-.2429.9835-1.3053 1.6514-2.0643.7286-.8196.85-.9046.5464-.4311h1.0321l.759 1.1293-.34 1.1657-1.0625 1.3478-.8804 1.1414-1.2628 1.7-.7893 1.36.0729.1093.1882-.0183 2.8535-.607 1.5421-.2794 1.8396-.3157.8318.3886.091.3946-.3278.8075-1.967.4857-2.3072.4614-3.4364.8136-.0425.0304.0486.0607 1.5482.1457.6618.0364h1.621l3.0175.2247.7892.522.4736.6376-.079.4857-1.2142.6193-1.6393-.3886-3.825-.9107-1.3113-.3279h-.1822v.1093l1.0929 1.0686 2.0035 1.8092 2.5075 2.3314.1275.5768-.3218.4554-.34-.0486-2.2039-1.6575-.85-.7468-1.9246-1.621h-.1275v.17l.4432.6496 2.3436 3.5214.1214 1.0807-.17.3521-.6071.2125-.6679-.1214-1.3721-1.9246L14.38 17.959l-1.1414-1.9428-.1397.079-.674 7.2552-.3156.3703-.7286.2793-.6071-.4614-.3218-.7468.3218-1.4753.3886-1.9246.3157-1.53.2853-1.9004.17-.6314-.0121-.0425-.1397.0182-1.4328 1.9672-2.1796 2.9446-1.7243 1.8456-.4128.164-.7164-.3704.0667-.6618.4008-.5889 2.386-3.0357 1.4389-1.882.929-1.0868-.0062-.1579h-.0546l-6.3385 4.1164-1.1293.1457-.4857-.4554.0608-.7467.2307-.2429 1.9064-1.3114Z' ),
			'chatgpt' => array( '#000000', 'M22.2819 9.8211a5.9847 5.9847 0 0 0-.5157-4.9108 6.0462 6.0462 0 0 0-6.5098-2.9A6.0651 6.0651 0 0 0 4.9807 4.1818a5.9847 5.9847 0 0 0-3.9977 2.9 6.0462 6.0462 0 0 0 .7427 7.0966 5.98 5.98 0 0 0 .511 4.9107 6.051 6.051 0 0 0 6.5146 2.9001A5.9847 5.9847 0 0 0 13.2599 24a6.0557 6.0557 0 0 0 5.7718-4.2058 5.9894 5.9894 0 0 0 3.9977-2.9001 6.0557 6.0557 0 0 0-.7475-7.0729zm-9.022 12.6081a4.4755 4.4755 0 0 1-2.8764-1.0408l.1419-.0804 4.7783-2.7582a.7948.7948 0 0 0 .3927-.6813v-6.7369l2.02 1.1686a.071.071 0 0 1 .038.052v5.5826a4.504 4.504 0 0 1-4.4945 4.4944zm-9.6607-4.1254a4.4708 4.4708 0 0 1-.5346-3.0137l.142.0852 4.783 2.7582a.7712.7712 0 0 0 .7806 0l5.8428-3.3685v2.3324a.0804.0804 0 0 1-.0332.0615L9.74 19.9502a4.4992 4.4992 0 0 1-6.1408-1.6464zM2.3408 7.8956a4.485 4.485 0 0 1 2.3655-1.9728V11.6a.7664.7664 0 0 0 .3879.6765l5.8144 3.3543-2.0201 1.1685a.0757.0757 0 0 1-.071 0l-4.8303-2.7865A4.504 4.504 0 0 1 2.3408 7.872zm16.5963 3.8558L13.1038 8.364 15.1192 7.2a.0757.0757 0 0 1 .071 0l4.8303 2.7913a4.4944 4.4944 0 0 1-.6765 8.1042v-5.6772a.79.79 0 0 0-.407-.667zm2.0107-3.0231l-.142-.0852-4.7735-2.7818a.7759.7759 0 0 0-.7854 0L9.409 9.2297V6.8974a.0662.0662 0 0 1 .0284-.0615l4.8303-2.7866a4.4992 4.4992 0 0 1 6.6802 4.66zM8.3065 12.863l-2.02-1.1638a.0804.0804 0 0 1-.038-.0567V6.0742a4.4992 4.4992 0 0 1 7.3757-3.4537l-.142.0805L8.704 5.459a.7948.7948 0 0 0-.3927.6813zm1.0976-2.3654l2.602-1.4998 2.6069 1.4998v2.9994l-2.5974 1.4997-2.6067-1.4997Z' ),
		);
		if ( ! isset( $logos[ $name ] ) ) {
			return self::icon( 'terminal' );
		}
		return '<svg viewBox="0 0 24 24" role="img" aria-hidden="true" fill="' . $logos[ $name ][0] . '"><path d="' . $logos[ $name ][1] . '"/></svg>';
	}

	private static function copy_field( string $value ): string {
		return '<span class="wzai-copy"><span class="wzai-copy__text">' . esc_html( $value ) . '</span><button type="button" class="wzai-btn wzai-btn--ghost wzai-btn--sm" data-copy="' . esc_attr( $value ) . '">' . esc_html__( 'Copy', 'wpzoom-ai-connect' ) . '</button></span>';
	}

	private static function notice( string $type, string $html ): void {
		$icon = array( 'ok' => 'check', 'warn' => 'warn', 'info' => 'info' )[ $type ] ?? 'info';
		echo wp_kses( '<div class="wzai-notice wzai-notice--' . esc_attr( $type ) . '" role="status">' . self::icon( $icon ) . '<div>' . $html . '</div></div>', self::allowed_html() );
	}

	/** A prompt to try, with a copy button. */
	private static function prompt_row( string $prompt ): string {
		return '<div class="wzai-prompt"><span>' . esc_html( $prompt ) . '</span><button type="button" data-copy="' . esc_attr( $prompt ) . '" aria-label="' . esc_attr__( 'Copy', 'wpzoom-ai-connect' ) . '">' . self::icon( 'copy' ) . '</button></div>';
	}

	/**
	 * One "Customize & Configure"-style card. $actions: list of [label, url, primary?, external?]. $prompt: an
	 * example to say to the AI, shown with a copy button.
	 */
	private static function card( string $icon, string $title, string $about, array $actions = array(), string $extra_class = '', string $badge = '', string $prompt = '' ): void {
		?>
		<div class="wzai-card <?php echo esc_attr( $extra_class ); ?>">
			<h3 class="wzai-card__title"><?php echo wp_kses( self::icon( $icon ), self::allowed_html() ); ?><?php echo esc_html( $title ); ?><?php echo wp_kses( $badge, self::allowed_html() ); ?></h3>
			<p class="wzai-card__about"><?php echo esc_html( $about ); ?></p>
			<?php if ( '' !== $prompt ) : ?>
				<div class="wzai-card__try"><span class="wzai-card__try-label"><?php esc_html_e( 'Try:', 'wpzoom-ai-connect' ); ?></span><?php echo wp_kses( self::prompt_row( $prompt ), self::allowed_html() ); ?></div>
			<?php endif; ?>
			<?php if ( $actions ) : ?>
				<div class="wzai-card__actions">
					<?php foreach ( $actions as $action ) : ?>
						<a class="wzai-btn <?php echo ! empty( $action[2] ) ? 'wzai-btn--primary' : 'wzai-btn--secondary'; ?>" href="<?php echo esc_url( $action[1] ); ?>"<?php echo ! empty( $action[3] ) ? ' target="_blank" rel="noopener"' : ''; ?>><?php echo esc_html( $action[0] ); ?></a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<?php
	}

	/** "Who sees AI Connect" on the Settings tab: every administrator, or only the ones ticked. */
	private static function render_access(): void {
		$chosen = WPZOOM_AI_Access::chosen();
		$me     = get_current_user_id();
		?>
		<div class="wzai-access" id="wzai-access">
			<h2 class="wzai-access__title"><?php esc_html_e( 'Who sees AI Connect', 'wpzoom-ai-connect' ); ?></h2>
			<p class="wzai-muted"><?php esc_html_e( 'Working on a client\'s site? Keep the AI Connect menu, this screen and its notices to yourself. Other administrators still see the plugin on the Plugins screen, and each connection stays listed on the connected user\'s profile, so the site owner can always remove it.', 'wpzoom-ai-connect' ); ?></p>
			<?php if ( WPZOOM_AI_Access::locked() ) : ?>
				<p>
					<?php
					echo esc_html(
						$chosen
							? sprintf( /* translators: 1: constant name, 2: list of user names */ __( 'Set in wp-config.php (%1$s): only %2$s.', 'wpzoom-ai-connect' ), WPZOOM_AI_Access::CONSTANT, wp_sprintf( '%l', array_map( static function ( $id ) { return get_userdata( $id )->display_name; }, $chosen ) ) )
							: sprintf( /* translators: %s: constant name */ __( '%s is set in wp-config.php but names no administrator of this site, so every administrator sees AI Connect.', 'wpzoom-ai-connect' ), WPZOOM_AI_Access::CONSTANT )
					);
					?>
				</p>
			<?php else : ?>
				<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
					<?php wp_nonce_field( 'wpzoom_ai_access' ); ?>
					<input type="hidden" name="action" value="wpzoom_ai_access">
					<label class="wzai-access__choice"><input type="radio" name="access" value="all" <?php checked( ! $chosen ); ?>> <?php esc_html_e( 'Every administrator', 'wpzoom-ai-connect' ); ?></label>
					<label class="wzai-access__choice"><input type="radio" name="access" value="chosen" <?php checked( (bool) $chosen ); ?>> <?php esc_html_e( 'Only these administrators:', 'wpzoom-ai-connect' ); ?></label>
					<div class="wzai-access__users">
						<?php foreach ( WPZOOM_AI_Access::administrators() as $admin ) : ?>
							<?php $is_me = (int) $admin->ID === $me; ?>
							<label class="wzai-access__user">
								<input type="checkbox" name="admins[]" value="<?php echo (int) $admin->ID; ?>" <?php checked( $is_me || in_array( (int) $admin->ID, $chosen, true ) ); ?> <?php disabled( $is_me ); ?>>
								<span><?php echo esc_html( $admin->display_name ); ?> <span class="wzai-muted"><?php echo esc_html( $admin->user_email ); ?><?php echo $is_me ? ' · ' . esc_html__( 'you', 'wpzoom-ai-connect' ) : ''; ?></span></span>
							</label>
						<?php endforeach; ?>
					</div>
					<button type="submit" class="wzai-btn wzai-btn--secondary wzai-btn--sm"><?php esc_html_e( 'Save', 'wpzoom-ai-connect' ); ?></button>
					<p class="wzai-muted wzai-small">
						<?php
						printf(
							/* translators: %s: example wp-config.php line */
							esc_html__( 'Setting it up on many sites? Add %s to wp-config.php (logins, user IDs or emails).', 'wpzoom-ai-connect' ),
							'<code>' . esc_html( "define( '" . WPZOOM_AI_Access::CONSTANT . "', 'your-login' );" ) . '</code>'
						);
						?>
					</p>
				</form>
			<?php endif; ?>
		</div>
		<?php
	}

	/* ---------- the screen ---------- */

	public static function render(): void {
		if ( ! WPZOOM_AI_Access::can_see() ) {
			return;
		}
		$s          = self::state();
		$connected  = ! empty( $s['connections'] );
		$waiting    = $connected && ! $s['last_ai_call']; // the site is connected, but no AI app has used it yet
		$just_done  = isset( $_GET['wzai_connected'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$just_gone  = isset( $_GET['wzai_disconnected'] ); // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$prompts    = self::prompts( $s );
		$warnings   = array_values( array_filter( $s['diagnosis']['checks'], static function ( $c ) { return 'warn' === $c['status']; } ) );
		$theme_name = $s['theme']->get( 'Name' );
		$can_click  = $s['can_connect'] && $s['has_api'];
		$account    = '';
		foreach ( $s['connections'] as $c ) {
			if ( '' !== $c['account'] ) {
				$account = $c['account'];
				break;
			}
		}

		if ( ! $s['has_api'] ) {
			$badge = array( 'warn', __( 'Needs WordPress 6.9', 'wpzoom-ai-connect' ) );
		} elseif ( $waiting ) {
			$badge = array( 'warn', __( 'Connected, waiting for your AI', 'wpzoom-ai-connect' ) );
		} elseif ( $connected ) {
			$badge = array( 'ok', sprintf( /* translators: %s: user display name */ __( 'Connected as %s', 'wpzoom-ai-connect' ), $s['connections'][0]['user'] ) );
		} elseif ( ! $s['can_connect'] ) {
			$badge = array( 'warn', __( 'Needs HTTPS', 'wpzoom-ai-connect' ) );
		} else {
			$badge = array( 'off', __( 'Not connected yet', 'wpzoom-ai-connect' ) );
		}

		// Not menu_page_url(): the page hangs off the theme's custom 'inspiro' parent slug, which that helper turns into wp-admin/inspiro?page=…
		$starter_url = $s['starter'] ? admin_url( 'admin.php?page=inspiro-demo' ) : '';
		$kinds       = array_keys( array_filter( $s['content'] ) );
		$kind_label  = array( 'forms' => __( 'forms', 'wpzoom-ai-connect' ), 'portfolio' => __( 'portfolio items', 'wpzoom-ai-connect' ), 'recipes' => __( 'recipe cards', 'wpzoom-ai-connect' ) );
		$post_types  = current_user_can( 'manage_options' ) ? WPZOOM_AI_Post_Types::choices() : array();
		$tabs        = self::tabs();
		$tab         = isset( $_GET['tab'] ) ? sanitize_key( wp_unslash( $_GET['tab'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		if ( ! isset( $tabs[ $tab ] ) ) {
			// Setup first; once an AI has used the site, what it can do.
			$tab = $connected && ! $waiting ? 'can' : 'start';
		}
		?>
		<div class="wrap wzai" id="wzai">
				<div class="wzai-main">
					<?php // WordPress moves other plugins' notices here; without it they land after the h1 inside the hero. ?>
					<hr class="wp-header-end">

					<?php
					if ( $waiting ) {
						self::notice( $just_done ? 'ok' : 'warn', '<strong>' . esc_html( $just_done ? __( 'Site connected. One more step:', 'wpzoom-ai-connect' ) : __( 'One more step:', 'wpzoom-ai-connect' ) ) . '</strong> '
							. ( '' !== $account
								? sprintf(
									/* translators: %s: WPZOOM account email */
									esc_html__( 'add WPZOOM to your AI app (Claude, ChatGPT or another) and, when it asks you to sign in to WPZOOM, use %s. That is the account this site is connected to; it does not have to match your Claude or ChatGPT login. It takes a minute and you do it once; the steps are below. Until then your AI cannot reach this site.', 'wpzoom-ai-connect' ),
									'<strong>' . esc_html( $account ) . '</strong>'
								)
								: esc_html__( 'add WPZOOM to your AI app (Claude, ChatGPT or another), with the same email you signed in with. It takes a minute and you do it once; the steps are below. Until then your AI cannot reach this site.', 'wpzoom-ai-connect' ) )
							. ' <a href="' . esc_url( self::tab_url( 'start', 'wzai-guide' ) ) . '">' . esc_html__( 'Show me how', 'wpzoom-ai-connect' ) . ' &darr;</a>' );
					} elseif ( $just_done || ( $connected && isset( $_GET['welcome'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						self::notice( 'ok', '<strong>' . esc_html__( 'Connected.', 'wpzoom-ai-connect' ) . '</strong> ' . esc_html__( 'Go back to your AI and say "show me my site overview" to start.', 'wpzoom-ai-connect' ) );
					} elseif ( $just_gone ) {
						self::notice( 'info', esc_html__( 'Disconnected. Your AI can no longer reach this site until you connect again.', 'wpzoom-ai-connect' ) );
					}
					if ( isset( $_GET['wzai_css_saved'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						self::notice( 'ok', WPZOOM_AI_CSS::allowed_for_connection()
							? esc_html__( 'Saved. Your AI may add Additional CSS on this site.', 'wpzoom-ai-connect' )
							: esc_html__( 'Saved. Your AI can no longer change Additional CSS on this site.', 'wpzoom-ai-connect' ) );
					}
					if ( isset( $_GET['wzai_access_saved'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						$chosen = WPZOOM_AI_Access::chosen();
						self::notice( 'ok', $chosen
							? esc_html( sprintf( /* translators: %s: list of user names */ __( 'Saved. Only %s see AI Connect: its menu, this screen and its notices. Other administrators still see the plugin on the Plugins screen.', 'wpzoom-ai-connect' ), wp_sprintf( '%l', array_map( static function ( $id ) { $u = get_userdata( $id ); return $u ? $u->display_name : '#' . $id; }, $chosen ) ) ) )
							: esc_html__( 'Saved. Every administrator sees AI Connect.', 'wpzoom-ai-connect' ) );
					}
					if ( isset( $_GET['wzai_types_saved'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
						self::notice( 'ok', esc_html__( 'Saved. Your AI now follows these choices for custom post types.', 'wpzoom-ai-connect' ) );
					}
					if ( ! $s['has_api'] ) {
						self::notice( 'warn', esc_html__( 'This plugin needs WordPress 6.9 or newer. Update WordPress from Dashboard → Updates, then come back.', 'wpzoom-ai-connect' ) );
					} elseif ( ! $s['can_connect'] && ! $connected ) {
						self::notice( 'warn', $s['https']
							? esc_html__( 'Application passwords are turned off on this site, so AI assistants cannot sign in. A security plugin usually does this; allow them and reload this page.', 'wpzoom-ai-connect' )
							: esc_html__( 'Your site is not served over HTTPS yet, so AI assistants cannot connect safely. Ask your host to enable SSL (most do it free), then come back.', 'wpzoom-ai-connect' ) );
					}
					?>

					<div class="wzai-hero">
						<div class="wzai-hero__top">
							<div class="wzai-brand">
								<span class="wzai-brand__mark"><?php echo wp_kses( self::MARK, self::allowed_html() ); ?></span>
								<div>
									<h1><?php esc_html_e( 'AI Connect', 'wpzoom-ai-connect' ); ?> <span class="wzai-brand__version"><?php echo esc_html( WPZOOM_AI_CONNECT_VERSION ); ?></span></h1>
									<div class="wzai-brand__sub"><?php
										echo esc_html( $s['theme_ok']
											? sprintf( /* translators: %s: theme name */ __( 'Your AI just learned %s.', 'wpzoom-ai-connect' ), $theme_name )
											: __( 'Let Claude, ChatGPT or any AI assistant work on this site.', 'wpzoom-ai-connect' ) );
									?></div>
								</div>
							</div>
							<span class="wzai-badge wzai-badge--<?php echo esc_attr( $badge[0] ); ?>"><span class="wzai-dot"></span> <?php echo esc_html( $badge[1] ); ?></span>
						</div>
						<p><?php esc_html_e( 'Talk to your site the way you would to a designer: change colors and layouts, add pages and sections, adapt a starter site, create forms. Claude, ChatGPT and other AI assistants work with it.', 'wpzoom-ai-connect' ); ?></p>
						<div class="wzai-hero__actions">
							<?php if ( $waiting ) : ?>
								<a class="wzai-btn wzai-btn--primary" href="<?php echo esc_url( self::tab_url( 'start', 'wzai-guide' ) ); ?>"><?php esc_html_e( 'Add WPZOOM to your AI', 'wpzoom-ai-connect' ); ?></a>
								<a class="wzai-btn wzai-btn--secondary" href="<?php echo esc_url( self::tab_url( 'can' ) ); ?>"><?php esc_html_e( 'What can it do?', 'wpzoom-ai-connect' ); ?> &rarr;</a>
							<?php elseif ( $connected ) : ?>
								<a class="wzai-btn wzai-btn--primary" href="https://claude.ai/new" target="_blank" rel="noopener" data-open-ai><?php esc_html_e( 'Open Claude', 'wpzoom-ai-connect' ); ?></a>
								<a class="wzai-btn wzai-btn--secondary" href="<?php echo esc_url( self::tab_url( 'can' ) ); ?>"><?php esc_html_e( 'What can it do?', 'wpzoom-ai-connect' ); ?> &rarr;</a>
							<?php elseif ( $can_click ) : ?>
								<a class="wzai-btn wzai-btn--primary" href="<?php echo esc_url( $s['connect_url'] ); ?>"><?php esc_html_e( 'Connect this site', 'wpzoom-ai-connect' ); ?></a>
								<a class="wzai-btn wzai-btn--secondary" href="<?php echo esc_url( self::tab_url( 'start', 'wzai-guide' ) ); ?>"><?php esc_html_e( 'How it works', 'wpzoom-ai-connect' ); ?> &rarr;</a>
							<?php else : ?>
								<a class="wzai-btn wzai-btn--secondary" href="<?php echo esc_url( self::tab_url( 'checkup' ) ); ?>"><?php esc_html_e( 'See what to fix', 'wpzoom-ai-connect' ); ?> &rarr;</a>
							<?php endif; ?>
							<?php if ( ! $connected || $waiting ) : // the walkthrough, while setup isn't finished ?>
								<a class="wzai-btn wzai-btn--secondary" href="<?php echo esc_url( self::VIDEO_URL ); ?>" target="_blank" rel="noopener"><?php echo wp_kses( self::icon( 'play' ), self::allowed_html() ); ?><?php esc_html_e( 'Watch the video', 'wpzoom-ai-connect' ); ?> <span class="wzai-btn__meta"><?php echo esc_html( self::VIDEO_LENGTH ); ?></span></a>
							<?php endif; ?>
						</div>
					</div>

					<?php
					if ( $s['other'] ) {
						self::notice( 'info', sprintf( /* translators: %s: plugin names */ esc_html__( 'You already use %s. Your WPZOOM tools show up there too, nothing else to set up.', 'wpzoom-ai-connect' ), '<strong>' . esc_html( implode( ', ', $s['other'] ) ) . '</strong>' ) );
					}
					?>

					<nav class="wzai-tabs" aria-label="<?php esc_attr_e( 'AI Connect sections', 'wpzoom-ai-connect' ); ?>">
						<?php foreach ( $tabs as $id => $label ) : ?>
							<a class="wzai-tabs__tab<?php echo $id === $tab ? ' is-active' : ''; ?>" href="<?php echo esc_url( self::tab_url( $id ) ); ?>"<?php echo $id === $tab ? ' aria-current="page"' : ''; ?>><?php echo esc_html( $label ); ?><?php if ( 'checkup' === $id && $warnings ) : ?> <span class="wzai-tabs__count"><?php echo (int) count( $warnings ); ?></span><?php endif; ?></a>
						<?php endforeach; ?>
						<button type="button" class="wzai-tabs__feedback" data-wzai-feedback-open><?php echo wp_kses( self::icon( 'message' ), self::allowed_html() ); ?><?php esc_html_e( 'Share feedback', 'wpzoom-ai-connect' ); ?></button>
					</nav>

					<?php if ( 'start' === $tab ) : ?>
					<!-- Get started -->
					<section class="wzai-section wzai-section--first" id="wzai-start">
						<div class="wzai-section__head">
							<h2><?php esc_html_e( 'Get started in three steps', 'wpzoom-ai-connect' ); ?></h2>
							<p><?php esc_html_e( 'About a minute. Nothing to install on your computer.', 'wpzoom-ai-connect' ); ?></p>
						</div>
						<div class="wzai-grid wzai-grid--3">

							<div class="wzai-card wzai-card--done">
								<h3 class="wzai-card__title"><span class="wzai-step__num"><?php echo wp_kses( self::icon( 'check' ), self::allowed_html() ); ?></span><?php esc_html_e( 'Pick your AI', 'wpzoom-ai-connect' ); ?></h3>
								<div class="wzai-card__about" role="group" aria-label="<?php esc_attr_e( 'AI assistant', 'wpzoom-ai-connect' ); ?>">
									<button type="button" class="wzai-choice" data-client="claude" aria-pressed="true"><span class="wzai-choice__icon wzai-choice__icon--logo"><?php echo wp_kses( self::logo( 'claude' ), self::allowed_html() ); ?></span><span><span class="wzai-choice__label">Claude</span><span class="wzai-choice__hint"><?php esc_html_e( 'claude.ai, desktop app, Claude Code', 'wpzoom-ai-connect' ); ?></span></span></button>
									<button type="button" class="wzai-choice" data-client="chatgpt" aria-pressed="false"><span class="wzai-choice__icon wzai-choice__icon--logo"><?php echo wp_kses( self::logo( 'chatgpt' ), self::allowed_html() ); ?></span><span><span class="wzai-choice__label">ChatGPT</span><span class="wzai-choice__hint"><?php esc_html_e( 'chatgpt.com, from its plugin directory', 'wpzoom-ai-connect' ); ?></span></span></button>
									<button type="button" class="wzai-choice" data-client="other" aria-pressed="false"><span class="wzai-choice__icon"><?php echo wp_kses( self::icon( 'terminal' ), self::allowed_html() ); ?></span><span><span class="wzai-choice__label"><?php esc_html_e( 'Cursor and others', 'wpzoom-ai-connect' ); ?></span><span class="wzai-choice__hint"><?php esc_html_e( 'any app that speaks MCP', 'wpzoom-ai-connect' ); ?></span></span></button>
								</div>
								<div class="wzai-card__foot"><?php esc_html_e( 'The same connection works in all of them.', 'wpzoom-ai-connect' ); ?></div>
							</div>

							<div class="wzai-card <?php echo $connected ? 'wzai-card--done' : ( $can_click ? 'wzai-card--active' : '' ); ?>">
								<h3 class="wzai-card__title"><span class="wzai-step__num"><?php echo wp_kses( $connected ? self::icon( 'check' ) : '2', self::allowed_html() ); ?></span><?php esc_html_e( 'Connect this site', 'wpzoom-ai-connect' ); ?></h3>
								<?php if ( $connected ) : ?>
									<div class="wzai-card__about">
										<?php foreach ( array_slice( $s['connections'], 0, 3 ) as $c ) : ?>
											<div class="wzai-conn">
												<div><strong><?php echo esc_html( $c['user'] ); ?></strong><?php if ( '' !== $c['account'] ) : ?><span><?php echo esc_html( sprintf( /* translators: %s: WPZOOM account email */ __( 'WPZOOM account: %s', 'wpzoom-ai-connect' ), $c['account'] ) ); ?></span><?php endif; ?><span><?php
													echo esc_html( $waiting
														? sprintf( /* translators: %s: human time diff */ __( 'Connected %s ago, no AI has used it yet', 'wpzoom-ai-connect' ), human_time_diff( $c['created'] ) )
														: sprintf( /* translators: %s: human time diff */ __( 'Last used %s ago', 'wpzoom-ai-connect' ), human_time_diff( max( $c['last_used'], $s['last_ai_call'] ) ) ) );
												?></span></div>
												<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" onsubmit="return confirm('<?php echo esc_js( __( 'Disconnect this AI from the site?', 'wpzoom-ai-connect' ) ); ?>');">
													<?php wp_nonce_field( 'wpzoom_ai_disconnect' ); ?>
													<input type="hidden" name="action" value="wpzoom_ai_disconnect">
													<input type="hidden" name="user_id" value="<?php echo (int) $c['user_id']; ?>">
													<input type="hidden" name="uuid" value="<?php echo esc_attr( $c['uuid'] ); ?>">
													<button type="submit" class="wzai-btn wzai-btn--ghost wzai-btn--sm"><?php esc_html_e( 'Disconnect', 'wpzoom-ai-connect' ); ?></button>
												</form>
											</div>
										<?php endforeach; ?>
									</div>
									<div class="wzai-card__actions"><a class="wzai-btn wzai-btn--secondary wzai-btn--block" href="<?php echo esc_url( $s['connect_url'] ); ?>"><?php esc_html_e( 'Connect another account', 'wpzoom-ai-connect' ); ?></a></div>
									<div class="wzai-card__foot"><?php esc_html_e( 'Your AI acts as the connected user, limited to content, design settings and this plugin\'s abilities. It can never install or remove plugins and themes, manage users, change Settings or add scripts.', 'wpzoom-ai-connect' ); ?></div>
								<?php else : ?>
									<p class="wzai-card__about"><?php esc_html_e( 'Sign in with your email, approve the connection on this site, and you are back here. Connecting lets the WPZOOM connector act on this site as you, limited to content, design settings and this plugin\'s abilities, until you disconnect.', 'wpzoom-ai-connect' ); ?></p>
									<div class="wzai-card__actions">
										<?php if ( $can_click ) : ?>
											<?php $connect_label = sprintf( /* translators: %s: site host */ __( 'Connect %s', 'wpzoom-ai-connect' ), $s['site_label'] ); ?>
											<a class="wzai-btn wzai-btn--primary wzai-btn--lg wzai-btn--block" href="<?php echo esc_url( $s['connect_url'] ); ?>" title="<?php echo esc_attr( $connect_label ); ?>"><span class="wzai-btn__label"><?php echo esc_html( $connect_label ); ?></span></a>
										<?php else : ?>
											<span class="wzai-btn wzai-btn--primary wzai-btn--lg wzai-btn--block" aria-disabled="true"><?php esc_html_e( 'Connect this site', 'wpzoom-ai-connect' ); ?></span>
										<?php endif; ?>
									</div>
									<div class="wzai-card__foot"><?php esc_html_e( 'You can disconnect any time from this page.', 'wpzoom-ai-connect' ); ?></div>
								<?php endif; ?>
							</div>

							<div class="wzai-card <?php echo $connected ? 'wzai-card--active' : ''; ?>">
								<h3 class="wzai-card__title"><span class="wzai-step__num">3</span><?php esc_html_e( 'Say this to your AI', 'wpzoom-ai-connect' ); ?></h3>
								<div class="wzai-card__about">
									<?php if ( $waiting ) : ?>
										<p class="wzai-small"><?php esc_html_e( 'First add WPZOOM to your AI app (steps below). Then try:', 'wpzoom-ai-connect' ); ?></p>
									<?php endif; ?>
									<?php foreach ( $prompts as $prompt ) : ?>
										<?php echo wp_kses( self::prompt_row( $prompt ), self::allowed_html() ); ?>
									<?php endforeach; ?>
								</div>
								<div class="wzai-card__foot"><?php esc_html_e( 'Plain language works. Your AI asks before it changes anything.', 'wpzoom-ai-connect' ); ?></div>
							</div>
						</div>

						<div class="wzai-card wzai-card--soft wzai-guide wzai-mt-24" id="wzai-guide">
							<div data-guide="claude">
								<h3 class="wzai-card__title"><?php echo wp_kses( self::logo( 'claude' ), self::allowed_html() ); ?><?php esc_html_e( 'Add WPZOOM to Claude', 'wpzoom-ai-connect' ); ?></h3>
								<p class="wzai-small wzai-mt-8"><?php esc_html_e( 'WPZOOM is in Claude\'s connector directory, so there is no address to paste.', 'wpzoom-ai-connect' ); ?></p>
								<p class="wzai-mt-16"><a class="wzai-btn wzai-btn--primary wzai-btn--lg wzai-btn--logo" href="<?php echo esc_url( self::CLAUDE_DIRECTORY_URL ); ?>" target="_blank" rel="noopener"><?php echo wp_kses( self::logo( 'claude' ), self::allowed_html() ); ?><?php esc_html_e( 'Add to Claude', 'wpzoom-ai-connect' ); ?></a></p>
								<ol>
									<li><?php esc_html_e( 'Press the button. Claude opens WPZOOM in its directory: press "Connect to Claude" there.', 'wpzoom-ai-connect' ); ?></li>
									<li><?php echo '' !== $account
										? wp_kses( sprintf( /* translators: %s: WPZOOM account email */ __( 'When Claude asks you to sign in to WPZOOM, use %s (we send a code). It does not have to match your Claude login.', 'wpzoom-ai-connect' ), '<strong>' . esc_html( $account ) . '</strong>' ), array( 'strong' => array() ) )
										: esc_html__( 'When Claude asks you to sign in to WPZOOM, use your email (we send a code).', 'wpzoom-ai-connect' ); ?></li>
									<li><?php esc_html_e( 'Come back here and press "Connect this site".', 'wpzoom-ai-connect' ); ?></li>
								</ol>
								<details class="wzai-details wzai-details--inline">
									<summary><?php echo wp_kses( self::icon( 'chevron' ), self::allowed_html() ); ?><?php esc_html_e( 'Add it by address instead', 'wpzoom-ai-connect' ); ?></summary>
									<p class="wzai-small wzai-mt-8"><?php esc_html_e( 'Do this if your Claude does not show the directory listing, for example in a workspace where an admin limits which connectors can be added.', 'wpzoom-ai-connect' ); ?></p>
									<ol>
										<li><?php printf( /* translators: %s: link */ esc_html__( 'Open %s and choose "Add custom connector".', 'wpzoom-ai-connect' ), '<a href="https://claude.ai/new#customize/connectors/yours" target="_blank" rel="noopener">claude.ai → Customize → Connectors</a>' ); ?></li>
										<li><?php esc_html_e( 'Name it WPZOOM and paste this address:', 'wpzoom-ai-connect' ); ?> <?php echo wp_kses( self::copy_field( self::CONNECTOR_URL ), self::allowed_html() ); ?></li>
										<li><?php echo '' !== $account
											? wp_kses( sprintf( /* translators: %s: WPZOOM account email */ __( 'Press Connect and sign in to WPZOOM with %s (we send a code). It does not have to match your Claude login.', 'wpzoom-ai-connect' ), '<strong>' . esc_html( $account ) . '</strong>' ), array( 'strong' => array() ) )
											: esc_html__( 'Press Connect. Claude asks you to sign in with your email (we send a code).', 'wpzoom-ai-connect' ); ?></li>
									</ol>
								</details>
							</div>
							<div data-guide="chatgpt" hidden>
								<h3 class="wzai-card__title"><?php echo wp_kses( self::logo( 'chatgpt' ), self::allowed_html() ); ?><?php esc_html_e( 'Add WPZOOM to ChatGPT', 'wpzoom-ai-connect' ); ?></h3>
								<p class="wzai-small wzai-mt-8"><?php esc_html_e( 'WPZOOM is in ChatGPT\'s plugin directory, so there is no address to paste. If your ChatGPT plan has no plugins, use Claude, where WPZOOM works on the free plan.', 'wpzoom-ai-connect' ); ?></p>
								<p class="wzai-mt-16"><a class="wzai-btn wzai-btn--primary wzai-btn--lg wzai-btn--logo" href="<?php echo esc_url( self::CHATGPT_PLUGIN_URL ); ?>" target="_blank" rel="noopener"><?php echo wp_kses( self::logo( 'chatgpt' ), self::allowed_html() ); ?><?php esc_html_e( 'Add to ChatGPT', 'wpzoom-ai-connect' ); ?></a></p>
								<ol>
									<li><?php esc_html_e( 'Press the button. ChatGPT opens the WPZOOM plugin: press "Install plugin" there.', 'wpzoom-ai-connect' ); ?></li>
									<li><?php echo '' !== $account
										? wp_kses( sprintf( /* translators: %s: WPZOOM account email */ __( 'When ChatGPT asks you to sign in to WPZOOM, use %s (we send a code). It does not have to match your ChatGPT login.', 'wpzoom-ai-connect' ), '<strong>' . esc_html( $account ) . '</strong>' ), array( 'strong' => array() ) )
										: esc_html__( 'When ChatGPT asks you to sign in to WPZOOM, use your email (we send a code).', 'wpzoom-ai-connect' ); ?></li>
									<li><?php esc_html_e( 'Come back here and press "Connect this site". In a chat, type @WPZOOM to use it.', 'wpzoom-ai-connect' ); ?></li>
								</ol>
								<details class="wzai-details wzai-details--inline">
									<summary><?php echo wp_kses( self::icon( 'chevron' ), self::allowed_html() ); ?><?php esc_html_e( 'Add it by address instead', 'wpzoom-ai-connect' ); ?></summary>
									<p class="wzai-small wzai-mt-8"><?php esc_html_e( 'Do this when other plugins on your site give AI their own tools, as some SEO and code-snippet plugins do: the directory version has a fixed set of tools and leaves those out. It works on paid ChatGPT plans; in a Business or Enterprise workspace an admin may have to allow custom MCP servers first.', 'wpzoom-ai-connect' ); ?></p>
									<ol>
										<li><?php printf( /* translators: %s: link */ esc_html__( 'Open %s (or Plugins in the ChatGPT sidebar), press the + button and choose "Add custom MCP server".', 'wpzoom-ai-connect' ), '<a href="https://chatgpt.com/settings/plugins-settings" target="_blank" rel="noopener">ChatGPT → Settings → Plugins</a>' ); ?></li>
										<li><?php esc_html_e( 'Name it WPZOOM. Under Connection, paste this address:', 'wpzoom-ai-connect' ); ?> <?php echo wp_kses( self::copy_field( self::CONNECTOR_URL ), self::allowed_html() ); ?></li>
										<li><?php echo '' !== $account
											? wp_kses( sprintf( /* translators: %s: WPZOOM account email */ __( 'Choose OAuth for authentication, tick "I understand and want to continue", then press "Create as a plugin" and sign in to WPZOOM with %s (we send a code). It does not have to match your ChatGPT login.', 'wpzoom-ai-connect' ), '<strong>' . esc_html( $account ) . '</strong>' ), array( 'strong' => array() ) )
											: esc_html__( 'Choose OAuth for authentication, tick "I understand and want to continue", then press "Create as a plugin" and sign in with your email (we send a code).', 'wpzoom-ai-connect' ); ?></li>
									</ol>
								</details>
							</div>
							<div data-guide="other" hidden>
								<h3 class="wzai-card__title"><?php echo wp_kses( self::icon( 'terminal' ), self::allowed_html() ); ?><?php esc_html_e( 'Add WPZOOM to Cursor, Claude Code or any MCP app', 'wpzoom-ai-connect' ); ?></h3>
								<ol>
									<li><?php esc_html_e( 'Claude Code, in a terminal:', 'wpzoom-ai-connect' ); ?> <?php echo wp_kses( self::copy_field( 'claude mcp add --transport http wpzoom ' . self::CONNECTOR_URL ), self::allowed_html() ); ?></li>
									<li><?php esc_html_e( 'Cursor and others: add a server named WPZOOM with this address (Streamable HTTP, OAuth):', 'wpzoom-ai-connect' ); ?> <?php echo wp_kses( self::copy_field( self::CONNECTOR_URL ), self::allowed_html() ); ?></li>
									<li><?php esc_html_e( 'Sign in with your email when the app opens the browser, then come back here and press "Connect this site".', 'wpzoom-ai-connect' ); ?></li>
								</ol>
							</div>
						</div>
					</section>
					<?php endif; ?>

					<?php if ( 'can' === $tab ) : ?>
					<!-- What your AI can do -->
					<section class="wzai-section wzai-section--first" id="wzai-can">
						<div class="wzai-section__head"><h2><?php esc_html_e( 'What your AI can do on this site', 'wpzoom-ai-connect' ); ?></h2></div>
						<div class="wzai-grid wzai-grid--3">
							<?php
							if ( $s['theme_ok'] ) {
								self::card( 'palette', __( 'Theme and design', 'wpzoom-ai-connect' ),
									sprintf( /* translators: %d: count */ _n( '%d theme setting it can read and change: colors, palettes, fonts, header and footer layouts.', '%d theme settings it can read and change: colors, palettes, fonts, header and footer layouts.', $s['settings_total'], 'wpzoom-ai-connect' ), $s['settings_total'] ),
									array( array( __( 'Open Customizer', 'wpzoom-ai-connect' ), admin_url( 'customize.php' ) ) ), '', '',
									__( 'Make the header dark and use a serif font for headings', 'wpzoom-ai-connect' ) );
							} else {
								self::card( 'palette', __( 'Theme and design', 'wpzoom-ai-connect' ),
									__( 'Theme settings work with WPZOOM themes. With another theme, your AI can still build pages, forms and checkups.', 'wpzoom-ai-connect' ),
									array( array( __( 'Browse WPZOOM themes', 'wpzoom-ai-connect' ), 'https://www.wpzoom.com/themes/', false, true ) ), 'wzai-card--soft', '',
									__( 'What can you change on this site?', 'wpzoom-ai-connect' ) );
							}
							self::card( 'layout', __( 'Pages and sections', 'wpzoom-ai-connect' ),
								$s['patterns']
									? sprintf( /* translators: %d: count */ _n( '%d ready-made section it can drop into any page: hero, services, pricing, team, FAQ, contact.', '%d ready-made sections it can drop into any page: hero, services, pricing, team, FAQ, contact.', $s['patterns'], 'wpzoom-ai-connect' ), $s['patterns'] )
									: __( 'It can create and edit pages, and drop 100+ ready-made design sections from the WPZOOM catalog into any page: hero, services, pricing, team, FAQ, contact.', 'wpzoom-ai-connect' ),
								array( array( __( 'View pages', 'wpzoom-ai-connect' ), admin_url( 'edit.php?post_type=page' ) ) ), '', '',
								__( 'Add a Services page with a hero, three services and a FAQ', 'wpzoom-ai-connect' ) );
							if ( $s['premium_demos'] ) {
								self::card( 'wand', __( 'Demo importer', 'wpzoom-ai-connect' ),
									__( 'Inspiro Premium installs its demos from the theme\'s own importer. Pick one there, then ask your AI to adapt the pages to your business.', 'wpzoom-ai-connect' ),
									array( array( __( 'Open demo importer', 'wpzoom-ai-connect' ), admin_url( 'admin.php?page=wpzoom_demo_importer' ) ) ), '', '',
									__( 'Rewrite the homepage for my bakery in Amsterdam', 'wpzoom-ai-connect' ) );
							} elseif ( $s['starter'] ) {
								self::card( 'wand', __( 'Starter sites', 'wpzoom-ai-connect' ),
									$s['imported']
										? sprintf( /* translators: %s: demo id */ __( 'You imported "%s". Ask your AI to adapt its pages to your business.', 'wpzoom-ai-connect' ), $s['imported'] )
										: sprintf( /* translators: %d: count */ _n( '%d complete demo to import here; then ask your AI to adapt it to your business.', '%d complete demos to import here; then ask your AI to adapt one to your business.', $s['demos'], 'wpzoom-ai-connect' ), $s['demos'] ),
									$starter_url ? array( array( __( 'Browse starter sites', 'wpzoom-ai-connect' ), $starter_url ) ) : array(), '', '',
									$s['imported'] ? __( 'Rewrite the homepage for my bakery in Amsterdam', 'wpzoom-ai-connect' ) : __( 'Which starter site fits a photography studio?', 'wpzoom-ai-connect' ) );
							} else {
								self::card( 'wand', __( 'Starter sites', 'wpzoom-ai-connect' ),
									__( 'Install the free Inspiro Starter Sites plugin to import a complete demo, then ask your AI to adapt it to your business.', 'wpzoom-ai-connect' ),
									array( array( __( 'Install plugin', 'wpzoom-ai-connect' ), admin_url( 'plugin-install.php?s=inspiro%20starter%20sites&tab=search&type=term' ) ) ), 'wzai-card--soft', '',
									__( 'Rewrite my homepage for a yoga studio in Lisbon', 'wpzoom-ai-connect' ) );
							}
							self::card( 'form', __( 'Forms, portfolio, recipes', 'wpzoom-ai-connect' ),
								$kinds
									? sprintf( /* translators: %s: list of content types */ __( 'Ready on this site: %s. Describe what you need and it builds it.', 'wpzoom-ai-connect' ), implode( ', ', array_map( static function ( $k ) use ( $kind_label ) { return $kind_label[ $k ]; }, $kinds ) ) )
									: __( 'Add WPZOOM Forms, Portfolio or Recipe Card and your AI can create them by description.', 'wpzoom-ai-connect' ),
								array(), $kinds ? '' : 'wzai-card--soft', '',
								in_array( 'forms', $kinds, true ) ? __( 'Add a booking request form to the Contact page', 'wpzoom-ai-connect' )
									: ( in_array( 'portfolio', $kinds, true ) ? __( 'Add a portfolio item for my latest project', 'wpzoom-ai-connect' )
									: ( in_array( 'recipes', $kinds, true ) ? __( 'Turn my latest post into a recipe card', 'wpzoom-ai-connect' )
									: __( 'Add a contact section to my homepage', 'wpzoom-ai-connect' ) ) ) );
							self::card( 'shield', __( 'Checkups', 'wpzoom-ai-connect' ),
								$s['diagnosis']['summary']['verdict'] ? $s['diagnosis']['summary']['verdict'] . ' ' . __( 'License, HTTPS, permalinks, front page, slideshow and menus are checked.', 'wpzoom-ai-connect' ) : __( 'Ask your AI for a checkup of license, HTTPS, permalinks, front page and menus.', 'wpzoom-ai-connect' ),
								array( array( $warnings ? __( 'See what to fix', 'wpzoom-ai-connect' ) : __( 'See the checkup', 'wpzoom-ai-connect' ), self::tab_url( 'checkup' ) ) ),
								'wzai-card--soft',
								$warnings ? ' <span class="wzai-badge wzai-badge--warn">' . esc_html( sprintf( /* translators: %d: count */ _n( '%d to fix', '%d to fix', count( $warnings ), 'wpzoom-ai-connect' ), count( $warnings ) ) ) . '</span>' : ' <span class="wzai-badge wzai-badge--ok">' . esc_html__( 'All good', 'wpzoom-ai-connect' ) . '</span>',
								__( 'Run a checkup of my site and fix what you can', 'wpzoom-ai-connect' ) );
							self::card( 'lock', __( 'Safe by design', 'wpzoom-ai-connect' ),
								__( 'Your AI works as the WordPress user who connected, asks before changing anything, and loses access the moment you disconnect.', 'wpzoom-ai-connect' ),
								array( array( __( 'Your WPZOOM AI account', 'wpzoom-ai-connect' ), self::CONNECTOR_ORIGIN . '/sites', false, true ) ), 'wzai-card--soft', '',
								__( 'Show me what you changed this week and undo the last change', 'wpzoom-ai-connect' ) );
							?>
						</div>
					</section>
					<?php endif; ?>

					<?php if ( 'checkup' === $tab ) : ?>
						<?php
						$order  = array( 'warn' => 0, 'info' => 1, 'ok' => 2 );
						$checks = $s['diagnosis']['checks'];
						usort( $checks, static function ( $a, $b ) use ( $order ) { return ( $order[ $a['status'] ] ?? 1 ) <=> ( $order[ $b['status'] ] ?? 1 ); } );
						?>
						<section class="wzai-section wzai-section--first" id="wzai-checkups">
							<div class="wzai-section__head">
								<h2><?php echo esc_html( $warnings ? __( 'Worth fixing', 'wpzoom-ai-connect' ) : __( 'All good', 'wpzoom-ai-connect' ) ); ?></h2>
								<p><?php echo esc_html( $warnings ? __( 'Your AI can do most of these: say "run a checkup and fix what you can".', 'wpzoom-ai-connect' ) : __( 'Nothing to fix on this site right now. Your AI runs the same checkup when you ask for one.', 'wpzoom-ai-connect' ) ); ?></p>
							</div>
							<?php foreach ( $checks as $check ) : ?>
								<?php $status = in_array( $check['status'], array( 'ok', 'warn' ), true ) ? $check['status'] : 'info'; ?>
								<div class="wzai-check wzai-check--<?php echo esc_attr( $status ); ?>">
									<span class="wzai-check__icon"><?php echo wp_kses( self::icon( 'ok' === $status ? 'check' : $status ), self::allowed_html() ); ?></span>
									<div>
										<div class="wzai-check__title"><?php echo esc_html( $check['title'] ); ?></div>
										<?php if ( ! empty( $check['detail'] ) ) : ?><div class="wzai-check__detail"><?php echo esc_html( $check['detail'] ); ?></div><?php endif; ?>
										<?php if ( 'warn' === $status && ! empty( $check['fix'] ) ) : ?><div class="wzai-check__fix"><?php echo esc_html( $check['fix'] ); ?></div><?php endif; ?>
									</div>
								</div>
							<?php endforeach; ?>
						</section>
					<?php endif; ?>

					<?php if ( 'settings' === $tab ) : ?>
						<section class="wzai-section wzai-section--first" id="wzai-css">
							<div class="wzai-section__head">
								<h2><?php esc_html_e( 'Design', 'wpzoom-ai-connect' ); ?></h2>
							</div>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( 'wpzoom_ai_css_setting' ); ?>
								<input type="hidden" name="action" value="wpzoom_ai_css_setting">
								<label class="wzai-setting"><input type="checkbox" name="allow_css" value="1" <?php checked( WPZOOM_AI_CSS::allowed_for_connection() ); ?> onchange="this.form.submit()"> <span><strong><?php esc_html_e( 'Let your AI add Additional CSS', 'wpzoom-ai-connect' ); ?></strong><span class="wzai-muted"><?php esc_html_e( 'For design changes the theme settings cannot make. Each change is labelled and saved as a revision you can restore in the Customizer.', 'wpzoom-ai-connect' ); ?></span></span></label>
								<noscript><button type="submit" class="wzai-btn wzai-btn--secondary wzai-btn--sm"><?php esc_html_e( 'Save', 'wpzoom-ai-connect' ); ?></button></noscript>
							</form>
						</section>

					<?php if ( $post_types ) : ?>
						<section class="wzai-section" id="wzai-post-types">
							<div class="wzai-section__head">
								<h2><?php esc_html_e( 'Custom post types your AI can use', 'wpzoom-ai-connect' ); ?></h2>
								<p><?php esc_html_e( 'Choose what your AI may do with each type. Posts and pages are always available. New types stay closed until you open them, because some hold private data such as form entries or orders; WooCommerce products, the public catalog, start as read-only.', 'wpzoom-ai-connect' ); ?></p>
							</div>
							<form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
								<?php wp_nonce_field( 'wpzoom_ai_post_types' ); ?>
								<input type="hidden" name="action" value="wpzoom_ai_post_types">
							<div class="wzai-types">
								<?php foreach ( $post_types as $choice ) : ?>
									<?php $field = 'wzai-type-' . sanitize_html_class( $choice['type'] ); ?>
									<div class="wzai-types__row">
										<label for="<?php echo esc_attr( $field ); ?>"><strong><?php echo esc_html( $choice['label'] ); ?></strong> <code><?php echo esc_html( $choice['type'] ); ?></code></label>
										<?php if ( $choice['available'] ) : ?>
											<select id="<?php echo esc_attr( $field ); ?>" name="post_types[<?php echo esc_attr( $choice['type'] ); ?>]">
												<option value="none" <?php selected( $choice['mode'], WPZOOM_AI_Post_Types::NONE ); ?>><?php esc_html_e( 'No access', 'wpzoom-ai-connect' ); ?></option>
												<option value="read" <?php selected( $choice['mode'], WPZOOM_AI_Post_Types::READ ); ?>><?php esc_html_e( 'Can read', 'wpzoom-ai-connect' ); ?></option>
												<option value="edit" <?php selected( $choice['mode'], WPZOOM_AI_Post_Types::EDIT ); ?>><?php esc_html_e( 'Can read and edit', 'wpzoom-ai-connect' ); ?></option>
											</select>
										<?php else : ?>
											<span class="wzai-types__note"><?php esc_html_e( 'Not available: this type is closed to the WordPress REST API', 'wpzoom-ai-connect' ); ?></span>
										<?php endif; ?>
									</div>
								<?php endforeach; ?>
							</div>
								<div class="wzai-card__actions wzai-mt-16"><button type="submit" class="wzai-btn wzai-btn--primary"><?php esc_html_e( 'Save', 'wpzoom-ai-connect' ); ?></button></div>
								<div class="wzai-card__foot"><?php esc_html_e( 'Published pages your visitors can see stay public, so your AI can still view those like any visitor.', 'wpzoom-ai-connect' ); ?></div>
							</form>
						</section>
					<?php endif; ?>

						<section class="wzai-section">
							<?php self::render_access(); ?>
						</section>
					<?php endif; ?>

					<?php if ( 'developers' === $tab ) : ?>
					<section class="wzai-section wzai-section--first" id="wzai-dev">
							<div class="wzai-section__head">
								<h2><?php esc_html_e( 'For developers', 'wpzoom-ai-connect' ); ?></h2>
								<p><?php esc_html_e( 'Endpoints, the abilities this site offers your AI, and other MCP plugins.', 'wpzoom-ai-connect' ); ?></p>
							</div>
							<table class="wzai-table">
								<tbody>
								<tr><th><?php esc_html_e( 'Connector', 'wpzoom-ai-connect' ); ?></th><td><code><?php echo esc_html( self::CONNECTOR_URL ); ?></code> <span class="wzai-muted"><?php esc_html_e( 'Streamable HTTP, OAuth 2.1, hosted by WPZOOM', 'wpzoom-ai-connect' ); ?></span></td></tr>
								<tr><th><?php esc_html_e( 'Site REST', 'wpzoom-ai-connect' ); ?></th><td><code><?php echo esc_html( rest_url( 'wp-abilities/v1/abilities' ) ); ?></code> <span class="wzai-muted"><?php esc_html_e( 'Abilities API, application-password auth', 'wpzoom-ai-connect' ); ?></span></td></tr>
								<tr><th><?php esc_html_e( 'Theme', 'wpzoom-ai-connect' ); ?></th><td><?php echo esc_html( $theme_name . ' ' . $s['theme']->get( 'Version' ) ); ?> <span class="wzai-muted"><?php echo esc_html( $s['theme_ok'] ? __( 'Customizer catalog available', 'wpzoom-ai-connect' ) : __( 'no WPZOOM Customizer catalog; theme-setting abilities are off', 'wpzoom-ai-connect' ) ); ?></span></td></tr>
								<tr><th><?php esc_html_e( 'App passwords', 'wpzoom-ai-connect' ); ?></th><td><?php echo esc_html( $s['app_passwords'] ? __( 'Available', 'wpzoom-ai-connect' ) : __( 'Unavailable', 'wpzoom-ai-connect' ) ); ?> · <a href="<?php echo esc_url( admin_url( 'profile.php#application-passwords-section' ) ); ?>"><?php esc_html_e( 'manage on your profile', 'wpzoom-ai-connect' ); ?></a></td></tr>
								<tr><th><?php esc_html_e( 'Other MCP plugins', 'wpzoom-ai-connect' ); ?></th><td><?php echo esc_html( $s['other'] ? implode( ', ', $s['other'] ) : __( 'None detected', 'wpzoom-ai-connect' ) ); ?></td></tr>
								</tbody>
							</table>
							<table class="wzai-table wzai-mt-24">
								<thead><tr><th><?php echo esc_html( sprintf( /* translators: %d: count */ __( 'Abilities (%d)', 'wpzoom-ai-connect' ), count( $s['abilities'] ) ) ); ?></th><th><?php esc_html_e( 'What it does', 'wpzoom-ai-connect' ); ?></th><th><?php esc_html_e( 'Writes', 'wpzoom-ai-connect' ); ?></th></tr></thead>
								<tbody>
								<?php foreach ( $s['abilities'] as $ability ) : ?>
									<tr>
										<td><strong><?php echo esc_html( $ability['label'] ); ?></strong><br><code><?php echo esc_html( $ability['name'] ); ?></code></td>
										<td><?php echo esc_html( $ability['description'] ); ?></td>
										<td><?php echo $ability['readonly'] ? esc_html__( 'No', 'wpzoom-ai-connect' ) : esc_html__( 'Yes', 'wpzoom-ai-connect' ); ?></td>
									</tr>
								<?php endforeach; ?>
								</tbody>
							</table>
					</section>
					<?php endif; ?>

				</div>
				<?php self::render_feedback(); ?>
		</div>
		<?php
	}

	/** "Share feedback": one question, anonymous unless they want a reply. */
	private static function render_feedback(): void {
		$email = wp_get_current_user()->user_email;
		?>
		<div class="wzai-modal" id="wzai-feedback" hidden>
			<div class="wzai-modal__box" role="dialog" aria-modal="true" aria-labelledby="wzai-feedback-title">
				<form class="wzai-modal__body" data-wzai-feedback-form>
					<h2 id="wzai-feedback-title"><?php esc_html_e( 'What should AI Connect do next?', 'wpzoom-ai-connect' ); ?></h2>
					<p><?php esc_html_e( 'Something your AI couldn\'t do on your site, a tool you\'re missing, or something that got in your way. A sentence or two is enough, and we read every message.', 'wpzoom-ai-connect' ); ?></p>
					<label class="screen-reader-text" for="wzai-feedback-message"><?php esc_html_e( 'Your feedback', 'wpzoom-ai-connect' ); ?></label>
					<textarea id="wzai-feedback-message" name="message" rows="5" maxlength="2000" placeholder="<?php esc_attr_e( 'My AI should be able to…', 'wpzoom-ai-connect' ); ?>"></textarea>
					<label class="wzai-modal__check"><input type="checkbox" name="consent" value="1"> <?php echo wp_kses( sprintf( /* translators: %s: the current user's email */ __( 'You may reply to me at %s', 'wpzoom-ai-connect' ), '<strong>' . esc_html( $email ) . '</strong>' ), array( 'strong' => array() ) ); ?></label>
					<p class="wzai-modal__note"><?php echo wp_kses( self::icon( 'shield-check' ), self::allowed_html() ); ?><span><?php echo esc_html( sprintf( /* translators: %s: plugin version */ __( 'Anonymous unless you tick the box: we receive your message, the plugin version (%s) and your WordPress version, not your site address or email, and we don\'t store IP addresses.', 'wpzoom-ai-connect' ), WPZOOM_AI_CONNECT_VERSION ) ); ?></span></p>
					<p class="wzai-modal__error" role="alert" hidden></p>
				</form>
				<div class="wzai-modal__done" hidden>
					<h2><?php esc_html_e( 'Thank you!', 'wpzoom-ai-connect' ); ?></h2>
					<p><?php esc_html_e( 'Your message reached us. Ideas like yours decide what we build next.', 'wpzoom-ai-connect' ); ?></p>
				</div>
				<div class="wzai-modal__foot">
					<button type="button" class="wzai-btn wzai-btn--ghost" data-wzai-feedback-close><?php esc_html_e( 'Cancel', 'wpzoom-ai-connect' ); ?></button>
					<button type="button" class="wzai-btn wzai-btn--primary" data-wzai-feedback-send disabled><?php esc_html_e( 'Send feedback', 'wpzoom-ai-connect' ); ?></button>
				</div>
			</div>
		</div>
		<?php
	}
}
