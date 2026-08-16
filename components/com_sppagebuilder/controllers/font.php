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
 * Fonts Controller class
 *
 * @since 5.0.0
 */
class SppagebuilderControllerFont extends FormController
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
     * Get installed fonts.
     *
     * @return	array	The fonts array.
     * @since	5.0.0
     */
    public function getInstalledFonts()
    {
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('*')
            ->from($db->quoteName('#__sppagebuilder_fonts'))
            ->where($db->quoteName('published') . ' = 1');

        $db->setQuery($query);

        try {
            $response = $db->loadObjectList();


            if (isset($response)) {
                foreach ($response as $key => $value) {
                    if (isset($value->data)) {
                        $value->data = json_decode($value->data);
                    }
                }
            }
        } catch (\Exception $e) {
            $response = [];
        }

        $this->sendResponse($response);
    }

    /**
     * Send JSON Response to the client.
     *
     * @param	array	$response	The response array or data.
     * @param	int		$statusCode	The status code of the HTTP response.
     *
     * @return	void
     * @since	5.0.0
     */
    private function sendResponse($response, int $statusCode = 200): void
    {
        $app = Factory::getApplication();
        $app->setHeader('status', $statusCode, true);
        $app->sendHeaders();
        echo new JsonResponse($response);
        $app->close();
    }
}
