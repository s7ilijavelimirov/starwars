/**
 * Load More funkcionalnost za WooCommerce proizvode - Finalna verzija
 * 
 * @package s7design
 * @version 1.0.3
 */

(function ($) {
    'use strict';

    // Globalne varijable
    let isLoading = false;

    // Pamtimo ID-jeve proizvoda koje smo već učitali
    let loadedProductIds = [];

    // Funkcija koja se poziva kada je DOM spreman
    $(document).ready(function () {
        console.log('DOM Spreman - inicijalizacija Load More');
        initLoadMore();
    });

    /**
     * Inicijalizacija Load More funkcionalnosti
     */
    function initLoadMore() {
        const $loadMoreBtn = $('#sw-load-more');

        // Debug info - da vidimo da li je dugme uopšte pronađeno
        console.log('Load More inicijalizacija, dugme pronađeno:', $loadMoreBtn.length > 0);

        // Ako dugme ne postoji na stranici, izađi
        if (!$loadMoreBtn.length) {
            console.log('Dugme nije pronađeno, izlazim iz funkcije');
            return;
        }

        // Debug info za atribute dugmeta
        console.log('Load More dugme podaci:', {
            'data-page': $loadMoreBtn.attr('data-page'),
            'data-max-pages': $loadMoreBtn.attr('data-max-pages'),
            'data-category': $loadMoreBtn.attr('data-category'),
            'data-orderby': $loadMoreBtn.attr('data-orderby'),
            'data-posts-per-page': $loadMoreBtn.attr('data-posts-per-page'),
            'data-loaded-ids': $loadMoreBtn.attr('data-loaded-ids') || 'nije postavljen'
        });

        // Inicijalizacija loadedProductIds sa već učitanim proizvodima
        if ($loadMoreBtn.attr('data-loaded-ids')) {
            loadedProductIds = $loadMoreBtn.attr('data-loaded-ids').split(',').map(id => parseInt(id, 10));
            console.log('Inicijalizovani već učitani ID-jevi proizvoda:', loadedProductIds);
        }

        // Dohvatanje kontejnera za proizvode
        const $productsContainer = $('.products');
        console.log('Products kontejner pronađen:', $productsContainer.length > 0);

        // Provera da li su globalne varijable dostupne
        console.log('sw_load_more_params dostupan:', typeof sw_load_more_params !== 'undefined');

        if (typeof sw_load_more_params === 'undefined') {
            console.error('sw_load_more_params nije dostupan! Proverite wp_localize_script u PHP kodu.');
            return;
        }

        console.log('sw_load_more_params:', {
            'ajaxurl': sw_load_more_params.ajaxurl,
            'nonce': sw_load_more_params.nonce ? 'Postoji' : 'Ne postoji',
        });

        // Slušanje klika na Load More dugme
        $loadMoreBtn.on('click', function (e) {
            e.preventDefault();

            console.log('Load More dugme kliknuto');

            // Onemogući dugme dok AJAX radi
            if (isLoading) {
                console.log('Već se učitava, prekidamo');
                return;
            }

            setLoading(true);

            // Dobijanje trenutnih parametara
            const currentPage = parseInt($loadMoreBtn.attr('data-page'), 10);
            const maxPages = parseInt($loadMoreBtn.attr('data-max-pages'), 10);
            const category = $loadMoreBtn.attr('data-category');
            const orderby = $loadMoreBtn.attr('data-orderby');
            const postsPerPage = parseInt($loadMoreBtn.attr('data-posts-per-page'), 10);

            // Debug info pre zahteva
            console.log('AJAX zahtev parametri:', {
                currentPage: currentPage,
                maxPages: maxPages,
                category: category,
                orderby: orderby,
                postsPerPage: postsPerPage,
                loadedProductIds: loadedProductIds
            });

            // Priprema podataka za zahtev
            const requestData = {
                action: 'load_more_products',
                nonce: sw_load_more_params.nonce,
                page: currentPage + 1,
                posts_per_page: postsPerPage,
                category: category,
                orderby: orderby,
                loaded_ids: loadedProductIds.join(',') // Prosleđujemo već učitane ID-jeve
            };

            console.log('AJAX zahtev data:', requestData);

            // AJAX zahtev
            $.ajax({
                url: sw_load_more_params.ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: requestData,
                beforeSend: function (xhr) {
                    console.log('AJAX zahtev se šalje...');
                },
                success: function (response) {
                    console.log('AJAX uspeh, odgovor:', response);

                    // Resetujemo stanje učitavanja
                    setLoading(false);

                    if (response && response.success) {
                        // KLJUČNA IZMENA: Provera da li su svi proizvodi učitani
                        if (response.data.all_loaded) {
                            console.log('Svi proizvodi su uspešno učitani, nema više za prikaz');

                            // Prikaži poruku o uspešnom učitavanju svih proizvoda
                            showSuccessMessage('Svi proizvodi su uspešno učitani.');

                            // Sakrivamo dugme jer nema više proizvoda
                            $loadMoreBtn.addClass('hidden');
                            return;
                        }

                        // Dodavanje novog HTML-a
                        if (response.data.html && response.data.html.length > 0) {
                            console.log('Dodajem HTML sadržaj u kontejner');
                            $productsContainer.append(response.data.html);

                            // Dodajemo nove ID-jeve u listu već učitanih
                            if (response.data.product_ids && response.data.product_ids.length > 0) {
                                loadedProductIds = loadedProductIds.concat(response.data.product_ids);
                                console.log('Ažurirani učitani ID-jevi:', loadedProductIds);

                                // Ažuriraj atribut na dugmetu za sledeći zahtev
                                $loadMoreBtn.attr('data-loaded-ids', loadedProductIds.join(','));
                            }

                            // Ažuriranje parametara dugmeta
                            $loadMoreBtn.attr('data-page', currentPage + 1);

                            // Ažuriranje informacija o paginaciji
                            updatePaginationInfo(response.data.pagination);

                            // Sakrij dugme ako smo na poslednjoj stranici
                            if (currentPage + 1 >= maxPages) {
                                console.log('Poslednja stranica dostignuta, sakrivamo dugme');
                                $loadMoreBtn.addClass('hidden');
                            }

                            // Inicijalizuj Quick View ako postoji
                            if (typeof window.initQuickView === 'function') {
                                console.log('Inicijalizujemo Quick View');
                                window.initQuickView();
                            }

                            // Trigger događaj da su proizvodi učitani
                            $(document.body).trigger('sw-products-loaded');
                            $(document.body).trigger('post-load');
                        } else {
                            console.warn('Dobijen prazan HTML ili nema HTML-a u odgovoru');
                            showNoMoreProducts();
                        }
                    } else {
                        // Prikaz poruke ako nema više proizvoda
                        console.log('Nema više proizvoda ili greška u odgovoru', response);

                        // Prikaži detaljniju poruku o grešci
                        let errorMessage = 'Nema više proizvoda.';
                        if (response && response.data && response.data.message) {
                            errorMessage = response.data.message;
                        }

                        console.error('AJAX greška:', errorMessage);
                        showNoMoreProducts(errorMessage);
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                    console.error('AJAX greška:', textStatus, errorThrown);
                    console.log('jqXHR status:', jqXHR.status);
                    console.log('jqXHR responseText:', jqXHR.responseText);

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

            // Dobijanje broja stranice
            const clickedPage = parseInt($(this).text(), 10);
            console.log('Kliknuta stranica:', clickedPage);

            // Promena URL-a
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
            console.log('Postavljanje loading stanja');
            $loadMoreBtn.addClass('loading');
        } else {
            console.log('Uklanjanje loading stanja');
            $loadMoreBtn.removeClass('loading');
        }
    }

    /**
     * Ažuriranje informacija o paginaciji
     */
    function updatePaginationInfo(pagination) {
        if (!pagination) return;
        console.log('Ažuriranje paginacije:', pagination);

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
            console.log('Dodajemo poruku:', message || defaultMessage);
            $loadMoreBtn.after('<p class="no-more-products">' + (message || defaultMessage) + '</p>');
        }
    }

    /**
     * NOVO: Prikazivanje poruke o uspešnom učitavanju
     */
    function showSuccessMessage(message) {
        const $loadMoreBtn = $('#sw-load-more');

        // Sakrij dugme
        $loadMoreBtn.addClass('hidden');

        // Dodaj poruku uspeha
        if (!$('.sw-load-success').length) {
            console.log('Dodajemo poruku o uspešnom učitavanju:', message);
            $loadMoreBtn.after('<p class="sw-load-success">' + message + '</p>');
        }
    }

    /**
     * Prikazivanje poruke o grešci
     */
    function showErrorMessage(message) {
        const $loadMoreBtn = $('#sw-load-more');
        console.log('Prikazujemo poruku o grešci:', message);

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