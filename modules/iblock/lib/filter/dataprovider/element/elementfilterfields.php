<?php

namespace Bitrix\Iblock\Filter\DataProvider\Element;

use Bitrix\Iblock\Filter\DataProvider\Settings\ElementSettings;
use Bitrix\Iblock\Integration\UI\EntitySelector\IblockPropertyElementProvider;
use Bitrix\Iblock\Integration\UI\EntitySelector\IblockPropertySectionProvider;
use Bitrix\Iblock\PropertyEnumerationTable;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Main\Localization\Loc;
use CIBlockSection;

class ElementFilterFields
{
	private const PROPERTY_PREFIX = 'PROPERTY_';
	private const PROPERTY_MASK = '/^' . self::PROPERTY_PREFIX . '([0-9]+)$/';

	private int $iblockId;
	private bool $isShowXmlId;
	private bool $isShowSections;
	private array $propertyList = [];
	private array $userTypeList;

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
		$fields['DATE_ACTIVE_FROM'] = [
			'type' => 'date',
			'default' => false,
		];
		$fields['DATE_ACTIVE_TO'] = [
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
			PropertyTable::TYPE_ELEMENT => 'entity_selector',
			PropertyTable::TYPE_SECTION => 'entity_selector',
			PropertyTable::TYPE_STRING . ':' . PropertyTable::USER_TYPE_DIRECTORY => 'entity_selector',
			PropertyTable::TYPE_STRING . ':' . PropertyTable::USER_TYPE_DATE => 'date',
			PropertyTable::TYPE_STRING . ':' . PropertyTable::USER_TYPE_DATETIME => 'datetime',
			PropertyTable::TYPE_ELEMENT . ':' . PropertyTable::USER_TYPE_ELEMENT_AUTOCOMPLETE => 'entity_selector',
			PropertyTable::TYPE_ELEMENT . ':' . PropertyTable::USER_TYPE_SKU => 'entity_selector',
			PropertyTable::TYPE_SECTION . ':' . PropertyTable::USER_TYPE_SECTION_AUTOCOMPLETE => 'entity_selector',
		];

		$iterator = PropertyTable::getList([
			'select' => [
				'ID',
				'IBLOCK_ID',
				'NAME',
				'SORT',
				'PROPERTY_TYPE',
				'LIST_TYPE',
				'MULTIPLE',
				'LINK_IBLOCK_ID',
				'USER_TYPE',
				'USER_TYPE_SETTINGS_LIST',
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
			$row = $this->validateProperty($row);
			if ($row === null)
			{
				continue;
			}

			$fullType = $this->getFullPropertyType($row);

			$fieldType = $typesMap[$fullType] ?? null;
			if (!$fieldType)
			{
				continue;
			}

			$id = $this->getPropertyId($row['ID']);
			$field = [
				'type' => $fieldType,
				'name' => $row['NAME'],
			];

			if ($row['USER_TYPE'] === '')
			{
				switch ($row['PROPERTY_TYPE'])
				{
					case PropertyTable::TYPE_LIST:
					case PropertyTable::TYPE_ELEMENT:
					case PropertyTable::TYPE_SECTION:
						$field['partial'] = true;
						break;
				}
			}
			else
			{
				$userType = $this->userTypeList[$row['USER_TYPE']];
				if (isset($userType['GetUIFilterProperty']) && is_callable($userType['GetUIFilterProperty']))
				{
					call_user_func_array(
						$userType['GetUIFilterProperty'],
						[
							$row,
							[],
							&$field
						]
					);
					if (empty($field) || !is_array($field))
					{
						continue;
					}
					$field['partial'] = true;
				}
			}

			$properties[$id] = $field;

			$this->propertyList[$id] = $row;
		}
		unset($row, $iterator);

		return $properties;
	}

	public function prepareFilterValue(array $rawFilterValue): array
	{
		$result = [];
		foreach ($rawFilterValue as $fieldId => $values)
		{
			$field = \CIBlock::MkOperationFilter($fieldId);
			$id = $field['FIELD'];
			if (!$this->isPropertyId($id))
			{
				$result[$fieldId] = $values;
			}
			elseif (isset($this->propertyList[$id]))
			{
				$prepareResult = $this->preparePropertyValues($id, $values);
				if ($prepareResult)
				{
					$result[$fieldId] = $prepareResult;
				}
				unset($prepareResult);
			}
		}

		return $result;
	}

	private function preparePropertyValues(string $propertyId, $values): mixed
	{
		$result = null;
		$row = $this->propertyList[$propertyId];
		if ($row['USER_TYPE'] === '')
		{
			$result = $values;
		}
		else
		{
			$userType = $this->userTypeList[$row['USER_TYPE']] ?? null;
			if ($userType)
			{
				if (isset($userType['ConvertToDB']) && is_callable($userType['ConvertToDB']))
				{
					if (is_array($values))
					{
						$prepareValues = [];
						foreach ($values as $item)
						{
							$prepareItem = $this->convertPropertyValueToDb(
								$userType['ConvertToDB'],
								$row,
								$item
							);
							if ($prepareItem)
							{
								$prepareValues[] = $prepareItem;
							}
							unset($prepareItem);
						}
						if (!empty($prepareValues))
						{
							$result = $prepareValues;
						}
						unset($prepareValues);
					}
					else
					{
						$prepareValue = $this->convertPropertyValueToDb(
							$userType['ConvertToDB'],
							$row,
							$values
						);
						if ($prepareValue)
						{
							$result = $prepareValue;
						}
						unset($prepareValue);
					}
				}
				else
				{
					$result = $values;
				}
			}
			unset($userType);
		}
		unset($row);

		return $result;
	}

	private function convertPropertyValueToDb(callable $function, array $property, $value): mixed
	{
		$result = call_user_func_array(
			$function,
			[
				$property,
				[
					'VALUE' => $value,
				],
			]
		);

		if (
			is_array($result) && isset($result['VALUE']) && (string)$result['VALUE'] !== ''
		)
		{
			return $result['VALUE'];
		}

		return null;
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
		if (!$this->isPropertyId($fieldId))
		{
			return false;
		}
		if (!isset($this->propertyList[$fieldId]))
		{
			return false;
		}
		$row = $this->propertyList[$fieldId];

		return
			$row['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST
			&& $row['USER_TYPE'] === ''
		;
	}

	/*
	 * @deprecated
	 */
	public function getPropertyEnumFieldListItems(string $fieldId): array
	{
		$propertyId =  $this->propertyList[$fieldId]['ID'] ?? null;
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

	private function getPropertyId(string|int $id): string
	{
		return self::PROPERTY_PREFIX . $id;
	}

	public function isPropertyId(string $id): bool
	{
		return (preg_match(self::PROPERTY_MASK, $id) === 1);
	}

	public function getPropertyDescription(string $id): ?array
	{
		if (!isset($this->propertyList[$id]))
		{
			return null;
		}

		$row = $this->propertyList[$id];

		$description = null;
		if ($row['USER_TYPE'] === '')
		{
			switch ($row['PROPERTY_TYPE'])
			{
				case PropertyTable::TYPE_LIST:
					$description = [
						'items' => $this->getPropertyEnumValueListItems($row['ID']),
					];
					if (count($description['items']) > 1)
					{
						$description['params'] = [
							'multiple' => true,
						];
					}
					break;
				case PropertyTable::TYPE_ELEMENT:
					$description = $this->getElementPropertyDescription($row);
					break;
				case PropertyTable::TYPE_SECTION:
					$description = $this->getSectionPropertyDescription($row);
					break;
			}
		}
		else
		{
			$userType = $this->userTypeList[$row['USER_TYPE']];
			if (isset($userType['GetUIFilterProperty']) && is_callable($userType['GetUIFilterProperty']))
			{
				$description = [];
				call_user_func_array(
					$userType['GetUIFilterProperty'],
					[
						$row,
						[],
						&$description
					]
				);
				if (empty($description) || !is_array($description))
				{
					$description = null;
				}
			}
		}

		return $description;
	}

	private function validateProperty(array $row): ?array
	{
		$row['ID'] = (int)$row['ID'];
		$row['USER_TYPE'] = (string)$row['USER_TYPE'];
		$row['USER_TYPE_SETTINGS'] = $row['USER_TYPE_SETTINGS_LIST'];
		unset($row['USER_TYPE_SETTINGS_LIST']);
		$row['FULL_PROPERTY_TYPE'] = $this->getFullPropertyType($row);
		if ($row['USER_TYPE'] === '')
		{
			return $row;
		}

		if (!isset($this->userTypeList))
		{
			$this->userTypeList = \CIBlockProperty::GetUserType();
		}
		$userTypeId = $row['USER_TYPE'];
		if (!isset($this->userTypeList[$userTypeId]))
		{
			return null;
		}

		return $row;
	}

	private function getFullPropertyType(array $row): string
	{
		return
			$row['USER_TYPE'] === ''
				? $row['PROPERTY_TYPE']
				: $row['PROPERTY_TYPE'] . ':' . $row['USER_TYPE']
		;
	}

	private function getElementPropertyDescription(array $property): array
	{
		return [
			'type' => 'entity_selector',
			'params' => [
				'multiple' => 'Y',
				'dialogOptions' => [
					'entities' => [
						[
							'id' => IblockPropertyElementProvider::ENTITY_ID,
							'dynamicLoad' => true,
							'dynamicSearch' => true,
							'options' => [
								'iblockId' => (int)($property['LINK_IBLOCK_ID'] ?? 0),
							],
						],
					],
					'searchOptions' => [
						'allowCreateItem' => false,
					],
				],
			],
		];
	}

	private function getSectionPropertyDescription(array $property): array
	{
		return [
			'type' => 'entity_selector',
			'params' => [
				'multiple' => 'Y',
				'dialogOptions' => [
					'entities' => [
						[
							'id' => IblockPropertySectionProvider::ENTITY_ID,
							'dynamicLoad' => true,
							'dynamicSearch' => true,
							'options' => [
								'iblockId' => (int)($property['LINK_IBLOCK_ID'] ?? 0),
							],
						],
					],
					'searchOptions' => [
						'allowCreateItem' => false,
					],
				],
			],
		];
	}
}
