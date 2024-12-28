<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Validation\Validator\MinValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Min extends AbstractPropertyValidationAttribute
{
	public function __construct(
		private readonly int $min,
		protected ?string $errorMessage = null
	)
	{
	}

	protected function getValidators(): array
	{
		return [
			(new MinValidator($this->min)),
		];
	}
}