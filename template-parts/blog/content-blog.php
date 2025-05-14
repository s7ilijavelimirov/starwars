<?php

/**
 * Template part za prikaz blog kartica u modernom Star Wars stilu - bez ikonica
 *
 * @package StarWars
 */

// Post counter se prosleđuje iz parent šablona (home.php, archive.php, category.php)
$post_counter = isset($args['post_counter']) ? $args['post_counter'] : 0;
$column_class = ($post_counter == 0) ? 'col-sm-12' : 'col-sm-12 col-md-6';
$featured_class = ($post_counter == 0) ? 'featured-post' : '';
?>

<div class="<?php echo esc_attr($column_class); ?>">
    <article <?php post_class('sw-card ' . $featured_class); ?> id="post-<?php the_ID(); ?>">
        <a href="<?php the_permalink(); ?>" class="card-link" aria-label="<?php echo esc_attr(get_the_title()); ?>">
            <!-- Card Header with thumbnail -->
            <div class="card-header">
                <?php if (has_post_thumbnail()) : ?>
                    <div class="card-thumbnail">
                        <?php the_post_thumbnail('large', array('class' => 'thumbnail-img')); ?>
                    </div>
                <?php else : ?>
                    <div class="card-thumbnail no-image"></div>
                <?php endif; ?>

                <!-- Category badge -->
                <?php
                $categories = get_the_category();
                if (!empty($categories)) :
                    $category = $categories[0]; // Prvi pokazujemo
                ?>
                    <span class="category-badge">
                        <?php echo esc_html($category->name); ?>
                    </span>
                <?php endif; ?>

                <!-- Hover overlay -->
                <div class="hover-overlay">
                    <span class="read-more-text">
                        <?php esc_html_e('Pročitaj članak', 'starwars'); ?>
                    </span>
                </div>
            </div>

            <!-- Card Body with content -->
            <div class="card-body">
                <!-- Post meta info -->
                <div class="card-meta">
                    <span class="post-date">
                        <?php echo esc_html(get_the_date()); ?>
                    </span>

                    <span class="post-author">
                        <?php the_author(); ?>
                    </span>
                </div>

                <!-- Post title -->
                <h2 class="card-title">
                    <?php the_title(); ?>
                </h2>

                <!-- Post excerpt -->
                <div class="card-excerpt">
                    <?php echo wp_kses_post(wp_trim_words(get_the_excerpt(), 20, '...')); ?>
                </div>
            </div>

            <!-- Card Footer with CTA -->
            <div class="card-footer">
                <span class="read-more-btn">
                    <?php esc_html_e('Pročitaj više', 'starwars'); ?>
                </span>

                <?php if (has_tag()) : ?>
                    <div class="post-tags">
                        <?php
                        $post_tags = get_the_tags();
                        if ($post_tags) {
                            $tag_count = 0;
                            foreach ($post_tags as $tag) {
                                if ($tag_count < 2) { // Prikaži samo dva taga
                                    echo '<span class="tag">' . esc_html($tag->name) . '</span>';
                                    $tag_count++;
                                }
                            }

                            // Ako ima više od 2 taga, prikaži +N
                            if (count($post_tags) > 2) {
                                echo '<span class="tag-more">+' . (count($post_tags) - 2) . '</span>';
                            }
                        }
                        ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Light effect -->
            <div class="sw-light-effect"></div>
        </a>
    </article>
</div>