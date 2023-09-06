<?php

if ( ! class_exists( 'ZAY_Slider_Settings' ) ):

    class ZAY_Slider_Settings{
        public static $options;

        public function __construct(){
            self::$options = get_option( 'zay_slider_options' );
            add_action( 'admin_init', array( $this, 'admin_init' ) );
            // add_action( 'admin_menu', array($this, 'add_custom_post_type_settings') );
        }

        public function admin_init() {
            register_setting( 'zay_slider_group', 'zay_slider_options', array($this, 'zay_slider_validate'));

            add_settings_section(
                'zay_slider_main_section',
                esc_html__( 'How does it work?', 'zay-slider' ),
                null,
                'zay_slider_page1'
            );
            add_settings_field(
                'zay_slider_shortcode',
                esc_html__('Shortcode', 'zay-slider'),
                array($this, 'zay_slider_shortcode_callback'),
                'zay_slider_page1',
                'zay_slider_main_section'
            );
            /*
            add_settings_section(
                'zay_slider_second_section',
                esc_html__('Other Plugin Settings', 'zay-slider'),
                null,
                'zay_slider_page2'
            );
            add_settings_field(
                'zay_slider_slides_to_show',
                esc_html__("Number Of Slides To Show", 'zay-slider'),
                array($this, 'zay_slider_sliders_callback'),
                'zay_slider_page2',
                'zay_slider_second_section',
                array(
                    'label_for' => 'zay_slider_slides_to_show',
                )
            );
            add_settings_field(
                'zay_slider_bullets',
                esc_html__( 'Display Bullets', 'zay-slider' ),
                array( $this, 'zay_slider_bullets_callback' ),
                'zay_slider_page2',
                'zay_slider_second_section',
                array(
                    'label_for' => 'zay_slider_bullets'
                )
            );
            */
        }

        

        public function zay_slider_sliders_callback( $args ) {
            ?>
                <input 
                    type="text" 
                    name="zay_slider_options[zay_slider_slides_to_show]" 
                    id="zay_slider_slides_to_show" 
                    value=" <?php echo isset(self::$options['zay_slider_slides_to_show']) ? esc_attr( self::$options['zay_slider_slides_to_show'] ) : 2 ?> "
                >
            <?php
        }

        public function zay_slider_bullets_callback($args) {
            ?>
                <input 
                    type="checkbox"
                    name="zay_slider_options[zay_slider_bullets]" 
                    id="zay_slider_bullets"
                    value="1"
                    <?php
                        if ( isset( self::$options['zay_slider_bullets'] ) ){
                            checked('1', self::$options['zay_slider_bullets'], true);
                        }
                    ?>
                >
                <label for="zay_slider_bullets"> <?php esc_html_e('Wheter to display bullets or not', 'zay-slider'); ?> </label>
            <?php
        }
        public function zay_slider_shortcode_callback( $args ) {
            ?>
            <span>
                <?php esc_html_e('Use The given Shortcode In custom post type table [zay_slider id="givenID" ] to display the slider in any page/post/widget', 'zay-slider') ?>
            </span>
          <?php
        }

        public function zay_slider_validate( $input ) {
            $new_input = array();
            foreach($input as $key => $value){
                switch($key){
                    case 'zay_slider_slides_to_show':
                        if (!is_numeric($value)){
                            add_settings_error('zay_slider_options', 'zay_slider_message', esc_html__('The Slides Number Field Can Be Nothing Else Except Integer', 'zay-slider'));
                            $value = 2;
                        }
                        $new_input[$key] = sanitize_text_field(intval($value));
                        break;
                    default:
                        $new_input[$key] = sanitize_text_field($value);
                        break;
                }
            }
            return $new_input;
        }
    }



endif;