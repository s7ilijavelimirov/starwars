<?php

/**
 * Theme scripts - optimizovan setup za Bootstrap 5.3
 *
 * @package s7design
 */

function s7design_load_scripts()
{
    // Definišemo putanju do dist foldera
    $dist_path = get_template_directory_uri() . '/dist';
    $dist_dir = get_template_directory() . '/dist';

    // Bootstrap JS
    wp_register_script(
        's7design-bootstrap-js',
        $dist_path . '/js/bootstrap.bundle.min.js',
        array(), // Bez zavisnosti
        file_exists($dist_dir . '/js/bootstrap.bundle.min.js') ? filemtime($dist_dir . '/js/bootstrap.bundle.min.js') : null,
        true
    );
    wp_enqueue_script('s7design-bootstrap-js');
    wp_script_add_data('s7design-bootstrap-js', 'defer', true);

    // Glavni JS fajl teme
    wp_register_script(
        's7design-main-js',
        $dist_path . '/js/frontend-build.js',
        array('s7design-bootstrap-js'),
        file_exists($dist_dir . '/js/frontend-build.js') ? filemtime($dist_dir . '/js/frontend-build.js') : null,
        true
    );
    wp_enqueue_script('s7design-main-js');
    wp_script_add_data('s7design-main-js', 'defer', true);

    // Prosleđujemo varijable u JS
    wp_localize_script(
        's7design-main-js',
        'wp_data',
        array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('s7design-nonce'),
            'site_url' => site_url(),
            'is_home' => is_home(),
            'is_front_page' => is_front_page(),
            'theme_url' => get_template_directory_uri(),
        )
    );
}
add_action('wp_enqueue_scripts', 's7design_load_scripts');

/**
 * Optimizacija WooCommerce skripti
 */
function s7design_optimize_woocommerce_scripts()
{
    // Samo ako WooCommerce postoji
    if (!class_exists('WooCommerce')) {
        return;
    }

    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_account_page() && !is_shop()) {
        // Potpuno isključi WooCommerce stilove na ne-WooCommerce stranicama
        add_filter('woocommerce_enqueue_styles', '__return_empty_array');
        
        // Ukloni sve nepotrebne skripte
        wp_dequeue_script('woocommerce');
        wp_dequeue_script('wc-add-to-cart');
        wp_dequeue_script('js-cookie');
        wp_dequeue_script('jquery-blockui');

        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');
        wp_dequeue_style('woocommerce-blockui');

        wp_dequeue_style('wc-blocks-style');
        wp_dequeue_style('wc-blocks-vendors-style');
    }

    // Ukloni posebne skripte samo na određenim stranicama
    if (!is_checkout()) {
        wp_dequeue_script('wc-order-attribution');
        wp_dequeue_script('wc-checkout');
        wp_dequeue_script('wc-credit-card-form');
    }

    // Cart fragments optimizacija - samo na relevantnim stranicama
    if (!is_cart() && !is_checkout()) {
        wp_dequeue_script('wc-cart-fragments');
    }
}
add_action('wp_enqueue_scripts', 's7design_optimize_woocommerce_scripts', 99);

/**
 * Optimizacija JS za scroll efekat header-a
 */
function s7design_optimize_scroll_effects() {
    // Implementiraj direktno ako se koristi samo na nekim stranicama,
    // inače ovo može ostati u frontend.js
    if (is_front_page() || is_home() || is_single()) {
        // Već implementirano u frontend.js
    }
}
add_action('wp_footer', 's7design_optimize_scroll_effects', 99);