<?php
/**
 * @version $Id$
 * @package DJ-Suggester
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-Suggester is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-Suggester is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-Suggester. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die('Restricted access');

jimport('joomla.html.html');
jimport('joomla.form.formfield');

class JFormFieldHikashopCategory extends JFormField
{
	protected $type = 'HikashopCategory';

	protected function getInput()
	{
		if(!JFile::exists(JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_hikashop' . DIRECTORY_SEPARATOR . 'hikashop.php')){
			return JText::_('PLG_DJMEDIATOOLS_HIKASHOP_COMPONENT_DISABLED');
		}
		
		$app 	= JFactory::getApplication();
		$db		= JFactory::getDbo();
		
		$query = 'SELECT * FROM #__hikashop_category WHERE category_type="product" ORDER BY category_depth, category_parent_id, category_ordering';
		$db->setQuery($query);
		
		$categories	= $db->loadObjectList();
		$categories = $this->getSortedItems($categories);
		
		ob_start();
		?>
		<select name="<?php echo $this->name;?>" class="<?php echo $this->element['class'];?>">
		<?php		
		foreach($categories as $category)
		{
			$selected	= ($category->category_id == $this->value ? ' selected="selected"' : '');
		?>
			<option value="<?php echo $category->category_id;?>"<?php echo $selected;?>><?php echo str_repeat('- ', $category->level) . $category->category_name;?></option>
		<?php
		}
		?>
		</select>
		<?php
		$html	= ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
	
	private function getSortedItems(&$items, $parent = 1, $level = 0)
	{
	
		$categories = array();
	
		foreach($items as $item) {
				
			if(isset($item->level)) {
				continue;
			}
			if($item->category_parent_id == $parent) {
				$item->level = $level;
				$categories[] = $item;
				$categories = array_merge($categories, $this->getSortedItems($items, $item->category_id, $level + 1));
			}
	
		}
	
		return $categories;
	}

}
