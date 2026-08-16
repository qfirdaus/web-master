<?php

/**
 * @package Educon
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2022 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');

class SppagebuilderAddonSocial_icons extends SppagebuilderAddons
{
	public function render()
	{
		$class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
		$title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
		$heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

		//output start
		$output = '';
		$output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title . '</' . $heading_selector . '>' : '';

		$output .= '<div class="sppb-social-icons ' . $class . '" >';

		foreach ($this->addon->settings->sp_social_icons_item as $key => $social_item) {
			$output .= '<span style="display:inline-block;">';
			$output .= '<a href="' . $social_item->url . '">';
			$output .= '<i class="fa ' . $social_item->icon . ' "></i>';
			$output .= '</a>';
			$output .= '</span>';
		}

		$output .= '</div>'; //END:: /.sppb-social-icon-wrapper

		return $output;
	}

	public static function getTemplate()
	{
		$output = '
			<# var heading_selector = data.heading_selector || "h3";
			if( !_.isEmpty( data.title ) ){ #>
				<{{ heading_selector }} class="sppb-addon-title">{{ data.title }}</{{ heading_selector }}>
			<# } #>
			<div class="sppb-social-icons {{data.class}} " >
			
			<# _.each (data.sp_social_icons_item, function(social_item) { #>
				<span style="display:inline-block;">
				<a href="{{social_item.url}}">
				<i class="fa {{social_item.icon}} "></i>
				</a>
				</span>
			<# }); #>

			</div>
		';

		return $output;
	}
}
