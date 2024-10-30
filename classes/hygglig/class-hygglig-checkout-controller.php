<?php

namespace classes\hygglig;

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

use classes\Hyggligutils\Hygglig_Cart_Utils;
use classes\Hyggligutils\Hygglig_Compatability;
use classes\Hyggligutils\Hygglig_WC_Order_Utils;

class Hygglig_Checkout_Controller{
    /** Processes order for Hygglig API and returns API Response
     * @param string $hygglig_currency
     * @return array
     */
    public static function create_checkout( $hygglig_currency ){
        // Get or create order_id
        $order_id = Hygglig_WC_Order_Utils::create_order();

        hygglig_write_log('Order created...');

        if ( ! is_numeric( $order_id ) ) {
            self::print_error( 'Something went wrong - ivalid order id '.$order_id );
        }

        WC()->session->set( 'ongoing_hygglig_order', $order_id );

        $order = wc_get_order( $order_id );

        $cart = Hygglig_Cart_Utils::format_order_items( $order );

        //Do roundvalue
        //$this->round_cart_item( $cart, $total_amount );

        hygglig_write_log('Get Checkout page...');

        $response_data = Hygglig_Api::create_checkout( $order, $cart, Hygglig_Compatability::get_cart_total(), $hygglig_currency );

        // Check for errors
        if ( empty( $response_data['html_snippet'] ) ) {
            hygglig_write_log( 'Getting Checkout Error...' ); 
            hygglig_write_log( $response_data ); 
            self::print_error( 'Something went wrong' );
            die();
        }
        else {
            hygglig_write_log('Order Processed '.$response_data["order_number"]);
			
            add_post_meta( $order_id, '_transaction_id', $response_data["order_number"], true );
			
			hygglig_write_log('set_transaction_id '.$response_data["order_number"]);
			 
			 
            WC()->session->set( 'hygglig_order_number', $response_data["order_number"] );
			 
			return $response_data["html_snippet"];
        }
    }

    /** Updates checkout
     * @param $token
     * @param $order_id
     * @return mixed
     * @throws \Exception
     */
    public static function update_checkout( $hygglig_order_number, $order_id ){

        $response = json_decode(Hygglig_Order_Controller::update_hygglig_order( $hygglig_order_number, $order_id), true);
		if ( !isset( $response['html_snippet'] ) ) {
            throw new \Exception('Invalid checkout');
        }
        return $response['html_snippet'];
    }

    /**
     * Print error
     * @param string $message
     */
    private static function print_error( $message ){
        echo '<ul class="woocommerce-error"><li>' . wp_kses_post( $message ) . '</li></ul>';
        exit();
    }
}