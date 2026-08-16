<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

$columns = $this->params->get('columns', '3');

?>

<div id="splms" class="splms view-splms-quiz-list">

	<?php foreach (array_chunk($this->items, $columns) as $this->items) { ?>
		<div class="splms-row">
			<?php foreach ($this->items as $item) { ?>
				<div class="splms-col-sm-6 splms-col-md-4 splms-col-lg-<?php echo round(12 / $columns); ?>">
					<div class="quiz-item-wrapper">
						<div class="quiz-banner">
							<img class="img-responsive" src="<?php echo $item->image; ?>" alt="<?php echo $item->title; ?>">
						</div>
						<div class="quiz-description">
							<h3 class="quiz-title"><a href="<?php echo $item->url; ?>"><?php echo $item->title; ?></a></h3>
							<?php echo $item->description; ?>
							<h4 class="quiz-course-name"><?php echo $item->cat_name; ?></h4>
							<h4 class="quiz-duration">Duration: <?php echo $item->duration; ?> Sec </h4>
							<a href="<?php echo $item->url; ?>" class="btn btn-primary"><?php echo JText::_('COM_SPMS_START_THE_QUIZ'); ?></a>
						</div>
					</div>
				</div>
			<?php } ?>
		</div> <!-- /.splms-row -->
	<?php } // END:: array chunk 
	?>

</div> <!-- /.splms -->

<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
	<nav class="pagination-wrapper">
		<?php echo $this->pagination->getPagesLinks(); ?>
		<?php if ($this->params->def('show_pagination_results', 1)) : ?>
		<?php endif; ?>
	</nav>
<?php endif; ?>