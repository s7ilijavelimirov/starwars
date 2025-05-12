<?php

/**
 * The Sidebar containing the main widget area
 *
 * @package WordPress
 * @subpackage Twenty_Fourteen
 * @since Twenty Fourteen 1.0
 */
?>
<div id="sidebar" class="primary-sidebar widget-area" role="complementary">
	<div class="rec-post">
		<h3 class="h-rec">Najčitanije vesti</h3>
		<?php
		$args = array(
			'posts_per_page' => 3,
			'offset' => 0,
			'meta_key' => 'post_views_count',
			'orderby' => 'meta_value_num',
			'order' => 'DESC',
			'post_type' => 'post',
			'post_status' => 'publish'
		);

		$query = new WP_Query($args);
		if ($query->have_posts()) :
			while ($query->have_posts()) : $query->the_post();
		?>
				<figure class="snip1529">
					<?php if (get_the_post_thumbnail_url()) : ?>
						<img class="img-fluid" src="<?php echo get_the_post_thumbnail_url(); ?>" alt="<?php the_title(); ?>">
					<?php else : ?>
						<img class="img-fluid" src="<?php echo home_url() . '/wp-content/uploads/2022/10/logostarwars_srbija-150x150.png' ?>" alt="<?php the_title() ?>">
					<?php endif; ?>
					<div class="date">
						<span class="day"><?php echo get_the_date('d'); ?></span>
						<span class="month"><?php echo get_the_date('M'); ?></span>
					</div>
					<figcaption>
						<?php the_title(); ?>
					</figcaption>
					<div class="hover"><i class="fa-brands fa-old-republic"></i></div>
					<a href="<?php the_permalink(); ?>"></a>
				</figure>
		<?php
			endwhile;
		endif;
		wp_reset_query();
		?>
	</div>
	<div class="dropdown" id="archive">
		<button class="category-button" type="button" id="dropdownMenuButton1" data-bs-toggle="dropdown" aria-expanded="false">
			<h3 class="p-cat">Arhiva vesti</h3><i class="fa fa-chevron-down"></i>
		</button>
		<ul class="dropdown-menu" aria-labelledby="dropdownMenuButton1">
			<?php wp_get_archives(array('type' => 'monthly', 'limit' => 12, 'format' => 'custom', 'before' => '<li>', 'after' => '</li>', 'echo' => 1)); ?>
		</ul>
	</div>


</div>