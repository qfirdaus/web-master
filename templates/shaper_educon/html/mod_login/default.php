<?php
/**
* @package		Joomla.Site
* @subpackage	mod_login
* @copyright	Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
* @license		GNU General Public License version 2 or later; see LICENSE.txt
*/

// no direct access
defined('_JEXEC') or die;
JHtml::_('behavior.keepalive');

$user = JFactory::getUser();

?>
<div class="sp-educon-login sp-mod-login">
	<a href="<?php echo JRoute::_('index.php?option=com_users&view=login'); ?>" class="login">
		<i class="fa fa-sign-in"></i>
		<?php echo JText::_('EDUCON_LOGIN'); ?>
	</a>
	<a href="<?php echo JRoute::_('index.php?option=com_users&view=registration'); ?>" class="registration">
		<i class="fa fa-user-o"></i>
		<?php echo JText::_('EDUCON_SIGNUP'); ?>
	</a>
</div>