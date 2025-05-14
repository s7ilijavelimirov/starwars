<?php

/**
 * Template part za prikaz pojedinačnog blog članka
 *
 * @package StarWars
 */

// Dodatne opcije koje se mogu prosleđivati
$show_featured_image = isset($args['show_featured_image']) ? $args['show_featured_image'] : true;
?>

<article id="post-<?php the_ID(); ?>" <?php post_class('single-post'); ?> itemscope itemtype="https://schema.org/BlogPosting">

    <?php if ($show_featured_image && has_post_thumbnail()) : ?>
        <div class="hero-image" style="background-image: url('<?php echo esc_url(get_the_post_thumbnail_url(get_the_ID(), 'full')); ?>')">
            <div class="hero-gradient">
                <div class="hero-content container">
                    <div class="post-titles d-flex flex-column flex-lg-row align-items-start justify-content-center">
                        <div class="date">
                            <span class="day"><?php echo get_the_date('d'); ?></span>
                            <span class="month"><?php echo get_the_date('M'); ?></span>
                        </div>
                        <h1 class="post-title text-center" itemprop="headline"><?php echo wp_kses_post(get_the_title()); ?></h1>
                    </div>

                    <div class="post-meta d-flex flex-column align-items-center">
                        <span class="author text-uppercase">
                            <?php esc_html_e('By:', 'starwars'); ?>
                            <span itemprop="author" itemscope itemtype="https://schema.org/Person">
                                <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>" itemprop="url">
                                    <span itemprop="name"><?php the_author(); ?></span>
                                </a>
                            </span>
                        </span>

                        <?php if (has_tag()) : ?>
                            <span class="tag"><?php the_tags('', ', ', ''); ?></span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php else : ?>
        <header class="entry-header container my-5">
            <h1 class="entry-title" itemprop="headline"><?php the_title(); ?></h1>

            <?php
            // Uključi meta podatke
            get_template_part('template-parts/blog/meta', null, array('display_style' => 'full'));
            ?>
        </header>
    <?php endif; ?>

    <div class="entry-content container my-5" itemprop="articleBody">
        <?php
        the_content();

        wp_link_pages(array(
            'before' => '<div class="page-links">' . esc_html__('Stranice:', 'starwars'),
            'after'  => '</div>',
        ));
        ?>
    </div>

    <footer class="entry-footer container mb-5">
        <?php if (function_exists('get_field') && get_field('article_source')) : ?>
            <div class="source-citation">
                <p class="source-text">
                    <strong><?php esc_html_e('Izvor:', 'starwars'); ?></strong>
                    <?php echo wp_kses_post(get_field('article_source')); ?>
                </p>
            </div>
        <?php endif; ?>

        <div class="article-footer-meta">
            <meta itemprop="datePublished" content="<?php echo esc_attr(get_the_date('c')); ?>">
            <meta itemprop="dateModified" content="<?php echo esc_attr(get_the_modified_date('c')); ?>">

            <?php if (has_category()) : ?>
                <div itemprop="about" itemscope itemtype="https://schema.org/Thing">
                    <meta itemprop="name" content="<?php echo esc_attr(get_the_category()[0]->name); ?>">
                </div>
            <?php endif; ?>
        </div>
    </footer>
</article>