<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );
jimport('joomla.form.formfield');
class JFormFieldJ2StoreMenuItem extends JFormField
{

	protected $type = 'J2storemenuitem';
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string	The field input markup.
	 * @since   1.6
	 */
	protected function getInput()
	{
		$app = JFactory::getApplication();
		$options = array();
		$module_id = $app->input->getInt('id');
		$menus =JMenu::getInstance('site');
		$menu_id = null;
		$menuItems = array();
		foreach($menus->getMenu() as $item)
		{
			if($item->type== 'component'){
				if(isset($item->query['option']) && $item->query['option'] == 'com_j2store' ){
					if(isset($item->query['catid'])){
						$options[$item->id] = $item->title;
					}
				}
			}
		}
	 return JHTML::_('select.genericlist', $options, $this->name, array('class'=>"input"), 'value', 'text', $this->value);
	}

}

