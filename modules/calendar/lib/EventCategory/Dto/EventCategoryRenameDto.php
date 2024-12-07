<?php

namespace Bitrix\Calendar\EventCategory\Dto;

use Bitrix\Main\Type\Contract\Arrayable;

final class EventCategoryRenameDto implements Arrayable
{
	public function __construct(
		public readonly int $chatId,
		public readonly string $name
	)
	{

	}

	public function toArray(): array
	{
		return [
			'chatId' => $this->chatId,
			'name' => $this->name,
		];
	}
}
