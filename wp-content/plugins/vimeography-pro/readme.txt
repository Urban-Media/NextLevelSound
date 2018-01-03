=== Vimeography Pro ===
Contributors: iamdavekiss
Tags: vimeo, video, videos, gallery, vimeography, media, player, playlist, showcase, skins, themes, video gallery
Requires at least: 3.8
Tested up to: 4.9
Stable tag: 1.1.2
Author: Dave Kiss
Author URL: http://davekiss.com
Version: 1.1.2

The easiest way to create beautiful Vimeo galleries on your WordPress site.

== Copyright ==
Copyright 2017 Dave Kiss

== Description ==

Vimeography Pro supercharges your Vimeography galleries by adding unlimited videos, sorting, Vimeo Pro support, hidden videos, playlists, downloads and more.

A quick overview:
http://vimeo.com/75977512

For more information, check out [vimeography.com](http://vimeography.com/ "vimeography.com")

Some amazing features:

* Automatically add videos uploaded to a Vimeo user account, channel, album or group
* Easily insert galleries on a page, post or template with the gallery helper or shortcode
* Set a featured video to appear as the first video in your gallery
* Change your gallery's appearance with custom themes
* Tweak your theme's look with the appearance editor
* Control the gallery width using pixels or percentages
* Built-in caching for quick page loads
* Create unlimited galleries

Make your gallery stand out with our custom themes!
[http://vimeography.com/themes](http://vimeography.com/themes "vimeography.com/themes")

For the latest updates, follow us!
[http://twitter.com/vimeography](http://twitter.com/vimeography "twitter.com/vimeography")

== Installation ==

1. Upload `vimeography-pro.zip` to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==
= Help! My theme doesn't look right! =

Okay, deep breath. More than likely, it is another plugin causing this issue. See if you can pinpoint which one by disabling your plugins, one by one, and really determining if you need it. If that task sounds daunting, try disabling plugins that are used for photo galleries, minifying scripts, widgets, or otherwise alter your blog's appearance.

= Why don’t you support YouTub/MetaHall/Flacker/PreschoolHumor? =

Like many other video professionals, we believe that Vimeo is a beautiful website complete with clean design, a supportive community and a straightforward API. This makes Vimeo a great choice for professional looking portfolios. Yes, there are other crummier sites that may also do the job, but that’s like forcing down chicken nuggets for dinner when you could be having baked scallops and a caprese appetizer. Vimeo only; enough said.

= How do I add my Vimeography gallery to a post or page? =

Easy! All you have to do is type `[vimeography id="#"]`, where `#` is replaced by the ID number of your gallery.

= Where do I find the ID number of my Vimeography gallery? =

Each gallery’s ID number is located next to the gallery’s title in the first column on the edit galleries page.

= Can I override my Vimeography gallery settings in the shortcode? =

Sure thing! You can define all of the properties found in the admin panel right in your shortcode as well. Try using one, any, or all of the following parameters:
`[vimeography id="3" theme="thumbs" featured="http://vimeo.com/28380190" source="http://vimeo.com/channels/staffpicks" limit="60" cache="3600" width="600px"]`

= Can I add the Vimeography gallery to my theme’s sidebar/footer/header etc.? =

Yes, but you’ll need some PHP knowledge to do it! Open the file you want to add the gallery to, and type `<?php do_shortcode('[vimeography id="#"]'); ?>`, where `#` is replaced by the ID number of your gallery.

= Can I change the look of my Vimeography theme? =

Heck yeah! Use the appearance editor to change your theme's style so that it matches your site perfectly.

== Screenshots ==

1. Create a gallery in 30 seconds, tops!
2. Preview your gallery and customize its appearance.
3. Manage your galleries with a simple interface.
4. Get new styles by installing gallery themes.

== Changelog ==
= 1.1.2 =
* [Fix] Make sure download links display as expected with Vimeography 1.5.3

= 1.1.1 =
* [New] Send along a user agent with requests to Vimeo
* [Fix] Ensures Vimeography Pro's database structure is set up correctly.

= 1.1 =
* [New] You can now export and import your galleries, making it super easy to make backups or move them between sites.

= 1.0.1 =
* [New] Added `vimeography-pro/edit-access-token` filter to allow app token to be swapped
* [Fix] Updated to support WordPress multisite installations

= 1.0 =
* [New] Added /tags as a valid endpoint - create a gallery based off of a Vimeo tag
* [New] Added a "reverse order" setting in the gallery editor to flip your video sort order
* [New] Compatible with Vimeography 1.3's new gallery appearance editor
* [New] Added Pro's gallery parameters to the new Vimeography theme JS global
* [Fix] Ensure that a video cache exists before attempting to delete it
* [Fix] Make sure to check for Vimeography Pro's db_version using get_site_option
* [Tweak] Update localization files
* [Tweak] Refactor the code for better organization and formatting
* [Tweak] Add a link to the Vimeography Pro page in the setup message

= 0.9 =
* [New] Allow video downloads for Vimeo Pro members
* Not a Vimeo Pro member? You're missing out. Learn more at http://vimeography.com/vimeo-pro

= 0.8 =
* Added support for Playlists [woop woop!]
* Fixed an issue where the featured video description would sometimes get processed twice
* Added an error message if pro is installed without the basic version of Vimeography

= 0.7.0.2 =
* Fixed an issue where galleries did not respect max-width settings

= 0.7.0.1 =
* Added a fix for users running PHP <= 5.2, which doesn't support namespaces

= 0.7 =
* Updated compatibility with Vimeography 1.2
* Fixed a conflict with the WordPress Heartbeat API during paging requests
* Added a reminder on the plugins to add activation key if not yet added
* Fixed an issue on network installs during paging requests
* Namespaced the Vimeo library to ensure compatibility with other Vimeo plugins

= 0.6.3 =
* Added uninstaller
* Fixed error messages to be more helpful

= 0.6.2 =
* Fixed a bug that caused an error while upgrading Vimeography.

= 0.6.1 =
* Fixed a bug that prevented sort being passed to pagination request.
* Fixed a bug that prevented video limits to work with videos per page.

= 0.6 =
* Initial release

== Upgrade Notice ==

= 0.6 =
This is the first public beta release of Vimeography Pro.