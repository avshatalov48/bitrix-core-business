<?php

namespace Bitrix\Iblock\Grid\Column;

use Bitrix\Main\Grid;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Main\Grid\Column\Editable\Config;

class ElementProvider extends BaseElementProvider
{
	public function prepareColumns(): array
	{
		$result = [];

		if (!$this->isSkuSelectorEnabled())
		{
			$result['NAME'] = [
				'type' => Grid\Column\Type::TEXT,
				'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_NAME'),
				'necessary' => true,
				'editable' => true,
				'multiple' => false,
				'sort' => 'NAME',
			];
			$result['PREVIEW_PICTURE'] = [
				'type' => Grid\Column\Type::FILE,
				'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_PREVIEW_PICTURE'),
				'sort' => 'HAS_PREVIEW_PICTURE',
				'necessary' => false,
				'editable' => true,
				'multiple' => false,
				'prevent_default' => true, // TODO: what is this
			];
			$result['DETAIL_PICTURE'] = [
				'type' => Grid\Column\Type::FILE,
				'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_DETAIL_PICTURE'),
				'sort' => 'HAS_DETAIL_PICTURE',
				'necessary' => false,
				'editable' => true,
				'multiple' => false,
				'prevent_default' => true, // TODO: what is this
			];
		}

		$result = array_merge(
			$this->getCommonColumns(),
			$this->getElementFieldsColumns(),
			$this->getSpecificElementColumns(),
			$result,
			$this->getSectionFields(),
		);

		return $this->createColumns($result);
	}

	protected function getCommonColumns(): array
	{
		$result = [];

		$result['ACTIVE'] = [
			'type' => Grid\Column\Type::CHECKBOX,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_ACTIVE'),
			'title' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_TITLE_ACTIVE'),
			'necessary' => true,
			'editable' => true,
			'multiple' => false,
			'sort' => 'ACTIVE',
			'align' => 'center',
		];
		$result['SORT'] = [
			'type' => Grid\Column\Type::INT,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_SORT'),
			'title' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_TITLE_SORT'),
			'necessary' => false,
			'editable' => true,
			'multiple' => false,
			'sort' => 'SORT',
			'align' => 'right',
		];
		$result['CODE'] = [
			'type' => Grid\Column\Type::TEXT,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_CODE'),
			'title' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_TITLE_CODE'),
			'necessary' => false,
			'editable' => true,
			'multiple' => false,
			'sort' => 'CODE',
		];
		$result['XML_ID'] = [
			'type' => Grid\Column\Type::TEXT,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_XML_ID_MSGVER_1'),
			'title' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_TITLE_XML_ID'),
			'necessary' => false,
			'editable' => true,
			'multiple' => false,
			'sort' => 'XML_ID',
		];
		$result['TIMESTAMP_X'] = [
			'type' => Grid\Column\Type::DATE,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_TIMESTAMP_X'),
			'title' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_TITLE_TIMESTAMP_X'),
			'necessary' => false,
			'editable' => false,
			'multiple' => false,
			'sort' => 'TIMESTAMP_X',
		];
		$result['MODIFIED_BY'] = [
			'type' => Grid\Column\Type::CUSTOM,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_MODIFIED_BY'),
			'title' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_TITLE_MODIFIED_BY'),
			'necessary' => false,
			'editable' => false,
			'multiple' => false,
			'sort' => 'MODIFIED_BY',
			'safeMode' => true,
		];
		$result['DATE_CREATE'] = [
			'type' => Grid\Column\Type::DATE,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_DATE_CREATE'),
			'title' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_TITLE_DATE_CREATE'),
			'necessary' => false,
			'editable' => false,
			'multiple' => false,
			'sort' => 'DATE_CREATE', // TODO: check - created
		];
		$result['CREATED_BY'] = [
			'type' => Grid\Column\Type::CUSTOM,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_CREATED_BY'),
			'title' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_TITLE_CREATED_BY'),
			'necessary' => false,
			'editable' => false,
			'multiple' => false,
			'sort' => 'CREATED_BY',
			'safeMode' => true,
		];
		$select = [
			'ID',
		];
		if ($this->isSkuSelectorEnabled())
		{
			$select[] = 'NAME';
		}
		$result['ID'] = [
			'type' => Grid\Column\Type::CUSTOM,
			'name' => 'ID',
			'necessary' => true,
			'editable' => false,
			'multiple' => false,
			'select' => $select,
			'sort' => 'ID',
		];
		unset($select);

		return $result;
	}

	protected function getElementFieldsColumns(): array
	{
		$result = [];
		$result['ACTIVE_FROM'] = [
			'type' => Grid\Column\Type::DATE,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_ACTIVE_FROM'),
			'necessary' => false,
			'editable' => true,
			'multiple' => false,
			'sort' => 'ACTIVE_FROM',
		];
		$result['ACTIVE_TO'] = [
			'type' => Grid\Column\Type::DATE,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_ACTIVE_TO'),
			'necessary' => false,
			'editable' => true,
			'multiple' => false,
			'sort' => 'ACTIVE_TO',
		];
		$result['SHOW_COUNTER'] = [
			'type' => Grid\Column\Type::INT,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_SHOW_COUNTER'),
			'necessary' => false,
			'editable' => false,
			'multiple' => false,
			'sort' => 'SHOW_COUNTER',
			'align' => 'right',
		];
		$result['SHOW_COUNTER_START'] = [
			'type' => Grid\Column\Type::DATE,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_SHOW_COUNTER_START'),
			'necessary' => false,
			'editable' => false,
			'multiple' => false,
			'sort' => 'SHOW_COUNTER_START',
		];

		$result['PREVIEW_TEXT'] = [
			'type' => Grid\Column\Type::TEXT,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_PREVIEW_TEXT'),
			'necessary' => false,
			'editable' => true,
			'multiple' => false,
			'select' => [
				'PREVIEW_TEXT',
				'PREVIEW_TEXT_TYPE',
			],
			'editable' => new Config('PREVIEW_TEXT', Grid\Editor\Types::TEXTAREA),
		];

		$result['DETAIL_TEXT'] = [
			'type' => Grid\Column\Type::TEXT,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_DETAIL_TEXT'),
			'necessary' => false,
			'editable' => true,
			'multiple' => false,
			'select' => [
				'DETAIL_TEXT',
				'DETAIL_TEXT_TYPE',
			],
			'editable' => new Config('DETAIL_TEXT', Grid\Editor\Types::TEXTAREA),
		];

		$result['TAGS'] = [
			'type' => Grid\Column\Type::INPUT,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_TAGS'),
			'necessary' => false,
			'editable' => true,
			'multiple' => false,
			'sort' => 'TAGS',
		];

		return $result;
	}

	private function getSpecificElementColumns(): array
	{
		$result = [];

		if ($this->isAllowedIblockSections())
		{
			$result['SECTIONS'] = [
				'type' => Grid\Column\Type::CUSTOM,
				'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_SECTIONS'),
				'necessary' => false,
				'editable' => false,
				'multiple' => false,
				'safeMode' => true,
			];
		}

		return $result;
	}

	protected function getSectionFields(): array
	{
		if (!$this->isIblockCombinedMode())
		{
			return [];
		}

		$result = [];
		$result['ELEMENT_CNT'] = [
			'type' => Grid\Column\Type::CUSTOM,
			'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_ELEMENT_CNT'),
			'necessary' => false,
			'editable' => false,
			'multiple' => false,
			'sort' => 'ELEMENT_CNT',
			'align' => 'right',
		];
		if ($this->getIblockId() !== null)
		{
			$result['SECTION_CNT'] = [
				'type' => Grid\Column\Type::CUSTOM,
				'name' => Loc::getMessage('IBLOCK_ELEMENT_COLUMN_PROVIDER_FIELD_SECTION_CNT'),
				'necessary' => false,
				'editable' => false,
				'multiple' => false,
				'align' => 'right',
			];
		}

		return $result;
	}

	protected function isAllowedIblockSections(): bool
	{
		$settings = $this->getSettings();

		return $settings->isAllowedIblockSections();
	}

	protected function getIblockListMode(): string
	{
		$settings = $this->getSettings();

		return $settings->getListMode();
	}

	protected function isIblockSeparateMode(): bool
	{
		return $this->getIblockListMode() === Iblock\IblockTable::LIST_MODE_SEPARATE;
	}

	protected function isIblockCombinedMode(): bool
	{
		return $this->getIblockListMode() === Iblock\IblockTable::LIST_MODE_COMBINED;
	}
}
