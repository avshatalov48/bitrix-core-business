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
	private static $instance;

	private $registry = [];

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
	}
}