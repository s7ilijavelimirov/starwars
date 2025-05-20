/**
 * Load More funkcionalnost za WooCommerce proizvode - Optimizovana verzija
 * Sa podrškom za Quick View reinicijalizaciju
 * 
 * @package s7design
 * @version 1.0.4
 */

(function ($) {
    'use strict';

    // Globalne varijable
    let isLoading = false;
    let loadedProductIds = [];

    // Funkcija koja se poziva kada je DOM spreman
    $(document).ready(function () {
        initLoadMore();
    });

    /**
     * Inicijalizacija Load More funkcionalnosti
     */
    function initLoadMore() {
        const $loadMoreBtn = $('#sw-load-more');

        // Ako dugme ne postoji na stranici, izađi
        if (!$loadMoreBtn.length) {
            return;
        }

        // Inicijalizacija loadedProductIds sa već učitanim proizvodima
        if ($loadMoreBtn.attr('data-loaded-ids')) {
            loadedProductIds = $loadMoreBtn.attr('data-loaded-ids').split(',').map(id => parseInt(id, 10));
        }

        // Dohvatanje kontejnera za proizvode
        const $productsContainer = $('.products');

        // Provera da li su globalne varijable dostupne
        if (typeof sw_load_more_params === 'undefined') {
            return;
        }

        // Slušanje klika na Load More dugme
        $loadMoreBtn.on('click', function (e) {
            e.preventDefault();

            // Onemogući dugme dok AJAX radi
            if (isLoading) {
                return;
            }

            setLoading(true);

            // Dobijanje trenutnih parametara
            const currentPage = parseInt($loadMoreBtn.attr('data-page'), 10);
            const maxPages = parseInt($loadMoreBtn.attr('data-max-pages'), 10);
            const category = $loadMoreBtn.attr('data-category');
            const orderby = $loadMoreBtn.attr('data-orderby');
            const postsPerPage = parseInt($loadMoreBtn.attr('data-posts-per-page'), 10);

            // Priprema podataka za zahtev
            const requestData = {
                action: 'load_more_products',
                nonce: sw_load_more_params.nonce,
                page: currentPage + 1,
                posts_per_page: postsPerPage,
                category: category,
                orderby: orderby,
                loaded_ids: loadedProductIds.join(',')
            };

            // AJAX zahtev
            $.ajax({
                url: sw_load_more_params.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: requestData,
                success: function (response) {
                    // Resetujemo stanje učitavanja
                    setLoading(false);

                    if (response && response.success) {
                        // Provera da li su svi proizvodi učitani
                        if (response.data.all_loaded) {
                            // Prikaži poruku o uspešnom učitavanju svih proizvoda
                            showSuccessMessage('Svi proizvodi su uspešno učitani.');

                            // Sakrivamo dugme jer nema više proizvoda
                            $loadMoreBtn.addClass('hidden');
                            return;
                        }

                        // Dodavanje novog HTML-a
                        if (response.data.html && response.data.html.length > 0) {
                            $productsContainer.append(response.data.html);

                            // Dodajemo nove ID-jeve u listu već učitanih
                            if (response.data.product_ids && response.data.product_ids.length > 0) {
                                loadedProductIds = loadedProductIds.concat(response.data.product_ids);

                                // Ažuriraj atribut na dugmetu za sledeći zahtev
                                $loadMoreBtn.attr('data-loaded-ids', loadedProductIds.join(','));
                            }

                            // Ažuriranje parametara dugmeta
                            $loadMoreBtn.attr('data-page', currentPage + 1);

                            // Ažuriranje informacija o paginaciji
                            updatePaginationInfo(response.data.pagination);

                            // Sakrij dugme ako smo na poslednjoj stranici
                            if (currentPage + 1 >= maxPages) {
                                $loadMoreBtn.addClass('hidden');
                            }

                            // KLJUČNO: Eksplicitno inicijalizuj Quick View za nove proizvode
                            initializeQuickViewForNewProducts();

                            // Trigger događaj da su proizvodi učitani
                            $(document.body).trigger('sw-products-loaded');
                            $(document.body).trigger('post-load');
                        } else {
                            showNoMoreProducts();
                        }
                    } else {
                        // Prikaz poruke ako nema više proizvoda
                        let errorMessage = 'Nema više proizvoda.';
                        if (response && response.data && response.data.message) {
                            errorMessage = response.data.message;
                        }

                        showNoMoreProducts(errorMessage);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    setLoading(false);

                    // Pokušaj da parsiraš odgovor
                    let errorMessage = 'Došlo je do greške pri učitavanju proizvoda.';
                    try {
                        const errorResponse = JSON.parse(jqXHR.responseText);
                        if (errorResponse && errorResponse.message) {
                            errorMessage = errorResponse.message;
                        }
                    } catch (e) {
                        // Ako ne može da parsira, koristi statusText ili errorThrown
                        errorMessage = errorThrown || textStatus || 'Nepoznata greška.';
                    }

                    showErrorMessage(errorMessage);
                }
            });
        });

        // Slušaj klik na brojeve strana
        $('.sw-page-number').on('click', function (e) {
            e.preventDefault();
            window.location.href = $(this).attr('href');
        });
    }

    /**
     * Promena stanja učitavanja
     */
    function setLoading(loading) {
        isLoading = loading;
        const $loadMoreBtn = $('#sw-load-more');

        if (loading) {
            $loadMoreBtn.addClass('loading');
        } else {
            $loadMoreBtn.removeClass('loading');
        }
    }

    /**
     * NOVA FUNKCIJA: Inicijalizuje Quick View za nove proizvode
     * Rešava problem gde Quick View ne funkcioniše na novo učitanim proizvodima
     */
    function initializeQuickViewForNewProducts() {
        // Prvo proverimo da li je Quick View funkcija uopšte dostupna
        if (typeof window.initQuickView === 'function') {
            // Pozovemo inicijalizaciju
            window.initQuickView();
        } else {
            // Alternativni pristup - direktno binduj događaje na dugmiće 
            // ako je originalna funkcija nedostupna
            $('.sw-quick-view-button').off('click.quickview').on('click.quickview', function (e) {
                e.preventDefault();

                // Dobijamo podatke iz data atributa
                const productId = $(this).data('product-id');
                const productTitle = $(this).data('product-title');
                const productImage = $(this).data('product-image');
                const productPrice = $(this).data('product-price');
                const productPermalink = $(this).data('product-permalink');

                // Otvori modal sa tim podacima
                if ($('#sw-quick-view-modal').length) {
                    const $modal = $('#sw-quick-view-modal');

                    // Popuni modal sa osnovnim podacima
                    $modal.find('.sw-quick-view-title').html(productTitle);
                    $modal.find('.sw-quick-view-image').attr('src', productImage);
                    $modal.find('.sw-quick-view-price').html(productPrice);
                    $modal.find('.sw-quick-view-details').attr('href', productPermalink);

                    // Prikaži modal
                    $modal.fadeIn(300);
                    $('body').addClass('sw-quick-view-open');
                }
            });
        }
    }

    /**
     * Ažuriranje informacija o paginaciji
     */
    function updatePaginationInfo(pagination) {
        if (!pagination) return;

        // Ažuriranje teksta stranice
        $('.sw-current-page').text('Stranica ' + pagination.current_page + ' od ' + pagination.max_pages);

        // Ažuriranje brojača proizvoda
        $('.sw-products-count').text(
            'Prikazuje se ' +
            pagination.showing_from + ' - ' +
            pagination.showing_to + ' od ' +
            pagination.found_posts + ' proizvoda'
        );

        // Ažuriranje aktivne stranice
        $('.sw-page-number').removeClass('current');
        $('.sw-page-number').filter(function () {
            return $(this).text() == pagination.current_page;
        }).addClass('current');
    }

    /**
     * Poruka da nema više proizvoda
     */
    function showNoMoreProducts(message) {
        const $loadMoreBtn = $('#sw-load-more');
        const defaultMessage = 'Nema više proizvoda.';

        // Sakrij dugme
        $loadMoreBtn.addClass('hidden');

        // Dodaj poruku
        if (!$('.no-more-products').length) {
            $loadMoreBtn.after('<p class="no-more-products">' + (message || defaultMessage) + '</p>');
        }
    }

    /**
     * Prikazivanje poruke o uspešnom učitavanju
     */
    function showSuccessMessage(message) {
        const $loadMoreBtn = $('#sw-load-more');

        // Sakrij dugme
        $loadMoreBtn.addClass('hidden');

        // Dodaj poruku uspeha
        if (!$('.sw-load-success').length) {
            $loadMoreBtn.after('<p class="sw-load-success">' + message + '</p>');
        }
    }

    /**
     * Prikazivanje poruke o grešci
     */
    function showErrorMessage(message) {
        const $loadMoreBtn = $('#sw-load-more');

        // Dodaj poruku o grešci
        if (!$('.sw-load-more-error').length) {
            $loadMoreBtn.after('<div class="sw-load-more-error">' + message + '</div>');

            // Sakri poruku nakon 5 sekundi
            setTimeout(function () {
                $('.sw-load-more-error').fadeOut();
            }, 5000);
        }
    }

})(jQuery);