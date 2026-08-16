<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Content.adminexile
 *
 * @copyright   (C) 2018 Michael Richey. <https://www.richeyweb.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Adminexile\Field;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * Javascript Field class for the Adminexile Plugin.
 *
 * @since  3.9.1
 */
class JavascriptField extends FormField
{
    /**
     * The form field type.
     *
     * @var    string
     * @since  3.9.1
     */
    protected $type = 'Javascript';

    /**
     * Method to get the field label markup.
     *
     * @return  string  The field label markup.
     *
     * @since   3.9.1
     */
    protected function getLabel()
    {
        return;
    }

    /**
     * Method to get the field input markup.
     *
     * @return  string  The field input markup.
     *
     * @since   4.0.0
     */
    protected function getInput()
    {
        $document = Factory::getDocument();
        $document->addScript(Uri::root().'/media/plg_system_adminexile/js/admin.js');
    }
}
