<?php

/**
 * Template Part: Delivery Section
 *
 * @package s7design
 */

if (!function_exists('get_field') || !have_rows('dostava')) {
    return;
}
?>
<div class="container dostava">
    <div class="row">
        <?php while (have_rows('dostava')) : the_row();
            $image = get_sub_field('icon');
            $head_title = get_sub_field('text');
            $small_text = get_sub_field('small_text');
        ?>
            <div class="col-sm-12 col-md-12 col-lg-4 text-center mt-4 mt-lg-0">
                <div class="content">
                    <img class="img-fluid" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr(get_bloginfo('name')); ?>">
                    <h4><?php echo esc_html($head_title); ?></h4>
                    <h5><?php echo esc_html($small_text); ?></h5>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>