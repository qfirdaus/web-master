<?php

/**
 * @package     SP LMS
 * @subpackage  mod_splmscoursesearch
 *
 * @copyright   Copyright (c) 2010 - 2025 JoomShaper. All rights reserved.
 * @license     GNU General Public License version 2 or later.
 */
defined('_JEXEC') or die;
?>


<div class="row justify-content-center">
    <div class="mod-splms-course-search col-md-offset-2 col-sm-12 col-md-8 <?php echo $moduleclass_sfx; ?>">
        <div class="course-search-inner">
            <input type="text" class="splms-coursesearch-input" placeholder="<?php echo $params->get('placeholder'); ?>">
            <span class="splms-course-search-icons"><i class="splms-icon-search"></i></span>
        </div>
        <div class="splms-course-search-results"></div>
    </div>
</div>