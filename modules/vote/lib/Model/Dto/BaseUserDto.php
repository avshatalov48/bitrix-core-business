<?php

namespace Bitrix\Vote\Model\Dto;

use Bitrix\Main\Type\Contract\Arrayable;

class BaseUserDto implements Arrayable, \JsonSerializable
{
	public function __construct(
		public readonly int $id,
		public readonly ?string $name,
		public readonly ?string $image,
		public readonly ?string $workPosition,
	)
	{}

	public function toArray(): array
	{
		return [
			'ID' => $this->id,
			'NAME' => $this->name,
			'IMAGE' => $this->image,
			'WORK_POSITION' => $this->workPosition,
		];
	}

	public function jsonSerialize(): array
	{
		return $this->toArray();
	}
}