<?php
/**
 * The template for displaying the footer
 *
 * Contains the closing of the #content div and all content after.
 *
 * @link https://developer.wordpress.org/themes/basics/template-files/#template-partials
 *
 * @package Shoptimizer Child
 * @version 1.0.9
 * @author KB
 * @link https://kowb.ru
 */

?>

</div><!-- #content -->

<footer id="colophon" class="mkx-site-footer" role="contentinfo" itemscope itemtype="https://schema.org/WPFooter">
    <!-- Main Footer -->
    <div class="mkx-footer-main">
        <div class="mkx-container">
            <div class="mkx-footer-content">

                <!-- Company Info Section -->
                <div class="mkx-footer-section mkx-footer-company">
                    <!-- Logo + Brand Section -->
                    <div class="mkx-footer-logo-section">
                        <?php if ( ! is_front_page() ) : ?>
                        <a href="<?php echo esc_url( home_url( '/' ) ); ?>" rel="home" aria-label="<?php esc_attr_e( 'На главную страницу', 'shoptimizer-child' ); ?>" class="mkx-footer-logo-container">
                        <?php else : ?>
                        <div class="mkx-footer-logo-container">
                        <?php endif; ?>
                            <!-- SVG Logo -->
                            <div class="mkx-footer-logo-svg">
                                <img src="<?php echo esc_url(get_theme_file_uri('/assets/images/logo/logo.svg')); ?>"
                                     alt="Логотип МИКЗ"
                                     width="72"
                                     height="72"
                                     loading="lazy"
                                     class="mkx-footer-brand-logo" />
                            </div>

                            <!-- Brand Text -->
                            <div class="mkx-footer-brand-text">
                                <span class="mkx-footer-brand-line1"><?php esc_html_e( 'МИКЗ', 'shoptimizer-child' ); ?></span>
                            </div>
                        <?php if ( ! is_front_page() ) : ?>
                        </a>
                        <?php else : ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <!-- Tagline Sections -->
                    <div class="mkx-footer-tagline">
                        <span class="mkx-footer-tagline-line1"><?php esc_html_e( 'ЗАПЧАСТИ ДЛЯ МОБИЛЬНЫХ', 'shoptimizer-child' ); ?></span>
                    </div>

                    <div class="mkx-footer-tagline">
                        <span class="mkx-footer-tagline-line2"><?php esc_html_e( 'ТЕЛЕФОНОВ И КОМПЬЮТЕРОВ', 'shoptimizer-child' ); ?></span>
                    </div>

                    <div class="mkx-footer-company-info">
                        <!-- <h3 class="mkx-footer-section-title">
                            <?php esc_html_e( 'О нас', 'shoptimizer-child' ); ?>
                        </h3> -->
                        <p class="mkx-footer-description">
                            <?php echo wp_kses_post( get_theme_mod( 'mkx_footer_description', __( 'Качественные запчасти для мобильных телефонов и компьютеров с гарантией и быстрой доставкой по России.', 'shoptimizer-child' ) ) ); ?>
                        </p>

                        <?php if ( get_theme_mod( 'mkx_footer_show_working_hours', true ) ) : ?>
                            <div class="mkx-working-hours">
                                <h4 class="mkx-working-hours-title">
                                    <i class="ph ph-clock" aria-hidden="true"></i>
                                    <?php esc_html_e( 'Режим работы', 'shoptimizer-child' ); ?>
                                </h4>
                                <p class="mkx-working-hours-text">
                                    <?php echo esc_html( get_theme_mod( 'mkx_footer_working_hours', __( 'Пн-Пт: 9:00-18:00, Сб-Вс: 10:00-16:00', 'shoptimizer-child' ) ) ); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Contact Section -->
                <div class="mkx-footer-section mkx-footer-contact">
                    <h3 class="mkx-footer-section-title">
                        <?php esc_html_e( 'Контакты', 'shoptimizer-child' ); ?>
                    </h3>

                    <div class="mkx-contact-info">
                        <?php
                        $phone = get_theme_mod( 'mkx_phone', '+7 (999) 123-45-67' );
                        $email = get_theme_mod( 'mkx_email', 'info@example.com' );
                        $address = get_theme_mod( 'mkx_address', __( 'г. Москва, ул. Примерная, д. 123', 'shoptimizer-child' ) );
                        ?>

                        <?php if ( $phone ) : ?>
                            <div class="mkx-contact-item">
                                <i class="ph ph-phone" aria-hidden="true"></i>
                                <div class="mkx-contact-details">
                                    <span class="mkx-contact-label"><?php esc_html_e( 'Телефон:', 'shoptimizer-child' ); ?></span>
                                    <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $phone ) ); ?>"
                                       class="mkx-contact-link">
                                        <?php echo esc_html( $phone ); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $email ) : ?>
                            <div class="mkx-contact-item">
                                <i class="ph ph-envelope" aria-hidden="true"></i>
                                <div class="mkx-contact-details">
                                    <span class="mkx-contact-label"><?php esc_html_e( 'Email:', 'shoptimizer-child' ); ?></span>
                                    <a href="mailto:<?php echo esc_attr( $email ); ?>"
                                       class="mkx-contact-link">
                                        <?php echo esc_html( $email ); ?>
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>

                        <?php if ( $address ) : ?>
                            <div class="mkx-contact-item">
                                <i class="ph ph-map-pin" aria-hidden="true"></i>
                                <div class="mkx-contact-details">
                                    <span class="mkx-contact-label"><?php esc_html_e( 'Адрес:', 'shoptimizer-child' ); ?></span>
                                    <span class="mkx-contact-text"><?php echo esc_html( $address ); ?></span>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Social Links -->
                    <?php if ( get_theme_mod( 'mkx_footer_show_social', true ) ) : ?>
                        <div class="mkx-social-links">
                            <h4 class="mkx-social-title"><?php esc_html_e( 'Соцсети/мессенджеры:', 'shoptimizer-child' ); ?></h4>
                            <div class="mkx-social-icons">
                                <?php
                                $social_links = array(
                                    'vk'        => array( 'icon' => 'ph-vk-logo', 'label' => 'VKontakte' ),
                                    'telegram'  => array( 'icon' => 'ph-telegram-logo', 'label' => 'Telegram' ),
                                    'whatsapp'  => array( 'icon' => 'ph-whatsapp-logo', 'label' => 'WhatsApp' ),
                                    'instagram' => array( 'icon' => 'ph-instagram-logo', 'label' => 'Instagram' ),
                                );

                                foreach ( $social_links as $network => $data ) {
                                    $url = get_theme_mod( "mkx_social_{$network}", '' );
                                    if ( $url ) : 
                                        ?>
                                        <a href="<?php echo esc_url( $url ); ?>"
                                           class="mkx-social-link mkx-social-<?php echo esc_attr( $network ); ?>"
                                           target="_blank"
                                           rel="noopener noreferrer"
                                           aria-label="<?php echo esc_attr( sprintf( __( 'Посетить наш %s', 'shoptimizer-child' ), $data['label'] ) ); ?>">
                                            <?php if ( 'vk' === $network ) : ?>
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="currentColor" xmlns="http://www.w3.org/2000/svg"><path fill-rule="evenodd" clip-rule="evenodd" d="M2.618 6.678A.5.5 0 0 1 3 6.5h3.52a.5.5 0 0 1 .494.42c.144.882.62 2.586 1.74 4.187a.5.5 0 0 1-.82.573A11.9 11.9 0 0 1 6.107 7.5h-2.49c.43 1.738 1.606 4.69 3.38 6.92.972 1.222 2.029 2.006 2.942 2.478.929.479 1.675.619 2.023.6h.039a.8.8 0 0 0 .505-.16c.127-.103.28-.315.28-.792v-1.52c0-.294.116-.59.36-.78.26-.2.598-.229.9-.088a19 19 0 0 1 .95.482c1.138.746 2.233 1.944 2.928 2.833q.014.017.024.022l.011.003h2.516l.01-.001h.002a.03.03 0 0 0 .009-.014l.004-.023q.001-.009-.015-.03a91 91 0 0 0-2.179-2.495l-.716-.803c-.5-.565-.535-1.393-.15-2.013.842-1.358 1.95-3.307 2.293-4.619H17.69q0-.002-.02.008a.13.13 0 0 0-.041.055c-.713 1.608-2.215 3.593-3.307 4.59-.306.278-.717.323-1.05.13-.311-.18-.485-.524-.485-.89V7.5h-2.671q.053.1.105.205c.202.416.388.935.388 1.436l.003.917c.006.988.013 2.347-.004 3.087a.5.5 0 1 1-1-.022c.017-.725.01-2.05.004-3.036L9.61 9.14c0-.279-.111-.637-.288-.999a4.2 4.2 0 0 0-.516-.817.5.5 0 0 1 .38-.825h4.102a.5.5 0 0 1 .5.5v4.283c.987-.95 2.305-2.72 2.928-4.125.163-.368.525-.658.976-.658h2.048c.62 0 1.137.573.965 1.237-.382 1.483-1.581 3.565-2.415 4.91a.7.7 0 0 0 .05.822c.208.235.446.501.7.784.726.813 1.578 1.766 2.217 2.54.548.664.117 1.705-.781 1.705H17.96c-.332 0-.63-.161-.824-.41-.663-.849-1.671-1.94-2.67-2.6a19 19 0 0 0-.678-.347v1.405c0 .723-.25 1.242-.65 1.568a1.8 1.8 0 0 1-1.142.384c-.587.025-1.505-.19-2.514-.712-1.035-.534-2.203-1.407-3.267-2.744-2.107-2.648-3.41-6.225-3.707-7.957a.5.5 0 0 1 .11-.407M13.789 15v.002Zm.005-3.566"/></svg>
                                            <?php else : ?>
                                                <i class="ph <?php echo esc_attr( $data['icon'] ); ?>" aria-hidden="true"></i>
                                            <?php endif; ?>
                                        </a>
                                    <?php
                                    endif;
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Newsletter Section -->
                <div class="mkx-footer-section mkx-footer-newsletter">
                    <h3 class="mkx-footer-section-title">
                        <?php esc_html_e( 'Подписка на новости', 'shoptimizer-child' ); ?>
                    </h3>

                    <p class="mkx-newsletter-description">
                        <?php echo esc_html( get_theme_mod( 'mkx_newsletter_description', __( 'Подпишитесь на нашу рассылку и получайте информацию о новых товарах и акциях', 'shoptimizer-child' ) ) ); ?>
                    </p>

                    <div class="mkx-newsletter-form">
                        <?php
                        // Get the shortcode from the Customizer, with a fallback to the default.
                        $shortcode = get_theme_mod( 'mkx_footer_newsletter_shortcode', '[contact-form-7 id="cbb552c" title="FollowUs"]' );
                        if ( ! empty( $shortcode ) ) {
                            echo do_shortcode( $shortcode );
                        } else {
                            // Optional: Display a message if no shortcode is provided.
                            if ( current_user_can( 'edit_theme_options' ) ) {
                                esc_html_e( 'Please add a newsletter shortcode in the Customizer.', 'shoptimizer-child' );
                            }
                        }
                        ?>
                    </div>

                    <!-- Newsletter Benefits -->
                    <?php if ( get_theme_mod( 'mkx_footer_show_benefits', true ) ) : ?>
                        <div class="mkx-newsletter-benefits">
                            <div class="mkx-benefit-item">
                                <i class="ph ph-check-circle" aria-hidden="true"></i>
                                <span><?php esc_html_e( 'Эксклюзивные скидки', 'shoptimizer-child' ); ?></span>
                            </div>
                            <div class="mkx-benefit-item">
                                <i class="ph ph-check-circle" aria-hidden="true"></i>
                                <span><?php esc_html_e( 'Первыми узнавайте о новинках', 'shoptimizer-child' ); ?></span>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Additional Links Section -->
                <div class="mkx-footer-section mkx-footer-additional">
                    <h3 class="mkx-footer-section-title">
                        <?php esc_html_e( 'Дополнительно', 'shoptimizer-child' ); ?>
                    </h3>

                    <?php
                    if ( has_nav_menu( 'footer_additional' ) ) {
                        wp_nav_menu( array(
                            'theme_location' => 'footer_additional',
                            'menu_class'     => 'mkx-footer-additional-menu',
                            'container'      => false,
                            'depth'          => 1,
                            'fallback_cb'    => false,
                        ) );
                    }
                    ?>
                </div>

                <!-- Policy Links Section -->
                <div class="mkx-footer-section mkx-footer-policies">
                    <h3 class="mkx-footer-section-title">
                        <?php esc_html_e( 'Политики', 'shoptimizer-child' ); ?>
                    </h3>

                    <?php
                    if ( has_nav_menu( 'footer_policies' ) ) {
                        wp_nav_menu( array(
                            'theme_location' => 'footer_policies',
                            'menu_class'     => 'mkx-footer-policies-menu',
                            'container'      => false,
                            'depth'          => 1,
                            'fallback_cb'    => false,
                        ) );
                    }
                    ?>
                </div>

            </div><!-- .mkx-footer-content -->
        </div><!-- .mkx-container -->
    </div><!-- .mkx-footer-main -->

    <!-- Bottom Footer -->
    <div class="mkx-footer-bottom">
        <div class="mkx-container">
            <div class="mkx-footer-bottom-content">

                <!-- Copyright -->
                <div class="mkx-footer-copyright">
                    <p>
                        &copy; <?php echo esc_html( date( 'Y' ) ); ?>
                        <span class="mkx-site-name"><?php bloginfo( 'name' ); ?></span>.
                        <?php esc_html_e( 'Все права защищены.', 'shoptimizer-child' ); ?>
                    </p>
                </div>

                <?php /*
                <!-- Phone in Bottom Footer -->
                <div class="mkx-footer-bottom-phone">
                    <?php if ( $phone ) : ?>
                        <a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/", ", $phone ) ); ?>"
                           class="mkx-footer-bottom-phone-link">
                            <?php echo esc_html( $phone ); ?>
                        </a>
                    <?php endif; ?>
                </div>
                */ ?>

            </div>
        </div>
    </div>

    <!-- Structured Data -->
    <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "Organization",
            "name": "<?php echo esc_js( get_bloginfo( 'name' ) ); ?>",
            "url": "<?php echo esc_js( home_url() ); ?>",
        <?php if ( $phone ) : ?>
            "telephone": "<?php echo esc_js( $phone ); ?>",
        <?php endif; ?>
        <?php if ( $email ) : ?>
            "email": "<?php echo esc_js( $email ); ?>",
        <?php endif; ?>
        <?php if ( $address ) : ?>
            "address": {
                "@type": "PostalAddress",
                "streetAddress": "<?php echo esc_js( $address ); ?>"
            },
        <?php endif; ?>
        "sameAs": [
        <?php
        $social_urls = array();
        foreach ( array( 'vk', 'telegram', 'whatsapp', 'instagram' ) as $network ) {
            $url = get_theme_mod( "mkx_social_{$network}", '' );
            if ( $url ) {
                $social_urls[] = '"' . esc_js( $url ) . '"';
            }
        }
        echo implode( ',', $social_urls );
        ?>
        ]
    }
    </script>

</footer><!-- #colophon -->

</div><!-- #page -->

<?php wp_footer(); ?>

</body>
</html>
