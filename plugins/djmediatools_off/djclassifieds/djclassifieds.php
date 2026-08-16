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

class plgDJMediatoolsDJClassifieds extends JPlugin
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
		$default_image = $params->get('plg_classifieds_image');
		
		require_once(JPATH_BASE.'/modules/mod_djclassifieds_items/helper.php');
		require_once(JPATH_BASE.'/administrator/components/com_djclassifieds/lib/djtheme.php');
		require_once(JPATH_BASE.'/administrator/components/com_djclassifieds/lib/djseo.php');
		require_once(JPATH_BASE.'/administrator/components/com_djclassifieds/lib/djimage.php');

		$mparams = new JRegistry;
		foreach($params->toArray() as $key => $value) {
			if(strpos($key, 'plg_classifieds_')!==FALSE) {
				$nkey = substr($key, 16);
				switch($nkey) {
					case 'catid': 
						$mparams->set('cat_id', $value); break;
					case 'follow_category':
						$mparams->set('fallow_category', $value); break;
					case 'follow_region':
						$mparams->set('fallow_region', $value); break;
					case 'only_promoted':
						foreach($value as $promo) $mparams->set('only_'.$promo, 1); break; 
					default:
						$mparams->set($nkey, $value); break;
				}
			}
		}
		
		// override number of items
		$mparams->set('items_nr', $params->get('max_images'));
		// we need ads only with images
		if(empty($default_image)) $mparams->set('only_with_img', 1);
		
		//$this->debug($mparams);
		$items = modDjClassifiedsItems::getItems($mparams);
		$types = modDjClassifiedsItems::getTypes();
		
		$slides = array();
		
		if($items) foreach($items as $i){
			
			$slide = (object) array();
			
			if(isset($i->images) && count($i->images)){
				// DJ-Classifieds 3.4+
				$slide->image = $i->images[0]->path.$i->images[0]->name.'.'.$i->images[0]->ext;
				if(!JFile::exists(JPATH_ROOT . $slide->image)) {
					$slide->image = $i->images[0]->thumb_b;
				}
			} else if(!empty($i->img_path) && !empty($i->img_name) && !empty($i->img_ext)) {
				// DJ-Classifieds 3.2+
				$slide->image = $i->img_path.$i->img_name.'.'.$i->img_ext;
				if(!JFile::exists(JPATH_ROOT . $slide->image)) {
					$slide->image = $i->img_path.$i->img_name.'_thb.'.$i->img_ext;
				}
			} else if(!empty($i->image_url)) {
				// DJ-Classifieds version < 3.2
				$images = explode(';',$i->image_url);
				$slide->image = 'components/com_djclassifieds/images/'.$images[0];				
			} else if(!empty($default_image)) {
				$slide->image = $default_image;
			} else {
				continue;
			}
			
			$slide->image = preg_replace('/^\//', '', $slide->image);
			//$this->debug($slide->image);
			
			// we got image now take extra information
			$slide->extra = '';
			if($mparams->get('show_date')==1){
				$slide->extra.= '<div class="date">';
				if(method_exists('DJClassifiedsTheme', 'formatDate')) $slide->extra.= DJClassifiedsTheme::formatDate(strtotime($i->date_start));
				else $slide->extra.= DJClassifiedsTheme::dateFormatFromTo(strtotime($i->date_start));
				$slide->extra.= '</div>';
			}
			if($mparams->get('show_cat')==1){
				$slide->extra.= '<div class="category">';
				if($mparams->get('cat_link')==1){						
					$slide->extra.= '<a class="title_cat" href="'.JRoute::_(DJClassifiedsSEO::getCategoryRoute($i->cat_id.':'.$i->c_alias)).'">'.$i->c_name.'</a>';
				}else{
					$slide->extra.= $i->c_name;
				}
				$slide->extra.= '</div>';
			}
			if($mparams->get('show_type') && $i->type_id>0){
				if(isset($types[$i->type_id])){
					$slide->extra.= '<div class="type">';
					$type = $types[$i->type_id];
					if($type->params->bt_class){
						$bt_class = ' '.$type->params->bt_class;
					}else{
						$bt_class = '';
					}
					if($type->params->bt_use_styles){
						if($mparams->get('show_type')==2){
							$style='style="display:inline-block;'
								.'border:'.(int)$type->params->bt_border_size.'px solid '.$type->params->bt_border_color.';'
								.'background:'.$type->params->bt_bg.';'
								.'color:'.$type->params->bt_color.';'
								.$type->params->bt_style.'"';
							$slide->extra.= '<div class="type_button'.$bt_class.'" '.$style.' >'.$type->name.'</div>';
						}else{
							$slide->extra.= '<div class="type_label'.$bt_class.'" >'.$type->name.'</div>';
						}
					}else{
						$slide->extra.= '<div class="type_label'.$bt_class.'" >'.$type->name.'</div>';
					}
					$slide->extra.= '</div>';
				}
			}
			if($mparams->get('show_region')==1){
				$slide->extra.= '<div class="region">';
				$slide->extra.= $i->r_name;
				$slide->extra.= '</div>';
			}
			if($mparams->get('show_price')==1 && $i->price){
				$slide->extra.= '<div class="price">';
				$slide->extra.= DJClassifiedsTheme::priceFormat($i->price,$i->currency);
				$slide->extra.= '</div>';
			}
			
			// finish getting extra information
			
			$slide->title = $i->name;
			$slide->description = $i->intro_desc;
			if(empty($slide->description)) $slide->description = $i->description;
			$slide->full_desc = $i->description;

			$cparams = JComponentHelper::getParams('com_djclassifieds');
			$sufix = (($cparams->get('showitem_jump')) ? '#dj-classifieds' : '');


			if(method_exists('DJClassifiedsSEO', 'getRegionRoute')) {
				$slide->canonical = $slide->link = JRoute::_(DJClassifiedsSEO::getItemRoute($i->id.':'.$i->alias,$i->cat_id.':'.$i->c_alias,$i->region_id.':'.$i->r_name)) . $sufix;
			} else {
				$slide->canonical = $slide->link = JRoute::_(DJClassifiedsSEO::getItemRoute($i->id.':'.$i->alias,$i->cat_id.':'.$i->c_alias)) . $sufix;
			}
			$slide->id = $i->id.':'.$i->alias;
			
			if($comments = $params->get('commnets',0)) {
				$host = str_replace(JURI::root(true), '', JURI::root());
				$host = preg_replace('/\/$/', '', $host);
				switch($comments) {
					case 1: // jcomments
						$slide->comments = array('id' => $i->id, 'group' => 'com_djclassifieds');
						break;
					case 2: // disqus
						$disqus_shortname = $params->get('disqus_shortname','');
						if(!empty($disqus_shortname)) {
							$slide->comments = array();
							$slide->comments['url'] =  $host . $slide->link;
							$slide->comments['identifier'] = $disqus_shortname.'-djcf-'.$i->id;
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
		
		if(!JFile::exists(JPATH_ROOT.'/components/com_djclassifieds/djclassifieds.php')) return JText::_('PLG_DJMEDIATOOLS_DJCLASSIFIEDS_COMPONENT_DISABLED');
		jimport('joomla.application.component.helper');
		$com = JComponentHelper::getComponent('com_djclassifieds', true);
		if(!$com->enabled) return JText::_('PLG_DJMEDIATOOLS_DJCLASSIFIEDS_COMPONENT_DISABLED');
		
		if(!JFile::exists(JPATH_ROOT.'/modules/mod_djclassifieds_items/helper.php')) return JText::_('PLG_DJMEDIATOOLS_DJCLASSIFIEDS_ITEMS_MODULE_NOT_INSTALLED');
		
		// load module language
		$lang = JFactory::getLanguage();
		$path = JPATH_ROOT . '/modules/mod_djclassifieds_items';
		$lang->load('mod_djclassifieds_items', JPATH_ROOT, 'en-GB', false, false);
		$lang->load('mod_djclassifieds_items', $path, 'en-GB', false, false);
		$lang->load('mod_djclassifieds_items', JPATH_ROOT, null, true, false);
		$lang->load('mod_djclassifieds_items', $path, null, true, false);
		
		return true;		
	}
	
	function debug($data, $type = 'message') {
	
		$app = JFactory::getApplication();
		$app->enqueueMessage("<pre>".print_r($data, true)."</pre>", $type);
	
	}
}
