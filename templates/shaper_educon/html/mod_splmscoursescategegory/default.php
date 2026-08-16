<?php

/**
 * @package     SP LMS
 * @subpackage  mod_splmscoursescategegory
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;
?>

<div class="mod-splms-course-categoies <?php echo $moduleclass_sfx; ?>">
    <div class="row">
        <ul class="row">
            <?php foreach ($items as $item) { ?>
                <div class="col-sm-6 col-md-4 text-center">
                    <li class="lms-single-category">
                        <a href="<?php echo $item->url; ?>">
                            <?php if (($params->get('show_icon')) && ($item->icon)) { ?>
                                <i class="fa fa-<?php echo $item->icon; ?>"></i>
                            <?php } ?>
                            <span>
                                <?php echo $item->title; ?>
                            </span>
                        </a>
                    </li>
                </div>
            <?php } ?>
        </ul>
    </div>
</div>