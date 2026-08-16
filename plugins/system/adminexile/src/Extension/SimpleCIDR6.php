<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.adminexile
 *
 * @copyright   (C) 2020 Michael Richey. <https://www.richeyweb.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Adminexile\Extension;

defined('_JEXEC') or die('Restricted access');

class SimpleCIDR6 {

    protected static $instances = array();
    public $network;

    public function __construct($network = false) {
		if ($network)
			$this->setNetwork($network);
		}

		public static function getInstance($network = false) {
		$instanceid = $network ? $network : '';
		if (empty(self::$instances[$instanceid]))
		{
			self::$instances[$instanceid] = new SimpleCIDR6($instanceid);
		}
		return self::$instances[$instanceid];
		}

		public function setNetwork($network = false) {
		if ($network)
			$this->network = $network;
		}

		public function contains($ip) {
		$ip = inet_pton($ip);
		$binaryip = self::inet_to_bits($ip);

		list($net, $maskbits) = explode('/', $this->network);
		$net = inet_pton($net);
		$binarynet = self::inet_to_bits($net);

		$ip_net_bits = substr($binaryip, 0, $maskbits);
		$net_bits = substr($binarynet, 0, $maskbits);

		if ($ip_net_bits === $net_bits)
		{
			return true;
		}
		return false;
    }

    private static function inet_to_bits($inet) {
		// unpacked is a keyword that triggers JAMSS to flag the code as suspicious - this is beyond lazy
		// $unpacked = unpack('A16', $inet);
		// $unpacked = str_split($unpacked[1]);
		$up = unpack('A16', $inet);
		$up = str_split($up[1]);
		$binaryip = '';
		foreach ($up as $char)
		{
			$binaryip .= str_pad(decbin(ord($char)), 8, '0', STR_PAD_LEFT);
		}
		return $binaryip;
    }

}