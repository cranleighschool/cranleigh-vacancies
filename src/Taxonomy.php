<?php


	namespace CranleighSchool\CranleighVacancies;

class Taxonomy {

	private $slug;

	public function __construct( string $slug ) {
		$this->slug = $slug;
	}

	public function register() {
		$labels = [
			'name'                       => _x( 'Job types', 'Taxonomy General Name', 'cranleigh' ),
			'singular_name'              => _x( 'Job Type', 'Taxonomy Singular Name', 'cranleigh' ),
			'menu_name'                  => __( 'Job Types', 'cranleigh' ),
			'all_items'                  => __( 'All Job Types', 'cranleigh' ),
			'parent_item'                => __( 'Parent Job Type', 'cranleigh' ),
			'parent_item_colon'          => __( 'Parent Job Type:', 'cranleigh' ),
			'new_item_name'              => __( 'New Job Type Name', 'cranleigh' ),
			'add_new_item'               => __( 'Add New Job Type', 'cranleigh' ),
			'edit_item'                  => __( 'Edit Job Type', 'cranleigh' ),
			'update_item'                => __( 'Update Job Type', 'cranleigh' ),
			'view_item'                  => __( 'View Job Type', 'cranleigh' ),
			'separate_items_with_commas' => __( 'Separate job types with commas', 'cranleigh' ),
			'add_or_remove_items'        => __( 'Add or remove job types', 'cranleigh' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'cranleigh' ),
			'popular_items'              => __( 'Popular Job Types', 'cranleigh' ),
			'search_items'               => __( 'Search Job Types', 'cranleigh' ),
			'not_found'                  => __( 'Not Found', 'cranleigh' ),
			'no_terms'                   => __( 'No Job Types', 'cranleigh' ),
			'items_list'                 => __( 'Items job types', 'cranleigh' ),
			'items_list_navigation'      => __( 'Items list navigation', 'cranleigh' ),
		];

		$args = [
			'labels'            => $labels,
			'hierarchical'      => true,
			'public'            => true,
			'show_ui'           => true,
			'show_admin_column' => true,
			'show_in_nav_menus' => true,
			'show_tagcloud'     => false,
			'rewrite'           => [
				'slug'       => $this->slug,
				'with_front' => false,
			],
			'capabilities'      => [
				'manage_terms' => 'manage_job_type',
				'edit_terms'   => 'edit_job_type',
				'delete_terms' => 'delete_job_type',
				'assign_terms' => 'assign_job_type',
			],
		];
		register_taxonomy( 'vacancytypes', [ Configuration::POST_TYPE_KEY ], $args );
		$this->default_vacancy_types();
	}

	private function default_vacancy_types() {
		$categories = [
			'academic'  => 'Academic',
			'bursarial' => 'Bursarial',
			'music-vmt' => 'Music (VMT)',
		];

		foreach ( $categories as $tax => $name ) :
			if ( ! term_exists( $tax, Configuration::TAXONOMY_NAME ) ) :
				wp_insert_term( $name[ $tax ], Configuration::TAXONOMY_NAME, [ 'slug' => $tax ] );
				endif;
			endforeach;
	}

}
