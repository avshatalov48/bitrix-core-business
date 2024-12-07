<?php

namespace Bitrix\Calendar\EventCategory\Dto;

use Bitrix\Calendar\EventCategory\Enum\AttendeesUpdateTypeEnum;
use Bitrix\Main\Type\Contract\Arrayable;

final class EventCategoryAttendeesUpdateDto implements Arrayable
{
	public function __construct(
		public readonly int $chatId,
		public readonly AttendeesUpdateTypeEnum $type
	)
	{
	}

	public function toArray(): array
	{
		return [
			'chatId' => $this->chatId,
			'type' => $this->type->value,
		];
	}
}
