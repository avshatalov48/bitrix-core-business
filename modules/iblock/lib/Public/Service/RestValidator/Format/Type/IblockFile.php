<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format\Type;

class IblockFile implements FileInterface
{
	public function isCorrectFormat(mixed $value): bool
	{
		if (!is_array($value))
		{
			return false;
		}
		$file = $value['VALUE'] ?? $value;
		if (!is_array($file))
		{
			return false;
		}
		if (!isset($file['tmp_name']) && !isset($file['del']))
		{
			return false;
		}

		return true;
	}
}
