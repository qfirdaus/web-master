<?php

/**
 * @package     SP LMS
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\Language\Text;

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

$columns = $this->params->get('columns', '3');

?>

<div class="splms-row">
    <div id="splms" class="splms view-splms-techers splms-techers-list splms-persons">
        <?php foreach ($this->items as $this->item) { ?>
        <div class="splms-person splms-col-md-<?php echo round(12/$columns); ?> splms-col-sm-6">
            <div class="splms-person-details">

                <img src="<?php echo $this->item->image; ?>" class="splms-person-img splms-img-responsive"
                    alt="<?php echo $this->item->title; ?>">

                <div class="splms-person-info-wrap">
                    <div class="splms-person-info">
                        <a class="splms-person-title" href="<?php echo $this->item->url; ?>">
                            <?php echo $this->item->title; ?>
                        </a>
                    </div>

                    <div class="splms-person-content">
                        <?php if (!empty($this->item->website)) { ?>
                        <p>
                            <span><?php echo Text::_('COM_SPLMS_COMMON_WEBSITE') . ': '; ?></span>
                            <a href="<?php echo $this->item->website; ?>" target="_blank">
                                <?php echo $this->item->website; ?>
                            </a>
                        </p>
                        <?php } ?>

                        <?php if (!empty($this->item->specialist_in)) { ?>
                        <p>
                            <span><?php echo Text::_('COM_SPLMS_COMMON_EXPERIENCE') . ': '; ?></span>
                            <?php echo $this->item->experience; ?>
                        </p>
                        <?php } ?>

                        <?php if (!empty($this->item->specialist_in)) { ?>
                        <p>
                            <span><?php echo Text::_('COM_SPLMS_COMMON_SPECIALIST_IN') . ': '; ?> </span>
                            <?php echo $this->item->specialist_in; ?>
                        </p>
                        <?php } ?>

                        <p>
                            <span><?php echo Text::_('COM_SPLMS_COMMON_TOTAL') . ': '; ?> </span>
                            <?php echo $this->item->teacher_total_lessons; ?>
                            <?php echo Text::_('COM_SPLMS_COMMON_LESSONS'); ?>
                        </p>

                        <?php if ( (!empty($this->item->social_facebook)) ||
							(!empty($this->item->social_linkedin)) ||
							(!empty($this->item->social_twitter)) ||
							(!empty($this->item->social_gplus))) { ?>

                        <ul class="splms-persion-social-icons">
                            <?php if (!empty($this->item->social_facebook)) { ?>
                            <li class="facebook">
                                <a href="http://facebook.com/<?php echo $this->item->social_facebook; ?>"
                                    target="_blank">
                                    <i class="splms-icon-facebook"></i>
                                </a>
                            </li>
                            <?php } if (!empty($this->item->social_linkedin)) {?>
                            <li class="linkedin">
                                <a href="http://linkedin.com/<?php echo $this->item->social_linkedin; ?>"
                                    target="_blank">
                                    <i class="splms-icon-linkedin"></i>
                                </a>
                            </li>
                            <?php } if (!empty($this->item->social_twitter)) {?>
                            <li class="twitter">
                                <a href="http://twitter.com/<?php echo $this->item->social_twitter; ?>" target="_blank">
                                    <i class="splms-icon-twitter"></i>
                                </a>
                            </li>
                            <?php } if (!empty($this->item->social_gplus)) {?>
                            <li class="gplus">
                                <a href="https://plus.google.com/<?php echo $this->item->social_gplus; ?>"
                                    target="_blank">
                                    <i class="splms-icon-google-plus"></i>
                                </a>
                            </li>
                            <?php } ?>
                        </ul>
                        <?php } ?>
                    </div>
                </div> <!-- /.splms-person-info-wrap -->
            </div>
            <!--/.item-content-->
        </div> <!-- /.splms-person -->
        <?php } ?>
    </div> <!-- /#splms -->
</div> <!-- /.splms-row -->

<?php if (($this->params->def('show_pagination', 1) == 1 || ($this->params->get('show_pagination') == 2)) && ($this->pagination->pagesTotal > 1)) : ?>
<nav class="pagination-wrapper">
    <?php echo $this->pagination->getPagesLinks(); ?>
    <?php if ($this->params->def('show_pagination_results', 1)) : ?>
    <?php endif; ?>
</nav>
<?php endif; ?>