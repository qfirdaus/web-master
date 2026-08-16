<?php
/**
 * @package     JO Visitor Counter
 * @subpackage  System Plugin
 *
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Application\CMSApplication;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

class PlgSystemJOVisitorCounter extends CMSPlugin
{
	protected $app;

	public function onAfterRoute()
	{
		// Run fixUnknownVisitors() if flagged
		if ((int) $this->params->get('fix_unknown_visitors') === 1)
		{
			require_once __DIR__ . '/helpers/PlgSystemJOVisitorCounterHelper.php';
			\PlgSystemJOVisitorCounterHelper::fixUnknownVisitors();
			$this->resetFuvRsParam();
		}
		// Run reCalculateVisitors() if flagged
		if ((int) $this->params->get('re_calculate_visitors') === 1)
		{
			require_once __DIR__ . '/helpers/PlgSystemJOVisitorCounterHelper.php';
			\PlgSystemJOVisitorCounterHelper::reCalculateVisitors();
			$this->resetFuvRsParam();
		}

		if ($this->app->isClient('site')) {
			$enabled = (bool) $this->params->get('enable_tracking', 1);
			if (!$enabled) {
				return;
			}

			// Get current menu item ID
			$menu = $this->app->getMenu();
			$activeMenuItem = $menu->getActive();
			$currentItemId = $activeMenuItem ? $activeMenuItem->id : 0;

			// Check assign_to_menu_items parameter
			$assignToMenuItems = $this->params->get('assign_to_menu_items', '');
			if (!empty($assignToMenuItems)) {
				$allowedItemIds = array_map('trim', explode(',', $assignToMenuItems));
				$includeMode = true;

				foreach ($allowedItemIds as $itemId) {
					if (str_starts_with((string)$itemId, '-')) {
						$excludeId = ltrim($itemId, '-');
						if ((int)$excludeId === $currentItemId) {
							return; // Skip tracking
						}
					} else {
						$includeMode = false;
					}
				}

				if (!$includeMode && !in_array((string)$currentItemId, $allowedItemIds)) {
					return; // Not allowed → skip tracking
				}
			}

			// Proceed with tracking logic
			require_once __DIR__ . '/helpers/PlgSystemJOVisitorCounterHelper.php';
			$timeFrame = (int) $this->params->get('tracking_time_frame', 24) * 3600;

			// Show debug message only if debug mode is ON
			if ($this->params->get('debug_mode', 0)) {
				Factory::getApplication()->enqueueMessage(
					'JO Visitor Counter: Plugin is active and tracking visitors.',
					'message'
				);
			}

			PlgSystemJOVisitorCounterHelper::trackVisitor($timeFrame);

			// Also show current menu ID if debug mode is ON
			if ($this->params->get('debug_mode', 0)) {
				Factory::getApplication()->enqueueMessage(
					"JO Visitor Counter: Current Menu Item ID: {$currentItemId}",
					'notice'
				);
			}
		}
	}

	protected function resetFuvRsParam()
	{
		$db = Factory::getDbo();

		$query = $db->getQuery(true)
			->select($db->quoteName('params'))
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('element') . ' = ' . $db->quote('jovisitorcounter'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'));

		$db->setQuery($query);
		$paramsString = $db->loadResult();

		$params = json_decode($paramsString, true);

		$params['fix_unknown_visitors'] = 0;
		$params['re_calculate_visitors'] = 0;

		$newParams = json_encode($params);

		$query = $db->getQuery(true)
			->update($db->quoteName('#__extensions'))
			->set($db->quoteName('params') . ' = ' . $db->quote($newParams))
			->where($db->quoteName('element') . ' = ' . $db->quote('jovisitorcounter'))
			->where($db->quoteName('folder') . ' = ' . $db->quote('system'));

		$db->setQuery($query);
		$db->execute();
	}


}
