<?php

/**
 * WooCommerce Template
 *
 * This file is the main template file for the WooCommerce plugin.
 *
 * @link https://docs.woocommerce.com/document/template-structure/
 *
 * @package StarWars
 */


get_header(); ?>

<div class="container">
	<?php
	if (function_exists('is_woocommerce')) {
		if (is_woocommerce()) {
			//woocommerce_breadcrumb();

			if (is_cart()) {
				wc_get_template('cart/cart.php');
			} elseif (is_checkout()) {
				wc_get_template('checkout/form-checkout.php');
			} elseif (is_account_page()) {
				wc_get_template('myaccount/my-account.php');
			} elseif (is_shop() || is_product_category() || is_product_tag() || is_tax()) {
				wc_get_template('archive-product.php');
			} else {
				// For single product page or other WooCommerce pages
				woocommerce_content();
			}
		} else {
			// Your code for non-WooCommerce pages
			if (have_posts()) :
				while (have_posts()) : the_post();
					the_content();
				endwhile;
			else :
				get_template_part('content', 'none');
			endif;
		}
	}
	?>
</div>

<?php get_footer(); ?>