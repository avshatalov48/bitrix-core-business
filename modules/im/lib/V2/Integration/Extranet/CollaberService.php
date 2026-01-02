<?php

namespace Bitrix\Im\V2\Integration\Extranet;

use Bitrix\Extranet\Service\ServiceContainer;
use Bitrix\Main\Loader;
use Bitrix\Extranet\PortalSettings;

class CollaberService
{
	protected static ?self $instance = null;

	private function __construct(){}

	public static function getInstance(): self
	{
		if (self::$instance === null)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	public function isCollaber(int $userId): bool
	{
		if (!Loader::includeModule('extranet'))
		{
			return false;
		}

		return ServiceContainer::getInstance()->getCollaberService()->isCollaberById($userId);
	}

	public function isEnabledCollabersInvitation(): bool
	{
		if (!Loader::includeModule('extranet'))
		{
			return false;
		}

		return PortalSettings::getInstance()->isEnabledCollabersInvitation();
	}
}
