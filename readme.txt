=== Rezgo Import Contacts into CRM ===
Contributors: schwarzhund
Donate link: http://alexvp.elance.com
Tags:  tour operator software, tour booking system, activity booking software, tours, activities, events, attractions, booking, reservation, ticketing, e-commerce, business, rezgo, emails, notifications, web hook, api, mailchimp, zohocrm, icontact, constantcontact
Requires at least: 3.0.0
Tested up to: 3.6
Stable tag: 0.1

Automatically add your Rezgo customers into a CRM after they make a booking through your Rezgo online booking engine.

== Description ==

> This plugin is completely free to use, but it requires a Rezgo account.  <a href="http://www.rezgo.com">Sign-up with Rezgo today</a> and experience the leading tour operator software.

**What is Rezgo?** Rezgo is an online booking engine for tour and activity operators that helps manage inventory, accept reservations, and process credit card payments. This plugin allows you to automatically add your customers to a CRM after they make a booking through your Rezgo online booking website.


The plugin connects to your Rezgo account using the Rezgo Webhook and API and, based on your settings, will add the main billing contact to your selected CRM or email marketing software.

= Plugin features include =

* Connect to your Rezgo API
* Select one or more CRMs for import
* Create a custom mapping of fields from your Rezgo account to your CRM
* Automated import using the Rezgo Webhook

= Support for this plugin =

This plugin was developed by AlexVP.  There is no support provided for this plugin.  It is available as-is with no guarantees.  If you would like the plugin customized or modified for your needs, please feel free to send a proposal or hire Alex through Elance.

[http://alexvp.elance.com](http://alexvp.elance.com)

== Installation ==

= Install the Custom Reminder plugin =

1. Install the Rezgo Import Contacts plugin in your WordPress admin by going
to 'Plugins / Add New' and searching for 'Rezgo' **OR** upload the
'wp-rezgo-to-crm’ folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress

= Plugin Configuration and Settings =

In order to use the , your Rezgo account must be activated.  This means that you **must** have a valid credit card on file with Rezgo before your plugin can connect to your Rezgo account.

1. Make sure the Rezgo Import Contacts plugin is activated in WordPress.
2. Add your Rezgo account CID and API KEY in the plugin settings and click the 'Save Changes' button.
3. Select your CRM from the menu provided.
4. In the field mapping table, add your CRM specific fields in the space next to the corresponding Rezgo field.
5. Save your changes.
6. You can select additional CRMs if you like.
7. Add the webhook URL to your Rezgo notifications.
10. To test your importer, create a new front-end booking on your Rezgo account. The new customer booking data will be pushed into your CRM via the webhook.

== Frequently Asked Questions ==

= Can I contact Rezgo for support for this plugin? =

No. Rezgo did not create this plugin and does not support it.

= I added a booking but no notification was sent, what should I do?  =

Check the Bookings and the Log in the plugin to see what error was received.  Check to make sure that your Webhook notification is set-up correctly in Rezgo.  Read this article for information on [how to create a web hook notification](http://j.mp/14CDNh7).

= Does this work for back-office or point of sale bookings? =

No, the importer only works for bookings made through the customer facing booking engine.  Back-office bookings do not trigger the webhook at this time.

= I want the plugin to do something that it doesn't do now, who should I contact? =

You can contact Alex at Elance : [http://alexvp.elance.com](http://alexvp.elance.com)

Please note, there is NO FREE SUPPORT for this plugin.  Any changes or modifications will be charged.

== Screenshots ==

1. Once you activate the Rezgo Import Contacts plugin, you will need to enter 
in your Rezgo API credentials on the settings page located in your 
WordPress Admin.  Look for Rezgo to CRM in the sidebar.
2. Select a CRM.
3. Customize the field mapping.

== Changelog ==

= 1.0 =
* Initial release.

== Upgrade Notice ==

= You have the most recent version =