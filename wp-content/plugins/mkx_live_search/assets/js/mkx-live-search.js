/**
 * MKX Live Search JavaScript - Fixed Category URLs
 * @package MKX_Live_Search
 * @version 1.0.1
 */

(function($) {
    'use strict';

    const MKXLiveSearch = {
        init: function() {
            this.searchInput = $('.site-search input[type="search"], .search-field');
            this.searchForm = this.searchInput.closest('form');
            this.resultsContainer = null;
            this.debounceTimer = null;
            this.currentRequest = null;

            if (this.searchInput.length) {
                this.setupAutocomplete();
            }

            this.setupCategoryFilters();
        },

        setupAutocomplete: function() {
            const self = this;

            this.searchInput.wrap('<div class="mkx-search-wrapper"></div>');
            this.resultsContainer = $('<div class="mkx-search-results" role="listbox"></div>');
            this.searchInput.parent().append(this.resultsContainer);

            this.searchInput.attr({
                'autocomplete': 'off',
                'role': 'combobox',
                'aria-autocomplete': 'list',
                'aria-expanded': 'false',
                'aria-owns': 'mkx-search-results'
            });

            this.resultsContainer.attr('id', 'mkx-search-results');

            this.searchInput.on('input', function() {
                const term = $(this).val().trim();
                self.handleInput(term);
            });

            this.searchInput.on('focus', function() {
                const term = $(this).val().trim();
                if (term.length >= mkxLiveSearch.minChars) {
                    self.resultsContainer.addClass('mkx-search-results--visible');
                    self.searchInput.attr('aria-expanded', 'true');
                }
            });

            $(document).on('click', function(e) {
                if (!$(e.target).closest('.mkx-search-wrapper').length) {
                    self.hideResults();
                }
            });

            this.searchInput.on('keydown', function(e) {
                self.handleKeyboard(e);
            });
        },

        handleInput: function(term) {
            const self = this;

            clearTimeout(this.debounceTimer);

            if (term.length < mkxLiveSearch.minChars) {
                this.hideResults();
                return;
            }

            this.searchInput.addClass('mkx-search-loading');

            this.debounceTimer = setTimeout(function() {
                const expandedTerm = self.expandSearchTerm(term);
                self.performSearch(expandedTerm);
            }, mkxLiveSearch.delay);
        },

        expandSearchTerm: function(term) {
            
            if (typeof searchCombinations === 'undefined') {
                console.log('searchCombinations is not defined');
                return term;
            }

            const words = term.toLowerCase().split(' ');
            const expandedWords = words.map(word => {
                for (const category in searchCombinations) {
                    if (typeof searchCombinations[category] === 'object' && !Array.isArray(searchCombinations[category])) {
                        for (const subCategory in searchCombinations[category]) {
                            if (searchCombinations[category][subCategory].includes(word)) {
                                return searchCombinations[category][subCategory][0];
                            }
                        }
                    } else if (Array.isArray(searchCombinations[category])) {
                        if (searchCombinations[category].includes(word)) {
                            return searchCombinations[category][0];
                        }
                    }
                }
                return word;
            });
            
            const expandedTerm = expandedWords.join(' ');
            console.log('Expanded term:', expandedTerm);
            return expandedTerm;
        },

        performSearch: function(term) {
            const self = this;

            if (this.currentRequest) {
                this.currentRequest.abort();
            }

            this.currentRequest = $.ajax({
                url: mkxLiveSearch.ajaxUrl,
                type: 'POST',
                data: {
                    action: 'mkx_live_search',
                    nonce: mkxLiveSearch.nonce,
                    search: term
                },
                success: function(response) {
                    self.searchInput.removeClass('mkx-search-loading');
                    
                    if (response.success) {
                        self.renderResults(response.data, term);
                    } else {
                        self.showError(response.data.message);
                    }
                },
                error: function(xhr) {
                    if (xhr.statusText !== 'abort') {
                        self.searchInput.removeClass('mkx-search-loading');
                        self.showError(mkxLiveSearch.strings.noResults);
                    }
                }
            });
        },

        renderResults: function(data, term) {
            const self = this;
            let html = '';

            if (data.categories && data.categories.length > 0) {
                html += '<div class="mkx-search-results__categories">';
                html += '<h4 class="mkx-search-results__categories-title">Категории</h4>';
                
                data.categories.forEach(function(category) {
                    const categoryUrl = self.buildSearchUrl(term, category.slug);
                    html += `<a href="${categoryUrl}" class="mkx-search-results__category-item">${self.escapeHtml(category.name)}</a>`;
                });
                
                html += '</div>';
            }

            if (data.products && data.products.length > 0) {
                html += '<div class="mkx-search-results__products">';
                html += '<h4 class="mkx-search-results__products-title">Товары</h4>';
                
                data.products.forEach(function(product) {
                    html += self.renderProduct(product, term);
                });
                
                html += '</div>';

                const searchUrl = self.buildSearchUrl(term);
                html += `<a href="${searchUrl}" class="mkx-search-results__view-all">Показать все результаты</a>`;
            }

            if (html === '') {
                html = `<div class="mkx-search-results__empty">${mkxLiveSearch.strings.noResults}</div>`;
            }

            this.resultsContainer.html(html);
            this.showResults();
        },

        renderProduct: function(product, term) {
            const stockClass = product.in_stock ? '' : 'out-of-stock';
            const stockText = product.in_stock ? '' : '<span class="stock-status">Нет в наличии</span>';
            
            const highlightedTitle = this.highlightTerm(product.title, term);

            return `
                <a href="${product.url}" class="mkx-search-results__product-item ${stockClass}">
                    <div class="mkx-search-results__product-image">
                        <img src="${product.image}" alt="${this.escapeHtml(product.title)}" loading="lazy" width="64" height="64">
                    </div>
                    <div class="mkx-search-results__product-content">
                        <h5 class="mkx-search-results__product-title">${highlightedTitle}</h5>
                        <div class="mkx-search-results__product-meta">
                            ${product.sku ? `<span class="sku">Арт: ${this.escapeHtml(product.sku)}</span>` : ''}
                            <span class="mkx-search-results__product-price">${product.price}</span>
                            ${stockText}
                        </div>
                    </div>
                </a>
            `;
        },

        highlightTerm: function(text, term) {
            if (!term) return this.escapeHtml(text);
            
            const escapedText = this.escapeHtml(text);
            const regex = new RegExp('(' + this.escapeRegex(term) + ')', 'gi');
            return escapedText.replace(regex, '<mark>$1</mark>');
        },

        buildSearchUrl: function(term, category) {
            let url = window.location.origin + '/?s=' + encodeURIComponent(term) + '&post_type=product';
            
            if (category) {
                url += '&product_cat=' + encodeURIComponent(category);
            }
            
            return url;
        },

        showResults: function() {
            this.resultsContainer.addClass('mkx-search-results--visible');
            this.searchInput.attr('aria-expanded', 'true');
        },

        hideResults: function() {
            this.resultsContainer.removeClass('mkx-search-results--visible');
            this.searchInput.attr('aria-expanded', 'false');
        },

        showError: function(message) {
            this.resultsContainer.html(`<div class="mkx-search-results__empty">${this.escapeHtml(message)}</div>`);
            this.showResults();
        },

        handleKeyboard: function(e) {
            const items = this.resultsContainer.find('a');
            const currentIndex = items.index(document.activeElement);

            switch(e.keyCode) {
                case 27:
                    this.hideResults();
                    this.searchInput.blur();
                    break;
                
                case 38:
                    e.preventDefault();
                    if (currentIndex > 0) {
                        items.eq(currentIndex - 1).focus();
                    } else {
                        this.searchInput.focus();
                    }
                    break;
                
                case 40:
                    e.preventDefault();
                    if (currentIndex < items.length - 1) {
                        items.eq(currentIndex + 1).focus();
                    } else if (currentIndex === -1 && items.length > 0) {
                        items.eq(0).focus();
                    }
                    break;
            }
        },

        setupCategoryFilters: function() {
            const self = this;

            $(document).on('click', '.mkx-search-tag', function(e) {
                e.preventDefault();
                
                const $tag = $(this);
                const category = $tag.data('category');
                const searchTerm = new URLSearchParams(window.location.search).get('s');

                if (!searchTerm) return;

                $('.mkx-search-tag').removeClass('mkx-search-tag--active');
                $tag.addClass('mkx-search-tag--active');

                // Перенаправляем на правильный URL вместо AJAX
                const newUrl = self.buildSearchUrl(searchTerm, category);
                window.location.href = newUrl;
            });
        },

        escapeHtml: function(text) {
            const map = {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#039;'
            };
            return String(text).replace(/[&<>"']/g, function(m) { return map[m]; });
        },

        escapeRegex: function(text) {
            return text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        }
    };

    $(document).ready(function() {
        MKXLiveSearch.init();
    });

})(jQuery);