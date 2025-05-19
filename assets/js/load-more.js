/**
 * Optimizovana implementacija Load More funkcionalnosti
 * Sa posebnim fokusom na ispravno prikazivanje Quick View dugmeta
 * 
 * @package s7design
 */

(function ($) {
    'use strict';

    // Globalna referenca na parametre
    let loadMoreParams = {
        ajaxurl: (typeof sw_load_more_params !== 'undefined') ? sw_load_more_params.ajaxurl : '',
        nonce: (typeof sw_load_more_params !== 'undefined') ? sw_load_more_params.nonce : '',
    };

    // Glavna funkcija koja se pokreće kad je DOM spreman
    $(document).ready(function () {
        initLoadMore();
    });

    /**
     * Debounce helper funkcija 
     */
    function debounce(func, wait, immediate) {
        let timeout;
        return function () {
            const context = this, args = arguments;
            const later = function () {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            const callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    }

    /**
     * Inicijalizacija Load More funkcionalnosti
     */
    function initLoadMore() {
        const $loadMoreBtn = $('#sw-load-more');

        // Ako dugme ne postoji na stranici, izađi
        if (!$loadMoreBtn.length) return;

        // Dohvatanje kontejnera za proizvode
        const $productsContainer = $('.products');

        // Reference na paginacione elemente
        const $paginationContainer = $('.sw-pagination-container');

        // Slušanje klika na Load More dugme
        $loadMoreBtn.off('click').on('click', debounce(function (e) {
            e.preventDefault();
            console.log('Load More dugme kliknuto');

            // Onemogući dugme dok AJAX radi
            if ($loadMoreBtn.hasClass('loading')) return;
            $loadMoreBtn.addClass('loading');

            // Dobijanje trenutnih parametara
            const currentPage = parseInt($loadMoreBtn.attr('data-page'), 10);
            const maxPages = parseInt($loadMoreBtn.attr('data-max-pages'), 10);
            const category = $loadMoreBtn.attr('data-category');
            const orderby = $loadMoreBtn.attr('data-orderby');
            const postsPerPage = parseInt($loadMoreBtn.attr('data-posts-per-page'), 10);

            // Dodaj CSS klasu za tranziciju
            $paginationContainer.addClass('loading');

            console.log('Slanje AJAX zahteva za stranicu:', currentPage + 1);

            // AJAX zahtev
            $.ajax({
                url: loadMoreParams.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: {
                    action: 'load_more_products',
                    nonce: loadMoreParams.nonce,
                    page: currentPage + 1,
                    posts_per_page: postsPerPage,
                    category: category,
                    orderby: orderby
                },
                success: function (response) {
                    console.log('AJAX uspešan, primljen odgovor:', response);

                    if (response && response.success) {
                        // Dodaj nove proizvode u kontejner
                        const newHTML = response.data.html;
                        $productsContainer.append(newHTML);
                        console.log('Dodato novih proizvoda:', $(newHTML).filter('.product').length);

                        // Ažuriraj atribut stranice
                        $loadMoreBtn.attr('data-page', currentPage + 1);

                        // Ažuriraj paginacione indikatore
                        updatePaginationInfo(response.data.pagination || {
                            current_page: currentPage + 1,
                            max_pages: maxPages,
                            found_posts: $productsContainer.find('li.product').length,
                            posts_per_page: postsPerPage
                        });

                        // Sakrij dugme ako smo na poslednjoj stranici
                        if (currentPage + 1 >= maxPages) {
                            $loadMoreBtn.addClass('hidden');
                        }

                        // VAŽNO: Osiguraj da su Quick View dugmad vidljiva - Dodajte ovo
                        ensureQuickViewButtons();

                        // Ažuriranje URL-a bez ručnog preusmeravanja
                        updateURLParameter('paged', currentPage + 1);

                        // Skroluj malo gore da korisnik vidi nove proizvode
                        smoothScrollToNewProducts();

                        // Potpuna reinicijalizacija
                        reinitializeAllComponents();
                    } else {
                        console.error('AJAX greška:', response ? response.data?.message : 'Nema odgovora');
                        showErrorMessage('Došlo je do greške prilikom učitavanja proizvoda.');
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX greška:', xhr, status, error);
                    showErrorMessage('Greška pri komunikaciji sa serverom. Molimo pokušajte ponovo.');
                },
                complete: function () {
                    // Ukloni stanje učitavanja
                    $loadMoreBtn.removeClass('loading');
                    $paginationContainer.removeClass('loading');

                    // Još jednom osiguraj da su Quick View dugmad vidljiva
                    setTimeout(function () {
                        ensureQuickViewButtons();
                        reinitializeAllComponents();
                    }, 500);
                }
            });
        }, 300)); // 300ms debounce za višestruke klikove

        /**
         * NOVA FUNKCIJA: Osigurava da su Quick View dugmad pravilno prikazana
         */
        function ensureQuickViewButtons() {
            console.log('Osiguravanje da su Quick View dugmad vidljiva');

            // 1. Proveri da li postoje proizvodi bez Quick View dugmeta
            const $products = $productsContainer.find('li.product');
            console.log('Ukupno proizvoda:', $products.length);

            $products.each(function (index) {
                const $product = $(this);
                const $quickViewBtn = $product.find('.sw-quick-view-wrapper');

                // Ako proizvod nema Quick View dugme
                if (!$quickViewBtn.length) {
                    console.log('Proizvod #' + index + ' nema Quick View dugme, dodajem ga');

                    // Probaj oporaviti podatke o proizvodu
                    const productId = $product.attr('data-product-id') || $product.find('.add_to_cart_button').data('product_id');
                    const productTitle = $product.find('.woocommerce-loop-product__title').text();
                    const productImage = $product.find('img').attr('src');
                    const productPermalink = $product.find('a').first().attr('href');

                    if (productId) {
                        // Ručno dodaj Quick View dugme
                        const quickViewHTML = `
                            <div class="sw-quick-view-wrapper">
                                <button type="button" 
                                     class="sw-quick-view-button quick-view-button" 
                                     data-product-id="${productId}" 
                                     data-product-title="${productTitle}"
                                     data-product-image="${productImage}"
                                     data-product-image-alt="${productTitle}"
                                     data-product-permalink="${productPermalink}"
                                     aria-label="Brzi pregled"
                                     title="Brzi pregled">
                                    <span class="sw-quick-view-icon">
                                        <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M12 5C5.636 5 1 12 1 12C1 12 5.636 19 12 19C18.364 19 23 12 23 12C23 12 18.364 5 12 5Z" fill="none"></path>
                                            <circle cx="12" cy="12" r="3" fill="none"></circle>
                                        </svg>
                                    </span>
                                </button>
                            </div>
                        `;

                        // Dodaj dugme na proizvod
                        $product.append(quickViewHTML);

                        // Osiguraj da je pozicioniranje ispravno
                        $product.css('position', 'relative');
                    }
                } else {
                    // Ako već postoji dugme, osiguraj da je vidljivo na hover
                    console.log('Proizvod #' + index + ' ima Quick View dugme');

                    // Dodaj inline stilove ako je potrebno (samo za debugovanje)
                    if (!$quickViewBtn.is(':visible')) {
                        console.log('Quick View dugme nije vidljivo, dodajem inline stilove');
                        $quickViewBtn.attr('style', 'display: block !important; opacity: 0;');
                        $product.hover(
                            function () { $quickViewBtn.css('opacity', '1'); },
                            function () { $quickViewBtn.css('opacity', '0'); }
                        );
                    }
                }
            });

            // 2. Proveri CSS stil za hover efekat
            if (!$('.sw-quick-view-test-style').length) {
                $('head').append(`
                    <style class="sw-quick-view-test-style">
                    .product:hover .sw-quick-view-wrapper {
                        opacity: 1 !important;
                    }
                    </style>
                `);
            }

            // 3. Reinicijalizuj Quick View funkcionalnost
            if (typeof window.initQuickView === 'function') {
                window.initQuickView();
            }
        }

        /**
         * Kompletna reinicijalizacija svih komponenti
         */
        function reinitializeAllComponents() {
            console.log('Potpuna reinicijalizacija svih komponenti');

            // 1. Quick View inicijalizacija
            if (typeof window.initQuickView === 'function') {
                window.initQuickView();
            }

            // 2. Trigger WooCommerce događaja
            $(document.body).trigger('post-load');

            // 3. Trigger našeg posebnog događaja
            $(document).trigger('sw-products-loaded');

            // 4. Poseban event za Quick View
            $(document).trigger('sw-quick-view-reload');

            // 5. Pozvati sve ostale funkcije koje treba reinicijalizovati
            if (typeof window.initProductCarousels === 'function') {
                window.initProductCarousels();
            }

            // 6. Izvrši nakon kratkog odlaganja da se osigura da je DOM ažuriran
            setTimeout(function () {
                // Ponovna inicijalizacija Quick View-a
                if (typeof window.initQuickView === 'function') {
                    window.initQuickView();
                }

                // Još jedna provera da li su Quick View dugmad vidljiva
                ensureQuickViewButtons();
            }, 300);
        }

        /**
         * Ažurira paginacione indikatore
         */
        function updatePaginationInfo(paginationData) {
            // Ažuriranje teksta stranice
            $('.sw-current-page').text('Stranica ' + paginationData.current_page + ' od ' + paginationData.max_pages);

            // Ažuriranje brojača proizvoda
            $('.sw-products-count').text(
                'Prikazuje se ' +
                (paginationData.showing_from || '?') + ' - ' +
                (paginationData.showing_to || '?') + ' od ' +
                (paginationData.found_posts || '?') + ' proizvoda'
            );

            // Ažuriranje aktivne stranice u paginaciji
            $('.sw-page-numbers a').removeClass('current');
            $('.sw-page-numbers a[data-page="' + paginationData.current_page + '"]').addClass('current');
        }

        /**
         * Ažurira URL parametar bez preusmeravanja
         */
        function updateURLParameter(key, value) {
            if (history.pushState) {
                let searchParams = new URLSearchParams(window.location.search);
                searchParams.set(key, value);
                let newURL = window.location.pathname + '?' + searchParams.toString();
                window.history.pushState({ path: newURL }, '', newURL);
            }
        }

        /**
         * Glatko skrolovanje do novih proizvoda
         */
        function smoothScrollToNewProducts() {
            const currentPage = parseInt($loadMoreBtn.attr('data-page'), 10);
            const postsPerPage = parseInt($loadMoreBtn.attr('data-posts-per-page'), 10);
            const startIndex = (currentPage - 2) * postsPerPage;

            // Probaj da nađeš novi proizvod
            const $allProducts = $productsContainer.find('li.product');

            if ($allProducts.length > startIndex) {
                const $targetProduct = $allProducts.eq(startIndex);
                const offset = 80;

                $('html, body').animate({
                    scrollTop: $targetProduct.offset().top - offset
                }, 500);

                // Dodaj highlight efekat
                $targetProduct.addClass('sw-new-product-highlight');
                setTimeout(function () {
                    $targetProduct.removeClass('sw-new-product-highlight');
                }, 1500);
            } else {
                // Fallback
                $('html, body').animate({
                    scrollTop: $(window).scrollTop() + 100
                }, 300);
            }
        }

        /**
         * Prikazivanje poruke o grešci
         */
        function showErrorMessage(message) {
            // Obriši postojeće poruke
            $('.sw-load-more-error').remove();

            // Kreiraj poruku
            const $errorMessage = $('<div class="sw-load-more-error">' + message + '</div>');

            // Stilizuj
            $errorMessage.css({
                'color': '#ff3c38',
                'margin-top': '10px',
                'text-align': 'center',
                'font-weight': '500',
                'padding': '8px 15px',
                'background-color': 'rgba(255, 60, 56, 0.1)',
                'border-radius': '4px'
            });

            // Dodaj ispod dugmeta
            $loadMoreBtn.after($errorMessage);

            // Sakrij nakon 5 sekundi
            setTimeout(function () {
                $errorMessage.fadeOut(300, function () {
                    $(this).remove();
                });
            }, 5000);
        }
    }

    // Odmah osiguraj da su Quick View dugmad vidljiva na početku
    $(document).ready(function () {
        // Dodaj CSS za highlight efekat
        $('head').append(`
            <style>
            @keyframes sw-highlight-pulse {
                0% { box-shadow: 0 0 0 0 rgba(255, 221, 85, 0.4); }
                70% { box-shadow: 0 0 0 10px rgba(255, 221, 85, 0); }
                100% { box-shadow: 0 0 0 0 rgba(255, 221, 85, 0); }
            }
            .sw-new-product-highlight {
                animation: sw-highlight-pulse 1.5s ease-in-out;
                border: 1px solid rgba(255, 221, 85, 0.7) !important;
            }
            </style>
        `);
    });

})(jQuery);