/**
 * Poboljšana inicijalizacija Swiper-a sa boljim lazy loading-om
 * 
 * @package s7design
 * @version 3.0.0
 */

document.addEventListener('DOMContentLoaded', function () {
    console.log('DOMContentLoaded event triggered - pokretanje inicijalizacije Swipera');
    
    // Provera da li je Swiper dostupan
    if (typeof Swiper === 'undefined') {
        console.error('GREŠKA: Swiper biblioteka nije učitana. Proveriti da li je script pravilno učitan.');
        return;
    } else {
        console.log('✓ Swiper biblioteka je dostupna (verzija: ' + (Swiper.version || 'nepoznata') + ')');
    }
    
    // Inicijalizacija svih product karusela
    initProductCarousels();

    /**
     * Glavna funkcija za inicijalizaciju svih karusela
     */
    function initProductCarousels() {
        // Uzmi sve karusel kontejnere
        const carouselContainers = document.querySelectorAll('.swiper-container[id^="product-section-"]');
        
        console.log('Tražim karusele sa ID-om product-section-*');
        console.log('Pronađeno karusela: ' + carouselContainers.length);
        
        if (carouselContainers.length === 0) {
            console.warn('Nije pronađen nijedan karusel na stranici. Proverite HTML strukturu i ID-ove.');
            return;
        }

        // Inicijalizuj svaki karusel
        carouselContainers.forEach((container, index) => {
            console.log('-----------------------------------');
            console.log(`Inicijalizacija karusela #${index + 1}: ${container.id}`);
            
            // Proveri DOM strukturu
            const wrapper = container.querySelector('.swiper-wrapper');
            const slides = container.querySelectorAll('.swiper-slide');
            const pagination = container.querySelector('.swiper-pagination');
            
            console.log(`DOM struktura: wrapper=${wrapper ? 'OK' : 'NIJE PRONAĐEN!'}, slides=${slides.length}, pagination=${pagination ? 'OK' : 'NIJE PRONAĐEN!'}`);
            
            // Proveri da li je karusel već inicijalizovan
            if (container.classList.contains('swiper-initialized')) {
                console.log(`Karusel ${container.id} je već inicijalizovan - preskačem.`);
                return;
            }

            // Pretvaramo sve lazy slike u obične slike pre inicijalizacije
            // Ovo rešava probleme sa lazy loading-om
            preloadImages(container);

            // Čitamo broj slajdova koji treba prikazati iz data atributa
            const slidesPerView = parseInt(container.getAttribute('data-slides') || 5, 10);
            console.log(`Broj slajdova za prikaz: ${slidesPerView} (iz data-slides atributa)`);
            
            try {
                console.log(`Pokušavam inicijalizaciju Swiper instance za #${container.id}`);
                
                // Inicijalizujemo Swiper sa naprednim opcijama
                const swiper = new Swiper(`#${container.id}`, {
                    // Osnovne opcije
                    slidesPerView: 1,
                    spaceBetween: 20,
                    speed: 600,
                    watchOverflow: true,
                    observer: true,
                    observeParents: true,
                    
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
                            console.log(`✓ Swiper instanca za #${container.id} je uspešno inicijalizovana`);
                            console.log(`  - Broj slidova: ${this.slides.length}`);
                            console.log(`  - Trenutni slide: ${this.activeIndex}`);
                            console.log(`  - isBeginning: ${this.isBeginning}, isEnd: ${this.isEnd}`);
                            
                            // Provera navigacije
                            const nextBtn = document.getElementById(`next-${container.id}`);
                            const prevBtn = document.getElementById(`prev-${container.id}`);
                            console.log(`  - Next dugme: ${nextBtn ? 'pronađeno' : 'NIJE PRONAĐENO'}`);
                            console.log(`  - Prev dugme: ${prevBtn ? 'pronađeno' : 'NIJE PRONAĐENO'}`);
                            
                            // Ukloni placeholder efekat ako postoji
                            container.classList.remove('swiper-placeholder');
                            // Dodaj klasu za inicijalizovan swiper
                            container.classList.add('swiper-fully-loaded');
                            
                            // Inicijalno ažuriranje navigacionih dugmića
                            updateNavigationState(this, container.id);
                        },
                        slideChange: function () {
                            console.log(`#${container.id} slide promenjen na: ${this.activeIndex}`);
                            // Ažuriranje navigacionih dugmića
                            updateNavigationState(this, container.id);
                        }
                    }
                });

                // Direktna provera navigacije
                console.log('Direktna provera navigacionih dugmića:');
                const nextBtnEl = document.getElementById(`next-${container.id}`);
                const prevBtnEl = document.getElementById(`prev-${container.id}`);
                if (nextBtnEl) {
                    console.log(`Next btn (#next-${container.id}): ${nextBtnEl.outerHTML.substring(0, 50)}...`);
                } else {
                    console.error(`Next btn (#next-${container.id}) NIJE PRONAĐEN!`);
                }
                if (prevBtnEl) {
                    console.log(`Prev btn (#prev-${container.id}): ${prevBtnEl.outerHTML.substring(0, 50)}...`);
                } else {
                    console.error(`Prev btn (#prev-${container.id}) NIJE PRONAĐEN!`);
                }

                // Dodatni event listeneri za dugmiće
                setupExtraNavigation(swiper, container.id);

            } catch (error) {
                console.error(`❌ GREŠKA pri inicijalizaciji karusela ${container.id}:`, error);
                console.error('Detalji greške:', error.stack);
            }
        });
    }

    /**
     * Preloaduje sve lazy slike u kontejneru
     * Zamenjuje data-src atribut sa src za pouzdaniji prikaz
     */
    function preloadImages(container) {
        const lazyImages = container.querySelectorAll('img.swiper-lazy');
        
        if (lazyImages.length > 0) {
            console.log(`Preloadujem ${lazyImages.length} lazy slika u karuselu ${container.id}`);
            
            lazyImages.forEach((img, index) => {
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
        console.log(`Postavljanje direktnih event listener-a za dugmiće karusela ${sectionId}`);
        
        const prevBtn = document.getElementById(`prev-${sectionId}`);
        const nextBtn = document.getElementById(`next-${sectionId}`);

        if (prevBtn) {
            // Direktan event listener za prev dugme
            prevBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log(`Kliknuto prev dugme za ${sectionId} - pozivam slidePrev()`);
                swiper.slidePrev();
                updateNavigationState(swiper, sectionId);
            });
            
            // Visual styling
            prevBtn.addEventListener('mouseenter', function() {
                this.classList.add('nav-hover');
            });
            prevBtn.addEventListener('mouseleave', function() {
                this.classList.remove('nav-hover');
            });
            
            console.log(`✓ Postavljen dodatni click handler za prev dugme #prev-${sectionId}`);
        }

        if (nextBtn) {
            // Direktan event listener za next dugme
            nextBtn.addEventListener('click', function(e) {
                e.preventDefault();
                console.log(`Kliknuto next dugme za ${sectionId} - pozivam slideNext()`);
                swiper.slideNext();
                updateNavigationState(swiper, sectionId);
            });
            
            // Visual styling
            nextBtn.addEventListener('mouseenter', function() {
                this.classList.add('nav-hover');
            });
            nextBtn.addEventListener('mouseleave', function() {
                this.classList.remove('nav-hover');
            });
            
            console.log(`✓ Postavljen dodatni click handler za next dugme #next-${sectionId}`);
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
                console.log(`#${sectionId} je na početku - disable prev dugme`);
                prevBtn.classList.add('swiper-button-disabled');
            } else {
                console.log(`#${sectionId} nije na početku - enable prev dugme`);
                prevBtn.classList.remove('swiper-button-disabled');
            }
        }

        if (nextBtn) {
            if (swiper.isEnd) {
                console.log(`#${sectionId} je na kraju - disable next dugme`);
                nextBtn.classList.add('swiper-button-disabled');
            } else {
                console.log(`#${sectionId} nije na kraju - enable next dugme`);
                nextBtn.classList.remove('swiper-button-disabled');
            }
        }
    }

    // Dodatna optimizacija - ažuriranje svih swiper-a nakon učitavanja stranice
    window.addEventListener('load', function() {
        console.log('Window load event - ažuriranje svih Swiper instanci');
        setTimeout(function() {
            document.querySelectorAll('.swiper-container').forEach(function(container) {
                if (container.swiper) {
                    console.log(`Ažuriranje Swiper instance za #${container.id}`);
                    container.swiper.update();
                } else {
                    console.warn(`Swiper instanca nije pronađena za #${container.id}`);
                }
            });
        }, 500);
    });
});