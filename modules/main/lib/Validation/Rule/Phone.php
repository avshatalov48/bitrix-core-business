<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Localization\LocalizableMessageInterface;
use Bitrix\Main\Validation\Validator\PhoneValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Phone extends AbstractPropertyValidationAttribute
{
	public function __construct(
		protected string|LocalizableMessageInterface|null $errorMessage = null
	)
	{
	}

	protected function getValidators(): array
	{
		return [
			(new PhoneValidator()),
		];
	}
}