<?php
/**
 * @version $Id: djcatalog2.php 42 2014-09-24 12:20:47Z szymon $
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

class plgDJMediatoolsDJClassifiedsGallery extends JPlugin
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
		require_once(JPATH_BASE.'/administrator/components/com_djclassifieds/lib/djtheme.php');
		require_once(JPATH_BASE.'/administrator/components/com_djclassifieds/lib/djseo.php');
		require_once(JPATH_BASE.'/administrator/components/com_djclassifieds/lib/djimage.php');

		$view = $app->input->getCmd('view');
		$itemId = $entryId = $app->input->getInt('id');
		$categoryId = $app->input->getInt('cid');
		$producerId = $app->input->getInt('pid');
	
		$type = 'item';

		// album's 'image on click bahavior: open items details in modal window' fix
		if($app->input->getCmd('option') == 'com_djmediatools' && $view == 'item'){
			$inputCookie  = JFactory::getApplication()->input->cookie;
			$latest_items = $inputCookie->get('djcf_lastitems', '');
			$latest_items_ids = explode('_', $latest_items);
			if(!empty($latest_items_ids[0])){
				$itemId = $latest_items_ids[0];
			}
		}

		$items = DJClassifiedsImage::getAdsImages($itemId);
		//echo '<pre>'.print_r($items,true).'</pre>'; return;
		
		if (empty($items)) {
			return null;
		}

		$slides = array();
		
		foreach($items as $item){
			
			$slide = (object) array();
			
			if(!empty($item->path)) {
				$slide->image = substr($item->path.'/'.$item->name.'.'.$item->ext, 1);
				if(!JFile::exists(JPATH_ROOT . '/' . $slide->image)) {
					$slide->image = $item->thumb_b;
				}
				$slide->image = str_replace('//', '/', $slide->image);
				$slide->image = preg_replace('/^\//', '', $slide->image);
			} else {
				continue;
			}
			$slide->title = $item->caption ? $item->caption : $item->name;
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
		
		if(!JFile::exists(JPATH_ROOT.'/components/com_djclassifieds/djclassifieds.php')) return JText::_('PLG_DJMEDIATOOLS_DJCLASSIFIEDSGALLERY_COMPONENT_DISABLED');
		jimport('joomla.application.component.helper');
		$com = JComponentHelper::getComponent('com_djclassifieds', true);
		if(!$com->enabled) return JText::_('PLG_DJMEDIATOOLS_DJCLASSIFIEDSGALLERY_COMPONENT_DISABLED');
		
		$app = JFactory::getApplication();
		$view = $app->input->getCmd('view');
		$views = array('item');
		if ($app->input->getCmd('option') != 'com_djclassifieds' || !in_array($view, $views)) {
			return false;
		}
		
		return true;		
	}

	function debug($data, $type = 'message') {
		
		$app = JFactory::getApplication();		
		$app->enqueueMessage("<pre>".print_r($data, true)."</pre>", $type);
		
	}
}
