<?php
/**
 * @file
 * ExtraWatch - Real-time Visitor Analytics and Stats
 * @package ExtraWatch
 * @version 5.0
 * @revision 1
 * @license http://www.gnu.org/licenses/gpl-3.0.txt     GNU General Public License v3
 * @copyright (C) 2024 by CodeGravity.com - All rights reserved!
 * @website http://www.extrawatch.com
 */

defined('_JEXEC') or die('Restricted access');  	 	    	    		  	 	  	 	  		 	 		    	 			 	   		  	 	 		 	 	   	      	  	 		 		 				 			 		  		    	 		 		  

jimport('joomla.filesystem.folder');  	 	    	    		  	 	  	 	  		 	 		    	 			 	   		  	 	 		 	 	   	      	  	 		 		 				 			 		  		    	 		 		  
jimport('joomla.filesystem.file');  	 	    	    		  	 	  	 	  		 	 		    	 			 	   		  	 	 		 	 	   	      	  	 		 		 				 			 		  		    	 		 		  

ini_set('display_errors', TRUE);   	 	    	    		  	 	  	 	  		 	 		    	 			 	   		  	 	 		 	 	   	      	  	 		 		 				 			 		  		    	 		 		  
error_reporting(E_ALL & ~(E_STRICT|E_NOTICE));  	 	    	    		  	 	  	 	  		 	 		    	 			 	   		  	 	 		 	 	   	      	  	 		 		 				 			 		  		    	 		 		  

class com_extrawatchInstallerScript {

    const EW_TABLE_TMP_SETTINGS = "#__extrawatch_settings";

    function postflight($action, $installer) {
    }

    function uninstall($parent) {
        $this->dropTables();
    }

    private function dropTables() {
        $db = JFactory::getDbo();

        $db->dropTable(self::EW_TABLE_TMP_SETTINGS, true);
    }


}
