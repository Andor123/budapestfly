<?php
/**
 * REST API for the new form builder.
 *
 * Namespace: wpzf/v1
 *
 *   GET    /forms                        list forms (id, title, modified, status, submissions)
 *   POST   /forms                        create new form (returns id)
 *   GET    /forms/<id>                   read full form (schema + meta)
 *   PUT    /forms/<id>                   update schema, settings, title
 *   DELETE /forms/<id>                   trash form
 *   POST   /forms/<id>/duplicate         duplicate a form
 *
 * All endpoints require manage_options or edit_posts capability.
 *
 * @package WPZOOM_Forms
 */

defined( 'ABSPATH' ) || exit;

class WPZOOM_Forms_REST {

	const NAMESPACE_V1 = 'wpzf/v1';

	public function register() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		register_rest_route( self::NAMESPACE_V1, '/forms', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'list_forms' ),
				'permission_callback' => array( $this, 'check_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::CREATABLE,
				'callback'            => array( $this, 'create_form' ),
				'permission_callback' => array( $this, 'check_manage' ),
				'args'                => array(
					'title' => array( 'type' => 'string', 'required' => false ),
				),
			),
		) );

		register_rest_route( self::NAMESPACE_V1, '/forms/(?P<id>\d+)', array(
			array(
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => array( $this, 'read_form' ),
				'permission_callback' => array( $this, 'check_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::EDITABLE,
				'callback'            => array( $this, 'update_form' ),
				'permission_callback' => array( $this, 'check_manage' ),
			),
			array(
				'methods'             => WP_REST_Server::DELETABLE,
				'callback'            => array( $this, 'delete_form' ),
				'permission_callback' => array( $this, 'check_manage' ),
			),
		) );

		register_rest_route( self::NAMESPACE_V1, '/forms/(?P<id>\d+)/duplicate', array(
			'methods'             => WP_REST_Server::CREATABLE,
			'callback'            => array( $this, 'duplicate_form' ),
			'permission_callback' => array( $this, 'check_manage' ),
		) );

		register_rest_route( self::NAMESPACE_V1, '/field-types', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'field_types' ),
			'permission_callback' => array( $this, 'check_manage' ),
		) );

		register_rest_route( self::NAMESPACE_V1, '/templates', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'list_templates' ),
			'permission_callback' => array( $this, 'check_manage' ),
		) );

		register_rest_route( self::NAMESPACE_V1, '/option-lists', array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => array( $this, 'option_lists' ),
			'permission_callback' => array( $this, 'check_manage' ),
		) );
	}

	/**
	 * Return summaries of all prebuilt form templates, including a v2 schema
	 * preview computed by parsing the template's block markup through the
	 * Migration helper. This lets the builder render a live preview of the
	 * form before the user commits to creating it.
	 */
	public function list_templates() {
		$templates = WPZOOM_Forms_Templates::all();
		if ( empty( $templates ) ) return rest_ensure_response( array() );

		$out = array();
		foreach ( $templates as $t ) {
			$is_pro = ! empty( $t['is_pro'] );
			$out[] = array(
				'id'     => isset( $t['id'] ) ? $t['id'] : '',
				'name'   => isset( $t['name'] ) ? $t['name'] : '',
				'desc'   => isset( $t['desc'] ) ? $t['desc'] : '',
				'icon'   => isset( $t['icon'] ) ? $t['icon'] : '',
				'isPro'  => $is_pro,
				// PRO templates have no markup in the free build, so skip the schema cost.
				'schema' => $is_pro ? null : $this->schema_for_template( $t ),
			);
		}
		return rest_ensure_response( $out );
	}

	/**
	 * Compile a v2 schema preview for a template without persisting anything.
	 */
	private function schema_for_template( $template ) {
		$content = isset( $template['content'] ) ? $template['content'] : '';
		return WPZOOM_Forms_Migration::build_from_content( $content );
	}

	/** Predefined option-list packs (countries, US states). */
	public function option_lists() {
		return rest_ensure_response( WPZOOM_Forms_Option_Lists::all() );
	}

	public function check_manage() {
		return current_user_can( 'edit_posts' );
	}

	public function field_types() {
		return rest_ensure_response( WPZOOM_Forms_Schema::field_types() );
	}

	public function list_forms( $req ) {
		$q = new WP_Query( array(
			'post_type'      => 'wpzf-form',
			'post_status'    => array( 'publish', 'draft', 'pending', 'private' ),
			'posts_per_page' => 200,
			'orderby'        => 'modified',
			'order'          => 'DESC',
			'no_found_rows'  => true,
		) );
		$items = array();
		foreach ( $q->posts as $p ) {
			$items[] = $this->summary( $p );
		}
		return rest_ensure_response( $items );
	}

	private function submission_count( $form_id ) {
		$q = new WP_Query( array(
			'post_type'      => 'wpzf-submission',
			'posts_per_page' => 1,
			'post_status'    => array( 'publish', 'spam' ),
			'meta_query'     => array(
				array( 'key' => '_wpzf_form_id', 'value' => (int) $form_id ),
			),
			'fields'         => 'ids',
		) );
		return (int) $q->found_posts;
	}

	private function summary( $p ) {
		return array(
			'id'           => $p->ID,
			'title'        => get_the_title( $p ),
			'status'       => $p->post_status,
			'modified'     => mysql2date( 'c', $p->post_modified_gmt ?: $p->post_modified, false ),
			'submissions'  => $this->submission_count( $p->ID ),
			'editUrl'      => admin_url( 'admin.php?page=wpzf-form-builder&id=' . $p->ID ),
			'shortcode'    => '[wpzf_form id="' . $p->ID . '"]',
		);
	}

	public function create_form( $req ) {
		$title    = $req->get_param( 'title' );
		$template = $req->get_param( 'template' );

		// If a template id is given, prefer its name for the post title.
		$template_data = null;
		if ( $template ) {
			$template_data = WPZOOM_Forms_Templates::get( $template );
			if ( $template_data && ! empty( $template_data['is_pro'] ) ) {
				return new WP_Error( 'wpzf_pro_only', __( 'This template is only available in WPZOOM Forms PRO.', 'wpzoom-forms' ), array( 'status' => 402 ) );
			}
			if ( $template_data && empty( $title ) ) {
				$title = $template_data['name'];
			}
		}

		if ( empty( $title ) ) $title = __( 'New Form', 'wpzoom-forms' );

		$post_content = $template_data ? $template_data['content'] : '';

		$id = wp_insert_post( array(
			'post_type'    => 'wpzf-form',
			'post_status'  => 'publish',
			'post_title'   => sanitize_text_field( $title ),
			'post_content' => $post_content,
		) );

		if ( is_wp_error( $id ) || $id === 0 ) {
			return new WP_Error( 'wpzf_create_failed', __( 'Could not create form.', 'wpzoom-forms' ), array( 'status' => 500 ) );
		}

		// If template content was supplied, convert it (block markup) to v2 schema.
		if ( $template_data ) {
			$schema = WPZOOM_Forms_Migration::build_from_post_content( $id );
			WPZOOM_Forms_Schema::save_for_form( $id, $schema );
		} else {
			WPZOOM_Forms_Schema::save_for_form( $id, WPZOOM_Forms_Schema::defaults() );
		}

		update_post_meta( $id, '_form_method', 'combined' );
		update_post_meta( $id, '_form_email', get_option( 'admin_email' ) );
		update_post_meta( $id, '_form_subject', __( 'New Form Submission', 'wpzoom-forms' ) );
		update_post_meta( $id, '_form_success_message', __( "Thanks! We've received your submission.", 'wpzoom-forms' ) );
		update_post_meta( $id, '_form_failure_message', __( 'Sorry, something went wrong. Please try again.', 'wpzoom-forms' ) );

		return rest_ensure_response( $this->read_form_payload( $id ) );
	}

	public function read_form( $req ) {
		$id = (int) $req['id'];
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'wpzf-form' ) {
			return new WP_Error( 'wpzf_not_found', __( 'Form not found.', 'wpzoom-forms' ), array( 'status' => 404 ) );
		}
		return rest_ensure_response( $this->read_form_payload( $id ) );
	}

	private function read_form_payload( $id ) {
		$schema = WPZOOM_Forms_Schema::get_for_form( $id );
		if ( $schema === null ) {
			// Build schema in memory for the builder UI — do not persist. Schema is
			// only written to postmeta when the user explicitly clicks Save.
			$schema = WPZOOM_Forms_Migration::build_from_post_content( $id );
		}

		$post = get_post( $id );
		return array(
			'id'        => $id,
			'title'     => get_the_title( $post ),
			'status'    => $post->post_status,
			'modified'  => mysql2date( 'c', $post->post_modified_gmt ?: $post->post_modified, false ),
			'editUrl'   => admin_url( 'admin.php?page=wpzf-form-builder&id=' . $id ),
			'shortcode' => '[wpzf_form id="' . $id . '"]',
			'schema'    => $schema,
			'notifications' => array(
				'method'           => get_post_meta( $id, '_form_method', true ) ?: 'combined',
				'email'            => get_post_meta( $id, '_form_email', true ),
				'subject'          => get_post_meta( $id, '_form_subject', true ),
				'successMessage'   => get_post_meta( $id, '_form_success_message', true ),
				'failureMessage'   => get_post_meta( $id, '_form_failure_message', true ),
			),
			'submissions' => $this->submission_count( $id ),
		);
	}

	public function update_form( $req ) {
		$id = (int) $req['id'];
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'wpzf-form' ) {
			return new WP_Error( 'wpzf_not_found', __( 'Form not found.', 'wpzoom-forms' ), array( 'status' => 404 ) );
		}

		$body = $req->get_json_params();
		if ( ! is_array( $body ) ) $body = $req->get_params();

		// Update title
		if ( isset( $body['title'] ) ) {
			wp_update_post( array(
				'ID'         => $id,
				'post_title' => sanitize_text_field( wp_unslash( $body['title'] ) ),
			) );
		}

		// Update schema (fields + form settings)
		if ( isset( $body['schema'] ) ) {
			WPZOOM_Forms_Schema::save_for_form( $id, $body['schema'] );
		}

		// Update notification settings
		if ( isset( $body['notifications'] ) && is_array( $body['notifications'] ) ) {
			$n = $body['notifications'];
			if ( isset( $n['method'] ) ) {
				$method = sanitize_key( $n['method'] );
				if ( ! in_array( $method, array( 'email', 'db', 'combined' ), true ) ) $method = 'combined';
				update_post_meta( $id, '_form_method', $method );
			}
			if ( isset( $n['email'] ) ) {
				$email = sanitize_email( wp_unslash( $n['email'] ) );
				update_post_meta( $id, '_form_email', $email );
			}
			if ( isset( $n['subject'] ) ) {
				update_post_meta( $id, '_form_subject', sanitize_text_field( wp_unslash( $n['subject'] ) ) );
			}
			if ( isset( $n['successMessage'] ) ) {
				update_post_meta( $id, '_form_success_message', sanitize_text_field( wp_unslash( $n['successMessage'] ) ) );
			}
			if ( isset( $n['failureMessage'] ) ) {
				update_post_meta( $id, '_form_failure_message', sanitize_text_field( wp_unslash( $n['failureMessage'] ) ) );
			}
		}

		return rest_ensure_response( $this->read_form_payload( $id ) );
	}

	public function delete_form( $req ) {
		$id = (int) $req['id'];
		$post = get_post( $id );
		if ( ! $post || $post->post_type !== 'wpzf-form' ) {
			return new WP_Error( 'wpzf_not_found', __( 'Form not found.', 'wpzoom-forms' ), array( 'status' => 404 ) );
		}
		$force = (bool) $req->get_param( 'force' );
		$result = $force ? wp_delete_post( $id, true ) : wp_trash_post( $id );
		if ( ! $result ) {
			return new WP_Error( 'wpzf_delete_failed', __( 'Could not delete form.', 'wpzoom-forms' ), array( 'status' => 500 ) );
		}
		return rest_ensure_response( array( 'deleted' => true, 'id' => $id ) );
	}

	public function duplicate_form( $req ) {
		$src_id = (int) $req['id'];
		$src = get_post( $src_id );
		if ( ! $src || $src->post_type !== 'wpzf-form' ) {
			return new WP_Error( 'wpzf_not_found', __( 'Form not found.', 'wpzoom-forms' ), array( 'status' => 404 ) );
		}

		$new_id = wp_insert_post( array(
			'post_type'    => 'wpzf-form',
			'post_status'  => 'publish',
			/* translators: %s: original form title */
			'post_title'   => sprintf( __( '%s (Copy)', 'wpzoom-forms' ), get_the_title( $src ) ),
			'post_content' => $src->post_content,
		) );

		if ( is_wp_error( $new_id ) || $new_id === 0 ) {
			return new WP_Error( 'wpzf_dup_failed', __( 'Could not duplicate form.', 'wpzoom-forms' ), array( 'status' => 500 ) );
		}

		// Copy all relevant meta.
		foreach ( array( WPZOOM_Forms_Schema::META_KEY, '_form_method', '_form_email', '_form_subject', '_form_success_message', '_form_failure_message' ) as $key ) {
			$val = get_post_meta( $src_id, $key, true );
			if ( $val !== '' ) update_post_meta( $new_id, $key, $val );
		}

		// If src had no schema, migrate first then copy.
		if ( ! WPZOOM_Forms_Schema::form_has_schema( $new_id ) ) {
			WPZOOM_Forms_Migration::migrate( $new_id );
		}

		return rest_ensure_response( $this->read_form_payload( $new_id ) );
	}
}
