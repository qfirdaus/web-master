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

use Joomla\CMS\Language\Text;

$btnText = $params->get('mobile_button_text', Text::_('MOD_DJMEGAMENU_DEFAULT_BUTTON_TEXT'));
?>
<button class="dj-mobile-open-btn <?php echo $fa_class; ?>" aria-label="<?php echo Text::_('MOD_DJMEGAMENU_OPEN_MENU_BTN'); ?>"><?php
	if($params->get('mobile_button', 'icon') == 'icon' || $params->get('mobile_button') == 'icon_text') { ?><span class="dj-mobile-open-icon" aria-hidden="true"></span><?php }
	if($params->get('mobile_button', 'icon') == 'text' || $params->get('mobile_button') == 'icon_text') { ?><span class="dj-mobile-open-label"><?php echo $btnText ?></span><?php }
?></button>
