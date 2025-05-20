<?php

/**
 * Inicijalizacija Load More funkcionalnosti - Ispravka za nonce
 *
 * @package s7design
 */

function sw_register_load_more_scripts()
{
    $dist_uri = get_template_directory_uri() . '/dist';
    $dist_dir = get_template_directory() . '/dist';

    // Debug info
    if (is_shop() || is_product_category() || is_product_tag()) {
        error_log('Na WooCommerce stranici smo, registrujemo Load More skriptu');
    } else {
        error_log('Nismo na WooCommerce stranici');
        return; // Izlazimo ako nismo na relevantnoj stranici
    }

    // Registruj Load More skriptu samo na stranicama gde je potrebno
    if (is_shop() || is_product_category() || is_product_tag()) {
        // Registruj skriptu
        wp_enqueue_script(
            's7design-load-more',
            $dist_uri . '/js/load-more-build.js',
            ['jquery'],
            file_exists($dist_dir . '/js/load-more-build.js') ? filemtime($dist_dir . '/js/load-more-build.js') : null,
            true
        );

        error_log('Registrovana skripta: ' . $dist_uri . '/js/load-more-build.js');

        // KRITIČNA IZMENA: Lokalizacija skripte za AJAX sa istim identifikatorom
        // Umesto lokalizacije 'jquery', koristi 's7design-load-more'
        wp_localize_script('s7design-load-more', 'sw_load_more_params', [
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('sw_load_more_nonce'),
            'current_page' => max(1, get_query_var('paged')),
            'text_loading' => __('Učitavanje...', 's7design'),
            'text_no_more' => __('Nema više proizvoda', 's7design')
        ]);

        error_log('Lokalizacija skripte za AJAX završena za sw_load_more_params');

        // Za testiranje - sačuvaj generisani nonce
        error_log('Generisani nonce: ' . wp_create_nonce('sw_load_more_nonce'));
    }
}
add_action('wp_enqueue_scripts', 'sw_register_load_more_scripts', 30);
