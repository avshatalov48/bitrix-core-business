<?php

namespace Bitrix\Rest\Engine\ActionFilter;

use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

/**
 * Class Scope
 * @package Bitrix\Rest\Engine\ActionFilter
 */
class Scope extends Base
{
	public const ERROR_INSUFFICIENT_SCOPE = 'insufficient_scope';

	private $scopes;

	public function __construct(...$scopes)
	{
		$this->scopes = $scopes;
		parent::__construct();
	}

	public function onBeforeAction(Event $event)
	{
		$scopeList = $this->getCurrentScope();

		$need = array_diff($this->scopes, $scopeList);

		if (!$need)
		{
			return null;
		}

		$this->addError(
			new Error(
				'The current method required more scopes. (' . implode(', ', $need) . ')',
				self::ERROR_INSUFFICIENT_SCOPE
			)
		);

		return new EventResult(EventResult::ERROR, null, null, $this);
	}

	protected function getCurrentScope()
	{
		$server = $this->getRestServer();
		if ($server)
		{
			return $server->getAuthScope();
		}

		return [];
	}
}