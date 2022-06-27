<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\MerchantCenter;

use Automattic\WooCommerce\GoogleListingsAndAds\API\Google\Merchant;
use Automattic\WooCommerce\GoogleListingsAndAds\Exception\InvalidValue;
use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Service;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\WP;
use Automattic\WooCommerce\GoogleListingsAndAds\Utility\ISOUtility;
use Automattic\WooCommerce\GoogleListingsAndAds\Value\PhoneNumber;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\WC;
use Google\Service\Exception as GoogleServiceException;

defined( 'ABSPATH' ) || exit;

/**
 * Class PolicyComplianceCheck
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\MerchantCenter
 *
 * @since 1.5.0
 */
class PolicyComplianceCheck implements Service {
	/**
	 * The WC proxy object.
	 *
	 * @var wc
	 */
	protected $wc;

	/**
	 * BaseController constructor.
	 *
	 * @param WC $wc
	 */
	public function __construct( WC $wc ) {
		$this->wc = wc;
	}

	/**
	 * Get the allowed countries for the controller.
	 *
	 * @return array
	 */
	protected function get_allowed_countries(): array {
		return [ 'allowed_countries' => $this->wc->get_allowed_countries() ];
	}

	/**
	 * Check if the payment gateways is empty or not for the controller.
	 *
	 * @return bool
	 */
	protected function has_payment_gateways(): bool {
		$gateways = $this->wc->get_available_payment_gateways();
		if ( empty( $gateways ) ) {
			return [ 'payment_gateways' => false ];
		}
		return [ 'payment_gateways' => true ];
	}

	/**
	 * Check if the store is using SSL for the controller.
	 *
	 * @return bool
	 */
	protected function get_is_store_ssl(): bool {
		return [ 'ssl' => is_ssl() ];
	}

	/**
	 * Check if the store has refund return policy page for the controller.
	 *
	 * @return bool
	 */
	protected function has_refund_return_policy_page(): bool {
		if ( $this->the_slug_exists( 'refund_returns' ) ) {
			return [ 'refund_return_policy_page' => true ];
		}
		return [ 'refund_return_policy_page' => false ];
	}


	/**
	 * Check if the slug exists or not.
	 *
	 * @param PostName $post_name
	 * @return bool
	 */
	protected function the_slug_exists( $post_name ): bool {
		$args = [
			'name'        => $post_name,
			'post_type'   => 'page',
			'post_status' => 'publish',
			'numberposts' => 1,
		];

		if ( get_posts( $args ) ) {
			return true;
		}
		return false;
	}

}
