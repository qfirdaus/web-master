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

use Joomla\Registry\Registry;

class plgDJMediatoolsContent extends JPlugin
{
	public function __construct(&$dispatcher, $config = array())
	{
		$lang = JFactory::getLanguage();
		$module = 'mod_articles_category';

		$lang->load($module, JPATH_ROOT , 'en-GB', true, false);
		$lang->load($module, JPATH_ROOT .'/modules/'.$module, 'en-GB', true, false);
		$lang->load($module, JPATH_ROOT , null, true, false);
		$lang->load($module, JPATH_ROOT .'/modules/'.$module, null, true, false);
		
		parent::__construct($dispatcher, $config);
	}
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
		$sourceParams = $params->get('source_params', array());
		
		if (version_compare(JVERSION, '4', '>=')) { // Joomla 4+
			require_once(JPATH_ROOT . '/plugins/djmediatools/content/Helper/modArticlesCategoryHelper.php');
		} else {
			require_once(JPATH_ROOT.'/modules/mod_articles_category/helper.php');
		}
		
		$modParams = new Registry;
		$modParams->loadArray($sourceParams);
		
		$modParams->set('count', $params->get('max_images'));
		
		// translate old params to new for backward compatibility
		$type = $params->get('plg_content_type');
		if(!empty($type)) {
			
			$catid = $params->get('plg_content_id');
			$modParams->set('catid', array($catid));
			$modParams->set('category_filtering_type', 1);
			$levels = $params->get('plg_content_maxlevel');
			$modParams->set('show_child_category_articles', ($levels == 0 ? 0 : 1));
			$modParams->set('levels', ($levels < 0 ? 10 : $levels));
			
			switch($type) {
				case 'articles':
					$modParams->set('show_front', 'show');
					break;
				case 'features':
					$modParams->set('show_front', 'only');
					break;
				case 'nofeatures':
					$modParams->set('show_front', 'hide');
					break;
			}
				
			$modParams->set('article_ordering', $params->get('plg_content_order'));
			$modParams->set('article_ordering_direction', $params->get('plg_content_order_dir'));
			$modParams->set('default_image', $params->get('plg_content_image'));
		}
		// end of translating old params to new
		$imageSources = array('fullimage','introimage','fulltext','introtext');
		if(!empty($sourceParams['image_source'])) {
			$imageSources = $sourceParams['image_source'];
		}
		$descSources = array('introtext','fulltext');
		if(!empty($sourceParams['description_source'])) {
			$descSources = $sourceParams['description_source'];
		}
		
		$default_image = $modParams->get('default_image');
		
		$items = ModArticlesCategoryHelper::getList($modParams);
		
		if(!$items) return null;
		
		$slides = array();
		
		foreach($items as $item){
			$slide = (object) array();
			
			$images = new JRegistry($item->images);
			foreach($imageSources as $imageSource) {
				switch($imageSource) {
					case 'fullimage':
						$slide->image = $images->get('image_fulltext');
						$slide->alt = $images->get('image_fulltext_alt');
						break;
					case 'introimage':
						$slide->image = $images->get('image_intro');
						$slide->alt = $images->get('image_intro_alt');
						break;
					case 'fulltext':
						$slide->image = DJMediatoolsLayoutHelper::getImageFromText($item->fulltext);
						break;
					case 'introtext':
						$slide->image = DJMediatoolsLayoutHelper::getImageFromText($item->introtext);
						break;
				}
				
				if(!empty($slide->image)) break;
			}
			// if no image found in fulltext then take default image
			if(!$slide->image) $slide->image = $default_image;
			// if no default image set then don't display this article
			if(!$slide->image) continue;
			
			$slide->title = $item->title;
			$slide->description = '';
			
			if(count($descSources)) {
				foreach($descSources as $field) {
					$description = trim(strip_tags($item->$field));
					if(!empty($description)) {
						$slide->description = $item->$field;
						break;
					}
				}
			} else {
				$slide->description = $item->introtext;
				if(empty($slide->description)) $slide->description = $item->fulltext;
			}
			
			$slide->id = $item->alias ? ($item->id . ':' . $item->alias) : $item->id;
			$slide->canonical = $slide->link = $item->link;
			
			// display extra information
			$slide->extra = '';
			if($modParams->get('show_date') == 1){
				$slide->extra.= '<div class="djmt_date">';
				$slide->extra.= $item->displayDate;
				$slide->extra.= '</div>';
			}
			if($modParams->get('show_category') == 1){
				$slide->extra.= '<div class="djmt_category">';
				$slide->extra.= $item->displayCategoryTitle;
				$slide->extra.= '</div>';
			}
			if($modParams->get('show_hits') == 1){
				$slide->extra.= '<div class="djmt_hits">';
				$slide->extra.= $item->displayHits;
				$slide->extra.= '</div>';
			}
			if($modParams->get('show_author') == 1){
				$slide->extra.= '<div class="djmt_author">';
				$slide->extra.= $item->displayAuthorName;
				$slide->extra.= '</div>';
			}
			
			if($comments = $params->get('commnets',0)) {
				$host = str_replace(JURI::root(true), '', JURI::root());
				$host = preg_replace('/\/$/', '', $host);
				switch($comments) {
					case 1: // jcomments
						$slide->comments = array('id' => $item->id, 'group' => 'com_content');
						break;
					case 2: // disqus
						$disqus_shortname = $params->get('disqus_shortname','');
						if(!empty($disqus_shortname)) {
							$slide->comments = array();
							$slide->comments['url'] =  $host . $slide->link;
							$slide->comments['identifier'] = substr(md5($disqus_shortname), 0, 10)."_id".$item->id;
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
		
		return true;		
	}
	
}
