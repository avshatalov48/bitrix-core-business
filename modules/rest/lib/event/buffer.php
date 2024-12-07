<?php

namespace Bitrix\Rest\Event;

use Bitrix\Main\Error;
use Bitrix\Main\Result;

class Buffer
{
	private static Buffer $instance;
	private array $events = [];

	public static function getInstance(): self
	{
		if (!isset(self::$instance))
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	public function addEvent(array $event): Result
	{
		$result = new Result();

		if (!$this->isEventDuplicate($event))
		{
			$this->events[] = $event;
		}
		else
		{
			$result->addError(new Error('Event already added'));
		}

		return $result;
	}

	public function getEvents(): array
	{
		return $this->events;
	}

	private function isEventDuplicate(array $event): bool
	{
		return in_array($event, $this->events, true);
	}

	private function __construct()
	{
	}

	private function __clone()
	{
	}
}