<?php

/**
 * Template Part: Product Sections - Optimizovana verzija sa poboljšanim lazy loadingom
 *
 * @package s7design
 * @version 2.4.0
 */

// Proveri da li imamo ACF polja
if (!function_exists('get_field') || !have_rows('product_carousels')) {
    return;
}

?>

<!-- Proizvodi sekcija -->
<div class="container products">
    <?php
    // Brojač za jedinstvene ID-jeve
    $carousel_count = 0;

    // Loop kroz ACF repeater sa karuselima
    while (have_rows('product_carousels')) : the_row();
        $carousel_count++;

        // Osnovni podaci iz ACF-a
        $section_title = get_sub_field('section_title');
        $view_all_link = get_sub_field('view_all_link');
        $product_source = get_sub_field('product_source');
        $number_of_products = get_sub_field('number_of_products') ?: 10;
        $slides_to_show = get_sub_field('slides_to_show') ?: 5;
        $show_dots = get_sub_field('show_dots');
        $product_order = get_sub_field('product_order') ?: 'date_desc';
        $selected_category_id = get_sub_field('product_category');

        // Pripremi argumente za WP_Query
        $args = array(
            'post_type' => 'product',
            'posts_per_page' => $number_of_products,
            'post_status' => 'publish'
        );

        // Postavi izvor podataka
        if ($product_source === 'category' && $selected_category_id) {
            $args['tax_query'] = array(
                array(
                    'taxonomy' => 'product_cat',
                    'field' => 'term_id',
                    'terms' => $selected_category_id
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
            }
        }

        // Query proizvoda
        $products_query = new WP_Query($args);

        // Prikaži samo ako imamo proizvode
        if ($products_query->have_posts()) :
            // Jedinstveni ID za ovaj karusel
            $carousel_id = 'product-carousel-' . $carousel_count;
    ?>
            <div class="product-section">
                <!-- Naslov i link "pogledaj sve" -->
                <div class="head-products d-flex">
                    <h2 class="fs-1"><?php echo esc_html($section_title); ?></h2>
                    <?php if ($view_all_link) : ?>
                        <a class="links-all" href="<?php echo esc_url($view_all_link['url']); ?>">
                            <?php echo esc_html($view_all_link['title'] ?: 'Pogledaj sve'); ?> &rarr;
                        </a>
                    <?php endif; ?>
                </div>

                <!-- Navigacione strelice -->
                <div class="product-nav-buttons">
                    <button class="product-nav-prev" id="prev-<?php echo esc_attr($carousel_id); ?>">&larr;</button>
                    <button class="product-nav-next" id="next-<?php echo esc_attr($carousel_id); ?>">&rarr;</button>
                </div>

                <!-- Swiper Container -->
                <div class="swiper-container lazy-swiper" id="<?php echo esc_attr($carousel_id); ?>" data-slides="<?php echo esc_attr($slides_to_show); ?>">
                    <div class="swiper-wrapper">
                        <?php
                        $slide_count = 0;
                        while ($products_query->have_posts()) : $products_query->the_post();
                            global $product;
                            $slide_count++;

                            if (!$product || !$product->is_visible()) {
                                continue;
                            }

                            // Da li je proizvod na akciji
                            $is_on_sale = $product->is_on_sale();
                            $sale_class = $is_on_sale ? 'on-sale' : '';
                        ?>
                            <div class="swiper-slide">
                                <a href="<?php the_permalink(); ?>" class="product-card <?php echo $sale_class; ?>">
                                    <div class="product-image">
                                        <?php
                                        if (has_post_thumbnail()) {
                                            // Dohvati informacije o slici za maksimalnu optimizaciju
                                            $thumbnail_id = get_post_thumbnail_id();
                                            $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
                                            $image_alt = $image_alt ? $image_alt : get_the_title();

                                            // Koristi Swiper-ov nativni lazy loading sa data-src atributom
                                            $image_src = wp_get_attachment_image_src($thumbnail_id, 'woocommerce_thumbnail')[0];

                                            // Učitaj prve dve slike odmah, ostale kroz Swiper lazy loading
                                            if ($slide_count <= 2) {
                                                echo '<img src="' . esc_url($image_src) . '" alt="' . esc_attr($image_alt) . '" class="swiper-loaded" />';
                                            } else {
                                                echo '<img data-src="' . esc_url($image_src) . '" alt="' . esc_attr($image_alt) . '" class="swiper-lazy" />';
                                                echo '<div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div>';
                                            }
                                        } else {
                                            // Placeholder slika
                                            echo wc_placeholder_img();
                                        }
                                        ?>
                                        <?php if ($is_on_sale) : ?>
                                            <span class="product-badge sale-badge">
                                                <?php
                                                // Prikaži procenat popusta za proizvode koji nisu varijabilni
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
                    <?php if ($show_dots) : ?>
                        <!-- Paginacija (dots) -->
                        <div class="swiper-pagination"></div>
                    <?php endif; ?>
                </div>
            </div>
    <?php
        endif;
    endwhile;
    ?>
</div>