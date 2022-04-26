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
 * @package Automattic\WooCommerce\GoogleListingsAndAds\API\Site\Controllers
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
			'mc/policy_check',
			[
				[
					'methods'             => TransportMethods::READABLE,
					'callback'            => $this->get_policy_check_callback(),
					'permission_callback' => $this->get_permission_callback(),
				],
			]
		);
	}

	/**
	 * Get the callback function for returning supported countries.
	 *
	 * @return callable
	 */
	protected function get_policy_check_callback(): callable {
		return function() {
			// return $this->get_supported_countries();
      return array(
        "allowed_countries" => $this->get_allowed_countries(),
        "payment_gateways" => $this->get_has_payment_gateways(),
        "is_ssl" => $this->get_is_ssl(),
        "has_refund_return_policy_page_content" => $this->get_has_refund_return_policy_page_content()
      );
		};
	}

	/**
	 * Get the array of supported countries.
	 *
	 * @return array
	 */
	protected function get_supported_countries(): array {
		$all_countries = $this->wc->get_countries();
		$mc_countries  = $this->google_helper->get_mc_supported_countries_currencies();

		$supported = [];
		foreach ( $mc_countries as $country => $currency ) {
			if ( ! array_key_exists( $country, $all_countries ) ) {
				continue;
			}

			$supported[ $country ] = [
				'name'     => $all_countries[ $country ],
				'currency' => $currency,
			];
		}

		uasort(
			$supported,
			function( $a, $b ) {
				return $a['name'] <=> $b['name'];
			}
		);

		return $supported;
	}

	/**
	 * Get the item schema name for the controller.
	 *
	 * Used for building the API response schema.
	 *
	 * @return array
	 */
	protected function get_allowed_countries(): array {
    return $this->wc->get_allowed_countries();
	}

  	/**
	 * Get the item schema name for the controller.
	 *
	 * Used for building the API response schema.
	 *
	 * @return string
	 */
	protected function get_has_payment_gateways(): string {
		$gateways = $this->wc->get_available_payment_gateways();
    if ( empty ( $gateways)) {
      return false;
    }
    else {
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
	protected function get_is_ssl(): string {
		return is_ssl();
	}

    	/**
	 * Get the item schema name for the controller.
	 *
	 * Used for building the API response schema.
	 *
	 * @return string
	 */
	protected function get_has_refund_return_policy_page_content(): string {
		$results = WC_Install::get_refunds_return_policy_page_content();
    if ( empty( $results ) ) {
      return false;
    }
    else {
      return true;
    }
	}
}
