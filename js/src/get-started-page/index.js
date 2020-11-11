/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Panel, PanelBody, PanelRow } from '@wordpress/components';
import { more } from '@wordpress/icons';


/**
 * Internal dependencies
 */
import './index.scss';

const GetStartedPage = ( { query } ) => {
	return (
		<div className="changeme">
		    <Panel header="My Panel">
		        <PanelBody title="My Block Settings" icon={ more } initialOpen={ true }>
		            <PanelRow>My Panel Inputs and Labels</PanelRow>
		        </PanelBody>
		    </Panel>
		</div>
	);
};

export default GetStartedPage;
