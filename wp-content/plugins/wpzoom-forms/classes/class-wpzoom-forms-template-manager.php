<?php
/**
 * Class WPZOOM_Forms_Template_Manager
 *
 * @since   1.0.5
 * @package WPZOOM_Forms
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( 'WPZOOM_Forms_Template_Manager' ) ) {
	/**
	 * Main WPZOOM_Template_Manager Class.
	 *
	 * @since 1.0.5
	 */
	class WPZOOM_Forms_Template_Manager {
		/**
		 * This plugin's instance.
		 *
		 * @var WPZOOM_Forms_Template_Manager
		 * @since 1.0.5
		 */
		private static $instance;

		/**
		 * Provides singleton instance.
		 *
		 * @since 1.0.5
		 * @return self instance
		 */
		public static function instance() {
			if ( null === self::$instance ) {
				self::$instance = new WPZOOM_Forms_Template_Manager();
			}

			return self::$instance;
		}

		/**
		 * The Constructor.
		 */
		public function __construct() {
			
			add_action( 'admin_enqueue_scripts', array( $this, 'scripts' ) );
			add_action( 'admin_footer', array( $this, 'modal_window' ) );
			add_filter( 'default_content', array( $this, 'default_form_content' ), 10, 2 );
		
		}

	/**
	 * Set default content for the form CPT.
	 *
	 * @since 1.2.0
	 */
	public function default_form_content( $content, $post ) {

		$template = isset( $_GET['template'] ) ? $_GET['template'] : 'contact-form';

		if ( self::get_current_post_type( 'wpzf-form' ) ) {
			if( ! empty( $template ) ) {
				$templates = include WPZOOM_FORMS_PATH . 'templates/templates.php';
				foreach( $templates as $template_data ) {
					if( $template_data['id'] == $template ) {
						$content = $template_data['content'];
					}
				}
			}
		}


		return $content;

	}

	/**
	 * Get current post type.
	 *
	 * @since 1.2.0
	 */
	public static function get_current_post_type( $post_type = '' ) {
		
		$type = false;
	
		if( isset( $_GET['post'] ) ) {
			$id = $_GET['post'];
			$post = get_post( $id );
			if( is_object( $post ) && $post->post_type == $post_type ) {
				$type = true;
			}
		} elseif ( isset( $_GET['post_type'] ) && $_GET['post_type'] == $post_type ) {
			$type = true;
		}
		
		return $type;	
	}

		/**
		 * Include admin scripts & styles
		 */
		public function scripts() {
				
			// Enqueue modal scripts
			wp_enqueue_script( 
				'wpzoom-forms-modal', 
				WPZOOM_FORMS_URL . 'dist/assets/admin/js/modal.js', 
				array( 'jquery' ),
				WPZOOM_FORMS_VERSION, 
				true 
			);
			
			wp_enqueue_style( 
				'wpzoom-forms-modal', 
				WPZOOM_FORMS_URL . 'dist/assets/admin/css/modal.css',
				array(), 
				WPZOOM_FORMS_VERSION
			);

		}

		/**
		 * Include modal window
		 */
		public function modal_window() {

			$screen = get_current_screen();
						
			if( $screen->base == 'edit' && $screen->post_type == 'wpzf-form' ) {
				$settings_class = new WPZOOM_Forms_Settings();
				$upsell_banner = $settings_class->upsell_banner();
			}

				$new_post_link = admin_url( 'post-new.php?post_type=wpzf-form' );

				$new_post_link = add_query_arg( array(
					'template' => 'contact-form',
				), $new_post_link );

				?>
				<div id="wpzoom-forms-modal" class="wpzoom-forms-modal" data-new-post-url="<?php echo esc_url( $new_post_link ); ?>">
					<div class="wpzoom-forms-modal-content">
						<div class="wpzoom-forms-modal-header">
							<h2>
								<span class="wpzoom-forms-modal-icon">
								<svg width="32" height="32" viewBox="0 0 32 32" fill="none" xmlns="http://www.w3.org/2000/svg">
<path d="M4 21C4.55228 21 5 21.4477 5 22V25.3333C5 26.2544 5.74562 27 6.66667 27H10C10.5523 27 11 27.4477 11 28C11 28.5523 10.5523 29 10 29H6.66667C4.64105 29 3 27.359 3 25.3333V22C3 21.4477 3.44772 21 4 21Z" fill="#323232"/>
<path d="M28 21C28.5523 21 29 21.4477 29 22V25.3333C29 27.359 27.359 29 25.3333 29H22C21.4477 29 21 28.5523 21 28C21 27.4477 21.4477 27 22 27H25.3333C26.2544 27 27 26.2544 27 25.3333V22C27 21.4477 27.4477 21 28 21Z" fill="#323232"/>
<path d="M16 15C16.5523 15 17 15.4477 17 16C17 16.5523 16.5523 17 16 17H13.3333C12.781 17 12.3333 16.5523 12.3333 16C12.3333 15.4477 12.781 15 13.3333 15H16Z" fill="#323232"/>
<path d="M18.6667 11C19.219 11 19.6667 11.4477 19.6667 12C19.6667 12.5523 19.219 13 18.6667 13H13.3333C12.781 13 12.3333 12.5523 12.3333 12C12.3333 11.4477 12.781 11 13.3333 11H18.6667Z" fill="#323232"/>
<path fill-rule="evenodd" clip-rule="evenodd" d="M20 7C22.0256 7 23.6667 8.64105 23.6667 10.6667V21.3333C23.6667 23.359 22.0256 25 20 25H12C9.97438 25 8.33333 23.359 8.33333 21.3333V10.6667C8.33333 8.64105 9.97438 7 12 7H20ZM12 9C11.079 9 10.3333 9.74562 10.3333 10.6667V21.3333C10.3333 22.2544 11.079 23 12 23H20C20.921 23 21.6667 22.2544 21.6667 21.3333V10.6667C21.6667 9.74562 20.921 9 20 9H12Z" fill="#323232"/>
<path d="M10 3C10.5523 3 11 3.44772 11 4C11 4.55228 10.5523 5 10 5H6.66667C5.74562 5 5 5.74562 5 6.66667V10C5 10.5523 4.55228 11 4 11C3.44772 11 3 10.5523 3 10V6.66667C3 4.64105 4.64105 3 6.66667 3H10Z" fill="#323232"/>
<path d="M25.3333 3C27.359 3 29 4.64105 29 6.66667V10C29 10.5523 28.5523 11 28 11C27.4477 11 27 10.5523 27 10V6.66667C27 5.74562 26.2544 5 25.3333 5H22C21.4477 5 21 4.55228 21 4C21 3.44772 21.4477 3 22 3H25.3333Z" fill="#323232"/>
</svg>
</span>
								<?php esc_html_e( 'Select a Template', 'wpzoom-forms' ); ?>
							</h2>
							<span class="wpzoom-forms-modal-close">&times;</span>
						</div>
						
						<div class="wpzoom-forms-ai-upsell">
							<div class="wpzoom-forms-ai-upsell-content">
								<div class="wpzoom-forms-ai-upsell-icon">
									<svg width="40" height="40" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
										<path d="M12 2L15.09 8.26L22 9.27L17 14.14L18.18 21.02L12 17.77L5.82 21.02L7 14.14L2 9.27L8.91 8.26L12 2Z" fill="#3496FF"/>
									</svg>
								</div>
								<div class="wpzoom-forms-ai-upsell-text">
									<h3><?php esc_html_e( 'Generate Forms with AI', 'wpzoom-forms' ); ?></h3>
									<p><?php esc_html_e( 'Describe your form in simple words and let AI create it for you instantly.', 'wpzoom-forms' ); ?></p>
								</div>
								<div class="wpzoom-forms-ai-upsell-button">
									<a href="https://www.wpzoom.com/plugins/wpzoom-forms/?utm_source=wpadmin&utm_medium=wpzoom-forms-free&utm_campaign=ai-generator-upsell-template-modal" target="_blank" class="button button-primary">
										<?php esc_html_e( 'Upgrade to PRO', 'wpzoom-forms' ); ?>
									</a>
								</div>
							</div>
						</div>
						
						<ul class="wpzoom-forms-templates-list">
							<?php
								$templates = include WPZOOM_FORMS_PATH . 'templates/templates.php';
								foreach( $templates as $template_data ) {
									$is_pro = isset( $template_data['is_pro'] ) && $template_data['is_pro'] ? true : false;
									$class = array( 'wpzoom-forms-template-list-item' );

									if( $is_pro ) {
										$class[] = ' wpzoom-forms-template-list-item-pro';
									}
									$class = implode( ' ', $class );
									
									?>
									<li class="<?php echo $class; ?>">
										<a href="#" data-template-id="<?php echo esc_attr( $template_data['id'] ) ?>">
											<div class="wpzoom-forms-template-list-wrapper">
												<span class="wpzoom-forms-icon-holder"><?php echo $template_data['icon'] ?></span>
												<div class="wpzoom-forms-template-list-content">
													<?php echo $template_data['name'] ?>
													<span><?php echo $template_data['desc'] ?></span>
												</div>
											</div>
										</a>
										<?php if( $is_pro ) : ?>
											<small class="pro-only"><?php esc_html_e( 'PRO', 'wpzoom-forms' ); ?></small>
										<?php endif; ?>
									</li>
									<?php
								}
							?>
							<li class="wpzoom-forms-template-list-item wpzoom-forms-template-list-item-cta">
								<a href="https://zoomforms.co/?utm_source=wpadmin&utm_medium=wpzoom-forms-free&utm_campaign=view-all-templates-template-modal" target="_blank">
									<div class="wpzoom-forms-template-list-wrapper">
										<span class="wpzoom-forms-icon-holder">
											<svg xmlns="http://www.w3.org/2000/svg" height="24px" viewBox="0 -960 960 960" width="24px" fill="#000000"><path d="M440-140H212.31Q182-140 161-161q-21-21-21-51.31v-535.38Q140-778 161-799q21-21 51.31-21H440v680Zm80-380v-300h227.69Q778-820 799-799q21 21 21 51.31V-520H520Zm0 380v-300h300v227.69Q820-182 799-161q-21 21-51.31 21H520Z"/></svg>
										</span>
										<div class="wpzoom-forms-template-list-content">
											<?php esc_html_e( 'View All 30+ Templates', 'wpzoom-forms' ); ?>
											<span><?php esc_html_e( 'Explore our complete collection of professional form templates', 'wpzoom-forms' ); ?></span>
										</div>
									</div>
								</a>
							</li>
						</ul>
						<div class="wpzoom-forms-modal-footer">
							<a class="button-secondary wpzoom-btn-bordered" id="wpzoom_close_modal" href="#"><?php esc_html_e( 'Cancel', 'wpzoom-forms' ); ?></a>
						</div>
					</div>
					
				</div>

				<script type="text/javascript">
					jQuery(document).ready(function($) {
						$('.wpzoom-forms-settings-upsell-container').insertAfter('#posts-filter');
					});
					</script>
				<?php
		}
	
	}

	WPZOOM_Forms_Template_Manager::instance();
}