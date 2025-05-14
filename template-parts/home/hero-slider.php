<?php
// Provera da li postoji hero_slider
if (have_rows('hero_slider')) : ?>
    <div id="heroCarousel" class="hero-carousel carousel slide carousel-fade" data-bs-ride="carousel">
        <!-- Indikatori -->
        <div class="carousel-indicators">
            <?php
            $slide_count = count(get_field('hero_slider'));
            for ($i = 0; $i < $slide_count; $i++) : ?>
                <button
                    type="button"
                    data-bs-target="#heroCarousel"
                    data-bs-slide-to="<?php echo $i; ?>"
                    class="indicator <?php echo $i === 0 ? 'active' : ''; ?>"
                    aria-label="Slide <?php echo $i + 1; ?>"></button>
            <?php endfor; ?>
        </div>

        <!-- Slide wrapper -->
        <div class="carousel-inner">
            <?php
            $slide_index = 0;
            while (have_rows('hero_slider')) : the_row();
                // Provera i fallback za slike
                $desktop_image = get_sub_field('desktop_image');
                $mobile_image = get_sub_field('mobile_image');

                // Ako nema slike, preskočite ovaj slajd
                if (!$desktop_image) continue;

                $title = get_sub_field('title');
                $title_color = get_sub_field('title_color') ?: '#ffffff';
                $subtitle = get_sub_field('subtitle');
                $subtitle_color = get_sub_field('subtitle_color') ?: '#ffffff';
                $button = get_sub_field('button');
                $button_color = get_sub_field('button_color') ?: '#007bff';
                $content_alignment = get_sub_field('content_alignment') ?: 'center';
                $animation_type = get_sub_field('animation_type') ?: 'fade-enter';
            ?>
                <div class="carousel-item <?php echo $slide_index === 0 ? 'active' : ''; ?> <?php echo $animation_type; ?>"
                    style="text-align: <?php echo $content_alignment; ?>;">
                    <!-- Overlay -->
                    <div class="carousel-overlay"></div>

                    <!-- Responsive slike -->
                    <picture>
                        <?php if ($mobile_image) : ?>
                            <source media="(max-width: 768px)" srcset="<?php echo esc_url($mobile_image['url']); ?>">
                        <?php endif; ?>
                        <img
                            src="<?php echo esc_url($desktop_image['url']); ?>"
                            alt="<?php echo esc_attr($desktop_image['alt'] ?: $title); ?>"
                            class="carousel-image"
                            loading="lazy">
                    </picture>

                    <!-- Sadržaj slajda -->
                    <div class="carousel-caption">
                        <?php if ($title) : ?>
                            <h1 class="hero-title" style="color: <?php echo esc_attr($title_color); ?>;">
                                <?php echo wp_kses_post($title); ?>
                            </h1>
                        <?php endif; ?>

                        <?php if ($subtitle) : ?>
                            <p class="hero-subtitle" style="color: <?php echo esc_attr($subtitle_color); ?>;">
                                <?php echo wp_kses_post($subtitle); ?>
                            </p>
                        <?php endif; ?>

                        <?php if ($button) : ?>
                            <a
                                href="<?php echo esc_url($button['url']); ?>"
                                target="<?php echo esc_attr($button['target'] ?: '_self'); ?>"
                                class="btn hero-btn"
                                style="
                                background-color: <?php echo esc_attr($button_color); ?>; 
                                border-color: <?php echo esc_attr($button_color); ?>; 
                                color: #ffffff;">
                                <?php echo esc_html($button['title']); ?>
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php
                $slide_index++;
            endwhile; ?>
        </div>

        <!-- Kontrolne strelice -->
        <?php if ($slide_count > 1) : ?>
            <button
                class="carousel-control-prev"
                type="button"
                data-bs-target="#heroCarousel"
                data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Prethodna</span>
            </button>
            <button
                class="carousel-control-next"
                type="button"
                data-bs-target="#heroCarousel"
                data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sledeća</span>
            </button>
        <?php endif; ?>
    </div>

<?php
    // Dodajte JS za dodatne animacije
    wp_enqueue_script('hero-carousel-script', get_template_directory_uri() . '/js/hero-carousel.js', array('jquery', 'bootstrap'), '1.0', true);
endif;
?>