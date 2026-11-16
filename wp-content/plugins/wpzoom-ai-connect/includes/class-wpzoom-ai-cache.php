<?php
/**
 * Clears page caches, so a change an AI makes shows up for visitors at once instead of after the cache
 * expires (otherwise users on WP Rocket, LiteSpeed and similar think the AI failed).
 *
 * Runs on its own after every successful write request made through the WPZOOM AI Connect application
 * password, and on request through the wpzoom/clear-cache ability. Covers the common caching plugins and
 * the hosts that cache pages themselves; each is called only when it is installed.
 * Turn the automatic clearing off with: add_filter( 'wpzoom_ai_connect_clear_cache', '__return_false' );
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Cache {

	/** Whether this request already cleared the caches. */
	private static $cleared = false;

	public static function init() {
		add_filter( 'rest_post_dispatch', array( __CLASS__, 'after_rest_write' ), 10, 3 );
	}

	/** Whether the current request is authenticated with the WPZOOM AI Connect application password. */
	public static function from_ai_connect(): bool {
		return WPZOOM_AI_Scope::is_connector_request();
	}

	/** After a successful POST, PUT, PATCH or DELETE from the connector, clears the caches once. */
	public static function after_rest_write( $response, $server, $request ) {
		if ( self::$cleared || ! ( $request instanceof WP_REST_Request ) || 'GET' === $request->get_method() || 'HEAD' === $request->get_method() ) {
			return $response;
		}
		if ( ! ( $response instanceof WP_REST_Response ) || $response->is_error() || $response->get_status() >= 400 ) {
			return $response;
		}
		if ( ! self::from_ai_connect() || ! apply_filters( 'wpzoom_ai_connect_clear_cache', true, $request ) ) {
			return $response;
		}
		$cleared = self::clear();
		if ( $cleared ) {
			$response->header( 'X-WPZOOM-AI-Cache-Cleared', implode( ', ', $cleared ) );
		}
		return $response;
	}

	/**
	 * Clears every page cache found on the site. Returns the names of the caches cleared.
	 *
	 * @param bool $elementor_css Also regenerate Elementor's CSS files for every page (Elementor → Tools → Regenerate CSS).
	 */
	public static function clear( bool $elementor_css = false ): array {
		self::$cleared = true;
		$done          = array();
		$try           = static function ( string $name, callable $work ) use ( &$done ) {
			try {
				$work();
				$done[] = $name;
			} catch ( \Throwable $e ) {
				// one cache failing does not stop the others
			}
		};

		if ( function_exists( 'rocket_clean_domain' ) ) {
			$try( 'WP Rocket', static function () {
				rocket_clean_domain();
				if ( function_exists( 'rocket_clean_minify' ) ) {
					rocket_clean_minify();
				}
			} );
		}
		if ( defined( 'LSCWP_V' ) || class_exists( 'LiteSpeed\Core' ) ) {
			$try( 'LiteSpeed Cache', static function () {
				do_action( 'litespeed_purge_all' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- LiteSpeed Cache's own hook.
			} );
		}
		if ( function_exists( 'w3tc_flush_all' ) ) {
			$try( 'W3 Total Cache', 'w3tc_flush_all' );
		}
		if ( function_exists( 'wp_cache_clear_cache' ) ) {
			$try( 'WP Super Cache', static function () {
				wp_cache_clear_cache( get_current_blog_id() );
			} );
		}
		if ( class_exists( 'WpFastestCache' ) ) {
			$try( 'WP Fastest Cache', static function () {
				do_action( 'wpfc_clear_all_cache', true ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WP Fastest Cache's own hook.
			} );
		}
		if ( function_exists( 'sg_cachepress_purge_everything' ) ) {
			$try( 'SiteGround', 'sg_cachepress_purge_everything' );
		} elseif ( function_exists( 'sg_cachepress_purge_cache' ) ) {
			$try( 'SiteGround', 'sg_cachepress_purge_cache' );
		}
		if ( class_exists( 'Breeze_PurgeCache' ) || defined( 'BREEZE_VERSION' ) ) {
			$try( 'Breeze', static function () {
				do_action( 'breeze_clear_all_cache' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Breeze's own hook.
			} );
		}
		if ( defined( 'WPHB_VERSION' ) ) {
			$try( 'Hummingbird', static function () {
				do_action( 'wphb_clear_page_cache' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Hummingbird's own hook.
			} );
		}
		if ( defined( 'NGINX_HELPER_BASEPATH' ) || class_exists( 'Nginx_Helper' ) ) {
			$try( 'Nginx Helper', static function () {
				do_action( 'rt_nginx_helper_purge_all' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Nginx Helper's own hook.
			} );
		}
		if ( class_exists( 'Cache_Enabler' ) ) {
			$try( 'Cache Enabler', static function () {
				do_action( 'cache_enabler_clear_complete_cache' ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- Cache Enabler's own hook.
			} );
		}
		if ( class_exists( 'autoptimizeCache' ) && method_exists( 'autoptimizeCache', 'clearall' ) ) {
			$try( 'Autoptimize', array( 'autoptimizeCache', 'clearall' ) );
		}
		if ( class_exists( 'WpeCommon' ) ) {
			$try( 'WP Engine', static function () {
				if ( method_exists( 'WpeCommon', 'purge_memcached' ) ) {
					WpeCommon::purge_memcached();
				}
				if ( method_exists( 'WpeCommon', 'purge_varnish_cache' ) ) {
					WpeCommon::purge_varnish_cache();
				}
			} );
		}
		global $kinsta_cache;
		if ( is_object( $kinsta_cache ) && isset( $kinsta_cache->kinsta_cache_purge ) && method_exists( $kinsta_cache->kinsta_cache_purge, 'purge_complete_caches' ) ) {
			$try( 'Kinsta', static function () use ( $kinsta_cache ) {
				$kinsta_cache->kinsta_cache_purge->purge_complete_caches();
			} );
		}
		if ( class_exists( 'Swift_Performance_Cache' ) && method_exists( 'Swift_Performance_Cache', 'clear_all_cache' ) ) {
			$try( 'Swift Performance', array( 'Swift_Performance_Cache', 'clear_all_cache' ) );
		}
		if ( $elementor_css && class_exists( '\Elementor\Plugin' ) && isset( \Elementor\Plugin::$instance->files_manager ) ) {
			$try( 'Elementor CSS', static function () {
				\Elementor\Plugin::$instance->files_manager->clear_cache();
			} );
		}

		/** Fires after AI Connect cleared the caches it knows, for other caches to clear themselves. */
		do_action( 'wpzoom_ai_connect_cleared_cache', $done );
		return $done;
	}

	/** The wpzoom/clear-cache ability. */
	public static function ability( array $in = array() ): array {
		$cleared = self::clear( ! empty( $in['elementor_css'] ) );
		return array(
			'cleared' => $cleared,
			'note'    => $cleared
				? sprintf( /* translators: %s: the caches that were cleared, comma-separated */ __( 'Cleared: %s. Pages are rebuilt on the next visit. A CDN such as Cloudflare may keep its own copy for a while; purge it in its dashboard if the change still does not show.', 'wpzoom-ai-connect' ), implode( ', ', $cleared ) )
				: __( 'No page cache plugin or host cache was found on this site, so there was nothing to clear here. If a change does not show, the cache is elsewhere (a CDN such as Cloudflare, or the host) or it is the browser\'s: try a private window.', 'wpzoom-ai-connect' ),
		);
	}
}
WPZOOM_AI_Cache::init();
