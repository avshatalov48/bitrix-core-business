<?php

namespace Bitrix\Rest\Engine\ActionFilter;

use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Rest;

/**
 * Class AuthType
 * @package Bitrix\Rest\Engine\ActionFilter
 */
class AuthType extends Base
{
	public const PASSWORD = 0b00000001;
	public const APPLICATION = 0b00000010;
	public const SESSION = 0b00000100;

	public const ALL = self::APPLICATION | self::PASSWORD | self::SESSION;

	public const ERROR_INSUFFICIENT_AUTH_TYPE = 'insufficient_auth_type';

	private $types;

	public function __construct($types)
	{
		$this->types = $types;
		parent::__construct();
	}

	public function onBeforeAction(Event $event)
	{
		$scope = $this->getCurrentAuthType();
		if (($this->types & $scope) === $scope)
		{
			return null;
		}

		$this->addError(new Error('The request requires higher privileges than provided.', self::ERROR_INSUFFICIENT_AUTH_TYPE));

		return new EventResult(EventResult::ERROR, null, null, $this);
	}

	protected function getCurrentAuthType() : ?int
	{
		$server = $this->getRestServer();
		if ($server)
		{
			switch ($server->getAuthType())
			{
				case Rest\APAuth\Auth::AUTH_TYPE:
					return static::PASSWORD;
				case Rest\OAuth\Auth::AUTH_TYPE:
					return static::APPLICATION;
				case Rest\SessionAuth\Auth::AUTH_TYPE:
					return static::SESSION;
			}
		}

		return null;
	}
}