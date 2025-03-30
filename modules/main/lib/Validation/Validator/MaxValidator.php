<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

class MaxValidator implements ValidatorInterface
{
	public function __construct(
		private readonly int $max
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
					new LocalizableMessage('MAIN_VALIDATION_MAX_NOT_A_NUMBER'),
					failedValidator: $this
				)
			);

			return $result;
		}

		if ($value > $this->max)
		{
			$result->addError(
				new ValidationError(
					new LocalizableMessage('MAIN_VALIDATION_MAX_GREATER_THAN_MAX', ['#MAX#' => $this->max]),
					failedValidator: $this
				)
			);

			return $result;
		}

		return $result;
	}
}