<?php

/**
 * Template : Header
 *
 * @package S7design
 */
?>
<!doctype html>
<html <?php language_attributes(); ?>>

<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">

	<style>
		body {
			background-color: #000000;
			background-image: url('<?php echo get_template_directory_uri(); ?>/background.webp');
			background-repeat: no-repeat;
			background-attachment: fixed;
			background-position: center;
			background-size: cover;
		}
	</style>

	<?php wp_head(); ?>
</head>

<body <?php body_class(); ?>>
	<?php wp_body_open(); ?>
	<header id="navbar" class="site-header sticky-top">
	<div class="sw-yellow-border"></div>
		<!-- Glavni navigacijski dio -->
		<nav class="navbar navbar-dark navbar-expand-lg">
			<div class="container">
				<!-- Hamburger za mobilne -->
				<button class="navbar-toggler border-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu" aria-controls="mobileMenu" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>

				<!-- Desktop navigacija u 3 dijela (Grid) -->
				<div class="d-none d-lg-flex align-items-center justify-content-between w-100">
					<!-- Lijevi menu - fiksna širina -->
					<div class="navbar-nav nav-left">
						<?php
						wp_nav_menu(array(
							'theme_location' => 'left-menu',
							'menu_id'        => 'left-menu',
							'menu_class'     => 'navbar-nav',
							'container'      => false,
							'fallback_cb'    => '__return_false',
							'depth'          => 2,
							'walker'         => new bootstrap_5_wp_nav_menu_walker()
						));
						?>
					</div>

					<!-- Logo u sredini - stvarno centriran -->
					<div class="navbar-brand-wrapper">
						<a href="<?php echo esc_url(home_url('/')); ?>" class="navbar-brand mx-auto pb-0">
							<?php
							if (has_custom_logo()) {
								$custom_logo_id = get_theme_mod('custom_logo');
								$logo_image = wp_get_attachment_image(
									$custom_logo_id,
									'full',
									false,
									array(
										'class'    => 'header-logo',
										'loading'  => 'eager',
										'itemprop' => 'logo',
										'alt'      => get_bloginfo('name')
									)
								);
								echo $logo_image;
							} else {
							?>
								<span class="logo-text"><?php echo esc_html(get_bloginfo('name')); ?></span>
							<?php } ?>
						</a>
					</div>

					<!-- Desni menu - fiksna širina, da balansira s levim -->
					<div class="navbar-nav nav-right">
						<?php
						wp_nav_menu(array(
							'theme_location' => 'right-menu',
							'menu_id'        => 'right-menu',
							'menu_class'     => 'navbar-nav',
							'container'      => false,
							'fallback_cb'    => '__return_false',
							'depth'          => 2,
							'walker'         => new bootstrap_5_wp_nav_menu_walker()
						));
						?>

						<!-- Opciono: Korpa za desktop -->
						<?php if (shortcode_exists('xoo_wsc_cart')) : ?>
							<div class="ms-3 cart-icon-wrapper">
								<?php echo do_shortcode('[xoo_wsc_cart]'); ?>
							</div>
						<?php endif; ?>
					</div>
				</div>

				<!-- Logo za mobilne - centriran -->
				<a href="<?php echo esc_url(home_url('/')); ?>" class="navbar-brand mx-auto d-lg-none">
					<?php
					if (has_custom_logo()) {
						$custom_logo_id = get_theme_mod('custom_logo');
						$logo_image = wp_get_attachment_image(
							$custom_logo_id,
							'full',
							false,
							array(
								'class'    => 'header-logo-mobile',
								'loading'  => 'eager',
								'itemprop' => 'logo',
								'alt'      => get_bloginfo('name')
							)
						);
						echo $logo_image;
					} else {
					?>
						<span class="logo-text"><?php echo esc_html(get_bloginfo('name')); ?></span>
					<?php } ?>
				</a>

				<!-- Korpa za mobilne -->
				<?php if (shortcode_exists('xoo_wsc_cart')) : ?>
					<div class="d-lg-none ms-2 cart-icon-wrapper">
						<?php echo do_shortcode('[xoo_wsc_cart]'); ?>
					</div>
				<?php endif; ?>
			</div>
		</nav>

		<!-- Offcanvas mobilni meni (klizanje s desne strane) -->
		<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileMenu" aria-labelledby="mobileMenuLabel">
			<div class="offcanvas-header">
				<h5 class="offcanvas-title" id="mobileMenuLabel">
					<?php echo esc_html(get_bloginfo('name')); ?>
				</h5>
				<button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
			</div>
			<div class="offcanvas-body">
				<?php
				wp_nav_menu(array(
					'theme_location' => 'mobile-menu',
					'menu_id'        => 'mobile-menu-nav',
					'menu_class'     => 'navbar-nav',
					'container'      => false,
					'fallback_cb'    => '__return_false',
					'depth'          => 2,
					'walker'         => new bootstrap_5_wp_nav_menu_walker()
				));
				?>

				<!-- Widgeti za mobilni menu ako postoje -->
				<?php if (is_active_sidebar('mobile-menu-widgets')) : ?>
					<div class="mobile-menu-widgets mt-4">
						<?php dynamic_sidebar('mobile-menu-widgets'); ?>
					</div>
				<?php endif; ?>
			</div>
		</div>
	</header>

	<div class="main-content">
		<!-- Glavni sadržaj stranice počinje ovdje -->