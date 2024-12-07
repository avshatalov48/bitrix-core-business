<?php

namespace Bitrix\Catalog\Product\SystemField;

use Bitrix\Main;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Localization\LanguageTable;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\Catalog\Grid\Panel\ProductGroupAction;
use Bitrix\UI;

class MarkingCodeGroup extends Highloadblock
{
	public const FIELD_ID = 'MARKING_CODE_GROUP';

	protected const SHORT_FIELD_ID = 'PRODUCT_GROUP';

	public const TYPE_ID = Catalog\Product\SystemField\Type\HighloadBlock::class;

	protected const VALUE_NAME_PREFIX = 'MARKING_CODE_GROUP_TYPE_';

	protected const USE_PARENT_PRODUCT_VALUE = -1;
	protected const USE_PARENT_PRODUCT_XML_VALUE = 'PARENT_MARKING_GROUP';

	public static function getConfig(): ?array
	{
		if (!static::isAllowed())
		{
			return null;
		}

		/** @var Catalog\Product\SystemField\Type\HighloadBlock $className */
		$className = static::getTypeId();
		$fieldId = static::getFieldId();

		$result = [
			'HIGHLOADBLOCK' => [
				'TABLE_NAME' => $className::getTableName($fieldId),
				'NAME' => $className::getName($fieldId),
				'FIELDS' => static::getHighloadblockFields(),
				'RIGHTS' => $className::getDefaultRights(),
				'TRANSFORM_VALUES' => static::getHighloadblockTransformValues(),
				'VALUES' => static::getHighloadblockValues(),
			],
			'FIELD' => self::getUserFieldBaseParam() + [
				'SORT' => 100,
				'SHOW_FILTER' => 'S',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
				'SETTINGS' => $className::getDefaultSettings(),
			],
			'FIELD_CONFIG' => [
				'HLFIELD_ID' => 'UF_NAME',
			],
		];

		$titles = static::getMessages(
			__FILE__,
			['TITLES' => 'MARKING_CODE_GROUP_STORAGE_TITLE',]
		);

		$result['HIGHLOADBLOCK'] = $result['HIGHLOADBLOCK'] + $titles;

		$result['FIELD'] += static::getMessages(
			__FILE__,
			[
				'EDIT_FORM_LABEL' => 'MARKING_CODE_GROUP_FIELD_TITLE',
				'LIST_COLUMN_LABEL' => 'MARKING_CODE_GROUP_FIELD_TITLE',
				'LIST_FILTER_LABEL' => 'MARKING_CODE_GROUP_FIELD_TITLE',
			]
		);

		return $result;
	}

	public static function isAllowed(): bool
	{
		/** @var Catalog\Product\SystemField\Type\HighloadBlock $className */
		$className = static::getTypeId();

		if (!$className::isAllowed())
		{
			return false;
		}

		return Main\Application::getInstance()->getLicense()->getRegion() === 'ru';
	}

	protected static function getTitleInternal(): ?string
	{
		return Loc::getMessage('MARKING_CODE_GROUP_FIELD_TITLE');
	}

	public static function getUserFieldBaseParam(): array
	{
		/** @var Catalog\Product\SystemField\Type\HighloadBlock $className */
		$className = static::getTypeId();

		return [
			'ENTITY_ID' => Catalog\ProductTable::getUfId(),
			'FIELD_NAME' => static::getUserFieldName(self::SHORT_FIELD_ID),
			'USER_TYPE_ID' => $className::getUserTypeId(),
			'XML_ID' => static::getFieldId(),
			'MULTIPLE' => 'N',
			'MANDATORY' => 'N',
		];
	}

	/**
	 * @return array
	 */
	protected static function getHighloadblockFields(): array
	{
		$result = [];

		$fieldSettings = [
			'XML_ID' => [
				'DEFAULT_VALUE' => '',
				'SIZE' => 16,
				'ROWS' => 1,
				'MIN_LENGTH' => 0,
				'MAX_LENGTH' => 0,
				'REGEXP' => '/^[0-9]{1,16}$/'
			],
			'NAME' => [
				'DEFAULT_VALUE' => '',
				'SIZE' => 100,
				'ROWS' => 1,
				'MIN_LENGTH' => 1,
				'MAX_LENGTH' => 255,
				'REGEXP' => ''
			]
		];

		$sort = 100;
		foreach (array_keys($fieldSettings) as $fieldId)
		{
			$messageList = static::getMessages(
				__FILE__,
				[
					'EDIT_FORM_LABEL' => 'MARKING_CODE_GROUP_UF_FIELD_'.$fieldId,
					'LIST_COLUMN_LABEL' => 'MARKING_CODE_GROUP_UF_FIELD_'.$fieldId,
					'LIST_FILTER_LABEL' => 'MARKING_CODE_GROUP_UF_FIELD_'.$fieldId
				]
			);

			$result[] = [
				'FIELD_NAME' => static::getUserFieldName($fieldId),
				'USER_TYPE_ID' => Main\UserField\Types\StringType::USER_TYPE_ID,
				'XML_ID' => $fieldId,
				'SORT' => $sort,
				'MULTIPLE' => 'N',
				'MANDATORY' => 'Y',
				'SHOW_FILTER' => 'S',
				'SHOW_IN_LIST' => 'Y',
				'EDIT_IN_LIST' => 'Y',
				'IS_SEARCHABLE' => 'N',
				'SETTINGS' => $fieldSettings[$fieldId],
			] + $messageList;
			$sort += 100;
		}

		return $result;
	}

	protected static function getHighloadblockValues(): array
	{
		$groupCodes = [
			'02',
			'03',
			'05',
			'17485',
			'8258',
			'8721',
			'9840',
			'06',
			'5010',
			'5137',
			'5139',
			'5140',
		];
		$groupTitles = Loc::loadLanguageFile(
			$_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/catalog/regionalsystemfields/markingcodegroup.php',
			'ru'
		);

		$result = [];
		foreach ($groupCodes as $id)
		{
			$result[] = [
				'UF_XML_ID' => $id,
				'UF_NAME' => $groupTitles[self::VALUE_NAME_PREFIX.$id]
			];
		}

		return $result;
	}

	protected static function getHighloadblockTransformValues(): array
	{
		return [
			[
				'OLD_XML_ID' => '5048',
				'NEW_XML_ID' => '17485',
			],
			[
				'OLD_XML_ID' => '5408',
				'NEW_XML_ID' => '17485',
			],
		];
	}

	protected static function getGridActionConfig(ProductGroupAction $panel): ?array
	{
		$catalog = $panel->getCatalogConfig();
		if (empty($catalog))
		{
			return null;
		}
		$allowForOffers =
			$catalog['CATALOG_TYPE'] === \CCatalogSku::TYPE_OFFERS
			&& self::isUsedMarkingOffer()
		;
		if (
			$catalog['CATALOG_TYPE'] !== \CCatalogSku::TYPE_CATALOG
			&& $catalog['CATALOG_TYPE'] !== \CCatalogSku::TYPE_FULL
			&& $catalog['CATALOG_TYPE'] !== \CCatalogSku::TYPE_PRODUCT
			&& !$allowForOffers
		)
		{
			return null;
		}

		$field = static::load();
		if (empty($field))
		{
			return null;
		}

		$config = [
			'USER_FIELD' => $field,
			'VISUAL' => [
				'LIST' => [
					'ID' => $panel->getFormRowFieldId($field['FIELD_NAME']),
					'NAME' => $panel->getFormRowFieldName($field['FIELD_NAME']),
				],
			],
		];

		if ($allowForOffers)
		{
			$config['ADDITIONAL_ITEMS'] = [
				'LIST' => [
					[
						'VALUE' => self::USE_PARENT_PRODUCT_VALUE,
						'NAME' => Loc::getMessage('MARKING_CODE_GROUP_MESS_USE_PARENT_PRODUCT_VALUE'),
					],
				]
			];
		}

		return $config;
	}

	public static function getAllowedProductTypeList(): array
	{
		$result = [
			Catalog\ProductTable::TYPE_PRODUCT,
			Catalog\ProductTable::TYPE_SKU,
		];
		if (self::isUsedMarkingOffer())
		{
			$result[] = Catalog\ProductTable::TYPE_OFFER;
		}

		return $result;
	}

	public static function checkRestictions(array $restrictions): bool
	{
		if (!parent::checkRestictions($restrictions))
		{
			return false;
		}

		if (isset($restrictions['IBLOCK_ID']) && !isset($restrictions['CATALOG']))
		{
			$iterator = Catalog\CatalogIblockTable::getList([
				'select' => [
					'*',
				],
				'filter' => [
					'=IBLOCK_ID' => $restrictions['IBLOCK_ID'],
				],
				'cache' => [
					'ttl' => 86400,
				]
			]);
			$row = $iterator->fetch();
			if (!empty($row))
			{
				$restrictions['CATALOG'] = $row;
			}
			unset($row, $iterator);
		}

		if (!empty($restrictions['CATALOG']) && is_array($restrictions['CATALOG']))
		{
			if ($restrictions['CATALOG']['SUBSCRIPTION'] === 'Y')
			{
				return false;
			}
		}

		return true;
	}

	public static function getAllowedOperations(): array
	{
		return [
			Catalog\Product\SystemField::OPERATION_PROVIDER,
			Catalog\Product\SystemField::OPERATION_EXPORT,
			Catalog\Product\SystemField::OPERATION_IMPORT,
		];
	}

	public static function getOperationSelectFieldList(string $operation): array
	{
		if (!static::isAllowed())
		{
			return [];
		}

		$fields = static::getUserFieldBaseParam();
		switch($operation)
		{
			case Catalog\Product\SystemField::OPERATION_PROVIDER:
			case Catalog\Product\SystemField::OPERATION_EXPORT:
			case Catalog\Product\SystemField::OPERATION_IMPORT:
				$result = [
					$fields['XML_ID'] => $fields['FIELD_NAME'],
				];
				break;
			default:
				$result = [];
				break;
		}

		return $result;
	}

	public static function prepareValue(string $operation, array $productRow): array
	{
		$field = static::load();
		if ($field === null)
		{
			return $productRow;
		}
		if (!array_key_exists($field['XML_ID'], $productRow))
		{
			return $productRow;
		}

		switch ($operation)
		{
			case Catalog\Product\SystemField::OPERATION_PROVIDER:
				$productRow = self::prepareValueForProvider($field, $productRow);
				break;
			case Catalog\Product\SystemField::OPERATION_IMPORT:
				$productRow = self::prepareValueForImport($field, $productRow);
				break;
			case Catalog\Product\SystemField::OPERATION_EXPORT:
				$productRow = self::prepareValueForExport($field, $productRow);
				break;
		}

		return $productRow;
	}

	private static function prepareValueForProvider(array $field, array $productRow): array
	{
		$value = $productRow[$field['XML_ID']];
		if ($value !== null)
		{
			$value = (int)$value;
		}
		if (self::isNeedParent($productRow))
		{
			if (
				!self::isUsedMarkingOffer()
				|| $value === self::USE_PARENT_PRODUCT_VALUE
			)
			{
				$productValue = self::getParentProductValue($productRow['ID'], $field);
				if ($value === self::USE_PARENT_PRODUCT_VALUE)
				{
					$value = $productValue;
				}
				else
				{
					$value = $productValue ?? $value;
				}
			}
		}

		$productRow[$field['XML_ID']] = ($value !== null
			? self::getXmlIdById($field['SETTINGS']['HLBLOCK_ID'], (int)$value)
			: null
		);

		return $productRow;
	}

	private static function prepareValueForImport(array $field, array $productRow): array
	{
		$value = $productRow[$field['XML_ID']];
		if ($value === self::USE_PARENT_PRODUCT_XML_VALUE)
		{
			$productRow[$field['FIELD_NAME']] = self::USE_PARENT_PRODUCT_VALUE;
		}
		else
		{
			$productRow[$field['FIELD_NAME']] = ($value !== null
				? self::getIdByXmlId($field['SETTINGS']['HLBLOCK_ID'], $value)
				: null
			);
		}
		unset($productRow[$field['XML_ID']]);

		return $productRow;
	}

	private static function prepareValueForExport(array $field, array $productRow): array
	{
		$value = $productRow[$field['XML_ID']];
		if ($value !== null)
		{
			$value = (int)$value;
		}
		if ($value === self::USE_PARENT_PRODUCT_VALUE)
		{
			$productRow[$field['XML_ID']] = self::USE_PARENT_PRODUCT_XML_VALUE;
		}
		elseif ($value !== null)
		{
			$productRow[$field['XML_ID']] = self::getXmlIdById(
				$field['SETTINGS']['HLBLOCK_ID'],
				$value
			);
		}

		return $productRow;
	}

	private static function isNeedParent(array $productRow): bool
	{
		return
			isset($productRow['ID'])
			&& isset($productRow['TYPE'])
			&& (int)$productRow['TYPE'] === Catalog\ProductTable::TYPE_OFFER
		;
	}

	private static function isUsedMarkingOffer(): bool
	{
		return Option::get('catalog', 'use_offer_marking_code_group') === 'Y';
	}

	private static function getParentProductValue(int $id, $field): ?string
	{
		$result = null;
		$parentsList = \CCatalogSku::getProductList($id);
		if (!empty($parentsList) && isset($parentsList[$id]))
		{
			$row = Catalog\ProductTable::getRow([
				'select' => [
					'ID',
					$field['FIELD_NAME'],
				],
				'filter' => [
					'=ID' => $parentsList[$id]['ID'],
				],
			]);
			if ($row !== null)
			{
				$result = $row[$field['FIELD_NAME']];
			}
		}

		return $result;
	}

	protected static function afterLoadInternalModify(array $row): array
	{
		$row = parent::afterLoadInternalModify($row);
		if (empty($row['SETTINGS']) || !is_array($row['SETTINGS']))
		{
			$row['SETTINGS'] = [];
		}
		$row['SETTINGS']['HLBLOCK_ID'] = (int)($row['SETTINGS']['HLBLOCK_ID'] ?? 0);
		$row['SETTINGS']['HLFIELD_ID'] = (int)($row['SETTINGS']['HLFIELD_ID'] ?? 0);

		return $row;
	}

	public static function updateProductFormConfiguration(): void
	{
		if (!static::isAllowed())
		{
			return;
		}
		$field = static::load();
		if ($field === null)
		{
			return;
		}

		Catalog\Update\UiFormConfiguration::addFormField(
			[
				'name' => $field['FIELD_NAME'],
				'optionFlags' => '1',
				'options' => [
					'showCode' => 'true',
				]
			],
			Catalog\Update\UiFormConfiguration::PARENT_SECTION_MAIN
		);
	}

	public static function renderAdminFormControl(array $field, array $product, array $config): ?string
	{
		$result = parent::renderAdminFormControl($field, $product, $config);
		if ($result !== null)
		{
			if ($product['TYPE'] === Catalog\ProductTable::TYPE_OFFER)
			{
				$parentSelected = (int)$field['VALUE'] === self::USE_PARENT_PRODUCT_VALUE;
				$addOption = '<option value="' . self::USE_PARENT_PRODUCT_VALUE . '"'
					. ($parentSelected ? ' selected' : '')
					. '>' . htmlspecialcharsbx(Loc::getMessage('MARKING_CODE_GROUP_MESS_USE_PARENT_PRODUCT_VALUE'))
					. '</option>'
				;
				if ($parentSelected)
				{
					$result = str_replace('selected', '', $result);
				}
				$index = strpos($result, '</option>');
				if ($index !== false)
				{
					$index += 9; //  after first option
					$result = substr($result, 0, $index)
						. $addOption
						. substr($result, $index)
					;
				}
			}
		}

		return $result;
	}

	protected static function getUiDescriptionInternal(array $description, array $userField, array $restrictions): ?array
	{
		$description['type'] = UI\EntityForm\Control\Type::LIST;

		$config = [
			'RESULT' => [
				'RETURN_FIELD_ID' => 'Y',
			],
		];

		if (
			isset($restrictions['TYPE'])
			&& $restrictions['TYPE'] === Catalog\ProductTable::TYPE_OFFER
			&& self::isUsedMarkingOffer()
		)
		{
			$config['ADDITIONAL_ITEMS'] = [
				'LIST' => [
					0 => [
						'ID' => (string)self::USE_PARENT_PRODUCT_VALUE,
						'VALUE' => (string)self::USE_PARENT_PRODUCT_VALUE,
						'NAME' => Loc::getMessage('MARKING_CODE_GROUP_MESS_USE_PARENT_PRODUCT_VALUE'),
					]
				]
			];
		}

		$items = Type\HighloadBlock::getItems($userField, $config);
		if ($items !== null)
		{
			$description['data'] += [
				'items' => $items
			];
		}
		unset($items);

		return $description;
	}
}
