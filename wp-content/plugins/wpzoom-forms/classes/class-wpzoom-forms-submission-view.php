<?php
/**
 * Polished submission detail view.
 *
 * Replaces the existing single-submission screen with a cleaner two-column
 * layout: structured fields on the left, submission metadata on the right.
 * Re-uses the wpzf-submission CPT — no schema change required.
 *
 * @package WPZOOM_Forms
 */

defined( 'ABSPATH' ) || exit;

class WPZOOM_Forms_Submission_View {

	public function register() {
		add_action( 'add_meta_boxes_wpzf-submission', array( $this, 'add_meta_boxes' ), 20 );
		add_action( 'admin_enqueue_scripts',          array( $this, 'enqueue_assets' ) );
		add_action( 'admin_head-post.php',            array( $this, 'force_two_columns' ) );
		// Suppress the default "Custom Fields" meta box for submissions — distracting noise.
		add_action( 'admin_init', function() {
			remove_post_type_support( 'wpzf-submission', 'custom-fields' );
		}, 99 );
	}

	/** Force 2-column layout on the submission edit screen so the side meta box renders. */
	public function force_two_columns() {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || $screen->post_type !== 'wpzf-submission' ) return;
		add_screen_option( 'layout_columns', array( 'max' => 2, 'default' => 2 ) );
	}

	public function add_meta_boxes() {
		// Remove the legacy meta boxes (added by the main plugin class).
		remove_meta_box( 'wpzf-submission-mb',         'wpzf-submission', 'normal' );
		remove_meta_box( 'wpzf-submission-details-mb', 'wpzf-submission', 'side' );

		add_meta_box(
			'wpzf-v2-submission-data',
			__( 'Submission Details', 'wpzoom-forms' ),
			array( $this, 'render_main' ),
			'wpzf-submission',
			'normal',
			'high'
		);
		add_meta_box(
			'wpzf-v2-submission-meta',
			__( 'About this submission', 'wpzoom-forms' ),
			array( $this, 'render_side' ),
			'wpzf-submission',
			'normal',
			'low'
		);
	}

	public function render_main( $post ) {
		$fields = get_post_meta( $post->ID, '_wpzf_fields', true );
		if ( ! is_array( $fields ) || empty( $fields ) ) {
			echo '<p class="wpzf-empty">' . esc_html__( 'No data was captured for this submission.', 'wpzoom-forms' ) . '</p>';
			return;
		}
		echo '<table class="wpzf-sub-table"><tbody>';
		foreach ( $fields as $label => $value ) {
			echo '<tr><th>' . esc_html( $label ) . '</th><td>';
			if ( filter_var( $value, FILTER_VALIDATE_EMAIL ) ) {
				echo '<a href="mailto:' . esc_attr( $value ) . '">' . esc_html( $value ) . '</a>';
			} elseif ( filter_var( $value, FILTER_VALIDATE_URL ) ) {
				echo '<a href="' . esc_url( $value ) . '" target="_blank" rel="noopener">' . esc_html( $value ) . '</a>';
			} else {
				echo nl2br( esc_html( $value ) );
			}
			echo '</td></tr>';
		}
		echo '</tbody></table>';
	}

	public function render_side( $post ) {
		$form_id = (int) get_post_meta( $post->ID, '_wpzf_form_id', true );
		$meta    = get_post_meta( $post->ID, '_wpzf_meta', true );
		$form    = $form_id > 0 ? get_post( $form_id ) : null;

		echo '<ul class="wpzf-sub-side">';
		if ( $form ) {
			echo '<li><strong>' . esc_html__( 'Form', 'wpzoom-forms' ) . '</strong><span><a href="' . esc_url( admin_url( 'admin.php?page=wpzf-form-builder&id=' . $form_id ) ) . '">' . esc_html( get_the_title( $form ) ) . '</a></span></li>';
		}
		echo '<li><strong>' . esc_html__( 'Submitted', 'wpzoom-forms' ) . '</strong><span>' . esc_html( get_the_date( '', $post ) . ' ' . get_the_time( '', $post ) ) . '</span></li>';
		if ( is_array( $meta ) ) {
			if ( ! empty( $meta['ip'] ) ) {
				echo '<li><strong>' . esc_html__( 'IP', 'wpzoom-forms' ) . '</strong><span>' . esc_html( $meta['ip'] ) . '</span></li>';
			}
			if ( ! empty( $meta['referer'] ) ) {
				echo '<li><strong>' . esc_html__( 'Referer', 'wpzoom-forms' ) . '</strong><span><a href="' . esc_url( $meta['referer'] ) . '" target="_blank" rel="noopener">' . esc_html( $meta['referer'] ) . '</a></span></li>';
			}
			if ( ! empty( $meta['userAgent'] ) ) {
				echo '<li><strong>' . esc_html__( 'User Agent', 'wpzoom-forms' ) . '</strong><span class="wpzf-ua">' . esc_html( $meta['userAgent'] ) . '</span></li>';
			}
		}
		echo '<li><strong>' . esc_html__( 'Status', 'wpzoom-forms' ) . '</strong><span class="wpzf-status wpzf-status--' . esc_attr( $post->post_status ) . '">' . esc_html( $post->post_status ) . '</span></li>';
		echo '</ul>';
	}

	public function enqueue_assets( $hook ) {
		if ( $hook !== 'post.php' && $hook !== 'post-new.php' ) return;
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || $screen->post_type !== 'wpzf-submission' ) return;

		$css = WPZOOM_FORMS_PATH . 'build/submission-view/style.css';
		if ( file_exists( $css ) ) {
			wp_enqueue_style(
				'wpzf-submission-view',
				WPZOOM_FORMS_URL . 'build/submission-view/style.css',
				array(),
				WPZOOM_FORMS_VERSION
			);
		}
	}
}
