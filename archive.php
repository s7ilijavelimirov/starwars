<?php
get_header();

if (have_posts()) :
    $post_counter = 0; // Initialize a counter to track post index
?>
    <div class="container my-5">
        <h1 class="mb-5"><?php echo single_post_title(); ?></h1>
        <div class="row justify-content-between" id="blogpage">
            <div class="col-sm-12 col-md-12 col-lg-8">
                <div class="row">
                    <?php while (have_posts()) : the_post();
                        $col_class = ($post_counter == 0) ? 'col-sm-12' : 'col-sm-12 col-xl-6'; // First post col-12, others col-6
                    ?>
                        <div class="<?php echo $col_class; ?> mb-4">
                            <div class="blog-card <?php echo $col_class = ($post_counter == 0) ? 'flex-column' : 'flex-column'; ?>">
                                <div class="meta">
                                    <div class="photo" style="background-image: url(<?php echo get_the_post_thumbnail_url(get_the_ID(), 'full'); ?>)"></div>
                                    <ul class="details">
                                        <li class="author text-uppercase"><a href="<?php echo get_author_posts_url(get_the_author_meta('ID')); ?>"><?php the_author(); ?></a></li>
                                        <li class="date"><?php the_date(); ?></li>
                                        <li class="tags mt-auto">
                                            <ul>
                                                <?php
                                                $posttags = get_the_tags();
                                                if ($posttags) {
                                                    $last_tag = end($posttags); // Dohvati poslednji tag
                                                    foreach ($posttags as $tag) {
                                                        echo '<li><a href="' . get_tag_link($tag->term_id) . '">' . $tag->name . '</a>';
                                                        if ($tag !== $last_tag) {
                                                            echo ' | '; // Dodaj znak "|" samo ako tag nije poslednji
                                                        }
                                                        echo '</li> ';
                                                    }
                                                }
                                                ?>
                                            </ul>
                                        </li>
                                    </ul>
                                </div>

                                <div class="description">
                                    <h1><?php the_title(); ?></h1>
                                    <p class="mt-4"><?php echo wp_trim_words(get_the_content(), 20, '...'); ?></p>
                                    <?php if ($col_class = ($post_counter == 0)) : ?>
                                        <p class="read-more mt-auto mb-0 me-3">
                                            <a class="read" href="<?php the_permalink(); ?>">Pročitaj više</a>
                                        </p>
                                    <?php endif; ?>
                                </div>

                                <?php if ($col_class = ($post_counter > 0)) : ?>
                                    <p class="read-more mt-auto mb-4 me-3">
                                        <a href="<?php the_permalink(); ?>">Pročitaj više</a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php
                        $post_counter++; // Increment post counter
                    endwhile; ?>
                </div>
                <div class="starwars-pagination">
                    <?php echo the_posts_pagination(); ?>
                </div>
            </div>
            <div class="col-sm-12 col-md-12 col-lg-4">
                <?php get_template_part('sidebar'); ?>
            </div>
        </div>
    </div>
<?php
else :
    _e('Sorry, no posts were found.', 'textdomain');
endif;
get_footer();
?>