<?php

/**
 * Template Part: Category Banners in Container
 *
 * @package s7design
 */

if (!function_exists('get_field') || !have_rows('block')) {
    return;
}
?>

<section class="category-banners-section py-5">
    <div class="container">
        <div class="row g-4">
            <?php while (have_rows('block')) : the_row();
                $image = get_sub_field('image');
                $head_title = get_sub_field('text');
                $link = get_sub_field('link');
            ?>
                <div class="col-12 col-md-6">
                    <a href="<?php echo esc_url($link); ?>" class="category-banner d-block position-relative text-center" aria-label="<?php echo esc_attr(strip_tags($head_title)); ?>">
                        <div class="banner-img-wrapper">
                            <img src="<?php echo esc_url($image); ?>" alt="" class="img-fluid w-100 h-100 banner-img" loading="lazy" decoding="async" role="presentation">
                        </div>
                        <div class="banner-content">
                            <h3 class="banner-title"><?php echo wp_kses_post($head_title); ?></h3>
                        </div>
                    </a>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>