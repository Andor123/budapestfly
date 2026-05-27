<?php
/**
 * Canonical form schema for WPZOOM Forms v2.
 *
 * The new builder stores each form as a JSON document in postmeta key `_wpzf_schema`.
 * This class owns the schema definition, sanitization, defaults, and field-type registry.
 *
 * @package WPZOOM_Forms
 */

defined( 'ABSPATH' ) || exit;

class WPZOOM_Forms_Schema {

	const META_KEY        = '_wpzf_schema';
	const SCHEMA_VERSION  = 2;

	/** Field types supported by the builder. */
	public static function field_types() {
		return array(
			'text'       => array( 'label' => 'Text', 'group' => 'standard', 'icon' => 'text',     'is_input' => true ),
			'name'       => array( 'label' => 'Name',             'group' => 'standard', 'icon' => 'user',     'is_input' => true ),
			'email'      => array( 'label' => 'Email',            'group' => 'standard', 'icon' => 'email',    'is_input' => true ),
			'tel'        => array( 'label' => 'Phone',            'group' => 'standard', 'icon' => 'phone',    'is_input' => true ),
			'url'        => array( 'label' => 'Website / URL',    'group' => 'standard', 'icon' => 'link',     'is_input' => true ),
			'number'     => array( 'label' => 'Number',           'group' => 'standard', 'icon' => 'number',   'is_input' => true ),
			'textarea'   => array( 'label' => 'Message',   'group' => 'standard', 'icon' => 'paragraph','is_input' => true ),
			'select'     => array( 'label' => 'Dropdown',         'group' => 'choice',   'icon' => 'select',   'is_input' => true ),
			'radio'      => array( 'label' => 'Single Choice',  'group' => 'choice',   'icon' => 'radio',    'is_input' => true ),
			'checkboxes' => array( 'label' => 'Checkboxes',       'group' => 'choice',   'icon' => 'checkboxes','is_input' => true ),
			'checkbox'   => array( 'label' => 'Single Checkbox',  'group' => 'choice',   'icon' => 'checkbox', 'is_input' => true ),
			'date'       => array( 'label' => 'Date',             'group' => 'standard', 'icon' => 'date',     'is_input' => true ),
			'hidden'     => array( 'label' => 'Hidden',           'group' => 'advanced', 'icon' => 'hidden',   'is_input' => true ),
			'heading'    => array( 'label' => 'Heading',          'group' => 'layout',   'icon' => 'heading',  'is_input' => false ),
			'paragraph'  => array( 'label' => 'Paragraph',        'group' => 'layout',   'icon' => 'text',     'is_input' => false ),
			'divider'    => array( 'label' => 'Divider',          'group' => 'layout',   'icon' => 'divider',  'is_input' => false ),
		);
	}

	/** Field widths supported. */
	public static function field_widths() {
		return array( 'full', 'half', 'third', 'two-thirds' );
	}

	/** Default empty schema for a brand-new form. */
	public static function defaults() {
		return array(
			'version'  => self::SCHEMA_VERSION,
			'settings' => array(
				'submitLabel'      => __( 'Submit', 'wpzoom-forms' ),
				'submitAlign'      => 'left',
				'labelsPosition'   => 'top',
				'theme'            => 'default',
				'formLayout'       => 'default',
				'honeypot'         => true,
				'showRequiredMark' => true,
			),
			'fields' => array(
				self::make_field( 'name', array( 'label' => __( 'Your Name', 'wpzoom-forms' ), 'required' => true ) ),
				self::make_field( 'email', array( 'label' => __( 'Your Email', 'wpzoom-forms' ), 'required' => true, 'isReplyTo' => true ) ),
				self::make_field( 'textarea', array( 'label' => __( 'Your Message', 'wpzoom-forms' ), 'required' => true, 'rows' => 6 ) ),
			),
		);
	}

	/** Make a single field with reasonable defaults. */
	public static function make_field( $type, $overrides = array() ) {
		$base = array(
			'id'           => self::new_field_id(),
			'type'         => $type,
			'label'        => '',
			'placeholder'  => '',
			'required'     => false,
			'help'         => '',
			'defaultValue' => '',
			'width'        => 'full',
			'cssClass'     => '',
			'customCSS'    => '',
		);

		switch ( $type ) {
			case 'textarea':
				$base['rows']     = 4;
				$base['label']    = __( 'Text', 'wpzoom-forms' );
				break;
			case 'number':
				$base['min']  = null;
				$base['max']  = null;
				$base['step'] = null;
				$base['label'] = __( 'Number', 'wpzoom-forms' );
				break;
			case 'date':
				$base['format']     = 'Y-m-d';
				$base['mode']       = 'single'; // single | multiple | range
				$base['label']      = __( 'Date', 'wpzoom-forms' );
				break;
			case 'select':
			case 'radio':
			case 'checkboxes':
				$base['options'] = array(
					array( 'label' => __( 'Option 1', 'wpzoom-forms' ), 'value' => 'option-1' ),
					array( 'label' => __( 'Option 2', 'wpzoom-forms' ), 'value' => 'option-2' ),
					array( 'label' => __( 'Option 3', 'wpzoom-forms' ), 'value' => 'option-3' ),
				);
				if ( 'select' === $type ) $base['label'] = __( 'Dropdown', 'wpzoom-forms' );
				if ( 'radio' === $type )  $base['label'] = __( 'Single Choice', 'wpzoom-forms' );
				if ( 'checkboxes' === $type ) $base['label'] = __( 'Checkboxes', 'wpzoom-forms' );
				break;
			case 'checkbox':
				$base['label']      = __( 'I agree', 'wpzoom-forms' );
				$base['checkboxText'] = __( 'I agree to the terms', 'wpzoom-forms' );
				break;
			case 'email':
				$base['label']      = __( 'Email', 'wpzoom-forms' );
				$base['isReplyTo']  = false;
				break;
			case 'tel':
				$base['label'] = __( 'Phone', 'wpzoom-forms' );
				break;
			case 'url':
				$base['label'] = __( 'Website', 'wpzoom-forms' );
				break;
			case 'name':
				$base['label'] = __( 'Name', 'wpzoom-forms' );
				break;
			case 'text':
				$base['label']     = __( 'Text', 'wpzoom-forms' );
				$base['isSubject'] = false;
				break;
			case 'hidden':
				$base['label'] = __( 'Hidden Field', 'wpzoom-forms' );
				break;
			case 'heading':
				$base['label']  = '';
				$base['text']   = __( 'Section Name', 'wpzoom-forms' );
				$base['level']  = 'h3';
				break;
			case 'paragraph':
				$base['label'] = '';
				$base['text']  = __( 'Paragraph text describing the next section of the form.', 'wpzoom-forms' );
				break;
			case 'divider':
				$base['label'] = '';
				break;
		}

		return array_merge( $base, $overrides );
	}

	/** Generate a stable random field id (eight chars). */
	public static function new_field_id() {
		try {
			$rand = bin2hex( random_bytes( 4 ) );
		} catch ( Exception $e ) {
			$rand = substr( md5( uniqid( '', true ) ), 0, 8 );
		}
		return 'f_' . $rand;
	}

	/**
	 * Validate + sanitize a schema. Returns a canonical, fully-populated structure.
	 * Bad fields are dropped silently rather than rejected.
	 */
	public static function sanitize( $input ) {
		$defaults = self::defaults();

		if ( ! is_array( $input ) ) {
			$input = array();
		}

		$out = array(
			'version'  => self::SCHEMA_VERSION,
			'settings' => self::sanitize_settings( isset( $input['settings'] ) ? $input['settings'] : array(), $defaults['settings'] ),
			'fields'   => self::sanitize_fields( isset( $input['fields'] ) ? $input['fields'] : array() ),
		);

		return $out;
	}

	private static function sanitize_settings( $in, $defaults ) {
		$in = is_array( $in ) ? $in : array();
		$out = array(
			'submitLabel'      => isset( $in['submitLabel'] ) ? self::clean_string( $in['submitLabel'] ) : $defaults['submitLabel'],
			'submitAlign'      => self::enum( isset( $in['submitAlign'] ) ? $in['submitAlign'] : '', array( 'left', 'center', 'right', 'full' ), $defaults['submitAlign'] ),
			'labelsPosition'   => self::enum( isset( $in['labelsPosition'] ) ? $in['labelsPosition'] : '', array( 'top', 'left', 'hidden' ), $defaults['labelsPosition'] ),
			'theme'            => self::enum( isset( $in['theme'] ) ? $in['theme'] : '', array( 'default', 'minimal', 'modern', 'bold', 'rounded', 'flat' ), $defaults['theme'] ),
			'formLayout'       => self::enum( isset( $in['formLayout'] ) ? $in['formLayout'] : '', array( 'default', 'horizontal', 'compact' ), $defaults['formLayout'] ),
			'honeypot'         => ! empty( $in['honeypot'] ),
			'showRequiredMark' => ! empty( $in['showRequiredMark'] ),
		);
		if ( empty( $out['submitLabel'] ) ) $out['submitLabel'] = $defaults['submitLabel'];
		return $out;
	}

	private static function sanitize_fields( $fields ) {
		if ( ! is_array( $fields ) ) return array();
		$types = self::field_types();
		$out = array();
		$used_ids = array();

		foreach ( $fields as $f ) {
			if ( ! is_array( $f ) ) continue;
			$type = isset( $f['type'] ) ? sanitize_key( $f['type'] ) : '';
			if ( ! isset( $types[ $type ] ) ) continue;

			$id = isset( $f['id'] ) ? sanitize_key( $f['id'] ) : '';
			if ( empty( $id ) || isset( $used_ids[ $id ] ) ) {
				$id = self::new_field_id();
			}
			$used_ids[ $id ] = true;

			$base = self::make_field( $type ); // populates type-specific defaults
			$clean = $base;
			$clean['id']           = $id;
			$clean['type']         = $type;
			$clean['label']        = isset( $f['label'] ) ? self::clean_string( $f['label'] ) : $base['label'];
			$clean['placeholder']  = isset( $f['placeholder'] ) ? self::clean_string( $f['placeholder'] ) : '';
			$clean['required']     = ! empty( $f['required'] );
			$clean['help']         = isset( $f['help'] ) ? self::clean_string( $f['help'] ) : '';
			if ( $type === 'checkboxes' ) {
				$raw_defaults = isset( $f['defaultValue'] ) && is_array( $f['defaultValue'] ) ? $f['defaultValue'] : array();
				$clean['defaultValue'] = array_values( array_map( 'sanitize_text_field', $raw_defaults ) );
			} else {
				$clean['defaultValue'] = isset( $f['defaultValue'] ) ? self::clean_string( (string) $f['defaultValue'] ) : '';
			}
			$clean['width']        = self::enum( isset( $f['width'] ) ? $f['width'] : 'full', self::field_widths(), 'full' );
			$clean['cssClass']     = isset( $f['cssClass'] ) ? sanitize_html_class( $f['cssClass'], '' ) : '';
			$clean['customCSS']    = isset( $f['customCSS'] ) ? wp_strip_all_tags( wp_unslash( $f['customCSS'] ) ) : '';

			switch ( $type ) {
				case 'textarea':
					$clean['rows'] = isset( $f['rows'] ) ? max( 1, min( 20, (int) $f['rows'] ) ) : 4;
					break;
				case 'number':
					$clean['min']  = isset( $f['min'] )  && $f['min']  !== '' ? (float) $f['min']  : null;
					$clean['max']  = isset( $f['max'] )  && $f['max']  !== '' ? (float) $f['max']  : null;
					$clean['step'] = isset( $f['step'] ) && $f['step'] !== '' ? (float) $f['step'] : null;
					break;
				case 'date':
					$clean['format'] = isset( $f['format'] ) ? self::clean_string( $f['format'] ) : 'Y-m-d';
					$clean['mode']   = self::enum( isset( $f['mode'] ) ? $f['mode'] : 'single', array( 'single', 'multiple', 'range' ), 'single' );
					break;
				case 'select':
				case 'radio':
				case 'checkboxes':
					$clean['options'] = self::sanitize_options( isset( $f['options'] ) ? $f['options'] : array() );
					break;
				case 'checkbox':
					$clean['checkboxText'] = isset( $f['checkboxText'] ) ? self::clean_string( $f['checkboxText'] ) : __( 'I agree', 'wpzoom-forms' );
					break;
				case 'email':
					$clean['isReplyTo'] = ! empty( $f['isReplyTo'] );
					break;
				case 'text':
					$clean['isSubject'] = ! empty( $f['isSubject'] );
					break;
				case 'heading':
					$clean['text']  = isset( $f['text'] ) ? self::clean_string( $f['text'] ) : '';
					$clean['level'] = self::enum( isset( $f['level'] ) ? $f['level'] : 'h3', array( 'h2', 'h3', 'h4', 'h5' ), 'h3' );
					break;
				case 'paragraph':
					$clean['text'] = isset( $f['text'] ) ? wp_kses_post( $f['text'] ) : '';
					break;
			}

			$out[] = $clean;
		}

		return $out;
	}

	private static function sanitize_options( $options ) {
		if ( ! is_array( $options ) ) return array();
		$out = array();
		foreach ( $options as $opt ) {
			if ( ! is_array( $opt ) ) continue;
			$label = isset( $opt['label'] ) ? self::clean_string( $opt['label'] ) : '';
			$value = isset( $opt['value'] ) ? self::clean_string( $opt['value'] ) : '';
			if ( $value === '' && $label !== '' ) {
				$value = sanitize_title( $label );
			}
			if ( $label === '' && $value === '' ) continue;
			if ( $label === '' ) $label = $value;
			$out[] = array( 'label' => $label, 'value' => $value );
		}
		return $out;
	}

	private static function clean_string( $val ) {
		if ( ! is_scalar( $val ) ) return '';
		return sanitize_text_field( wp_unslash( (string) $val ) );
	}

	private static function enum( $val, $allowed, $default ) {
		return in_array( $val, $allowed, true ) ? $val : $default;
	}

	/**
	 * Read the schema for a form post id. If the form has no schema yet, returns null
	 * (caller should run migration or use defaults).
	 */
	public static function get_for_form( $form_id ) {
		$raw = get_post_meta( $form_id, self::META_KEY, true );
		if ( empty( $raw ) ) return null;
		if ( is_array( $raw ) ) return self::sanitize( $raw );
		$decoded = json_decode( $raw, true );
		if ( ! is_array( $decoded ) ) return null;
		return self::sanitize( $decoded );
	}

	/** Save schema as JSON in postmeta. */
	public static function save_for_form( $form_id, $schema ) {
		$clean = self::sanitize( $schema );
		// update_post_meta will stripslashes the value internally, so we wp_slash
		// to compensate. Storing as JSON keeps a stable byte-for-byte representation.
		update_post_meta( $form_id, self::META_KEY, wp_slash( wp_json_encode( $clean, JSON_UNESCAPED_UNICODE ) ) );
		return $clean;
	}

	/** Quick check: does this form have a v2 schema saved? */
	public static function form_has_schema( $form_id ) {
		$raw = get_post_meta( $form_id, self::META_KEY, true );
		return ! empty( $raw );
	}

	/** Find the field marked as reply-to email, or the first email field. */
	public static function reply_to_field( $schema ) {
		if ( empty( $schema['fields'] ) ) return null;
		$first_email = null;
		foreach ( $schema['fields'] as $f ) {
			if ( $f['type'] === 'email' ) {
				if ( ! empty( $f['isReplyTo'] ) ) return $f;
				if ( $first_email === null ) $first_email = $f;
			}
		}
		return $first_email;
	}

	/** Find the field marked as subject, or null. */
	public static function subject_field( $schema ) {
		if ( empty( $schema['fields'] ) ) return null;
		foreach ( $schema['fields'] as $f ) {
			if ( $f['type'] === 'text' && ! empty( $f['isSubject'] ) ) return $f;
		}
		return null;
	}
}
