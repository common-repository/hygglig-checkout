<?php

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

do_action( 'woocommerce_before_mini_cart' );
do_action( 'woocommerce_check_cart_items' );
?>
<?php if ( ! WC()->cart->is_empty() ) : ?>
    <div class="hygglig_-mini-cart">
        <?php
        //CHECK FOR WC CART ERRORS AND REDIRECT USING JS
        if( wc_notice_count( 'error' ) )
            echo '<input type="hidden" id="wc_errors_found" value="1" />';
        ?>
        <ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
            <?php

            do_action( 'woocommerce_before_mini_cart_contents' );

            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

                $_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                    $product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                    $thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
                    $product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
                    $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                    ?>

                    <li class="<?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">

                        <?php echo apply_filters( 'woocommerce_cart_item_remove_link', sprintf(
                            '<a href="%s" class="remove" title="%s" cart_item="%s" data-product_id="%s" data-product_sku="%s"><i class="fal fa-times"></i></a>',
                            esc_url( WC()->cart->get_remove_url( $cart_item_key ) ),
                            __( 'Remove this item', 'woocommerce' ),
                            esc_attr( $cart_item_key ),
                            esc_attr( $product_id ),
                            esc_attr( $_product->get_sku() )
                        ), $cart_item_key ); ?>

                        <?php if ( ! $_product->is_visible() ) : ?>

                            <?php echo esc_attr(str_replace( array( 'http:', 'https:' ), '', $thumbnail ) . $product_name . '&nbsp;'); ?>

                        <?php else : ?>

                            <a href="<?php echo esc_url( $_product->get_permalink( $cart_item ) ); ?>" class="cart-item-thumb">
                                <div>
                                    <?php echo esc_attr($thumbnail); ?>
                                </div>
                            </a>
                            <a href="<?php echo esc_url( $_product->get_permalink( $cart_item ) ); ?>" class="cart-item-info">
                                <div>
                                    <div class="cart-item-title"><?php echo esc_attr($product_name); ?></div>
                                    <?php if (! empty( WC()->cart->get_item_data( $cart_item ) ) ): ?>
                                        <div class="cart-item-variation"><?php echo esc_attr(WC()->cart->get_item_data( $cart_item )); ?></div>
                                    <?php endif; ?>
                                    <div class="cart-item-price"> <?php echo esc_attr($product_price); ?></div>
                                </div>
                            </a>
                        <?php endif; ?>

                        <?php
                        $parent_id = $_product->get_parent_id();

                        $pid = $_product->get_id();

                        //our own values - disabled since we are using WC plugin
                        //$min = get_post_meta( $pid, '_quantity_min', true);
                        //$max = get_post_meta( $pid, '_quantity_max', true);

                        $min = get_post_meta( $pid, 'minimum_allowed_quantity', true);
                        $max = get_post_meta( $pid, 'maximum_allowed_quantity', true);

                        if( ! $max && ! $_product->backorders_allowed() && $_product->is_in_stock() && $_product->get_manage_stock() && get_option('woocommerce_manage_stock') == 'yes' ){
                            $max = $_product->get_stock_quantity();
                        }

                        if(!$max) $max = "";
                        if(!$min || $min == 0) $min = 1;
                        $max_disabled = "";
                        $min_disabled = "";

                        if( $max && $cart_item['quantity']>=$max) $max_disabled = " disabled ";
                        if( $cart_item['quantity']<=$min) $min_disabled = " disabled ";

                        ?>
                        <div class="quantity">
                            <input type="number"
                                   name="quantity"
                                   step="1"
                                   pattern="{\d*}"
                                   cart_item_id="<?php echo esc_attr($cart_item_key); ?>"
                                   min="<?php echo esc_attr($min); ?>"
                                   max="<?php echo esc_attr($max); ?>"
                                   value="<?php echo esc_attr($cart_item['quantity'])?>"/>
                        </div>
                    </li>

                    <?php
                }
            }
            ?>

            <?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>

                <li class="cart-discount coupon-<?php echo esc_attr( sanitize_title( $code ) ); ?>">
                    <span class="coupon-label"><?php wc_cart_totals_coupon_label( $coupon ); ?></span>
                    <span class="coupon-data"><?php wc_cart_totals_coupon_html( $coupon ); ?></span>
                </li>

            <?php endforeach; ?>

            <?php do_action( 'woocommerce_mini_cart_contents' ); ?>
        </ul>
    </div>

<?php else : ?>

    <p class="hygglig_-empty-link">
        <?php
        echo sprintf( __('Your cart is empty. %s to go back to the shop.', ''),
            '<a href="'.esc_url(get_permalink( wc_get_page_id( 'shop' ) )).'">'.
            __('Click here', '').
            '</a>' );
        ?>
    </p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>