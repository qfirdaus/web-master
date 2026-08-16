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

defined('_JEXEC') or die();
defined('JPATH_BASE') or die;
defined('DS') or define('DS', DIRECTORY_SEPARATOR);

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Database\DatabaseInterface;

class JFormFieldDJModules extends FormField {

	protected $type = 'DJModules';

	protected function getInput()
	{
		$attr = 'multiple="true"';

		$attr .= $this->element['class'] ? ' class="'.(string) $this->element['class'].'"' : '';
		$attr .= ((string) $this->element['disabled'] == 'true') ? ' disabled="disabled"' : '';
		$attr .= $this->element['size'] ? ' size="'.(int) $this->element['size'].'"' : '';

		$db	= Factory::getContainer()->get(DatabaseInterface::class);
		$lang = Factory::getApplication()->getLanguage()->getTag();
		$where = 'language IN (' . $db->quote($lang) . ',' . $db->quote('*') . ')';
		$query = "SELECT * FROM #__modules WHERE client_id=0 AND $where ORDER BY position, ordering";

		$db->setQuery($query);
		$modules = $db->loadObjectList();

		$options = array();

		if(count($modules)) foreach($modules as $module){
			$options[] = HTMLHelper::_('select.option', $module->module.'|'.$module->title, $module->title);
		}

		$html = HTMLHelper::_('select.genericlist', $options, $this->name, trim($attr), 'value', 'text', $this->value);

		return ($html);

	}
}
?>
