<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Http\Http;

/** No direct access */
defined('_JEXEC') or die('Restricted access');

final class ApplicationHelper
{
	public static function generateSiteClassName($addonName)
	{
		if (empty($addonName))
		{
			return '';
		}

		if (stripos($addonName, 'easystore_') === 0)
		{
			$parts = explode('_', $addonName);
			$parts = array_map(function ($part)
			{
				return ucfirst($part);
			}, $parts);

			return 'SppagebuilderAddon' . implode('', $parts);
		}

		return 'SppagebuilderAddon' . ucfirst($addonName);
	}

	public static function hasPageContent(int $id)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('content')
			->from($db->quoteName('#__sppagebuilder'))
			->where($db->quoteName('id') . ' = ' . (int)$id);

		$db->setQuery($query);

		try
		{
			$result = $db->loadResult();

			if (\is_string($result) && !empty($result))
			{
				$result = \json_decode($result);
			}

			return !empty($result);
		}
		catch (\Exception $e)
		{
			return false;
		}
	}

	public static function sanitizePageText($text)
	{
		$text = $text ?? '[]';
		$text = !\is_string($text) ? json_encode($text) : $text;
		$parsed = SpPageBuilderAddonHelper::__($text);
		$parsed = SppagebuilderHelperSite::sanitize($parsed);

		return json_decode($parsed);
	}

	public static function preparePageData($pageData)
	{
		if (empty($pageData))
		{
			return (object) [
				'text' => new stdClass
			];
		}

		$content = [];

		if (empty($pageData->content))
		{
			$pageData->text = SppagebuilderHelperSite::prepareSpacingData($pageData->text);
			$pageData->text = self::sanitizePageText($pageData->text);

			return $pageData;
		}

		if (\is_string($pageData->content))
		{
			$content = \json_decode($pageData->content);
		}

		if (empty($content))
		{
			$pageData->text = SppagebuilderHelperSite::prepareSpacingData($pageData->text);
			$pageData->text = self::sanitizePageText($pageData->text);

			return $pageData;
		}

		$version = SppagebuilderHelper::getVersion();
		$storedVersion = $pageData->version;
		$pageData->text = $content;
		$pageData->text = SppagebuilderHelperSite::prepareSpacingData($pageData->text);
		$pageData->text = json_decode($pageData->text);

		if ($version !== $storedVersion)
		{
			$pageData->text = self::sanitizePageText(json_encode($pageData->text));
		}


		return $pageData;
	}

	public static function isEasyStoreInstalled()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('enabled')
			->from($db->quoteName('#__extensions'))
			->where($db->quoteName('name') . ' = ' . $db->quote('com_easystore'));

		$db->setQuery($query);

		$isEasyStorePro = is_dir(JPATH_ROOT . '/components/com_easystore/sppagebuilder');

		try
		{
			return ((int) $db->loadResult() === 1 && $isEasyStorePro);
		}
		catch (Exception $e)
		{
			return false;
		}

		return false;
	}

	public static function getStorePageId($viewType, $language = null)
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);

		$query->select('id')
			->from($db->quoteName('#__sppagebuilder'))
			->where($db->quoteName('extension') . ' = ' . $db->quote('com_easystore'))
			->where($db->quoteName('extension_view') . ' = ' . $db->quote($viewType));

		if (!is_null($language))
		{
			// Store pages are multilingual: one row per (extension_view, language). Legacy rows
			// created before language support have language = '' (the column's schema default),
			// not '*' - treat them as the same "All" row so lookups for '*' still find them.
			if ($language === '*')
			{
				$query->where('(' . $db->quoteName('language') . ' = ' . $db->quote('*') . ' OR ' . $db->quoteName('language') . ' = ' . $db->quote('') . ')');
			}
			else
			{
				$query->where($db->quoteName('language') . ' = ' . $db->quote($language));
			}
		}
		else
		{
			// Prefer the "All (*)" row so the default open is deterministic.
			$query->order('CASE WHEN ' . $db->quoteName('language') . ' = ' . $db->quote('*') . ' THEN 0 ELSE 1 END ASC')
				->order($db->quoteName('id') . ' ASC');
		}

		$db->setQuery($query, 0, 1);

		try
		{
			return $db->loadResult() ?? 0;
		}
		catch (Exception $error)
		{
			return 0;
		}

		return 0;
	}

	public static function isProVersion()
	{
		return true;
	}

    /**
     * Get the application configuration
     *
     * @return Registry
     * @since 5.5.1
     */
    public static function getAppConfig()
    {
        if (JVERSION < 4) {
            return Factory::getConfig();
        }

		/** @var CMSApplication */
		$app = Factory::getApplication();
		return $app->getConfig();
    }
}
