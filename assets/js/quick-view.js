/**
 * Optimizovani Quick View JS sa podrškom za varijacije
 * Sada zatvara modal klikom bilo gde izvan sadržaja
 * 
 * @package s7design
 * @version 2.2
 */

(function ($) {
    'use strict';

    // Globalna funkcija za inicijalizaciju Quick View 
    window.initQuickView = function () {
        // Quick View varijable
        const $modal = $('#sw-quick-view-modal');
        const $modalContent = $modal.find('.sw-quick-view-content');
        const $closeBtn = $modal.find('.sw-quick-view-close');
        const $overlay = $modal.find('.sw-quick-view-overlay');
        const $container = $modal.find('.sw-quick-view-container');

        // Ako nema modalnog prozora, izađi
        if (!$modal.length) return;

        // Dodavanje click događaja na svako Quick View dugme
        $(document).off('click', '.sw-quick-view-button').on('click', '.sw-quick-view-button', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Dohvatanje podataka o proizvodu iz data atributa
            const $button = $(this);
            const productId = $button.data('product-id');

            if (!productId) return;

            // KLJUČNA IZMENA: Koristi attr umesto data za varijacije
            // Učitaj osnovne podatke iz data atributa
            const productTitle = $button.data('product-title') || '';
            let productImage = $button.data('product-image') || '';
            // Osiguraj HTTPS za sliku
            productImage = productImage.replace(/^http:\/\//i, 'https://');

            const productImageAlt = $button.data('product-image-alt') || productTitle;
            const productPrice = $button.data('product-price') || '';
            const productDesc = $button.data('product-description') || '';
            const productPermalink = $button.data('product-permalink') || '#';
            const productDimensions = $button.data('product-dimensions') || '';
            // Uzimamo varijacije iz ATTR umesto iz DATA
            const productVariations = $button.attr('data-product-variations') || '';

            // Reference na modal elemente
            const $title = $modal.find('.sw-quick-view-title');
            const $mainImage = $modal.find('.sw-quick-view-image.main-image');
            const $zoomOverlay = $modal.find('.sw-quick-view-image.zoom-overlay');
            const $price = $modal.find('.sw-quick-view-price');
            const $desc = $modal.find('.sw-quick-view-description');
            const $details = $modal.find('.sw-quick-view-details');
            const $dimensions = $modal.find('.sw-quick-view-dimensions');
            const $variations = $modal.find('.sw-quick-view-variations');

            // Popuni modal osnovnim podacima
            $title.html(productTitle);

            // Postavi slike za glavni prikaz i za zoom
            $mainImage.attr({
                'src': productImage,
                'alt': productImageAlt
            });

            $zoomOverlay.attr({
                'src': productImage,
                'alt': productImageAlt
            });

            $price.html(productPrice);
            $desc.html(productDesc);
            $details.attr('href', productPermalink);

            // Dimenzije ako postoje
            if (productDimensions) {
                $dimensions.html('<div class="sw-dimensions-title">Dimenzije:</div><div class="sw-dimensions-data">' + productDimensions + '</div>');
                $dimensions.show();
            } else {
                $dimensions.hide();
            }

            // Varijacije ako postoje
            if (productVariations) {
                $variations.html('<div class="sw-variations-title">Modeli:</div><div class="sw-variations-data">' + productVariations + '</div>');
                $variations.show();
            } else {
                $variations.hide();
            }

            // Prikaži modal
            showModal();

            // Inicijalizuj zoom efekat
            setTimeout(function () {
                initImageZoom();
            }, 300);
        });

        // Zatvaranje modala na klik X
        $closeBtn.off('click').on('click', function () {
            closeModal();
        });

        // Zatvaranje modala na klik overlaya - POSTOJEĆI KOD
        $overlay.off('click').on('click', function () {
            closeModal();
        });

        // NOVO: Zatvaranje modala na klik bilo gde izvan sadržaja
        $container.off('click').on('click', function (e) {
            // Proveri da li je klik bio direktno na container-u, a ne na njegovoj deci
            if (e.target === this) {
                closeModal();
            }
        });

        // NOVO: Spreči zatvaranje modala kada se klikne na sadržaj
        $modalContent.off('click').on('click', function (e) {
            e.stopPropagation();
        });

        // Zatvaranje modala pritiskom escape tastera
        $(document).off('keyup.quickview').on('keyup.quickview', function (e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                closeModal();
            }
        });

        /**
         * Implementacija zoom efekta za slike
         */
        function initImageZoom() {
            const $container = $('.sw-image-zoom-container');
            if (!$container.length) return;

            const $mainImage = $container.find('.sw-quick-view-image.main-image');
            const $zoomOverlay = $container.find('.sw-quick-view-image.zoom-overlay');

            if (!$mainImage.length || !$zoomOverlay.length) return;

            // Zoom faktor - koliko puta će slika biti uvećana
            const zoomFactor = 4;

            // Inicijalno sakrij zoom overlay
            $zoomOverlay.css({
                'transform': `scale(${zoomFactor})`,
                'opacity': 0
            });

            // Aktiviraj zoom na hover - optimizovana verzija
            $container.off('mouseenter mouseleave mousemove')
                .on('mouseenter', function () {
                    // Osiguraj HTTPS za sliku
                    let imageUrl = $mainImage.attr('src');
                    imageUrl = imageUrl.replace(/^http:\/\//i, 'https://');

                    $zoomOverlay.attr('src', imageUrl);
                    $container.addClass('sw-image-zoom-active');
                })
                .on('mouseleave', function () {
                    $container.removeClass('sw-image-zoom-active');

                    // Resetuj zoom overlay
                    $zoomOverlay.css({
                        'transform': `scale(${zoomFactor})`,
                        'opacity': 0,
                        'transform-origin': '50% 50%' // Reset na centar
                    });
                })
                .on('mousemove', function (e) {
                    if (!$container.hasClass('sw-image-zoom-active')) return;

                    const containerWidth = $container.outerWidth();
                    const containerHeight = $container.outerHeight();

                    // Pozicija miša u containeru (0-1)
                    const xRatio = (e.pageX - $container.offset().left) / containerWidth;
                    const yRatio = (e.pageY - $container.offset().top) / containerHeight;

                    // Izračunaj x i y pomak za zoom overlay
                    const xOffset = Math.max(0, Math.min(100, xRatio * 100));
                    const yOffset = Math.max(0, Math.min(100, yRatio * 100));

                    // Primeni transformaciju na zoom overlay
                    $zoomOverlay.css({
                        'transform-origin': `${xOffset}% ${yOffset}%`,
                        'transform': `scale(${zoomFactor})`,
                        'opacity': 1
                    });
                });
        }

        /**
         * Funkcija za prikazivanje modala
         */
        function showModal() {
            $modal.fadeIn(200);
            $('body').addClass('sw-quick-view-open');

            // Performance optimizacija - pre-fetch slike
            const imgUrl = $modal.find('.sw-quick-view-image.main-image').attr('src');
            if (imgUrl) {
                const img = new Image();
                img.src = imgUrl.replace(/^http:\/\//i, 'https://'); // Osiguraj HTTPS
            }
        }

        /**
         * Funkcija za zatvaranje modala
         */
        function closeModal() {
            $modal.fadeOut(150);
            $('body').removeClass('sw-quick-view-open');
        }
    };

    // Inicijalizacija kada je DOM spreman
    $(document).ready(function () {
        // Pokretanje Quick View inicijalizacije
        if (typeof window.initQuickView === 'function') {
            window.initQuickView();
        }
    });

    // Event listeneri za različite načine osvežavanja sadržaja
    $(document.body).on('post-load', function () {
        if (typeof window.initQuickView === 'function') {
            window.initQuickView();
        }
    });

    $(document).on('sw-products-loaded', function () {
        if (typeof window.initQuickView === 'function') {
            window.initQuickView();
        }
    });

    // Slušanje AJAX Complete događaja - optimizovana verzija
    $(document).ajaxComplete(function (event, xhr, settings) {
        // Proveri da li je AJAX zahtev vezan za proizvode - brza provera
        if (settings.url && settings.url.indexOf('admin-ajax.php') > -1 &&
            settings.data && (
                settings.data.indexOf('load_more_products') > -1 ||
                settings.data.indexOf('fetch_product_variations') > -1
            )
        ) {
            // Malo odložiti izvršavanje da se DOM potpuno učita
            setTimeout(function () {
                if (typeof window.initQuickView === 'function') {
                    window.initQuickView();
                }
            }, 50); // Brži timeout
        }
    });

})(jQuery);