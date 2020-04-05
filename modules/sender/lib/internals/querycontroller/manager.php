<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2017 Bitrix
 */
namespace Bitrix\Sender\Internals\QueryController;

use Bitrix\Main\Web\Uri;

class Manager
{
	/**
	 * Get action requesting uri.
	 *
	 * @param string $actionName Action name.
	 * @param array $parameters Parameters.
	 * @param string $controllerUri Controller uri.
	 * @return string
	 */
	public static function getActionRequestingUri ($actionName, array $parameters = array(), $controllerUri)
	{
		$parameters['action'] = $actionName;
		$parameters['sessid'] = bitrix_sessid();

		$uri = new Uri($controllerUri);
		$uri->addParams($parameters);

		return $uri->getLocator();
	}
}