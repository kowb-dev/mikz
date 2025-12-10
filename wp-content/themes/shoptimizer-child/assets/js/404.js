/**
 * 404 Error Page Interactive Features
 * Version: 1.0.0
 * Author: Костя Вебин
 * URI: https://kowb.ru
 */

(function() {
    'use strict';

    // Wait for DOM to be fully loaded
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

    function init() {
        initSearchEnhancements();
        initBrandCardAnimations();
        initKeyboardNavigation();
        trackUserBehavior();
    }

    /**
     * Enhanced search functionality
     */
    function initSearchEnhancements() {
        const searchInput = document.getElementById('mkx-404-search-input');
        if (!searchInput) return;

        // Autofocus on desktop (but not on mobile to avoid keyboard popup)
        if (window.innerWidth >= 768) {
            searchInput.focus();
        }

        // Popular search suggestions
        const suggestions = [
            'дисплей iPhone 13',
            'батарея Samsung Galaxy',
            'камера Xiaomi Redmi',
            'стекло Huawei P40',
            'корпус OPPO',
            'микросхема Apple'
        ];

        let currentSuggestion = 0;
        let suggestionInterval;

        // Rotate placeholder suggestions
        function rotateSuggestions() {
            if (searchInput.value === '' && document.activeElement !== searchInput) {
                searchInput.placeholder = `Например: ${suggestions[currentSuggestion]}`;
                currentSuggestion = (currentSuggestion + 1) % suggestions.length;
            }
        }

        // Start rotation
        suggestionInterval = setInterval(rotateSuggestions, 3000);

        // Clear interval on focus
        searchInput.addEventListener('focus', function() {
            clearInterval(suggestionInterval);
            searchInput.placeholder = 'Введите название запчасти или модель устройства';
        });

        // Resume rotation on blur if empty
        searchInput.addEventListener('blur', function() {
            if (searchInput.value === '') {
                suggestionInterval = setInterval(rotateSuggestions, 3000);
                rotateSuggestions();
            }
        });

        // Form validation
        const searchForm = searchInput.closest('form');
        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const query = searchInput.value.trim();
                
                if (query.length < 2) {
                    e.preventDefault();
                    searchInput.focus();
                    
                    // Visual feedback
                    searchInput.style.borderColor = '#EF5350';
                    setTimeout(() => {
                        searchInput.style.borderColor = '';
                    }, 2000);
                    
                    return false;
                }
            });
        }
    }

    /**
     * Brand card hover animations with performance optimization
     */
    function initBrandCardAnimations() {
        const brandCards = document.querySelectorAll('.mkx-brand-card');
        if (!brandCards.length) return;

        // Intersection Observer for lazy animation
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                    observer.unobserve(entry.target);
                }
            });
        }, {
            threshold: 0.1,
            rootMargin: '50px'
        });

        brandCards.forEach((card, index) => {
            // Initial state
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = `opacity 0.5s ease ${index * 0.1}s, transform 0.5s ease ${index * 0.1}s`;
            
            observer.observe(card);

            // Add ripple effect on click (only on touch devices)
            if ('ontouchstart' in window) {
                card.addEventListener('click', function(e) {
                    const ripple = document.createElement('span');
                    const rect = card.getBoundingClientRect();
                    const size = Math.max(rect.width, rect.height);
                    const x = e.clientX - rect.left - size / 2;
                    const y = e.clientY - rect.top - size / 2;

                    ripple.style.cssText = `
                        position: absolute;
                        width: ${size}px;
                        height: ${size}px;
                        left: ${x}px;
                        top: ${y}px;
                        background: rgba(255, 109, 0, 0.3);
                        border-radius: 50%;
                        transform: scale(0);
                        animation: mkx-ripple 0.6s ease-out;
                        pointer-events: none;
                    `;

                    const inner = card.querySelector('.mkx-brand-card-inner');
                    if (inner) {
                        inner.style.position = 'relative';
                        inner.style.overflow = 'hidden';
                        inner.appendChild(ripple);

                        setTimeout(() => ripple.remove(), 600);
                    }
                });
            }
        });

        // Add ripple animation to document
        if (!document.getElementById('mkx-ripple-animation')) {
            const style = document.createElement('style');
            style.id = 'mkx-ripple-animation';
            style.textContent = `
                @keyframes mkx-ripple {
                    to {
                        transform: scale(2);
                        opacity: 0;
                    }
                }
            `;
            document.head.appendChild(style);
        }
    }

    /**
     * Enhanced keyboard navigation
     */
    function initKeyboardNavigation() {
        const focusableElements = document.querySelectorAll(`
            .mkx-404-btn,
            .mkx-brand-card-link,
            .mkx-404-link-card,
            .mkx-404-search-input,
            .mkx-404-search-btn
        `);

        if (!focusableElements.length) return;

        // Add keyboard shortcut: Press 'S' to focus search
        document.addEventListener('keydown', function(e) {
            // Only if not already in an input
            if (e.key === 's' && 
                e.target.tagName !== 'INPUT' && 
                e.target.tagName !== 'TEXTAREA') {
                
                e.preventDefault();
                const searchInput = document.getElementById('mkx-404-search-input');
                if (searchInput) {
                    searchInput.focus();
                    searchInput.select();
                }
            }

            // Press 'H' to go home
            if (e.key === 'h' && 
                e.target.tagName !== 'INPUT' && 
                e.target.tagName !== 'TEXTAREA') {
                
                const homeBtn = document.querySelector('.mkx-404-btn-primary');
                if (homeBtn) {
                    window.location.href = homeBtn.href;
                }
            }
        });

        // Improve focus visibility on tab navigation
        focusableElements.forEach(el => {
            el.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    if (el.tagName === 'A' || el.tagName === 'BUTTON') {
                        el.style.transform = 'scale(0.95)';
                        setTimeout(() => {
                            el.style.transform = '';
                        }, 150);
                    }
                }
            });
        });
    }

    /**
     * Track user behavior for analytics (privacy-friendly)
     */
    function trackUserBehavior() {
        // Track time on page
        const startTime = Date.now();

        // Track which section user interacts with
        const sections = {
            search: false,
            brands: false,
            quickLinks: false
        };

        // Search interaction
        const searchInput = document.getElementById('mkx-404-search-input');
        if (searchInput) {
            searchInput.addEventListener('focus', function() {
                sections.search = true;
            }, { once: true });
        }

        // Brand cards interaction
        const brandCards = document.querySelectorAll('.mkx-brand-card-link');
        brandCards.forEach(card => {
            card.addEventListener('click', function() {
                sections.brands = true;
            }, { once: true });
        });

        // Quick links interaction
        const quickLinks = document.querySelectorAll('.mkx-404-link-card');
        quickLinks.forEach(link => {
            link.addEventListener('click', function() {
                sections.quickLinks = true;
            }, { once: true });
        });

        // Send analytics on page leave (if analytics are set up)
        window.addEventListener('beforeunload', function() {
            const timeSpent = Math.round((Date.now() - startTime) / 1000);
            
            // Only send if there's a global analytics function
            if (typeof window.gtag !== 'undefined') {
                window.gtag('event', '404_interaction', {
                    time_spent: timeSpent,
                    used_search: sections.search,
                    clicked_brands: sections.brands,
                    clicked_quick_links: sections.quickLinks
                });
            }

            // Yandex Metrica if available
            if (typeof window.ym !== 'undefined') {
                window.ym(window.yaCounterId, 'reachGoal', '404_visited', {
                    timeSpent: timeSpent,
                    interactions: sections
                });
            }
        });
    }

    /**
     * Lazy load brand logos with fade-in effect
     */
    function initLazyLoadImages() {
        const images = document.querySelectorAll('.mkx-brand-logo');
        if (!images.length) return;

        const imageObserver = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    
                    // Add loaded class when image loads
                    if (img.complete) {
                        img.classList.add('loaded');
                    } else {
                        img.addEventListener('load', function() {
                            img.classList.add('loaded');
                        });
                    }
                    
                    imageObserver.unobserve(img);
                }
            });
        }, {
            rootMargin: '100px'
        });

        images.forEach(img => imageObserver.observe(img));
    }

    // Initialize lazy loading
    initLazyLoadImages();

    /**
     * Add subtle parallax effect to hero section
     */
    function initParallaxEffect() {
        const hero = document.querySelector('.mkx-404-hero');
        if (!hero) return;

        // Only on desktop and if user hasn't disabled motion
        const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        if (window.innerWidth < 768 || prefersReducedMotion) return;

        let ticking = false;

        window.addEventListener('scroll', function() {
            if (!ticking) {
                window.requestAnimationFrame(function() {
                    const scrolled = window.pageYOffset;
                    const rate = scrolled * 0.3;
                    
                    hero.style.transform = `translate3d(0, ${rate}px, 0)`;
                    ticking = false;
                });

                ticking = true;
            }
        });
    }

    initParallaxEffect();

})();