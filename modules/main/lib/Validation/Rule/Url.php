<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Validation\Validator\UrlValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Url extends AbstractPropertyValidationAttribute
{
	public function __construct(
		protected ?string $errorMessage = null
	)
	{
	}

	protected function getValidators(): array
	{
		return [
			(new UrlValidator()),
		];
	}
}