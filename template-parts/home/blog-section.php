<?php

/**
 * Template Part: Blog Section
 *
 * @package s7design
 */

// Opciona ACF polja za prilagođavanje
$blog_title = function_exists('get_field') ? get_field('blog_section_title') : 'Najnovije vesti';
$blog_posts_count = function_exists('get_field') ? get_field('blog_posts_count') : 5;
?>
<div class="container my-5">
    <h1 id="news" class="my-5 fs-1"><?php echo esc_html($blog_title); ?></h1>
    <div id="newsSlider" class="custom-slider">
        <div class="slider-inner">
            <?php
            $args = array(
                'posts_per_page' => $blog_posts_count,
                'post_status' => 'publish'
            );
            $latest_posts = new WP_Query($args);

            if ($latest_posts->have_posts()) :
                while ($latest_posts->have_posts()) : $latest_posts->the_post();
            ?>
                    <div class="slider-item">
                        <a href="<?php the_permalink(); ?>" class="link-posts">
                            <div class="custom-card">
                                <div class="photo" style="background-image: url('<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>');"></div>
                                <div class="details">
                                    <ul>
                                        <li class="author"><span><?php the_author(); ?></span></li>
                                        <li class="dates">
                                            <span class="day"><?php echo get_the_date('d'); ?></span>
                                            <span class="month"><?php echo get_the_date('M'); ?></span>
                                        </li>
                                        <li class="tags">
                                            <ul>
                                                <?php
                                                $posttags = get_the_tags();
                                                if ($posttags) {
                                                    $total_tags = count($posttags);
                                                    $current_tag = 0;
                                                    foreach ($posttags as $tag) {
                                                        $current_tag++;
                                                        echo '<li><span>' . esc_html($tag->name) . '</span>';
                                                        if ($current_tag < $total_tags) {
                                                            echo ' | ';
                                                        }
                                                        echo '</li>';
                                                    }
                                                }
                                                ?>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>
                                <div class="description">
                                    <h1><?php the_title(); ?></h1>
                                    <p><?php echo wp_trim_words(get_the_content(), 18, '...'); ?></p>
                                    <div class="read-more">
                                        <span>Pročitaj više</span>
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
        <button class="slider-control-prev">&#10094;</button>
        <button class="slider-control-next">&#10095;</button>
    </div>
</div>