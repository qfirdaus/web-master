/**
 * @package DJ-MegaMenu
 * @copyright Copyright (C) 2024 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: https://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski, Artur Kaczmarek
 */

 var DJMegaMenu;

(function($){
	DJMegaMenu = function (menu, options) {

		this.options = {
			openDelay: 250, // delay before open sub-menu
			closeDelay: 500, // delay before close sub-menu
			animIn: 'fadeIn',
			animOut: 'fadeOut',
			animSpeed: 'normal',
			duration: 450, // depends on speed: normal - 450, fast - 250, slow - 650
			wrap: null,
			direction: 'ltr',
			event: 'mouseenter',
			eventClose: 'mouseleave',
			parentOpen: 0,
			touch: (('ontouchstart' in window) || (navigator.MaxTouchPoints > 0) || (navigator.msMaxTouchPoints > 0)), // touch screens detection
			offset: 0,
			wcag: 1,
			overlay: 0
		};

		this.init(menu, options);
	};

	DJMegaMenu.prototype.init = function (menu, options) {

		var self = this;

		jQuery.extend(self.options, options);

		if ( !menu.length ) return;

		// remove hidden menu items from the DOM
		menu.find('.dj-hideitem').remove();

		// add active class
		menu.find('.modules-wrap ul.nav li.current').each(function () {
			var $this = $(this);
			$this.parents('ul.dj-submenu > li, li.dj-up').each(function () {
				$this.addClass('active').find('> a').addClass('active');
			});
		});

    if( ! menu.find('.dj-up.active').length ) {
      menu.find('li.active').first().parents('li').each(function() {
        $(this).addClass('active').find('> a').addClass('active');
      });
    }

    self.options.activeParent = menu.find('.dj-up.active.parent').first();

		self.options.menu = menu;
		self.options.blurTimer = null;

		switch (self.options.animSpeed) {
			case 'fast':
				self.options.duration = 250;
				break;
			case 'slow':
				self.options.duration = 650;
				break;
		}

		if( self.options.animIn == '0' && self.options.animOut == '0' ) {
			self.options.duration = 0;
		}

		menu.addClass('dj-megamenu-js ' + self.options.animSpeed);

		var kids = menu.find('li.dj-up');
		self.kids = [];

		self.options.wrap = $('#' + self.options.wrap);
		if (!self.options.wrap.length) self.options.wrap = menu.parents('div').last();

		if (self.options.touch) menu.on('touchstart', function (e) {
			e.stopPropagation();
		}); // stop propagation

		kids.each(function (index) { //main items init
			var kid = $(this);
			self.kids[index] = new DJMMenuItem(kid, 0, self, self.options);
		});

		if (self.options.fixed == 1) {
			$(document).on('djmegamenu:pageload', function() {
				self.makeSticky(menu);
			});
		}

		//close submenu
		if( self.options.touch || self.options.eventClose == 'click' ) {
			$(document).on('mouseup', function (e) {
				if( ! $(e.target).closest('.dj-megamenu').length ) { //if click outside menu, close it
					self.hideAllSubs();
				}
			});
		}

		//keyboard navigation
		if (self.options.wcag == 1) {
			//disable tab
			var anchors = menu.find('a');
			anchors.attr('tabindex', -1);
			anchors.first().attr('tabindex', 0);

			$(document.body).on('keydown', function ( event ) {
				if(event.key == 'Escape') {
					self.hideAllSubs();
				}
			});
		}

		//parent open
		if(self.options.parentOpen > 0 && self.options.activeParent.length) {
			self.options.activeParent.find('> a').trigger('click');
		}

		//overlay
		if(self.options.overlay == 1) {
			var body = $(document.body);
			var wrapper = self.options.menu.parent();
			$(document).on('djmegamenu:showsubmenu', function() {
				if( ! body.hasClass('dj-megamenu-overlay') ) {
					body.addClass('dj-megamenu-overlay');
					var overlay = $('<div class="dj-megamenu-overlay-box" />');
					overlay.appendTo(wrapper).fadeIn(300);
				}
			});

			$(document).on('djmegamenu:hidesubmenu djmegamenu:leaveitem', function() {
				if( self.options.eventClose == 'mouseleave' && self.options.menu.find('.parent.hover').length ) return; //make sure that any dropdowns are not opened
				body.removeClass('dj-megamenu-overlay');
				wrapper.find('.dj-megamenu-overlay-box').fadeOut(300, function() { $(this).remove() });
			});
		}
	};

	DJMegaMenu.prototype.hideAllSubs= function () {
		var self = this;

		var opened = self.options.menu.find('.hover');
    if( opened.length ) {
      opened.get().reverse();
      $.each(opened, function (index, item) {
        $(item).trigger('djmegamenu:hideSub');
      });
    }
	};

	DJMegaMenu.prototype.makeSticky = function (menu) {

		var self = this;

		self.sticky = false;
		var wrapper = $('#' + menu.attr('id') + 'sticky');
		var placeholder = $('<div />');
		placeholder.css({
			display: 'none',
			opacity: 0,
			height: menu.height()
		});
		placeholder.attr('id', menu.attr('id') + 'placeholder');
		placeholder.insertBefore(wrapper);
		$(window).scroll(self.scroll.bind(self, wrapper, menu, placeholder, false));
		$(window).resize(self.scroll.bind(self, wrapper, menu, placeholder, true));
		self.scroll(wrapper, menu, placeholder, false);
		$(window).on('orientationchange', function () {
			setTimeout(function () {
				$(window).trigger('resize');
			}, 500);
		});
	};

	DJMegaMenu.prototype.scroll = function (wrapper, menu, placeholder, resize) {

		var self = this;

		if (menu.is(':hidden')) return;
		var scroll = $(window).scrollTop();
		var step = (self.sticky ? placeholder.offset().top : menu.offset().top) - parseInt(self.options.offset);

		// we need to clean the sticky styles and classes on scroll above the step or window resize
		if (self.sticky && (scroll < step || resize)) {

			menu.css({
				position: '',
				top: '',
				background: '',
				width: '',
				height: ''
			});

			menu.removeClass('dj-megamenu-fixed');
			wrapper.find('.dj-stickylogo').css('display', 'none');

			wrapper.css({
				position: '',
				top: '',
				height: '',
				left: '',
				width: '',
				display: 'none'
			});

			placeholder.css({
				display: 'none',
				'min-width': ''
			});
			// remove the max height for the submenu in sticky megamenu
			menu.find('.dj-up > .dj-subwrap').css({
				'max-height': '',
				'overflow-y': ''
			});
			self.sticky = false;
		}

		// if menu is not sticky (also on resize) we add styles and classes to make it sticky
		if (!self.sticky && scroll >= step) {

			wrapper.css({
				position: 'fixed',
				top: parseInt(self.options.offset),
				left: 0,
				width: '100%',
				display: 'block'
			});

			placeholder.css({
				'min-width': menu.outerWidth(true),
				display: ''
			});

			var lh = 0;
			var logo = wrapper.find('.dj-stickylogo');
			if (logo.length) {
				logo.css('display', '');
				if (logo.hasClass('dj-align-center')) {
					lh = logo.outerHeight(true);
					//console.log(lh);
				}
			}
			menu.css({
				position: 'fixed',
				top: parseInt(self.options.offset) + lh,
				background: 'transparent',
				height: 'auto'
			});

			menu.addClass('dj-megamenu-fixed');
			menu.css('width', placeholder.width() ? placeholder.width() + 1 : 'auto');
			placeholder.css('height', menu.outerHeight());
			// add place for sticky logo
			wrapper.css('height', lh + menu.outerHeight());
			// set the max height for the submenu in sticky megamenu
			var mh = $(window).height() - parseInt(self.options.offset) - wrapper.height();
			menu.find('.dj-up > .dj-subwrap').each(function () {
				if (!$(this).find('.dj-subwrap').length) {
					$(this).css({
						'max-height': mh,
						'overflow-y': 'auto'
					});
				}
			});
			self.sticky = true;
		}
	};

	/* DJMenuItem private constructor class */
	var DJMMenuItem = function (menu, level, parent, options) {
		this.options = {};
		this.init(menu, level, parent, options);
	};

	DJMMenuItem.prototype.init = function (menu, level, parent, options) {

		var self = this;

		jQuery.extend(self.options, options);

		self.menu = menu;
		self.level = level;
		self.parent = parent;

		self.timer = null;
		self.blurTimer = null;

		self.sub = self.menu.find('> .dj-subwrap').first();

		var subitems = self.menu.find('.dj-submenu > li, .dj-subtree > li');

		//console.log(self.menu);
		//menu.mouseenter(function(){console.log(subitems)});

		if ( ! subitems.length ) {
			// no subitems, clean and change to non-parent item
			self.sub.remove();
			self.menu.removeClass('parent');
			self.menu.find('span.dj-drop').removeClass('dj-drop');
			self.menu.find('i.arrow').remove();
		}

		var anchor = self.menu.find('> a').first();
		if ( ! anchor.attr('href') && ! self.menu.hasClass('separator')) self.menu.addClass('separator');

		var separator = ( self.menu.hasClass('separator') ) ? self.menu : false;
		if ( separator ) anchor.css('cursor', 'pointer');

		if( self.options.touch || self.options.event == 'click_all' || (self.options.event == 'click' && separator) ) {
			anchor.on('touchend click', function (e) {
				if( self.sub.length && ! self.menu.hasClass('hover') ) {
					e.preventDefault();
					if (e.type == 'touchend') self.menu.trigger('click');
				}
			});
		}

		//show submenu
		self.menu.on('click', function(e) {
			if( ! self.menu.hasClass('hover') ) { //if submenu hidden, display it
				self.showSub();
			} else if( separator && self.options.event == 'click' ) { //if is separator and submenu visible, close it
				self.hideSub();
			}
		}.bind(self));

		if( self.options.event == 'mouseenter' || (self.options.event == 'click' && ! separator) ) {
			self.menu.on('mouseenter', function (e) {
				//console.log('mouseenter');
				self.showSub();
			}.bind(self));
		}

		if( self.options.eventClose == 'mouseleave' ) {
			self.menu.on('mouseleave', function (e) {
				//console.log('mouseleave');
				if ( $(e.target).is('input') ) return;
				self.hideSub();
			});
		}

		self.menu.on('djmegamenu:showSub', function (e) {
			e.stopPropagation();
			//console.log('djmegamenu:showSub');
			self.showSub();
		});

		self.menu.on('djmegamenu:hideSub', function (e) {
			e.stopPropagation();
			//console.log('djmegamenu:hideSub');
			self.hideSub();
		});

		//init lower levels of menu
		if (self.sub.length) {
			self.initKids();
		}

		//self.menu.on("click mouseenter mouseleave touchstart touchend", function(e){ console.log(e.type + ' => ' + e.currentTarget.localName); });

		if (self.options.wcag == 1) {
			self.keyboardNavigation();
		}

	};

	DJMMenuItem.prototype.keyboardNavigation = function () {
		var self = this;

		var nextItem = function( item ) { // item is current LI
			var next = item.next();
			if ( item.is(':last-of-type') ) {
				//console.log('last-of-type');
				var column = item.closest('.dj-subcol');

				var next_column = column.next('.dj-subcol');
				var prev_column = column.prev('.dj-subcol');
				var first_column = column.parent().children().first();

				var menu_mod = item.closest('.nav.menu'); //check if item from module
				var menu_mod_submenu = ( item.closest('.nav-child').length ) ? true : false;
				var target = ( menu_mod.length ) ? '> .dj-submenu > li .menu > li' : '> .dj-submenu > li';

				if( next_column.length && !menu_mod_submenu ) {
					next = next_column.find(target).first();
				} else if( prev_column.length  && !menu_mod_submenu) {
					next = first_column.find(target).first();
				} else {
					next = item.closest('ul').find('> li').first();
				}
			} else if( ! next.length ) {
				next = item;
			}
			return next;
		}

		var prevItem = function( item ) { // item is current LI
			var prev = item.prev();
			if ( item.is(':first-of-type') ) {
				//console.log('first-of-type');
				var column = item.closest('.dj-subcol');

				var prev_column = column.prev('.dj-subcol');
				var next_column = column.next('.dj-subcol');
				var last_column = column.parent().children().last();

				var menu_mod = item.closest('.nav.menu'); //check if item from module
				var menu_mod_submenu = ( item.closest('.nav-child').length ) ? true : false;
				var target = ( menu_mod.length ) ? '> .dj-submenu > li .menu > li' : '> .dj-submenu > li';

				if( prev_column.length && !menu_mod_submenu  ) {
					prev = prev_column.find(target).last();
				} else if( next_column.length && !menu_mod_submenu ) {
					prev = last_column.find(target).last();
				} else {
					prev = item.closest('ul').find('> li').last();
				}
			} else if( ! prev.length ) {
				prev = item;
			}
			return prev;
		}

		var submenuLastItem = function( submenu ) {
			var column = submenu.find(' > .dj-subwrap-in > .dj-subcol').last();
			var menu_mod = column.find('.nav.menu');
			var target = ( menu_mod.length ) ? '> .dj-submenu > li .nav.menu > li' : '> .dj-submenu > li';
			var item = column.find(target).last();
			return item;
		}

		var findCharacter = function( item, character ) {

			var items;

			var submenu = item.closest('.dj-subwrap-in');
			if( submenu.length ) {
				var menu_mod = item.closest('.nav.menu');
				var target = ( menu_mod.length ) ? '> .dj-subcol > ul > li .nav.menu > li' : '> .dj-subcol > ul > li';
				items = submenu.find(target);
			} else {
				items = item.parent().find(' > li');
			}

			var i = items.index(item);

			var items_next = items.slice(i+1, items.length);
			var items_prev = items.slice(0, i);

			var next = items_next.filter('[data-key="' + character + '"]');
			var prev = items_prev.filter('[data-key="' + character+ '"]');

			if( next.length ) {
				return next.first();
			} else if( prev.length ) {
				return prev.first();
			} else {
				return item;
			}
		}

		var anchors = self.menu.find('a'); //all anchors, DJMenuItem and menu modules
		anchors.each(function() {
			var $this = $(this);
			var letter = $this.text().charAt(0).toLowerCase();

			$this.parent().attr('data-key', letter); //LI
		});

		anchors.on('focus', function(){
			//set tabindex on focus
			if(self.level === 0) { //only for main items
				self.options.menu.find('[tabindex="0"]').attr('tabindex', -1);
				$(this).attr('tabindex', 0);
			}
		});

		anchors.on('blur', function(){
			//console.log('blur');
			self.blurTimer = setTimeout(function() {
				if( ! self.options.menu.find(':focus').length ){
					var firstLvl = self;
					while(firstLvl.level > 0) {
						firstLvl.hideSub();
						firstLvl = firstLvl.parent;
					}
					firstLvl.hideSub();

					//when leave menu, reset tabindex
					self.options.menu.find('[tabindex="0"]').attr('tabindex', -1);
					firstLvl.menu.find('> a').attr('tabindex', 0);
				}
			}, self.options.openDelay);
		});

		self.options.menu.on('click', function(){ clearTimeout(self.blurTimer); });

		self.menu.on('keydown', function ( event ) {
			event.stopPropagation();

			$this = $(this);

			switch ( event.key ) {

				// ARROW RIGHT
				case 'ArrowRight':
					event.preventDefault();

					self.hideSub();

					if( self.level === 0 ) { //main item
						nextItem( $this ).find('> a').focus();
					} else if( self.sub.length ) { //have sub

						//If focus is on an item with a submenu, opens the submenu and places focus on the first item.

						self.showSub();
						setTimeout(function () {
							self.sub.find('a').first().focus();
						}, self.options.openDelay);
					} else if( $this.hasClass('subtree') ) {
						$this.find('.dj-subtree > li > a').first().focus();
					} else { //no sub

						//If focus is on an item that does not have a submenu:
						// Closes submenu.
						// Moves focus to next item in the menubar.
						// Opens submenu of newly focused menubar item, keeping focus on that parent menubar item.

						nextItem( self.menu.closest('.dj-up') ).trigger('djmegamenu:showSub').find('> a').focus();
					}
					break;

				// ARROW LEFT
				case 'ArrowLeft':
					event.preventDefault();

					self.hideSub();

					if( self.level === 0 ) { //main item
						prevItem( $this ).find('> a').focus();
					} else if( self.level == 1 ) {

						//If parent menu item is in the menubar, also:
						// moves focus to previous item in the menubar.
						// Opens submenu of newly focused menubar item, keeping focus on that parent menubar item.

						var parent = self.menu.closest('.dj-up');
						prevItem( parent ).trigger('djmegamenu:showSub').find('> a').focus();

					} else {

						//Closes submenu and moves focus to parent menu item.

						self.parent.menu.trigger('djmegamenu:hideSub').find('> a').focus();
					}
					return;

				// ARROW UP
				case 'ArrowUp':
					event.preventDefault();

					if( self.level === 0 ) { //main item
						if( self.sub.length ){
							self.showSub();
							setTimeout(function () {
								submenuLastItem( self.sub ).find('> a').focus();
							}, self.options.openDelay);
						}
					} else { //child items
						prevItem( $this ).find('> a').focus();
					}
					break;

				// ARROW DOWN
				case 'ArrowDown':
					event.preventDefault();

					if( self.level === 0 ) { //main item
						if( self.sub.length ){
							self.showSub();
							setTimeout(function () {
								self.sub.find('a').first().focus();
							}, self.options.openDelay);
						}
					} else { //child items
						nextItem( $this ).find('> a').focus();
					}
					break;


				// SPACE and ENTER
				case ' ': case 'Spacebar': case 'Enter':

					//Opens submenu and moves focus to first item in the submenu.

					if(event.key == ' ' || event.key == 'Spacebar') event.preventDefault();

					if( self.sub.length && (event.key == ' ' || event.key == 'Spacebar' || self.menu.hasClass('separator'))) { //have submenu and space
						self.showSub();
						setTimeout(function () {
							self.sub.find('a').first().focus();
						}, self.options.openDelay);
					} else { //no sub, trigger click
						$this.find('> a')[0].click();
					}

					break;

				// ESCAPE
				case 'Escape':
					if( self.level > 0 ) {
						event.preventDefault();
						self.parent.menu.trigger('djmegamenu:hideSub').find('> a').focus();
					} else {
						self.hideSub();
					}
					break;

				// HOME
				case 'Home':
					event.preventDefault();
					$this.parent().children().first().find('> a').focus();
					break;

				// END
				case 'End':
					event.preventDefault();
					$this.parent().children().last().find('> a').focus();
					break;

				default:
					// character keyboard support
					findCharacter($this, event.key).find('> a').focus();

			}

		});

		// menu modules and tree events
		self.menu.find('.nav.menu li, .dj-subtree li').on('keydown', function ( event ) {

			$this = $(this);
			switch ( event.key ) {

				// ARROW RIGHT
				case 'ArrowRight':
					event.preventDefault();
					if( $this.hasClass('parent') ) {
						event.stopPropagation();
						$this.find('> ul > li > a').first().focus();
					} else if( $this.parent().parent().hasClass('parent') ) {
						event.stopPropagation();
						$this.parent().parent().next().find('> a').focus();
					}
					break;

				// ARROW LEFT
				case 'ArrowLeft':
					event.preventDefault();
					if( $this.parent().parent().hasClass('parent') ) {
						event.stopPropagation();
						// li < ul < li
						$this.parent().parent().find('> a').focus();
					}
					break;

				// ARROW UP
				case 'ArrowUp':
					event.stopPropagation();
					event.preventDefault();

					prevItem( $this ).find('> a').focus();

					break;

				// ARROW DOWN
				case 'ArrowDown':
					event.stopPropagation();
					event.preventDefault();

					nextItem( $this ).find('> a').focus();

					break;

				// HOME
				case 'Home':
					event.stopPropagation();
					event.preventDefault();
					$this.closest('ul').find('> li').first().find('> a').focus();
					break;

				// END
				case 'End':
					event.stopPropagation();
					event.preventDefault();
					$this.closest('ul').find('> li').last().find('> a').focus();
					break;

				// ESCAPE
				case 'Escape':
					event.stopPropagation();
					event.preventDefault();
					$this.parents('li').find('> a').focus();
					break;

				// SPACE
				case ' ': case 'Spacebar':
					event.stopPropagation();
					if(event.key == ' ' || event.key == 'Spacebar') event.preventDefault();
					$this.find('> a')[0].click();

					break;

				default:
					// character keyboard support
					event.stopPropagation();
					findCharacter($this, event.key).find('> a').focus();
			}
		});

	}

	DJMMenuItem.prototype.showSub = function () {

		//console.log('showSub');

		var self = this;

		clearTimeout(self.timer);

		if (self.menu.hasClass('hover') && !self.sub.hasClass(self.options.animOut)) {
			return; // do nothing if menu is open
		}

		if (self.sub.length) {
			self.sub.css('display', 'none');
		}

		self.timer = setTimeout(function () {

			clearTimeout(self.animTimer);

			self.menu.addClass('hover');
			self.hideOther(); // hide other submenus at the same level

			if (self.sub.length) {
				self.sub.css('display', '');
				self.sub.removeClass(self.options.animOut);
				self.checkDir();
				self.sub.addClass(self.options.animIn);
				self.menu.find('> a').attr('aria-expanded', 'true');
				self.menu.trigger('djmegamenu:showsubmenu');
				if (self.sub.find('.modules-wrap').length) {
					// it's required to refresh the modules inside the submenu
					$(window).trigger('resize');
				}
			}
		}, self.options.openDelay);
	};

	DJMMenuItem.prototype.hideSub = function () {

		var self = this;

		//console.log('hideSub');

		clearTimeout(self.timer);

    if( '2' === self.options.parentOpen && self.options.activeParent.length && self.options.activeParent.hasClass('hover') ) {
        return; //do nothing
    }

		if (self.sub.length/* && self.options.eventClose != 'click'*/) {
			self.timer = setTimeout(function () {
				self.sub.removeClass(self.options.animIn);
				self.sub.addClass(self.options.animOut);
				self.animTimer = setTimeout(function () {
					self.menu.removeClass('hover');
					self.menu.find('> a').attr('aria-expanded', 'false');
					self.menu.trigger('djmegamenu:hidesubmenu');

          if( '2' === self.options.parentOpen && self.options.activeParent.length ) {
              self.options.activeParent.find('> a').trigger('click'); //re-open submenu
          }

				}, self.options.duration);
			}, self.options.closeDelay);
		} else {
			self.menu.removeClass('hover');
			self.menu.trigger('djmegamenu:leaveitem');
		}

	};

	DJMMenuItem.prototype.checkDir = function () {

		//console.log('checkDir');

		var self = this;

		self.sub.css('left', '');
		self.sub.css('right', '');
		self.sub.css('margin-left', '');
		self.sub.css('margin-right', '');

    var sub = self.sub.offset();
    var wrap = self.options.wrap.offset();

    if( self.menu.hasClass('fullwidth') ) {
      if (self.options.direction == 'ltr') {
        self.sub.css('left', -(sub.left));
        //self.sub.find('> .dj-subwrap-in').css('padding-left', sub.left);
      } else {
        self.sub.css('right', sub.left);
        //self.sub.find('> .dj-subwrap-in').css('padding-right', sub.left);
      }
      self.sub.css('width', document.body.clientWidth);
      return;
    }

		if ( self.menu.hasClass('fullsub') ) return;

		//console.log(sub);
		//if(self.options.wrap.hasClass('dj-megamenu')) { // fix wrapper position for sticky menu
		//var placeholder = $('#'+self.options.wrap.attr('id')+'placeholder');
		//if(placeholder.length) wrap = placeholder.offset();
		//}

		if (self.options.direction == 'ltr') {
			var offset = sub.left + self.sub.outerWidth() - self.options.wrap.outerWidth() - wrap.left;
			//console.log(offset+' = '+sub.left+' + '+self.sub.outerWidth()+' - '+self.options.wrap.outerWidth()+' - '+wrap.left);
			if (offset > 0 || self.sub.hasClass('open-left')) {
				if (self.level) {
					self.sub.css('right', self.menu.outerWidth());
					self.sub.css('left', 'auto');
				} else {
					if (self.sub.hasClass('open-left')) {
						self.sub.css('right', self.sub.css('left'));
						self.sub.css('left', 'auto');
					} else {
						self.sub.css('margin-left', -offset);
					}
				}
			}
		} else if (self.options.direction == 'rtl') {
			var offset = sub.left - wrap.left;
			//console.log(offset+' = '+sub.left+' - '+wrap.left);
			if (offset < 0 || self.sub.hasClass('open-right')) {
				if (self.level) {
					self.sub.css('left', self.menu.outerWidth());
					self.sub.css('right', 'auto');
				} else {
					if (self.sub.hasClass('open-right')) {
						self.sub.css('left', self.sub.css('right'));
						self.sub.css('right', 'auto');
					} else {
						self.sub.css('margin-right', offset);
					}
				}
			}
		}
	};

	DJMMenuItem.prototype.initKids = function () {

		var self = this;

		self.kids = [];

		var kids = self.sub.find('> .dj-subwrap-in > .dj-subcol > ul.dj-submenu > li');

		kids.each(function (index) {
			var kid = $(this);
			self.kids[index] = new DJMMenuItem(kid, self.level + 1, self, self.options);
		});
	};

	DJMMenuItem.prototype.hideOther = function () {

		//console.log('hideOther');

		var self = this;

		$.each(self.parent.kids, function (index, kid) {

			if (kid.menu.hasClass('hover') && kid != self) {

				if (kid.sub.length) {
					kid.hideOtherSub(); // hide next levels immediately

					kid.sub.removeClass(kid.options.animIn);
					kid.sub.addClass(kid.options.animOut);
					kid.animTimer = setTimeout(function () {
						kid.menu.removeClass('hover');
						kid.menu.find('> a').attr('aria-expanded', 'false');
					}, self.options.duration);
				} else {
					kid.menu.removeClass('hover');
				}
			}
		});
	};

	DJMMenuItem.prototype.hideOtherSub = function () {

		var self = this;

		$.each(self.kids, function (index, kid) {
			if (kid.sub.length) {
				kid.hideOtherSub();
				kid.sub.removeClass(kid.options.animIn);
				kid.sub.removeClass(kid.options.animOut);
			}
			kid.menu.removeClass('hover');
		});
	};
})(jQuery);

var initMenu = function() {
  if (typeof jQuery == 'undefined') {
		console.log('DJ-Megamenu: jQuery missing');
	} else {
		jQuery('.dj-megamenu[data-options]').each(function () {
			var menu = jQuery(this);
			menu.data();
			var options = menu.data('options');
			menu.removeAttr('data-options');
			new DJMegaMenu(menu, options);
		});
	}
}

if (document.readyState !== 'loading') {
  initMenu();
} else {
  document.addEventListener('DOMContentLoaded', function() {
    initMenu();
  });
}

window.addEventListener('load', function() {
	if (typeof jQuery == 'undefined') {
		console.log('DJ-Megamenu: jQuery missing');
	} else {
		jQuery(document).trigger('djmegamenu:pageload');
	}
});
