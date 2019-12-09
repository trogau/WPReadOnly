=== WPReadOnly ===
Contributors: trogau
Tags: security
Requires at least: 4.5.1
Tested up to: 4.5.1
Stable tag: 4.5.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WPReadOnly allows you to quickly toggle the filesystem permissions on your WordPress install between 'read only' and 'writable'. 

== Description ==

WARNING: this is intended for more advanced WordPress users who are familiar with Linux filesystem permissions and the ramifications of changing them!

A common problem with WordPress sites is hacking. One of the major vectors is that the standard WordPress setup typically is such that files can be written to the web server's disk - commonly exploited to serve malware, spam, etc. 

For many implementations of WordPress, the ability to write to disk is only required when setting up and/or performing certain types of maintenance (e.g., installing themes, plugins, updates). 

It is (probably) safer, as a general rule, if the web user cannot write to disk - in the event of an exploit in the PHP application, it will mitigate some of the attacks. Attackers will no longer be able to edit the PHP files to server ads/spam/malware, or add new files to the disk. 

Permissions are set using chmod and find via exec(). 

The two 'modes' are: 

1. read-only: sets mode 555 on directores and 444 on files
1. write: sets mode 755 on directories and 644 on files


== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->WPReadOnly Options page to toggle the permissions. 


== Frequently Asked Questions ==

= What's with the 'couldn't exec()' failure? =

One of my test machines runs under HHVM; sometimes all exec() calls just fail, requiring a restart of the HHVM daemon. This is just to catch that. 

= What's on the TODO? =

* Better error handling - probably a range of things I haven't thought of which will cause this to break. 
* Better checks on writeable directories/files (at the moment it only looks at /wp-content).

== Changelog ==
= 0.3 =
* Removed dependency on `find` and `chmod` binaries; now uses native PHP calls to loop through files and change mode
* Changed link name in admin menu to make it a little neater
* Changed so the main display page now uses get_home_path() as well instead of DOCUMENT_ROOT

= 0.2 =
* Changed $_SERVER['DOCUMENT_ROOT'] to use get_home_path() per WP plugin requirements
* Added check for Linux per WP plugin requirements
* Added nonce to form per WP plugin requirements
* Added a bunch of warnings to dissuade non-technical users from using it
* Added check to ensure required binaries (find, chmod) are present in expected locations
* Added administrator role requirement

= 0.1 =
* First release
