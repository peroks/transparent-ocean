/**
 * Customizer enhancements for a better user experience.
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */
( function( $ ) {

	var api = wp.customize;

	api( 'troc_header_sticky_logo_color', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_sticky_logo_color' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_sticky_logo_color">.is-sticky #site-logo a.site-logo-text { color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_header_sticky_logo_hover_color', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_sticky_logo_hover_color' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_sticky_logo_hover_color">.is-sticky #site-logo a.site-logo-text:hover { color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_header_logo_color', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_logo_color' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_logo_color">#site-logo a.site-logo-text { color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_header_logo_hover_color', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_logo_hover_color' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_logo_hover_color">#site-logo a.site-logo-text:hover { color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_header_menu_link_color', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_menu_link_color' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_menu_link_color">#site-navigation-wrap .dropdown-menu > li > a, .oceanwp-mobile-menu-icon a { color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_header_menu_link_color_hover', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_menu_link_color_hover' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_menu_link_color_hover">#site-navigation-wrap .dropdown-menu > li > a:hover, .oceanwp-mobile-menu-icon a:hover { color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_header_menu_link_color_active', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_menu_link_color_active' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_menu_link_color_active">#site-navigation-wrap .dropdown-menu > .current-menu-item > a,#site-navigation-wrap .dropdown-menu > .current-menu-parent > a,#site-navigation-wrap .dropdown-menu > .current-menu-item > a:hover,#site-navigation-wrap .dropdown-menu > .current-menu-parent > a:hover { color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_header_menu_link_background', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_menu_link_background' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_menu_link_background">#site-navigation-wrap .dropdown-menu > li > a { background-color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_header_menu_link_hover_background', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_menu_link_hover_background' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_menu_link_hover_background">#site-navigation-wrap .dropdown-menu > li > a:hover,#site-navigation-wrap .dropdown-menu > li.sfHover > a { background-color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_header_menu_link_active_background', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_menu_link_active_background' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_menu_link_active_background">#site-navigation-wrap .dropdown-menu > .current-menu-item > a,#site-navigation-wrap .dropdown-menu > .current-menu-parent > a,#site-navigation-wrap .dropdown-menu > .current-menu-item > a:hover,#site-navigation-wrap .dropdown-menu > .current-menu-parent > a:hover { background-color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_header_menu_social_links_color', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_menu_social_links_color' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_menu_social_links_color">.oceanwp-social-menu.simple-social ul li a { color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_header_menu_social_hover_links_color', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_header_menu_social_hover_links_color' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_header_menu_social_hover_links_color">.oceanwp-social-menu.simple-social ul li a:hover { color: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} );
} )( jQuery );