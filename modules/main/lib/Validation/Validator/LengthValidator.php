<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Validator;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Localization\LocalizableMessagePlural;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

class LengthValidator implements ValidatorInterface
{
	/**
	 * @throws ArgumentException
	 */
	public function __construct(
		private readonly ?int $min = null,
		private readonly ?int $max = null
	)
	{
		if (null === $this->min && null === $this->max)
		{
			throw new ArgumentException(Loc::getMessage('MAIN_VALIDATION_LENGTH_INVALID_ARGUMENT'));
		}
	}

	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if (!is_string($value))
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_LENGTH_INVALID_ARGUMENT'),
				failedValidator: $this
			));

			return $result;
		}

		$length = mb_strlen($value);

		if (null !== $this->min)
		{
			$result = $this->checkMin($length);
		}

		if (null !== $this->max && $result->isSuccess())
		{
			$result = $this->checkMax($length);
		}

		return $result;
	}

	private function checkMin(int $length): ValidationResult
	{
		$result = new ValidationResult();

		if ($length < $this->min)
		{
			$result->addError(new ValidationError(
				new LocalizableMessagePlural('MAIN_VALIDATION_LENGTH_INVALID_MIN', $this->min, ['#MIN#' => $this->min]),
				failedValidator: $this
			));
		}

		return $result;
	}

	private function checkMax(int $length): ValidationResult
	{
		$result = new ValidationResult();

		if ($length > $this->max)
		{
			$result->addError(new ValidationError(
				new LocalizableMessagePlural('MAIN_VALIDATION_LENGTH_INVALID_MAX', $this->max, ['#MAX#' => $this->max])
				, failedValidator: $this
			));
		}

		return $result;
	}
}