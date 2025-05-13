<?php

/**
 * Template Part: Product Sections - Swiper.js implementacija
 *
 * @package s7design
 * @version 2.0.0
 */

// Proveri da li imamo ACF polja
if (!function_exists('get_field') || !have_rows('product_carousels')) {
    return;
}
?>

<div class="container products" data-testid="homepage-products">
    <?php
    // Widget za proizvode (ako ga imaš)
    if (is_active_sidebar('product-widget')) : ?>
        <div class="footer-widget-logo" role="complementary">
            <?php dynamic_sidebar('product-widget'); ?>
        </div>
    <?php endif; ?>

    <?php
    // Prolazak kroz sve karusele proizvoda
    $carousel_count = 0;
    while (have_rows('product_carousels')) : the_row();
        $carousel_count++;

        // Osnovni podaci
        $section_title = get_sub_field('section_title');
        $view_all_link = get_sub_field('view_all_link');
        $product_source = get_sub_field('product_source');
        $number_of_products = get_sub_field('number_of_products') ?: 9;
        $slides_to_show = get_sub_field('slides_to_show') ?: 5;
        $show_dots = get_sub_field('show_dots');
        $product_order = get_sub_field('product_order') ?: 'date_desc';

        // Pripremi argumente za WP_Query
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => $number_of_products,
            'post_status' => 'publish',
        );

        // Postavi izvor podataka
        if ($product_source === 'category' && get_sub_field('product_category')) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => get_sub_field('product_category'),
                ),
            );
        } elseif ($product_source === 'custom' && get_sub_field('custom_products')) {
            $args['post__in'] = get_sub_field('custom_products');
            $args['orderby'] = 'post__in';
        }

        // Postavi redosled (samo ako nije custom izbor)
        if ($product_source !== 'custom') {
            switch ($product_order) {
                case 'date_desc':
                    $args['orderby'] = 'date';
                    $args['order'] = 'DESC';
                    break;
                case 'date_asc':
                    $args['orderby'] = 'date';
                    $args['order'] = 'ASC';
                    break;
                case 'price_asc':
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'ASC';
                    break;
                case 'price_desc':
                    $args['meta_key'] = '_price';
                    $args['orderby'] = 'meta_value_num';
                    $args['order'] = 'DESC';
                    break;
                case 'title_asc':
                    $args['orderby'] = 'title';
                    $args['order'] = 'ASC';
                    break;
                case 'title_desc':
                    $args['orderby'] = 'title';
                    $args['order'] = 'DESC';
                    break;
                case 'rand':
                    $args['orderby'] = 'rand';
                    break;
            }
        }

        // Query proizvoda
        $products_query = new WP_Query($args);

        // Prikaži samo ako imamo proizvode
        if ($products_query->have_posts()) :
            // Jedinstveni ID za ovaj karusel
            $carousel_id = 'product-carousel-' . $carousel_count;
    ?>
            <div class="head-products d-flex">
                <h2 class="fs-1"><?php echo esc_html($section_title); ?></h2>
                <?php if ($view_all_link) : ?>
                    <a class="links-all" href="<?php echo esc_url($view_all_link['url']); ?>" aria-label="<?php echo esc_attr($view_all_link['title'] ?: 'Pogledaj sve'); ?>">
                        <?php echo esc_html($view_all_link['title'] ?: 'Pogledaj sve'); ?> <i class="fa-solid fa-right-long" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </div>

            <!-- Swiper Container -->
            <div class="swiper-container <?php echo $show_dots ? 'with-pagination' : ''; ?>"
                id="<?php echo esc_attr($carousel_id); ?>"
                data-slides="<?php echo esc_attr($slides_to_show); ?>"
                data-category="<?php echo esc_attr(get_sub_field('product_category')); ?>">

                <div class="swiper-wrapper">
                    <?php while ($products_query->have_posts()) : $products_query->the_post();
                        global $product;

                        if (!$product || !$product->is_visible()) {
                            continue;
                        }

                        // Uzmi ID proizvoda za praćenje
                        $product_id = $product->get_id();
                        $product_sku = $product->get_sku();
                        $product_price = $product->get_price();
                        $product_cat_ids = $product->get_category_ids();
                        $product_cats = array();

                        foreach ($product_cat_ids as $cat_id) {
                            $term = get_term_by('id', $cat_id, 'product_cat');
                            if ($term) {
                                $product_cats[] = $term->name;
                            }
                        }

                        $product_cat_string = implode(', ', $product_cats);

                        // Proveri da li je proizvod na popustu
                        $is_on_sale = $product->is_on_sale();
                        $sale_class = $is_on_sale ? 'on-sale' : '';
                    ?>
                        <div class="swiper-slide">
                            <a href="<?php the_permalink(); ?>" class="product-card <?php echo $sale_class; ?>"
                                data-product-id="<?php echo esc_attr($product_id); ?>"
                                data-product-name="<?php echo esc_attr(get_the_title()); ?>"
                                data-product-price="<?php echo esc_attr($product_price); ?>"
                                data-product-sku="<?php echo esc_attr($product_sku); ?>"
                                data-product-category="<?php echo esc_attr($product_cat_string); ?>"
                                data-position="<?php echo esc_attr($products_query->current_post + 1); ?>">

                                <div class="product-image">
                                    <?php
                                    if (has_post_thumbnail()) {
                                        // Direktno učitaj sliku, ali dodaj swiper-lazy klasu za kompatibilnost
                                        echo get_the_post_thumbnail($product->get_id(), 'woocommerce_thumbnail', array(
                                            'class' => 'swiper-lazy',
                                            'alt' => get_the_title()
                                        ));
                                    } else {
                                        echo wc_placeholder_img();
                                    }
                                    ?>
                                    <div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div>

                                    <?php if ($is_on_sale) : ?>
                                        <span class="product-badge sale-badge">
                                            <?php
                                            if (!$product->is_type('variable')) {
                                                $regular_price = (float) $product->get_regular_price();
                                                $sale_price = (float) $product->get_sale_price();

                                                if ($regular_price > 0) {
                                                    $percentage = round(100 - ($sale_price / $regular_price * 100));
                                                    echo "-{$percentage}%";
                                                } else {
                                                    echo esc_html__('Sniženo', 'starwars-theme');
                                                }
                                            } else {
                                                echo esc_html__('Sniženo', 'starwars-theme');
                                            }
                                            ?>
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <h6 class="product-title"><?php the_title(); ?></h6>

                                <div class="product-price">
                                    <?php echo $product->get_price_html(); ?>
                                </div>
                            </a>
                        </div>
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>

                <!-- Kontrolna dugmad za navigaciju -->
                <div class="swiper-button-prev"></div>
                <div class="swiper-button-next"></div>

                <?php if ($show_dots) : ?>
                    <!-- Paginacija (dots) -->
                    <div class="swiper-pagination"></div>
                <?php endif; ?>
            </div>
    <?php
        endif;
    endwhile;
    ?>
</div>