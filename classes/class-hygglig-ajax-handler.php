<?php

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

use classes\hygglig\Hygglig_Order_Controller;
use classes\Hyggligutils\Hygglig_Compatability;
use classes\Hygglig_Checkout_Templates;


    /**
     * Handling ajax calls
     */
class Ajax_Handler{

    /**
     * Initialization for the class
     */
    public static function init(){
        add_action( 'wp_ajax_hygglig_ajax', [ __CLASS__, 'ajax_handle' ] );
        add_action( 'wp_ajax_nopriv_hygglig_ajax', [ __CLASS__, 'ajax_handle' ] );
    }

    /**
     * Handling AJAX routine
     */
    public static function ajax_handle(){
        //check nonce
        //if( ! wp_verify_nonce( $_POST['nonce'], '' ) ) die( '[ERROR] Authentication error' );

        if( ! defined( 'DOING_AJAX' ) ){
            define('DOING_AJAX', true);
        }

        hygglig_write_log($_POST);
        switch( $_POST['do'] ){
            case 'update':
                self::update_cart();
                break;
            case 'add_order_comment':
                self::add_order_comment();
                break;
            default: echo '[ERROR]' . __( 'Ajax action is not recognized', '' );
        }
        wp_die();
    }

    /**
     * Add Order note
     */
    private static function add_order_comment(){
        $order_id = WC()->session->get( 'ongoing_hygglig_order' );

        // Get an instance of the created order
        $order = wc_get_order( $order_id );

        if( isset( $_POST['message'] ) ) {

            remove_all_actions( 'woocommerce_new_customer_note' );

            $order->add_order_note( $_POST['message'], true, true );
            $response = [
                'success' => true
            ];
			$order->save();
            echo json_encode( $response );
        }
    }

    /**
     * Update cart and return fragments
     */
    private static function update_cart(){
        //update cart quantities and return new data
        $post_quantities = sanitize_text_field( $_POST['quantities'] );

        $quantities = (array)$post_quantities;

        /*CHECK SHIPPING METHODS*/

        if( 'default' !== $_POST['shipping'] ){
            WC()->session->set( 'chosen_shipping_methods', [ $_POST['shipping'] ] );
        }

        //SET COUNTRY CODE
        if( isset( $_POST['hygglig_billing_country_code']) && $_POST['hygglig_billing_country_code'] )
            WC()->customer->set_billing_country( $_POST['hygglig_billing_country_code']);
        if( isset( $_POST['hygglig_shipping_country_code'] ) && $_POST['hygglig_shipping_country_code'] )
            WC()->customer->set_shipping_country($_POST['hygglig_shipping_country_code']);

        WC()->customer->set_calculated_shipping( true );
        WC()->customer->save();

        //SET QUANTITIES IN THE CART
        foreach( $quantities as $q ){
            WC()->cart->cart_contents[$q['cart_item_id']]['quantity'] = $q['quantity'];
        }

        //Remove items which quantities are not exist
        $q_keys = array_map( function ( $a ) {
            return $a['cart_item_id'];
        }, $quantities );

        foreach ( WC()->cart->cart_contents as $cart_item_key => $values ) {
            if ( ! in_array( $cart_item_key, $q_keys ) )
                WC()->cart->remove_cart_item( $cart_item_key );
        }

        /*UPDATE CART*/
        WC()->cart->calculate_shipping();
        WC()->cart->calculate_fees();
        WC()->cart->calculate_totals( true );
        WC()->cart->persistent_cart_update();

        $checkout_response = json_decode( Hygglig_Order_Controller::update_hygglig_order( WC()->session->get( 'hygglig_order_number' ), WC()->session->get( 'ongoing_hygglig_order' ) ), true );

        $response = [
                'cart'                  => ( WC()->cart->is_empty() ? '[CART_EMPTY]' : Hygglig_Checkout_Templates::get_side_cart_html() ),
                'cart_total'            => Hygglig_Compatability::get_cart_total(),
                'cart_totals_html'      => WC()->cart->get_total(),
                'shipping'              => Hygglig_Checkout_Templates::get_shipping_methods_html(),
                'checkout'              => $checkout_response['html_snippet']
        ];

        if ( WC()->cart->get_cart_contents_count() == 0 ) {
            $response['cart_empty'] = true;
        }

        $options = get_option( 'woocommerce_hygglig_checkout_settings' );
        if( $options['enable_order_comment'] ){
            $response['order_comments'] = Hygglig_Checkout_Templates::get_order_comments_html();
        }
        echo json_encode( $response );
    }
}
Ajax_Handler::init();
