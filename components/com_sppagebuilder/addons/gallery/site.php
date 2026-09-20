<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2026 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

use Joomla\CMS\Uri\Uri;

//no direct access
defined('_JEXEC') or die('Restricted access');

class SppagebuilderAddonGallery extends SppagebuilderAddons
{
	/**
	 * The addon frontend render method.
	 * The returned HTML string will render to the frontend page.
	 *
	 * @return  string  The HTML string.
	 * @since   1.0.0
	 */
	public function render()
	{
		$settings = $this->addon->settings;
		$addon_id = 'sppb-addon-' . $this->addon->id;
		$class = (isset($settings->class) && $settings->class) ? $settings->class : '';
		$title = (isset($settings->title) && $settings->title) ? $settings->title : '';
		$heading_selector = (isset($settings->heading_selector) && $settings->heading_selector) ? $settings->heading_selector : 'h3';
		$item_alignment = (isset($settings->item_alignment) && $settings->item_alignment) ? $settings->item_alignment : '';
		$item_alignment = AddonUtils::parseDeviceData($item_alignment, SpPgaeBuilderBase::$defaultDevice);
		$show_thumb_desc = (isset($settings->show_thumb_desc) && $settings->show_thumb_desc) ? $settings->show_thumb_desc : 0;
		$show_full_desc = (isset($settings->show_full_desc) && $settings->show_full_desc) ? $settings->show_full_desc : 0;
		$show_desc_on_hover = (isset($settings->show_desc_on_hover) && $settings->show_desc_on_hover) ? $settings->show_desc_on_hover : 0;

		$output  = '<div class="sppb-addon sppb-addon-gallery ' . $class . '">';
		$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';
		$output .= '<div class="sppb-addon-content">';
		$output .= '<ul class="sppb-gallery clearfix gallery-item-' . $item_alignment . '">';

		if (isset($settings->sp_gallery_item) && count((array) $settings->sp_gallery_item))
		{
			foreach ($settings->sp_gallery_item as $key => $value)
			{
				if (isset($value->item_visibility) && !$value->item_visibility) {
					continue;
				}
				$media_type = isset($value->media_type) && $value->media_type ? $value->media_type : 'image';

				if($media_type === 'video') {
					$description = isset($value->description) && $value->description ? $value->description : '';
					$title_attr = $show_full_desc && $description ? 'data-title="<div id=' . $addon_id . '><p class=sppb-gallery-desc>' . $description . '</p></div>"' : 'data-title=""';
					$video_src = isset($value->video) && $value->video ? $this->resolveMediaSrc($value->video) : '';
					$scheme = parse_url($video_src, PHP_URL_SCHEME);

					if ($scheme && !in_array(strtolower($scheme), ['http', 'https'], true)) {
						$video_src = '';
					}

					$video_src = htmlspecialchars($video_src, ENT_QUOTES, 'UTF-8');
					
					$video_poster = isset($value->video_poster) && $value->video_poster ? $value->video_poster : (isset($value->thumb) ? $value->thumb : '');
					$video_poster_src = $this->resolveMediaSrc($video_poster);
					$video_preview_src = $video_poster_src ? $video_poster_src : $this->resolveYoutubeThumb($video_src);
					$video_preview_src = $video_preview_src ? $video_preview_src : $this->resolveVimeoThumb($video_src);
					$video_poster_alt = isset($video_poster->alt) ? $video_poster->alt : (isset($value->title) ? $value->title : '');
					$video_poster_width = isset($video_poster->width) && $video_poster->width ? $video_poster->width : '';
					$video_poster_height = isset($video_poster->height) && $video_poster->height ? $video_poster->height : '';
					$video_width = isset($value->video->width) && $value->video->width ? $value->video->width : '';
					$video_height = isset($value->video->height) && $value->video->height ? $value->video->height : '';

					if (!empty($video_src))
					{
						$video_placeholder = $video_preview_src ? $this->get_image_placeholder($video_preview_src) : false;

						$output .= '<li>';
						$output .= '<a ' . ($show_desc_on_hover ? 'style="position: relative;" ' : '') . 'href="' . $video_src . '" class="sppb-gallery-btn sppb-gallery-video-btn gallery-item-' . $key . '" data-mfp-type="iframe">';

						if (!empty($video_preview_src))
						{
							$output .= '<img ' . $title_attr . ' class="sppb-img-responsive ' . ($video_placeholder ? ' sppb-element-lazy' : '') . '" src="' . ($video_placeholder ? $video_placeholder : $video_preview_src) . '" alt="' . $video_poster_alt . '" ' . ($video_placeholder ? 'data-large="' . $video_preview_src . '"' : '') . ' ' . ($video_poster_width ? 'width="' . $video_poster_width . '"' : '') . ' ' . ($video_poster_height ? 'height="' . $video_poster_height . '"' : '') . ' loading="lazy">';
						}
						else
						{
							$output .= '<video ' . $title_attr . ' class="sppb-img-responsive sppb-gallery-video-preview" src="' . $video_src . '" ' . ($video_width ? 'width="' . $video_width . '"' : '') . ' ' . ($video_height ? 'height="' . $video_height . '"' : '') . ' preload="metadata" muted playsinline></video>';
						}

						$output .= '</a>';

						if ($show_thumb_desc && $description)
						{
							$output .= '<p class="sppb-gallery-desc">' . $description . '</p>';
						}

						$output .= '</li>';
					}
				} else {
					$thumb_img = isset($value->thumb) && $value->thumb ? $value->thumb : '';
					$thumb_src = isset($thumb_img->src) ? $thumb_img->src : $thumb_img;
					$alt_text_fallback = isset($value->title) ? $value->title : '';
					$alt_text = isset($thumb_img->alt) ? $thumb_img->alt : $alt_text_fallback;
					$thumb_width = isset($thumb_img->width) && $thumb_img->width ? $thumb_img->width : '';
					$thumb_height = isset($thumb_img->height) && $thumb_img->height ? $thumb_img->height : '';
					$description = isset($value->description) && $value->description ? $value->description : '';

					$full_img = isset($value->full) && $value->full ? $value->full : '';
					$full_src = isset($full_img->src) ? $full_img->src : $full_img;

					if (!is_string($thumb_src)) {
						$thumb_src = '';
					}

					if (!empty($thumb_src))
					{
						if (strpos($thumb_src, "http://") !== false || strpos($thumb_src, "https://") !== false)
						{
							$thumb_src = $thumb_src;
						}
						else
						{
							$thumb_src = Uri::base(true) . '/' . $thumb_src;
						}

						$placeholder = $thumb_src == '' ? false : $this->get_image_placeholder($thumb_src);
						$title = $show_full_desc && isset($value->description) && $value->description ?  'data-title="<div id='. $addon_id . '>' . '<p class=sppb-gallery-desc>' . $description . '</p>' . '</div>"' : 'data-title=""';

						$output .= '<li>';
						$output .= ($full_src) ? '<a '. ($show_desc_on_hover ? 'style="position: relative;" ' : '') . 'href="' . $full_src . '" class="sppb-gallery-btn gallery-item-' . $key . '">' : '';
						$output .= '<img ' . $title . ' class="sppb-img-responsive ' . ($placeholder ? ' sppb-element-lazy' : '') . '" src="' . ($placeholder ? $placeholder : $thumb_src) . '" alt="' . $alt_text . '" ' . ($placeholder ? 'data-large="' . $thumb_src . '"' : '') . ' ' . ($thumb_width ? 'width="' . $thumb_width . '"' : '') . ' ' . ($thumb_height ? 'height="' . $thumb_height . '"' : '') . ' loading="lazy">';
						$output .= ($full_src) ? '</a>' : '';

						if($show_thumb_desc && $description)
						{
							$output .= '<p class="sppb-gallery-desc">' . $description . '</p>';
						}

						$output .= '</li>';
					}
				}
			}
		}

		$output .= '</ul>';
		$output .= '</div>';
		$output .= '</div>';

		return $output;
	}

	private function resolveMediaSrc($media)
	{
		$src = isset($media->src) ? $media->src : $media;

		if (!is_string($src) || empty($src)) {
			return '';
		}

		if (strpos($src, 'http://') === 0 || strpos($src, 'https://') === 0 || strpos($src, '//') === 0) {
			return $src;
		}

		return Uri::base(true) . '/' . ltrim($src, '/');
	}

	private function resolveYoutubeThumb($url)
	{
		if (!is_string($url) || empty($url)) {
			return '';
		}

		$normalizedUrl = strpos($url, '//') === 0 ? 'https:' . $url : $url;
		$videoParts = parse_url($normalizedUrl);

		if (!isset($videoParts['host'])) {
			return '';
		}

		$host = strtolower($videoParts['host']);
		$path = isset($videoParts['path']) ? trim($videoParts['path'], '/') : '';
		$videoId = '';

		if ($host === 'youtu.be') {
			$segments = explode('/', $path);
			$videoId = isset($segments[0]) ? $segments[0] : '';
		} elseif (strpos($host, 'youtube.com') !== false) {
			if ($path === 'watch') {
				$query = [];
				parse_str(isset($videoParts['query']) ? $videoParts['query'] : '', $query);
				$videoId = isset($query['v']) ? $query['v'] : '';
			} elseif (strpos($path, 'shorts/') === 0 || strpos($path, 'embed/') === 0) {
				$segments = explode('/', $path);
				$videoId = isset($segments[1]) ? $segments[1] : '';
			}
		}

		$videoId = preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $videoId);

		if (empty($videoId)) {
			return '';
		}

		return 'https://img.youtube.com/vi/' . $videoId . '/hqdefault.jpg';
	}

	private function resolveVimeoThumb($url)
	{
		if (!is_string($url) || empty($url)) {
			return '';
		}

		$normalizedUrl = strpos($url, '//') === 0 ? 'https:' . $url : $url;
		$videoParts = parse_url($normalizedUrl);

		if (!isset($videoParts['host'])) {
			return '';
		}

		$host = strtolower($videoParts['host']);

		if (strpos($host, 'vimeo.com') === false) {
			return '';
		}

		$path = isset($videoParts['path']) ? trim($videoParts['path'], '/') : '';
		$segments = array_reverse(explode('/', $path));
		$videoId = '';

		foreach ($segments as $segment) {
			if (ctype_digit($segment)) {
				$videoId = $segment;
				break;
			}
		}

		if (empty($videoId)) {
			return '';
		}

		return 'https://vumbnail.com/' . $videoId . '.jpg';
	}

	/**
	 * Attach inline stylesheet.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	public function stylesheets()
	{
		return array(Uri::base(true) . '/components/com_sppagebuilder/assets/css/magnific-popup.css');
	}

	/**
	 * Attach external scripts.
	 *
	 * @return  array
	 * @since   1.0.0
	 */
	public function scripts()
	{
		return array(Uri::base(true) . '/components/com_sppagebuilder/assets/js/jquery.magnific-popup.min.js');
	}

	/**
	 * Attach inline JavaScript.
	 *
	 * @return  string  The JS string.
	 * @since   1.0.0
	 */
	public function js()
	{
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$js = 'jQuery(function($){
			$("' . $addon_id . ' ul li").magnificPopup({
				delegate: "a",
				mainClass: "mfp-no-margins mfp-with-zoom",
				gallery:{
					enabled:true
				},
				image: {
					verticalFit: true,
					titleSrc: function(item) {
						return item.el.find("img,video").first().data("title");
					}
				},
				callbacks: {
					elementParse: function(item) {
						item.type = item.el.attr("data-mfp-type") || "image";

						if (item.type === "iframe") {
						var url = item.el.attr("href");

						if (url && /youtube\.com\/shorts\//i.test(url)) {
								var match = url.match(/youtube\.com\/shorts\/([^?&#/]+)/i);

								if (match && match[1]) {
									item.src = "https://www.youtube.com/embed/" + match[1];
								}
							}
						}
					},
					markupParse: function(template, values, item) {
						if (item.type === "iframe") {
							values.title = item.el.find("img,video").first().data("title") || "";
						}
					}
				},
				iframe: {
					markup:
						\'<div class="mfp-iframe-scaler">\' +
						\'<div class="mfp-close"></div>\' +
						\'<iframe class="mfp-iframe" frameborder="0" allowfullscreen></iframe>\' +
						\'<div class="mfp-bottom-bar" style="margin-top:0;">\' +
						\'<div class="mfp-title"></div>\' +
						\'<div class="mfp-counter"></div>\' +
						\'</div>\' +
						\'</div>\',
					patterns: {
						youtube: {
							index: "youtube.com/",
							id: function(url) {
								var match = url.match(/youtube\.com\/(?:watch\?v=|shorts\/|embed\/)([^?&#/]+)/i);

								if (match && match[1]) {
									return match[1];
								}

								return null;
							},
							src: "https://www.youtube.com/embed/%id%?autoplay=1&rel=0"
						}
					}
				},
				zoom: {
					enabled: true,
					duration: 300
				}
			});
		})';

		return $js;
	}

	/**
	 * Generate the CSS string for the frontend page.
	 *
	 * @return 	string 	The CSS string for the page.
	 * @since 	1.0.0
	 */
	public function css()
	{
		$settings = $this->addon->settings;
		$addon_id = '#sppb-addon-' . $this->addon->id;
		$cssHelper = new CSSHelper($addon_id);

		$css = '';

		$border_radius = (isset($settings->border_radius) && $settings->border_radius) ? $settings->border_radius : 0;

		if ($border_radius) {
			$border_radius = explode(" ", $settings->border_radius);
		}

		$galleryImageDefaultStyle = '';

		if (!isset($settings->width) || empty($settings->width)) {
			$galleryImageDefaultStyle = $cssHelper->generateStyle('.sppb-gallery img, .sppb-gallery video', $settings, [], false, [], null, false, 'width: auto;');
		}

		if (is_array($border_radius) && (count($border_radius) > 2)) {
			$galleryImageStyle = $cssHelper->generateStyle('.sppb-gallery img, .sppb-gallery video', $settings, ['width' => 'width', 'height' => 'height', 'border_radius' => 'border-radius'],
			[
				'border_radius' => false
			],
			[
				'border_radius' => 'spacing'
			]);
		} else {
			$galleryImageStyle = $cssHelper->generateStyle('.sppb-gallery img, .sppb-gallery video', $settings, ['width' => 'width', 'height' => 'height', 'border_radius' => 'border-radius']);
		}

		$galleryStyle = $cssHelper->generateStyle('.sppb-gallery', $settings, ['item_gap' => 'margin: -%s', 'item_alignment' => 'justify-content'], ['item_alignment' => false]);
		$galleryItemStyle = $cssHelper->generateStyle('.sppb-gallery li', $settings, ['item_gap' => 'margin']);
		$transformCss = $cssHelper->generateTransformStyle('.sppb-gallery', $settings, 'transform');
		$descriptionAlignment = $cssHelper->generateStyle('.sppb-gallery-desc', $settings, ['description_alignment' => 'text-align'], false);
		$descriptionWidth = $cssHelper->generateStyle('.sppb-gallery-desc', $settings, ['width' => 'max-width'], 'px');

		$show_desc_on_hover = (isset($settings->show_desc_on_hover) && $settings->show_desc_on_hover) ? $settings->show_desc_on_hover : 0;

		if($show_desc_on_hover){
			foreach($settings->sp_gallery_item as $key => $value){
				if (isset($value->item_visibility) && !$value->item_visibility) {
					continue;
				}
				$description = isset($value->description) && $value->description ? $value->description : '';
				if($description){
					$css .= $cssHelper->generateStyle('.gallery-item-'.$key.'::after', $settings, ['description_alignment' => 'text-align', 'width' => 'width', 'border_radius' => 'border-radius'], ['description_alignment' => false, 'width' => 'px', 'border_radius' => 'px'], false, null, false, 'content: "' . $description . '"; position: absolute; top: 0; left: 0; right: 0; bottom: 0; display: flex; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.5); color: #fff; opacity: 0; transition: opacity 0.3s;');
					$css .= $addon_id.' .gallery-item-'.$key.':hover::after { opacity: 1; }';
				}
			}
		}

		$descTypography = $cssHelper->typography('.sppb-gallery-desc, .sppb-gallery-btn', $settings, 'description_typography', [
							'font' => 'description_font_family',
							'size' => 'description_fontsize',
							'line_height' => 'description_lineheight',
							'letter_spacing' => 'description_letterspace',
							'uppercase' => 'description_font_style.uppercase',
							'italic' => 'description_font_style.italic',
							'underline' => 'description_font_style.underline',
							'weight' => 'description_font_style.weight',
						]);
		

		$css .= $galleryStyle;
		$css .= $galleryItemStyle;
		$css .= $galleryImageDefaultStyle;
		$css .= $galleryImageStyle;
		$css .= $transformCss;
		$css .= $descriptionAlignment;
		$css .= $descriptionWidth;
		$css .= $descTypography;

		return $css;
	}

	/**
	 * Generate the lodash template string for the frontend editor.
	 *
	 * @return 	string 	The lodash template string.
	 * @since 	1.0.0
	 */
	public static function getTemplate()
	{

		$lodash = new Lodash('#sppb-addon-{{ data.id }}');

		$output = '<style type="text/css">';

		$output .= $lodash->alignment('justify-content', '.sppb-gallery', 'data.item_alignment');

		$output .= $lodash->alignment('text-align', '.sppb-gallery-desc', 'data.description_alignment');
		$output .= $lodash->unit('max-width', '.sppb-gallery-desc', 'data.width', 'px');

		$output .= $lodash->unit('width', '.sppb-gallery img, .sppb-gallery video', 'data.width', 'px');
		$output .= $lodash->unit('height', '.sppb-gallery img, .sppb-gallery video', 'data.height', 'px');

		$output .= '<# if((data.border_radius + "").split(" ").length < 2) { #>';
		$output .= $lodash->unit('border-radius', '.sppb-gallery img, .sppb-gallery video', 'data.border_radius', 'px');
		$output .= '<# } else { #>';
		$output .= '.sppb-gallery img, .sppb-gallery video {
			{{window.getSplitRadius(data.border_radius)}}	
		}';
		$output .= '<# } #>';

		$output .= $lodash->unit('margin', '.sppb-gallery li', 'data.item_gap', 'px');
		$output .= $lodash->unit('margin', '.sppb-gallery', 'data.item_gap', 'px', true, '-');

		// Title
		$titleTypographyFallbacks = [
			'font'           => 'data.title_font_family',
			'size'           => 'data.title_fontsize',
			'line_height'    => 'data.title_lineheight',
			'letter_spacing' => 'data.title_letterspace',
			'uppercase'      => 'data.title_font_style?.uppercase',
			'italic'         => 'data.title_font_style?.italic',
			'underline'      => 'data.title_font_style?.underline',
			'weight'         => 'data.title_font_style?.weight',
		];

		$descriptionTypographyFallbacks = [
			'font'           => 'data.description_font_family',
			'size'           => 'data.description_fontsize',
			'line_height'    => 'data.description_lineheight',
			'letter_spacing' => 'data.description_letterspace',
			'uppercase'      => 'data.description_font_style?.uppercase',
			'italic'         => 'data.description_font_style?.italic',
			'underline'      => 'data.description_font_style?.underline',
			'weight'         => 'data.description_font_style?.weight',
		];

		$output .= $lodash->typography('.sppb-addon-title', 'data.title_typography', $titleTypographyFallbacks);
		$output .= $lodash->typography('.sppb-gallery-desc', 'data.description_typography', $descriptionTypographyFallbacks);

		$output .= $lodash->unit('margin-top', '.sppb-addon-title', 'data.title_margin_top', 'px');
		$output .= $lodash->unit('margin-bottom', '.sppb-addon-title', 'data.title_margin_bottom', 'px');
		$output .= $lodash->generateTransformCss('.sppb-gallery', 'data.transform');

		$output .= '
		</style>

		<div class="sppb-addon sppb-addon-gallery {{ data.class }}">

		<# function resolveMediaSrc(media) {

							if (!media) {
								return "";
							}

							let src =
								typeof media.src !== "undefined"
									? media.src
									: media;

							if (typeof src !== "string" || !src) {
								return "";
							}

							if (
								src.startsWith("http://") ||
								src.startsWith("https://") ||
								src.startsWith("//")
							) {
								return src;
							}

							return pagebuilder_base + src;
						}


						function resolveYoutubeThumb(url) {

							if (typeof url !== "string" || !url) {
								return "";
							}

							let normalizedUrl =
								url.startsWith("//")
									? "https:" + url
									: url;

							let videoParts;

							try {
								videoParts = new URL(normalizedUrl);
							} catch (e) {
								return "";
							}

							let host =
								videoParts.hostname.toLowerCase();

							let path =
								videoParts.pathname.replace(
									/^\/+|\/+$/g,
									""
								);

							let videoId = "";


							if (host === "youtu.be") {

								let segments = path.split("/");

								videoId = segments[0] || "";

							} else if (
								host.indexOf("youtube.com") !== -1
							) {

								if (path === "watch") {

									videoId =
										new URLSearchParams(
											videoParts.search
										).get("v") || "";

								} else if (
									path.indexOf("shorts/") === 0 ||
									path.indexOf("embed/") === 0
								) {

									let segments = path.split("/");

									videoId =
										segments[1] || "";
								}
							}


							videoId =
								videoId.replace(
									/[^a-zA-Z0-9_-]/g,
									""
								);


							if (!videoId) {
								return "";
							}


							return (
								"https://img.youtube.com/vi/" +
								videoId +
								"/hqdefault.jpg"
							);
						}


						function resolveVimeoThumb(url) {

							if (typeof url !== "string" || !url) {
								return "";
							}

							let normalizedUrl =
								url.startsWith("//")
									? "https:" + url
									: url;

							let videoParts;

							try {
								videoParts = new URL(normalizedUrl);
							} catch (e) {
								return "";
							}

							let host =
								videoParts.hostname.toLowerCase();

							if (
								host.indexOf("vimeo.com") === -1
							) {
								return "";
							}

							let path =
								videoParts.pathname.replace(
									/^\/+|\/+$/g,
									""
								);

							let segments = path.split("/").reverse();

							let videoId = "";

							for (let segment of segments) {

								if (/^\d+$/.test(segment)) {
									videoId = segment;
									break;
								}
							}

							if (!videoId) {
								return "";
							}

							return (
								"https://vumbnail.com/" +
								videoId +
								".jpg"
							);
						}
#>

			<# if( !_.isEmpty( data.title ) ){ #>
				<{{ data.heading_selector }}
					class="sppb-addon-title sp-inline-editable-element"
					data-id={{data.id}}
					data-fieldName="title"
					contenteditable="true"
				>{{ data.title }}</{{ data.heading_selector }}>
			<# } #>

			<div class="sppb-addon-content">

				<ul class="sppb-gallery clearfix gallery-item-{{data.item_alignment}}">

					<#
					_.each(data.sp_gallery_item, function (value, key) {

						if(typeof value.item_visibility !== "undefined" && !value.item_visibility){
							return;
						}

						let mediaType =
							typeof value.media_type !== "undefined" && value.media_type
								? value.media_type
								: "image";

						let videoSource =
							resolveMediaSrc(value.video);

						let videoPoster = "";
						if (value.video_poster) {

							videoPoster =
								resolveMediaSrc(
									value.video_poster
								);
						}

						if (!videoPoster && videoSource) {

							videoPoster =
								resolveYoutubeThumb(
									videoSource
								);
						}

						if (!videoPoster && videoSource) {

							videoPoster =
								resolveVimeoThumb(
									videoSource
								);
						}


						var thumbImg = {};
						var fullImg = {};

						var desc =
							data.show_thumb_desc &&
							value.description;

						if (
							typeof value.thumb !== "undefined" &&
							typeof value.thumb.src !== "undefined"
						) {

							thumbImg = value.thumb;

						} else {

							thumbImg = {
								src: value.thumb
							};
						}


						if (
							typeof value.full !== "undefined" &&
							typeof value.full.src !== "undefined"
						) {

							fullImg = value.full;

						} else {

							fullImg = {
								src: value.full
							};
						}


						if (
							thumbImg.src &&
							mediaType !== "video"
						){
					#>

						<li>

							<# if(
								fullImg.src &&
								fullImg.src.indexOf("http://") == -1 &&
								fullImg.src.indexOf("https://") == -1
							){ #>

								<a
									href=\'{{ pagebuilder_base + fullImg.src }}\'
									class="sppb-gallery-btn"
								>

							<# } else if(fullImg.src){ #>

								<a
									href=\'{{ fullImg.src }}\'
									class="sppb-gallery-btn"
								>

							<# } #>


							<# if(
								thumbImg.src &&
								thumbImg.src.indexOf("http://") == -1 &&
								thumbImg.src.indexOf("https://") == -1
							){ #>

								<img
									class="sppb-img-responsive"
									src=\'{{ pagebuilder_base + thumbImg.src }}\'
									alt="{{ value.thumb.alt ?? value.title }}"
								>

							<# } else if(thumbImg.src){ #>

								<img
									class="sppb-img-responsive"
									src=\'{{ thumbImg.src }}\'
									alt="{{ value.thumb.alt ?? value.title }}"
								>

							<# } #>


							<# if(fullImg.src){ #>
								</a>
							<# } #>


							<# if(desc){ #>
								<p class="sppb-gallery-desc">
									{{ value.description }}
								</p>
							<# } #>

						</li>


					<# } else if(mediaType === "video") { #>

						<li>

							<a
								href=\'{{ videoSource }}\'
								class="sppb-gallery-btn sppb-gallery-video-btn"
								data-mfp-type="iframe"
							>

								<# if(videoPoster){ #>

									<# if(
										videoPoster.indexOf("http://") == -1 &&
										videoPoster.indexOf("https://") == -1
									){ #>

										<img
											class="sppb-img-responsive"
											src=\'{{ pagebuilder_base + videoPoster }}\'
											alt="{{ value.video_poster && value.video_poster.alt ? value.video_poster.alt : (value.thumb && value.thumb.alt ? value.thumb.alt : value.title) }}"
										>

									<# } else { #>

										<img
											class="sppb-img-responsive"
											src=\'{{ videoPoster }}\'
											alt="{{ value.video_poster && value.video_poster.alt ? value.video_poster.alt : (value.thumb && value.thumb.alt ? value.thumb.alt : value.title) }}"
										>

									<# } #>

								<# } else { #>

									<video
										class="sppb-img-responsive sppb-gallery-video-preview"
										src=\'{{ videoSource }}\'
										<# if(
											value.video &&
											value.video.width
										){ #>
											width="{{ value.video.width }}"
										<# } #>
										<# if(
											value.video &&
											value.video.height
										){ #>
											height="{{ value.video.height }}"
										<# } #>
										preload="metadata"
										muted
										playsinline
									></video>

								<# } #>

							</a>

							<# if(desc){ #>
								<p class="sppb-gallery-desc">
									{{ value.description }}
								</p>
							<# } #>

						</li>

					<# } #>

					<# }); #>

				</ul>

			</div>

		</div>
		';

		return $output;
	}
}
