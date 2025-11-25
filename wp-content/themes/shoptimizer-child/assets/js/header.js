/**
 * Header JavaScript for Shoptimizer Child Theme
 * Author: KB
 * URL: https://kowb.ru
 * Version: 1.1.0 - Fixed Cursor and Overlay Hover Issues
 */

(function() {
    'use strict';

    /**
     * Header functionality class
     */
    class HeaderManager {
        constructor() {
            this.state = {
                isDesktop: window.innerWidth > 768,
                isMegamenuOpen: false,
                isMobileMenuOpen: false,
                listenersAttached: false,
                hoverTimeout: null,
                resizeTimeout: null,
                catalogInHistory: false,
                bodyScrollTop: undefined,
                isHoveringCatalogArea: false
            };

            this.elements = {};
            this.handlers = {};

            this.init();
        }

        /**
         * Initialize header functionality
         */
        init() {
            this.cacheElements();
            this.bindHandlers();
            this.setupInitialState();
            this.attachEventListeners();
            this.setupHistoryAPI();

            setTimeout(() => {
                this.initializeMobileCatalogDelayed();
            }, 100);
        }

        /**
         * Setup History API for mobile catalog
         */
        setupHistoryAPI() {
            window.addEventListener('popstate', (event) => {
                if (event.state && event.state.catalogOpen) {
                    if (!this.state.isMegamenuOpen) {
                        this.showMegamenu(false);
                    }
                } else {
                    if (this.state.isMegamenuOpen) {
                        this.closeMegamenu(false);
                    }
                    if (this.state.isMobileMenuOpen) {
                        this.closeMobileMenu(false);
                    }
                }
            });
        }

        /**
         * Initialize mobile catalog with delay
         */
        initializeMobileCatalogDelayed() {
            this.elements.mobileCatalogToggle = document.getElementById('mobileCatalogToggle');

            if (!this.elements.mobileCatalogToggle) {
                this.elements.mobileCatalogToggle = document.querySelector('.mkx-mobile-nav-item--catalog');
            }

            if (this.elements.mobileCatalogToggle) {
                this.elements.mobileCatalogToggle.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    if (!this.state.isDesktop) {
                        this.toggleMobileMenu();
                    }
                });
            } else {
                setTimeout(() => {
                    this.tryFindMobileCatalogAgain();
                }, 500);
            }
        }

        /**
         * Last attempt to find mobile catalog button
         */
        tryFindMobileCatalogAgain() {
            const mobileCatalog = document.getElementById('mobileCatalogToggle') ||
                document.querySelector('.mkx-mobile-nav-item--catalog');

            if (mobileCatalog) {
                this.elements.mobileCatalogToggle = mobileCatalog;
                mobileCatalog.addEventListener('click', (event) => {
                    event.preventDefault();
                    event.stopPropagation();

                    if (!this.state.isDesktop) {
                        this.toggleMobileMenu();
                    }
                });
            } else {
                const catalogButtons = document.querySelectorAll('button, a');
                catalogButtons.forEach(btn => {
                    const text = btn.textContent.toLowerCase();
                    if (text.includes('каталог') && btn.closest('.mkx-mobile-bottom-nav')) {
                        this.elements.mobileCatalogToggle = btn;
                        btn.addEventListener('click', (event) => {
                            event.preventDefault();
                            event.stopPropagation();

                            if (!this.state.isDesktop) {
                                this.toggleMobileMenu();
                            }
                        });
                    }
                });
            }
        }

        /**
         * Cache DOM elements
         */
        cacheElements() {
            this.elements = {
                siteHeader: document.querySelector('.mkx-site-header'),
                headerTopBar: document.querySelector('.mkx-header-top-bar'),
                catalogButton: document.querySelector('.mkx-catalog-button'),
                catalogToggle: document.getElementById('catalogToggle'),
                catalogMegamenu: document.getElementById('catalogMegamenu'),
                catalogMegamenuClose: document.getElementById('catalogMegamenuClose'),
                overlay: document.getElementById('megamenuOverlay'),
                mobileMenuToggle: document.querySelector('.mkx-mobile-menu-toggle'),
                mobileMenu: document.querySelector('.mkx-mobile-menu'),
                mobileMenuClose: document.getElementById('mobileMenuClose'),
                mainNavigation: document.querySelector('.mkx-main-navigation'),
                primaryMenu: document.querySelector('.mkx-primary-menu'),
                body: document.body
            };
        }

        /**
         * Bind event handlers to maintain context
         */
        bindHandlers() {
            this.handlers = {
                catalogButtonEnter: this.handleCatalogButtonEnter.bind(this),
                catalogButtonLeave: this.handleCatalogButtonLeave.bind(this),
                catalogMegamenuEnter: this.handleCatalogMegamenuEnter.bind(this),
                catalogMegamenuLeave: this.handleCatalogMegamenuLeave.bind(this),
                catalogToggleClick: this.handleCatalogToggleClick.bind(this),
                catalogMegamenuCloseClick: this.handleCatalogMegamenuCloseClick.bind(this),
                overlayClick: this.handleOverlayClick.bind(this),
                mobileMenuToggleClick: this.handleMobileMenuToggleClick.bind(this),
                mobileMenuCloseClick: this.handleMobileMenuCloseClick.bind(this),
                documentClick: this.handleDocumentClick.bind(this),
                windowResize: this.handleWindowResize.bind(this)
            };
        }

        /**
         * Setup initial state
         */
        setupInitialState() {
            this.updateScreenSize();
            this.initializeMobileNav();
        }

        /**
         * Attach appropriate event listeners based on screen size
         */
        attachEventListeners() {
            this.removeAllListeners();

            if (this.state.isDesktop) {
                this.attachDesktopListeners();
            } else {
                this.attachMobileListeners();
            }

            if (this.elements.catalogMegamenuClose) {
                this.elements.catalogMegamenuClose.addEventListener('click', this.handlers.catalogMegamenuCloseClick);
            }

            if (this.elements.mobileMenuClose) {
                this.elements.mobileMenuClose.addEventListener('click', this.handlers.mobileMenuCloseClick);
            }

            window.addEventListener('resize', this.handlers.windowResize);

            this.state.listenersAttached = true;
        }

        /**
         * Attach desktop-specific event listeners
         */
        attachDesktopListeners() {
            if (!this.elements.catalogButton || !this.elements.catalogMegamenu) return;

            // Attach to catalog button
            this.elements.catalogButton.addEventListener('mouseenter', this.handlers.catalogButtonEnter);
            this.elements.catalogButton.addEventListener('mouseleave', this.handlers.catalogButtonLeave);

            // Add click listener to catalogToggle for redirection to /shop on desktop
            if (this.elements.catalogToggle) {
                this.elements.catalogToggle.addEventListener('click', (event) => {
                    event.preventDefault(); // Prevent default button behavior
                    window.location.href = '/shop'; // Redirect to /shop page
                });
            }

            // Attach to megamenu
            this.elements.catalogMegamenu.addEventListener('mouseenter', this.handlers.catalogMegamenuEnter);
            this.elements.catalogMegamenu.addEventListener('mouseleave', this.handlers.catalogMegamenuLeave);

            // Attach to overlay for click only (hover handled separately)
            if (this.elements.overlay) {
                this.elements.overlay.addEventListener('click', this.handlers.overlayClick);
            }
        }

        /**
         * Attach mobile-specific event listeners
         */
        attachMobileListeners() {
            if (this.elements.catalogToggle) {
                this.elements.catalogToggle.addEventListener('click', this.handlers.catalogToggleClick);
            }

            if (this.elements.mobileMenuToggle) {
                this.elements.mobileMenuToggle.addEventListener('click', this.handlers.mobileMenuToggleClick);
            }

            document.addEventListener('click', this.handlers.documentClick);
        }

        /**
         * Remove all event listeners
         */
        removeAllListeners() {
            if (!this.state.listenersAttached) return;

            if (this.elements.catalogButton) {
                this.elements.catalogButton.removeEventListener('mouseenter', this.handlers.catalogButtonEnter);
                this.elements.catalogButton.removeEventListener('mouseleave', this.handlers.catalogButtonLeave);
            }

            if (this.elements.catalogMegamenu) {
                this.elements.catalogMegamenu.removeEventListener('mouseenter', this.handlers.catalogMegamenuEnter);
                this.elements.catalogMegamenu.removeEventListener('mouseleave', this.handlers.catalogMegamenuLeave);
            }

            if (this.elements.overlay) {
                this.elements.overlay.removeEventListener('click', this.handlers.overlayClick);
            }

            if (this.elements.catalogToggle) {
                this.elements.catalogToggle.removeEventListener('click', this.handlers.catalogToggleClick);
            }

            if (this.elements.catalogMegamenuClose) {
                this.elements.catalogMegamenuClose.removeEventListener('click', this.handlers.catalogMegamenuCloseClick);
            }

            if (this.elements.mobileMenuToggle) {
                this.elements.mobileMenuToggle.removeEventListener('click', this.handlers.mobileMenuToggleClick);
            }

            if (this.elements.mobileMenuClose) {
                this.elements.mobileMenuClose.removeEventListener('click', this.handlers.mobileMenuCloseClick);
            }

            document.removeEventListener('click', this.handlers.documentClick);

            this.state.listenersAttached = false;
        }

        /**
         * Handle catalog button mouse enter
         */
        handleCatalogButtonEnter() {
            this.clearHoverTimeout();
            this.state.isHoveringCatalogArea = true;
            this.showMegamenu();
        }

        /**
         * Handle catalog button mouse leave
         */
        handleCatalogButtonLeave(event) {
            // Simple check: if not moving to megamenu area, start hide timer
            if (!this.isMovingToActiveArea(event.relatedTarget)) {
                this.state.isHoveringCatalogArea = false;
                this.hideMegamenuWithDelay();
            }
        }

        /**
         * Handle catalog megamenu mouse enter
         */
        handleCatalogMegamenuEnter() {
            this.clearHoverTimeout();
            this.state.isHoveringCatalogArea = true;
        }

        /**
         * Handle catalog megamenu mouse leave
         */
        handleCatalogMegamenuLeave(event) {
            // Simple check: if not moving to active area, start hide timer
            if (!this.isMovingToActiveArea(event.relatedTarget)) {
                this.state.isHoveringCatalogArea = false;
                this.hideMegamenuWithDelay();
            }
        }

        /**
         * Handle overlay mouse enter - only when megamenu is open
         */
        handleOverlayEnter() {
            if (this.state.isMegamenuOpen) {
                this.clearHoverTimeout();
                this.state.isHoveringCatalogArea = true;
            }
        }

        /**
         * Handle overlay mouse leave - only hide if leaving entire active area
         */
        handleOverlayLeave(event) {
            if (this.state.isMegamenuOpen && !this.isMovingToActiveArea(event.relatedTarget)) {
                this.state.isHoveringCatalogArea = false;
                this.hideMegamenuWithDelay();
            }
        }

        /**
         * Check if mouse is moving to active area (button or megamenu)
         */
        isMovingToActiveArea(target) {
            if (!target) return false;

            return (
                this.elements.catalogButton?.contains(target) ||
                target === this.elements.catalogButton ||
                this.elements.catalogMegamenu?.contains(target) ||
                target === this.elements.catalogMegamenu ||
                target.closest('.mkx-catalog-button') ||
                target.closest('.mkx-catalog-megamenu')
            );
        }

        /**
         * Handle catalog toggle click (mobile)
         */
        handleCatalogToggleClick(event) {
            event.preventDefault();
            event.stopPropagation();
            this.toggleMegamenu();
        }

        /**
         * Handle catalog megamenu close button click
         */
        handleCatalogMegamenuCloseClick(event) {
            event.preventDefault();
            event.stopPropagation();
            this.closeMegamenu();
        }

        /**
         * Handle mobile menu close button click
         */
        handleMobileMenuCloseClick(event) {
            event.preventDefault();
            event.stopPropagation();
            this.closeMobileMenu();
        }

        /**
         * Handle mobile menu toggle click
         */
        handleMobileMenuToggleClick(event) {
            event.preventDefault();
            event.stopPropagation();
            this.toggleMobileMenu();
        }

        /**
         * Handle overlay click
         */
        handleOverlayClick() {
            if (this.state.isDesktop) {
                this.closeMegamenu();
                this.closeMobileMenu();
            }
        }

        /**
         * Handle document click (for closing menus)
         */
        handleDocumentClick(event) {
            if (this.state.isDesktop && this.state.isMegamenuOpen &&
                !this.elements.catalogButton?.contains(event.target) &&
                !this.elements.catalogMegamenu?.contains(event.target)) {
                this.closeMegamenu();
            }
        }

        /**
         * Handle window resize with debouncing
         */
        handleWindowResize() {
            clearTimeout(this.state.resizeTimeout);
            this.state.resizeTimeout = setTimeout(() => {
                const wasDesktop = this.state.isDesktop;
                this.updateScreenSize();

                if (wasDesktop !== this.state.isDesktop) {
                    this.closeMegamenu();
                    this.closeMobileMenu();
                    this.attachEventListeners();
                }
            }, 150);
        }

        /**
         * Update screen size state
         */
        updateScreenSize() {
            this.state.isDesktop = window.innerWidth > 768;
        }

        /**
         * Show megamenu
         */
        showMegamenu(addToHistory = true) {
            if (!this.elements.catalogMegamenu || this.state.isMegamenuOpen) return;

            this.elements.catalogMegamenu.style.display = 'block';

            if (!this.state.isDesktop) {
                this.state.bodyScrollTop = window.pageYOffset || document.documentElement.scrollTop;

                document.body.style.position = 'fixed';
                document.body.style.top = `-${this.state.bodyScrollTop}px`;
                document.body.style.width = '100%';

                this.elements.catalogMegamenu.scrollTop = 0;

                if (addToHistory && !this.state.catalogInHistory) {
                    history.pushState({ catalogOpen: true }, '', window.location.href);
                    this.state.catalogInHistory = true;
                }
            }

            requestAnimationFrame(() => {
                this.elements.catalogMegamenu.classList.add('mkx-catalog-megamenu--active');
                this.elements.body.classList.add('mkx-megamenu-open');

                if (this.state.isDesktop && this.elements.overlay) {
                    this.elements.overlay.classList.add('mkx-megamenu-overlay--active');
                }

                this.state.isMegamenuOpen = true;

                const content = this.elements.catalogMegamenu.querySelector('.mkx-catalog-megamenu__content');
                if (content) {
                    content.style.opacity = '1';
                    content.style.visibility = 'visible';
                }
            });
        }

        /**
         * Hide megamenu with delay
         */
        hideMegamenuWithDelay() {
            this.clearHoverTimeout();

            this.state.hoverTimeout = setTimeout(() => {
                if (!this.state.isHoveringCatalogArea) {
                    this.closeMegamenu();
                }
            }, 200);
        }

        /**
         * Close megamenu immediately
         */
        closeMegamenu(updateHistory = true) {
            this.clearHoverTimeout();

            if (!this.elements.catalogMegamenu || !this.state.isMegamenuOpen) return;

            this.state.isHoveringCatalogArea = false;
            this.elements.catalogMegamenu.classList.remove('mkx-catalog-megamenu--active');
            this.elements.body.classList.remove('mkx-megamenu-open');
            this.state.isMegamenuOpen = false;

            if (this.state.isDesktop && this.elements.overlay) {
                this.elements.overlay.classList.remove('mkx-megamenu-overlay--active');
            }

            if (!this.state.isDesktop && this.state.bodyScrollTop !== undefined) {
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                window.scrollTo(0, this.state.bodyScrollTop);
                this.state.bodyScrollTop = undefined;
            }

            if (!this.state.isDesktop && updateHistory && this.state.catalogInHistory) {
                history.back();
                this.state.catalogInHistory = false;
            }

            setTimeout(() => {
                if (!this.state.isMegamenuOpen) {
                    this.elements.catalogMegamenu.style.display = '';
                }
            }, 300);
        }

        /**
         * Toggle megamenu (mobile)
         */
        toggleMegamenu() {
            if (this.state.isMegamenuOpen) {
                this.closeMegamenu();
            } else {
                this.showMegamenu();
            }
        }

        /**
         * Show mobile menu
         */
        showMobileMenu(addToHistory = true) {
            if (!this.elements.mobileMenu) return;

            this.state.bodyScrollTop = window.pageYOffset || document.documentElement.scrollTop;
            document.body.style.position = 'fixed';
            document.body.style.top = `-${this.state.bodyScrollTop}px`;
            document.body.style.width = '100%';

            if (addToHistory && !this.state.catalogInHistory) {
                history.pushState({ mobileMenuOpen: true }, '', window.location.href);
                this.state.catalogInHistory = true;
            }

            this.elements.mobileMenu.classList.add('mkx-mobile-menu--active');
            this.elements.body.classList.add('mkx-mobile-menu-open');
            this.state.isMobileMenuOpen = true;
        }

        /**
         * Close mobile menu
         */
        closeMobileMenu(updateHistory = true) {
            if (!this.elements.mobileMenu) return;

            this.elements.mobileMenu.classList.remove('mkx-mobile-menu--active');
            this.elements.body.classList.remove('mkx-mobile-menu-open');
            this.state.isMobileMenuOpen = false;

            if (this.state.bodyScrollTop !== undefined) {
                document.body.style.position = '';
                document.body.style.top = '';
                document.body.style.width = '';
                window.scrollTo(0, this.state.bodyScrollTop);
                this.state.bodyScrollTop = undefined;
            }

            if (updateHistory && this.state.catalogInHistory) {
                history.back();
                this.state.catalogInHistory = false;
            }
        }

        /**
         * Toggle mobile menu
         */
        toggleMobileMenu() {
            if (this.state.isMobileMenuOpen) {
                this.closeMobileMenu();
            } else {
                this.showMobileMenu();
            }
        }

        /**
         * Clear hover timeout
         */
        clearHoverTimeout() {
            if (this.state.hoverTimeout) {
                clearTimeout(this.state.hoverTimeout);
                this.state.hoverTimeout = null;
            }
        }

        /**
         * Initialize mobile navigation
         */
        initializeMobileNav() {
            if (this.state.isDesktop) return;

            this.elements.body.classList.add('mkx-mobile-nav-active');
            this.setActiveNavItem();
        }

        /**
         * Set active navigation item
         */
        setActiveNavItem() {
            const currentUrl = window.location.pathname;
            const navItems = document.querySelectorAll('.mkx-mobile-nav-item');

            navItems.forEach(item => {
                const href = item.getAttribute('href');
                if (href && currentUrl.includes(href)) {
                    item.classList.add('mkx-mobile-nav-item--active');
                } else {
                    item.classList.remove('mkx-mobile-nav-item--active');
                }
            });
        }

        /**
         * Cleanup method
         */
        destroy() {
            this.removeAllListeners();
            window.removeEventListener('resize', this.handlers.windowResize);

            if (this.elements.mobileCatalogToggle) {
                this.elements.mobileCatalogToggle.removeEventListener('click', this.handlers.mobileCatalogToggleClick);
            }

            if (this.state.resizeTimeout) {
                clearTimeout(this.state.resizeTimeout);
            }

            this.clearHoverTimeout();
        }
    }

    /**
     * Mobile menu submenu toggle functionality
     */
    class MobileSubmenuManager {
        constructor() {
            this.init();
        }

        init() {
            const submenuToggles = document.querySelectorAll('.mkx-mobile-menu__toggle');

            submenuToggles.forEach(toggle => {
                toggle.addEventListener('click', this.handleToggleClick.bind(this));
            });
        }

        handleToggleClick(event) {
            event.preventDefault();
            const toggle = event.currentTarget; // Use currentTarget to ensure we get the button
            const submenu = toggle.nextElementSibling;

            if (submenu && submenu.classList.contains('mkx-mobile-submenu')) {
                const isExpanded = toggle.classList.toggle('mkx-mobile-menu__toggle--active');
                submenu.classList.toggle('mkx-mobile-submenu--active');
                toggle.setAttribute('aria-expanded', isExpanded);
            }
        }

        // The closeAllSubmenus function is no longer needed for this behavior.
    }

    /**
     * Search functionality enhancements
     */
    class SearchManager {
        constructor() {
            this.init();
        }

        init() {
            const searchForms = document.querySelectorAll('.mkx-woocommerce-product-search');

            searchForms.forEach(form => {
                const searchField = form.querySelector('.mkx-search-field');
                const submitButton = form.querySelector('.mkx-search-submit');

                if (searchField && submitButton) {
                    form.addEventListener('submit', this.handleFormSubmit.bind(this));
                    searchField.addEventListener('input', this.handleSearchInput.bind(this));
                }
            });
        }

        handleFormSubmit(event) {
            const form = event.target;
            const searchField = form.querySelector('.mkx-search-field');
            const submitButton = form.querySelector('.mkx-search-submit');

            if (searchField && searchField.value.trim() === '') {
                event.preventDefault();
                searchField.focus();
                return false;
            }

            if (submitButton) {
                submitButton.classList.add('loading');
            }
        }

        handleSearchInput(event) {
            const searchField = event.target;
            const form = searchField.closest('form');
            const submitButton = form?.querySelector('.mkx-search-submit');

            if (submitButton) {
                submitButton.classList.remove('loading');
            }
        }
    }

    /**
     * Header scroll behavior class
     */
    class HeaderScrollManager {
        constructor() {
            this.state = {
                lastScrollTop: 0,
                isFixed: false,
                isVisible: false,
                ticking: false,
                scrollThreshold: 150,
                scrollOffset: 5,
                isDesktop: window.innerWidth > 768
            };

            this.elements = {};
            this.resizeTimeout = null;

            this.init();
        }

        init() {
            setTimeout(() => {
                this.cacheElements();
                this.attachEventListeners();
                this.handleScroll();
            }, 100);
        }

        /**
         * Cache DOM elements
         */
        cacheElements() {
            this.elements = {
                siteHeader: document.querySelector('.mkx-site-header'),
                headerTopBar: document.querySelector('.mkx-header-top-bar')
            };
        }

        /**
         * Attach event listeners
         */
        attachEventListeners() {
            if (!this.elements.siteHeader) {
                return;
            }

            window.addEventListener('scroll', this.onScroll.bind(this), { passive: true });
            window.addEventListener('resize', this.onResize.bind(this), { passive: true });
        }

        /**
         * Throttled scroll handler using requestAnimationFrame
         */
        onScroll() {
            if (!this.state.ticking) {
                requestAnimationFrame(() => {
                    this.handleScroll();
                    this.state.ticking = false;
                });
                this.state.ticking = true;
            }
        }

        /**
         * Handle scroll events
         */
        handleScroll() {
            const scrollTop = window.pageYOffset || document.documentElement.scrollTop;
            const scrollDirection = scrollTop > this.state.lastScrollTop ? 'down' : 'up';
            const scrollDistance = Math.abs(scrollTop - this.state.lastScrollTop);

            if (scrollDistance < this.state.scrollOffset) {
                return;
            }

            const shouldBeFixed = scrollTop > this.state.scrollThreshold;
            const shouldBeVisible = scrollDirection === 'up' && shouldBeFixed;

            this.updateHeaderState(shouldBeFixed, shouldBeVisible);

            this.state.lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
        }

        /**
         * Update header visual state
         */
        updateHeaderState(fixed, visible) {
            if (this.state.isDesktop) {
                this.updateDesktopHeader(fixed, visible);
            } else {
                this.updateMobileHeader(fixed, visible);
            }
        }

        /**
         * Update desktop header (only top-bar)
         */
        updateDesktopHeader(fixed, visible) {
            const targetElement = this.elements.headerTopBar;
            if (!targetElement) return;

            if (fixed && !this.state.isFixed) {
                targetElement.classList.add('mkx-header-fixed');
                this.state.isFixed = true;
                this.state.isVisible = false;

                const headerHeight = targetElement.offsetHeight;
                document.body.style.paddingTop = headerHeight + 'px';
            } else if (!fixed && this.state.isFixed) {
                targetElement.classList.remove('mkx-header-fixed', 'mkx-header-visible');
                this.state.isFixed = false;
                this.state.isVisible = false;
                document.body.style.paddingTop = '';
            }

            if (this.state.isFixed) {
                if (visible && !this.state.isVisible) {
                    targetElement.classList.add('mkx-header-visible');
                    this.state.isVisible = true;
                } else if (!visible && this.state.isVisible) {
                    targetElement.classList.remove('mkx-header-visible');
                    this.state.isVisible = false;
                }
            }
        }

        /**
         * Update mobile header (entire header)
         */
        updateMobileHeader(fixed, visible) {
            const targetElement = this.elements.siteHeader;
            if (!targetElement) return;

            if (fixed && !this.state.isFixed) {
                targetElement.classList.add('mkx-header-fixed');
                this.state.isFixed = true;
                this.state.isVisible = false;

                const headerHeight = targetElement.offsetHeight;
                document.body.style.paddingTop = headerHeight + 'px';
            } else if (!fixed && this.state.isFixed) {
                targetElement.classList.remove('mkx-header-fixed', 'mkx-header-visible');
                this.state.isFixed = false;
                this.state.isVisible = false;
                document.body.style.paddingTop = '';
            }

            if (this.state.isFixed) {
                if (visible && !this.state.isVisible) {
                    targetElement.classList.add('mkx-header-visible');
                    this.state.isVisible = true;
                } else if (!visible && this.state.isVisible) {
                    targetElement.classList.remove('mkx-header-visible');
                    this.state.isVisible = false;
                }
            }
        }

        /**
         * Handle window resize
         */
        onResize() {
            clearTimeout(this.resizeTimeout);
            this.resizeTimeout = setTimeout(() => {
                const wasDesktop = this.state.isDesktop;
                this.state.isDesktop = window.innerWidth > 768;

                if (wasDesktop !== this.state.isDesktop) {
                    this.resetHeaderState();
                    this.cacheElements();
                    setTimeout(() => this.handleScroll(), 50);
                }

                if (this.state.isFixed) {
                    const targetElement = this.state.isDesktop ?
                        this.elements.headerTopBar :
                        this.elements.siteHeader;

                    if (targetElement) {
                        document.body.style.paddingTop = targetElement.offsetHeight + 'px';
                    }
                }
            }, 150);
        }

        /**
         * Reset header state
         */
        resetHeaderState() {
            if (this.elements.siteHeader) {
                this.elements.siteHeader.classList.remove('mkx-header-fixed', 'mkx-header-visible');
            }

            if (this.elements.headerTopBar) {
                this.elements.headerTopBar.classList.remove('mkx-header-fixed', 'mkx-header-visible');
            }

            this.state.isFixed = false;
            this.state.isVisible = false;
            this.state.lastScrollTop = 0;
            document.body.style.paddingTop = '';
        }

        /**
         * Cleanup function
         */
        destroy() {
            window.removeEventListener('scroll', this.onScroll);
            window.removeEventListener('resize', this.onResize);

            this.resetHeaderState();

            if (this.resizeTimeout) {
                clearTimeout(this.resizeTimeout);
            }
        }
    }

    /**
     * Initialize all functionality when DOM is ready
     */
    function initializeHeader() {
        window.headerManager = new HeaderManager();
        window.headerScrollManager = new HeaderScrollManager();
        new MobileSubmenuManager();
        new SearchManager();

        if (typeof wc_add_to_cart_params !== 'undefined') {
            initializeCartUpdates();
        }
    }

    /**
     * Initialize cart counter updates
     */
    function initializeCartUpdates() {
        updateCartCount();

        document.body.addEventListener('wc_fragments_refreshed', updateCartCount);
        document.body.addEventListener('added_to_cart', updateCartCount);
        document.body.addEventListener('removed_from_cart', updateCartCount);
    }

    /**
     * Update cart count in navigation
     */
    function updateCartCount() {
        const cartCountElements = document.querySelectorAll('.mkx-cart-count, .mkx-mobile-nav-cart-count');

        cartCountElements.forEach(element => {
            element.style.animation = 'none';
            element.offsetHeight;
            element.style.animation = 'cartBounce 0.3s var(--mkx-ease)';
        });
    }

    /**
     * Initialize when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initializeHeader);
    } else {
        setTimeout(initializeHeader, 50);
    }

    /**
     * Cleanup on page unload
     */
    window.addEventListener('beforeunload', function() {
        if (window.headerManager) {
            window.headerManager.destroy();
        }

        if (window.headerScrollManager) {
            window.headerScrollManager.destroy();
        }
    });

})();