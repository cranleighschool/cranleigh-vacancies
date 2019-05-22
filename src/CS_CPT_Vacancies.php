<?php
/**
 * Created by PhpStorm.
 * User: fredbradley
 * Date: 2019-05-15
 * Time: 10:13
 */

namespace CranleighSchool\CranleighVacancies;


class CS_CPT_Vacancies {

	public $post_type_key = "vacancy";
	public $expiry_removal = "4 days"; // The amount of time to pass before we should consider deleting expired vacancies.

	function __construct() {

		register_activation_hook( __FILE__, [ $this, "on_activation" ] );
		register_deactivation_hook( __FILE__, [ $this, "deactivate" ] );

		add_action( "init", [ $this, "custom_post_type" ], 0 );
		add_action( "init", [ $this, "vacancy_types_tax" ], 0 );
		add_action( "init", [ $this, "default_vacancy_types" ] );
		add_action( "manage_vacancy_posts_custom_column", [ $this, "manage_columns" ], 10, 2 );
		add_action( "edit_form_after_title", [ $this, "edit_form_after_title" ] );
		add_action( "cranleigh_vacancies_daily_event", [ $this, "clear_expired_notices" ] );

		add_filter( "manage_edit-vacancy_columns", [ $this, "edit_columns" ] );
		add_filter( "rwmb_meta_boxes", [ $this, "meta_boxes" ] );
		add_filter( "the_content", [ $this, "content_filter" ] );
		add_filter( "views_edit-" . $this->post_type_key, [ $this, "next_removal_notice" ] );

		add_shortcode( "vacancy-list", [ $this, "shortcode" ] );
	}

	function edit_form_after_title() {

		if ( get_post_type() == $this->post_type_key ) {
			echo "<p>Please <span style='color:red;'>don't use CAPITALS in the title</span>. Just use normal uppercase for first letters only!</p>";
		}
	}

	function edit_columns( $columns ) {

		$columns[ 'expiry' ] = "Expiry";
		$columns[ 'photo' ]  = "Photo";
		unset( $columns[ 'wpseo-score' ] );
		unset( $columns[ 'wpseo-score-readability' ] );
		unset( $columns[ 'wpseo-title' ] );
		unset( $columns[ 'wpseo-metadesc' ] );
		unset( $columns[ 'wpseo-focuskw' ] );

		return $columns;
	}

	function next_removal_notice( $views ) {

		$timestamp = wp_next_scheduled( 'cranleigh_vacancies_daily_event' );

		echo "<p><em>The expired vacancy remove script will next run in: <strong>" . human_time_diff( time(),
				$timestamp ) . "</strong>. This will auto remove expired vacancies that have been expired for " . $this->expiry_removal . " or more.</em></p>";

		return $views;
	}

	function manage_columns( $column, $post_id ) {

		global $post;
		switch ( $column ) {
			case 'expiry':

				$expiry      = get_post_meta( $post_id, 'vacancy_expiry', true );
				$expiry_date = date( "jS F Y", strtotime( $expiry ) );
				$expiry_time = date( "G:ia", strtotime( $expiry ) );

				printf( __( '%s at %s' ), $expiry_date, $expiry_time );
				echo '</span>';
				break;
			case 'photo':
				if ( has_post_thumbnail() ):
					the_post_thumbnail( 'thumb' );
				else:
					echo '<span style="color:red">Not Set, please set one!</span>';
				endif;

				break;
		}
	}

	function deactivate() {

		wp_clear_scheduled_hook( 'cranleigh_vacancies_daily_event' );
	}

	function on_activation() {

		$timestamp = wp_next_scheduled( 'cranleigh_vacancies_daily_event' );

		if ( $timestamp === false ) {
			$timestamp = time() + 300; // Now plus 5 mins
			wp_schedule_event( $timestamp, 'daily', 'cranleigh_vacancies_daily_event' );
		}

	}

	function content_filter( $content ) {

		global $post;

		if ( is_admin() ) {
			// Don't process if you are on the wp-admin side of the road.
			return;
		}

		if ( $post->post_type == $this->post_type_key && is_single() ) {

			$vacancy_expiry = wp_strtotime( get_post_meta( get_the_ID(), 'vacancy_expiry', true ) );

			if ( $vacancy_expiry < time() ) {
				$date    = date( get_option( 'date_format' ), $vacancy_expiry );
				$content = "<p class=\"text-danger\"><strong>The deadline for this vacancy passed on " . $date . ".</strong></p>" . $content;
			} else {
				$extra          = "<hr /><p class=\"\"><i class=\"fa fa-fw fa-clock-o\"></i><strong>Closing Date: " . date( get_option( 'date_format' ),
						$vacancy_expiry ) . ".</strong></p>";
				$vacancy_pdf_id = get_post_meta( get_the_ID(), 'vacancy_pdf', true );
				if ( $vacancy_pdf_id ):
					$extra    .= "<p class=\"\"><i class=\"fa fa-fw fa-download\"></i> <a href=\"" . wp_get_attachment_url( $vacancy_pdf_id ) . "\">Job Description (PDF)</a></p>";
					$taxonomy = wp_get_post_terms( get_the_ID(), 'vacancytypes' )[ 0 ];

					$download_args = [
						"post_type"      => 'dlm_download',
						"posts_per_page" => - 1,
						"tax_query"      => [
							[
								"taxonomy"         => 'dlm_download_category',
								"field"            => "slug",
								"terms"            => [ 'vacancies', $taxonomy->slug ],
								"include_children" => false
							],
						]
					];
					$downloads     = new WP_Query( $download_args );
					while ( $downloads->have_posts() ): $downloads->the_post();
						$extra .= "<p><i class=\"fa fa-fw fa-download\"></i> <a href=\"/download/" . $post->post_name . "\">" . get_the_title() . "</a></p>";
					endwhile;
					wp_reset_postdata();
				endif;
				$safeguarding = "<p class=\"text-muted well-sm well\"><small><em>Cranleigh School is committed to the protection & safety of its pupils. The successful application will be subject to a <a href=\"https://www.gov.uk/disclosure-barring-service-check/overview\" target=\"_blank\">DBS check</a>.</em></small></p>";
				$content      = $content . $extra . $safeguarding;
			}
		}

		return $content;
	}

	function shortcode( $atts, $content = null ) {

		$atts = shortcode_atts(
			[ 'type' => null ]
			, $atts );

		$post_args = [
			"posts_per_page" => - 1,
			"post_type"      => $this->post_type_key,
			"meta_query"     => [
				[
					"key"     => "vacancy_expiry",
					"value"   => date( 'Y-m-d G:i' ),
					"compare" => ">",
					"type"    => "DATETIME"
				]
			]
		];
		if ( $atts[ 'type' ] !== null ) {
			$post_args[ 'tax_query' ] = [
				[
					'taxonomy' => 'vacancytypes',
					'field'    => 'slug',
					'terms'    => $atts[ 'type' ]
				]
			];
			$type                     = get_term_by( 'slug', $atts[ 'type' ], 'vacancytypes' );
		} else {
			$type = null;
		}

		$new_query = new WP_Query( $post_args );
		echo '<h3>';
		if ( isset( $type->name ) ) {
			echo $type->name . " ";
		}
		echo 'Vacancies</h3>';
		if ( $new_query->have_posts() ):
			while ( $new_query->have_posts() ): $new_query->the_post();
				get_template_part( 'template-parts/archive/content', 'vacancy' );
			endwhile;
			wp_reset_postdata();
			wp_reset_query();
		else:
			?>
			<p class="text-danger">No current vacancies. <br />
				<small>(Last Updated: <?php echo date( 'Y-m-d H:i' ); ?>)</small>
			</p>
		<?php
		endif;
	}

	function default_vacancy_types() {

		$taxs                = [ "academic", "bursarial", "music-vmt" ];
		$name[ 'academic' ]  = "Academic";
		$name[ 'bursarial' ] = "Bursarial";
		$name[ 'music-vmt' ] = "Music (VMT)";
		foreach ( $taxs as $tax ):
			if ( ! term_exists( $tax, "vacancytypes" ) ):
				wp_insert_term( $name[ $tax ], "vacancytypes", [ "slug" => $tax ] );
			endif;
		endforeach;
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
	function meta_boxes( $meta_boxes ) {

		$prefix       = "vacancy_";
		$meta_boxes[] = [
			"id"         => "Vacancy_meta",
			"title"      => "Vacancy Data",
			"post_types" => [ $this->post_type_key ],
			"context"    => "normal",
			"priority"   => "high",
			"autosave"   => true,
			"fields"     => [
				[
					"name"             => __( "Job Description PDF", "cranleigh" ),
					"id"               => "{$prefix}pdf",
					"type"             => "file_advanced",
					"desc"             => "Upload the Job Description PDF",
					"max_file_uploads" => 1
				],
				[
					'name'       => __( 'Closing Date', 'cranleigh' ),
					'desc'       => "The Closing Date of the job",
					'id'         => "{$prefix}expiry",
					'type'       => 'datetime',
					// jQuery datetime picker options. See here http://trentrichardson.com/examples/timepicker/
					'js_options' => [
						'stepMinute'     => 15,
						'showTimepicker' => true,
					],
				],
			],
			'validation' => [
				'rules' => [

					"{$prefix}expiry" => [
						"required" => true
					]
				],
				// optional override of default jquery.validate messages

			],
		];

		return $meta_boxes;
	}

	// Register Custom Vacancy
	function custom_post_type() {

		$cpt = new CustomPostType( $this->post_type_key );
		$cpt->register();


	}

	// Register Custom Taxonomy
	function vacancy_types_tax() {

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
				"slug"       => "welcome/work-at-cranleigh/vacancies-by-type",
				"with_front" => false
			],
			'capabilities'      => [
				'manage_terms' => 'manage_job_type',
				'edit_terms'   => 'edit_job_type',
				'delete_terms' => 'delete_job_type',
				'assign_terms' => 'assign_job_type',
			]
		];
		register_taxonomy( 'vacancytypes', [ 'vacancy' ], $args );

	}


	function clear_expired_notices() {

		require_once ABSPATH . 'sync_scripts/src/Slacker.php';
		if ( ! class_exists( 'FredBradley\CranleighSchool\Slacker' ) ) {
			wp_mail( "support@cranleigh.org", "Warning - Slacker Class not Found",
				"The Slacker Class could not be found for use in the `cranleigh-vacancies` plugin" );

			return false;
		}

		$slacker = new FredBradley\CranleighSchool\Slacker();

		$slacker = $slacker->setUsername( "Vacancy Expiry Bot" );

		$args      = [
			"posts_per_page" => - 1,
			"post_type"      => $this->post_type_key,
			'meta_query'     => [
				[
					'key'     => 'vacancy_expiry',
					'value'   => date( "Y-m-d G:i", strtotime( "-" . $this->expiry_removal ) ),
					'compare' => '<',
					'type'    => 'DATETIME'
				]
			]
		];
		$the_query = new WP_Query( $args );

		if ( $the_query->have_posts() ):

			$post_titles = [];
			while ( $the_query->have_posts() ): $the_query->the_post();
				wp_trash_post( get_the_ID() );
				$post_titles[] = get_the_title();
			endwhile;
			wp_reset_postdata();

			$message = "We have removed " . $the_query->post_count . " expired vacancies:\n\n";

			foreach ( $post_titles as $title ):
				$message .= "\t" . $title . "\n";
				$slacker = $slacker->addAttachmentField( "Removed Vacancy", $title );
			endforeach;

			if ( $the_query->post_count > 0 ):
				$slacker = $slacker->addAttachment( "Removed " . count( $post_titles ) . " vacancies" )->post();
			// Comment out the mail line below, as we are not needing this once the slack notifications are working!
			// wp_mail( "frb@cranleigh.org", "Removed Expired Vacancies", $message);
			else:
				// There aren't any notices that have been expired, so we don't need to do anything else. (No mail if nothing has been done!)
				//wp_mail("frb@cranleigh.org", "No Expired Notices", "If you received this messages you can go and delete this line from the code");
			endif;

		endif;
	}

}

