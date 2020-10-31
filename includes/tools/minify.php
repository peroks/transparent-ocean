<?php namespace silverscreen\wp\troc;
use Ocean_Extra_JSMin;
/**
 * Minifies css and js files.
 *
 * @author     Per Egil Roksvaag
 * @license    MIT License
 */
class Minify {
	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		add_filter( Asset::FILTER_ASSET_SOURCE, array( $this, 'asset_source' ), 10, 2 );
	}

	/* -------------------------------------------------------------------------
	 * Asset Callbacks
	 * ---------------------------------------------------------------------- */

	/**
	 * Minifies css and js files.
	 */
	public static function asset_source( $source, $path ) {
		if( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG && strcmp( $source, $path ) ) {
			if( preg_match( '/^(.+)(?<![.]min)[.](css|js)$/', $source, $match ) ) {
				$ext = $match[2];
				$min = $match[1] . '.min.' . $ext;

				$min_path  = Main::plugin_path( $min );
				$read_path = Main::plugin_path( $source );
				$content   = '';

				if( !file_exists( $min_path ) || filemtime( $min_path ) < filemtime( $read_path ) ) {
					if( 'css' == $ext ) {
						$content = oceanwp_minify_css( file_get_contents( $read_path ) );
					} elseif( 'js' == $ext && Main::check_ocean_extra() ) {
						$content = Ocean_Extra_JSMin::minify( file_get_contents( $read_path ) );
					}

					if( $content ) {
						file_put_contents( $min_path, $content );
						touch( $min_path, filemtime( $read_path ) );
					}
				}
			}
		}
		return $source;
	}
}