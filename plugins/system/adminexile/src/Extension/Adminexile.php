<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  System.adminexile
 *
 * @copyright   (C) 2020 Michael Richey. <https://www.richeyweb.com>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Joomla\Plugin\System\Adminexile\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Event\SubscriberInterface;
use Joomla\Event\DispatcherInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Application\CMSWebApplicationInterface;
use Joomla\CMS\Application\ApiApplication;
// use Joomla\Plugin\Console\TORNodes\Extension\Test as TORTest;

// phpcs:disable PSR1.Files.SideEffects
\defined('_JEXEC') or die;
// phpcs:enable PSR1.Files.SideEffects

/**
 * System plugin to add additional security features to the administrator interface.
 *
 * @since  5.0.0
 */
final class Adminexile extends CMSPlugin
{

    protected $app;
    private $_pass = false;
    private $_key = '';
    private $_ip = '';
    private $_ipv = '';
    private $_faillog = '';
    private $_debug = 0;

    /**
     * Returns an array of events this subscriber will listen to.
     *
     * @return  array
     *
     * @since   4.0.0
     */
    public static function getSubscribedEvents(): array
    {
        return ['onAfterInitialise' => 'onAfterInitialise'];
    }

    public function __construct(
        DispatcherInterface $dispatcher,
        array $config
    ) {
        parent::__construct($dispatcher, $config);
        $this->app = Factory::getApplication();
        $this->_key = $this->params->get('key', 'adminexile', 'raw');
        $this->_ip = $this->_getIP();
		// $this->_ipv = preg_match_all('/:/', $this->_ip) ? 'SimpleCIDR6' : 'SimpleCIDR';
        strstr($this->_ip,':' ) ? $this->_ipv = 'SimpleCIDR6' : $this->_ipv = 'SimpleCIDR';
        $this->_debug = Factory::getApplication()->get('debug', 0);
        $this->_faillog = $this->params->get('faillog', 0);
        $this->_pass = $this->_checkPass();
    }

	public function onAfterInitialise() {
        if (!$this->app instanceof CMSWebApplicationInterface) {
            return false;
        }
        if($this->app instanceof ApiApplication) {
            return false;
        }
        if ($this->_pass) {
            $this->_debugMsg('pass');
            return;
        }
        $enableip = (bool)$this->params->get('enableip',0);
        if($enableip && $this->_whitelisted()) {
            $this->_debugMsg('whitelisted');
            $this->_authorize();
            return;
        }
        if($enableip && $this->_blacklisted()) {
            $this->_debugMsg('blacklisted');
            $this->_fail('onAfterInitialise1');
            return;
        }
        if($enableip && (bool)$this->params->get('tornodes',0)){
            $this->_debugMsg('tornodes');
            if($this->_tornode() === true) {
                $this->_debugMsg('TOR: '.$this->_ip);
                $this->_faillogMsg('TOR: '.$this->_ip);
                $this->_fail('onAfterInitialise2');
                return;
            }
        }
        if ($this->_keyauth()) {
            $this->_debugMsg('keyauth success');
            $this->_authorize();
        } else {
            $this->_debugMsg('keyauth fail');
            $this->_faillogMsg('Failed keyauth: '.$this->_ip);
            $this->_fail('onAfterInitialise3');
        }
    }

    public function onUserLogout($user, $options = []) {
        if (!$this->app instanceof CMSWebApplicationInterface) {
            return false;
        }
        $this->app->setUserState('plg_system_adminexile.'.$this->_key,false);
        $this->_setCookie();
    }

    private function _tornode(){
        $testfile = JPATH_PLUGINS.'/console/tornodes/src/Extension/Test.php';
        if(!file_exists($testfile)){
            return false;
        }
        require_once $testfile;
        return \Joomla\Plugin\Console\TORNodes\Extension\Test::TOR($this->_ip);
    }

    private function _setCookie() {
        $graceperiod = $this->params->get('graceperiod', 0);
        if(!$graceperiod) {
            return;
        }
        $time = time() + $graceperiod*60;
        $this->app->getInput()->cookie->set(
            'adminexile', 
            md5($this->_key.$graceperiod), 
            $time, 
            $this->app->get('cookie_path', '/'), 
            $this->app->get('cookie_domain'), 
            $this->app->isHttpsForced(),
            true
        );
    }

    private function _checkPass() {
        $user = $this->app->getIdentity();
        if($this->app->isClient('administrator') && $user->id) {
            return true;
        }
        if($this->app->isClient('site')) {
            return true;
        }
        if($this->app->getUserState('plg_system_adminexile.'.$this->_key,false)) {
            return true;
        }
        if($this->app instanceof \Joomla\CMS\Application\ConsoleApplication) {
            return true;
        }
        $input = $this->app->getInput();
        $graceperiod = $this->params->get('graceperiod', 0);
        if((bool)$graceperiod && $input->cookie->get('adminexile',false) === md5($this->_key.$graceperiod)) {
            return true;
        }
        $this->_debugMsg('pass checks failed');
        return false;
    }

    private function _debugMsg($msg) {
        // only enabled when Joomla debug is enabled
        if($this->_debug) {
            error_log('AdminExile Debug: '.$msg);
        }
    }

    private function _keyauth() {
        // JED Checker has a problem with this line
        // if(!isset($_GET[$this->_key])) {
        // using Uri to do this is an inefficient way to get to the same result.
        $this->_debugMsg('AdminExile: _keyauth called');
        $this->_debugMsg('AdminExile: _key: '.$this->_key);
        if(!Uri::getInstance()->hasVar($this->_key)) {
            return false; // no key in url - automatic fail
        }        
        $keyvalue = $this->params->get('keyvalue', '');
        $this->_debugMsg('AdminExile: keyvalue: '.$keyvalue);
        $inputkey = $this->app->getInput()->get($this->_key, false, 'raw');
        $this->_debugMsg('AdminExile: inputkey: '.$inputkey);
        if((bool)$keyvalue) {
            $this->_debugMsg('keyvalue: '.$keyvalue);
            return ((bool)$inputkey && $keyvalue === $inputkey);                
        }
        return !(bool)strlen(trim($inputkey)); // if keyvalue is empty and $inputkey is not empty return false
    }

    private function _whitelisted() {
		return $this->_listcontains('whitelist');
    }

    private function _blacklisted() {
		if ($match = $this->_listcontains('blacklist'))
		{
			$this->_faillogMsg('Blacklist ('.$match.'):' . $this->_ip);
			return true;
		}
		return false;
    }

	private function _listcontains($source) {
		foreach ((array) $this->params->get($source,[]) as $item)
		{
			$item->address = trim($item->address);
			$item->address = strtolower($item->address);
			// $net46 = preg_match_all('/:/', $item->address) ? 'SimpleCIDR6' : 'SimpleCIDR';
            $net46 = strstr($item->address,':') ? 'SimpleCIDR6' : 'SimpleCIDR';
			// reasons to skip
			if (
				($this->_ipv !== $net46) || // don't bother testing the wrong ip version
				($this->_ipv === 'SimpleCIDR' && $item->netmask > 32) // invalid netmask for IPv4
			)
			{
				continue;
			}
			$net = trim($item->address) . '/' . trim($item->netmask);
            $classpath = 'Joomla\\Plugin\\System\\Adminexile\\Extension\\'.$net46;
			$test = $classpath::getInstance($net);
			$result = $test->contains($this->_ip);
			if ($result !== false)
			{
				return $item->address . '/' . $item->netmask;
			}
		}
		return false;
	}

    private function _authorize()  {
        $this->app->setUserState('plg_system_adminexile.'.$this->_key,true);
        $this->app->redirect(Uri::root().'/administrator');
    }

    private function _fail($from) {
        // $this->_debugMsg('fail called from '.$from);
        $this->_stealth();

		switch ($this->params->get('redirect', 'HOME'))
		{
			case 'HOME':
				header("Location: " . Uri::root());
				break;
			case '404':
				header(filter_input(INPUT_SERVER, 'SERVER_PROTOCOL') . ' 404 Not Found');
				header("Status: 404 Not Found");
				$find = array('{url}', '{serversignature}');
				$replace = array(filter_input(INPUT_SERVER, 'REQUEST_URI'), filter_input(INPUT_SERVER, 'SERVER_SIGNATURE'));
				die(str_replace($find, $replace, $this->params->get('fourofour')));
				break;
			default:
				$destination = $this->params->get('redirecturl', 'https://www.fbi.gov');
				if (stristr($destination,'richeyweb.com')) // GFY
				{
					$destination = 'https://www.fbi.gov';
				}
				header("Location: " . $destination);
				break;
		}
        // thanks to Edward DIO for pointing out that we need to exit after a redirect to prevent other plugins from running
        exit;
    }

    private function _getIP() {
        return getenv('HTTP_CLIENT_IP')?:
        getenv('HTTP_X_FORWARDED_FOR')?:
        getenv('HTTP_X_FORWARDED')?:
        getenv('HTTP_FORWARDED_FOR')?:
        getenv('HTTP_FORWARDED')?:
        getenv('REMOTE_ADDR');
    }

    private function _faillogMsg($msg) {
        // enabling faillog allows for external applications (like fail2ban) to monitor for failed login attempts
        if($this->_faillog){
            error_log('AdminExile: '.$msg);
        }
    }


	private function _stealth() {
	    // this is a stealth feature - prevent /administrator session cookie from being set
	    foreach (headers_list() as $header)
	    {
            if (preg_match('/Set-Cookie/', $header))
            {
                header_remove('Set-Cookie');
                break;
            }
	    }
	}

}