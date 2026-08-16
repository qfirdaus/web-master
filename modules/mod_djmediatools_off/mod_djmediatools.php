<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// Include the syndicate functions only once
require_once(JPATH_ROOT.'/components/com_djmediatools/helpers/helper.php');
JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_djmediatools/models', 'DJMediatoolsModel');
JModelLegacy::addTablePath(JPATH_ADMINISTRATOR.'/components/com_djmediatools/tables');

$lang = JFactory::getLanguage();
$lang->load('com_djmediatools', JPATH_SITE, 'en-GB', false, false);
$lang->load('com_djmediatools', JPATH_SITE . '/components/com_djmediatools', 'en-GB', false, false);
$lang->load('com_djmediatools', JPATH_SITE, null, true, false);
$lang->load('com_djmediatools', JPATH_SITE . '/components/com_djmediatools', null, true, false);

$model = JModelLegacy::getInstance('Categories', 'DJMediatoolsModel', array('ignore_request'=>true));
$model->setState('category.id', $params->get('catid'));		
$model->setState('filter.published', 1);
$cparams = $model->getParams(false, $params);
$category = $model->getItem($params->get('catid'));
$params = $cparams;

if ($category === false) {
	//JError::raiseError(404, JText::_('COM_DJMEDIATOOLS_ERROR_CATEGORY_NOT_FOUND'));
	return false;
}

// get gallery slides and layout
$helper = DJMediatoolsLayoutHelper::getInstance($params->get('layout', 'slideshow'));

$mid = $category->id.'m'.$module->id;
$params->set('gallery_id',$mid);
$params->set('category',$category->id);
$params->set('source',$category->source);
$params = $helper->getParams($params);
$slides = $helper->getSlides($params);

$mtParams = JComponentHelper::getParams('com_djmediatools');
$doc = JFactory::getDocument();

if($mtParams->get('counting_clicks')) {
    $doc->addScript(JUri::root(true).'/modules/mod_djmediatools_albums/assets/clicks.js');
}

if($mtParams->get('counting_views')) {
    $doc->addScript(JUri::root(true).'/modules/mod_djmediatools_albums/assets/views.js');
}


if($slides) {
	$helper->addScripts($params);
	$helper->addStyles($params);
	$navigation = $helper->getNavigation($params);
} else {
	//echo JText::_('COM_DJMEDIATOOLS_EMPTY_CATEGORY');
	return false;
}



require JModuleHelper::getLayoutPath('mod_djmediatools', $params->get('layout', 'slideshow'));

?>
