/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { CardBody, CardFooter } from '@wordpress/components';
import { Pagination, TablePlaceholder } from '@woocommerce/components';

/**
 * Internal dependencies
 */
import { ISSUE_TABLE_PER_PAGE } from '../constants';
import { recordTablePageEvent } from '.~/utils/recordEvent';
import useMCIssues from '.~/hooks/useMCIssues';
import getActiveIssueType from '.~/product-feed/issues-table-card/getActiveIssueType';
import IssuesTableData from '.~/product-feed/issues-table-card/issues-table-data';
import './index.scss';

const headers = [
	{
		key: 'type',
		label: __( 'Type', 'google-listings-and-ads' ),
		isLeftAligned: true,
		required: true,
	},
	{
		key: 'affectedProduct',
		label: __( 'Affected product', 'google-listings-and-ads' ),
		isLeftAligned: true,
		required: true,
	},
	{
		key: 'issue',
		label: __( 'Issue', 'google-listings-and-ads' ),
		isLeftAligned: true,
		required: true,
	},
	{
		key: 'suggestedAction',
		label: __( 'Suggested action', 'google-listings-and-ads' ),
		isLeftAligned: true,
		required: true,
	},
	{ key: 'action', label: '', required: true },
];

const IssuesTable = ( { issueType } ) => {
	const [ data, hasFinishedResolution, page, setPage ] = useMCIssues(
		issueType
	);

	if ( getActiveIssueType() !== issueType ) return null;

	const handlePageChange = ( newPage, direction ) => {
		setPage( newPage );
		recordTablePageEvent( `issues-to-resolve`, newPage, direction );
	};

	return (
		<>
			<CardBody size={ null }>
				{ ! hasFinishedResolution ? (
					<TablePlaceholder headers={ headers } caption="" />
				) : (
					<IssuesTableData headers={ headers } data={ data } />
				) }
			</CardBody>
			<CardFooter justify="center">
				<Pagination
					page={ page }
					perPage={ ISSUE_TABLE_PER_PAGE }
					total={ data?.total ?? 0 }
					showPagePicker={ false }
					showPerPagePicker={ false }
					onPageChange={ handlePageChange }
				/>
			</CardFooter>
		</>
	);
};

export default IssuesTable;
