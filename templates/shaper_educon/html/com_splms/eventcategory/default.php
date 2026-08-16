<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

?>

<div class="event-category">
	<div id="splms" class="splms splms-view-events splms-event-category-items">
		<?php if(count($this->category_items)) { ?>
			<?php foreach ($this->category_items as $category_item) { ?>
				<div class="splms-event">
					<div class="splms-row">
						<div class="splms-col-sm-3">
							<div class="event-date-wrape">
								<a href="<?php echo $category_item->url; ?>">
									<div class="event-date" style="background-image: url('<?php echo $category_item->thumb; ?>');">
										<h1>
											<?php echo JHtml::date($category_item->event_start_date , 'd'); ?>
											<span><?php echo JHtml::date($category_item->event_start_date , 'M, Y'); ?></span>
										</h1>
									</div>
								</a>
							</div>
						</div>

						<div class="splms-col-sm-9">
							<div class="splms-event-details">
								<h3 class="splms-event-title">
									<a href="<?php echo $category_item->url; ?>">
										<?php echo $category_item->title; ?>
									</a>
								</h3>

								<div class="splms-event-short-description">
									<p>
										<?php echo JHtml::_('string.truncate', strip_tags($category_item->description), $this->params->get('intro_limit', 300)); ?>
									</p>
								</div>

								<ul class="splms-event-info-list">
									<?php if ($category_item->event_start_date && $category_item->event_time) { ?>
										<li>
											<?php if ($category_item->event_time) {
												echo date('g:i a', strtotime($category_item->event_start_date)) . ' - ' . date('g:i a', strtotime($category_item->event_time));
											} else {
												echo date('g:i a', strtotime($category_item->event_start_date));
											} ?>
										</li>
									<?php } ?>
									<li>
										<strong>
											<?php echo JText::_('COM_SPLMS_EVENT_LOCATION') . ': '; ?>
										</strong>
										<?php echo $category_item->event_address; ?>
									</li>
								</ul>

								<?php if ($this->params->get('show_readmore')) { ?>
									<a class="btn btn-primary" href="<?php echo $category_item->url; ?>"><?php echo $this->params->get('readmore_text', JText::_('COM_SPLMS_DETAILS')); ?>
									</a>
								<?php } ?>

							</div>
						</div>
					</div><!--/row-->
				</div>
			<?php }?>
		<?php } ?>
	</div>
</div>


