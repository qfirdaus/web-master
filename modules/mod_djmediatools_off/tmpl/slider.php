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

$wcag = $params->get('wcag', 1) ? ' tabindex="0"' : '';

$border_radius = $params->get('border_radius', '0px 0px 0px 0px;');
?>

<div style="border: 0px !important;">
	<div id="djslider-loader<?php echo $mid; ?>" class="djslider-loader"<?php echo $wcag; ?>>
	
	<?php if($params->get('show_custom_nav') && $params->get('custom_nav_pos')=='above') { ?>
		<div id="cust-navigation<?php echo $mid; ?>" class="navigation-container-custom <?php echo $params->get('show_custom_nav') == '2' ? 'showOnHover':'';?>">
			<div class="cust-navigation-in">
			<?php $i = 0; foreach ($slides as $slide) { 
				?><a href="#" class="load-button<?php if ($i == 0) echo ' load-button-active'; ?>"><?php //echo ($i+1) ?></a><?php 
				$i++; } ?>
			</div>
        </div>
    <?php } ?>
	
    <div id="djslider<?php echo $mid; ?>" class="djslider">
        <div id="slider-container<?php echo $mid; ?>" class="slider-container" style="border-radius: <?php echo $border_radius; ?>">
        	<ul id="slider<?php echo $mid; ?>" class="dj-slides" >
          		<?php foreach ($slides as $slide) { ?>
          			<li>
          				<?php $image = 	'<img src="'.$slide->resized_image.'" '
 									.	(!empty($slide->srcset) ? 'srcset="'.$slide->srcset.'" sizes="'.$slide->sizes.'" ':'')
 									.	'alt="'.$slide->alt.'" class="dj-image" width="'.$slide->size->w.'" height="'.$slide->size->h.'" '
 									.	(!empty($slide->img_title) ? ' title="'.$slide->img_title.'"':'') . ' />'; ?>
            			
						<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_imagelink'); ?>
						
						<div class="dj-slide-desc <?php echo $params->get('show_desc') == '2' ? 'showOnHover':'';?>">
							<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
						</div>
						
					</li>
                <?php } ?>
        	</ul>
        </div>
        <?php if($params->get('show_arrows') || $params->get('show_buttons')) { ?>
        <div id="navigation<?php echo $mid; ?>" class="navigation-container">
        	<?php if($params->get('show_arrows')) { ?>
        	<a href="#" id="prev<?php echo $mid; ?>" class="prev-button <?php echo $params->get('show_arrows') == '2' ? 'showOnHover':'';?>">
        		<img src="<?php echo $navigation->prev; ?>" alt="<?php echo JText::_('Previous'); ?>" />
			</a>
			<a href="#" id="next<?php echo $mid; ?>" class="next-button <?php echo $params->get('show_arrows') == '2' ? 'showOnHover':'';?>">
				<img src="<?php echo $navigation->next; ?>" alt="<?php echo JText::_('Next'); ?>" />
			</a>
			<?php } ?>
			<?php if($params->get('show_buttons')) { ?>
			<a href="#" id="play<?php echo $mid; ?>" class="play-button <?php echo $params->get('show_buttons') == '2' ? 'showOnHover':'';?>">
				<img src="<?php echo $navigation->play; ?>" alt="<?php echo JText::_('Play'); ?>" />
			</a>
			<a href="#" id="pause<?php echo $mid; ?>" class="pause-button <?php echo $params->get('show_buttons') == '2' ? 'showOnHover':'';?>">
				<img src="<?php echo $navigation->pause; ?>" alt="<?php echo JText::_('Pause'); ?>" />
			</a>
			<?php } ?>
        </div>
        <?php } ?>
        <?php if($params->get('show_custom_nav') && ($params->get('custom_nav_pos')=='topin' || $params->get('custom_nav_pos')=='bottomin')) { ?>
		<div id="cust-navigation<?php echo $mid; ?>" class="navigation-container-custom <?php echo $params->get('show_custom_nav') == '2' ? 'showOnHover':'';?>">
			<div class="cust-navigation-in">
			<?php $i = 0; foreach ($slides as $slide) { 
				?><a href="#" class="load-button<?php if ($i == 0) echo ' load-button-active'; ?>"><?php //echo ($i+1) ?></a><?php 
				$i++; } ?>
			</div>
        </div>
        <?php } ?>
    </div>
    
    <?php if($params->get('show_custom_nav') && $params->get('custom_nav_pos')=='below') { ?>
    	<div id="cust-navigation<?php echo $mid; ?>" class="navigation-container-custom <?php echo $params->get('show_custom_nav') == '2' ? 'showOnHover':'';?>">
			<div class="cust-navigation-in">
			<?php $i = 0; foreach ($slides as $slide) { 
				?><a href="#" class="load-button<?php if ($i == 0) echo ' load-button-active'; ?>"><?php //echo ($i+1) ?></a><?php 
				$i++; } ?>
			</div>
        </div>
    <?php } ?>
    
	</div>
</div>
<div style="clear: both" class="djslider-end"<?php echo $wcag; ?>></div>