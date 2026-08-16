<?php

/**

 * @package DJ-MegaMenu
 * @copyright Copyright (C) 2024 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: https://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski, Artur Kaczmarek
 *
 * DJ-MegaMenu is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MegaMenu is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MegaMenu. If not, see <http://www.gnu.org/licenses/>.
 *
 */

defined('_JEXEC') or die;
// Note. It is important to remove spaces between elements.

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Helper\ModuleHelper;
use Joomla\CMS\Language\Text;

$stack = array();

//data attribute
$data_attr = array();

if($isJoomla4) $data_attr[] = 'data-joomla4';
$data_attr[] = 'data-tmpl="' . $template . '"';
$data_attr = implode(' ', $data_attr);

//class attribute
$class_attr = array();

$class_attr[] = 'dj-megamenu-' . $theme;
if( $wcag ) $class_attr[] = 'dj-megamenu-wcag';
if ( $custom_colors ) $class_attr[] = 'dj-megamenu-custom';
$class_attr[] = $params->get('orientation') . 'Menu ';
$class_attr[] = $fa_class;
$class_attr = implode(' ', $class_attr);

?>
<div class="dj-megamenu-wrapper" <?php echo $data_attr; ?>>
	<?php if ($params->get('fixed')) { ?>
		<div id="dj-megamenu<?php echo $module->id; ?>sticky" class="dj-megamenu <?php echo $class_attr; ?> dj-megamenu-sticky" style="display: none;">
			<?php if ($params->get('fixed_logo', 0) && $params->get('logo_type', 'inline') == 'fixed') { ?>
				<div id="dj-megamenu<?php echo $module->id; ?>stickylogo" class="dj-stickylogo dj-align-<?php echo $params->get('fixed_logo_align') ?>">
					<a href="<?php echo Uri::base(); ?>">
						<img src="<?php echo $params->get('fixed_logo') ?>" alt="<?php echo htmlspecialchars($params->get('fixed_logo_alt') ?: Factory::getApplication()->get('sitename')) ?>" <?php echo ($params->get('fixed_logo_width')) ? 'width="'.$params->get('fixed_logo_width').'"' : '' ?>  <?php echo ($params->get('fixed_logo_height')) ? 'height="'.$params->get('fixed_logo_height').'"' : '' ?> />
					</a>
				</div>
			<?php } ?>
		</div>
	<?php } ?>
	<ul id="dj-megamenu<?php echo $module->id; ?>" class="dj-megamenu <?php echo $class_attr; ?>" <?php echo !empty($options) ? "data-options='" . $options . "'" : ""; ?> data-trigger="<?php echo (int)$params->get('width') ?>" role="menubar" aria-label="<?php echo $module->title; ?>">
		<?php

		//add logo
		if($params->get('fixed_logo', 0) && $params->get('logo_type', 'inline') == 'inline') {
			$list = modDJMegaMenuHelper::addLogo($params);
		}

		$first = true;
		foreach ($list as $index => &$item) :
			$class = '';
			$aclass = '';
			$item_params = $item->getParams();

			if ($item->level == $startLevel) {
				$class .= 'dj-up';
				$aclass .= 'dj-up_a ';
			}
			$class .= ' itemid' . $item->id;
			if ($first) {
				$class .= ' first';
				$first = false;
			} else if ($item->level > $startLevel + 1 && $expand[$item->parent_id] == 'tree') {
				// don't break into column in expanded submenu tree
			} else if ($item->level > $startLevel && $item_params->get('djmegamenu-column_break', 0)) { // start new column if break point is set
				echo '</ul></div>';
				if ($item_params->get('djmegamenu-row_break', 0)) {
					echo '<div class="djsubrow_separator"></div>';
				}
				echo '<div class="dj-subcol" style="width:' . $item_params->get('djmegamenu-column_width') . '"><ul class="dj-submenu" role="menu" aria-label="' . $item->title . '">';
				$class .= ' first';
			}
			if ($item->id == $active_id) {
				$class .= ' current';
			}
			if (in_array($item->id, $path)) {
				$class .= ' active';
				$aclass .= ($item->level > $startLevel && $item->parent ? '-active active' : 'active');
			} elseif ($item->type == 'alias') {
				$aliasToId = $item_params->get('aliasoptions');
				if (in_array($aliasToId, $path)) {
					$class .= ' active';
					$aclass .= ($item->level > $startLevel && $item->parent ? '-active active' : 'active');
				}
			}

			if ($item->parent && $item->level == $startLevel && $item_params->get('djmegamenu-fullwidth')) { //full container
				$class .= ' fullsub';
			}

			if ($item->parent && $item->level == $startLevel && $item_params->get('djmegamenu-fullwidth') === '2') { //full screen width
				$class .= ' fullwidth';
			}

			if ($item->parent && (!$endLevel || $item->level < $endLevel)) {
				$class .= ' parent';
				if ($item->level > $startLevel) {
					$aclass = 'dj-more' . $aclass;
				}
			}

			if ($item->type == 'separator') {
				$class .= ' separator';
			}

			if ($item->type == 'alias') {
				$class .= ' alias';
			}

			if ( !empty($item->logo) ) {
				$class .= ' logo';
			}

			if (isset($item->modules)) {
				$class .= ' withmodule';
			}

			if ($item_params->get('djmegamenu-show') == 'mobile') {
				$class .= ' dj-hideitem';
			}

			if ($item->parent && $item->level > $startLevel && $expand[$item->id] == 'tree') {
				$class .= ' subtree';
			}

			if (!empty($class)) {
				$class = ' class="' . trim($class) . '"';
			}

			$li_role = ($item_params->get('djmegamenu-module_show_link', 0) || (!isset($item->mobilemodules) && !isset($item->modules))) ? 'none' : 'menuitem';
			echo '<li' . $class . ' role="' . $li_role . '">';

			if ($item_params->get('djmegamenu-module_show_link', 0) || (!isset($item->mobilemodules) && !isset($item->modules))) {
				// Render the menu item.
				require ModuleHelper::getLayoutPath('mod_djmegamenu', 'default_url');
			}
			if (isset($item->modules)) {
				echo '<div class="modules-wrap">' . $item->modules . '</div>';
			}
			// echo $item->level;
			// The next item is deeper.
			if ($item->deeper) {
				$stack[] = $item->id;
				if ($item->level > $startLevel && $expand[$item->id] == 'tree') {
					echo '<ul class="dj-subtree" role="menu" aria-label="' . $item->title . '">';
				} else {

          if ($item_params->get('djmegamenu-fullwidth') === '2') {
            $style = '';
						$style_in = '';
						$open_dir = '';
					} elseif ($item_params->get('djmegamenu-fullwidth')) {
						$style = 'width: 100%;';
						$style_in = 'width: 100%;';
						$open_dir = '';
					} else {
						$style = '';
						$style_in = 'width:' . $subwidth[$item->id] . 'px;';

						$open_dir = $item_params->get('djmegamenu-dropdown_dir', $params->get('dropdown_dir'), '');
						if (!empty($open_dir)) $open_dir = 'open-' . $open_dir;
					}

					$image = $item_params->get('djmegamenu-bg_image', '');
					if (!empty($image)) {
						if (strcasecmp(substr($image, 0, 4), 'http') !== 0) {
							$image = Uri::root(true) . '/' . $image;
						}
						$style_in .= ' background-image: url(' . $image . '); '
							. ' background-position: ' . $item_params->get('djmegamenu-bg_pos_hor', 'right') . ' ' . $item_params->get('djmegamenu-bg_pos_ver', 'bottom') . ';'
							. ' background-repeat: no-repeat;';
					}

					echo '<div class="dj-subwrap ' . $open_dir . ' ' . ($subcols[$item->id] > 1 ? 'multiple_cols' : 'single_column') . ' subcols' . $subcols[$item->id] . '" style="' . $style . '"><div class="dj-subwrap-in" style="' . $style_in . '">';
					echo '<div class="dj-subcol" style="width:' . $item_params->get('djmegamenu-first_column_width') . '"><ul class="dj-submenu" role="menu" aria-label="' . $item->title . '">';
				}
				$first = true;
			}
			// The next item is shallower.
			elseif ($item->shallower) {
				echo '</li>';
				for ($i = $item->level - 1; $i >= $item->level - $item->level_diff; $i--) {
					$parent = array_pop($stack);
					if ($expand[$parent] == 'tree' && $i > $startLevel) {
						echo '</ul></li>';
					} else {
						echo '</ul></div></div></div></li>';
					}
				}
			}
			// The next item is on the same level.
			else {
				echo '</li>';
			}
		endforeach;
		?></ul>
	<?php if ( $mobilemenu == '2' ) { //OFFCANVAS ?>
		<?php require( ModuleHelper::getLayoutPath('mod_djmegamenu', 'default_offcanvas') ); ?>
	<?php } else if ( $mobilemenu == '3' ) { //ACCORDION ?>
		<?php require( ModuleHelper::getLayoutPath('mod_djmegamenu', 'default_accordion') ); ?>
	<?php } else if($mobilemenu == '1') { //SELECT ?>
		<?php require( ModuleHelper::getLayoutPath('mod_djmegamenu', 'default_select') ); ?>
	<?php } ?>
</div>
