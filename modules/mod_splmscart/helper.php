<?php
/**
* @package com_splms
* @subpackage  mod_splmscart
*
* @author JoomShaper http://www.joomshaper.com
* @copyright Copyright (c) 2010 - 2017 JoomShaper
* @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');


class ModSplmscartHelper {

	public static function getCourseInfo($course_id) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select('*');
		$query->from($db->quoteName('#__splms_courses'));
		$query->where($db->quoteName('published') . ' = 1');
		$query->where('language in (' . $db->quote(JFactory::getLanguage()->getTag()) . ',' . $db->quote('*') . ')');
		$query->where($db->quoteName('access')." IN (" . implode( ',', JFactory::getUser()->getAuthorisedViewLevels() ) . ")");
		$query->where($db->quoteName('id') . ' = ' . $course_id);
		$db->setQuery($query);
		$item = $db->loadObject();

		$item->url = JRoute::_('index.php?option=com_splms&view=course&id='.$item->id.':'.$item->alias . SplmsHelper::getItemid('courses'));

		return $item;
	}

}
