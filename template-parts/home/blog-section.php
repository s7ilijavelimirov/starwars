<?php

/**
 * Template Part: Blog Section - Swiper.js implementacija
 *
 * @package s7design
 * @version 2.0.0
 */

// Opciona ACF polja za prilagođavanje
$blog_title = function_exists('get_field') ? get_field('blog_section_title') : 'Najnovije vesti';
$blog_posts_count = function_exists('get_field') ? get_field('blog_posts_count') : 5;
?>
<div class="container my-5">
    <h1 id="news" class="my-5 fs-1"><?php echo esc_html($blog_title); ?></h1>
    
    <!-- Swiper Blog Container -->
    <div class="swiper-container" id="blog-swiper">
        <div class="swiper-wrapper">
            <?php
            $args = array(
                'posts_per_page' => $blog_posts_count,
                'post_status' => 'publish'
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
                                         data-background="<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>">
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
                                        <?php echo wp_trim_words(get_the_excerpt(), 20, '...'); ?>
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
        
        <!-- Blog Slider kontrole -->
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
        <div class="swiper-pagination"></div>
    </div>
</div>