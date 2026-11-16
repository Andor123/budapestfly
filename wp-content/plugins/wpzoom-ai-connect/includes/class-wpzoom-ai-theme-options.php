<?php
/**
 * The WPZOOM framework's Theme Options panel (WPZOOM → Theme Options), read and written by an AI.
 *
 * Older WPZOOM themes (Tribune, Indigo, Foodica, Academica, Domino...) and Inspiro Premium keep options there
 * besides the Customizer: homepage sections, featured categories, banners, feeds. The theme's options file
 * is the definition (via option::getOptionsArray()), the framework's option class stores and reads values.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Theme_Options {

	/** Plain text from the definition's HTML labels and descriptions. */
	private static function plain( $value ): string {
		$value = preg_replace( '#<br\s*/?>|</(p|li|div)>#i', ' ', (string) $value );
		return trim( html_entity_decode( wp_strip_all_tags( $value ), ENT_QUOTES, 'UTF-8' ) );
	}

	const TYPES = array( 'checkbox', 'text', 'textarea', 'select', 'select-layout', 'select-page', 'select_page', 'select-category', 'dropdown_categories', 'select-category-multi', 'select-tag-multi', 'upload', 'color' );

	const SKIP_IDS = array( 'misc_export', 'misc_export_widgets', 'misc_debug', 'misc_import', 'misc_import_widgets' );

	/** Options that hold code (header/footer scripts, ad codes, custom CSS): never listed or written. */
	const CODE_ID = '/(^|_)(code|codes|css|js|script|scripts)$|_code_|^ad_|custom_css/';

	public static function available(): bool {
		return class_exists( 'option' ) && method_exists( 'option', 'getOptionsArray' );
	}

	/** id => option definition with tab and section added. */
	private static function definitions(): array {
		static $defs = null;
		if ( null !== $defs ) {
			return $defs;
		}
		$defs = array();
		if ( ! self::available() ) {
			return $defs;
		}
		set_error_handler( static function () { return true; }, E_WARNING | E_NOTICE | E_DEPRECATED ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_set_error_handler
		try {
			$all = (array) option::getOptionsArray();
		} catch ( Throwable $e ) {
			$all = array();
		}
		restore_error_handler();

		$tabs = array();
		foreach ( (array) ( $all['menu'] ?? array() ) as $tab ) {
			if ( isset( $tab['id'], $tab['name'] ) ) {
				$tabs[ 'id' . $tab['id'] ] = (string) $tab['name'];
			}
		}
		foreach ( $all as $key => $rows ) {
			if ( 'menu' === $key || ! is_array( $rows ) ) {
				continue;
			}
			$section = '';
			$group   = '';
			foreach ( $rows as $row ) {
				$type = (string) ( $row['type'] ?? '' );
				if ( 'preheader' === $type ) {
					$section = wp_strip_all_tags( (string) ( $row['name'] ?? '' ) );
					$group   = '';
					continue;
				}
				if ( 'startsub' === $type ) {
					$group = wp_strip_all_tags( (string) ( $row['name'] ?? '' ) );
					continue;
				}
				if ( 'endsub' === $type ) {
					$group = '';
					continue;
				}
				$id = isset( $row['id'] ) ? (string) $row['id'] : '';
				if ( '' === $id || ! in_array( $type, self::TYPES, true ) || in_array( $id, self::SKIP_IDS, true ) || preg_match( self::CODE_ID, $id ) ) {
					continue;
				}
				$row['_tab']     = $tabs[ (string) $key ] ?? (string) $key;
				$row['_section'] = trim( $section . ( '' !== $group ? ' › ' . $group : '' ) );
				$defs[ $id ]     = $row;
			}
		}
		return $defs;
	}

	private static function kind( string $type ): string {
		$map = array(
			'select-page'           => 'page',
			'select_page'           => 'page',
			'select-category'       => 'category',
			'dropdown_categories'   => 'category',
			'select-category-multi' => 'categories',
			'select-tag-multi'      => 'tags',
			'select-layout'         => 'choice',
			'select'                => 'choice',
			'upload'                => 'image',
		);
		return $map[ $type ] ?? $type;
	}

	private static function choices( array $def ): array {
		$out     = array();
		$options = (array) ( $def['options'] ?? array() );
		if ( 'select-layout' === $def['type'] ) {
			foreach ( $options as $value => $label ) {
				$out[] = array( 'value' => (string) $value, 'label' => self::plain( $label ) );
			}
			return $out;
		}
		foreach ( $options as $key => $label ) {
			// The framework's select saves the label itself; associative arrays are rare but keyed by value.
			$out[] = array( 'value' => is_string( $key ) ? $key : (string) $label, 'label' => self::plain( $label ) );
		}
		return $out;
	}

	private static function describe( string $id, array $def ): array {
		$kind  = self::kind( (string) $def['type'] );
		$value = option::get( $id );
		$out   = array(
			'id'          => $id,
			'label'       => self::plain( $def['name'] ?? $id ),
			'tab'         => $def['_tab'],
			'section'     => $def['_section'],
			'type'        => $kind,
			'value'       => 'checkbox' === $kind ? ( 'on' === $value || true === $value || '1' === $value ) : $value,
			'default'     => 'checkbox' === $kind ? ( 'on' === ( $def['std'] ?? '' ) ) : ( $def['std'] ?? '' ),
		);
		$desc = preg_replace( '/\s+/', ' ', self::plain( $def['desc'] ?? '' ) );
		if ( '' !== $desc ) {
			$out['description'] = mb_substr( $desc, 0, 300 );
		}
		if ( 'choice' === $kind ) {
			$out['choices'] = self::choices( $def );
		}
		if ( 'page' === $kind && $value ) {
			$out['value_label'] = get_the_title( (int) $value );
		}
		if ( 'category' === $kind && $value ) {
			$term = get_term( (int) $value );
			$out['value_label'] = $term && ! is_wp_error( $term ) ? $term->name : '';
		}
		return $out;
	}

	public static function all( array $in = array() ) {
		if ( ! self::available() ) {
			return new WP_Error( 'wpzoom_no_theme_options', __( 'The active theme has no WPZOOM Theme Options panel. Use wpzoom/list-theme-settings for its Customizer settings.', 'wpzoom-ai-connect' ) );
		}
		$search = strtolower( trim( (string) ( $in['search'] ?? '' ) ) );
		$tab    = strtolower( trim( (string) ( $in['tab'] ?? '' ) ) );
		$items  = array();
		$tabs   = array();
		foreach ( self::definitions() as $id => $def ) {
			$tabs[ $def['_tab'] ] = ( $tabs[ $def['_tab'] ] ?? 0 ) + 1;
			if ( '' !== $tab && strtolower( $def['_tab'] ) !== $tab ) {
				continue;
			}
			$haystack = strtolower( $id . ' ' . ( $def['name'] ?? '' ) . ' ' . wp_strip_all_tags( (string) ( $def['desc'] ?? '' ) ) . ' ' . $def['_section'] );
			if ( '' !== $search && false === strpos( $haystack, $search ) ) {
				continue;
			}
			$items[] = self::describe( $id, $def );
		}
		return array(
			'theme'   => wp_get_theme()->get( 'Name' ),
			'where'   => 'WPZOOM → Theme Options',
			'tabs'    => $tabs,
			'total'   => count( $items ),
			'options' => array_slice( $items, 0, 150 ),
			'note'    => count( $items ) > 150 ? __( 'Showing 150; narrow with "search" or "tab".', 'wpzoom-ai-connect' ) : __( 'Change values with wpzoom/update-theme-options. Customizer settings are separate: wpzoom/list-theme-settings.', 'wpzoom-ai-connect' ),
		);
	}

	/** Checks and normalises one value for the option's type. */
	private static function coerce( array $def, $value ) {
		$kind = self::kind( (string) $def['type'] );
		switch ( $kind ) {
			case 'checkbox':
				if ( is_string( $value ) ) {
					$value = in_array( strtolower( $value ), array( 'on', 'true', '1', 'yes' ), true );
				}
				return $value ? 'on' : 'off';
			case 'choice':
				foreach ( self::choices( $def ) as $choice ) {
					if ( 0 === strcasecmp( (string) $value, $choice['value'] ) || 0 === strcasecmp( (string) $value, $choice['label'] ) ) {
						return $choice['value'];
					}
				}
				return new WP_Error( 'wpzoom_option_value', sprintf( /* translators: %s: the allowed values, comma-separated */ __( 'Choose one of: %s.', 'wpzoom-ai-connect' ), implode( ', ', wp_list_pluck( self::choices( $def ), 'value' ) ) ) );
			case 'page':
				$id = (int) $value;
				if ( $id && 'page' !== get_post_type( $id ) ) {
					return new WP_Error( 'wpzoom_option_value', sprintf( /* translators: %d: page ID */ __( 'Page %d does not exist.', 'wpzoom-ai-connect' ), $id ) );
				}
				return $id;
			case 'category':
				$id = (int) $value;
				if ( $id && ! term_exists( $id, 'category' ) ) {
					return new WP_Error( 'wpzoom_option_value', sprintf( /* translators: %d: category ID */ __( 'Category %d does not exist.', 'wpzoom-ai-connect' ), $id ) );
				}
				return $id;
			case 'categories':
			case 'tags':
				$taxonomy = 'tags' === $kind ? 'post_tag' : 'category';
				$ids      = array_values( array_filter( array_map( 'intval', (array) $value ) ) );
				foreach ( $ids as $id ) {
					if ( ! term_exists( $id, $taxonomy ) ) {
						return new WP_Error( 'wpzoom_option_value', sprintf( /* translators: 1: term ID, 2: taxonomy name */ __( 'Term %1$d does not exist in %2$s.', 'wpzoom-ai-connect' ), $id, $taxonomy ) );
					}
				}
				return $ids;
			case 'image':
				return esc_url_raw( (string) $value );
			case 'color':
				$color = sanitize_hex_color( (string) $value );
				return $color ? $color : new WP_Error( 'wpzoom_option_value', __( 'Use a hex color such as #1a73e8.', 'wpzoom-ai-connect' ) );
			case 'textarea':
				return wp_kses_post( (string) $value );
			default:
				return wp_kses_post( (string) $value );
		}
	}

	public static function update( array $in ) {
		if ( ! self::available() ) {
			return new WP_Error( 'wpzoom_no_theme_options', __( 'The active theme has no WPZOOM Theme Options panel.', 'wpzoom-ai-connect' ) );
		}
		$settings = isset( $in['options'] ) && is_array( $in['options'] ) ? $in['options'] : array();
		if ( ! $settings ) {
			return new WP_Error( 'wpzoom_option_empty', __( 'Pass "options": { "option_id": value, ... }.', 'wpzoom-ai-connect' ) );
		}
		$defs    = self::definitions();
		$changed = array();
		$errors  = array();
		foreach ( $settings as $id => $value ) {
			$id = (string) $id;
			if ( ! isset( $defs[ $id ] ) ) {
				$close    = array_filter( array_keys( $defs ), static function ( $known ) use ( $id ) { return levenshtein( $known, $id ) <= 3 || false !== strpos( $known, $id ); } );
				$errors[] = $close
					/* translators: 1: option ID, 2: similar option IDs, comma-separated */
					? sprintf( __( '%1$s: unknown option (did you mean %2$s?)', 'wpzoom-ai-connect' ), $id, implode( ', ', array_slice( $close, 0, 3 ) ) )
					/* translators: %s: option ID */
					: sprintf( __( '%s: unknown option', 'wpzoom-ai-connect' ), $id );
				continue;
			}
			$new = self::coerce( $defs[ $id ], $value );
			if ( is_wp_error( $new ) ) {
				$errors[] = "{$id}: " . $new->get_error_message();
				continue;
			}
			$old = option::get( $id );
			option::set( $id, $new );
			$changed[] = array( 'id' => $id, 'label' => self::plain( $defs[ $id ]['name'] ?? $id ), 'from' => $old, 'to' => $new );
		}
		if ( ! $changed ) {
			return new WP_Error( 'wpzoom_option_value', implode( ' ', $errors ) );
		}
		return array(
			'changed' => $changed,
			'errors'  => $errors,
			'where'   => admin_url( 'admin.php?page=wpzoom_options' ),
			'note'    => __( 'Saved and live. A page cache may need a purge before visitors see it.', 'wpzoom-ai-connect' ),
		);
	}
}
