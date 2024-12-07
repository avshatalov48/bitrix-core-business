<?php

namespace Bitrix\Calendar\Internals\Counter\Event;

class EventCollection
{
	private static $instance;
	private array $registry = [];
	private array $ids = [];

	/**
	 * CounterService constructor.
	 */
	private function __construct()
	{

	}

	/**
	 * @return self
	 */
	public static function getInstance(): self
	{
		if (!self::$instance)
		{
			self::$instance = new self();
		}

		return self::$instance;
	}

	/**
	 * @return array
	 */
	public function list(): array
	{
		return $this->registry;
	}

	/**
	 * @param Event $event
	 */
	public function push(Event $event): void
	{
		$this->registry[] = $event;
		$this->ids[] = $event->getId();
	}

	/**
	 * @return array
	 */
	public function getEventsId(): array
	{
		return $this->ids;
	}

}