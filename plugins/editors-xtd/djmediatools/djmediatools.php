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
defined('_JEXEC') or die;

class plgButtonDJMediatools extends JPlugin
{
	
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		$this->loadLanguage();
	}

	/**
	 * Display the button
	 *
	 * @return array A two element array of (imageName, textToInsert)
	 */
	public function onDisplay($name)
	{
		$app = JFactory::getApplication();
		//if($app->isSite()) return;
		
		$doc = JFactory::getDocument();
		$template = $app->getTemplate();
		
		$js = "
		function jInsertDJMedia_".md5($name)."(catid, img, title, cover) {
			if(cover) {
				var tag = '<img class=\"djalbum_link\" src=\"' + img + '\" style=\"display: block; margin: 10px auto; \" alt=\"djalbum_link:' + catid + '\" title=\"' + title + '\" width=\"290\" />';
			} else {
				var tag = '<div><img src=\"' + img + '\" style=\"background: #f5f5f5 url(".JURI::root(true)."/administrator/components/com_djmediatools/assets/icon.png) 10px center no-repeat; display: block; max-width: 100%; max-height: 300px; margin: 10px auto; padding: 10px 10px 10px 110px; border: 1px solid #ddd; -moz-box-sizing: border-box; box-sizing: border-box;\" alt=\"djmedia:' + catid + '\" title=\"' + title + '\" /></div>';
			}
		";
		if(version_compare(JVERSION, '4', '>=')) {
			$js.= "
				window.parent.Joomla.editors.instances['".$name."'].replaceSelection(tag);
				window.parent.Joomla.Modal.getCurrent().close();
	    		return false;
			}";
		} else {
			$js.= "
				jInsertEditorText(tag, '".$name."');
				SqueezeBox.close();
			}";
            JHtml::_('behavior.modal');
		}
		
		$doc->addScriptDeclaration($js);
				
		$link = 'index.php?option=com_djmediatools&amp;view=categories&amp;layout=modal&amp;tmpl=component&amp;f_name=jInsertDJMedia_'.md5($name);
		

		
		$button = new JObject;
		$button->modal = true;
		$button->class = 'btn';
		$button->link = $link;
		$button->text = JText::_('PLG_EDITORSXTD_DJMEDIATOOLS_BUTTON');
		$button->name = 'camera';
		$button->icon = 'camera-2';
		$button->options = "{handler: 'iframe', size: {x: 1200, y: 500}}";
        $button->iconSVG = '<svg width="24" height="24" viewBox="0 0 512 512"><path d="M464 64H48C21.49 64 0 85.49 0 112v288c0 26.51 21.49 48'
            . ' 48 48h416c26.51 0 48-21.49 48-48V112c0-26.51-21.49-48-48-48zm-6 336H54a6 6 0 0 1-6-6V118a6 6 0 0 1 6-6h404a6 6'
            . ' 0 0 1 6 6v276a6 6 0 0 1-6 6zM128 152c-22.091 0-40 17.909-40 40s17.909 40 40 40 40-17.909 40-40-17.909-40-40-40'
            . 'zM96 352h320v-80l-87.515-87.515c-4.686-4.686-12.284-4.686-16.971 0L192 304l-39.515-39.515c-4.686-4.686-12.284-4'
            . '.686-16.971 0L96 304v48z"></path></svg>';

		return $button;
	}
}
