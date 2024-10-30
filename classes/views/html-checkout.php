<?php

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

?>

<?php $accent_color = get_option('woocommerce_hygglig_checkout_settings')['accent_color']; ?>

<style>
    .hygglig_checkout a {
        color: <?php print $accent_color; ?> !important;
        background: none !important;
    }
    .hygglig_checkout input[type=submit] {
        background: <?php print $accent_color; ?> !important;
    }
</style>
<input type="hidden" name="hco_token" value="<?php echo esc_attr( $token ) ; ?>" >
<div id="hygglig-frame">
    <section id="hco-contents">
        <div id="hco-checkout-area" class="checkout-area">
<?php
    error_reporting(E_ALL ^ E_WARNING); 
    $allowed_atts_hygglig = allowed_atts_hygglig();
    $allowed_tags_hygglig = allowed_tags_hygglig();
    $list_tags = array();
    $dom = new DOMDocument();
    @$dom->loadHTML($output);
    foreach($dom->getElementsByTagName('*') as $element ){
        if(!in_array($element->nodeName,$allowed_tags_hygglig)){
            $list_tags[$element->nodeName]= $allowed_atts_hygglig;
        }
    }
    $allowed_html = array_merge($allowed_tags_hygglig,$list_tags);
    echo html_entity_decode(wp_kses($output,$allowed_html), ENT_QUOTES | ENT_XML1, 'UTF-8');
?>  
        </div>
    </section>
</div>

<div id="hygglig_block" style="display:none;">
    <div class="hygglig-block-ui">
        <img src="<?php echo esc_url(HYGGLIG_URL) . '/img/spinner.png' ?>" />
    </div>
</div>