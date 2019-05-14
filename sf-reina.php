<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://reinapatton.com
 * @since             1.0.0
 * @package           Sf_Reina
 *
 * @wordpress-plugin
 * Plugin Name:       SF Reina
 * Plugin URI:        https://reinapatton.com
 * Description:       This is a short description of what the plugin does. It's displayed in the WordPress admin area.
 * Version:           1.0.0
 * Author:            Reina Patton
 * Author URI:        https://reinapatton.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       sf-reina
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'SF_REINA_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-sf-reina-activator.php
 */
function activate_sf_reina() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sf-reina-activator.php';
	Sf_Reina_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-sf-reina-deactivator.php
 */
function deactivate_sf_reina() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-sf-reina-deactivator.php';
	Sf_Reina_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_sf_reina' );
register_deactivation_hook( __FILE__, 'deactivate_sf_reina' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-sf-reina.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_sf_reina() {

	$plugin = new Sf_Reina();
	$plugin->run();

}
run_sf_reina();

function curl_reina_sf($post_ID, $post_after, $post_before) {


if ($post_after->post_type == 'listing') {


	$email = get_userdata($post_after->post_author)->user_email;

	$update['contact']['Phone'] = get_post_meta($post_ID, 'wilcity_phone')[0];
	$update['contact']['MailingStreet'] = get_post_meta($post_ID, 'geolocation_street')[0];
	$update['contact']['MailingCity'] = get_post_meta($post_ID, 'geolocation_city')[0];
	$update['contact']['MailingState'] = get_post_meta($post_ID, 'geolocation_state_short')[0];
	$update['contact']['MailingPostalCode'] = get_post_meta($post_ID, 'geolocation_postcode')[0];

	$update['account']['Name'] = $post_after->post_title;
	$update['account']['Website'] = get_post_meta($post_ID, 'wilcity_website')[0];
	$update['account']['Phone'] = get_post_meta($post_ID, 'wilcity_phone')[0];
	$update['account']['BillingStreet'] = get_post_meta($post_ID, 'geolocation_street_number')[0] . ' ' . get_post_meta($post_ID, 'geolocation_street')[0];
	$update['account']['BillingCity'] = get_post_meta($post_ID, 'geolocation_city')[0];
	$update['account']['BillingState'] = get_post_meta($post_ID, 'geolocation_state_short')[0];
	$update['account']['BillingPostalCode'] = get_post_meta($post_ID, 'geolocation_postcode')[0];

	$update['account']['Email__c'] = $email;


	define("USERNAME", "");
	define("PASSWORD", "");
	define("SECURITY_TOKEN", "");

	require_once ('vendor/uuf6429/force.com-toolkit-for-php/soapclient/SforceEnterpriseClient.php');
	try {
	$mySforceConnection = new SforceEnterpriseClient();
	$mySforceConnection->createConnection(dirname(__FILE__) . "/includes/wsdl--enterprise.xml");
	$mySforceConnection->login(USERNAME, PASSWORD.SECURITY_TOKEN);

	$lead_query = "SELECT Id FROM Lead WHERE Email = '$email'";
	$response_lead = $mySforceConnection->query($lead_query);

	if (!($response_lead->records)) {
		$sfurl = 'https://webto.salesforce.com/servlet/servlet.WebToLead?encoding=UTF-8';

		$sffields = array(
	    'oid' => '00Df4000001QUpX',
	    'lead_source' => 'Website',
	    'last_name' => urlencode(get_userdata($post_after->post_author)->display_name),
	    'company' => urlencode($post_after->post_title),
	    'email' => urlencode(get_userdata($post_after->post_author)->user_email),
	    'Account_Email__c' => urlencode(get_userdata($post_after->post_author)->user_email),
	    'phone' => urlencode(get_post_meta($post_ID, 'wilcity_phone')[0]),
	    'URL' => urlencode(get_post_meta($post_ID, 'wilcity_website')[0]),
	    'street' => urlencode(get_post_meta($post_ID, 'geolocation_street')[0]),
	    'city' => urlencode(get_post_meta($post_ID, 'geolocation_city')[0]),
	    'state' => urlencode(get_post_meta($post_ID, 'geolocation_state_short')[0]),
	    'zip' => urlencode(get_post_meta($post_ID, 'geolocation_postcode')[0]),
	    'country' => urlencode(get_post_meta($post_ID, 'geolocation_country_short')[0]),
	    'phone' => urlencode(get_post_meta($post_ID, 'wilcity_phone')[0]),
	  );

		foreach($sffields as $key=>$value) { $fieldstring .= $key.'='.$value.'&'; }

		// var_dump($sffields);
		// exit();
		rtrim($fieldstring, '&');

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $sfurl);
		curl_setopt($ch, CURLOPT_POST, count($sffields));
		curl_setopt($ch, CURLOPT_POSTFIELDS, $fieldstring);
		curl_setopt($ch, CURLOPT_HEADER, FALSE);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, FALSE);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);

		$result = curl_exec($ch);

		curl_close($ch);
	}


	$query = "SELECT Id, AccountId FROM Contact WHERE Email = '$email'";
	$response = $mySforceConnection->query($query);
	foreach ($response->records as $record) {

		if ($update['contact']) {
			$contacts[0] = new stdclass();
			$contacts[0]->Id = $record->Id;
			foreach ($update['contact'] as $key => $value) {
				$contacts[0]->$key = $value;
			}

			$response_contacts = $mySforceConnection->update($contacts, 'Contact');

		}

		if ($update['account']) {
			$accounts[0] = new stdclass();
			$accounts[0]->Id = $record->AccountId;
			foreach ($update['account'] as $key2 => $value2) {
				$accounts[0]->$key2 = $value2;
			}

			$response_accounts = $mySforceConnection->update($accounts, 'Account');
			// var_dump($response_accounts);
		}
		// exit();

	}
	} catch {
		return true;
	}
	}

	return true;
}
add_action( 'save_post', 'curl_reina_sf', 10, 3 );
