<!-- FRONT PAGE -->
<?php get_header(); ?>
<?php
remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30);
remove_action('woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30);
remove_action('woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30);
add_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);

if (have_posts()) :
    while (have_posts()) :
        the_post(); ?>
        <?php echo get_template_part('template-part/section', 'hero-slider'); ?>


        <div class="container products">
            <div class="head-products d-flex align-center">
                <h1 class="fs-1">Proizvodi</h1>
                <a class="links-all" href="<?php echo site_url() . '/prodavnica'; ?>">Pogledaj sve proizvode <i class="fa-solid fa-right-long"></i></a>
            </div>
            <?php if (is_active_sidebar('product-widget')) : ?>
                <div class="footer-widget-logo" role="complementary">
                    <?php dynamic_sidebar('product-widget'); ?>
                </div>
            <?php endif; ?>
            <div class="head-products d-flex align-center mt-5">
                <h1 class="fs-1">DEČIJE MAJICE</h1>
                <a class="links-all" href="<?php echo site_url() . '/product-category/decije-majice/'; ?>">Pogledaj sve dečije majice <i class="fa-solid fa-right-long"></i></a>
            </div>
            <?php echo do_shortcode('[wcpscwc_pdt_slider cats="131" slide_to_show="5"  limit="9" dots="true"  autoplay="false"]'); ?>
            <div class="head-products d-flex align-center mt-5">
                <h1 class="fs-1">MAJICE</h1>
                <a class="links-all" href="<?php echo site_url() . '/product-category/majice/'; ?>">Pogledaj sve majice <i class="fa-solid fa-right-long"></i></a>
            </div>
            <?php echo do_shortcode('[wcpscwc_pdt_slider cats="28" slide_to_show="5"  limit="9" dots="true"  autoplay="false"]'); ?>
            <div class="head-products d-flex align-center mt-5">
                <h1 class="fs-1">DUKSEVI</h1>
                <a class="links-all" href="<?php echo site_url() . '/product-category/duksevi/'; ?>">Pogledaj sve dukseve <i class="fa-solid fa-right-long"></i></a>
            </div>
            <?php echo do_shortcode('[wcpscwc_pdt_slider cats="29" slide_to_show="5"  limit="9" dots="true"  autoplay="false"]'); ?>
            <div class="head-products d-flex align-center mt-5">
                <h1 class="fs-1">HOODI</h1>
                <a class="links-all" href="<?php echo site_url() . '/product-category/hoodi/'; ?>">Pogledaj sve hoodi-e <i class="fa-solid fa-right-long"></i></a>
            </div>
            <?php echo do_shortcode('[wcpscwc_pdt_slider cats="34" slide_to_show="5"  limit="9" dots="true"  autoplay="false"]'); ?>
        </div>
        <div class="container dostava">
            <?php if (have_rows('dostava')) : ?>
                <div class="row">
                    <?php while (have_rows('dostava')) : the_row(); ?>
                        <?php
                        $image = get_sub_field('icon');
                        $head_title = get_sub_field('text');
                        $small_text = get_sub_field('small_text'); ?>
                        <div class="col-sm-12 col-md-12 col-lg-4 text-center mt-4 mt-lg-0">
                            <div class="content">
                                <img class="img-fluid" src="<?php echo $image; ?>" alt="<?php echo get_bloginfo('name'); ?>">
                                <h4><?php echo $head_title; ?></h4>
                                <h5><?php echo $small_text; ?></h5>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="container-fluid block">
            <div class="row">
                <?php if (have_rows('block')) :
                    $i = 0;
                    while (have_rows('block')) : the_row();
                        $image = get_sub_field('image');
                        $head_title = get_sub_field('text');
                        $link = get_sub_field('link'); ?>
                        <div class="col-sm-12 col-md-12 col-lg-6 p-0">
                            <a href="<?php echo $link; ?>">
                                <img class="img-fluid" src="<?php echo $image; ?>">
                                <h3><?php echo $head_title; ?></h3>
                                <div class="overlay">

                                    <h3><?php echo $head_title; ?></h3>
                                </div>
                            </a>
                        </div>

                    <?php endwhile; ?>
                <?php
                endif; ?>
            </div>
        </div>
        <div class="container my-5">
            <h1 id="news" class="my-5 fs-1">Najnovije vesti</h1>
            <div id="newsSlider" class="custom-slider">
                <div class="slider-inner">
                    <?php
                    $args = array(
                        'posts_per_page' => 5,
                        'post_status' => 'publish'
                    );
                    $latest_posts = new WP_Query($args);
                    while ($latest_posts->have_posts()) : $latest_posts->the_post();
                    ?>
                        <div class="slider-item">
                            <a href="<?php the_permalink(); ?>" class="link-posts">
                                <div class="custom-card">
                                    <div class="photo" style="background-image: url('<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>');"></div>
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
                                                            echo '<li><span>' . $tag->name . '</span>';
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
                    <?php endwhile;
                    wp_reset_postdata(); ?>
                </div>
                <button class="slider-control-prev">&#10094;</button>
                <button class="slider-control-next">&#10095;</button>
            </div>
        </div>



<?php
    endwhile;
endif;
get_footer(); ?>