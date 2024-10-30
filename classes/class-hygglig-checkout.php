<?php

use classes\hygglig\Hygglig_Api;
use classes\Hygglig_Checkout_Templates;
use classes\hygglig\Hygglig_Checkout_Controller;


if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

class WC_Gateway_Hygglig_Checkout extends WC_Gateway_Hygglig {

    /**
     * ID of the merchant.
     *
     * @since 1.0.0
     * @var int
     */
    private $merchantid;

    /**
     * Hygglig secret.
     *
     * @since 1.0.0
     * @var string
     */
    private $secret;

    /**
     * The checkout url.
     *
     * @since 1.0.0
     * @var string
     */
    private $hygglig_checkout_url;

    /**
     * Sends completed order to hygglig handlarwebb.
     *
     * @since 1.0.0
     * @var string
     */
    private $auto_send_order;

    /**
     * Cancels order to hygglig handlarwebb.
     *
     * @since 1.0.0
     * @var bool
     */
    private $auto_cancel_order;

    /**
     * The thank you url.
     *
     * @since 1.0.0
     * @var bool
     */
    private $hygglig_checkout_thanks_url;

    /**
     * Testmode.
     *
     * @since 1.0.0
     * @var string
     */
    private $testmode;

    /**
     * The merchant ID.
     *
     * @since 1.0.0
     * @var string
     */
    private $hygglig_merchantid;

    /**
     * The secret.
     *
     * @since 1.0.0
     * @var string
     */
    private $hygglig_secret;

    /**
     * The checkout url.
     *
     * @since 1.0.0
     * @var string
     */
    private $hygglig_currency;

    /**
     * Enable coupons.
     *
     * @since 1.2.0
     * @var string
     */
    private $enable_coupons;

    /**
     * Enable other payment methods.
     *
     * @since 1.2.0
     * @var string
     */
    private $enable_other_payment_options;

    /**
     * Enable order comments.
     *
     * @since 1.2.0
     * @var string
     */
    private $enable_order_comment;

    /**
     * Authorized countries.
     *
     * @since 1.0.0
     * @var string
     */
    private $authorized_countries;

	const checkout_style_whitelist = [
		'admin-bar',
		'select2',
		'wcs-checkout',
		'wcsatt-css',
		'wc-bundle-style',
		'cxssh-main-css',
		'wtc_checkout_css_def',
		'wtc_checkout_css_fon',
		'wtc_checkout_css_emp',
		'wtc_checkout_css_mob',
		'animate',
		'hygglig_checkout_css',
		'hygglig_checkout_fontawesome',
	];


    public function __construct() {

		parent::__construct();

       	$this->id                   = 'hygglig_checkout';
		$this->method_title         = 'Hygglig';
		$this->title                = 'Betala med Hygglig';
		$this->method_description   = __( 'Betala med Hygglig', 'woocommerce' );
		$this->shop_country		    = get_option('woocommerce_default_country');

		$this->description = 'Betala med Hygglig';

       	$this->has_fields = false;

       	// Load the form fields.
       	$this->init_form_fields();

       	// Load the settings.
       	$this->init_settings();

       	// Define user set variables
       	$this->enabled								= ( isset( $this->settings['enabled'] ) ) ? $this->settings['enabled'] : '';
       	$this->merchantid							= sanitize_text_field(( isset( $this->settings['merchantid'] ) ) ? $this->settings['merchantid'] : '');
       	$this->secret								= sanitize_text_field(( isset( $this->settings['secret'] ) ) ? $this->settings['secret'] : '');
       	$this->hygglig_checkout_url					= esc_url(( isset( $this->settings['hygglig_checkout_url'] ) ) ? $this->settings['hygglig_checkout_url'] : '');
       	$this->hygglig_checkout_thanks_url			= esc_url(( isset( $this->settings['hygglig_checkout_thanks_url'] ) ) ? $this->settings['hygglig_checkout_thanks_url'] : '');
       	$this->terms_url							= esc_url(( isset( $this->settings['terms_url'] ) ) ? $this->settings['terms_url'] : '');
       	$this->testmode								= ( isset( $this->settings['testmode'] ) ) ? $this->settings['testmode'] : '';
       	$this->debug								= ( isset( $this->settings['debug'] ) ) ? $this->settings['debug'] : '';
		$this->std_checkout_button_label			= sanitize_text_field(( isset( $this->settings['std_checkout_button_label'] ) ) ? $this->settings['std_checkout_button_label'] : '');
	    $this->accent_color               			= strip_tags( stripslashes( trim( ( isset( $this->settings['accent_color'] ) ) ? $this->settings['accent_color'] : '') ) );
       	$this->create_customer_account				= ( isset( $this->settings['create_customer_account'] ) ) ? $this->settings['create_customer_account'] : '';
       	$this->send_new_account_email				= ( isset( $this->settings['send_new_account_email'] ) ) ? $this->settings['send_new_account_email'] : '';
       	$this->auto_send_order						= ( isset( $this->settings['auto_send_order'] ) ) ? $this->settings['auto_send_order'] : '';
		$this->auto_cancel_order					= ( isset( $this->settings['auto_cancel_order'] ) ) ? $this->settings['auto_cancel_order'] : '';
		$this->enable_coupons					    = ( isset( $this->settings['enable_coupons'] ) ) ? $this->settings['enable_coupons'] : '';
		$this->enable_other_payment_options			= ( isset( $this->settings['enable_other_payment_options'] ) ) ? $this->settings['enable_other_payment_options'] : '';
		$this->enable_order_comment					= ( isset( $this->settings['enable_order_comment'] ) ) ? $this->settings['enable_order_comment'] : '';

		if ( empty( $this->terms_url ) ){
            //removed for null get_page_permastruct issue
		   /*  if( function_exists( 'wc_get_page_id' ) ){
                $this->terms_url = esc_url( get_permalink( wc_get_page_id( 'terms') ) );
            }
            else{
                $this->terms_url = esc_url( get_permalink( woocommerce_get_page_id( 'terms') ) );
            } */
        }

		// Set current country based on used currency
		switch ( get_woocommerce_currency() ) {
			case 'SEK' :
				$hygglig_country = 'SE';
				break;
			default:
				$hygglig_country = '';
		}

		$this->shop_country	= $hygglig_country;

		// Country and language
		switch ( $this->shop_country ) {

			case 'SE' :
			case 'SV' :
				$hygglig_country 			= 'SE';
				$hygglig_language 			= 'sv-se';
				$hygglig_currency 			= 'SEK';
				$hygglig_merchantid 		= $this->merchantid;
				$hygglig_secret 			= $this->secret;
				$hygglig_checkout_url		= $this->hygglig_checkout_url;

				if ($this->hygglig_checkout_thanks_url == '' ) {
					$hygglig_checkout_thanks_url 	= $this->hygglig_checkout_url;
				} else {
					$hygglig_checkout_thanks_url 	= $this->hygglig_checkout_thanks_url;
				}

				break;
			default:
				$hygglig_country = '';
				$hygglig_language = '';
				$hygglig_currency = '';
				$hygglig_merchantid = '';
				$hygglig_secret = '';
				$hygglig_checkout_url = '';
				$hygglig_checkout_thanks_url = '';
		}

		// Apply filters to Country and language
		$this->hygglig_country 				= apply_filters( 'hygglig_country', $hygglig_country );
		$this->hygglig_language 			= apply_filters( 'hygglig_language', $hygglig_language );
		$this->hygglig_currency 			= apply_filters( 'hygglig_currency', $hygglig_currency );
		$this->hygglig_merchantid			= apply_filters( 'hygglig_merchantid', $hygglig_merchantid );
		$this->hygglig_secret				= apply_filters( 'hygglig_secret', $hygglig_secret );
		$this->hygglig_checkout_url			= apply_filters( 'hygglig_checkout_url', $hygglig_checkout_url );
		$this->hygglig_checkout_thanks_url	= apply_filters( 'hygglig_checkout_thanks_url', $hygglig_checkout_thanks_url );

	   	add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
       	add_action( 'woocommerce_api_wc_gateway_hygglig_checkout', array( $this, 'check_checkout_listener' ) );
       	add_action( 'woocommerce_cart_totals_coupon_html', array( $this, 'hygglig_coupon_discount_amount_html' ), 10,3 );

		if ( is_page() ) {

			global $post;
			$hygglig_checkout_thanks_page_id = url_to_postid ( $this->hygglig_checkout_thanks_url );
			$hygglig_checkout_page_id = url_to_postid ( $this->hygglig_checkout_url );

			if ( $post->ID == $hygglig_checkout_thanks_page_id ) {
				remove_action( 'woocommerce_thankyou', 'woocommerce_order_details_table', 10 );
			}
			if ( $post->ID == $hygglig_checkout_page_id ) {
				//commented for theme templates issues not to use custom template page
				
				//add_filter( 'page_template', array( $this, 'get_hygglig_page_template' ) ); 
				//clear all unnecessary styles
				//add_action( 'wp_print_styles', array( $this, 'remove_all_theme_styles' ) );
				//add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_checkout_style' ) );
			}
		}
    }

    /**
	 * Initialize Gateway Settings Form Fields
	 */
    function init_form_fields() {

        $this->form_fields = apply_filters('hygglig_checkout_form_fields', array(
            'enabled' => array(
                'title' => __( 'Enable/Disable', 'hygglig' ),
                'type' => 'checkbox',
                'label' => __( 'Enable Hygglig Checkout', 'hygglig' ),
                'default' => 'no'
            ),
            'merchantid' => array(
                'title' => __( 'Merchant id - Sweden', 'hygglig' ),
                'type' => 'text',
                'id'   => 'hygglig_merchantid',
                'description' => __( 'Please enter your Hygglig Merchantid for Sweden. ', 'hygglig' ),
                'default' => ''
            ),
            'secret' => array(
                'title' => __( 'Secret - Sweden', 'hygglig' ),
                'type' => 'text',
                'id'   => 'hygglig_secret',
                'description' => __( 'Please enter your Hygglig Secret for Sweden.', 'hygglig' ),
                'default' => ''
            ),
            'hygglig_checkout_url' => array(
                'title' => __( 'Custom Checkout Page - Sweden', 'hygglig' ),
                'type' => 'text',
                'description' => __( 'Please enter the URL to the page that acts as Checkout Page for Hygglig Checkout Sweden. This page must contain the shortcode [woocommerce_hygglig_checkout].', 'hygglig' ),
                'default' => ''
            ),
            'enable_order_comment' => array(
                'title' => __( 'Enable order comment in checkout form', 'hygglig' ),
                'type' => 'checkbox',
                'label' => __( 'Adds an input box in checkout for adding comments', 'hygglig' ),
                'default' => 'yes'
            ),
            'enable_coupons' => array(
                'title' => __( 'Enable coupons in checkout form', 'hygglig' ),
                'type' => 'checkbox',
                'default' => 'yes'
            ),
            'enable_other_payment_options' => array(
                'title' => __( 'Adds a link to other payment options', 'hygglig' ),
                'type' => 'checkbox',
                'default' => 'no'
            ),
            'hygglig_checkout_thanks_url' => array(
                'title' => __( 'Custom Thanks Page - Sweden', 'hygglig' ),
                'type' => 'text',
                'description' => __( 'Enter the URL to the page that acts as Thanks Page for Hygglig Checkout Sweden. This page must contain the shortcode [woocommerce_hygglig_checkout].', 'hygglig' ),
                'default' => ''
            ),
            'auto_send_order' => array(
                'title' => __( 'Auto Send Order', 'hygglig' ),
                'type' => 'checkbox',
                'label' => __( 'When order is set to Completed in WooCommerce, order is also set to Sent in Hygglig Handlarwebb.', 'hygglig' ),
                'default' => 'yes'
            ),
            'auto_cancel_order' => array(
                'title' => __( 'Auto Cancel Order', 'hygglig' ),
                'type' => 'checkbox',
                'label' => __( 'When order is set to Cancelled in WooCommerce, order is also Cancelled in Hygglig Handlarwebb.', 'hygglig' ),
                'default' => 'yes'
            ),
            'std_checkout_button_label' => array(
                'title' => __( 'Label for Standard Checkout Button', 'hygglig' ),
                'type' => 'text',
                'description' => __( 'Please enter the text for the button that links to the standard checkout page from the Hygglig Checkout form.', 'hygglig' ),
                'default' => ''
            ),
            'accent_color' => array(
                'title' => __( 'Accent color for Checkout page', 'hygglig' ),
                'type' => 'text',
                'description' => __( 'Please select the accent color used for links in the Hygglig Checkout form. Default Hygglig color is #E01E3B.', 'hygglig' ),
                'class' => 'color-picker-field',
                'default' => '#E01E3B'
            ),
            'terms_url' => array(
                'title' => __( 'Terms Page', 'hygglig' ),
                'type' => 'text',
                'description' => __( 'Please enter the URL to the page that acts as Terms Page for Hygglig Checkout. Leave blank to use the defined WooCommerce Terms Page.', 'hygglig' ),
                'default' => ''
            ),
            'create_customer_account' => array(
                'title' => __( 'Create customer account', 'hygglig' ),
                'type' => 'checkbox',
                'label' => __( 'Automatically create an account when new customer uses Hygglig Checkout.', 'hygglig' ),
                'default' => 'yes'
            ),
            'send_new_account_email' => array(
                'title' => __( 'Send New account email.', 'hygglig' ),
                'type' => 'checkbox',
                'label' => __( 'Send New account email when creating new accounts', 'hygglig' ),
                'default' => 'no'
            ),
            'testmode' => array(
                'title' => __( 'Test Mode', 'hygglig' ),
                'type' => 'checkbox',
                'label' => __( 'Enable Hygglig Test Mode. This will only work if you have a Hygglig test account.', 'hygglig' ),
                'default' => 'no'
            ),
            'debug' => array(
                'title' => __( 'Debug', 'hygglig' ),
                'type' => 'checkbox',
                'label' => __( 'Enable logging', 'hygglig' ),
                'default' => 'no'
            )
        ));

	}

	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 */
    public function admin_options() {
        $settings_html = $this->generate_settings_html( array(), false );
        include( 'views/html-admin-options.php' );
	} // End admin_options()

	/**
	 * Make the gateway disabled on the regular checkout page
	 *
	 */
    function is_available() {
        global $woocommerce;
        return false;
	}

    /**
     * Hygglig coupon discount amount html
     * @param $coupon_html
     * @param $coupon
     * @param $discount_amount_html
     * @return string
     */
    public function hygglig_coupon_discount_amount_html( $coupon_html, $coupon, $discount_amount_html) {
        return $discount_amount_html . ' <a href="#" class="hco-remove-coupon" data-coupon="' . esc_attr( $coupon->get_code() ) . '">' . __( '[Remove]', 'woocommerce' ) . '</a>';

    }

    /** Function for "Token set" *
     * @param $safe_token
     * @param $order_id
     */
    public function render_thank_you( $safe_token, $order_id ){ //$order_id needed for template
        global $woocommerce;

        // Debug
        hygglig_write_log( 'Rendering Thank you page...' );

        $hygglig_order_number = WC()->session->get( 'hygglig_order_number' );
		$text = Hygglig_Api::get_iframe( $hygglig_order_number );
        if(isset($text["html_snippet"])){
            $text = $text["html_snippet"];
        }else{
            $text = "";
        }
        include( 'views/html-thank-you.php' );
    }

    /**
     * @throws Exception
     */
    private function validate_checkout(){
        if ( $this->enabled != 'yes' ) throw new Exception( 'Settings are not valid' );
        if ( empty( $this->terms_url ) ) throw new Exception('Settings are not valid' );

        // If no Hygglig country is set - return.
        if ( empty( $this->hygglig_country ) ) {
            $url_endpoint = parse_url( get_permalink(get_option( 'woocommerce_checkout_page_id' ) ) );
            echo "<script>window.location.href = ". esc_url( $url_endpoint ). ";";
            throw new Exception('Settings are not valid' );
        }

        // Recheck cart items so that they are in stock
        $result = WC()->cart->check_cart_item_stock();
        if( is_wp_error( $result ) ) {
            throw new Exception( $result->get_error_message() );
        }

        // If checkout registration is disabled and not logged in, the user cannot checkout
        $checkout = WC()->checkout();
        if ( ! $checkout->enable_guest_checkout && ! is_user_logged_in() ) {
            echo apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) );
            throw new Exception();
        }
    }

    /** Validates a query param given by key, if not present execution exists
     * @param string $key
     * @return string
     * @throws Exception
     */
    function validate_query_param( $key ){

        $get_key = sanitize_text_field( $_GET[$key] );
        
        $param = strval( $get_key );
        if ( ! $param ) {
            $param = '';
            hygglig_write_log("Error in " . $key );
            throw new Exception( "Error in " . $key );
        }
        return $param;
    }

    /** Handles callback
     *
     */
    function handle_callback(){
        try {
            $safe_token = $this->validate_query_param('token');
        }
        catch ( Exception $e ){
            exit();
        }

        try{
            $order_id = $this->validate_query_param( 'sid' );
        }
        catch ( Exception $e ){

        }
        if( isset( $order_id ) ){
            $this->render_thank_you( $safe_token, $order_id);
        }
        else{

        }
    }

    function reset_session_vars(){
        WC()->session->__unset( 'hygglig_token' );
        WC()->session->__unset( 'ongoing_hygglig_order' );
		WC()->session->__unset( 'hygglig_order_number' );
    }

    function render_checkout_html(){
        // Set $add_hygglig_window_size_script to true so that Window size detection script can load in the footer
        global $add_hygglig_window_size_script;
        $add_hygglig_window_size_script = true;

        if ( sizeof( WC()->cart->get_cart() ) > 0 ) {

            $options = get_option( 'woocommerce_hygglig_checkout_settings' );
            $side_cart = Hygglig_Checkout_Templates::get_side_cart_html();
			$shipping_selector = Hygglig_Checkout_Templates::get_shipping_methods_html();
			$hygglig_order_number = WC()->session->get( 'hygglig_order_number' );
            $ongoing_order_id = WC()->session->get( 'ongoing_hygglig_order' );
			
			if( isset( $hygglig_order_number ) && isset( $ongoing_order_id )){
                try{
					$output = Hygglig_Checkout_Controller::update_checkout( $hygglig_order_number, $ongoing_order_id );
                } catch ( Exception $e ){
					$this->reset_session_vars();
                    $output = Hygglig_Checkout_Controller::create_checkout( $this->hygglig_currency );
                }
            }
            else{
                $output = Hygglig_Checkout_Controller::create_checkout( $this->hygglig_currency );

            }
            hygglig_write_log($output);

            $hygglig_order_number = WC()->session->get( 'hygglig_order_number' );

            ob_start();
            include( 'views/html-checkout.php' );
            return ob_get_clean();
        }
    }

    /**
     * Render checkout page
     */
    function get_hygglig_checkout_page() {

        // Display checkout

        hygglig_write_log('HCO page about to render...' );

        //Checkout country should always be Sweden
        WC()->customer->set_shipping_country('SE');

        if( isset( $_GET['sid'] ) && isset( $_GET['token'] ) ){
            $this->handle_callback();
        }
        else{
            if( isset( $_GET['token'] )  ){
                WC()->session->__unset( 'hygglig_token' );
                WC()->session->__unset( 'ongoing_hygglig_order' );
            }
            // Don't render the Hygglig Checkout form if the payment gateway isn't enabled.
            try{
                $this->validate_checkout();
            }
            catch ( Exception $e ){
                return $e->getMessage();
            }

            // Process order via Hygglig Checkout page
            if ( !defined( 'WOOCOMMERCE_CHECKOUT' ) ) define( 'WOOCOMMERCE_CHECKOUT', true );
            if ( !defined( 'WOOCOMMERCE_HYGGLIG_CHECKOUT' ) ) define( 'WOOCOMMERCE_HYGGLIG_CHECKOUT', true );

            // Set Hygglig Checkout as the choosen payment method in the WC session
            WC()->session->set( 'chosen_payment_method', 'Hygglig' );
            $this->customer_id = apply_filters( 'woocommerce_checkout_customer_id', get_current_user_id() );

            // Debug
            hygglig_write_log('Rendering Checkout page...' );
            return $this->render_checkout_html();
        }
    }

/**
     * Render Cart page
     */
	 function render_cart_html(){
        if ( isset(WC()->cart) && sizeof( WC()->cart->get_cart() ) > 0 ) {

            $options = get_option( 'woocommerce_hygglig_checkout_settings' );
            $side_cart = Hygglig_Checkout_Templates::get_side_cart_html();
			$shipping_selector = Hygglig_Checkout_Templates::get_shipping_methods_html();
			
			$token = WC()->session->get( 'hygglig_token' );
			
			ob_start();
			include "views/html-cart.php";
			return ob_get_clean();
        }
    }
	
    function get_hygglig_cart_page() {
        // Display cart
		hygglig_write_log('Rendering cart html in checkout page... ' );// Debug
        
        return $this->render_cart_html();
    }
	
    /**
     * FUNCTION THAT ADDS ROUNDING ITEM TO HYGGLIG ORDER
     * @param $total_amount
     * @return array
     */
    private function round_cart_item( &$total_amount ){
        /*$round_value = ( WC()->cart->total* 100 ) - $total_amount ;
        if( round( $round_value,2 ) != 0){
            $total_amount += $round_value;
            return array(
                    'article_name'   => 'Öresavrundning',
					'article_number' => 'Öresavrundning',
                    'description'   => 'Öresavrundning',
                    'price'         => intval($round_value),
                    'quantity'      => 100,
                    'vat'           => '2500'
            );
        }*/
    }

    /**
     * Helper function get_enabled
     */
    function get_enabled() {
        return $this->enabled;
    }

    /**
     * Helper function get_hygglig_checkout_page
     */
    function get_hygglig_checkout_url() {
        return $this->hygglig_checkout_url;
    }

    /**
     * Helper function get hygglig checkout thanks url
     */
    function get_hygglig_checkout_thanks_url() {
        return $this->hygglig_checkout_thanks_url;
    }

    /**
     * Helper function get_hygglig_country
     */
    function get_hygglig_country() {
        return $this->hygglig_country;
    }

    /**
     * Helper function - get authorized countries
     */
    function get_authorized_countries() {
        $this->authorized_countries		= array();
        if( ! empty( $this->merchantid ) ) {
            $this->authorized_countries[] = 'SE';
        }
        return $this->authorized_countries;
    }

    /**
     * Helper function - get correct currency for selected country
     * @param $country
     * @return string
     */
    function get_currency_for_country($country) {

        switch ( $country ) {
            case 'SE' :
                $currency = 'SEK';
                break;

            default:
                $currency = '';
        }
        return $currency;
    }

    /**
    * Add Metaboxes
    */
    public function add_hygglig_meta_box() {

        global $boxes;
        global $post;

        $order = wc_get_order( $post->ID );

        // Only on WC orders
        if( get_post_type() != 'shop_order' )
            return;

        if( $order->order_custom_fields['_payment_method'][0] == 'hygglig_checkout') {

            $boxes = apply_filters( 'hygglig_boxes', array(
                'status' => 'Hygglig'
            ) );

            //Add one Metabox for every $box_id
            foreach ($boxes as $box_id=>$box_label) {

                $screens = apply_filters( 'hygglig_screens', array( 'shop_order' ) );
                foreach ($screens as $screen) {
                    add_meta_box(
                        'hygglig_' . $box_id,
                        __( $box_label, 'hygglig' ),
                        array( &$this, 'render_meta_box_content' ),
                        $screen,
                        'normal', //('normal', 'advanced', or 'side')
                        'high', //('high', 'core', 'default' or 'low')
                        array( 'label' => $box_label, 'id' => $box_id)
                    );
                }
            }
        }
    }

    /**
     * Use custom template for checkout
     */
	function get_hygglig_page_template () {
		return HYGGLIG_PATH . '/hygglig-checkout-template.php';
	}

	/**
	 * Use custom css for checkout
	 */
	function enqueue_checkout_style () {
		wp_enqueue_style( 'hygglig_checkout_fontawesome', HYGGLIG_URL . '/css/fontawesome/css/fontawesome-all.css'); //fonts
		wp_enqueue_style( 'hygglig_checkout_css', HYGGLIG_URL . '/css/checkout/checkout.css', null, '0.1.0');
	}

	/**
	 * Return the filtered whitelist
	 */
	public static function get_style_whitelist() {
		return apply_filters( 'hygglig_checkout_style_whitelist', self::checkout_style_whitelist );
	}

	/**
	 * Remove everything except necessary css
	 */
	public static function remove_all_theme_styles() {
		global $wp_styles;
		$queue = $wp_styles->queue;
		$wp_styles->queue = array();
		foreach ($queue as $key => $value) {
            if (in_array($value, self::get_style_whitelist())) {
                array_push($wp_styles->queue, $value);
            }
        }
	}
}