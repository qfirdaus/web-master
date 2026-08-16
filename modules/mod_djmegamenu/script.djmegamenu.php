<?php
// No direct access to this file
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\Path;

class Mod_DJMegamenuInstallerScript
{
	/*
	 * $parent is the class calling this method.
	 * $type is the type of change (install, update or discover_install, not uninstall).
	 * preflight runs before anything else and while the extracted files are in the uploaded temp folder.
	 * If preflight returns false, Joomla will abort the update and undo everything already done.
	 */

	function postflight( $type, $parent ) {

		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$config = Factory::getApplication()->getConfig();

		if($type == 'install') {

			$db->setQuery("UPDATE #__extensions SET enabled=1 WHERE type='plugin' AND element='djmegamenu'");
			$db->execute();
		}

		if($type == 'update') {

			// we need to handle the update server for package here
			require_once(Path::clean(JPATH_ROOT.'/modules/mod_djmegamenu/fields/djupdater.php'));
			JFormFieldDJUpdater::setUpdateServer(DJUPDATER_URL_PRO);

			// disable old update server
			$db->setQuery("UPDATE #__update_sites SET enabled=0 WHERE name='DJ-MegaMenu Package' AND type='extension'");
			$db->execute();
		}

	}
}
