<?php namespace silverscreen\wp\troc;
/**
 * Plugin setup
 *
 * @author Per Egil Roksvaag
 */
class Setup {
	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	/* -------------------------------------------------------------------------
	 * WordPress callbacks
	 * ---------------------------------------------------------------------- */

	public function init() {
		load_plugin_textdomain( Main::DOMAIN, false, Main::plugin_base( Main::DIR_LANGUAGES ) ); 
	}
}