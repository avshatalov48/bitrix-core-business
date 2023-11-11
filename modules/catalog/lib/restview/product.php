<?php

namespace Bitrix\Catalog\RestView;

use Bitrix\Main\Engine\Response\Converter;
use Bitrix\Main\Error;
use Bitrix\Main\ORM\Fields\ScalarField;
use Bitrix\Main\Result;
use Bitrix\Main\Type\Date;
use Bitrix\Main\Type\DateTime;
use Bitrix\Catalog;
use Bitrix\Catalog\ProductTable;
use Bitrix\Iblock;
use Bitrix\Iblock\PropertyTable;
use Bitrix\Rest\Integration\View\Attributes;
use Bitrix\Rest\Integration\View\Base;
use Bitrix\Rest\Integration\View\DataType;

final class Product extends Base
{
	public const BOOLEAN_VALUE_YES = 'Y';
	public const BOOLEAN_VALUE_NO = 'N';
	private array $productFieldNames = [];

	/**
	 * @return array
	 * return fields all type product
	 */
	public function getFields()
	{
		$this->loadFieldNames();

		return array_merge($this->getFieldsIBlockElement(), $this->getFieldsCatalogProduct());
	}

	/**
	 * @param array $info
	 * @param array $attributs
	 * @return array
	 */
	protected function prepareFieldAttributs($info, $attributs): array
	{
		$r = parent::prepareFieldAttributs($info, $attributs);

		$r['NAME'] = $info['NAME'];
		if ($info['TYPE'] === DataType::TYPE_PRODUCT_PROPERTY)
		{
			$r['IS_DYNAMIC'] = true;
			$r['IS_MULTIPLE'] = in_array(Attributes::MULTIPLE, $attributs, true);
			$r['PROPERTY_TYPE'] = $info['PROPERTY_TYPE'];
			$r['USER_TYPE'] = $info['USER_TYPE'];
			if (isset($info['VALUES']))
			{
				$r['VALUES'] = $info['VALUES'];
			}
		}

		return $r;
	}

	/**
	 * @return array
	 */
	private function getFieldsIBlockElement(): array
	{
		$fieldList = [
			'ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'CREATED_BY' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'DATE_CREATE' => [
				'TYPE' => DataType::TYPE_DATETIME,
			],
			'MODIFIED_BY' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'TIMESTAMP_X' => [
				'TYPE' => DataType::TYPE_DATETIME,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'ACTIVE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'DATE_ACTIVE_FROM' => [
				'TYPE' => DataType::TYPE_DATETIME,
			],
			'DATE_ACTIVE_TO' => [
				'TYPE' => DataType::TYPE_DATETIME,
			],
			'NAME' => [
				'TYPE' => DataType::TYPE_STRING,
				'ATTRIBUTES' => [
					Attributes::REQUIRED_ADD,
				],
			],
			'CODE' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'SORT' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'PREVIEW_TEXT' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'PREVIEW_TEXT_TYPE' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'PREVIEW_PICTURE' => [
				'TYPE' => DataType::TYPE_FILE,
			],
			'DETAIL_TEXT' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'DETAIL_TEXT_TYPE' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'DETAIL_PICTURE' => [
				'TYPE' => DataType::TYPE_FILE,
			],
			'IBLOCK_ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::REQUIRED,
					Attributes::IMMUTABLE,
				],
			],
			'IBLOCK_SECTION_ID' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'XML_ID' => [
				'TYPE' => DataType::TYPE_STRING,
			],
		];

		return $this->fillFieldNames($fieldList);
	}

	/**
	 * @param array $filter
	 * @return Result
	 */
	private function getFieldsIBlockPropertyValuesByFilter(array $filter): Result
	{
		$result = new Result();
		$fieldsInfo = [];

		$iblockId = (int)($filter['IBLOCK_ID'] ?? 0);

		if ($iblockId <= 0)
		{
			$result->addError(new Error('parameter - iblockId is empty'));
		}

		if ($result->isSuccess())
		{
			$catalogInfo = \CCatalogSku::GetInfoByOfferIBlock($iblockId);
			$skuPropertyId = $catalogInfo['SKU_PROPERTY_ID'] ?? null;
			unset($catalogInfo);

			$allowedTypes = array_fill_keys(self::getUserType(), true);

			$cache = [
				'ttl' => 86400,
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
					'IS_REQUIRED',
					'USER_TYPE',
				],
				'filter' => [
					'=IBLOCK_ID' => $iblockId,
					'=ACTIVE' => 'Y',
				],
				'order' => [
					'SORT' => 'ASC',
					'ID' => 'ASC',
				],
				'cache' => $cache,
			]);
			while ($property = $iterator->fetch())
			{
				$property['ID'] = (int)$property['ID'];
				$userType = (string)$property['USER_TYPE'];
				if (
					$userType !== ''
					&& !isset($allowedTypes[$property['PROPERTY_TYPE'] . ':' . $userType])
				)
				{
					continue;
				}

				$info = [
					'TYPE' => DataType::TYPE_PRODUCT_PROPERTY,
					'PROPERTY_TYPE' => $property['PROPERTY_TYPE'],
					'USER_TYPE' => $property['USER_TYPE'],
					'ATTRIBUTES' => [Attributes::DYNAMIC],
					'NAME' => $property['NAME'],
				];

				if ($property['MULTIPLE'] === 'Y')
				{
					$info['ATTRIBUTES'][] = Attributes::MULTIPLE;
				}
				if ($property['IS_REQUIRED'] === 'Y')
				{
					$info['ATTRIBUTES'][] = Attributes::REQUIRED;
				}

				if (
					$property['PROPERTY_TYPE'] === PropertyTable::TYPE_LIST
					&& $userType === ''
					&& $property['MULTIPLE'] === 'N'
				)
				{
					$enumFilter = [
						'=PROPERTY_ID' => $property['ID'],
					];
					if (Iblock\PropertyEnumerationTable::getCount($enumFilter, $cache) === 1)
					{
						$variant = Iblock\PropertyEnumerationTable::getRow([
							'select' => [
								'ID',
								'PROPERTY_ID',
								'VALUE',
							],
							'filter' => $enumFilter,
							'cache' => $cache,
						]);
						$info['BOOLEAN_VALUE_YES'] = [
							'ID' => $variant['ID'],
							'VALUE' => $variant['VALUE'],
						];
					}
				}

				if ($this->isPropertyBoolean($info))
				{
					$info['USER_TYPE'] = Catalog\Controller\Enum::PROPERTY_USER_TYPE_BOOL_ENUM;
				}

				$canonicalName = 'PROPERTY_' . $property['ID'];
				if ($property['ID'] === $skuPropertyId)
				{
					$info['CANONICAL_NAME'] = $canonicalName;
					$fieldsInfo['PARENT_ID'] = $info;
				}
				else
				{
					$fieldsInfo[$canonicalName] = $info;
				}
				unset($canonicalName);
			}
			unset($property, $iterator);

			$fieldsInfo['PROPERTY_*'] = [
				'TYPE' => DataType::TYPE_PRODUCT_PROPERTY,
				'ATTRIBUTES' => [
					Attributes::READONLY,
					Attributes::DYNAMIC,
				],
			];

			$result->setData($fieldsInfo);
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getFieldsCatalogProductCommonFields(): array
	{
		$fieldList = [
			'ID' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'TIMESTAMP_X' => [
				'TYPE' => DataType::TYPE_DATETIME,
			],
			'PRICE_TYPE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'TYPE' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'BUNDLE' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
		];

		return $this->fillFieldNames($fieldList);
	}

	public function isAllowedProductTypeByIBlockId($productTypeId, $iblockId): Result
	{
		$result = new Result();

		$iblockData = \CCatalogSku::GetInfoByIBlock($iblockId);
		if (empty($iblockData))
		{
			$result->addError(new Error('iblock is not catalog'));
		}
		else
		{
			$allowedTypes = self::getProductTypes($iblockData['CATALOG_TYPE']);

			if (!isset($allowedTypes[$productTypeId]))
			{
				$result->addError(new Error('productType is not allowed for this catalog'));
			}
		}

		return $result;
	}

	/**
	 * @param array $filter
	 * @return Result
	 */
	private function getFieldsCatalogProductByFilter(array $filter): Result
	{
		$result = new Result();

		$iblockId = (int)($filter['IBLOCK_ID'] ?? 0);
		$productTypeId = (int)($filter['PRODUCT_TYPE'] ?? 0);

		if ($iblockId <= 0)
		{
			$result->addError(new Error('parameter - iblockId is empty'));
		}

		if ($productTypeId <= 0)
		{
			$result->addError(new Error('parameter - productType is empty'));
		}

		if ($result->isSuccess())
		{
			$r = $this->isAllowedProductTypeByIBlockId($productTypeId, $iblockId);
			if ($r->isSuccess())
			{
				$result->setData($this->getFieldsCatalogProductByType($productTypeId));
			}
			else
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @return array
	 */
	private function getFieldsCatalogProduct(): array
	{
		$fieldList = [
			'TYPE' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'AVAILABLE' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'BUNDLE' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'QUANTITY' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'QUANTITY_RESERVED' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'QUANTITY_TRACE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'CAN_BUY_ZERO' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'SUBSCRIBE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'VAT_ID' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'VAT_INCLUDED' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'PURCHASING_PRICE' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'PURCHASING_CURRENCY' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'BARCODE_MULTI' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'WEIGHT' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'LENGTH' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'WIDTH' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'HEIGHT' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'MEASURE' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'RECUR_SCHEME_LENGTH' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'RECUR_SCHEME_TYPE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'TRIAL_PRICE_ID' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'WITHOUT_ORDER' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
		];

		if (Catalog\Config\State::isUsedInventoryManagement())
		{
			$lockFields = [
				'QUANTITY',
				'QUANTITY_RESERVED',
				'PURCHASING_PRICE',
				'PURCHASING_CURRENCY',
			];

			foreach ($lockFields as $fieldName)
			{
				if (!isset($fieldList[$fieldName]['ATTRIBUTES']))
				{
					$fieldList[$fieldName]['ATTRIBUTES'] = [
						Attributes::READONLY,
					];
				}
				else
				{
					$fieldList[$fieldName]['ATTRIBUTES'][] = Attributes::READONLY;
					$fieldList[$fieldName]['ATTRIBUTES'] = array_unique($fieldList[$fieldName]['ATTRIBUTES']);
				}
			}
		}

		return $this->fillFieldNames($fieldList);
	}

	/**
	 * @param int $id
	 * @return array
	 */
	private function getFieldsCatalogProductByType(int $id): array
	{
		switch ($id)
		{
			case ProductTable::TYPE_SERVICE:
				$r = $this->getFieldsCatalogProductByTypeService();
				break;
			case ProductTable::TYPE_PRODUCT:
				$r = $this->getFieldsCatalogProductByTypeProduct();
				break;
			case ProductTable::TYPE_SET:
				$r = $this->getFieldsCatalogProductByTypeSet();
				break;
			case ProductTable::TYPE_SKU:
			case ProductTable::TYPE_EMPTY_SKU:
				$r = $this->getFieldsCatalogProductByTypeSKU();
				break;
			case ProductTable::TYPE_OFFER:
			case ProductTable::TYPE_FREE_OFFER:
				$r = $this->getFieldsCatalogProductByTypeOffer();
				break;
			default:
				$r = [];
				break;
		}

		return $r;
	}

	/**
	 * @return array
	 */
	private function getFieldsCatalogProductByTypeService(): array
	{
		$fieldList = [
			'AVAILABLE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
		];

		return $this->fillFieldNames($fieldList);
	}

	/**
	 * @return array
	 */
	private function getFieldsCatalogProductByTypeProduct(): array
	{
		$fieldList = [
			'AVAILABLE' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'PURCHASING_PRICE' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'PURCHASING_CURRENCY' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'VAT_ID' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'VAT_INCLUDED' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'QUANTITY' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'QUANTITY_RESERVED' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'MEASURE' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'QUANTITY_TRACE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'CAN_BUY_ZERO' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'NEGATIVE_AMOUNT_TRACE' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'SUBSCRIBE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'WEIGHT' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'LENGTH' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'WIDTH' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'HEIGHT' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
		];

		return $this->fillFieldNames($fieldList);
	}

	/**
	 * @return array
	 */
	private function getFieldsCatalogProductByTypeSKU(): array
	{
		$fieldList = [
			'AVAILABLE' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
		];

		return $this->fillFieldNames($fieldList);
	}

	/**
	 * @return array
	 */
	private function getFieldsCatalogProductByTypeOffer(): array
	{
		return $this->getFieldsCatalogProductByTypeProduct();
	}

	/**
	 * @return array
	 */
	private function getFieldsCatalogProductByTypeSet(): array
	{
		$fieldList = [
			'AVAILABLE' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'PURCHASING_PRICE' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'PURCHASING_CURRENCY' => [
				'TYPE' => DataType::TYPE_STRING,
			],
			'VAT_ID' => [
				'TYPE' => DataType::TYPE_INT,
			],
			'VAT_INCLUDED' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'QUANTITY' => [
				'TYPE' => DataType::TYPE_FLOAT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'MEASURE' => [
				'TYPE' => DataType::TYPE_INT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'QUANTITY_TRACE' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'CAN_BUY_ZERO' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'NEGATIVE_AMOUNT_TRACE' => [
				'TYPE' => DataType::TYPE_CHAR,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'SUBSCRIBE' => [
				'TYPE' => DataType::TYPE_CHAR,
			],
			'WEIGHT' => [
				'TYPE' => DataType::TYPE_FLOAT,
				'ATTRIBUTES' => [
					Attributes::READONLY,
				],
			],
			'LENGTH' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'WIDTH' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
			'HEIGHT' => [
				'TYPE' => DataType::TYPE_FLOAT,
			],
		];

		return $this->fillFieldNames($fieldList);
	}

	/**
	 * @param array $filter
	 * @return Result
	 */
	public function getFieldsByFilter(array $filter): Result
	{
		$result = new Result();

		$iblockId = (int)($filter['IBLOCK_ID'] ?? 0);
		$productTypeId = (int)($filter['PRODUCT_TYPE'] ?? 0);
		if ($iblockId <= 0)
		{
			$result->addError(new Error('parameter - iblockId is empty'));
		}

		if ($productTypeId <= 0)
		{
			$result->addError(new Error('parameter - productType is empty'));
		}

		if ($result->isSuccess())
		{
			$this->loadFieldNames();

			$r = $this->isAllowedProductTypeByIBlockId($productTypeId, $iblockId);
			if ($r->isSuccess())
			{
				$propertyValues = $this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID' => $iblockId]);
				$properties = [];
				if ($propertyValues->isSuccess())
				{
					$properties = $propertyValues->getData();
					unset($properties['PROPERTY_*']);
				}
				unset($propertyValues);
				$result->setData(
					array_merge(
						$this->getFieldsIBlockElement(),
						$properties,
						$this->getFieldsCatalogProductCommonFields(),
						$this->getFieldsCatalogProductByType($productTypeId)
					)
				);
				unset($properties);
			}
			else
			{
				$result->addErrors($r->getErrors());
			}
		}

		return $result;
	}

	/**
	 * @param $catalogType
	 * @return array
	 */
	private static function getProductTypes($catalogType): array
	{
		//TODO: remove after create \Bitrix\Catalog\Model\CatalogIblock
		switch ($catalogType)
		{
			case \CCatalogSku::TYPE_CATALOG:
				$result = [
					ProductTable::TYPE_SERVICE => true,
					ProductTable::TYPE_PRODUCT => true,
					ProductTable::TYPE_SET => true,
				];
				break;
			case \CCatalogSku::TYPE_OFFERS:
				$result = [
					ProductTable::TYPE_OFFER => true,
					ProductTable::TYPE_FREE_OFFER => true,
				];
				break;
			case \CCatalogSku::TYPE_FULL:
				$result = [
					ProductTable::TYPE_SERVICE => true,
					ProductTable::TYPE_PRODUCT => true,
					ProductTable::TYPE_SET => true,
					ProductTable::TYPE_SKU => true,
					ProductTable::TYPE_EMPTY_SKU => true,
				];
				break;
			case \CCatalogSku::TYPE_PRODUCT:
				$result = [
					ProductTable::TYPE_SKU => true,
					ProductTable::TYPE_EMPTY_SKU => true,
				];
				break;
			default:
				$result = [];
				break;
		}

		return $result;
	}

	/**
	 * @return string[]
	 */
	private static function getUserType(): array
	{
		return [
			PropertyTable::TYPE_STRING . ':' . PROPERTYTable::USER_TYPE_DATE,
			PropertyTable::TYPE_STRING . ':' . PropertyTable::USER_TYPE_DATETIME,
			PropertyTable::TYPE_STRING . ':' . PropertyTable::USER_TYPE_HTML,
			PropertyTable::TYPE_STRING . ':' . PropertyTable::USER_TYPE_XML_ID,
			PropertyTable::TYPE_STRING . ':' . PropertyTable::USER_TYPE_DIRECTORY,
			PropertyTable::TYPE_STRING . ':Money',
			PropertyTable::TYPE_STRING . ':map_yandex',
			PropertyTable::TYPE_STRING . ':map_google',
			PropertyTable::TYPE_STRING . ':employee',
			PropertyTable::TYPE_STRING . ':ECrm',
			PropertyTable::TYPE_STRING . ':UserID',

			PropertyTable::TYPE_NUMBER . ':' . PropertyTable::USER_TYPE_SEQUENCE,

			PropertyTable::TYPE_ELEMENT . ':' . PropertyTable::USER_TYPE_ELEMENT_LIST,
			PropertyTable::TYPE_ELEMENT . ':' . PropertyTable::USER_TYPE_ELEMENT_AUTOCOMPLETE,
			PropertyTable::TYPE_ELEMENT . ':' . PropertyTable::USER_TYPE_SKU,

			PropertyTable::TYPE_SECTION . ':' . PropertyTable::USER_TYPE_SECTION_AUTOCOMPLETE,

			//TODO: support types
			//'S:video',
			//'S:TopicID',
			//'S:FileMan',
			//'S:DiskFile',
		];
	}

	public function internalizeFieldsList($arguments, $fieldsInfo = []): array
	{
		// param - IBLOCK_ID is reqired in filter
		$iblockId = (int)($arguments['filter']['IBLOCK_ID'] ?? 0);

		$propertyValues = $this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID' => $iblockId]);
		$fieldsInfo = array_merge(
			$this->getFields(),
			($propertyValues->isSuccess() ? $propertyValues->getData() : [])
		);
		unset($propertyValues);

		return parent::internalizeFieldsList($arguments, $fieldsInfo);
	}

	public function internalizeFieldsAdd($fields, $fieldsInfo = []): array
	{
		// param - IBLOCK_ID is reqired in filter
		$iblockId = (int)($fields['IBLOCK_ID'] ?? 0);

		$propertyValues = $this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID' => $iblockId]);
		$fieldsInfo = array_merge(
			$this->getFields(),
			($propertyValues->isSuccess() ? $propertyValues->getData() : [])
		);
		unset($propertyValues);

		return parent::internalizeFieldsAdd($fields, $fieldsInfo);
	}

	public function internalizeFieldsUpdate($fields, $fieldsInfo = []): array
	{
		// param - IBLOCK_ID is reqired in filter
		$iblockId = (int)($fields['IBLOCK_ID'] ?? 0);

		$propertyValues = $this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID' => $iblockId]);
		$fieldsInfo = array_merge(
			$this->getFields(),
			($propertyValues->isSuccess() ? $propertyValues->getData() : [])
		);
		unset($propertyValues);

		return parent::internalizeFieldsUpdate($fields, $fieldsInfo);
	}

	protected function internalizeDateValue($value): Result
	{
		//API does not accept DataTime objects, so the ISO format is transformed into a format for a filter.

		$r = new Result();

		$date = $this->internalizeDate($value);

		if ($date instanceof Date)
		{
			$value = $date->format('d.m.Y');
		}
		else
		{
			$r->addError(new Error('Wrong type data'));
		}

		if ($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	protected function internalizeDateTimeValue($value): Result
	{
		//API does not accept DataTime objects, so the ISO format is transformed into a format for a filter.

		$r = new Result();

		$date = $this->internalizeDateTime($value);
		if ($date instanceof DateTime)
		{
			$value = $date->format('d.m.Y H:i:s');
		}
		else
		{
			$r->addError(new Error('Wrong type datetime'));
		}

		if ($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	protected function internalizeDateProductPropertyValue($value): Result
	{
		//API does not accept DataTime objects, so the ISO format is transformed into a format for a filter.

		$r = new Result();

		$date = $this->internalizeDate($value);

		if ($date instanceof Date)
		{
			$value = $date->format('Y-m-d');
		}
		else
		{
			$r->addError(new Error('Wrong type data'));
		}

		if ($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	protected function internalizeDateTimeProductPropertyValue($value): Result
	{
		//API does not accept DataTime objects, so the ISO format is transformed into a format for a filter.

		$r = new Result();

		$date = $this->internalizeDateTime($value);
		if ($date instanceof DateTime)
		{
			$value = $date->format('Y-m-d H:i:s');
		}
		else
		{
			$r->addError(new Error('Wrong type datetime'));
		}

		if ($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	protected function internalizeExtendedTypeValue($value, $info): Result
	{
		$r = new Result();

		$type = $info['TYPE'] ?? '';

		if ($type === DataType::TYPE_PRODUCT_PROPERTY)
		{
			$propertyType = $info['PROPERTY_TYPE'] ?? '';
			$userType = $info['USER_TYPE'] ?? '';

			$attrs = $info['ATTRIBUTES'] ?? [];
			$isMultiple = in_array(Attributes::MULTIPLE, $attrs, true);

			$r = $isMultiple ? $this->checkIndexedMultipleValue($value) : new Result();

			if ($r->isSuccess())
			{
				$value = $isMultiple ? $value : [$value];

				if ($propertyType === PropertyTable::TYPE_STRING && $userType === PropertyTable::USER_TYPE_DATE)
				{
					array_walk($value, function(&$item) use ($r)
					{
						$date = $this->internalizeDateProductPropertyValue($item['VALUE']);
						if ($date->isSuccess())
						{
							$item['VALUE'] = $date->getData()[0];
						}
						else
						{
							$r->addErrors($date->getErrors());
						}
					});
				}
				elseif ($propertyType === PropertyTable::TYPE_STRING && $userType === PropertyTable::USER_TYPE_DATETIME)
				{
					array_walk($value, function(&$item) use ($r)
					{
						$date = $this->internalizeDateTimeProductPropertyValue($item['VALUE']);
						if ($date->isSuccess())
						{
							$item['VALUE'] = $date->getData()[0];
						}
						else
						{
							$r->addErrors($date->getErrors());
						}
					});
				}
				elseif ($propertyType === PropertyTable::TYPE_FILE && empty($userType))
				{
					array_walk($value, function(&$item) use ($r)
					{
						$date = $this->internalizeFileValue($item['VALUE']);
						if (!empty($date))
						{
							$item['VALUE'] = $date;
						}
						else
						{
							$r->addError(new Error('Wrong file date'));
						}
					});
				}
				elseif ($this->isPropertyBoolean($info))
				{
					$booleanValue = $value[0]['VALUE'];
					if ($booleanValue === self::BOOLEAN_VALUE_YES)
					{
						$value[0]['VALUE'] = $info['BOOLEAN_VALUE_YES']['ID'];
					}
					elseif ($booleanValue === self::BOOLEAN_VALUE_NO)
					{
						$value[0]['VALUE'] = null;
					}
				}
				//elseif ($propertyType === 'S' && $userType === 'HTML'){}

				$value = $isMultiple? $value: $value[0];
			}
		}

		if ($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	public function internalizeArguments($name, $arguments): array
	{
		if (
			$name === 'getfieldsbyfilter'
			|| $name === 'download'
		)
		{
			return $arguments;
		}

		// Returns throw
		return parent::internalizeArguments($name, $arguments);
	}

	protected function externalizeEmptyValue($name, $value, $fields, $fieldsInfo)
	{
		$fieldInfo = $fieldsInfo[$name] ?? [];
		if ($this->isPropertyBoolean($fieldInfo))
		{
			return self::BOOLEAN_VALUE_NO;
		}

		return parent::externalizeEmptyValue($name, $value, $fields, $fieldsInfo);
	}

	public function externalizeFieldsGet($fields, $fieldsInfo = []): array
	{
		// param - IBLOCK_ID is reqired in filter
		$iblockId = (int)($fields['IBLOCK_ID'] ?? 0);
		$productType = (int)($fields['TYPE'] ?? 0);

		$propertyValues = $this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID' => $iblockId]);
		$product = $this->getFieldsCatalogProductByFilter(['IBLOCK_ID' => $iblockId, 'PRODUCT_TYPE' => $productType]);

		if ($product->isSuccess())
		{
			$fieldsInfo = array_merge(
				$this->getFieldsIBlockElement(),
				($propertyValues->isSuccess() ? $propertyValues->getData() : []),
				$this->getFieldsCatalogProductCommonFields(),
				$product->getData()
			);
		}
		else
		{
			// if it was not possible to determine the view fields by product type,
			// we get the default fields, all fields of the catalog and fields of the Information Block

			$fieldsInfo = array_merge(
				$this->getFields(),
				($propertyValues->isSuccess() ? $propertyValues->getData() : [])
			);
		}
		unset($product, $propertyValues);

		return parent::externalizeFieldsGet($fields, $fieldsInfo);
	}

	public function externalizeListFields($list, $fieldsInfo = []): array
	{
		// param - IBLOCK_ID is reqired in filter
		$iblockId = (int)($list[0]['IBLOCK_ID'] ?? 0);

		$propertyValues = $this->getFieldsIBlockPropertyValuesByFilter(['IBLOCK_ID' => $iblockId]);
		$fieldsInfo = array_merge(
			$this->getFields(),
			($propertyValues->isSuccess() ? $propertyValues->getData() : [])
		);
		unset($propertyValues);

		return parent::externalizeListFields($list, $fieldsInfo);
	}

	public function externalizeResult($name, $fields): array
	{
		if (
			$name === 'getfieldsbyfilter'
			|| $name === 'download'
		)
		{
			return $fields;
		}

		// Returns throw
		return parent::externalizeResult($name, $fields);
	}

	public function convertKeysToSnakeCaseArguments($name, $arguments)
	{
		if ($name === 'getfieldsbyfilter')
		{
			if (isset($arguments['filter']))
			{
				$filter = $arguments['filter'];
				if (!empty($filter))
				{
					$arguments['filter'] = $this->convertKeysToSnakeCaseFilter($filter);
				}
			}
		}
		elseif ($name === 'download')
		{
			if (isset($arguments['fields']))
			{
				$fields = $arguments['fields'];
				if (!empty($fields))
				{
					$converter = new Converter(
						Converter::VALUES
						| Converter::TO_SNAKE
						| Converter::TO_SNAKE_DIGIT
						| Converter::TO_UPPER
					);
					$converterForKey = new Converter(
						Converter::KEYS
						| Converter::TO_SNAKE
						| Converter::TO_SNAKE_DIGIT
						| Converter::TO_UPPER
					);

					$result = [];
					foreach ($converter->process($fields) as $key => $value)
					{
						$result[$converterForKey->process($key)] = $value;
					}
					$arguments['fields'] = $result;
				}
			}
		}
		else
		{
			parent::convertKeysToSnakeCaseArguments($name, $arguments);
		}

		return $arguments;
	}

	public function checkFieldsList($arguments): Result
	{
		$r = new Result();

		$select = $arguments['select'] ?? [];
		if (!is_array($select))
		{
			$select = [];
		}

		$error = [];
		if (!in_array('ID', $select))
		{
			$error[] = 'id';
		}
		if (!in_array('IBLOCK_ID', $select))
		{
			$error[] = 'iblockId';
		}

		if (!empty($error))
		{
			$r->addError(new Error('Required select fields: ' . implode(', ', $error)));
		}

		if (!isset($arguments['filter']['IBLOCK_ID']))
		{
			$r->addError(new Error('Required filter fields: iblockId'));
		}

		return $r;
	}

	public function checkArguments($name, $arguments): Result
	{
		if ($name === 'download')
		{
			$fields = $arguments['fields'];
			return $this->checkFieldsDownload($fields);
		}
		else
		{
			return parent::checkArguments($name, $arguments);
		}
	}

	protected function checkFieldsDownload($fields): Result
	{
		$r = new Result();

		$emptyFields = [];

		if (!isset($fields['FIELD_NAME']))
		{
			$emptyFields[] = 'fieldName';
		}

		if (!isset($fields['FILE_ID']))
		{
			$emptyFields[] = 'fileId';
		}

		if (!isset($fields['PRODUCT_ID']))
		{
			$emptyFields[] = 'productId';
		}

		if (!empty($emptyFields))
		{
			$r->addError(new Error('Required fields: '.implode(', ', $emptyFields)));
		}

		return $r;
	}

	protected function getActionUriToDownload(): string
	{
		return '/rest/catalog.product.download';
	}

	protected function externalizeFileValue($name, $value, $fields): array
	{
		$productId = ($fields['ID'] ?? 0);

		$data = [
			'fields' => [
				'fieldName' => Converter::toJson()
					->process($name)
				,
				'fileId' => $value,
				'productId' => $productId,
			],
		];

		$uri = new \Bitrix\Main\Web\Uri($this->getActionUriToDownload());

		return [
			'ID' => $value,
			'URL' => new \Bitrix\Main\Engine\Response\DataType\ContentUri(
				$uri->addParams($data)
					->__toString()
			),
		];
	}

	protected function externalizeExtendedTypeValue($name, $value, $fields, $fieldsInfo): Result
	{
		$r = new Result();

		$info = $fieldsInfo[$name] ?? [];
		$type = $info['TYPE'] ?? '';

		if ($type === DataType::TYPE_PRODUCT_PROPERTY)
		{
			$attrs = $info['ATTRIBUTES'] ?? [];
			$isMultiple = in_array(Attributes::MULTIPLE, $attrs, true);

			$propertyType = $info['PROPERTY_TYPE'] ?? '';
			$userType = $info['USER_TYPE'] ?? '';

			$value = $isMultiple? $value: [$value];

			if ($propertyType === PropertyTable::TYPE_STRING && $userType === PropertyTable::USER_TYPE_DATE)
			{
				array_walk($value, function(&$item)use($r)
				{
					$date = $this->externalizeDateValue($item['VALUE']);
					if ($date->isSuccess())
					{
						$item['VALUE'] = $date->getData()[0];
					}
					else
					{
						$r->addErrors($date->getErrors());
					}
				});
			}
			elseif ($propertyType === PropertyTable::TYPE_STRING && $userType === PropertyTable::USER_TYPE_DATETIME)
			{
				array_walk($value, function(&$item) use($r)
				{
					$date = $this->externalizeDateTimeValue($item['VALUE']);
					if ($date->isSuccess())
					{
						$item['VALUE'] = $date->getData()[0];
					}
					else
					{
						$r->addErrors($date->getErrors());
					}
				});
			}
			elseif ($propertyType === PropertyTable::TYPE_FILE && empty($userType))
			{
				array_walk($value, function(&$item) use ($fields, $name)
				{
					$item['VALUE'] = $this->externalizeFileValue($name, $item['VALUE'], ['PRODUCT_ID' => $fields['ID']]);
				});
			}
			elseif ($this->isPropertyBoolean($info))
			{
				if ($value)
				{
					$value = self::BOOLEAN_VALUE_YES;
				}
				else
				{
					$value = self::BOOLEAN_VALUE_NO;
				}
			}

			$value = $isMultiple? $value: $value[0];
		}

		if ($r->isSuccess())
		{
			$r->setData([$value]);
		}

		return $r;
	}

	/**
	 * Loads names for standart fields.
	 *
	 * @return void
	 */
	private function loadFieldNames(): void
	{
		if (!empty($this->productFieldNames))
		{
			return;
		}

		$this->loadEntityFieldNames(Iblock\ElementTable::getMap());
		$this->loadEntityFieldNames(Catalog\ProductTable::getMap());
	}

	/**
	 * Loads names for entity scalar fields.
	 *
	 * @param array $fieldList
	 * @return void
	 */
	private function loadEntityFieldNames(array $fieldList)
	{
		/** @var \Bitrix\Main\ORM\Fields\Field $field */
		foreach ($fieldList as $field)
		{
			if ($field instanceof ScalarField)
			{
				$name = $field->getName();
				$title = $field->getTitle();

				$this->productFieldNames[$name] = $title ?: $name;
			}
		}
	}

	/**
	 * Returns field list with name attribute.
	 *
	 * @param array $fieldList
	 * @return array
	 */
	private function fillFieldNames(array $fieldList): array
	{
		foreach (array_keys($fieldList) as $id)
		{
			$fieldList[$id]['NAME'] = $this->productFieldNames[$id] ?? $id;
		}

		return $fieldList;
	}

	private function isPropertyBoolean(array $property): bool
	{
		if (($property['PROPERTY_TYPE'] ?? '') !== PropertyTable::TYPE_LIST)
		{
			return false;
		}
		$attributes = $property['ATTRIBUTES'] ?? [];
		if (!is_array($attributes))
		{
			$attributes = [];
		}
		if (in_array(Attributes::MULTIPLE, $attributes, true))
		{
			return false;
		}
		$userType = (string)($property['USER_TYPE'] ?? '');
		if ($userType !== '' && $userType !== Catalog\Controller\Enum::PROPERTY_USER_TYPE_BOOL_ENUM)
		{
			return false;
		}
		return (!empty($property['BOOLEAN_VALUE_YES']) && is_array($property['BOOLEAN_VALUE_YES']));
	}

	protected function checkIndexedMultipleValue($values): Result
	{
		$r = new Result();

		return
			$this->isIndexedArray($values)
				? $r
				: $r->addError(new Error('For type Multiple field - value must be an Indexed array'))
		;
	}

	protected function isIndexedArray($ary): bool
	{
		if (!is_array($ary))
		{
			return false;
		}

		$keys = array_keys($ary);
		foreach ($keys as $k)
		{
			if (!is_int($k))
			{
				return false;
			}
		}
		return true;
	}
}
