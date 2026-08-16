<?php

/**
 * @copyright	Copyright © 2018 - All rights reserved.
 * @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @author	https://templateplazza.com
 */

defined('JPATH_BASE') or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Uri\Uri;

jimport('joomla.form.formfield');

/* adding additional javascript and css loads to the  template backend */

class JFormFieldFlybtn extends FormField {
protected $type = 'flybtn';
protected function getInput() {
    $doc = Factory::getDocument();
	$doc->addStyleSheet(Uri::root() .'/modules/mod_floating_buttons/admin/assets/css/admin.css');
    return null;
	}
}

?>