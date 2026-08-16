<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

$columns = $this->params->get('columns', '4');

?>

<div id="splms" class="splms view-splms-courses splms-course-categories">

	<?php if (count($this->items)) { ?>
		<!-- Column -->
		<?php foreach (array_chunk($this->items, $columns) as $this->items) { ?>
			<div class="splms-row splms-course-category-wrapper">

				<?php foreach ($this->items as $item) { ?>
					<div class="splms-col-xs-12 splms-col-sm-<?php echo round(12 / $columns); ?> splms-course-category">
						<div class="splms-coursescategory-wrapper">
							<div class="splms-cat-title">
								<a href="<?php echo $item->url; ?>">
									<?php if ($item->show == 1 && $item->image) { ?>
										<img src="<?php echo JURI::root() . $item->image; ?>">
									<?php } else { ?>
										<i class="fa fa-<?php echo $item->icon; ?>"></i>
									<?php } ?>
									<span class="splms-cat-count">
										<?php echo $item->title; ?>
										<small><?php echo '(' . $item->courses . ')'; ?></small>
									</span>
								</a>
							</div>
						</div>
					</div>
				<?php } // END:: foreach 
				?>
			</div> <!-- /.splms-row -->
		<?php } // END:: array_chunk 
		?>
	<?php } //END:: Count items 
	?>
</div>