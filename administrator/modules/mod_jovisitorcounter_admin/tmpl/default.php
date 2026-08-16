<?php
/**
 * @package     JO Visitor Counter Admin
 * @subpackage  mod_jovisitorcounter_admin
 *
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
?>

<div class="mod_jovisitorcounter-admin table-responsive card p-3 mb-4">
    <?php if ($showTotal): ?>
        <div class="text-center mb-3" style="border: 1px solid #ddd;">
			<div style="padding: 1rem 0;font-weight: 700;">
            <?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_TOTAL_VISITORS'); ?>
			</div>
            <span class="display-4"><?php echo $totalVisitors; ?></span>
        </div>
    <?php endif; ?>

    <?php if ($showStatistics && !empty($visitorStats)): ?>
	<div class="mb-3">
		<div style="padding: 1rem 0;font-weight: 700;">
        <?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_STATISTICS_TITLE'); ?>
		</div>
		<table class="table table-striped table-sm" style="border: 1px solid var(--border-color);font-size: .9rem;">
		<?php if ($showToday): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_TODAY'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['today']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showYesterday): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_YESTERDAY'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['yesterday']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showThisWeek): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_THIS_WEEK'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['this_week']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showLastWeek): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_LAST_WEEK'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['last_week']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showThisMonth): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_THIS_MONTH'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['this_month']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showLastMonth): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_LAST_MONTH'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['last_month']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showTwoMonthsAgo): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_TWO_MONTHS_AGO'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['two_months_ago']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showThreeMonthsAgo): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_THREE_MONTHS_AGO'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['three_months_ago']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showFourMonthsAgo): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_FOUR_MONTHS_AGO'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['four_months_ago']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showFiveMonthsAgo): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_FIVE_MONTHS_AGO'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['five_months_ago']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showLastSixMonths): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_LAST_SIX_MONTHS'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['last_six_months']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showThisYear): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_THIS_YEAR'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['this_year']; ?></td>
			</tr>
		<?php endif; ?>

		<?php if ($showLastYear): ?>
			<tr>
				<td><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_LAST_YEAR'); ?></td>
				<td class="text-end"><?php echo (int)$visitorStats['last_year']; ?></td>
			</tr>
		<?php endif; ?>
		</table>
	</div>
    <?php endif; ?>

    <!-- Recent Visitors Section -->
    <?php if ($showRecentVisitors && !empty($recentVisitors)): ?>
	<div class="mb-3">
		<div style="padding: 1rem 0;font-weight: 700;">
        <?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_RECENT_VISITORS'); ?>
		</div>
        <table class="table table-striped table-sm" style="border: 1px solid var(--border-color);font-size: .9rem;">
            <thead>
                <tr>
                    <th><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_IP_ADDRESS'); ?></th>
                    <th><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_COUNTRY'); ?></th>
                    <th><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_TIME'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentVisitors as $row): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row->ip_address); ?></td>
                        <td>
                            <?php if (!empty($row->country_code) && $row->country_code !== 'unknown'): ?>
                                <span class="fi fi-<?php echo $row->country_code; ?>"></span>
                            <?php endif; ?>
                            <?php echo htmlspecialchars($row->country ?? 'Unknown'); ?>
                        </td>
                        <td><?php echo HTMLHelper::_('date', $row->visit_time, Text::_('DATE_FORMAT_LC4')); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
	</div>
    <?php endif; ?>

    <!-- Visitor by Country Section -->
	<?php if ($showVisitorByCountry && !empty($visitorsByCountry)): ?>
	<div class="mb-3">
		<div style="padding: 1rem 0;font-weight: 700;">
		<?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_TOP_COUNTRIES'); ?>
		</div>
		<table class="table table-striped table-sm" style="border: 1px solid var(--border-color);font-size: .9rem;">
			<thead>
				<tr>
					<th><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_COUNTRY'); ?></th>
					<th><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_TOTAL_VISITORS'); ?></th>
					<th><?php echo Text::_('MOD_JOVISITORCOUNTER_ADMIN_PERCENTAGE'); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($visitorsByCountry as $country): ?>
					<tr>
						<td>
							<?php if (!empty($country->country_code) && $country->country_code !== 'unknown'): ?>
								<span class="fi fi-<?php echo htmlspecialchars($country->country_code); ?> me-2"></span>
							<?php endif; ?>
							<?php echo htmlspecialchars($country->country_name ?? 'Unknown'); ?>
						</td>
						<td><?php echo (int)$country->total_visitors; ?></td>
						<td><?php echo (float)$country->percentage; ?>%</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	</div>	
	<?php endif; ?>
</div>