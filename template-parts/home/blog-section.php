<?php

/**
 * Template Part: Blog Sekcija - Sa strelicama za navigaciju kao kod product-carousel
 *
 * @package s7design
 * @version 2.6.0
 */

// Parametri sekcije
$blog_title = 'Najnovije vesti';
$blog_posts_count = 6;
$section_id = 'blog-swiper'; // ID za navigaciju

// Resetuj disable status za navigacione dugmiće
$prev_disabled = false;
$next_disabled = false;
?>
<section id="homepage-blog-section" class="py-5">
    <div class="container">
        <!-- Header za blog sekciju sa navigacijom - responsivan -->
        <div class="row align-items-center mb-4">
            <div class="col-md-6">
                <h2 class="swiper-heading fs-1" id="news"><?php echo esc_html($blog_title); ?></h2>
            </div>
            <div class="col-md-6 text-md-end mt-3 mt-md-0">
                <div class="d-flex align-items-center justify-content-md-end">
                    <?php if (get_permalink(get_option('page_for_posts'))) : ?>
                        <!-- Dugme za prikaz svih vesti -->
                        <a class="view-all-btn" href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" aria-label="Pogledaj sve vesti">
                            Pogledaj sve vesti <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                        </a>
                    <?php endif; ?>

                    <!-- Navigacija - identična kao kod product-carousel -->
                    <div class="product-nav-buttons">
                        <button type="button" class="product-nav-prev" id="prev-<?php echo esc_attr($section_id); ?>" aria-label="Prethodni blogovi" role="button" <?php echo $prev_disabled ? 'disabled' : ''; ?>>
                            <img src="<?php echo get_template_directory_uri(); ?>/dist/images/arrow.svg" alt="<?php echo $prev_disabled ? 'Nema prethodnih blogova' : 'Prethodno'; ?>" />
                        </button>
                        <button type="button" class="product-nav-next" id="next-<?php echo esc_attr($section_id); ?>" aria-label="Sledeći blogovi" role="button" <?php echo $next_disabled ? 'disabled' : ''; ?>>
                            <img src="<?php echo get_template_directory_uri(); ?>/dist/images/arrow.svg" alt="<?php echo $next_disabled ? 'Nema sledećih blogova' : 'Sledeće'; ?>" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Swiper Blog Container -->
        <div class="swiper-container" id="blog-swiper" data-slides="3">
            <div class="swiper-wrapper">
                <?php
                $args = array(
                    'posts_per_page' => $blog_posts_count,
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'no_found_rows' => true,
                );
                $latest_posts = new WP_Query($args);

                if ($latest_posts->have_posts()) :
                    while ($latest_posts->have_posts()) : $latest_posts->the_post();
                        // Dobavljamo URL slike i postavljamo fallback ako slika ne postoji
                        $image_url = has_post_thumbnail() ? get_the_post_thumbnail_url(get_the_ID(), 'medium_large') : get_template_directory_uri() . '/dist/images/placeholder.jpg';

                        // Dobavljamo kategoriju i tagove
                        $categories = get_the_category();
                        $category_name = !empty($categories) ? esc_html($categories[0]->name) : '';
                        $post_tags = get_the_tags();
                ?>
                        <div class="swiper-slide">
                            <article class="blog-card">
                                <a href="<?php the_permalink(); ?>" class="card-link">
                                    <!-- Card Header sa slikom -->
                                    <div class="card-header">
                                        <img class="card-img"
                                            src="<?php echo esc_url($image_url); ?>"
                                            alt="<?php the_title_attribute(); ?>"
                                            loading="lazy">

                                        <?php if ($category_name) : ?>
                                            <span class="category-badge">
                                                <?php echo $category_name; ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Card Body sa sadržajem -->
                                    <div class="card-body">
                                        <div class="card-meta">
                                            <span class="post-date">
                                                <?php echo get_the_date(); ?>
                                            </span>
                                            <span class="post-author">
                                                <?php the_author(); ?>
                                            </span>
                                        </div>

                                        <h3 class="card-title">
                                            <?php the_title(); ?>
                                        </h3>

                                        <div class="card-excerpt">
                                            <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                                        </div>

                                        <?php if ($post_tags && !is_wp_error($post_tags)) : ?>
                                            <div class="post-tags">
                                                <?php foreach ($post_tags as $tag) : ?>
                                                    <span class="post-tag"><?php echo esc_html($tag->name); ?></span>
                                                <?php endforeach; ?>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <!-- Card Footer -->
                                    <div class="card-footer">
                                        <span class="read-more">Pročitaj više</span>
                                    </div>
                                </a>
                            </article>
                        </div>
                <?php
                    endwhile;
                    wp_reset_postdata();
                endif;
                ?>
            </div>

            <!-- Paginacija -->
            <div class="swiper-pagination"></div>
        </div>
    </div>
</section>