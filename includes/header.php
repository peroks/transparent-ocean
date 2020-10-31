<?php namespace silverscreen\wp\troc;
use OceanWP_Customizer_Buttonset_Control;
use OceanWP_Customizer_Color_Control;
use OceanWP_Customizer_Heading_Control;
use WP_Customize_Image_Control;
/**
 * Transparent Header for OceanWP.
 *
 * @author     Per Egil Roksvaag
 * @license    MIT License
 */
class Header {
	use Singleton;

	/**
	 * @var array Buffer for OceanWP header settings.
	 */
	protected $solid = array();

	/**
	 * Constructor.
	 */
	protected function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_action( 'get_header', array( $this, 'get_header' ) );
	}

	/* -------------------------------------------------------------------------
	 * Helpers
	 * ---------------------------------------------------------------------- */

	public function sticky_inherit() {
		return apply_filters( 'troc_header_sticky_inherit', get_theme_mod( 'troc_header_sticky_inherit', 'ocean' ) );
	}

	/* -------------------------------------------------------------------------
	 * Callbacks
	 * ---------------------------------------------------------------------- */

	public function init() {
		// Wordpress Cusomizer
		add_action( 'customize_preview_init', array( $this, 'customize_preview_init' ) );
		add_action( 'customize_register', array( $this, 'customize_register' ) );

		// OceanWP Metabox
		add_action( 'butterbean_register', array( $this, 'butterbean_register' ), 10, 2 );
	}

	public function get_header() {
		if( false === strpos( oceanwp_header_style(), 'transparent' ) ) {
			return;
		}

		// Outputs css in html head
		add_filter( 'ocean_head_css', array( $this, 'ocean_head_css' ) );

		/* -------------------------------------------------------------------------
		 * Sticky Header Heading
		 * ---------------------------------------------------------------------- */

		// Sticky header default colors
		add_filter( 'troc_header_sticky_inherit', array( $this, 'troc_header_sticky_inherit' ) );

		/* -------------------------------------------------------------------------
		 * Logo Heading
		 * ---------------------------------------------------------------------- */

		// Custom Logo
		add_filter( 'theme_mod_custom_logo', array( $this, 'theme_mod_custom_logo' ), 25 );
		add_filter( 'osh_sticky_logo', array( $this, 'osh_sticky_logo' ), 25 );

		add_filter( 'theme_mod_ocean_retina_logo', array( $this, 'theme_mod_ocean_retina_logo' ), 25 );
		add_filter( 'osh_retina_sticky_logo', array( $this, 'osh_retina_sticky_logo' ), 25 );

		// Text logo color
		add_filter( 'theme_mod_ocean_logo_color', array( $this, 'theme_mod_ocean_logo_color' ), 25 );
		add_filter( 'troc_header_logo_color', array( $this, 'troc_header_logo_color' ) );

		// Text logo hover color
		add_filter( 'theme_mod_ocean_logo_hover_color', array( $this, 'theme_mod_ocean_logo_hover_color' ), 25 );
		add_filter( 'troc_header_logo_hover_color', array( $this, 'troc_header_logo_hover_color' ) );

		/* -------------------------------------------------------------------------
		 * Menu Heading
		 * ---------------------------------------------------------------------- */

		// Menu link color
		add_filter( 'theme_mod_ocean_menu_link_color', array( $this, 'theme_mod_ocean_menu_link_color' ), 25 );
		add_filter( 'osh_links_color', array( $this, 'osh_links_color' ), 25 );

		// Menu link hover color
		add_filter( 'theme_mod_ocean_menu_link_color_hover', array( $this, 'theme_mod_ocean_menu_link_color_hover' ), 25 );
		add_filter( 'osh_links_hover_color', array( $this, 'osh_links_hover_color' ), 25 );

		// Menu link active color
		add_filter( 'theme_mod_ocean_menu_link_color_active', array( $this, 'theme_mod_ocean_menu_link_color_active' ), 25 );
		add_filter( 'osh_links_active_color', array( $this, 'osh_links_active_color' ), 25 );

		// Menu link background color
		add_filter( 'theme_mod_ocean_menu_link_background', array( $this, 'theme_mod_ocean_menu_link_background' ), 25 );
		add_filter( 'osh_links_bg_color', array( $this, 'osh_links_bg_color' ), 25 );

		// Menu link hover background color
		add_filter( 'theme_mod_ocean_menu_link_hover_background', array( $this, 'theme_mod_ocean_menu_link_hover_background' ), 25 );
		add_filter( 'osh_links_hover_bg_color', array( $this, 'osh_links_hover_bg_color' ), 25 );

		// Menu link active background color
		add_filter( 'theme_mod_ocean_menu_link_active_background', array( $this, 'theme_mod_ocean_menu_link_active_background' ), 25 );
		add_filter( 'osh_links_active_bg_color', array( $this, 'osh_links_active_bg_color' ), 25 );

		/* -------------------------------------------------------------------------
		 * Social Menu Heading
		 * ---------------------------------------------------------------------- */

		// Social menu link color
		add_filter( 'theme_mod_ocean_menu_social_links_color', array( $this, 'theme_mod_ocean_menu_social_links_color' ), 25 );
		add_filter( 'osh_menu_social_links_color', array( $this, 'osh_menu_social_links_color' ), 25 );

		// Social menu link hover color
		add_filter( 'theme_mod_ocean_menu_social_hover_links_color', array( $this, 'theme_mod_ocean_menu_social_hover_links_color' ), 25 );
		add_filter( 'osh_menu_social_hover_links_color', array( $this, 'osh_menu_social_hover_links_color' ), 25 );
	}

	/**
	 * Get CSS
	 */
	public function ocean_head_css( $output ) {
		$css = '';
		$css .= '#menu-main .wishlist_products_counter:before{color:inherit;}';

		if( $color = get_theme_mod( 'ocean_transparent_header_bg' ) ) {
			$css .= '#site-header.transparent-header:before{content:"";position:absolute;width:100%;height:200%;}';
			//	$css .= '#site-header.transparent-header:before{background:' . $color . ';}';
			$css .= '#site-header.transparent-header:before{background:linear-gradient(' . $color . ',rgba(0,0,0,0));}';
			$css .= '#site-header.transparent-header{background:rgba(0,0,0,0);}';
			$css .= '.is-sticky #site-header.transparent-header:before{background:rgba(0,0,0,0);}';
		}

		if( 'ocean' == $this->sticky_inherit() ) {
			// Sticky Header logo color
			if( $color = apply_filters( 'troc_header_sticky_logo_color', get_theme_mod( 'troc_header_sticky_logo_color', $this->solid['logo_color'] ?? '' ) ) ) {
				$css .= '.is-sticky #site-logo a.site-logo-text{color:' . $color . ';}';
			}
			// Sticky Header logo hover color
			if( $color = apply_filters( 'troc_header_sticky_logo_hover_color', get_theme_mod( 'troc_header_sticky_logo_hover_color', $this->solid['logo_hover_color'] ?? '' ) ) ) {
				$css .= '.is-sticky #site-logo a.site-logo-text:hover{color:' . $color . ';}';
			}
		}
		return $output .= '/* Transparent Header CSS */' . $css;
	}

	/* -------------------------------------------------------------------------
	 * Sticky Heading callbacks
	 * ---------------------------------------------------------------------- */

	/**
	 * Sticky Header default colors
	 */
	public function troc_header_sticky_inherit( $inherit ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_header_sticky_inherit', true );
		return empty( $meta ) || ( 'default' == $meta ) ? $inherit : $meta;
	}

	/* -------------------------------------------------------------------------
	 * Logo Heading callbacks
	 * ---------------------------------------------------------------------- */

	/**
	 * @ignore, just testing.
	 */
	public function theme_mod_ocean_override( $setting ) {
		$key = str_replace( 'theme_mod_ocean_', '', current_filter() );
		$mod = str_replace( 'theme_mod_ocean_', 'troc_header_', current_filter() );

		if( $value = apply_filters( $mod, get_theme_mod( $mod ) ) ) {
			$this->solid[$key] = $setting;
			return $value;
		}
		return $setting;
	}

	/**
	 * Custom logo
	 */
	public function theme_mod_custom_logo( $id ) {
		if( $value = apply_filters( 'troc_header_custom_logo', get_theme_mod( 'troc_header_custom_logo' ) ) ) {
			$this->solid['custom_logo'] = $id;
			return is_numeric( $value ) ? $value : attachment_url_to_postid( $value );
		}
		return $id;
	}

	public function osh_sticky_logo( $url ) {
		if( empty( $url ) && !empty( $this->solid['custom_logo'] ) && 'ocean' == $this->sticky_inherit() ) {
			$logo = wp_get_attachment_image_src( $this->solid['custom_logo'], 'full' );
			return $logo[0];
		}
		return $url;
	}

	/**
	 * Retina logo
	 */
	public function theme_mod_ocean_retina_logo( $url ) {
		if( $value = apply_filters( 'troc_header_retina_logo', get_theme_mod( 'troc_header_retina_logo' ) ) ) {
			$this->solid['retina_logo'] = $url;
			return $value;
		}
		return $url;
	}

	public function osh_retina_sticky_logo( $url ) {
		if( empty( $url ) && !empty( $this->solid['retina_logo'] ) && 'ocean' == $this->sticky_inherit() ) {
			return apply_filters( 'ocean_retina_logo', $this->solid['retina_logo'] );
		}
		return $url;
	}

	/**
	 * Text logo color
	 */
	public function theme_mod_ocean_logo_color( $color ) {
		if( $value = apply_filters( 'troc_header_logo_color', get_theme_mod( 'troc_header_logo_color' ) ) ) {
			$this->solid['logo_color'] = $color;
			return $value;
		}
		return $color;
	}

	public function troc_header_logo_color( $color ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_header_logo_color', true );
		return $meta ?: $color;
	}

	/**
	 * Text logo hover color
	 */
	public function theme_mod_ocean_logo_hover_color( $color ) {
		if( $value = apply_filters( 'troc_header_logo_hover_color', get_theme_mod( 'troc_header_logo_hover_color' ) ) ) {
			$this->solid['logo_hover_color'] = $color;
			return $value;
		}
		return $color;
	}

	public function troc_header_logo_hover_color( $color ) {
		$meta = get_post_meta( oceanwp_post_id(), 'troc_header_logo_hover_color', true );
		return $meta ?: $color;
	}

	/* -------------------------------------------------------------------------
	 * Menu Heading callbacks
	 * ---------------------------------------------------------------------- */

	/**
	 * Menu link color
	 */
	public function theme_mod_ocean_menu_link_color( $color ) {
		if( $value = apply_filters( 'troc_header_menu_link_color', get_theme_mod( 'troc_header_menu_link_color' ) ) ) {
			$this->solid['menu_link_color'] = $color;
			return $value;
		}
		return $color;
	}

	public function osh_links_color( $color ) {
		if( empty( $color ) && !empty( $this->solid['menu_link_color'] ) && 'ocean' == $this->sticky_inherit() ) {
			return apply_filters( 'ocean_menu_link_color', $this->solid['menu_link_color'] );
		}
		return $color;
	}

	/**
	 * Menu link hover color
	 */
	public function theme_mod_ocean_menu_link_color_hover( $color ) {
		if( $value = apply_filters( 'troc_header_menu_link_color_hover', get_theme_mod( 'troc_header_menu_link_color_hover' ) ) ) {
			$this->solid['menu_link_color_hover'] = $color;
			return $value;
		}
		return $color;
	}

	public function osh_links_hover_color( $color ) {
		if( empty( $color ) && !empty( $this->solid['menu_link_color_hover'] ) && 'ocean' == $this->sticky_inherit() ) {
			return apply_filters( 'ocean_menu_link_color_hover', $this->solid['menu_link_color_hover'] );
		}
		return $color;
	}

	/**
	 * Menu link active color
	 */
	public function theme_mod_ocean_menu_link_color_active( $color ) {
		if( $value = apply_filters( 'troc_header_menu_link_color_active', get_theme_mod( 'troc_header_menu_link_color_active' ) ) ) {
			$this->solid['menu_link_color_active'] = $color;
			return $value;
		}
		return $color;
	}

	public function osh_links_active_color( $color ) {
		if( empty( $color ) && !empty( $this->solid['menu_link_color_active'] ) && 'ocean' == $this->sticky_inherit() ) {
			return apply_filters( 'ocean_menu_link_color_active', $this->solid['menu_link_color_active'] );
		}
		return $color;
	}

	/**
	 * Menu link background color
	 */
	public function theme_mod_ocean_menu_link_background( $color ) {
		if( $value = apply_filters( 'troc_header_menu_link_background', get_theme_mod( 'troc_header_menu_link_background' ) ) ) {
			$this->solid['menu_link_background'] = $color;
			return $value;
		}
		return $color;
	}

	public function osh_links_bg_color( $color ) {
		if( empty( $color ) && !empty( $this->solid['menu_link_background'] ) && 'ocean' == $this->sticky_inherit() ) {
			return apply_filters( 'ocean_menu_link_background', $this->solid['menu_link_background'] );
		}
		return $color;
	}

	/**
	 * Menu link hover background color
	 */
	public function theme_mod_ocean_menu_link_hover_background( $color ) {
		if( $value = apply_filters( 'troc_header_menu_link_hover_background', get_theme_mod( 'troc_header_menu_link_hover_background' ) ) ) {
			$this->solid['menu_link_hover_background'] = $color;
			return $value;
		}
		return $color;
	}

	public function osh_links_hover_bg_color( $color ) {
		if( empty( $color ) && !empty( $this->solid['menu_link_hover_background'] ) && 'ocean' == $this->sticky_inherit() ) {
			return apply_filters( 'ocean_menu_link_hover_background', $this->solid['menu_link_hover_background'] );
		}
		return $color;
	}

	/**
	 * Menu link hover background color
	 */
	public function theme_mod_ocean_menu_link_active_background( $color ) {
		if( $value = apply_filters( 'troc_header_menu_link_active_background', get_theme_mod( 'troc_header_menu_link_active_background' ) ) ) {
			$this->solid['menu_link_active_background'] = $color;
			return $value;
		}
		return $color;
	}

	public function osh_links_active_bg_color( $color ) {
		if( empty( $color ) && !empty( $this->solid['menu_link_active_background'] ) && 'ocean' == $this->sticky_inherit() ) {
			return apply_filters( 'ocean_menu_link_active_background', $this->solid['menu_link_active_background'] );
		}
		return $color;
	}

	/* -------------------------------------------------------------------------
	 * Social Menu Heading callbacks
	 * ---------------------------------------------------------------------- */

	/**
	 * Social menu links color
	 */
	public function theme_mod_ocean_menu_social_links_color( $color ) {
		if( $value = apply_filters( 'troc_header_menu_social_links_color', get_theme_mod( 'troc_header_menu_social_links_color' ) ) ) {
			$this->solid['menu_social_links_color'] = $color;
			return $value;
		}
		return $color;
	}

	public function osh_menu_social_links_color( $color ) {
		if( empty( $color ) && !empty( $this->solid['menu_social_links_color'] ) && 'ocean' == $this->sticky_inherit() ) {
			return apply_filters( 'ocean_menu_social_links_color', $this->solid['menu_social_links_color'] );
		}
		return $color;
	}

	/**
	 * Social menu link hover color
	 */
	public function theme_mod_ocean_menu_social_hover_links_color( $color ) {
		if( $value = apply_filters( 'troc_header_menu_social_hover_links_color', get_theme_mod( 'troc_header_menu_social_hover_links_color' ) ) ) {
			$this->solid['menu_social_hover_links_color'] = $color;
			return $value;
		}
		return $color;
	}

	public function osh_menu_social_hover_links_color( $color ) {
		if( empty( $color ) && !empty( $this->solid['menu_social_hover_links_color'] ) && 'ocean' == $this->sticky_inherit() ) {
			return apply_filters( 'ocean_menu_social_hover_links_color', $this->solid['menu_social_hover_links_color'] );
		}
		return $color;
	}

	/* =========================================================================
	 * Wordpress Customizer
	 * ====================================================================== */

	/**
	 * Loads js file for customizer preview
	 */
	public function customize_preview_init() {
		Asset::instance()->enqueue_script( 'assets/js/customizer/header.min.js', array( 'customize-preview' ) );
	}

	/**
	 * Customizer Controls and settings
	 *
	 * @param WP_Customize_Manager $wp_customize Theme Customizer object.
	 */
	public function customize_register( $wp_customize ) {
		$section = 'troc_header_section';
		$wp_customize->add_section( $section, array(
			'title'    => esc_html__( 'Transparent Header', 'transparent-ocean' ),
			'panel'    => 'troc_panel',
			'priority' => 20,
		) );

		/* -------------------------------------------------------------------------
		 * Sticky Header Heading
		 * ---------------------------------------------------------------------- */

		$wp_customize->add_setting( 'troc_header_sticky_heading', array(
			'sanitize_callback' => 'wp_kses',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Heading_Control( $wp_customize, 'troc_header_sticky_heading', array(
			'label'    => esc_html__( 'Sticky header behavior', 'transparent-ocean' ),
			'section'  => 'osh_section',
			'priority' => 10,
		) ) );

		// Sticky header style
		$wp_customize->add_setting( 'troc_header_sticky_inherit', array(
			'default'           => 'ocean',
			'sanitize_callback' => 'oceanwp_sanitize_select',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Buttonset_Control( $wp_customize, 'troc_header_sticky_inherit', array(
			'label'    => esc_html__( 'Sticky Header Default Colors', 'transparent-ocean' ),
			'section'  => 'osh_section',
			'settings' => 'troc_header_sticky_inherit',
			'priority' => 10,
			'choices'  => array(
				'ocean' => esc_html__( 'OceanWP', 'oceanwp' ),
				'troc'  => esc_html__( 'Transparent', 'transparent-ocean' ),
			),
		) ) );

		// Sticky Logo Color
		$wp_customize->add_setting( 'troc_header_sticky_logo_color', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_sticky_logo_color', array(
			'label'           => esc_html__( 'Sticky Logo Color', 'transparent-ocean' ),
			'section'         => 'osh_section',
			'settings'        => 'troc_header_sticky_logo_color',
			'priority'        => 10,
			'active_callback' => 'oceanwp_cac_hasnt_custom_logo',
		) ) );

		// Sticky Logo Hover Color
		$wp_customize->add_setting( 'troc_header_sticky_logo_hover_color', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_sticky_logo_hover_color', array(
			'label'           => esc_html__( 'Sticky Logo Color: Hover', 'transparent-ocean' ),
			'section'         => 'osh_section',
			'settings'        => 'troc_header_sticky_logo_hover_color',
			'priority'        => 10,
			'active_callback' => 'oceanwp_cac_hasnt_custom_logo',
		) ) );

		/* -------------------------------------------------------------------------
		 * Logo Heading
		 * ---------------------------------------------------------------------- */

		$wp_customize->add_setting( 'troc_header_logo_heading', array(
			'sanitize_callback' => 'wp_kses',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Heading_Control( $wp_customize, 'troc_header_logo_heading', array(
			'label'    => esc_html__( 'Logo', 'oceanwp' ),
			'section'  => $section,
			'priority' => 10,
		) ) );

		// Transparent Logo
		$wp_customize->add_setting( 'troc_header_custom_logo', array(
			'default'           => '',
			'sanitize_callback' => array( Common::instance(), 'sanitize_image' ),

		) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'troc_header_custom_logo', array(
			'label'       => esc_html__( 'Transparent Logo', 'transparent-ocean' ),
			'description' => esc_html__( 'If you want to display a different logo on a transparent header', 'transparent-ocean' ),
			'section'     => $section,
			'settings'    => 'troc_header_custom_logo',
			'priority'    => 10,
		) ) );

		// Transparent Retina Logo
		$wp_customize->add_setting( 'troc_header_retina_logo', array(
			'default'           => '',
			'sanitize_callback' => array( Common::instance(), 'sanitize_image' ),
		) );
		$wp_customize->add_control( new WP_Customize_Image_Control( $wp_customize, 'troc_header_retina_logo', array(
			'label'    => esc_html__( 'Transparent Retina Logo', 'ocean-sticky-header' ),
			'section'  => $section,
			'settings' => 'troc_header_retina_logo',
			'priority' => 10,
		) ) );

		// Logo Color
		$wp_customize->add_setting( 'troc_header_logo_color', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_logo_color', array(
			'label'           => esc_html__( 'Logo Color', 'transparent-ocean' ),
			'section'         => $section,
			'settings'        => 'troc_header_logo_color',
			'priority'        => 10,
			'active_callback' => 'oceanwp_cac_hasnt_custom_logo',
		) ) );

		// Logo Hover Color
		$wp_customize->add_setting( 'troc_header_logo_hover_color', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_logo_hover_color', array(
			'label'           => esc_html__( 'Logo Color: Hover', 'transparent-ocean' ),
			'section'         => $section,
			'settings'        => 'troc_header_logo_hover_color',
			'priority'        => 10,
			'active_callback' => 'oceanwp_cac_hasnt_custom_logo',
		) ) );

		// Move OceanWP transparent background setting
		//	$wp_customize->get_control( 'ocean_transparent_header_heading' )->section = $section;
		//	$wp_customize->get_control( 'ocean_transparent_header_bg' )->section      = $section;

		/* -------------------------------------------------------------------------
		 * Menu Heading
		 * ---------------------------------------------------------------------- */

		$wp_customize->add_setting( 'troc_header_menu_heading', array(
			'sanitize_callback' => 'wp_kses',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Heading_Control( $wp_customize, 'troc_header_menu_heading', array(
			'label'    => esc_html__( 'Menu', 'oceanwp' ),
			'section'  => $section,
			'priority' => 10,
		) ) );

		/**
		 * Menu Link Color
		 */
		$wp_customize->add_setting( 'troc_header_menu_link_color', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_menu_link_color', array(
			'label'    => esc_html__( 'Link Color', 'oceanwp' ),
			'section'  => $section,
			'settings' => 'troc_header_menu_link_color',
			'priority' => 10,
		) ) );

		/**
		 * Menu Link Color Hover
		 */
		$wp_customize->add_setting( 'troc_header_menu_link_color_hover', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_menu_link_color_hover', array(
			'label'    => esc_html__( 'Link Color: Hover', 'oceanwp' ),
			'section'  => $section,
			'settings' => 'troc_header_menu_link_color_hover',
			'priority' => 10,
		) ) );

		/**
		 * Menu Link Active Color
		 */
		$wp_customize->add_setting( 'troc_header_menu_link_color_active', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_menu_link_color_active', array(
			'label'    => esc_html__( 'Link Color: Current Menu Item', 'oceanwp' ),
			'section'  => $section,
			'settings' => 'troc_header_menu_link_color_active',
			'priority' => 10,
		) ) );

		/**
		 * Menu Link Background Color
		 */
		$wp_customize->add_setting( 'troc_header_menu_link_background', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_menu_link_background', array(
			'label'    => esc_html__( 'Link Background', 'oceanwp' ),
			'section'  => $section,
			'settings' => 'troc_header_menu_link_background',
			'priority' => 10,
		) ) );

		/**
		 * Menu Link Hover Background Color
		 */
		$wp_customize->add_setting( 'troc_header_menu_link_hover_background', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_menu_link_hover_background', array(
			'label'    => esc_html__( 'Link Background: Hover', 'oceanwp' ),
			'section'  => $section,
			'settings' => 'troc_header_menu_link_hover_background',
			'priority' => 10,
		) ) );

		/**
		 * Menu Link Background Current Menu Item
		 */
		$wp_customize->add_setting( 'troc_header_menu_link_active_background', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_menu_link_active_background', array(
			'label'    => esc_html__( 'Link Background: Current Menu Item', 'oceanwp' ),
			'section'  => $section,
			'settings' => 'troc_header_menu_link_active_background',
			'priority' => 10,
		) ) );

		/* -------------------------------------------------------------------------
		 * Social Menu Heading
		 * ---------------------------------------------------------------------- */

		$wp_customize->add_setting( 'troc_header_social_heading', array(
			'sanitize_callback' => 'wp_kses',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Heading_Control( $wp_customize, 'troc_header_social_heading', array(
			'label'    => esc_html__( 'Social Menu', 'oceanwp' ),
			'section'  => $section,
			'priority' => 10,
			'active_callback' => 'oceanwp_cac_has_menu_social_and_simple_style',
		) ) );

		/**
		 * Social Menu Link Color
		 */
		$wp_customize->add_setting( 'troc_header_menu_social_links_color', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_menu_social_links_color', array(
			'label'           => esc_html__( 'Social: Color', 'oceanwp' ),
			'section'         => $section,
			'settings'        => 'troc_header_menu_social_links_color',
			'priority'        => 10,
			'active_callback' => 'oceanwp_cac_has_menu_social_and_simple_style',
		) ) );

		/**
		 * Social Menu Link Hover Color
		 */
		$wp_customize->add_setting( 'troc_header_menu_social_hover_links_color', array(
			'transport'         => 'postMessage',
			'sanitize_callback' => 'oceanwp_sanitize_color',
		) );
		$wp_customize->add_control( new OceanWP_Customizer_Color_Control( $wp_customize, 'troc_header_menu_social_hover_links_color', array(
			'label'           => esc_html__( 'Social Hover: Color', 'oceanwp' ),
			'section'         => $section,
			'settings'        => 'troc_header_menu_social_hover_links_color',
			'priority'        => 10,
			'active_callback' => 'oceanwp_cac_has_menu_social_and_simple_style',
		) ) );
	}

	/* =========================================================================
	 * OceanWP Metabox
	 * ====================================================================== */

	/**
	 * Add troc header tab in metabox.
	 */
	public function butterbean_register( $butterbean, $post_type ) {
		$capabilities = apply_filters( 'ocean_main_metaboxes_capabilities', 'manage_options' );
		if( false == current_user_can( $capabilities ) ) {
			return;
		}

		// Gets the manager object we want to add sections to.
		$manager = $butterbean->get_manager( 'oceanwp_mb_settings' );
		$section = 'troc_header_section';

		// Adds the "Transparent Header" section to the OceanWP metabox.
		$manager->register_section( $section, array(
			'label' => esc_html__( 'Transparent Header', 'transparent-ocean' ),
			'icon'  => 'dashicons-welcome-view-site',
		) );

		$manager->register_setting( 'troc_header_sticky_inherit', array(
			'sanitize_callback' => 'sanitize_key',
			'default'           => 'default',
		) );
		$manager->register_control( 'troc_header_sticky_inherit', array(
			'section'     => $section,
			'type'        => 'buttonset',
			'label'       => esc_html__( 'Sticky Header Default Colors', 'transparent-ocean' ),
			'description' => esc_html__( 'Choose the default sticky style on this page/post.', 'transparent-ocean' ),
			'choices'     => array(
				'default' => esc_html__( 'Default', 'oceanwp' ),
				'ocean'   => esc_html__( 'OceanWP', 'oceanwp' ),
				'troc'    => esc_html__( 'Transparent', 'transparent-ocean' ),
			),
		) );

		$manager->register_setting( 'troc_header_logo_color', array(
			'sanitize_callback' => 'butterbean_maybe_hash_hex_color',
		) );
		$manager->register_control( 'troc_header_logo_color', array(
			'section'     => $section,
			'type'        => 'rgba-color',
			'label'       => esc_html__( 'Text Logo Color', 'transparent-ocean' ),
			'description' => esc_html__( 'Select a color. Hex code, ex: #555', 'ocean-extra' ),
		) );

		$manager->register_setting( 'troc_header_logo_hover_color', array(
			'sanitize_callback' => 'butterbean_maybe_hash_hex_color',
		) );
		$manager->register_control( 'troc_header_logo_hover_color', array(
			'section'     => $section,
			'type'        => 'rgba-color',
			'label'       => esc_html__( 'Text Logo Color: Hover', 'transparent-ocean' ),
			'description' => esc_html__( 'Select a color. Hex code, ex: #13aff0', 'ocean-extra' ),
		) );
	}
}