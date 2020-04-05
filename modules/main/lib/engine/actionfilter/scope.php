<?php

namespace Bitrix\Main\Engine\ActionFilter;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use http\Exception\InvalidArgumentException;

class Scope extends Base
{
	const AJAX = 0b00000001;
	const REST = 0b00000010;
	const CLI  = 0b00000100;
	const ALL  = 0b00000111;

	const NOT_AJAX = self::ALL & ~self::AJAX;
	const NOT_REST = self::ALL & ~self::REST;
	const NOT_CLI  = self::ALL & ~self::CLI;

	private $scopes;

	public function __construct($scopes)
	{
		$this->scopes = $scopes;
		parent::__construct();
	}

	public function onBeforeAction(Event $event)
	{
		$scope = $this->getCurrentScope();
		if (($this->scopes & $scope) === $scope)
		{
			return null;
		}

		$this->addError(new Error('Requested scope is invalid'));

		return new EventResult(EventResult::ERROR, null, null, $this);
	}

	protected function getCurrentScope()
	{
		switch ($this->getAction()->getController()->getScope())
		{
			case Controller::SCOPE_AJAX:
				return static::AJAX;
			case Controller::SCOPE_REST:
				return static::REST;
			case Controller::SCOPE_CLI:
				return static::CLI;
		}

		throw new InvalidArgumentException('Scope is invalid');
	}
}