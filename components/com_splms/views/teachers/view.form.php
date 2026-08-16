<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2016 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class SplmsViewTeachers extends FOFViewForm{
	//display function
	public function display( $tpl = null ){

		//get model
		$model = $this->getModel();
		//get items
		$this->items = $model->getItemList();

		// Load lesson Model
		$lessons_model = FOFModel::getTmpInstance('Lessons', 'SplmsModel');

		foreach ($this->items as $this->item) {
			// Generate URL
			$this->item->url = JRoute::_('index.php?option=com_splms&view=teacher&id='.$this->item->splms_teacher_id.':'.$this->item->slug . SplmsHelper::getItemid('teachers'));
			  //Get Teachers Lessons
			$this->item->teacher_total_lessons= count($lessons_model->getTeacherLessons($this->item->splms_teacher_id));

			$specialistist_decodes = json_decode($this->item->specialist_in);

			if (isset($specialistist_decodes) && is_object($specialistist_decodes)) {
				$specialists = array();
				foreach ($specialistist_decodes as $key => $specialistist_decode) {
					$specialists[] = $specialistist_decode->specialist_text;
				}
				$this->item->specialist_in = implode(', ', $specialists);
			}


		}

		return parent::display( $tpl = null );
	}
}
