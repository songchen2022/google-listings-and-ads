<?php
declare( strict_types=1 );

/**
 * Overrides vendor/googleads/google-ads-php/src/Google/Ads/GoogleAds/Lib/V6/ServiceClientFactoryTrait.php
 *
 * phpcs:disable WordPress.NamingConventions.ValidFunctionName.MethodNameInvalid
 * phpcs:disable WordPress.NamingConventions.ValidVariableName
 * phpcs:disable Squiz.Commenting.VariableComment
 */

namespace Automattic\WooCommerce\GoogleListingsAndAds\Google\Ads;

use Google\Ads\GoogleAds\Lib\ConfigurationTrait;
use Google\Ads\GoogleAds\V6\Services\AccountBudgetProposalServiceClient;
use Google\Ads\GoogleAds\V6\Services\AccountBudgetServiceClient;
use Google\Ads\GoogleAds\V6\Services\AccountLinkServiceClient;
use Google\Ads\GoogleAds\V6\Services\AdGroupAdLabelServiceClient;
use Google\Ads\GoogleAds\V6\Services\AdGroupAdServiceClient;
use Google\Ads\GoogleAds\V6\Services\AdGroupCriterionServiceClient;
use Google\Ads\GoogleAds\V6\Services\AdGroupServiceClient;
use Google\Ads\GoogleAds\V6\Services\AdServiceClient;
use Google\Ads\GoogleAds\V6\Services\BillingSetupServiceClient;
use Google\Ads\GoogleAds\V6\Services\CampaignBudgetServiceClient;
use Google\Ads\GoogleAds\V6\Services\CampaignServiceClient;
use Google\Ads\GoogleAds\V6\Services\ConversionActionServiceClient;
use Google\Ads\GoogleAds\V6\Services\CustomerServiceClient;
use Google\Ads\GoogleAds\V6\Services\GoogleAdsServiceClient;
use Google\Ads\GoogleAds\V6\Services\MerchantCenterLinkServiceClient;

/**
 * Contains service client factory methods.
 */
trait ServiceClientFactoryTrait {
	use ConfigurationTrait;

	private static $CREDENTIALS_LOADER_KEY  = 'credentials';
	private static $DEVELOPER_TOKEN_KEY     = 'developer-token';
	private static $LOGIN_CUSTOMER_ID_KEY   = 'login-customer-id';
	private static $LINKED_CUSTOMER_ID_KEY  = 'linked-customer-id';
	private static $SERVICE_ADDRESS_KEY     = 'serviceAddress';
	private static $DEFAULT_SERVICE_ADDRESS = 'googleads.googleapis.com';
	private static $TRANSPORT_KEY           = 'transport';

	/**
	 * Gets the Google Ads client options for making API calls.
	 *
	 * @return array the client options
	 */
	public function getGoogleAdsClientOptions() {
		$clientOptions = [
			self::$CREDENTIALS_LOADER_KEY => $this->getOAuth2Credential(),
			self::$DEVELOPER_TOKEN_KEY    => '',
			self::$TRANSPORT_KEY          => 'rest',
		];

		if ( ! empty( $this->getEndpoint() ) ) {
			$clientOptions += [ self::$SERVICE_ADDRESS_KEY => $this->getEndpoint() ];
		}

		if ( isset( $this->httpClient ) ) {
			$clientOptions['transportConfig'] = [
				'rest' => [
					'httpHandler' => $this->buildHttpHandler(),
				],
			];
		}

		return $clientOptions;
	}

	/**
	 * @return AccountBudgetProposalServiceClient
	 */
	public function getAccountBudgetProposalServiceClient() {
		return new AccountBudgetProposalServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return AccountBudgetServiceClient
	 */
	public function getAccountBudgetServiceClient() {
		return new AccountBudgetServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return AccountLinkServiceClient
	 */
	public function getAccountLinkServiceClient() {
		return new AccountLinkServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return AdGroupAdServiceClient
	 */
	public function getAdGroupAdServiceClient() {
		return new AdGroupAdServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return AdGroupAdLabelServiceClient
	 */
	public function getAdGroupAdLabelServiceClient() {
		return new AdGroupAdLabelServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return AdGroupCriterionServiceClient
	 */
	public function getAdGroupCriterionServiceClient() {
		return new AdGroupCriterionServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return AdGroupServiceClient
	 */
	public function getAdGroupServiceClient() {
		return new AdGroupServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return AdServiceClient
	 */
	public function getAdServiceClient() {
		return new AdServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return BillingSetupServiceClient
	 */
	public function getBillingSetupServiceClient() {
		return new BillingSetupServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return CampaignBudgetServiceClient
	 */
	public function getCampaignBudgetServiceClient() {
		return new CampaignBudgetServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return CampaignServiceClient
	 */
	public function getCampaignServiceClient() {
		return new CampaignServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return ConversionActionServiceClient
	 */
	public function getConversionActionServiceClient() {
		return new ConversionActionServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return CustomerServiceClient
	 */
	public function getCustomerServiceClient() {
		return new CustomerServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return GoogleAdsServiceClient
	 */
	public function getGoogleAdsServiceClient() {
		return new GoogleAdsServiceClient( $this->getGoogleAdsClientOptions() );
	}

	/**
	 * @return MerchantCenterLinkServiceClient
	 */
	public function getMerchantCenterLinkServiceClient() {
		return new MerchantCenterLinkServiceClient( $this->getGoogleAdsClientOptions() );
	}
}
