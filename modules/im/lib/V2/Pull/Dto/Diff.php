<?php

namespace Bitrix\Im\V2\Pull\Dto;

class Diff
{
	private const JSON_ENCODE_FLAGS = JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR;

	public function __construct(
		public readonly int $userId,
		public readonly array $params = [],
	){}

	public function getKey(): string
	{
		return md5(json_encode($this->params, self::JSON_ENCODE_FLAGS));
	}
}