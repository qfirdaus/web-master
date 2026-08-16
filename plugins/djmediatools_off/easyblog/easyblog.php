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

class plgDJMediatoolsEasyblog extends JPlugin
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
		
		$default_image = $params->get('plg_easyblog_image');
		
		$eb5 = true;
		$engine = JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/easyblog.php';
		
		if (!JFile::exists($engine)) { // Easyblog < 5
			$path = JPATH_ROOT . DIRECTORY_SEPARATOR . 'components' . DIRECTORY_SEPARATOR . 'com_easyblog' . DIRECTORY_SEPARATOR . 'helpers' . DIRECTORY_SEPARATOR . 'helper.php';
			if( !JFile::exists( $path ) )
			{
				return null;
			}
			require_once( $path );
			require_once( EBLOG_HELPERS . DIRECTORY_SEPARATOR . 'router.php' );
			$model = EasyBlogHelper::getModel( 'Blog' );
			$categories	= EasyBlogHelper::getCategoryInclusion( $params->get( 'plg_easyblog_catid' ) );
			$eb5 = false;
			
		} else { // Easyblog 5+
			require_once($engine);
			require_once(JPATH_ADMINISTRATOR . '/components/com_easyblog/includes/modules/modules.php');
			$model = EB::model('Blog');
			$categories	= EB::getCategoryInclusion($params->get('plg_easyblog_catid'));
		}
		
		JFactory::getLanguage()->load( 'com_easyblog' , JPATH_ROOT );
		
		$mparams = new JRegistry();
		
		$count = $params->get('max_images');
		$sort = array();
		$sort[0] = $params->get( 'plg_easyblog_order');
		$sort[1] = $params->get( 'plg_easyblog_order_dir');
		$featured = $params->get( 'plg_easyblog_usefeatured' );
		$type = 'latest';
		
		$catIds     = array();
		
		if( !empty( $categories ) )
		{
			if( !is_array( $categories ) )
			{
				$categories	= array($categories);
			}
		
			foreach($categories as $item)
			{
				$category   = new stdClass();
				$category->id   = trim( $item );
		
				$catIds[]   = $category->id;
		
				if( $params->get( 'plg_easyblog_includesubcategory', 0 ) )
				{
					$category->childs = null;
					EasyBlogHelper::buildNestedCategories($category->id, $category , false , true );
					EasyBlogHelper::accessNestedCategoriesId($category, $catIds);
				}
			}
		
			$catIds     = array_unique( $catIds );
		}
		
		$cid		= $catIds;
		
		if( !empty( $cid ) )
		{
			$type 	= 'category';
		}
		
		$posts = $model->getBlogsBy( $type , $cid , $sort , $count , EBLOG_FILTER_PUBLISHED, null, $featured , array() , false , false , true , array() , $cid);
		
		if (count($posts) > 0 && !$eb5) {
			$posts = EasyBlogHelper::modules()->processItems($posts, $params);
		}
		
		
		$slides = array();
		$base = preg_replace('/^https?:/', '', JURI::base());
		
		foreach($posts as $item){
			
			
			$slide = (object) array();
			
			if($eb5) {
				$post = EB::post($item->id);
				if ($post->hasImage()) {
					$slide->image = str_replace($base, '', $post->getImage('original'));
				}
			} else {
				$row = EasyBlogHelper::getTable( 'Blog', 'Table' );
				$row->bind( $item );
				$image = $row->getImage();
				
				if(!empty($image)) $image = str_replace(JURI::base(), '', is_object($image) ? $image->getSource('original') : $image);
				if(strstr($image, 'components/com_easyblog/themes/wireframe/images/placeholder-image.png')!==FALSE) {
					$image = null;
				}
				if(!empty($image)) $slide->image = $image;
			}
				
			// if no image found in images then try introtext
			if(empty($slide->image)) $slide->image = DJMediatoolsLayoutHelper::getImageFromText($item->intro);
			// if no image found in images and introtext then try fulltext
			if(empty($slide->image)) $slide->image = DJMediatoolsLayoutHelper::getImageFromText($item->content);
			// if no image found in fulltext then take default image
			if(empty($slide->image)) $slide->image = $default_image;
			// if no default image set then don't display this article
			if(empty($slide->image)) continue;

			$slide->title = $item->title;
			$slide->description = $item->intro;
			if(empty($slide->description)) $slide->description = $item->content;
			
			
			if(class_exists('EBR')) { // EB 5.2+
				$slide->canonical = $slide->link = EBR::_('index.php?option=com_easyblog&view=entry&id='. $item->id );
			} else {
				$slide->canonical = $slide->link = EasyBlogRouter::_('index.php?option=com_easyblog&view=entry&id='. $item->id );
			}
			
			$slide->id = $item->id.':'.$item->permalink;
			//$this->dd($item);
			if($comments = $params->get('commnets',0)) {
				$host = str_replace(JURI::root(true), '', JURI::root());
				$host = preg_replace('/\/$/', '', $host);
				switch($comments) {
					case 1: // jcomments
						$slide->comments = array('id' => $item->id, 'group' => 'com_easyblog');
						break;
					case 2: // disqus
						$disqus_shortname = $params->get('disqus_shortname','');
						if(!empty($disqus_shortname)) {
							$slide->comments = array();
							$slide->comments['url'] =  $host . $slide->link;
							$slide->comments['identifier'] = $disqus_shortname.'-easyblog-'.$item->id; // ??
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
		
		if(!JFile::exists(JPATH_ROOT.'/components/com_easyblog/easyblog.php')) return JText::_('PLG_DJMEDIATOOLS_EASYBLOG_COMPONENT_DISABLED');
		jimport('joomla.application.component.helper');
		$com = JComponentHelper::getComponent('com_easyblog', true);
		if(!$com->enabled) return JText::_('PLG_DJMEDIATOOLS_EASYBLOG_COMPONENT_DISABLED');
		
		return true;		
	}
	
	private function dd($data, $type = 'message') {
	
		$app = JFactory::getApplication();
		$app->enqueueMessage("<pre>".print_r($data, true)."</pre>", $type);
	
	}
}
