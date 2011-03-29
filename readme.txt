=== Stop Spammer Registrations Plugin ===
Tags: spam, registration, spammers, MU
Donate link: http://www.amazon.com/gp/product/1456336584?ie=UTF8&tag=thenewjt30page&linkCode=as2&camp=1789&creative=390957&creativeASIN=1456336584
Requires at least: 2.8
Tested up to: 3.0
Contributors: Keith Graham
Stable tag: 1.16

This plugin Uses the StopForumSpam.com DB to prevent spammers from registering or making comments.

== Description ==
The Stop Spammers Plugin Plugin Accesses the StopForumSpam.com db to check emails, username and IP before a user can register. The StopForumSpam database contains over a million spammer emails, User Names and IPs, and is updated daily. 

Watch the video! http://www.youtube.com/watch?v=EKrUX0hHAx8. The video shows one of my plugins that anti-spam cops use. They run honey pots or sites that do nothing but attract spammers. These sites report as many as 1,000 spammers per hour to the same database that this plugin checks.

The plugin validates Email, IP and User name against the StopForumSpam.com db. It denies login attempts, such as spammers trying to guess passwords or spammers trying to create login ids. It also stops spammers who try to add comments with a spammer email or ip addresses.

The plugin caches 60 spam results to avoid hits on StopForumSpam.com. It remembers the Username, IP and Email of any request that fails on any part of Username, IP and Email, so be careful when testing because an invalid Email will also invalidate your IP or test username. You may have to clear the cache in order to clear out a mistake.

The plugin will optionally stop spammers who do not send the HTTP_ACCEPT header from the browser. Since all normal browsers use this header, it is safe to assume that anyone who does not send it is hitting you with a robot.

StopForumSpam.com limits hits on its database to 10,000 a day, so if you are being hit hard by spammers you may not be able to effectively block them. I have yet to see this many hits on any of my sites. 

Many spam solutions are concerned with comments. This plugin validates email addresses and is primarily concerned with registrations. I have a website where I needed to allow registrations, but the users would probably not comment. This plugin kept the flood of registrations down to a manageable few. It works when Wordpress Validates an email address so it will also work when users enter an email address in comments.

This plugin also stopped spam registations on my WordPress MU site.

The plugin dispays a list of the last 30 emails, IPs, User names and passwords used in failed denied login attempts. It displays the cached results of hits to StopForumSpam.com up to 60 entries.

If you have a StopForumSpam.com API key you can report spam. You can easily get an API key after registering at StopForumSpam.com.

When you include the Project Honeypot API key each user will be validated against the HTTP:bl blacklist. You can get an api key at http://www.projecthoneypot.org

There is a white list option so that you can bypass the database check for some users.

 
== Installation ==
1. Download the plugin.
2. Upload the plugin to your wp-content/plugins directory.
3. Activate the plugin.
4. Add the appropriate API keys (optional). Update the white list.

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
 
= 1.13 =
* Changed Evidence field to spam url or content

= 1.14 =
* Changes suggested by Paul at StopForumSpam. Fix bug in zero history data. There has been much interest in the plugin so there has been lots of feedback. I am sorry for all the updates, but they are all good stuff.

= 1.15 =
Options added. 1) Reject if Accept header not found. Spammers use some kind of lazy approach that does not send the HTTP_ACCEPT header. All real browsers have this header. 2) Check on BL Blacklist. If for some reason the ip and email pass on the StopForumSpam db you can have a second check on Project Honeypot. 3) Added a white list in case there are IPs or emails that have problems. 4) Stopped checking for Usernames because of too many false positives. 4) Made checking for emails optional. Most spammers use bogus or random emails anyway. 5) Ability to recheck comments against the HoneyPot db from the comments admin form.

= 1.16 =
Added RoboScout.com spam check to ip address. Added limits to checking to allow know spammers who are not recent spammers or do not have many spam reported. Added a complete list of passed and rejected login attempts. Fixed a bug introduced in 1.15. Fixed check on accept headers that prevented it from working.

== Support ==
Version 2.0 is being developed. It will check against additional spam databases and fully support MU blogs from one panel.
This plugin is in active development. All feedback is welcome on "<a href="http://www.blogseye.com/" title="Wordpress plugin: Stop Spammer Registrations Plugin">program development pages</a>".
This plugin is free and I expect nothing in return. If you wish to support my programming, buy the book: 
<a href="http://www.amazon.com/gp/product/1456336584?ie=UTF8&tag=thenewjt30page&linkCode=as2&camp=1789&creative=390957&creativeASIN=1456336584">Error Message Eyes: A Programmer's Guide to the Digital Soul</a>

