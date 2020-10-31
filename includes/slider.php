<?php namespace silverscreen\wp\troc;
use OceanWP_Customize_Multicheck_Control;
use OceanWP_Customizer_Range_Control;
use WP_Customize_Control;
/**
 * Page Slider for OceanWP.
 *
 * @author     Per Egil Roksvaag
 * @license    MIT License
 */
class Slider {
	use Singleton;

	/**
	 * @var bool Cache for has_slider().
	 */
	protected $_has_slider = null;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		Banner::instance();
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ), 5 );
	}

	public function init() {
		// Wordpress Customizer
		add_action( 'customize_register', array( $this, 'customize_register' ) );

		// OceanWP Metabox
		add_action( 'butterbean_register', array( $this, 'butterbean_register' ), 10, 2 );
	}

	public function template_redirect() {
		if( false == $this->has_slider() ) {
			return;
		}

		// Page Slider callbacks
		add_filter( 'troc_slider_enable', array( $this, 'troc_slider_enable' ) );
		add_filter( 'troc_slider_style', array( $this, 'troc_slider_style' ) );
		add_filter( 'troc_slider_thumb', array( $this, 'troc_slider_thumb' ) );
		add_filter( 'troc_slider_length', array( $this, 'troc_slider_length' ) );
		add_filter( 'troc_slider_speed', array( $this, 'troc_slider_speed' ) );
		add_filter( 'troc_slider_autoplay', array( $this, 'troc_slider_autoplay' ) );
		add_filter( 'troc_slider_autostop', array( $this, 'troc_slider_autostop' ) );
		add_filter( 'troc_slider_autospeed', array( $this, 'troc_slider_autospeed' ) );
		add_filter( 'troc_slider_arrows', array( $this, 'troc_slider_arrows' ) );
		add_filter( 'troc_slider_dots', array( $this, 'troc_slider_dots' ) );
		add_filter( 'troc_slider_caption', array( $this, 'troc_slider_caption' ) );
		add_filter( 'troc_slider_credit', array( $this, 'troc_slider_credit' ) );
		add_filter( 'troc_slider_lazyload', array( $this, 'troc_slider_lazyload' ) );

		// Page Banner callbacks
		add_filter( 'troc_has_banner', array( $this, 'troc_has_banner' ) );
		add_filter( 'troc_banner_image', array( $this, 'troc_banner_image' ) );
		add_filter( 'troc_banner_position', array( $this, 'troc_banner_position' ) );
		add_filter( 'troc_banner_attachment', array( $this, 'troc_banner_attachment' ) );
		add_filter( 'troc_before_page_banner_inner', array( $this, 'troc_before_page_banner_inner' ) );

		// OceanWP callbacks
		add_filter( 'body_class', array( $this, 'body_class' ) );
		add_filter( 'ocean_head_css', array( $this, 'ocean_head_css' ) );

		// Wordpress callbacks
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		add_filter( 'wp_get_attachment_image_attributes', array( $this, 'wp_get_attachment_image_attributes' ), 10, 3 );
	}

	/* -------------------------------------------------------------------------
	 * Page Slider output
	 * ---------------------------------------------------------------------- */

	/**
	 * Builds the Page Slider.
	 *
	 * @param array $gallery List of Attachment IDs.
	 * @return string The Page Slider HTML code.
	 */
	public function build_slider( $gallery = array(), $size = 'troc-banner' ) {
		$classes = array( 'page-slider' );
		$attr    = array( 'itemprop' => 'image' );
		$data    = array(
			'speed'         => absint( $this->get_slider_speed() ),
			'autoplay'      => (bool) $this->get_slider_autoplay(),
			'autostop'      => (bool) $this->get_slider_autostop(),
			'autoplaySpeed' => absint( $this->get_slider_autospeed() * 1000 ),
			'arrows'        => (bool) $this->get_slider_arrows(),
			'dots'          => (bool) $this->get_slider_dots(),
		);

		if( $style = $this->get_slider_style() ) {
			$data['fade']     = ( 'fade' == $style );
			$data['vertical'] = ( 'vertical' == $style );
		}
		if( $lazyload = $this->get_slider_lazyload() ) {
			$data['lazyLoad'] = esc_attr( $lazyload );
			$attr['lazyload'] = true;
		}

		$data = apply_filters( 'troc_slider_data', $data );
		$data = count( $data ) ? " data-slick='" . esc_js( json_encode( $data ) ) . "'" : '';

		$classes = apply_filters( 'troc_slider_classes', $classes );
		$classes = count( $classes ) ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '';

		$gallery = $gallery ?: $this->get_slider_gallery();
		$slides  = $this->build_slide( $gallery, $attr, $size );
		$slider  = '';

		$slider .= apply_filters( 'troc_before_page_slider', '' );
		$slider .= '<div' . $classes . $data . '>';
		$slider .= apply_filters( 'troc_before_page_slider_inner', '' );
		$slider .= $slides;
		$slider .= apply_filters( 'troc_after_page_slider_inner', '' );
		$slider .= '</div>';
		$slider .= apply_filters( 'troc_after_page_slider', '' );

		return $slider;
	}

	public function build_slide( $gallery, $attr = array(), $size = 'troc-banner' ) {
		$slides = '';
		foreach( $gallery as $attachment_id ) {
			$caption = '';
			$credit  = '';

			if( $this->get_slider_caption() ) {
				$caption = wp_get_attachment_caption( $attachment_id );
				$caption = $caption ? '<figcaption>' . esc_html( $caption ) . '</figcaption>' : '';
			}
			if( $this->get_slider_credit() && class_exists ( 'silverscreen\wp\photo_credit\Meta' ) ) {
				$meta   = silverscreen\wp\photo_credit\Meta::instance()->get_metadata( $attachment_id );
				$credit = $meta['credit'] ?? $meta['copyright'] ?? '';
				$credit = $credit ? '<span class="credit">Credit: ' . esc_html( $credit ) . '</span>' : '';
			}
			$image  = wp_get_attachment_image( $attachment_id, $size, false, $attr );
			$image  = apply_filters( 'photo_credit_display_credit', $image, $attachment_id );
			$slides .= '<figure>' . $image . $caption . $credit . '</figure>';
		}
		return $slides;
	}

	/* -------------------------------------------------------------------------
	 * Get Page Slider Settings
	 * ---------------------------------------------------------------------- */

	/**
	 * Check if the current Page Type or Taxonomy is enabled for Page Sliders
	 * and if a Slider gallery was found.
	 *
	 * @param $id int|WP_Term|WP_Post|Twain A Term or Post ID, object or wrapper.
	 * @param $type string The object type ('post' or 'term') to match the id.
	 * @return bool True if a Page Banner can be displayed, false otherwise.
	 */
	public function has_slider( $id = null, $type = 'post' ) {
		if( is_null( $this->_has_slider ) ) {
			$twain  = Twain::factory( $id, $type );
			$result = false;

			if( $this->is_slider_enabled( $twain ) ) {
				$length  = $this->get_slider_length();
				$result = $twain->has_gallery( $length );
			}
			$this->_has_slider = apply_filters( 'troc_has_slider', $result );
		}
		return $this->_has_slider;
	}

	/**
	 * Check if Page Sliders are enabled for the current post type or taxonomy.
	 *
	 * @param $id int|WP_Term|WP_Post|Twain A Term or Post ID, object or wrapper.
	 * @param $type string The object type ('post' or 'term') to match the id.
	 * @return bool Returns true if Page Sliders are enabled.
	 */
	public function is_slider_enabled( $id = null, $type = 'post' ) {
		$type   = Twain::factory( $id, $type )->get_type();
		$enable = get_theme_mod( 'troc_slider_enable', array() ) ?: array();
		$result = $enable && is_array( $enable ) && in_array( $type, $enable );
		$result = $this->troc_slider_enable( $result );

		return apply_filters( 'troc_slider_enable', $result, $type );
	}

	/**
	 * Retrieves an image gallery to slide on the Page Banner.
	 *
	 * @param $id int|WP_Term|WP_Post|Twain A Term or Post ID, object or wrapper.
	 * @param $type string The object type ('post' or 'term') to match the id.
	 * @return array List of Attachment IDs.
	 */
	public function get_slider_gallery( $id = null, $type = 'post' ) {
		$twain   = Twain::factory( $id, $type );
		$length  = $this->get_slider_length();
		$gallery = $twain->get_gallery_ids( $length );

		if( ( $position = $this->get_slider_thumb() ) && ( $thumb = $twain->get_thumbnail_id() ) ) {
			if( 'first' == $position ) {
				array_unshift( $gallery, $thumb );
			} elseif( 'last' == $position && count( $gallery ) < $length ) {
				array_push( $gallery, $thumb );
			} elseif( 'last' == $position && false == in_array( $thumb, $gallery ) ) {
				$gallery[$length - 1] = $thumb;
			}
			$gallery = array_slice( array_unique( array_filter( $gallery ) ), 0, $length );
		}
		return apply_filters( 'troc_slider_gallery', $gallery );
	}

	/**
	 * Gets the Page Slider style.
	 *
	 * @return string The Page Slider style: 'horizontal', 'vertical' or 'fade'.
	 */
	public function get_slider_style() {
		$style = get_theme_mod( 'troc_slider_style', 'horizontal' ) ?: 'horizontal';
		return apply_filters( 'troc_slider_style', $style );
	}

	/**
	 * Add own thumbnail to gallery
	 *
	 * @return string The Page Slider style: 'disable', 'first' or 'last'.
	 */
	public function get_slider_thumb() {
		$thumb = get_theme_mod( 'troc_slider_thumb', 'disable' );
		$thumb = apply_filters( 'troc_slider_thumb', $thumb );
		return ( 'disable' == $thumb ) ? '' : $thumb;
	}

	/**
	 * Gets the number of slides to display on the Page Slider.
	 *
	 * @return int The number of slides to display.
	 */
	public function get_slider_length() {
		$length = get_theme_mod( 'troc_slider_length', 5 ) ?: 5;
		return apply_filters( 'troc_slider_length', $length );
	}

	/**
	 * Gets the Page Slider animation speed in milliseconds.
	 *
	 * @return int The animation speed in milliseconds
	 */
	public function get_slider_speed() {
		$speed = get_theme_mod( 'troc_slider_speed', 500 );
		return apply_filters( 'troc_slider_speed', $speed );
	}

	/**
	 * Checks if Page Slider autoplay is enabled.
	 *
	 * @return bool True if autoplay is enabled, false otherwise.
	 */
	public function get_slider_autoplay() {
		$autoplay = get_theme_mod( 'troc_slider_autoplay', true );
		return apply_filters( 'troc_slider_autoplay', $autoplay );
	}

	/**
	 * Checks if Page Slider autostop on the last slide is enabled.
	 *
	 * @return bool True if autostop is enabled, false otherwise.
	 */
	public function get_slider_autostop() {
		$autostop = get_theme_mod( 'troc_slider_autostop', true );
		return apply_filters( 'troc_slider_autostop', $autostop );
	}

	/**
	 * Gets the Page Slider autoplay speed in seconds.
	 *
	 * @return int The autoplay speed in seconds.
	 */
	public function get_slider_autospeed() {
		$autospeed = get_theme_mod( 'troc_slider_autospeed', 5 ) ?: 5;
		return apply_filters( 'troc_slider_autospeed', $autospeed );
	}

	/**
	 * Checks if navigation arrows are enabled on the Page Slider.
	 *
	 * @return bool True if navigation arrows are enabled, false otherwise.
	 */
	public function get_slider_arrows() {
		$navigation = get_theme_mod( 'troc_slider_arrows', true );
		return apply_filters( 'troc_slider_arrows', $navigation );
	}

	/**
	 * Checks if navigation dots are enabled on the Page Slider.
	 *
	 * @return bool True if navigation dots are enabled, false otherwise.
	 */
	public function get_slider_dots() {
		$navigation = get_theme_mod( 'troc_slider_dots', false );
		return apply_filters( 'troc_slider_dots', $navigation );
	}

	/**
	 * Checks if the image caption is enabled on the Page Slider.
	 *
	 * @return bool True if image caption is enabled, false otherwise.
	 */
	public function get_slider_caption() {
		$caption = get_theme_mod( 'troc_slider_caption', false );
		return apply_filters( 'troc_slider_caption', $caption );
	}

	/**
	 * Checks if the photo credits are enabled on the Page Slider.
	 *
	 * @return bool True if photo credits are enabled, false otherwise.
	 */
	public function get_slider_credit() {
		$credit = get_theme_mod( 'troc_slider_credit', false );
		return apply_filters( 'troc_slider_credit', $credit );
	}

	/**
	 * Gets the image lazy loading mode of the Page Slider.
	 *
	 * @return string Lazy loading: 'ondemand', 'progressive' or empty string (disabled).
	 */
	public function get_slider_lazyload() {
		$lazyload = get_theme_mod( 'troc_slider_lazyload', '' );
		$lazyload = apply_filters( 'troc_slider_lazyload', $lazyload );
		return $lazyload == 'disable' ? '' : $lazyload;
	}

	/* -------------------------------------------------------------------------
	 * Page Slider Callbacks to override the theme settings
	 * ---------------------------------------------------------------------- */

	public function troc_slider_enable( $enable ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_slider_enable', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $enable : ( 'enable' == $meta );
	}

	public function troc_slider_style( $style ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_slider_style', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $style : $meta;
	}

	public function troc_slider_thumb( $thumb ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_slider_thumb', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $thumb : $meta;
	}

	public function troc_slider_length( $length ) {
		return get_post_meta( oceanwp_post_id(), 'troc_slider_length', true ) ?: $length;
	}

	public function troc_slider_speed( $speed ) {
		return get_post_meta( oceanwp_post_id(), 'troc_slider_speed', true ) ?: $speed;
	}

	public function troc_slider_autoplay( $autoplay ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_slider_autoplay', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $autoplay : ( 'enable' == $meta );
	}

	public function troc_slider_autostop( $autostop ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_slider_autostop', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $autostop : ( 'enable' == $meta );
	}

	public function troc_slider_autospeed( $autospeed ) {
		return get_post_meta( oceanwp_post_id(), 'troc_slider_autospeed', true ) ?: $autospeed;
	}

	public function troc_slider_arrows( $arrows ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_slider_arrows', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $arrows : ( 'enable' == $meta );
	}

	public function troc_slider_dots( $dots ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_slider_dots', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $dots : ( 'enable' == $meta );
	}

	public function troc_slider_caption( $caption ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_slider_caption', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $caption : ( 'enable' == $meta );
	}

	public function troc_slider_credit( $credit ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_slider_credit', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $credit : ( 'enable' == $meta );
	}

	public function troc_slider_lazyload( $lazyload ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_slider_lazyload', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $lazyload : $meta;
	}

	/* -------------------------------------------------------------------------
	 * Page Banner Callbacks
	 * ---------------------------------------------------------------------- */

	public function troc_has_banner( $has_banner ) {
		return $has_banner || $this->has_slider();
	}

	public function troc_banner_image( $image ) {
		if( $this->has_slider() ) {
			$gallery = $this->get_slider_gallery();
			$image   = reset( $gallery );
		}
		return $image;
	}

	public function troc_banner_position( $position ) {
		if( $this->has_slider() ) {
			$position = 'center center';
		}
		return $position;
	}

	public function troc_banner_attachment( $attachment ) {
		if( $this->has_slider() ) {
			$attachment = 'scroll';
		}
		return $attachment;
	}

	public function troc_before_page_banner_inner( $content ) {
		if( $this->has_slider() ) {
			$content .= $this->build_slider();
		}
		return $content;
	}

	/* -------------------------------------------------------------------------
	 * OceanWP Callbacks
	 * ---------------------------------------------------------------------- */

	/**
	 * Adds the 'has-page-slider' class to the HTML Body element.
	 *
	 * @param array $classes List of Body element class names.
	 * @return array Modified list of Body element class names.
	 */
	public function body_class( $classes ) {
		if( $this->has_slider() ) {
			$classes[] = 'has-page-slider';
		}
		return $classes;
	}

	/**
	 * Adds CSS to the HTML head.
	 *
	 * @param string $output CSS styles
	 * @return string Additional CSS styles
	 */
	public function ocean_head_css( $output ) {
		return $output;
	}

	/* -------------------------------------------------------------------------
	 * Wordpress Callbacks
	 * ---------------------------------------------------------------------- */

	/**
	 * Adds Plugin Stylesheets.
	 *
	 * @hooked wp_enqueue_scripts
	 */
	public function wp_enqueue_styles() {
		Asset::instance()->enqueue_style( 'assets/css/slider.min.css' );
	}

	/**
	 * Adds Plugin Javascript.
	 *
	 * @hooked wp_enqueue_scripts
	 */
	public function wp_enqueue_scripts() {
		Asset::instance()->enqueue_script( 'assets/js/slider.min.js', array( 'oceanwp-main' ) );
	}

	/**
	 * Removes the 'src' and 'srcset' attributes form images where lazy loading is enabled.
	 *
	 * @hooked wp_get_attachment_image_attributes
	 *
	 * @param array $attr Attributes for the image markup.
	 * @param WP_Post $attachment Image attachment post.
	 * @param string|array $size Requested size. Image size or array of width and height values (in that order).
	 * @return array Modified attributes for the image markup.
	 */
	public function wp_get_attachment_image_attributes( $attr, $attachment, $size ) {
		if( isset( $attr['lazyload'] ) && true === $attr['lazyload'] ) {
			$attr['data-lazy'] = $attr['src'];
			$attr['src'] = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mNkYAAAAAYAAjCB0C8AAAAASUVORK5CYII=';
			unset( $attr['lazyload'] );
			unset( $attr['srcset'] );
		}
		return $attr;
	}

	/* -------------------------------------------------------------------------
	 * Page Slider Customizer
	 * ---------------------------------------------------------------------- */

	/**
	 * Check if Page Slider Autoplay is enabled for Customizer Active Callback.
	 *
	 * @return bool True if Page Slider Autoplay is enabled.
	 */
	public function troc_cac_has_slider_autoplay() {
		return get_theme_mod( 'troc_slider_autoplay', false );
	}

	/**
	 * Adds Page Slider Settings to the Wordpress Customizer.
	 *
	 * @hooked customize_register
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function customize_register( $wp_customize ) {

		$section = 'troc_slider_section';
		$wp_customize->add_section( $section, array(
			'title'    => esc_html__( 'Page Slider', 'transparent-ocean' ),
			'panel'    => 'troc_panel',
			'priority' => 40,
		) );

		/**
		 * Enable Page Slider for post types
		 */
		$wp_customize->add_setting( 'troc_slider_enable', array(
			'default'           => array(),
			'sanitize_callback' => 'oceanwp_sanitize_multicheck',
		) );
		$wp_customize->add_control( new OceanWP_Customize_Multicheck_Control( $wp_customize, 'troc_slider_enable', array(
			'label'    => esc_html__( 'Display Page Sliders on', 'transparent-ocean' ) . ':',
			'section'  => $section,
			'settings' => 'troc_slider_enable',
			'priority' => 10,
			'choices'  => Twain::get_types(),
		) ) );

		/**
		 * Page Slider Style
		 */
		$wp_customize->add_setting( 'troc_slider_style', array(
			'transport'         => 'postMessage',
			'default'           => 'horizontal',
			'sanitize_callback' => 'oceanwp_sanitize_select',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_slider_style', array(
			'label'    => esc_html__( 'Sliding Style', 'oceanwp' ),
			'type'     => 'select',
			'section'  => $section,
			'settings' => 'troc_slider_style',
			'priority' => 10,
			'choices'  => array(
				'horizontal' => esc_html__( 'Horizontal', 'transparent-ocean' ),
				//	'vertical'   => esc_html__( 'Vertical', 'transparent-ocean' ),
				'fade'       => esc_html__( 'Fade', 'transparent-ocean' ),
			),
		) ) );

		/**
		 * Add own thumbnail to gallery
		 */
		$wp_customize->add_setting( 'troc_slider_thumb', array(
			'transport'         => 'postMessage',
			'default'           => 'disable',
			'sanitize_callback' => 'oceanwp_sanitize_select',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_slider_thumb', array(
			'label'    => esc_html__( 'Add own thumbnail to gallery', 'transparent-ocean' ),
			'type'     => 'select',
			'section'  => $section,
			'settings' => 'troc_slider_thumb',
			'priority' => 10,
			'choices'  => array(
				'disable' => esc_html__( 'Disable', 'transparent-ocean' ),
				'first'   => esc_html__( 'First slide', 'transparent-ocean' ),
				'last'    => esc_html__( 'Last slide', 'transparent-ocean' ),
			),
		) ) );

		/**
		 * Page Slider Slides
		 */
		$wp_customize->add_setting( 'troc_slider_length', array(
			'transport'         => 'postMessage',
			'default'           => 5,
			'sanitize_callback' => 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Range_Control( $wp_customize, 'troc_slider_length', array(
			'label'       => esc_html__( 'Number of slides to show', 'transparent-ocean' ),
			'section'     => $section,
			'settings'    => 'troc_slider_length',
			'priority'    => 10,
			'input_attrs' => array( 'min' => 0, 'max' => 20, 'step' => 1 ),
		) ) );

		/**
		 * Animation Speed
		 */
		$wp_customize->add_setting( 'troc_slider_speed', array(
			'transport'         => 'postMessage',
			'default'           => 500,
			'sanitize_callback' => 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Range_Control( $wp_customize, 'troc_slider_speed', array(
			'label'       => esc_html__( 'Slider Animation Speed', 'transparent-ocean' ) . ' (ms)',
			'section'     => $section,
			'settings'    => 'troc_slider_speed',
			'priority'    => 10,
			'input_attrs' => array( 'min' => 0, 'max' => 5000, 'step' => 50 ),
		) ) );

		/**
		 * Autoplay
		 */
		$wp_customize->add_setting( 'troc_slider_autoplay', array(
			'default'           => false,
			'sanitize_callback' => 'oceanwp_sanitize_checkbox',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_slider_autoplay', array(
			'label'    => esc_html__( 'Enable Autoplay', 'transparent-ocean' ),
			'type'     => 'checkbox',
			'section'  => $section,
			'settings' => 'troc_slider_autoplay',
			'priority' => 10,
		) ) );

		/**
		 * Autostop
		 */
		$wp_customize->add_setting( 'troc_slider_autostop', array(
			'default'           => true,
			'sanitize_callback' => 'oceanwp_sanitize_checkbox',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_slider_autostop', array(
			'label'           => esc_html__( 'Stop autoplay on last slide', 'transparent-ocean' ),
			'type'            => 'checkbox',
			'section'         => $section,
			'settings'        => 'troc_slider_autostop',
			'priority'        => 10,
			'active_callback' => array( $this, 'troc_cac_has_slider_autoplay' ),
		) ) );

		/**
		 * Autoplay Speed
		 */
		$wp_customize->add_setting( 'troc_slider_autospeed', array(
			'transport'         => 'postMessage',
			'default'           => 5,
			'sanitize_callback' => 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Range_Control( $wp_customize, 'troc_slider_autospeed', array(
			'label'           => esc_html__( 'Autoplay speed (sec)', 'transparent-ocean' ),
			'section'         => $section,
			'settings'        => 'troc_slider_autospeed',
			'priority'        => 10,
			'active_callback' => array( $this, 'troc_cac_has_slider_autoplay' ),
			'input_attrs'     => array( 'min' => 0, 'max' => 30, 'step' => 1 ),
		) ) );

		/**
		 * Navigation Arrows
		 */
		$wp_customize->add_setting( 'troc_slider_arrows', array(
			'default'           => true,
			'sanitize_callback' => 'oceanwp_sanitize_checkbox',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_slider_arrows', array(
			'label'    => esc_html__( 'Display navigation arrows', 'transparent-ocean' ),
			'type'     => 'checkbox',
			'section'  => $section,
			'settings' => 'troc_slider_arrows',
			'priority' => 10,
		) ) );

		/**
		 * Navigation Dots
		 */
		$wp_customize->add_setting( 'troc_slider_dots', array(
			'default'           => false,
			'sanitize_callback' => 'oceanwp_sanitize_checkbox',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_slider_dots', array(
			'label'    => esc_html__( 'Display navigation dots', 'transparent-ocean' ),
			'type'     => 'checkbox',
			'section'  => $section,
			'settings' => 'troc_slider_dots',
			'priority' => 10,
		) ) );

		/**
		 * Image Caption
		 */
		$wp_customize->add_setting( 'troc_slider_caption', array(
			'default'           => false,
			'sanitize_callback' => 'oceanwp_sanitize_checkbox',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_slider_caption', array(
			'label'    => esc_html__( 'Display image caption', 'transparent-ocean' ),
			'type'     => 'checkbox',
			'section'  => $section,
			'settings' => 'troc_slider_caption',
			'priority' => 10,
		) ) );

		/**
		 * Photo Credit
		 */
		$wp_customize->add_setting( 'troc_slider_credit', array(
			'default'           => false,
			'sanitize_callback' => 'oceanwp_sanitize_checkbox',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_slider_credit', array(
			'label'    => esc_html__( 'Display photo credit', 'transparent-ocean' ),
			'type'     => 'checkbox',
			'section'  => $section,
			'settings' => 'troc_slider_credit',
			'priority' => 10,
		) ) );

		/**
		 * Lazy Loading
		 */
		$wp_customize->add_setting( 'troc_slider_lazyload', array(
			'default'           => 'disable',
			'sanitize_callback' => 'oceanwp_sanitize_select',
		) );
		// OceanWP_Customizer_Buttonset_Control
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_slider_lazyload', array(
			'label'    => esc_html__( 'Lazy Loading', 'transparent-ocean' ),
			'type'     => 'select',
			'section'  => $section,
			'settings' => 'troc_slider_lazyload',
			'priority' => 10,
			'choices'  => array(
				'disable'     => esc_html__( 'Disable', 'transparent-ocean' ),
				'progressive' => esc_html__( 'Progressive', 'transparent-ocean' ),
				'ondemand'    => esc_html__( 'On Demand', 'transparent-ocean' ),
			),
		) ) );
	}

	/* -------------------------------------------------------------------------
	 * Page Slider Metabox
	 * ---------------------------------------------------------------------- */

	/**
	 * Adds Plugin Settings to the OceanWP Extra Metabox: Page Slider
	 *
	 * @hooked butterbean_register
	 *
	 * @param ButterBean_Manager $butterbean
	 * @param string $post_type Post Type
	 */
	public function butterbean_register( $butterbean, $post_type ) {
		$capabilities = apply_filters( 'ocean_main_metaboxes_capabilities', 'manage_options' );
		if( false == current_user_can( $capabilities ) ) {
			return;
		}

		// Gets the manager object we want to add sections to.
		$manager = $butterbean->get_manager( 'oceanwp_mb_settings' );
		$section = 'troc_slider_section';

		// Adds the "Page Slider" section to the OceanWP metabox.
		$manager->register_section( $section, array(
			'label' => esc_html__( 'Page Slider', 'transparent-ocean' ),
			'icon'  => 'dashicons-welcome-view-site',
		) );

		$manager->register_setting( 'troc_slider_enable', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_slider_enable', array(
			'section' => $section,
			'type'    => 'buttonset',
			'label'   => esc_html__( 'Enable Page Slider', 'transparent-ocean' ),
			'choices' => array(
				'default' => esc_html__( 'Default', 'oceanwp' ),
				'enable'  => esc_html__( 'Enable', 'oceanwp' ),
				'disable' => esc_html__( 'Disable', 'oceanwp' ),
			),
		) );

		$manager->register_setting( 'troc_slider_style', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_slider_style', array(
			'section' => $section,
			'type'    => 'select',
			'label'   => esc_html__( 'Sliding Style', 'ocean-extra' ),
			'choices' => array(
				'default'    => esc_html__( 'Default', 'ocean-extra' ),
				'horizontal' => esc_html__( 'Horizontal', 'ocean-extra' ),
				//	'vertical'   => esc_html__( 'Vertical', 'ocean-extra' ),
				'fade'       => esc_html__( 'Fade', 'transparent-ocean' ),
			),
		) );

		$manager->register_setting( 'troc_slider_thumb', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_slider_thumb', array(
			'section' => $section,
			'type'    => 'select',
			'label'   => esc_html__( 'Add own thumbnail to gallery', 'ocean-extra' ),
			'choices' => array(
				'default' => esc_html__( 'Default', 'ocean-extra' ),
				'disable' => esc_html__( 'Disable', 'ocean-extra' ),
				'first'   => esc_html__( 'First slide', 'ocean-extra' ),
				'last'    => esc_html__( 'Last slide', 'transparent-ocean' ),
			),
		) );

		$manager->register_setting( 'troc_slider_length', array(
			'sanitize_callback' => array( Common::instance(), 'sanitize_absint' ),
		) );
		$manager->register_control( 'troc_slider_length', array(
			'section' => $section,
			'type'    => 'number',
			'label'   => esc_html__( 'Number of slides to show', 'transparent-ocean' ),
			'attr'    => array( 'min' => '0', 'max' => '20', 'step' => '1' ),
		) );

		$manager->register_setting( 'troc_slider_speed', array(
			'sanitize_callback' => array( Common::instance(), 'sanitize_absint' ),
		) );
		$manager->register_control( 'troc_slider_speed', array(
			'section' => $section,
			'type'    => 'number',
			'label'   => esc_html__( 'Slider animation speed (ms)', 'transparent-ocean' ),
			'attr'    => array( 'min' => '0', 'max' => '5000', 'step' => '50' ),
		) );

		$manager->register_setting( 'troc_slider_autoplay', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_slider_autoplay', array(
			'section' => $section,
			'type'    => 'buttonset',
			'label'   => esc_html__( 'Autoplay', 'transparent-ocean' ),
			'choices' => array(
				'default' => esc_html__( 'Default', 'oceanwp' ),
				'enable'  => esc_html__( 'Enable', 'oceanwp' ),
				'disable' => esc_html__( 'Disable', 'oceanwp' ),
			),
		) );

		$manager->register_setting( 'troc_slider_autostop', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_slider_autostop', array(
			'section' => $section,
			'type'    => 'buttonset',
			'label'   => esc_html__( 'Stop autoplay on last slide', 'transparent-ocean' ),
			'choices' => array(
				'default' => esc_html__( 'Default', 'oceanwp' ),
				'enable'  => esc_html__( 'Enable', 'oceanwp' ),
				'disable' => esc_html__( 'Disable', 'oceanwp' ),
			),
		) );

		$manager->register_setting( 'troc_slider_autospeed', array(
			'sanitize_callback' => array( Common::instance(), 'sanitize_absint' ),
		) );
		$manager->register_control( 'troc_slider_autospeed', array(
			'section' => $section,
			'type'    => 'number',
			'label'   => esc_html__( 'Autoplay speed (sec)', 'transparent-ocean' ),
			'attr'    => array( 'min' => '0', 'max' => '30', 'step' => '1' ),
		) );

		$manager->register_setting( 'troc_slider_arrows', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_slider_arrows', array(
			'section' => $section,
			'type'    => 'buttonset',
			'label'   => esc_html__( 'Display Navigation Arrows', 'transparent-ocean' ),
			'choices' => array(
				'default' => esc_html__( 'Default', 'oceanwp' ),
				'enable'  => esc_html__( 'Enable', 'oceanwp' ),
				'disable' => esc_html__( 'Disable', 'oceanwp' ),
			),
		) );

		$manager->register_setting( 'troc_slider_dots', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_slider_dots', array(
			'section' => $section,
			'type'    => 'buttonset',
			'label'   => esc_html__( 'Display Navigation Dots', 'transparent-ocean' ),
			'choices' => array(
				'default' => esc_html__( 'Default', 'oceanwp' ),
				'enable'  => esc_html__( 'Enable', 'oceanwp' ),
				'disable' => esc_html__( 'Disable', 'oceanwp' ),
			),
		) );

		$manager->register_setting( 'troc_slider_caption', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_slider_caption', array(
			'section' => $section,
			'type'    => 'buttonset',
			'label'   => esc_html__( 'Display Image Caption', 'transparent-ocean' ),
			'choices' => array(
				'default' => esc_html__( 'Default', 'oceanwp' ),
				'enable'  => esc_html__( 'Enable', 'oceanwp' ),
				'disable' => esc_html__( 'Disable', 'oceanwp' ),
			),
		) );

		$manager->register_setting( 'troc_slider_credit', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_slider_credit', array(
			'section' => $section,
			'type'    => 'buttonset',
			'label'   => esc_html__( 'Display Photo Credit', 'transparent-ocean' ),
			'choices' => array(
				'default' => esc_html__( 'Default', 'oceanwp' ),
				'enable'  => esc_html__( 'Enable', 'oceanwp' ),
				'disable' => esc_html__( 'Disable', 'oceanwp' ),
			),
		) );

		$manager->register_setting( 'troc_slider_lazyload', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_slider_lazyload', array(
			'section'     => $section,
			'type'        => 'select',
			'label'       => esc_html__( 'Lazy Loading', 'ocean-extra' ),
			'description' => esc_html__( 'Select your background image size.', 'ocean-extra' ),
			'choices'     => array(
				'default'     => esc_html__( 'Default', 'ocean-extra' ),
				'disable'     => esc_html__( 'Disable', 'ocean-extra' ),
				'progressive' => esc_html__( 'Progressive', 'transparent-ocean' ),
				'ondemand'    => esc_html__( 'On Demand', 'transparent-ocean' ),
			),
		) );
	}
}