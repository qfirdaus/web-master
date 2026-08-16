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

class plgDJMediatoolsDJCatalog2Gallery extends JPlugin
{
	/**
	 * Plugin that returns the object list for DJ-Mediatools album
	 * 
	 * Each object must contain following properties (mandatory): title, description, image
	 * Optional properties: link, target (_blank or _self), alt (alt attribute for image)
	 * 
	 * @param	object	The album params
	 */
	public function onAlbumPrepare(&$source, &$params)
	{
		// Lets check the requirements
		$check = $this->onCheckRequirements($source);
		if (is_null($check) || is_string($check)) {
			return null;
		}
						
		$app = JFactory::getApplication();
		require_once(JPATH_BASE.'/components/com_djcatalog2/helpers/route.php');
		require_once(JPATH_BASE.'/components/com_djcatalog2/helpers/djcatalog2.php');
		require_once(JPATH_BASE.'/components/com_djcatalog2/helpers/theme.php');
		require_once(JPATH_BASE.'/administrator/components/com_djcatalog2/helpers/image.php');
		require_once(JPATH_BASE.'/components/com_djcatalog2/helpers/price.php');
		require_once(JPATH_BASE.'/components/com_djcatalog2/helpers/html.php');
		
		$view = $app->input->getCmd('view');
		$itemId = $entryId = $app->input->getInt('id');
		$categoryId = $app->input->getInt('cid');
		$producerId = $app->input->getInt('pid');
	
		$type = 'item';
		if ($view == 'items') {
			$type = 'category';
			$entryId = $categoryId;
		} else if ($view == 'producer') {
			$type = 'producer';
			$entryId = $producerId;
		}
		$items = DJCatalog2ImageHelper::getImages($type, $entryId);
		
		if (empty($items)) {
			return null;
		}
		
		$pathPrefix = defined('DJCATIMGPATH') ? DJCATIMGPATH : 'media/djcatalog2/images';

		$slides = array();
		
		foreach($items as $item){
			
			$slide = (object) array();
			
			if(!empty($item->fullpath)) {
				$slide->image = $pathPrefix.'/'.$item->fullpath;
			} else if(!empty($item->fullname)) {
				$slide->image = $pathPrefix.'/'.$item->fullname;
			} else {
				continue;
			}
			$slide->title = $item->caption;
			$slide->description = '';//$item->intro_desc;			
			$slide->canonical = $slide->link = '';//JRoute::_(DJCatalogHelperRoute::getItemRoute($app->input->getString('id'), $app->input->getString('cid')));			
			$slide->alt = $item->caption;
			$slide->id = $item->id;
	
	
			$slides[] = $slide;
		}
		
		return $slides;		
	}
	
	/*
	 * Define any requirements here (such as specific extensions installed etc.)
	 * 
	 * Returns true if requirements are met or text message about not met requirement
	 */
	public function onCheckRequirements(&$source) {
		
		// Don't run this plugin when the source is different
		if ($source != $this->_name) {
			return null;
		}
		
		if(!JFile::exists(JPATH_ROOT.'/components/com_djcatalog2/djcatalog2.php')) return JText::_('PLG_DJMEDIATOOLS_DJCATALOG2GALLERY_COMPONENT_DISABLED');
		jimport('joomla.application.component.helper');
		$com = JComponentHelper::getComponent('com_djcatalog2', true);
		if(!$com->enabled) return JText::_('PLG_DJMEDIATOOLS_DJCATALOG2GALLERY_COMPONENT_DISABLED');
		
		$app = JFactory::getApplication();
		$view = $app->input->getCmd('view');
		$views = array('item', 'items', 'producer');
		if ($app->input->getCmd('option') != 'com_djcatalog2' || !in_array($view, $views)) {
			return false;
		}
		
		return true;		
	}

	function debug($data, $type = 'message') {
		
		$app = JFactory::getApplication();		
		$app->enqueueMessage("<pre>".print_r($data, true)."</pre>", $type);
		
	}
}
