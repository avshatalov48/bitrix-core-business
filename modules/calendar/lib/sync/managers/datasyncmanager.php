<?php

namespace Bitrix\Calendar\Sync\Managers;

use Bitrix\Calendar\Core\Builders\EventBuilderFromArray;
use Bitrix\Calendar\Core\Builders\SectionBuilderFromDataManager;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Mappers\Factory;
use Bitrix\Calendar\Internals\EventConnectionTable;
use Bitrix\Calendar\Internals\SectionTable;
use Bitrix\Calendar\Sync\Builders\BuilderConnectionFromDM;
use Bitrix\Calendar\Sync\Connection\Connection;
use Bitrix\Calendar\Sync\Dictionary;
use Bitrix\Calendar\Sync\Icloud;
use Bitrix\Calendar\Rooms;
use Bitrix\Calendar\Internals\SectionConnectionTable;
use Bitrix\Calendar\Sync\Util\AttendeesDescription;
use Bitrix\Calendar\Sync\Util\RequestLogger;
use Bitrix\Calendar\Sync\Util\Result;
use Bitrix\Calendar\Util;
use Bitrix\Dav\Internals\DavConnectionTable;
use Bitrix\Dav\Internals\EO_DavConnection;
use Bitrix\Dav\Internals\EO_DavConnection_Collection;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Query\Query;
use Bitrix\Main\SystemException;
use Bitrix\Main\Type\DateTime;

class DataSyncManager
{
	private const ENTITY_TYPE = 'user';
	private const MAX_NUMBER = 5;
	private const TIME_SLICE = 2600000;

	/** @var Factory $mapperHelper */
	private $mapperFactory;

	/**
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 */
	private function __construct()
	{
		$this->mapperFactory = ServiceLocator::getInstance()->get('calendar.service.mappers.factory');
	}

	/**
	 * @return string
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
	 */
	public static function dataSyncAgent(): string
	{
		(new self())->dataSync();

		return "\\Bitrix\\Calendar\\Sync\\Managers\\DataSyncManager::dataSyncAgent();";
	}

	/**
	 * @return DataSyncManager
	 */
	public static function createInstance(): DataSyncManager
	{
		return new self();
	}

	/**
	 * @param $userId
	 *
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 * @throws \CDavArgumentNullException
	 */
	public function dataSync($userId = null): bool
	{
		if (!Loader::includeModule('dav') || !Loader::includeModule('calendar'))
		{
			return true;
		}

		$connections = $this->getConnections($userId);
		foreach ($connections as $connection)
		{
			$connection = $this->createConnectionObject($connection);
			$result = $this->syncConnection($connection);
			if ($result->isSuccess())
			{
				\CDavConnection::SetLastResult($connection->getId(), $result->getData()['lastResult']);
				Util::addPullEvent('refresh_sync_status', $connection->getOwner()->getId(), [
					'syncInfo' => [
						$connection->getAccountType() => [
							'status' => $result->getData()['syncStatus'],
							'type' => $connection->getAccountType(),
							'connected' => true,
							'id' => $connection->getId(),
							'syncOffset' => 0
						],
					],
					'requestUid' => Util::getRequestUid(),
				]);
			}
		}

		return true;
	}

	/**
	 *
	 * @param EO_DavConnection $connection
	 *
	 * @return Result
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ArgumentNullException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	private function syncConnection(Connection $connection): Result
	{
		$result = new Result();
		$logger = null;

		if (
			RequestLogger::isWriteToLogForSyncRequest(
				$connection->getOwner()->getId(),
				$connection->getVendor()->getCode()
			)
		)
		{
			$logger = new RequestLogger($connection->getOwner()->getId(), $connection->getVendor()->getCode());
		}

		$client = $this->initClient($connection);

		$calendarsList = $client->GetCalendarList($connection->getServer()->getBasePath(), $logger);

		if ($client->getError())
		{
			$error = $this->processError($client->getError());
			$result->setData([
				'lastResult' => $error,
				'syncStatus' => false,
			]);

			return $result;
		}

		if (!$calendarsList || !is_array($calendarsList))
		{
			$result->setData([
				'lastResult' => '[204] No Content',
				'syncStatus' => true,
			]);

			return $result;
		}

		$calendarsList = $this->syncSections($connection, $calendarsList);

		foreach ($calendarsList as $calendar)
		{
			[$events, $eventsMap] = $this->getEventsToSync(
				$connection,
				$client,
				$calendar,
				$logger
			);

			if ($client->getError())
			{
				$error = $this->processError($client->getError());
				$result->setData([
					'lastResult' => $error,
					'syncStatus' => false,
				]);

				return $result;
			}

			foreach ($events as $event)
			{
				$this->modifyEvent(
					$connection,
					$client,
					$event,
					$eventsMap,
					$calendar
				);
			}
		}

		$result->setData([
			'lastResult' => '[200] OK',
			'syncStatus' => true,
		]);

		return $result;
	}

	/**
	 * @param Connection $connection
	 * @param \CDavGroupdavClientCalendar $client
	 * @param array $event
	 * @param array $eventsMap
	 * @param array $calendar
	 *
	 * @return void
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	private function modifyEvent(
		Connection $connection,
		\CDavGroupdavClientCalendar $client,
		array $event,
		array $eventsMap,
		array $calendar
	): void
	{
		if (!array_key_exists($event['href'], $eventsMap))
		{
			return;
		}

		[$event, $exDate] = $this->mergeExternalEventWithLocal($eventsMap[$event['href']], $event, $client);

		$eventId = $this->modifySingleEvent(
			$connection,
			$event['calendar-data'],
			[
				'SECTION_ID' => $calendar['SECTION_ID'],
				'VERSION' => $eventsMap[$event['href']]['VERSION'],
				'EVENT_CONNECTION_ID' => $eventsMap[$event['href']]['EVENT_CONNECTION_ID'],
			]
		);

		if (is_array($event['calendar-data-ex']) && $eventId && count($event['calendar-data-ex']) > 0)
		{
			$this->modifyRecurrenceEvent(
				$connection,
				$event['calendar-data-ex'],
				[
					'PARENT_ID' => $eventId,
					'SECTION_ID' => $calendar['SECTION_ID'],
					'PERIOD_UNTIL' => $event['calendar-data']['PROPERTY_PERIOD_UNTIL'] ?? null,
				]
			);
		}
		else if ($exDate && $event['calendar-data']['ID'])
		{
			$this->deleteDuplicateExDates(
				$exDate,
				$event['calendar-data']['DATE_FROM'],
				$event['calendar-data']['ID'],
				$connection->getOwner()->getId(),
			);
		}
	}

	/**
	 * @param $exDate
	 * @param $dateFrom
	 * @param $eventId
	 * @param $userId
	 *
	 * @return void
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Exception
	 */
	private function deleteDuplicateExDates($exDate, $dateFrom, $eventId, $userId): void
	{
		global $DB;
		$exDates = \CCalendarEvent::GetExDate($exDate);
		$dtStartTimestamp = \CCalendar::Timestamp($dateFrom, false);
		$needToUpdate = false;
		foreach ($exDates as $date)
		{
			$dateTs = \CCalendar::Timestamp($date, false);
			if ($dateTs < $dtStartTimestamp)
			{
				$needToUpdate = true;
				break;
			}
		}

		if ($needToUpdate)
		{
			$childEvents = EventConnectionTable::query()
				->setSelect([
		            'EVENT_ID',
		            'VERSION',
		            'DATE_FROM' => 'EVENT.DATE_FROM',
		            'EVENT_CONNECTION_ID' => 'ID',
				])
				->where('EVENT.RECURRENCE_ID', $eventId)
				->where('EVENT.DELETED', 'N')
				->where('EVENT.OWNER_ID', $userId)
				->exec()
			;

			while ($child = $childEvents->fetch())
			{
				$sqlStr = "UPDATE b_calendar_event SET DELETED='Y' WHERE PARENT_ID = " . $child['EVENT_ID'] . ";";
				$DB->Query($sqlStr);

				EventConnectionTable::delete($child['EVENT_CONNECTION_ID']);
			}
		}
	}

	/**
	 * @param Connection $connection
	 * @param array $event
	 * @param array $additionalParams
	 *
	 * @return int|null
	 * @throws SystemException
	 * @throws \Bitrix\Calendar\Core\Base\BaseException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Exception
	 */
	private function modifySingleEvent(Connection $connection, array $event, array $additionalParams): ?int
	{
		$eventObject = $this->prepareEventParams(
			$event,
			$additionalParams['SECTION_ID'],
			$connection->getOwner()->getId()
		);

		if ($eventObject->getId())
		{
			$result = $this->mapperFactory->getEvent()->update($eventObject, [
				'userId' => $connection->getOwner()->getId(),
				'bAffectToDav' => false, // Used to prevent synchro with calDav again
				'bSilentAccessMeeting' => true,
				'autoDetectSection' => false,
				'originalFrom' => $connection->getVendor()->getCode(),
			]);
		}
		else
		{
			$result = $this->mapperFactory->getEvent()->create($eventObject, [
				'userId' => $connection->getOwner()->getId(),
				'bAffectToDav' => false, // Used to prevent synchro with calDav again
				'bSilentAccessMeeting' => true,
				'autoDetectSection' => false,
				'originalFrom' => $connection->getVendor()->getCode(),
			]);
		}

		if ($result->getId())
		{
			$data = [];
			// Prepare Data with outer params
			if ($event['ATTENDEE'] || $event['ORGANIZER_ENTITY'])
			{
				$this->parseInvitedAttendees($event, $data);
			}
			if ($event['ATTACH'])
			{
				$this->parseAttachments($event, $data);
			}
			if ($event['URL'])
			{
				$data['URL'] = $event['URL'];
			}

			if ($additionalParams['EVENT_CONNECTION_ID'])
			{
				EventConnectionTable::update($additionalParams['EVENT_CONNECTION_ID'], [
					'SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
					'ENTITY_TAG' => $event['MODIFICATION_LABEL'],
					'VERSION' => (string)$additionalParams['VERSION'],
					'VENDOR_VERSION_ID' => (string)$additionalParams['VERSION'],
					'DATA' => $data,
				]);
			}
			else
			{
				EventConnectionTable::add([
					'EVENT_ID' => (int)$result->getId(),
					'CONNECTION_ID' => $connection->getId(),
					'VENDOR_EVENT_ID' => $event['XML_ID'],
					'SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
					'ENTITY_TAG' => $event['MODIFICATION_LABEL'],
					'VERSION' => (string)$additionalParams['VERSION'],
					'VENDOR_VERSION_ID' => (string)$additionalParams['VERSION'],
					'DATA' => $data,
				]);
			}

			return (int)$result->getId();
		}

		return null;
	}

	/**
	 * @param Connection $connection
	 * @param array $importInstances
	 * @param array $additionalParams
	 *
	 * @return void
	 * @throws SystemException
	 * @throws \Bitrix\Calendar\Core\Base\BaseException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Exception
	 */
	private function modifyRecurrenceEvent(
		Connection $connection,
		array $importInstances,
		array $additionalParams
	): void
	{
		global $DB;
		[$importInstances, $importedInstancesDates] = $this->prepareInstanceEvents($importInstances);
		$parentEvent = \CCalendarEvent::GetById($additionalParams['PARENT_ID']);

		if ($parentEvent && \CCalendarEvent::CheckRecurcion($parentEvent))
		{
			$exDates = \CCalendarEvent::GetExDate($parentEvent['EXDATE']);
			$localInstances = EventConnectionTable::query()
				->setSelect([
		            'EVENT_ID',
		            'VERSION',
		            'DATE_FROM' => 'EVENT.DATE_FROM',
		            'EVENT_CONNECTION_ID' => 'ID',
		            'MEETING' => 'EVENT.MEETING',
					'IS_MEETING' => 'EVENT.IS_MEETING',
					'MEETING_HOST' => 'EVENT.MEETING_HOST',
 		            'ATTENDEES_CODES' => 'EVENT.ATTENDEES_CODES',
		            'ACCESSIBILITY' => 'EVENT.ACCESSIBILITY',
	            ])
				->where('EVENT.RECURRENCE_ID', $additionalParams['PARENT_ID'])
				->where('EVENT.DELETED', 'N')
				->where('EVENT.OWNER_ID', $connection->getOwner()->getId())
				->whereNot('EVENT.MEETING_STATUS', 'N')
				->exec()
			;

			$importedInstancesCount = count($importInstances);
			while ($localInstance = $localInstances->fetch())
			{
				$isActive = false;
				$localInstanceDate = \CCalendar::Date(\CCalendar::Timestamp($localInstance['DATE_FROM']), false);
				for ($i = 0; $i < $importedInstancesCount; $i++)
				{
					if ($localInstanceDate === $importedInstancesDates[$i])
					{
						$this->mergeInstanceParams($importInstances[$i], $localInstance);
						$isActive = true;

						break;
					}
				}

				if (!$isActive)
				{
					\CCalendarEvent::Delete([
						'id' => $localInstance['EVENT_ID'],
						'bMarkDeleted' => true,
						'bAffectToDav' => false,
						'originalFrom' => $connection->getVendor()->getCode(),
						'userId' => $connection->getOwner()->getId(),
					]);

					EventConnectionTable::delete($localInstance['EVENT_CONNECTION_ID']);
				}
			}

			foreach ($importInstances as $instance)
			{
				if (
					$additionalParams['PERIOD_UNTIL']
					&& \CCalendar::Timestamp($instance['DATE_FROM']) > \CCalendar::Timestamp($additionalParams['PERIOD_UNTIL'])
				)
				{
					continue;
				}
				$instance = $this->addParentDataToInstance($instance, $parentEvent);

				$this->modifySingleEvent(
					$connection,
					$instance,
					[
						'SECTION_ID' => $additionalParams['SECTION_ID'],
						'VERSION' => $instance['VERSION'] ?? 1,
						'EVENT_CONNECTION_ID' => $instance['EVENT_CONNECTION_ID'] ?? 0,
					]
				);

				if ($instance['RECURRENCE_ID_DATE'])
				{
					$exDates[] = \CCalendar::Date(\CCalendar::Timestamp($instance['RECURRENCE_ID_DATE']), false);
				}
			}
			$exDate = \CCalendarEvent::SetExDate($exDates);
			$DB->Query("UPDATE b_calendar_event 
				SET EXDATE  = '" . $exDate . "'
				WHERE PARENT_ID = " . $parentEvent['ID'] . "
			");
		}
	}

	/**
	 * @param $userId
	 *
	 * @return EO_DavConnection_Collection
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function getConnections($userId = null): EO_DavConnection_Collection
	{
		$query = DavConnectionTable::query()
			->setSelect(['*'])
			->whereIn('ACCOUNT_TYPE', [Icloud\Helper::ACCOUNT_TYPE])
			->where('ENTITY_TYPE', self::ENTITY_TYPE)
			->where('IS_DELETED', 'N')
			->setLimit(self::MAX_NUMBER)
			->setOrder(['SYNCHRONIZED' => 'ASC'])
		;
		if ($userId)
		{
			$query->where('ENTITY_ID', $userId);
		}

		return $query->exec()->fetchCollection();
	}

	/**
	 * @param Connection $connection
	 *
	 * @return \CDavGroupdavClientCalendar
	 */
	private function initClient(Connection $connection): \CDavGroupdavClientCalendar
	{
		$client = new \CDavGroupdavClientCalendar(
			$connection->getServer()->getScheme(),
			$connection->getServer()->getHost(),
			$connection->getServer()->getPort(),
			$connection->getServer()->getUserName(),
			$connection->getServer()->getPassword()
		);
		if (\CDav::UseProxy())
		{
			$proxy = \CDav::GetProxySettings();
			$client->SetProxy(
				$proxy['PROXY_SCHEME'],
				$proxy['PROXY_HOST'],
				$proxy['PROXY_PORT'],
				$proxy['PROXY_USERNAME'],
				$proxy['PROXY_PASSWORD']
			);
		}

		return $client;
	}

	/**
	 * @param Connection $connection
	 * @param array $calendars
	 *
	 * @return array
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectException
	 * @throws \Bitrix\Main\ObjectNotFoundException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Exception
	 */
	private function syncCalendarSections(
		Connection $connection,
		array $calendars
	): array
	{
		$calendarNames = [];
		$result = [];

		foreach ($calendars as $calendar)
		{
			if ($calendar['TYPE'] === 'VEVENT')
			{
				$calendarNames[$calendar['XML_ID']] = $calendar;
			}
		}

		$sectionsLink = SectionConnectionTable::query()
			->setSelect([
	            'SECTION_CONNECTION_ID' => 'ID',
	            'NAME' => 'SECTION.NAME',
	            'EXTERNAL_TYPE' => 'SECTION.EXTERNAL_TYPE',
	            'VENDOR_SECTION_ID',
	            'VERSION_ID',
	            'SECTION_ID',
			])
			->where('SECTION.CAL_TYPE', self::ENTITY_TYPE)
			->where('SECTION.OWNER_ID', $connection->getOwner()->getId())
			->where('CONNECTION_ID', $connection->getId())
			->exec()
		;

		while ($link = $sectionsLink->fetch())
		{
			$xmlId = $link['VENDOR_SECTION_ID'];
			if (empty($xmlId))
			{
				continue;
			}

			if (!array_key_exists($xmlId, $calendarNames))
			{
				$section = $this->mapperFactory->getSection()->getById($link['SECTION_ID']);
				if ($section)
				{
					(new IncomingManager($connection))->deleteSection($section, $link['SECTION_CONNECTION_ID']);
				}
			}
			else
			{
				if ($link['VERSION_ID'] !== $calendarNames[$xmlId]['MODIFICATION_LABEL'])
				{
					$fields =  [
						'ID' => (int)$link['SECTION_ID'],
						'NAME' => $link['EXTERNAL_TYPE'] === 'local'
							? $link['NAME']
							: $calendarNames[$xmlId]['NAME']
						,
						'DESCRIPTION' => $calendarNames[$xmlId]['DESCRIPTION'],
						'COLOR' => $calendarNames[$xmlId]['COLOR'],
					];

					\CCalendarSect::Edit([
						'arFields' => $fields,
						'bAffectToDav' => false,
						'originalFrom' => $connection->getVendor()->getCode(),
					]);

					SectionConnectionTable::update((int)$link['SECTION_CONNECTION_ID'], [
							'LAST_SYNC_DATE' => new DateTime(),
							'LAST_SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
							'VERSION_ID' => $calendarNames[$xmlId]['MODIFICATION_LABEL'],
						]
					);

					$result[] = [
						'XML_ID' => $xmlId,
						'SECTION_ID' => $link['SECTION_ID'],
						'SECTION_CONNECTION_ID' => $link['SECTION_CONNECTION_ID'],
						'SYNC_TOKEN' => $link['VERSION_ID'],
						'IS_NEW' => false,
					];
				}

				unset($calendarNames[$xmlId]);
			}
		}

		foreach ($calendarNames as $curXmlId => $calendar)
		{
			$fields = [
				'CAL_TYPE' => self::ENTITY_TYPE,
				'OWNER_ID' => $connection->getOwner()->getId(),
				'CREATED_BY' => $connection->getOwner()->getId(),
				'NAME' => $calendar['NAME'],
				'DESCRIPTION' => $calendar['DESCRIPTION'],
				'COLOR' => $calendar['COLOR'],
				'EXPORT' => ['ALLOW' => false],
				'EXTERNAL_TYPE' => $connection->getVendor()->getCode(),
			];

			$id = (int)\CCalendarSect::Edit([
				'arFields' => $fields,
				'bAffectToDav' => false,
				'originalFrom' => $connection->getVendor()->getCode(),
			]);

			if ($id)
			{
				$linkId = SectionConnectionTable::add([
                    'SECTION_ID' => $id,
                    'CONNECTION_ID' => $connection->getId(),
                    'VENDOR_SECTION_ID' => $curXmlId,
                    'ACTIVE' => 'Y',
                    'LAST_SYNC_DATE' => new DateTime(),
                    'LAST_SYNC_STATUS' => Dictionary::SYNC_STATUS['success'],
                    'VERSION_ID' => $calendar['MODIFICATION_LABEL'],
                ]);

				$result[] = [
					'XML_ID' => $curXmlId,
					'SECTION_ID' => $id,
					'SECTION_CONNECTION_ID' => $linkId->getId(),
					'SYNC_TOKEN' => $calendar['MODIFICATION_LABEL'],
					'IS_NEW' => true,
				];
			}
		}

		return $result;
	}

	/**
	 * @param Connection $connection
	 * @param int $sectionId
	 * @param array $events
	 *
	 * @return array
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Exception
	 */
	private function syncCalendarEvents(
		Connection $connection,
		int $sectionId,
		array $events
	): array
	{
		$linksMap = [];
		$result = [];

		$eventsLink = EventConnectionTable::query()
			->setSelect([
	            'EVENT_CONNECTION_ID' => 'ID',
	            'VENDOR_EVENT_ID',
	            'VERSION',
	            'ENTITY_TAG',
	            'EVENT_ID',
				'IS_MEETING' => 'EVENT.IS_MEETING',
	            'MEETING' => 'EVENT.MEETING',
	            'EXDATE' => 'EVENT.EXDATE',
	            'EVENT_PARENT_ID' => 'EVENT.PARENT_ID',
	            'ATTENDEES_CODES' => 'EVENT.ATTENDEES_CODES',
	            'ACCESSIBILITY' => 'EVENT.ACCESSIBILITY',
			])
			->where('EVENT.CAL_TYPE', self::ENTITY_TYPE)
			->where('EVENT.OWNER_ID', $connection->getOwner()->getId())
			->where('EVENT.SECTION_ID', $sectionId)
			->where('EVENT.DELETED', 'N')
			->where(Query::filter()
				->logic('or')
				->whereNot('EVENT.MEETING_STATUS', 'N')
				->whereNull('EVENT.MEETING_STATUS')
			)
			->whereNotNull('ENTITY_TAG')
			->exec()
		;

		while ($event = $eventsLink->fetch())
		{
			$linksMap[$event['VENDOR_EVENT_ID']] = $event;
		}

		foreach ($events as $index => $event)
		{
			if (isset($linksMap[$event['XML_ID']]))
			{
				// TODO: EVENT_ID = EVENT_PARENT_ID must be in core for edit and delete event
				if (
					(int)$event['STATUS'] === 200
					&& $linksMap[$event['XML_ID']]['EVENT_ID'] === $linksMap[$event['XML_ID']]['EVENT_PARENT_ID']
				)
				{
					if ($linksMap[$event['XML_ID']]['ENTITY_TAG'] !== $event['SYNC_TOKEN'])
					{
						$result[] = $this->prepareExistedEventParams($event['XML_ID'], $linksMap[$event['XML_ID']]);
					}
					else
					{
						unset($events[$index]);
					}
				}
				else if (
					(int)$event['STATUS'] === 404
					&& $linksMap[$event['XML_ID']]['EVENT_ID'] === $linksMap[$event['XML_ID']]['EVENT_PARENT_ID']
				)
				{
					\CCalendar::DeleteEvent(
						$linksMap[$event['XML_ID']]['EVENT_ID'],
						false,
						[
							'markDeleted' => true,
							'originalFrom' => $connection->getVendor()->getCode(),
							'checkPermissions' => false,
						]
					);

					EventConnectionTable::delete($linksMap[$event['XML_ID']]['EVENT_CONNECTION_ID']);
				}
			}
			else if ($event['SYNC_TOKEN'] && (int)$event['STATUS'] === 200)
			{
				$result[] = $this->prepareExistedEventParams($event['XML_ID']);
			}
		}

		return $result;
	}

	/**
	 * @param array $event
	 * @param int $sectionId
	 * @param int $entityId
	 *
	 * @return Event
	 * @throws \Bitrix\Main\SystemException
	 */
	private function prepareEventParams(array $event, int $sectionId, int $entityId): Event
	{
		$fields = [
			'ID' => (int)$event['ID'],
			'NAME' => $this->prepareName($event['NAME']),
			'CAL_TYPE' => self::ENTITY_TYPE,
			'DESCRIPTION' => $event['DESCRIPTION'] ?? '',
			'OWNER_ID' => $entityId,
			'CREATED_BY' => $entityId,
			'ATTENDEES_CODES' => ['U' . $entityId],
			'SECTIONS' => [$sectionId],
			'ACCESSIBILITY' => (int)$event['ID']
				? $event['ACCESSIBILITY']
				: ($event['PROPERTY_ACCESSIBILITY'] ?? 'busy')
			,
			'IS_MEETING' => $event['IS_MEETING'] ? true : null,
			'IMPORTANCE' => $event['IMPORTANCE'] ?? 'normal',
			'REMIND' => is_array($event['REMIND']) ? $event['REMIND'] : [],
			'RRULE' => is_array($event['RRULE']) ? $event['RRULE'] : [],
			'VERSION' => (int)$event['VERSION'],
			'PRIVATE_EVENT' => (bool)$event['PRIVATE_EVENT'],
			'DATE_FROM' => $event['DATE_FROM'],
			'DATE_TO' => $event['DATE_TO'],
			'TZ_FROM' => $event['TZ_FROM'],
			'TZ_TO' => $event['TZ_TO'],
			'SKIP_TIME' => $event['SKIP_TIME'] ? 'Y' : 'N',
			'ACTIVE' => 'Y',
			'DELETED' => 'N',
			'TIMESTAMP_X' => new DateTime(),
			'DATE_CREATE' => new DateTime(),
		];

		if ($event['RECURRENCE_ID'])
		{
			$fields['RECURRENCE_ID'] = $event['RECURRENCE_ID'];
		}

		if ($event['MEETING'])
		{
			$fields['MEETING'] = $event['MEETING'];
			$fields['MEETING_HOST'] = $event['MEETING']['MEETING_CREATOR'] ?? null;
		}
		else
		{
			$fields['MEETING'] = [
				'HOST_NAME' => \CCalendar::GetUserName($entityId),
				'NOTIFY' => true,
				'REINVITE' => false,
				'ALLOW_INVITE' => false,
				'MEETING_CREATOR' => $entityId,
				'HIDE_GUESTS' => true,
				'LANGUAGE_ID' => \CCalendar::getUserLanguageId($entityId)
			];
			$fields['MEETING_HOST'] = $entityId;
			$fields['MEETING_STATUS'] = 'H';
		}

		if ($event['ATTENDEES_CODES'])
		{
			$fields['ATTENDEES_CODES'] = $event['ATTENDEES_CODES'];
		}

		if ($event['RECURRENCE_ID_DATE'])
		{
			$fields['ORIGINAL_DATE_FROM'] = $event['RECURRENCE_ID_DATE'];
		}

		if ($fields['ORIGINAL_DATE_FROM'] && $fields['RECURRENCE_ID'])
		{
			$fields['RELATIONS'] = ['COMMENT_XML_ID' => \CCalendarEvent::GetEventCommentXmlId($fields)];
		}

		if ($event['SKIP_TIME'])
		{
			$fields['DATE_FROM'] = \CCalendar::Date(\CCalendar::Timestamp($fields['DATE_FROM'], false));
			$fields['DATE_TO'] = \CCalendar::Date(
				\CCalendar::Timestamp($fields['DATE_TO']) - \CCalendar::GetDayLen(),
				false
			);
		}

		if ($event['PROPERTY_REMIND_SETTINGS'])
		{
			if (is_array($event['PROPERTY_REMIND_SETTINGS']))
			{
				foreach ($event['PROPERTY_REMIND_SETTINGS'] as $remind)
				{
					$parsed = explode('_', $remind);
					$this->prepareRemind($parsed, $fields);
				}
			}
			else
			{
				$parsed = explode('_', $event['PROPERTY_REMIND_SETTINGS']);
				$this->prepareRemind($parsed, $fields);
			}
		}

		if ($event['PROPERTY_IMPORTANCE'])
		{
			$fields['IMPORTANCE'] = $event['PROPERTY_IMPORTANCE'];
		}

		if ($event['PROPERTY_LOCATION'])
		{
			$fields['LOCATION'] = Rooms\Util::unParseTextLocation($event['PROPERTY_LOCATION']);
		}

		if ($event['DETAIL_TEXT'])
		{
			$this->prepareDescription($event, $fields);
		}

		//RRULE SEGMENT
		if (
			$event['PROPERTY_PERIOD_TYPE']
			&& in_array($event['PROPERTY_PERIOD_TYPE'], ['DAILY', 'WEEKLY', 'MONTHLY', 'YEARLY'])
		)
		{
			$this->prepareRecurrenceRule($event, $fields);
		}

		return (new EventBuilderFromArray($fields))->build();
	}

	/**
	 * @param $calendarsList
	 * @param $connection
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function syncSections(Connection $connection, $calendarsList): array
	{
		$result = [];
		foreach ($calendarsList as $calendar)
		{
			$result[] = [
				'XML_ID' => $calendar['href'],
				'NAME' => $calendar['displayname'],
				'DESCRIPTION' => $calendar['calendar-description'],
				'TYPE' => $calendar['supported-calendar-component-set'] ?? '',
				'COLOR' => $calendar['calendar-color'],
				'MODIFICATION_LABEL' => $calendar['getctag'],
			];
		}

		return $this->syncCalendarSections(
			$connection,
			$result
		);
	}

	/**
	 * @param Connection $connection
	 * @param \CDavGroupdavClientCalendar $client
	 * @param array $calendar
	 * @param RequestLogger|null $logger
	 *
	 * @return array
	 * @throws SystemException
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 */
	private function getEventsToSync(
		Connection $connection,
		\CDavGroupdavClientCalendar $client,
		array $calendar,
		?RequestLogger $logger = null
	): array
	{
		$calendarEvents = [];
		$syncToken = !$calendar['IS_NEW'] ? $calendar['SYNC_TOKEN'] : null;

		$calendarItems = $client->GetCalendarItemsBySyncToken($calendar['XML_ID'], $syncToken, $logger);

		if (!$calendarItems || !is_array($calendarItems))
		{
			return $calendarEvents;
		}

		foreach ($calendarItems as $item)
		{
			if (
				(int)$item['status'] === 404
				|| mb_strpos($item['getcontenttype'], 'text/calendar') !== false
			)
			{
				$calendarEvents[] = [
					'XML_ID' => $client::getBasenameWithoutExtension($item['href']),
					'SYNC_TOKEN' => $item['getetag'] ?? null,
					'STATUS' => $item['status'],
				];
			}
		}

		$calendarEvents = $this->syncCalendarEvents(
			$connection,
			$calendar['SECTION_ID'],
			$calendarEvents
		);

		$eventsToUpdate = [];
		$eventsMap = [];
		foreach ($calendarEvents as $event)
		{
			$link = $client->GetRequestEventPath($calendar['XML_ID'], $event['XML_ID']);
			$eventsToUpdate[] = $link;
			$eventsMap[$link] = $event;
		}

		$calendarItems = [];
		if ($eventsToUpdate)
		{
			$calendarItems = $client->GetCalendarItemsList(
				$calendar['XML_ID'],
				$eventsToUpdate,
				true,
				1,
				[],
				$logger
			);
		}

		if (!$syncToken && $calendarItems)
		{
			$calendarItems = $this->applyTimeLimitForEvents($calendarItems);
		}

		return [$calendarItems, $eventsMap];
	}

	/**
	 * @param $events
	 *
	 * @return array
	 */
	private function applyTimeLimitForEvents($events): array
	{
		$timestamp = time() - self::TIME_SLICE;
		foreach ($events as $key => $event)
		{
			if ($event['calendar-data']['PROPERTY_PERIOD_UNTIL'])
			{
				if ((int)\CCalendar::Timestamp($event['calendar-data']['PROPERTY_PERIOD_UNTIL']) - $timestamp < 0)
				{
					unset($events[$key]);
				}
			}
			else if (
				$event['calendar-data']['DATE_TO']
				&& (int)\CCalendar::Timestamp($event['calendar-data']['DATE_TO']) - $timestamp < 0
			)
			{
				unset($events[$key]);
			}
		}

		return array_values($events);
	}

	/**
	 * @param array $event
	 * @param array $data
	 *
	 * @return void
	 */
	private function parseInvitedAttendees(array $event, array &$data): void
	{
		if ($event['ATTENDEE'])
		{
			/** @var \CDavICalendarProperty $attendee */
			foreach ($event['ATTENDEE'] as $attendee)
			{
				$attendeeData = [];

				if ($attendee->Parameter('CN'))
				{
					$attendeeData['CN'] = $attendee->Parameter('CN');
				}
				if ($attendee->Parameter('CUTYPE'))
				{
					$attendeeData['CUTYPE'] = $attendee->Parameter('CUTYPE');
				}
				if ($attendee->Parameter('PARTSTAT'))
				{
					$attendeeData['PARTSTAT'] = $attendee->Parameter('PARTSTAT');
				}
				if ($attendee->Parameter('ROLE'))
				{
					$attendeeData['ROLE'] = $attendee->Parameter('ROLE');
				}
				if ($attendee->Parameter('EMAIL'))
				{
					$attendeeData['EMAIL'] = $attendee->Parameter('EMAIL');
				}
				if ($attendee->Parameter('SCHEDULE-STATUS'))
				{
					$attendeeData['SCHEDULE-STATUS'] = $attendee->Parameter('SCHEDULE-STATUS');
				}
				if ($attendee->Value())
				{
					$attendeeData['VALUE'] = $attendee->Value();
				}

				$data['ATTENDEE'][] = $attendeeData;
			}
		}
		/** @var \CDavICalendarProperty $organizer */
		if ($organizer = $event['ORGANIZER_ENTITY'][0])
		{
			if ($organizer->Parameter('EMAIL'))
			{
				$data['ORGANIZER']['EMAIL'] = $organizer->Parameter('EMAIL');
			}
			if ($organizer->Parameter('CN'))
			{
				$data['ORGANIZER']['CN'] = $organizer->Parameter('CN');
			}
			if ($organizer->Value())
			{
				$data['ORGANIZER']['VALUE'] = $organizer->Value();
			}
		}
	}

	/**
	 * @param array $event
	 * @param array $data
	 *
	 * @return void
	 */
	private function parseAttachments(array $event, array &$data): void
	{
		/** @var \CDavICalendarProperty $attachment */
		foreach ($event['ATTACH'] as $attachment)
		{
			$attachmentData = [];
			if ($attachment->Parameter('FMTTYPE'))
			{
				$attachmentData['FMTTYPE'] = $attachment->Parameter('FMTTYPE');
			}
			if ($attachment->Parameter('SIZE'))
			{
				$attachmentData['SIZE'] = $attachment->Parameter('SIZE');
			}
			if ($attachment->Parameter('FILENAME'))
			{
				$attachmentData['FILENAME'] = $attachment->Parameter('FILENAME');
			}
			if ($attachment->Parameter('MANAGED-ID'))
			{
				$attachmentData['MANAGED-ID'] = $attachment->Parameter('MANAGED-ID');
			}
			if ($attachment->Value())
			{
				$attachmentData['VALUE'] = $attachment->Value();
			}

			$data['ATTACH'][] = $attachmentData;
		}
	}

	/**
	 * @param $parsed
	 * @param array $fields
	 *
	 * @return void
	 * @throws \Bitrix\Main\ObjectException
	 */
	private function prepareRemind($parsed, array &$fields): void
	{
		$cnt = count($parsed);
		if ($cnt === 2 && $parsed[1] === 'date')
		{
			$fields['REMIND'][] = [
				'type' => $parsed[1],
				'value' => new DateTime($parsed[0], 'Ymd\\THis\\Z'),
			];
		}
		else if ($cnt === 2 && $fields['SKIP_TIME'] === 'Y')
		{
			$fields['REMIND'][] = [
				'type' => 'daybefore',
				'before' => 1,
				'time' => 1440 - (int)$parsed[0] * 60,
			];
		}
		else if ($cnt === 2)
		{
			$fields['REMIND'][] = [
				'count' => (int)$parsed[0],
				'type' => $parsed[1],
			];
		}
		else if ($cnt === 3 && $parsed[2] === 'daybefore')
		{
			$fields['REMIND'][] = [
				'type' => $parsed[2],
				'before' => 0,
				'time' => (int)$parsed[0] * 60,
			];
		}
		else if ($cnt === 4 && $fields['SKIP_TIME'] === 'Y')
		{
			$fields['REMIND'][] = [
				'type' => 'daybefore',
				'before' => $parsed[0] + 1,
				'time' => 1440 - (int)$parsed[2] * 60,
			];
		}
		else if ($cnt === 4)
		{
			$fields['REMIND'][] = [
				'type' => $parsed[3],
				'count' => (int)$parsed[0] * 24 + $parsed[2],
			];
		}
	}

	/**
	 * @param EO_DavConnection $connection
	 *
	 * @return Connection
	 */
	private function createConnectionObject(EO_DavConnection $connection): Connection
	{
		return (new BuilderConnectionFromDM($connection))->build();
	}

	/**
	 * @param array $error
	 *
	 * @return string
	 */
	private function processError(array $error): string
	{
		return '[' . $error[0] . '] ' . $error[1];
	}

	/**
	 * @param string|null $name
	 *
	 * @return string
	 */
	private function prepareName(?string $name): string
	{
		if (!$name)
		{
			IncludeModuleLangFile($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/calendar/classes/general/calendar_js.php");
			$name = Loc::getMessage('EC_DEFAULT_ENTRY_NAME');
		}

		return $name;
	}

	/**
	 * @param array $event
	 * @param array $fields
	 *
	 * @return void
	 */
	private function prepareRecurrenceRule(array $event, array &$fields): void
	{
		$fields['RRULE']['FREQ'] = $event['PROPERTY_PERIOD_TYPE'];
		$fields['RRULE']['INTERVAL'] = $event['PROPERTY_PERIOD_COUNT'];

		if (!$fields['DT_LENGTH'] && $event['PROPERTY_EVENT_LENGTH'])
		{
			$fields['DT_LENGTH'] = (int)$fields['PROPERTY_EVENT_LENGTH'];
		}
		else
		{
			$fields['DT_LENGTH'] = $event['DT_TO_TS'] - $event['DT_FROM_TS'];
		}

		if ($fields['RRULE']['FREQ'] === 'WEEKLY' && $event['PROPERTY_PERIOD_ADDITIONAL'])
		{
			$fields['RRULE']['BYDAY'] = [];
			$days = explode(',', $event['PROPERTY_PERIOD_ADDITIONAL']);
			foreach ($days as $day)
			{
				$day = \CCalendar::WeekDayByInd($day, false);
				if ($day !== false)
				{
					$fields['RRULE']['BYDAY'][] = $day;
				}
			}
			$fields['RRULE']['BYDAY'] = implode(',', $fields['RRULE']['BYDAY']);
		}

		if ($event['PROPERTY_RRULE_COUNT'])
		{
			$fields['RRULE']['COUNT'] = $event['PROPERTY_RRULE_COUNT'];
		}
		else if ($event['PROPERTY_PERIOD_UNTIL'])
		{
			$fields['RRULE']['UNTIL'] = $event['PROPERTY_PERIOD_UNTIL'];
		}
		else
		{
			$fields['RRULE']['UNTIL'] = $event['DT_TO_TS'];
		}

		if ($event['EXDATE'])
		{
			$fields['EXDATE'] = $event['EXDATE'];
		}
	}

	/**
	* @param array $fields
	* @param array $event
	*
	* @return void
	 */
	private function prepareDescription(array $event, array &$fields): void
	{
		if ($fields['ATTENDEES_CODES'])
		{
			if (isset($event['MEETING']) && !empty($event['MEETING']['LANGUAGE_ID']))
			{
				$languageId = $event['MEETING']['LANGUAGE_ID'];
			}
			else
			{
				$languageId = \CCalendar::getUserLanguageId((int)$fields['OWNER_ID']);
			}

			$fields['DESCRIPTION'] = (new AttendeesDescription($languageId))
				->cutAttendeesFromDescription($event['DETAIL_TEXT']);
		}
		else
		{
			$fields['DESCRIPTION'] = $event['DETAIL_TEXT'];
		}
	}

	/**
	 * @param array $instance
	 * @param array $localInstance
	 *
	 * @return void
	 */
	private function mergeInstanceParams(array &$instance, array $localInstance): void
	{
		$instance['ID'] = (int)$localInstance['EVENT_ID'];
		$instance['EVENT_CONNECTION_VERSION'] = (int)$localInstance['VERSION'];
		$instance['EVENT_CONNECTION_ID'] = (int)$localInstance['EVENT_CONNECTION_ID'];

		if ($localInstance['MEETING'])
		{
			$instance['MEETING'] = unserialize($localInstance['MEETING'], ['allow_classes' => false]);
		}
		if ($localInstance['ATTENDEES_CODES'])
		{
			$instance['ATTENDEES_CODES'] = explode(',', $localInstance['ATTENDEES_CODES']);
		}
		if ($localInstance['IS_MEETING'])
		{
			$instance['IS_MEETING'] = (bool)$localInstance['IS_MEETING'];
		}
		if ($localInstance['MEETING_HOST'])
		{
			$instance['MEETING_HOST'] = $localInstance['MEETING_HOST'];
		}
		if ($localInstance['ACCESSIBILITY'])
		{
			$instance['ACCESSIBILITY'] = $localInstance['ACCESSIBILITY'];
		}
	}

	/**
	 * @param array $instance
	 * @param array $parentEvent
	 *
	 * @return array
	 */
	private function addParentDataToInstance(array $instance, array $parentEvent): array
	{
		if (!$instance['IS_MEETING'])
		{
			$instance['IS_MEETING'] = $parentEvent['IS_MEETING'];
		}
		if (!$instance['MEETING_HOST'])
		{
			$instance['MEETING_HOST'] = $parentEvent['MEETING_HOST'];
		}
		if (!$instance['MEETING'])
		{
			$instance['MEETING'] = $parentEvent['MEETING'];
		}
		if (!$instance['ATTENDEES_CODES'])
		{
			$instance['ATTENDEES_CODES'] = $parentEvent['ATTENDEES_CODES'];
		}

		$instance['VERSION'] = $instance['EVENT_CONNECTION_VERSION']
			? max($parentEvent['VERSION'], $instance['EVENT_CONNECTION_VERSION'])
			: $parentEvent['VERSION']
		;
		$instance['RECURRENCE_ID'] = $parentEvent['ID'];

		return $instance;
	}

	/**
	 * @param array $events
	 *
	 * @return array[]
	 *
	 * Removes data of same-date instances
	 */
	private function prepareInstanceEvents(array $events): array
	{
		$instances = [];
		$eventDates = [];
		$eventsCount = count($events);

		for ($i = $eventsCount - 1; $i >= 0; $i--)
		{
			$eventDate = \CCalendar::Date(\CCalendar::Timestamp($events[$i]['DATE_FROM']), false);
			if (!in_array($eventDate, $eventDates, true))
			{
				$instances[] = $events[$i];
				$eventDates[] = $eventDate;
			}
		}

		return [$instances, $eventDates];
	}

	/**
	 * @param string $xmlId
	 * @param array|null $link
	 *
	 * @return array
	 */
	private function prepareExistedEventParams(string $xmlId, ?array $link = null): array
	{
		return [
			'XML_ID' => $xmlId,
			'ID' => (int)$link['EVENT_ID'],
			'EVENT_CONNECTION_ID' => (int)$link['EVENT_CONNECTION_ID'],
			'EXDATE' => $link['EXDATE'] ?? null,
			'VERSION' => $link['VERSION'] ?? 1,
			'MEETING' => $link['MEETING']
				? unserialize($link['MEETING'], ['allowed_classes' => false])
				: null
			,
			'IS_MEETING' => (bool)$link['IS_MEETING'],
			'ATTENDEES_CODES' => $link['ATTENDEES_CODES']
				? explode(',', $link['ATTENDEES_CODES'])
				: null
			,
			'ACCESSIBILITY' => $link['ACCESSIBILITY'] ?? 'busy'
		];
	}

	/**
	 * @param array $existedEvent
	 * @param array $event
	 * @param \CDavGroupdavClientCalendar $client
	 *
	 * @return array
	 */
	private function mergeExternalEventWithLocal(
		array $existedEvent,
		array $event,
		\CDavGroupdavClientCalendar $client
	): array
	{
		$exDate = $existedEvent['EXDATE'];
		$event['calendar-data'] = array_merge($event['calendar-data'], [
			'ID' => $existedEvent['ID'],
			'XML_ID' => $client::getBasenameWithoutExtension($event['href']),
			'MODIFICATION_LABEL' => $event['getetag'],
			'MEETING' => $existedEvent['MEETING'],
			'IS_MEETING' => $existedEvent['IS_MEETING'],
			'ATTENDEES_CODES' => $existedEvent['ATTENDEES_CODES'],
			'ACCESSIBILITY' => $existedEvent['ACCESSIBILITY'],
		]);

		return [$event, $exDate];
	}
}