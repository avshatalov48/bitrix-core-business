<?php

namespace Bitrix\Calendar\Core\Oauth;

use Bitrix\Main\Engine\UrlManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Web\Uri;

abstract class Base
{
	/**
	 * @var \CSocServAuth
	 */
	protected \CSocServAuth $oauthClient;

	/**
	 * @var string
	 */
	protected string $serviceName;

	/**
	 * @return static|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public static function getInstance(): ?static
	{
		if (!Loader::includeModule('socialservices'))
		{
			return null;
		}

		if (!$userId = \CCalendar::GetCurUserId())
		{
			return null;
		}

		$className = static::class;

		$instance = new $className($userId);

		if (!$instance->checkService())
		{
			return null;
		}

		$instance->serviceName = $className::getServiceName();

		return $instance;
	}

	/**
	 * @param $userId
	 */
	abstract protected function __construct($userId);

	/**
	 * @return string
	 */
	abstract public function getUrl(): string;

	/**
	 * @return bool
	 */
	abstract protected function checkService(): bool;


	/**
	 * @return string
	 */
	abstract public static function getServiceName(): string;
}