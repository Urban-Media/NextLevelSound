# WP Discourse Widgets

Discourse Widgets plugin for WordPress

### Features

* **Topics Widget**
 * Custom widget title.
 * Control how many topics should display.
 * Filter topics by
   Latest - topics with recent posts
   New - topics created in the last few days
   Top - most active topics

### Installation

<!---
#### From your WordPress dashboard

1. Visit 'Plugins > Add New'
2. Search for 'WP Discourse Widgets'
3. Activate WP Discourse Widgets from your Plugins page
-->

#### By uploading manually

1. Download WP Discourse Widgets
2. Upload the 'wp-discourse-widgets' directory to your '/wp-content/plugins/' directory (also you can upload via WordPress dashboard `/wp-admin/plugin-install.php?tab=upload`).
3. Activate WP Discourse Widgets from your Plugins page

#### With Composer

If you're using Composer to manage WordPress, add WP-Discourse-Widgets to your project's dependencies. Run:

```sh
composer require vinkas/wp-discourse-widgets 1.0.0
```

Or manually add it to your `composer.json`:

```json
"require": {
  "php": ">=5.3.0",
  "wordpress": "4.4.0",
  "vinkas/wp-discourse-widgets": "1.0.0"
}
```
