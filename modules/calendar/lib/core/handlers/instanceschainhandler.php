<?php

namespace Bitrix\Calendar\Core\Handlers;

use Bitrix\Calendar\Core\Mappers\Event;

class InstancesChainHandler
{
	public function __construct()
	{
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __invoke(int $id, int $ownerId, array $fields = ['*']): \Bitrix\Calendar\Core\Event\EventMap
	{
		return (new Event())->getMapFullChainByParentId($id, $ownerId, $fields);
	}
}
