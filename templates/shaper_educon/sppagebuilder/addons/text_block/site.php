<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2017 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('restricted aceess');

class SppagebuilderAddonText_block extends SppagebuilderAddons
{

    public function render()
    {

        $class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
        $title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
        $title_url = (isset($this->addon->settings->title_url) && $this->addon->settings->title_url) ? $this->addon->settings->title_url : '';
        $heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

        //Options
        $text = (isset($this->addon->settings->text) && $this->addon->settings->text) ? $this->addon->settings->text : '';
        $alignment = (isset($this->addon->settings->alignment) && $this->addon->settings->alignment) ? $this->addon->settings->alignment : '';

        if ($title_url == '') {
            $title_url = $title;
        } else {
            $title_url = '<a href="' . $title_url . '">' . $title . '</a>';
        }

        //Output
        $output = '<div class="sppb-addon sppb-addon-text-block ' . $alignment . ' ' . $class . '">';
        $output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title">' . $title_url . '</' . $heading_selector . '>' : '';
        $output .= '<div class="sppb-addon-content">';
        $output .= $text;
        $output .= '</div>';
        $output .= '</div>';

        return $output;
    }
}
