<?php

/**

 * @package DJ-MegaMenu
 * @copyright Copyright (C) 2024 DJ-Extensions.com LTD, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: https://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski, Artur Kaczmarek
 *
 * DJ-MegaMenu is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MegaMenu is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MegaMenu. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$djmegamenu = '#dj-megamenu' . $params->get('module_id');

$megabg = $params->get('megabg'); //menu background
$megacolor = $params->get('megacolor'); //menu text
$megastcolor = $params->get('megastcolor'); //menu subtitle

$megabg_a = $params->get('megabg_a'); //active menu background
$megacolor_a = $params->get('megacolor_a'); //active menu text
$megastcolor_a = $params->get('megastcolor_a'); //active menu subtitle

$megasubbg = $params->get('megasubbg'); //submenu background
$megasubcolor = $params->get('megasubcolor'); //submenu text
$megasubstcolor = $params->get('megasubstcolor'); //submenu subtitle

$megasubbg_a = $params->get('megasubbg_a'); //active submenu background
$megasubcolor_a = $params->get('megasubcolor_a'); //active submenu text
$megasubstcolor_a = $params->get('megasubstcolor_a'); //active submenu subtitle

$megamodcolor = $params->get('megamodcolor'); //module text
$megaoverlaycolor = $params->get('megaoverlaycolor'); //overlay background

$megasbg = $params->get('megasbg'); //sticky menu background
$megascolor = $params->get('megascolor'); //sticky menu text
$megasstcolor = $params->get('megasstcolor'); //sticky menu subtitle

$megafocuscolor = $params->get('megafocuscolor');

?>
<?php if ( !empty($megabg) ) { ?>
	<?php echo $djmegamenu; ?>,
	<?php echo $djmegamenu; ?>sticky {
		background: <?php echo $megabg; ?>;
	}
<?php } ?>

<?php if ( !empty($megacolor) ) { ?>
	<?php echo $djmegamenu; ?> li a.dj-up_a {
		color: <?php echo $megacolor; ?>;
	}
<?php } ?>

<?php if ( !empty($megastcolor) ) { ?>
	<?php echo $djmegamenu; ?> li a.dj-up_a small.subtitle {
		color: <?php echo $megastcolor; ?>;
	}
<?php } ?>

<?php if ( !empty($megasbg) ) { ?>
	<?php echo $djmegamenu; ?>sticky,
	<?php echo $djmegamenu; ?>.dj-megamenu-fixed {
		background: <?php echo $megasbg; ?>;
	}
<?php } ?>

<?php if ( !empty($megascolor) ) { ?>
	<?php echo $djmegamenu; ?>.dj-megamenu-fixed li a.dj-up_a {
		color: <?php echo $megascolor; ?>;
	}
<?php } ?>

<?php if ( !empty($megasstcolor) ) { ?>
	<?php echo $djmegamenu; ?>.dj-megamenu-fixed li a.dj-up_a small.subtitle {
		color: <?php echo $megasstcolor; ?>;
	}
<?php } ?>

<?php if ( !empty($megabg_a) ) { ?>
	<?php echo $djmegamenu; ?> li:hover a.dj-up_a,
	<?php echo $djmegamenu; ?> li.hover a.dj-up_a,
	<?php echo $djmegamenu; ?> li.active a.dj-up_a {
		background: <?php echo $megabg_a; ?>;
	}
<?php } ?>

<?php if ( !empty($megacolor_a) ) { ?>
	<?php echo $djmegamenu; ?> li:hover a.dj-up_a,
	<?php echo $djmegamenu; ?> li.hover a.dj-up_a,
	<?php echo $djmegamenu; ?> li.active a.dj-up_a {
		color: <?php echo $megacolor_a; ?>;
	}
<?php } ?>

<?php if ( !empty($megastcolor_a) ) { ?>
	<?php echo $djmegamenu; ?> li:hover a.dj-up_a small.subtitle,
	<?php echo $djmegamenu; ?> li.hover a.dj-up_a small.subtitle,
	<?php echo $djmegamenu; ?> li.active a.dj-up_a small.subtitle {
		color: <?php echo $megastcolor_a; ?>;
	}
<?php } ?>

<?php if ( !empty($megasubbg) ) { ?>
	<?php echo $djmegamenu; ?> li:hover div.dj-subwrap > .dj-subwrap-in,
	<?php echo $djmegamenu; ?> li.hover div.dj-subwrap > .dj-subwrap-in {
		background-color: <?php echo $megasubbg; ?>;
	}

	<?php echo $djmegamenu; ?> li:hover div.dj-subwrap li:hover > div.dj-subwrap > .dj-subwrap-in,
	<?php echo $djmegamenu; ?> li.hover div.dj-subwrap li.hover > div.dj-subwrap > .dj-subwrap-in {
		background-color: <?php echo $megasubbg; ?>;
	}
<?php } ?>

<?php if ( !empty($megasubcolor) ) { ?>
	<?php echo $djmegamenu; ?> li ul.dj-submenu > li > a {
		color: <?php echo $megasubcolor; ?>;
	}

	<?php echo $djmegamenu; ?> li ul.dj-subtree > li > a {
		color: <?php echo $megasubcolor; ?>;
	}
<?php } ?>

<?php if ( !empty($megasubstcolor) ) { ?>
	<?php echo $djmegamenu; ?> li ul.dj-submenu > li > a small.subtitle {
		color: <?php echo $megasubstcolor; ?>;
	}
	<?php echo $djmegamenu; ?> li ul.dj-subtree > li {
		color: <?php echo $megasubstcolor; ?>;
	}
	<?php echo $djmegamenu; ?> li ul.dj-subtree > li > a small.subtitle {
		color: <?php echo $megasubstcolor; ?>;
}
<?php } ?>

<?php if ( !empty($megasubbg_a) ) { ?>
	<?php echo $djmegamenu; ?> li ul.dj-submenu > li > a:hover,
	<?php echo $djmegamenu; ?> li ul.dj-submenu > li > a.active,
	<?php echo $djmegamenu; ?> li ul.dj-submenu > li.hover:not(.subtree) > a {
		background: <?php echo $megasubbg_a; ?>;
	}
<?php } ?>

<?php if ( !empty($megasubcolor_a) ) { ?>
	<?php echo $djmegamenu; ?> li ul.dj-submenu > li > a:hover,
	<?php echo $djmegamenu; ?> li ul.dj-submenu > li > a.active,
	<?php echo $djmegamenu; ?> li ul.dj-submenu > li.hover:not(.subtree) > a {
		color: <?php echo $megasubcolor_a; ?>;
	}

	<?php echo $djmegamenu; ?> li ul.dj-subtree > li > a:hover {
		color: <?php echo $megasubcolor_a; ?>;
	}
<?php } ?>

<?php if ( !empty($megasubstcolor_a) ) { ?>
	<?php echo $djmegamenu; ?> li ul.dj-submenu > li > a:hover small.subtitle,
	<?php echo $djmegamenu; ?> li ul.dj-submenu > li > a.active small.subtitle,
	<?php echo $djmegamenu; ?> li ul.dj-submenu > li.hover:not(.subtree) > a small.subtitle {
		color: <?php echo $megasubstcolor_a; ?>;
	}

	<?php echo $djmegamenu; ?> li ul.dj-subtree > li > a:hover small.subtitle {
		color: <?php echo $megasubstcolor_a; ?>;
	}
<?php } ?>

<?php if ( !empty($megamodcolor) ) { ?>
<?php echo $djmegamenu; ?> .modules-wrap {
	color: <?php echo $megamodcolor; ?>;
}
<?php } ?>

<?php if ( !empty($megaoverlaycolor) ) { ?>
body .dj-megamenu-overlay-box {
	background: <?php echo $params->get('megaoverlaycolor'); ?>;
}
<?php } ?>

<?php if ( !empty($megafocuscolor) ) { ?>
	<?php echo $djmegamenu; ?>.dj-megamenu-wcag *.focus,
	<?php echo $djmegamenu; ?>.dj-megamenu-wcag *:focus-visible {
		outline-color: <?php echo $params->get('megafocuscolor'); ?>;
	}
<?php } ?>
