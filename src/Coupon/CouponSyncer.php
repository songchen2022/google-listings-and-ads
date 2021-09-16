<?php
declare(strict_types = 1);
namespace Automattic\WooCommerce\GoogleListingsAndAds\Coupon;

use Automattic\WooCommerce\GoogleListingsAndAds\Exception\InvalidValue;
use Automattic\WooCommerce\GoogleListingsAndAds\Google\GooglePromotionService;
use Automattic\WooCommerce\GoogleListingsAndAds\Google\InvalidCouponEntry;
use Automattic\WooCommerce\GoogleListingsAndAds\Infrastructure\Service;
use Automattic\WooCommerce\GoogleListingsAndAds\MerchantCenter\MerchantCenterService;
use Automattic\WooCommerce\GoogleListingsAndAds\Proxies\WC;
use Google\Exception as GoogleException;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Exception;
use WC_Coupon;
defined( 'ABSPATH' ) || exit();

/**
 * Class CouponSyncer
 *
 * @package Automattic\WooCommerce\GoogleListingsAndAds\Coupon
 */
class CouponSyncer implements Service {

    public const FAILURE_THRESHOLD = 5;

    // Number of failed attempts allowed per FAILURE_THRESHOLD_WINDOW
    public const FAILURE_THRESHOLD_WINDOW = '3 hours';

    /**
     *
     * @var GoogleProductService
     */
    protected $google_service;

    /**
     *
     * @var CouponHelper
     */
    protected $coupon_helper;

    /**
     *
     * @var ValidatorInterface
     */
    protected $validator;

    /**
     *
     * @var MerchantCenterService
     */
    protected $merchant_center;

    /**
     *
     * @var WC
     */
    protected $wc;

    /**
     * CouponSyncer constructor.
     *
     * @param GoogleProductService $google_service
     * @param CouponHelper $coupon_helper
     * @param ValidatorInterface $validator
     * @param MerchantCenterService $merchant_center
     * @param WC $wc
     */
    public function __construct( 
        GooglePromotionService $google_service,
        CouponHelper $coupon_helper,
        ValidatorInterface $validator,
        MerchantCenterService $merchant_center,
        WC $wc ) {
        $this->google_service = $google_service;
        $this->coupon_helper = $coupon_helper;
        $this->validator = $validator;
        $this->merchant_center = $merchant_center;
        $this->wc = $wc;
    }

    /**
     * Submit a WooCommerce coupon to Google Merchant Center.
     *
     * @param WC_Coupon $coupon
     *
     * @throws CouponSyncerException If there are any errors while syncing coupon with Google Merchant Center.
     */
    public function update( WC_Coupon $coupon ) {
        $this->validate_merchant_center_setup();

        if ( ! $this->coupon_helper->is_sync_ready( $coupon ) ) {
            do_action( 
                'woocommerce_gla_debug_message',
                sprintf( 
                    'Skipping coupon (ID: %s) because it is not ready to be synced.',
                    $coupon->get_id() ),
                __METHOD__ );
            return;
        }

        $target_country = $this->merchant_center->get_main_target_country();
        if ( ! $this->merchant_center->is_promotion_supported_country( 
            $target_country ) ) {
            do_action( 
                'woocommerce_gla_debug_message',
                sprintf( 
                    'Skipping coupon (ID: %s) because it is not supported in main target country %s.',
                    $coupon->get_id(),
                    $target_country ),
                __METHOD__ );
            return;
        }

        $adapted_coupon = new WCCouponAdapter( 
            ['wc_coupon' => $coupon,'targetCountry' => $target_country] );
        $validation_result = $this->validate_coupon( $adapted_coupon );
        if ( $validation_result instanceof InvalidCouponEntry ) {
            $this->coupon_helper->mark_as_invalid( 
                $coupon,
                $validation_result->get_errors() );

            do_action( 
                'woocommerce_gla_debug_message',
                sprintf( 
                    'Skipping coupon (ID: %s) because it does not pass validation: %s',
                    $coupon->get_id(),
                    json_encode( $validation_result ) ),
                __METHOD__ );

            return;
        }

        try {
            $response = $this->google_service->create( $adapted_coupon );
            $this->coupon_helper->mark_as_synced( 
                $coupon,
                $response->getId(),
                $country );
            do_action( 'woocommerce_gla_updated_coupon', $adapted_coupon );

            do_action( 
                'woocommerce_gla_debug_message',
                sprintf( 
                    "Submitted promotion:\n%s",
                    json_encode( $adapted_coupon ) ),
                __METHOD__ );
        } catch ( GoogleException $google_exception ) {
            $invalid_promotion = new InvalidCouponEntry( 
                $wc_coupon_id = $coupon->get_id(),
                $target_country = $target_country,
                $errors = [$google_exception->getMessage()] );
            $this->coupon_helper->mark_as_invalid( 
                $coupon,
                [$invalid_promotion] );

            $this->handle_update_errors( [$invalid_promotion] );

            do_action( 
                'woocommerce_gla_debug_message',
                sprintf( 
                    "Promotion failed to sync with Merchant Center:\n%s",
                    json_encode( $invalid_promotion ) ),
                __METHOD__ );
        } catch ( Exception $exception ) {
            do_action( 'woocommerce_gla_exception', $exception, __METHOD__ );

            throw new CouponSyncerException( 
                sprintf( 
                    'Error updating Google promotion: %s',
                    $exception->getMessage() ),
                0,
                $exception );
        }
    }

    /**
     *
     * @param WCCouponAdapter $coupon
     *
     * @return InvalidCouponEntry|true
     */
    protected function validate_coupon( WCCouponAdapter $coupon ) {
        $violations = $this->validator->validate( $coupon );

        if ( 0 !== count( $violations ) ) {
            $invalid_promotion = new InvalidCouponEntry( 
                $coupon->get_wc_coupon()->get_id() );
            $invalid_promotion->map_validation_violations( $violations );

            return $invalid_promotion;
        }

        return true;
    }

    /**
     * Filters the list of invalid coupon entries and returns an array of WooCommerce coupon IDs with internal errors
     *
     * @param InvalidCouponEntry[] $invalid_coupons
     *
     * @return int[] An array of WooCommerce coupon ids.
     */
    public function get_internal_error_coupons( array $invalid_coupons ): array {
        $internal_error_ids = [];
        foreach ( $invalid_coupons as $invalid_coupon ) {
            if ( $invalid_coupon->has_error( 
                GooglePromotionService::INTERNAL_ERROR_REASON ) ) {
                $coupon_id = $invalid_coupon->get_wc_coupon_id();

                $internal_error_ids[$coupon_id] = $coupon_id;
            }
        }

        return $internal_error_ids;
    }

    /**
     * Delete a WooCommerce coupon from Google Merchant Center.
     *
     * @param DeleteCouponEntry $coupon
     *
     * @throws CouponSyncerException If there are any errors while deleting coupon from Google Merchant Center.
     */
    public function delete( DeleteCouponEntry $coupon ) {
        $this->validate_merchant_center_setup();

        $deleted_promotions = [];
        $invalid_promotions = [];
        $synced_google_ids = $coupon->get_synced_google_ids;
        $wc_coupon = $this->wc->maybe_get_coupon();
        $wc_coupon_exist = $wc_product instanceof WC_Product;
        foreach ( $synced_google_ids as $target_country => $google_id ) {
            try {
                $adapted_coupon = $coupon->get_coupon();
                $adapted_coupon->setTargetCountry( $target_country );
                $adapted_coupon->disableCoupon();

                $response = $this->google_service->create( $adapted_coupon );
                array_push( $deleted_promotions, $adapted_coupon );
                // Remove synced google id
                if ( $wc_coupon_exist ) {
                    $this->coupon_helper->remove_google_id( 
                        $wc_coupon,
                        $google_id );
                }
            } catch ( GoogleException $google_exception ) {
                array_push( 
                    $invalid_promotions,
                    $this->generate_update_coupon_invalid( 
                        $coupon->get_coupon()
                            ->getPromotionId(),
                        $country,
                        [$google_exception->getMessage()] ) );
            } catch ( Exception $exception ) {
                do_action( 'woocommerce_gla_exception', $exception, __METHOD__ );

                throw new CouponSyncerException( 
                    sprintf( 
                        'Error deleting Google promotion: %s',
                        $exception->getMessage() ),
                    0,
                    $exception );
            }
        }

        if ( ! empty( $invalid_promotions ) ) {
            $this->handle_delete_errors( $invalid_promotions );
            do_action( 
                'woocommerce_gla_debug_message',
                sprintf( 
                    "Failed to delete %s promotions from Merchant Center:\n%s",
                    count( $invalid_promotions ),
                    json_encode( $invalid_promotions ) ),
                __METHOD__ );
        } else if ( $wc_coupon_exist ) {
            $this->coupon_helper->mark_as_unsynced( $wc_coupon );
        }

        do_action( 
            'woocommerce_gla_batch_deleted_promotion',
            $deleted_promotions,
            $invalid_promotions );

        do_action( 
            'woocommerce_gla_debug_message',
            sprintf( 
                "Deleted %s promoitons:\n%s",
                count( $deleted_promotions ),
                json_encode( $deleted_promotions ) ),
            __METHOD__ );
    }

    /**
     * Return whether coupon is supported as visible on Google.
     *
     * @return bool
     */
    public static function is_coupon_supported( WC_Coupon $coupon ): bool {
        if ( ! empty( $coupon->get_email_restrictions() ) ) {
            return false;
        }
        if ( 
            ! empty( $coupon->get_exclude_sale_items() ) &&
            get_exclude_sale_items() ) {
            return false;
        }
        return true;
    }

    /**
     * Return the list of supported coupon types.
     *
     * @return array
     */
    public static function get_supported_coupon_types(): array {
        return (array) apply_filters( 
            'woocommerce_gla_supported_coupon_types',
            ['percent','fixed_cart','fixed_product'] );
    }

    /**
     * Return the list of coupon types we will hide functionality for (default none).
     *
     * @since 1.2.0
     *       
     * @return array
     */
    public static function get_hidden_coupon_types(): array {
        return (array) apply_filters( 'woocommerce_gla_hidden_coupon_types', [] );
    }

    /**
     *
     * @param InvalidCouponEntry[] $invalid_promotions
     */
    protected function handle_update_errors( array $invalid_promotions ) {
        // An invalid promtoion is a wc_coupon mapping to a country.
        // Get all related wc coupon ids that have internal errors.
        $internal_error_coupon_ids = $this->get_internal_error_coupons( 
            $invalid_promotions );

        if ( 
            ! empty( $internal_error_coupon_ids ) &&
            apply_filters( 
                'woocommerce_gla_coupons_update_retry_on_failure',
                true,
                $internal_error_coupon_ids ) ) {
            do_action( 
                'woocommerce_gla_retry_update_coupons',
                $internal_error_coupon_ids );

            do_action( 
                'woocommerce_gla_error',
                sprintf( 
                    'Internal API errors while submitting the following coupons: %s',
                    join( ', ', $internal_error_coupon_ids ) ),
                __METHOD__ );
        }
    }

    /**
     *
     * @param BatchInvalidCouponEntry[] $invalid_coupons
     */
    protected function handle_delete_errors( array $invalid_coupons ) {
        // TODO: handle delete errors.
    }

    /**
     * Validates whether Merchant Center is set up and connected.
     *
     * @throws CouponSyncerException If Google Merchant Center is not set up and connected.
     */
    protected function validate_merchant_center_setup(): void {
        if ( ! $this->merchant_center->is_connected() ) {
            do_action( 
                'woocommerce_gla_error',
                'Cannot sync any coupons before setting up Google Merchant Center.',
                __METHOD__ );

            throw new CouponSyncerException( 
                __( 
                    'Google Merchant Center has not been set up correctly. Please review your configuration.',
                    'google-listings-and-ads' ) );
        }
    }
}