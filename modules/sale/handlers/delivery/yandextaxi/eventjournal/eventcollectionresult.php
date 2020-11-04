<?php

namespace Sale\Handlers\Delivery\YandexTaxi\EventJournal;

use Bitrix\Main\Result;
use Sale\Handlers\Delivery\YandexTaxi\Api\ApiResult\Journal\Event;

/**
 * Class EventCollectionResult
 * @package Sale\Handlers\Delivery\YandexTaxi\EventJournal
 * @internal
 */
final class EventCollectionResult extends Result
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
