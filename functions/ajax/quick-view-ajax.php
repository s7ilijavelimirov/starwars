<?php

/**
 * AJAX funkcije za Quick View funkcionalnost - optimizovana verzija
 *
 * @package s7design
 */

// Sprečavanje direktnog pristupa
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dodaje Quick View dugme i inline podatke na WooCommerce proizvode
 */
function sw_add_quick_view_button()
{
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

    // HTML za Quick View dugme sa inline podacima
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
             aria-label="' . esc_attr__('Brzi pregled', 's7design') . '">';
    echo '<span class="sw-quick-view-icon"></span>';
    echo '<span class="sw-quick-view-text">' . esc_html__('Brzi pregled', 's7design') . '</span>';
    echo '</button>';
    echo '</div>';
}

/**
 * Dodaje modal za Quick View sa optimizovanim učitavanjem (bez dodatnih AJAX poziva)
 */
function sw_add_quick_view_modal()
{
    // Proveravamo da li smo na arhivi proizvoda ili kategoriji
    if (is_shop() || is_product_category() || is_product_tag()) {
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
                    const $image = $modal.find('.sw-quick-view-image');
                    const $price = $modal.find('.sw-quick-view-price');
                    const $desc = $modal.find('.sw-quick-view-description');
                    const $details = $modal.find('.sw-quick-view-details');
                    const $dimensions = $modal.find('.sw-quick-view-dimensions');
                    const $variations = $modal.find('.sw-quick-view-variations');

                    // Popuni modal osnovnim podacima
                    $title.html(productTitle);
                    $image.attr('src', productImage).attr('alt', productImageAlt);
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
                    $modal.fadeIn(300);
                    $('body').addClass('sw-quick-view-open');
                });

                // Zatvaranje modala
                $('.sw-quick-view-close, .sw-quick-view-overlay').on('click', function() {
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
                    $('#sw-quick-view-modal').fadeOut(200);
                    $('body').removeClass('sw-quick-view-open');
                }
            })(jQuery);
        </script>
<?php
    }
}

// Dodavanje dugmića i modala
add_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button', 15);
add_action('wp_footer', 'sw_add_quick_view_modal');
