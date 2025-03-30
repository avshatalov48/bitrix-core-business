<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Localization\LocalizableMessageInterface;
use Bitrix\Main\Validation\Validator\LengthValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Length extends AbstractPropertyValidationAttribute
{
	public function __construct(
		private readonly ?int $min = null,
		private readonly ?int $max = null,
		protected string|LocalizableMessageInterface|null $errorMessage = null,
	)
	{
	}
	protected function getValidators(): array
	{
		return [
			(new LengthValidator($this->min, $this->max)),
		];
	}
}