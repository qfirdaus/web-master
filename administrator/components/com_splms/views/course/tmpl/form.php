<?php
/**
 * @package     SP Simple Portfolio
 *
 * @copyright   Copyright (C) 2010 - 2020 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die();

use Joomla\CMS\HTML\HTMLHelper;

if(SplmsHelper::getJoomlaVersion() < 4)
{
    HTMLHelper::_('formbehavior.chosen', 'select');
}

echo $this->getRenderedForm();