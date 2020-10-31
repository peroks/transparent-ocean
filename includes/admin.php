<?php namespace silverscreen\wp\troc;
/**
 * Admin class.
 *
 * @author     Per Egil Roksvaag
 * @license    MIT License
 */
class Admin {
	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		add_action( 'admin_init', array( $this, 'admin_init' ), 25 );
	}

	public function admin_init() {
		add_filter( 'plugin_row_meta', array( $this, 'plugin_row_meta' ), 25, 2 );
	}

	/* -------------------------------------------------------------------------
	 * WordPress settings callbacks.
	 * ---------------------------------------------------------------------- */

	/**
	 * Displays a "Support" link for this plugin on the Plugins page.
	 *
	 * @param  mixed $links Plugin Row Meta.
	 * @param  mixed $file Plugin Base file.
	 * @return array
	 */
	public static function plugin_row_meta( $links, $file ) {
		if( $file == plugin_basename( Main::FILE ) ) {
			$support = esc_url( 'https://www.silverscreen.tours/about/contact/' );
			$links[] = '<a href="' . $support . '" target="_blank" rel="noopener">' . esc_html__( 'Support', 'transparent-ocean' ) . '</a>';
		}
		return $links;
	}
}