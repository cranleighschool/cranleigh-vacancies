<?php
/*
Plugin Name: Cranleigh School Vacancies
Description: A Wordpress plugin which creates the custom post type to hold job listings on the website
Author: Fred Bradley (frb@cranleigh.org)
Version: 1.0
Author URI: http://fred.im
*/

require_once(dirname(__FILE__).'/class-tgm-plugin-activation.php');
require_once(dirname(__FILE__).'/settingsapiwrapper.php');
require_once(dirname(__FILE__).'/settings.php');

class Cran_Jobs {

	public $post_type_key = "vacancy";

	function __construct(){
		add_action('init', array($this, 'register_taxonomy'));
		add_action('init', array($this, 'register_post_type'));
		add_filter('rwmb_meta_boxes', array($this,'meta_boxes') );
	//	add_action('admin_menu',array(&$this,'create_settings_menu')); 
		
		register_activation_hook(__FILE__, array($this, 'activate'));
		add_filter( 'enter_title_here', array($this,'title_text_input'));
	
	}
	
	function activate() {
		$this->insert_default_terms();
	}
	
	function title_text_input( $title ){
		
		if (get_post_type()==$this->post_type_key) {
			return $title = 'Job Title';
		}
		
		return $title;
	}
	
	function insert_default_terms() {
		$this->register_taxonomy();
		
		$terms = array(
			"Academic",
			"Bursarial",
			"Music (VMT)",
		);
		
		foreach ($terms as $term):
			$test[] = wp_insert_term($term, "vacancy-types");
		endforeach;
	}
	
	// Register Custom Taxonomy
	function register_taxonomy() {
	
		$labels = array(
			'name'                       => _x( 'Job Types', 'Taxonomy General Name', 'text_domain' ),
			'singular_name'              => _x( 'Job Type', 'Taxonomy Singular Name', 'text_domain' ),
			'menu_name'                  => __( 'Job Types', 'text_domain' ),
			'parent_item'                => __( 'Parent Job Type', 'text_domain' ),
			'parent_item_colon'          => __( 'Parent Job Type:', 'text_domain' ),
			'new_item_name'              => __( 'New Job Type', 'text_domain' ),
			'add_new_item'               => __( 'Add New Job Type', 'text_domain' ),
			'edit_item'                  => __( 'Edit Job Type', 'text_domain' ),
			'update_item'                => __( 'Update Job Type', 'text_domain' ),
			'view_item'                  => __( 'View Job Type', 'text_domain' ),
			'separate_items_with_commas' => __( 'Separate items with commas', 'text_domain' ),
			'add_or_remove_items'        => __( 'Add or remove job types', 'text_domain' ),
			'choose_from_most_used'      => __( 'Choose from the most used', 'text_domain' ),
			'popular_items'              => __( 'Popular Job Types', 'text_domain' ),
			'search_items'               => __( 'Search Job Types', 'text_domain' ),
			'not_found'                  => __( 'Not Found', 'text_domain' ),
			'no_terms'                   => __( 'No items', 'text_domain' ),
			'items_list'                 => __( 'Items list', 'text_domain' ),
			'items_list_navigation'      => __( 'Items list navigation', 'text_domain' ),
		);
		$args = array(
			'labels'                     => $labels,
			'hierarchical'               => true,
			'public'                     => true,
			'show_ui'                    => true,
			'show_admin_column'          => true,
			'show_in_nav_menus'          => false,
			'show_tagcloud'              => false,
			"rewrite" 					 => array(
				"slug"=>"vacancies",
				"with_front"=>false
			),
		);
		register_taxonomy( 'vacancy-types', array( 'vacancy' ), $args );
	
	}
	// Register Custom Post Type
	function register_post_type() {
	
		$labels = array(
			'name'                  => _x( 'Vacancies', 'Post Type General Name', 'text_domain' ),
			'singular_name'         => _x( 'Vacancy', 'Post Type Singular Name', 'text_domain' ),
			'menu_name'             => __( 'Job Vacancies', 'text_domain' ),
			'name_admin_bar'        => __( 'Vacancies', 'text_domain' ),
			'archives'              => __( 'Vacancies', 'text_domain' ),
			'parent_item_colon'     => __( 'Parent Item:', 'text_domain' ),
			'all_items'             => __( 'All Vacancies', 'text_domain' ),
			'add_new_item'          => __( 'Add New Vacancy', 'text_domain' ),
			'add_new'               => __( 'Add New', 'text_domain' ),
			'new_item'              => __( 'New Vacancy', 'text_domain' ),
			'edit_item'             => __( 'Edit Vacancy', 'text_domain' ),
			'update_item'           => __( 'Update Vacancy', 'text_domain' ),
			'view_item'             => __( 'View Vacancy', 'text_domain' ),
			'search_items'          => __( 'Search Vacancy', 'text_domain' ),
			'not_found'             => __( 'Not found', 'text_domain' ),
			'not_found_in_trash'    => __( 'Not found in Trash', 'text_domain' ),
			'featured_image'        => __( 'Featured Image', 'text_domain' ),
			'set_featured_image'    => __( 'Select Profile Picture', 'text_domain' ),
			'remove_featured_image' => __( 'Remove Profile Picture', 'text_domain' ),
			'use_featured_image'    => __( 'Use as Profile Picture', 'text_domain' ),
			'insert_into_item'      => __( 'Insert into item', 'text_domain' ),
			'uploaded_to_this_item' => __( 'Uploaded to this item', 'text_domain' ),
			'items_list'            => __( 'Vacancies list', 'text_domain' ),
			'items_list_navigation' => __( 'Vacancies list navigation', 'text_domain' ),
			'filter_items_list'     => __( 'Filter people list', 'text_domain' ),
		);
		$args = array(
			'label'                 => __( 'Vacancies', 'text_domain' ),
			'description'           => __( 'A List of People that are mentioned on the website', 'text_domain' ),
			'labels'                => $labels,
			'supports'              => array( 'title', 'editor'),
			'taxonomies'            => array(),
			'hierarchical'          => false,
			'public'                => true,
			'show_ui'               => true,
			'show_in_menu'          => true,
			'menu_position'         => 5,
			'menu_icon'             => 'dashicons-lightbulb',
			'show_in_admin_bar'     => true,
			'show_in_nav_menus'     => true,
			'can_export'            => true,
			'has_archive'           => true,			
			'exclude_from_search'   => false,
			'publicly_queryable'    => true,
			'capability_type'       => 'page',
		);
		register_post_type( $this->post_type_key, $args );
	
	}


	function meta_boxes( array $meta_boxes ){
		$prefix = '_vacancies_';

		$meta_boxes[] = array(
			'id'         => 'jobpdf_metabox',
			'title'      => 'Job Description PDF',
			'pages'      => array( 'vacancy'),
			'context'    => 'side',
			'priority'   => 'high',
			'show_names' => true,
			'fields' => array(
				array(
					'name' => 'Closing Date',
					'desc' => 'The Closing Date',
					'id' => $prefix . 'jobdesc_date',
					'type' => 'date',
				),
				array(
					'name' => 'PDF Download',
					'desc' => 'Upload the Job Description PDF here',
					'id' => $prefix . 'jobdesc_pdf',
					'type' => 'file',
				),
			)

        );
        
        return $meta_boxes;
	}

	function create_settings_menu(){
                add_submenu_page('edit.php?post_type=vacancy','Vacancy Config','Config','manage_options','vacancy-config',array(&$this,'settings_page'));
                //reg the setting
                add_action('admin_init',array(&$this,'admin_init'));

    }
	function settings_page(){
		include('settings.php');
	}
	function admin_init(){
		 register_setting( 'cran_job_group', 'cran_jobs_blurb' );
	}
	
	/**
	 * Register the required plugins for this theme.
	 *
	 * In this example, we register five plugins:
	 * - one included with the TGMPA library
	 * - two from an external source, one from an arbitrary source, one from a GitHub repository
	 * - two from the .org repo, where one demonstrates the use of the `is_callable` argument
	 *
	 * The variables passed to the `tgmpa()` function should be:
	 * - an array of plugin arrays;
	 * - optionally a configuration array.
	 * If you are not changing anything in the configuration array, you can remove the array and remove the
	 * variable from the function call: `tgmpa( $plugins );`.
	 * In that case, the TGMPA default settings will be used.
	 *
	 * This function is hooked into `tgmpa_register`, which is fired on the WP `init` action on priority 10.
	 */
	function cs__register_required_plugins() {
		/*
		 * Array of plugin arrays. Required keys are name and slug.
		 * If the source is NOT from the .org repo, then source is also required.
		 */
		$plugins = array(

			array(
				'name'      => 'Meta Box',
				'slug'      => 'meta-box',
				'required'  => true,
			),
	
		);
	
		/*
		 * Array of configuration settings. Amend each line as needed.
		 *
		 * TGMPA will start providing localized text strings soon. If you already have translations of our standard
		 * strings available, please help us make TGMPA even better by giving us access to these translations or by
		 * sending in a pull-request with .po file(s) with the translations.
		 *
		 * Only uncomment the strings in the config array if you want to customize the strings.
		 */
		$config = array(
			'id'           => 'cranleigh',				// Unique ID for hashing notices for multiple instances of TGMPA.
			'default_path' => '',						// Default absolute path to bundled plugins.
			'menu'         => 'tgmpa-install-plugins',	// Menu slug.
			'parent_slug'  => 'plugins.php',            // Parent menu slug.
			'capability'   => 'manage_options',			// Capability needed to view plugin install page, should be a capability associated with the parent menu used.
			'has_notices'  => true,						// Show admin notices or not.
			'dismissable'  => true,						// If false, a user cannot dismiss the nag message.
			'dismiss_msg'  => '',						// If 'dismissable' is false, this message will be output at top of nag.
			'is_automatic' => false,					// Automatically activate plugins after installation or not.
			'message'      => '',						// Message to output right before the plugins table.
	
		);
	
		tgmpa( $plugins, $config );
	}
  
} // End Class

$Cran_Jobs = new Cran_Jobs();
