<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Validator;

use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\MinValidator;
use Bitrix\Main\Validation\Validator\ValidatorInterface;

class ArrayOfPositiveNumbersValidator implements ValidatorInterface
{
	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if (!is_array($value))
		{
			$result->addError(new ValidationError('Wrong array type'));

			return $result;
		}

		$positiveNumberValidator = new MinValidator(1);
		foreach ($value as $item)
		{
			$itemResult = $positiveNumberValidator->validate($item);
			if (!$itemResult->isSuccess())
			{
				$result->addErrors($itemResult->getErrors());

				return $result;
			}
		}

		return $result;
	}
}
