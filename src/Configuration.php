<?php


	namespace CranleighSchool\CranleighVacancies;


	use DateTime;
	use DateTimeZone;

	class Configuration
	{
		CONST POST_TYPE_KEY = "vacancy";
		CONST TAXONOMY_NAME = "vacancytypes";
		CONST EXPIRY_REMOVAL = "4 days"; // The amount of time to pass before we should consider deleting expired vacancies.

		public static function now()
		{
			return time();
		}
		public static function getVacancyDownloads(\WP_Post $post = NULL)
		{
			return (new VacancyDownloads($post))->downloads;
		}

		public static function wp_strtotime($str)
		{
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

				if (substr($tz_offset, 0, 1) != "-" && substr($tz_offset, 0, 1) != "+" && substr($tz_offset, 0, 1) != "U") {
					$timezone = "+" . $tz_offset;
				}
			}

			$datetime = new DateTime($str, new DateTimeZone($timezone));

			return $datetime->format('U');
		}
	}
