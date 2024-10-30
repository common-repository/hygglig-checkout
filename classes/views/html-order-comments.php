<?php

if ( ! defined( 'ABSPATH' ) ) {
    //exit;
}

?>
<section id="woocommerce-order-comments">
    <h2><?php _e('Orderkommentarer', HYGGLIG_CHECKOUT); ?></h2>
    <p><?php _e('Orderkomentar, exempelvis speciella leveranskrav.', HYGGLIG_CHECKOUT); ?></p>

    <?php
    $order_id = WC()->session->get( 'ongoing_hygglig_order' );
	if( $order_id ){
		$order = wc_get_order( $order_id );
       foreach ( $order->get_customer_order_notes() as $comment ) {
            echo '<p>' . esc_html( $comment->comment_content) . '</p>';
        }
    }
    ?>
    <a href="#" id="show-order-notes">+ <?php _e('Lägg till', HYGGLIG_CHECKOUT); ?></a>
    <form class="hco-order-notes" method="post"
          style="position: static; zoom: 1; display: none;">
        <p class="form-row form-row-first">
            <textarea name="order_comment"
                      class="input-text"
                      placeholder="<?php _e('Orderkommentar','woocommerce') ?>"
                      id="order_comment"></textarea>
        </p>
        <p class="form-row form-row-last">
            <input type="submit"
                   class="button"
                   name="add_order_notes" value="<?php _e('Lägg till','woocommerce'); ?>">
        </p>
        <div class="clear"></div>
    </form>
</section>

