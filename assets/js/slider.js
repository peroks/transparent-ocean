( function( $ ) {
	'use strict';
	$( document ).ready( function() {

		$( '.page-slider' ).slick( {
			easing: 'ease',
			waitForAnimate: false,
			prevArrow: '<button type="button" class="slick-prev"><span class="fa fa-angle-left"></span></button>',
			nextArrow: '<button type="button" class="slick-next"><span class="fa fa-angle-right"></span></button>',
		} );

		$( '.page-slider, .gallery-format' ).on( 'afterChange', function( event, slick, currentSlide ) {
			if( currentSlide == slick.slideCount - 1 ) {
				if( true === slick.getOption( 'autostop' ) ) {
					slick.pause();
				}
			}
		} );

	} );
} )( jQuery );