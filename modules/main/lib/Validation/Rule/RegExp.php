<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Localization\LocalizableMessageInterface;
use Bitrix\Main\Validation\Validator\RegExpValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class RegExp extends AbstractPropertyValidationAttribute
{
	public function __construct(
		private readonly string $pattern,
		private readonly int $flags = 0,
		private readonly int $offset = 0,
		protected string|LocalizableMessageInterface|null $errorMessage = null
	)
	{
	}

	protected function getValidators(): array
	{
		return [
			(new RegExpValidator($this->pattern, $this->flags, $this->offset)),
		];
	}
}