<?php

namespace Bitrix\Calendar\Sync\Factories;

use Bitrix\Calendar\Core\Builders\EventBuilderFromEntityObject;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Internals\EO_EventConnection;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromDM;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Loader;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;

class EventConnectionFactory
{
	/**
	 * @param array $params
	 *
	 * @return EventConnection|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 *
	 * todo: all this logic can be moved to builder
	 */
	public function getEventConnection(array $params): ?EventConnection
	{
		if (!Loader::includeModule('dav'))
		{
			return null;
		}

		$select = ['*'];
		if (empty($params['event']))
		{
			$select[] = 'EVENT';
		}
		if (empty($params['connection']))
		{
			$select[] = 'CONNECTION';
		}

		$statement = EventConnectionTable::query();
		$statement->setSelect($select);
		if (!empty($params['filter']))
		{
			$statement->setFilter($params['filter']);
		}

		$link = $statement->exec()->fetchObject() ?: null;
		if ($link === null)
		{
			return null;
		}

		$event = $params['event']
			?? (new EventBuilderFromEntityObject($link->getEvent()))->build();

		$connection = $params['connection']
			?? (new BuilderConnectionFromDM($link->getConnection()))->build();

		$result = new EventConnection();
		$result
			->setId($link->getId())
			->setEvent($event)
			->setConnection($connection)
			->setVendorEventId($link->getVendorEventId())
			->setLastSyncStatus($link->getSyncStatus())
			->setEntityTag($link->getEntityTag())
			->setVersion($link->getVersion())
			->setData($link->getData())
		;

		return $result;
	}

	/**
	 * @param Event $event
	 * @param Connection $connection
	 *
	 * @return EventConnection|null
	 *
	 * @throws ArgumentException
	 * @throws ObjectPropertyException
	 * @throws SystemException
	 */
	public function getFromEventConnection(Event $event, Connection $connection): ?EventConnection
	{
		$statement = EventConnectionTable::query()
			->setSelect(['*'])
			->addFilter('EVENT_ID', $event->getId())
			->addFilter('CONNECTION_ID', $connection->getId())
			->exec()
		;
		/** @var EO_EventConnection $link */
		$link = $statement->fetchObject() ?: null;
		if ($link === null)
		{
			return null;
		}
		$result = new EventConnection();
		$result
			->setId($link->getId())
			->setEvent($event)
			->setConnection($connection)
			->setVendorEventId($link->getVendorEventId())
			->setLastSyncStatus($link->getSyncStatus())
			->setEntityTag($link->getEntityTag())
			->setVersion($link->getVersion())
			->setData($link->getData())
		;

		return $result;
	}
}
