<?php
declare( strict_types=1 );

namespace Automattic\WooCommerce\GoogleListingsAndAds\Menu;

use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Registerable;
use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Service;

/**
 * Class GoogleConnect
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\Menu
 */
class GoogleConnect implements Service, Registerable {

	/**
	 * Register a service.
	 */
	public function register(): void {
		add_filter(
			'woocommerce_marketing_menu_items',
			function( $menu_items ) {
				return $this->add_items( $menu_items );
			}
		);

		add_action(
			'admin_menu',
			function() {
				$this->fix_menu_paths();
			}
		);
	}

	/**
	 * Add Google Menu item under Marketing
	 *
	 * @param array $items
	 *
	 * @return array
	 */
	protected function add_items( array $items ): array {
		$items[] = [
			'id'         => 'google-start',
			'title'      => __( 'Google', 'google-listings-and-ads' ),
			'path'       => '/google/start',
			'capability' => 'manage_woocommerce',
		];

		return $items;
	}

	/**
	 * Fix sub-menu paths. wc_admin_register_page() gets it wrong.
	 */
	protected function fix_menu_paths() {
		global $submenu;

		if ( ! isset( $submenu['woocommerce-marketing'] ) ) {
			return;
		}

		foreach ( $submenu['woocommerce-marketing'] as &$item ) {
			// The "slug" (aka the path) is the third item in the array.
			if ( 0 === strpos( $item[2], 'wc-admin' ) ) {
				$item[2] = 'admin.php?page=' . $item[2];
			}
		}
	}
}
