<?php
/* 
Template Name: Archives
*/
get_header();
if ( have_posts() ) :
    while ( have_posts() ) : the_post(); ?>
        <div class="container"> 
            <h1><?php the_title();?></h1>
			<?php the_content();?>
    </div>
    <?php endwhile;
else :
    _e( 'Sorry, no posts were found.', 'textdomain' );
endif;
?>

<?php 
get_footer();