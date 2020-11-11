/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';


/**
 * Internal dependencies
 */
import './index.scss';

const GetStartedPage = ( { query } ) => {
	return (
		<div className="changeme">
			Hello World!
			<Button isPrimary>Click Me!</Button>
		</div>
	);
};

export default GetStartedPage;
