<?php

namespace Bitrix\Socialnetwork\Internals\Counter;

use Bitrix\Main;
use Bitrix\Main\Application;
use Bitrix\Socialnetwork\Internals\Counter;
use Bitrix\Socialnetwork\Internals\Counter\Event\Event;
use Bitrix\Socialnetwork\Internals\Counter\Event\EventCollection;

/**
 * Class CounterService
 *
 * @package Bitrix\Socialnetwork\Internals\Counter
 */
class CounterService
{
	private static $instance;
	private static $isJobOn = false;

	/**
	 * CounterService constructor.
	 */
	private function __construct()
	{
		$this->enableJob();
	}

	/**
	 * @return CounterService
	 */
	public static function getInstance(): CounterService
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
		$event = new Event($type);
		$event->setData($data);

		$this->getEventCollection()->push($event);
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
		(new Counter\Event\WorkgroupEventProcessor())->process();
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
}
