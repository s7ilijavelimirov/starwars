<?php

/**
 * The template for displaying all single posts and attachments
 *
 */

get_header();
setPostViews(get_the_ID()); // Update post views count
?>
<div id="single-post" class="container-fluid p-0">
    <?php
    // Start the loop.
    while (have_posts()) : the_post();
        $url = get_the_post_thumbnail_url(get_the_ID(), 'full'); // Get full size image
    ?>
        <!-- Hero Image Section -->
        <div class="hero-image" style="background-image: url('<?php echo $url; ?>')">
            <div class="hero-gradient">
                <div class="hero-content container">
                    <div class="post-titles d-flex flex-column flex-lg-row align-items-start justify-content-center">
                        <div class="date">
                            <span class="day"><?php echo get_the_date('d'); ?></span>
                            <span class="month"><?php echo get_the_date('M'); ?></span>
                        </div>
                        <h1 class="post-title text-center"><?php echo wp_strip_all_tags(get_the_title()); ?></h1>
                    </div>
                    <div class="post-meta d-flex flex-column align-items-center">
                        <span class="author text-uppercase">By: <?php the_author(); ?></span>
                        <span class="tag"><?php the_tags(); ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Post Content Section -->
        <div class="content container">
            <?php the_content(); ?>
        </div>
    <?php
    endwhile;
    ?>
    <div class="container my-5">
        <?php
        // Previous/next post navigation.
        the_post_navigation(array(
            'next_text' => '<span class="meta-nav" aria-hidden="true">' . __('Sledeća', 'starwars') . ' <i class="fa-solid fa-arrow-right"></i></span> ' .
                '<span class="screen-reader-text">' . __('Sledeća vest', 'starwars') . '</span> ' .
                '<span class="post-title">%title</span>',
            'prev_text' => '<span class="meta-nav" aria-hidden="true"><i class="fa-solid fa-arrow-left"></i> ' . __('Prethodna vest', 'starwars') . '</span> ' .
                '<span class="screen-reader-text">' . __('Prethodna vest', 'starwars') . '</span> ' .
                '<span class="post-title">%title</span>',
        ));
        ?>
    </div>
</div>
<?php get_footer(); ?>