<?php
/**
 * @package DJ-Megamenu
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski, Michał Olczyk, Artur Kaczmarek
 *
 */

defined('_JEXEC') or die;

define ('DJUPDATER_PATH', JPATH_ROOT.'/modules/mod_djmegamenu'); //plugin path
define ('DJUPDATER_ELEMENT', 'pkg_dj-megamenu'); //element name in database
define ('DJUPDATER_NAME', 'DJ-MegaMenu'); //name of update server

define ('DJUPDATER_URL_PRO', 'https://dj-extensions.com/index.php?option=com_ars&view=update&task=stream&format=xml&id=5');
define ('DJUPDATER_URL_LIGHT', 'https://dj-extensions.com/index.php?option=com_ars&view=update&task=stream&format=xml&id=6');

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @link   http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since  11.1
 */
class JFormFieldDJUpdater extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'DJUpdater';

	/**
     * Method to get the field label markup for a spacer.
     * Use the label text or name from the XML element as the spacer or
     * Use a hr="true" to automatically generate plain hr markup
     *
     * @return  string  The field label markup.
     *
     * @since   11.1
     */
    protected function getLabel()
    {
    	return '';
    }

    /**
     * Method to get the field title.
     *
     * @return  string  The field title.
     *
     * @since   11.1
     */
    protected function getTitle()
    {
        return '';
    }

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{

		return '';
	}

	public static function setUpdateServer( $url ) {

		// update the update server information for package
		$db = Factory::getContainer()->get(DatabaseInterface::class);
		$query = "SELECT extension_id, manifest_cache FROM #__extensions WHERE element='".DJUPDATER_ELEMENT."'";
		$db->setQuery($query);
		$pkg = $db->loadObject();

		if($pkg) {
			$db->setQuery("SELECT COUNT(*) FROM #__update_sites WHERE name='".DJUPDATER_NAME."' AND type='extension'");
			if ($db->loadResult() > 0) {
				$db->setQuery("UPDATE #__update_sites SET
						location='".$url."'
						WHERE name='".DJUPDATER_NAME."' AND type='extension'");
			} else {
				$db->setQuery("INSERT INTO #__update_sites (`name`, `type`, `location`, `enabled`, `extra_query`) VALUES
				('".DJUPDATER_NAME."', 'extension', '".$url."', 1, '')");
				$db->execute();

				$update_site_id = $db->insertid();
				$db->setQuery("INSERT INTO #__update_sites_extensions (`update_site_id`, `extension_id`)
						VALUES (".$update_site_id.", ".$pkg->extension_id.")");
			}
			$db->execute();
		}
	}
}
