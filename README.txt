=== Media EP ===
Contributors: weskoop
Donate link: http://ofdesks.com/
Tags: twitter, twitpic, media, end point
Requires at least: 3.5
Tested up to: 3.5
Stable tag: trunk
License: GPLv2 or later

A non-authenticating WordPress Media Endpoint for Twitter Clients. It does NOT require O-Auth or Twitter developer keys.

== Description ==

This is a Media End Point for use in Twitter clients such as Tweetbot or Twitterific. It can be used by any application that supports a Twitpic-like interface.

The plugin generates a *secret URL* (like a secret API key) that is pasted in your Twitter client.

Images can automatically be assigned to a page, and that page can use a plain [gallery] tag to display new images automatically.

Unlike other end point plugins, this does not require any Twitter developer Keys or O-Auth authentication; in turn does not authenticate or use any of Twitter's API.

== Installation ==

Pretty simple actually —

1. Upload the `media-ep/` folder to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Configure the plugin, and get/change/regenerate your secret url in  **Settings &rarr; Media EP**
1. Copy the secret URL into your favorite Twitter client

== Frequently Asked Questions ==

= What if I send my secret URL to someone? =

Then that person can post images to your site. If you have done this by accident, then change your Secret URL to cut off access.

= Why does it require 3.5? =

Because I'm not going to test it on older versions.

== Screenshots ==

1. To configure the destination of your photoes and get your *secret url* — did we mention it's a secret? — Go to **Settings &rarr; Media EP**
2. Copy that URL into your favorite Twitter client (TweetBot or Twitteriffic work great)

== Changelog ==

= 1.0.1 =
* Initial Release

== Upgrade Notice ==
First Release.
