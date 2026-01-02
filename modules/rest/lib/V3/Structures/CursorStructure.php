<?php

namespace Bitrix\Rest\V3\Structures;

use Bitrix\Main\DB\Order;
use Bitrix\Rest\V3\Dto\PropertyHelper;
use Bitrix\Rest\V3\Exceptions\InvalidOrderException;
use Bitrix\Rest\V3\Exceptions\InvalidPaginationException;
use Bitrix\Rest\V3\Exceptions\UnknownDtoPropertyException;
use Bitrix\Rest\V3\Exceptions\Validation\InvalidRequestFieldTypeException;
use Bitrix\Rest\V3\Exceptions\Validation\RequiredFieldInRequestException;
use Bitrix\Rest\V3\Interaction\Request\Request;

final class CursorStructure extends Structure
{
	protected string $field;
	protected mixed $value = null;
	protected Order $order;

	protected int $limit = 50;

	public static function create(mixed $value, string $dtoClass = null, Request $request = null): self
	{
		$structure = new self();

		$reflection = new \ReflectionClass($dtoClass);

		if (isset($value['limit']))
		{
			if (!is_numeric($value['limit']) || $value['limit'] == 0)
			{
				throw new InvalidPaginationException(['limit' => $value['limit']]);
			}

			$structure->limit = min((int)$value['limit'], PaginationStructure::MAX_LIMIT);
		}

		if (!empty($value['field']))
		{
			if (!PropertyHelper::isValidProperty($reflection, $value['field']))
			{
				throw new UnknownDtoPropertyException($dtoClass, $value['field']);
			}
			$structure->field = $value['field'];
		}
		else
		{
			throw new RequiredFieldInRequestException('cursor.field');
		}

		if (!empty($value['value']))
		{
			$property = PropertyHelper::getProperty($reflection, $structure->field);
			$itemValue = FieldsConverter::convertValueByType($property->getType()?->getName(), $value['value']);

			if (!FieldsValidator::validateReflectionPropertyAndValue($property, $itemValue))
			{
				throw new InvalidRequestFieldTypeException($property->getName(), $property->getType() ? $property->getType()->getName() : 'unknown');
			}

			$structure->value = $itemValue;
		}
		else
		{
			throw new RequiredFieldInRequestException('cursor.value');
		}

		if (!empty($value['order']))
		{
			$itemOrder = Order::tryFrom(strtoupper($value['order']));

			if ($itemOrder === null)
			{
				throw new InvalidOrderException($value['order']);
			}

			$structure->order = $itemOrder;
		}
		else
		{
			$structure->order = Order::Asc;
		}

		return $structure;
	}

	public function getField(): string
	{
		return $this->field;
	}

	public function getValue(): mixed
	{
		return $this->value;
	}

	public function getOrder(): Order
	{
		return $this->order;
	}

	public function getLimit(): int
	{
		return $this->limit;
	}
}
