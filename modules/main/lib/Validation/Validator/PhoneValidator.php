<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\PhoneNumber\Parser;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

class PhoneValidator implements ValidatorInterface
{
	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		$parser = Parser::getInstance();

		if (!$parser->parse($value)->isValid())
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_PHONE_INVALID'),
				failedValidator: $this
			));
		}

		return  $result;
	}
}