<?php
/**
 * Content the WPZOOM plugins own: portfolio items (WPZOOM Portfolio), forms (WPZOOM Forms) and
 * recipe cards (Recipe Card Blocks). Each creator writes exactly what the plugin's own editor would.
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Content {

	/* ---------- availability ---------- */

	public static function has_portfolio(): bool {
		return post_type_exists( 'portfolio_item' );
	}

	public static function has_forms(): bool {
		return post_type_exists( 'wpzf-form' ) && ( class_exists( 'WPZOOM_Forms_Schema' ) || class_exists( '\WPZOOM\Forms_Pro\Schema' ) );
	}

	public static function has_recipes(): bool {
		return WP_Block_Type_Registry::get_instance()->is_registered( 'wpzoom-recipe-card/block-recipe-card' );
	}

	/* ---------- shared ---------- */

	/**
	 * Downloads an image URL into the Media Library and returns the attachment id.
	 *
	 * @return int|WP_Error
	 */
	public static function sideload_image( string $url, int $post_id = 0, string $alt = '' ) {
		if ( ! current_user_can( 'upload_files' ) ) {
			return new WP_Error( 'wpzoom_forbidden', __( 'The connected user may not upload files, so the image was not added.', 'wpzoom-ai-connect' ) );
		}
		if ( ! function_exists( 'media_sideload_image' ) ) {
			require_once ABSPATH . 'wp-admin/includes/media.php';
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}
		$id = media_sideload_image( esc_url_raw( $url ), $post_id, $alt ?: null, 'id' );
		if ( is_wp_error( $id ) ) {
			return $id;
		}
		if ( $alt ) {
			update_post_meta( (int) $id, '_wp_attachment_image_alt', sanitize_text_field( $alt ) );
		}
		return (int) $id;
	}

	/** Plain text or simple HTML to block markup: one paragraph block per paragraph. */
	public static function to_blocks( string $text ): string {
		$text = trim( $text );
		if ( '' === $text ) {
			return '';
		}
		if ( false !== strpos( $text, '<!-- wp:' ) ) {
			// Block markup: the same HTML filtering as post content (wp_kses_post also filters block attributes).
			return wp_kses_post( $text );
		}
		$paragraphs = preg_split( "/\n\s*\n/", wp_kses_post( $text ) );
		$blocks     = array();
		foreach ( $paragraphs as $p ) {
			$p = trim( $p );
			if ( '' === $p ) {
				continue;
			}
			if ( preg_match( '/^<(h[1-6]|ul|ol|blockquote|figure)/i', $p ) ) {
				$blocks[] = $p; // Already HTML; leave as a classic chunk.
			} else {
				$blocks[] = "<!-- wp:paragraph -->\n<p>" . nl2br( $p ) . "</p>\n<!-- /wp:paragraph -->";
			}
		}
		return implode( "\n\n", $blocks );
	}

	private static function post_links( int $id ): array {
		$post = get_post( $id );
		return array(
			'id'          => $id,
			'title'       => $post ? $post->post_title : '',
			'status'      => $post ? $post->post_status : '',
			'edit_url'    => get_edit_post_link( $id, 'raw' ),
			'preview_url' => $post && 'publish' === $post->post_status ? get_permalink( $id ) : get_preview_post_link( $id ),
		);
	}

	private static function status( $value ): string {
		return in_array( $value, array( 'publish', 'draft', 'pending', 'private' ), true ) ? $value : 'draft';
	}

	/**
	 * Whether the current user may create posts of this type, by the type's own capabilities (a custom post type
	 * can map them to anything). A type that is not registered falls back to edit_posts, so the ability itself can
	 * explain which plugin is missing. The connection also needs the site owner's choice for the type to allow it.
	 */
	public static function can_create( string $post_type ): bool {
		if ( ! WPZOOM_AI_Post_Types::may_write( $post_type ) ) {
			return false;
		}
		$type = get_post_type_object( $post_type );
		return $type ? current_user_can( $type->cap->create_posts ) : current_user_can( 'edit_posts' );
	}

	/** The status to save with: a user who cannot publish this type gets a draft; null when they cannot create it at all. */
	private static function allowed_status( string $post_type, string $status ): ?string {
		$type = get_post_type_object( $post_type );
		if ( ! $type || ! current_user_can( $type->cap->create_posts ) ) {
			return null;
		}
		if ( in_array( $status, array( 'publish', 'private' ), true ) && ! current_user_can( $type->cap->publish_posts ) ) {
			return 'draft';
		}
		return $status;
	}

	/* ---------- portfolio ---------- */

	public static function create_portfolio_item( array $in ) {
		if ( ! self::has_portfolio() ) {
			return new WP_Error( 'wpzoom_no_portfolio', __( 'Portfolio items are not available: install and activate the WPZOOM Portfolio plugin (or a WPZOOM theme with portfolio support).', 'wpzoom-ai-connect' ) );
		}
		$title = sanitize_text_field( (string) ( $in['title'] ?? '' ) );
		if ( '' === $title ) {
			return new WP_Error( 'wpzoom_missing_title', __( 'A title is required.', 'wpzoom-ai-connect' ) );
		}
		$status = self::allowed_status( 'portfolio_item', self::status( $in['status'] ?? 'draft' ) );
		if ( null === $status ) {
			return new WP_Error( 'wpzoom_forbidden', __( 'You are not allowed to create portfolio items.', 'wpzoom-ai-connect' ), array( 'status' => 403 ) );
		}
		$post_id = wp_insert_post(
			array(
				'post_type'    => 'portfolio_item',
				'post_status'  => $status,
				'post_title'   => $title,
				'post_content' => wp_slash( self::to_blocks( (string) ( $in['content'] ?? '' ) ) ),
				'post_excerpt' => wp_slash( sanitize_textarea_field( (string) ( $in['excerpt'] ?? '' ) ) ),
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$notes = array();
		$taxonomy = get_taxonomy( 'portfolio' );
		if ( ! empty( $in['categories'] ) && is_array( $in['categories'] ) && $taxonomy && ! current_user_can( $taxonomy->cap->assign_terms ) ) {
			$notes[] = __( 'Categories were not set: you are not allowed to assign portfolio categories.', 'wpzoom-ai-connect' );
		} elseif ( ! empty( $in['categories'] ) && is_array( $in['categories'] ) && $taxonomy ) {
			$ids = array();
			foreach ( $in['categories'] as $name ) {
				$name = sanitize_text_field( (string) $name );
				if ( '' === $name ) {
					continue;
				}
				$term = term_exists( $name, 'portfolio' );
				if ( ! $term && ! ( current_user_can( $taxonomy->cap->manage_terms ) && current_user_can( $taxonomy->cap->edit_terms ) ) ) {
					$notes[] = sprintf( /* translators: %s: category name */ __( 'Category "%s" does not exist and you are not allowed to create categories.', 'wpzoom-ai-connect' ), $name );
					continue;
				}
				if ( ! $term ) {
					$term = wp_insert_term( $name, 'portfolio' );
					if ( is_wp_error( $term ) ) {
						$notes[] = sprintf( /* translators: 1: category name, 2: error message */ __( 'Category "%1$s" could not be created: %2$s', 'wpzoom-ai-connect' ), $name, $term->get_error_message() );
						continue;
					}
					$notes[] = sprintf( /* translators: %s: category name */ __( 'Created portfolio category "%s".', 'wpzoom-ai-connect' ), $name );
				}
				$ids[] = (int) ( is_array( $term ) ? $term['term_id'] : $term );
			}
			if ( $ids ) {
				wp_set_object_terms( $post_id, $ids, 'portfolio' );
			}
		}
		if ( ! empty( $in['featured_image_url'] ) ) {
			$image = self::sideload_image( (string) $in['featured_image_url'], $post_id, $title );
			if ( is_wp_error( $image ) ) {
				$notes[] = sprintf( /* translators: %s: error message */ __( 'Featured image could not be downloaded: %s', 'wpzoom-ai-connect' ), $image->get_error_message() );
			} else {
				set_post_thumbnail( $post_id, $image );
			}
		}
		if ( ! empty( $in['video_url'] ) ) {
			update_post_meta( $post_id, '_wpzb_portfolio_video_type', 'service' );
			update_post_meta( $post_id, '_wpzb_portfolio_video_url', esc_url_raw( (string) $in['video_url'] ) );
		}

		$out          = self::post_links( $post_id );
		$out['notes'] = $notes;
		$out['show_on_page'] = sprintf( /* translators: %s: block markup */ __( 'To list portfolio items on a page insert this block: %s', 'wpzoom-ai-connect' ), '<!-- wp:wpzoom-blocks/portfolio {"align":"full","amount":6,"columnsAmount":3,"layout":"grid","categories":["-1"],"showCategoryFilter":true} /-->' );
		return $out;
	}

	/* ---------- forms ---------- */

	const FORM_FIELD_TYPES = array( 'text', 'name', 'email', 'tel', 'url', 'number', 'textarea', 'select', 'radio', 'checkboxes', 'checkbox', 'date', 'hidden', 'heading', 'paragraph', 'divider' );

	public static function create_form( array $in ) {
		if ( ! self::has_forms() ) {
			return new WP_Error( 'wpzoom_no_forms', __( 'Forms are not available: install and activate the WPZOOM Forms plugin.', 'wpzoom-ai-connect' ) );
		}
		$title  = sanitize_text_field( (string) ( $in['title'] ?? '' ) );
		$fields = isset( $in['fields'] ) && is_array( $in['fields'] ) ? $in['fields'] : array();
		if ( '' === $title || ! $fields ) {
			return new WP_Error( 'wpzoom_form_invalid', __( 'A title and at least one field are required.', 'wpzoom-ai-connect' ) );
		}

		$schema_fields = array();
		$has_email     = false;
		foreach ( $fields as $i => $field ) {
			if ( ! is_array( $field ) ) {
				continue;
			}
			$type = sanitize_key( (string) ( $field['type'] ?? 'text' ) );
			if ( ! in_array( $type, self::FORM_FIELD_TYPES, true ) ) {
				return new WP_Error( 'wpzoom_form_invalid', sprintf( /* translators: 1: the field type sent, 2: the field types, comma-separated */ __( 'Unknown field type "%1$s". Use one of: %2$s.', 'wpzoom-ai-connect' ), $type, implode( ', ', self::FORM_FIELD_TYPES ) ) );
			}
			$entry = array(
				'id'          => 'f_' . substr( md5( $title . $i . wp_rand() ), 0, 8 ),
				'type'        => $type,
				'label'       => sanitize_text_field( (string) ( $field['label'] ?? ucfirst( $type ) ) ),
				'required'    => ! empty( $field['required'] ),
				'placeholder' => sanitize_text_field( (string) ( $field['placeholder'] ?? '' ) ),
				'help'        => sanitize_text_field( (string) ( $field['help'] ?? '' ) ),
				'width'       => in_array( $field['width'] ?? 'full', array( 'full', 'half', 'third', 'two-thirds' ), true ) ? $field['width'] : 'full',
			);
			if ( in_array( $type, array( 'select', 'radio', 'checkboxes' ), true ) ) {
				$options = array();
				foreach ( (array) ( $field['options'] ?? array() ) as $option ) {
					$label = sanitize_text_field( is_array( $option ) ? (string) ( $option['label'] ?? '' ) : (string) $option );
					if ( '' !== $label ) {
						$options[] = array( 'label' => $label, 'value' => sanitize_title( $label ) );
					}
				}
				$entry['options'] = $options;
			}
			if ( 'checkbox' === $type ) {
				$entry['checkboxText'] = sanitize_text_field( (string) ( $field['checkbox_text'] ?? $entry['label'] ) );
			}
			if ( 'textarea' === $type ) {
				$entry['rows'] = 6;
			}
			if ( 'email' === $type && ! $has_email ) {
				$entry['isReplyTo'] = true;
				$has_email          = true;
			}
			if ( ! empty( $field['is_subject'] ) ) {
				$entry['isSubject'] = true;
			}
			$schema_fields[] = $entry;
		}

		$schema = array(
			'version'  => 2,
			'settings' => array(
				'submitLabel'      => sanitize_text_field( (string) ( $in['submit_label'] ?? 'Send' ) ),
				'submitAlign'      => 'left',
				'labelsPosition'   => 'top',
				'theme'            => 'default',
				'formLayout'       => 'default',
				'honeypot'         => true,
				'showRequiredMark' => true,
			),
			'fields'   => $schema_fields,
		);

		if ( 'publish' !== self::allowed_status( 'wpzf-form', 'publish' ) ) {
			return new WP_Error( 'wpzoom_forbidden', __( 'You are not allowed to publish forms.', 'wpzoom-ai-connect' ), array( 'status' => 403 ) );
		}
		$form_id = wp_insert_post(
			array(
				'post_type'   => 'wpzf-form',
				'post_status' => 'publish',
				'post_title'  => $title,
			),
			true
		);
		if ( is_wp_error( $form_id ) ) {
			return $form_id;
		}
		if ( class_exists( '\WPZOOM\Forms_Pro\Schema' ) ) {
			\WPZOOM\Forms_Pro\Schema::save_for_form( $form_id, $schema );
		} else {
			WPZOOM_Forms_Schema::save_for_form( $form_id, $schema );
		}
		update_post_meta( $form_id, '_form_method', 'combined' );
		$recipient = sanitize_email( (string) ( $in['recipient_email'] ?? '' ) );
		update_post_meta( $form_id, '_form_email', $recipient ?: get_option( 'admin_email' ) );
		update_post_meta( $form_id, '_form_subject', sanitize_text_field( (string) ( $in['subject'] ?? sprintf( /* translators: %s: form name */ __( 'New message from %s', 'wpzoom-ai-connect' ), $title ) ) ) );
		update_post_meta( $form_id, '_form_success_message', sanitize_text_field( (string) ( $in['success_message'] ?? __( 'Thank you, your message has been sent.', 'wpzoom-ai-connect' ) ) ) );
		update_post_meta( $form_id, '_form_failure_message', __( 'Something went wrong. Please try again.', 'wpzoom-ai-connect' ) );

		$embed = sprintf( '<!-- wp:wpzoom-forms/form-block {"formId":"%d","align":"none"} /-->', $form_id );
		$out   = array(
			'form_id'     => $form_id,
			'title'       => $title,
			'fields'      => count( $schema_fields ),
			'recipient'   => $recipient ?: get_option( 'admin_email' ),
			'edit_url'    => get_edit_post_link( $form_id, 'raw' ),
			'embed_block' => $embed,
			'shortcode'   => sprintf( '[wpzf_form id="%d"]', $form_id ),
		);
		if ( ! empty( $in['add_to_page_id'] ) ) {
			$page_id = (int) $in['add_to_page_id'];
			$page    = get_post( $page_id );
			if ( $page && current_user_can( 'edit_post', $page_id ) ) {
				wp_update_post( array( 'ID' => $page_id, 'post_content' => wp_slash( rtrim( $page->post_content ) . "\n\n" . $embed ) ) );
				$out['added_to_page'] = self::post_links( $page_id );
			} else {
				$out['notes'] = array( sprintf( /* translators: %d: page ID */ __( 'Page %d not found or not editable; the form was created but not embedded.', 'wpzoom-ai-connect' ), $page_id ) );
			}
		}
		return $out;
	}

	/* ---------- recipes ---------- */

	public static function create_recipe( array $in ) {
		if ( ! self::has_recipes() ) {
			return new WP_Error( 'wpzoom_no_recipes', __( 'Recipe cards are not available: install and activate Recipe Card Blocks by WPZOOM.', 'wpzoom-ai-connect' ) );
		}
		$title = sanitize_text_field( (string) ( $in['title'] ?? '' ) );
		$ingredients = isset( $in['ingredients'] ) && is_array( $in['ingredients'] ) ? $in['ingredients'] : array();
		$steps       = isset( $in['steps'] ) && is_array( $in['steps'] ) ? $in['steps'] : array();
		if ( '' === $title || ! $ingredients || ! $steps ) {
			return new WP_Error( 'wpzoom_recipe_invalid', __( 'A title, ingredients and steps are required.', 'wpzoom-ai-connect' ) );
		}

		$post_type = ( isset( $in['post_type'] ) && 'recipe' === $in['post_type'] && post_type_exists( 'wpzoom_rcb' ) ) ? 'wpzoom_rcb' : 'post';
		$status    = self::allowed_status( $post_type, self::status( $in['status'] ?? 'draft' ) );
		if ( null === $status ) {
			return new WP_Error( 'wpzoom_forbidden', __( 'You are not allowed to create recipes.', 'wpzoom-ai-connect' ), array( 'status' => 403 ) );
		}

		$post_id = wp_insert_post(
			array(
				'post_type'   => $post_type,
				'post_status' => $status,
				'post_title'  => $title,
			),
			true
		);
		if ( is_wp_error( $post_id ) ) {
			return $post_id;
		}

		$notes = array();
		$image = array( 'id' => 0, 'url' => '' );
		if ( ! empty( $in['image_url'] ) ) {
			$attachment = self::sideload_image( (string) $in['image_url'], $post_id, $title );
			if ( is_wp_error( $attachment ) ) {
				$notes[] = sprintf( /* translators: %s: error message */ __( 'Recipe image could not be downloaded: %s', 'wpzoom-ai-connect' ), $attachment->get_error_message() );
			} else {
				$image = array( 'id' => $attachment, 'url' => wp_get_attachment_url( $attachment ) );
				set_post_thumbnail( $post_id, $attachment );
			}
		}

		$list = static function ( array $items, string $text_key, string $json_key, string $prefix ): array {
			$out = array();
			foreach ( $items as $item ) {
				$text  = is_array( $item ) ? (string) ( $item['text'] ?? '' ) : (string) $item;
				$group = is_array( $item ) ? (string) ( $item['group'] ?? '' ) : '';
				if ( '' !== $group ) {
					$out[] = array( 'id' => $prefix . wp_unique_id(), 'isGroup' => true, $text_key => $group, $json_key => $group );
				}
				$text = sanitize_text_field( $text );
				if ( '' === $text ) {
					continue;
				}
				$out[] = array( 'id' => $prefix . wp_unique_id(), $text_key => array( $text ), $json_key => $text );
			}
			return $out;
		};

		$minutes = static function ( $value ): string {
			return is_numeric( $value ) && $value > 0 ? (string) (int) $value : '';
		};
		$prep  = $minutes( $in['prep_time_minutes'] ?? 0 );
		$cook  = $minutes( $in['cook_time_minutes'] ?? 0 );
		$total = $minutes( $in['total_time_minutes'] ?? ( ( (int) $prep + (int) $cook ) ?: 0 ) );

		$attributes = array(
			'id'               => 'wpzoom-recipe-card',
			'style'            => 'default',
			'hasInstance'      => true,
			'initDetails'      => true,
			'recipeTitle'      => $title,
			'summary'          => sanitize_textarea_field( (string) ( $in['summary'] ?? '' ) ),
			'jsonSummary'      => sanitize_textarea_field( (string) ( $in['summary'] ?? '' ) ),
			'hasImage'         => (bool) $image['id'],
			'image'            => $image,
			'course'           => array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $in['course'] ?? array() ) ) ) ),
			'cuisine'          => array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $in['cuisine'] ?? array() ) ) ) ),
			'difficulty'       => array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $in['difficulty'] ?? array() ) ) ) ),
			'keywords'         => array_values( array_filter( array_map( 'sanitize_text_field', (array) ( $in['keywords'] ?? array() ) ) ) ),
			'settings'         => array(
				array(
					'primary_color'      => '#222222',
					'displayServings'    => true,
					'displayPrepTime'    => true,
					'displayCookingTime' => true,
					'displayTotalTime'   => true,
					'displayCalories'    => (bool) $minutes( $in['calories'] ?? 0 ),
					'displayCourse'      => true,
					'displayCuisine'     => true,
					'displayDifficulty'  => true,
					'displayAuthor'      => true,
					'headerAlign'        => 'left',
					'ingredientsLayout'  => '1-column',
					'blockInit'          => true,
				),
			),
			'details'          => array(
				array( 'id' => 'detail-item-1', 'iconSet' => 'oldicon', 'icon' => 'food', 'label' => 'Servings', 'unit' => 'servings', 'value' => (string) (int) ( $in['servings'] ?? 4 ) ),
				array( 'id' => 'detail-item-2', 'iconSet' => 'oldicon', 'icon' => 'clock', 'label' => 'Prep time', 'unit' => 'minutes', 'value' => $prep ),
				array( 'id' => 'detail-item-3', 'iconSet' => 'foodicons', 'icon' => 'cooking-food-in-a-hot-casserole', 'label' => 'Cooking time', 'unit' => 'minutes', 'value' => $cook ),
				array( 'id' => 'detail-item-4', 'iconSet' => 'foodicons', 'icon' => 'fire-flames', 'label' => 'Calories', 'unit' => 'kcal', 'value' => $minutes( $in['calories'] ?? 0 ) ),
				array( 'id' => 'detail-item-5', 'iconSet' => 'fa', '_prefix' => 'far', 'icon' => 'clock' ),
				array( 'id' => 'detail-item-6', 'iconSet' => 'oldicon', 'icon' => 'chef-cooking' ),
				array( 'id' => 'detail-item-7', 'iconSet' => 'oldicon', 'icon' => 'food-1' ),
				array( 'id' => 'detail-item-8', 'iconSet' => 'fa', '_prefix' => 'fas', 'icon' => 'sort-amount-down' ),
				array( 'id' => 'detail-item-9', 'iconSet' => 'fa', '_prefix' => 'far', 'icon' => 'clock', 'label' => 'Total time', 'unit' => 'minutes', 'value' => $total ),
				array( 'id' => 'detail-item-10', 'iconSet' => 'fa', '_prefix' => 'fas', 'icon' => 'sort-amount-down', 'value' => 1 ),
			),
			'ingredientsTitle' => 'Ingredients',
			'directionsTitle'  => 'Directions',
			'notesTitle'       => 'Notes',
			'ingredients'      => $list( $ingredients, 'name', 'jsonName', 'ingredient-item-' ),
			'steps'            => $list( $steps, 'text', 'jsonText', 'direction-step-' ),
			'notes'            => implode( '', array_map( static function ( $n ) { return '<li>' . esc_html( sanitize_text_field( (string) $n ) ) . '</li>'; }, (array) ( $in['notes'] ?? array() ) ) ),
		);

		$block   = serialize_block( array( 'blockName' => 'wpzoom-recipe-card/block-recipe-card', 'attrs' => $attributes, 'innerBlocks' => array(), 'innerHTML' => '', 'innerContent' => array() ) );
		$content = self::to_blocks( (string) ( $in['intro_content'] ?? '' ) );
		$content = ( '' !== $content ? $content . "\n\n" : '' ) . $block;
		$updated = wp_update_post( array( 'ID' => $post_id, 'post_content' => wp_slash( $content ) ), true );
		if ( is_wp_error( $updated ) ) {
			return $updated;
		}

		$out          = self::post_links( $post_id );
		$out['post_type'] = $post_type;
		$out['notes'] = $notes;
		return $out;
	}
}
