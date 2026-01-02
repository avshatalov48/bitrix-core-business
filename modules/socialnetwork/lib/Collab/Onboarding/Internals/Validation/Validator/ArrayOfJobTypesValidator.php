<?php

declare(strict_types=1);

namespace Bitrix\Socialnetwork\Collab\Onboarding\Internals\Validation\Validator;

use Bitrix\Main\Error;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\ValidatorInterface;
use Bitrix\Socialnetwork\Collab\Onboarding\Internals\Type;

class ArrayOfJobTypesValidator implements ValidatorInterface
{
	public function validate(mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		if (!is_array($value))
		{
			$result->addError(new ValidationError('Wrong array type'));

			return $result;
		}

		foreach ($value as $item)
		{
			if (Type::tryFrom($item) === null)
			{
				$result->addError(new Error('Wrong onboarding job type'));

				return $result;
			}
		}

		return $result;
	}
}
