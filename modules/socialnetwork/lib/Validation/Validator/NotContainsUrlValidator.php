<?php

declare(strict_types=1);

namespace Bitrix\SocialNetwork\Validation\Validator;

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\ValidatorInterface;

class NotContainsUrlValidator implements ValidatorInterface
{
	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if (!is_string($value))
		{
			$result->addError(new ValidationError(
				'Value must be a string',
				failedValidator: $this,
			));

			return $result;
		}

		if (preg_match('/\b((https?:\/\/|www\.)[^\s]+)/iu', $value))
		{
			$result->addError(new ValidationError(
				'Value must not contain URLs',
				failedValidator: $this,
			));
		}

		return $result;
	}
}
