<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Iblock\ElementTable;
use Bitrix\Main\ORM\Entity;

class ElementFieldValidator extends BaseFieldValidator
{
	protected function getEntity(): Entity
	{
		return ElementTable::getEntity();
	}

	protected function isCorrectValue(string $fieldName, mixed $value): bool
	{
		if ($fieldName === 'PREVIEW_PICTURE' || $fieldName === 'DETAIL_PICTURE')
		{
			$valueValidator = $this->getFileValidator();

			return $valueValidator->isCorrectFormat($value);
		}
		else
		{
			return is_scalar($value);
		}
	}
}
