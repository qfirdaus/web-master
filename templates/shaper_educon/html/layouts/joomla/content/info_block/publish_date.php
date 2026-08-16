<?php
/**
 * @package Helix3 Framework
 * @author JoomShaper https://www.joomshaper.com
 * @Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
*/

defined('JPATH_BASE') or die;
?>
<div class="published-date">
	<time datetime="<?php echo JHtml::_('date', $displayData['item']->publish_up, 'c'); ?>" itemprop="datePublished" >
        <span><?php echo JHtml::_('date', $displayData['item']->publish_up, 'd'); ?></span>
        <?php echo JHtml::_('date', $displayData['item']->publish_up, 'M'); ?>,<?php echo JHtml::_('date', $displayData['item']->publish_up, 'Y'); ?>
    </time>
</div>