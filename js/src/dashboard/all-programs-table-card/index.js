/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';
import { Button } from '@wordpress/components';
import { getQuery, onQueryChange } from '@woocommerce/navigation';

/**
 * Internal dependencies
 */
import AppTableCard from '../../components/app-table-card';
import RemoveProgramButton from './remove-program-button';
import EditProgramLink from './edit-program-link';
import PauseProgramButton from './pause-program-button';
import ResumeProgramButton from './resume-program-button';
import './index.scss';

const headers = [
	{
		key: 'title',
		label: __( 'Title', 'google-listings-and-ads' ),
		isLeftAligned: true,
		required: true,
		isSortable: true,
	},
	{
		key: 'spend',
		label: __( 'Spend', 'google-listings-and-ads' ),
		isSortable: true,
	},
	{
		key: 'numberOfProducts',
		label: __( 'Number of Products', 'google-listings-and-ads' ),
		isSortable: true,
	},
	{ key: 'actions', label: '', required: true },
];

const AllProgramsTableCard = () => {
	const query = getQuery();

	// TODO: data from backend API.
	// using the above query (e.g. orderby, order and page) as parameter.
	const data = [
		{
			id: 123,
			title: 'Google Shopping Free Listings',
			spend: 'Free',
			numberOfProducts: 497,
			active: true,
		},
		{
			id: 456,
			title: 'Smart Shopping Campaign 1',
			spend: '$200.00',
			numberOfProducts: 133,
			active: false,
		},
	];

	// TODO: what happens when click add paid campaign button.
	const handleAddPaidCampaignClick = () => {};

	return (
		<AppTableCard
			trackEventReportId="all-programs"
			className="gla-all-programs-table-card"
			title={
				<div className="gla-all-programs-table-card__header">
					{ __( 'All Programs', 'google-listings-and-ads' ) }
					<Button
						isSecondary
						isSmall
						onClick={ handleAddPaidCampaignClick }
					>
						Add Paid Campaign
					</Button>
				</div>
			}
			headers={ headers }
			rows={ data.map( ( el ) => {
				return [
					{ display: el.title },
					{ display: el.spend },
					{ display: el.numberOfProducts },
					{
						display: (
							<div className="program-actions">
								<EditProgramLink programId={ el.id } />
								{ el.active ? (
									<PauseProgramButton programId={ el.id } />
								) : (
									<ResumeProgramButton programId={ el.id } />
								) }
								<RemoveProgramButton programId={ el.id } />
							</div>
						),
					},
				];
			} ) }
			totalRows={ data.length }
			rowsPerPage={ 10 }
			query={ query }
			onQueryChange={ onQueryChange }
		/>
	);
};

export default AllProgramsTableCard;