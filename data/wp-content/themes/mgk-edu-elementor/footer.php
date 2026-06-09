<?php
/**
 * MGK site footer.
 */

if ( ! defined( 'ABSPATH' ) ) exit;
?>
    </main>
    <footer class="mgk-footer">
        <div class="mgk-shell">
            <div class="mgk-footer-grid">
                <div>
                    <?php echo mgk_site_logo_html( 'mgk-footer-logo' ); ?>
                    <p><?php echo esc_html( mgk_site_setting( 'footer_intro' ) ); ?></p>
                    <p><?php echo esc_html( mgk_site_setting( 'footer_registration' ) ); ?></p>
                </div>
                <div>
                    <h2>For Parents</h2>
                    <a href="<?php echo esc_url( mgk_cta_url( 'browse' ) ); ?>">Browse Tutors</a>
                    <a href="<?php echo esc_url( mgk_url( '/how-it-works/' ) ); ?>">How It Works</a>
                    <a href="<?php echo esc_url( mgk_cta_url( 'pricing' ) ); ?>">Pricing</a>
                    <a href="<?php echo esc_url( mgk_cta_url( 'faq' ) ); ?>">FAQ</a>
                </div>
                <div>
                    <h2>For Tutors</h2>
                    <a href="<?php echo esc_url( mgk_cta_url( 'tutor' ) ); ?>">Apply as Tutor</a>
                    <a href="<?php echo esc_url( mgk_url( '/tutor-resources/' ) ); ?>">Tutor Resources</a>
                </div>
                <div>
                    <h2>Company</h2>
                    <a href="<?php echo esc_url( mgk_url( '/about/' ) ); ?>">About Us</a>
                    <a href="<?php echo esc_url( mgk_url( '/contact/' ) ); ?>">Contact</a>
                    <a href="<?php echo esc_url( mgk_url( '/careers/' ) ); ?>">Careers</a>
                </div>
                <div>
                    <h2>Legal</h2>
                    <a href="<?php echo esc_url( mgk_url( '/terms/' ) ); ?>">Terms</a>
                    <a href="<?php echo esc_url( mgk_url( '/privacy-policy/' ) ); ?>">PDPA</a>
                    <a href="<?php echo esc_url( mgk_url( '/refund-policy/' ) ); ?>">Refund Policy</a>
                </div>
            </div>
            <div class="mgk-footer-bottom">
                <span>&copy; <?php echo esc_html( date( 'Y' ) ); ?> <?php echo esc_html( mgk_site_setting( 'footer_copyright' ) ); ?></span>
                <span><?php echo esc_html( mgk_site_setting( 'footer_regions' ) ); ?></span>
            </div>
        </div>
    </footer>
</div>
<?php wp_footer(); ?>
</body>
</html>
