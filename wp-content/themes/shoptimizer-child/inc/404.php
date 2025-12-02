<?php
/**
 * Template Name: 404 Error Page
 * Description: Custom 404 page for МИКЗ phone parts store
 */

get_header(); ?>

<style>
.error-404-page {
    min-height: calc(100vh - var(--header-height, 80px));
    display: flex;
    align-items: center;
    justify-content: center;
    padding: var(--space-2xl) var(--container-padding);
    background: linear-gradient(135deg, var(--mkx-bg-secondary) 0%, var(--mkx-white) 100%);
    position: relative;
    overflow: hidden;
}

.error-404-page::before {
    content: '';
    position: absolute;
    top: -50%;
    right: -10%;
    width: 600px;
    height: 600px;
    background: radial-gradient(circle, var(--mkx-primary-lighter) 0%, transparent 70%);
    border-radius: var(--radius-full);
    opacity: 0.3;
    animation: float 8s ease-in-out infinite;
}

.error-404-page::after {
    content: '';
    position: absolute;
    bottom: -30%;
    left: -5%;
    width: 400px;
    height: 400px;
    background: radial-gradient(circle, var(--mkx-secondary-lighter) 0%, transparent 70%);
    border-radius: var(--radius-full);
    opacity: 0.4;
    animation: float 6s ease-in-out infinite reverse;
}

@keyframes float {
    0%, 100% {
        transform: translateY(0) rotate(0deg);
    }
    50% {
        transform: translateY(-30px) rotate(5deg);
    }
}

.error-404-container {
    max-width: 900px;
    width: 100%;
    text-align: center;
    position: relative;
    z-index: var(--z-10);
}

.error-404-visual {
    margin-bottom: var(--space-2xl);
    position: relative;
}

.error-404-number {
    font-size: clamp(6rem, 15vw, 12rem);
    font-weight: var(--mkx-font-black);
    line-height: var(--mkx-lh-none);
    color: var(--mkx-primary);
    text-shadow: 4px 4px 0 var(--mkx-primary-lighter);
    margin: 0;
    letter-spacing: var(--mkx-tracking-tight);
    position: relative;
    display: inline-block;
}

.error-404-number::before {
    content: '404';
    position: absolute;
    top: 0;
    left: 0;
    color: var(--mkx-primary-dark);
    opacity: 0.1;
    transform: translate(8px, 8px);
    z-index: -1;
}

.broken-phone-icon {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: clamp(80px, 15vw, 120px);
    height: auto;
    opacity: 0.15;
    animation: shake 3s ease-in-out infinite;
}

@keyframes shake {
    0%, 100% {
        transform: translate(-50%, -50%) rotate(0deg);
    }
    25% {
        transform: translate(-48%, -50%) rotate(-2deg);
    }
    75% {
        transform: translate(-52%, -50%) rotate(2deg);
    }
}

.error-404-content {
    margin-bottom: var(--space-xl);
}

.error-404-title {
    font-size: var(--mkx-fs-h1);
    font-weight: var(--mkx-font-bold);
    color: var(--mkx-text-primary);
    margin: 0 0 var(--space-md);
    line-height: var(--mkx-lh-tight);
}

.error-404-description {
    font-size: var(--fs-lg);
    color: var(--mkx-text-secondary);
    line-height: var(--mkx-lh-relaxed);
    margin: 0 0 var(--space-xl);
    max-width: 600px;
    margin-left: auto;
    margin-right: auto;
}

.error-404-actions {
    display: flex;
    gap: var(--space-md);
    justify-content: center;
    flex-wrap: wrap;
    margin-bottom: var(--space-2xl);
}

.error-404-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--space-xs);
    padding: var(--btn-padding-y) var(--btn-padding-x);
    font-size: var(--fs-base);
    font-weight: var(--btn-font-weight);
    text-decoration: none;
    border-radius: var(--btn-border-radius);
    transition: var(--mkx-transition);
    border: var(--border-2) solid transparent;
    cursor: pointer;
    font-family: var(--mkx-font-primary);
}

.error-404-btn-primary {
    background-color: var(--mkx-primary);
    color: var(--mkx-white);
    box-shadow: var(--shadow-md);
}

.error-404-btn-primary:hover {
    background-color: var(--mkx-primary-dark);
    transform: translateY(-2px);
    box-shadow: var(--shadow-lg);
}

.error-404-btn-secondary {
    background-color: transparent;
    color: var(--mkx-text-primary);
    border-color: var(--mkx-border-primary);
}

.error-404-btn-secondary:hover {
    background-color: var(--mkx-bg-secondary);
    border-color: var(--mkx-primary);
    color: var(--mkx-primary);
}

.error-404-suggestions {
    background: var(--mkx-white);
    border-radius: var(--radius-xl);
    padding: var(--space-xl);
    box-shadow: var(--shadow-lg);
}

.error-404-suggestions-title {
    font-size: var(--mkx-fs-h4);
    font-weight: var(--mkx-font-semibold);
    color: var(--mkx-text-primary);
    margin: 0 0 var(--space-lg);
}

.error-404-links {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: var(--space-md);
    list-style: none;
    padding: 0;
    margin: 0;
}

.error-404-link-item {
    text-align: left;
}

.error-404-link {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    padding: var(--space-md);
    background: var(--mkx-bg-secondary);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--mkx-text-primary);
    transition: var(--mkx-transition);
    border: var(--border-1) solid transparent;
}

.error-404-link:hover {
    background: var(--mkx-primary-lighter);
    border-color: var(--mkx-primary);
    transform: translateX(4px);
}

.error-404-link-icon {
    width: 24px;
    height: 24px;
    color: var(--mkx-primary);
    flex-shrink: 0;
}

.error-404-link-text {
    font-size: var(--fs-base);
    font-weight: var(--mkx-font-medium);
}

/* Responsive */
@media (max-width: 768px) {
    .error-404-page {
        padding: var(--space-xl) var(--space-md);
    }

    .error-404-actions {
        flex-direction: column;
        width: 100%;
    }

    .error-404-btn {
        width: 100%;
        justify-content: center;
    }

    .error-404-links {
        grid-template-columns: 1fr;
    }

    .error-404-number {
        text-shadow: 2px 2px 0 var(--mkx-primary-lighter);
    }

    .error-404-number::before {
        transform: translate(4px, 4px);
    }
}

/* Accessibility */
@media (prefers-reduced-motion: reduce) {
    .error-404-page::before,
    .error-404-page::after,
    .broken-phone-icon {
        animation: none;
    }

    .error-404-btn:hover {
        transform: none;
    }
}
</style>

<main class="error-404-page">
    <div class="error-404-container">
        <div class="error-404-visual">
            <h1 class="error-404-number">404</h1>
            
            <!-- Broken Phone SVG Icon -->
            <svg class="broken-phone-icon" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M17 2H7C5.89543 2 5 2.89543 5 4V20C5 21.1046 5.89543 22 7 22H17C18.1046 22 19 21.1046 19 20V4C19 2.89543 18.1046 2 17 2Z" 
                      stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M12 18H12.01" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                <path d="M3 8L21 16" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M8 12L10 10" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
                <path d="M14 14L16 12" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
            </svg>
        </div>

        <div class="error-404-content">
            <h2 class="error-404-title">Упс! Страница не найдена</h2>
            <p class="error-404-description">
                Похоже, что эта страница разобрана на запчасти или находится на ремонте. 
                Но не волнуйтесь, у нас есть всё необходимое, чтобы помочь вам найти то, что нужно!
            </p>

            <div class="error-404-actions">
                <a href="<?php echo esc_url(home_url('/')); ?>" class="error-404-btn error-404-btn-primary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/>
                        <polyline points="9 22 9 12 15 12 15 22"/>
                    </svg>
                    Вернуться на главную
                </a>
                <a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="error-404-btn error-404-btn-secondary">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <circle cx="9" cy="21" r="1"/>
                        <circle cx="20" cy="21" r="1"/>
                        <path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/>
                    </svg>
                    Перейти в каталог
                </a>
            </div>
        </div>

        <div class="error-404-suggestions">
            <h3 class="error-404-suggestions-title">Популярные разделы</h3>
            <ul class="error-404-links">
                <li class="error-404-link-item">
                    <a href="<?php echo esc_url(home_url('/catalog/iphone/')); ?>" class="error-404-link">
                        <svg class="error-404-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                            <line x1="12" y1="18" x2="12.01" y2="18"/>
                        </svg>
                        <span class="error-404-link-text">Запчасти iPhone</span>
                    </a>
                </li>
                <li class="error-404-link-item">
                    <a href="<?php echo esc_url(home_url('/catalog/samsung/')); ?>" class="error-404-link">
                        <svg class="error-404-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                            <line x1="12" y1="18" x2="12.01" y2="18"/>
                        </svg>
                        <span class="error-404-link-text">Запчасти Samsung</span>
                    </a>
                </li>
                <li class="error-404-link-item">
                    <a href="<?php echo esc_url(home_url('/catalog/xiaomi/')); ?>" class="error-404-link">
                        <svg class="error-404-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="5" y="2" width="14" height="20" rx="2" ry="2"/>
                            <line x1="12" y1="18" x2="12.01" y2="18"/>
                        </svg>
                        <span class="error-404-link-text">Запчасти Xiaomi</span>
                    </a>
                </li>
                <li class="error-404-link-item">
                    <a href="<?php echo esc_url(home_url('/aksessuary/')); ?>" class="error-404-link">
                        <svg class="error-404-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M20.59 13.41l-7.17 7.17a2 2 0 0 1-2.83 0L2 12V2h10l8.59 8.59a2 2 0 0 1 0 2.82z"/>
                            <line x1="7" y1="7" x2="7.01" y2="7"/>
                        </svg>
                        <span class="error-404-link-text">Аксессуары</span>
                    </a>
                </li>
                <li class="error-404-link-item">
                    <a href="<?php echo esc_url(home_url('/kontakty/')); ?>" class="error-404-link">
                        <svg class="error-404-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/>
                            <circle cx="12" cy="10" r="3"/>
                        </svg>
                        <span class="error-404-link-text">Контакты</span>
                    </a>
                </li>
                <li class="error-404-link-item">
                    <a href="<?php echo esc_url(home_url('/o-kompanii/')); ?>" class="error-404-link">
                        <svg class="error-404-link-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="16" x2="12" y2="12"/>
                            <line x1="12" y1="8" x2="12.01" y2="8"/>
                        </svg>
                        <span class="error-404-link-text">О компании</span>
                    </a>
                </li>
            </ul>
        </div>
    </div>
</main>

<?php get_footer(); ?>