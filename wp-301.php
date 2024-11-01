<?php
/*
Plugin Name: WP-301
Plugin URI: http://www.littlebizzy.com/plugins/wp-301/
Description: Easily 301 redirect any page or URL to a new location.
Version: 1.1
Author: Little Bizzy
Author URI: http://www.littlebizzy.com/
*/

/*  Copyright 2012 Little Bizzy (email : info@littlebizzy.com)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if (!class_exists("wp301")) {
	class wp301 {
		function create_menu()
		{
		  add_options_page('WP-301', 'WP-301', 10, 'wp301', array($this,'options_page'));
		}
		function options_page()
		{
		?>
		<div class="wrap">
		<h2>WP-301 (Configuration)</h2>
		<br />
		<form method="post" action="options-general.php?page=wp301">
		<table>
			<tr>
				<td><small>example: /about.html</small></td>
				<td><small>example: <?php echo get_option('home'); ?>/about/</small></td>
			</tr>
			<?php echo $this->expand_redirects(); ?>
			<tr>
				<td><input type="text" name="301_redirects[request][]" value="" style="width:300px;" />&nbsp;&nbsp;&raquo;&nbsp;&nbsp;</td>
				<td><input type="text" name="301_redirects[destination][]" value="" style="width:400px;" /></td>
			</tr>
		</table>
		<p><em>Notice: disabling or removing this plugin will immediately cancel all of your saved 301 redirects!</em></p>
		<p><em>After many requests, we are now accepting PayPal donations, thank you very much! <a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TY992RC4F3GQS" target="_blank">Donate Here</a></em></p>
		<p><em>If you have a minute, please <a href="http://wordpress.org/extend/plugins/wp-301/" target="_blank">rate this plugin</a> on WordPress.org... thanks!</em></p>
		<p class="submit"><input type="submit" name="submit_301" class="button-primary" value="Save Redirects" /></p>
		</form>
<br />
<p>Check out some of these great Little Bizzy projects:</p>
<p><ul><li>&bull;&nbsp;<a href="http://www.littlebizzy.com/themes/tube/" target="_blank">FREE WordPress Tube Theme</a> (build your own video tube)</li>
<li>&bull;&nbsp;<a href="http://www.littlebizzy.com/plugins/wp-protect/" target="_blank">WP-Protect</a> (WordPress plugin that disables right clicks and more)</li>
<li>&bull;&nbsp;<a href="http://filepig.org/" target="_blank">FilePig</a> (best way to discover useful software)</li>
<li>&bull;&nbsp;<a href="http://reviews.collegetimes.us/" target="_blank">CollegeTimes</a> (does your college suck? tell the world)</li>
<li>&bull;&nbsp;<a href="http://www.juju.cc/" target="_blank">JuJu</a> (free penpals and international dating)</li>
<li>&bull;&nbsp;<a href="http://www.ratebin.com/" target="_blank">RateBin</a> (ratings for webmasters - web hosts, registrars, and more)</li>
<li>&bull;&nbsp;<a href="http://www.urlworth.com/" target="_blank">URLWorth</a> (check the value and statistics of your website)</li></ul></p>
		</div>
		<?php
		} // end of function options_page
		function expand_redirects(){
			$redirects = get_option('301_redirects');
			$output = '';
			if (!empty($redirects)) {
				foreach ($redirects as $request => $destination) {
					$output .= '
					
					<tr>
						<td><input type="text" name="301_redirects[request][]" value="'.$request.'" style="width:300px;" />&nbsp;&nbsp;&raquo;&nbsp;&nbsp;</td>
						<td><input type="text" name="301_redirects[destination][]" value="'.$destination.'" style="width:400px;" /></td>
					</tr>
					
					';
				}
			} // end if
			return $output;
		}
		function save_redirects($data)
		{
			$redirects = array();
			
			for($i = 0; $i < sizeof($data['request']); ++$i) {
				$request = trim($data['request'][$i]);
				$destination = trim($data['destination'][$i]);
			
				if ($request == '' && $destination == '') { continue; }
				else { $redirects[$request] = $destination; }
			}

			update_option('301_redirects', $redirects);
		}
		function redirect()
		{
			// this is what the user asked for (strip out home portion, case insensitive)
			$userrequest = str_ireplace(get_option('home'),'',$this->getAddress());
			$userrequest = rtrim($userrequest,'/');
			
			$redirects = get_option('301_redirects');
			if (!empty($redirects)) {
				foreach ($redirects as $storedrequest => $destination) {
					// compare user request to each 301 stored in the db
					if(urldecode($userrequest) == rtrim($storedrequest,'/')) {
						header ('HTTP/1.1 301 Moved Permanently');
						header ('Location: ' . $destination);
						exit();
					}
					else { unset($redirects); }
				}
			}
		} // end function redirect
		function getAddress()
		{
			/*** check for https ***/
			$protocol = $_SERVER['HTTPS'] == 'on' ? 'https' : 'http';
			/*** return the full address ***/
			return $protocol.'://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		}
		
	} // end class wp301
	
} // end check for existance of class

// instantiate
$redirect_plugin = new wp301();

if (isset($redirect_plugin)) {
	// add the redirect action, high priority
	add_action('init', array($redirect_plugin,'redirect'), 1);

	// create the menu
	add_action('admin_menu', array($redirect_plugin,'create_menu'));

	// if submitted, process the data
	if (isset($_POST['submit_301'])) {
		$redirect_plugin->save_redirects($_POST['301_redirects']);
	}
}

// this is here for php4 compatibility
if(!function_exists('str_ireplace')){
  function str_ireplace($search,$replace,$subject){
    $token = chr(1);
    $haystack = strtolower($subject);
    $needle = strtolower($search);
    while (($pos=strpos($haystack,$needle))!==FALSE){
      $subject = substr_replace($subject,$token,$pos,strlen($search));
      $haystack = substr_replace($haystack,$token,$pos,strlen($search));
    }
    $subject = str_replace($token,$replace,$subject);
    return $subject;
  }
}

function wp301_actions( $links, $file ) {
if( $file == 'wp-301/wp-301.php' && function_exists( "admin_url" ) ) {
$settings_link = '<a href="' . admin_url( 'options-general.php?page=wp301' ) . '">' .'Settings' . '</a>';
array_unshift( $links, $settings_link );
}
return $links;
}

add_filter('plugin_action_links', 'wp301_actions', 10, 2);
?>