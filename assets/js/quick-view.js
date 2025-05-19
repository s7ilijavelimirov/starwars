/**
 * Optimizovana Quick View funkcionalnost za WooCommerce proizvode
 * Sa podrškom za dinamički učitane proizvode
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

        // Ako nema modalnog prozora, izađi
        if (!$modal.length) return;

        // Dodavanje click događaja na svako Quick View dugme
        // Koristimo delegaciju događaja da bi radilo i na dinamički učitanim dugmadima
        $(document).off('click', '.sw-quick-view-button').on('click', '.sw-quick-view-button', function (e) {
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

        // Zatvaranje modala na klik X - koristimo off().on() da izbegnemo višestruko vezivanje događaja
        $closeBtn.off('click').on('click', function () {
            closeModal();
        });

        // Zatvaranje modala na klik overlaya
        $overlay.off('click').on('click', function () {
            closeModal();
        });

        // Dodatni slušač za overlay (za sigurnost)
        $(document).off('click', '.sw-quick-view-overlay').on('click', '.sw-quick-view-overlay', function () {
            closeModal();
        });

        // Zatvaranje modala pritiskom escape tastera - koristimo namespace za događaj
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

            // Aktiviraj zoom na hover - koristimo delegaciju za sve mouseenter/leave/move događaje
            $container.off('mouseenter mouseleave mousemove')
                .on('mouseenter', function () {
                    const imageUrl = $mainImage.attr('src');
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
                img.src = imgUrl;
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

    // 1. WooCommerce post-load event (standardni)
    $(document.body).on('post-load', function () {
        if (typeof window.initQuickView === 'function') {
            window.initQuickView();
        }
    });

    // 2. Sopstveni event koji se može trigerovati nakon učitavanja novih proizvoda
    $(document).on('sw-products-loaded', function () {
        if (typeof window.initQuickView === 'function') {
            window.initQuickView();
        }
    });

    // 3. Event koji se može trigerovati nakon promene strane ili filtriranja
    $(document).on('sw-content-updated', function () {
        if (typeof window.initQuickView === 'function') {
            window.initQuickView();
        }
    });

    // 4. Slušanje AJAX Complete događaja - za WooCommerce AJAX filtriranje
    $(document).ajaxComplete(function (event, xhr, settings) {
        // Proveri da li je AJAX zahtev vezan za proizvode
        if (settings.url.indexOf('wc-ajax=') > -1 ||
            settings.data && (
                settings.data.indexOf('action=load_more_products') > -1 ||
                settings.data.indexOf('action=woocommerce_') > -1
            )
        ) {
            // Malo odložiti izvršavanje da se DOM potpuno učita
            setTimeout(function () {
                if (typeof window.initQuickView === 'function') {
                    window.initQuickView();
                }
            }, 100);
        }
    });

    // 5. Posmatraj promene u DOM-u za dinamički dodate elemente (napredna metoda)
    if (window.MutationObserver) {
        // Funkcija koja proverava da li je dodat novi proizvod
        const checkForNewProducts = function (addedNodes) {
            for (let i = 0; i < addedNodes.length; i++) {
                const node = addedNodes[i];
                if (node.nodeType === 1) { // Element node
                    // Proveri da li je element proizvod ili sadrži quick view dugme
                    if (node.classList && (
                        node.classList.contains('product') ||
                        node.querySelector('.sw-quick-view-button')
                    )) {
                        return true;
                    }

                    // Rekurzivno proveri decu
                    if (node.childNodes && node.childNodes.length) {
                        if (checkForNewProducts(node.childNodes)) {
                            return true;
                        }
                    }
                }
            }
            return false;
        };

        // Kreiraj observer
        const observer = new MutationObserver(function (mutations) {
            let shouldReinitialize = false;

            mutations.forEach(function (mutation) {
                if (mutation.addedNodes && mutation.addedNodes.length) {
                    if (checkForNewProducts(mutation.addedNodes)) {
                        shouldReinitialize = true;
                    }
                }
            });

            if (shouldReinitialize) {
                if (typeof window.initQuickView === 'function') {
                    window.initQuickView();
                }
            }
        });

        // Posmatraj promene u kontejneru za proizvode
        const productsContainer = document.querySelector('.products');
        if (productsContainer) {
            observer.observe(productsContainer, {
                childList: true,
                subtree: true
            });
        }
    }

})(jQuery);