<?php

/**
 * Template part za prikaz metapodataka članka
 *
 * @package StarWars
 */

// Način prikazivanja metapodataka: 'compact' ili 'full'
$display_style = isset($args['display_style']) ? $args['display_style'] : 'full';
$show_tags = isset($args['show_tags']) ? $args['show_tags'] : true;
$show_categories = isset($args['show_categories']) ? $args['show_categories'] : true;
?>

<div class="post-meta <?php echo esc_attr($display_style); ?>">
    <?php if ($display_style === 'full') : ?>
        <div class="meta-row d-flex flex-wrap align-items-center mb-3">
        <?php endif; ?>

        <div class="post-date me-4">
            <i class="fa-regular fa-calendar-alt me-1" aria-hidden="true"></i>
            <time datetime="<?php echo esc_attr(get_the_date('c')); ?>" itemprop="datePublished">
                <?php echo esc_html(get_the_date()); ?>
            </time>
        </div>

        <div class="post-author me-4">
            <i class="fa-regular fa-user me-1" aria-hidden="true"></i>
            <span itemprop="author" itemscope itemtype="https://schema.org/Person">
                <a href="<?php echo esc_url(get_author_posts_url(get_the_author_meta('ID'))); ?>" itemprop="url">
                    <span itemprop="name"><?php the_author(); ?></span>
                </a>
            </span>
        </div>

        <?php if ($show_categories && has_category()) : ?>
            <div class="post-categories me-4">
                <i class="fa-solid fa-folder me-1" aria-hidden="true"></i>
                <?php
                $categories = get_the_category();
                $cat_links = array();

                foreach ($categories as $category) {
                    $cat_links[] = '<a href="' . esc_url(get_category_link($category->term_id)) . '">' . esc_html($category->name) . '</a>';
                }

                echo implode(', ', $cat_links);
                ?>
            </div>
        <?php endif; ?>

        <?php if ($display_style === 'full') : ?>
        </div>
    <?php endif; ?>

    <?php if ($show_tags && has_tag()) : ?>
        <div class="post-tags <?php echo ($display_style === 'full') ? 'mt-3' : 'mt-2 me-4'; ?>">
            <i class="fa-solid fa-tags me-1" aria-hidden="true"></i>
            <?php
            $tags = get_the_tags();
            $tag_links = array();

            if ($tags) {
                foreach ($tags as $tag) {
                    $tag_links[] = '<a href="' . esc_url(get_tag_link($tag->term_id)) . '" rel="tag">' . esc_html($tag->name) . '</a>';
                }

                echo implode(', ', $tag_links);
            }
            ?>
        </div>
    <?php endif; ?>
</div>