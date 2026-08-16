<?php

/**
* @package       JExtBOX Google Analytics Tracker
* @author        Galaa
* @publisher     JExtBOX - BOX of Joomla Extensions (www.jextbox.com)
* @authorUrl     www.galaa.net
* @copyright     Copyright (C) 2021-2023 Galaa
* @license       This extension in released under the GNU/GPL License - http://www.gnu.org/copyleft/gpl.html
*/

// No direct access
defined('_JEXEC') or die;

class plgSystemJExtBOXGoogleAnalyticsTracker extends Joomla\CMS\Plugin\CMSPlugin
{

	function onBeforeCompileHead ()
	{

		if
		(
			($this->params->get('skip_administrator', '1') AND Joomla\CMS\Factory::getApplication()->isClient('administrator'))
			OR
			($this->params->get('skip_localhost', '1') AND in_array($_SERVER['REMOTE_ADDR'], array('127.0.0.1', '::1')))
			OR
			empty($ga_measurement_id = $this->params->get('ga_measurement_id', ''))
		)
			return;
		$document = Joomla\CMS\Factory::getDocument();
		$document->addScript("https://www.googletagmanager.com/gtag/js?id=$ga_measurement_id", array(), array('async' => 'async'));
		$document->addScriptDeclaration("
window.dataLayer = window.dataLayer || [];
function gtag(){dataLayer.push(arguments);}
gtag('js', new Date());
gtag('config', '$ga_measurement_id');
");

	}

}

?>
