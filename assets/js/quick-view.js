/**
 * Optimizovani Quick View JS - bez duplikovanja, brže učitavanje
 * 
 * @package s7design
 * @version 2.3
 */

(function ($) {
    'use strict';

    // Cache za elemente da se izbegnu ponovna pretraživanja
    let $modal, $modalContent, $closeBtn, $overlay, $container;
    let isInitialized = false;

    // Globalna funkcija za inicijalizaciju Quick View 
    window.initQuickView = function () {
        // Inicijalizuj samo jednom
        if (isInitialized) return;

        // Cache DOM elemente
        $modal = $('#sw-quick-view-modal');
        if (!$modal.length) return;

        $modalContent = $modal.find('.sw-quick-view-content');
        $closeBtn = $modal.find('.sw-quick-view-close');
        $overlay = $modal.find('.sw-quick-view-overlay');
        $container = $modal.find('.sw-quick-view-container');

        // Dodavanje click događaja - koristi delegiranje samo jednom
        $(document).off('click.quickview').on('click.quickview', '.sw-quick-view-button', handleQuickViewClick);

        // Zatvaranje modala - samo jednom
        $closeBtn.on('click.quickview', closeModal);
        $overlay.on('click.quickview', closeModal);

        // Zatvaranje klikom izvan sadržaja
        $container.on('click.quickview', function (e) {
            if (e.target === this) closeModal();
        });

        // Spreči zatvaranje kada se klikne na sadržaj
        $modalContent.on('click.quickview', function (e) {
            e.stopPropagation();
        });

        // Escape key
        $(document).off('keyup.quickview').on('keyup.quickview', function (e) {
            if (e.key === 'Escape' && $modal.is(':visible')) {
                closeModal();
            }
        });

        isInitialized = true;
    };

    /**
     * Handler za Quick View click
     */
    function handleQuickViewClick(e) {
        e.preventDefault();
        e.stopPropagation();

        const $button = $(this);
        const productId = $button.data('product-id');
        if (!productId) return;

        // Učitaj podatke
        const productData = {
            title: $button.data('product-title') || '',
            image: ($button.data('product-image') || '').replace(/^http:\/\//i, 'https://'),
            imageAlt: $button.data('product-image-alt') || $button.data('product-title') || '',
            price: $button.data('product-price') || '',
            description: $button.data('product-description') || '',
            permalink: $button.data('product-permalink') || '#',
            dimensions: $button.data('product-dimensions') || '',
            variations: $button.attr('data-product-variations') || ''
        };

        // Popuni modal
        populateModal(productData);
        showModal();

        // Inicijalizuj zoom sa delay-om
        setTimeout(initImageZoom, 300);
    }

    /**
     * Popuni modal sa podacima
     */
    function populateModal(data) {
        $modal.find('.sw-quick-view-title').html(data.title);

        const $mainImage = $modal.find('.sw-quick-view-image.main-image');
        const $zoomOverlay = $modal.find('.sw-quick-view-image.zoom-overlay');

        $mainImage.attr({ 'src': data.image, 'alt': data.imageAlt });
        $zoomOverlay.attr({ 'src': data.image, 'alt': data.imageAlt });

        $modal.find('.sw-quick-view-price').html(data.price);
        $modal.find('.sw-quick-view-description').html(data.description);
        $modal.find('.sw-quick-view-details').attr('href', data.permalink);

        // Dimenzije
        const $dimensions = $modal.find('.sw-quick-view-dimensions');
        if (data.dimensions) {
            $dimensions.html(`<div class="sw-dimensions-title">Dimenzije:</div><div class="sw-dimensions-data">${data.dimensions}</div>`).show();
        } else {
            $dimensions.hide();
        }

        // Varijacije
        const $variations = $modal.find('.sw-quick-view-variations');
        if (data.variations) {
            $variations.html(`<div class="sw-variations-title">Modeli:</div><div class="sw-variations-data">${data.variations}</div>`).show();
        } else {
            $variations.hide();
        }
    }

    /**
     * Zoom efekat - optimizovan
     */
    function initImageZoom() {
        const $container = $('.sw-image-zoom-container');
        if (!$container.length) return;

        const $mainImage = $container.find('.sw-quick-view-image.main-image');
        const $zoomOverlay = $container.find('.sw-quick-view-image.zoom-overlay');

        if (!$mainImage.length || !$zoomOverlay.length) return;

        const zoomFactor = 4;

        // Reset zoom overlay
        $zoomOverlay.css({
            'transform': `scale(${zoomFactor})`,
            'opacity': 0
        });

        // Event handlers sa namespace
        $container.off('.zoom')
            .on('mouseenter.zoom', function () {
                let imageUrl = $mainImage.attr('src').replace(/^http:\/\//i, 'https://');
                $zoomOverlay.attr('src', imageUrl);
                $container.addClass('sw-image-zoom-active');
            })
            .on('mouseleave.zoom', function () {
                $container.removeClass('sw-image-zoom-active');
                $zoomOverlay.css({
                    'transform': `scale(${zoomFactor})`,
                    'opacity': 0,
                    'transform-origin': '50% 50%'
                });
            })
            .on('mousemove.zoom', function (e) {
                if (!$container.hasClass('sw-image-zoom-active')) return;

                const rect = this.getBoundingClientRect();
                const xRatio = (e.clientX - rect.left) / rect.width;
                const yRatio = (e.clientY - rect.top) / rect.height;

                const xOffset = Math.max(0, Math.min(100, xRatio * 100));
                const yOffset = Math.max(0, Math.min(100, yRatio * 100));

                $zoomOverlay.css({
                    'transform-origin': `${xOffset}% ${yOffset}%`,
                    'transform': `scale(${zoomFactor})`,
                    'opacity': 1
                });
            });
    }

    /**
     * Prikaži modal
     */
    function showModal() {
        $modal.fadeIn(200);
        $('body').addClass('sw-quick-view-open');
    }

    /**
     * Zatvori modal
     */
    function closeModal() {
        $modal.fadeOut(150);
        $('body').removeClass('sw-quick-view-open');
    }

    // DOM ready - inicijalizuj samo jednom
    $(function () {
        window.initQuickView();
    });

})(jQuery);