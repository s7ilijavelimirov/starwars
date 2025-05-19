/**
 * Optimizovana Quick View funkcionalnost za WooCommerce proizvode
 * 
 * @package s7design
 */

(function ($) {
    'use strict';

    // Globalna funkcija za inicijalizaciju Quick View - optimizovana verzija bez AJAX-a
    window.initQuickView = function () {
        // Quick View varijable
        const $modal = $('#sw-quick-view-modal');
        const $closeBtn = $modal.find('.sw-quick-view-close');
        const $overlay = $modal.find('.sw-quick-view-overlay');
        const quickViewButtons = document.querySelectorAll('.sw-quick-view-button');

        // Ako nema potrebnih elemenata, izađi
        if (!$modal.length || !quickViewButtons.length) return;

        // Dodavanje click događaja na svako Quick View dugme
        $(document).on('click', '.sw-quick-view-button', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Dohvatanje podataka o proizvodu iz data atributa
            const $button = $(this);
            const productId = $button.data('product-id');

            if (!productId) return;

            // Učitaj osnovne podatke direktno iz data atributa (brži pristup)
            const productTitle = $button.data('product-title') || '';
            const productImage = $button.data('product-image') || '';
            const productImageAlt = $button.data('product-image-alt') || productTitle;
            const productPrice = $button.data('product-price') || '';
            const productDesc = $button.data('product-description') || '';
            const productPermalink = $button.data('product-permalink') || '#';
            const productDimensions = $button.data('product-dimensions') || '';
            const productVariations = $button.data('product-variations') || '';

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
                $variations.html('<div class="sw-variations-title">Varijacije:</div><div class="sw-variations-data">' + productVariations + '</div>');
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
        $closeBtn.on('click', function () {
            closeModal();
        });

        // Zatvaranje modala na klik overlaya
        $overlay.on('click', function () {
            closeModal();
        });

        // Zatvaranje modala pritiskom escape tastera
        $(document).on('keyup', function (e) {
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
            const zoomFactor = 1.8;

            // Inicijalno sakrij zoom overlay
            $zoomOverlay.css({
                'transform': `scale(${zoomFactor})`,
                'opacity': 0
            });

            // Aktiviraj zoom na hover
            $container.on('mouseenter', function () {
                const imageUrl = $mainImage.attr('src');
                $zoomOverlay.attr('src', imageUrl);
                $container.addClass('sw-image-zoom-active');
            });

            // Deaktiviraj zoom na mouseout
            $container.on('mouseleave', function () {
                $container.removeClass('sw-image-zoom-active');

                // Resetuj zoom overlay
                $zoomOverlay.css({
                    'transform': `scale(${zoomFactor})`,
                    'opacity': 0,
                    'transform-origin': '50% 50%' // Reset na centar
                });
            });

            // Prati kretanje miša za zoom efekat
            $container.on('mousemove', function (e) {
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
            $modal.fadeIn(200); // Ubrzali smo fade-in na 200ms
            $('body').addClass('sw-quick-view-open');

            // Performance optimizacija - pre-fetch slike
            const imgUrl = $modal.find('.sw-quick-view-image.main-image').attr('src');
            if (imgUrl) {
                const img = new Image();
                img.src = imgUrl;
            }
        }

        /**
         * Funkcija za zatvaranje modala
         */
        function closeModal() {
            $modal.fadeOut(150); // Ubrzali smo fade-out na 150ms
            $('body').removeClass('sw-quick-view-open');
        }
    };

    // Inicijalizacija kada je DOM spreman
    $(document).ready(function () {
        // Pokretanje Quick View inicijalizacije
        if (typeof window.initQuickView === 'function') {
            window.initQuickView();
        }

        // Ponovno inicijalizovanje nakon AJAX učitavanja kroz Load More
        $(document.body).on('post-load', function () {
            if (typeof window.initQuickView === 'function') {
                window.initQuickView();
            }
        });
    });

})(jQuery);