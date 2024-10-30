<?php
/*
Plugin Name: Kwippy post
Plugin URI: http://wordpress.org/#
Description: Auto post your blogs on kwippy.
Version: 1.0
Author: Dipankar Sarkar
Author URI: http://dipankar.name
*/

function kwippypost_install() {
	global $wpdb;
	$table_name = $wpdb->prefix."kwippy";
	if($wpdb->get_var("show tables like '$table_name'") != $table_name) {
	  $sql = "CREATE TABLE " . $table_name . " (
	  id mediumint(9) NOT NULL AUTO_INCREMENT,
	  username VARCHAR(200) NOT NULL,
	  password VARCHAR(200) NOT NULL,
	);";
	}
      require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
      dbDelta($sql);
}
function send_to_kwippy($post_ID) {
	$username = get_option('kwippy_username');
        $password = get_option('kwippy_password');
        $posted = get_post($post_ID);
	$ch = curl_init();    // initialize curl handle
	curl_setopt($ch, CURLOPT_URL,"http://www.kwippy.com/api/kwips/update.xml"); // set url to post to
	curl_setopt($ch, CURLOPT_FAILONERROR, 1);
	curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);// allow redirects
	curl_setopt($ch, CURLOPT_RETURNTRANSFER,1); // return into a variable
	curl_setopt($ch, CURLOPT_TIMEOUT, 3); // times out after 4s
	curl_setopt($ch, CURLOPT_POST, 1); // set POST method
	curl_setopt($ch, CURLOPT_USERPWD, $username.':'.$password);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "kwip=".$posted->post_title.' - '.$posted->guid."&vendor=16"); // add POST fields
	$result = curl_exec($ch); // run the whole process
	curl_close($ch);  
}
function kwippypost_menu() {
  add_options_page('Kwippy Post Options', 'Kwippy Post', 8, __FILE__, 'kwippypost_options');
}

function kwippypost_options() {
echo '<div class="wrap">';
echo '<h2>Kwippy post</h2>';

echo '<form method="post" action="options.php">';
echo wp_nonce_field('update-options');

echo '<table class="form-table">';

echo '<tr valign="top">';
echo '<th scope="row">Kwippy username</th>';
echo '<td><input type="text" name="kwippy_username" value="'.get_option('kwippy_username').'" /></td>';
echo '</tr>';
 
echo '<tr valign="top">';
echo '<th scope="row">Kwippy password</th>';
echo '<td><input type="password" name="kwippy_password" value="'.get_option('kwippy_password').'" /></td>';
echo '</tr>';

echo '</table>';

echo '<input type="hidden" name="action" value="update" />';
echo '<input type="hidden" name="page_options" value="kwippy_username,kwippy_password" />';

echo '<p class="submit">';
echo '<input type="submit" name="Submit" value="'._e('Save Changes').'" />';
echo '</p>';

echo '</form>';
echo '</div>';

}

// Create the tables
// register_activation_hook(__FILE__,'kwippypost_install');
// Create the admin menu
add_action('admin_menu', 'kwippypost_menu');
// Create the publish post action
add_action('publish_post', 'send_to_kwippy');
?>
