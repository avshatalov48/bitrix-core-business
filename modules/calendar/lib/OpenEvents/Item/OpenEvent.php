<?php

namespace Bitrix\Calendar\OpenEvents\Item;

use Bitrix\Calendar\EventOption\Dto\EventOptionsDto;
use Bitrix\Main\Type\Contract\Arrayable;

class OpenEvent implements Arrayable
{
	public function __construct(
		public readonly int $id,
		public readonly string $name,
		public readonly int $dateFromTs,
		public readonly int $dateToTs,
		public readonly bool $isFullDay,
		public readonly ?bool $isAttendee,
		public readonly int $attendeesCount,
		public readonly int $creatorId,
		public readonly EventOptionsDto $eventOptions,
		public readonly int $categoryId,
		public readonly string $categoryName,
		public readonly ?int $categoryChannelId = 0,
		public readonly ?string $color = null,
		public readonly ?int $commentsCount = 0,
		public readonly ?int $threadId = 0,
		public readonly ?bool $isNew = false,
		public readonly ?string $rrule = null,
		public readonly ?string $rruleDescription = null,
		public readonly ?string $exdate = null,
	)
	{
	}

	public function toArray(): array
	{
		return [
			'id' => $this->id,
			'name' => $this->name,
			'dateFromTs' => $this->dateFromTs,
			'dateToTs' => $this->dateToTs,
			'isFullDay' => $this->isFullDay,
			'isAttendee' => $this->isAttendee,
			'attendeesCount' => $this->attendeesCount,
			'eventOptions' => $this->eventOptions?->toArray(),
			'creatorId' => $this->creatorId,
			'categoryId' => $this->categoryId,
			'categoryName' => $this->categoryName,
			'categoryChannelId' => $this->categoryChannelId,
			'color' => $this->color,
			'commentsCount' => $this->commentsCount,
			'threadId' => $this->threadId,
			'isNew' => $this->isNew,
			'rrule' => $this->rrule,
			'rruleDescription' => $this->rruleDescription,
			'exdate' => $this->exdate,
		];
	}
}
