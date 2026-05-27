<?php
/**
 * Custom admin pages for the v2 form builder.
 *
 * Adds two screens:
 *   - admin.php?page=wpzf-form-builder&id=<id>   The full-screen React builder
 *   - admin.php?page=wpzf-form-builder           Creates a new form, then redirects
 *
 * Also intercepts edit links so clicking a form in the CPT list opens the new
 * builder instead of the block editor, and disables Gutenberg for wpzf-form.
 *
 * @package WPZOOM_Forms
 */

defined( 'ABSPATH' ) || exit;

class WPZOOM_Forms_Builder_Page {

	const SLUG = 'wpzf-form-builder';

	public function register() {
		add_action( 'admin_menu',         array( $this, 'register_page' ), 9 );
		add_action( 'admin_init',         array( $this, 'maybe_intercept_edit_screen' ) );
		add_filter( 'use_block_editor_for_post_type', array( $this, 'disable_block_editor' ), 10, 2 );
		add_filter( 'get_edit_post_link', array( $this, 'replace_edit_link' ), 10, 2 );
		add_filter( 'post_row_actions',   array( $this, 'replace_row_actions' ), 11, 2 );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_builder_assets' ) );
		add_filter( 'wp_redirect',        array( $this, 'rewrite_post_new_redirect' ) );
	}

	public function register_page() {
		// Hidden admin page (not in menu) — accessed only via direct URL.
		add_submenu_page(
			'edit.php?post_type=wpzf-form',
			__( 'Form Builder', 'wpzoom-forms' ),
			__( 'Form Builder', 'wpzoom-forms' ),
			'edit_posts',
			self::SLUG,
			array( $this, 'render_page' )
		);

		// Hide it from the actual submenu (no menu label).
		add_action( 'admin_head', array( $this, 'hide_submenu' ) );
	}

	public function hide_submenu() {
		global $submenu;
		if ( ! empty( $submenu['edit.php?post_type=wpzf-form'] ) ) {
			foreach ( $submenu['edit.php?post_type=wpzf-form'] as $idx => $item ) {
				if ( isset( $item[2] ) && $item[2] === self::SLUG ) {
					unset( $submenu['edit.php?post_type=wpzf-form'][ $idx ] );
				}
			}
		}
	}

	public function disable_block_editor( $use, $post_type ) {
		if ( $post_type === 'wpzf-form' ) return false;
		return $use;
	}

	/**
	 * If user lands on post.php?post=NN&action=edit for a wpzf-form, send them to the builder.
	 */
	public function maybe_intercept_edit_screen() {
		global $pagenow;
		if ( ! is_admin() ) return;

		// Edit existing
		if ( $pagenow === 'post.php' && isset( $_GET['action'] ) && $_GET['action'] === 'edit' && isset( $_GET['post'] ) ) {
			$pid = (int) $_GET['post'];
			if ( $pid > 0 && get_post_type( $pid ) === 'wpzf-form' ) {
				wp_safe_redirect( admin_url( 'admin.php?page=' . self::SLUG . '&id=' . $pid ) );
				exit;
			}
		}

		// New form via post-new.php → forward to the builder's template picker.
		if ( $pagenow === 'post-new.php' && isset( $_GET['post_type'] ) && $_GET['post_type'] === 'wpzf-form' ) {
			$target = admin_url( 'admin.php?page=' . self::SLUG );
			if ( isset( $_GET['template'] ) ) {
				$target = add_query_arg( 'template', sanitize_key( $_GET['template'] ), $target );
			}
			wp_safe_redirect( $target );
			exit;
		}
	}

	/**
	 * Sometimes WP redirects to post.php after save; just let that happen normally for non-forms.
	 * For forms we never get there (because the builder uses REST, not the post.php save flow).
	 * This filter is a safety net.
	 */
	public function rewrite_post_new_redirect( $location ) {
		if ( strpos( $location, 'post-new.php?post_type=wpzf-form' ) !== false ) {
			return admin_url( 'admin.php?page=' . self::SLUG );
		}
		return $location;
	}

	public function replace_edit_link( $url, $post_id ) {
		if ( get_post_type( $post_id ) === 'wpzf-form' ) {
			return admin_url( 'admin.php?page=' . self::SLUG . '&id=' . $post_id );
		}
		return $url;
	}

	public function replace_row_actions( $actions, $post ) {
		if ( $post && $post->post_type === 'wpzf-form' ) {
			if ( isset( $actions['edit'] ) ) {
				$url = admin_url( 'admin.php?page=' . self::SLUG . '&id=' . $post->ID );
				$actions['edit'] = sprintf( '<a href="%s">%s</a>', esc_url( $url ), esc_html__( 'Edit', 'wpzoom-forms' ) );
			}
			// Remove "Quick Edit" / "View" — they don't apply.
			unset( $actions['inline hide-if-no-js'] );
			unset( $actions['view'] );
		}
		return $actions;
	}

	public function enqueue_builder_assets( $hook ) {
		if ( $hook !== 'wpzf-form_page_' . self::SLUG ) return;

		$base_path = WPZOOM_FORMS_PATH . 'build/builder/';
		$base_url  = WPZOOM_FORMS_URL  . 'build/builder/';

		$asset_file = $base_path . 'script.asset.php';
		$asset = file_exists( $asset_file ) ? include $asset_file : array( 'dependencies' => array(), 'version' => WPZOOM_FORMS_VERSION );

		wp_enqueue_script(
			'wpzf-builder',
			$base_url . 'script.js',
			array_merge( $asset['dependencies'], array( 'wp-element', 'wp-api-fetch', 'wp-i18n', 'wp-components' ) ),
			$asset['version'],
			true
		);

		wp_enqueue_style(
			'wpzf-builder',
			$base_url . 'style.css',
			array( 'wp-components' ),
			$asset['version']
		);

		// Also enqueue the frontend form CSS so the preview matches the public form.
		wp_enqueue_style(
			'wpzoom-forms-css-frontend-formblock',
			WPZOOM_FORMS_URL . 'build/form-block/frontend/style.css',
			array(),
			WPZOOM_FORMS_VERSION
		);

		wp_localize_script( 'wpzf-builder', 'wpzfBuilder', array(
			'apiRoot'   => esc_url_raw( rest_url( 'wpzf/v1' ) ),
			'nonce'     => wp_create_nonce( 'wp_rest' ),
			'fieldTypes'=> WPZOOM_Forms_Schema::field_types(),
			'fieldWidths' => WPZOOM_Forms_Schema::field_widths(),
			'adminUrl'  => admin_url(),
			'pluginUrl' => WPZOOM_FORMS_URL,
			'newFormUrl'=> admin_url( 'admin.php?page=' . self::SLUG ),
			'formsListUrl' => admin_url( 'edit.php?post_type=wpzf-form' ),
			'submissionsListUrl' => admin_url( 'edit.php?post_type=wpzf-submission' ),
		) );

		wp_set_script_translations( 'wpzf-builder', 'wpzoom-forms' );
	}

	public function render_page() {
		$id       = isset( $_GET['id'] ) ? (int) $_GET['id'] : 0;
		$template = isset( $_GET['template'] ) ? sanitize_key( $_GET['template'] ) : '';
		echo '<div id="wpzf-builder-root" class="wpzf-builder-root" data-form-id="' . esc_attr( $id ) . '" data-template="' . esc_attr( $template ) . '"></div>';
	}
}
