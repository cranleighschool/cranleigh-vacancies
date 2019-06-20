<?php


	namespace CranleighSchool\CranleighVacancies\Shortcodes;

	use CranleighSchool\CranleighVacancies\Configuration;
	use WP_Query;

class VacancyList extends BaseShortcode {

	protected $tag  = 'vacancy-list';
	protected $atts = [
		'type' => null,
	];

	public function render( $atts, $content = null ) {
		$this->atts = shortcode_atts( $this->atts, $atts );

		$post_args = [
			'posts_per_page' => -1,
			'post_type'      => Configuration::POST_TYPE_KEY,
			'meta_query'     => [
				[
					'key'     => 'vacancy_expiry',
					'value'   => date( 'Y-m-d G:i' ),
					'compare' => '>',
					'type'    => 'DATETIME',
				],
			],
		];
		if ( $this->atts['type'] !== null ) {
			$post_args['tax_query'] = [
				[
					'taxonomy' => 'vacancytypes',
					'field'    => 'slug',
					'terms'    => $this->atts['type'],
				],
			];
			$type                   = get_term_by( 'slug', $this->atts['type'], 'vacancytypes' );
		} else {
			$type = null;
		}

		$new_query = new WP_Query( $post_args );
		echo '<h3>';
		if ( isset( $type->name ) ) {
			echo $type->name . ' ';
		}
		echo 'Vacancies</h3>';
		if ( $new_query->have_posts() ) :
			while ( $new_query->have_posts() ) :
				$new_query->the_post();
				get_template_part( 'template-parts/archive/content', 'vacancy' );
				endwhile;
			wp_reset_postdata();
			wp_reset_query();
			else :
				?>
				<p class="text-danger">No current vacancies. <br/>
					<small>(Last Updated: <?php echo date( 'Y-m-d H:i' ); ?>)</small>
				</p>
				<?php
			endif;
	}
}
