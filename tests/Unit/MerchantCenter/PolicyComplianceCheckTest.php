<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\Tests\Unit\MerchantCenter;

use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Service;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\WC;
use Automattic\WooCommerce\GoogleListingsAndAds\Google\GoogleHelper;
use Automattic\WooCommerce\GoogleListingsAndAds\PluginHelper;
use Automattic\WooCommerce\GoogleListingsAndAds\MerchantCenter\TargetAudience;

use PHPUnit\Framework\MockObject\MockObject;

defined( 'ABSPATH' ) || exit;

/**
 * Class PolicyComplianceCheckTest
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\Tests\Unit\MerchantCenter
 *
 * @property  MockObject|Merchant $merchant
 * @property  MockObject|Settings $google_settings
 * @property  ContactInformation  $contact_information
 */
class PolicyComplianceCheckTest extends UnitTest {

	use MerchantTrait;

	/**
	 * Runs before each test is executed.
	 */
	public function setUp(): void {
		parent::setUp();
		$this->wc            = $this->createMock( WC::class );
		$this->google_helper     = $this->createMock( GoogleHelper::class );
		$this->target_audience     = $this->createMock( TargetAudience::class );

		$this->policy_compliance_check = new PolicyComplianceCheck( $this->wc, $this->google_helper, $this->target_audience );
	}



	public function test_website_accessible() {
		$this->wc->expects( $this->any() )
					   ->method( 'get_allowed_countries' )
					   ->willReturn( ["AU", "AT", "CA", "US"] );
		$this->target_audience->expects( $this->any() )
					   ->method( 'get_target_countries' )
					   ->willReturn( ["AU", "US"] );

		$this->assertEquals($this->policy_compliance_check->is_accessible(), true);
	}


	public function test_website_not_accessible() {
		$this->wc->expects( $this->any() )
					   ->method( 'get_allowed_countries' )
					   ->willReturn( ["AU", "AT", "CA", "US"] );
		$this->target_audience->expects( $this->any() )
					   ->method( 'get_target_countries' )
					   ->willReturn( ["FR", "US"] );

		$this->assertEquals($this->policy_compliance_check->is_accessible(), false);
	}


	public function test_payment_gateways() {
		$this->wc->expects( $this->any() )
					   ->method( 'get_available_payment_gateways' )
					   ->willReturn( ["PayPal", "Stripe"] );

		$this->assertEquals($this->policy_compliance_check->has_payment_gateways(), false);
	}
}
