<?php

namespace Bitrix\Iblock\Grid\Row\Assembler;

use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Grid\Row\FieldAssembler;
use CIBlockSection;

final class ElementCountFieldAssembler extends FieldAssembler
{
	public function __construct()
	{
		parent::__construct([
			'ELEMENT_CNT',
		]);
	}

	protected function prepareRow(array $row): array
	{
		$value = '';

		$id = (int)($row['data']['ID'] ?? 0);
		$rowType = $row['data']['ROW_TYPE'] ?? null;
		if ($id > 0 && $rowType === RowType::SECTION)
		{
			$count = (int)($row['data']['ELEMENT_CNT'] ?? 0);
			$allCount = CIBlockSection::GetSectionElementsCount($id, ['CNT_ALL' => 'Y']);

			$value = "{$count} ({$allCount})";
		}

		$row['columns'] ??= [];
		$row['columns']['ELEMENT_CNT'] = $value;

		return $row;
	}
}
