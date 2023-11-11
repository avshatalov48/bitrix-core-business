<?php

namespace Bitrix\Catalog\Grid\Row\Assembler;

use Bitrix\Main\Grid\Row\Assembler\Field\ListFieldAssembler;
use CCatalogMeasure;

final class MeasureFieldAssembler extends ListFieldAssembler
{
	protected function getNames(): array
	{
		$result = [];

		$rows = CCatalogMeasure::getList();
		while ($row = $rows->Fetch())
		{
			$id = (int)$row['ID'];
			$result[$id] = $row['MEASURE_TITLE'];
		}

		return $result;
	}
}
