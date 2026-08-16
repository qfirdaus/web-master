<?php
/**
 * @package     JO Visitor Counter
 * @subpackage  mod_jo_visitor_counter
 *
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
 
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;

class ModJOVisitorCounterDisplayHelper
{
	/**
	 * Get total visitor count.
	 */
	public static function getTotalVisitors()
	{
		// Check if "Total Visitor(Visitor Counter)" is enabled
		$params = self::getModuleParams();
		$TotalVisitorsEnabled = (bool) $params->get('show_visitor_counter', 0);

		if (!$TotalVisitorsEnabled) {
			return []; // Return an empty array if the feature is disabled
		}

		$db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true)
			->select('total_visitors')
			->from($db->quoteName('#__jovisitorcounter_summary'))
			->where($db->quoteName('id') . ' = 1');

		try {
			$db->setQuery($query);
			$result = $db->loadResult();
			return (int)$result ?: 0;
		} catch (\Exception $e) {
			return 0;
		}
	}

	/**
	 * Visitor Statistics Module.
	 */
	public static function getVisitorStatistics()
	{
		// Check if "Visitor Statistics" is enabled
		$params = self::getModuleParams();
		$visitorStatisticsEnabled = (bool) $params->get('visitor_statistics', 0);

		if (!$visitorStatisticsEnabled) {
			return []; // Return an empty array if the feature is disabled
		}

		$db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		// Select all fields from the summary table
		$query->select('*')
			  ->from($db->quoteName('#__jovisitorcounter_summary'))
			  ->where($db->quoteName('id') . ' = 1'); // Assuming only one row exists

		$db->setQuery($query);
		return $db->loadAssoc();
	}



    /**
     * Recent Visitors Module.
     */
	public static function getVisitors($params)
	{
		// Check if "Show Recent Visitors" is enabled
		$showRecentVisitors = (bool) $params->get('show_recent_visitors', 0);
		if (!$showRecentVisitors) {
			return []; // Return an empty array if the feature is disabled
		}

		$db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		$query->select('*')
			  ->from($db->quoteName('#__jovisitorcounter'))
			  ->order($db->quoteName('visit_time') . ' DESC');

		// Apply list limit for recent visitors
		$listLimitRecent = (int) $params->get('list_limit_recent', 10); // Default limit is 10
		$db->setQuery($query, 0, $listLimitRecent);

		return $db->loadObjectList();
	}
	
	
	/**
	 * Visitor by Country Module.
	 */
	public static function getVisitorsByCountry($period, $params)
	{
		// Check if "Visitor by Country" is enabled
		$showVisitorByCountry = (bool) $params->get('show_visitor_by_country', 0);
		if (!$showVisitorByCountry) {
			return []; // Return an empty array if the feature is disabled
		}

		$db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
		$query = $db->getQuery(true);

		// Map period to database column name
		$columnMap = [
			'today' => 'today',
			'yesterday' => 'yesterday',
			'this_week' => 'this_week',
			'last_week' => 'last_week',
			'this_month' => 'this_month',
			'last_month' => 'last_month',
			'last_six_months' => 'last_six_months',
			'this_year' => 'this_year',
			'all_time' => 'total_visitors'
		];

		$columnName = $columnMap[$period] ?? 'total_visitors';

		// Base query: select country + selected column
		$query->select([
			$db->quoteName('country_name'),
			$db->quoteName('country_code'),
			$db->quoteName($columnName, 'total_visitors')
		])
		->from($db->quoteName('#__jovisitorcounter_country_summary'))
		->where($db->quoteName($columnName) . ' > 0')
		->order($db->quoteName($columnName) . ' DESC');

		// Apply limit
		$listLimitCountry = (int) $params->get('list_limit_country', 10); // Default is 10
		$db->setQuery($query, 0, $listLimitCountry);

		try {
			$results = $db->loadObjectList();
		} catch (\Exception $e) {
			return [];
		}

		if (empty($results)) {
			return [];
		}

		// Calculate total visitors for percentage based only on selected column
		$totalVisitorsInPeriod = array_sum(array_column($results, 'total_visitors'));

		// Add percentage and clean up data
		foreach ($results as &$result) {
			$result->percentage = $totalVisitorsInPeriod > 0
				? round(($result->total_visitors / $totalVisitorsInPeriod) * 100, 2)
				: 0;
		}

		return $results;
	}
	
	
 
    /**
     * Module Parameter.
    */
	private static $moduleParams;

	public static function getModuleParams()
	{
		if (self::$moduleParams === null) {
			$module = ModuleHelper::getModule('mod_jovisitorcounter');
			self::$moduleParams = new Joomla\Registry\Registry($module->params);
		}
		return self::$moduleParams;
	}


}