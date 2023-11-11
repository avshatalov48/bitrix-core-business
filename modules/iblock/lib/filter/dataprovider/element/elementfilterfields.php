<?php

namespace Bitrix\Iblock\Filter\DataProvider\Element;

use Bitrix\Iblock\Filter\DataProvider\Settings\ElementSettings;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;
use CIBlockSection;

class ElementFilterFields
{
	private int $iblockId;
	private bool $isShowXmlId;
	private bool $isShowSections;
	private array $listPropertyFieldIds = [];

	public function __construct(int $iblockId, bool $isShowXmlId, bool $isShowSections)
	{
		$this->iblockId = $iblockId;
		$this->isShowXmlId = $isShowXmlId;
		$this->isShowSections = $isShowSections;
	}

	public static function createFromElementSettings(ElementSettings $settings): self
	{
		return new static(
			$settings->getIblockId(),
			$settings->isShowXmlId(),
			$settings->isShowSections()
		);
	}

	public function getElementFieldsParams(): array
	{
		$fields = [];

		$fields['NAME'] = [
			'type' => 'string',
			'default' => true,
		];
		$fields['ID'] = [
			'type' => 'number',
			'default' => true,
		];

		if ($this->isShowSections)
		{
			$fields['SECTION_ID'] = [
				'type' => 'list',
				'default' => true,
				'partial' => true,
			];
			$fields['INCLUDE_SUBSECTIONS'] = [
				'type' => 'checkbox',
				'default' => true,
			];
		}

		$fields['ACTIVE'] = [
			'type' => 'checkbox',
			'default' => false,
		];

		if ($this->isShowXmlId)
		{
			$fields['XML_ID'] = [
				'type' => 'string',
				'default' => false,
			];
		}
		$fields['CODE'] = [
			'type' => 'string',
			'default' => false,
		];
		$fields['TIMESTAMP_X'] = [
			'type' => 'date',
			'default' => false,
		];
		$fields['MODIFIED_BY'] = [
			'type' => 'entity_selector',
			'partial' => true,
		];
		$fields['DATE_CREATE'] = [
			'type' => 'date',
			'default' => false,
		];
		$fields['CREATED_BY'] = [
			'type' => 'entity_selector',
			'partial' => true,
		];
		$fields['ACTIVE_FROM'] = [
			'type' => 'date',
			'default' => false,
		];
		$fields['ACTIVE_TO'] = [
			'type' => 'date',
			'default' => false,
		];

		return $fields;
	}

	public function getElementPropertiesParams(): array
	{
		$properties = [];

		$typesMap = [
			PropertyTable::TYPE_STRING => 'string',
			PropertyTable::TYPE_NUMBER => 'number',
			PropertyTable::TYPE_LIST => 'list',
			'S:directory' => 'list',
		];

		$iterator = PropertyTable::getList([
			'select' => [
				'ID',
				'IBLOCK_ID',
				'NAME',
				'SORT',
				'PROPERTY_TYPE',
				'MULTIPLE',
				'USER_TYPE',
			],
			'filter' => [
				'=IBLOCK_ID' => $this->iblockId,
				'=ACTIVE' => 'Y',
				'=FILTRABLE' => 'Y',
			],
			'order' => [
				'SORT' => 'ASC',
				'ID' => 'ASC',
			],
			'cache' => [
				'ttl' => 84600,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$fullType =
				empty($row['USER_TYPE'])
					? $row['PROPERTY_TYPE']
					: "{$row['PROPERTY_TYPE']}:{$row['USER_TYPE']}"
			;

			$fieldType = $typesMap[$fullType] ?? null;
			if (!$fieldType)
			{
				continue;
			}

			$id = 'PROPERTY_' . $row['ID'];
			$field = [
				'type' => $fieldType,
				'name' => $row['NAME'],
			];

			if ($fullType === PropertyTable::TYPE_LIST)
			{
				$field['partial'] = true;
				$this->listPropertyFieldIds[$id] = (int)$row['ID'];
			}

			$properties[$id] = $field;
		}
		unset($row, $iterator);

		return $properties;
	}

	public function getSectionListItems(): array
	{
		$result = [];

		$result[''] = null;
		$result[0] = Loc::getMessage('IBLOCK_FILTER_DATAPROVIDER_ELEMENT_FIELDS_SECTION_TOP_LEVEL');

		$iterator = CIBlockSection::GetList(
			[
				'LEFT_MARGIN' => 'ASC',
			],
			[
				'IBLOCK_ID' => $this->iblockId,
				'ACTIVE' => 'Y',
				'GLOBAL_ACTIVE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			],
			false,
			[
				'ID',
				'NAME',
				'IBLOCK_ID',
				'DEPTH_LEVEL',
				'LEFT_MARGIN',
			]
		);
		while ($row = $iterator->Fetch())
		{
			$margin = max((int)$row['DEPTH_LEVEL'], 1) - 1;
			$result[$row['ID']] = str_repeat('.', $margin) . $row['NAME'];
		}
		unset($row, $iterator);

		return $result;
	}

	public function isPropertyEnumField(string $fieldId): bool
	{
		return isset($this->listPropertyFieldIds[$fieldId]);
	}

	public function getPropertyEnumFieldListItems(string $fieldId): array
	{
		$propertyId = $this->listPropertyFieldIds[$fieldId] ?? null;
		if ($propertyId)
		{
			return $this->getPropertyEnumValueListItems($propertyId);
		}

		return [];
	}

	private function getPropertyEnumValueListItems(int $propertyId): array
	{
		$result = [];
		$iterator = PropertyEnumerationTable::getList([
			'select' => [
				'ID',
				'VALUE',
				'SORT',
			],
			'filter' => [
				'=PROPERTY_ID' => $propertyId,
			],
			'order' => [
				'SORT' => 'ASC',
				'VALUE' => 'ASC',
				'ID' => 'ASC',
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);
		while ($row = $iterator->fetch())
		{
			$result[$row['ID']] = $row['VALUE'];
		}
		unset($row, $iterator);

		return $result;
	}
}
