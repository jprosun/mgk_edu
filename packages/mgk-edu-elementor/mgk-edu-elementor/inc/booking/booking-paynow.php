<?php
/**
 * MGK Booking Engine — Phase 0.5 · PayNow EMVCo QR payload (LOCKED DATA CORE).
 * ===========================================================================
 * Builds a standards-compliant PayNow (SGQR / EMVCo) QR string that SG banking
 * apps can scan — encoding the merchant UEN, the exact amount, and a reference.
 * The image itself is drawn client-side from this payload (qrcode.js); this file
 * owns only the payload + validation so the logic stays server-authoritative.
 *
 * IMPORTANT — PayNow has NO automatic confirmation (unlike Stripe webhooks).
 * Scanning + transferring moves money into the company bank account with no
 * signal back to the site. A PayNow booking therefore stays PENDING_PAYMENT
 * until an admin force-confirms it (Step 9) or a future PayNow-collection gateway
 * is wired in. The QR is correct and scannable; reconciliation is manual.
 *
 * EMVCo field reference (PayNow proxy = UEN, type 2):
 *   00 Payload format = "01"
 *   01 Point of init  = "12" (dynamic, amount fixed) | "11" (static)
 *   26 Merchant acct (PayNow):
 *        00 = "SG.PAYNOW"
 *        01 = proxy type "2" (UEN)  [ "0" = mobile ]
 *        02 = proxy value (the UEN)
 *        03 = amount editable "0" (locked) | "1"
 *        04 = expiry (YYYYMMDD) — optional
 *   52 MCC = "0000"
 *   53 Currency = "702" (SGD)
 *   54 Amount (when dynamic)
 *   58 Country = "SG"
 *   59 Merchant name
 *   60 City = "Singapore"
 *   62 Additional data:
 *        01 = bill/reference number
 *   63 CRC16-CCITT (False) over everything incl. "6304"
 */

if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Effective payment configuration — the SINGLE source of truth for which methods
 * are actually usable. An "enabled" checkbox is INTENT; a method is only ACTIVE
 * when it is also fully configured (tight logic, as requested):
 *   - PayNow active  ⟺ enabled AND a valid UEN is set
 *   - Stripe active  ⟺ enabled AND a secret key is set
 *   - Stripe is LIVE ⟺ secret key set (else the engine runs MOCK mode)
 *
 * @return array{
 *   paynow_active:bool, paynow_uen:string, paynow_payee:string,
 *   stripe_active:bool, stripe_live:bool,
 *   methods:array<int,array{key:string,label:string,tag:string}>,
 *   has_any:bool
 * }
 */
function mgk_payment_config() {
	$paynow_on  = mgk_site_setting( 'pay_paynow_enabled', '1' ) === '1';
	$uen        = strtoupper( trim( (string) mgk_site_setting( 'paynow_uen', '' ) ) );
	$payee      = (string) mgk_site_setting( 'paynow_payee', '' );
	$paynow_active = $paynow_on && mgk_paynow_is_valid_uen( $uen );

	$stripe_on  = mgk_site_setting( 'pay_stripe_enabled', '0' ) === '1';
	$secret     = function_exists( 'mgk_stripe_secret_key' ) ? mgk_stripe_secret_key() : '';
	$stripe_live = $secret !== '';
	// Stripe is "active" as a method if enabled. In MOCK mode (no key) it still
	// works end-to-end for testing, so we allow it when enabled even without a key.
	$stripe_active = $stripe_on;

	$methods = [];
	if ( $paynow_active ) $methods[] = [ 'key' => 'paynow', 'label' => 'PayNow QR', 'tag' => 'INSTANT' ];
	if ( $stripe_active ) $methods[] = [ 'key' => 'card',   'label' => 'Card (Stripe)', 'tag' => $stripe_live ? '' : 'TEST' ];

	return [
		'paynow_active' => $paynow_active,
		'paynow_uen'    => $uen,
		'paynow_payee'  => $payee,
		'stripe_active' => $stripe_active,
		'stripe_live'   => $stripe_live,
		'methods'       => $methods,
		'has_any'       => ! empty( $methods ),
	];
}

/**
 * Validate a Singapore UEN (loose but useful). Accepts the common formats:
 *   - "NNNNNNNNNX"      (9 digits + check letter, business)
 *   - "YYYYNNNNNX"      (year + 5 digits + letter, local company)
 *   - "TNNGGNNNNX"/"SNN…" (entity types)
 * We accept 9–10 alphanumeric chars, must contain digits, end in a letter.
 */
function mgk_paynow_is_valid_uen( $uen ) {
	$uen = strtoupper( trim( (string) $uen ) );
	if ( ! preg_match( '/^[0-9A-Z]{9,10}$/', $uen ) ) return false;
	if ( ! preg_match( '/[0-9]/', $uen ) ) return false;       // must have digits
	if ( ! preg_match( '/[A-Z]$/', $uen ) ) return false;       // ends in a letter
	return true;
}

/** TLV helper: id + zero-padded 2-digit length + value. */
function mgk_paynow_tlv( $id, $value ) {
	$value = (string) $value;
	return $id . str_pad( (string) strlen( $value ), 2, '0', STR_PAD_LEFT ) . $value;
}

/**
 * CRC16-CCITT (False): poly 0x1021, init 0xFFFF, no reflect, no xorout.
 * Returns 4 uppercase hex chars.
 */
function mgk_paynow_crc16( $data ) {
	$crc = 0xFFFF;
	$len = strlen( $data );
	for ( $i = 0; $i < $len; $i++ ) {
		$crc ^= ( ord( $data[ $i ] ) << 8 );
		for ( $b = 0; $b < 8; $b++ ) {
			$crc = ( $crc & 0x8000 ) ? ( ( $crc << 1 ) ^ 0x1021 ) : ( $crc << 1 );
			$crc &= 0xFFFF;
		}
	}
	return strtoupper( str_pad( dechex( $crc ), 4, '0', STR_PAD_LEFT ) );
}

/**
 * Build the full PayNow EMVCo payload string.
 *
 * @param array $args uen, amount(float), reference, merchant_name,
 *                    editable(bool), expiry(YYYYMMDD|'')
 * @return string|WP_Error  the QR payload, or WP_Error if config invalid.
 */
function mgk_paynow_build_payload( array $args ) {
	$uen = strtoupper( trim( (string) ( $args['uen'] ?? '' ) ) );
	if ( ! mgk_paynow_is_valid_uen( $uen ) ) {
		return new WP_Error( 'mgk_bad_uen', 'PayNow UEN is missing or invalid.' );
	}

	$amount   = round( (float) ( $args['amount'] ?? 0 ), 2 );
	$editable = ! empty( $args['editable'] );
	$ref      = preg_replace( '/[^A-Za-z0-9\-]/', '', (string) ( $args['reference'] ?? '' ) );
	$ref      = substr( $ref, 0, 25 ) ?: 'TRIAL';
	$name     = substr( (string) ( $args['merchant_name'] ?? 'MARGICK' ), 0, 25 ) ?: 'MARGICK';
	$expiry   = preg_match( '/^\d{8}$/', (string) ( $args['expiry'] ?? '' ) ) ? $args['expiry'] : '';

	// Tag 26 — PayNow merchant account info.
	$mai  = mgk_paynow_tlv( '00', 'SG.PAYNOW' );
	$mai .= mgk_paynow_tlv( '01', '2' );                          // proxy type: UEN
	$mai .= mgk_paynow_tlv( '02', $uen );                         // proxy value
	$mai .= mgk_paynow_tlv( '03', $editable ? '1' : '0' );        // amount editable
	if ( $expiry ) $mai .= mgk_paynow_tlv( '04', $expiry );

	$payload  = mgk_paynow_tlv( '00', '01' );                     // format
	$payload .= mgk_paynow_tlv( '01', $editable ? '11' : '12' );  // static vs dynamic
	$payload .= mgk_paynow_tlv( '26', $mai );
	$payload .= mgk_paynow_tlv( '52', '0000' );                   // MCC
	$payload .= mgk_paynow_tlv( '53', '702' );                    // SGD
	if ( ! $editable && $amount > 0 ) {
		$payload .= mgk_paynow_tlv( '54', number_format( $amount, 2, '.', '' ) );
	}
	$payload .= mgk_paynow_tlv( '58', 'SG' );
	$payload .= mgk_paynow_tlv( '59', $name );
	$payload .= mgk_paynow_tlv( '60', 'Singapore' );
	$payload .= mgk_paynow_tlv( '62', mgk_paynow_tlv( '01', $ref ) ); // additional data: ref

	// CRC over everything + the CRC tag id+len ("6304").
	$payload .= '63' . '04';
	$payload .= mgk_paynow_crc16( $payload );

	return $payload;
}

/**
 * High-level: the PayNow QR payload for a specific booking, using site config.
 * Returns ['payload'=>str,'amount'=>float,'reference'=>str,'uen'=>str,'payee'=>str]
 * or WP_Error when PayNow isn't configured.
 */
function mgk_paynow_payload_for_booking( $booking_id ) {
	$row = function_exists( 'mgk_get_booking_row' ) ? mgk_get_booking_row( (int) $booking_id ) : null;
	if ( ! $row ) return new WP_Error( 'mgk_no_booking', 'Booking not found.' );

	$uen   = (string) mgk_site_setting( 'paynow_uen', '' );
	$payee = (string) mgk_site_setting( 'paynow_payee', '' );
	$ref   = function_exists( 'mgk_get_payment_reference' )
		? mgk_get_payment_reference( [ 'tutor_slug' => '', 'lead_token' => '' ] )
		: $row['booking_code'];
	// Prefer the booking code as the reference (stable + unique).
	$ref = $row['booking_code'];

	$payload = mgk_paynow_build_payload( [
		'uen'           => $uen,
		'amount'        => (float) $row['price_amount'],
		'reference'     => $ref,
		'merchant_name' => $payee ?: 'MARGICK',
		'editable'      => false,
	] );
	if ( is_wp_error( $payload ) ) return $payload;

	return [
		'payload'   => $payload,
		'amount'    => (float) $row['price_amount'],
		'reference' => $ref,
		'uen'       => $uen,
		'payee'     => $payee,
	];
}
