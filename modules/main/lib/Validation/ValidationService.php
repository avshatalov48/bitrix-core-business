<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation;

use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\Rule\ClassValidationAttributeInterface;
use Bitrix\Main\Validation\Rule\Recursive\Validatable;
use Bitrix\Main\Validation\Rule\PropertyValidationAttributeInterface;
use Generator;
use ReflectionClass;
use ReflectionProperty;

final class ValidationService
{
	public function validate(object $object): ValidationResult
	{
		$result = new ValidationResult();

		$propertyResult = $this->validateByPropertyAttributes($object);
		$result->addErrors($propertyResult->getErrors());

		$classResult = $this->validateByClassAttributes($object);
		$result->addErrors($classResult->getErrors());

		return $result;
	}

	private function validateByClassAttributes(object $object): ValidationResult
	{
		$result = new ValidationResult();

		$class = new ReflectionClass($object);
		$attributes = $class->getAttributes();

		if (empty($attributes))
		{
			return $result;
		}

		foreach ($attributes as $attribute)
		{
			$attributeInstance = $attribute->newInstance();
			if ($attributeInstance instanceof ClassValidationAttributeInterface)
			{
				$attributeErrors = $attributeInstance->validateObject($object)->getErrors();
				$result->addErrors($attributeErrors);
			}
		}

		return $result;
	}

	private function validateByPropertyAttributes(object $object): ValidationResult
	{
		$result = new ValidationResult();

		$properties = (new ReflectionClass($object))->getProperties();
		foreach ($properties as $property)
		{
			if ($property->isInitialized($object))
			{
				$generator = $this->validateProperty($property, $object);
				$errors = iterator_to_array($generator);
				$result->addErrors($errors);

				continue;
			}

			$type = $property->getType();
			if (null === $type)
			{
				continue;
			}

			if ($type->allowsNull())
			{
				continue;
			}

			if (!$type->allowsNull())
			{
				$result->addError(new ValidationError(
					new LocalizableMessage('MAIN_VALIDATION_EMPTY_PROPERTY'),
					$property->getName()
				));
			}
		}

		return $result;
	}

	private function validateProperty(ReflectionProperty $property, object $object): Generator
	{
		$attributes = $property->getAttributes();

		foreach ($attributes as $attribute)
		{
			$attributeErrors = [];

			$name = $property->getName();
			$value = $property->getValue($object);

			$attributeInstance = $attribute->newInstance();

			if ($attributeInstance instanceof Validatable && !$property->getType()?->isBuiltin())
			{
				$attributeErrors = $this->validate($value)->getErrors();
			}
			elseif ($attributeInstance instanceof PropertyValidationAttributeInterface)
			{
				$attributeErrors = $attributeInstance->validateProperty($value)->getErrors();
			}

			foreach ($attributeErrors as $error)
			{
				if ($error instanceof ValidationError)
				{
					if ($error->hasCode())
					{
						$error->setCode($name . '.' . $error->getCode());
					}
					else
					{
						$error->setCode($name);
					}
				}

				yield $error;
			}
		}
	}
}