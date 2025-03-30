<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

class UrlValidator implements ValidatorInterface
{
	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if (!filter_var($value, FILTER_VALIDATE_URL))
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_URL_INVALID'),
				failedValidator: $this
			));
		}

		return $result;
	}
}