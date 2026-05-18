=== aGo Maintenance ===
Contributors: sixtovaldese
Donate link: https://paypal.me/sixtovaldes
Tags: maintenance, coming soon, under construction, countdown, splash
Requires at least: 6.0
Tested up to: 6.9
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

One-click maintenance mode and coming soon page with countdown timer, IP whitelist, admin bypass and SEO-friendly headers.

== Description ==

aGo Maintenance switches your site to a clean maintenance or coming soon page with a single toggle. Configure the message, countdown, social links and color scheme from the dashboard.

**Features**

* Two modes: maintenance (HTTP 503) or coming soon (HTTP 200).
* Custom title, subtitle, message and background color or image.
* Optional countdown timer to a target date.
* IP whitelist to keep the site visible to selected addresses.
* Admins always bypass the page when logged in.
* SEO-friendly: 503 with Retry-After header for maintenance, regular 200 for coming soon.
* Social icons and contact email.
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

== Changelog ==

= 1.0.0 =
* Initial release.

== Upgrade Notice ==

= 1.0.0 =
Initial release.
