/**
 * External dependencies
 */
import { apiFetch } from '@wordpress/data-controls';
import { dispatch } from '@wordpress/data';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import TYPES from './action-types';
import { API_NAMESPACE } from './constants';

export function handleFetchError( error, message ) {
	const { createNotice } = dispatch( 'core/notices' );
	createNotice( 'error', message );

	// eslint-disable-next-line no-console
	console.log( error );
}

export function* fetchShippingRates() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/shipping/rates`,
		} );

		const shippingRates = Object.values( response ).map( ( el ) => {
			return {
				countryCode: el.country_code,
				currency: el.currency,
				rate: el.rate.toString(),
			};
		} );

		return {
			type: TYPES.RECEIVE_SHIPPING_RATES,
			shippingRates,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading shipping rates.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* upsertShippingRates( shippingRate ) {
	const { countryCodes, currency, rate } = shippingRate;

	try {
		yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/shipping/rates/batch`,
			method: 'POST',
			data: {
				country_codes: countryCodes,
				currency,
				rate,
			},
		} );

		return {
			type: TYPES.UPSERT_SHIPPING_RATES,
			shippingRate,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error trying to add / update shipping rates.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* deleteShippingRates( countryCodes ) {
	try {
		yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/shipping/rates/batch`,
			method: 'DELETE',
			data: {
				country_codes: countryCodes,
			},
		} );

		return {
			type: TYPES.DELETE_SHIPPING_RATES,
			countryCodes,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error trying to delete shipping rates.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* fetchShippingTimes() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/shipping/times`,
		} );

		const shippingTimes = Object.values( response ).map( ( el ) => {
			return {
				countryCode: el.country_code,
				time: el.time,
			};
		} );

		return {
			type: TYPES.RECEIVE_SHIPPING_TIMES,
			shippingTimes,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading shipping times.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* upsertShippingTimes( shippingTime ) {
	const { countryCodes, time } = shippingTime;

	try {
		yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/shipping/times/batch`,
			method: 'POST',
			data: {
				country_codes: countryCodes,
				time,
			},
		} );

		return {
			type: TYPES.UPSERT_SHIPPING_TIMES,
			shippingTime,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error trying to add / update shipping times.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* deleteShippingTimes( countryCodes ) {
	try {
		yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/shipping/times/batch`,
			method: 'DELETE',
			data: {
				country_codes: countryCodes,
			},
		} );

		return {
			type: TYPES.DELETE_SHIPPING_TIMES,
			countryCodes,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error trying to delete shipping times.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* fetchSettings() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/settings`,
		} );

		return {
			type: TYPES.RECEIVE_SETTINGS,
			settings: response,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading merchant center settings.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* saveSettings( settings ) {
	try {
		yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/settings`,
			method: 'POST',
			data: settings,
		} );

		return {
			type: TYPES.SAVE_SETTINGS,
			settings,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error trying to save settings.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* fetchJetpackAccount() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/jetpack/connected`,
		} );

		return {
			type: TYPES.RECEIVE_ACCOUNTS_JETPACK,
			account: response,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading Jetpack account info.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* fetchGoogleAccount() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/google/connected`,
		} );

		return {
			type: TYPES.RECEIVE_ACCOUNTS_GOOGLE,
			account: response,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading Google account info.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* fetchGoogleMCAccount() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/connection`,
		} );

		return {
			type: TYPES.RECEIVE_ACCOUNTS_GOOGLE_MC,
			account: response,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading Google Merchant Center account info.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* fetchExistingGoogleMCAccounts() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/accounts`,
		} );

		return {
			type: TYPES.RECEIVE_ACCOUNTS_GOOGLE_MC_EXISTING,
			accounts: response,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error getting your Google Merchant Center accounts.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* fetchGoogleAdsAccount() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/ads/connection`,
		} );

		return {
			type: TYPES.RECEIVE_ACCOUNTS_GOOGLE_ADS,
			account: response,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading Google Ads account info.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* disconnectGoogleAdsAccount() {
	try {
		yield apiFetch( {
			path: `${ API_NAMESPACE }/ads/connection`,
			method: 'DELETE',
		} );

		return {
			type: TYPES.DISCONNECT_ACCOUNTS_GOOGLE_ADS,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'Unable to disconnect your Google Ads account. Please try again later.',
				'google-listings-and-ads'
			)
		);
		throw error;
	}
}

export function* disconnectAllAccounts() {
	try {
		yield apiFetch( {
			path: `${ API_NAMESPACE }/connections`,
			method: 'DELETE',
		} );

		return {
			type: TYPES.DISCONNECT_ACCOUNTS_ALL,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'Unable to disconnect all your accounts. Please try again later.',
				'google-listings-and-ads'
			)
		);
		throw error;
	}
}

export function* fetchGoogleAdsAccountBillingStatus() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/ads/billing-status`,
		} );

		return receiveGoogleAdsAccountBillingStatus( response );
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error getting the billing status of your Google Ads account.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* fetchExistingGoogleAdsAccounts() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/ads/accounts`,
		} );

		return {
			type: TYPES.RECEIVE_ACCOUNTS_GOOGLE_ADS_EXISTING,
			accounts: response,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error getting your Google Ads accounts.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* fetchCountries() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/countries`,
		} );

		return {
			type: TYPES.RECEIVE_COUNTRIES,
			countries: response,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading supported country details.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* fetchTargetAudience() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/target_audience`,
		} );

		return {
			type: TYPES.RECEIVE_TARGET_AUDIENCE,
			target_audience: response,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading target audience.',
				'google-listings-and-ads'
			)
		);
	}
}

export function receiveMCAccount( account ) {
	return {
		type: TYPES.RECEIVE_ACCOUNTS_GOOGLE_MC,
		account,
	};
}

export function receiveAdsAccount( account ) {
	return {
		type: TYPES.RECEIVE_ACCOUNTS_GOOGLE_ADS,
		account,
	};
}

export function receiveGoogleAdsAccountBillingStatus( billingStatus ) {
	return {
		type: TYPES.RECEIVE_ACCOUNTS_GOOGLE_ADS_BILLING_STATUS,
		billingStatus,
	};
}

export function* saveTargetAudience( targetAudience ) {
	try {
		yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/target_audience`,
			method: 'POST',
			data: targetAudience,
		} );

		return {
			type: TYPES.SAVE_TARGET_AUDIENCE,
			target_audience: targetAudience,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error saving target audience data.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* fetchAdsCampaigns() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/ads/campaigns`,
		} );

		return receiveAdsCampaigns( response );
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading ads campaigns.',
				'google-listings-and-ads'
			)
		);
	}
}

export function receiveAdsCampaigns( adsCampaigns ) {
	return {
		type: TYPES.RECEIVE_ADS_CAMPAIGNS,
		adsCampaigns,
	};
}

// TODO: deprecated actions to be removed after relevant actions migrating to corresponding batch actions.
export function* upsertShippingRate( shippingRate ) {
	const { countryCode, currency, rate } = shippingRate;
	yield upsertShippingRates( {
		countryCodes: [ countryCode ],
		currency,
		rate,
	} );
}

export function* deleteShippingRate( countryCode ) {
	yield deleteShippingRates( [ countryCode ] );
}

export function* upsertShippingTime( shippingTime ) {
	const { countryCode, time } = shippingTime;
	yield upsertShippingTimes( { countryCodes: [ countryCode ], time } );
}

export function* deleteShippingTime( countryCode ) {
	yield deleteShippingTimes( [ countryCode ] );
}
