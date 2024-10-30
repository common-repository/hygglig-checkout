<?php
/**
 * Created by PhpStorm.
 * User: tomaskircher
 * Date: 2018-03-30
 * Time: 12:08
 */
?>

<?php do_action( 'hygglig_before_hco_confirmation'); ?>

<div id="back-to-shop">
    <a href="<?php echo esc_url(get_permalink( wc_get_page_id( 'shop' ) )); ?>" class="back-link">
        <span class="left"><</span> <?php _e('Tillbaka', HYGGLIG_CHECKOUT ) ?>
    </a>
    <!-- Add logo here! -->
    <div class="logo-holder">
        <?php

        $logo = '';
        if( class_exists('FLTheme') ) :
            ob_start(); ?>
            <div class="fl-page-header-logo
                            fl-inline-logo-<?php echo esc_attr(\FLTheme::get_setting( 'fl-inline-logo-side' )); ?>
                            col-sm-12" itemscope="itemscope" itemtype="http://schema.org/Organization">
                <a href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php \FLTheme::logo(); ?></a>
            </div>
            <?php $logo = ob_get_clean();
        endif;

        if( ! $logo && function_exists('the_custom_logo')) {
            ob_start();
            the_custom_logo();
            $logo = ob_get_clean();
        }

        if( ! $logo && function_exists('get_header_image') ){
            ob_start();
            ?>
            <img src="<?php echo( get_header_image() ); ?>"
                 alt="<?php echo( get_bloginfo( 'title' ) ); ?>" />
            <?php
            $logo = ob_get_clean();
        }

        if( ! $logo ){
            $custom_logo_id = get_theme_mod( 'custom_logo' );
            $image = wp_get_attachment_image_src( $custom_logo_id , 'full' );
            echo '<img src="'.esc_url($image[0]).'" border="0" alt="'. esc_attr(get_bloginfo( 'title' ) ).'" />';
        }

        if( $logo ){
            echo wp_kses_post($logo);
        }
        else{
            echo wp_kses_post(get_bloginfo('name'));
        }
        ?>
    </div>
</div>
<div id="hygglig-viewport">
<?php if ( ( preg_match('/Success/', $text ) || preg_match('/success/',$text ) ) && isset( $order_id ) ){

    //Is success - clear session and cart
    do_action( 'woocommerce_thankyou', $order_id );

    WC()->session->__unset( 'hygglig_checkout' );
    WC()->session->__unset( 'ongoing_hygglig_order' );
    WC()->session->__unset( 'hygglig_token' );
	WC()->session->__unset( 'hygglig_order_number' ); 

    // Remove cart
    WC()->cart->empty_cart( true ); 
    ?>
    <script type="text/javascript">
        <!--
        //If Theme use "woo-menu-cart" hide it
        var x = document.getElementsByClassName( "woo-menu-cart" );
        if(x.length){
            x[0].style.display = 'none';
        }

    </script>
<?php
    $allowed_atts_hygglig = allowed_atts_hygglig();
    $allowed_tags_hygglig = allowed_tags_hygglig();
    $list_tags = array();
    $dom = new DOMDocument();
    @$dom->loadHTML($text);
    foreach($dom->getElementsByTagName('*') as $element ){
        if(!in_array($element->nodeName,$allowed_tags_hygglig)){
            $list_tags[$element->nodeName]= $allowed_atts_hygglig;
        }
    }
    $allowed_html = array_merge($allowed_tags_hygglig,$list_tags);
    echo html_entity_decode(wp_kses($text,$allowed_html), ENT_QUOTES | ENT_XML1, 'UTF-8');
}
?>
</div>

<?php do_action( 'hygglig_after_hco_confirmation', $order_id ); ?>
