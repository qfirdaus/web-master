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

$descpos = $params->get('desc_position');

$border_radius = $params->get('border_radius', '0px 0px 0px 0px;');
?>

<div style="border: 0px !important;">
<div id="dj-tabber<?php echo $mid; ?>" class="dj-tabber<?php echo $descpos ? ' desc-'.$descpos:''; ?>">
	<div class="dj-tabber-in dj-tabs-<?php echo $params->get('tab_position') ?>">
		<div class="dj-slides" style="border-radius: <?php echo $border_radius; ?>">
        	
          	<?php foreach ($slides as $key => $slide) { ?>
			
				<div class="dj-slide dj-slide-<?php echo ($key+1) . ($key == 0 ? ' dj-active':''); ?>">
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
        <div class="dj-navigation">
        	<div class="dj-navigation-in">
        		<?php if($params->get('show_arrows')) { ?>
	        		<a href="#" class="dj-prev <?php echo ($params->get('show_arrows')==2 ? 'showOnMouseOver' : ''); ?>"><img src="<?php echo $navigation->prev; ?>" alt="<?php echo JText::_('Previous'); ?>" /></a>
					<a href="#" class="dj-next <?php echo ($params->get('show_arrows')==2 ? 'showOnMouseOver' : ''); ?>"><img src="<?php echo $navigation->next; ?>" alt="<?php echo JText::_('Next'); ?>" /></a>
				<?php } ?>
				<?php if($params->get('show_buttons')) { ?>
					<a href="#" class="dj-play <?php echo ($params->get('show_buttons')==2 ? 'showOnMouseOver' : ''); ?>"><img src="<?php echo $navigation->play; ?>" alt="<?php echo JText::_('Play'); ?>" /></a>
					<a href="#" class="dj-pause <?php echo ($params->get('show_buttons')==2 ? 'showOnMouseOver' : ''); ?>"><img src="<?php echo $navigation->pause; ?>" alt="<?php echo JText::_('Pause'); ?>" /></a>
        		<?php } ?>
			</div>
		</div>
		<div class="dj-tabs">
			<div class="dj-tabs-in">
				<?php $key = 0; foreach ($slides as $slide) { ?>
				<a href="#" class="dj-tab <?php echo (++$key==1 ? 'dj-tab-active' : '') ?>">
					<span class="dj-tab-in">						
						
						<?php if($params->get('show_thumbs')) { ?><span>
							<img src="<?php echo $slide->thumb_image; ?>" alt="<?php echo $slide->alt; ?>" width="<?php echo $params->get('thumb_width') ?>" height="<?php echo $params->get('thumb_height') ?>" />
						</span><?php } ?>
						<span>
							<?php echo JHtml::_('string.truncate', $slide->title, $params->get('tab_title_limit', 0), false, false); ?>
						</span>
						
					</span>
				</a>
				<?php } ?>
				<?php if($params->get('tab_indicator')) { ?>
					<div class="dj-tab-indicator  dj-tab-indicator-<?php echo $params->get('tab_position') ?>"></div>
				<?php } ?>
			</div>
		</div>
		
		<div class="dj-loader"></div>
	</div>
</div>
<div style="clear: both" class="djslideshow-end"></div>
</div>
