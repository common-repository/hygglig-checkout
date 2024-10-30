<?php
/**
 * Created by PhpStorm.
 * User: tomaskircher
 * Date: 2018-05-02
 * Time: 21:28
 */

namespace classes\Hyggligutils;

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

class Hygglig_Cart_Utils{

    /** Returns order items in hygglig format
     * @param \WC_Order $order
     * @return array
     */
    public static function format_order_items( $order ){

        $order_items = self::order_cart_item( $order );

        if( $order->get_shipping_method() ) {
            $order_items[] = self::shipping_cart_item( $order );
        }

        $rounding_item = self::round_cart_item( $order_items );

        if( $rounding_item ){
            $order_items[] = $rounding_item;
        }

        return $order_items;
    }

    /**
     * @param $cart
     * @return array|bool
     */
    public static function round_cart_item( $cart ){

        /*$sum = array_reduce( $cart, function ( $carry, $item ){
            $carry += floatval( $item['Quantity'] ) * floatval( $item['Price'] ) / 100;
            return $carry;
        }, 0 );
        $round_value = (Hygglig_Compatability::get_cart_total() * 100 ) - $sum ;

        if( round( $round_value,2 ) != 0 ){
            return array(
                'article_name' =>  'Öresavrundning',
                'article_number' => 'Öresavrundning',
                'description' =>  'Öresavrundning',
                'price' => intval( $round_value ),
                'quantity' => 100,
                'vat' => '2500');
        }*/
        return false;
    }

    /** Returns order product items in hygglig format
     * @param \WC_Order $order
     * @return array
     */
    public static function order_cart_item( $order ){

        $order_items = array();

        foreach ( $order->get_items() as $item ) {

            if( is_a( $item, 'WC_Order_Item_Product') ){

                if ( $item->get_quantity() > 0 ) {

                    $product = $item->get_product();
                    $item_name 	= $item['name'];

                    $order_items[] = array(
                        'article_name'   => substr( strip_tags( $item_name ),0,99 ),
                        'article_number' => self::get_product_reference( $product, $item ),
                        'description'   => self::get_product_description( $product ),
                        'price'         => (int)number_format( $order->get_item_total( $item, true ) * 100, 0, '', ''),
                        'quantity'      => intval($item->get_quantity() * 100 ),
                        'vat'           => intval( self::get_product_tax( $product, $order, $item ) )
                    );
                }
            }
        }

        return $order_items;
    }

    /** Return tax rate in hygglig format
     * @param \WC_Product $product
     * @param $order
     * @param $item
     * @return string
     */
    private static function get_product_tax( $product, $order, $item ){
        // We manually calculate the tax percentage here
        if ( $product->is_taxable() && $order->get_line_tax( $item )>0 ) {

            $tax = new \WC_Tax();
            $rates = $tax->get_rates( $product->get_tax_class() );
            return round(array_shift( $rates )['rate'] * 100,0);

        } else {
            return 00;
        }
    }

    /** Return product reference
     * @param WC_Product $product
     * @param $item
     * @return string
     */
    private static function get_product_reference( $product, $item ){
        //Prefer SKU
        if( $product->get_sku() != NULL ){
            return $product->get_sku();
        }
        else{
            //Build id as fallback
            return $item['product_id'] + $item['variation_id'];
        }
    }

    /** Get product description
     * @param \WC_Product $product
     * @return string
     */
    private static function get_product_description( $product ){
        $product_description = trim( $product->get_description() );

        if( strlen( $product_description ) == 0 ){

            $product_description = trim( get_post( $product->get_id() )->post_excerpt );

            if( strlen( $product_description ) == 0){

                $product_description = "Ingen beskrivning tillgänglig";
            }
        }

        return mb_substr( $product_description, 0, 149 );
    }

    /** Formats Shipping order item in Hygglig Format
     * @param \WC_Order $order
     * @return array
     */
    private static function shipping_cart_item( $order ){

        // We manually calculate the tax percentage here
        if ( $order->get_total_shipping() > 0) {
            // Calculate tax percentage
            $shipping_tax_percentage = round($order->get_shipping_tax() / $order->get_shipping_total(), 2) * 100 . '00';

        } else {
            $shipping_tax_percentage = 00;
        }

        $shipping_price = number_format( ( $order->get_shipping_total() + $order->get_shipping_tax() ) * 100, 0, '', '');

        return array(
            'article_name'       =>  $order->get_shipping_method(),
            'article_number'     => '999',
            'description'       =>  $order->get_shipping_method(),
            'price'             => intval( $shipping_price ),
            'quantity'          => 100,
            'vat'               => intval( $shipping_tax_percentage )
        );
    }
}