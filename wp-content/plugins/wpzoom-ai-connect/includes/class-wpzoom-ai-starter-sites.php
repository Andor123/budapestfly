<?php
/**
 * Inspiro Starter Sites integration and the WPZOOM section catalog: list the demos (the user imports them in
 * WordPress admin), the ai.wpzoom.com account connection, the section catalog (list sections, insert them with
 * example copy) and, with Starter Sites, plan and build a draft page with AI as a background job (see
 * WPZOOM_AI_Jobs).
 *
 * The catalog abilities work on every site: when Inspiro Starter Sites is active they go through its
 * AiPageBuilder (one cache, one stylesheet); otherwise through the plugin's own WPZOOM_AI_Catalog,
 * WPZOOM_AI_Section_Renderer and WPZOOM_AI_Sections. Planning and building pages with AI still need Starter Sites.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Starter_Sites {

	const JOB_BUILD  = 'build-page';

	public static function init(): void {
		WPZOOM_AI_Jobs::register_handler( self::JOB_BUILD, array( __CLASS__, 'run_build' ) );
	}

	public static function available(): bool {
		return class_exists( 'Inspiro_Starter_Sites_Importer_Setup' ) && class_exists( '\Inspiro\Starter_Sites\InspiroStarterSitesImporter' );
	}

	/**
	 * True on Inspiro Premium (WPZOOM framework loaded, template 'inspiro'). Inspiro Starter Sites hides its
	 * Import Demo page there on purpose, because the theme has its own importer (WPZOOM → Demo Importer).
	 */
	public static function premium_importer(): bool {
		return class_exists( 'WPZOOM' ) && 'inspiro' === get_template();
	}

	/** The demo tools' answer on Inspiro Premium: use the theme's own importer. */
	private static function premium_demos_error() {
		return new WP_Error(
			'wpzoom_use_premium_importer',
			sprintf(
				/* translators: %s: URL of the theme's Demo Importer page. */
				__( 'This site runs Inspiro Premium, which installs its demos from the theme\'s own Demo Importer, not from Inspiro Starter Sites. Ask the user to open WPZOOM → Demo Importer (%s), pick a demo and import it there. After that you can adapt the imported pages, menus and theme settings to their business with the other tools.', 'wpzoom-ai-connect' ),
				admin_url( 'admin.php?page=wpzoom_demo_importer' )
			),
			array( 'importer_url' => admin_url( 'admin.php?page=wpzoom_demo_importer' ) )
		);
	}

	public static function ai_available(): bool {
		return class_exists( '\Inspiro\Starter_Sites\Ai\AiPageBuilder' ) && class_exists( '\Inspiro\Starter_Sites\Ai\AiProxyClient' );
	}

	private static function unavailable( string $what ) {
		/* translators: %s: the feature that needs the Inspiro Starter Sites plugin, e.g. "Demo import". */
		return new WP_Error( 'wpzoom_no_starter_sites', sprintf( __( '%s needs the Inspiro Starter Sites plugin (free on wordpress.org). Install and activate it, then try again.', 'wpzoom-ai-connect' ), $what ) );
	}

	/* ---------- demos ---------- */

	/** The free demo list, validated the way the importer does. */
	private static function free_demos(): array {
		$files = apply_filters( 'inspiro_starter_sites/import_files', array() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Inspiro Starter Sites' hook.
		if ( class_exists( '\Inspiro\Starter_Sites\Helpers' ) ) {
			$files = \Inspiro\Starter_Sites\Helpers::validate_import_file_info( $files );
		}
		return is_array( $files ) ? array_values( $files ) : array();
	}

	/** import_id => plugin slugs, from the importer's private map plus what every demo gets. */
	private static function demo_plugins(): array {
		$map = array();
		try {
			$setup  = Inspiro_Starter_Sites_Importer_Setup::get_instance();
			$method = new ReflectionMethod( $setup, 'demo_plugins' );
			$method->setAccessible( true );
			$map = (array) $method->invoke( $setup );
		} catch ( Throwable $e ) {
			// Older plugin without the map: only the always-on plugins.
		}
		return $map;
	}

	/** How many free demos can be imported (0 when the Starter Sites plugin is missing). */
	public static function demo_count(): int {
		return self::available() ? count( self::free_demos() ) : 0;
	}

	public static function list_demos() {
		if ( self::premium_importer() ) {
			return self::premium_demos_error();
		}
		if ( ! self::available() ) {
			return self::unavailable( __( 'Listing starter sites', 'wpzoom-ai-connect' ) );
		}
		$plugins  = self::demo_plugins();
		$imported = (string) get_option( 'inspiro_starter_sites_imported_demo_id', '' );
		$demos    = array();
		foreach ( self::free_demos() as $index => $demo ) {
			$id      = (string) ( $demo['import_id'] ?? '' );
			$demos[] = array(
				'index'            => $index,
				'id'               => $id,
				'name'             => (string) ( $demo['import_file_name'] ?? '' ),
				'builder'          => (string) ( $demo['type'] ?? 'blocks' ),
				'group'            => (string) ( $demo['group_label'] ?? '' ),
				'categories'       => array_values( (array) ( $demo['categories'] ?? array() ) ),
				'preview_url'      => (string) ( $demo['preview_url'] ?? '' ),
				'screenshot'       => (string) ( $demo['import_preview_image_url'] ?? '' ),
				'required_plugins' => array_values( array_unique( array_merge( array( 'wpzoom-forms' ), (array) ( $plugins[ $id ] ?? array() ) ) ) ),
				'missing_plugins'  => wp_list_pluck( self::missing_plugins( $id ), 'slug' ),
				'importable'       => true,
				'imported'         => $imported === $id,
			);
		}
		$premium = array();
		try {
			foreach ( (array) Inspiro_Starter_Sites_Importer_Setup::get_instance()->premium_demos() as $demo ) {
				$premium[] = array(
					'name'        => (string) ( $demo['import_file_name'] ?? '' ),
					'preview_url' => (string) ( $demo['preview_url'] ?? '' ),
					'screenshot'  => (string) ( $demo['import_preview_image_url'] ?? '' ),
					'importable'  => false,
				);
			}
		} catch ( Throwable $e ) {
			// Remote list unavailable; fine.
		}
		return array(
			'theme'          => wp_get_theme()->get( 'Name' ),
			'imported_demo'  => $imported ?: null,
			'demos'          => $demos,
			'premium_demos'  => array(
				'note'         => __( 'Premium starter sites come with Inspiro Premium and are imported from the theme, not from here.', 'wpzoom-ai-connect' ),
				'items'        => $premium,
			),
			'importer_url'   => admin_url( 'admin.php?page=inspiro-demo' ),
			'how_to_import'  => __( 'This connector does not import demos. Send the user to the Inspiro Starter Sites screen (importer_url) to import the one they pick, after installing any plugins listed in missing_plugins. Once it is imported, adapt its pages, menus and theme settings to their business with the other tools.', 'wpzoom-ai-connect' ),
		);
	}

	/** Plugin slug => basename of the installed plugin file, or '' when it is not installed. */
	private static function plugin_file( string $slug ): string {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		foreach ( array_keys( get_plugins() ) as $basename ) {
			if ( 0 === strpos( $basename, $slug . '/' ) || $basename === $slug . '.php' ) {
				return $basename;
			}
		}
		return '';
	}

	/**
	 * The plugins a demo needs that are not active. This plugin never installs or activates plugins itself: the
	 * site administrator does that from the dashboard.
	 *
	 * @return array<int, array{slug: string, installed: bool}>
	 */
	private static function missing_plugins( string $import_id ): array {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$missing = array();
		foreach ( array_unique( array_merge( array( 'wpzoom-forms' ), (array) ( self::demo_plugins()[ $import_id ] ?? array() ) ) ) as $slug ) {
			$file = self::plugin_file( (string) $slug );
			if ( '' === $file || ! is_plugin_active( $file ) ) {
				$missing[] = array( 'slug' => (string) $slug, 'installed' => '' !== $file );
			}
		}
		return $missing;
	}

	/* ---------- AI account on ai.wpzoom.com ---------- */

	private static function proxy() {
		return new \Inspiro\Starter_Sites\Ai\AiProxyClient();
	}

	/** Whether the site holds a registration key from ai.wpzoom.com (through either plugin: same option). */
	private static function connected(): bool {
		return WPZOOM_AI_Catalog::is_connected();
	}

	public static function ai_status() {
		$licensed = '' !== WPZOOM_AI_Catalog::premium_license();
		$out      = array(
			'connected' => $licensed || self::connected(),
			'licensed'  => $licensed,
			'email'     => WPZOOM_AI_Catalog::connected_email() ?: null,
		);
		if ( self::ai_available() ) {
			if ( $out['connected'] ) {
				$quota = self::proxy()->quota( 'check', \Inspiro\Starter_Sites\Ai\AiPageBuilder::FEATURE );
				if ( is_array( $quota ) ) {
					$out['page_builds'] = array_intersect_key( $quota, array_flip( array( 'used', 'limit', 'remaining', 'allowed' ) ) );
				}
			}
		} else {
			$out['page_builds'] = null;
			$out['note']        = __( 'The connection unlocks the section catalog (wpzoom/list-sections, wpzoom/insert-sections) on this theme. AI-written pages (wpzoom/plan-page, wpzoom/build-page) also need the Inspiro Starter Sites plugin.', 'wpzoom-ai-connect' );
		}
		if ( ! $out['connected'] ) {
			$out['how'] = __( 'Connecting is free: call wpzoom/connect-ai with the user\'s email (ask for consent to receive the code), then wpzoom/verify-ai-code with the 6-digit code they receive. Inspiro Premium sites are connected through their license automatically.', 'wpzoom-ai-connect' );
		}
		return $out;
	}

	public static function connect_ai( array $in ) {
		$email = sanitize_email( (string) ( $in['email'] ?? '' ) );
		if ( ! is_email( $email ) ) {
			return new WP_Error( 'wpzoom_invalid_email', __( 'A valid email is required.', 'wpzoom-ai-connect' ) );
		}
		$consent = ! empty( $in['consent'] );
		$result  = self::ai_available() ? self::proxy()->connect( $email, $consent ) : WPZOOM_AI_Catalog::connect( $email, $consent );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		if ( is_array( $result ) && empty( $result['pending'] ) ) {
			return array(
				'email' => $email,
				'next'  => __( 'The site is connected; no code is needed. wpzoom/list-sections works now.', 'wpzoom-ai-connect' ),
				'raw'   => $result,
			);
		}
		return array(
			'email' => $email,
			'next'  => __( 'A 6-digit code was emailed to the user. Ask for it and call wpzoom/verify-ai-code.', 'wpzoom-ai-connect' ),
			'raw'   => $result,
		);
	}

	public static function verify_ai_code( array $in ) {
		$code = preg_replace( '/\D+/', '', (string) ( $in['code'] ?? '' ) );
		if ( 6 !== strlen( $code ) ) {
			return new WP_Error( 'wpzoom_invalid_code', __( 'The code is 6 digits.', 'wpzoom-ai-connect' ) );
		}
		$result = self::ai_available() ? self::proxy()->verify( $code ) : WPZOOM_AI_Catalog::verify( $code );
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		// Starter Sites' client stored the key; the plugin's cached "connect first" answer no longer applies.
		delete_transient( WPZOOM_AI_Catalog::DOWN_TRANSIENT );
		return self::ai_status();
	}

	/* ---------- section catalog ---------- */

	private static function builder() {
		return \Inspiro\Starter_Sites\Ai\AiPageBuilder::get_instance();
	}

	/**
	 * The catalog index with what the site satisfies, through Starter Sites when it is active (its cache and
	 * stylesheet) and through the plugin's own client otherwise.
	 *
	 * @return array|WP_Error [ 'sections' => array[], 'site_has' => string[], 'licensed' => bool ]
	 */
	private static function catalog_index() {
		if ( self::ai_available() ) {
			$builder  = self::builder();
			$sections = (array) $builder->catalog_index();
			$site_has = (array) $builder->site_has();
		} else {
			$sections = WPZOOM_AI_Sections::index();
			$site_has = WPZOOM_AI_Sections::site_has();
		}
		if ( ! $sections ) {
			return self::catalog_error();
		}
		return array(
			'sections' => array_values( $sections ),
			'site_has' => array_values( $site_has ),
			'licensed' => '' !== WPZOOM_AI_Catalog::premium_license(),
		);
	}

	/**
	 * Why the catalog is empty: the proxy wants the site's email connected first (`registration_required`, with
	 * next_step), or ai.wpzoom.com could not be reached.
	 */
	private static function catalog_error(): WP_Error {
		if ( self::ai_available() ) {
			// Starter Sites' client does not say why; an unconnected, unlicensed site is the one case the proxy refuses.
			$unregistered = '' === WPZOOM_AI_Catalog::premium_license() && ! self::connected();
		} else {
			$unregistered = 'registration_required' === WPZOOM_AI_Catalog::last_error();
		}
		if ( $unregistered ) {
			return new WP_Error(
				'registration_required',
				__( 'The section catalog needs this site connected to WPZOOM AI first (free). Offer the user to connect: call wpzoom/connect-ai with their email (ask for consent to receive a verification code), then wpzoom/verify-ai-code with the 6-digit code they receive. Inspiro Premium sites are connected through their license. wpzoom/ai-status shows the current state.', 'wpzoom-ai-connect' ),
				array(
					'next_step' => 'wpzoom/connect-ai',
					'then'      => 'wpzoom/verify-ai-code',
					'status'    => 403,
				)
			);
		}
		return new WP_Error( 'wpzoom_catalog_unavailable', __( 'The section catalog could not be loaded from ai.wpzoom.com right now. Try again in a few minutes.', 'wpzoom-ai-connect' ), array( 'status' => 503 ) );
	}

	public static function list_sections( array $in ) {
		$index = self::catalog_index();
		if ( is_wp_error( $index ) ) {
			if ( 'registration_required' === $index->get_error_code() ) {
				// Nothing is known about the catalog before the first fetch; say what unlocks it.
				return array(
					'count'     => 0,
					'roles'     => array(),
					'site_has'  => self::ai_available() ? array_values( (array) self::builder()->site_has() ) : WPZOOM_AI_Sections::site_has(),
					'licensed'  => false,
					'connected' => false,
					'sections'  => array(),
					'next_step' => 'wpzoom/connect-ai',
					'how'       => $index->get_error_message(),
				);
			}
			return $index;
		}
		$sections = $index['sections'];
		$search   = isset( $in['search'] ) ? strtolower( trim( (string) $in['search'] ) ) : '';
		$role     = isset( $in['role'] ) ? sanitize_key( (string) $in['role'] ) : '';
		$out      = array();
		foreach ( $sections as $section ) {
			if ( $role && $role !== $section['role'] ) {
				continue;
			}
			if ( $search && false === strpos( strtolower( $section['id'] . ' ' . $section['role'] . ' ' . $section['description'] ), $search ) ) {
				continue;
			}
			if ( empty( $in['include_unavailable'] ) && empty( $section['available'] ) ) {
				continue;
			}
			$out[] = $section;
		}
		$roles = array_count_values( array_column( $sections, 'role' ) );
		ksort( $roles );
		$how = self::ai_available()
			? __( 'wpzoom/insert-sections adds sections with the demo\'s example copy instantly (no AI). wpzoom/plan-page and wpzoom/build-page write the copy with AI for the user\'s business. Locked sections need Inspiro Premium.', 'wpzoom-ai-connect' )
			: __( 'wpzoom/insert-sections adds sections with the demo\'s example copy instantly (no AI); then adapt the text with the page tools. Locked sections need Inspiro Premium; sections with available: false name what the site lacks in needs. AI-written pages (wpzoom/plan-page, wpzoom/build-page) need the Inspiro Starter Sites plugin.', 'wpzoom-ai-connect' );
		return array(
			'count'     => count( $out ),
			'roles'     => $roles,
			'site_has'  => $index['site_has'],
			'licensed'  => $index['licensed'],
			'connected' => $index['licensed'] || self::connected(),
			'sections'  => $out,
			'how'       => $how,
		);
	}

	/** Creates or extends a page with block markup and marks it so the sections' stylesheet loads. */
	private static function place_markup( array $in, string $markup, string $default_title ) {
		$page_id = isset( $in['page_id'] ) ? (int) $in['page_id'] : 0;
		$created = false;
		if ( $page_id ) {
			$post = get_post( $page_id );
			if ( ! $post || ! current_user_can( 'edit_post', $page_id ) ) {
				return new WP_Error( 'wpzoom_page_not_found', sprintf( /* translators: %d: page ID */ __( 'No editable page with id %d.', 'wpzoom-ai-connect' ), $page_id ) );
			}
			$existing = trim( (string) $post->post_content );
			$position = ( isset( $in['position'] ) && 'prepend' === $in['position'] ) ? 'prepend' : 'append';
			$content  = '' === $existing ? $markup : ( 'prepend' === $position ? $markup . "\n\n" . $existing : $existing . "\n\n" . $markup );
			$result   = wp_update_post( array( 'ID' => $page_id, 'post_content' => wp_slash( $content ) ), true );
		} else {
			$title   = sanitize_text_field( (string) ( $in['new_page_title'] ?? $default_title ) ) ?: $default_title;
			$result  = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'draft', 'post_title' => $title, 'post_content' => wp_slash( $markup ) ), true );
			$page_id = is_wp_error( $result ) ? 0 : (int) $result;
			$created = true;
		}
		if ( is_wp_error( $result ) ) {
			return $result;
		}
		WPZOOM_AI_Sections::mark_page( $page_id );
		$post = get_post( $page_id );
		return array(
			'page_id'     => $page_id,
			'title'       => $post->post_title,
			'status'      => $post->post_status,
			'created'     => $created,
			'edit_url'    => get_edit_post_link( $page_id, 'raw' ),
			'preview_url' => 'publish' === $post->post_status ? get_permalink( $page_id ) : get_preview_post_link( $page_id ),
		);
	}

	/**
	 * Renders the sections through Starter Sites' builder (when active) or the plugin's own code, and explains
	 * every id that did not render.
	 *
	 * @return array|WP_Error { markup: id => html, skipped: id => reason, placeholders: ids, site_media: ids }
	 */
	private static function render_sections( array $ids, array $headlines ) {
		if ( ! self::ai_available() ) {
			$index = self::catalog_index();
			if ( is_wp_error( $index ) ) {
				return $index;
			}
			return WPZOOM_AI_Sections::render( $ids, $headlines );
		}
		$index = self::catalog_index();
		if ( is_wp_error( $index ) ) {
			return $index;
		}
		$meta = array();
		foreach ( $index['sections'] as $section ) {
			$meta[ $section['id'] ] = $section;
		}
		$rendered = (array) self::builder()->preview_markup( $ids, $headlines );
		$out      = array(
			'markup'       => array(),
			'skipped'      => array(),
			'placeholders' => array(),
			'site_media'   => array(),
		);
		foreach ( $ids as $id ) {
			if ( isset( $rendered[ $id ] ) && '' !== trim( (string) $rendered[ $id ] ) ) {
				$out['markup'][ $id ] = (string) $rendered[ $id ];
				if ( false !== strpos( $out['markup'][ $id ], 'picsum.photos' ) ) {
					$out['placeholders'][] = $id;
				}
				continue;
			}
			if ( ! isset( $meta[ $id ] ) ) {
				$out['skipped'][ $id ] = __( 'unknown section id (not in wpzoom/list-sections)', 'wpzoom-ai-connect' );
			} elseif ( ! empty( $meta[ $id ]['locked'] ) ) {
				$out['skipped'][ $id ] = __( 'a premium section: it needs an active Inspiro Premium license on this site', 'wpzoom-ai-connect' );
			} elseif ( ! empty( $meta[ $id ]['needs'] ) && array_diff( (array) $meta[ $id ]['needs'], $index['site_has'] ) ) {
				$out['skipped'][ $id ] = sprintf( /* translators: %s: what the section needs, e.g. "the Icon Block plugin"; several are separated by semicolons */ __( 'needs %s', 'wpzoom-ai-connect' ), implode( '; ', array_map( array( 'WPZOOM_AI_Sections', 'describe_need' ), array_diff( (array) $meta[ $id ]['needs'], $index['site_has'] ) ) ) );
			} else {
				$out['skipped'][ $id ] = __( 'the section could not be fetched from ai.wpzoom.com right now', 'wpzoom-ai-connect' );
			}
		}
		return $out;
	}

	public static function insert_sections( array $in ) {
		$ids       = array();
		$headlines = array();
		foreach ( (array) ( $in['sections'] ?? array() ) as $item ) {
			$id = is_array( $item ) ? (string) ( $item['id'] ?? '' ) : (string) $item;
			$id = sanitize_key( $id );
			if ( '' === $id ) {
				continue;
			}
			$ids[] = $id;
			if ( is_array( $item ) && ! empty( $item['headline'] ) ) {
				$headlines[ $id ] = sanitize_text_field( (string) $item['headline'] );
			}
		}
		$ids = array_values( array_unique( $ids ) );
		if ( ! $ids ) {
			return new WP_Error( 'wpzoom_no_sections', __( 'Send at least one section id from wpzoom/list-sections.', 'wpzoom-ai-connect' ) );
		}
		$rendered = self::render_sections( $ids, $headlines );
		if ( is_wp_error( $rendered ) ) {
			return $rendered;
		}
		if ( ! $rendered['markup'] ) {
			$reasons = array();
			foreach ( $rendered['skipped'] as $id => $reason ) {
				$reasons[] = $id . ': ' . $reason;
			}
			return new WP_Error( 'wpzoom_sections_unavailable', sprintf( /* translators: %s: each section id with the reason it was skipped */ __( 'None of these sections could be inserted. %s', 'wpzoom-ai-connect' ), implode( '. ', $reasons ) ), array( 'skipped_reasons' => $rendered['skipped'] ) );
		}
		$markup = array();
		foreach ( $ids as $id ) {
			if ( isset( $rendered['markup'][ $id ] ) ) {
				$markup[] = trim( (string) $rendered['markup'][ $id ] );
			}
		}
		$placed = self::place_markup( $in, implode( "\n\n", $markup ), __( 'New page', 'wpzoom-ai-connect' ) );
		if ( is_wp_error( $placed ) ) {
			return $placed;
		}
		$placed['sections_added']   = array_values( array_intersect( $ids, array_keys( $rendered['markup'] ) ) );
		$placed['sections_skipped'] = array_values( array_keys( $rendered['skipped'] ) );
		$placed['skipped_reasons']  = $rendered['skipped'];
		$placed['placeholders']     = array_values( $rendered['placeholders'] );
		$note                       = __( 'The sections carry the demo\'s example copy: rewrite the text for the user\'s business with the page tools.', 'wpzoom-ai-connect' );
		if ( $rendered['placeholders'] ) {
			$note .= ' ' . sprintf(
				/* translators: %s: section ids. */
				__( 'The photos in %s are neutral placeholder images (the site had no photos of that shape): ask the user for their own photos, or search stock photos, and swap them in the editor.', 'wpzoom-ai-connect' ),
				implode( ', ', $rendered['placeholders'] )
			);
		} elseif ( ! empty( $rendered['site_media'] ) ) {
			$note .= ' ' . __( 'The photos were taken from the site\'s own Media Library (the newest ones): check they fit, and swap any that do not.', 'wpzoom-ai-connect' );
		}
		if ( self::ai_available() ) {
			$note .= ' ' . __( 'wpzoom/build-page can write the copy with AI instead.', 'wpzoom-ai-connect' );
		}
		$placed['note'] = $note;
		return $placed;
	}

	private static function ai_error( WP_Error $error ): WP_Error {
		if ( 'ai_registration_required' === $error->get_error_code() ) {
			return new WP_Error( $error->get_error_code(), __( 'This site is not connected to WPZOOM AI yet. Call wpzoom/connect-ai with the user\'s email (with their consent), then wpzoom/verify-ai-code.', 'wpzoom-ai-connect' ) );
		}
		return $error;
	}

	public static function plan_page( array $in ) {
		if ( ! self::ai_available() ) {
			return self::unavailable( __( 'AI page planning', 'wpzoom-ai-connect' ) );
		}
		$plan = self::builder()->plan(
			array(
				'description' => (string) ( $in['description'] ?? '' ),
				'page_type'   => (string) ( $in['page_type'] ?? 'auto' ),
				'style'       => (string) ( $in['style'] ?? '' ),
				'pinned'      => (array) ( $in['pinned'] ?? array() ),
				'avoid'       => (array) ( $in['avoid'] ?? array() ),
			),
			null
		);
		if ( is_wp_error( $plan ) ) {
			return self::ai_error( $plan );
		}
		return array(
			'plan' => $plan,
			'next' => __( 'Show the user the page title and the section list. When they agree, call wpzoom/build-page with this plan unchanged (or with sections removed). Building uses one AI generation from the site\'s quota.', 'wpzoom-ai-connect' ),
		);
	}

	public static function build_page( array $in ) {
		if ( ! self::ai_available() ) {
			return self::unavailable( __( 'AI page building', 'wpzoom-ai-connect' ) );
		}
		$plan = isset( $in['plan'] ) && is_array( $in['plan'] ) ? $in['plan'] : null;
		if ( ! $plan ) {
			if ( empty( $in['description'] ) ) {
				return new WP_Error( 'wpzoom_missing_plan', __( 'Send the plan from wpzoom/plan-page, or a description to plan and build in one go.', 'wpzoom-ai-connect' ) );
			}
			$planned = self::plan_page( $in );
			if ( is_wp_error( $planned ) ) {
				return $planned;
			}
			$plan = $planned['plan'];
		}
		if ( ! current_user_can( 'edit_pages' ) ) {
			return new WP_Error( 'wpzoom_forbidden', __( 'The connected user may not create pages.', 'wpzoom-ai-connect' ) );
		}
		$job = WPZOOM_AI_Jobs::create(
			self::JOB_BUILD,
			array(
				'plan'           => $plan,
				'page_id'        => isset( $in['page_id'] ) ? (int) $in['page_id'] : 0,
				'new_page_title' => isset( $in['new_page_title'] ) ? sanitize_text_field( (string) $in['new_page_title'] ) : '',
				'position'       => (string) ( $in['position'] ?? 'append' ),
			),
			sprintf( /* translators: %s: page title */ __( 'Build page "%s" with AI', 'wpzoom-ai-connect' ), (string) ( $plan['title'] ?? __( 'Page', 'wpzoom-ai-connect' ) ) )
		);
		return array(
			'job'  => WPZOOM_AI_Jobs::describe( $job ),
			'plan' => array( 'title' => $plan['title'] ?? '', 'sections' => array_column( (array) ( $plan['sections'] ?? array() ), 'id' ) ),
			'note' => __( 'The AI writes the copy and the page is built in the background (one to three minutes). Poll wpzoom/job-status with this job id every 20 seconds; the result carries the page link.', 'wpzoom-ai-connect' ),
		);
	}

	/** Background job: consume quota, build, create the page, refund on failure. */
	public static function run_build( array $args, callable $progress ) {
		if ( ! self::ai_available() ) {
			return self::unavailable( __( 'AI page building', 'wpzoom-ai-connect' ) );
		}
		$proxy   = self::proxy();
		$feature = \Inspiro\Starter_Sites\Ai\AiPageBuilder::FEATURE;
		$progress( 'quota', __( 'Checking the AI quota', 'wpzoom-ai-connect' ), 5 );
		$quota = $proxy->quota( 'consume', $feature );
		if ( is_wp_error( $quota ) ) {
			return self::ai_error( $quota );
		}
		if ( is_array( $quota ) && isset( $quota['allowed'] ) && ! $quota['allowed'] ) {
			/* translators: 1: generations used, 2: the free quota limit. */
			return new WP_Error( 'quota_exhausted', sprintf( __( 'The free AI quota is used up (%1$s of %2$s). Inspiro Premium raises it, and AI Credits top it up at wpzoom.com.', 'wpzoom-ai-connect' ), $quota['used'] ?? '?', $quota['limit'] ?? '?' ) );
		}

		$progress( 'writing', __( 'The AI is writing the sections', 'wpzoom-ai-connect' ), 20 );
		$tick   = static function () use ( $progress ) {
			$progress( 'writing', '' );
		};
		$result = self::builder()->build( (array) $args['plan'], $tick );
		if ( is_wp_error( $result ) ) {
			$proxy->quota( 'refund', $feature );
			return self::ai_error( $result );
		}

		$progress( 'page', __( 'Creating the page', 'wpzoom-ai-connect' ), 90 );
		$placed = self::place_markup(
			array( 'page_id' => $args['page_id'], 'new_page_title' => $args['new_page_title'] ?: ( $result['title'] ?? __( 'Page', 'wpzoom-ai-connect' ) ), 'position' => $args['position'] ),
			(string) $result['markup'],
			(string) ( $result['title'] ?? __( 'Page', 'wpzoom-ai-connect' ) )
		);
		if ( is_wp_error( $placed ) ) {
			return $placed;
		}
		$placed['sections'] = $result['sections'] ?? array();
		$placed['images']   = count( (array) ( $result['images'] ?? array() ) );
		$placed['quota']    = is_array( $quota ) ? array_intersect_key( $quota, array_flip( array( 'used', 'limit', 'remaining' ) ) ) : null;
		$placed['next']     = __( 'The page is a draft. Review the preview with the user, adjust wording with the page tools, then publish.', 'wpzoom-ai-connect' );
		return $placed;
	}
}
