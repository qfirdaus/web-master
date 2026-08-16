<?php


/**
 * @package com_splms
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No Direct Access
defined('_JEXEC') or die('Resticted Aceess');

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;

$skills_layout = $this->params->get('skills_layout', 'pie');

$doc = Factory::getDocument();
if ($skills_layout == 'pie') {
	$doc->addScript(Uri::root(true) . '/components/com_splms/assets/js/jquery.easypiechart.min.js');
}

?>

<div id="splms" class="splms view-splms-teacher splms-person">
	<div class="splms-row">
		<div class="splms-col-sm-4">
			<div class="splms-person-profile-image">
				<img src="<?php echo $this->item->image ?>" class="splms-person-image splms-img-responsive" alt="<?php echo $this->item->title; ?>">
			</div>
		</div>
		<div class="splms-col-sm-8">
			<h2 class="splms-teacher-name">
				<?php echo $this->item->title; ?>
			</h2>
			<h3 class="teacher-biography"><?php echo Text::_('COM_SPLMS_SHORT_INFO'); ?></h3>
			<div class="splms-section">
				<ul class="splms-teacher-information list-unstyled">
					<?php if ($this->item->ratings->ratings > 0) { ?>
						<li class="splms-teacher-ratings">
							<?php echo Text::_('COM_SPLMS_RATINGS') . ': '; ?>
							<span><?php echo round(($this->item->ratings->ratings / $this->item->ratings->count), 1); ?></span>
						</li>
					<?php } ?>

					<?php if ($this->item->designation) { ?>
						<li class="splms-teacher-designation">
							<?php echo Text::_('COM_SPLMS_DESIGNATION') . ': '; ?>
							<?php echo $this->item->designation; ?>
						</li>
					<?php } ?>

					<?php if (!empty($this->item->experience)) { ?>
						<li class="splms-teacher-experience">
							<?php echo Text::_('COM_SPLMS_COMMON_EXPERIENCE') . ': '; ?>
							<span><?php echo $this->item->experience; ?></span>
						</li>
					<?php } ?>

					<li class="splms-teacher-course-count">
						<?php echo Text::_('COM_SPLMS_COURSES') . ': '; ?>
						<span><?php echo $this->item->courseCount; ?></span>
					</li>

					<?php if ($this->params->get('follow_teacher', 0)) { ?>
						<li class="splms-teacher-followers">
							<?php echo Text::_('COM_SPLMS_TEACHER_FOLLOWERS') . ': '; ?>
							<span data-followers-count data-count="<?php echo $this->item->followersCount; ?>"><?php echo $this->item->followersCount; ?></span>
						</li>
					<?php } ?>

					<?php if (!empty($this->item->website)) { ?>
						<li class="splms-person-website">
							<?php echo Text::_('COM_SPLMS_COMMON_WEBSITE') . ': '; ?>
							<a href="<?php echo $this->item->website; ?>" target="_blank">
								<?php echo $this->item->website; ?> </a>
						</li>
					<?php } ?>
				</ul>
			</div>
			<?php echo LayoutHelper::render('teacher.social_icons', array('teacher' => $this->item)); ?>
		</div>

		<div class="splms-col-sm-12">
			<div class="splms-section">
				<div class="splms-teacher-bio">
					<h3 class="teacher-biography"><?php echo Text::_('COM_SPLMS_BIOGRAPHY'); ?></h3>
					<?php echo $this->item->description; ?>
				</div>
			</div>

			<div id="splms-teacher-contact-from" class="splms-teacher-contact-from">
				<div class="contact-from-title-wrap splms-text-center">
					<div class="contact-from-title">
						<h3 class="title-heading">
							<?php echo Text::_('COM_SPLMS_TEACHER_CONTACT_TITLE') . " " . $this->item->title; ?></h3>
						<p class="title-subheading"><?php echo Text::_('COM_SPLMS_PERSON_CONTACT_TITLE_SUBTITLE'); ?>
						</p>
					</div>
				</div>
				<?php echo LayoutHelper::render('teacher.contact', array('teacher' => $this->item)); ?>
			</div>
		</div>


		<?php if (!empty($this->item->email) && !$this->params->get('teacher_contact')) { ?>
			<p class="splms-person-email">
				<a class="btn btn-success" href="<?php echo 'mailto:' . $this->item->email; ?>">
					<i class="splms-icon-envelope"></i> <?php echo Text::_('COM_SPLMS_TEACHER_SEND_MESSAGE'); ?>
				</a>
			</p>
		<?php } ?>

		<?php if ($this->params->get('follow_teacher', 0)) { ?>
			<div class="splms-section splms-teacher-toggle-follow">
				<a href="#" class="btn btn-primary" action-toggle-follow data-id="<?php echo $this->item->id; ?>">
					<i class="splms-icon splms-icon-<?php echo $this->item->isFollowing == 1 ? 'remove' : 'add'; ?>"></i>
					<span><?php echo ($this->item->isFollowing == 1) ? Text::_('COM_SPLMS_TEACHER_FOLLOWING') : Text::_('COM_SPLMS_TEACHER_FOLLOW'); ?></span>
				</a>
			</div>
		<?php } ?>

		<?php if (!empty($this->item->featuredCourse)) { ?>
			<div class="splms-section splms-teacher-featured-course">
				<h3 class="splms-section-title"><?php echo Text::_('COM_SPLMS_TEACHER_FEATURED_COURSE'); ?></h3>
				<?php if (!empty($this->item->featuredCourse->video_url)) { ?>
					<div class="splms-course-video">
						<?php echo LayoutHelper::render('player', array('video' => $this->item->featuredCourse->video_url, 'thumbnail' => $this->item->featuredCourse->image)); ?>
					</div>
				<?php } elseif ($this->item->featuredCourse->image) { ?>
					<div class="splms-course-thumbnail">
						<img class="splms-img-responsive" src="<?php echo $this->item->featuredCourse->image; ?>" alt="<?php echo $this->item->featuredCourse->title; ?>">
					</div>
				<?php } ?>
				<div class="splms-teacher-featured-course-info">
					<h3 class="splms-teacher-featured-course-title">
						<a href="<?php echo $this->item->featuredCourse->link; ?>">
							<?php echo $this->item->featuredCourse->title; ?>
						</a>
					</h3>
					<span class="splms-course-duration splms-pull-right"><?php echo $this->item->featuredCourse->duration; ?></span>
				</div>
			</div>
		<?php } ?>

		<?php if ($this->params->get('follow_teacher', 0) && (isset($this->item->followers) && count($this->item->followers))) { ?>
			<div class="splms-section">
				<h3 class="splms-section-title"><?php echo Text::_('COM_SPLMS_TEACHER_FOLLOWER_LIST'); ?></h3>
				<div class="splms-teacher-follower-list splms-row" data-teacher-follower-list>
					<?php foreach ($this->item->followers as $follower) { ?>
						<?php echo LayoutHelper::render('avatar', array('avatar' => $follower->avatar, 'title' => $follower->name)); ?>
					<?php } ?>
				</div>
				<?php if (count($this->item->followers) < $this->item->followersCount) { ?>
					<a href="#" class="btn btn-primary btn-load-followers" action-load-followers data-teacher="<?php echo $this->item->id; ?>" data-total="<?php echo $this->item->followersCount; ?>" data-loaded="<?php echo count($this->item->followers); ?>"><?php echo Text::_('COM_SPLMS_LOAD_MORE'); ?></a>
				<?php } ?>
			</div>
		<?php } ?>
	</div>
</div>