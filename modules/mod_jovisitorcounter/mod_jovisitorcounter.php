<?php
/**
 * @package     JO Visitor Counter
 * @subpackage  mod_jo_visitor_counter
 *
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
 
defined('_JEXEC') or die;

// Include the helper file
require_once __DIR__ . '/helper.php';

// Retrieve the parameters
$timeFrame = $params->get('time_frame', 24) * 60 * 60; // Convert hours to seconds
$showVisitorCounter = (bool) $params->get('show_visitor_counter', 1); // Visitor Counter toggle
$visitorStatsEnabled = (bool) $params->get('visitor_statistics', 1); // Visitor Statistics toggle
$showRecentVisitors = (bool) $params->get('show_recent_visitors', 1); // Recent Visitors toggle
$showVisitorByCountry = (bool) $params->get('show_visitor_by_country', 1); // Visitor by Country toggle
$calculationPeriod = $params->get('calculation_period', 'all_time'); // Calculation period

// Fetch visitor data
$visitors = ModJOVisitorCounterDisplayHelper::getVisitors($params);

// Fetch visitor statistics if enabled
$visitorStats = $visitorStatsEnabled ? ModJOVisitorCounterDisplayHelper::getVisitorStatistics() : [];

// Fetch total visitors if enabled
$totalVisitors = $showVisitorCounter && isset($visitorStats['total_visitors']) ? $visitorStats['total_visitors'] : 0;

// Fetch visitors by country if enabled
$visitorsByCountry = $showVisitorByCountry ? ModJOVisitorCounterDisplayHelper::getVisitorsByCountry($calculationPeriod, $params) : [];

// Pass parameters to the template
require \Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_jovisitorcounter', $params->get('layout', 'default'));