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

if(!empty($slide->video)) {
	
	$layout = $params->get('layout');
	$layout = explode(':', $layout);
	$layout = end($layout);
	
	$playbackType = $params->get('playback', 'popup');
	$playInline = true;
	
	if($playbackType == 'inline' && DJMediatoolsLayoutHelper::isInlineVideoSupported($layout)) {
		
		if(strstr($slide->video, 'youtube.com/embed') !== false) { // YouTube
			
			$slide->video = str_replace('autoplay=1', 'autoplay=0', $slide->video);
			$slide->video .= (strstr($slide->video, '?') !== false ? '&amp;':'?')
				.('rel=0&amp;showinfo=0&amp;enablejsapi=1'); ?>
			<iframe class="full_video" width="100%" height="100%" frameborder="0" allowfullscreen
				src="<?php echo $slide->video ?>" data-provider="YouTube"></iframe>
			
		<?php } else if(strstr($slide->video, '//player.vimeo.com') !== false) { // Vimeo
			
			$slide->video = str_replace('autoplay=1', 'autoplay=0', $slide->video);
			$slide->video .= (strstr($slide->video, '?') !== false ? '&amp;':'?')
				.('portrait=0&amp;title=0&amp;byline=0&amp;api=1') ?>
			<iframe class="full_video" width="100%" height="100%" frameborder="0" allowfullscreen
				src="<?php echo $slide->video ?>" data-provider="Vimeo"></iframe>
					
		<?php } else if(preg_match('/\.mp4$/i', $slide->video)) { ?>
			
			<video class="full_video" width="100%" height="100%" controls data-provider="html5">
				<source src="<?php echo $slide->video ?>" type="video/mp4"></source>
			</video>
			
		<?php } else { // otherwise use playback in the popup
			$playInline = false;
		}
		
	} else {
		
		$playInline = false;
	}
	
	if($playInline) {
		echo '<a data-id="' . $slide->id . '" href="#" class="play-video video-icon showOnMouseOver"><span class="sr-only">' . JText::_('Play') .'</span></a>';
	} else {

		if(!($imagelink == 2 && $lightboxType == 'magnific')) {
			$slide->video .= (strstr($slide->video, '?') === false ? '?' : '&amp;').'autoplay=1&amp;rel=0';
			echo '<a data-id="' . $slide->id . '" class="dj-slide-popup mfp-iframe" href="'.$slide->video.'" target="'.$slide->target.'"><span class="video-icon showOnMouseOver"></span></a>';
		}
	}
}
