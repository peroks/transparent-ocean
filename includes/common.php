<?php namespace silverscreen\wp\troc;
use OceanWP_Customizer_Buttonset_Control;
/**
 * Common Class.
 *
 * @author     Per Egil Roksvaag
 * @license    MIT License
 */
class Common {
	use Singleton;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'init' ) );
	}

	public function init() {
		// Wordpress Customizer
		add_action( 'customize_register', array( $this, 'customize_register' ) );
		add_action( 'customize_controls_enqueue_scripts', array( $this, 'customize_controls_enqueue' ) );
		add_filter( 'pre_update_option_theme_mods_oceanwp', array( $this, 'pre_update_option_theme_mods_oceanwp' ) );

		// OceanWP Extra Metabox
		add_filter( 'ocean_gallery_metabox_post_types', array( $this, 'ocean_gallery_metabox_post_types' ) );
	}

	public function admin_init() {
		// OceanWP Extra Metabox
		add_filter( 'ocean_gallery_metabox_post_types', array( $this, 'ocean_gallery_metabox_post_types' ) );
	}

	public function is_header_active() {
		$active = get_theme_mod( 'troc_header_active', 'enable' ) == 'enable';
		return apply_filters( 'troc_header_active', $active );
	}

	public function is_banner_active() {
		$active = get_theme_mod( 'troc_banner_active', 'enable' ) == 'enable';
		return apply_filters( 'troc_banner_active', $active );
	}

	public function is_slider_active() {
		$active = get_theme_mod( 'troc_slider_active', 'enable' ) == 'enable';
		return apply_filters( 'troc_slider_active', $active );
	}

	/* -------------------------------------------------------------------------
	 * HTML Formatting Helpers
	 * ---------------------------------------------------------------------- */

	/**
	 * Transforms an associative array to HTML data attributes.
	 * If not empty, the result contains a leding space.
	 *
	 * @param array $data A key/value array, keys without the 'data-' prefix.
	 * @return string HTML data attributes.
	 */
	public function data_attr_encode( $data ) {
		$result = '';
		foreach( (array) $data as $key => $value ) {
			$result .= ' data-' . esc_attr( $key ) . '="' . esc_attr( $value ) . '"';
		}
		return $result;
	}

	/* -------------------------------------------------------------------------
	 * WP Customizer Sanitize Helpers
	 * ---------------------------------------------------------------------- */

	public function troc_sanitize_multicheck( $list, $setting ) {
		if( $list = oceanwp_sanitize_multicheck( $image, $setting ) ) {
		}
		return $list;
	}

	/**
	 * Sanitizes GPS decimal coordinates.
	 *
	 * @param string $value GPS decimal latitude or longitude.
	 * @param WP_Customize_Setting $setting Handles saving and sanitizing of settings.
	 * @return string Formatted gps latitude or longitude.
	 */
	public function sanitize_number( $value, $setting ) {
		return is_numeric( $value ) ? number_format( intval( $value ), 0 ) : '';
	}

	public function sanitize_absint( $value, $setting ) {
		return is_numeric( $value ) ? number_format( absint( $value ), 0 ) : '';
	}

	/**
	 * Sanitizes GPS decimal coordinates.
	 *
	 * @param string $value GPS decimal latitude or longitude.
	 * @param WP_Customize_Setting $setting Handles saving and sanitizing of settings.
	 * @return string Formatted gps latitude or longitude.
	 */
	public function sanitize_gps( $value, $setting ) {
		return is_numeric( $value ) ? number_format( floatval( $value ), 6 ) : '';
	}

	/**
	 * Saves images with their Attachment ID instead of their url.
	 *
	 * @param string $image Attachment url.
	 * @param WP_Customize_Setting $setting Handles saving and sanitizing of settings.
	 * @return int Attachment ID
	 */
	public function sanitize_image( $image, $setting ) {
		if( is_numeric( $image ) ) {
			return absint( $image );
		}
		if( $image = oceanwp_sanitize_image( $image, $setting ) ) {
			$id = attachment_url_to_postid( $image ) ?: $image;
			return $id;
		}
		return $image;
	}

	/* -------------------------------------------------------------------------
	 * WP Customizer Callbacks
	 * ---------------------------------------------------------------------- */

	/**
	 * Adds Stylesheet to Customizer Controls.
	 *
	 * @hooked customize_controls_enqueue
	 */
	public function customize_controls_enqueue() {
		Asset::instance()->enqueue_style( 'assets/css/customizer/common.min.css' );
	}

	/**
	 * Adds a new Panel to the Wordpress Customizer.
	 *
	 * @hooked customize_register
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function customize_register( $wp_customize ) {
		$wp_customize->add_panel( 'troc_panel', array(
			'title'    => esc_html__( 'Transparent Ocean', 'transparent-ocean' ),
			'priority' => 210,
		) );

		$section = 'troc_general_section';
		$wp_customize->add_section( $section, array(
			'title'    => esc_html__( 'General Options', 'transparent-ocean' ),
			'panel'    => 'troc_panel',
			'priority' => 20,
		) );

		/**
		 * Enable Transparent Header
		 */
		$wp_customize->add_setting( 'troc_header_active', array(
			'transport'         => 'postMessage',
			'default'           => 'enable',
			'sanitize_callback' => 'oceanwp_sanitize_select',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Buttonset_Control( $wp_customize, 'troc_header_active', array(
			'label'    => esc_html__( 'Transparent Header', 'transparent-ocean' ),
			'section'  => $section,
			'settings' => 'troc_header_active',
			'priority' => 10,
			'choices'  => array(
				'enable'  => esc_html__( 'Enable', 'oceanwp' ),
				'disable' => esc_html__( 'Disable', 'oceanwp' ),
			),
		) ) );

		/**
		 * Enable Page Banner
		 */
		$wp_customize->add_setting( 'troc_banner_active', array(
			'transport'         => 'postMessage',
			'default'           => 'enable',
			'sanitize_callback' => 'oceanwp_sanitize_select',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Buttonset_Control( $wp_customize, 'troc_banner_active', array(
			'label'    => esc_html__( 'Page Banner', 'transparent-ocean' ),
			'section'  => $section,
			'settings' => 'troc_banner_active',
			'priority' => 10,
			'choices'  => array(
				'enable'  => esc_html__( 'Enable', 'oceanwp' ),
				'disable' => esc_html__( 'Disable', 'oceanwp' ),
			),
		) ) );

		/**
		 * Enable Page Slider
		 */
		$wp_customize->add_setting( 'troc_slider_active', array(
			'transport'         => 'postMessage',
			'default'           => 'enable',
			'sanitize_callback' => 'oceanwp_sanitize_select',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Buttonset_Control( $wp_customize, 'troc_slider_active', array(
			'label'    => esc_html__( 'Page Slider', 'transparent-ocean' ),
			'section'  => $section,
			'settings' => 'troc_slider_active',
			'priority' => 10,
			'choices'  => array(
				'enable'  => esc_html__( 'Enable', 'oceanwp' ),
				'disable' => esc_html__( 'Disable', 'oceanwp' ),
			),
		) ) );
	}

	/**
	 * Removes empty Theme Mod settings.
	 *
	 * @hooked pre_update_option_theme_mods_oceanwp
	 *
	 * @param array Array of Theme Mod settings.
	 * @return array Theme Mods without empty settings.
	 */
	public function pre_update_option_theme_mods_oceanwp( $mods ) {
		foreach( $mods as $key => $value ) {
			if( '' === $value && 0 === strpos( $key, Main::PREFIX . '_' ) ) {
				unset( $mods[$key] );
			}
		}
		return $mods;
	}

	/* -------------------------------------------------------------------------
	 * OceanWP Extra Metabox Callbacks
	 * ---------------------------------------------------------------------- */

	/**
	 * Adds OceanWP Extra Metabox to Posts and Pages.
	 *
	 * @param array $types List of Post types to display the OceanWP Extra Metabox.
	 * @return array Modified list of Post types
	 */
	public function ocean_gallery_metabox_post_types( $types ) {
		if( $this->is_header_active() || $this->is_banner_active() || $this->is_slider_active() ) {
			return array_unique( array_merge( $types, array( 'post', 'page' ) ) );
		}
		return $types;
	}
}