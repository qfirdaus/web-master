<?php
/**
 * @version $Id: galleryGrid.php 99 2017-08-04 10:55:30Z szymon $
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

$tip = ($params->get('desc_position')=='tip' ? true : false);
if($tip){
	JHTML::_('bootstrap.tooltip', '.descTip');
}

$wcag = $params->get('wcag', 1) ? ' tabindex="0"' : '';
$descpos = $params->get('desc_position');


$border_radius = $params->get('border_radius', '0px 0px 0px 0px;');
?>

<div id="dj-masonry<?php echo $mid; ?>" class="dj-masonry<?php echo $descpos ? ' desc-'.$descpos:''; ?>">
	<div class="dj-masonry-in">
		<div class="dj-slides">
          	<?php foreach ($slides as $key => $slide) { 
          		
          		if($tip) {
          			$tooltip = '';
					if($params->get('show_title')) $tooltip = htmlspecialchars($slide->title);
					if($params->get('show_desc') && !empty($slide->description)) $tooltip = (!empty($tooltip) ? '<strong>'.$tooltip.'</strong> - ':'')
					. htmlspecialchars(strip_tags($slide->description,"<a><b><strong><em><i><u>"));
          		} ?>
			
				<div class="dj-slide dj-slide-<?php echo ($key+1) . ($tip ? ' descTip" title="'.$tooltip : '') ?>"<?php echo $wcag ?>>
					<div class="dj-slide-in" style="border-radius: <?php echo $border_radius; ?>">
					
						<?php if($descpos == 'above') { ?>
							<div class="dj-slide-desc">
								<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
							</div>
						<?php } ?>
						
						<?php $image = 	'<img src="'.$slide->resized_image.'" '
 									.	(!empty($slide->srcset) ? ' srcset="'.$slide->srcset.'" sizes="'.$slide->sizes.'" ':'')
 									.	'alt="'.$slide->alt.'" class="dj-image" width="'.$slide->size->w.'" height="'.$slide->size->h.'" '
 									.	(!empty($slide->img_title) ? ' title="'.$slide->img_title.'"':'') . ' />'; ?>
            			
						<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_imagelink'); ?>
						
						<?php if($descpos != 'above' && $descpos != 'tip') { ?>
							<div class="dj-slide-desc">
								<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
							</div>
						<?php } ?>
					</div>
				</div>
				
            <?php } ?>
		</div>
	</div>
</div>

