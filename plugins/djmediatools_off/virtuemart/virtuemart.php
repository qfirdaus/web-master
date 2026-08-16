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

// no direct access
defined('_JEXEC') or die;

class plgDJMediatoolsVirtuemart extends JPlugin
{
	/**
	 * Plugin that returns the object list for DJ-Mediatools album
	 * 
	 * Each object must contain following properties (mandatory): title, description, image
	 * Optional properties: link, target (_blank or _self), alt (alt attribute for image)
	 * 
	 * @param	object	The album params
	 */
	public function onAlbumPrepare(&$source, &$params)
	{
		// Lets check the requirements
		$check = $this->onCheckRequirements($source);
		if (is_null($check) || is_string($check)) {
			return null;
		}
		
		$app = JFactory::getApplication();
		
		// Load the language file of com_virtuemart.
		JFactory::getLanguage ()->load ('com_virtuemart');
		/* Load  VM fonction */
		if (!class_exists( 'VmConfig' )) require(JPATH_ADMINISTRATOR  . '/components/com_virtuemart/helpers/config.php');
		VmConfig::loadConfig();
		VmConfig::loadJLang('mod_virtuemart_product', true);
		include_once(JPATH_ADMINISTRATOR . '/components/com_virtuemart/tables/categories.php');
		if (!class_exists ('calculationHelper')) require(JPATH_ADMINISTRATOR  . '/components/com_virtuemart/helpers/calculationh.php');
		if (!class_exists ('CurrencyDisplay')) require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/currencydisplay.php');
		if (!class_exists ('VirtueMartModelVendor')) require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/models/vendor.php');
		if (!class_exists ('VmImage')) require(JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/image.php');
		if (!class_exists ('shopFunctionsF')) require(JPATH_SITE . '/components/com_virtuemart/helpers/shopfunctionsf.php');
		if (!class_exists ('calculationHelper')) require(JPATH_COMPONENT_SITE  . 'helpers/cart.php');
		if (!class_exists ('VirtueMartModelProduct')) JLoader::import ('product', JPATH_ADMINISTRATOR . '/components/com_virtuemart/models');
		
		$category_id = $params->get('plg_virtuemart_category_id', null);
		$product_group = $params->get('plg_virtuemart_product_group', 'latest');
		$max_items = ($params->get('max_images'));
		$show_price = (bool) $params->get('plg_virtuemart_show_price', 1);
		$show_addtocart = (bool) $params->get('plg_virtuemart_show_addtocart', 1);
		$menu_item = $params->get('plg_virtuemart_itemid','');
		if(!empty($menu_item)) $menu_item = '&Itemid='.$menu_item;
		$default_image = $params->get('plg_virtuemart_image');
		
		$filter_category = $category_id ? TRUE : FALSE;
		
		$productModel = VmModel::getModel('Product');
		
		$products = $productModel->getProductListing($product_group, $max_items, $show_price, true, false, $filter_category, $category_id);
		$productModel->addImages($products);
		//$this->debug($productModel);
		$currency = CurrencyDisplay::getInstance( );
		
		if ($show_addtocart) {
			vmJsApi::jPrice();
			vmJsApi::cssSite();
		}
		
		//$this->debug($products); 
		$slides = array();
		
		foreach($products as $product){
			$slide = (object) array();
			//$this->debug($product->images);
			if(isset($product->images[0])) $slide->image = $product->images[0]->file_url;
			else if($default_image) $slide->image = $default_image;
			else continue;
			
			$slide->title = $product->product_name;
			$slide->description = $product->product_s_desc;
			if(empty($slide->description)) $slide->description = $product->product_desc;
			
			$slide->canonical = $slide->link = JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&virtuemart_product_id=' . $product->virtuemart_product_id . '&virtuemart_category_id=' . $product->virtuemart_category_id . $menu_item);
			$slide->id = $product->virtuemart_product_id . ':' . $product->slug;
			
			if($comments = $params->get('commnets',0)) {
				$host = str_replace(JURI::root(true), '', JURI::root());
				$host = preg_replace('/\/$/', '', $host);
				switch($comments) {
					case 1: // jcomments
						$slide->comments = array('id' => $item->virtuemart_product_id, 'group' => 'com_virtuemart');
						break;
					case 2: // disqus
						$disqus_shortname = $params->get('disqus_shortname','');
						if(!empty($disqus_shortname)) {
							$slide->comments = array();
							$slide->comments['url'] =  $host . $slide->link;
							$slide->comments['identifier'] = $disqus_shortname.'-virtuemart-'.$item->virtuemart_product_id; // ??
						}
						break;
					case 3: // facebook
						$slide->comments = $host . $slide->link;
						break;
					case 4: //komento
						// not implemented
						break;
				}
			}
			
			if ($show_price && isset($product->prices)) {
				$slide->extra = '<div class="vmproduct"><div class="product-price">'.$currency->createPriceDiv ('salesPrice', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
				if (!empty($product->prices['salesPriceWithDiscount'])) {
					$slide->extra .= $currency->createPriceDiv ('salesPriceWithDiscount', '', $product->prices, FALSE, FALSE, 1.0, TRUE);
				}
				$slide->extra .= '</div>';
			}
			
			if ($show_addtocart) {
				if(!isset($slide->extra)) $slide->extra = '<div class="vmproduct">';
				ob_start();
				echo $this->addtocart($product);
				$slide->extra .= ob_get_clean();
			}
			if(isset($slide->extra)) $slide->extra .= '</div>';
			
			$slides[] = $slide;
		}
		
		return $slides;		
	}
	
	/*
	 * Define any requirements here (such as specific extensions installed etc.)
	 * 
	 * Returns true if requirements are met or text message about not met requirement
	 */
	public function onCheckRequirements(&$source) {
		
		// Don't run this plugin when the source is different
		if ($source != $this->_name) {
			return null;
		}
		
		if(!JFile::exists(JPATH_ROOT.'/components/com_virtuemart/virtuemart.php')) return JText::_('PLG_DJMEDIATOOLS_VIRTUEMART_COMPONENT_DISABLED');
		jimport('joomla.application.component.helper');
		$com = JComponentHelper::getComponent('com_virtuemart', true);
		if(!$com->enabled) return JText::_('PLG_DJMEDIATOOLS_VIRTUEMART_COMPONENT_DISABLED');
		
		return true;		
	}
	
	private function addtocart($product) {
	
		if (!VmConfig::get ('use_as_catalog', 0)) {
			$stockhandle = VmConfig::get ('stockhandle', 'none');
			if (($stockhandle == 'disableit' or $stockhandle == 'disableadd') and ($product->product_in_stock - $product->product_ordered) < 1) {
				$button_lbl = JText::_ ('COM_VIRTUEMART_CART_NOTIFY');
				$button_cls = 'notify-button';
				$button_name = 'notifycustomer';
				?>
					<div style="display:inline-block;">
				<a href="<?php echo JRoute::_ ('index.php?option=com_virtuemart&view=productdetails&layout=notify&virtuemart_product_id=' . $product->virtuemart_product_id); ?>" class="notify"><?php echo JText::_ ('COM_VIRTUEMART_CART_NOTIFY') ?></a>
					</div>
				<?php
				} else {
					?>
				<div class="addtocart-area">
	
					<form method="post" class="product" action="index.php">
						<?php
						// Product custom_fields
						/*if (!empty($product->customfieldsCart)) {
							?>
							<div class="product-fields">
								<?php foreach ($product->customfieldsCart as $field) { ?>
	
								<div style="display:inline-block;" class="product-field product-field-type-<?php echo $field->field_type ?>">
									<span class="product-fields-title"><b><?php echo $field->custom_title ?></b></span>
									<?php echo JHTML::tooltip ($field->custom_tip, $field->custom_title, 'tooltip.png'); ?>
									<span class="product-field-display"><?php echo $field->display ?></span>
									<span class="product-field-desc"><?php echo $field->custom_field_desc ?></span>
								</div>
	
								<?php } ?>
							</div>
							<?php }*/ ?>
	
						<div class="addtocart-bar">
	
							<?php
							// Display the quantity box
							?>
							<!-- <label for="quantity<?php echo $product->virtuemart_product_id;?>" class="quantity_box"><?php echo JText::_ ('COM_VIRTUEMART_CART_QUANTITY'); ?>: </label> -->
				<span class="quantity-box">
				<input type="text" class="quantity-input" name="quantity[]" value="1"/>
				</span>
				<!-- span class="quantity-controls">
				<input type="button" class="quantity-controls quantity-plus"/>
				<input type="button" class="quantity-controls quantity-minus"/>
				</span-->
	
	
							<?php
							// Add the button
							//$button_lbl = JText::_ ('COM_VIRTUEMART_CART_ADD_TO');
							//$button_cls = ''; //$button_cls = 'addtocart_button';
	
	
							?>
							<?php // Display the add to cart button ?>
							<span class="addtocart-button">
								<?php echo shopFunctionsF::getAddToCartButton($product->orderable); ?>
							</span>
	
							<div class="clear"></div>
						</div>
	
						<input type="hidden" class="pname" value="<?php echo $product->product_name ?>"/>
						<input type="hidden" name="option" value="com_virtuemart"/>
						<input type="hidden" name="view" value="cart"/>
						<noscript><input type="hidden" name="task" value="add"/></noscript>
						<input type="hidden" name="virtuemart_product_id[]" value="<?php echo $product->virtuemart_product_id ?>"/>
						<input type="hidden" name="virtuemart_category_id[]" value="<?php echo $product->virtuemart_category_id ?>"/>
					</form>
					<div class="clear"></div>
				</div>
				<?php
				}
			}
	}
	
	function debug($data, $type = 'message') {
	
		$app = JFactory::getApplication();
		$app->enqueueMessage("<pre>".print_r($data, true)."</pre>", $type);
	
	}

}
