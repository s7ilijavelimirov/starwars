<?php

/**
 * AJAX funkcije za Load More funkcionalnost - Direktno sortiranje
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
    // Debug informacije
    error_log('================ LOAD MORE AJAX REQUEST ================');
    error_log('POST data: ' . print_r($_POST, true));

    // Provera nonce-a za sigurnost
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sw_load_more_nonce')) {
        error_log('Nonce provera nije uspela');
        wp_send_json_error(['message' => 'Sigurnosna provera nije uspela']);
        die();
    }

    // Osnovni parametri upita
    $paged = isset($_POST['page']) ? absint($_POST['page']) : 1;
    $posts_per_page = isset($_POST['posts_per_page']) ? absint($_POST['posts_per_page']) : get_option('posts_per_page');

    error_log('Stranica: ' . $paged);
    error_log('Proizvoda po stranici: ' . $posts_per_page);

    // Dohvatanje kategorije, ako je definisana
    $category = isset($_POST['category']) ? sanitize_text_field($_POST['category']) : '';
    if (!empty($category)) {
        error_log('Kategorija: ' . $category);
    }

    // Dohvatanje ID-jeva proizvoda koje treba preskočiti
    $loaded_ids = isset($_POST['loaded_ids']) ? array_map('absint', explode(',', $_POST['loaded_ids'])) : [];
    if (!empty($loaded_ids)) {
        error_log('Već učitani proizvodi (treba ih preskočiti): ' . implode(', ', $loaded_ids));
    }

    // NOVI PRISTUP: Dohvatamo SVE proizvode iz kategorije i ručno ih filtriramo
    // Ovo je drugačiji pristup od uobičajenog paged parametra, ali je pouzdaniji

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

    error_log('WP_Query argumenti: ' . print_r($args, true));

    // Izvršavanje WP upita
    $products_query = new WP_Query($args);

    // Dobijanje ID-jeva proizvoda
    $all_product_ids = $products_query->posts;

    error_log('Ukupno pronađeno proizvoda u kategoriji: ' . count($all_product_ids));

    // Filtriranje već prikazanih proizvoda
    $remaining_product_ids = array_diff($all_product_ids, $loaded_ids);

    error_log('Preostalo za prikaz nakon filtriranja: ' . count($remaining_product_ids));

    // Paginacija na preostalim proizvodima
    $offset = ($paged - 1) * $posts_per_page;
    $current_page_product_ids = array_slice($remaining_product_ids, 0, $posts_per_page);

    error_log('ID-jevi proizvoda za trenutnu stranicu: ' . print_r($current_page_product_ids, true));

    // Ako nema više proizvoda, vraćamo odgovor
    if (empty($current_page_product_ids)) {
        error_log('Nema više proizvoda za prikaz - svi proizvodi su već prikazani');

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

    error_log('Proizvodi za prikaz: ' . $products_query->post_count);

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

        // Osigurava da će quick-view funkcija biti pozvana
        if (function_exists('sw_add_quick_view_button')) {
            add_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button', 15);
        }

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

        $response['data']['html'] = ob_get_clean();
        error_log('HTML generisan: ' . strlen($response['data']['html']) . ' bajtova');
    } else {
        error_log('Nema proizvoda za prikazivanje');
        $response['data']['html'] = '<p class="no-more-products">Nema više proizvoda.</p>';
    }

    error_log('Slanje AJAX odgovora sa uspehom');
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

    // Debug informacije
    error_log('============ LOAD MORE BUTTON RENDER ============');
    error_log('Ukupno proizvoda (wp_query): ' . $wp_query->found_posts);
    error_log('Proizvoda po stranici (wp_query): ' . $wp_query->query_vars['posts_per_page']);
    error_log('Ukupno stranica (wp_query): ' . $wp_query->max_num_pages);
    error_log('Trenutna stranica (wp_query): ' . get_query_var('paged'));

    // Ako imamo samo jednu stranicu, ne prikazujemo dugme
    if ($wp_query->max_num_pages <= 1) {
        error_log('Samo jedna stranica, ne prikazujemo load more');
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

    error_log('Prikazuje se ' . $showing_from . ' - ' . $showing_to . ' od ' . $found_posts . ' proizvoda');

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
    error_log('Trenutno prikazani proizvodi: ' . $product_ids_str);

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

    error_log('Load More dugme prikazano');
}
