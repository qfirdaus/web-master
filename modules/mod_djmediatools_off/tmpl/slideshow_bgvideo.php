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

$layout = $params->get('layout');
$layout = explode(':', $layout);
$layout = end($layout);

if(!empty($slide->bgvideo) && DJMediatoolsLayoutHelper::isBackgroundVideoSupported($layout)) { ?>

	<div class="video_cover" style="background-image: url('<?php echo $slide->image ?>');">
	<?php if(strstr($slide->bgvideo, 'youtube.com/embed') !== false) { // YouTube
	
		preg_match('/embed\/([\w\d_-]+)/', $slide->bgvideo, $match);
		$videoID = $match[1];
		$slide->bgvideo = str_replace('autoplay=1', 'autoplay=0', $slide->bgvideo);
		$slide->bgvideo .= (strstr($slide->bgvideo, '?') !== false ? '&amp;':'?')
			.('muted=1&amp;loop=1&amp;playlist='.$videoID.'&amp;controls=0&amp;rel=0&amp;showinfo=0&amp;enablejsapi=1'); ?>
		<iframe class="cover lazyOff" width="100%" height="100%" frameborder="0"
			src="<?php echo $slide->bgvideo ?>" allow="autoplay" data-provider="YouTube"></iframe>
			
	<?php } else if(strstr($slide->bgvideo, '//player.vimeo.com') !== false) { // Vimeo
	
		$slide->bgvideo = str_replace('autoplay=1', 'autoplay=0', $slide->bgvideo);
		$slide->bgvideo .= (strstr($slide->bgvideo, '?') !== false ? '&amp;':'?')
			.('background=1&amp;muted=1&amp;loop=1&amp;autoplay=0&amp;portrait=0&amp;title=0&amp;byline=0&amp;api=1') ?>
		<iframe class="cover lazyOff" width="100%" height="100%" frameborder="0"
			src="<?php echo $slide->bgvideo ?>" allow="autoplay" data-provider="Vimeo"></iframe>
			
	<?php } else if(preg_match('/\.mp4$/i', $slide->bgvideo)) { ?>
	
		<video width="100%" height="100%" muted loop data-provider="html5">
			<source src="<?php echo $slide->bgvideo ?>" type="video/mp4"></source>
		</video>
	<?php } ?>
			
		<div class="video_mask"></div>
	</div>
<?php }
