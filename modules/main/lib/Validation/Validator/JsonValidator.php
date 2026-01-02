<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Web\Json;

class JsonValidator implements ValidatorInterface
{
	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if (!is_string($value))
		{
			return $result->addError(
				new ValidationError(
					new LocalizableMessage('MAIN_VALIDATION_JSON_NOT_A_STRING'),
					failedValidator: $this
				)
			);
		}

		if (!Json::validate($value))
		{
			return $result->addError(
				new ValidationError(
					new LocalizableMessage('MAIN_VALIDATION_JSON_NOT_JSON'),
					failedValidator: $this
				)
			);
		}

		return $result;
	}
}