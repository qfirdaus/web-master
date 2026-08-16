
(function (window){
	
	var APYTLoader = function (data){
				
		var self = this,
		$ = jQuery.noConflict(),
		type,
		currYtData,
		processData = [],
		finishData = [],
		channelArr = [],
		channel_counter=0,
		pl_path,//save from channel process
		user_channel_process,//process more than 1 yt channel
		youtubeCounter, 
		deeplinkCounter,
		youtubeEnlargeCounter = 50,
		youtubeLimit,
		ytOrder,
		searchOrderV2 = ['relevance','published','viewCount','rating'],
		plOrderV2 = ['position','commentCount','duration','published','reversedPosition','title','viewCount'],
		searchOrderV3 = ['date','rating','relevance','title','videoCount','viewCount'],
		apiVersion = 3,
		gapi_key = data.yt_app_id,
		protocol = $.inArray(window.location.protocol, ['http:', 'https:']) ? 'http:' : window.location.protocol;
		
		/*
		V3 order: //date,rating,relevance(default),title,videoCount,viewCount (now only for search?)
		
		V2 order https://developers.google.com/youtube/2.0/developers_guide_protocol_api_query_parameters#orderbysp
		video search: 
			relevance – Entries are ordered by their relevance to a search query. This is the default setting for video search results feeds.
			published – Entries are returned in reverse chronological order. This is the default value for video feeds other than search results feeds.
			viewCount – Entries are ordered from most views to least views.
			rating – Entries are ordered from highest rating to lowest rating.
		playlist:
			position – Entries are ordered by their position in the playlist. This is the default setting.
			commentCount – Entries are ordered by number of comments from most comments to least comments.
			duration – Entries are ordered by length of each playlist video from longest video to shortest video.
			published – Entries are returned in reverse chronological order.
			reversedPosition – Entries are ordered in reverse of their position in the playlist.
			title – Entries are ordered alphabetically by title.
			viewCount – Entries are ordered from most views to least views.
		*/

		this.setData = function(data){
			if(!gapi_key || gapi_key == ''){
				alert('Youtube API key missing! Please set API key in player settings.');
				return;
			}
			finishData = [];
			processData = $.extend(true, [], [data]);
			checkYoutube();
		}
		//PRIVATE	
		function checkYoutube() {
			if(processData.length){
				currYtData = processData.shift();

				type = currYtData.type;
				var path = currYtData.path, url;
				
				channelArr = [];
				deeplinkCounter = 0;
				youtubeCounter = 1;
				youtubeLimit = currYtData.limit ? currYtData.limit : 200;
				
				if(apiVersion == 2){
					ytOrder = currYtData.order ? currYtData.order : 'position';
					if(type == 'youtube_single'){
						url = protocol+'//gdata.youtube.com/feeds/api/videos/'+path+'?v=2&format=5&alt=jsonc';
						_processYoutube(url);
					}else{
						if(type == 'youtube_playlist'){
							if($.inArray(ytOrder, plOrderV2) == -1)ytOrder = 'position';
							url = protocol+"//gdata.youtube.com/feeds/api/playlists/"+path+"?start-index="+youtubeCounter+"&max-results="+youtubeEnlargeCounter+"&orderby="+ytOrder+"&v=2&format=5&alt=jsonc";
						}else if(type == 'youtube_video_query'){
							var query = currYtData.query || currYtData.path;
							if($.inArray(ytOrder, searchOrderV2) == -1)ytOrder = 'relevance';
							url = protocol+"//gdata.youtube.com/feeds/api/videos?q="+query+"&start-index="+youtubeCounter+"&max-results="+youtubeEnlargeCounter+"&orderby="+ytOrder+"&v=2&format=5&alt=jsonc";
						}else{
							alert('Wrong youtube type V2!' + type);
							return;
						}
						_processYoutubeComplexV2(url);
					}
				}else{
					ytOrder = currYtData.order ? currYtData.order : 'relevance';
					if(type == 'youtube_single'){
						//video, https://developers.google.com/youtube/v3/docs/videos
						url = 'https://www.googleapis.com/youtube/v3/videos?id='+path+'&key='+gapi_key+'&part=snippet,contentDetails,statistics,status';
					}else{
						if($.inArray(ytOrder, searchOrderV3) == -1)ytOrder = 'relevance';
						if(type == 'youtube_playlist'){
							//https://developers.google.com/youtube/v3/docs/playlistItems/list
							url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,status,contentDetails&maxResults='+youtubeEnlargeCounter+'&playlistId='+path+'&key='+gapi_key+'';
							//console.log(url)
						}else if(type == 'youtube_video_query'){
							//https://developers.google.com/youtube/v3/docs/search/list
							var query = currYtData.query || currYtData.path;
							url = 'https://www.googleapis.com/youtube/v3/search?part=id,snippet&maxResults='+youtubeEnlargeCounter+'&order='+ytOrder+'&q='+query+'&key='+gapi_key+'';
						}else if(type == 'youtube_user_channels'){
							//https://developers.google.com/youtube/v3/docs/channels/list (user_id)
							url = 'https://www.googleapis.com/youtube/v3/channels?part=contentDetails&maxResults='+youtubeEnlargeCounter+'&forUsername='+path+'&key='+gapi_key+'';
						}else if(type == 'youtube_channel'){
							//(channel id)
							url = 'https://www.googleapis.com/youtube/v3/channels?part=snippet,contentDetails&id='+path+'&key='+gapi_key+'';
						}else{
							alert('Wrong youtube type V3!');
							return;
						}
					}
					_processYoutube(url);
				}
			}else{
				$(self).trigger('APYTLoader.END_LOAD', [finishData]);
			}
		}
		function _processYoutube(url) {
			$.ajax({
				url: url,
				dataType: "jsonp"
			}).done(function(response) {
				if(apiVersion == 2){
					if(response.data && response.data.accessControl)finishData.push(getYtItemDataV2(response.data, type));//youtube_single
					checkYoutube();
				}else{
					processYtSuccessV3(response);
				}
			}).fail(function(jqXHR, textStatus, errorThrown) {
				alert('There was an error retrieveing youtube data: ' + jqXHR.responseText);
				checkYoutube();
			});	
		}
		function _processYoutubeComplexV2(url) {
			$.ajax({
				url: url,
				dataType: "jsonp"
			}).done(function(response) {
					 
				 if(response.data.items){
					
					 var len = response.data.items.length, i, _item;
					 if(len + deeplinkCounter > youtubeLimit)len = youtubeLimit - deeplinkCounter;
					 //console.log('... ', len, youtubeLimit);

					 for(i=0; i < len; i++){
						if(type == 'youtube_video_query'){
							_item = response.data.items[i];
						}else{
							_item = response.data.items[i].video;
						}
						if(_item && _item.accessControl)finishData.push(getYtItemDataV2(_item, type));
						deeplinkCounter++;
					}

					youtubeCounter += youtubeEnlargeCounter;	
					if(youtubeCounter <= youtubeLimit){
						var path = currYtData.path;
						if(type == 'youtube_playlist'){
							url = protocol+"//gdata.youtube.com/feeds/api/playlists/"+path+"?start-index="+youtubeCounter+"&max-results="+youtubeEnlargeCounter+"&orderby="+ytOrder+"&v=2&format=5&alt=jsonc";
						}else if(type == 'youtube_video_query'){
							url = protocol+"//gdata.youtube.com/feeds/api/videos?q="+path+"&start-index="+youtubeCounter+"&max-results="+youtubeEnlargeCounter+"&orderby="+ytOrder+"&v=2&format=5&alt=jsonc";
						}
						_processYoutubeComplexV2(url);
					}else{
						checkYoutube();
					}
				 }else{
					checkYoutube();
				 }
			}).fail(function(jqXHR, textStatus, errorThrown) {
				alert('Error retrieveing youtube data V2: ' + jqXHR.responseText);
				checkYoutube();
			});	
		}
		function processYtSuccessV3(response, set) {
			//console.log(response);
			 var i, len = response.items.length, obj;
			 if(len + deeplinkCounter > youtubeLimit)len = youtubeLimit - deeplinkCounter;
			 //console.log('... ', len, youtubeLimit);
			 for(i=0; i < len; i++){
				 _item = response.items[i];
				 if(_item){
					 if(type=='youtube_playlist' || type=='youtube_single'){
						 if(_item.status.privacyStatus != 'private'){
							 finishData.push(getYtItemDataV3(_item, type));
						 }else{
							 //console.log(_item.status.privacyStatus);	 	 
						 }
						 deeplinkCounter++;
					 }
					 else if(type=='youtube_video_query'){
						 finishData.push(getYtItemDataV3(_item, type));
						 deeplinkCounter++;
					 }else if(type=='youtube_user_channels' || type=='youtube_channel'){
						 channelArr.push(_item.contentDetails.relatedPlaylists.uploads);//get playlist ids
					 }
				 }
			 }
			 if(type=='youtube_single'){
				  checkYoutube();
			 }else{
				  if(type!='youtube_user_channels' && type!='youtube_channel'){
					 youtubeCounter += youtubeEnlargeCounter;	
					 //console.log(youtubeCounter, youtubeLimit);
					 if(youtubeCounter < youtubeLimit){
						  var totalResults = response.pageInfo.totalResults;
						  if(youtubeCounter <= totalResults && response.nextPageToken){
							  if(type=='youtube_playlist'){
								  var url = 'https://www.googleapis.com/youtube/v3/playlistItems?pageToken='+response.nextPageToken+'&part=snippet,status,contentDetails&maxResults='+youtubeEnlargeCounter+'&playlistId='+pl_path+'&key='+gapi_key+'';
							  }else if(type=='youtube_video_query'){
								  var path = currYtData.path, 
								  url = 'https://www.googleapis.com/youtube/v3/search?pageToken='+response.nextPageToken+'&part=id,snippet&maxResults='+youtubeEnlargeCounter+'&order='+ytOrder+'&q='+path+'&key='+gapi_key+''; 
							  }
							  _processYoutube(url);
						  }else{
							  checkYoutube();
						  }
					 }else{
						 if(user_channel_process){
							channel_counter++;
							if(channel_counter<channelArr.length){
								youtubeCounter=0;//reset
								pl_path = channelArr[channel_counter]; 
								var url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,status,contentDetails&maxResults='+youtubeEnlargeCounter+'&playlistId='+pl_path+'&key='+gapi_key+'';
								_processYoutube(url);
							}else{
								user_channel_process = false;
								checkYoutube();
							}
						 }
						 checkYoutube();
					 }
				 }else{
					 if(channelArr.length){
						 //console.log(channelArr);
						 //for youtube_user_channels we need to get playlist ids for all channels, then process each playlist id! 
						 if(channelArr.length>1)user_channel_process=true;
						 pl_path = channelArr[channel_counter];
						 var url = 'https://www.googleapis.com/youtube/v3/playlistItems?part=snippet,status,contentDetails&maxResults='+youtubeEnlargeCounter+'&playlistId='+pl_path+'&key='+gapi_key+'';
						 type='youtube_playlist';//switch
						 _processYoutube(url);
					 }else{
						 checkYoutube();
					 }
				 }
			 }
		 }
		 function getYtItemDataV2(_item) {
			 //if(_item && _item.accessControl)//skip deleted, private videos
			 //http://apiblog.youtube.com/2011/12/understanding-playback-restrictions.html
			 //https://developers.google.com/youtube/2.0/developers_guide_protocol_uploading_videos#Setting_Access_Controls
			 //console.log(_item.status.value);
			 
			 var obj = jQuery.extend(true, {}, currYtData);
			 if(obj.deeplink){
				if(obj.type != 'youtube_single')obj.deeplink = obj.deeplink+(deeplinkCounter+1).toString();	
			 }
			 obj.data = _item;
			 obj.type = 'youtube';
			 obj.origtype = type;
			 obj.id=_item.id;
			 if(!obj.title)obj.title=_item.title?_item.title:null;
			 if(!obj.description)obj.description = _item.description?_item.description:null;
			 if(!obj.thumb && _item.thumbnail){
				 if(_item.thumbnail.mqdefault)obj.thumb=_item.thumbnail.mqdefault;
				 else if(_item.thumbnail.hqDefault)obj.thumb=_item.thumbnail.hqDefault;
				 else if(_item.thumbnail.sqDefault)obj.thumb=_item.thumbnail.sqDefault;
				 obj.thumb = obj.thumb.substr(0,obj.thumb.lastIndexOf('/')+1)+'mqdefault.jpg';//manually get 16:9 format, api2 doesnt have it listed
			 }	
			 return obj;
		}
		function getYtItemDataV3(_item, type) {
			 var obj = jQuery.extend(true, {}, currYtData);
			 if(obj.deeplink){
				if(obj.type != 'youtube_single')obj.deeplink = obj.deeplink+(deeplinkCounter+1).toString();	
			 }
			 obj.data = _item;
			 obj.type = 'youtube';
			 obj.origtype = type;
			 if(type=='youtube_single'){
				obj.id = _item.id;
			 }else if(type=='youtube_playlist'){
				obj.id = _item.contentDetails.videoId;
			 }else if(type=='youtube_video_query'){
				obj.id = _item.id.videoId; 
			 }
			 if(!obj.title)obj.title = _item.snippet.title?_item.snippet.title:null;
			 if(!obj.description)obj.description = _item.snippet.description?_item.snippet.description:null;
			 if(!obj.thumb && _item.snippet.thumbnails){
				 if(_item.snippet.thumbnails.medium)obj.thumb=_item.snippet.thumbnails.medium.url;
				 else if(_item.snippet.thumbnails.standard)obj.thumb=_item.snippet.thumbnails.standard.url;
				 /*
				 if(_item.snippet.thumbnails.default)obj.thumb=_item.snippet.thumbnails.default.url;//"default" - reserved keyword, fails in ie8!
				 else if(_item.snippet.thumbnails.medium)obj.thumb=_item.snippet.thumbnails.medium.url;
				 else if(_item.snippet.thumbnails.standard)obj.thumb=_item.snippet.thumbnails.standard.url;
				 */
			 }
			 return obj;
		}
	
	};	

	window.APYTLoader = APYTLoader;

}(window));



/* DEFAULTS */

var vplp_mediaArr = [];
var audio = document.createElement('audio'), mp3Support, oggSupport, html5Support=false;
if (audio.canPlayType) {
	html5Support=true;
	mp3Support = !!audio.canPlayType && "" != audio.canPlayType('audio/mpeg');
	oggSupport = !!audio.canPlayType && "" != audio.canPlayType('audio/ogg; codecs="vorbis"');
}

var isMobile = (/Android|webOS|iPhone|iPad|iPod|sony|BlackBerry/i.test(navigator.userAgent));
var isIOS=false, agent = navigator.userAgent;
var isAndroid = agent.indexOf("Android") > -1;
var isiPhoneIpod = agent.indexOf('iPhone') > -1 || agent.indexOf('iPod') > -1;
var isiPad = agent.indexOf('iPad') > -1;
if(agent.indexOf('iPhone') > -1 || agent.indexOf('iPod') > -1 || agent.indexOf('iPad') > -1) {
	 isIOS=true;
}
var mobile_type;
if (agent.indexOf('iPhone') > -1 || agent.indexOf('iPod') > -1 || agent.indexOf('iPad') > -1) {
	if(agent.indexOf('iPhone') > -1)mobile_type = 'iPhone';
	else if(agent.indexOf('iPod') > -1)mobile_type = 'iPod';
	else if(agent.indexOf('iPad') > -1)mobile_type = 'iPad';
}

//http://stackoverflow.com/questions/9847580/how-to-detect-safari-chrome-ie-firefox-and-opera-browser
var isSafari = Object.prototype.toString.call(window.HTMLElement).indexOf('Constructor') > 0;
var isChrome = !isSafari && testCSS('WebkitTransform');
if(isIOS && navigator.userAgent.match('CriOS'))isChrome = true;
var isOpera = !!(window.opera && window.opera.version);
function testCSS(prop) {
	return prop in document.documentElement.style;
}

var isWindows = false;
if (agent.toLowerCase().indexOf("windows") !== -1) {
  isWindows = true;
}


var isIE = false, ieBelow9 = false, ieBelow8 = false;
var ie_check = getInternetExplorerVersion();
if (ie_check != -1){
	isIE = true;
	if(ie_check < 9)ieBelow9 = true;
	if(ie_check < 8)ieBelow8 = true;
} 


function getInternetExplorerVersion(){
  var rv = -1;
  if (navigator.appName == 'Microsoft Internet Explorer')
  {
	var ua = navigator.userAgent;
	var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
	if (re.exec(ua) != null)
	  rv = parseFloat( RegExp.$1 );
  }
  else if (navigator.appName == 'Netscape')
  {
	var ua = navigator.userAgent;
	var re  = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
	if (re.exec(ua) != null)
	  rv = parseFloat( RegExp.$1 );
  }
  return rv;
}

var hasLocalStorage = supports_local_storage();
function supports_local_storage() {
  try {
	return 'localStorage' in window && window['localStorage'] !== null;
  } catch(e){
	return false;
  }
}

function inject_facebook_sdk(fs_app_id) {
	var fb_root, script, protocol = jQuery.inArray(window.location.href.split(':')[0], ['http', 'https']) ? 'https://' : '//';
	//console.log(protocol);
	if (!window.FB && fs_app_id && !document.body.querySelector('#fb-root')) {
	  script = document.createElement("script");
	  script.text = "window.fbAsyncInit=function(){FB.init({appId:'" + fs_app_id + "',status:true,xfbml:true})};(function(e,t,n){var r,i=e.getElementsByTagName(t)[0];if(e.getElementById(n)){return}r=e.createElement(t);r.id=n;r.src='" + protocol + "connect.facebook.net/en_US/all.js';i.parentNode.insertBefore(r,i)})(document,'script','facebook-jssdk')";
	  fb_root = document.createElement("div");
	  fb_root.id = "fb-root";
	  document.body.appendChild(fb_root);
	  return document.body.appendChild(script);
	}
}

/* END DEFAULTS */





/*
 * Video Gallery With Live Playlist 4.1
 * http://codecanyon.net/item/html5-video-gallery-with-live-playlist-/490139
 */

(function($) {

	$.fn.videoGallery = function(settings, is_popup) {
	//console.log(settings);
	
	var source_path = settings.source_path || '';
	
	var _componentInited=false;
	var _self = this;
	vplp_mediaArr.push({player_id: _self, media_id: settings.media_id});
	
	var _body = $('body');
	var _window = $(window);
	var _doc = $(document);
	
	var baseURL = window.location.href, protocol = window.location.protocol;
	if(protocol=='file:')protocol='http:';

	var _downEvent = "";
	var _moveEvent = "";
	var _upEvent = "";
	var hasTouch;
	var touchOn=true;
	if("ontouchstart" in window) {
		hasTouch = true;
		_downEvent = "touchstart.ap";
		_moveEvent = "touchmove.ap";
		_upEvent = "touchend.ap";
	}else{
		hasTouch = false;
		_downEvent = "mousedown.ap";
		_moveEvent = "mousemove.ap";
		_upEvent = "mouseup.ap";
	}

	var prevX=0;//mouse track pos
	
	//****************
	
	var mp4Support = canPlayMP4();
	var vorbisSupport = canPlayVorbis();
	var webmSupport = canPlayWebM();
	//console.log("vorbisSupport = " + vorbisSupport, ", mp4Support = " + mp4Support, ", webmSupport = " + webmSupport);
	var html5Support=(!!document.createElement('video').canPlayType); 
	if(settings.playlist_type == 'list'){
		
		var markup =  
		'<div class="componentWrapper">'+
             '<div class="playerHolder">'+
                 '<div class="mediaHolder"></div>'+
                 '<div class="mediaPreview"></div>'+
                 '<div class="preloader"></div>'+
				 '<div class="big_play"><i class="fa fa-play ap_bplay icon_color"></i></div>';
				 
				 if(!isEmpty(settings.logo_path)){
					 markup += '<div class="playerLogo hidden tooltip" data-aptitle="'+settings.logo_tooltip_text+'"><a href="'+settings.logo_url+'" target="'+settings.logo_target+'"><img src="'+settings.logo_path+'" alt="'+settings.logo_tooltip_text+'" /></a></div>';
				 }
				 
                 markup += '<div class="playerControls">'+
                      '<div class="player_toggleControl"><i class="fa fa-play ap_play icon_color"></i></div>'+
                      '<div class="player_mediaTime_current"><p>00:00</p></div>'+ 
                      '<div class="player_seekbar">'+
                          '<div class="progress_bg">'+
                              '<div class="load_level"></div>'+
                              '<div class="progress_level"></div>'+
                          '</div>'+
                          '<div class="player_progress_tooltip tooltip" data-aptitle="Time"></div>'+
                      '</div>'+
                      '<div class="player_mediaTime_total"><p>00:00</p></div>'+
                      '<div class="player_captions">'+ 
                          '<div class="caption_holder"></div>'+
                          '<div class="caption_btn tooltip" data-aptitle="Captions"><i class="fa fa-file-text-o ap_cc icon_color"></i></div>'+
                      '</div>'+ 
                      '<div class="player_quality">'+ 
                          '<div class="quality_holder"></div>'+
                          '<div class="quality_btn tooltip" data-aptitle="Quality"><i class="fa ap_play fa-play icon_color"></i></div>'+
                      '</div>'+ 
                      '<div class="player_download tooltip" data-aptitle="Download video"><i class="fa fa-download ap_down icon_color"></i></div>'+
                      '<div class="player_volume_wrapper">'+
                          '<div class="player_volume tooltip" data-aptitle="Volume"><i class="fa fa-volume-up ap_vol icon_color"></i></div>'+
                          '<div class="volume_seekbar">'+
                             '<div class="player_volume_tooltip tooltip" data-aptitle="Volume"></div>'+
                             '<div class="volume_bg"></div>'+
                             '<div class="volume_level"></div>'+
                          '</div>'+
                      '</div>'+
                      '<div class="player_fullscreen tooltip" data-aptitle="Toggle Fullscreen"><i class="fa fa-expand ap_fs_ent icon_color"></i></div>'+
                 '</div>'+
             	 '<div class="infoHolder"><div class="info_inner"></div></div>'+
                 '<div class="player_addon">'+
                     '<div class="info_toggle tooltip" data-aptitle="Description"><i class="fa fa-info-circle ap_pl_info icon_color"></i></div>';
					 
					 if(settings.use_share){
					 
						 markup += '<div class="ap_share">'+
							 '<div class="ap_share_btn tooltip" data-aptitle="Share"><i class="fa fa-share-square ap_pl_share icon_color"></i></div>'+
							 '<div class="ap_share_holder">'+
								'<div class="ap_share_wrapper">'+
									'<ul>'+
										'<li><a class="tooltip" href="#" data-type="facebook" data-aptitle="Share on Facebook"><img src="'+source_path+'data/social/facebook.png" alt="facebook"/></a></li>'+
										'<li><a class="tooltip" href="#" data-type="twitter" data-aptitle="Share on Twitter"><img src="'+source_path+'data/social/twitter.png" alt="twitter"/></a></li>'+
										'<li><a class="tooltip" href="#" data-type="google" data-aptitle="Share on Google"><img src="'+source_path+'data/social/google.png" alt="google"/></a></li>'+
										'<li><a class="tooltip" href="#" data-type="pinterest" data-aptitle="Share on Pinterest"><img src="'+source_path+'data/social/pinterest.png" alt="pinterest"/></a></li>'+
										'<li><a class="tooltip" href="#" data-type="linkedin" data-aptitle="Share on Linkedin"><img src="'+source_path+'data/social/linkedin.png" alt="linkedin"/></a></li>'+
										'<li><a class="tooltip" href="#" data-type="digg" data-aptitle="Share on Digg"><img src="'+source_path+'data/social/digg.png" alt="digg"/></a></li>'+
										'<li><a class="tooltip" href="#" data-type="reddit" data-aptitle="Share on Reddit"><img src="'+source_path+'data/social/reddit.png" alt="reddit"/></a></li>'+
										'<li><a class="tooltip" href="#" data-type="tumblr" data-aptitle="Share on Tumblr"><img src="'+source_path+'data/social/tumblr.png" alt="tumblr"/></a></li>'+
										'<li><a class="tooltip" href="#" data-type="stumbleupon" data-aptitle="Share on Stumbleupon"><img src="'+source_path+'data/social/stumbleupon.png" alt="stumbleupon"/></a></li>'+
										'<li><a class="tooltip" href="#" data-type="delicious" data-aptitle="Share on Delicious"><img src="'+source_path+'data/social/delicious.png" alt="delicious"/></a></li>'+
								   '</ul>'+
								'</div>'+
							 '</div>'+
						 '</div>';
					 
				 	}
					 
                 markup += '</div>'+
            	 '<div class="ap_adv_skip">'+
                	 '<div class="ap_adv_msg"><p>'+settings.advert_skip_video_text+'</p></div>'+
                     '<div class="ap_adv_img"></div>'+
                     '<div class="ap_adv_msg_end"><p>'+settings.advert_skip_btn_text+'</p></div>'+
                 '</div>'+
             '</div>';
			 
		if(settings.show_playlist){
			 
             markup += '<div class="playlistHolder">'+
                 '<div class="componentPlaylist">'+
                     '<div class="playlist_inner"><div class="playlist_content"></div></div>'+
                 '</div>';
			
			if(settings.scroll_type == 'buttons'){
				 
				if(settings.playlist_orientation == 'vertical'){
					markup += '<div class="thumbBackward"><i class="fa fa-chevron-up ap_pl_back icon_color"></i></div>'+
					 '<div class="thumbForward"><i class="fa fa-chevron-down ap_pl_forw icon_color"></i></div>'+
				     '</div>';
				}else{
					markup += '<div class="thumbBackward"><i class="fa fa-chevron-left ap_pl_back icon_color"></i></div>'+
					'<div class="thumbForward"><i class="fa fa-chevron-right ap_pl_forw icon_color"></i></div>'+
				    '</div>';

				} 
			}
		}
		
        markup += '</div>';
		
		if(settings.popup_url == 'popup_list.html')markup += '<div class="popup_toggle"><p>OPEN IN POPUP</p></div>';
		
	}else{//wall
		
		var markup = 
             '<div class="componentWrapper">'+
             '<div class="playlistHolder">'+
                 '<div class="componentPlaylist">'+
                     '<div class="playlist_inner"><div class="playlist_content"></div></div>'+
                 '</div>'+
             '</div> '+
           '</div>'+
           '<div class="preloader"></div>';
		
	}
	
	$(markup).appendTo($(this));
	
	//elements
	var mainWrapper = $(this);
	var componentWrapper = mainWrapper.find('.componentWrapper');
	var componentPlaylist = mainWrapper.find('.componentPlaylist');
	var innerWrapper = mainWrapper.find('.innerWrapper');
	var playlist_type = settings.playlist_type;
	var playlist_inner = playlist_type == 'list' ? mainWrapper.find('.playlist_inner') : componentWrapper.find('.playlist_inner');
	var playlist_content = mainWrapper.find('.playlist_content');
	var playlistHolder=mainWrapper.find('.playlistHolder'), playlistHolderLeft;
	var playlistHidden = settings.show_playlist ? false : true;
	var playlist_list= $(settings.playlist_list).css('display','none');
	var playerHolder=mainWrapper.find('.playerHolder');
	var phow = playerHolder.width(), plhow = playlistHolder.width();
	var player_mediaTime_current=mainWrapper.find('.player_mediaTime_current');
	var player_mediaTime_total=mainWrapper.find('.player_mediaTime_total');
	var playerControls=mainWrapper.find('.playerControls');
	var player_toggleControl = mainWrapper.find('.player_toggleControl');
	var player_seekbar = mainWrapper.find('.player_seekbar');
	var progress_bg = mainWrapper.find('.progress_bg');
	var load_level = mainWrapper.find('.load_level');
	var progress_level = mainWrapper.find('.progress_level');
	var volume_seekbar = mainWrapper.find('.volume_seekbar').css('zIndex',300);
	var volume_bg = mainWrapper.find('.volume_bg');
	var volume_level = mainWrapper.find('.volume_level');
	var player_fullscreen= mainWrapper.find('.player_fullscreen');
	//captions
	var player_captions= mainWrapper.find('.player_captions');
	var caption_holder= mainWrapper.find('.caption_holder').css('zIndex',301);
	
	
	var playerLogo = mainWrapper.find('.playerLogo'),
	logo_position = settings.logo_position,
	logo_x_offset = settings.logo_x_offset,
	logo_y_offset = settings.logo_y_offset;
	
	if(logo_position == 'tl'){
		playerLogo.css({
			top: logo_y_offset+'px',
			left: logo_x_offset+'px'
		});
	}else if(logo_position == 'tr'){
		playerLogo.css({
			top: logo_y_offset+'px',
			right: logo_x_offset+'px'
		});
	}else if(logo_position == 'bl'){
		playerLogo.css({
			bottom: logo_y_offset+'px',
			left: logo_x_offset+'px'
		});
	}else if(logo_position == 'br'){
		playerLogo.css({
			bottom: logo_y_offset+'px',
			right: logo_x_offset+'px'
		});
	}
	 
	
	
	
	var caption_btn= mainWrapper.find('.caption_btn').css('cursor','pointer');
	if(!isMobile){
		player_captions.bind('mouseenter', function(e){
			if(!_componentInited || _playlistTransitionOn) return false;
			if(!captionMenuSizeTaken)checkCaptionMenuSize();
			caption_holder.css('display','block');
		}).bind('mouseleave', function(e){
			if(!_componentInited || _playlistTransitionOn) return false;
			caption_holder.css('display','none');
		});
	}else{
		caption_btn.on('click', function(){
			if(!_componentInited || _playlistTransitionOn) return false;
			quality_holder.css('display','none');
			if(!captionMenuSizeTaken)checkCaptionMenuSize();
			caption_holder.toggle();
			return false;
		});
	}
	function checkCaptionMenuSize(){
		caption_holder.css({display: 'block'});
		var sum = 0;
		captions_menu.find('li').each(function() {
		   sum += $(this).outerHeight(true); 
		});
		//console.log('toggleCaptionMenu: ', sum)
		//console.log(playerHolder.offset(), caption_btn.offset(), caption_holder.height(), captions_menu.height());
		if(captions_menu.height() < caption_btn.offset().top){//fits above
			caption_holder.css({top: -sum-2+'px', display: 'none'});
		}else{
			caption_holder.css({top: caption_btn.height()+'px', display: 'none'});
		}
		captionMenuSizeTaken=true;
	}
	
	//advert skip
	var show_controls_in_advert = settings.show_controls_in_advert,
	disable_seekbar_in_advert = settings.disable_seekbar_in_advert,
	show_skip_button_in_advert = settings.show_skip_button_in_advert,
	skipEnableTime,
	skipTimeHappened,
	ap_adv_skip= mainWrapper.find('.ap_adv_skip').css('cursor', 'pointer').bind('click', function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!advert_on) return false;
		if(skipEnableTime && !skipTimeHappened)return false;
		advert_done = true;
		if(!isMobile)setAutoplay(true);
		_findMedia();
		return false;
	}),
	ap_adv_img = mainWrapper.find('.ap_adv_img'),
	ap_adv_msg = mainWrapper.find('.ap_adv_msg'),
	ap_adv_msg_end = mainWrapper.find('.ap_adv_msg_end'),
	skipTimeText = ap_adv_msg.find('p').html();
	
	//quality
	var player_quality = componentWrapper.find('.player_quality');
	var quality_holder = componentWrapper.find('.quality_holder').css('zIndex',303);
	var quality_btn = componentWrapper.find('.quality_btn').css('cursor','pointer');
	if(!isMobile){
		player_quality.bind('mouseenter', function(e){
			if(!_componentInited || _playlistTransitionOn) return false;
			if(!qualityMenuSizeTaken)checkQualityMenuSize();
			quality_holder.css('display','block');
		}).bind('mouseleave', function(e){
			if(!_componentInited || _playlistTransitionOn) return false;
			quality_holder.css('display','none');
		});
	}else{
		quality_btn.on('click', function(){
			if(!_componentInited || _playlistTransitionOn) return false;
			caption_holder.css('display','none');
			if(!qualityMenuSizeTaken)checkQualityMenuSize();
			quality_holder.toggle();
			return false;
		});
	}
	function checkQualityMenuSize(){
		quality_holder.css({display: 'block'});
		var sum = 0;
		quality_menu.find('li').each(function() {
		   sum += $(this).outerHeight(true); 
		});
		//console.log('toggleQualityMenu: ', sum)
		if(quality_menu.height() < quality_btn.offset().top){//fits above
			quality_holder.css({top: -sum-2+'px', display: 'none'});
		}else{
			quality_holder.css({top: quality_btn.height()+'px', display: 'none'});
		}
		qualityMenuSizeTaken=true;
	}
	
	//download
	var player_download = mainWrapper.find('.player_download');
	var _downloadOn,  mailSet, dl_mail;
	if(componentWrapper.find("iframe[class='dl_iframe']").length==0){
		var dl_iframe = $('<iframe class="dl_iframe"/>').css({position:'absolute',left:-out_left+'px',display:'none'}).appendTo(componentWrapper);
	}else{
		var dl_iframe = componentWrapper.find("iframe[class='dl_iframe']");
	}
	if(isMobile){//download confirmation
		var downConf_timeoutID, downConf_timeout=2000;
		if(playerHolder.find("div[class='download_confirm']").length==0){
			var download_confirm = $('<div class="download_confirm"><p>DOWNLOAD STARTING</p></DIV>').css({opacity:0, zIndex:1000}).appendTo(playerHolder);	
		}else{
			var download_confirm = playerHolder.find("div[class='download_confirm']");	
		}
	}
	
	var flashMain, flashPreview, flashPreviewHolder, youtubeIframeMain, click_blocker, embedFlashInt=20, embedFlashIntID;
	var use_live_preview=!isMobile ? settings.use_live_preview : false;
	if(html5Support && use_live_preview){
		youtubeIframePreview=$('<div class="youtubeIframePreview"/>');
	}
	var out_left=10000;
	
	var playerHolder=mainWrapper.find('.playerHolder');
	var mediaHolder=mainWrapper.find('.mediaHolder').bind('click', function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		togglePlayBack();
		return false;
	});
	var mediaPreview=mainWrapper.find('.mediaPreview').bind('click', function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		togglePlayBack();
		return false;
	});
	var player_addon = mainWrapper.find('.player_addon');
	
	//share
	var useShare;
	if(mainWrapper.find('.ap_share').length)useShare=true;
	if(useShare){
		var ap_share_btn = mainWrapper.find('.ap_share_btn');
		var ap_share_holder = mainWrapper.find('.ap_share_holder');
		
		player_addon.css({visibility:'hidden', display:'block'});//show to measure
		ap_share_holder.css('display','block');
		
		var sw=0, shareArr = [], use_fs, ap_share_wrapper = mainWrapper.find('.ap_share_wrapper');
		ap_share_wrapper.find('ul').children('li').each(function(){
			sw += $(this).outerWidth(true);
			shareArr.push($(this).children('a[class*=tooltip]'));
			if($(this).children('a[class*=tooltip]').attr('data-type').toLowerCase() == 'facebook')use_fs=true;
			//console.log($(this).children('a[class*=tooltip]').attr('data-type'));
		});
		if(use_fs && settings.fs_app_id)inject_facebook_sdk(settings.fs_app_id);
		ap_share_wrapper.width(sw);
		
		player_addon.css({visibility:'visible', display:'none'});//hide after measure
		ap_share_holder.css('display','none');
		
		if(!isMobile){
			ap_share_holder.bind('mouseenter', function(){
				if(!_componentInited) return false;
				if(ap_share_wrapper.width() <= ap_share_holder.width()) return false;
				ap_share_holder.unbind('mousemove').bind('mousemove', scrollContent);
				return false;	
			}).bind('mouseleave', function(){
				if(!_componentInited) return false;
				ap_share_holder.unbind('mousemove');
				return false;	
			});
		}else{
			if(hasTouch){
				initShareTouch();
			}
		}
			
		$(shareArr).each(function(){
			$(this).bind('click', function(e){
				
				var i = _playlistManager.getCounter();
				if(playlistDataArr[i]){
				
					var type = $(this).attr('data-type').toLowerCase(), 
					w = 600, h = 300, cw = (window.screen.width - w) / 2, ch = (window.screen.height - h) / 2,
					data = playlistDataArr[i], path,
					title = data.title,
					description = data.description,
					td = data.title+' \n'+data.description,
					thumb = data.thumb,
					url = data.deeplink;	
						
					if(type == "facebook"){	
						path = protocol+'//www.facebook.com/sharer/sharer.php?u='+encodeURIComponent(thumb);
						if(window.FB){
							getFbSdk(url, thumb, title, description);
							return false;	
						}
					}else if(type == "twitter"){	
						path = protocol+'//twitter.com/share?url='+encodeURIComponent(url)+'&text='+encodeURIComponent(td);
					}else if(type == "google"){	
						path = protocol+'//plus.google.com/share?url='+encodeURIComponent(thumb);
					}else if(type == "pinterest"){		
						path = 'https://www.pinterest.com/pin/create/button?url='+encodeURIComponent(thumb)+'&description='+encodeURIComponent(title)+'&media='+encodeURIComponent(thumb);
					}else if(type == "linkedin"){		
						path = protocol+'//www.linkedin.com/shareArticle?mini=true&url='+encodeURIComponent(thumb)+'&title='+encodeURIComponent(title)+'&summary='+encodeURIComponent(description);
					}else if(type == "digg"){	
						path = protocol+'//digg.com/submit?url='+encodeURIComponent(url);
					}else if(type == "reddit"){	
						path = protocol+'//reddit.com/submit?url='+encodeURIComponent(url)+'&title='+encodeURIComponent(td);
					}else if(type == "tumblr"){		
						path = protocol+'//www.tumblr.com/share/link?url='+encodeURIComponent(url)+'&name='+encodeURIComponent(title)+'&description='+encodeURIComponent(description);
					}else if(type == "stumbleupon"){	
						path = protocol+'//www.stumbleupon.com/submit?url='+encodeURIComponent(url)+'&title='+encodeURIComponent(title);
					}else if(type == "delicious"){	
						path = protocol+'//del.icio.us/post?url='+encodeURIComponent(url)+'&title='+encodeURIComponent(td);
					}
					if(path)window.open(path, '', 'menubar=no,toolbar=no,resizable=yes,scrollbars=yes,width='+w+',height='+h+',left='+cw+',top='+ch+'');
				}
				if(use_tooltips)$(e.currentTarget).tooltipster('hide');
				return false;
			});		
		});
	}
	function getFbSdk(url, thumb, title, description){
		//console.log(window.FB);
		if(window.FB){
			 return FB.ui({
				method: 'feed',
				name: document.title,
				link: url,
				picture: thumb,
				caption: title,
				description: description
			 });
		}
	}	
	function scrollContent(e){
		var content = ap_share_wrapper, wrapper = ap_share_holder,
		wrapper_x = parseInt(wrapper.css('left'),10), wrapper_w = wrapper.width(), content_w = content.width(),
		_preOffset = 10, _afterOffset = 15, _rightValue = _preOffset, _leftValue = _preOffset + _afterOffset, z = wrapper_x;
		content.css('left', ((wrapper_w - content_w - _leftValue)/wrapper_w * (e.pageX - z) + _rightValue)+'px');
	}
	function initShareTouch(){
		var startX,
			startY,
			touchStartX,
			touchStartY,
			moved,
			moving = false,
			wrapper = ap_share_holder,
			content = ap_share_wrapper,
			orientation ='horizontal';
			
		content.unbind('touchstart.ap touchmove.ap touchend.ap click.ap-touchclick').bind(
			'touchstart.ap',
			function(e){
				if(!_componentInited) return false;
				if(!touchOn){//if touch disabled we want click executed
					return true;
				}
				var touch = e.originalEvent.touches[0];
				startX = content.position().left;
				startY = content.position().top;
				touchStartX = touch.pageX;
				touchStartY = touch.pageY;
				moved = false;
				moving = true;
			}
		).bind(
			'touchmove.ap',
			function(ev){
				if(!moving){
					return;
				}
				var touchPos = ev.originalEvent.touches[0];
				if(orientation =='horizontal'){
					var value = startX - touchStartX + touchPos.pageX, w = wrapper.width();
					
					if(value > 0)value=0;	
					if(value < w- content.width())value=w- content.width();	
						
					content.css('left',value+'px');
				}else{
					var value=startY - touchStartY + touchPos.pageY, h = wrapper.height();
					
					if(value > 0)value=0;	
					if(value < h- content.width())value=h- content.width();	
					
					content.css('top',value+'px');
				}
				moved = moved || Math.abs(touchStartX - touchPos.pageX) > 5 || Math.abs(touchStartY - touchPos.pageY) > 5;
				
				return false;
			}
		).bind(
			'touchend.ap',
			function(e){
				moving = false;
			}
		).bind(
			'click.ap-touchclick',
			function(e){
				if(moved) {
					moved = false;
					return false;
				}
			}
		);
	}
	
	
	
	var info_toggle = mainWrapper.find('.info_toggle');
	var infoHolder =mainWrapper.find('.infoHolder');
	var player_volume =mainWrapper.find('.player_volume');
	var big_play =mainWrapper.find('.big_play').css('cursor', 'pointer').bind('click', function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		togglePlayBack();
		return false;
	});
	var ap_bplay = mainWrapper.find('.ap_bplay');
	var info_inner =mainWrapper.find('.info_inner');
	var preloader =mainWrapper.find('.preloader');
	var playlistControls =mainWrapper.find('.playlistControls');
	
	var scrollPaneRedo, previewOrigW, previewOrigH;
	
	var currVideoData, advert_on, advert_done;
	
	var sizeSet, media_started = false;
	var _currentInsert;
	var lastTime;
	var captionArr = [], currCaptionArr = [], getCaptionsDone;
	var videoDataInterval=100, videoDataIntervalID;
	
	var thumbnailPreloaderUrl=source_path+settings.buttons_url.thumbnailPreloaderUrl;
	
	var deeplinkCounter;
	var _playlistLoaded = false;//callback boolean
	
	var end_insert,//insert at playlist end 
	insert_position, //insert position 
	addTrack_process,
	addTrack_playit,
	playlist_first_init;//first time create playlist with active item (call checkActiveItem after setup)
	
	var api_query;
	
	var _videoProcessDataUrl=[];
	
	var qualityDataArr=[], qualityMenuArr=[], curr_time, curr_quality, quality_change;
	
	var playlistInited;
	
	var media_id = settings.media_id;
	var mainPath='/main/';
	var wall_path=settings.wall_path;
	
	var auto_play=settings.auto_play;
	var yt_autoPlay= settings.auto_play;
	var initialAutoplay= settings.auto_play;
	if(isMobile){
		auto_play =false;
		yt_autoPlay = false;
	}
	var auto_advance_to_next_video=settings.auto_advance_to_next_video;
	var auto_hide_controls=isMobile ? false : settings.auto_hide_controls;
	var auto_open_description=settings.auto_open_description;
	var playlist_orientation =settings.playlist_orientation;
	var scroll_type = settings.scroll_type;
	var autoOpenPlaylist=settings.autoOpenPlaylist;
	var random_play=settings.random_play;
	var looping_on=settings.looping_on;
	var aspect_ratio=settings.aspect_ratio;
	var layout100perc=settings.layout100perc;
	var use_tooltips = isMobile ? false : settings.use_tooltips;
	var auto_reuse_mail_for_download = settings.auto_reuse_mail_for_download;
	if(playlistHidden){
		playlistHolder.css('display','none');
	} 
	if(!mp4Support){
		html5Support = false;//force flash
		mp4Support=true;
	}
	
	//wall
	var active_item, _activeItemID, _autoInitActiveItem=false;
	if(playlist_type=='wall' || playlist_type=='wall_popup'){//override defaults
		active_item=-1;
	}
	if(isMobile)aspect_ratio=1;//no elements above video!
	//isiPhoneIpod=true
	if(isiPhoneIpod){
		//remove elements above video since we cant use them
		infoHolder.remove();
		playerControls.remove();
		player_addon.remove();
		$(settings.dropdown_id).remove();
		$('#videoSearch').remove();
		mainWrapper.after(ap_adv_skip.css({position:'relative','float':'left',clear:'both',left:0, top:0}));//move it out of video area
	}
	
	//vars
	var ad_url_off=false;
	var playlistDataArr = [];
	var processPlaylistLength, processPlaylistCounter;
	var _activePlaylist, _activePlaylistID;
	var _playlistTransitionOn=false;
	var lightbox_use=false;
	var html5video_inited=false;
	var advertContainInfo;
	
	var previewVideo, previewVideoUp2Js;
	var lastPlaylist;
	var mediaPreviewType;
	
	var boxWidth, boxHeight, boxMarginBottom, boxMarginRight;
	
	var activePlaylistID;
	var activePlaylistThumb;
	var activePlaylistThumbImg;
	var activePlaylistPreloader;
	var activePlaylistVideoDiv;
	
	//fullscreen
	var fullscreenCount=0, fullscreenPossible = false, fs_removed = false;
	if(checkFullScreenSupport()){
		fullscreenPossible = true;
	}
	if(isIOS){
		 mainWrapper.find('.player_volume_wrapper').remove();
	}
			
	var toggleControl_width = mainWrapper.find('.player_toggleControl').outerWidth(true);
	var current_time_width = mainWrapper.find('.player_mediaTime_current').outerWidth(true);
	var total_time_width = mainWrapper.find('.player_mediaTime_total').outerWidth(true);
	var volume_width = mainWrapper.find('.player_volume_wrapper').outerWidth(true);
	var fullscreen_width = mainWrapper.find('.player_fullscreen').outerWidth(true);
	var player_captions_width = mainWrapper.find('.player_captions').outerWidth(true);	
	var player_quality_width = mainWrapper.find('.player_quality').outerWidth(true);	
	var player_download_width = mainWrapper.find('.player_download').outerWidth(true);	
			
	//youtube
	var youtubeTypeArr = ['youtube_playlist', 'youtube_user_favourites', 'youtube_user_uploads', 'youtube_video_query', 'youtube_related_video_query', 'youtube_playlist_query', 'youtube_user_playlists', 'youtube_channel_query', 'youtube_standard_feed'];
	var _youtubePlayer, _youtubePreviewPlayer, _videoProcessCounter=0,_videoProcessData=[],playlistStartCounter, playlistEnlargeCounter=50, youtubePlaylistPath, 
	_youtubeInited, _youtubePreviewInited, _youtubeChromeless=true;
	var youtubeEnlargeCounter=50, youtubeCounter, youtubeLimit, youtubeType, _youtubeStarted;

	var previewPoster;
	
	var captionsExist, captionTimeout=100, captionTimeoutID, active_caption_item, captions_menu, captionMenuSizeTaken;
	
	var curr_lang,
	off_lang = 'Off';
	
	var qualityExist, qualityTimeout=100, qualityTimeoutID, active_quality_item, quality_menu, qualityMenuSizeTaken;
	
	var videoInited;
	var mediaPath;	
	var previewMediaPath;	
	var ytPreviewPath;
	var mediaPlaying;
	var thumbWidth, thumbHeight;
	
	var scrollCheckInterval = 100;
	var scrollCheckIntervalID;
	var flashReadyInterval = 100;
	var flashReadyIntervalID;
	var flashCheckDone;
	
	var controls_timeout = settings.controls_timeout;
	var controlsTimeoutID;
	
	var dataInterval = 100;//tracking media data
	var dataIntervalID;
	
	var thumbArr = [];
	var thumbImgArr = [];
	var thumbHitDivArr = [];
	var thumbPreloaderArr = [];
	var _playlistLength=0;
	
	var useRolloversOnButtons=true;
	var mediaWidth;
	var mediaHeight;
	var mediaType;
	var componentSize = 'normal';
	var video;
	var videoUp2Js;
	var infoOpened;
	
	//scroll
	var info_scrollPaneApi;
	var scrollPane, scrollPaneApi;
	
	//buttons
	var thumbBackward, thumbForward;
	var _thumbScrollIntervalID;
	var _thumbsScrollValue=50;	
	var _thumbForwardSize;
	var _thumbBackwardSize;
	var _thumbInnerContainerSize, thumbInnerContainer;
	
	if(playlist_type == 'list'){
		if(scroll_type == 'buttons'){
			thumbInnerContainer = playlist_inner;	
			if(hasTouch){
				initTouch();
			}
		}
	}

	var _APYTLoader = new APYTLoader(settings);
	$(_APYTLoader).on('APYTLoader.END_LOAD', function(e, data){
		var i, len = data.length, obj;
		for(i=0;i<len;i++){
			obj = data[i];
			if(!apiCreation)obj.item = _videoProcessDataUrl[_videoProcessCounter].item.clone();
			_videoProcessData.push(obj);
		}
		_checkProcessCounter();
	});

	var buttonArr=[
		player_toggleControl,
		mainWrapper.find('.ap_share_btn'),
		info_toggle,
		mainWrapper.find('.player_volume'),
		mainWrapper.find('.player_fullscreen'),
		player_download,
		mainWrapper.find('.player_prev'),
		mainWrapper.find('.player_next'),
		mainWrapper.find('.thumbBackward'),
		mainWrapper.find('.thumbForward'),
		mainWrapper.find('.quality_btn'),
		mainWrapper.find('.caption_btn')
		
	];
	var btn,len = buttonArr.length,i=0;
	for(i;i<len;i++){
		btn = $(buttonArr[i]).css('cursor', 'pointer').bind('click', clickControls);
		if(!isMobile && useRolloversOnButtons){
			btn.bind('mouseenter', overControls).bind('mouseleave', outControls);
		}
	}
	
	//*************** start context menu
	var hasContextMenu;
	if(settings.context_menu_type){
		hasContextMenu=true;
		var context_menu_type = settings.context_menu_type,
		cm = '<div class="ap-context-menu">' + 
				'<ul>' + 
					'<li class="ap-context-play"><span>Play</span></li>' + 
					'<li class="ap-context-mute"><span>Mute</span></li>' + 
					'<li class="ap-context-full"><span>Fullscreen</span></li>'; 
			
		if(!isEmpty(settings.context_menu_text)){
			cm+='<li class="ap-context-copyright"><span>'+settings.context_menu_text+'</span></li>';
		}
		cm+='</ul></div>';//close
		contextMenu = $(cm);
		if(context_menu_type == 'custom')contextMenu.appendTo(_body);
		
		var contextTogglePlayback = $(".ap-context-play").css('cursor','pointer').on("click", togglePlayBack),
		contextToggleFs = $(".ap-context-full").css('cursor','pointer').on("click", toggleFullscreen),
		contextToggleVol = $(".ap-context-mute").css('cursor','pointer').on("click", function(){
			toggleVolume();
			setVolume();
		});
		if(!isEmpty(settings.context_menu_link))$(".ap-context-copyright").css('cursor','pointer').on("click", copyrightClick);
		if(!auto_play)contextTogglePlayback.find('span').html('Play');
		
		_body.on("mouseleave", hideContextMenu);
		_doc.bind("contextmenu", hideContextMenu).keyup(function(e){
		   if(e.keyCode == 27){ //esc
			  hideContextMenu();
		   }  
		});
		mainWrapper.bind("contextmenu", showContextMenu);
	}
		
	function copyrightClick(){
		if(settings.context_menu_target == "_blank"){	
			if(mediaPlaying)togglePlayBack();
			window.open(settings.context_menu_link);
		}else{//parent
			window.location = settings.context_menu_link;
		}
	}
	function showContextMenu(e) {
		if(context_menu_type == 'disabled'){
			return false;
		}else if(context_menu_type == 'custom'){
			if($(e.target).hasClass('embed_code'))return true;
			e.preventDefault();
			e.stopPropagation();
			var x = e.pageX, y = e.pageY;
			contextMenu.css({left: x+'px', top: y+'px', display:'block'});
			_body.one("click.apvideo", hideContextMenu);
		}
	}
	function hideContextMenu() {
		_body.unbind("click.apvideo", hideContextMenu);
		contextMenu.css('display','none');
	}
	//*************** end context menu
	
	//*********** seekbar
	
	var seekBarDown=false,seekBarElementsSize,playerControlsSize,seekBarSize, player_seekbar_offset=30;
	
	player_seekbar.css('cursor', 'pointer').bind(_downEvent,function(e){
		if(advert_on && disable_seekbar_in_advert)return false;
		_onDragStartSeek(e);
		return false;		
	}); 
	
	// Start dragging 
	function _onDragStartSeek(e) {
		if(!_componentInited || _playlistTransitionOn) return false;
		if(volumebarDown) return false;
		if(!seekBarDown){					
			var point;
			if(hasTouch){
				var currTouches = e.originalEvent.touches;
				if(currTouches && currTouches.length > 0) {
					point = currTouches[0];
				}else{	
					return false;						
				}
			}else{
				point = e;								
				e.preventDefault();						
			}
			seekBarDown = true;
			_doc.bind(_moveEvent, function(e) { _onDragMoveSeek(e); });
			_doc.bind(_upEvent, function(e) { _onDragReleaseSeek(e); });		
		}
		return false;	
	}
				
	function _onDragMoveSeek(e) {	
		var point;
		if(hasTouch){
			var touches;
			if(e.originalEvent.touches && e.originalEvent.touches.length) {
				touches = e.originalEvent.touches;
			}else if(e.originalEvent.changedTouches && e.originalEvent.changedTouches.length) {
				touches = e.originalEvent.changedTouches;
			}else{
				return false;
			}
			// If touches more then one, so stop sliding and allow browser do default action
			if(touches.length > 1) {
				return false;
			}
			point = touches[0];	
			e.preventDefault();				
		} else {
			point = e;
			e.preventDefault();		
		}
		setProgress(point.pageX);
		
		return false;		
	}
	
	function _onDragReleaseSeek(e) {
		if(seekBarDown){	
			seekBarDown = false;			
			_doc.unbind(_moveEvent).unbind(_upEvent);	
			
			var point;
			if(hasTouch){
				var touches;
				if(e.originalEvent.touches && e.originalEvent.touches.length) {
					touches = e.originalEvent.touches;
				}else if(e.originalEvent.changedTouches && e.originalEvent.changedTouches.length) {
					touches = e.originalEvent.changedTouches;
				}else{
					return false;
				}
				// If touches more then one, so stop sliding and allow browser do default action
				if(touches.length > 1) {
					return false;
				}
				point = touches[0];	
				e.preventDefault();				
			} else {
				point = e;
				e.preventDefault();		
			}
			setProgress(point.pageX);
		}
		return false;
	}	
	
	function setProgress(x) {
		var newPercent,ct,ct2f, seekPercent;
		
		seekPercent = x - progress_bg.offset().left;
		if(seekPercent<0) seekPercent=0;
		else if(seekPercent>seekBarSize) seekPercent=seekBarSize;
		newPercent = Math.max(0, Math.min(1, seekPercent / seekBarSize));
		//console.log('newPercent = ', newPercent);
		
		if(mediaType == 'local'){
			progress_level.css('width', seekPercent+'px');
			if(html5Support){
				if(videoInited){
					ct = newPercent * videoUp2Js.duration;
					ct2f = ct.toFixed(1);
					//console.log(videoUp2Js.seekable, videoUp2Js.seekable.length)
					try{
						videoUp2Js.currentTime = ct2f;
					}catch(er){}
				}
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_seek(newPercent); 
			}
		}else if(mediaType == 'youtube'){
			if(html5Support){
				if(_youtubePlayer){
					ct = newPercent * _youtubePlayer.getDuration();
					ct2f = ct.toFixed(1);
					_youtubePlayer.seek(ct2f);
				}
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_seek(newPercent);
			}
		}
	}
	
	function setProgress2(val) {
		if(mediaType == 'local'){
			if(html5Support){
				if(videoInited){
					try{
						videoUp2Js.currentTime = val;
					}catch(er){}
				}
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_seek(val); 
			}
		}else if(mediaType == 'youtube'){
			if(html5Support){
				if(_youtubePlayer){
					_youtubePlayer.seek(val);
				}
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_seek(val);
			}
		}
	}
	
	function getMediaTime(){
		var t = 0;
		if(html5Support){
			if(videoInited){
				try{
					t = parseInt(videoUp2Js.currentTime,10);
				}catch(er){}
			}
		}else{
			if(typeof getFlashMovie(flashMain) !== "undefined")t = getFlashMovie(flashMain).pb_getTime(); 
		}
		return t;	
	}
	
	if(!isMobile){
		var player_progress_tooltip = mainWrapper.find('.player_progress_tooltip').css({'left': parseInt(player_seekbar.css('left'), 10) + 'px', 'zIndex':302});
		player_seekbar.bind('mouseover', mouseOverHandlerSeek);
	}
	
	//************* seekbar tooltip
		
	function mouseOverHandlerSeek() {
		if(!videoInited) return false;
		if(advert_on && disable_seekbar_in_advert)return false;
		if(use_tooltips)player_progress_tooltip.css('display', 'block').tooltipster('show');
		player_seekbar.bind('mousemove', mouseMoveHandlerSeekTooltip).bind('mouseout', mouseOutHandlerSeek);
		_doc.bind('mouseout', mouseOutHandlerSeek);
	}
	
	function mouseOutHandlerSeek() {
		if(!videoInited) return false;
		if(use_tooltips)player_progress_tooltip.css('display', 'none').tooltipster('hide');
		player_seekbar.unbind('mousemove', mouseMoveHandlerSeekTooltip).unbind('mouseout', mouseOutHandlerSeek);
		_doc.unbind('mouseout', mouseOutHandlerSeek);
	}
	
	function mouseMoveHandlerSeekTooltip(e){
		var s = e.pageX - progress_bg.offset().left;
		if(s<0) s=0;
		else if(s>seekBarSize) s=seekBarSize;
		
		var center = parseInt(e.pageX - player_seekbar.offset().left - player_progress_tooltip.width() / 2,10);
		player_progress_tooltip.css('left', center + 'px');
		
		var newPercent = Math.max(0, Math.min(1, s / seekBarSize));
		var value, dur;
		
		if(mediaType == 'local'){
			if(html5Support){
				value=newPercent * videoUp2Js.duration, dur = videoUp2Js.duration;
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined"){
					dur = getFlashMovie(flashMain).pb_getFlashDuration();
					value=newPercent * dur;
				}else{
					return;	
				}
			}
		}else if(mediaType == 'youtube'){
			if(html5Support){
				value=newPercent *_youtubePlayer.getDuration();
				dur = _youtubePlayer.getDuration();
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined"){
					dur = getFlashMovie(flashMain).pb_getFlashDuration();
					value=newPercent * dur;
				}else{
					return;	
				}
			}
		}
		if(use_tooltips)player_progress_tooltip.tooltipster('content', formatCurrentTime(value)+' | '+formatDuration(dur));
	}
	
	//********* volume
	
	var volume_seekbar_autoHide = true;
	var default_volume =settings.default_volume;
	var _lastVolume;
	if(default_volume<0) default_volume=0;
	if(default_volume == 0) _lastVolume=0.5;//if we click unmute from mute on the beginning
	else if(default_volume>1)default_volume=1;
	if(default_volume!=0)_lastVolume = default_volume;
	var volumebarDown=false;
	var volumeSize=volume_bg.height();
	volume_level.css('height', default_volume*volumeSize+'px');
	
	var vol_seekbar_opened=false;//for mobile (we cant use rollover to open vol seekbar and click on vol toggle btn to toggle mute/unmute, so we use vol toggle btn just to open/close vol seekbar on mobile)
	var volumeTimeoutID, volumeTimeout = 3000;//hide volume seekbar
	
	function hideVolume(){
		if(volumeTimeoutID) clearTimeout(volumeTimeoutID);
		volume_seekbar.css('display','none');
		vol_seekbar_opened=false;
	}
	
	function toggleVolumeMobile(){
		if(volumeTimeoutID) clearTimeout(volumeTimeoutID);
		if(!vol_seekbar_opened){
			volume_seekbar.css('display','block');
			vol_seekbar_opened=true;
			//additional hide volume on timer 
			volumeTimeoutID = setTimeout(hideVolume,volumeTimeout);	
		}else{
			volume_seekbar.css('display','none');
			vol_seekbar_opened=false;
		}
	}
	
	if(!isMobile){
		player_volume.bind('mouseover', function(){
			if(!_componentInited) return false;
			//show volume seekbar
			if(volumeTimeoutID) clearTimeout(volumeTimeoutID);
			volume_seekbar.css('display','block');
			vol_seekbar_opened=true;
			return false;
		}).bind('mouseout', function(){
			if(!_componentInited) return false;
			if(volumeTimeoutID) clearTimeout(volumeTimeoutID);
			volumeTimeoutID = setTimeout(hideVolume,volumeTimeout);
			return false;
		});
	}else{
		player_volume.bind('click', function(){//close caption menu so it doesnt sit above volume
			if(!_componentInited) return false;
			if(volumeTimeoutID) clearTimeout(volumeTimeoutID);
			return false;
		});
	}
	
	if(default_volume == 0){
		mainWrapper.find('.player_volume').find('i').removeClass('fa-volume-up').addClass('fa-volume-off');
	}
	
	volume_seekbar.css('cursor', 'pointer').bind(_downEvent,function(e){
		_onDragStartVol(e);
		return false;		
	}); 

	// Start dragging 
	function _onDragStartVol(e) {
		if(!_componentInited || _playlistTransitionOn) return false;
		if(seekBarDown) return false;
		if(!volumebarDown){					
			var point;
			if(hasTouch){
				var currTouches = e.originalEvent.touches;
				if(currTouches && currTouches.length > 0) {
					point = currTouches[0];
				}else{	
					return false;						
				}
			}else{
				point = e;								
				e.preventDefault();						
			}
			volumebarDown = true;
			_doc.bind(_moveEvent, function(e) { _onDragMoveVol(e); }).bind(_upEvent, function(e) { _onDragReleaseVol(e); });		
		}
		return false;	
	}
				
	function _onDragMoveVol(e) {	
		var point;
		if(hasTouch){
			var touches;
			if(e.originalEvent.touches && e.originalEvent.touches.length) {
				touches = e.originalEvent.touches;
			}else if(e.originalEvent.changedTouches && e.originalEvent.changedTouches.length) {
				touches = e.originalEvent.changedTouches;
			}else{
				return false;
			}
			// If touches more then one, so stop sliding and allow browser do default action
			if(touches.length > 1) {
				return false;
			}
			point = touches[0];	
			e.preventDefault();				
		} else {
			point = e;
			e.preventDefault();		
		}
		volumeTo(point.pageY);
		
		return false;		
	}
	
	function _onDragReleaseVol(e) {
		if(volumebarDown){	
			volumebarDown = false;			
			_doc.unbind(_moveEvent).unbind(_upEvent);	
			
			var point;
			if(hasTouch){
				var touches;
				if(e.originalEvent.touches && e.originalEvent.touches.length) {
					touches = e.originalEvent.touches;
				}else if(e.originalEvent.changedTouches && e.originalEvent.changedTouches.length) {
					touches = e.originalEvent.changedTouches;
				}else{
					return false;
				}
				// If touches more then one, so stop sliding and allow browser do default action
				if(touches.length > 1) {
					return false;
				}
				point = touches[0];	
				e.preventDefault();				
			} else {
				point = e;
				e.preventDefault();		
			}
			
			volumeTo(point.pageY);
			
		}
		return false;
	}	
	
	function volumeTo(y) {
		default_volume = Math.max(0, Math.min(1, (y - volume_bg.offset().top) / volumeSize));
		default_volume = 1 - default_volume;//reverse
		setVolume();
	}
	
	function toggleVolume(){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(default_volume>0){
			_lastVolume = default_volume;//remember last volume
			default_volume = 0;//set mute on (volume to 0)
		}else{
			default_volume = _lastVolume;//restore last volume
		}
	}
	
	function setVolume(){
		//console.log('setVolume ', default_volume);
		volume_level.css('height', default_volume*volumeSize+'px');
		//if(!videoInited)return false;
		if(html5Support){
			if(mediaType == 'local'){
				if(videoUp2Js)videoUp2Js.volume = default_volume;
			}else{
				if(_youtubePlayer) _youtubePlayer.setVolume(default_volume);
			}
		}else{
			if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_setVolume(default_volume); 
		}
		
		if(default_volume == 0){
			mainWrapper.find('.player_volume').find('i').removeClass('fa-volume-up').addClass('fa-volume-off');
			if(hasContextMenu)contextToggleVol.find('span').html('UnMute');
		}else if(default_volume > 0){
			mainWrapper.find('.player_volume').find('i').removeClass('fa-volume-off').addClass('fa-volume-up');
			if(hasContextMenu)contextToggleVol.find('span').html('Mute');
		}
		
		if(isMobile && volume_seekbar_autoHide){//additional hide volume on timer after we use vol seekbar so vol toggle btn doesnt have to be used to close vol seekbar. This also reset volumeTimeoutID which is necessary, otherwise volume seekbar would close even while we touch on it constantly in less time than volumeTimeout
			if(volumeTimeoutID) clearTimeout(volumeTimeoutID);
			volumeTimeoutID = setTimeout(hideVolume,volumeTimeout);	
		}
	}
	
	
	if(!isMobile){
		volume_seekbar.bind('mouseover', mouseOverHandlerVolume);
		var player_volume_tooltip = componentWrapper.find('.player_volume_tooltip'), player_volume_tooltip_origT = parseInt(player_volume_tooltip.css('top'),10);
		
		//prevent mouse stuck over tooltip
		player_volume_tooltip.bind('mouseenter', function(){
			volume_seekbar.unbind('mouseover', mouseOverHandlerVolume);
			player_volume_tooltip.css('display', 'none');
		}).bind('mouseleave', function(){
			volume_seekbar.bind('mouseover', mouseOverHandlerVolume); 
		});	
	}
	
	//************* volume tooltip
	
	function mouseOverHandlerVolume() {
		if(volume_seekbar_autoHide) if(volumeTimeoutID) clearTimeout(volumeTimeoutID);
		if(use_tooltips)player_volume_tooltip.css('display', 'block').tooltipster('show');
		volume_seekbar.bind('mousemove', mouseMoveHandlerVolumeTooltip).bind('mouseout', mouseOutHandlerVolume);
		_doc.bind('mouseout', mouseOutHandlerVolume);
	}
	
	function mouseOutHandlerVolume() {
		if(volume_seekbar_autoHide){
			if(volumeTimeoutID) clearTimeout(volumeTimeoutID);
			volumeTimeoutID = setTimeout(hideVolume,volumeTimeout);
		}
		if(use_tooltips)player_volume_tooltip.css('display', 'none').tooltipster('hide');
		volume_seekbar.unbind('mousemove', mouseMoveHandlerVolumeTooltip).unbind('mouseout', mouseOutHandlerVolume);
		_doc.unbind('mouseout', mouseOutHandlerVolume);
	}
	
	function mouseMoveHandlerVolumeTooltip(e){
		var s = e.pageY - volume_bg.offset().top;
		if(s<0) s=0;
		else if(s>volumeSize) s=volumeSize;
		
		var center = parseInt(s - player_volume_tooltip.height()/2+player_volume_tooltip_origT,10);
		player_volume_tooltip.css('top', center + 'px');
		
		var newPercent = Math.max(0, Math.min(1, s / volumeSize));
		newPercent = 1 - newPercent;//reverse
		var value=parseInt(newPercent * 100, 10);
		if(use_tooltips)player_volume_tooltip.tooltipster('content', value+' %');
	}
	
	//*************** end volume

	function initTouch(){
		var startX,
			startY,
			touchStartX,
			touchStartY,
			moved,
			moving = false;

		thumbInnerContainer.unbind('touchstart.ap touchmove.ap touchend.ap click.ap-touchclick').bind(
			'touchstart.ap',
			function(e){
				if(!_componentInited || _playlistTransitionOn) return false;
				if(!touchOn){//if touch disabled we want click executed
					return true;
				}
				var touch = e.originalEvent.touches[0];
				startX = thumbInnerContainer.position().left;
				startY = thumbInnerContainer.position().top;
				touchStartX = touch.pageX;
				touchStartY = touch.pageY;
				moved = false;
				moving = true;
			}
		).bind(
			'touchmove.ap',
			function(ev){
				if(!moving){
					return;
				}
				var touchPos = ev.originalEvent.touches[0];
				if(playlist_orientation =='horizontal'){
					var value = startX - touchStartX + touchPos.pageX, w = componentPlaylist.width();
					
					//toggle advance buttons
					if(value > 0){
						value=0;	
						togglePrevBtn('off');
					}else{
						togglePrevBtn('on');
					}
					if(value < w- _thumbInnerContainerSize){
						value=w- _thumbInnerContainerSize;	
						toggleNextBtn('off');
					}else{
						toggleNextBtn('on');
					}
								
					thumbInnerContainer.css('left',value+'px');
				}else{
					var value=startY - touchStartY + touchPos.pageY, h = componentPlaylist.height();
					
					//toggle advance buttons
					if(value > 0){
						value=0;	
						togglePrevBtn('off');
					}else{
						togglePrevBtn('on');
					}
					if(value < h- _thumbInnerContainerSize){
						value=h- _thumbInnerContainerSize;	
						toggleNextBtn('off');
					}else{
						toggleNextBtn('on');
					}
					
					thumbInnerContainer.css('top',value+'px');
				}
				moved = moved || Math.abs(touchStartX - touchPos.pageX) > 5 || Math.abs(touchStartY - touchPos.pageY) > 5;
				
				return false;
			}
		).bind(
			'touchend.ap',
			function(e){
				moving = false;
			}
		).bind(
			'click.ap-touchclick',
			function(e){
				if(moved) {
					moved = false;
					return false;
				}
			}
		);
	}
		
	//*************** scroll button functions
		
	function _scrollThumbsBack() {
		var value;
		if(playlist_orientation == 'horizontal'){
			value = parseInt(thumbInnerContainer.css('left'),10);
			value+=_thumbsScrollValue;
			if(value > 0){
				if(_thumbScrollIntervalID) clearInterval(_thumbScrollIntervalID);
				value=0;	
				togglePrevBtn('off');
			}else{
				togglePrevBtn('on');
			}
			thumbInnerContainer.css('left', value+'px');
		}else{
			value = parseInt(thumbInnerContainer.css('top'),10);
			value+=_thumbsScrollValue;
			if(value > 0){
				if(_thumbScrollIntervalID) clearInterval(_thumbScrollIntervalID);
				value=0;	
				togglePrevBtn('off');
			}else{
				togglePrevBtn('on');
			}
			thumbInnerContainer.css('top', value+'px');
		}
		toggleNextBtn('on');
	}
	
	function _scrollThumbsForward() {
		var value;
		if(playlist_orientation == 'horizontal'){
			value = parseInt(thumbInnerContainer.css('left'),10), w = componentPlaylist.width();
			value-=_thumbsScrollValue;
			if(value < w- _thumbInnerContainerSize){
				if(_thumbScrollIntervalID) clearInterval(_thumbScrollIntervalID);
				value=w- _thumbInnerContainerSize;	
				toggleNextBtn('off');
			}else{
				toggleNextBtn('on');
			}
			thumbInnerContainer.css('left', value+'px');
		}else{
			value = parseInt(thumbInnerContainer.css('top'),10), h = componentPlaylist.height();
			value-=_thumbsScrollValue;
			if(value < h- _thumbInnerContainerSize){
				if(_thumbScrollIntervalID) clearInterval(_thumbScrollIntervalID);
				value=h- _thumbInnerContainerSize;	
				toggleNextBtn('off');
			}else{
				toggleNextBtn('on');
			}
			thumbInnerContainer.css('top', value+'px');
		}
		togglePrevBtn('on');
	}
	
	function togglePrevBtn(dir) {
		if(dir == 'on'){
			if(thumbBackward)thumbBackward.css('display','block');
		}else{
			if(thumbBackward)thumbBackward.css('display','none');
		}
	}
	
	function toggleNextBtn(dir) {
		if(dir == 'on'){
			if(thumbForward)thumbForward.css('display','block');
		}else{
			if(thumbForward)thumbForward.css('display','none');
		}
	}
	
	function _checkThumbPosition() {
		//console.log('_checkThumbPosition', _thumbInnerContainerSize);
		
		if(playlist_orientation == 'horizontal'){
			var w = componentPlaylist.width(), value;
			if(_thumbInnerContainerSize > w){
				togglePrevBtn('on');
				toggleNextBtn('on');
				touchOn=true;
				value = parseInt(thumbInnerContainer.css('left'),10);
				if(value < w- _thumbInnerContainerSize){
					if(_thumbScrollIntervalID) clearInterval(_thumbScrollIntervalID);
					value=w- _thumbInnerContainerSize;	
				}else if(value > 0){
					value=0;
				}
				thumbInnerContainer.css('left', value+'px');
				if(parseInt(thumbInnerContainer.css('left'),10) >= 0)togglePrevBtn('off');
			}else{
				togglePrevBtn('off');
				toggleNextBtn('off');
				touchOn=false;
				//thumbInnerContainer.css('left', w / 2 - _thumbInnerContainerSize / 2 +'px');//center thumbs if less
			}
		}else{
			var h = componentPlaylist.height(), value;
			if(_thumbInnerContainerSize > h){
				togglePrevBtn('on');
				toggleNextBtn('on');
				touchOn=true;
				value = parseInt(thumbInnerContainer.css('top'),10);
				if(value < h- _thumbInnerContainerSize){
					if(_thumbScrollIntervalID) clearInterval(_thumbScrollIntervalID);
					value=h- _thumbInnerContainerSize;	
				}else if(value > 0){
					value=0;
				}
				thumbInnerContainer.css('top', value+'px');
				if(parseInt(thumbInnerContainer.css('top'),10) >= 0)togglePrevBtn('off');
			}else{
				togglePrevBtn('off');
				toggleNextBtn('off');
				touchOn=false;
				//thumbInnerContainer.css('top', h / 2 - _thumbInnerContainerSize / 2 +'px');//center thumbs if less
			}
		}
	}
		
	//**********
	
	function checkScroll(){
		//console.log('checkScroll');
		
		if(scroll_type == 'scroll'){
		
			if(playlist_type == 'list'){
				if(!scrollPane){
					scrollPane = playlist_inner;
					scrollPane.bind('jsp-initialised',function(event, isScrollable){
						//console.log('Handle jsp-initialised', this,'isScrollable=', isScrollable);
					});
					if(playlist_orientation == 'horizontal'){
						scrollPane.jScrollPane({
							horizontalDragMinWidth: 100,
							horizontalDragMaxWidth: 100
						});
						if(!isMobile)playlist_inner.bind('mousewheel', horizontalMouseWheel);
					}else{
						scrollPane.jScrollPane({
							verticalDragMinHeight: 100,
							verticalDragMaxHeight: 100,
							mouseWheelSpeed: 30,
							contentWidth: '0px'//disable horizontal scrollbar
						});
					}
					scrollPaneApi = scrollPane.data('jsp');
				}else{
					if(playlistHolder.css('display')=='block'){
						scrollPaneApi.reinitialise();
						if(playlist_orientation == 'vertical'){
							scrollPaneApi.scrollToY(0);
							$('.jspPane').css('top',0+'px');
						}else{
							scrollPaneApi.scrollToX(0);
						    $('.jspPane').css('left',0+'px');
						}
					}else{
						scrollPaneRedo = true;
					}
				}
			}
		
		}else if(scroll_type == 'buttons'){
			
			thumbBackward = mainWrapper.find('.thumbBackward').css({cursor:'pointer', display:'none'})
			.bind(_downEvent, function(){
				if(!_componentInited || _playlistTransitionOn) return false;
				if(_thumbScrollIntervalID) clearInterval(_thumbScrollIntervalID);
				_thumbScrollIntervalID = setInterval(function() { _scrollThumbsBack(); }, 100);
				return false;
			}).bind(_upEvent, function(){
				if(_thumbScrollIntervalID) clearInterval(_thumbScrollIntervalID);
				return false;
			});
			
			thumbForward = mainWrapper.find('.thumbForward').css({cursor:'pointer', display:'none'})
			.bind(_downEvent, function(){
				if(!_componentInited || _playlistTransitionOn) return false;
				if(_thumbScrollIntervalID) clearInterval(_thumbScrollIntervalID);
				_thumbScrollIntervalID = setInterval(function() { _scrollThumbsForward(); }, 100);
				return false;
			}).bind(_upEvent, function(){
				if(_thumbScrollIntervalID) clearInterval(_thumbScrollIntervalID);
				return false;
			});
			
			_checkThumbPosition();
			
			if(!isMobile){
				playlist_inner.bind('mousewheel', function(event, delta, deltaX, deltaY){
					//console.log(event);
					if(!_componentInited || _playlistTransitionOn) return false;
					var d = delta > 0 ? 1 : -1, value, componentPlaylistSize;//normalize
					
					if(playlist_orientation == 'vertical'){
						componentPlaylistSize = componentPlaylist.height();
					}else{
						componentPlaylistSize = componentPlaylist.width();
					}
					
					var s = componentPlaylistSize;
					if(playlist_orientation =='horizontal'){
						if(_thumbInnerContainerSize < s) return false;//if centered
						value = parseInt(thumbInnerContainer.css('left'),10);
						value+=_thumbsScrollValue*d;
						if(value > 0){
							value=0;	
						}else if(value < s- _thumbInnerContainerSize){
							value=s- _thumbInnerContainerSize;	
						}
						thumbInnerContainer.css('left', value+'px');
					}else{
						if(_thumbInnerContainerSize < s) return false;
						value = parseInt(thumbInnerContainer.css('top'),10);
						value+=_thumbsScrollValue*d;
						if(value > 0){
							value=0;	
						}else if(value < s- _thumbInnerContainerSize){
							value=s- _thumbInnerContainerSize;	
						}
						thumbInnerContainer.css('top', value+'px');
					}
					//adjust buttons
					if(value == 0){
						togglePrevBtn('off');
						toggleNextBtn('on');
					}else if(value <= s- _thumbInnerContainerSize){
						toggleNextBtn('off');
						togglePrevBtn('on');
					}else{
						togglePrevBtn('on');
						toggleNextBtn('on');
						touchOn=true;
					}
					return false;
				});	
			}
		}
	}
	
	function horizontalMouseWheel(event, delta, deltaX, deltaY){//for thumb scroll
		if(!_componentInited || _playlistTransitionOn) return false;
		var d = delta > 0 ? -1 : 1;//normalize
		if(scrollPaneApi) scrollPaneApi.scrollByX(d * 100);
		return false;
	}
	
	//*************** api
		
	var noPlaylistCreation;	
	var apiCreation=false;
	var fromApiAddresses = [];	
		
	var ap_api_num_res = $('#ap_api_num_res');
	var ap_api_query_title = $('#ap_api_query_title');
	var ap_api_query_v = $('#ap_api_query_v').focusin(function(){
		ap_api_query_title.removeClass("ap_hap_error");	
	});
	var ap_api_limit_v = $('#ap_api_limit_v').on("keyup.ap", checkKey);
	var ap_api_youtube_sort_v = $('#ap_api_youtube_sort_v');
	var api_panel_wrap = $('#api_panel_wrap');
	var ap_api_append = $('#ap_api_append');
	var toggle_search = $('.toggle_search');
	var ap_api_youtube_feature_v = $('#ap_api_youtube_feature_v');
	var ap_api_path_title_v = $('#ap_api_path_title_v');
	
	var api_panel_opened;
	$('#api_panel_title_wrap').css('cursor', 'pointer').bind('click', function(e){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!api_panel_opened){
			api_panel_wrap.show(400);
			toggle_search.removeClass('toggle_search').addClass('toggle_search_close');
			api_panel_opened=true;
		}else{
			api_panel_wrap.hide(400);
			toggle_search.removeClass('toggle_search_close').addClass('toggle_search');
			api_panel_opened=false;
		}
		return false;
	});
	
	$('#ap_api_execute').find('span').css('cursor', 'pointer').bind('click', function(e){
		if(!_componentInited || _playlistTransitionOn) return false;
	
		if(isEmpty(ap_api_query_v.val())){//query required
			ap_api_query_title.addClass("ap_hap_error");
			alert("Please fill missing fields!");
			return false;
		}
		
		_videoProcessCounter=0;//reset
		_videoProcessData=[];
		_videoProcessDataUrl=[];
		
		var obj = {};
		obj.fromapi = true;
		obj.type = 'youtube_video_query';
		obj.path = obj.id = ap_api_path_title_v.val();
		obj.order = ap_api_youtube_sort_v.val();
		obj.limit = parseInt(ap_api_limit_v.val(),10);
		obj.query = api_query = ap_api_query_v.val();
		if(use_deeplink)obj.deeplink = ap_api_youtube_feature_v.val();
		_videoProcessDataUrl.push(obj);
	
		
		var append = ap_api_append.find('input:radio[name="ap_api_append"]:checked').val() == "yes" ? true : false;
		if(!append){
			destroyPlaylist();
			
			if(use_deeplink){
				activeCategory=null;
				secondLevelExist=false;
				secondLevel=null;
				
				var i = 0, len = categoryArr.length;
				for(i;i<len;i++){//remove last deeplink data created form api (if we get back with browser button last playlist created from api doesnt exist!)
					if(categoryArr[i] && categoryArr[i].fromapi)categoryArr.splice(i, 1);
				}
			}
		}
		
		_playlistLength=_videoProcessDataUrl.length;
		
		apiCreation= true;	
		
		preloader.css('display','block');	
		
		_processJson();
		
		return false;
	});
	
	

	
	//*************** end api
	
	//***************** playlist manager
	
	var pm_settings = {'random_play': random_play, 'looping_on': looping_on};
	var _playlistManager = $.playlistManager(pm_settings);
	$(_playlistManager).bind('ap_PlaylistManager.COUNTER_READY', function(){
		//console.log('COUNTER_READY');
		
		cleanMedia();
		_activeItemID = _playlistManager.getCounter();
		
		if(use_deeplink){
			if(!_addressSet){
				$.address.value(findAddress2(_playlistManager.getCounter()));
				if(!$.address.history()) $.address.history(true);//restore history
			}else{
				_addressSet=false;
				_disableActiveItem();
				_findMedia();
			}
		}else{
			_disableActiveItem();
			_findMedia();
		}
		//console.log(_activeItemID);
	}).bind('ap_PlaylistManager.PLAYLIST_END', function(){
		//console.log('PLAYLIST_END');
		if(typeof vplpPlaylistEnd !== 'undefined')vplpPlaylistEnd(_self, media_id);
	}).bind('ap_PlaylistManager.PLAYLIST_END_ALERT', function(){
		//console.log('ap_PlaylistManager.PLAYLIST_END_ALERT');
		if(typeof vplpPlaylistEnd !== 'undefined')vplpPlaylistEnd(_self, media_id);
	});
	
	//*************** deeplinking
	/*
	IMPORTANT!
	Since we cant see all the deplinks before each playlist has been processed (this would be valid only for single video links), first search for first level url on each playlist, then process this playlist and create deeplinking for this category (playlist).
	1. on each deeplink change check first level, find if already loaded, if not load, if yes, find second level from there. If not found - 404
	*/
	
	var categoryArr=[],cat;
	playlist_list.children("div[data-address]").each(function(){
		var obj = {};
		cat = $(this);
		obj.categoryName = cat.attr('data-address');
		obj.id = cat.attr('id');
		categoryArr.push(obj);
	});
	
	var deeplinkUniqueCounter=categoryArr.length+1;
	
	var use_deeplink=settings.use_deeplink;
	if(playlist_type == 'wall' || playlist_type == 'wall_popup')use_deeplink=false;
	if(use_deeplink){
		
		var strict = $.address.strict() ? '#/' : '#';
		var currentProcessDeeplink;
		var dlink;
		var secondLevelExist=false;
		var secondLevel;
		var firstLevel;
		var deepLink;
		var _addressSet=false;
		var _addressInited=false;
		var addressTimeout=500;
		var addressTimeoutID;
		var _externalChangeEvent;
		var startUrl=settings.active_playlist;
		if(!isEmpty(startUrl)){
			
			if(settings.active_item != -1){
			
				var first_playlist_item = $(playlist_list.find("div[id="+settings.active_playlist+"]")).children('div[class*=playlistNonSelected]').eq(0),
				active_item = first_playlist_item.attr('data-address'),
				type = first_playlist_item.attr('data-type');
				if(type != 'local' && type != 'youtube_single')	active_item+=parseInt(settings.active_item+1,10).toString();
				settings.active_item=active_item;
				startUrl += '/'+settings.active_item;//append second level

			}
		}
		//console.log(startUrl);
		var activeCategory;
		var currentCategory;
		var transitionFinishInterval=100;
		var transitionFinishIntervalID;
		var reCheckAddressTimeoutID;
		var reCheckAddressTimeout = 250;//address sometimes doesnt fire on beginning
	
		//console.log($.address.strict());
		//$.address.strict(false);
		//$.address.init(initAddress);
		$.address.internalChange(function(e){
			e.stopPropagation();
			//console.log("internalChange: ", e.value);
			if(reCheckAddressTimeoutID) clearTimeout(reCheckAddressTimeoutID);
			onChange(e);
		});
		$.address.externalChange(function(e){
			e.stopPropagation();
			//console.log("externalChange: ", e.value);
			if(reCheckAddressTimeoutID) clearTimeout(reCheckAddressTimeoutID);
			_externalChangeEvent = e;
			if(!_playlistTransitionOn){
				if(!_addressInited){
					//on the beginning onExternalChange fires first, then onInternalChange immediatelly, so skip onExternalChange call
	
					if(e.value == "/"){
						//console.log('strict mode off, skip /');
						
						_addressSet=true;
						$.address.history(false);//skip the "/"
						
						if(!isEmpty(startUrl)){
							$.address.value(startUrl);
							if(!$.address.history()) $.address.history(true);//restore history
						}else{
							//open menu
							//toggleMenuHandler(true);
						}
						
					}else if(isEmpty(e.value)){
						//console.log('strict mode on');
						_addressSet=true;
						$.address.history(false);//skip the ""
						
						if(!isEmpty(startUrl)){
							$.address.value(startUrl);
							if(!$.address.history()) $.address.history(true);//restore history
						}else{
							//open menu
							//toggleMenuHandler(true);
						}
					}else{
						//console.log('other deeplink start');
						onChange(e);
					}
					
					return;
				}
				if(addressTimeoutID) clearTimeout(addressTimeoutID);
				addressTimeoutID = setTimeout(swfAddressTimeoutHandler, addressTimeout);
			}else{
				if(addressTimeoutID) clearTimeout(addressTimeoutID);
				//wait for transition finish
				if(transitionFinishIntervalID) clearInterval(transitionFinishIntervalID);
				transitionFinishIntervalID = setInterval(transitionFinishHandler, transitionFinishInterval);
			}
		});
	}else{//no deeplink
		_activePlaylist=settings.active_playlist;
		if(!isEmpty(_activePlaylist))_setPlaylist();	
		else _endInit();
	}
	
	function transitionFinishHandler() {
		//console.log('transitionFinishHandler');
		if(!_playlistTransitionOn){//when module transition finishes
			if(transitionFinishIntervalID) clearInterval(transitionFinishIntervalID);
			if(addressTimeoutID) clearTimeout(addressTimeoutID);
			onChange(_externalChangeEvent);
		}
	}
	
	function reCheckAddress() {
		//console.log('reCheckAddress');
		if(reCheckAddressTimeoutID) clearTimeout(reCheckAddressTimeoutID);
		_addressSet=true;
		$.address.history(false);//skip the "/"
		
		if(!isEmpty(startUrl)){
			$.address.value(startUrl);
			if(!$.address.history()) $.address.history(true);//restore history
		}else{
			//open menu
			//toggleMenuHandler(true);
		}
	}
	
	function swfAddressTimeoutHandler() {
		//timeout if user repeatedly pressing back/forward browser buttons to stop default action executing immediatelly
		if(addressTimeoutID) clearTimeout(addressTimeoutID);
		onChange(_externalChangeEvent);
	}
	
	//fix for window.load instead of document.ready
	/*if(var reCheckAddressTimeoutID) clearTimeout(var reCheckAddressTimeoutID);
	var reCheckAddressTimeoutID = setTimeout(function(){
		if(reCheckAddressTimeoutID) clearTimeout(reCheckAddressTimeoutID);
		reCheckAddress();
	},var reCheckAddressTimeout);*/
	
	//************************** deeplinking	
			
	/*
	http://www.asual.com/jquery/address/docs/
				
	internalChange is called when we set value ourselves; 
	externalChange is called when the URL is changed or the browser backward or forward button is pressed. 
	
	I don't want to an AJAX request if there are no query parameters in the URL, which is why I test for an empty object.
	if($.isEmptyObject(event.parameters))
	return;
	  
	jQuery.address.strict(false);//Note that you need to disable plugin's strict option, otherwise it would add slash symbol immediately after hash symbol, like this: #/11.
	*/
	
	function filterAllowedChars(str) {
		var allowedChars = "_-";
		var n = str.length;
		var returnStr = "";
		var i = 0;
		var _char;
		var z;
		for (i; i < n; i++) {
			_char = str.charAt(i).toLowerCase(); //convert to lowercase
			if (_char == "\\") _char = "/";
			z = getCharCode(_char);			
			if ((z >= getCharCode("a") && z <= getCharCode("z")) || (z >= getCharCode("0") && z <= getCharCode("9")) || allowedChars.indexOf(_char) >= 0) {
				//only accepted characters (this will remove the spaces as well)
				returnStr += _char;
			}
		}
		return returnStr;
	}
	
	function getCharCode(s) {
		return s.charCodeAt(0);
	}
	
	function initAddress(e) {
		e.stopPropagation();
		//console.log("init: ", e.value);
	}
	
	function onChange(e) {
		e.stopPropagation();
		//console.log("onChange: ", e.value);
		
		if(!_addressInited){
			_addressInited = true;
		}
		
		if(e.value == "/"){//safari fix (copy from externalChange)!
			//console.log('strict mode off, skip /');
			_addressSet=true;
			$.address.history(false);//skip the "/"
			if(!isEmpty(startUrl)){
				$.address.value(startUrl);
				if(!$.address.history()) $.address.history(true);//restore history
				return;
			}
		}
		
		
		
		deepLink = e.value;
		if(deepLink.charAt(0) == "/") deepLink = deepLink.substring(1)//check if first character is slash
		if(deepLink.charAt(deepLink.length - 1) == "/") deepLink = deepLink.substring(0, deepLink.length - 1)//check if last character is slash
		//console.log("onChange after trim: ", deepLink);

		//check if first level exist
		var first_level = findFirstLevel(deepLink);
		if(!findCategoryByName(first_level)){
			//console.log(first_level, this.fromApiAddresses);
			if($.inArray(first_level, fromApiAddresses) != -1){
				//when we create playlist from api, then move to another playlist from api, when we get back with browser back button, we dont have that playlist available anymore so we just want to erase history
			}else{
				alert('404 page not found, check your deeplinks first level!');
			}
			$.address.history(false);//skip invalid url
			return false;
		}
		
		_addressSet=false;

		//check for category change
		if(currentCategory == undefined || currentCategory != activeCategory){
			//process new playlist and get deeplink data
			_setPlaylist();	
			return;
		}
		
		//console.log('//console.log(_playlistManager.getCounter(), active_item); = ', _playlistManager.getCounter(), ' , ', active_item);
		if(secondLevel){
			if(!findCounterByName(secondLevel)){//if second level exist but invalid
				alert('404 page not found, check your deeplinks second level!');
				$.address.history(false);//skip invalid url
				return;	
			}
		}else{//back from 2 level to one level
			destroyMedia();
			//console.log('here destroyMedia');
			return;
		}
		
		if(_playlistManager.getCounter() != active_item){
			//console.log('1a.......');
			_addressSet=true;
			if(_playlistManager.getCounter()!=-1)_enableActiveItem();
			_playlistManager.setCounter(active_item, false);
		}else{
			//console.log('2a.......');
			_disableActiveItem();
			_findMedia();
		}
	}
	
	function findAddress(value){
		//console.log('findAddress');
		
		var arr = value.split('/'), single_level = false;
		if(arr.length!=2){
			single_level = true;
			nameFound=true;
		}
		//console.log(arr);
		var category_name=arr[0],categoryFound=false,nameFound=false,i = 0, len = categoryArr.length;
		
		for(i; i < len;i++){
			if(categoryArr[i].categoryName == category_name){
				//console.log('activeCategory = ', i, ' , category_name = ', category_name);
				activeCategory = i;
				categoryFound=true;
				break;	
			}
		}
		if(!categoryFound) return false;
	
		if(single_level){
			media_name=arr[1];
			
			i = 0, arr = categoryArr[activeCategory].mediaName;
			var len = arr.length;
			for(i; i < len;i++){
				if(arr[i] == media_name){
					//console.log('active_item = ', i, ' , media_name = ', media_name);
					active_item = i;
					nameFound=true;
					break;	
				}
			}
		}
		
		if(!categoryFound || !nameFound){
			return false;
		}else{
			return true;	
		}
	}
	
	function findCounterByName(value){
		var found=false, i = 0, arr = categoryArr[activeCategory].mediaName, len = arr.length;
		for(i; i < len;i++){
			if(arr[i] == value){
				active_item = i;
				found=true;
				break;	
			}
		}
		if(!found){
			return false;
		}else{
			return true;	
		}
	}
	
	function findCounterByName2(value){//return counter from decription deeplink
		//console.log('findCounterByName2');
		var i = 0, arr = categoryArr[activeCategory].mediaName, len = arr.length, c = -1;
		for(i; i < len;i++){
			if(arr[i] == value){
				c = i;
				break;	
			}
		}
		return c;	
	}
	
	function findCategoryByName(value){
		var found=false, i = 0, len = categoryArr.length;
		for(i; i < len; i++){
			if(categoryArr[i].categoryName == value){
				//console.log('findCategoryByName: ', i, value);
				activeCategory = i;
				_activePlaylist= categoryArr[i].id;//get id attributte from current deepling
				found=true;
				break;	
			}
		}
		if(!found){
			return false;
		}else{
			return true;	
		}
	}
	
	function findAddress2(i){//return media name with requested counter
		//console.log('findAddress2');
		var arr = categoryArr[activeCategory].mediaName;
		return categoryArr[activeCategory].categoryName+'/'+arr[i];
	}
	 
	function findFirstLevel(value){
		var str_to_filter, result;
		if(value.indexOf('/') > 0){//two level
			secondLevelExist=true;
			str_to_filter = value.substr(0, value.indexOf('/'));
			firstLevel=str_to_filter;
			secondLevel = value.substr(value.indexOf('/')+1);//remember second part url
		}else{
			firstLevel=value;
			secondLevelExist=false;
			secondLevel=null;
			str_to_filter = value;//single level
		}
		//console.log('firstLevel = ', firstLevel);
		//console.log('secondLevel = ', secondLevel);
		result = filterAllowedChars(str_to_filter);
		return result;
	}
	
	//******************* PLAYLIST PROCESS
	
	function _endLoad(){
		apiCreation=false;	
		addTrack_process=false;
		preloader.css('display','none');
		_playlistTransitionOn=false;		
	}
	
	function _endInit(){
		//console.log('endInit');
		addTrack_process=false;
		preloader.css('display','none');	
		
		if(!_componentInited){
			_componentInited=true;
			_doneResizing();
			
			if(playlist_type=='list'){
				if(auto_hide_controls){
					playerHolder.bind('mouseenter', overComponent).bind('mouseleave', outComponent);
				}else{
					showControls();
				}
			}
			if(use_live_preview && !playlistHidden){
				playlistHolder.bind('mouseleave', outPlaylist);
				_body.on("mouseleave", cleanPreviewVideo);
			}
			
			if(typeof vplpSetupDone !== 'undefined'){
				setTimeout(function(){
					clearTimeout(this);
					vplpSetupDone(_self, media_id);
				},50);	
			}
			if(hap_drop){
				var t = hap_drop.find("option[value='"+_activePlaylist+"']").text();
				//console.log(t, _activePlaylist);
				if(!isMobile){
					//hap_drop.selectbox('change', _activePlaylist, t);
					$(settings.dropdown_id).find('.sbSelector').text(t);//manual change!
				}else{
					$(settings.dropdown_id).find('.lp_playlist option[value="' + _activePlaylist + '"]').prop('selected', true);
				}
			}
			
			playerLogo.removeClass('hidden').addClass('visible');
		}
		
		if(!_autoInitActiveItem){//only after destroy playist, not after add
			_autoInitActiveItem=true;
			if(playlist_type=='list' && !addTrack_process && _playlistLength>0){
				if(use_deeplink){
					//check second level
					if(secondLevelExist){
						if(!findCounterByName(secondLevel)){//if second level exist but invalid
							alert('404 page not found, check your deeplinks second level!');
							$.address.history(false);//skip invalid url
							return;	
						}
						_addressSet=true;
						_playlistManager.setCounter(active_item, false);
					}
				}else{
					var ai = parseInt(settings.active_item,10);
					if(ai > _playlistLength-1)ai = _playlistLength-1;
					if(ai>-1){
						_playlistManager.setCounter(ai, false);
					}
				}
				showControls2();
			}
		}
		
		//we need to reinit tooltipster on wall layout! (not necesarily on list)
		if(use_tooltips){
			mainWrapper.find('.tooltip').tooltipster({
				onlyOne:true,
				updateAnimation:false,
				offsetY:3,//rollover menu in ie<10 (same as below)
				speed:0//pointer events none?
			});
		}
	}
	
	//******************* PLAYLIST PROCESS
	
	function _checkProcessCounter(data) {
		 _videoProcessCounter++;
		 
		 //console.log(_videoProcessCounter, _playlistLength, noPlaylistCreation)
		 //console.log(_videoProcessDataUrl[_videoProcessCounter])

		 if(_videoProcessCounter>_playlistLength - 1){
			if(!noPlaylistCreation) {
				_buildPlaylist();
			}else{
				noPlaylistCreation = false;
				preloader.css('display','none');	
				_endInit();
			}
		 }else{
			_processJson(); 
		 }
	}
	function _processJson() {
		var type = _videoProcessDataUrl[_videoProcessCounter].type;
		//console.log('_processJson: ', type);
		if(RegExp('local').test(type)) {
			 _videoProcessData.push(_videoProcessDataUrl[_videoProcessCounter]); 
			 _checkProcessCounter();
		}else if(RegExp('folder').test(type)) {
			 _processFolder();
		}else if(RegExp('xml').test(type)) {
			 _processXml();
		}else if(RegExp('youtube').test(type)) {
			_APYTLoader.setData(_videoProcessDataUrl[_videoProcessCounter]);
		}else if(RegExp('vimeo').test(type)) {
			
		}	
	}
		
	function _setPlaylist(){
		//console.log('_setPlaylist');
		_playlistTransitionOn=true;
		preloader.css('display','block');
	
		if(!flashCheckDone){
			flashCheckDone=true;
			
			if(html5Support){
				if(playlist_type=='list')mediaHolder.css('display', 'block');
			}else{
				mediaHolder.remove();
				embedFlash();
				return;
			}
		}
	
		if(lastPlaylist)destroyPlaylist();
		_playlistLoaded=false;
		
		
		if(!sizeSet){
			sizeSet=true;
			//get playlist item size and thumb size
			var box = $("<div/>").addClass('playlistNonSelected').css('opacity',0).appendTo(playlistHolder);
			boxMarginBottom = parseInt(box.css('marginBottom'),10);
			boxMarginRight = parseInt(box.css('marginRight'),10);
			boxWidth = box.width();
			boxHeight = box.height();
			box.remove();
			box=null;
			
			var thumb = $("<div/>").addClass('playlistThumb').css('opacity',0).appendTo(playlistHolder);
			thumbWidth = thumb.width();
			thumbHeight = thumb.height();
			thumbLeft = parseInt(thumb.css('left'),10);
			thumbTop = parseInt(thumb.css('top'),10);
			thumb.remove();
			thumb=null;
			//console.log('boxWidth = ', boxWidth, ', boxHeight = ', boxHeight, ', thumbWidth = ', thumbWidth, ', thumbHeight = ', thumbHeight, 'boxMarginBottom = ', boxMarginBottom, 'boxMarginRight = ', boxMarginRight, ' , thumbLeft = ', thumbLeft , ' , thumbTop = ', thumbTop);
		}
		
		
		
		currentCategory = activeCategory;
	  
		_videoProcessCounter=0;//reset
		_videoProcessData=[];
		_videoProcessDataUrl=[];
		var obj, _item;
			
		
			
		//console.log(playlist_list, _activePlaylist);	
		var playlist = $(playlist_list.find("div[id="+_activePlaylist+"]"));
		
		if(playlist.length==0){
			alert('Failed playlist selection! No playlist with id: ' + _activePlaylist);
			_endLoad();
			return false;
		}
		lastPlaylist = playlist;
		_activePlaylistID = _activePlaylist;
		
		//console.log(playlist);
		playlist.children('div[class*=playlistNonSelected]').each(function() { 
			_item=$(this);
			obj = getTrackData(_item);
			_videoProcessDataUrl.push(obj);
		});
		
		_playlistLength=_videoProcessDataUrl.length;

		_playlistLength>0 ? _processJson() : _checkProcessCounter();
	}
	
	//************ process folder
	
	function _processFolder(){
		//console.log('_processFolder');
		var obj = _videoProcessDataUrl[_videoProcessCounter], _item = $(obj.item), path = _item.attr('data-path'), url = source_path + 'includes/folder_parser.php', subdirs = false;
		
		if(playlist_type=='wall' || playlist_type=='wall_popup'){
			path += wall_path;
		}else{
			path += mainPath;
		}
		path = path.replace(/\/\//g, "/");
		//console.log(url);
		
		var data = {"dir": path, "subdirs": subdirs};
		//console.log(data);
		
		if(use_deeplink){
			currentProcessDeeplink = _item.attr('data-address');
			deeplinkCounter = 0;	
		}
		
		$.ajax({
			type: 'GET',
			url: url,
			data: data,
			dataType: "json"
		}).done(function(media) {

			//console.log(media);

			//sort by path
			keysrt(media, 'path');

			var i = 0, len = media.length, entry, obj, path, no_ext, title, dirname, temp;
			//console.log(len);
			for(i; i < len; i++){
				entry = media[i];
				//console.log(entry);
				
				/*
				dirname = stripslashes(entry.dirname);
				dirname = dirname.substr(entry.root.length);
				temp = '/' + dirname + '/' + entry.basename;
				temp = temp.replace(/\/\//g, '/');
				path = window.location.protocol+"//"+window.location.host + temp;
				*/
				
				path = entry.path;
				
				obj={};
				obj.type = 'local';
				obj.origtype = 'folder';
				
				if(/.mp4/.test(path)){
					
					obj.mp4 = path;
					obj.preview = path.substr(0, path.lastIndexOf('.')) + '.jpg';//asssume preview file exist in the directory with the same name!
					
					//thumb and video preview come from preview dir
					obj.video_preview = obj.mp4.replace('/main/', '/preview/');
					obj.thumb = obj.preview.replace('/main/', '/preview/');
					
					no_ext = path.substr(0, path.lastIndexOf('.'));
					if(no_ext.indexOf('/')){
						title = no_ext.substr(no_ext.lastIndexOf('/')+1);
					}else{
						title = no_ext;
					}
					//remove underscores from title 
					obj.title = title.split("_").join(" ");
					
					obj.item = _videoProcessDataUrl[_videoProcessCounter].item.clone();
					
					if(use_deeplink){
						obj.deeplink = currentProcessDeeplink+(deeplinkCounter+1).toString();
						obj.item.attr({
							'data-address': obj.deeplink
						});
						deeplinkCounter++;	
					}
					
					obj.item.attr({
						'data-type': 'local',
						'data-mp4': obj.mp4,
						'data-preview': obj.preview,
						'data-video-preview': obj.video_preview,
						'data-thumb': obj.thumb
					});
					
					$('<div class="ap_title">'+obj.title+'</div>').appendTo(obj.item);
						
					_videoProcessData.push(obj); 
					
				}
			}
			//console.log(_videoProcessData)
			_checkProcessCounter();
	  
		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert('There was an error processing folder: ' + jqXHR.responseText);
			_endLoad();
		});	
	}
	
	//************ process xml
	
	function _processXml(){
		//console.log('_processXml');
		var obj = _videoProcessDataUrl[_videoProcessCounter];
		//console.log(obj);
		
		var j = 0, i = _videoProcessCounter;
		//console.log(_videoProcessDataUrl, _videoProcessDataUrl.length);
		//insert all items starting at the position xml was in _videoProcessDataUrl!
		_videoProcessDataUrl.splice(i, 1);//remove xml
		//console.log(_videoProcessDataUrl, _videoProcessDataUrl.length);
		
		$.ajax({
			type: 'GET',
			url: obj.path,
			dataType: "html",
			cache: false
		}).done(function(result) {

			var _item, obj;
			
			$(result).children('div[class*=playlistNonSelected]').each(function(){
				
				_item=$(this);
				obj = getTrackData(_item);
				
				_videoProcessDataUrl.splice(i, 0, obj);
			
				i++;
				j++;
				
			});
			//console.log(_videoProcessDataUrl, _videoProcessDataUrl.length);
			_playlistLength+=j-1;//increase by the number of xml items to process minus removed xml
			_videoProcessCounter-=1;//bring it back since we removed xml from _videoProcessDataUrl
			//console.log(_videoProcessCounter);
			//console.log(_videoProcessDataUrl[_videoProcessCounter])
			_checkProcessCounter();
	  
		}).fail(function(jqXHR, textStatus, errorThrown) {
			alert('There was an error processing xml: ' + jqXHR.responseText);
			_endLoad();
		});	
	}
	
	
	//************
	
	function _buildPlaylist() {
		//console.log('_buildPlaylist');
		
		var i = 0, j = !addTrack_process ? playlistDataArr.length : insert_position, len = _videoProcessData.length, type, div, data, append, thumb, thumbImg, playlistThumb, hitdiv, title, description, description_short, link, hook, weblink, target, deeplink, counter_add=0, fadeArr=[];
		
		//deeplink data
		if(use_deeplink){
			//when creating playlist from get/add api!
			if(!activeCategory && activeCategory !== 0){
				var obj = {};
				var uid = 'ap_playlist' + deeplinkUniqueCounter;
				fromApiAddresses.push(uid);
				deeplinkUniqueCounter++;
				obj.categoryName = obj.id = uid;
				obj.fromapi = true;
				categoryArr.push(obj);
				activeCategory = categoryArr.length-1;
				currentCategory = activeCategory;
				firstLevel = uid;
				$.address.value(uid);
			}

			if(!categoryArr[activeCategory].mediaName){//if not already processed
				var tempArr=[];
				categoryArr[activeCategory].mediaName=tempArr;
			}else{
				var tempArr = categoryArr[activeCategory].mediaName;
			}
			var strict = $.address.strict() ? '#/' : '#', dlink = baseURL + strict + firstLevel + '/';
				
		}

		for (i; i < len; i++) {
			
			var counter = i+j;
			if(addTrack_process)counter_add++;
			
			data = _videoProcessData[i];
			type = data.type;
			//console.log(data)
			
			div = $('<div/>').addClass('playlistNonSelected').attr({'data-id': counter, 'data-type': type});
			
			if(!playlistHidden){
				if(!addTrack_process){
					div.appendTo(playlist_content);
				}else{
					if(!_currentInsert){
						if(end_insert){
							div.appendTo(lastPlaylist);
						}else{
							lastPlaylist.children().eq(insert_position).before(div);
						}
					}else{
						_currentInsert.after(div);
					}
					_currentInsert=div;
				}
			}
			
			//deeplink
			deeplink = null;
			if(use_deeplink){
				var str_to_filter = filterAllowedChars(data.deeplink);
				tempArr.splice(counter, 0, str_to_filter);
				
				deeplink = use_deeplink ? dlink+data.deeplink : baseURL;
				data.deeplink = deeplink;//update
			}
			
			//thumb
			playlistThumb = $('<div/>').addClass('playlistThumb').appendTo(div);
			thumbArr.splice(counter, 0, playlistThumb);
		
			thumb = data.thumb ? data.thumb : null;
		
			if(thumb){
				thumbImg = $('<img class="thumb hidden" src="'+thumb+'" alt=""/>').appendTo(playlistThumb);
				thumbImgArr.splice(counter, 0, thumbImg);
			}else{
				thumbImgArr.splice(counter, 0, '');
			}
			fadeArr.push(thumbImg ? thumbImg : '');
			
			//title, desc
			title = data.title ? data.title : '';
			description_short = data.description_short ? data.description_short : '';
			description = data.description ? data.description : '';
			
			var desc = description_short;
			if(type != 'local'){
				if(isEmpty(desc))desc = description;
				if(desc.length>50)desc=desc.substr(0,50)+'...';//limit desciption
			} 
			
			if(playlist_type=='list')$('<div class="playlistInfo"><p><span class="playlistTitle">'+title+'</span><br><span class="playlistContent">'+desc+'</span></p></div>').appendTo(div);
			
			
			var c2copy = data.item ? data.item.attr('class').replace('playlistNonSelected', '') : '';//copy lightbox classes if exist

			hitdiv =$("<div/>").addClass('hitdiv').css({//hit div for rollover over whole playlist item
			   cursor: 'pointer'
			}).attr('dataTitle', 'hitdiv');
			
			if(playlist_type=='list'){
				
				hitdiv.appendTo(div);	
				thumbHitDivArr.splice(counter, 0, hitdiv);
				div.bind('click', clickPlaylistItem).attr('data-id', i + j);
				
			}else if(playlist_type=='wall'){
				
				link = data.lightbox ? data.lightbox.link ? data.lightbox.link : null : null;
				hook = data.lightbox ? data.lightbox.hook ? data.lightbox.hook : null : null;
				weblink = data.lightbox ? data.lightbox.weblink ? data.lightbox.weblink : null : null;
				target = data.lightbox ? data.lightbox.target ? data.lightbox.target : '_blank' : null;
				
				if(apiCreation && api_query){
					hook = 'fancybox-'+api_query.replace(/\s+/g, '');
					link = protocol+'//www.youtube.com/watch?v='+data.id;
				}
				
				if(data.origtype && data.origtype != 'youtube_single'){
					//if just hook exist make lightbox links to yt video id's, if just link exist make links (but all will have to point to the same link!)
					if(hook)link = protocol+'//www.youtube.com/watch?v='+data.id;//set it to something (not undefined) so it gets selected as hook/link selection
				}
				
				append = div;
					
				if(type == 'local'){
					
					createWallData('local', div, counter, title, desc, hook, link, target, weblink, thumb, append, hitdiv, c2copy);
					
				}else{//youtube
				
					createWallData('yt', div, counter, title, desc, hook, link, target, weblink, thumb, append, hitdiv, c2copy);
					
				}
					
			}else if(playlist_type=='wall_popup'){
					
				hitdiv.appendTo(div);	
				thumbHitDivArr.splice(counter, 0, hitdiv);
				div.bind('click', clickPlaylistItem).data('no_action', 'true').attr('data-id', counter);
				
				if(type != 'local' && apiCreation){
					//create playlist items in playlist list so we can copy playlist to popup
					var bd = $('<div class="playlistNonSelected" data-type="youtube_single" data-path="'+data.id+'" ></div>').appendTo(playlist_list.find('#' + settings.active_playlist));
					if(use_deeplink)bd.attr('data-address', data.deeplink);
				}
			}
				
			if(!isMobile){
				div.bind('mouseenter', overPlaylistItem).bind('mouseleave', outPlaylistItem).attr('data-id', counter);
			}
		
			if(use_live_preview){
				createVideoDiv(counter);
			}
			
			playlistDataArr.splice(counter, 0, {'id': counter, 'type': type, item: div, data: data});
			
			//get advert data
			if(data.item && data.item.find("div[class='ap_adv']").length){
				playlistDataArr[counter].advert = getAdvertData(data.item.find("div[class='ap_adv']").eq(0));
			}
				
		}
		
		//fade thumbs
		var p=0, z, len = fadeArr.length;
		for(z=0;z<len;z++){
			setTimeout(function(){
			   fadeArr[p].removeClass('hidden').addClass('visible');
			   p++;
			},50 + ( z * 50 ));
		}
		
		_currentInsert=null;
		lastPlaylist = playlist_content;
		
		//console.log('playlistDataArr = ', playlistDataArr);
		_playlistLength= playlistDataArr.length;
		//console.log('_playlistLength = ', _playlistLength);
		
		updateId();
		
		if(componentPlaylist.find('.playlistInfo').length)componentPlaylist.find('.playlistInfo').dotdotdot();
		if(componentPlaylist.find('.ap_wall_title').length)componentPlaylist.find('.ap_wall_title').dotdotdot();
		
		
		if(playlist_type=='list'){
		
			updateScrollData();
			checkScroll();
		
		}else if(playlist_type=='wall'){
		
			if(lightbox_use){
		
				$("a[data-fancybox-group^='fancybox-']").fancybox({
					openEffect : 'none',
					closeEffect : 'none',
					prevEffect : 'none',
					nextEffect : 'none',
					mouseWheel:false,
					arrows:false,
					helpers : {
						media : {},
						title: {
							type: 'inside'
						},
						thumbs	: {
							width	: 50,
							height	: 50
						}
					},
					beforeLoad: function () {
						if(use_live_preview)cleanPreviewVideo();
						if(typeof vplpItemTriggered !== 'undefined')vplpItemTriggered(_self, media_id, $(this.element).attr('data-id'));
					},
					afterLoad: function () {
						var title = this.title ? this.title : " ";
						var alt = $(this.element).find('img').attr('alt') ? $(this.element).find('img').attr('alt') : " ";
						this.title = '<h3>' + title + '</h3>' + alt + '<br />';
					}
				});
			}
		}
		
		if(!addTrack_process && !apiCreation){
			_playlistManager.setPlaylistItems(_playlistLength);
		}else{
			var current_counter = _playlistManager.getCounter();
			_playlistManager.setPlaylistItems(_playlistLength, false);
			//console.log(insert_position, current_counter, end_insert, counter_add);
			if(insert_position <= current_counter){
				if(!end_insert)	_playlistManager.reSetCounter(current_counter+counter_add);
			}
			//console.log(playlist_first_init, addTrack_playit, insert_position);
			
			if(playlist_type=='list'){
				if(playlist_first_init){
					playlist_first_init=false;	
					
					if(addTrack_playit && !isMobile){
						setAutoplay(true);
					}
					_playlistManager.setCounter(insert_position, false);
						
				}else if(addTrack_playit){
					//console.log('play: ', insert_position);
					_enableActiveItem();
					_playlistManager.setCounter(insert_position, false);
				}
			}
		}

		_endLoad();
		_endInit();
		
		if(_playlistLength == 0)if(typeof vplpPlaylistEmpty !== 'undefined')vplpPlaylistEmpty(_self, media_id);//callback
		setTimeout(function(){vplpPlaylistLoaded(_self, media_id, _playlistManager.getCounter());},50);	
		//if(typeof vplpPlaylistLoaded !== 'undefined')vplpPlaylistLoaded(_self, media_id);//callback
		_playlistLoaded= true;
		
	}
	
	function createWallData(type, _item, counter, title, description, hook, link, target, weblink, thumb, append, hitdiv, c2copy){
			
		if(hook && link){
			//console.log(i, 'lightbox');
		
			lightbox_use=true;
				
			a = $('<a class="fancybox hitdiv" href="'+link+'" data-fancybox-group="fancybox-'+hook+'" title="'+title+'" data-id="'+counter+'"/>').css({background: '#111', opacity:0}).appendTo(append);
			//ie 10,9 .css({background: '#111', opacity:0})
			img = $('<img src="'+thumb+'" alt="'+description+'"/>').css('display','none').appendTo(a);
			
			thumbHitDivArr.splice(counter, 0, '');
				
			if(!isEmpty(c2copy))a.addClass(c2copy);
			
		}else if(weblink){
			//console.log(i, 'weblink');
			
			a = $('<a class="hitdiv" href="'+weblink+'" target="'+target+'" data-id="'+counter+'"/>').appendTo(append);
			
			thumbHitDivArr.splice(counter, 0, '');
				
		}else{
			//console.log(i, 'no action');
		
			hitdiv.appendTo(append);	
			_item.bind('click', clickPlaylistItem).data('no_action', 'true').attr('data-id', counter);
			
			thumbHitDivArr.splice(counter, 0, hitdiv);
			
		}	
	}
	
	function createVideoDiv(counter){
		var videoDiv =$("<div/>").addClass('thumb_vid').attr({
		   dataTitle: 'videoDiv'
		}).prependTo(thumbArr[counter]);
	
		//thumb preloaders
		var thumbPreloader = $("<div/>").addClass('thumbPreloader').appendTo(thumbArr[counter]);
		thumbPreloaderArr.splice(counter, 0, thumbPreloader);
	}
	
	function updateScrollData(){
		
		if(_playlistLength > 0){
		
			//reset margins
			var i = 0, div;
			for(i;i<_playlistLength;i++){
				div = $(playlistDataArr[i].item);
				if(playlist_orientation == 'horizontal'){
					div.css('marginRight', boxMarginRight+'px');
				}else{
					div.css('marginBottom', boxMarginBottom+'px');
				}
			}
			
			//remove margin on last playlist item
			if(playlist_orientation == 'vertical'){
				playlistDataArr[_playlistLength-1].item.css('marginBottom', 0+'px');
			}else{
				playlistDataArr[_playlistLength-1].item.css('marginRight', 0+'px');
			}
			
			_thumbInnerContainerSize=0;
			var i = 0, div;
			for(i;i<_playlistLength;i++){
				div = $(playlistDataArr[i].item);
				if(playlist_orientation == 'horizontal'){
					_thumbInnerContainerSize+=div.outerWidth(true);
				}else{
					_thumbInnerContainerSize+=div.outerHeight(true);
				}
			}
			//console.log('_thumbInnerContainerSize= ', _thumbInnerContainerSize);
			
			if(scroll_type == 'buttons'){
				if(playlist_orientation == 'horizontal'){
					playlist_inner.width(_thumbInnerContainerSize);
				}
				_checkThumbPosition();
			}else if(scroll_type == 'scroll'){
				if(playlist_orientation == 'horizontal'){
					lastPlaylist.width(_thumbInnerContainerSize);
				}
			}
			
			if(scroll_type == 'buttons'){
				if(playlist_orientation == 'horizontal'){
					var w = componentPlaylist.width();
					if(_thumbInnerContainerSize <= w) thumbInnerContainer.css('left',0+'px');
				}else{
					var h = componentPlaylist.height();
					if(_thumbInnerContainerSize <= h) thumbInnerContainer.css('top',0+'px');
				}
			}
		
		}
	}

	function updateId(){
		var i = 0, div;
		_playlistLength = playlistDataArr.length;
		for(i;i<_playlistLength;i++){
			playlistDataArr[i].id = i;
			div = $(playlistDataArr[i].item).attr('data-id', i)
			.find("[data-id]").each(function(){
				$(this).attr('data-id', i);
			});
		}
	}
	
	function updateTrackData(id){
		
		if(use_live_preview)cleanPreviewVideo();
		
		playlistDataArr.splice(id,1);
		
		thumbHitDivArr.splice(id,1);
		thumbArr.splice(id,1);
		thumbImgArr.splice(id,1);
		
		if(use_live_preview){
			thumbPreloaderArr.splice(id,1);
		}
		playlist_content.children('.playlistNonSelected, .playlistSelected').eq(id).remove();
		
		if(use_deeplink)categoryArr[activeCategory].mediaName.splice(id,1);
		
		updateId();	
	}
	
	//**************** PREVIEW VIDEO
	
	function overPlaylistItem(e){
		if(!_componentInited || _playlistTransitionOn) return false;
		
		if (!e) var e = window.event;
		if(e.cancelBubble) e.cancelBubble = true;
		else if (e.stopPropagation) e.stopPropagation();
		
		var currentTarget = $(e.currentTarget), id = parseInt(currentTarget.attr('data-id'),10), _item = $(playlistDataArr[id].item), data = playlistDataArr[id].data;
		
		if(id == _playlistManager.getCounter()){
			if(typeof playlistItemRollover !== 'undefined')playlistItemRollover(_self, media_id, _item, id);//callback
			return false;//active item
		} 
		
		mediaPreviewType=currentTarget.attr('data-type');
		
		if(playlist_type=='list'){
			currentTarget.removeClass('playlistNonSelected').addClass('playlistSelected');
		}
		
		var has_preview = data.video_preview || data.id;
		if(use_live_preview && has_preview){
			
			activePlaylistID = id;
			activePlaylistThumb = $(thumbArr[id]);
			//hide thumb
			activePlaylistThumbImg = $(thumbImgArr[id]).removeClass('visible').addClass('hidden');//hide thumb
			//show preloader
			activePlaylistPreloader = $(thumbPreloaderArr[id]).css('display','block');
			//video div
			activePlaylistVideoDiv=activePlaylistThumb.find('div[class=thumb_vid]');
			
			if(html5Support){
				if(mediaPreviewType == 'local'){
					
					previewMediaPath = data.video_preview;
					
					var videoCode='';
					videoCode += '<video class="preview_video_cont" width="'+thumbWidth+'" height="'+thumbHeight+'" preload="auto" >';
					videoCode += '<source src="'+previewMediaPath+'" />';
					videoCode += '</video>';

					if(!isAndroid){//no type on android
						var append = 'type="video/mp4"';
						var m = videoCode.match(/\/\>/);//closing source tag
						videoCode = videoCode.slice(0, m.index) + append + videoCode.slice(m.index);
					}
					//console.log(videoCode);

					activePlaylistVideoDiv.html(videoCode);
					previewVideo = activePlaylistVideoDiv.find('.preview_video_cont');//get player reference
					previewVideoUp2Js = previewVideo[0];
					
					previewVideo.bind("ended", previewVideoEndHandler).bind("canplaythrough", previewCanplaythroughHandler)
					.bind("canplay", previewCanplayHandler);
				}else{//youtube
					ytPreviewPath = data.id;
				   
				   _youtubePreviewInited=false;
					youtubeIframePreview.appendTo(activePlaylistVideoDiv);
				   
					if(isIE){
						youtubeIframePreview.css({
							left:-out_left+'px',
							width:320+'px',
							height:240+'px'
						});
					}
					_initYoutubePreview();
				}
			}else{
				previewMediaPath = mediaPreviewType == 'local' ? data.video_preview : data.id; 
				
				//we loose external interface in ie if we reparent flash move, we need to embed new one each time
				var id = parseInt((Math.random()*0xFFFFFF),10);
				flashPreviewHolder.empty().appendTo(activePlaylistVideoDiv).append(getFlashCode(id));
				var data={mt:mediaPreviewType, mp:previewMediaPath,cw:thumbWidth, ch:thumbHeight, defaultVolume:default_volume, settings:settings};
				checkFlashPreviewReady(data);
			}
		}
		
		if(typeof playlistItemRollover !== 'undefined')playlistItemRollover(_self, media_id, _item, id);//callback
		
		return false;
	}
	
	function previewVideoEndHandler(){
		//console.log('previewVideoEndHandler');
		try{
			previewVideoUp2Js.currentTime=0;//rewind
		}catch(er){}
		previewVideoUp2Js.play();//chrome fix
	}
	
	function previewCanplaythroughHandler(){
		initPreviewVideo();
	}
	
	function previewCanplayHandler(){
		initPreviewVideo();
	}
	
	function initPreviewVideo(){
		//console.log('initPreviewVideo');
		if(previewVideo){
			previewVideo.unbind("canplaythrough", previewCanplaythroughHandler).unbind("canplay", previewCanplayHandler);
		}
		//hide preloader
		if(activePlaylistPreloader) activePlaylistPreloader.css('display','none');
		//play video
		if(previewVideoUp2Js) previewVideoUp2Js.play();
	}
	
	function cleanPreviewVideo(){
		//console.log('cleanPreviewVideo');
		if(scrollCheckIntervalID) clearInterval(scrollCheckIntervalID);
		
		if(html5Support){
			if(mediaPreviewType == 'local'){
				if(previewVideo){
					previewVideo.unbind("ended", previewVideoEndHandler).unbind("canplaythrough", previewCanplaythroughHandler).unbind("canplay", previewCanplayHandler).find('source').attr('src','');
				}
				//clean video code
				if(activePlaylistVideoDiv) activePlaylistVideoDiv.html('');
			}else{//youtube
			
				if(_youtubePreviewPlayer){
					 $(_youtubePreviewPlayer).off('ap_YoutubePlayer.START_PLAY');
					 _youtubePreviewPlayer.clean(true); 
					 _youtubePreviewPlayer=null;
				}
				if(isIE){
					youtubeIframePreview.css({
						left:-out_left+'px'
					});
				}
			}
		}else{
			if(embedFlashIntID)clearInterval(embedFlashIntID);
			flashPreviewHolder.empty();
		}
		//hide preloader
		if(activePlaylistPreloader) activePlaylistPreloader.css('display','none');
		//show thumb
		if(activePlaylistThumbImg) activePlaylistThumbImg.removeClass('hidden').addClass('visible');
	}
	
	function outPlaylistItem(e){
		
		if(!_componentInited || _playlistTransitionOn) return false;
		if (!e) var e = window.event;
		if(e.cancelBubble) e.cancelBubble = true;
		else if (e.stopPropagation) e.stopPropagation();
		
		var currentTarget = $(e.currentTarget), id = parseInt(currentTarget.attr('data-id'),10), _item = $(playlistDataArr[id].item);
		
		if(id == _playlistManager.getCounter()){
			if(typeof vplpPlaylistItemRollout !== 'undefined')vplpPlaylistItemRollout(_self, media_id, _item, id);//callback	
			return false;//active item
		} 
		
		if(use_live_preview)cleanPreviewVideo();
		
		if(playlist_type=='list'){
			if(currentTarget.hasClass('playlistSelected'))currentTarget.removeClass('playlistSelected').addClass('playlistNonSelected');
		}
		
		if(typeof vplpPlaylistItemRollout !== 'undefined')vplpPlaylistItemRollout(_self, media_id, _item, id);//callback	
		
		return false;
	}
	
	//**************** END PREVIEW VIDEO
	
	function clickPlaylistItem(e){
		if(!_componentInited || _playlistTransitionOn) return false;
		if (!e) var e = window.event;
		if(e.cancelBubble) e.cancelBubble = true;
		else if (e.stopPropagation) e.stopPropagation();
		var currentTarget = $(e.currentTarget);
		var id = currentTarget.attr('data-id');
		
		if(playlist_type=='list'){
		
			if(id == _playlistManager.getCounter()){//active item
				return false;
			} 
			
			if(use_live_preview) cleanPreviewVideo();
			
			_enableActiveItem();
			_playlistManager.processPlaylistRequest(id);
			
		}else if(playlist_type=='wall_popup' && !is_popup){
			if(typeof open_popup !== 'undefined'){
				settings.active_item = id;
				if(isIOS && isChrome && hasLocalStorage){//http://stackoverflow.com/questions/14637937/window-opener-not-set-in-ios-chrome
					localStorage.setItem('vplp_wall_popup_mltp_settings',JSON.stringify(settings));
					localStorage.setItem('vplp_wall_act_playlist',escape(playlist_list.wrap('<p>').parent().html()));
				}
				open_popup(settings, parseInt(id,10));
			}
		}
		
		return false;
	}
	
	function _enableActiveItem(){
		//console.log('_enableActiveItem');
		if(playlist_type=='wall' || playlist_type=='wall_popup')return false;
		if(_playlistManager.getCounter()!=-1){
			var i = _playlistManager.getCounter(),_item = $(playlistDataArr[i].item);
			if(_item){
				var id = _item.attr('data-id');
				_item.removeClass('playlistSelected').addClass('playlistNonSelected');
				if(typeof vplpPlaylistItemEnabled !== 'undefined')vplpPlaylistItemEnabled(_self, media_id, _item, id);//callback
			}
			if(playlist_type=='list'){
				var hitdiv = thumbHitDivArr[i];
				if(hitdiv) hitdiv.css('cursor', 'pointer');
			}
		}
	}
	
	function _disableActiveItem(){
		//console.log('_disableActiveItem');
		if(playlist_type=='wall' || playlist_type=='wall_popup')return false;
		var i = _playlistManager.getCounter(),_item = $(playlistDataArr[i].item);
		if(_item){
			var id = _item.attr('data-id');
			_item.removeClass('playlistNonSelected').addClass('playlistSelected');
			if(typeof vplpPlaylistItemDisabled !== 'undefined')vplpPlaylistItemDisabled(_self, media_id, _item, id);//callback
		}
		if(playlist_type=='list'){
			var hitdiv = thumbHitDivArr[i];
			if(hitdiv) hitdiv.css('cursor', 'default');
		}
	}
	
	function clickControls(e){
		if(!_componentInited || _playlistTransitionOn) return false;
		if (!e) var e = window.event;
		if(e.cancelBubble) e.cancelBubble = true;
		else if (e.stopPropagation) e.stopPropagation();
		
		var currentTarget = $(e.currentTarget),c=currentTarget.attr('class'), m = c.split(' ');
		
		if($.inArray('player_toggleControl', m) != -1){	
			ad_url_off = true;
			togglePlayBack();
		}else if($.inArray('player_volume', m) != -1){	
			if(!isMobile){
				toggleVolume();
				setVolume();
			}else{
				if(volume_seekbar_autoHide){
					toggleVolumeMobile();//if volume seekbar autohides, then on mobile we cant use player volume btn for mute/unmute volume, we need to use it to open volume seekbar on which we can then adjust volume (and set mute if necessary)
				}else{
					toggleVolume();
					setVolume();
				}	
			}
		}else if($.inArray('info_toggle', m) != -1){	
			toggleInfo();
		}else if($.inArray('ap_share_btn', m) != -1){	
			ap_share_holder.toggle();
			infoHolder.css('display','none');
			infoOpened=false;
		}else if($.inArray('player_fullscreen', m) != -1){	
			toggleFullscreen(true);
			if(use_tooltips)$(e.currentTarget).tooltipster('hide');
		}else if($.inArray('player_download', m) != -1){	
			globalDownload();
		}else if($.inArray('player_prev', m) != -1){	
			previousMedia();
		}else if($.inArray('player_next', m) != -1){	
			nextMedia();
		}
		return false;
	}
	
	function overControls(e){
		e.preventDefault();
		if(!_componentInited || _playlistTransitionOn) return false;
		var currentTarget = $(e.currentTarget), i=currentTarget.find('i').removeClass('icon_color').addClass('icon_rollover_color');
	}
	function outControls(e){
		e.preventDefault();
		if(!_componentInited || _playlistTransitionOn) return false;
		var currentTarget = $(e.currentTarget), i=currentTarget.find('i').removeClass('icon_rollover_color').addClass('icon_color');
	}
	
	//*******************
	
	function nextMedia(){
		if(!_componentInited) return;
		_enableActiveItem();
		_playlistManager.advanceHandler(1, true);
	}
	
	function previousMedia(){
		if(!_componentInited) return;
		_enableActiveItem();
		_playlistManager.advanceHandler(-1, true);
	}	
	
	function destroyMedia(){
		//console.log('destroyMedia');
		if(!_componentInited || !mediaType) return;
		_enableActiveItem();
		if(mediaType)cleanMedia();
		_playlistManager.reSetCounter();
	}
	
	function destroyPlaylist() {
		//console.log('destroyPlaylist');
		
		if(scrollCheckIntervalID) clearInterval(scrollCheckIntervalID);
		
		cleanMedia();
		mediaHolder.html('').css('display', 'none');
		if(use_live_preview)cleanPreviewVideo();
		playlist_content.empty();
		
		if(use_deeplink){
			categoryArr[activeCategory].mediaName = [];	
		}

		addTrack_process=false;
		_playlistLoaded=false;
		html5video_inited=false;
		lightbox_use=false;
		_playlistLength=0;
		processPlaylistCounter = -1;
		processPlaylistLength=0;
		_videoProcessData=[];
		playlistDataArr = [];
		_playlistManager.reSetCounter();
		_currentInsert=null;

		thumbArr=[];
		thumbImgArr=[];
		thumbPreloaderArr=[];
		thumbHitDivArr=[];
		
		infoOpened=false;
		_thumbInnerContainerSize=0;

		if(playlist_type=='list'){
			if(scroll_type == 'buttons'){
				if(playlist_orientation =='horizontal'){//reset scroll
					thumbInnerContainer.css('left', 0+'px');
				}else{
					thumbInnerContainer.css('top', 0+'px');
				}	
				//hide buttons
				if(thumbBackward)thumbBackward.css('display','none');
				if(thumbForward)thumbForward.css('display','none');
			}
		}

		_autoInitActiveItem=false;
	}
	function getQuality(){
		if(mediaType== 'local'){
			var i = 0, len = qualityDataArr.length, obj, path;
			for(i;i<len;i++){
				obj=qualityDataArr[i];
				if(curr_quality == obj.quality){
					path = obj.mp4;
					if(typeof vplpQualityChange !== 'undefined')vplpQualityChange(_self, media_id, curr_quality);//callback
					break;
				}
			}
		}else{
			path = currVideoData.id;
		}
		//console.log('getQuality: ', path,curr_quality);
		return path;
	}
	function _findMedia(qc){
		//console.log('_findMedia');
		
		quality_change = qc ? qc : null;//only on quality toggle btn
		quality_change ? cleanMediaQuality(): cleanMedia();
		
		if(!quality_change){
			
			if(!advert_done && playlistDataArr[_playlistManager.getCounter()].advert){
				currVideoData = playlistDataArr[_playlistManager.getCounter()].advert;
				advert_on=true;
			}else{
				currVideoData = playlistDataArr[_playlistManager.getCounter()].data;
				advert_on = false;
				advert_done = true;
			}

			mediaType = currVideoData.type;
			
			if(mediaType=='local')checkQuality('local');
			
			//captions
			if(html5Support && mediaType=='local' && currVideoData.subs){
				createCaptionMenu(currVideoData.subs);
				player_captions.css('display','block');
				captionsExist=true;
			}
			//advert
			if(advert_on && show_skip_button_in_advert){
				disable_seekbar_in_advert = settings.disable_seekbar_in_advert;
				skipTimeHappened=false;
				if(currVideoData.skip_time){
					skipEnableTime = currVideoData.skip_time;
					ap_adv_msg.show();
					ap_adv_img.empty().show().append($('<img class="adv_thumb" src="'+playlistDataArr[_playlistManager.getCounter()].data.thumb+'" alt=""/>'));
				}else{
					skipEnableTime=null;
					ap_adv_img.hide();
					ap_adv_msg.hide();
					ap_adv_msg_end.show();
					disable_seekbar_in_advert=false;
				}
			}
		}
		
		mediaPath = getQuality();
		//console.log(mediaType,mediaPath);
		
		if(mediaType == 'local'){
			if(auto_play){
				if(html5Support){
					initVideo();
				}else{
					if(typeof getFlashMovie(flashMain) !== "undefined"){
						getFlashMovie(flashMain).pb_play(mediaPath, aspect_ratio, componentWrapper.width(), componentWrapper.height(), 'local', true, quality_change ? curr_time : null);
						videoInited=true;
					}
				}
			}else{
				loadPreview();
				showControls();
			}
		}else if(mediaType == 'youtube'){
			if(html5Support){
				_initYoutube();
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined"){
					getFlashMovie(flashMain).pb_play(mediaPath, aspect_ratio, componentWrapper.width(), componentWrapper.height(), 'youtube', yt_autoPlay, null, currVideoData.quality ? currVideoData.quality : 'default');
					if(auto_play)videoInited=true;
				}
			}
		}
		if(playlist_type=='list'){
			if(typeof vplpItemTriggered !== 'undefined')vplpItemTriggered(_self, media_id, _playlistManager.getCounter());	
		}
	}
		
	function cleanMedia(){
		//console.log('cleanMedia');
		if(videoDataIntervalID)clearInterval(videoDataIntervalID);
		if(dataIntervalID) clearInterval(dataIntervalID);
		
		captionMenuSizeTaken = qualityMenuSizeTaken = null;
		ap_adv_skip.hide();	
		ap_adv_msg_end.hide();	
		hideControls();
		hideInfo();
		if(useShare)ap_share_holder.css('display','none');
		player_download.css('display','none');
		if(click_blocker)click_blocker.hide();
		
		if(mediaType && mediaType == 'local'){
			if(html5Support){
				if(videoUp2Js){
					videoUp2Js.pause();
					try{
						videoUp2Js.currentTime = 0;
					}catch(er){}
					videoUp2Js.src = '';
				}
				if(video)video.unbind("ended", videoEndHandler).unbind("loadedmetadata", videoMetadata).unbind("waiting",waitingHandler).unbind("playing", playingHandler).unbind("play", playHandler).unbind("pause", pauseHandler);
				
				mediaHolder.css('display', 'none');
				if(!isMobile & html5Support){
					mediaHolder.html('');
					html5video_inited=false;	
				}else{
					if(captionsExist && video)video.removeAttr('id').removeClass('captioned');
				}
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_dispose();
			}
		}else if(mediaType && mediaType == 'youtube'){
			if(html5Support){
				if(_youtubePlayer) _youtubePlayer.stop();
				youtubeIframeMain.css('left', -out_left+'px');
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_dispose();
			}
		}	
		
		toggleBigPlay('off');
		if(previewPoster){
			previewPoster.remove();
			previewPoster=null;
		} 
		mediaPreview.css('display', 'none');
		
		if(quality_menu)quality_menu.remove();
		player_quality.css('display','none');
		quality_holder.css('display','none');
		
		resetData();
		mediaPlaying=false;
		videoInited=false;//reset
		media_started = false;
		addTrack_playit=false;
		
		resizeControls();
		mediaWidth=mediaHeight=null;
		if(!advert_on)advert_done = false;
		
		if(captionator)captionator.destroy();
		if(captions_menu)captions_menu.remove();
		player_captions.css('display','none');
		caption_holder.css('display','none');
		captionsExist=false;
		
		ad_url_off=false;
	}
	
	function cleanMediaQuality(){//quality change
		//console.log('cleanMediaQuality');
		if(videoDataIntervalID)clearInterval(videoDataIntervalID);
		if(dataIntervalID) clearInterval(dataIntervalID);
		
		if(click_blocker)click_blocker.hide();
		
		if(mediaType && mediaType == 'local'){
			if(html5Support){
				if(videoUp2Js){
					videoUp2Js.pause();
					try{
						videoUp2Js.currentTime = 0;
					}catch(er){}
					videoUp2Js.src = '';
				}
				if(video)video.unbind("ended", videoEndHandler).unbind("loadedmetadata", videoMetadata).unbind("waiting",waitingHandler).unbind("playing", playingHandler).unbind("play", playHandler).unbind("pause", pauseHandler);
				mediaHolder.css('display', 'none');
				if(!isMobile & html5Support){
					mediaHolder.html('');
					html5video_inited=false;	
				}else{
					if(captionsExist && video)video.removeAttr('id').removeClass('captioned');
				}
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_dispose();
			}
		}else if(mediaType && mediaType == 'youtube'){
			if(html5Support){
				if(_youtubePlayer) _youtubePlayer.stop();
				youtubeIframeMain.css('left', -out_left+'px');
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_dispose();
			}
		}	
		
		resetData();
		mediaPlaying=false;
		videoInited=false;//reset
		media_started = false;
		addTrack_playit=false;
		ad_url_off=false;
		
		if(captionator)captionator.destroy();
		caption_holder.css('display','none');
	}
	
	function toggleBigPlay(dir) {
		if(dir=='off'){
			big_play.css('display', 'none'); 	
		}else{
			big_play.css('display', 'block'); 	
		}
	}
	
	//***************** YOUTUBE
	
	function _initYoutube() {
		//console.log('_initYoutube ', _youtubeInited);
		
		if(!_youtubeStarted){//ios fix, we need to start yt playback by clicking on yt player first time!!
			mediaHolder.css('display','none');
		}

		if(!_youtubeInited){
			youtubeIframeMain = $("<div class='youtubeIframeMain'/>").prependTo(playerHolder);
			click_blocker = $('<div class="click_blocker"></div>').on('click', function(e){
				e.preventDefault();
				if(!_componentInited || _playlistTransitionOn) return false;
				togglePlayBack();
			}).appendTo(youtubeIframeMain);
			var data={'auto_play': yt_autoPlay, 'default_volume': default_volume, 
			'mediaPath': mediaPath, 'youtubeHolder': youtubeIframeMain, 'youtubeChromeless': _youtubeChromeless, 
			'isMobile': isMobile, 'initialAutoplay': initialAutoplay, 'quality':currVideoData.quality, protocol:protocol};
			_youtubePlayer = $.youtubePlayer(data);
			$(_youtubePlayer).bind('ap_YoutubePlayer.YT_READY', function(){
				//console.log('ap_YoutubePlayer.YT_READY');
				resizeVideo();
			});
			$(_youtubePlayer).bind('ap_YoutubePlayer.START_PLAY', function(){
				//console.log('ap_YoutubePlayer.START_PLAY');
				_youtubeStarted=true;
				videoInited=true;
				if(!isMobile)setAutoplay(true);
				checkQuality('youtube');
				resizeVideo();
				if(!quality_change)checkInfo();
				showControls();
				if(dataIntervalID) clearInterval(dataIntervalID);
				dataIntervalID = setInterval(trackVideoData, dataInterval);	
				toggleBigPlay('on');
				if(componentSize== "fullscreen")click_blocker.show();
			});
			$(_youtubePlayer).bind('ap_YoutubePlayer.END_PLAY', function(){
				//console.log('ap_YoutubePlayer.END_PLAY');
				videoEndHandler();	
			});
			$(_youtubePlayer).bind('ap_YoutubePlayer.STATE_PLAYING', function(){
				//console.log('ap_YoutubePlayer.STATE_PLAYING');
				playHandler();
			});
			$(_youtubePlayer).bind('ap_YoutubePlayer.STATE_PAUSED', function(){
				//console.log('ap_YoutubePlayer.STATE_PAUSED');
				pauseHandler();
			});
			$(_youtubePlayer).bind('ap_YoutubePlayer.STATE_CUED', function(){//cue doesnt fire always
				//console.log('ap_YoutubePlayer.STATE_CUED');
				preloader.css('display','none');
			});
			$(_youtubePlayer).on('ap_YoutubePlayer.QUALITY_CHANGE', function(e, quality){
				//console.log('ap_YoutubePlayer.QUALITY_CHANGE', quality);
				findQualityMenuItem(quality);
				if(typeof vplpQualityChange !== 'undefined')vplpQualityChange(_self, media_id, quality);//callback
			});
			_youtubeInited=true;
		}else{
			resizeVideo();
			_youtubePlayer.initVideo(mediaPath, currVideoData.quality);
		}
		setTimeout(function(){preloader.css('display','none')},1000);
		showControls();
	}
	
	function _initYoutubePreview() {
		
		if(!_youtubePreviewInited){
			var data={'auto_play': true, 'default_volume': 0, 
			'mediaPath': ytPreviewPath, 'youtubeHolder': youtubeIframePreview, 'youtubeChromeless': true, 
			'isMobile': isMobile, 'initialAutoplay': initialAutoplay, 'quality':'small', 'small_embed':true, 'isIE': isIE, protocol:protocol};
			_youtubePreviewPlayer = $.youtubePlayer(data);
			$(_youtubePreviewPlayer).on('ap_YoutubePlayer.START_PLAY', function(){
				if(isIE){
					//for ie we need zindexfix if we need yt iframe z-index, in which case ie refuses to play video too small size
					youtubeIframePreview.css({
						left:0+'px',
						width:thumbWidth+'px',
						height:thumbHeight+'px'
					});
				}
				if(activePlaylistPreloader) activePlaylistPreloader.css('display','none');
				
			});
			$(_youtubePreviewPlayer).bind('ap_YoutubePlayer.END_PLAY', function(){
				_youtubePreviewPlayer.play();
			});
			_youtubePreviewInited=true;
		}else{
			_youtubePreviewPlayer.initVideo(ytPreviewPath);
		}
	}
	
	//***************** LOCAL VIDEO
	
	function loadPreview(){
		//console.log('loadPreview');
		
		mediaPreview.css('display', 'block');
		preloader.css('display','block');

		var path = currVideoData.preview, url = path+"?rand=" + (Math.random() * 99999999);
		
		previewPoster = $(new Image()).addClass('ap_media').css({
		   position: 'absolute',
		   display: 'block',
		   opacity: 0
		})
		.load(function() {
			preloader.css('display','none');
			previewOrigW=this.width;
			previewOrigH=this.height;
			mediaWidth=this.width;
			mediaHeight=this.height;
			//console.log(mediaWidth,mediaHeight );
			previewPoster.appendTo(mediaPreview);
			resizePreview(previewPoster);
			previewPoster.animate({'opacity': 1},  {duration: 500});
			toggleBigPlay('on');
		}).error(function(e) {
			//console.log("error " + e);
		}).attr('src', url);
	}
	
	function initVideo(){
		//console.log('initVideo');

		preloader.css('display','block');
		
		mediaPath+="?rand=" + (Math.random() * 99999999);//ios fix

		if(!html5video_inited){//we need one video source if we want to auto-advance on ios (with no click)
		
			var videoCode='';
			videoCode += '<video class="ap_media" width="'+mediaWidth+'" height="'+mediaHeight+'">';
			videoCode += '<source src="'+mediaPath+'" />';
			videoCode += '</video>';
			
			if(!isAndroid){//no type on android
				var append = 'type="video/mp4"';
				var m = videoCode.match(/\/\>/);//closing source tag
				videoCode = videoCode.slice(0, m.index) + append + videoCode.slice(m.index);
			}
			
			mediaHolder.css({opacity:0, display:'block'}).html(videoCode);
			
			video = mediaHolder.find('video[class=ap_media]');//get player reference
			videoUp2Js = video[0];
			//console.log(video, videoUp2Js);
			
		}else{
			
			mediaHolder.css({opacity:0, display:'block'});
			
			video = mediaHolder.find('video[class=ap_media]');//get player reference
			videoUp2Js = video[0];
			
			videoUp2Js.src = mediaPath;
			videoUp2Js.load();
			
		}
		

		videoUp2Js.volume = default_volume;
		video.css('position','absolute').bind("ended", videoEndHandler).bind("loadedmetadata", videoMetadata).bind("waiting",waitingHandler).bind("playing", playingHandler).bind("play", playHandler).bind("pause", pauseHandler);
			
		if(isIOS && !html5video_inited){
			videoUp2Js.src = mediaPath;
			videoUp2Js.load();
		}
		else if(isAndroid && !html5video_inited){
			videoUp2Js.play();
			
			toggleBigPlay('off');
			if(previewPoster){
				previewPoster.stop().animate({ 'opacity':0},  {duration: 500, complete:function(){
					previewPoster.remove();
					previewPoster=null;
				}});
			}
			videoInited=true;
			showControls();
		}
		
		html5video_inited=true;
		
		if(captionsExist){
			video.append(currVideoData.subs);
			
			mediaPreview.css('display', 'block');//show because captions are inside!
			var h = mediaHolder.height(), ch = playerControls.height();
			captionator.captionify('.ap_media',null,{
				controlHeight:ch+10+'px',
				appendCueCanvasTo:'.mediaPreview'
			},h);
			captionator.setRedraw(true);
			
			video.find('track').remove();
		}
	}
	
	function waitingHandler(e){//show preloader
		//console.log('waitingHandler');
		 preloader.css('display','block');
	}
	
	function playingHandler(e){//hide preloader
		//console.log('playingHandler');
		 preloader.css('display','none');
	}
	
	function playHandler(e){
		//console.log('playHandler');
		player_toggleControl.find('i').removeClass('fa-play').addClass('fa-pause');
		if(hasContextMenu)contextTogglePlayback.find('span').html('Pause');
		toggleBigPlay('off');
		preloader.css('display','none');
		if(!media_started){//fires only first time media is played
			if(isiPhoneIpod){//we cant use elements above video
				mainWrapper.find('.big_play').remove();
			}
			if(typeof vplpVideoStart !== 'undefined')vplpVideoStart(_self, media_id, _playlistManager.getCounter());//callback
			media_started=true;	
		}
		if(typeof vplpPlay !== 'undefined')vplpPlay(_self, media_id, _playlistManager.getCounter());//callback 
		mediaPlaying=true;
	}
	
	function pauseHandler(e){
		//console.log('pauseHandler');
		
		if(!ad_url_off){//not on toggle playback btn click
			if(advert_on && media_started && mediaPlaying){
				if(currVideoData.link != undefined){
					if(currVideoData.target != undefined && currVideoData.target == '_blank'){	
						window.open(currVideoData.link);
					}else{//parent
						window.location = currVideoData.link;
					}
				}
			}
		}
		ad_url_off=false;
		
		player_toggleControl.find('i').removeClass('fa-pause').addClass('fa-play');
		if(hasContextMenu)contextTogglePlayback.find('span').html('Play');
		toggleBigPlay('on');
		if(typeof vplpPause !== 'undefined')vplpPause(_self, media_id, _playlistManager.getCounter());//callback 
		mediaPlaying=false;
	}
	
	function videoMetadata(e){
		//console.log("videoMetadata: ", videoUp2Js.duration, videoUp2Js.videoWidth, videoUp2Js.videoHeight);
		if(videoUp2Js.videoWidth)mediaWidth=videoUp2Js.videoWidth;
		if(videoUp2Js.videoHeight)mediaHeight=videoUp2Js.videoHeight;
		resizeVideo();
		if(dataIntervalID) clearInterval(dataIntervalID);
		dataIntervalID = setInterval(trackVideoData, dataInterval);
		
		videoUp2Js.play();
		videoInited=true;
		
		mediaHolder.stop().animate({'opacity':1}, {duration: 300});
		
		if(quality_change && curr_time){
			try{
				videoUp2Js.currentTime=curr_time-2;//-2 to buffer better
			}catch(er){}
		}
		
		if(!quality_change)checkInfo();
		if(!isMobile)setAutoplay(true);
		else auto_play=true;
		showControls();
	}
	
	function setAutoplay(val){
		auto_play=yt_autoPlay=initialAutoplay=val;
		if(_youtubePlayer)_youtubePlayer.setAutoPlay(val);
		//if(!html5Support)if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_setAutoplay(val);
	}
	
	function togglePlayBack(){
		//console.log('togglePlayBack', _playlistManager.getCounter());
		if(_playlistManager.getCounter() == -1) return false;
		if(mediaType == 'local'){
			 if(!videoInited && !auto_play){
				preloader.css('display','block');	
				toggleBigPlay('off');
				if(previewPoster) {
					previewPoster.stop().animate({ 'opacity':0},  {duration: 500, complete:function(){
						previewPoster.remove();
						previewPoster=null;
					}});
				}
				if(html5Support){
					initVideo();
				}else{
					if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_play(mediaPath, aspect_ratio, componentWrapper.width(), componentWrapper.height(), 'local', true, quality_change ? curr_time : null);
				}
			 }else{
				if(html5Support){
				    if (videoUp2Js.paused) {
					    videoUp2Js.play();
				    } else {
					    videoUp2Js.pause();
				    }
				}else{
					if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_togglePlayback();
				}
			 }
		 }else if(mediaType == 'youtube'){
			  if(html5Support){
				  if(_youtubePlayer){
					  _youtubePlayer.togglePlayback();
				  } 
			  }else{
				  if(typeof getFlashMovie(flashMain) !== "undefined")getFlashMovie(flashMain).pb_togglePlayback();	
				  videoInited=true;
			  }
	   	 }
		 videoInited=true;
		 return false;
	}
	
	function trackVideoData(){
		var ct;
		if(mediaType == 'local'){
			if(html5Support){
				player_mediaTime_current.find('p').html(formatCurrentTime(videoUp2Js.currentTime));
				player_mediaTime_total.find('p').html(formatDuration(videoUp2Js.duration));
				if(!seekBarDown){
					progress_level.width((videoUp2Js.currentTime / videoUp2Js.duration) * seekBarSize);
					try{
						var buffered = Math.floor(videoUp2Js.buffered.end(0));
					}catch(error){}
					if(!isNaN(buffered)){
						var percent = buffered / Math.floor(videoUp2Js.duration);
						//console.log(percent, buffered);
						if(!isNaN(percent)){
							load_level.width(percent * seekBarSize);	
						}
					}
				}
				ct=parseInt(parseInt(videoUp2Js.currentTime),10);
			}
		}else if(mediaType == 'youtube'){
			if(html5Support){
				player_mediaTime_current.find('p').html(formatCurrentTime(_youtubePlayer.getCurrentTime()));
				player_mediaTime_total.find('p').html(formatDuration(_youtubePlayer.getDuration()));
				if(_youtubePlayer && !seekBarDown){
					progress_level.width((_youtubePlayer.getCurrentTime() / _youtubePlayer.getDuration()) * seekBarSize);
					percent = _youtubePlayer.getVideoBytesLoaded() / _youtubePlayer.getVideoBytesTotal();
					load_level.width(percent * seekBarSize);
					ct=parseInt(_youtubePlayer.getCurrentTime(),10);
				}
			}
		}	
		if(advert_on){
			if(show_skip_button_in_advert && skipEnableTime != null && !skipTimeHappened){
				//console.log(ct, skipEnableTime);
				if(ct < skipEnableTime){
					ap_adv_msg.find('p').html(skipTimeText+' '+(skipEnableTime-ct).toString());	
				}else{
					skipTimeHappened = true;	
					ap_adv_img.hide();
					ap_adv_msg.hide();
					ap_adv_msg_end.show();
					disable_seekbar_in_advert=false;
				}
			}	
		}
	};
	
	function videoEndHandler(){//only for html5 support
		//console.log('videoEndHandler');
		if(typeof vplpVideoEnd !== 'undefined')vplpVideoEnd(_self, media_id, _playlistManager.getCounter());//callback
		if(advert_on){
			advert_done=true;
			_findMedia();
		}else{
			if(auto_advance_to_next_video){
				nextMedia();
			}else{
				if(mediaType == 'local'){
					if(html5Support){
						try{
							videoUp2Js.currentTime=0;
						}catch(er){}
						if(videoUp2Js.paused)videoUp2Js.play();
						if(!auto_play){
							videoUp2Js.pause();
						}
					}
				}else if(mediaType == 'youtube'){
					if(html5Support){
						if(auto_play){
							_youtubePlayer.play();
						}else{
							toggleBigPlay('off');
						}
					}
				}
			}
		}
	}
	
	//********** captions
	function createCaptionMenu(data){
		var i, len = data.length, li, a, _item, default_exist;
	
		captions_menu = $("<ul class='captions_menu'></ul>").appendTo(caption_holder);
	
		for(i=0;i<len;i++){

			_item=$(data[i]);
			
			li = $('<li/>').appendTo(captions_menu);
			a = $('<a href="#"/>').text(_item.attr('label')).addClass('captionOut').attr('data-srclang',_item.attr('srclang')).on('click', captionToggle).appendTo(li);
			if(!isMobile)a.on('mouseover', overCaption).on('mouseout', outCaption);
			
			if (_item.attr('default')){
				default_exist=true;
				active_caption_item=a.removeClass('captionOut').addClass('captionOver').data('active', true).css('cursor', 'default');
			}
		};
		
		li = $('<li/>').appendTo(captions_menu);
		a = $('<a href="#"/>').text('None').addClass('captionOut').attr('data-srclang','none').on('click', captionToggle).appendTo(li);
		if(!default_exist)active_caption_item=a.removeClass('captionOut').addClass('captionOver').data('active', true).css('cursor', 'default');
		if(!isMobile)a.on('mouseover', overCaption).on('mouseout', outCaption);
		
		caption_holder.css('top', - caption_holder.height()-5+'px');
		
	}
	function captionToggle(e){
		e.preventDefault();
		
		var _item = $(this);
		if(_item.data('active') == true)return false;//active item
		if(active_caption_item){
			active_caption_item.removeClass('captionOver').addClass('captionOut').data('active', false).css('cursor', 'pointer');
		}
		active_caption_item = _item.removeClass('captionOut').addClass('captionOver').data('active', true).css('cursor', 'default');
		
		var caption_tracks = videoUp2Js.textTracksNew, lang = _item.attr('data-srclang'), i = 0, len = caption_tracks.length;

		if(lang == 'none'){
			for(i;i<len;i++){//hide all captions
				caption_tracks[i].mode = 0;
			}
		}else{
			for(i;i<len;i++){
				if(caption_tracks[i].language == lang){//disable active
					caption_tracks[i].mode = 2;
				}else{//hide others
					caption_tracks[i].mode = 0;
				}
			}
		}
		if(typeof vplpCaptionChange !== 'undefined')vplpCaptionChange(_self, media_id, lang);//callback
	}
	function overCaption(){
		$(this).removeClass('captionOut').addClass('captionOver');
		return false;
	}
	function outCaption(){
		if($(this).data('active') != true)$(this).removeClass('captionOver').addClass('captionOut');
		return false;
	}
	
	//********** quality 
	function checkQuality(type, cc, quality){
		
		if(type=='youtube'){
			var arr = _youtubePlayer.getQualityLevels();
			curr_quality = _youtubePlayer.getCurrQuality();
		}
		else if(type=='youtube_flash'){
			var arr = quality;
			curr_quality = cc;
		}else{
			var arr = [];
			
			qualityDataArr=[];
			qualityMenuArr=[];
			$.each($(currVideoData.item)[0].attributes, function (i, e) { 
				//console.log('name='+ i + ' value=' +e);
				if(/mp4/.test(e.name)){	
					if(e.name.indexOf('data-mp4') == 0){
						var menu_name = e.name.substr(9).toLowerCase();
						if(isEmpty(menu_name))menu_name='default';
						arr.push(menu_name);
						qualityDataArr.push({quality:menu_name, mp4:e.value});
					}
				}
			});
			if(cc)curr_quality =cc;
			else curr_quality = qualityDataArr[0].quality;//set first
		
		}

		if(arr.length==0)return;
		
		var i = 0, len = arr.length, _item, li, a;
		quality_menu = $("<ul class='quality_menu'></ul>").appendTo(quality_holder);
		
		for(i;i<len;i++){
			_item=arr[i];
			//console.log(_item);
			
			li = $('<li/>').appendTo(quality_menu);
			a = $('<a href="#"/>').text( _item).addClass('qualityOut').attr('data-quality',_item).bind('click', qualityToggle).appendTo(li);
			if(!isMobile)a.bind('mouseover', overQuality).bind('mouseout', outQuality);
			qualityMenuArr.push({quality: _item, item: a});
			
			//console.log(curr_quality, _item);
			//disable current quality
			if (curr_quality == _item){
				default_exist=true;
				active_quality_item=a.removeClass('qualityOut').addClass('qualityOver').data('active', true).css('cursor', 'default');
			}
		}
		player_quality.css('display','block');
		resizeControls();
		//console.log(qualityMenuArr);
	}
	function findQualityMenuItem(quality){
		if(quality_menu){
			quality_menu.find('a').each(function() {
				if($(this).attr('data-quality') == quality){
					if(active_quality_item)active_quality_item.removeClass('qualityOver').addClass('qualityOut').data('active', false).css('cursor', 'pointer');
					active_quality_item = $(this).removeClass('qualityOut').addClass('qualityOver').data('active', true).css('cursor', 'default');
				}
			});
		}
	}
	function setQualityMenuItem(_item){
		if(active_quality_item)active_quality_item.removeClass('qualityOver').addClass('qualityOut').data('active', false).css('cursor', 'pointer');
		if(_item)active_quality_item = _item.removeClass('qualityOut').addClass('qualityOver').data('active', true).css('cursor', 'default');
	}
	function qualityToggle(){
		var _item = $(this);
		if(_item.data('active') == true)return false;//active item
		setQualityMenuItem(_item);
		
		var quality = _item.attr('data-quality');
		if(mediaType == 'local'){
			var i = 0, len = qualityDataArr.length;
			for(i;i<len;i++){
				if(quality == qualityDataArr[i].quality){
					curr_quality = quality;
					break;
				}
			}
			curr_time=null;
			//save current time
			if(html5Support){
				if(videoUp2Js){
					try{
						curr_time = videoUp2Js.currentTime;
					}catch(er){}	
				}
			}else{
				if(typeof getFlashMovie(flashMain) !== "undefined")curr_time = getFlashMovie(flashMain).pb_getTime(); 
			}
			_findMedia(true);
			
		}else{
			_youtubePlayer.setPlaybackQuality(quality);
		}
		return false;
	}
	function overQuality(){
		$(this).removeClass('qualityOut').addClass('qualityOver');
		return false;
	}
	function outQuality(){
		if($(this).data('active') != true)$(this).removeClass('qualityOver').addClass('qualityOut');
		return false;
	}
	
	//********** download
	
	function globalDownload(){
		if(_downloadOn)return false;
		var c = _playlistManager.getCounter(), path = playlistDataArr[c].data.download, name = playlistDataArr[c].data.title,
		 dwn = getDownloadPath(mediaType, name, path);
		//console.log(dwn.name, dwn.path);
		//return;
		checkDownload(dwn.name, dwn.path);	
	}
	
	function getDownloadPath(type, name, path){
		if(path.indexOf('\\')){//replace backward slashes
			path = path.replace(/\\/g,"/");
		}
		//construct full download path 
		if(!path.match(/^http([s]?):\/\/.*/)){
			//console.log(window.location);
			var main_path = window.location.href;
			main_path = main_path.substr(0, main_path.lastIndexOf('/')+1);
			if((/^\.\.\//i).test(path)){//replace ../ for one level up since we have media files one level up from root
				path = path.substr(3);//remove ../
				if(main_path.charAt(main_path.length-1)=='/')main_path = main_path.substr(0,main_path.lastIndexOf('/'));//remove last slash
				main_path = main_path.substr(0,main_path.lastIndexOf('/')+1);//remove current directory
			}	
			path = main_path + path;
		}
		name = name.replace(/[^A-Z0-9\-\_\.]+/ig, "_");//remove spaces and spec chars
		if(name.length > 40) name = name.substr(0, 40) + "...";
		if(path.lastIndexOf('.') > 0){
			var ext = path.substr(path.lastIndexOf('.'));
			//console.log(ext, new RegExp('/' + ext + '$/i'));
			if(!(new RegExp('/' + ext + '$/i')).test(name)){
				name+=ext;//append extension
			}
		}
		return {name:name, path:path};
	}
	
	function checkDownload(name, path){
		if(!isMobile){
			dl_iframe.attr('src',source_path+"includes/dl.php?path="+path+"&name="+name);
		}else{//send mail on ios because it doesnt allow file download
			_downloadOn = true;
			if(auto_reuse_mail_for_download){
				if(!mailSet){
					dl_mail = getMail();
					if(dl_mail)mailSet = true;
				}
				if(dl_mail){
					sendMail(dl_mail, name, path);
				}else{
					_downloadOn = false;	
				}
			}else{
				var email = getMail();
				if(email){
					sendMail(email, name, path);
				}else{
					_downloadOn = false;	
				}
			}
		}
	}
	
	function sendMail(mail, name, path){//send mail on ios
		//console.log('sendMail');
		var data = "mail=" + mail + "&name=" + name + "&path=" + path;
		//console.log(data);
		$.ajax({
			type: "POST",
			url: source_path+"includes/mail.php",
			data: data
		}).done(function(msg) {
			//console.log('Send mail success: ' + msg);
			_downloadOn = false;	
		}).fail(function(jqXHR, textStatus, errorThrown) {
			//console.log('Send email error: ' + jqXHR.responseText);
			alert('Send email error: ' + jqXHR.responseText);
			hideDownConf();
			_downloadOn = false;
		});	
		download_confirm.css({marginTop:-download_confirm.height()/2+'px',display:'block'}).stop().animate({'opacity': 1},{duration: 500});
		if(downConf_timeoutID) clearTimeout(downConf_timeoutID);
		downConf_timeoutID = setTimeout(hideDownConf, downConf_timeout);
	}
	
	function hideDownConf(){
		if(downConf_timeoutID) clearTimeout(downConf_timeoutID);
		download_confirm.stop().animate({'opacity': 0},  {duration: 500, complete: function(){
			download_confirm.css('display','none');	
		}});
	}
	
	function getMail(){
		var email = prompt("Please enter your email address where download link will be sent:");
		//validate email
		var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
		while(!emailReg.test(email) || isEmpty(email)){
			if(email == null){
				break;
			}
			email = prompt("Please enter a valid email address:");
		}
		return email;
	}
	
	//********** flash
	
	this.flashPreviewVideoStart = function(){
		if(activePlaylistPreloader) activePlaylistPreloader.css('display','none');
	}
	this.flashVideoPause = function(){
		pauseHandler();
	}
	this.flashVideoResume = function(){
		playHandler();
	}
	this.flashVideoEnd = function(){
		videoEndHandler();	
	}
	this.flashVideoStart = function(){
		videoInited=true;
		playHandler();
		showControls();
		if(!quality_change)checkInfo();
	}
	this.dataUpdateFlash = function(bl,bt,t,d){
		load_level.width((bl/bt) * seekBarSize);	
		progress_level.width((t/d) * seekBarSize);
		player_mediaTime_current.html(formatCurrentTime(t));
		player_mediaTime_total.html(formatDuration(d));
		
		if(advert_on){
			if(show_skip_button_in_advert && skipEnableTime != null && !skipTimeHappened){
				if(parseInt(t,10) < skipEnableTime){
					ap_adv_msg.find('p').html(skipTimeText+' '+(skipEnableTime-parseInt(t,10)).toString());	
				}else{
					skipTimeHappened = true;	
					ap_adv_img.hide();
					ap_adv_msg.hide();
					ap_adv_msg_end.show();
					disable_seekbar_in_advert=false;
				}
			}	
		}
	}
	this.checkQuality = function(type, cc, quality){
		checkQuality(type, cc, quality);	
	}
	this.flashYtQualityChange = function(cc){
		findQualityMenuItem(cc);
		if(typeof tvpQualityChange !== 'undefined')tvpQualityChange(_self, media_id, cc);//callback
	}
	function embedFlashPreview(){
		
		var id = parseInt((Math.random()*0xFFFFFF),10);	
		flashPreviewHolder = $('<div/>').addClass('flashPreview').append(getFlashCode(id));
		
		flashPreviewHolder.prependTo(playlist_content);
		
		if(embedFlashIntID)clearInterval(embedFlashIntID);
		embedFlashIntID = setInterval(function(){
			if(typeof getFlashMovie(flashPreview) !== "undefined" && getFlashMovie(flashPreview).setData != undefined){
				clearInterval(embedFlashIntID);
				if(!settings.flash_id)settings.flash_id = vplp_mediaArr.length-1;//if wall / no main flash 
				getFlashMovie(flashPreview).setData(settings);
				if(!isEmpty(_activePlaylist))_setPlaylist();	
				else _endInit();	
			}
		},embedFlashInt);
	}
	function getFlashCode(id){
		var flash_path = source_path+'fallback2.swf', f_id = 'flashPreview' + id;
		flashPreview = '#'+f_id;
		return $('<object id="'+f_id+'" type="application/x-shockwave-flash" data="' + flash_path + '" width="100%" height="100%">' + 
					'<param name="movie" value="' + flash_path + '" />' + 
					'<param name="allowScriptAccess" value="always" />' + 
					'<param name="bgcolor" value="#000000" />' + 
					'<param name="allowfullscreen" value="true" />' +  
					'<param name="wmode" value="transparent" />' + 
					'<a href="'+protocol+'//get.adobe.com/flashplayer/" target="_blank"><img src="'+protocol+'//www.adobe.com/images/shared/download_buttons/get_flash_player.gif" /></a>' +
				'</object>');	
	}
	function checkFlashPreviewReady(data) {
		if(embedFlashIntID)clearInterval(embedFlashIntID);
		embedFlashIntID = setInterval(function(){
			if(typeof getFlashMovie(flashPreview) !== "undefined" && getFlashMovie(flashPreview).pb_play != undefined){
				clearInterval(embedFlashIntID);
				getFlashMovie(flashPreview).pb_play(data);
			}
		},embedFlashInt);
	}
	function checkFlashReady(){
		if(getFlashMovie(flashMain).setData != undefined){
			if(flashReadyIntervalID) clearInterval(flashReadyIntervalID);
			getFlashMovie(flashMain).setData(settings);
			if(use_live_preview){
				embedFlashPreview();
			}else{
				if(!isEmpty(_activePlaylist))_setPlaylist();	
				else _endInit();
			}
		}
	}
	function getFlashMovie(name) {
		if(name.charAt(0)=='#')name = name.substr(1);//remove'#'
		if(isSafari)return window[name];
		else return (navigator.appName.indexOf("Microsoft") != -1) ? window[name] : document[name];
	}	
	function embedFlash(){
		if(playlist_type=='list'){
			
			var id = vplp_mediaArr.length-1;
			
			var f_id = 'flashMain' + id;
			flashMain = '#'+f_id;
			settings.flash_id = id;
			
			var flash_path = source_path+'fallback.swf', 
			flash = $('<object id="'+f_id+'" type="application/x-shockwave-flash" data="' + flash_path + '" width="100%" height="100%">' + 
						'<param name="movie" value="' + flash_path + '" />' + 
						'<param name="allowScriptAccess" value="always" />' + 
						'<param name="bgcolor" value="#000000" />' + 
						'<param name="allowfullscreen" value="true" />' +  
						'<param name="wmode" value="transparent" />' + 
						'<a href="'+protocol+'//get.adobe.com/flashplayer/" target="_blank"><img src="'+protocol+'//www.adobe.com/images/shared/download_buttons/get_flash_player.gif" /></a>' +
					'</object>');
			
			$('<div/>').addClass('flashMain').append(flash).prependTo(playerHolder);
			
			var interval = setInterval(function(){
				if(typeof getFlashMovie(flashMain) !== "undefined"){
					clearInterval(interval);
					flashReadyIntervalID = setInterval(checkFlashReady, flashReadyInterval);	
				}
			},50);
			
		}else{
			if(use_live_preview){
				embedFlashPreview();
			}else{
				if(!isEmpty(_activePlaylist))_setPlaylist();	
				else _endInit();
			}
		}
	}	
	//***************** description
	
	function checkInfo(){
		//console.log('checkInfo');
		advertContainInfo=false;
		
		if(currVideoData.description != undefined){
			advertContainInfo=true;
			var infoData = currVideoData.description;
			//console.log(infoData);
			info_inner.html(infoData);
			
			info_toggle.css({opacity: 0, display: 'block'}).stop().animate({ 'opacity': 1},  {duration: 500});
			if(auto_open_description){
				toggleInfo();
			}
		}
		if(currVideoData.download){
			player_download.css('display','block');
		}
		resizeControls();
	}
	function hideInfo(){
		//console.log('hideInfo');
		infoHolder.css('display','none');
		info_toggle.css('display','none');
		infoOpened=false;
	}
	function toggleInfo(){
		//console.log('toggleInfo');
		if(!infoOpened){
			if(useShare)ap_share_holder.hide();
			infoHolder.css({opacity:0, display:'block'});//show it for jscrollpane!
			
			if(!info_scrollPaneApi){
				info_scrollPaneApi = infoHolder.jScrollPane().data().jsp;
				infoHolder.bind('jsp-initialised',function(event, isScrollable){
					//console.log('Handle jsp-initialised', ' isScrollable=', isScrollable);
				});
				infoHolder.jScrollPane({
					verticalDragMinHeight: 100,
					verticalDragMaxHeight: 100,
					mouseWheelSpeed: 30
				});
			}else{
				info_scrollPaneApi.reinitialise();
				info_scrollPaneApi.scrollToY(0);
			}
			
			infoHolder.stop().animate({ 'opacity': 1},  {duration: 500});
			infoOpened = true;
		}else{
			infoHolder.stop().animate({ 'opacity': 0},  {duration: 500, complete: function(){
				infoHolder.css('display','none');
			}});
			infoOpened=false;
		}
	}
	
	function resizeInfo(){
		if(info_scrollPaneApi){
			info_scrollPaneApi.reinitialise();
			info_scrollPaneApi.scrollToY(0);
		}
	}
	
	//***************
	
	function togglePlaylist(dir){
		if(!_componentInited || _playlistTransitionOn) return false;
		//console.log('togglePlaylist');
		if(playlist_type=='list'){
			//we cant use display none because of flash and yt!!!
			if(typeof dir === 'undefined'){
				parseInt(playlistHolder.css('left'),10)!=-out_left ? dir = 'off' : 'on';
			}
			if(dir=='off'){
				if(use_live_preview)cleanPreviewVideo();//dispose yt video before hiding!
				playlistHolderLeft = playlistHolder.css('left');
				playlistHolder.css('left',-out_left+'px');
			}else{
				checkPlaylist();
			}	
		}
	}
	
	function overComponent(e) {
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!lastPlaylist) return false;
		if (!e) var e = window.event;
		if(e.cancelBubble) e.cancelBubble = true;
		else if (e.stopPropagation) e.stopPropagation();
		showControls();
		return false;
	}
	
	function outComponent(e) {
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!lastPlaylist) return false;
		//console.log('outComponent');
		if (!e) var e = window.event;
		if(e.cancelBubble) e.cancelBubble = true;
		else if (e.stopPropagation) e.stopPropagation();
		if(auto_hide_controls && componentSize!= "fullscreen")hideControls();
		return false;
	}
	
	function outPlaylist(e) {
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!lastPlaylist) return false;
		//console.log('outPlaylist');
		if (!e) var e = window.event;
		if(e.cancelBubble) e.cancelBubble = true;
		else if (e.stopPropagation) e.stopPropagation();
		return false;
	}
	
	function showControls(){
		if(playlist_type=='wall' || playlist_type=='wall_popup')return false;
		//console.log('showControls');
		if(advert_on){
			if(advertContainInfo)player_addon.css('display','block');
		}else{
			player_addon.css('display','block');
		}
		if(!videoInited) return false;
		if(advert_on){
			if(show_controls_in_advert)playerControls.css('display','block');
			if(show_skip_button_in_advert)ap_adv_skip.css('display','block');
		}else{
			playerControls.css('display','block');
		}
		resizeControls();
	}
	function showControls2(){
		if(playlist_type=='wall' || playlist_type=='wall_popup')return false;
		//console.log('showControls');
		if(advert_on){
			if(advertContainInfo)player_addon.css('display','block');
		}else{
			player_addon.css('display','block');
		}
		if(!videoInited) return false;
		if(advert_on){
			if(show_controls_in_advert)playerControls.css('display','block');
			if(show_skip_button_in_advert)ap_adv_skip.css('display','block');
		}else{
			playerControls.css('display','block');
		}
	}
	
	function hideControls(){
		//console.log('hideControls');
		if(playlist_type=='wall' || playlist_type=='wall_popup')return false;
		player_addon.css('display','none');
		playerControls.css('display','none');
		if(use_tooltips)mainWrapper.find('.tooltipstered').tooltipster('hide');
		if(advert_on && ap_adv_skip)ap_adv_skip.css('display','none');
	}
	
	//************* RESIZE
	
	if(playlist_type=='list'){
		_window.resize(function() {
			 if(!_componentInited || _playlistTransitionOn) return false;
			 _doneResizing();
		});
	}
	
	if(isMobile){
		_window.doubletap(function() { 
			setTimeout(function(){
				clearTimeout($(this));
				_doneResizing();
			 },500);
		 });	
	}
	
	function _doneResizing(){
		//console.log('_doneResizing');
		
		checkPlaylist();
		
		if(componentSize== "fullscreen"){
			resizeComponent();	
		}else{
			if(mediaType == 'local'){
				if(!videoInited){
					resizePreview(previewPoster);
				}else{
					if(html5Support)resizeVideo();
				}
			}else if(mediaType == 'youtube'){
				resizeVideo();
			}
		}
		resizeControls();
		if(infoOpened)resizeInfo();
		if(useShare)resizeShare();
		
		if(captionsExist){
			var h = mediaHolder.height();
			captionator.setHeight(h);
			captionator.setRedraw(false);
		}
	}
	
	function checkPlaylist(){
		//console.log('checkPlaylist ', componentSize);
		if(!playlistHidden){
		
			if(playlist_type=='list'){
				
				if(componentSize!= "fullscreen"){
					
					var w = mainWrapper.width() != 0 ? mainWrapper.width() : getDocumentWidth(), 
					h = mainWrapper.height() != 0 ? mainWrapper.height() : getDocumentHeight();
					
					if(playlist_orientation == 'vertical'){
							
						if(w < 400 + plhow ){
							playlistHolder.removeClass('playlistHolder').addClass('playlistHolder_small');
							playerHolder.css({'width':w-playlistHolder.width()+'px'});
						}else{
							if(!layout100perc){
								var w1 = w-plhow > phow ? phow : w-plhow;
							}else{
								var w1 = w-plhow;
							}
							playerHolder.css({'width':w1+'px'});
							playlistHolder.removeClass('playlistHolder_small').addClass('playlistHolder');
						}
						
						var pw =playerHolder.width(); 
						playlistHolder.css({'left': pw+'px'});
						
						mediaHolder.css({'width':pw+'px'});
						mediaPreview.css({'width':pw+'px'});
						if(youtubeIframeMain)youtubeIframeMain.css({'width':pw+'px'});
						//if(!html5Support)$(flashMain).css({'width':pw+'px'});
						playerControls.css({'width':pw+'px'});
						//preloader.css({'left':pw/2-preloader.outerWidth(true)/2+'px'});
						
					}else{//horizontal
					
						if(layout100perc){
							playerHolder.css({'height':h-playlistHolder.height()+'px'});
							
							var pw =playerHolder.height(); 
							//playlistHolder.css({'top': pw+'px'});
							if(youtubeIframeMain)youtubeIframeMain.css({'height':pw+'px'});
							mediaHolder.css({'height':pw+'px'});
							mediaPreview.css({'height':pw+'px'});
							playerControls.css({'top':pw-playerControls.height()+'px'});
						}
						playlistHolder.css({'left': 0+'px'});
					}
				}else{
					
					var w =playerHolder.width(), h = playerHolder.height(); 
					
					if(playlist_orientation == 'vertical'){
						playerHolder.css({'width':w+'px'});
						mediaHolder.css({'width':w+'px'});
						mediaPreview.css({'width':w+'px'});
						//if(youtubeIframeMain)youtubeIframeMain.css({'width':w+'px'});
						playerControls.css({'width':w+'px'});
					}else{
						
						if(layout100perc){
							playerHolder.css({'height':h+'px'});
							mediaHolder.css({'height':h+'px'});
							mediaPreview.css({'height':h+'px'});
							if(youtubeIframeMain)youtubeIframeMain.css({'height':h+'px'});
							playerControls.css({'top':playerHolder.height()-playerControls.height()+'px'});
						}
					}
				}
				
				_thumbInnerContainerSize=0;
				var i = 0, div;
				for(i;i<_playlistLength;i++){
					div = $(playlistDataArr[i].item);
					if(playlist_orientation == 'horizontal'){
						_thumbInnerContainerSize+=div.outerWidth(true);
					}else{
						_thumbInnerContainerSize+=div.outerHeight(true);
					}
				}
				
				if(scroll_type == 'buttons'){
					if(playlist_orientation == 'horizontal'){
						thumbInnerContainer.css('left', 0+'px');
						playlist_inner.width(_thumbInnerContainerSize);
					}else{
						thumbInnerContainer.css('top', 0+'px');
					}
					_checkThumbPosition();
				}else if(scroll_type == 'scroll'){
					if(playlist_orientation == 'horizontal'){
						lastPlaylist.width(_thumbInnerContainerSize);
					}
					checkScroll();
				}
			}
		}
	}

	function resizeComponent(){
		//console.log('resizeComponent');

		if(controlsTimeoutID) clearTimeout(controlsTimeoutID);
		_doc.unbind('mousemove',trackFsMouse);

		if(componentSize== "fullscreen"){
			$('html').addClass('fsOverflow');
			
			if(playlist_type=='list'){
				playerHolder.removeClass('playerHolder').addClass('playerHolder_fs');
			}
			if(isMobile){
				controlsTimeoutID = setTimeout(function() {clearTimeout(controlsTimeoutID);checkActivity();},controls_timeout);
			}else{
				_doc.bind('mousemove',trackFsMouse);
				controlsTimeoutID = setTimeout(function() {clearTimeout(controlsTimeoutID);trackFsMouse();},controls_timeout);
			}
			if(click_blocker && mediaType != 'local')click_blocker.show();
			togglePlaylist('off');
			setFullscreenIcon('off');
			if(typeof vplpFsEnter !== 'undefined')vplpFsEnter(_self, media_id);//callback
		}else{
			$('html').removeClass('fsOverflow');//restore original overflow
			if(playlist_type=='list'){
				playerHolder.removeClass('playerHolder_fs').addClass('playerHolder');
			}
			if(click_blocker)click_blocker.hide();
			setFullscreenIcon('on');
			if(typeof vplpFsExit !== 'undefined')vplpFsExit(_self, media_id);//callback
		}
		
		checkPlaylist();
		
		if(previewPoster)resizePreview(previewPoster);
		if(html5Support)resizeVideo();
		if(infoOpened)resizeInfo();
		resizeControls();
		showControls();
		
		if(captionsExist){
			if(captionTimeoutID) clearTimeout(captionTimeoutID);
			captionTimeoutID = setTimeout(fixCaptionOutOfFs, captionTimeout);
		}
	}
	
	function fixCaptionOutOfFs(){
		if(captionTimeoutID) clearTimeout(captionTimeoutID);
		var h = mediaHolder.height();
		captionator.setHeight(h);
	}
	
	//chrome fix, 
	//http://stackoverflow.com/questions/17818493/mousemove-event-repeating-every-second
	//http://stackoverflow.com/questions/4579071/jquery-mousemove-is-called-even-if-the-mouse-is-still
	function trackFsMouse(e){
		if(!videoInited) return false;
		if(controlsTimeoutID)clearTimeout(controlsTimeoutID);
		if(e){
			if (prevX != e.clientX) {
				//show controls
				//console.log('mouse moved');
				showControls2();
			}
			prevX = e.clientX;
		}
		controlsTimeoutID = setTimeout(function(){
			//hide controls
			//console.log('mouse still');
			hideControls();
			
		},controls_timeout);
	}
	
	if(isMobile){
		_doc.bind("touchend.ap2",function(e){
			if(componentSize== "fullscreen"){
				if(controlsTimeoutID) clearTimeout(controlsTimeoutID);
				showControls2();
				controlsTimeoutID = setTimeout(function() {clearTimeout(controlsTimeoutID);checkActivity();},controls_timeout);
			}
		});
	}
	
	function resizeShare(){
		ap_share_wrapper.css('left', 0);
		var sw = ap_share_wrapper.width();
		if(sw + parseInt(ap_share_holder.css('left'),10) > playerHolder.width())sw = playerHolder.width() - parseInt(ap_share_holder.css('left'),10);
		ap_share_holder.width(sw);
	}
	
	function checkActivity(){
		if(controlsTimeoutID) clearTimeout(controlsTimeoutID);
		hideControls();
		controlsTimeoutID = setTimeout(function() {clearTimeout(controlsTimeoutID);checkActivity();},controls_timeout);
	}
	
	function checkActivity2(){
		if(controlsTimeoutID) clearTimeout(controlsTimeoutID);
		hideControls();
	}
	
	function checkKey() {
		this.value = this.value.replace(/[^0-9]/g, "");
	}
	
	function isNumber(n) {
	   return !isNaN(parseFloat(n)) && isFinite(n);
	}

	function resizeControls(){
		//console.log('resizeControls');
		seekBarElementsSize = getSeekBarElementsSize();
		playerControlsSize = playerControls.width();
		seekBarSize = playerControlsSize - seekBarElementsSize - player_seekbar_offset;
		//console.log(playerControlsSize, seekBarSize, seekBarElementsSize);
		player_seekbar.width(seekBarSize + player_seekbar_offset-1);
		progress_bg.width(seekBarSize);
	}
	
	function getSeekBarElementsSize(){
		var a = player_mediaTime_current.css('display') == 'block' ? current_time_width : 0;
			b = player_mediaTime_total.css('display') == 'block' ? total_time_width : 0; 
			c = player_volume.css('display') == 'block' ? volume_width : 0; 
			d = player_fullscreen.css('display') == 'block' ? fullscreen_width : 0;
			e = player_captions.css('display') == 'block' ? player_captions_width : 0;
			f = player_quality.css('display') == 'block' ? player_quality_width : 0;
			g = player_download.css('display') == 'block' ? player_download_width : 0;
			if(fs_removed)d=0;
		return toggleControl_width + a + b + c + d + e + f + g;
	}
	
	function resizePreview(img) {
		if(!img) return false;
		//console.log('resizePreview');
		var o, x, y, w = mediaPreview.width(), h = mediaPreview.height();
		
		if(aspect_ratio == 0) {//normal media dimensions
			o=getMediaSize();
		}else if(aspect_ratio == 1) {//fitscreen
			o = retrieveObjectRatio(true, img,previewOrigW,previewOrigH);
		}else if(aspect_ratio == 2) {//fullscreen
			o = retrieveObjectRatio(false, img,previewOrigW,previewOrigH);
		}
		x = parseInt(((w - o.width) / 2),10);
		y = parseInt(((h - o.height) / 2),10);
		img.css({
			width: o.width+ 'px',
			height: o.height+ 'px',
			left:x+'px',
			top:y+'px'
		});
	}
	
	function resizeVideo() {
		
		if(_playlistManager.getCounter()==-1) return false;
		//console.log('resizeVideo');
		var o, x, y, w = mediaHolder.width(), h = mediaHolder.height();
		
		if(aspect_ratio == 0) {//normal media dimensions
			o=getMediaSize();
		}else if(aspect_ratio == 1) {//fitscreen
			o = retrieveObjectRatio(true);
		}else if(aspect_ratio == 2) {//fullscreen
			o = retrieveObjectRatio(false);
		}
		x = parseInt(((w - o.width) / 2),10);
		y = parseInt(((h - o.height) / 2),10);
		//console.log(o.width, o.height, w,h);
		if(mediaType == 'local'){
			if(video){
				video.css({
					width: o.width+ 'px',
					height: o.height+ 'px',
					left:x+'px',
					top:y+'px'
				});
			}
		}else if(mediaType == 'youtube'){
			if(youtubeIframeMain){
				youtubeIframeMain.css({
					width: o.width+ 'px',
					height: o.height+ 'px',
					left:x+'px',
					top:y+'px'
				});
			}
		}
	}
	
	 function retrieveObjectRatio( _fitScreen, obj, cw,ch) {
		//console.log('retrieveObjectRatio'); 
		var _paddingX=0,_paddingY=0;
	
		var w = playerHolder.width(), h = getComponentSize('h') != 0 ? getComponentSize('h') : getDocumentHeight();
		
		var targetWidth, targetHeight, val={};
	 	if(!obj){
			var obj = getMediaSize();
			targetWidth = obj.width;
			targetHeight = obj.height;
		}else{
			if(typeof(cw) !== "undefined" && typeof(ch) !== "undefined"){
				targetWidth = cw;
				targetHeight = ch;
			}else{
				targetWidth = obj.width();
				targetHeight = obj.height();
			}
		}
		
		//console.log(w,', ',h);
		//console.log(targetWidth,', ',targetHeight);
		
		var destinationRatio = (w - _paddingX) / (h - _paddingY);
		var targetRatio = targetWidth / targetHeight;

		if (targetRatio < destinationRatio) {
			if (!_fitScreen) {//fullscreen
				val.height = ((w - _paddingX) /targetWidth) * targetHeight;
				val.width = (w - _paddingX);
			} else {//fitscreen
				val.width = ((h - _paddingY) / targetHeight) *targetWidth;
				val.height = (h - _paddingY);
			}
		} else if (targetRatio > destinationRatio) {
			if (_fitScreen) {//fitscreen
				val.height = ((w - _paddingX) /targetWidth) * targetHeight;
				val.width = (w - _paddingX);
			} else {//fullscreen
				val.width = ((h - _paddingY) / targetHeight) *targetWidth;
				val.height = (h - _paddingY);
			}
		} else {//fitscreen & fullscreen
			val.width = (w - _paddingX);
			val.height = (h - _paddingY);
		}
		return val;
	}
	
	function getMediaSize() {
		//console.log('getMediaSize');
		var o={}, default_w=640, default_h=360;
		//console.log(videoUp2Js.videoWidth, mediaWidth, videoUp2Js.videoHeight, mediaHeight);
		if(mediaType=='local'){
			if(!mediaWidth || isNaN(mediaWidth) || !mediaHeight || isNaN(mediaHeight)){
				if(videoUp2Js){
					o.width = videoUp2Js.videoWidth;
					o.height = videoUp2Js.videoHeight;
				}else{
					o.width = default_w;//default values (16:9)
					o.height = default_h;
				}
			}else{
				o.width=mediaWidth;
				o.height=mediaHeight;	
			}
		}else if(mediaType == 'youtube'){
			if(!mediaWidth || isNaN(mediaWidth) || !mediaHeight || isNaN(mediaHeight)){
				o.width = default_w;//default youtube values (16:9)
				o.height = default_h;
			}else{
				o.width=mediaWidth;
				o.height=mediaHeight;	
			}
		}
		return o;
	}
	
	function getComponentSize(type) {
		if(type == "w"){//width
			return componentSize == "normal" ? componentWrapper.width() : getDocumentWidth();
		}else{//height
			return componentSize == "normal" ? componentWrapper.height() : getDocumentHeight();
		}
	}
	
	function getDocumentWidth(){
		return Math.max(
			_window.width(),
			/* For opera: */
			document.documentElement.clientWidth
		);
	};	
	
	function getDocumentHeight(){
		return Math.max(
			_window.height(),
			/* For opera: */
			document.documentElement.clientHeight
		);
	};
	
	//**************** FULLSCREEN
	
	function setFullscreenIcon(dir){
		if(dir=='on'){
			player_fullscreen.find('i').removeClass().addClass('fa fa-expand ap_fs_ent icon_color');
			if(hasContextMenu)contextToggleFs.find('span').html('Fullscreen');
		}else{
			player_fullscreen.find('i').removeClass().addClass('fa fa-compress ap_fs_exit icon_color');
			if(hasContextMenu)contextToggleFs.find('span').html('Exit Fullscreen');
		}
	}
	
	if(playlist_type=='list'){
		if(fullscreenPossible){
			_doc.on("fullscreenchange mozfullscreenchange MSFullscreenChange webkitfullscreenchange", function(e){
				if(componentSize== "fullscreen" && fullscreenCount>0){
					componentSize="normal";
					resizeComponent();	
					if(hasContextMenu)hideContextMenu();
				}
				fullscreenCount=1;//fix for escape key
				if(captionsExist)captionator.setRedraw(false);
			});
		}
	}
	
	function toggleFullscreen(btnInitiated){
		//console.log('toggleFullscreen', fullscreenPossible);
		fullscreenCount=0;
		
		if(componentSize== "normal")componentSize= "fullscreen";
		else componentSize="normal";
		//http://stackoverflow.com/questions/8427413/webkitrequestfullscreen-fails-when-passing-element-allow-keyboard-input-in-safar
					
		if(fullscreenPossible){
			var elem = document.documentElement;
			if (elem.requestFullscreen) {
				if (document.fullscreenElement || document.fullscreenElement) {
					document.exitFullscreen();
				} else {
					elem.requestFullscreen();
				}
			} else if (elem.webkitRequestFullScreen) {
				if (document.webkitIsFullScreen) {
					document.webkitCancelFullScreen();
				} else {
					elem.webkitRequestFullScreen();
				}
			} else if (elem.msRequestFullscreen) {
				if (document.msIsFullscreen || document.msFullscreenElement) {
					document.msExitFullscreen();
				} else {
					elem.msRequestFullscreen();
				}
			} else if (elem.mozRequestFullScreen) {
				if (document.fullscreenElement || document.mozFullScreenElement) {
					document.mozCancelFullScreen();
				} else {
					elem.mozRequestFullScreen();
				}
			}
			/*else if(isIOS){
				try{
					if(videoUp2Js && videoUp2Js.webkitEnterFullScreen != undefined)videoUp2Js.webkitEnterFullScreen();
				}catch(error){}
			}*/
		}
		if(!fullscreenPossible) {
			resizeComponent();	
			if(captionsExist)captionator.setRedraw(false);//if fs not supported
		}else if(componentSize=="normal" && btnInitiated){
			resizeComponent();		
		}
	}
	function checkFullScreenSupport() {
		var support=false;
		if (document.fullscreenEnabled || document.webkitFullscreenEnabled || document.mozFullScreenEnabled || document.msFullscreenEnabled)support=true;
		else if (document.documentElement.requestFullscreen || document.documentElement.mozRequestFullScreen || document.documentElement.msRequestFullscreen || document.documentElement.oRequestFullscreen || document.documentElement.webkitRequestFullScreen) support=true;
		//if(isiPad) support=true;
		return support;
	}
	
	//**************** HELPER FUNCTIONS
	
	function stripslashes(str) {
		str=str.replace(/\\/g,'/');
		str=str.replace(/\\'/g,'\'');
		str=str.replace(/\\"/g,'"');
		str=str.replace(/\\0/g,'\0');
		str=str.replace(/\\\\/g,'\\');
		return str;
	};
	
	function isEmpty(str) {
	    return str.replace(/^\s+|\s+$/g, '').length == 0;
	}
	
	function resetData(){
	  player_mediaTime_current.find('p').html('00:00');
	  player_mediaTime_total.find('p').html('00:00');
	  progress_level.width(0);
	  load_level.width(0);
	}	
	
	function keysrt(arr, type, desc){
		if(type == 'title'){
			arr.sort(function(a, b) { 
				return a.title == b.title ? 0 : a.title < b.title ? -1 : 1 
			});	
		}else if(type == 'type'){
			arr.sort(function(a, b) { 
				return a.type == b.type ? 0 : a.type < b.type ? -1 : 1 
			});	
		}
		if(desc)arr.reverse();
	}
	
	function canPlayVorbis() {
		var v = document.createElement('video');
		return !!(v.canPlayType && v.canPlayType('video/ogg; codecs="theora, vorbis"').replace(/no/, ''));
	}
	
	function canPlayMP4() {
		var v = document.createElement('video');
		return !!(v.canPlayType && v.canPlayType('video/mp4; codecs="avc1.42E01E, mp4a.40.2"').replace(/no/, ''));
	}
	
	function canPlayWebM() {
		var v = document.createElement('video');
		return !!(v.canPlayType && v.canPlayType('video/webm; codecs="vp8, vorbis"').replace(/no/, ''));
	}
	
	function formatCurrentTime(seconds) {
		seconds = Math.round(seconds);
		minutes = Math.floor(seconds / 60);
		minutes = (minutes >= 10) ? minutes : "0" + minutes;
		seconds = Math.floor(seconds % 60);
		seconds = (seconds >= 10) ? seconds : "0" + seconds;
		return minutes + ":" + seconds;
	}
	
	function formatDuration(seconds) {
		seconds = Math.round(seconds);
		minutes = Math.floor(seconds / 60);
		minutes = (minutes >= 10) ? minutes : "0" + minutes;
		seconds = Math.floor(seconds % 60);
		seconds = (seconds >= 10) ? seconds : "0" + seconds;
		return minutes + ":" + seconds;
	}
	
	function qualifyURL(url) {
		var a = document.createElement('a');
		a.href = url;
		return a.href;
	}
	
	/* creates tracks from html format */	
	function createTrackFromHtml(data){
		//console.log(data);
		var obj = $(data), str, ul, li;
		str = $('<div>').append(obj.clone()).html();//convert object to string
		ul = document.createElement('div');
		ul.innerHTML = str;
		li = ul.firstChild;
		return $(li);
	}
	function getTrackData(_item){
		var obj = {};
		obj.item = _item.clone();
		obj.type = _item.attr('data-type');
		
		if(obj.type != 'local'){
			
			obj.path = obj.id = _item.attr('data-path');
			
			if(_item.attr('data-limit') != undefined && !isEmpty(_item.attr('data-limit'))){
				obj.limit = parseInt(_item.attr('data-limit'),10);
			}
			if(_item.attr('data-order') != undefined && !isEmpty(_item.attr('data-order'))){
				obj.order = _item.attr('data-order');
			}
			if(_item.attr('data-query') != undefined && !isEmpty(_item.attr('data-query'))){
				obj.query = _item.attr('data-query');
			}
			if(_item.attr('data-quality') != undefined && !isEmpty(_item.attr('data-quality'))){
				obj.quality = _item.attr('data-quality');
			}
		}
		
		if(use_deeplink){
			if(_item.attr('data-address') != undefined && !isEmpty(_item.attr('data-address'))){
				obj.deeplink = _item.attr('data-address');
			}else{
				var path = obj.type == 'local' ? _item.attr('data-mp4') : _item.attr('data-path');
				alert('data-address attribute missing on playlist item: ' + path);
				return false;	
			}
		}	
		if(_item.attr('data-preview') != undefined && !isEmpty(_item.attr('data-preview'))){
			obj.preview = _item.attr('data-preview');
		}
		if(_item.attr('data-thumb') != undefined && !isEmpty(_item.attr('data-thumb'))){
			obj.thumb = _item.attr('data-thumb');
		}
		if(_item.attr('data-video-preview') != undefined && !isEmpty(_item.attr('data-video-preview'))){
			obj.video_preview = _item.attr('data-video-preview');
		}
		if(_item.attr('data-download') != undefined && !isEmpty(_item.attr('data-download'))){
			obj.download = _item.attr('data-download');
		}
		
		obj.lightbox = {};
		
		if(_item.attr('data-link') != undefined && !isEmpty(_item.attr('data-link'))){
			obj.lightbox.link = _item.attr('data-link');
		}
		if(_item.attr('data-hook') != undefined && !isEmpty(_item.attr('data-hook'))){
			obj.lightbox.hook = _item.attr('data-hook');
		}
		if(_item.attr('data-weblink') != undefined && !isEmpty(_item.attr('data-weblink'))){
			obj.lightbox.weblink = _item.attr('data-weblink');
		}
		if(_item.attr('data-target') != undefined && !isEmpty(_item.attr('data-target'))){
			obj.lightbox.target = _item.attr('data-target');
		}

		if(_item.children("div[class='ap_title']").length){
			obj.title = $.trim(_item.children("div[class='ap_title']").html());
		}
		if(_item.children("div[class='ap_short_desc']").length){
			obj.description_short = $.trim(_item.children("div[class='ap_short_desc']").html());
		}
		if(_item.children("div[class='ap_desc']").length){
			obj.description = $.trim(_item.children("div[class='ap_desc']").html());
		}
		if(_item.children("div[class='track_list']").length){
			obj.subs = _item.children("div[class='track_list']").children();
		}
		
		
		return obj;
	}
	
	function getAdvertData(_item){
		
		var obj = {};
		obj.item = _item.clone();
		obj.type = _item.attr('data-type');
		
		if(_item.attr('data-skipEnableTime') != undefined && parseInt(_item.attr('data-skipEnableTime'),10)>0){
			obj.skip_time = parseInt(_item.attr('data-skipEnableTime'),10);
		}
		if(_item.attr('data-link') != undefined && !isEmpty(_item.attr('data-link'))){
			obj.link = _item.attr('data-link');
			obj.target = '_blank';
			if(_item.attr('data-target') != undefined && !isEmpty(_item.attr('data-target'))){
				obj.target = _item.attr('data-target');
			}
		}
		if(_item.attr('data-preview') != undefined && !isEmpty(_item.attr('data-preview'))){
			obj.preview = _item.attr('data-preview');
		}
		if(_item.children("div[class='ap_desc']").length){
			obj.description = $.trim(_item.children("div[class='ap_desc']").html());
		}
		if(_item.children("div[class='track_list']").length){
			obj.subs = _item.children("div[class='track_list']").children();
		}
		
		return obj;
			
	}
	
	
	// ******************************** PUBLIC API **************** //
	
	/* play active media */
	this.playMedia = function() {
		if(!_componentInited || _playlistTransitionOn || !mediaType) return false;
		if(mediaPlaying) return false;
		if(mediaType == 'local'){
			togglePlayBack();
		}else if(mediaType == 'youtube'){
			if(_youtubePlayer) _youtubePlayer.play();
		}
		mediaPlaying=true;
	}
	/* pause active media */
	this.pauseMedia = function() {	
		if(!_componentInited || _playlistTransitionOn || !mediaType)  return false;
		if(!mediaPlaying) return false;
		if(mediaType == 'local'){
			togglePlayBack();
		}else if(mediaType == 'youtube'){
			if(_youtubePlayer) _youtubePlayer.pause();
		}
		mediaPlaying=false;
	}	
	/* used in multiple instances to toggle one instance if another has started */ 
	this.checkMedia = function(act) {	
		if(!_componentInited || _playlistTransitionOn || !mediaType) return false;
		var action = act.toLowerCase();
		if(mediaPlaying){
			if(action=='pause'){
				if(mediaType == 'local'){
					togglePlayBack();
				}else if(mediaType == 'youtube'){
					if(_youtubePlayer) _youtubePlayer.pause();
				}
				mediaPlaying=false;
			}
		}
	}	
	/* toggle active media playback */
	this.togglePlayBack = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(playlist_type=='wall' || playlist_type=='wall_popup') return false;
		if(!mediaType) return false;
		togglePlayBack();
	}
	/* play next media */
	this.nextMedia = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!lastPlaylist) return false;
		if(playlist_type=='wall' || playlist_type=='wall_popup') return false;
		nextMedia();
	}
	/* play previous media */
	this.previousMedia = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!lastPlaylist) return false;
		if(playlist_type=='wall' || playlist_type=='wall_popup') return false;
		previousMedia();
	}
	/* set autoplay */
	this.setAutoPlay = function(val){
		if(!_componentInited) return false;
		setAutoplay(val);
	}
	/* get autoplay */
	this.getAutoPlay = function(){
		if(!_componentInited) return false;
		return auto_play;
	}
	/* get volume (0-1) */
	this.getVolume = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		return default_volume;	
	}
	/* set volume (0-1) */
	this.setVolume = function(val){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(playlist_type=='wall' || playlist_type=='wall_popup') return false;
		if(!videoInited) return false;
		if(val<0) val=0;
		else if(val>1) val=1;
		default_volume = val;
		setVolume();
	}
	/* toggle random playback */ 
	this.toggleShuffle = function() {
		if(!_componentInited || _playlistTransitionOn) return false;
		if(random_play){
			random_play=false;
		}else{
			random_play=true;
		}
		_playlistManager.setRandom(random_play);
	}
	/* toggle playlist loop */ 
	this.toggleLoop = function() {
		if(!_componentInited || _playlistTransitionOn) return false;
		if(looping_on){
			looping_on=false;
		}else{
			looping_on=true;
		}
		_playlistManager.setLooping(looping_on);
	}
	/* seek media (in seconds) */
	this.seek = function(val){
		if(!_componentInited || _playlistTransitionOn) return false;
		setProgress2(val);	
	}
	/* destroy active media */
	this.destroyMedia = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(playlist_type=='wall' || playlist_type=='wall_popup') return false;
		destroyMedia();
	}
	/* destroy active playlist */
	this.destroyPlaylist = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		destroyPlaylist();
		lastPlaylist=null;
		_activePlaylist=null;
	}
	/* toggle description */
	this.toggleDescription = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(playlist_type=='wall' || playlist_type=='wall_popup') return false;
		if(_playlistManager.getCounter()==-1) return false;
		toggleInfo();
	}
	/* get title */
	this.getTitle = function(value) {	
		if(!_componentInited || _playlistTransitionOn) return false;
		if(currVideoData){
			return currVideoData.title;
		}else{
			return null;	
		}
	}
	/* get description */
	this.getDescription = function(value) {	
		if(!_componentInited || _playlistTransitionOn) return false;
		if(currVideoData){
			return currVideoData.description;
		}else{
			return null;	
		}
	}
	/* load media/playlist 
		use_deeplink:
        pass deeplink url as string (single or two level).
	
		no Deeplink: 
	    for media pass number, counting starts from 0,
	    for playlist pass element 'id' attribute.
	    */
	this.loadMedia = function(value){
		//console.log('loadMedia');
		if(!_componentInited || _playlistTransitionOn) return false;
		if(playlist_type=='list'){
			if(use_deeplink){
				if(typeof(value) === 'string'){
					$.address.value(value);
				}else{
					alert('Invalid value loadMedia Deeplink!');
					return false;	
				}
			}else{
				if(typeof(value) === 'number'){
					if(!lastPlaylist) return false;
					if(value<0)value=0;
					else if(value>_playlistLength-1)value=_playlistLength-1;
					_enableActiveItem();
					_playlistManager.processPlaylistRequest(value);
				}else if(typeof(value) === 'string'){
					if(_activePlaylist==value)return false;//playlist already loaded!
					_activePlaylist=value;
					_setPlaylist();			
				}else{
					alert('Invalid value loadMedia no Deeplink!');
					return false;	
				}
			}
		
		}else{
			if(typeof(value) === 'number'){
				if(value<0)value=0;
				else if(value>_playlistLength-1)value=_playlistLength-1;
				_enableActiveItem();
				_playlistManager.processPlaylistRequest(value);
			}else if(typeof(value) === 'string'){
				if(_activePlaylist==value)return false;//playlist already loaded!
				_activePlaylist=value;
				_setPlaylist();			
			}else{
				alert('Invalid value loadMedia Wall Layout!');
				return false;	
			}
		}
	}
	/* add track to current playlist(
			 param1 (required): type of track, html/data (string),
			 param2 (required): pass track or array of tracks,
			 param3 (optional): automatically play track after add (boolean) 
			 param4 (optional): position to insert track(s) (number, counting starts from 0), leave out parameter for the end append) */
	this.addTrack = function(format, track, playit, position) {
		if(!_componentInited || _playlistTransitionOn) return false;
		
		if(typeof(format) === 'undefined'){
			alert('addTrack method requires format parameter. AddTrack failed.');
			return false;
		}
		if(typeof(track) === 'undefined'){
			alert('addTrack method requires track parameter. AddTrack failed.');
			return false;
		}
		addTrack_playit=false;
		if(typeof(playit) !== 'undefined'){
			addTrack_playit = playit;
		}
		
		var len = 1, is_array = false;	
		
		if(typeof(track) === 'string' || Object.prototype.toString.call(track) === '[object Object]'){
			//console.log('addtrack object');
		}else if(Object.prototype.toString.call(track) === '[object Array]'){
			//console.log('addtrack array');
			len = track.length;
			is_array=true;
		}else{
			alert('addTrack method requires track as string, object or array parameter. AddTrack failed.');
			return false;
		}
		
		insert_position = position;
		end_insert = false;
		addTrack_process=true;
		
		//console.log(addTrack_process);
		//console.log('addTrack: ', lastPlaylist, _playlistLength, insert_position);
		
		if(lastPlaylist){
			if(typeof(insert_position) !== 'undefined'){
				if(insert_position<0){
					alert('Invalid position to insert track to. Position number "' + position + '" doesnt exist. AddTrack failed.');
					return false;
				}
				else if(insert_position > _playlistLength){
					alert('Invalid position to insert track to. Position number "' + position + '" doesnt exist. AddTrack failed.');
					return false;
				}
				else if(insert_position == _playlistLength){
					end_insert=true;
				}
			}else{
				end_insert=true;
				insert_position = _playlistLength;	
			}
		}else{//first time create playlist from addTrack method
			if(typeof(insert_position) !== 'undefined'){
				if(insert_position!=0){
					alert('Invalid position to insert track to. Position number "' + position + '" doesnt exist. AddTrack failed.');
					return false;
				}
			}else{
				insert_position=0;
			}
			end_insert=true;
		}
		
		_playlistTransitionOn=true;
		preloader.css('display','block');
		_playlistLoaded=false;
		apiCreation=true;
		
		_videoProcessCounter=0;//reset
		_videoProcessData=[];
		_videoProcessDataUrl=[];
		var i = 0, _track, obj, _item;
		
		for(i; i < len; i++){
			//create playlist item node	
			_track = is_array ? track[i] : track;
			//console.log(_track);
			obj = {};
			if(format == 'html'){
				obj = getTrackData(createTrackFromHtml(_track));
				_videoProcessDataUrl.push(obj);
				//console.log(obj.path);
			}else{//data
				/*if(_track.type && _track.mp4 || _track.path){//required data!
					_li = createTrackFromData(_track).appendTo(playlist_ul);
				}else{
					continue;	
				}*/
			}
		}
		//console.log(_videoProcessDataUrl);
		_playlistLength=_videoProcessDataUrl.length;
	
		playlist_first_init = false;//reset
		if(!lastPlaylist){
			playlist_first_init = true;
		}
		lastPlaylist = playlist_content;
		
		_processJson();
		
	}
	/*remove track from current playlist(
         param1: (string!) pass track title or number (for numbers, counting starts from zero). 
		 */
	this.removeTrack = function(id) {
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!lastPlaylist) return false;
		
		if(typeof(id) === 'undefined'){
			alert('removeTrack method requires track parameter. removeTrack failed.');
			return false;
		}
		
		if(!$.isNumeric(id)){
			
			var i = 0, found = false;
			for(i;i<_playlistLength;i++){//find media name and counter
				if(id == playlistDataArr[i].title){
					id = i;
					found=true;
					break;	
				}
			}
			if(!found){
				alert('Track with name "' + id + '" doesnt exist. RemoveTrack failed.');
				return false;	
			}
			
		}else if($.isNumeric(id)){
			id = parseInt(id,10);
			
			if(id<0 || id > _playlistLength-1){
				alert('Invalid track number. Track number  "' + id + '" doesnt exist. RemoveTrack failed.');
				return false;
			}
			
		}else{
			alert('removeTrack method requires either a track number or a track title to remove. removeTrack failed.');
			return false;
		}
		
		//console.log('removeTrack: ', id);
		if(playlistDataArr[id]){
			updateTrackData(id);
			if(playlist_type=='list'){
				updateScrollData();
				checkScroll();
			}
			updateTrackRemoval(id);
		}else{
			alert('RemoveTrack with id "' + id + '" failed.');
		}
	}
	
	function updateTrackRemoval(track){
		
		_playlistLength = playlistDataArr.length;
		//console.log('updateTrackRemoval', _playlistLength);
		
		if(_playlistLength > 0){
			
			var current_counter = _playlistManager.getCounter();
			if(track == current_counter){//remove number equal to current counter
				cleanMedia();	
				_playlistManager.setPlaylistItems(_playlistLength);//counter resets to -1
			}else{
				_playlistManager.setPlaylistItems(_playlistLength, false);
				if(track < current_counter){//remove number less than current counter
					_playlistManager.reSetCounter(_playlistManager.getCounter()-1);//if we removed media before current playing media, descrease counter!	
				}else{//remove number larger than current counter, current counter doesnt change
				}
			}
			
		}else{//we removed last track in playlist
			destroyPlaylist();	
			lastPlaylist=null;
			if(typeof vplpPlaylistEmpty !== 'undefined')vplpPlaylistEmpty(_self, media_id);//callback
		}
	}
	/* clean preview video (if use_live_preview = true) */
	this.cleanPreviewVideo = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!use_live_preview) return false;
		cleanPreviewVideo();
	}
	/* return number of items in playlist */ 
	this.getMediaCount = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		return isNumber(_playlistLength) ? _playlistLength : 0;
	}
	/* return media id (player instance reference) */
	this.getMediaId = function(){
		if(!_componentInited) return false;
		return media_id;
	}
	/* return media playing or paused */
	this.getMediaPlaying = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		return mediaPlaying;
	}
	/* return playlist loading (is playlist loading) */
	this.getPlaylistTransition = function(){
		return _playlistTransitionOn;
	}
	/* return playlist loaded (finished loading) */
	this.getPlaylistLoaded = function(){
		return _playlistLoaded;
	}
	/* return media initiated (active video ready) */
	this.getVideoInited = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		return videoInited;
	}
	/* return component setup is finished */
	this.getSetupDone = function(){
		return _componentInited;
	}
	/* return playlist list holder */
	this.getPlaylistList = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		return playlist_list;
	}
	/* return active item id in playlist (counting starts from 0) */
	this.getActiveItem = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		return _activeItemID;
	}
	/* set active item */
	this.setActiveItem = function(i){
		if(!_componentInited || _playlistTransitionOn) return false;
		active_item = i;
	}
	/* return active playlist id */
	this.getActivePlaylist = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		return _activePlaylistID;
	}
	/* return playlist data */
	this.getPlaylistData = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!lastPlaylist) return false;
		return playlistDataArr;	
	}
	/* set playlist data */
	this.setPlaylistData = function(arr){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!arr) return false;
		playlistDataArr = jQuery.extend(true, [], arr);	
		_playlistLength = playlistDataArr.length;
	}
	/* return player settings */
	this.getSettings = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		return settings ? settings : null;
	}
	/* return componentPlaylist */
	this.getPlaylist = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		return componentPlaylist;
	}
	/* clean youtube player, so we can reinit it again (neccesarry in some instances where player (sometimes) looses events (if component set to display none or similar) */
	this.cleanYt = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(_youtubePlayer){
			$(_youtubePlayer).unbind();
			_youtubePlayer.clean();
			_youtubePlayer = null;	
		}
		_youtubeInited = false;
	}
	/* skip intro */
	this.skipIntro = function(){
		if(!_componentInited || _playlistTransitionOn) return false;
		if(!advert_on) return false;
		if(skipEnableTime && !skipTimeHappened)return false;
		advert_done = true;
		if(!isMobile)setAutoplay(true);
		_findMedia();
		return false;
	}
	this.getIsMobile = function(){
		return isMobile;
	}
	
	//************ start selector dropdown
	//http://www.bulgaria-web-developers.com/projects/javascript/selectbox/
	var hap_drop;
	if($(settings.dropdown_id).find('.lp_playlist').length){
		if(!isMobile){
			hap_drop = $(settings.dropdown_id).find(".lp_playlist").selectbox({
				onChange: function (val, inst) {
					//console.log(val, inst);
					if(settings.useDeeplink)val=getFirstVideoDeeplink(val);
					_self.loadMedia(val);
				}
			});
		}else{//we want default mobile scroll on selectbox
			hap_drop = $(settings.dropdown_id).find('.lp_playlist').change(function() {
				var val = $(this).val();
				if(settings.useDeeplink)getFirstVideoDeeplink(val);
				_self.loadMedia(val);
			});
		}
	}
	
	function getFirstVideoDeeplink(val){
		//optional, search for first video deeplink
		var item = playlist_list.find("div[id="+val+"]").children('div[class*=playlistNonSelected]').eq(0), type = item.attr('data-type'), add;
		if(type == 'local' || type == 'youtube_single'){
			add = item.attr('data-address');
		}else{
			add = item.attr('data-address') + 1;
		}
		return val+='/'+add+'/';
	}
	
	//************ end selector dropdown
	
	
	
	return this;

	}
	
})(jQuery);






/* CALLBACKS */
function vplpSetupDone(instance, media_id){
	/* called when component is ready to use public API. Returns player instance, media_id. */
	//console.log('vplpSetupDone: ', media_id);
	
	if(media_id == 'popup_single'){
		if (hasLocalStorage) {
			if(localStorage.getItem('vplp_single_track')){
				var track = localStorage.getItem('vplp_single_track');
				instance.destroyMedia(); 
				instance.addTrack('html', track, true);
			}
		}
	}
}
function vplpPlaylistEnd(instance, media_id){
	/* called on playlist end. Returns player instance, media_id. */
	//console.log('vplpPlaylistEnd: ', media_id);
}
function vplpPlaylistLoaded(instance, media_id){
	/* called when playlist is loaded. Returns player instance, media_id. */
	//console.log('vplpPlaylistLoaded: ', media_id);
	
	if(media_id=='wall_clear2'){
		var arr = instance.getPlaylistData(), i = 0, len = arr.length, div, pi, t, d, remove;
		for(i;i<len;i++){
			pi = arr[i];
			if(pi.item && !pi.item.data('has_wallInfo')){
				t = pi.data.title;
				d = pi.data.description_short || pi.data.description;
				div = jQuery('<div class="wallInfo"><span class="wallTitle">'+t+'</span><br><span class="wallContent">'+d+'</span></div>').appendTo(pi.item);
				pi.item.data('has_wallInfo', true);
			}
		}
		instance.getPlaylist().find('.wallInfo').dotdotdot();
	}
}
function vplpItemTriggered(instance, media_id, counter){
	/* called when new media is triggered. Returns player instance, media_id, media counter. */
	//console.log('vplpItemTriggered: ', counter, media_id);
}
function vplpVideoStart(instance, media_id, counter){
	/* called when current playing video starts. Returns player instance, media_id, media counter. */
	//console.log('vplpVideoStart: ', counter);
}
function vplpPlay(instance, media_id, counter){
	/* called when media is played. Returns player instance, media_id, media counter. */
	//console.log('vplpPlay: ', counter);
	
	if(typeof(vplp_mediaArr) !== 'undefined' && vplp_mediaArr.length){
		var i = 0, len = vplp_mediaArr.length;
		for(i;i<len;i++){
			if(media_id != vplp_mediaArr[i].media_id){
				//console.log('vplpPlay: ', media_id, vplp_mediaArr[i].media_id);
				vplp_mediaArr[i].player_id.checkMedia('pause');
			}
		}
	}
}
function vplpPause(instance, media_id, counter){
	/* called when media is played. Returns player instance, media_id, media counter. */
	//console.log('vplpPause: ', counter);
}
function vplpVideoEnd(instance, media_id, counter){
	/* called when current playing video ends. Returns player instance, media_id, media counter. */
	//console.log('vplpVideoEnd: ', counter);
}
function vplpPlaylistItemEnabled(instance, media_id, target, counter){
	/* called on playlist item enable. Returns player instance, media_id, playlist item (target), media counter. */
	//console.log('vplpPlaylistItemEnabled: ', counter);
}
function vplpPlaylistItemDisabled(instance, media_id, target, counter){
	/* called on playlist item disable. Returns player instance, media_id, playlist item (target), media counter. */
	//console.log('vplpPlaylistItemDisabled: ', counter);
}
function playlistItemRollover(instance, media_id, target, counter){
	/* called on playlist item mouseenter. Returns player instance, media_id, playlist item (target), media counter. */
	//console.log('playlistItemRollover: ', counter);
}
function vplpPlaylistItemRollout(instance, media_id, target, counter){
	/* called on playlist item mouseleave. Returns player instance, media_id, playlist item (target), media counter. */
	//console.log('vplpPlaylistItemRollout: ', counter);
}
function vplpPlaylistEmpty(instance, media_id){
	/* called when playlist becomes empty (no items in playlist after new playlist has been created or last playlist item removed from playlist, NOT after destroyPlaylist!). Returns player instance, media_id. */
	//console.log('vplpPlaylistEmpty: ', media_id);
}
function vplpFsEnter(instance, media_id){
	/* called on fullscreen enter. Returns player instance, media_id. */
	//console.log('vplpFsEnter: ', media_id);
}
function vplpFsExit(instance, media_id){
	/* called on fullscreen exit. Returns player instance, media_id. */
	//console.log('vplpFsExit: ', media_id);
}
function vplpCaptionChange(instance, media_id, lang){
	/* called on caption change. Returns player instance, media_id, caption language. */
	//console.log('vplpCaptionChange: ', media_id, lang);
}
function vplpQualityChange(instance, media_id, quality){
	/* called on quality change. Returns player instance, media_id, quality. */
	//console.log('vplpQualityChange: ', media_id, quality);
}










/* double tap  */
(function(a){var d=/android|iphone|ipad/i.test(navigator.userAgent.toLowerCase())?"touchend":"click";a.fn.doubletap=function(e,b){b=null==b?300:b;this.bind(d,function(d){var f=(new Date).getTime(),c=a(this).data("lastTouch")||f+1,c=f-c;c<b&&0<c?(a(this).data("lastTouch",null),null!==e&&"function"===typeof e&&e(d)):a(this).data("lastTouch",f)})}})(jQuery);







(function($) {

	 $.youtubePlayer = function(data) {
		return new ap_YoutubePlayer(data);
	 };
	 
	 function ap_YoutubePlayer(data){
		
		 var _self = this;
		 this.isIE = data.isIE ? data.isIE : false;
		 this.isMobile = data.isMobile;
		 this.initialAutoplay = data.initialAutoplay;
		 this._initialAutoplaySet=false;//must be set after first play, not just load
		 this._inited = false;
		 this._player;
		 this._autoPlay = data.auto_play;
		 this._defaultVolume = data.default_volume;
		 this._youtubeHolder = data.youtubeHolder;
		 this._frameId='ytplayer'+Math.floor(Math.random()*0xFFFFFF);
		 if(data.quality) this.quality=data.quality;
		 if(data.small_embed) this.small_embed=data.small_embed;
		 //load/cue methods called before player ready
		 this.lastID;
		 this.playerReadyInterval=100;
		 this.playerReadyIntervalID;
		 this.playerReady=false;
		 this.protocol = data.protocol;
		 
		 var _youtubeChromeless = data.youtubeChromeless;
		 var zindexfix='&amp;wmode=transparent';
		 var youtubeApi='&amp;enablejsapi=1';
		 var no_controls='?controls=0';
		 var no_info='&amp;showinfo=0';
		 var theme='&amp;theme=dark';
		 var no_relVid = '&amp;rel=0';
		 var autoplay_on='&amp;autoplay=1';
		 var loop_on = '&amp;loop=1';
		 var videoIFrameSrc;
		 this.forceMainStop = false;
		 this.forcePreviewStop = false;
		 
		 this.isFirefox = testCSS('MozBoxSizing'); 
		 function testCSS(prop) {
			return prop in document.documentElement.style;
		 }
		 //console.log(this.isFirefox);
		 
		 if(!_youtubeChromeless){
			 no_relVid = '?rel=0';
			 videoIFrameSrc = this.protocol+'//www.youtube.com/embed/' + data.mediaPath + no_relVid + youtubeApi + theme + zindexfix;
		 }else{
			 videoIFrameSrc = this.protocol+'//www.youtube.com/embed/' + data.mediaPath + no_controls + no_info + youtubeApi + zindexfix;	
		 }
		  
		 this.youtubeVideoIframe = $('<iframe />', {
				frameborder: 0,
				src: videoIFrameSrc,
				width: 100 + '%',
				height: 100 + '%',
				id: this._frameId,
				webkitAllowFullScreen: true,
				mozallowfullscreen: true,
				allowFullScreen: true
		 });
		 //console.log(youtubeVideoIframe);
			
		 this._youtubeHolder.css('display', 'block').prepend(this.youtubeVideoIframe); 
		 
		var tag = document.createElement('script');
		tag.src = this.protocol+"//www.youtube.com/iframe_api";
		var firstScriptTag = document.getElementsByTagName('script')[0];
		firstScriptTag.parentNode.insertBefore(tag, firstScriptTag);
	 
		var interval = setInterval(function(){
			 if(window.YT && window.YT.Player){
				 if(interval) clearInterval(interval);
				 //console.log(window.YT, window.YT.Player);
				 _self._player = new YT.Player(_self._frameId, {
					events: {
						  'onReady': onPlayerReady,
						  'onPlaybackQualityChange': onPlayerPlaybackQualityChange,
						  'onStateChange': onPlayerStateChange,
						  'onError': onPlayerError
					}
				 });
				 
			 }
		 }, 100);
		 
		 window.onYouTubeIframeAPIReady = function() {
			//console.log('onYouTubeIframeAPIReady');
		 }
		 
		 function onPlayerReady(event) {
			//console.log('onPlayerReady');
			//console.log(_self._player);
			_self.playerReady=true;
			$(_self).trigger('ap_YoutubePlayer.YT_READY');
			if(typeof _self._player.setVolume !== "undefined"){
				_self._player.setVolume(_self._defaultVolume * 100);//Sets the volume. Accepts an integer between 0 and 100.
			}
			if(_self._autoPlay){
				_self._player.playVideo();
			}
		 }
		
		 function onPlayerPlaybackQualityChange(event) {
			//console.log('onPlayerPlaybackQualityChange: ', event.data);
			$(_self).trigger('ap_YoutubePlayer.QUALITY_CHANGE', [event.data]);
		 }
		
		 function onPlayerStateChange(event) {
			//console.log('onPlayerStateChange ', event.data);

			if(_self.forceMainStop){
				//console.log('_self.forceMainStop = ', _self.forceMainStop);
				_self.forceMainStop=false;
				if(typeof _self._player.stopVideo !== "undefined"){
					_self._player.stopVideo();
					$(_self).trigger('ap_YoutubePlayer.FORCE_MAIN_STOP');
					return;
				}
			}
		
			if(event.data == -1){//unstarted
			/*	if(typeof $.videoGallery !== "undefined" && typeof $.videoGallery.getVolume !== "undefined" && _self.isFirefox && typeof _self._player.setVolume !== "undefined")_self._player.setVolume($.videoGallery.getVolume() * 100);//firefox fix!*/
			}
			else if(event.data == 0){//ended
				$(_self).trigger('ap_YoutubePlayer.END_PLAY');
			}
			else if(event.data == 1){//playing
			
				_self._autoPlay=true;
			
				if(_self.small_embed && typeof _self._player.setVolume !== "undefined")_self._player.setVolume(0);//firefox fix in small preview!

				if(_self.forcePreviewStop){
					if(typeof _self._player.stopVideo !== "undefined")_self._player.stopVideo();
				}
				
				if(!_self._inited){
					$(_self).trigger('ap_YoutubePlayer.START_PLAY');
					_self._inited=true;	
					if(!_self.small_embed && _self.quality)_self._player.setPlaybackQuality(_self.quality);
					else if(_self.small_embed)_self._player.setPlaybackQuality(_self._player.getAvailableQualityLevels()[_self._player.getAvailableQualityLevels().length-1]);//set lowest available
				}
				
				$(_self).trigger('ap_YoutubePlayer.STATE_PLAYING');
			}
			else if(event.data == 2){//paused
				$(_self).trigger('ap_YoutubePlayer.STATE_PAUSED');
			}
			else if(event.data == 5){//paused
				$(_self).trigger('ap_YoutubePlayer.STATE_CUED');
			}
			
			/*
			
			YT.PlayerState.ENDED 0
			YT.PlayerState.PLAYING 1
			YT.PlayerState.PAUSED 2
			YT.PlayerState.BUFFERING 3
			YT.PlayerState.CUED 5
			
			-1 (unstarted)
			0 (ended)
			1 (playing)
			2 (paused)
			3 (buffering)
			5 (video cued).
			*/
		 }
		
		 function onPlayerError(e) {
			//console.log(e);
			
			/*
			event.data
			 2 – The request contains an invalid parameter value. For example, this error occurs if you specify a video ID that does not have 11 characters, or if the video ID contains invalid characters, such as exclamation points or asterisks.
			 5 – The requested content cannot be played in an HTML5 player or another error related to the HTML5 player has occurred.
			100 – The video requested was not found. This error occurs when a video has been removed (for any reason) or has been marked as private.
			101 – The owner of the requested video does not allow it to be played in embedded players.
			150 – This error is the same as 101. It's just a 101 error in disguise!
			*/
			
			switch(e.data){
				case 2:
				//console.log("Error code = "+e.data+": The request contains an invalid parameter value. For example, this error occurs if you specify a video ID that does not have 11 characters, or if the video ID contains invalid characters, such as exclamation points or asterisks.")
				break;
				case 100:
				//console.log("Error code = "+e.data+": Video not found, removed, or marked as private")
				break;
				case 101:
				//console.log("Error code = "+e.data+": Embedding disabled for this video")
				break;
				case 150:
				//console.log("Error code = "+e.data+": Video not found, removed, or marked as private [same as error 100]")
				break;
			}
			
			$(_self).trigger('ap_YoutubePlayer.ERROR_HANDLER', [e.data]);
			
		 }
		 
	 };
	 ap_YoutubePlayer.prototype = {
		clean:function(hide) {
			if(this._player){
				if(typeof this._player.stopVideo !== "undefined") this._player.stopVideo();
				this._player=null;
			}
			if(this.youtubeVideoIframe){
				this.youtubeVideoIframe.attr('src','');
				this.youtubeVideoIframe.remove();//SCRIPT5009: '__flash__removeCallback' is undefined 
				this.youtubeVideoIframe=null;
			}
			if(typeof hide !== "undefined")this._youtubeHolder.empty().css('display', 'none');
		},
		stopPreview:function() {
			this.forcePreviewStop=true;
		},
		initVideo:function(id, quality) {
			this.forceMainStop=false;//reset 
			this.quality = quality;
			if(this.playerReady){
				if(this._player){
					this.forcePreviewStop=false;//reset
					this._inited=false;
					//console.log(this._autoPlay);
					if(this._autoPlay){
						if(typeof this._player.loadVideoById !== "undefined")this._player.loadVideoById(id);
					}else{
						if(typeof this._player.cueVideoById !== "undefined")this._player.cueVideoById(id);
					}
				}
			}else{
				var _self=this;
				this.lastID = id;//remember last id
				if(this.playerReadyIntervalID)return;//if already started
				this.playerReadyIntervalID = setInterval(function(){
					if(_self.playerReady){
						if(_self.playerReadyIntervalID) clearInterval(_self.playerReadyIntervalID);
						if(!_self.forceMainStop)_self.initVideo(_self.lastID);
					}
				}, this.playerReadyInterval);
			}
		},
		stop:function() {
			this.forceMainStop=true;
			if(this._player && typeof this._player.stopVideo !== "undefined") this._player.stopVideo();
		},
		play:function() {
			if(this._player && typeof this._player.playVideo !== "undefined") this._player.playVideo();
		},
		pause:function() {
			if(this._player && typeof this._player.pauseVideo !== "undefined") this._player.pauseVideo();
		},
		togglePlayback:function(state) {
			if(this._player) {
				if(state == undefined){
					if(typeof this._player.getPlayerState === "undefined") return false;
					var player_state = this._player.getPlayerState();
					//console.log('player_state = ', player_state);
					if(player_state == 1){//playing
						if(typeof this._player.pauseVideo !== "undefined")this._player.pauseVideo();
					}else if(player_state == 2){//paused
						if(typeof this._player.playVideo !== "undefined")this._player.playVideo();
					}else if(player_state == -1 || player_state == 5 || player_state == 0){//unstarted, cued, ended
						if(typeof this._player.playVideo !== "undefined")this._player.playVideo();
					}
				}else{
					if(state){//start
						if(typeof this._player.playVideo !== "undefined")this._player.playVideo();
					}else{//stop
						if(typeof this._player.pauseVideo !== "undefined")this._player.pauseVideo();
					}
				}
			}
		},
		seek:function(val) {
			if(this._player && typeof this._player.seekTo !== "undefined") this._player.seekTo(val);
		},
		isMuted:function() {
			if(this._player && typeof this._player.isMuted !== "undefined") return this._player.isMuted();
		},
		getDuration:function() {
			if(this._player && typeof this._player.getDuration !== "undefined") return this._player.getDuration();
		},
		getCurrentTime:function() {
			if(this._player && typeof this._player.getCurrentTime !== "undefined") return this._player.getCurrentTime();
		},
		getVideoLoadedFraction:function() {
			if(this._player && typeof this._player.getVideoLoadedFraction !== "undefined") return this._player.getVideoLoadedFraction();
		},
		getVideoBytesLoaded:function() {
			if(this._player && typeof this._player.getVideoBytesLoaded !== "undefined") return this._player.getVideoBytesLoaded();
		},
		getVideoBytesTotal:function() {
			if(this._player && typeof this._player.getVideoBytesTotal !== "undefined") return this._player.getVideoBytesTotal();
		},
		setVolume:function(val) {
			//Sets the volume. Accepts an integer between 0 and 100.
			if(val<0) vol=0;
			else if(val > 1) val = 1;
			if(this._player && typeof this._player.setVolume !== "undefined") this._player.setVolume(val * 100);
		},
		getPlayerState:function() {
			if(this._player && typeof this._player.getPlayerState !== "undefined") return this._player.getPlayerState();
		},
		setAutoPlay:function(val) {
			this._autoPlay = val;
		},
		getQualityLevels:function() {
			return this._player.getAvailableQualityLevels();
		},
		getCurrQuality:function() {
			return this._player.getPlaybackQuality();
		},
		setPlaybackQuality:function(val) {
			//http://developers.google.com/youtube/js_api_reference#Playback_quality
			this._player.setPlaybackQuality(val);
		}
	}

})(jQuery);







(function($) {

	 $.playlistManager = function(data) {
		var pm = new ap_PlaylistManager(data);
		return pm;
	 };

	 function ap_PlaylistManager(settings) {
		
		this._loopingOn = settings.looping_on;
		this._randomPlay = settings.random_play;
		
		this._playlistItems;
		this._lastInOrder = false;
		this._counter = -1;
		this._lastPlayedFromPlaylistClick;//last played on click.
		this._lastRandomCounter;//last played random media in random playlist.
		this._randomPaused = false;//when random is playing and we interrupt it by click on the playlist.
		this._traceCounter = false;	
		this._randomArr = [];
		this._playlistSelect = false;//prevent geting counter from randomArr on playlist click (get 'normal' counter instead)
		
	 }
	 
	 ap_PlaylistManager.prototype = {
				 
		//set counter to specific number or add it to the currect counter value		 
		setCounter:function(value, _add) {
			if (typeof _add === 'undefined') _add = true;
			if(_add){
				this._counter += parseInt(value, 10);
			}else{
				this._counter = parseInt(value, 10);
			}
			//console.log('setCounter ', this._counter);
			this._checkCounter();
		},
		getCounter:function() {
			var i;
			if(this._randomPlay){
				if(!this._playlistSelect){
					i = this._randomArr[this._counter];
				}else{
					i = this._counter;
				}
			}else{
				i = this._counter;
			}
			return i;
		},
		advanceHandler:function(a) {
			this._playlistSelect = false;//reset
			if(this._randomPaused){
				this._handleRandomPaused(a);
			}else{
				this.setCounter(a);
			}
		},
		processPlaylistRequest:function(id) {
			this._playlistSelect = false;//reset
			if(this._randomPlay){
				this._playlistSelect = true;
				this._lastPlayedFromPlaylistClick = id;//always remember last played on each click.
				if(!this._randomPaused){
					this._lastRandomCounter = this._counter;
					//console.log("memory = " + _lastRandomCounter);
					this._randomPaused = true;//this needs to stay until random play comes back again! So that the above reference to last random counter doesnt get lost. (if we constantly clicking playlist)
				}
			}
			this.setCounter(id, false);
		},
		getLastInOrder:function() {
			return this._lastInOrder;
		},
		getRandomPaused:function() {
			return this._randomPaused;
		},
		setPlaylistItems:function(val, resetCounter) {
			if(typeof resetCounter === 'undefined') resetCounter = true;
			if(resetCounter)this._counter = -1;
			this._playlistItems = val;
			if(this._randomPlay) this._makeRandomList();
		},
		reSetCounter:function(num) {
			if(typeof num === 'undefined'){
				 this._counter = -1;
			}else{//set counter to specific number
				var n = parseInt(num,10);
				if(this._playlistItems){
					if(n > this._playlistItems - 1){
						n = this._playlistItems - 1;
					}else if(n < 0){
						n = 0;
					}
					this._counter = n;
				}else{
					this._counter = -1;
				}
			}
		},
		setRandom:function(val) {
			this._randomPlay = val;
			if(this._randomPlay) this._makeRandomList();
			this._randomChange();
		},
		setLooping:function(val) {
			this._loopingOn = val;
		},
		setTraceCounter:function(val) {
			this._traceCounter = val;
		},
		
		//******PRIVATE
		//exiting _randomPaused and going back to random mode
		_handleRandomPaused:function(a) {
			//this is just an exit out of _randomPaused (because of a playlist click) and back to random again
			//console.log("handleRandomPaused");
			//console.log("_lastRandomCounter ", _lastRandomCounter);
			var self = $(this);
			this._randomPaused = false;//reset before because of the getCounter()
			
			if(this._lastRandomCounter + a > this._playlistItems - 1){
				this._counter = this._playlistItems - 1;
				//trace("end");
				self.trigger('ap_PlaylistManager.COUNTER_READY');
				return;
			} else if( this._lastRandomCounter + a < 0){
				this._counter = 0;
				//trace("beginning");
				self.trigger('ap_PlaylistManager.COUNTER_READY');
				return;
			}
			this.setCounter(this._lastRandomCounter + a, false);
		},
		_randomChange:function() {//when random is turned on / off
			//console.log('randomChange');
			if(this._randomPlay){
				this._activeIndexFirst();
				this._counter = 0;//we have to do it like this, because with (setCounter(0, false)) media starts to play from the beginning if its already playing. (when random requested)
				//we need to say this on the every beginning of random to redirect the counter from wherever the currently is to 0, so that it becomes first index in randomArr. (after we have moved active index to beginning of randomArr)
				
			}else{
				//we are not going through setCounter here because its just getting out of random mode, and its not changing counter, it just stays where it is (playing or not)
				if(this._randomPaused){
					this._counter = this._lastPlayedFromPlaylistClick;
					this._randomPaused = false;//when random mode stops _randomPaused stops also.
				}else{
					this._counter = this._randomArr[this._counter];//when we turn off random we need to set counter to the value of the current counter in randomArr, so if the counter is 1, and thats value 3 in randomArr for example, we want the active counter to stay 3, not 1, and next to go to 4, not 2.
				}
			}
		},
		_checkCounter:function() {
			//console.log('_checkCounter');
			if(isNaN(this._counter)){
				alert('ap_PlaylistManager message: No active media, counter = ' + this._counter);
				return;
			}
			//reset
			var self = $(this);
			this._lastInOrder = false;
			
			if(this._loopingOn){
				if(this._randomPlay){
					
					if(this._counter > this._playlistItems - 1){//moving fowards
						this._counter = this._randomArr[ this._playlistItems - 1];//remember counter for comparison
						this._makeRandomList();
						this._firstIndexCheck();
						this._counter = 0;
						self.trigger('ap_PlaylistManager.PLAYLIST_END_ALERT');
						
					}else if(this._counter < 0){//moving backwards
						this._counter = this._randomArr[0];//remember counter for comparison
						this._makeRandomList();
						this._lastIndexCheck();
						this._counter = this._playlistItems - 1;
					}
					
				}else{//random off
					if(this._counter > this._playlistItems - 1){
						this._counter = 0;
						self.trigger('ap_PlaylistManager.PLAYLIST_END_ALERT');
					}else if( this._counter < 0){
						this._counter = this._playlistItems - 1;
					}
				}
				
				self.trigger('ap_PlaylistManager.COUNTER_READY');
				
			}else{//looping off
				
				if(this._counter > this._playlistItems - 1){
					this._counter = this._playlistItems - 1;
					this._lastInOrder = true;//last item
					//console.log("last item");
				}else if(this._counter < 0){
					this._counter = 0;
					//console.log("first item");
				}
				
				if(!this._lastInOrder){
					self.trigger('ap_PlaylistManager.COUNTER_READY');
				}else{
					self.trigger('ap_PlaylistManager.PLAYLIST_END');
				}
			}
			
			if(this._traceCounter) console.log("counter = " + this.getCounter());
		},
		//make random set of numbers
		_makeRandomList:function() {
			if(this._playlistItems < 3) return;
			this._randomArr = this._randomiseIndex(this._playlistItems);
			//console.log('_randomArr = ', this._randomArr);
		},
		_firstIndexCheck:function() {
			//we need to check that first item in newly generated random array isnt equal to last active item.
			if(this._randomArr[0] == this._counter){//if yes, put it at the last place in array.
				var i = this._randomArr.splice(0,1);
				this._randomArr.push(i);
				//console.log("firstIndexCheck " + _randomArr);
			}
		},
		_lastIndexCheck:function() {
			if(this._randomArr[this._playlistItems - 1] == this._counter){//if yes, put it at the first place in array.
				var i = this._randomArr.splice(this._playlistItems - 1,1);
				this._randomArr.unshift(i);
				//console.log("lastIndexCheck " + _randomArr);
			}
		},
		_activeIndexFirst:function() {//when going into random (playing or not) put currently active index on the first place of random array.
			//console.log("activeIndexFirst");
			var i = 0,len = this._randomArr.length, j;
			
			for(i; i < len; i++){
				
				if(this._randomArr[i] == this._counter){
					if(i == 0){//if its already on the first place no need for action.
						break;
					}
					j = this._randomArr.splice(i,1);
					//console.log('_randomArr = ', this._randomArr);
					//console.log(i,j);
					this._randomArr.unshift(parseInt(j,10));
					break;
				}
			}
			//console.log(this._randomArr);
		},
		_randomiseIndex:function(num) {
			var arr = [],randomArr = [],i = 0;
			
			for (i; i < num; i++) {//first fill the ordered set of indexes
				arr[i] = i;
			}
			
			var j = 0, randomIndex;
			for (j; j < num; j++) { //then randomize those indexes
				randomIndex = Math.round(Math.random()*(arr.length-1));
				randomArr[j] = arr[randomIndex];
				arr.splice(randomIndex, 1);
			}
			return randomArr;
		}

	}


})(jQuery);	



var vplp_tracks_list = [
	 '<div class="playlistNonSelected" data-address="local1" data-type="local" data-mp4="../media/video/1/main/01.mp4" data-preview="../media/video/1/main/01.jpg" data-thumb="../media/video/1/preview/01.jpg" data-video-preview="../media/video/1/preview/01.mp4"></div>',
	 '<div class="playlistNonSelected" data-address="local2" data-type="local" data-mp4="../media/video/1/main/02.mp4" data-preview="../media/video/1/main/02.jpg" data-thumb="../media/video/1/preview/02.jpg" data-video-preview="../media/video/1/preview/02.mp4"></div>'
];

var vplp_tracks_list2 = [
	 '<div class="playlistNonSelected" data-address="youtube_single1" data-type="youtube_single" data-path="F08U2yCxbYg" ></div>',
	 '<div class="playlistNonSelected" data-address="youtube_single2" data-type="youtube_single" data-path="jXSxzMTrKq0" ></div>',
	 '<div class="playlistNonSelected" data-address="youtube_single3" data-type="youtube_single" data-path="NY-4XAQR_uk" ></div>'
];




	
	


//******** flash callabcks
function isReady() {return document.readyState === "complete"}
/*main flash callbacks*/
function flashVideoEnd(data){
	if(typeof(vplp_mediaArr) !== 'undefined' && vplp_mediaArr[data.id])vplp_mediaArr[data.id].player_id.flashVideoEnd();
}
function flashVideoStart(data){
	if(typeof(vplp_mediaArr) !== 'undefined' && vplp_mediaArr[data.id]){
		vplp_mediaArr[data.id].player_id.flashVideoStart();
		if(data.cc && data.quality){//only yt
			vplp_mediaArr[data.id].player_id.checkQuality('youtube_flash', data.cc, data.quality);
		}
	}
}
function flashVideoPause(data){
	if(typeof(vplp_mediaArr) !== 'undefined' && vplp_mediaArr[data.id])vplp_mediaArr[data.id].player_id.flashVideoPause();
}
function flashVideoResume(data){
	if(typeof(vplp_mediaArr) !== 'undefined' && vplp_mediaArr[data.id])vplp_mediaArr[data.id].player_id.flashVideoResume();
}
function dataUpdateFlash(data){
	if(typeof(vplp_mediaArr) !== 'undefined' && vplp_mediaArr[data.id])vplp_mediaArr[data.id].player_id.dataUpdateFlash(data.bl,data.bt,data.t,data.d);
}
function flashYtQualityChange(data){
	if(typeof(vplp_mediaArr) !== 'undefined' && vplp_mediaArr[data.id])vplp_mediaArr[data.id].player_id.flashYtQualityChange(data.cc);
}
/*preview flash callbacks*/
function flashPreviewVideoStart(data){
	if(typeof(vplp_mediaArr) !== 'undefined' && vplp_mediaArr[data.id])vplp_mediaArr[data.id].player_id.flashPreviewVideoStart();
}
















/* POPUP */
var hap_popup;
function notify_popup(type){//called from popup window when popup window has opened!
	//console.log('notify_popup');
	if(hap_popup && hap_popup.initPopup != undefined){//dont do anything if we are not going to be able to open popup!
		try {
			if(hap_popup.initPopup != undefined){
			
				var settings = hap_player.getSettings();
				settings.active_playlist = hap_player.getActivePlaylist();//copy active playlist, active item

				if(isIE){
					var pl = (hap_player.getPlaylistList().clone(true, true).wrap('<p>').parent().html());//HIERARCHY_REQUEST_ERROR!!
				}else{
					var pl = hap_player.getPlaylistList().clone(true, true);
				}
			
				if(type == 'wall_popup'){
					hap_popup.initPopup(pl, settings);
				}else if(type == 'list_popup'){
					settings.active_item = hap_player.getActiveItem();
					hap_popup.initPopup(pl, settings);
				}
			}
		}catch(e){
			alert('parent notify_popup error: ' + e.message);
			return false;
		}
	}
}

function open_popup(settings, ai){
	//console.log('open_popup');
	
	var url = settings.popup_url, w = settings.popup_width, h = settings.popup_height, 
	cw = (window.screen.width - w) / 2, ch = (window.screen.height - h) / 2;//center popup in window
	
	//if popup window not already opened!
	if(!hap_popup || hap_popup.closed) {
		hap_popup=window.open(url,'hap_popup_window','menubar=no,toolbar=no,location=no,scrollbars=1,resizable,width='+w+',height='+h+',left='+cw+',top='+ch+'');

		if(!hap_popup) {
			alert("Player can not be opened in a popup window because your browser is blocking Pop-Ups. You need to allow Pop-Ups in browser for this site to use the Player.");
			return false;
		}
		if (window.focus) {hap_popup.focus();}
	}else{
		//console.log('popup already opened!');	
		//play video in popup
		if(hap_popup.loadMedia != undefined)hap_popup.loadMedia(ai);
	}
	return false;
}

//called from popup window when popup is closed
function open_player(ap, ai){
	jQuery('#mainWrapper').css('display', 'block');
	jQuery('.popup_toggle').show();
	hap_player.getSettings().active_item = ai;
	hap_player.loadMedia(ap);
}






	
	