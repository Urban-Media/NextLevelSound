LifterLMS Stripe Changelog
==========================

v4.3.2 - 2017-11-06
-------------------

+ Fix: Remount Stripe Card Element after coupon application & removal


v4.3.1 - 2017-10-30
-------------------

+ Fix: Only mount Stripe Card Element on order view page of Student dashboard.


v4.3.0 - 2017-10-30
-------------------

+ Upgrade to Stripe.js v3 and utilize Stripe Elements for checkout.
+ Add validation checks on admin checkout settings screen to help prevent accidental exposure of Secret Key when adding keys in the incorrect fields
+ On admin checkout settings only show live key fields when test mode disabled & only show test mode fields when test mode enabled
+ Ordered API key fields on admin panel to match the order of keys as displayed on Stripe's dashboard.
+ Removed Stripe error message translations. All translations now powered by Stripe Elements locale
+ Fixed undefined variable error occurring when refunds are processed and debug logging is enabled.


v4.2.0 - 2017-07-05
-------------------

+ Enable LifterLMS 3.10 compatibility to allow students and admins to switch the payment method associated with their recurring subscriptions to courses or memberships
+ Fix issue preventing checkout when a saved card is selected due to new card fields not being properly disabled
+ Update Stripe API Version to latest (2017-06-05)
+ Added filter to allow customization of the API version where needed: `llms_stripe_api_version`
+ Fix typo on pot file location (from "l18n" to "i18n")


v4.1.1 - 2017-03-23
-------------------

+ Prevent Stripe token callbacks from running when other gateways are selected for checkout


v4.1.0 - 2017-03-08
-------------------

+ 1-click refunds now available for transactions
+ Load textdomain `lifterlms-stripe`
+ Added pot file located at `l18n/lifterlms-stripe.pot`


v4.0.1 - 2016-12-20
-------------------

##### Bugs

+ Only enqueues Stripe js files if Stripe is actually enabled as a payment gateway to keep checkout from freezing when disabled w/o publishable keys


v4.0.0 - 2016-10-11
-------------------

**Compatible with LifterLMS 3.0 & Incompatible with LifterLMS 2.x**
**Please see [Upgrading to LifterLMS 3.0](https://lifterlms.com/docs/upgrading-lifterlms-3-0/) for more information**

+ Addresses 3.0 changes to order data structure
+ Adds "force ssl" error message


v4.0.0-rc.1 - 2016-09-26
--------------------------

##### Release Candidate ready for Core 3.0.0 release. Removes two unfinished features that will be added in 4.1.0

+ remove non functioning automatic refunds
+ remove Checkout option

##### Bugs

+ Now trims statement descriptor to 22 chars before sending it to Stripe via API


v4.0.0-beta-2 - 2016-09-01
--------------------------

+ adds support for recurring orders with a free trial or a trial discounted to 0 via coupon
+ updated checkout handling via core apis added in 3.0.0-beta.4
+ minified js because I forgot to do that in beta.1
+ updated js enqueue to define llms core js as a dependency


v4.0.0-beta-1 - 2016-08-29
--------------------------

+ Deprecates numerous functions
+ relies on LifterLMS 3.0.0 Gateway APIs & functions
+ incompatible with any 2.x version of LifterLMS core


v3.0.2 - 2016-06-29
-------------------

+ Resolved an issue where recurring payments with a free trial would attempt to charge 0 through the Stripe API and encounter an error. Free trial payment plans now correctly subscriber your customers to a plan with a free trial and no payment is attempted during checkout confirmation.


v3.0.1 - 2016-03-29
-------------------

+ Added the LifterLMS Helper helper class shared by LifterLMS extensions. This adds a few functions that make working with the LifterLMS Helper a bit easier.
+ In combination with LifterLMS Update 2.3.1 resolves an issue where users could not checkout with a 100% off coupon when using Stripe as the payment method.
+ Reorderd the API key boxes on the LifterLMS Stripe Gateway settings screen to reflect the UI on Stripe's account dashboard where you locate your keys.
+ Fixed an undefined variable notice
+ Fixed a CSS issue on FireFox that prevented users from being able to click in certain dropdown areas on the credit card form.


v3.0.0 - 2016-02-11
-------------------

+ Deprecated reliance on the Plugin Update Checker class from the LifterLMS core in order to allow update and activation via the new free LifterLMS Helper plugin.
+ This version requires LifterLMS 1.5.0 at a minimum and will not function with older versions of LifterLMS.
+ Upgrading to v3.0.0 from older versions of LifterLMS Stripe is easy, simply retrieve the update automatically from your WordPress installation. Please Note that after updating to v3.0.0 you will no longer be able to receive automatic updates without installing the free LifterLMS Helper plugin. Version 2.0.0 of LifterLMS (coming soon) will remove update functionality so we urge you to update to Stripe v3.0.0 soon and install the updater plugin to continue receiving automatic updates.


v2.0.0 - 2016-01-28
-------------------

**Completely rewritten for improved performance and stability**

##### Improvements and Enhancements

+ Now entirely Object Oriented codebase
+ Proper error handling and catching for all processing errors and interactions.
+ A new "debug mode" setting has been created to allow for better debugging of processing issues. This setting will output the raw error response of any unsuccessful API calls made to Stripe during payment processing.
+ Improved frontend validation via [Stripe.js](https://stripe.com/docs/stripe.js) helps identify potential typos and missed fields prior to payment processing without a page reload.
+ Prevent users from accidentally double charging themselves by locking the credit card form upon submission.
+ A CSS-only loading animation during payment processing that was a lot of fun and only warrants and changelog item because I enjoyed coding it.
+ User and plan data is now identified as either "live" or "test" data. This prevents test mode id's from being used after switching to live mode (and vice versa).
+ Test transactions are clearly marked as "TEST" when viewing the LifterLMS order inside LifterLMS
+ A testing interface has been added when test mode is enabled to help you prefill the credit card form with a few of the available test mode cards that Stripe provides.
+ New filters to allow customization of error messages, on screen language, and more.
+ Removed the dependency on Stripe's PHP SDK in favor of CURL requests via the WordPress Core function `wp_safe_remote_post`. This results in a significantly smaller and simpler codebase and enables compatibility with servers on older versions of PHP that don't support namespacing.
+ Added support for [Stripe Metadata](https://stripe.com/docs/api/curl#metadata). On Stripe dashboards it is now easier to see the related WordPress post or user ID.
+ On LifterLMS Orders, Stripe Metadata is now clearly visible in the admin panel. Links to the Stripe Customer, Charge, and more are available for all Stripe transactions.


##### Upgrading from previous versions to v2.0.0

+ Almost all data saved by LifterLMS Stripe has been reformatted so previously saved subscriptions and customers will be stored in a new location
+ Because test and live mode identifiers were never stored by previous version of LifterLMS Stripe, we cannot migrate existing IDs during upgrade. However, all plans and customers will be automatically found or created during payment processing so no migration is actually needed anyway!
+ A new database table (`{$wpdb->prefix}lifterlms_stripe_plans`) will be installed to hold all created plans
+ Existing LifterLMS Stripe Tables that were required for previous versions are no longer needed be are being preserved for historical purposes. If you wish to delete these tables, you may do so using the method of your choosing. The deprecated tables are `{$wpdb->prefix}lifterlms_stripe_orders` and `{$wpdb->prefix}lifterlms_stripe_subscription_plans`

##### Deprecated Functions and Classes

**CLASSES**

+ `LLMS_Settings_Integrations_Stripe` was replaced by `LLMS_Stripe_Settings`

**FUNCTIONS**

+ `llmsstripe_locate_template()` is no longer needed
+ `llmsstripe_theme_override()` is no longer needed
+ `llmsstripe_get_template()` is no longer needed
+ `llmsstripe_enqueue_scripts()` was replaced by `LLMS_Stripe_Checkout_Form->enqueue()`
+ `llmsstripe_person_edit_account_url()` is no longer needed
+ `llmsstripe_generate_random_plan_id()` is no longer needed
+ `llmsstripe_save_subscription_plans()` is no longer needed
+ `llms_stripe_locate_payment_plan()` was replaced by `LLMS_Stripe_Plan->locate()`
+ `llms_stripe_create_new_payment_plan()` was replaced by `LLMS_Stripe_Plan->create()`
+ `llms_stripe_get_trial_days()` was replaced by `LLMS_Stripe_Plan->get_trial_days()`
+ `llms_stripe_setup_expired_subscription_check_schedule()` was replaced by `LLMS_Stripe_Crons->schedule()`
+ `cancel_expired_subscriptions()` was replaced by `LLMS_Stripe_Crons->cancel_expired_subscriptions()`
+ `get_public_key()` was replaced by `LLMS_Stripe_Settings->get_publishable_key()`
+ `get_secret_key()` was replaced by `LLMS_Stripe_Settings->get_secret_key()`
+ `llms_get_stripe_subscription_plans_table_name()` is no longer needed


v1.1.2 - 2016-01-12
-------------------

+ Improved error handling during checkout for various card declined scenarios.


v1.1.1 - 2016-01-11
-------------------

+ Fixed an issue which prevented some recurring transactions to process correctly on initial checkout
+ Improved error handling during plugin activation key submission


v1.1.0 - 2015-12-10
-------------------

+ Coupons now discount both single and recurring transactions
+ Tested with WordPress 4.4


v1.0.1 - 2015-09-25
-------------------
+ Bug fix: resolved conflict with Paypal when both gateways are active
+ Bug fix: resolved issue where credit card details were required when coupon was 100% off.


v1.0.0 - 2015-04-29
-------------------

+ Process single payments using credit cards
+ Process recurring payments using credit cards
+ Cancel recurring payments after payments complete (set payment cycle only)
+ Run test transactions
+ Run production transactions
+ Activation and update functionality


v0.0.2 - 2015-04-05
-------------------

+ Stripe charges now recieved
+ Stripe customer ID saved in database under `wp_lifterlms_stripe_orders` table
+ Confirmation of order now works


v0.0.1 - 2015-03-15
-------------------

+ Created plugin main class
