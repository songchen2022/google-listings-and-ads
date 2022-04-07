<?php
/**
 * Global Site Tag functionality - add main script and track conversions.
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds
 */

namespace Automattic\WooCommerce\GoogleListingsAndAds\Google;

use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Conditional;
use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Registerable;
use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Service;
use Automattic\WooCommerce\GoogleListingsAndAds\Options\OptionsAwareInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\Options\OptionsAwareTrait;
use Automattic\WooCommerce\GoogleListingsAndAds\Options\OptionsInterface;
use Automattic\WooCommerce\GoogleListingsAndAds\Product\ProductHelper;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\GoogleGtagJs;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\WP;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\WC;

/**
 * Main class for Global Site Tag.
 */
class GlobalSiteTag implements Service, Registerable, Conditional, OptionsAwareInterface {


	use OptionsAwareTrait;

	/** @var string Developer ID */
	protected const DEVELOPER_ID = 'dOGY3NW';

	/** @var string Meta key used to mark orders as converted */
	protected const ORDER_CONVERSION_META_KEY = '_gla_tracked';

	/**
	 * @var GoogleGtagJs
	 */
	protected $gtag_js;

	/**
	 * @var WP
	 */
	protected $wp;

	/**
	 * @var ProductHelper
	 */
	protected $product_helper;

	/**
	 * @var WC
	 */
	protected $wc;

	/**
	 * Global Site Tag constructor.
	 *
	 * @param GoogleGtagJs  $gtag_js
	 * @param WP            $wp
	 * @param ProductHelper $product_helpe
	 * @param WC            $wc
	 */
	public function __construct( GoogleGtagJs $gtag_js, WP $wp, ProductHelper $product_helper, WC $wc ) {
		$this->gtag_js        = $gtag_js;
		$this->wp             = $wp;
		$this->product_helper = $product_helper;
	    $this->wc 			  = $wc;
	}

	/**
	 * Register the service.
	 */
	public function register(): void {
		$conversion_action = $this->options->get( OptionsInterface::ADS_CONVERSION_ACTION );

		// No snippets without conversion action info.
		if ( ! $conversion_action ) {
			return;
		}

		$ads_conversion_id    = $conversion_action['conversion_id'];
		$ads_conversion_label = $conversion_action['conversion_label'];

		add_action(
			'wp_head',
			function () use ( $ads_conversion_id ) {
				$this->activate_global_site_tag( $ads_conversion_id );
			},
			999998
		);
		add_action(
			'woocommerce_before_thankyou',
			function ($order_id) use ( $ads_conversion_id, $ads_conversion_label ) {
				$this->maybe_display_event_snippet( $ads_conversion_id, $ads_conversion_label, $order_id );
				// $this->display_purchase_page_snippet($order_id);
			},
			1000000
		);

		add_action(
			'woocommerce_after_single_product',
			function () {
				$this->display_view_item_event_snippet();
			}
		);

		add_action(
			'wp_body_open',
			function () {
				$this->display_page_view_event_snippet();
				$this->display_cart_page_snippet();
			}
		);

		add_filter(
			'wc_add_to_cart_message_html',
			function ( $message, $products ) {
				return $this->custom_action_add_to_cart( $message, $products );
			},
			1000000,
			2
		);
	}

	/**
	 * Activate the Global Site Tag framework:
	 * - Insert GST code, or
	 * - Include the Google Ads conversion ID in WooCommerce Google Analytics Integration output, if available
	 *
	 * @param string $ads_conversion_id Google Ads account conversion ID.
	 */
	public function activate_global_site_tag( string $ads_conversion_id ) {
		if ( $this->gtag_js->is_adding_framework() ) {
			add_filter(
				'woocommerce_gtag_snippet',
				function ( $gtag_snippet ) use ( $ads_conversion_id ) {
					return preg_replace(
						'~(\s)</script>~',
						"\tgtag('config', '" . $ads_conversion_id . "', { 'groups': 'GLA' });\n$1</script>",
						$gtag_snippet
					);
				}
			);
		} else {
			$this->display_global_site_tag( $ads_conversion_id );
		}
	}

	/**
	 * Display the JavaScript code to load the Global Site Tag framework.
	 *
	 * @param string $ads_conversion_id Google Ads account conversion ID.
	 */
	protected function display_global_site_tag( string $ads_conversion_id ) {
		// phpcs:disable WordPress.WP.EnqueuedResources.NonEnqueuedScript
		?>

<!-- Global site tag (gtag.js) - Google Ads: <?php echo esc_js( $ads_conversion_id ); ?> - Google Listings & Ads -->
<script async src="https://www.googletagmanager.com/gtag/js?id=<?php echo esc_js( $ads_conversion_id ); ?>"></script>
<script>
	window.dataLayer = window.dataLayer || [];
	function gtag(){dataLayer.push(arguments);}
	gtag('js', new Date());
	gtag('set', 'developer_id.<?php echo esc_js( self::DEVELOPER_ID ); ?>', true);

	gtag('config','<?php echo esc_js( $ads_conversion_id ); ?>', { 'groups': 'GLA' });
</script>
		<?php
		// phpcs:enable WordPress.WP.EnqueuedResources.NonEnqueuedScript
	}

	/**
	 * Display the JavaScript code to track conversions on the order confirmation page.
	 *
	 * @param string $ads_conversion_id Google Ads account conversion ID.
	 * @param string $ads_conversion_label Google Ads conversion label.
	 */
	public function maybe_display_event_snippet( string $ads_conversion_id, string $ads_conversion_label, int $order_id): void {
		// Only display on the order confirmation page.
		if ( ! is_order_received_page() ) {
			return;
		}

		$order = wc_get_order( $order_id );
		// Make sure there is a valid order object and it is not already marked as tracked
		if ( ! $order || 1 === $order->get_meta( self::ORDER_CONVERSION_META_KEY, true ) ) {
			return;
		}

		// Mark the order as tracked, to avoid double-reporting if the confirmation page is reloaded.
		$order->update_meta_data( self::ORDER_CONVERSION_META_KEY, 1 );
		$order->save_meta_data();

		printf(
			'<script>
            gtag("event", "conversion", 
                {"send_to": "%s",
                 "value": "%s",
                 "currency": "%s",
                 "transaction_id": "%s"});
            </script>',
			esc_js( "{$ads_conversion_id}/{$ads_conversion_label}" ),
			esc_js( $order->get_total() ),
			esc_js( $order->get_currency() ),
			esc_js( $order->get_id() ),
		);

		// Get the item infor in the order
		$item_info = '';
		foreach ( $order->get_items() as $item_id => $item ) {
			$product_id   = $item->get_product_id();
			$product_name = $item->get_name();
			$quantity     = $item->get_quantity();
			$price        = $item->get_subtotal();
			$item_info    = $item_info . sprintf(
				'{
                    "id": "gla_%s",
                    "price": %s,
                    "google_business_vertical": "retail",
                    "name":"%s",
                    "quantity":"%s",
                }',
				esc_js( $product_id ),
				esc_js( $price ),
				esc_js( $product_name ),
				esc_js( $quantity ),
			);
		}

		// Check if this is the first time customer
		$is_new_customer = $this->is_first_time_customer( $order->get_billing_email() );

		// Track the purchase page
		$language        = $this->wp->get_locale();
		if ( 'en_US' === $language ) {
			$language = 'English';
		}
		printf(
			'<script>
            gtag("event", "purchase",
				{
                    "developer_id.%s": "true",
                    "ecomm_pagetype": "purchase",
                    "send_to": "GLA",
                    "transaction_id": "%s",
                    "currency": "%s",
                    "country": "%s",
                    "value": "%s",
                    "new_customer": "%s",
                    "tax": "%s",
                    "shipping": "%s",
                    "delivery_postal_code": "%s",
                    "aw_feed_country": "%s",   
                    "aw_feed_language": "%s",                 
                    items: [' . $item_info . ']}); </script>',
			esc_js( self::DEVELOPER_ID ),
			esc_js( $order->get_id() ),
			esc_js( $order->get_currency() ),
			esc_js( $this->wc->get_base_country() ),
			esc_js( $order->get_total() ),
			esc_js( $is_new_customer ),
			esc_js( $order->get_cart_tax() ),
			esc_js( $order->get_total_shipping() ),
			esc_js( $order->get_shipping_postcode() ),
			esc_js( $this->wc->get_base_country() ),
			esc_js( $language ),
		);
	}

	/**
	 * Display the JavaScript code to track the product view page.
	 */
	private function display_view_item_event_snippet(): void {
		// Only display on the product view page.
		if ( ! is_product() ) {
			return;
		}
		$product = wc_get_product( get_the_ID() );
		printf(
			'<script>
                gtag("event", "view_item", {
                    "send_to": "GLA",
                    "developer_id.%s": "true",
                    "ecomm_pagetype": "product",
                    "value": "%s",
                    items:[{
                    "id": "gla_%s",
                    "price": %s,
                    "google_business_vertical": "retail",
                    "name":"%s",
                    "category":"%s",
                    }]});
			</script>',
			esc_js( self::DEVELOPER_ID ),
			esc_js( (string) $product->get_price() ),
			esc_js( $product->get_id() ),
			esc_js( (string) $product->get_price() ),
			esc_js( $product->get_name() ),
			esc_js( join( '& ', $this->product_helper->get_categories( $product ) ) ),
		);
	}

	/**
	 * Display the JavaScript code to track all pages.
	 */
	private function display_page_view_event_snippet(): void {
		printf(
			'<script>
                gtag(
                    "event", "page_view", {
                    "send_to": "GLA",
                    "developer_id.%s": "true",});
			</script>',
			esc_js( self::DEVELOPER_ID )
		);
	}

	/**
	 * Display the JavaScript code to track the cart page.
	 */
	private function display_cart_page_snippet(): void {
		// Only display on the cart page.
		if ( ! is_cart() ) {
			return;
		}

		$item_info = '';

		foreach ( WC()->cart->get_cart() as $cart_item ) {
			// gets the product id
			$id = $cart_item['product_id'];

			// gets the product object
			$product = $cart_item['data'];
			$name    = $product->get_name();
			$price   = $product->get_price();
			// gets the cart item quantity
			$quantity = $cart_item['quantity'];

			$item_info = $item_info . sprintf(
				'{
				"id": "gla_%s",
				"price": %s,
				"google_business_vertical": "retail",
				"name":"%s",
				"quantity":"%s",
				}',
				esc_js( $id ),
				esc_js( $price ),
				esc_js( $name ),
				esc_js( $quantity )
			);
		}
		$value          = WC()->cart->total;
		$page_view_gtag = sprintf(
			'gtag("event", "page_view",
				{"send_to": "GLA",
				"ecomm_pagetype": "cart",
				"value": "%s",
				 items: [' . $item_info . ']});',
			esc_js( $value ),
		);
		wp_print_inline_script_tag( $page_view_gtag );
	}

	/**
	 * Display the JavaScript code to track the purchase page.
	 */
// 	private function display_purchase_page_snippet(): void {
// 		// Only display on the order confirmation page.
// 		if ( ! is_order_received_page() ) {
// 			return;
// 		}
// 		$order_id = $this->wp->get_query_vars( 'order-received', 0 );
// 		if ( empty( $order_id ) ) {
// 			return;
// 		}
// 		$order = wc_get_order( $order_id );


// do_action(
// 	'woocommerce_gla_debug_message',
// 	sprintf('order is %s',
// 	(string) $order),
// 	__METHOD__
// );

// 		$item_info = '';
// 		foreach ( $order->get_items() as $item_id => $item ) {
// 			$product_id   = $item->get_product_id();
// 			$product_name = $item->get_name();
// 			$quantity     = $item->get_quantity();
// 			$price        = $item->get_subtotal();
// 			$item_info    = $item_info . sprintf(
// 				'{
//                     "id": "gla_%s",
//                     "price": %s,
//                     "google_business_vertical": "retail",
//                     "name":"%s",
//                     "quantity":"%s",
//                 }',
// 				esc_js( $product_id ),
// 				esc_js( $price ),
// 				esc_js( $product_name ),
// 				esc_js( $quantity ),
// 			);

// 		}

// 		do_action(
// 			'woocommerce_gla_debug_message',
// 			sprintf('order is %s',
// 			(string) $item_info),
// 			__METHOD__
// 		);

// 		$is_new_customer = $this->is_first_time_customer( $order->get_billing_email() );
// 		do_action(
// 			'woocommerce_gla_debug_message',
// 			sprintf('order is %s',
// 			(string) $is_new_customer),
// 			__METHOD__
// 		);


// 		$language        = $this->wp->get_locale();
// 		if ( 'en_US' === $language ) {
// 			$language = 'English';
// 		}
// 		printf(
// 			'<script>
//             gtag("event", "purchase",
// 				{
//                     "developer_id.%s": "true",
//                     "ecomm_pagetype": "purchase",
//                     "send_to": "GLA",
//                     "transaction_id": "%s",
//                     "currency": "%s",
//                     "country": "%s",
//                     "value": "%s",
//                     "new_customer": "%s",
//                     "tax": "%s",
//                     "shipping": "%s",
//                     "delivery_postal_code": "%s",
//                     "aw_feed_country": "%s",   
//                     "aw_feed_language": "%s",                 
//                     items: [' . $item_info . ']}); </script>',
// 			esc_js( self::DEVELOPER_ID ),
// 			esc_js( $order->get_id() ),
// 			esc_js( $order->get_currency() ),
// 			esc_js( $this->wc->get_base_country() ),
// 			esc_js( $order->get_total() ),
// 			esc_js( $is_new_customer ),
// 			esc_js( $order->get_cart_tax() ),
// 			esc_js( $order->get_total_shipping() ),
// 			esc_js( $order->get_shipping_postcode() ),
// 			esc_js( $this->wc->get_base_country() ),
// 			esc_js( $language ),
// 		);
// 	}

	/**
	 * Display the JavaScript code to track the add to cart button.
	 *
	 * @param string $message Add to cart messages.
	 * @param array  $products Product ID list.
	 */
	private function custom_action_add_to_cart( $message, $products ) {
		// Only display this tag info after click the add to cart button .
		// $product = wc_get_product( array_key_first( $products ) );

		foreach ( $products as $product_id ) {
			// foreach ( $products as $product ) {
			do_action(
				'woocommerce_gla_debug_message',
				sprintf(
					'All Product is %s',
					(string)$products
				),
				__METHOD__
			);
	
		$product = wc_get_product( $product_id );
		do_action(
			'woocommerce_gla_debug_message',
			sprintf(
				'Product is %s',
				(string)$product
			),
			__METHOD__
		);

		add_action(
			'wp_footer',
			function () use ( $product ) {
				printf(
					'<script>
                        gtag("event", "add_to_cart", {
                            "send_to": "GLA",
                            "developer_id.%s": "true",
                            "ecomm_pagetype": "cart",
                            "value": "%s",
                            items:[{
                            "id": "gla_%s",
                            "price": %s,
                            "google_business_vertical": "retail",
                            "name":"%s",
                            "category":"%s",
                            }]});
                    </script>',
					esc_js( self::DEVELOPER_ID ),
					esc_js( (string) $product->get_price() ),
					esc_js( $product->get_id() ),
					esc_js( (string) $product->get_price() ),
					esc_js( $product->get_name() ),
					esc_js( join( '& ', $this->product_helper->get_categories( $product ) ) ),
				);
			}
		);
	}

		do_action(
			'wp_footer'
		);

		return $message;
	}

	/**
	 * TODO: Should the Global Site Tag framework be used if there are no paid Ads campaigns?
	 *
	 * @return bool True if the Global Site Tag framework should be included.
	 */
	public static function is_needed(): bool {
		return true;
	}

	/**
	 * Check if it is the new customer order.
	 *
	 * @param string $customer_email Customer email address.
	 * @return string True if this is new customer order.
	 */
	private static function is_first_time_customer( $customer_email ): string {
		$query = new \WC_Order_Query(
			[
				'return' => 'ids',
			]
		);
		$query->set( 'customer', $customer_email );
		$orders = $query->get_orders();
		return count( $orders ) === 1? 'true': 'false';
	}
}
