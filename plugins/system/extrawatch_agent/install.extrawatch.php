<?php
/**
 * @file
 * ExtraWatch - Real-time Visitor Analytics and Stats
 * @package ExtraWatch
 * @version 5.0
 * @revision 1
 * @license http://www.gnu.org/licenses/gpl-3.0.txt     GNU General Public License v3
 * @copyright (C) 2024 by CodeGravity.com - All rights reserved!
 * @website http://www.extrawatch.com
 */

defined('_JEXEC') or die('Restricted access');

class Plgsystemextrawatch_agentInstallerScript {

    const EW_PLUGIN_NAME = "extrawatch_agent";

    function install($parent) {
    }

    function uninstall($parent) {
    }

    function update($parent) {
    }

    function preflight($type, $parent) {
    }

    function postflight($type, $parent) {
        $this->activatePlugin();
    }


    public function activatePlugin() {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);
        $query->update('#__extensions');
        $query->set(array('enabled = 1', 'ordering = 9999'));
        $query->where("element = '". self::EW_PLUGIN_NAME ."'");
        $query->where("type = 'plugin'", 'AND');
        $query->where("folder = 'system'", 'AND');
        $db->setQuery($query);
        method_exists($db, 'execute') ? $db->execute() : $db->query();
    }
}