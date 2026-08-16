<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski - szymon.woronowski@design-joomla.eu
 *
 * DJ-MediaTools is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * DJ-MediaTools is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with DJ-MediaTools. If not, see <http://www.gnu.org/licenses/>.
 *
 */

// No direct access
defined('_JEXEC') or die; ?>

<div class="product-<?php echo $item->j2store_product_id; ?>">

<!-- Price Container  -->
<?php if($params->get('plg_j2store_show_price', 1)):?>
<div class="product-price-container"
	id="j2store_product_price_<?php echo $item->j2store_product_id; ?>">
	<?php $class='';?>
	<?php if(isset($item->pricing->is_discount_pricing_available)) $class='strike'; ?>

	<div class="base-price <?php echo $class?>">
		<span class="product-element-value"> <?php echo J2Store::product()->displayPrice($item->pricing->base_price, $item, $main_params);?>
		</span>
	</div>

	<?php /** *  If Special Price Exists	 */?>

	<?php if(isset($item->pricing->is_discount_pricing_available)):?>
	<div class="sale-price">
		<span class="product-element-value"> <?php echo J2Store::product()->displayPrice($item->pricing->price, $item, $main_params);?>
		</span>
	</div>
	<?php endif; ?>

	<?php if($main_params->get('display_price_with_tax_info', 0) ): ?>
	<div class="tax-text">
		<?php echo J2Store::product()->get_tax_text(); ?>
	</div>
	<?php endif; ?>

</div>
<?php endif; ?>


<!-- contains cart related section -->
<?php if($params->get('plg_j2store_show_cartbutton',1)): ?>
<div class="j2store_product_cart j2store-product-list-cart">
	<form
		action="<?php echo JRoute::_('index.php?option=com_j2store&view=mycart'); ?>"
		method="post" name="j2storeProductForm"
		data-product_id="<?php echo $item->j2store_product_id; ?>"
		data-product_type="<?php echo $item->product_type; ?>"
		id="j2store-addtocart-form-<?php echo $item->j2store_product_id; ?>"
		class="j2store-addtocart-form">

		<?php if($params->get('plg_j2store_show_cartbutton',1) && $item->product_type == 'simple'):?>
			<?php $cart_type = $params->get('plg_j2store_list_show_cart', 1); ?>
			<?php if($cart_type == 1 && ($item->product_type == 'simple' || $item->product_type == 'downloadable' ))  : ?>
				<!-- Here product options  -->
				<?php require( JPluginHelper::getLayoutPath('djmediatools', 'j2store', 'default_options') );?>
				<?php require( JPluginHelper::getLayoutPath('djmediatools', 'j2store', 'default_cart') );?>
			<?php elseif( ($cart_type == 2 && count($item->options)) || $cart_type == 3 ):?>
				<a href="<?php echo $item->product_link; ?>"
					class="<?php echo $params->get('choosebtn_class', 'btn btn-success'); ?>"><?php echo JText::_('J2STORE_VIEW_PRODUCT_DETAILS'); ?>
				</a>
				<?php echo J2Store::plugin()->eventWithHtml('AfterAddToCartButton', array($item, J2Store::utilities()->getContext('cart'))); ?>
			<?php else:?>
				<?php require( JPluginHelper::getLayoutPath('djmediatools', 'j2store', 'default_cart') );?>
			<?php endif;?>
		<?php else:?>
			<a href="<?php echo $item->product_link; ?>"
				class="<?php echo $params->get('choosebtn_class', 'btn btn-success'); ?>"><?php echo JText::_('J2STORE_VIEW_PRODUCT_DETAILS'); ?>
			</a>
		<?php endif;?>
		<input type="hidden" name="option" value="com_j2store" /> <input
			type="hidden" name="view" value="carts" /> <input type="hidden"
			name="task" value="addItem" /> <input type="hidden" name="ajax"
			value="1" />
		<?php echo JHTML::_( 'form.token' ); ?>
		<input type="hidden" name="return"
			value="<?php echo base64_encode( JUri::getInstance()->toString() ); ?>" />
		<div class="j2store-notifications"></div>
	</form>
</div>
<?php endif; ?>

</div>