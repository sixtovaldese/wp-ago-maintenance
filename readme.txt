=== aGo Maintenance ===
Contributors: agolab
Donate link: https://paypal.me/sixtovaldes
Tags: maintenance, coming soon, under construction, countdown, splash
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One-click maintenance mode and coming soon page with countdown timer, IP whitelist, admin bypass and SEO-friendly headers.

== Description ==

aGo Maintenance switches your site to a clean maintenance or coming soon page with a single toggle. Configure the title, message, countdown, logo, background image and color scheme from the dashboard.

**Features**

* Two modes: maintenance (HTTP 503) or coming soon (HTTP 200).
* Custom title, message, logo, background color or background image with adjustable overlay.
* Optional countdown timer to a target date.
* IP whitelist to keep the site visible to selected addresses.
* Logged-in users who can edit posts (administrators, editors, authors and contributors) always see the site.
* Admin bar indicator while the page is active, plus a one-click preview.
* SEO-friendly: 503 with Retry-After header for maintenance, regular 200 for coming soon.
* No external services. No tracking.

== Installation ==

1. Upload the `ago-maintenance` folder to `/wp-content/plugins/` or install via the Plugins screen.
2. Activate the plugin through the Plugins menu in WordPress.
3. Go to aGo Tools, then Maintenance.
4. Configure the page and toggle "Enable maintenance mode" or "Enable coming soon mode".

== Frequently Asked Questions ==

= Will search engines deindex my site? =

In maintenance mode the plugin returns HTTP 503 with a Retry-After header. Search engines understand this as a temporary state. Coming soon mode returns 200 and is meant for sites that are not yet indexed.

= Can I keep working on the site? =

Yes. Logged-in users who can edit posts see the site as usual. You can also add your IP address to the whitelist.

= The IP whitelist does not match my address. Why? =

The plugin compares the address of the connection (REMOTE_ADDR) and ignores forwarding headers such as X-Forwarded-For, because any visitor can send them. If your site is behind a proxy or a CDN, the server has to pass the real visitor address to WordPress; until then, the address WordPress sees is the one of the proxy.

= I turned it on, but visitors still see the site. Why? =

A page cache (a caching plugin, the hosting server or a CDN) can keep serving the pages it stored before maintenance was turned on. Clear that cache after you turn the page on or off. The maintenance page itself is sent with no-cache headers, so it is not stored.

= Is the plugin translated? =

The plugin uses your WordPress site language. Translations are contributed through translate.wordpress.org, and the screens show in English until one exists for your language.

== External services ==

This plugin does not connect to any external services. It runs entirely on your own server. No data is sent anywhere, and no remote APIs are contacted.

The settings screen links to external sites that only open when you click them: the aGo Lab website and documentation, UptimeRobot, httpstatus.io, Google Search Console and PayPal donation pages. No data is transmitted automatically.

== Privacy ==

aGo Maintenance stores a single option (`agomaintenance_settings`) holding your maintenance settings (mode, title, message, colors, countdown date, logo and background image URLs, and your IP whitelist). It collects no visitor data, sets no cookies, and contacts no third party.

On uninstall, the plugin deletes its option. No personal data is collected, profiled, or shared.

== Screenshots ==

1. Settings screen with the mode switch, page texts, images, colors, countdown and IP whitelist.
2. The maintenance page visitors see, with logo, message and countdown.
3. Sidebar with Quick links, About, Other aGo Lab plugins and donation cards.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
