<?php

namespace Bitrix\Im\V2\Integration\AI\Dto;

class File implements \JsonSerializable
{
	public function __construct(
		public readonly string $name,
	) {}

	public function jsonSerialize(): array
	{
		return ['name' => $this->name];
	}
}