<?php

/**
 * @package Lightbox
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2022 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');

SpAddonsConfig::addonConfig(
    array(
        'type' => 'repeatable',
        'addon_name' => 'sp_slideshow_full',
        'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SLIDESHOW_FULL'),
        'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SLIDESHOW_FULL_DESC'),
        'attr' => array(
            'general' => array(
                'autoplay' => array(
                    'type' => 'select',
                    'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_AUTOPLAY'),
                    'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_AUTOPLAY_DESC'),
                    'values' => array(
                        1 => JText::_('JYES'),
                        0 => JText::_('JNO'),
                    ),
                    'std' => 1,
                ),
                'controllers' => array(
                    'type' => 'select',
                    'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_SHOW_CONTROLLERS'),
                    'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_SHOW_CONTROLLERS_DESC'),
                    'values' => array(
                        1 => JText::_('JYES'),
                        0 => JText::_('JNO'),
                    ),
                    'std' => 1,
                ),
                'arrows' => array(
                    'type' => 'select',
                    'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_SHOW_ARROWS'),
                    'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_SHOW_ARROWS_DESC'),
                    'values' => array(
                        1 => JText::_('JYES'),
                        0 => JText::_('JNO'),
                    ),
                    'std' => 1,
                ),
                'color' => array(
                    'type' => 'color',
                    'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_FONT_COLOR'),
                    'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_FONT_COLOR_DESC'),
                ),
                'class' => array(
                    'type' => 'text',
                    'title' => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
                    'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
                    'std' => ''
                ),
                // Repeatable Item
                'sp_slideshow_full_item' => array(
                    'title' => JText::_('COM_SPPAGEBUILDER_ADDON_REPEATABLE_ITEMS'),
                    'attr' => array(
                        'sub_title' => array(
                            'type' => 'text',
                            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_SUB_TITLE'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_SUB_TITLE_DESC'),
                            'std' => 'Carousel Item Sub Title',
                        ),
                        'title' => array(
                            'type' => 'text',
                            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_TITLE'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_TITLE_DESC'),
                            'std' => 'Carousel Item Title',
                        ),
                        'content' => array(
                            'type' => 'editor',
                            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_CONTENT'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_CONTENT_DESC'),
                            'std' => 'Anim pariatur cliche reprehenderit, enim eiusmod high life accusamus terry richardson ad squid. 3 wolf moon officia aute, non cupidatat skateboard dolor brunch. Food truck quinoa nesciunt laborum eiusmod. Brunch 3 wolf moon tempor, sunt aliqua put a bird on it squid single-origin coffee nulla assumenda shoreditch et.'
                        ),
                        'bg' => array(
                            'type' => 'media',
                            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_BACKGROUND_IMAGE'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SF_ITEM_BACKGROUND_IMAGE_DESC'),
                        ),
                        'target' => array(
                            'type' => 'select',
                            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET_DESC'),
                            'values' => array(
                                '_self' => JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET_SAME_WINDOW'),
                                '_blank' => JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_TARGET_NEW_WINDOW'),
                            ),
                        ),
                        'separator_one' => array(
                            'type' => 'separator',
                            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_ONE'),
                        ),
                        'button_one_text' => array(
                            'type' => 'text',
                            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_ONE_TEXT'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_ONE_TEXT_DESC'),
                        ),
                        'button_one_url' => array(
                            'type' => 'text',
                            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_ONE_URL'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_BUTTON_ONE_URL_DESC'),
                        ),
                        'button_one_before_icon' => array(
                            'type' => 'icon',
                            'title' => JText::_('COM_SPPAGEBUILDER_ADDON_BEFORE_TITLE_BUTTON_ONE_ICON'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_BEFORE_TITLE_BUTTON_ONE_ICON_DESC'),
                        ),
                        'separator_title' => array(
                            'type' => 'separator',
                            'title' => JText::_('COM_SPPAGEBUILDER_SF_TITLE_ANIMATION'),
                        ),
                        'title_animation' => array(
                            'type' => 'animation',
                            'title' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION_DESC'),
                        ),
                        'title_animationduration' => array(
                            'type' => 'number',
                            'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION_DESC'),
                            'std' => '300',
                            'placeholder' => '300',
                        ),
                        'title_animationdelay' => array(
                            'type' => 'number',
                            'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY_DESC'),
                            'std' => '0',
                            'placeholder' => '300',
                        ),
                        'separator_subtitle' => array(
                            'type' => 'separator',
                            'title' => JText::_('COM_SPPAGEBUILDER_SF_SUBTITLE_ANIMATION'),
                        ),
                        'subtitle_animation' => array(
                            'type' => 'animation',
                            'title' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION_DESC'),
                        ),
                        'subtitle_animationduration' => array(
                            'type' => 'number',
                            'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION_DESC'),
                            'std' => '300',
                            'placeholder' => '300',
                        ),
                        'subtitle_animationdelay' => array(
                            'type' => 'number',
                            'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY_DESC'),
                            'std' => '0',
                            'placeholder' => '300',
                        ),
                        'separator_content' => array(
                            'type' => 'separator',
                            'title' => JText::_('COM_SPPAGEBUILDER_SF_CONTENT_ANIMATION'),
                        ),
                        'cotent_animation' => array(
                            'type' => 'animation',
                            'title' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION_DESC'),
                        ),
                        'cotent_animationduration' => array(
                            'type' => 'number',
                            'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION_DESC'),
                            'std' => '300',
                            'placeholder' => '300',
                        ),
                        'cotent_animationdelay' => array(
                            'type' => 'number',
                            'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY_DESC'),
                            'std' => '0',
                            'placeholder' => '300',
                        ),
                        'separator_button' => array(
                            'type' => 'separator',
                            'title' => JText::_('COM_SPPAGEBUILDER_SF_BUTTON_ANIMATION'),
                        ),
                        'button_animation' => array(
                            'type' => 'animation',
                            'title' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_COLUMN_ANIMATION_DESC'),
                        ),
                        'button_animationduration' => array(
                            'type' => 'number',
                            'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DURATION_DESC'),
                            'std' => '300',
                            'placeholder' => '300',
                        ),
                        'button_animationdelay' => array(
                            'type' => 'number',
                            'title' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY'),
                            'desc' => JText::_('COM_SPPAGEBUILDER_ANIMATION_DELAY_DESC'),
                            'std' => '0',
                            'placeholder' => '300',
                        ),
                    )
                ),
            ),
        )
    )
);
