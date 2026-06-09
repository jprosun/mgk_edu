<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$packages = $args['pricing']['packages'] ?? [];
?>
<section class="mgk-section mgk-section-surface">
    <div class="mgk-shell">
        <div class="mgk-section-head center">
            <h2>Choose your package</h2>
            <p>Save more when you commit longer.</p>
        </div>
        <div class="mgk-pricing-card-grid">
            <?php foreach ( $packages as $plan ) : ?>
                <?php get_template_part( 'template-parts/components/pricing-card', null, [ 'plan' => $plan ] ); ?>
            <?php endforeach; ?>
        </div>
    </div>
</section>
