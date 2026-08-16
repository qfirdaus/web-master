<?php 
/**

 * @package DJ-MegaMenu
 * @copyright Copyright (C) 2024 DJ-Extensions.com, All rights reserved.
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

$djmegamenu = '#dj-megamenu' . $params->get('module_id') . 'mobile';
// set different #ID for Offcanvas wrapper
$djmegamenuNav = ( $params->get('select', '0') == '2' ) ? '#dj-megamenu' . $params->get('module_id') . 'offcanvas' : $djmegamenu;

$mobilebtnbg = $params->get('mobilebtnbg'); //Open button background
$mobilebtncolor = $params->get('mobilebtncolor'); //Open button color
$mobilebg = $params->get('mobilebg'); //Menu background
$mobilecolor = $params->get('mobilecolor'); //Menu text
$mobilestcolor = $params->get('mobilestcolor'); //Menu subtitle
$mobilebg_a = $params->get('mobilebg_a'); //Active menu background
$mobilecolor_a = $params->get('mobilecolor_a'); //Active menu text
$mobilestcolor_a = $params->get('mobilestcolor_a'); //Active menu subtitle
$mobilesubbg = $params->get('mobilesubbg'); //Submenu background
$mobilesubcolor = $params->get('mobilesubcolor'); //Submenu text
$mobilesubstcolor = $params->get('mobilesubstcolor'); //Submenu subtitle
$mobilesubbg_a = $params->get('mobilesubbg_a'); //Active submenu background
$mobilesubcolor_a = $params->get('mobilesubcolor_a'); //Active submenu text
$mobilesubstcolor_a = $params->get('mobilesubstcolor_a'); //Active submenu subtitle
$mobilemodcolor = $params->get('mobilemodcolor'); //Module text

?>
<?php if ( !empty($mobilebtncolor) ) { ?>
	<?php echo $djmegamenu; ?>.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn,
	<?php echo $djmegamenu; ?>.dj-megamenu-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn,
	<?php echo $djmegamenu; ?>.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn {
		color: <?php echo $mobilebtncolor; ?>;
	}

	<?php echo $djmegamenu; ?>.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:focus,
	<?php echo $djmegamenu; ?>.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?>:hover .dj-mobile-open-btn,
	<?php echo $djmegamenu; ?>.dj-megamenu-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:hover,
	<?php echo $djmegamenu; ?>.dj-megamenu-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:focus,
	<?php echo $djmegamenu; ?>.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:hover,
	<?php echo $djmegamenu; ?>.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:focus {
		background: <?php echo $mobilebtncolor; ?>;
	}
<?php } ?>

<?php if ( !empty($mobilebtnbg) ) { ?>
	<?php echo $djmegamenu; ?>.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn,
	<?php echo $djmegamenu; ?>.dj-megamenu-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn,
	<?php echo $djmegamenu; ?>.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn {
		background: <?php echo $mobilebtnbg; ?>;
	}

	<?php echo $djmegamenu; ?>.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:focus,
	<?php echo $djmegamenu; ?>.dj-megamenu-select-<?php echo $params->get('mobiletheme') ?>:hover .dj-mobile-open-btn,
	<?php echo $djmegamenu; ?>.dj-megamenu-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:hover,
	<?php echo $djmegamenu; ?>.dj-megamenu-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:focus,
	<?php echo $djmegamenu; ?>.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:hover,
	<?php echo $djmegamenu; ?>.dj-megamenu-accordion-<?php echo $params->get('mobiletheme') ?> .dj-mobile-open-btn:focus {
		color: <?php echo $mobilebtnbg; ?>;
	}
<?php } ?>

<?php if ( !empty($mobilemodcolor) ) { ?>
	<?php echo $djmegamenuNav; ?>.dj-offcanvas-<?php echo $params->get('mobiletheme') ?> {
		color: <?php echo $mobilemodcolor; ?>;
	}

	<?php echo $djmegamenu; ?> .dj-accordion-<?php echo $params->get('mobiletheme') ?> .dj-accordion-in {
		color: <?php echo $mobilemodcolor; ?>;
	}
<?php } ?>

<?php if ( !empty($mobilebg) ) { ?>
	<?php echo $djmegamenuNav; ?>.dj-offcanvas-<?php echo $params->get('mobiletheme') ?> {
		background: <?php echo $mobilebg; ?>;
	}

	<?php echo $djmegamenuNav; ?>.dj-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-offcanvas-top {
		background: <?php echo adjustBrightness($mobilebg, 1.1); ?>;
	}

	<?php echo $djmegamenu; ?> .dj-accordion-<?php echo $params->get('mobiletheme') ?> .dj-accordion-in {
		background: <?php echo $mobilebg; ?>;
	}

	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem > a {
		background: <?php echo $mobilebg; ?>;
		border-top-color: <?php echo adjustBrightness($mobilebg, 1.2); ?>;
	}
<?php } ?>

<?php if ( !empty($mobilecolor) ) { ?>
	<?php echo $djmegamenuNav; ?>.dj-offcanvas-<?php echo $params->get('mobiletheme') ?> .dj-offcanvas-close-btn {
		color: <?php echo $mobilecolor; ?>;
	}

	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem > a {
		color: <?php echo $mobilecolor; ?>;
	}
<?php } ?>

<?php if ( !empty($mobilecolor_a) ) { ?>
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem:hover > a,
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem.active > a {
		color: <?php echo $mobilecolor_a; ?>;
	}
<?php } ?>

<?php if ( !empty($mobilebg_a) ) { ?>
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem:hover > a,
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem.active > a {
		background: <?php echo $mobilebg_a; ?>;
	}
<?php } ?>

<?php if ( !empty($mobilestcolor) ) { ?>
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem > a .subtitle {
		color: <?php echo $mobilestcolor; ?>;
	}
<?php } ?>

<?php if ( !empty($mobilestcolor_a) ) { ?>
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem:hover > a .subtitle,
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> li.dj-mobileitem.active > a .subtitle {
		color: <?php echo $mobilestcolor_a; ?>;
	}
<?php } ?>

<?php if ( !empty($mobilesubcolor) ) { ?>
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem > a {
		color: <?php echo $mobilesubcolor; ?>;
	}
<?php } ?>

<?php if ( !empty($mobilesubbg) ) { ?>
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem > a {
		background: <?php echo $mobilesubbg; ?>;
		border-top-color: <?php echo adjustBrightness($mobilesubbg, 1.2); ?>;
	}

	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul li.dj-mobileitem > a {
		background: <?php echo adjustBrightness($mobilesubbg, 0.9); ?>;
		border-top-color: <?php echo adjustBrightness($mobilesubbg, 1.2); ?>;
	}

	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul ul li.dj-mobileitem > a {
		background: <?php echo adjustBrightness($mobilesubbg, 0.9); ?>;
		border-top-color: <?php echo adjustBrightness($mobilesubbg, 1.2); ?>;
	}
<?php } ?>

<?php if ( !empty($mobilesubcolor_a) ) { ?>
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem:hover > a,
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem.active > a {
		color: <?php echo $mobilesubcolor_a; ?>;
	}
<?php } ?>

<?php if ( !empty($mobilesubbg_a) ) { ?>
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem:hover > a,
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem.active > a {
		background: <?php echo $mobilesubbg_a; ?>;
	}

	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul li.dj-mobileitem:hover > a,
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul li.dj-mobileitem.active > a {
		background: <?php echo adjustBrightness($mobilesubbg_a, 0.9); ?>;
	}

	<?php /* ?>
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul ul li.dj-mobileitem:hover > a,
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul ul li.dj-mobileitem.active > a {
		background: <?php echo adjustBrightness($mobilesubbg_a, 0.9); ?>;
	}

	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul ul ul ul li.dj-mobileitem > a {
		background: <?php echo adjustBrightness($mobilesubbg_a, 0.9); ?>;
	}
	<?php */ ?>
<?php } ?>

<?php if ( !empty($mobilesubstcolor) ) { ?>
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem > a .subtitle {
		color: <?php echo $mobilesubstcolor; ?>;
	}
<?php } ?>

<?php if ( !empty($mobilesubstcolor_a) ) { ?>
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem:hover > a .subtitle,
	<?php echo $djmegamenuNav; ?> ul.dj-mobile-<?php echo $params->get('mobiletheme') ?> ul li.dj-mobileitem.active > a .subtitle {
		color: <?php echo $mobilesubstcolor_a; ?>;
	}
<?php } ?>
