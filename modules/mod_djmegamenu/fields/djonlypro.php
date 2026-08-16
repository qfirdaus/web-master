<?php
/**
 * @package DJ-Megamenu
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski, Artur Kaczmarek
 *
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for the Joomla Platform.
 * Supports a one line text field.
 *
 * @link   http://www.w3.org/TR/html-markup/input.text.html#input.text
 * @since  11.1
 */
class JFormFieldDJOnlyPro extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'DJOnlyPro';

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput()
	{
		$lang = Factory::getApplication()->getLanguage();
		$lang->load('mod_djmegamenu', JPATH_ROOT, 'en-GB', true, false);
    	$lang->load('mod_djmegamenu', JPATH_ROOT . '/modules/mod_djmegamenu', 'en-GB', true, false);
    	$lang->load('mod_djmegamenu', JPATH_ROOT, null, true, false);
    	$lang->load('mod_djmegamenu', JPATH_ROOT . '/modules/mod_djmegamenu', null, true, false);

		$html = Text::sprintf('MOD_DJMEGAMENU_GET_PRO_LINK', Text::_('MOD_DJMEGAMENU_ONLY_PRO'));

		return $html;
	}
}
