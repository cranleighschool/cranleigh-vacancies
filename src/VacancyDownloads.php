<?php


	namespace CranleighSchool\CranleighVacancies;

	use WP_Query;

class VacancyDownloads {

	public $downloads;

	public function __construct( \WP_Post $vacancy = null ) {
		$terms = [ 'vacancies' ];

		if ( $vacancy !== null ) {
			$taxonomy = wp_get_post_terms( $vacancy->ID, Configuration::TAXONOMY_NAME )[0];
			array_push( $terms, $taxonomy->slug );
		}

		$download_args = [
			'post_type'      => 'dlm_download',
			'posts_per_page' => -1,
			'tax_query'      => [
				[
					'taxonomy'         => 'dlm_download_category',
					'field'            => 'slug',
					'terms'            => $terms,
					'include_children' => false,
				],
			],
		];

		$this->downloads = ( new WP_Query( $download_args ) );
		wp_reset_query();
	}

}
