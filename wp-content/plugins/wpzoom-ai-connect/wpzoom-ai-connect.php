<?php
/**
 * Plugin Name: WPZOOM AI Connect – MCP Connector for Claude & ChatGPT
 * Plugin URI: https://www.wpzoom.com/ai-connect/
 * Description: Lets AI assistants (Claude, ChatGPT, Cursor and any MCP client) understand and change your WPZOOM theme: theme settings, color palettes, design patterns and pages, through the WordPress Abilities API.
 * Version: 0.12.6
 * Requires at least: 6.9
 * Requires PHP: 7.4
 * Author: WPZOOM
 * Author URI: https://www.wpzoom.com/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: wpzoom-ai-connect
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'WPZOOM_AI_CONNECT_VERSION', '0.12.6' );
define( 'WPZOOM_AI_CONNECT_FILE', __FILE__ );
define( 'WPZOOM_AI_CONNECT_DIR', plugin_dir_path( __FILE__ ) );

require_once WPZOOM_AI_CONNECT_DIR . 'includes/auth-fallback.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-theme-settings.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-patterns.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-site.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-jobs.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-catalog.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-section-renderer.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-sections.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-starter-sites.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-content.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-diagnostics.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-css.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-theme-options.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-recipes.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-seo.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-accessibility.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-updates.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-elementor.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-post-types.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-scope.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-cache.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/connector-header.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/run-input.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/abilities.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/abilities-more.php';
require_once WPZOOM_AI_CONNECT_DIR . 'includes/abilities-site.php';

WPZOOM_AI_Scope::init();
WPZOOM_AI_Jobs::init();
WPZOOM_AI_Starter_Sites::init();
WPZOOM_AI_Sections::init();

if ( is_admin() ) {
	require_once WPZOOM_AI_CONNECT_DIR . 'includes/class-wpzoom-ai-admin.php';
	WPZOOM_AI_Admin::init();
}

/**
 * Remembers where the plugin was installed from, and sends the person who activated it to the welcome screen once.
 * Not when another plugin or the theme activated it over AJAX (Inspiro Starter Sites' plugin step, the theme's
 * plugin page), WP-CLI or cron: the redirect would land on that installer's next request and break it.
 */
function wpzoom_ai_connect_activate() {
	if ( false === get_option( WPZOOM_AI_CONNECT_SOURCE_OPTION, false ) ) {
		add_option( WPZOOM_AI_CONNECT_SOURCE_OPTION, wpzoom_ai_connect_install_source(), '', false );
	}
	if ( ! wp_doing_ajax() && ! wp_doing_cron() && ! ( defined( 'WP_CLI' ) && WP_CLI ) ) {
		set_transient( 'wpzoom_ai_connect_activated', 1, 60 );
	}
}

/** The option holding where this install came from; sent along with "Connect this site" so channels can be compared. */
const WPZOOM_AI_CONNECT_SOURCE_OPTION = 'wpzoom_ai_connect_source';

/** Where the plugin is being activated from: a known installer's AJAX action, or the WordPress screens. */
function wpzoom_ai_connect_install_source(): string {
	$source = 'wordpress';
	if ( wp_doing_ajax() ) {
		$action = isset( $_REQUEST['action'] ) ? sanitize_key( wp_unslash( $_REQUEST['action'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		$known  = array(
			'inspiro_starter_sites_install_plugin' => 'inspiro-starter-sites',
			'inspiro_install_plugin'               => 'inspiro-theme',
			'wpzoom_install_plugin'                => 'wpzoom-theme-plugins',
			'wpzoom_required_plugins'              => 'wpzoom-theme-onboarding',
			'wpzoom_connect_install_ai_connect'    => 'social-icons',
		);
		$source = $known[ $action ] ?? 'ajax';
	} elseif ( defined( 'WP_CLI' ) && WP_CLI ) {
		$source = 'wp-cli';
	}
	/**
	 * Filters where this install of WPZOOM AI Connect came from, e.g. "inspiro-starter-sites".
	 *
	 * @since 0.12.2
	 *
	 * @param string $source Lowercase letters, digits and dashes.
	 */
	return wpzoom_ai_connect_clean_source( (string) apply_filters( 'wpzoom_ai_connect_install_source', $source ) );
}

/** A source label as the connector accepts it: lowercase letters, digits and dashes, at most 40 characters. */
function wpzoom_ai_connect_clean_source( string $source ): string {
	return substr( preg_replace( '/[^a-z0-9-]/', '', strtolower( $source ) ), 0, 40 );
}
register_activation_hook( __FILE__, 'wpzoom_ai_connect_activate' );

/**
 * Admin notice when the Abilities API is missing (WordPress older than 6.9).
 */
function wpzoom_ai_connect_requirements_notice() {
	if ( function_exists( 'wp_register_ability' ) || ! current_user_can( 'activate_plugins' ) ) {
		return;
	}
	echo '<div class="notice notice-error"><p>';
	esc_html_e( 'WPZOOM AI Connect needs WordPress 6.9 or newer (the Abilities API). Please update WordPress.', 'wpzoom-ai-connect' );
	echo '</p></div>';
}
add_action( 'admin_notices', 'wpzoom_ai_connect_requirements_notice' );
