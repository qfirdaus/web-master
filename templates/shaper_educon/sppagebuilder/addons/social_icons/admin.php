<?php

/**
 * @package Educon
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2022 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');

SpAddonsConfig::addonConfig(
	array(
		'type' => 'repeatable',
		'addon_name' => 'sp_social_icons',
		'category' => 'Educon',
		'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_ICONS'),
		'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_SOCIAL_ICONS_DESC'),
		'attr' => array(
			'general' => array(
				'title' => array(
					'type' => 'text',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_DESC'),
					'std' =>  ''
				),

				'heading_selector' => array(
					'type' => 'select',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_DESC'),
					'values' => array(
						'h1' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H1'),
						'h2' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H2'),
						'h3' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H3'),
						'h4' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H4'),
						'h5' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H5'),
						'h6' => JText::_('COM_SPPAGEBUILDER_ADDON_HEADINGS_H6'),
					),
					'std' => 'h3',
					'depends' => array(array('title', '!=', '')),
				),

				'title_font_family' => array(
					'type' => 'fonts',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_FAMILY'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_FAMILY_DESC'),
					'depends' => array(array('title', '!=', '')),
					'selector' => array(
						'type' => 'font',
						'font' => '{{ VALUE }}',
						'css' => '.sppb-addon-title { font-family: {{ VALUE }}; }'
					)
				),

				'title_fontsize' => array(
					'type' => 'slider',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE_DESC'),
					'std' => '',
					'depends' => array(array('title', '!=', '')),
					'responsive' => true,
					'max' => 400,
				),

				'title_lineheight' => array(
					'type' => 'slider',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_LINE_HEIGHT'),
					'std' => '',
					'depends' => array(array('title', '!=', '')),
					'responsive' => true,
					'max' => 400,
				),

				'title_font_style' => array(
					'type' => 'fontstyle',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_STYLE'),
					'depends' => array(array('title', '!=', '')),
				),

				'title_letterspace' => array(
					'type' => 'select',
					'title' => JText::_('COM_SPPAGEBUILDER_GLOBAL_LETTER_SPACING'),
					'values' => array(
						'0' => 'Default',
						'1px' => '1px',
						'2px' => '2px',
						'3px' => '3px',
						'4px' => '4px',
						'5px' => '5px',
						'6px' =>	'6px',
						'7px' =>	'7px',
						'8px' =>	'8px',
						'9px' =>	'9px',
						'10px' => '10px'
					),
					'std' => '0',
					'depends' => array(array('title', '!=', '')),
				),

				'title_text_color' => array(
					'type' => 'color',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR_DESC'),
					'depends' => array(array('title', '!=', '')),
				),

				'title_margin_top' => array(
					'type' => 'slider',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP_DESC'),
					'placeholder' => '10',
					'depends' => array(array('title', '!=', '')),
					'responsive' => true,
					'max' => 400,
				),

				'title_margin_bottom' => array(
					'type' => 'slider',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM_DESC'),
					'placeholder' => '10',
					'depends' => array(array('title', '!=', '')),
					'responsive' => true,
					'max' => 400,
				),

				'class' => array(
					'type' => 'text',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
					'std' => ''
				),
				//Repetable
				'sp_social_icons_item' => array(
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_REPEATABLE_ITEMS'),
					'attr' => array(
						'title' => array(
							'type' => 'text',
							'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE'),
							'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_DESC'),
							'std' => 'Title of the icon'
						),
						'icon' => array(
							'type' => 'icon',
							'title' => JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_ICON_NAME'),
							'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_GLOBAL_ICON_NAME_DESC'),
							'std' => ''
						),
						'url' => array(
							'type' => 'text',
							'title' => JText::_('COM_SPPAGEBUILDER_ADDON_URL'),
							'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_URL_DESC'),
							'placeholder' => 'http://www.facebook.com/joomshaper',
							'std' => '',
						),
					)
				),
			),
		)
	)
);
