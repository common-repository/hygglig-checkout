=== Hygglig Checkout ===


Contributors: bilalr, aliimranahmad, Northworks Technologies Ltd.

Tags: Hygglig, marg, Betalningar,Betalning, Checkout, Payment gateway, Payment-gateway, Betall�sning, Kort, Faktura, Bank, marginalen

Requires at least: 3.8


Tested up to: 6.1.3

Stable tag: 3.8

License: GPLv2 or later


Hygglig checkout makes it easy for you to accept payments from your Swedish customers.



== Description ==


The Hygglig Gateway module is a plugin that extends WooCommerce.

It allows you as a merchant to have only one payment integration,

and your customers to pay with the payment method of their choice.


Hygglig Checkout is at the moment only available in Sweden.

Hygglig checkout requires a WooCommerce installation version 2.5 or above.



Major features in Hygglig Checkout include:



* Easy admin interface in backend

* When you send an order. It's activated in Hygglig  = Invoice sent to your customer

* When you cancel an order. It's canceled in Hygglig = Credited in Hygglig

* Display the order review above or below the checkout - or simply hide it.

* Ajax update of shipping method from checkout page.

* Button to go to your other checkout options



PS: You'll need a [Hygglig Merchant ID & Secret Test keys](mailto:support@hygglig.com) to test it.

When you're ready for production let us know and we'll send you an agreement to sign.



[Step by step installation guide at Hygglig.com](https://www.hygglig.com/)



== Installation ==



1. Activate the plugin through the "Plugins" menu in WordPress Administration.

1. Go to  WooCommerce -> Settings -> Checkout -> Hygglig Checkout, and configure your Hygglig settings.

1. Hygglig checkout is enabled when the Enable/Disable checkbox is checked. This also makes Hygglig Checkout the default checkout page (stated in Custom Checkout Page field).

1. Enter Merchant id - Sweden and Secret - Sweden for Hygglig. (If it is the keys for Hygglig Test Environment, you need to check the Test Mode checkbox.)

1. Enter the full URL to Hygglig checkout page in the settings field Custom Checkout Page - Sweden. This page must contain the shortcode [woocommerce_hygglig_checkout].

1. If you use the existing checkout page for Hygglig, make sure you replace the existing shortcode in the checkout page and instead use the shortcode [woocommerce_hygglig_checkout].

1. Enter the full URL to your Thank You page in the settings field Custom Thanks Page - Sweden. Make sure to use the shortcode [woocommerce_hygglig_checkout].

1. Check Auto Send Order and Auto Cancel Order boxes if you want WooCommerce to send order status changes automatically to Hygglig Merchant Web.

1. Label for Standard Checkout Button: Enter the text for the link that links to the Standard Checkout page from the Hygglig Checkout page.

1. A Terms page must be set. If the Terms Page field is left empty, the Terms and Conditions page defined in  WooCommerce Settings  Checkout will be used.

1. Create customer account should be checked if you want Wordpress to save new customer information entered in Hygglig Checkout.

1. With both Create customer account and Send New account email checked, WooCommerce will send a New account email to the customer. If you want to edit that email this can be done under  WooCommerce -> Settings -> Emails -> New account, and click button View template.

1. Show or hide the order review at the checkout page.

1. Test Mode. Check this box to enable Hygglig Test Mode. This will only work if you have a Hygglig Checkout test account.

1. Configure you page using the following shortcodes:



* [woocommerce_hygglig_checkout] = The checkout iframe
* [woocommerce_hygglig_cart] = Cart (Optional)


After you have saved the Hygglig Checkout settings, you can start making test purchases (using the test credentials obtained from Hygglig).



== Changelog ==

=3.8=

*Release Date -2023-09-04
*Author Northworks Technologies Ltd.
-Fix the checkout load issue, some themese were adding extra html chars to template page when shortcodes are presents, fixed those.

=3.7=

*Release Date -2023-08-30
*Author Northworks Technologies Ltd.
-Fix the fatal error issue on plugin activation for custom urls

=3.6=

*Release Date -2023-04-26
*Author Northworks Technologies Ltd.
-Updates to fix auto-complete order issues

=3.5=

*Release Date -2022-02-27
*Author Northworks Technologies Ltd.
-Update to work with new REST Hygglig API
-New Checkout Integration API implemented
-New Order Management API Implemented
-Admin Updates

=3.4=

*Release Date -2021-02-27
*Author Northworks Technologies Ltd.
-Checkout load / White checkout error fix on custom themes

=3.3=

*Release Date -2021-02-02
*Author Northworks Technologies Ltd.


-Email notifications in order
 - New order
 - Cancel order
 - Faild order
 - Order -on hold
 - Processing order
 - Completed order
 - Refunded order

Above notifications have been implmented in module so if they are active in wooCommerce then module will trigger those notifications.

=3.2=

*Release Date -2020-12-08

Health check tool issue on LiteSpeed webserver (releated with cRUL and session variables) problem on Rest-API 
-
=3.1=

* Release Date - 2020-11-26

Admin update error and update link issue

=3.0=

* Release Date - 2020-10-19

*	Divide woocommerce_hygglig_checkout short code into following two short codes 
  *	  woocommerce_hygglig_cart 
  * 	woocommerce_hygglig_checkout 
*	Fix quantity issue in checkout page. 
*	Fix shipping issue in checkout page
*	Make able to add comment field in checkout page.
*	Make able to add other payments method in checkout page.
*	Fix buyer information issue in push notification. 
*	Make working order activating with long URL of web shop


= 2.1.0 =



* Release Date - 2018-05-20



* New checkout design

* Cart added to checkout

* Removed shortcodes "woocommerce_hygglig_review", "woocommerce_hygglig_checkout_order_note", "woocommerce_hygglig_checkout_discount_field", "woocommerce_hygglig_checkout_other_payments",

These can now be configured in settings.

* Log implemented



= 2.0.1 =



* Release Date - 2017-04-20



* Removed use of legacy functions for Woo 3.0



= 2.0.0 =



* Release Date - 2017-04-20



* Removed use of legacy functions for Woo 3.0



= 1.1.7 =



* Release Date - 2017-04-18



* Restructured Hygglig class and move stuff from gateway to class

* Changed how tax is added to Hygglig order object



= 1.1.6 =



* Release Date - 2017-04-05



* Added support for Woocommerce 3.0



= 1.1.5 =



* Release Date - 2017-04-04



* Added "Press twice to delete" when auto cancel is set in Hygglig



= 1.1.4 =



* Release Date - 2017-04-03



* Added support for article names longer than 100 char

* Added https support for sandbox



= 1.1.2 =



* Release Date - 2016-09-09



* Changed Success page behaviour to be more like WC standard

* Added shortcode woocommerce_hygglig_checkout_payment_options to display payment methods





= 1.1.1 =



* Release Date - 2016-08-16



* Bug fixes for failed card payments



= 1.1.0 =



* Release Date - 2016-08-08



* Rounding only shows if there's actually something to round.





= 1.0.9 =



* Release Date - 2016-08-01



* Major update - Checkout page made modular

* Added shortcodes to enable styling of checkout page

* [woocommerce_hygglig_checkout] = The checkout iframe

* [woocommerce_hygglig_review] = Display a review @ checkout page

* [woocommerce_hygglig_checkout_order_note] = allow your customers to add notes to their orders

* [woocommerce_hygglig_checkout_discount_field] = Coupon input @ checkout page

* [woocommerce_hygglig_checkout_other_payments] = place your "other payment options - button" where ever you like on the checkout page.



= 1.0.8 =



* Release Date - 2016-07-29



* Small fix





= 1.0.7 =



* Release Date - 2016-07-28



* Updated test with to 4.5.3

* Changed JSON decoding



= 1.0.6 =



* Release Date - 2016-07-27



* Optimization of Checkout page

* Added security to push notification



= 1.0.5 =



* Release Date - 21 July 2016



* If order is deleted - it's set to "makulerad" in Hygglig backoffice



= 1.0.4 =



* Release Date - 19 July 2016



* Added support for stores with multi currency





= 1.0.3 =



* Release Date - 18 July 2016



* Improved new customer creation



= 1.0.2 =



* Release Date - 13 July 2016



* Fixed title for Hygglig in admin section

* Added Hygglig payment method to "other payment" listing.



= 1.0.1 =



* Release Date - 7 July 2016



* Minor fix for M-order link when sending orders

* Support for shipping zone functionality in WC 2.6 OK



= 1.0.0 =



* Release Date - 7 July 2016



* First public release on Wordpress



== Upgrade Notice ==



= 1.1.0 =

If you missed it. Checkout page is now modular. You need to add the Review shortcode [woocommerce_hygglig_review] do display at checkout page!



= 1.0.9 =

Checkout page improved. Please read installation guide for new functions!

 
