=== Stop Spammers ===
Tags: spam, comment, registration, login
Requires at least: 3.0
Tested up to: 4.2-alpha
Contributors: Keith Graham
Stable tag: 6.05
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Stop Spammers Plugin blocks spammers from leaving comments or logging in.

== Description == 
Stop Spammers is an aggressive website defence against comment spam and login attempts. It is capable of performing more than 20 different checks for malicious events and can block spammers from over 100 different countries. 
Much of the code in this plugin is dedicated to allowing good users access to comments with many "allow" features to prevent having false positives when checking spam.
There are 12 pages of options that can be used to configure the plugin to your needs.
In cases where spam is detected, users are offered a second chance to post their comments or login. Denied requests are presented with a captcha screen in order to prevent users from being blocked. The captcha can be configures as OpenCaptcha, Google reCaptcha, or SolveMedia Captcha. The Captcha will only appear when a user is denied access as a spammer.
The plugin is designed to work with other plugins like Gravity Forms. It looks at any FORM POST such as BBPress or other addons that use access controls. THe plugin implements a fuzzy search for email and user ids in order to check for spam.
The Stop Spammers Plugin has been under development since 2010.

 
== Installation ==
1. Install the plugin using "add new" from the plugin's menu item on the WordPress control panel. Search for Stop Spammers and install.
OR
1. Download the plugin.
2. Upload the plugin to your wp-content/plugins directory.
THEN
3. Activate the plugin.
4. Under the settings, review options that are enabled. The plugin will operate very well without changing any settings. You may wish to update Web Services APIs for reporting spam and change the captcha settings from the default OpenCapture.  

== Changelog ==


= 6.05 =
* Bad mistake in cloudflare module fixed. Breaks on IPv6 checks

= 6.04 =
* Removed goto in cloudflare check. It was a wonderful dream that turned into a nightmare when it turns out 5.2 PHP doesn't support the goto statement. It was the first goto that I've coded in high level language in 25 years and I wanted it to work.

= 6.03 =
* Added robust full wild card search for lists using * and ?
* Restored link in registration email
* Restored use of WP_Http for all web service file reads
* Added PHPInfo to Diagnostics
* Added delete transients option to Other WP Options
* Changed from Ugly image to a more conventional one on admin panel
* Fixed bug in link for SFS api checks.
* Forced CloudFlare IP fixing if CloudFlare plugin not found. 
It is still better to install CloudFlare plugin to get most recent IP list, but at least this way the plugin can check for bad ips.



= 6.02 =
* fix link typo in summary.
* fix conflict with Woo Commerce.


= 6.01 =
* Total Rewrite of all code. The plugin uses modular approach so that programmers can add new modules to detect spam. 
* added Diagnostic checks.
* added the ability to use a simple API so that plugin authors can hook into the Stop Spammers' processing to add new detection methods.
* added the ability to block spammers by country.
* added better proxy and firewall detection.
* added multiple allow lists to help prevent false positives.
* improved the plugin interface.
* added the ability to scan the WordPress installation for malicious code.
* added the ability to view and maintain all options, including those from other plugins.
* added second chance captcha options including OpenCaptcha, Google reCaptcha or SolveMedia captcha.



== Frequently Asked Questions ==

= All spammers have the same IP address =
This is the most comment problem that I see. If you see in your log that all users have the same IP address it is possible that your site is behind a firewall of proxy. The IP address that the plugin sees is the IP address of the Proxy or Firewall. You need to configure the proxy to pass the user's original source IP to you. CloudFlare will use its IP address if it is acting as a proxy for your site. You MUST install the CloudFlare plugin in this case. Stop Spammers can do little without a reliable IP address.

= Help, I'm locked out of my Website =
Not everyone who is marked as a spammer is actually a spammer. It is quite possible that you have been marked as a spammer on one of the spammer databases. There is no "back door", because spammers could use it.
Rename stop-spammer-registrations.php to stop-spammer-registrations.xxx and then login. Rename it back and check the history logs for the reason why your were denied access. Was your email or IP address marked as spam in one of the databases? If so, contact the website that maintains the database and ask them to remove you. 
Check off the box, "Automatically add admins to Allow List" in the spammer options settings. Then save your settings. This puts your IP address into the Allow List. You should be able to logout and then log back in.
Use the button on the Stop Spammer settings page to see if you pass. You may have to uncheck some options in order to pass. 
Users in some countried often have to use Proxy servers or VPNs in order to access the site. Often the proxy servers are marked as a source of spam. You should find the IP addresses of the proxies that you use and add add those IP addresses to the Allow List.
You can possibly find out why you were locked out by using the form on the Diagnostics page.
Avoid lockouts my making sure that the second chance captcha is turned on.


= I have found a bug =

Please report it NOW. I fill try to fix it and incorporate the fix into the next release. I try to respond quickly to bugs that are possible to fix (all others take a few days). 
If you are adventurous you can download the latest versions of some of my plugins before I release them.

= I used an older version of the plugin and it worked, but the latest version breaks my site =
You can download previous versions of the plugin at: http://wordpress.org/extend/plugins/stop-spammer-registrations-plugin/developers/
Don't forget to report to me what the problem is so I can try to fix it.

= All spammers have the same IP =
I am finding more and more plugin users on hosts that do some kind of Network Address Translation (NAT) or are behind a firewall, router, or proxy that does not pass the original IP address to the web server. If the proxy does not support X-FORWARDED-FOR (XFF) type headers then there is little that you can do. You must uncheck the "Check IP" box and rely on the plugin to use the passive methods to eliminate spammers. These are good methods and will stop most spammers, but you cannot report spam without reporting yourself, and you cannot cache bad IP addresses.

= I can't log into WordPress from my Android/iPhone app. =
Check your log files to find out exactly why the app was rejected. It usually is often the HTTP_REFERER header was not sent correctly. This is one sign of badly written spam software. It is also, unfortunately, a sign of badly written login software. Uncheck the box on the Stop Spammer settings page "Block with missing or invalid HTTP_REFERER". I Allow List iPhones and iPads using Safari on some checks because of bugs in the headers it sends.

= I see errors in the error listing below the cache listing =
It could be that there is something in your system that is causing errors. Copy the errors and email them to me, or paste them into a comment on the WordPress plugin page. I will investigate and try to fix these errors.

= You plugin is stopping new spam registrations, but how do I clean up existing spam registrations? =
Unfortunately, WordPress did not record the IP address of User registrations prior to version 5.0. This is a design flaw in WordPress. They do record the IP of comments. I cannot run a check against logins without their IP address, so you have to remove users the old fashioned way, one at a time. 
You might try listing the emails of all registered users, and then deleting them. You can then ask all users to re-register, but that would probably annoy your legitimate users.

= I have a cool idea for a feature for Stop-Spammer-Registrations-Plugin. =
I am a full time programmer and have little time to work on my own projects. I will certainly make note of your suggestion, but I may never get to it.

= I would like to support your programming efforts =
I am slowing down maintenance on this plugin. I don't have time to work on it. Don't send me money unless you have a corporate credit card and your bosses can afford it. There is a plugin menu item to contribute. It has links for contributions and buying my books. The best way to support me is to buy me a beer at the local Blues Jam and don't laugh when I play harmonica.

== Support ==

2/21/2015: I found that I cannot handle support other than try to fix problems when pointed out. If you are locked out of your website, delete the plugin and don't use it again. If you find it is too aggressive then start un-checking boxes in the configuration until it works. My sites are hosted on SiteGround.com. I pay for this service, and the plugin works perfectly. I can recommend www.SiteGround.com wholeheartedly. If you self-host or you are on a free or cheap hosting company that uses a proxy server or does not implement basic PHP functions then you cannot use this plugin.


