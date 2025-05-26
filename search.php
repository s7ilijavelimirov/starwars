<?php

/**
 * Search Results Template - Star Wars tema
 * 
 * @package StarWars
 * @version 2.0
 */

get_header();

// Dobijanje search query
$search_query = get_search_query();
$found_posts = $wp_query->found_posts;
?>

<div class="search-results-page py-5">
	<div class="container">

		<!-- Search Header -->
		<div class="search-header mb-5">
			<div class="row align-items-center">
				<div class="col-md-8">
					<h1 class="search-title mb-3">
						<svg xmlns="http://www.w3.org/2000/svg" width="32" height="32" fill="currentColor" class="text-warning me-3" viewBox="0 0 16 16">
							<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
						</svg>
						Rezultati pretrage
					</h1>

					<?php if ($found_posts > 0) : ?>
						<p class="search-info text-muted mb-0">
							Pronađeno <strong class="text-warning"><?php echo $found_posts; ?></strong>
							<?php echo ($found_posts == 1) ? 'rezultat' : 'rezultata'; ?>
							za "<strong class="text-white"><?php echo esc_html($search_query); ?></strong>"
						</p>
					<?php else : ?>
						<p class="search-info text-muted mb-0">
							Nema rezultata za "<strong class="text-white"><?php echo esc_html($search_query); ?></strong>"
						</p>
					<?php endif; ?>
				</div>

				<div class="col-md-4">
					<!-- Search Form -->
					<form role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>" class="search-form">
						<div class="input-group">
							<input
								type="search"
								class="form-control bg-dark text-white border-warning"
								placeholder="Nova pretraga..."
								value="<?php echo esc_attr($search_query); ?>"
								name="s"
								required>
							<button class="btn btn-warning" type="submit">
								<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
									<path d="M11.742 10.344a6.5 6.5 0 1 0-1.397 1.398h-.001c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1zM12 6.5a5.5 5.5 0 1 1-11 0 5.5 5.5 0 0 1 11 0z" />
								</svg>
							</button>
						</div>
					</form>
				</div>
			</div>
		</div>

		<?php if (have_posts()) : ?>
			<!-- Search Results -->
			<div class="search-results">
				<div class="row">
					<?php while (have_posts()) : the_post(); ?>
						<div class="col-12 mb-4">
							<article class="search-result-card bg-dark border border-warning rounded p-4">
								<div class="row">
									<!-- Thumbnail -->
									<div class="col-md-3">
										<?php if (has_post_thumbnail()) : ?>
											<a href="<?php the_permalink(); ?>" class="d-block">
												<?php the_post_thumbnail('medium', [
													'class' => 'img-fluid rounded search-thumbnail',
													'loading' => 'lazy'
												]); ?>
											</a>
										<?php else : ?>
											<div class="search-thumbnail-placeholder bg-secondary rounded d-flex align-items-center justify-content-center">
												<?php if (get_post_type() == 'product') : ?>
													<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="text-warning" viewBox="0 0 16 16">
														<path d="M8 1a2.5 2.5 0 0 1 2.5 2.5V4h-5v-.5A2.5 2.5 0 0 1 8 1zm3.5 3v-.5a3.5 3.5 0 1 0-7 0V4H1v10a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V4h-3.5zM2 5h12v9a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V5z" />
													</svg>
												<?php else : ?>
													<svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" class="text-warning" viewBox="0 0 16 16">
														<path d="M5.5 7a.5.5 0 0 0 0 1h5a.5.5 0 0 0 0-1h-5zM5 9.5a.5.5 0 0 1 .5-.5h5a.5.5 0 0 1 0 1h-5a.5.5 0 0 1-.5-.5zm0 2a.5.5 0 0 1 .5-.5h2a.5.5 0 0 1 0 1h-2a.5.5 0 0 1-.5-.5z" />
														<path d="M9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2V4.5L9.5 0zm0 1v2A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z" />
													</svg>
												<?php endif; ?>
											</div>
										<?php endif; ?>
									</div>

									<!-- Content -->
									<div class="col-md-9">
										<!-- Post Type Badge -->
										<div class="mb-2">
											<?php
											$post_type = get_post_type();
											$post_type_label = '';
											$badge_class = 'bg-secondary';

											switch ($post_type) {
												case 'product':
													$post_type_label = 'Proizvod';
													$badge_class = 'bg-warning text-dark';
													break;
												case 'post':
													$post_type_label = 'Članak';
													$badge_class = 'bg-info';
													break;
												case 'page':
													$post_type_label = 'Stranica';
													$badge_class = 'bg-success';
													break;
												default:
													$post_type_label = ucfirst($post_type);
											}
											?>
											<span class="badge <?php echo $badge_class; ?>"><?php echo $post_type_label; ?></span>
										</div>

										<!-- Title -->
										<h2 class="search-result-title h4 mb-2">
											<a href="<?php the_permalink(); ?>" class="text-white text-decoration-none">
												<?php the_title(); ?>
											</a>
										</h2>

										<!-- Excerpt -->
										<div class="search-result-excerpt text-muted mb-3">
											<?php
											if (get_post_type() == 'product') {
												$product = wc_get_product(get_the_ID());
												if ($product) {
													echo wp_trim_words($product->get_short_description(), 20);
												}
											} else {
												echo wp_trim_words(get_the_excerpt(), 25);
											}
											?>
										</div>

										<!-- Meta Info -->
										<div class="search-result-meta d-flex align-items-center text-muted small">
											<?php if (get_post_type() == 'product') : ?>
												<?php
												$product = wc_get_product(get_the_ID());
												if ($product && $product->get_price()) :
												?>
													<span class="me-3 text-warning fw-bold">
														<?php echo $product->get_price_html(); ?>
													</span>
												<?php endif; ?>
											<?php else : ?>
												<span class="me-3">
													<svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="currentColor" class="me-1" viewBox="0 0 16 16">
														<path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z" />
													</svg>
													<?php echo get_the_date(); ?>
												</span>
											<?php endif; ?>

											<a href="<?php the_permalink(); ?>" class="btn btn-sm btn-outline-warning">
												Pogledaj više
												<svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" fill="currentColor" class="ms-1" viewBox="0 0 16 16">
													<path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z" />
												</svg>
											</a>
										</div>
									</div>
								</div>
							</article>
						</div>
					<?php endwhile; ?>
				</div>

				<!-- Pagination -->
				<div class="search-pagination mt-5">
					<?php
					the_posts_pagination([
						'mid_size' => 2,
						'prev_text' => '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M11.354 1.646a.5.5 0 0 1 0 .708L5.707 8l5.647 5.646a.5.5 0 0 1-.708.708l-6-6a.5.5 0 0 1 0-.708l6-6a.5.5 0 0 1 .708 0z"/></svg> Prethodni',
						'next_text' => 'Sledeći <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M4.646 1.646a.5.5 0 0 1 .708 0l6 6a.5.5 0 0 1 0 .708l-6 6a.5.5 0 0 1-.708-.708L10.293 8 4.646 2.354a.5.5 0 0 1 0-.708z"/></svg>',
						'class' => 'pagination-starwars'
					]);
					?>
				</div>
			</div>

		<?php else : ?>
			<!-- No Results -->
			<div class="no-search-results text-center py-5">
				<div class="mb-4">
					<svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="text-warning" viewBox="0 0 16 16">
						<path d="M6.5 12a5.5 5.5 0 1 0 0-11 5.5 5.5 0 0 0 0 11zM13 6.5a6.5 6.5 0 1 1-13 0 6.5 6.5 0 0 1 13 0z" />
						<path d="M10.344 11.742c.03.04.062.078.098.115l3.85 3.85a1 1 0 0 0 1.415-1.414l-3.85-3.85a1.007 1.007 0 0 0-.115-.1 6.538 6.538 0 0 1-1.398-1.4z" />
						<path d="M6.5 7a.5.5 0 0 0-1 0v1.5H4a.5.5 0 0 0 0 1h1.5V11a.5.5 0 0 0 1 0V9.5H8a.5.5 0 0 0 0-1H6.5V7z" />
					</svg>
				</div>

				<h2 class="text-warning mb-3">Nema rezultata</h2>
				<p class="text-muted mb-4">Pokušajte sa drugim pojmovima ili proverite da li ste uneli ispravno.</p>

				<div class="suggestions">
					<h3 class="h5 text-white mb-3">Predlog za pretragu:</h3>
					<div class="d-flex justify-content-center flex-wrap gap-2">
						<a href="<?php echo esc_url(home_url('/?s=majice')); ?>" class="btn btn-sm btn-outline-warning">majice</a>
						<a href="<?php echo esc_url(home_url('/?s=figurice')); ?>" class="btn btn-sm btn-outline-warning">figurice</a>
						<a href="<?php echo esc_url(home_url('/?s=star+wars')); ?>" class="btn btn-sm btn-outline-warning">star wars</a>
						<a href="<?php echo esc_url(home_url('/shop')); ?>" class="btn btn-sm btn-warning">Svi proizvodi</a>
					</div>
				</div>
			</div>
		<?php endif; ?>

	</div>
</div>

<style>
	.search-thumbnail-placeholder {
		height: 120px;
		min-width: 120px;
	}

	.search-result-card {
		transition: all 0.3s ease;
	}

	.search-result-card:hover {
		border-color: #ffdd55 !important;
		box-shadow: 0 4px 15px rgba(255, 221, 85, 0.3);
		transform: translateY(-2px);
	}

	.pagination-starwars .page-numbers {
		background-color: rgba(0, 0, 0, 0.7);
		border: 1px solid #ffdd55;
		color: #fff;
		padding: 0.5rem 0.75rem;
		margin: 0 0.25rem;
		border-radius: 4px;
		text-decoration: none;
		transition: all 0.3s ease;
	}

	.pagination-starwars .page-numbers:hover,
	.pagination-starwars .page-numbers.current {
		background-color: #ffdd55;
		color: #000;
	}
</style>

<?php get_footer(); ?>