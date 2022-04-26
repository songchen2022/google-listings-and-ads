/**
 * Internal dependencies
 */
import groupShippingRatesByFreeShippingThreshold from './groupShippingRatesByFreeShippingThreshold';

describe( 'groupShippingRatesByFreeShippingThreshold', () => {
	it( 'should group the shipping rates based on currency and rate', () => {
		const shippingRates = [
			{
				id: '1',
				country: 'US',
				currency: 'USD',
				rate: 20,
				options: {},
			},
			{
				id: '2',
				country: 'AU',
				currency: 'USD',
				rate: 20,
				options: {
					free_shipping_threshold: 100,
				},
			},
			{
				id: '3',
				country: 'CN',
				currency: 'USD',
				rate: 25,
				options: {
					free_shipping_threshold: 100,
				},
			},
			{
				id: '4',
				country: 'BR',
				currency: 'USD',
				rate: 20,
				options: {
					free_shipping_threshold: 250,
				},
			},
		];

		const result = groupShippingRatesByFreeShippingThreshold(
			shippingRates
		);

		expect( result.length ).toEqual( 3 );
		expect( result[ 0 ] ).toStrictEqual( {
			countries: [ 'US' ],
			currency: 'USD',
			threshold: undefined,
		} );
		expect( result[ 1 ] ).toStrictEqual( {
			countries: [ 'AU', 'CN' ],
			currency: 'USD',
			threshold: 100,
		} );
		expect( result[ 2 ] ).toStrictEqual( {
			countries: [ 'BR' ],
			currency: 'USD',
			threshold: 250,
		} );
	} );
} );
