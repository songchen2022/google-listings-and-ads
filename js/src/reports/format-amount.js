/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { numberFormat } from '@woocommerce/number';

/**
 * Internal dependencies
 */
import formatAmountWithCode from '.~/utils/formatAmountWithCode';

const unavailable = __( 'Unavailable', 'google-listings-and-ads' );

/**
 * @typedef {import('.~/utils/formatAmountWithCode').CurencyConfig} CurencyConfig
 */

/**
 * Formats given number as currency
 * according to given config, or return `"Unavailable"` if the value is undefined.
 *
 * Entire usage could be simplified once
 * https://github.com/woocommerce/woocommerce-admin/pull/7575 is released and accessible.
 *
 * @param {number} value Value to be formatted.
 * @param {CurencyConfig} currencyConfig Currency config used to format amount.
 * @return {string} Formatted currency or "Unavailable".
 */
function formatCurrencyCell( value, currencyConfig ) {
	if ( value === undefined ) {
		return unavailable;
	}
	return formatAmountWithCode( currencyConfig, value );
}
/**
 * Formats given number as Ads' currency
 * or return `"Unavailable"` if the value is undefined.
 *
 * @param {number} value Value to be formatted.
 * @param {CurencyConfig} storeCurrencyConfig Store's currency config. It's not used, it's kept to conform Metric.formatFn API.
 * @param {CurencyConfig} adsCurrencyConfig Ads' currency config. May be fetched from {@link .~/hooks/useAdsCurrency.useAdsCurrencyConfig}.
 * @return {string} Formatted currency or "Unavailable".
 */
export function formatAdsCurrencyCell(
	value,
	storeCurrencyConfig,
	adsCurrencyConfig
) {
	return formatCurrencyCell( value, adsCurrencyConfig );
}
/**
 * Formats given number as store's currency
 * or return `"Unavailable"` if the value is undefined.
 *
 * @param {number} value Value to be formatted.
 * @param {CurencyConfig} storeCurrencyConfig Store's currency config.
 * @return {string} Formatted currency or "Unavailable".
 */
export function formatStoreCurrencyCell( value, storeCurrencyConfig ) {
	return formatCurrencyCell( value, storeCurrencyConfig );
}
/**
 * Formats given number according to given config,
 * or return `"Unavailable"` if the value is undefined.
 *
 * We do not use currency code or symbol, but decimal separators, etc.
 *
 * @param {number} value Value to be formatted.
 * @param {CurencyConfig} currencyConfig Currency config.
 * @return {string} Formatted number or "Unavailable".
 */
export function formatNumericCell( value, currencyConfig ) {
	if ( value === undefined ) {
		return unavailable;
	}
	return numberFormat(
		{
			...currencyConfig,
			precision: 0,
		},
		value
	);
}
