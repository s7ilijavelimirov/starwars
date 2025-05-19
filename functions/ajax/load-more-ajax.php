<?php

/**
 * AJAX funkcije za Load More funkcionalnost - optimizovana verzija
 *
 * @package s7design
 */

// Sprečavanje direktnog pristupa
if (!defined('ABSPATH')) {
    exit;
}

/**
 * AJAX funkcija za učitavanje više proizvoda
 * Optimizovana verzija sa podrškom za Quick View funkcionalnost
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

    // Da li treba zameniti postojeći sadržaj (za paginaciju) ili dodati novi (za load more)
    $replace_content = isset($_POST['replace_content']) ? (bool)$_POST['replace_content'] : false;

    // Početni argumenti za upit - bolja optimizacija
    $args = [
        'post_type'      => 'product',
        'post_status'    => 'publish',
        'posts_per_page' => $posts_per_page,
        'paged'          => $paged,
        'orderby'        => 'date',
        'order'          => 'DESC',
        'no_found_rows'  => false, // Moramo imati found_rows za paginaciju
        'fields'         => 'ids', // Optimizacija - učitaj samo ID-jeve prvo
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
    if (isset($_POST['orderby']) && !empty($_POST['orderby'])) {
        $orderby = sanitize_text_field($_POST['orderby']);

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
            case 'date':
                $args['orderby'] = 'date';
                $args['order'] = 'DESC';
                break;
        }
    }

    // Pretraga
    if (isset($_POST['search']) && !empty($_POST['search'])) {
        $search_term = sanitize_text_field($_POST['search']);
        $args['meta_query']['relation'] = 'OR';
        $args['meta_query'][] = [
            'key'     => '_sku',
            'value'   => $search_term,
            'compare' => 'LIKE'
        ];
        $args['s'] = $search_term;
    }

    // Izvršavanje WP upita - optimizovano
    $products_query = new WP_Query($args);

    // Priprema odgovora
    $response = [
        'success' => true,
        'data' => [
            'max_pages' => $products_query->max_num_pages,
            'found_posts' => $products_query->found_posts,
            'replace_content' => $replace_content,
            'pagination' => [
                'current_page' => $paged,
                'max_pages' => $products_query->max_num_pages,
                'found_posts' => $products_query->found_posts,
                'posts_per_page' => $posts_per_page,
                'showing_from' => (($paged - 1) * $posts_per_page) + 1,
                'showing_to' => min($paged * $posts_per_page, $products_query->found_posts)
            ],
            'html' => ''
        ]
    ];

    // Obrada HTML-a samo ako imamo proizvode
    if ($products_query->have_posts()) {
        ob_start();

        // KLJUČNA MODIFIKACIJA: Osigurava da će quick-view funkcija biti pozvana
        // ======================================================================

        // Ova funkcija osigurava da će quick-view biti dostupan na AJAX-om učitanim proizvodima
        add_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button', 15);

        // Dodajemo atribute za lazy loading
        add_filter('wp_get_attachment_image_attributes', 'sw_add_lazy_loading_to_images', 10, 3);

        // Učitaj kompletne podatke proizvoda
        foreach ($products_query->posts as $product_id) {
            $GLOBALS['post'] = get_post($product_id);
            setup_postdata($GLOBALS['post']);
            wc_get_template_part('content', 'product');
        }

        // Uklanjamo filter nakon što završimo
        remove_filter('wp_get_attachment_image_attributes', 'sw_add_lazy_loading_to_images', 10);

        wp_reset_postdata();

        $response['data']['html'] = ob_get_clean();
    } else {
        $response['data']['html'] = '<p class="no-more-products">' . esc_html__('Nema više proizvoda.', 's7design') . '</p>';
    }

    wp_send_json($response);
    die();
}

/**
 * Dodaje lazy loading atribute slikama
 */
function sw_add_lazy_loading_to_images($attr, $attachment, $size)
{
    // Ne koristimo lazy loading za prvu sliku (koja će biti vidljiva odmah)
    // ili za glavne slike kategorija
    if (isset($attr['class']) && (
        strpos($attr['class'], 'attachment-woocommerce_thumbnail') !== false ||
        strpos($attr['class'], 'attachment-shop_catalog') !== false
    )) {
        if (!isset($attr['loading'])) {
            $attr['loading'] = 'lazy';
        }
        // Dodajemo fetchpriority="low" za dodatne optimizacije
        $attr['fetchpriority'] = 'low';
    }

    return $attr;
}

/**
 * Uklanja standardnu WooCommerce paginaciju i dodaje Load More dugme sa stranicama
 */
function sw_remove_woocommerce_pagination()
{
    remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);
    add_action('woocommerce_after_shop_loop', 'sw_add_load_more_button', 10);
}

/**
 * Dodaje Load More dugme i paginacione indikatore - poboljšani prikaz
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

    // Poboljšana struktura sa TRI kolone
    echo '<div class="sw-pagination-container" data-pagination-info="' . esc_attr($showing_from) . '-' . esc_attr($showing_to) . ' od ' . esc_attr($found_posts) . '">';

    // LEVA KOLONA - samo informacije o paginaciji
    echo '<div class="sw-pagination-left">';

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

    echo '</div>'; // Kraj sw-pagination-left

    // CENTRALNA KOLONA - Load More dugme
    echo '<div class="sw-pagination-center">';

    // Load More dugme - sada u centralnoj koloni
    echo '<div class="sw-load-more-container">';
    echo '<button id="sw-load-more" class="sw-load-more-btn" 
        data-page="' . esc_attr($current_page) . '" 
        data-max-pages="' . esc_attr($wp_query->max_num_pages) . '" 
        data-category="' . esc_attr($current_cat) . '" 
        data-orderby="' . esc_attr($current_orderby) . '" 
        data-posts-per-page="' . esc_attr($posts_per_page) . '">';
    echo '<span>' . esc_html__('Učitaj još proizvoda', 's7design') . '</span>';
    echo '<div class="sw-loading-spinner"></div>';
    echo '</button>';
    echo '</div>';

    echo '</div>'; // Kraj sw-pagination-center

    // DESNA KOLONA - paginacija sa brojevima stranica
    echo '<div class="sw-pagination-right">';

    // Paginacioni brojevi
    echo '<div class="sw-pagination-numbers">';
    echo '<span class="sw-page-text">' . __('Stranice:', 's7design') . '</span>';
    echo '<ul class="sw-page-numbers">';

    // Maksimalan broj stranica za prikaz
    $max_pages_to_show = 5;
    $start_page = max(1, min($current_page - floor($max_pages_to_show / 2), $wp_query->max_num_pages - $max_pages_to_show + 1));

    // Korigujemo start_page ako je max_num_pages manji od max_pages_to_show
    if ($wp_query->max_num_pages < $max_pages_to_show) {
        $start_page = 1;
    }

    $end_page = min($start_page + $max_pages_to_show - 1, $wp_query->max_num_pages);

    // Prikaži prvu stranicu ako nije u rasponu
    if ($start_page > 1) {
        echo '<li><a href="' . esc_url(get_pagenum_link(1)) . '" class="sw-page-number' . (1 == $current_page ? ' current' : '') . '" data-page="1">1</a></li>';
        if ($start_page > 2) {
            echo '<li><span class="sw-dots">...</span></li>';
        }
    }

    // Prikaži stranice u rasponu
    for ($i = $start_page; $i <= $end_page; $i++) {
        echo '<li><a href="' . esc_url(get_pagenum_link($i)) . '" class="sw-page-number' . ($i == $current_page ? ' current' : '') . '" data-page="' . $i . '">' . $i . '</a></li>';
    }

    // Prikaži poslednju stranicu ako nije u rasponu
    if ($end_page < $wp_query->max_num_pages) {
        if ($end_page < $wp_query->max_num_pages - 1) {
            echo '<li><span class="sw-dots">...</span></li>';
        }
        echo '<li><a href="' . esc_url(get_pagenum_link($wp_query->max_num_pages)) . '" class="sw-page-number' . ($wp_query->max_num_pages == $current_page ? ' current' : '') . '" data-page="' . $wp_query->max_num_pages . '">' . $wp_query->max_num_pages . '</a></li>';
    }

    echo '</ul>';
    echo '</div>';

    echo '</div>'; // Kraj sw-pagination-right

    echo '</div>'; // Kraj sw-pagination-container
}

// Registrovanje AJAX akcija
add_action('wp_ajax_load_more_products', 'sw_load_more_products');
add_action('wp_ajax_nopriv_load_more_products', 'sw_load_more_products');

// Zamena standardne WooCommerce paginacije
add_action('init', 'sw_remove_woocommerce_pagination');
