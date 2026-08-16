<?php

/**
 * @package Educon
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');

SpAddonsConfig::addonConfig(
	array(
		'type' => 'general',
		'addon_name' => 'sp_courses',
		'category' => 'Educon',
		'title' => JText::_('COM_SPPAGEBUILDER_ADDON_COURSES'),
		'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_COURSES_DESC'),
		'attr' => array(
			'general' => array(
				'admin_label' => array(
					'type' => 'text',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_ADMIN_LABEL_DESC'),
					'std' => ''
				),

				'title' => array(
					'type' => 'text',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_DESC'),
					'std' => ''
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

				'title_fontsize' => array(
					'type' => 'number',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_SIZE_DESC'),
					'std' => '',
					'depends' => array(array('title', '!=', '')),
				),

				'title_lineheight' => array(
					'type' => 'text',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_LINE_HEIGHT'),
					'std' => '',
					'depends' => array(array('title', '!=', '')),
				),

				'title_fontstyle' => array(
					'type' => 'select',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_STYLE'),
					'values' => array(
						'underline' => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UNDERLINE'),
						'uppercase' => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_UPPERCASE'),
						'italic' => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_ITALIC'),
						'lighter' => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_LIGHTER'),
						'normal' => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_NORMAL'),
						'bold' => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLD'),
						'bolder' => JText::_('COM_SPPAGEBUILDER_GLOBAL_FONT_STYLE_BOLDER'),
					),
					'multiple' => true,
					'std' => '',
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

				'title_fontweight' => array(
					'type' => 'text',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_WEIGHT'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_FONT_WEIGHT_DESC'),
					'std' => '',
					'depends' => array(array('title', '!=', '')),
				),

				'title_text_color' => array(
					'type' => 'color',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_TEXT_COLOR_DESC'),
					'depends' => array(array('title', '!=', '')),
				),

				'title_margin_top' => array(
					'type' => 'number',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_TOP_DESC'),
					'placeholder' => '10',
					'depends' => array(array('title', '!=', '')),
				),

				'title_margin_bottom' => array(
					'type' => 'number',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_TITLE_MARGIN_BOTTOM_DESC'),
					'placeholder' => '10',
					'depends' => array(array('title', '!=', '')),
				),

				'separator_subtitle' => array(
					'type'  => 'separator',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SEPARATOR_SUBTITLE'),
					'std'   => ''
				),
				'subtitle' => array(
					'type' => 'text',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_COURSES_SUBTITLE'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_COURSES_SUBTITLE_DESC'),
					'std' => ''
				),

				'separator_course_optons' => array(
					'type'  => 'separator',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_SEPARATOR_COURSE_OPTONS'),
					'std'   => ''
				),
				'course_type' => array(
					'type' => 'select',
					'title' => JText::_('COURSES_TYPE'),
					'desc' => JText::_('COURSES_TYPE_DESC'),
					'values' => array(
						'' => JText::_('COURSES_TYPE_DEFAULT'),
						'paid' => JText::_('COURSES_TYPE_PAID'),
						'free' => JText::_('COURSES_TYPE_FREE'),
						'featured' => JText::_('COURSES_TYPE_FREATURED'),
						'popular' => JText::_('COURSES_TYPE_POPULAR')
					),
					'std' => '',
				),

				'columns' => array(
					'type' => 'select',
					'title' => JText::_('COURSES_COLUMNS'),
					'desc' => JText::_('COURSES_COLUMNS_DESC'),
					'values' => array(2 => 2, 3 => 3, 4 => 4, 6 => 6),
					'std' => 3,
				),

				'limit' => array(
					'type' => 'number',
					'title' => JText::_('COURSES_LIMIT'),
					'desc' => JText::_('COURSES_LIMIT_DESC'),
					'std' => '12'
				),

				'layout' => array(
					'type' => 'select',
					'title' => JText::_('COURSES_LAYOUT'),
					'desc' => JText::_('COURSES_LAYOUT_DESC'),
					'values' => array(
						'default' => JText::_('COURSES_LAYOUT_DEFAULT'),
						'carousel' => JText::_('COURSES_LAYOUT_CAROUSEL')
					),
					'std' => 'default',
				),

				'course_meta' => array(
					'type' => 'checkbox',
					'title' => JText::_('COURSES_COURSE_META'),
					'desc' => JText::_('COURSES_COURSE_META_DESC'),
					'values' => array(
						'1' => JText::_('JYES'),
						'0' => JText::_('JNO')
					),
					'std' => '0',
				),

				'apply_btn' => array(
					'type' => 'checkbox',
					'title' => JText::_('COURSES_COURSE_APPLY_BTN'),
					'desc' => JText::_('COURSES_COURSE_APPLY_BTN_DESC'),
					'values' => array(
						'1' => JText::_('JYES'),
						'0' => JText::_('JNO')
					),
					'std' => '0',
				),

				'class' => array(
					'type' => 'text',
					'title' => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS'),
					'desc' => JText::_('COM_SPPAGEBUILDER_ADDON_CLASS_DESC'),
					'std' => ''
				)
			)
		)
	)
);
