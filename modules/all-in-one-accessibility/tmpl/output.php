<?php
/**
 * @package     All in One Accessibility
 * @author      Skynet Technologies USA LLC.
 * @copyright   (C) 2024 - Skynet Technologies USA LLC.
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 **/

use Joomla\CMS\Uri\Uri;
defined('_JEXEC') or die;



$uri = Uri::getInstance();
$url = $uri->toString();
$aioa_current_url_parse = parse_url($url);
$aioa_website_hostname = $aioa_current_url_parse['host'];


$curl = curl_init();
// Set cURL options
curl_setopt($curl, CURLOPT_URL, "https://ada.skynettechnologies.us/check-website"); // API endpoint
curl_setopt($curl, CURLOPT_RETURNTRANSFER, true); // Return the response as a string
curl_setopt($curl, CURLOPT_POST, true); // Set request method to POST
// POST data
$postData = ['domain' => $aioa_website_hostname];
curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData)); // Set the POST data
// Execute cURL request
$aioa_curl_result = curl_exec($curl);
$settingURLObject = (object)json_decode($aioa_curl_result, true);



$aioalicenseKey = "";
if(isset($settingURLObject->api_key))
{
    $aioalicenseKey = $settingURLObject->api_key;
}
$aioa_custom_tag = "<script>
        setTimeout(() => {
            let aioa_script_tag = document.createElement('script');
            aioa_script_tag.src = 'https://www.skynettechnologies.com/accessibility/js/all-in-one-accessibility-js-widget-minify.js?aioa_reg_req=true&colorcode=&token=".$aioalicenseKey."&position=bottom_right';
            aioa_script_tag.id = 'aioa-adawidget';
            aioa_script_tag.type = 'module';
            aioa_script_tag.defer = true;
            document.getElementsByTagName('head')[0].appendChild(aioa_script_tag);
        }, 500);
    </script>";



$document = JFactory::getDocument();
$document->addCustomTag($aioa_custom_tag);

