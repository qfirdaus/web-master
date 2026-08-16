<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

$columns = 3;

?>

<div id="splms" class="splms splms-view-category-courses">
	<?php if(count($this->category_items)) { ?>

		<div class="splms-courses-list">

			<?php foreach(array_chunk($this->category_items, $columns) as $this->category_items) { ?>
			<div class="splms-row splms-popular-course-wrapper">

			<?php foreach ($this->category_items as $category_item) { ?>

				<div class="splms-col-sm-<?php echo round(12/$columns); ?>">
					<div class="splms-course splms-match-height">
						<div class="splms-common-overlay-wrapper">

							<?php
								$filename = basename($category_item->image);
								$path = JPATH_BASE .'/'. dirname($category_item->image) . '/thumbs/' . JFile::stripExt($filename) . '_' . $this->thumb_size . '.' . JFile::getExt($filename);
								$src = JURI::base(true) . '/' . dirname($category_item->image) . '/thumbs/' . JFile::stripExt($filename) . '_' . $this->thumb_size . '.' . JFile::getExt($filename);
								
								if(JFile::exists($path)) {
									$thumb = $src;
								} else {
									$thumb = $category_item->image;	
								}
							?>

				    		<img src="<?php echo $thumb; ?>" class="splms-course-img splms-img-responsive" alt="<?php echo $this->item->title; ?>">

				    		<?php if ($category_item->price == 0) {
		            			echo '<span class="splms-badge-free">' . JText::_('COM_SPLMS_FREE') . '</span>';
		            		} ?>
						</div>
						<div class="splms-course-info">
							<h3 class="splms-courses-title">
								<a href="<?php echo $category_item->url; ?>">
									<?php echo $category_item->title; ?>
								</a>
							</h3>

							<!-- Has teacher -->
							<?php if (!empty($teachers)) { ?>
							<div class="splms-course-teachers">
								<span><?php echo JText::_('COM_SPLMS_BY'); ?></span>
									
								<?php foreach ($teachers as $teacher) {
									// Get Last Item
									$last_item = end($teachers);
								?>
								<a href="<?php echo $teacher->url; ?>" class="splms-teacher-name">
									<strong>
										<?php echo $teacher->title; ?>
									</strong>
									<?php echo ($teacher == $last_item) ? '' : JText::_(', '); ?>
								</a>
								<?php } // END:: foreach ?>
							</div>
							<?php } // END:: has teahcer ?>

							<p class="splms-course-short-info"><?php echo $category_item->short_description; ?></p>
							<div class="splms-course-meta">
								<?php echo $category_item->c_price; ?>
								<?php echo JText::_(' . '); ?>
								<?php echo count($category_item->lessons); ?>
								<?php echo JText::_('COM_SPLMS_COMMON_LESSONS'); ?>
								<?php echo JText::_(' . '); ?>
								<?php echo count($category_item->total_attachments); ?>
								<?php echo JText::_('COM_SPLMS_COMMON_ATTACHMENTS'); ?>
								<div class="course-details-btn">
									<a href="<?php echo $category_item->url; ?>" class="splms-readmore btn btn-primary">
			    						<?php echo JText::_('COM_SPLMS_DETAILS');?> <i class="fa fa-angle-right"></i>
				    				</a>
				    			</div>
							</div>
						</div>
					</div> <!-- /.splms-course -->
				</div> <!-- /.splms-col-sm -->

			<?php } //END:: foreach ?>
			</div> <!-- /.splms-row -->
			<?php } //END:: array_chunk ?>

		</div> <!-- /.splms-courses-list -->

	<?php } ?>
</div> <!-- /.splms -->