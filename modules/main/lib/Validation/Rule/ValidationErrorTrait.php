<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Bitrix\Main\Localization\LocalizableMessageInterface;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\ValidatorInterface;

trait ValidationErrorTrait
{
	protected string|LocalizableMessageInterface|null $errorMessage = null;

	protected function getError(): ?ValidationError
	{
		return null !== $this->errorMessage ? new ValidationError($this->errorMessage) : null;
	}

	protected function replaceWithCustomError(ValidationResult $result, ?ValidatorInterface $validator = null): ValidationResult
	{
		if ($result->isSuccess())
		{
			return $result;
		}

		$customError = $this->getError();
		if (null === $customError)
		{
			return $result;
		}

		if (null !== $validator)
		{
			$customError->setFailedValidator($validator);
		}

		return (new ValidationResult())
			->setData($result->getData())
			->addError($customError);
	}

	protected function getFailedValidator(ValidationResult $result): ?ValidatorInterface
	{
		if (!$result->isSuccess())
		{
			$error = $result->getErrors()[0];
			if ($error instanceof ValidationError)
			{
				return $error->getFailedValidator();
			}
		}

		return null;
	}
}