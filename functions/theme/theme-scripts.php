<?php

/**
 * Učitavanje skripti za temu - optimizovana verzija
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

    // Swiper core - ENQUEUE umesto REGISTER
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
        ['s7design-swiper'], // Zavisnost od Swiper-a
        file_exists($dist_dir . '/js/swiper-init-build.js') ? filemtime($dist_dir . '/js/swiper-init-build.js') : null,
        true
    );

    // Glavni frontend JS - učitan nakon Bootstrap-a, Swiper-a i Swiper-init
    wp_enqueue_script(
        's7design-frontend',
        $dist_uri . '/js/frontend-build.js',
        ['s7design-bootstrap', 's7design-swiper', 's7design-swiper-init', 'jquery'],
        file_exists($dist_dir . '/js/frontend-build.js') ? filemtime($dist_dir . '/js/frontend-build.js') : null,
        true
    );

    // Dodavanje WP AJAX URL-a za skripte (ako koristite AJAX)
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
 * Odvojeni enqueue specifično za ecommerce skripte
 */
function s7design_enqueue_ecommerce_scripts($dist_uri, $dist_dir)
{
    // Učitaj WooCommerce custom skripte samo na WooCommerce stranicama
    if (class_exists('WooCommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page())) {
        wp_enqueue_script(
            's7design-woocommerce',
            $dist_uri . '/js/woocommerce-custom-build.js',
            ['jquery'],
            file_exists($dist_dir . '/js/woocommerce-custom-build.js') ? filemtime($dist_dir . '/js/woocommerce-custom-build.js') : null,
            true
        );
    }

    // Učitaj Quick View samo na stranicama proizvoda i arhivama
    if (is_shop() || is_product_category() || is_product_tag() || is_front_page()) {
        // Registracija Quick View skripte
        wp_enqueue_script(
            's7design-quick-view',
            $dist_uri . '/js/quick-view-build.js',
            ['jquery'],
            file_exists($dist_dir . '/js/quick-view-build.js') ? filemtime($dist_dir . '/js/quick-view-build.js') : null,
            true
        );

        // Lokalizacija skripte za AJAX
        wp_localize_script('s7design-quick-view', 'sw_quick_view_params', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('sw_quick_view_nonce'),
            'cache_enabled' => 'true' // Omogućava klijentsko keširanje za brži prikaz
        ]);
    }

    // Učitaj Load More samo na stranicama arhive proizvoda
    if (is_shop() || is_product_category() || is_product_tag()) {
        wp_enqueue_script(
            's7design-load-more',
            $dist_uri . '/js/load-more-build.js',
            ['jquery'],
            file_exists($dist_dir . '/js/load-more-build.js') ? filemtime($dist_dir . '/js/load-more-build.js') : null,
            true
        );

        // Lokalizacija skripte za AJAX
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
 * Preload kritičnih resursa za brži prikazivanje stranice
 */
function s7design_preload_resources()
{
    $uri = get_template_directory_uri();

    // Preload glavnih stilova
    echo '<link rel="preload" href="' . $uri . '/dist/css/frontend.min.css" as="style">' . "\n";

    // Preload kritičnih skripti
    echo '<link rel="preload" href="' . $uri . '/dist/js/bootstrap.bundle.min.js" as="script">' . "\n";
    echo '<link rel="preload" href="' . $uri . '/dist/js/frontend-build.js" as="script">' . "\n";

    // Preload Swiper only on pages that need it
    if (is_front_page() || is_home() || is_shop() || is_product_category() || is_product_tag()) {
        echo '<link rel="preload" href="' . $uri . '/dist/js/swiper-bundle.min.js" as="script">' . "\n";
        echo '<link rel="preload" href="' . $uri . '/dist/js/swiper-init-build.js" as="script">' . "\n";
    }

    // Preload fontova - najviši prioritet za regularni font
    echo '<link rel="preload" href="' . $uri . '/dist/fonts/Montserrat-Regular.woff2" as="font" type="font/woff2" crossorigin fetchpriority="high">' . "\n";
    echo '<link rel="preload" href="' . $uri . '/dist/fonts/Montserrat-Bold.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
    echo '<link rel="preload" href="' . $uri . '/dist/fonts/Montserrat-Medium.woff2" as="font" type="font/woff2" crossorigin>' . "\n";

    // DNS prefetch za eksternu domenu ako koristi CDN
    echo '<link rel="dns-prefetch" href="//cdn.jsdelivr.net">' . "\n";
}

/**
 * Dodavanje defer atributa na nekritične skripte
 */
function s7design_defer_scripts($tag, $handle, $src)
{
    // Lista skripti koje treba učitati sa defer
    $defer_scripts = [
        's7design-quick-view',
        's7design-load-more',
        's7design-woocommerce'
    ];

    // Ako je skripta u listi, dodaj defer
    if (in_array($handle, $defer_scripts)) {
        return str_replace(' src', ' defer src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 's7design_defer_scripts', 10, 3);

/**
 * Optimizacija AJAX zahteva sa klijentskim keširanjem
 */
function s7design_optimize_ajax_requests()
{
    // Pokreni ovo samo na stranicama gde koristimo AJAX
    if (is_shop() || is_product_category() || is_product_tag() || is_front_page()) {
?>
        <script>
            // Optimizacija za AJAX zahteve sa klijentskim keširanjem
            (function($) {
                // Keširanje za brži prikaz quick-view-a i load-more proizvoda
                if (typeof sw_quick_view_params !== 'undefined' && sw_quick_view_params.cache_enabled === 'true') {
                    // Keširanje za već učitane proizvode
                    window.swProductCache = {};

                    // Dodaj praćenje već učitanih proizvoda
                    $(document).on('click', '.sw-quick-view-button', function() {
                        var productId = $(this).data('product-id');
                        if (productId && window.swProductCache[productId]) {
                            console.log('Using cached product data');
                        }
                    });

                    // Zapamti otvorene quick-view prozore da se ne učitavaju ponovo
                    $(document).ajaxSuccess(function(event, xhr, settings) {
                        try {
                            if (settings.data && settings.data.indexOf('sw_load_quick_view') > -1) {
                                var productId = settings.data.match(/product_id=(\d+)/);
                                if (productId && productId[1] && xhr.responseJSON && xhr.responseJSON.success) {
                                    window.swProductCache[productId[1]] = xhr.responseJSON;
                                }
                            }
                        } catch (e) {}
                    });
                }

                // Optimizacija učitavanja slika sa intersection observer
                if ('IntersectionObserver' in window) {
                    var lazyImageObserver = new IntersectionObserver(function(entries, observer) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                let lazyImage = entry.target;
                                if (lazyImage.dataset.src) {
                                    lazyImage.src = lazyImage.dataset.src;
                                    lazyImage.removeAttribute('data-src');
                                    lazyObserver.unobserve(lazyImage);
                                }
                            }
                        });
                    });

                    // Primeni na sve slike koje imaju data-src atribut
                    document.addEventListener('DOMContentLoaded', function() {
                        const lazyImages = document.querySelectorAll('img[data-src]');
                        lazyImages.forEach(function(lazyImage) {
                            lazyImageObserver.observe(lazyImage);
                        });

                        // Ponovno pokreni za nove slike nakon AJAX zahteva
                        $(document).ajaxComplete(function() {
                            const newLazyImages = document.querySelectorAll('img[data-src]');
                            newLazyImages.forEach(function(lazyImage) {
                                lazyImageObserver.observe(lazyImage);
                            });
                        });
                    });
                }
            })(jQuery);
        </script>
<?php
    }
}
add_action('wp_footer', 's7design_optimize_ajax_requests', 99);


