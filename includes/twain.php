<?php namespace silverscreen\wp\troc;
use WP_Query;
use WP_Post;
use WP_Term;
/**
 * Wrapper class for agnostic handling of WP_Post and WP_Term objects.
 *
 * @author     Per Egil Roksvaag
 * @license    MIT License
 */
class Twain {
	/**
	 * @var array Object cache for Twain subclasses.
	 */
	protected static $_cache = array();

	/**
	 * Creates a new Twain instance.
	 *
	 * @param WP_Post|WP_Term $object The Post or Term object to wrap.
	 * @return Twain a new instance of this class.
	 */
	protected static function create( $object ) {
		$class = apply_filters( Main::FILTER_CLASS_CREATE, static::class );
		return apply_filters( Main::FILTER_CLASS_CREATED, new $class( $object ), $class, static::class );
	}

	/**
	 * Gets a cached or new Twain subclass based on the given ID.
	 *
	 * @param int|Twain|WP_Term|WP_Post $id Post or Term numeric ID, object or wrapper. Defaults to the WP 'queried_object'.
	 * @param string $type Only required if $id is numeric, ignored otherwise.
	 * @return Twain A subclass of Twain.
	 */
	public static function factory( $id = null, $type = 'post' ) {
		if( $id instanceof Twain ) {
			return $id;
		}
		if( empty( $id ) ) {
			$id = get_queried_object();
		}

		if( is_numeric( $id ) ) {
			$key = $type . '-' . $id;
		} elseif( $id instanceof WP_Term ) {
			$type = 'term';
			$key  = $type . '-' . $id->term_id;
		} elseif( $id instanceof WP_Post ) {
			$type = 'post';
			$key  = $type . '-' . $id->ID;
		} else {
			$type = 'post';
			$id   = oceanwp_post_id();
			$key  = $type . '-' . $id;
		}

		if( array_key_exists( $key, static::$_cache ) ) {
			return static::$_cache[$key];
		}
		if( ( 'term' == $type ) && ( $term = get_term( $id ) ) && ( $term instanceof WP_Term ) ) {
			return static::$_cache[$key] = Twain_Term::create( $term );
		}
		if( ( 'post' == $type ) && ( $post = get_post( $id ) ) && ( $post instanceof WP_Post ) ) {
			return static::$_cache[$key] = Twain_Post::create( $post );
		}
		return Twain::create( get_queried_object() );
	}

	public static function get_types() {
		$types = array_flip( get_post_types( array( 'public' => true, 'show_in_menu' => true ) ) );
		$tax   = array_flip( get_taxonomies( array( 'public' => true, 'show_in_menu' => true ) ) );

		foreach( $types as $key => &$value ) {
			$value = get_post_type_object( $key )->label;
		}
		foreach( $tax as $key => &$value ) {
			$value = get_taxonomy( $key )->label;
		}
		if( Main::check_woo() ) {
			$tax['product_attr'] = __( 'Product attributes', 'transparent-ocean' );
		}
		return apply_filters( 'troc_twain_get_types', array_merge( $types, $tax ) );
	}

	/**
	 * @ignore Manual setting
	 */
	public static function x_get_types() {
		$types = array(
			'post'       => get_post_type_object( 'post' )->label,
			'page'       => get_post_type_object( 'page' )->label,
			'attachment' => get_post_type_object( 'attachment' )->label,
			'category'   => get_taxonomy( 'category' )->label,
			'post_tag'   => get_taxonomy( 'post_tag' )->label,
		);

		if( Main::check_woo() ) {
			$types = array_merge( $types, array(
				'product'      => get_post_type_object( 'product' )->label,
				'product_cat'  => get_taxonomy( 'product_cat' )->label,
				'product_tag'  => get_taxonomy( 'product_tag' )->label,
				'product_attr' => __( 'Product attributes', 'transparent-ocean' ),
			) );
		}
		return apply_filters( 'troc_twain_get_types', $types );
	}

	/**
	 * @var WP_Post|WP_Term The wrapped object.
	 */
	protected $_object = null;

	/**
	 * @var int A cached Thumbnail ID.
	 */
	protected $_thumbnail = null;

	/**
	 * @var array Cached Gallery IDs.
	 */
	protected $_gallery = array();

	/**
	 * Constructor.
	 *
	 * @param WP_Post|WP_Term $object The Post or Term object to wrap.
	 */
	protected function __construct( $object ) {
		$this->_object = $object;
	}

	/**
	 * Gets the wrapped object.
	 *
	 * @return WP_Post|WP_Term.
	 */
	public function get_object() {
		return $this->_object;
	}

	/**
	 * Gets the ID of the wrapped object.
	 *
	 * @return int The ID of the wrapped object.
	 */
	public function get_id() {
		return false;
	}

	/**
	 * Gets the Post type or the Term taxonomy. Product Attributes always returns 'product_attr'.
	 *
	 * @return string The type of the wrapped object.
	 */
	public function get_type() {
		return '';
	}

	/**
	 * Gets the shorthand type of the wrapper class: 'post', 'term' or 'empty'.
	 *
	 * @return string Wrapper class type.
	 */
	public function wrap_type() {
		return '';
	}

	/**
	 * Checks if the wrapped object has a Thumbnail.
	 *
	 * @return bool True if the wrapped object has a Thumbnail, false otherwise.
	 */
	public function has_thumbnail() {
		return (bool) $this->get_thumbnail_id();
	}

	/**
	 * Checks if the wrapped object has a (generated) gallery of at least one image.
	 *
	 * @return bool True if the wrapped object has a gallery, false otherwise.
	 */
	public function has_gallery( $lenght = 10 ) {
		return (bool) $this->get_gallery_ids( $lenght );
	}

	/**
	 * Gets the thumbnail of the wrapped object.
	 *
	 * @return int|bool The attachment ID of the thumbnail or false if no thumbnail was found.
	 */
	public function get_thumbnail_id() {
		return false;
	}

	public function get_thumbnail_src() {
		$attachment_id = $this->get_thumbnail_id();
		return $attachment_id ? wp_get_attachment_url( $attachment_id ) : false;
	}

	/**
	 * Compiles an image gallery based on Terms and/or Posts related to the wrapped object, e.g. all thumbnails of Posts in a Category.
	 * Galleries in posts have presedence over compiled galleries.
	 *
	 * @param int $length The max number of images to be displayed..
	 * @return array List of attachment IDs.
	 */
	public function get_gallery_ids( $length = 10 ) {
		return array();
	}
}

/**
 * Wrapper for WP_Term objects.
 */
class Twain_Term extends Twain {
	/**
	 * @see Twain::get_id()
	 * @return int The ID of the wrapped object.
	 */
	public function get_id() {
		return $this->_object->term_id;
	}

	/**
	 * @see Twain::get_type()
	 * @return string The type of the wrapped object.
	 */
	public function get_type() {
		return 'pa_' == substr( $this->get_taxonomy(), 0, 3 ) ? 'product_attr' : $this->get_taxonomy();
	}

	/**
	 * @see Twain::wrap_type()
	 */
	public function wrap_type() {
		return 'term';
	}

	/**
	 * Gets the real - not shortened as in get_type() - Term taxonomy, i.e. also the Product Attributes.
	 *
	 * @return string The real Term taxonomy.
	 */
	protected function get_taxonomy() {
		return $this->_object->taxonomy;
	}

	/**
	 * Gets the thumbnail ID of a Term.
	 *
	 * @return int|bool The attachment ID of the thumbnail or false if no thumbnail was found.
	 */
	public function get_thumbnail_id() {
		if( is_null( $this->_thumbnail ) ) {
			$attachment_id    = get_term_meta( $this->get_id(), 'thumbnail_id', true );
			$this->_thumbnail = apply_filters( 'troc_get_term_thumbnail_id', $attachment_id, $this->get_object() );
		}
		return $this->_thumbnail;
	}

	/**
	 * Compiles an image gallery based on the child Terms and/or the Posts associated with a Term,
	 * e.g. all thumbnails of Posts in a Category.
	 *
	 * @param int $length The max number of images to be displayed.
	 * @return array List of Attachment IDs.
	 */
	public function get_gallery_ids( $length = 10 ) {
		if( false == isset( $this->_gallery[$length] ) ) {
			$gallery = array();

			foreach( $this->get_term_children_ids( $length ) as $term_id ) {
				$gallery[] = get_term_meta( $term_id, 'thumbnail_id', true );
			}
			foreach( $this->get_term_post_ids( $length - count( $gallery ) ) as $post_id ) {
				$gallery[] = get_post_meta( $post_id, '_thumbnail_id', true );
			}

			$gallery = array_unique( array_filter( $gallery ) );
			$gallery = apply_filters( 'troc_get_term_gallery_ids', $gallery, $this->get_object() );

			$this->_gallery[$length] = $gallery;
		}
		return $this->_gallery[$length];
	}

	/**
	 * Merge all direct children of a Term into a single array of their IDs.
	 * Only relevant for hierarchical taxonomies like Product Categories.
	 *
	 * @param int $length The max number of images to be displayed.
	 * @return array List of Term IDs.
	 */
	protected function get_term_children_ids( $length = 10 ) {
		if( $length ) {
			$children = get_term_children( $this->get_id(), $this->get_taxonomy() );
			return array_slice( $children, 0, $length );
		}
		return array();
	}

	/**
	 * Gets the IDs of Posts related to the given Term.
	 *
	 * @link https://developer.wordpress.org/reference/classes/WP_Query/parse_query/
	 * @link https://tomelliott.com/wordpress/get-posts-custom-taxonomies-terms
	 *
	 * @param int $length The max number of images to be displayed.
	 * @param array $args Optional associative array of query fields and values, see link above.
	 * @return array List of Post IDs.
	 */
	protected function get_term_post_ids( $length = 10, $args = array() ) {
		if( $length ) {
			$args  = apply_filters( 'troc_get_term_posts', array_merge( $args, array(
				'fields'         => 'ids',
				'post_status'    => 'publish',
				'posts_per_page' => $length,
				'no_found_rows'  => true,
				'orderby'        => 'date',
				'meta_key'       => '_thumbnail_id',
				'tax_query'      => array(
					array(
						'field'            => 'term_id',
						'terms'            => $this->get_id(),
						'taxonomy'         => $this->get_taxonomy(),
						'include_children' => false,
					),
					array(
						'field'    => 'name',
						'terms'    => 'exclude-from-catalog',
						'taxonomy' => 'product_visibility',
						'operator' => 'NOT IN',
					),
				),
			) ) );
			$query = new WP_Query( $args );
			return $query->posts;
		}
		return array();
	}
}

/**
 * Wrapper for WP_Post objects.
 */
class Twain_Post extends Twain {
	/**
	 * @see Twain::get_id()
	 */
	public function get_id() {
		return $this->_object->ID;
	}

	/**
	 * @see Twain::get_type()
	 */
	public function get_type() {
		return $this->_object->post_type;
	}

	/**
	 * @see Twain::wrap_type()
	 */
	public function wrap_type() {
		return 'post';
	}

	protected function mime_type() {
		return explode( '/', $this->_object->post_mime_type )[0];
	}

	/**
	 * @see Twain::get_thumbnail_id()
	 */
	public function get_thumbnail_id() {
		if( is_null( $this->_thumbnail ) ) {
			if( 'attachment' == $this->get_type() && 'image' == $this->mime_type() ):
				$this->_thumbnail = $this->get_id();
			else:
				$this->_thumbnail = get_post_thumbnail_id( $this->get_object() );
			endif;
		}
		return $this->_thumbnail;
	}

	/**
	 * @see Twain::get_gallery_ids()
	 */
	public function get_gallery_ids( $length = 10 ) {
		if( false == isset( $this->_gallery[$length] ) ) {
			$gallery = array();

			// Local OceanWP galleries have presedence
			if( $temp = oceanwp_get_gallery_ids( $this->get_id() ) ) {
				$gallery = array_slice( $temp, 0, $length );
			} // Local WP Gallery
			elseif( $temp = get_post_gallery( $this->get_object(), false ) ) {
				$gallery = array_slice( explode( ',', $temp['ids'] ), 0, $length );
			} // Product
			elseif( Main::check_woo() && 'product' == $this->get_type() ) {
				global $product;
				$reuse   = $product instanceof WC_Product && $product->get_id() == $this->get_id();
				$prod    = $reuse ? $product : wc_get_product( $this->get_object() );
				$gallery = array_slice( $prod->get_gallery_image_ids(), 0, $length );
			} // Blog page
			elseif( get_option( 'page_for_posts' ) == $this->get_id() ) {
				foreach( $this->get_blog_posts( $length ) as $child ) {
					$gallery[] = get_post_thumbnail_id( $child );
				}
			} // Shop Page
			elseif( Main::check_woo() && wc_get_page_id( 'shop' ) == $this->get_id() ) {
				foreach( $this->get_shop_term_ids( $length ) as $term_id ) {
					$gallery[] = get_term_meta( $term_id, 'thumbnail_id', true );
				}
			}

			// wp_get_post_terms();
			$gallery = array_unique( array_filter( $gallery ) );
			$gallery = apply_filters( 'troc_get_post_gallery_ids', $gallery, $this->get_object() );

			$this->_gallery[$length] = $gallery;
		}
		return $this->_gallery[$length];
	}

	/**
	 * Retrieves a list of the latest Posts - normally displayed on the Blog Page.
	 * Only Posts with thumbnails are returned.
	 *
	 * @link https://developer.wordpress.org/reference/classes/WP_Query/parse_query/
	 *
	 * @param int $length The max number of images to be displayed.
	 * @param array $args Optional associative array of query fields and values, see link above.
	 * @return array List of WP_Post objects.
	 */
	protected function get_blog_posts( $length = 10, $args = array() ) {
		$args = apply_filters( 'troc_get_blog_posts', array_merge( $args, array(
			'post_status'    => 'publish',
			'posts_per_page' => $length,
			'no_found_rows'  => true,
			'orderby'        => 'date',
			'meta_key'       => '_thumbnail_id',
		) ) );
		return get_posts( $args );
	}

	/**
	 * Gets the top level Product Categories (no parents).
	 * Only Categories with thumbnails are returned.
	 *
	 * @param int $length The max number of images to be displayed.
	 * @param array $args Optional associative array of query fields and values, see link above.
	 * @return array List of Product Categories.
	 */
	protected function get_shop_term_ids( $length = 10, $args = array() ) {
		$args = apply_filters( 'troc_get_shop_terms', array_merge( $args, array(
			'fields'   => 'ids',
			'taxonomy' => 'product_cat',
			'parent'   => 0,
			'number'   => $length,
			'orderby'  => 'name',
			'meta_key' => 'thumbnail_id',
			//	'object_ids' => $this->get_id(),
		) ) );
		return get_terms( $args );
	}
}