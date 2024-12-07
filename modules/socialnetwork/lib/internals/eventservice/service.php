<?php

namespace Bitrix\Socialnetwork\Internals\EventService;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Socialnetwork\Internals\EventService;

class Service
{
	private const JOB_PRIORITY = Application::JOB_PRIORITY_LOW - 5;
	private const LOCK_KEY = 'sonet.eventlock';
	private static Service|null $instance = null;
	private static bool $isJobOn = false;
	private static string $hitId;

	/**
	 * EventService constructor.
	 */
	private function __construct()
	{
		self::$hitId = $this->generateHid();
		$this->enableJob();
		// TODO: spaces stub
		// $this->handleLostEvents();
	}

	/**
	 * @return Service
	 */
	public static function getInstance(): Service
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @param string $type
	 * @param array $data
	 */
	public static function addEvent(string $type, array $data): void
	{
		self::getInstance()->pushEvent($type, $data);
		// TODO: spaces stub
		return;

		// self::getInstance()->storeEvent($type, $data);
	}

	public static function proceedEvents(): void
	{
		(new EventService\Processors\WorkGroupEventProcessor())->process();

		// TODO: spaces stub
		return;

		if ((EventCollection::getInstance())->isEmpty())
		{
			Application::getConnection()->unlock(self::LOCK_KEY);
			return;
		}

		$service = self::getInstance();

		(new EventService\Processors\SpaceEventPreProcessor())->process();
		(new EventService\Processors\SpaceEventProcessor())->process();

		$service->done();
	}

	/**
	 * @param string $type
	 * @param array $data
	 */
	private function storeEvent(string $type, array $data = []): void
	{
		$event = EventService\Event\Factory::buildEvent(self::$hitId, $type, $data);

		if ($this->getEventCollection()->isDuplicate($event))
		{
			return;
		}

		$eventId = $this->saveToDb($event);
		$event->setId($eventId);

		$this->getEventCollection()->push($event);
	}

	private function pushEvent(string $type, array $data = []): void
	{
		if (!in_array($type, EventDictionary::WORKGROUP_EVENTS_SUPPORTED, true))
		{
			return;
		}

		$event = EventService\Event\Factory::buildEvent(self::$hitId, $type, $data);

		if ($this->getEventCollection()->isDuplicate($event))
		{
			return;
		}

		$this->getEventCollection()->push($event);
	}

	private function handleLostEvents(): void
	{
		if (!Application::getConnection()->lock(self::LOCK_KEY))
		{
			return;
		}

		$events = EventTable::getLostEvents();
		if (empty($events))
		{
			return;
		}

		foreach ($events as $row)
		{
			$event = EventService\Event\Factory::buildEvent(
				$row['HID'],
				$row['TYPE'],
				Main\Web\Json::decode($row['DATA']),
				$row['ID']
			);

			$this->getEventCollection()->push($event);
		}
	}

	/**
	 *
	 */
	private function enableJob(): void
	{
		if (!self::$isJobOn)
		{
			$application = Application::getInstance();
			$application && $application->addBackgroundJob(
				[ __CLASS__, 'proceedEvents' ],
				[],
				self::JOB_PRIORITY
			);

			self::$isJobOn = true;
		}
	}

	/**
	 * @return EventCollection
	 */
	private function getEventCollection(): EventCollection
	{
		return EventCollection::getInstance();
	}

	/**
	 * @param string $type
	 * @param array $data
	 * @return int
	 */
	private function saveToDb(Event $event): int
	{
		try
		{
			$res = EventTable::add([
				'HID' => self::$hitId,
				'TYPE' => $event->getType(),
				'DATA' => Main\Web\Json::encode($event->getData()),
				'LOG_DATA' => null,
				'PROCESSED' => Main\Type\DateTime::createFromTimestamp(0),
			]);
		}
		catch (\Exception $e)
		{
			return 0;
		}

		return (int)$res->getId();
	}

	/**
	 *
	 */
	private function done(): void
	{
		$ids = $this->getEventCollection()->getEventsId();
		if (empty($ids))
		{
			return;
		}

		EventTable::markProcessed([
			'@ID' => $ids
		]);

		Application::getConnection()->unlock(self::LOCK_KEY);
	}

	/**
	 * @return string
	 */
	private function generateHid(): string
	{
		return sha1(microtime(true) . mt_rand(10000, 99999));
	}
}
