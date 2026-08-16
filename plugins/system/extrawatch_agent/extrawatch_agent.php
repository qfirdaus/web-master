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

jimport('joomla.plugin.plugin');
jimport('joomla.environment.browser');
jimport('joomla.filesystem.file');
jimport('joomla.application.module.helper');

if (!defined("DS")) {
    define("DS", DIRECTORY_SEPARATOR);
}

class plgSystemExtraWatch_Agent extends JPlugin {

    const EXTRAWATCH_AGENT_URL = "
            var _extraWatchParams = _extraWatchParams || [];
            _extraWatchParams.projectId = '%s';
            (function() {
                var ew = document.createElement('script'); ew.type = 'text/javascript'; ew.async = true;
                ew.src = 'https://agent.extrawatch.com/agent/js/ew.js';
                var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ew, s);
            })();
            ";

    function __construct(&$subject, $config) {
        parent::__construct($subject, $config);
    }


    public function onBeforeCompileHead() {
        if ($this->checkIfAdminLoaded()) {
            return;
        }
        $extrawatchProjectId = $this->findProjectId();
        $this->renderExtraWatchAgentHeader($extrawatchProjectId);
    }

    public function onBeforeRender() {
        if (!$this->isJoomlaAdmin() ) {
        }
    }


    public function onAfterRoute() {
        if (@strstr($_SERVER['REQUEST_URI'],'?option=com_ajax&plugin=extrawatch_agent')) {
        }
    }

    private function renderExtraWatchAgentHeader($extrawatchProjectId) {
        if ($extrawatchProjectId) {
            JFactory::getDocument()->addScriptDeclaration(
                sprintf(self::EXTRAWATCH_AGENT_URL, $extrawatchProjectId)
            );
        }
    }

    private function findProjectId() {

        $this->requireFileIfExists(realpath(__DIR__ . DS ."..".DS."..".DS."..".DS."administrator". DS. "components". DS. "com_extrawatch". DS. "ew-plg-common" . DS . "ExtraWatchSettings.php"));
        $this->requireFileIfExists(realpath(__DIR__ . DS ."..".DS."..".DS."..".DS."administrator". DS. "components". DS. "com_extrawatch". DS. "ew-plg-common" . DS . "cms" . DS . "ExtraWatchJoomlaSpecific.php"));

        $extraWatchLiveJoomlaSpecific = new ExtraWatchJoomlaSpecific();
        $extrawatchProjectId = $extraWatchLiveJoomlaSpecific->getPluginOptionProjectId();
        return $extrawatchProjectId;
    }

    private function requireFileIfExists($file) {
        if (!file_exists($file)) {
            return;
        }
        require_once($file);
    }

    private function checkIfAdminLoaded() {
        $uri = @$_SERVER['REQUEST_URI'];
        if (@strstr($uri,'/administrator/')) {
            return TRUE;
        }
        return FALSE;

    }

    private function isJoomlaAdmin() {
        return @strstr($_SERVER['REQUEST_URI'],"/administrator/");
    }

}
