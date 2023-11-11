<?php

namespace Bitrix\Catalog\Filter\DataProvider\Measure;

use Bitrix\Catalog\MeasureTable;
use CCatalogMeasureClassifier;

trait MeasureListItems
{
	protected function getMeasureListItems(): array
	{
		$result = [];

		$rows = MeasureTable::getList([
			'select' => [
				'ID',
				'CODE',
				'MEASURE_TITLE',
			],
			'order' => [
				'IS_DEFAULT' => 'ASC',
				'CODE' => 'ASC',
			],
		]);

		foreach ($rows as $row)
		{
			$result[$row['ID']] = $row['MEASURE_TITLE'] ?: CCatalogMeasureClassifier::getMeasureTitle($row['CODE']);
		}

		return $result;
	}
}
