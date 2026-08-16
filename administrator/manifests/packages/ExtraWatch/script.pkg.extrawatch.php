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

jimport('joomla.filesystem.folder');
jimport('joomla.filesystem.file');

ini_set('display_errors', TRUE);
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));

class pkg_extrawatchInstallerScript {


    function postflight($action, $installer)
    {
        if ($action == "install") {
            $this->renderPostInstallMessage();
        }
    }

    private function renderPostInstallMessage() {

        $url = JURI::base()."/index.php?option=com_extrawatch";

        $app = JFactory::getApplication();
        $code = "<div style='color: black; margin: 10px;'><h2>ExtraWatch installed and activated successfully</h2>";
        $code .= "<h2 style='color: #1115AB'>How to access ExtraWatch?</h2>";
        $code .= "<ul style='background-image:none'><li>From the main menu choose <b>Components -&gt; ExtraWatch</b></li></ul><br/>";
        $code .= "<ul style='background-image:none'><a href='".$url."' class='btn btn-primary'>Open ExtraWatch analytics</a></ul>";
        $code .= "<br/>";

        $code .= "<h2 style='color: #1115AB'>Need help?</h2>";
        $code .= "<ul style='background-image:none'><li>Open a support ticket at <a href='http://www.extrawatch.com/support' target='_blank'>extrawatch.com</a></ul><br/>";
        $code .= "<h2 style='color: #1115AB'>Join the community of users of ExtraWatch, share your tips!</h2>";
        $code .= "<ul style='background-image:none'><li>Give us a like us on <a href='http://www.facebook.com/ExtraWatch' target='_blank'>Facebook</a> or follow us on <a href='http://www.twitter.com/ExtraWatch' target='_blank'>twitter</a> and get latest information!</li><br/>";
        $code .= "</ul></div>";
        $app->enqueueMessage($code);
    }


}
