<?php
/**
 * @package     SP LMS
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

$columns = $this->params->get('columns', '2');
?>

<div id="splms" class="splms splms-view-event-categories">

    <?php if (count($this->items)) { ?>
        <!-- Column -->
        <?php foreach (array_chunk($this->items, $columns) as $this->items) { ?>
            <div class="splms-row">
                <?php foreach ($this->items as $this->item) { ?>

                    <?php
                    $this->item->url = JRoute::_('index.php?option=com_splms&view=eventcategory&id=' . $this->item->splms_eventcategory_id . ':' . $this->item->slug . SplmsHelper::getItemid('eventcategory'));
                    ?>

                    <div class="splms-col-lg-<?php echo round(12 / $columns); ?> splms-col-sm-6">
                        <div class="splms-event-category">
                            <img class="splms-event-category-img splms-img-responsive" src="<?php echo $this->item->image; ?>" alt="<?php echo $this->item->title; ?>">
                            <div class="splms-event-category-info">
                                <h2><?php echo $this->item->title; ?></h2>
                                <p><?php echo JHtml::_('string.truncate', strip_tags($this->item->description), $this->params->get('intro_limit', 150)); ?></p>
                                <div class="more-details">
                                    <a href="<?php echo $this->item->url; ?>" class="sppb-btn  sppb-btn-primary"><?php echo JText::_('COM_SPLMS_DETAILS'); ?> <i class="fa fa-angle-right"></i></a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php } ?>
            </div>
        <?php }
    } // END:: array_chunk
    ?>

    <?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
		<nav class="pagination-wrapper">
			<?php echo $this->pagination->getPagesLinks(); ?>
			<?php if ($this->params->def('show_pagination_results', 1)) : ?>
			<?php endif; ?>
		</nav>
	<?php endif; ?>

</div>