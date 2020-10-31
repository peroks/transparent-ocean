<?php namespace silverscreen\wp\troc;
/*
 * Description:       Extends OceanWP with beautiful transparent headers, banners and sliders.
 * Plugin Name:       Transparent Ocean
 * Plugin URI:        https://silverscreen.tours
 * Text Domain:       transparent-ocean
 *
 * Author:            Silverscreen Tours
 * Author URI:        https://silverscreen.tours
 * Copyright:         Silverscreen Tours GmbH
 *
 * Version:           0.7.0
 * Stable tag:        0.7.0
 * Requires at least: 4.5.0
 * Tested up to:      5.2.3
 * Requires PHP:      7.0
 *
 * License:           MIT License
 * License URI:       https://opensource.org/licenses/MIT
 *
 * Copyright © Silverscreen Tours GmbH
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all
 * copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 *
 * -----------------------------------------------------------------------------
 * Transparent Ocean bundles the following third-party resources
 * -----------------------------------------------------------------------------
 *
 * paroller.js, Copyright (c) 2017 tgomilar
 * License: MIT License
 * Source: https://github.com/tgomilar/paroller.js/
 */

/**
 * The Transparent Ocean plugin main class.
 *
 * @author     Silverscreen Tours
 * @link       https://silverscreen.tours
 * @copyright  Silverscreen Tours GmbH
 * @version    0.7.0
 */
class Main {
	/**
	 * @var string The plugin version.
	 */
	const VERSION = '0.7.0';

	/**
	 * @var string The plugin file.
	 */
	const FILE = __FILE__;

	/**
	 * @var string The plugin name.
	 */
	const NAME = 'Transparent Ocean';

	/**
	 * @var string The plugin domain.
	 */
	const DOMAIN = 'transparent-ocean';

	/**
	 * @var string The plugin underscore prefix.
	 */
	const PREFIX = 'troc';

	/**
	 * @var string The system requirements.
	 */
	const REQUIRE_WP    = '4.5';
	const REQUIRE_PHP   = '7.0';
	const REQUIRE_OCEAN = '1.5';

	/**
	 * @var string The plugin global options.
	 */
	const OPTION_VERSION		= self::PREFIX . '_version';

	/**
	 * @var string The plugin global action hooks.
	 */
	const ACTION_LOADED			= self::PREFIX . '_loaded';
	const ACTION_UPDATE			= self::PREFIX . '_update';
	const ACTION_ACTIVATE		= self::PREFIX . '_activate';
	const ACTION_DEACTIVATE		= self::PREFIX . '_deactivate';
	const ACTION_DELETE			= self::PREFIX . '_delete';

	/**
	 * @var string The plugin global filter hooks.
	 */
	const FILTER_CLASS_CREATE	= self::PREFIX . '_class_create';
	const FILTER_CLASS_CREATED	= self::PREFIX . '_class_created';
	const FILTER_CLASS_PATH		= self::PREFIX . '_class_path';
	const FILTER_SYSTEM_CHECK	= self::PREFIX . '_system_check';
	const FILTER_PLUGIN_PATH	= self::PREFIX . '_plugin_path';
	const FILTER_PLUGIN_BASE	= self::PREFIX . '_plugin_base';
	const FILTER_PLUGIN_PART	= self::PREFIX . '_plugin_part';
	const FILTER_PLUGIN_URL		= self::PREFIX . '_plugin_url';
	const FILTER_ENQUEUE_STYLE	= self::PREFIX . '_enqueue_style';
	const FILTER_ENQUEUE_SCRIPT	= self::PREFIX . '_enqueue_script';

	/**
	 * @var string The base directories for css and js assets.
	 */
	const DIR_STYLES	= 'assets/css';
	const DIR_SCRIPTS	= 'assets/js';
	const DIR_LANGUAGES	= 'languages';

	/**
	 * @var Main The class singleton.
	 */
	protected static $_instance;

	/**
	 * @return Main The class singleton.
	 */
	public static function instance() {
		if( is_null( static::$_instance ) && static::check() ) {
			static::$_instance = false;
			$class = apply_filters( self::FILTER_CLASS_CREATE, static::class );
			static::$_instance = apply_filters( self::FILTER_CLASS_CREATED, new $class(), $class, static::class );
			do_action( self::ACTION_LOADED, static::$_instance );
		}
		return static::$_instance;
	}

	/**
	 * Constructor
	 */
	protected function __clone() { }
	protected function __wakeup() { }
	protected function __construct() {
		$this->autoload();
		$this->run();
		$this->update();
		$this->register();
	}

	/**
	 * Loads and runs the activated modules.
	 */
	protected function run() {
		Setup::instance();
		$common = Common::instance();
		$common->is_header_active() && Header::instance();
		$common->is_banner_active() && Banner::instance();
		$common->is_slider_active() && Slider::instance();

		if( is_admin() ) {
			Admin::instance();
		}
		if( defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) {
			Minify::instance();
		}
	}

	/**
	 * Registers autoloading.
	 */
	protected function autoload() {
		$classes = apply_filters( self::FILTER_CLASS_PATH, array(
			__NAMESPACE__ . '\Singleton'	=> static::plugin_path( 'includes/singleton.php' ),
			__NAMESPACE__ . '\Setup'		=> static::plugin_path( 'includes/setup.php' ),
			__NAMESPACE__ . '\Common'		=> static::plugin_path( 'includes/common.php' ),
			__NAMESPACE__ . '\Banner'		=> static::plugin_path( 'includes/banner.php' ),
			__NAMESPACE__ . '\Slider'		=> static::plugin_path( 'includes/slider.php' ),
			__NAMESPACE__ . '\Header'		=> static::plugin_path( 'includes/header.php' ),
			__NAMESPACE__ . '\Twain'		=> static::plugin_path( 'includes/twain.php' ),
			__NAMESPACE__ . '\Admin'		=> static::plugin_path( 'includes/admin.php' ),
			__NAMESPACE__ . '\Asset'		=> static::plugin_path( 'includes/tools/asset.php' ),
			__NAMESPACE__ . '\Minify'		=> static::plugin_path( 'includes/tools/minify.php' ),
		) );

		spl_autoload_register( function( $name ) use ( $classes ) {
			if( array_key_exists( $name, $classes ) ) {
				include_once $classes[$name];
			}
		} );
	}

	/* =========================================================================
	 * Stop reading! Everything below this line is just plugin management and
	 * some very basic path and url handlers. You'll find the real action in
	 * the modules / classes loaded above.
	 * ====================================================================== */

	/* -------------------------------------------------------------------------
	 * Check system requirements
	 * ---------------------------------------------------------------------- */

	/**
	 * Checks if the system environment is supported.
	 *
	 * @return bool True if the system environment is supported, false otherwise.
	 */
	public static function check() {
		$error  = esc_html__( 'Error', 'transparent-ocean' );
		$format = esc_html__( '%1$s requires %2$s version %3$s or higher, the plugin is currently NOT RUNNING.', 'transparent-ocean' );
		$notice = '';

		if( version_compare( PHP_VERSION, self::REQUIRE_PHP ) < 0 ) {
			$notice = sprintf( $format, self::NAME, 'PHP', self::REQUIRE_PHP );
			add_action( 'admin_notices', function() use( $error, $notice) {
				printf( '<div class="notice notice-error"><p><strong>%s: </strong>%s</p></div>', $error, $notice );
			} );
			trigger_error( $notice, E_USER_WARNING );
		}
		if( version_compare( get_bloginfo( 'version' ), self::REQUIRE_WP ) < 0 ) {
			$notice = sprintf( $format, self::NAME, 'WordPress', self::REQUIRE_WP );
			add_action( 'admin_notices', function() use( $error, $notice) {
				printf( '<div class="notice notice-error"><p><strong>%s: </strong>%s</p></div>', $error, $notice );
			} );
			trigger_error( $notice, E_USER_WARNING );
		}
		if( empty( static::check_ocean( self::REQUIRE_OCEAN ) ) ) {
			$notice = sprintf( $format, self::NAME, 'OceanWP', self::REQUIRE_OCEAN );
			add_action( 'admin_notices', function() use( $error, $notice) {
				printf( '<div class="notice notice-error"><p><strong>%s: </strong>%s</p></div>', $error, $notice );
			} );
			trigger_error( $notice, E_USER_WARNING );
		}
		return apply_filters( self::FILTER_SYSTEM_CHECK, empty( $notice ) );
	}

	/**
	 * Checks if OceanWP is installed and has a supported version.
	 *
	 * @param string $version The required version.
	 * @return bool True if OceanWP is installed with a supported version, false otherwise.
	 */
	public static function check_ocean( $version = self::REQUIRE_OCEAN ) {
		$theme = wp_get_theme();
		if( 'OceanWP' == $theme->name || 'oceanwp' == $theme->template ) {
			return 'oceanwp' == $theme->template || version_compare( $theme->version, $version ) >= 0;
		}
		return false;
	}

	/**
	 * Checks if OceanExtra is installed and has a supported version.
	 *
	 * @param string $version The required version.
	 * @return bool True if Ocean Extra is installed with a supported version, false otherwise.
	 */
	public static function check_ocean_extra( $version = '1.3' ) {
		if( class_exists( 'Ocean_Extra' ) && function_exists( 'Ocean_Extra' ) ) {
			return version_compare( Ocean_Extra()->version, $version ) >= 0;
		}
		return false;
	}

	/**
	 * Checks if WooCommerce is installed and has a supported version.
	 *
	 * @param string $version The required version.
	 * @return bool True if WooCommerce is installed with a supported version, false otherwise.
	 */
	public static function check_woo( $version = '3.3' ) {
		if( class_exists( 'WooCommerce' ) && function_exists( 'wc' ) ) {
			return version_compare( wc()->version, $version ) >= 0;
		}
		return false;
	}

	/* -------------------------------------------------------------------------
	 * Update, activate, deactivate and uninstall plugin.
	 * ---------------------------------------------------------------------- */

	/**
	 * Checks if the plugin was updated.
	 * Notifies plugin modules to update and flushes rewrite rules.
	 *
	 * @return bool True if the plugin was updated, false otherwise.
	 */
	protected function update() {
		$version = get_option( self::OPTION_VERSION );

		if( $version !== self::VERSION ) {
			do_action( self::ACTION_UPDATE, $this, self::VERSION, $version );
			update_option( self::OPTION_VERSION, self::VERSION );

			add_action( 'wp_loaded', 'flush_rewrite_rules' );
			add_action( 'admin_notices', function() {
				$notice = sprintf( esc_html__( '%s has been updated to version %s', 'transparent-ocean' ), self::NAME, self::VERSION );
				printf( '<div class="notice notice-success is-dismissible"><p>%s.</p></div>', $notice );
				error_log( $notice );
			} );
			return true;
		}
		return false;
	}

	/**
	 * 	Registers plugin activation, deactivation and uninstall hooks.
	 */
	protected function register() {
		if( is_admin() ) {
			register_activation_hook( self::FILE, array( static::class, 'activate' ) );
			register_deactivation_hook( self::FILE, array( static::class, 'deactivate' ) );
			register_uninstall_hook( self::FILE, array( static::class, 'uninstall' ) );
		}
	}

	/**
	 * Runs when the plugin is activated.
	 * This hook is called AFTER all other hooks (except 'shutdown').
	 * WP redirects the request immediately after this hook, so we can't register any hooks to be executed later.
	 */
	public static function activate() {
		if( is_admin() && current_user_can( 'activate_plugins' ) ) {
			do_action( self::ACTION_ACTIVATE, static::instance(), self::VERSION, get_option( self::OPTION_VERSION ) );
			update_option( self::OPTION_VERSION, self::VERSION );
			error_log( sprintf( esc_html__( '%s version %s has been activated', 'transparent-ocean' ), self::NAME, self::VERSION ) );
			flush_rewrite_rules();
		}
	}

	/**
	 * Runs when the plugin is deactivated, shortly after wp_loaded.
	 * Flushes rewrite rules and notifies plugin modules to deactivate.
	 */
	public static function deactivate() {
		if( is_admin() && current_user_can( 'activate_plugins' ) ) {
			do_action( self::ACTION_DEACTIVATE, static::instance(), self::VERSION, get_option( self::OPTION_VERSION ) );
			error_log( sprintf( esc_html__( '%s has been deactivated', 'transparent-ocean' ), self::NAME ) );
			flush_rewrite_rules();
		}
	}

	/**
	 * Runs when the plugin is deleted, shortly after wp_loaded.
	 * Notifies plugin modules to delete all plugin settings.
	 */
	public static function uninstall() {
		if( is_admin() && current_user_can( 'delete_plugins' ) ) {
			do_action( self::ACTION_DELETE, static::instance(), self::VERSION, get_option( self::OPTION_VERSION ) );
			delete_option( self::OPTION_VERSION );
			error_log( sprintf( esc_html__( '%s has been removed', 'transparent-ocean' ), self::NAME ) );
			flush_rewrite_rules();
		}
	}

	/* -------------------------------------------------------------------------
	 * Basic path, url and asset handlers.
	 * ---------------------------------------------------------------------- */

	/**
	 * Gets a full filesystem path from a local path.
	 *
	 * @param string $path The local path relative to this plugin's root directory.
	 * @return string The full filesystem path.
	 */
	public static function plugin_path( $path = '' ) {
		$full = plugin_dir_path( self::FILE ) . ltrim( $path, '/' );
		return apply_filters( self::FILTER_PLUGIN_PATH, $full, $path );
	}

	/**
 	 * Gets a path relative to the WP_PLUGIN_DIR from a local plugin path.
	 *
	 * @param string $path The local path relative to this plugin's root directory.
	 * @return string The local path relative to the WP_PLUGIN_DIR directory.
	 */
	public static function plugin_base( $path = '' ) {
		$base = dirname( plugin_basename( self::FILE ) ) . '/' . ltrim( $path, '/' );
		return apply_filters( self::FILTER_PLUGIN_BASE, $base, $path );
	}

	/**
	 * Includes a template or template part.
	 * $args may NOT contain a 'template' key. This is RESERVED for internal use.
	 *
	 * @param string $path The local path relative to this plugin's root directory.
	 * @param array $args Arguments to be extracted and passed on to the template.
	 */
	public static function plugin_part( $path, $args = array() ) {
		$template = static::plugin_path( $path );
		$args['template'] = apply_filters( self::FILTER_PLUGIN_PART, $template, $path );
		extract( $args );
		include $template;
	}

	/**
	 * Gets the URL to the given local path.
	 *
	 * @param string $path The local path relative to this plugin's root directory.
	 * @return string The URL.
	 */
	public static function plugin_url( $path = '' ) {
		$url = plugins_url( $path, self::FILE );
		return apply_filters( self::FILTER_PLUGIN_URL, $url, $path );
	}

	/**
	 * Enqueues a stylesheet.
	 *
	 * @param string $path The local path to the asset relative to this plugin's root directory (no leading slash).
	 * @param array $deps An array of registered stylesheet handles this stylesheet depends on.
	 * @param array $args Optional additional arguments: media, etc.
	 * @return string|bool The generated asset handle if successful, or false otherwise.
	 */
	public static function enqueue_style( $path, $deps = array(), $args = array() ) {
		$path = trim( trim( $path ), '/' );
		$base = trim( self::DIR_STYLES, '/' );
		$source = empty( SCRIPT_DEBUG ) ? $path : preg_replace( '/[.]min[.](js|css)$/', '.$1', $path );
		$handle = preg_replace( "!^{$base}/(.+?)([.]min)?[.](js|css)$!", '$1', $source );
		$handle = self::PREFIX . '-' . preg_replace( '![/.]!', '-', $handle );

		wp_enqueue_style( $handle, static::plugin_url( $source ), $deps, self::VERSION, $args['media'] ?? 'all' );
		return apply_filters( self::FILTER_ENQUEUE_STYLE, $handle, $path, $source, $deps, $args );
	}

	/**
	 * Enqueues a script.
	 *
	 * @param string $path The local path to the asset relative to this plugin's root directory (no leading slash).
	 * @param array $deps An array of registered script handles this script depends on.
	 * @param array $args Optional additional arguments: footer, defer, async, etc.
	 * @return string|bool The generated asset handle if successful, or false otherwise.
	 */
	public static function enqueue_script( $path, $deps = array(), $args = array() ) {
		$path = trim( trim( $path ), '/' );
		$base = trim( self::DIR_SCRIPTS, '/' );
		$source = empty( SCRIPT_DEBUG ) ? $path : preg_replace( '/[.]min[.](js|css)$/', '.$1', $path );
		$handle = preg_replace( "!^{$base}/(.+?)([.]min)?[.](js|css)$!", '$1', $source );
		$handle = self::PREFIX . '-' . preg_replace( '![/.]!', '-', $handle );

		wp_enqueue_script( $handle, static::plugin_url( $source ), $deps, self::VERSION, $args['footer'] ?? true );
		return apply_filters( self::FILTER_ENQUEUE_SCRIPT, $handle, $path, $source, $deps, $args );
	}

	/* -------------------------------------------------------------------------
	 * Debugging
	 * ---------------------------------------------------------------------- */

	public static function debug( $data, $append = false ) {
		$options = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT;
		$flags = $append ? FILE_APPEND : 0;
		$file = __DIR__ . '/debug.txt';
		file_put_contents( $file, json_encode( $data, $options ) . "\n", $flags );
	}

	/**
	 * Writes an entry to the php log and adds context information.
	 *
	 * @param string $log Log entry.
	 */
	public static function log( $log = '' ) {
		$caller = debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS );
		$file   = empty( $caller[0]['file'] ) ? '' : ' in ' . $caller[0]['file'];
		$line   = empty( $caller[0]['line'] ) ? '' : ' on line ' . $caller[0]['line'];
		$type   = empty( $caller[1]['function'] ) ? '#Debug: ' : '#' . $caller[1]['function'] . ': ';
		$entry  = json_encode( $log, JSON_UNESCAPED_SLASHES, JSON_UNESCAPED_UNICODE );
		error_log( $type . gettype( $log ) . ': ' . $entry . $file . $line );
	}

	/**
	 * Writes the backtrace to the php log.
	 */
	public static function trace() {
		static::log( debug_backtrace( DEBUG_BACKTRACE_IGNORE_ARGS ) );
	}
}

//	Don't load directly
defined( 'ABSPATH' ) || exit;

//	Run the main class
add_action( 'plugins_loaded', array( Main::class, 'instance' ), 5 );