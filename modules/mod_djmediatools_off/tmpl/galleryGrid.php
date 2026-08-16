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

$tip = ($params->get('desc_position')=='tip' ? true : false);
if($tip){
	$toolTipArray = array(
		'showDelay'=>'0',
		'hideDelay'=>'200', 'fixed'=>false,
		'onShow'=>"function(tip) {tip.fade('in');}", 
		'onHide'=>"function(tip) {tip.fade('out');}",
		'offsets'=>array('x'=>20, 'y'=>20));
	JHTML::_('behavior.tooltip', '.descTip', $toolTipArray); 
}

$wcag = $params->get('wcag', 1) ? ' tabindex="0"' : '';
$descpos = $params->get('desc_position');
$border_radius = $params->get('border_radius', '0px 0px 0px 0px;');

?>



<div id="dj-galleryGrid<?php echo $mid; ?>" class="dj-galleryGrid<?php echo $descpos ? ' desc-'.$descpos:''; ?>">
	<div class="dj-galleryGrid-in">
		<div class="dj-slides" >
        	
          	<?php foreach ($slides as $key => $slide) { ?>
			
				<div style="border-radius: <?php echo $border_radius; ?>" class="dj-slide dj-slide-<?php echo ($key+1) . ($tip ? ' descTip':'') ?>" <?php echo $tip ? 'title="'.htmlspecialchars($slide->title).'::'.htmlspecialchars($slide->description).'"':''?><?php echo $wcag; ?> >
					<div class="dj-slide-in">
					
						<?php if($descpos == 'above') { ?>
							<div class="dj-slide-desc">
								<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
							</div>
						<?php } ?>
						
						<?php $image = 	'<img src="'.$slide->blank.'" data-src="'.$slide->resized_image.'" '
 									.	(!empty($slide->data_srcset) ? ' data-srcset="'.$slide->data_srcset.'" data-sizes="'.$slide->sizes.'" ':'')
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
		
		<div class="dj-gallery-end" style="clear: both"<?php echo $wcag; ?>></div>
	</div>
</div>

