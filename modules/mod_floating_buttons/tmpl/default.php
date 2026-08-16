<?php
/**
 * @copyright	Copyright © 2020 - All rights reserved.
* @license    GNU General Public License version 2 or later; see LICENSE.txt
 * @author	https://templateplazza.com
 */
defined('_JEXEC') or die;

if($main_item_icon_custom) {
    $main_item_icon = "<img class='main_item_icon_custom' src='".JURI::base(true)."/".$main_item_icon_custom."' alt=''/>";
}else{
    $main_item_icon = $main_item_icon;
}
?>
<div class="fab-container-<?php echo $fabposition; ?> <?php echo $tooltip_class.$visibility_class; ?>">
    <button id="fab<?php echo $module->id; ?>" class="fab-button" style="background-color:<?php echo $main_item_bgcolor; ?>;color:<?php echo $main_item_color; ?>;"><?php echo $main_item_icon; ?></button>
    <ul id="options<?php echo $module->id; ?>" class="options-list">
        <?php 
        foreach ($child_item_items as $item) : 
            if(in_array($item->access, $levels)) : 
                $child_item_icon_custom = $item->child_item_icon_custom;
                if($child_item_icon_custom){
                    $child_item_icon = "<img class='child_item_icon_custom' src='".JURI::base(true)."/".$item->child_item_icon_custom."' alt=''/>";
                }else{
                    $child_item_icon = $item->child_item_icon;
                }
                ?>
                <li>
                    <a href="<?php echo $item->child_item_url; ?>" <?php if ($item->child_item_url_target == '1') { echo 'target=_blank'; } ?> style="background:<?php echo $item->child_item_bgcolor; ?>;color:<?php echo $item->child_item_color; ?>">
                        <span class="<?php echo $tooltip_class;?>"><?php echo $item->child_item_title; ?></span>
                        <?php echo $child_item_icon; ?>
                    </a>
                </li>
            <?php 
            endif; 
        endforeach; ?>
    </ul>
</div>
<script>
const fabButton<?php echo $module->id; ?> = document.getElementById("fab<?php echo $module->id; ?>");
const optionsList<?php echo $module->id; ?> = document.getElementById("options<?php echo $module->id; ?>");
const fabContainer<?php echo $module->id; ?> = document.querySelector(".fab-container");

let isFabOpen<?php echo $module->id; ?> = false;

fabButton<?php echo $module->id; ?>.addEventListener("click", () => {
    if (isFabOpen<?php echo $module->id; ?>) {
        // Close options
        optionsList<?php echo $module->id; ?>.classList.remove("active");

        // Add event listener to detect when the animation is finished
        optionsList<?php echo $module->id; ?>.addEventListener("animationend", () => {
            optionsList<?php echo $module->id; ?>.style.display = "none";
        }, { once: true });
    } else {
        // Open options
        optionsList<?php echo $module->id; ?>.style.display = "block";
        optionsList<?php echo $module->id; ?>.classList.add("active");
    }

    isFabOpen<?php echo $module->id; ?> = !isFabOpen<?php echo $module->id; ?>;
});
</script>
