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


    <div id="dj-horizontalSwipe<?php echo $mid; ?>" class="djslider dj-slides">

        <?php if($params->get('show_custom_nav') && ($params->get('custom_nav_pos')=='above' || $params->get('custom_nav_pos')=='topin' )) { ?>
            <div class="dj-indicators <?php echo ($params->get('show_custom_nav')==2 ? 'showOnMouseOver' : ''); ?>">
                <div class="dj-indicators-in" style="display: none">
                 
                </div>
            </div>
        <?php } ?>

        <div id="slider-container<?php echo $mid; ?>" class="djhorizontalswipe">
            <?php foreach ($slides as $slide) : ?>

                <div class="dj-horizontal-slide">
                    <div class="dj-slide-in">

                        <?php if ($descpos == 'above') { ?>
                            <div class="dj-slide-desc">
                                <?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
                            </div>
                        <?php } ?>

                        <div class="dj-image" >
                            <?php $image = '<img src="' . $slide->blank . '" data-src="' . $slide->resized_image . '" '
                                . (!empty($slide->data_srcset) ? ' data-srcset="' . $slide->data_srcset . '" data-sizes="' . $slide->sizes . '" ' : '')
                                . 'alt="' . $slide->alt . '" class="dj-image" width="' . $slide->size->w . '" height="' . $slide->size->h . '" '
                                . (!empty($slide->img_title) ? ' title="' . $slide->img_title . '"' : '') . ' />'; ?>

                            <?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_imagelink'); ?>

                            <?php if ($descpos == 'over') { ?>
                                <div class="dj-slide-desc">
                                    <?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
                                </div>
                            <?php } ?>
                        </div>


                        <?php if ($descpos != 'above' && $descpos != 'tip' && $descpos != 'over') { ?>
                            <div class="dj-slide-desc">
                                <?php require JModuleHelper::getLayoutPath('mod_djmediatools', 'slideshow_description'); ?>
                            </div>
                        <?php } ?>
                        
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
     
        <div class="dj-loader-overlay"><div class="dj-loader"></div></div>
        <div class="dj-navigation">
            <div class="dj-navigation-in">
                <?php if($params->get('show_arrows')) { ?>
                    <a href="#" class="dj-prev <?php echo ($params->get('show_arrows')==2 ? 'showOnMouseOver' : ''); ?>"><img src="<?php echo $navigation->prev; ?>" alt="<?php echo JText::_('Previous'); ?>" /></a>
                    <a href="#" class="dj-next <?php echo ($params->get('show_arrows')==2 ? 'showOnMouseOver' : ''); ?>"><img src="<?php echo $navigation->next; ?>" alt="<?php echo JText::_('Next'); ?>" /></a>
                <?php } ?>
                <div class="dj-navigation-play">

                </div>
            </div>
        </div>
        <?php if($params->get('show_custom_nav') && ($params->get('custom_nav_pos')=='below' || $params->get('custom_nav_pos')=='bottomin')) { ?>
            <div class="dj-indicators <?php echo ($params->get('show_custom_nav')==2 ? 'showOnMouseOver' : ''); ?>">
                <div class="dj-indicators-in" style="display: none">

                </div>
            </div>
        <?php } ?>

    </div>

</div>
