<?php
/**
 * Render a v2 form schema to HTML on the frontend.
 *
 * Produces semantic, lightly-classed markup that the frontend CSS skins. The
 * markup intentionally avoids inline styles and per-field decorations — visual
 * appearance is centralized in build/form-block/frontend/style.css.
 *
 * @package WPZOOM_Forms
 */

defined( 'ABSPATH' ) || exit;

class WPZOOM_Forms_Renderer {

	/**
	 * Render a form by id. Returns HTML string, never echoes.
	 *
	 * @param int    $form_id      The form post id.
	 * @param string $root_classes Extra CSS classes for the wrapper (e.g. block
	 *                             alignment classes like "alignwide"/"alignfull").
	 */
	public static function render( $form_id, $root_classes = '' ) {
		$form_id = (int) $form_id;
		if ( $form_id < 1 ) return self::error_html( __( 'Invalid form id.', 'wpzoom-forms' ), false );

		$post = get_post( $form_id );
		if ( ! $post || $post->post_type !== 'wpzf-form' ) {
			return self::error_html( __( 'Form not found.', 'wpzoom-forms' ), current_user_can( 'manage_options' ) );
		}
		if ( $post->post_status !== 'publish' ) {
			return self::error_html( __( 'This form is not published.', 'wpzoom-forms' ), current_user_can( 'manage_options' ) );
		}

		$schema = WPZOOM_Forms_Schema::get_for_form( $form_id );
		if ( $schema === null ) {
			$schema = WPZOOM_Forms_Migration::migrate( $form_id );
		}

		return self::render_schema( $form_id, $schema, $root_classes );
	}

	private static function error_html( $msg, $show_for_admins_only ) {
		if ( $show_for_admins_only && ! current_user_can( 'manage_options' ) ) return '';
		return sprintf(
			'<div class="wpzf-error" style="border:1px solid #d63638;background:#fff;padding:1em;color:#d63638;">%s</div>',
			esc_html( $msg )
		);
	}

	private static function render_schema( $form_id, $schema, $root_classes = '' ) {
		$settings  = $schema['settings'];
		$fields    = $schema['fields'];

		$form_uid  = 'wpzf-' . $form_id;
		$notice_id = $form_uid . '-notice';
		$success_msg = get_post_meta( $form_id, '_form_success_message', true ) ?: __( "Thanks! We've received your submission.", 'wpzoom-forms' );
		$failure_msg = get_post_meta( $form_id, '_form_failure_message', true ) ?: __( 'Sorry, something went wrong. Please try again.', 'wpzoom-forms' );

		$captcha_html = self::captcha_field_html();

		// Per-instance enqueue (no-op if the global enqueue already ran).
		// Respect the site owner's existing settings: only attach the plugin's
		// default stylesheet when "Load default styling for forms" is enabled.
		if ( class_exists( 'WPZOOM_Forms_Settings' ) && (bool) WPZOOM_Forms_Settings::get( 'wpzf_use_theme_styles' ) ) {
			wp_enqueue_style( 'wpzoom-forms-css-frontend-formblock' );
		}

		// Detect legacy redirect result (?success=1&wpzf_submitted_form=X) and
		// pre-fill the notice server-side so no JS is needed to display it.
		$notice_status = '';
		$notice_text   = '';
		$submitted_id  = isset( $_GET['wpzf_submitted_form'] ) ? absint( $_GET['wpzf_submitted_form'] ) : 0;
		if ( $submitted_id === $form_id && isset( $_GET['success'] ) ) {
			if ( '1' === $_GET['success'] ) {
				$notice_status = 'success';
				$notice_text   = $success_msg;
			} else {
				$notice_status = 'error';
				$notice_text   = $failure_msg;
			}
		}

		$theme_class  = ' wpzf-theme-'  . sanitize_html_class( ! empty( $settings['theme'] )      ? $settings['theme']      : 'default' );
		$layout_class = ' wpzf-layout-' . sanitize_html_class( ! empty( $settings['formLayout'] ) ? $settings['formLayout'] : 'default' );

		// Extra wrapper classes supplied by the caller (e.g. block alignment).
		$extra_class = '';
		if ( ! empty( $root_classes ) ) {
			$parts = array_filter( array_map( 'sanitize_html_class', preg_split( '/\s+/', trim( $root_classes ) ) ) );
			if ( $parts ) {
				$extra_class = ' ' . implode( ' ', $parts );
			}
		}

		// Collect per-field custom CSS.
		$field_css = '';
		foreach ( $fields as $field ) {
			if ( ! empty( $field['customCSS'] ) ) {
				$fid      = sanitize_html_class( $field['id'] );
				$selector = '#' . $form_uid . ' .wpzf-field_' . $fid;
				$css      = str_replace( 'selector', $selector, $field['customCSS'] );
				// Strip any </style> injection attempts.
				$css      = preg_replace( '#</?\s*style[^>]*>#i', '', $css );
				$field_css .= $css . "\n";
			}
		}

		ob_start();
		if ( ! empty( $field_css ) ) :
			// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			echo '<style>' . $field_css . '</style>';
		endif;
		?>
		<div id="<?php echo esc_attr( $form_uid ); ?>" class="wpzf-form wpzoom-forms_form<?php echo esc_attr( $theme_class ); ?><?php echo esc_attr( $layout_class ); ?><?php echo esc_attr( $extra_class ); ?> wpzf-labels-<?php echo esc_attr( $settings['labelsPosition'] ); ?>">
			<form method="POST" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="wpzf-form__inner wp-block-wpzoom-forms-form" data-form-id="<?php echo esc_attr( $form_id ); ?>" data-success="<?php echo esc_attr( $success_msg ); ?>" data-failure="<?php echo esc_attr( $failure_msg ); ?>">
				<input type="hidden" name="action" value="wpzf_submit" />
				<input type="hidden" name="form_id" value="<?php echo esc_attr( $form_id ); ?>" />
				<?php wp_nonce_field( 'wpzf_submit' ); ?>

				<?php if ( ! empty( $settings['honeypot'] ) ) : ?>
					<div class="wpzf-honeypot" aria-hidden="true">
						<label>Leave this field empty: <input type="text" name="wpzf_hp" tabindex="-1" autocomplete="off" value="" /></label>
					</div>
				<?php endif; ?>

				<div class="wpzf-fields">
					<?php foreach ( $fields as $field ) : ?>
						<?php echo self::render_field( $field, $settings ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					<?php endforeach; ?>
				</div>

				<?php echo $captcha_html; // phpcs:ignore ?>

				<div class="wpzf-submit wpzf-submit--align-<?php echo esc_attr( $settings['submitAlign'] ); ?>">
					<button type="submit" class="wpzf-submit__btn"><?php echo esc_html( $settings['submitLabel'] ); ?></button>
				</div>
			</form>
			<div id="<?php echo esc_attr( $notice_id ); ?>" class="wpzf-notice" style="margin-top:1em;"<?php if ( $notice_status ) : ?> data-status="<?php echo esc_attr( $notice_status ); ?>"<?php else : ?> hidden<?php endif; ?>><?php echo esc_html( $notice_text ); ?></div>
		</div>
		<?php
		return ob_get_clean();
	}

	private static function render_field( $field, $form_settings ) {
		$type      = $field['type'];
		$fid_class = ' wpzf-field_' . sanitize_html_class( $field['id'] );

		// Layout-only fields (no input).
		if ( $type === 'heading' ) {
			$tag = isset( $field['level'] ) ? $field['level'] : 'h3';
			return sprintf(
				'<div class="wpzf-field wpzf-field--width-%1$s wpzf-field--layout%4$s"><%2$s class="wpzf-heading">%3$s</%2$s></div>',
				esc_attr( $field['width'] ),
				esc_attr( $tag ),
				esc_html( $field['text'] ),
				$fid_class
			);
		}
		if ( $type === 'paragraph' ) {
			return sprintf(
				'<div class="wpzf-field wpzf-field--width-%1$s wpzf-field--layout%3$s"><div class="wpzf-paragraph">%2$s</div></div>',
				esc_attr( $field['width'] ),
				wp_kses_post( $field['text'] ),
				$fid_class
			);
		}
		if ( $type === 'divider' ) {
			return sprintf(
				'<div class="wpzf-field wpzf-field--width-%1$s wpzf-field--layout%2$s"><hr class="wpzf-divider" /></div>',
				esc_attr( $field['width'] ),
				$fid_class
			);
		}
		if ( $type === 'hidden' ) {
			return sprintf(
				'<input type="hidden" name="wpzf_%1$s" value="%2$s" />',
				esc_attr( $field['id'] ),
				esc_attr( $field['defaultValue'] )
			);
		}

		$field_name  = 'wpzf_' . $field['id'];
		static $seq = 0; $seq++;
		$field_id    = $field['id'] . '-' . $seq;
		$required    = ! empty( $field['required'] );
		$required_attr = $required ? ' required="required" aria-required="true"' : '';
		$placeholder = ! empty( $field['placeholder'] ) ? ' placeholder="' . esc_attr( $field['placeholder'] ) . '"' : '';
		$default     = isset( $field['defaultValue'] ) ? $field['defaultValue'] : '';

		$extra_class = ! empty( $field['cssClass'] ) ? ' ' . esc_attr( $field['cssClass'] ) : '';

		$show_label = $form_settings['labelsPosition'] !== 'hidden' && $type !== 'checkbox';
		$req_mark   = $required && ! empty( $form_settings['showRequiredMark'] ) ? ' <span class="wpzf-required">*</span>' : '';
		$label_html = $show_label
			? sprintf( '<label class="wpzf-label" for="%1$s">%2$s%3$s</label>', esc_attr( $field_id ), esc_html( $field['label'] ), $req_mark )
			: '';
		$help_html  = ! empty( $field['help'] )
			? sprintf( '<div class="wpzf-help">%s</div>', esc_html( $field['help'] ) )
			: '';

		$input_html = '';
		switch ( $type ) {
			case 'text':
				$extra = ! empty( $field['isSubject'] ) ? ' data-subject="1"' : '';
				$input_html = sprintf(
					'<input type="text" id="%1$s" name="%2$s" class="wpzf-input"%3$s%4$s%5$s value="%6$s" />',
					esc_attr( $field_id ), esc_attr( $field_name ), $required_attr, $placeholder, $extra, esc_attr( $default )
				);
				if ( ! empty( $field['isSubject'] ) ) {
					$input_html .= '<input type="hidden" name="wpzf_subject" value="' . esc_attr( $field_name ) . '" />';
				}
				break;
			case 'name':
				$input_html = sprintf(
					'<input type="text" id="%1$s" name="%2$s" class="wpzf-input" autocomplete="name"%3$s%4$s value="%5$s" />',
					esc_attr( $field_id ), esc_attr( $field_name ), $required_attr, $placeholder, esc_attr( $default )
				);
				break;
			case 'email':
				$input_html = sprintf(
					'<input type="email" id="%1$s" name="%2$s" class="wpzf-input" autocomplete="email"%3$s%4$s value="%5$s" />',
					esc_attr( $field_id ), esc_attr( $field_name ), $required_attr, $placeholder, esc_attr( $default )
				);
				if ( ! empty( $field['isReplyTo'] ) ) {
					$input_html .= '<input type="hidden" name="wpzf_replyto" value="' . esc_attr( $field_name ) . '" />';
				}
				break;
			case 'tel':
				$input_html = sprintf(
					'<input type="tel" id="%1$s" name="%2$s" class="wpzf-input" autocomplete="tel"%3$s%4$s value="%5$s" />',
					esc_attr( $field_id ), esc_attr( $field_name ), $required_attr, $placeholder, esc_attr( $default )
				);
				break;
			case 'url':
				$input_html = sprintf(
					'<input type="url" id="%1$s" name="%2$s" class="wpzf-input" inputmode="url" autocomplete="url"%3$s%4$s value="%5$s" />',
					esc_attr( $field_id ), esc_attr( $field_name ), $required_attr, $placeholder, esc_attr( $default )
				);
				break;
			case 'number':
				$min  = isset( $field['min'] )  && $field['min']  !== null ? ' min="'  . esc_attr( $field['min'] )  . '"' : '';
				$max  = isset( $field['max'] )  && $field['max']  !== null ? ' max="'  . esc_attr( $field['max'] )  . '"' : '';
				$step = isset( $field['step'] ) && $field['step'] !== null ? ' step="' . esc_attr( $field['step'] ) . '"' : '';
				$input_html = sprintf(
					'<input type="number" id="%1$s" name="%2$s" class="wpzf-input"%3$s%4$s%5$s%6$s%7$s value="%8$s" />',
					esc_attr( $field_id ), esc_attr( $field_name ), $required_attr, $placeholder, $min, $max, $step, esc_attr( $default )
				);
				break;
			case 'textarea':
				$rows = isset( $field['rows'] ) ? (int) $field['rows'] : 4;
				$input_html = sprintf(
					'<textarea id="%1$s" name="%2$s" class="wpzf-input wpzf-input--textarea" rows="%3$d"%4$s%5$s>%6$s</textarea>',
					esc_attr( $field_id ), esc_attr( $field_name ), $rows, $required_attr, $placeholder, esc_textarea( $default )
				);
				break;
			case 'date':
				// Use flatpickr (same as legacy forms) instead of the native date
				// input, so the picker looks consistent across the site and honours
				// the configured format/mode. datepicker.js initialises any
				// input[data-datepicker="true"] and reads data-date-format/data-mode.
				$fmt  = ! empty( $field['format'] ) ? $field['format'] : 'Y-m-d';
				$mode = ! empty( $field['mode'] )   ? $field['mode']   : 'single';
				$date_ph = '' !== $placeholder ? $placeholder : ' placeholder="' . esc_attr( $fmt ) . '"';
				$input_html = sprintf(
					'<input type="text" id="%1$s" name="%2$s" class="wpzf-input" data-datepicker="true" data-date-format="%3$s" data-mode="%4$s" autocomplete="off"%5$s%6$s value="%7$s" />',
					esc_attr( $field_id ), esc_attr( $field_name ), esc_attr( $fmt ), esc_attr( $mode ), $required_attr, $date_ph, esc_attr( $default )
				);
				// Ensure the datepicker assets load (registered in_footer, so a late
				// enqueue during render still prints correctly).
				wp_enqueue_style( 'wpzoom-forms-css-frontend-flatpickr' );
				wp_enqueue_script( 'wpzoom-forms-js-frontend-flatpickr' );
				wp_enqueue_script( 'wpzoom-forms-js-frontend-datepicker' );
				break;
			case 'select':
				$opts = isset( $field['options'] ) ? $field['options'] : array();
				$ohtml = '<option value="">' . esc_html( $field['placeholder'] ?: __( '— Select —', 'wpzoom-forms' ) ) . '</option>';
				foreach ( $opts as $opt ) {
					$sel = ( $default !== '' && $default === $opt['value'] ) ? ' selected="selected"' : '';
					$ohtml .= sprintf( '<option value="%1$s"%2$s>%3$s</option>', esc_attr( $opt['value'] ), $sel, esc_html( $opt['label'] ) );
				}
				$input_html = sprintf(
					'<select id="%1$s" name="%2$s" class="wpzf-input wpzf-input--select"%3$s>%4$s</select>',
					esc_attr( $field_id ), esc_attr( $field_name ), $required_attr, $ohtml
				);
				break;
			case 'radio':
				$opts = isset( $field['options'] ) ? $field['options'] : array();
				$inner = '';
				foreach ( $opts as $i => $opt ) {
					$oid = $field_id . '-' . $i;
					$chk = ( $default !== '' && $default === $opt['value'] ) ? ' checked="checked"' : '';
					$inner .= sprintf(
						'<label class="wpzf-choice" for="%1$s"><input type="radio" id="%1$s" name="%2$s" value="%3$s"%4$s%5$s /> <span>%6$s</span></label>',
						esc_attr( $oid ), esc_attr( $field_name ), esc_attr( $opt['value'] ), $required_attr, $chk, esc_html( $opt['label'] )
					);
				}
				$input_html = '<div class="wpzf-choices wpzf-choices--radio">' . $inner . '</div>';
				break;
			case 'checkboxes':
				$opts     = isset( $field['options'] ) ? $field['options'] : array();
				$defaults = is_array( $default ) ? $default : array();
				$inner    = '';
				foreach ( $opts as $i => $opt ) {
					$oid = $field_id . '-' . $i;
					$chk = in_array( $opt['value'], $defaults, true ) ? ' checked="checked"' : '';
					$inner .= sprintf(
						'<label class="wpzf-choice" for="%1$s"><input type="checkbox" id="%1$s" name="%2$s[]" value="%3$s"%4$s /> <span>%5$s</span></label>',
						esc_attr( $oid ), esc_attr( $field_name ), esc_attr( $opt['value'] ), $chk, esc_html( $opt['label'] )
					);
				}
				$input_html = '<div class="wpzf-choices wpzf-choices--checkbox">' . $inner . '</div>';
				break;
			case 'checkbox':
				$text = isset( $field['checkboxText'] ) ? $field['checkboxText'] : $field['label'];
				$input_html = sprintf(
					'<label class="wpzf-choice wpzf-choice--single" for="%1$s"><input type="checkbox" id="%1$s" name="%2$s" value="1"%3$s /> <span>%4$s%5$s</span></label>',
					esc_attr( $field_id ), esc_attr( $field_name ), $required_attr, esc_html( $text ), $req_mark
				);
				// hide the outer label since the inner one renders text.
				$label_html = '';
				break;
		}

		return sprintf(
			'<div class="wpzf-field wpzf-field--width-%1$s wpzf-field--type-%2$s%3$s%4$s%5$s">%6$s%7$s%8$s</div>',
			esc_attr( $field['width'] ),
			esc_attr( $type ),
			$extra_class,
			' ' . esc_attr( self::legacy_field_class( $type ) ),
			$fid_class,
			$label_html,
			$input_html,
			$help_html
		);
	}

	/**
	 * Returns the matching legacy block class for a given v2 field type,
	 * so any custom theme CSS targeting the old `.wp-block-wpzoom-forms-*`
	 * selectors continues to apply against the new markup.
	 */
	private static function legacy_field_class( $type ) {
		static $map = array(
			'text'       => 'wp-block-wpzoom-forms-text-plain-field',
			'name'       => 'wp-block-wpzoom-forms-text-name-field',
			'email'      => 'wp-block-wpzoom-forms-text-email-field',
			'tel'        => 'wp-block-wpzoom-forms-text-phone-field',
			'url'        => 'wp-block-wpzoom-forms-text-website-field',
			'textarea'   => 'wp-block-wpzoom-forms-textarea-field',
			'select'     => 'wp-block-wpzoom-forms-select-field',
			'radio'      => 'wp-block-wpzoom-forms-radio-field',
			'checkboxes' => 'wp-block-wpzoom-forms-multi-checkbox-field',
			'checkbox'   => 'wp-block-wpzoom-forms-checkbox-field',
			'date'       => 'wp-block-wpzoom-forms-datepicker-field',
		);
		return isset( $map[ $type ] ) ? $map[ $type ] : '';
	}

	/**
	 * Returns HTML for the active captcha field, if any (reads existing settings).
	 * Also enqueues the matching provider SDK on demand.
	 */
	private static function captcha_field_html() {
		$config = self::captcha_config();

		if ( $config['active'] === 'recaptcha' && ! empty( $config['site_key'] ) ) {
			// reCAPTCHA v3 — invisible: load the api.js with ?render=<key> and
			// intercept form submit to call grecaptcha.execute() before posting.
			if ( $config['type'] === 'v3' ) {
				wp_enqueue_script(
					'wpzf-google-recaptcha',
					'https://www.google.com/recaptcha/api.js?render=' . rawurlencode( $config['site_key'] ),
					array(),
					null,
					true
				);
				// Add the submit-intercept handler once per page load (static guard prevents
				// duplicates when multiple v3 forms are rendered on the same page).
				static $v3_inline_added = false;
				if ( ! $v3_inline_added ) {
					wp_add_inline_script(
						'wpzf-google-recaptcha',
						'(function(){document.addEventListener("submit",function(e){var f=e.target;if(!f.classList.contains("wpzf-form__inner"))return;var t=f.querySelector(".wpzf-recaptcha-token");if(!t||t.value)return;e.preventDefault();grecaptcha.ready(function(){grecaptcha.execute(t.dataset.sitekey,{action:t.dataset.action||"wpzf_submit"}).then(function(token){t.value=token;f.submit();});});},true);})();'
					);
					$v3_inline_added = true;
				}
				return sprintf(
					'<input type="hidden" name="recaptcha_token" class="wpzf-recaptcha-token" data-sitekey="%1$s" data-action="wpzf_submit" />',
					esc_attr( $config['site_key'] )
				);
			}
			// reCAPTCHA v2 — visible checkbox widget; api.js auto-renders any .g-recaptcha element.
			wp_enqueue_script(
				'wpzf-google-recaptcha',
				'https://www.google.com/recaptcha/api.js',
				array(),
				null,
				true
			);
			return sprintf(
				'<div class="wpzf-captcha"><div class="g-recaptcha" data-sitekey="%1$s"></div></div>',
				esc_attr( $config['site_key'] )
			);
		}

		if ( $config['active'] === 'turnstile' && ! empty( $config['site_key'] ) ) {
			wp_enqueue_script(
				'wpzf-turnstile',
				'https://challenges.cloudflare.com/turnstile/v0/api.js',
				array(),
				null,
				array( 'strategy' => 'defer' )
			);
			return sprintf(
				'<div class="wpzf-captcha"><div class="cf-turnstile" data-sitekey="%1$s"></div></div>',
				esc_attr( $config['site_key'] )
			);
		}

		return '';
	}

	/**
	 * Mirror of the captcha-config logic in the main plugin class, but read-only.
	 */
	private static function captcha_config() {
		// Delegate to the main plugin if it exposes the existing helper.
		global $wpzoom_forms;
		if ( $wpzoom_forms && method_exists( $wpzoom_forms, 'get_spam_protection_config' ) ) {
			$cfg = $wpzoom_forms->get_spam_protection_config();
			$service = isset( $cfg['active_service'] ) ? $cfg['active_service'] : 'none';
			if ( $service === 'recaptcha' ) {
				$type = isset( $cfg['type'] ) ? $cfg['type'] : 'v2';
				$site = $type === 'v3' ? ( $cfg['recaptcha_v3_site_key'] ?? '' ) : ( $cfg['recaptcha_v2_site_key'] ?? '' );
				return array( 'active' => 'recaptcha', 'type' => $type, 'site_key' => $site );
			}
			if ( $service === 'turnstile' ) {
				return array(
					'active'   => 'turnstile',
					'type'     => '',
					'site_key' => $cfg['turnstile_site_key'] ?? '',
				);
			}
		}
		return array( 'active' => 'none', 'type' => '', 'site_key' => '' );
	}
}
