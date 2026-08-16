/**
 * @version $Id: jquery.djselect.js 24 2014-05-27 11:02:15Z szymon $
 * @package DJ-MegaMenu
 * @copyright Copyright (C) 2024 DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: https://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski, Artur Kaczmarek
 */

(function($){var l=function(g,h,j){var k='';for(var i=0;i<j;i++){k+='- '}g.each(function(){var a=$(this);var b=a.find('> a').first();var c=a.find('> .dj-subwrap > .dj-subwrap-in > .dj-subcol > .dj-submenu > li, > .dj-subtree > li');if(b.length){var d='';var e=b.find('img').first();if(e.length){d=k+e.attr('alt')}else{d=b.html().replace(/(<small[^<]+<\/small>)/ig,"");d=k+d.replace(/(<([^>]+)>)/ig,"")}var f=$('<option value="'+b.prop('href')+'">'+d+'</option>').appendTo(h);if(!b.prop('href')){f.prop('disabled',true)}if(a.hasClass('active')){h.val(f.val())}}if(c)l(c,h,j+1)})};$(window).load(function(){$('.dj-megamenu').each(function(){var a=$(this);var b=$('<select id="'+a.attr('id')+'select" class="inputbox dj-select" />').on('change',function(){if($(this).val)window.location=$(this).val()});var c=a.find('li.dj-up');l(c,b,0);b.insertAfter(a)})})})(jQuery);
