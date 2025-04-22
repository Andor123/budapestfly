<?php
namespace WPZOOMElementorForms;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;

use Elementor\Plugin;
use Elementor\Utils;

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * WPZOOM Elementor Forms Widget
 *
 * Elementor widget that inserts a customizable forms.
 *
 * @since 1.0.0
 */
class Wpzoom_Forms_Cpt extends Widget_Base {


	/**
	 * @var \WP_Query
	 */
	private $query = null;

	/**
	 * $post_type
	 * @var string
	 */
	private $post_type = 'wpzf-form';	

	/**
	 * Constructor.
	 *
	 * @since 1.0.0
	 * @access public
	 */
	public function __construct( $data = array(), $args = null ) {
		parent::__construct( $data, $args );
	}

	/**
	 * Get widget name.
	 *
	 * Retrieve widget name.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'wpzoom-forms-widget-cpt';
	}

	/**
	 * Get widget title.
	 *
	 * Retrieve widget title.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Contact Form by WPZOOM', 'wpzoom-forms' );
	}

	/**
	 * Get widget icon.
	 *
	 * Retrieve widget icon.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-form-horizontal';
	}

    /**
     * Get widget keywords.
     *
     * Retrieve the list of keywords the widget belongs to.
     *
     * @since 1.1.0
     *
     * @return array Widget keywords.
     */
    public function get_keywords() {

        return [
            'form',
            'forms',
            'wpzoom',
            'contact form',
            'contact',
        ];
    }

	/**
	 * Get widget categories.
	 *
	 * Retrieve the list of categories the widget belongs to.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return array( 'wpzoom-elementor-form' );
	}

	/**
	 * Script Dependencies.
	 *
	 * Returns all the scripts the widget depends on.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array Script slugs.
	 */
	public function get_script_depends() {
		$deps = array( 'jquery' );
		return $deps;
	}

	/**
	 * Get the query
	 *
	 * Returns the current query.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return \WP_Query The current query.
	 */
	public function get_query() {
		return $this->query;
	}

	/**
	 * Register Controls.
	 *
	 * Registers all the controls for this widget.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_controls() {
		$this->register_content_controls();
		$this->register_style_controls();
	}

	/**
	 * Register Content Controls.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_content_controls() {

		$this->start_controls_section(
			'_section_forms_cpt',
			array(
				'label' => esc_html__( 'WPZOOM Forms', 'wpzoom-forms' ),
				'tab'   => Controls_Manager::TAB_CONTENT,
			)
		);

		$this->add_control(
			'forms_post_id',
			array(
				'label'    => esc_html__( 'Select a Form', 'wpzoom-forms' ),
				'type'     => Controls_Manager::SELECT2,
				'label_block' => true,
				'options'  => $this->get_forms_posts(),
			)
		);

		$this->end_controls_section();
	}

	/**
	 * Register Styling Controls.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function register_style_controls() {

		// Form.
		$this->start_controls_section(
			'section_form_style',
			[
				'label' => esc_html__( 'Form', 'wpzoom-forms' ),
				'tab'   => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Background::get_type(),
			[
				'name' => 'form_bg',
				'types' => [ 'classic', 'gradient' ],
				'selector' => '{{WRAPPER}} .wp-block-wpzoom-forms-form',
			]
		);

		$this->add_control(
			'form_text',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Text Color', 'wpzoom-forms' ),
				'separator' => '',
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} .wp-block-wpzoom-forms-form p' => 'color: {{VALUE}};'
				]
			]
		);

		$this->add_control(
			'form_padding',
			[
				'label' => esc_html__( 'Padding', 'wpzoom-forms' ),
				'type' => \Elementor\Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} .wp-block-wpzoom-forms-form' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_section();


		// Fields.
		$this->start_controls_section(
			'section_fields_style',
			[
				'label' => esc_html__( 'Fields', 'wpzoom-forms' ),
				'tab'   => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'fields_border',
				'selector' => '{{WRAPPER}} input:not([type="submit"]), {{WRAPPER}} textarea',
			]
		);

		$this->add_responsive_control(
			'fields_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-forms' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} input:not([type="submit"]), {{WRAPPER}} textarea' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'fields_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-forms' ),
				'separator' => '',
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} input:not([type="submit"]), {{WRAPPER}} textarea' => 'color: {{VALUE}};'
				]
			]
		);

		$this->add_control(
			'fields_bg',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Background Color', 'wpzoom-forms' ),
				'separator' => '',
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} input:not([type="submit"]), {{WRAPPER}} textarea' => 'background-color: {{VALUE}};'
				]
			]
		);

		$this->end_controls_section();

		// Labels.
		$this->start_controls_section(
			'section_labels_style',
			[
				'label' => esc_html__( 'Labels', 'wpzoom-forms' ),
				'tab'   => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_control(
			'labels_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-forms' ),
				'separator' => '',
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} label' => 'color: {{VALUE}};'
				]
			]
		);

		$this->end_controls_section();

		// Button.
		$this->start_controls_section(
			'section_button_style',
			[
				'label' => esc_html__( 'Button', 'wpzoom-forms' ),
				'tab'   => Controls_Manager::TAB_STYLE
			]
		);

		$this->add_group_control(
			\Elementor\Group_Control_Border::get_type(),
			[
				'name' => 'button_border',
				'selector' => '{{WRAPPER}} input[type="submit"]',
			]
		);

		$this->add_responsive_control(
			'button_border_radius',
			[
				'label' => esc_html__( 'Border Radius', 'wpzoom-forms' ),
				'type' => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%', 'em', 'rem', 'custom' ],
				'selectors' => [
					'{{WRAPPER}} input[type="submit"]' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);

		$this->add_control(
			'button_color',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Color', 'wpzoom-forms' ),
				'separator' => '',
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} input[type="submit"]' => 'color: {{VALUE}};'
				]
			]
		);

		$this->add_control(
			'button_bg',
			[
				'type'      => Controls_Manager::COLOR,
				'label'     => esc_html__( 'Background Color', 'wpzoom-forms' ),
				'separator' => '',
				'default'   => '',
				'selectors' => [
					'{{WRAPPER}} input[type="submit"]' => 'background-color: {{VALUE}};'
				]
			]
		);

		$this->end_controls_section();


	}



	/**
	 * Get rcb posts.
	 *
	 * Retrieve a list of all forms posts.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return array All rcb posts.
	 */
	protected function get_forms_posts() {

		$forms_posts = array();

		$args = array(
			'post_type'   => $this->post_type,
			'numberposts' => -1
		);

		$posts = get_posts( $args );

		if ( !empty( $posts ) && !is_wp_error( $posts ) ) {
			foreach ( $posts as $key => $post ) {
				if ( is_object( $post ) && property_exists( $post, 'ID' ) ) {
					$forms_posts[ $post->ID ] = get_the_title( $post );
				}
			}
		}

		return $forms_posts;

	}

	/**
	 * Render the Widget.
	 *
	 * Renders the widget on the frontend.
	 *
	 * @since 1.0.0
	 * @access public
	 * @return void
	 */
	protected function render() {

		$settings = $this->get_settings_for_display();

		$post_id = isset( $settings['forms_post_id'] ) ? $settings['forms_post_id'] : null;

		if( ! $post_id ) {
			return;
		}

		$form = get_post( intval( $post_id ) );

		if ( has_blocks( $form->post_content ) ) {
			$blocks = parse_blocks( $form->post_content );
		}

		if ( \Elementor\Plugin::$instance->editor->is_edit_mode() ) {
			printf( 
				'<div class="wpzoom-forms-post wpzoom-rcb-post-shortcode">%1$s</div>',
				do_blocks( $form->post_content )
			);
	   } else {
			echo do_shortcode( '[wpzf_form id="' . $post_id . '"]' );
	   }

	}

}