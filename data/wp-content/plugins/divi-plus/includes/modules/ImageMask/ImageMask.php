<?php
/**
 * @author      Elicus <hello@elicus.com>
 * @link        https://www.elicus.com/
 * @copyright   2022 Elicus Technologies Private Limited
 * @version     1.9.6
 */
class DIPL_ImageMask extends ET_Builder_Module {

	public $slug       = 'dipl_image_mask';
	public $child_slug = 'dipl_image_mask_item';
	public $vb_support = 'on';

	protected $module_credits = array(
		'module_uri' => 'https://diviextended.com/product/divi-plus/',
		'author'     => 'Elicus',
		'author_uri' => 'https://elicus.com/',
	);

	public function init() {
		$this->name = esc_html__( 'DP Image Mask', 'divi-plus' );
	}

	public function get_settings_modal_toggles() {
		return array(
			'general'  => array(
				'toggles' => array(
					'main_content' => array(
						'title' => esc_html__( 'Content', 'divi-plus' ),
					),
				),
			),
			'advanced' => array(
				'toggles' => array(
					'mask_settings' => array(
						'title'    => esc_html__( 'Mask Settings', 'divi-plus' ),
						'priority' => 1,
					),
				),
			),
		);
	}

	public function get_advanced_fields_config() {
		return array(
			'fonts'          => false,
			'text'           => false,
			'borders'        => false,
			'max_width'      => false,
			'margin_padding' => false,
			'filters'        => false,
		);
	}

	public function get_fields() {

		$dipl_image_mask_fields = array(
			'mask_on'                  => array(
				'label'           => esc_html__( 'Mask On', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'image'      => esc_html__( 'Image', 'divi-plus' ),
					'background' => esc_html__( 'Background', 'divi-plus' ),
				),
				'default'         => 'image',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can choose the mask on which element.', 'divi-plus' ),
			),
			'mask_image'               => array(
				'label'              => esc_html__( 'Upload Image to be Masked', 'divi-plus' ),
				'type'               => 'upload',
				'option_category'    => 'configuration',
				'upload_button_text' => esc_attr__( 'Upload an image', 'divi-plus' ),
				'choose_text'        => esc_attr__( 'Choose an Image', 'divi-plus' ),
				'update_text'        => esc_attr__( 'Set As Image', 'divi-plus' ),
				'show_if'            => array(
					'mask_on' => 'image',
				),
				'tab_slug'           => 'general',
				'toggle_slug'        => 'main_content',
				'description'        => esc_html__( 'Here you can set the image to be masked.', 'divi-plus' ),
			),
			'mask_image_alt'           => array(
				'label'           => esc_html__( 'Image Alt Text', 'divi-plus' ),
				'type'            => 'text',
				'option_category' => 'basic_option',
				'show_if'         => array(
					'mask_on' => 'image',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Define the HTML ALT text for your image here.', 'divi-plus' ),
			),
			'mask_background_color'    => array(
				'label'             => esc_html__( 'Background to be Masked', 'divi-plus' ),
				'type'              => 'background-field',
				'base_name'         => 'mask_background',
				'context'           => 'mask_background_color',
				'option_category'   => 'button',
				'custom_color'      => true,
				'background_fields' => $this->generate_background_options( 'mask_background', 'button', 'advanced', 'main_content', 'mask_background_color' ),
				'show_if'           => array(
					'mask_on' => 'background',
				),
				'mobile_options'    => true,
				'tab_slug'          => 'general',
				'toggle_slug'       => 'main_content',
				'description'       => esc_html__( 'Here you can set the background to be masked.', 'divi-plus' ),
			),
			'mask_type'                => array(
				'label'           => esc_html__( 'Mask Type', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'arrow'   => esc_html__( 'Arrow', 'divi-plus' ),
					'circle'  => esc_html__( 'Circle', 'divi-plus' ),
					'fluid'   => esc_html__( 'Fluid', 'divi-plus' ),
					'heart'   => esc_html__( 'Heart', 'divi-plus' ),
					'leaf'    => esc_html__( 'Leaf', 'divi-plus' ),
					'music'   => esc_html__( 'Music', 'divi-plus' ),
					'shield'  => esc_html__( 'Shield', 'divi-plus' ),
					'speech'  => esc_html__( 'Speech Bubble', 'divi-plus' ),
					'splodge' => esc_html__( 'Splodge', 'divi-plus' ),
					'star'    => esc_html__( 'Star', 'divi-plus' ),
					'square'  => esc_html__( 'Square', 'divi-plus' ),
					'no_mask' => esc_html__( 'No Mask', 'divi-plus' ),
				),
				'default'         => 'arrow',
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the mask type.', 'divi-plus' ),
			),
			'arrow_style'              => array(
				'label'           => esc_html__( 'Arrow Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'arrow_1'  => esc_html__( 'Arrow 1', 'divi-plus' ),
					'arrow_2'  => esc_html__( 'Arrow 2', 'divi-plus' ),
					'arrow_3'  => esc_html__( 'Arrow 3', 'divi-plus' ),
					'arrow_4'  => esc_html__( 'Arrow 4', 'divi-plus' ),
					'arrow_5'  => esc_html__( 'Arrow 5', 'divi-plus' ),
					'arrow_6'  => esc_html__( 'Arrow 6', 'divi-plus' ),
					'arrow_7'  => esc_html__( 'Arrow 7', 'divi-plus' ),
					'arrow_8'  => esc_html__( 'Arrow 8', 'divi-plus' ),
					'arrow_9'  => esc_html__( 'Arrow 9', 'divi-plus' ),
					'arrow_10' => esc_html__( 'Arrow 10', 'divi-plus' ),
					'arrow_11' => esc_html__( 'Arrow 11', 'divi-plus' ),
				),
				'default'         => 'arrow_1',
				'show_if'         => array(
					'mask_type' => 'arrow',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the arrow style.', 'divi-plus' ),
			),
			'circle_style'             => array(
				'label'           => esc_html__( 'Circle Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'circle_1' => esc_html__( 'Circle 1', 'divi-plus' ),
					'circle_2' => esc_html__( 'Circle 2', 'divi-plus' ),
				),
				'default'         => 'circle_1',
				'show_if'         => array(
					'mask_type' => 'circle',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the circle style.', 'divi-plus' ),
			),
			'fluid_style'              => array(
				'label'           => esc_html__( 'Fluid Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'fluid_1'  => esc_html__( 'Fluid 1', 'divi-plus' ),
					'fluid_2'  => esc_html__( 'Fluid 2', 'divi-plus' ),
					'fluid_3'  => esc_html__( 'Fluid 3', 'divi-plus' ),
					'fluid_4'  => esc_html__( 'Fluid 4', 'divi-plus' ),
					'fluid_5'  => esc_html__( 'Fluid 5', 'divi-plus' ),
					'fluid_6'  => esc_html__( 'Fluid 6', 'divi-plus' ),
					'fluid_7'  => esc_html__( 'Fluid 7', 'divi-plus' ),
					'fluid_8'  => esc_html__( 'Fluid 8', 'divi-plus' ),
					'fluid_9'  => esc_html__( 'Fluid 9', 'divi-plus' ),
					'fluid_10' => esc_html__( 'Fluid 10', 'divi-plus' ),
					'fluid_11' => esc_html__( 'Fluid 11', 'divi-plus' ),
					'fluid_12' => esc_html__( 'Fluid 12', 'divi-plus' ),
				),
				'default'         => 'fluid_1',
				'show_if'         => array(
					'mask_type' => 'fluid',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the fluid style.', 'divi-plus' ),
			),
			'heart_style'              => array(
				'label'           => esc_html__( 'Heart Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'heart_1' => esc_html__( 'Heart 1', 'divi-plus' ),
					'heart_2' => esc_html__( 'Heart 2', 'divi-plus' ),
					'heart_3' => esc_html__( 'Heart 3', 'divi-plus' ),
					'heart_4' => esc_html__( 'Heart 4', 'divi-plus' ),
					'heart_5' => esc_html__( 'Heart 5', 'divi-plus' ),
					'heart_6' => esc_html__( 'Heart 6', 'divi-plus' ),
				),
				'default'         => 'heart_1',
				'show_if'         => array(
					'mask_type' => 'heart',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the heart style.', 'divi-plus' ),
			),
			'hexagon_style'            => array(
				'label'           => esc_html__( 'Hexagon Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'hexagon_1' => esc_html__( 'Hexagon 1', 'divi-plus' ),
					'hexagon_2' => esc_html__( 'Hexagon 2', 'divi-plus' ),
					'hexagon_3' => esc_html__( 'Hexagon 3', 'divi-plus' ),
					'hexagon_4' => esc_html__( 'Hexagon 4', 'divi-plus' ),
					'hexagon_5' => esc_html__( 'Hexagon 5', 'divi-plus' ),
					'hexagon_6' => esc_html__( 'Hexagon 6', 'divi-plus' ),
				),
				'default'         => 'hexagon_1',
				'show_if'         => array(
					'mask_type' => 'hexagon',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the hexagon style.', 'divi-plus' ),
			),
			'leaf_style'               => array(
				'label'           => esc_html__( 'Leaf Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'leaf_1' => esc_html__( 'Leaf 1', 'divi-plus' ),
					'leaf_2' => esc_html__( 'Leaf 2', 'divi-plus' ),
					'leaf_3' => esc_html__( 'Leaf 3', 'divi-plus' ),
					'leaf_4' => esc_html__( 'Leaf 4', 'divi-plus' ),
				),
				'default'         => 'leaf_1',
				'show_if'         => array(
					'mask_type' => 'leaf',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the leaf style.', 'divi-plus' ),
			),
			'music_style'              => array(
				'label'           => esc_html__( 'Music Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'music_1' => esc_html__( 'Music 1', 'divi-plus' ),
					'music_2' => esc_html__( 'Music 2', 'divi-plus' ),
					'music_3' => esc_html__( 'Music 3', 'divi-plus' ),
					'music_4' => esc_html__( 'Music 4', 'divi-plus' ),
					'music_5' => esc_html__( 'Music 5', 'divi-plus' ),
				),
				'default'         => 'music_1',
				'show_if'         => array(
					'mask_type' => 'music',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the music style.', 'divi-plus' ),
			),
			'shield_style'             => array(
				'label'           => esc_html__( 'Shield Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'shield_1' => esc_html__( 'Shield 1', 'divi-plus' ),
					'shield_2' => esc_html__( 'Shield 2', 'divi-plus' ),
				),
				'default'         => 'shield_1',
				'show_if'         => array(
					'mask_type' => 'shield',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the shield style.', 'divi-plus' ),
			),
			'speech_style'             => array(
				'label'           => esc_html__( 'Speech Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'speech_1' => esc_html__( 'Speech 1', 'divi-plus' ),
					'speech_2' => esc_html__( 'Speech 2', 'divi-plus' ),
					'speech_3' => esc_html__( 'Speech 3', 'divi-plus' ),
				),
				'default'         => 'speech_1',
				'show_if'         => array(
					'mask_type' => 'speech',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the speech style.', 'divi-plus' ),
			),
			'splodge_style'            => array(
				'label'           => esc_html__( 'Splodge Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'splodge_1' => esc_html__( 'Splodge 1', 'divi-plus' ),
					'splodge_2' => esc_html__( 'Splodge 2', 'divi-plus' ),
					'splodge_3' => esc_html__( 'Splodge 3', 'divi-plus' ),
					'splodge_4' => esc_html__( 'Splodge 4', 'divi-plus' ),
				),
				'default'         => 'splodge_1',
				'show_if'         => array(
					'mask_type' => 'splodge',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the splodge style.', 'divi-plus' ),
			),
			'star_style'               => array(
				'label'           => esc_html__( 'Star Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'star_1' => esc_html__( 'Star 1', 'divi-plus' ),
					'star_2' => esc_html__( 'Star 2', 'divi-plus' ),
					'star_3' => esc_html__( 'Star 3', 'divi-plus' ),
					'star_4' => esc_html__( 'Star 4', 'divi-plus' ),
					'star_5' => esc_html__( 'Star 5', 'divi-plus' ),
				),
				'default'         => 'star_1',
				'show_if'         => array(
					'mask_type' => 'star',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the star style.', 'divi-plus' ),
			),
			'square_style'             => array(
				'label'           => esc_html__( 'Square Style', 'divi-plus' ),
				'type'            => 'select',
				'option_category' => 'configuration',
				'options'         => array(
					'square_1' => esc_html__( 'Square 1', 'divi-plus' ),
					'square_2' => esc_html__( 'Square 2', 'divi-plus' ),
					'square_3' => esc_html__( 'Square 3', 'divi-plus' ),
					'square_4' => esc_html__( 'Square 4', 'divi-plus' ),
				),
				'default'         => 'square_1',
				'show_if'         => array(
					'mask_type' => 'square',
				),
				'tab_slug'        => 'general',
				'toggle_slug'     => 'main_content',
				'description'     => esc_html__( 'Here you can set the square style.', 'divi-plus' ),
			),
			'mask_width'               => array(
				'label'          => esc_html__( 'Mask Width', 'divi-plus' ),
				'type'           => 'range',
				'range_settings' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'mobile_options' => true,
				'default'        => '100%',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'mask_settings',
				'description'    => esc_html__( 'Here you can select mask width', 'divi-plus' ),
			),
			'mask_height'              => array(
				'label'          => esc_html__( 'Mask Height', 'divi-plus' ),
				'type'           => 'range',
				'range_settings' => array(
					'min'  => '0',
					'max'  => '3000',
					'step' => '1',
				),
				'show_if'        => array(
					'mask_on' => 'background',
				),
				'mobile_options' => true,
				'default'        => '500px',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'mask_settings',
				'description'    => esc_html__( 'Here you can select mask height', 'divi-plus' ),
			),
			'scale_mask'               => array(
				'label'          => esc_html__( 'Scale Mask', 'divi-plus' ),
				'type'           => 'range',
				'range_settings' => array(
					'min'  => '0',
					'max'  => '1',
					'step' => '0.05',
				),
				'unitless'       => true,
				'default'        => '1',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'mask_settings',
				'description'    => esc_html__( 'Here you can scale the mask.', 'divi-plus' ),
			),
			'rotate_mask'              => array(
				'label'          => esc_html__( 'Rotate Mask', 'divi-plus' ),
				'type'           => 'range',
				'range_settings' => array(
					'min'  => '0',
					'max'  => '360',
					'step' => '1',
				),
				'default'        => '0deg',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'mask_settings',
				'description'    => esc_html__( 'Here you can rotate the mask.', 'divi-plus' ),
			),
			'horizontal_mask_position' => array(
				'label'          => esc_html__( 'Horizontal Mask Position', 'divi-plus' ),
				'type'           => 'range',
				'range_settings' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'default'        => '0%',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'mask_settings',
				'description'    => esc_html__( 'Here you can set the mask position horizontally.', 'divi-plus' ),
			),
			'vertical_mask_position'   => array(
				'label'          => esc_html__( 'Vertical Mask Position', 'divi-plus' ),
				'type'           => 'range',
				'range_settings' => array(
					'min'  => '0',
					'max'  => '100',
					'step' => '1',
				),
				'default'        => '0%',
				'tab_slug'       => 'advanced',
				'toggle_slug'    => 'mask_settings',
				'description'    => esc_html__( 'Here you can set the mask position vertically.', 'divi-plus' ),
			),
		);

		$dipl_image_mask_fields = array_merge( $dipl_image_mask_fields, $this->generate_background_options( 'mask_background', 'skip', 'general', 'main_content', 'mask_background_color' ) );

		return $dipl_image_mask_fields;
	}

	public function render( $attrs, $content, $render_slug ) {

		$mask_on                  = esc_attr( $this->props['mask_on'] );
		$mask_image               = esc_attr( $this->props['mask_image'] );
		$mask_image_alt           = esc_attr( $this->props['mask_image_alt'] );
		$mask_type                = esc_attr( $this->props['mask_type'] );
		$arrow_style              = esc_attr( $this->props['arrow_style'] );
		$circle_style             = esc_attr( $this->props['circle_style'] );
		$fluid_style              = esc_attr( $this->props['fluid_style'] );
		$heart_style              = esc_attr( $this->props['heart_style'] );
		$hexagon_style            = esc_attr( $this->props['hexagon_style'] );
		$leaf_style               = esc_attr( $this->props['leaf_style'] );
		$music_style              = esc_attr( $this->props['music_style'] );
		$shield_style             = esc_attr( $this->props['shield_style'] );
		$speech_style             = esc_attr( $this->props['speech_style'] );
		$splodge_style            = esc_attr( $this->props['splodge_style'] );
		$star_style               = esc_attr( $this->props['star_style'] );
		$square_style             = esc_attr( $this->props['square_style'] );
		$mask_width               = et_pb_responsive_options()->get_property_values( $this->props, 'mask_width' );
		$mask_height              = et_pb_responsive_options()->get_property_values( $this->props, 'mask_height' );
		$scale_mask               = floatval( $this->props['scale_mask'] );
		$rotate_mask              = esc_attr( $this->props['rotate_mask'] );
		$horizontal_mask_position = esc_attr( $this->props['horizontal_mask_position'] );
		$vertical_mask_position   = esc_attr( $this->props['vertical_mask_position'] );
		$content                  = ( '' !== $this->content ) ? $this->content : '';

		if ( '' !== $scale_mask ) {
			$scale = sprintf( 'scale(%1$s)', esc_attr( $scale_mask ) );
		}

		if ( '' !== $rotate_mask ) {
			$rotate = sprintf( 'rotate(%1$s)', esc_attr( $rotate_mask ) );
		}

		if ( ! empty( array_filter( $mask_width ) ) && 'no_mask' !== $mask_type ) {
			et_pb_responsive_options()->generate_responsive_css( $mask_width, '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner', 'width', $render_slug, '!important;', 'type' );
		}

		if ( ( '' !== $scale || '' !== $rotate ) && 'no_mask' !== $mask_type ) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( 'transform: %1$s %2$s !important;', esc_attr( $scale ), esc_attr( $rotate ) ),
				)
			);
		}

		if ( '' !== $horizontal_mask_position && 'no_mask' !== $mask_type ) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( '-webkit-mask-position-x: %1$s !important;', esc_attr( $horizontal_mask_position ) ),
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( '-moz-mask-position-x: %1$s !important;', esc_attr( $horizontal_mask_position ) ),
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( '-o-mask-position-x: %1$s !important;', esc_attr( $horizontal_mask_position ) ),
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( 'mask-position-x: %1$s !important;', esc_attr( $horizontal_mask_position ) ),
				)
			);
		}

		if ( '' !== $vertical_mask_position && 'no_mask' !== $mask_type ) {
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( '-webkit-mask-position-y: %1$s !important;', esc_attr( $vertical_mask_position ) ),
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( '-moz-mask-position-y: %1$s !important;', esc_attr( $vertical_mask_position ) ),
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( '-o-mask-position-y: %1$s !important;', esc_attr( $vertical_mask_position ) ),
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( 'mask-position-y: %1$s !important;', esc_attr( $vertical_mask_position ) ),
				)
			);
		}

		if ( '' !== $mask_type && 'no_mask' !== $mask_type ) {
			$base_url = get_site_url() . '/wp-content/plugins/divi-plus/includes/assets/image_mask/';
			if ( 'circle' === $mask_type ) {
				$mask_url = $base_url . $mask_type . '/' . $circle_style . '.svg';
			} elseif ( 'fluid' === $mask_type ) {
				$mask_url = $base_url . $mask_type . '/' . $fluid_style . '.svg';
			} elseif ( 'heart' === $mask_type ) {
				$mask_url = $base_url . $mask_type . '/' . $heart_style . '.svg';
			} elseif ( 'hexagon' === $mask_type ) {
				$mask_url = $base_url . $mask_type . '/' . $hexagon_style . '.svg';
			} elseif ( 'leaf' === $mask_type ) {
				$mask_url = $base_url . $mask_type . '/' . $leaf_style . '.svg';
			} elseif ( 'music' === $mask_type ) {
				$mask_url = $base_url . $mask_type . '/' . $music_style . '.svg';
			} elseif ( 'shield' === $mask_type ) {
				$mask_url = $base_url . $mask_type . '/' . $shield_style . '.svg';
			} elseif ( 'speech' === $mask_type ) {
				$mask_url = $base_url . $mask_type . '/' . $speech_style . '.svg';
			} elseif ( 'splodge' === $mask_type ) {
				$mask_url = $base_url . $mask_type . '/' . $splodge_style . '.svg';
			} elseif ( 'star' === $mask_type ) {
				$mask_url = $base_url . $mask_type . '/' . $star_style . '.svg';
			} elseif ( 'square' === $mask_type ) {
				$mask_url = $base_url . $mask_type . '/' . $square_style . '.svg';
			} else {
				$mask_url = $base_url . $mask_type . '/' . $arrow_style . '.svg';
			}

			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( '-webkit-mask-image: url(%1$s) !important;', esc_attr( $mask_url ) ),
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( '-moz-mask-image: url(%1$s) !important;', esc_attr( $mask_url ) ),
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( '-o-mask-image: url(%1$s) !important;', esc_attr( $mask_url ) ),
				)
			);
			self::set_style(
				$render_slug,
				array(
					'selector'    => '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner',
					'declaration' => sprintf( 'mask-image: url(%1$s) !important;', esc_attr( $mask_url ) ),
				)
			);
		}

		if ( 'image' === $mask_on ) {
			$output = sprintf( '<div class="dipl_image_mask_container"><div class="dipl_image_mask_inner"><img src="%1$s" alt="%3$s"/></div>%2$s</div>', $mask_image, $content, $mask_image_alt );

		} else {
			if ( ! empty( array_filter( $mask_height ) ) ) {
				et_pb_responsive_options()->generate_responsive_css( $mask_height, '%%order_class%% .dipl_image_mask_container .dipl_image_mask_inner', 'height', $render_slug, '!important;', 'type' );
			}

			$args = array(
				'render_slug'	=> $render_slug,
				'props'			=> $this->props,
				'fields'		=> $this->fields_unprocessed,
				'module'		=> $this,
				'backgrounds' 	=> array(
					'mask_background' => array(
						'normal' => "{$this->main_css_element} .dipl_image_mask_inner",
						'hover' => "{$this->main_css_element} .dipl_image_mask_inner:hover",
		 			),
				),
			);

			DiviPlusHelper::process_background( $args );
			
			$output = sprintf( '<div class="dipl_image_mask_container"><div class="dipl_image_mask_inner"></div>%1$s</div>', $content );
		}

		$file = et_is_builder_plugin_active() ? 'style-dbp' : 'style';
        wp_enqueue_style( 'dipl-image-mask-style', PLUGIN_PATH . 'includes/modules/ImageMask/' . $file . '.min.css', array(), '1.0.1' );

		return $output;
	}

	public function add_new_child_text() {
		return esc_html__( 'Add Element', 'divi-plus' );
	}
}

$plugin_options = get_option( ELICUS_DIVI_PLUS_OPTION );
if ( isset( $plugin_options['dipl-modules'] ) ) {
	$modules = explode( ',', $plugin_options['dipl-modules'] );
	if ( in_array( 'dipl_image_mask', $modules ) ) {
		new DIPL_ImageMask();
	}
} else {
	new DIPL_ImageMask();
}
