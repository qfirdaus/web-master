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

class plgDJMediatoolsHikashop extends JPlugin
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
		
		require_once(JPATH_ADMINISTRATOR.'/components/com_hikashop/helpers/helper.php');
		require_once(JPATH_BASE.'/components/com_hikashop/views/product/view.html.php');
		
		$view = new ProductViewProduct(array( 'base_path'=>HIKASHOP_FRONT ));
		$view->setLayout('listing');
		
		// create params for hikashop view
		$mparams = new JRegistry();
		$mparams->set('show_limit', 0);
		$mparams->set('from_module', 'djmediatools');
		$mparams->set('content_type', 'product');
		$mparams->set('add_to_wishlist', 0);
		$mparams->set('link_to_product_page', 0);
		$mparams->set('show_vote_product', 0);
		$mparams->set('display_custom_item_fields', 0);
		$mparams->set('display_filters', 0);
		$mparams->set('display_badges', 0);
		
		// get options from album
		$mparams->def('limit', $params->get('max_images'));
		$mparams->def('content_synchronize', (int)$params->get('plg_hikashop_content_synchronize'));
		$mparams->def('product_synchronize', $params->get('plg_hikashop_product_synchronize'));
		$mparams->def('recently_viewed', (int)$params->get('plg_hikashop_recently_viewed'));
		$mparams->def('selectparentlisting', $params->get('plg_hikashop_selectparentlisting'));
		$mparams->def('filter_type', $params->get('plg_hikashop_filter_type'));
		$mparams->def('product_order', $params->get('plg_hikashop_product_order'));
		$mparams->def('order_dir', $params->get('plg_hikashop_order_dir'));
		$mparams->def('itemid', $params->get('plg_hikashop_itemid'));
		$mparams->def('add_to_cart', $params->get('plg_hikashop_add_to_cart'));
		$mparams->def('show_quantity_field', $params->get('plg_hikashop_show_quantity_field'));
		$mparams->def('show_out_of_stock', $params->get('plg_hikashop_show_out_of_stock'));
		$mparams->def('show_price', $params->get('plg_hikashop_show_price'));
		$mparams->def('random', ($mparams->get('product_order')=='random' ? 1 : 0));
		
		$show_price = (bool) $mparams->get('show_price');
		$show_addtocart = (bool) $mparams->get('add_to_cart');
		$default_image = $params->get('plg_hikashop_image');
		
		/* hikashop authors force me to do it, because their component is not object oriented and there is no other way
		 * to get listing of the products unless we will rewrite the whole component logic, what is nonsens :)
		 */
		ob_start();
		$view->display(null,$mparams);
		$js = @$view->js;
		ob_get_clean(); // we don't need this, just $view object after view rendering
		
		//$this->debug($view->row);
		
		$slides = array();
		
		foreach($view->rows as $item){
			$slide = (object) array();
			
			$slide->image = $item->file_path;
			$slide->alt = $item->file_name;
			
			if(empty($slide->image)) $slide->image = DJMediatoolsLayoutHelper::getImageFromText($item->product_description);
			
			// if no image found in product description then take default image
			if(empty($slide->image)) $slide->image = $default_image;
			else $slide->image = str_replace(JURI::root(true), '', $view->image->uploadFolder_url) . $slide->image;
			
			// if no default image set then don't display this product
			if(empty($slide->image)) continue;
			
			$slide->title = $item->product_name;
			$slide->description = $item->product_description;
			
			$slide->canonical = $slide->link = hikashop_contentLink('product&task=show&cid='.$item->product_id.'&name='.$item->alias.$view->itemid.$view->category_pathway,$item);
			$slide->id = $item->product_id .':'.$item->alias;
			
			if($comments = $params->get('commnets',0)) {
				$host = str_replace(JURI::root(true), '', JURI::root());
				$host = preg_replace('/\/$/', '', $host);
				switch($comments) {
					case 1: // jcomments
						$slide->comments = array('id' => $item->product_id, 'group' => 'com_hikashop');
						break;
					case 2: // disqus
						$disqus_shortname = $params->get('disqus_shortname','');
						if(!empty($disqus_shortname)) {
							$slide->comments = array();
							$slide->comments['url'] =  $host . $slide->link;
							$slide->comments['identifier'] = $disqus_shortname.'-hikashop-'.$item->product_id; // ??
						}
						break;
					case 3: // facebook
						$slide->comments = $host . $slide->link;
						break;
					case 4: //komento
						// not implemented
						break;
				}
			}
			
			$view->row = &$item;
			
			if($show_price) {				
				$view->setLayout('listing_price');
				$slide->extra = $view->loadTemplate();
			}
			
			if($show_addtocart) {
				if(!isset($slide->extra)) $slide->extra = '';
				$view->setLayout('add_to_cart_listing');
				$slide->extra .=  $view->loadTemplate();
			}
			
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
		
		if(!JFile::exists(JPATH_ROOT.'/components/com_hikashop/hikashop.php')) return JText::_('PLG_DJMEDIATOOLS_HIKASHOP_COMPONENT_DISABLED');
		jimport('joomla.application.component.helper');
		$com = JComponentHelper::getComponent('com_hikashop', true);
		if(!$com->enabled) return JText::_('PLG_DJMEDIATOOLS_HIKASHOP_COMPONENT_DISABLED');
		
		//if(!JFile::exists(JPATH_ROOT.'/modules/mod_djclassifieds_items/helper.php')) return JText::_('PLG_DJMEDIATOOLS_DJCLASSIFIEDS_ITEMS_MODULE_NOT_INSTALLED');
		
		return true;		
	}
	
	function debug($data, $type = 'message') {
	
		$app = JFactory::getApplication();
		$app->enqueueMessage("<pre>".print_r($data, true)."</pre>", $type);
	
	}
}
