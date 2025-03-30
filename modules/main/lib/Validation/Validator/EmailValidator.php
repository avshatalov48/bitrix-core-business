<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

class EmailValidator implements ValidatorInterface
{
	public function __construct(
		private readonly bool $strict = false,
		private readonly bool $domainCheck = false,
	)
	{
	}

	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if (!check_email($value, $this->strict, $this->domainCheck))
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_EMAIL_INVALID'),
				failedValidator: $this
			));
		}

		return $result;
	}
}