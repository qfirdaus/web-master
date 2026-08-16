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

class plgDJMediatoolsJ2Store extends JPlugin
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
		$document =JFactory::getDocument();
		$document->addScript(JURI::root(true).'/media/j2store/js/j2store.js');
		
		if (!defined('F0F_INCLUDED'))
		{
			include_once JPATH_LIBRARIES . '/f0f/include.php';
		}
		// include helpers
		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/helpers/j2store.php');
		require_once (JPATH_SITE.'/components/com_content/router.php');
		require_once (JPATH_SITE.'/components/com_content/helpers/route.php');
		JModelLegacy::addIncludePath(JPATH_SITE.'/components/com_content/models', 'ContentModel');
		
		$main_params = J2Store::config();
		
		$max = $params->get('max_images');
		$default_image = $params->get('plg_j2store_image');
		$source = $params->get('plg_j2store_product_source', 'category');
		$itams = array();
		
		switch($source) {
		
			case 'item':
		
				$ids = $params->get('plg_j2store_items_list', '');
				$ids = explode(",", $ids);
				if($ids) {
					// Get an instance of the generic articles model
					$model = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
					$appParams = $app->getParams();
					$model->setState('params', $appParams);
					$model->setState('filter.published', 1);
					// Access filter
					$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
					$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
					$model->setState('filter.access', $access);
		
					$model->setState('filter.article_id', $ids);
					$model->setState('filter.article_id.include', true); // include
					$items = $model->getItems();
				}
				break;
			case 'category':
			default:
				$articles = JModelLegacy::getInstance('Articles', 'ContentModel', array('ignore_request' => true));
				// Set application parameters in model
				$app = JFactory::getApplication();
				$appParams = $app->getParams();
				$articles->setState('params', $appParams);
		
				// Set the filters based on the module params
				$articles->setState('list.start', 0);
				$articles->setState('list.limit', 10 * $max);
				$articles->setState('filter.published', 1);
		
				// Access filter
				$access = !JComponentHelper::getParams('com_content')->get('show_noauth');
				$authorised = JAccess::getAuthorisedViewLevels(JFactory::getUser()->get('id'));
				$articles->setState('filter.access', $access);
		
				//get category ids
				$catids = $params->get('plg_j2store_catid');
				$articles->setState('filter.category_id.include', (bool) $params->get('plg_j2store_category_filtering_type', 1));
		
				// Category filter
				if ($catids)
				{
					if ($params->get('plg_j2store_show_child_category_articles', 0) && (int) $params->get('plg_j2store_levels', 0) > 0)
					{
						// Get an instance of the generic categories model
						$categories = JModelLegacy::getInstance('Categories', 'ContentModel', array('ignore_request' => true));
						$categories->setState('params', $appParams);
						$levels = $params->get('plg_j2store_levels', 1) ? $params->get('plg_j2store_levels', 1) : 9999;
						$categories->setState('filter.get_children', $levels);
						$categories->setState('filter.published', 1);
						$categories->setState('filter.access', $access);
						$additional_catids = array();
		
						foreach ($catids as $catid)
						{
							$categories->setState('filter.parentId', $catid);
							$recursive = true;
							$items = $categories->getItems($recursive);
		
							if ($items)
							{
								foreach ($items as $category)
								{
									$condition = (($category->level - $categories->getParent()->level) <= $levels);
									if ($condition)
									{
										$additional_catids[] = $category->id;
									}
		
								}
							}
						}
		
						$catids = array_unique(array_merge($catids, $additional_catids));
					}
		
					$articles->setState('filter.category_id', $catids);
				}
		
				// Ordering
				$articles->setState('list.ordering', $params->get('plg_j2store_article_ordering', 'a.ordering'));
				$articles->setState('list.direction', $params->get('plg_j2store_article_ordering_direction', 'ASC'));
		
				// New Parameters
				$articles->setState('filter.featured', $params->get('plg_j2store_show_front', 'show'));
				/*
				$articles->setState('filter.author_id', $params->get('plg_j2store_created_by', ""));
				$articles->setState('filter.author_id.include', $params->get('plg_j2store_author_filtering_type', 1));
				$articles->setState('filter.author_alias', $params->get('plg_j2store_created_by_alias', ""));
				$articles->setState('filter.author_alias.include', $params->get('plg_j2store_author_alias_filtering_type', 1));
				$excluded_articles = $params->get('plg_j2store_excluded_articles', '');
		
				if ($excluded_articles)
				{
					$excluded_articles = explode("\r\n", $excluded_articles);
					$articles->setState('filter.article_id', $excluded_articles);
					$articles->setState('filter.article_id.include', false); // Exclude
				}
		
				$date_filtering = $params->get('plg_j2store_date_filtering', 'off');
				if ($date_filtering !== 'off')
				{
					$articles->setState('filter.date_filtering', $date_filtering);
					$articles->setState('filter.date_field', $params->get('plg_j2store_date_field', 'a.created'));
					$articles->setState('filter.start_date_range', $params->get('plg_j2store_start_date_range', '1000-01-01 00:00:00'));
					$articles->setState('filter.end_date_range', $params->get('plg_j2store_end_date_range', '9999-12-31 23:59:59'));
					$articles->setState('filter.relative_date', $params->get('plg_j2store_relative_date', 30));
				}
				*/
				// Filter by language
				$articles->setState('filter.language', $app->getLanguageFilter());
		
				$items = $articles->getItems();
				break;
		}
		
		// Find current Article ID if on an article page
		$option = $app->input->get('option');
		$view = $app->input->get('view');
		
		if ($option === 'com_content' && $view === 'article')
		{
			$active_article_id = $app->input->getInt('id');
		}
		else
		{
			$active_article_id = 0;
		}
		
		$product_image = $params->get('plg_j2store_product_image');
		$link_type =  $params->get('plg_j2store_link_j2store_detailproduct');
		$show_extra = $params->get('plg_j2store_show_price', 1) || $params->get('plg_j2store_show_cartbutton',1);
		$comments = $params->get('commnets',0);

		$already_exist =array();
		$slides = array();
		//$already_exist =array();
		$product_helper = J2Store::product();
		// Prepare data for display using display options
		foreach ($items as $single_item)
		{
			$ptable = F0FTable::getAninstance('Product', 'J2StoreTable');
			$ptable->load(array('product_source_id' => $single_item->id));
			$product = $product_helper->setId( $ptable->j2store_product_id)->getProduct();
			F0FModel::getTmpInstance('Products', 'J2StoreModel')->runMyBehaviorFlag(true)->getProduct($product);
			if( isset($product) && $product->enabled && $product->visibility && !in_array($product->j2store_product_id ,$already_exist)){
				
				$already_exist[] = $product->j2store_product_id;
				
				$slide = (object) array();
				
				// first get the image
				if($product_image=='j2store') {
					$slide->image = $product->main_image;
					if(!$slide->image) $slide->image = $product->thumb_image;
				}
				//} else { // article
				if($product_image=='article' || empty($slide->image)) {
					$images = new JRegistry($single_item->images);
					if($images->get('image_intro')) $slide->image = $images->get('image_intro');
					else if($images->get('image_fulltext')) $slide->image = $images->get('image_fulltext');
					else $slide->image = DJMediatoolsLayoutHelper::getImageFromText($single_item->introtext);
					//djdebug($single_item->fulltext);
					// if no image found in article images and introtext then try fulltext
					if(!$slide->image) $slide->image = DJMediatoolsLayoutHelper::getImageFromText($single_item->fulltext);
				}
				// if no image found in fulltext then take default image
				if(!$slide->image) $slide->image = $default_image;
				// if no default image set then don't display this article
				if(!$slide->image) continue;
				
				// get the product url
				if($link_type == 'article' ){
					$slide->link = $product->link = $product->product_view_url;
				} else {
					$menuitem = $params->get('plg_j2store_menuitem_id',0);
					$slide->link = $product->link = JRoute::_('index.php?option=com_j2store&view=products&task=view&id='.$product->j2store_product_id.'&Itemid='.$menuitem);
				}
				
				$product->product_link = $product->link;
				
				$slide->title = $single_item->title;
				$slide->description = $single_item->introtext;
				if(empty($slide->description)) $slide->description = $single_item->fulltext;
				
				$slide->id = $single_item->alias ? ($single_item->id . ':' . $single_item->alias) : $single_item->id;
				$slide->canonical = $slide->link;
				
				if($comments) {
					$host = str_replace(JURI::root(true), '', JURI::root());
					$host = preg_replace('/\/$/', '', $host);
					switch($comments) {
						case 1: // jcomments
							$slide->comments = array('id' => $single_item->id, 'group' => $link_type=='article' ? 'com_content':'com_j2store');
							break;
						case 2: // disqus
							$disqus_shortname = $params->get('disqus_shortname','');
							if(!empty($disqus_shortname)) {
								$slide->comments = array();
								$slide->comments['url'] =  $host . $slide->link;
								$slide->comments['identifier'] = substr(md5($disqus_shortname), 0, 10)."_id".$single_item->id;
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
				
				// render extra info such as price and add to cart button
				if($show_extra) {
					$product->source = $single_item;
					$item = $product;
					ob_start();
					include JPluginHelper::getLayoutPath('djmediatools', 'j2store');
					$slide->extra = ob_get_clean();
				}
				
				$slides[] = $slide;
				
				if(count($slides) >= $max) break;
			}
		}
		
		//$this->debug($slides);
		
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
		
		if(!JFile::exists(JPATH_ROOT.'/components/com_j2store/j2store.php')) return JText::_('PLG_DJMEDIATOOLS_J2STORE_COMPONENT_DISABLED');
		jimport('joomla.application.component.helper');
		$com = JComponentHelper::getComponent('com_j2store', true);
		if(!$com->enabled) return JText::_('PLG_DJMEDIATOOLS_J2STORE_COMPONENT_DISABLED');
		
		require_once (JPATH_ADMINISTRATOR.'/components/com_j2store/version.php');
		if(!version_compare(J2STORE_VERSION, '3.0.0', 'ge')) return JText::_('PLG_DJMEDIATOOLS_J2STORE_COMPONENT_VERSION3_REQUIRED');
		
		return true;		
	}
	
	function debug($data, $type = 'message') {
	
		$app = JFactory::getApplication();
		$app->enqueueMessage("<pre>".print_r($data, true)."</pre>", $type);
	
	}
}
