<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

//LMS version 4.0.2
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;


//Load extended model
$app           = Factory::getApplication();
$extended_helper = JPATH_ROOT . '/templates/' . $app->getTemplate() . '/html/com_splms/helper/common.php';

if (file_exists($extended_helper)) {
   require_once($extended_helper);
} else {
   die('Please install and activate lms component');
}
$commonHelper = new SplmsHelperCommon();
$speakers = $commonHelper->getEventSpeakers($this->item->splms_speaker_id);

// $getUri = JFactory::getURI();
// $currentURL = $getUri->toString();

//opengraph
// $document = JFactory::getDocument();
// $document->setTitle($this->item->title);
// $document->addCustomTag('<meta property="og:url" content="'.$currentURL.'" />');
// $document->addCustomTag('<meta property="og:type" content="article" />');
// $document->setDescription( JHtml::_('string.truncate', $this->item->description, 155, false, false ) );
// $document->addCustomTag('<meta property="og:title" content="'. $this->item->title .'" />');
// $document->addCustomTag('<meta property="og:description" content="'. JHtml::_('string.truncate', $this->item->description, 155, false, false ) .'" />');
// if ($this->item->image) {
//    $document->addCustomTag('<meta property="og:image" content="'. JURI::root() . $this->item->image .'" />');
//    $document->addCustomTag('<meta property="og:image:width" content="600" />');
//    $document->addCustomTag('<meta property="og:image:height" content="315" />');
// }

// Load assets
$doc = Factory::getDocument();
$doc->addStylesheet(Uri::root(true) . '/components/com_splms/assets/css/lightboxgallery-min.css');
$doc->addScript(Uri::root(true) . '/components/com_splms/assets/js/lightboxgallery-min.js');

$params = ComponentHelper::getParams('com_splms');
$mapbox_api = $params->get('mapbox_api', '');

$disable_gmap = $params->get('disable_gmap', 0);
$select_map = $params->get('select_map', 1);

// Load Leaflet
if (!$disable_gmap && $select_map == 1) {
   $doc->addStyleSheet('//unpkg.com/leaflet@1.7.1/dist/leaflet.css', [], ["integrity" => "sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==", "crossorigin" => ""]);
   $doc->addScript('//unpkg.com/leaflet@1.7.1/dist/leaflet.js', [], ["integrity" => "sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==", "crossorigin" => ""]);
   $doc->addScriptOptions('lat', trim($this->map[0]));
   $doc->addScriptOptions('long', trim($this->map[1]));
   $doc->addScriptOptions('mapbox_api', trim($mapbox_api));
   if (!empty($this->item->event_address)) {
      $doc->addScriptOptions('address', $this->item->event_address);
   }
   $doc->addScript(Uri::root(true) . '/components/com_splms/assets/js/omap.js', [], ['defer' => 'defer']);
}
//LMS version 4.0.2

?>
<div id="splms" class="splms splms-view-event">
    <div class="splms-event-image">
        <div class="event-date">
            <?php echo HTMLHelper::date($this->item->event_start_date, 'd'); ?>
            <span><?php echo HTMLHelper::date($this->item->event_start_date, 'M, Y'); ?></span>
        </div>
        <img src="<?php echo $this->item->image ?>" class="splms-event-img splms-img-responsive"
            alt="<?php echo $this->item->title; ?>">
    </div>
    <div class="event-details-wrape">
        <h2 class="splms-event-title">
            <?php echo $this->item->title; ?>
        </h2>
        <ul class="splms-event-detail-list">
            <?php if ($this->item->event_time && $this->item->event_end_time) { ?>
            <li>
                <?php echo date('g:i a', strtotime($this->item->event_time)) ?> -
                <?php echo date('g:i a', strtotime($this->item->event_end_time)); ?>
            </li>
            <?php } // has event start time and end time 
         ?>
            <?php if ($this->item->event_address) { ?>
            <li>
                <?php echo $this->item->event_address; ?>
            </li>
            <?php } // has address 
         ?>
            <?php if (isset($this->item->speaker_infos) && !empty($this->item->speaker_infos)) { ?>
            <li>
                <strong><?php echo Text::_('COM_SPLMS_EVENT_SPEAKERS') . ': '; ?></strong>
                <?php
               $last_item = end($this->item->speaker_infos);
               foreach ($this->item->speaker_infos as $speaker_info) {
               ?>
                <a href="<?php echo $speaker_info->url ?>">
                    <?php echo $speaker_info->title; ?>
                </a>
                <?php echo ($speaker_info == $last_item) ? '' : ', ';
               } ?>
            </li>
            <?php } // has speaker 
         ?>
            <?php if ($this->item->price) { ?>
            <li>
                <strong><?php echo Text::_('COM_SPLMS_EVENT_PRICE') . ': '; ?></strong>
                <?php echo $this->item->price; ?>
            </li>
            <?php } ?>
        </ul>
        <div class="splms-event-description">
            <p><strong><?php echo TEXT::_('COM_SPLMS_EDUCON_ABOUT_EVENT'); ?></strong></p>
            <?php echo $this->item->description; ?>
        </div>
        <!-- /.splms-event-description -->



        <!-- event gallery -->
        <?php if (isset($this->item->gallery) && is_array($this->item->gallery) && count($this->item->gallery)) { ?>
        <div class="splms-event-gallery">
            <h3 class="splms-event-title"><?php echo Text::_('COM_SPLMS_EVENT_PHOTO_GALLERY'); ?></h3>
            <div class="splms-event-gallery-list">
                <?php foreach ($this->item->gallery as $key => $gallery_item) { ?>
                <a class="lightboxgallery-gallery-item" href="<?php echo Uri::root() . $gallery_item['image']; ?>">
                    <img src="<?php echo Uri::root() . $gallery_item['image']; ?>" alt="image">
                    <h4 class="title"><?php echo $gallery_item['text']; ?></h4>
                </a>
                <?php } ?>
            </div> <!-- //.gallery-list -->
        </div> <!-- //.splms-event-gallery -->
        <?php } ?>


        <!-- event topics -->
        <?php if (is_array($this->topics) && count($this->topics)) { ?>
        <div class="splms-event-topics">
            <h3 class="splms-event-title"><?php echo Text::_('COM_SPLMS_EVENT_TOPICS'); ?></h3>
            <ul class="splms-event-topics-list">
                <?php foreach ($this->topics as $key => $topics) { ?>
                <li>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <td><?php echo $key; ?></td>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($topics as $topic) { ?>
                                <tr>
                                    <?php if ($topic['title']) { ?>
                                    <td><?php echo $topic['title']; ?></td>
                                    <?php }
                                    if ($topic['time']) { ?>
                                    <td><i class="fa fa-clock-o"></i><?php echo $topic['time']; ?></td>
                                    <?php }
                                    if ($topic['speaker_infos']) { ?>
                                    <td><span class="with"><?php echo Text::_('COM_SPLMS_COMMON_WITH'); ?></span> : <a
                                            href="<?php echo $topic['speaker_infos']->url; ?>"><?php echo $topic['speaker_infos']->title; ?></a>
                                    </td>
                                    <?php } ?>
                                </tr> <!-- //each row -->
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                    <?php } ?>
            </ul> <!-- //.splms-event-topics-list -->
        </div> <!-- //.splms-event-topics -->
        <?php } ?>

        <div class="event-details-speaker">
            <div class="container-inner sppb-lms-speakers">
                <div class="container">
                    <div class="section-title text-left">
                        <h4 class="title-heading"><?php echo Text::_('COM_SPLMS_EVENT_SPEAKER'); ?></h4>
                    </div>
                </div>
                <?php if (!empty($speakers)) { ?>
                <?php foreach ($speakers as $speaker_info) { ?>
                <div class="row sppb-lms-speaker">
                    <div class="col-md-6">
                        <div class="event-speaker">
                            <div class="pull-left">
                                <a href="<?php echo $speaker_info->url ?>">
                                    <span class="img-container">
                                        <img class="img-responsive"
                                            src="<?php echo Uri::Root() .  $speaker_info->image; ?>"
                                            alt="<?php echo $speaker_info->title; ?>">
                                    </span>
                                </a>
                            </div>
                            <div class="event-speaker-info-wrap">
                                <div class="event-speaker-info">
                                    <h4 class="speaker-designation"><?php echo $speaker_info->designation; ?></h4>
                                    <a href="<?php echo $speaker_info->url ?>">
                                        <h2 class="speaker-title"><?php echo $speaker_info->title; ?></h2>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <?php if ((isset($speaker_info->social_facebook)) ||
                           (isset($speaker_info->social_linkedin)) ||
                           (isset($speaker_info->social_twitter)) ||
                           (isset($speaker_info->social_gplus))
                        ) { ?>
                        <div class="speaker-social-icon">
                            <div class="addon-title-wrapper">
                                <h3 class="addon-title"><?php echo Text::_('COM_SPLMS_EVENT_SPEAKER_SOCIAL_LINKS'); ?>
                                </h3>
                            </div>
                            <div class="social-icons">
                                <?php if (isset($speaker_info->social_facebook) && $speaker_info->social_facebook) { ?>
                                <span><a href="<?php echo $speaker_info->social_facebook;  ?>"><i
                                            class="fa fa-facebook "></i></a></span>
                                <?php }
                                 if (isset($speaker_info->social_twitter) && $speaker_info->social_twitter) { ?>
                                <span><a href="<?php echo $speaker_info->social_twitter;  ?>"><i
                                            class="fa fa-twitter "></i></a></span>
                                <?php }
       
                                 if (isset($speaker_info->social_linkedin) && $speaker_info->social_linkedin) { ?>
                                <span><a href="<?php echo $speaker_info->social_linkedin;  ?>"><i
                                            class="fa fa-linkedin "></i></a></span>
                                <?php }
                                 if (isset($speaker_info->website) && $speaker_info->website) { ?>
                                <span><a href="<?php echo $speaker_info->website;  ?>"><i
                                            class="fa fa-globe"></i></a></span>
                                <?php } ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
                <?php } ?>
            </div>
        </div>


        <!-- event tickets -->
        <?php if (is_array($this->item->pricing_tables) && count($this->item->pricing_tables)) { ?>
        <div id="splms-event-tickets" class="splms-event-tickets">
            <h3 class="splms-event-title"><?php echo Text::_('COM_SPLMS_EVENT_TICKETS'); ?></h3>
            <div class="splms-row splms-event-tickets-list">
                <?php foreach ($this->item->pricing_tables as $pricing_table) { ?>
                <div class="splms-col-sm-4">
                    <div class="event-pricing-table splms-text-center">
                        <div class="splms-pricing-box">
                            <div class="splms-pricing-header">
                                <span class="splms-pricing-price"><?php echo $pricing_table['price'] ?></span>
                                <div class="splms-pricing-title">
                                    <?php echo $pricing_table['title'] ?>
                                </div>
                            </div>
                            <div class="splms-pricing-features"><?php echo $pricing_table['description'] ?></div>
                            <div class="splms-pricing-footer">
                                <?php if ($pricing_table['purchase_url']) { ?>
                                <a class="splms-btn splms-btn-info splms-btn-rounded"
                                    href="<?php echo $this->item->buy_url; ?>">Proceed <i
                                        class="fa fa-chevron-right"></i></a>
                                <?php } ?>
                            </div>
                        </div>
                    </div> <!-- //.event-pricing-table -->
                </div> <!-- //.splms-col-sm-4 -->
                <?php } ?>
            </div> <!-- //.splms-row splms-event-tickets-list -->
        </div> <!-- //.splms-event-tickets -->
        <?php } ?>





        <?php if (!$this->params->get('disable_gmap', 0)) { ?>
        <?php if ($this->params->get('select_map', 0) == 0 && $this->item->event_address) { ?>
        <div class="splms-event-location-map">
            <h3 class="splms-event-location-title"><?php echo Text::_('COM_SPLMS_EVENT_LOCATION'); ?></h3>
            <div id="splms-event-map" class="splms-gmap-canvas" data-lat="<?php echo $this->map[0]; ?>"
                data-lng="<?php echo $this->map[1]; ?>" data-address="<?php echo $this->item->event_address; ?>"
                style="height:300px"></div>
        </div>
        <?php } ?>

        <?php if ($this->params->get('select_map', 1) == 1) { ?>
        <div class="splms-event-location-map">
            <h3 class="splms-event-location-title"><?php echo Text::_('COM_SPLMS_EVENT_LOCATION'); ?></h3>
            <div id="open-map" style="height:300px"></div>
        </div>
        <?php } ?>
        <?php } ?>


        <div class="event-details-bottom">
            <div class="container-inner">
                <div class="row">
                    <div class="splms-event-purchase-btn splms-col-sm-4">
                        <?php if ($this->item->buy_url) { ?>
                        <a class="btn btn-primary"
                            href="<?php echo $this->item->buy_url; ?>"><?php echo Text::_('COM_SPLMS_EVENT_BUY_TICKET'); ?></a>
                        <?php } ?>
                    </div>
                    <div class="column-addons splms-col-sm-8">
                        <div class="addon addon-social-share social-share-style-simple">
                            <div class="social-share">
                                <h4 class="addon-title"><?php echo Text::_('COM_SPLMS_SHARE_THIS_EVENT'); ?></h4>
                                <div class="social-share-wrap row">
                                    <div class="social-items-wrap col-sm-12">
                                        <ul>
                                            <li class="social-share-facebook"><a
                                                    onclick="window.open('http://www.facebook.com/sharer.php?u=<?php echo $currentURL; ?>','Facebook','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;"
                                                    href="http://www.facebook.com/sharer.php?u=<?php echo $currentURL; ?>"><i
                                                        class="fa fa-facebook"></i></a></li>
                                            <li class="social-share-twitter"><a
                                                    onclick="window.open('http://twitter.com/share?url=<?php echo $currentURL; ?>&amp;text=Event%20Details','Twitter share','width=600,height=300,left='+(screen.availWidth/2-300)+',top='+(screen.availHeight/2-150)+''); return false;"
                                                    href="http://twitter.com/share?url=<?php echo $currentURL; ?>&amp;text=Event%20Details"><i
                                                        class="fa fa-twitter"></i></a></li>

                                            <li class="social-share-linkedin"><a
                                                    onclick="window.open('http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo $currentURL; ?>','Linkedin','width=585,height=666,left='+(screen.availWidth/2-292)+',top='+(screen.availHeight/2-333)+''); return false;"
                                                    href="http://www.linkedin.com/shareArticle?mini=true&amp;url=<?php echo $currentURL; ?>"><i
                                                        class="fa fa-linkedin-square"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<!-- /#splms -->