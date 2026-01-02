<?php

declare(strict_types=1);

namespace Bitrix\Calendar\Synchronization\Internal\Repository\Mapper;

use Bitrix\Calendar\Core\Builders\EventBuilderFromEntityObject;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromDM;
use Bitrix\Calendar\Synchronization\Internal\Entity\EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Model\EO_EventConnection;
use Bitrix\Calendar\Synchronization\Internal\Model\EventConnectionTable;

class EventConnectionMapper
{
	public function convertFromOrm(EO_EventConnection $ormModel): EventConnection
	{
		$eventConnection = new EventConnection();

		if ($ormEvent = $ormModel->getEvent())
		{
			// @todo Remove old dependency
			$eventBuilder = new EventBuilderFromEntityObject($ormEvent);

			$eventConnection->setEvent($eventBuilder->build());
		}

		if ($ormConnection = $ormModel->getConnection())
		{
			// @todo Remove old dependency
			$connectionBuilder = new BuilderConnectionFromDM($ormConnection);

			$eventConnection->setConnection($connectionBuilder->build());
		}

		$eventConnection
			->setId($ormModel->getId())
			->setVendorEventId($ormModel->getVendorEventId())
			->setLastSyncStatus($ormModel->getSyncStatus())
			->setRetryCount($ormModel->getRetryCount())
			->setEntityTag($ormModel->getEntityTag())
			->setVersion((int)$ormModel->getVersion())
			->setVendorVersionId($ormModel->getVendorVersionId())
			->setRecurrenceId($ormModel->getRecurrenceId())
			->setData($ormModel->getData());

		return $eventConnection;
	}

	/**
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function convertToOrm(EventConnection $entity): EO_EventConnection
	{
		$ormEventConnection = $entity->getId()
			? EO_EventConnection::wakeUp($entity->getId())
			: EventConnectionTable::createObject();

		$ormEventConnection
			->setEventId($entity->getEvent()->getId())
			->setConnectionId($entity->getConnection()->getId())
			->setVendorEventId($entity->getVendorEventId())
			->setSyncStatus($entity->getLastSyncStatus())
			->setRetryCount($entity->getRetryCount())
			->setEntityTag($entity->getEntityTag())
			->setVendorVersionId($entity->getVendorVersionId())
			->setRecurrenceId($entity->getRecurrenceId())
			->setVersion((string)$entity->getVersion())
			->setData($entity->getData())
		;

		return $ormEventConnection;
	}
}
