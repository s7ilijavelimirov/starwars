<?php

/**
 * Template za prikaz pojedinačnog članka
 *
 * @package StarWars
 */

get_header();

// Brojanje pregleda posta ako postoji funkcija
if (function_exists('setPostViews')) {
    setPostViews(get_the_ID());
}

// Start the loop
while (have_posts()) : the_post();
    // Dobijanje featured image URL-a
    $featured_image = get_the_post_thumbnail_url(get_the_ID(), 'full');
    $has_featured_image = !empty($featured_image);

    // Dobijanje kategorija
    $categories = get_the_category();
    $category_links = array();
    if (!empty($categories)) {
        foreach ($categories as $category) {
            $category_links[] = '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
        }
    }

    // Da li post ima tagove
    $has_tags = has_tag();
?>

    <article id="post-<?php the_ID(); ?>" <?php post_class('single-post'); ?> itemscope itemtype="https://schema.org/BlogPosting">

        <?php if ($has_featured_image) : ?>
            <!-- Hero section sa slikom ograničenom na 400px visine -->
            <div class="hero-banner-container fixed-height">
                <div class="hero-banner">
                    <div class="image-container">
                        <img src="<?php echo esc_url($featured_image); ?>" alt="<?php the_title_attribute(); ?>" class="featured-image">
                    </div>
                    <div class="hero-overlay">
                        <div class="container">
                            <div class="hero-content">
                                <?php if (!empty($categories)) : ?>
                                    <div class="post-categories">
                                        <?php echo implode(' ', $category_links); ?>
                                    </div>
                                <?php endif; ?>

                                <h1 class="entry-title" itemprop="headline"><?php the_title(); ?></h1>

                                <div class="post-meta d-flex align-items-center">
                                    <div class="author-logo">
                                        <?php
                                        if (has_custom_logo()):
                                            // Ako postoji prilagođeni logo, ispiši ga sa klasom i sakrij alt tekst
                                            $logo = wp_get_attachment_image_src(get_theme_mod('custom_logo'), 'full');
                                            echo '<img src="' . esc_url($logo[0]) . '" alt="" aria-hidden="true" class="logo-icon">';
                                        endif;
                                        ?>
                                    </div>

                                    <div class="meta-details">
                                        <div class="author-name" itemprop="author" itemscope itemtype="https://schema.org/Person">
                                            <span itemprop="name"><?php the_author(); ?></span>
                                        </div>

                                        <div class="post-date">
                                            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" itemprop="datePublished">
                                                <?php echo esc_html(get_the_date()); ?>
                                            </time>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php else : ?>
            <!-- Alternativni header bez featured slike -->
            <div class="standard-header bg-dark">
                <div class="container py-5">
                    <?php if (!empty($categories)) : ?>
                        <div class="post-categories">
                            <?php echo implode(' ', $category_links); ?>
                        </div>
                    <?php endif; ?>

                    <h1 class="entry-title" itemprop="headline"><?php the_title(); ?></h1>

                    <div class="post-meta d-flex align-items-center">
                        <div class="author-logo">
                            <img src="<?php echo get_template_directory_uri(); ?>/assets/images/logo-icon.png" alt="<?php bloginfo('name'); ?>" class="logo-icon">
                        </div>

                        <div class="meta-details">
                            <div class="author-name" itemprop="author" itemscope itemtype="https://schema.org/Person">
                                <span itemprop="name"><?php the_author(); ?></span>
                            </div>

                            <div class="post-date">
                                <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" itemprop="datePublished">
                                    <?php echo esc_html(get_the_date()); ?>
                                </time>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="content-wrapper">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <!-- Main content -->
                        <div class="entry-content" itemprop="articleBody">
                            <?php the_content(); ?>

                            <?php
                            // Pagination for multi-page posts
                            wp_link_pages(array(
                                'before' => '<div class="page-links my-4"><span class="page-links-title">' . esc_html__('Stranice:', 'starwars') . '</span>',
                                'after'  => '</div>',
                                'link_before' => '<span class="page-number">',
                                'link_after'  => '</span>',
                            ));
                            ?>
                        </div>

                        <?php if ($has_tags) : ?>
                            <div class="entry-tags">
                                <span class="tags-title"><?php esc_html_e('Tagovi:', 'starwars'); ?></span>
                                <?php the_tags('<div class="tag-list">', '', '</div>'); ?>
                            </div>
                        <?php endif; ?>

                        <div class="post-navigation-container">
                            <?php
                            // Koristi naš template-part za paginaciju sa stilom 'post'
                            get_template_part('template-parts/blog/pagination', null, array(
                                'style'         => 'post',
                                'wrapper_class' => 'post-navigation-wrapper',
                                'show_thumbnails' => true
                            ));
                            ?>
                        </div>

                        <?php
                        // Related posts
                        $current_post_id = get_the_ID();
                        $cat_ids = wp_list_pluck($categories, 'term_id');

                        if (!empty($cat_ids)) {
                            $related_args = array(
                                'post_type'      => 'post',
                                'posts_per_page' => 3,
                                'post_status'    => 'publish',
                                'post__not_in'   => array($current_post_id),
                                'category__in'   => $cat_ids,
                                'orderby'        => 'rand',
                            );

                            $related_query = new WP_Query($related_args);

                            if ($related_query->have_posts()) :
                        ?>
                                <div class="related-posts">
                                    <h3 class="section-title"><?php esc_html_e('Povezani članci', 'starwars'); ?></h3>

                                    <div class="related-posts-container">
                                        <div class="row">
                                            <?php while ($related_query->have_posts()) : $related_query->the_post(); ?>
                                                <div class="col-md-4">
                                                    <div class="related-post-card">
                                                        <a href="<?php the_permalink(); ?>" class="post-link">
                                                            <div class="post-thumbnail">
                                                                <?php if (has_post_thumbnail()) : ?>
                                                                    <?php the_post_thumbnail('medium_large', array('class' => 'img-fluid')); ?>
                                                                <?php else : ?>
                                                                    <div class="no-image">
                                                                        <i class="fa-solid fa-jedi"></i>
                                                                    </div>
                                                                <?php endif; ?>

                                                                <div class="post-overlay">
                                                                    <span class="read-more"><?php esc_html_e('Pročitaj više', 'starwars'); ?></span>
                                                                </div>
                                                            </div>
                                                            <div class="post-details">
                                                                <h4 class="post-title"><?php the_title(); ?></h4>
                                                                <div class="post-meta">
                                                                    <time datetime="<?php echo esc_attr(get_the_date('c')); ?>">
                                                                        <?php echo esc_html(get_the_date()); ?>
                                                                    </time>
                                                                </div>
                                                            </div>
                                                        </a>
                                                    </div>
                                                </div>
                                            <?php endwhile; ?>
                                        </div>
                                    </div>
                                </div>
                        <?php
                            endif;
                            wp_reset_postdata();
                        }
                        ?>
                    </div>

                    <div class="col-lg-4">
                        <aside class="single-sidebar">
                            <?php get_template_part('sidebar'); ?>
                        </aside>
                    </div>
                </div>
            </div>
        </div>
    </article>

<?php
endwhile;
get_footer();
