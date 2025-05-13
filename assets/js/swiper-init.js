/**
 * Jednostavna ali robusna Swiper.js inicijalizacija
 * 
 * @package s7design
 * @version 1.0.0
 */

(function ($) {
    'use strict';

    $(document).ready(function () {
        // Reinicijalizuj sve Swiper karusele
        $('.swiper-container').each(function () {
            const id = $(this).attr('id');
            const slides = parseInt($(this).data('slides'), 10) || 5;
            const hasPages = $(this).hasClass('with-pagination');

            // Uništi postojeći swiper ako postoji
            if (this.swiper) {
                this.swiper.destroy(true, true);
            }

            // Kreiraj novi swiper sa minimalnim opcijama
            new Swiper(`#${id}`, {
                slidesPerView: slides,
                spaceBetween: 15,
                navigation: {
                    nextEl: `#${id} .swiper-button-next`,
                    prevEl: `#${id} .swiper-button-prev`,
                },
                pagination: hasPages ? {
                    el: `#${id} .swiper-pagination`,
                    clickable: true
                } : false,
                breakpoints: {
                    320: { slidesPerView: 1, spaceBetween: 10 },
                    576: { slidesPerView: 2, spaceBetween: 15 },
                    768: { slidesPerView: 3, spaceBetween: 15 },
                    992: { slidesPerView: 4, spaceBetween: 20 },
                    1200: { slidesPerView: slides, spaceBetween: 20 }
                }
            });

            console.log('Swiper inicijalizovan:', id);
        });
    });
})(jQuery);