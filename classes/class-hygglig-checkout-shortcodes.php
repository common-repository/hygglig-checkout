<?php

/**

 * Created by PhpStorm.

 * User: tomas

 * Date: 2018-03-29

 * Time: 15:05

 */



namespace classes;



use WC_Gateway_Hygglig_Checkout;



class Hygglig_Checkout_Shortcodes {





    public static function init() {

        add_action( 'init',  [ 'classes\Hygglig_Checkout_Shortcodes', 'start_session' ],1 );

        add_action( 'before_woocommerce_init', [ 'classes\Hygglig_Checkout_Shortcodes',  'prevent_caching' ] );//TODO might have to move this



        add_shortcode( 'woocommerce_hygglig_checkout',[ 'classes\Hygglig_Checkout_Shortcodes', 'hygglig_checkout_page' ] );
		
		add_shortcode( 'woocommerce_hygglig_cart',[ 'classes\Hygglig_Checkout_Shortcodes', 'hygglig_cart_page' ] );

        add_shortcode( 'woocommerce_hygglig_checkout_payment_options', [ 'classes\Hygglig_Checkout_Shortcodes', 'hygglig_payment_options' ] );
		
		add_shortcode( 'woocommerce_hygglig_checkout_other_payments', [ 'classes\Hygglig_Checkout_Shortcodes', 'hygglig_payment_options' ] );



        add_filter( 'woocommerce_get_checkout_url', [ 'classes\Hygglig_Checkout_Shortcodes',  'change_checkout_url' ], 20 );

        add_filter( 'woocommerce_get_checkout_page_id', [ 'classes\Hygglig_Checkout_Shortcodes', 'change_checkout_page_id' ] );



    }



    // Prevent caching in HCO and HCO thank you pages

    public static function prevent_caching() {

        $data = new WC_Gateway_Hygglig_Checkout;

        $hygglig_checkout_url = trailingslashit( $data->get_hygglig_checkout_url() );



        // Clean request URI to remove all parameters
        $req_url = sanitize_url($_SERVER['REQUEST_URI']);

        $clean_req_uri = explode( '?', $req_url );

        $clean_req_uri = $clean_req_uri[0];

        $clean_req_uri = trailingslashit( $clean_req_uri );



        $length = strlen( $clean_req_uri );



        // Get last $length characters from HCO and HCO thank you URLs

        $hygglig_checkout_compare = substr( $hygglig_checkout_url, 0 - $length );

        $hygglig_checkout_compare = trailingslashit( $hygglig_checkout_compare );



        if ( $clean_req_uri == $hygglig_checkout_compare ) {

            // Prevent caching

            if ( ! defined( 'DONOTCACHEPAGE' ) )

                define( "DONOTCACHEPAGE", "true" );

            if ( ! defined( 'DONOTCACHEOBJECT' ) )

                define( "DONOTCACHEOBJECT", "true" );

            if ( ! defined( 'DONOTCACHEDB' ) )

                define( "DONOTCACHEDB", "true" );



            nocache_headers();

        }

    }



    /**

     * Set session

     */

    public static function start_session() {


		if (class_exists('WC_Gateway_Hygglig_Checkout')) {
			$data = new WC_Gateway_Hygglig_Checkout();
	
			$enabled = $data->get_enabled();
	
	
	
			if( ! session_id() && $enabled == 'yes') {
	
				//session_start();
				session_start([
				   'read_and_close' => true,
				]);//updated for session issues at site health
			}
		}

    }



    /**

     * Shortcode Hygglig other payments

     */

    public static function hygglig_other_payments() {

        //Filter out successpage

        if (!isset($_GET['token'])) {

            ob_start();



            return ob_get_clean();

        }

    }



    /**

     * Shortcode Hygglig Checkout

     */

    public static function hygglig_checkout_page() {

		if(!is_admin() && !stristr($_SERVER['HTTP_REFERER'],"wp-admin/")){ 

        	$gateway = new WC_Gateway_Hygglig_Checkout;

        	return '<div class="hygglig_checkout">' . $gateway->get_hygglig_checkout_page() . '</div>';

		}

    }

	/**

     * Shortcode Hygglig Cart

     */

    public static function hygglig_cart_page() {

		if(!is_admin() && !stristr($_SERVER['HTTP_REFERER'],"wp-admin/")){ 

        	$gateway = new WC_Gateway_Hygglig_Checkout;

        	return '<div class="hygglig_cart">' . $gateway->get_hygglig_cart_page() . '</div>';

		}

    }

    /**

     * Shortcode Payment options widget

     */

    public static function hygglig_payment_options() {

        $data = 'data:image/png;base64,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';

        return '<img src="'. $data .'" class="hygglig_widget"/>';

    }



    /**

     *  Change Checkout URL

     *

     *  Triggered from the 'woocommerce_get_checkout_url' action.

     *  Alter the checkout url to the custom Hygglig Checkout Checkout page.

     *

     * @param $url

     * @return mixed|string|void

     */

    public static function change_checkout_url( $url ) {

        global $woocommerce;

        $data = new WC_Gateway_Hygglig_Checkout;

        $enabled = $data->get_enabled();

        $hygglig_checkout_url = $data->get_hygglig_checkout_url();

        $hygglig_country = $data->get_hygglig_country();

        $available_countries = $data->get_authorized_countries();



        // Change the Checkout URL if this is enabled in the settings and country and currency is Sweden

        if(  $enabled == 'yes' && ! empty( $hygglig_checkout_url ) && in_array( $hygglig_country, $available_countries ) && get_woocommerce_currency() == 'SEK') {

            $url = $hygglig_checkout_url;

        }



        return $url;

    }



    /**

     * Change checkout page ID to Hygglig Thank You page, when in Hygglig Thank You page only

     * @param $checkout_page_id

     * @return int

     */

    public static  function change_checkout_page_id( $checkout_page_id ) {

        global $post;

        $data = new WC_Gateway_Hygglig_Checkout;

        $hygglig_checkout_url = $data->get_hygglig_checkout_url();

        $hygglig_checkout_thanks_url = $data->get_hygglig_checkout_thanks_url();



        if ( is_page() ) {

            $current_page_url = get_permalink( $post->ID );

            // Compare Hygglig Thank You page URL to current page URL

            if ( ( esc_url( trailingslashit( $hygglig_checkout_url ) ) == esc_url( trailingslashit( $current_page_url ) )) || ( esc_url( trailingslashit( $hygglig_checkout_thanks_url ) ) == esc_url( trailingslashit( $current_page_url ) ) )) {

                $checkout_page_id = $post->ID;

            }

        }



        return $checkout_page_id;

    }

}