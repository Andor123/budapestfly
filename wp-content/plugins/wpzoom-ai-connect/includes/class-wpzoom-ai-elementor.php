<?php
/**
 * Pages built with Elementor's classic widgets (sections, columns, containers and widgets such as Heading,
 * Text Editor, Button, Image and the WPZOOM Elementor Addons), read as an outline and edited field by field.
 *
 * Only a widget's content fields are offered (its Content tab in the editor: text, links, images, galleries,
 * numbers and lists such as team-member profiles or pricing features), plus a section's background image or
 * video. Styling stays in Elementor. Which fields exist comes from Elementor's own control definitions, so
 * Elementor Pro and add-on widgets work too. Saving goes through Elementor's document save, which keeps a
 * revision, filters HTML for users without unfiltered_html and regenerates the page's CSS.
 *
 * Elementor 4's atomic elements are listed but not edited here: Elementor's own abilities handle those.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Elementor {

	/** Control types whose values are content an AI may change. */
	const CONTENT_TYPES = array( 'text', 'textarea', 'wysiwyg', 'url', 'media', 'gallery', 'number', 'repeater' );

	/** Section and container settings shown although Elementor puts them in the Style tab. */
	const BACKGROUND_FIELDS = array( 'background_image', 'background_video_link' );

	/** Longest text returned per field in the outline. */
	const MAX_TEXT = 600;

	public static function available(): bool {
		return did_action( 'elementor/loaded' ) && class_exists( '\Elementor\Plugin' );
	}

	private static function unavailable() {
		return new WP_Error( 'wpzoom_no_elementor', __( 'Elementor is not active on this site.', 'wpzoom-ai-connect' ) );
	}

	/** The Elementor document of a post the current user may edit, or an error. */
	private static function document( int $post_id ) {
		if ( ! self::available() ) {
			return self::unavailable();
		}
		$post = get_post( $post_id );
		if ( ! $post || 'trash' === $post->post_status ) {
			return new WP_Error( 'wpzoom_not_found', sprintf( /* translators: %d: post ID */ __( 'No post with id %d.', 'wpzoom-ai-connect' ), $post_id ), array( 'status' => 404 ) );
		}
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return new WP_Error( 'wpzoom_forbidden', __( 'You cannot edit this page.', 'wpzoom-ai-connect' ), array( 'status' => 403 ) );
		}
		$document = \Elementor\Plugin::$instance->documents->get( $post_id, false );
		if ( ! $document || ! $document->is_built_with_elementor() ) {
			return new WP_Error( 'wpzoom_not_elementor', sprintf( /* translators: %d: post ID */ __( 'Post %d is not built with Elementor; edit it with update_content.', 'wpzoom-ai-connect' ), $post_id ), array( 'status' => 400 ) );
		}
		return $document;
	}

	private static function is_atomic( array $element ): bool {
		return 0 === strpos( (string) ( $element['elType'] ?? '' ), 'e-' ) || 0 === strpos( (string) ( $element['widgetType'] ?? '' ), 'e-' );
	}

	/** An Elementor element object for raw element data, or null for unknown widgets (an add-on that was removed). */
	private static function instance( array $element ) {
		try {
			return \Elementor\Plugin::$instance->elements_manager->create_element_instance( $element );
		} catch ( \Throwable $e ) {
			return null;
		}
	}

	/** The content controls of an element: name => control, only those that apply with its current settings. */
	private static function controls( $instance, array $element ): array {
		$out      = array();
		$settings = $instance->get_settings();
		$is_box   = in_array( $element['elType'] ?? '', array( 'section', 'container', 'column' ), true );
		foreach ( $instance->get_controls() as $name => $control ) {
			$type = $control['type'] ?? '';
			if ( ! in_array( $type, self::CONTENT_TYPES, true ) || '_' === substr( (string) $name, 0, 1 ) ) {
				continue; // _element_id, _css_classes and other Advanced-tab fields
			}
			$content_tab = 'content' === ( $control['tab'] ?? 'content' );
			if ( ! $content_tab && ! ( $is_box && in_array( $name, self::BACKGROUND_FIELDS, true ) ) ) {
				continue;
			}
			if ( ! empty( $control['responsive'] ) && isset( $control['parent'] ) ) {
				continue; // _tablet / _mobile copies of a field
			}
			if ( ! $instance->is_control_visible( $control, $settings ) ) {
				continue;
			}
			$out[ $name ] = $control;
		}
		return $out;
	}

	private static function short( string $text ): string {
		$text = trim( $text );
		return strlen( $text ) > self::MAX_TEXT ? substr( $text, 0, self::MAX_TEXT ) . '…' : $text;
	}

	/** A field value as the outline shows it. */
	private static function show( array $control, $value ) {
		switch ( $control['type'] ) {
			case 'url':
				return is_array( $value ) ? (string) ( $value['url'] ?? '' ) : (string) $value;
			case 'media':
				if ( is_array( $value ) && ! empty( $value['url'] ) && empty( $value['id'] ) && false !== strpos( (string) $value['url'], 'elementor/assets/images/placeholder' ) ) {
					return null; // Elementor's grey placeholder: no image chosen
				}
				return is_array( $value ) && ! empty( $value['url'] ) ? array_filter( array( 'url' => $value['url'], 'id' => (int) ( $value['id'] ?? 0 ) ?: null, 'alt' => $value['alt'] ?? null ) ) : null;
			case 'gallery':
				return array_values( array_map( static function ( $item ) {
					return array_filter( array( 'url' => $item['url'] ?? '', 'id' => (int) ( $item['id'] ?? 0 ) ?: null ) );
				}, is_array( $value ) ? $value : array() ) );
			case 'repeater':
				$items = array();
				foreach ( is_array( $value ) ? $value : array() as $item ) {
					$row = array( '_id' => (string) ( $item['_id'] ?? '' ) );
					foreach ( (array) ( $control['fields'] ?? array() ) as $field_name => $field ) {
						$field = is_array( $field ) ? $field : array();
						$name  = is_string( $field_name ) ? $field_name : (string) ( $field['name'] ?? '' );
						if ( '' === $name || ! in_array( $field['type'] ?? '', self::CONTENT_TYPES, true ) || 'repeater' === $field['type'] ) {
							continue;
						}
						$shown = self::show( $field, $item[ $name ] ?? ( $field['default'] ?? '' ) );
						if ( null !== $shown && '' !== $shown && array() !== $shown ) {
							$row[ $name ] = $shown;
						}
					}
					$items[] = $row;
				}
				return $items;
			case 'wysiwyg':
			case 'textarea':
			case 'text':
				return self::short( is_scalar( $value ) ? (string) $value : '' );
			default:
				return is_scalar( $value ) ? $value : null;
		}
	}

	private static function label( $instance, array $element ): string {
		if ( 'widget' === ( $element['elType'] ?? '' ) ) {
			return method_exists( $instance, 'get_title' ) ? (string) $instance->get_title() : (string) ( $element['widgetType'] ?? 'Widget' );
		}
		return ucfirst( (string) ( $element['elType'] ?? 'element' ) );
	}

	/** One element of the outline, with its fields, or null when it has nothing to show. */
	private static function describe( array $element, bool $include_empty ) {
		if ( self::is_atomic( $element ) ) {
			return array(
				'id'     => (string) $element['id'],
				'type'   => (string) ( $element['widgetType'] ?? $element['elType'] ),
				'atomic' => true,
			);
		}
		$instance = self::instance( $element );
		if ( ! $instance ) {
			return array( 'id' => (string) $element['id'], 'type' => (string) ( $element['widgetType'] ?? $element['elType'] ), 'unknown' => __( 'Its widget is not registered (a plugin that adds it is inactive), so it cannot be read or changed.', 'wpzoom-ai-connect' ) );
		}
		$settings = $instance->get_settings();
		$fields   = array();
		foreach ( self::controls( $instance, $element ) as $name => $control ) {
			$value = self::show( $control, $settings[ $name ] ?? null );
			if ( ! $include_empty && ( null === $value || '' === $value || array() === $value ) ) {
				continue;
			}
			$fields[ $name ] = $value;
		}
		$is_widget = 'widget' === ( $element['elType'] ?? '' );
		if ( ! $fields && ( ! $is_widget || ! $include_empty ) ) {
			return null;
		}
		$out = array( 'id' => (string) $element['id'], 'type' => (string) ( $is_widget ? $element['widgetType'] : $element['elType'] ), 'label' => self::label( $instance, $element ) );
		if ( $fields ) {
			$out['fields'] = $fields;
		}
		return $out;
	}

	/** Widgets (and boxes with a background) under an element, depth first. */
	private static function flatten( array $elements, bool $include_empty, array &$out, int &$atomic, int $section = 0 ) {
		foreach ( $elements as $index => $element ) {
			if ( ! is_array( $element ) || empty( $element['id'] ) ) {
				continue;
			}
			$number = $section ?: $index + 1;
			$item   = self::describe( $element, $include_empty );
			if ( $item ) {
				$out[] = array( 'section' => $number ) + $item;
				if ( ! empty( $item['atomic'] ) ) {
					++$atomic;
				}
			}
			if ( ! empty( $element['elements'] ) && ! self::is_atomic( $element ) ) {
				self::flatten( $element['elements'], $include_empty, $out, $atomic, $number );
			}
		}
	}

	/* ----- colors, for the accessibility check ----- */

	/**
	 * Text and background color pairs of a page built with Elementor, read from Elementor's own data rather than its
	 * CSS. Each widget's color controls count: a value set on the widget, a global color, or the control's default
	 * global color while Elementor's default colors are on (a heading's Primary, a text's Text, a button's Accent).
	 * Elementor's selectors say whether a control is a text color or a background. A text color is paired with the
	 * background of the same part of the widget, else the widget's own background, else the nearest container,
	 * section or column background, else Site Settings' background, else the page's. Hover states are left out; a
	 * background image, gradient, video or slideshow is returned as "image" so it is listed, not guessed.
	 *
	 * @param int   $post_id The page.
	 * @param array $root    The page's text and background colors, for what Elementor's Site Settings leave unset.
	 * @return array|null { pairs, unknown }, or null when the page is not built with Elementor (or Elementor is off).
	 */
	public static function color_pairs( int $post_id, array $root ): ?array {
		if ( ! self::available() ) {
			return null;
		}
		$document = \Elementor\Plugin::$instance->documents->get( $post_id, false );
		if ( ! $document || ! $document->is_built_with_elementor() ) {
			return null;
		}
		$kits     = \Elementor\Plugin::$instance->kits_manager;
		$defaults = ! method_exists( $kits, 'is_custom_colors_enabled' ) || $kits->is_custom_colors_enabled();
		$globals  = self::global_colors();
		$kit      = self::kit_colors( $globals );
		$context  = array(
			'globals'  => $globals,
			'defaults' => $defaults,
			'root'     => array(
				'text'       => '' !== $kit['text'] ? $kit['text'] : (string) ( $root['text'] ?? '' ),
				'background' => '' !== $kit['background'] ? $kit['background'] : (string) ( $root['background'] ?? '' ), // '' when the page does not say
			),
		);
		$background = array(
			'value' => $context['root']['background'],
			'label' => '' !== $kit['background'] ? __( 'the Site Settings background', 'wpzoom-ai-connect' ) : ( '' !== $context['root']['background'] ? __( 'the page background', 'wpzoom-ai-connect' ) : __( 'the page background (not known here)', 'wpzoom-ai-connect' ) ),
			'from'  => '',
		);
		$out = array( 'pairs' => array(), 'unknown' => array() );
		// On the front end Elementor keeps style controls (colors among them) aside to load pages faster; its own CSS
		// generator switches them on like this while it reads widgets, and so does this.
		$performance = '\Elementor\Core\Frontend\Performance';
		$switch      = class_exists( $performance ) && method_exists( $performance, 'set_use_style_controls' );
		$was         = $switch && method_exists( $performance, 'is_use_style_controls' ) ? $performance::is_use_style_controls() : false;
		if ( $switch ) {
			$performance::set_use_style_controls( true );
		}
		try {
			self::walk_colors( (array) $document->get_elements_data(), $background, $context, $out );
		} finally {
			if ( $switch ) {
				$performance::set_use_style_controls( $was );
			}
		}
		return $out;
	}

	/** The kit's global colors (Site Settings → Global Colors): id => [ color, title ]. */
	private static function global_colors(): array {
		$out = array();
		try {
			$kit = \Elementor\Plugin::$instance->kits_manager->get_active_kit_for_frontend();
			foreach ( array( 'system_colors', 'custom_colors' ) as $key ) {
				foreach ( (array) $kit->get_settings_for_display( $key ) as $color ) {
					if ( is_array( $color ) && isset( $color['_id'], $color['color'] ) ) {
						$out[ (string) $color['_id'] ] = array( 'color' => (string) $color['color'], 'title' => (string) ( $color['title'] ?? $color['_id'] ) );
					}
				}
			}
		} catch ( \Throwable $e ) {
			// no kit: colors set on widgets are still read
		}
		return $out;
	}

	/** The body text and background colors from Site Settings, when set. */
	private static function kit_colors( array $globals ): array {
		$out = array( 'text' => '', 'background' => '' );
		try {
			$kit      = \Elementor\Plugin::$instance->kits_manager->get_active_kit_for_frontend();
			$settings = (array) $kit->get_settings();
			$refs     = (array) ( $settings['__globals__'] ?? array() );
			$pick     = static function ( string $name ) use ( $settings, $refs, $globals ) {
				if ( ! empty( $refs[ $name ] ) ) {
					return self::global_value( (string) $refs[ $name ], $globals )['color'] ?? '';
				}
				return is_string( $settings[ $name ] ?? null ) ? (string) $settings[ $name ] : '';
			};
			$out['text'] = $pick( 'body_color' );
			if ( 'classic' === ( $settings['body_background_background'] ?? '' ) && empty( $settings['body_background_image']['url'] ) ) {
				$out['background'] = $pick( 'body_background_color' );
			}
		} catch ( \Throwable $e ) {
			// no kit
		}
		return $out;
	}

	/** A global color reference (globals/colors?id=primary) as [ color, title ], or null. */
	private static function global_value( string $ref, array $globals ): ?array {
		if ( ! preg_match( '/[?&]id=([a-z0-9_-]+)/i', $ref, $m ) ) {
			return null;
		}
		return $globals[ $m[1] ] ?? null;
	}

	/**
	 * A color control's value: [ value, global ] (global: the global color's title, or ''), or null when it inherits.
	 */
	private static function color_value( array $controls, array $settings, string $name, array $context ): ?array {
		$refs = (array) ( $settings['__globals__'] ?? array() );
		if ( ! empty( $refs[ $name ] ) ) {
			$global = self::global_value( (string) $refs[ $name ], $context['globals'] );
			return $global ? array( 'value' => $global['color'], 'global' => $global['title'] ) : null;
		}
		$value = $settings[ $name ] ?? '';
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			return array( 'value' => trim( $value ), 'global' => '' );
		}
		// The control's own default global color applies until the user picks something (and the default colors are on).
		$default = $controls[ $name ]['global']['default'] ?? '';
		if ( $context['defaults'] && ! array_key_exists( $name, $refs ) && is_string( $default ) && '' !== $default ) {
			$global = self::global_value( $default, $context['globals'] );
			return $global ? array( 'value' => $global['color'], 'global' => $global['title'] ) : null;
		}
		return null;
	}

	/**
	 * The background an element sets for what is inside it (a container's, or a widget's Advanced-tab background):
	 * [ value, label, from ], "image" as the value for an image, gradient, video or slideshow, or null for none.
	 */
	private static function own_background( array $element, $instance, array $settings, array $context, string $prefix ): ?array {
		$type = (string) ( $settings[ $prefix . 'background_background' ] ?? '' );
		$id   = (string) $element['id'];
		if ( in_array( $type, array( 'gradient', 'video', 'slideshow' ), true ) || ( 'classic' === $type && ! empty( $settings[ $prefix . 'background_image' ]['url'] ) ) ) {
			return array( 'value' => 'image', 'label' => __( 'a background image, gradient or video', 'wpzoom-ai-connect' ), 'from' => $id );
		}
		if ( 'classic' !== $type ) {
			return null;
		}
		$color = self::color_value( $instance->get_controls(), $settings, $prefix . 'background_color', $context );
		if ( ! $color ) {
			return null;
		}
		return array(
			'value'  => $color['value'],
			'label'  => sprintf( 'the %s background (element %s)', self::label( $instance, $element ), $id ),
			'from'   => $id,
			'global' => $color['global'],
		);
	}

	private static function walk_colors( array $elements, array $background, array $context, array &$out ): void {
		foreach ( $elements as $element ) {
			if ( ! is_array( $element ) || empty( $element['id'] ) || self::is_atomic( $element ) ) {
				continue;
			}
			$instance = self::instance( $element );
			if ( ! $instance ) {
				continue;
			}
			$settings  = (array) $instance->get_settings();
			$is_widget = 'widget' === ( $element['elType'] ?? '' );
			$own       = self::own_background( $element, $instance, $settings, $context, $is_widget ? '_' : '' );
			$here      = $own ?: $background;
			if ( $is_widget ) {
				self::widget_colors( $element, $instance, $settings, $here, $context, $out );
			}
			if ( ! empty( $element['elements'] ) ) {
				self::walk_colors( (array) $element['elements'], $here, $context, $out );
			}
		}
	}

	/** The text color pairs of one widget. */
	private static function widget_colors( array $element, $instance, array $settings, array $background, array $context, array &$out ): void {
		$controls = (array) $instance->get_controls();
		$texts    = array();
		$backs    = array();
		foreach ( $controls as $name => $control ) {
			$name = (string) $name;
			if ( 'color' !== ( $control['type'] ?? '' ) || '_' === substr( $name, 0, 1 ) || preg_match( '/hover|active|focus|overlay|_b$|_tablet$|_mobile$/', $name ) ) {
				continue;
			}
			if ( ( ! empty( $control['responsive'] ) && isset( $control['parent'] ) ) || ! $instance->is_control_visible( $control, $settings ) ) {
				continue;
			}
			foreach ( (array) ( $control['selectors'] ?? array() ) as $selector => $css ) {
				if ( ! preg_match_all( '/(?:^|;)\s*([a-z-]+)\s*:\s*\{\{VALUE\}\}/', (string) $css, $m ) ) {
					continue;
				}
				$where = trim( str_replace( '{{WRAPPER}}', '', explode( ',', (string) $selector )[0] ) );
				if ( in_array( 'color', $m[1], true ) ) {
					$texts[ $name ] = $where;
				} elseif ( array_intersect( array( 'background-color', 'background' ), $m[1] ) ) {
					$backs[ $name ] = $where;
				}
				break;
			}
		}

		$widget   = self::label( $instance, $element );
		$id       = (string) $element['id'];
		$heading  = 'heading' === ( $element['widgetType'] ?? '' );
		$found    = false;
		foreach ( $texts as $name => $where ) {
			$text = self::color_value( $controls, $settings, $name, $context );
			if ( ! $text && 'button_text_color' === $name ) {
				$text = array( 'value' => '#ffffff', 'global' => '' ); // Elementor's own button style
			}
			if ( ! $text ) {
				continue; // inherits: covered by the widget-level pair below
			}
			$found = true;
			// The background of the same part of the widget, the most specific one that contains it.
			$back = null;
			$best = -1;
			foreach ( $backs as $back_name => $back_where ) {
				$contains = '' === $back_where || $back_where === $where || 0 === strpos( $where, $back_where . ' ' );
				if ( ! $contains || strlen( $back_where ) <= $best ) {
					continue;
				}
				$type_key = substr( $back_name, 0, -strlen( '_color' ) ) . '_background';
				$type     = (string) ( $settings[ $type_key ] ?? 'classic' );
				if ( '' === $type ) {
					continue; // that background is off
				}
				if ( 'classic' !== $type ) {
					$back = array( 'value' => 'image', 'label' => 'a gradient', 'from' => $id );
					$best = strlen( $back_where );
					continue;
				}
				$color = self::color_value( $controls, $settings, $back_name, $context );
				if ( $color ) {
					$back = array( 'value' => $color['value'], 'label' => self::control_label( $controls, $back_name ), 'from' => $id, 'global' => $color['global'], 'setting' => $back_name );
					$best = strlen( $back_where );
				}
			}
			if ( ! $back && 'button_text_color' === $name ) {
				$back = array( 'value' => '#69727d', 'label' => __( 'Elementor\'s default button background', 'wpzoom-ai-connect' ), 'from' => $id ); // Elementor's own button style
			}
			$back = $back ?: $background;
			if ( '' === $back['value'] ) {
				continue; // on the page background, which the page does not declare: nothing to compare against
			}
			$size = $heading ? strtolower( (string) ( $settings['header_size'] ?? 'h2' ) ) : '';
			$out['pairs'][] = array(
				'element_id' => $id,
				'widget'     => $widget,
				'text'       => array( 'value' => $text['value'], 'setting' => $name, 'label' => self::control_label( $controls, $name ), 'global' => $text['global'] ),
				'background' => $back,
				'large'      => in_array( $size, array( 'h1', 'h2', 'h3' ), true ) || ( ! $heading && false !== strpos( $name, 'title' ) ),
			);
		}
		// A widget with text that sets no text color of its own inherits the page's, which matters on a background
		// an Elementor element sets.
		if ( ! $found && '' !== $background['from'] && self::has_text( $controls, $settings ) && '' !== $context['root']['text'] ) {
			$out['pairs'][] = array(
				'element_id' => $id,
				'widget'     => $widget,
				'text'       => array( 'value' => $context['root']['text'], 'setting' => '', 'label' => __( 'the page\'s text color', 'wpzoom-ai-connect' ), 'global' => '' ),
				'background' => $background,
				'large'      => $heading,
			);
		}
	}

	private static function control_label( array $controls, string $name ): string {
		$label = (string) ( $controls[ $name ]['label'] ?? '' );
		// Elementor drops control labels on the front end, so the name is made readable: grid_title_style_color → Grid title style.
		return '' !== $label ? wp_strip_all_tags( $label ) : ucfirst( str_replace( '_', ' ', (string) preg_replace( '/_color$/', '', $name ) ) );
	}

	/** Whether a widget shows text: a filled text, textarea or editor field. */
	private static function has_text( array $controls, array $settings ): bool {
		foreach ( $controls as $name => $control ) {
			if ( in_array( $control['type'] ?? '', array( 'text', 'textarea', 'wysiwyg' ), true ) && '_' !== substr( (string) $name, 0, 1 ) && is_string( $settings[ $name ] ?? null ) && '' !== trim( wp_strip_all_tags( $settings[ $name ] ) ) ) {
				return true;
			}
		}
		return false;
	}

	/* ----- abilities ----- */

	/** Posts, pages and Elementor templates (headers, footers, popups...) built with Elementor. */
	public static function pages( array $in = array() ) {
		if ( ! self::available() ) {
			return self::unavailable();
		}
		$per_page = max( 1, min( 50, (int) ( $in['per_page'] ?? 20 ) ) );
		$page     = max( 1, (int) ( $in['page'] ?? 1 ) );
		$args     = array(
			'post_type'      => 'any',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private', 'future' ),
			'meta_key'       => '_elementor_edit_mode', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			'meta_value'     => 'builder', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
			'posts_per_page' => $per_page,
			'paged'          => $page,
			'orderby'        => 'modified',
			'order'          => 'DESC',
		);
		if ( ! empty( $in['search'] ) ) {
			$args['s'] = (string) $in['search'];
		}
		$query = new WP_Query( $args + array( 'post_type' => array_merge( get_post_types( array( 'public' => true ) ), array( 'elementor_library' ) ) ) );
		$front = (int) get_option( 'page_on_front' );
		$items = array();
		foreach ( $query->posts as $post ) {
			if ( ! current_user_can( 'edit_post', $post->ID ) ) {
				continue;
			}
			$item = array(
				'post_id'  => $post->ID,
				'title'    => get_the_title( $post ),
				'type'     => $post->post_type,
				'status'   => $post->post_status,
				'modified' => $post->post_modified,
			);
			if ( 'elementor_library' === $post->post_type ) {
				$item['template_type'] = (string) get_post_meta( $post->ID, '_elementor_template_type', true );
			} else {
				$item['link'] = get_permalink( $post );
			}
			if ( $post->ID === $front ) {
				$item['homepage'] = true;
			}
			$items[] = $item;
		}
		return array(
			'total' => (int) $query->found_posts,
			'page'  => $page,
			'pages' => (int) $query->max_num_pages,
			'items' => $items,
			'note'  => __( 'Templates (type elementor_library) are Elementor Theme Builder parts such as the header, footer or a popup, used across the site.', 'wpzoom-ai-connect' ),
		);
	}

	/** The outline of one Elementor page: every widget with its id and content fields. */
	public static function get( array $in ) {
		$document = self::document( (int) ( $in['post_id'] ?? 0 ) );
		if ( is_wp_error( $document ) ) {
			return $document;
		}
		$post     = $document->get_post();
		$elements = (array) $document->get_elements_data();
		$items    = array();
		$atomic   = 0;
		self::flatten( $elements, ! empty( $in['include_empty'] ), $items, $atomic );

		$out = array(
			'post_id'           => $post->ID,
			'title'             => get_the_title( $post ),
			'link'              => get_permalink( $post ),
			'modified'          => $post->post_modified,
			'elementor_version' => defined( 'ELEMENTOR_VERSION' ) ? ELEMENTOR_VERSION : '',
			'sections'          => count( $elements ),
			'elements'          => $items,
			'how_to_edit'       => __( 'Change fields with wpzoom/update-elementor-page: element id, then field name => new value, and pass "modified" as if_unmodified_since. Text fields take plain text (Text Editor "editor" fields take HTML); links take a URL; images take a media id or an image URL; lists (repeaters) take the full new list, keeping each item\'s _id. Only these content fields can be changed here; colors, fonts and spacing are set in Elementor.', 'wpzoom-ai-connect' ),
		);
		if ( $atomic ) {
			$out['atomic_note'] = sprintf( /* translators: %d: number of elements */ _n( '%d element uses Elementor 4\'s atomic elements, which this tool lists but does not change. Use Elementor\'s own abilities (elementor/manage-elements) for those, or the Elementor editor.', '%d elements use Elementor 4\'s atomic elements, which this tool lists but does not change. Use Elementor\'s own abilities (elementor/manage-elements) for those, or the Elementor editor.', $atomic, 'wpzoom-ai-connect' ), $atomic );
		}
		return $out;
	}

	/** Finds an element by id in raw element data, by reference. */
	private static function &find( array &$elements, string $id ) {
		$none = null;
		foreach ( $elements as &$element ) {
			if ( isset( $element['id'] ) && (string) $element['id'] === $id ) {
				return $element;
			}
			if ( ! empty( $element['elements'] ) && is_array( $element['elements'] ) ) {
				$found = &self::find( $element['elements'], $id );
				if ( null !== $found ) {
					return $found;
				}
			}
		}
		return $none;
	}

	/** A media value from a media id, an image URL, or { id, url, alt }. */
	private static function media( $value, $old ) {
		if ( null === $value || '' === $value ) {
			return array( 'url' => '', 'id' => '' );
		}
		$old = is_array( $old ) ? $old : array();
		if ( is_numeric( $value ) ) {
			$value = array( 'id' => (int) $value );
		} elseif ( is_string( $value ) ) {
			$value = array( 'url' => $value );
		}
		if ( ! is_array( $value ) ) {
			return new WP_Error( 'wpzoom_bad_value', __( 'An image is a media id, an image URL, or { "id", "url", "alt" }.', 'wpzoom-ai-connect' ) );
		}
		$out = array( 'url' => '', 'id' => '', 'alt' => '', 'source' => 'library' );
		$url = ! empty( $value['id'] ) ? wp_get_attachment_url( (int) $value['id'] ) : false;
		if ( ! empty( $value['id'] ) && ! $url && empty( $value['url'] ) ) {
			return new WP_Error( 'wpzoom_bad_value', sprintf( /* translators: %s: media ID */ __( 'No media with id %s.', 'wpzoom-ai-connect' ), $value['id'] ) );
		}
		if ( ! empty( $value['id'] ) && ! $url ) {
			// An image from imported demo content whose file is not in this Media Library: keep it as it was.
			$out = array( 'url' => esc_url_raw( (string) $value['url'] ), 'id' => (int) $value['id'], 'alt' => '', 'source' => 'library' );
		} elseif ( $url ) {
			$out['id']  = (int) $value['id'];
			$out['url'] = $url;
			$out['alt'] = (string) get_post_meta( (int) $value['id'], '_wp_attachment_image_alt', true );
		} elseif ( ! empty( $value['url'] ) ) {
			$url = esc_url_raw( (string) $value['url'] );
			$id  = attachment_url_to_postid( $url );
			$out = array( 'url' => $url, 'id' => $id ?: '', 'alt' => '', 'source' => $id ? 'library' : 'url' );
		}
		if ( isset( $value['alt'] ) ) {
			$out['alt'] = sanitize_text_field( (string) $value['alt'] );
		}
		return $out + array_intersect_key( $old, array( 'size' => true ) );
	}

	/** A new raw value for one field from what the AI sent, merged onto the old value. */
	private static function value( array $control, $value, $old, string $path ) {
		switch ( $control['type'] ) {
			case 'url':
				$old = is_array( $old ) ? $old : array( 'url' => '', 'is_external' => '', 'nofollow' => '' );
				if ( is_array( $value ) ) {
					if ( isset( $value['url'] ) ) {
						$value['url'] = esc_url_raw( (string) $value['url'] );
					}
					foreach ( array( 'is_external', 'nofollow' ) as $flag ) {
						if ( isset( $value[ $flag ] ) ) {
							$value[ $flag ] = $value[ $flag ] ? 'on' : '';
						}
					}
					return array_merge( $old, array_intersect_key( $value, array( 'url' => 1, 'is_external' => 1, 'nofollow' => 1 ) ) );
				}
				// Anchors (#section) and relative paths are valid links; esc_url_raw keeps both.
				return array_merge( $old, array( 'url' => '' === (string) $value ? '' : esc_url_raw( (string) $value ) ) );
			case 'media':
				return self::media( $value, $old );
			case 'gallery':
				if ( ! is_array( $value ) ) {
					return new WP_Error( 'wpzoom_bad_value', sprintf( /* translators: %s: the field's path in the request */ __( '%s: a gallery is a list of media ids or image URLs.', 'wpzoom-ai-connect' ), $path ) );
				}
				$items = array();
				foreach ( $value as $item ) {
					$media = self::media( $item, null );
					if ( is_wp_error( $media ) ) {
						return $media;
					}
					$items[] = array( 'id' => $media['id'], 'url' => $media['url'] );
				}
				return $items;
			case 'number':
				return is_numeric( $value ) ? $value + 0 : ( '' === $value || null === $value ? '' : new WP_Error( 'wpzoom_bad_value', sprintf( /* translators: %s: the field's path in the request */ __( '%s: expected a number.', 'wpzoom-ai-connect' ), $path ) ) );
			case 'repeater':
				return self::repeater( $control, $value, $old, $path );
			default:
				if ( ! is_scalar( $value ) && null !== $value ) {
					return new WP_Error( 'wpzoom_bad_value', sprintf( /* translators: %s: the field's path in the request */ __( '%s: expected text.', 'wpzoom-ai-connect' ), $path ) );
				}
				// Text, textarea and WYSIWYG fields keep the HTML a post may hold (links, emphasis, line breaks)
				// and nothing else: no scripts, event handlers, iframes or styles.
				return wp_kses_post( (string) $value );
		}
	}

	/** The full new list of a repeater: items with an existing _id start from that item, new ones get an id. */
	private static function repeater( array $control, $value, $old, string $path ) {
		if ( ! is_array( $value ) || ( $value && array_keys( $value ) !== range( 0, count( $value ) - 1 ) ) ) {
			return new WP_Error( 'wpzoom_bad_value', sprintf( /* translators: %s: the field's path in the request */ __( '%s: a list is the full new list of items, each an object (keep "_id" for existing items).', 'wpzoom-ai-connect' ), $path ) );
		}
		$by_id  = array();
		$sample = array();
		foreach ( is_array( $old ) ? $old : array() as $item ) {
			if ( isset( $item['_id'] ) ) {
				$by_id[ (string) $item['_id'] ] = $item;
				$sample                         = $sample ?: $item;
			}
		}
		$fields = array();
		foreach ( (array) ( $control['fields'] ?? array() ) as $field_name => $field ) {
			$name = is_string( $field_name ) ? $field_name : (string) ( $field['name'] ?? '' );
			if ( '' !== $name && in_array( $field['type'] ?? '', self::CONTENT_TYPES, true ) && 'repeater' !== $field['type'] ) {
				$fields[ $name ] = $field;
			}
		}
		$out = array();
		foreach ( $value as $index => $item ) {
			if ( ! is_array( $item ) ) {
				return new WP_Error( 'wpzoom_bad_value', sprintf( /* translators: %s: the item's path in the request */ __( '%s: each item is an object of field => value.', 'wpzoom-ai-connect' ), "{$path}[{$index}]" ) );
			}
			$id = isset( $item['_id'] ) ? (string) $item['_id'] : '';
			if ( '' !== $id && ! isset( $by_id[ $id ] ) && isset( $item['_restore'] ) && is_array( $item['_restore'] ) ) {
				// Undo of a removal: the item exactly as it was, icons and styling included.
				$out[] = array( '_id' => $id ) + $item['_restore'];
				continue;
			}
			if ( '' !== $id && ! isset( $by_id[ $id ] ) ) {
				return new WP_Error( 'wpzoom_bad_value', sprintf( /* translators: 1: the item's path in the request, 2: the _id sent */ __( '%1$s: no item with _id %2$s; leave _id out to add a new item.', 'wpzoom-ai-connect' ), "{$path}[{$index}]", $id ) );
			}
			// A new item copies the look of the first existing one (its style settings), then takes the given fields.
			$base = '' !== $id ? $by_id[ $id ] : array_diff_key( $sample, $fields, array( '_id' => 1 ) );
			$row  = $base;
			foreach ( $item as $name => $field_value ) {
				if ( '_id' === $name || '_restore' === $name ) {
					continue;
				}
				if ( ! isset( $fields[ $name ] ) ) {
					return new WP_Error( 'wpzoom_bad_value', sprintf( /* translators: 1: the field's path in the request, 2: the item's fields, comma-separated */ __( '%1$s cannot be changed here. Fields: %2$s.', 'wpzoom-ai-connect' ), "{$path}[{$index}].{$name}", implode( ', ', array_keys( $fields ) ) ) );
				}
				$new = self::value( $fields[ $name ], $field_value, $base[ $name ] ?? null, "{$path}[{$index}].{$name}" );
				if ( is_wp_error( $new ) ) {
					return $new;
				}
				$row[ $name ] = $new;
			}
			$row['_id'] = '' !== $id ? $id : substr( md5( wp_generate_uuid4() ), 0, 7 );
			$out[]      = $row;
		}
		return $out;
	}

	/** The previous value of a field, in the shape update() accepts, for undo. */
	private static function previous( array $control, $old ) {
		switch ( $control['type'] ) {
			case 'url':
				return is_array( $old ) ? array_intersect_key( $old, array( 'url' => 1, 'is_external' => 1, 'nofollow' => 1 ) ) : array( 'url' => '' );
			case 'media':
				return is_array( $old ) && ! empty( $old['url'] ) ? array_filter( array( 'id' => (int) ( $old['id'] ?? 0 ) ?: null, 'url' => $old['url'], 'alt' => (string) ( $old['alt'] ?? '' ) ), static function ( $v ) { return null !== $v; } ) : '';
			case 'gallery':
				return array_values( array_map( static function ( $item ) {
					return ! empty( $item['id'] ) ? (int) $item['id'] : (string) ( $item['url'] ?? '' );
				}, is_array( $old ) ? $old : array() ) );
			case 'repeater':
				$items = array();
				foreach ( is_array( $old ) ? $old : array() as $item ) {
					$row = array( '_id' => (string) ( $item['_id'] ?? '' ) );
					foreach ( (array) ( $control['fields'] ?? array() ) as $field_name => $field ) {
						$name = is_string( $field_name ) ? $field_name : (string) ( $field['name'] ?? '' );
						if ( '' !== $name && array_key_exists( $name, $item ) && in_array( $field['type'] ?? '', self::CONTENT_TYPES, true ) && 'repeater' !== $field['type'] ) {
							$row[ $name ] = self::previous( $field, $item[ $name ] );
						}
					}
					$items[] = $row;
				}
				return $items;
			default:
				return is_scalar( $old ) ? $old : '';
		}
	}

	/**
	 * Changes content fields of widgets on one Elementor page and saves it through Elementor.
	 * Returns what changed and "previous": the same changes with the old values, which undo it.
	 */
	public static function update( array $in ) {
		$post_id  = (int) ( $in['post_id'] ?? 0 );
		$document = self::document( $post_id );
		if ( is_wp_error( $document ) ) {
			return $document;
		}
		$post = $document->get_post();
		if ( ! empty( $in['if_unmodified_since'] ) && (string) $in['if_unmodified_since'] !== $post->post_modified ) {
			return new WP_Error( 'wpzoom_edited_since', sprintf( /* translators: %s: the page's modified date */ __( 'The page was changed since you read it (now modified %s). Read it again with wpzoom/get-elementor-page and redo the change.', 'wpzoom-ai-connect' ), $post->post_modified ), array( 'status' => 409 ) );
		}
		$changes = (array) ( $in['changes'] ?? array() );
		if ( ! $changes ) {
			return new WP_Error( 'wpzoom_nothing', __( 'Pass "changes": a list of { "element_id", "fields": { field: value } }.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
		}

		$elements = (array) $document->get_elements_data();
		$changed  = array();
		$previous = array();
		foreach ( $changes as $n => $change ) {
			$change = (array) $change;
			$id     = (string) ( $change['element_id'] ?? '' );
			$fields = (array) ( $change['fields'] ?? array() );
			$element = &self::find( $elements, $id );
			if ( null === $element ) {
				unset( $element );
				return new WP_Error( 'wpzoom_bad_element', sprintf( /* translators: 1: the change's path in the request, 2: element ID */ __( '%1$s: no element with id "%2$s" on this page. Ids come from wpzoom/get-elementor-page.', 'wpzoom-ai-connect' ), "changes[{$n}]", $id ), array( 'status' => 400 ) );
			}
			if ( self::is_atomic( $element ) ) {
				unset( $element );
				return new WP_Error( 'wpzoom_atomic', sprintf( /* translators: %s: element ID */ __( 'Element %s is an Elementor 4 atomic element; change it with Elementor\'s own abilities (elementor/manage-elements) or in the editor.', 'wpzoom-ai-connect' ), $id ), array( 'status' => 400 ) );
			}
			$instance = self::instance( $element );
			if ( ! $instance ) {
				unset( $element );
				return new WP_Error( 'wpzoom_unknown_widget', sprintf( /* translators: %s: element ID */ __( 'Element %s\'s widget is not registered (the plugin that adds it is inactive).', 'wpzoom-ai-connect' ), $id ), array( 'status' => 400 ) );
			}
			$controls = self::controls( $instance, $element );
			// Fields whose condition is not met right now are still allowed when the widget has them at all.
			$all      = $instance->get_controls();
			$settings = isset( $element['settings'] ) && is_array( $element['settings'] ) ? $element['settings'] : array();
			$defaults = $instance->get_settings();
			$before   = array();
			foreach ( $fields as $name => $value ) {
				$control = $controls[ $name ] ?? null;
				if ( ! $control && isset( $all[ $name ] ) && in_array( $all[ $name ]['type'] ?? '', self::CONTENT_TYPES, true ) && 'content' === ( $all[ $name ]['tab'] ?? 'content' ) ) {
					$control = $all[ $name ];
				}
				if ( ! $control ) {
					$label = self::label( $instance, $element );
					unset( $element );
					return new WP_Error( 'wpzoom_bad_field', sprintf( /* translators: 1: the change's path in the request, 2: field name, 3: element ID, 4: widget name, 5: the element's content fields, comma-separated */ __( '%1$s: "%2$s" is not a content field of element %3$s (%4$s): colors, fonts and spacing are changed in Elementor. Fields: %5$s.', 'wpzoom-ai-connect' ), "changes[{$n}]", $name, $id, $label, implode( ', ', array_keys( $controls ) ) ), array( 'status' => 400 ) );
				}
				$old = $settings[ $name ] ?? ( $defaults[ $name ] ?? null );
				$new = self::value( $control, $value, $old, "changes[{$n}].fields.{$name}" );
				if ( is_wp_error( $new ) ) {
					unset( $element );
					return $new;
				}
				$before[ $name ] = self::previous( $control, $old );
				if ( 'repeater' === $control['type'] && is_array( $old ) ) {
					$kept = array_column( $new, '_id' );
					foreach ( $old as $i => $old_item ) {
						if ( isset( $old_item['_id'] ) && ! in_array( $old_item['_id'], $kept, true ) ) {
							$before[ $name ][ $i ]['_restore'] = array_diff_key( $old_item, array( '_id' => 1 ) );
						}
					}
				}
				$settings[ $name ] = $new;
			}
			$element['settings'] = $settings;
			unset( $element );
			$changed[]  = array( 'element_id' => $id, 'fields' => array_keys( $fields ) );
			$previous[] = array( 'element_id' => $id, 'fields' => $before );
		}

		$saved = $document->save( array( 'elements' => $elements ) );
		if ( false === $saved ) {
			return new WP_Error( 'wpzoom_save_failed', __( 'Elementor did not save the page (it may be locked by someone editing it, or you lack permission).', 'wpzoom-ai-connect' ), array( 'status' => 500 ) );
		}
		clean_post_cache( $post_id );
		$post = get_post( $post_id );
		return array(
			'post_id'  => $post_id,
			'link'     => get_permalink( $post ),
			'modified' => $post->post_modified,
			'changed'  => $changed,
			'previous' => $previous,
			'note'     => __( 'Saved through Elementor: a revision was kept and the page\'s CSS is regenerated on the next visit.', 'wpzoom-ai-connect' ),
		);
	}
}
