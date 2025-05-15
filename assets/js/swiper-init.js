/**
 * Optimizovana inicijalizacija Swiper-a
 * Ispravljena verzija koja radi sa Star Wars temom
 * 
 * @package s7design
 * @version 3.2.1
 */

document.addEventListener('DOMContentLoaded', function () {
    // Provera da li je Swiper dostupan
    if (typeof Swiper === 'undefined') {
        console.error('Swiper biblioteka nije dostupna. Proverite da li je pravilno učitana.');
        return;
    }

    // Inicijalizacija svih product karusela
    initProductCarousels();

    /**
     * Glavna funkcija za inicijalizaciju svih karusela
     */
    function initProductCarousels() {
        // Uzmi SVE karusel kontejnere - prošireni selektor
        const carouselContainers = document.querySelectorAll('.swiper-container');

        if (carouselContainers.length === 0) {
            return;
        }

        // Sakrij sve kontejnere dok se ne inicijalizuju
        carouselContainers.forEach(container => {
            // Sakrij karusel dok se učitava
            container.style.visibility = 'hidden';
            container.style.opacity = '0';
            container.style.transition = 'opacity 0.5s ease';
        });

        // Inicijalizuj svaki karusel
        carouselContainers.forEach((container) => {
            // Proveri DOM strukturu
            const wrapper = container.querySelector('.swiper-wrapper');
            const slides = container.querySelectorAll('.swiper-slide');
            const pagination = container.querySelector('.swiper-pagination');

            // Proveri da li je karusel već inicijalizovan
            if (container.classList.contains('swiper-initialized')) {
                container.style.visibility = 'visible';
                container.style.opacity = '1';
                return;
            }

            // Pretvaramo sve lazy slike u obične slike pre inicijalizacije
            preloadImages(container);

            // Čitamo broj slajdova koji treba prikazati iz data atributa
            const slidesPerView = parseInt(container.getAttribute('data-slides') || 5, 10);

            try {
                // Inicijalizujemo Swiper sa naprednim opcijama
                const swiper = new Swiper(`#${container.id}`, {
                    // Osnovne opcije
                    slidesPerView: 1,
                    spaceBetween: 5,
                    speed: 600,
                    watchOverflow: true,
                    observer: true,
                    observeParents: true,
                    preloadImages: true,

                    // Optimizacija performansi
                    watchSlidesProgress: true,
                    updateOnWindowResize: true,

                    // Responzivne opcije
                    breakpoints: {
                        320: { slidesPerView: 2, spaceBetween: 0 },
                        576: { slidesPerView: 2, spaceBetween: 5 },
                        768: { slidesPerView: 3, spaceBetween: 5 },
                        992: { slidesPerView: 4, spaceBetween: 10 },
                        1200: { slidesPerView: slidesPerView, spaceBetween: 10 }
                    },

                    // Navigacija - tražimo dugmiće po ID-u
                    navigation: {
                        nextEl: `#next-${container.id}`,
                        prevEl: `#prev-${container.id}`,
                        disabledClass: 'swiper-button-disabled',
                        hiddenClass: 'swiper-button-hidden'
                    },

                    // Paginacija (proveravamo da li kontejner ima paginaciju)
                    pagination: pagination ? {
                        el: `#${container.id} .swiper-pagination`,
                        clickable: true,
                        dynamicBullets: true,
                        bulletActiveClass: 'swiper-pagination-bullet-active'
                    } : false,

                    // Događaji
                    on: {
                        init: function () {
                            // Ukloni placeholder efekat ako postoji
                            container.classList.remove('swiper-placeholder');
                            // Dodaj klasu za inicijalizovan swiper
                            container.classList.add('swiper-fully-loaded');

                            // Inicijalno ažuriranje navigacionih dugmića
                            updateNavigationState(this, container.id);
                        },
                        imagesReady: function () {
                            // Prikaži karusel kada su sve slike učitane
                            setTimeout(function () {
                                container.style.visibility = 'visible';
                                container.style.opacity = '1';
                            }, 100);
                        },
                        slideChange: function () {
                            // Ažuriranje navigacionih dugmića
                            updateNavigationState(this, container.id);
                        }
                    }
                });

                // Dodatni event listeneri za dugmiće
                setupExtraNavigation(swiper, container.id);

                // Backup timer - ako se imagesReady ne aktivira iz nekog razloga
                setTimeout(function () {
                    if (container.style.visibility !== 'visible') {
                        container.style.visibility = 'visible';
                        container.style.opacity = '1';
                    }
                }, 2000);

            } catch (error) {
                // Prikaži karusel u slučaju greške da ne bi ostao sakriven
                container.style.visibility = 'visible';
                container.style.opacity = '1';
            }
        });
    }

    /**
     * Preloaduje sve lazy slike u kontejneru
     * Zamenjuje data-src atribut sa src za pouzdaniji prikaz
     */
    function preloadImages(container) {
        const lazyImages = container.querySelectorAll('img.swiper-lazy, img[data-src]');

        if (lazyImages.length > 0) {
            lazyImages.forEach((img) => {
                const dataSrc = img.getAttribute('data-src');
                if (dataSrc) {
                    // Direktno postavi src atribut umesto da koristimo lazy loading
                    img.setAttribute('src', dataSrc);
                    img.removeAttribute('data-src');
                    img.classList.remove('swiper-lazy');

                    // Ukloni preloader ako postoji
                    const preloader = img.nextElementSibling;
                    if (preloader && preloader.classList.contains('swiper-lazy-preloader')) {
                        preloader.parentNode.removeChild(preloader);
                    }
                }
            });
        }
    }

    /**
     * Dodaje dodatne event listenere za navigacione dugmiće
     * Ovo je fallback ako standardna Swiper navigacija ne radi
     */
    function setupExtraNavigation(swiper, sectionId) {
        const prevBtn = document.getElementById(`prev-${sectionId}`);
        const nextBtn = document.getElementById(`next-${sectionId}`);

        if (prevBtn) {
            // Direktan event listener za prev dugme
            prevBtn.addEventListener('click', function (e) {
                e.preventDefault();
                swiper.slidePrev();
                updateNavigationState(swiper, sectionId);
            });

            // Visual styling
            prevBtn.addEventListener('mouseenter', function () {
                this.classList.add('nav-hover');
            });
            prevBtn.addEventListener('mouseleave', function () {
                this.classList.remove('nav-hover');
            });
        }

        if (nextBtn) {
            // Direktan event listener za next dugme
            nextBtn.addEventListener('click', function (e) {
                e.preventDefault();
                swiper.slideNext();
                updateNavigationState(swiper, sectionId);
            });

            // Visual styling
            nextBtn.addEventListener('mouseenter', function () {
                this.classList.add('nav-hover');
            });
            nextBtn.addEventListener('mouseleave', function () {
                this.classList.remove('nav-hover');
            });
        }
    }

    /**
     * Ažurira stanje navigacionih dugmića
     */
    function updateNavigationState(swiper, sectionId) {
        const prevBtn = document.getElementById(`prev-${sectionId}`);
        const nextBtn = document.getElementById(`next-${sectionId}`);

        if (prevBtn) {
            if (swiper.isBeginning) {
                prevBtn.classList.add('swiper-button-disabled');
            } else {
                prevBtn.classList.remove('swiper-button-disabled');
            }
        }

        if (nextBtn) {
            if (swiper.isEnd) {
                nextBtn.classList.add('swiper-button-disabled');
            } else {
                nextBtn.classList.remove('swiper-button-disabled');
            }
        }
    }

    // Dodatna optimizacija - ažuriranje svih swiper-a nakon učitavanja stranice
    window.addEventListener('load', function () {
        setTimeout(function () {
            document.querySelectorAll('.swiper-container').forEach(function (container) {
                if (container.swiper) {
                    container.swiper.update();

                    // Osiguraj da je karusel prikazan
                    if (container.style.visibility !== 'visible') {
                        container.style.visibility = 'visible';
                        container.style.opacity = '1';
                    }
                }
            });
        }, 500);
    });
});
/**
 * Blog Swiper inicijalizacija - Osnovna verzija bez centriranja
 * Optimizovana za mobilne uređaje i da ne izlazi iz kontejnera
 */

document.addEventListener('DOMContentLoaded', function () {
    initBasicBlogSwiper();

    /**
     * Osnovna inicijalizacija blog Swiper-a 
     * Jednostavna verzija koja kliza po 1 karticu
     */
    function initBasicBlogSwiper() {
        const blogSwiper = document.getElementById('blog-swiper');

        if (!blogSwiper) return;

        try {
            // Inicijalizacija Swiper-a za blog - osnovne opcije
            const swiper = new Swiper('#blog-swiper', {
                // Osnovne opcije - po jedna kartica
                slidesPerView: 3,
                slidesPerGroup: 1, // Pomera se po jedna kartica
                spaceBetween: 20,
                grabCursor: true,
                speed: 500,

                // Ključno: isključujemo centriranje i ostale napredne opcije
                centeredSlides: false,
                loop: false,

                // Responzivne opcije
                breakpoints: {
                    320: { slidesPerView: 1, spaceBetween: 15 },
                    576: { slidesPerView: 1, spaceBetween: 15 },
                    768: { slidesPerView: 2, spaceBetween: 20 },
                    992: { slidesPerView: 3, spaceBetween: 20 },
                    1200: { slidesPerView: 3, spaceBetween: 20 }
                },

                // Navigacija
                navigation: {
                    nextEl: '#next-blog-swiper',
                    prevEl: '#prev-blog-swiper',
                    disabledClass: 'swiper-button-disabled'
                },

                // Paginacija
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true
                },

                // Jednostavan efekat bez komplikovanih animacija
                effect: 'slide'
            });

            // Dodavanje event listener-a za resize
            window.addEventListener('resize', function () {
                swiper.update();
            }, { passive: true });

        } catch (error) {
            console.error('Blog Swiper inicijalizacija nije uspela:', error);
        }
    }
});