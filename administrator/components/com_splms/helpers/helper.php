<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (C) 2010 - 2016 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

class SplmsHelper {

	// Get Orders
	public static function getOrders() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(splms_order_id)');
		$query->from($db->quoteName('#__splms_orders'));
		$query->where($db->quoteName('enabled')." = 1");
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadResult();
		return $results;
	}

	// Get Orders
	public static function getCourses() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(splms_course_id)');
		$query->from($db->quoteName('#__splms_courses'));
		$query->where($db->quoteName('enabled')." = 1");
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadResult();

		return $results;
	}

	// Get Orders
	public static function getLessons() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(splms_lesson_id)');
		$query->from($db->quoteName('#__splms_lessons'));
		$query->where($db->quoteName('enabled')." = 1");
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadResult();

		return $results;
	}

	// Get Orders
	public static function getUsers() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('COUNT(id)');
		$query->from($db->quoteName('#__users'));
		$query->where($db->quoteName('block')." = 0");
		$db->setQuery($query);
		$results = $db->loadResult();

		return $results;
	}


	//Get total sales by day
	public static function getTotalSales() {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('SUM(order_payment_price)');
		$query->from($db->quoteName('#__splms_orders'));
		$query->where($db->quoteName('enabled')." = 1");
		$db->setQuery($query);
		$results = $db->loadResult();

		return round($results,2);
	}

	//Get total sales by day
	public static function getSales($day, $month, $year) {

		$db = JFactory::getDbo();
		$query = $db->getQuery(true);
		$query->select('SUM(order_payment_price)');
		$query->from($db->quoteName('#__splms_orders'));
		$query->where('DAY(created_on) = ' . $day);
		$query->where('MONTH(created_on) = ' . $month);
		$query->where('YEAR(created_on) = ' . $year);
		$query->where($db->quoteName('enabled')." = 1");
		$db->setQuery($query);
		$results = $db->loadResult();

		return $results;
	}

	//Orders List
	public static function getOrdersList() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('a.splms_order_id', 'a.splms_course_id', 'a.created_on', 'a.order_payment_price', 'b.title', 'b.splms_course_id')));
		$query->from($db->quoteName('#__splms_orders', 'a'));
		$query->join('LEFT', $db->quoteName('#__splms_courses', 'b') . ' ON (' . $db->quoteName('a.splms_course_id') . ' = ' . $db->quoteName('b.splms_course_id') . ')');
		$query->where($db->quoteName('a.enabled')." = 1");
		$query->setLimit('5');
		$query->order('a.ordering DESC');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	public static function getCoursesList() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true);

		$query->select($db->quoteName(array('splms_course_id', 'title', 'created_on', 'price')));
		$query->from($db->quoteName('#__splms_courses'));
		$query->where($db->quoteName('enabled')." = 1");
		$query->setLimit('5');
		$query->order('ordering DESC');
		$db->setQuery($query);
		$results = $db->loadObjectList();

		return $results;
	}

	// Generate Currency
	public static function generateCurrency($amt = 0){

		//Joomla Component Helper & Get LMS Params
		jimport('joomla.application.component.helper');
		$params = JComponentHelper::getParams('com_splms');

		//Get Currency
		$currency = explode(':', $params->get('currency', 'USD:$'));

		switch ($currency[0]) {
			case 'USD':
				$lancode = 'en_US';
				break;

			case 'GBP':
				$lancode = 'en_GB';
				break;

			case 'RUB':
				$lancode = 'ru_RU';
				break;

			case 'BRL':
				$lancode = 'pt_BR';
				break;

			case 'CAD':
				$lancode = 'en_CA';
				break;

			case 'CZK':
				$lancode = 'cs_CZ';
				break;

			case 'DKK':
				$lancode = 'en_DK';
				break;

			case 'EUR':
				$lancode = 'fr_FR';
				break;

			case 'HKD':
				$lancode = 'zh_HK';
				break;

			case 'HUF':
				$lancode = 'hu_HU';
				break;

			case 'ILS':
				$lancode = 'zh_HK';
				break;

			case 'JPY':
				$lancode = 'ja_JP';
				break;

			case 'MXN':
				$lancode = 'es_MX';
				break;

			case 'NOK':
				$lancode = 'nb_NO';
				break;

			case 'NZD':
				$lancode = 'en_GB';
				break;

			case 'PHP':
				$lancode = 'en_PH';
				break;

			case 'PLN':
				$lancode = 'pl_PL';
				break;

			case 'SGD':
				$lancode = 'zh_SG';
				break;

			case 'SEK':
				$lancode = 'sv_SE';
				break;

			case 'CHF':
				$lancode = 'de_LI';
				break;

			case 'TWD':
				$lancode = 'zh_TW';
				break;

			case 'THB':
				$lancode = 'th_TH';
				break;

			case 'TRY':
				$lancode = 'tr_TR';
				break;

			default:
				$lancode = 'en_US';
				break;
		}

		// $fmt = new \NumberFormatter( $lancode, NumberFormatter::CURRENCY );
		// $result = $fmt->formatCurrency($amt, $currency[0]);

		if ($currency[0] == 'EUR' || $currency[0] == 'RUB' || $currency[0] == 'CZK' || $currency[0] == 'HUF' || $currency[0] == 'PLN') {
			setlocale(LC_MONETARY, $lancode);
      if (function_exists('money_format')) {
          $result = money_format( '%!n ' . $currency[1], $amt);
      } else {
        $result = self::money_format( '%!n ' . $currency[1], $amt);
      }

		} else {
			setlocale(LC_MONETARY, $lancode);
      if (function_exists('money_format')) {
          $result = money_format( $currency[1] . '%!n', $amt);
      } else {
        $result = self::money_format( $currency[1] . '%!n', $amt);
      }
		}


		return $result;
	}

	private static function money_format($format, $number){
    $regex  = '/%((?:[\^!\-]|\+|\(|\=.)*)([0-9]+)?'.
              '(?:#([0-9]+))?(?:\.([0-9]+))?([in%])/';
    if (setlocale(LC_MONETARY, 0) == 'C') {
        setlocale(LC_MONETARY, '');
    }
    $locale = localeconv();
    preg_match_all($regex, $format, $matches, PREG_SET_ORDER);
    foreach ($matches as $fmatch) {
        $value = floatval($number);
        $flags = array(
            'fillchar'  => preg_match('/\=(.)/', $fmatch[1], $match) ?
                           $match[1] : ' ',
            'nogroup'   => preg_match('/\^/', $fmatch[1]) > 0,
            'usesignal' => preg_match('/\+|\(/', $fmatch[1], $match) ?
                           $match[0] : '+',
            'nosimbol'  => preg_match('/\!/', $fmatch[1]) > 0,
            'isleft'    => preg_match('/\-/', $fmatch[1]) > 0
        );
        $width      = trim($fmatch[2]) ? (int)$fmatch[2] : 0;
        $left       = trim($fmatch[3]) ? (int)$fmatch[3] : 0;
        $right      = trim($fmatch[4]) ? (int)$fmatch[4] : $locale['int_frac_digits'];
        $conversion = $fmatch[5];

        $positive = true;
        if ($value < 0) {
            $positive = false;
            $value  *= -1;
        }
        $letter = $positive ? 'p' : 'n';

        $prefix = $suffix = $cprefix = $csuffix = $signal = '';

        $signal = $positive ? $locale['positive_sign'] : $locale['negative_sign'];
        switch (true) {
            case $locale["{$letter}_sign_posn"] == 1 && $flags['usesignal'] == '+':
                $prefix = $signal;
                break;
            case $locale["{$letter}_sign_posn"] == 2 && $flags['usesignal'] == '+':
                $suffix = $signal;
                break;
            case $locale["{$letter}_sign_posn"] == 3 && $flags['usesignal'] == '+':
                $cprefix = $signal;
                break;
            case $locale["{$letter}_sign_posn"] == 4 && $flags['usesignal'] == '+':
                $csuffix = $signal;
                break;
            case $flags['usesignal'] == '(':
            case $locale["{$letter}_sign_posn"] == 0:
                $prefix = '(';
                $suffix = ')';
                break;
        }
        if (!$flags['nosimbol']) {
            $currency = $cprefix .
                        ($conversion == 'i' ? $locale['int_curr_symbol'] : $locale['currency_symbol']) .
                        $csuffix;
        } else {
            $currency = '';
        }
        $space  = $locale["{$letter}_sep_by_space"] ? ' ' : '';

        $value = number_format($value, $right, $locale['mon_decimal_point'],
                 $flags['nogroup'] ? '' : $locale['mon_thousands_sep']);
        $value = @explode($locale['mon_decimal_point'], $value);

        $n = strlen($prefix) + strlen($currency) + strlen($value[0]);
        if ($left > 0 && $left > $n) {
            $value[0] = str_repeat($flags['fillchar'], $left - $n) . $value[0];
        }
        $value = implode($locale['mon_decimal_point'], $value);
        if ($locale["{$letter}_cs_precedes"]) {
            $value = $prefix . $currency . $space . $value . $suffix;
        } else {
            $value = $prefix . $value . $space . $currency . $suffix;
        }
        if ($width > 0) {
            $value = str_pad($value, $width, $flags['fillchar'], $flags['isleft'] ?
                     STR_PAD_RIGHT : STR_PAD_LEFT);
        }

        $format = str_replace($fmatch[0], $value, $format);
    }
    return $format;
  }

	public static function getVersion() {
		$db = JFactory::getDbo();
		$query = $db->getQuery(true)
		->select('e.manifest_cache')
		->select($db->quoteName('e.manifest_cache'))
		->from($db->quoteName('#__extensions', 'e'))
		->where($db->quoteName('e.element') . ' = ' . $db->quote('com_splms'));

		$db->setQuery($query);
		$manifest_cache = json_decode($db->loadResult());

		if(isset($manifest_cache->version) && $manifest_cache->version) {
			return $manifest_cache->version;
		}

		return '1.0';
	}

}
