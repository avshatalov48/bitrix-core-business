<?php

namespace Bitrix\Calendar\Integration\Crm;

use Bitrix\Crm\Service\Container;
use Bitrix\Main\Loader;

class DealHandler
{
	public static function getDeal(int $dealId): mixed
	{
		if (Loader::includeModule('crm'))
		{
			$entityBroker = Container::getInstance()->getEntityBroker(\CCrmOwnerType::Deal);

			return $entityBroker?->getById($dealId);
		}

		return null;
	}
}