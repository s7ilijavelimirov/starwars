<div class="carousel-container container-fluid p-0">
    <div id="starWarsCarousel" class="carousel slide carousel-fade" data-bs-ride="carousel">

        <!-- PAGINATION (dots) -->
        <div class="carousel-indicators custom-indicators">
            <?php
            if (have_rows('slider')) :
                $i = 0;
                while (have_rows('slider')) : the_row(); ?>
                    <button type="button" data-bs-target="#starWarsCarousel" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>" aria-label="Slide <?php echo $i + 1; ?>"></button>
            <?php $i++;
                endwhile;
            endif;
            ?>
        </div>

        <!-- SLIDES -->
        <div class="carousel-inner">
            <?php
            if (have_rows('slider')) :
                $i = 0;
                while (have_rows('slider')) : the_row();
                    $image = get_sub_field('image');
                    $boja_head_naslova = get_sub_field('boja_head_naslova');
                    $boja_small_text = get_sub_field('boja_small_text');
                    $boja_buttona = get_sub_field('boja_buttona');
                    $head_title = get_sub_field('head_text', false);
                    $small_title = get_sub_field('small_text', false);
                    $link = get_sub_field('link');
                    $active_class = $i === 0 ? 'active' : '';
            ?>
                    <div class="carousel-item <?php echo $active_class; ?>">
                        <img src="<?php echo esc_url($image); ?>" class="d-block w-100 carousel-img" alt="Slider Image" loading="lazy">
                        <div class="carousel-caption d-flex align-items-center">
                            <div class="container">
                                <div class="row">
                                    <div class="col-md-8">
                                        <div class="slide-content">
                                            <?php if ($head_title) : ?>
                                                <h1 class="slide-head" style="<?php echo $boja_head_naslova ? 'color:' . $boja_head_naslova : ''; ?>">
                                                    <?php echo $head_title; ?>
                                                </h1>
                                            <?php endif; ?>
                                            <?php if ($small_title) : ?>
                                                <h4 class="slide-sub" style="<?php echo $boja_small_text ? 'color:' . $boja_small_text : ''; ?>">
                                                    <?php echo $small_title; ?>
                                                </h4>
                                            <?php endif; ?>
                                            <?php if ($link) : ?>
                                                <a href="<?php echo esc_url($link['url']); ?>" class="btn slide-btn" style="<?php echo $boja_buttona ? 'color:' . $boja_buttona . '; border-color:' . $boja_buttona : ''; ?>">
                                                    <?php echo esc_html($link['title']); ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
            <?php $i++;
                endwhile;
                wp_reset_postdata();
            endif; ?>
        </div>

        <!-- Bootstrap strelice -->
        <button class="carousel-control-prev" type="button" data-bs-target="#starWarsCarousel" data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>

        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#starWarsCarousel" data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>

        </button>
    </div>
</div>