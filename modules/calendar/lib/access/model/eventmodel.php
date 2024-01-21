<?php

namespace Bitrix\Calendar\Access\Model;

use Bitrix\Calendar\Access\AccessibleEvent;
use Bitrix\Calendar\Core\Event\Event;
use Bitrix\Calendar\Core\Role\Role;
use Bitrix\Main\Access\AccessibleItem;

class EventModel implements AccessibleEvent
{
	private static array $cache = [];

	private int $id = 0;
	private int $ownerId = 0;
	private int $sectionId = 0;
	private string $sectionType = '';
	private string $eventType = '';
	private string $meetingStatus = '';
	private int $parentEventSectionId = 0;
	private string $parentEventSectionType = '';
	private int $parentEventOwnerId = 0;
	private int $parentEventId = 0;

	public static function createFromId(int $itemId = 0): AccessibleItem
	{
		if ($itemId <= 0)
		{
			return self::createNew();
		}

		if (!isset(static::$cache[$itemId]))
		{
			$model = new self();
			$model->setId($itemId);
			static::$cache[$itemId] = $model;
		}

		return static::$cache[$itemId];
	}

	public static function createNew(): self
	{
		return new self();
	}

	public static function createFromArray(array $fields): self
	{
		if (($fields['ID'] ?? null) && (int)$fields['ID'])
		{
			$model = self::createFromId((int)$fields['ID']);
		}
		else
		{
			$model = self::createNew();
		}

		if (($fields['OWNER_ID'] ?? null) && (int)$fields['OWNER_ID'])
		{
			$model->setOwnerId($fields['OWNER_ID']);
		}

		if ($fields['SECTION_ID'] ?? null)
		{
			$model->setSectionId((int)$fields['SECTION_ID']);
		}

		if (($fields['CAL_TYPE'] ?? null) && is_string($fields['CAL_TYPE']))
		{
			$model->setSectionType($fields['CAL_TYPE']);
		}

		if (($fields['EVENT_TYPE'] ?? null) && is_string($fields['EVENT_TYPE']))
		{
			$model->setEventType($fields['EVENT_TYPE']);
		}

		if (($fields['MEETING_STATUS'] ?? null) && is_string($fields['MEETING_STATUS']))
		{
			$model->setMeetingStatus($fields['MEETING_STATUS']);
		}

		if ((int)($fields['PARENT_ID'] ?? null))
		{
			$model->setParentEventId((int)$fields['PARENT_ID']);
		}

		if (
			(int)($fields['PARENT_ID'] ?? null)
			&& ($fields['ID'] ?? null) !== ($fields['PARENT_ID'] ?? null)
		)
		{
			$parentFields = \CCalendarSect::GetSectionByEventId((int)$fields['PARENT_ID']);
			if ($parentFields && is_array($parentFields))
			{
				$model->setParentEventSectionFields($parentFields);
			}
		}
		elseif ((int)($fields['ID'] ?? null) && ($fields['ID'] ?? null) === ($fields['PARENT_ID'] ?? null))
		{
			$model->setParentEventSectionFields($fields);
		}

		return $model;
	}

	public static function createFromObject(Event $event)
	{
		if ($event->getId() > 0)
		{
			$model = self::createFromId($event->getId());
		}
		else
		{
			$model = self::createNew();
		}

		$owner = $event->getOwner();
		if ($owner instanceof Role)
		{
			$model->setOwnerId($owner->getId());
		}

		$model
			->setSectionId($event->getSection()->getId())
			->setSectionType($event->getSection()->getType())
			->setEventType($event->getSpecialLabel())
			->setMeetingStatus($event->getMeetingStatus())
		;

		$parentFields =\CCalendarSect::GetSectionByEventId($event->getParentId());
		if ($parentFields && is_array($parentFields))
		{
			$model->setParentEventSectionFields($parentFields);
		}

		return $model;
	}

	public function setParentEventSectionFields(array $fields): self
	{
		if (($fields['OWNER_ID'] ?? null) && (int)$fields['OWNER_ID'])
		{
			$this->setParentEventOwnerId((int)$fields['OWNER_ID']);
		}

		if (($fields['SECTION_ID'] ?? null) && (int)$fields['SECTION_ID'])
		{
			$this->setParentEventSectionId((int)$fields['SECTION_ID']);
		}

		if (($fields['CAL_TYPE'] ?? null) && is_string($fields['CAL_TYPE']))
		{
			$this->setParentEventSectionType($fields['CAL_TYPE']);
		}

		return $this;
	}

	public function setId(int $id): self
	{
		$this->id = $id;

		return $this;
	}

	public function getId(): int
	{
		return $this->id;
	}

	public function setOwnerId(int $ownerId): self
	{
		$this->ownerId = $ownerId;

		return $this;
	}

	public function getOwnerId(): int
	{
		return $this->ownerId;
	}

	public function setSectionId(int $sectionId): self
	{
		$this->sectionId = $sectionId;

		return $this;
	}

	public function getSectionId(): int
	{
		return $this->sectionId;
	}

	public function setSectionType(string $sectionType): self
	{
		$this->sectionType = $sectionType;

		return $this;
	}

	public function getSectionType(): string
	{
		return $this->sectionType;
	}

	public function setEventType(string $eventType): self
	{
		$this->eventType = $eventType;

		return $this;
	}

	public function getEventType(): string
	{
		return $this->eventType;
	}

	public function setMeetingStatus(string $meetingStatus): self
	{
		$this->meetingStatus = $meetingStatus;

		return $this;
	}

	public function getMeetingStatus(): string
	{
		return $this->meetingStatus;
	}

	public function setParentEventSectionId(int $parentEventSectionId): self
	{
		$this->parentEventSectionId = $parentEventSectionId;

		return $this;
	}

	public function setParentEventId(int $parentEventId): self
	{
		$this->parentEventId = $parentEventId;

		return $this;
	}

	public function getParentEventSectionId(): int
	{
		return $this->parentEventSectionId;
	}

	public function setParentEventSectionType(string $parentEventSectionType): self
	{
		$this->parentEventSectionType = $parentEventSectionType;

		return $this;
	}

	public function getParentEventSectionType(): string
	{
		return $this->parentEventSectionType;
	}

	public function setParentEventOwnerId(int $parentEventOwnerId): self
	{
		$this->parentEventOwnerId = $parentEventOwnerId;

		return $this;
	}
	public function getParentEventOwnerId(): int
	{
		return $this->parentEventOwnerId;
	}

	public function getParentEventId(): int
	{
		return $this->parentEventId;
	}
}