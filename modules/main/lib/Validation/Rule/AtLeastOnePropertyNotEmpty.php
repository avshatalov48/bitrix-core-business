<?php

declare(strict_types=1);

namespace Bitrix\Main\Validation\Rule;

use Attribute;
use Bitrix\Main\Localization\LocalizableMessage;
use Bitrix\Main\Localization\LocalizableMessageInterface;
use Bitrix\Main\Validation\ValidationError;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\AtLeastOneNotEmptyValidator;
use ReflectionClass;
use ReflectionProperty;

#[Attribute(Attribute::TARGET_CLASS)]
final class AtLeastOnePropertyNotEmpty extends AbstractClassValidationAttribute
{
	public function __construct(
		private readonly array $fields,
		private readonly bool $allowZero = false,
		private readonly bool $allowEmptyString = false,
		protected string|LocalizableMessageInterface|null $errorMessage = null,
	)
	{
	}

	public function validateObject(object $object): ValidationResult
	{
		$result = new ValidationResult();

		$properties = $this->getProperties($object);

		if (empty($properties))
		{
			$result->addError(new ValidationError(
				new LocalizableMessage('MAIN_VALIDATION_AT_LEAST_ONE_PROPERTY_NOT_EMPTY_EMPTY')
			));

			return $this->replaceWithCustomError($result);
		}

		$values = $this->getValues($object, ...$properties);

		$result = (new AtLeastOneNotEmptyValidator($this->allowZero, $this->allowEmptyString))
			->validate($values);

		return $this->replaceWithCustomError($result);
	}

	private function getProperties(object $object): array
	{
		$reflection = new ReflectionClass($object);

		return array_filter(
			$reflection->getProperties(),
			fn (ReflectionProperty $property): bool =>
			in_array($property->getName(), $this->fields, true)
		);
	}

	private function getValues(object $object, ReflectionProperty ...$properties): array
	{
		$values = [];
		foreach ($properties as $property)
		{
			if ($property->isInitialized($object))
			{
				$values[] = $property->getValue($object);
			}
		}

		return $values;
	}
}