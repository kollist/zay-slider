<?php 
    $content = __('Your Item Description Goes Here', 'zay-slider'); // Initialize the content variable with the desired initial content

    $editor_id = 'my_custom_editor'; // Unique ID for the editor instance
    
    $settings = array(
        'textarea_name' => 'my_custom_textarea', // Name attribute for the textarea
        'media_buttons' => true, // Whether to display media upload buttons
        'tinymce' => array(
            'toolbar1' => ' blockquote bold italic underline | bullist numlist | link unlink | strikethrough', // Customize the toolbar buttons
            // 'toolbar2' => 'alignleft aligncenter alignright alignjustify | forecolor backcolor | wp_adv'
        ),
        'editor_height',
        'editor_class' => 'my-item-custom-editor',
    );
    $father_id = $post->ID;
    $my_query = new WP_Query(array(
        'post_type' => 'zay-slider-item',
        'posts_per_page' => -1,
        'orderby' => 'menu_order',
        'order' => 'ASC',
        'meta_query' => array(
            array(
                'key' => 'zay_slider_item_parent',
                'value' => $post->ID,
                'compare' => '=',  // Replace '=' with the desired comparison operator
            ),
        ),
    ));
    ?>
<div class="delete-dialog" style="display: none;">
        <div class="deleteBtn">
            <button id="closeDeleteDialogBtn">
                <span class="dashicons dashicons-no"></span>
            </button>
        </div>
        <div class="message">
            <p>Are You Sure You Want To Delete This Item ?</p>
        </div>
        <div class="dialogBtns">
            <button class="button button-primary cancelDeleteBtn">
                <?php esc_html_e('Cancel', 'zay-slider') ?>
            </button>
            <button class="button button-primary confirmDeleteBtn" data-nonce="<?php echo wp_create_nonce('delete_item_nonce'); ?>" >
                <?php esc_html_e('Delete', 'zay-slider') ?>
            </button>
        </div>
</div>
    <ul id="sortable" class="menu list-group" >
    <?php
        if ($my_query->have_posts()):
            global $wp;
            while($my_query->have_posts()):
                $my_query->the_post();
                $price_amount = get_post_meta( get_the_ID(), 'zay_slider_item_price', true );
                $price_currency = get_post_meta( get_the_ID(), 'zay_slider_item_price_name', true );
                
                ?>


                <li class="menu-item" id="<?php the_ID(); ?>">
                    <div class="show-data">
                        
                        <?php 
                            if (has_post_thumbnail()): 
                                the_post_thumbnail(array(70, 70));
                            else:
                                echo wp_get_attachment_image(get_option('zayslider_default_image'), array(70, 70)); 
                            endif;
                        ?>
                        <div class="item-info">
                            <div class="item-show-title">
                                <h4> <?php the_title(); ?> </h4>
                                <div class="item-show-price">
                                    <span><?php echo isset($price_amount) ? $price_amount : "" ?></span>
                                    <span><?php echo isset($price_currency) ? $price_currency : "" ?></span>
                                </div>
                            </div>
                            <div class="item-show-desc">
                            <?php the_content(); ?>
                            </div>
                        </div>
                    </div>
                    <div class="action-data">
                        <button class="delete-btn">
                            <span class="deleteIcon dashicons dashicons-trash"></span>
                        </button>
                        <button class="edit-btn-displayer" data-nonce="<?php echo wp_create_nonce('edit_item_nonce'); ?>" >
                            <span class="editIcon dashicons dashicons-edit"></span>
                        </button>
                    </div>
                </li>
                <!-- <span class="dashicons dashicons-menu"></span> -->
                <?php
                
            endwhile;
        endif;
        ?>
    </ul>
    <?php
    if ($post->post_status == 'publish'): 
        
?>
<div class="toggleMenu">
    <button class="button itemBtn button-default button-large"> <?php echo _e('Add New Item', 'zay-slider') ?> </button>
</div>
<div class="modal fade dialogMenuItem hide">
    <input type="hidden" name="zay_slider_nonce" class="zay_slider_nonce" value="<?php echo wp_create_nonce( "zay_slider_nonce" ); ?>">
    <div class="modal-dialog modal-lg dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title"><?php _e( 'Slider item' , 'zay-slider' ); ?></h4>
                <button type="button" class="modal-button">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">

                    <div class="col-xs-3">
                        <div class="edit-image">
                            <div class="uploader-upload">
                                <div>
                                    <button class="button button-default" id="upload_image_button"><?php _e('Select Image', 'zay-slider'); ?></button>
                                </div>
                            </div>
                            <div class="uploader-image" >
                                <img  class='thumb' src="<?php if (isset($options['upload-image'])) echo esc_attr($options['upload-image']); ?>">
                                <button type="button" class="icon-delete">&times;</button>
                            </div>
                        </div>
                    </div>

                    <div class="form-container">
                        <div class="form-group">
                            <label for="itemTitle" ><?php _ex('Title','modal window','zay-slider'); ?></label>
                            <input type="text" id="itemTitle" class="form-control item-title" value="<?php _e('Item Title', 'zay-slider') ?>">
                        </div>
                        <div class="form-group">
                            <label for="<?php echo $editor_id ?>"><?php _ex('Description','modal window', 'zay-slider'); ?></label>
                            <div class="modal-texteditor">
                                <?php wp_editor($content, $editor_id, $settings); ?>
                            </div>
                        </div>
                        <div class="form-group price-form" >
                                        <label><?php _e('Price','zay-slider'); ?></label>
                                        <div class="edit-price">
                                            <div class="item-price hidePrice">
                                                <input id="zay_slider_item_price" name='zay_slider_item_price' class="priceAmount" placeholder="<?php _e('Price', 'zay-slider'); ?>"  >
                                                <input id="zay_slider_item_price_name" name="zay_slider_item_price_name" class="priceName" placeholder="<?php _e('Title', 'zay-slider'); ?>"  >
                                                <button type="button" class="rmvPrice">&times;</button>
                                            </div>
                                            <span id="errorMessage" class="errorMessage hide">You can't have a price name without a price</span>
                                            <button  class="add-price button button-default" ><?php _e( 'Add price' , 'zay-slider' ); ?></button>
                                        </div>
                        </div>
                    </div>

                </div>

            </div>
            <div class="modal-footer">
                <button type="reset" class="button button-primary cancel-btn"><?php _e( 'Cancel' , 'zay-slider' ); ?></button>
                <button type="submit" class="button button-primary save-btn"><?php _e( 'Save' , 'zay-slider' ); ?></button>
                <button type="submit" data-nonce="<?php echo wp_create_nonce('edit_item_nonce'); ?>" class="button button-primary edit-btn" style="display: none"><?php _e( 'Edit' , 'zay-slider' ); ?></button>
            </div>

        </div>
    </div>
</div>
<?php else: ?>
    <h3>Please, SAVE THIS POST to begin adding Slider Items</h3>
<?php endif; ?>
