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

use Joomla\CMS\Helper\ModuleHelper;

?>
<div id="dj-megamenu<?php echo $module->id; ?>mobile" class="dj-megamenu-accordion <?php echo 'dj-megamenu-accordion-' . $params->get('mobiletheme') . ' dj-pos-' . $params->get('accordion_pos') . ' ' . ' dj-align-' . $params->get('accordion_align') . ' ' . $fa_class . ' ' . $class_sfx; ?>">
	<?php require(ModuleHelper::getLayoutPath('mod_djmegamenu', 'default_btn')); ?>
	<div class="dj-accordion <?php echo 'dj-accordion-' . $params->get('mobiletheme') . ' ' . $class_sfx; ?>">
		<div class="dj-accordion-in">
			<?php require(ModuleHelper::getLayoutPath('mod_djmegamenu', 'default_mobile')); ?>
		</div>
	</div>
</div>
