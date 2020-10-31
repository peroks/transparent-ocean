( function( $ ) {
	'use strict';
	$( document ).ready( function() {

		$( '.troc-parallax' ).paroller();

		var $banner = $( '.page-banner' );
		var bg = $banner.css( 'background-image' );

		if( bg ) {
			var src = bg.replace( /(^url\()|(\)$|[\"\'])/g, '' );
			$( '<img/>' ).attr( 'src', src ).on( 'load', function() {
				$banner.addClass( 'loaded' );
			} );
		}
	} );
} )( jQuery );