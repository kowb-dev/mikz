/**
 * MKX Live Search Initialization
 * @package MKX_Live_Search
 * @version 1.0.1
 */
(function($) {
    'use strict';

    function addDropdownStyles() {
        if ($('#mkx-search-dynamic-styles').length) {
            return;
        }

        const styles = `
<style id="mkx-search-dynamic-styles">
    .mkx-search-wrapper {
        position: relative !important;
        width: 100% !important;
    }

    .mkx-search-results {
        position: absolute !important;
        top: 100% !important;
        left: 0 !important;
        right: 0 !important;
        z-index: 9999 !important;
        margin-top: 0.5rem !important;
        background: #fff !important;
        border: 1px solid #e5e7eb !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 25px rgba(0,0,0,0.1) !important;
        max-height: 70vh !important;
        overflow-y: auto !important;
        display: none !important;
    }

    .mkx-search-results.mkx-search-results--visible {
        display: block !important;
    }
</style>
        `;

        $('head').append(styles);
    }

    function findSearchInput() {
        const selectors = [
            'input[type="search"]',
            'input[name="s"]',
            '.search-field',
            '.site-search input',
            'form.search-form input',
            'form[role="search"] input',
            '.header-search input',
            '.search-box input',
            '#search-input',
            '.woocommerce-product-search input'
        ];

        for (let selector of selectors) {
            const $input = $(selector);
            if ($input.length) {
                return $input.first();
            }
        }

        console.error('MKX Live Search: No search input found!');
        return null;
    }

    function forceInit() {
        addDropdownStyles();

        const $input = findSearchInput();
        if (!$input || !$input.length) {
            console.error('MKX Live Search: Cannot initialize - input not found');
            return;
        }

        if ($input.parent().hasClass('mkx-search-wrapper')) {
            // уже инициализировано
            return;
        }

        $input.wrap('<div class="mkx-search-wrapper"></div>');
        const $wrapper = $input.parent('.mkx-search-wrapper');

        const $resultsContainer = $('<div class="mkx-search-results" id="mkx-search-results-dropdown"></div>');
        $wrapper.append($resultsContainer);

        $input.attr({
            'autocomplete': 'off',
            'role': 'combobox',
            'aria-autocomplete': 'list',
            'aria-expanded': 'false'
        });

        $input.on('input', function() {
            const term = $(this).val().trim();
            handleSearch(term, $input, $resultsContainer);
        });

        $input.on('focus', function() {
            const term = $(this).val().trim();
            if (term.length >= 2 && $resultsContainer.children().length) {
                $resultsContainer.addClass('mkx-search-results--visible');
            }
        });

        $(document).on('click', function(e) {
            if (!$(e.target).closest('.mkx-search-wrapper').length) {
                $resultsContainer.removeClass('mkx-search-results--visible');
            }
        });
    }

    let searchTimeout = null;

    function handleSearch(term, $input, $container) {
        clearTimeout(searchTimeout);

        if (term.length < 2) {
            $container.removeClass('mkx-search-results--visible');
            return;
        }

        $input.addClass('mkx-search-loading');
        $container.html('<div class="mkx-search-results__loading">Поиск...</div>');
        $container.addClass('mkx-search-results--visible');

        searchTimeout = setTimeout(function() {
            performSearch(term, $input, $container);
        }, 300);
    }

    function performSearch(term, $input, $container) {
        $.ajax({
            url: mkxLiveSearch.ajaxUrl,
            type: 'POST',
            data: {
                action: 'mkx_live_search',
                nonce: mkxLiveSearch.nonce,
                search: term
            },
            success: function(response) {
                $input.removeClass('mkx-search-loading');
                if (response.success) {
                    renderResults(response.data, term, $container);
                } else {
                    $container.html('<div class="mkx-search-results__empty">Ничего не найдено</div>');
                }
            },
            error: function() {
                $input.removeClass('mkx-search-loading');
                $container.html('<div class="mkx-search-results__empty">Ошибка поиска</div>');
            }
        });
    }

    function renderResults(data, term, $container) {
        let html = '';

        if (data.categories && data.categories.length > 0) {
            html += '<div class="mkx-search-results__categories" style="padding: 12px; border-bottom: 1px solid #f1f5f9;">';
            html += '<h4 style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin: 0 0 8px 0;">Категории</h4>';

            data.categories.forEach(function(category) {
                const url = buildSearchUrl(term, category.slug);
                html += `
<a href="${url}"
   style="display: block; padding: 6px 8px; color: #0f172a; text-decoration: none; border-radius: 4px; font-size: 14px; transition: all 0.2s;"
   onmouseover="this.style.background='#f8fafc'; this.style.color='#f57300';"
   onmouseout="this.style.background='transparent'; this.style.color='#0f172a';">
    ${escapeHtml(category.name)}
</a>`;
            });

            html += '</div>';
        }

        if (data.products && data.products.length > 0) {
            html += '<div class="mkx-search-results__products" style="padding: 12px;">';
            html += '<h4 style="font-size: 11px; font-weight: 600; color: #94a3b8; text-transform: uppercase; margin: 0 0 8px 0;">Товары</h4>';

            data.products.forEach(function(product) {
                html += renderProduct(product);
            });

            html += '</div>';

            const searchUrl = buildSearchUrl(term);
            html += `
<a href="${searchUrl}"
   style="display: block; padding: 12px; text-align: center; color: #f57300; text-decoration: none; font-weight: 500; border-top: 1px solid #f1f5f9;">
    Показать все результаты
</a>`;
        }

        if (html === '') {
            html = '<div class="mkx-search-results__empty" style="padding: 24px; text-align: center; color: #64748b;">Ничего не найдено</div>';
        }

        $container.html(html);
        $container.addClass('mkx-search-results--visible');
    }

    function renderProduct(product) {
        const stockText = product.in_stock
            ? ''
            : '<span style="color: #ef4444; font-size: 11px;">Нет в наличии</span>';

        return `
<a href="${product.url}"
   style="display: flex; gap: 12px; padding: 8px; text-decoration: none; border-radius: 4px; transition: all 0.2s;"
   onmouseover="this.style.background='#f8fafc';"
   onmouseout="this.style.background='transparent';">
    <div style="flex-shrink: 0; width: 60px; height: 60px; border-radius: 4px; overflow: hidden; border: 1px solid #f1f5f9;">
        <img src="${product.image}"
             alt="${escapeHtml(product.title)}"
             style="width: 100%; height: 100%; object-fit: cover;">
    </div>
    <div style="flex: 1; min-width: 0;">
        <h5 style="font-size: 14px; font-weight: 500; color: #0f172a; margin: 0 0 4px 0; overflow: hidden; text-overflow: ellipsis; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical;">
            ${escapeHtml(product.title)}
        </h5>
        <div style="display: flex; align-items: center; gap: 8px; font-size: 12px; color: #94a3b8;">
            ${product.sku ? `<span>Арт: ${escapeHtml(product.sku)}</span>` : ''}
            <span style="font-weight: 600; color: #f57300;">${product.price}</span>
            ${stockText}
        </div>
    </div>
</a>`;
    }

    function buildSearchUrl(term, category) {
        let url = window.location.origin + '/?s=' + encodeURIComponent(term) + '&post_type=product';

        if (category) {
            url += '&product_cat=' + encodeURIComponent(category);
        }

        return url;
    }

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };

        return String(text).replace(/[&<>"']/g, function(m) {
            return map[m];
        });
    }

    $(document).ready(function() {
        forceInit();
    });

    $(window).on('load', function() {
        forceInit();
    });

    setTimeout(function() {
        forceInit();
    }, 1000);

})(jQuery);
