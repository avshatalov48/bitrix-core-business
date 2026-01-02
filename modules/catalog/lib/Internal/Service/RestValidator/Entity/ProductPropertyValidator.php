<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Entity;

use Bitrix\Catalog\RestView\ProductProperty;
use Bitrix\Iblock\Public\Service\RestValidator\Format;

class ProductPropertyValidator extends EntityValidator
{
	public function getViewClassName(): string
	{
		return ProductProperty::class;
	}

	public function getFormatValidators(): array
	{
		return [
			Format\PropertyFieldValidator::getInstance(),
		];
	}
}
