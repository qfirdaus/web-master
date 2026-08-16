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

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\Database\DatabaseInterface;
use Joomla\Filesystem\File;

class modDJMMHelper extends modMenuHelper {

	private static $subwidth = array();
	private static $subcols = array();
	private static $expand = array();
	private static $rows = array();
	public static $main_items = array();
	public static $modules = null;
	public static $mobilemodules = null;
	private static $version = null;
	public static $list = array();

	public static function parseParams(&$params) {

		$params->def('menutype', $params->get('menu','mainmenu'));
		$params->def('startLevel', 1);
		$params->def('endLevel', 0);
		$params->def('showAllChildren', 1);
		$params->def('mobiletheme', 'dark');
		$params->set('column_width', (int)$params->get('column_width',200));
		$params->def('width', 979);
		$params->def('select_type', 'button');
		$params->def('accordion_pos', 'static');
		$params->def('accordion_align', 'center');
		$params->def('accordion_collapsed', 0);
		$params->def('icons', '2');
		$params->def('subtitles', '2');


		if($params->get('pro')) {
			$params->def('fixed_logo', 0);
			$params->def('fixed_logo_align', 'right');
			$params->def('orientation', 'horizontal');

			//enable custom Colors when deprecated custom theme
			if($params->get('theme')=='_custom') $params->set('customColors', 1);
			if($params->get('mobiletheme')=='_custom') $params->set('customMobileColors', 1);

		} else {
			$params->set('fixed', 0);
			$params->set('openDelay', 0);
			$params->set('orientation', 'horizontal');
			$params->set('mobile_button', 'icon');
		}
		if($params->get('orientation') == 'vertical') {
			$params->set('fixed', 0);
			$params->set('wrapper', '');
		}

		//set default theme instead of custom (deprecated)
		if($params->get('theme')=='_custom') $params->set('theme', 'default');
		if($params->get('mobiletheme')=='_custom') $params->set('mobiletheme', 'dark');

	}

	public static function getActive(&$params) {

		$menu = Factory::getApplication()->getMenu();

		// Get active menu item from parameters
		if ($params->get('active')) {
			$active = $menu->getItem($params->get('active'));
		} else {
			$active = false;
		}

		// If no active menu, use current or default
		if (!$active) {
			$active = ($menu->getActive()) ? $menu->getActive() : $menu->getDefault();
		}

		return $active;
	}

	public static function getList(&$params) {

		$list = parent::getList($params);

		// array with submenu wrapper widths
		if(!isset(self::$subwidth[$params->get('module_id')])) {

			self::$subwidth[$params->get('module_id')] = array();
			self::$subcols[$params->get('module_id')] = array();
			self::$expand[$params->get('module_id')] = array();
			self::$rows[$params->get('module_id')] = array();

			$i = 0;
			$first = false;
			$parent = null;
			$hasSubtitles = false;
			$startLevel = $params->get('startLevel');

			foreach($list as $item) {
				$item_params = $item->getParams();

				if($item->level == $startLevel) self::$main_items[$i] = $item->id;
				$i++;

				if($params->get('orientation')=='vertical' && $item_params->get('djmegamenu-fullwidth')) {
					$item_params->set('djmegamenu-fullwidth', 0);
					$item_params->set('djmegamenu-column_width', '');
				}

				if($parent || $item_params->get('djmegamenu-column_break',0)) {

					if(!$params->get('pro')) {
						$item_params->set('djmegamenu-column_width', $params->get('column_width'));
					}

					if(isset(self::$rows[$params->get('module_id')][$item->parent_id])) { // child of full width submenu

						if(!isset(self::$subwidth[$params->get('module_id')][$item->parent_id])) self::$subwidth[$params->get('module_id')][$item->parent_id] = 0;

						$width = (int)$item_params->get('djmegamenu-column_width',$params->get('percent_width', 25));

						if($width > 100) $width = 100;

						if($width + self::$subwidth[$params->get('module_id')][$item->parent_id] > 100) {
							$item_params->set('djmegamenu-row_break', 1);
							self::$rows[$params->get('module_id')][$item->parent_id]++;
							self::$subwidth[$params->get('module_id')][$item->parent_id] = 0;
						}

						self::$subwidth[$params->get('module_id')][$item->parent_id] += $width;

						if($parent) {
							$parent_params = $parent->getParams();
							$parent_params->set('djmegamenu-first_column_width', $width.'%');
							$parent=null;
						} else {
							$item_params->set('djmegamenu-column_width', $width.'%');
						}

					} else { // pixels widths

						$width = (int)$item_params->get('djmegamenu-column_width',$params->get('column_width'));

						if($parent) {
							$parent_params = $parent->getParams();
							$parent_params->set('djmegamenu-first_column_width', $width.'px');
							$parent=null;
						} else {
							$item_params->set('djmegamenu-column_width', $width.'px');
						}

						// calculate width of the sum
						if(!isset(self::$subwidth[$params->get('module_id')][$item->parent_id])) self::$subwidth[$params->get('module_id')][$item->parent_id] = 0;
						self::$subwidth[$params->get('module_id')][$item->parent_id] += (int)$item_params->get('djmegamenu-column_width',$params->get('column_width'));

					}

					// count number of columns for this submenu
					if(!isset(self::$subcols[$params->get('module_id')][$item->parent_id])) self::$subcols[$params->get('module_id')][$item->parent_id] = 1;
					else self::$subcols[$params->get('module_id')][$item->parent_id]++;
				}

				if($item->deeper) {
					$first = true;
					$parent = $item;

					if($params->get('pro') && $item->level == $startLevel && $item_params->get('djmegamenu-fullwidth')) {
						self::$rows[$params->get('module_id')][$item->id] = 1;
						//echo "<pre>".print_r($item, true)."</pre>";
					}
				}

				// load module if position set
				if($params->get('pro') && $position = $item_params->get('djmegamenu-module_pos')) {
					$item->modules = modDJMegaMenuHelper::loadModules($position,$item_params->get('djmegamenu-module_style','xhtml'));
				}
				// load module if position set
				if($params->get('pro') && $position = $item_params->get('djmobilemenu-module_pos')) {
					$item->mobilemodules = modDJMegaMenuHelper::loadModules($position,$item_params->get('djmobilemenu-module_style','xhtml'));
				}

				$subtitle = htmlspecialchars($item_params->get('djmegamenu-subtitle',''));
				if(empty($subtitle) && $params->get('usenote')) $subtitle = htmlspecialchars($item->note);
				if($item->menu_image && !$item_params->get('menu_text', 1)) $subtitle = null;
				$item_params->set('djmegamenu-subtitle', $subtitle);

				if($item->level == $startLevel && !empty($subtitle)) $hasSubtitles = true;

				if($item->parent) self::$expand[$params->get('module_id')][$item->id] = $item_params->get('djmegamenu-expand',
						isset(self::$expand[$params->get('module_id')][$item->parent_id]) ? self::$expand[$params->get('module_id')][$item->parent_id] : $params->get('expand','dropdown'));
			}

			$params->def('hasSubtitles',$hasSubtitles);
		}
		self::$list = $list;
		return $list;
	}

	public static function getSubWidth(&$params) {

		if(!isset(self::$subwidth[$params->get('module_id')])) self::getList($params);

		return self::$subwidth[$params->get('module_id')];
	}

	public static function getSubCols(&$params) {

		if(!isset(self::$subcols[$params->get('module_id')])) self::getList($params);

		return self::$subcols[$params->get('module_id')];
	}

	public static function getExpand(&$params) {

		if(!isset(self::$expand[$params->get('module_id')])) self::getList($params);

		return self::$expand[$params->get('module_id')];
	}

	public static function getMainItems() {
		return self::$main_items;
	}

	public static function getCenterItem() {
		$items = self::getMainItems();
		$num = count($items);
		$keys = array_keys($items);
		$center = round($num/2, 0, PHP_ROUND_HALF_DOWN);

		return $keys[$center]; //return index of center item
	}

	public static function logoMenuItem(&$params) {

		$sitename = Factory::getApplication()->get('sitename');

		$logo = new Joomla\CMS\Menu\MenuItem;
		$logo->id = 0;
		$logo->title = $params->get('fixed_logo_alt','') ?: $sitename;
		$logo->alias = 'djmegamenu-logo';
		$logo->level = 1;
		$logo->access = 1;
		$logo->language = '*';
		$logo->menu_image = $params->get('fixed_logo');
		$logo->link = Uri::base();
		$logo->type = 'url';
		$logo->logo = 1;
		$logo->anchor_title = $sitename;
		$logo->parent = '';
		$logo->anchor_css = '';
		$logo->flink = '';
		$logo->anchor_rel = '';
		$logo->deeper = '';
		$logo->shallower = '';
		$logo->width = $params->get('fixed_logo_width', '');
		$logo->height = $params->get('fixed_logo_height', '');
		return $logo;


	}

	public static function addLogo(&$params) {

		$list = (isset(self::$list)) ? self::$list : self::getList($params);

		$logo = self::logoMenuItem($params);
		$logo_position = $params->get('fixed_logo_align', 'left');

		if( $logo_position == 'center' ) {
			$positionIndex = self::getCenterItem();
			array_splice( $list, $positionIndex, 0, array($logo) ); //center
		} elseif( $logo_position == 'right' ) {
			$list[] = $logo; //last item
		} else {
			array_unshift($list, $logo); //first item
		}

		return $list;
	}

	public static function getFile( $file, &$params ) {
		// Start capturing output into a buffer
		ob_start();

		// Include the requested template filename in the local scope
		// (this will execute the view logic).
		include $file;

		// Done with the requested template; get the buffer and
		// clear it.
		$content = ob_get_contents();
		ob_end_clean();

		return $content;
	}

	public static function addTheme(&$params, $direction) {

		$app = Factory::getApplication();
		$doc = Factory::getApplication()->getDocument();

		$ver = self::getVersion($params);

		if($params->get('theme')!='_override') { //regular theme
			if( $params->get('theme')=='_custom' ) {
				$params->set('theme', 'default');
			}
			$css = 'modules/mod_djmegamenu/themes/'.$params->get('theme','default').'/css/djmegamenu.css';
		} else { //override from template
			$params->set('theme', 'override');
			$css = 'templates/'.$app->getTemplate().'/css/djmegamenu.css';
		}

		// load theme only if file exists or ef4 template in use
		if(file_exists(JPATH_ROOT . '/' . $css) || defined('JMF_EXEC')) {
			$doc->addStyleSheet(Uri::root(true).'/'.$css, array('version' => $ver));
		}
		if($direction == 'rtl') { // load rtl theme css if file exists or ef4 template in use
			$css_rtl = File::stripExt($css).'_rtl.css';
			if(file_exists(JPATH_ROOT . '/' . $css_rtl) || defined('JMF_EXEC')) {
				$doc->addStyleSheet(Uri::root(true).'/'.$css_rtl, array('version' => $ver));
			}
		}

		if( $params->get('customColors', '0') && $params->get('pro') ) {
			$path = JPATH_ROOT . '/' . 'modules/mod_djmegamenu/themes/'.$params->get('theme','default').'/custom.css.php';
			if( file_exists($path) ) {
				$custom_styles = preg_replace('/\s+/S', " ", modDJMegaMenuHelper::getFile($path, $params));
				$custom_styles = trim($custom_styles);

				if( !empty( $custom_styles ) ) {
					$doc->addStyleDeclaration($custom_styles);
				}
			}
		}

	}

	public static function addMobileTheme(&$params, $direction) {

		$app = Factory::getApplication();
		$doc = Factory::getApplication()->getDocument();;

		$ver = self::getVersion($params);

		if($params->get('mobiletheme')!='_override') {
			if( $params->get('mobiletheme')=='_custom' ) {
				$params->set('mobiletheme', 'dark');
			}
			$css = 'modules/mod_djmegamenu/mobilethemes/'.$params->get('mobiletheme','dark').'/djmobilemenu.css';
		} else {
			$params->set('mobiletheme', 'override');
			$css = 'templates/'.$app->getTemplate().'/css/djmobilemenu.css';
		}

		// add only if theme file exists
		if(file_exists(JPATH_ROOT . '/' . $css)) {
			$doc->addStyleSheet(Uri::root(true).'/'.$css, array('version' => $ver));
		}
		if($direction == 'rtl') { // load rtl css if exists in theme or joomla template
			$css_rtl = File::stripExt($css).'_rtl.css';
			if(file_exists(JPATH_ROOT . '/' . $css_rtl)) {
				$doc->addStyleSheet(Uri::root(true).'/'.$css_rtl, array('version' => $ver));
			}
		}

		if( $params->get('customMobileColors', '0') && $params->get('pro') ) {
			$path = JPATH_ROOT . '/' . 'modules/mod_djmegamenu/mobilethemes/'.$params->get('mobiletheme','dark').'/custom.css.php';
			if( file_exists($path) ) {
				$custom_styles = preg_replace('/\s+/S', " ", modDJMegaMenuHelper::getFile($path, $params));
				$custom_styles = trim($custom_styles);

				if( !empty( $custom_styles ) ) {
					$doc->addStyleDeclaration($custom_styles);
				}
			}
		}
	}

	public static function getVersion($params) {

		if(is_null(self::$version)) {

			$db = Factory::getContainer()->get(DatabaseInterface::class);
			$db->setQuery("SELECT manifest_cache FROM #__extensions WHERE element='mod_djmegamenu' LIMIT 1");
			$ver = json_decode($db->loadResult());
			self::$version = $ver->version . ($params->get('pro', 0) ? '.pro' : '.free');
		}

		return self::$version;
	}

	public static function parseBBcode( $name ) {
		$subs = array(
			'/\[b\](.+)\[\/b\]/Ui' => '<strong>$1</strong>',
			'/\[i\](.+)\[\/i\]/Ui' => '<em>$1</em>',
			'/\[ico\](.+)\[\/ico\]/Ui' => '<span class="dj-icon $1" aria-hidden="true"></span>',
		);

		$name = preg_replace(array_keys($subs), array_values($subs), $name);
		return $name;
	}
}

?>
