<?php
	/**
	 * Created by PhpStorm.
	 * User: fredbradley
	 * Date: 13/09/2017
	 * Time: 16:15
	 */

	namespace CranleighSchool\CranleighVacancies\Shortcodes;

abstract class BaseShortcode {

	public function __construct() {
		$this->init();
	}
	private function init() {
		add_shortcode( $this->tag, array( $this, 'render' ) );
	}
	abstract public function render( $atts, $content = null);
}
