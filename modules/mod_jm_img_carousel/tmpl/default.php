<?php
/*
 * Copyright (C) joomla-monster.com
 * Website: http://www.joomla-monster.com
 * Support: info@joomla-monster.com
 *
 * JM Image Carousel is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * JM Image Carousel is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with JM Image Carousel. If not, see <http://www.gnu.org/licenses/>.
*/

// no direct access
defined( '_JEXEC' ) or die( 'Restricted access' );

$i = 0;

$navigation = '';
$aria_live = '';

if( $indicators ) {
	$navigation .= 'nav-indicators ';
} else {
	$navigation .= 'nav-buttons ';
	$aria_live = "aria-live='off'";
}

$widget_name = $id;

?>
<div id="<?php echo $id; ?>" class="jmm-img-carousel <?php echo $mod_class_suffix; ?>">
	<div class="jmm-carousel">
		<ul class="jmm-carousel-in <?php echo $navigation; ?>" id="<?php echo $widget_name; ?>-container" <?php echo $aria_live; ?>>
		<?php

		foreach($output_data as $item) {

		$i++;
		$alt = ( !empty($item->alt) ) ? $item->alt : '';

		$aria = '';
		if( $indicators ){
			$aria = 'aria-labeledby="' . $widget_name. '-' . $i . '"';
		} else {
			$aria = 'aria-roledescription="slide" aria-label="' . $i . ' of ' . $elements . '"';
		}

		?>
			<li id="tab-<?php echo $widget_name; ?>-<?php echo $i; ?>" class="jmm-item item-<?php echo $i; ?>" data-flip-title="<?php echo $i; ?>" role="tabpanel">
				<?php if( !empty($item->url) ) : ?>
				<a href="<?php echo $item->url; ?>" target="<?php echo $target_attribute; ?>" <?php echo $aria; ?>>
				<?php else : ?>
				<a <?php echo $aria; ?>>
				<?php endif; ?>
					<img src="<?php echo $item->slide_image; ?>" alt="<?php echo $alt; ?>">
					<?php if( !empty($item->title) ) { ?>
						<div class="jmm-title">
							<?php echo $item->title; ?>
						</div>
					<?php } ?>
				<?php if( !empty($item->url) ) : ?>
				</a>
				<?php else : ?>
				</a>
				<?php endif; ?>
			</li>
		<?php

		}

		?>
		</ul>
	</div>
</div>
