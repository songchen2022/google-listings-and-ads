/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { useSelect } from '@wordpress/data';

/**
 * Internal dependencies
 */
import AppSpinner from '../../../../components/app-spinner';
import { STORE_KEY } from '../../../../data/constants';
import TitleButtonLayout from '../title-button-layout';
import ConnectAccount from './connect-account';

const CardContent = ( props ) => {
	const { disabled } = props;

	const { google, isResolving } = useSelect( ( select ) => {
		const acc = select( STORE_KEY ).getGoogleAccount();
		const resolving = select( STORE_KEY ).isResolving( 'getGoogleAccount' );

		return { google: acc, isResolving: resolving };
	} );

	if ( isResolving ) {
		return <AppSpinner />;
	}

	if ( ! google ) {
		return (
			<TitleButtonLayout
				title={ __(
					'Error while loading WordPress.com account info',
					'google-listings-and-ads'
				) }
			/>
		);
	}

	if ( google.active === 'yes' ) {
		return <TitleButtonLayout title={ google.email } />;
	}

	return <ConnectAccount disabled={ disabled } />;
};

export default CardContent;
