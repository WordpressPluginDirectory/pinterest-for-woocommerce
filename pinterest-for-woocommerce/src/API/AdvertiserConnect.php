<?php
/**
 * Handle Pinterest Advertiser Connect
 *
 * @package     Pinterest_For_Woocommerce/API
 * @version     1.0.0
 */

namespace Automattic\WooCommerce\Pinterest\API;

use Automattic\WooCommerce\Pinterest\AdCredits;
use Automattic\WooCommerce\Pinterest\Billing;
use Automattic\WooCommerce\Pinterest\Utilities\Utilities;
use Exception;
use Pinterest_For_Woocommerce;
use Throwable;
use WP_REST_Server;
use WP_REST_Request;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Endpoint handing Pinterest advertiser.
 */
class AdvertiserConnect extends VendorAPI {

	/**
	 * Initialize class
	 */
	public function __construct() {

		$this->base              = 'tagowner';
		$this->endpoint_callback = 'connect_advertiser';
		$this->methods           = WP_REST_Server::CREATABLE;

		$this->register_routes();
	}


	/**
	 * Connect the selected advertiser with Pinterest account.
	 *
	 * @param WP_REST_Request $request The request.
	 *
	 * @return array|WP_Error
	 *
	 * @throws Exception PHP Exception.
	 */
	public function connect_advertiser( WP_REST_Request $request ) {
		try {
			$advertiser_id = $request->has_param( 'advrtsr_id' ) ? $request->get_param( 'advrtsr_id' ) : false;
			$tag_id        = $request->has_param( 'tag_id' ) ? $request->get_param( 'tag_id' ) : false;

			if ( ! $advertiser_id || ! $tag_id ) {
				throw new Exception( esc_html__( 'Missing advertiser or tag parameters.', 'pinterest-for-woocommerce' ), 400 );
			}

			$integration_data = Pinterest_For_Woocommerce::get_data( 'integration_data' );
			if ( $advertiser_id === $integration_data['connected_advertiser_id'] ?? '' ) {
				return array(
					'connected'   => $advertiser_id,
					'reconnected' => false,
				);
			}

			// Update integration with new advertiser and a tag.
			return self::connect_advertiser_and_tag( $advertiser_id, $tag_id );
		} catch ( Throwable $th ) {
			/* Translators: The error description as returned from the API */
			$error_message = sprintf( esc_html__( 'Could not connect advertiser with Pinterest. [%s]', 'pinterest-for-woocommerce' ), $th->getMessage() );
			return new WP_Error( \PINTEREST_FOR_WOOCOMMERCE_PREFIX . '_advertiser_connect_error', $error_message, array( 'status' => $th->getCode() ) );
		}
	}


	/**
	 * Connect an advertiser and a tag to the platform.
	 *
	 * @param string $advertiser_id The ID of the advertiser.
	 * @param string $tag_id        The ID of the tag.
	 *
	 * @return array {
	 *      Updates Pinterest integration with the new advertiser and tag.
	 *
	 *      @type string $connected   The ID of the connected advertiser.
	 *      @type bool   $reconnected Whether the advertiser was reconnected.
	 * }
	 * @throws Exception PHP Exception.
	 */
	public static function connect_advertiser_and_tag( string $advertiser_id, string $tag_id ): array {

		$integration_data     = Pinterest_For_Woocommerce::get_data( 'integration_data' );
		$external_business_id = $integration_data['external_business_id'] ?? '';

		$data = array(
			'connected_advertiser_id' => $advertiser_id,
			'connected_tag_id'        => $tag_id,
		);

		try {
			$response = Pinterest_For_Woocommerce::update_commerce_integration( $external_business_id, $data );
		} catch ( Throwable $th ) {
			throw new Exception(
				sprintf(
					/* translators: 1. the error message as returned by the Pinterest API */
					esc_html__( 'Commerce integration update error: $1%s', 'pinterest-for-woocommerce' ),
					esc_html( $th->getMessage() )
				),
				400
			);
		}

		// At this stage we can check if the connected advertiser has billing setup.
		$has_billing = Billing::update_billing_information();

		/*
		 * If the advertiser does not have a correct billing lets check for the setup frequently for the next hour.
		 */
		if ( ! $has_billing ) {
			Billing::check_billing_setup_often();
		}

		// Try to claim coupons if they are available for the merchant.
		AdCredits::handle_redeem_credit();

		/*
		 * This is the last step of the connection process. We can use this moment to
		 * track when the connection to the account was made.
		 */
		Utilities::set_account_connection_timestamp();

		// Reset UI state when the new advertiser is connected.
		UserInteraction::flush_options();

		return array(
			'connected'   => $response['connected_advertiser_id'],
			'reconnected' => true,
		);
	}


	/**
	 * Disconnect the previous connected advertiser.
	 *
	 * @param string $connected_advertiser The ID of the connected advertiser.
	 * @param string $connected_tag        The ID of the connected tag.
	 *
	 * @throws \Exception PHP Exception.
	 */
	public static function disconnect_advertiser( $connected_advertiser, $connected_tag ) {

		try {

			$response = Base::disconnect_advertiser( $connected_advertiser, $connected_tag );

			if ( 'success' !== $response['status'] ) {
				throw new \Exception( esc_html__( 'The advertiser could not be disconnected from Pinterest.', 'pinterest-for-woocommerce' ), 400 );
			}

			// Advertiser disconnected, clear the billing status information in the account data.
			$account_data                        = Pinterest_For_Woocommerce()::get_setting( 'account_data' );
			$account_data['is_billing_setup']    = null;
			$account_data['coupon_redeem_info']  = null;
			$account_data['available_discounts'] = null;
			Pinterest_For_Woocommerce()::save_setting( 'account_data', $account_data );

		} catch ( \Exception $e ) {

			throw new \Exception( esc_html__( 'The advertiser could not be disconnected from Pinterest.', 'pinterest-for-woocommerce' ), 400 );
		}
	}


	/**
	 * Enable AEM during the onboarding process.
	 *
	 * @param string $tag_id The tracking tag identifier.
	 */
	protected static function enable_aem_tag( $tag_id ) {
		Base::update_tag(
			$tag_id,
			array(
				'aem_enabled'      => true,
				'aem_fnln_enabled' => true,
				'aem_ph_enabled'   => true,
				'aem_ge_enabled'   => true,
				'aem_db_enabled'   => true,
				'aem_loc_enabled'  => true,
			)
		);
	}
}
