<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die ('Restricted access'); 

$imagelink = $params->get('link_image',1);
$lightboxType = $params->get('lightbox','magnific');
?>

<div class="dj-slide-image"><?php

require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_bgvideo');

if(!empty($slide->video) && $imagelink == 2 && $lightboxType == 'magnific') {
	$image .= '<span class="video-icon showOnMouseOver"></span>';
}

if ((($slide->link || !empty($slide->video)) && $imagelink==1) || $imagelink > 1) {

$caption = '';
if($params->get('show_title')) {
	$caption = htmlspecialchars($slide->title);
}
if($params->get('show_desc') && !empty($slide->description)) {
	$caption.= (!empty($caption) ? ' - ':'').htmlspecialchars(strip_tags($slide->description,"<a><b><strong><em><i><u>"));
}
 
switch($imagelink) {
	case 2:
		if($lightboxType == 'magnific') {
			if(!empty($slide->video)) {
				if(!preg_match('/\.mp4$/i', $slide->video)) $slide->video .= (strstr($slide->video, '?') === false ? '?' : '&amp;').'autoplay=1&amp;rel=0';
				echo '<a data-id="' . $slide->id . '" class="dj-slide-link mfp-iframe" href="'.$slide->video.'" target="'.$slide->target.'">'.$image.'</a>';
			} else {
				echo '<a data-id="' . $slide->id . '" class="dj-slide-link" data-title="'.$caption.'" href="'.$slide->image.'" target="'.$slide->target.'">'.$image.'</a>';
			}
		} else if($lightboxType == 'photoswipe') {
			echo '<a data-id="' . $slide->id . '" class="dj-slide-pswp" data-caption="'.$caption.'" data-size="'.$slide->image_size->w.'x'.$slide->image_size->h.'" data-msrc="'.$slide->resized_image.'" href="'.$slide->image.'" target="'.$slide->target.'">'.$image.'</a>';
		} else {
			echo '<a data-id="' . $slide->id . '" rel="lightbox-grid'.$mid.'" title="'.$caption.'" '
				.'href="'.$slide->image.'" target="'.$slide->target.'">'.$image.'</a>';
		}		
		break;
	case 3:
		echo '<a data-id="' . $slide->id . '" class="dj-slide-popup" href="'.$slide->item_link.'" target="'.$slide->target.'">'.$image.(isset($slide->video) && !empty($slide->video) ? '<span class="video-icon showOnMouseOver"></span>':'').'</a>';
		break;
	default:
		$attr = 'target="'.$slide->target.'"' .(!empty($slide->rel) ? ' rel="'.$slide->rel.'"':'');
		echo '<a data-id="' . $slide->id . '" href="'.$slide->link.'" '.$attr.'>'.$image.'</a>';
		break;
}

} else {
	
	echo $image;
	
} 

require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_video');
?>
</div>
