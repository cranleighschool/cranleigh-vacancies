<?php
/*
	Plugin Name: Cranleigh Vacancies
	Plugin URI: http://www.cranleigh.org/vacancies
	Description: Custom Plugin that manages the vacancies section for the Cranleigh School Website.
	Author: Fred Bradley
	Version: 1.3.2
	Author URI: http://fred.im
*/
use CranleighSchool\CranleighVacancies\CS_CPT_Vacancies;
function wp_strtotime($str) {
	// This function behaves a bit like PHP's StrToTime() function, but taking into account the Wordpress site's timezone
	// CAUTION: It will throw an exception when it receives invalid input - please catch it accordingly
	// From https://mediarealm.com.au/

	$tz_string = get_option('timezone_string');
	$tz_offset = get_option('gmt_offset', 0);

	if (!empty($tz_string)) {
		// If site timezone option string exists, use it
		$timezone = $tz_string;

	} elseif ($tz_offset == 0) {
		// get UTC offset, if it isn’t set then return UTC
		$timezone = 'UTC';

	} else {
		$timezone = $tz_offset;

		if(substr($tz_offset, 0, 1) != "-" && substr($tz_offset, 0, 1) != "+" && substr($tz_offset, 0, 1) != "U") {
			$timezone = "+" . $tz_offset;
		}
	}

	$datetime = new DateTime($str, new DateTimeZone($timezone));
	return $datetime->format('U');
}
require_once 'vendor/autoload.php';


new CS_CPT_Vacancies();
