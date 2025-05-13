<?php

/**
 * Template Name: Front Page
 * 
 * @package s7design
 */

get_header();

// Uklanjanje WooCommerce akcija
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
remove_action('woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30);
remove_action('woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30);
add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

if (have_posts()) :
    while (have_posts()) : the_post();

        // Hero Slider
        get_template_part('template-parts/home/hero-slider');

        // Proizvodi
        get_template_part('template-parts/home/product-sections');

        // Dostava Sekcija
        get_template_part('template-parts/home/delivery-section');

        // Kategorije Blokovi
        get_template_part('template-parts/home/category-blocks');

        // Blog Sekcija
        //get_template_part('template-parts/home/blog-section');

    endwhile;
endif;

get_footer();
