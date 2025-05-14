<?php

/**
 * The sidebar containing the main widget area
 *
 * @package StarWars
 */
?>

<div id="sidebar" class="site-sidebar" role="complementary">
	<!-- Popularne objave -->
	<div class="sidebar-widget popular-posts">
		<h3 class="widget-title"><?php esc_html_e('Najčitanije', 'starwars'); ?></h3>

		<div class="popular-posts-container">
			<?php
			$popular_args = array(
				'posts_per_page' => 3,
				'post_status'    => 'publish',
				'meta_key'       => 'post_views_count',
				'orderby'        => 'meta_value_num',
				'order'          => 'DESC'
			);

			$popular_query = new WP_Query($popular_args);

			if ($popular_query->have_posts()) :
				while ($popular_query->have_posts()) : $popular_query->the_post();
			?>
					<article class="popular-post">
						<a href="<?php the_permalink(); ?>" class="post-link">
							<div class="post-image">
								<?php if (has_post_thumbnail()) : ?>
									<?php the_post_thumbnail('thumbnail', array('class' => 'post-thumbnail')); ?>
								<?php else : ?>
									<div class="no-image">
										<i class="fa-solid fa-jedi" aria-hidden="true"></i>
									</div>
								<?php endif; ?>
							</div>

							<div class="post-info">
								<h4 class="post-title"><?php the_title(); ?></h4>
								<div class="post-date">
									<time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
										<?php echo esc_html(get_the_date()); ?>
									</time>
								</div>
							</div>
						</a>
					</article>
				<?php
				endwhile;
				wp_reset_postdata();
			else :
				?>
				<p class="no-posts"><?php esc_html_e('Trenutno nema popularnih objava.', 'starwars'); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Kategorije -->
	<div class="sidebar-widget categories-widget">
		<h3 class="widget-title"><?php esc_html_e('Kategorije', 'starwars'); ?></h3>

		<ul class="categories-list">
			<?php
			$categories = get_categories(array(
				'orderby'    => 'count',
				'order'      => 'DESC',
				'hide_empty' => true,
				'number'     => 10,
			));

			foreach ($categories as $category) :
				// Dobijanje broja postova u kategoriji
				$category_count = $category->count;
			?>
				<li class="category-item">
					<a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="category-link">
						<span class="category-name"><?php echo esc_html($category->name); ?></span>
						<span class="post-count"><?php echo esc_html($category_count); ?></span>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>

	<!-- Tagovi -->
	<div class="sidebar-widget tags-widget">
		<h3 class="widget-title"><?php esc_html_e('Tagovi', 'starwars'); ?></h3>

		<div class="tag-cloud">
			<?php
			$tags = get_tags(array(
				'orderby'    => 'count',
				'order'      => 'DESC',
				'number'     => 20,
			));

			if ($tags) :
				foreach ($tags as $tag) :
			?>
					<a href="<?php echo esc_url(get_tag_link($tag->term_id)); ?>" class="tag-link">
						<?php echo esc_html($tag->name); ?>
					</a>
				<?php
				endforeach;
			else :
				?>
				<p class="no-tags"><?php esc_html_e('Trenutno nema tagova.', 'starwars'); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<!-- Social Widget - Postojeći social widget -->
	<?php if (is_active_sidebar('footer_3')) : ?>
		<div class="footer-social-icons sidebar-widget" role="complementary">
			<?php dynamic_sidebar('footer_3'); ?>
		</div>
	<?php endif; ?>

	<?php
	// Dinamički sidebar widgeti
	if (is_active_sidebar('sidebar-1')) {
		dynamic_sidebar('sidebar-1');
	}
	?>
</div>