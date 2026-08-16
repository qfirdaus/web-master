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

class plgContentDJMediatools extends JPlugin
{
	protected static $galleries = array();
	protected static $covers = array();
	protected static $_modal = false;
	/**
	 * Plugin that loads DJ-Mediatools gallery within content
	 *
	 * @param	string	The context of the content being passed to the plugin.
	 * @param	object	The article object.  Note $article->text is also available
	 * @param	object	The article params
	 * @param	int		The 'page' number
	 */
	public function __construct(& $subject, $config)
	{
		parent::__construct($subject, $config);
		
		$lang = JFactory::getLanguage();
		$lang->load('com_djmediatools', JPATH_SITE, 'en-GB', false, false);
		$lang->load('com_djmediatools', JPATH_SITE . '/components/com_djmediatools', 'en-GB', false, false);
		$lang->load('com_djmediatools', JPATH_SITE, null, true, false);
		$lang->load('com_djmediatools', JPATH_SITE . '/components/com_djmediatools', null, true, false);
		
		jimport( 'joomla.application.module.helper' );
		// Include the syndicate functions only once
		require_once(JPATH_ROOT.'/components/com_djmediatools/helpers/helper.php');
		JModelLegacy::addIncludePath(JPATH_ROOT.'/components/com_djmediatools/models', 'DJMediatoolsModel');
		JModelLegacy::addTablePath(JPATH_ADMINISTRATOR.'/components/com_djmediatools/tables');
	}
	
	public function onContentPrepare($context, &$article, &$params, $page = 0)
	{
		// Don't run this plugin for backend
		$app = JFactory::getApplication();
		if($app->isClient('administrator')) return true;
		
		// Don't run this plugin when the content is being indexed
		if ($context == 'com_finder.indexer') {
			return true;
		}

		// simple performance check to determine whether bot should process further
		if (strpos($article->text, 'djmedia') === false && strpos($article->text, 'djalbum_link') === false) {
			return true;
		}

		// expression to search for album insertion
		$regex 		= '/{djmedia\s*(.*?)}/i';
		$regex2		= '/<img [^>]*alt="djmedia:\s*([^"]+)"[^>]*>/i';
		
		// replace the image placeholder with plugin code
		$article->text = preg_replace($regex2, '{djmedia $1}', $article->text);
		
		// Find all instances of plugin and put in $matches for djmedia code
		// $matches[0] is full pattern match, $matches[1] is the album ID
		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);
		// No matches, skip this
		if ($matches) {
			
			foreach ($matches as $match) {
				
				$matcheslist = array_map('trim', explode(',', $match[1]));
				
				$album = (int)$matcheslist[0];
				$preset = isset($matcheslist[1]) ? $matcheslist[1] : $this->params->get('preset', '');
				
				$output = '';
				// add album code
				if (!empty($album)) {
					$output = $this->_load($album, $preset);
				}
				
				// We should replace only first occurrence in order to allow the same category to regenerate their content:
				$article->text = preg_replace("|$match[0]|", addcslashes($output, '\\$'), $article->text, 1);
			}
		}
		
		// linked album cover
		$regex	= '/<img [^>]*alt="djalbum_link:(\d*)"[^>]*>/i';
		
		// Find all instances of plugin and put in $matches for djmedia code
		// $matches[0] is full pattern match, $matches[1] is the album ID
		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);
		
		// No matches, skip this
		if ($matches) {
			foreach ($matches as $match) {
				$output = '';
				//$this->debug($match);
				// Chceck if album ID is set.
				if (isset($match[1]) && (int)$match[1] > 0) {
					$output = $this->_getLinkedCover($match);
				}
				// We should replace only first occurrence in order to allow the same category to regenerate their content:
				$article->text = preg_replace("|$match[0]|", addcslashes($output, '\\$'), $article->text, 1);
			}
		}
	}

	protected function _load($album, $preset)
	{
		$galleryKey = md5($album.'+'.$preset);
		
		if (!isset(self::$galleries[$galleryKey])) {
			
			self::$galleries[$galleryKey] = '';
			
			$presetParams = new JRegistry();
			$presetParams->set('preset', $preset);
			
			$model = JModelLegacy::getInstance('Categories', 'DJMediatoolsModel', array('ignore_request'=>true));
			$model->setState('category.id', $album);
			$model->setState('filter.published', 1);
			$params = $model->getParams(false, $presetParams);
			$category = $model->getItem();
			
			if ($category === false) { // album is not available
				return self::$galleries[$galleryKey];
			}
			
			// get gallery slides and layout
			$helper = DJMediatoolsLayoutHelper::getInstance($params->get('layout', 'slideshow'));
			$mid = $category->id.'p';
			$params->set('gallery_id',$mid);
			$params->set('category',$category->id.':'.$category->alias);
			$params->set('source',$category->source);
			$params = $helper->getParams($params);
			$slides = $helper->getSlides($params);
			if($slides) {
				$helper->addScripts($params);
				$helper->addStyles($params);
				$navigation = $helper->getNavigation($params);
			} else { // album is empty
				return self::$galleries[$galleryKey];
			}
						
			ob_start();

			require JModuleHelper::getLayoutPath('mod_djmediatools', $params->get('layout', 'slideshow'));

			self::$galleries[$galleryKey] = ob_get_clean();
		}
		
		return self::$galleries[$galleryKey];
	}
	
	protected function _getLinkedCover($match)
	{
		$id = $match[1];
		preg_match('/src="([^"]+)"/i', $match[0], $tmp);
		$src = isset($tmp[1]) ? $tmp[1] : '';
		preg_match('/style="([^"]+)"/i', $match[0], $tmp);
		$style = isset($tmp[1]) ? $tmp[1] : '';
		preg_match('/title="([^"]+)"/i', $match[0], $tmp);
		$title = isset($tmp[1]) ? $tmp[1] : '';
		preg_match('/width="([^"]+)"/i', $match[0], $tmp);
		$width = isset($tmp[1]) ? $tmp[1] : '600';
		
		if (!isset(self::$covers[$id])) {
			
			$app = JFactory::getApplication();
			$document= JFactory::getDocument();
			
			$model = JModelLegacy::getInstance('Categories', 'DJMediatoolsModel', array('ignore_request'=>true));
			$model->setState('category.id', $id);
			$model->setState('filter.published', 1);
			$params = $model->getParams(false);
			$category = $model->getItem();
				
			if ($category === false) {
				return ''; // no category
			}
			
			// get layout helper
			$helper = DJMediatoolsLayoutHelper::getInstance($params->get('layout', 'slideshow'));
			$mid = $category->id.'p';
			$params->set('gallery_id',$mid);
			$params->set('category',$category->id.':'.$category->alias);
			$params->set('source',$category->source);				
			$params = $helper->getParams($params);
			$slides = $helper->getSlides($params);
			
			if(!isset($slides[0])) {
				return ''; // no items
			}
			
			$helper->addLightbox('magnific', true);
			
			$document->addStyleSheet(JURI::root(true).'/components/com_djmediatools/assets/css/default.css');
			
			$juri_root = JURI::root(true);
			$src = !empty($juri_root) ? str_replace($juri_root.'/', '', $src) : $src;
			if(!$image = DJImageResizer::createThumbnail($src, 'media/djmediatools/cache', $width, 0, 'toWidth', 75)) {
				$image = $src;
			}
			if(strcasecmp(substr($image, 0, 4), 'http') != 0 && !empty($image)) {
				$image = JURI::root(true).'/'.$image;
			}
			
			$link = JRoute::_(DJMediatoolsHelperRoute::getItemRoute($slides[0]->id, $params->get('category')));
			
			self::$covers[$id] = '<img class="djalbum_img" src="'.$image.'" alt="'.$title.'" width="'.$width.'" />';
			self::$covers[$id] = '<a class="dj-slide-popup" href="'.$link.'">'.self::$covers[$id].'<span class="djalbum_link_tip">'.JText::_('COM_DJMEDIATOOLS_SHOW_GALLERY').'</span>'.'</a>';
			self::$covers[$id] = '<span class="djalbum_link" style="'.$style.' width:'.$width.'px;">'.self::$covers[$id].'</span>';

		}
		
		return self::$covers[$id];
	}
	
	private function debug($msg, $type = 'message') {
		
		$app = JFactory::getApplication();
		$app->enqueueMessage("<pre>".print_r($msg, true)."</pre>", $type);
		
	}
}
