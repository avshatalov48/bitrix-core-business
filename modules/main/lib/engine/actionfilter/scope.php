<?php

namespace Bitrix\Main\Engine\ActionFilter;

use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;

class Scope extends Base
{
	public const AJAX = 0b00000001;
	public const REST = 0b00000010;
	public const CLI  = 0b00000100;
	public const ALL  = 0b00000111;

	public const NOT_AJAX = self::ALL & ~self::AJAX;
	public const NOT_REST = self::ALL & ~self::REST;
	public const NOT_CLI  = self::ALL & ~self::CLI;

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

		throw new ArgumentOutOfRangeException('Scope is invalid');
	}
}