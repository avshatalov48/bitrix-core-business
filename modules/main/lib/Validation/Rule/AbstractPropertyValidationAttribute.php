<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\ValidatorInterface;

abstract class AbstractPropertyValidationAttribute implements PropertyValidationAttributeInterface
{
	use ValidationErrorTrait;

	/**
	 * @return ValidatorInterface[]
	 */
	abstract protected function getValidators(): array;

	public function validateProperty(mixed $propertyValue): ValidationResult
	{
		$result = new ValidationResult();

		$failedValidator = null;
		$validators = $this->getValidators();
		foreach ($validators as $validator)
		{
			$validationResult = $validator->validate($propertyValue);
			$result->addErrors($validationResult->getErrors());
			$failedValidator ??= $this->getFailedValidator($validationResult);
		}

		return $this->replaceWithCustomError($result, $failedValidator);
	}
}