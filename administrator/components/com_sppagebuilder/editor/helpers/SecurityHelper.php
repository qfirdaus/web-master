<?php

/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2026 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\Path;

/** No direct access */
defined('_JEXEC') or die('Restricted access');

final class SecurityHelper
{
	public static function isActionableFolder(string $folder)
	{
		$params = ComponentHelper::getParams('com_media');
		$filesFolderPath = $params->get('file_path', 'images');
		$imagesFolderPath = $params->get('image_path', 'images');

		$folder = strtolower(Path::clean($folder));
		$parts = explode(DIRECTORY_SEPARATOR, $folder);
		$parts = array_filter($parts, function ($part)
		{
			return !empty($part);
		});
		$parts = array_values($parts);

		foreach ($parts as $part)
		{
			if ($part === '..')
			{
				return false;
			}
		}

		if (empty($parts) || !is_array($parts) || count($parts) < 2 || ($parts[0] !== strtolower($filesFolderPath) && $parts[0] !== strtolower($imagesFolderPath)))
		{
			return false;
		}

		return true;
	}

	/**
	 * Check whether the current user may create or edit the given menu item.
	 *
	 * com_menus enforces this in its controller, so calling its item model directly
	 * skips the check entirely. Mirror it here for every such direct caller.
	 *
	 * @param   integer  $menuId    Menu item id being saved, 0 for a new item.
	 * @param   string   $menutype  Menutype the new item is created in.
	 *
	 * @return  boolean
	 */
	/**
	 * Folders the media upload and import paths are allowed to write into: the com_media
	 * roots, plus the fixed media/ subfolders this component uses for non-image types.
	 *
	 * Import destinations come from JSON inside an uploaded zip, so they never pass through
	 * Joomla's PATH input filter and can contain traversal that Path::clean() does not remove
	 * and Folder::create() does not refuse.
	 *
	 * @param   string  $folder  Root-relative folder from an upload or import payload.
	 *
	 * @return  boolean
	 */
	public static function isWritableMediaFolder(string $folder)
	{
		if (self::isGetablePath($folder))
		{
			return true;
		}

		$folder = strtolower(trim(Path::clean($folder, '/'), '/'));

		if ($folder === '' || strpos($folder, '..') !== false)
		{
			return false;
		}

		foreach (['media/videos', 'media/audios', 'media/attachments', 'media/fonts'] as $root)
		{
			if ($folder === $root || strpos($folder, $root . '/') === 0)
			{
				return true;
			}
		}

		return false;
	}

	public static function canManageMenuItem($menuId, $menutype = '')
	{
		$menuId = (int) $menuId;
		$db = Factory::getDbo();

		if ($menuId)
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('menutype'))
				->from($db->quoteName('#__menu'))
				->where($db->quoteName('id') . ' = ' . $menuId);
			$db->setQuery($query);
			$menutype = (string) $db->loadResult();

			// Protected menutype, com_menus never allows editing these.
			if ($menutype === 'main')
			{
				return false;
			}
		}

		$menutypeId = 0;

		if ($menutype !== '')
		{
			$query = $db->getQuery(true)
				->select($db->quoteName('id'))
				->from($db->quoteName('#__menu_types'))
				->where($db->quoteName('menutype') . ' = ' . $db->quote($menutype));
			$db->setQuery($query);
			$menutypeId = (int) $db->loadResult();
		}

		$action = $menuId ? 'core.edit' : 'core.create';

		return Factory::getUser()->authorise($action, 'com_menus.menu.' . $menutypeId);
	}

	public static function isGetablePath(string $path)
	{
		$params = ComponentHelper::getParams('com_media');
		$filesFolderPath = $params->get('file_path', 'images');
		$imagesFolderPath = $params->get('image_path', 'images');

		$path = strtolower(Path::clean($path));
		$pathArray = explode(DIRECTORY_SEPARATOR, $path);
		$pathArray = array_filter($pathArray, function ($part)
		{
			return !empty($part);
		});

		$pathArray = array_values($pathArray);

		if (in_array('..', $pathArray, true))
		{
			return false;
		}

		if (empty($pathArray) || !is_array($pathArray) || count($pathArray) < 1 || ($pathArray[0] !== strtolower($filesFolderPath) && $pathArray[0] !== strtolower($imagesFolderPath)))
		{
			return false;
		}

		return true;
	}
}
