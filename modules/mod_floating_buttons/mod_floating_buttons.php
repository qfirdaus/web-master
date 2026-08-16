<?php
/**
 * @copyright	Copyright © 2020 - All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @author	https://templateplazza.com
 */
defined('_JEXEC') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Uri\Uri;

$doc = Factory::getDocument();
// Include assets
$doc->addStyleSheet(Uri::root()."modules/mod_floating_buttons/assets/css/style.min.css?v=2.0.0");
$css_adjustment = $params->get('css_adjustment');
if($css_adjustment){
    $doc->addStyleDeclaration($css_adjustment);
}
$main_item_position = $params->get('main_item_position');
switch ($main_item_position) {
    case 1:
        $fabposition = "left";
        break;
    case 2:
        $fabposition = "topright";
        break;
    case 3:
        $fabposition = "topleft";
        break;
    default:
        $fabposition = "right";
}
$doc->addStyleSheet('https://fonts.googleapis.com/icon?family=Material+Icons');
$doc->addScript(Uri::root()."modules/mod_floating_buttons/assets/js/script.min.js?v=2.0.0");
$main_item_bgcolor		    = $params->get("main_item_bgcolor","#4285F4");
$main_item_icon		        = $params->get("main_item_icon","&#xE145;");
$main_item_icon_custom		= $params->get("main_item_icon_custom");
$main_item_color		    = $params->get("main_item_color","#FFFFFF");
$child_item_items		    = $params->get("child_item_items");
$main_item_is_rotated		= $params->get("main_item_is_rotated",'true');
$mod_visibility		        = $params->get("mod_visibility");
$tooltip_theme		        = $params->get("tooltip_theme");

if($mod_visibility == "1") {
    $visibility_class =" visible-xs";
}elseif($mod_visibility == "2"){
    $visibility_class =" hidden-md hidden-lg";
}else {
    $visibility_class ="";
}

if($tooltip_theme == "0"){
    $tooltip_class = "tooltip_dark";
} else {
    $tooltip_class = "tooltip_light";
}

$user = Factory::getUser();
$levels = $user->getAuthorisedViewLevels();

require ModuleHelper::getLayoutPath('mod_floating_buttons', $params->get('layout', 'default'));