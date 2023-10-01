<?php

namespace Bitrix\Location\Controller;

use Bitrix\Location\Entity\Address;
use Bitrix\Location\Infrastructure\Service\RecentAddressesService;
use Bitrix\Main\Engine\Controller;

class RecentAddress extends Controller
{
	public static function saveAction(array $address)
	{
		RecentAddressesService::getInstance()->add(Address::fromArray($address));
	}
}
