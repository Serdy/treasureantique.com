=== Plugin Name ===
Contributors: leewillis77
Donate link: http://www.leewillis.co.uk/wordpress-plugins/?utm_source=wordpress&utm_medium=www&utm_campaign=wp-e-commerce-xml-sitemap
Tags: e-commerce, sitemaps
Requires at least: 2.8
Tested up to: 3.0.4
Stable tag: 1.1.5

Add XML sitemap entries for WP E-Commerce categories and products

== Description ==

This plugin automatically adds products and categories from the WP E-Commerce plugin to your XML sitemap to help search engines discover your shop.

== Installation ==

*You Must* already have the following plugins installed:

1. [WP e-Commerce](http://wordpress.org/extend/plugins/wp-e-commerce/)
2. [Google XML Sitemaps](http://wordpress.org/extend/plugins/google-sitemap-generator/) - Version 3.2 or above
3. Upload the plugin to the `/wp-content/plugins/` directory
4. Activate the plugin through the 'Plugins' menu in WordPress
5. Regenerate your sitemap

== Frequently Asked Questions ==


== Screenshots ==


== Changelog ==

= 1.1.5 =
* Fix encoding problem with non-permalink (E.g. ?page=22&category_id=22&product_id=22) style URLs

= 1.1.4 =
* Fix compatibility with WP E-Commerce versions prior to 3.7

= 1.1 =
* Better compatibility with WP E-Commerce versions prior to 3.7

= 1.0 =
* Product timestamp could be empty if no product ratings

= 0.9 =
* Product timestamp should go up if a product is "rated". Timestamp will update when sitemap is next updated after a product rating - it doesn't automatically trigger yet.

= 0.8 = 
* Bumped comaptability tag to Wordpress 2.9

= 0.7 = 
* Fix problem with people whose table prefix wasn't wp_ (Thanks to Ben Huson)

= 0.6 =
* No changes from 0.4 - I just messed up pushing it to the Wordpress plugin directory :)

= 0.4 = 
* Only include active products and categories (Thanks to Dan Lawson) 

= 0.3 =
* Support new regeneration hook in Google XML Sitemaps plugin

= 0.2 =
* Initial Release
