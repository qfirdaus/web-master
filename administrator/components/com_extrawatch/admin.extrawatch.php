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

require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "cms" . DIRECTORY_SEPARATOR . "ExtraWatchJoomlaSpecific.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "auth". DIRECTORY_SEPARATOR . "ExtraWatchOAuth2Request.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "auth". DIRECTORY_SEPARATOR . "ExtraWatchAuth.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "ExtraWatchURLHelper.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "ExtraWatchController.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "ExtraWatchRequestHelper.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "ExtraWatchRenderer.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "ExtraWatchPrerequisites.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "ExtraWatchLogin.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "ExtraWatchProject.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "ExtraWatchConfig.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "ExtraWatchSettings.php");
require_once("ew-plg-common" . DIRECTORY_SEPARATOR . "ExtraWatchAPI.php");


$doc = JFactory::getDocument();
$doc->addStyleSheet(JUri::base(true).'/components/com_extrawatch/css/style.css');

$extraWatchLiveController = new ExtraWatchController(new ExtraWatchJoomlaSpecific());
echo $extraWatchLiveController->controlLivePage();
