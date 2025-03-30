<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use ArrayAccess;
use Attribute;
use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Localization\LocalizableMessageInterface;
use Bitrix\Main\Validation\Rule\Enum\Type;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;

#[Attribute(Attribute::TARGET_PROPERTY)]
class ElementsType implements PropertyValidationAttributeInterface
{
	use ValidationErrorTrait;

	/**
	 * @throws ArgumentException
	 */
	public function __construct(
		private readonly ?Type   $typeEnum = null,
		private readonly ?string $className = null,
		string|LocalizableMessageInterface|null $errorMessage = null
	)
	{
		if (null === $this->typeEnum && null === $this->className)
		{
			throw new ArgumentException(Loc::getMessage('MAIN_VALIDATION_ELEMENTS_TYPE_UNSUPPORTED_TYPE'));
		}

		$this->errorMessage = $errorMessage;
	}

	public function validateProperty(mixed $propertyValue): ValidationResult
	{
		$result = new ValidationResult();

		if (!is_array($propertyValue) && !is_iterable($propertyValue) && !($propertyValue instanceof ArrayAccess))
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_ELEMENTS_TYPE_UNSUPPORTED_TYPE')
			));

			return $this->replaceWithCustomError($result);
		}

		if (null === $this->typeEnum)
		{
			$result = $this->checkClassType($propertyValue);
		}
		else
		{
			$result = $this->checkScalar($propertyValue);
		}

		return $this->replaceWithCustomError($result);
	}

	private function checkScalar(mixed $propertyValue): ValidationResult
	{
		$result = new ValidationResult();

		$checkType = $this->typeEnum->value;

		foreach ($propertyValue as $item)
		{
			if (!$checkType($item))
			{
				$result->addError(new ValidationError(
					new LocalizableMessage('MAIN_VALIDATION_ELEMENTS_TYPE_UNSUPPORTED_TYPE')
				));

				return $result;
			}
		}

		return $result;
	}

	private function checkClassType(mixed $propertyValue): ValidationResult
	{
		$result = new ValidationResult();

		if (!class_exists($this->className))
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_ELEMENTS_TYPE_UNSUPPORTED_TYPE')
			));

			return $result;
		}

		foreach ($propertyValue as $item)
		{
			if (!$item instanceof $this->className)
			{
				$result->addError(new ValidationError(
					new LocalizableMessage('MAIN_VALIDATION_ELEMENTS_TYPE_UNSUPPORTED_TYPE')
				));

				return $result;
			}
		}

		return $result;
	}
}