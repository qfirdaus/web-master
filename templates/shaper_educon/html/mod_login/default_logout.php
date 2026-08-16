<?php
/**
* @package		Joomla.Site
* @subpackage	mod_login
* @copyright	Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License version 2 or later; see LICENSE.txt
*/

defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');
?>

<div class="sp-login sp-mod-login">
	<div class="sp-my-menu">
		<ul class="sp-my-account">
			<li>
				<?php echo JFactory::getDocument()->getBuffer('modules', 'usermenu', array('style' => 'none')); ?>
			</li>
		</ul>
	</div><!-- /.sp-my-account-menu -->	
</div> <!-- /.sp-moviedb-login -->



