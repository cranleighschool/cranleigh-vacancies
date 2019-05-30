<?php
	/**
	 * Created by PhpStorm.
	 * User: fredbradley
	 * Date: 2019-05-15
	 * Time: 10:13
	 */

	namespace CranleighSchool\CranleighVacancies;

	use CranleighSchool\CranleighVacancies\Shortcodes\VacancyList;
	use WP_Query;

	/**
	 * Class Cranleigh_Vacancies
	 *
	 * @package CranleighSchool\CranleighVacancies
	 */
	class CranleighVacancies
	{

		/**
		 * Cranleigh_Vacancies constructor.
		 */
		function __construct()
		{

			register_activation_hook(__FILE__, [$this, "on_activation"]);
			register_deactivation_hook(__FILE__, [$this, "deactivate"]);

			add_action("init", [$this, "custom_post_type"], 0);
			add_action("init", [$this, "vacancy_types_tax"], 0);
			add_action("cranleigh_vacancies_daily_event", [$this, "clear_expired_notices"]);


			$this->loadShortcodes();
			$this->loadMetaBoxes();
			if (!is_admin()) {
				$content = new FrontendContent();
			} else {
				$content = new BackendContent();
			}
		}


		/**
		 *
		 */
		private function loadShortcodes()
		{
			$classes = [
				VacancyList::class
			];
			foreach ($classes as $class) {
				new $class();
			}
		}

		/**
		 * @return \CranleighSchool\CranleighVacancies\MetaBoxes
		 */
		private function loadMetaBoxes()
		{
			return (new MetaBoxes());
		}


		/**
		 *
		 */
		function deactivate()
		{

			wp_clear_scheduled_hook('cranleigh_vacancies_daily_event');
		}

		/**
		 *
		 */
		function on_activation()
		{

			$timestamp = wp_next_scheduled('cranleigh_vacancies_daily_event');

			if ($timestamp === false) {
				$timestamp = time() + 300; // Now plus 5 mins
				wp_schedule_event($timestamp, 'daily', 'cranleigh_vacancies_daily_event');
			}

		}



		// Register Custom Vacancy

		/**
		 *
		 */
		function custom_post_type()
		{
			$cpt = new CustomPostType(Configuration::POST_TYPE_KEY);
			$cpt->register();
		}

		// Register Custom Taxonomy

		/**
		 *
		 */
		function vacancy_types_tax()
		{
			$tax = new Taxonomy("welcome/work-at-cranleigh/vacancies-by-type");
			$tax->register();
		}


		/**
		 * @return bool
		 */
		function clear_expired_notices()
		{

			require_once ABSPATH . 'sync_scripts/src/Slacker.php';
			if (!class_exists('FredBradley\CranleighSchool\Slacker')) {
				wp_mail("support@cranleigh.org", "Warning - Slacker Class not Found",
					"The Slacker Class could not be found for use in the `cranleigh-vacancies` plugin");

				return false;
			}

			$slacker = new FredBradley\CranleighSchool\Slacker();

			$slacker = $slacker->setUsername("Vacancy Expiry Bot");

			$args = [
				"posts_per_page" => -1,
				"post_type"      => Configuration::POST_TYPE_KEY,
				'meta_query'     => [
					[
						'key'     => 'vacancy_expiry',
						'value'   => date("Y-m-d G:i", strtotime("-" . Configuration::EXPIRY_REMOVAL)),
						'compare' => '<',
						'type'    => 'DATETIME'
					]
				]
			];
			$the_query = new WP_Query($args);

			if ($the_query->have_posts()):

				$post_titles = [];
				while ($the_query->have_posts()): $the_query->the_post();
					wp_trash_post(get_the_ID());
					$post_titles[] = get_the_title();
				endwhile;
				wp_reset_postdata();

				$message = "We have removed " . $the_query->post_count . " expired vacancies:\n\n";

				foreach ($post_titles as $title):
					$message .= "\t" . $title . "\n";
					$slacker = $slacker->addAttachmentField("Removed Vacancy", $title);
				endforeach;

				if ($the_query->post_count > 0):
					$slacker = $slacker->addAttachment("Removed " . count($post_titles) . " vacancies")->post();
				// Comment out the mail line below, as we are not needing this once the slack notifications are working!
				// wp_mail( "frb@cranleigh.org", "Removed Expired Vacancies", $message);
				else:
					// There aren't any notices that have been expired, so we don't need to do anything else. (No mail if nothing has been done!)
					//wp_mail("frb@cranleigh.org", "No Expired Notices", "If you received this messages you can go and delete this line from the code");
				endif;

			endif;
		}

	}

