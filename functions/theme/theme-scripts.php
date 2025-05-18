<?php

/**
 * Učitavanje skripti za temu
 *
 * @package s7design
 */

function s7design_enqueue_scripts()
{
    $dist_uri = get_template_directory_uri() . '/dist';
    $dist_dir = get_template_directory() . '/dist';

    // jQuery (osigurajmo da je dostupan)
    wp_enqueue_script('jquery');

    // Bootstrap bundle
    wp_enqueue_script(
        's7design-bootstrap',
        $dist_uri . '/js/bootstrap.bundle.min.js',
        ['jquery'],
        file_exists($dist_dir . '/js/bootstrap.bundle.min.js') ? filemtime($dist_dir . '/js/bootstrap.bundle.min.js') : null,
        true
    );

    // Swiper core - ENQUEUE umesto REGISTER
    wp_enqueue_script(
        's7design-swiper',
        $dist_uri . '/js/swiper-bundle.min.js',
        [],
        file_exists($dist_dir . '/js/swiper-bundle.min.js') ? filemtime($dist_dir . '/js/swiper-bundle.min.js') : null,
        true
    );

    // Swiper INIT skripta - ENQUEUE umesto REGISTER i sa zavisnošću
    wp_enqueue_script(
        's7design-swiper-init',
        $dist_uri . '/js/swiper-init-build.js',
        ['s7design-swiper'], // Ključno - mora zavisiti od Swiper-a
        file_exists($dist_dir . '/js/swiper-init-build.js') ? filemtime($dist_dir . '/js/swiper-init-build.js') : null,
        true
    );

    // Glavni frontend JS - nakon Bootstrap-a, Swiper-a i Swiper-init
    wp_enqueue_script(
        's7design-frontend',
        $dist_uri . '/js/frontend-build.js',
        ['s7design-bootstrap', 's7design-swiper', 's7design-swiper-init', 'jquery'],
        file_exists($dist_dir . '/js/frontend-build.js') ? filemtime($dist_dir . '/js/frontend-build.js') : null,
        true
    );
    if (class_exists('WooCommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page())) {
        wp_enqueue_script(
            's7design-woocommerce',
            get_template_directory_uri() . '/dist/js/woocommerce-custom-build.js',
            array('jquery'),
            filemtime(get_template_directory() . '/dist/js/woocommerce-custom-build.js'),
            true
        );
    }
    // Dodavanje WP AJAX URL-a za skripte (ako koristite AJAX)
    wp_localize_script('s7design-frontend', 'ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('s7design_nonce')
    ]);

    // Preload u <head>
    add_action('wp_head', 's7design_preload_resources', 1);
}
add_action('wp_enqueue_scripts', 's7design_enqueue_scripts');

/**
 * Preload JS, CSS i fontova
 */
function s7design_preload_resources()
{
    $uri = get_template_directory_uri();

    echo '<link rel="preload" href="' . $uri . '/dist/css/frontend.min.css" as="style">' . "\n";

    echo '<link rel="preload" href="' . $uri . '/dist/js/bootstrap.bundle.min.js" as="script">' . "\n";
    echo '<link rel="preload" href="' . $uri . '/dist/js/frontend-build.js" as="script">' . "\n";
    echo '<link rel="preload" href="' . $uri . '/dist/js/swiper-bundle.min.js" as="script">' . "\n";
    echo '<link rel="preload" href="' . $uri . '/dist/js/swiper-init-build.js" as="script">' . "\n";

    echo '<link rel="preload" href="' . $uri . '/dist/fonts/Montserrat-Regular.woff2" as="font" type="font/woff2" crossorigin fetchpriority="high">' . "\n";
    echo '<link rel="preload" href="' . $uri . '/dist/fonts/Montserrat-Bold.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
    echo '<link rel="preload" href="' . $uri . '/dist/fonts/Montserrat-Medium.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
}
