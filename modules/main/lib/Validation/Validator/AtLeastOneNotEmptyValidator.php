<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use ArrayAccess;
use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

class AtLeastOneNotEmptyValidator implements ValidatorInterface
{
	public function __construct(
		private readonly bool $allowZero = false,
		private readonly bool $allowEmptyString = false,
	)
	{
	}

	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if (!is_array($value) && !is_iterable($value) && !($value instanceof ArrayAccess))
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_AT_AT_LEAST_ONE_PROPERTY_NOT_EMPTY_UNSUPPORTED_TYPE'),
				failedValidator: $this
			));

			return $result;
		}

		$allEmpty = true;
		foreach ($value as $item)
		{
			if (!empty($item))
			{
				$allEmpty = false;

				break;
			}

			if ($this->allowZero && 0 === $item)
			{
				$allEmpty = false;

				break;
			}

			if ($this->allowEmptyString && '' === $item)
			{
				$allEmpty = false;

				break;
			}
		}

		if ($allEmpty)
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_AT_LEAST_ONE_PROPERTY_NOT_EMPTY_ALL_EMPTY'),
				failedValidator: $this
			));
		}

		return $result;
	}
}