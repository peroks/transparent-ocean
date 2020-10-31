<?php namespace silverscreen\wp\troc;
/**
 * Handles plugin assets.
 *
 * @author     Per Egil Roksvaag
 * @license    MIT License
 */
class Asset {
	use Singleton;

	/**
	 * @var string The class filter hooks.
	 */
	const FILTER_ASSET_HANDLE = Main::PREFIX . '_asset_handle';
	const FILTER_ASSET_SOURCE = Main::PREFIX . '_asset_source';

	/**
	 * @var string The class action hooks.
	 */
	const ACTION_ASSET_STYLE  = Main::PREFIX . '_asset_style';
	const ACTION_ASSET_SCRIPT = Main::PREFIX . '_asset_script';

	/**
	 * Constructor.
	 */
	protected function __construct() {
	//	add_filter( 'script_loader_tag', array( $this, 'script_loader_tag' ), 10, 3 );
	//	add_filter( 'style_loader_tag', array( $this, 'style_loader_tag' ), 10, 3 );
	}

	/* -------------------------------------------------------------------------
	 * Basic asset handlers
	 * ---------------------------------------------------------------------- */

	/**
	 * Extracts a handle from an asset path.
	 *
	 * @param string $path The local path to the asset relative to this plugin's root directory (no leading slash).
	 * @param string $prefix The handle prefix.
	 * @return string The asset handle.
	 */
	protected function get_handle( $path, $prefix = Main::PREFIX ) {
		$prefix = $prefix ? str_replace( '_', '-', $prefix ) . '-' : '';
		$strip  = preg_replace( '!^(assets)/(js|css)/(.+?)([.]min)?[.](js|css)$!', '$3', trim( $path ) );
		$handle = $prefix . preg_replace( '![/.]!', '-', $strip );
		return apply_filters( self::FILTER_ASSET_HANDLE, $handle, $path );
	}

	/**
	 * Gets the readable asset source when SCRIPT_DEBUG is true, or the minified version otherwise.
	 *
	 * @param string $path The local path to the asset relative to this plugin's root directory (no leading slash).
	 * @return string The local path to the source asset file or the minified version.
	 */
	protected function get_source( $path ) {
		if( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			if( preg_match( '/^(.+)([.]min)([.](css|js))$/', $path, $match ) ) {
				$source = $match[1] . $match[3];
				$source = apply_filters( self::FILTER_ASSET_SOURCE, $source, $path );
				return file_exists( Main::plugin_path( $source ) ) ? $source : $path;
			}
		}
		return apply_filters( self::FILTER_ASSET_SOURCE, $path, $path );
	}

	/**
	 * Registers a stylesheet.
	 *
	 * @param string $path The local path to the asset relative to this plugin's root directory (no leading slash).
	 * @param array $deps An array of registered stylesheets handles this stylesheet depends on.
	 * @return string|bool The generated asset handle if successful, or false otherwise.
	 */
	public function register_style( $path, $deps = array() ) {
		$handle = $this->get_handle( $path );
		$source = Main::plugin_url( $this->get_source( $path ) );
		$result = wp_register_style( $handle, $source, $deps, Main::VERSION );

		do_action( self::ACTION_ASSET_STYLE, $handle, $path, compact( 'source', 'deps' ) );
		return $result ? $handle : false;
	}

	/**
	 * Registers a script.
	 *
	 * @param string $path The local path to the asset relative to this plugin's root directory (no leading slash).
	 * @param array $deps An array of registered script handles this script depends on.
	 * @param bool $defer Enable (true) or disable (false) deferred script loading.
	 * @param bool $async Enable (true) or disable (false) async script loading.
	 * @return string|bool The generated asset handle if successful, or false otherwise.
	 */
	public function register_script( $path, $deps = array(), $defer = true, $async = false ) {
		$handle = $this->get_handle( $path );
		$source = Main::plugin_url( $this->get_source( $path ) );
		$result = $this->mod_register_script( $handle, $source, $deps, Main::VERSION, true, $defer, $async );

		do_action( self::ACTION_ASSET_SCRIPT, $handle, $path, compact( 'source', 'deps', 'defer', 'async' ) );
		return $result ? $handle : false;
	}

	/**
	 * Enqueues a stylesheet.
	 *
	 * @param string $path The local path to the asset relative to this plugin's root directory (no leading slash).
	 * @param array $deps An array of registered stylesheet handles this stylesheet depends on.
	 * @return string|bool The generated asset handle if successful, or false otherwise.
	 */
	public function enqueue_style( $path, $deps = array() ) {
		$handle = $this->register_style( $path, $deps );
		wp_enqueue_style( $handle );
		return $handle;
	}

	/**
	 * Enqueues a script.
	 *
	 * @param string $path The local path to the asset relative to this plugin's root directory (no leading slash).
	 * @param array $deps An array of registered script handles this script depends on.
	 * @param bool $defer Enable (true) or disable (false) deferred script loading.
	 * @param bool $async Enable (true) or disable (false) async script loading.
	 * @return string|bool The generated asset handle if successful, or false otherwise.
	 */
	public function enqueue_script( $path, $deps = array(), $defer = true, $async = false ) {
		$handle = $this->register_script( $path, $deps, $defer, $async );
		wp_enqueue_script( $handle );
		return $handle;
	}

	/* -------------------------------------------------------------------------
	 * Mods to Enable deferred and async scripts.
	 * ---------------------------------------------------------------------- */

	/**
	 * Registers deferred and async scripts.
	 *
	 * @param string $handle Name of the script.
	 * @param string $src Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param array $deps An array of registered script handles this script depends on.
	 * @param bool $ver String specifying script version number.
	 * @param bool $in_footer Whether to enqueue the script before </body> instead of in the <head>.
	 * @param bool $defer Enable (true) or disable (false) deferred script loading.
	 * @param bool $async Enable (true) or disable (false) async script loading.
	 * @return bool Whether the script has been registered. True on success, false on failure.
	 */
	public function mod_register_script( $handle, $src, $deps = array(), $ver = false, $in_footer = false, $defer = false, $async = false ) {
		$wp_scripts = wp_scripts();
		$registered = $wp_scripts->add( $handle, $src, $deps, $ver );

		if( $in_footer ) {
			$wp_scripts->add_data( $handle, 'group', 1 );
		}
		if( $defer ) {
			$wp_scripts->add_data( $handle, 'defer', 1 );
		}
		if( $async ) {
			$wp_scripts->add_data( $handle, 'async', 1 );
		}
		return $registered;
	}

	/**
	 * Enqueues deferred and async scripts.
	 *
	 * @param string $handle Name of the script.
	 * @param string $src Full URL of the script, or path of the script relative to the WordPress root directory.
	 * @param array $deps An array of registered script handles this script depends on.
	 * @param bool $ver String specifying script version number.
	 * @param bool $in_footer Whether to enqueue the script before </body> instead of in the <head>.
	 * @param bool $defer Enable (true) or disable (false) deferred script loading.
	 * @param bool $async Enable (true) or disable (false) async script loading.
	 */
	public function mod_enqueue_script( $handle, $src = '', $deps = array(), $ver = false, $in_footer = false, $defer = false, $async = false ) {
		$this->mod_register_script( $handle, $src, $deps, $ver, $in_footer, $defer, $async );
		wp_enqueue_script( $handle );
	}

	/**
	 * Renders deferred and async scripts and removes the deprecated type attribute.
	 *
	 * @param string $tag The script tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 * @param string $src The script's source URL.
	 * @return string The modified tag.
	 */
	public function script_loader_tag( $tag, $handle, $src ) {
		if( wp_scripts()->get_data( $handle, 'defer' ) ) {
			$tag = str_replace( ' src=', ' defer="defer" src=', $tag );
		}
		if( wp_scripts()->get_data( $handle, 'async' ) ) {
			$tag = str_replace( ' src=', ' async="async" src=', $tag );
		}
		return str_replace( " type='text/javascript'", '', $tag );
	}

	/**
	 * Removes deprecated rel attribute.
	 *
	 * @param string $tag The link tag for the enqueued style.
	 * @param string $handle The style's registered handle.
	 * @param string $src The stylesheet's source URL.
	 * @return string The modified tag.
	 */
	public function style_loader_tag( $tag, $handle, $src ) {
		return str_replace( " type='text/css'", '', $tag );
	}
}