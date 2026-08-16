<?php
/**
 * @package     SP LMS
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class SplmsHelperCommon {

	// Get Course By Course id
	public static function getCategoryById($id) {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('id', 'title', 'alias', 'icon', 'description')));
		$query->from($db->quoteName('#__splms_coursescategories'));
		$query->where($db->quoteName('published')." = 1");
		$query->where($db->quoteName('id')." = ".$db->quote($id));
		$db->setQuery($query);
		$result = $db->loadObject();
		$result->url = JRoute::_('index.php?option=com_splms&view=coursescategory&id='.$result->id.':'.$result->alias . SplmsHelper::getItemid('coursescategories'));

		return $result;
	}

	// Get Event Speakers by event
	public static function getEventSpeakers($ids) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		if (is_array($ids) == false) {
			$replaceArr  	 = array("\"","[","]");
			$speakerids 	 = str_replace( $replaceArr,"", $ids);
		}else{
			$speakerids = implode(',', $ids);
		}


		if (!empty($speakerids)) {
			$query->select('*');
			$query->from($db->quoteName('#__splms_speakers'));
			$query->where($db->quoteName('published')." = 1");
			$query->where($db->quoteName('id')." IN (". $speakerids . ")");
			$query->order('ordering DESC');

			$db->setQuery($query);

			$items = $db->loadObjectList();

			foreach ($items as &$item) {
				$item->url = JRoute::_('index.php?option=com_splms&view=speaker&id='.$item->id.':'.$item->alias . SplmsHelper::getItemid('speakers'));
			}
		}else{
			$items='';
		}

		return $items;
	}

}
