<?php

/**
 * Optimizovani template za prikaz blog kartica u Star Wars stilu
 *
 * @package StarWars
 */

// Post counter se prosleđuje iz parent šablona
$post_counter = isset($args['post_counter']) ? $args['post_counter'] : 0;
$column_class = ($post_counter == 0) ? 'col-sm-12' : 'col-sm-12 col-md-6';
$featured_class = ($post_counter == 0) ? 'featured-post' : '';
$excerpt_length = ($post_counter == 0) ? 30 : 20; // Duži excerpt za featured post
?>

<div class="<?php echo esc_attr($column_class); ?>">
    <article <?php post_class('sw-card ' . $featured_class); ?> id="post-<?php the_ID(); ?>">
        <a href="<?php the_permalink(); ?>" class="card-link" aria-label="<?php echo esc_attr(get_the_title()); ?>">
            <!-- Card Header sa thumbnail-om -->
            <div class="card-header">
                <?php if (has_post_thumbnail()) :
                    $thumbnail_id = get_post_thumbnail_id();
                    $alt_text = get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) ?: get_the_title();
                    $image_src = wp_get_attachment_image_src($thumbnail_id, 'large');
                    $width = $image_src[1];
                    $height = $image_src[2];
                ?>
                    <div class="card-thumbnail">
                        <?php the_post_thumbnail('large', array(
                            'class' => 'thumbnail-img',
                            'alt' => esc_attr($alt_text),
                            'loading' => 'lazy',
                            'width' => $width,
                            'height' => $height
                        )); ?>
                    </div>
                <?php else : ?>
                    <div class="card-thumbnail no-image">
                        <span class="sw-icon">SW</span>
                    </div>
                <?php endif; ?>

                <!-- Kategorija - badge -->
                <?php
                $categories = get_the_category();
                if (!empty($categories)) :
                    $category = $categories[0]; // Prvi prikazujemo
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

            <!-- Card Body sa sadržajem -->
            <div class="card-body">
                <!-- Post meta podaci -->
                <div class="card-meta">
                    <span class="post-date">
                        <?php echo esc_html(get_the_date()); ?>
                    </span>

                    <span class="post-author">
                        <?php the_author(); ?>
                    </span>
                </div>

                <!-- Naslov posta -->
                <h2 class="card-title">
                    <?php the_title(); ?>
                </h2>

                <!-- Automatski excerpt umesto custom -->
                <div class="card-excerpt">
                    <?php
                    // Dobijanje sadržaja posta, uklanjanje shortcode-ova
                    $content = get_the_content();
                    $content = strip_shortcodes($content);
                    $content = wp_strip_all_tags($content);

                    // Prikazivanje ograničenog broja reči
                    echo esc_html(wp_trim_words($content, $excerpt_length, '...'));
                    ?>
                </div>
            </div>

            <!-- Card Footer sa CTA -->
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

            <!-- Svetlosni efekat -->
            <div class="sw-light-effect"></div>
        </a>
    </article>
</div>