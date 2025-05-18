/**
 * WooCommerce JavaScript funkcionalnosti
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

        // Unapređeni hover efekti
        function enhanceProductHover() {
            $('.products li.product').hover(
                function () {
                    $(this).find('img').css('transform', 'scale(1.05)');
                    $(this).find('.woocommerce-loop-product__title').css('color', '#ffdd55');
                },
                function () {
                    $(this).find('img').css('transform', 'scale(1)');
                    $(this).find('.woocommerce-loop-product__title').css('color', '');
                }
            );
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
            $('.woocommerce img:not(.lazy-loaded)').each(function () {
                if (!$(this).attr('loading')) {
                    $(this).attr('loading', 'lazy').addClass('lazy-loaded');
                }
            });
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

        // Fiksiraj header korpe za mobilne
        function fixMobileCartHeader() {
            if (window.innerWidth < 768 && $('.woocommerce-cart').length) {
                var $cartHeader = $('.shop_table thead tr');
                var headerOffset = $cartHeader.offset().top;

                $(window).on('scroll', function () {
                    if ($(window).scrollTop() > headerOffset) {
                        if (!$cartHeader.hasClass('fixed-header')) {
                            $cartHeader.addClass('fixed-header');
                            $cartHeader.closest('table').addClass('has-fixed-header');
                        }
                    } else {
                        $cartHeader.removeClass('fixed-header');
                        $cartHeader.closest('table').removeClass('has-fixed-header');
                    }
                });
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
            fixMobileCartHeader();

            // Pokreni ponovo pri promeni veličine ekrana
            $(window).on('resize', function () {
                enhanceMobileExperience();
                fixMobileCartHeader();
            });
        }

        // Pokreni nakon učitavanja strane
        $(function () {
            init();
        });

    })(jQuery);
});