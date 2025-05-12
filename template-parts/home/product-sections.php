<?php

/**
 * Template Part: Product Sections
 *
 * @package s7design
 * @version 1.0.0
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
            <div class="head-products d-flex align-center mt-5">
                <h2 class="fs-1"><?php echo esc_html($section_title); ?></h2>
                <?php if ($view_all_link) : ?>
                    <a class="links-all" href="<?php echo esc_url($view_all_link['url']); ?>" aria-label="<?php echo esc_attr($view_all_link['title'] ?: 'Pogledaj sve'); ?>">
                        <?php echo esc_html($view_all_link['title'] ?: 'Pogledaj sve'); ?> <i class="fa-solid fa-right-long" aria-hidden="true"></i>
                    </a>
                <?php endif; ?>
            </div>

            <div class="sw-product-carousel<?php echo $show_dots ? ' with-dots' : ''; ?>"
                id="<?php echo esc_attr($carousel_id); ?>"
                data-slides="<?php echo esc_attr($slides_to_show); ?>"
                data-product-category="<?php echo esc_attr(get_sub_field('product_category')); ?>">

                <div class="sw-carousel-container">
                    <div class="sw-carousel-wrapper">
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
                        ?>
                            <div class="sw-carousel-item">
                                <a href="<?php the_permalink(); ?>" class="product-card"
                                    data-product-id="<?php echo esc_attr($product_id); ?>"
                                    data-product-name="<?php echo esc_attr(get_the_title()); ?>"
                                    data-product-price="<?php echo esc_attr($product_price); ?>"
                                    data-product-sku="<?php echo esc_attr($product_sku); ?>"
                                    data-product-category="<?php echo esc_attr($product_cat_string); ?>"
                                    data-position="<?php echo esc_attr($products_query->current_post + 1); ?>"
                                    onClick="trackProductClick(this)">
                                    <div class="product-image">
                                        <?php
                                        if (has_post_thumbnail()) {
                                            the_post_thumbnail('woocommerce_thumbnail', array(
                                                'class' => 'img-fluid',
                                                'alt' => get_the_title(),
                                                'loading' => 'lazy'
                                            ));
                                        } else {
                                            echo wc_placeholder_img();
                                        }
                                        ?>
                                    </div>
                                    <h3 class="product-title"><?php the_title(); ?></h3>
                                    <div class="product-price">
                                        <?php echo $product->get_price_html(); ?>
                                    </div>
                                </a>
                            </div>
                        <?php endwhile;
                        wp_reset_postdata(); ?>
                    </div>
                </div>

                <button class="sw-carousel-prev" aria-label="Prethodni proizvodi" aria-controls="<?php echo esc_attr($carousel_id); ?>">
                    <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
                </button>
                <button class="sw-carousel-next" aria-label="Sledeći proizvodi" aria-controls="<?php echo esc_attr($carousel_id); ?>">
                    <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                </button>

                <?php if ($show_dots) :
                    // Izračunaj broj tačkica na osnovu broja proizvoda i vidljivih proizvoda
                    $total_products = $products_query->found_posts;
                    $total_slides = ceil($total_products / $slides_to_show);
                ?>
                    <div class="sw-carousel-dots" role="tablist">
                        <?php for ($i = 0; $i < $total_slides; $i++) : ?>
                            <button class="sw-carousel-dot<?php echo $i === 0 ? ' active' : ''; ?>"
                                data-slide="<?php echo $i; ?>"
                                aria-label="Slajd <?php echo $i + 1; ?>"
                                role="tab"
                                aria-selected="<?php echo $i === 0 ? 'true' : 'false'; ?>"
                                tabindex="<?php echo $i === 0 ? '0' : '-1'; ?>"></button>
                        <?php endfor; ?>
                    </div>
                <?php endif; ?>
            </div>
    <?php
        endif;
    endwhile;
    ?>
</div>