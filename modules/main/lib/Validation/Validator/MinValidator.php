<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

class MinValidator implements ValidatorInterface
{
	public function __construct(
		private readonly int $min
	)
	{
	}

	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if (!is_numeric($value))
		{
			$result->addError(
				new ValidationError(
					new LocalizableMessage('MAIN_VALIDATION_MIN_NOT_A_NUMBER'),
					failedValidator: $this
				)
			);

			return $result;
		}

		if ($value < $this->min)
		{
			$result->addError(
				new ValidationError(
					new LocalizableMessage('MAIN_VALIDATION_MIN_LESS_THAN_MIN', ['#MIN#' => $this->min]),
					failedValidator: $this)
			);
		}

		return $result;
	}
}