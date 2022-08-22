<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\Tests\Unit\Shipping\GoogleAdapter;

use Automattic\WooCommerce\GoogleListingsAndAds\Exception\InvalidValue;
use Automattic\WooCommerce\GoogleListingsAndAds\Shipping\GoogleAdapter\PostcodesRateGroupAdapter;
use Automattic\WooCommerce\GoogleListingsAndAds\Shipping\ShippingLocation;
use Automattic\WooCommerce\GoogleListingsAndAds\Shipping\LocationRate;
use Automattic\WooCommerce\GoogleListingsAndAds\Shipping\PostcodeRange;
use Automattic\WooCommerce\GoogleListingsAndAds\Shipping\ShippingRate;
use Automattic\WooCommerce\GoogleListingsAndAds\Shipping\ShippingRegion;
use Automattic\WooCommerce\GoogleListingsAndAds\Tests\Framework\UnitTest;
use Google\Service\ShoppingContent\Row;

/**
 * Class PostcodesRateGroupAdapterTest
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\Tests\Unit\Shipping\GoogleAdapter
 */
class PostcodesRateGroupAdapterTest extends UnitTest {

	public function test_maps_location_rates() {
		$region_1 = new ShippingRegion( '123456', 'US', [ new PostcodeRange( '4000', '4001' ) ] );
		$region_2 = new ShippingRegion( '234567', 'AU', [ new PostcodeRange( '1000', '1001' ) ] );

		$location_rates = [
			new LocationRate( new ShippingLocation( 0, 'US', null, $region_1 ), new ShippingRate( 110 ) ),
			new LocationRate( new ShippingLocation( 4, 'AU', null, $region_2 ), new ShippingRate( 410 ) ),
		];

		$rate_group = new PostcodesRateGroupAdapter(
			[
				'location_rates'           => $location_rates,
				'currency'                 => 'USD'
			]
		);

		$table = $rate_group->getMainTable();

		$this->assertCount( 2, $table->getRowHeaders()->getPostalCodeGroupNames() );
		$this->assertCount( 2, $table->getRows() );

		$this->assertEqualSets(
			[
				'123456',
				'234567',
			],
			$table->getRowHeaders()->getPostalCodeGroupNames()
		);

		$rates = array_map(
			function ( Row $row ) {
				$this->assertCount( 1, $row->getCells() );

				return $row->getCells()[0]->getFlatRate()->getValue();
			},
			$table->getRows()
		);

		$this->assertEqualSets(
			[
				110,
				410,
			],
			$rates
		);
	}

	public function test_fails_if_no_rates_provided() {
		$this->expectException( InvalidValue::class );

		new PostcodesRateGroupAdapter(
			[
				'currency' => 'USD',
			]
		);
	}

	public function test_fails_if_no_currency_provided() {
		$this->expectException( InvalidValue::class );

		$region_1 = new ShippingRegion( '123456', 'US', [ new PostcodeRange( '4000', '4001' ) ] );

		new PostcodesRateGroupAdapter(
			[
				'location_rates' => [
					new LocationRate( new ShippingLocation( 0, 'US', null, $region_1 ), new ShippingRate( 110 ) ),
				],
			]
		);
	}
}
