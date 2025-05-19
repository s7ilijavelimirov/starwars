/**
 * Star Wars WooCommerce Custom JS
 * Optimizacije i poboljšanja za WooCommerce
 * Verzija: 1.2.0 - Optimizovana, bez height kontrole
 */
document.addEventListener('DOMContentLoaded', function () {
    // Proveri da li je jQuery dostupan
    if (typeof jQuery === 'undefined') {
        console.error('jQuery is not loaded');
        return;
    }

    // Koristi jQuery
    (function ($) {
        'use strict';

        // Plus/minus dugmad za količinu
        function initQuantityButtons() {
            // Samo na WooCommerce stranicama
            if (!$('body').hasClass('woocommerce') &&
                !$('body').hasClass('woocommerce-cart') &&
                !$('body').hasClass('woocommerce-checkout')) {
                return;
            }

            // Dodaj dugmiće na sve quantity inpute
            if (!$('.quantity').find('.plus').length) {
                $('.quantity:not(.buttons-added)').addClass('buttons-added').append('<button type="button" class="plus">+</button>').prepend('<button type="button" class="minus">-</button>');
            }

            // Event handler za klik na plus/minus
            $(document).on('click', '.quantity .plus, .quantity .minus', function () {
                // Get values
                var $qty = $(this).closest('.quantity').find('.qty'),
                    currentVal = parseFloat($qty.val()),
                    max = parseFloat($qty.attr('max')),
                    min = parseFloat($qty.attr('min')),
                    step = $qty.attr('step');

                // Format values
                if (!currentVal || currentVal === '' || currentVal === 'NaN') currentVal = 0;
                if (max === '' || max === 'NaN') max = '';
                if (min === '' || min === 'NaN') min = 0;
                if (step === 'any' || step === '' || step === undefined || parseFloat(step) === 'NaN') step = 1;

                // Change the value
                if ($(this).is('.plus')) {
                    if (max && (currentVal >= max)) {
                        $qty.val(max);
                    } else {
                        $qty.val(currentVal + parseFloat(step));
                    }
                } else {
                    if (min && (currentVal <= min)) {
                        $qty.val(min);
                    } else if (currentVal > 0) {
                        $qty.val(currentVal - parseFloat(step));
                    }
                }

                // Trigger change event
                $qty.trigger('change');
            });

            // Dodaj event za ažuriranje korpe
            $(document.body).on('updated_cart_totals', function () {
                if (!$('.quantity').find('.plus').length) {
                    $('.quantity:not(.buttons-added)').addClass('buttons-added').append('<button type="button" class="plus">+</button>').prepend('<button type="button" class="minus">-</button>');
                }
            });
        }

        // Unapređena galerija proizvoda
        function enhanceProductGallery() {
            if (!$('.woocommerce-product-gallery').length) return;

            // Dodaj zoom efekat ako je dostupan
            if (typeof $.fn.zoom === 'function') {
                $('.woocommerce-product-gallery__image a').zoom({
                    touch: false
                });
            }
        }

        // Unapređeni hover efekti - koristi CSS transformacije umesto jQuery manipulacije
        function enhanceProductHover() {
            // Dodaj klasu za CSS da preuzme kontrolu umesto JS manipulacije
            $('.products li.product').addClass('sw-hover-ready');

            // Nema potrebe za jQuery hover manipulacijom - koristimo CSS
            // Ovo poboljšava performanse
        }

        // Poboljšani responzivni checkout
        function enhanceCheckoutResponsive() {
            if (!$('.woocommerce-checkout').length) return;

            // Prikaži učitavanje za checkout
            $('form.checkout').on('checkout_place_order', function () {
                $(this).addClass('processing').append('<div class="checkout-loading"><div class="spinner"></div><span>Obrađujemo vašu porudžbinu...</span></div>');
            });
        }

        // Lazy load za slike
        function lazyLoadImages() {
            // Koristi native Intersection Observer kada je moguće
            if ('IntersectionObserver' in window) {
                const imagesToLoad = document.querySelectorAll('.woocommerce img:not(.lazy-loaded)');

                const imageObserver = new IntersectionObserver((entries, observer) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting) {
                            const img = entry.target;
                            if (!img.getAttribute('loading')) {
                                img.setAttribute('loading', 'lazy');
                                img.classList.add('lazy-loaded');
                            }
                            observer.unobserve(img);
                        }
                    });
                });

                imagesToLoad.forEach(img => {
                    imageObserver.observe(img);
                });
            } else {
                // Fallback za starije browsere
                $('.woocommerce img:not(.lazy-loaded)').each(function () {
                    if (!$(this).attr('loading')) {
                        $(this).attr('loading', 'lazy').addClass('lazy-loaded');
                    }
                });
            }
        }

        // Dodavanje u korpu animacija
        function enhanceAddToCart() {
            $(document.body).on('added_to_cart', function () {
                $('.added_to_cart').addClass('wc-forward-animated');
                setTimeout(function () {
                    $('.added_to_cart').removeClass('wc-forward-animated');
                }, 1000);
            });
        }

        // Poboljšanje za mobilne uređaje
        function enhanceMobileExperience() {
            if (window.innerWidth < 768) {
                // Poboljšaj interakciju na dodir
                $('.products li.product').on('touchstart', function () {
                    $(this).addClass('touch-hover');
                }).on('touchend touchcancel', function () {
                    var $product = $(this);
                    setTimeout(function () {
                        $product.removeClass('touch-hover');
                    }, 300);
                });
            }
        }

        // Optimizacija za kategorije
        function enhanceCategories() {
            // Ako smo na strani kategorije
            if ($('body').hasClass('woocommerce-product-category')) {
                // Dodaj efekat na hover za kategorije
                $('.subcategory-item a').hover(
                    function () {
                        $(this).find('.subcategory-name').css('color', '#ffdd55');
                    },
                    function () {
                        $(this).find('.subcategory-name').css('color', '');
                    }
                );
            }
        }

        // Inicijalizuj sve funkcije
        function init() {
            initQuantityButtons();
            enhanceProductGallery();
            enhanceProductHover();
            enhanceCheckoutResponsive();
            lazyLoadImages();
            enhanceAddToCart();
            enhanceMobileExperience();
            enhanceCategories();

            // Ukloni sve inline height stilove koji su možda već postavljeni
            removeInlineHeights();

            // Pokreni ponovo pri promeni veličine ekrana
            $(window).on('resize', function () {
                enhanceMobileExperience();
            });

            // Load event - inicijalizacije nakon kompletnog učitavanja
            $(window).on('load', function () {
                lazyLoadImages();
            });
        }

        // Nova funkcija za uklanjanje inline height stilova
        function removeInlineHeights() {
            // Pronađi sve karte proizvoda i ukloni visinu
            $('.products li.product').each(function () {
                const $product = $(this);

                // Dohvati trenutni stil
                const style = $product.attr('style');

                // Ako postoji stil, ukloni height atribut
                if (style) {
                    // Ukloni height deo iz style atributa
                    const newStyle = style.replace(/height\s*:\s*[^;]+;?/gi, '');

                    // Postavi novi stil ili ukloni atribut ako je prazan
                    if (newStyle.trim()) {
                        $product.attr('style', newStyle);
                    } else {
                        $product.removeAttr('style');
                    }
                }
            });

            // Sprečavanje ponovnog postavljanja height - kroz CSS rešenje
            $('head').append('<style>.products li.product { height: auto !important; }</style>');
        }

        // MutationObserver da sprečimo bilo koji pokušaj postavljanja inline visine
        function setupHeightObserver() {
            if (!window.MutationObserver) return;

            const observer = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    // Proveri da li je promenjen style atribut
                    if (mutation.type === 'attributes' &&
                        mutation.attributeName === 'style' &&
                        mutation.target.className &&
                        mutation.target.className.includes('product')) {

                        const style = mutation.target.getAttribute('style');

                        // Ako stil sadrži height, ukloni ga
                        if (style && style.includes('height:')) {
                            const newStyle = style.replace(/height\s*:\s*[^;]+;?/gi, '');

                            if (newStyle.trim()) {
                                mutation.target.setAttribute('style', newStyle);
                            } else {
                                mutation.target.removeAttribute('style');
                            }
                        }
                    }
                });
            });

            // Posmatraj sve trenutne i buduće proizvode
            document.querySelectorAll('.products').forEach(productList => {
                observer.observe(productList, {
                    childList: true,
                    subtree: true,
                    attributes: true,
                    attributeFilter: ['style']
                });
            });
        }

        // Pokreni nakon učitavanja strane
        $(function () {
            init();
            setupHeightObserver();

            // Nakon svakog AJAX zahteva
            $(document).ajaxComplete(function () {
                setTimeout(removeInlineHeights, 10);
            });
        });

    })(jQuery);
});