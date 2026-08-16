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

use Joomla\Utilities\ArrayHelper;

class plgDJMediatoolsDJCatalog2Producers extends JPlugin
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
		require_once(JPATH_BASE.'/components/com_djcatalog2/helpers/html.php');
		
		Djcatalog2Helper::loadComponentLanguage();
		DJCatalog2ThemeHelper::setThemeAssets();
		
		JModelLegacy::addIncludePath(JPATH_BASE.'/components/com_djcatalog2/models');
		$model = JModelLegacy::getInstance('Producers', 'Djcatalog2Model', array('ignore_request'=>true));
		
		$order		= $params->get('plg_catalog2producers_orderby','i.ordering');
		$order_Dir	= $params->get('plg_catalog2producers_orderdir','asc');		
		$filter_catid		= $params->get('plg_catalog2producers_catid', array());
		$filter_itemids		= $params->get('plg_catalog2producers_item_ids', null);		
		
		$limit = $params->get('max_images');
		$default_image = $params->get('plg_catalog2producers_image');		
		
		$link_type = $params->get('plg_catalog2producers_linktype', 'profile');
		
		$cparams = $app->getParams('com_djcatalog2');
		$cparams->set('product_catalogue', 0);
		$model->setState('params', $cparams);
		
		$model->setState('list.start', 0);
		$model->setState('list.limit', $limit);

		$model->setState('filter.category',$filter_catid);
		$model->setState('filter.catalogue',false);
		$model->setState('list.ordering',$order);
		$model->setState('list.direction',$order_Dir);		
		
		if($filter_itemids) {
			$filter_itemids = explode(',', $filter_itemids);
			ArrayHelper::toInteger($filter_itemids);
			// $model->setState('filter.item_ids', $filter_itemids);
			$model->setState('filter.ids', $filter_itemids);
		}
		
		$items = $model->getItems();

		if (!empty($items)){
			if (!empty($filter_itemids)) {
				foreach ($items as $k_i => $v_i ) {
					if (!in_array($v_i->id, $filter_itemids)) {
						unset($items[$k_i]);	
					}
				}
			}
		}
		
		$pathPrefix = defined('DJCATIMGPATH') ? DJCATIMGPATH : 'media/djcatalog2/images';
		
		$slides = array();				
		
		if (!empty($items)){
			foreach ($items as $item) {
				$slide = (object) array();
				
				if(!empty($item->image_fullpath)) {
					$slide->image = $pathPrefix.'/'.$item->image_fullpath;
				} else if(!empty($item->item_image)) {
					$slide->image = $pathPrefix.'/'.$item->item_image;
				} else if(!empty($default_image)) {
					$slide->image = $default_image;
				} else {
					continue;
				}
			
				$slide->title = $item->name;
				$slide->description = $item->description;
	
				$slide->alt = $item->image_caption;
				$slide->id = $item->id;
				
				$slide->link = 
					JRoute::_(DJCatalog2HelperRoute::getProducerRoute($item->id.':'.$item->alias), false);
				
				if ('items' === $link_type) {
					$slide->link = JRoute::_(DJCatalog2HelperRoute::getCategoryRoute(0, $item->id.':'.$item->alias), false);
				}	
				
				$slides[] = $slide;	
			}
		}
		
		return $slides;
	}
	
	/*
	 * Define any requirements here (such as specific extensions installed etc.)
	 * 
	 * Returns true if requirements are met or text message about not met requirement
	 */
	public function onCheckRequirements(&$source) 
	{
		// Don't run this plugin when the source is different
		if ($source != $this->_name) {
			return null;
		}
		
		if(!JFile::exists(JPATH_ROOT.'/components/com_djcatalog2/djcatalog2.php')) return JText::_('PLG_DJMEDIATOOLS_DJCATALOG2PRODUCERS_COMPONENT_DISABLED');
		jimport('joomla.application.component.helper');
		$com = JComponentHelper::getComponent('com_djcatalog2', true);
		if(!$com->enabled) return JText::_('PLG_DJMEDIATOOLS_DJCATALOG2PRODUCERS_COMPONENT_DISABLED');
		
		return true;		
	}
}
