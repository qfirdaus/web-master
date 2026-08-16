<?php
/**
 * @package     JO Visitor Counter
 * @subpackage  mod_jo_visitor_counter
 *
 * @copyright   Copyright (C) 2025 Your Name. All rights reserved.
 * @license     GNU/GPL v2 or later http://www.gnu.org/licenses/gpl-2.0.html
 */
 
defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

$moduleTheme = $params->get('module_theme', 'light');
// Retrieve visitor statistics and toggles
$showVisitorCounter = (bool) $params->get('show_visitor_counter', 1);
$visitorStatsEnabled = (bool) $params->get('visitor_statistics', 1);
$showRecentVisitors = (bool) $params->get('show_recent_visitors', 1);
$showVisitorByCountry = (bool) $params->get('show_visitor_by_country', 1);
$showToday = (bool) $params->get('show_today', 1);
$showYesterday = (bool) $params->get('show_yesterday', 1);
$showThisWeek = (bool) $params->get('show_this_week', 1);
$showLastWeek = (bool) $params->get('show_last_week', 1);
$showThisMonth = (bool) $params->get('show_this_month', 1);
$showLastMonth = (bool) $params->get('show_last_month', 1);
$showTwoMonthsAgo = (bool) $params->get('show_two_months_ago', 1);
$showThreeMonthsAgo = (bool) $params->get('show_three_months_ago', 1);
$showFourMonthsAgo = (bool) $params->get('show_four_months_ago', 1);
$showFiveMonthsAgo = (bool) $params->get('show_five_months_ago', 1);
$showLastSixMonths = (bool) $params->get('show_last_six_months', 1);
$showThisYear = (bool) $params->get('show_this_year', 1);
$showLastYear = (bool) $params->get('show_last_year', 1);
$calculationPeriod = $params->get('calculation_period', 'all_time');
$counterStyle = $params->get('counter_style', 'default');
$totalVisitors = ModJOVisitorCounterDisplayHelper::getTotalVisitors();
$visitorStats = ModJOVisitorCounterDisplayHelper::getVisitorStatistics();
$visitors = ModJOVisitorCounterDisplayHelper::getVisitors($params);
$visitorsByCountry = ModJOVisitorCounterDisplayHelper::getVisitorsByCountry($calculationPeriod, $params);

// Get the selected layout
$layout = $params->get('layout', 'vertical'); // Default is "vertical"

// Count the number of enabled sections
$enabledSections = 0;
$enabledSections += $showVisitorCounter ? 1 : 0;
$enabledSections += $visitorStatsEnabled ? 1 : 0;
$enabledSections += $showRecentVisitors ? 1 : 0;
$enabledSections += $showVisitorByCountry ? 1 : 0;

// Calculate the Bootstrap column width dynamically
$columnClass = $enabledSections > 0 ? 'col-md-' . (12 / $enabledSections) : 'col-md-12';

// Include Flag Icons CSS
$flagIconsCss = \Joomla\CMS\Uri\Uri::base(true) . '/modules/mod_jovisitorcounter/assets/flag-icons/css/flag-icons.min.css';
$moduleCss = \Joomla\CMS\Uri\Uri::base(true) . '/modules/mod_jovisitorcounter/assets/css/mod_jovisitorcounter.css';

// Local paths for Odometer.js
$odometerJs = \Joomla\CMS\Uri\Uri::base(true) . '/modules/mod_jovisitorcounter/assets/js/odometer.min.js';
?>
<link rel="stylesheet" href="<?php echo $flagIconsCss; ?>">
<link rel="stylesheet" href="<?php echo $moduleCss; ?>">

<!-- Include Odometer.js -->
<?php if ($counterStyle === 'animated_odometer'): ?>
    <?php
    // Get the selected Odometer theme
    $odometerTheme = $params->get('odometer_theme', 'default');
    $odometerThemeCss = \Joomla\CMS\Uri\Uri::base(true) . '/modules/mod_jovisitorcounter/assets/css/odometer-themes/odometer-theme-' . $odometerTheme . '.css';
    ?>
    <link rel="stylesheet" href="<?php echo $odometerThemeCss; ?>">
    <script src="<?php echo $odometerJs; ?>"></script>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const odometerElement = document.getElementById('visitor-counter');
            if (odometerElement) {
                setTimeout(() => {
                    odometerElement.innerHTML = <?php echo $totalVisitors; ?>;
                }, 500); // Delay to trigger the animation
            }
        });
    </script>
<?php endif; ?>

<div class="mod_jovisitorcounter <?php echo $moduleTheme === 'dark' ? 'dark-theme' : 'light-theme'; ?>">
    <div class="row">
        <!-- Visitor Counter Section -->
        <?php if ($showVisitorCounter && !$visitorStatsEnabled): ?>
            <?php
            // Get custom responsive classes for Visitor Counter
            $responsiveClassesVisitorCounter = trim($params->get('responsive_classes_visitor_counter', ''));
            $visitorCounterClass = !empty($responsiveClassesVisitorCounter)
                ? $responsiveClassesVisitorCounter
                : ($layout === 'horizontal' ? $columnClass : 'col-md-12 mb-4');
            ?>
            <div class="<?php echo $visitorCounterClass; ?> visitor-counter">
			<?php if ($params->get('show_title_visitor_counter', 1)): ?>
				<h4><?php echo Text::_('MOD_JOVISITORCOUNTER_VISITOR_COUNTER'); ?></h4>
			<?php endif; ?>
                <div class="counter-container">
                    <?php if ($counterStyle === 'default'): ?>
                        <p class="h1 text-center"><?php echo $totalVisitors; ?></p>
                    <?php elseif ($counterStyle === 'badge'): ?>
                        <span class="badge bg-primary h1 text-center"><?php echo $totalVisitors; ?></span>
                    <?php elseif ($counterStyle === 'animated_odometer'): ?>
                        <p id="visitor-counter" class="h1 text-center odometer">0</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- Visitor Statistics Section -->
        <?php if ($visitorStatsEnabled): ?>
            <?php
            // Get custom responsive classes for Visitor Statistics
            $responsiveClassesVisitorStatistics = trim($params->get('responsive_classes_visitor_statistics', ''));
            $visitorStatisticsClass = !empty($responsiveClassesVisitorStatistics)
                ? $responsiveClassesVisitorStatistics
                : ($layout === 'horizontal' ? $columnClass : 'col-md-12');
            ?>
            <div class="<?php echo $visitorStatisticsClass; ?>">
			<?php if ($params->get('show_title_visitor_statistics', 1)): ?>
				<h4><?php echo Text::_('MOD_JOVISITORCOUNTER_STATISTICS'); ?></h4>
			<?php endif; ?>
                <ul class="list-group">
                    <?php if ($showToday): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_TODAY'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['today']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showYesterday): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_YESTERDAY'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['yesterday']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showThisWeek): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_THIS_WEEK'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['this_week']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showLastWeek): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_LAST_WEEK'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['last_week']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showThisMonth): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_THIS_MONTH'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['this_month']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showLastMonth): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_LAST_MONTH'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['last_month']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showTwoMonthsAgo): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_TWO_MONTHS_AGO'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['two_months_ago']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showThreeMonthsAgo): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_THREE_MONTHS_AGO'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['three_months_ago']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showFourMonthsAgo): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_FOUR_MONTHS_AGO'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['four_months_ago']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showFiveMonthsAgo): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_FIVE_MONTHS_AGO'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['five_months_ago']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showLastSixMonths): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_LAST_SIX_MONTHS'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['last_six_months']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showThisYear && !$showVisitorCounter): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_THIS_YEAR'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['this_year']; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showVisitorCounter): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_TOTAL_VISITORS'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $totalVisitors; ?></span>
                        </li>
                    <?php endif; ?>
                    <?php if ($showLastYear): ?>
                        <li class="list-group-item d-flex justify-content-between align-items-center">
                            <?php echo Text::_('MOD_JOVISITORCOUNTER_LAST_YEAR'); ?>
                            <span class="badge bg-primary rounded-pill"><?php echo $visitorStats['last_year']; ?></span>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Recent Visitors Section -->
        <?php if ($showRecentVisitors): ?>
            <?php
            // Get custom responsive classes for Recent Visitors
            $responsiveClassesRecentVisitors = trim($params->get('responsive_classes_recent_visitors', ''));
            $recentVisitorsClass = !empty($responsiveClassesRecentVisitors)
                ? $responsiveClassesRecentVisitors
                : ($layout === 'horizontal' ? $columnClass : 'col-md-12 mt-4');
            ?>
            <div class="<?php echo $recentVisitorsClass; ?>">
				<?php if ($params->get('show_title_recent_visitors', 1)): ?>
					<h4><?php echo Text::_('MOD_JOVISITORCOUNTER_RECENT_VISITORS'); ?></h4>
				<?php endif; ?>
                <?php if (!empty($visitors)): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th><?php echo Text::_('MOD_JOVISITORCOUNTER_IP'); ?></th>
                                <th><?php echo Text::_('MOD_JOVISITORCOUNTER_COUNTRY'); ?></th>
                                <th><?php echo Text::_('MOD_JOVISITORCOUNTER_TIME'); ?></th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($visitors as $visitor): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($visitor->ip_address); ?></td>
                                    <td>
                                        <?php if ($visitor->country_code && $visitor->country_code !== 'unknown'): ?>
                                            <span class="fi fi-<?php echo htmlspecialchars($visitor->country_code); ?> me-2"></span>
                                        <?php endif; ?>
                                        <?php echo htmlspecialchars($visitor->country); ?>
                                    </td>
                                    <td><?php echo htmlspecialchars($visitor->visit_time); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <p><?php echo Text::_('MOD_JOVISITORCOUNTER_NO_VISITORS'); ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <!-- Visitor by Country Section -->
<?php if ($showVisitorByCountry): ?>
    <?php
    // Get custom responsive classes for Visitor by Country
    $responsiveClassesVisitorByCountry = trim($params->get('responsive_classes_visitor_by_country', ''));
    $visitorByCountryClass = !empty($responsiveClassesVisitorByCountry)
        ? $responsiveClassesVisitorByCountry
        : ($layout === 'horizontal' ? $columnClass : 'col-md-12 mt-4');
    ?>
    <div class="<?php echo $visitorByCountryClass; ?> visitor-by-country">
		<?php if ($params->get('show_title_visitor_by_country', 1)): ?>
		<?php
		// Map calculation period to language constant suffix
		$periodLabels = [
			'today'            => 'TODAY',
			'yesterday'        => 'YESTERDAY',
			'this_week'        => 'THIS_WEEK',
			'last_week'        => 'LAST_WEEK',
			'this_month'       => 'THIS_MONTH',
			'last_month'       => 'LAST_MONTH',
			'last_six_months'  => 'LAST_SIX_MONTHS',
			'this_year'        => 'THIS_YEAR',
			'all_time'         => 'ALL_TIME',
		];

		$suffix = $periodLabels[$calculationPeriod] ?? 'ALL_TIME';
		$headingKey = "MOD_JOVISITORCOUNTER_VISITOR_BY_COUNTRY_{$suffix}";

		$visitorByCountryHeading = Text::_($headingKey);
		?>
		<h4><?php echo $visitorByCountryHeading; ?></h4>
		<?php endif; ?>		
        <?php if (!empty($visitorsByCountry)): ?>
            <table class="table" aria-label="Visitors by Country">
                <thead>
                    <tr>
                        <th><?php echo Text::_('MOD_JOVISITORCOUNTER_COUNTRY'); ?></th>
                        <th><?php echo Text::_('MOD_JOVISITORCOUNTER_TOTAL_VISITORS'); ?></th>
                        <th><?php echo Text::_('MOD_JOVISITORCOUNTER_PERCENTAGE'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($visitorsByCountry as $countryData): ?>
                        <tr>
							<td>
								<?php if ($countryData->country_code && $countryData->country_code !== 'unknown'): ?>
									<span class="fi fi-<?php echo htmlspecialchars($countryData->country_code); ?> me-2"></span>
								<?php endif; ?>
								<?php echo !empty($countryData->country_name) ? htmlspecialchars($countryData->country_name) : 'Unknown'; ?>
							</td>
                            <td><?php echo isset($countryData->total_visitors) ? $countryData->total_visitors : 0; ?></td>
                            <td><?php echo isset($countryData->percentage) ? $countryData->percentage . '%' : '0%'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <p><?php echo Text::_('MOD_JOVISITORCOUNTER_NO_DATA'); ?></p>
        <?php endif; ?>
    </div>
<?php endif; ?>
    </div>
</div>
