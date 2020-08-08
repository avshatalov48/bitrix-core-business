<?php

namespace Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\Journal;

/**
 * Class Result
 * @package Sale\Handlers\Delivery\Taxi\Yandex\Api\ApiResult\Journal
 */
class Result extends \Bitrix\Main\Result
{
	/** @var string */
	private $cursor;

	/** @var Event[]  */
	private $events = [];

	/**
	 * @return string
	 */
	public function getCursor()
	{
		return $this->cursor;
	}

	/**
	 * @param string $cursor
	 * @return Result
	 */
	public function setCursor(string $cursor): Result
	{
		$this->cursor = $cursor;

		return $this;
	}

	/**
	 * @param Event $event
	 */
	public function addEvent(Event $event)
	{
		$this->events[] = $event;
	}

	/**
	 * @return Event[]
	 */
	public function getEvents(): array
	{
		return $this->events;
	}
}
