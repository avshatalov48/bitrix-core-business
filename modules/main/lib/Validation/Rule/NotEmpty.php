<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Localization\LocalizableMessageInterface;
use Bitrix\Main\Validation\Validator\NotEmptyValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotEmpty extends AbstractPropertyValidationAttribute
{
	public function __construct(
		private readonly bool $allowZero = false,
		private readonly bool $allowSpaces = false,
		protected string|LocalizableMessageInterface|null $errorMessage = null
	)
	{
	}

	protected function getValidators(): array
	{
		return [
			(new NotEmptyValidator($this->allowZero, $this->allowSpaces)),
		];
	}
}