<?php
/**
 * Registers the wpzoom/* abilities with the WordPress Abilities API (6.9+). Every MCP client
 * that discovers abilities (WPVibe, Easy MCP AI, the official MCP Adapter, WPZOOM's own
 * connector) sees them as tools.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpzoom_ai_connect_register_categories() {
	if ( wp_has_ability_category( 'wpzoom' ) ) {
		return; // Registered by a WPZOOM theme (framework abilities).
	}
	wp_register_ability_category(
		'wpzoom',
		array(
			'label'       => __( 'WPZOOM', 'wpzoom-ai-connect' ),
			'description' => __( 'Understand and change a WPZOOM theme: settings, color palettes, design patterns and pages.', 'wpzoom-ai-connect' ),
		)
	);
}
add_action( 'wp_abilities_api_categories_init', 'wpzoom_ai_connect_register_categories' );

function wpzoom_ai_connect_register_abilities() {
	$any = array( 'string', 'number', 'integer', 'boolean', 'object', 'array', 'null' );

	$meta = static function ( bool $readonly, bool $idempotent, bool $destructive = false ): array {
		return array(
			'public'       => true,
			'show_in_rest' => true,
			'annotations'  => array(
				'readonly'    => $readonly,
				'destructive' => $destructive,
				'idempotent'  => $idempotent,
			),
			'mcp'          => array( 'public' => true ),
		);
	};

	$can_edit_theme = static function () {
		return current_user_can( 'edit_theme_options' );
	};

	$setting_schema = array(
		'type'                 => 'object',
		'properties'           => array(
			'id'            => array( 'type' => 'string' ),
			'label'         => array( 'type' => 'string' ),
			'description'   => array( 'type' => 'string' ),
			'section'       => array( 'type' => 'string' ),
			'section_title' => array( 'type' => 'string' ),
			'type'          => array(
				'type' => 'string',
				'enum' => array( 'text', 'html', 'checkbox', 'choice', 'color', 'gradient', 'number', 'typography', 'image', 'object' ),
			),
			'storage'       => array( 'type' => 'string' ),
			'default'       => array( 'type' => $any ),
			'value'         => array( 'type' => $any ),
			'choices'       => array(
				'type'  => 'array',
				'items' => array(
					'type'                 => 'object',
					'properties'           => array(
						'value' => array( 'type' => 'string' ),
						'label' => array( 'type' => 'string' ),
					),
					'additionalProperties' => true,
				),
			),
			'min'           => array( 'type' => 'number' ),
			'max'           => array( 'type' => 'number' ),
			'step'          => array( 'type' => 'number' ),
		),
		'additionalProperties' => true,
	);

	wp_register_ability(
		'wpzoom/site-overview',
		array(
			'label'               => __( 'Site overview', 'wpzoom-ai-connect' ),
			'description'         => __( 'Start here. Describes this WordPress site and its WPZOOM theme: theme name and version, site title, the active color palette and header layout, WPZOOM plugins, content counts, and which wpzoom/* abilities to use next.', 'wpzoom-ai-connect' ),
			'category'            => 'wpzoom',
			'execute_callback'    => array( 'WPZOOM_AI_Site', 'overview' ),
			'permission_callback' => static function () {
				return current_user_can( 'edit_theme_options' ) || current_user_can( 'edit_pages' );
			},
			'output_schema'       => array(
				'type'                 => 'object',
				'additionalProperties' => true,
			),
			'meta'                => $meta( true, true ),
		)
	);

	// WPZOOM themes with the framework's abilities module register these two first (priority 5); keep theirs.
	if ( ! wp_has_ability( 'wpzoom/list-theme-settings' ) ) wp_register_ability( // phpcs:ignore Generic.ControlStructures.InlineControlStructure.NotAllowed
		'wpzoom/list-theme-settings',
		array(
			'label'               => __( 'List theme settings', 'wpzoom-ai-connect' ),
			'description'         => __( 'Lists the theme\'s Customizer settings grouped by section, each with its id, label, type, allowed choices, default and current value. Filter with "section" (an id from site-overview) or "search" (matches ids, labels and section titles, e.g. "header", "font", "footer color"). Use the ids with wpzoom/update-theme-settings.', 'wpzoom-ai-connect' ),
			'category'            => 'wpzoom',
			'input_schema'        => array(
				'type'                 => 'object',
				'properties'           => array(
					'section'        => array(
						'type'        => 'string',
						'description' => __( 'Only this section id.', 'wpzoom-ai-connect' ),
					),
					'search'         => array(
						'type'        => 'string',
						'description' => __( 'Only settings whose id, label, description or section title contains this text.', 'wpzoom-ai-connect' ),
					),
					'include_values' => array(
						'type'        => 'boolean',
						'default'     => true,
						'description' => __( 'Include each setting\'s current value.', 'wpzoom-ai-connect' ),
					),
				),
				'additionalProperties' => false,
			),
			'output_schema'       => array(
				'type'                 => 'object',
				'properties'           => array(
					'total'              => array( 'type' => 'integer' ),
					'sections'           => array(
						'type'  => 'array',
						'items' => array(
							'type'                 => 'object',
							'properties'           => array(
								'id'       => array( 'type' => 'string' ),
								'title'    => array( 'type' => 'string' ),
								'settings' => array(
									'type'  => 'array',
									'items' => $setting_schema,
								),
							),
							'additionalProperties' => true,
						),
					),
					'available_sections' => array(
						'type'  => 'array',
						'items' => array(
							'type'                 => 'object',
							'additionalProperties' => true,
						),
					),
				),
				'additionalProperties' => true,
			),
			'execute_callback'    => static function ( $input = null ) {
				if ( ! WPZOOM_AI_Theme_Settings::is_available() ) {
					return new WP_Error( 'wpzoom_no_theme_settings', __( 'The active theme does not expose WPZOOM theme settings.', 'wpzoom-ai-connect' ) );
				}
				return WPZOOM_AI_Theme_Settings::all( is_array( $input ) ? $input : array() );
			},
			'permission_callback' => $can_edit_theme,
			'meta'                => $meta( true, true ),
		)
	);

	if ( ! wp_has_ability( 'wpzoom/update-theme-settings' ) ) wp_register_ability( // phpcs:ignore Generic.ControlStructures.InlineControlStructure.NotAllowed
		'wpzoom/update-theme-settings',
		array(
			'label'               => __( 'Update theme settings', 'wpzoom-ai-connect' ),
			'description'         => __( 'Changes one or more theme settings at once: colors, the global color palette, header layout, fonts, footer, slider and more. Send an object of setting id => new value using ids and allowed values from wpzoom/list-theme-settings (checkboxes take true/false, choices take one of their values, colors take hex). All ids are validated before anything is written. Returns before/after values and a preview URL.', 'wpzoom-ai-connect' ),
			'category'            => 'wpzoom',
			'input_schema'        => array(
				'type'                 => 'object',
				'properties'           => array(
					'settings' => array(
						'type'                 => 'object',
						'description'          => __( 'Setting id => new value.', 'wpzoom-ai-connect' ),
						'additionalProperties' => array( 'type' => $any ),
					),
				),
				'required'             => array( 'settings' ),
				'additionalProperties' => false,
			),
			'output_schema'       => array(
				'type'                 => 'object',
				'properties'           => array(
					'updated'     => array(
						'type'  => 'array',
						'items' => array(
							'type'                 => 'object',
							'additionalProperties' => true,
						),
					),
					'preview_url' => array( 'type' => 'string' ),
				),
				'additionalProperties' => true,
			),
			'execute_callback'    => static function ( $input = null ) {
				$settings = is_array( $input ) && isset( $input['settings'] ) && is_array( $input['settings'] ) ? $input['settings'] : array();
				if ( ! $settings ) {
					return new WP_Error( 'wpzoom_no_settings', __( 'Send at least one setting in "settings".', 'wpzoom-ai-connect' ) );
				}
				return WPZOOM_AI_Theme_Settings::update( $settings );
			},
			'permission_callback' => $can_edit_theme,
			'meta'                => $meta( false, true, true ),
		)
	);

	wp_register_ability(
		'wpzoom/list-patterns',
		array(
			'label'               => __( 'List design patterns', 'wpzoom-ai-connect' ),
			'description'         => __( 'Lists the ready-made design sections (block patterns) the active theme ships, plus WPZOOM pattern plugins: hero, about, services, pricing tables, FAQ, team, testimonials, contact, call to action and more. Each has a slug for wpzoom/insert-pattern. Optional "search" filters by title, description or keyword.', 'wpzoom-ai-connect' ),
			'category'            => 'wpzoom',
			'input_schema'        => array(
				'type'                 => 'object',
				'properties'           => array(
					'search' => array( 'type' => 'string' ),
				),
				'additionalProperties' => false,
			),
			'output_schema'       => array(
				'type'                 => 'object',
				'properties'           => array(
					'count'    => array( 'type' => 'integer' ),
					'patterns' => array(
						'type'  => 'array',
						'items' => array(
							'type'                 => 'object',
							'properties'           => array(
								'slug'        => array( 'type' => 'string' ),
								'title'       => array( 'type' => 'string' ),
								'description' => array( 'type' => 'string' ),
								'keywords'    => array(
									'type'  => 'array',
									'items' => array( 'type' => 'string' ),
								),
								'categories'  => array(
									'type'  => 'array',
									'items' => array( 'type' => 'string' ),
								),
								'source'      => array( 'type' => 'string' ),
							),
							'additionalProperties' => true,
						),
					),
				),
				'additionalProperties' => true,
			),
			'execute_callback'    => static function ( $input = null ) {
				$patterns = WPZOOM_AI_Patterns::all( is_array( $input ) && isset( $input['search'] ) ? (string) $input['search'] : '' );
				return array(
					'count'    => count( $patterns ),
					'patterns' => $patterns,
				);
			},
			'permission_callback' => static function () {
				return current_user_can( 'edit_pages' );
			},
			'meta'                => $meta( true, true ),
		)
	);

	wp_register_ability(
		'wpzoom/insert-pattern',
		array(
			'label'               => __( 'Insert design pattern', 'wpzoom-ai-connect' ),
			'description'         => __( 'Adds a ready-made design section (a pattern slug from wpzoom/list-patterns) to a page as blocks, at the end or the start, or creates a new draft page with it when "new_page_title" is given instead of "page_id". The pattern carries demo text and images; edit the page afterwards to replace them. Never deletes existing content.', 'wpzoom-ai-connect' ),
			'category'            => 'wpzoom',
			'input_schema'        => array(
				'type'                 => 'object',
				'properties'           => array(
					'pattern'        => array(
						'type'        => 'string',
						'description' => __( 'Pattern slug, e.g. wpzoom/faq-accordion.', 'wpzoom-ai-connect' ),
					),
					'page_id'        => array(
						'type'        => 'integer',
						'description' => __( 'Existing page (or post) to add the section to.', 'wpzoom-ai-connect' ),
					),
					'new_page_title' => array(
						'type'        => 'string',
						'description' => __( 'Create a new draft page with this title instead of using page_id.', 'wpzoom-ai-connect' ),
					),
					'position'       => array(
						'type'    => 'string',
						'enum'    => array( 'append', 'prepend' ),
						'default' => 'append',
					),
				),
				'required'             => array( 'pattern' ),
				'additionalProperties' => false,
			),
			'output_schema'       => array(
				'type'                 => 'object',
				'properties'           => array(
					'page_id'      => array( 'type' => 'integer' ),
					'title'        => array( 'type' => 'string' ),
					'status'       => array( 'type' => 'string' ),
					'created'      => array( 'type' => 'boolean' ),
					'blocks_added' => array( 'type' => 'integer' ),
					'edit_url'     => array( 'type' => 'string' ),
					'preview_url'  => array( 'type' => 'string' ),
				),
				'additionalProperties' => true,
			),
			'execute_callback'    => static function ( $input = null ) {
				return WPZOOM_AI_Patterns::insert( is_array( $input ) ? $input : array() );
			},
			'permission_callback' => static function ( $input = null ) {
				if ( is_array( $input ) && ! empty( $input['page_id'] ) ) {
					return current_user_can( 'edit_post', (int) $input['page_id'] );
				}
				return current_user_can( 'edit_pages' );
			},
			'meta'                => $meta( false, false ),
		)
	);
}
add_action( 'wp_abilities_api_init', 'wpzoom_ai_connect_register_abilities' );

/**
 * Core validates a null input against the schema and rejects it ("not of type object").
 * For wpzoom abilities whose input is entirely optional, treat a missing input as valid.
 *
 * @param true|WP_Error $validity
 * @param mixed         $input
 * @param string        $name
 * @return true|WP_Error
 */
function wpzoom_ai_connect_allow_missing_input( $validity, $input, $name ) {
	if ( true === $validity || null !== $input || 0 !== strpos( (string) $name, 'wpzoom/' ) ) {
		return $validity;
	}
	$ability = wp_get_ability( $name );
	$schema  = $ability ? $ability->get_input_schema() : array();
	if ( ! empty( $schema ) && empty( $schema['required'] ) ) {
		return true;
	}
	return $validity;
}
add_filter( 'wp_ability_validate_input', 'wpzoom_ai_connect_allow_missing_input', 10, 3 );
