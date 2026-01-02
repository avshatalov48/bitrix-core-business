<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format\Type;

class SkipFile implements FileInterface
{
	public function isCorrectFormat(mixed $value): bool
	{
		return true;
	}
}
