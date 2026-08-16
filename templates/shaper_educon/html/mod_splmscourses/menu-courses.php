<?php
/**
 * @package     SP LMS
 * @subpackage  mod_splmscourses
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;
jimport('joomla.filesystem.file');

//Joomla Component Helper & Get LMS Params
jimport('joomla.application.component.helper');
$splmsparams = JComponentHelper::getParams('com_splms');

//Get Currency
$currency = explode(':', $splmsparams->get('currency', 'USD:$'));
$currency = $currency[1];

// Get image thumb
$thumb_size = strtolower($splmsparams->get('course_thumbnail', '370X210'));


$lesson_model = JPATH_BASE . '/components/com_splms/models/lessons.php';
$helper = JPATH_BASE . '/components/com_splms/helpers/helper.php';

if(!file_exists($lesson_model) || !file_exists($helper)) {
  return;
} else {
  require_once $lesson_model;
  require_once $helper;
}


//Get Currency
$currency = explode(':', $params->get('currency', 'USD:$'));
$currency =  $currency[1];


//Load extended model
$app    				= JFactory::getApplication();
$extended_helper = JPATH_ROOT .'/templates/'.$app->getTemplate().'/html/com_splms/helper/common.php';

if (file_exists($extended_helper)) {
	require_once($extended_helper);
} else {
	die('Please install and activate lms component');
}
$commonHelper = new SplmsHelperCommon();

?>

<div class="splms view-splms-courses mod-splms-courses <?php echo $moduleclass_sfx; ?> mod-splms-courses-menu">
    <div class="splms-courses-list">
      <?php foreach(array_chunk($items, 4) as $items) {
        $total_items = count($items);
        ?>
        <div class="row">
          <?php foreach ($items as $course_item) {
            $course_category = $commonHelper->getCategoryById($course_item->coursecategory_id);
            $lessons  = SplmsModelLessons::getLessons( $course_item->id );
            //get attachments
            $total_attachments = array();
						foreach ($lessons as $lesson){
							if ($lesson->attachment) {
								$total_attachments[] = $lesson->attachment;
							}
						}

            // Get Prices
						if ($course_item->price == 0) {
							$price = JText::_('SPLMS_COURSE_FREE');
						}else{
							$price = SplmsHelper::generateCurrency($course_item->price);
						}

            $filename = basename($course_item->image);
            $path = JPATH_BASE .'/'. dirname($course_item->image) . '/thumbs/' . JFile::stripExt($filename) . '_' . $thumb_size . '.' . JFile::getExt($filename);
            $src = JURI::base(true) . '/' . dirname($course_item->image) . '/thumbs/' . JFile::stripExt($filename) . '_' . $thumb_size . '.' . JFile::getExt($filename);

            if(JFile::exists($path)) {
              $thumb = $src;
            } else {
              $thumb = $course_item->image;
            }
            ?>
            <div class="col-sm-<?php echo 12/round($total_items); ?>">
                <div class="splms-course splms-match-height">
                    <div class="splms-common-overlay-wrapper">
                        <img src="<?php echo $thumb; ?>" class="splms-course-img splms-img-responsive" alt="<?php echo $course_item->title; ?>">
                    </div>

                    <div class="splms-course-info">
                        <a href="<?php echo $course_category->url; ?>" class="course-category">
                          <?php echo $course_category->title; ?>
                        </a>

                        <h3 class="splms-courses-title">
                            <a href="<?php echo $course_item->url; ?>">
                              <?php echo $course_item->title; ?>
                            </a>
                        </h3>

                        <!-- Has teacher -->
                        <p class="splms-course-short-info">
                          <?php echo $course_item->short_description; ?>
                        </p>
                    </div>
                    <!-- /.splms-course-info -->
                </div>
                <!-- /.splms-course -->
            </div>
            <!-- /.col-sm -->
            <?php } ?>

        </div>
        <!-- /.splms-row -->
        <?php } ?>
    </div>
    <!-- /.splms-courses-list -->
</div>
