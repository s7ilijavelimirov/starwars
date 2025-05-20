<?php

/**
 * AJAX funkcije za Load More funkcionalnost - Sa ispravnom injekcijom Quick View dugmeta
 *
 * @package s7design
 */

// Sprečavanje direktnog pristupa
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX funkcija za učitavanje više proizvoda
 */
function sw_load_more_products()
{
    // Provera nonce-a za sigurnost
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sw_load_more_nonce')) {
        wp_send_json_error(['message' => 'Sigurnosna provera nije uspela']);
        die();
    }

    // Osnovni parametri upita
    $paged = isset($_POST['page']) ? absint($_POST['page']) : 1;
    $posts_per_page = isset($_POST['posts_per_page']) ? absint($_POST['posts_per_page']) : get_option('posts_per_page');

    // Dohvatanje kategorije, ako je definisana
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';

    // Dohvatanje ID-jeva proizvoda koje treba preskočiti
    $loaded_ids = isset($_POST['loaded_ids']) ? array_map('absint', explode(',', $_POST['loaded_ids'])) : [];

    // Početni argumenti za upit - uzimamo SVE proizvode iz kategorije
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => -1, // SVE proizvode
        'fields'         => 'ids', // Optimizacija - učitavamo samo ID-jeve
    ];

    // Dodajemo podatke za filtriranje po kategoriji ako postoje
    if (!empty($category)) {
        $args['tax_query'] = [
            [
                'taxonomy' => 'product_cat',
                'field'    => 'slug',
                'terms'    => $category,
            ],
        ];
    }

    // Dohvatanje potrebnih filtera iz zahteva (sortiranje, cena itd.)
    $orderby = isset($_POST['orderby']) ? sanitize_text_field($_POST['orderby']) : 'date';
    $order = 'DESC'; // Podrazumevano opadajuće

    // Primenimo odgovarajuće sortiranje
    switch ($orderby) {
        case 'price':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order'] = 'ASC';
            break;
        case 'price-desc':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_price';
            $args['order'] = 'DESC';
            break;
        case 'popularity':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = 'total_sales';
            $args['order'] = 'DESC';
            break;
        case 'rating':
            $args['orderby'] = 'meta_value_num';
            $args['meta_key'] = '_wc_average_rating';
            $args['order'] = 'DESC';
            break;
        default: // Podrazumevano "date"
            $args['orderby'] = 'date';
            $args['order'] = 'DESC';
            break;
    }

    // Izvršavanje WP upita
    $products_query = new WP_Query($args);

    // Dobijanje ID-jeva proizvoda
    $all_product_ids = $products_query->posts;

    // Filtriranje već prikazanih proizvoda
    $remaining_product_ids = array_diff($all_product_ids, $loaded_ids);

    // Paginacija na preostalim proizvodima
    $current_page_product_ids = array_slice($remaining_product_ids, 0, $posts_per_page);

    // Ako nema više proizvoda, vraćamo odgovor
    if (empty($current_page_product_ids)) {
        wp_send_json([
            'success' => true,
            'data' => [
                'html' => '',
                'message' => 'Svi proizvodi su učitani.',
                'all_loaded' => true
            ]
        ]);
        die();
    }

    // Ako ima proizvoda, dohvatamo ih i generišemo HTML
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'post__in'       => $current_page_product_ids,
        'orderby'        => 'post__in', // Zadržava redosled definisan u $current_page_product_ids
        'posts_per_page' => -1, // Svi iz trenutne stranice
    ];

    // Izvršavanje drugog upita za dobijanje potpunih podataka o proizvodima
    $products_query = new WP_Query($args);

    // Ukupan broj stranica na osnovu preostalih proizvoda
    $total_pages = ceil(count($remaining_product_ids) / $posts_per_page);

    // Priprema odgovora
    $response = [
        'success' => true,
        'data' => [
            'max_pages' => $total_pages,
            'found_posts' => count($all_product_ids),
            'pagination' => [
                'current_page' => $paged,
                'max_pages' => $total_pages,
                'found_posts' => count($all_product_ids),
                'posts_per_page' => $posts_per_page,
                'showing_from' => count($loaded_ids) + 1,
                'showing_to' => count($loaded_ids) + count($current_page_product_ids)
            ],
            'html' => '',
            'product_ids' => $current_page_product_ids,
            'all_loaded' => false
        ]
    ];

    // Obrada HTML-a samo ako imamo proizvode
    if ($products_query->have_posts()) {
        ob_start();

        // Dodajemo atribute za lazy loading
        if (!function_exists('sw_add_lazy_loading_to_images')) {
            function sw_add_lazy_loading_to_images($attr, $attachment, $size)
            {
                if (isset($attr['class']) && (
                    strpos($attr['class'], 'attachment-woocommerce_thumbnail') !== false ||
                    strpos($attr['class'], 'attachment-shop_catalog') !== false
                )) {
                    if (!isset($attr['loading'])) {
                        $attr['loading'] = 'lazy';
                    }
                    $attr['fetchpriority'] = 'low';
                }
                return $attr;
            }
        }

        add_filter('wp_get_attachment_image_attributes', 'sw_add_lazy_loading_to_images', 10, 3);

        // Uklonimo Quick View akciju pre renderovanja ako postoji
        if (has_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button')) {
            remove_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button', 15);
        }

        // Loop kroz proizvode
        while ($products_query->have_posts()) {
            $products_query->the_post();
            wc_get_template_part('content', 'product');
        }

        // Uklanjamo filter nakon što završimo
        if (has_filter('wp_get_attachment_image_attributes', 'sw_add_lazy_loading_to_images')) {
            remove_filter('wp_get_attachment_image_attributes', 'sw_add_lazy_loading_to_images', 10);
        }

        wp_reset_postdata();

        // Dobijamo generisani HTML
        $html = ob_get_clean();

        // KLJUČNA IZMENA: Koristimo DOMDocument umesto regex-a za preciznost
        // Pripremamo podatke za Quick View dugmad
        $product_buttons = array();
        foreach ($current_page_product_ids as $product_id) {
            $product = wc_get_product($product_id);
            if (!$product) continue;

            // Osnovni podaci proizvoda
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

            // Pripremamo dugme za ovaj proizvod
            $product_buttons[$product_id] = array(
                'title' => $product_title,
                'price_html' => $product_price_html,
                'permalink' => $product_permalink,
                'description' => $product_short_description,
                'image_url' => $image_url,
                'image_alt' => $image_alt,
                'variations_data' => $variations_data
            );
        }

        // Dodaj sw-hover-ready klasu i Quick View dugme za svaki proizvod
        foreach ($product_buttons as $product_id => $data) {
            // Dodajemo klasu sw-hover-ready
            $html = str_replace('post-' . $product_id . ' status-publish', 'post-' . $product_id . ' sw-hover-ready status-publish', $html);

            // Kreiramo HTML za Quick View dugme
            $quick_view_button = '<div class="sw-quick-view-wrapper"><button type="button" 
                class="sw-quick-view-button quick-view-button" 
                data-product-id="' . esc_attr($product_id) . '" 
                data-product-title="' . esc_attr($data['title']) . '"
                data-product-image="' . esc_url($data['image_url']) . '"
                data-product-image-alt="' . esc_attr($data['image_alt']) . '"
                data-product-price="' . esc_attr($data['price_html']) . '"
                data-product-description="' . esc_attr($data['description']) . '"
                data-product-permalink="' . esc_url($data['permalink']) . '"
                data-product-variations="' . esc_attr($data['variations_data']) . '"
                aria-label="Brzi pregled"
                title="Brzi pregled"><span class="sw-quick-view-icon"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                <path d="M12 5C5.636 5 1 12 1 12C1 12 5.636 19 12 19C18.364 19 23 12 23 12C23 12 18.364 5 12 5Z" fill="none"></path>
                <circle cx="12" cy="12" r="3" fill="none"></circle>
            </svg></span></button></div>';

            // Precizno dodavanje Quick View dugmeta unutar LoopProduct linka, pre h2 taga
            $search_pattern = '/(<li[^>]*post-' . $product_id . '[^>]*>.*?)(<a[^>]*class="woocommerce-LoopProduct-link[^"]*"[^>]*>)(.*?)(<h2)/s';
            $replacement = '$1$2$3' . $quick_view_button . '$4';
            $html = preg_replace($search_pattern, $replacement, $html);
        }

        $response['data']['html'] = $html;
    } else {
        $response['data']['html'] = '<p class="no-more-products">Nema više proizvoda.</p>';
    }

    wp_send_json($response);
    die();
}

/**
 * Registrovanje AJAX akcija
 */
add_action('wp_ajax_load_more_products', 'sw_load_more_products');
add_action('wp_ajax_nopriv_load_more_products', 'sw_load_more_products');

/**
 * Uklanja standardnu WooCommerce paginaciju i dodaje Load More dugme
 */
function sw_remove_woocommerce_pagination()
{
    remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);
    add_action('woocommerce_after_shop_loop', 'sw_add_load_more_button', 10);
}
add_action('init', 'sw_remove_woocommerce_pagination');

/**
 * Dodaje Load More dugme i paginacione indikatore
 */
function sw_add_load_more_button()
{
    global $wp_query;

    // Ako imamo samo jednu stranicu, ne prikazujemo dugme
    if ($wp_query->max_num_pages <= 1) {
        return;
    }

    // Trenutni broj stranice
    $current_page = max(1, get_query_var('paged'));

    // Trenutna kategorija
    $current_cat = is_product_category() ? get_queried_object()->slug : '';

    // Trenutni redosled
    $current_orderby = isset($_GET['orderby']) ? sanitize_text_field($_GET['orderby']) : '';

    // Broj ukupno pronađenih proizvoda
    $found_posts = $wp_query->found_posts;
    $posts_per_page = $wp_query->query_vars['posts_per_page'];

    // Formatiranje podataka za SEO i pristupačnost
    $showing_from = ($current_page - 1) * $posts_per_page + 1;
    $showing_to = min($current_page * $posts_per_page, $found_posts);

    // Sakupljamo ID-jeve trenutno prikazanih proizvoda
    $displayed_product_ids = [];
    if (have_posts()) {
        global $post;
        $original_post = $post; // Sačuvaj originalni $post

        while (have_posts()) {
            the_post();
            if (isset($post->ID)) {
                $displayed_product_ids[] = $post->ID;
            }
        }

        rewind_posts(); // Resetuje petlju
        $post = $original_post; // Vrati originalni $post
    }

    $product_ids_str = implode(',', $displayed_product_ids);

    // Poboljšana struktura sa informacijama o paginaciji
    echo '<div class="sw-pagination-container">';

    // Paginacioni indikatori
    echo '<div class="sw-pagination-info">';
    echo '<span class="sw-current-page">' . sprintf(__('Stranica %1$s od %2$s', 's7design'), $current_page, $wp_query->max_num_pages) . '</span>';
    echo '<span class="sw-products-count">' . sprintf(
        __('Prikazuje se %1$s - %2$s od %3$s proizvoda', 's7design'),
        $showing_from,
        $showing_to,
        $found_posts
    ) . '</span>';
    echo '</div>';

    // Load More dugme
    echo '<div class="sw-load-more-container">';
    echo '<button id="sw-load-more" class="sw-load-more-btn" 
        data-page="' . esc_attr($current_page) . '" 
        data-max-pages="' . esc_attr($wp_query->max_num_pages) . '" 
        data-category="' . esc_attr($current_cat) . '" 
        data-orderby="' . esc_attr($current_orderby) . '" 
        data-posts-per-page="' . esc_attr($posts_per_page) . '"
        data-loaded-ids="' . esc_attr($product_ids_str) . '">';
    echo '<span>' . esc_html__('Učitaj još proizvoda', 's7design') . '</span>';
    echo '<div class="sw-loading-spinner"></div>';
    echo '</button>';
    echo '</div>';

    // Numerička paginacija - opciono
    echo '<div class="sw-pagination-numbers">';
    echo '<ul class="sw-page-numbers">';

    // Prikaz stranica sa brojevima
    for ($i = 1; $i <= $wp_query->max_num_pages; $i++) {
        echo '<li>';
        echo '<a href="' . esc_url(get_pagenum_link($i)) . '" class="sw-page-number ' . ($i == $current_page ? 'current' : '') . '">' . $i . '</a>';
        echo '</li>';
    }

    echo '</ul>';
    echo '</div>';

    echo '</div>';
}
