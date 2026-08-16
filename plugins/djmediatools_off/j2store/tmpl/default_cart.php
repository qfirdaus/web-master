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
defined('_JEXEC') or die;

if(!empty($item->addtocart_text)) {
	$cart_text = JText::_($item->addtocart_text);
} else {
	$cart_text = JText::_('J2STORE_ADD_TO_CART');
}

if($item->variant->availability || J2Store::product()->backorders_allowed($item->variant)) {
	$show = true;
} else {
	$show = false;
}

if($item->product_type == 'variable') {
	$show = true;
}
?>

	<?php if($show): ?>
		<div class="cart-action-complete" style="display:none;">
				<p class="text-success">
					<?php echo JText::_('J2STORE_ITEM_ADDED_TO_CART');?>
					<a href="<?php echo $item->checkout_link; ?>" class="j2store-checkout-link">
						<?php echo JText::_('J2STORE_CHECKOUT'); ?>
					</a>
				</p>
		</div>


		<div id="add-to-cart-<?php echo $item->j2store_product_id; ?>" class="j2store-add-to-cart">

		<?php if($params->get('plg_j2store_show_qtyinput', 1)): ?>
			 <div class="product-qty">
		 		<input type="number" name="product_qty" value="<?php echo (int) $item->quantity; ?>" class="input-mini form-control" min="<?php echo (int) $item->quantity; ?>" step='1' />
			</div>
		<?php else: ?>
			<input type="hidden" name="product_qty" value="<?php echo (int) $item->quantity; ?>" />
		<?php endif; ?>

			<input type="hidden" name="product_id" value="<?php echo $item->j2store_product_id; ?>" />

				<input
					data-cart-action-always="<?php echo JText::_('J2STORE_ADDING_TO_CART'); ?>"
					data-cart-action-done="<?php echo $cart_text; ?>"
					data-cart-action-timeout="1000"
				   value="<?php echo $cart_text; ?>"
				   type="submit"
				   class="j2store-cart-button <?php echo $params->get('addtocart_button_class', 'btn btn-primary');?>"
				   />

	   </div>
	<?php else: ?>
			<input value="<?php echo JText::_('J2STORE_OUT_OF_STOCK'); ?>" type="button" class="j2store_button_no_stock btn btn-warning" />
	<?php endif; ?>
	<?php echo J2Store::plugin()->eventWithHtml('AfterAddToCartButton', array($item, J2Store::utilities()->getContext('cart'))); ?>
			
	<input type="hidden" name="variant_id" value="<?php echo $item->variant->j2store_variant_id;?>" />
	<input type="hidden" name="option" value="com_j2store" />
	<input type="hidden" name="view" value="carts" />
	<input type="hidden" name="task" value="addItem" />
	<input type="hidden" name="ajax" value="0" />
	<?php echo JHTML::_( 'form.token' ); ?>
	<input type="hidden" name="return" value="<?php echo base64_encode( JUri::getInstance()->toString() ); ?>" />
	<div class="j2store-notifications"></div>
