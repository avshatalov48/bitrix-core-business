<?php

namespace Bitrix\Iblock\Grid\Panel\UI\Actions\Item\ElementGroup\Helpers;

use Bitrix\Main\Grid\Panel\Types;
use Bitrix\Main\Localization\Loc;
use CIBlockSection;

trait SectionSelectControl
{
	private array $sectionTree;

	abstract protected function getIblockId(): int;

	protected function getSectionSelectControl(bool $withTopLevel): array
	{
		$dropdownSectionItems = [];

		if ($withTopLevel)
		{
			$dropdownSectionItems[] = [
				'NAME' => Loc::getMessage('IBLOCK_GRID_PANEL_UI_ACTIONS_ITEM_ELEMENT_GROUP_SECTION_TREE_TOP_LEVEL_NAME'),
				'VALUE' => 0,
			];
		}

		if (!isset($this->sectionTree))
		{
			$this->sectionTree = [];

			$rows = CIBlockSection::getTreeList(
				['IBLOCK_ID' => $this->getIblockId()],
				['ID', 'NAME', 'DEPTH_LEVEL']
			);
			while ($row = $rows->Fetch())
			{
				$this->sectionTree[] = [
					'NAME' => str_repeat(' . ', $row['DEPTH_LEVEL']) . $row['NAME'],
					'VALUE' => (int)$row['ID'],
				];
			}
		}

		array_push($dropdownSectionItems, ...$this->sectionTree);

		return [
			'TYPE' => Types::DROPDOWN,
			'ID' => 'section_id',
			'NAME' => 'section_id',
			'ITEMS' => $dropdownSectionItems,
		];
	}
}
