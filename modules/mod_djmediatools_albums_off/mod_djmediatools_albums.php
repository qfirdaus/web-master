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

$lang = JFactory::getLanguage();
$lang->load('com_djmediatools', JPATH_SITE, 'en-GB', false, false);
$lang->load('com_djmediatools', JPATH_SITE . '/components/com_djmediatools', 'en-GB', false, false);
$lang->load('com_djmediatools', JPATH_SITE, null, true, false);
$lang->load('com_djmediatools', JPATH_SITE . '/components/com_djmediatools', null, true, false);

JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_djmediatools/models', 'DJMediatoolsModel');
$model = JModelLegacy::getInstance('Categories', 'DJMediatoolsModel', array('ignore_request'=>true));
$model->setState('category.id', $params->get('catid'));		
$model->setState('filter.published', 1);
$model->setState('list.start',0);
$model->setState('list.limit',0);
$model->setState('list.ordering', 'ordering');
$model->setState('list.direction', 'asc');

$category = $model->getItem($params->get('catid'));
$subcats = $model->getItems();

if ($category === false || !count($subcats)) {
	return false;
}

require_once JPATH_ROOT.'/components/com_djmediatools/helpers/route.php';

$url = JRoute::_(DJMediatoolsHelperRoute::getCategoriesRoute((int)$params->get('catid')));


$mtParams = JComponentHelper::getParams('com_djmediatools');
$doc = JFactory::getDocument();

if($mtParams->get('counting_clicks')) {
    $doc->addScript(JUri::root(true).'/modules/mod_djmediatools_albums/assets/clicks.js');
}

if($mtParams->get('counting_views')) {
    $doc->addScript(JUri::root(true).'/modules/mod_djmediatools_albums/assets/views.js');
}


$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::root(true).'/components/com_djmediatools/assets/css/default.css');
$doc->addStyleSheet(JUri::root(true).'/modules/mod_djmediatools_albums/assets/style.css');
$doc->addScript(JUri::root(true).'/modules/mod_djmediatools_albums/assets/script.js');

require JModuleHelper::getLayoutPath('mod_djmediatools_albums', $params->get('layout', 'default'));

?>
