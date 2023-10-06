<?php 

if ( !class_exists( 'Zay_Slider_Post_Type')) {

    
    class Zay_Slider_Post_Type {
        // public $post_id;
        function __construct(){
            add_action('init', array($this, 'create_post_type'));
            add_action( 'add_meta_boxes', array($this, 'add_meta_boxes'));
            add_action( 'save_post', array( $this, 'save_post' ));
            add_action('wp_ajax_submit_cpt', array($this, 'submit_cpt'));
            add_action("wp_ajax_get_post_by_id", array($this, 'get_post_by_id'));
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
        
        public function get_post_by_id(){
            if (isset($_POST["security"]) ){
                if ( !wp_verify_nonce( $_POST['security'], 'zay_post_nonce' )){
                    return wp_send_json_error(array( "message" => "The Nonce is not from wordpress"));
                }
            }
            if (!isset($_POST["id"])){
                return wp_send_json_error(array( "message" => "item ID is required"));
            }

            $id = sanitize_key($_POST["id"]);


            $post = get_post($id);
            $post->_hide_title = get_post_meta($id, "_hide_title", true);
            $post->_hide_image = get_post_meta($id, "_hide_image", true);
            $post->_hide_price = get_post_meta($id, "_hide_price", true);
            $post->_hide_name = get_post_meta($id, "_hide_name", true);
            $post->_title_style = get_post_meta($id, "_title_style", true);
            $post->_author_style = get_post_meta($id, "_author_style", true);
            $post->_price_style = get_post_meta($id, "_price_style", true);
            $post->_image_style = get_post_meta($id, "_image_style", true);
            $post->_description_style = get_post_meta($id, "_description_style", true);
            $post->_slide_custom_classes = get_post_meta($id, "_slide_custom_classes", true);
            $post->_slide_custom_id = get_post_meta($id, "_slide_custom_id", true);
            $post->thumbnail = get_the_post_thumbnail_url($post) ? get_the_post_thumbnail_url($post) : wp_get_attachment_image_url(get_option('zayslider_default_image'));
            $post->price = get_post_meta($id, "zay_slider_item_price", true);
            $post->price_title = get_post_meta($id, "zay_slider_item_price_name", true);
            $post->creator_name = get_post_meta($id, "zay_slider_item_creator_name", true);
            $post->card_layout = get_post_meta($id, "_card_layout", true);

            return wp_send_json_success($post);

        }

        public function modify_menu_row_actions( $actions, $post ) {
            if ( $post->post_type === 'zay-slider-menu' ) {
                // Add your custom link as a new action
                $post_type = get_post_type( $post->ID );
                $settings_page_url = get_edit_post_link($post->ID);
                $actions['settings'] = '<a id="settings-link" href="'. $settings_page_url.'&scroll_to=zay_slider_settings_meta_box">'. __("Settings", 'zay-slider') .'</a>';
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
                    'label' => esc_html__('Slider', 'zay-slider'),
                    'description' => esc_html__("Sliders", 'zay-slider'),
                    'labels' => array(
                        'name' => esc_html__('Slider', 'zay-slider'),
                        'add_new' => esc_html__('Add New Slider', 'zay-slider'),
                        'add_new_item' => esc_html__('Add New Slider', 'zay-slider')

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
        public function add_meta_boxes() {
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
                esc_html__('Items Additional Data', 'zay-slider'),
                array($this, 'add_item_meta_boxes'),
                'zay-slider-item',
                'normal',
                'high'
            );

            add_meta_box(
                'zay_slider_settings_meta_box',
                esc_html__('Slider Slides Settings', 'zay-slider'),
                array($this, 'add_settings_meta_box'),
                'zay-slider-menu',
                'normal'
            );
            
            add_meta_box(
                'zay_slider_item_meta_displayer',
                esc_html__("Optional dispalying", 'zay-slider'),
                array($this, "meta_displayer"),
                'zay-slider-item',
                'normal',
                'high'
            );
            add_meta_box(
                "zay_slider_item_custom_css",
                esc_html("Slide Custom CSS", 'zay-slider'),
                array($this, "item_custom_css"),
                'zay-slider-item',
                'normal', 
                'high'
            );
            add_meta_box(
                'zay_slider_custom_attributes',
                esc_html("Custom CSS Attributes", 'zay-slider'),
                array($this, "item_css_attributes"),
                'zay-slider-item',
                'normal',
                'high'
            );
            add_meta_box(
                'zay_slider_custom_layouts',
                esc_html("Custom Card Layouts", "zay-slider"),
                array($this, "custom_layouts"),
                'zay-slider-item',
                'normal'
            );
        }
        public function custom_layouts($post){
            $custom_layouts = get_post_meta($post->ID, "_card_layout", true);
            ?>
            <table class="custom-layouts">
                <tr>
                    <td>
                        <label for="default">Default Layout</label>
                    </td>
                    <td>
                        <input value="default" <?php checked("default", $custom_layouts, true) ?> type="radio" name="layout" id="default">
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="background_image">Image As Background Layout</label>
                    </td>
                    <td>
                        <input value="background-image-card-layout" <?php checked("background-image-card-layout", $custom_layouts, true) ?> id="background_image" name="layout" type="radio"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="comment_layout">Comment Card Layout</label>
                    </td>
                    <td>
                        <input value="comment-card-layout" <?php checked("comment-card-layout", $custom_layouts, true) ?> id="comment_layout" name="layout" type="radio"/>
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="menu_card">Menu Card Layout</label>
                    </td>
                    <td>
                        <input value="menu-card-layout" <?php checked("menu-card-layout", $custom_layouts, true) ?> id="menu_card" name="layout" type="radio"/>
                    </td>
                </tr>
            </table>
            <?php
        } 

        public function item_css_attributes($post) { 
            $custom_classes = get_post_meta($post->ID, "_slide_custom_classes", true);
            $custom_id = get_post_meta($post->ID, "_slide_custom_id", true);
            ?>
            <div>
                <div class="css-attributes css-id">
                    <label for="_slide_custom_id">
                        CSS ID
                    </label>
                    <input type="text" id="_slide_custom_id" name="_slide_custom_id" value="<?php echo isset($custom_classes) ? $custom_classes : "" ?>">
                </div>
                <div class="css-attributes css-class">
                    <label for="_slide_custom_classes">
                        CSS Class
                    </label>
                    <input type="text" id="_slide_custom_classes" name="_slide_custom_classes" value="<?php echo isset($custom_id) ? $custom_id : "" ?>">
                </div>
            </div>
            <?php
        }
        public function item_custom_css($post) {
            $custom_title_css = get_post_meta(get_the_ID(), "_title_style", true);
            $custom_image_css = get_post_meta(get_the_ID(), "_image_style", true);
            $custom_price_css = get_post_meta(get_the_ID(), "_price_style", true);
            $custom_name_css = get_post_meta(get_the_ID(), "_author_style", true);
            $custom_description_css = get_post_meta(get_the_ID(), "_description_style", true);
            ?>
                <div class="custom-css">
                    <div class="style title">
                        <label for="_title_style">Title</label>
                        <textarea name="_title_style" id="_title_style" cols="50" rows="10"><?php echo isset($custom_title_css) ? $custom_title_css : ""  ?></textarea>
                    </div>
                    <div class="style image">
                        <label for="_image_style">Image</label>
                        <textarea name="_image_style" id="_image_style" cols="50" rows="10"><?php echo isset($custom_image_css) ? $custom_image_css : ""  ?></textarea>
                    </div>
                    <div class="style price">
                        <label for="_price_style">Price</label>
                        <textarea name="_price_style" id="_price_style" cols="50" rows="10"><?php echo isset($custom_price_css) ? $custom_price_css : ""  ?></textarea>
                    </div>
                    <div class="style description">
                        <label for="_description_style">Description</label>
                        <textarea name="_description_style" id="_description_style" cols="50" rows="10"><?php echo isset($custom_description_css) ? $custom_description_css : ""  ?></textarea>
                    </div>
                    <div class="style author">
                        <label for="_author_style">Author</label>
                        <textarea name="_author_style" id="_author_style" cols="50" rows="10"><?php echo isset($custom_name_css) ? $custom_name_css : ""  ?></textarea>
                    </div>
                </div>
            <?php
        }
        public function meta_displayer($post) {
            $hide_title = get_post_meta($post->ID, "_hide_title", true);
            $hide_image = get_post_meta($post->ID, "_hide_image", true);
            $hide_price = get_post_meta($post->ID, "_hide_price", true);
            $hide_name = get_post_meta($post->ID, "_hide_name", true);
            ?>
            <table class="data-optional-displayer">
                <tr>
                    <td>
                        <label for="_hide_title"> <?php _e("Hide Title", 'zay-slider') ?> </label>
                    </td>
                    <td>
                        <input type="checkbox" name="_hide_title" id="_hide_title" value="1" <?php checked("1", $hide_title, true) ?> >
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="_hide_image"> <?php _e("Hide image", 'zay-slider') ?> </label>
                    </td>
                    <td>
                        <input type="checkbox" name="_hide_image" id="_hide_image" value="1" <?php checked("1", $hide_image, true) ?> >
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="_hide_price"> <?php _e("Hide Price", 'zay-slider') ?> </label>
                    </td>
                    <td>
                        <input type="checkbox" name="_hide_price" id="_hide_price" value="1" <?php checked("1", $hide_price, true) ?> >
                    </td>
                </tr>
                <tr>
                    <td>
                        <label for="_hide_name"> <?php _e("Hide Name", 'zay-slider') ?> </label>
                    </td>
                    <td>
                        <input type="checkbox" name="_hide_name" id="_hide_name" value="1" <?php checked("1", $hide_name, true) ?> >
                    </td>
                </tr>
            </table>

            <?php 
            
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
                update_post_meta($post_id, '_slides_number', sanitize_key($new_slides_number), $old_slides_number);
                update_post_meta($post_id, '_show_bullets', sanitize_key($show_bullets_new), $show_bullets_old);
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
                    $new_crearor_name = $_POST["zay_slider_item_creator_name"];
                    $old_creator_name = get_post_meta($post_id, "zay_slider_item_creator_name", true);

                    $hide_title_old = get_post_meta($post_id, "_hide_title", true);
                    $hide_image_old = get_post_meta($post_id, "_hide_image", true);
                    $hide_price_old = get_post_meta($post_id, "_hide_price", true);
                    $hide_name_old = get_post_meta($post_id, "_hide_name", true);

                    $hide_title = wp_kses($_POST[ "_hide_title"]);
                    $hide_image = wp_kses($_POST["_hide_image"  ]);
                    $hide_name = wp_kses($_POST["_hide_name"]);
                    $hide_price = wp_kses($_POST["_hide_price"]);

                    $slide_custom_classes = sanitize_html_class($_POST["_slide_custom_classes"]);
                    $slide_custom_id = sanitize_html_class($_POST["_slide_custom_id"]);

                    $old_custom_classes = get_post_meta($post_id, "_slide_custom_classes", true);
                    $old_custom_id = get_post_meta($post_id, "_slide_custom_id", true);

                    $custom_title_css = $_POST["_title_style"];
                    $custom_name_css = $_POST["_author_style"];
                    $custom_price_css = $_POST["_price_style"];
                    $custom_image_css = $_POST["_image_style"];
                    $custom_description_css = $_POST["_description_style"];
                    
                    $card_layout = sanitize_key($_POST["layout"]);
                    $old_card_layout = get_post_meta($post_id, "_card_layout", true);

                    update_post_meta($post_id, "_card_layout", $card_layout, $old_card_layout);

                    $old_custom_title_css = get_post_meta($post_id, "_title_style", true);
                    $old_custom_name_css = get_post_meta($post_id, "_author_style", true);
                    $old_custom_price_css = get_post_meta($post_id, "_price_style", true);
                    $old_custom_image_css = get_post_meta($post_id, "_image_style", true);
                    $old_custom_description_css = get_post_meta($post_id, "_description_style", true);

                    

                    update_post_meta($post_id, "_slide_custom_classes", $slide_custom_classes, $old_custom_classes);
                    update_post_meta($post_id, "_slide_custom_id", $slide_custom_id, $old_custom_id);

                    update_post_meta($post_id, "_title_style", wp_kses_post($custom_title_css), $old_custom_title_css);
                    update_post_meta($post_id, "_author_style", wp_kses_post($custom_name_css), $old_custom_name_css);
                    update_post_meta($post_id, "_price_style", wp_kses_post($custom_price_css), $old_custom_price_css);
                    update_post_meta($post_id, "_image_style", wp_kses_post($custom_image_css), $old_custom_image_css);
                    update_post_meta($post_id, "_description_style", wp_kses_post($custom_description_css), $old_custom_description_css);

                    update_post_meta($post_id, "_hide_title", sanitize_text_field($hide_title), $hide_title_old);
                    update_post_meta($post_id, "_hide_image", sanitize_text_field($hide_image), $hide_image_old);
                    update_post_meta($post_id, "_hide_name", sanitize_text_field($hide_name), $hide_name_old);
                    update_post_meta($post_id, "_hide_price", sanitize_text_field($hide_price), $hide_price_old);

                    update_post_meta($post_id, 'zay_slider_item_price', sanitize_text_field($new_price_amount), $old_price_amount);
                    update_post_meta($post_id, 'zay_slider_item_price_name', sanitize_text_field($new_price_name), $old_price_name);

                    update_post_meta($post_id, 'zay_slider_item_creator_name', sanitize_text_field($new_crearor_name), $old_creator_name);
                }
            }
            
        }
        public function edit_item_post() {
                
            if (isset($_POST['itemEditedSecurity'])){
                if ( !wp_verify_nonce($_POST['itemEditedSecurity'], 'edit_item_nonce')){
                    wp_send_json_error(array(
                        'message' => __('Something Wrong Happened 1', 'zay-slider'),
                    ));
                    wp_die();
                }
            }
            $post_data = array(
                'ID' => intval(sanitize_key($_POST['itemEditedID'])),
                'post_title' => sanitize_text_field($_POST['itemEditedTitle']),
                'post_content' => sanitize_text_field( $_POST['itemEditedContent']),
                'post_type' => sanitize_text_field($_POST['itemEditedPosttype']),
                'post_status' => 'publish'
            );
            $postID = wp_update_post($post_data);

            if (!$postID){
                wp_send_json_error(array(
                    'message' =>  __('Something Wrong Happened 2', 'zay-slider')
                ));
            }

            if ($_POST['itemEditedThumbnail']){
                $new_thumbnail_id = attachment_url_to_postid(esc_url($_POST['itemEditedThumbnail']));
                update_post_meta(intval(sanitize_text_field($_POST['itemEditedID'])), '_thumbnail_id', $new_thumbnail_id);
            }
            if (isset($_POST['itemEditedPriceAmount']) && isset($_POST['itemEditedPriceName'])){
                update_post_meta($postID, 'zay_slider_item_price', sanitize_text_field($_POST['itemEditedPriceAmount']) );
                update_post_meta($postID, 'zay_slider_item_price_name', sanitize_text_field($_POST['itemEditedPriceName']));
                update_post_meta($postID, 'zay_slider_item_parent', sanitize_text_field($_POST['itemEditedParent_ID']));
                update_post_meta($postID, 'zay_slider_item_creator_name', sanitize_text_field($_POST["itemEditedCreatorName"]));
            }
            $hide_title = sanitize_key($_POST["itemEditedHideTitle"]);
            $hide_image = sanitize_key($_POST["itemEditedHideImage"]);
            $hide_name = sanitize_key($_POST["itemEditedHideName"]);
            $hide_price = sanitize_key($_POST["itemEditedHidePrice"]);

            $_title_style = $_POST["_title_style"];
            $_author_style = $_POST["_author_style"];
            $_price_style = $_POST["_price_style"];
            $_image_style = $_POST["_image_style"];
            $_description_style = $_POST["_description_style"];
            $custom_class = sanitize_html_class($_POST["_slide_custom_classes"]);
            $custom_id = sanitize_html_class($_POST["_slide_custom_id"]);

            $_card_layout = sanitize_key($_POST["_card_layout"]);
            $_old_card_layout = get_post_meta($postID, "_card_layout", true);

            update_post_meta($postID, "_card_layout", $_card_layout, $_old_card_layout);

            update_post_meta($postID, "_hide_title", $hide_title);
            update_post_meta($postID, "_hide_image", $hide_image);
            update_post_meta($postID, "_hide_name", $hide_name);
            update_post_meta($postID, "_hide_price", $hide_price);

            update_post_meta($postID, "_title_style", $_title_style);
            update_post_meta($postID, "_author_style", $_author_style);
            update_post_meta($postID, "_price_style", $_price_style);
            update_post_meta($postID, "_image_style", $_image_style);
            update_post_meta($postID, "_description_style", $_description_style);
            update_post_meta($postID, "_slide_custom_classes", $custom_class);
            update_post_meta($postID, "_slide_custom_id", $custom_id);

            wp_send_json_success(array(
                'message' => __('Post Have Been Updated Succefully', 'zay-slider'),
                'data' => array(
                    "hide_title" => $hide_title,
                    "hide_image" => $hide_image,
                    "hide_price" => $hide_price,
                    "hide_name" => $hide_name
                ),
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
                $new_creator_name = $_POST['creatorName'];
                $old_creator_name = get_post_meta($item_id, 'zay_slider_item_creator_name', true);
                $hide_title = $_POST[ "_hide_title"];
                $hide_title_old = get_post_meta($item_id, "_hide_title", true);
                $hide_image = $_POST["_hide_image"  ];
                $hide_image_old = get_post_meta($item_id, "_hide_image", true);
                $hide_price = $_POST["_hide_price"];
                $hide_price_old = get_post_meta($item_id, "_hide_price", true);
                $hide_name = $_POST["_hide_name"];
                $hide_name_old = get_post_meta($item_id, "_hide_name", true);
                $_title_style = $_POST["_title_style"];
                $_author_style = $_POST["_author_style"];
                $_price_style = $_POST["_price_style"];
                $_image_style = $_POST["_image_style"];
                $_description_style = $_POST["_description_style"];
                $custom_class = $_POST["_slide_custom_classes"];
                $custom_id = $_POST["_slide_custom_id"];
                $_old_title_style = get_post_meta($item_id, "_title_style", true);
                $_old_author_style = get_post_meta($item_id, "_author_style", true);
                $_old_price_style = get_post_meta($item_id, "_price_style", true);
                $_old_image_style = get_post_meta($item_id, "_image_style", true);
                $_old_description_style = get_post_meta($item_id, "_description_style", true);
                $_old_custom_classes = get_post_meta($item_id, "_slide_custom_classes", true);
                $_old_custom_id = get_post_meta($item_id, "_slide_custom_id", true);
                $_card_layout = $_POST["_card_layout"];
                $_old_card_layout = get_post_meta($item_id, "_card_layout", true);
                update_post_meta($item_id, "_card_layout", $_card_layout, $_old_card_layout);
                update_post_meta($item_id, 'zay_slider_item_price', $new_price_amount, $old_price_amount);
                update_post_meta($item_id, 'zay_slider_item_price_name', $new_price_name, $old_price_name);
                update_post_meta($item_id, 'zay_slider_item_parent', $new_parent_id, $old_parent_id);
                update_post_meta($item_id, 'zay_slider_item_creator_name', $new_creator_name, $old_creator_name);
                update_post_meta($item_id, "_hide_title", $hide_title, $hide_title_old);
                update_post_meta($item_id, "_hide_image", $hide_image, $hide_image_old);
                update_post_meta($item_id, "_hide_price", $hide_price, $hide_price_old);
                update_post_meta($item_id, "_hide_name", $hide_name, $hide_name_old);
                update_post_meta($item_id, "_title_style", $_title_style, $_old_title_style);
                update_post_meta($item_id, "_author_style", $_author_style , $_old_author_style);
                update_post_meta($item_id, "_price_style", $_price_style, $_old_price_style);
                update_post_meta($item_id, "_image_style", $_image_style, $_old_image_style);
                update_post_meta($item_id, "_description_style", $_description_style, $_old_description_style);
                update_post_meta($item_id, "_slide_custom_classes", $custom_class, $_old_custom_classes);
                update_post_meta($item_id, "_slide_custom_id", $custom_id, $_old_custom_id);
            }
            if ($response){
                wp_send_json($response);
            }else {
                wp_send_json([
                    'id' => $item_id,
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
            $name = get_post_meta($post->ID, 'zay_slider_item_creator_name', true);
            ?>
                <table class="item-price"><tr>
                        <td>
                            <label for="zay_slider_item_parent" zay_slider_item_parent></label><?php _e('PARENT ID: ', 'zay-slider') ?></label>
                        </td>
                        <td>
                            <input type="text" readonly name="zay_slider_item_parent" id="zay_slider_item_parent" value="<?php echo esc_html($parent_id) ?>">
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <label for="zay_slider_item_creator_name"> <?php _e("Name", 'zay-slider') ?> </label>
                        </td>
                        <td>
                            <input type="text" placeholder="<?php _e('Creator Name', 'zay-slider'); ?>" name="zay_slider_item_creator_name" id="zay_slider_item_creator_name" value="<?php echo isset($name) ? esc_html($name) : "" ?>">
                        </td>
                    </tr>     
                    <tr>
                        <td>
                            <label for="zay_slider_item_price"> <?php _e("Items Price", "zay-slider") ?>: </label>
                        </td>
                        <td>
                            <input 
                                type="text"
                                id="zay_slider_item_price" 
                                class="priceAmount" 
                                name="zay_slider_item_price" 
                                placeholder="<?php _e('Price', 'zay-slider'); ?>" 
                                value="<?php echo isset($price_amount) ? esc_html( $price_amount) : '' ?>"
                            >
                            <input type="text" value="<?php echo isset($price_name) ? esc_html( $price_name) : '' ?>" id="zay_slider_item_price_name" name="zay_slider_item_price_name" class="priceName" placeholder="<?php _e('Price Title', 'zay-slider'); ?>" >
                        </td>
                    </tr>
                </table>
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