=== Category Thumbnails ===
Contributors: Adrian Preuss
Version: 1.0.6
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=H56DKPMQE49NJ
Tags: category, thumbnail, taxonomy, custom
Requires at least: 3.4.2
Tested up to: 4.7.4
Stable tag: 1.0.6

This Plugin provide functions like post-thumbnails for categories and (own) custom taxonomys. Please visit the Author-URL for Documentation.

== Description ==
This Plugin provide functions like post-thumbnails for categories and (own) custom taxonomys. Please visit the Author-URL for Documentation.

**Functions:**

Please show on my Website, for the Documentation. [category_id] can be empty, or a Category ID.

*   has_category_thumbnail([category_id])
*   the_category_thumbnail([category_id])
*   get_the_category_thumbnail([category_id])
*   get_the_category_data([category_id])

The Plugin is Multilangual. Following languages are available:

*	[EN] English
*	[DE] German

If you translate the Plugin, please see the language directory. I hope you submit your language to me for other users :)

** ToDo **

* Create Hooks/Actions/filters to override it by theme
* Update/Create new API Functions like post_thumbnails methods
            
== Installation ==

Download the Plugin and upload it to your Wordpress site. On the Plugins-Page you must activate the Plugin to use it.
To use the Functions, you must implement the Thumbnail-Functions in your Theme. Please go to my Website for a documentation.

> Problems or Questions?
> Contact us: [support@hovida-design.de](mailto:support@hovida-design.de "support@hovida-design.de")

== Upgrade Notice ==

If an update available, you can update it on your Wordpress administration.

== Frequently Asked Questions ==

= How i can install this Plugin? =

Going into the Wordpress Administration (wp-admin) and search this Plugin. Here you can install the plugin easyest.
Optionally you can download the Plugin as ZIP-Archive on the Wordpress.org Site and upload it to your Wordpress site.

= I need more Plugins from you! =

No Problem! Contacting me, if you need a Plugin :-)

= I have found a Bug! =

Please contact me, i will fix that shortly.

== Change log ==

= 1.0.5 =
"[20.03.2014, Last Update: 23:00 PM] - Adrian Preuss"

* cleanup code
* Adding documentation

= 1.0.4 =
"[17.03.2014, Last Update: 19:28 PM] - Adrian Preuss"

* fixing bug on the_category_thumbnail()

= 1.0.3 =
"[21.12.2013, Last Update: 23:01 PM] - Adrian Preuss"

* Adding new method to get plain data, get_the_category_data([category_id])
* Fixing help URL on Plugins page

= 1.0.2 =
"[30.01.2013, Last Update: 00:18 AM] - Adrian Preuss"

* Fix text in Media-Upload
* (own) custom taxonomys can be use
* Deprecated "thickbox_button" by version 1.0.1
* Add a Preview-Row on the category/terms table
* Fix image attributes for valid HTML
* Add new Screenshots for Plugin Details
* Add new Language (NL)
* Pre-Release for Wordpress 3.6

WARNING: If you update from 1.0.0, the Media upload has Changed! Images will be stored as JSON-Data in the Database and not more as HTML!
If you make an Update or Activate/Deactivate the Plugin, the Database will restored to the default.

= 1.0.1 =
"[27.01.2013, Last Update: 13:25 AM] - Adrian Preuss"

* Update new Media Manager
* Fix update/save problems
* Update der Media-Upload methode

= 1.0.0 =
"[10.01.2013, Last Update: 8:09 PM] - Adrian Preuss"

* Create the Plugin

== Screenshots ==
1. Set the Thumbnail on a new Category
2. The Thubnail if you edit a Category
3. Code-Preview (How i can use it)
4. The Thumbnail-Preview on the Category/Terms Table
5. The new Media-Upload
