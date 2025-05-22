<?php

/**
 * AJAX funkcije za Quick View funkcionalnost - Optimizovana verzija
 * Uklonjen inline script da se izbegne duplikovanje
 *
 * @package s7design
 */

// Sprečavanje direktnog pristupa
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Dodaje Quick View dugme sa optimizovanim atributima
 */
function sw_add_quick_view_button()
{
    // Brze provere - izađi ako nismo na odgovarajućoj stranici
    if (is_front_page() || is_product() || is_singular('product')) {
        return;
    }

    if (!is_shop() && !is_product_category() && !is_product_tag() && !is_tax('pa_*')) {
        return;
    }

    global $product;
    if (!$product) return;

    // Cache osnovnih podataka
    static $cache = [];
    $product_id = $product->get_id();

    if (!isset($cache[$product_id])) {
        $cache[$product_id] = [
            'title' => $product->get_title(),
            'price_html' => $product->get_price_html(),
            'permalink' => $product->get_permalink(),
            'description' => wp_trim_words($product->get_short_description(), 20, '...'),
            'image_url' => wp_get_attachment_image_url($product->get_image_id(), 'woocommerce_single'),
            'image_alt' => get_post_meta($product->get_image_id(), '_wp_attachment_image_alt', true) ?: $product->get_title(),
            'dimensions' => sw_get_product_dimensions($product),
            'variations' => sw_get_product_variations($product)
        ];
    }

    $data = $cache[$product_id];

    // Kompaktan HTML output
    printf(
        '<div class="sw-quick-view-wrapper">
            <button type="button" class="sw-quick-view-button" 
                data-product-id="%d"
                data-product-title="%s"
                data-product-image="%s"
                data-product-image-alt="%s"
                data-product-price="%s"
                data-product-description="%s"
                data-product-permalink="%s"
                data-product-dimensions="%s"
                data-product-variations="%s"
                aria-label="%s" title="%s">
                <span class="sw-quick-view-icon">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 5C5.636 5 1 12 1 12C1 12 5.636 19 12 19C18.364 19 23 12 23 12C23 12 18.364 5 12 5Z" fill="none"></path>
                        <circle cx="12" cy="12" r="3" fill="none"></circle>
                    </svg>
                </span>
            </button>
        </div>',
        $product_id,
        esc_attr($data['title']),
        esc_url($data['image_url']),
        esc_attr($data['image_alt']),
        esc_attr($data['price_html']),
        esc_attr($data['description']),
        esc_url($data['permalink']),
        esc_attr($data['dimensions']),
        esc_attr($data['variations']),
        esc_attr__('Brzi pregled', 's7design'),
        esc_attr__('Brzi pregled', 's7design')
    );
}

/**
 * Helper funkcija za dimenzije
 */
function sw_get_product_dimensions($product)
{
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

    return implode(' | ', $dimensions);
}

/**
 * Helper funkcija za varijacije
 */
function sw_get_product_variations($product)
{
    if (!$product->is_type('variable')) {
        return '';
    }

    $attributes = $product->get_attributes();
    $variation_items = [];

    foreach ($attributes as $attribute) {
        if (!$attribute->get_variation()) continue;

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

    return implode('', $variation_items);
}

/**
 * Modal struktura - samo HTML, bez JS-a
 */
function sw_add_quick_view_modal()
{
    // Iste provere kao i za dugme
    if (is_front_page() || (!is_shop() && !is_product_category() && !is_product_tag() && !is_tax('pa_*'))) {
        return;
    }

    // Samo jednom dodaj modal na stranicu
    static $modal_added = false;
    if ($modal_added) return;
    $modal_added = true;

?>
    <div id="sw-quick-view-modal" class="sw-quick-view-modal" style="display: none;">
        <div class="sw-quick-view-overlay"></div>
        <div class="sw-quick-view-container">
            <div class="sw-quick-view-content">
                <div class="sw-quick-view-close">
                    <span class="sw-quick-view-close-icon">×</span>
                </div>
                <div class="sw-quick-view-inner">
                    <div class="sw-quick-view-product">
                        <div class="sw-quick-view-row">
                            <div class="sw-quick-view-images">
                                <div class="sw-image-zoom-container">
                                    <img src="" alt="" class="sw-quick-view-image main-image">
                                    <img src="" alt="" class="sw-quick-view-image zoom-overlay">
                                    <div class="sw-zoom-icon" aria-hidden="true">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <circle cx="11" cy="11" r="8"></circle>
                                            <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                        </svg>
                                    </div>
                                    <span class="sw-zoom-hint">Zoom</span>
                                </div>
                            </div>
                            <div class="sw-quick-view-summary">
                                <h2 class="sw-quick-view-title"></h2>
                                <div class="sw-quick-view-price"></div>
                                <div class="sw-quick-view-dimensions"></div>
                                <div class="sw-quick-view-variations"></div>
                                <div class="sw-quick-view-description"></div>
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
<?php
}

// Dodavanje hook-ova
add_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button', 15);
add_action('wp_footer', 'sw_add_quick_view_modal', 5); // Ranije učitavanje

/**
 * Ukloni Quick View sa related products
 */
function sw_remove_quick_view_from_related_products()
{
    if (is_product()) {
        remove_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button', 15);
    }
}
add_action('woocommerce_before_single_product_summary', 'sw_remove_quick_view_from_related_products', 5);
