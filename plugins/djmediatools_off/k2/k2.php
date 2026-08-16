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

class plgDJMediatoolsK2 extends JPlugin
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
		
		require_once(JPATH_ROOT.'/modules/mod_k2_content/helper.php');
		
		// fix K2 models path inclusion, we need to add path with prefix to avoid conflicts with other extensions
		JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_k2/models', 'K2Model');

		// create parameters for K2 content module helper
		$mparams = new JRegistry();
		$mparams->loadArray($params->get('source_params', array()));
		
		$mparams->set('itemCount', $params->get('max_images'));
		
		// translate old params to new for backward compatibility 
		$mparams->def('source', $params->get('plg_k2_source'));
		$mparams->def('catfilter', $params->get('plg_k2_catfilter'));
		$mparams->def('category_id', $params->get('plg_k2_category_id', array()));
		$mparams->def('getChildren', $params->get('plg_k2_getChildren'));
		$mparams->def('itemsOrdering', $params->get('plg_k2_itemsOrdering'));
		$mparams->def('FeaturedItems', $params->get('plg_k2_FeaturedItems'));
		$mparams->def('popularityRange', $params->get('plg_k2_popularityRange'));
		$mparams->def('videosOnly', $params->get('plg_k2_videosOnly'));
		$mparams->def('items', $params->get('plg_k2_items', array()));
		$mparams->def('itemImage', 1);
		$mparams->def('itemIntroText', 1);
		
		$default_image = $mparams->get('default_image', $params->get('plg_k2_image'));
		
		$items = modK2ContentHelper::getItems($mparams);
		$slides = array();
		
		foreach($items as $item){
			$slide = (object) array();
			
			if(isset($item->imageXLarge)) $slide->image = str_replace(JURI::base(true), '', $item->imageXLarge);
			else $slide->image = DJMediatoolsLayoutHelper::getImageFromText($item->introtext);
			// if no image found in article images and introtext then try fulltext
			if(!$slide->image) $slide->image = DJMediatoolsLayoutHelper::getImageFromText($item->fulltext);
			// if no image found in fulltext then take default image
			if(!$slide->image) $slide->image = $default_image;
			// if no default image set then don't display this article
			if(!$slide->image) continue;
			
			$slide->image = preg_replace('/^\//', '', $slide->image);
			
			$slide->title = $item->title;
			$slide->description = $item->introtext;
			if(empty($slide->description)) $slide->description = $item->fulltext;
			/* // getting youtube video
			if(preg_match('/{YouTube}([\w\d_-]+){\/YouTube}/i', $item->video, $video)) {
				$slide->video = '//www.youtube.com/embed/'.$video[1];
				//$app->enqueueMessage("<pre>".print_r($slide->video, true)."</pre>");
			}
			*/
			$slide->id = $item->id.':'.$item->alias;
			$slide->canonical = $slide->link = $item->link;
			
			if($comments = $params->get('commnets',0)) {
				$host = str_replace(JURI::root(true), '', JURI::root());
				$host = preg_replace('/\/$/', '', $host);
				switch($comments) {
					case 1: // jcomments
						$slide->comments = array('id' => $item->id, 'group' => 'com_k2');
						break;
					case 2: // disqus
						$disqus_shortname = $params->get('disqus_shortname','');
						if(!empty($disqus_shortname)) {
							$slide->comments = array();
							$slide->comments['url'] =  $host . $item->link;
							$slide->comments['identifier'] = $item->id;
						}
						break;
					case 3: // facebook
						$slide->comments = $host . $item->link;
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
		
		if(!JFile::exists(JPATH_ROOT.'/components/com_k2/k2.php')) return JText::_('PLG_DJMEDIATOOLS_K2_COMPONENT_DISABLED');
		jimport('joomla.application.component.helper');
		$com = JComponentHelper::getComponent('com_k2', true);
		if(!$com->enabled) return JText::_('PLG_DJMEDIATOOLS_K2_COMPONENT_DISABLED');
		
		if(!JFile::exists(JPATH_ROOT.'/modules/mod_k2_content/helper.php')) return JText::_('PLG_DJMEDIATOOLS_K2_CONTENT_MODULE_NOT_INSTALLED');
		
		$language = JFactory::getLanguage();
		$language->load('mod_k2_content', JPATH_SITE, null, true);
		
		return true;		
	}
	
}
