<?php 

if (!function_exists('send_data_to_jquery')):
function send_data_to_jquery_admin() {

wp_enqueue_script('zay-slider-jq', ZAY_SLIDER_URL . 'assets/js/zay-slider-custom-post-type.js' /*, array("wp-color-picker") */ );
    wp_localize_script('zay-slider-jq', 'ZAY_FRONT', array('ajaxurl' => admin_url('admin-ajax.php')));
}
endif;

if (!function_exists('zay_slider_options')):

    function zay_slider_options(){
        $menus = get_posts( array(
            'post_type' => 'zay-slider-menu',
            'posts_per_page' => -1,
        ) );
        $itemsPerSlides = array();
        $showBullets = array();

        foreach($menus as $menu){
            $itemsPerSlides["$menu->ID"] = get_post_meta($menu->ID, '_slides_number', true) ? get_post_meta($menu->ID, '_slides_number', true) : 1;
            $showBullets["$menu->ID"] = get_post_meta($menu->ID, '_show_bullets', true) === 'yes' ? true : false;
        }

        wp_enqueue_script('zay-slider-options-js', ZAY_SLIDER_URL . 'vendor/carousel/script.js', array(), ZAY_SLIDER_VERSION, true);
        wp_localize_script('zay-slider-options-js', 'SLIDER_OPTIONS', array(
            'slidesToShow' => $itemsPerSlides,
            'showBullets' => $showBullets
        ));
    }

endif;

?>