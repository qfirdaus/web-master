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

$border_radius = $params->get('border_radius', '0px 0px 0px 0px;');

// no direct access
defined('_JEXEC') or die ('Restricted access'); ?>
<script type="text/javascript">
	jQuery(window).on('load',function() { jQuery('#nivoSlider<?php echo $mid; ?>').nivoSlider({effect: 'random'}); });
</script>

	<div id="nivoSlider-wrapper<?php echo $mid; ?>" class="slider-wrapper">
    	<div id="nivoSlider<?php echo $mid; ?>" class="nivoSlider dj-slides" style="border-radius: <?php echo $border_radius; ?>">
    		<?php foreach ($slides as $key => $slide) { ?>
    					<?php $image = '<img src="'.$slide->resized_image.'" alt="'.$slide->alt.'" class="dj-image" title="#nivo'.$mid.'Desc'.$key.'" />'; ?>
            			
						<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_imagelink'); ?>
            <?php } ?>
    	</div>
	</div>
	<?php foreach ($slides as $key => $slide) { ?>
	<div id="nivo<?php echo $mid; ?>Desc<?php echo $key ?>" class="nivo-html-caption">
    	<?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
	</div>
	<?php } ?>