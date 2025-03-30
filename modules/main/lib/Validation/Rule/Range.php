<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Localization\LocalizableMessageInterface;
use Bitrix\Main\Validation\Validator\MaxValidator;
use Bitrix\Main\Validation\Validator\MinValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
final class Range extends AbstractPropertyValidationAttribute
{
	public function __construct(
		private readonly int $min,
		private readonly int $max,
		protected string|LocalizableMessageInterface|null $errorMessage = null
	)
	{
	}

	protected function getValidators(): array
	{
		return [
			(new MinValidator($this->min)),
			(new MaxValidator($this->max)),
		];
	}
}