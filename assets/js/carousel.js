/**
 * Star Wars Theme Carousel JavaScript
 * Optimizovani karusel za proizvode i vesti
 * 
 * @package s7design
 * @version 1.0.1
 */

(function () {
    'use strict';

    // Čekaj da se DOM potpuno učita
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCarousels);
    } else {
        // DOM je već učitan
        initCarousels();
    }

    // Glavna inicijalizaciona funkcija
    function initCarousels() {
        // Inicijalizuj sve karusele proizvoda
        try {
            initProductCarousels();
        } catch (error) {
            console.error('Greška pri inicijalizaciji product carousela:', error);
        }

        // Inicijalizuj blog slider
        try {
            initBlogSlider();
        } catch (error) {
            console.error('Greška pri inicijalizaciji blog slidera:', error);
        }
    }

    /**
     * Inicijalizacija svih karusela proizvoda
     */
    function initProductCarousels() {
        // Proveri da li element postoji pre pristupa
        const carousels = document.querySelectorAll('.sw-product-carousel');

        if (!carousels || carousels.length === 0) {
            // console.log('Nema sw-product-carousel elemenata na stranici');
            return;
        }

        carousels.forEach(function (carousel, carouselIndex) {
            try {
                const wrapper = carousel.querySelector('.sw-carousel-wrapper');
                const container = carousel.querySelector('.sw-carousel-container');

                // Proveri da li postoje neophodni elementi
                if (!wrapper) {
                    console.log(`Nije pronađen .sw-carousel-wrapper u karuselu #${carouselIndex}`);
                    return;
                }

                if (!container) {
                    console.log(`Nije pronađen .sw-carousel-container u karuselu #${carouselIndex}`);
                    return;
                }

                const prevBtn = carousel.querySelector('.sw-carousel-prev');
                const nextBtn = carousel.querySelector('.sw-carousel-next');
                const dotsContainer = carousel.querySelector('.sw-carousel-dots');

                // Proveri da li postoje kontrolna dugmad
                if (!prevBtn || !nextBtn) {
                    console.log(`Nisu pronađena kontrolna dugmad u karuselu #${carouselIndex}`);
                    return;
                }

                // Svi dots elementi
                const dots = carousel.querySelectorAll('.sw-carousel-dot');

                let currentPosition = 0;
                let slidesPerView = parseInt(carousel.dataset.slides, 10) || 5;
                let slideItems = wrapper.children;
                let totalItems = slideItems.length;
                let isDragging = false;
                let startPos = 0;
                let currentTranslate = 0;
                let prevTranslate = 0;

                // Proveri da li ima dovoljno elemenata
                if (totalItems === 0) {
                    console.log(`Nema elemenata u karuselu #${carouselIndex}`);
                    return;
                }

                // Responsive slidesPerView
                function updateSlidesPerView() {
                    const desktopSlides = parseInt(carousel.dataset.slides, 10) || 5;

                    // Responsive logika ostaje ista
                    if (window.innerWidth < 576) {
                        slidesPerView = 1;
                    } else if (window.innerWidth < 768) {
                        slidesPerView = 2;
                    } else if (window.innerWidth < 992) {
                        slidesPerView = 3;
                    } else if (window.innerWidth < 1200) {
                        slidesPerView = 4;
                    } else {
                        // Koristi specifičan broj slidova za ovaj carousel
                        slidesPerView = desktopSlides;
                    }

                    // Postavi širinu za svaki item
                    if (slideItems && slideItems.length > 0) {
                        for (let i = 0; i < slideItems.length; i++) {
                            const item = slideItems[i];
                            if (item) {
                                item.style.width = (100 / slidesPerView) + '%';
                            }
                        }
                    }

                    // NOVA FUNKCIONALNOST: Generisanje dots-ova
                    if (dotsContainer) {
                        // Izračunaj tačan broj dots-ova
                        const totalSlides = Math.ceil(totalItems / slidesPerView);
                        dotsContainer.innerHTML = ''; // Očisti postojeće dots-ove

                        for (let i = 0; i < totalSlides; i++) {
                            const dot = document.createElement('button');
                            dot.classList.add('sw-carousel-dot');
                            dot.setAttribute('data-slide', i);
                            dot.setAttribute('aria-label', `Slajd ${i + 1}`);
                            dot.setAttribute('role', 'tab');

                            // Prvi dot je aktivan
                            if (i === 0) {
                                dot.classList.add('active');
                                dot.setAttribute('aria-selected', 'true');
                                dot.setAttribute('tabindex', '0');
                            } else {
                                dot.setAttribute('aria-selected', 'false');
                                dot.setAttribute('tabindex', '-1');
                            }

                            // Event listener za click
                            dot.addEventListener('click', () => {
                                const slideToShow = i * slidesPerView;
                                moveCarousel(slideToShow);
                            });

                            dotsContainer.appendChild(dot);
                        }
                    }

                    // Ažuriraj visinu containera
                    updateContainerHeight();

                    // Ponovo izračunaj poziciju
                    moveCarousel(currentPosition, false);
                }

                // Ažurira visinu containera na osnovu najvišeg slidea
                function updateContainerHeight() {
                    if (!container || !slideItems || slideItems.length === 0) return;

                    // Resetuj visine prvo
                    for (let i = 0; i < slideItems.length; i++) {
                        const item = slideItems[i];
                        if (item) {
                            item.style.height = 'auto';
                        }
                    }

                    // Sačekaj da se renderuju izmene
                    setTimeout(() => {
                        // Pronađi najviši slide
                        let maxHeight = 0;
                        for (let i = 0; i < slideItems.length; i++) {
                            const item = slideItems[i];
                            if (item) {
                                const height = item.offsetHeight;
                                if (height > maxHeight) {
                                    maxHeight = height;
                                }
                            }
                        }

                        // Postavi visinu na svaki slide ako postoji maxHeight
                        if (maxHeight > 0) {
                            for (let i = 0; i < slideItems.length; i++) {
                                const item = slideItems[i];
                                if (item) {
                                    item.style.height = maxHeight + 'px';
                                }
                            }

                            // Postavi visinu containera
                            container.style.height = maxHeight + 'px';
                        }
                    }, 100);
                }

                // Pomeri karusel
                function moveCarousel(position, animate = true) {
                    if (!wrapper) return;

                    const maxPosition = Math.max(0, totalItems - slidesPerView);

                    // Ograniči poziciju
                    currentPosition = Math.max(0, Math.min(position, maxPosition));

                    // Izračunaj procenat za transformaciju
                    const slideWidth = 100 / slidesPerView;
                    const translateX = -currentPosition * slideWidth;

                    // Primeni transformaciju
                    if (animate) {
                        wrapper.style.transition = 'transform 0.3s ease';
                    } else {
                        wrapper.style.transition = 'none';
                    }

                    wrapper.style.transform = `translateX(${translateX}%)`;

                    // Vrati transition nakon transformacije
                    if (!animate) {
                        setTimeout(() => {
                            if (wrapper) {
                                wrapper.style.transition = 'transform 0.3s ease';
                            }
                        }, 50);
                    }

                    // Ažuriraj stanje dugmadi
                    if (prevBtn && nextBtn) {
                        prevBtn.disabled = currentPosition === 0;
                        prevBtn.classList.toggle('disabled', currentPosition === 0);
                        nextBtn.disabled = currentPosition >= maxPosition;
                        nextBtn.classList.toggle('disabled', currentPosition >= maxPosition);
                    }

                    // Ažuriraj aktivnu tačkicu
                    updateActiveDot();
                }

                function updateActiveDot() {
                    if (!dotsContainer) return;

                    const dots = dotsContainer.querySelectorAll('.sw-carousel-dot');
                    if (!dots || dots.length === 0) return;

                    // Izračunaj koji dot treba biti aktivan
                    const totalSlides = Math.ceil(totalItems / slidesPerView);
                    const currentSlide = Math.floor(currentPosition / slidesPerView);

                    // Postavi active klasu na odgovarajući dot
                    dots.forEach((dot, index) => {
                        // Ukloni active klasu sa svih dots
                        dot.classList.remove('active');
                        dot.setAttribute('aria-selected', 'false');
                        dot.setAttribute('tabindex', '-1');

                        // Dodaj active klasu samo na trenutni dot
                        if (index === currentSlide) {
                            dot.classList.add('active');
                            dot.setAttribute('aria-selected', 'true');
                            dot.setAttribute('tabindex', '0');
                        }
                    });
                }

                // Postavi event listenere samo ako elementi postoje
                if (prevBtn) {
                    prevBtn.addEventListener('click', function () {
                        moveCarousel(currentPosition - 1);
                    });
                }

                if (nextBtn) {
                    nextBtn.addEventListener('click', function () {
                        moveCarousel(currentPosition + 1);
                    });
                }

                // Dots click - koristi petlju umesto forEach za bolju kompatibilnost
                if (dots && dots.length) {
                    for (let i = 0; i < dots.length; i++) {
                        const dot = dots[i];
                        if (dot) {
                            dot.addEventListener('click', function () {
                                const slideToShow = i * slidesPerView;
                                moveCarousel(slideToShow);
                            });

                            // Pristupačnost - dodajemo keyboard podršku
                            dot.addEventListener('keydown', function (e) {
                                if (e.key === 'Enter' || e.key === ' ') {
                                    e.preventDefault();
                                    this.click();
                                }
                            });
                        }
                    }
                }

                // Touch podrška
                function touchStart(e) {
                    if (!wrapper) return;
                    isDragging = true;
                    startPos = getPositionX(e);
                    wrapper.style.cursor = 'grabbing';
                    wrapper.style.userSelect = 'none';
                }

                function touchMove(e) {
                    if (!isDragging || !wrapper) return;

                    const currentPosition = getPositionX(e);
                    const diff = currentPosition - startPos;
                    currentTranslate = prevTranslate + diff;

                    // Izračunaj procenat za transformaciju
                    const slideWidth = wrapper.clientWidth / slidesPerView;
                    const translatePercent = (currentTranslate / slideWidth) * 100;

                    // Primeni transformaciju
                    wrapper.style.transition = 'none';
                    wrapper.style.transform = `translateX(${translatePercent}%)`;
                }

                function touchEnd() {
                    if (!isDragging || !wrapper) return;

                    isDragging = false;

                    wrapper.style.cursor = 'grab';
                    wrapper.style.userSelect = '';

                    // Izračunaj koliko je prešao
                    const slideWidth = wrapper.clientWidth / slidesPerView;
                    const threshold = slideWidth * 0.2; // 20% of slide width
                    const diff = currentTranslate - prevTranslate;

                    if (Math.abs(diff) > threshold) {
                        // Dovoljno je prevučeno za promenu slajda
                        if (diff > 0) {
                            // Prevučeno udesno - idi na prethodni
                            moveCarousel(currentPosition - 1);
                        } else {
                            // Prevučeno ulevo - idi na sledeći
                            moveCarousel(currentPosition + 1);
                        }
                    } else {
                        // Nije dovoljno prevučeno, ostani na trenutnom
                        moveCarousel(currentPosition);
                    }

                    prevTranslate = currentTranslate;
                }

                function getPositionX(e) {
                    return e.type.includes('mouse') ? e.pageX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
                }

                // Touch eventi - samo ako wrapper postoji
                if (wrapper) {
                    wrapper.addEventListener('mousedown', touchStart);
                    wrapper.addEventListener('touchstart', touchStart);
                    wrapper.addEventListener('mousemove', touchMove);
                    wrapper.addEventListener('touchmove', touchMove);
                    wrapper.addEventListener('mouseup', touchEnd);
                    wrapper.addEventListener('touchend', touchEnd);
                    wrapper.addEventListener('mouseleave', touchEnd);
                }

                // Keyboard pristupačnost
                if (carousel) {
                    carousel.addEventListener('keydown', function (e) {
                        if (e.key === 'ArrowLeft') {
                            e.preventDefault();
                            if (prevBtn) prevBtn.click();
                        } else if (e.key === 'ArrowRight') {
                            e.preventDefault();
                            if (nextBtn) nextBtn.click();
                        }
                    });
                }

                // Responsive handling
                let resizeDebounce;
                window.addEventListener('resize', function () {
                    clearTimeout(resizeDebounce);
                    resizeDebounce = setTimeout(() => {
                        updateSlidesPerView();
                    }, 100);
                });

                // Inicijalna postavka
                updateSlidesPerView();
                moveCarousel(0, false);

                // Dostupnost
                if (carousel) {
                    carousel.setAttribute('role', 'region');
                    carousel.setAttribute('aria-roledescription', 'carousel');
                }
            } catch (error) {
                console.error(`Greška u inicijalizaciji karusela #${carouselIndex}:`, error);
            }
        });
    }

    /**
     * Inicijalizacija blog slidera
     */
    function initBlogSlider() {
        const blogSlider = document.getElementById('newsSlider');
        if (!blogSlider) {
            // console.log('Blog slider nije pronađen na stranici');
            return;
        }

        const sliderInner = blogSlider.querySelector('.slider-inner');
        const prevBtn = blogSlider.querySelector('.slider-control-prev');
        const nextBtn = blogSlider.querySelector('.slider-control-next');

        if (!sliderInner) {
            console.log('Nije pronađen .slider-inner u blog slideru');
            return;
        }

        if (!prevBtn || !nextBtn) {
            console.log('Nisu pronađena kontrolna dugmad u blog slideru');
            return;
        }

        let currentSlide = 0;
        let slidesPerView = 3; // Default
        let isDragging = false;
        let startPos = 0;
        let currentTranslate = 0;
        let prevTranslate = 0;

        const slideItems = sliderInner.querySelectorAll('.slider-item');
        if (!slideItems || slideItems.length === 0) {
            console.log('Nema .slider-item elemenata u blog slideru');
            return;
        }

        function updateSlidesPerView() {
            if (window.innerWidth < 768) {
                slidesPerView = 1;
            } else if (window.innerWidth < 992) {
                slidesPerView = 2;
            } else {
                slidesPerView = 3;
            }

            // Prilagodi širinu svakog slidea
            for (let i = 0; i < slideItems.length; i++) {
                const item = slideItems[i];
                if (item) {
                    item.style.flex = `0 0 ${100 / slidesPerView}%`;
                }
            }

            // Ponovo izračunaj poziciju
            goToSlide(currentSlide, false);
        }

        function goToSlide(index, animate = true) {
            const maxSlide = Math.max(0, slideItems.length - slidesPerView);
            currentSlide = Math.max(0, Math.min(index, maxSlide));

            if (!sliderInner) return;

            if (animate) {
                sliderInner.style.transition = 'transform 0.3s ease';
            } else {
                sliderInner.style.transition = 'none';
            }

            sliderInner.style.transform = `translateX(-${currentSlide * (100 / slidesPerView)}%)`;

            if (!animate) {
                setTimeout(() => {
                    if (sliderInner) {
                        sliderInner.style.transition = 'transform 0.3s ease';
                    }
                }, 50);
            }

            // Ažuriraj stanje dugmadi
            if (prevBtn && nextBtn) {
                prevBtn.classList.toggle('disabled', currentSlide === 0);
                nextBtn.classList.toggle('disabled', currentSlide >= maxSlide);
            }
        }

        // Event handlers
        if (prevBtn) {
            prevBtn.addEventListener('click', () => goToSlide(currentSlide - 1));
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', () => goToSlide(currentSlide + 1));
        }

        // Touch podrška
        function touchStart(e) {
            if (!sliderInner) return;
            isDragging = true;
            startPos = getPositionX(e);
            sliderInner.style.cursor = 'grabbing';
            sliderInner.style.userSelect = 'none';
        }

        function touchMove(e) {
            if (!isDragging || !sliderInner) return;

            const currentPosition = getPositionX(e);
            const diff = currentPosition - startPos;
            currentTranslate = prevTranslate + diff;

            const slideWidth = sliderInner.clientWidth / slidesPerView;
            const translatePercent = (currentTranslate / slideWidth) * 100;

            sliderInner.style.transition = 'none';
            sliderInner.style.transform = `translateX(${translatePercent}%)`;
        }

        function touchEnd() {
            if (!isDragging || !sliderInner) return;

            isDragging = false;

            sliderInner.style.cursor = 'grab';
            sliderInner.style.userSelect = '';

            // Izračunaj koliko je prešao
            const slideWidth = sliderInner.clientWidth / slidesPerView;
            const threshold = slideWidth * 0.2; // 20% of slide width
            const diff = currentTranslate - prevTranslate;

            if (Math.abs(diff) > threshold) {
                // Dovoljno je prevučeno za promenu slajda
                if (diff > 0) {
                    // Prevučeno udesno - idi na prethodni
                    goToSlide(currentSlide - 1);
                } else {
                    // Prevučeno ulevo - idi na sledeći
                    goToSlide(currentSlide + 1);
                }
            } else {
                // Nije dovoljno prevučeno, ostani na trenutnom
                goToSlide(currentSlide);
            }

            prevTranslate = currentTranslate;
        }

        function getPositionX(e) {
            return e.type.includes('mouse') ? e.pageX : (e.touches && e.touches[0] ? e.touches[0].clientX : 0);
        }

        // Touch events
        if (sliderInner) {
            sliderInner.addEventListener('mousedown', touchStart);
            sliderInner.addEventListener('touchstart', touchStart);
            sliderInner.addEventListener('mousemove', touchMove);
            sliderInner.addEventListener('touchmove', touchMove);
            sliderInner.addEventListener('mouseup', touchEnd);
            sliderInner.addEventListener('touchend', touchEnd);
            sliderInner.addEventListener('mouseleave', touchEnd);
        }

        // Keyboard pristupačnost
        if (blogSlider) {
            blogSlider.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    if (prevBtn) prevBtn.click();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    if (nextBtn) nextBtn.click();
                }
            });
        }

        // Responsive handling
        let resizeDebounce;
        window.addEventListener('resize', () => {
            clearTimeout(resizeDebounce);
            resizeDebounce = setTimeout(() => {
                updateSlidesPerView();
            }, 100);
        });

        // Init
        updateSlidesPerView();
        goToSlide(0, false);

        // Dostupnost
        if (blogSlider) {
            blogSlider.setAttribute('role', 'region');
            blogSlider.setAttribute('aria-roledescription', 'carousel');
        }
    }

    // Funkcija za praćenje klika na proizvod (može se iskoristiti za analytics)
    window.trackProductClick = function (element) {
        if (!element || !element.dataset) return;

        const productData = {
            id: element.dataset.productId || '',
            name: element.dataset.productName || '',
            price: element.dataset.productPrice || '',
            position: element.dataset.position || '',
            category: element.dataset.productCategory || '',
            sku: element.dataset.productSku || ''
        };

        console.log('Product clicked:', productData);

        // Ovde možeš dodati kod za Google Analytics, Facebook Pixel ili drugi tracking sistem
        // if (typeof gtag === 'function') {
        //     gtag('event', 'select_content', {
        //         content_type: 'product',
        //         items: [productData]
        //     });
        // }
    };
})();