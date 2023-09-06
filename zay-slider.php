<?php

/**
 * Plugin Name: ZAY SLIDER
 * Plugin URI: https://www.wordpress.org/zay-slider
 * Description: My plugin's description
 * Version: 1.0.0
 * Requires at least: 5.6
 * Author: Zaytech
 * Author URI: https://https://zaytech.com/
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: zay-slider
 * Domain Path: /languages
 */

 /*
ZAY Slider is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.
ZAY Slider is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.
You should have received a copy of the GNU General Public License
along with ZAY Slider. If not, see https://www.gnu.org/licenses/gpl-2.0.html.
*/

if( ! defined( 'ABSPATH') ){
    exit;
}


function create_placeholder_image() {
    $default_image = get_option( 'zay_default_image', 0 );

    // Validate current setting if set. If set, return.
    if ( ! empty( $default_image ) ) {
        if ( ! is_numeric( $default_image ) ) {
            return;
        } elseif ( $default_image && wp_attachment_is_image( $default_image ) ) {
            return;
        }
    }

    $upload_dir = wp_upload_dir();
    $source     = untrailingslashit( plugin_dir_path( __FILE__ ) ) . '/assets/images/default.jpeg';
    $filename   = $upload_dir['basedir'] . '/zayslider-default.jpeg';

    if ( ! file_exists( $filename ) ) {
        copy( $source, $filename ); // @codingStandardsIgnoreLine.
    }

    if ( ! file_exists( $filename ) ) {
        update_option( 'zayslider_default_image', 0 );
        return;
    }

    $filetype   = wp_check_filetype( basename( $filename ), null );
    $attachment = array(
        'guid'           => $upload_dir['url'] . '/' . basename( $filename ),
        'post_mime_type' => $filetype['type'],
        'post_title'     => preg_replace( '/\.[^.]+$/', '', basename( $filename ) ),
        'post_content'   => '',
        'post_status'    => 'inherit',
    );

    $attach_id = wp_insert_attachment( $attachment, $filename );
    if ( is_wp_error( $attach_id ) ) {
        update_option( 'zayslider_default_image', 0 );
        return;
    }

    update_option( 'zayslider_default_image', $attach_id );

    // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
    require_once ABSPATH . 'wp-admin/includes/image.php';

    // Generate the metadata for the attachment, and update the database record.
    $attach_data = wp_generate_attachment_metadata( $attach_id, $filename );
    wp_update_attachment_metadata( $attach_id, $attach_data );
}


if( ! class_exists( 'ZAY_Slider' ) ){
    
    class ZAY_Slider{
        public static $default_attachment_id;

        function __construct(){
            $this->define_constants();

            require_once(ZAY_SLIDER_PATH . 'shortcodes/class.zay-slider-shortcode.php');

            require_once( ZAY_SLIDER_PATH . 'functions/functions.php' );

            require_once(ZAY_SLIDER_PATH . 'post-types/class.zay-slider-cpt.php');
            $zay_slider_cpt = new Zay_Slider_Post_Type();

            require_once( ZAY_SLIDER_PATH . 'class.zay-slider-settings.php');
            $MV_Slider_Settings = new ZAY_Slider_Settings();

            // Instantiate the Zay_Slider_Shortcode class when saving the CPT
            $zay_slider_shortcode = new Zay_Slider_Shortcode();

            add_action('admin_menu', array($this, 'add_menu'));
        }

        public function define_constants(){
            define( 'ZAY_SLIDER_PATH', plugin_dir_path( __FILE__ ) );
            define( 'ZAY_SLIDER_URL', plugin_dir_url( __FILE__ ) );
            define( 'ZAY_SLIDER_VERSION', '1.0.0' );
            add_action( 'wp_enqueue_scripts', array($this, 'register_scripts'));
            add_action('admin_enqueue_scripts', array($this, 'register_admin_scripts'));
        }
        public static function activate(){
            update_option( 'rewrite_rules', '' );
            create_placeholder_image();
        }

        public static function deactivate(){
            flush_rewrite_rules();
            unregister_post_type( 'zay-slider' );
        }

        public static function uninstall(){

        }
        public function add_menu() {
            add_menu_page(
                esc_html__( 'Zay Slider Options', 'zay-slider' ),
                'Zay Slider',
                'manage_options',
                'zay_slider_admin',
                array( $this, 'zay_slider_settings_page' ),
                'dashicons-food'
            );

            add_submenu_page(
                'zay_slider_admin',
                esc_html__( 'Add New Menu', 'zay-slider' ),
                esc_html__( 'Add New Menu', 'zay-slider' ),
                'manage_options',
                'post-new.php?post_type=zay-slider-menu',
                null,
                null
            );

            add_submenu_page(
                'zay_slider_admin',
                esc_html__( 'All Slide Menus', 'zay-slider' ),
                esc_html__( 'All Slide Menus', 'zay-slider' ),
                'manage_options',
                'edit.php?post_type=zay-slider-menu',
                null,
                null
            );
            
            add_submenu_page(
                'zay_slider_admin',
                esc_html__( 'All Menus Food', 'zay-slider' ),
                esc_html__( 'All Menus Food', 'zay-slider' ),
                'manage_options',
                'edit.php?post_type=zay-slider-item',
                null,
                null
            );

        }
        public function zay_slider_settings_page() {
            if( ! current_user_can( 'manage_options' ) ){
                return;
            }

            if( isset( $_GET['settings-updated'] ) ){
                add_settings_error( 'zay_slider_options', 'zay_slider_message', esc_html__( 'Settings Saved', 'zay-slider' ), 'success' );
            }
            
            settings_errors( 'zay_slider_options' );

            require( ZAY_SLIDER_PATH . 'views/settings-page.php' );
        }

        public function register_scripts() {
            wp_enqueue_media();
            wp_register_style('zay-slider-main-css', ZAY_SLIDER_URL . 'vendor/carousel/style.css', array(), ZAY_SLIDER_VERSION, 'all');
            wp_register_style('zay-slider-style-css', ZAY_SLIDER_URL . 'assets/css/zay-slider-shortcode.css', array(), ZAY_SLIDER_VERSION, 'all');

        }
        public function register_admin_scripts() {
            global $typenow;
            if ($typenow == 'zay-slider-menu') {
                wp_enqueue_script('jquery');
                wp_enqueue_style('zay-slider-admin-style', ZAY_SLIDER_URL . 'assets/css/zay-slider-custom-post-type-style.css');
                wp_enqueue_script('jquery-ui-sortable');
                wp_enqueue_script("jquery-ui-dialog");
                // wp_enqueue_style( 'wp-color-picker' );
            }
        }

    }
}

if( class_exists( 'ZAY_Slider' ) ){
    register_activation_hook( __FILE__, array( 'ZAY_SLIDER', 'activate' ) );
    register_deactivation_hook( __FILE__, array( 'ZAY_SLIDER', 'deactivate' ) );
    register_uninstall_hook( __FILE__, array( 'ZAY_SLIDER', 'uninstall' ) );

    $zay_slider = new ZAY_SLIDER();
} 
