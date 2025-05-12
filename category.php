<?php

/**
 * The template for displaying all single posts and attachments
 */

get_header();
if (have_posts()) : ?>
    <div class="container my-5">
        <div class="row justify-content-between">
            <div class="col-sm-12 col-md-12 col-lg-7">
                <?php while (have_posts()) : the_post(); ?>
                    <div class="left-sidebar">
                        <p class="date"><?php the_date(); ?></p>
                        <?php $url =  get_the_post_thumbnail_url(); ?>
                        <a href="<?php echo the_permalink() ?>">
                            <h1><?php echo wp_strip_all_tags(get_the_title()); ?></h1>
                            <?php if ($url) : ?>
                                <img class="img-responsive w-100 single" src="<?php echo $url; ?>" alt="<?php the_title(); ?>">
                            <?php elseif (get_field('youtube')) : ?>
                                <?php echo the_field('youtube'); ?>
                            <?php endif; ?>
                            <p><?php the_excerpt(); ?></p>
                        </a>
                    </div>
                <?php endwhile; ?>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-4">
                <?php get_template_part('sidebar'); ?>
            </div>
        </div>
    </div>
<?php 
else :
    _e('Sorry, no posts were found.', 'textdomain');
    
endif;
?>

<?php
get_footer();
