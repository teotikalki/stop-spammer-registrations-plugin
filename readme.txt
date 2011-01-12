=== Stop Spammer Registrations Plugin ===
Tags: spam, registration, spammers, MU
Donate link: https://online.nwf.org/site/Donation2?df_id=6620&6620.donation=form1
Requires at least: 2.3
Tested up to: 3.0
Contributors: Keith Graham
Stable tag: 1.12

This plugin Uses the StopForumSpam.com DB to prevent spammers from registering or making comments.

== Description ==
The Stop Spammers Plugin Plugin Accesses the StopForumSpam.com db to check emails, username and IP before a user can register. The StopForumSpam database contains over a million spammer emails, User Names and IPs, and is updated daily. 

The plugin validates Email, IP and User name against the StopForumSpam.com db. It denies login attempts, such as spammers trying to guess passwords or spammers trying to create login ids. It also stops spammers who try to add comments with a spammer email or ip addresses.

The plugin caches 60 spam results to avoid hits on StopForumSpam.com. It remembers the Username, IP and Email of any request that fails on any part of Username, IP and Email, so be careful when testing because an invalid Email will also invalidate your IP or test username. You may have to clear the cache in order to clear out a mistake.

StopForumSpam.com limits hits on its database to 5,000 a day, so if you are being hit hard by spammers you may not be able to effectively block them. I have yet to see this many hits on any of my sites. 

Many spam solutions are concerned with comments. This plugin validates email addresses and is primarily concerned with registrations. I have a website where I needed to allow registrations, but the users would probably not comment. This plugin kept the flood of registrations down to a manageable few. It works when Wordpress Validates an email address so it will also work when users enter an email address in comments.

This plugin also stopped spam registations on my WordPress MU site.

The plugin dispays a list of the last 30 emails, IPs, User names and passwords used in failed denied login attempts. It displays the cached results of hits to StopForumSpam.com up to 60 entries.

With the 1.9 release I added a link on the WordPress comments maintenance so you can check a comment against the StopForumSpam.com database. 

If you have a StopForumSpam.com API key you can report spam. This requires that you click the link where it will pre-fill the form for you. At that point you can enter your API key and submit. If you have previously logged in, it will fill in the API key for you and then you can submit the spam. You can easily get an API key after registering at StopForumSpam.com

 
== Installation ==
1. Download the plugin.
2. Upload the plugin to your wp-content/plugins directory.
3. Activate the plugin.

== Changelog ==

= 1.0 =
* initial release 

= 1.2 =
 * renumber releases due to typo
 
= 1.3 =
 * Check the ip address whenever email is checked.
 
= 1.4 =
 * Checks the user name. Cache failed attempts with option to clear cache. Cleans up after itself when uninstalled. 

= 1.5 =
* fixed a bug where the the admin user was cached in error.

= 1.6 =
* Improved caching to help stop false rejections.
 
= 1.7 =
* Included signup form, that I forgot to add before. Cached data is automatically expired after 24 hours.
 
= 1.8 =
* fixed the cache cleanup (again). Changed the name in the titles and menus of the plugin to reflect that it does more than stop registrations.

= 1.9 =
* Added link to report spam to StopForumSpam.com database.

= 1.10 =
* Improved the access to StopForumSpam.com database. Fixed white space at end of plugin.
 

= 1.11 =
* Stored the StopForumSpam API Key. Fixed a possible security hole on the settings page.
 
= 1.12 =
* Fixed typo error.
 

== Support ==
I am work ing on a version that can update the stopforumspam db when a new spammer is identified.
This plugin is in active development. All feedback is welcome on "<a href="http://www.blogseye.com/" title="Wordpress plugin: Stop Spammer Registrations Plugin">program development pages</a>".
This plugin is free and I expect nothing in return. 
Please check out the books and stories that I've written on Amazon: 
<a href="http://www.amazon.com/gp/product/1456336584?ie=UTF8&tag=thenewjt30page&linkCode=as2&camp=1789&creative=390957&creativeASIN=1456336584">Error Message Eyes: A Programmer's Guide to the Digital Soul</a>

