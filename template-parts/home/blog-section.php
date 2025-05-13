<?php

/**
 * Template Part: Blog Section - Unapređena Swiper.js implementacija
 *
 * @package s7design
 * @version 2.1.0
 */

// Opciona ACF polja za prilagođavanje
$blog_title = function_exists('get_field') ? get_field('blog_section_title') : 'Najnovije vesti';
$blog_posts_count = function_exists('get_field') ? get_field('blog_posts_count') : 3;
?>
<div class="container my-5">
    <!-- Unapređeni header za blog sekciju sa navigacijom -->
    <div class="swiper-section-header">
        <h2 class="swiper-heading fs-1" id="news"><?php echo esc_html($blog_title); ?></h2>

        <div class="swiper-nav-controls">
            <?php if (get_permalink(get_option('page_for_posts'))) : ?>
                <a class="links-all" href="<?php echo esc_url(get_permalink(get_option('page_for_posts'))); ?>" aria-label="Pogledaj sve vesti">
                    Pogledaj sve vesti <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                </a>
            <?php endif; ?>

            <!-- Custom navigacione strelice za blog slider -->
            <div class="blog-swiper-prev swiper-custom-nav">
                <i class="fa-solid fa-chevron-left" aria-hidden="true"></i>
            </div>
            <div class="blog-swiper-next swiper-custom-nav">
                <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
            </div>
        </div>
    </div>

    <!-- Swiper Blog Container -->
    <div class="swiper-container with-pagination" id="blog-swiper">
        <div class="swiper-wrapper">
            <?php
            $args = array(
                'posts_per_page' => $blog_posts_count,
                'post_status' => 'publish',
                'no_found_rows' => true, // Optimizacija
            );
            $latest_posts = new WP_Query($args);

            if ($latest_posts->have_posts()) :
                while ($latest_posts->have_posts()) : $latest_posts->the_post();
            ?>
                    <div class="swiper-slide">
                        <a href="<?php the_permalink(); ?>" class="blog-card">
                            <div class="blog-card-inner">
                                <?php if (has_post_thumbnail()) : ?>
                                    <div class="blog-image swiper-lazy"
                                        data-background="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'medium_large')); ?>">
                                        <div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div>
                                    </div>
                                <?php else : ?>
                                    <div class="blog-image swiper-lazy"
                                        data-background="<?php echo esc_url(get_template_directory_uri() . '/assets/images/placeholder.jpg'); ?>">
                                        <div class="swiper-lazy-preloader swiper-lazy-preloader-white"></div>
                                    </div>
                                <?php endif; ?>

                                <div class="blog-details">
                                    <div class="blog-meta">
                                        <span class="blog-date"><?php echo get_the_date('d M Y'); ?></span>
                                        <?php if (get_the_author()) : ?>
                                            <span class="blog-author"><?php the_author(); ?></span>
                                        <?php endif; ?>
                                    </div>

                                    <h3 class="blog-title"><?php the_title(); ?></h3>

                                    <div class="blog-excerpt">
                                        <?php echo wp_trim_words(get_the_excerpt(), 15, '...'); ?>
                                    </div>

                                    <div class="blog-tags">
                                        <?php
                                        $posttags = get_the_tags();
                                        if ($posttags) {
                                            $tag_names = array();
                                            foreach ($posttags as $tag) {
                                                $tag_names[] = $tag->name;
                                            }
                                            echo '<span>' . esc_html(implode(' | ', $tag_names)) . '</span>';
                                        }
                                        ?>
                                    </div>

                                    <div class="read-more">
                                        <span>Pročitaj više <i class="fa-solid fa-arrow-right"></i></span>
                                    </div>
                                </div>
                            </div>
                        </a>
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