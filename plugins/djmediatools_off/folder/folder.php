<?php
/**
 * @version 1.0
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

class plgDJMediatoolsFolder extends JPlugin
{
	/**
	 * Plugin that returns the object list for DJ-Mediatools album
	 * 
	 * Each object must contain following properties (mandatory): title, description, image
	 * Optional properties: link, target (_blank or _self), alt (alt attribute for image)
	 * 
	 * @param	string	The source of the album being passed to the plugin.
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
		
		$max = $params->get('max_images');
        $folder = $params->get('plg_folder_path');
        if(!$dir = @opendir(JPath::clean(JPATH_ROOT.'/'.$folder))) return null;
        while (false !== ($file = readdir($dir)))
        {
            if (preg_match('/.+\.(jpg|jpeg|gif|png)$/i', strtolower($file))) {
            	// check with getimagesize() which attempts to return the image mime-type 
            	if(getimagesize(JPath::clean(JPATH_ROOT.'/'.$folder.'/'.$file)) !== FALSE) $files[] = $file;
			}
        }
        closedir($dir);
        if($params->get('sort_by')) natcasesort($files);
		else shuffle($files);

		$images = array_slice($files, 0, $max);
		
		foreach($images as $image) {
			$title = preg_replace('/\.(jpg|jpeg|gif|png)$/i', '', $image);
			$slides[] = (object) array('title'=>$title, 'description'=>'', 'image'=>$folder.'/'.$image, 'link'=>$params->get('plg_folder_link'));
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
