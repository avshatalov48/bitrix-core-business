<?php

namespace Bitrix\Catalog\Grid\Row\Assembler;

use Bitrix\Catalog\VatTable;
use Bitrix\Main\Grid\Row\Assembler\Field\ListFieldAssembler;

final class VatFieldAssembler extends ListFieldAssembler
{
	protected function getNames(): array
	{
		$result = [];

		$rows = VatTable::getList([
			'select' => [
				'ID',
				'NAME',
			],
		]);

		foreach ($rows as $row)
		{
			$id = $row['ID'];
			$result[$id] = $row['NAME'];
		}

		return $result;
	}
}
