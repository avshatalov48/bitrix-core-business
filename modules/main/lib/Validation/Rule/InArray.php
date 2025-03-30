<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Localization\LocalizableMessageInterface;
use Bitrix\Main\Validation\Validator\InArrayValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class InArray extends AbstractPropertyValidationAttribute
{
	public function __construct(
		private readonly array $validValues,
		private readonly bool $strict = false,
		protected string|LocalizableMessageInterface|null $errorMessage = null,
	)
	{
	}

	protected function getValidators(): array
	{
		return [
			(new InArrayValidator($this->validValues, $this->strict)),
		];
	}
}