<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage sender
 * @copyright 2001-2012 Bitrix
 */
namespace Bitrix\Sender\Internals\CommonAjax;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\HttpRequest;

use Bitrix\Sender\Internals\QueryController as Controller;

Loc::loadMessages(__FILE__);

/**
 * Class ActionGetTemplate
 * @package Bitrix\Sender\Internals\CommonAjax
 */
abstract class CommonAction
{
	const NAME = 'unknown';

	/**
	 * Get action instance.
	 *
	 * @return Controller\Action
	 */
	public static function get()
	{
		return Controller\Action::create(static::NAME)->setHandler(array(get_called_class(), 'onRequest'));
	}

	/**
	 * Get action requesting uri.
	 *
	 * @param string $controllerUri Controller uri.
	 * @param array $parameters Parameters.
	 * @return string
	 */
	public static function getRequestingUri($controllerUri, array $parameters = array())
	{
		return Controller\Manager::getActionRequestingUri(static::NAME, $parameters, $controllerUri);
	}

	/**
	 * On request event handler.
	 *
	 * @param HttpRequest $request Request.
	 * @param Controller\Response $response Response.
	 */
	public static function onRequest(HttpRequest $request, Controller\Response $response)
	{

	}
}