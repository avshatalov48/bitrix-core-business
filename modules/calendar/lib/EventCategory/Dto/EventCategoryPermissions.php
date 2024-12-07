<?php

namespace Bitrix\Calendar\EventCategory\Dto;

use Bitrix\Main\Type\Contract\Arrayable;

final class EventCategoryPermissions implements \JsonSerializable, Arrayable
{
	public function __construct(
		public readonly bool $edit,
		public readonly bool $delete,
	)
	{
	}

	public static function initRestricted(): self
	{
		return new self(edit: false, delete: false);
	}

	public function toArray(): array
	{
		return [
			'edit' => $this->edit,
			'delete' => $this->delete,
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
