<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Validator;

use Bitrix\Main\Error;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\MinValidator;
use Bitrix\Main\Validation\Validator\ValidatorInterface;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;

class JobDataValidator implements ValidatorInterface
{
	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if (!is_array($value))
		{
			$result->addError(new ValidationError('Wrong array type'));

			return $result;
		}

		if (
			!array_key_exists('collabId', $value)
			|| !array_key_exists('userId', $value)
			|| !array_key_exists('id', $value)
			|| !array_key_exists('type', $value)
			|| !array_key_exists('createdDate', $value)
			|| !array_key_exists('nextExecution', $value)
		)
		{
			$result->addError(new ValidationError('Incorrect array data'));

			return $result;
		}

		if (Type::tryFrom($value['type']) === null)
		{
			$result->addError(new Error('Wrong onboarding job type'));

			return $result;
		}

		$positiveNumberValidator = new MinValidator(1);
		if (!$positiveNumberValidator->validate($value['collabId'])->isSuccess())
		{
			$result->addError(new ValidationError('Collab id must be greater than 0'));

			return $result;
		}

		if (!$positiveNumberValidator->validate($value['userId'])->isSuccess())
		{
			$result->addError(new ValidationError('User id must be greater than 0'));

			return $result;
		}

		if ($value['id'] !== null && (int)$value['id'] <= 0)
		{
			$result->addError(new ValidationError('Id must be greater than 0 or empty'));
		}

		return $result;
	}
}
