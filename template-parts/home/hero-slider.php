<?php

/**
 * Optimizovani Hero Slider template part
 * 
 * @package s7design
 * @version 3.2.0
 */

// Provera da li postoji hero_slider ACF polje
if (have_rows('hero_slider')) :
    // Brojanje slajdova za bolje upravljanje kontrolama
    $slide_count = count(get_field('hero_slider'));
?>
    <section id="heroSlider" class="hero-slider carousel slide carousel-fade" data-bs-ride="carousel" data-bs-interval="6000">

        <?php if ($slide_count > 1) : ?>
            <!-- Indikatori -->
            <div class="carousel-indicators">
                <?php for ($i = 0; $i < $slide_count; $i++) : ?>
                    <button
                        type="button"
                        data-bs-target="#heroSlider"
                        data-bs-slide-to="<?php echo $i; ?>"
                        class="<?php echo $i === 0 ? 'active' : ''; ?>"
                        aria-label="Slide <?php echo $i + 1; ?>"
                        aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>">
                    </button>
                <?php endfor; ?>
            </div>
        <?php endif; ?>

        <!-- Slide wrapper -->
        <div class="carousel-inner">
            <?php
            $slide_index = 0;
            while (have_rows('hero_slider')) : the_row();
                // Dobavljanje i validacija slika
                $desktop_image = get_sub_field('desktop_image');
                $mobile_image = get_sub_field('mobile_image');

                // Ako nema desktop slike, preskačemo ovaj slajd
                if (empty($desktop_image)) continue;

                // Dobavljanje ostalih polja
                $title = get_sub_field('title');
                $subtitle = get_sub_field('subtitle');
                $title_color = get_sub_field('title_color') ?: '#ffffff';
                $subtitle_color = get_sub_field('subtitle_color') ?: '#ffffff';
                $button = get_sub_field('button');
                $button_color = get_sub_field('button_color') ?: '#007bff';
                $content_alignment = get_sub_field('content_alignment') ?: 'center';
                $animation_type = get_sub_field('animation_type') ?: 'fade-enter';

                // Formatiranje alt teksta za bolji SEO
                $slide_alt = !empty($desktop_image['alt']) ? $desktop_image['alt'] : (!empty($title) ? wp_strip_all_tags($title) : 'Slide ' . ($slide_index + 1));
            ?>
                <div class="carousel-item <?php echo $slide_index === 0 ? 'active' : ''; ?>" data-animation="<?php echo esc_attr($animation_type); ?>">
                    <!-- Optimizovane responsive slike -->
                    <picture>
                        <?php if ($mobile_image && !empty($mobile_image['url'])) : ?>
                            <source media="(max-width: 768px)" srcset="<?php echo esc_url($mobile_image['url']); ?>">
                        <?php endif; ?>
                        <img
                            src="<?php echo esc_url($desktop_image['url']); ?>"
                            alt="<?php echo esc_attr($slide_alt); ?>"
                            class="d-block w-100 carousel-img"
                            loading="<?php echo $slide_index === 0 ? 'eager' : 'lazy'; ?>"
                            <?php echo $slide_index === 0 ? 'fetchpriority="high"' : ''; ?>
                            width="<?php echo isset($desktop_image['width']) ? esc_attr($desktop_image['width']) : '1920'; ?>"
                            height="<?php echo isset($desktop_image['height']) ? esc_attr($desktop_image['height']) : '750'; ?>">
                    </picture>

                    <!-- Overlay sa prilagođenim efektom -->
                    <div class="carousel-overlay"></div>

                    <!-- Sadržaj slajda -->
                    <?php if ($title || $subtitle || ($button && !empty($button['url']) && !empty($button['title']))) : ?>
                        <div class="carousel-caption align-<?php echo esc_attr($content_alignment); ?>">
                            <div class="container">
                                <div class="caption-content">
                                    <?php if ($title) : ?>
                                        <h2 class="hero-title"
                                            style="color: <?php echo esc_attr($title_color); ?>;">
                                            <?php echo wp_kses_post($title); ?>
                                        </h2>
                                    <?php endif; ?>

                                    <?php if ($subtitle) : ?>
                                        <div class="hero-subtitle"
                                            style="color: <?php echo esc_attr($subtitle_color); ?>;">
                                            <?php echo wp_kses_post($subtitle); ?>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($button && !empty($button['url']) && !empty($button['title'])) : ?>
                                        <div class="hero-button-wrapper">
                                            <a
                                                href="<?php echo esc_url($button['url']); ?>"
                                                target="<?php echo esc_attr($button['target'] ?: '_self'); ?>"
                                                class="btn hero-btn"
                                                style="background-color: <?php echo esc_attr($button_color); ?>; border-color: <?php echo esc_attr($button_color); ?>;">
                                                <span><?php echo esc_html($button['title']); ?></span>
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            <?php
                $slide_index++;
            endwhile;
            ?>
        </div>

        <?php if ($slide_count > 1) : ?>
            <!-- Kontrolne strelice -->
            <button class="carousel-control-prev" type="button" data-bs-target="#heroSlider" data-bs-slide="prev" aria-label="Prethodni slajd">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Prethodni slajd</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#heroSlider" data-bs-slide="next" aria-label="Sledeći slajd">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Sledeći slajd</span>
            </button>
        <?php endif; ?>
    </section>

<?php



endif;
