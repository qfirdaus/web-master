<?php
/**
 * @file
 * ExtraWatch - Real-time visitor dashboard and stats
 * @package ExtraWatch
 * @version 5.0
 * @revision 1
 * @license http://www.gnu.org/licenses/gpl-3.0.txt     GNU General Public License v3
 * @copyright (C) 2024 by CodeGravity.com - All rights reserved!
 * @website http://www.extrawatch.com
 */

class ExtraWatchProject {

		private $extraWatchRequestHelper;
		private $extraWatchCMSSpecific;

    /**
     * ExtraWatchProject constructor.
     */
    public function __construct(ExtraWatchCMSSpecific $extraWatchCMSSpecific) {

        $this->extraWatchRequestHelper = new ExtraWatchAPI($extraWatchCMSSpecific);
        $this->extraWatchCMSSpecific = $extraWatchCMSSpecific;

    }

    public function createProjectForUrl($url, $token) {
        if (!$this->extraWatchCMSSpecific->isAdmin()) {
            die("Not authorized");
        }

        $email = $this->extraWatchCMSSpecific->getPluginOptionEmail();
        return $this->extraWatchRequestHelper->createProjectForUrl($url, $email, $token);
    }

}
