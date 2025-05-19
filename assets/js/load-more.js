/**
 * Visoko optimizovana Load More + Quick View funkcionalnost
 * Sa brzo-učitavajućim batch varijacijama
 * 
 * @package s7design
 * @version 2.1
 */

(function ($) {
    'use strict';

    // Globalna referenca na parametre
    let loadMoreParams = {
        ajaxurl: (typeof sw_load_more_params !== 'undefined') ? sw_load_more_params.ajaxurl : '',
        nonce: (typeof sw_load_more_params !== 'undefined') ? sw_load_more_params.nonce : '',
    };

    // Keš za varijacije proizvoda radi optimizacije
    const variationsCache = {};

    // Glavna funkcija koja se pokreće kad je DOM spreman
    $(document).ready(function () {
        initLoadMore();
    });

    /**
     * Debounce helper funkcija za bolje performanse
     */
    function debounce(func, wait) {
        let timeout;
        return function () {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(function () { func.apply(context, args); }, wait);
        };
    }

    /**
     * Helper funkcija za sigurno escapovanje HTML atributa
     */
    function escapeHtml(str) {
        if (!str || typeof str !== 'string') return '';
        return str.replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    /**
     * Helper funkcija za osiguravanje HTTPS URL-ova slika
     */
    function ensureHttps(url) {
        if (!url) return url;
        return url.replace(/^http:\/\//i, 'https://');
    }

    /**
     * Optimizovana funkcija za dohvatanje varijacija za više proizvoda odjednom
     * Batch API poziv za minimalno opterećenje servera
     * 
     * @param {Array} productIds - Niz ID-ova proizvoda
     * @param {function} callback - Callback funkcija koja se poziva nakon dohvatanja
     */
    function getBatchProductVariations(productIds, callback) {
        // Ako nema ID-jeva, odmah vrati prazan objekat
        if (!productIds || !productIds.length) {
            callback({});
            return;
        }

        // Izbaci duplikate
        const uniqueIds = [...new Set(productIds)];

        // Izbaci ID-jeve koji su već u kešu
        const idsToFetch = uniqueIds.filter(id => !variationsCache[id]);

        // Ako su svi proizvodi već u kešu, odmah vrati rezultat
        if (idsToFetch.length === 0) {
            const cachedResults = uniqueIds.reduce((acc, id) => {
                acc[id] = variationsCache[id] || '';
                return acc;
            }, {});
            callback(cachedResults);
            return;
        }

        // Brži batch AJAX zahtev za sve ID-jeve odjednom
        $.ajax({
            url: loadMoreParams.ajaxurl,
            type: 'POST',
            data: {
                action: 'fetch_product_variations',
                nonce: loadMoreParams.nonce,
                product_ids: idsToFetch
            },
            success: function (response) {
                if (response && response.success && response.data && response.data.variations) {
                    // Dodaj dohvaćene varijacije u keš
                    Object.keys(response.data.variations).forEach(id => {
                        variationsCache[id] = response.data.variations[id];
                    });

                    // Kombinuj sa postojećim keširanim vrednostima
                    const allResults = uniqueIds.reduce((acc, id) => {
                        acc[id] = variationsCache[id] || '';
                        return acc;
                    }, {});

                    callback(allResults);
                } else {
                    // U slučaju greške, vrati prazne varijacije
                    const emptyResults = uniqueIds.reduce((acc, id) => {
                        acc[id] = '';
                        return acc;
                    }, {});
                    callback(emptyResults);
                }
            },
            error: function () {
                // U slučaju greške, vrati prazne varijacije
                const emptyResults = uniqueIds.reduce((acc, id) => {
                    acc[id] = '';
                    return acc;
                }, {});
                callback(emptyResults);
            }
        });
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

            // Izmeri trenutnu poziciju skrola
            const scrollPosition = $(window).scrollTop();

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

            // AJAX zahtev za učitavanje proizvoda
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
                    if (response && response.success) {
                        // Privremeni div za procesiranje HTML-a
                        const $tempDiv = $('<div></div>');
                        $tempDiv.html(response.data.html);

                        // Dohvati sve product IDs brzo iz klasa
                        const productIds = [];
                        $tempDiv.find('li.product').each(function () {
                            const classAttr = $(this).attr('class');
                            if (classAttr) {
                                const match = classAttr.match(/post-(\d+)/);
                                if (match && match[1]) {
                                    productIds.push(match[1]);
                                }
                            }
                        });

                        // Brzi AJAX batch dohvat varijacija za sve odjednom
                        getBatchProductVariations(productIds, function (variationsData) {
                            // Ažuriraj proizvode sa quick view dugmadima
                            $tempDiv.find('li.product').each(function () {
                                const $product = $(this);
                                const $link = $product.find('a.woocommerce-LoopProduct-link');

                                if (!$link.length) return;

                                // Brzo dohvati product ID iz klase
                                const classAttr = $product.attr('class');
                                if (!classAttr) return;

                                const match = classAttr.match(/post-(\d+)/);
                                const productId = match ? match[1] : '';

                                if (!productId) return;

                                // Dohvati ostale podatke
                                const productTitle = $product.find('.woocommerce-loop-product__title').text();
                                let productImage = $product.find('img').attr('src');
                                // Osiguraj HTTPS za sliku
                                productImage = ensureHttps(productImage);

                                const productImageAlt = $product.find('img').attr('alt') || productTitle;
                                let productPrice = $product.find('.price').html() || '';

                                // Dohvati varijacije iz batch rezultata
                                const productVariations = variationsData[productId] || '';

                                // Kreiraj quick view dugme
                                const quickViewHTML = `<div class="sw-quick-view-wrapper"><button type="button" 
                                    class="sw-quick-view-button quick-view-button" 
                                    data-product-id="${productId}" 
                                    data-product-title="${escapeHtml(productTitle)}"
                                    data-product-image="${productImage}"
                                    data-product-image-alt="${escapeHtml(productImageAlt)}"
                                    data-product-price='${productPrice}'
                                    data-product-permalink="${ensureHttps($link.attr('href'))}"
                                    data-product-variations="${escapeHtml(productVariations)}"
                                    aria-label="Brzi pregled"
                                    title="Brzi pregled"><span class="sw-quick-view-icon"><svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M12 5C5.636 5 1 12 1 12C1 12 5.636 19 12 19C18.364 19 23 12 23 12C23 12 18.364 5 12 5Z" fill="none"></path>
                                        <circle cx="12" cy="12" r="3" fill="none"></circle>
                                    </svg></span></button></div>`;

                                // Ubaci quick view dugme nakon slike
                                $link.find('img').after(quickViewHTML);
                            });

                            // Dodaj obrađene proizvode u kontejner
                            $productsContainer.append($tempDiv.html());

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

                            // Ažuriranje URL-a bez ručnog preusmeravanja
                            updateURLParameter('paged', currentPage + 1);

                            // Zadrži poziciju skrola
                            $(window).scrollTop(scrollPosition);

                            // Trigger dodatnih događaja za inicijalizaciju
                            setTimeout(function () {
                                // Inicijalizuj Quick View
                                if (typeof window.initQuickView === 'function') {
                                    window.initQuickView();
                                }

                                // Triggeruj WooCommerce event
                                $(document.body).trigger('post-load');
                            }, 10);

                            // Ukloni stanje učitavanja
                            $loadMoreBtn.removeClass('loading');
                            $paginationContainer.removeClass('loading');
                        });
                    } else {
                        // Prikaži poruku o grešci samo ako nema odgovora
                        showErrorMessage('Došlo je do greške prilikom učitavanja proizvoda.');
                        $loadMoreBtn.removeClass('loading');
                        $paginationContainer.removeClass('loading');
                    }
                },
                error: function () {
                    showErrorMessage('Greška pri komunikaciji sa serverom. Molimo pokušajte ponovo.');
                    $loadMoreBtn.removeClass('loading');
                    $paginationContainer.removeClass('loading');
                }
            });
        }, 250)); // 250ms debounce za optimalne performanse

        /**
         * Ažurira paginacione indikatore - optimizovana verzija
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

            // Efikasno ažuriranje aktivne stranice
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
                history.pushState(
                    { path: window.location.pathname + '?' + searchParams.toString() },
                    '',
                    window.location.pathname + '?' + searchParams.toString()
                );
            }
        }

        /**
         * Prikazivanje poruke o grešci
         */
        function showErrorMessage(message) {
            const $existingError = $('.sw-load-more-error');
            if ($existingError.length) {
                $existingError.text(message).show();
            } else {
                const $errorMessage = $('<div class="sw-load-more-error">' + message + '</div>').css({
                    'color': '#ff3c38',
                    'margin-top': '10px',
                    'text-align': 'center',
                    'font-weight': '500',
                    'padding': '8px 15px',
                    'background-color': 'rgba(255, 60, 56, 0.1)',
                    'border-radius': '4px'
                });
                $loadMoreBtn.after($errorMessage);
            }

            // Auto-hide nakon 5 sekundi
            setTimeout(function () {
                $('.sw-load-more-error').fadeOut(300);
            }, 5000);
        }
    }

    // Aktiviraj naprednu verziju quick-view procesiranja
    $(function () {
        if (typeof window.initQuickView === 'function') {
            const originalInitQuickView = window.initQuickView;

            // Poboljšana verzija koja osigurava da varijacije rade
            window.initQuickView = function () {
                // Pozovi originalnu inicijalizaciju
                originalInitQuickView();

                // Dodatno poboljšanje - koristi attr umesto data za varijacije
                $(document).off('click.sw_quick_view_attr').on('click.sw_quick_view_attr', '.sw-quick-view-button', function () {
                    const $button = $(this);
                    const $variations = $('#sw-quick-view-modal').find('.sw-quick-view-variations');

                    // Koristi attr umesto data za dobijanje varijacija
                    const productVariations = $button.attr('data-product-variations') || '';

                    // Proveri da li ima varijacija i prikaži ih
                    if (productVariations) {
                        $variations.html('<div class="sw-variations-title">Varijacije:</div><div class="sw-variations-data">' + productVariations + '</div>');
                        $variations.show();
                    } else {
                        $variations.hide();
                    }
                });
            };

            // Ponovo inicijalizuj quick view
            window.initQuickView();
        }
    });

})(jQuery);