<?php
/**
 * @package     JO Visitor Counter
 * @subpackage  jovisitorcounter
 *
 * @copyright   Copyright (C) 2025 JewelOsman.Com. All rights reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

class plgSystemJovisitorcounterInstallerScript
{
    public function postflight($type, $parent)
    {
        if (!in_array($type, ['install', 'update']))
        {
            return;
        }

        $db = Factory::getContainer()->get(DatabaseInterface::class);

        // Enable the plugin (published = 1)
        $query = $db->getQuery(true)
            ->update($db->quoteName('#__extensions'))
            ->set($db->quoteName('enabled') . ' = 1')
            ->where($db->quoteName('type') . ' = ' . $db->quote('plugin'))
            ->where($db->quoteName('folder') . ' = ' . $db->quote('system'))
            ->where($db->quoteName('element') . ' = ' . $db->quote('jovisitorcounter'));

        try
        {
            $db->setQuery($query)->execute();
        }
        catch (\Exception $e)
        {
            // Silent fail or log if needed
        }
    }
}
