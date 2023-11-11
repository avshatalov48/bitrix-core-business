<?php

namespace Bitrix\Iblock\Grid\Row\Assembler\Property;

use Bitrix\Iblock\Grid\RowType;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Iblock\UI\Input\Section;
use Bitrix\Main\SystemException;
use CIBlockSection;

final class SectionFieldAssembler extends BaseFieldAssembler
{
	private array $names;

	protected function getPropertyFilter(): array
	{
		return [
			'=PROPERTY_TYPE' => PropertyTable::TYPE_SECTION,
			'=USER_TYPE' => null,
		];
	}

	protected function validateProperty(array $property): ?array
	{
		$property['LINK_IBLOCK_ID'] = (int)$property['LINK_IBLOCK_ID'];
		if ($property['LINK_IBLOCK_ID'] <= 0)
		{
			$property['LINK_IBLOCK_ID'] = null;
		}

		return $property;
	}

	public function prepareRows(array $rowList): array
	{
		$elementIds = $this->compileColumnValues($rowList, BaseFieldAssembler::NORMALIZE_BY_INT);
		$this->preloadNames($elementIds);
		unset($elementIds);

		return parent::prepareRows($rowList);
	}

	protected function prepareRow(array $row): array
	{
		if (!self::isElementRow($row))
		{
			return $row;
		}

		$columnIds = $this->getColumnIds();
		if (empty($columnIds))
		{
			return $row;
		}

		$rowId = RowType::getIndex(self::getRowType($row), (string)($row['data']['ID'] ?? ''));

		$row['columns'] ??= [];

		foreach ($columnIds as $columnId)
		{
			$value = $this->getColumnValues($row['data'][$columnId] ?? null);
			$viewValue = '';
			if (!empty($value))
			{
				$tmp = [];
				foreach ($value as $valueItem)
				{
					$tmp[] = $this->getName((int)$valueItem);
				}

				$viewValue = join(' / ', $tmp);
			}

			// view
			$row['columns'][$columnId] ??= $viewValue;

			// edit
			if ($this->isCustomEditable($columnId))
			{
				$row['data']['~' . $columnId] = $this->getEditValue($rowId, $columnId, $this->properties[$columnId], $value);
			}
			unset($value);
		}

		return $row;
	}

	private function getName(int $id)
	{
		if (!isset($this->names))
		{
			throw new SystemException('Before need preload sections');
		}

		return $this->names[$id] ?? '';
	}

	private function preloadNames(array $elementIds): void
	{
		$this->names = [];

		if (empty($elementIds))
		{
			return;
		}

		$rows = CIBlockSection::GetList(
			[],
			[
				'ID' => $elementIds,
			],
			false,
			[
				'ID',
				'NAME',
			]
		);
		while ($row = $rows->Fetch())
		{
			$this->names[$row['ID']] = $row['NAME'];
		}
	}

	private function isCustomEditable(string $columnId): bool
	{
		return in_array($columnId, $this->customEditableColumnIds);
	}

	private function getEditValue(string $rowId, string $columnId, array $property, $values): string
	{
		return Section::renderSelector(
			$property,
			$values ?? null,
			[
				'ROW_ID' => $rowId,
				'FIELD_NAME' => $columnId,
			]
		);
	}
}
