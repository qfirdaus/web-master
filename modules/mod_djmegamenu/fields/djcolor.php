<?php
/**
 * @package DJ-Megamenu
 * @copyright Copyright (C) DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Szymon Woronowski, Artur Kaczmarek
 *
 */

defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

/**
 * Color Form Field class for the Joomla Platform.
 * This implementation is designed to be compatible with HTML5's `<input type="color">`
 *
 * @link   http://www.w3.org/TR/html-markup/input.color.html
 * @since  11.3
 */
class JFormFieldDJColor extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.3
	 */
	protected $type = 'DJColor';

	/**
	 * The control.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $control = 'hue';

	/**
	 * The format.
	 *
	 * @var    string
	 * @since  3.6.0
	 */
	protected $format = 'hex';

	/**
	 * The keywords (transparent,initial,inherit).
	 *
	 * @var    string
	 * @since  3.6.0
	 */
	protected $keywords = '';

	/**
	 * The position.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $position = 'default';

	/**
	 * The colors.
	 *
	 * @var    mixed
	 * @since  3.2
	 */
	protected $colors;

	/**
	 * The split.
	 *
	 * @var    integer
	 * @since  3.2
	 */
	protected $split = 3;

	/**
	 * Method to get certain otherwise inaccessible properties from the form field object.
	 *
	 * @param   string  $name  The property name for which to the the value.
	 *
	 * @return  mixed  The property value or null.
	 *
	 * @since   3.2
	 */
	public function __get($name)
	{
		switch ($name)
		{
			case 'control':
			case 'format':
			case 'keywords':
			case 'exclude':
			case 'colors':
			case 'split':
				return $this->$name;
		}

		return parent::__get($name);
	}

	/**
	 * Method to set certain otherwise inaccessible properties of the form field object.
	 *
	 * @param   string  $name   The property name for which to the the value.
	 * @param   mixed   $value  The value of the property.
	 *
	 * @return  void
	 *
	 * @since   3.2
	 */
	public function __set($name, $value)
	{
		switch ($name)
		{
			case 'split':
				$value = (int) $value;
			case 'control':
			case 'format':
				$this->$name = (string) $value;
				break;
			case 'keywords':
				$this->$name = (string) $value;
				break;
			case 'exclude':
			case 'colors':
				$this->$name = (string) $value;
				break;

			default:
				parent::__set($name, $value);
		}
	}

	/**
	 * Method to attach a JForm object to the field.
	 *
	 * @param   SimpleXMLElement  $element  The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param   mixed             $value    The form field value to validate.
	 * @param   string            $group    The field name group control value. This acts as as an array container for the field.
	 *                                      For example if the field has name="foo" and the group value is set to "bar" then the
	 *                                      full field name would end up being "bar[foo]".
	 *
	 * @return  boolean  True on success.
	 *
	 * @see     JFormField::setup()
	 * @since   3.2
	 */
	public function setup(SimpleXMLElement $element, $value, $group = null)
	{
		$return = parent::setup($element, $value, $group);

		if ($return)
		{
			$this->control  = isset($this->element['control']) ? (string) $this->element['control'] : 'hue';
			$this->format   = isset($this->element['format']) ? (string) $this->element['format'] : 'hex';
			$this->keywords = isset($this->element['keywords']) ? (string) $this->element['keywords'] : '';
			$this->position = isset($this->element['position']) ? (string) $this->element['position'] : 'default';
			$this->colors   = (string) $this->element['colors'];
			$this->split    = isset($this->element['split']) ? (int) $this->element['split'] : 3;
		}

		return $return;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.3
	 */
	protected function getInput()
	{
		$lang = Factory::getApplication()->getLanguage();;

		// Translate placeholder text
		$hint = $this->translateHint ? Text::_($this->hint) : $this->hint;

		// Control value can be: hue (default), saturation, brightness, wheel or simple
		$control = $this->control;

		// Position of the panel can be: right (default), left, top or bottom (default RTL is left)
		$position = ' data-position="' . (($lang->isRTL() && $this->position == 'default') ? 'left' : $this->position) . '"';

		// Validation of data can be: color (hex color value). Keep for B/C (minicolors.js already auto-validates color)
		$validate = $this->validate ? ' data-validate="' . $this->validate . '"' : '';

		$onchange  = !empty($this->onchange) ? ' onchange="' . $this->onchange . '"' : '';
		$class     = $this->class;
		$required  = $this->required ? ' required aria-required="true"' : '';
		$disabled  = $this->disabled ? ' disabled' : '';
		$autofocus = $this->autofocus ? ' autofocus' : '';

		$color = strtolower($this->value);
		$color = ! $color ? '' : $color;

		if (in_array($this->format, array('rgb', 'rgba')) && $this->validate != 'color')
		{
			$alpha = ($this->format == 'rgba') ? true : false;
			$placeholder = $alpha ? 'rgba(0, 0, 0, 0.5)' : 'rgb(0, 0, 0)';
		}
		else
		{
			$placeholder = '#rrggbb';
		}

		$inputclass   = ($this->keywords && ! in_array($this->format, array('rgb', 'rgba'))) ? ' keywords' : ' ' . $this->format;
		$class        = ' class="' . trim('minicolors ' . $class) . ($this->validate == 'color' ? '' : $inputclass) . '"';
		$control      = $control ? ' data-control="' . $control . '"' : '';
		$format       = $this->format ? ' data-format="' . $this->format . '"' : '';
		$keywords     = $this->keywords ? ' data-keywords="' . $this->keywords . '"' : '';
		$readonly     = $this->readonly ? ' readonly' : '';
		$hint         = strlen($hint) ? ' placeholder="' . $hint . '"' : ' placeholder="' . $placeholder . '"';
		$autocomplete = ! $this->autocomplete ? ' autocomplete="off"' : '';

		// Force LTR input value in RTL, due to display issues with rgba/hex colors
		$direction    = $lang->isRTL() ? ' dir="ltr" style="text-align:right"' : '';

		// Including fallback code for HTML5 non supported browsers.
		HTMLHelper::_('jquery.framework');
		HTMLHelper::_('script', 'system/html5fallback.js', false, true);

		/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
		$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
		$wa->usePreset('minicolors')
			->useScript('field.color-adv');

		return '<input type="text" name="' . $this->name . '" id="' . $this->id . '"' . ' value="'
			. htmlspecialchars($color, ENT_COMPAT, 'UTF-8') . '"' . $hint . $class . $position . $control
			. $readonly . $disabled . $required . $onchange . $autocomplete . $autofocus
			. $format . $keywords . $direction . $validate . '/>';
	}
}
