<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Entity;

use Bitrix\Catalog\RestView\ProductProperty;
use Bitrix\Iblock\Public\Service\RestValidator\Format;

class ProductPropertyFilterValidator extends EntityValidator
{
	public function getViewClassName(): string
	{
		return ProductProperty::class;
	}

	/**
	 * @inheritDoc
	 */
	public function getFormatValidators(): array
	{
		return [
			Format\PropertyFilterFieldValidator::getInstance(),
		];
	}
}
