<?php
/**
 * Registers the third wave of wpzoom/* abilities, picked from what WPZOOM support answers most:
 * Additional CSS, the older Theme Options panel, available updates, editing existing Recipe Card recipes, the
 * Yoast SEO or Rank Math fields of a post, the site title and tagline, and a check of a page for common
 * accessibility problems.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpzoom_ai_connect_register_site_abilities() {
	$object = array( 'type' => 'object', 'additionalProperties' => true );
	$meta   = static function ( bool $readonly, bool $idempotent, bool $destructive = false ): array {
		return array(
			'public'       => true,
			'show_in_rest' => true,
			'annotations'  => array( 'readonly' => $readonly, 'destructive' => $destructive, 'idempotent' => $idempotent ),
			'mcp'          => array( 'public' => true ),
		);
	};
	$can = static function ( string $cap ) {
		return static function () use ( $cap ) {
			return current_user_can( $cap );
		};
	};
	$input = static function ( array $properties, array $required = array() ): array {
		$schema = array( 'type' => 'object', 'properties' => $properties, 'additionalProperties' => false );
		if ( $required ) {
			$schema['required'] = $required;
		}
		return $schema;
	};
	$str  = static function ( string $description, array $extra = array() ): array {
		return array_merge( array( 'type' => 'string', 'description' => $description ), $extra );
	};
	$int  = static function ( string $description, array $extra = array() ): array {
		return array_merge( array( 'type' => 'integer', 'description' => $description ), $extra );
	};
	$strs = static function ( string $description ): array {
		return array( 'type' => 'array', 'items' => array( 'type' => 'string' ), 'description' => $description );
	};

	/* ----- Additional CSS ----- */

	wp_register_ability( 'wpzoom/get-custom-css', array(
		'label'               => __( 'Read the Additional CSS', 'wpzoom-ai-connect' ),
		'description'         => __( 'The site\'s Additional CSS (Customizer, or Site Editor styles on block themes): the full text, where it is edited, and the labels of changes the AI added earlier.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array() ),
		'output_schema'       => $object,
		'execute_callback'    => static function () {
			return WPZOOM_AI_CSS::get();
		},
		'permission_callback' => $can( 'edit_theme_options' ),
		'meta'                => $meta( true, true ),
	) );

	wp_register_ability( 'wpzoom/add-custom-css', array(
		'label'               => __( 'Add Additional CSS', 'wpzoom-ai-connect' ),
		'description'         => __( 'Adds CSS to the site\'s Additional CSS under a short label, for small visual changes no theme setting covers (hover colors, spacing, hiding an element). Saving again with the same label replaces that change; empty "css" with a label removes it. CSS a person wrote is not touched, WordPress keeps a revision, and CSS the Customizer would reject (markup, a closing style tag, @import, unbalanced braces) is refused. Live immediately: prefer a theme setting when one exists, check selectors on the real page first, and confirm with the user. Needs WordPress\'s edit_css permission; the site owner can switch this off on the AI Connect screen.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input(
			array(
				'label' => $str( __( 'Short name for this change, e.g. "Menu hover color"', 'wpzoom-ai-connect' ), array( 'maxLength' => 80 ) ),
				'css'   => $str( __( 'The CSS rules to add (empty to remove the change with this label)', 'wpzoom-ai-connect' ) ),
			),
			array( 'label', 'css' )
		),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_CSS::add( (array) $in );
		},
		'permission_callback' => $can( 'edit_css' ),
		'meta'                => $meta( false, true, true ),
	) );

	/* ----- custom post types ----- */

	wp_register_ability( 'wpzoom/list-post-types', array(
		'label'               => __( 'List the custom post types open to the AI', 'wpzoom-ai-connect' ),
		'description'         => __( 'The custom post types the site owner opened on the AI Connect screen, each with its access (read or edit), REST route, supported fields and taxonomies. Types not listed are closed.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array() ),
		'output_schema'       => $object,
		'execute_callback'    => static function () {
			return array( 'post_types' => WPZOOM_AI_Post_Types::list_open() );
		},
		'permission_callback' => $can( 'read' ),
		'meta'                => $meta( true, true ),
	) );

	/* ----- WPZOOM → Theme Options ----- */

	// A WPZOOM theme with the framework's abilities module registers these itself.
	if ( WPZOOM_AI_Theme_Options::available() && ! wp_has_ability( 'wpzoom/list-theme-options' ) ) {
		wp_register_ability( 'wpzoom/list-theme-options', array(
			'label'               => __( 'List Theme Options', 'wpzoom-ai-connect' ),
			'description'         => __( 'The options in the theme\'s WPZOOM → Theme Options panel (separate from the Customizer): homepage sections, featured categories and slider, banners, feeds and more on WPZOOM themes such as Tribune, Indigo, Foodica, Academica and Inspiro Premium. Each with id, label, tab, section, type, choices and current value. Filter with search or tab.', 'wpzoom-ai-connect' ),
			'category'            => 'wpzoom',
			'input_schema'        => $input(
				array(
					'search' => $str( __( 'Words to look for in the option name, id or description', 'wpzoom-ai-connect' ) ),
					'tab'    => $str( __( 'Only this tab, e.g. "Homepage"', 'wpzoom-ai-connect' ) ),
				)
			),
			'output_schema'       => $object,
			'execute_callback'    => static function ( $in ) {
				return WPZOOM_AI_Theme_Options::all( (array) $in );
			},
			'permission_callback' => $can( 'manage_options' ),
			'meta'                => $meta( true, true ),
		) );

		wp_register_ability( 'wpzoom/update-theme-options', array(
			'label'               => __( 'Change Theme Options', 'wpzoom-ai-connect' ),
			'description'         => __( 'Changes options in the WPZOOM → Theme Options panel. Pass { "option_id": value }: true/false for checkboxes, one of the listed choices, a page or category id, a list of category ids, a URL for images, a hex color. Live immediately, so confirm with the user first.', 'wpzoom-ai-connect' ),
			'category'            => 'wpzoom',
			'input_schema'        => $input(
				array( 'options' => array( 'type' => 'object', 'additionalProperties' => true, 'description' => __( 'Option id => new value, ids from wpzoom/list-theme-options', 'wpzoom-ai-connect' ) ) ),
				array( 'options' )
			),
			'output_schema'       => $object,
			'execute_callback'    => static function ( $in ) {
				return WPZOOM_AI_Theme_Options::update( (array) $in );
			},
			'permission_callback' => $can( 'manage_options' ),
			'meta'                => $meta( false, true, true ),
		) );
	}

	/* ----- updates ----- */

	wp_register_ability( 'wpzoom/list-updates', array(
		'label'               => __( 'List available updates', 'wpzoom-ai-connect' ),
		'description'         => __( 'The themes and plugins with a newer version available (with the active theme always listed), WordPress and PHP versions, and why an update cannot be installed when that is the case (a missing WPZOOM license, PHP or WordPress too old). Read only: the user installs updates in Dashboard → Updates. Lists themes only for users who can update themes and plugins only for users who can update plugins. Pass refresh: true to ask the update servers again instead of using what WordPress checked in the last 12 hours.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input(
			array(
				'refresh'     => array( 'type' => 'boolean', 'description' => __( 'Check the update servers now', 'wpzoom-ai-connect' ) ),
				'include_all' => array( 'type' => 'boolean', 'description' => __( 'List every installed theme and plugin, not only those with updates', 'wpzoom-ai-connect' ) ),
			)
		),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Updates::all( (array) $in );
		},
		'permission_callback' => static function () {
			return current_user_can( 'update_plugins' ) || current_user_can( 'update_themes' );
		},
		'meta'                => $meta( true, true ),
	) );

	/* ----- existing recipes ----- */

	wp_register_ability( 'wpzoom/list-recipes', array(
		'label'               => __( 'List recipes', 'wpzoom-ai-connect' ),
		'description'         => __( 'Finds the posts that contain Recipe Card Blocks recipes (posts, pages and the Recipes post type), newest change first, with each recipe\'s index, title, servings, total time and number of ingredients and steps.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input(
			array(
				'search'   => $str( __( 'Words in the post title or recipe', 'wpzoom-ai-connect' ) ),
				'per_page' => $int( __( 'Posts per page, 1 to 50', 'wpzoom-ai-connect' ), array( 'minimum' => 1, 'maximum' => 50, 'default' => 20 ) ),
				'page'     => $int( __( 'Page number', 'wpzoom-ai-connect' ), array( 'minimum' => 1, 'default' => 1 ) ),
			)
		),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Recipes::all( (array) $in );
		},
		'permission_callback' => $can( 'edit_posts' ),
		'meta'                => $meta( true, true ),
	) );

	wp_register_ability( 'wpzoom/get-recipe', array(
		'label'               => __( 'Read a recipe', 'wpzoom-ai-connect' ),
		'description'         => __( 'One recipe card in full: title, summary, image, course, cuisine, difficulty, keywords, details (servings, times, calories with units), ingredients and steps (with group headings) and notes.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input(
			array(
				'post_id' => $int( __( 'The post that holds the recipe, from wpzoom/list-recipes', 'wpzoom-ai-connect' ) ),
				'index'   => $int( __( 'Which recipe card in that post, 0 for the first', 'wpzoom-ai-connect' ), array( 'minimum' => 0, 'default' => 0 ) ),
			),
			array( 'post_id' )
		),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Recipes::get( (array) $in );
		},
		'permission_callback' => static function ( $in = null ) {
			return is_array( $in ) && ! empty( $in['post_id'] ) ? current_user_can( 'edit_post', (int) $in['post_id'] ) : current_user_can( 'edit_posts' );
		},
		'meta'                => $meta( true, true ),
	) );

	$item_list = array(
		'type'        => 'array',
		'description' => __( 'The full new list, in order: strings, or { "group": "For the sauce" } headings', 'wpzoom-ai-connect' ),
		'items'       => array( 'type' => array( 'string', 'object' ) ),
	);
	wp_register_ability( 'wpzoom/update-recipe', array(
		'label'               => __( 'Edit a recipe', 'wpzoom-ai-connect' ),
		'description'         => __( 'Changes an existing recipe card. Pass only what changes: title, summary, course/cuisine/difficulty/keywords, details by label (e.g. { "Servings": "6", "Prep time": { "value": "15", "unit": "minutes" } }), the full ingredients, steps or notes lists, or "replacements" (exact text to find and replace across ingredients and steps, e.g. a unit). Overwrites the recipe (the post keeps a revision), so confirm with the user first.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input(
			array(
				'post_id'      => $int( __( 'The post that holds the recipe', 'wpzoom-ai-connect' ) ),
				'index'        => $int( __( 'Which recipe card in that post, 0 for the first', 'wpzoom-ai-connect' ), array( 'minimum' => 0, 'default' => 0 ) ),
				'title'        => $str( __( 'Recipe title', 'wpzoom-ai-connect' ) ),
				'summary'      => $str( __( 'Short description', 'wpzoom-ai-connect' ) ),
				'course'       => $strs( __( 'Course, e.g. ["Dinner"]', 'wpzoom-ai-connect' ) ),
				'cuisine'      => $strs( __( 'Cuisine, e.g. ["Italian"]', 'wpzoom-ai-connect' ) ),
				'difficulty'   => $strs( __( 'Difficulty, e.g. ["Easy"]', 'wpzoom-ai-connect' ) ),
				'keywords'     => $strs( __( 'Keywords for search engines', 'wpzoom-ai-connect' ) ),
				'details'      => array( 'type' => 'object', 'additionalProperties' => true, 'description' => __( 'Detail label => new value, or { value, unit }', 'wpzoom-ai-connect' ) ),
				'ingredients'  => $item_list,
				'steps'        => $item_list,
				'notes'        => $strs( __( 'The full new list of notes', 'wpzoom-ai-connect' ) ),
				'replacements' => array(
					'type'        => 'array',
					'description' => __( 'Find/replace pairs applied to every ingredient and step', 'wpzoom-ai-connect' ),
					'items'       => array(
						'type'                 => 'object',
						'properties'           => array( 'find' => array( 'type' => 'string' ), 'replace' => array( 'type' => 'string' ) ),
						'required'             => array( 'find', 'replace' ),
						'additionalProperties' => false,
					),
				),
			),
			array( 'post_id' )
		),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Recipes::update( (array) $in );
		},
		'permission_callback' => static function ( $in = null ) {
			return is_array( $in ) && ! empty( $in['post_id'] ) ? current_user_can( 'edit_post', (int) $in['post_id'] ) : current_user_can( 'edit_posts' );
		},
		'meta'                => $meta( false, true, true ),
	) );

	/* ----- SEO fields (Yoast SEO or Rank Math) ----- */

	// The post's own capability: an editor may change a page's SEO fields, a contributor someone else's may not. For
	// an id with no post, edit_posts lets the ability answer "no post with this id" instead of a permission error.
	$can_edit_post = static function ( $in = null ) {
		$post_id = is_array( $in ) && ! empty( $in['post_id'] ) ? (int) $in['post_id'] : 0;
		return $post_id && get_post( $post_id ) ? current_user_can( 'edit_post', $post_id ) : current_user_can( 'edit_posts' );
	};

	wp_register_ability( 'wpzoom/get-seo', array(
		'label'               => __( 'Read the SEO title, description, focus keyword and noindex', 'wpzoom-ai-connect' ),
		'description'         => __( 'Reads the Yoast SEO or Rank Math fields of a post or page: SEO title, meta description, focus keyword and noindex, as stored (an empty string means the plugin\'s template applies), plus rendered_title and rendered_description, the text a search result shows with the template variables filled in, when the plugin can resolve them. Flags a title over 60 characters or a description over 160 (title_too_long, description_too_long). With neither plugin active it returns plugin "none" and the post\'s own title and excerpt, roughly what search engines use. Changes nothing. Canonical URLs, social previews and schema are not covered.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input(
			array( 'post_id' => $int( __( 'The post or page, e.g. 2', 'wpzoom-ai-connect' ) ) ),
			array( 'post_id' )
		),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_SEO::get( is_array( $in ) ? ( $in['post_id'] ?? 0 ) : 0 );
		},
		'permission_callback' => $can_edit_post,
		'meta'                => $meta( true, true ),
	) );

	wp_register_ability( 'wpzoom/update-seo', array(
		'label'               => __( 'Edit the SEO title, description, focus keyword or noindex', 'wpzoom-ai-connect' ),
		'description'         => /* translators: %%title%% and %title% are SEO plugin template variables: keep them as they are. */ __( 'Sets the Yoast SEO or Rank Math fields of a post or page: SEO title, meta description, focus keyword and noindex. Pass only what changes: fields not passed are left alone, and an empty string clears a field back to the plugin\'s template. Template variables such as %%title%% (Yoast) or %title% (Rank Math) are kept. A title over 60 characters or a description over 160 is saved and flagged (title_too_long, description_too_long), so shorten it if that happens. noindex: true hides the page from search engines, a big change for a public page; null puts back the site default. Returns the fields after the change and their previous values. Fails when neither plugin is active. Canonical URLs, social previews and schema are not covered. Overwrites the current values, so confirm with the user first.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input(
			array(
				'post_id'       => $int( __( 'The post or page, e.g. 2', 'wpzoom-ai-connect' ) ),
				'title'         => $str( __( 'SEO title, up to about 60 characters, e.g. "Wedding Photographer in Lisbon | Studio Name"', 'wpzoom-ai-connect' ) ),
				'description'   => $str( __( 'Meta description, up to about 160 characters, e.g. "Natural wedding photography in Lisbon and Sintra. See recent weddings and check your date."', 'wpzoom-ai-connect' ) ),
				'focus_keyword' => $str( __( 'Main keyphrase, e.g. "wedding photographer lisbon"', 'wpzoom-ai-connect' ) ),
				'noindex'       => array(
					'type'        => array( 'boolean', 'null' ),
					'description' => __( 'true hides the page from search engines (a big change for a public page), false lets them show it, null goes back to the site default', 'wpzoom-ai-connect' ),
				),
			),
			array( 'post_id' )
		),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_SEO::update( (array) $in );
		},
		'permission_callback' => $can_edit_post,
		'meta'                => $meta( false, true, true ),
	) );

	/* ----- accessibility check ----- */

	wp_register_ability( 'wpzoom/check-accessibility', array(
		'label'               => __( 'Check a page for common accessibility problems', 'wpzoom-ai-connect' ),
		'description'         => __( 'Reads a published page the way a visitor\'s browser gets it, header, menus and footer included, and finds common accessibility problems with the fix for each. Checks: image_alt (images with no alt attribute), link_text (links and buttons with no text, or only "click here", "read more", "here" or "link"), heading_order (no h1 or more than one, or a skipped level such as h2 then h4), form_labels (fields without a label), page_language (no valid language on the page), iframe_title (embeds without a title) and color_contrast (the theme\'s own text and background color settings, colors set on blocks in the page content and Elementor widgets\' colors, under 4.5:1, or 3:1 for headings). Each finding gives where it is (a short CSS path and the opening tag), the tool that fixes it in edit_with (content: update_content; elementor: wpzoom_update_elementor_page, which changes texts, links and images but not colors; menu: update_menu_item; template: update_template; theme_settings: wpzoom_update_theme_settings; global_styles: update_global_styles; media: the Media Library; report: no tool here, tell the user where it is changed) and how. passed and skipped say what else was looked at. Changes nothing: explain each fix and confirm it with the user before making it. Only published, public pages can be checked; for the homepage pass the front page\'s id. An automated check finds common problems; it does not cover everything a person with a screen reader or keyboard would meet. Say so when you report the results, and describe what was found rather than giving the page a verdict.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input(
			array(
				'post_id' => $int( __( 'The page to check, e.g. 2', 'wpzoom-ai-connect' ) ),
				'checks'  => $strs( __( 'Which checks to run; all when left out: image_alt, link_text, heading_order, form_labels, page_language, iframe_title, color_contrast', 'wpzoom-ai-connect' ) ),
			),
			array( 'post_id' )
		),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			$in = is_array( $in ) ? $in : array();
			return WPZOOM_AI_Accessibility::check( $in['post_id'] ?? 0, array_map( 'strval', (array) ( $in['checks'] ?? array() ) ) );
		},
		// Reading a public page needs no permission, but the findings point at the post's content.
		'permission_callback' => $can_edit_post,
		'meta'                => $meta( true, true ),
	) );

	/* ----- site title and tagline ----- */

	wp_register_ability( 'wpzoom/update-site-identity', array(
		'label'               => __( 'Change the site title or tagline', 'wpzoom-ai-connect' ),
		'description'         => __( 'Sets the site title and the tagline (Settings → General). Pass only what changes; the title cannot be empty. Live at once on every page and in search results, so confirm with the user first. Returns the previous and new values. The other Settings (homepage, posts per page, time zone, formats, admin email) stay read-only for the connection: the site owner changes them in Settings → General or Reading.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input(
			array(
				'title'   => $str( __( 'Site title, e.g. "Heart of Gippsland"', 'wpzoom-ai-connect' ), array( 'maxLength' => 200 ) ),
				'tagline' => $str( __( 'Tagline, shown under the title by most themes; an empty string removes it', 'wpzoom-ai-connect' ), array( 'maxLength' => 300 ) ),
			)
		),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			$options = array( 'title' => 'blogname', 'tagline' => 'blogdescription' );
			$given   = array_intersect_key( is_array( $in ) ? $in : array(), $options );
			if ( ! $given ) {
				return new WP_Error( 'wpzoom_nothing_to_change', __( 'Nothing to change: pass title, tagline or both.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
			}
			$values = array();
			foreach ( $given as $field => $value ) {
				$values[ $field ] = sanitize_text_field( (string) $value );
			}
			if ( isset( $values['title'] ) && '' === $values['title'] ) {
				return new WP_Error( 'wpzoom_empty_title', __( 'The site title cannot be empty. Nothing was changed.', 'wpzoom-ai-connect' ), array( 'status' => 400 ) );
			}
			$changed = array();
			foreach ( $values as $field => $value ) {
				$before = (string) get_option( $options[ $field ] );
				if ( $before !== $value ) {
					update_option( $options[ $field ], $value );
				}
				$changed[ $field ] = array( 'from' => $before, 'to' => (string) get_option( $options[ $field ] ) );
			}
			return array(
				'changed' => $changed,
				'link'    => home_url( '/' ),
				'note'    => __( 'Saved and live. A page cache may need a purge before visitors see it.', 'wpzoom-ai-connect' ),
			);
		},
		'permission_callback' => $can( 'edit_theme_options' ),
		'meta'                => $meta( false, true ),
	) );

	/* ----- caches ----- */

	wp_register_ability( 'wpzoom/clear-cache', array(
		'label'               => __( 'Clear the page cache', 'wpzoom-ai-connect' ),
		'description'         => __( 'Clears the site\'s page caches (WP Rocket, LiteSpeed, W3 Total Cache, WP Super Cache, WP Fastest Cache, SiteGround, Breeze, Hummingbird, Nginx Helper, Kinsta, WP Engine and others found on the site) so visitors see the latest version. Changes made through WPZOOM AI Connect already clear them on their own; use this when the user says a change does not show, or after changing things elsewhere. elementor_css: true also regenerates Elementor\'s CSS files (Elementor → Tools → Regenerate CSS), for Elementor pages that look broken or unstyled.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input(
			array( 'elementor_css' => array( 'type' => 'boolean', 'description' => __( 'Also regenerate Elementor CSS for every page', 'wpzoom-ai-connect' ) ) )
		),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Cache::ability( (array) $in );
		},
		'permission_callback' => $can( 'manage_options' ),
		'meta'                => $meta( false, true ),
	) );

	/* ----- Elementor pages (classic widgets) ----- */

	if ( WPZOOM_AI_Elementor::available() ) {
		wp_register_ability( 'wpzoom/list-elementor-pages', array(
			'label'               => __( 'List Elementor pages', 'wpzoom-ai-connect' ),
			'description'         => __( 'The pages, posts and Elementor templates (Theme Builder header, footer, popups) built with Elementor, newest change first, with post_id, title, type, status and which one is the homepage.', 'wpzoom-ai-connect' ),
			'category'            => 'wpzoom',
			'input_schema'        => $input(
				array(
					'search'   => $str( __( 'Words in the title', 'wpzoom-ai-connect' ) ),
					'per_page' => $int( __( 'Items per page, 1 to 50', 'wpzoom-ai-connect' ), array( 'minimum' => 1, 'maximum' => 50, 'default' => 20 ) ),
					'page'     => $int( __( 'Page number', 'wpzoom-ai-connect' ), array( 'minimum' => 1, 'default' => 1 ) ),
				)
			),
			'output_schema'       => $object,
			'execute_callback'    => static function ( $in ) {
				return WPZOOM_AI_Elementor::pages( (array) $in );
			},
			'permission_callback' => $can( 'edit_pages' ),
			'meta'                => $meta( true, true ),
		) );

		wp_register_ability( 'wpzoom/get-elementor-page', array(
			'label'               => __( 'Read an Elementor page', 'wpzoom-ai-connect' ),
			'description'         => __( 'The layout of a page built with Elementor, top to bottom: each widget (Heading, Text Editor, Button, Image, Video, WPZOOM Addons such as Team Members, Pricing Table, Slider, and Elementor Pro widgets) with its element id and its content fields: text, links, images, galleries, numbers and lists such as social profiles or pricing features, plus section background images. Read this before wpzoom/update-elementor-page, and pass its "modified" as if_unmodified_since.', 'wpzoom-ai-connect' ),
			'category'            => 'wpzoom',
			'input_schema'        => $input(
				array(
					'post_id'       => $int( __( 'The page, post or Elementor template, from wpzoom/list-elementor-pages or list_content', 'wpzoom-ai-connect' ) ),
					'include_empty' => array( 'type' => 'boolean', 'description' => __( 'Also list content fields that are empty (e.g. a button without a link)', 'wpzoom-ai-connect' ) ),
				),
				array( 'post_id' )
			),
			'output_schema'       => $object,
			'execute_callback'    => static function ( $in ) {
				return WPZOOM_AI_Elementor::get( (array) $in );
			},
			'permission_callback' => static function ( $in = null ) {
				return is_array( $in ) && ! empty( $in['post_id'] ) ? current_user_can( 'edit_post', (int) $in['post_id'] ) : current_user_can( 'edit_pages' );
			},
			'meta'                => $meta( true, true ),
		) );

		wp_register_ability( 'wpzoom/update-elementor-page', array(
			'label'               => __( 'Edit an Elementor page', 'wpzoom-ai-connect' ),
			'description'         => __( 'Changes the content of widgets on a page built with Elementor, the way the Elementor editor would: texts, headings, button labels and links, images, galleries, numbers, and lists such as team-member profiles or pricing features. Pass the element ids and field names from wpzoom/get-elementor-page. Text fields take plain text (Text Editor "editor" takes HTML); links take a URL; images take a media id (upload_media first) or an image URL; a list takes the full new list, keeping each existing item\'s "_id" and leaving it out for a new item. Colors, fonts and spacing are not changed here. Saved through Elementor, which keeps a revision; live immediately if the page is published, so confirm with the user first.', 'wpzoom-ai-connect' ),
			'category'            => 'wpzoom',
			'input_schema'        => $input(
				array(
					'post_id'             => $int( __( 'The page, post or Elementor template', 'wpzoom-ai-connect' ) ),
					'changes'             => array(
						'type'        => 'array',
						'minItems'    => 1,
						'description' => __( 'One entry per element to change', 'wpzoom-ai-connect' ),
						'items'       => array(
							'type'                 => 'object',
							'properties'           => array(
								'element_id' => array( 'type' => 'string', 'description' => __( 'Element id from wpzoom/get-elementor-page', 'wpzoom-ai-connect' ) ),
								'fields'     => array( 'type' => 'object', 'additionalProperties' => true, 'description' => __( 'Field name => new value, e.g. { "title": "About us", "link": "/contact" }', 'wpzoom-ai-connect' ) ),
							),
							'required'             => array( 'element_id', 'fields' ),
							'additionalProperties' => false,
						),
					),
					'if_unmodified_since' => $str( __( 'The "modified" value from wpzoom/get-elementor-page; the change is refused if the page was edited since', 'wpzoom-ai-connect' ) ),
				),
				array( 'post_id', 'changes' )
			),
			'output_schema'       => $object,
			'execute_callback'    => static function ( $in ) {
				return WPZOOM_AI_Elementor::update( (array) $in );
			},
			'permission_callback' => static function ( $in = null ) {
				return is_array( $in ) && ! empty( $in['post_id'] ) ? current_user_can( 'edit_post', (int) $in['post_id'] ) : current_user_can( 'edit_pages' );
			},
			'meta'                => $meta( false, true, true ),
		) );
	}
}
add_action( 'wp_abilities_api_init', 'wpzoom_ai_connect_register_site_abilities' );
