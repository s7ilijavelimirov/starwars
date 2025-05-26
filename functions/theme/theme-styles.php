<?php

/**
 * OPTIMIZOVANI theme-styles.php - BEZ CRITICAL CSS
 * Fokus na conditional loading i performance
 */

function s7design_enqueue_styles()
{
    $dist_uri = get_template_directory_uri() . '/dist';
    $dist_dir = get_template_directory() . '/dist';

    // 1. GLAVNI CSS - samo jedan fajl za sve
    wp_enqueue_style(
        's7design-main',
        $dist_uri . '/css/frontend.min.css',
        [],
        filemtime($dist_dir . '/css/frontend.min.css'),
        'all'
    );

    // 2. Swiper CSS - SAMO gde treba
    if (is_front_page()) {
        wp_enqueue_style(
            'swiper-css',
            $dist_uri . '/css/swiper-bundle.min.css',
            [],
            '11.2.6',
            'all'
        );
    }

    // 3. Style.css - child tema
    wp_enqueue_style(
        's7design-style',
        get_stylesheet_uri(),
        ['s7design-main'],
        filemtime(get_stylesheet_directory() . '/style.css'),
        'all'
    );

    // 4. WooCommerce CSS - SAMO na WC stranicama
    if (class_exists('WooCommerce')) {
        // Ukloni default WC stilove SVUGDE
        wp_dequeue_style('woocommerce-general');
        wp_dequeue_style('woocommerce-layout');
        wp_dequeue_style('woocommerce-smallscreen');

        // Dodaj custom WC CSS SAMO na WC stranicama
        if (is_woocommerce() || is_cart() || is_checkout() || is_account_page() || is_shop()) {
            wp_enqueue_style(
                's7design-woocommerce',
                get_template_directory_uri() . '/woocommerce.css',
                ['s7design-main'],
                filemtime(get_template_directory() . '/woocommerce.css'),
                'all'
            );
        }
    }
}
add_action('wp_enqueue_scripts', 's7design_enqueue_styles');

/**
 * Inline kritični CSS SAMO za homepage hero
 */
function s7design_inline_homepage_css()
{
    if (is_front_page()) {
?>
        <style id="homepage-critical">
            .hero-section {
                min-height: 100vh;
                display: flex;
                align-items: center;
                justify-content: center
            }

            .carousel-container {
                width: 100%;
                max-width: 1200px;
                height: 600px;
                position: relative;
                overflow: hidden
            }

            .carousel-img {
                width: 100%;
                height: 100%;
                object-fit: cover
            }

            @media(max-width:768px) {
                .carousel-container {
                    height: 400px
                }
            }
        </style>
    <?php
    }
}
add_action('wp_head', 's7design_inline_homepage_css', 20);

/**
 * Inline shop grid optimizacija
 */
function s7design_inline_shop_css()
{
    if (is_shop() || is_product_category() || is_product_tag()) {
    ?>
        <style id="shop-critical">
            .woocommerce ul.products {
                display: grid !important;
                grid-template-columns: repeat(5, 1fr);
                gap: 20px;
                margin: 0;
                padding: 0;
                list-style: none
            }

            .woocommerce ul.products li.product {
                margin: 0 !important;
                width: 100% !important;
                border: 1px solid #ffdd55;
                border-radius: 10px;
                overflow: hidden;
                min-height: 300px
            }

            .woocommerce ul.products li.product img {
                width: 100%;
                height: 200px;
                object-fit: contain
            }

            @media(max-width:1400px) {
                .woocommerce ul.products {
                    grid-template-columns: repeat(4, 1fr)
                }
            }

            @media(max-width:992px) {
                .woocommerce ul.products {
                    grid-template-columns: repeat(3, 1fr)
                }
            }

            @media(max-width:768px) {
                .woocommerce ul.products {
                    grid-template-columns: repeat(2, 1fr);
                    gap: 15px
                }
            }
        </style>
<?php
    }
}
add_action('wp_head', 's7design_inline_shop_css', 20);

/**
 * Font preload optimizacija
 */
function s7design_preload_fonts()
{
    $uri = get_template_directory_uri();

    // Preload samo 2 najvažnija fonta
    echo '<link rel="preload" href="' . $uri . '/dist/fonts/Montserrat-Regular.woff2" as="font" type="font/woff2" crossorigin fetchpriority="high">' . "\n";
    echo '<link rel="preload" href="' . $uri . '/dist/fonts/Montserrat-Bold.woff2" as="font" type="font/woff2" crossorigin>' . "\n";
}
add_action('wp_head', 's7design_preload_fonts', 1);

/**
 * Ukloni nepotrebne externe fontove
 */
function s7design_remove_external_fonts()
{
    // Pronađi i ukloni Google Fonts
    global $wp_styles;
    if (isset($wp_styles->registered)) {
        foreach ($wp_styles->registered as $handle => $style) {
            if (isset($style->src) && (
                strpos($style->src, 'fonts.googleapis.com') !== false ||
                strpos($style->src, 'fonts.gstatic.com') !== false
            )) {
                wp_deregister_style($handle);
            }
        }
    }
}
add_action('wp_enqueue_scripts', 's7design_remove_external_fonts', 5);
?>