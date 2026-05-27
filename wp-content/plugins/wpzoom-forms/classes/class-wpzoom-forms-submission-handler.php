<?php
/**
 * Handle a v2 form submission.
 *
 * Replaces the legacy block-parsing logic with schema-driven processing:
 *   1. Resolve form + schema (auto-migrate if legacy)
 *   2. Honeypot check
 *   3. CAPTCHA check (re-uses settings/services from the main plugin class)
 *   4. Required-field validation
 *   5. Collect submitted values keyed by field label
 *   6. Apply spam check (Akismet via main class) → email + DB store per form_method
 *   7. Redirect (graceful) or return JSON (when ajax)
 *
 * The action name `wpzf_submit` is preserved so existing embedded forms continue to work.
 *
 * @package WPZOOM_Forms
 */

defined( 'ABSPATH' ) || exit;

class WPZOOM_Forms_Submission_Handler {

	public function register() {
		// Replace the legacy handler — main bootstrap removes the old binding.
		add_action( 'admin_post_wpzf_submit',        array( $this, 'handle' ), 10 );
		add_action( 'admin_post_nopriv_wpzf_submit', array( $this, 'handle' ), 10 );
	}

	public function handle() {
		$is_ajax = isset( $_SERVER['HTTP_X_REQUESTED_WITH'] ) && strtolower( wp_unslash( $_SERVER['HTTP_X_REQUESTED_WITH'] ) ) === 'xmlhttprequest';
		$referer = isset( $_POST['_wp_http_referer'] ) ? sanitize_text_field( wp_unslash( $_POST['_wp_http_referer'] ) ) : home_url();
		$form_id = isset( $_POST['form_id'] ) ? (int) $_POST['form_id'] : 0;

		$respond = function( $success, $message = '', $errors = array() ) use ( $is_ajax, $referer, $form_id ) {
			if ( $is_ajax ) {
				wp_send_json( array(
					'success' => $success,
					'message' => $message,
					'errors'  => $errors,
				), $success ? 200 : 422 );
			}
			$url = add_query_arg( array(
				'success'             => $success ? '1' : '0',
				'wpzf_submitted_form' => $form_id,
			), $referer );
			wp_safe_redirect( $url . ( $form_id > 0 ? '#wpzf-' . $form_id . '-notice' : '' ) );
			exit;
		};

		if ( ! isset( $_POST['_wpnonce'] ) || ! wp_verify_nonce( wp_unslash( $_POST['_wpnonce'] ), 'wpzf_submit' ) ) {
			$respond( false, __( 'Security check failed. Please reload the page and try again.', 'wpzoom-forms' ) );
		}

		if ( $form_id < 1 || get_post_type( $form_id ) !== 'wpzf-form' ) {
			$respond( false, __( 'Form not found.', 'wpzoom-forms' ) );
		}

		// Honeypot: silently swallow as success (don't reveal we're filtering).
		if ( ! empty( $_POST['wpzf_hp'] ) ) {
			$respond( true, __( 'Thanks!', 'wpzoom-forms' ) );
		}

		$schema = WPZOOM_Forms_Schema::get_for_form( $form_id );
		if ( $schema === null ) {
			// No schema means this is a legacy block-based form — hand off to the
			global $wpzoom_forms;
			if ( $wpzoom_forms ) {
				$wpzoom_forms->action_form_post();
			}
			exit;
		}

		// CAPTCHA check via main plugin's existing infrastructure (delegates to its config).
		if ( ! $this->captcha_ok() ) {
			$respond( false, __( 'CAPTCHA verification failed. Please try again.', 'wpzoom-forms' ) );
		}

		// Collect + validate
		$collected = array();   // label => value (sanitized)
		$errors    = array();   // field_id => message
		$reply_to_email = '';
		$override_subject = '';

		$types = WPZOOM_Forms_Schema::field_types();
		foreach ( $schema['fields'] as $field ) {
			if ( ! isset( $types[ $field['type'] ] ) || empty( $types[ $field['type'] ]['is_input'] ) ) continue;

			$name = 'wpzf_' . $field['id'];
			$raw  = isset( $_POST[ $name ] ) ? wp_unslash( $_POST[ $name ] ) : '';

			if ( is_array( $raw ) ) {
				$raw = array_map( 'sanitize_text_field', $raw );
				// For checkboxes/radio/select with options, swap submitted *values* for the
				// human-readable *labels* so the email + admin list don't show opaque slugs.
				$mapped = $this->values_to_labels( $field, $raw );
				$value_for_email = implode( ', ', $mapped );
				$value_for_store = $value_for_email;
				$is_empty = empty( $raw );
			} else {
				if ( $field['type'] === 'textarea' ) {
					$value_for_store = sanitize_textarea_field( $raw );
					$value_for_email = $value_for_store;
				} elseif ( $field['type'] === 'email' ) {
					$value_for_store = sanitize_email( $raw );
					$value_for_email = $value_for_store;
				} elseif ( $field['type'] === 'url' ) {
					$value_for_store = esc_url_raw( $raw );
					$value_for_email = $value_for_store;
				} elseif ( in_array( $field['type'], array( 'select', 'radio' ), true ) ) {
					$value_for_store = sanitize_text_field( $raw );
					$mapped = $this->values_to_labels( $field, array( $value_for_store ) );
					$value_for_email = $mapped[0] ?? $value_for_store;
				} elseif ( $field['type'] === 'checkbox' ) {
					// Single-checkbox: empty unless checked. Render as human-friendly text.
					$value_for_store = sanitize_text_field( $raw );
					if ( $value_for_store !== '' ) {
						$value_for_email = ! empty( $field['checkboxText'] ) ? $field['checkboxText'] : __( 'Yes', 'wpzoom-forms' );
					} else {
						$value_for_email = '';
					}
				} else {
					$value_for_store = sanitize_text_field( $raw );
					$value_for_email = $value_for_store;
				}
				$is_empty = $value_for_store === '' || $value_for_store === null;
			}

			if ( $field['type'] === 'email' && $value_for_store !== '' && ! is_email( $value_for_store ) ) {
				$errors[ $field['id'] ] = __( 'Please enter a valid email address.', 'wpzoom-forms' );
			}

			if ( ! empty( $field['required'] ) && $is_empty ) {
				$errors[ $field['id'] ] = __( 'This field is required.', 'wpzoom-forms' );
			}

			if ( $field['type'] === 'email' && ! empty( $field['isReplyTo'] ) && $value_for_store !== '' && is_email( $value_for_store ) ) {
				$reply_to_email = $value_for_store;
			}

			if ( $field['type'] === 'text' && ! empty( $field['isSubject'] ) && $value_for_store !== '' ) {
				$override_subject = $value_for_store;
			}

			$label_key = $this->label_for( $field );
			$collected[ $label_key ] = $value_for_email;
		}

		if ( ! empty( $errors ) ) {
			$respond( false, __( 'Please fix the errors below.', 'wpzoom-forms' ), $errors );
		}

		// Anti-spam (Akismet) via main plugin class. Akismet expects the standard
		// comment-check shape — flatten what we have into name/email/url/message
		// the way the legacy not_spam() reads it.
		global $wpzoom_forms;
		if ( $wpzoom_forms && method_exists( $wpzoom_forms, 'not_spam' ) ) {
			$name_for_akismet = '';
			$url_for_akismet  = '';
			$message_lines    = array();
			foreach ( $schema['fields'] as $f ) {
				if ( empty( $types[ $f['type'] ]['is_input'] ) ) continue;
				$value = isset( $collected[ $this->label_for( $f ) ] ) ? $collected[ $this->label_for( $f ) ] : '';
				if ( $value === '' ) continue;
				if ( $f['type'] === 'name' && $name_for_akismet === '' ) {
					$name_for_akismet = $value;
				} elseif ( $f['type'] === 'url' && $url_for_akismet === '' ) {
					$url_for_akismet = $value;
				} elseif ( in_array( $f['type'], array( 'textarea', 'text' ), true ) ) {
					$message_lines[] = $value;
				}
			}
			$details = array(
				'name'    => $name_for_akismet,
				'from'    => $reply_to_email,
				'url'     => $url_for_akismet,
				'message' => trim( implode( "\n\n", $message_lines ) ),
			);
			if ( ! $wpzoom_forms->not_spam( $details ) ) {
				// Mark as spam silently — keep UX, store as spam status.
				$this->store_submission( $form_id, $collected, 'spam' );
				$respond( true, __( 'Thanks!', 'wpzoom-forms' ) );
			}
		}

		// Route per form_method
		$method = get_post_meta( $form_id, '_form_method', true ) ?: 'combined';
		$mailed = null;
		$stored = null;
		$success_message = get_post_meta( $form_id, '_form_success_message', true ) ?: __( "Thanks! We've received your submission.", 'wpzoom-forms' );

		if ( $method === 'email' || $method === 'combined' ) {
			$mailed = $this->send_email( $form_id, $collected, $reply_to_email, $override_subject );
		}
		if ( $method === 'db' || $method === 'combined' ) {
			$stored = $this->store_submission( $form_id, $collected, 'publish' );
		}

		// Success policy: for "combined" we count it as success when EITHER channel
		// succeeded — losing the email shouldn't lose the submission.
		$success = false;
		if ( $method === 'email' )    $success = (bool) $mailed;
		if ( $method === 'db' )       $success = (bool) $stored;
		if ( $method === 'combined' ) $success = (bool) $mailed || (bool) $stored;

		if ( $success ) {
			$respond( true, $success_message );
		}
		$failure_message = get_post_meta( $form_id, '_form_failure_message', true ) ?: __( 'Sorry, something went wrong. Please try again.', 'wpzoom-forms' );
		$respond( false, $failure_message );
	}

	private function label_for( $field ) {
		$label = isset( $field['label'] ) && $field['label'] !== '' ? $field['label'] : ucfirst( $field['type'] );
		// Prevent collisions by appending the field id when duplicates exist.
		return $label;
	}

	/**
	 * Translate submitted option values into their human-readable labels for
	 * select / radio / checkboxes fields. Unmapped values pass through as-is
	 * so any custom or legacy data still appears in the output.
	 */
	private function values_to_labels( $field, $values ) {
		if ( empty( $field['options'] ) || ! is_array( $field['options'] ) ) {
			return $values;
		}
		$lookup = array();
		foreach ( $field['options'] as $opt ) {
			if ( isset( $opt['value'] ) ) {
				$lookup[ (string) $opt['value'] ] = isset( $opt['label'] ) && $opt['label'] !== '' ? $opt['label'] : $opt['value'];
			}
		}
		$out = array();
		foreach ( $values as $v ) {
			$key = (string) $v;
			$out[] = array_key_exists( $key, $lookup ) ? $lookup[ $key ] : $v;
		}
		return $out;
	}

	private function captcha_ok() {
		global $wpzoom_forms;
		if ( ! $wpzoom_forms || ! method_exists( $wpzoom_forms, 'get_spam_protection_config' ) ) return true;

		$config = $wpzoom_forms->get_spam_protection_config();
		$service = isset( $config['active_service'] ) ? $config['active_service'] : 'none';
		if ( $service === 'recaptcha' ) {
			$type    = isset( $config['type'] ) ? $config['type'] : 'v2';
			$token   = '';
			if ( $type === 'v3' && isset( $_POST['recaptcha_token'] ) ) {
				$token = sanitize_text_field( wp_unslash( $_POST['recaptcha_token'] ) );
			} elseif ( isset( $_POST['g-recaptcha-response'] ) ) {
				$token = sanitize_text_field( wp_unslash( $_POST['g-recaptcha-response'] ) );
			}
			if ( empty( $token ) ) return false;
			$secret = $type === 'v3' ? ( $config['recaptcha_v3_secret_key'] ?? '' ) : ( $config['recaptcha_v2_secret_key'] ?? '' );
			if ( empty( $secret ) ) return true; // mis-configured: don't block.
			$response = wp_remote_post( 'https://www.google.com/recaptcha/api/siteverify', array(
				'body' => array(
					'secret'   => $secret,
					'response' => $token,
					'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				),
			) );
			if ( is_wp_error( $response ) ) return false;
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			if ( empty( $body['success'] ) ) return false;
			if ( $type === 'v3' && isset( $body['score'] ) && $body['score'] < 0.5 ) return false;
			return true;
		}
		if ( $service === 'turnstile' ) {
			$token  = isset( $_POST['cf-turnstile-response'] ) ? sanitize_text_field( wp_unslash( $_POST['cf-turnstile-response'] ) ) : '';
			if ( empty( $token ) ) return false;
			$secret = $config['turnstile_secret_key'] ?? '';
			if ( empty( $secret ) ) return true;
			$response = wp_remote_post( 'https://challenges.cloudflare.com/turnstile/v0/siteverify', array(
				'body' => array(
					'secret'   => $secret,
					'response' => $token,
					'remoteip' => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
				),
			) );
			if ( is_wp_error( $response ) ) return false;
			$body = json_decode( wp_remote_retrieve_body( $response ), true );
			return ! empty( $body['success'] );
		}
		return true;
	}

	private function store_submission( $form_id, $fields, $status = 'publish' ) {
		$id = wp_insert_post( array(
			'post_type'      => 'wpzf-submission',
			'post_status'    => $status,
			'comment_status' => 'closed',
			'ping_status'    => 'closed',
			'post_title'     => sprintf(
				/* translators: %s: form title */
				__( 'Submission for %s', 'wpzoom-forms' ),
				get_the_title( $form_id )
			),
			'post_content'   => '',
			'meta_input'     => array(
				'_wpzf_form_id' => (int) $form_id,
				'_wpzf_fields'  => $fields,
				'_wpzf_meta'    => array(
					'ip'        => isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '',
					'userAgent' => isset( $_SERVER['HTTP_USER_AGENT'] ) ? sanitize_text_field( wp_unslash( $_SERVER['HTTP_USER_AGENT'] ) ) : '',
					'referer'   => isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) : '',
					'submittedAt' => current_time( 'mysql' ),
				),
			),
		) );
		return $id > 0;
	}

	private function send_email( $form_id, $fields, $reply_to_email, $override_subject ) {
		$site_name = sanitize_text_field( get_bloginfo( 'name' ) );
		$to        = get_post_meta( $form_id, '_form_email', true );
		if ( empty( $to ) || ! is_email( $to ) ) $to = get_option( 'admin_email' );

		$base_subject = get_post_meta( $form_id, '_form_subject', true ) ?: __( 'New Form Submission', 'wpzoom-forms' );
		$subject      = $override_subject !== '' ? $override_subject : $base_subject;
		$subject     .= sprintf( ' - %s', $site_name );

		$from_email = $reply_to_email ?: $to;

		// Stacked label / value rows (bold label, value underneath) — a clean,
		// simple layout that renders well everywhere.
		$rows = '';
		foreach ( $fields as $label => $val ) {
			$rows .= '<tr><td style="padding:0 0 18px 0;color:#222222;font-size:14px;line-height:1.6;vertical-align:top;">'
				. '<strong style="display:block;margin-bottom:3px;color:#1a1a1a;">' . esc_html( $label ) . '</strong>'
				. '<span style="color:#555555;">' . nl2br( esc_html( $val ) ) . '</span>'
				. '</td></tr>';
		}

		$footer = sprintf(
			/* translators: 1: site name (linked), 2: plugin name */
			esc_html__( 'Sent from %1$s using the %2$s plugin.', 'wpzoom-forms' ),
			'<a href="' . esc_url( home_url() ) . '" style="color:#888888;">' . esc_html( $site_name ) . '</a>',
			'<strong>WPZOOM Forms</strong>'
		);

		// All visual styling is inline (best for email clients). The small <style>
		// block carries only the things inline CSS can't express: the iOS
		// data-detector reset (stops Apple Mail recoloring dates/phones/addresses
		// inside submitted values) and Outlook/mobile text-size hardening.
		$body = '<!doctype html><html><head>'
			. '<meta charset="utf-8">'
			. '<meta name="viewport" content="width=device-width,initial-scale=1">'
			. '<style>'
			.   'a[x-apple-data-detectors]{color:inherit!important;text-decoration:none!important;font-size:inherit!important;font-family:inherit!important;font-weight:inherit!important;line-height:inherit!important;}'
			.   'table{mso-table-lspace:0pt;mso-table-rspace:0pt;}'
			.   'body,table,td,a{-webkit-text-size-adjust:100%;-ms-text-size-adjust:100%;}'
			. '</style>'
			. '</head>'
			. '<body style="margin:0;padding:24px;background:#f4f3f3;font-family:-apple-system,BlinkMacSystemFont,Segoe UI,Helvetica,Arial,sans-serif;color:#222222;">'
			.   '<div style="max-width:600px;margin:0 auto;background:#ffffff;border:1px solid #eaeaea;border-radius:10px;overflow:hidden;">'
			.     '<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">'
			.       '<tr><td style="padding:28px 28px 10px 28px;">'
			.         '<table width="100%" cellpadding="0" cellspacing="0" style="border-collapse:collapse;">' . $rows . '</table>'
			.       '</td></tr>'
			.       '<tr><td style="padding:18px 28px;border-top:1px solid #eaeaea;color:#888888;font-size:12px;line-height:1.5;">' . $footer . '</td></tr>'
			.     '</table>'
			.   '</div>'
			. '</body></html>';

		$headers = array(
			'Content-Type: text/html; charset=UTF-8',
			sprintf( 'From: %1$s <%2$s>', $site_name, $from_email ),
			sprintf( 'Reply-To: %s', $from_email ),
		);

		return (bool) wp_mail( $to, $subject, $body, $headers );
	}
}
