<?php

declare(strict_types=1);


namespace Bitrix\Socialnetwork\Collab\Integration\Intranet;

use Bitrix\Main\Loader;

class ServiceContainer
{
	public static function getInstance(): ?\Bitrix\Intranet\Service\ServiceContainer
	{
		if (!Loader::includeModule('intranet'))
		{
			return null;
		}

		return \Bitrix\Intranet\Service\ServiceContainer::getInstance();
	}
}