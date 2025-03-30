<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Localization\LocalizableMessageInterface;
use Bitrix\Main\Validation\Validator\MaxValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Max extends AbstractPropertyValidationAttribute
{
	public function __construct(
		private readonly int $max,
		protected string|LocalizableMessageInterface|null $errorMessage = null
	)
	{
	}

	protected function getValidators(): array
	{
		return [
			(new MaxValidator($this->max)),
		];
	}
}