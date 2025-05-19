/**
 * AJAX Load More funkcionalnost za WooCommerce arhive
 * Sa podrškom za paginacione indikatore
 * 
 * @package s7design
 */

(function ($) {
    'use strict';

    // Glavna funkcija koja se pokreće kad je DOM spreman
    $(document).ready(function () {
        const $loadMoreBtn = $('#sw-load-more');

        // Ako dugme ne postoji na stranici, izađi
        if (!$loadMoreBtn.length) return;

        // Dohvatanje kontejnera za proizvode
        const $productsContainer = $('.products');

        // Dohvatanje paginacionih elemenata
        const $currentPageSpan = $('.sw-current-page');
        const $productsCountSpan = $('.sw-products-count');
        const $pageNumbers = $('.sw-page-numbers a');

        // Slušanje klika na Load More dugme
        $loadMoreBtn.on('click', function (e) {
            e.preventDefault();

            // Onemogući dugme dok AJAX radi
            if ($loadMoreBtn.hasClass('loading')) return;
            $loadMoreBtn.addClass('loading');

            // Dobijanje trenutnih parametara
            const currentPage = parseInt($loadMoreBtn.attr('data-page'));
            const maxPages = parseInt($loadMoreBtn.attr('data-max-pages'));
            const category = $loadMoreBtn.attr('data-category');
            const orderby = $loadMoreBtn.attr('data-orderby');
            const postsPerPage = parseInt($loadMoreBtn.attr('data-posts-per-page'));

            // AJAX zahtev
            $.ajax({
                url: sw_load_more_params.ajaxurl,
                type: 'POST',
                data: {
                    action: 'load_more_products',
                    nonce: sw_load_more_params.nonce,
                    page: currentPage + 1,
                    posts_per_page: postsPerPage,
                    category: category,
                    orderby: orderby
                },
                success: function (response) {
                    if (response.success) {
                        // Dodaj nove proizvode u kontejner
                        $productsContainer.append(response.data.html);

                        // Ažuriraj atribut stranice
                        $loadMoreBtn.attr('data-page', currentPage + 1);

                        // Ažuriraj paginacione indikatore
                        updatePaginationInfo(currentPage + 1, maxPages, response.data.found_posts, postsPerPage);

                        // Sakrij dugme ako smo na poslednjoj stranici
                        if (currentPage + 1 >= maxPages) {
                            $loadMoreBtn.addClass('hidden');
                        }

                        // Osvežavanje Swiper-a ili drugih skripti ako postoje
                        refreshProductComponents();

                        // Ažuriranje URL-a bez ručnog preusmeravanja
                        updateURLParameter('paged', currentPage + 1);

                        // Skroluj malo gore da korisnik vidi nove proizvode
                        $('html, body').animate({
                            scrollTop: $(window).scrollTop() + 100
                        }, 300);
                    } else {
                        console.error('AJAX greška:', response.data);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                },
                complete: function () {
                    // Ukloni stanje učitavanja
                    $loadMoreBtn.removeClass('loading');
                }
            });
        });

        // Slušanje klika na broj stranice
        $('.sw-page-numbers a').on('click', function (e) {
            e.preventDefault();

            const clickedPage = parseInt($(this).attr('data-page'));
            const currentPage = parseInt($loadMoreBtn.attr('data-page'));

            // Ako je kliknuta trenutna stranica ili je dugme onemogućeno, izađi
            if (clickedPage === currentPage || $loadMoreBtn.hasClass('loading')) return;

            // Ako kliknemo na stranicu koja je već učitana (prethodna), samo sakrijemo vidljive proizvode
            if (clickedPage < currentPage) {
                window.location.href = $(this).attr('href');
                return;
            }

            // Inače, učitavamo proizvode AJAX-om (ako idemo na sledeću stranicu)
            $loadMoreBtn.addClass('loading');

            // AJAX zahtev za učitavanje određene stranice
            const maxPages = parseInt($loadMoreBtn.attr('data-max-pages'));
            const category = $loadMoreBtn.attr('data-category');
            const orderby = $loadMoreBtn.attr('data-orderby');
            const postsPerPage = parseInt($loadMoreBtn.attr('data-posts-per-page'));

            $.ajax({
                url: sw_load_more_params.ajaxurl,
                type: 'POST',
                data: {
                    action: 'load_more_products',
                    nonce: sw_load_more_params.nonce,
                    page: clickedPage,
                    posts_per_page: postsPerPage,
                    category: category,
                    orderby: orderby,
                    replace_content: true
                },
                success: function (response) {
                    if (response.success) {
                        // Za klik na paginaciju, zamenjujemo umesto da dodajemo 
                        window.location.href = $(e.target).attr('href');
                    } else {
                        console.error('AJAX greška:', response.data);
                    }
                },
                error: function (xhr, status, error) {
                    console.error('AJAX error:', error);
                },
                complete: function () {
                    $loadMoreBtn.removeClass('loading');
                }
            });
        });

        /**
         * Ažurira paginacione indikatore na osnovu trenutnog stanja
         */
        function updatePaginationInfo(currentPage, maxPages, foundPosts, postsPerPage) {
            // Ažuriranje teksta trenutne stranice
            if ($currentPageSpan.length) {
                $currentPageSpan.text('Stranica ' + currentPage + ' od ' + maxPages);
            }

            // Ažuriranje brojača proizvoda
            if ($productsCountSpan.length) {
                const start = (currentPage - 1) * postsPerPage + 1;
                const end = Math.min(currentPage * postsPerPage, foundPosts);
                $productsCountSpan.text('Prikazuje se ' + start + ' - ' + end + ' od ' + foundPosts + ' proizvoda');
            }

            // Ažuriranje aktivne stranice u paginaciji
            if ($pageNumbers.length) {
                $pageNumbers.removeClass('current');
                $pageNumbers.filter('[data-page="' + currentPage + '"]').addClass('current');
            }
        }

        /**
         * Osvežava komponente nakon učitavanja novih proizvoda
         */
        function refreshProductComponents() {
            // Ako koristite Swiper ili druge skripte, osvežite ih ovde
            if (typeof Swiper !== 'undefined' && typeof initProductCarousels === 'function') {
                initProductCarousels();
            }

            // Pokušaj inicijalizacije Quick View funkcije ako postoji
            if (typeof initQuickView === 'function') {
                try {
                    initQuickView();
                } catch (e) {
                    console.warn('Failed to initialize Quick View:', e);
                }
            }

            // Ako koristite WooCommerce skripte
            $(document.body).trigger('post-load');
        }

        /**
         * Ažurira URL parametar bez preusmeravanja stranice
         */
        function updateURLParameter(key, value) {
            if (history.pushState) {
                let searchParams = new URLSearchParams(window.location.search);
                searchParams.set(key, value);
                let newURL = window.location.pathname + '?' + searchParams.toString();
                window.history.pushState({ path: newURL }, '', newURL);
            }
        }
    });
})(jQuery);