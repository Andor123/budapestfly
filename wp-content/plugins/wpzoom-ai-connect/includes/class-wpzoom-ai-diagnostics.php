<?php
/**
 * License status and theme-aware diagnostics: the checks a WPZOOM support engineer runs first.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Diagnostics {

	private static function is_wpzoom_theme(): bool {
		$theme  = wp_get_theme();
		$parent = $theme->parent();
		return false !== stripos( (string) $theme->get( 'Author' ), 'wpzoom' ) || ( $parent && false !== stripos( (string) $parent->get( 'Author' ), 'wpzoom' ) );
	}

	/** Premium WPZOOM themes built on the framework store the license under the template slug. */
	private static function is_premium_framework_theme(): bool {
		return class_exists( 'WPZOOM' ) || defined( 'WPZOOM_INC' );
	}

	public static function license_status(): array {
		$theme    = wp_get_theme();
		$template = get_template();
		$out      = array(
			'theme'     => $theme->get( 'Name' ),
			'version'   => $theme->get( 'Version' ),
			'is_wpzoom' => self::is_wpzoom_theme(),
		);
		if ( ! self::is_premium_framework_theme() ) {
			$out['license'] = 'none';
			$out['note']    = self::is_wpzoom_theme()
				? __( 'This is a free WPZOOM theme: no license is needed. Premium features and support come with the premium version.', 'wpzoom-ai-connect' )
				: __( 'The active theme is not a WPZOOM theme.', 'wpzoom-ai-connect' );
			return $out;
		}
		$key    = (string) get_option( $template . '_license_key', '' );
		$status = (string) get_option( $template . '_license_key_status', 'inactive' );
		$last   = get_option( $template . '_license_last_check' );
		$out['license']       = $status ?: 'inactive';
		$out['key_present']   = '' !== $key;
		$out['key_hint']      = $key ? '…' . substr( $key, -4 ) : '';
		$out['license_valid'] = 'valid' === $status;
		if ( is_array( $last ) ) {
			foreach ( array( 'expires', 'site_count', 'license_limit', 'message' ) as $field ) {
				if ( isset( $last[ $field ] ) && '' !== $last[ $field ] ) {
					$out[ $field ] = $last[ $field ];
				}
			}
			if ( ! empty( $last['time'] ) ) {
				$out['last_checked'] = gmdate( 'c', (int) $last['time'] );
			}
		}
		$out['theme_update_status'] = (string) get_option( 'wpzoom_theme_status', '' ) ?: 'unknown';
		$out['manage_url']          = admin_url( 'admin.php?page=wpzoom_license#license' );
		$out['renew_url']           = 'https://www.wpzoom.com/account/licenses/';
		$meaning = array(
			'valid'              => __( 'Active: updates and premium features work.', 'wpzoom-ai-connect' ),
			'expired'            => __( 'Expired: the theme keeps working but gets no updates; renew at wpzoom.com.', 'wpzoom-ai-connect' ),
			'inactive'           => __( 'No license activated on this site yet.', 'wpzoom-ai-connect' ),
			'site_inactive'      => __( 'The key exists but is not activated for this site URL.', 'wpzoom-ai-connect' ),
			'invalid'            => __( 'The key is not valid for this theme.', 'wpzoom-ai-connect' ),
			'disabled'           => __( 'The license was disabled.', 'wpzoom-ai-connect' ),
			'item_name_mismatch' => __( 'The key belongs to a different product.', 'wpzoom-ai-connect' ),
		);
		$out['meaning'] = $meaning[ $status ] ?? __( 'Unknown status.', 'wpzoom-ai-connect' );
		return $out;
	}

	/**
	 * @return array { summary: array, checks: array }
	 */
	public static function diagnose(): array {
		$checks = array();
		$add    = static function ( string $id, string $status, string $title, string $detail = '', string $fix = '' ) use ( &$checks ) {
			$check = compact( 'id', 'status', 'title', 'detail' );
			if ( $fix ) {
				$check['fix'] = $fix;
			}
			$checks[] = $check;
		};

		$theme = wp_get_theme();
		/* translators: 1: theme name, 2: theme version */
		$add( 'theme', self::is_wpzoom_theme() ? 'ok' : 'info', sprintf( __( 'Active theme: %1$s %2$s', 'wpzoom-ai-connect' ), $theme->get( 'Name' ), $theme->get( 'Version' ) ), self::is_wpzoom_theme() ? '' : __( 'Not a WPZOOM theme; theme-specific checks are skipped.', 'wpzoom-ai-connect' ) );

		// License.
		if ( self::is_premium_framework_theme() ) {
			$license = self::license_status();
			$add(
				'license',
				$license['license_valid'] ? 'ok' : 'warn',
				/* translators: %s: license status, like valid or expired */
				sprintf( __( 'Theme license: %s', 'wpzoom-ai-connect' ), $license['license'] ),
				$license['meaning'],
				/* translators: %s: URL of the theme's License page */
				$license['license_valid'] ? '' : sprintf( __( 'Activate or renew the license on the theme\'s License page (%s).', 'wpzoom-ai-connect' ), $license['manage_url'] )
			);
			$update = (string) get_option( 'wpzoom_theme_status', '' );
			if ( 'needs_update' === $update ) {
				$add( 'theme-update', 'warn', __( 'A theme update is available', 'wpzoom-ai-connect' ), __( 'The theme reports a newer version.', 'wpzoom-ai-connect' ), __( 'Update the theme from Appearance → Themes (needs a valid license).', 'wpzoom-ai-connect' ) );
			}
		}

		// HTTPS and permalinks.
		$home = home_url( '/' );
		$add( 'https', 0 === strpos( $home, 'https://' ) ? 'ok' : 'warn', __( 'Site URL uses HTTPS', 'wpzoom-ai-connect' ), $home, 0 === strpos( $home, 'https://' ) ? '' : __( 'Serve the site over HTTPS; application passwords and most AI connectors need it.', 'wpzoom-ai-connect' ) );
		$permalinks = (string) get_option( 'permalink_structure' );
		$add( 'permalinks', $permalinks ? 'ok' : 'warn', __( 'Pretty permalinks', 'wpzoom-ai-connect' ), $permalinks ?: __( 'Plain permalinks (?p=123)', 'wpzoom-ai-connect' ), $permalinks ? '' : __( 'Settings → Permalinks → choose "Post name".', 'wpzoom-ai-connect' ) );

		// Front page.
		$show_on_front = get_option( 'show_on_front' );
		$front_id      = (int) get_option( 'page_on_front' );
		if ( 'page' === $show_on_front && $front_id && get_post( $front_id ) ) {
			$add(
				'front-page',
				'ok',
				/* translators: %s: title of the front page */
				sprintf( __( 'Static front page: %s', 'wpzoom-ai-connect' ), get_the_title( $front_id ) ),
				/* translators: 1: page ID, 2: page template slug, or "default" */
				sprintf( __( 'Page %1$d, template: %2$s', 'wpzoom-ai-connect' ), $front_id, get_page_template_slug( $front_id ) ?: 'default' )
			);
		} else {
			$add( 'front-page', self::is_wpzoom_theme() ? 'warn' : 'info', __( 'Front page shows latest posts', 'wpzoom-ai-connect' ), __( 'WPZOOM demos use a static homepage.', 'wpzoom-ai-connect' ), __( 'Settings → Reading → "A static page" and pick the homepage, or import a starter site.', 'wpzoom-ai-connect' ) );
		}

		// Homepage slider (Inspiro Premium).
		if ( post_type_exists( 'slider' ) ) {
			$enabled = (bool) get_theme_mod( 'featured_posts_show', true );
			$slides  = (int) ( wp_count_posts( 'slider' )->publish ?? 0 );
			if ( $enabled && 0 === $slides ) {
				$add( 'slider', 'warn', __( 'Homepage slideshow is on but has no slides', 'wpzoom-ai-connect' ), __( 'Theme setting featured_posts_show is on; 0 published slides.', 'wpzoom-ai-connect' ), __( 'Add slides under Slider → Add New (with a featured image), or turn the slideshow off with wpzoom/update-theme-settings {"featured_posts_show": false}.', 'wpzoom-ai-connect' ) );
			} elseif ( $enabled ) {
				$without = get_posts( array( 'post_type' => 'slider', 'post_status' => 'publish', 'posts_per_page' => 50, 'fields' => 'ids', 'meta_query' => array( array( 'key' => '_thumbnail_id', 'compare' => 'NOT EXISTS' ) ) ) ); // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- support diagnostic, at most 50 slider posts.
				$add(
					'slider',
					$without ? 'warn' : 'ok',
					/* translators: %d: number of slides */
					sprintf( _n( 'Homepage slideshow: %d slide', 'Homepage slideshow: %d slides', $slides, 'wpzoom-ai-connect' ), $slides ),
					/* translators: %d: number of slides without a featured image */
					$without ? sprintf( _n( '%d slide has no featured image and renders empty.', '%d slides have no featured image and render empty.', count( $without ), 'wpzoom-ai-connect' ), count( $without ) ) : '',
					/* translators: %s: comma-separated slide IDs */
					$without ? sprintf( __( 'Set a featured image on slides %s.', 'wpzoom-ai-connect' ), implode( ', ', $without ) ) : ''
				);
			} else {
				/* translators: %d: number of slides */
				$add( 'slider', 'info', __( 'Homepage slideshow is off', 'wpzoom-ai-connect' ), sprintf( _n( '%d slide exists.', '%d slides exist.', $slides, 'wpzoom-ai-connect' ), $slides ) );
			}
		}

		// Menus.
		$locations = get_registered_nav_menus();
		$assigned  = get_nav_menu_locations();
		$missing   = array();
		foreach ( $locations as $slug => $label ) {
			if ( empty( $assigned[ $slug ] ) ) {
				$missing[] = sprintf( '%s (%s)', wp_strip_all_tags( $label ), $slug );
			}
		}
		if ( $locations ) {
			$primary_ok = ! empty( $assigned['primary'] );
			$add(
				'menus',
				$primary_ok ? 'ok' : 'warn',
				$primary_ok ? __( 'Main menu assigned', 'wpzoom-ai-connect' ) : __( 'No menu assigned to the main location', 'wpzoom-ai-connect' ),
				/* translators: %s: comma-separated menu locations */
				$missing ? sprintf( __( 'Unassigned locations: %s', 'wpzoom-ai-connect' ), implode( ', ', $missing ) ) : __( 'All locations have a menu.', 'wpzoom-ai-connect' ),
				$primary_ok ? '' : __( 'Appearance → Menus → Manage Locations, assign a menu to "primary".', 'wpzoom-ai-connect' )
			);
		}

		// Plugins the theme wants.
		$inactive_required = array();
		$inactive_recommended = array();
		if ( class_exists( 'WPZOOM_TGMPA_Assistance' ) && method_exists( 'WPZOOM_TGMPA_Assistance', 'get_instance' ) ) {
			// Some framework versions emit notices while listing plugins; keep them out of the JSON response.
			set_error_handler( static function () { return true; }, E_WARNING | E_NOTICE | E_DEPRECATED ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
			ob_start();
			try {
				$assist = WPZOOM_TGMPA_Assistance::get_instance();
				foreach ( (array) $assist->get_inactive_plugins() as $slug => $plugin ) {
					$name = is_array( $plugin ) ? ( $plugin['name'] ?? $slug ) : $slug;
					if ( method_exists( $assist, 'is_recommended_plugin' ) && $assist->is_recommended_plugin( $slug ) ) {
						$inactive_recommended[] = $name;
					} else {
						$inactive_required[] = $name;
					}
				}
			} catch ( Throwable $e ) {
				// The framework class signature changed; skip this check rather than fail.
			}
			ob_end_clean();
			restore_error_handler();
		}
		if ( $inactive_required ) {
			$add( 'plugins-required', 'warn', __( 'Plugins the theme needs are not active', 'wpzoom-ai-connect' ), implode( ', ', $inactive_required ), __( 'Install and activate them on the theme\'s Plugins page.', 'wpzoom-ai-connect' ) );
		} elseif ( $inactive_recommended ) {
			$add( 'plugins-recommended', 'info', __( 'Recommended plugins not active', 'wpzoom-ai-connect' ), implode( ', ', $inactive_recommended ) );
		} elseif ( class_exists( 'WPZOOM_TGMPA_Assistance' ) ) {
			$add( 'plugins', 'ok', __( 'All plugins the theme asks for are active', 'wpzoom-ai-connect' ) );
		}

		// Demo state.
		$demo = get_theme_mod( 'wpz_demo_imported' ) ?: get_option( 'inspiro_starter_sites_imported_demo_id' );
		/* translators: %s: demo ID */
		$add( 'demo', 'info', $demo ? sprintf( __( 'Imported demo: %s', 'wpzoom-ai-connect' ), $demo ) : __( 'No demo imported', 'wpzoom-ai-connect' ), $demo ? '' : __( 'Starter sites give a complete homepage in one step (wpzoom/list-demos).', 'wpzoom-ai-connect' ) );

		// Content volume.
		$pages = (int) ( wp_count_posts( 'page' )->publish ?? 0 );
		$posts = (int) ( wp_count_posts( 'post' )->publish ?? 0 );
		/* translators: 1: number of published pages, 2: number of published posts */
		$add( 'content', 'info', sprintf( __( 'Published pages: %1$d, published posts: %2$d', 'wpzoom-ai-connect' ), $pages, $posts ) );

		// Updates WordPress already knows about for the active theme and plugins (no network call here).
		if ( class_exists( 'WPZOOM_AI_Updates' ) ) {
			$outdated = WPZOOM_AI_Updates::outdated();
			$add( 'updates', $outdated ? 'warn' : 'ok', $outdated ? sprintf( /* translators: %d: number of themes and plugins with an update */ _n( '%d update available for what the site runs', '%d updates available for what the site runs', count( $outdated ), 'wpzoom-ai-connect' ), count( $outdated ) ) : __( 'Active theme and plugins are up to date', 'wpzoom-ai-connect' ), $outdated ? implode( '; ', $outdated ) : '', $outdated ? __( 'Update them in Dashboard → Updates. Many support issues are already fixed in newer versions.', 'wpzoom-ai-connect' ) : '' );
		}

		// PHP version: 8.1 and older no longer get security fixes.
		/* translators: %s: PHP version */
		$add( 'php', version_compare( PHP_VERSION, '8.2', '<' ) ? 'warn' : 'ok', sprintf( __( 'PHP %s', 'wpzoom-ai-connect' ), PHP_VERSION ), version_compare( PHP_VERSION, '8.2', '<' ) ? __( 'This PHP version no longer receives security updates.', 'wpzoom-ai-connect' ) : '', version_compare( PHP_VERSION, '8.2', '<' ) ? __( 'Ask the host to switch the site to PHP 8.3 or newer.', 'wpzoom-ai-connect' ) : '' );

		// WP-Cron, needed for background jobs (demo import, AI builds).
		$cron_disabled = defined( 'DISABLE_WP_CRON' ) && DISABLE_WP_CRON;
		$add( 'cron', $cron_disabled ? 'info' : 'ok', $cron_disabled ? __( 'WP-Cron is disabled (system cron expected)', 'wpzoom-ai-connect' ) : __( 'WP-Cron enabled', 'wpzoom-ai-connect' ), $cron_disabled ? __( 'Background jobs start when the system cron hits wp-cron.php.', 'wpzoom-ai-connect' ) : '' );

		// Application passwords (what hosted connectors use).
		$app = function_exists( 'wp_is_application_passwords_available' ) && wp_is_application_passwords_available();
		$add( 'app-passwords', $app ? 'ok' : 'warn', $app ? __( 'Application passwords available', 'wpzoom-ai-connect' ) : __( 'Application passwords unavailable', 'wpzoom-ai-connect' ), $app ? '' : __( 'Usually because the site is not on HTTPS.', 'wpzoom-ai-connect' ), $app ? '' : __( 'Enable HTTPS, or use a plugin or the wp_is_application_passwords_available filter.', 'wpzoom-ai-connect' ) );

		$counts = array_count_values( array_column( $checks, 'status' ) );
		return array(
			'summary' => array(
				'ok'       => $counts['ok'] ?? 0,
				'warnings' => $counts['warn'] ?? 0,
				'info'     => $counts['info'] ?? 0,
				/* translators: %d: number of checks that need attention. */
				'verdict'  => empty( $counts['warn'] ) ? __( 'Nothing needs attention.', 'wpzoom-ai-connect' ) : sprintf( _n( '%d item needs attention.', '%d items need attention.', $counts['warn'], 'wpzoom-ai-connect' ), $counts['warn'] ),
			),
			'checks'  => $checks,
		);
	}
}
