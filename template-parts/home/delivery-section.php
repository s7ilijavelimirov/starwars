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

<section class="delivery-section py-5">
    <div class="container">
        <div class="row justify-content-center">
            <?php while (have_rows('dostava')) : the_row();
                $image = get_sub_field('icon');
                $head_title = get_sub_field('text');
                $small_text = get_sub_field('small_text');
            ?>
                <div class="col-12 col-md-6 col-lg-4 d-flex align-items-stretch mb-4">
                    <div class="delivery-card text-center p-4 w-100">
                        <?php if ($image) : ?>
                            <img class="delivery-card__icon mb-3" src="<?php echo esc_url($image); ?>" alt="<?php echo esc_attr($head_title); ?>">
                        <?php endif; ?>
                        <h4 class="delivery-card__title mb-2"><?php echo esc_html($head_title); ?></h4>
                        <p class="delivery-card__text mb-0"><?php echo esc_html($small_text); ?></p>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </div>
</section>