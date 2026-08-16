/**
 * @package DJ-MediaTools
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license DJ-Extensions.com Proprietary Use License
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 */
(function($){
	
	var ModDJMediaToolsAlbums = function(element){
		var self = this;
		
		self.module = jQuery(element);
		self.module.data();
		
		self.loader = self.module.find('.djloader').first();
		
		self.sendXhr(self.module.data('url'));
	};
	
	ModDJMediaToolsAlbums.prototype.xhr = null;
	
	ModDJMediaToolsAlbums.prototype.sendXhr = function(url) {
		var self = this;
		
		if (self.xhr && self.xhr.readyState != 4) {
			return;
		}
		
		self.module.append(self.loader);
		
		self.xhr = $.ajax({
			url: url,
			method: 'get',
			//data: 'tmpl=component',
			dataType: 'html'
		}).done(function(response) {
			if (response != '') {
				
				var html = jQuery(response);
				var djmediatools = html.find('#djmediatools');
				
				self.module.html(djmediatools);
				
				self.module.find('.dj-pagination a').click(function(event){
					
					var href = $(this).attr('href');
					
					if(href.length) {
						event.preventDefault();
						self.sendXhr(href);
					}
				});
			}
		}).always(function() {
			self.loader.detach();
		});
	};
	
	$(document).ready(function(){
		$('.djmediatools_albums').each(function(){
			new ModDJMediaToolsAlbums(this);
		});
	});
	
})(jQuery);
