<?php
/**

 * @package DJ-MediaTools
 * @copyright Copyright (C) 2024 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: https://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski, Artur Kaczmarek
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

defined('_JEXEC') or die;

use Joomla\CMS\Helper\ModuleHelper;

// Note. It is important to remove spaces between elements.
?>
<?php // The menu class is deprecated. Use nav instead. ?>
<ul class="dj-mobile-nav <?php echo 'dj-mobile-'.$params->get('mobiletheme') . ' ' . $class_sfx;?>" role="menubar">
<?php
foreach ($list as $i => &$item)
{
	if( !empty($item->logo) ) continue;

	$item_params = $item->getParams();
	$class = 'dj-mobileitem itemid-' . $item->id;

	if (($item->id == $active_id) OR ($item->type == 'alias' AND  $item_params->get('aliasoptions') == $active_id))
	{
		$class .= ' current';
	}



	if (in_array($item->id, $path))
	{
		$class .= ' active';
	}
	elseif ($item->type == 'alias')
	{
		$aliasToId = $item_params->get('aliasoptions');

		if (count($path) > 0 && $aliasToId == $path[count($path) - 1])
		{
			$class .= ' active';
		}
		elseif (in_array($aliasToId, $path))
		{
			$class .= ' alias-parent-active';
		}
	}

	if ($item->type == 'separator')
	{
		$class .= ' divider';
	}

	if ($item->deeper)
	{
		$class .= ' deeper';
	}

	if ($item->parent)
	{
		$class .= ' parent';
	}

	if($item_params->get('djmegamenu-show') == 'mega') {
		$class .= ' dj-hideitem';
	}

	if (!empty($class))
	{
		$class = ' class="' . trim($class) . '"';
	}

	$li_role = ($item_params->get('djmobilemenu-module_show_link', 0) || (!isset($item->mobilemodules) && !isset($item->modules))) ? 'none' : 'menuitem';
	echo '<li' . $class . ' role="' . $li_role . '">';

	if($item_params->get('djmobilemenu-module_show_link',0) || (!isset($item->mobilemodules) && !isset($item->modules))) {
		// Render the menu item.
		require ModuleHelper::getLayoutPath('mod_djmegamenu', 'default_mobile_url');
	}
	if(isset($item->mobilemodules)) {
		echo '<div class="modules-wrap">'.$item->mobilemodules.'</div>';
	}

	// The next item is deeper.
	if ($item->deeper)
	{
		echo '<ul class="dj-mobile-nav-child">';
	}
	elseif ($item->shallower)
	{
		// The next item is shallower.
		echo '</li>';
		echo str_repeat('</ul></li>', $item->level_diff);
	}
	else
	{
		// The next item is on the same level.
		echo '</li>';
	}
}
?></ul>
