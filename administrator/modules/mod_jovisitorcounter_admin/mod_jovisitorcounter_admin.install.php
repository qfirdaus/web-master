<?php
/**
 * @package     JO Visitor Counter
 * @subpackage  mod_jovisitorcounter_admin
 *
 * @copyright   Copyright (C) 2025 JewelOsman.Com. All rights reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

class mod_jovisitorcounter_adminInstallerScript
{
    public function postflight($type, $parent)
    {
        // Only run after install or update
        if ($type !== 'install' && $type !== 'update') {
            return;
        }

        $module = 'mod_jovisitorcounter_admin';
        $position = 'cpanel';

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Check if module exists
        $query = $db->getQuery(true)
            ->select($db->quoteName('id'))
            ->from($db->quoteName('#__modules'))
            ->where($db->quoteName('module') . ' = ' . $db->quote($module))
            ->where($db->quoteName('client_id') . ' = 1');

        $db->setQuery($query);
        $moduleId = $db->loadResult();

        if (!$moduleId)
        {
            return;
        }

        // Set position and publish status
        $updateQuery = $db->getQuery(true)
            ->update($db->quoteName('#__modules'))
            ->set([
                $db->quoteName('position') . ' = ' . $db->quote($position),
                $db->quoteName('published') . ' = 1',
                $db->quoteName('ordering') . ' = 1',
				$db->quoteName('access') . ' = 3'
            ])
            ->where($db->quoteName('id') . ' = ' . (int) $moduleId);

        try
        {
            $db->setQuery($updateQuery)->execute();
        }
        catch (\Exception $e)
        {
            // Log error silently
        }

        // Ensure module is linked to the admin dashboard (menuid = 0)
        $query = $db->getQuery(true)
            ->select('COUNT(*)')
            ->from($db->quoteName('#__modules_menu'))
            ->where($db->quoteName('moduleid') . ' = ' . (int) $moduleId)
            ->where($db->quoteName('menuid') . ' = 0');

        $db->setQuery($query);
        $exists = (int) $db->loadResult();

        if (!$exists)
        {
            $columns = ['moduleid', 'menuid'];
            $values = [(int) $moduleId, 0];

            $query = $db->getQuery(true)
                ->insert($db->quoteName('#__modules_menu'))
                ->columns($db->quoteName($columns))
                ->values(implode(',', $values));

            try {
                $db->setQuery($query)->execute();
            } catch (\Exception $e) {
                // Log error silently
            }
        }
    }
}
