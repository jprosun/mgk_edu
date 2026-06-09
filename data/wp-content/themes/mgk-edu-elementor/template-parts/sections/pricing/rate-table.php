<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$rows = $args['pricing']['rate_rows'] ?? [];
?>
<section class="mgk-section mgk-section-surface mgk-pricing-rate-section">
    <div class="mgk-shell">
        <?php mgk_render_section_heading( 'Hourly Rates by Level & Tutor Tier (SGD)', '', 'Based on Singapore market rates · Updated quarterly' ); ?>
        <div class="mgk-table-scroll">
            <table class="mgk-rate-table">
                <thead><tr><th>Level</th><th>Part-time</th><th>Full-time</th><th>Ex-MOE</th><th>Premium / PhD</th></tr></thead>
                <tbody>
                    <?php foreach ( $rows as $row ) : ?>
                        <tr>
                            <th><?php echo esc_html( $row['level'] ); ?><?php echo $row['note'] ? ' <span>' . esc_html( $row['note'] ) . '</span>' : ''; ?></th>
                            <td><?php echo esc_html( $row['part_time'] ); ?></td>
                            <td><?php echo esc_html( $row['full_time'] ); ?></td>
                            <td><?php echo esc_html( $row['ex_moe'] ); ?></td>
                            <td><?php echo esc_html( $row['premium'] ); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <p class="mgk-table-note">Rates may vary by tutor experience, qualifications, and demand. Final rate is confirmed at booking.</p>
    </div>
</section>
