<input type="hidden" name="hco_token" value="<?php echo esc_attr( $token ) ; ?>" >
<div id="hygglig-cart"><section id="woocommerce-cart-contents">
            <h2><?php _e('Din varukorg', HYGGLIG_CHECKOUT); ?></h2>
            <div class="cart-foldable">
                <div id="hco-minicart-content" class="cart">
					<?php echo esc_html ( $side_cart ); ?>
                </div>

				<?php if( ! \WC()->cart->is_empty()) : ?>

                    <div id="hco-shipping-selector" class="shipping-selector">

						<?php if( !\WC()->cart->is_empty()) : ?>

                            <div class="shipping-methods-foldable">
								<?php echo  esc_html ( $shipping_selector ); ?>
                            </div>

						<?php endif; ?>

                    </div>
                    <div class="woocommerce-mini-cart__total total">
                <span class="table-text">
                    <?php
                    _e( 'Totalbelopp', 'woocommerce' );
                    $suffix = ( 'incl' === get_option( 'woocommerce_tax_display_cart' )? 'inkl. moms' : 'exkl. moms' );
                    echo  esc_html ( ' ' . $suffix );
                    ?>
                </span>
                        <span class="table-value">
                            <?php if( 'incl' === get_option( 'woocommerce_tax_display_cart' ) ):?>
                                <?php echo esc_attr(WC()->cart->get_total()); ?>
                            <?php else: ?>
                                <?php echo esc_attr(WC()->cart->get_total_ex_tax()); ?>
                            <?php endif;?>
                </span>
                    </div>

					<?php if( 'yes' == $options['enable_coupons'] ) : ?>

                        <div class="cart-discount">
                            <div class="woocommerce-info">
                                <a href="#" class="showcoupon"><?php _e('Har du en rabattkod?', 'woocommerce'); ?></a>
                            </div>
                            <form class="checkout_coupon" method="post"
                                  style="position: static; zoom: 1; display: none;">
                                <p class="form-row form-row-first">
                                    <input type="text" name="coupon_code"
                                           class="input-text"
                                           placeholder="<?php _e('Rabattkod','woocommerce') ?>"
                                           id="coupon_code" value="">
                                </p>
                                <p class="form-row form-row-last">
                                    <input type="submit"
                                           class="button"
                                           name="apply_coupon" value="<?php _e('Apply coupon','woocommerce'); ?>">
                                </p>
                                <div class="clear"></div>
                            </form>
                        </div>

					<?php endif;?>

                    <div class="cart-order-total" style="display: none;">
                <span class="sum cart-item-price">
                    <?php echo esc_attr(\WC()->cart->cart_contents_count . ' ' . __('pc', HYGGLIG_CHECKOUT) . ' (' . \WC()->cart->get_total() . ')'); ?>
                </span>
                    </div>

				<?php endif;?>

            </div>
        </section>
        <?php if( 'yes' == $options['enable_order_comment'] ) : ?>
            <?php include( 'html-order-comments.php' ); ?>
		<?php endif;?>

        <?php if( 'yes' == $options['enable_other_payment_options'] ) : ?>
            <section id="">
                <div class="wc-proceed-to-checkout">
                    <a id="HCO_otherPaymentMethods" href="<?php echo  esc_url(get_permalink( get_option('woocommerce_checkout_page_id') ));?>" class="checkout-button button alt wc-forward">
                        <?php echo esc_attr(get_option('woocommerce_hygglig_checkout_settings')['std_checkout_button_label']); ?>
                    </a>
                </div>
            </section>
        <?php endif;?>
</div>