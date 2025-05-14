<?php

/**
 * Template za prikaz arhiva (datuma, tagova)
 *
 * @package StarWars
 */

get_header();
?>

<div class="archive-header py-5 bg-dark">
    <div class="container">
        <h1 class="page-title text-center">
            <?php
            if (is_day()) :
                printf(esc_html__('Dnevna arhiva: %s', 'starwars'), get_the_date());
            elseif (is_month()) :
                printf(esc_html__('Mesečna arhiva: %s', 'starwars'), get_the_date(_x('F Y', 'monthly archives date format', 'starwars')));
            elseif (is_year()) :
                printf(esc_html__('Godišnja arhiva: %s', 'starwars'), get_the_date(_x('Y', 'yearly archives date format', 'starwars')));
            elseif (is_tag()) :
                printf(esc_html__('Tag: %s', 'starwars'), single_tag_title('', false));
            else :
                esc_html_e('Arhiva', 'starwars');
            endif;
            ?>
        </h1>

        <?php if (function_exists('yoast_breadcrumb')) : ?>
            <div class="breadcrumbs mt-3 text-center">
                <?php yoast_breadcrumb('<p id="breadcrumbs" class="mb-0">', '</p>'); ?>
            </div>
        <?php endif; ?>

        <?php
        // Prikaz opisa taksonomije ako je dostupan
        $term_description = term_description();
        if (!empty($term_description)) :
            printf('<div class="archive-description mt-4 text-center">%s</div>', wp_kses_post($term_description));
        endif;
        ?>
    </div>
</div>

<div class="content-area py-5">
    <div class="container">
        <div class="row justify-content-between" id="blogpage">
            <div class="col-sm-12 col-md-12 col-lg-8 mb-5 mb-lg-0">
                <main id="main" class="site-main" role="main">
                    <?php if (have_posts()) : ?>
                        <div class="row">
                            <?php
                            $post_counter = 0;
                            while (have_posts()) :
                                the_post();

                                // Prosleđujemo post_counter template-partu za responsive layout
                                get_template_part('template-parts/blog/content-blog', null, array(
                                    'post_counter' => $post_counter
                                ));

                                $post_counter++;
                            endwhile;
                            ?>
                        </div>

                        <?php
                        // Paginacija sa brojevima
                        get_template_part('template-parts/blog/pagination', null, array(
                            'style' => 'numbers',
                            'wrapper_class' => 'starwars-pagination mt-4'
                        ));
                        ?>

                    <?php else : ?>
                        <div class="no-results">
                            <h2><?php esc_html_e('Nema pronađenih objava', 'starwars'); ?></h2>
                            <p><?php esc_html_e('Nije pronađena nijedna objava. Probajte da pretražite sajt.', 'starwars'); ?></p>
                            <?php get_search_form(); ?>
                        </div>
                    <?php endif; ?>
                </main>
            </div>

            <div class="col-sm-12 col-md-12 col-lg-4">
                <aside id="secondary" class="sidebar widget-area" role="complementary">
                    <?php get_template_part('sidebar'); ?>
                </aside>
            </div>
        </div>
    </div>
</div>

<?php
get_footer();
