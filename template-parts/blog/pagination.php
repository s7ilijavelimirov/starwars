<?php

/**
 * Template part za Star Wars paginaciju - Finalna verzija
 * Podržava dva tipa paginacije: 
 * 1. numbers - za blog/archive/category stranice
 * 2. post - za navigaciju između pojedinačnih postova
 *
 * @package StarWars
 */

// Stil paginacije: 'numbers' (default), 'post' (za single.php)
$pagination_style = isset($args['style']) ? $args['style'] : 'numbers';
$wrapper_class = isset($args['wrapper_class']) ? $args['wrapper_class'] : 'starwars-pagination';
$show_thumbnails = isset($args['show_thumbnails']) ? $args['show_thumbnails'] : false;
?>

<div class="<?php echo esc_attr($wrapper_class); ?>">
    <?php if ($pagination_style === 'numbers') : ?>
        <?php
        // Paginacija sa brojevima stranica (za blog/kategorije/arhive)
        echo wp_kses_post(
            paginate_links(array(
                'prev_text' => '<span class="arrow prev-arrow"></span><span class="nav-text">' . esc_html__('Prethodna', 'starwars') . '</span>',
                'next_text' => '<span class="nav-text">' . esc_html__('Sledeća', 'starwars') . '</span><span class="arrow next-arrow"></span>',
                'type'      => 'list',
                'mid_size'  => 2,
                'end_size'  => 1,
                'aria_current' => 'page',
            ))
        );
        ?>
    <?php elseif ($pagination_style === 'post') : ?>
        <?php
        // Navigacija za pojedinačne članke sa thumbnailima
        $prev_post = get_previous_post();
        $next_post = get_next_post();

        if (!empty($prev_post) || !empty($next_post)) :
        ?>
            <nav class="post-navigation enhanced" aria-label="<?php esc_attr_e('Navigacija članaka', 'starwars'); ?>">
                <div class="nav-links">
                    <?php if (!empty($prev_post)) :
                        $prev_thumb = $show_thumbnails ? get_the_post_thumbnail_url($prev_post->ID, 'thumbnail') : '';
                        $has_thumb = !empty($prev_thumb);
                    ?>
                        <div class="nav-previous <?php echo esc_attr($has_thumb ? 'has-thumb' : 'no-thumb'); ?>">
                            <a href="<?php echo esc_url(get_permalink($prev_post->ID)); ?>" rel="prev">
                                <?php if ($has_thumb) : ?>
                                    <div class="nav-thumb">
                                        <img src="<?php echo esc_url($prev_thumb); ?>" alt="<?php echo esc_attr(get_the_title($prev_post->ID)); ?>" loading="lazy" width="110" height="110">
                                        <div class="nav-overlay"></div>
                                    </div>
                                <?php endif; ?>
                                <div class="nav-content">
                                    <div class="nav-direction">
                                        <span class="arrow prev-arrow"></span>
                                        <?php esc_html_e('Prethodna vest', 'starwars'); ?>
                                    </div>
                                    <span class="nav-title"><?php echo esc_html(get_the_title($prev_post->ID)); ?></span>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($next_post)) :
                        $next_thumb = $show_thumbnails ? get_the_post_thumbnail_url($next_post->ID, 'thumbnail') : '';
                        $has_thumb = !empty($next_thumb);
                    ?>
                        <div class="nav-next <?php echo esc_attr($has_thumb ? 'has-thumb' : 'no-thumb'); ?>">
                            <a href="<?php echo esc_url(get_permalink($next_post->ID)); ?>" rel="next">
                                <?php if ($has_thumb) : ?>
                                    <div class="nav-thumb">
                                        <img src="<?php echo esc_url($next_thumb); ?>" alt="<?php echo esc_attr(get_the_title($next_post->ID)); ?>" loading="lazy" width="110" height="110">
                                        <div class="nav-overlay"></div>
                                    </div>
                                <?php endif; ?>
                                <div class="nav-content">
                                    <div class="nav-direction">
                                        <?php esc_html_e('Sledeća vest', 'starwars'); ?>
                                        <span class="arrow next-arrow"></span>
                                    </div>
                                    <span class="nav-title"><?php echo esc_html(get_the_title($next_post->ID)); ?></span>
                                </div>
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>