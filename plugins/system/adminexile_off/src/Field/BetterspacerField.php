<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.sessionkeeper
 *
 * @copyright   (C) 2018 Michael Richey. <https://www.richeyweb.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\AdminExile\Field;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Betterspacer Field class for the AdminExile Plugin.
 *
 * @since  3.9.1
 */
class BetterspacerField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.9.1
     */
    protected $type = 'Betterspacer';
    protected $hiddenDescription = true;

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   4.0.0
     */
    protected function getInput()
    {
        return Text::_($this->element['description']);
    }
}
