<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 2018-05-11
 * Time: 13:57
 */

namespace classes\Hyggligutils;

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

class Hygglig_Compatability{

    /**
     * Returns cart total
     * @return float|mixed
     */
    public static function get_cart_total(){
        if( method_exists( WC()->cart, 'get_totals' ) ){

            $totals = WC()->cart->get_totals();
            return $totals['total'];
        }
        else{
            return  WC()->cart->total;
        }
    }
}