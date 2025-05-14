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

    // Glavni frontend stilovi (Bootstrap + custom)
    wp_enqueue_style(
        's7design-frontend',
        $dist_uri . '/css/frontend.min.css',
        [],
        file_exists($dist_dir . '/css/frontend.min.css') ? filemtime($dist_dir . '/css/frontend.min.css') : null,
        'all'
    );

    // WordPress style.css (opciono ako koristiš dodatne stilove tu)
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
