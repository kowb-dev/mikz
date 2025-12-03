/**
 * Hero Carousel JavaScript with Enhanced Fade Animation - Fixed Version
 *
 * @package Shoptimizer Child
 * @version 1.0.3
 * @author KW
 * @link https://kowb.ru
 */

(function() {
    'use strict';

    /**
     * Enhanced Carousel Class with Fade Animation - Fixed
     */
    class MKXCarousel {
        constructor(container, options = {}) {
            this.container = container;
            this.track = container.querySelector('.mkx-carousel-track');
            this.slides = container.querySelectorAll('.mkx-carousel-slide');
            this.indicators = container.querySelectorAll('.mkx-carousel-indicator');
            this.prevBtn = container.querySelector('.mkx-carousel-nav--prev');
            this.nextBtn = container.querySelector('.mkx-carousel-nav--next');

            // Enhanced options
            this.options = {
                autoplay: container.dataset.autoplay === 'true',
                interval: parseInt(container.dataset.interval) || 6000,
                pauseOnHover: true,
                enableSwipe: true,
                fadeSpeed: 800,
                enableKenBurns: true,
                debug: false, // Enable for debugging
                ...options
            };

            // Current state - simplified
            this.currentSlide = this.findInitialActiveSlide();
            this.totalSlides = this.slides.length;
            this.isPlaying = this.options.autoplay;
            this.autoplayTimer = null;
            this.isTransitioning = false;

            // Touch/swipe properties
            this.touchStartX = 0;
            this.touchEndX = 0;
            this.minSwipeDistance = 50;

            // Debug info
            this.debugLog('Initializing carousel', {
                totalSlides: this.totalSlides,
                currentSlide: this.currentSlide,
                autoplay: this.options.autoplay
            });

            this.init();
        }

        /**
         * Debug logging
         */
        debugLog(message, data = null) {
            if (this.options.debug) {
                console.log(`[MKXCarousel] ${message}`, data || '');
            }
        }

        /**
         * Find initial active slide
         */
        findInitialActiveSlide() {
            const activeSlide = this.container.querySelector('.mkx-carousel-slide--active');
            if (activeSlide) {
                return Array.from(this.slides).indexOf(activeSlide);
            }
            return 0; // Default to first slide
        }

        /**
         * Initialize carousel
         */
        init() {
            if (this.totalSlides <= 1) {
                this.debugLog('Not enough slides, skipping initialization');
                return;
            }

            // Ensure correct initial state
            this.setInitialState();
            this.setupEventListeners();
            this.setupAccessibility();
            this.preloadImages();

            if (this.options.autoplay) {
                this.debugLog('Starting autoplay');
                this.startAutoplay();
            }

            this.container.classList.add('mkx-carousel-initialized');
            this.debugLog('Carousel initialized successfully');
        }

        /**
         * Set correct initial state
         */
        setInitialState() {
            // Reset all slides first
            this.slides.forEach((slide, index) => {
                slide.classList.remove('mkx-carousel-slide--active', 'mkx-carousel-slide--fade-in', 'mkx-carousel-slide--fade-out');
                slide.setAttribute('aria-hidden', 'true');
            });

            // Reset all indicators
            this.indicators.forEach(indicator => {
                indicator.classList.remove('mkx-carousel-indicator--active');
                indicator.setAttribute('aria-selected', 'false');
            });

            // Set active slide and indicator
            if (this.slides[this.currentSlide]) {
                this.slides[this.currentSlide].classList.add('mkx-carousel-slide--active');
                this.slides[this.currentSlide].setAttribute('aria-hidden', 'false');
            }

            if (this.indicators[this.currentSlide]) {
                this.indicators[this.currentSlide].classList.add('mkx-carousel-indicator--active');
                this.indicators[this.currentSlide].setAttribute('aria-selected', 'true');
            }

            this.debugLog('Initial state set', { currentSlide: this.currentSlide });
        }

        /**
         * Setup event listeners
         */
        setupEventListeners() {
            // Navigation buttons
            if (this.prevBtn) {
                this.prevBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.debugLog('Previous button clicked');
                    this.handleNavClick('prev');
                });
            }

            if (this.nextBtn) {
                this.nextBtn.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.debugLog('Next button clicked');
                    this.handleNavClick('next');
                });
            }

            // Indicators
            this.indicators.forEach((indicator, index) => {
                indicator.addEventListener('click', (e) => {
                    e.preventDefault();
                    this.debugLog('Indicator clicked', { index });
                    this.handleNavClick('goto', index);
                });

                // Keyboard navigation for indicators
                indicator.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter' || e.key === ' ') {
                        e.preventDefault();
                        this.debugLog('Indicator keyboard navigation', { index });
                        this.handleNavClick('goto', index);
                    }
                });
            });

            // Pause on hover (simplified)
            if (this.options.pauseOnHover && this.options.autoplay) {
                this.container.addEventListener('mouseenter', () => {
                    this.debugLog('Mouse enter - pausing');
                    this.stopAutoplay();
                });

                this.container.addEventListener('mouseleave', () => {
                    this.debugLog('Mouse leave - resuming');
                    if (this.isPlaying) {
                        this.startAutoplay();
                    }
                });
            }

            // Touch/swipe events
            if (this.options.enableSwipe) {
                this.setupTouchEvents();
            }

            // Keyboard navigation
            this.container.addEventListener('keydown', (e) => {
                if (e.key === 'ArrowLeft') {
                    e.preventDefault();
                    this.handleNavClick('prev');
                } else if (e.key === 'ArrowRight') {
                    e.preventDefault();
                    this.handleNavClick('next');
                }
            });

            // Visibility change handling
            document.addEventListener('visibilitychange', () => {
                if (document.hidden) {
                    this.debugLog('Page hidden - stopping autoplay');
                    this.stopAutoplay();
                } else if (this.isPlaying) {
                    this.debugLog('Page visible - starting autoplay');
                    this.startAutoplay();
                }
            });
        }

        /**
         * Handle navigation click - unified method
         */
        handleNavClick(action, slideIndex = null) {
            if (this.isTransitioning) {
                this.debugLog('Navigation blocked - transition in progress');
                return;
            }

            // Stop autoplay temporarily
            const wasPlaying = this.isPlaying && this.autoplayTimer;
            if (wasPlaying) {
                this.stopAutoplay();
            }

            // Execute navigation
            switch (action) {
                case 'prev':
                    this.prevSlide();
                    break;
                case 'next':
                    this.nextSlide();
                    break;
                case 'goto':
                    this.goToSlide(slideIndex);
                    break;
            }

            // Restart autoplay if it was running
            if (wasPlaying) {
                setTimeout(() => {
                    if (this.isPlaying) {
                        this.debugLog('Restarting autoplay after navigation');
                        this.startAutoplay();
                    }
                }, this.options.fadeSpeed + 200);
            }
        }

        /**
         * Setup touch/swipe events
         */
        setupTouchEvents() {
            let wasPlaying = false;

            this.track.addEventListener('touchstart', (e) => {
                this.touchStartX = e.touches[0].clientX;
                wasPlaying = this.isPlaying && this.autoplayTimer;
                if (wasPlaying) {
                    this.stopAutoplay();
                }
            }, { passive: true });

            this.track.addEventListener('touchend', (e) => {
                this.touchEndX = e.changedTouches[0].clientX;
                this.handleSwipe();

                if (wasPlaying) {
                    setTimeout(() => {
                        if (this.isPlaying) {
                            this.startAutoplay();
                        }
                    }, 500);
                }
            }, { passive: true });

            this.track.addEventListener('touchmove', (e) => {
                const touchCurrentX = e.touches[0].clientX;
                const deltaX = Math.abs(touchCurrentX - this.touchStartX);
                const deltaY = Math.abs(e.touches[0].clientY - (e.touches[0].clientY || 0));

                if (deltaX > deltaY) {
                    e.preventDefault();
                }
            }, { passive: false });
        }

        /**
         * Handle swipe gesture
         */
        handleSwipe() {
            const swipeDistance = this.touchStartX - this.touchEndX;

            if (Math.abs(swipeDistance) > this.minSwipeDistance) {
                if (swipeDistance > 0) {
                    this.nextSlide();
                } else {
                    this.prevSlide();
                }
            }
        }

        /**
         * Setup accessibility attributes
         */
        setupAccessibility() {
            this.container.setAttribute('role', 'region');
            if (!this.container.getAttribute('aria-label')) {
                this.container.setAttribute('aria-label', 'Карусель изображений');
            }

            this.slides.forEach((slide, index) => {
                slide.setAttribute('role', 'tabpanel');
                slide.setAttribute('id', `slide-${this.generateId()}-${index}`);
            });

            this.indicators.forEach((indicator, index) => {
                indicator.setAttribute('role', 'tab');
                indicator.setAttribute('aria-controls', `slide-${this.generateId()}-${index}`);
                indicator.setAttribute('tabindex', index === this.currentSlide ? '0' : '-1');
            });
        }

        /**
         * Generate unique ID for carousel
         */
        generateId() {
            if (!this.containerId) {
                this.containerId = Math.random().toString(36).substr(2, 9);
            }
            return this.containerId;
        }

        /**
         * Preload images for smoother transitions
         */
        preloadImages() {
            this.slides.forEach((slide, index) => {
                const img = slide.querySelector('.mkx-slide-bg');
                if (img) {
                    slide.classList.remove('mkx-loading');
                }
            });
        }

        /**
         * Go to specific slide - simplified and fixed
         */
        goToSlide(slideIndex) {
            if (slideIndex === this.currentSlide || slideIndex < 0 || slideIndex >= this.totalSlides) {
                this.debugLog('Invalid slide index or same slide', { slideIndex, currentSlide: this.currentSlide });
                return;
            }

            if (this.isTransitioning) {
                this.debugLog('Transition blocked - already transitioning');
                return;
            }

            this.isTransitioning = true;
            const previousSlide = this.currentSlide;
            this.currentSlide = slideIndex;

            this.debugLog('Changing slide', { from: previousSlide, to: slideIndex });

            // Update slides
            this.slides[previousSlide].classList.remove('mkx-carousel-slide--active');
            this.slides[previousSlide].classList.add('mkx-carousel-slide--fade-out');

            this.slides[slideIndex].classList.add('mkx-carousel-slide--active', 'mkx-carousel-slide--fade-in');

            // Update indicators
            this.indicators[previousSlide]?.classList.remove('mkx-carousel-indicator--active');
            this.indicators[previousSlide]?.setAttribute('aria-selected', 'false');

            this.indicators[slideIndex]?.classList.add('mkx-carousel-indicator--active');
            this.indicators[slideIndex]?.setAttribute('aria-selected', 'true');

            // Update ARIA
            this.slides[previousSlide].setAttribute('aria-hidden', 'true');
            this.slides[slideIndex].setAttribute('aria-hidden', 'false');

            // Clean up after transition
            setTimeout(() => {
                this.slides[previousSlide].classList.remove('mkx-carousel-slide--fade-out');
                this.slides[slideIndex].classList.remove('mkx-carousel-slide--fade-in');
                this.isTransitioning = false;
                this.debugLog('Transition completed');
            }, this.options.fadeSpeed);

            // Trigger custom event
            this.container.dispatchEvent(new CustomEvent('slideChanged', {
                detail: {
                    currentSlide: this.currentSlide,
                    previousSlide: previousSlide,
                    totalSlides: this.totalSlides
                }
            }));
        }

        /**
         * Go to next slide
         */
        nextSlide() {
            const nextIndex = (this.currentSlide + 1) % this.totalSlides;
            this.goToSlide(nextIndex);
        }

        /**
         * Go to previous slide
         */
        prevSlide() {
            const prevIndex = (this.currentSlide - 1 + this.totalSlides) % this.totalSlides;
            this.goToSlide(prevIndex);
        }

        /**
         * Start autoplay - simplified
         */
        startAutoplay() {
            if (!this.options.autoplay || this.totalSlides <= 1) {
                return;
            }

            this.stopAutoplay(); // Clear any existing timer
            this.isPlaying = true;

            this.debugLog('Starting autoplay timer', { interval: this.options.interval });

            this.autoplayTimer = setInterval(() => {
                if (!this.isTransitioning && this.isPlaying) {
                    this.debugLog('Autoplay tick - next slide');
                    this.nextSlide();
                } else {
                    this.debugLog('Autoplay tick skipped', { isTransitioning: this.isTransitioning, isPlaying: this.isPlaying });
                }
            }, this.options.interval);
        }

        /**
         * Stop autoplay - simplified
         */
        stopAutoplay() {
            if (this.autoplayTimer) {
                clearInterval(this.autoplayTimer);
                this.autoplayTimer = null;
                this.debugLog('Autoplay stopped');
            }
        }

        /**
         * Get current state for debugging
         */
        getState() {
            return {
                currentSlide: this.currentSlide,
                totalSlides: this.totalSlides,
                isPlaying: this.isPlaying,
                isTransitioning: this.isTransitioning,
                hasActiveTimer: !!this.autoplayTimer,
                interval: this.options.interval,
                autoplayEnabled: this.options.autoplay
            };
        }

        /**
         * Enable debug mode
         */
        enableDebug() {
            this.options.debug = true;
            this.debugLog('Debug mode enabled');
            return this.getState();
        }

        /**
         * Destroy carousel and cleanup
         */
        destroy() {
            this.stopAutoplay();
            this.container.classList.remove('mkx-carousel-initialized');

            this.slides.forEach(slide => {
                slide.classList.remove('mkx-carousel-slide--active', 'mkx-carousel-slide--fade-in', 'mkx-carousel-slide--fade-out');
            });

            this.indicators.forEach(indicator => {
                indicator.classList.remove('mkx-carousel-indicator--active');
            });

            this.debugLog('Carousel destroyed');
        }
    }

    /**
     * Initialize carousels when DOM is ready
     */
    function initCarousels() {
        const carouselContainers = document.querySelectorAll('.mkx-carousel-container');

        carouselContainers.forEach(container => {
            if (container.classList.contains('mkx-carousel-initialized')) {
                return;
            }

            // Enable debug for development (remove in production)
            const carousel = new MKXCarousel(container, {
                debug: false // Set to true for debugging
            });

            container.mkxCarousel = carousel;
        });
    }

    /**
     * Lazy load images when they come into view
     */
    function setupLazyLoading() {
        const images = document.querySelectorAll('img[loading="lazy"]');

        if ('IntersectionObserver' in window) {
            const imageObserver = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        const img = entry.target;
                        img.classList.add('loaded');
                        imageObserver.unobserve(img);
                    }
                });
            }, {
                rootMargin: '50px 0px'
            });

            images.forEach(img => {
                imageObserver.observe(img);
            });
        } else {
            images.forEach(img => {
                img.classList.add('loaded');
            });
        }
    }

    /**
     * Handle reduced motion preference
     */
    function handleReducedMotion() {
        const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');

        function updateMotion(e) {
            const carousels = document.querySelectorAll('.mkx-carousel-container');

            carousels.forEach(carousel => {
                if (e.matches && carousel.mkxCarousel) {
                    carousel.mkxCarousel.stopAutoplay();
                } else if (!e.matches && carousel.mkxCarousel && carousel.dataset.autoplay === 'true') {
                    carousel.mkxCarousel.startAutoplay();
                }
            });
        }

        mediaQuery.addEventListener('change', updateMotion);
        updateMotion(mediaQuery);
    }

    /**
     * Global API for external control
     */
    window.MKXCarousel = {
        Carousel: MKXCarousel,
        init: initCarousels,
        getInstance: (element) => {
            const container = typeof element === 'string' ?
                document.querySelector(element) : element;
            return container ? container.mkxCarousel : null;
        },
        pauseAll: () => {
            document.querySelectorAll('.mkx-carousel-container').forEach(container => {
                if (container.mkxCarousel) {
                    container.mkxCarousel.stopAutoplay();
                }
            });
        },
        resumeAll: () => {
            document.querySelectorAll('.mkx-carousel-container').forEach(container => {
                if (container.mkxCarousel && container.dataset.autoplay === 'true') {
                    container.mkxCarousel.startAutoplay();
                }
            });
        },
        // Debug helper
        debugAll: () => {
            document.querySelectorAll('.mkx-carousel-container').forEach((container, index) => {
                if (container.mkxCarousel) {
                    console.log(`Carousel ${index}:`, container.mkxCarousel.enableDebug());
                }
            });
        }
    };

    /**
     * Initialize everything when DOM is ready
     */
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            initCarousels();
            setupLazyLoading();
            handleReducedMotion();
        });
    } else {
        initCarousels();
        setupLazyLoading();
        handleReducedMotion();
    }

})();