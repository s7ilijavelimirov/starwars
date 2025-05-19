<?php
/**
 * AJAX funkcije za Quick View funkcionalnost - optimizovana verzija
 * sa podrškom za dohvatanje varijacija i paginaciju
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
    // Proverimo da li treba prikazati Quick View
    // Preskačemo na single product stranici ili na naslovnoj
    if (is_product() || is_singular('product') || is_front_page()) {
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

    // Slika proizvoda - osigurava HTTPS protokol
    $image_id = $product->get_image_id();
    $image_url = wp_get_attachment_image_url($image_id, 'woocommerce_single');
    // HTTPS fix - konverzija HTTP u HTTPS
    $image_url = str_replace('http://', 'https://', $image_url);
    $image_alt = get_post_meta($image_id, '_wp_attachment_image_alt', true) ?: $product_title;

    // Podaci o varijacijama
    $variations_data = '';
    if (method_exists($product, 'is_type') && $product->is_type('variable')) {
        $variations = $product->get_available_variations();
        $attributes = $product->get_attributes();
        $variation_items = [];

        foreach ($attributes as $attribute) {
            if (method_exists($attribute, 'get_variation') && $attribute->get_variation()) {
                $attribute_name = wc_attribute_label($attribute->get_name());
                $attribute_values = [];

                if (method_exists($attribute, 'is_taxonomy') && $attribute->is_taxonomy()) {
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
    if (method_exists($product, 'get_length') && $product->get_length()) {
        $dimensions[] = 'Dužina: ' . $product->get_length() . get_option('woocommerce_dimension_unit');
    }
    if (method_exists($product, 'get_width') && $product->get_width()) {
        $dimensions[] = 'Širina: ' . $product->get_width() . get_option('woocommerce_dimension_unit');
    }
    if (method_exists($product, 'get_height') && $product->get_height()) {
        $dimensions[] = 'Visina: ' . $product->get_height() . get_option('woocommerce_dimension_unit');
    }
    if (method_exists($product, 'get_weight') && $product->get_weight()) {
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
 * Dodaje modal za Quick View
 */
function sw_add_quick_view_modal()
{
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
<?php
}

/**
 * AJAX akcija za dohvatanje varijacija proizvoda
 * Koristi se za load more funkcionalnost
 */
function sw_fetch_product_variations() {
    // Sigurnosna provera
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sw_load_more_nonce')) {
        wp_send_json_error(['message' => 'Sigurnosna provera nije uspela']);
        die();
    }
    
    // Možemo dobiti jedan ID ili više ID-eva
    $product_ids = isset($_POST['product_ids']) ? $_POST['product_ids'] : [];
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    
    // Ako je pojedinačni ID, obradi ga standardno
    if ($product_id && empty($product_ids)) {
        $product_ids = [$product_id];
    }
    
    // Ako nema ID-jeva, vrati grešku
    if (empty($product_ids)) {
        wp_send_json_error(['message' => 'Nedostaje ID proizvoda']);
        die();
    }
    
    $result = [];
    
    // Obradi svaki proizvod
    foreach ($product_ids as $id) {
        $product = wc_get_product($id);
        if (!$product) continue;
        
        $variations_data = '';
        
        if (method_exists($product, 'is_type') && $product->is_type('variable')) {
            try {
                $attributes = $product->get_attributes();
                $variation_items = [];

                foreach ($attributes as $attribute) {
                    if (method_exists($attribute, 'get_variation') && $attribute->get_variation()) {
                        $attribute_name = wc_attribute_label($attribute->get_name());
                        $attribute_values = [];

                        if (method_exists($attribute, 'is_taxonomy') && $attribute->is_taxonomy()) {
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
            } catch (Exception $e) {
                $variations_data = '';
            }
        }
        
        $result[$id] = $variations_data;
    }
    
    wp_send_json_success(['variations' => $result]);
    die();
}

// Registrovanje AJAX akcije za varijacije
add_action('wp_ajax_fetch_product_variations', 'sw_fetch_product_variations');
add_action('wp_ajax_nopriv_fetch_product_variations', 'sw_fetch_product_variations');

// Dodajemo akcije za prikazivanje dugmeta i modala
add_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button', 15);
add_action('wp_footer', 'sw_add_quick_view_modal');