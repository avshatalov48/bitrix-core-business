<?php

namespace Bitrix\Calendar\Core\Mappers;

use Bitrix\Calendar\Core\Base\BaseException;
use Bitrix\Calendar\Core\Builders\EventBuilderFromEntityObject;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Sync;
use Bitrix\Main\DI\ServiceLocator;

class SyncEvent
{
	/**
	 * @var Event
	 */
	private Event $eventMapper;
	/**
	 * @var EventConnection
	 */
	private EventConnection $eventConnectionMapper;

	public function __construct()
	{
		$helper = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
		$this->eventMapper = $helper->getEvent();
		$this->eventConnectionMapper = $helper->getEventConnection();
	}

	/**
	 * @throws \Bitrix\Main\SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public function getSyncEventCollectionByIdCollection(array $collection): Sync\Entities\SyncEventMap
	{
		$filter = ['VENDOR_EVENT_ID' => $collection];
		$eventConnectionMap = $this->eventConnectionMapper->getMap($filter);

		$syncEventMap = new Sync\Entities\SyncEventMap();
		/** @var Sync\Connection\EventConnection $item */
		foreach ($eventConnectionMap as $item)
		{
			$syncEventMap->add($item, $item->getVendorEventId());
		}

		return $syncEventMap;
	}

	public function getSyncEventWithVendorId(string $vendorId): Sync\Entities\SyncEvent
	{
		$eventConnectionDM = EventConnectionTable::query()
			->addFilter('VENDOR_EVENT_ID', $vendorId)
			->setSelect(['*'])
			->exec()
			->fetchObject()
		;

		if ($eventConnectionDM)
		{
			$syncEvent = new Sync\Entities\SyncEvent();
			$eventConnection = (new Sync\Builders\BuilderEventConnectionFromDM($eventConnectionDM))->build();
			$syncEvent
				->setEventConnection($eventConnection)
				->setEvent((new EventBuilderFromEntityObject($eventConnectionDM->getEvent()))->build())
			;

			return $syncEvent;
		}

		throw new BaseException('do not get SyncEvent');
	}

	/**
	 * @param Sync\Entities\SyncEvent $syncEvent
	 * @return Sync\Entities\SyncEvent
	 * @throws BaseException
	 * @throws \Exception
	 */
	public function delete(Sync\Entities\SyncEvent $syncEvent): Sync\Entities\SyncEvent
	{
		try
		{
			$event = $this->eventMapper->delete($syncEvent->getEvent());
			$eventConnection = $syncEvent->getEventConnection();

			if ($eventConnection === null)
			{
				throw new BaseException('you should send eventConnection property');
			}

			$eventConnection->setEvent($event);
			$syncEvent->setEventConnection($this->eventConnectionMapper->delete($eventConnection));
			$syncEvent->setEvent($event);

			return $syncEvent;
		}
		catch (BaseException $exception)
		{
			throw new BaseException($exception->getMessage());
		}
	}
}
