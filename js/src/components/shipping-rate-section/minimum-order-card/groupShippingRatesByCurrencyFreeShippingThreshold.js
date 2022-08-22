/**
 * @typedef { import(".~/data/actions").ShippingRate } ShippingRate
 * @typedef { import("./typedefs").MinimumOrderGroup } MinimumOrderGroup
 */

/**
 * Group shipping rates by currency and free shipping threshold into minimum order groups.
 *
 * @param {Array<ShippingRate>} shippingRates Array of shipping rates.
 * @return {Array<MinimumOrderGroup>} Array of minimum order groups.
 */
const groupShippingRatesByCurrencyFreeShippingThreshold = ( shippingRates ) => {
	const map = new Map();

	shippingRates.forEach( ( shippingRate ) => {
		const {
			options: { free_shipping_threshold: threshold },
			currency,
		} = shippingRate;
		const key = `${ threshold } ${ currency }`;
		const group = map.get( key ) || {
			countries: [],
			threshold,
			currency,
		};
		group.countries.push( shippingRate.country );
		map.set( key, group );
	} );

	return Array.from( map.values() );
};

export default groupShippingRatesByCurrencyFreeShippingThreshold;
