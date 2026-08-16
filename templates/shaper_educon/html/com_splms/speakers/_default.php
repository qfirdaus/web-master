<?php
/**
 * @package     SP LMS
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

// No Direct Access
defined ('_JEXEC') or die('Resticted Aceess');

// Get Columns
$columns = $this->params->get('columns', '4');

?>

<div class="splms-row">
	<div id="splms" class="splms view-splms-speakers splms-speakers-list splms-persons">
		<?php if(count($this->items)) { ?>

		<!-- Column -->
		<?php foreach ($this->items as $this->item) { ?>
		<div class="splms-person splms-col-md-<?php echo round(12/$columns); ?> splms-col-sm-6">
			<div class="splms-person-details">

				<img src="<?php echo $this->item->image; ?>" class="splms-person-img splms-img-responsive" alt="<?php echo $this->item->title; ?>">

				<div class="splms-person-info-wrap">
					<div class="splms-person-info">
						<small class="splms-person-designation"><?php echo $this->item->designation; ?></small>
						<a class="splms-person-title" href="<?php echo $this->item->url; ?>">
							<?php echo $this->item->title; ?>
						</a>
					</div> <!-- /.splms-person-info -->

					<div class="splms-person-content">
						<?php if (isset($this->item->website)) { ?>
						<p>
							<span><?php echo JText::_('COM_SPLMS_COMMON_WEBSITE') . ': '; ?></span>
							<a href="<?php echo $this->item->website; ?>" target="_blank">
								<?php echo $this->item->website; ?>
							</a>
						</p>
						<?php } ?>
						<?php if (isset($this->item->email)) { ?>
						<p>
							<span><?php echo JText::_('COM_SPLMS_COMMON_EMAIL') . ': '; ?></span>
							<a href="mailto:<?php echo $this->item->email; ?>" target="_blank">
								<?php echo $this->item->email; ?>
							</a>
						</p>
						<?php } ?>

						<?php if (isset($this->item->speaker_events) && $this->item->speaker_events != '') { ?>
						<p>
							<span><?php echo JText::_('COM_SPLMS_COMMON_TOTAL') . ': '; ?>  </span>
							<?php echo $this->item->speaker_events; ?>
							<?php echo JText::_('COM_SPLMS_COMMON_TOTAL_SESSION'); ?>  
						</p>
						<?php } ?>

						<?php if ( (isset($this->item->social_facebook)) ||
						(isset($this->item->social_linkedin)) ||
						(isset($this->item->social_twitter)) ||
						(isset($this->item->social_gplus))) { ?>

						<ul class="splms-persion-social-icons">
							<?php if (isset($this->item->social_facebook)) { ?>			                		
							<li class="facebook">
								<a href="http://facebook.com/<?php echo $this->item->social_facebook; ?>" target="_blank"> 
									<i class="splms-icon-facebook"></i>
								</a>
							</li>
							<?php } if (isset($this->item->social_linkedin)) {?>
							<li class="linkedin">
								<a href="http://linkedin.com/<?php echo $this->item->social_linkedin; ?>" target="_blank"> 
									<i class="splms-icon-linkedin"></i>
								</a>
							</li>
							<?php } if (isset($this->item->social_twitter)) {?>
							<li class="twitter">
								<a href="http://twitter.com/<?php echo $this->item->social_twitter; ?>" target="_blank"> 
									<i class="splms-icon-twitter"></i>
								</a>
							</li>
							<?php } if (isset($this->item->social_gplus)) {?>
							<li class="gplus">
								<a href="https://plus.google.com/<?php echo $this->item->social_gplus; ?>" target="_blank"> 
									<i class="splms-icon-google-plus"></i>
								</a>
							</li>
							<?php } ?>
						</ul>
						<?php } ?>	
					</div> <!-- /.splms-person-content -->
				</div> <!-- /.splms-person-info-wrap -->
			</div><!--/.item-content-->
		</div>

		<?php }  ?>
		<?php } ?>
	</div>
</div>

<!-- BEGIN:: Pagination -->
<?php if ($this->params->get('hide_pagination') == 0) { ?>
<?php if ($this->pagination->get('pages.total') >1) { ?>
<div class="pagination">
	<?php echo $this->pagination->getPagesLinks(); ?>
</div>
<?php } ?>
<?php } ?>
<!-- END:: Pagination -->