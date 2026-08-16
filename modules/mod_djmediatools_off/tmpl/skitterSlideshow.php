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
defined('_JEXEC') or die ('Restricted access'); ?>
<script type="text/javascript">
	jQuery(document).ready(function() { 
		jQuery('#box_skitter<?php echo $mid; ?>').skitter({
			animation: 'random',
			theme: 'square',
			interval: <?php echo $params->get('delay'); ?>,
			navigation: <?php echo $params->get('show_arrows') ? 1 : 0; ?>,
			dots: <?php echo $params->get('show_custom_nav') ? 1 : 0; ?>,
			numbers_align: '<?php echo $params->get('custom_nav_align'); ?>',
			controls: <?php echo $params->get('show_buttons') ? 1 : 0; ?>,
			auto_play: <?php echo $params->get('autoplay') ? 1 : 0; ?>,
			stop_over: <?php echo $params->get('pause_autoplay') ? 1 : 0; ?>
		});
	});
</script>
<div id="box_skitter<?php echo $mid; ?>" class="skitter <?php echo $params->get('show_custom_nav') ? 'with-dots':'' ?> box_skitter dj-slides">
	
		<ul>        	
          	<?php foreach ($slides as $slide) { ?>			
				<li>
					<?php $image = 	'<img src="'.$slide->resized_image.'" alt="'.$slide->alt.'" class="dj-image lazyoff" '
 									.(!empty($slide->img_title) ? ' title="'.$slide->img_title.'"':'') . ' />'; ?>
            		
					<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_imagelink'); ?>
						
					<div class="label_text">
						<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
					</div> 
				</li>				
            <?php } ?>        	
        </ul>
</div>

<div style="clear: both"></div>