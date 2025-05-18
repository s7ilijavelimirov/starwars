<?php

// Helpers.
require_once get_template_directory() . '/functions/helpers/helpers.php';

// Admin setup.
// require_once get_template_directory() . '/functions/admin/admin-setup.php';
// require_once get_template_directory() . '/functions/admin/admin-styles.php';
// require_once get_template_directory() . '/functions/admin/admin-scripts.php';

// Theme setup.
include_once get_template_directory() . '/functions/theme/theme-setup.php';
include_once get_template_directory() . '/functions/theme/theme-styles.php';
include_once get_template_directory() . '/functions/theme/theme-scripts.php';

/* Disable WordPress Admin Bar for all users */
add_filter('show_admin_bar', '__return_false');
add_filter('use_widgets_block_editor', '__return_false');

/**
 * Isključivanje emoji funkcionalnosti
 */
function disable_emojis()
{
    remove_action('wp_head', 'print_emoji_detection_script', 7);
    remove_action('admin_print_scripts', 'print_emoji_detection_script');
    remove_action('wp_print_styles', 'print_emoji_styles');
    remove_action('admin_print_styles', 'print_emoji_styles');
    remove_filter('the_content_feed', 'wp_staticize_emoji');
    remove_filter('comment_text_rss', 'wp_staticize_emoji');
    remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
}
add_action('init', 'disable_emojis');

function s7design_preload_background()
{
    echo '<link rel="preload" href="' . get_template_directory_uri() . '/dist/images/background.webp" as="image" type="image/webp">' . "\n";
}
add_action('wp_head', 's7design_preload_background', 5);

/**
 * Deregistruje Google Fonts koje drugi plugini možda učitavaju
 */
function s7design_deregister_external_fonts()
{
    // Pronađi i ukloni Google Fonts i slične externe fontove
    global $wp_styles;
    foreach ($wp_styles->registered as $handle => $style) {
        if (
            strpos($style->src, 'fonts.googleapis.com') !== false ||
            strpos($style->src, 'fonts.gstatic.com') !== false
        ) {
            wp_deregister_style($handle);
        }
    }
}
add_action('wp_enqueue_scripts', 's7design_deregister_external_fonts', 999);

/**
 * Dodaje lazy-loading atribut svim slikama u sadržaju
 */
function s7design_add_image_loading_attribute($content)
{
    // Zameni sve img tagove da uključe loading="lazy" atribut, osim ako već nemaju loading atribut
    if (!is_admin()) {
        $content = preg_replace('/(<img[^>]*?)(?!\sloading=)(.*?\/?>)/i', '$1 loading="lazy"$2', $content);
    }
    return $content;
}
add_filter('the_content', 's7design_add_image_loading_attribute');
add_filter('post_thumbnail_html', 's7design_add_image_loading_attribute');

/**
 * Asinhrono učitavanje manje kritičnih skripti
 */
function s7design_async_less_critical_scripts()
{
?>
    <script>
        // Funkcija za odloženo učitavanje skripti
        function loadScript(src, async = true, defer = true) {
            const script = document.createElement('script');
            script.src = src;
            if (async) script.async = true;
            if (defer) script.defer = true;
            document.head.appendChild(script);
        }

        // Odloženo učitavanje nakon učitavanja stranice
        window.addEventListener('load', function() {
            // Ovde liste skripti koje se mogu učitati sa odlaganjem
            setTimeout(function() {
                // Dodajte ovde URL-ove skripti koje nisu kritične
                // loadScript('https://example.com/script.js');
            }, 2000);
        });
    </script>
<?php
}
add_action('wp_footer', 's7design_async_less_critical_scripts', 99);

// Brojač poseta
function setPostViews($postID)
{
    $countKey = 'post_views_count';
    $count = get_post_meta($postID, $countKey, true);
    if ($count === '') {
        delete_post_meta($postID, $countKey);
        add_post_meta($postID, $countKey, '0');
    } else {
        update_post_meta($postID, $countKey, ++$count);
    }
}

// Prikaz navigacije za postove
function show_posts_nav()
{
    global $wp_query;
    return $wp_query->max_num_pages > 1;
}

/**
 * Funkcija za prevođenje datuma na srpski
 */
function translate_date_to_serbian($date)
{
    $eng_months = array(
        'January',
        'February',
        'March',
        'April',
        'May',
        'June',
        'July',
        'August',
        'September',
        'October',
        'November',
        'December'
    );

    $sr_months = array(
        'Januar',
        'Februar',
        'Mart',
        'April',
        'Maj',
        'Jun',
        'Jul',
        'Avgust',
        'Septembar',
        'Oktobar',
        'Novembar',
        'Decembar'
    );

    $eng_days = array(
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday',
        'Saturday',
        'Sunday'
    );

    $sr_days = array(
        'Ponedeljak',
        'Utorak',
        'Sreda',
        'Četvrtak',
        'Petak',
        'Subota',
        'Nedelja'
    );

    $date = str_replace($eng_months, $sr_months, $date);
    $date = str_replace($eng_days, $sr_days, $date);

    return $date;
}

// Filteri za prevođenje datuma
add_filter('the_date', 'translate_date_to_serbian');
add_filter('get_the_date', 'translate_date_to_serbian');
add_filter('get_the_time', 'translate_date_to_serbian');
add_filter('the_time', 'translate_date_to_serbian');
add_filter('date_i18n', 'translate_date_to_serbian', 10, 4);
