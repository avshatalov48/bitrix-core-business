<?php

namespace Bitrix\Iblock\Public\Service\RestValidator\Format;

use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\Public\Service\RestValidator\Trait;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

class SimplePropertyValueValidator extends BaseValidator
{
	use Trait\FileValidatorTrait;

	private const FIELD_NAME_PREFIX = 'PROPERTY_';

	protected int $iblockId;

	protected array $properties;

	public function setIblockId(int $iblockId): self
	{
		if ($iblockId < 0)
		{
			return $this;
		}
		if ($this->getIblockId() !== $iblockId)
		{
			$this->iblockId = $iblockId;
			$this->setFieldAliases([]);
			$this->init();
		}

		return $this;
	}

	public function getIblockId(): ?int
	{
		return $this->iblockId ?? null;
	}

	private function getPropertyIndex(int $propertyId): string
	{
		return self::FIELD_NAME_PREFIX . $propertyId;
	}

	protected function init(): void
	{
		$iblockId = $this->getIblockId();
		if ($iblockId)
		{
			$fields = [];
			$properties = [];

			$iterator = PropertyTable::getList([
				'select' => [
					'ID',
					'PROPERTY_TYPE',
					'MULTIPLE',
				],
				'filter' => $this->getPropertyFilter($iblockId),
				'order' => [
					'ID' => 'ASC',
				],
				'cache' => [
					'ttl' => 86400,
				],
			]);
			while ($row = $iterator->fetch())
			{
				$row['ID'] = (int)$row['ID'];
				$id = $row['ID'];
				$propertyIndex = $this->getPropertyIndex($id);
				$fields[] = $propertyIndex;
				$properties[$propertyIndex] = $row;
			}
			unset(
				$row,
				$iterator,
			);

			$this->setFields($fields);
			$this->properties = $properties;
		}
	}

	protected function getPropertyFilter(int $iblockId): array
	{
		return [
			'=IBLOCK_ID' => $iblockId,
			'=ACTIVE' => 'Y',
			'=USER_TYPE' => null,
		];
	}

	protected function isPropertyExists(string $propertyIndex): bool
	{
		return isset($this->properties[$propertyIndex]);
	}

	protected function getPropertyType(string $propertyIndex): string
	{
		return $this->properties[$propertyIndex]['PROPERTY_TYPE'] ?? PropertyTable::TYPE_STRING;
	}

	protected function isPropertyMultiple(string $propertyIndex): bool
	{
		return ($this->properties[$propertyIndex]['MULTIPLE'] ?? 'N') === 'Y';
	}

	public function run(array $rawData): Result
	{
		$result = new Result();

		$useAliases = $this->isExistsFieldAliases();
		foreach ($this->getFields() as $propertyIndex)
		{
			$index = $useAliases ? $this->getRealAlias($propertyIndex) : $propertyIndex;
			if ($index === null)
			{
				continue;
			}
			if (!isset($rawData[$index]))
			{
				continue;
			}

			if (!$this->isCorrectValue($propertyIndex, $rawData[$index]))
			{
				$result->addError(new Error(
					'Wrong format value of field `' . $index . '`.'
				));
			}
		}

		return $result;
	}

	protected function isCorrectValue(string $fieldName, $value): bool
	{
		if (!$this->isPropertyExists($fieldName))
		{
			return true;
		}

		$value = $this->normalizeValueFormat($value);

		if (!$this->isPropertyMultiple($fieldName) && count($value) > 1)
		{
			return false;
		}
		$isFile = $this->getPropertyType($fieldName) === PropertyTable::TYPE_FILE;

		$result = true;
		foreach ($value as $row)
		{
			if ($isFile)
			{
				$result = $this->isCorrectFileValue($row);
			}
			else
			{
				$result = $this->isCorrectNonFileValue($row);
			}
			if (!$result)
			{
				break;
			}
		}

		return $result;
	}

	protected function isCorrectFileValue(mixed $value): bool
	{
		$validator = $this->getFileValidator();

		return $validator->isCorrectFormat($value);
	}

	protected function isCorrectNonFileValue(mixed $value): bool
	{
		if (is_scalar($value))
		{
			return true;
		}
		if (is_array($value))
		{
			$value = array_change_key_case($value, CASE_UPPER);
			if (isset($value['VALUE']) && is_scalar($value['VALUE']))
			{
				return true;
			}
		}

		return false;
	}

	protected function normalizeValueFormat(mixed $value): array
	{
		if (!is_array($value))
		{
			return [$value];
		}
		if (isset($value['VALUE']))
		{
			return [$value['VALUE']];
		}
		if (isset($value['value']))
		{
			return [$value['value']];
		}
		if (array_is_list($value))
		{
			return $value;
		}

		$prepare = [];
		$needNormalize = false;
		foreach (array_keys($value) as $index)
		{
			if (is_int($index))
			{
				continue;
			}
			if (preg_match('/^n[0-9]+$/', $index, $prepare))
			{
				continue;
			}
			$needNormalize = true;

			break;
		}

		return
			$needNormalize
				? [$value]
				: $value
		;
	}
}
