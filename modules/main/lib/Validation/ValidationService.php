<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation;

use Bitrix\Main\ArgumentException;
use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Validation\Rule\ClassValidationAttributeInterface;
use Bitrix\Main\Validation\Rule\Recursive\Validatable;
use Bitrix\Main\Validation\Rule\PropertyValidationAttributeInterface;
use Generator;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionParameter;
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

	public function validateParameter(ReflectionParameter $parameter, mixed $value): ValidationResult
	{
		$result = new ValidationResult();

		$attributes = $this->getValidationAttributes($parameter);

		$name = $parameter->getName();

		$generator = $this->validateValue($value, $name, $attributes);
		$errors = iterator_to_array($generator);

		return $result->addErrors($errors);
	}

	private function validateByClassAttributes(object $object): ValidationResult
	{
		$result = new ValidationResult();

		$class = new ReflectionClass($object);
		$attributes = $class->getAttributes(ClassValidationAttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF);

		if (empty($attributes))
		{
			return $result;
		}

		foreach ($attributes as $attribute)
		{
			$attributeInstance = $attribute->newInstance();
			$attributeErrors = $attributeInstance->validateObject($object)->getErrors();
			$result->addErrors($attributeErrors);
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
		$attributes = $this->getValidationAttributes($property);

		$name = $property->getName();
		$value = $property->getValue($object);

		yield from $this->validateValue($value, $name, $attributes);
	}

	private function validateValue(mixed $value, string $name, array $attributes): Generator
	{
		foreach ($attributes as $attribute)
		{
			$attributeInstance = $attribute->newInstance();

			if ($attributeInstance instanceof Validatable)
			{
				yield from $this->setErrorCodes(
					$name,
					$this->validateValidatableProperty($value, $attributeInstance)
				);
			}
			elseif ($attributeInstance instanceof PropertyValidationAttributeInterface)
			{
				yield from $this->setErrorCodes(
					$name,
					$attributeInstance->validateProperty($value)->getErrors()
				);
			}
		}
	}

	private function validateValidatableProperty(mixed $value, Validatable $attributeInstance): Generator
	{
		if ($value === null)
		{
			return;
		}

		if (!$attributeInstance->iterable)
		{
			if (!is_object($value))
			{
				throw new ArgumentException('Only objects can be marked as Validatable');
			}

			yield from $this->validate($value)->getErrors();

			return;
		}

		if (!is_iterable($value))
		{
			throw new ArgumentException('Only iterable values can be marked as Validatable when "iterable" is true');
		}

		foreach ($value as $i => $item)
		{
			if (!is_object($item))
			{
				throw new ArgumentException('Only objects can be Validatable inside an iterable');
			}

			$attributeErrors = $this->validate($item)->getErrors();

			yield from $this->setErrorCodes((string)$i, $attributeErrors);
		}
	}

	private function setErrorCodes(string $name, iterable $errors): Generator
	{
		foreach ($errors as $error)
		{
			if ($error instanceof ValidationError)
			{
				$error->setCode($name);
			}

			yield $error;
		}
	}

	private function getValidationAttributes(ReflectionParameter|ReflectionProperty $parameter): array
	{
		return array_merge(
			$parameter->getAttributes(Validatable::class, ReflectionAttribute::IS_INSTANCEOF),
			$parameter->getAttributes(PropertyValidationAttributeInterface::class, ReflectionAttribute::IS_INSTANCEOF)
		);
	}
}