<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;
use Countable;

class NotEmptyValidator implements ValidatorInterface
{
	public function __construct(
		private readonly bool $allowZero = false,
		private readonly bool $allowSpaces = false
	)
	{
	}

	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if ($this->allowZero && ($value === 0 || $value === '0'))
		{
			return $result;
		}

		if ($value instanceof Countable && count($value) === 0)
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_NOT_EMPTY_INVALID'),
				failedValidator: $this
			));

			return $result;
		}

		if (!$this->allowSpaces && is_string($value))
		{
			$value = trim($value);
		}

		if (empty($value))
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_NOT_EMPTY_INVALID'),
				failedValidator: $this
			));
		}

		return $result;
	}
}