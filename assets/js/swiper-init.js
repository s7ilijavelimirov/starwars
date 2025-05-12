/**
 * Swiper.js Inicijalizacija
 * 
 * @package s7design
 * @version 1.0.0
 */

(function ($) {
    'use strict';

    // Čekaj da se DOM potpuno učita
    $(document).ready(function () {
        initProductSwipers();
        initBlogSwiper();

        // Funkcija za praćenje klikova
        $(document).on('click', '.product-card', function () {
            trackProductClick(this);
        });
    });

    /**
     * Inicijalizacija svih product swipers
     */
    function initProductSwipers() {
        // Za svaki Swiper kontejner
        $('.swiper-container:not(#blog-swiper)').each(function (index) {
            const $container = $(this);
            const slidesPerView = parseInt($container.data('slides') || 5, 10);

            // Lazy loading opcija - učitava samo kada je vidljiv
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        // Kada je Swiper vidljiv, inicijalizuj ga
                        initSingleProductSwiper($container, slidesPerView);
                        observer.unobserve(entry.target);
                    }
                });
            }, {
                rootMargin: '50px',
                threshold: 0.1
            });

            // Posmatraj ovaj Swiper kontejner
            observer.observe(this);
        });
    }

    /**
     * Inicijalizacija pojedinačnog product Swiper-a
     */
    function initSingleProductSwiper($container, slidesPerView) {
        const containerId = $container.attr('id');
        const showPagination = $container.hasClass('with-pagination');

        // Konfiguracija za Swiper
        const swiperConfig = {
            // Osnovne opcije
            slidesPerView: slidesPerView,
            spaceBetween: 20,
            watchSlidesProgress: true,
            grabCursor: false,

            // Lazy loading slika
            lazy: {
                loadPrevNext: true,
                loadPrevNextAmount: 2
            },

            // Optimizacija - učitavanje slika samo kada su vidljive
            preloadImages: false,
            watchSlidesVisibility: true,

            // Navigacija
            navigation: {
                nextEl: `#${containerId} .swiper-button-next`,
                prevEl: `#${containerId} .swiper-button-prev`,
            },

            // Responsive breakpoints
            breakpoints: {
                // Mobilni (ispod 576px)
                320: {
                    slidesPerView: 1,
                    spaceBetween: 10
                },
                // Mobilni (preko 576px)
                576: {
                    slidesPerView: 2,
                    spaceBetween: 15
                },
                // Tableti
                768: {
                    slidesPerView: 3,
                    spaceBetween: 15
                },
                // Mali desktop
                992: {
                    slidesPerView: 4,
                    spaceBetween: 20
                },
                // Desktop
                1200: {
                    slidesPerView: slidesPerView,
                    spaceBetween: 20
                }
            }
        };

        // Dodaj paginaciju ako je potrebna
        if (showPagination) {
            swiperConfig.pagination = {
                el: `#${containerId} .swiper-pagination`,
                clickable: true
            };
        }

        // Kreiraj Swiper instancu
        new Swiper(`#${containerId}`, swiperConfig);
    }

    /**
     * Inicijalizacija blog Swiper-a
     */
    function initBlogSwiper() {
        const $blogSwiper = $('#blog-swiper');

        if ($blogSwiper.length === 0) return;

        // Lazy loading opcija - učitava samo kada je vidljiv
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    // Kada je blog Swiper vidljiv, inicijalizuj ga
                    new Swiper('#blog-swiper', {
                        // Osnovne opcije
                        slidesPerView: 3,
                        spaceBetween: 30,
                        watchSlidesVisibility: true,

                        // Lazy loading background slika
                        lazy: {
                            loadPrevNext: true,
                            loadPrevNextAmount: 3
                        },

                        // Navigacija i paginacija
                        navigation: {
                            nextEl: '#blog-swiper .swiper-button-next',
                            prevEl: '#blog-swiper .swiper-button-prev',
                        },
                        pagination: {
                            el: '#blog-swiper .swiper-pagination',
                            clickable: true
                        },

                        // Responsive breakpoints
                        breakpoints: {
                            // Mobilni
                            320: {
                                slidesPerView: 1,
                                spaceBetween: 20
                            },
                            // Tableti
                            768: {
                                slidesPerView: 2,
                                spaceBetween: 20
                            },
                            // Desktop
                            992: {
                                slidesPerView: 3,
                                spaceBetween: 30
                            }
                        }
                    });

                    observer.unobserve(entry.target);
                }
            });
        }, {
            rootMargin: '50px',
            threshold: 0.1
        });

        // Posmatraj blog Swiper
        observer.observe($blogSwiper[0]);
    }

    /**
     * Funkcija za praćenje klikova na proizvode
     */
    function trackProductClick(element) {
        if (!element || !element.dataset) return;

        const productData = {
            id: element.dataset.productId || '',
            name: element.dataset.productName || '',
            price: element.dataset.productPrice || '',
            position: element.dataset.position || '',
            category: element.dataset.productCategory || '',
            sku: element.dataset.productSku || ''
        };

        // Za debugging (ako je uključen debug mode)
        if (window.location.search.includes('debug=true')) {
            console.log('Product clicked:', productData);
        }

        // Ovde možeš dodati kod za Google Analytics, Facebook Pixel ili drugi tracking sistem
        // Primer za GA4:
        // if (typeof gtag === 'function') {
        //     gtag('event', 'select_item', {
        //         items: [{
        //             item_id: productData.id,
        //             item_name: productData.name,
        //             item_category: productData.category,
        //             price: productData.price,
        //             index: productData.position
        //         }]
        //     });
        // }
    }
})(jQuery);