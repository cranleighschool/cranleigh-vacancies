<?php


	namespace CranleighSchool\CranleighVacancies;


	class BackendContent
	{
		public $admin_notice;
		private $remove_edit_columns = [
			'wpseo-score',
			'wpseo-readability',
			'wpseo-title',
			'wpseo-metadesc',
			'wpseo-focuskw'
		];

		private $add_edit_columns = [
			'Expiry', 'Photo'
		];

		public function __construct()
		{
			add_action("manage_vacancy_posts_custom_column", [$this, "manage_columns"], 10, 2);
			add_action("edit_form_after_title", [$this, "edit_form_after_title"]);

			add_filter("manage_edit-vacancy_columns", [$this, "edit_columns"]);
			add_filter("views_edit-" . Configuration::POST_TYPE_KEY, [$this, "next_removal_notice"]);
			add_action('admin_init', [$this, 'require_dlm_download']);
		}

		/**
		 *
		 */
		function edit_form_after_title()
		{

			if (get_post_type() == Configuration::POST_TYPE_KEY) {
				echo "<p>Please <span style='color:red;'>don't use CAPITALS in the title</span>. Just use normal uppercase for first letters only!</p>";
			}
		}


		/**
		 * @param $columns
		 *
		 * @return mixed
		 */
		function edit_columns($columns)
		{

			foreach ($this->add_edit_columns as $title) {
				$columns[ sanitize_title($title) ] = $title;
			}
			foreach ($this->remove_edit_columns as $column_id) {
				unset($column_id);
			}

			return $columns;
		}

		/**
		 * @param $views
		 *
		 * @return mixed
		 */
		function next_removal_notice($views)
		{

			$timestamp = wp_next_scheduled('cranleigh_vacancies_daily_event');

			echo "<p><em>The expired vacancy remove script will next run in: <strong>" . human_time_diff(time(),
					$timestamp) . "</strong>. This will auto remove expired vacancies that have been expired for " . Configuration::EXPIRY_REMOVAL . " or more.</em></p>";

			return $views;
		}

		/**
		 * @param $column
		 * @param $post_id
		 */
		function manage_columns($column, $post_id)
		{
			switch ($column) {
				case 'expiry':

					$expiry = get_post_meta($post_id, 'vacancy_expiry', true);
					$expiry_date = date("jS F Y", strtotime($expiry));
					$expiry_time = date("G:ia", strtotime($expiry));

					printf(__('%s at %s'), $expiry_date, $expiry_time);
					break;
				case 'photo':
					if (has_post_thumbnail()):
						the_post_thumbnail('thumb');
					else:
						echo '<span style="color:red">Not Set, please set one!</span>';
					endif;
					break;
			}
		}


		/**
		 *
		 */
		public function require_dlm_download()
		{

			if (is_admin() && current_user_can('activate_plugins')) {
				if (!is_plugin_active('download-monitor/download-monitor.php')) {
					// Plugin is not Active
					$this->admin_notice = "To use the Cranleigh Vacancies plugin properly, please activate the Download Monitor
					Plugin, and add some downloads to the 'vacancies' category.";
					add_action('admin_notices', [$this, 'dlm_download_admin_notice']);
				} elseif (Configuration::getVacancyDownloads()->post_count < 1) {
					$this->admin_notice = "There aren't any Vacancy related Downloads in Download Monitor. This doesn't seem right... <a href='post-new.php?post_type=dlm_download'>Add them</a> to the &quot;vacancies&quot; category.";
					add_action('admin_notices', [$this, 'dlm_download_admin_notice']);
				}

			}
		}

		/**
		 *
		 */
		public function dlm_download_admin_notice()
		{
			$class = 'notice notice-warning';
			printf('<div class="%1$s"><p>%2$s</p></div>', esc_attr($class), $this->admin_notice);

		}

	}
