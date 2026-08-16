<?php
/**
 * @package     SP LMS
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

// Load Lessons model
$model = $this->getModel();
$tpl_params = JFactory::getApplication()->getTemplate(true)->params;

?>

<div id="splms" class="splms splms-view-events">
    <?php if (count($this->items)) {?>


    <?php foreach ($this->items as $item) {
    //Get Eveent Sepaker

    $speaker_infos = $model->getEventSpeakers($item->splms_speaker_id);
    $last_item = is_array($speaker_infos) ? end($speaker_infos) : '';?>
    <div class="splms-event">
        <div class="splms-row">
            <div class="splms-col-sm-3">
                <div class="event-date-wrape">
                    <a href="<?php echo $item->url; ?>">
                        <div class="event-date" style="background-image: url('<?php echo $item->thumb; ?>');">
                            <h1>
                                <?php echo JHtml::date($item->event_start_date, 'd'); ?>
                                <span><?php echo JHtml::date($item->event_start_date, 'M, Y'); ?></span>
                            </h1>
                        </div>
                    </a>
                </div>
            </div>

            <div class="splms-col-sm-9">
                <div class="splms-event-details">
                    <h3 class="splms-event-title">
                        <a href="<?php echo $item->url; ?>">
                            <?php echo $item->title; ?>
                        </a>
                    </h3>

                    <?php if ($tpl_params->get('show_events_desc', 0)) {?>
                    <div class="splms-event-short-description">
                        <p><?php echo JHtml::_('string.truncate', $item->description, $this->params->get('intro_limit', 300)); ?>
                        </p>
                    </div>
                    <?php }?>

                    <ul class="splms-event-info-list">
                        <?php if ($item->event_time && $item->event_end_time) {?>
                        <li>
                            <?php if ($item->event_end_time) {
        echo date('g:i a', strtotime($item->event_time)) . ' - ' . date('g:i a', strtotime($item->event_end_time));
    } else {
        echo date('g:i a', strtotime($item->event_time));
    }?>
                        </li>
                        <?php }?>

                        <li>
                            <?php echo $item->event_address; ?>
                        </li>
                        <?php if(is_array($speaker_infos)):?>
                        <li>
                            <span><?php echo JText::_('COM_SPLMS_EVENT_SPEAKERS') . ': '; ?></span>
                            <?php foreach ($speaker_infos as $speaker_info) {?>
                            <a href="<?php echo $speaker_info->url ?>">
                                <?php echo $speaker_info->title; ?>
                            </a>
                            <?php echo ($speaker_info == $last_item) ? '' : ', ';
    } ?>
                        </li>
                        <?php endif;?>
                    </ul>

                    <?php if ($this->params->get('show_readmore')) {?>
                    <a class="btn btn-primary"
                        href="<?php echo $item->url; ?>"><?php echo $this->params->get('readmore_text', JText::_('COM_SPLMS_DETAILS')); ?></a>
                    <?php }?>
                </div>
            </div>
        </div>
        <!--/row-->
    </div>

    <?php }?>


    <?php if ($this->params->get('hide_pagination') == 0) { ?>
    <?php if ($this->pagination->pagesTotal > 1) { ?>
    <div class="pagination">
        <?php echo $this->pagination->getPagesLinks(); ?>
    </div>
    <?php } ?>
    <?php } ?>

    <?php }?>
</div>