<?php

/**
 * Učitavanje stilova za temu
 *
 * @package s7design
 */

function s7design_enqueue_styles()
{
    $dist_uri = get_template_directory_uri() . '/dist';
    $dist_dir = get_template_directory() . '/dist';

    // Swiper CSS - mora se učitati pre frontend.min.css da bi se mogao override-ovati
    wp_enqueue_style(
        'swiper-css',
        $dist_uri . '/css/swiper-bundle.min.css',
        [],
        '11.2.6', // Dodajte odgovarajuću verziju
        'all'
    );

    // Glavni frontend stilovi (Bootstrap + custom)
    wp_enqueue_style(
        's7design-frontend',
        $dist_uri . '/css/frontend.min.css',
        ['swiper-css'], // Swiper CSS kao dependency
        file_exists($dist_dir . '/css/frontend.min.css') ? filemtime($dist_dir . '/css/frontend.min.css') : null,
        'all'
    );

    wp_enqueue_style(
        's7design-style',
        get_stylesheet_uri(),
        ['s7design-frontend'],
        file_exists(get_stylesheet_directory() . '/style.css') ? filemtime(get_stylesheet_directory() . '/style.css') : null,
        'all'
    );

    // WooCommerce stilovi
    if (class_exists('WooCommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_shop())) {
        wp_enqueue_style(
            's7design-woocommerce',
            get_template_directory_uri() . '/woocommerce.css',
            ['s7design-frontend'],
            file_exists(get_template_directory() . '/woocommerce.css') ? filemtime(get_template_directory() . '/woocommerce.css') : null,
            'all'
        );
    }
}
add_action('wp_enqueue_scripts', 's7design_enqueue_styles');
