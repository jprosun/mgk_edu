<?php
/**
 * Edu commerce adapter — edu's implementations of the industry SEAMS.
 * ==================================================================
 * SCHEMA-AND-MIGRATIONS.md: edu is an INDUSTRY plugging into the blind core. The
 * core (OrderRepository) never names edu; edu registers HERE what only edu knows:
 *   - PricingResolver   — what an edu sellable (edu_trial, edu_package_*) costs.
 *   - FulfillmentHandler — "edu payment confirmed → do X".
 *
 * Wiring discipline (ADDITIVE — this slice changes NO existing behaviour):
 *   - The legacy confirm logic still runs on mgk_booking_confirmed. The handler is
 *     the DECLARED home where that provisioning migrates LATER; for now it announces
 *     a canonical `mgk_commerce_fulfilled` event so the seam is live and observable.
 *   - booking-core-order.php calls Dispatcher::fulfill ONLY on a PENDING→PAID
 *     transition (idempotent across webhook retries), wrapped so it can never break
 *     the confirm flow.
 */

if ( ! defined( 'ABSPATH' ) ) exit;

use Margick\Commerce\Dispatcher;
use Margick\Commerce\Contracts\PricingResolver;
use Margick\Commerce\Contracts\FulfillmentHandler;
use Margick\Commerce\Domain\Money;

add_action( 'init', function () {
	if ( ! class_exists( Dispatcher::class ) ) return; // commerce module absent → no-op

	// ── Pricing seam ───────────────────────────────────────────────────────
	// edu_trial | edu_package_8 | edu_package_16 … The base price is computed by
	// the edu pricing layer and passed in $context['base']; this resolver is the
	// declared place the core can ask "what does this edu item cost" by item_type.
	Dispatcher::registerPricing( new class implements PricingResolver {
		public function supports( string $itemType ): bool {
			return str_starts_with( $itemType, 'edu_' );
		}
		public function basePrice( string $itemType, int $refId, array $context = [] ): Money {
			$currency = isset( $context['currency'] ) ? (string) $context['currency'] : 'SGD';
			$base     = isset( $context['base'] ) ? (float) $context['base'] : 0.0;
			return Money::ofMajor( $base, $currency );
		}
	} );

	// ── Fulfillment seam ─────────────────────────────────────────────────────
	Dispatcher::registerFulfillment( new class implements FulfillmentHandler {
		public function supports( string $itemType ): bool {
			return str_starts_with( $itemType, 'edu_' );
		}
		public function fulfill( string $itemType, int $refId, array $order ): void {
			// Canonical, industry-blind "fulfilled" signal. Existing edu confirm
			// logic (login provisioning etc.) still fires on mgk_booking_confirmed;
			// it will migrate to listen here as the seam takes over.
			do_action( 'mgk_commerce_fulfilled', $itemType, $refId, $order );
		}
	} );
}, 6 );
