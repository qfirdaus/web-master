<?php
/**

 * @package DJ-MegaMenu
 * @copyright Copyright (C) 2024 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: https://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski, Artur Kaczmarek
 *
 * DJ-MegaMenu is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MegaMenu is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MegaMenu. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die;

use Joomla\CMS\Filter\OutputFilter;

// Note. It is important to remove spaces between elements.
$title_attr = $item->anchor_title ? 'title="' . $item->anchor_title . '" ' : '';

//name
$name = ( false == $item_params->get('djmegamenu-hide-title', 0) && !empty($item->title) && empty($item->logo) ) ? '<span class="name">' . $item->title . '</span>' : '';

//subtitle
$subtitle = '';
if ( $params->get('subtitles') == 1 || $params->get('subtitles') == 2 ) {
	$subtitle = $item_params->get('djmegamenu-subtitle');
	if( !empty($subtitle) ) {
		$subtitle = '<small class="subtitle">' . $subtitle . '</small>';
		if ($item->level == $startLevel) $aclass .= ' withsubtitle';
	}
}

//badge
$badge = $item_params->get('djmegamenu-badge');
if( !empty($badge) ) {
	$badge_bg = ( $item_params->get('djmegamenu-badge-color', 0) ) ? 'background:' . $item_params->get('djmegamenu-badge-color', 0) . ';' : '';
	$badge_text = ( $item_params->get('djmegamenu-badge-text', 0) ) ? 'color:' . $item_params->get('djmegamenu-badge-text', 0) . ';' : '';
	$badge_style = ( !empty($badge_bg) || !empty($badge_text) ) ? ' style="' . $badge_bg . $badge_text . '"' : '';
	$badge = '<span class="dj-badge"'. $badge_style .'>' . $badge . '</span>';
	if ($item->level == $startLevel) $aclass .= ' withbadge';
}

$class = $item->anchor_css || !empty($aclass) ? 'class="' . $aclass . ' ' . $item->anchor_css . '" ' : '';

//access key
$accesskey = $item_params->get('djmegamenu-accesskey', '');
if (!empty($accesskey)) {
	$class .= ' accesskey="' . htmlspecialchars($accesskey) . '" ';
}

//icons & images
$icon = '';
if ($params->get('icons') == 1 || $params->get('icons') == 2 || !empty($item->logo)) {
	//icons
	$faicon = $item_params->get('djmegamenu-fa', '');
	$alt = htmlspecialchars($item->title);
	$width = isset($item->width)  ? 'width="'.$params->get('fixed_logo_width').'"' : '';
	$height = isset($item->height) ? 'height="'.$params->get('fixed_logo_height').'"' : '';
	if ( !empty($faicon) ) {
		if ( $item_params->get('menu_text', 1) ) {
			$icon = '<span class="dj-icon ' . $faicon . '" aria-hidden="true"></span>';
		} else {
			$icon = '<span class="dj-icon ' . $faicon . '" aria-hidden="true" title="' . $alt . '"></span>';
			$title_attr .= 'aria-label="' . $alt . '" ';
		}
		if ($item->level == $startLevel) $aclass .= ' withicon';
	} else if ( $item->menu_image ) { //images
		if( !empty($name) ) {
			$icon = '<img class="dj-icon" src="' . $item->menu_image . '" alt="" '.$width.' '.$height.' aria-hidden="true" />';
		} else {
			$icon = '<img class="dj-icon" src="' . $item->menu_image . '" alt="' . $alt . '" '.$width.' '.$height.'  />';
		}
		if ($item->level == $startLevel) $aclass .= ' withimage';
	}
}

$itemclass = ( !empty($icon) ) ? 'image-title' : 'title';
$linktype = $icon;
if( !empty($name) || !empty($badge) || !empty($subtitle) ) $linktype .= '<span class="' . $itemclass . '">' . $name . $badge . $subtitle . '</span>';

//parent stuff
$aria = '';
if ($item->parent && (!$endLevel || $item->level < $endLevel)) {
	$aria = ' aria-haspopup="true" aria-expanded="false" ';
	$linktype .= '<span class="arrow" aria-hidden="true"></span>';
}

//dropdown
if ($item->level == $startLevel) {
	$spanclass = ($item->parent) ? 'class="dj-drop" ' : '';
	$linktype = '<span ' . $spanclass . '>' . $linktype . '</span>';
}

//bbcode
$linktype = modDJMegaMenuHelper::parseBBcode($linktype);

//if($item->level > $startLevel) $linktype .= ' '.$expand[$item->parent_id];

switch ($item->type):
	case 'heading':
	case 'separator':
		$item->browserNav = 3;
		break;
	case 'component':
	case 'url':
	default:
		$flink = OutputFilter::ampReplace(htmlspecialchars($item->flink, ENT_COMPAT, 'UTF-8', false));
		break;
endswitch;

//custom HTML
$html_position = (bool) $item_params->get('djmegamenu-html-pos', 0);
$custom_attr = $item_params->get('djmegamenu-custom-attr', '');
$custom_attr = ( !empty($custom_attr) ) ? ' ' . $custom_attr : '';

$html_before = $item_params->get('djmegamenu-html-before', '');
if (!empty($html_before) && !$html_position) {
	$linktype = '<span class="html-before">' . $html_before . '</span>' . $linktype;
}

$html_after = $item_params->get('djmegamenu-html-after', '');
if (!empty($html_after) && !$html_position) {
	$linktype = $linktype . '<span class="html-after">' . $html_after . '</span>';
}

if (!empty($html_before) && $html_position) {
	echo '<div class="html-before">' . $html_before . '</div>';
}

//rel attr
$relattr = ($item->anchor_rel) ? 'rel="' . $item->anchor_rel . '" ' : '';

switch ($item->browserNav):
	default:
	case 0:
		?><a <?php echo $relattr . $class . $aria; ?>href="<?php echo $flink; ?>" <?php echo $title_attr; ?><?php echo $custom_attr; ?> role="menuitem"><?php echo $linktype; ?></a><?php
		break;
	case 1:
		// _blank
		?><a <?php echo $relattr . $class . $aria; ?>href="<?php echo $flink; ?>" target="_blank" <?php echo $title_attr; ?><?php echo $custom_attr; ?> role="menuitem"><?php echo $linktype; ?></a><?php
		break;
	case 2:
		// window.open
		$options = 'toolbar=no,location=no,status=no,menubar=no,scrollbars=yes,resizable=yes,' . $params->get('window_open');
		?><a <?php echo $relattr . $class . $aria; ?>href="<?php echo $flink; ?>" onclick="window.open(this.href,'targetWindow','<?php echo $options; ?>');return false;" <?php echo $title_attr; ?><?php echo $custom_attr; ?> role="menuitem"><?php echo $linktype; ?></a><?php
		break;
	case 3:
		?><a <?php echo $relattr . $class . $aria; ?> <?php echo $title_attr; ?> tabindex="0" <?php echo $custom_attr; ?> role="menuitem"><?php echo $linktype; ?></a><?php
endswitch;

if (!empty($html_after) && $html_position) {
	echo '<div class="html-after">' . $html_after . '</div>';
}
