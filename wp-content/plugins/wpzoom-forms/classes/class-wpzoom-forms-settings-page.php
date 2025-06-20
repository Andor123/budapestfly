<?php
/**
 * Class WPZOOM Forms Settings Page
 *
 * @since   1.0.5
 * @package WPZOOM_Forms
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Class for settings page.
 */
class WPZOOM_Forms_Settings {
	/**
	 * Option name
	 */
	public static $option = 'wpzf-settings';

	/**
	 * Store all default settings options.
	 *
	 * @static
	 */
	public static $defaults = array();

	/**
	 * Store all settings options.
	 *
	 * @static
	 */
	public static $settings = array();

	/**
	 * Active Tab.
	 */
	public static $active_tab;

	/**
	 * Class WPZOOM_Forms_Settings_Fields instance.
	 */
	public $_fields;

	/**
	 * Store Settings options.
	 */
	public static $options = array();

	/**
	 * License key
	 */
	public static $license_key = false;

	/**
	 * License status
	 */
	public static $license_status = false;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		global $pagenow;

		self::$options = get_option( self::$option );

		// Check what page we are on.
		$page = isset( $_GET['page'] ) ? sanitize_text_field( $_GET['page'] ) : '';

		if ( is_admin() ) {
			add_action( 'admin_init', array( $this, 'settings_init' ) );
			add_action( 'admin_init', array( $this, 'set_defaults' ) );

			// Include admin scripts & styles
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );

			// Do ajax request
			add_action( 'wp_ajax_wpzoom_reset_settings', array( $this, 'reset_settings' ) );

			// Only load if we are actually on the settings page.
			if ( WPZOOM_FORMS_SETTINGS_PAGE === $page ) {
				add_action( 'wpzoom_forms_admin_page', array( $this, 'settings_page' ) );
			}

			$this->_fields = new WPZOOM_Forms_Settings_Fields();

			add_action( 'wpzoom_forms_settings_after_main_container', array( $this, 'upsell_banner' ) );
		}
	}

	/**
	 * Set default values for setting options.
	 */
	public function set_defaults() {
		// Set active tab
		self::$active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'tab-general';

		self::$defaults = self::get_defaults();

		if ( empty( self::$defaults ) ) {
			return false;
		}

		// If 'wpzoom-forms-settings' is empty update option with defaults values
		if ( empty( self::$options ) ) {
			self::update_option( self::$defaults );
		}

		// If new setting is added, update 'wpzoom-forms-settings' option
		if ( ! empty( self::$options ) ) {
			$new_settings = array_diff_key( self::$defaults, self::$options );
			if ( ! empty( $new_settings ) ) {
				self::update_option( array_merge( self::$options, $new_settings ) );
			}
		}

		return apply_filters( 'wpzoom_forms_set_settings_defaults', self::$defaults );
	}

	/**
	 * Update option value
	 *
	 * @param string|array $value
	 * @param string       $option
	 */
	public static function update_option( $value, $option = '', $autoload = null ) {
		if ( empty( $option ) ) {
			$option = self::$option;
		}

		if ( self::$options !== false ) {
			// The option already exists, so we just update it.
			update_option( $option, $value, $autoload );
		} else {
			// The option hasn't been added yet. We'll add it with $autoload set to 'no'.
			$deprecated = null;
			$autoload   = 'no';
			add_option( $option, $value, $deprecated, $autoload );
		}
	}

	/**
	 * Get default values of setting options.
	 *
	 * @static
	 */
	public static function get_defaults() {
		$defaults = array();

		foreach ( self::$settings as $key => $setting ) {
			if ( isset( $setting['sections'] ) && is_array( $setting['sections'] ) ) {
				foreach ( $setting['sections'] as $section ) {
					if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
						foreach ( $section['fields'] as $field ) {
							if ( isset( $field['args']['default'] ) ) {
								$defaults[ $field['id'] ] = (string) $field['args']['default'];
							}
						}
					}
				}
			}
		}

		return $defaults;
	}

	/**
	 * Get default value by option name
	 *
	 * @param string $option_name
	 * @static
	 * @return boolean
	 */
	public static function get_default_option_value( $option_name ) {
		return isset( self::$defaults[ $option_name ] ) ? self::$defaults[ $option_name ] : false;
	}

	/**
	 * Get license key
	 *
	 * @since 1.2.0
	 * @return string The License key
	 */
	public static function get_license_key() {
		return self::$license_key;
	}

	/**
	 * Get license status
	 *
	 * @since 1.2.0
	 * @return string The License status
	 */
	public static function get_license_status() {
		return self::$license_status;
	}

	/**
	 * Get setting options
	 *
	 * @since 1.2.0
	 * @return array
	 */
	public static function get_settings() {
		return apply_filters( 'wpzoom_forms_get_settings', self::$options );
	}

	/**
	 * Get setting option value
	 *
	 * @since 1.2.0
	 * @param string $option  Option name
	 * @return string|boolean
	 */
	public static function get( $option ) {
		return isset( self::$options[ $option ] ) ? self::$options[ $option ] : false;
	}

	/**
	 * Initilize all settings
	 */
	public function settings_init() {
		$premium_badge = '<span class="wpzoom-forms-badge wpzoom-forms-field-is_premium">' . __( 'Premium', 'wpzoom-forms' ) . '</span>';
		$soon_badge    = '<span class="wpzoom-forms-badge wpzoom-forms-field-is_coming_soon">' . __( 'Coming Soon', 'wpzoom-forms' ) . '</span>';

		self::$settings = array(
			'general'     => array(
				'tab_id'       => 'tab-general',
				'tab_title'    => __( 'General', 'wpzoom-forms' ),
				'option_group' => 'wpzoom-forms-settings-general',
				'option_name'  => self::$option,
				'sections'     => array(
					array(
						'id'       => 'wpzoom_section_general',
						'title'    => __( 'Styling', 'wpzoom-forms' ),
						'page'     => 'wpzoom-forms-settings-general',
						'callback' => array( $this, 'section_general_cb' ),
						'fields'   => array(
							array(
								'id'    => 'wpzf_use_theme_styles',
								'title' => esc_html__( 'Load default styling for forms', 'wpzoom-forms' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzf_use_theme_styles',
									'class'       => 'wpzoom-forms-field',
									'description' => esc_html__( 'Uncheck this option if you want your current theme to handle the styling for forms.', 'wpzoom-forms' ),
									'default'     => true,
								),
							),
							array(
								'id'    => 'wpzf_global_assets_load',
								'title' => esc_html__( 'Load plugin assets globally', 'wpzoom-forms' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for'   => 'wpzf_global_assets_load',
									'class'       => 'wpzoom-forms-field',
									'description' => esc_html__( 'If you want to embed a form using shortcodes in a page builder, enable this option to ensure all the needed assets are loaded.', 'wpzoom-forms' ),
									'default'     => true,
									
								),
							),
						),
					),
				),
			),
			'recaptcha' => array(
				'tab_id'       => 'tab-spam-protection',
				'tab_title'    => __( 'Spam Protection', 'wpzoom-forms' ),
				'option_group' => 'wpzf-spam-protection-settings',
				'option_name'  => self::$option,
				'sections'     => array(
					array(
						'id'       => 'wpzoom_section_recaptcha',
						'title'    => __( 'Spam Protection', 'wpzoom-forms' ),
						'page'     => 'wpzf-spam-protection-settings',
						'callback' => array( $this, 'section_recaptcha_cb' ),
						'after_section' => array( $this, 'section_recaptcha_after_cb' ),
						'fields'   => array(
							array(
								'id'    => 'wpzf_global_captcha_service',
								'title' => esc_html__( '', 'wpzoom-forms' ),
								'type'  => 'radio',
								'args'  => array(
									'label_for'   => 'wpzf_global_captcha_service',
									'class'       => 'wpzoom-forms-field wpzf-spam-protection-service',
									'description' => '',
									'default'     => 'none',
									'options'     => array(
										'none'      => esc_html__( 'No Protection', 'wpzoom-forms' ),
										'recaptcha' => esc_html__( 'Google reCAPTCHA', 'wpzoom-forms' ),
										'turnstile' => esc_html__( 'Cloudflare Turnstile', 'wpzoom-forms')
									)
								),
							),
							array(
								'id'    => 'wpzf_global_captcha_note',
								'title' => '',
								'type'  => 'note',
								'args'  => array(
									'class'       => 'wpzoom-forms-field required-recaptcha',
									'description' => '<a target="_blank" href="https://www.google.com/recaptcha/admin/create">Click here</a> to generate your reCAPTCHA keys and enter them below.',
								),
							),
							array(
								'id'    => 'wpzf_global_captcha_type',
								'title' => esc_html__( 'Type', 'wpzoom-forms' ),
								'type'  => 'radio',
								'args'  => array(
									'label_for'   => 'wpzf_global_captcha_type',
									'class'       => 'wpzoom-forms-field required-recaptcha',
									'description' => '',
									'default'     => 'v2',
									'wrap'        => true,
									'options'     => array(
										'v2'      => esc_html__( 'Invisible reCAPTCHA v2', 'wpzoom-forms' ),
										'v3'      => esc_html__( 'reCAPTCHA v3', 'wpzoom-forms' )
									)
								),
							),
							array(
								'id'    => 'wpzf_global_recaptcha_v2_site_key',
								'title' => __( 'reCaptcha v2 Site Key', 'wpzoom-forms' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzf_global_captcha_site_key',
									'class'       => 'wpzoom-forms-field required-recaptcha required-recaptcha-v2',
									'default'     => '',
									'description' => '',
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzf_global_recaptcha_v2_secret_key',
								'title' => __( 'reCaptcha v2 Secret Key', 'wpzoom-forms' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzf_global_captcha_secret_key',
									'class'       => 'wpzoom-forms-field required-recaptcha required-recaptcha-v2',
									'default'     => '',
									'description' => '',
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzf_global_recaptcha_v3_site_key',
								'title' => __( 'reCaptcha v3 Site Key', 'wpzoom-forms' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzf_global_captcha_site_key_v3',
									'class'       => 'wpzoom-forms-field required-recaptcha required-recaptcha-v3',
									'default'     => '',
									'description' => '',
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzf_global_recaptcha_v3_secret_key',
								'title' => __( 'reCaptcha v3 Secret Key', 'wpzoom-forms' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzf_global_captcha_secret_key_v3',
									'class'       => 'wpzoom-forms-field required-recaptcha required-recaptcha-v3',
									'default'     => '',
									'description' => '',
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzf_global_recaptcha_badge_location',
								'title' => esc_html__( 'Badge Location', 'wpzoom-forms' ),
								'type'  => 'select',
								'args'  => array(
									'label_for'   => 'wpzf_global_recaptcha_badge_location',
									'class'       => 'wpzoom-forms-field required-recaptcha',
									'description' => '',
									'default'     => 'bottomright',
									'options'     => array(
										'bottomright'  => esc_html__( 'Bottom Right', 'wpzoom-forms' ),
										'bottomleft' => esc_html__( 'Bottom Left', 'wpzoom-forms' ),
										'inline'  => esc_html__( 'Inline', 'wpzoom-forms')
									)
								),
							),
							array(
								'id'    => 'wpzf_global_turnstile_note',
								'title' => '',
								'type'  => 'note',
								'args'  => array(
									'class'       => 'wpzoom-forms-field required-turnstile',
									'description' => '<a target="_blank" href="https://dash.cloudflare.com/sign-up/turnstile">Click here</a> to generate your Turnstile keys and enter them below.',
								),
							),
							array(
								'id'	=> 'wpzf_global_turnstile_site_key',
								'title' => __( 'Site Key', 'wpzoom-forms' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzf_global_turnstile_site_key',
									'class'       => 'wpzoom-forms-field required-turnstile',
									'default'     => '',
									'description' => '',
									'type'        => 'text',
								),
							),
							array(
								'id'	=> 'wpzf_global_turnstile_secret_key',
								'title' => __( 'Secret Key', 'wpzoom-forms' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'   => 'wpzf_global_turnstile_secret_key',
									'class'       => 'wpzoom-forms-field required-turnstile',
									'default'     => '',
									'description' => '',
									'type'        => 'text',
								),
							),
							array(
								'id'    => 'wpzf_global_turnstile_widget_theme',
								'title' => esc_html__( 'Widget Theme', 'wpzoom-forms' ),
								'type'  => 'select',
								'args'  => array(
									'label_for'   => 'wpzf_global_turnstile_widget_theme',
									'class'       => 'wpzoom-forms-field required-turnstile',
									'description' => '',
									'default'     => 'auto',
									'options'     => array(
										'auto'  => esc_html__( 'Auto', 'wpzoom-forms' ),
										'light' => esc_html__( 'Light', 'wpzoom-forms' ),
										'dark'  => esc_html__( 'Dark', 'wpzoom-forms')
									)
								),
							),
						),
					),
				),
			),
			'ajax_settings' => array(
				'tab_id'       => 'tab-ajax',
				'tab_title'    => __( 'AJAX [PRO]', 'wpzoom-forms' ),
				'option_group' => 'wpzf-ajax-settings',
				'option_name'  => self::$option,
				'sections'     => array(
					array(
						'id'       => 'wpzf-ajax-settings-section',
						'title'    => __( 'AJAX Form Submission', 'wpzoom-forms' ),
						'callback' => array( $this, 'section_ajax_cb' ),
						'page'     => 'wpzf-ajax-settings',
						'fields'   => array(
							array(
								'id'    => 'wpzf_enable_ajax_submit',
								'title' => __( 'Allows users to submit forms without reloading the page.', 'wpzoom-forms' ),
								'type'  => 'checkbox',
								'args'  => array(
									'label_for' => 'wpzf_enable_ajax_submit',
									'desc'      => __( 'Submit forms without page reload using AJAX.', 'wpzoom-forms' ),
									'default'   => true,
								),
							),
							array(
								'id'    => 'wpzf_ajax_sending_message',
								'title' => __( 'Sending Message', 'wpzoom-forms' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'          => 'wpzf_ajax_sending_message',
									'placeholder' => __( 'Sending your message…', 'wpzoom-forms' ),
									'desc'        => __( 'Message displayed while the form is being submitted.', 'wpzoom-forms' ),
									'default'     => __( 'Sending...', 'wpzoom-forms' ),
								),
							),
							array(
								'id'    => 'wpzf_ajax_success_message',
								'title' => __( 'Success Message', 'wpzoom-forms' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'          => 'wpzf_ajax_success_message',
									'placeholder' => __( 'Thank you! Your message has been sent.', 'wpzoom-forms' ),
									'desc'        => __( 'Message displayed after successful form submission.', 'wpzoom-forms' ),
									'default'     => __( 'Thank you! Your message has been sent successfully.', 'wpzoom-forms' ),
								),
							),
							array(
								'id'    => 'wpzf_ajax_error_message',
								'title' => __( 'Error Message', 'wpzoom-forms' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'          => 'wpzf_ajax_error_message',
									'placeholder' => __( 'Oops! Something went wrong. Please try again. Please try again.', 'wpzoom-forms' ),
									'desc'        => __( 'Message displayed if form submission fails.', 'wpzoom-forms' ),
									'default'     => __( 'Oops! Something went wrong. Please try again. Please try again.', 'wpzoom-forms' ),
								),
							),
							array(
								'id'    => 'wpzf_ajax_validation_message',
								'title' => __( 'Validation Message', 'wpzoom-forms' ),
								'type'  => 'input',
								'args'  => array(
									'label_for'          => 'wpzf_ajax_validation_message',
									'placeholder' => __( 'Please complete all required fields before submitting.', 'wpzoom-forms' ),
									'desc'        => __( 'Message displayed if required fields are not filled in.', 'wpzoom-forms' ),
									'default'     => __( 'Please complete all required fields before submitting.', 'wpzoom-forms' ),
								),
							),
						),
					),
				),
			)
		);

		$this->register_settings();
	}

	/**
	 * Register all Setting options
	 *
	 * @since 1.1.0
	 * @return boolean
	 */
	public function register_settings() {
		// filter hook
		self::$settings = apply_filters( 'wpzoom_forms_before_register_settings', self::$settings );

		if ( empty( self::$settings ) ) {
			return;
		}

		foreach ( self::$settings as $key => $setting ) {
			$this->register_setting( $setting );
		}

		return true;
	}

	/**
	 * Register Setting
	 *
	 * @since 2.3.0
	 * @param array $setting
	 * @return void
	 */
	public function register_setting( $setting ) {
		$setting['sanitize_callback'] = isset( $setting['sanitize_callback'] ) ? $setting['sanitize_callback'] : array();
		register_setting( $setting['option_group'], $setting['option_name'], $setting['sanitize_callback'] );

		if ( isset( $setting['sections'] ) && is_array( $setting['sections'] ) ) {
			foreach ( $setting['sections'] as $section ) {
				if ( ! isset( $section['id'] ) ) {
					return;
				}
				add_settings_section( $section['id'], $section['title'], $section['callback'], $section['page'] );

				if ( isset( $section['fields'] ) && is_array( $section['fields'] ) ) {
					foreach ( $section['fields'] as $field ) {
						if ( ! isset( $field['id'] ) ) {
							return;
						}

						if ( method_exists( $this->_fields, $field['type'] ) ) {
							$field['callback'] = array( $this->_fields, $field['type'] );
						} else {
							$field['callback'] = '__return_false';
						}

						add_settings_field( $field['id'], $field['title'], $field['callback'], $section['page'], $section['id'], $field['args'] );
					}
				}
			}
		}
	}

	/**
	 * HTML output for Setting page
	 */
	public function settings_page() {
		// check user capabilities
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		$reset_settings   = isset( $_GET['wpzoom_reset_settings'] ) ? sanitize_text_field( $_GET['wpzoom_reset_settings'] ) : false;
		$settings_updated = isset( $_GET['settings-updated'] ) ? sanitize_text_field( $_GET['settings-updated'] ) : false;
		?>
		<div class="wrap">

			<?php settings_errors(); ?>

			<?php if ( $reset_settings && ! $settings_updated ) : ?>
				<div class="updated settings-error notice is-dismissible">
					<p><strong>Settings have been successfully reset.</strong></p>
				</div>
			<?php endif; ?>

			<form id="wpzf-settings" action="options.php" method="post">
				<ul class="wp-tab-bar">
					<?php foreach ( self::$settings as $setting ) : ?>
						<?php if ( self::$active_tab === $setting['tab_id'] ) : ?>
							<li class="wp-tab-active"><a href="?post_type=wpzf-form&page=wpzf-settings&tab=<?php echo esc_attr( $setting['tab_id'] ); ?>"><?php echo esc_html( $setting['tab_title'] ); ?></a></li>
						<?php else : ?>
							<li><a href="?post_type=wpzf-form&page=wpzf-settings&tab=<?php echo esc_attr( $setting['tab_id'] ); ?>"><?php echo esc_html( $setting['tab_title'] ); ?></a></li>
						<?php endif ?>
					<?php endforeach ?>
				</ul>

				<?php foreach ( self::$settings as $setting ) : ?>
					<?php if ( self::$active_tab === $setting['tab_id'] ) : ?>

						<?php if ( self::$active_tab === 'tab-ajax' ) : ?>
							<?php $this->ajax_promo_banner( true ); ?>
							<?php $this->upsell_banner(); ?>
						<?php endif; ?>

						<div class="wp-tab-panel" id="<?php echo esc_attr( $setting['tab_id'] ); ?>">
							<?php if ( $setting['tab_id'] === 'tab-ajax' ) : ?>
								<div class="wpzoom-lock-overlay"></div>
							<?php endif; ?>

							<?php
								settings_fields( $setting['option_group'] );
								do_settings_sections( $setting['option_group'] );
								if ( isset( $setting['sections'][0]['after_section'] ) ) {
									call_user_func( $setting['sections'][0]['after_section'], $setting['sections'][0] );
								}
							?>
						</div>
					<?php else : ?>

						<?php if ( $setting['tab_id'] === 'tab-ajax' ) : ?>
							<?php $this->ajax_promo_banner( false ); ?>
							<?php $this->upsell_banner(); ?>
						<?php endif; ?>

						<div class="wp-tab-panel" id="<?php echo esc_attr( $setting['tab_id'] ); ?>" style="display: none;">
						<?php if ( $setting['tab_id'] === 'tab-ajax' ) : ?>
							<div class="wpzoom-lock-overlay"></div>
						<?php endif; ?>

							<?php
								settings_fields( $setting['option_group'] );
								do_settings_sections( $setting['option_group'] );
								if ( isset( $setting['sections'][0]['after_section'] ) ) {
									call_user_func( $setting['sections'][0]['after_section'], $setting['sections'][0] );
								}
							?>
						</div>
					<?php endif ?>
				<?php endforeach ?>
				<?php do_action( 'wpzoom_forms_settings_after_main_container' ); ?>
				
				<div class="wpzoom-forms-settings-buttons-container">
					<span class="wpzoom_forms_settings_save"><?php submit_button( 'Save Settings', 'primary', 'wpzoom_forms_settings_save', false ); ?></span>
					<span class="wpzoom_forms_reset_settings"><input type="button" class="button button-secondary" name="wpzoom_forms_reset_settings" id="wpzoom_forms_reset_settings" value="Reset Settings"></span>
				</div>
			</form>
		</div>
		<?php
	}


	/**
	 * Upsell banner
	 */
	public function upsell_banner() {

		$wpzoom_forms = new WPZOOM_Forms();
		?>
		<div class="wpzoom-forms-settings-upsell-container">
			<a href="https://www.wpzoom.com/plugins/wpzoom-forms/?utm_source=wpadmin&utm_medium=wpzoom-forms-free&utm_campaign=upsell-banner-right-sidebar" target="_blank">
				<img src="<?php echo WPZOOM_FORMS_URL; ?>/dist/assets/admin/images/upsell.png" alt="Upgrade to WPZOOM Forms Pro">
			</a>
			<?php $this->subscribe_form(); ?>
		</div>
		<?php
		
	}

	/**
	 * Ajax promo banner
	 */
	public function ajax_promo_banner( $show = true ) {
		$wpzoom_forms = new WPZOOM_Forms();
		if ( $show ) {
			$display = 'inline-block';
		} else {
			$display = 'none';
		}
		?>
		<div class="wpzoom-forms-settings-ajax-promo-container" style="display: <?php echo $display; ?>">
			<div class="wpzoom-forms-settings-ajax-promo-container-inner">
				<a href="https://www.wpzoom.com/plugins/wpzoom-forms/?utm_source=wpadmin&utm_medium=wpzoom-forms-free&utm_campaign=settings-ajax-pro-upsell" target="_blank">
					<span class="wpzoom-forms-settings-ajax-promo-container-inner-lock">🔒</span> <?php esc_html_e('This feature is only available in the PRO version', 'wpzoom-forms'); ?>
				</a>
				<h2><?php esc_html_e('Unlock AJAX Submissions with WPZOOM Forms PRO', 'wpzoom-forms'); ?></h2>
				<p><?php esc_html_e('Let your forms submit instantly without page reloads. Improve user experience and reduce drop-offs with real-time feedback.', 'wpzoom-forms'); ?></p>
				<ul>
					<li>✅ <?php esc_html_e('Display custom success & error messages', 'wpzoom-forms'); ?></li>
					<li>✅ <?php esc_html_e('Works seamlessly with shortcodes and blocks', 'wpzoom-forms'); ?></li>
					<li>✅ <?php esc_html_e('No reloading or redirecting after form submission', 'wpzoom-forms'); ?></li>
				</ul>
				<a href="https://www.wpzoom.com/plugins/wpzoom-forms/?utm_source=wpadmin&utm_medium=wpzoom-forms-free&utm_campaign=settings-ajax-pro-upsell" target="_blank" class="button button-primary"><?php esc_html_e('Upgrade to PRO', 'wpzoom-forms'); ?></a>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue scripts and styles
	 *
	 * @param string $hook
	 */
	public function scripts( $hook ) {

		$pos = strpos( $hook, WPZOOM_FORMS_SETTINGS_PAGE );

		wp_enqueue_style(
			'wpzoom-forms-admin-css',
			untrailingslashit( WPZOOM_FORMS_URL ) . '/dist/assets/admin/css/admin.css',
			array(),
			WPZOOM_FORMS_VERSION
		);

		if ( $pos !== false ) {
			// Add the color picker css file
			wp_enqueue_style( 'wp-color-picker' );

			wp_enqueue_style(
				'wpzoom-forms-admin-style',
				untrailingslashit( WPZOOM_FORMS_URL ) . '/dist/assets/admin/css/style.css',
				array(),
				WPZOOM_FORMS_VERSION
			);

			wp_enqueue_script(
				'wpzoom-forms-admin-script',
				untrailingslashit( WPZOOM_FORMS_URL ) . '/dist/assets/admin/js/script.js',
				array( 'jquery', 'wp-color-picker' ),
				WPZOOM_FORMS_VERSION
			);

			wp_localize_script(
				'wpzoom-forms-admin-script',
				'WPZOOM_Settings',
				array(
					'ajaxUrl'    => admin_url( 'admin-ajax.php' ),
					'ajax_nonce' => wp_create_nonce( 'wpzoom-reset-settings-nonce' ),
				)
			);
		}
	}

	/**
	 * Reset settings to default values
	 *
	 * @since 1.1.0
	 * @return void
	 */
	public function reset_settings() {
		check_ajax_referer( 'wpzoom-reset-settings-nonce', 'security' );

		$defaults = self::get_defaults();

		if ( empty( $defaults ) ) {
			$response = array(
				'status'  => '304',
				'message' => 'NOT',
			);

			wp_send_json_error( $response );
		}

		$response = array(
			'status'  => '200',
			'message' => 'OK',
		);

		self::update_option( $defaults );

		wp_send_json_success( $response );
	}

	public function get_image_sizes() {

		global $_wp_additional_image_sizes;

		$sizes = array();
		$sizes['full'] = 'Full';

		$wp_image_sizes = get_intermediate_image_sizes();

		foreach( $wp_image_sizes as $size ) {
			$sizes[$size] = $size;
		}

		return $sizes;

	}
	
	/**
	 * General section callback
	 */
	public function section_general_cb( $args ) {
		?>
		 <p class="section-description"><?php esc_html_e( 'Customize how WPZOOM Forms loads styles and assets on your site.', 'wpzoom-forms' ); ?></p>
		<?php
	}

	/**
	 * Recaptcha section callback
	 */
	public function section_recaptcha_cb( $args ) {
		?>
        <p class="section-description"><?php esc_html_e( 'Protect your forms from bots, spam, and abuse using privacy-friendly verification tools. Block fake submissions while keeping the experience smooth for real users.', 'wpzoom-forms' ); ?></p>
        <?php
	}

	/**
	 * Recaptcha after section callback
	 */
	public function section_recaptcha_after_cb( $args ) {
		?>
		<div class="wpzoom-forms-settings-recaptcha-after">
			<p><strong><?php esc_html_e('Google reCAPTCHA v3', 'wpzoom-forms') ?></strong><br/>
			<?php esc_html_e('Runs silently in the background and scores each submission to detect spam. Once enabled, it applies to all forms by default. You can disable it per form in the settings.', 'wpzoom-forms') ?></p>

	<p><strong><?php esc_html_e('Google reCAPTCHA v2 (Invisible)', 'wpzoom-forms') ?></strong><br/>
	<?php esc_html_e('Displays a small badge and only shows a challenge (like selecting images) when suspicious activity is detected. Requires v2 site keys — not checkbox keys. Add the CAPTCHA field manually in the form editor.', 'wpzoom-forms') ?></p>

	<p><strong><?php esc_html_e('Cloudflare Turnstile', 'wpzoom-forms') ?></strong><br/>
	<?php esc_html_e('A privacy-first alternative to reCAPTCHA. No cookies or tracking involved. Works invisibly and only challenges users when necessary. To use Turnstile, enter your site and secret keys in the settings and add a Turnstile field to your form.', 'wpzoom-forms') ?></p>
		</div>
		<?php
	}

	/**
	 * AJAX section callback
	 *
	 * @param array $args
	 */
	public function section_ajax_cb( $args ) {
		echo '<p class="section-description">' . esc_html__( 'Enable seamless form submissions without page reloads', 'wpzoom-forms' ) . '</p>';
	}

	/**
	 * Subscribe form
	 */
	public function subscribe_form() {

		$current_user = wp_get_current_user();
		$screen = get_current_screen();
		
		if(  'edit-wpzf-form' !== $screen->id ) { 
			return;
		}
		?>
		<div class="wpzoom-forms-settings-subscribe-form">
            <?php $current_user = wp_get_current_user(); ?>

            <div id="mlb2-6096806" class="ml-form-embedContainer ml-subscribe-form ml-subscribe-form-6096806">
            <div class="ml-form-align-center">
            <div class="ml-form-embedWrapper embedForm">
            <div class="ml-form-embedBody ml-form-embedBodyDefault row-form">
            <div class="ml-form-embedContent" style="">
            	<h4><svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M7.38586 8.35699C7.03068 8.14388 6.56998 8.25906 6.35687 8.61424C6.14376 8.96943 6.25893 9.43012 6.61412 9.64323L11.6141 12.6432C11.8516 12.7857 12.1484 12.7857 12.3859 12.6432L17.3859 9.64323C17.741 9.43012 17.8562 8.96943 17.6431 8.61424C17.43 8.25906 16.9693 8.14388 16.6141 8.35699L12 11.1255L7.38586 8.35699Z" fill="#242628"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M5 19.7315H19C20.519 19.7315 21.75 18.501 21.75 16.9825V7.01953C21.75 5.50032 20.5192 4.26953 19 4.26953H5C3.48079 4.26953 2.25 5.50032 2.25 7.01953V16.9815C2.25 18.5007 3.48079 19.7315 5 19.7315ZM3.75 7.01953C3.75 6.32874 4.30921 5.76953 5 5.76953H19C19.6908 5.76953 20.25 6.32874 20.25 7.01953V16.9825C20.25 17.6721 19.691 18.2315 19 18.2315H5C4.30921 18.2315 3.75 17.6723 3.75 16.9815V7.01953Z" fill="#242628"/>
</svg> Stay Updated on WPZOOM Forms</h4>
            	<p>Subscribe to get notified about new plugin updates and features. We’ll also send you useful tips, tutorials, and limited-time promotions.</p>
            </div>
            <form class="ml-block-form" action="https://static.mailerlite.com/webforms/submit/p0c0n3" data-code="p0c0n3" method="post" target="_blank">
            	<div class="ml-form-formContent">
            	<div class="ml-form-fieldRow ml-last-item">
            		<div class="ml-field-group ml-field-email ml-validate-email ml-validate-required">
            		<input aria-label="email" aria-required="true" value="<?php echo esc_attr( $current_user->user_email ); ?>" type="email" class="form-control" data-inputmask="" name="fields[email]" placeholder="Email" autocomplete="email">
            		</div>
            	</div>
            	</div>
            	<input type="hidden" name="ml-submit" value="1">
            	<div class="ml-form-embedSubmit">
            	<button type="submit" class="button button-primary">Subscribe</button>
            	<button disabled="disabled" style="display:none" type="button" class="loading"> <div class="ml-form-embedSubmitLoad"></div> <span class="sr-only">Loading...</span> </button>
            	</div>
            	<input type="hidden" name="anticsrf" value="true">
            </form>
            </div>
            <div class="ml-form-successBody row-success" style="display:none">
            <div class="ml-form-successContent">
            	<h4>Thank you!</h4>
            	<p>You have successfully joined our subscriber list.</p>
            </div>
            </div>
            </div>
            </div>
            </div>
            <script>
            function ml_webform_success_6096806(){var r=ml_jQuery||jQuery;r(".ml-subscribe-form-6096806 .row-success").show(),r(".ml-subscribe-form-6096806 .row-form").hide()}
            </script>
            <img src="https://track.mailerlite.com/webforms/o/6096806/p0c0n3?v1750160739" width="1" height="1" style="max-width:1px;max-height:1px;visibility:hidden;padding:0;margin:0;display:block" alt="." border="0">
            <script src="https://static.mailerlite.com/js/w/webforms.min.js?vd4de52e171e8eb9c47c0c20caf367ddf" type="text/javascript"></script>
		</div>

	<?php
	}
}

new WPZOOM_Forms_Settings();