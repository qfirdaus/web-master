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
defined('_JEXEC') or die;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Version;
use Joomla\CMS\Form\Form;
use Joomla\Filesystem\Exception\FilesystemException;
use Joomla\Filesystem\File;
use Joomla\CMS\Factory;

require_once(dirname(__FILE__) .'/helpers/legacy.php');

class plgSystemDJMegaMenu extends CMSPlugin
{
    function onContentPrepareForm($form, $data)
    {
        $isJoomla4 = version_compare(DJLegacyHelper::getJoomlaVersion(), '4', '>=');

        // extra option for menu item
        if ($form->getName() == 'com_menus.item') {
            $this->loadLanguage();
            Form::addFormPath(dirname(__FILE__) . DIRECTORY_SEPARATOR . 'params');
            $form->loadFile('djmmitem', true);
        }

        if($form->getName() == 'com_modules.module' || $form->getName() == 'com_advancedmodules.module') {
            // delete old custom themes
            if(isset($data->module) && $data->module == 'mod_djmegamenu') {
                $path = JPATH_ROOT . '/media/djmegamenu/themes/custom'.$data->id.'.css';
                try {
                    File::delete($path);
                } catch (FilesystemException $e) {}
                $path = JPATH_ROOT . '/media/djmegamenu/themes/custom'.$data->id.'_rtl.css';
                try {
                File::delete($path);
                } catch (FilesystemException $e) {}
                $path = JPATH_ROOT . '/media/djmegamenu/mobilethemes/custom'.$data->id.'.css';
                try {
                File::delete($path);
                } catch (FilesystemException $e) {}
                $path = JPATH_ROOT . '/media/djmegamenu/mobilethemes/custom'.$data->id.'_rtl.css';
                try {
                File::delete($path);
                } catch (FilesystemException $e) {}
            }
        }

    }

    function onBeforeRender() {

        $app = Factory::getApplication();
        $doc = Factory::getApplication()->getDocument();;


        if(DJLegacyHelper::isAdmin()) {
            return;
        }

        $items = $app->getMenu()->getItems(array(), array());
        $css = '.dj-hideitem'; // hide menu items before they are removed from DOM

        foreach($items as $item) {

			$item_params = $item->getParams();

            $modules = ($item_params->get('djmegamenu-module_pos') || $item_params->get('djmobilemenu-module_pos')) ? true : false;
            $show_link = ($item_params->get('djmegamenu-module_show_link') || $item_params->get('djmobilemenu-module_show_link')) ? true : false;
            if(($modules && !$show_link) || $item_params->get('djmegamenu-show')) {
                $css .= ", li.item-$item->id";
            }
        }

        $css .= " { display: none !important; }\n";
        $doc->addStyleDeclaration($css);
    }

    function onAfterRender() {

        $app = Factory::getApplication();

        if(DJLegacyHelper::isAdmin()) {
            return;
        }

        $version = new Version();
        $isJoomla4 = version_compare($version->getShortVersion(), '4', '>=');
		$template = $app->getTemplate();

		$FXnotSupported = ( 'cassiopeia' == $template ) ? true : false;

        $addWrappers = $this->params->get('wrappers', 1);

        $documentFormat = $app->input->getCmd('format', 'html');

        if (!$FXnotSupported && $addWrappers && ($documentFormat == 'html' || is_null($documentFormat))) {

            if (version_compare(JVERSION, '3.2.3', '>=')) {
                $html = $app->getBody();
            } else {
                $html = JResponse::getBody();
            }

            if( preg_match("/<body[^>]*>(.*?)<\/body>/is", $html, $matches)) {

                $body = '<div class="dj-offcanvas-wrapper"><div class="dj-offcanvas-pusher"><div class="dj-offcanvas-pusher-in">'.$matches[1].'</div></div></div>';

				//check if offcanvas effect 1 'slide in on top'
				$simple_FX = preg_match('/aside(.*)dj-offcanvas(.*)data-effect="1"/is', $html);
                if( !$simple_FX ) $html = str_replace($matches[1], $body, $html);
            }

            if (version_compare(JVERSION, '3.2.3', '>=')) {
                $app->setBody($html);
            } else {
                JResponse::setBody($html);
            }
        }
    }

    function debug($msg){
        $app = Factory::getApplication();
        $app->enqueueMessage("<pre>".print_r($msg, true)."</pre>");
    }

}
