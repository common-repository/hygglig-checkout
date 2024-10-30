<?php
namespace classes\hygglig;

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

use classes\Hyggligutils\Hygglig_Cart_Utils;
use classes\Hyggligutils\Hygglig_WC_Order_Utils;

class Hygglig_Order_Controller{

    /**
     * Initializes hygglig order hooks
     */
    public static function init_order_hooks(){
        $HOC = new Hygglig_Order_Controller();
		
		add_action( 'woocommerce_order_status_completed', [$HOC, 'send_hygglig_order'] );
        add_action( 'woocommerce_order_status_cancelled', [$HOC, 'cancel_hygglig_order'] );
        add_action( 'wp_trash_post', [$HOC, 'cancel_hygglig_order'] );
		
		add_action('woocommerce_order_status_failed', [$HOC, 'send_custom_email_notifications'] );
		add_action('woocommerce_order_status_processing', [$HOC, 'send_custom_email_notifications'] );
		add_action('woocommerce_order_status_changed', [$HOC, 'send_custom_email_notifications_changed'], 10, 3 );
    }

    /** Updates order to and sends it to Hygglig API
     * @param string $token
     * @param int $order_id
     * @return mixed
     */
    public static function update_hygglig_order( $hygglig_order_number, $order_id ){

        $order = wc_get_order( $order_id );
        hygglig_write_log("Updating order to hygglig");

        if ( sizeof( WC()->cart->get_cart() ) > 0 ) {

            Hygglig_WC_Order_Utils::update_order( $order );
            $cart = Hygglig_Cart_Utils::format_order_items( $order );
            return Hygglig_Api::update_order( $hygglig_order_number, $cart, $order );
        }
    }

    /** Cancels order in Hygglig API
     * @param int $order_id
     */
    public static function cancel_hygglig_order( $order_id ) {

        $options = get_option('woocommerce_hygglig_checkout_settings');

        if( $options['auto_cancel_order'] == 'yes'){
            Hygglig_Api::cancel_order( $order_id );
        }
		
    }

    /** Cancels order in Hygglig API
     * @param int $order_id
     */
    public static function send_hygglig_order( $order_id ) {
        $options = get_option('woocommerce_hygglig_checkout_settings');

        if( $options['auto_send_order'] == 'yes'){

            Hygglig_Api::send_order( $order_id );
        }
		
    }
	
	/* Add custome email order functions */
	function send_custom_email_notifications_changed( $order_id, $status_transition_from, $status_transition_to ){
		 $order = wc_get_order( $order_id );
		 $new_status = $order->get_status();
		 hygglig_write_log("HYGGLIG Status Changed -> new_status $new_status status_transition_from $status_transition_from status_transition_to $status_transition_to order_id -> $order_id");
			 
		if ( $new_status == 'on-hold' || ($status_transition_from == 'completed' && $status_transition_to == 'cancelled') ){
			$wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
			$customer_email = $order->get_billing_email(); // The customer email
		}
		
		if ( $new_status == 'on-hold' && $wc_emails['WC_Email_Customer_On_Hold_Order']->is_enabled() ) {
			// change the recipient of this instance
			$wc_emails['WC_Email_Customer_On_Hold_Order']->recipient = $customer_email;
			// Sending the email from this instance
			$wc_emails['WC_Email_Customer_On_Hold_Order']->trigger( $order_id );
			
			hygglig_write_log("WC_Email_Customer_On_Hold_Order trigger to ".$wc_emails['WC_Email_Customer_On_Hold_Order']->recipient);
		}
		else if ( ($status_transition_from == 'completed' && $status_transition_to == 'cancelled') && $wc_emails['WC_Email_Cancelled_Order']->is_enabled() ) {
			// Sending the email from this instance
			$wc_emails['WC_Email_Cancelled_Order']->trigger( $order_id );
			
			hygglig_write_log("WC_Email_Cancelled_Order trigger to ".$wc_emails['WC_Email_Cancelled_Order']->recipient);
		}
	}
	
	function send_custom_email_notifications( $order_id ){
		 $order = wc_get_order( $order_id );
		 $new_status = $order->get_status();
		 hygglig_write_log("HYGGLIG Status -> $new_status order_id -> $order_id");
			 
		if ( $new_status == 'failed' || $new_status == 'processing' ){
			$wc_emails = WC()->mailer()->get_emails(); // Get all WC_emails objects instances
			$customer_email = $order->get_billing_email(); // The customer email
		}
		
		if ( $new_status == 'failed' && $wc_emails['WC_Email_Failed_Order']->is_enabled() ) {
			// Sending the email from this instance
			$wc_emails['WC_Email_Failed_Order']->trigger( $order_id );
			
			 hygglig_write_log("WC_Email_Failed_Order trigger to ".$wc_emails['WC_Email_Failed_Order']->recipient);
		}
		elseif ( $new_status == 'processing' && $wc_emails['WC_Email_Customer_Processing_Order']->is_enabled() ) {
			// Sending the email from this instance
			$wc_emails['WC_Email_Customer_Processing_Order']->trigger( $order_id );
			
			hygglig_write_log("WC_Email_Customer_Processing_Order trigger to ".$wc_emails['WC_Email_Customer_Processing_Order']->recipient);
		}
	}
}