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
$options = $item->options;
$product_id = $item->j2store_product_id;
$product_helper = J2Store::product();

?>
<?php if ($options) { ?>

<div class="options">
        <?php foreach ($options as $option) { ?>
        <?php //var_dump($option); ?>
        <?php if ($option['type'] == 'select') { ?>
        <!-- select -->
	<div id="dj-option-<?php echo $option['productoption_id']; ?>"
		class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <b><?php echo $option['option_name']; ?>:</b><br /> <select
			name="product_option[<?php echo $option['productoption_id']; ?>]"
			onChange="doAjaxPrice(<?php echo $product_id?>,
          						'#dj-option-<?php echo $option["productoption_id"]; ?>'
          						);">
			<option value=""><?php echo JText::_('J2STORE_ADDTOCART_SELECT'); ?></option>
            <?php foreach ($option['optionvalue'] as $option_value) { ?>
            	<?php $checked = ''; if($option_value['product_optionvalue_default']) $checked = 'selected="selected"'; ?>

            <option <?php echo $checked; ?>
				value="<?php echo $option_value['product_optionvalue_id']; ?>"><?php echo stripslashes(JFactory::getDbo()->escape(JText::_($option_value['optionvalue_name']))); ?>
            <?php if ($option_value['product_optionvalue_price'] > 0 && $params->get('product_option_price', 1)) { ?>
            (
            <?php if($params->get('product_option_price_prefix', 1)): ?>
            	<?php echo $option_value['product_optionvalue_prefix']; ?>
            <?php endif; ?>
            <?php  echo $product_helper->displayPrice($option_value['product_optionvalue_price'], $item, $params); ?>
            )
            <?php } ?>
            </option>
            <?php } ?>
          </select>
	</div>
	<br />
        <?php } ?>

        <?php if ($option['type'] == 'radio') { ?>
          <!-- radio -->
	<div id="dj-option-<?php echo $option['productoption_id']; ?>"
		class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <b><?php echo $option['option_name']; ?>:</b><br />
          <?php foreach ($option['optionvalue'] as $option_value) { ?>
          	<?php $checked = ''; if($option_value['product_optionvalue_default']) $checked = 'checked="checked"'; ?>
          <input <?php echo $checked; ?> type="radio"
			name="product_option[<?php echo $option['productoption_id']; ?>]"
			value="<?php echo $option_value['product_optionvalue_id']; ?>"
			id="option-value-<?php echo $option_value['product_optionvalue_id']; ?>"
			onChange="doAjaxPrice(
          						<?php echo $product_id; ?>,
          						'#dj-option-<?php echo $option["productoption_id"]; ?>'
          						);" />

          <?php if(
          			$params->get('image_for_product_options', 0) &&
          			  isset($option_value['optionvalue_image']) &&
          			!empty($option_value['optionvalue_image'])
				):
          ?>
				<img
			class="optionvalue-image-<?php echo $option_value['product_optionvalue_id']; ?>"
			src="<?php echo JUri::root().$option_value['optionvalue_image']; ?>" />
          <?php endif; ?>
          <label
			for="option-value-<?php echo $option_value['product_optionvalue_id']; ?>"><?php echo stripslashes(JFactory::getDbo()->escape(JText::_($option_value['optionvalue_name']))); ?>
            <?php if ($option_value['product_optionvalue_price'] > 0 && $params->get('product_option_price', 1)) { ?>
	         	(
	         	<?php if($params->get('product_option_price_prefix', 1)): ?>
            		<?php echo $option_value['product_optionvalue_prefix']; ?>
            	<?php endif; ?>
            	<?php  echo $product_helper->displayPrice($option_value['product_optionvalue_price'], $item, $params); ?>
            	)

            <?php } ?>
          </label> <br />
          <?php } ?>
        </div>
	<br />
        <?php } ?>

        <?php if ($option['type'] == 'checkbox') { ?>
          <!-- checkbox-->

	<div id="dj-option-<?php echo $option['productoption_id']; ?>"
		class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <b><?php echo $option['option_name']; ?>:</b><br />
          <?php foreach ($option['optionvalue'] as $option_value) { ?>
          <input type="checkbox"
			name="product_option[<?php echo $option['productoption_id']; ?>][]"
			value="<?php echo $option_value['product_optionvalue_id']; ?>"
			id="option-value-<?php echo $option_value['product_optionvalue_id']; ?>" />
		<label
			for="option-value-<?php echo $option_value['product_optionvalue_id']; ?>"><?php echo stripslashes($this->escape(JText::_($option_value['optionvalue_name']))); ?>
            <?php if ($option_value['product_optionvalue_price'] > 0 && $params->get('product_option_price', 1)) { ?>
               (
               <?php if($params->get('product_option_price_prefix', 1)): ?>
            		<?php echo $option_value['product_optionvalue_prefix']; ?>
            	<?php endif; ?>
            	<?php  echo $product_helper->displayPrice($option_value['product_optionvalue_price'], $item, $params); ?>
            	)
            	<?php } ?>
          </label> <br />
          <?php } ?>
        </div>
	<br />

		<script type="text/javascript">

			(function($) {
				var po_id = '<?php echo $option['productoption_id']; ?>';
				$('#option-'+po_id+' input:checkbox').bind("click",function(){
				    var product_id = '<?php echo $product_id?>';
					doAjaxPrice(product_id, '#dj-option-'+po_id+' input:checkbox');
				});
			})(j2store.jQuery);
		</script>

        <?php } ?>


        <?php if ($option['type'] == 'text') { ?>
         <!-- text -->
	<div id="dj-option-<?php echo $option['productoption_id']; ?>"
		class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <b><?php echo $option['option_name']; ?>:</b><br /> <input
			type="text"
			name="product_option[<?php echo $option['productoption_id']; ?>]"
			value="<?php echo $option['optionvalue']; ?>" />
	</div>
	<br />
        <?php } ?>


        <?php if ($option['type'] == 'textarea') { ?>
         <!-- textarea -->
	<div id="dj-option-<?php echo $option['productoption_id']; ?>"
		class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <b><?php echo $option['option_name']; ?>:</b><br />
		<textarea
			name="product_option[<?php echo $option['productoption_id']; ?>]"
			cols="40" rows="5"><?php echo $option['optionvalue']; ?></textarea>
	</div>
	<br />
        <?php } ?>

          <?php if ($option['type'] == 'file') { ?>
         <!-- File -->
	<div id="dj-option-<?php echo $option['productoption_id']; ?>"
		class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <b><?php echo $option['option_name']; ?>:</b><br />
		<button type="button"
			id="product-option-<?php echo $option['productoption_id']; ?>"
			data-loading-text="<?php echo JText::_('J2STORE_LOADING')?>"
			class="btn btn-default">
			<i class="fa fa-upload"></i> <?php echo JText::_('J2STORE_PRODUCT_OPTION_CHOOSE_FILE')?></button>
		<input type="hidden"
			name="product_option[<?php echo $option['productoption_id']; ?>]"
			value="" id="input-option<?php echo $option['productoption_id']; ?>" />

	</div>
	<br />

        <?php } ?>


        <?php if ($option['type'] == 'date') { ?>
          <!-- date -->
	<div id="dj-option-<?php echo $option['productoption_id']; ?>"
		class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <b><?php echo $option['option_name']; ?>:</b><br /> <input
			type="text"
			name="product_option[<?php echo $option['productoption_id']; ?>]"
			value="<?php echo $option['optionvalue']; ?>" class="j2store_date" />
	</div>
	<br />
        <?php } ?>


        <?php if ($option['type'] == 'datetime') { ?>
         <!-- datetime -->
	<div id="dj-option-<?php echo $option['productoption_id']; ?>"
		class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <b><?php echo $option['option_name']; ?>:</b><br /> <input
			type="text"
			name="product_option[<?php echo $option['productoption_id']; ?>]"
			value="<?php echo $option['optionvalue']; ?>"
			class="j2store_datetime" />
	</div>
	<br />
        <?php } ?>

        <?php if ($option['type'] == 'time') { ?>
        <!-- time -->
	<div id="dj-option-<?php echo $option['productoption_id']; ?>"
		class="option">
          <?php if ($option['required']) { ?>
          <span class="required">*</span>
          <?php } ?>
          <b><?php echo $option['option_name']; ?>:</b><br /> <input
			type="text"
			name="product_option[<?php echo $option['productoption_id']; ?>]"
			value="<?php echo $option['optionvalue']; ?>" class="j2store_time" />
	</div>
	<br />
        <?php } ?>
        <?php } ?>
      </div>
<?php } ?>

<?php if(isset($options) && !empty($options)): ?>

<?php foreach ($options as $option) : ?>
<?php if ($option['type'] == 'file'):  ?>
<script type="text/javascript">
(function($){
$('#product-option-<?php echo $option['productoption_id']; ?>').on('click', function() {
	var node = this;
	$('#form-upload').remove();
	$('body').prepend('<form enctype="multipart/form-data" id="form-upload" style="display: none;"><input type="file" name="file" /></form>');
	$('#form-upload input[name=\'file\']').trigger('click');
	timer = setInterval(function() {
		if ($('#form-upload input[name=\'file\']').val() != '') {
			clearInterval(timer);
			$.ajax({
				url: 'index.php?option=com_j2store&view=carts&task=upload&product_id='+<?php echo $item->j2store_product_id;?>,
				type: 'post',
				dataType: 'json',
				data: new FormData($('#form-upload')[0]),
				cache: false,
				contentType: false,
				processData: false,
				beforeSend: function() {
					$(node).button('loading');
				},
				complete: function() {
					$(node).button('reset');
				},
				success: function(json) {
					$('.text-danger, .text-success').remove();

					if (json['error']) {
						$(node).parent().find('input').after('<span class="text-danger">' + json['error'] + '</span>');
					}

					if (json['success']) {
						$(node).parent().find('input').after('<span class="text-success">' + json['success'] + ' </span>');
						$(node).parent().find('input').attr('value', json['code']);
					}
				},
				error: function(xhr, ajaxOptions, thrownError) {
					alert(thrownError + "\r\n" + xhr.statusText + "\r\n" + xhr.responseText);
				}
			});
		}
	}, 500);
});
})(j2store.jQuery);
</script>
<?php endif; ?>
<?php endforeach; ?>
<?php endif; ?>