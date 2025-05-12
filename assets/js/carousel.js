/**
 * Star Wars Theme Carousel JavaScript
 * Optimizovani karusel za proizvode i vesti
 * 
 * @package s7design
 * @version 1.0.0
 */

(function () {
    'use strict';

    document.addEventListener('DOMContentLoaded', function () {
        // Inicijalizuj sve karusele proizvoda
        initProductCarousels();

        // Inicijalizuj blog slider
        initBlogSlider();
    });

    /**
     * Inicijalizacija svih karusela proizvoda
     */
    function initProductCarousels() {
        const carousels = document.querySelectorAll('.sw-product-carousel');

        if (!carousels.length) return;

        carousels.forEach(function (carousel) {
            const wrapper = carousel.querySelector('.sw-carousel-wrapper');
            const container = carousel.querySelector('.sw-carousel-container');
            const prevBtn = carousel.querySelector('.sw-carousel-prev');
            const nextBtn = carousel.querySelector('.sw-carousel-next');
            const dots = carousel.querySelectorAll('.sw-carousel-dot');

            if (!wrapper || !prevBtn || !nextBtn) return;

            let currentPosition = 0;
            let slidesPerView = parseInt(carousel.dataset.slides) || 5;
            let totalItems = wrapper.children.length;
            let isDragging = false;
            let startPos = 0;
            let currentTranslate = 0;
            let prevTranslate = 0;

            // Responsive slidesPerView
            function updateSlidesPerView() {
                const desktopSlides = parseInt(carousel.dataset.slides) || 5;

                if (window.innerWidth < 576) {
                    slidesPerView = 1; // XS screens
                } else if (window.innerWidth < 768) {
                    slidesPerView = 2; // SM screens
                } else if (window.innerWidth < 992) {
                    slidesPerView = 3; // MD screens
                } else if (window.innerWidth < 1200) {
                    slidesPerView = 4; // LG screens
                } else {
                    slidesPerView = desktopSlides; // XL screens
                }

                // Postavi širinu za svaki item
                Array.from(wrapper.children).forEach(function (item) {
                    item.style.width = (100 / slidesPerView) + '%';
                });

                // Ažuriraj visinu containera
                updateContainerHeight();

                // Ponovo izračunaj poziciju
                moveCarousel(currentPosition, false);
            }

            // Ažurira visinu containera na osnovu najvišeg slidea
            function updateContainerHeight() {
                if (!container) return;

                // Resetuj visine prvo
                Array.from(wrapper.children).forEach(item => {
                    item.style.height = 'auto';
                });

                // Sačekaj da se renderuju izmene
                setTimeout(() => {
                    // Pronađi najviši slide
                    let maxHeight = 0;
                    Array.from(wrapper.children).forEach(item => {
                        const height = item.offsetHeight;
                        if (height > maxHeight) {
                            maxHeight = height;
                        }
                    });

                    // Postavi visinu na svaki slide
                    Array.from(wrapper.children).forEach(item => {
                        item.style.height = maxHeight + 'px';
                    });

                    // Postavi visinu containera
                    container.style.height = maxHeight + 'px';
                }, 100);
            }

            // Pomeri karusel
            function moveCarousel(position, animate = true) {
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
                        wrapper.style.transition = 'transform 0.3s ease';
                    }, 50);
                }

                // Ažuriraj stanje dugmadi
                prevBtn.disabled = currentPosition === 0;
                prevBtn.classList.toggle('disabled', currentPosition === 0);
                nextBtn.disabled = currentPosition >= maxPosition;
                nextBtn.classList.toggle('disabled', currentPosition >= maxPosition);

                // Ažuriraj aktivnu tačkicu
                if (dots.length) {
                    const currentSlide = Math.floor(currentPosition / slidesPerView);
                    dots.forEach((dot, i) => {
                        const isActive = i === currentSlide;
                        dot.classList.toggle('active', isActive);
                        dot.setAttribute('aria-selected', isActive ? 'true' : 'false');
                        dot.setAttribute('tabindex', isActive ? '0' : '-1');
                    });
                }
            }

            // Event listeneri
            prevBtn.addEventListener('click', function () {
                moveCarousel(currentPosition - 1);
            });

            nextBtn.addEventListener('click', function () {
                moveCarousel(currentPosition + 1);
            });

            // Dots click
            dots.forEach((dot, i) => {
                dot.addEventListener('click', function () {
                    const slideToShow = i * slidesPerView;
                    moveCarousel(slideToShow);
                });

                // Pristupačnost - dodajemo keyboard podršku
                dot.addEventListener('keydown', function (e) {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        dot.click();
                    }
                });
            });

            // Touch podrška
            function touchStart(e) {
                isDragging = true;
                startPos = getPositionX(e);
                wrapper.style.cursor = 'grabbing';
                wrapper.style.userSelect = 'none';
            }

            function touchMove(e) {
                if (!isDragging) return;

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
                if (!isDragging) return;

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
                return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
            }

            // Touch eventi
            wrapper.addEventListener('mousedown', touchStart);
            wrapper.addEventListener('touchstart', touchStart);
            wrapper.addEventListener('mousemove', touchMove);
            wrapper.addEventListener('touchmove', touchMove);
            wrapper.addEventListener('mouseup', touchEnd);
            wrapper.addEventListener('touchend', touchEnd);
            wrapper.addEventListener('mouseleave', touchEnd);

            // Keyboard pristupačnost
            carousel.addEventListener('keydown', function (e) {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    prevBtn.click();
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    nextBtn.click();
                }
            });

            // Responsive handling
            window.addEventListener('resize', function () {
                updateSlidesPerView();
            });

            // Inicijalna postavka
            updateSlidesPerView();
            moveCarousel(0, false);

            // Dostupnost
            carousel.setAttribute('role', 'region');
            carousel.setAttribute('aria-roledescription', 'carousel');
        });
    }

    /**
     * Inicijalizacija blog slidera
     */
    function initBlogSlider() {
        const blogSlider = document.getElementById('newsSlider');
        if (!blogSlider) return;

        const sliderInner = blogSlider.querySelector('.slider-inner');
        const prevBtn = blogSlider.querySelector('.slider-control-prev');
        const nextBtn = blogSlider.querySelector('.slider-control-next');

        if (!sliderInner || !prevBtn || !nextBtn) return;

        let currentSlide = 0;
        let slidesPerView = 3; // Default
        let isDragging = false;
        let startPos = 0;
        let currentTranslate = 0;
        let prevTranslate = 0;

        function updateSlidesPerView() {
            if (window.innerWidth < 768) {
                slidesPerView = 1;
            } else if (window.innerWidth < 992) {
                slidesPerView = 2;
            } else {
                slidesPerView = 3;
            }

            // Prilagodi širinu svakog slidea
            const slideItems = sliderInner.querySelectorAll('.slider-item');
            slideItems.forEach(item => {
                item.style.flex = `0 0 ${100 / slidesPerView}%`;
            });

            // Ponovo izračunaj poziciju
            goToSlide(currentSlide, false);
        }

        function goToSlide(index, animate = true) {
            const slideItems = sliderInner.querySelectorAll('.slider-item');
            const maxSlide = Math.max(0, slideItems.length - slidesPerView);

            currentSlide = Math.max(0, Math.min(index, maxSlide));

            if (animate) {
                sliderInner.style.transition = 'transform 0.3s ease';
            } else {
                sliderInner.style.transition = 'none';
            }

            sliderInner.style.transform = `translateX(-${currentSlide * (100 / slidesPerView)}%)`;

            if (!animate) {
                setTimeout(() => {
                    sliderInner.style.transition = 'transform 0.3s ease';
                }, 50);
            }

            // Ažuriraj stanje dugmadi
            prevBtn.classList.toggle('disabled', currentSlide === 0);
            nextBtn.classList.toggle('disabled', currentSlide >= maxSlide);
        }

        // Event handlers
        prevBtn.addEventListener('click', () => goToSlide(currentSlide - 1));
        nextBtn.addEventListener('click', () => goToSlide(currentSlide + 1));

        // Touch podrška
        function touchStart(e) {
            isDragging = true;
            startPos = getPositionX(e);
            sliderInner.style.cursor = 'grabbing';
            sliderInner.style.userSelect = 'none';
        }

        function touchMove(e) {
            if (!isDragging) return;

            const currentPosition = getPositionX(e);
            const diff = currentPosition - startPos;
            currentTranslate = prevTranslate + diff;

            const slideWidth = sliderInner.clientWidth / slidesPerView;
            const translatePercent = (currentTranslate / slideWidth) * 100;

            sliderInner.style.transition = 'none';
            sliderInner.style.transform = `translateX(${translatePercent}%)`;
        }

        function touchEnd() {
            if (!isDragging) return;

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
            return e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
        }

        // Touch events
        sliderInner.addEventListener('mousedown', touchStart);
        sliderInner.addEventListener('touchstart', touchStart);
        sliderInner.addEventListener('mousemove', touchMove);
        sliderInner.addEventListener('touchmove', touchMove);
        sliderInner.addEventListener('mouseup', touchEnd);
        sliderInner.addEventListener('touchend', touchEnd);
        sliderInner.addEventListener('mouseleave', touchEnd);

        // Keyboard pristupačnost
        blogSlider.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                prevBtn.click();
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                nextBtn.click();
            }
        });

        // Responsive handling
        window.addEventListener('resize', () => {
            updateSlidesPerView();
        });

        // Init
        updateSlidesPerView();
        goToSlide(0, false);
    }
})();