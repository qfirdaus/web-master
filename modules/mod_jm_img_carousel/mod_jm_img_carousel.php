<?php
/*
 * Copyright (C) joomla-monster.com
 * Website: http://www.joomla-monster.com
 * Support: info@joomla-monster.com
 *
 * JM Image Carousel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JM Image Carousel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JM Image Carousel. If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$version = new JVersion;
$jversion = '3';
if (version_compare($version->getShortVersion(), '3.0.0', '<')) {
		$jversion = '2.5';
}

$doc = JFactory::getDocument();

$moduleId = $module->id;

$id = 'jmm-img-carousel-' . $moduleId;

$data = trim( $params->get('slides') );

$json_data = ( !empty($data) ) ? json_decode($data) : false;

if ($json_data === false) {
	echo JText::_('MOD_JM_JM_IMG_CAROUSEL_NO_ITEMS');
	return false;
}

$field_pattern = '#^jform\[params\]\[([a-zA-Z0-9\_\-]+)\]#i';

$output_data = array();
foreach ($json_data as $item) {
	$item_obj = new stdClass();
	foreach($item as $field) {
		if (preg_match($field_pattern, $field->name, $matches)) {
			$attr = $matches[1];
			if (isset($item_obj->$attr)) {
				if (is_array($item_obj->$attr)) {
					$temp = $item_obj->$attr;
					$temp[] = $field->value;
					$item_obj->$attr = $temp;
				} else {
					$temp = array($item_obj->$attr);
					$temp[] = $field->value;
					$item_obj->$attr = $temp;
				}
			} else {
				$item_obj->$attr = $field->value;
			}
		}
	}
	$output_data[] = $item_obj;
}

$elements = count($output_data);

if( $elements < 1 ) {
	echo JText::_('MOD_JM_JM_IMG_CAROUSEL_NO_ITEMS');
	return false;
}
JHtml::_('jquery.framework', true);

$doc->addStyleSheet(JURI::root(true).'/modules/mod_jm_img_carousel/assets/jquery.flipster.css');
$doc->addScript(JURI::root(true).'/modules/mod_jm_img_carousel/assets/jquery.flipster.min.js');
$doc->addScript(JURI::root(true).'/modules/mod_jm_img_carousel/assets/script.js');

$link_target = $params->get('link_target', 0);
$target_attribute = ( $link_target == 1 ) ? '_blank' : '_self';

$show_indicators = $params->get('show_indicators', 0);
$nav = ( $show_indicators == 1 ) ? '"after"' : 'false';
$indicators = ( $show_indicators == 1 ) ? true : false;

$loop_param = $params->get('loop', 0);
$loop = ( $loop_param == 1 ) ? 'true' : 'false';

$buttons_param = $params->get('buttons', 0);
$buttons = ( $buttons_param == 1 ) ? 'true' : 'false';

$scroll_param = $params->get('scroll', 0);
$scroll = ( $scroll_param == 1 ) ? 'true' : 'false';

$style_param = $params->get('carousel_style', 1);

if( $style_param == 2 ) {
	$style = 'coverflow';
} elseif( $style_param == 3 ) {
	$style = 'flat';
} else {
	$style = 'carousel';
}



$doc->addScriptDeclaration('
	jQuery(document).ready(function() {
		jmm_image_carousel({
			id: "#' . $id . '",
			style: "' . $style . '",
			spacing: -0.4,
			nav: ' . $nav . ',
			buttons: ' . $buttons . ',
			loop: ' . $loop . ',
			scrollwheel: ' . $scroll . ',
			label_prev: "' . JText::_('MOD_JM_JM_IMG_CAROUSEL_LABEL_PREV') . '",
			label_next: "' . JText::_('MOD_JM_JM_IMG_CAROUSEL_LABEL_NEXT') . '",
			label_indicators: "' . JText::_('MOD_JM_JM_IMG_CAROUSEL_LABEL_INDICATORS') . '"
		});
	});
');

$mod_class_suffix = $params->get('moduleclass_sfx', '');

require JModuleHelper::getLayoutPath('mod_jm_img_carousel', $params->get('layout', 'default'));

?>
