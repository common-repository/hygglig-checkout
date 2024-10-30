<?php

namespace classes\Hyggligutils;

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

use Exception;

class Hygglig_WC_Order_Utils{

    /**
     * Create new order for WooCommerce version 3.0+
     * @return int| \WP_ERROR
     */
    public static function create_order() {

        WC()->cart->calculate_totals();

        hygglig_write_log('Creating WooCommerce Order WC_Checkout');
		try{
			$checkout = new \WC_Checkout();
			hygglig_write_log('Called WC_Checkout');
			$data = array (
				'payment_method' => "Hygglig"
			);
			$order_id = $checkout->create_order( $data );
			hygglig_write_log('Called checkout->create_order');
			$order = wc_get_order( $order_id );
			$order->set_status('wc-hco-incomplete');
			hygglig_write_log('set_status '.$order_id.' wc-hco-incomplete');
        
            $order->save();
			hygglig_write_log('save order->save');
        }
        catch ( Exception $e ) {
            hygglig_write_log( $e->getMessage() );
        }

        return $order_id;
    }

    /** Updates ongoing hygglig order
     * @param \WC_Order $order
     * @return \WP_Error
     */
    public static function update_order( $order ){

        hygglig_write_log('Updating WooCommerce order');

        try {

            $order->remove_order_items();
            $order->set_created_via( 'Hygglig' );
            $order->set_customer_id( apply_filters( 'woocommerce_checkout_customer_id', get_current_user_id() ) );
            $order->set_currency( get_woocommerce_currency() );
            $order->set_prices_include_tax( 'yes' === get_option( 'woocommerce_prices_include_tax' ) );
            $order->set_customer_ip_address( \WC_Geolocation::get_ip_address() );
            $order->set_customer_user_agent( wc_get_user_agent() );
            $order->set_payment_method( 'Hygglig' );

            if( version_compare( WC_VERSION, '3.2', '>=') ) {
                $order->set_discount_total( WC()->cart->get_discount_total() );
                $order->set_discount_tax( WC()->cart->get_discount_tax() );
                $order->set_shipping_total( WC()->cart->get_shipping_total() );
                $order->set_cart_tax( WC()->cart->get_cart_tax() );
                $order->set_shipping_tax( WC()->cart->get_shipping_tax() );
            }
            else{
                $order->set_discount_total( WC()->cart->get_cart_discount_total() );
                $order->set_discount_tax( WC()->cart->get_cart_discount_tax_total() );
                $order->set_shipping_total( WC()->cart->shipping_total );
                $order->set_cart_tax( WC()->cart->tax_total );
                $order->set_shipping_tax( WC()->cart->shipping_tax_total );
            }

            $order->set_total( WC()->cart->total );

            $checkout = new \WC_Checkout;
            $checkout->create_order_line_items( $order, WC()->cart );
            $checkout->create_order_fee_lines( $order, WC()->cart );
            $checkout->create_order_shipping_lines( $order, WC()->session->get( 'chosen_shipping_methods' ), WC()->shipping->get_packages() );
            $checkout->create_order_tax_lines( $order, WC()->cart );
            $checkout->create_order_coupon_lines( $order, WC()->cart );
			
            $order->save();
        }
        catch ( Exception $e ) {
            return new \WP_Error( 'checkout-error', $e->getMessage() );
        }
    }
}