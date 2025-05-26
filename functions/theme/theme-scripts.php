<?php

/**
 * Učitavanje skripti za temu - optimizovana verzija bez inline JS-a
 *
 * @package s7design
 */

/**
 * Glavni enqueue za skripte
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

    // Swiper core
    wp_enqueue_script(
        's7design-swiper',
        $dist_uri . '/js/swiper-bundle.min.js',
        [],
        file_exists($dist_dir . '/js/swiper-bundle.min.js') ? filemtime($dist_dir . '/js/swiper-bundle.min.js') : null,
        true
    );

    // Swiper INIT skripta sa zavisnošću od Swiper-a
    wp_enqueue_script(
        's7design-swiper-init',
        $dist_uri . '/js/swiper-init-build.js',
        ['s7design-swiper'],
        file_exists($dist_dir . '/js/swiper-init-build.js') ? filemtime($dist_dir . '/js/swiper-init-build.js') : null,
        true
    );

    // Glavni frontend JS
    wp_enqueue_script(
        's7design-frontend',
        $dist_uri . '/js/frontend-build.js',
        ['s7design-bootstrap', 's7design-swiper', 's7design-swiper-init', 'jquery'],
        file_exists($dist_dir . '/js/frontend-build.js') ? filemtime($dist_dir . '/js/frontend-build.js') : null,
        true
    );

    // AJAX parametri - samo osnovni
    wp_localize_script('s7design-frontend', 'ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('s7design_nonce'),
        'is_woocommerce' => class_exists('WooCommerce') ? 'true' : 'false',
        'is_shop' => is_shop() ? 'true' : 'false',
    ]);

    // Učitaj ecommerce-specifične skripte
    s7design_enqueue_ecommerce_scripts($dist_uri, $dist_dir);

    // Preload u <head>
    add_action('wp_head', 's7design_preload_resources', 1);
}
add_action('wp_enqueue_scripts', 's7design_enqueue_scripts');

/**
 * Ecommerce skripte
 */
function s7design_enqueue_ecommerce_scripts($dist_uri, $dist_dir)
{
    // WooCommerce custom skripte samo na WC stranicama
    if (class_exists('WooCommerce') && (is_woocommerce() || is_cart() || is_checkout())) {
        wp_enqueue_script(
            's7design-woocommerce',
            $dist_uri . '/js/woocommerce-custom-build.js',
            ['jquery'],
            file_exists($dist_dir . '/js/woocommerce-custom-build.js') ? filemtime($dist_dir . '/js/woocommerce-custom-build.js') : null,
            true
        );
    }

    // Quick View samo na shop/category stranicama
    if (is_shop() || is_product_category() || is_product_tag()) {
        wp_enqueue_script(
            's7design-quick-view',
            $dist_uri . '/js/quick-view-build.js',
            ['jquery'],
            file_exists($dist_dir . '/js/quick-view-build.js') ? filemtime($dist_dir . '/js/quick-view-build.js') : null,
            true
        );

        // Minimalna lokalizacija
        wp_localize_script('s7design-quick-view', 'sw_quick_view_params', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('sw_quick_view_nonce')
        ]);
    }

    // Load More skripte
    s7design_register_load_more_scripts($dist_uri, $dist_dir);
}

/**
 * Load More skripte
 */
function s7design_register_load_more_scripts($dist_uri, $dist_dir)
{
    if (is_shop() || is_product_category() || is_product_tag()) {
        wp_enqueue_script(
            's7design-load-more',
            $dist_uri . '/js/load-more-build.js',
            ['jquery'],
            file_exists($dist_dir . '/js/load-more-build.js') ? filemtime($dist_dir . '/js/load-more-build.js') : null,
            true
        );

        // Minimalna lokalizacija
        wp_localize_script('s7design-load-more', 'sw_load_more_params', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('sw_load_more_nonce'),
            'current_page' => max(1, get_query_var('paged')),
            'text_loading' => __('Učitavanje...', 's7design'),
            'text_no_more' => __('Nema više proizvoda', 's7design')
        ]);
    }
}

/**
 * Preload samo najkritičnijih resursa
 */
function s7design_preload_resources()
{
    $uri = get_template_directory_uri();

    // Preload glavnih stilova - najviši prioritet
    echo '<link rel="preload" href="' . $uri . '/dist/css/frontend.min.css" as="style" fetchpriority="high">' . "\n";

    // Preload samo kritičnih skripti
    echo '<link rel="preload" href="' . $uri . '/dist/js/bootstrap.bundle.min.js" as="script">' . "\n";
    echo '<link rel="preload" href="' . $uri . '/dist/js/frontend-build.js" as="script">' . "\n";

    // Swiper samo ako je stvarno potreban
    if (is_front_page() || is_home() || is_shop() || is_product_category() || is_product_tag()) {
        echo '<link rel="preload" href="' . $uri . '/dist/js/swiper-bundle.min.js" as="script">' . "\n";
        echo '<link rel="preload" href="' . $uri . '/dist/js/swiper-init-build.js" as="script">' . "\n";
    }

    // Preload samo najvažnijih fontova
    echo '<link rel="preload" href="' . $uri . '/dist/fonts/Montserrat-Regular.woff2" as="font" type="font/woff2" crossorigin fetchpriority="high">' . "\n";
    echo '<link rel="preload" href="' . $uri . '/dist/fonts/Montserrat-Bold.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
}

/**
 * Defer atribut za nekritične skripte
 */
function s7design_defer_scripts($tag, $handle, $src)
{
    $defer_scripts = [
        's7design-quick-view',
        // 's7design-load-more',    // OBRIŠI OVU LINIJU!
        's7design-woocommerce'
    ];

    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 's7design_defer_scripts', 10, 3);
