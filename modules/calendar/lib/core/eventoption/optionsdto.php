<?php

namespace Bitrix\Calendar\Core\EventOption;

use Bitrix\Main\Type\Contract\Arrayable;

final class OptionsDto implements Arrayable, \JsonSerializable
{
	public function __construct(public readonly int $maxAttendees = 0)
	{
	}

	public static function fromArray(array $params): self
	{
		return new self($params['max_attendees'] ?? 0);
	}

	public function toArray(): array
	{
		return [
			'max_attendees' => $this->maxAttendees,
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}