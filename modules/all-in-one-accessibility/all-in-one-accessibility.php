<?php
/**
 * @package     All in One Accessibility
 * @author      Skynet Technologies USA LLC.
 * @copyright   (C) 2024 - Skynet Technologies USA LLC.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 **/


// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;

// Include your JavaScript file
HTMLHelper::_('script', 'all-in-one-accessibility/js/script.js', ['version' => 'auto', 'relative' => true]);

$document = JFactory::getDocument();
require JModuleHelper::getLayoutPath('all-in-one-accessibility', $params->get('layout', 'output'));
