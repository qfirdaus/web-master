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



$attr = 'target="' . $slide->target . '"' . (!empty($slide->rel) ? ' rel="' . $slide->rel . '"' : '');
?>
<?php if ($params->get('show_title') || ($params->get('show_desc') && !empty($slide->description)) || ($params->get('show_readmore') && $slide->link)) { ?>
    <!-- Slide description area: START -->
    <div class="dj-slide-desc-in<?php echo($params->get('show_desc') == 2 ? ' showOnMouseOver' : ''); ?>">
        <div class="dj-slide-desc-bg"></div>
        <div class="dj-slide-desc-text" >
			<?php if (!empty($slide->extra)) { ?>
                <div class="dj-extra">
					<?php echo $slide->extra ?>
                </div>
			<?php } ?>

			<?php if ($params->get('show_title')) { ?>
                <div class="dj-slide-title">
					<?php if ($params->get('link_title') && $slide->link) { ?><a style="<?php echo $title_style ; ?>"
                            href="<?php echo $slide->link; ?>" <?php echo $attr; ?>><?php } ?>
						<?php echo JHtml::_('string.truncate', $slide->title, $params->get('title_limit', 0), false, false); ?>
						<?php if ($params->get('link_title') && $slide->link) { ?></a><?php } ?>
                </div>
			<?php } ?>

			<?php if ($params->get('show_desc')) { ?>
                <div class="dj-slide-description" >
					<?php if ($params->get('link_desc') && $slide->link) { ?><a style="<?php echo $desc_style; ?>"
                            href="<?php echo $slide->link; ?>" <?php echo $attr; ?>><?php } ?>
						<?php echo JHTML::_('content.prepare', $slide->description); ?>
						<?php if ($params->get('link_desc') && $slide->link) { ?></a><?php } ?>
                </div>
			<?php } ?>

			<?php
			if ($params->get('show_exif')) {
				echo DJMediatoolsLayoutHelper::getExifInfoAllInOne(
					$slide,
					$params
				);
			}
			?>

			<?php if ($params->get('show_readmore') && $slide->link) { ?>
                <div style="clear: both"></div>
                <div class="dj-readmore-wrapper">
                    <a href="<?php echo $slide->link ?>"
                     class="dj-readmore" <?php echo $attr; ?>><?php echo($params->get('readmore_text', 0) ? $params->get('readmore_text') : JText::_('COM_DJMEDIATOOLS_READMORE')); ?></a>
                </div>
			<?php } ?>
        </div>
        <div style="clear: both"></div>
    </div>
    <!-- Slide description area: END -->
<?php } ?>
