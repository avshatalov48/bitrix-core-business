<?php

namespace Bitrix\Im\V2\Integration\AI\Dto;

class ForwardInfo implements \JsonSerializable
{
	public function __construct(
		public readonly string $originalAuthorName,
	) {}

	public function jsonSerialize(): array
	{
		return ['originalAuthorName' => $this->originalAuthorName];
	}
}