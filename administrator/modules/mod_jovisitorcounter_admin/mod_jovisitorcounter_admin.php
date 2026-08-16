<?php
/**
 * @package     JO Visitor Counter Admin
 * @subpackage  mod_jovisitorcounter_admin
 *
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Document\Document;
use Joomla\CMS\Uri\Uri;

// Load flag CSS
$doc = Factory::getDocument();
$doc->addStyleSheet('/modules/mod_jovisitorcounter/assets/flag-icons/css/flag-icons.min.css');

require_once __DIR__ . '/helper.php';

// Load parameters
$showTotal = (bool)$params->get('show_total', 1);
$showStatistics = (bool)$params->get('show_statistics', 1);
$showRecentVisitors = (bool)$params->get('show_recent_visitors', 1);
$listLimitRecent = (int)$params->get('list_limit_recent', 5);
$showVisitorByCountry = (bool)$params->get('show_visitor_by_country', 1);
$listLimitCountry = (int)$params->get('list_limit_country', 5);
$showToday = $params->get('show_today', 1);
$showYesterday = $params->get('show_yesterday', 1);
$showThisWeek = $params->get('show_this_week', 1);
$showLastWeek = $params->get('show_last_week', 1);
$showThisMonth = $params->get('show_this_month', 1);
$showLastMonth = $params->get('show_last_month', 1);
$showTwoMonthsAgo = $params->get('show_two_months_ago', 1);
$showThreeMonthsAgo = $params->get('show_three_months_ago', 1);
$showFourMonthsAgo = $params->get('show_four_months_ago', 1);
$showFiveMonthsAgo = $params->get('show_five_months_ago', 1);
$showLastSixMonths = $params->get('show_last_six_months', 1);
$showThisYear = $params->get('show_this_year', 1);
$showLastYear = $params->get('show_last_year', 1);

// Fetch data
$totalVisitors = ModJOVisitorCounterAdminHelper::getTotalVisitors();
$visitorStats = $showStatistics ? ModJOVisitorCounterAdminHelper::getVisitorStatistics() : [];
$recentVisitors = $showRecentVisitors ? ModJOVisitorCounterAdminHelper::getRecentVisitors($listLimitRecent) : [];
$visitorsByCountry = $showVisitorByCountry ? ModJOVisitorCounterAdminHelper::getVisitorsByCountry($listLimitCountry) : [];

// Pass data to template
require ModuleHelper::getLayoutPath('mod_jovisitorcounter_admin', $params->get('layout', 'default'));