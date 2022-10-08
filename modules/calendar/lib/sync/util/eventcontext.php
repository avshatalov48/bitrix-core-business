<?php

namespace Bitrix\Calendar\Sync\Util;

use Bitrix\Calendar\Sync\Connection\EventConnection;
use Bitrix\Calendar\Sync\Connection\SectionConnection;

/**
 * @property mixed|string|null $location
 */
class EventContext extends Context
{
	/**
	 * @var SectionConnection|null
	 */
	protected ?SectionConnection $sectionConnection = null;
	/**
	 * @var EventConnection|null
	 */
	protected ?EventConnection $eventConnection = null;

	/**
	 * @param SectionConnection|null $sectionConnection
	 * @return EventContext
	 */
	public function setSectionConnection(?SectionConnection $sectionConnection): EventContext
	{
		$this->sectionConnection = $sectionConnection;

		return $this;
	}

	/**
	 * @param EventConnection|null $eventConnection
	 * @return EventContext
	 */
	public function setEventConnection(?EventConnection $eventConnection): EventContext
	{
		$this->eventConnection = $eventConnection;

		return $this;
	}

	/**
	 * @return EventConnection|null
	 */
	public function getEventConnection(): ?EventConnection
	{
		return $this->eventConnection;
	}

	/**
	 * @return SectionConnection|null
	 */
	public function getSectionConnection(): ?SectionConnection
	{
		return $this->sectionConnection;
	}
}
