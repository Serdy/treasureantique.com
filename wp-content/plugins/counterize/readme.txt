=== Counterize ===
Contributors: GabSoftware, Gabriel Hautclocq
Donate link: http://www.gabsoftware.com/donate/
Tags: hits, visit, counter, traffic, statistics, stats, browser, operating, system, graph, chart, diagram
Requires at least: 3.3.0
Tested up to: 3.5.0
Stable tag: 3.1.5

Counter and statistics plugin for WordPress.

== Description ==

Counterize is a complete counter and statistics plugin with no external library dependency.

Saves timestamps, visited URL, referring URL, IP addresses (1), operating systems
and browser informations into the database, and can display the total hits,
unique hits and other statistics in your WordPress pages.

**Detailed features:**

* Hourly, daily, weekly and monthly traffic stats
* Popular pages and posts stats
* IP addresses stats
* Countries stats
* Referers stats
* Outlinks stats
* Accurate and detailed browsers stats (browser name and versions)
* Accurate and detailed operating system stats (operating system name, versions and platforms)
* Keywords stats
* Powerful history with filters
* Email reports
* Dashboard widget for a quick overview of your blog statistics
* Track real visitors: most bots are excluded from the statistics by default
* Complete API to use the data of Counterize charts as you like and create your own Counterize plugins
* An administration interface using the WordPress Settings API is available, as well as a dashboard with detailed information and statistics.
* The users that are authorized to display the Counterize dashboard can be defined using WordPress capabilities in the settings.
* Counterize can display statistics in your pages and posts - visit [this webpage](http://www.gabsoftware.com/2011/05/28/counterize-demo/) for an example.
* Since version 3.0.22, Counterize can retrieve informations about the country of the visitors. The country detection was made possible thanks to the Software77.net database (http://software77.net/cgi-bin/ip-country/geo-ip.pl), and to the author of this script (http://www.phptutorial.info/iptocountry/the_script.html).

Version 3 of Counterize is willing to support the latest version of WordPress
as much as possible, but this is always a work-in-progress. Do not hesitate to
report any incompatibility!

I am not the initial author of Counterize so I may not be aware of some old bugs.
You can report them to me. But do NOT report Counterize II bugs.

If you want to propose a translation for Counterize, please follow
[this guide](http://www.gabsoftware.com/tips/a-guide-for-wordpress-plugins-translators-gettext-poedit-locale/).

Counterize is based on the Counterize II 2.14.1 plugin by Steffen Forkmann
(<http://www.navision-blog.de/counterize>) and WordPress Browser Detection Plugin
by Iman Nurchyo (<http://priyadi.net/>). Counterize has evolved a lot since the
initial fork, and does not share a lot of common code with the plugins it is based on
anymore.

**(1): Since 3.0.13, by default, Counterize will not store any IP information**,
because this is illegal in some countries (e.g. Germany). For users living in
those countries, Counterize will store only a small hash to distinguish between
different users and to get information about the user count. For other countries,
IP addresses will be recorded. This is just an option in the administration area
of Counterize.

**Notes:**

Counterize is a popular plugin, so I have to be more and more careful with each
updates as the user base is growing day after day. I am currently the only maintainer
of this project, and I do it for free. As such, although I try my best to provide
a plugin of good quality, I am not failure-proof and so cannot guarantee a bug-free
plugin. Please remember it before you send angry ALLCAPS messages to me in case
of problem. If your Counterize data is very important, please make backups before updating.

**Notes to native English speakers:**

I have been told sometimes that I sound arrogant and/or cold in my answers. If
that is your feeling, please accept my apologies. I'm not sounding arrogant by will,
that's  mostly due to the fact that **I am not a native English speaker**,
so it's not an easy thing to use the proper tone in my answers. I also tend to
give the shortest answer without bells and whistles, like I usually do in French.
That can create a feeling of coldness, but keep in mind that it's not my goal and
that I'm happy to assist you. Thank you for your understanding!

Thank you for reading and have nice moments using Counterize:)

Gabriel Hautclocq



== Installation ==

1. Unzip the package and upload the folder **counterize** to your **/wp-content/plugins/** folder.
2. Activate the plugin through the **Plugins** menu in WordPress.
3. Go to **Counterize** on **Options** page, configurate your settings. *Save the settings*.



== Screenshots ==

1. Browser statistics
2. Country statistics
3. IP Address statistics
4. Keywords statistics
5. Operating system statistics
6. Outlink statistics
7. Post/Page statistics
8. Referer statistics
9. Hit counter table
10. Filters in the History page
11. Settings page
12. Toolbar menu



== Migration from Counterize II ==

1. Do some backup in case of problems
2. Deactivate **Counterize II** on the **Plugins** menu in WordPress
3. Unzip the package and upload the folder **counterize** to **/wp-content/plugins/**.
4. Activate the plugin through the **Plugins** menu in WordPress.
5. Go to **Counterize** on **Options** page, configurate your settings. *Save the settings*

Counterize uses the same tables than Counterize II so you won't loose your data.
You can proceed to the migration safely and benefit from the numerous improvements in Counterize.



== Reporting bugs ==

If you encounter a bug, please send us a descriptive report of what happened, with the error messages if any.



== Many thanks to ==

* Steffen Forkmann (author of Counterize II from which this plugin is based from)
* Carsten Becker (German translation, WP 3.x integration)
* Can Aydemir (Turkish translation)
* Helmutt Hoffman (found some bugs and proposed solutions)
* Spela Golob Peterlin (Slovenian translation)
* Emale (Right-to-left compatibility)
* software77.net (IP to country database, http://software77.net/geo-ip/)
* phptutorial.info (PHP version of software77 database, http://www.phptutorial.info/iptocountry/the_script.html)
* Iman Nurchyo (counterize_browsniff.php is based on his WordPress Browser Detection Plugin)
* Daniel from chaosonline.de (for his much appreciated help in the Counterize upgrade script)
* Greg Froese (for his contributions)
* José Delgado (Spanish translation)
* Gérard (HTTPS links fix)
* Subhash and terrah for their fix of the counterize_current_post_hits() function
* Tomáš Losák (CZ translation)
* lisss (Russian translation)
* Hacko (Bulgarian translation)
* Ahrale Shrem (Hebrew translation)
* Vadym Shvachko (Ukrainian translation + non-ASCII characters fix)

== Special thanks to them for their donation ==

* Billy Willoughby
* Nancy Harkey
* Opulentia
* Dr. Houston David Hughes
* Christy Chauvin
* Matt Mikka


== TODO list ==

Legend: [status, priority] Description

* `[wip, medium]` Make a Counterize widget
* `[10%,    low]` Add some sorting options (by label for example)
* `[ 0%, medium]` Get rid of pre-3.0.0 installation scripts
* `[ 0%, medium]` A new installation should have its own installation script (currently upgrading from 1.0.0 to 3.x.x...)
* `[ 0%,    low]` To be able to resize and move charts in the dashboard (but I consider it not urgent)
* `[ 0%,    low]` Using the HighCharts library as an alternative fancier display
* `[ 0%, medium]` Making a standalone version of Counterize. That would allow to port it to other platforms (Drupal, Magento...).
* `[ 0%,   high]` Allow wildcard characters support in IP address exclusion list
* `[ 0%,   high]` Solve time zones problems as many reported it
* `[ 0%,   high]` Reduce the Counterize footprint on database [BIG JOB AS ALL QUERIES MUST BE RE-WRITTEN]
* `[ 0%, medium]` Send daily, weekly or monthly reports by email
* `[ 0%, medium]` Better IP exclusion list handling
* `[ 0%,    low]` Add more function to count unique visitors, in a similar way as hits
* `[ 0%,   high]` Move some Counterize functions into their respective plugins
* `[ 0%, medium]` Add screen size statistics



== Counterize API ==

After you have installed the Counterize plugin, you can see a lot of diagrams on the Counterize Dashboard page (Dashboard/Counterize).

Most likely you'd like to have a counter somewhere on your pages, showing the number of visitors or similar.
Here's an overview of the functions which can be used anywhere in your WordPress blog.

Depending on where you use the Counterize plugins functions in your theme, you may have to add the following line before:

global $counterize_plugins;



= Traffic functions =

* `counterize_getamount()                 `: Returns the total hits seen by Counterize.
* `counterize_getpagesamount()            `: Returns the total pages/posts views.
* `counterize_gethitstoday()              `: Returns the total hits registered today.
* `counterize_get_online_users()          `: Returns the number of currently online users.
* `counterize_getlatest7days()            `: Returns the total amount of hits from the last 7 days.
* `counterize_getuniqueamount()           `: Returns the total unique IP's that's been seen.
* `counterize_getfromcurrentip()          `: Returns the total hits from the IP that's visiting.
* `counterize_getuniquehitstoday()        `: Returns the number of different IP's registered today.
* `counterize_gethitstodayfromcurrentip() `: Returns the daily hits from the IP that's visiting.
* `counterize_current_post_hits()         `: Gets number of hits on current post/page.
* `counterize_return_first_hit()          `: Returns the date of the first registered entry in the database.
* `counterize_get_hits()                  `: Outputs an HTML table with statistics about hits.
* `$counterize_plugins['traffic']->counterize_feed_daily_stats()             `: Gets the daily stats data feed.
* `$counterize_plugins['traffic']->counterize_render_daily_stats()           `: Renders the daily stats data feed.
* `$counterize_plugins['traffic']->counterize_feed_monthly_stats()           `: Gets the monthly stats data feed.
* `$counterize_plugins['traffic']->counterize_render_monthly_stats()         `: Renders the monthly stats data feed.
* `$counterize_plugins['traffic']->counterize_feed_weekly_stats()            `: Gets the weekly stats data feed.
* `$counterize_plugins['traffic']->counterize_render_weekly_stats()          `: Renders the weekly stats data feed.
* `$counterize_plugins['traffic']->counterize_feed_week_progression_stats()  `: Gets the progression between the last current week stats data feed.
* `$counterize_plugins['traffic']->counterize_render_week_progression_stats()`: Renders the progression between the last current week stats data feed.
* `$counterize_plugins['traffic']->counterize_feed_hourly_stats()            `: Gets the hourly stats data feed.
* `$counterize_plugins['traffic']->counterize_render_hourly_stats()          `: Renders the hourly stats data feed.



= Referers functions =

* `counterize_getuniquereferers()                                              `: Returns the amount of unique referers that have been recorded.
* `$counterize_plugins['referers']->counterize_feed_most_seen_referers()       `: Gets the most seen referers data feed.
* `$counterize_plugins['referers']->counterize_render_most_seen_referers()     `: Renders the most seen referers data feed.
* `$counterize_plugins['referers']->counterize_feed_most_seen_referers24hrs()  `: Gets the most seen referers data feed for the last 24 hours.
* `$counterize_plugins['referers']->counterize_render_most_seen_referers24hrs()`: Renders the most seen referers data feed for the last 24 hours.



= Pages and Posts functions =

* `counterize_getuniqueURL()                                                 `: Returns the amount of unique URL's that have been shown.
* `$counterize_plugins['pages']->counterize_feed_most_requested_urls()       `: Gets the most requested URLs data feed.
* `$counterize_plugins['pages']->counterize_render_most_requested_urls()     `: Renders the most requested URLs data feed.
* `$counterize_plugins['pages']->counterize_feed_most_requested_urls24hrs()  `: Gets the most requested URLs data feed for the last 24 hours.
* `$counterize_plugins['pages']->counterize_render_most_requested_urls24hrs()`: Renders the most requested URLs data feed for the last 24 hours.
* `$counterize_plugins['pages']->counterize_feed_most_popular_posts()        `: Gets the most popular posts/pages data feed.
* `$counterize_plugins['pages']->counterize_render_most_popular_posts()      `: Renders the most popular posts/pages data feed.
* `$counterize_plugins['pages']->counterize_feed_most_popular_posts24hrs()   `: Gets sthe most popular posts/pages data feed for the last 24 hours.
* `$counterize_plugins['pages']->counterize_render_most_popular_posts24hrs() `: Renders the most popular posts/pages data feed for the last 24 hours.



= IP addresses functions =

* `$counterize_plugins['ip']->counterize_feed_most_active_ips()       `: Gets the most active IP addresses data feed.
* `$counterize_plugins['ip']->counterize_render_most_active_ips()     `: Renders the most active IP addresses data feed.
* `$counterize_plugins['ip']->counterize_feed_most_active_ips24hrs()  `: Gets the most active IP addresses data feed of the last 24 hours.
* `$counterize_plugins['ip']->counterize_render_most_active_ips24hrs()`: Renders the most active IP addresses data feed of the last 24 hours.



= Keywords functions =

* `$counterize_plugins['keywords']->counterize_feed_most_searched_keywords()       `: Gets the most searched keywords data feed.
* `$counterize_plugins['keywords']->counterize_render_most_searched_keywords()     `: Renders the most searched keywords data feed.
* `$counterize_plugins['keywords']->counterize_feed_most_searched_keywords24hrs()  `: Gets the most searched keywords data feed for the last 24 hours.
* `$counterize_plugins['keywords']->counterize_render_most_searched_keywords24hrs()`: Renders the most searched keywords data feed for the last 24 hours.



= Countries functions =

* `$counterize_plugins['countries']->counterize_feed_most_visiting_countries()       `: Gets the most visiting countries data feed.
* `$counterize_plugins['countries']->counterize_render_most_visiting_countries()     `: Renders the most visiting countries data feed.
* `$counterize_plugins['countries']->counterize_feed_most_visiting_countries24hrs()  `: Gets the most visiting countries data feed for the last 24 hours.
* `$counterize_plugins['countries']->counterize_render_most_visiting_countries24hrs()`: Renders the most visiting countries data feed for the last 24 hours.



= Browsers functions =

* `counterize_getuniquebrowsers()                                                         `: Returns the amount of unique browser strings that have visited your blog.
* `$counterize_plugins['browsers']->counterize_feed_most_used_browsers_collapsible()      `: Gets the most used browsers data feed with detailed version statistics for each item.
* `$counterize_plugins['browsers']->counterize_render_most_used_browsers_collapsible()    `: Renders the most used browsers data feed with detailed version statistics for each item.
* `$counterize_plugins['browsers']->counterize_feed_most_used_browsers_without_version()  `: Gets the most used browsers without version data feed.
* `$counterize_plugins['browsers']->counterize_render_most_used_browsers_without_version()`: Renders the most used browsers without version data feed.
* `$counterize_plugins['browsers']->counterize_feed_most_used_browsers()                  `: Gets the most used browsers data feed.
* `$counterize_plugins['browsers']->counterize_render_most_used_browsers()                `: Renders the most used browsers data feed.



= Operating systems functions =

* `$counterize_plugins['os']->counterize_feed_most_used_os_collapsible()      `: Gets the most used os data feed with detailed version statistics for each item.
* `$counterize_plugins['os']->counterize_render_most_used_os_collapsible()    `: Renders the most used os data feed with detailed version statistics for each item.
* `$counterize_plugins['os']->counterize_feed_most_used_os_without_version()  `: Gets the most used os without version data feed.
* `$counterize_plugins['os']->counterize_render_most_used_os_without_version()`: Renders the most used os without version data feed.
* `$counterize_plugins['os']->counterize_feed_most_used_os()                  `: Gets the most used os data feed.
* `$counterize_plugins['os']->counterize_render_most_used_os()                `: Renders the most used os data feed.



= Actions =

* `counterize_show_data                  `: Let plugins show their data into the "Counterize" sub-menu page.
* `counterize_after_includes             `: Happens right after Counterize included its files.
* `counterize_before_insert_into_database`: Let plugins do something just before some data is inserted into the database.
* `counterize_after_insert_into_database `: Let plugins do something just after some data is inserted into the database.
* `counterize_init                       `: Let plugins add a hook into Counterize initialization procedure.
* `counterize_before_install             `: Let plugins do something just before installation.
* `counterize_after_install              `: Let plugins do something just after installation.

= Filters =
* `counterize_before_includes            `: Let plugins filter which files are included.
* `counterize_server_remote_addr         `: Let plugins filter the `$_SERVER['REMOTE_ADDR']` value.
* `counterize_server_http_user_agent     `: Let plugins filter the `$_SERVER['HTTP_USER_AGENT']` value.
* `counterize_server_request_uri         `: Let plugins filter the `$_SERVER['REQUEST_URI']` value.
* `counterize_server_referer             `: Let plugins filter the `$_SERVER['HTTP_REFERER']` value.
* `counterize_server_this_url            `: Let plugins filter the $this_url variable.
* `counterize_bot_array                  `: Let plugins filter the bot array.
* `counterize_check_insert_into_database `: Let plugins add their own conditions for determinating if data should be inserted into the database.
* `counterize_check_data                 `: Let plugins add their own diagrams.
* `counterize_shortcodes                 `: Let plugins add their own shortcodes.
* `counterize_dashboard_add_submenu      `: Let plugins add their own sub menu.
* `counterize_mce_js_before_form_filter  `: Let plugins output something before the form of the Counterize modal dialog in the Visual editor.
* `counterize_mce_js_before_fields_filter`: Let plugins output something before the fields of the Counterize modal dialog in the Visual editor.
* `counterize_mce_js_type_filter         `: Let plugins add more type items in the Counterize modal dialog in the Visual editor.
* `counterize_mce_js_period_filter       `: Let plugins add more period items the Counterize modal dialog in the Visual editor.
* `counterize_mce_js_after_fields_filter `: Let plugins output something after the fields of the Counterize modal dialog in the Visual editor.
* `counterize_mce_js_after_form_filter   `: Let plugins output something after the form of the Counterize modal dialog in the Visual editor.
* `counterize_mce_js_options_filter      `: Let plugins add/modify attributes and their defaults for the Counterize modal dialog in the Visual editor.
* `counterize_mce_js_radiobutton_filter  `: Let plugins add/modify the list of attributes of type "radio button" for the Counterize modal dialog in the Visual editor.
* `counterize_mce_js_checkbox_filter     `: Let plugins add/modify the list of attributes of type "checkbox" for the Counterize modal dialog in the Visual editor.
* `counterize_report                     `: Let plugins output some content in the email reports
* `counterize_report_what_filter         `: Let plugins add some items in the list used to generate email reports



= Removed functions =

The following functions have been **removed** and should not be used anymore:

 * `counterize_most_visited_pages()`
 * `counterize_most_visited_pages24hrs()`
 * `counterize_most_requested_urls()`
 * `counterize_most_requested_urls24hrs()`
 * `counterize_most_popular_posts()`
 * `counterize_most_popular_posts24hrs()`
 * `counterize_most_visited_referrers()`
 * `counterize_most_visited_referrers24hrs()`
 * `counterize_most_visited_IPs()`
 * `counterize_most_visited_IPs24hrs()`
 * `counterize_most_visiting_countries()`
 * `counterize_most_visiting_countries24hrs()`
 * `counterize_most_searched_keywords()`
 * `counterize_most_searched_keywords_today()`
 * `counterize_most_used_browsers()`
 * `counterize_most_used_browsers_without_version()`
 * `counterize_most_used_browsers_collapsible()`
 * `counterize_most_used_os()`
 * `counterize_most_used_os_without_version()`
 * `counterize_most_used_os_collapsible()`
 * `counterize_feed_daily_stats()`
 * `counterize_render_daily_stats()`
 * `counterize_feed_monthly_stats()`
 * `counterize_render_monthly_stats()`
 * `counterize_feed_weekly_stats()`
 * `counterize_render_weekly_stats()`
 * `counterize_feed_week_progression_stats()`
 * `counterize_render_week_progression_stats()`
 * `counterize_feed_hourly_stats()`
 * `counterize_render_hourly_stats()`
 * `counterize_feed_most_seen_referers()`
 * `counterize_render_most_seen_referers()`
 * `counterize_feed_most_seen_referers24hrs()`
 * `counterize_render_most_seen_referers24hrs()`
 * `counterize_feed_most_requested_urls()`
 * `counterize_render_most_requested_urls()`
 * `counterize_feed_most_requested_urls24hrs()`
 * `counterize_render_most_requested_urls24hrs()`
 * `counterize_feed_most_popular_posts()`
 * `counterize_render_most_popular_posts()`
 * `counterize_feed_most_popular_posts24hrs()`
 * `counterize_render_most_popular_posts24hrs()`
 * `counterize_feed_most_active_ips()`
 * `counterize_render_most_active_ips()`
 * `counterize_feed_most_active_ips24hrs()`
 * `counterize_render_most_active_ips24hrs()`
 * `counterize_feed_most_searched_keywords()`
 * `counterize_render_most_searched_keywords()`
 * `counterize_feed_most_searched_keywords24hrs()`
 * `counterize_render_most_searched_keywords24hrs()`
 * `counterize_feed_most_visiting_countries()`
 * `counterize_render_most_visiting_countries()`
 * `counterize_feed_most_visiting_countries24hrs()`
 * `counterize_render_most_visiting_countries24hrs()`
 * `counterize_feed_most_used_browsers_collapsible()`
 * `counterize_render_most_used_browsers_collapsible()`
 * `counterize_feed_most_used_browsers_without_version()`
 * `counterize_render_most_used_browsers_without_version()`
 * `counterize_feed_most_used_browsers()`
 * `counterize_render_most_used_browsers()`
 * `counterize_feed_most_used_os_collapsible()`
 * `counterize_render_most_used_os_collapsible()`
 * `counterize_feed_most_used_os_without_version()`
 * `counterize_render_most_used_os_without_version()`
 * `counterize_feed_most_used_os()`
 * `counterize_render_most_used_os()`



== Statistics in your posts and pages ==

= Shortcodes =

You can insert a Counterize diagram using the Counterize button in your Visual editor. The most basic shortcode is:

`[counterize]`

This will display a short copyright notice.

All the attributes are optional. The following attributes and their values are currently available:
<ul>
	<li><strong>type</strong>: String. Valid values are:
		<ul>
			<li>"*copyright*": Shows a copyright notice (default value)</li>
			<li>"browsers":  Browsers diagrams</li>
			<li>"os":        Operating systems diagrams</li>
			<li>"countries": Countries diagrams</li>
			<li>"ip":        IP addresses diagrams</li>
			<li>"hosts":     Hostnames diagrams (only available if enabled in the settings)</li>
			<li>"outlinks":  Outlinks diagrams</li>
			<li>"exitpages": Exit pages diagrams</li>
			<li>"keywords":  Keywords diagrams</li>
			<li>"referers":  Referers diagrams</li>
			<li>"domains":   Domains diagrams</li>
			<li>"hits":      Hits table</li>
			<li>"hourly":    Hourly stats diagrams</li>
			<li>"daily":     Daily stats diagrams</li>
			<li>"weekly":    Weekly stats diagrams</li>
			<li>"monthly":   Monthly stats diagrams</li>
			<li>"all":       All tables and diagrams</li>
		</ul>
	</li>
	<li><strong>items</strong>:        Positive and non-zero integer that represents the number of items to display in the diagram. Default value: 10</li>
	<li><strong>subitems</strong>:     Positive and non-zero integer that represents the number of subitems to display in the diagram. Only effective with collapsible diagrams. Default value: 15</li>
	<li><strong>version</strong>:      String. Set to "yes" to display diagrams with version information, "no" otherwise. Only effective with Browsers and OS diagrams. Default value: "yes"</li>
	<li><strong>collapsible</strong>:  String. Set to "yes" to display collapsible diagrams with each item containing child items, "no" otherwise. Only effective with Browsers and OS diagrams. Default value: "no"</li>
	<li><strong>print_header</strong>: String. Set to "yes" to display a header above the diagram, "no" otherwise. Default value: "yes"</li>
	<li><strong>header</strong>:       String. Set to non-empty string to override the default header. Default value: empty string</li>
	<li><strong>period</strong>:       String. Valid values are:
		<ul>
			<li>(empty string):  No period limit (default value)</li>
			<li>"24h":           Limit data to the latest 24 hours. Available for most diagrams except the Traffic ones</li>
			<li>"onlytoday":     Limit data to today only. Available fpr Traffic diagrams</li>
			<li>"onlythisweek":  Limit data to this week only. Available fpr Traffic diagrams</li>
			<li>"onlythismonth": Limit data to this month only. Available fpr Traffic diagrams</li>
			<li>"onlythisyear":  Limit data to this year only. Available fpr Traffic diagrams</li>
		</ul>
	</li>
	<li><strong>tn_width</strong>:  Positive and non-zero integer. Width of the post thumbnail. Default value: 50</li>
	<li><strong>tn_height</strong>: Positive and non-zero integer. Height of the post thumbnail. Default value: 50</li>
</ul>



= Deprecated =

You can use the following codes in HTML mode when editing your post or page, where 'xx' can be replaced by any integer greater than 0:

* `<!-- counterize_stats -->`
* `<!-- counterize_stats_hits -->`
* `<!-- counterize_stats_browsers_xx -->`
* `<!-- counterize_stats_browsers_nover_xx -->`
* `<!-- counterize_stats_browsers_mixed_xx_xx -->`
* `<!-- counterize_stats_os_xx -->`
* `<!-- counterize_stats_os_nover_xx -->`
* `<!-- counterize_stats_os_mixed_xx_xx -->`
* `<!-- counterize_stats_urls_xx -->`
* `<!-- counterize_stats_urls_24hrs_xx -->`
* `<!-- counterize_stats_posts_xx -->`
* `<!-- counterize_stats_posts_24hrs_xx -->`
* `<!-- counterize_stats_referers_xx -->`
* `<!-- counterize_stats_referers_24hrs_xx -->`
* `<!-- counterize_stats_ip_xx -->`
* `<!-- counterize_stats_ip_24hrs_xx -->`
* `<!-- counterize_stats_countries_xx -->`
* `<!-- counterize_stats_countries_24hrs_xx -->`
* `<!-- counterize_stats_keywords_xx -->`
* `<!-- counterize_stats_keywords_today_xx -->`
* `<!-- counterize_stats_copyright -->`

You can also use `#-` and `-#` instead of `<!--` and `-->`.

You can visit [this webpage](http://www.gabsoftware.com/2011/05/28/counterize-demo/) for a more descriptive example.



== botlist.txt ==

Counterize provides a botlist.txt file. This file is a list of things that should
not be counted in the statistics. It is also used to delete bots manually after
you edited it.

There are three kind of lines you can find in botlist.txt:

* Lines containing part or all of the user-agent to block, without any escaping.
  If a user-agent contains one of this kind of lines, it will be blocked. Example: "bot"
* Lines containing regular expressions. They must be of the following format,
  without the quotes: `regexp:my_regular_expression_pattern`.
  Example: `regexp:#^Mozilla/5\.0 \(compatible$#` will match exactly the
  "Mozilla/5.0 (compatible" malformed user-agent.
* Lines containing complex filters. They must be of the following format:
  `complexfilter:Suspicious_complete_user_agent_string###requested_url###Complete_referer_string`.
  "requested_url" can also be "*" if the bot goes to several pages.
  If "Complete_referer_string" is "%HTTP_HOST%", it will be replaced by the
  complete URL of your website (eg: http://www.yourwebsite.com/).
  If "Complete_referer_string" is "unknown", it means the referer is empty.
  Here is an example of complex filter:
  `complexfilter:Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)###/comment-page-1/###%HTTP_HOST%`
  will block any user-agent equal to "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)"
  accessing "/comment-page-1/" with the referer "http://www.yourwebsite.com/".
  Another example: `complexfilter:Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)###*###unknown`
  will block the "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1)" user-agent
  accessing any pages with an empty referer. Complex filters are powerful, use with caution!

You can add your own lines in the file user_botlist.txt. You may create this file if it doesn't exist.

== Frequently Asked Questions ==

= I know that I have incoming traffic but Counterize keeps telling me that no data has been recorded yet. Why? =
There is probably a problem somewhere, so I will need you to do the following:

* Open your wp-config.php file
* Ensure that WP_DEBUG is set to true. The line should be `define( 'WP_DEBUG', false );`
* Add the following lines just after:

`error_reporting( E_ALL | E_STRICT );
ini_set('display_errors', 1);`

* Reload your website in your browser
* Then tell me about any errors, warnings and notices you may see, if any

If no errors are shown, then you probably do not have incoming traffic... Is Counterize correctly activated?

= How to add a counter for the current post/page in my sidebar? =
You can edit your theme sidebar, or use a PHP code widget (recommended), and use the following code:
`<?php if( is_single() || is_page() ): ?>
<p>This page has been viewed <?php echo counterize_current_post_hits(); ?> times.</p>
<?php endif; ?>`

= How to make my PHP widget appear only for posts and pages? =
If you use a PHP widget and want the widget to appear only for posts and pages, install the "Widget Logic" plugin and use `is_single() || is_page()` as condition.

= How to add a counter in the footer of my blog? =
Try adding the following line somewhere in your footer.php:
`<span class="counter"><?php echo counterize_getuniqueamount( 0, 3600 ); ?> visitors</span>`
the 0 parameter stands for "since forever" and 3600 stands for "count only one visit if the same visitor visit the website less than 3600 seconds after the initial visit". Both values are optional.

= Why aren't the IP of my visitors logged? =
You need to enable the option in the Counterize settings. It is disabled by default.

= How to translate Counterize in my language? =
Please follow <a href="http://www.gabsoftware.com/tips/a-guide-for-wordpress-plugins-translators-gettext-poedit-locale/">these instructions</a>

= Can I extend Counterize? =
Yes. Counterize is now modularizable and you can add your own Counterize plugin. Counterize also provides several action and filter hooks.

= Can Counterize replace Google Analytics? =
Not yet. But it's a good addition/alternative.

= What are the minimum requirements to run Counterize? =
The same as the current stable version of WordPress, but as with each supplementary plugin, it will use more RAM, so take this into account.
Counterize is also known to take a lot of database space in its current version, especially if your website is very visited.

= I see an error message like "`The script whose uid is 12345 is not allowed to access /var/tmp/" or "Warning: session_start() [function.session-start]: Cannot send session cache limiter – headers already sent`", HELP! =
Try adding the following 3 lines just after the PHP start tag of your wp-config.php:
`if( ! isset( $_SESSION ) )
{
	session_start();
}`
Also, make sure the session.save_path in you php.ini is set and point to a valid path

= Do you provide support for Counterize? =
**Sometimes**, I can provide BASIC support for free. It mostly depends on my free time, because Counterize is developped on my free time too.
Nobody likes to work for peanuts, so do not expect professional level support for free.
If you need a better support or something is urgent, please contact me to discuss of a price.

== Changelog ==

= 3.1.5 =
* Fixed some issues with wrong queries
* History now fully functionnal again

= 3.1.4 =
* This is a maintenance release for WP 3.5 compatibility. Nothing new has been added.
* Fixed warnings caused by $wpdb->prepare() called with only one argument and so fixed some possible SQL injection vulnerabilities.
* Fixed warnings caused by unknown countries
* Added Ukrainian and Hebrew translations
* Fixed issue with non-ASCII characters

= 3.1.3 =
* Fixed a race condition in email reports: Counterize now checks if a report should be sent after all the Counterize plugins have been loaded.
* If a report failed to be sent, Counterize will try to send it using the blog admin email as sender
* Corrected a mispelled variable in the Pages plugin
* Corrected some notices about undefined indexes
* Updated French translation
* Added Total hits support in the [counterize] shortcode window helper (other functions will be available in a next release)
* updated installation procedure to update user agents and delete bots only when necessary

= 3.1.2 =
* It wasn't possible to delete entries in History anymore. Now fixed.
* Removed a trailing line in the file ip_files/countries.php. That could cause errors if headers were already sent.
* Updated readme.txt

= 3.1.1 =
* Corrected an issue with ip_files/countries.php: does not use PHP short tags anymore
* Added an updated Spanish translation by José Delgado (Thanks to him!)
* Added a warning message when no Counterize plugins are activated
* Set all the plugins to the version of Counterize because a WordPress bug nag users to update the plugins and report a wrong version information.

= 3.1.0 =
* Counterize has been splitted into several plugins, so that you can enable/disable what you want, and also create your own plugin easily. As a result, several functions have been moved into their respective plugin. See the API section for more information.
* Added Outlinks and exit pages tracking
* Added Email reports
* Added a [counterize] shortcode
* Added a Counterize button in the Visual editor to easily insert the [counterize] shortcode
* Added possibility to use the wildcard characters % and _ in the filters of the History page
* Added the LIKE and NOT LIKE filters on the History page
* Added a debug checkbox to see the generated SQL query in the History page
* Added a date filter in the History page
* Added help sections using the new WP_Screen API
* Added a Counterize menu with nice icons in the WordPress toolbar for easy access
* Now display the post thumbnails correctly in the Most popular pages plugin
* Vertical diagrams bars will reflect the actual percentage better and use the available space in their cells
* Fixed the counterize_current_post_hits() function, with many thanks to Subhash and terrah
* Added a lot of actions and filters that you can hook into
* Improved the required capability dropdown listitem and added roles inside (roles are easier to understand than capabilities...)
* Now use pure CSS to display the diagram bars, no more images, for faster loading time and less server requests
* Counterize will load the file user_botlist.txt if it exists. Users can create this file and put their bots inside, so that it is not overwritten after an update
* Corrected a bug in the Counterize Javascript that made switching between Visual and HTML tab impossible in editor, and possibly many others problems
* Updated IP to Country database
* Optimization of the counterize_delete_bots() and counterize_delete_broken_entries() functions
* Added some indexes to speed up things. Especially on counterize_referers.keywordID during execution of the function counterize_delete_broken_entries().
* Added the following countries: Ascension Island, Åland Islands, Saint Barthélemy, "Bonaire, Sint Eustatius and Saba", Bouvet Island, Clipperton Island, Curaçao, Chrismas Island, Diego Garcia, Ceuta and Melilla, Western Sahara, Guernsey, Heard Island and McDonald Islands, Canary Islands, Isle of Man, Democratic People's Republic of Korea, Saint Martin (French part), Montserrat, Saint Pierre and Miquelon, Pitcairn, "Saint Helena, Ascension and Tristan da Cunha", Svalbard and Jan Mayen, South Sudan, Sint Maarten (Dutch part), Tristan da Cunha, Turks and Caicos Islands, East Timor, United States Minor Outlying Islands, Wallis and Futuna, Grenada, Saint Lucia, Saint Vincent, Zaire.
* Modified the following countries: Libya, Virgin Islands (British), Virgin Islands (US).
* Changed Counterize pages headers from h2 to h1

= 3.0.33 =
* Improved charts, now more beautiful
* Bipolar charts will now display correctly in case the maximum is <= 0
* Charts without any collapsible item will not display the first column
* Corrected links when the blog is in HTTPS mode (thanks to Gérard)
* Code cleaning
* Some memory usage optimizations
* Corrected some notices
* Improved keywords detection with many more search engines

= 3.0.32 =
* New keyword filter in Counterize History
* In the Counterize History, the "reset filter" link is not a button, as well as navigation links
* Corrected a regression introduced in 3.0.31: localizations works again now.
* Corrected the "#- counterize_function() -#" format that was not working properly before
* The bot logging option is now effective (it was not used before)
* The function "counterize_getuniqueamount()" now has an optional "$since" parameter that allows to return the number of visitors since $since seconds
* The function "counterize_getuniqueamount()" now has an optional "$interval" parameter that allows to count an already existing IP again if it has been inserted $interval seconds after the previous entry for this IP
* The function "counterize_getamount()" now has an optional "$since" parameter that allows to return the number of hits since $since seconds
* New function "counterize_getpagesamount()" that get the number of page views with an optional $since parameter representing a number of second.
* Improved the "Hit counter" section in the Traffic dashboard and added new useful data
* The distinction between a visitor and a hit is more clear now. Traffic charts have been renamed to reflect this.
* Corrected a bug in the post ID detection
* Updated the count field of all tables because it wasn't in phase after the several previous updates and bugs
* A new keyword is now inserted in the database before the referer

= 3.0.31 =
* Corrected eventual SQL injection and XSS vulnerabilities through uncorrectly sanitized _GET and _POST variables. Please update! I reviewed the code and it should be all safe now.
* Added new charts in the Traffic section of the Counterize dashboard: "Visits for the last 7 days", "Progression between last week and current week", "Monthly visits for the current year" and "Hourly visits for the last 24 hours"
* The function "render_feed_horizontal()" of the CounterizeFeed class can now render negative values. A "+" and "-" sign is added to values of such charts, and negative values are shown in a new row for readability.
* A new optional "print_header" parameter is available for counterize charts rendering functions. If set to true (the default value), it will display the feed "title" member.
* A new optional "print_percents" parameter is available for counterize charts rendering functions. If set to true (the default value), it will display the percentages.
* A new optional "unit" parameter is available for the contructor of the CounterizeFeed class. If non-empty, this unit will be printed after the value in charts.
* counterize_feed_weekly_stats() has a new optional parameter "only_this_week"
* counterize_feed_monthly_stats() has a new optional parameter "only_this_year"
* Some divisions by zeros corrected
* Replaced wpdb->escape() by wpdb->prepare() calls
* Rewrite of some SQL functions
* $wpdb->get_var returns NULL (not false) in case of failure: corrected the code to reflect this behavior.
* Code cleaning and reformatting
* counterize_copyright() function now outputs valid HTML code
* This version is officially compatible with WordPress 3.2

= 3.0.30 =
* Introducing Counterize data feeds. You can now use the data as your want!
* Improved rendering of vertical and horizontal stats
* Several functions deprecated, placed in "counterize_deprecated.php". Update your blog to use the new functions!
* Several new functions to replace the deprecated ones (see the "Functions" section in the Readme)
* Code cleaning
* Improved History look and feel
* Added Trisquel GNU/Linux detection
* Added Ultimate Edition Linux detection
* Added PS3 browser detection
* Corrected ChromePlus detection
* Corrected ELinks detection
* Collapsible charts are more beautiful, more lightweight and only collapsible when necessary
* Updated botlist.txt
* Fixed detection of the Jersey country
* Fixed PHP start tag in countries.php (thanks to Robert Hurst for pointing this)
* New Spanish translation from José Delgado
* Added blue color bar if the amount is greater than 99%
* Corrected duplicate entries appearing in Most popular posts/pages charts

= 3.0.29 =
* In the History, going to another page will keep the filters
* The number of pages is now displayed and is more accurately computed
* Added a simple navigation bar on the bottom of the History entries
* Added buttons to clear individual filters
* New function "counterize_current_post_hits()" (Thanks to Greg Froese)
* New functions "counterize_most_popular_posts()" and "counterize_most_popular_posts24hrs()" (from an original idea of Greg Froese)
* Function "counterize_most_visited_pages()" is deprecated and replaced by "counterize_most_requested_urls()"
* Function "counterize_most_visited_pages24hrs()" is deprecated and replaced by "counterize_most_requested_urls24hrs()"

= 3.0.28 =
* Fixed a regression caused by an integer overflow in the country detection code (note for myself: PHP integers are 32 bits SIGNED integers)

= 3.0.27 =
* Removed try catch blocks in upgrade scripts as exceptions are not catched unless I implement my own exception manager, which I don't want. Will also solve PHP4 users complaints.
* Optimized the function counterize_iptocountrycode() in counterize_iptocountry.php. Slightly faster.
* Now test if indexes exist before adding or deleting them

= 3.0.26 =
* Improved installation script (many thanks to Daniel from chaosonline.de!)
* Fixed several SQL bugs found during code review
* Some indexes were transformed to UNIQUE indexes to speed up things and future upgrades
* INSERT, UPDATE and ALTER TABLE statements use the IGNORE clause to avoid the insertion of duplicate records (happened to me)
* Duplicate records will be deleted during the upgrade process
* The upgrade from 3.0.21 to 3.0.22 should use less memory
* The upgrade script should not run two times the previous upgrades
* Modified the History table to be more standard XHTML
* The history table will repeat its header every 25 rows for better readability
* The history table headers and some table rows are now middle centered
* Kill an entry will now decount the related country counter
* Improved History look n feel
* Added parsing of bing referers and fixed bug in referer analyser code

= 3.0.25 =
* Corrected SQL error in history when the database is empty
* Corrected notices caused by get_option() on fresh installation
* Corrected errors during database flush
* Added country counter reset during database flush

= 3.0.24 =
* Corrected an issue with a new Counterize installation

= 3.0.23 =
* Added a navigation bar to the Counterize History page
* Corrected SQL for the counterize_most_visiting_countries() function
* Corrected an issue which prevented the Counterize history to display entries with "unavailable" as IP.
* Added the "00" country code to the countries table. This code is used when the country cannot be determined.
* Deleting bots will also delete broken records from the database (log entries refering to deleted entries for example) and decount each of them from the related tables counters
* Updated botlist.txt
* Corrected changelog typo in the readme

= 3.0.22 =
* Added support for country detection
* New country charts in Counterize Dashboard
* New country filters in history
* New country charts available to insert in your pages and posts (see the "Statistics in your posts and pages" section of this Readme)

= 3.0.21 =
* Fixed some issues with RTL scripts in the History page
* Improved filters in history: clicking the filter link (F) will now add the filter to the filter form instead of reloading the history. If javascript is disabled, will behave as before.
* Can now combine filters of same and different types (example, different IP addresses, Referer AND agent...)
* Can now define include or exclude filters
* Can now filter unknown referers (before the link was missing)
* Button to clear the form and button to cancel changes made to the form. Difference between the two buttons is that the second will restore the previously submitted filter (if any).
* Buttons now integrates better with the WordPress theme

= 3.0.20 =
* Changed "#-- something --#" to "#- something -#" to insert statistics into posts as it seems WordPress change double dashes to one single long dash.
* Removed HTML comments from the Javascript file, it caused some Javascript errors in IE
* Counterize should now display correctly in Right to Left scripts.

= 3.0.19 =
* Maintenance release. The Counterize Dashboard will now try to insert itself in an available position in the menu instead of blindly replace previous menus.

= 3.0.18 =
* Added option to choose the Geo IP tool (before it was hard-coded)
* All the Counterize pages should now be 100% valid XHTML 1.0 Transitional. This means less potential issues.
* Collapsible stats are now a little lighter due to an url encoding issue.
* Counterize should load a little faster because all XML errors have been eliminated.
* Corrected a conflict with the wp-security-scan plugin (function make_seed() was already declared in this plugin).
* Ensure that every function begin with the "counterize_" prefix to avoid conflicts
* Renamed Javascript functions with the "counterize_" prefix
* Renamed browsniff.php to counterize_browsniff.php

= 3.0.17 =
* New form to apply filters in the history (still a work in progress, your ideas are welcome)
* Flushing the DB will reset the auto_increment counters to 1 (thanks to Carsten Becker)
* Backquoted the tables and table fields in Counterize queries
* Moved the Counterize dashboard in its own menu and divided it among several pages for easier access.
* Improved bot detection with possibility to define complex filters and use regular expressions in the botlist.txt file (see the "Other Notes", "botlist.txt" section).
* Improved bot deletion: deleting a bot should now substract the hits related to the bot.
* New Slovenian translation (thanks to Spela Golob Peterlin)

= 3.0.16 =
* Corrected wrong link in the Counterize dashboard widget
* Made some changes to allow non-administrators to see the statistics. The capability to see the statistics can be defined in the administration section and is defaulting to "manage_options" (which only administrators have, usually). See http://codex.wordpress.org/Roles_and_Capabilities#Capability_vs._Role_Table for a descriptions of capabilities.
* The is_admin() function does not behave as I thought (a simple registered user will actually pass the is_admin() check if he goes his profile dashboard) so it has been replaced by current_user_can('manage_options') where relevant.
* Moved some function calls in the init action.

= 3.0.15 =
* Corrected a minor bug in the data validation of the options
* Added function counterize_most_visited_IPs24hrs()
* Added counterize_stats_ip_xx and counterize_stats_ip_24hrs_xx
* Corrected readme.txt
* Updated all translations

= 3.0.14 =
* Updated French translation
* Updated Turkish translation (thanks to Can Aydemir)
* Updated German translation (thanks to Carsten Becker)
* Commented error_reporting(E_ALL) (I forgot to comment it in the previous version)
* Corrected a SQL injection risk
* Corrected the alternative way to put statistics into posts and pages (using #-- and --# instead of &lt;!-- and --&gt;)
* Corrected a serious bug in the installation procedure about migration from version 2.x to 3.x of Counterize

= 3.0.13 =
* Use the WordPress Settings API for the options page for a more WordPress 3 compliant option page
* Now only 2 options stored in the WordPress options table ('counterize_options' and 'counterize_version')
* Restored user-agent filtering in Counterize Dashboard
* IP statistics restored if allowed by your country.
* User can choose to collect IPs if it is allowed in his country. If not allowed, IP will be replaced by 'unavailable'.
* Updated the Geo IP website to whatismyipaddress.com because the other one does not work
* Fixed a lot of issues (warnings, notices, errors...) thanks to WP_DEBUG=true and PHP's error_reporting(E_ALL)
* Renamed a lot of callbacks with the '_callback' suffix in order to recognize them more easily
* Links open in a new window
* Updated French translation. Translation authors should update their translation also.
* Added an alternative way to put statistics into posts and pages (use #-- and --# instead of &lt;!-- and --&gt;)

= 3.0.12 =
* (internal release)

= 3.0.11 =
* Fixed minor installation issue

= 3.0.10 =
* Fixed incorrect naming of localization files. I was thinking the country suffix was not mandatory for the .po and .mo files, but actually it is, otherwise the translation is ignored by WordPress. So for example if your WPLANG value is "fr_FR", WordPress will only load .mo files finishing by "-fr_FR.mo". Thanks to Can for letting me find this.
* Moved some functions away from counterize.php
* Added a bot in botlist.txt

= 3.0.9 =
* Added two functions: counterize_most_used_browsers_collapsible() and counterize_most_used_os_collapsible(). Clicking on the [+] will display the statistics about each version of a given browser or operating system.
* Added their corresponding code for adding the charts in a post or page
* Updated bot list to block a larger number of bots while keeping the list small
* Improved CSS and Javascript loading
* Carsten Becker updated the German translation
* New Turkish translation from Can

= 3.0.8 =
* Corrected an issue with the detection of botfile.txt and delete the bots that made it to the Counterize DB.
* New German translation (thanks to Carsten Becker)
* Added a button to manually delete the bots into the Counterize options page. So now you can modify your botlist.txt and delete the bots with that button.

= 3.0.7 =
* Added more possibilities to add statistics into user posts in a convenient way
* New function counterize_most_used_os_without_version()
* New function counterize_get_hits()
* Fixed function counterize_most_visited_referrers24hrs() (invalid SQL)
* Fixed function counterize_most_visited_pages24hrs() (invalid column header)
* counterize_most_visited_referrers24hrs()) and counterize_most_visited_pages24hrs() now display the amount of the last 24 hours instead of the global amount

= 3.0.6 =
* Security update: The forms and links use Nonce in the administration area
* Security update: It is now impossible to execute the Counterize PHP files directly
* Modified the Counterize dashboard and options URL for a more friendly one
* A complete uninstall script has been added (uninstall.php)
* Fixed excluded user list too small (select style was set to height:2em; )
* Javascript is now loaded using the WordPress recommended way
* 'Configure' link added to the plugins page
* A few SQL query tricks

= 3.0.5 =
* Fixed installation issue: hook register_activation_hook does not fire when plugin is automatically updated

= 3.0.4 =
* The bot list is now in a separate text file (botlist.txt)
* SQL queries have been reformated for easier reading
* Fixed an obvious SQL error when using filters in latest entries

= 3.0.3 =
* The install function was not called after the plugin activation, now fixed
* The Counterize detailed dashboard is now a Dashboard sub-menu (before: a Post sub-menu)
* Warn user about an 'out of memory' error if 0 is defined for the 'Amount of rows to show in history' option
* Corrected a lot of bugs thanks to PHP's error_reporting=E_ALL and WP_DEBUG=true options

= 3.0.2 =
* The Counterize dashboard section is now WordPress 3.x compliant (thanks to Carsten Becker for pointing this out)
* Implemented the fixes found at http://www.mikoder.com.au/2009/07/fixing-counterize/ (thanks to Helmut Hoffmann again!)
* The counterize version is correctly shown in the administration page of Counterize
* The function counterize_get_online_users() was not documented anywhere
* Updated POT file if someone want to translate it
* Updated French translation

= 3.0.1 =
* DEFAULT was ommited in a query, causing the installation to fail. Thanks to Helmut Hoffmann for reporting this!
* No default version number was given. Thanks again to Helmut Hoffmann!

= 3.0.0 =
* Development is now continued by Gabriel Hautclocq (me)
* Chrome is now recognized as well as plenty of other browsers
* Newer versions of Windows recognized
* Many other OS have also been added
* Updated the bot exclude list
* Many OS version added
* Updated some old icons
* Several other improvements have been made to browsniff.php
* Added a button in the admin interface to refresh the user-agent table (useful if you modify browsniff.php yourself)
* Distinction between 32 and 64 bits versions of the OS, as well as ARM and PowerPC  versions
* Browsers and OS charts now display a link to the product
* Fixed garbage alt attribute of the chart bars
* Cleaner PHP code
* Cleaner code output (indentation...)
* WordPress 3.x compliant code
* WordPress 3.x compliant Readme
* Unfortunately, most translations should to be updated to reflect the changes. They have not been included with this version except for the French translation ; please allow some time to the translation authors to update their translation.
* Now distributed under the ISC license

== Upgrade Notice ==

= 3.1.0 =
Please make a backup of your current database before installing this upgrade. After the upgrade, you will need to activate any Counterize plugin you want to use in addition to Counterize itself.

= 3.0.32 =
You are advised to backup your database before installing this update because depending on your table count, it may take a while to update the tables count field.
If the upgrade script stops, the update queries are conceived so that it should be able to restart where it left.

= 3.0.30 =
<strong>Important! The following functions are <font color="red">DEPRECATED</font> and should not be used anymore:</strong>
* counterize_most_visited_pages()
* counterize_most_visited_pages24hrs()
* counterize_most_requested_urls()
* counterize_most_requested_urls24hrs()
* counterize_most_popular_posts()
* counterize_most_popular_posts24hrs()
* counterize_most_visited_referrers()
* counterize_most_visited_referrers24hrs()
* counterize_most_visited_IPs()
* counterize_most_visited_IPs24hrs()
* counterize_most_visiting_countries()
* counterize_most_visiting_countries24hrs()
* counterize_most_searched_keywords()
* counterize_most_searched_keywords_today()
* counterize_most_used_browsers()
* counterize_most_used_browsers_without_version()
* counterize_most_used_browsers_collapsible()
* counterize_most_used_os()
* counterize_most_used_os_without_version()
* counterize_most_used_os_collapsible()
Please update your blog and use the new functions.
The new functions are described in the "Functions" of the Readme.
The deprecated functions are still available for a few releases but they will be deleted sooner or later, so you are strongly advised to use the new functions.

= 3.0.29 =
* The function "counterize_most_visited_pages()" is deprecated and has been replaced by "counterize_most_requested_urls()"
* The function "counterize_most_visited_pages24hrs()" is deprecated and has been replaced by "counterize_most_requested_urls24hrs()"

= 3.0.23 =
This release will probably delete hundreds of broken entries in your database. You are advised to make a backup before upgrading.
You SHOULD make a backup if you are upgrading from 3.0.21 and below (see Readme to see why).

= 3.0.22 =
The migration from Counterize 3.0.21 to 3.0.22 is particularly CPU demanding because we have to compute the country from each IP in the database.
If the webserver throws a timeout, you should be able to resume the upgrade where it stopped.
On my web server I was able to run the upgrade script in 13 seconds, for about 6500+ IP addresses in the database.
My apologies for any inconvenience caused by this upgrade.
Consider to make backups of your Counterize tables before upgrading.
Upgrading your database on a local web server should circumvent almost any limitations that your web hosting company set up on your hosting.
In case something wrong happens, here is the SQL code to get back to the previous database state (assuming you were using 3.0.21 before, and that your wordpress tables prefix is 'wp_'):
ALTER IGNORE TABLE `wp_Counterize` DROP INDEX `IP` ;
ALTER IGNORE TABLE `wp_Counterize` DROP COLUMN `countryID` ;
DROP TABLE IF EXISTS `wp_Counterize_Countries` ;
UPDATE `wp_options` SET `option_value`='3.0.21' WHERE `option_name`='counterize_version';
The IP database is updated daily on the software77.net website, however Counterize database is not (at the moment).
If you need to upgrade to the latest version of the database, go to http://www.phptutorial.info/iptocountry/the_script.html and download the ip_files.zip archive.
Extract this archive in the "ip_files" folder located in the Counterize plugin folder.

= 3.0.0 =
If you are upgrading from Counterize II, please read the "Migration from Counterize II" section.

