<?php

/**
 * AJAX funkcije za Quick View funkcionalnost - optimizovana verzija
 * sa ograničenjem prikaza samo na određenim stranicama
 *
 * @package s7design
 */

// Sprečavanje direktnog pristupa
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dodaje Quick View dugme i inline podatke na WooCommerce proizvode
 * samo na shop, category, attribute i tag stranicama
 */
function sw_add_quick_view_button()
{
    // Proverimo da li smo na dozvoljenoj stranici
    // Dozvoljavamo samo shop, kategorije, atribute i tagove, a isključujemo frontpage i single product
    if (!is_shop() && !is_product_category() && !is_product_tag() && !is_tax('pa_*')) {
        return; // Izađi iz funkcije ako nismo na željenoj stranici
    }

    // Provera da nismo na single product stranici ili related products sekciji
    if (is_product() || is_singular('product')) {
        return;
    }

    // Provera da nismo na naslovnoj stranici
    if (is_front_page()) {
        return;
    }

    global $product;

    if (!$product) return;

    // Osnovni podaci o proizvodu
    $product_id = $product->get_id();
    $product_title = $product->get_title();
    $product_price_html = $product->get_price_html();
    $product_permalink = $product->get_permalink();
    $product_short_description = wp_trim_words($product->get_short_description(), 20, '...');

    // Slika proizvoda
    $image_id = $product->get_image_id();
    $image_url = wp_get_attachment_image_url($image_id, 'woocommerce_single');
    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $product_title;

    // Podaci o varijacijama
    $variations_data = '';
    if ($product->is_type('variable')) {
        $variations = $product->get_available_variations();
        $attributes = $product->get_attributes();
        $variation_items = [];

        foreach ($attributes as $attribute) {
            if ($attribute->get_variation()) {
                $attribute_name = wc_attribute_label($attribute->get_name());
                $attribute_values = [];

                if ($attribute->is_taxonomy()) {
                    $terms = $attribute->get_terms();
                    foreach ($terms as $term) {
                        $attribute_values[] = $term->name;
                    }
                } else {
                    $attribute_values = $attribute->get_options();
                }

                if (!empty($attribute_values)) {
                    $variation_items[] = '<span class="sw-variation-item">' .
                        $attribute_name . ': ' . implode(', ', $attribute_values) .
                        '</span>';
                }
            }
        }

        $variations_data = !empty($variation_items) ? implode('', $variation_items) : '';
    }

    // Informacije o dimenzijama i težini
    $dimensions = [];
    if ($product->get_length()) {
        $dimensions[] = 'Dužina: ' . $product->get_length() . get_option('woocommerce_dimension_unit');
    }
    if ($product->get_width()) {
        $dimensions[] = 'Širina: ' . $product->get_width() . get_option('woocommerce_dimension_unit');
    }
    if ($product->get_height()) {
        $dimensions[] = 'Visina: ' . $product->get_height() . get_option('woocommerce_dimension_unit');
    }
    if ($product->get_weight()) {
        $dimensions[] = 'Težina: ' . $product->get_weight() . get_option('woocommerce_weight_unit');
    }
    $dimensions_str = !empty($dimensions) ? implode(' | ', $dimensions) : '';

    // HTML za Quick View dugme sa inline podacima - SAMO SA IKONICOM, BEZ TEKSTA
    echo '<div class="sw-quick-view-wrapper">';
    echo '<button type="button" 
             class="sw-quick-view-button quick-view-button" 
             data-product-id="' . esc_attr($product_id) . '" 
             data-product-title="' . esc_attr($product_title) . '"
             data-product-image="' . esc_url($image_url) . '"
             data-product-image-alt="' . esc_attr($image_alt) . '"
             data-product-price="' . esc_attr($product_price_html) . '"
             data-product-description="' . esc_attr($product_short_description) . '"
             data-product-permalink="' . esc_url($product_permalink) . '"
             data-product-dimensions="' . esc_attr($dimensions_str) . '"
             data-product-variations="' . esc_attr($variations_data) . '"
             aria-label="' . esc_attr__('Brzi pregled', 's7design') . '"
             title="' . esc_attr__('Brzi pregled', 's7design') . '">';

    // Samo ikonica oka - SVG
    echo '<span class="sw-quick-view-icon">';
    // SVG ikonica OKA umesto lupe
    echo '<svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
             <path d="M12 5C5.636 5 1 12 1 12C1 12 5.636 19 12 19C18.364 19 23 12 23 12C23 12 18.364 5 12 5Z" fill="none"></path>
             <circle cx="12" cy="12" r="3" fill="none"></circle>
          </svg>';
    echo '</span>';
    echo '</button>';
    echo '</div>';
}

/**
 * Dodaje modal za Quick View sa optimizovanim učitavanjem (bez dodatnih AJAX poziva)
 * Prikazuje se samo na shop, category, attribute i tag stranicama
 */
function sw_add_quick_view_modal()
{
    // Proveravamo da li smo na dozvoljenoj stranici
    if (!is_shop() && !is_product_category() && !is_product_tag() && !is_tax('pa_*')) {
        return; // Izađi iz funkcije ako nismo na željenoj stranici
    }

    // Dodatna provera da nismo na naslovnoj stranici
    if (is_front_page()) {
        return;
    }

    // HTML struktura za modal
?>
    <div id="sw-quick-view-modal" class="sw-quick-view-modal" style="display: none;">
        <div class="sw-quick-view-overlay"></div>
        <div class="sw-quick-view-container">
            <div class="sw-quick-view-content">
                <div class="sw-quick-view-close">
                    <span class="sw-quick-view-close-icon">×</span>
                </div>
                <div class="sw-quick-view-inner">
                    <!-- Sadržaj će biti učitan na klik -->
                    <div class="sw-quick-view-product">
                        <div class="sw-quick-view-row">
                            <!-- Leva kolona: Slika proizvoda -->
                            <div class="sw-quick-view-images">
                                <div class="sw-image-zoom-container">
                                    <img src="" alt="" class="sw-quick-view-image main-image">
                                    <img src="" alt="" class="sw-quick-view-image zoom-overlay">

                                    <!-- SVG Ikona lupe umesto CSS :before/:after -->
                                    <div class="sw-zoom-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                        </svg>
                                    </div>

                                    <span class="sw-zoom-hint">Zoom</span>
                                </div>
                            </div>

                            <!-- Desna kolona: Informacije o proizvodu -->
                            <div class="sw-quick-view-summary">
                                <h2 class="sw-quick-view-title"></h2>

                                <!-- Cena -->
                                <div class="sw-quick-view-price"></div>

                                <!-- Dimenzije ako postoje -->
                                <div class="sw-quick-view-dimensions"></div>

                                <!-- Varijacije ako postoje -->
                                <div class="sw-quick-view-variations"></div>

                                <!-- Kratak opis -->
                                <div class="sw-quick-view-description"></div>

                                <!-- Link ka proizvodu -->
                                <div class="sw-quick-view-actions">
                                    <a href="#" class="sw-quick-view-details button">
                                        <?php esc_html_e('Pogledajte detalje', 's7design'); ?>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Pojednostavljeno Quick View - samo informativno, bez AJAX-a
        (function($) {
            // Obrada klika na Quick View dugme
            $(document).on('click', '.sw-quick-view-button', function(e) {
                e.preventDefault();
                e.stopPropagation();

                const $button = $(this);
                const productId = $button.data('product-id');

                if (!productId) return;

                // Učitaj osnovne podatke iz data atributa
                const productTitle = $button.data('product-title');
                const productImage = $button.data('product-image');
                const productImageAlt = $button.data('product-image-alt');
                const productPrice = $button.data('product-price');
                const productDesc = $button.data('product-description');
                const productPermalink = $button.data('product-permalink');
                const productDimensions = $button.data('product-dimensions');
                const productVariations = $button.data('product-variations');

                // Reference na modal
                const $modal = $('#sw-quick-view-modal');
                const $title = $modal.find('.sw-quick-view-title');
                const $mainImage = $modal.find('.sw-quick-view-image.main-image');
                const $zoomOverlay = $modal.find('.sw-quick-view-image.zoom-overlay');
                const $price = $modal.find('.sw-quick-view-price');
                const $desc = $modal.find('.sw-quick-view-description');
                const $details = $modal.find('.sw-quick-view-details');
                const $dimensions = $modal.find('.sw-quick-view-dimensions');
                const $variations = $modal.find('.sw-quick-view-variations');

                // Popuni modal osnovnim podacima
                $title.html(productTitle);

                // Postavi slike za glavni prikaz i za zoom
                $mainImage.attr({
                    'src': productImage,
                    'alt': productImageAlt
                });

                $zoomOverlay.attr({
                    'src': productImage,
                    'alt': productImageAlt
                });

                $price.html(productPrice);
                $desc.html(productDesc);
                $details.attr('href', productPermalink);

                // Dimenzije ako postoje
                if (productDimensions) {
                    $dimensions.html('<div class="sw-dimensions-title">Dimenzije:</div><div class="sw-dimensions-data">' + productDimensions + '</div>');
                    $dimensions.show();
                } else {
                    $dimensions.hide();
                }

                // Varijacije ako postoje
                if (productVariations) {
                    $variations.html('<div class="sw-variations-title">Varijacije:</div><div class="sw-variations-data">' + productVariations + '</div>');
                    $variations.show();
                } else {
                    $variations.hide();
                }

                // Prikaži modal
                $modal.fadeIn(200);
                $('body').addClass('sw-quick-view-open');

                // Inicijalizuj zoom efekat
                initImageZoom();
            });

            // Zatvaranje modala
            $(document).on('click', '.sw-quick-view-close, .sw-quick-view-overlay', function() {
                closeQuickViewModal();
            });

            // Zatvaranje modala na escape
            $(document).on('keyup', function(e) {
                if (e.key === 'Escape' && $('#sw-quick-view-modal').is(':visible')) {
                    closeQuickViewModal();
                }
            });

            // Funkcija za zatvaranje modala
            function closeQuickViewModal() {
                $('#sw-quick-view-modal').fadeOut(150);
                $('body').removeClass('sw-quick-view-open');
            }

            /**
             * Implementacija zoom efekta za slike
             */
            function initImageZoom() {
                const $container = $('.sw-image-zoom-container');
                if (!$container.length) return;

                const $mainImage = $container.find('.sw-quick-view-image.main-image');
                const $zoomOverlay = $container.find('.sw-quick-view-image.zoom-overlay');

                // Zoom faktor - koliko puta će slika biti uvećana
                const zoomFactor = 1.8;

                // Inicijalno sakrij zoom overlay
                $zoomOverlay.css({
                    'transform': `scale(${zoomFactor})`,
                    'opacity': 0
                });

                // Aktiviraj zoom na hover
                $container.on('mouseenter', function() {
                    $container.addClass('sw-image-zoom-active');
                });

                // Deaktiviraj zoom na mouseout
                $container.on('mouseleave', function() {
                    $container.removeClass('sw-image-zoom-active');

                    // Resetuj zoom overlay
                    $zoomOverlay.css({
                        'transform': `scale(${zoomFactor})`,
                        'opacity': 0,
                        'transform-origin': '50% 50%'
                    });
                });

                // Prati kretanje miša za zoom efekat
                $container.on('mousemove', function(e) {
                    if (!$container.hasClass('sw-image-zoom-active')) return;

                    const containerWidth = $container.outerWidth();
                    const containerHeight = $container.outerHeight();

                    // Pozicija miša u containeru (0-1)
                    const xRatio = (e.pageX - $container.offset().left) / containerWidth;
                    const yRatio = (e.pageY - $container.offset().top) / containerHeight;

                    // Izračunaj x i y pomak za zoom overlay
                    const xOffset = Math.max(0, Math.min(100, xRatio * 100));
                    const yOffset = Math.max(0, Math.min(100, yRatio * 100));

                    // Primeni transformaciju na zoom overlay
                    $zoomOverlay.css({
                        'transform-origin': `${xOffset}% ${yOffset}%`,
                        'transform': `scale(${zoomFactor})`,
                        'opacity': 1
                    });
                });
            }
        })(jQuery);
    </script>
<?php
}

/**
 * Funkcija koja uklanja Quick View dugme sa related products sekcije
 * Koristimo je kao proveru da li smo u "related products" sekciji
 */
function sw_remove_quick_view_from_related_products()
{
    if (!is_product()) {
        return;
    }

    // Uklanjamo akciju samo za related products
    remove_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button', 15);
}
add_action('woocommerce_before_single_product_summary', 'sw_remove_quick_view_from_related_products', 5);

// Dodajemo akcije za prikazivanje dugmeta i modala, ali samo na odgovarajućim stranicama
add_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button', 15);
add_action('wp_footer', 'sw_add_quick_view_modal');

/**
 * Dodatno uklanjanje Quick View funkcionalnosti sa naslovne stranice
 */
function sw_maybe_remove_quick_view_from_frontpage()
{
    if (is_front_page()) {
        remove_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button', 15);
    }
}
add_action('wp', 'sw_maybe_remove_quick_view_from_frontpage');
