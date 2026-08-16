<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2022 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */
//no direct accees
defined('_JEXEC') or die('restricted aceess');

class SppagebuilderAddonCall_to_action extends SppagebuilderAddons
{

    public function render()
    {
        $class = (isset($this->addon->settings->class) && $this->addon->settings->class) ? $this->addon->settings->class : '';
        $style = (isset($this->addon->settings->style) && $this->addon->settings->style) ? $this->addon->settings->style : 'panel-default';
        $title = (isset($this->addon->settings->title) && $this->addon->settings->title) ? $this->addon->settings->title : '';
        $heading_selector = (isset($this->addon->settings->heading_selector) && $this->addon->settings->heading_selector) ? $this->addon->settings->heading_selector : 'h3';

        //Addon Options
        $subtitle = (isset($this->addon->settings->subtitle) && $this->addon->settings->subtitle) ? $this->addon->settings->subtitle : '';
        $text = (isset($this->addon->settings->text) && $this->addon->settings->text) ? $this->addon->settings->text : '';
        $button_text = (isset($this->addon->settings->button_text) && $this->addon->settings->button_text) ? $this->addon->settings->button_text : '';
        $button_url = (isset($this->addon->settings->button_url) && $this->addon->settings->button_url) ? $this->addon->settings->button_url : '';
        $button_classes = (isset($this->addon->settings->button_size) && $this->addon->settings->button_size) ? ' sppb-btn-' . $this->addon->settings->button_size : '';
        $button_classes .= (isset($this->addon->settings->button_type) && $this->addon->settings->button_type) ? ' sppb-btn-' . $this->addon->settings->button_type : '';
        $button_classes .= (isset($this->addon->settings->button_shape) && $this->addon->settings->button_shape) ? ' sppb-btn-' . $this->addon->settings->button_shape : ' sppb-btn-rounded';
        $button_classes .= (isset($this->addon->settings->button_appearance) && $this->addon->settings->button_appearance) ? ' sppb-btn-' . $this->addon->settings->button_appearance : '';
        $button_classes .= (isset($this->addon->settings->button_block) && $this->addon->settings->button_block) ? ' ' . $this->addon->settings->button_block : '';
        $button_icon = (isset($this->addon->settings->button_icon) && $this->addon->settings->button_icon) ? $this->addon->settings->button_icon : '';
        $button_icon_position = (isset($this->addon->settings->button_icon_position) && $this->addon->settings->button_icon_position) ? $this->addon->settings->button_icon_position : 'left';

        $button_position = (isset($this->addon->settings->button_position) && $this->addon->settings->button_position) ? $this->addon->settings->button_position : '';
        $button_attribs = (isset($this->addon->settings->button_target) && $this->addon->settings->button_target) ? ' target="' . $this->addon->settings->button_target . '"' : '';
        $button_attribs .= (isset($this->addon->settings->button_url) && $this->addon->settings->button_url) ? ' href="' . $this->addon->settings->button_url . '"' : '';

        // Generate Button
        if ($button_icon_position == 'left') {
            $button_text = ($button_icon) ? '<i class="fa ' . $button_icon . '"></i> ' . $button_text : $button_text;
        } else {
            $button_text = ($button_icon) ? $button_text . ' <i class="fa ' . $button_icon . '"></i>' : $button_text;
        }
        $button_output = '<a' . $button_attribs . ' id="btn-' . $this->addon->id . '" class="sppb-btn' . $button_classes . '">' . $button_text . '</a>';

        // Addon Output
        $output = '<div class="sppb-addon sppb-addon-cta ' . $class . '">';

        if ($button_position == 'right') {
            $output .= '<div class="sppb-row">';
            $output .= '<div class="sppb-col-sm-9">';
            $output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title sppb-cta-title">' . $title . '</' . $heading_selector . '>' : '';
            $output .= ($subtitle) ? '<p class="sppb-lead sppb-cta-subtitle">' . $subtitle . '</p>' : '';
            $output .= ($text) ? '<p class="sppb-cta-text">' . $text . '</p>' : '';
            $output .= '</div>';
            $output .= '<div class="sppb-col-sm-3 sppb-text-right">';
            if (!empty($button_text)) {
                $output .= $button_output;
            }
            $output .= '</div>';
            $output .= '</div>';
        } else {
            $output .= '<div class="text-center">';
            $output .= ($title) ? '<' . $heading_selector . ' class="sppb-addon-title sppb-cta-title">' . $title . '</' . $heading_selector . '>' : '';
            $output .= ($subtitle) ? '<p class="sppb-lead sppb-cta-subtitle">' . $subtitle . '</p>' : '';
            $output .= ($text) ? '<p class="sppb-cta-text">' . $text . '</p>' : '';
            $output .= '<div>';
            if (!empty($button_text)) {
                $output .= $button_output;
            }
            $output .= '</div>';
            $output .= '</div>';
        }

        $output .= '</div>';

        return $output;
    }

    public function css()
    {
        $addon_id = '#sppb-addon-' . $this->addon->id;
        $layout_path = JPATH_ROOT . '/components/com_sppagebuilder/layouts';
        $css_path = new JLayoutFile('addon.css.button', $layout_path);
        $number_style = '';
        $text_style = '';

        $style = (isset($this->addon->settings->background) && $this->addon->settings->background) ? "background-color: " . $this->addon->settings->background . ";" : '';
        $style .= (isset($this->addon->settings->color) && $this->addon->settings->color) ? "color: " . $this->addon->settings->color . ";" : '';
        $style .= (isset($this->addon->settings->padding) && $this->addon->settings->padding) ? "padding: " . $this->addon->settings->padding . ";" : "padding: 40px 20px;";

        // Sub title
        $subtitle_style = (isset($this->addon->settings->subtitle_text_color) && $this->addon->settings->subtitle_text_color) ? 'color:' . $this->addon->settings->subtitle_text_color . ';' : '';
        $subtitle_style .= (isset($this->addon->settings->subtitle_fontsize) && $this->addon->settings->subtitle_fontsize) ? 'font-size: ' . $this->addon->settings->subtitle_fontsize . 'px; line-height: ' . $this->addon->settings->subtitle_fontsize . 'px;' : '';

        $css = '';
        if ($style) {
            $css .= $addon_id . ' .sppb-addon-cta {';
            $css .= $style;
            $css .= '}';
        }

        if ($subtitle_style) {
            $css .= $addon_id . ' .sppb-cta-subtitle {';
            $css .= $subtitle_style;
            $css .= '}';
        }

        // Button options
        $css .= $css_path->render(array('addon_id' => $addon_id, 'options' => $this->addon->settings, 'id' => 'btn-' . $this->addon->id));;

        return $css;
    }

    public static function getTemplate()
    {
        $output = '
                <#
                    var contentClass = (!_.isEmpty(data.class)) ? data.class : "";
                    var title = (!_.isEmpty(data.title)) ? data.title : "";
                    var heading_selector = (!_.isEmpty(data.heading_selector)) ? data.heading_selector : "h3";
                    var subtitle = (!_.isEmpty(data.subtitle)) ? data.subtitle : "";
                    var subtitleFontSize = (!_.isEmpty(data.subtitle_fontsize)) ? data.subtitle_fontsize : "";
                    var text = (!_.isEmpty(data.text)) ? data.text : "";
                    var button_text = (!_.isEmpty(data.button_text)) ? data.button_text : "";
                    var button_url = (!_.isEmpty(data.button_url)) ? data.button_url : "";
                    var button_classes = (!_.isEmpty(data.button_size)) ? " sppb-btn-" + data.button_size : "";
                        button_classes += (!_.isEmpty(data.button_type)) ? " sppb-btn-" + data.button_type : "";
                        button_classes += (!_.isEmpty(data.button_shape)) ? " sppb-btn-" + data.button_shape: " sppb-btn-rounded";
                        button_classes += (!_.isEmpty(data.button_appearance)) ? " sppb-btn-" + data.button_appearance : "";
                        button_classes += (!_.isEmpty(data.button_block)) ? \' \' + data.button_block : "";
                    var button_icon = (!_.isEmpty(data.button_icon) && data.button_icon) ? data.button_icon : "";
                    var button_icon_position = (!_.isEmpty(data.button_icon_position)) ? data.button_icon_position: "left";

                    var button_position = (!_.isEmpty(data.button_position) && data.button_position) ? data.button_position : "";
                    var button_attribs = (!_.isEmpty(data.button_target)) ? \' target="data.button_target"\' : "";
                        button_attribs += (!_.isEmpty(data.button_url)) ? \' href="data.button_url"\' : "";

                    if (button_icon_position == "left") {
                        button_text = (button_icon) ? \'<i class="fa \' + button_icon + \'"></i> \' + button_text : button_text;
                    } else {
                        button_text = (button_icon) ? button_text + \' <i class="fa \' + button_icon + \'"></i>\' : button_text;
                    }
                    var button_output = \'<a\' + button_attribs + \' id="btn-\' + data.id + \'" class="sppb-btn\' + button_classes + \'">\' + button_text + \'</a>\';
                    #>

		<div class="sppb-addon sppb-addon-cta {{contentClass}}">
                <# if(button_position=="right") { #>
			<div class="sppb-row">
			<div class="sppb-col-sm-9">
                        <# if (!_.isEmpty(title)){ #>
			<{{{heading_selector}}} class="sppb-addon-title sppb-cta-title">{{{title}}}</{{{heading_selector}}}>
                         <# } #>
			<# if (!_.isEmpty(subtitle)){ #>
                        <p class="sppb-lead sppb-cta-subtitle" style="font-size: {{subtitleFontSize}}px; color: {{data.subtitle_text_color}};"> {{{subtitle}}}</p>
                        <# } #>
                        <# if (!_.isEmpty(text)){ #>
			 <p class="sppb-cta-text">{{{text}}}</p>
                         <# } #>
			</div>
			<div class="sppb-col-sm-3 sppb-text-right">
			<# if (!_.isEmpty(button_text)) { #>
                            {{{button_output}}}
			<# } #>
			</div>
			</div>
		<# } else { #>
                    <div class="text-center">
                    <# if (!_.isEmpty(title)){ #>
                     <{{{heading_selector}}} class="sppb-addon-title sppb-cta-title">{{{title}}}</{{heading_selector}}>
                     <# } #>
                     <# if (!_.isEmpty(subtitle)){ #>
                     <p class="sppb-lead sppb-cta-subtitle" style="font-size: {{subtitleFontSize}}px; color: {{data.subtitle_text_color}};">{{{subtitle}}}</p>
                     <# } #>
                     <# if (!_.isEmpty(text)){ #>
                     <p class="sppb-cta-text">{{{text}}}</p>
                     <# } #>
                    <div>
                    <# if (!_.isEmpty(button_text)) { #>
                        {{{button_output}}}
                    <# } #>
                    </div>
                    </div>
		<# } #>

		</div>';
        return $output;
    }
}
