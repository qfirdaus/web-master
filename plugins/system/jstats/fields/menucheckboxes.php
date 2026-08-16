<?php
/*Plugin now for Joomla4 an 5 by https://jstats.de/
 *from Alexander Mueller - SEO NW ( new Script for J4! an J5!)
 *The GNU General Public License is a free, copyleft license for
 *software and other kinds of works.
 *@license https://www.gnu.org/copyleft/gpl.html GNU/GPL
 *
 * A plugin that allows you to add custom Head Code
 * *** Last update: 19.10, 2023 ***
*/
defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldMenuCheckboxes extends FormField
{
    protected $type = 'MenuCheckboxes';

    protected function getInput()
    {
        // Get all menu items
        $db    = Factory::getDbo();
        $query = $db->getQuery(true)
            ->select('id, title')
            ->from('#__menu')
            ->where('client_id = 0') // we want only frontend menu items
            ->where('published = 1') // get only published menu items
            ->order('lft');

        $db->setQuery($query);
        $menus = $db->loadObjectList();

        // Prepare options array
        $options = [];

        if ($menus) {
            foreach ($menus as $menu) {
                $options[] = HTMLHelper::_('select.option', $menu->id, $menu->title);
            }
        }

        // The 'multiple' attribute is added to allow selection of multiple menu items
        return HTMLHelper::_('select.genericlist', $options, $this->name . '[]', ['multiple' => true], 'value', 'text', $this->value, $this->id);
    }
}?>