<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage socialnetwork
 * @copyright 2001-2022 Bitrix
 */

namespace Bitrix\Socialnetwork\Internals\EventService;

class EventCollection
{
	private array $ids = [];
	private array $registry = [];

	private static $instance;

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
		$this->registry[$event->getHash()] = $event;
		$this->ids[] = $event->getId();
	}

	/**
	 * @return array
	 */
	public function getEventsId(): array
	{
		return $this->ids;
	}

	public function isEmpty(): bool
	{
		return empty($this->registry);
	}

	public function isDuplicate(Event $event): bool
	{
		return (isset($this->registry[$event->getHash()]));
	}
}