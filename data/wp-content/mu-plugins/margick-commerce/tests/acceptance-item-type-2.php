<?php
/**
 * Acceptance test — SCHEMA-AND-MIGRATIONS.md §4-Phase0 #5.
 * A SECOND, non-booking item_type runs đặt → trả → fulfill through the SAME
 * industry-blind core (mgk_core_orders + OrderRepository) + the Dispatcher seams.
 * Proves the core isn't edu-in-disguise. Greenfield, touches no edu data, cleans up.
 */

use Margick\Commerce\Dispatcher;
use Margick\Commerce\Wp\OrderRepository;
use Margick\Commerce\Wp\CoreSchema;
use Margick\Commerce\Contracts\PricingResolver;
use Margick\Commerce\Contracts\FulfillmentHandler;
use Margick\Commerce\Domain\Money;

$fails = [];
$check = function (string $label, bool $ok) use (&$fails) {
    echo ($ok ? "  PASS  " : "  FAIL  ") . $label . "\n";
    if (! $ok) { $fails[] = $label; }
};

$CODE = 'ACC-ACCEPTANCE-TEST';

// Pre-clean any leftover from a previous run.
global $wpdb;
$pre = OrderRepository::findIdByCode($CODE);
if ($pre) {
    $wpdb->delete(CoreSchema::table('order_items'), ['order_id' => $pre]);
    $wpdb->delete(CoreSchema::table('orders'), ['id' => $pre]);
}

Dispatcher::reset();

// A flat, non-booking sellable (e.g. a downloadable study pack). Edu-plausible but
// uses ZERO booking machinery — the whole point of the acceptance.
Dispatcher::registerPricing(new class implements PricingResolver {
    public function supports(string $t): bool { return $t === 'acc_flat'; }
    public function basePrice(string $t, int $ref, array $ctx = []): Money {
        return Money::ofMajor(25.00, 'SGD');
    }
});

$GLOBALS['acc_fulfilled'] = [];
Dispatcher::registerFulfillment(new class implements FulfillmentHandler {
    public function supports(string $t): bool { return $t === 'acc_flat'; }
    public function fulfill(string $t, int $ref, array $order): void {
        $GLOBALS['acc_fulfilled'][] = (string) $order['order_code'];
    }
});

// 1) Pricing seam resolves a non-booking type.
$price = Dispatcher::priceFor('acc_flat', 999);
$check('pricing seam resolves acc_flat = 25.00 SGD', $price !== null && abs($price->toMajor() - 25.00) < 0.001);

// 2) Order envelope created through the SAME core door edu uses.
$oid = OrderRepository::createOrder([
    'order_code' => $CODE,
    'currency'   => 'SGD',
    'status'     => 'PENDING',
    'metadata'   => ['source' => 'acceptance_test'],
]);
$check('createOrder returns id', $oid > 0);

// 3) Line item with qty=2 — proves qty math is generic (not booking's always-1).
OrderRepository::addItem($oid, [
    'item_type'   => 'acc_flat',
    'item_ref_id' => 999,
    'name'        => 'Acceptance study pack',           // LAW-2 snapshot
    'sku'         => 'ACC-PACK',
    'sell_unit'   => 'piece',
    'unit_price'  => $price->toMajor(),
    'qty'         => 2,
    'line_total'  => $price->toMajor() * 2,
]);
OrderRepository::recalcTotals($oid);

$order = OrderRepository::getOrderWithItems($oid);
$check('order has 1 item', is_array($order) && count($order['items']) === 1);
$check('recalc total = 50.00 (2 × 25.00)', $order && abs((float) $order['total_amount'] - 50.00) < 0.001);
$check('item_type stored verbatim (core never interprets it)', $order && $order['items'][0]['item_type'] === 'acc_flat');

// 4) Pay → fulfill via the seam.
OrderRepository::updateStatus($oid, 'PAID');
$paid = OrderRepository::getOrder($oid);
$check('status advanced to PAID', $paid && $paid['status'] === 'PAID');

$ran = Dispatcher::fulfill('acc_flat', 999, $paid);
$check('fulfillment seam ran for acc_flat', $ran === true);
$check('fulfillment received this order_code', in_array($CODE, $GLOBALS['acc_fulfilled'], true));

// 5) Negative control: an unregistered type must NOT silently fulfill.
$check('unregistered type does NOT fulfill (no silent no-op)', Dispatcher::fulfill('acc_unknown', 1, $paid) === false);

// Cleanup — leave no footprint.
$wpdb->delete(CoreSchema::table('order_items'), ['order_id' => $oid]);
$wpdb->delete(CoreSchema::table('orders'), ['id' => $oid]);
$gone = OrderRepository::findIdByCode($CODE) === 0;
$check('cleanup removed the test order', $gone);

Dispatcher::reset();

echo "\n" . (empty($fails) ? "ACCEPTANCE PASSED ✅ — core is industry-blind for a 2nd item_type.\n"
                           : "ACCEPTANCE FAILED ❌ — " . count($fails) . " check(s).\n");
