<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2016 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class SplmsViewEvents extends FOFViewForm{

	public function display($tpl = null){
		// Get model
		$model = $this->getModel();
		$this->items = $model->getItemList();

		// Import Joomla component helper
		jimport('joomla.application.component.helper');
		jimport('joomla.filesystem.file');

		// Get Thumb Size
		$lmsparams = JComponentHelper::getParams('com_splms');
		$thumb_size = strtolower($lmsparams->get('event_thumbnail', '480X300'));

		foreach ($this->items as &$this->item) {
			
			$this->item->url = JRoute::_('index.php?option=com_splms&view=event&id=' . $this->item->splms_event_id . ':' . $this->item->slug . SplmsHelper::getItemid('event'));

			$filename = basename($this->item->image);
			$path = JPATH_BASE .'/'. dirname($this->item->image) . '/thumbs/' . JFile::stripExt($filename) . '_' . $thumb_size . '.' . JFile::getExt($filename);
			$src = JURI::base(true) . '/' . dirname($this->item->image) . '/thumbs/' . JFile::stripExt($filename) . '_' . $thumb_size . '.' . JFile::getExt($filename);

			if(JFile::exists($path)) {
				$this->item->thumb = $src;
			} else {
				$this->item->thumb = $this->item->image;	
			}
		
		}

		
		
		return parent::display($tpl = null);
	}

}