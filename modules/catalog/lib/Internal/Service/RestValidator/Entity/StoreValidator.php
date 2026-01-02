<?php

namespace Bitrix\Catalog\Internal\Service\RestValidator\Entity;

use Bitrix\Catalog\Internal\Service\RestValidator\Format;
use Bitrix\Catalog\RestView\Store;

class StoreValidator extends EntityValidator
{
	public function getViewClassName(): string
	{
		return Store::class;
	}

	public function getFormatValidators(): array
	{
		return [
			Format\StoreFieldValidator::getInstance(),
		];
	}
}
