<?php
/**
 * @package		Joomla.Site
 * @subpackage	mod_search
 * @copyright	Copyright (C) 2005 - 2022 Open Source Matters, Inc. All rights reserved.
 * @license		GNU General Public License version 2 or later; see LICENSE.txt
 */
// no direct access
defined('_JEXEC') or die;

use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Component\Finder\Site\Helper\RouteHelper;
use Joomla\Module\Finder\Site\Helper\FinderHelper;

?>
<div class="top-search-wrapper">
    <div class="icon-top-wrapper">
        <i class="fa fa-search search-open-icon" aria-hidden="true"></i>
        <i class="fa fa-times search-close-icon" aria-hidden="true"></i>
    </div>
</div> <!-- /.top-search-wrapper -->
<div class="top-search-input-wrap">
    <div class="top-search-overlay"></div>
    <?php if(JVERSION > 4){
        $route = RouteHelper::getSearchRoute($params->get('searchfilter', null));
        // Load the smart search component language file.
        $lang = $app->getLanguage();
        $lang->load('com_finder', JPATH_SITE);

        $input = '<input type="text" name="q" id="mod-finder-searchword' . $module->id . '" class="js-finder-search-query form-control" value="' . htmlspecialchars($app->input->get('q', '', 'string'), ENT_COMPAT, 'UTF-8') . '"'
            . ' placeholder="' . Text::_('MOD_FINDER_SEARCH_VALUE') . '">';

        $showLabel  = $params->get('show_label', 1);
        $labelClass = (!$showLabel ? 'visually-hidden ' : '') . 'finder';
        $label      = '<label for="mod-finder-searchword' . $module->id . '" class="' . $labelClass . '">' . $params->get('alt_label', Text::_('JSEARCH_FILTER_SUBMIT')) . '</label>';

        $output = '';

        if ($params->get('show_button', 0))
        {
            $output .= $label;
            $output .= '<div class="mod-finder__search input-group">';
            $output .= $input;
            $output .= '<button class="btn btn-primary" type="submit"><span class="icon-search icon-white" aria-hidden="true"></span> ' . Text::_('JSEARCH_FILTER_SUBMIT') . '</button>';
            $output .= '</div>';
        }
        else
        {
            $output .= $label;
            $output .= $input;
        }

        Text::script('MOD_FINDER_SEARCH_VALUE', true);

        /** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
        $wa = $app->getDocument()->getWebAssetManager();
        $wa->getRegistry()->addExtensionRegistryFile('com_finder');

        /*
        * This segment of code sets up the autocompleter.
        */
        if ($params->get('show_autosuggest', 1))
        {
            $wa->usePreset('awesomplete');
            $app->getDocument()->addScriptOptions('finder-search', array('url' => Route::_('index.php?option=com_finder&task=suggestions.suggest&format=json&tmpl=component')));
        }

        $wa->useScript('com_finder.finder');

        ?>

        <form class="mod-finder js-finder-searchform form-search" action="<?php echo Route::_($route); ?>" method="get" role="search">
            <div class="search-wrap">
                <div class="search <?php echo $moduleclass_sfx ?>">
                    <?php echo $output; ?>
        
                    <?php $show_advanced = $params->get('show_advanced', 0); ?>
                    <?php if ($show_advanced == 2) : ?>
                        <br>
                        <a href="<?php echo Route::_($route); ?>" class="mod-finder__advanced-link"><?php echo Text::_('COM_FINDER_ADVANCED_SEARCH'); ?></a>
                    <?php elseif ($show_advanced == 1) : ?>
                        <div class="mod-finder__advanced js-finder-advanced">
                            <?php echo HTMLHelper::_('filter.select', $query, $params); ?>
                        </div>
                    <?php endif; ?>
                    <?php echo FinderHelper::getGetFields($route, (int) $params->get('set_itemid', 0)); ?>
                </div>
            </div>
        </form>
    <?php } else {?>
        <form action="<?php echo JRoute::_('index.php'); ?>" method="post">
            <div class="search-wrap">
                <div class="search <?php echo $moduleclass_sfx ?>">
                    <?php
                    $output = '<div class="sp_search_input"><input name="searchword" maxlength="' . $maxlength . '"  class="mod-search-searchword inputbox' . $moduleclass_sfx . '" type="text" size="' . $width . '" value="' . $text . '"  onblur="if (this.value==\'\') this.value=\'' . $text . '\';" onfocus="if (this.value==\'' . $text . '\') this.value=\'\';" /></div>';

                    if ($button) :
                        if ($imagebutton) :
                            $button = '<input type="image" value="' . $button_text . '" class="button' . $moduleclass_sfx . '" src="' . $img . '" onclick="this.form.searchword.focus();"/>';
                        else :
                            $button = '<input type="submit" value="' . $button_text . '" class="button' . $moduleclass_sfx . '" onclick="this.form.searchword.focus();"/>';
                        endif;
                    endif;

                    switch ($button_pos) :
                        case 'top' :
                            $button = $button . '<br />';
                            $output = $button;
                            break;

                        case 'bottom' :
                            $button = '<br />' . $button;
                            $output = $output;
                            break;

                        case 'right' :
                            $output = $output;
                            break;

                        case 'left' :
                        default :
                            $output = $button;
                            break;
                    endswitch;

                    echo $output;
                    ?>
                    <input type="hidden" name="task" value="search" />
                    <input type="hidden" name="option" value="com_search" />
                    <input type="hidden" name="Itemid" value="<?php echo $mitemid; ?>" />
                </div>
            </div>
        </form>
    <?php }?>
</div> <!-- /.top-search-input-wrap -->