=== aGo Maintenance ===
Contributors: agolab
Donate link: https://paypal.me/sixtovaldes
Tags: maintenance, coming soon, under construction, countdown, splash
Requires at least: 6.0
Tested up to: 7.0
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
* Admins and logged-in editors always bypass the page.
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

Yes. Logged-in administrators bypass the page. You can also add your IP to the whitelist.

== External services ==

This plugin does not connect to any external services. It runs entirely on your own server. No data is sent anywhere, and no remote APIs are contacted.

The sidebar links to external sites (PayPal donation pages and the aGo Lab website) that only open when you click them. No data is transmitted automatically.

== Privacy ==

aGo Maintenance stores a single option (`agomaintenance_settings`) holding your maintenance settings (mode, title, message, colors, countdown date, logo and background image URLs, and your IP whitelist). It collects no visitor data, sets no cookies, and contacts no third party.

On uninstall, the plugin deletes its option. No personal data is collected, profiled, or shared.

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
