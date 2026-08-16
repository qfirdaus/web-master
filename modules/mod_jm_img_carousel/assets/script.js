(function($){
	window.jmm_image_carousel = function ( data ){
		//console.log(data);

		$widget = $(data.id);
		var id = $widget.attr('id');

		var myFlipster = $widget.flipster({
			style: data.style,
			spacing: -0.4,
			nav: data.nav,
			buttons: data.buttons,
			loop: data.loop,
			scrollwheel: data.scrollwheel,
			keyboard: false,
			onItemSwitch: function ( current, prev ) {
				slideChange(current, prev);
			}
		});

		$widget.find('.flipster__item').find('a').attr('tabindex', '-1');
		$widget.find('.flipster__item--current').find('a').attr('tabindex', '0');

		if( data.buttons == true ) {
			$widget.find('.flipster__button--prev, .flipster__button--next').attr('aria-controls', id + '-container');
			$widget.find('.flipster__button--prev').attr('aria-label', data.label_prev).insertBefore( $widget.find('.jmm-carousel-in') );
			$widget.find('.flipster__button--next').attr('aria-label', data.label_next).insertBefore( $widget.find('.jmm-carousel-in') );
		}
		
		if( data.nav !== false ) {
			$widget.find('.flipster__nav').insertBefore( $widget.find('.jmm-carousel-in') );
			$widget.find('.flipster__nav').attr('aria-label', data.label_indicators).attr('role', 'tablist');
			$widget.find('.flipster__nav__item a').attr('role', 'tab').attr('tabindex', '-1').attr('aria-selected', 'false');
			$widget.find('.flipster__nav__item--current a').attr('tabindex', '0').attr('aria-selected', 'true');

			$widget.find('.flipster__nav__item a').each(function () {
				var widgetID = $(this).closest('.jmm-img-carousel').attr('id');
				var tabNumber = $(this).text();

				$(this).attr('aria-controls', 'tab-' + widgetID + '-' + tabNumber);
				$(this).attr('id', widgetID + '-' + tabNumber);
			});
		}

	}

	function slideChange( current, prev ) {
		var $this = $(current);
		$this.find('a').attr('tabindex', '0');
		$this.siblings().find('a').attr('tabindex', '-1');

		var id = $this.find('a').attr('aria-labeledby'); //available only when indicators enabled

		if( typeof id !== 'undefined' ) {
			var flipster = $this.closest('.flipster');
			var nav = flipster.find('.flipster__nav__item #' + id);
			nav.attr('tabindex', '0').attr('aria-selected', 'true').parent().siblings().find('a').attr('tabindex', '-1').attr('aria-selected', 'false');
			if( flipster.find('.flipster__nav__item a:focus').length ) {
				nav.focus();
			}
		}
	}

	// Keyboard Navigation for PE Image Carousel
	//-------------------------------------------------------
	function imageCarouselKeyboard() {

		var nav = $('.flipster__nav__link');

		if( nav.length ) {

			nav.keydown(function (event) {
				//prevent default
				if (event.which == 35 || event.which == 36 || event.which == 37 || event.which == 39) {
					event.preventDefault();
				}
			});

			nav.keyup(function (event) {

				var $flipster = $(this).closest('.flipster');

				if (event.which == 36) { // Home key
					$flipster.flipster('jump', 0);
				}

				if (event.which == 35) { // End key
					$flipster.flipster('jump', $('.flipster__item:last-child'));
				}

				if (event.which == 39) { // Right arrow
					if( $flipster.find('.flipster__item--current').is(':last-child') ) {
						$flipster.flipster('jump', 0);
					} else {
						$flipster.flipster('next');
					}
				}

				if (event.which == 37) { // Left arrow
					if( $flipster.find('.flipster__item--current').is(':first-child') ) {
						$flipster.flipster('jump', $('.flipster__item:last-child'));
					} else {
						$flipster.flipster('prev');
					}
				}

			});
		}

	}

	$(document).ready(function () {

		//wait for flipster
		setTimeout(function() {
			imageCarouselKeyboard();
		}, 500);

	});

})(jQuery);