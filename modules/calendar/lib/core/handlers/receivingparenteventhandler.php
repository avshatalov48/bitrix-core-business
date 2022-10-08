<?php

namespace Bitrix\Calendar\Core\Handlers;

use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Main\DI\ServiceLocator;

class ReceivingParentEventHandler
{
	public function __construct()
	{

	}

	/**
	 * @param int $parentId
	 * @param int $ownerId
	 *
	 * @return Event
	 * @throws \Bitrix\Calendar\Core\Base\BaseException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function __invoke(int $parentId, int $ownerId): Event
	{
		/** @var Factory $mapperFactory */
		$mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');

		return $mapperFactory->getEvent()->getMap([
			'=PARENT_ID' => $parentId,
			'=OWNER_ID' => $ownerId
		])->fetch();
	}
}
