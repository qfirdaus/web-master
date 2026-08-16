<?php

/**
 * @package Helix3 Framework
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or Later
 */
//no direct accees
defined('_JEXEC') or die('resticted aceess');

class Helix3FeatureAccount
{

	private $helix3;
	public $position;

	public function __construct($helix3)
	{
		$this->helix3 = $helix3;
		$this->position = 'top2';
	}

	public function renderFeature()
	{

		$user = JFactory::getUser();

		$output = '';

		if ($user->guest) {
			$output .= '<ul class="sp-my-account">';
			$output .= '</ul>';
		} else {
			$output .= '<div class="sp-my-account">';
			$output .= '<a class="btn-account" href="#">' . JText::_('EDUCON_ACCOUNT') . '</a>';
			$output .= '<div class="sp-account-info">';
			$output .= JFactory::getDocument()->getBuffer('modules', 'myaccount', array('style' => 'none'));
			$output .= '</div>';
			$output .= '</div>';
		}

		return $output;
	}

	public static function getItemid($view = 'login')
	{
		$db = JFactory::getDbo();

		$query = $db->getQuery(true);
		$query->select($db->quoteName(array('id')));
		$query->from($db->quoteName('#__menu'));
		$query->where($db->quoteName('link') . ' LIKE ' . $db->quote('%option=com_users&view=' . $view . '%'));
		$query->where($db->quoteName('published') . ' = ' . $db->quote('1'));
		$db->setQuery($query);
		$result = $db->loadResult();

		if (count($result)) {
			return '&Itemid=' . $result;
		}

		return;
	}
}