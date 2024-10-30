<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 2018-03-28
 * Time: 18:18
 */

namespace classes;

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

use classes\hygglig\Hygglig_Order_Controller;
use WP_Error;

if ( ! defined( 'ABSPATH' ) ) {
    //exit; // Exit if accessed directly
}

class Hygglig_Callback{

    /**
     *
     */
    private static $required_args_create_order = array(
        'token'         => 'string',
        'email'         => 'string',
        'orderNumber'   => 'int',
        'firstName'     => 'string',
        'lastName'      => 'string',
        'address'       => 'string',
        'postalCode'    => 'int',
        'city'          => 'string',
        'phoneNumber'   => 'string',
        'orderReference'=> 'int',
        'pushCheckSum'  => 'string'
    );

    private static $required_args_update_order = array(
        'token'         => 'string',
        'hid'           => 'int',
    );


    /**
     * @param string $type
     */
    private static function validate_query_args( $type ){
        switch( $type ){
            case 'create_order':
                $required_args = self::$required_args_create_order;
                break;
            case 'update_order':
                $required_args = self::$required_args_update_order;
                break;
            default:
                //exit;
                break;
        }

        foreach ( $required_args as $key => $value ){
            $arg = $value == 'string' ? strval( get_query_var( $key ) ) : intval( get_query_var( $key ) );
            if ( ! $arg ) {
                echo "HTTP/1.0 400 BAD REQUEST\n";
                hygglig_write_log( "Error in " . $key);
                exit();
            }
        }
    }

    /**
     * @param \WC_Order $order
     */
    private static function set_wc2_address( &$order ){
        //Before 3.0 version of adding address
        // Billing
        if( strlen( $order->get_formatted_billing_address() ) < 10 ) {
            $order->set_address(
                array(
                    'first_name'    => strval( get_query_var('firstName') ),
                    'last_name'     => strval( get_query_var('lastName') ),
                    'address_1'     => strval( get_query_var('address') ),
                    'city'          => strval( get_query_var('city') ),
                    'postcode'      => strval( get_query_var('postalCode') ),
                    'email'         => strval( get_query_var('email') ),
                    'phone'         => strval( get_query_var('phoneNumber') )
                )
            );
        }

        // Shipping
        if( strlen( $order->get_formatted_shipping_address() ) <10 ) {
            $order->set_address(
                array(
                    'first_name'    => strval( get_query_var('firstName') ),
                    'last_name'     => strval( get_query_var('lastName') ),
                    'address_1'     => strval( get_query_var('address') ),
                    'city'          => strval( get_query_var('city') ),
                    'postcode'      => strval( get_query_var('postalCode') ),
                ),'shipping'
            );
        }
    }

    /**
     * @param \WC_Order $order
     * @param $safe_order_reference
     */
    private static function set_payment_data( &$order, $safe_order_reference, $transaction_id ){
        //$order->payment_complete( intval( get_query_var('orderNumber') ) ); disable for 10 digit hyglig order id
		
        //disable for order to not get auto completed 19-04-2023
        //$order->payment_complete( intval( $transaction_id ) );
        
        //added for status to get processing on 19-04-2023 
        $order->set_status('processing');
		hygglig_write_log('set_status '.$order_id.' processing');
		
        $order->set_payment_method( 'Hygglig' );
        $order->set_payment_method_title( 'Hygglig');
    }

    /** Set WooCommerce 3 address
     * @param \WC_Order $order
     */
    private static function set_wc3_address( &$order ){
        //Billing
        $order->set_billing_first_name( strval( get_query_var('firstName') ) );
        $order->set_billing_last_name( strval( get_query_var('lastName') ) );
        $order->set_billing_address_1( strval( get_query_var('address') ) );
        $order->set_billing_city( strval( get_query_var('city') ) );
        $order->set_billing_postcode( strval( get_query_var('postalCode') ) );
        $order->set_billing_email( strval( get_query_var('email') ) );
        $order->set_billing_phone( strval( get_query_var('phoneNumber') ) );

        //Shipping
        $order->set_shipping_first_name( strval( get_query_var('firstName') ) );
        $order->set_shipping_last_name( strval( get_query_var('lastName') ) );
        $order->set_shipping_address_1( strval( get_query_var('address') ) );
        $order->set_shipping_city( strval( get_query_var('city') ) );
        $order->set_shipping_postcode( strval( get_query_var('postalCode') ) );
    }

    /**
     * Update order
     */
    private static function update_order(){
        self::validate_query_args( 'update_order' );
            //CALL HYGGLIG CLASS
        Hygglig_Order_Controller::update_hygglig_order( strval( get_query_var('token' ) ), intval( get_query_var('hid' ) ) );
        echo "HTTP/1.0 200 OK\n";
        exit();
    }

    /**
     * Create order
     */
    private static function create_order(){
        ob_start();

        self::validate_query_args( 'create_order' );

        $safe_order_reference = intval( get_query_var('orderReference') );
        $safe_push_check_sum = strval( get_query_var('pushCheckSum') );
        $safe_email = strval( get_query_var('email') );

        $options = get_option( 'woocommerce_hygglig_checkout_settings' );
        //Compare sent pushCheckSum with created pushCheckSum - if valid - create order
        if( $safe_push_check_sum == strtoupper( sha1($safe_order_reference.strtolower( $options['secret'] ) ) ) ) {
            // Get an instance of the created order
            $order = wc_get_order( $safe_order_reference );

            hygglig_write_log( "Hygglig" . print_r( $order,true ) );

            if( version_compare( WC_VERSION, '3.0', '>=' ) ){
                //New way of adding address to order
                self::set_wc3_address( $order );
            }
            else{
                self::set_wc2_address( $order );
            }

            self::set_payment_data( $order, $safe_order_reference, $order->get_transaction_id() );
            //hygglig_write_log( "Payment for Order #" . $safe_order_reference." completed against Hygglig order_id ".$order->get_transaction_id().". Stocks reduced.");


            // Store user id in order so the user can keep track of track it in My account
            if( email_exists( $safe_email ) ) {
                hygglig_write_log(  'Hygglig Billing email: ' . $safe_email );
                $user = get_user_by('email', $safe_email );
                hygglig_write_log(  'Hygglig Customer User ID: ' . $user->ID );
                $customer_id = $user->ID;
                $order->set_customer_id( $customer_id );

            }
            else {
                // Create new user
                if( $options['create_customer_account'] == 'yes' ) {
                    $new_customer_id = self::create_new_customer( $safe_email );

                    // Check if creation of new customer was successfull
                    if ( is_wp_error( $new_customer_id ) ) {
                        $order->add_order_note( sprintf(__('Customer creation failed. Error: %s.', 'woocommerce'), $new_customer_id->get_error_message(), $safe_order_reference));
                        $customer_id = 0;

                    } else {
                        $order->add_order_note(sprintf(__('New customer created (user ID %s).', 'woocommerce'), $new_customer_id, $safe_order_reference ) );
                        // Send New account creation email to customer? //TODO
                    }

                    $order->set_customer_id( $new_customer_id );
					if( $options['send_new_account_email'] == 'yes' ) {
						$wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
						if ( $wc_emails['WC_Email_Customer_New_Account']->is_enabled() ) {
							// Sending the email from this instance
							$wc_emails['WC_Email_Customer_New_Account']->trigger( $new_customer_id );
							
							 hygglig_write_log("WC_Email_Customer_New_Account trigger to ".$wc_emails['WC_Email_Customer_New_Account']->recipient);
						}
					}
                }
            }
			$wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
			if ( $wc_emails['WC_Email_New_Order']->is_enabled() ) {
				// Sending the email from this instance
				$wc_emails['WC_Email_New_Order']->trigger( $safe_order_reference );
				
				 hygglig_write_log("WC_Email_New_Order trigger to ".$wc_emails['WC_Email_New_Order']->recipient);
			}
            //Save everything
            @$order->save();
            //Send response to M-order
            echo "HTTP/1.0 200 OK\n";
        }
        ob_end_flush();
        exit;
    }

    /**
     * Handles callback from marginalen
     */
    public static function callback() {

        hygglig_write_log( "POST RECEIVED" );

        //If Hygglig callback - validate all fields and create order on success
        if( get_query_var('hyggligCallback' ) ) {
            hygglig_write_log( "hyggligCallback" ." ".print_r($_REQUEST,1) );
            self::create_order();
        }
        //If Hygglig Update order - verify wp-nonce
        elseif( get_query_var('hid') && check_ajax_referer( get_query_var('token'), 'ajax_nonce' ) ){
            hygglig_write_log( "update_order" );
            self::update_order();
        }
    }

    /** Create a new customer
     *
     * @param  string $email
     * @return WP_Error on failure, Int (user ID) on success
    */
    public static function create_new_customer( $email ) {
        // Check the e-mail address
        if ( empty( $email ) || ! is_email( $email ) ) {
            return new WP_Error("registration-error", __("Please provide a valid email address.", "woocommerce"));
        }
        if ( email_exists( $email ) ) {
            return new WP_Error("registration-error", __("An account is already registered with your email address. Please login.", "woocommerce"));
        }

        // Handle username creation
        $username = sanitize_user( current( explode( '@', $email ) ) );

        // Ensure username is unique
        $append     = 1;
        $o_username = $username;

        while ( username_exists( $username ) ) {
            $username = $o_username . $append;
            $append ++;
        }

        // Handle password creation
        $password = wp_generate_password();
        $password_generated = true;

        // WP Validation
        $validation_errors = new WP_Error();

        do_action( 'woocommerce_register_post', $username, $email, $validation_errors );

        $validation_errors = apply_filters( 'woocommerce_registration_errors', $validation_errors, $username, $email );

        if ( $validation_errors->get_error_code() ) {
            return $validation_errors;
        }

        $new_customer_data = apply_filters( 'woocommerce_new_customer_data', array(
            'user_login' => $username,
            'user_pass'  => $password,
            'user_email' => $email,
            'role'       => 'customer'
        ) );

        $customer_id = wp_insert_user( $new_customer_data );

        //Billing address
        update_user_meta( $customer_id, "billing_first_name", sanitize_text_field($_POST['firstName']) 	);
        update_user_meta( $customer_id, "billing_last_name", sanitize_text_field($_POST['lastName']) 	);
        update_user_meta( $customer_id, "billing_address_1", sanitize_text_field($_POST['address']) 		);
        update_user_meta( $customer_id, "billing_city"	, sanitize_text_field($_POST['city']) 		);
        update_user_meta( $customer_id, "billing_postcode"	, intval($_POST['postalCode']) 	);
        update_user_meta( $customer_id, "billing_email", sanitize_email($_POST['email']) 		);
        update_user_meta( $customer_id, "billing_phone", sanitize_text_field($_POST['phoneNumber']) 	);

        //Shipping address
        update_user_meta( $customer_id, "shipping_first_name",sanitize_text_field($_POST['firstName']) );
        update_user_meta( $customer_id, "shipping_last_name",sanitize_text_field($_POST['lastName']) 	);
        update_user_meta( $customer_id, "shipping_address_1",sanitize_text_field($_POST['address']) );
        update_user_meta( $customer_id, "shipping_city",sanitize_text_field($_POST['city']) );
        update_user_meta( $customer_id, "shipping_postcode",intval($_POST['postalCode']) );

        if ( is_wp_error( $customer_id ) ) {
            return new WP_Error("registration-error", '<strong>' . __('ERROR', 'woocommerce') . '</strong>: ' . __('Couldn&#8217;t register you&hellip; please contact us if you continue to have problems.', 'woocommerce'));
        }

        return $customer_id;
    }
}