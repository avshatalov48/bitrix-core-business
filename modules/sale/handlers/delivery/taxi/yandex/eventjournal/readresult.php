<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\EventJournal;

use Bitrix\Main\Result;
use Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\Journal\Event;

/**
 * Class ReadResult
 * @package Sale\Handlers\Delivery\Taxi\Yandex\EventJournal
 */
class ReadResult extends Result
{
	/** @var Event[] */
	private $events = [];

	/**
	 * @return Event[]
	 */
	public function getEvents(): array
	{
		return $this->events;
	}

	/**
	 * @param Event $event
	 */
	public function addEvent(Event $event)
	{
		$this->events[] = $event;
	}
}
