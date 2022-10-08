<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromArray;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Factories\FactoryBuilder;
use Bitrix\Calendar\Sync\Util\Context;
use Bitrix\Calendar\Sync\Util\EventContext;
use Bitrix\Main\Type;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\ObjectException;
use Bitrix\Main\ObjectNotFoundException;
use Bitrix\Main\ObjectPropertyException;
use Bitrix\Main\SystemException;
use \Bitrix\Main\Loader;
use Exception;

class EventQueueManager
{
	public const CHECK_EVENTS_PERIOD = 600; // 10min
	private const CHECK_ENTRY_LIMIT = 10;
	private const MIN_RETRY_COUNT = 5;
	private const MAX_RETRY_COUNT = 20;
	private array $connectionList = [];

	/**
	 * @var Core\Mappers\Factory
	 */
	private Core\Mappers\Factory $mapperFactory;

	/**
	 * @throws ObjectNotFoundException
	 */
	public function __construct()
	{
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	public static function createInstance(): EventQueueManager
	{
		return new self();
	}

	/**
	 * @return string|null
	 * @throws ArgumentException
	 * @throws ObjectNotFoundException
	 * @throws ObjectPropertyException
	 * @throws SystemException|\Bitrix\Main\LoaderException
	 */
	public static function checkEvents(): ?string
	{
		if (
			!Loader::includeModule('calendar')
			|| !Loader::includeModule('dav')
		)
		{
			return null;
		}

		$qm = self::createInstance();

		$entriesDb = $qm::getEventListDb();
		while ($queueItem = $entriesDb->Fetch())
		{
			$connection = $qm->getConnectionForQueueItem($queueItem);

			$event = $qm->mapperFactory->getEvent()->getEntity((int)$queueItem['EVENT_ID']);
			if (!$event)
			{
				continue;
			}

			$context = $qm->prepareEventContext(
				[
					'connection' => $connection,
					'event' => $event,
					'queueItem' => $queueItem,
				]
			);

			if ($event->getOwner() === null || $event->getOwner()->getId() === null)
			{
				$eventConnection = $context->getEventConnection();
				if ($eventConnection !== null)
				{
					$qm->mapperFactory->getEventConnection()->update(
						$eventConnection->setLastSyncStatus(Dictionary::SYNC_STATUS['success'])
					);
				}

				continue;
			}

			$factory = FactoryBuilder::create($connection->getVendor()->getCode(), $connection, $context);
			$syncManager = new VendorSynchronization($factory);
			$result = null;

			try{
				switch ($queueItem['SYNC_STATUS'])
				{
					case Dictionary::SYNC_STATUS['create']:
						$result = $syncManager->createEvent($event, $context);
						break;
					case Dictionary::SYNC_STATUS['update']:
						$result = $syncManager->updateEvent($event, $context);
						break;
					case Dictionary::SYNC_STATUS['delete']:
						$result = $syncManager->deleteEvent($event, $context);
						break;
				}
			}
			catch(Exception $e){}

			$eventLink = $qm->mapperFactory->getEventConnection()->getMap([
				'=EVENT_ID' => $event->getId(),
				'=CONNECTION_ID' => $factory->getConnection()->getId(),
			])->fetch();

			if (!is_null($eventLink))
			{
				$retryCount = $eventLink->getRetryCount() + 1;
				$currentNextSyncTry = $connection->getNextSyncTry();
				if ($result && $result->isSuccess())
				{
					$resultData = $result->getData();
					if (is_array($resultData) && $resultData['status'] === Dictionary::SYNC_STATUS['success'])
					{
						$retryCount = 0;
						$currentNextSyncTry = null;
					}
				}

				$eventLink->setRetryCount($retryCount);
				$qm->mapperFactory->getEventConnection()->update($eventLink);
			}
			else
			{
				$retryCount = 0;
				$currentNextSyncTry = null;
			}

			$connection->setNextSyncTry(self::prepareNextTime(
				$currentNextSyncTry,
				$retryCount
			));
		}

		$qm->saveConnections();

		return "\\Bitrix\\Calendar\\Sync\\Managers\\EventQueueManager::checkEvents();";
	}

	private function getConnectionForQueueItem(array $queueItem): Connection
	{
		$connectionId = (int)$queueItem['CONNECTION_ID'];
		if (!isset($this->connectionList[$connectionId]))
		{
			$this->connectionList[$connectionId] = (new BuilderConnectionFromArray($queueItem))->build();
		}

		return $this->connectionList[$connectionId];
	}

	/**
	 * @param Date|null $currentNextSyncTime
	 * @param int $retryCount
	 *
	 * @return Date
	 *
	 * @throws ObjectException
	 */
	private static function prepareNextTime(?Date $currentNextSyncTime, int $retryCount): Date
	{
		$nextSyncTime = new Date(new Type\DateTime());
		if ($retryCount > self::MIN_RETRY_COUNT)
		{
			$nextSyncTime = $nextSyncTime->add('+1 day');
		}

		if (!is_null($currentNextSyncTime) && $currentNextSyncTime->getTimestamp() > $nextSyncTime->getTimestamp())
		{
			$nextSyncTime = $currentNextSyncTime;
		}

		return $nextSyncTime;
	}


	private function saveConnections()
	{
		foreach($this->connectionList as $connectionId => $connection)
		{
			try{
				$this->mapperFactory->getConnection()->update($connection);
			}
			catch(Exception $e){}
		}
	}

	private function prepareEventContext(array $params): EventContext
	{
		$queueItem = $params['queueItem'];

		$context = new Context(
			[
				'connection' => $params['connection'],
			]
		);

		$eventLink = (new EventConnection())
			->setId($queueItem['EVENT_CONNECTION_ID'])
			->setEntityTag($queueItem['ENTITY_TAG'])
			->setVendorVersionId($queueItem['VENDOR_VERSION_ID'])
			->setRetryCount($queueItem['RETRY_COUNT'])
			->setLastSyncStatus($queueItem['SYNC_STATUS'])
			->setVendorEventId($queueItem['VENDOR_EVENT_ID'])
			->setData(json_decode($queueItem['DATA']))
			->setVersion((int) $queueItem['VERSION'])
			->setConnection($params['connection'])
			->setEvent($params['event'])
		;

		$sectionLink = $this->mapperFactory->getSectionConnection()->getMap(
			[
				'=SECTION_ID' => (int) $queueItem['SECTION_ID'],
				'=CONNECTION_ID' => (int) $queueItem['CONNECTION_ID'],
			]
		)->fetch();

		$context = (new EventContext())
			->merge($context)
			->setEventConnection($eventLink)
			->setSectionConnection($sectionLink);

		return $context;
	}

	/**
	 * @return \CDBResult|false|void
	 */
	private static function getEventListDb()
	{
		global $DB;
		$sqlQuery = "SELECT "
				. " e.SECTION_ID,"
				. " ec.ID as EVENT_CONNECTION_ID,"
				. " ec.EVENT_ID,"
				. " ec.ENTITY_TAG,"
				. " ec.VENDOR_VERSION_ID,"
				. " ec.CONNECTION_ID,"
				. " ec.VENDOR_EVENT_ID,"
				. " ec.VERSION,"
				. " ec.SYNC_STATUS,"
				. " ec.RETRY_COUNT,"
				. " con.*"
			. " FROM b_calendar_event e"
				. " INNER JOIN b_calendar_event_connection ec ON ec.EVENT_ID = e.ID "
				. " INNER JOIN b_calendar_section s ON s.ID = e.SECTION_ID "
				. " INNER JOIN b_dav_connections con ON con.ID = ec.CONNECTION_ID "
				. " INNER JOIN b_calendar_section_connection sc ON sc.SECTION_ID = e.SECTION_ID "
			. " WHERE "
				. " ec.SYNC_STATUS <> 'success'"
				. " and s.ACTIVE = 'Y' and sc.ACTIVE = 'Y' and sc.CONNECTION_ID = con.ID"
				. " and con.NEXT_SYNC_TRY <= NOW()"
				. " and ec.RETRY_COUNT <= ".self::MAX_RETRY_COUNT
			. " ORDER BY ec.RETRY_COUNT ASC"
			. " LIMIT ".self::CHECK_ENTRY_LIMIT;

		return $DB->Query($sqlQuery);
	}
}
