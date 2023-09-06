<?php 

if ( !class_exists( 'Zay_Slider_Post_Type')) {

    
    class Zay_Slider_Post_Type {
        // public $post_id;
        function __construct(){
            add_action('init', array($this, 'create_post_type'));
            add_action( 'add_meta_boxes', array($this, 'add_meta_boxes'), 10, 2 );
            add_action( 'save_post', array( $this, 'save_post' ));
            add_action('wp_ajax_submit_cpt', array($this, 'submit_cpt'));
            add_filter('manage_zay-slider-item_posts_columns', array($this, 'zay_slider_cpt_items_columns'));
            add_filter('manage_zay-slider-menu_posts_columns', array($this, 'zay_slider_cpt_menu_columns'));
            add_action('manage_zay-slider-item_posts_custom_column', array($this, 'zay_slider_custom_items_columns'), 10, 2);
            add_action('manage_zay-slider-menu_posts_custom_column', array($this, 'zay_slider_custom_menu_columns'), 10, 2);
            add_action('wp_ajax_save_sortable_data', array($this, 'save_sortable_data'));
            add_action('wp_ajax_delete_item_post', array($this, 'delete_item_post'));
            add_action('wp_ajax_edit_item_post', array($this, 'edit_item_post'));
            add_action('before_delete_post', array($this, 'before_delete_handler'), 10, 2);
            add_filter( 'post_row_actions', array($this, 'modify_menu_row_actions'), 10, 2 );
        }

        public function modify_menu_row_actions( $actions, $post ) {
            if ( $post->post_type === 'zay-slider-menu' ) {
                // Add your custom link as a new action
                $post_type = get_post_type( $post->ID );
                $settings_page_url = admin_url( 'admin.php?page=' . $post_type . '-settings-' . $post->ID );
                $actions['settings'] = '<a href="'. $settings_page_url .'">'. __("Settings", 'zay-slider') .'</a>';
            }
        
            return $actions;
        }

        public function save_sortable_data() {
            if (!isset($_POST['order'])):
                wp_send_json_error(array(
                    'message' => _("Something Went Wrong", 'zay-slider')
                ));
            endif;
            $order = $_POST['order'];
            $count = 1;
            foreach ($order as $post_id) {
                $post = array(
                    'ID' => intval($post_id),
                    'menu_order' => intval($count)
                );
                wp_update_post($post);
                $count++;
            }

            wp_send_json_success(array(
                'message' => __('Order updated succefully', 'zay-slider')
            ));
        
            wp_die();
        }
        public function create_post_type(){
            register_post_type(
                'zay-slider-menu',
                array(
                    'label' => esc_html__('Food Menu Slider', 'zay-slider'),
                    'description' => esc_html__("Food Menu Sliders", 'zay-slider'),
                    'labels' => array(
                        'name' => esc_html__('Food Menu Slider', 'zay-slider'),
                        'add_new' => esc_html__('Add New Menu', 'zay-slider'),
                        'add_new_item' => esc_html__('Add New Menu', 'zay-slider')

                    ),
                    'public' => true,
                    'show_in_menu' => false,
                    'publicly_querable' => false,   
                    'has_archive' => false                 
                ),
            );
            register_post_type(
                'zay-slider-item',
                array(
                    'label' => esc_html__('Item Slider', 'zay-slider'),
                    'description' => esc_html__("Item Sliders", 'zay-slider'),
                    'labels' => array(
                        'name' => esc_html__('Item Slider', 'zay-slider'),
                        'add_new' => esc_html__('Add New Item', 'zay-slider'),
                        'add_new_item' => esc_html__('Add New Item', 'zay-slider')
                    ),
                    'public' => true,
                    'show_in_menu' => false,
                    'supports' => array('title', 'editor', 'thumbnail', 'author', 'revisions'),
                    'capabilities' => array(
                        'create_posts' => 'do_not_allow',
                        'edit_post' => true,
                        'delete_post' => true,
                    ),
                    'publicly_querable' => false
                ),
            );
            
        }
        public function zay_slider_cpt_items_columns( $columns ){
            $columns = array('cb' => $columns['cb'], 'image' => __('Image', 'zay-slider'), 'title' => 'Title', 'parent_id' => "Parent ID") + $columns;
            return $columns;
        }
        public function before_delete_handler($post_id, $post) {
            if ($post->post_type === "zay-slider-item") {
                return;
            }
        
            $post_items = new WP_Query(array(
                'post_type' => 'zay-slider-item',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'meta_query' => array(
                    array(
                        'key' => 'zay_slider_item_parent',
                        'value' => $post_id,
                        'compare' => '=',  // Replace '=' with the desired comparison operator
                    ),
                ),
            ));
        
            if ($post_items->have_posts()) {
                while ($post_items->have_posts()) {
                    $post_items->the_post();
                    wp_delete_post(get_the_ID(), true);
                }
            }
        }
        public function zay_slider_cpt_menu_columns( $columns) {
            $columns = array('cb' => $columns['cb'], 'title' => $columns['title'], 'shortcode' => __('ShortCode', 'zay-slider')) + $columns;
            return $columns;
        }
        public function zay_slider_custom_items_columns($column, $post_id){
            switch( $column ){
                case 'image':
                    $thumbnail = get_the_post_thumbnail($post_id, array(60, 60));
                    if (!has_post_thumbnail($post_id)){
                        echo '<a href="'. get_preview_post_link($post_id) . '">' . wp_get_attachment_image(get_option('zayslider_default_image'), array(60, 60)) . '</a>';
                    }
                    echo '<a href="'. get_preview_post_link($post_id) . '">' . $thumbnail . '</a>';
                    break;
                case 'parent_id':
                    echo '<a href="'. get_edit_post_link(get_post_meta($post_id, 'zay_slider_item_parent', true)) .'">'. get_post_meta($post_id, 'zay_slider_item_parent', true) .'</a>';
            }
        }
        public function zay_slider_custom_menu_columns($columns, $post_id){
            switch($columns){
                case 'shortcode':
                     echo '[zay_slider id="'.$post_id.'" ]';
                     break;
            }
        }
        public function add_meta_boxes($post_type, $post) {
            add_meta_box(
                'zay_slider_menu_meta_box',
                esc_html__('Add Items To Your Menu', 'zay-slider'),
                array($this, 'add_menu_meta_boxes'),
                'zay-slider-menu',
                'normal',
                'high'
            );
            add_meta_box(
                'zay_slider_item_meta_box',
                esc_html__('Add Item Price', 'zay-slider'),
                array($this, 'add_item_meta_boxes'),
                'zay-slider-item',
                'normal',
                'high'
            );

            add_meta_box(
                'zay_slider_settings_meta_box',
                esc_html__('Slider Slides Settings', 'zay-slider'),
                function () use ($post){
                    $this->add_settings_meta_box($post);
                },
                'zay-slider-menu',
                'normal'
            );
            
        }


        public function add_settings_meta_box( $post ) {

            $post_items = new WP_Query(array(
                'post_type' => 'zay-slider-item',
                'posts_per_page' => -1,
                'meta_query' => array(array(
                    'key' => 'zay_slider_item_parent',
                    'value' => $post->ID,
                    'compare' => '='
                )),
            ));
            $slidesNumber = get_post_meta($post->ID, '_slides_number', true);
            $show_bullets = get_post_meta($post->ID, '_show_bullets', true);
            ?>
                <table class="slides-meta">
                    <tr class="slides">
                        <td>
                            <label for="_slides_number"> <?php _e('Number of slides: ', 'zay-slider') ?> </label>
                        </td>
                        <td>
                            <select name="_slides_number" id="_slides_number">
                                <optgroup label="Number Of Slides">
                                    <?php
                                        if ($post_items->have_posts()):
                                            $nbr = 1;
                                            while($post_items->have_posts()):
                                                $post_items->the_post();
                                                ?>
                                                <option value="<?php echo $nbr ?>" <?php selected($slidesNumber ? $slidesNumber : 1, $nbr, true) ?> > <?php echo $nbr ?> </option>
                                                <?php
                                                $nbr++;
                                            endwhile;
                                        endif;
                                    ?>
                                </optgroup>
                            </select>
                        </td>
                    </tr>
                    <tr class="bullets">
                        <td><label for="_show_bullets"> <?php _e('Show Bullets', 'zay-slider') ?> </label></td>
                        <td><input type="checkbox" name="_show_bullets" id="_show_bullets" value="yes" <?php checked('yes', $show_bullets, true) ?> ></td>
                    </tr>
                    <!-- <tr class="bgcolor">
                        <td>
                            <label for="bgcolor"> <?php _e("Slider Background Color", 'zay-slider') ?> </label>
                        </td>
                        <td>
                            <input type="text" value="#bada55" class="bgcolor_field" data-default-color="#effeff" >
                        </td>
                    </tr> -->
                </table>
                    <?php
        }
        

        public function save_post( $post_id ){

            if ( isset( $_POST['zay_slider_nonce'] )){
                if ( !wp_verify_nonce( $_POST['zay_slider_nonce'], 'zay_slider_nonce' )){
                    return;
                }
            }
            if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE){
                return;
            }
            if ( isset($_POST['post_type']) && $_POST['post_type'] === 'zay-slider-menu'){
                if( ! current_user_can( 'edit_page', $post_id ) ){
                    return;
                }elseif( ! current_user_can( 'edit_post', $post_id ) ){
                    return;
                }

                $new_slides_number = $_POST['_slides_number'];
                $old_slides_number = get_post_meta($post_id, '_slides_number', true);
                $show_bullets_new = $_POST['_show_bullets'];
                $show_bullets_old = get_post_meta($post_id, '_show_bullets', true);
                update_post_meta($post_id, '_slides_number', $new_slides_number, $old_slides_number);
                update_post_meta($post_id, '_show_bullets', $show_bullets_new, $show_bullets_old);
            }
            if ( isset($_POST['post_type']) && $_POST['post_type'] === 'zay-slider-item'){

                if( ! current_user_can( 'edit_post', $post_id ) ){
                    return;
                }
                if ( isset($_POST['action']) && $_POST['action'] == 'editpost'){
                    $new_price_amount = $_POST['zay_slider_item_price'];
                    $old_price_amount = get_post_meta($post_id, 'zay_slider_item_price', true);
                    $new_price_name = $_POST['zay_slider_item_price_name'];
                    $old_price_name = get_post_meta($post_id, "zay_slider_item_price_name", true);
                    update_post_meta($post_id, 'zay_slider_item_price', $new_price_amount, $old_price_amount);
                    update_post_meta($post_id, 'zay_slider_item_price_name', $new_price_name, $old_price_name);
                }
            }
            
        } 


        
        public function edit_item_post() {
                
            if (isset($_POST['itemEditedSecurity'])){
                if ( !wp_verify_nonce($_POST['itemEditedSecurity'], 'edit_item_nonce')){
                    wp_send_json_error(array(
                        'message' => __('Something Wrong Happened', 'zay-slider'),
                    ));
                    wp_die();
                }
            }
            $post_data = array(
                'ID' => intval(sanitize_text_field($_POST['itemEditedID'])),
                'post_title' => sanitize_text_field($_POST['itemEditedTitle']),
                'post_content' => $_POST['itemEditedContent'],
                'post_type' => sanitize_text_field($_POST['itemEditedPosttype']),
                'post_status' => 'publish'
            );

            ob_start();
            wp_update_post($post_data);

            if (!ob_get_clean()){
                wp_send_json_error(array(
                    'message' =>  __('Something Wrong Happened', 'zay-slider'),
                ));
            }

            if ($_POST['itemEditedThumbnail']){
                $new_thumbnail_id = attachment_url_to_postid($_POST['itemEditedThumbnail']);
                update_post_meta(intval(sanitize_text_field($_POST['itemEditedID'])), '_thumbnail_id', $new_thumbnail_id);
            }
            if ($_POST['itemEditedPriceAmount'] && $_POST['itemEditedPriceName']){
                update_post_meta(intval(sanitize_text_field($_POST['itemEditedID'])), 'zay_slider_item_price', $_POST['itemEditedPriceAmount'] );
                update_post_meta(intval(sanitize_text_field($_POST['itemEditedID'])), 'zay_slider_item_price_name', $_POST['itemEditedPriceName']);
                update_post_meta(intval(sanitize_text_field($_POST['itemEditedID'])), 'zay_slider_item_parent', $_POST['itemEditedParent_ID']);
            }

            wp_send_json_success(array(
                'message' => __('Post Have Been Updated Succefully', 'zay-slider'),
            ));


            
        }
        public function submit_cpt() {
            if (isset($_POST['itemSecurity'])){
                if ( !wp_verify_nonce($_POST['itemSecurity'], 'zay_slider_nonce') ){
                    wp_send_json(array(
                        'message' => __("Something Wrong Happened"),
                    ));
                    wp_die();
                }
            }
            $post_data = array(
                'post_title' => $_POST['itemTitle'],
                'post_content' => $_POST['itemContent'],
                'post_type' => $_POST['itemPosttype'],
                'post_status' => 'publish'
            );
            
            if (!isset( $_POST['itemParent_ID'])){
                wp_send_json(array(
                    'message' => 'something went wrong'
                ));
                wp_die();
            }
            $parent = get_post($_POST['itemParent_ID']);
            if (!$parent){
                wp_send_json(array(
                    'message' => 'something went wrong'
                ));
                wp_die();
            }
            $item_id = wp_insert_post($post_data);
            if (!$item_id){
                $response = array(
                    'success' => false,
                    'message' => 'Failed to save post.'
                );
            }
            
            if (isset($_POST['itemThumbnail'])){
                $attachement_id = attachment_url_to_postid($_POST['itemThumbnail']);
                if ($attachement_id){
                    set_post_thumbnail($item_id, $attachement_id);
                }
            }

            if (isset($_POST['itemPriceAmount']) || isset($_POST['itemPriceName']) && isset($_POST['itemParent_ID'])){
                $new_price_amount = $_POST['itemPriceAmount'];
                $old_price_amount = get_post_meta($item_id, 'zay_slider_item_price', true);
                $new_price_name = $_POST['itemPriceName'];
                $old_price_name = get_post_meta($item_id, "zay_slider_item_price_name", true);
                $new_parent_id = $_POST['itemParent_ID'];
                $old_parent_id = get_post_meta($item_id, 'zay_slider_item_parent', true);
                update_post_meta($item_id, 'zay_slider_item_price', $new_price_amount, $old_price_amount);
                update_post_meta($item_id, 'zay_slider_item_price_name', $new_price_name, $old_price_name);
                update_post_meta($item_id, 'zay_slider_item_parent', $new_parent_id, $old_parent_id);
            }
            if ($response){
                wp_send_json($response);
            }else {
                wp_send_json([
                    'status' => 'success',
                    'message' => 'Post got saved successfully' 
                ]);
            }
            wp_die();
        }
        public function add_menu_meta_boxes( $post ) {
            require_once( ZAY_SLIDER_PATH . 'views/zay-slider-food-metabox.php');
            send_data_to_jquery_admin();
        }
        public function add_item_meta_boxes( $post ) {
            $price_amount = get_post_meta($post->ID, 'zay_slider_item_price', true);
            $price_name = get_post_meta($post->ID, 'zay_slider_item_price_name', true);
            $parent_id = get_post_meta($post->ID, 'zay_slider_item_parent', true);
            ?>
                <div class="item-price" style="display: flex; justify-content: space-between; align-items: center;">
                    <div id="priceInputs">
                        <input 
                            id="zay_slider_item_price" 
                            class="priceAmount" 
                            name="zay_slider_item_price" 
                            placeholder="<?php _e('Price', 'zay-slider'); ?>" 
                            value="<?php isset($price_amount) ? _e( esc_html( $price_amount)) : '' ?>"
                        >
                        <input value="<?php isset($price_name) ? _e( esc_html( $price_name)) : '' ?>" id="zay_slider_item_price_name" name="zay_slider_item_price_name" class="priceName" placeholder="<?php _e('Title', 'zay-slider'); ?>" >
                    </div>
                    <div class="item-parent">
                        <fieldset style="">
                            <legend><?php _e('PARENT ID: ', 'zay-slider') ?></legend>
                            <input readonly type="text" name="zay_slider_item_parent" id="zay_slider_item_parent" value="<?php echo esc_html($parent_id) ?>">
                        </fieldset>
                    </div>
                </div>
            <?php
        }
        public function delete_item_post() {
            if (isset($_POST['security'])){
                if (!wp_verify_nonce($_POST['security'], 'delete_item_nonce')) {
                    wp_send_json_error("Permission denied.");
                }
            }
            
            if (isset($_POST['post_type']) && $_POST['post_type'] === 'zay-slider-item') {
                if (!current_user_can('delete_post', $_POST['postID'])) {
                    wp_send_json_error("Permission denied.");
                }
            }
            
            $deleted = wp_delete_post((int)$_POST['postID']);
            
            if ($deleted === false) {
                wp_send_json_error("Failed to delete the post.");
            }
        
            wp_send_json(array(
                'successStatus' => true,
                'dataMessage' => 'post deleted successfully.',
            ));
        }
    }
}