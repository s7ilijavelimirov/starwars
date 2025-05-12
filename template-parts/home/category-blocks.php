<?php
/**
 * Template Part: Category Blocks
 *
 * @package s7design
 */

if (!function_exists('get_field') || !have_rows('block')) {
    return;
}
?>
<div class="container-fluid block">
    <div class="row">
        <?php while (have_rows('block')) : the_row();
            $image = get_sub_field('image');
            $head_title = get_sub_field('text');
            $link = get_sub_field('link'); 
        ?>
            <div class="col-sm-12 col-md-12 col-lg-6 p-0">
                <a href="<?php echo esc_url($link); ?>">
                    <img class="img-fluid" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($head_title); ?>">
                    <h3><?php echo esc_html($head_title); ?></h3>
                    <div class="overlay">
                        <h3><?php echo esc_html($head_title); ?></h3>
                    </div>
                </a>
            </div>
        <?php endwhile; ?>
    </div>
</div>