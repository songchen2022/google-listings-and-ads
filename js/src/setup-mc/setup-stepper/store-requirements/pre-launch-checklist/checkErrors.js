/**
 * External dependencies
 */
import { __ } from '@wordpress/i18n';

export default function checkErrors( values ) {
	const errors = {};

	/**
	 * Pre-launch checklist.
	 */
	if ( values.website_live !== true ) {
		errors.website_live = __(
			'Your Merchant Center account can be suspended if your store is not functional. Ensure that your domain and product pages are not leading to an under construction webpage, or an error page that displays a status code beginning with a 4 or 5(such as a 405 error).',
			'google-listings-and-ads'
		);
	}

	if ( values.checkout_process_complete !== true ) {
		errors.checkout_process_secure = __(
			'Ensure that all customers are able to complete the full checkout process on your site with an eligible payment method. Include a confirmation of the purchase after completion of the checkout process. <button type="button">Click Me!</button> ',
			'google-listings-and-ads'
		);
	}

	if ( values.checkout_process_secure !== true ) {
		errors.checkout_process_secure = __(
			'Update your website to ensure that every webpage that collects a customer\'s personal information is processed through a secure SSL server. Any page on your website that collects any personal information from the user needs to be SSL protected.<br/>  Use a secure server: Make sure to use a secure processing server when processing customer\'s personal information (SSL-protected, with a valid SSL certificate). With SSL, your webpage URL will appear with https:// instead of http:// <button type="button">Click Me!</button>.',

			'google-listings-and-ads'
		);
	}

	if ( values.payment_methods_visible !== true ) {
		errors.payment_methods_visible = __(
			'Please check the requirement.',
			'google-listings-and-ads'
		);
	}

	if ( values.refund_tos_visible !== true ) {
		errors.refund_tos_visible = __(
			"Show a clear return and refund policy on your website. Include return process, refund process, and customer requirements (return window, product condition and reason for return). If you don't accept returns or refunds, clearly state that on your website. Learn more link",
			'google-listings-and-ads'
		);
	}

	if ( values.contact_info_visible !== true ) {
		errors.contact_info_visible = __(
			'Allow your customers to contact you for product inquiries by inclusing contact information on your website (i.e. contact us form, business profile link, social media, email or phone number.).',
			'google-listings-and-ads'
		);
	}

	return errors;
}
