<?php
/*
Plugin Name: WPReadOnly
Plugin URI: http://trog.qgl.org/WPReadOnly
Description: Plugin to toggle write permissions in a standard WordPress install. 
Author: trogau
Version: 0.2
Author URI: http://trog.qgl.org
License: GPLv2 or later
*/

/**
 * WordPress Options
 *
 * Taken from http://ottopress.com/2009/wordpress-settings-api-tutorial/ (referenced in official WordPress 
 * docs here: https://codex.wordpress.org/Creating_Options_Pages
 */

function wpreadonly_init()
{
	require_once( ABSPATH . '/wp-admin/includes/file.php' );

	if(current_user_can('administrator'))
	{
		$docroot = get_home_path();
		$actiontext = changePermissions($docroot);
	}
	else
	{
		$actiontext  = "Insufficient access.";
	}
}


function pldplugin_admin_add_page()
{
	add_options_page('WPReadOnly Options', 'WPReadOnly Options', 'manage_options', 'WPReadOnly', 'WPReadOnly_options_page');
}

function changePermissions($docroot)
{
	// Process lock/unlock here
	if (isset($_POST['lockmode']) && check_admin_referer(plugin_basename( __FILE__ ), "wpreadonlynonce") )
	{
		// Quick sanity check to make sure what is being passed to find is actually a directory
		if (!is_dir($docroot))
			die("Error :|");

		if (!checkRequirements())
			die("Error: requirements checked failed; missing a required component.");

		if ($_POST['lockmode'] == "lock")
		{
			exec("/usr/bin/find $docroot -type d -exec /bin/chmod 555 \{\} \\;", $res, $ret);
			exec("/usr/bin/find $docroot -type f -exec /bin/chmod 444 \{\} \\;", $res, $ret2);

			// exec() may fail and return NULL (at least, under HHVM if it runs out of memory
			if ($ret === NULL || $ret2 === NULL)
				die("ERROR: couldn't exec()");
			else
				return "LOCKED ($docroot)";

		}
		else if ($_POST['lockmode'] == "unlock")
		{
			exec("/usr/bin/find $docroot -type d -exec /bin/chmod 755 \{\} \\;", $res, $ret);
			exec("/usr/bin/find $docroot -type f -exec /bin/chmod 644 \{\} \\;", $res, $ret2);

			// exec() may fail and return NULL (at least, under HHVM if it runs out of memory
			if ($ret === NULL || $ret2 === NULL)
				die("ERROR: couldn't exec()");
			else
				return "UNLOCKED ($docroot)";
		}
	}
}

function WPReadOnly_options_page()
{
	$docroot = $_SERVER['DOCUMENT_ROOT'];
	$wpcontentdir = $docroot."/"."wp-content";

	?>
	<h1>WPReadOnly</h1>
	<p></p>

	<?php
	print "<b>Document root:</b> $docroot<br />";

	global $actiontext;
	if (isset($actiontext) && $actiontext != "")
		print $actiontext;

	if (!checkRequirements())
	{
		print "<b>Error: missing required files, aborting.</b>";
		return;
	}

	if (wp_is_writable($docroot))
	{
		print "<p>$docroot is currently <font color='red'><b>writeable</b></font>.</p>";
	}
	else
	{
		print "<p>$docroot is currently <b>not writeable</b>.</p>";
	}

	if (wp_is_writable($wpcontentdir))
	{
		print "<p>$wpcontentdir is currently <font color='red'><b>writeable</b></font>.</p>";
	}
	else
	{
		print "<p>$wpcontentdir is currently <b>not writeable</b>.</p>";
	}

?>
	<div>
	<h2>Set permissions</h2>

	<p><b>Warning!</b> This plugin is intended for advanced users who understand how Linux filesystem permissions affect WordPress.</p>

	<p>This will recursively change the permissions on your <i>entire</i> document root directory (<?= $docroot ?>). 
	It will completely overwrite any current permissions settings that are in place.</p>

	<p>Note that in read-only mode, some WordPress functionality will stop working (e.g., updates, theme editing, installing plugins. Any functionality that requires writing to the disk will fail.</p>

	<form action="options-general.php?page=WPReadOnly" method="post">

	<select name="lockmode">
		<option value="lock"   <?= (wp_is_writable($wpcontentdir)) ? "selected" : ""  ?>>Set read-only mode</option>
		<option value="unlock" <?= (!wp_is_writable($wpcontentdir)) ? "selected" : ""  ?>>Allow writing</option>
	</select>

	<?php wp_nonce_field( plugin_basename( __FILE__ ), 'wpreadonlynonce' ); ?>
	<input type="submit" value="Change">

	</form></div>

<?php
}

function WPReadOnly_adminbar_link($wp_admin_bar)
{
	$docroot = $_SERVER['DOCUMENT_ROOT'];
	$wpcontentdir = $docroot."/"."wp-content";

	if (wp_is_writable($wpcontentdir))
		$text = "<font color='red'>WRITEABLE!</font>";
	else
		$text = "<font color='lightgreen'>Read Only</font>";

	$args = array(
		'id'    => 'wpreadonlystatus',
		'title' => 'Filesystem Status: '.$text,
		'href'  => 'options-general.php?page=WPReadOnly'
	);

	$wp_admin_bar->add_node( $args );
}

function checkRequirements()
{
	$bins = array("/usr/bin/find", "/bin/chmod");

	foreach ($bins as $bin)
	{
		if (!file_exists($bin))
		{
			print "<div class='updated'>Missing required file: $bin</div>";
			return false;
		}
	}

	return true;
}

/**
 * START Settings Link in Plugin List
 * http://bavotasan.com/2009/a-settings-link-for-your-wordpress-plugins/
 */
function WPReadOnly_settings_link($links)
{
	$settings_link = "<a href='options-general.php?page=WPReadOnly'>Settings</a>";
	array_unshift($links, $settings_link);
	return $links;
}

$plugin = plugin_basename(__FILE__);
add_filter("plugin_action_links_$plugin", 'WPReadOnly_settings_link' );

/**
 * END Settings Link in Plugin List
 */



// Bail if running under Windows
if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN')
{
	print "<div class='updated'>";
	_e("<br />WPReadOnly is not supported under Windows!<br /><br />");
	print "</div>";
}
else
{
	add_action('admin_menu', 'pldplugin_admin_add_page');
	add_action('admin_bar_menu', 'WPReadOnly_adminbar_link');
	add_action('admin_init', 'wpreadonly_init');
}
