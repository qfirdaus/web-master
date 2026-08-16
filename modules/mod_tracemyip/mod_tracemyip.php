<?php
/**
# 'TraceMyIP for Joomla' module for Joomla v3.9
#
# @version		V1.0.2
# @author		Trace My IP <relations@tracemyip.org>
# @link			http://www.tracemyip.org
# @copyright	Copyright (C) 2024 TraceMyIP.org. All rights reserved.
# @license		GNU/GPL
# 
# TraceMyIP for Joomla is free software: you can redistribute it and/or
# modify it under the terms of the GNU General Public License as published
# by the Free Software Foundation, either version 3 of the License, or
# (at your option) any later version.
# 
# TraceMyIP for Joomla is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
# 
# You should have received a copy of the GNU General Public License
# along with TraceMyIP for Joomla.  If not, see <http://www.gnu.org/licenses/>.
**/

// no direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Helper\ModuleHelper;

$tracemyipTrackerCode	= $params->get('tracemyipTrackerCode', '');

if ($tracemyipTrackerCode != '')
{
    require ModuleHelper::getLayoutPath('mod_tracemyip', 'tracemyip');
}
?>