<?php

namespace Bitrix\Socialnetwork\Internals\EventService;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Socialnetwork\Internals\EventService;

/**
 * Class Service
 *
 * @package Bitrix\Socialnetwork\Internals\EventService\Service
 */
class Service
{
	protected static $instance;
	private static $isJobOn = false;

	protected $oldFields = [];
	protected $newFields = [];

	/**
	 * EventService constructor.
	 */
	private function __construct()
	{
		$this->enableJob();
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
		self::getInstance()->storeEvent($type, $data);
	}

	/**
	 * @param string $type
	 * @param array $data
	 */
	private function storeEvent(string $type, array $data = []): void
	{
		$event = $this->getEventInstance($type);
		$event->setData($data);

		$this->getEventCollection()->push($event);
	}

	private function getEventInstance($type): Event
	{
		switch ($type)
		{
			case EventDictionary::EVENT_WORKGROUP_ADD:
			case EventDictionary::EVENT_WORKGROUP_BEFORE_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_DELETE:
				$event = new EventService\Event\WorkgroupEvent($type);
				break;
			case EventDictionary::EVENT_WORKGROUP_USER_ADD:
			case EventDictionary::EVENT_WORKGROUP_USER_UPDATE:
			case EventDictionary::EVENT_WORKGROUP_USER_DELETE:
				$event = new EventService\Event\WorkgroupUserEvent($type);
				break;
			default:
				$event = new EventService\Event($type);
		}

		return $event;
	}

	/**
	 * @throws Main\ArgumentException
	 * @throws Main\DB\SqlQueryException
	 * @throws Main\LoaderException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function proceedEvents(): void
	{
		(new Event\WorkgroupEvent())->process();
//		(new Event\WorkgroupUserEvent())->process();
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
				Application::JOB_PRIORITY_LOW - 2
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

	public function setOldFields($oldFields): void
	{
		$this->oldFields = $oldFields;
	}

	public function getOldFields(): array
	{
		return $this->oldFields;
	}

	public function setNewFields($newFields): void
	{
		$this->newFields = $newFields;
	}

	public function getNewFields(): array
	{
		return $this->newFields;
	}
}
