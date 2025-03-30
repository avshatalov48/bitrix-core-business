<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

class RegExpValidator implements ValidatorInterface
{
	public function __construct(
		private readonly string $pattern,
		private readonly int $flags = 0,
		private readonly int $offset = 0
	)
	{
	}

	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();
		if (!is_string($value))
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_REG_EXP_INVALID_TYPE'),
				failedValidator: $this
			));

			return $result;
		}

		if (!preg_match($this->pattern, $value, flags: $this->flags, offset: $this->offset))
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_REG_EXP_INVALID'),
				failedValidator: $this
			));
		}

		return $result;
	}
}