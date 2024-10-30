<?php

/*
Plugin Name: BookCottages Availability Calendar
Plugin URI: http://availability.bookcottages.com/wordpress-plugin.php
Description: The BookCottages availability calendar allows you to embed availability and rate information directly into your wordpress PAGE or POST. The availability calendar works with many business types such as holiday rentals, bed and breakfast, photography studios, vehicle hire, venue reservations etc. We've included a free demonstration configuration so you can see how our system works, but make sure you create your own FREE account in order to show your own rates and availability.
Version: 1.0
Author: BookCottages.com	
Author URI: http://www.bookcottages.com/
*/

// Set The Path to the plugin
define('AVAILABILITYBOOKCOTTAGESPATH', get_option('siteurl').'/wp-content/plugins/availability-BookCottages');
define('AVAILABILITYBOOKCOTTAGESFILE',  __FILE__ );// Set Installed flags and priveliges
if (!defined('AVAILABILITYBOOKCOTTAGESFILENAME'))  define('AVAILABILITYBOOKCOTTAGESFILENAME',  basename( __FILE__ ) );              // menu-compouser.php
if (!defined('AVAILABILITYBOOKCOTTAGESDIRNAME'))   define('AVAILABILITYBOOKCOTTAGESDIRNAME',  plugin_basename(dirname(__FILE__)) ); // menu-compouser

$availabilitybookcottages_installed = true;

// Define the default iFrame
$htmlCode = '<div id="uub5jjtr"></div><script type="text/javascript" src="http://availability.bookcottages.com/js/calendar.php?pid=1461&did=110"></script><a href="http://www.bookcottages.com/" title="Holiday homes"  id="2jhwefh">Holiday homes</a>';

// Initialize
add_action('init', 'availabilitybookcottages_calendar_init');
add_action('widgets_init', 'widget_init_availabilitybookcottages');
add_filter('the_content','availabilitybookcottages_insert');
add_filter('plugin_action_links', 'plugin_links', 10, 2 );


// Adds Settings link to plugins settings
function plugin_links($links, $file) {
	$this_plugin = plugin_basename(AVAILABILITYBOOKCOTTAGESFILE);
	if ($file == $this_plugin) {
		$settings_link = '<a href="admin.php?page=' . AVAILABILITYBOOKCOTTAGESDIRNAME . '/'. AVAILABILITYBOOKCOTTAGESFILENAME . '">'.__("Settings", 'wpdev-booking').'</a>';
		array_unshift($links, $settings_link);
	}
	return $links;
}



// Insertion Function for POSTs and PAGEs
function availabilitybookcottages_insert($content)
{
	if (preg_match('{AVAILABILITYBOOKCOTTAGES}',$content))
	{
		$content = str_replace('{AVAILABILITYBOOKCOTTAGES}',availabilitybookcottages(),$content);
	}
	return $content;
}



function availabilitybookcottages()
{
	global  $userdata, $table_prefix, $wpdb, $availabilitybookcottages_installed;
	get_currentuserinfo();
	$str='';
	if(!availabilitybookcottages_calendar_installed()) $availabilitybookcottages_installed = availabilitybookcottages_calendar_install();
	if(!$availabilitybookcottages_installed)
	{
		echo "Plugin not installed correctly";
		return;
	}
	$query = "SELECT code FROM ".$table_prefix."availabilitybookcottages_calendar	LIMIT 1";
	$code = $wpdb->get_var( $query );

	if( $code === null )
	{
		$str.= '<h4>You don\'t have availabilitybookcottages Calendar, please set code in Settings menu.</h4>';
	}
	else
	{
		$str.= $code;
	}
	return $str;
}

function widget_init_availabilitybookcottages() {
	if (!function_exists('register_sidebar_widget')) return;
}

function availabilitybookcottages_calendar_init()
{		
 	add_action('admin_menu', 'availabilitybookcottages_calendar_config_page');
}

function availabilitybookcottages_calendar_config_page() 
{
	if ( function_exists('add_submenu_page') )
	{
		add_menu_page('availabilitybookcottages Calendar', 'Availability', 8, __FILE__, 'availabilitybookcottages_calendar_main_page');
	}
}

function availabilitybookcottages_calendar_main_page()
{
	global $userdata, $table_prefix, $wpdb, $availabilitybookcottages_installed, $htmlCode;
    get_currentuserinfo();
    if( !availabilitybookcottages_calendar_installed() )
		$availabilitybookcottages_installed = availabilitybookcottages_calendar_install();
    if( !$availabilitybookcottages_installed )
    {
		echo "PLUGIN NOT CORRECTLY INSTALLED, PLEASE CHECK ALL INSTALL PROCEDURE!";
		return;
	}
	?>
	<div class="wrap">
	<?php
	$valid = true;
	$queryS = "select * from ".$table_prefix."availabilitybookcottages_calendar limit 1";
	$d1 = $wpdb->get_var( $queryS );
	if( $d1 === null )
		{
			$query ="
				INSERT INTO ".$table_prefix."availabilitybookcottages_calendar (code)
				VALUES ('". $htmlCode ."')
			";
			$wpdb->query( $query );
		}
	else
		{
			$query = "SELECT code AS code FROM ".$table_prefix."availabilitybookcottages_calendar	LIMIT 1";
			$htmlCode = $wpdb->get_var( $query );
		}
	if( isset($_POST["set"]) AND $_POST["set"] == "SAVE" )
	{
		if( !availabilitybookcottages_calendar_code( $_POST["code"] ) )
			$valid = false;
		else
		{
			$query ="Update ".$table_prefix."availabilitybookcottages_calendar set code = '".$_POST["code"]."'";
			$wpdb->query( $query );
			$htmlCode = str_replace("\\", "", ($_POST["code"]));
		}
	}
	if( isset( $_GET["ui"]) and $_GET["ui"] == "true" )
	{
		$query = "
			DROP TABLE ".$table_prefix."availabilitybookcottages_calendar
		";
		mysql_query( $query ) or die( mysql_error() );
		delete_option( 'availabilitybookcottages_calendar_privileges' ); //Removing option from database...
		$installed = availabilitybookcottages_calendar_installed();
		if( !$installed ) {
			echo "PLUGIN UNINSTALLED. NOW DE-ACTIVATE PLUGIN.<br />";
			echo " <a href=plugins.php>CLICK HERE</a>";
			return;
			}
		else
		{
			echo "PROBLEMS WITH UNINSTALL FUNCTION.";
		}
	}
	?>
		<div style="margin-bottom:20px;"><h2 style="color: #C65326; font-family: Arial,Sans-Serif,Helvetica;">BookCottages Availability Calendar</h2></div>
		<div style="margin-bottom:20px;">
			<div style="float:left;width:400px;">
				<img src="<?php echo AVAILABILITYBOOKCOTTAGESPATH; ?>/homepage-pic.png" title="Free Availability Calendar"/>
				<h2 style="color: #9D9F0D;font-family: Lucida Sans Unicode,sans-serif;font-size: 1.3em;font-style: normal;font-weight: normal;">Calendar Features</h2>
				<ul>
					<li>It is <span class="highlight">totally free</span>, no hidden charges, zip, zero!</li>
					<li>Takes a couple of minutes to setup.</li>
					<li>Calendar is displayed and embedded  on your website.</li>
					<li>Join Hundreds of properties using our technology.</li>
					<li>Update the availability as many times as you want whenever you want.</li>
					<li>Multiple calendars for multiple properties if required.</li>
					<li>Works on PC, Macs, Mobile Phones, IPhones.</li>
					<li>Optionally show rates as well as availability.</li>
				</ul>
				<hr>
				<strong><span  style="color: #C65326; font-family: Arial,Sans-Serif,Helvetica;">Sign Up for a BookCottages Availability Account</span></strong><br>
				<p style="font-size:10px;">If you don't already have an account you can sign up for free</p>
				<a href ="http://availability.bookcottages.com/signup.php" target="_new">Click here to create a FREE account</a></p>
			</div>	

			<div style="float:left;width:400px;padding-left:20px;" >
			<h3 style="color: #9D9F0D;font-family: Lucida Sans Unicode,sans-serif;font-size: 1.3em;font-style: normal;font-weight: normal;">Steps to Set-up your Availability Calendar</h3>
			<p><b>1. Log in to your Account</b></p>
			<p style="margin:10px;">
				<a style="color: #C65326;" href="http://availability.bookcottages.com/propertyowners/" target="_blank" class="button">Edit Rates and Availability</a>
			</p>
			<p style="font-size:10px;">
				In your property owner area you can:<br>
				 - Set-up your property<br>
				 - Manage Availability and Rates<br>
			</p>
	
			<form action="<?php echo $_SERVER["PHP_SELF"]."?page=".$_GET["page"]; ?>" method="POST">
				<p style="margin-top:20px;margin-bottom:0px;"><b>2. Enter your Availability Calendar Code</b></p>
				<p style="font-size:10px;">
					Once in the property owner area, click on the 'Get Html code' link, copy the code where it say 'Display Calendar on your website' and paste into the box below<br/>
					<i>Remember to press SAVE.</i>
				</p>
				<p>
					<textarea type ="text" name="code" rows="7" cols="60"><?php echo $htmlCode ?></textarea>
				</p>
				<input type="submit" name="set" value="SAVE" />
			</form>
			<br>
			<b>3. Simply add {AVAILABILITYBOOKCOTTAGES}. </b><br />
			<p style="font-size:10px;">All you need to do now to get the availability calendar to display in any POST or PAGE is to just add <b>{AVAILABILITYBOOKCOTTAGES}</b> to your content.</p>	
		</div>
		<br class="clear"/>
	</div>
	<?php
}

function availabilitybookcottages_calendar_code( $code )
{
	$code = str_replace("\\","",$code);
	if( strpos($code, 'script type="text/javascript" src="http://availability.bookcottages.com/js/calendar.php') === FALSE )
		return false;
	else
		return true;
}

function availabilitybookcottages_calendar_installed()
{
	global $table_prefix, $wpdb;
	$query = "SHOW TABLES LIKE '".$table_prefix."availabilitybookcottages_calendar'";
	$install = $wpdb->get_var( $query );
	if( $install === NULL )
		return false;
	else
		return true;
}

function availabilitybookcottages_calendar_install()
{
	global $table_prefix, $wpdb;
	$query = "
		CREATE TABLE ".$table_prefix."availabilitybookcottages_calendar (
			calendar_id INT(11) NOT NULL auto_increment,
			code TEXT NOT NULL,
			PRIMARY KEY( calendar_id )
		)
	";
	$wpdb->query( $query );
	//Using option for availabilitybookcottages calendar plugin!
	add_option( "availabilitybookcottages_calendar_privileges", "2" );
	if( !availabilitybookcottages_calendar_installed() )
		return false;
	else
		return true;
}
?>
