<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

class InArrayValidator implements ValidatorInterface
{
	public function __construct(
		private readonly array $validValues,
		private readonly bool $strict = false
	)
	{
	}

	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if (!in_array($value, $this->validValues, $this->strict))
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_IN_ARRAY'),
				failedValidator: $this
			));
		}

		return $result;
	}
}