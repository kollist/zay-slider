<?php

if ( ! class_exists('Zay_Slider_Shortcode') ){

    class Zay_Slider_Shortcode{
        public $cptID;
        public function __construct(){
            add_shortcode('zay_slider', array($this, 'add_special_shortcode'));
        }

        public function add_special_shortcode($atts = array(), $content = null, $tag = ''){
            $atts = array_change_key_case( (array) $atts, CASE_LOWER);
            extract( shortcode_atts(
                array(
                    'id' => '',
                    'orderby' => 'date'
                ),
                $atts,
                $tag
            ));
            if( !empty( $id ) ){
                $id = array_map( 'absint', explode( ',', $id ) );
            }else {
                return 'ID is required!!';
            }
            ob_start();
            require( ZAY_SLIDER_PATH . 'views/zay-slider_shortcode.php');
            wp_enqueue_style( 'zay-slider-main-css' );
            wp_enqueue_style( 'zay-slider-style-css' );
            zay_slider_options();
            return ob_get_clean();
        }
    }
}
