<?php
/**
 * @package     JO Visitor Counter
 * @subpackage  System Plugin
 *
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

class PlgSystemJOVisitorCounterHelper
{
    /**
     * Loads the plugin language files
     */
    protected static function loadLanguage()
    {
        $lang = Factory::getLanguage();
        $lang->load('plg_system_jovisitorcounter', JPATH_ADMINISTRATOR, null, false, true);
    }
    
    /**
     * Tracks the current visitor.
     */
    public static function trackVisitor($timeFrame)
    {
        // Get the IP address
        $headers = [
            'HTTP_CF_CONNECTING_IP',   // Cloudflare
            'HTTP_X_FORWARDED_FOR',    // Common proxy
            'HTTP_CLIENT_IP',          // Alternate
            'REMOTE_ADDR',             // Fallback
        ];

        $app = \Joomla\CMS\Factory::getApplication();
        $ip = null;

        foreach ($headers as $header) {
            $ipCandidate = $app->input->server->get($header, null, 'string');
            
            if ($ipCandidate) {
                // If X_FORWARDED_FOR has a list, take the first valid IP
                if ($header === 'HTTP_X_FORWARDED_FOR' && strpos($ipCandidate, ',') !== false) {
                    $ipList = array_map('trim', explode(',', $ipCandidate));
                    foreach ($ipList as $entry) {
                        if (filter_var($entry, FILTER_VALIDATE_IP)) {
                            $ip = $entry;
                            break 2; // Break both loops
                        }
                    }
                } elseif (filter_var($ipCandidate, FILTER_VALIDATE_IP)) {
                    $ip = $ipCandidate;
                    break; // Found a valid IP
                }
            }
        }

        // Fallback to REMOTE_ADDR if no valid IP found in other headers
        if (empty($ip)) {
            $ip = $app->input->server->get('REMOTE_ADDR', '', 'string');
        }
        
        // Validate the IP address
        if (!filter_var($ip, FILTER_VALIDATE_IP)) {
            \Joomla\CMS\Factory::getApplication()->enqueueMessage('Invalid IP address detected.', 'error');
            return;
        }

        $session = \Joomla\CMS\Factory::getSession();
        $sessionKey = 'jo_visitorcounter_tracked_' . md5($ip);
        
        // Check cookie
        if (!empty(\Joomla\CMS\Factory::getApplication()->input->cookie->get('jo_visitorcounter_tracked'))) {
            return;
        }

        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');

        try {
            // Start a transaction
            $db->transactionStart();

            // Skip tracking if already tracked in this session
            if ($session->has($sessionKey)) {
                return;
            }

            // Check if the visitor has been tracked within the specified time frame
            $query = $db->getQuery(true);
            $query->select('id')
                  ->from($db->quoteName('#__jovisitorcounter'))
                  ->where($db->quoteName('ip_address') . ' = ' . $db->quote($ip))
                  ->where($db->quoteName('visit_time') . ' >= DATE_SUB(NOW(), INTERVAL ' . (int)$timeFrame . ' SECOND)')
                  ->setLimit(1);

            $db->setQuery($query);
            $existingVisitor = $db->loadResult();

            // If no recent record exists, insert a new visitor
            if (!$existingVisitor) {
                // Fetch country data for the new visitor
                $countryData = self::getCountryFromIP($ip);

                // Insert the new visitor into #__jovisitorcounter
                $query = $db->getQuery(true);
                $columns = ['ip_address', 'country', 'country_code', 'visit_time'];
                $values = [
                    $db->quote($ip),
                    $db->quote($countryData['name']),
                    $db->quote($countryData['code']),
                    $db->quote(date('Y-m-d H:i:s')),
                ];

                $query->insert($db->quoteName('#__jovisitorcounter'))
                      ->columns($db->quoteName($columns))
                      ->values(implode(',', $values));

                $db->setQuery($query)->execute();

                // Update the summary table after inserting the new visitor
                self::updateVisitorStatistics();
                self::updateCountrySummary($countryData['code']);
            }

            // Set session flag
            $session->set($sessionKey, true);
            
            // Set cookie
            $app->input->cookie->set(
                'jo_visitorcounter_tracked',
                '1',
                [
                    'expires'  => time() + $timeFrame,
                    'path'     => '/',
                    'secure'   => true,
                    'httponly' => true,
                    'samesite' => 'Lax'
                ]
            );

            // Commit the transaction
            $db->transactionCommit();
        } catch (Exception $e) {
            // Rollback on error
            $db->transactionRollback();
            \Joomla\CMS\Factory::getApplication()->enqueueMessage('Database Error: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Visitor Statistics Module Calculation with Hybrid Lock + Retry
     */
    private static function updateVisitorStatistics()
    {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        
        // Check if MySQL locks are supported
        $locksSupported = self::areMySQLLocksSupported();
        
        if ($locksSupported) {
            // Use MySQL Lock method
            self::updateWithLock();
        } else {
            // Fallback to Retry method
            self::updateWithRetry();
        }
    }

    /**
     * Check if MySQL GET_LOCK is supported
     */
    private static function areMySQLLocksSupported()
    {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        
        try {
            $db->setQuery("SELECT GET_LOCK('test_lock', 0)")->execute();
            $result = $db->loadResult();
            $db->setQuery("SELECT RELEASE_LOCK('test_lock')")->execute();
            return true;
        } catch (Exception $e) {
            return false; // Locks not supported (e.g., older MySQL, some shared hosting)
        }
    }

    /**
     * Update with MySQL Lock method (Primary)
     */
    private static function updateWithLock()
    {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $maxLockWait = 5; // seconds
        
        try {
            // Try to acquire lock
            $db->setQuery("SELECT GET_LOCK('jovisitorcounter_summary_lock', " . (int)$maxLockWait . ")")->execute();
            $locked = $db->loadResult();
            
            if (!$locked) {
                // Could not acquire lock, try retry method instead
                self::updateWithRetry();
                return;
            }
            
            // Lock acquired, perform update
            $stats = self::getVisitorStatisticsFromDetails();
            $params = self::getModuleParams();
            $counterStartFrom = (int) $params->get('counter_start_from', 0);
            
            $query = $db->getQuery(true);
            $fields = [
                $db->quoteName('total_visitors') . ' = ' . ((int)$stats['total_visitors'] + $counterStartFrom),
                $db->quoteName('today') . ' = ' . (int)$stats['today'],
                $db->quoteName('yesterday') . ' = ' . (int)$stats['yesterday'],
                $db->quoteName('this_week') . ' = ' . (int)$stats['this_week'],
                $db->quoteName('last_week') . ' = ' . (int)$stats['last_week'],
                $db->quoteName('this_month') . ' = ' . (int)$stats['this_month'],
                $db->quoteName('last_month') . ' = ' . (int)$stats['last_month'],
                $db->quoteName('two_months_ago') . ' = ' . (int)$stats['two_months_ago'],
                $db->quoteName('three_months_ago') . ' = ' . (int)$stats['three_months_ago'],
                $db->quoteName('four_months_ago') . ' = ' . (int)$stats['four_months_ago'],
                $db->quoteName('five_months_ago') . ' = ' . (int)$stats['five_months_ago'],
                $db->quoteName('last_six_months') . ' = ' . (int)$stats['last_six_months'],
                $db->quoteName('this_year') . ' = ' . (int)$stats['this_year'],
                $db->quoteName('last_year') . ' = ' . (int)$stats['last_year'],
                $db->quoteName('last_updated') . ' = NOW()',
            ];
            $conditions = [$db->quoteName('id') . ' = 1'];
            
            $query->update($db->quoteName('#__jovisitorcounter_summary'))
                ->set($fields)
                ->where($conditions);
            
            $db->setQuery($query)->execute();
            
            // Release lock
            $db->setQuery("SELECT RELEASE_LOCK('jovisitorcounter_summary_lock')")->execute();
            
        } catch (Exception $e) {
            // Release lock on error if possible
            try {
                $db->setQuery("SELECT RELEASE_LOCK('jovisitorcounter_summary_lock')")->execute();
            } catch (Exception $releaseError) {
                // Ignore release errors
            }
            
            // Fallback to retry method
            self::updateWithRetry();
        }
    }

    /**
     * Update with Retry Logic (Fallback for hosts without lock support)
     */
    private static function updateWithRetry()
    {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $maxRetries = 5;
        $retryCount = 0;
        $success = false;
        
        while ($retryCount < $maxRetries && !$success) {
            try {
                $stats = self::getVisitorStatisticsFromDetails();
                $params = self::getModuleParams();
                $counterStartFrom = (int) $params->get('counter_start_from', 0);
                
                // Use INSERT ... ON DUPLICATE KEY UPDATE (more atomic)
                $insertQuery = "INSERT INTO " . $db->quoteName('#__jovisitorcounter_summary') . " (
                    id, total_visitors, today, yesterday, this_week, last_week, 
                    this_month, last_month, two_months_ago, three_months_ago, four_months_ago, 
                    five_months_ago, last_six_months, this_year, last_year, last_updated
                ) VALUES (
                    1,
                    " . ((int)$stats['total_visitors'] + $counterStartFrom) . ",
                    " . (int)$stats['today'] . ",
                    " . (int)$stats['yesterday'] . ",
                    " . (int)$stats['this_week'] . ",
                    " . (int)$stats['last_week'] . ",
                    " . (int)$stats['this_month'] . ",
                    " . (int)$stats['last_month'] . ",
                    " . (int)$stats['two_months_ago'] . ",
                    " . (int)$stats['three_months_ago'] . ",
                    " . (int)$stats['four_months_ago'] . ",
                    " . (int)$stats['five_months_ago'] . ",
                    " . (int)$stats['last_six_months'] . ",
                    " . (int)$stats['this_year'] . ",
                    " . (int)$stats['last_year'] . ",
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                    total_visitors = VALUES(total_visitors),
                    today = VALUES(today),
                    yesterday = VALUES(yesterday),
                    this_week = VALUES(this_week),
                    last_week = VALUES(last_week),
                    this_month = VALUES(this_month),
                    last_month = VALUES(last_month),
                    two_months_ago = VALUES(two_months_ago),
                    three_months_ago = VALUES(three_months_ago),
                    four_months_ago = VALUES(four_months_ago),
                    five_months_ago = VALUES(five_months_ago),
                    last_six_months = VALUES(last_six_months),
                    this_year = VALUES(this_year),
                    last_year = VALUES(last_year),
                    last_updated = NOW()";
                
                $db->setQuery($insertQuery)->execute();
                $success = true;
                
            } catch (Exception $e) {
                $retryCount++;
                
                if ($retryCount >= $maxRetries) {
                    // Log error after all retries failed
                    \Joomla\CMS\Log\Log::add(
                        'Failed to update visitor statistics after ' . $maxRetries . ' attempts: ' . $e->getMessage(),
                        \Joomla\CMS\Log\Log::ERROR,
                        'jovisitorcounter'
                    );
                    
                    // Optional: Show error in debug mode
                    $params = self::getModuleParams();
                    if ($params->get('debug_mode', 0)) {
                        \Joomla\CMS\Factory::getApplication()->enqueueMessage(
                            'JO Visitor Counter: Update failed after ' . $maxRetries . ' retries. Count may be inaccurate.',
                            'warning'
                        );
                    }
                } else {
                    // Wait before retry (exponential backoff)
                    usleep(10000 * $retryCount); // 10ms, 20ms, 30ms, etc.
                }
            }
        }
    }

    private static function getVisitorStatisticsFromDetails()
    {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);

        $query->select("
            COUNT(*) AS total_visitors,
            SUM(CASE WHEN DATE(visit_time) = CURDATE() THEN 1 ELSE 0 END) AS today,
            SUM(CASE WHEN DATE(visit_time) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS yesterday,
            SUM(CASE WHEN YEARWEEK(visit_time, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) AS this_week,
            SUM(CASE WHEN YEARWEEK(visit_time, 1) = YEARWEEK(CURDATE(), 1) - 1 THEN 1 ELSE 0 END) AS last_week,
            SUM(CASE WHEN MONTH(visit_time) = MONTH(CURDATE()) AND YEAR(visit_time) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS this_month,
            SUM(CASE WHEN MONTH(visit_time) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(visit_time) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN 1 ELSE 0 END) AS last_month,
            SUM(CASE WHEN YEAR(visit_time) = YEAR(DATE_SUB(CURDATE(), INTERVAL 2 MONTH)) AND MONTH(visit_time) = MONTH(DATE_SUB(CURDATE(), INTERVAL 2 MONTH)) THEN 1 ELSE 0 END) AS two_months_ago,
            SUM(CASE WHEN YEAR(visit_time) = YEAR(DATE_SUB(CURDATE(), INTERVAL 3 MONTH)) AND MONTH(visit_time) = MONTH(DATE_SUB(CURDATE(), INTERVAL 3 MONTH)) THEN 1 ELSE 0 END) AS three_months_ago,
            SUM(CASE WHEN YEAR(visit_time) = YEAR(DATE_SUB(CURDATE(), INTERVAL 4 MONTH)) AND MONTH(visit_time) = MONTH(DATE_SUB(CURDATE(), INTERVAL 4 MONTH)) THEN 1 ELSE 0 END) AS four_months_ago,
            SUM(CASE WHEN YEAR(visit_time) = YEAR(DATE_SUB(CURDATE(), INTERVAL 5 MONTH)) AND MONTH(visit_time) = MONTH(DATE_SUB(CURDATE(), INTERVAL 5 MONTH)) THEN 1 ELSE 0 END) AS five_months_ago,
            SUM(CASE WHEN visit_time >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) THEN 1 ELSE 0 END) AS last_six_months,
            SUM(CASE WHEN YEAR(visit_time) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS this_year,
            SUM(CASE WHEN YEAR(visit_time) = YEAR(CURDATE()) - 1 THEN 1 ELSE 0 END) AS last_year
        ")
        ->from($db->quoteName('#__jovisitorcounter'));
        $db->setQuery($query);
        return $db->loadAssoc();
    }

    /**
     * Visitor by Country Module Calculation with Hybrid Lock + Retry
     */
    private static function updateCountrySummary($countryCode)
    {
        // Check if "Show Visitor by Country" is enabled
        $params = self::getModuleParams();
        $showVisitorByCountry = (bool) $params->get('show_visitor_by_country', 0);
        if (!$showVisitorByCountry) {
            return;
        }
        
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $locksSupported = self::areMySQLLocksSupported();
        
        if ($locksSupported) {
            self::updateCountryWithLock($countryCode);
        } else {
            self::updateCountryWithRetry($countryCode);
        }
    }

    private static function updateCountryWithLock($countryCode)
    {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $lockName = 'jovisitorcounter_country_lock_' . md5($countryCode);
        
        try {
            $db->setQuery("SELECT GET_LOCK('" . $lockName . "', 3)")->execute();
            $locked = $db->loadResult();
            
            if (!$locked) {
                self::updateCountryWithRetry($countryCode);
                return;
            }
            
            $stats = self::getCountryStatisticsFromDetails($countryCode);
            
            $query = $db->getQuery(true);
            $query->select('DISTINCT ' . $db->quoteName('country'))
                ->from($db->quoteName('#__jovisitorcounter'))
                ->where($db->quoteName('country_code') . ' = ' . $db->quote($countryCode));
            $db->setQuery($query);
            $countryName = $db->loadResult() ?? 'Unknown';
            
            $insertQuery = "INSERT INTO " . $db->quoteName('#__jovisitorcounter_country_summary') . " (
                country_code, country_name, total_visitors, today, yesterday, 
                this_week, last_week, this_month, last_month, last_six_months, 
                this_year, last_updated
            ) VALUES (
                " . $db->quote($countryCode) . ",
                " . $db->quote($countryName) . ",
                " . (int)$stats['total_visitors'] . ",
                " . (int)$stats['today'] . ",
                " . (int)$stats['yesterday'] . ",
                " . (int)$stats['this_week'] . ",
                " . (int)$stats['last_week'] . ",
                " . (int)$stats['this_month'] . ",
                " . (int)$stats['last_month'] . ",
                " . (int)$stats['last_six_months'] . ",
                " . (int)$stats['this_year'] . ",
                NOW()
            )
            ON DUPLICATE KEY UPDATE
                country_name = VALUES(country_name),
                total_visitors = VALUES(total_visitors),
                today = VALUES(today),
                yesterday = VALUES(yesterday),
                this_week = VALUES(this_week),
                last_week = VALUES(last_week),
                this_month = VALUES(this_month),
                last_month = VALUES(last_month),
                last_six_months = VALUES(last_six_months),
                this_year = VALUES(this_year),
                last_updated = NOW()";
            
            $db->setQuery($insertQuery)->execute();
            $db->setQuery("SELECT RELEASE_LOCK('" . $lockName . "')")->execute();
            
        } catch (Exception $e) {
            try {
                $db->setQuery("SELECT RELEASE_LOCK('" . $lockName . "')")->execute();
            } catch (Exception $releaseError) {}
            self::updateCountryWithRetry($countryCode);
        }
    }

    private static function updateCountryWithRetry($countryCode)
    {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $maxRetries = 3;
        $retryCount = 0;
        
        while ($retryCount < $maxRetries) {
            try {
                $stats = self::getCountryStatisticsFromDetails($countryCode);
                
                $query = $db->getQuery(true);
                $query->select('DISTINCT ' . $db->quoteName('country'))
                    ->from($db->quoteName('#__jovisitorcounter'))
                    ->where($db->quoteName('country_code') . ' = ' . $db->quote($countryCode));
                $db->setQuery($query);
                $countryName = $db->loadResult() ?? 'Unknown';
                
                $insertQuery = "INSERT INTO " . $db->quoteName('#__jovisitorcounter_country_summary') . " (
                    country_code, country_name, total_visitors, today, yesterday, 
                    this_week, last_week, this_month, last_month, last_six_months, 
                    this_year, last_updated
                ) VALUES (
                    " . $db->quote($countryCode) . ",
                    " . $db->quote($countryName) . ",
                    " . (int)$stats['total_visitors'] . ",
                    " . (int)$stats['today'] . ",
                    " . (int)$stats['yesterday'] . ",
                    " . (int)$stats['this_week'] . ",
                    " . (int)$stats['last_week'] . ",
                    " . (int)$stats['this_month'] . ",
                    " . (int)$stats['last_month'] . ",
                    " . (int)$stats['last_six_months'] . ",
                    " . (int)$stats['this_year'] . ",
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                    country_name = VALUES(country_name),
                    total_visitors = VALUES(total_visitors),
                    today = VALUES(today),
                    yesterday = VALUES(yesterday),
                    this_week = VALUES(this_week),
                    last_week = VALUES(last_week),
                    this_month = VALUES(this_month),
                    last_month = VALUES(last_month),
                    last_six_months = VALUES(last_six_months),
                    this_year = VALUES(this_year),
                    last_updated = NOW()";
                
                $db->setQuery($insertQuery)->execute();
                break; // Success
                
            } catch (Exception $e) {
                $retryCount++;
                if ($retryCount >= $maxRetries) {
                    \Joomla\CMS\Log\Log::add(
                        'Failed to update country summary for ' . $countryCode . ': ' . $e->getMessage(),
                        \Joomla\CMS\Log\Log::ERROR,
                        'jovisitorcounter'
                    );
                } else {
                    usleep(10000 * $retryCount);
                }
            }
        }
    }

    private static function getCountryStatisticsFromDetails($countryCode)
    {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $query = $db->getQuery(true);
        $query->select("
            COUNT(*) AS total_visitors,
            SUM(CASE WHEN DATE(visit_time) = CURDATE() THEN 1 ELSE 0 END) AS today,
            SUM(CASE WHEN DATE(visit_time) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) AS yesterday,
            SUM(CASE WHEN YEARWEEK(visit_time, 1) = YEARWEEK(CURDATE(), 1) THEN 1 ELSE 0 END) AS this_week,
            SUM(CASE WHEN YEARWEEK(visit_time, 1) = YEARWEEK(CURDATE(), 1) - 1 THEN 1 ELSE 0 END) AS last_week,
            SUM(CASE WHEN MONTH(visit_time) = MONTH(CURDATE()) AND YEAR(visit_time) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS this_month,
            SUM(CASE WHEN MONTH(visit_time) = MONTH(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) AND YEAR(visit_time) = YEAR(DATE_SUB(CURDATE(), INTERVAL 1 MONTH)) THEN 1 ELSE 0 END) AS last_month,
            SUM(CASE WHEN visit_time >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH) THEN 1 ELSE 0 END) AS last_six_months,
            SUM(CASE WHEN YEAR(visit_time) = YEAR(CURDATE()) THEN 1 ELSE 0 END) AS this_year
        ")
            ->from($db->quoteName('#__jovisitorcounter'))
            ->where($db->quoteName('country_code') . ' = ' . $db->quote($countryCode));
        $db->setQuery($query);
        return $db->loadAssoc();
    }

    /**
     * Module Parameter.
     */
    public static function getModuleParams()
    {
        // Find module by name and load params
        $db = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('params')
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('module') . ' = ' . $db->quote('mod_jovisitorcounter'))
            ->setLimit(1);
        $db->setQuery($query);
        $paramsJson = $db->loadResult();
        if ($paramsJson) {
            return new \Joomla\Registry\Registry($paramsJson);
        }
        return new \Joomla\Registry\Registry([]);
    }

    /**
     * IP to Country using ip-api.com API.
     */
    private static function getCountryFromIP($ip)
    {
        // Localhost check
        if (in_array($ip, ['127.0.0.1', '::1'])) {
            return ['name' => 'Localhost', 'code' => 'local'];
        }
        $url = "http://ip-api.com/json/{$ip}";
        $response = @file_get_contents($url);
        if ($response === false) {
            return [
                'name' => 'Unknown',
                'code' => 'unknown',
            ];
        }
        $data = json_decode($response, true);
        if ($data && isset($data['country']) && isset($data['countryCode'])) {
            return [
                'name' => $data['country'],
                'code' => strtolower($data['countryCode']),
            ];
        }
        return [
            'name' => 'Unknown',
            'code' => 'unknown',
        ];
    }
    
    /**
     * Manually re-check unknown visitors and update their country info
     */
    public static function fixUnknownVisitors()
    {
        self::loadLanguage();
        $db = \Joomla\CMS\Factory::getDbo();
        $app = \Joomla\CMS\Factory::getApplication();

        try {
            // Get all unknown visitors
            $query = $db->getQuery(true)
                ->select('*')
                ->from($db->quoteName('#__jovisitorcounter'))
                ->where($db->quoteName('country_code') . ' = ' . $db->quote('unknown'));

            $db->setQuery($query);
            $visits = $db->loadObjectList();

            if (empty($visits)) {
                $app->enqueueMessage(Text::_('PLG_SYSTEM_JOVISITORCOUNTER_NO_UNKNOWN_VISITORS_FOUND'), 'notice');
                return;
            }

            $updatedCount = 0;

            foreach ($visits as $visit) {
                // Recheck country data
                $countryData = self::getCountryFromIP($visit->ip_address);

                if ($countryData['code'] === 'unknown') {
                    continue; // Still unknown → skip
                }

                // Update main visitor record
                $updateQuery = $db->getQuery(true)
                    ->update($db->quoteName('#__jovisitorcounter'))
                    ->set([
                        $db->quoteName('country') . ' = ' . $db->quote($countryData['name']),
                        $db->quoteName('country_code') . ' = ' . $db->quote($countryData['code']),
                    ])
                    ->where($db->quoteName('id') . ' = ' . (int)$visit->id);

                $db->setQuery($updateQuery)->execute();
                $updatedCount++;
            }

            if ($updatedCount > 0) {
                $app->enqueueMessage(Text::plural('PLG_SYSTEM_JOVISITORCOUNTER_FIXED_UNKNOWN_VISITORS', $updatedCount), 'message');
            } 
            else {
                $app->enqueueMessage(Text::_('PLG_SYSTEM_JOVISITORCOUNTER_NO_NEW_COUNTRY_DATA_FOUND'), 'notice');
            }
        } 
        catch (\Exception $e) {
            $app->enqueueMessage('Error fixing unknown visitors: ' . $e->getMessage(), 'error');
        }
    }
    
    /**
     * Manually re-calculate all visitor statistics.
     */
    public static function reCalculateVisitors()
    {
        self::loadLanguage();
        $app = \Joomla\CMS\Factory::getApplication();
        
        try {
            $app->enqueueMessage(Text::_('PLG_SYSTEM_JOVISITORCOUNTER_RE_CALCULATED_VISITORS_STAT'), 'notice');
            self::updateVisitorStatistics();
            self::updateAllCountriesSummary();
        } catch (\Exception $e) {
            $app->enqueueMessage('Error during recalculation: ' . $e->getMessage(), 'error');
        }
    }

    /**
     * Updates country summary for ALL countries found in the visitor table
     */
    public static function updateAllCountriesSummary()
    {
        $db = \Joomla\CMS\Factory::getContainer()->get('DatabaseDriver');
        $db->transactionStart();

        try {
            // Step 1: Query summarized stats grouped by country
            $query = $db->getQuery(true)
                ->select([
                    'LOWER(country_code) AS country_code',
                    'country AS country_name',
                    'COUNT(*) AS total_visitors',
                    'SUM(DATE(visit_time) = CURDATE()) AS today',
                    'SUM(DATE(visit_time) = CURDATE() - INTERVAL 1 DAY) AS yesterday',
                    'SUM(YEARWEEK(visit_time, 1) = YEARWEEK(CURDATE(), 1)) AS this_week',
                    'SUM(YEARWEEK(visit_time, 1) = YEARWEEK(CURDATE(), 1) - 1) AS last_week',
                    'SUM(MONTH(visit_time) = MONTH(CURDATE()) AND YEAR(visit_time) = YEAR(CURDATE())) AS this_month',
                    'SUM(MONTH(visit_time) = MONTH(CURDATE() - INTERVAL 1 MONTH) AND YEAR(visit_time) = YEAR(CURDATE() - INTERVAL 1 MONTH)) AS last_month',
                    'SUM(visit_time >= CURDATE() - INTERVAL 6 MONTH) AS last_six_months',
                    'SUM(YEAR(visit_time) = YEAR(CURDATE())) AS this_year'
                ])
                ->from($db->quoteName('#__jovisitorcounter'))
                ->where('country_code IS NOT NULL AND country_code != ' . $db->quote(''))
                ->group(['LOWER(country_code)', 'country']);

            $stats = $db->setQuery($query)->loadAssocList();

            if (empty($stats)) {
                $db->transactionCommit();
                return;
            }

            // Optional: clear previous summary
            $db->setQuery('TRUNCATE TABLE ' . $db->quoteName('#__jovisitorcounter_country_summary'))->execute();

            // Step 2: Prepare insert query
            $now = $db->quote(date('Y-m-d H:i:s'));

            $insert = $db->getQuery(true)
                ->insert($db->quoteName('#__jovisitorcounter_country_summary'))
                ->columns([
                    $db->quoteName('country_code'),
                    $db->quoteName('country_name'),
                    $db->quoteName('total_visitors'),
                    $db->quoteName('today'),
                    $db->quoteName('yesterday'),
                    $db->quoteName('this_week'),
                    $db->quoteName('last_week'),
                    $db->quoteName('this_month'),
                    $db->quoteName('last_month'),
                    $db->quoteName('last_six_months'),
                    $db->quoteName('this_year'),
                    $db->quoteName('last_updated')
                ]);

            foreach ($stats as $row) {
                $insert->values(implode(',', [
                    $db->quote($row['country_code']),
                    $db->quote($row['country_name'] ?: 'Unknown'),
                    (int) $row['total_visitors'],
                    (int) $row['today'],
                    (int) $row['yesterday'],
                    (int) $row['this_week'],
                    (int) $row['last_week'],
                    (int) $row['this_month'],
                    (int) $row['last_month'],
                    (int) $row['last_six_months'],
                    (int) $row['this_year'],
                    $now
                ]));
            }

            $db->setQuery($insert)->execute();
            $db->transactionCommit();

        } catch (Exception $e) {
            $db->transactionRollback();
            \Joomla\CMS\Log\Log::add('Summary insert failed: ' . $e->getMessage(), \Joomla\CMS\Log\Log::ERROR, 'jovisitorcounter');
        }
    }
}