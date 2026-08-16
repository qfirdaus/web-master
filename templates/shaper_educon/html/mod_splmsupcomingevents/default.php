<?php
/**
 * @package     SP LMS
 * @subpackage  mod_splmupcomingevents
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;

// import component helper
jimport('joomla.application.component.helper');
jimport('joomla.filesystem.file');

// Get Thumb
$params = JComponentHelper::getParams('com_splms');
$thumb_size_small = strtolower($params->get('event_thumbnail', '100X60'));


//Load extended model
$app = JFactory::getApplication();
$extended_helper = JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/com_splms/helper/common.php';

if (file_exists($extended_helper)) {
    require_once($extended_helper);
} else {
    die('Please install and activate lms component');
}
$commonHelper = new SplmsHelperCommon();
?>

<div class="splms splms-module splms-view-events">
    <?php
    foreach ($items as $event_item) {
        //Get Eveent Sepaker
        $speaker_infos = $commonHelper->getEventSpeakers($event_item->speaker_id);
        $last_item = end($speaker_infos);

        $filename = basename($event_item->image);
        $path = JPATH_BASE . '/' . dirname($event_item->image) . '/thumbs/' . JFile::stripExt($filename) . '_' . $thumb_size_small . '.' . JFile::getExt($filename);
        $src = JURI::base(true) . '/' . dirname($event_item->image) . '/thumbs/' . JFile::stripExt($filename) . '_' . $thumb_size_small . '.' . JFile::getExt($filename);
        if (JFile::exists($path)) {
            $thumb = $src;
        } else {
            $thumb = $event_item->image;
        }
        ?>
        <div class="splms-event">
            <div class="event-date-wrape">
                <a href="<?php echo $event_item->url; ?>">
                    <div class="event-date" style="background-image: url('<?php echo $thumb; ?>');">
                        <h1>
                            <?php echo JHtml::date($event_item->event_start_date, 'd'); ?>
                            <span><?php echo JHtml::date($event_item->event_start_date, 'M, Y'); ?></span>
                        </h1>
                    </div>
                </a>
            </div> <!-- //event-date-wrape -->
            <div class="splms-event-details">
                <ul class="splms-event-info-list">
                    <?php if ($event_item->event_time && $event_item->event_end_time) { ?>
                        <li>
                            <?php
                            if ($event_item->event_end_time) {
                                echo date('g:i a', strtotime($event_item->event_time)) . ' - ' . date('g:i a', strtotime($event_item->event_end_time));
                            } else {
                                echo date('g:i a', strtotime($event_item->event_time));
                            }
                            ?>
                        </li>
                    <?php } ?>
                </ul>

                <h3 class="splms-event-title">
                    <a href="<?php echo $event_item->url; ?>">
                        <?php echo $event_item->title; ?>
                    </a>
                </h3>
            </div> <!-- //splms-event-details -->
        </div> <!-- //.splms-event -->
<?php } ?>

</div>
