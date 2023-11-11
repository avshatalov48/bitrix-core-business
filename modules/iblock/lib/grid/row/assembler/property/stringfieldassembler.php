<?php

namespace Bitrix\Iblock\Grid\Row\Assembler\Property;

use Bitrix\Main;
use Bitrix\Iblock\PropertyTable;

class StringFieldAssembler extends BaseFieldAssembler
{
	protected function getPropertyFilter(): array
	{
		return [
			'=PROPERTY_TYPE' => PropertyTable::TYPE_STRING,
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

		foreach ($columnIds as $columnId)
		{
			$value = $this->getFlatColumnValues($row['data'][$columnId] ?? null);

			// edit
			if ($value === null)
			{
				$row['data']['~' . $columnId] = $this->isMultipleColumn($columnId) ? [] : '';
			}
			else
			{
				$row['data']['~' . $columnId] = $value;
			}

			// view
			if (is_array($value))
			{
				$value = implode(' / ', $value);
			}
			$value = Main\Text\HtmlFilter::encode((string)$value);
			$row['columns'][$columnId] = $value;

			unset($value);
		}

		return $row;
	}
}