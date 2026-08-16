<?php
/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2021 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\BaseController;

class SplmsControllerCart extends BaseController {
    
    public function add() {
		$cookie             = Factory::getApplication()->input->cookie;
		$input  			= Factory::getApplication()->input;
		$course 			= (int) $input->get('course', 0, 'INT');
		$courses 			= array();
		
		// Set cookie data
		$exist_orders = $cookie->get('lmsOrders', base64_encode(json_encode(array())));
		$decoded_orders = base64_decode($exist_orders);
		if (SplmsHelper::isJson($decoded_orders)) {
			$courses = json_decode($decoded_orders, true);
		} else {
			$courses = unserialize($decoded_orders, ['allowed_classes' => false]);
		}
		$courses = is_array($courses) ? $courses : array();
		
		if(!array_key_exists($course, $courses)) {
			$courses[$course]['product'] = $course;
		}

		$serialized_courses = array_map('json_encode', $courses);
		$unique_serialized = array_unique($serialized_courses);
		$orders = array_map(function($item) {
			return json_decode($item, true);
		}, $unique_serialized);

		$cookie->set('lmsOrders', base64_encode(json_encode($orders)), $expire = 0, Uri::base(true) );
		
		$output = array(
			'redirect'=>Route::_('index.php?option=com_splms&view=cart' . SplmsHelper::getItemid('cart')),
			'button_text'=>Text::_('COM_SPLMS_ORDER_CHECKOUT')
		);

		echo json_encode($output);
		die;
    }
    
    public function remove() {	
		$cookie             = Factory::getApplication()->input->cookie;
		$input  			= Factory::getApplication()->input;
		$course 			= (int) $input->get('course', 0, 'INT');
        $courses 			= array();
		
		// Set cookie data
		$exist_orders = $cookie->get('lmsOrders', base64_encode(json_encode(array())));
		$decoded_orders = base64_decode($exist_orders);
		if (SplmsHelper::isJson($decoded_orders)) {
			$exist_orders = json_decode($decoded_orders, true);
		} else {
			$exist_orders = unserialize($decoded_orders, ['allowed_classes' => false]);
		}
		$exist_orders = is_array($exist_orders) ? $exist_orders : array();
		
		if(array_key_exists($course, $exist_orders)) {
			unset($exist_orders[$course]);
		}

		$serialized_courses = array_map('json_encode', $exist_orders);
		$unique_serialized = array_unique($serialized_courses);
		$orders = array_map(function($item) {
			return json_decode($item, true);
		}, $unique_serialized);

		$cookie->set('lmsOrders', base64_encode(json_encode($orders)), $expire = 0, Uri::base(true) );
		
		$output = array(
			'redirect'=>Route::_('index.php?option=com_splms&view=cart' . SplmsHelper::getItemid('cart'))
		);

		echo json_encode($output);
		die;
	}
}