<?php

namespace Bitrix\Iblock\Grid\Row\Assembler\Property;

use Bitrix\Main;
use Bitrix\Iblock\Grid\Column\ElementPropertyProvider;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Grid\Row\FieldAssembler;

final class MultipleFieldAssembler extends FieldAssembler
{
	private int $iblockId;

	public function __construct(int $iblockId, array $excludeColumnsIds)
	{
		$this->iblockId = $iblockId;

		parent::__construct(
			$this->getPropertyColumnsIds($excludeColumnsIds)
		);
	}

	private function getPropertyColumnsIds(array $excludeColumnsIds): array
	{
		$result = [];

		$rows = PropertyTable::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=IBLOCK_ID' => $this->iblockId,
				'=MULTIPLE' => 'Y',
				'USER_TYPE' => null,
			],
		]);
		foreach ($rows as $row)
		{
			$columnId = ElementPropertyProvider::getColumnIdByPropertyId((int)$row['ID']);
			if (!in_array($columnId, $excludeColumnsIds, true))
			{
				$result[] = $columnId;
			}
		}

		return $result;
	}

	protected function prepareRow(array $row): array
	{
		$columnIds = $this->getColumnIds();
		if (empty($columnIds))
		{
			return $row;
		}

		$row['columns'] ??= [];

		foreach ($columnIds as $columnId)
		{
			$value = $this->getFlatColumnValues($row['data'][$columnId] ?? null);
			if (is_array($value))
			{
				$value = join(' / ', $value);
			}
			$value = Main\Text\HtmlFilter::encode((string)$value);

			$row['columns'][$columnId] ??= $value;
			$row['data']['~' . $columnId] ??= $value;
		}

		return $row;
	}

	private static function getFlatColumnValues(mixed $rawValues)
	{
		if (!is_array($rawValues))
		{
			return null;
		}
		if (array_key_exists('VALUE', $rawValues))
		{
			return $rawValues['VALUE'];
		}
		else
		{
			$result = [];
			foreach ($rawValues as $row)
			{
				if (is_array($row) && array_key_exists('VALUE', $row))
				{
					$result[] = $row['VALUE'];
				}
			}

			return $result;
		}
	}
}
