<?php
/**
 * @package     JO Visitor Counter Admin
 * @subpackage  mod_jovisitorcounter_admin
 *
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class ModJOVisitorCounterAdminHelper
{
    /**
     * Get total visitors
     */
    public static function getTotalVisitors()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('total_visitors')
            ->from($db->quoteName('#__jovisitorcounter_summary'))
            ->where($db->quoteName('id') . ' = 1');

        try {
            $db->setQuery($query);
            return (int)$db->loadResult();
        } catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Get detailed visitor statistics
     */
    public static function getVisitorStatistics()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__jovisitorcounter_summary'))
            ->where($db->quoteName('id') . ' = 1');

        try {
            $db->setQuery($query);
            return $db->loadAssoc();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get recent visitors
     */
    public static function getRecentVisitors($limit = 5)
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('*')
            ->from($db->quoteName('#__jovisitorcounter'))
            ->order($db->quoteName('visit_time') . ' DESC');

        try {
            $db->setQuery($query, 0, $limit);
            return $db->loadObjectList();
        } catch (\Exception $e) {
            return [];
        }
    }

    /**
     * Get top countries
     */
	public static function getVisitorsByCountry($limit = 5)
	{
		$db = \Joomla\CMS\Factory::getDbo();
		$query = $db->getQuery(true);

		// Base query
		$query->select(['country_name', 'country_code', 'total_visitors'])
			->from($db->quoteName('#__jovisitorcounter_country_summary'))
			->where($db->quoteName('country_code') . ' != ' . $db->quote('unknown'))
			->order($db->quoteName('total_visitors') . ' DESC');

		try {
			$db->setQuery($query, 0, $limit);
			$results = $db->loadObjectList();

			if (empty($results)) {
				return [];
			}

			// Calculate total visitors in period from returned data
			$totalVisitorsInPeriod = array_sum(array_column($results, 'total_visitors'));

			// Add percentage field
			foreach ($results as &$result) {
				$result->percentage = $totalVisitorsInPeriod > 0
					? round(($result->total_visitors / $totalVisitorsInPeriod) * 100, 2)
					: 0;
			}

			return $results;

		} catch (\Exception $e) {
			return [];
		}
	}
}