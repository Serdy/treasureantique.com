=== WP e-Commerce - Store Exporter ===

Contributors: visser
Donate link: http://www.visser.com.au/#donations
Tags: e-commerce, wp e-commerce, shop, cart, ecommerce, export, csv, xml, customers, products, sales, coupons
Requires at least: 2.9.2
Tested up to: 3.9.1
Stable tag: 1.6.2

== Description ==

Export store details out of WP e-Commerce into simple formatted files (e.g. CSV, XML, TXT, etc.).

Features include:

* Export Products (*)
* Export Products by Product Category
* Export Products by Product Status
* Export Categories
* Export Tags
* Export Orders (**)
* Export Orders by Order Status (**)
* Export Orders by Order Date (**)
* Export Orders by Product (**)
* Export Orders by Customers (**)
* Export Customers (**)
* Export Coupons (**)
* Toggle and save export fields
* Works with WordPress Multisite
* Export to CSV file
* Export to XML file (**)
* Supports external CRON commands (**)
* Supports scheduled exports (**)

(*) Compatible with Product Importer Deluxe, All in One SEO Pack, Ultimate SEO, WordPress SEO by Yoast, Advanced Google Product Feed, Custom Fields, Related Products, Simple Product Options and more.
(**) Requries the Pro upgrade to enable additional store export functionality.

For more information visit: http://www.visser.com.au/wp-ecommerce/

== Installation ==

1. Upload the folder 'wp-e-commerce-exporter' to the '/wp-content/plugins/' directory
2. Activate 'WP e-Commerce - Store Exporter' through the 'Plugins' menu in WordPress

See Usage section before for instructions on how to generate export files.

== Usage ==

1. Open Products > Store Export from the WordPress Administration
2. Select the Export tab on the Store Exporter screen
3. Select which data type and WP e-Commerce details you would like to export
4. Click Export
5. Download archived copies of previous exports from the Archives tab

Done!

== Support ==

If you have any problems, questions or suggestions please join the members discussion on our WP e-Commerce dedicated forum.

http://www.visser.com.au/wp-ecommerce/forums/

== Screenshots ==

1. The overview screen for Store Exporter.
2. Select the data fields to be included in the export, selections are remembered for next export.
3. Each dataset (e.g. Products, Orders, etc.) include filter options to filter by date, status, type, customer and more.
4. A range of export options can be adjusted to suit different languages and file formatting requirements.
5. Export a list of WP e-Commerce Product Categories into a CSV file.
6. Export a list of WP e-Commerce Product Tags into a CSV file.
7. Download achived copies of previous exports.

== Changelog ==

= 1.6.2 =
* Fixed: Coupon export as XML
* Fixed: Order export as XML
* Fixed: Customer export as XML
* Fixed: Compatibility with WordPress 3.9.1
* Added: Product export support for Advanced Google Product Feed
* Added: Product export support for All in One SEO Pack
* Added: Product export support for WordPress SEO
* Added: Product export support for Ultimate SEO
* Fixed: Fatal error affecting CRON export for XML export
* Added: Filter export Orders by Product

= 1.6.1 =
* Fixed: Clearing the Limit Volume or Offset values would not be saved
* Fixed: Force file extension if removed from the Filename option on Settings screen
* Changed: Reduced memory load by storing $args in $export global

= 1.6 =
* Fixed: Fatal error if Store Exporter is not activated

= 1.5.9 =
* Changed: Replaced wpsc_ce_save_csv_file_attachment() with generic wpsc_ce_save_file_attachment()
* Changed: Replaced wpsc_ce_save_csv_file_guid() with generic wpsc_ce_save_file_guid()
* Changed: Replaced wpsc_ce_save_csv_file_details() with generic wpsc_ce_save_file_details()
* Changed: Replaced wpsc_ce_update_csv_file_detail() with generic wpsc_ce_update_file_detail()
* Changed: Moved wpsc_ce_save_file_details() into common Plugin space
* Changed: Added third allow_empty property to custom get_option()

= 1.5.8 =
* Added: Disabled support for XML Export Format under Export Option
* Changed: Created new functions-csv.php file
* Changed: Moved wpsc_ce_generate_csv_filename() to functions-csv.php
* Changed: Moved wpsc_ce_generate_csv_header() to functions-csv.php
* Added: General Settings header to Settings screen
* Added: CSV Settings header to Settings screen
* Changed: Re-ordered field options on Settings screen

= 1.5.7 =
* Fixed: Export error prompt displaying due to WordPress transient

= 1.5.6 =
* Changed: Using WP_Query instead of get_posts for bulk export
* Changed: Moved export function into common space for CRON and scheduled exports
* Added: Toggle visibility of each export types fields within Export Options

= 1.5.5 =
* Changed: Improved support for legacy WP e-Commerce 3.7

= 1.5.4 =
* Changed: Dropped $wpsc_ce global
* Added: Using Plugin constants
* Changed: Moved debug log to WordPress transient
* Added: Support for Table Rate Price on Product export

= 1.5.3 =
* Fixed: Multi-site support resolved
* Changed: Permanently delete failed exports
* Added: Post Published and Post Modified

= 1.5.2 =
* Added: Date format to Export options

= 1.5.1 =
* Added: Detection of non-WP e-Commerce installs with notices
* Fixed: Language support for translations
* Changed: Moved wpsc_ce_count_object() to formatting.php
* Fixed: File encoding for all export fields
* Added: Separate files for each dataset

= 1.5 =
* Added: Simple Product Options integration
* Changed: Order Items field names
* Changed: Checkout field names
* Added: Order Items: Product SKU
* Added: Product ID to Products export
* Added: Parent ID to Products export
* Added: Parent SKU to Products export
* Added: Parent Term ID to Categories export

= 1.4.9 =
* Added: User ID to Customers export
* Added: Username to Customers export
* Changed: Backend names for Customer export fields
* Added: jQuery Chosen support to Orders Customer dropdown

= 1.4.8 =
* Added: Category structure export
* Added: Native jQuery UI support
* Fixed: Various small bugs

= 1.4.7 =
* Fixed: Customers export dataset
* Fixed: Custom Fields integration for Products
* Added: Filter Products by Product Category
* Added: Trash Product Status to Product Filters
* Changed: Cleaned up Export options short list
* Added: Past Exports support
* Added: Integration to WordPress Media

= 1.4.6 =
* Fixed: Styling in WP e-Commerce 3.7
* Fixed: Permission issue in WP e-Commerce 3.7
* Fixed: Export link within Plugins screen
* Added: Product Tax Bands support

= 1.4.5 =
* Fixed: Export buttons not adjusting Export Dataset
* Added: Support for Length column
* Added: Product Variation support
* Added: Taxable Amount Product detail
* Fixed: Local Shipping and International Shipping Product details
* Added: No Shipping Product detail
* Added: Notify OOS and Unpublish OOS Product details

= 1.4.4 =
* Added: Partial export support
* Added: Select All support to Export screen
* Changed: References of Sales to Orders

= 1.4.3 =
* Fixed: Image column contents
* Added: Featured image support for Image column

= 1.4.2 =
* Changed: Removed HTML filter
* Changed: Introduced new encoder
* Changed: Moved formatting functions to formatting.php

= 1.4.1 =
* Fixed: Coupons export
* Added: Selectable Coupon fields

= 1.4 =
* Added: Customers support
* Added: Integration with Exporter Deluxe

= 1.3.9 =
* Changed: Moved styles to admin_enqueue_scripts
* Changed: Categories now using wp_terms
* Changed: Options engine

= 1.3.8 =
* Fixed: Export of Tags
* Fixed: Template header bug
* Added: Sales support for Checkout data
* Changed: Filter Heading from Checkout data
* Fixed: Coupons support

= 1.3.7 =
* Added: Tabbed viewing on the Exporter screen
* Fixed: Tag generation error
* Added: External Link Text
* Added: External Link Target
* Added: Related Products
* Added: Export Sales
* Added: Product columns
* Added: Sales columns

= 1.3.6 =
* Fixed: Category column adding surplus Root and Parent category

= 1.3.5 =
* Changed: Migrated to WordPress Extend

= 1.3.4 =
* Changed: More efficient Tag generation

= 1.3.3 =
* Added: Category heirachy support (up to 3 levels deep)
* Fixed: Foreign character support
* Changed: Removed HTML converter in Description and Additional Description

= 1.3.2 =
* Fixed: Product export issue
* Added: Custom Fields integration

= 1.3.1 =
* Added: Advanced Google Product Feed integration
* Added: All in One SEO Pack integration

= 1.3 =
* Added: Export Coupon details

= 1.2 =
* Fixed: WP e-Commerce Plugins widget markup
* Added: Support special characters
* Fixed: progress.gif URL
* Added: Permalink column

= 1.1 =
* Fixed: Styling issue within Plugins Dashboard widget
* Fixed: Issue introduced with wpsc_get_action()
* Added: Alt. switch to wpsc_get_action()

= 1.0 =
* Added: First working release of the Plugin

== Disclaimer ==

It is not responsible for any harm or wrong doing this Plugin may cause. Users are fully responsible for their own use. This Plugin is to be used WITHOUT warranty.