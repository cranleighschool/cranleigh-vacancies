<?php


	namespace CranleighSchool\CranleighVacancies;

	use WP_Query;

class FrontendContent {

	public function __construct() {
		 add_filter( 'the_content', [ $this, 'content_filter' ] );
	}

	/**
	 * @param $content
	 *
	 * @return string|void
	 * @throws \Exception
	 */
	function content_filter( $content ) {
		global $post;

		if ( is_admin() ) {
			// Don't process if you are on the wp-admin side of the road.
			return;
		}

		if ( $post->post_type == Configuration::POST_TYPE_KEY && is_single() ) {

			$vacancy_expiry = Configuration::wp_strtotime( get_post_meta( get_the_ID(), 'vacancy_expiry', true ) );

			if ( $vacancy_expiry < time() ) {
				$date    = date( get_option( 'date_format' ), $vacancy_expiry );
				$content = '<p class="text-danger"><strong>The deadline for this vacancy passed on ' . $date . '.</strong></p>' . $content;
			} else {
				$extra = '<hr /><p class=""><i class="fa fa-fw fa-clock-o"></i><strong>Closing Date: ' . date(
						get_option( 'date_format' ),
						$vacancy_expiry
					) . '.</strong></p>';

				$vacancy_pdf_id = get_post_meta( get_the_ID(), 'vacancy_pdf', true );
				if ( $vacancy_pdf_id ) :
					$extra .= '<p class=""><i class="fa fa-fw fa-download"></i> <a href="' . wp_get_attachment_url( $vacancy_pdf_id ) . '">Job Description (PDF)</a></p>';

					if ( ! $this->shouldHideForms( $post ) ) {
						$downloads = Configuration::getVacancyDownloads( $post );
						while ( $downloads->have_posts() ) :
							$downloads->the_post();
							$extra .= '<p><i class="fa fa-fw fa-download"></i> <a href="/download/' . $post->post_name . '">' . get_the_title() . '</a></p>';
						endwhile;
						wp_reset_postdata();
					}
				endif;
				$safeguarding = '<p class="text-muted well-sm well"><small><em>Cranleigh School is committed to the protection & safety of its pupils. The successful application will be subject to a <a href="https://www.gov.uk/disclosure-barring-service-check/overview" target="_blank">DBS check</a>.</em></small></p>';
				$content      = $content . $extra . $safeguarding;
			}
		}

		return $content;
	}

	public static function _is_expired_vacancy( int $vacancy_id ): bool {
		$vacancy_expiry_timestamp = Configuration::wp_strtotime( get_post_meta( $vacancy_id, 'vacancy_expiry', true ) );
		if ( Configuration::now() > $vacancy_expiry_timestamp ) {
			return true;
		} else {
			return false;
		}

	}

	/**
	 * @param \WP_Post $post
	 *
	 * @return bool
	 */
	public function shouldHideForms(\WP_Post $post): bool
	{
		$value = get_post_meta($post->ID, 'vacancy_hide_forms', true);

		if ($value === 'hide') {
			return true;
		}

		return false;

	}

}
