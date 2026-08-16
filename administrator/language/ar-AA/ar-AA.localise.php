<?php
/**
 * @version	   $Id: ar-AA.localise.php 100644 2016-11-02 16:15:23 Abu Nidal $
 * @package    Joomla.Language
 * @Copyright  Copyright (C) Translation 2010 - 2016 Ashraf Damra/Abu Nidal https://www.jarabic.com . All rights reserved.
 * @Copyright  Copyright (C) Hijri Calendar  2016 Saeed Hubaishan https://github.com/hubaishan/hijriDateLib .
 * @copyright  Copyright (C) 2005 - 2014 Open Source Matters, Inc. All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 */
/**
 * ar-AA localise class
 *
 * @package		Joomla.Language
 * @since		1.6
 */

jimport('joomla.utilities.date');
abstract class Ar_AALocalise 
{
	/**
	 * Returns the potential suffixes for a specific number of items
	 *
	 * @param	int $count  The number of items.
	 *
	 * @return	array  An array of potential suffixes.
	 *
	 * @since	1.6
	 */
	public static function getPluralSuffixes($count) 
	{
		if ($count == 0) 
		{
			$return = array('0');
		}
		elseif($count == 1) 
		{
			$return = array('1');
		}
		else 
		{
			$return = array('MORE');
		}
		return $return;
	}

	/**
	 * Returns the ignored search words
	 *
	 * @return	array  An array of ignored search words.
	 *
	 * @since	1.6
	 */
	public static function getIgnoredSearchWords() 
	{
		$search_ignore = array();
		$search_ignore[] = "و";
		$search_ignore[] = "في";
		$search_ignore[] = "على";
		$search_ignore[] = "من";
		$search_ignore[] = "عن";
		$search_ignore[] = "مع";
		$search_ignore[] = "بين";
		$search_ignore[] = "لو";
		$search_ignore[] = "إلى";
		$search_ignore[] = "حتى";
		return $search_ignore;
	}

	/**
	 * Returns the lower length limit of search words
	 *
	 * @return	integer  The lower length limit of search words.
	 *
	 * @since	1.6
	 */
	public static function getLowerLimitSearchWord() 
	{
		return 3;
	}

	/**
	 * Returns the upper length limit of search words
	 *
	 * @return	integer  The upper length limit of search words.
	 *
	 * @since	1.6
	 */
	public static function getUpperLimitSearchWord() 
	{
		return 75;
	}

	/**
	 * Returns the number of chars to display when searching
	 *
	 * @return	integer  The number of chars to display when searching.
	 *
	 * @since	1.6
	 */
	public static function getSearchDisplayedCharactersNumber() 
	{
		return 200;
	}
}

