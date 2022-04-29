/**
 * External dependencies
 */
import { createInterpolateElement } from '@wordpress/element';
import { __ } from '@wordpress/i18n';

/**
 * Internal dependencies
 */
import FaqsPanel from '.~/components/faqs-panel';
import AppDocumentationLink from '.~/components/app-documentation-link';
import './index.scss';

const faqItems = [
	{
		trackId: 'what-do-i-need-to-get-started',
		question: __(
			'What do I need to get started?',
			'google-listings-and-ads'
		),
		answer: createInterpolateElement(
			__(
				'In order to sync your WooCommerce store with Google and begin showcasing your products online, you will need to provide the following during setup; Google account access, target audience, shipping information, tax rate information (required for US only), and ensure your store is running on a compatible PHP version. <link>Learn more.</link>',
				'google-listings-and-ads'
			),
			{
				link: (
					<AppDocumentationLink
						context="faqs"
						linkId="general-requirements"
						href="https://woocommerce.com/document/google-listings-and-ads/#general-requirements"
					/>
				),
			}
		),
	},
	{
		trackId: 'what-if-i-already-have-free-listings',
		question: __(
			'What if I already have Google listings or ads set up? Will syncing my store replace my current Google listings?',
			'google-listings-and-ads'
		),
		answer: (
			<>
				<p>
					{ __(
						'Once you link an existing account to connect your store, your Shopping ads and free listings will stop running. You’ll need to re-upload your feed and product data in order to run Shopping ads and show free listings.',
						'google-listings-and-ads'
					) }
				</p>
				<p>
					{ createInterpolateElement(
						__(
							'Learn more about claiming URLs <link>here</link>.',
							'google-listings-and-ads'
						),
						{
							link: (
								<AppDocumentationLink
									context="faqs"
									linkId="claiming-urls"
									href="https://support.google.com/merchants/answer/7527436"
								/>
							),
						}
					) }
				</p>
				<p>
					{ __(
						'If you have an existing Content API feed, it will not be changed, overwritten or deleted by this WooCommerce integration. Instead, products will be added to your existing Content API feed.',
						'google-listings-and-ads'
					) }
				</p>
			</>
		),
	},
	{
		trackId: 'is-my-store-ready-to-sync-with-google',
		question: __(
			'Is my store ready to sync with Google?',
			'google-listings-and-ads'
		),
		answer: createInterpolateElement(
			__(
				'In order to meet the Google Merchant Center requirements make sure your website has the following; secure checkout process and payment information, refund and return policies, billing terms and conditions, business contact information. <link>Learn more.</link>',
				'google-listings-and-ads'
			),
			{
				link: (
					<AppDocumentationLink
						context="faqs"
						linkId="google-merchant-center-requirements"
						href="https://woocommerce.com/document/google-listings-and-ads/#google-merchant-center-requirements"
					/>
				),
			}
		),
	},
	{
		trackId: 'what-is-a-performance-max-campaign',
		question: __(
			'What is a Performance Max campaign?',
			'google-listings-and-ads'
		),
		answer: createInterpolateElement(
			__(
				'Performance Max campaigns make it easy to connect your WooCommerce store to Google Shopping ads so you can showcase your products to shoppers across Google Search, Maps, Shopping, YouTube, Gmail, the Display Network and Discover feed to drive traffic and sales for your store. <link>Learn more.</link>',
				'google-listings-and-ads'
			),
			{
				link: (
					<AppDocumentationLink
						context="faqs"
						linkId="performance-max"
						href="https://woocommerce.com/document/google-listings-and-ads/#google-performance-max-campaigns"
					/>
				),
			}
		),
	},
	{
		trackId: 'will-my-deals-and-promotions-display-on-google',
		question: __(
			'Will my deals and promotions display on Google?',
			'google-listings-and-ads'
		),
		answer: (
			<>
				<p>
					{ __(
						'To show your coupons and promotions on Google Shopping listings, make sure you’re using the latest version of Google Listings & Ads. When you create or update a coupon in your WordPress dashboard under Marketing > Coupons, you’ll see a Channel Visibility settings box on the right: select "Show coupon on Google" to enable. This is currently available in the US only.',
						'google-listings-and-ads'
					) }
				</p>
			</>
		),
	},
	{
		trackId: 'what-are-performance-max-campaigns',
		question: __(
			'What are Performance Max campaigns?',
			'google-listings-and-ads'
		),
		answer: __(
			'Performance Max campaigns are Google Ads that combine Google’s machine learning with automated bidding and ad placements to maximize conversion value and strategically display your ads to people searching for products like yours, at your given budget. The best part? You only pay when people click on your ad.',
			'google-listings-and-ads'
		),
	},
	{
		trackId: 'how-much-do-performance-max-campaigns-cost',
		question: __(
			'How much do Performance Max campaigns cost?',
			'google-listings-and-ads'
		),
		answer: __(
			'Performance Max campaigns are pay-per-click, meaning you only pay when someone clicks on your ads. You can customize your daily budget in Google Listings & Ads but we recommend starting off with the suggested minimum budget, and you can change this budget at any time.',
			'google-listings-and-ads'
		),
	},
	{
		trackId: 'can-i-run-both-free-listings-and-performance-max-campaigns',
		question: __(
			'Can I run both free listings and Performance Max campaigns at the same time?',
			'google-listings-and-ads'
		),
		answer: __(
			'Yes, you can run both at the same time, and we recommend it! In the US, advertisers running free listings and ads together have seen an average of over 50% increase in clicks and over 100% increase in impressions on both free listings and ads on the Shopping tab. Your store is automatically opted into free listings automatically and can choose to run a paid Performance Max campaign.',
			'google-listings-and-ads'
		),
	},
	{
		trackId: 'how-can-i-get-the-ad-credit-offer',
		question: __(
			'How can I get the $500 ad credit offer?',
			'google-listings-and-ads'
		),
		answer: (
			<>
				<p>
					{ __(
						'Create a new Google Ads account through Google Listings & Ads and a promotional code will be automatically applied to your account. You’ll have 60 days to spend $500 to qualify for the $500 ads credit.',
						'google-listings-and-ads'
					) }
				</p>
				<p>
					{ createInterpolateElement(
						__(
							'Ad credit amounts vary by country and region. Full terms and conditions can be found <link>here</link>.',
							'google-listings-and-ads'
						),
						{
							link: (
								<AppDocumentationLink
									context="faqs"
									linkId="terms-and-conditions-of-google-ads-coupons"
									href="https://www.google.com/ads/coupons/terms/"
								/>
							),
						}
					) }
				</p>
			</>
		),
	},
];

/**
 * Clicking on getting started page faq item to collapse or expand it.
 *
 * @event gla_get_started_faq
 * @property {string} id FAQ identifier
 * @property {string} action (`expand`|`collapse`)
 */

/**
 * @fires gla_get_started_faq
 */
const Faqs = () => {
	return (
		<FaqsPanel
			className="gla-get-started-faqs"
			trackName="gla_get_started_faq"
			faqItems={ faqItems }
		/>
	);
};

export default Faqs;
