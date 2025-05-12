<?php

/**
 * Theme styles - optimizovano za minimalan Bootstrap 5.3 setup
 *
 * @package s7design
 */

function s7design_load_styles()
{
    // Definiši putanju do dist foldera
    $dist_path = get_template_directory_uri() . '/dist';
    $dist_dir = get_template_directory() . '/dist';

    // Glavni CSS fajl teme koji sadrži bootstrap kroz import
    wp_register_style(
        's7design-main',
        $dist_path . '/css/frontend.min.css',
        array(),
        file_exists($dist_dir . '/css/frontend.min.css') ? filemtime($dist_dir . '/css/frontend.min.css') : null,
        'all'
    );
    wp_enqueue_style('s7design-main');

    // Glavni stylesheet (style.css) - samo ako sadrži dodatne stilove koji nisu u glavnom CSS-u
    wp_enqueue_style(
        's7design-style',
        get_stylesheet_uri(),
        array('s7design-main'), // Zavisi od glavnog CSS-a
        file_exists(get_stylesheet_directory() . '/style.css') ? filemtime(get_stylesheet_directory() . '/style.css') : null,
        'all'
    );

    // WooCommerce stilovi - samo na WooCommerce stranicama
    if (class_exists('WooCommerce') && (is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_shop())) {
        wp_register_style(
            's7design-woocommerce',
            get_template_directory_uri() . '/woocommerce.css',
            array('s7design-main'), // Dodajemo zavisnost od glavnog CSS-a
            file_exists(get_template_directory() . '/woocommerce.css') ? filemtime(get_template_directory() . '/woocommerce.css') : null,
            'all'
        );
        wp_enqueue_style('s7design-woocommerce');
    }

    // Dodajemo preload ključnih resursa
    add_action('wp_head', 's7design_preload_resources', 1);
}
add_action('wp_enqueue_scripts', 's7design_load_styles');

/**
 * Optimizovani preload ključnih fontova
 */
function s7design_preload_resources()
{
    // Preload CSS i JS
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/dist/css/frontend.min.css" as="style">';
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/dist/js/bootstrap.bundle.min.js" as="script">';
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/dist/js/frontend-build.js" as="script">';

    // Preload kritičnih fontova sa fetchpriority="high" za glavni Regular font
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/dist/fonts/Montserrat-Regular.woff2" as="font" type="font/woff2" crossorigin fetchpriority="high">';

    // Preload ostalih fontova (Bold i Medium su takođe važni, ali sa normalnim prioritetom)
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/dist/fonts/Montserrat-Bold.woff2" as="font" type="font/woff2" crossorigin>';
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/dist/fonts/Montserrat-Medium.woff2" as="font" type="font/woff2" crossorigin>';

    // Ostali fontovi se dinamički učitavaju kada su potrebni
}

/**
 * Dodajemo font-display: swap za bolje performanse učitavanja fontova
 */
function s7design_font_display_swap()
{
?>
    <style>
        /* Kritični CSS koji postavlja font-family pre učitavanja glavnog CSS-a */
        body,
        h1,
        h2,
        h3,
        h4,
        h5,
        h6,
        p,
        button,
        input,
        select,
        textarea {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }

        /* Hack za Safari koji ponekad ima probleme sa font-display: swap */
        @supports (-webkit-overflow-scrolling: touch) {
            @font-face {
                font-family: 'Montserrat';
                font-display: swap;
            }
        }
    </style>
<?php
}
add_action('wp_head', 's7design_font_display_swap', 1);

/**
 * Optimizacija učitavanja slika
 */
function s7design_optimize_images()
{
    // Dodaj podršku za native lazy loading
    add_filter('wp_img_tag_add_loading_attr', function ($value, $image, $context) {
        if (!$value || $value === 'eager') {
            // Dodaj lazy loading svim slikama osim header logoa
            if (strpos($image, 'header-logo') === false && strpos($image, 'logo-') === false) {
                return 'lazy';
            }
        }
        return $value;
    }, 10, 3);

    // Dodaj srcset i sizes atribute za responsive slike
    add_filter('wp_calculate_image_srcset', function ($sources, $size_array, $image_src, $image_meta, $attachment_id) {
        return $sources;
    }, 10, 5);
}
add_action('after_setup_theme', 's7design_optimize_images');

/**
 * Dodavanje font preconnect za dodatno poboljšanje učitavanja fontova (opciono)
 */
function s7design_add_font_preconnect()
{
    // Preconnect za Google fontove ako ih koristiš
    // echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>';
    // echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';

    // Preconnect za Typekit fontove ako ih koristiš
    // echo '<link rel="preconnect" href="https://use.typekit.net" crossorigin>';
}
// add_action('wp_head', 's7design_add_font_preconnect', 1); // Zakomentarisano ako ne koristiš eksterne fontove