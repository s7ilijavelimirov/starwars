/**
 * Optimizovani Swiper sa IntersectionObserver i Native Lazy Loading
 * 
 * @package s7design
 * @version 2.4.0
 */

document.addEventListener('DOMContentLoaded', function () {
    // Provera da li je Swiper dostupan
    if (typeof Swiper !== 'function') {
        console.error('Swiper biblioteka nije učitana!');
        return;
    }

    // 1. IntersectionObserver za inicijalizaciju swiper-a samo kada je vidljiv
    if ('IntersectionObserver' in window) {
        const swiperObserver = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    const container = entry.target;

                    // Inicijalizuj Swiper samo ako već nije inicijalizovan
                    if (!container.classList.contains('swiper-initialized')) {
                        initSwiperInstance(container);
                    } else if (container.swiper) {
                        // Ako je već inicijalizovan, samo ažuriraj
                        container.swiper.update();
                    }

                    // Posmatramo samo jednom - nakon inicijalizacije prestajemo da posmatramo
                    swiperObserver.unobserve(container);
                }
            });
        }, {
            // Margina od 200px znači da će se swiper inicijalizovati 
            // kada je udaljen 200px od vidljivog područja
            rootMargin: '200px 0px',
            threshold: 0
        });

        // Posmatraj sve swiper containere
        document.querySelectorAll('.swiper-container').forEach(function (container) {
            swiperObserver.observe(container);

            // Postavimo inicijalnu klasu za vizuelni placeholder
            container.classList.add('swiper-placeholder');
        });
    } else {
        // Fallback za browsere koji ne podržavaju IntersectionObserver
        document.querySelectorAll('.swiper-container').forEach(function (container) {
            initSwiperInstance(container);
        });
    }

    /**
     * Inicijalizuje Swiper instancu sa optimizovanim opcijama
     */
    function initSwiperInstance(container) {
        if (container.classList.contains('swiper-initialized')) {
            return;
        }

        const id = container.id;
        console.log('Inicijalizujem swiper:', id);

        // Pronadji elemente
        const pagination = container.querySelector('.swiper-pagination');
        const slidesPerView = parseInt(container.getAttribute('data-slides') || 5, 10);

        try {
            // Inicijalizujemo sa naprednim opcijama
            const swiper = new Swiper('#' + id, {
                // Osnovne opcije
                slidesPerView: 1,
                spaceBetween: 20,
                speed: 600,

                // Optimizovani lazy loading
                preloadImages: false,
                lazy: {
                    loadPrevNext: true,
                    loadPrevNextAmount: 2,
                    loadOnTransitionStart: true,
                    elementClass: 'swiper-lazy', // CSS klasa koja će se koristiti za lazy elemente
                    loadingClass: 'swiper-lazy-loading', // CSS klasa tokom učitavanja
                    loadedClass: 'swiper-lazy-loaded', // CSS klasa nakon učitavanja
                    preloaderClass: 'swiper-lazy-preloader' // CSS klasa za preloader
                },

                // Optimizacija performansi
                watchSlidesProgress: true,
                updateOnWindowResize: true,

                // Responzivne opcije
                breakpoints: {
                    320: { slidesPerView: 1, spaceBetween: 10 },
                    576: { slidesPerView: 2, spaceBetween: 15 },
                    768: { slidesPerView: 3, spaceBetween: 15 },
                    992: { slidesPerView: 4, spaceBetween: 20 },
                    1200: { slidesPerView: slidesPerView, spaceBetween: 20 }
                },

                // Paginacija
                pagination: pagination ? {
                    el: pagination,
                    clickable: true,
                    dynamicBullets: true,
                    bulletActiveClass: 'swiper-pagination-bullet-active',
                    renderBullet: function (index, className) {
                        return '<span class="' + className + '"></span>';
                    }
                } : false,

                // Događaji
                on: {
                    init: function () {
                        // Ukloni placeholder efekat
                        container.classList.remove('swiper-placeholder');
                        console.log('Swiper inicijalizovan:', id);

                        // Inicijalno ažuriranje navigacionih dugmića
                        updateNavigationState(this, id);
                    },
                    lazyImageReady: function (slideEl, imageEl) {
                        // Kada je slika učitana
                        imageEl.classList.add('swiper-lazy-loaded');
                    },
                    slideChange: function () {
                        // Ažuriranje navigacionih dugmića
                        updateNavigationState(this, id);
                    },
                    resize: function () {
                        // Ažuriranje na resize
                        this.update();
                    }
                }
            });

            // Sačuvamo referencu
            container.swiper = swiper;

            // Podešavanje navigacionih dugmića
            setupNavigationButtons(container, swiper, id);

        } catch (error) {
            console.error('Greška pri inicijalizaciji swiper-a:', id, error);
        }
    }

    /**
     * Podešava navigacione dugmiće za Swiper
     */
    function setupNavigationButtons(container, swiper, id) {
        const prevBtn = document.getElementById('prev-' + id);
        const nextBtn = document.getElementById('next-' + id);

        if (prevBtn) {
            // Prethodni slide
            prevBtn.addEventListener('click', function () {
                swiper.slidePrev();
            });

            // Stilovi na hover
            prevBtn.addEventListener('mouseenter', function () {
                if (!this.classList.contains('disabled')) {
                    this.classList.add('nav-hover');
                }
            });

            prevBtn.addEventListener('mouseleave', function () {
                this.classList.remove('nav-hover');
            });
        }

        if (nextBtn) {
            // Sledeći slide
            nextBtn.addEventListener('click', function () {
                swiper.slideNext();
            });

            // Stilovi na hover
            nextBtn.addEventListener('mouseenter', function () {
                if (!this.classList.contains('disabled')) {
                    this.classList.add('nav-hover');
                }
            });

            nextBtn.addEventListener('mouseleave', function () {
                this.classList.remove('nav-hover');
            });
        }
    }

    /**
     * Ažurira stanje navigacionih dugmića
     */
    function updateNavigationState(swiper, id) {
        const prevBtn = document.getElementById('prev-' + id);
        const nextBtn = document.getElementById('next-' + id);

        if (prevBtn) {
            prevBtn.classList.toggle('disabled', swiper.isBeginning);
        }

        if (nextBtn) {
            nextBtn.classList.toggle('disabled', swiper.isEnd);
        }
    }

    // Dodatna optimizacija - ažuriranje svih swiper-a nakon učitavanja stranice
    window.addEventListener('load', function () {
        // Mala pauza da se prvo učitaju slike
        setTimeout(function () {
            document.querySelectorAll('.swiper-container').forEach(function (container) {
                if (container.swiper) {
                    container.swiper.update();
                }
            });
        }, 500);
    });
});