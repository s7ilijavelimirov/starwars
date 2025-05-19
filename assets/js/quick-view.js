/**
 * Quick View funkcionalnost za WooCommerce proizvode
 * 
 * @package s7design
 */

(function ($) {
    'use strict';

    // Globalna funkcija za inicijalizaciju Quick View
    window.initQuickView = function () {
        // Quick View varijable
        const $modal = $('#sw-quick-view-modal');
        const $modalInner = $modal.find('.sw-quick-view-inner');
        const $modalLoader = $modal.find('.sw-quick-view-loader');
        const $closeBtn = $modal.find('.sw-quick-view-close');
        const $overlay = $modal.find('.sw-quick-view-overlay');
        const quickViewButtons = document.querySelectorAll('.sw-quick-view-button');

        // Ako nema potrebnih elemenata, izađi
        if (!$modal.length || !$modalInner.length || !quickViewButtons.length) return;

        // Dodavanje click događaja na svako Quick View dugme
        $(document).on('click', '.sw-quick-view-button', function (e) {
            e.preventDefault();
            e.stopPropagation();

            // Dohvatanje podataka o proizvodu
            const productId = $(this).data('product-id');
            const productTitle = $(this).data('product-title');

            if (!productId) return;

            // Prikazivanje modala
            showModal();

            // Učitavanje sadržaja proizvoda
            loadProductContent(productId, productTitle);
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

        // Dodavanje u korpu iz Quick View modala
        $(document).on('click', '.sw-quick-view-add-to-cart', function (e) {
            e.preventDefault();

            const $button = $(this);
            const productId = $button.data('product-id');

            // Prikazivanje animacije učitavanja
            $button.addClass('loading');

            // AJAX zahtev za dodavanje u korpu
            $.ajax({
                url: sw_quick_view_params.ajaxurl,
                type: 'POST',
                data: {
                    action: 'sw_quick_view_add_to_cart',
                    nonce: sw_quick_view_params.nonce,
                    product_id: productId,
                    quantity: 1
                },
                success: function (response) {
                    if (response.success) {
                        // Prikazivanje poruke o uspehu
                        showAddToCartMessage(response.data.message);

                        // Ažuriranje ikonica korpe ako postoje
                        if (typeof updateCartIcon === 'function') {
                            updateCartIcon(response.data.cart_count, response.data.cart_total);
                        }

                        // Zatvaramo modal nakon kratke pauze
                        setTimeout(function () {
                            closeModal();
                        }, 1500);
                    } else {
                        // Prikazivanje poruke o grešci
                        showAddToCartMessage(response.data, 'error');
                    }
                },
                error: function () {
                    showAddToCartMessage('Došlo je do greške. Pokušajte ponovo.', 'error');
                },
                complete: function () {
                    $button.removeClass('loading');
                }
            });
        });

        // Funkcionalnost galerije
        $(document).on('click', '.sw-quick-view-thumbnail', function () {
            const $this = $(this);
            const imageId = $this.data('image-id');

            if (!imageId) return;

            // Dohvatanje punog URL-a slike
            const fullImageSrc = $this.find('img').attr('src').replace('-150x150', '');

            // Ažuriranje glavne slike
            $('.sw-quick-view-image').attr('src', fullImageSrc);

            // Ažuriranje aktivne thumbnail sličice
            $('.sw-quick-view-thumbnail').removeClass('active');
            $this.addClass('active');
        });

        /**
         * Funkcija za prikazivanje modala
         */
        function showModal() {
            // Čišćenje prethodnog sadržaja
            $modalInner.html('<div class="sw-quick-view-loader"><div class="sw-quick-view-spinner"></div></div>');

            // Prikazivanje modala
            $modal.fadeIn(300);

            // Onemogućavanje skrolovanja body-ja
            $('body').addClass('sw-quick-view-open');
        }

        /**
         * Funkcija za zatvaranje modala
         */
        function closeModal() {
            $modal.fadeOut(200);

            // Omogućavanje skrolovanja body-ja
            $('body').removeClass('sw-quick-view-open');

            // Čišćenje sadržaja nakon zatvaranja
            setTimeout(function () {
                $modalInner.html('<div class="sw-quick-view-loader"><div class="sw-quick-view-spinner"></div></div>');
            }, 300);
        }

        /**
         * Funkcija za učitavanje sadržaja proizvoda
         */
        function loadProductContent(productId, productTitle) {
            // Prikazivanje loadera
            $modalLoader.show();

            // AJAX zahtev za dobijanje sadržaja proizvoda
            $.ajax({
                url: sw_quick_view_params.ajaxurl,
                type: 'POST',
                data: {
                    action: 'sw_load_quick_view',
                    nonce: sw_quick_view_params.nonce,
                    product_id: productId
                },
                success: function (response) {
                    if (response.success) {
                        // Dodajemo sadržaj proizvoda u modal
                        $modalInner.html(response.data.html);

                        // Ažuriranje title tag-a
                        document.title = response.data.product_title + ' - Brzi pregled';
                    } else {
                        $modalInner.html('<div class="sw-quick-view-error">Došlo je do greške pri učitavanju proizvoda. Molimo pokušajte ponovo.</div>');
                    }
                },
                error: function () {
                    $modalInner.html('<div class="sw-quick-view-error">Došlo je do greške pri učitavanju proizvoda. Molimo pokušajte ponovo.</div>');
                },
                complete: function () {
                    // Sakrivanje loadera
                    $modalLoader.hide();
                }
            });
        }

        /**
         * Funkcija za prikazivanje poruke o dodavanju u korpu
         */
        function showAddToCartMessage(message, type = 'success') {
            // Uklanjanje postojeće poruke
            $('.sw-quick-view-message').remove();

            // Kreiranje nove poruke
            const $message = $('<div>', {
                class: 'sw-quick-view-message ' + type,
                html: message
            });

            // Dodavanje poruke u modal
            $('.sw-quick-view-actions').prepend($message);

            // Sakrivanje poruke nakon 3 sekunde
            setTimeout(function () {
                $message.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 3000);
        }

        /**
         * Funkcija za ažuriranje ikone korpe (opciono)
         */
        function updateCartIcon(count, total) {
            // Ažuriranje brojača stavki u korpi, ako postoji
            $('.xoo-wsc-items-count').text(count);

            // Ažuriranje ukupne cene, ako postoji
            $('.xoo-wsc-sc-subt').html(total);

            // Ako koristite drugi plugin za korpu, ovde možete dodati ažuriranje i za njega
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