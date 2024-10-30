<?php

namespace classes;

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

class Hygglig_Checkout_Templates{

    /** Renders checkout side cart
     * @return string
     */
    public static function get_side_cart_html() {
        ob_start();
        include HYGGLIG_PATH . "/classes/views/html-checkout-cart.php";
        return ob_get_clean();
    }

    /** Renders checkout shipping methods
     * @return string
     */
    public static function get_shipping_methods_html(){
        ob_start();
        include HYGGLIG_PATH . "/classes/views/html-checkout-shipping-methods.php";
        return ob_get_clean();
    }

    /** Renders checkout order comments
     * @return string
     */
    public static function get_order_comments_html() {
        ob_start();
        include HYGGLIG_PATH . "/classes/views/html-order-comments.php";
        return ob_get_clean();
    }
}