<?php namespace silverscreen\wp\troc;
/**
 * Implements the singleton pattern.
 *
 * @author     Per Egil Roksvaag
 * @license    MIT License
 */
trait Singleton {
	/**
	 * @var object The class singleton.
	 */
	protected static $_instance = null;

	/**
	 * @return object The class singleton.
	 */
	public static function instance() {
		if( is_null( static::$_instance ) ) {
			static::$_instance = false;
			$class             = apply_filters( Main::FILTER_CLASS_CREATE, static::class );
			static::$_instance = apply_filters( Main::FILTER_CLASS_CREATED, new $class(), $class, static::class );
		}
		return static::$_instance;
	}

	/**
	 * Protect constructor.
	 */
	protected function __construct() { }
	protected function __clone() { }
	protected function __wakeup() { }
}