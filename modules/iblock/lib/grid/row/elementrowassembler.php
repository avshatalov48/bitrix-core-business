<?php

namespace Bitrix\Iblock\Grid\Row;

use Bitrix\Iblock\Grid\Entity\ElementSettings;
use Bitrix\Iblock\Grid\Row\Assembler\ElementCountFieldAssembler;
use Bitrix\Iblock\Grid\Row\Assembler\IntranetUserFieldAssembler;
use Bitrix\Iblock\Grid\Row\Assembler\Property\ElementFieldAssembler;
use Bitrix\Iblock\Grid\Row\Assembler\Property\FileFieldAssembler;
use Bitrix\Iblock\Grid\Row\Assembler\Property\ListFieldAssembler;
use Bitrix\Iblock\Grid\Row\Assembler\Property\MultipleFieldAssembler;
use Bitrix\Iblock\Grid\Row\Assembler\Property\NumberFieldAssembler;
use Bitrix\Iblock\Grid\Row\Assembler\Property\SectionFieldAssembler;
use Bitrix\Iblock\Grid\Row\Assembler\Property\StringFieldAssembler;
use Bitrix\Iblock\Grid\Row\Assembler\Property\UserTypePropertyFieldAssembler;
use Bitrix\Iblock\Grid\Row\Assembler\SectionCountFieldAssembler;
use Bitrix\Iblock\Grid\Row\Assembler\SectionNameFieldAssembler;
use Bitrix\Iblock\Grid\RowType;
use Bitrix\Main\Grid\Column\Columns;
use Bitrix\Main\Grid\Editor\Types;
use Bitrix\Main\Grid\Row\Assembler\Field\UserFieldAssembler;
use Bitrix\Main\Grid\Row\FieldAssembler;
use Bitrix\Main\Grid\Row\RowAssembler;
use Bitrix\Main\ModuleManager;

class ElementRowAssembler extends RowAssembler
{
	protected ElementSettings $settings;
	protected Columns $columns;

	public function __construct(
		array $visibleColumnsIds,
		ElementSettings $settings,
		Columns $columns
	)
	{
		parent::__construct($visibleColumnsIds);

		$this->settings = $settings;
		$this->columns = $columns;
	}

	/**
	 * @inheritDoc
	 */
	protected function prepareFieldAssemblers(): array
	{
		$result = [];

		$result[] = $this->getUserAssembler();

		$result[] = new ElementCountFieldAssembler();

		$result[] = new SectionCountFieldAssembler(
			$this->settings->getIblockId()
		);

		$result[] = new SectionNameFieldAssembler(
			['NAME'],
			$this->settings->getUrlBuilder()
		);

		array_push($result, ... $this->getPropertiesAssemblers());

		return $result;
	}

	private function getUserAssembler(): FieldAssembler
	{
		$columnIds = [
			'MODIFIED_BY',
			'CREATED_BY',
		];

		if (ModuleManager::isModuleInstalled('intranet'))
		{
			return new IntranetUserFieldAssembler($columnIds, '/company/personal/user/#ID#/');
		}

		return new UserFieldAssembler($columnIds);
	}

	private function getPropertiesAssemblers(): array
	{
		$result = [];

		$customColumnIds = [];
		foreach ($this->columns as $column)
		{
			$editable = $column->getEditable();
			if (isset($editable) && $editable->getType() === Types::CUSTOM)
			{
				$customColumnIds[] = $column->getId();
			}
		}

		$result[] = new StringFieldAssembler(
			$this->settings->getIblockId(),
			[]
		);

		$result[] = new NumberFieldAssembler(
			$this->settings->getIblockId(),
			[]
		);

		$result[] = new ListFieldAssembler(
			$this->settings->getIblockId()
		);

		$result[] = new ElementFieldAssembler(
			$this->settings->getIblockId(),
			$customColumnIds
		);

		$result[] = new SectionFieldAssembler(
			$this->settings->getIblockId(),
			$customColumnIds
		);

		$result[] = new UserTypePropertyFieldAssembler(
			$this->settings->getIblockId(),
			$customColumnIds
		);

		$result[] = new FileFieldAssembler(
			$this->settings->getIblockId()
		);

		$processedColumnsIds = [];
		foreach ($result as $assembler)
		{
			/**
			 * @var FieldAssembler $assembler
			 */
			array_push($processedColumnsIds, ...$assembler->getColumnIds());
		}

		$result[] = new MultipleFieldAssembler(
			$this->settings->getIblockId(),
			$processedColumnsIds
		);

		return $result;
	}

	public function prepareRows(array $rowsList): array
	{
		$result = parent::prepareRows($rowsList);

		foreach ($result as &$row)
		{
			$rowType = $row['data']['ROW_TYPE'] ?? RowType::ELEMENT;
			$row['id'] = RowType::getIndex($rowType, $row['data']['ID']);

			if ($rowType === RowType::SECTION)
			{
				$row = $this->disableElementFieldsEditableForSection($row);
			}
			// TODO: CRUTCH! remove after main 23.600.0
			else
			{
				$row['data']['~DETAIL_TEXT'] = (string)($row['data']['DETAIL_TEXT'] ?? '');
				$row['data']['~PREVIEW_TEXT'] = (string)($row['data']['PREVIEW_TEXT'] ?? '');
			}
		}
		unset($row);

		return $result;
	}

	private function disableElementFieldsEditableForSection(array $row): array
	{
		$editableColumns = array_fill_keys([
			'NAME',
			'CODE',
			'SORT',
			'XML_ID',
		], true);

		$row['editableColumns'] ??= [];
		foreach ($this->getVisibleColumnIds() as $columnId)
		{
			if (!isset($row['editableColumns'][$columnId]))
			{
				$row['editableColumns'][$columnId] = isset($editableColumns[$columnId]);
			}
		}

		return $row;
	}
}
