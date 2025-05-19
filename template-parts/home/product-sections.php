<?php

/**
 * Template Part: Product Sections - Sa ispravkom za slike
 * Verzija bez lazy loading-a za slike (direktan prikaz)
 *
 * @package s7design
 * @version 3.0.0
 */

// Proveri da li imamo ACF polja
if (!function_exists('get_field') || !have_rows('product_sections')) {
    return;
}
?>

<!-- Proizvodi sekcija -->
<section class="container products py-5">
    <?php
    // Brojač za jedinstvene ID-jeve
    $section_count = 0;

    // Loop kroz ACF repeater sa sekcijama proizvoda
    while (have_rows('product_sections')) : the_row();
        $section_count++;

        // Osnovni podaci iz ACF-a sa proverama
        $section_title = get_sub_field('section_title');
        $section_subtitle = get_sub_field('section_title_copy', false);
        $view_all_link = get_sub_field('view_all_link');
        $display_type = get_sub_field('display_type') ?: 'carousel'; // Default: carousel
        $product_source = get_sub_field('product_source');
        $number_of_products = get_sub_field('number_of_products') ?: 10;
        $slides_to_show = get_sub_field('slides_to_show') ?: 5;
        $show_dots = get_sub_field('show_dots');
        $product_order = get_sub_field('product_order') ?: 'date_desc';
        $selected_category_id = get_sub_field('product_category');
        $custom_products = get_sub_field('custom_products');

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
        } elseif ($product_source === 'custom' && !empty($custom_products)) {
            $args['post__in'] = $custom_products;
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

        // Prikaži samo ako imamo proizvode i naslov sekcije
        if ($products_query->have_posts() && !empty($section_title)) :
            // Jedinstveni ID za ovu sekciju
            $section_id = 'product-section-' . $section_count;
    ?>
            <div class="product-section">
                <!-- Naslov i link "pogledaj sve" -->
                <div class="head-products d-flex mb-3 pb-3">
                    <h2 class="fs-1"><?php echo esc_html($section_title); ?></h2>
                    <?php if (!empty($view_all_link) && !empty($view_all_link['url'])) : ?>
                        <a class="links-all" href="<?php echo esc_url($view_all_link['url']); ?>">
                            <?php echo esc_html(!empty($view_all_link['title']) ? $view_all_link['title'] : 'Pogledaj sve'); ?>
                        </a>
                    <?php endif; ?>
                </div>

                <?php if ($display_type === 'carousel') : ?>
                    <!-- KARUSEL PRIKAZ -->


                    <!-- Donji red: podnaslov levo, strelice desno -->
                    <div class="row align-items-center mb-3">
                        <?php if (!empty($section_subtitle)) : ?>
                            <div class="col-md-9 col-12">
                                <h6 class="mb-0 fw-normal text-white"><?php echo $section_subtitle; ?></h6>
                            </div>
                        <?php endif; ?>

                        <div class="col-auto ms-auto">
                            <?php
                            // Inicijalizuj promenljive sa podrazumevanim vrednostima
                            $prev_disabled = isset($prev_disabled) ? $prev_disabled : false;
                            $next_disabled = isset($next_disabled) ? $next_disabled : false;
                            ?>

                            <div class="product-nav-buttons">
                                <button type="button" class="product-nav-prev" id="prev-<?php echo esc_attr($section_id); ?>" aria-label="Prethodni proizvodi" role="button" <?php echo $prev_disabled ? 'disabled' : ''; ?>>
                                    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/arrow.svg" alt="<?php echo $prev_disabled ? 'Nema prethodnih proizvoda' : 'Prethodno'; ?>" />
                                </button>
                                <button type="button" class="product-nav-next" id="next-<?php echo esc_attr($section_id); ?>" aria-label="Sledeći proizvodi" role="button" <?php echo $next_disabled ? 'disabled' : ''; ?>>
                                    <img src="<?php echo get_template_directory_uri(); ?>/dist/images/arrow.svg" alt="<?php echo $next_disabled ? 'Nema sledećih proizvoda' : 'Sledeće'; ?>" />
                                </button>
                            </div>


                        </div>
                    </div>


                    <!-- Swiper Container -->
                    <div class="swiper-container" id="<?php echo esc_attr($section_id); ?>" data-slides="<?php echo esc_attr($slides_to_show); ?>">
                        <div class="swiper-wrapper">
                            <?php
                            while ($products_query->have_posts()) : $products_query->the_post();
                                global $product;

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
                                                $thumbnail_id = get_post_thumbnail_id();
                                                $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
                                                $image_alt = $image_alt ? $image_alt : get_the_title();
                                                $image_src = wp_get_attachment_image_src($thumbnail_id, 'woocommerce_thumbnail')[0];
                                                echo '<img src="' . esc_url($image_src) . '" alt="' . esc_attr($image_alt) . '" class="product-image-img" />';
                                            } else {
                                                echo wc_placeholder_img();
                                            }
                                            ?>
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
                        <?php if ($show_dots) : ?>
                            <!-- Paginacija (dots) -->
                            <div class="swiper-pagination"></div>
                        <?php endif; ?>
                    </div>
                <?php else : ?>
                    <?php if (!empty($section_subtitle)) : ?>
                        <!-- Donji red: podnaslov levo, strelice desno -->
                        <div class="row align-items-center mb-3">
                            <?php if (!empty($section_subtitle)) : ?>
                                <div class="col-md-9 col-12">
                                    <h6 class="mb-0 fw-normal text-white"><?php echo $section_subtitle; ?></h6>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>
                    <!-- GRID PRIKAZ -->
                    <div class="product-grid">
                        <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-4">
                            <?php
                            while ($products_query->have_posts()) : $products_query->the_post();
                                global $product;

                                if (!$product || !$product->is_visible()) {
                                    continue;
                                }

                                $is_on_sale = $product->is_on_sale();
                                $sale_class = $is_on_sale ? 'on-sale' : '';
                            ?>
                                <div class="col">
                                    <a href="<?php the_permalink(); ?>" class="product-card <?php echo $sale_class; ?>">
                                        <div class="product-image">
                                            <?php
                                            if (has_post_thumbnail()) {
                                                $thumbnail_id = get_post_thumbnail_id();
                                                $image_alt = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true);
                                                $image_alt = $image_alt ? $image_alt : get_the_title();
                                                $image_src = wp_get_attachment_image_src($thumbnail_id, 'woocommerce_thumbnail')[0];
                                                echo '<img src="' . esc_url($image_src) . '" alt="' . esc_attr($image_alt) . '" class="product-image-img" loading="lazy" />';
                                            } else {
                                                echo wc_placeholder_img();
                                            }
                                            ?>
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
                    </div>
                <?php endif; ?>
            </div>
    <?php
        endif;
    endwhile;
    ?>
    </div>