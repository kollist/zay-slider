<?php 
    $post = new WP_Query(array(
        'post_type' => 'zay-slider-menu',
        'post_status' => 'publish',
        'post__in' => $id,
    ));

    if ($post->have_posts()):
        while ($post->have_posts()):
            $post->the_post();
?>
<div class="shortcode-container" id="menu-shortcode" data-id="<?php the_ID(); ?>">
<div class="menu-info">
    <h3 class="container-title">
        <?php the_title() ?>
    </h3>
    <div class="container-content">
        <?php the_content(); ?>
    </div>
</div>
<div class='items-wrapper'>
<?php
            $post_items = new WP_Query(array(
                'post_type' => 'zay-slider-item',
                'post_status' => 'publish',
                'posts_per_page' => -1,
                'orderby' => 'menu_order',
                'order' => 'ASC',
                'meta_query' => array(array(
                    'key' => 'zay_slider_item_parent',
                    'value' => $id,
                    'compare' => '=',  // Replace '=' with the desired comparison operator
                
                )),
            ));
            ?>
            <span class="dashicons dashicons-arrow-left-alt2" id='left'></span>
            <div class="carousel" id="carousel">
                <?php
                    if ($post_items->have_posts()):
                        while($post_items->have_posts()):
                            $post_items->the_post();
                            ?>
                            <div draggable="false" class="item-card swiper-slide ">
                                <div class="item-card-img">
                                    <?php 
                                        if (! has_post_thumbnail() ) {
                                            echo wp_get_attachment_image(get_option('zayslider_default_image'), 'thumbnail'); 
                                        } the_post_thumbnail(array(200, 200));
                                    ?>
                                </div>
                                <div class="item-card-info">
                                    <h2 class="item-card-title"><?php the_title() ?> </h2>
                                    <!-- <p class="item-card-content">
                                        </p> -->
                                        <?php the_content(); ?>    
                                </div>
                            </div>
                            <?php
                        endwhile;
                    endif;
                ?>
            </div>
            <span class="dashicons dashicons-arrow-right-alt2" id="right"></span>
            <div class="bullets">
                
            </div>
            <?php
        endwhile;
    endif;
?>
</div>
</div>

