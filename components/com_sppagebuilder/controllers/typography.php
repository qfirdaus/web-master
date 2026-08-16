<?php
/**
 * @package SP Page Builder
 * @author JoomShaper http://www.joomshaper.com
 * @copyright Copyright (c) 2010 - 2025 JoomShaper
 * @license http://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 or later
 */

// No direct access
defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Response\JsonResponse;
use Joomla\CMS\Session\Session;

/**
 * Typography Controller class
 *
 * @since 5.5.5
 */
class SppagebuilderControllerTypography extends FormController
{

	public function __construct($config = [])
	{
		parent::__construct($config);

		$user = Factory::getUser();
		$authorised = $user->authorise('core.admin', 'com_sppagebuilder') || $user->authorise('core.manage', 'com_sppagebuilder');

		$app   = Factory::getApplication();
		$method = $app->input->getMethod();

		if ($method == 'GET') {
			$authorised = $user->authorise('core.edit', 'com_sppagebuilder') || $user->authorise('core.edit.own', 'com_sppagebuilder');
		}

		if (!$authorised)
		{
			$response['message'] = Text::_('COM_SPPAGEBUILDER_EDITOR_ADMIN_ACCESS_REQUIRED');
			$this->sendResponse($response, 403, true);
		}

		if (!$user->id)
		{
			$response['message'] = Text::_('COM_SPPAGEBUILDER_EDITOR_LOGIN_SESSION_EXPIRED');
			$this->sendResponse($response, 401, true);
		}

		if (!Session::checkToken())
		{
			$response['message'] = Text::_('COM_SPPAGEBUILDER_EDITOR_SESSION_MISMATCHED');
			$this->sendResponse($response, 403, true);
		}
	}
   /**
     * Retrieves all published typography configurations from the database.
     * 
     * This method:
     * 1. Queries the database for all typography records with published status
     * 2. Loads typography data including id, name, and typography content
     * 3. Decodes the JSON typography data for each record
     * 4. Sends the response as JSON to the client
     *
     * @return void
     * 
     * @since 5.5.5
     */

	public function globalTypographies()
	{
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(['id', 'name', 'typography'])
			->from($db->quoteName('#__sppagebuilder_typography'))
			->where($db->quoteName('published') . ' = 1');
		$db->setQuery($query);

		$typographies = [];

		try
		{
			$typographies = $db->loadObjectList();
		}
		catch (\Exception $e)
		{
			return [];
		}

		if (!empty($typographies))
		{
			foreach ($typographies as &$typography)
			{
				$typography->typography = \json_decode($typography->typography);
			}

			unset($typography);
		}

		$this->sendResponse($typographies);
	}

	public static function getGlobalTypographiesLocally() {
		$db = Factory::getDbo();
		$query = $db->getQuery(true);
		$query->select(['id', 'name', 'typography'])
			->from($db->quoteName('#__sppagebuilder_typography'))
			->where($db->quoteName('published') . ' = 1');
		$db->setQuery($query);

		$typographies = [];

		try
		{
			$typographies = $db->loadObjectList();

			if (empty($typographies)) {
				return null;
			}
		}
		catch (\Exception $e)
		{
			return [];
		}

		if (!empty($typographies))
		{
			foreach ($typographies as &$typography)
			{
				$typography->typography = \json_decode($typography->typography);
			}

			unset($typography);
		}
		return $typographies;
	}

	 /**
     * Sends JSON response to the client with appropriate headers.
     * 
     * This helper method:
     * 1. Sets the HTTP status code header
     * 2. Sends all headers to the client
     * 3. Outputs the response data as JSON
     * 4. Closes the application to prevent further output
     * 
     * @param mixed $response The data to be sent as JSON response
     * @param int $statusCode HTTP status code to include in the response
     * 
     * @return void
     * 
     * @since 5.5.5
     */
	
	private function sendResponse($response, int $statusCode = 200) : void
	{
		$app = Factory::getApplication();
		$app->setHeader('status', $statusCode, true);
		$app->sendHeaders();
		echo new JsonResponse($response);
		$app->close();
	}
}
