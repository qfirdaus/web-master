<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2015 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class SplmsViewCourses extends FOFViewForm{

	public function display($tpl = null){
		// Get model
		$model = $this->getModel();
		$this->items = $model->getItemList();

		// Load Lessons model
		$lessons_model = FOFModel::getTmpInstance('Lessons', 'SplmsModel');


		// Loaad jQuary framework
		JHtml::_('jquery.framework');

		// Add Script
		$doc = JFactory::getDocument();
		$doc->addScript( JURI::root(true) . '/components/com_splms/assets/js/matchheight.js' );

		//Joomla Component Helper & Get LMS Params
		jimport('joomla.application.component.helper');
		$this->params = JComponentHelper::getParams('com_splms');

		//Get Currency
		$this->currency = explode(':', $this->params->get('currency', 'USD:$'));
		$this->currency =  $this->currency[1];

		// Get Coumn
		$this->columns = $this->params->get('columns', '3');

		// Get image thumb
		$this->thumb_size = strtolower($this->params->get('course_thumbnail', '480X300'));

	

		foreach ($this->items as $key => $this->item) {

			//print_r($this->item);
			// Generate URL
			$this->item->url = JRoute::_('index.php?option=com_splms&view=course&id=' . $this->item->splms_course_id . ':' . $this->item->slug . SplmsHelper::getItemid('course'));

			// Get course teachers
			$this->item->teachers = $model->getCourseTeachers( $this->item->splms_course_id );
			// Get course lessons
			$this->item->lessons  = $lessons_model->getLessons( $this->item->splms_course_id );

			// Count course lesosns Attachments
			$this->item->total_attachments = array();
			foreach ($this->item->lessons as $lesson){
				if ($lesson->attachment) {
					$this->item->total_attachments[] = $lesson->attachment;
				}
			} // END:: foreach

			// Get Prices
			if ($this->item->price == 0) {
				$this->item->course_price = JText::_('COM_SPLMS_FREE');
			}else{
				$this->item->course_price = SplmsHelper::generateCurrency($this->item->price);
			}

			// image thumb size
			$filename = basename($this->item->image);
			$path = JPATH_BASE .'/'. dirname($this->item->image) . '/thumbs/' . JFile::stripExt($filename) . '_' . $this->thumb_size . '.' . JFile::getExt($filename);
			$src = JURI::base(true) . '/' . dirname($this->item->image) . '/thumbs/' . JFile::stripExt($filename) . '_' . $this->thumb_size . '.' . JFile::getExt($filename);
			
			if(JFile::exists($path)) {
				$this->item->thumb = $src;
			} else {
				$this->item->thumb = $this->item->image;	
			}

		}


		
		return parent::display($tpl = null);
	}

}