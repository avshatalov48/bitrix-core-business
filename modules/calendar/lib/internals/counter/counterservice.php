<?php

namespace Bitrix\Calendar\Internals\Counter;

use Bitrix\Main\Application;
use Bitrix\Calendar\Internals\Counter\Event\EventCollection;

class CounterService
{
	private static $instance;
	private static $jobOn = false;
	private static $hitId;

	/**
	 * CounterService constructor.
	 */
	private function __construct()
	{
		self::$hitId = $this->generateHid();
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

	public static function proceedEvents(): void
	{
		$events = EventCollection::getInstance()->list();
		if (empty($events))
		{
			return;
		}

		(new Processor\OpenEvent())->process();
		(new Processor\Invite())->process();
		(new Processor\GroupInvite())->process();
		(new Processor\Sync())->process();
		(new Processor\Total())->process();
	}

	/**
	 * @param string $type
	 * @param array $data
	 */
	private function storeEvent(string $type, array $data): void
	{
		$event = new Event\Event(self::$hitId, $type);
		$event->setData($data);

		$this->getEventCollection()->push($event);
	}

	private function enableJob(): void
	{
		if (self::$jobOn)
		{
			return;
		}

		$application = Application::getInstance();
		$application && $application->addBackgroundJob(
			[self::class, 'proceedEvents'],
			[],
			Application::JOB_PRIORITY_LOW - 2,
		);

		self::$jobOn = true;
	}

	/**
	 * @return EventCollection
	 */
	private function getEventCollection(): EventCollection
	{
		return EventCollection::getInstance();
	}

	/**
	 * @return string
	 */
	private function generateHid(): string
	{
		return sha1(microtime(true) . mt_rand(10000, 99999));
	}
}
