<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Localization\LocalizableMessageInterface;
use Bitrix\Main\Validation\Validator\MinValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class PositiveNumber extends AbstractPropertyValidationAttribute
{
	public function __construct(
		protected string|LocalizableMessageInterface|null $errorMessage = null
	)
	{
	}

	protected function getValidators(): array
	{
		return [
			(new MinValidator(1)),
		];
	}
}