<?php

/**
 * Template : Footer
 *
 * @package S7design
 */
?>

</div> <!-- main-content -->

<footer class="site-footer" itemscope itemtype="https://schema.org/WPFooter">
    <!-- Star Wars granica -->
    <div class="sw-yellow-border" aria-hidden="true"></div>

    <!-- Back to top dugme sa SVG -->
    <div class="back-to-top-container">
        <button id="backToTop" class="back-to-top-btn" aria-label="Povratak na vrh">
            <svg xmlns="http://www.w3.org/2000/svg" width="35" height="35" viewBox="0 0 76 76" class="back-to-top-icon" aria-hidden="true">
                <path fill="currentColor" fill-rule="evenodd" stroke-linejoin="round" d="M 37.4819,43.369C 37.1558,42.893 35.416,40.3538 35.416,40.3538L 36.7419,44.0005L 32.6313,44.5973L 36.7419,45.194L 35.1507,48.3103C 35.1507,48.3103 37.1359,46.2232 37.4164,45.9283C 37.3008,50.4471 37.2724,51.5592 37.2724,51.5592C 37.2724,51.5592 27.1281,46.8516 32.7638,36.4418C 32.7638,36.4418 25.7357,28.6843 32.1008,23.9105C 32.1008,23.9105 21.2272,30.4745 28.1226,41.7462C 28.1226,41.7462 22.4206,36.1767 25.4042,30.5408C 25.4042,30.5408 20.2327,37.8343 26.5314,45.8571C 26.5314,45.8571 24.8076,44.7961 23.2826,40.7517C 23.2826,40.7517 24.395,52.792 37.8658,52.95L 37.8658,52.9516C 37.9106,52.9516 37.9554,52.9514 38,52.9512C 38.0446,52.9514 38.0894,52.9516 38.1343,52.9516L 38.1343,52.95C 51.6051,52.792 52.7175,40.7517 52.7175,40.7517C 51.1925,44.7961 49.4687,45.8571 49.4687,45.8571C 55.7674,37.8343 50.5958,30.5408 50.5958,30.5408C 53.5794,36.1767 47.8774,41.7462 47.8774,41.7462C 54.7729,30.4745 43.8993,23.9105 43.8993,23.9105C 50.2643,28.6843 43.2362,36.4418 43.2362,36.4418C 48.8719,46.8516 38.7277,51.5592 38.7277,51.5592C 38.7277,51.5592 38.6992,50.4471 38.5836,45.9283C 38.8641,46.2232 40.8494,48.3103 40.8494,48.3103L 39.2581,45.194L 43.3688,44.5973L 39.2581,44.0005L 40.5841,40.3538C 40.5841,40.3538 38.8443,42.893 38.5182,43.369C 38.3705,37.597 38.0041,23.2757 38.0024,23.2081L 38.0017,23.0484L 38,23.1144L 37.9983,23.0484L 37.9976,23.2081C 37.9947,23.321 37.6292,37.6101 37.4819,43.369 Z"></path>
                <path fill="currentColor" fill-rule="evenodd" stroke-width="0.32" stroke-linejoin="round" d="M 38,19C 27.5068,19 19,27.5067 19,38C 19,48.4932 27.5068,57 38,57C 48.4933,57 57,48.4932 57,38C 57,27.5067 48.4933,19 38,19 Z M 38,21.2763C 47.2361,21.2763 54.7237,28.7639 54.7237,38C 54.7237,47.2361 47.2361,54.7236 38,54.7236C 28.7639,54.7236 21.2764,47.2361 21.2764,38C 21.2764,28.7639 28.7639,21.2763 38,21.2763 Z"></path>
            </svg>
            <span class="back-to-top-text">VRH</span>
        </button>
    </div>

    <div class="footer-main py-5">
        <div class="container">
            <div class="row">
                <!-- Logo kolona -->
                <div class="col-lg-3 col-md-6 col-sm-12 footer-logo-column">
                    <?php if (is_active_sidebar('footer_4')) : ?>
                        <?php dynamic_sidebar('footer_4'); ?>
                    <?php else : ?>
                        <a href="<?php echo esc_url(home_url('/')); ?>" class="footer-logo" aria-label="<?php echo esc_attr(get_bloginfo('name')); ?> homepage">
                            <?php if (has_custom_logo()) : ?>
                                <?php
                                // Umesto korišćenja wp_get_attachment_image, koristimo direktni pristup
                                $custom_logo_id = get_theme_mod('custom_logo');
                                $image_data = wp_get_attachment_image_src($custom_logo_id, 'full');

                                if ($image_data) {
                                    $image_url = $image_data[0];
                                    $image_alt = get_post_meta($custom_logo_id, '_wp_attachment_image_alt', true);
                                    if (empty($image_alt)) {
                                        $image_alt = get_bloginfo('name');
                                    }
                                ?>
                                    <img
                                        src="<?php echo esc_url($image_url); ?>"
                                        class="footer-logo-img"
                                        alt="<?php echo esc_attr($image_alt); ?>"
                                        itemprop="logo"
                                        width="180"
                                        height="180"
                                        loading="lazy"
                                        style="width: 180px; height: auto; max-width: 180px; object-fit: contain;">
                                <?php
                                } else {
                                    echo '<h3 class="footer-site-title">' . esc_html(get_bloginfo('name')) . '</h3>';
                                }
                                ?>
                            <?php else : ?>
                                <h3 class="footer-site-title"><?php echo esc_html(get_bloginfo('name')); ?></h3>
                            <?php endif; ?>
                        </a>

                        <!-- Dodajemo naziv sajta ispod logoa -->
                        <p class="footer-site-name" role="contentinfo"><?php echo esc_html(get_bloginfo('name')); ?></p>


                        <!-- Dodajemo email sa ikonom -->
                        <div class="footer-contact-info">
                            <a href="mailto:office@starwarssrbija.rs" class="footer-email">
                                office@starwarssrbija.rs
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Social ikone -->
                    <?php if (is_active_sidebar('footer_3')) : ?>
                        <div class="footer-social-icons" role="complementary">
                            <?php dynamic_sidebar('footer_3'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Footer menije (Navigacija) -->
                <div class="col-lg-5 col-md-6 col-sm-12 footer-menus-container">
                    <?php if (is_active_sidebar('footer_2')) : ?>
                        <div class="row footer-widget-menus-row">
                            <?php dynamic_sidebar('footer_2'); ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Blog postovi -->
                <div class="col-lg-4 col-md-12 col-sm-12 footer-blog-container">
                    <div class="footer-widget-blog">
                        <div class="blog">
                            <h2 class="rounded">Blog</h2>
                            <?php
                            // Optimizovano dohvatanje postova sa preload hint-ovima
                            $recent_posts = get_posts(array(
                                'post_type' => 'post',
                                'numberposts' => 3,
                                'post_status' => 'publish'
                            ));

                            if ($recent_posts) : ?>
                                <ul>
                                    <?php
                                    // Preload glavnih slika za bolje performanse
                                    $image_urls = array();
                                    foreach ($recent_posts as $post) {
                                        $thumb_id = get_post_thumbnail_id($post->ID);
                                        if ($thumb_id) {
                                            $thumb_url = wp_get_attachment_image_url($thumb_id, 'thumbnail');
                                            if ($thumb_url) {
                                                $image_urls[] = $thumb_url;
                                            }
                                        }
                                    }

                                    // Dodajemo preload hint za slike
                                    foreach ($image_urls as $url) {
                                        echo '<link rel="preload" href="' . esc_url($url) . '" as="image">';
                                    }

                                    // Ispisujem postove
                                    foreach ($recent_posts as $post) :
                                        setup_postdata($post);
                                        // Optimizovano dobavljanje thumbnail slike
                                        $thumb_id = get_post_thumbnail_id($post->ID);
                                        $thumb_url = '';
                                        $thumb_alt = '';

                                        if ($thumb_id) {
                                            $thumb_url = wp_get_attachment_image_url($thumb_id, 'thumbnail');
                                            $thumb_alt = get_post_meta($thumb_id, '_wp_attachment_image_alt', true);
                                        }

                                        if (!$thumb_url) {
                                            $thumb_url = get_template_directory_uri() . '/assets/images/default-post.jpg';
                                            $thumb_alt = get_the_title();
                                        }

                                        if (empty($thumb_alt)) {
                                            $thumb_alt = get_the_title();
                                        }
                                    ?>
                                        <li class="footer-blog-item">
                                            <div class="footer-blog-image">
                                                <a href="<?php the_permalink(); ?>" aria-label="Read more about <?php echo esc_attr(get_the_title()); ?>">
                                                    <img
                                                        src="<?php echo esc_url($thumb_url); ?>"
                                                        alt="<?php echo esc_attr($thumb_alt); ?>"
                                                        loading="lazy"
                                                        width="70"
                                                        height="70">
                                                </a>
                                            </div>
                                            <div class="footer-blog-content">
                                                <a href="<?php the_permalink(); ?>"><?php echo esc_html(get_the_title()); ?></a>
                                                <span class="post-date"><?php echo esc_html(get_the_date()); ?></span>
                                            </div>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php
                                wp_reset_postdata();
                            endif;
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Copyright red -->
            <div class="row footer-copyright-row">
                <div class="col-md-12">
                    <?php if (is_active_sidebar('footer_1')) : ?>
                        <div class="footer-copyright" role="complementary">
                            <?php dynamic_sidebar('footer_1'); ?>
                        </div>
                    <?php else : ?>
                        <div class="footer-copyright">
                            <p>&copy; <?php echo esc_html(date('Y')); ?> <?php echo esc_html(get_bloginfo('name')); ?>. Sva prava zadržana.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</footer>

<?php wp_footer(); ?>

<script>
    // Jednostavan JS za "Back to top" funkcionalnost
    document.addEventListener('DOMContentLoaded', function() {
        const backToTopButton = document.getElementById('backToTop');

        // Prikaži/sakrij dugme na skrol
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                backToTopButton.classList.add('show');
            } else {
                backToTopButton.classList.remove('show');
            }
        });

        // Brzi scroll to top kada se klikne
        backToTopButton.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Dodavanje "active" klase na trenutnu stranicu u navigaciji
        const currentPath = window.location.pathname;
        const navLinks = document.querySelectorAll('.navbar-nav a');

        navLinks.forEach(link => {
            const linkPath = new URL(link.href).pathname;
            if (currentPath === linkPath ||
                (currentPath.includes(linkPath) && linkPath !== '/')) {
                link.classList.add('active');
            }
        });
    });
</script>
</body>

</html>