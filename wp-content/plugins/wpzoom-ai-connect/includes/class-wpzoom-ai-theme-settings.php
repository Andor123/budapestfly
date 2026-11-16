<?php
/**
 * Reads and writes the theme's Customizer settings for an AI.
 *
 * Source of truth is the Customizer itself: a WP_Customize_Manager is bootstrapped, the theme (and core)
 * register their settings and controls, and every control with a setting the current user may edit becomes
 * an entry with id, label, section, type, choices, default and current value. That covers WPZOOM themes on
 * the WPZOOM framework (Inspiro Premium, Eclipse, Foodica PRO...), Inspiro Lite and in fact any theme.
 * When the WPZOOM framework's `wpzoom_customizer_data` definition is present it enriches the entries.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Theme_Settings {

	/** Settings never exposed: huge, dangerous or meaningless for an AI. */
	// Additional CSS (custom_css[<theme>]) is code, not a setting: it stays in the Customizer.
	const SKIP_SETTINGS = array( 'custom_css[', 'custom_css_post[', 'active_theme', 'nav_menus_created_posts', 'widgets', 'sidebars_widgets[', 'nav_menu[', 'nav_menu_item[', 'nav_menu_locations[' );

	/** Settings that hold code (header/footer scripts, ad codes, custom CSS or JS): never listed or written. */
	const CODE_ID = '/(^|[_\-\[])(code|codes|css|js|script|scripts)(\]?)$|[_\-]code[_\-]|custom_css|custom_js/';

	/** @var array<string, array>|null id => description (without the current value) */
	private static $catalog = null;

	/** @var array<string, array> section id => [ title, panel_title ] */
	private static $sections = array();

	public static function is_available(): bool {
		return count( self::catalog() ) > 0;
	}

	/* ---------- Customizer bootstrap ---------- */

	private static function manager(): WP_Customize_Manager {
		global $wp_customize;
		if ( $wp_customize instanceof WP_Customize_Manager ) {
			return $wp_customize;
		}
		if ( ! class_exists( 'WP_Customize_Manager' ) ) {
			require_once ABSPATH . WPINC . '/class-wp-customize-manager.php';
		}
		$manager      = new WP_Customize_Manager();
		$wp_customize = $manager;
		// What core does on wp_loaded for the Customizer screen: core settings, then the theme's.
		set_error_handler( static function () { return true; }, E_WARNING | E_NOTICE | E_DEPRECATED ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		ob_start();
		try {
			$manager->register_controls();
			self::boot_theme_customizer();
			do_action( 'customize_register', $manager ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core's hook, fired so the theme registers its Customizer controls.
		} catch ( Throwable $e ) {
			// A theme control that cannot be built outside the Customizer screen: continue with what registered.
		}
		ob_end_clean();
		restore_error_handler();
		return $manager;
	}

	/**
	 * Themes that hook their Customizer modules only when the manager global already exists while the theme
	 * loads (Neve) never register outside the Customizer screen: boot them now that the global is set.
	 */
	private static function boot_theme_customizer(): void {
		if ( did_action( 'customize_register' ) ) {
			return;
		}
		if ( class_exists( '\Neve\Customizer\Loader' ) ) {
			( new \Neve\Customizer\Loader() )->init();
		}
		$blocksy = get_template_directory() . '/inc/customizer/init.php';
		if ( function_exists( 'blocksy_get_options' ) && ! function_exists( 'blocksy_sync_single_post_container' ) && is_readable( $blocksy ) ) {
			require $blocksy; // Blocksy includes it only when $wp_customize exists while the theme loads.
		}
	}

	/** Control types that only decorate the Customizer (OceanWP and Neve dividers, titles, notes, upsells, tabs). */
	const DECORATIVE_TYPES = array( 'ocean-divider', 'ocean-title', 'ocean-content', 'ocean-spacer', 'ocean-upsell', 'ocean-links', 'customizer-heading', 'neve_customizer_heading', 'nv_simple_upsell', 'interface-tabs' );

	/** The control's real type: OceanWP replaces it with "ocean-headless" and keeps the original in its JSON data. */
	private static function control_type( WP_Customize_Control $control ): string {
		if ( 'ocean-headless' === $control->type && is_array( $control->json ) && ! empty( $control->json['oceanControlType'] ) ) {
			return (string) $control->json['oceanControlType'];
		}
		return (string) $control->type;
	}

	/** The WPZOOM framework definition, when the theme has one: id => option definition. */
	private static function framework_options(): array {
		static $options = null;
		if ( null !== $options ) {
			return $options;
		}
		$options = array();
		foreach ( (array) apply_filters( 'wpzoom_customizer_data', array() ) as $section ) {
			foreach ( (array) ( $section['options'] ?? array() ) as $id => $option ) {
				if ( is_array( $option ) ) {
					$options[ (string) $id ] = $option;
				}
			}
		}
		return $options;
	}

	/**
	 * Every settable control, described, keyed by setting id.
	 *
	 * @return array<string, array>
	 */
	public static function catalog(): array {
		if ( null !== self::$catalog ) {
			return self::$catalog;
		}
		self::$catalog = array();
		$manager       = self::manager();
		$framework     = self::framework_options();

		foreach ( $manager->controls() as $control ) {
			$settings = array();
			if ( $control->setting instanceof WP_Customize_Setting ) {
				$settings[''] = $control->setting;
			} elseif ( is_array( $control->settings ) ) {
				foreach ( $control->settings as $key => $linked ) {
					if ( $linked instanceof WP_Customize_Setting ) {
						$settings[ (string) $key ] = $linked; // e.g. OceanWP color controls: a normal and a hover setting.
					}
				}
			}
			foreach ( $settings as $key => $setting ) {
				$described = self::describe_linked( $control, $setting, $key, $framework, $manager );
				if ( $described ) {
					self::$catalog[ $setting->id ] = $described;
				}
			}
		}
		foreach ( self::astra_controls( $manager ) as $id => $described ) {
			if ( ! isset( self::$catalog[ $id ] ) ) {
				self::$catalog[ $id ] = $described;
			}
		}
		foreach ( self::blocksy_settings( $manager ) as $id => $described ) {
			if ( ! isset( self::$catalog[ $id ] ) ) {
				self::$catalog[ $id ] = $described;
			}
		}

		// WPZOOM framework themes register their Customizer controls only on the Customizer screen; their
		// definition array is available everywhere, so anything the manager did not show comes from it.
		foreach ( (array) apply_filters( 'wpzoom_customizer_data', array() ) as $section_id => $section ) {
			foreach ( (array) ( $section['options'] ?? array() ) as $id => $option ) {
				$id = (string) $id;
				if ( isset( self::$catalog[ $id ] ) || empty( $option['setting'] ) || ! is_array( $option['setting'] ) ) {
					continue;
				}
				if ( ! current_user_can( $option['setting']['capability'] ?? 'edit_theme_options' ) ) {
					continue;
				}
				self::$catalog[ $id ] = self::describe_framework_option( $id, (string) $section_id, (array) $section, (array) $option );
			}
		}
		return self::$catalog;
	}

	/**
	 * One setting linked to a control (most controls have one, under the "default" key), or null when it is not
	 * for an AI: hidden, decorative, code, or outside the user's capabilities.
	 */
	private static function describe_linked( WP_Customize_Control $control, WP_Customize_Setting $setting, string $key, array $framework, WP_Customize_Manager $manager ): ?array {
		if ( ! $setting->check_capabilities() ) {
			return null;
		}
		$id = $setting->id;
		foreach ( self::SKIP_SETTINGS as $skip ) {
			if ( 0 === strpos( $id, $skip ) || $id === rtrim( $skip, '[' ) ) {
				return null;
			}
		}
		if ( preg_match( self::CODE_ID, $id ) || $control instanceof WP_Customize_Code_Editor_Control || 'code_editor' === $control->type ) {
			return null;
		}
		if ( isset( self::$catalog[ $id ] ) || in_array( $control->type, array( 'hidden' ), true ) || in_array( self::control_type( $control ), self::DECORATIVE_TYPES, true ) ) {
			return null;
		}
		$described = self::describe_control( $control, $setting, $framework[ $id ] ?? null, $manager );
		if ( $described && '' !== $key && 'default' !== $key && $key !== $id ) {
			$described['label'] = trim( $described['label'] . ' (' . self::humanize( $key ) . ')' );
			if ( 'choice' === $described['type'] && is_numeric( $setting->default ) ) {
				$described['type'] = 'number'; // A numeric companion of a radio control (WooCommerce thumbnail cropping width).
				unset( $described['choices'] );
			}
		}
		return $described;
	}

	/** Describes an option from the WPZOOM framework's definition array (no control object available). */
	private static function describe_framework_option( string $id, string $section_id, array $section, array $option ): array {
		$setting = $option['setting'];
		$control = isset( $option['control'] ) && is_array( $option['control'] ) ? $option['control'] : array();
		$ctype   = (string) ( $control['control_type'] ?? '' );
		$type    = (string) ( $control['type'] ?? '' );
		$attrs   = isset( $control['input_attrs'] ) && is_array( $control['input_attrs'] ) ? $control['input_attrs'] : array();
		$hay     = strtolower( $ctype . ' ' . $type );
		$choices = null;

		if ( false !== strpos( $hay, 'palette' ) ) {
			$kind    = 'choice';
			$choices = self::palette_choices();
		} elseif ( false !== strpos( $hay, 'gradient' ) ) {
			$kind = 'gradient';
		} elseif ( false !== strpos( $hay, 'color' ) ) {
			$kind = 'color';
		} elseif ( false !== strpos( $hay, 'checkbox' ) || false !== strpos( $hay, 'toggle' ) ) {
			$kind = 'checkbox';
		} elseif ( false !== strpos( $hay, 'radio' ) || false !== strpos( $hay, 'select' ) ) {
			$kind    = 'choice';
			$choices = self::choices( $control['choices'] ?? array() );
		} elseif ( false !== strpos( $hay, 'range' ) || in_array( $type, array( 'range', 'number' ), true ) ) {
			$kind = 'number';
		} elseif ( 'typography' === $type ) {
			$kind = 'typography';
		} elseif ( false !== strpos( $hay, 'image' ) || false !== strpos( $hay, 'media' ) ) {
			$kind = 'image';
		} elseif ( false !== strpos( $hay, 'editor' ) || 'textarea' === $type ) {
			$kind = 'html';
		} else {
			$kind = 'text';
		}

		$font = self::is_font_family( $id, $setting['sanitize_callback'] ?? null );
		if ( $font ) {
			$kind    = 'choice';
			$choices = self::font_choices();
		}

		$described = array(
			'id'            => $id,
			'label'         => self::text( $control['label'] ?? '' ) ?: self::humanize( $id ),
			'description'   => self::text( $control['description'] ?? '' ),
			'section'       => $section_id,
			'section_title' => self::text( $section['title'] ?? $section_id ),
			'type'          => $kind,
			'storage'       => ( isset( $setting['type'] ) && 'option' === $setting['type'] ) ? 'option' : 'theme_mod',
			'default'       => $setting['default'] ?? '',
			'sanitize'      => isset( $setting['sanitize_callback'] ) && is_callable( $setting['sanitize_callback'] ) ? $setting['sanitize_callback'] : null,
		);
		if ( null !== $choices ) {
			$described['choices'] = $choices;
		}
		if ( $font ) {
			$described['choices_open'] = true;
			$described['description']  = trim( $described['description'] . ' ' . self::font_note() );
		}
		if ( 'number' === $kind ) {
			foreach ( array( 'min', 'max', 'step' ) as $key ) {
				if ( isset( $attrs[ $key ] ) && is_numeric( $attrs[ $key ] ) ) {
					$described[ $key ] = $attrs[ $key ] + 0;
				}
			}
		}
		if ( 'checkbox' === $kind ) {
			$described['description'] = trim( $described['description'] . ' ' . __( 'Send true or false.', 'wpzoom-ai-connect' ) );
		}
		if ( 'color' === $kind ) {
			$described['description'] = trim( $described['description'] . ' ' . __( 'Send a hex color like #1e73be, or an empty string to reset.', 'wpzoom-ai-connect' ) );
		}
		return $described;
	}

	private static function section_info( WP_Customize_Manager $manager, string $section_id ): array {
		if ( isset( self::$sections[ $section_id ] ) ) {
			return self::$sections[ $section_id ];
		}
		$section = $manager->get_section( $section_id );
		$title   = $section ? self::text( $section->title ) : self::text( $section_id );
		$panel   = $section && $section->panel ? $manager->get_panel( $section->panel ) : null;
		self::$sections[ $section_id ] = array(
			'title' => $title,
			'panel' => $panel ? self::text( $panel->title ) : '',
		);
		return self::$sections[ $section_id ];
	}

	private static function kind_from_control( WP_Customize_Control $control, ?array $framework = null ): array {
		$class   = get_class( $control );
		$type    = self::control_type( $control );
		$fw_type = (string) ( $framework['control']['control_type'] ?? '' ) . ' ' . (string) ( $framework['control']['type'] ?? '' );
		$hay     = strtolower( $class . ' ' . $type . ' ' . $fw_type );
		$choices = null;

		if ( false !== strpos( $hay, 'palette' ) && function_exists( 'wpzoom_get_color_palettes' ) ) {
			$kind    = 'choice';
			$choices = self::palette_choices();
		} elseif ( false !== strpos( $hay, 'gradient' ) ) {
			$kind = 'gradient';
		} elseif ( false !== strpos( $hay, 'color' ) ) {
			$kind = 'color';
		} elseif ( false !== strpos( $hay, 'typography' ) || false !== strpos( $hay, 'font' ) && is_array( $control->choices ) && ! $control->choices ) {
			$kind = 'typography';
		} elseif ( false !== strpos( $hay, 'checkbox' ) || false !== strpos( $hay, 'toggle' ) || false !== strpos( $hay, 'switch' ) ) {
			$kind = 'checkbox';
		} elseif ( 'dropdown-pages' === $type ) {
			$kind    = 'choice';
			$choices = array();
			foreach ( get_pages( array( 'number' => 200 ) ) as $page ) {
				$choices[] = array( 'value' => (string) $page->ID, 'label' => $page->post_title );
			}
		} elseif ( ! empty( $control->choices ) && is_array( $control->choices ) && self::range_choices( $control->choices ) ) {
			// A range slider whose "choices" are per-device ranges, not values: GeneratePress passes
			// array( 'desktop' => array( 'min', 'max', 'step', 'unit' ) ) for its logo width and sizes.
			$kind = 'number';
		} elseif ( ! empty( $control->choices ) && is_array( $control->choices ) ) {
			$kind    = 'choice';
			$choices = self::choices( $control->choices );
		} elseif ( is_array( $control->json ) && ! empty( $control->json['choices'] ) && is_array( $control->json['choices'] ) ) {
			$kind    = 'choice'; // OceanWP hands the choices to its React controls through the JSON data.
			$choices = self::choices( $control->json['choices'] );
		} elseif ( false !== strpos( $hay, 'radio' ) || false !== strpos( $hay, 'select' ) ) {
			$kind    = 'choice';
			$choices = self::choices( (array) ( $framework['control']['choices'] ?? array() ) );
			if ( ! $choices && is_array( $control->input_attrs ) ) {
				// Kadence keeps a radio icon's options in input_attrs['layout'] (value => tooltip + icon), others in 'options'.
				foreach ( array( 'layout', 'options', 'choices' ) as $key ) {
					if ( ! empty( $control->input_attrs[ $key ] ) && is_array( $control->input_attrs[ $key ] ) ) {
						$choices = self::choices( $control->input_attrs[ $key ] );
						break;
					}
				}
			}
		} elseif ( false !== strpos( $hay, 'range' ) || false !== strpos( $hay, 'slider' ) || in_array( $type, array( 'number', 'range' ), true ) ) {
			$kind = 'number';
		} elseif ( false !== strpos( $hay, 'image' ) || false !== strpos( $hay, 'media' ) || false !== strpos( $hay, 'upload' ) || false !== strpos( $hay, 'cropped' ) ) {
			$kind = 'image';
		} elseif ( false !== strpos( $hay, 'editor' ) || 'textarea' === $type ) {
			$kind = 'html';
		} elseif ( in_array( $type, array( 'url', 'email', 'text' ), true ) ) {
			$kind = 'text';
		} else {
			$kind = 'text';
		}
		return array( $kind, $choices );
	}

	private static function describe_control( WP_Customize_Control $control, WP_Customize_Setting $setting, ?array $framework, WP_Customize_Manager $manager ): ?array {
		list( $kind, $choices ) = self::kind_from_control( $control, $framework );
		$range = 'number' === $kind && ! empty( $control->choices ) && is_array( $control->choices ) && self::range_choices( $control->choices ) ? self::range_for( $control, $setting->id ) : array();
		$font  = empty( $choices ) && self::is_font_family( $setting->id, $framework['setting']['sanitize_callback'] ?? $setting->sanitize_callback );
		if ( $font ) {
			$kind    = 'choice';
			$choices = self::font_choices();
		}
		$default = $setting->default;
		if ( is_array( $default ) && $default && ! in_array( $kind, array( 'typography', 'gradient' ), true ) && empty( $choices ) ) {
			// Kadence (and others) store responsive sizes, color objects, borders and shadows as arrays: a plain
			// color or number would corrupt them.
			$kind = 'object';
		}
		$json_keys = null;
		if ( is_string( $default ) && '' !== $default && '{' === $default[0] && is_array( json_decode( $default, true ) ) && empty( $choices ) ) {
			// Neve and OceanWP keep responsive values as JSON strings.
			$kind      = 'text';
			$json_keys = array_keys( json_decode( $default, true ) );
		} elseif ( is_scalar( $default ) && ! is_bool( $default ) && '' !== (string) $default && empty( $choices ) ) {
			// A scalar linked to a typography or range control (OceanWP's per-device size and unit settings).
			if ( 'typography' === $kind ) {
				$kind = is_numeric( $default ) ? 'number' : 'text';
			} elseif ( 'number' === $kind && ! is_numeric( $default ) ) {
				$kind = 'text';
			}
		} elseif ( 'typography' === $kind && preg_match( '/\[[^\]]+\]$/', $setting->id ) && ! is_array( $default ) ) {
			$kind = 'text'; // One part of a typography control (font-family, font-size...), stored on its own.
		}
		$section   = self::section_info( $manager, (string) $control->section );
		$label     = self::text( $control->label ?: ( $framework['control']['label'] ?? '' ) );
		if ( '' === $label ) {
			$label = self::humanize( $setting->id );
		}
		$described = array(
			'id'            => $setting->id,
			'label'         => $label,
			'description'   => self::text( $control->description ?: ( $framework['control']['description'] ?? '' ) ),
			'section'       => (string) $control->section,
			'section_title' => trim( ( $section['panel'] ? $section['panel'] . ' › ' : '' ) . $section['title'] ),
			'type'          => $kind,
			'storage'       => 'option' === $setting->type ? 'option' : 'theme_mod',
			'default'       => $setting->default,
		);
		if ( null !== $choices ) {
			$described['choices'] = $choices;
		}
		$attrs = is_array( $control->input_attrs ) ? $control->input_attrs : array();
		if ( 'number' === $kind ) {
			$limits = $range + $attrs;
			foreach ( array( 'min', 'max', 'step' ) as $key ) {
				if ( isset( $limits[ $key ] ) && is_numeric( $limits[ $key ] ) ) {
					$described[ $key ] = $limits[ $key ] + 0;
				}
			}
			if ( ! empty( $limits['unit'] ) && is_string( $limits['unit'] ) ) {
				$described['unit']        = $limits['unit'];
				$described['description'] = trim( $described['description'] . ' ' . sprintf( /* translators: %s: unit, like px or em */ __( 'Send a number, in %s.', 'wpzoom-ai-connect' ), $limits['unit'] ) );
			}
		}
		if ( 'checkbox' === $kind ) {
			$described['description'] = trim( $described['description'] . ' ' . __( 'Send true or false.', 'wpzoom-ai-connect' ) );
		}
		if ( 'color' === $kind ) {
			$described['description'] = trim( $described['description'] . ' ' . __( 'Send a hex color like #1e73be, or an empty string to reset.', 'wpzoom-ai-connect' ) );
		}
		if ( 'image' === $kind ) {
			$described['description'] = trim( $described['description'] . ' ' . __( 'Send an image URL from the Media Library (or an attachment id for cropped-image settings).', 'wpzoom-ai-connect' ) );
		}
		if ( $font ) {
			$described['choices_open'] = true;
			$described['description']  = trim( $described['description'] . ' ' . self::font_note() );
		}
		if ( $json_keys ) {
			$described['description'] = trim( $described['description'] . ' ' . sprintf( /* translators: %s: comma-separated keys */ __( 'Stored as a JSON string with the keys %s; send the whole string shaped like the current value.', 'wpzoom-ai-connect' ), implode( ', ', array_map( 'strval', $json_keys ) ) ) );
		}
		if ( 'object' === $kind ) {
			$keys = implode( ', ', array_map( 'strval', array_keys( $setting->default ) ) );
			$described['description'] = trim( $described['description'] . ' ' . sprintf( /* translators: %s: comma-separated keys */ __( 'Structured setting: send an object shaped like its current value (keys: %s); colors inside it take hex or the theme\'s palette slugs.', 'wpzoom-ai-connect' ), $keys ) );
		}
		return $described;
	}

	/* ---------- fonts (WPZOOM framework) ---------- */

	/** Appended to font settings' descriptions. */
	private static function font_note(): string {
		return __( 'The listed fonts are the standard and most popular ones; any Google Font name (e.g. "Playfair Display") is accepted too.', 'wpzoom-ai-connect' );
	}

	/** Whether a control's "choices" are per-device ranges (min, max, step), the way GeneratePress's range slider passes them. */
	private static function range_choices( array $choices ): bool {
		foreach ( $choices as $choice ) {
			if ( ! is_array( $choice ) || ( ! isset( $choice['min'] ) && ! isset( $choice['max'] ) ) ) {
				return false;
			}
		}
		return (bool) $choices;
	}

	/** The range that applies to one setting of a range slider: the device it is linked as, else desktop, else the first. */
	private static function range_for( WP_Customize_Control $control, string $setting_id ): array {
		$choices  = (array) $control->choices;
		$settings = is_array( $control->settings ) ? $control->settings : array();
		foreach ( $settings as $device => $linked ) {
			$linked_id = is_object( $linked ) && isset( $linked->id ) ? (string) $linked->id : (string) $linked;
			if ( $linked_id === $setting_id && isset( $choices[ $device ] ) && is_array( $choices[ $device ] ) ) {
				return $choices[ $device ];
			}
		}
		if ( isset( $choices['desktop'] ) && is_array( $choices['desktop'] ) ) {
			return $choices['desktop'];
		}
		$first = reset( $choices );
		return is_array( $first ) ? $first : array();
	}

	/** Whether a WPZOOM framework setting is a font-family select (its choices are built in JavaScript). */
	private static function is_font_family( string $id, $sanitize ): bool {
		if ( ! function_exists( 'zoom_customizer_sanitize_font_choice' ) ) {
			return false;
		}
		return 'zoom_customizer_sanitize_font_choice' === $sanitize || (bool) preg_match( '/-font-family$/', $id );
	}

	/** Standard fonts (with any custom fonts the framework merges in) and the popular Google fonts. */
	public static function font_choices(): array {
		static $choices = null;
		if ( null !== $choices ) {
			return $choices;
		}
		$choices = array();
		$groups  = array();
		if ( function_exists( 'zoom_customizer_get_standard_fonts' ) ) {
			$groups[ __( 'Standard', 'wpzoom-ai-connect' ) ] = zoom_customizer_get_standard_fonts();
		}
		if ( function_exists( 'zoom_customizer_get_popular_google_fonts' ) ) {
			$groups[ __( 'Popular Google Fonts', 'wpzoom-ai-connect' ) ] = zoom_customizer_get_popular_google_fonts();
		}
		foreach ( $groups as $group => $fonts ) {
			foreach ( (array) $fonts as $name => $font ) {
				$choices[] = array(
					'value' => (string) $name,
					'label' => self::text( is_array( $font ) && isset( $font['label'] ) ? $font['label'] : $name ),
					'group' => $group,
				);
			}
		}
		return $choices;
	}

	/* ---------- Astra ---------- */

	/**
	 * Astra builds most of its Customizer controls in JavaScript from PHP configuration arrays, so the
	 * manager has settings for only a handful of them. Describe the simple ones (colors, selects, toggles,
	 * sliders, text, fonts) from those configurations; grouped/responsive controls stay out.
	 *
	 * @return array<string, array>
	 */
	private static function astra_controls( WP_Customize_Manager $manager ): array {
		if ( ! class_exists( 'Astra_Customizer' ) || ! method_exists( 'Astra_Customizer', 'get_js_configs' ) ) {
			return array();
		}
		$kinds = array(
			'ast-color'          => 'color',
			'ast-selector'       => 'choice',
			'ast-select'         => 'choice',
			'ast-radio-image'    => 'choice',
			'ast-toggle-control' => 'checkbox',
			'ast-slider'         => 'number',
			'ast-number'         => 'number',
			'ast-text-input'     => 'text',
			'ast-font'           => 'text',
			'ast-html-editor'    => 'html',
		);
		$out = array();
		set_error_handler( static function () { return true; }, E_WARNING | E_NOTICE | E_DEPRECATED ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		ob_start();
		try {
			$configs = Astra_Customizer::get_js_configs();
			if ( empty( $configs['controls'] ) ) {
				// Outside the Customizer screen Astra loads no configurations: do what it does there.
				$astra = Astra_Customizer::get_instance();
				if ( ! class_exists( 'Astra_Site_Layout_Configs' ) && method_exists( $astra, 'include_configurations' ) ) {
					$astra->include_configurations();
				}
				if ( method_exists( $astra, 'prepare_customizer_javascript_configs' ) ) {
					$astra->prepare_customizer_javascript_configs();
				}
				$configs = Astra_Customizer::get_js_configs();
			}
			$lists = array_values( (array) ( $configs['controls'] ?? array() ) );
			foreach ( (array) ( $configs['sub_controls'] ?? array() ) as $sub ) {
				$lists[] = $sub;
			}
			foreach ( $lists as $controls ) {
				foreach ( (array) $controls as $config ) {
					$id   = (string) ( $config['name'] ?? '' );
					$type = (string) ( $config['type'] ?? $config['control'] ?? '' );
					if ( '' === $id || ! isset( $kinds[ $type ] ) || ! empty( $config['responsive'] ) || false !== strpos( $id, 'ast-callback-notice' ) ) {
						continue;
					}
					if ( ! empty( $config['capability'] ) && ! current_user_can( (string) $config['capability'] ) ) {
						continue;
					}
					$kind       = $kinds[ $type ];
					$section_id = (string) ( $config['section'] ?? '' );
					$section    = $configs['sections'][ $section_id ] ?? null;
					$title      = is_array( $section ) ? self::text( $section['title'] ?? $section_id ) : '';
					if ( '' === $title ) {
						$title = self::section_info( $manager, $section_id )['title'];
					}
					$panel = is_array( $section ) && ! empty( $section['panel'] ) && isset( $configs['panels'][ $section['panel'] ] ) ? self::text( $configs['panels'][ $section['panel'] ]['title'] ?? '' ) : '';
					$sanitize = $config['sanitize_callback'] ?? null;
					if ( ! $sanitize && class_exists( 'Astra_Customizer_Control_Base' ) && method_exists( 'Astra_Customizer_Control_Base', 'get_sanitize_call' ) ) {
						$sanitize = Astra_Customizer_Control_Base::get_sanitize_call( $type ); // What Astra itself runs when the Customizer saves this control.
					}
					$described = array(
						'id'            => $id,
						'label'         => self::text( $config['label'] ?? $config['title'] ?? '' ) ?: self::humanize( $id ),
						'description'   => self::text( $config['description'] ?? '' ),
						'section'       => $section_id,
						'section_title' => trim( ( $panel ? $panel . ' › ' : '' ) . $title ),
						'type'          => $kind,
						'storage'       => ( isset( $config['datastore_type'] ) && 'theme_mod' === $config['datastore_type'] ) ? 'theme_mod' : 'option',
						'default'       => $config['default'] ?? '',
						'sanitize'      => $sanitize && is_callable( $sanitize ) ? $sanitize : null,
					);
					if ( 'choice' === $kind ) {
						$described['choices'] = self::choices( $config['choices'] ?? array() );
						if ( ! $described['choices'] ) {
							continue;
						}
					}
					if ( 'number' === $kind ) {
						$attrs = isset( $config['input_attrs'] ) && is_array( $config['input_attrs'] ) ? $config['input_attrs'] : array();
						foreach ( array( 'min', 'max', 'step' ) as $key ) {
							if ( isset( $attrs[ $key ] ) && is_numeric( $attrs[ $key ] ) ) {
								$described[ $key ] = $attrs[ $key ] + 0;
							}
						}
						if ( ! empty( $config['suffix'] ) && is_scalar( $config['suffix'] ) ) {
							$described['description'] = trim( $described['description'] . ' ' . sprintf( /* translators: %s: unit, like px or em */ __( 'Unit: %s.', 'wpzoom-ai-connect' ), (string) $config['suffix'] ) );
						}
					}
					if ( 'checkbox' === $kind ) {
						$described['description'] = trim( $described['description'] . ' ' . __( 'Send true or false.', 'wpzoom-ai-connect' ) );
					}
					if ( 'color' === $kind ) {
						$described['description'] = trim( $described['description'] . ' ' . __( 'Send a hex color like #1e73be, or an empty string to reset.', 'wpzoom-ai-connect' ) . ' ' . __( 'Astra global colors are accepted as var(--ast-global-color-0) … var(--ast-global-color-8).', 'wpzoom-ai-connect' ) );
					}
					if ( 'ast-font' === $type ) {
						$described['description'] = trim( $described['description'] . ' ' . __( 'Font family as Astra stores it, e.g. \'Inter\', sans-serif, or inherit.', 'wpzoom-ai-connect' ) );
					}
					$out[ $id ] = $described;
				}
			}
		} catch ( Throwable $e ) {
			// Astra internals changed: fall back to the manager's controls.
		}
		ob_end_clean();
		restore_error_handler();
		return $out;
	}

	/* ---------- Blocksy ---------- */

	/**
	 * Blocksy registers a Customizer setting for every option of its PHP option tree but renders the controls in
	 * React, so the manager has settings without controls. Describe the simple ones from the tree.
	 *
	 * @return array<string, array>
	 */
	private static function blocksy_settings( WP_Customize_Manager $manager ): array {
		if ( ! function_exists( 'blocksy_get_options' ) || ! function_exists( 'blocksy_collect_options' ) ) {
			return array();
		}
		$kinds = array(
			'ct-color-picker'   => 'object',
			'ct-select'         => 'choice',
			'ct-radio'          => 'choice',
			'ct-image-picker'   => 'choice',
			'ct-switch'         => 'checkbox',
			'ct-slider'         => 'number',
			'ct-number'         => 'number',
			'ct-text'           => 'text',
			'ct-textarea'       => 'html',
			'ct-typography'     => 'typography',
			'ct-spacing'        => 'object',
			'ct-border'         => 'object',
			'ct-box-shadow'     => 'object',
			'ct-background'     => 'object',
			'ct-image-uploader' => 'image',
		);
		$out = array();
		set_error_handler( static function () { return true; }, E_WARNING | E_NOTICE | E_DEPRECATED ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		ob_start();
		try {
			$collected = array();
			$tree      = blocksy_get_options( 'customizer' );
			blocksy_collect_options(
				$collected,
				$tree,
				array(
					'limit_option_types'      => false,
					'limit_level'             => 0, // Unlimited depth.
					'include_container_types' => true, // Containers come first, in order: they give each option its panel path.
					'include_inner_options'   => true, // Panels keep their children under inner-options.
					'info_wrapper'            => true,
				)
			);
			$stack = array();
			foreach ( (array) $collected as $item ) {
				$option = isset( $item['option'] ) && is_array( $item['option'] ) ? $item['option'] : array();
				$level  = (int) ( $item['level'] ?? 1 );
				$type   = (string) ( $option['type'] ?? '' );
				if ( 'container' === ( $item['group'] ?? '' ) ) {
					foreach ( array_keys( $stack ) as $l ) {
						if ( $l >= $level ) {
							unset( $stack[ $l ] );
						}
					}
					$title = self::text( $option['title'] ?? $option['label'] ?? '' );
					if ( '' !== $title && ! in_array( $type, array( 'tab', 'ct-condition', 'ct-group', 'ct-has-meta-category-button' ), true ) ) {
						$stack[ $level ] = $title; // Panels and sections; tabs and conditions are not places.
					}
					continue;
				}
				$id = (string) ( $item['id'] ?? '' );
				if ( '' === $id || ! isset( $kinds[ $type ] ) ) {
					continue;
				}
				$setting = $manager->get_setting( $id );
				if ( ! $setting || ! $setting->check_capabilities() || $manager->get_control( $id ) ) {
					continue;
				}
				$kind    = $kinds[ $type ];
				$default = $option['value'] ?? '';
				if ( is_array( $default ) && $default && ! in_array( $kind, array( 'typography', 'object' ), true ) ) {
					$kind = 'object'; // A responsive slider or multi-state value.
				}
				$path    = array_values( array_filter( $stack, static function ( $title, $l ) use ( $level ) { return $l < $level && '' !== $title; }, ARRAY_FILTER_USE_BOTH ) );
				$section = $path ? end( $path ) : 'blocksy';
				$described = array(
					'id'            => $id,
					'label'         => self::text( $option['label'] ?? '' ) ?: self::humanize( $id ),
					'description'   => self::text( $option['desc'] ?? '' ),
					'section'       => sanitize_key( (string) $section ),
					'section_title' => self::text( implode( ' › ', $path ) ) ?: 'Blocksy',
					'type'          => $kind,
					'storage'       => 'option' === $setting->type ? 'option' : 'theme_mod',
					'default'       => $default,
				);
				if ( 'choice' === $kind ) {
					$choices = array();
					foreach ( (array) ( $option['choices'] ?? array() ) as $key => $label ) {
						if ( is_array( $label ) && isset( $label['key'] ) ) {
							$choices[ (string) $label['key'] ] = $label['value'] ?? $label['key'];
						} else {
							$choices[ (string) $key ] = $label;
						}
					}
					$described['choices'] = self::choices( $choices );
					if ( ! $described['choices'] ) {
						continue;
					}
				}
				if ( 'number' === $kind ) {
					foreach ( array( 'min', 'max', 'step' ) as $key ) {
						if ( isset( $option[ $key ] ) && is_numeric( $option[ $key ] ) ) {
							$described[ $key ] = $option[ $key ] + 0;
						}
					}
				}
				if ( 'checkbox' === $kind ) {
					$described['description'] = trim( $described['description'] . ' ' . __( 'Send true or false.', 'wpzoom-ai-connect' ) );
				}
				if ( 'object' === $kind && is_array( $default ) && $default ) {
					$described['description'] = trim( $described['description'] . ' ' . sprintf( /* translators: %s: comma-separated keys */ __( 'Structured setting: send an object shaped like its current value (keys: %s); colors inside it take hex or Blocksy palette variables like var(--theme-palette-color-1).', 'wpzoom-ai-connect' ), implode( ', ', array_map( 'strval', array_keys( $default ) ) ) ) );
				}
				$out[ $id ] = $described;
			}
		} catch ( Throwable $e ) {
			// Blocksy internals changed: fall back to the manager's controls.
		}
		ob_end_clean();
		restore_error_handler();
		return $out;
	}

	/* ---------- reads ---------- */

	/**
	 * Every settable option, described, optionally filtered.
	 *
	 * @param array $args { section?: string, search?: string, include_values?: bool }
	 */
	public static function all( array $args = array() ): array {
		$section_filter = isset( $args['section'] ) ? sanitize_key( (string) $args['section'] ) : '';
		$search         = isset( $args['search'] ) ? strtolower( trim( (string) $args['search'] ) ) : '';
		$include_values = ! isset( $args['include_values'] ) || (bool) $args['include_values'];

		$grouped   = array();
		$counts    = array();
		$titles    = array();
		foreach ( self::catalog() as $id => $described ) {
			$section = $described['section'];
			$counts[ $section ] = ( $counts[ $section ] ?? 0 ) + 1;
			$titles[ $section ] = $described['section_title'];
			if ( $section_filter && $section_filter !== sanitize_key( $section ) ) {
				continue;
			}
			if ( $search ) {
				$haystack = strtolower( $id . ' ' . $described['label'] . ' ' . $described['description'] . ' ' . $described['section_title'] );
				if ( false === strpos( $haystack, $search ) ) {
					continue;
				}
			}
			if ( $include_values ) {
				$described['value'] = self::get_value( $described );
			}
			$grouped[ $section ][] = $described;
		}

		$sections = array();
		$total    = 0;
		foreach ( $grouped as $section => $items ) {
			$total     += count( $items );
			$sections[] = array( 'id' => $section, 'title' => $titles[ $section ], 'settings' => $items );
		}
		$available = array();
		foreach ( $counts as $section => $count ) {
			$available[] = array( 'id' => $section, 'title' => $titles[ $section ], 'settings' => $count );
		}
		return array(
			'sections'           => $sections,
			'total'              => $total,
			'available_sections' => $available,
		);
	}

	public static function describe( string $id, bool $include_value = true ): ?array {
		$catalog = self::catalog();
		if ( ! isset( $catalog[ $id ] ) ) {
			return null;
		}
		$described = $catalog[ $id ];
		if ( $include_value ) {
			$described['value'] = self::get_value( $described );
		}
		return $described;
	}

	/** @return mixed */
	public static function get_value( array $described ) {
		$id    = $described['id'];
		$multi = (bool) preg_match( '/^([^\[]+)\[([^\]]+)\]$/', $id, $m );
		if ( $multi ) {
			// A WP_Customize_Setting snapshots the whole `base` array when it is built, so after a write it
			// would still report the old value: read the stored array itself.
			$root = 'option' === $described['storage'] ? get_option( $m[1] ) : get_theme_mod( $m[1] );
			if ( is_array( $root ) && array_key_exists( $m[2], $root ) ) {
				return $root[ $m[2] ];
			}
		}
		$manager = self::manager();
		$setting = $manager->get_setting( $id );
		if ( $setting ) {
			return $setting->value();
		}
		if ( $multi ) {
			return $described['default'];
		}
		return 'option' === $described['storage'] ? get_option( $id, $described['default'] ) : get_theme_mod( $id, $described['default'] );
	}

	/**
	 * Every color setting with its section, label and current value, for the accessibility check's contrast pairs.
	 * Core's background_color is stored without "#"; it is returned with one.
	 *
	 * @return array[] id, label, section, section_title, value.
	 */
	public static function colors(): array {
		$out = array();
		foreach ( self::catalog() as $id => $described ) {
			if ( 'color' !== $described['type'] ) {
				continue;
			}
			$value = self::get_value( $described );
			$value = is_scalar( $value ) ? trim( (string) $value ) : '';
			if ( preg_match( '/^[0-9a-f]{3}([0-9a-f]{3})?$/i', $value ) ) {
				$value = '#' . $value;
			}
			$out[] = array(
				'id'            => (string) $id,
				'label'         => $described['label'],
				'section'       => $described['section'],
				'section_title' => $described['section_title'],
				'value'         => $value,
			);
		}
		return $out;
	}

	/* ---------- writes ---------- */

	/**
	 * Validates, sanitizes and stores several settings; nothing is written unless every id and value is valid.
	 *
	 * @param array<string, mixed> $settings id => new value
	 * @return array|WP_Error
	 */
	public static function update( array $settings ) {
		if ( ! self::is_available() ) {
			return new WP_Error( 'wpzoom_no_theme_settings', __( 'The active theme registers no Customizer settings this user may change.', 'wpzoom-ai-connect' ) );
		}
		$plan    = array();
		$unknown = array();
		foreach ( $settings as $id => $value ) {
			$described = self::describe( (string) $id );
			if ( ! $described ) {
				$registered = self::manager()->get_setting( (string) $id );
				if ( $registered && ! $registered->check_capabilities() ) {
					// Kadence gives every setting manage_options, which the WPZOOM AI Connect connection holds for reads only.
					return new WP_Error(
						'wpzoom_setting_forbidden',
						sprintf( /* translators: 1: setting ID, 2: capability name */ __( '"%1$s" exists but needs the "%2$s" capability, which this connection has for reading only. Nothing was changed; the site owner can change it in Appearance → Customize.', 'wpzoom-ai-connect' ), $id, (string) $registered->capability )
					);
				}
				$unknown[ $id ] = self::suggest( (string) $id );
				continue;
			}
			$clean = self::coerce( $described, $value );
			if ( is_wp_error( $clean ) ) {
				return $clean;
			}
			$setting = self::manager()->get_setting( $described['id'] );
			if ( ! $setting && ! empty( $described['sanitize'] ) && is_scalar( $clean ) ) {
				try {
					$clean = call_user_func( $described['sanitize'], $clean );
				} catch ( Throwable $e ) {
					// A sanitizer that needs the Customizer context: keep the coerced value.
				}
			}
			if ( $setting ) {
				$sanitized = $setting->sanitize( $clean );
				if ( is_wp_error( $sanitized ) ) {
					return new WP_Error( 'wpzoom_invalid_value', sprintf( '"%s": %s', $id, $sanitized->get_error_message() ) );
				}
				if ( null === $sanitized && null !== $clean ) {
					return new WP_Error( 'wpzoom_invalid_value', sprintf( /* translators: %s: setting ID */ __( '"%s" rejected the value by its own validation.', 'wpzoom-ai-connect' ), $id ) );
				}
				$clean = $sanitized;
			}
			$plan[] = array( $described, $clean );
		}
		if ( $unknown ) {
			$lines = array();
			foreach ( $unknown as $id => $suggestions ) {
				$lines[] = $suggestions ? sprintf( /* translators: 1: setting ID, 2: comma-separated similar setting IDs */ __( '"%1$s" (did you mean: %2$s)', 'wpzoom-ai-connect' ), $id, implode( ', ', $suggestions ) ) : sprintf( '"%s"', $id );
			}
			return new WP_Error(
				'wpzoom_unknown_setting',
				/* translators: %s: the unknown setting ids, each with suggestions, separated by semicolons. */
				sprintf( __( 'Unknown theme setting(s): %s. Nothing was changed. Use wpzoom/list-theme-settings to see the available ids.', 'wpzoom-ai-connect' ), implode( '; ', $lines ) ),
				array( 'unknown' => $unknown )
			);
		}

		$updated = array();
		foreach ( $plan as list( $described, $clean ) ) {
			$before = self::get_value( $described );
			self::write( $described, $clean );
			$updated[] = array(
				'id'      => $described['id'],
				'label'   => $described['label'],
				'section' => $described['section_title'],
				'before'  => $before,
				'after'   => self::get_value( $described ),
			);
		}
		if ( $updated ) {
			self::after_write();
		}
		return array( 'updated' => $updated, 'preview_url' => home_url( '/' ) );
	}

	/**
	 * What the Customizer does after it saves. Themes that cache their generated CSS rebuild it on
	 * `customize_save_after` (GeneratePress, Blocksy) or on their own hooks (Astra); a direct option write skips
	 * that, so the Customizer preview showed the new value while the front end kept serving the old stylesheet.
	 */
	private static function after_write(): void {
		try {
			do_action( 'customize_save_after', self::manager() ); // phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound -- WordPress core's hook, fired so themes refresh their caches as after a Customizer save.
		} catch ( Throwable $e ) {
			// A listener that needs the real Customizer request; the writes stand.
		}
		if ( defined( 'GENERATE_VERSION' ) ) {
			delete_option( 'generate_dynamic_css_cached_version' ); // GeneratePress rebuilds its cached CSS on the next page load.
		}
		if ( class_exists( 'Astra_Theme_Options' ) && method_exists( 'Astra_Theme_Options', 'refresh' ) ) {
			Astra_Theme_Options::refresh();
			do_action( 'astra_customizer_save' ); // Astra's own after-save hook; its Customizer hooks are not registered on REST requests.
		}
		/**
		 * Fires after WPZOOM AI Connect changed theme settings, for anything that caches derived data.
		 *
		 * @since 0.12.0
		 */
		do_action( 'wpzoom_ai_connect_theme_settings_updated' );
	}

	/** Writes a theme_mod or option, including one level of `base[key]` ids. */
	private static function write( array $described, $value ): void {
		$id = $described['id'];
		if ( preg_match( '/^([^\[]+)\[([^\]]+)\]$/', $id, $m ) ) {
			$base = $m[1];
			$key  = $m[2];
			if ( 'option' === $described['storage'] ) {
				$current         = (array) get_option( $base, array() );
				$current[ $key ] = $value;
				update_option( $base, $current );
			} else {
				$current         = (array) get_theme_mod( $base, array() );
				$current[ $key ] = $value;
				set_theme_mod( $base, $current );
			}
			return;
		}
		if ( 'option' === $described['storage'] ) {
			update_option( $id, $value );
		} else {
			set_theme_mod( $id, $value );
		}
	}

	/** Settable ids that look like the given one, for error messages. */
	public static function suggest( string $id, int $max = 3 ): array {
		$scores = array();
		foreach ( array_keys( self::catalog() ) as $candidate ) {
			similar_text( strtolower( $id ), strtolower( $candidate ), $percent );
			if ( $percent > 45 || false !== strpos( $candidate, $id ) || false !== strpos( $id, $candidate ) ) {
				$scores[ $candidate ] = $percent;
			}
		}
		arsort( $scores );
		return array_slice( array_keys( $scores ), 0, $max );
	}

	/**
	 * Turns an AI-supplied value into what the theme stores, or a WP_Error.
	 *
	 * @param mixed $value
	 * @return mixed|WP_Error
	 */
	private static function coerce( array $described, $value ) {
		$id = $described['id'];
		switch ( $described['type'] ) {
			case 'checkbox':
				if ( is_string( $value ) ) {
					$lower = strtolower( trim( $value ) );
					if ( in_array( $lower, array( 'true', '1', 'yes', 'on', 'block' ), true ) ) {
						$value = true;
					} elseif ( in_array( $lower, array( 'false', '0', 'no', 'off', 'none', '' ), true ) ) {
						$value = false;
					}
				}
				if ( is_int( $value ) && in_array( $value, array( 0, 1 ), true ) ) {
					$value = (bool) $value;
				}
				if ( ! is_bool( $value ) ) {
					return new WP_Error( 'wpzoom_invalid_value', sprintf( /* translators: %s: setting ID */ __( '"%s" is a checkbox: send true or false.', 'wpzoom-ai-connect' ), $id ) );
				}
				$default = $described['default'];
				if ( in_array( $default, array( 'block', 'none' ), true ) ) {
					return $value ? 'block' : 'none';
				}
				if ( in_array( $default, array( 'on', 'off' ), true ) ) {
					return $value ? 'on' : 'off';
				}
				if ( in_array( $default, array( 'yes', 'no' ), true ) ) {
					return $value ? 'yes' : 'no';
				}
				if ( is_int( $default ) || is_string( $default ) && in_array( $default, array( '0', '1' ), true ) ) {
					return $value ? 1 : 0;
				}
				return $value;

			case 'choice':
				$keys = array_column( $described['choices'], 'value' );
				if ( is_string( $value ) || is_numeric( $value ) ) {
					$value = (string) $value;
					if ( in_array( $value, $keys, true ) ) {
						return $value;
					}
					foreach ( $described['choices'] as $choice ) {
						if ( 0 === strcasecmp( $choice['label'], $value ) ) {
							return $choice['value'];
						}
					}
					if ( ! empty( $described['choices_open'] ) && function_exists( 'zoom_customizer_sanitize_font_choice' ) ) {
						$font = zoom_customizer_sanitize_font_choice( $value );
						if ( is_string( $font ) && '' !== $font ) {
							return $font; // Any font the framework knows (1,900+ Google fonts).
						}
						return new WP_Error( 'wpzoom_invalid_value', sprintf( /* translators: 1: setting ID, 2: font name, 3: comma-separated font names */ __( '"%1$s": unknown font "%2$s". Send a standard font, a Google Font name, or one of: %3$s.', 'wpzoom-ai-connect' ), $id, $value, implode( ', ', array_slice( $keys, 0, 20 ) ) ) );
					}
				}
				return new WP_Error( 'wpzoom_invalid_value', sprintf( /* translators: 1: setting ID, 2: comma-separated allowed values */ __( '"%1$s" must be one of: %2$s.', 'wpzoom-ai-connect' ), $id, implode( ', ', array_slice( $keys, 0, 40 ) ) ) );

			case 'color':
				if ( '' === $value || null === $value ) {
					return '';
				}
				if ( is_string( $value ) && preg_match( '/^(rgba?|hsla?)\(/i', trim( $value ) ) ) {
					return trim( $value ); // Some controls store rgba.
				}
				if ( is_string( $value ) && preg_match( '/^(var\(--[\w-]+\)|transparent|inherit|initial|currentcolor)$/i', trim( $value ) ) ) {
					return trim( $value ); // Global color variables (GeneratePress var(--accent), Astra var(--ast-global-color-0)) and CSS keywords.
				}
				$hex = is_string( $value ) ? sanitize_hex_color( trim( $value ) ) : null;
				if ( ! $hex ) {
					return new WP_Error( 'wpzoom_invalid_value', sprintf( /* translators: %s: setting ID */ __( '"%s" needs a hex color like #1e73be.', 'wpzoom-ai-connect' ), $id ) );
				}
				return $hex;

			case 'number':
				if ( ! is_numeric( $value ) ) {
					return new WP_Error( 'wpzoom_invalid_value', sprintf( /* translators: %s: setting ID */ __( '"%s" needs a number.', 'wpzoom-ai-connect' ), $id ) );
				}
				$number = $value + 0;
				if ( isset( $described['min'] ) && $number < $described['min'] ) {
					return new WP_Error( 'wpzoom_invalid_value', sprintf( /* translators: 1: setting ID, 2: minimum value */ __( '"%1$s" must be at least %2$s.', 'wpzoom-ai-connect' ), $id, $described['min'] ) );
				}
				if ( isset( $described['max'] ) && $number > $described['max'] ) {
					return new WP_Error( 'wpzoom_invalid_value', sprintf( /* translators: 1: setting ID, 2: maximum value */ __( '"%1$s" must be at most %2$s.', 'wpzoom-ai-connect' ), $id, $described['max'] ) );
				}
				return $number;

			case 'typography':
				if ( ! is_array( $value ) ) {
					return new WP_Error( 'wpzoom_invalid_value', sprintf( /* translators: %s: setting ID */ __( '"%s" is a typography setting: send an object shaped like its current value.', 'wpzoom-ai-connect' ), $id ) );
				}
				return $value;

			case 'object':
				if ( ! is_array( $value ) ) {
					return new WP_Error( 'wpzoom_invalid_value', sprintf( /* translators: %s: setting ID */ __( '"%s" is a structured setting: send an object shaped like its current value.', 'wpzoom-ai-connect' ), $id ) );
				}
				$default = is_array( $described['default'] ) ? $described['default'] : array();
				$unknown = $default ? array_diff( array_keys( $value ), array_keys( $default ) ) : array();
				if ( $unknown ) {
					return new WP_Error( 'wpzoom_invalid_value', sprintf( /* translators: 1: setting ID, 2: comma-separated unknown keys, 3: comma-separated valid keys */ __( '"%1$s": unknown key(s) %2$s; the value has the keys %3$s.', 'wpzoom-ai-connect' ), $id, implode( ', ', $unknown ), implode( ', ', array_keys( $default ) ) ) );
				}
				return $value;

			case 'image':
				if ( is_numeric( $value ) ) {
					return (int) $value;
				}
				return is_string( $value ) ? esc_url_raw( $value ) : '';
		}
		if ( is_array( $value ) ) {
			return $value;
		}
		return is_string( $value ) ? wp_kses_post( $value ) : $value;
	}

	/* ---------- helpers ---------- */

	/** @return array<int, array{value: string, label: string}> */
	private static function choices( $choices ): array {
		$out = array();
		foreach ( (array) $choices as $value => $label ) {
			if ( is_array( $label ) ) {
				if ( isset( $label['value'] ) && is_scalar( $label['value'] ) ) {
					$value = $label['value']; // A list of { value, label } pairs.
				}
				$label = $label['label'] ?? $label['title'] ?? $label['name'] ?? $label['tooltip'] ?? reset( $label );
			}
			$label = self::text( is_scalar( $label ) ? (string) $label : (string) $value );
			if ( '' === $label || preg_match( '#^(https?:)?//#i', $label ) || preg_match( '/\.(png|jpe?g|svg|gif|webp)$/i', $label ) ) {
				$label = self::humanize( (string) $value );
			}
			$out[] = array( 'value' => (string) $value, 'label' => $label );
		}
		return $out;
	}

	public static function palette_choices(): array {
		if ( ! function_exists( 'wpzoom_get_color_palettes' ) ) {
			return array();
		}
		$out = array();
		foreach ( (array) wpzoom_get_color_palettes() as $palette_id => $palette ) {
			$out[] = array(
				'value'  => (string) $palette_id,
				'label'  => self::text( $palette['label'] ?? $palette_id ),
				'colors' => isset( $palette['colors'] ) && is_array( $palette['colors'] ) ? array_values( $palette['colors'] ) : array(),
			);
		}
		return $out;
	}

	private static function humanize( string $value ): string {
		$words = preg_replace( '/^(wpz_|wpzoom_|inspiro_)/', '', $value );
		$words = trim( preg_replace( '/[_\-\[\]]+/', ' ', $words ) );
		return ucfirst( $words );
	}

	private static function text( $value ): string {
		return trim( wp_strip_all_tags( html_entity_decode( (string) $value ) ) );
	}
}
