<?php

/**
 * WordPress settings API demo class
 *
 * @author Tareq Hasan
 */
if ( !class_exists('CranleighVacanciesSettings' ) ):
class CranleighVacanciesSettings {

    private $settings_api;

    function __construct() {
        $this->settings_api = new WeDevs_Settings_API;

        add_action( 'admin_init', array($this, 'admin_init') );
        add_action( 'admin_menu', array($this, 'admin_menu') );
    }

    function admin_init() {

        //set the settings
        $this->settings_api->set_sections( $this->get_settings_sections() );
        $this->settings_api->set_fields( $this->get_settings_fields() );

        //initialize settings
        $this->settings_api->admin_init();
    }

    function admin_menu() {
        //add_options_page( 'Settings API', 'Settings API', 'delete_posts', 'settings_api_test', array($this, 'plugin_page') );
		add_submenu_page('edit.php?post_type=vacancy','Vacancy Config','Config','manage_options','vacancy-config',array($this,'plugin_page'));

    }

    function get_settings_sections() {
        $sections = array(
            array(
                'id' => 'vacancy_settings',
                'title' => __( 'Vacancy Settings', 'wedevs' )
            ),
        );
        return $sections;
    }

    /**
     * Returns all the settings fields
     *
     * @return array settings fields
     */
	function get_settings_fields() {
		$settings_fields = array(
			'vacancy_settings' => array(
				array(
					'name'  => 'safeguarding_footer',
					'label' => __( 'Safeguarding Footer Text', 'wedevs' ),
					'desc'  => __( 'The footer text about Safeguarding?', 'wedevs' ),
					'type'  => 'textarea'
				),
            ),
        );

        return $settings_fields;
    }

    function plugin_page() {
        echo '<div class="wrap">';

        $this->settings_api->show_navigation();
        $this->settings_api->show_forms();

        echo '</div>';
    }

    /**
     * Get all the pages
     *
     * @return array page names with key value pairs
     */
    function get_pages() {
        $pages = get_pages();
        $pages_options = array();
        if ( $pages ) {
            foreach ($pages as $page) {
                $pages_options[$page->ID] = $page->post_title;
            }
        }

        return $pages_options;
    }

}
$settings_api = new CranleighVacanciesSettings();
endif;