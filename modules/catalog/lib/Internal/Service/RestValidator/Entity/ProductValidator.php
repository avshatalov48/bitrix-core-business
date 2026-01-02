<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Entity;

use Bitrix\Catalog\Internal\Service\RestValidator\Format\ProductFieldValidator;
use Bitrix\Iblock\Public\Service\RestValidator\Format\ElementFieldValidator;
use Bitrix\Iblock\Public\Service\RestValidator\Format\SimplePropertyValueValidator;

class ProductValidator extends BaseProductValidator
{
	public function getFormatValidators(): array
	{
		$result = [];

		$element = ElementFieldValidator::getInstance();
		$element->setFileValidator($this->getFileValidator());
		$result[] = $element;

		$product = ProductFieldValidator::getInstance();
		$product->setFileValidator($this->getFileValidator());
		$result[] = $product;

		$iblockId = $this->getIblockId();
		if ($iblockId)
		{
			$validator = SimplePropertyValueValidator::getInstance();
			$validator->setIblockId($iblockId);
			$validator->setFileValidator($this->getFileValidator());
			$result[] = $validator;
		}

		return $result;
	}
}
