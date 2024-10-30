<?php

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

if( !isset(\WC()->cart) || \WC()->cart->is_empty() ) return;

//DEFAULT COUNTRY
$allowed_countries = array_keys( \WC()->countries->get_shipping_countries() );
if( !in_array( \WC()->customer->get_shipping_country(),  $allowed_countries ) )
    \WC()->customer->set_shipping_country( reset( $allowed_countries ) );

\WC()->customer->set_calculated_shipping(true);
\WC()->customer->save();
\WC()->cart->calculate_totals();
\WC()->customer->set_shipping_country('SE');

if ( ! function_exists('label_filter')) {
    function label_filter( $label, $method ){

        $method_id = str_replace(':', '_', $method->get_id());
        $options = get_option('woocommerce_' . $method_id . '_settings');
        $chosen_methods = isset( \WC()->session->chosen_shipping_methods ) ? \WC()->session->chosen_shipping_methods : [];
        \WC()->session->set( 'hygglig_chosen_shipping_methods', $chosen_methods );
        $shipping_cost = __('Free', 'woocommerce');

        if( $method->cost > 0) {
            if ( \WC()->cart->tax_display_cart == 'excl' ) {
                $shipping_cost = wc_price( $method->get_cost() );
                if ( $method->get_shipping_tax() > 0 && \WC()->cart->prices_include_tax ) {
                    $shipping_cost .= ' <small class="tax_label">' . \WC()->countries->ex_tax_or_vat() . '</small>';
                }
            }
            else {
                $shipping_cost = wc_price( $method->get_cost() + $method->get_shipping_tax() );
                if ( $method->get_shipping_tax() > 0 && !\WC()->cart->prices_include_tax ) {
                    $shipping_cost .= ' <small class="tax_label">' . \WC()->countries->inc_tax_or_vat() . '</small>';
                }
            }
        }

        $label = '<span class="shipping-label">' . $method->get_label() . '</span>
              <span class="shipping-cost">' . $shipping_cost . '</span>';


        if ( isset( $options['image'] ) && ! empty( $options['image'] ) ) {
            $label .= '<span class="shipping-image">
                        <img src="' . $options['image'] . '"
                             border="0" 
                             alt="'.get_option('blogname').'" />
                   </span>';
        }

        if ( isset($options['description'] ) && ! empty($options['description'] ) ) {
            $label .= '<span class="shipping-description">' . $options['description'] . '</span>';
        }

        if ( isset($options['features'] ) && ! empty($options['features'] ) ) {
            $label .= '<ul class="shipping-features">';
            $features = explode("\n", $options['features'] );
            foreach ( $features as $feature )
                $label .= '<li>' . $feature . '</li>';
            $label .= '</ul>';
        }

        return $label;

    }
}


add_filter('woocommerce_cart_shipping_method_full_label', __NAMESPACE__ . '\label_filter' , 99, 2);

wc_cart_totals_shipping_html();

remove_filter('woocommerce_cart_shipping_method_full_label', __NAMESPACE__ . '\label_filter');