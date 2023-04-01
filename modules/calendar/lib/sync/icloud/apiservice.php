<?php
	
namespace Bitrix\Calendar\Sync\Icloud;

use Bitrix\Calendar\Core\Builders\EventBuilderFromEntityObject;
use Bitrix\Calendar\Core\Builders\EventCloner;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Section\Section;
use Bitrix\Calendar\Core\Base\Date;
use Bitrix\Calendar\Internals\EventTable;
use Bitrix\Calendar\Sync\Entities\SyncEvent;
use Bitrix\Dav\Integration\Calendar\RecurrenceEventBuilder;
use Bitrix\Main\ORM\Query\Query;

class ApiService
{
	/** @var ?ApiClient $apiClient */
	protected ?ApiClient $apiClient = null;
	/** @var ?\CDavGroupdavClientCalendar $davClient */
	protected ?\CDavGroupdavClientCalendar $davClient = null;
	/** @var Helper $helper */
	protected Helper $helper;
	/** @var ?array $error */
	protected ?array $error = null;

	/**
	 * @param array $server
	 * @param int|null $userId
	 *
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function __construct(array $server = [], int $userId = null)
	{
		$this->helper = new Helper();
		if ($server)
		{
			$this->davClient = $this->createDavInstance(
				$server['SERVER_SCHEME'],
				$server['SERVER_HOST'],
				$server['SERVER_PORT'],
				$server['SERVER_USERNAME'],
				$server['SERVER_PASSWORD']
			);

			$this->apiClient = new ApiClient($this->davClient, $userId);
		}
	}

	/**
	 * @param $connection
	 * @param $server
	 *
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function getPrinciples($connection, $server): ?string
	{
		$userId = \CCalendar::GetUserId();
		$davClient = $this->createDavInstance(
			$server['scheme'],
			$server['host'],
			$server['port'],
			$connection['SERVER_USERNAME'],
			$connection['SERVER_PASSWORD']
		);
		
		$this->apiClient = new ApiClient($davClient, $userId);
		$principlesXml = $this->apiClient->propfind(
			$server['path'],
			['current-user-principal'],
			null,
			0
		);
		if ($principlesXml)
		{
			return $this->getXmlStringData(
				$principlesXml,
				'/response/propstat/prop/current-user-principal/href'
			);
		}
		
		return null;
	}

	/**
	 * @param $connection
	 * @param $server
	 *
	 * @return string|null
	 * @throws \Bitrix\Main\ArgumentNullException
	 */
	public function getCalendarPath($connection, $server): ?string
	{
		$userId = \CCalendar::GetUserId();
		$davClient = $this->createDavInstance(
			$server['scheme'],
			$server['host'],
			$server['port'],
			$connection['SERVER_USERNAME'],
			$connection['SERVER_PASSWORD']
		);
		
		$this->apiClient = new ApiClient($davClient, $userId);
		$calendarXml = $this->apiClient->propfind(
			$server['path'],
			[['calendar-home-set', 'urn:ietf:params:xml:ns:caldav']],
			null,
			0
		);
		
		if ($calendarXml)
		{
			return $this->getXmlStringData(
				$calendarXml,
				'/response/propstat/prop/calendar-home-set/href'
			);
		}
		
		return null;
	}

	/**
	 * @param $connection
	 * @param $server
	 *
	 * @return array|null
	 */
	public function getCalendarList($connection, $server): ?array
	{
		$davClient = $this->createDavInstance(
			$server['scheme'],
			$server['host'],
			$server['port'],
			$connection['SERVER_USERNAME'],
			$connection['SERVER_PASSWORD']
		);
		
		$calendars = $davClient->GetCalendarList($server['path']);
		if (!is_array($calendars) || empty($calendars))
		{
			return null;
		}
		
		return $calendars;
	}

	/**
	 * @param string $path
	 * @param Event $event
	 *
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function createEvent(string $path, Event $event): ?array
	{
		$event->setUid(VendorSyncService::generateUuid());
		$xmlId = $event->getUid();

		return $this->editEvent($path, $xmlId, $event);
	}

	/**
	 * @param string $path
	 * @param Event $event
	 * @param array|null $data
	 *
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function updateEvent(string $path, Event $event, ?array $data): ?array
	{
		$xmlId = $event->getUid();
		if (!$xmlId)
		{
			return null;
		}

		if ($event->getExcludedDateCollection()->getCollection())
		{
			return $this->saveInstance($path, $event, $data);
		}
		
		$eventPath = $this->davClient->GetRequestEventPath($path, $xmlId);

		return $this->editEvent($eventPath, $xmlId, $event, $data);
	}

	/**
	 * @param string $path
	 * @param Event $event
	 *
	 * @return bool|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function deleteEvent(string $path, Event $event): ?bool
	{
		$xmlId = $event->getUid();
		$acceptCodes = [200, 201, 204, 404];

		if (!$xmlId)
		{
			return null;
		}
		
		$eventPath = $this->davClient->GetRequestEventPath($path, $xmlId);
		
		$result = (int)$this->apiClient->delete($eventPath);

		if ($this->davClient->getError())
		{
			$this->addError($this->davClient->getError());
		}

		if (in_array($result, $acceptCodes))
		{
			return true;
		}
		
		return null;
	}

	/**
	 * @param string $path
	 * @param Event $event
	 * @param array|null $data
	 * @param Date|null $excludeDate
	 *
	 * @return array|null
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function saveInstance(string $path, Event $event, ?array $data, Date $excludeDate = null): ?array
	{
		$xmlId = $event->getUid();
		if (!$xmlId)
		{
			return null;
		}

		$event = (new EventCloner($event))->build();
		[$eventPath, $calendarData] = $this->prepareInstanceData($event, $path, $xmlId, $data, $excludeDate);

		return $this->sendPutAction($eventPath, $calendarData);
	}

	/**
	 * @param string $path
	 * @param SyncEvent $recurrenceEvent
	 *
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	public function saveRecurrence(string $path, SyncEvent $recurrenceEvent): ?array
	{
		if (!$recurrenceEvent->getEvent()->getUid())
		{
			$recurrenceEvent->getEvent()->setUid(VendorSyncService::generateUuid());
		}
		$xmlId = $recurrenceEvent->getEvent()->getUid();

		[$eventPath, $calendarData] = $this->prepareRecurrenceData($recurrenceEvent, $path, $xmlId);

		return $this->sendPutAction($eventPath, $calendarData);
	}

	/**
	 * @param string $path
	 * @param Section $section
	 *
	 * @return array|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function createSection(string $path, Section $section): ?array
	{
		$content = SectionBuilder::getInstance()->getCreateSectionContent($section);
		$result = (int)$this->apiClient->mkcol($path, $content);

		if ($this->davClient->getError())
		{
			$this->addError($this->davClient->getError());
		}

		if ($result === 200 || $result === 201)
		{
			$result = $this->davClient->GetCalendarList($path);
			if ($result && is_array($result))
			{
				return [
					'XML_ID' => $result[0]['href'],
					'MODIFICATION_LABEL' => $result[0]['getctag'],
				];
			}
		}

		return null;
	}

	/**
	 * @param string $path
	 * @param Section $section
	 *
	 * @return array|int[]|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function updateSection(string $path, Section $section): ?array
	{
		$content = SectionBuilder::getInstance()->getUpdateSectionContent($section);
		$result = (int)$this->apiClient->proppatch($path, $content);

		if ($this->davClient->getError())
		{
			$this->addError($this->davClient->getError());
		}

		if ($result === 207)
		{
			$result = $this->davClient->GetCalendarList($path);
			if ($result && is_array($result))
			{
				return [
					'XML_ID' => $result[0]['href'],
					'MODIFICATION_LABEL' => $result[0]['getctag'],
				];
			}
		}
		else
		{
			return [
				'ERROR' => $result,
			];
		}

		return null;
	}

	/**
	 * @param string $path
	 *
	 * @return bool|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	public function deleteSection(string $path): ?bool
	{
		$result = (int)$this->apiClient->delete($path);

		if ($this->davClient->getError())
		{
			$this->addError($this->davClient->getError());
		}

		$acceptCodes = [200, 201, 204, 404];

		if (in_array($result, $acceptCodes))
		{
			return true;
		}

		return null;
	}

	public function getSectionsList($path)
	{
		return $this->davClient->GetCalendarList($path);
	}

	public function getEventsList($path, $syncToken): ?array
	{
		return $this->davClient->GetCalendarItemsBySyncToken($path, $syncToken);
	}

	public function getEventsListWithHref($path, $hrefs): ?array
	{
		return $this->davClient->GetCalendarItemsList($path, $hrefs, true);
	}
	
	public function prepareUrl(string $url): array
	{
		$parsed = parse_url($url);
		if (empty($parsed['port']))
		{
			$parsed['port'] = ($parsed['scheme'] === 'https'
				? 443
				: 80
			);
		}
		
		return $parsed;
	}

	/**
	 * @param $xml
	 * @param $path
	 *
	 * @return string
	 */
	private function getXmlStringData($xml, $path): string
	{
		$data = '';
		$responsePath = $xml->GetPath('/*/response');
		foreach ($responsePath as $response)
		{
			if (!$data)
			{
				$dataXml = $response->GetPath($path);
				if (!empty($dataXml))
				{
					$data = urldecode($dataXml[0]->GetContent());
				}
			}
		}
		
		return $data;
	}

	/**
	 * @param string $scheme
	 * @param string $host
	 * @param string $port
	 * @param string $username
	 * @param string $password
	 *
	 * @return \CDavGroupdavClientCalendar
	 */
	private function createDavInstance(
		string $scheme,
		string $host,
		string $port,
		string $username,
		string $password
	): \CDavGroupdavClientCalendar
	{
		$davClient = new \CDavGroupdavClientCalendar(
			$scheme,
			$host,
			$port,
			$username,
			$password,
		);
		$davClient->SetPrivateIp(false);
		
		return $davClient;
	}

	/**
	 * @param string $path
	 * @param string|null $xmlId
	 *
	 * @return string
	 */
	private function getPath(string $path, ?string $xmlId): string
	{
		if (mb_substr($path, -mb_strlen('/' . $xmlId . '.ics')) != '/' . $xmlId . '.ics')
		{
			$path = rtrim($path, '/');
			$path .= '/' . $xmlId . '.ics';
		}

		return $path;
	}

	/**
	 * @param string $path
	 * @param string|null $xmlId
	 * @param Event $event
	 * @param array|null $data
	 *
	 * @return array|null
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function editEvent(
		string $path,
		?string $xmlId,
		Event $event,
		?array $data = null
	): ?array
	{
		$path = $this->getPath($path, $xmlId);
		$calendarData = EventBuilder::getInstance()->getContent($event, $data);
		if ($calendarData)
		{
			$calendarData = (new \CDavICalendar($calendarData))->Render();
		}

		return $this->sendPutAction($path, $calendarData);
	}

	/**
	 * @param Event $event
	 * @param string $path
	 * @param string $xmlId
	 * @param array|null $data
	 * @param Date|null $excludeDate
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function prepareInstanceData(
		Event $event,
		string $path,
		string $xmlId,
		?array $data,
		Date $excludeDate = null
	): array
	{
		$instancesOriginalDate = [];
		$exDates = $event->getExcludedDateCollection();
		$excludedInstance = $excludeDate ? $excludeDate->format('Ymd') : null;

		$instances = EventTable::query()
			->setSelect(['*'])
			->where('RECURRENCE_ID', $event->getParentId())
			->where('DELETED', 'N')
			->where('OWNER_ID', $event->getOwner()->getId())
			// ->whereNot('MEETING_STATUS', 'N')
			->where(Query::filter() // TODO: it's better to optimize it and don't use 'OR' logic here
				 ->logic('or')
				 ->whereNot('MEETING_STATUS', 'N')
				 ->whereNull('MEETING_STATUS')
			)
			->exec()->fetchCollection()
		;

		foreach ($instances as $instance)
		{
			$originalDate = $instance->getOriginalDateFrom()
				? $instance->getOriginalDateFrom()->format('Ymd')
				: $instance->getDateFrom()->format('Ymd')
			;
			if ($originalDate === $excludedInstance)
			{
				$instances->remove($instance);
				continue;
			}

			$instancesOriginalDate[] = $originalDate;
		}

		if ($exDates)
		{
			/**
			 * @var int $key
			 * @var Date $exDate
			 */
			foreach ($exDates->getCollection() as $key => $exDate)
			{
				if (in_array($exDate->format('Ymd'), $instancesOriginalDate, true))
				{
					$exDates->remove($key);
				}
			}
			$event->setExcludedDateCollection($exDates);
		}

		$eventPath = $this->davClient->GetRequestEventPath($path, $xmlId);
		$eventPath = $this->getPath($eventPath, $xmlId);
		$calendarData[] = EventBuilder::getInstance()->getContent($event, $data);

		foreach ($instances as $instance)
		{
			$instanceObject = (new EventBuilderFromEntityObject($instance))->build();
			$instanceObject->setUid($xmlId);
			$calendarData[] = EventBuilder::getInstance()->getContent($instanceObject, $data);
		}
		if ($calendarData)
		{
			$calendarData = (new RecurrenceEventBuilder($calendarData))->Render();
		}

		return [$eventPath, $calendarData];
	}

	/**
	 * @param SyncEvent $recurrenceEvent
	 * @param string $path
	 * @param $xmlId
	 *
	 * @return array
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Bitrix\Main\LoaderException
	 * @throws \Bitrix\Main\ObjectPropertyException
	 * @throws \Bitrix\Main\SystemException
	 */
	private function prepareRecurrenceData(SyncEvent $recurrenceEvent, string $path, $xmlId): array
	{
		$instanceDates = [];
		$exDates = $recurrenceEvent->getEvent()->getExcludedDateCollection();

		/** @var SyncEvent $instance */
		foreach ($recurrenceEvent->getInstanceMap()->getCollection() as $instance)
		{
			$instanceDates[] = $instance->getEvent()->getOriginalDateFrom()
				? $instance->getEvent()->getOriginalDateFrom()->format('Ymd')
				: $instance->getEvent()->getStart()->format('Ymd')
			;
		}

		if ($exDates)
		{
			/**
			 * @var int $key
			 * @var Date $date
			 */
			foreach ($exDates->getCollection() as $key => $date)
			{
				if (in_array($date->format('Ymd'), $instanceDates, true))
				{
					$exDates->remove($key);
				}
			}

			$recurrenceEvent->getEvent()->setExcludedDateCollection($exDates);
		}

		$eventPath = $this->davClient->GetRequestEventPath($path, $xmlId);
		$eventPath = $this->getPath($eventPath, $xmlId);
		$calendarData[] = EventBuilder::getInstance()->getContent($recurrenceEvent->getEvent());

		foreach ($recurrenceEvent->getInstanceMap()->getCollection() as $instance)
		{
			$instance->getEvent()->setUid($xmlId);
			$calendarData[] = EventBuilder::getInstance()->getContent($instance->getEvent());
		}

		if ($calendarData)
		{
			$calendarData = (new RecurrenceEventBuilder($calendarData))->Render();
		}

		return [$eventPath, $calendarData];
	}

	/**
	 * @param string $path
	 * @param $calendarData
	 *
	 * @return array|null
	 * @throws \Bitrix\Main\LoaderException
	 */
	private function sendPutAction(string $path, $calendarData): ?array
	{
		$result = (int)$this->apiClient->put($path, $calendarData);

		if ($this->davClient->getError())
		{
			$this->addError($this->davClient->getError());
		}

		if ($result === 201 || $result === 204)
		{
			$result = $this->davClient->GetCalendarItemsList(
				$path,
				null,
				false,
				2
			);

			if ($result && is_array($result))
			{
				return [
					'XML_ID' => $this->davClient::getBasenameWithoutExtension($result[0]['href']),
					'MODIFICATION_LABEL' => $result[0]['getetag'],
				];
			}
		}

		return null;
	}

	/**
	 * @param array $error
	 * @return void
	 */
	private function addError(array $error)
	{
		$this->error = $error;
	}

	/**
	 * @return array|null
	 */
	public function getError(): ?array
	{
		return $this->error;
	}
}
