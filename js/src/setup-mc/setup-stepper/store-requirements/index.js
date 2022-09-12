/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useState, useEffect } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { Form } from '@woocommerce/components';
import { getNewPath } from '@woocommerce/navigation';
import { isEqual } from 'lodash';

/**
 * Internal dependencies
 */
import { useAppDispatch } from '.~/data';
import useAdminUrl from '.~/hooks/useAdminUrl';
import useStoreAddress from '.~/hooks/useStoreAddress';
import useSettings from '.~/components/free-listings/configure-product-listings/useSettings';
import useDispatchCoreNotices from '.~/hooks/useDispatchCoreNotices';
import StepContent from '.~/components/stepper/step-content';
import StepContentHeader from '.~/components/stepper/step-content-header';
import StepContentFooter from '.~/components/stepper/step-content-footer';
import ContactInformation from '.~/components/contact-information';
import AppButton from '.~/components/app-button';
import AppSpinner from '.~/components/app-spinner';
import PreLaunchChecklist from './pre-launch-checklist';
import usePolicyCheck from '.~/hooks/usePolicyCheck';
import checkErrors from './pre-launch-checklist/checkErrors';

export default function StoreRequirements() {
	const adminUrl = useAdminUrl();
	const { updateGoogleMCContactInformation, saveSettings } = useAppDispatch();
	const { createNotice } = useDispatchCoreNotices();
	const { data: address } = useStoreAddress();
	const { settings } = useSettings();
	const { data: policyCheckData } = usePolicyCheck();

	/**
	 * Since it still lacking the phone verification state,
	 * all onboarding accounts are considered unverified phone numbers.
	 */
	const [ isPhoneNumberReady, setPhoneNumberReady ] = useState( false );
	const [ settingsSaved, setSettingsSaved ] = useState( true );
	const [ preprocessed, setPreprocessed ] = useState( false );
	const [ completing, setCompleting ] = useState( false );

	const handleChangeCallback = async ( _, values ) => {
		try {
			await saveSettings( values );
			setSettingsSaved( true );
		} catch ( error ) {
			//Create the notice only once
			if ( settingsSaved ) {
				createNotice(
					'error',
					__(
						'There was an error trying to save settings. Please try again later.',
						'google-listings-and-ads'
					)
				);
			}
			setSettingsSaved( false );
		}
	};

	const handleSubmitCallback = async () => {
		try {
			setCompleting( true );

			await updateGoogleMCContactInformation();

			await apiFetch( {
				path: '/wc/gla/mc/settings/sync',
				method: 'POST',
			} );

			// Force reload WC admin page to initiate the relevant dependencies of the Dashboard page.
			const path = getNewPath(
				{ guide: 'submission-success' },
				'/google/product-feed'
			);
			window.location.href = adminUrl + path;
		} catch ( error ) {
			setCompleting( false );

			createNotice(
				'error',
				__(
					'Unable to complete your setup. Please try again later.',
					'google-listings-and-ads'
				)
			);
		}
	};

	// Preprocess the auto-checked state and data saving.
	useEffect( () => {
		if ( preprocessed || ! settings || ! policyCheckData ) {
			return;
		}

		const newSettings = { ...settings };

		const websiteLive =
			policyCheckData.allowed_countries &&
			! policyCheckData.robots_restriction &&
			! policyCheckData.page_not_found_error &&
			! policyCheckData.page_redirects;

		if ( websiteLive !== settings.website_live ) {
			newSettings.website_live = websiteLive;
		}

		if ( policyCheckData.store_ssl !== settings.checkout_process_secure ) {
			newSettings.checkout_process_secure = policyCheckData.store_ssl;
		}

		if ( policyCheckData.refund_returns !== settings.refund_tos_visible ) {
			newSettings.refund_tos_visible = policyCheckData.refund_returns;
		}

		if (
			policyCheckData.payment_gateways !==
			newSettings.payment_methods_visible
		) {
			newSettings.payment_methods_visible =
				policyCheckData.payment_gateways;
		}

		const promise = isEqual( newSettings, settings )
			? Promise.resolve()
			: saveSettings( newSettings );

		promise.finally( () => setPreprocessed( true ) );
	}, [ preprocessed, policyCheckData, settings, saveSettings ] );

	if ( ! preprocessed ) {
		return <AppSpinner />;
	}

	return (
		<StepContent>
			<StepContentHeader
				title={ __(
					'[internal] Confirm store requirements',
					'google-listings-and-ads'
				) }
				description={ __(
					'Review and confirm that your store meets Google Merchant Center requirements.',
					'google-listings-and-ads'
				) }
			/>
			<Form
				initialValues={
					{
						website_live: settings.website_live,
						checkout_process_secure: settings.checkout_process_secure,
						payment_methods_visible: settings.payment_methods_visible,
						refund_tos_visible: settings.refund_tos_visible,
						contact_info_visible: settings.contact_info_visible,
					}
				}
				validate={ checkErrors }
				onChange={ handleChangeCallback }
				onSubmit={ handleSubmitCallback }
			>
				{ ( formProps ) => {
					const { handleSubmit, isValidForm } = formProps;

					const isReadyToComplete =
						isValidForm &&
						isPhoneNumberReady &&
						address.isAddressFilled &&
						settingsSaved;

					return (
						<>
							<PreLaunchChecklist formProps={ formProps } />
						</>
					);
				} }
			</Form>
		</StepContent>
	);
}
