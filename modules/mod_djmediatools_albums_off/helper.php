<?php
/**
 * @version $Id$
 * @package DJ-MediaTools
 * @copyright Copyright (C) 2017 DJ-Extensions.com, All rights reserved.
 * @license http://www.gnu.org/licenses GNU/GPL
 * @author url: http://dj-extensions.com
 * @author email contact@dj-extensions.com
 * @developer Mateusz Maciejewski - mateusz.maciejewski@indicoweb.com
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


class moddjmediatoolsalbumsHelper {
    static $params = null;


    public static function getParams($reload = false) {

        if (!self::$params || $reload == true) {
            $app		= JFactory::getApplication();

            // our params
            $params = new Registry();

            // component's global params
            $cparams = JComponentHelper::getParams( 'com_djmediatools' );

            // current params - all
            $aparams = $app->getParams();

            // curent params - djc2 only
            $mparams = $app->getParams('com_djmediatools');

            // first let's use all current params
            $params->merge($aparams);

            // then override them with djc2 global settings - in case some other extension share's the same parameter name
            $params->merge($cparams);

            if ($app->input->getCmd('option') == 'com_djmediatools') {
                // finally, override settings with current params, but only related to djc2.
                $params->merge($mparams);
            }

            self::$params = $params;
        }
        return self::$params;

    }

    public static function incViewsAjax(){
        $input = JFactory::getApplication()->input;
        $item_id = $input->get('item_id');

        if(!$item_id)
            return false;

        return self::incItemViews($item_id);
    }

    public static function incClicksAjax(){
        $input = JFactory::getApplication()->input;
        $item_id = $input->get('item_id');

        if(!$item_id)
            return false;

        return self::incItemClicks($item_id);
    }

    /* Incrementation viewed items */
    private static function incItemViews($id) {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        // Fields to update.
        $fields = array(
            $db->quoteName('views') . ' = views +1'
        );

        $conditions = array(
            $db->quoteName('id') . ' = ' . $id
        );

        $query->update($db->quoteName('#__djmt_items'))->set($fields)->where($conditions);

        $db->setQuery($query);

        return $db->execute();
    }

    /* Incrementation viewed items */
    private static function incItemClicks($id) {
        $db = JFactory::getDbo();

        $query = $db->getQuery(true);

        // Fields to update.
        $fields = array(
            $db->quoteName('clicks') . ' = clicks +1'
        );

        $conditions = array(
            $db->quoteName('id') . ' = ' . $id
        );

        $query->update($db->quoteName('#__djmt_items'))->set($fields)->where($conditions);

        $db->setQuery($query);

        return $db->execute();
    }
}