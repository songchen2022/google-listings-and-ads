<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\API\Site\Controllers\MerchantCenter;

use Automattic\WooCommerce\GoogleListingsAndAds\API\Site\Controllers\BaseController;
use Automattic\WooCommerce\GoogleListingsAndAds\API\Site\Controllers\CountryCodeTrait;
use Automattic\WooCommerce\GoogleListingsAndAds\API\Site\Controllers\EmptySchemaPropertiesTrait;
use Automattic\WooCommerce\GoogleListingsAndAds\API\TransportMethods;
use Automattic\WooCommerce\GoogleListingsAndAds\Google\GoogleHelper;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\RESTServer;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\WC;

defined( 'ABSPATH' ) || exit;

/**
 * Class PolicyComplianceCheckController
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\API\Site\Controllers\MerchantCenter
 */
class PolicyComplianceCheckController extends BaseController {

	use CountryCodeTrait;
	use EmptySchemaPropertiesTrait;

	/**
	 * The WC proxy object.
	 *
	 * @var WC
	 */
	protected $wc;

	/**
	 * @var GoogleHelper
	 */
	protected $google_helper;

	/**
	 * BaseController constructor.
	 *
	 * @param RESTServer   $server
	 * @param WC           $wc
	 * @param GoogleHelper $google_helper
	 */
	public function __construct( RESTServer $server, WC $wc, GoogleHelper $google_helper ) {
		parent::__construct( $server );
		$this->wc            = $wc;
		$this->google_helper = $google_helper;
	}

	/**
	 * Register rest routes with WordPress.
	 */
	public function register_routes(): void {
		$this->register_route(
			'mc/policy_check/allowed_countries',
			[
				[
					'methods'             => TransportMethods::READABLE,
					'callback'            => $this->get_allowed_countries(),
					'permission_callback' => $this->get_permission_callback(),
				],
			]
		);

		$this->register_route(
			'mc/policy_check/payment_gateways',
			[
				[
					'methods'             => TransportMethods::READABLE,
					'callback'            => $this->has_payment_gateways(),
					'permission_callback' => $this->get_permission_callback(),
				],
			]
		);

		$this->register_route(
			'mc/policy_check/ssl',
			[
				[
					'methods'             => TransportMethods::READABLE,
					'callback'            => $this->get_is_ssl(),
					'permission_callback' => $this->get_permission_callback(),
				],
			]
		);

		$this->register_route(
			'mc/policy_check/return_refund_policy',
			[
				[
					'methods'             => TransportMethods::READABLE,
					'callback'            => $this->has_refund_return_policy_page_content(),
					'permission_callback' => $this->get_permission_callback(),
				],
			]
		);
	}

	/**
	 * Get the allowed countries for the controller.
	 *
	 * @return array
	 */
	protected function get_allowed_countries(): array {
		return $this->wc->get_countries();
	}

	/**
	 * Check if the payment gateways is empty or not for the controller.
	 *
	 * @return bool
	 */
	protected function has_payment_gateways(): bool {
		$gateways = $this->wc->get_available_payment_gateways();
		if ( empty( $gateways ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Check if the store is using SSL for the controller.
	 *
	 * @return bool
	 */
	protected function get_is_ssl(): bool {
		return is_ssl();
	}

	/**
	 * Check if the store has refund return policy page content for the controller.
	 *
	 * @return bool
	 */
	protected function has_refund_return_policy_page_content(): bool {
		$results = WC_Install::get_refunds_return_policy_page_content();
		if ( empty( $results ) ) {
			return false;
		} else {
			return true;
		}
	}

	/**
	 * Get the item schema name for the controller.
	 *
	 * Used for building the API response schema.
	 *
	 * @return string
	 */
	protected function get_schema_title(): string {
		return 'policy_check';
	}
}
