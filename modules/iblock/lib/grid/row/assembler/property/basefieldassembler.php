<?php

namespace Bitrix\Iblock\Grid\Row\Assembler\Property;


use Bitrix\Main;
use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Iblock\Grid\Column\ElementPropertyProvider;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Iblock\PropertyTable;

abstract class BaseFieldAssembler extends FieldAssembler
{
	protected const NORMALIZE_EMPTY = 0;
	protected const NORMALIZE_BY_INT = 1;

	protected int $iblockId;

	protected array $customEditableColumnIds;

	protected array $properties;

	public function __construct(int $iblockId, array $customEditableColumnIds)
	{
		$this->iblockId = $iblockId;

		$this->customEditableColumnIds = $customEditableColumnIds;

		parent::__construct(
			$this->getPropertyColumnsIds()
		);
	}

	protected function getIblockId(): int
	{
		return $this->iblockId;
	}

	abstract protected function getPropertyFilter(): array;

	abstract protected function validateProperty(array $property): ?array;

	protected function loadProperties(): void
	{
		if (isset($this->properties))
		{
			return;
		}

		$this->properties = [];
		$iterator = PropertyTable::getList([
			'select' => [
				'ID',
				'IBLOCK_ID',
				'NAME',
				'SORT',
				'DEFAULT_VALUE',
				'PROPERTY_TYPE',
				'ROW_COUNT',
				'COL_COUNT',
				'LIST_TYPE',
				'MULTIPLE',
				'FILE_TYPE',
				'MULTIPLE_CNT',
				'LINK_IBLOCK_ID',
				'WITH_DESCRIPTION',
				'IS_REQUIRED',
				'USER_TYPE',
				'USER_TYPE_SETTINGS_LIST',
				'HINT',
			],
			'filter' => array_merge(
				[
					'=IBLOCK_ID' => $this->getIblockId(),
					'=ACTIVE' => 'Y',
				],
				$this->getPropertyFilter()
			),
			'order' => [
				'SORT' => 'ASC',
				'NAME' => 'ASC',
				'ID' => 'ASC',
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);

		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			$row['IBLOCK_ID'] = (int)$row['IBLOCK_ID'];

			$row['USER_TYPE'] = trim((string)$row['USER_TYPE']);
			if ($row['USER_TYPE'] === '')
			{
				$row['USER_TYPE'] = null;
			}

			$row['HINT'] = trim((string)$row['HINT']);
			if ($row['HINT'] === '')
			{
				$row['HINT'] = null;
			}

			$row = $this->validateProperty($row);

			if ($row)
			{
				$this->properties[ElementPropertyProvider::getColumnIdByPropertyId($row['ID'])] = $row;
			}
		}
		unset($row, $iterator);
	}

	protected function isMultipleColumn(string $columnId): bool
	{
		return ($this->properties[$columnId]['MULTIPLE'] ?? 'N') === 'Y';
	}

	protected function getPropertyColumnsIds(): array
	{
		$this->loadProperties();

		return array_keys($this->properties);
	}

	protected function getColumnValues(mixed $rawValues, string $fieldName = 'VALUE'): array
	{
		if (!is_array($rawValues))
		{
			return [];
		}
		if (array_key_exists($fieldName, $rawValues))
		{
			return [$rawValues[$fieldName]];
		}
		else
		{
			$result = [];
			foreach ($rawValues as $row)
			{
				if (is_array($row)&& array_key_exists($fieldName, $row))
				{
					$result[] = $row[$fieldName];
				}
			}

			return $result;
		}
	}

	protected function compileColumnValues(array $rowList, int $normalizationMode = self::NORMALIZE_EMPTY): array
	{
		$result = [];
		foreach ($rowList as $row)
		{
			foreach ($this->getColumnIds() as $columnId)
			{
				$columnValues = $this->getColumnValues($row['data'][$columnId] ?? null);
				if (!empty($columnValues))
				{
					$result = array_merge($result, $columnValues);
				}
				unset($columnValues);
			}
		}

		if (empty($result))
		{
			return $result;
		}

		if ($normalizationMode === self::NORMALIZE_BY_INT)
		{
			Main\Type\Collection::normalizeArrayValuesByInt($result, false);
		}

		return $result;
	}

	protected static function getRowType(array $row): ?string
	{
		$rowType = (string)($row['data']['ROW_TYPE'] ?? '');
		if ($rowType === RowType::ELEMENT || $rowType === RowType::SECTION)
		{
			return $rowType;
		}

		return null;
	}

	protected static function isElementRow(array $row): bool
	{
		return static::getRowType($row) === RowType::ELEMENT;
	}

	protected static function getFlatColumnValues(mixed $rawValues, $fieldName = 'VALUE')
	{
		if (!is_array($rawValues))
		{
			return null;
		}
		if (array_key_exists($fieldName, $rawValues))
		{
			return $rawValues[$fieldName];
		}
		else
		{
			$result = [];
			foreach ($rawValues as $row)
			{
				if (is_array($row) && array_key_exists($fieldName, $row))
				{
					$result[] = $row[$fieldName];
				}
			}

			return $result;
		}
	}
}
