/**
 * Customizer enhancements for a better user experience.
 * Contains handlers to make Theme Customizer preview reload changes asynchronously.
 */
( function( $ ) {

	var api = wp.customize;

	api( 'troc_banner_height', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_banner_height' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_banner_height">.page-banner { height: ' + to + 'px; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_banner_position', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_banner_position' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_banner_position">.page-banner { background-position: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_banner_attachment', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_banner_attachment' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_banner_attachment">.page-banner { background-attachment: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_banner_repeat', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_banner_repeat' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_banner_repeat">.page-banner { background-repeat: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_banner_size', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_banner_size' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_banner_size">.page-banner { background-size: ' + to + '; }</style>';
				if( $child.length ) {
					$child.replaceWith( img );
				} else {
					$( 'head' ).append( img );
				}
			} else {
				$child.remove();
			}
		} );
	} ), api( 'troc_banner_overlay_color', function( $swipe ) {
		$swipe.bind( function( to ) {
			var $child = $( '.customizer-troc_banner_overlay_color' );
			if( to ) {
				/** @type {string} */
				var img = '<style class="customizer-troc_banner_overlay_color">.page-banner-overlay { background-color: ' + to + '; }</style>';
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