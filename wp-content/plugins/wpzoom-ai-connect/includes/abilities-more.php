<?php
/**
 * Registers the second wave of wpzoom/* abilities: starter sites, the AI section catalog, content
 * (portfolio, forms, recipes), license and diagnostics, and background job status.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

function wpzoom_ai_connect_register_more_abilities() {
	$any    = array( 'string', 'number', 'integer', 'boolean', 'object', 'array', 'null' );
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
	$str = static function ( string $description, array $extra = array() ): array {
		return array_merge( array( 'type' => 'string', 'description' => $description ), $extra );
	};
	$page_target = array(
		'page_id'        => array( 'type' => 'integer', 'description' => __( 'Existing page to add to.', 'wpzoom-ai-connect' ) ),
		'new_page_title' => $str( __( 'Create a new draft page with this title instead of using page_id.', 'wpzoom-ai-connect' ) ),
		'position'       => array( 'type' => 'string', 'enum' => array( 'append', 'prepend' ), 'default' => 'append' ),
	);

	/* ----- jobs ----- */

	wp_register_ability( 'wpzoom/job-status', array(
		'label'               => __( 'Background job status', 'wpzoom-ai-connect' ),
		'description'         => __( 'Progress and result of a background job started by wpzoom/build-page: status (queued, running, done, failed), phase, percent, recent messages and the final result with page links.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array( 'job_id' => $str( __( 'The job id returned when the job was started.', 'wpzoom-ai-connect' ) ) ), array( 'job_id' ) ),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			$job = WPZOOM_AI_Jobs::get( (string) ( $in['job_id'] ?? '' ) );
			return $job && WPZOOM_AI_Jobs::can_view( $job ) ? WPZOOM_AI_Jobs::describe( $job ) : new WP_Error( 'wpzoom_job_not_found', __( 'No such job.', 'wpzoom-ai-connect' ) );
		},
		'permission_callback' => static function ( $in = null ) {
			if ( ! current_user_can( 'edit_pages' ) ) {
				return false;
			}
			$job = is_array( $in ) && isset( $in['job_id'] ) ? WPZOOM_AI_Jobs::get( (string) $in['job_id'] ) : null;
			// Unknown ids fall through to the "No such job" error; a job someone else started is not shown.
			return ! $job || WPZOOM_AI_Jobs::can_view( $job );
		},
		'meta'                => $meta( true, true ),
	) );

	/* ----- starter sites ----- */

	wp_register_ability( 'wpzoom/list-demos', array(
		'label'               => __( 'List starter sites', 'wpzoom-ai-connect' ),
		'description'         => __( 'The ready-made starter sites (demos) the user can import with Inspiro Starter Sites: name, id, page builder (blocks or Elementor), categories, preview URL and the plugins each needs, plus which demo is already imported and the address of the importer screen. Read only: the user imports a demo themselves in WordPress admin; afterwards you can adapt its pages. Needs the Inspiro Starter Sites plugin. On Inspiro Premium it returns where the theme\'s own Demo Importer is instead.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array() ),
		'output_schema'       => $object,
		'execute_callback'    => static function () {
			return WPZOOM_AI_Starter_Sites::list_demos();
		},
		'permission_callback' => $can( 'import' ),
		'meta'                => $meta( true, true ),
	) );

	/* ----- WPZOOM AI account ----- */

	wp_register_ability( 'wpzoom/ai-status', array(
		'label'               => __( 'WPZOOM AI status', 'wpzoom-ai-connect' ),
		'description'         => __( 'Whether this site is connected to WPZOOM AI (ai.wpzoom.com): connected or not, the email, licensed (Inspiro Premium), and how to connect. The connection unlocks the section catalog on every theme; with Inspiro Starter Sites it also shows the remaining free AI page generations.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array() ),
		'output_schema'       => $object,
		'execute_callback'    => static function () {
			return WPZOOM_AI_Starter_Sites::ai_status();
		},
		'permission_callback' => $can( 'manage_options' ),
		'meta'                => $meta( true, true ),
	) );

	wp_register_ability( 'wpzoom/connect-ai', array(
		'label'               => __( 'Connect WPZOOM AI (step 1)', 'wpzoom-ai-connect' ),
		'description'         => __( 'Registers this site with WPZOOM AI (ai.wpzoom.com) with the user\'s email: it unlocks the section catalog (wpzoom/list-sections, wpzoom/insert-sections) and, with Inspiro Starter Sites, free AI page generations. Ask the user for their email and for consent to receive a verification code, then call this; a 6-digit code is emailed. Follow with wpzoom/verify-ai-code. Not needed on Inspiro Premium sites with an active license.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array(
			'email'   => $str( __( 'The user\'s email address.', 'wpzoom-ai-connect' ), array( 'format' => 'email' ) ),
			'consent' => array( 'type' => 'boolean', 'description' => __( 'True when the user agreed to receive the code (and occasional WPZOOM emails).', 'wpzoom-ai-connect' ) ),
		), array( 'email' ) ),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Starter_Sites::connect_ai( (array) $in );
		},
		'permission_callback' => $can( 'manage_options' ),
		'meta'                => $meta( false, true ),
	) );

	wp_register_ability( 'wpzoom/verify-ai-code', array(
		'label'               => __( 'Connect WPZOOM AI (step 2)', 'wpzoom-ai-connect' ),
		'description'         => __( 'Completes the WPZOOM AI registration with the 6-digit code the user received by email after wpzoom/connect-ai.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array( 'code' => $str( __( 'The 6-digit code from the email.', 'wpzoom-ai-connect' ) ) ), array( 'code' ) ),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Starter_Sites::verify_ai_code( (array) $in );
		},
		'permission_callback' => $can( 'manage_options' ),
		'meta'                => $meta( false, true ),
	) );

	/* ----- section catalog ----- */

	wp_register_ability( 'wpzoom/list-sections', array(
		'label'               => __( 'List design sections (catalog)', 'wpzoom-ai-connect' ),
		'description'         => __( 'The WPZOOM section catalog for building pages on any theme: 100+ ready-made block sections (hero, about, services, features, pricing, testimonials, team, FAQ, stats, gallery, contact, call to action and more), each with an id, role, tone (light/dark/image), description, photo count, what it needs (a plugin or published posts) and whether it is available on this site. Use the ids with wpzoom/insert-sections (instant, example copy) or, with Inspiro Starter Sites, wpzoom/plan-page and wpzoom/build-page (AI-written copy). Premium sections need an Inspiro Premium license (locked: true otherwise). The catalog comes from ai.wpzoom.com; the first use may answer next_step: connect the site\'s email with wpzoom/connect-ai and wpzoom/verify-ai-code (free, no AI quota involved).', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array(
			'search'              => $str( __( 'Filter by words in the id, role or description.', 'wpzoom-ai-connect' ) ),
			'role'                => $str( __( 'Only sections with this role (e.g. hero, services, pricing, faq, contact).', 'wpzoom-ai-connect' ) ),
			'include_unavailable' => array( 'type' => 'boolean', 'default' => false, 'description' => __( 'Also list locked (Premium) sections and ones whose requirements this site lacks.', 'wpzoom-ai-connect' ) ),
		) ),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Starter_Sites::list_sections( (array) $in );
		},
		'permission_callback' => $can( 'edit_pages' ),
		'meta'                => $meta( true, true ),
	) );

	wp_register_ability( 'wpzoom/insert-sections', array(
		'label'               => __( 'Insert design sections', 'wpzoom-ai-connect' ),
		'description'         => __( 'Adds one or more catalog sections to a page (or a new draft page) instantly, with the demo\'s example copy, optionally with your own headline per section. Works on any theme: the sections take the site\'s colors and, on block themes, its buttons. Photos come from the site\'s own Media Library when it has some; otherwise a neutral placeholder image is used and the section is listed in placeholders, so tell the user to replace those. No AI, no quota. Premium sections (Inspiro Premium license) and sections whose needs the site lacks are refused by name in skipped_reasons. Edit the text afterwards with the page tools, or use wpzoom/build-page (Inspiro Starter Sites) for AI-written copy.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array_merge( array(
			'sections' => array(
				'type'        => 'array',
				'description' => __( 'Section ids from wpzoom/list-sections, in page order. Each item is an id string or {"id": "...", "headline": "..."}.', 'wpzoom-ai-connect' ),
				'items'       => array( 'type' => array( 'string', 'object' ), 'properties' => array( 'id' => array( 'type' => 'string' ), 'headline' => array( 'type' => 'string' ) ), 'additionalProperties' => false ),
			),
		), $page_target ), array( 'sections' ) ),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Starter_Sites::insert_sections( (array) $in );
		},
		'permission_callback' => static function ( $in = null ) {
			return is_array( $in ) && ! empty( $in['page_id'] ) ? current_user_can( 'edit_post', (int) $in['page_id'] ) : current_user_can( 'edit_pages' );
		},
		'meta'                => $meta( false, false ),
	) );

	wp_register_ability( 'wpzoom/plan-page', array(
		'label'               => __( 'Plan a page with AI', 'wpzoom-ai-connect' ),
		'description'         => __( 'Asks WPZOOM AI to plan a page from a description of the business and the page\'s purpose: it picks catalog sections and writes a headline and brief per section. Free (not metered). Show the plan to the user, then call wpzoom/build-page with it.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array(
			'description' => $str( __( 'What the page is for and who the business is, in a few sentences. The more specific, the better the copy.', 'wpzoom-ai-connect' ) ),
			'page_type'   => array( 'type' => 'string', 'enum' => array( 'auto', 'home', 'landing', 'inner', 'sections' ), 'default' => 'auto' ),
			'style'       => array( 'type' => 'string', 'enum' => array( '', 'light', 'dark', 'image', 'minimal' ), 'description' => __( 'Visual direction.', 'wpzoom-ai-connect' ) ),
			'pinned'      => array( 'type' => 'array', 'items' => array( 'type' => 'string' ), 'description' => __( 'Section ids that must be included.', 'wpzoom-ai-connect' ) ),
			'avoid'       => array( 'type' => 'array', 'items' => array( 'type' => 'string' ), 'description' => __( 'Section ids to leave out (e.g. from a previous plan).', 'wpzoom-ai-connect' ) ),
		), array( 'description' ) ),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Starter_Sites::plan_page( (array) $in );
		},
		'permission_callback' => $can( 'edit_pages' ),
		// Not readonly: it is a POST so clients can send the description in a body; it changes nothing on the site.
		'meta'                => $meta( false, true ),
	) );

	wp_register_ability( 'wpzoom/build-page', array(
		'label'               => __( 'Build a page with AI', 'wpzoom-ai-connect' ),
		'description'         => __( 'Builds a complete page from a plan (from wpzoom/plan-page) or straight from a description: WPZOOM AI writes the copy for every section, photos come from Pexels, and the page is created as a draft (or added to an existing page). Uses one AI generation from the site\'s quota. Runs in the background: poll wpzoom/job-status with the returned job id.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array_merge( array(
			'plan'        => array( 'type' => 'object', 'additionalProperties' => true, 'description' => __( 'The plan object returned by wpzoom/plan-page (sections may be removed).', 'wpzoom-ai-connect' ) ),
			'description' => $str( __( 'Alternative to plan: describe the page and business, and it is planned and built in one go.', 'wpzoom-ai-connect' ) ),
			'page_type'   => array( 'type' => 'string', 'enum' => array( 'auto', 'home', 'landing', 'inner', 'sections' ), 'default' => 'auto' ),
			'style'       => array( 'type' => 'string', 'enum' => array( '', 'light', 'dark', 'image', 'minimal' ) ),
		), $page_target ) ),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Starter_Sites::build_page( (array) $in );
		},
		'permission_callback' => $can( 'edit_pages' ),
		'meta'                => $meta( false, false ),
	) );

	/* ----- content ----- */

	wp_register_ability( 'wpzoom/create-portfolio-item', array(
		'label'               => __( 'Create a portfolio item', 'wpzoom-ai-connect' ),
		'description'         => __( 'Creates a portfolio project (WPZOOM Portfolio or a WPZOOM theme with portfolio support): title, description, categories (created if missing), a featured image from a URL and an optional YouTube/Vimeo video. Draft by default. Tells you which block lists portfolio items on a page.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array(
			'title'              => $str( __( 'Project title.', 'wpzoom-ai-connect' ) ),
			'content'            => $str( __( 'Description: plain text (paragraphs separated by blank lines) or block markup.', 'wpzoom-ai-connect' ) ),
			'excerpt'            => $str( __( 'One or two sentences shown in grids.', 'wpzoom-ai-connect' ) ),
			'categories'         => array( 'type' => 'array', 'items' => array( 'type' => 'string' ), 'description' => __( 'Portfolio category names.', 'wpzoom-ai-connect' ) ),
			'featured_image_url' => $str( __( 'Image URL to download as the featured image.', 'wpzoom-ai-connect' ), array( 'format' => 'uri' ) ),
			'video_url'          => $str( __( 'YouTube or Vimeo URL shown instead of the image.', 'wpzoom-ai-connect' ), array( 'format' => 'uri' ) ),
			'status'             => array( 'type' => 'string', 'enum' => array( 'draft', 'publish' ), 'default' => 'draft' ),
		), array( 'title' ) ),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Content::create_portfolio_item( (array) $in );
		},
		'permission_callback' => static function ( $in = null ) {
			// The portfolio post type's own capabilities; categories are checked when they are set. Downloading the
			// featured image creates a Media Library attachment, so it also needs upload_files.
			return WPZOOM_AI_Content::can_create( 'portfolio_item' ) && ( empty( $in['featured_image_url'] ) || current_user_can( 'upload_files' ) );
		},
		'meta'                => $meta( false, false ),
	) );

	wp_register_ability( 'wpzoom/create-form', array(
		'label'               => __( 'Create a form', 'wpzoom-ai-connect' ),
		'description'         => __( 'Creates a WPZOOM Forms form (contact, quote, booking request, newsletter...) from a list of fields, sets the recipient email and success message, and returns the block to embed it, optionally appending it to a page. Field types: text, name, email, tel, url, number, textarea, select, radio, checkboxes, checkbox, date, hidden, heading, paragraph, divider.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array(
			'title'           => $str( __( 'Form name, e.g. "Contact form".', 'wpzoom-ai-connect' ) ),
			'fields'          => array(
				'type'  => 'array',
				'items' => array(
					'type'                 => 'object',
					'properties'           => array(
						'type'          => array( 'type' => 'string', 'enum' => WPZOOM_AI_Content::FORM_FIELD_TYPES ),
						'label'         => array( 'type' => 'string' ),
						'required'      => array( 'type' => 'boolean' ),
						'placeholder'   => array( 'type' => 'string' ),
						'help'          => array( 'type' => 'string' ),
						'options'       => array( 'type' => 'array', 'items' => array( 'type' => 'string' ), 'description' => __( 'For select, radio and checkboxes.', 'wpzoom-ai-connect' ) ),
						'checkbox_text' => array( 'type' => 'string', 'description' => __( 'For a single checkbox: the text next to it.', 'wpzoom-ai-connect' ) ),
						'width'         => array( 'type' => 'string', 'enum' => array( 'full', 'half', 'third', 'two-thirds' ) ),
						'is_subject'    => array( 'type' => 'boolean', 'description' => __( 'Use this field as the email subject.', 'wpzoom-ai-connect' ) ),
					),
					'required'             => array( 'type', 'label' ),
					'additionalProperties' => false,
				),
			),
			'recipient_email' => $str( __( 'Where submissions are sent (defaults to the site admin email).', 'wpzoom-ai-connect' ), array( 'format' => 'email' ) ),
			'subject'         => $str( __( 'Notification email subject.', 'wpzoom-ai-connect' ) ),
			'success_message' => $str( __( 'Shown after a successful submission.', 'wpzoom-ai-connect' ) ),
			'submit_label'    => $str( __( 'Button text (default "Send").', 'wpzoom-ai-connect' ) ),
			'add_to_page_id'  => array( 'type' => 'integer', 'description' => __( 'Append the form block to this page.', 'wpzoom-ai-connect' ) ),
		), array( 'title', 'fields' ) ),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Content::create_form( (array) $in );
		},
		'permission_callback' => static function () {
			// Forms are published right away (an unpublished form cannot be embedded).
			if ( ! WPZOOM_AI_Post_Types::may_write( 'wpzf-form' ) ) {
				return false;
			}
			$type = get_post_type_object( 'wpzf-form' );
			return $type ? current_user_can( $type->cap->create_posts ) && current_user_can( $type->cap->publish_posts ) : current_user_can( 'publish_posts' );
		},
		'meta'                => $meta( false, false ),
	) );

	wp_register_ability( 'wpzoom/create-recipe', array(
		'label'               => __( 'Create a recipe', 'wpzoom-ai-connect' ),
		'description'         => __( 'Creates a post (or a Recipe Card recipe) with a complete Recipe Card block: title, summary, image, servings, prep/cook time, calories, course, cuisine, difficulty, keywords, ingredients, steps and notes, with valid Recipe structured data for Google. Draft by default. Needs Recipe Card Blocks by WPZOOM.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array(
			'title'              => $str( __( 'Recipe name.', 'wpzoom-ai-connect' ) ),
			'summary'            => $str( __( 'Two or three sentences about the dish.', 'wpzoom-ai-connect' ) ),
			'intro_content'      => $str( __( 'Optional text placed above the recipe card (paragraphs).', 'wpzoom-ai-connect' ) ),
			'image_url'          => $str( __( 'Photo URL to download as the recipe image and featured image.', 'wpzoom-ai-connect' ), array( 'format' => 'uri' ) ),
			'servings'           => array( 'type' => 'integer' ),
			'prep_time_minutes'  => array( 'type' => 'integer' ),
			'cook_time_minutes'  => array( 'type' => 'integer' ),
			'total_time_minutes' => array( 'type' => 'integer', 'description' => __( 'Defaults to prep + cook.', 'wpzoom-ai-connect' ) ),
			'calories'           => array( 'type' => 'integer' ),
			'course'             => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
			'cuisine'            => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
			'difficulty'         => array( 'type' => 'array', 'items' => array( 'type' => 'string' ), 'description' => 'e.g. ["Easy"].' ),
			'keywords'           => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
			'ingredients'        => array( 'type' => 'array', 'description' => __( 'Strings like "200 g spaghetti", or {"text": "...", "group": "For the sauce"} to start a group.', 'wpzoom-ai-connect' ), 'items' => array( 'type' => array( 'string', 'object' ), 'properties' => array( 'text' => array( 'type' => 'string' ), 'group' => array( 'type' => 'string' ) ), 'additionalProperties' => false ) ),
			'steps'              => array( 'type' => 'array', 'description' => __( 'Strings, or {"text": "...", "group": "Sauce"} to start a group.', 'wpzoom-ai-connect' ), 'items' => array( 'type' => array( 'string', 'object' ), 'properties' => array( 'text' => array( 'type' => 'string' ), 'group' => array( 'type' => 'string' ) ), 'additionalProperties' => false ) ),
			'notes'              => array( 'type' => 'array', 'items' => array( 'type' => 'string' ) ),
			'post_type'          => array( 'type' => 'string', 'enum' => array( 'post', 'recipe' ), 'default' => 'post', 'description' => __( '"recipe" stores it in the Recipe Card library to embed in several posts.', 'wpzoom-ai-connect' ) ),
			'status'             => array( 'type' => 'string', 'enum' => array( 'draft', 'publish' ), 'default' => 'draft' ),
		), array( 'title', 'ingredients', 'steps' ) ),
		'output_schema'       => $object,
		'execute_callback'    => static function ( $in ) {
			return WPZOOM_AI_Content::create_recipe( (array) $in );
		},
		'permission_callback' => static function ( $in = null ) {
			// The capabilities of the post type the recipe goes into (posts, or the Recipe Card library). Downloading
			// the recipe image creates a Media Library attachment, so it also needs upload_files.
			$type = is_array( $in ) && isset( $in['post_type'] ) && 'recipe' === $in['post_type'] ? 'wpzoom_rcb' : 'post';
			return WPZOOM_AI_Content::can_create( $type ) && ( empty( $in['image_url'] ) || current_user_can( 'upload_files' ) );
		},
		'meta'                => $meta( false, false ),
	) );

	/* ----- license and diagnostics ----- */

	wp_register_ability( 'wpzoom/license-status', array(
		'label'               => __( 'Theme license status', 'wpzoom-ai-connect' ),
		'description'         => __( 'The WPZOOM theme license: status (valid, expired, inactive...), what it means, expiry and site usage when known, whether a theme update is pending, and where to manage or renew it.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array() ),
		'output_schema'       => $object,
		'execute_callback'    => static function () {
			return WPZOOM_AI_Diagnostics::license_status();
		},
		'permission_callback' => $can( 'manage_options' ),
		'meta'                => $meta( true, true ),
	) );

	wp_register_ability( 'wpzoom/diagnose', array(
		'label'               => __( 'Diagnose the site', 'wpzoom-ai-connect' ),
		'description'         => __( 'Runs the checks a WPZOOM support engineer does first: license, HTTPS, permalinks, static front page, homepage slideshow and its slides, menu locations, plugins the theme needs, imported demo, content volume, WP-Cron and application passwords. Each check has a status (ok, warn, info) and a concrete fix. Use it when something "does not show" or before making changes.', 'wpzoom-ai-connect' ),
		'category'            => 'wpzoom',
		'input_schema'        => $input( array() ),
		'output_schema'       => $object,
		'execute_callback'    => static function () {
			return WPZOOM_AI_Diagnostics::diagnose();
		},
		'permission_callback' => $can( 'edit_theme_options' ),
		'meta'                => $meta( true, true ),
	) );
}
add_action( 'wp_abilities_api_init', 'wpzoom_ai_connect_register_more_abilities', 20 );
