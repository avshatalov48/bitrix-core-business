<?php

namespace Bitrix\Iblock\Grid\Row\Assembler\Property;

use Bitrix\Main;
use Bitrix\Iblock\Grid\Column\ElementPropertyProvider;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;

final class ListFieldAssembler extends BaseFieldAssembler
{
	public function __construct(int $iblockId)
	{
		parent::__construct($iblockId, []);
	}

	protected function getPropertyFilter(): array
	{
		return [
			'=PROPERTY_TYPE' => PropertyTable::TYPE_LIST,
			'=USER_TYPE' => null,
		];
	}

	protected function validateProperty(array $property): ?array
	{
		return $property;
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

		$row['columns'] ??= [];

		$columnToPropertyIds = ElementPropertyProvider::getPropertyIdsFromColumnsIds($columnIds);
		foreach ($columnToPropertyIds as $columnId => $propertyId)
		{
			// edit
			$value = $this->getColumnValues($row['data'][$columnId] ?? null, 'VALUE_ENUM_ID');
			Main\Type\Collection::normalizeArrayValuesByInt($value, false);
			if ($this->isMultipleColumn($columnId))
			{
				$row['data']['~' . $columnId] = $value;
			}
			else
			{
				$row['data']['~' . $columnId] = empty($value) ? '' : reset($value);
			}

			// view
			$value = $this->getColumnValues($row['data'][$columnId] ?? null, 'VALUE');
			$viewValue = '';
			if (!empty($value))
			{
				$viewValue = join(' / ', $value);
			}
			$viewValue = Main\Text\HtmlFilter::encode($viewValue);
			$row['columns'][$columnId] ??= $viewValue;
		}

		/*
		$columnToPropertyIds = ElementPropertyProvider::getPropertyIdsFromColumnsIds($columnIds);
		foreach ($columnToPropertyIds as $columnId => $propertyId)
		{
			if (isset($row['columns'][$columnId]))
			{
				continue;
			}

			$value = $row['data'][$columnId] ?? null;
			if (is_array($value))
			{
				$tmp = [];
				foreach ($value as $valueItem)
				{
					if (is_numeric($valueItem))
					{
						$tmp[] = $this->getEnumValue($propertyId, $valueItem);
					}
					else
					{
						$tmp[] = $valueItem;
					}

				}

				$value = join(' / ', $tmp);
			}
			elseif (is_numeric($value))
			{
				$value = $this->getEnumValue($propertyId, $value);
			}

			$row['columns'][$columnId] = $value;
		}

		*/

		return $row;
	}
}
