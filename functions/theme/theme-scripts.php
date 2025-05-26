<?php

/**
 * ULTRA OPTIMIZOVANI theme-scripts.php
 * Fokus na conditional loading i defer
 */

function s7design_enqueue_scripts()
{
    $dist_uri = get_template_directory_uri() . '/dist';
    $dist_dir = get_template_directory() . '/dist';

    // jQuery ostaje - potreban je
    wp_enqueue_script('jquery');

    // 1. BOOTSTRAP - svugde ali defer
    wp_enqueue_script(
        's7design-bootstrap',
        $dist_uri . '/js/bootstrap.bundle.min.js',
        ['jquery'],
        filemtime($dist_dir . '/js/bootstrap.bundle.min.js'),
        true
    );

    // 2. FRONTEND JS - glavni
    wp_enqueue_script(
        's7design-frontend',
        $dist_uri . '/js/frontend-build.js',
        ['jquery', 's7design-bootstrap'],
        filemtime($dist_dir . '/js/frontend-build.js'),
        true
    );

    // 3. SWIPER - SAMO gde treba
    if (is_front_page()) {
        wp_enqueue_script(
            's7design-swiper',
            $dist_uri . '/js/swiper-bundle.min.js',
            [],
            filemtime($dist_dir . '/js/swiper-bundle.min.js'),
            true
        );

        wp_enqueue_script(
            's7design-swiper-init',
            $dist_uri . '/js/swiper-init-build.js',
            ['s7design-swiper'],
            filemtime($dist_dir . '/js/swiper-init-build.js'),
            true
        );
    }

    // 4. WOOCOMMERCE skripte - conditional
    s7design_conditional_wc_scripts($dist_uri, $dist_dir);

    // 5. Minimalna AJAX lokalizacija
    wp_localize_script('s7design-frontend', 'sw_ajax', [
        'url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('sw_nonce')
    ]);
}
add_action('wp_enqueue_scripts', 's7design_enqueue_scripts');

/**
 * WooCommerce skripte - SAMO gde treba
 */
function s7design_conditional_wc_scripts($dist_uri, $dist_dir)
{
    if (!class_exists('WooCommerce')) return;

    // SHOP stranice - Quick View + Load More
    if (is_shop() || is_product_category() || is_product_tag()) {
        wp_enqueue_script(
            's7design-quick-view',
            $dist_uri . '/js/quick-view-build.js',
            ['jquery'],
            filemtime($dist_dir . '/js/quick-view-build.js'),
            true
        );

        wp_enqueue_script(
            's7design-load-more',
            $dist_uri . '/js/load-more-build.js',
            ['jquery'],
            filemtime($dist_dir . '/js/load-more-build.js'),
            true
        );

        // Lokalizacija za Load More
        wp_localize_script('s7design-load-more', 'sw_load_more', [
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sw_load_more_nonce'),
            'page' => max(1, get_query_var('paged')),
            'loading' => 'Učitavanje...',
            'no_more' => 'Nema više proizvoda'
        ]);

        // Quick View lokalizacija
        wp_localize_script('s7design-quick-view', 'sw_quick_view', [
            'url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('sw_quick_view_nonce')
        ]);
    }
}

/**
 * DEFER svih ne-kritičnih skripti
 */
function s7design_defer_scripts($tag, $handle, $src)
{
    // Svi skripti osim jQuery treba da budu defer
    $defer_handles = [
        's7design-bootstrap',
        's7design-frontend',
        's7design-swiper',
        's7design-swiper-init',
        's7design-quick-view',
        's7design-load-more',
        's7design-checkout',
        's7design-product'
    ];

    if (in_array($handle, $defer_handles)) {
        return str_replace(' src', ' defer src', $tag);
    }

    return $tag;
}
add_filter('script_loader_tag', 's7design_defer_scripts', 10, 3);

/**
 * Preload SAMO najkritičnijih skripti
 */
function s7design_preload_scripts()
{
    $uri = get_template_directory_uri() . '/dist';

    // Glavni JS
    echo '<link rel="preload" href="' . $uri . '/js/frontend-build.js" as="script">' . "\n";

    // Bootstrap
    echo '<link rel="preload" href="' . $uri . '/js/bootstrap.bundle.min.js" as="script">' . "\n";

    // Swiper SAMO na stranicama gde treba
    if (is_front_page() || is_shop() || is_product_category()) {
        echo '<link rel="preload" href="' . $uri . '/js/swiper-bundle.min.js" as="script">' . "\n";
    }
}
add_action('wp_head', 's7design_preload_scripts', 2);

/**
 * Ukloni POTPUNO nepotrebne skripte
 */
function s7design_remove_bloat_scripts()
{
    if (!is_admin()) {
        // WordPress bloat
        wp_deregister_script('wp-embed');
        wp_deregister_script('wp-emoji-release.min.js');

        // Ukloni heartbeat na frontend-u
        wp_deregister_script('heartbeat');

        // Ukloni comment-reply ako nema komentara
        if (!is_single() || !comments_open()) {
            wp_deregister_script('comment-reply');
        }
    }
}
add_action('wp_enqueue_scripts', 's7design_remove_bloat_scripts', 100);

/**
 * Async loading za manje važne skripte
 */
function s7design_async_scripts()
{
?>
    <script>
        // Odloženo učitavanje analytics i drugih ne-kritičnih skripti
        window.addEventListener('load', function() {
            setTimeout(function() {
                // Ovde možeš dodati Google Analytics, Facebook Pixel, itd.
                // loadScript('https://www.google-analytics.com/analytics.js');
            }, 3000); // 3 sekunde nakon load
        });

        function loadScript(src) {
            var script = document.createElement('script');
            script.src = src;
            script.async = true;
            document.head.appendChild(script);
        }
    </script>
<?php
}
add_action('wp_footer', 's7design_async_scripts', 99);
?>