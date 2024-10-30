<?php
/**
 * Plugin Name: Hygglig Gateway for WooCommerce
 * Plugin URI: https://www.hygglig.com/
 * Description: Extends WooCommerce. Provides a <a href="https://www.hygglig.com/" target="_blank">Hygglig</a> gateway for WooCommerce.
 * Version: 3.7
 * Author: Hygglig
 * Author URI: https://www.hygglig.com
 * License: GPL2
 * WC requires at least: 3.0.0
 * WC tested up to: 6.5.1
*/

/***Prevent data leaks***/


if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

require_once "autoload.php";

use classes\hygglig\Hygglig_Order_Controller;
use classes\Hygglig_Checkout_Shortcodes;

//define( 'HYGGLIG_PATH', dirname(__FILE__ ) );
//define( 'HYGGLIG_URL', WP_PLUGIN_DIR. '/hygglig-checkout' );
define( 'HYGGLIG_PATH', untrailingslashit( plugin_dir_path( __FILE__ ) ) );
define( 'HYGGLIG_URL', untrailingslashit( plugins_url( basename( plugin_dir_path( __FILE__ ) ), basename( __FILE__ ) ) ) );
define( 'HYGGLIG_CHECKOUT', 'hygglig-checkout' );

/***ACTIONS START***/
add_action( 'plugins_loaded', 'init_hygglig_gateway', 2);
add_action( 'admin_enqueue_scripts', 'register_admin_scripts' );

//Add action to trigger function on callback, getOrders and updateCheckout
add_action( 'template_redirect', ['classes\Hygglig_Callback', 'callback'] );

add_filter( 'init', 'register_hygglig_incomplete_order_status' );
add_filter( 'wc_order_statuses', 'add_hygglig_incomplete_to_order_statuses' );
add_filter( 'woocommerce_valid_order_statuses_for_payment', 'hygglig_incomplete_payment_complete' );
add_filter( 'woocommerce_valid_order_statuses_for_payment_complete', 'hygglig_incomplete_payment_complete' );

/***FILTERS START***/
add_filter( 'woocommerce_payment_gateways', 'add_hygglig_gateway' );
add_filter( 'query_vars', 'hygglig_add_query_vars_filter');

add_filter( 'woocommerce_product_variation_title_include_attributes', '__return_false' );
add_filter( 'woocommerce_order_status_changed', 'disable_cancelled_status', 10, 4);

add_filter('woocommerce_new_order_email_allows_resend', '__return_true' );

//for some theme adding extra tags while having shortcodes
add_filter('register_block_type_args', function ($settings, $name) {
    if ($name === 'core/shortcode') {
            $settings['render_callback'] = function ($attributes, $content) {
                    return $content;
            };
    }
    return $settings;
}, 10, 2);
/***FILTERS END***/

function disable_cancelled_status( $order_id, $old_status, $new_status, $order ){
    if( 'cancelled' === $old_status ){
        if( 'Hygglig' === $order->get_payment_method() ){
            $order->set_status( $old_status );
            @$order->save();
        }
    }
}

function hygglig_enqueue_styles_scripts(){

    if( ! is_hygglic_checkout() ){
        return;
    }

    if ( ! is_checkout() ) {
        return;
    }

    wp_register_script( 'hygglig_checkout_js',  HYGGLIG_URL . '/js/default.js', ['jquery']);
    wp_enqueue_script(  'hygglig_checkout_js');

    wp_localize_script( 'hygglig_checkout_js',  'hygglig_ajax_checkout',[
        'ajaxurl'                     => admin_url('admin-ajax.php'),
        'nonce'                       => wp_create_nonce(HYGGLIG_CHECKOUT ),
        'shop_location'               => get_permalink( wc_get_page_id('shop') ),
        'cart_url'                    => wc_get_cart_url(),
        'safe_inputs'                 => [],
        'assign_handler'              => 0
    ] );
}

add_action( 'wp_enqueue_scripts', 'hygglig_enqueue_styles_scripts' );

//allowed tags for escapting html content
function allowed_atts_hygglig(){
    $allowed_atts_hygglig = array(
        'align'      => array(),
        'class'      => array(),
        'type'       => array(),
        'id'         => array(),
        'dir'        => array(),
        'lang'       => array(),
        'style'      => array(),
        'xml:lang'   => array(),
        'src'        => array(),
        'alt'        => array(),
        'href'       => array(),
        'rel'        => array(),
        'rev'        => array(),
        'target'     => array(),
        'novalidate' => array(),
        'type'       => array(),
        'value'      => array(),
        'name'       => array(),
        'tabindex'   => array(),
        'action'     => array(),
        'method'     => array(),
        'for'        => array(),
        'width'      => array(),
        'height'     => array(),
        'data'       => array(),
        'title'      => array(),
        'valign'     => array(),
        'scope'      => array(),
        'placeholder'=> array(),
        'checked'    => array(),
        'frameborder'=> array(),
        'scrolling'  => array()
    );
    return $allowed_atts_hygglig;
}
function allowed_tags_hygglig() {
    $allowed_atts_hygglig = allowed_atts_hygglig();
    $allowed_tags_hygglig['form']     = $allowed_atts_hygglig;
    $allowed_tags_hygglig['label']    = $allowed_atts_hygglig;
    $allowed_tags_hygglig['input']    = $allowed_atts_hygglig;
    $allowed_tags_hygglig['textarea'] = $allowed_atts_hygglig;
    $allowed_tags_hygglig['iframe']   = $allowed_atts_hygglig;
    $allowed_tags_hygglig['script']   = $allowed_atts_hygglig;
    $allowed_tags_hygglig['style']    = $allowed_atts_hygglig;
    $allowed_tags_hygglig['strong']   = $allowed_atts_hygglig;
    $allowed_tags_hygglig['small']    = $allowed_atts_hygglig;
    $allowed_tags_hygglig['table']    = $allowed_atts_hygglig;
    $allowed_tags_hygglig['span']     = $allowed_atts_hygglig;
    $allowed_tags_hygglig['abbr']     = $allowed_atts_hygglig;
    $allowed_tags_hygglig['code']     = $allowed_atts_hygglig;
    $allowed_tags_hygglig['pre']      = $allowed_atts_hygglig;
    $allowed_tags_hygglig['div']      = $allowed_atts_hygglig;
    $allowed_tags_hygglig['img']      = $allowed_atts_hygglig;
    $allowed_tags_hygglig['h1']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['h2']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['h3']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['h4']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['h5']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['h6']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['ol']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['ul']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['li']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['em']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['hr']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['br']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['tr']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['td']       = $allowed_atts_hygglig;
    $allowed_tags_hygglig['p']        = $allowed_atts_hygglig;
    $allowed_tags_hygglig['a']        = $allowed_atts_hygglig;
    $allowed_tags_hygglig['b']        = $allowed_atts_hygglig;
    $allowed_tags_hygglig['i']        = $allowed_atts_hygglig;

    return $allowed_tags_hygglig;
}

function init_hygglig_gateway() {

	// If the WooCommerce payment gateway class is not available, do nothing
	if ( !class_exists( 'WC_Payment_Gateway' ) ) return;

	// Define Hygglig root Dir
	define( 'HYGGLIG_DIR', dirname(__FILE__) . '/' );

	class WC_Gateway_Hygglig extends WC_Payment_Gateway {

	    public $shop_country;

		public function __construct(){
            global $woocommerce;
            $this->shop_country = get_option('woocommerce_default_country');

            if ( strstr( $this->shop_country, ':') ) {
                $this->shop_country = current( explode(':', $this->shop_country ) );
            }

        	// Get current customers selected language if this is a multi lanuage site
			$iso_code = explode('_', get_locale());
			$this->shop_language = strtoupper( $iso_code[0] ); // Country ISO code (SE)

            //Switch built to allow for addition of more countries
			switch ( $this->shop_language ) {
				case 'SV' :
					$this->shop_language = 'SE';
					break;
			}
			// Currency
			$this->selected_currency = get_woocommerce_currency();

			// Apply filters to shop_country
			$this->shop_country = apply_filters( 'hygglig_shop_country', $this->shop_country );
		}

		/**
	 	 * Helper function to check if curl exist or not on the server
	 	 */
		public function curl_exist(){
			if( function_exists('curl_version') ) {
				return true;
			} else {
				return false;
			}
		}

		public function payment_fields(){?>
            <fieldset>
                <p class="form-row form-row-wide">
                    <label for="<?php echo esc_attr($this->id); ?>-admin-note"><a href="<?php echo esc_url($this->hygglig_checkout_url); ?>">
                    </a>
                </p>
                <div class="clear"></div>
            </fieldset>
		<?php }

	} // End class WC_Gateway_Hygglig

	// Include the WooCommerce Compatibility Utility class
	// The purpose of this class is to provide a single point of compatibility functions for dealing with supporting multiple versions of WooCommerce (currently 2.0.x and 2.1)
    require_once 'classes/class-hygglig-checkout.php';
    require_once 'classes/class-hygglig-ajax-handler.php';

} // End init_hygglig_gateway

/**
 * Add the gateway to WooCommerce
 **/
function add_hygglig_gateway( $methods ) {
    $methods[] = 'WC_Gateway_Hygglig_Checkout';
	return $methods;
}

function register_hygglig_incomplete_order_status() {
    register_post_status( 'wc-hco-incomplete', array(
        'label'                     => 'Hygglig incomplete',
        'public'                    => true,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => false,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Hygglig incomplete <span class="count">(%s)</span>', 'Hygglig incomplete <span class="count">(%s)</span>' ),
    ) );
}

function add_hygglig_incomplete_to_order_statuses( $order_statuses ) {
    // Add this status only if not in account page (so it doesn't show in My Account list of orders)
    if ( ! is_account_page() ) {
        $order_statuses['wc-hco-incomplete'] = 'Incomplete Hygglig Checkout';
    }
    return $order_statuses;
}

function hygglig_incomplete_payment_complete( $order_statuses ) {
    $order_statuses[] = 'hco-incomplete';

    return $order_statuses;
}


//Queryvars belonging to Callback
function hygglig_add_query_vars_filter( $vars ){

    $vars[] = "token";
    $vars[] = "orderNumber";
    $vars[] = "email";
    $vars[] = "firstName";
    $vars[] = "lastName";
    $vars[] = "address";
    $vars[] = "postalCode";
    $vars[] = "city";
    $vars[] = "phoneNumber";
    $vars[] = "orderReference";
    $vars[] = "pushCheckSum";
    $vars[] = "hyggligCallback";
    $vars[] = "hid";
    $vars[] = "orderNoteUpdate";
    $vars[] = "orderNoteText";
    $vars[] = "orderCoupon";
    $vars[] = "orderCouponText";
    $vars[] = "pickupCart";
    $vars[] = "pay_for_order";
    $vars[] = "key";

    return $vars;
}

// Admin scripts
function register_admin_scripts( $hook ) {
	$options = get_option( 'woocommerce_hygglig_checkout_settings' );
	if( $options['auto_cancel_order'] == 'yes'){
		//Load script
		wp_enqueue_script( 'validatedelete-admin-script', HYGGLIG_URL . '/js/validatedelete.js' , array('jquery') );
	}

	// Add the color picker css file
	wp_enqueue_style( 'wp-color-picker' );

	// Include our custom jQuery file with WordPress Color Picker dependency
	wp_enqueue_script( 'admin-js', HYGGLIG_URL . '/js/admin.js', array( 'wp-color-picker' ), false, true );
}

function hygglig_write_log ( $log )  {
    if ( class_exists( 'WooCommerce' ) ) {

        $options = get_option( 'woocommerce_hygglig_checkout_settings' );
        if ( $options['debug'] != 'yes' ) {
            return;
        }

        $logger = wc_get_logger();
        $context = array( 'source' => 'hygglig_checkout' );
        if ( is_array( $log ) || is_object( $log ) ) {
            $logger->debug( print_r( $log, true ), $context );
        } else {
            $logger->debug( $log, $context );
        }
    }
    else{
        if ( is_array( $log ) || is_object( $log ) ) {
            error_log( print_r( $log, true ) );
        } else {
            error_log( $log );
        }
    }
}

Hygglig_Checkout_Shortcodes::init();
Hygglig_Order_Controller::init_order_hooks();

function is_hygglic_checkout() {
    if ( is_page() ) {

        $checkout_settings = get_option( 'woocommerce_hygglig_checkout_settings' );
        $checkout_pages    = array();
        $thank_you_pages   = array();

        // Clean request URI to remove all parameters.
        $req_url = sanitize_url($_SERVER['REQUEST_URI']);

        $clean_req_uri = explode( '?', $req_url );
        $clean_req_uri = $clean_req_uri[0];
        if( substr( $clean_req_uri, -1) == '/' ) {
            $clean_req_uri = substr( $clean_req_uri, 0, -1 );
        }
        $length        = strlen( $clean_req_uri );

        // Get arrays of checkout and thank you pages for all countries.
        if ( is_array( $checkout_settings ) ) {
            foreach ( $checkout_settings as $cs_key => $cs_value ) {
                if ( strpos( $cs_key, 'hygglig_checkout_url' ) !== false ) {
                    if( substr( $cs_value, -1) == '/' ) {
                        $cs_value = substr( $cs_value, 0, -1 );
                    }
                    $checkout_pages[ $cs_key ] = substr( $cs_value, 0 - $length );
                }
                if ( strpos( $cs_key, 'hygglig_checkout_thanks_url' ) !== false ) {
                    $thank_you_pages[ $cs_key ] = substr( $cs_value, 0 - $length );
                }
            }
        }

        // Start session if on a KCO or KCO Thank You page and KCO enabled.
        if ( in_array( $clean_req_uri, $checkout_pages, true ) || in_array( $clean_req_uri, $thank_you_pages, true ) ) {
            return true;
        }

        return false;
    }

    return false;
}
?>
