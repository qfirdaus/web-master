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

class plgDJMediatoolsSobipro extends JPlugin
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
		require_once( JPATH_ROOT . '/components/com_sobipro/lib/sobi.php' );
		Sobi::Initialise( $params->get( 'section' ) );
		
		require_once( dirname( __FILE__ ) . '/lib/emod.php' );
		$emod = new SPEntriesDJMTCtrl();
		
		$imageField = $params->get('plg_sobipro_image_field', 'field_company_logo');
		$descField = $params->get('plg_sobipro_desc_field', 'field_short_description');
		
		$default_image = $params->get('plg_sobipro_image');
		
		// create parameters for K2 content module helper
		$mparams = new JRegistry();
		$mparams->def('entriesLimit', $params->get('max_images'));
		$mparams->def('section', $params->get('section'));
		$mparams->def('cid', $params->get('cid'));
		$mparams->def('sid', $params->get('sid'));
		$mparams->def('autoListing', $params->get('plg_sobipro_autoListing'));
		$mparams->def('spOrder', $params->get('spOrder'));
		$mparams->def('spOrderDir', $params->get('spOrderDir'));
		$mparams->def('spLimit', $params->get('spLimit'));
		$mparams->def('engine', 'static');
		
		$items = $emod->getSourceEntries($mparams);
		$slides = array();
		
		//$this->debug($items);
		
		foreach($items as $item){
			$slide = (object) array();

			$slide->title = $item['name']['_data'];
			$slide->description = @$item['fields'][$descField]['_data']['data']['_data'];
			
			$slide->image = @$item['fields'][$imageField]['_data']['data']['_attributes']['image'];
			if(!$slide->image) $slide->image = DJMediatoolsLayoutHelper::getImageFromText($slide->description);
			if(!$slide->image) $slide->image = $default_image;
			// if no default image set then don't display this entry
			if(!$slide->image) continue;

			$slide->id = $item['id'];
			$slide->canonical = $slide->link = $item['url'];
			
			if($comments = $params->get('commnets',0)) {
				$host = str_replace(JURI::root(true), '', JURI::root());
				$host = preg_replace('/\/$/', '', $host);
				switch($comments) {
					case 1: // jcomments
						$slide->comments = array('id' => $item->id, 'group' => 'com_sobipro');
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
		
		if(!JFile::exists(JPATH_ROOT.'/components/com_sobipro/sobipro.php')) return JText::_('PLG_DJMEDIATOOLS_SOBIPRO_COMPONENT_DISABLED');
		jimport('joomla.application.component.helper');
		$com = JComponentHelper::getComponent('com_sobipro', true);
		if(!$com->enabled) return JText::_('PLG_DJMEDIATOOLS_SOBIPRO_COMPONENT_DISABLED');
		
		return true;		
	}

	function debug($data, $type = 'message') {
		
		$app = JFactory::getApplication();		
		$app->enqueueMessage("<pre>".print_r($data, true)."</pre>", $type);
		
	}
}
