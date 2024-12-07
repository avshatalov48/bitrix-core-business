<?php

namespace Bitrix\Calendar\EventOption\Dto;

use Bitrix\Main\Type\Contract\Arrayable;

final class EventOptionsDto implements \JsonSerializable, Arrayable
{
	public function __construct(
		public readonly ?int $maxAttendees
	)
	{
	}

	public static function fromArray(array $data): self
	{
		return new self(
			maxAttendees: $data['max_attendees'],
		);
	}

	public static function fromJson(string $json): ?self
	{
		$array = json_decode($json, true);
		if (!$array) {
			return null;
		}

		return self::fromArray($array);
	}

	public function toArray(): array
	{
		return [
			'maxAttendees' => $this->maxAttendees,
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}
