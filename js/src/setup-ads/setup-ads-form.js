/**
 * External dependencies
 */
import { isEqual } from 'lodash';
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import { Form } from '@woocommerce/components';
import { getNewPath } from '@woocommerce/navigation';
import { recordEvent } from '@woocommerce/tracks';

/**
 * Internal dependencies
 */
import useAdminUrl from '.~/hooks/useAdminUrl';
import useNavigateAwayPromptEffect from '.~/hooks/useNavigateAwayPromptEffect';
import useTargetAudienceFinalCountryCodes from '.~/hooks/useTargetAudienceFinalCountryCodes';
import useAdsSetupCompleteCallback from '.~/hooks/useAdsSetupCompleteCallback';
import SetupAdsFormContent from './setup-ads-form-content';
import validateForm from '.~/utils/paid-ads/validateForm';

/**
 * @fires gla_launch_paid_campaign_button_click on submit
 */
const SetupAdsForm = () => {
	const [ didFormChanged, setFormChanged ] = useState( false );
	const [ isSubmitted, setSubmitted ] = useState( false );
	const [ handleSetupComplete, isSubmitting ] = useAdsSetupCompleteCallback();
	const adminUrl = useAdminUrl();
	const { data: targetAudience } = useTargetAudienceFinalCountryCodes();

	const initialValues = {
		amount: 0,
		countryCodes: targetAudience,
	};

	useEffect( () => {
		if ( isSubmitted ) {
			// Force reload WC admin page to initiate the relevant dependencies of the Dashboard page.
			const nextPath = getNewPath(
				{ guide: 'campaign-creation-success' },
				'/google/dashboard'
			);
			window.location.href = adminUrl + nextPath;
		}
	}, [ isSubmitted, adminUrl ] );

	const shouldPreventLeave = didFormChanged && ! isSubmitted;

	useNavigateAwayPromptEffect(
		__(
			'You have unsaved campaign data. Are you sure you want to leave?',
			'google-listings-and-ads'
		),
		shouldPreventLeave
	);

	const handleSubmit = ( values ) => {
		const { amount, countryCodes } = values;

		recordEvent( 'gla_launch_paid_campaign_button_click', {
			audiences: countryCodes.join( ',' ),
			budget: amount,
		} );

		handleSetupComplete( amount, countryCodes, () => {
			setSubmitted( true );
		} );
	};

	const handleChange = ( _, values ) => {
		const args = [ initialValues, values ].map(
			( { countryCodes, ...v } ) => {
				v.countrySet = new Set( countryCodes );
				return v;
			}
		);

		setFormChanged( ! isEqual( ...args ) );
	};

	if ( ! targetAudience ) {
		return null;
	}

	return (
		<Form
			initialValues={ initialValues }
			validate={ validateForm }
			onChange={ handleChange }
			onSubmit={ handleSubmit }
		>
			{ ( formProps ) => {
				const mixedFormProps = {
					...formProps,
					// TODO: maybe move all API calls in useSetupCompleteCallback to ~./data
					isSubmitting,
				};
				return <SetupAdsFormContent formProps={ mixedFormProps } />;
			} }
		</Form>
	);
};

export default SetupAdsForm;
