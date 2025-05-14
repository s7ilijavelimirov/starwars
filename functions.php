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

/**
 * Prioritetno učitavanje fonta - ostaje u functions.php
 */
function s7design_font_priority_loader()
{
?>
    <!-- Font Priority Loader -->
    <style id="s7design-font-priority">
        @font-face {
            font-family: 'Montserrat';
            font-style: normal;
            font-weight: 400;
            font-display: swap;
            src: local('Montserrat Regular'), local('Montserrat-Regular'),
                url('<?php echo get_template_directory_uri(); ?>/dist/fonts/Montserrat-Regular.woff2') format('woff2');
        }

        body {
            font-family: 'Montserrat', -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
        }
    </style>
<?php
}
add_action('wp_head', 's7design_font_priority_loader', 1);

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
add_filter('woocommerce_product_get_image', 's7design_add_image_loading_attribute');

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

// WooCommerce Wrapper funkcije
function my_theme_wrapper_start()
{
    echo '<section id="main">';
}
function my_theme_wrapper_end()
{
    echo '</section>';
}
add_action('woocommerce_before_main_content', 'my_theme_wrapper_start', 10);
add_action('woocommerce_after_main_content', 'my_theme_wrapper_end', 10);

// WooCommerce podrška
function mytheme_add_woocommerce_support()
{
    add_theme_support('woocommerce', array(
        'thumbnail_image_width' => 300,
        'single_image_width'    => 800,
        'product_grid'          => array(
            'default_rows'    => 5,
            'min_rows'        => 4,
            'max_rows'        => 5,
            'default_columns' => 3,
            'min_columns'     => 2,
            'max_columns'     => 5,
        ),
    ));
    add_theme_support("title-tag");
    add_theme_support('wc-product-gallery-zoom');
    add_theme_support('wc-product-gallery-lightbox');
    add_theme_support('wc-product-gallery-slider');
}
add_action('after_setup_theme', 'mytheme_add_woocommerce_support');

// WooCommerce valuta
add_filter('woocommerce_currency_symbol', 'change_existing_currency_symbol', 10, 2);
function change_existing_currency_symbol($currency_symbol, $currency)
{
    if ($currency === 'RSD') {
        $currency_symbol = 'RSD';
    }
    return $currency_symbol;
}

// Broj proizvoda po redu
add_filter('loop_shop_columns', 'loop_columns', 999);
function loop_columns()
{
    return 5; // 5 products per row
}

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

// Dodatne klase za galeriju proizvoda
add_filter('woocommerce_single_product_image_gallery_classes', 'custom_product_gallery_classes');
function custom_product_gallery_classes($classes)
{
    $classes[] = 'product-gallery-container';
    return $classes;
}

// Prikaz navigacije za postove
function show_posts_nav()
{
    global $wp_query;
    return $wp_query->max_num_pages > 1;
}

// Uklanjanje srodnih proizvoda
remove_action('woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15);
remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

// Tabela veličina - tab na proizvodu
add_filter('woocommerce_product_tabs', 'woo_new_product_tab');
function woo_new_product_tab($tabs)
{
    $tabs['new_tab'] = array(
        'title'    => __('Tabela veličine', 'woocommerce'),
        'priority' => 5,
        'callback' => 'woo_new_product_tab_content'
    );
    return $tabs;
}

// Sadržaj taba sa tabelom veličina
function woo_new_product_tab_content()
{
    global $product;
    $default_image_url = site_url() . '/wp-content/uploads/2022/12/tabela-velicina.png';
    $category_image_url = site_url() . '/wp-content/uploads/2024/05/velicina-majice.png';
    $category_duksevi_velicina_url = site_url() . '/wp-content/uploads/2024/07/tabela-velicina-duksevi.png';
    $category_decije_image_url = site_url() . '/wp-content/uploads/2024/05/velicina-decija.png';

    // Proverite da li proizvod pripada kategoriji "majice"
    if (has_term('majice', 'product_cat', $product->get_id())) {
        $image_url = $category_image_url;
    } else if (has_term('decije-majice', 'product_cat', $product->get_id())) {
        $image_url = $category_decije_image_url;
    } else if (has_term('duksevi', 'product_cat', $product->get_id())) {
        $image_url = $category_duksevi_velicina_url;
    } else {
        $image_url = $default_image_url;
    }
?>
    <img id="myImg" src="<?php echo $image_url; ?>" class="img-fluid" alt="<?php the_title(); ?>">
    <!-- Primer modalnog prozora sa ispravno označenim zatvaranjem -->
    <div id="myModal" class="modal">
        <span class="close">&times;</span> <!-- Preporučujem da koristite klasu .close umesto .closes za konzistentnost -->
        <div class="modal-images">
            <img class="modal-content" id="img01">
            <div id="caption"></div>
        </div>
    </div>
<?php
}

// Stilovi za input polja za količinu
add_action('wp_head', 'custom_quantity_fields_css');
function custom_quantity_fields_css()
{
?>
    <style>
        .quantity input::-webkit-outer-spin-button,
        .quantity input::-webkit-inner-spin-button {
            display: none;
            margin: 0;
        }

        .quantity input.qty {
            appearance: textfield;
            -webkit-appearance: none;
            -moz-appearance: textfield;
        }
    </style>
    <?php
}

// Kolona za cenu bez dostave u admin panelu
add_filter('manage_edit-shop_order_columns', 'add_order_price_without_shipping_column');
function add_order_price_without_shipping_column($columns)
{
    // Dodajte novu kolonu pre kolone "Ukupno"
    $new_columns = [];
    foreach ($columns as $key => $value) {
        if ($key === 'order_total') {
            $new_columns['order_price_without_shipping'] = __('Cena bez dostave', 'your-text-domain');
        }
        $new_columns[$key] = $value;
    }
    return $new_columns;
}

// Popunjavanje nove kolone sa podacima
add_action('manage_shop_order_posts_custom_column', 'add_order_price_without_shipping_column_data', 10, 2);
function add_order_price_without_shipping_column_data($column, $post_id)
{
    if ($column === 'order_price_without_shipping') {
        $order = wc_get_order($post_id);
        $total = $order->get_total();
        $shipping = $order->get_shipping_total();
        $price_without_shipping = $total - $shipping;
        echo wc_price($price_without_shipping);
    }
}

// Prevod opcija sortiranja
add_filter('woocommerce_catalog_orderby', 'custom_woocommerce_catalog_orderby');
function custom_woocommerce_catalog_orderby($orderby)
{
    // Proverite i prevedite opciju "Sort by latest"
    if (isset($orderby['date'])) {
        $orderby['date'] = __('Sortiraj po najnovijem', 'your-text-domain');
    }
    return $orderby;
}

add_filter('woocommerce_default_catalog_orderby_options', 'custom_woocommerce_catalog_orderby');
add_filter('woocommerce_catalog_orderby', 'custom_woocommerce_catalog_orderby');

add_filter('woocommerce_orderby_dropdown', 'custom_woocommerce_orderby_dropdown', 10, 1);
function custom_woocommerce_orderby_dropdown($sortby)
{
    if (isset($sortby['date'])) {
        $sortby['date'] = __('Sortiraj po najnovijem', 'your-text-domain');
    }
    return $sortby;
}

// Postavljanje alt i title atributa za slike na stranici proizvoda
add_filter('wp_get_attachment_image_attributes', 'custom_product_image_attributes', 10, 3);
function custom_product_image_attributes($attr, $attachment, $size)
{
    global $post;
    if ($post && $post->post_type == 'product') {
        $product = wc_get_product($post->ID);
        $title = $product->get_name();
        $attr['alt'] = $title;
        $attr['title'] = $title;
    }
    return $attr;
}

// Postavljanje alt i title atributa za slike na arhivi proizvoda
add_filter('post_thumbnail_html', 'custom_archive_product_image_attributes', 10, 5);
function custom_archive_product_image_attributes($html, $post_id, $post_thumbnail_id, $size, $attr)
{
    if (get_post_type($post_id) == 'product') {
        $product = wc_get_product($post_id);
        $title = $product->get_name();
        $html = str_replace('alt=""', 'alt="' . esc_attr($title) . '"', $html);
        $html = str_replace('title=""', 'title="' . esc_attr($title) . '"', $html);
    }
    return $html;
}

// Funkcija za prevođenje datuma na srpski
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

// Dodavanje PAK polja na checkout
add_filter('woocommerce_checkout_fields', 'custom_checkout_fields');
function custom_checkout_fields($fields)
{
    $fields['billing']['billing_pak'] = array(
        'type'        => 'number',
        'label'       => __('PAK', 'woocommerce'),
        'placeholder' => __('Unesite PAK', 'woocommerce'),
        'required'    => false,
        'class'       => array('form-row-wide'),
        'priority'    => 95,
        'custom_attributes' => array(
            'maxlength' => 6,
            'minlength' => 6,
            'pattern'   => '\d{6}',
        ),
    );
    return $fields;
}

// Validacija PAK polja
add_action('woocommerce_checkout_process', 'custom_checkout_field_process');
function custom_checkout_field_process()
{
    if (isset($_POST['billing_pak']) && !empty($_POST['billing_pak']) && !preg_match('/^\d{6}$/', $_POST['billing_pak'])) {
        wc_add_notice(__('PAK mora biti tačno šestocifreni broj.', 'woocommerce'), 'error');
    }
}

// Čuvanje PAK vrednosti u metadata
add_action('woocommerce_checkout_update_order_meta', 'custom_checkout_field_update_order_meta');
function custom_checkout_field_update_order_meta($order_id)
{
    if (!empty($_POST['billing_pak'])) {
        update_post_meta($order_id, 'PAK', sanitize_text_field($_POST['billing_pak']));
    }
}

// Prikaz PAK polja u admin panelu
add_action('woocommerce_admin_order_data_after_billing_address', 'custom_checkout_field_display_admin_order_meta', 10, 1);
function custom_checkout_field_display_admin_order_meta($order)
{
    echo '<p><strong>' . __('PAK', 'woocommerce') . ':</strong> ' . get_post_meta($order->get_id(), 'PAK', true) . '</p>';
}

// Dodavanje PAK polja u email-ove
add_filter('woocommerce_email_order_meta_keys', 'custom_checkout_field_order_meta_keys');
function custom_checkout_field_order_meta_keys($keys)
{
    $keys[] = 'PAK';
    return $keys;
}

// JavaScript za PAK polje
add_action('wp_footer', 'custom_checkout_field_js');
function custom_checkout_field_js()
{
    if (is_checkout()) {
    ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                $('input[name="billing_pak"]').on('input', function() {
                    this.value = this.value.replace(/\D/g, ''); // Remove non-digit characters
                    if (this.value.length > 6) {
                        this.value = this.value.slice(0, 6); // Limit to 6 characters
                    }
                });
            });
        </script>
<?php
    }
}

// Ažuriranje cena na osnovu ACF polja
function update_prices_on_save($post_id)
{
    // Proverite da li je ovo naš custom post type
    if (get_post_type($post_id) != 'page') {
        return;
    }

    // Proverite da li korisnik ima odgovarajuće dozvole
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    // Proverite da li postoji function get_field (ACF)
    if (!function_exists('get_field')) {
        return;
    }

    // Dobijanje vrednosti iz ACF polja
    $category = get_field('kategorija', $post_id);
    $new_price = get_field('nova_cena', $post_id);

    // Proverite da li su vrednosti postavljene i validne
    if (empty($category)) {
        return;
    }

    if (empty($new_price) || !is_numeric($new_price) || $new_price <= 0) {
        return;
    }

    // Provera da li je WooCommerce aktivan
    if (!function_exists('wc_get_product')) {
        return;
    }

    // Dobijanje proizvoda iz odabrane kategorije
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(
            array(
                'taxonomy' => 'product_cat',
                'field'    => 'term_id',
                'terms'    => $category,
            ),
        ),
    );
    $products = get_posts($args);

    // Ažuriranje cena proizvoda
    foreach ($products as $product) {
        $product_id = $product->ID;
        $product_obj = wc_get_product($product_id);

        if ($product_obj->is_type('variable')) {
            // Ako je promenljivi proizvod
            $variations = $product_obj->get_children();
            foreach ($variations as $variation_id) {
                $variation = wc_get_product($variation_id);
                $variation->set_regular_price($new_price);
                $variation->save();
            }
        } else {
            // Ako je jednostavni proizvod
            $product_obj->set_regular_price($new_price);
            $product_obj->save();
        }
    }
}

// Dodavanje akcije na save_post hook
add_action('save_post', 'update_prices_on_save');
