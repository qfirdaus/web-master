<?php

/**
 * @package     Joomla.Site
 * @subpackage  com_users
 *
 * @copyright   Copyright (C) 2005 - 2017 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die;

HTMLHelper::_('behavior.keepalive');

//print_r(get_class_methods($this->form));
//die;

$doc = Factory::getDocument();
$app = Factory::getApplication();

$tmp_params = Factory::getApplication()->getTemplate('true')->params;

//Login Logo
if ($logo_image = $tmp_params->get('login_logo')) {
    $logo = Uri::root() . '/' . $logo_image;
    $path = JPATH_ROOT . '/' . $logo_image;
}
?>
<div class="row justify-content-center">
    <div class="col-sm-6 col-sm-offset-3 text-center">
        <div class="reg-login-form-wrap">
            <div class="login-logo">
                <?php if ($login_logo = $this->params->get('login_logo')) { ?>
                <a href="<?php echo $this->baseurl; ?>/"><img class="login-logo" alt="logo"
                        src="<?php echo $logo; ?>" /></a>
                <?php } else {
                ?>
                <a href="<?php echo $this->baseurl; ?>/"><img class="login-logo" alt="logo"
                        src="<?php echo $logo; ?>" /></a>
                <?php }
                ?>
            </div>

            <div class="reg-login-title">
                <h3><?php echo Text::_('COM_USERS_LOGIN_TITLE'); ?></h3>
            </div>

            <div class="login <?php echo $this->pageclass_sfx ?>">
                <?php if ($this->params->get('show_page_heading')) : ?>
                <h1>
                    <?php echo $this->escape($this->params->get('page_heading')); ?>
                </h1>
                <?php endif; ?>
                <?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
                <div class="login-description">
                    <?php endif; ?>
                    <?php if ($this->params->get('logindescription_show') == 1) : ?>
                    <?php echo $this->params->get('login_description'); ?>
                    <?php endif; ?>
                    <?php if (($this->params->get('login_image') != '')) : ?>
                    <img src="<?php echo $this->escape($this->params->get('login_image')); ?>" class="login-image"
                        alt="<?php echo TEXT::_('COM_USERS_LOGIN_IMAGE_ALT') ?>" />
                    <?php endif; ?>
                    <?php if (($this->params->get('logindescription_show') == 1 && str_replace(' ', '', $this->params->get('login_description')) != '') || $this->params->get('login_image') != '') : ?>
                </div>
                <?php endif; ?>
                <form action="<?php echo Route::_('index.php?option=com_users&task=user.login'); ?>" method="post"
                    class="form-validate">
                    <?php
                    /* Set placeholder for username, password and secretekey */
                    $this->form->setFieldAttribute('username', 'hint', Text::_('COM_USERS_LOGIN_USERNAME_LABEL'));
                    $this->form->setFieldAttribute('password', 'hint', Text::_('JGLOBAL_PASSWORD'));
                    $this->form->setFieldAttribute('secretkey', 'hint', Text::_('JGLOBAL_SECRETKEY'));
                    ?>
                    <?php foreach ($this->form->getFieldset('credentials') as $field) : ?>
                    <?php if (!$field->hidden) : ?>
                    <div class="form-group">
                        <p><?php echo $field->label; ?></p>
                        <div class="group-control">
                            <?php echo $field->input; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php endforeach; ?>
                    <?php if ($this->tfa) : ?>
                    <div class="form-group">
                        <div class="group-control">
                            <?php echo $this->form->getField('secretkey')->input; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (PluginHelper::isEnabled('system', 'remember')) : ?>
                    <div class="checkbox">
                        <label>
                            <input id="remember" type="checkbox" name="remember" class="inputbox" value="yes">
                            <?php
                                echo Text::_('COM_USERS_LOGIN_REMEMBER_ME')
                                ?>
                        </label>
                    </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary btn-block">
                            <?php echo Text::_('JLOGIN'); ?>
                            <i class="fa fa-angle-right"></i>
                        </button>
                    </div>
                    <?php $return = $this->form->getValue('return', '', $this->params->get('login_redirect_url', $this->params->get('login_redirect_menuitem'))); ?>
                    <input type="hidden" name="return" value="<?php echo base64_encode($return ?? ''); ?>" />
                    <?php echo HTMLHelper::_('form.token'); ?>
                </form>
            </div>
            <div class="form-links">
                <ul>
                    <li>
                        <span><?php echo Text::_('COM_USERS_FORGOT_PASSWORD'); ?></span>
                        <a href="<?php echo Route::_('index.php?option=com_users&view=reset'); ?>">
                            <?php echo Text::_('COM_USERS_LOGIN_RESET'); ?></a>
                    </li>
                    <li>
                        <a href="<?php //echo JRoute::_('index.php?option=com_users&view=remind');   
                                ?>">
                            <?php //echo JText::_('COM_USERS_LOGIN_REMIND');  
                    ?></a>
                    </li>
                    <?php
                    $usersConfig = ComponentHelper::getParams('com_users');
                    if ($usersConfig->get('allowUserRegistration')) :
                    ?>
                    <li>
                        <span><?php echo Text::_('COM_USERS_LOGIN_DONT_HAVE_AN_ACCOUNT'); ?></span>
                        <a href="<?php echo Route::_('index.php?option=com_users&view=registration'); ?>">
                            <?php echo Text::_('COM_USERS_LOGIN_REGISTER'); ?></a>
                    </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </div>
</div>