/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import {
	Button,
	CheckboxControl,
	Panel,
	PanelBody,
	PanelRow,
} from '@wordpress/components';
import { recordEvent } from '@woocommerce/tracks';
import { useRef } from '@wordpress/element';

/**
 * Internal dependencies
 */
import './index.scss';

const getPanelToggleHandler = ( id ) => ( isOpened ) => {
	recordEvent( 'pre-launch-checklist', {
		id,
		action: isOpened ? 'expand' : 'collapse',
	} );
};

export default function PreLaunchCheckItem( {
	formProps,
	fieldName,
	firstPersonTitle,
	secondPersonTitle,
	children,
} ) {
	const { getInputProps, setValue, values } = formProps;
	const checked = values[ fieldName ];
	const initialCheckedRef = useRef( checked );

	if ( checked ) {
		return (
			<CheckboxControl
				label={
					initialCheckedRef.current
						? firstPersonTitle
						: secondPersonTitle
				}
				{ ...getInputProps( fieldName ) }
				disabled
			/>
		);
	}

	return (
		<div className="gla-pre-launch-checklist__checkbox">
			<CheckboxControl { ...getInputProps( fieldName ) } />
			<Panel>
				<PanelBody
					title={ secondPersonTitle }
					initialOpen={ true }
					onToggle={ getPanelToggleHandler( fieldName ) }
				>
					<PanelRow>
						{ children }
						<Button
							isPrimary
							onClick={ () => setValue( fieldName, true ) }
						>
							{ __( 'Confirm', 'google-listings-and-ads' ) }
						</Button>
					</PanelRow>
				</PanelBody>
			</Panel>
		</div>
	);
}
