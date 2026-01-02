<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Validation\Validator\JsonValidator;

#[Attribute(Attribute::TARGET_PROPERTY | Attribute::TARGET_PARAMETER)]
class Json extends AbstractPropertyValidationAttribute
{
	protected function getValidators(): array
	{
		return [
			new JsonValidator(),
		];
	}
}