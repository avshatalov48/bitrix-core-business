<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Entity;

use Bitrix\Catalog\Internal\Service\RestValidator\Format\ProductFilterFieldValidator;
use Bitrix\Iblock\Public\Service\RestValidator\Format\ElementFilterFieldValidator;
use Bitrix\Iblock\Public\Service\RestValidator\Format\PropertyValueFilterValidator;

class ProductFilterValidator extends BaseProductValidator
{
	public function getFormatValidators(): array
	{
		$result = [
			ElementFilterFieldValidator::getInstance(),
			ProductFilterFieldValidator::getInstance(),
		];

		$iblockId = $this->getIblockId();
		if ($iblockId)
		{
			$validator = PropertyValueFilterValidator::getInstance();
			$validator->setIblockId($iblockId);
			$result[] = $validator;
		}

		return $result;
	}
}
