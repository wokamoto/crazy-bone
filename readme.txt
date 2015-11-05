=== Crazy Bone ===
Contributors: wokamoto, megumithemes
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_donations&business=9S8AJCY7XB8F4&lc=JP&item_name=WordPress%20Plugins&item_number=wp%2dplugins&currency_code=JPY&bn=PP%2dDonationsBF%3abtn_donate_SM%2egif%3aNonHosted
Tags: log, login, users
Requires at least: 3.5
Tested up to: 4.3.1
Stable tag: 0.6.0

Tracks user name, time of login, IP address and browser user agent.

== Description ==

Tracks user name, time of login, IP address and browser user agent.

= Localization =
"Crazy Bone" has been translated into languages. Our thanks and appreciation must go to the following for their contributions:

* Japanese (ja) - [OKAMOTO Wataru](http://dogmap.jp/ "dogmap.jp") (plugin author)

If you have translated into your language, please let me know.

== Installation ==

1. Upload the entire `crazy-bone` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Frequently Asked Questions ==

none

== Screenshots ==

1. The admin page

== Changelog ==

**0.6.0 - Dec. 5, 2016

fixed XSS.
Unauthenticated attackers can inject html/js into User-Agent HTTP request header resulting in persistent XSS on page /wp-admin/users.php?page=crazy-bone%2Fplugin.php.


**0.5.6 - Dec. 5, 2016

fixed minor bug.

**0.5.5 - Jan. 18, 2015**

Pagination doesn't work correctly.

**0.5.4 - Dec. 17, 2014**

Fix "Redefining already defined constructor" warning

**0.5.3 - Oct. 20, 2014**

source code refactoring.

**0.5.2 - Oct. 23, 2013**

action hook fix, sql syntax fix

**0.5.1 - Sep. 9, 2013**

Multisite support.

**0.5.0 - Aug. 14, 2013**

Added custom filter ( 'crazy_bone::admin_menu_capability', 'crazy_bone::realtime_check' )

**0.4.0 - May 17, 2013**

Added Summary page.

**0.3.0 - May 17, 2013**

Added "Truncate Log" option.

**0.1.0 - May 10, 2013**  

Initial release.
