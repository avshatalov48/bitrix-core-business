<?php

namespace Bitrix\Catalog\Product\SystemField;

use Bitrix\Catalog\Access\AccessController;
use Bitrix\Catalog\Access\ActionDictionary;
use Bitrix\Catalog\Grid\Panel\ProductGroupAction;
use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;
use Bitrix\UI;

class ProductMapping extends Highloadblock
{
	public const FIELD_ID = 'PRODUCT_MAPPING';

	public const TYPE_ID = Catalog\Product\SystemField\Type\HighloadBlock::class;

	public const MAP_LANDING = 'LANDING';
	public const MAP_FACEBOOK = 'FACEBOOK';

	protected const VALUE_NAME_PREFIX = 'PRODUCT_MAPPING_TYPE_';

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
				'VALUES' => static::getHighloadblockValues(),
			],
			'FIELD' => self::getUserFieldBaseParam() + [
				'SORT' => 200,
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

		$result['FIELD']['SETTINGS']['DEFAULT_VALUE'] = [
			static::MAP_LANDING,
//			static::MAP_FACEBOOK,
		];

		$titles = static::getMessages(
			__FILE__,
			['TITLES' => 'PRODUCT_MAPPING_STORAGE_TITLE',]
		);

		$result['HIGHLOADBLOCK'] = $result['HIGHLOADBLOCK'] + $titles;


		$result['FIELD'] += static::getMessages(
			__FILE__,
			[
				'EDIT_FORM_LABEL' => 'PRODUCT_MAPPING_FIELD_TITLE',
				'LIST_COLUMN_LABEL' => 'PRODUCT_MAPPING_FIELD_TITLE',
				'LIST_FILTER_LABEL' => 'PRODUCT_MAPPING_FIELD_TITLE',
				'HELP_MESSAGE' => 'PRODUCT_MAPPING_FIELD_TITLE_HINT_MSGVER_1',
			]
		);

		return $result;
	}

	public static function isAllowed(): bool
	{
		return Type\HighloadBlock::isAllowed() && static::isBitrix24();
	}

	protected static function getTitleInternal(): ?string
	{
		return Loc::getMessage('PRODUCT_MAPPING_FIELD_TITLE');
	}

	public static function getUserFieldBaseParam(): array
	{
		/** @var Catalog\Product\SystemField\Type\HighloadBlock $className */
		$className = static::getTypeId();
		$fieldId = static::getFieldId();

		return [
			'ENTITY_ID' => Catalog\ProductTable::getUfId(),
			'FIELD_NAME' => static::getUserFieldName($fieldId),
			'USER_TYPE_ID' => $className::getUserTypeId(),
			'XML_ID' => $fieldId,
			'MULTIPLE' => 'Y',
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
				'SIZE' => 50,
				'ROWS' => 1,
				'MIN_LENGTH' => 1,
				'MAX_LENGTH' => 50,
				'REGEXP' => ''
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
					'EDIT_FORM_LABEL' => 'PRODUCT_MAPPING_UF_FIELD_'.$fieldId,
					'LIST_COLUMN_LABEL' => 'PRODUCT_MAPPING_UF_FIELD_'.$fieldId,
					'LIST_FILTER_LABEL' => 'PRODUCT_MAPPING_UF_FIELD_'.$fieldId
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
		$mapId = [
			self::MAP_LANDING,
//			self::MAP_FACEBOOK
		];

		$result = [];

		foreach ($mapId as $id)
		{
			$title = (string)Loc::getMessage(self::VALUE_NAME_PREFIX.$id);
			$result[] = [
				'UF_XML_ID' => $id,
				'UF_NAME' => $title ?: $id,
			];
		}

		return $result;
	}

	protected static function getGridActionConfig(ProductGroupAction $panel): ?array
	{
		$catalog = $panel->getCatalogConfig();
		if (empty($catalog))
		{
			return null;
		}
		if (
			$catalog['CATALOG_TYPE'] !== \CCatalogSku::TYPE_CATALOG
			&& $catalog['CATALOG_TYPE'] !== \CCatalogSku::TYPE_FULL
			&& $catalog['CATALOG_TYPE'] !== \CCatalogSku::TYPE_PRODUCT
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
		];
		$config['VISUAL'] = [
			'LIST' => [
				'ID' => $panel->getFormRowFieldId($field['FIELD_NAME']),
				'NAME' => $panel->getFormRowFieldName($field['FIELD_NAME']),
			]
		];

		return $config;
	}

	public static function getAllowedProductTypeList(): array
	{
		return [
			Catalog\ProductTable::TYPE_PRODUCT,
			Catalog\ProductTable::TYPE_SET,
			Catalog\ProductTable::TYPE_SKU,
			Catalog\ProductTable::TYPE_SERVICE,
		];
	}

	public static function getAllowedOperations(): array
	{
		return [
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
			case Catalog\Product\SystemField::OPERATION_IMPORT:
				$productRow = self::prepareValueForImport($field, $productRow);
				break;
			case Catalog\Product\SystemField::OPERATION_EXPORT:
				$productRow = self::prepareValueForExport($field, $productRow);
				break;
		}

		return $productRow;
	}

	private static function prepareValueForImport(array $field, array $productRow): array
	{
		if (!is_array($productRow[$field['XML_ID']]))
		{
			$productRow[$field['XML_ID']] = [];
		}
		if (!empty($productRow[$field['XML_ID']]))
		{
			$productRow[$field['FIELD_NAME']] = array_values(self::getIdListByXmlId(
				$field['SETTINGS']['HLBLOCK_ID'],
				$productRow[$field['XML_ID']]
			));
		}
		else
		{
			$productRow[$field['FIELD_NAME']] = [];
		}
		unset($productRow[$field['XML_ID']]);

		return $productRow;
	}

	private static function prepareValueForExport(array $field, array $productRow): array
	{
		if (!is_array($productRow[$field['XML_ID']]))
		{
			$productRow[$field['XML_ID']] = [];
		}
		if (!empty($productRow[$field['XML_ID']]))
		{
			$productRow[$field['XML_ID']] = array_values(self::getXmlIdListById(
				$field['SETTINGS']['HLBLOCK_ID'],
				$productRow[$field['XML_ID']]
			));
		}

		return $productRow;
	}

	public static function getExtendedFilterByArea(array $filter, string $areaXmlId): array
	{
		if (!static::isAllowed())
		{
			return $filter;
		}
		if ($areaXmlId === '')
		{
			return $filter;
		}

		$userField = static::load();
		if ($userField === null)
		{
			return $filter;
		}

		if (empty($userField['SETTINGS']) || !is_array($userField['SETTINGS']))
		{
			return $filter;
		}

		/** @var Catalog\Product\SystemField\Type\HighloadBlock $className */
		$className = static::getTypeId();

		$list = $className::getIdByXmlId((int)$userField['SETTINGS']['HLBLOCK_ID'], [$areaXmlId]);
		if (!isset($list[$areaXmlId]))
		{
			return $filter;
		}

		$filter['=PRODUCT_'.static::getUserFieldName(static::getFieldId())] = $list[$areaXmlId];

		return $filter;
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
		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_PUBLIC_VISIBILITY_SET))
		{
			$field['EDIT_IN_LIST'] = 'N';
		}

		return parent::renderAdminFormControl($field, $product, $config);
	}

	protected static function getUiDescriptionInternal(array $description, array $userField, array $restrictions): ?array
	{
		$description['type'] = UI\EntityForm\Control\Type::MULTI_LIST;

		$config = [
			'RESULT' => [
				'RETURN_FIELD_ID' => 'Y',
			],
		];

		$items = Type\HighloadBlock::getItems($userField, $config);
		if ($items !== null)
		{
			$description['data'] += [
				'items' => $items
			];
		}
		unset($items);

		if (!AccessController::getCurrent()->check(ActionDictionary::ACTION_PRODUCT_PUBLIC_VISIBILITY_SET))
		{
			$description['editable'] = false;
			$description['defaultValue'] = [];
			$description['lockText'] = Loc::getMessage('PRODUCT_MAPPING_FIELD_LOCK_TEXT');
		}

		return $description;
	}
}
