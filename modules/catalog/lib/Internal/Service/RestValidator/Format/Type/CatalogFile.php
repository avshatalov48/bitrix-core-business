<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Format\Type;

use Bitrix\Iblock\Public\Service\RestValidator\Format\Type\FileInterface;
use Bitrix\Main\Engine\Response\Converter;

class CatalogFile implements FileInterface
{
	public function isCorrectFormat(mixed $value): bool
	{
		if (!is_array($value))
		{
			return false;
		}

		$converter = new Converter(
			Converter::KEYS
			| Converter::RECURSIVE
			| Converter::TO_SNAKE
			| Converter::TO_SNAKE_DIGIT
			| Converter::TO_UPPER
		);
		$value = $converter->process($value);

		if (isset($value['VALUE']))
		{
			$value = $value['VALUE'];
		}
		if (isset($value['REMOVE']) && strtoupper((string)$value['REMOVE']) == 'Y')
		{
			return true;
		}
		if (empty($value['FILE_DATA']) || !is_array($value['FILE_DATA']))
		{
			return false;
		}

		return true;
	}
}
