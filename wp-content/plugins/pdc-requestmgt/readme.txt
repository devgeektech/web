=== PdC Request Management ===
Contributors: Philippe de Chabot
Tested up to: 4.3.1
Requires at least: 4.2.3
Stable tag: 0.9.9

Comprehensive management system for Setlr

== Description ==

Manages the creation of translation requests, translations and status history

Developed exclusively for Setlr.


== Frequently Asked Questions ==

= List of available shortcodes =

pdcrequest_list type=recent | top number=-1 | xx outputs a list of translation requests (ordered by top/recent, unlimited or limited to xx requests)
pdcrequest_list_applications type=own | recent | top, number=-1 | xx outputs a list of the translations (filtered by own, recent, top, unlimited or limited to xx translations) 
pdcrequest_all_lang_pairs list all language pairs Setlr can do with current translators
pdcrequest_own_list outputs a list of request made by the customer

== Screenshots ==
Soon

== Changelog ==

= 0.9.9.7 =
* Fixed payment information after successful payment

= 0.9.9.6 =
* Added setlr-profile-form
* Fixed [pdcrequest_form] shortcode to work with logged in and non-logged in customers
* Disabled shortcode [pdcrequest_new_request_profile] 

= 0.9.9.5 =
* Fixed javascript bug on add-a-new-project 

= 0.9.9.4 =
* Added shortcode [pdcrequest_new_request_profile] 

= 0.9.9.3 =
* Fixed languages on profile

= 0.9.9.2 =
* Fixed question project
* Added languages and country on profile

= 0.9.9.1 =
* Fixed update project

= 0.9.9 =
* Added table for Braintree payments feedback
* Added table for Braintree settlements feedback
* Added extra columns to requests admin page for payment status
* Added payment status to customer own project list
* Added payment status to helper my history project list
* Updated helper profile to ask for PayPal account id or email
* Fixed locale select not showing in admin


= 0.9.8.3 =
* Fixed locale select not showing in dashboard

= 0.9.8.2 =
* Fixed payment status not showing in dashboard

= 0.9.8.1 =
* Modified enqueueing of setlr_locale to avoid problem if form new request on different url

= 0.9.8 =
* Added settings page for payment
* added admin notice for payment system in sandbox mode

= 0.9.7 =
* Added payment status
* fixed bug that made available unpaid projects to helpers


= 0.9.6 =
* Replaced Stripe by BrainTree as Credit Card processor
* Added project quote
* Added payment processor
* Added new table for payment info
* Fixed some redirections
* Added some styles for custom templates

= 0.9.5 =
* Modified classes to follow new guidelines (prefixed with "Setlr_" followed by capitalized first letter class name)
* Added user short profile in request and translation tables (my-dashboard)

= 0.9.4 =
* Added language locale for customers
* Rationalized functions

= 0.9.3 =
* Added language locale for translators

= 0.9.2 =
* Fixed disapearing title
* Tweaked dashboard notifications
* Added word count on request form (version 1 does not take lang into consideration)
* Fixed preservation of styles on original post
* Added action for customers on dashboard

= 0.9.1 =
* Hidden admin bar for non administrators
* Minor code/ code documenation tweaks
* Added readme.txt file
* Added requests' word count for translators
* Updated translation status
* Updated request status
