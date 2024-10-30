<?php
/**
 * Created by PhpStorm.
 * User: tomaskircher
 * Date: 2018-03-30
 * Time: 12:11
 */

namespace classes\hygglig;

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

use classes\http\Hygglig_Request;
use classes\Hyggligutils\Hygglig_Compatability;

class Hygglig_Api{

	const SANDBOX_CHECKOUT_API_URL = 'https://sb-co.hygglig.com/api/checkout/'; 
    const API_CHECKOUT_URL = 'https://co.hygglig.com/api/checkout/';
    const SANDBOX_API_URL = 'https://sb-manage.hygglig.com/';
    const API_URL = 'https://manage.hygglig.com/';

    /** Returns correct Checkout URL
     *
     * @return string
     */
    public static function get_api_checkout_url(){
        $options = get_option( 'woocommerce_hygglig_checkout_settings' );

        if( $options['testmode'] == 'yes' ){
            return self::SANDBOX_CHECKOUT_API_URL;
        }
        return self::API_CHECKOUT_URL;
    }

    /** Returns correct API URL
     *
     * @return string
     */
    public static function get_api_url(){
        $options = get_option( 'woocommerce_hygglig_checkout_settings' );

        if( $options['testmode'] == 'yes' ){
            return self::SANDBOX_API_URL;
        }
        return self::API_URL;
    }

    /** Returns checkout html
     * @param \WC_Order $order
     * @param array $cart
     * @param float $total_amount
     * @param string $currency
     * @return mixed
     */
    public static function create_checkout( $order, $cart ,$total_amount, $currency ){
		
		$hygglig_order_number = WC()->session->get( 'hygglig_order_number' );
		if($hygglig_order_number > 0){
			return Hygglig_Api::get_iframe( $hygglig_order_number );
		}else{
			$options = get_option( 'woocommerce_hygglig_checkout_settings' );
			$order_key = $order->get_order_key();
			hygglig_write_log( $total_amount * 100 );
			$total_amount = $total_amount * 100 * 100;
            
            //success_url => self::format_success_url( wc_get_endpoint_url( 'order-received', $order->get_id(), $options['hygglig_checkout_thanks_url'] ), $order->get_id(), $order_key )
			$payload = array(
				'success_url'            => $options['hygglig_checkout_thanks_url'],
				'push_notification_url'   => site_url() . '/?hyggligCallback=1',
				'checkout_url'           => $options['hygglig_checkout_url'],
				'terms_url'              => $options['terms_url'],
				'order_reference'        => (string)$order->get_id(),
				'currency'              => $currency
			);
	
			$current_user = wp_get_current_user();
			$customer_id = get_current_user_id();
			$current_user_postcode = preg_replace('/\s+/', '', get_user_meta( $customer_id, 'shipping_postcode', true ) );
	
			foreach ( $cart as $item ) {
				$payload['articles'][] = $item;
			}
	
			hygglig_write_log( $payload ); 
			$response = Hygglig_Request::send( $payload, self::get_api_checkout_url() );//. 'StartCheckout' 
			hygglig_write_log( "Create Checkecout Successful" );
	
			if( $response === FALSE ){
				die();
			}
	
			return json_decode( $response, true );
		}
    }

    /** Returns html for order processed
     * @param string $safe_token
     * @return mixed
     */
    public static function get_iframe( $hygglig_order_number ){
        $options = get_option( 'woocommerce_hygglig_checkout_settings' );

        if($hygglig_order_number < 0){
			$hygglig_order_number = WC()->session->get( 'hygglig_order_number' );
		}
		if($hygglig_order_number){
        	$response = Hygglig_Request::get( self::get_api_checkout_url().$hygglig_order_number );//. 'GetIFrame' 
        	hygglig_write_log( "get_api_checkout_url get_iframe $hygglig_order_number call complete" );
        	return json_decode( $response , true );
		}
		return '';
    }

    /** Updates order in Hygglig
     * @param string $safe_token
     * @param $cart
     * @return mixed
     */
    public static function update_order( $hygglig_order_number, $cart, $order ){

        $options = get_option( 'woocommerce_hygglig_checkout_settings' );

        //Do roundvalue
        //$this->round_cart_item( $cart, $total_amount );
        $total = intval( Hygglig_Compatability::get_cart_total()  * 10000 );
		
		$order_key = $order->get_order_key();
        // success_url -> self::format_success_url( wc_get_endpoint_url( 'order-received', $order->get_id(), $options['hygglig_checkout_thanks_url'] ), $order->get_id(), $order_key )
        $payload = array(
            'success_url'            => $options['hygglig_checkout_thanks_url'] ,
            'push_notification_url'   => site_url() . '/?hyggligCallback=1',
            'checkout_url'           => $options['hygglig_checkout_url'],
            'terms_url'              => $options['terms_url'],
            'order_reference'        => (string)$order->get_id(),
            'currency'              => get_woocommerce_currency()
        );

        $current_user = wp_get_current_user();
        $customer_id = get_current_user_id();
        $current_user_postcode = preg_replace('/\s+/', '', get_user_meta( $customer_id, 'shipping_postcode', true ) );

        foreach ( $cart as $item ) {
            $payload['articles'][] = $item;
        }

        hygglig_write_log("Update order total: " . Hygglig_Compatability::get_cart_total());
        hygglig_write_log("Update order payload $hygglig_order_number");
        hygglig_write_log( $payload );
		
		if($hygglig_order_number > 0){
			$response = Hygglig_Request::patch( $payload, self::get_api_checkout_url() . $hygglig_order_number );
	
			hygglig_write_log("Update order response complete" );
			//hygglig_write_log( $response );
	
			return $response;
		}else{
			hygglig_write_log("update_order Not INT VAL $hygglig_order_number " );
		}
		return '';
    }

    /** Send order to Hygglig
     * @param $order_id
     * @return mixed
     * @internal param string $safe_token
     */
    public static function cancel_order( $order_id ){

        hygglig_write_log("Cancelling order to Hygglig API api/orders/cancel ");
		
		$transaction_id = get_post_meta( $order_id, '_transaction_id', true );
		
        $response = Hygglig_Request::patch_order( self::get_api_url() . 'api/orders/cancel?order_number='.$transaction_id );
        hygglig_write_log( "Response for $transaction_id: ".$response );

    }

    /** Cancels order in Hygglig
     * @param $order_id
     * @return mixed
     * @internal param string $safe_token
     */
    public static function send_order( $order_id ){

        hygglig_write_log("Sending order to Hygglig API ".self::get_api_url() . "api/orders/send ");

        $transaction_id = get_post_meta( $order_id, '_transaction_id', true );

        $response = Hygglig_Request::patch_order( self::get_api_url() . 'api/orders/send?order_number='.$transaction_id);
		hygglig_write_log( "Response for $transaction_id: ".$response );
    }

    public static function format_success_url( $url, $order_id, $order_key ){

        if( strpos( $url ,'?' ) ){
            return $url . '&sid=' . $order_id . '&key=' . $order_key;
        }
        return $url . '?sid=' . $order_id . '&key=' . $order_key;


}
}