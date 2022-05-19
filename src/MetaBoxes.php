<?php


	namespace CranleighSchool\CranleighVacancies;

class MetaBoxes {

	public function __construct() {
		 add_filter( 'rwmb_meta_boxes', [ $this, 'meta_boxes' ] );
	}

	/**
	 * meta_boxes function.
	 * Uses the 'rwmb_meta_boxes' filter to add custom meta boxes to our custom post type.
	 * Requires the plugin "meta-box"
	 *
	 * @access public
	 *
	 * @param array $meta_boxes
	 *
	 * @return void
	 */
	public function meta_boxes( $meta_boxes ) {
		$prefix       = 'vacancy_';
		$meta_boxes[] = [
			'id'         => 'Vacancy_meta',
			'title'      => 'Vacancy Data',
			'post_types' => [ Configuration::POST_TYPE_KEY ],
			'context'    => 'normal',
			'priority'   => 'high',
			'autosave'   => true,
			'fields'     => [
				[
					'name'             => __( 'Job Description PDF', 'cranleigh' ),
					'id'               => "{$prefix}pdf",
					'type'             => 'file_advanced',
					'desc'             => 'Upload the Job Description PDF',
					'max_file_uploads' => 1,
				],
				[
					'name'       => __( 'Closing Date', 'cranleigh' ),
					'desc'       => 'The Closing Date of the job',
					'id'         => "{$prefix}expiry",
					'type'       => 'datetime',
					// jQuery datetime picker options. See here http://trentrichardson.com/examples/timepicker/
					'js_options' => [
						'stepMinute'     => 15,
						'showTimepicker' => true,
					],
				],
				[
					'name' => __ ('Hide Application Forms', 'cranleigh'),
					'desc' => 'Hide the application forms in the downloads section for the odd times you might need to do that',
					'id' => "{$prefix}hide_forms",
					'type' => 'select',
					'std' => 'show',
					'options' => [
						'hide' => 'Yes, hide them',
						'show' => 'No, show them'
					]
				]
			],
			'validation' => [
				'rules' => [
					"{$prefix}expiry" => [
						'required' => true,
					],
				],
				// optional override of default jquery.validate messages

			],
		];

		return $meta_boxes;
	}

}
