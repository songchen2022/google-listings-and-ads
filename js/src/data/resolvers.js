/**
 * External dependencies
 */
import { apiFetch } from '@wordpress/data-controls';
import { addQueryArgs } from '@wordpress/url';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import {
	REPORT_SOURCE_PAID,
	REPORT_SOURCE_FREE,
	ISSUE_TYPE_ACCOUNT,
} from '.~/constants';
import TYPES from './action-types';
import { API_NAMESPACE } from './constants';
import { getReportKey } from './utils';
import { adaptAdsCampaign } from './adapters';

import {
	handleFetchError,
	fetchShippingRates,
	fetchShippingTimes,
	fetchSettings,
	fetchJetpackAccount,
	fetchGoogleAccount,
	fetchGoogleMCAccount,
	fetchExistingGoogleMCAccounts,
	fetchGoogleAdsAccount,
	fetchGoogleAdsAccountBillingStatus,
	fetchExistingGoogleAdsAccounts,
	receiveGoogleMCContactInformation,
	fetchTargetAudience,
	fetchMCSetup,
	receiveGoogleAccountAccess,
	receiveReport,
	receiveMCProductStatistics,
	receiveMCIssues,
	receiveMCProductFeed,
	receiveMCReviewRequest,
} from './actions';

export function* getShippingRates() {
	yield fetchShippingRates();
}

export function* getShippingTimes() {
	yield fetchShippingTimes();
}

export function* getSettings() {
	yield fetchSettings();
}

export function* getJetpackAccount() {
	yield fetchJetpackAccount();
}

export function* getGoogleAccount() {
	yield fetchGoogleAccount();
}

getGoogleAccount.shouldInvalidate = ( action ) => {
	return action.type === TYPES.DISCONNECT_ACCOUNTS_GOOGLE;
};

export function* getGoogleAccountAccess() {
	try {
		const data = yield apiFetch( {
			path: `${ API_NAMESPACE }/google/reconnected`,
		} );

		yield receiveGoogleAccountAccess( data );
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading Google account access info.',
				'google-listings-and-ads'
			)
		);
	}
}

getGoogleAccountAccess.shouldInvalidate = ( action ) => {
	return action.type === TYPES.DISCONNECT_ACCOUNTS_GOOGLE;
};

export function* getGoogleMCAccount() {
	yield fetchGoogleMCAccount();
}

export function* getExistingGoogleMCAccounts() {
	yield fetchExistingGoogleMCAccounts();
}

export function* getGoogleAdsAccount() {
	yield fetchGoogleAdsAccount();
}

export function* getGoogleAdsAccountBillingStatus() {
	yield fetchGoogleAdsAccountBillingStatus();
}

export function* getExistingGoogleAdsAccounts() {
	yield fetchExistingGoogleAdsAccounts();
}

export function* getGoogleMCContactInformation() {
	try {
		const data = yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/contact-information`,
		} );
		yield receiveGoogleMCContactInformation( data );
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading Google Merchant Center contact information.',
				'google-listings-and-ads'
			)
		);
	}
}

getGoogleMCContactInformation.shouldInvalidate = ( action ) => {
	return action.type === TYPES.VERIFIED_MC_PHONE_NUMBER;
};

export function* getMCCountriesAndContinents() {
	try {
		const query = { continents: true };
		const path = addQueryArgs( `${ API_NAMESPACE }/mc/countries`, query );
		const data = yield apiFetch( { path } );

		return {
			type: TYPES.RECEIVE_MC_COUNTRIES_AND_CONTINENTS,
			data,
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

/**
 * Fetch policy info for checking merchant onboarding policy setting.
 */
export function* getPolicyCheck() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/policy_check`,
		} );

		return {
			type: TYPES.POLICY_CHECK,
			data: response,
		};
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading policy check details.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* getTargetAudience() {
	yield fetchTargetAudience();
}

export function* getAdsCampaigns( query ) {
	try {
		const campaigns = yield apiFetch( {
			path: addQueryArgs( `${ API_NAMESPACE }/ads/campaigns`, query ),
		} );

		return {
			type: TYPES.RECEIVE_ADS_CAMPAIGNS,
			query,
			adsCampaigns: campaigns.map( adaptAdsCampaign ),
		};
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

getAdsCampaigns.shouldInvalidate = ( action, query ) => {
	return (
		( action.type === TYPES.UPDATE_ADS_CAMPAIGN ||
			action.type === TYPES.DELETE_ADS_CAMPAIGN ||
			action.type === TYPES.CREATE_ADS_CAMPAIGN ) &&
		query?.exclude_removed === false
	);
};

export function* getMCSetup() {
	yield fetchMCSetup();
}

export function* getMCProductStatistics() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/product-statistics`,
		} );

		yield receiveMCProductStatistics( response );
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading your merchant center product statistics.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* getMCReviewRequest() {
	try {
		const response = yield apiFetch( {
			path: `${ API_NAMESPACE }/mc/review`,
		} );

		yield receiveMCReviewRequest( response );
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading your merchant center product review request status.',
				'google-listings-and-ads'
			)
		);
	}
}

export function* getMCIssues( query ) {
	try {
		const { issue_type: issueType, ...args } = query;

		const response = yield apiFetch( {
			path: addQueryArgs(
				`${ API_NAMESPACE }/mc/issues/${
					issueType || ISSUE_TYPE_ACCOUNT
				}`,
				args
			),
		} );

		yield receiveMCIssues( query, response );
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading issues to resolve.',
				'google-listings-and-ads'
			)
		);
	}
}

getMCIssues.shouldInvalidate = ( action ) => {
	return action.type === TYPES.UPDATE_MC_PRODUCTS_VISIBILITY;
};

export function* getMCProductFeed( query ) {
	try {
		const response = yield apiFetch( {
			path: addQueryArgs( `${ API_NAMESPACE }/mc/product-feed`, query ),
		} );

		yield receiveMCProductFeed( query, response );
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading product feed.',
				'google-listings-and-ads'
			)
		);
	}
}

getMCProductFeed.shouldInvalidate = ( action, query ) => {
	if ( action.type === TYPES.UPDATE_MC_PRODUCTS_VISIBILITY ) {
		return true;
	}

	return (
		action.type === TYPES.RECEIVE_MC_PRODUCT_FEED &&
		( action.query.per_page !== query.per_page ||
			action.query.orderby !== query.orderby ||
			action.query.order !== query.order )
	);
};

const reportTypeMap = new Map( [
	[ REPORT_SOURCE_FREE, 'mc' ],
	[ REPORT_SOURCE_PAID, 'ads' ],
] );

export function* getReportByApiQuery( category, type, reportQuery ) {
	const reportType = reportTypeMap.get( type );
	const url = `${ API_NAMESPACE }/${ reportType }/reports/${ category }`;
	const path = addQueryArgs( url, reportQuery );

	try {
		const data = yield apiFetch( { path } );
		const reportKey = getReportKey( category, type, reportQuery );
		yield receiveReport( reportKey, data );
	} catch ( error ) {
		yield handleFetchError(
			error,
			__(
				'There was an error loading report.',
				'google-listings-and-ads'
			)
		);
	}
}
