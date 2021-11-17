=== Flexy Breadcrumb ===
Contributors: PressTigers
Donate link: https://www.presstigers.com
Tags: breadcrumb, breadcrumbs, navigation, menu, link, page link, navigate
Requires at least: 4.6
Tested up to: 5.8
Requires PHP: 7.2
Stable tag: 1.1.4
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Flexy Breadcrumb is a super light weight plugin that is easy to navigate through current page hierarchy.

== Description ==
Flexy Breadcrumb by <a href="https://www.presstigers.com">PressTigers</a> is one of the simple and robust breadcrumb menu system available for the WordPress site. By using this plugin, you can display breadcrumb navigation anywhere in your website via [flexy_breadcrumb] shortcode.
With the help of this plugin you can style and format the text, links and separators of breadcrumbs according to your own taste.

= Plugin Features =

* SEO Friendly(added schema structure).
* Use via [flexy_breadcrumb] shortcode. 
* Allow users to change breadcrumb separator.
* Set Home text and End text.
* Set Word/Character limit for navigation menu.
* Font Awesome icon picker for Home.
* Color options for text, link, separator and background through global settings.
* Set font size of breadcrumb trail.

= Shortcode =
This goes somewhere near the bottom of your theme’s header.php template. However, you can add it anywhere you want in your theme, and it’ll work.

` <?php echo do_shortcode( '[flexy_breadcrumb]'); ?> `

= Submit Patches =
If you’ve identified a bug and have a fix, we’d welcome it at our [GitHub page](https://github.com/presstigers/flexy-breadcrumb/) for Flexy Breadcrumb. Simply submit a pull request so we can review and merge into the codebase if appropriate from there. Happy coding!

== Installation ==

1. Upload flexy-breadcrumb.zip to the /wp-content/plugins/ directory to your web server.
1. Activate the plugin through the 'Plugins' menu in WordPress.
1. Add [flexy_breadcrumb] shortcode in the editor or appropriate file(header.php) to display the breadcrumb on front-end.

== Frequently Asked Questions ==

= How to use Flexy Breadcrumb Shortcode? =
There are several methods but in general, you need to add the following line of code to your theme. This goes somewhere near the bottom of your theme's header.php template. However, you can add it anywhere you want in your theme, and it'll work.

`<?php echo do_shortcode( '[flexy_breadcrumb]'); ?>`

== Screenshots ==
 
1. **Basic Theme** - Front-end view of breadcrumb trail.
2. **General Settings** - Allow user to manage general settings.
3. **Typography Settings** - Allow user to change color options for breadcrumb trail.

== Credits ==

1. Google Fonts(https://fonts.google.com)
1. jQuery UI(https://jqueryui.com)
1. WP Color Picker Alpha(https://github.com/23r9i0/wp-color-picker-alpha)
1. Font Awesome Icon Picker(https://github.com/itsjavi/fontawesome-iconpicker)
 
== Changelog ==

= 1.1.4 =
* Feature - Added breadcrumb trail for Blog.
* Tweak – Updated Google Structured of breadcrumbs.  
* Fix - Fixed the responsive spacing issue of separator.
* Fix - Resolved separator color issue related to typography.

= 1.1.3 =
* Tweak – Added compatibility for WordPress 5.5

= 1.1.2 =
* Fix - Resolved errors of breadcrumb trail with Google Search Console.

= 1.1.1 =
* Fix - Added slash at the end of the URL - Google Recommendation
* Fix - Wrapping the admin style to avoid from any style conflict.
* Tweak - Update the schema links to https.

= 1.1.0 =
* Feature - Added breadcrumb trail for default posts page.
* Feature - Display category in post detail page having highest count.
* Fix - Fixed Google Structured Schema for Breadcrumbs.
* Fix - Remove archive link for custom post type if archive parameter is false.
* Tweak - Display only highest category count in post detail page.

= 1.0.3 =
WP 4.9 Compatibility – Resolved the color picker issue in settings’s typography tab.
* Fix - Resolved the space issue between the <a> attributes.

= 1.0.2 =
* Fix - Resolved the structured data issue for active list element.

= 1.0.1 =
* Fix - Resolved the floating issues in breadcrumb template.
* Fix - Fixed the styling glitches of settings tab's layout.
* Fix - Resolved the 404 error in case of home page.
 
= 1.0.0 =
* Initial release

== Upgrade Notice ==
= 1.1.4 =
Fixed the issues and updated Google Structured data.