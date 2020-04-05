<?php


namespace Bitrix\Main\Engine\ActionFilter;


use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

final class HttpMethod extends Base
{
	const METHOD_GET  = 'GET';
	const METHOD_POST = 'POST';

	const ERROR_INVALID_HTTP_METHOD = 'invalid_http_method';
	/**
	 * @var array
	 */
	private $allowedMethods;

	/**
	 * HttpMethodFilter constructor.
	 * @param array $allowedMethods
	 */
	public function __construct(array $allowedMethods = array(self::METHOD_GET))
	{
		$this->allowedMethods = $allowedMethods;
		parent::__construct();
	}

	/**
	 * List allowed values of scopes where the filter should work.
	 * @return array
	 */
	public function listAllowedScopes()
	{
		return array(
			Controller::SCOPE_AJAX,
			Controller::SCOPE_REST,
		);
	}

	/**
	 * @return bool
	 */
	public function containsPostMethod()
	{
		return in_array(self::METHOD_POST, $this->allowedMethods, true);
	}

	public function onBeforeAction(Event $event)
	{
		$requestMethod = $this->action->getController()->getRequest()->getRequestMethod();

		if (!in_array($requestMethod, $this->allowedMethods, true))
		{
			$this->addError(new Error(
				'Wrong method for current action',
				self::ERROR_INVALID_HTTP_METHOD
			));

			return new EventResult(EventResult::ERROR, null, null, $this);
		}

		return null;
	}
}