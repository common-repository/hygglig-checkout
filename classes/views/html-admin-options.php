<?php
/**
 * Created by PhpStorm.
 * User: tomaskircher
 * Date: 2018-03-30
 * Time: 11:29
 */



/**
 * Admin Panel Options
 * - Options for bits like 'title' and availability on a country-by-country basis
 *
 * @since 1.0.0
 */
?>
    <img src="<?php echo esc_url(HYGGLIG_URL) . '/img/hygglig.png' ?>" />

<p><?php printf(__('With Hygglig Checkout your customers can pay by invoice or credit card. Hygglig Checkout works by replacing the standard WooCommerce checkout form.<br>Documentation <a href="%s" target="_blank">can be found here</a>.', 'hygglig'), 'https://www.hygglig.com/' ); ?></p>

<?php
// If the WooCommerce terms page isn't set, do nothing.
$hygglig_terms_page = get_option('woocommerce_terms_page_id');
if ( empty( $hygglig_terms_page ) && empty( $this->terms_url ) ) {
    echo ('<strong>' . esc_html('You need to specify a Terms Page in the WooCommerce settings or in the Hygglig Checkout settings in order to enable the Hygglig Checkout payment method.', 'hygglig') . '</strong>');
}

// Check if Curl is installed. If not - display message to the merchant about this.
if( function_exists('curl_version' ) ) {
} else {
    echo ('<div id="' . esc_attr( 'message' ) . '" class="' . esc_attr( 'error' ) . '"><p>' . esc_html('The PHP library cURL does not seem to be installed on your server. Hygglig Checkout will not work without it.', 'hygglig') . '</p></div>');
}
?>
<table class="form-table">
<?php
    $allowed_atts_hygglig = allowed_atts_hygglig();
    $allowed_tags_hygglig = allowed_tags_hygglig();
    $list_tags = array();
    $dom = new DOMDocument();
    @$dom->loadHTML($settings_html);
    foreach($dom->getElementsByTagName('*') as $element ){
        if(!in_array($element->nodeName,$allowed_tags_hygglig)){
            $list_tags[$element->nodeName]= $allowed_atts_hygglig;
        }
    }
    $allowed_html = array_merge($allowed_tags_hygglig,$list_tags);
    echo wp_kses($settings_html,$allowed_html );
?>
</table><!--/.form-table-->
