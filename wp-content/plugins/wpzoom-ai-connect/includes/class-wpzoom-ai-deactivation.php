<?php
/**
 * "Before you go": when someone deactivates WPZOOM AI Connect on the Plugins screen, a short survey asks why
 * (the same idea and look as the Inspiro theme's theme-switch survey).
 *
 * Nothing is sent unless they press "Submit & Deactivate". The answers then go from this site to
 * ai.wpzoom.com (WPZOOM's feedback collector, see the readme's External services) together with a few technical
 * details: plugin, WordPress and PHP versions, language, theme, how far setup got and for how long the plugin was
 * installed. Their email and the site address are added only when they tick the follow-up box.
 * "Skip & Deactivate" deactivates without sending anything.
 *
 * The same collector takes the "Share feedback" form on the AI Connect screen: the message, the plugin and
 * WordPress versions, and the person's email only if they tick "You may reply to me".
 *
 * @package WPZOOM_AI_Connect
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WPZOOM_AI_Deactivation {

	const ENDPOINT = 'https://ai.wpzoom.com/simple-plugin-feedback-endpoint.php';
	const ACTION   = 'wpzoom_ai_deactivation_feedback';
	/** The "Share feedback" form on the AI Connect screen. */
	const FEEDBACK_ACTION = 'wpzoom_ai_feedback';
	/** When this user last sent the survey; they aren't asked again for 30 days. */
	const USER_META = 'wpzoom_ai_connect_feedback_sent';

	public static function init(): void {
		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue' ) );
		// admin_footer, not admin_footer-plugins.php: that one runs after the footer scripts, so the script would not find the survey.
		add_action( 'admin_footer', array( __CLASS__, 'render' ) );
		add_action( 'wp_ajax_' . self::ACTION, array( __CLASS__, 'handle' ) );
		add_action( 'wp_ajax_' . self::FEEDBACK_ACTION, array( __CLASS__, 'handle_feedback' ) );
	}

	/** Reasons: slug => [ label, placeholder of its "tell us more" box, or '' for none ]. */
	private static function reasons(): array {
		return array(
			'couldnt-connect'   => array( __( 'I couldn\'t get it connected', 'wpzoom-ai-connect' ), __( 'Where did it get stuck?', 'wpzoom-ai-connect' ) ),
			'didnt-work'        => array( __( 'It didn\'t do what I asked', 'wpzoom-ai-connect' ), __( 'What did you ask for, and what happened?', 'wpzoom-ai-connect' ) ),
			'missing-features'  => array( __( 'It\'s missing something I need', 'wpzoom-ai-connect' ), __( 'What would you like it to do?', 'wpzoom-ai-connect' ) ),
			'not-comfortable'   => array( __( 'I\'m not comfortable giving an AI access to my site', 'wpzoom-ai-connect' ), '' ),
			'found-alternative' => array( __( 'I found another plugin or tool that fits better', 'wpzoom-ai-connect' ), __( 'Which one?', 'wpzoom-ai-connect' ) ),
			'temporary'         => array( __( 'I\'m only deactivating it for a moment', 'wpzoom-ai-connect' ), '' ),
			'other'             => array( __( 'Other reason', 'wpzoom-ai-connect' ), __( 'Please specify…', 'wpzoom-ai-connect' ) ),
		);
	}

	/** The payload field of each reason's text box. */
	private static function text_field( string $slug ): string {
		$fields = array(
			'couldnt-connect'   => 'text_couldnt_connect',
			'didnt-work'        => 'text_didnt_work',
			'missing-features'  => 'text_missing',
			'found-alternative' => 'text_alternative',
			'other'             => 'text_other',
		);
		return $fields[ $slug ] ?? '';
	}

	private static function wanted(): bool {
		if ( is_network_admin() || ! current_user_can( 'activate_plugins' ) ) {
			return false;
		}
		$sent = (int) get_user_meta( get_current_user_id(), self::USER_META, true );
		return ! $sent || time() - $sent > 30 * DAY_IN_SECONDS;
	}

	public static function enqueue( string $hook ): void {
		if ( 'plugins.php' !== $hook || ! self::wanted() ) {
			return;
		}
		wp_enqueue_style( 'wpzoom-ai-deactivation', plugins_url( 'assets/admin/deactivation.css', WPZOOM_AI_CONNECT_FILE ), array(), WPZOOM_AI_CONNECT_VERSION );
		wp_enqueue_script( 'wpzoom-ai-deactivation', plugins_url( 'assets/admin/deactivation.js', WPZOOM_AI_CONNECT_FILE ), array(), WPZOOM_AI_CONNECT_VERSION, true );
		wp_localize_script( 'wpzoom-ai-deactivation', 'wzaiDeactivate', array(
			'plugin'  => plugin_basename( WPZOOM_AI_CONNECT_FILE ),
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( self::ACTION ),
			'sending' => __( 'Sending…', 'wpzoom-ai-connect' ),
		) );
	}

	/** The survey, hidden until the plugin's Deactivate link is clicked. */
	public static function render(): void {
		$screen = function_exists( 'get_current_screen' ) ? get_current_screen() : null;
		if ( ! $screen || 'plugins' !== $screen->id || ! self::wanted() ) {
			return;
		}
		$user = wp_get_current_user();
		?>
		<div class="wzai-deact" id="wzai-deact" hidden>
			<div class="wzai-deact__modal" role="dialog" aria-modal="true" aria-labelledby="wzai-deact-title">
				<div class="wzai-deact__header">
					<h2 id="wzai-deact-title"><?php esc_html_e( 'Help us improve AI Connect', 'wpzoom-ai-connect' ); ?></h2>
					<button type="button" class="wzai-deact__close" data-wzai-close aria-label="<?php esc_attr_e( 'Close, keep the plugin active', 'wpzoom-ai-connect' ); ?>">&times;</button>
				</div>
				<div class="wzai-deact__body">
					<p class="wzai-deact__intro"><?php esc_html_e( 'Please tell us why you\'re deactivating WPZOOM AI Connect. Your feedback helps us make it better for everyone, and we read every answer.', 'wpzoom-ai-connect' ); ?></p>
					<form id="wzai-deact-form">
						<?php foreach ( self::reasons() as $slug => $reason ) : ?>
							<div class="wzai-deact__choice">
								<label class="wzai-deact__label"><input type="checkbox" name="reasons[]" value="<?php echo esc_attr( $slug ); ?>"> <?php echo esc_html( $reason[0] ); ?></label>
								<?php if ( '' !== $reason[1] ) : ?>
									<textarea name="<?php echo esc_attr( self::text_field( $slug ) ); ?>" class="wzai-deact__text" rows="2" maxlength="1000" placeholder="<?php echo esc_attr( $reason[1] ); ?>" hidden></textarea>
								<?php endif; ?>
								<?php if ( 'couldnt-connect' === $slug ) : ?>
									<p class="wzai-deact__hint" hidden>
										<?php
										printf(
											/* translators: 1: link to the setup video, 2: link to the support forum */
											esc_html__( 'The %1$s shows every step, and on the %2$s we can usually get you going.', 'wpzoom-ai-connect' ),
											'<a href="' . esc_url( WPZOOM_AI_Admin::VIDEO_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'setup video', 'wpzoom-ai-connect' ) . '</a>',
											'<a href="' . esc_url( WPZOOM_AI_Notices::HELP_URL ) . '" target="_blank" rel="noopener">' . esc_html__( 'support forum', 'wpzoom-ai-connect' ) . '</a>'
										);
										?>
									</p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>

						<div class="wzai-deact__nps">
							<h3><?php esc_html_e( 'One more quick question:', 'wpzoom-ai-connect' ); ?></h3>
							<p><?php esc_html_e( 'How likely are you to recommend AI Connect to a friend or colleague?', 'wpzoom-ai-connect' ); ?></p>
							<div class="wzai-deact__scale">
								<span><?php esc_html_e( 'Not at all likely', 'wpzoom-ai-connect' ); ?></span>
								<div class="wzai-deact__scores" role="radiogroup" aria-label="<?php esc_attr_e( 'From 0, not at all likely, to 5, extremely likely', 'wpzoom-ai-connect' ); ?>">
									<?php for ( $i = 0; $i <= 5; $i++ ) : ?>
										<button type="button" class="wzai-deact__score" data-score="<?php echo (int) $i; ?>" role="radio" aria-checked="false"><?php echo (int) $i; ?></button>
									<?php endfor; ?>
								</div>
								<span><?php esc_html_e( 'Extremely likely', 'wpzoom-ai-connect' ); ?></span>
							</div>
						</div>
					</form>
				</div>
				<div class="wzai-deact__footer">
					<div class="wzai-deact__consent">
						<label>
							<input type="checkbox" id="wzai-deact-consent">
							<span>
								<?php
								printf(
									/* translators: %s: the current user's email address */
									esc_html__( 'It\'s okay to follow up with me at %s about this. This also sends your site address.', 'wpzoom-ai-connect' ),
									'<strong>' . esc_html( $user->user_email ) . '</strong>'
								);
								?>
							</span>
						</label>
					</div>
					<p class="wzai-deact__privacy">
						<?php
						printf(
							/* translators: %s: link to the privacy policy */
							esc_html__( 'Your answers go to WPZOOM with a few technical details: the plugin, WordPress and PHP versions, your language, your theme, how far setup got and how long the plugin was installed. Please don\'t include personal information. See our %s.', 'wpzoom-ai-connect' ),
							'<a href="https://www.wpzoom.com/privacy-policy/" target="_blank" rel="noopener">' . esc_html__( 'privacy policy', 'wpzoom-ai-connect' ) . '</a>'
						);
						?>
					</p>
					<div class="wzai-deact__buttons">
						<a class="wzai-deact__btn" href="#" data-wzai-skip><?php esc_html_e( 'Skip & Deactivate', 'wpzoom-ai-connect' ); ?></a>
						<button type="button" class="wzai-deact__btn wzai-deact__btn--primary" data-wzai-submit disabled><?php esc_html_e( 'Submit & Deactivate', 'wpzoom-ai-connect' ); ?></button>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/** How far setup got, in the collector's words. */
	private static function setup_stage(): string {
		$connections = WPZOOM_AI_Admin::connections();
		if ( WPZOOM_AI_Scope::last_ai_call( $connections ) ) {
			return 'in_use';
		}
		if ( $connections ) {
			return 'connected_unused';
		}
		$home = home_url( '/' );
		if ( WPZOOM_AI_Admin::is_local( $home ) ) {
			return 'local';
		}
		if ( 0 !== strpos( $home, 'https://' ) ) {
			return 'no_https';
		}
		if ( ! function_exists( 'wp_is_application_passwords_available' ) || ! wp_is_application_passwords_available() ) {
			return 'no_app_passwords';
		}
		return 'not_connected';
	}

	/** What "Submit & Deactivate" sends. Kept separate so it can be checked without sending. */
	public static function payload( array $input ): array {
		$reasons = array_keys( self::reasons() );
		$chosen  = array_values( array_intersect( $reasons, array_map( 'sanitize_key', (array) ( $input['reasons'] ?? array() ) ) ) );
		$theme   = wp_get_theme();
		$data    = array(
			'plugin'         => 'wpzoom-ai-connect',
			'type'           => 'deactivation',
			'plugin_version' => WPZOOM_AI_CONNECT_VERSION,
			'choices'        => implode( ',', $chosen ),
			'setup_stage'    => self::setup_stage(),
			'days_installed' => (string) max( 0, (int) floor( ( time() - (int) get_option( WPZOOM_AI_Notices::INSTALLED_OPTION, time() ) ) / DAY_IN_SECONDS ) ),
			'ai_days'        => (string) WPZOOM_AI_Scope::ai_days()['count'],
			'install_source' => wpzoom_ai_connect_clean_source( (string) get_option( WPZOOM_AI_CONNECT_SOURCE_OPTION, '' ) ),
			'theme'          => $theme->get( 'Name' ) . ' ' . $theme->get( 'Version' ),
			'wp_version'     => get_bloginfo( 'version' ),
			'php_version'    => PHP_VERSION,
			'language'       => get_user_locale(),
			'is_multisite'   => is_multisite() ? '1' : '0',
			'user_consent'   => empty( $input['consent'] ) ? '0' : '1',
		);
		foreach ( $chosen as $slug ) {
			$field = self::text_field( $slug );
			if ( '' !== $field && ! empty( $input[ $field ] ) ) {
				$data[ $field ] = mb_substr( sanitize_textarea_field( (string) $input[ $field ] ), 0, 1000 );
			}
		}
		if ( isset( $input['nps_score'] ) && '' !== $input['nps_score'] && is_numeric( $input['nps_score'] ) ) {
			$data['nps_score'] = (string) max( 0, min( 5, (int) $input['nps_score'] ) );
		}
		if ( '1' === $data['user_consent'] ) {
			$data['admin_email'] = wp_get_current_user()->user_email;
			$data['domain']      = home_url();
		}
		return $data;
	}

	/** Sends the answers to WPZOOM, then lets the screen go on with deactivating (whatever the collector says). */
	public static function handle(): void {
		check_ajax_referer( self::ACTION, 'nonce' );
		if ( ! current_user_can( 'activate_plugins' ) ) {
			wp_send_json_error( null, 403 );
		}
		$data = self::payload( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- payload() keeps known fields only and sanitizes each.
		if ( '' === $data['choices'] ) {
			wp_send_json_error( array( 'message' => 'No reason chosen.' ), 400 );
		}
		$sent = self::send( $data );
		update_user_meta( get_current_user_id(), self::USER_META, time() );
		wp_send_json_success( array( 'sent' => $sent ) );
	}

	/** What the "Share feedback" form sends: anonymous unless they agree to a reply. */
	public static function feedback_payload( array $input ): array {
		$data = array(
			'plugin'         => 'wpzoom-ai-connect',
			'type'           => 'feedback',
			'plugin_version' => WPZOOM_AI_CONNECT_VERSION,
			'wp_version'     => get_bloginfo( 'version' ),
			'message'        => mb_substr( trim( sanitize_textarea_field( (string) ( $input['message'] ?? '' ) ) ), 0, 2000 ),
			'user_consent'   => empty( $input['consent'] ) ? '0' : '1',
		);
		if ( '1' === $data['user_consent'] ) {
			$data['admin_email'] = wp_get_current_user()->user_email;
		}
		return $data;
	}

	/** Sends a "Share feedback" message. One a minute per user. */
	public static function handle_feedback(): void {
		check_ajax_referer( self::FEEDBACK_ACTION, 'nonce' );
		if ( ! WPZOOM_AI_Access::can_see() ) {
			wp_send_json_error( null, 403 );
		}
		$data = self::feedback_payload( wp_unslash( $_POST ) ); // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- feedback_payload() sanitizes the two fields it reads.
		if ( '' === $data['message'] ) {
			wp_send_json_error( array( 'message' => 'Empty message.' ), 400 );
		}
		$key = 'wpzoom_ai_feedback_' . get_current_user_id();
		if ( get_transient( $key ) ) {
			wp_send_json_error( array( 'message' => 'Too soon.' ), 429 );
		}
		$sent = self::send( $data );
		if ( $sent ) {
			set_transient( $key, 1, MINUTE_IN_SECONDS );
		}
		$sent ? wp_send_json_success() : wp_send_json_error( null, 502 );
	}

	/**
	 * Posts to the collector. The user agent names only the plugin: WordPress's default one carries the site's
	 * address, which the person did not agree to send.
	 */
	private static function send( array $data ): bool {
		/**
		 * Filters where the deactivation survey and "Share feedback" messages are sent.
		 *
		 * @since 0.13.0
		 *
		 * @param string $url WPZOOM's feedback collector.
		 */
		$url      = (string) apply_filters( 'wpzoom_ai_connect_feedback_endpoint', self::ENDPOINT );
		$response = wp_remote_post( $url, array(
			'timeout'    => 5,
			'user-agent' => 'WPZOOM-AI-Connect/' . WPZOOM_AI_CONNECT_VERSION,
			'body'       => $data,
		) );
		return ! is_wp_error( $response ) && 200 === wp_remote_retrieve_response_code( $response );
	}
}
