<?php
/**
 * Created by PhpStorm.
 * User: tomas
 * Date: 2018-03-28
 * Time: 14:15
 */

namespace classes\http;

if ( ! defined( 'ABSPATH' ) ) {
    //exit; // Exit if accessed directly
}

class Hygglig_Request{

    /** Sends POST with cURL
     * @param array $data
     * @param string $address
     * @return arraymixed
     */
	 public static function send( $data, $address ){

        $options = get_option( 'woocommerce_hygglig_checkout_settings' );
        
		//updated to use Wordpress HTTP API
        $payload = array
        (
            'httpversion' => '1.0',
            'headers'     => array
            (
                'Content-type' => 'application/json',
                'Accept' => 'application/json; charset=utf-8',
                'Authorization' => 'Basic ' . base64_encode( $options['merchantid'] . ':' . $options['secret'] ) 
            ),
            'timeout' => 30,
            'body'        => json_encode( $data ),
            'method'      => 'POST'
        );
        $response = wp_remote_request ($address,  $payload);
        if(is_wp_error( $response )){
            hygglig_write_log( "Hygglig send error:" . print_r( $response,true ) );
        }
        $response = wp_remote_retrieve_body($response);
        //echo "<pre>";print_r($response);echo "</pre>";exit;
        
        return $response;
    }
	
	public static function patch( $data, $address ){

        $options = get_option( 'woocommerce_hygglig_checkout_settings' );
        
		//updated to use Wordpress HTTP API
        $payload = array
        (
            'httpversion' => '1.0',
            'headers'     => array
            (
                'Content-type' => 'application/json',
                'Accept' => 'application/json; charset=utf-8',
                'Authorization' => 'Basic ' . base64_encode( $options['merchantid'] . ':' . $options['secret'] ) 
            ),
            'timeout' => 30,
            'body'        => json_encode( $data ),
            'method'      => 'PATCH'
        );
        $response = wp_remote_request ($address,  $payload);
        if(is_wp_error( $response )){
            hygglig_write_log( "Hygglig send error:" . print_r( $response,true ) );
        }
        $response = wp_remote_retrieve_body($response);
		
        return $response;
    }
	
	public static function patch_order( $address ){

        $options = get_option( 'woocommerce_hygglig_checkout_settings' );
        
        $payload = array
        (
            'httpversion' => '1.0',
            'headers'     => array
            (
                'Content-type' => 'application/json',
                'Accept' => 'application/json; charset=utf-8',
                'Authorization' => 'Basic ' . base64_encode( $options['merchantid'] . ':' . $options['secret'] ) 
            ),
            'timeout' => 30,
            'body'        => json_encode( $data ),
            'method'      => 'PATCH'
        );
        $response = wp_remote_request ($address,  $payload);
        if(is_wp_error( $response )){
            hygglig_write_log( "Hygglig send error:" . print_r( $response,true ) );
        }
        $response = wp_remote_retrieve_body($response);
		
        return $response;
    }
	
	public static function get( $address ){

        $options = get_option( 'woocommerce_hygglig_checkout_settings' );
        
        $payload = array
        (
            'httpversion' => '1.0',
            'headers'     => array
            (
                'Content-type' => 'application/json',
                'Accept' => 'application/json; charset=utf-8',
                'Authorization' => 'Basic ' . base64_encode( $options['merchantid'] . ':' . $options['secret'] ) 
            ),
            'timeout' => 30,
            'method'      => 'GET'
        );
        $response = wp_remote_request ($address,  $payload);
        if(is_wp_error( $response )){
            hygglig_write_log( "Hygglig send error:" . print_r( $response,true ) );
        }
        //echo "<pre>";print_r($response);echo "</pre>";exit;
        $response = wp_remote_retrieve_body($response);
		//echo "<pre>";print_r($response);echo "</pre>";exit;
        return $response;
    }
}