/**
 * Star Wars WooCommerce Custom JS
 * Minimalna, optimizovana verzija za Star Wars temu
 * Verzija: 1.0.0
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
        // Lazy load za slike - poboljšava performanse
        // function lazyLoadImages() {
        //     if ('IntersectionObserver' in window) {
        //         const imagesToLoad = document.querySelectorAll('.woocommerce img:not([loading="lazy"])');
        //         if (!imagesToLoad.length) return;

        //         const imageObserver = new IntersectionObserver((entries, observer) => {
        //             entries.forEach(entry => {
        //                 if (entry.isIntersecting) {
        //                     const img = entry.target;
        //                     img.setAttribute('loading', 'lazy');
        //                     observer.unobserve(img);
        //                 }
        //             });
        //         });

        //         imagesToLoad.forEach(img => {
        //             imageObserver.observe(img);
        //         });
        //     }
        // }

        // Animacija nakon dodavanja u korpu
        function enhanceAddToCart() {
            $(document.body).on('added_to_cart', function () {
                $('.added_to_cart').addClass('wc-forward-animated');
                setTimeout(function () {
                    $('.added_to_cart').removeClass('wc-forward-animated');
                }, 1000);
            });
        }

        // Inicijalizuj sve funkcije
        function init() {
            initQuantityButtons();
           // lazyLoadImages();
            enhanceAddToCart();
        }

        // Pokreni inicijalizaciju
        $(function () {
            init();
        });

    })(jQuery);
});
