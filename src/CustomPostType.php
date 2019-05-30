<?php
	/**
	 * Created by PhpStorm.
	 * User: fredbradley
	 * Date: 2019-05-15
	 * Time: 10:14
	 */

	namespace CranleighSchool\CranleighVacancies;

	class CustomPostType
	{

		public $post_type_key;
		public $args = [];

		public function __construct(string $post_type_key)
		{

			$this->post_type_key = $post_type_key;
			$this->args = $this->custom_post_type_args();

		}

		public function register()
		{

			register_post_type($this->post_type_key, $this->args);
		}

		private function custom_post_type_args()
		{

			$labels = [
				'name'                  => _x('Vacancies', 'Vacancy General Name', 'cranleigh'),
				'singular_name'         => _x('Vacancy', 'Vacancy Singular Name', 'cranleigh'),
				'menu_name'             => __('Vacancies', 'cranleigh'),
				'name_admin_bar'        => __('Vacancy', 'cranleigh'),
				'archives'              => __('Item Archives', 'cranleigh'),
				'parent_item_colon'     => __('Parent Item:', 'cranleigh'),
				'all_items'             => __('All Vacancies', 'cranleigh'),
				'add_new_item'          => __('Add New Vacancy', 'cranleigh'),
				'add_new'               => __('Add New Vacancy', 'cranleigh'),
				'new_item'              => __('New Vacancy', 'cranleigh'),
				'edit_item'             => __('Edit Vacancy', 'cranleigh'),
				'update_item'           => __('Update Vacancy', 'cranleigh'),
				'view_item'             => __('View Vacancy', 'cranleigh'),
				'search_items'          => __('Search Vacancy', 'cranleigh'),
				'not_found'             => __('Not found', 'cranleigh'),
				'not_found_in_trash'    => __('Not found in Trash', 'cranleigh'),
				'featured_image'        => __('Featured Image', 'cranleigh'),
				'set_featured_image'    => __('Set featured image', 'cranleigh'),
				'remove_featured_image' => __('Remove featured image', 'cranleigh'),
				'use_featured_image'    => __('Use as featured image', 'cranleigh'),
				'insert_into_item'      => __('Insert into item', 'cranleigh'),
				'uploaded_to_this_item' => __('Uploaded to this item', 'cranleigh'),
				'items_list'            => __('Vacancies list', 'cranleigh'),
				'items_list_navigation' => __('Vacancies list navigation', 'cranleigh'),
				'filter_items_list'     => __('Filter Vacancy list', 'cranleigh'),
			];

			$capabilities = [
				'publish_posts'       => 'publish_' . $this->post_type_key,
				'edit_posts'          => 'edit_' . $this->post_type_key,
				'edit_others_posts'   => 'edit_others_' . $this->post_type_key,
				'delete_posts'        => 'delete_' . $this->post_type_key,
				'delete_others_posts' => 'delete_others_' . $this->post_type_key,
				'read_private_posts'  => 'read_private_' . $this->post_type_key,
				'edit_post'           => 'edit_' . $this->post_type_key,
				'delete_post'         => 'delete_' . $this->post_type_key,
				'read_post'           => 'read_' . $this->post_type_key,
			];

			$args = [
				'label'               => __('Vacancy', 'cranleigh'),
				'description'         => __('Cranleigh Vacancies', 'cranleigh'),
				'labels'              => $labels,
				'hierarchical'        => false,
				'public'              => true,
				'show_ui'             => true,
				'show_in_menu'        => true,
				'menu_position'       => 27,
				'show_in_admin_bar'   => true,
				'show_in_nav_menus'   => false,
				'can_export'          => true,
				'has_archive'         => true,
				'exclude_from_search' => false,
				'publicly_queryable'  => true,
				'capabilities'        => $capabilities,

				'rewrite'   => [
					"slug"       => "welcome/work-at-cranleigh/vacancies",
					"with_front" => false
				],
				'query_var' => "vacancy", // This goes to the WP_Query schema
				'supports'  => ['title', 'author', 'editor', 'thumbnail'/*'custom-fields'*/],
				'menu_icon' => "dashicons-carrot",
			];

			return $args;


		}
	}
