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

// no direct access
defined('_JEXEC') or die('Restricted access');
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

use Joomla\CMS\Factory;
use Joomla\CMS\Version;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;

$version = new Version();
$isJoomla4 = version_compare($version->getShortVersion(), '4', '>=');

if($isJoomla4) {
	require_once(dirname(__FILE__).'/helpers/ModMenuHelper.php');
} else {
	require_once JPATH_ROOT.'/modules/mod_menu/helper.php';
}

// Include the syndicate functions only once
require_once dirname(__FILE__).'/helpers/helperversion.php';

modDJMegaMenuHelper::parseParams($params);

$params->set('module_id', $module->id);
$startLevel = $params->get('startLevel');
$endLevel = $params->get('endLevel');

$list		= modDJMegaMenuHelper::getList($params);

if(!count($list)) return;

$subwidth	= modDJMegaMenuHelper::getSubWidth($params);
$subcols	= modDJMegaMenuHelper::getSubCols($params);
$expand		= modDJMegaMenuHelper::getExpand($params);
$active		= modDJMegaMenuHelper::getActive($params);
$active_id	= $active->id;
$path		= $active->tree;

$showAll	= $params->get('showAllChildren');
$class_sfx	= ($params->get('hasSubtitles') ? 'hasSubtitles ':'') . htmlspecialchars($params->get('moduleclass_sfx',''));

$app = Factory::getApplication();
$doc = Factory::getApplication()->getDocument();
$direction = $doc->direction;

//$app->enqueueMessage("<pre>".print_r($parents, true)."</pre>");

$canDefer = preg_match('/(?i)msie [6-9]/', @$_SERVER['HTTP_USER_AGENT']) ? false : true;

HTMLHelper::_('jquery.framework');

// direction integration with joomla monster templates
if ($app->input->get('direction') == 'rtl'){
	$direction = 'rtl';
} else if ($app->input->get('direction') == 'ltr') {
	$direction = 'ltr';
} else {
	if (isset($_COOKIE['jmfdirection'])) {
		$direction = $_COOKIE['jmfdirection'];
	} else {
		$direction = $app->input->get('jmfdirection', $direction);
	}
}

$ver = modDJMegaMenuHelper::getVersion($params);
$template = $app->getTemplate();

//use minified js and css files
$minified = ( true ) ? '.min' : '';

modDJMegaMenuHelper::addTheme($params, $direction);

$theme = $params->get('theme');
$wcag = $params->get('wcag', 1);
$custom_colors = $params->get('customColors', 0);

if( $params->get('moo',1) ) { //css & animations enabled

	$doc->addScript(Uri::root(true).'/modules/mod_djmegamenu/assets/js/jquery.djmegamenu' . $minified . '.js', array('version' => $ver), array('defer' => $canDefer));

	if(!is_numeric($openDelay = $params->get('openDelay'))) $openDelay = 250;
	if(!is_numeric($closeDelay = $params->get('closeDelay'))) $closeDelay = 500;

	$wrapper_id = $params->get('wrapper');
	$animIn = $params->get('animation_in');
	$animOut = $params->get('animation_out');
	$animSpeed = $params->get('animation_speed');
	$open_event = $params->get('event', 'mouseenter');
	$close_event = $params->get('eventClose', 'mouseleave');
	$parentOpen = $params->get('parentOpen', 0);
	$fixed = $params->get('fixed', 0);
	$fixed_offset = $params->get('fixed_offset', 0);
	$overlay = $params->get('overlay', 0);

	$options = json_encode(array(
		'wrap'         => $wrapper_id,
		'animIn'       => $animIn,
		'animOut'      => $animOut,
		'animSpeed'    => $animSpeed,
		'openDelay'    => $openDelay,
		'closeDelay'   => $closeDelay,
		'event'        => $open_event,
		'eventClose'   => $close_event,
		'parentOpen'   => $parentOpen,
		'fixed'        => $fixed,
		'offset'       => $fixed_offset,
		'theme'        => $theme,
		'direction'    => $direction,
		'wcag'         => $wcag,
		'overlay'      => $overlay ));

	//animations

	$animate_local = (int) $params->get('animate_local', 0);

	if ($animate_local) {
		$doc->addStyleSheet(Uri::root(true).'/modules/mod_djmegamenu/assets/css/animate.compat.min.css', array('version' => $ver));
	} else {
		$animLink = 'https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.compat.min.css';
		$doc->addCustomTag( '<link rel="stylesheet preload" href="' . $animLink . '" as="style">' );
		$doc->addStyleSheet($animLink);
	}

	//really, we should make this deprecated
	$extraAnimations = array('zoomInX', 'zoomOutX', 'zoomInY', 'zoomOutY');
	if(in_array($animIn, $extraAnimations) || in_array($animOut, $extraAnimations) ) {
		$doc->addStyleSheet(Uri::root(true).'/modules/mod_djmegamenu/assets/css/animate_ext' . $minified . '.css', array('version' => $ver));
	}
}

$mobilemenu = (int) $params->get('select', 0);
if($mobilemenu) {
	$mobilewidth = (int)$params->get('width');
	$doc->addStyleDeclaration("
		@media (min-width: " . ( $mobilewidth + 1 ) . "px) { #dj-megamenu$module->id"."mobile { display: none; } }
		@media (max-width: " . $mobilewidth . "px) { #dj-megamenu$module->id, #dj-megamenu$module->id"."sticky, #dj-megamenu$module->id"."placeholder { display: none !important; } }
	");

	if($mobilemenu == 2) {
		$position = $params->get('offcanvas_pos', 'left') == 'right' ? '_right':'';
		$doc->addStyleSheet(Uri::root(true).'/modules/mod_djmegamenu/assets/css/offcanvas'.$position . $minified.'.css', array('version' => $ver));
		$offmodules = array();
		if($params->get('pro')) {
			$offmodules['top'] = modDJMegaMenuHelper::loadModules('dj-offcanvas-top', $params->get('offcanvas_topmod_style','xhtml'));
			$offmodules['bottom'] = modDJMegaMenuHelper::loadModules('dj-offcanvas-bottom', $params->get('offcanvas_botmod_style','xhtml'));
		}
	}

	if($mobilemenu > 0) {
		$doc->addScript(Uri::root(true).'/modules/mod_djmegamenu/assets/js/jquery.djmobilemenu' . $minified . '.js', array('version' => $ver), array('defer' => $canDefer));
		modDJMegaMenuHelper::addMobileTheme($params, $direction);
	}
}

//font awesome
$fa = $params->get('fa', 5);
$fa_class = 'dj-fa-' . $fa;

if( $fa == 5 ) {
	$fa5_link  = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css';
	$fa5_shims = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/v4-shims.min.css';
	$doc->addCustomTag( '<link rel="stylesheet preload" href="' . $fa5_link . '" as="style">' );
	$doc->addCustomTag( '<link rel="stylesheet preload" href="' . $fa5_shims . '" as="style">' );
	$doc->addStyleSheet($fa5_link);
	$doc->addStyleSheet($fa5_shims);
} else if( $fa == 1 ) {
	$fa4_link = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css';
	$doc->addCustomTag( '<link rel="stylesheet preload" href="' . $fa4_link . '" as="style">' );
	$doc->addStyleSheet($fa4_link);
}

if($theme=='_override'|| $theme=='override') {
	$doc->addStyleSheet(Uri::root(true).'/modules/mod_djmegamenu/assets/css/theme_override' . $minified . '.css', array('version' => $ver));
}

require(ModuleHelper::getLayoutPath('mod_djmegamenu', $params->get('layout', 'default')));
