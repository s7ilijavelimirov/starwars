<!-- SLIDER -->
<section class="slider">
    <?php
    $args = array(
        'post_type' => 'slider',
        'posts_per_page' => 1,
    );
    $loop = new WP_Query($args);
    if ($loop->have_posts()) :
        while ($loop->have_posts()) : $loop->the_post();
            if (have_rows('slider')) :
                $i = 0;
    ?>
                <div id="carouselExampleFade" class="carousel slide carousel-fade" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        <?php
                        while (have_rows('slider', $loop->ID)) : the_row(); { {
                                    $image = get_sub_field('image');
                                    $title = get_sub_field('header_title');
                                    $text = get_sub_field('title');
                        ?>
                                    <div class="carousel-item <?php if ($i == 0) : echo 'active';
                                                                endif; ?>">
                                        <div class="row">
                                            <img src="<?php echo $image; ?>" class="d-block w-100 image-fluid" alt="Slider Banner">
                                            <div class="p-0 position-absolute description d-flex text-center justify-content-center h-100 w-100 flex-column align-items-center align-middle flex-wrap">
                                                <h1 class="w-50"><?php echo $title; ?></h1>
                                                <p class="text-sli w-50"><?php echo $text; ?></p>
                                            </div>
                                        </div>
                                    </div>
                        <?php
                                }
                            }
                            $i++;
                        endwhile;
                        ?>
                    </div>
                </div>
    <?php
            endif;
        endwhile;
    endif;
    ?>
    </div>
    <!-- ENDSLIDER -->