<?php

namespace Bitrix\Rest\V3\Structures;

use Bitrix\Main\Error;
use Bitrix\Rest\V3\Attributes\Editable;
use Bitrix\Rest\V3\Dto\Dto;
use Bitrix\Rest\V3\Dto\PropertyHelper;
use Bitrix\Rest\V3\Exceptions\UnknownDtoPropertyException;
use Bitrix\Rest\V3\Exceptions\Validation\DtoFieldRequiredAttributeException;
use Bitrix\Rest\V3\Exceptions\Validation\DtoValidationException;
use Bitrix\Rest\V3\Exceptions\Validation\InvalidFieldTypeTypeException;
use Bitrix\Rest\V3\Exceptions\Validation\InvalidRequestFieldTypeException;
use Bitrix\Rest\V3\Exceptions\Validation\RequestInvalidFieldTypeException;
use Bitrix\Rest\V3\Interaction\Request\Request;
use Bitrix\Rest\V3\Interaction\Request\UpdateRequest;

final class FieldsStructure extends Structure
{
	use UserFieldsTrait;

	protected string $dtoClass;

	/** @var string[] $items */
	protected array $items = [];

	public static function create(mixed $value, string $dtoClass, Request $request): self
	{
		$structure = new self();
		$structure->dtoClass = $dtoClass;
		
		$value = (array)$value;

		if (!empty($value))
		{
			$reflection = new \ReflectionClass($dtoClass);

			foreach ($value as $item => $itemValue)
			{
				if (str_starts_with($item, 'UF_'))
				{
					$structure->userFields[$item] = $itemValue;

					continue;
				}

				// check if dto has property
				if (!PropertyHelper::isValidProperty($reflection, $item))
				{
					throw new UnknownDtoPropertyException($dtoClass, $item);
				}

				if ($request instanceof UpdateRequest && !PropertyHelper::hasAttribute($dtoClass, $item, Editable::class))
				{
					throw new DtoFieldRequiredAttributeException($dtoClass, $item, Editable::class);
				}

				$property = PropertyHelper::getProperty($reflection, $item);
				$itemValue = FieldsConverter::convertValueByType($property->getType()?->getName(), $itemValue);

				if (!FieldsValidator::validateReflectionPropertyAndValue($property, $itemValue))
				{
					throw new InvalidRequestFieldTypeException($property->getName(), $property->getType() ? $property->getType()->getName() : 'unknown');
				}

				$structure->items[$item] = $itemValue;
			}
		}

		return $structure;
	}

	public function getItems(): array
	{
		return $this->items;
	}

	public function getAsDto(): Dto
	{
		$dtoClass = $this->dtoClass;
		$dto = new $dtoClass();

		foreach ($this->items as $propertyName => $value)
		{
			$dto->$propertyName = $value;
		}

		foreach ($this->userFields as $propertyName => $value)
		{
			$dto->$propertyName = $value;
		}

		return $dto;
	}
}
