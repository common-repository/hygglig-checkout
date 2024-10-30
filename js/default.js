var hygglig_update = (function($) {

        cart_total_quantity = 0, //for keeping current cart quantities and check if it is actually changed - if not - do nothing

        updater = 0, //update interval/timer - used to interrupt scheduled update

        blocker = '', //layer for blocking the checkout

        is_updating = false, //layer for blocking the checkout

        blocked_on_purchase = false, //flag if checkout is blocked on pressing the purchase button
        /**
         * Caller to reassign the events
         * Used to be sure we assign events once
         * This is important since some elements have mixed handlers: ours and Woo/Stripe
         */
        call_assign_events = function(){
            var t = 200;
            clearTimeout( hygglig_ajax_checkout.assign_handler );
            hygglig_ajax_checkout.assign_handler = setTimeout(function(){
                assign_events();
            }, t);
        },

        /**
         * Assign event handlers
         */
        assign_events = function () {

            /**
             * Quantity change
             */
            $('li.mini_cart_item div.quantity input[name=quantity]').off().on('keyup', function () {
                var val = Number($(this).val());
                var max = Number($(this).parent().find('input[name=quantity]').attr('max'));
                var min = Number($(this).parent().find('input[name=quantity]').attr('min'));
                if (!val || val == 0) val = 1;
                else {
                    if (max && val > max) val = max;
                    if (min && val < min) val = min;
                }
                //$(this).val(val);
            }).on('change', function(){
                ajax_update(false);
            });

            /**
             * Remove item from mini-cart
             */
            $('.mini_cart_item .remove').off().on( 'click', function (e) {
                $(this).parents('li').first().find('input[name=quantity]').attr('disabled', true);
                var cart_item = $(this).attr('cart_item');
                ajax_update( false );
                return false;
            });

            /**
             * Ship to different address
             */
            if ($('div.checkout-area #ship-to-different-address-checkbox').length)
                $('div.checkout-area #ship-to-different-address-checkbox').off().on('change', function (e) {
                    if (this.checked) {
                        $('div.checkout-area div.shipping_address').fadeIn();
                        $('select#shipping_country').off().on('change', function(){
                            ajax_update(false);
                        });
                    }
                    else {
                        $('div.checkout-area div.shipping_address').fadeOut();
                    }
                    //FORCE TO UPDATE IF SHIPPING AND BILLING ARE DIFFERENT
                    if ($('select#shipping_country').val() !== $('select#billing_country').val())
                        ajax_update(false);
                });

            /**
             * Shipping selector change
             */
            $('.shipping-selector input.shipping_method').off().on('change', function (e) {
                $(this).parents('ul').find('li').removeClass('selected');
                $(this).parents('li').addClass('selected');
                cart_total_quantity = 0; //force_update
                ajax_update({'shipping': $(this).val(), 'payment': 'default'});
                var id = '#' + $(this).attr('id').substr(4);
                $(id).trigger('click');
            });

            /**
             * Show order notes
             */
            $('a#show-order-notes').on('click', function(event){
                event.preventDefault();
                $( '.hco-order-notes' ).slideToggle( 400, function() {
                    $( '.hco-order-notes' ).find( ':input:eq(0)' ).focus();
                });
            });

            /**
             * Submit order notes
             */
            $('form.hco-order-notes').submit( function(event){
                is_updating = true;
                event.preventDefault();
                var $form = $( this );

                blockCheckout();

                $.ajax({
                    url: hygglig_ajax_checkout.ajaxurl,
                    type: 'post',
                    data: {
                        action: 'hygglig_ajax',
                        do: 'add_order_comment',
                        message:	$form.find( 'textarea[name="order_comment"]' ).val(),
                        nonce: hygglig_ajax_checkout.nonce,
                        dataType: 'html'
                    },
                    success: function () {
                        unblockCheckout();
                        cart_total_quantity = -1;
                        ajax_update(false);
                    }
                });

                return false;
            });

            /**
             * Update after coupon had been applied
            */
            $( document.body ).on( 'update_checkout', function(){

                if( ! is_updating ){
                    is_updating = true;
                    cart_total_quantity = -1;
                    ajax_update(false);
                }

            });

            /**
             * Remove coupon and update
             */
            $('.cart-discount .hco-remove-coupon').off().on('click', function(event){
                event.preventDefault();
                var data = {
                    security: wc_checkout_params.remove_coupon_nonce,
                    coupon:   $('.cart-discount .hco-remove-coupon').attr('data-coupon')
                };

                $.ajax({
                    type:    'POST',
                    url:     wc_checkout_params.wc_ajax_url.toString().replace( '%%endpoint%%', 'remove_coupon' ),
                    data:    data,
                    success: function( code ) {
                        cart_total_quantity = -1;
                        ajax_update(false);
                    },
                    error: function ( jqXHR ) {
                        if ( wc_checkout_params.debug_mode ) {
                            /* jshint devel: true */
                            console.log( jqXHR.responseText );
                        }
                    },
                    dataType: 'html'
                });
            });

            /**
             * Init hygglig events
             */
            $( document ).trigger( 'hygglig_reassign' );

            /**
             * Customizations
             */

            //----------------------------------------------------------------------------------------------------------

            /*CHANGE SHIPPING SELECTORS TO WORK SEPARATELY FROM STANDARD SELECTORS*/

            $('.shipping-selector label').each(function () {
                $(this).attr('for', 'hygglig_' + $(this).attr('for'));
            });

            $('.shipping-selector input').each(function () {
                $(this).attr('id', 'hygglig_' + $(this).attr('id'));
            });

            //----------------------------------------------------------------------------------------------------------

            /* CHANGE SHIPPING TO DIFFERENT COUNTRY -------------------------------------------------------------------*/
            // Delay assignment of the event to 600 ms, because WOO refreshes country selectors after rendering the page
            var tst = setTimeout(function(){
                clearTimeout(tst);
                $('select#billing_country, select#shipping_country').on('change', function (e) {
                    ajax_update(false);
                });
            },1200);
            //----------------------------------------------------------------------------------------------------------

        }, //assign events

        /**
         * Block checkout from editing
         */
        blockCheckout = function() {

            $('body').block({
                message: blocker,
                overlayCSS: {
                    background: '#fff',
                    opacity: 0.7
                },
                css: {
                    border: 'none',
                    background: 'none'
                }
            });
        },

        /**
         * Unblock checkout area
         *
         * @param do_not_erase_coupon_msg
         */
        unblockCheckout = function( do_not_erase_coupon_msg ) {
            $('body').unblock();

            $('#hygglig_block').hide();
            if (!do_not_erase_coupon_msg)
                $('div.cart-discount .woocommerce-message').fadeOut('400', function () {
                    $(this).remove();
                    clearTimeout(updater);
                });
        },


        /**
         * Ajax checkout updater (complete update)
         */
        ajax_update = function( params, event, item ) {

            //Set params
            params = params || {'shipping': 'default', 'payment': 'default', 'coupon_code': ''};

            //Collect quantities of the cart items
            var quantities = [];
            var ttl_q = 0;
            $('.woocommerce-mini-cart input[name=quantity]:not(:disabled)').each(function (e) {
                quantities.push({'cart_item_id': $(this).attr('cart_item_id'), 'quantity': $(this).val()});
                ttl_q += Number($(this).val());
            });

            //check if update is really needed - in case we did not change quantity actually ( +- = 0 )
            if (ttl_q !== cart_total_quantity) {
                cart_total_quantity = ttl_q;
            } else return false;
            //Block checkout
            blockCheckout();

            //fetch chosen country code (if available)
            if(params.country){
                billing_country_code = shipping_country_code = params.country;
            }else{
                var billing_country_code = 0;
                if ($('select#billing_country').length)
                    billing_country_code = $('select#billing_country').val();

                var shipping_country_code = billing_country_code;
                if ( $('select#shipping_country').length
                     && $('input[name=ship_to_different_address]:checked').length )
                        shipping_country_code = $('select#shipping_country').val();

                if(!shipping_country_code){
                    billing_country_code = fsaver.get_country( 'billing' );
                    if( !fsaver.get_extra_shipping_enabled() )
                        shipping_country_code = billing_country_code;
                    else
                        shipping_country_code = fsaver.get_country( 'shipping' );
                }
            }

            //Call update
            $.ajax({
                url: hygglig_ajax_checkout.ajaxurl,
                data: {
                    action                          : 'hygglig_ajax',
                    do                              : 'update',
                    nonce                           : hygglig_ajax_checkout.nonce,
                    quantities                      : quantities,
                    hygglig_billing_country_code    : billing_country_code,
                    hygglig_shipping_country_code   : shipping_country_code,
                    shipping                        : params.shipping,
                    coupon_code                     : params.coupon_code,
                    payment                         : params.payment,
                    token                           : $('input[name=hco_token]').val()
                },
                type: 'post',
                success: function (data) {
					location.reload();				  
                    is_updating = false;
                    // var response = JSON.parse(data);

                    // if (response && !response.error) {

                        // //check if cart empty - reload the page
                        // if(response.hasOwnProperty('cart_empty')){
                            // window.location.reload(true);
                            // return 0;
                        // }

                        // //render mini-cart
                        // $('#hco-minicart-content').html( response.cart );

                        // //update mobile view total
                        // $('.cart-order-total .sum').html( response.cart_total );
                        // $('.woocommerce-mini-cart__total > span.table-value ').html(response.cart_totals_html);

                        // //render checkout part
                        // if (response.checkout) {
                            // _hyggligCheckout.updateHygglig();
                        // }

                        // //render checkout part
                        // if (response.order_comments) {
                            // $('#woocommerce-order-comments').replaceWith(response.order_comments);
                        // }

                        // //shipping part
                        // $('.hygglig-checkout-cart-wrapper .right-col .shipping-methods-foldable').html( response.shipping) ;

                        // //unblock
                        // unblockCheckout(0);

                        // //reassign events
                        // call_assign_events();
                    // }
                    // else
                        // console.log(response);
                },
                error: function (a, b, error) {
                    unblockCheckout(0);
                    console.log(error);
                }
            });

        },//ajax updater

        checkForInput = function ( element ) {
            // element is passed to the function ^

            const $label = $(element).parents('p.form-row');

            if ($(element).val().length > 0) {
                $label.addClass('input-has-value');
            } else {
                $label.removeClass('input-has-value');
            }

            $(element).attr('placeholder', '');
        },

        /**
         * Detect touch events
         * Added by @Anton
         * Changed by Stim :)
         * @param delay
         */
        addInputEvents = function (delay) {
            setTimeout(function () {
                $('input, select').on('change keyup touchend', function () {
                    checkForInput(this);
                }).each( function () {
                    checkForInput(this);
                });
            }, delay);
        },

        /**
         * Update select2 objects on page load
         */
        update_woo_select2 = function (){
            $(  'select#shipping_country:not(.select2-hidden-accessible), ' +
                'select#billing_country:not(.select2-hidden-accessible),  ' +
                'select#billing_state:not(.select2-hidden-accessible),    ' +
                'select#shipping_state:not(.select2-hidden-accessible)    '
            ).selectWoo({width: 'style'});
        },

        /**
         * Input fields saver
         */
        fsaver = {

            clear: function () {
                hygglig_ajax_checkout.safe_inputs = [];
            },

            save: function () {
                fsaver.clear();
                $( 'div#customer_details input, ' +
                    'div#customer_details select, ' +
                    'div#customer_details textarea, ' +
                    'input#terms' ).each(function () {
                    if (!$(this).attr('id')) return;
                    hygglig_ajax_checkout.safe_inputs.push({
                        id      : $(this).attr('id'),
                        value   : $(this).val(),
                        checked : $(this).attr('checked')
                        //events  : fsaver.get_events( $(this) )
                    });
                });
            },

            restore: function () {
                if (!hygglig_ajax_checkout.safe_inputs.length) return;
                var i = 0;
                var f;
                while ( hygglig_ajax_checkout.safe_inputs[i] && ( f = hygglig_ajax_checkout.safe_inputs[i++]) )
                    $( '#' + f.id ).val( f.value ).attr( 'checked', f.checked );
            },

            get_country: function(type) {
                if (!hygglig_ajax_checkout.safe_inputs.length) return 0;
                var i = 0;
                var f;
                while (hygglig_ajax_checkout.safe_inputs[i] && (f = hygglig_ajax_checkout.safe_inputs[i++]))
                    if( type + '_country' === f.id ) return f.value;
                return 0;
            },

            get_extra_shipping_enabled: function(){
                if (!hygglig_ajax_checkout.safe_inputs.length) return 0;
                var i = 0;
                var f;
                while (hygglig_ajax_checkout.safe_inputs[i] && (f = hygglig_ajax_checkout.safe_inputs[i++]))
                    if( 'ship-to-different-address-checkbox' === f.id ) return f.checked;
                return 0;
            }
        };

    /**
     * Public functions
     */
    return {

        /**
         * Initialize
         */
        init : function(){

            $(document).ready(function() {

                //Get blocking layer
                blocker = $('#hygglig_block').html();

                //Assign events on load
                call_assign_events();

                //Store initial quantities
                $('.woocommerce-mini-cart input[name=quantity]').each(function (e) {
                    cart_total_quantity += Number($(this).val());
                });

                //Assign folding actions for mobile view
                $('.cart-order-total, .shipping-methods-folder').on('click', function () {
                    $(this).toggleClass('opened', '');
                });

                //Check if there is no any ajax running and unblock the cart
                $(document).ajaxStop(function () {
                    unblockCheckout(1);
                    addInputEvents(0);
                    //Block checkout on purchase
                    $('input#place_order').off().on('click', function (e) {
                        blocked_on_purchase = true;
                        blockCheckout();
                    });
                });

                //Unblock checkout if errors found on purchase
                $('form[name=checkout]').on('validate', function () {
                    if (!blocked_on_purchase) return;
                    $('.hygglig-checkout-cart-wrapper').unblock();
                });

                //Run first time page loads
                addInputEvents(300);

                update_woo_select2();

                var unblocker;

                window.addEventListener('message', function(event) {
                    clearTimeout(unblocker);
                    console.log(event);
                    if (event.data == "hyggligSubmitEvent") {
                        console.log('hyggligSubmitEvent');
                        blockCheckout();
                    } else if (event.data == "hyggligCancelSubmitEvent") {
                        console.log('hyggligCancelSubmitEvent');
                        unblockCheckout(1);
                        return;
                    } else {
                        console.log('iFrame event');
                    }
                    unblocker = window.setTimeout(function () {unblockCheckout(1)}, 5000);
                });

            });

        }

    }

})(jQuery);

/**
 * Initialize checkout JS
 */
hygglig_update.init();