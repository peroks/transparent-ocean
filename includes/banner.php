<?php namespace silverscreen\wp\troc;
use OceanWP_Customize_Multicheck_Control;
use OceanWP_Customizer_Color_Control;
use OceanWP_Customizer_Heading_Control;
use OceanWP_Customizer_Range_Control;
use WP_Customize_Control;
use WP_Customize_Image_Control;
/**
 * Page Banner for OceanWP.
 *
 * @author     Per Egil Roksvaag
 * @license    MIT License
 */
class Banner {
	use Singleton;

	/**
	 * @var bool Cache for has_banner().
	 */
	protected $_has_banner = null;

	/**
	 * Constructor.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'template_redirect', array( $this, 'template_redirect' ) );
	}

	public function init() {
		// Wordpress Customizer
		add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );

		// OceanWP Metabox
		add_action( 'butterbean_register', array( $this, 'butterbean_register' ), 10, 2 );
		
		// Add Banner size to WP.
		add_image_size( 'troc-banner', 1280, 680, array( 'center', 'top' ) );
	}

	public function template_redirect() {
		if( false == $this->has_banner() ) {
			return;
		}

		// Page Banner callbacks
		add_filter( 'troc_banner_enable', array( $this, 'troc_banner_enable' ) );
		add_filter( 'troc_banner_image', array( $this, 'troc_banner_image' ) );
		add_filter( 'troc_banner_height', array( $this, 'troc_banner_height' ) );
		add_filter( 'troc_banner_position', array( $this, 'troc_banner_position' ) );
		add_filter( 'troc_banner_attachment', array( $this, 'troc_banner_attachment' ) );
		add_filter( 'troc_banner_repeat', array( $this, 'troc_banner_repeat' ) );
		add_filter( 'troc_banner_size', array( $this, 'troc_banner_size' ) );
		add_filter( 'troc_banner_color', array( $this, 'troc_banner_color' ) );

		// OceanWP callbacks
		add_filter( 'body_class', array( $this, 'body_class' ), 25 );
		add_filter( 'ocean_header_style', array( $this, 'ocean_header_style' ), 5 );
		add_action( 'ocean_after_header', array( $this, 'ocean_after_header' ), 25 );
		add_filter( 'ocean_head_css', array( $this, 'ocean_head_css' ), 25 );

		// Wordpress callbacks
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_styles' ), 25 );
		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ), 25 );
	}

	/* -------------------------------------------------------------------------
	 * Page Banner output
	 * ---------------------------------------------------------------------- */

	/**
	 * Builds the Page Banner.
	 *
	 * @return string The Page Banner HTML code.
	 */
	public function build_banner() {
		$data    = array();
		$classes = array( 'page-banner' );

		if( 'parallax' == $this->get_banner_attachment() ) {
			$data['paroller-factor']    = '0.4';
			$data['paroller-type']      = 'background';
			$data['paroller-direction'] = 'vertical';
			$classes[]                  = 'troc-parallax';
		}
		if( $attribute_id = $this->get_banner_image() ) {
			$meta = get_post_meta( $attribute_id, 'photo_credit_credit', true );
			$credit = $meta['credit'] ?? $meta['copyright'] ?? '';
			$credit && $data['credit'] = $credit;
		}
		$data = apply_filters( 'troc_banner_data', $data );
		$data = Common::instance()->data_attr_encode( $data );

		$classes = apply_filters( 'troc_banner_classes', $classes );
		$classes = count( $classes ) ? ' class="' . esc_attr( implode( ' ', $classes ) ) . '"' : '';

		$banner = '';
		$banner .= apply_filters( 'troc_before_page_banner', '' );
		$banner .= '<div' . $classes . $data . '>';
		$banner .= apply_filters( 'troc_before_page_banner_inner', '' );
		$banner .= apply_filters( 'troc_after_page_banner_inner', '' );
		$banner .= '</div>';
		$banner .= apply_filters( 'troc_after_page_banner', '' );

		return $banner;
	}

	/* -------------------------------------------------------------------------
	 * Get Page Banner Settings
	 * ---------------------------------------------------------------------- */

	/**
	 * Check if the current Page Type or Taxonomy is enabled for Page Banners
	 * and if a Banner image was found.
	 *
	 * @param $id int|WP_Term|WP_Post|Twain A Term or Post ID, object or wrapper.
	 * @param $type string The object type ('post' or 'term') to match the id.
	 * @return bool True if a Page Banner can be displayed, false otherwise.
	 */
	public function has_banner( $id = null, $type = 'post' ) {
		if( is_null( $this->_has_banner ) ) {
			$twain  = Twain::factory( $id, $type );
			$result = $this->is_banner_enabled( $twain ) && $twain->has_thumbnail();
			$this->_has_banner = apply_filters( 'troc_has_banner', $result );
		}
		return $this->_has_banner;
	}

	/**
	 * Check if Page Banners (and Sliders) are enabled for the current post type or taxonomy.
	 *
	 * @param $id int|WP_Term|WP_Post|Twain A Term or Post ID, object or wrapper.
	 * @param $type string The object type ('post' or 'term') to match the id.
	 * @return bool Returns true if Page Banners are enabled.
	 */
	public function is_banner_enabled( $id = null, $type = 'post' ) {
		if( Common::instance()->is_banner_active() ) {
			$type   = Twain::factory( $id, $type )->get_type();
			$enable = get_theme_mod( 'troc_banner_enable', array() ) ?: array();
			$result = $enable && is_array( $enable ) && in_array( $type, $enable );
			$result = $this->troc_banner_enable( $result );
			return apply_filters( 'troc_banner_enable', $result, $type );
		}
		return false;
	}

	/**
	 * Gets a Post or Term thumbnail to be displayed on the Page Banner.
	 *
	 * @param $id int|WP_Term|WP_Post|Twain A Term or Post ID, object or wrapper.
	 * @param $type string The object type ('post' or 'term') to match the id.
	 * @return int Attachment ID of the image to be displayed.
	 */
	public function get_banner_image( $id = null, $type = 'post' ) {
		$image = Twain::factory( $id, $type )->get_thumbnail_id();
		$image = $image ?: get_theme_mod( 'troc_banner_image' );
		return apply_filters( 'troc_banner_image', $image );
	}

	/**
	 * Gets the height of the Page Banner.
	 *
	 * @return int The height of the Page Banner in px.
	 */
	public function get_banner_height() {
		$height = get_theme_mod( 'troc_banner_height', 400 ) ?: 400;
		return apply_filters( 'troc_banner_height', $height );
	}

	/**
	 * Gets the color of the Page Banner overlay.
	 *
	 * @return string The color of the overlay.
	 */
	public function get_banner_color() {
		$color = get_theme_mod( 'troc_banner_color' );
		return apply_filters( 'troc_banner_color', $color );
	}

	/**
	 * Gets the position of the Page Banner background image.
	 *
	 * @return string The CSS value for the 'background-position' property.
	 */
	public function get_banner_position() {
		$position = get_theme_mod( 'troc_banner_position', 'center center' ) ?: 'center center';
		return apply_filters( 'troc_banner_position', $position );
	}

	/**
	 * Gets the attachment of the Page Banner background image.
	 *
	 * @return string The CSS value for the 'background-attachment' property: scroll, fixed, parallax.
	 */
	public function get_banner_attachment() {
		$attachment = get_theme_mod( 'troc_banner_attachment', 'scroll' ) ?: 'scroll';
		return apply_filters( 'troc_banner_attachment', $attachment );
	}

	/**
	 * Gets the repeat property of the Page Banner background image.
	 *
	 * @return string The CSS value for the 'background-repeat' property: no-repeat, repeat, repeat-x, repeat-y.
	 */
	public function get_banner_repeat() {
		$repeat = get_theme_mod( 'troc_banner_repeat', 'no-repeat' ) ?: 'no-repeat';
		return apply_filters( 'troc_banner_repeat', $repeat );
	}

	/**
	 * Gets the size property of the Page Banner background image.
	 *
	 * @return string The CSS value for the 'background-size' property: auto, cover, contain.
	 */
	public function get_banner_size() {
		$size = get_theme_mod( 'troc_banner_size', 'cover' ) ?: 'cover';
		return apply_filters( 'troc_banner_size', $size );
	}

	/* -------------------------------------------------------------------------
	 * Page Banner Callbacks to override the theme settings
	 * ---------------------------------------------------------------------- */

	public function troc_banner_enable( $enable ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_banner_enable', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $enable : ( 'enable' == $meta );
	}

	public function troc_banner_image( $image ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_banner_image', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $image : $meta;
	}

	public function troc_banner_height( $height ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_banner_height', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $height : $meta;
	}

	public function troc_banner_color( $color ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_banner_color', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $color : $meta;
	}

	public function troc_banner_position( $position ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_banner_position', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $position : $meta;
	}

	public function troc_banner_attachment( $attachment ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_banner_attachment', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $attachment : $meta;
	}

	public function troc_banner_repeat( $repeat ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_banner_repeat', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $repeat : $meta;
	}

	public function troc_banner_size( $size ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_banner_size', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $size : $meta;
	}

	/* -------------------------------------------------------------------------
	 * OceanWP Callbacks
	 * ---------------------------------------------------------------------- */

	/**
	 * Adds the 'has-page-banner' class to the HTML Body element.
	 *
	 * @hooked body_class
	 *
	 * @param array $classes List of HTML class names.
	 * @return array Modified list of HTML class names.
	 */
	public function body_class( $classes ) {
		if( $this->has_banner() ) {
			$classes[] = 'has-page-banner';
		}
		return $classes;
	}

	/**
	 * Sets the OceanWP Header Style to 'transparent' when a Page Banner is displayed.
	 *
	 * @hooked ocean_header_style
	 *
	 * @param string $style OceanWP Header Style.
	 * @return string Modified OceanWP Header Style.
	 */
	public function ocean_header_style( $style ) {
		if( false === strpos( $style, 'transparent' ) ) {
			if( $this->has_banner() && get_theme_mod( 'troc_banner_transparent', false ) ) {
				$style = apply_filters( 'troc_banner_header_style', 'transparent' );
			}
		}
		return $style;
	}

	/**
	 * Adds the Page Banner after the OceanWP Header
	 *
	 * @hooked ocean_after_header
	 *
	 * @return The Page Banner HTML element.
	 */
	public function ocean_after_header() {
		if( $this->has_banner() ) {
			echo $this->build_banner();
		}
	}

	/**
	 * Adds CSS to the HTML head.
	 *
	 * @hooked ocean_head_css
	 *
	 * @param string $output CSS styles
	 * @return string Additional CSS styles
	 */
	public function ocean_head_css( $output ) {
		if( $this->has_banner() ) {
			$banner_image  = $this->get_banner_image();
			$banner_height = $this->get_banner_height();
			$overlay_color = $this->get_banner_color();

			$banner  = '';
			$overlay = '';
			$css     = '';

			// Banner height
			if( !empty( $banner_height ) && 400 != $banner_height ) {
				$banner .= 'height:' . $banner_height . 'px;';
			}

			// Overlay Color
			if( !empty( $overlay_color ) ) {
				$overlay .= 'background-color:' . $overlay_color . ';';
			}

			if( $banner_image ) {
				$size   = 'troc-banner';
				$image  = wp_get_attachment_image_src( $banner_image, $size );
				$image  = reset( $image );
				$banner .= 'background-image:url(' . $image . ');';

				$banner_position   = $this->get_banner_position();
				$banner_attachment = $this->get_banner_attachment();
				$banner_repeat     = $this->get_banner_repeat();
				$banner_size       = $this->get_banner_size();

				// Background position
				if( !empty( $banner_position ) && !in_array( $banner_position, array( 'default', 'center center' ) ) ) {
					$banner .= 'background-position:' . $banner_position . ';';
				}

				// Background attachment
				if( !empty( $banner_attachment ) && !in_array( $banner_attachment, array( 'default', 'scroll', 'parallax' ) ) ) {
					$banner .= 'background-attachment:' . $banner_attachment . ';';
				}

				// Background repeat
				if( !empty( $banner_repeat ) && !in_array( $banner_repeat, array( 'default', 'no-repeat' ) ) ) {
					$banner .= 'background-repeat:' . $banner_repeat . ';';
				}

				// Background size
				if( !empty( $banner_size ) && !in_array( $banner_size, array( 'default', 'cover' ) ) ) {
					$banner .= 'background-size:' . $banner_size . ';';
				}
			}

			if( !empty( $banner ) ) {
				$css .= '.page-banner{' . $banner . '}';
			}
			if( !empty( $overlay ) ) {
				$css .= '.page-banner:before{' . $overlay . '}';
			}
			if( !empty( $css ) ) {
				$output .= '/* Page Banner CSS */' . $css;
			}
		}
		return $output;
	}

	/* -------------------------------------------------------------------------
	 * Wordpress Callbacks
	 * ---------------------------------------------------------------------- */

	/**
	 * Adds Page Banner Stylesheets.
	 *
	 * @hooked wp_enqueue_scripts
	 */
	public function wp_enqueue_styles() {
		Asset::instance()->enqueue_style( 'assets/css/banner.min.css' );
	}

	/**
	 * Adds Page Banner Javascript.
	 *
	 * @hooked wp_enqueue_scripts
	 */
	public function wp_enqueue_scripts() {
		$paroller = Asset::instance()->enqueue_script( 'assets/js/third/jquery.paroller.min.js' );
		Asset::instance()->enqueue_script( 'assets/js/banner.min.js', array( $paroller ) );
	}

	/* -------------------------------------------------------------------------
	 * Wordpress Customizer
	 * ---------------------------------------------------------------------- */

	/**
	 * Loads js file for customizer preview
	 *
	 * @hooked customize_preview_init
	 */
	public function customize_preview_init() {
		Asset::instance()->enqueue_script( 'assets/js/customizer/banner.min.js', array( 'customize-preview' ) );
	}

	/**
	 * Adds Page Banner Settings to the Wordpress Customizer.
	 *
	 * @hooked customize_register
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function customize_register( $wp_customize ) {
		$section = 'troc_banner_section';
		$wp_customize->add_section( $section, array(
			'title'    => esc_html__( 'Page Banner', 'transparent-ocean' ),
			'panel'    => 'troc_panel',
			'priority' => 30,
		) );

		/**
		 * Enable Page Banner for post types
		 */
		$wp_customize->add_setting( 'troc_banner_enable', array(
			'default'           => array(),
			'sanitize_callback' => 'oceanwp_sanitize_multicheck',
		) );
		$wp_customize->add_control( new OceanWP_Customize_Multicheck_Control( $wp_customize, 'troc_banner_enable', array(
			'label'    => esc_html__( 'Display Page Banners on', 'transparent-ocean' ) . ':',
			'section'  => $section,
			'settings' => 'troc_banner_enable',
			'priority' => 10,
			'choices'  => Twain::get_types(),
		) ) );

		/**
		 * Transparent Header
		 */
		$wp_customize->add_setting( 'troc_banner_transparent', array(
			'default'           => false,
			'sanitize_callback' => 'oceanwp_sanitize_checkbox',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_banner_transparent', array(
			'label'    => esc_html__( 'Display with transparent header', 'transparent-ocean' ),
			'type'     => 'checkbox',
			'section'  => $section,
			'settings' => 'troc_banner_transparent',
			'priority' => 10,
		) ) );

		/**
		 * Page Banner Height
		 */
		$wp_customize->add_setting( 'troc_banner_height', array(
			'transport'         => 'postMessage',
			'default'           => '400',
			'sanitize_callback' => 'oceanwp_sanitize_number',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Range_Control( $wp_customize, 'troc_banner_height', array(
			'label'       => esc_html__( 'Height (px)', 'oceanwp' ),
			'section'     => $section,
			'settings'    => 'troc_banner_height',
			'priority'    => 10,
			'input_attrs' => array( 'min' => '0', 'max' => '800', 'step' => '1' ),
		) ) );

		/**
		 * Page Banner Image
		 */
		$wp_customize->add_setting( 'troc_banner_image', array(
			'default'           => '',
			'sanitize_callback' => array( Common::instance(), 'sanitize_image' ),
		) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'troc_banner_image', array(
			'label'       => esc_html__( 'Default Banner Image', 'transparent-ocean' ),
			'description' => esc_html__( 'Fallback if no "Featured Image" is present.', 'transparent-ocean' ),
			'section'     => $section,
			'settings'    => 'troc_banner_image',
			'priority'    => 10,
		) ) );

		/**
		 * Page Banner Overlay Color
		 */
		$wp_customize->add_setting( 'troc_banner_color', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_banner_color', array(
			'label'    => esc_html__( 'Overlay Color', 'oceanwp' ),
			'section'  => $section,
			'settings' => 'troc_banner_color',
			'priority' => 10,
		) ) );

		$wp_customize->add_setting( 'troc_banner_bg_heading', array(
			'sanitize_callback' => 'wp_kses',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Heading_Control( $wp_customize, 'troc_banner_bg_heading', array(
			'label'    => esc_html__( 'Background Image', 'transparent-ocean' ),
			'section'  => $section,
			'priority' => 10,
		) ) );

		/**
		 * Page Banner Image Position
		 */
		$wp_customize->add_setting( 'troc_banner_position', array(
			'transport'         => 'postMessage',
			'default'           => 'default',
			'sanitize_callback' => 'sanitize_text_field',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_banner_position', array(
			'label'    => esc_html__( 'Position', 'oceanwp' ),
			'type'     => 'select',
			'section'  => $section,
			'settings' => 'troc_banner_position',
			'priority' => 10,
			'choices'  => array(
				'default'       => esc_html__( 'Default', 'oceanwp' ),
				'top left'      => esc_html__( 'Top Left', 'oceanwp' ),
				'top center'    => esc_html__( 'Top Center', 'oceanwp' ),
				'top right'     => esc_html__( 'Top Right', 'oceanwp' ),
				'center left'   => esc_html__( 'Center Left', 'oceanwp' ),
				'center center' => esc_html__( 'Center Center', 'oceanwp' ),
				'center right'  => esc_html__( 'Center Right', 'oceanwp' ),
				'bottom left'   => esc_html__( 'Bottom Left', 'oceanwp' ),
				'bottom center' => esc_html__( 'Bottom Center', 'oceanwp' ),
				'bottom right'  => esc_html__( 'Bottom Right', 'oceanwp' ),
			),
		) ) );

		/**
		 * Page Banner Image Attachment
		 */
		$wp_customize->add_setting( 'troc_banner_attachment', array(
			'transport'         => 'postMessage',
			'default'           => 'default',
			'sanitize_callback' => 'oceanwp_sanitize_select',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_banner_attachment', array(
			'label'    => esc_html__( 'Attachment', 'oceanwp' ),
			'type'     => 'select',
			'section'  => $section,
			'settings' => 'troc_banner_attachment',
			'priority' => 10,
			'choices'  => array(
				'default'  => esc_html__( 'Default', 'oceanwp' ),
				'scroll'   => esc_html__( 'Scroll', 'oceanwp' ),
				'fixed'    => esc_html__( 'Fixed', 'oceanwp' ),
				'parallax' => esc_html__( 'Parallax', 'transparent-ocean' ),
			),
		) ) );

		/**
		 * Page Banner Image Repeat
		 */
		$wp_customize->add_setting( 'troc_banner_repeat', array(
			'transport'         => 'postMessage',
			'default'           => 'default',
			'sanitize_callback' => 'oceanwp_sanitize_select',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_banner_repeat', array(
			'label'    => esc_html__( 'Repeat', 'oceanwp' ),
			'type'     => 'select',
			'section'  => $section,
			'settings' => 'troc_banner_repeat',
			'priority' => 10,
			'choices'  => array(
				'default'   => esc_html__( 'Default', 'oceanwp' ),
				'no-repeat' => esc_html__( 'No-repeat', 'oceanwp' ),
				'repeat'    => esc_html__( 'Repeat', 'oceanwp' ),
				'repeat-x'  => esc_html__( 'Repeat-x', 'oceanwp' ),
				'repeat-y'  => esc_html__( 'Repeat-y', 'oceanwp' ),
			),
		) ) );

		/**
		 * Page Banner Image Size
		 */
		$wp_customize->add_setting( 'troc_banner_size', array(
			'transport'         => 'postMessage',
			'default'           => 'default',
			'sanitize_callback' => 'oceanwp_sanitize_select',
		) );
		$wp_customize->add_control( new WP_Customize_Control( $wp_customize, 'troc_banner_size', array(
			'label'    => esc_html__( 'Size', 'oceanwp' ),
			'type'     => 'select',
			'section'  => $section,
			'settings' => 'troc_banner_size',
			'priority' => 10,
			'choices'  => array(
				'default' => esc_html__( 'Default', 'oceanwp' ),
				'auto'    => esc_html__( 'Auto', 'oceanwp' ),
				'cover'   => esc_html__( 'Cover', 'oceanwp' ),
				'contain' => esc_html__( 'Contain', 'oceanwp' ),
			),
		) ) );
	}

	/* -------------------------------------------------------------------------
	 * OceanWP Extra Metabox
	 * ---------------------------------------------------------------------- */

	/**
	 * Adds Plugin Settings to the OceanWP Extra Metabox: Page Banner
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
		$section = 'troc_banner_section';

		// Adds the "Page Banner" section to the OceanWP metabox.
		$manager->register_section( $section, array(
			'label' => esc_html__( 'Page Banner', 'transparent-ocean' ),
			'icon'  => 'dashicons-welcome-view-site',
		) );

		$manager->register_setting( 'troc_banner_enable', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_banner_enable', array(
			'section'     => $section,
			'type'        => 'buttonset',
			'label'       => esc_html__( 'Enable Page Banner', 'transparent-ocean' ),
			'description' => esc_html__( 'Choose the default sticky style on this page/post.', 'transparent-ocean' ),
			'choices'     => array(
				'default' => esc_html__( 'Default', 'oceanwp' ),
				'enable'  => esc_html__( 'Enable', 'oceanwp' ),
				'disable' => esc_html__( 'Disable', 'oceanwp' ),
			),
		) );

		$manager->register_setting( 'troc_banner_height', array(
			'sanitize_callback' => array( Common::instance(), 'sanitize_absint' ),
		) );
		$manager->register_control( 'troc_banner_height', array(
			'section'     => $section,
			'type'        => 'number',
			'label'       => esc_html__( 'Banner Height', 'transparent-ocean' ),
			'description' => esc_html__( 'Select your custom height for your page banner', 'transparent-ocean' ),
			'attr'        => array( 'min' => '0', 'max' => '800', 'step' => '1' ),
		) );

		$manager->register_setting( 'troc_banner_image', array(
			'sanitize_callback' => 'sanitize_key',
		) );
		$manager->register_control( 'troc_banner_image', array(
			'section'     => $section,
			'type'        => 'image',
			'label'       => esc_html__( 'Page Banner Image', 'transparent-ocean' ),
			'description' => esc_html__( 'Select a custom image instead of the "Featured Image" for your page banner.', 'transparent-ocean' ),
		) );

		$manager->register_setting( 'troc_banner_color', array(
			'sanitize_callback' => 'butterbean_maybe_hash_hex_color',
		) );
		$manager->register_control( 'troc_banner_color', array(
			'section'     => $section,
			'type'        => 'rgba-color',
			'label'       => esc_html__( 'Banner Overlay Color', 'transparent-ocean' ),
			'description' => esc_html__( 'Select a color. Hex code, ex: #333', 'ocean-extra' ),
		) );

		$manager->register_setting( 'troc_banner_position', array(
			'sanitize_callback' => 'sanitize_text_field',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_banner_position', array(
			'section'     => $section,
			'type'        => 'select',
			'label'       => esc_html__( 'Position', 'ocean-extra' ),
			'description' => esc_html__( 'Select your background image position.', 'ocean-extra' ),
			'choices'     => array(
				'default'       => esc_html__( 'Default', 'ocean-extra' ),
				'top left'      => esc_html__( 'Top Left', 'ocean-extra' ),
				'top center'    => esc_html__( 'Top Center', 'ocean-extra' ),
				'top right'     => esc_html__( 'Top Right', 'ocean-extra' ),
				'center left'   => esc_html__( 'Center Left', 'ocean-extra' ),
				'center center' => esc_html__( 'Center Center', 'ocean-extra' ),
				'center right'  => esc_html__( 'Center Right', 'ocean-extra' ),
				'bottom left'   => esc_html__( 'Bottom Left', 'ocean-extra' ),
				'bottom center' => esc_html__( 'Bottom Center', 'ocean-extra' ),
				'bottom right'  => esc_html__( 'Bottom Right', 'ocean-extra' ),
			),
		) );

		$manager->register_setting( 'troc_banner_attachment', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_banner_attachment', array(
			'section'     => $section,
			'type'        => 'select',
			'label'       => esc_html__( 'Attachment', 'ocean-extra' ),
			'description' => esc_html__( 'Select your background image attachment.', 'ocean-extra' ),
			'choices'     => array(
				'default'  => esc_html__( 'Default', 'ocean-extra' ),
				'scroll'   => esc_html__( 'Scroll', 'ocean-extra' ),
				'fixed'    => esc_html__( 'Fixed', 'ocean-extra' ),
				'parallax' => esc_html__( 'Parallax', 'transparent-ocean' ),
			),
		) );

		$manager->register_setting( 'troc_banner_repeat', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_banner_repeat', array(
			'section'     => $section,
			'type'        => 'select',
			'label'       => esc_html__( 'Repeat', 'ocean-extra' ),
			'description' => esc_html__( 'Select your background image repeat.', 'ocean-extra' ),
			'choices'     => array(
				'default'   => esc_html__( 'Default', 'ocean-extra' ),
				'no-repeat' => esc_html__( 'No-repeat', 'ocean-extra' ),
				'repeat'    => esc_html__( 'Repeat', 'ocean-extra' ),
				'repeat-x'  => esc_html__( 'Repeat-x', 'ocean-extra' ),
				'repeat-y'  => esc_html__( 'Repeat-y', 'ocean-extra' ),
			),
		) );

		$manager->register_setting( 'troc_banner_size', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_banner_size', array(
			'section'     => $section,
			'type'        => 'select',
			'label'       => esc_html__( 'Size', 'ocean-extra' ),
			'description' => esc_html__( 'Select your background image size.', 'ocean-extra' ),
			'choices'     => array(
				'default' => esc_html__( 'Default', 'ocean-extra' ),
				'auto'    => esc_html__( 'Auto', 'ocean-extra' ),
				'cover'   => esc_html__( 'Cover', 'ocean-extra' ),
				'contain' => esc_html__( 'Contain', 'ocean-extra' ),
			),
		) );
	}
}