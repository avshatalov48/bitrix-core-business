<?php

namespace Bitrix\Iblock\Grid\Row\Assembler;

use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Grid\Row\FieldAssembler;
use CIBlockSection;

final class SectionCountFieldAssembler extends FieldAssembler
{
	private int $iblockId;

	public function __construct(int $iblockId)
	{
		parent::__construct([
			'SECTION_CNT',
		]);

		$this->iblockId = $iblockId;
	}

	protected function prepareRow(array $row): array
	{
		$value = '';

		$id = (int)($row['data']['ID'] ?? 0);
		$rowType = $row['data']['ROW_TYPE'] ?? null;
		if ($id > 0 && $rowType === RowType::SECTION)
		{
			$value = CIBlockSection::GetCount([
				'IBLOCK_ID' => $this->iblockId,
				'SECTION_ID' => $id,
			]);
		}

		$row['columns'] ??= [];
		$row['columns']['SECTION_CNT'] = $value;

		return $row;
	}
}
