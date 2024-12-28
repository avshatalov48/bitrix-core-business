<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Validation\Validator\EmailValidator;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Email extends AbstractPropertyValidationAttribute
{
	public function __construct(
		private readonly bool $strict = false,
		private readonly bool $domainCheck = false,
		protected ?string $errorMessage = null
	)
	{
	}

	protected function getValidators(): array
	{
		return [
			(new EmailValidator($this->strict, $this->domainCheck)),
		];
	}
}