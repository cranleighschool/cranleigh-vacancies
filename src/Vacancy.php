<?php


	namespace CranleighSchool\CranleighVacancies;


	class Vacancy
	{
		public function __construct($id)
		{
			$wp_post = get_post($id);

			$this->import($wp_post);

		}

		private function import(\WP_Post $post = NULL)
		{
			if ($post !== NULL && $post->post_type === Configuration::POST_TYPE_KEY) {
				foreach (get_object_vars($post) as $key => $value) {
					$this->$key = $value;
				}
			} else {
				throw new \Exception("Not a vacancy", 422);
			}
		}

		public static function load($id)
		{
			return new self($id);
		}

		public function _is_expired(): bool
		{
			$vacancy_expiry_timestamp = Configuration::wp_strtotime($this->getMeta('vacancy_expiry'));
			if (Configuration::now() > $vacancy_expiry_timestamp) {
				return true;
			} else {
				return false;
			}
		}

		private function getMeta(string $meta)
		{
			return get_post_meta($this->ID, $meta, true);
		}

		public function expiry_timestamp()
		{
			return Configuration::wp_strtotime(get_post_meta($this->ID, 'vacancy_expiry', true));
		}
	}
