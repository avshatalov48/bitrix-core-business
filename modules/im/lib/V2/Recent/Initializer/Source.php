<?php

namespace Bitrix\Im\V2\Recent\Initializer;

interface Source
{
	public function getSourceId(): ?int;
	public function getUsers(string $pointer, int $limit): InitialiazerResult;
	public static function getType(): SourceType;
}
