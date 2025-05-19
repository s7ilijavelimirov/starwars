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

/**
 * Dodaje Quick View dugme na WooCommerce proizvode
 */

// Dodajemo Quick View dugme na proizvode
function sw_add_quick_view_button()
{
    global $product;

    if (!$product) return;

    // Osnovni podaci o proizvodu
    $product_id = $product->get_id();
    $product_title = $product->get_title();

    // HTML za Quick View dugme
    echo '<div class="sw-quick-view-wrapper">';
    echo '<button type="button" 
                 class="sw-quick-view-button quick-view-button" 
                 data-product-id="' . esc_attr($product_id) . '" 
                 data-product-title="' . esc_attr($product_title) . '" 
                 aria-label="' . esc_attr__('Brzi pregled', 's7design') . '">';
    echo '<span class="sw-quick-view-icon"></span>';
    echo '<span class="sw-quick-view-text">' . esc_html__('Brzi pregled', 's7design') . '</span>';
    echo '</button>';
    echo '</div>';
}

// Dodajemo Quick View dugme nakon slike proizvoda
add_action('woocommerce_before_shop_loop_item_title', 'sw_add_quick_view_button', 15);

// Dodajemo modal za Quick View
function sw_add_quick_view_modal()
{
    // Proveravamo da li smo na arhivi proizvoda ili kategoriji
    if (is_shop() || is_product_category() || is_product_tag() || is_front_page()) {
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
                        <!-- Sadržaj će biti učitan putem AJAX-a -->
                        <div class="sw-quick-view-loader">
                            <div class="sw-quick-view-spinner"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    <?php
    }
}

// Dodajemo modal na kraj stranice
add_action('wp_footer', 'sw_add_quick_view_modal');

/**
 * AJAX funkcija za učitavanje sadržaja Quick View modala
 */
function sw_load_quick_view_content()
{
    // Provera nonce-a za sigurnost
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sw_quick_view_nonce')) {
        wp_send_json_error('Sigurnosna provera nije uspela');
        die();
    }

    // Dobavljanje ID-a proizvoda
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;

    if (!$product_id) {
        wp_send_json_error('Neispravan ID proizvoda');
        die();
    }

    // Dohvatanje proizvoda
    $product = wc_get_product($product_id);

    if (!$product) {
        wp_send_json_error('Proizvod nije pronađen');
        die();
    }

    // Počinjemo baferisanje za prikupljanje HTML-a
    ob_start();

    // Struktura Quick View sadržaja
    ?>
    <div class="sw-quick-view-product">
        <div class="sw-quick-view-row">
            <!-- Leva kolona: Slika proizvoda -->
            <div class="sw-quick-view-images">
                <?php
                // Prikaz glavne slike
                $image_id = $product->get_image_id();
                if ($image_id) {
                    echo wp_get_attachment_image($image_id, 'woocommerce_single', false, array(
                        'class' => 'sw-quick-view-image',
                        'alt' => $product->get_title()
                    ));
                } else {
                    echo '<img src="' . wc_placeholder_img_src('woocommerce_single') . '" alt="' . esc_attr__('Placeholder', 's7design') . '" class="sw-quick-view-image">';
                }

                // Prikaz galerije (thumbnail sličice)
                $attachment_ids = $product->get_gallery_image_ids();
                if (!empty($attachment_ids)) {
                    echo '<div class="sw-quick-view-thumbnails">';

                    // Dodajemo glavnu sliku na početak
                    if ($image_id) {
                        echo '<div class="sw-quick-view-thumbnail active" data-image-id="' . esc_attr($image_id) . '">';
                        echo wp_get_attachment_image($image_id, 'thumbnail', false, array(
                            'class' => 'sw-quick-view-thumbnail-img',
                            'alt' => $product->get_title()
                        ));
                        echo '</div>';
                    }

                    // Dodajemo ostale slike
                    foreach ($attachment_ids as $attachment_id) {
                        echo '<div class="sw-quick-view-thumbnail" data-image-id="' . esc_attr($attachment_id) . '">';
                        echo wp_get_attachment_image($attachment_id, 'thumbnail', false, array(
                            'class' => 'sw-quick-view-thumbnail-img',
                            'alt' => $product->get_title()
                        ));
                        echo '</div>';
                    }

                    echo '</div>';
                }
                ?>
            </div>

            <!-- Desna kolona: Informacije o proizvodu -->
            <div class="sw-quick-view-summary">
                <h2 class="sw-quick-view-title"><?php echo esc_html($product->get_title()); ?></h2>

                <!-- Cena -->
                <div class="sw-quick-view-price">
                    <?php echo $product->get_price_html(); ?>
                </div>

                <!-- Kratak opis -->
                <?php if ($product->get_short_description()) : ?>
                    <div class="sw-quick-view-description">
                        <?php echo wp_kses_post($product->get_short_description()); ?>
                    </div>
                <?php endif; ?>

                <!-- Link ka proizvodu -->
                <div class="sw-quick-view-actions">
                    <a href="<?php echo esc_url($product->get_permalink()); ?>" class="sw-quick-view-details button">
                        <?php esc_html_e('Pogledajte detalje', 's7design'); ?>
                    </a>

                    <?php
                    // "Dodaj u korpu" dugme (samo za jednostavne proizvode)
                    if ($product->is_type('simple') && $product->is_purchasable() && $product->is_in_stock()) {
                        echo '<form class="sw-quick-view-cart">';
                        echo '<button type="button" data-product-id="' . esc_attr($product_id) . '" class="sw-quick-view-add-to-cart button alt">';
                        echo esc_html__('Dodaj u korpu', 's7design');
                        echo '</button>';
                        echo '</form>';
                    }
                    ?>
                </div>

                <!-- Kategorije i oznake -->
                <div class="sw-quick-view-meta">
                    <?php
                    // Kategorije
                    $categories = wc_get_product_category_list($product_id);
                    if ($categories) {
                        echo '<div class="sw-quick-view-categories">';
                        echo '<span class="sw-meta-label">' . esc_html__('Kategorije:', 's7design') . '</span> ';
                        echo wp_kses_post($categories);
                        echo '</div>';
                    }

                    // Oznake (tagovi)
                    $tags = wc_get_product_tag_list($product_id);
                    if ($tags) {
                        echo '<div class="sw-quick-view-tags">';
                        echo '<span class="sw-meta-label">' . esc_html__('Oznake:', 's7design') . '</span> ';
                        echo wp_kses_post($tags);
                        echo '</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
<?php

    // Uzimamo generisani HTML
    $output = ob_get_clean();

    // Vraćamo AJAX odgovor
    wp_send_json_success(array(
        'html' => $output,
        'product_title' => $product->get_title()
    ));

    die();
}

// Registrujemo AJAX endpoint za učitavanje Quick View sadržaja
add_action('wp_ajax_sw_load_quick_view', 'sw_load_quick_view_content');
add_action('wp_ajax_nopriv_sw_load_quick_view', 'sw_load_quick_view_content');

/**
 * AJAX funkcija za dodavanje proizvoda u korpu iz Quick View
 */
function sw_quick_view_add_to_cart()
{
    // Provera nonce-a za sigurnost
    if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'sw_quick_view_nonce')) {
        wp_send_json_error('Sigurnosna provera nije uspela');
        die();
    }

    // Dobavljanje ID-a proizvoda
    $product_id = isset($_POST['product_id']) ? absint($_POST['product_id']) : 0;
    $quantity = isset($_POST['quantity']) ? absint($_POST['quantity']) : 1;

    if (!$product_id) {
        wp_send_json_error('Neispravan ID proizvoda');
        die();
    }

    // Dodajemo proizvod u korpu
    $added = WC()->cart->add_to_cart($product_id, $quantity);

    if ($added) {
        $data = array(
            'message' => esc_html__('Proizvod je dodat u korpu!', 's7design'),
            'cart_count' => WC()->cart->get_cart_contents_count(),
            'cart_total' => WC()->cart->get_cart_total()
        );

        wp_send_json_success($data);
    } else {
        wp_send_json_error(esc_html__('Došlo je do greške pri dodavanju proizvoda u korpu. Molimo pokušajte ponovo.', 's7design'));
    }

    die();
}

// Registrujemo AJAX endpoint za dodavanje u korpu iz Quick View
add_action('wp_ajax_sw_quick_view_add_to_cart', 'sw_quick_view_add_to_cart');
add_action('wp_ajax_nopriv_sw_quick_view_add_to_cart', 'sw_quick_view_add_to_cart');


/**
 * Registracija Quick View skripti i stilova
 */
function sw_register_quick_view_scripts()
{
    // Samo na odgovarajućim stranicama (shop, kategorije, arhive proizvoda)
    if (is_shop() || is_product_category() || is_product_tag() || is_front_page()) {
        // Registracija Quick View skripte
        wp_enqueue_script(
            'sw-quick-view',
            get_template_directory_uri() . '/dist/js/quick-view-build.js',
            array('jquery'),
            filemtime(get_template_directory() . '/dist/js/quick-view-build.js'),
            true
        );

        // Lokalizacija skripte za AJAX
        wp_localize_script('sw-quick-view', 'sw_quick_view_params', array(
            'ajaxurl' => admin_url('admin-ajax.php'),
            'nonce'   => wp_create_nonce('sw_quick_view_nonce'),
        ));
    }
}
add_action('wp_enqueue_scripts', 'sw_register_quick_view_scripts');
