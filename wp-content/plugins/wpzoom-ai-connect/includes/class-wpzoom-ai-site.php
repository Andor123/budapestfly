<?php
/**
 * The site overview an AI reads first: theme, design state, WPZOOM plugins, content counts.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Site {

	/**
	 * @param mixed $input Unused.
	 * @return array
	 */
	public static function overview( $input = null ): array {
		$theme  = wp_get_theme();
		$parent = $theme->parent();
		$author = (string) $theme->get( 'Author' );

		$overview = array(
			'site'  => array(
				'name'              => get_bloginfo( 'name' ),
				'tagline'           => get_bloginfo( 'description' ),
				'url'               => home_url( '/' ),
				'admin_url'         => admin_url(),
				'wordpress_version' => get_bloginfo( 'version' ),
				'language'          => get_locale(),
			),
			'theme' => array(
				'name'       => $theme->get( 'Name' ),
				'slug'       => $theme->get_stylesheet(),
				'version'    => $theme->get( 'Version' ),
				'author'     => wp_strip_all_tags( $author ),
				'is_wpzoom'  => false !== stripos( $author, 'wpzoom' ) || ( $parent && false !== stripos( (string) $parent->get( 'Author' ), 'wpzoom' ) ),
				'parent'     => $parent ? $parent->get( 'Name' ) : null,
				'customizer' => admin_url( 'customize.php' ),
			),
		);

		$block_theme = function_exists( 'wp_is_block_theme' ) && wp_is_block_theme();
		if ( $block_theme ) {
			$overview['theme']['block_theme'] = true;
			$overview['theme']['site_editor'] = admin_url( 'site-editor.php' );
		}

		if ( WPZOOM_AI_Theme_Settings::is_available() ) {
			$design = array();
			foreach ( array( 'color-palettes', 'header-layout-type', 'header-menu-style', 'headings-font-family' ) as $id ) {
				$setting = WPZOOM_AI_Theme_Settings::describe( $id );
				if ( ! $setting ) {
					continue;
				}
				$entry = array(
					'setting' => $id,
					'label'   => $setting['label'],
					'value'   => $setting['value'],
				);
				if ( ! empty( $setting['choices'] ) && is_scalar( $setting['value'] ) ) {
					foreach ( $setting['choices'] as $choice ) {
						if ( $choice['value'] === (string) $setting['value'] ) {
							$entry['value_label'] = $choice['label'];
						}
					}
				}
				$design[] = $entry;
			}
			$all                            = WPZOOM_AI_Theme_Settings::all( array( 'include_values' => false ) );
			$overview['design']             = $design;
			$overview['theme_settings']     = array(
				'total'    => $all['total'],
				'sections' => $all['available_sections'],
				'how'      => $block_theme
					? __( 'This is a block theme: colors, fonts and layout live in the Site Editor (Styles), not the Customizer, so wpzoom/list-theme-settings only has the core settings (site identity, front page). Style changes go through wpzoom/add-custom-css.', 'wpzoom-ai-connect' )
					: __( 'Call wpzoom/list-theme-settings (optionally with section or search) to see ids, choices and current values, then wpzoom/update-theme-settings to change them.', 'wpzoom-ai-connect' ),
			);
		} else {
			$overview['theme_settings'] = array(
				'total' => 0,
				'how'   => __( 'The active theme is not a WPZOOM theme with Customizer data, so theme settings cannot be listed or changed here.', 'wpzoom-ai-connect' ),
			);
		}

		$overview['patterns'] = array(
			'count' => count( WPZOOM_AI_Patterns::all() ),
			'how'   => __( 'Call wpzoom/list-patterns to see the ready-made design sections the theme ships, then wpzoom/insert-pattern to add one to a page.', 'wpzoom-ai-connect' ),
		);

		$overview['wpzoom_plugins'] = self::wpzoom_plugins();
		$overview['content']        = self::content();

		$overview['abilities'] = array(
			'wpzoom/site-overview'         => __( 'This overview.', 'wpzoom-ai-connect' ),
			'wpzoom/list-theme-settings'   => __( 'Every theme setting with its label, type, choices, default and current value.', 'wpzoom-ai-connect' ),
			'wpzoom/update-theme-settings' => __( 'Change one or more theme settings (colors, palette, header, fonts, layout).', 'wpzoom-ai-connect' ),
			'wpzoom/list-patterns'         => __( 'Ready-made design sections (hero, services, pricing, FAQ, team, contact...).', 'wpzoom-ai-connect' ),
			'wpzoom/insert-pattern'        => __( 'Add a design section to a page, or create a draft page from one.', 'wpzoom-ai-connect' ),
			'wpzoom/diagnose'              => __( 'The checks a support engineer runs first, each with a fix.', 'wpzoom-ai-connect' ),
		);
		if ( class_exists( 'WPZOOM_AI_Theme_Options' ) && WPZOOM_AI_Theme_Options::available() ) {
			$overview['theme_options'] = array(
				'where' => 'WPZOOM → Theme Options',
				'how'   => __( 'Call wpzoom/list-theme-options for the homepage, banner and feed options this theme keeps outside the Customizer, then wpzoom/update-theme-options.', 'wpzoom-ai-connect' ),
			);
			$overview['abilities']['wpzoom/list-theme-options']   = __( 'The WPZOOM → Theme Options panel: id, label, type, choices, value.', 'wpzoom-ai-connect' );
			$overview['abilities']['wpzoom/update-theme-options'] = __( 'Change Theme Options.', 'wpzoom-ai-connect' );
		}

		return $overview;
	}

	/**
	 * Active plugins by WPZOOM.
	 *
	 * @return array<int, array{slug: string, name: string, version: string}>
	 */
	private static function wpzoom_plugins(): array {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		$out = array();
		foreach ( get_plugins() as $file => $data ) {
			if ( ! is_plugin_active( $file ) ) {
				continue;
			}
			$slug      = dirname( $file );
			$is_wpzoom = false !== stripos( (string) $data['Author'], 'wpzoom' )
				|| (bool) preg_match( '/^(wpzoom|inspiro|recipe-card|social-icons|instagram-widget)/', $slug );
			if ( ! $is_wpzoom ) {
				continue;
			}
			$out[] = array(
				'slug'    => $slug,
				'name'    => wp_strip_all_tags( $data['Name'] ),
				'version' => $data['Version'],
			);
		}
		return $out;
	}

	/**
	 * What the site holds.
	 */
	private static function content(): array {
		$counts = array();
		foreach ( get_post_types( array( 'public' => true ), 'objects' ) as $type ) {
			if ( 'attachment' === $type->name ) {
				continue;
			}
			if ( WPZOOM_AI_Scope::is_connector_request() && WPZOOM_AI_Post_Types::NONE === WPZOOM_AI_Post_Types::mode( $type->name ) ) {
				continue; // The site owner keeps this type away from the connection.
			}
			$count = wp_count_posts( $type->name );
			$counts[ $type->name ] = array(
				'label'     => $type->labels->name,
				'published' => (int) ( $count->publish ?? 0 ),
				'draft'     => (int) ( $count->draft ?? 0 ),
			);
		}
		$front = array( 'shows' => get_option( 'show_on_front' ) );
		if ( 'page' === $front['shows'] ) {
			$page_id = (int) get_option( 'page_on_front' );
			if ( $page_id ) {
				$front['page_id'] = $page_id;
				$front['title']   = get_the_title( $page_id );
			}
		}
		return array(
			'post_types' => $counts,
			'front_page' => $front,
		);
	}
}
