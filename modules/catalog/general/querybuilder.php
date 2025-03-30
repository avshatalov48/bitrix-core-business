<?php

use Bitrix\Main;
use Bitrix\Catalog\GroupAccessTable;
use Bitrix\Catalog\GroupTable;
use Bitrix\Catalog\ProductTable;
use Bitrix\Currency;

final class CProductQueryBuilder
{
	public const ENTITY_PRODUCT = 'PRODUCT';
	public const ENTITY_PRODUCT_USER_FIELD = 'PRODUCT_USER_FIELD';
	public const ENTITY_PRICE = 'PRICE';
	public const ENTITY_WARENHOUSE = 'WARENHOUSE';
	public const ENTITY_FLAT_PRICE = 'FLAT_PRICES';
	public const ENTITY_FLAT_WAREHNOUSE = 'FLAT_WARENHOUSES';
	public const ENTITY_FLAT_BARCODE = 'FLAT_BARCODE';
	public const ENTITY_OLD_PRODUCT = 'OLD_PRODUCT';
	public const ENTITY_OLD_PRICE = 'OLD_PRICE';
	public const ENTITY_OLD_STORE = 'OLD_STORE';
	private const ENTITY_CATALOG_IBLOCK = 'CATALOG_IBLOCK';
	private const ENTITY_VAT = 'VAT';

	public const FIELD_ALLOWED_SELECT = 0x0001;
	public const FIELD_ALLOWED_FILTER = 0x0002;
	public const FIELD_ALLOWED_ORDER = 0x0004;
	public const FIELD_ALLOWED_ALL = self::FIELD_ALLOWED_SELECT|self::FIELD_ALLOWED_FILTER|self::FIELD_ALLOWED_ORDER;

	private const FIELD_PATTERN_OLD_STORE = '/^CATALOG_STORE_AMOUNT_([0-9]+)$/';
	private const FIELD_PATTERN_OLD_PRICE_ROW = '/^CATALOG_GROUP_([0-9]+)$/';
	private const FIELD_PATTERN_OLD_PRICE = '/^CATALOG_([A-Z][A-Z_]+)+_([0-9]+)$/';
	private const FIELD_PATTERN_OLD_PRODUCT = '/^CATALOG_([A-Z][A-Z_]+)$/';
	private const FIELD_PATTERN_FLAT_ENTITY = '/^([A-Z][A-Z_]+)$/';
	private const FIELD_PATTERN_SEPARATE_ENTITY = '/^([A-Z][A-Z_]+)_([1-9][0-9]*)$/';
	private const FIELD_PATTERN_PRODUCT_USER_FIELD = '/^PRODUCT_(UF_[A-Z0-9_]+)$/';

	private const ENTITY_TYPE_FLAT = 0x0001;
	private const ENTITY_TYPE_SEPARATE = 0x0002;

	private const FIELD_TYPE_INT = 'int';
	private const FIELD_TYPE_FLOAT = 'float';
	private const FIELD_TYPE_CHAR = 'char';
	private const FIELD_TYPE_STRING = 'string';

	private static array $entityDescription = [];

	private static array $entityFields = [];

	private static array $options = [];

	/**
	 * @param array $filter
	 * @param array $options
	 * @return null|array
	 */
	public static function makeFilter(array $filter, array $options = []): ?array
	{
		$query = self::prepareQuery(['filter' => $filter], $options);
		if ($query === null)
		{
			return null;
		}
		if (empty($query['filter']) && empty($query['join']))
		{
			return null;
		}

		return $query;
	}

	/**
	 * @param array $parameters
	 * @param array $options
	 * @return array|null
	 */
	public static function makeQuery(array $parameters, array $options = []): ?array
	{
		$query = self::prepareQuery($parameters, $options);
		if ($query === null)
		{
			return null;
		}
		if (empty($query['select']) && empty($query['filter']) && empty($query['order']))
		{
			return null;
		}

		return $query;
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public static function isValidField(string $field): bool
	{
		$field = strtoupper($field);
		if ($field === '')
		{
			return false;
		}

		self::initEntityDescription();
		self::initEntityFields();

		$prepared = [];

		if (preg_match(self::FIELD_PATTERN_OLD_STORE, $field, $prepared))
		{
			return true;
		}
		if (preg_match(self::FIELD_PATTERN_OLD_PRICE_ROW, $field, $prepared))
		{
			return true;
		}
		if (preg_match(self::FIELD_PATTERN_OLD_PRICE, $field, $prepared))
		{
			return true;
		}
		if (preg_match(self::FIELD_PATTERN_OLD_PRODUCT, $field, $prepared))
		{
			return true;
		}
		if (preg_match(self::FIELD_PATTERN_SEPARATE_ENTITY, $field, $prepared))
		{
			if (self::searchFieldEntity($prepared[1], self::ENTITY_TYPE_SEPARATE))
			{
				return true;
			}
		}
		if (preg_match(self::FIELD_PATTERN_FLAT_ENTITY, $field, $prepared))
		{
			if (self::searchFieldEntity($prepared[1], self::ENTITY_TYPE_FLAT))
			{
				return true;
			}
		}
		if (preg_match(self::FIELD_PATTERN_PRODUCT_USER_FIELD, $field, $prepared))
		{
			return true;
		}

		return false;
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public static function isRealFilterField(string $field): bool
	{
		self::initEntityDescription();
		self::initEntityFields();

		$prepareField = self::parseField($field);
		if (!self::checkPreparedField($prepareField))
		{
			return false;
		}
		if (!self::checkAllowedAction($prepareField['ALLOWED'], self::FIELD_ALLOWED_FILTER))
		{
			return false;
		}

		$description = self::getFieldDescription($prepareField['ENTITY'], $prepareField['FIELD']);
		if (empty($description))
		{
			return false;
		}

		if (isset($description['PHANTOM']))
		{
			return false;
		}

		return true;
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public static function isCatalogFilterField(string $field): bool
	{
		return self::isEntityFilterField(
			$field,
			[
				self::ENTITY_PRODUCT => true,
				self::ENTITY_PRODUCT_USER_FIELD => true,
				self::ENTITY_OLD_PRODUCT => true,
				self::ENTITY_OLD_PRICE => true,
				self::ENTITY_PRICE => true,
				self::ENTITY_FLAT_PRICE => true,
				self::ENTITY_WARENHOUSE => true,
				self::ENTITY_FLAT_WAREHNOUSE => true,
				self::ENTITY_FLAT_BARCODE => true,
				self::ENTITY_OLD_STORE => true,
			]
		);
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public static function isProductFilterField(string $field): bool
	{
		return self::isEntityFilterField(
			$field,
			[
				self::ENTITY_PRODUCT => true,
				self::ENTITY_PRODUCT_USER_FIELD => true,
				self::ENTITY_OLD_PRODUCT => true,
			]
		);
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public static function isPriceFilterField(string $field): bool
	{
		return self::isEntityFilterField(
			$field,
			[
				self::ENTITY_OLD_PRICE => true,
				self::ENTITY_PRICE => true,
				self::ENTITY_FLAT_PRICE => true,
			]
		);
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public static function isWarenhouseFilterField(string $field): bool
	{
		return self::isEntityFilterField(
			$field,
			[
				self::ENTITY_WARENHOUSE => true,
				self::ENTITY_FLAT_WAREHNOUSE => true,
				self::ENTITY_OLD_STORE => true,
			]
		);
	}

	/**
	 * @param array $filter
	 * @param array $order
	 * @param array $options
	 * @return array
	 */
	public static function modifyFilterFromOrder(array $filter, array $order, array $options): array
	{
		$result = $filter;

		self::initEntityDescription();
		self::initEntityFields();

		if (empty($order) || empty($options['QUANTITY']))
		{
			return $result;
		}

		foreach (array_keys($order) as $field)
		{
			$prepareField = self::parseField($field);
			if (!self::checkPreparedField($prepareField))
			{
				continue;
			}
			switch ($prepareField['ENTITY'])
			{
				case self::ENTITY_OLD_PRICE:
					if (
						$prepareField['FIELD'] === 'PRICE'
						|| $prepareField['FIELD'] === 'PRICE_SCALE'
						|| $prepareField['FIELD'] === 'CURRENCY'
					)
					{
						$filterFieldDescription = [
							'ENTITY' => $prepareField['ENTITY'],
							'FIELD' => 'SHOP_QUANTITY',
							'ENTITY_ID' => $prepareField['ENTITY_ID'],
						];
						$filterField = self::getField($filterFieldDescription, []);
						if (!empty($filterField))
						{
							$filterField = $filterField['ALIAS'];
							if (!isset($result[$filterField]))
							{
								$result[$filterField] = $options['QUANTITY'];
							}
						}
						unset($filterField, $filterFieldDescription);
					}
					break;
				case self::ENTITY_PRICE:
				case self::ENTITY_FLAT_PRICE:
					if (
						$prepareField['FIELD'] === 'PRICE'
						|| $prepareField['FIELD'] === 'SCALED_PRICE'
						|| $prepareField['FIELD'] === 'CURRENCY'
					)
					{
						$filterFieldDescription = [
							'ENTITY' => $prepareField['ENTITY'],
							'FIELD' => 'QUANTITY_RANGE_FILTER',
							'ENTITY_ID' => $prepareField['ENTITY_ID'],
						];
						$filterField = self::getField($filterFieldDescription, []);
						if (!empty($filterField))
						{
							$filterField = $filterField['ALIAS'];
							if (!isset($result[$filterField]))
							{
								$result[$filterField] = $options['QUANTITY'];
							}
						}
						unset($filterField, $filterFieldDescription);
					}
					break;
			}
		}

		return $result;
	}

	/**
	 * @param string $field
	 * @param int $useMode
	 * @return string|null
	 */
	public static function convertOldField(string $field, int $useMode): ?string
	{
		self::initEntityDescription();
		self::initEntityFields();

		$prepareField = self::parseField($field);
		if (!self::checkPreparedField($prepareField))
		{
			return null;
		}
		if (!self::checkAllowedAction($prepareField['ALLOWED'], $useMode))
		{
			return null;
		}

		$newEntity = null;
		switch ($prepareField['ENTITY'])
		{
			case self::ENTITY_OLD_PRODUCT:
				$newEntity = self::ENTITY_PRODUCT;
				break;
			case self::ENTITY_OLD_PRICE:
				$newEntity = self::ENTITY_PRICE;
				break;
			case self::ENTITY_OLD_STORE:
				$newEntity = self::ENTITY_WARENHOUSE;
				break;
		}
		if ($newEntity === null)
		{
			return null;
		}

		$description = self::getFieldDescription($prepareField['ENTITY'], $prepareField['FIELD']);
		if (empty($description))
		{
			return null;
		}

		if ($useMode === self::FIELD_ALLOWED_ORDER)
		{
			if (isset($description['ORDER_TRANSFORM']))
			{
				$description = self::getFieldDescription($prepareField['ENTITY'], $description['ORDER_TRANSFORM']);
				if (empty($description))
				{
					return null;
				}
			}
		}

		$newField = [
			'ENTITY' => $newEntity,
			'ENTITY_ID' => $prepareField['ENTITY_ID'],
			'FIELD' => $description['NEW_ID'] ?? $prepareField['FIELD'],
		];
		unset($newEntity, $prepareField);

		$description = self::getFieldDescription($newField['ENTITY'], $newField['FIELD']);
		if (empty($description))
		{
			return null;
		}

		return str_replace('#ENTITY_ID#', $newField['ENTITY_ID'], $description['ALIAS']);
	}

	/**
	 * @param array $select
	 * @return array
	 */
	public static function convertOldSelect(array $select): array
	{
		$result = [];

		if (empty($select))
		{
			return $result;
		}

		foreach ($select as $index => $field)
		{
			$newField = self::convertOldField($field, self::FIELD_ALLOWED_SELECT);
			$result[$index] = $newField === null ? $field : $newField;
		}
		unset($newField, $index, $field);

		return $result;
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	public static function convertOldFilter(array $filter): array
	{
		if (empty($filter))
		{
			return [];
		}

		$result = [];

		foreach ($filter as $field => $value)
		{
			if (is_object($value))
			{
				$result[$field] = $value;
			}
			elseif (is_numeric($field))
			{
				$result[$field] =
					is_array($value)
						? self::convertOldFilter($value)
						: $value
				;
			}
			else
			{
				$filterItem = \CIBlock::MkOperationFilter($field);
				if ($filterItem['FIELD'] === 'SUBQUERY')
				{
					if (
						is_array($value)
						&& isset($value['FIELD'])
						&& isset($value['FILTER'])
						&& is_array($value['FILTER'])
					)
					{
						$value['FILTER'] = self::convertOldFilter($value['FILTER']);
					}
					$result[$field] = $value;
				}
				else
				{
					$newField = self::convertOldField($filterItem['FIELD'], self::FIELD_ALLOWED_FILTER);
					if ($newField !== null)
					{
						$result[$filterItem['PREFIX'] . $newField] = $value;
					}
					else
					{
						$result[$field] = $value;
					}
					unset($newField);
				}
				unset($filterItem);
			}
		}
		unset($filed, $value);

		return $result;
	}

	/**
	 * @param array $order
	 * @return array
	 */
	public static function convertOldOrder(array $order): array
	{
		$result = [];

		if (empty($order))
			return $result;

		foreach ($order as $field => $direction)
		{
			$newField = self::convertOldField($field, self::FIELD_ALLOWED_ORDER);
			if ($newField === null)
				$result[$field] = $direction;
			else
				$result[$newField] = $direction;
		}
		unset($newField, $field, $direction);

		return $result;
	}

	/**
	 * @param string $entity
	 * @param array $options
	 * @return array|null
	 */
	public static function getEntityFieldAliases(string $entity, array $options = []): ?array
	{
		if ($entity === '')
		{
			return null;
		}

		$entity = strtoupper($entity);

		self::initEntityDescription();
		self::initEntityFields();

		if (!isset(self::$entityFields[$entity]))
		{
			return null;
		}

		$entityId = '';
		if (
			isset($options['ENTITY_ID'])
			&& (is_string($options['ENTITY_ID']) || is_int($options['ENTITY_ID']))
		)
		{
			$entityId = (string)$options['ENTITY_ID'];
		}

		$result = [];
		foreach (self::$entityFields[$entity] as $field)
		{
			$result[] = str_replace('#ENTITY_ID#', $entityId, $field['ALIAS']);
		}
		unset($field);

		return $result;
	}

	/**
	 * @return void
	 */
	private static function initEntityDescription(): void
	{
		if (!empty(self::$entityDescription))
		{
			return;
		}

		self::$entityDescription = [
			self::ENTITY_PRODUCT => [
				'NAME' => 'b_catalog_product',
				'ALIAS' => 'PRD',
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.ID = #ELEMENT#.ID)'
			],
			self::ENTITY_PRODUCT_USER_FIELD => [
				'EXTERNAL' => true,
				'HANDLER' => [__CLASS__, 'handleProductUserFields'],
			],
			self::ENTITY_PRICE => [
				'NAME' => 'b_catalog_price',
				'ALIAS' => 'PRC_#ENTITY_ID#',
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.PRODUCT_ID = #ELEMENT#.ID and #ALIAS#.CATALOG_GROUP_ID = #ENTITY_ID##JOIN_MODIFY#)'
			],
			self::ENTITY_WARENHOUSE => [
				'NAME' => 'b_catalog_store_product',
				'ALIAS' => 'WHS_#ENTITY_ID#',
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.PRODUCT_ID = #ELEMENT#.ID and #ALIAS#.STORE_ID = #ENTITY_ID#)'
			],
			self::ENTITY_FLAT_PRICE => [
				'NAME' => 'b_catalog_price',
				'ALIAS' => 'PRC_FT',
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.PRODUCT_ID = #ELEMENT#.ID#JOIN_MODIFY#)'
			],
			self::ENTITY_FLAT_WAREHNOUSE => [
				'NAME' => 'b_catalog_store_product',
				'ALIAS' => 'WHS_FT',
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.PRODUCT_ID = #ELEMENT#.ID)'
			],
			self::ENTITY_FLAT_BARCODE => [
				'NAME' => 'b_catalog_store_barcode',
				'ALIAS' => 'BRC_FT',
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.PRODUCT_ID = #ELEMENT#.ID)'
			],
			self::ENTITY_OLD_PRODUCT => [
				'NAME' => 'b_catalog_product',
				'ALIAS' => 'CAT_PR',
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.ID = #ELEMENT#.ID)'
			],
			self::ENTITY_OLD_PRICE => [
				'NAME' => 'b_catalog_price',
				'ALIAS' => 'CAT_P#ENTITY_ID#',
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.PRODUCT_ID = #ELEMENT#.ID and #ALIAS#.CATALOG_GROUP_ID = #ENTITY_ID##JOIN_MODIFY#)'
			],
			self::ENTITY_OLD_STORE => [
				'NAME' => 'b_catalog_store_product',
				'ALIAS' => 'CAT_SP#ENTITY_ID#',
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.PRODUCT_ID = #ELEMENT#.ID and #ALIAS#.STORE_ID = #ENTITY_ID#)'
			],
			self::ENTITY_CATALOG_IBLOCK => [
				'NAME' => 'b_catalog_iblock',
				'ALIAS' => 'CAT_IB',
				'JOIN' => 'left join #NAME# as #ALIAS# on ((CAT_PR.VAT_ID IS NULL or CAT_PR.VAT_ID = 0) and #ALIAS#.IBLOCK_ID = #ELEMENT#.IBLOCK_ID)'
			],
			self::ENTITY_VAT => [
				'NAME' => 'b_catalog_vat',
				'ALIAS' => 'CAT_VAT',
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.ID = CASE WHEN (CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0) THEN CAT_IB.VAT_ID ELSE CAT_PR.VAT_ID END)',
				'RELATION' => [self::ENTITY_CATALOG_IBLOCK]
			],
		];
	}

	/**
	 * @return void
	 */
	private static function initEntityFields(): void
	{
		if (!empty(self::$entityFields))
		{
			return;
		}

		self::$entityFields = [
			self::ENTITY_PRODUCT => self::getProductFields(),
			self::ENTITY_PRODUCT_USER_FIELD => self::getProductUserFields(),
			self::ENTITY_PRICE => self::getPriceFields(),
			self::ENTITY_WARENHOUSE => self::getWarenhouseFields(),
			self::ENTITY_FLAT_PRICE => self::getFlatPriceFields(),
			self::ENTITY_FLAT_WAREHNOUSE => self::getFlatWarenhouseFields(),
			self::ENTITY_FLAT_BARCODE => self::getFlatBarcodeFields(),
			self::ENTITY_OLD_PRODUCT => self::getOldProductFields(),
			self::ENTITY_OLD_PRICE => self::getOldPriceFields(),
			self::ENTITY_OLD_STORE => self::getOldStoreFields(),
			self::ENTITY_VAT => self::getVatFields(),
		];
	}

	/**
	 * @return array[]
	 */
	private static function getProductFields(): array
	{
		return [
			'TYPE' => [
				'NAME' => 'TYPE',
				'ALIAS' => 'TYPE',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'AVAILABLE' => [
				'NAME' => 'AVAILABLE',
				'ALIAS' => 'AVAILABLE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'ORDER_DEFAULT' => 'DESC',
			],
			'BUNDLE' => [
				'NAME' => 'BUNDLE',
				'ALIAS' => 'BUNDLE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'QUANTITY' => [
				'NAME' => 'QUANTITY',
				'ALIAS' => 'QUANTITY',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'QUANTITY_RESERVED' => [
				'NAME' => 'QUANTITY_RESERVED',
				'ALIAS' => 'QUANTITY_RESERVED',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'QUANTITY_TRACE' => [
				'NAME' => 'QUANTITY_TRACE',
				'ALIAS' => 'QUANTITY_TRACE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'SELECT_EXPRESSION' => [__CLASS__, 'selectQuantityTrace'],
				'FILTER_PREPARE_VALUE_EXPRESSION' => [__CLASS__, 'prepareFilterQuantityTrace'],
			],
			'QUANTITY_TRACE_RAW' => [
				'NAME' => 'QUANTITY_TRACE',
				'ALIAS' => 'QUANTITY_TRACE_RAW',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'CAN_BUY_ZERO' => [
				'NAME' => 'CAN_BUY_ZERO',
				'ALIAS' => 'CAN_BUY_ZERO',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'SELECT_EXPRESSION' => [__CLASS__, 'selectCanBuyZero'],
				'FILTER_PREPARE_VALUE_EXPRESSION' => [__CLASS__, 'prepareFilterCanBuyZero'],
			],
			'CAN_BUY_ZERO_RAW' => [
				'NAME' => 'CAN_BUY_ZERO',
				'ALIAS' => 'CAN_BUY_ZERO_RAW',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'SUBSCRIBE' => [
				'NAME' => 'SUBSCRIBE',
				'ALIAS' => 'SUBSCRIBE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'SELECT_EXPRESSION' => [__CLASS__, 'selectSubscribe'],
				'FILTER_PREPARE_VALUE_EXPRESSION' => [__CLASS__, 'prepareFilterSubscribe'],
			],
			'SUBSCRIBE_RAW' => [
				'NAME' => 'SUBSCRIBE',
				'ALIAS' => 'SUBSCRIBE_RAW',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'VAT_ID' => [
				'NAME' => 'VAT_ID',
				'ALIAS' => 'VAT_ID',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'VAT_INCLUDED' => [
				'NAME' => 'VAT_INCLUDED',
				'ALIAS' => 'VAT_INCLUDED',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'PURCHASING_PRICE' => [
				'NAME' => 'PURCHASING_PRICE',
				'ALIAS' => 'PURCHASING_PRICE',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'PURCHASING_CURRENCY' => [
				'NAME' => 'PURCHASING_CURRENCY',
				'ALIAS' => 'PURCHASING_CURRENCY',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'BARCODE_MULTI' => [
				'NAME' => 'BARCODE_MULTI',
				'ALIAS' => 'BARCODE_MULTI',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'WEIGHT' => [
				'NAME' => 'WEIGHT',
				'ALIAS' => 'WEIGHT',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'WIDTH' => [
				'NAME' => 'WIDTH',
				'ALIAS' => 'WIDTH',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'LENGTH' => [
				'NAME' => 'LENGTH',
				'ALIAS' => 'LENGTH',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'HEIGHT' => [
				'NAME' => 'HEIGHT',
				'ALIAS' => 'HEIGHT',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'MEASURE' => [
				'NAME' => 'MEASURE',
				'ALIAS' => 'MEASURE',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'PAYMENT_TYPE' => [
				'NAME' => 'PRICE_TYPE',
				'ALIAS' => 'PAYMENT_TYPE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'RECUR_SCHEME_LENGTH' => [
				'NAME' => 'RECUR_SCHEME_LENGTH',
				'ALIAS' => 'RECUR_SCHEME_LENGTH',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'RECUR_SCHEME_TYPE' => [
				'NAME' => 'RECUR_SCHEME_TYPE',
				'ALIAS' => 'RECUR_SCHEME_TYPE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'TRIAL_PRICE_ID' => [
				'NAME' => 'TRIAL_PRICE_ID',
				'ALIAS' => 'TRIAL_PRICE_ID',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'WITHOUT_ORDER' => [
				'NAME' => 'WITHOUT_ORDER',
				'ALIAS' => 'WITHOUT_ORDER',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
		];
	}

	/**
	 * @return array[]
	 */
	private static function getProductUserFields(): array
	{
		$result = [];

		$iterator = Main\UserFieldTable::getList([
			'select' => [
				'ID',
				'ENTITY_ID',
				'FIELD_NAME',
				'USER_TYPE_ID',
				'XML_ID',
				'SORT',
				'MULTIPLE',
			],
			'filter' => [
				'=ENTITY_ID' => Bitrix\Catalog\ProductTable::getUfId(),
			],
			'order' => [
				'SORT' => 'ASC',
				'ID' => 'ASC',
			],
			'cache' => [
				'ttl' => 86400,
			],
		]);
		while ($row = $iterator->fetch())
		{
			if (!self::isValidProductUserField($row))
			{
				continue;
			}
			$item = [
				'NAME' => $row['FIELD_NAME'],
				'ALIAS' => 'PRODUCT_'.$row['FIELD_NAME'],
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
			];

			$result[$row['FIELD_NAME']] = $item;
		}
		unset(
			$row,
			$iterator,
		);

		return $result;
	}

	/**
	 * @return array[]
	 */
	private static function getPriceFields(): array
	{
		return [
			'PRICE' => [
				'NAME' => 'PRICE',
				'ALIAS' => 'PRICE_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'ORDER_NULLABLE' => true,
			],
			'CURRENCY' => [
				'NAME' => 'CURRENCY',
				'ALIAS' => 'CURRENCY_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'ORDER_NULLABLE' => true,
			],
			'QUANTITY_FROM' => [
				'NAME' => 'QUANTITY_FROM',
				'ALIAS' => 'QUANTITY_FROM_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'ORDER_NULLABLE' => true,
			],
			'QUANTITY_TO' => [
				'NAME' => 'QUANTITY_TO',
				'ALIAS' => 'QUANTITY_TO_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'ORDER_NULLABLE' => true,
			],
			'SCALED_PRICE' => [
				'NAME' => 'PRICE_SCALE',
				'ALIAS' => 'SCALED_PRICE_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'ORDER_NULLABLE' => true,
			],
			'EXTRA_ID' => [
				'NAME' => 'EXTRA_ID',
				'ALIAS' => 'EXTRA_ID_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'DEFAULT_PRICE_FILTER' => [
				'PHANTOM' => true,
				'NAME' => null,
				'ALIAS' => 'DEFAULT_PRICE_FILTER_#ENTITY_ID#',
				'TYPE' => null,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'JOIN_MODIFY_EXPRESSION' => [__CLASS__, 'priceParametersFilter'],
			],
			'QUANTITY_RANGE_FILTER' => [
				'PHANTOM' => true,
				'NAME' => null,
				'ALIAS' => 'QUANTITY_RANGE_FILTER_#ENTITY_ID#',
				'TYPE' => null,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'JOIN_MODIFY_EXPRESSION' => [__CLASS__, 'priceParametersFilter'],
			],
			'CURRENCY_FOR_SCALE' => [
				'PHANTOM' => true,
				'NAME' => null,
				'ALIAS' => 'CURRENCY_FOR_SCALE_#ENTITY_ID#',
				'TYPE' => null,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'FILTER_MODIFY_EXPRESSION' => [__CLASS__, 'filterModifierCurrencyScale'],
			],
		];
	}

	/**
	 * @return array[]
	 */
	private static function getFlatPriceFields(): array
	{
		return [
			'PRICE_TYPE' => [
				'NAME' => 'CATALOG_GROUP_ID',
				'ALIAS' => 'PRICE_TYPE',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
			],
			'PRICE' => [
				'NAME' => 'PRICE',
				'ALIAS' => 'PRICE',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'ORDER_NULLABLE' => true,
			],
			'CURRENCY' => [
				'NAME' => 'CURRENCY',
				'ALIAS' => 'CURRENCY',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'ORDER_NULLABLE' => true,
			],
			'QUANTITY_FROM' => [
				'NAME' => 'QUANTITY_FROM',
				'ALIAS' => 'QUANTITY_FROM',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'ORDER_NULLABLE' => true,
			],
			'QUANTITY_TO' => [
				'NAME' => 'QUANTITY_TO',
				'ALIAS' => 'QUANTITY_TO',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'ORDER_NULLABLE' => true,
			],
			'SCALED_PRICE' => [
				'NAME' => 'PRICE_SCALE',
				'ALIAS' => 'SCALED_PRICE',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'ORDER_NULLABLE' => true,
			],
			'EXTRA_ID' => [
				'NAME' => 'EXTRA_ID',
				'ALIAS' => 'EXTRA_ID',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
			],
			'DEFAULT_PRICE_FILTER' => [
				'PHANTOM' => true,
				'NAME' => null,
				'ALIAS' => 'DEFAULT_PRICE_FILTER',
				'TYPE' => null,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'JOIN_MODIFY_EXPRESSION' => [__CLASS__, 'priceParametersFilter'],
			],
			'QUANTITY_RANGE_FILTER' => [
				'PHANTOM' => true,
				'NAME' => null,
				'ALIAS' => 'QUANTITY_RANGE_FILTER',
				'TYPE' => null,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'JOIN_MODIFY_EXPRESSION' => [__CLASS__, 'priceParametersFilter'],
			],
			'CURRENCY_FOR_SCALE' => [
				'PHANTOM' => true,
				'NAME' => null,
				'ALIAS' => 'CURRENCY_FOR_SCALE',
				'TYPE' => null,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'FILTER_MODIFY_EXPRESSION' => [__CLASS__, 'filterModifierCurrencyScale'],
			],
		];
	}

	/**
	 * @return array[]
	 */
	private static function getWarenhouseFields(): array
	{
		return [
			'STORE_AMOUNT' => [
				'NAME' => 'AMOUNT',
				'ALIAS' => 'STORE_AMOUNT_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'ORDER_NULLABLE' => true,
			],
		];
	}

	/**
	 * @return array[]
	 */
	private static function getFlatWarenhouseFields(): array
	{
		return [
			'STORE_NUMBER' => [
				'NAME' => 'STORE_ID',
				'ALIAS' => 'STORE_NUMBER',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
			],
			'STORE_AMOUNT' => [
				'NAME' => 'AMOUNT',
				'ALIAS' => 'STORE_AMOUNT',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'ORDER_NULLABLE' => true,
			],
		];
	}

	/**
	 * @return array[]
	 */
	private static function getFlatBarcodeFields(): array
	{
		return [
			'PRODUCT_BARCODE' => [
				'NAME' => 'BARCODE',
				'ALIAS' => 'PRODUCT_BARCODE',
				'TYPE' => self::FIELD_TYPE_STRING,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
			],
			'PRODUCT_BARCODE_STORE' => [
				'NAME' => 'STORE_ID',
				'ALIAS' => 'PRODUCT_BARCODE_STORE',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
			],
			'PRODUCT_BARCODE_ORDER' => [
				'NAME' => 'ORDER_ID',
				'ALIAS' => 'PRODUCT_BARCODE_ORDER',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
			],
		];
	}

	/**
	 * @return array[]
	 */
	private static function getOldProductFields(): array
	{
		return [
			'QUANTITY' => [
				'NAME' => 'QUANTITY',
				'ALIAS' => 'CATALOG_QUANTITY',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'QUANTITY_TRACE' => [
				'NAME' => 'QUANTITY_TRACE',
				'ALIAS' => 'CATALOG_QUANTITY_TRACE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
				'SELECT_EXPRESSION' => [__CLASS__, 'selectQuantityTrace'],
			],
			'QUANTITY_TRACE_ORIG' => [
				'NAME' => 'QUANTITY_TRACE',
				'ALIAS' => 'CATALOG_QUANTITY_TRACE_ORIG',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
				'NEW_ID' => 'QUANTITY_TRACE_RAW',
			],
			'WEIGHT' => [
				'NAME' => 'WEIGHT',
				'ALIAS' => 'CATALOG_WEIGHT',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'VAT_ID' => [
				'NAME' => 'VAT_ID',
				'ALIAS' => 'CATALOG_VAT_ID',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'VAT_INCLUDED' => [
				'NAME' => 'VAT_INCLUDED',
				'ALIAS' => 'CATALOG_VAT_INCLUDED',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'CAN_BUY_ZERO' => [
				'NAME' => 'CAN_BUY_ZERO',
				'ALIAS' => 'CATALOG_CAN_BUY_ZERO',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
				'SELECT_EXPRESSION' => [__CLASS__, 'selectCanBuyZero'],
			],
			'CAN_BUY_ZERO_ORIG' => [
				'NAME' => 'CAN_BUY_ZERO',
				'ALIAS' => 'CATALOG_CAN_BUY_ZERO_ORIG',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
				'NEW_ID' => 'CAN_BUY_ZERO_RAW',
			],
			'PURCHASING_PRICE' => [
				'NAME' => 'PURCHASING_PRICE',
				'ALIAS' => 'CATALOG_PURCHASING_PRICE',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'PURCHASING_CURRENCY' => [
				'NAME' => 'PURCHASING_CURRENCY',
				'ALIAS' => 'CATALOG_PURCHASING_CURRENCY',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'QUANTITY_RESERVED' => [
				'NAME' => 'QUANTITY_RESERVED',
				'ALIAS' => 'CATALOG_QUANTITY_RESERVED',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'SUBSCRIBE' => [
				'NAME' => 'SUBSCRIBE',
				'ALIAS' => 'CATALOG_SUBSCRIBE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT|self::FIELD_ALLOWED_FILTER,
				'SELECT_EXPRESSION' => [__CLASS__, 'selectSubscribe'],
				'FILTER_PREPARE_VALUE_EXPRESSION' => [__CLASS__, 'prepareFilterSubscribe'],
			],
			'SUBSCRIBE_ORIG' => [
				'NAME' => 'SUBSCRIBE',
				'ALIAS' => 'CATALOG_SUBSCRIBE_ORIG',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
				'NEW_ID' => 'SUBSCRIBE_RAW',
			],
			'WIDTH' => [
				'NAME' => 'WIDTH',
				'ALIAS' => 'CATALOG_WIDTH',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'LENGTH' => [
				'NAME' => 'LENGTH',
				'ALIAS' => 'CATALOG_LENGTH',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'HEIGHT' => [
				'NAME' => 'HEIGHT',
				'ALIAS' => 'CATALOG_HEIGHT',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'MEASURE' => [
				'NAME' => 'MEASURE',
				'ALIAS' => 'CATALOG_MEASURE',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'TYPE' => [
				'NAME' => 'TYPE',
				'ALIAS' => 'CATALOG_TYPE',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'AVAILABLE' => [
				'NAME' => 'AVAILABLE',
				'ALIAS' => 'CATALOG_AVAILABLE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'ORDER_DEFAULT' => 'DESC',
			],
			'BUNDLE' => [
				'NAME' => 'BUNDLE',
				'ALIAS' => 'CATALOG_BUNDLE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
			],
			'PRICE_TYPE' => [
				'NAME' => 'PRICE_TYPE',
				'ALIAS' => 'CATALOG_PRICE_TYPE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
				'NEW_ID' => 'PAYMENT_TYPE',
			],
			'RECUR_SCHEME_LENGTH' => [
				'NAME' => 'RECUR_SCHEME_LENGTH',
				'ALIAS' => 'CATALOG_RECUR_SCHEME_LENGTH',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'RECUR_SCHEME_TYPE' => [
				'NAME' => 'RECUR_SCHEME_TYPE',
				'ALIAS' => 'CATALOG_RECUR_SCHEME_TYPE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'TRIAL_PRICE_ID' => [
				'NAME' => 'TRIAL_PRICE_ID',
				'ALIAS' => 'CATALOG_TRIAL_PRICE_ID',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'WITHOUT_ORDER' => [
				'NAME' => 'WITHOUT_ORDER',
				'ALIAS' => 'CATALOG_WITHOUT_ORDER',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'SELECT_BEST_PRICE' => [
				'NAME' => 'SELECT_BEST_PRICE',
				'ALIAS' => 'CATALOG_SELECT_BEST_PRICE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'NEGATIVE_AMOUNT_TRACE' => [
				'NAME' => 'NEGATIVE_AMOUNT_TRACE',
				'ALIAS' => 'CATALOG_NEGATIVE_AMOUNT_TRACE',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
				'SELECT_EXPRESSION' => [__CLASS__, 'selectNegativeAmountTrace'],
			],
			'NEGATIVE_AMOUNT_TRACE_ORIG' => [
				'NAME' => 'NEGATIVE_AMOUNT_TRACE',
				'ALIAS' => 'CATALOG_NEGATIVE_AMOUNT_TRACE_ORIG',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
		];
	}

	/**
	 * @return array[]
	 */
	private static function getOldPriceFields(): array
	{
		return [
			'ID' => [
				'NAME' => 'ID',
				'ALIAS' => 'CATALOG_PRICE_ID_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'PRODUCT_ID' => [
				'NAME' => 'PRODUCT_ID',
				'ALIAS' => null,
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
			],
			'CATALOG_GROUP_ID' => [
				'NAME' => 'CATALOG_GROUP_ID',
				'ALIAS' => 'CATALOG_GROUP_ID_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT|self::FIELD_ALLOWED_FILTER,
			],
			'PRICE' => [
				'NAME' => 'PRICE',
				'ALIAS' => 'CATALOG_PRICE_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'ORDER_TRANSFORM' => 'PRICE_SCALE',
			],
			'CURRENCY' => [
				'NAME' => 'CURRENCY',
				'ALIAS' => 'CATALOG_CURRENCY_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'ORDER_NULLABLE' => true,
			],
			'PRICE_SCALE' => [
				'NAME' => 'PRICE_SCALE',
				'ALIAS' => null,
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ORDER|self::FIELD_ALLOWED_FILTER,
				'ORDER_NULLABLE' => true,
				'NEW_ID' => 'SCALED_PRICE',
			],
			'QUANTITY_FROM' => [
				'NAME' => 'QUANTITY_FROM',
				'ALIAS' => 'CATALOG_QUANTITY_FROM_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'QUANTITY_TO' => [
				'NAME' => 'QUANTITY_TO',
				'ALIAS' => 'CATALOG_QUANTITY_TO_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'EXTRA_ID' => [
				'NAME' => 'EXTRA_ID',
				'ALIAS' => 'CATALOG_EXTRA_ID_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_INT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
			'CATALOG_GROUP_NAME' => [
				'NAME' => null,
				'ALIAS' => 'CATALOG_GROUP_NAME_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_STRING,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
				'SELECT_EXPRESSION' => [__CLASS__, 'selectPriceTypeName'],
			],
			'CATALOG_CAN_ACCESS' => [
				'NAME' => null,
				'ALIAS' => 'CATALOG_CAN_ACCESS_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
				'SELECT_EXPRESSION' => [__CLASS__, 'selectPriceTypeAllowedView'],
			],
			'CATALOG_CAN_BUY' => [
				'NAME' => null,
				'ALIAS' => 'CATALOG_CAN_BUY_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_CHAR,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
				'SELECT_EXPRESSION' => [__CLASS__, 'selectPriceTypeAllowedBuy'],
			],
			'CURRENCY_SCALE' => [
				'PHANTOM' => true,
				'NAME' => null,
				'ALIAS' => 'CATALOG_CURRENCY_SCALE_#ENTITY_ID#',
				'TYPE' => null,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'FILTER_MODIFY_EXPRESSION' => [__CLASS__, 'filterModifierCurrencyScale'],
				'NEW_ID' => 'CURRENCY_FOR_SCALE',
			],
			'SHOP_QUANTITY' => [
				'PHANTOM' => true,
				'NAME' => null,
				'ALIAS' => 'CATALOG_SHOP_QUANTITY_#ENTITY_ID#',
				'TYPE' => null,
				'ALLOWED' => self::FIELD_ALLOWED_FILTER,
				'JOIN_MODIFY_EXPRESSION' => [__CLASS__, 'priceParametersFilter'],
				'NEW_ID' => 'DEFAULT_PRICE_FILTER',
			],
		];
	}

	/**
	 * @return array[]
	 */
	private static function getOldStoreFields(): array
	{
		return [
			'STORE_AMOUNT' => [
				'NAME' => 'AMOUNT',
				'ALIAS' => 'CATALOG_STORE_AMOUNT_#ENTITY_ID#',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_ALL,
				'ORDER_NULLABLE' => true,
			],
		];
	}

	/**
	 * @return array[]
	 */
	private static function getVatFields(): array
	{
		return [
			'RATE' => [
				'NAME' => 'RATE',
				'ALIAS' => 'CATALOG_VAT',
				'TYPE' => self::FIELD_TYPE_FLOAT,
				'ALLOWED' => self::FIELD_ALLOWED_SELECT,
			],
		];
	}

	/**
	 * @param array $userField
	 * @return bool
	 */
	public static function isValidProductUserField(array $userField): bool
	{
		if ($userField['USER_TYPE_ID'] === Main\UserField\Types\FileType::USER_TYPE_ID)
		{
			return false;
		}

		return true;
	}

	/**
	 * @param string $entity
	 * @param string $field
	 * @return int
	 */
	private static function getFieldAllowed(string $entity, string $field): int
	{
		if (!isset(self::$entityFields[$entity][$field]))
			return 0;
		if (!isset(self::$entityFields[$entity][$field]['ALLOWED']))
			return 0;
		return (self::$entityFields[$entity][$field]['ALLOWED']);
	}

	/**
	 * @param string $entity
	 * @return array|null
	 */
	private static function getFieldsAllowedToSelect(string $entity): ?array
	{
		$filter = function($field)
		{
			return (isset($field['ALLOWED']) && ($field['ALLOWED'] & self::FIELD_ALLOWED_SELECT > 0));
		};

		$result = array_filter(self::$entityFields[$entity], $filter);
		unset($filter);
		return (!empty($result) ? array_keys($result) : null);
	}

	/**
	 * @param string $field
	 * @return array|null
	 */
	private static function parseField(string $field): ?array
	{
		if ($field === '')
		{
			return null;
		}

		$field = strtoupper($field);

		$entity = '';
		$entityId = 0;
		$prepared = [];
		$allowed = 0;
		$compatible = false;
		$checked = false;

		if (preg_match(self::FIELD_PATTERN_OLD_STORE, $field, $prepared))
		{
			$compatible = true;
			$entity = self::ENTITY_OLD_STORE;
			$field = 'STORE_AMOUNT';
			$entityId = (int)$prepared[1];
			$checked = ($entityId > 0);
		}
		elseif (preg_match(self::FIELD_PATTERN_OLD_PRICE_ROW, $field, $prepared))
		{
			$compatible = true;
			$entity = self::ENTITY_OLD_PRICE;
			$field = 'ID';
			$entityId = (int)$prepared[1];
			$checked = ($entityId > 0);
		}
		elseif (preg_match(self::FIELD_PATTERN_OLD_PRICE, $field, $prepared))
		{
			$compatible = true;
			$entity = self::ENTITY_OLD_PRICE;
			$field = $prepared[1];
			$entityId = (int)$prepared[2];
			$checked = ($entityId > 0);
		}
		elseif (preg_match(self::FIELD_PATTERN_OLD_PRODUCT, $field, $prepared))
		{
			$compatible = true;
			$entity = self::ENTITY_OLD_PRODUCT;
			$field = $prepared[1];
			$checked = true;
		}
		elseif (preg_match(self::FIELD_PATTERN_PRODUCT_USER_FIELD, $field, $prepared))
		{
			$entity = self::ENTITY_PRODUCT_USER_FIELD;
			$field = $prepared[1];
			$checked = true;
		}
		elseif (preg_match(self::FIELD_PATTERN_SEPARATE_ENTITY, $field, $prepared))
		{
			$entity = self::searchFieldEntity($prepared[1], self::ENTITY_TYPE_SEPARATE);
			if (!empty($entity))
			{
				$field = $prepared[1];
				$entityId = (int)$prepared[2];
				$checked = ($entityId > 0);
			}
		}
		elseif (preg_match(self::FIELD_PATTERN_FLAT_ENTITY, $field, $prepared))
		{
			$entity = self::searchFieldEntity($prepared[1], self::ENTITY_TYPE_FLAT);
			if (!empty($entity))
			{
				$field = $prepared[1];
				$checked = true;
			}
		}

		if ($checked)
		{
			$allowed = self::getFieldAllowed($entity, $field);
			if (empty($allowed))
			{
				$checked = false;
			}
		}

		if (!$checked)
		{
			return null;
		}

		return [
			'ENTITY' => $entity,
			'FIELD' => $field,
			'ENTITY_ID' => $entityId,
			'ALLOWED' => $allowed,
			'COMPATIBLE' => $compatible
		];
	}

	/**
	 * @param string $field
	 * @param int $type
	 * @return null|string
	 */
	private static function searchFieldEntity(string $field, int $type): ?string
	{
		$result = null;

		switch ($type)
		{
			case self::ENTITY_TYPE_FLAT:
				if (isset(self::$entityFields[self::ENTITY_PRODUCT][$field]))
					$result = self::ENTITY_PRODUCT;
				elseif (isset(self::$entityFields[self::ENTITY_FLAT_PRICE][$field]))
					$result = self::ENTITY_FLAT_PRICE;
				elseif (isset(self::$entityFields[self::ENTITY_FLAT_WAREHNOUSE][$field]))
					$result = self::ENTITY_FLAT_WAREHNOUSE;
				elseif (isset(self::$entityFields[self::ENTITY_FLAT_BARCODE][$field]))
					$result = self::ENTITY_FLAT_BARCODE;
				break;
			case self::ENTITY_TYPE_SEPARATE:
				if (isset(self::$entityFields[self::ENTITY_WARENHOUSE][$field]))
					$result = self::ENTITY_WARENHOUSE;
				elseif (isset(self::$entityFields[self::ENTITY_PRICE][$field]))
					$result = self::ENTITY_PRICE;
				break;
		}

		return $result;
	}

	/**
	 * @param int $allowed
	 * @param int $action
	 * @return bool
	 */
	private static function checkAllowedAction(int $allowed, int $action): bool
	{
		return ($allowed & $action) > 0;
	}

	/**
	 * @param string $field
	 * @param array $entityList
	 * @return bool
	 */
	private static function isEntityFilterField(string $field, array $entityList): bool
	{
		if (is_numeric($field))
		{
			return false;
		}

		$result = false;

		self::initEntityDescription();
		self::initEntityFields();

		$filterItem = \CIBlock::MkOperationFilter($field);
		$prepareField = self::parseField($filterItem['FIELD']);
		if (
			self::checkPreparedField($prepareField)
			&& self::checkAllowedAction($prepareField['ALLOWED'], self::FIELD_ALLOWED_FILTER)
		)
		{
			if (isset($entityList[$prepareField['ENTITY']]))
				$result = true;
		}
		unset($prepareField, $filterItem);

		return $result;
	}

	/**
	 * @param array $field
	 * @return string
	 */
	private static function getEntityIndex(array $field): string
	{
		return $field['ENTITY'].':'.$field['ENTITY_ID'];
	}

	/**
	 * @param string $entity
	 * @return array|null
	 */
	private static function getEntityRow(string $entity): ?array
	{
		if (!isset(self::$entityDescription[$entity]))
		{
			return null;
		}

		return self::$entityDescription[$entity];
	}

	/**
	 * @param string $entity
	 * @return bool
	 */
	private static function isExternalEntity(string $entity): bool
	{
		return isset(self::$entityDescription[$entity]['EXTERNAL']);
	}

	/**
	 * Returns entity data for sql query.
	 *
	 * @param array $entity
	 * @return array|null
	 */
	private static function getEntityDescription(array $entity): ?array
	{
		$row = self::getEntityRow($entity['ENTITY']);
		if ($row === null)
		{
			return null;
		}
		if (self::isExternalEntity($entity['ENTITY']))
		{
			return $row;
		}

		$row['ALIAS'] = str_replace('#ENTITY_ID#', $entity['ENTITY_ID'], $row['ALIAS']);

		$joinTemplates = [
			'#NAME#' => $row['NAME'],
			'#ALIAS#' => $row['ALIAS'],
			'#ENTITY_ID#' => $entity['ENTITY_ID']
		];
		$additionalAliases = self::getOption('ALIASES');
		if (!empty($additionalAliases))
			$joinTemplates = $joinTemplates + $additionalAliases;
		unset($additionalAliases);
		$row['JOIN'] = str_replace(
			array_keys($joinTemplates),
			array_values($joinTemplates),
			$row['JOIN']
		);
		unset($joinTemplates);

		return $row;
	}

	/**
	 * @param array $field
	 * @return string
	 */
	private static function getFieldIndex(array $field): string
	{
		return $field['ENTITY'].':'.$field['ENTITY_ID'].':'.$field['FIELD'];
	}

	/**
	 * @param array $field
	 * @return bool
	 */
	private static function isPhantomField(array $field): bool
	{
		return isset($field['PHANTOM']);
	}

	/**
	 * @param string $entity
	 * @param string $field
	 * @return null|array
	 */
	private static function getFieldDescription(string $entity, string $field): ?array
	{
		if (empty(self::$entityFields[$entity][$field]))
			return null;
		return self::$entityFields[$entity][$field];
	}

	/**
	 * @param array $queryItem
	 * @param array $options
	 * @return null|array
	 */
	private static function getField(array $queryItem, array $options): ?array
	{
		$whiteList = [
			'ALIAS' => true,
			'SELECT' => true,
			'FILTER' => true,
			'ORDER' => true,
			'ENTITY_DESCRIPTION' => true
		];

		$field = self::getFieldDescription($queryItem['ENTITY'], $queryItem['FIELD']);
		if (empty($field))
		{
			return null;
		}

		$entity = self::getEntityDescription($queryItem);
		if (empty($entity))
		{
			return null;
		}

		$fantomField = self::isPhantomField($field);

		if ($field['ALIAS'] !== null)
		{
			$field['ALIAS'] = str_replace('#ENTITY_ID#', $queryItem['ENTITY_ID'], $field['ALIAS']);
		}
		if (!$fantomField)
		{
			$field['FULL_NAME'] = $entity['ALIAS'].'.'.$field['NAME'];
		}

		if (
			self::checkAllowedAction($field['ALLOWED'], self::FIELD_ALLOWED_SELECT)
			&& isset($options['select'])
		)
		{
			if (!$fantomField)
			{
				$field['SELECT'] = '#FULL_NAME#';
				if (isset($field['SELECT_EXPRESSION']) && is_callable($field['SELECT_EXPRESSION']))
				{
					call_user_func_array(
						$field['SELECT_EXPRESSION'],
						[&$queryItem, &$entity, &$field]
					);
				}
				$field['SELECT'] = str_replace('#FULL_NAME#', $field['FULL_NAME'], $field['SELECT']);
			}
		}

		if (
			self::checkAllowedAction($field['ALLOWED'], self::FIELD_ALLOWED_FILTER)
			&& isset($options['filter'])
		)
		{
			if (isset($field['FILTER_PREPARE_VALUE_EXPRESSION']) && is_callable($field['FILTER_PREPARE_VALUE_EXPRESSION']))
			{
				call_user_func_array(
					$field['FILTER_PREPARE_VALUE_EXPRESSION'],
					[&$queryItem, &$entity, &$field]
				);
			}

			if (!$fantomField)
			{
				$field['FILTER'] = \CIBlock::FilterCreate(
					'#FULL_NAME#',
					$queryItem['VALUES'],
					self::getFilterType($field['TYPE']),
					$queryItem['OPERATION']
				);
				$field['FILTER'] = str_replace('#FULL_NAME#', $field['FULL_NAME'], $field['FILTER']);
			}
		}

		if (
			self::checkAllowedAction($field['ALLOWED'], self::FIELD_ALLOWED_ORDER)
			&& isset($options['order'])
		)
		{
			if (!$fantomField)
			{
				$field['ORDER'] = \CIBlock::_Order(
					'#FULL_NAME#',
					$queryItem['ORDER'],
					$field['ORDER_DEFAULT'] ?? 'ASC',
					isset($field['ORDER_NULLABLE'])
				);
				$field['ORDER'] = str_replace('#FULL_NAME#', $field['FULL_NAME'], $field['ORDER']);
			}
		}

		if (isset($field['JOIN_MODIFY_EXPRESSION']) && is_callable($field['JOIN_MODIFY_EXPRESSION']))
		{
			call_user_func_array(
				$field['JOIN_MODIFY_EXPRESSION'],
				[&$queryItem, &$entity, &$field]
			);
		}
		if (isset($field['JOIN_MODIFY']))
		{
			$field['JOIN_MODIFY'] = str_replace('#TABLE#', $entity['ALIAS'], $field['JOIN_MODIFY']);
			$entity['JOIN_MODIFY'] = $field['JOIN_MODIFY'];
		}

		unset($fantomField);

		$field['ENTITY_DESCRIPTION'] = $entity;
		unset($entity);

		$result = array_intersect_key($field, $whiteList);
		unset($field, $whiteList);

		return $result;
	}

	/**
	 * @param string $fieldType
	 * @return string
	 */
	private static function getFilterType(string $fieldType): string
	{
		return match ($fieldType)
		{
			self::FIELD_TYPE_INT, self::FIELD_TYPE_FLOAT => 'number',
			self::FIELD_TYPE_CHAR => 'string_equal',
			default => 'string',
		};
	}

	/**
	 * @param array|null $field
	 * @return bool
	 */
	private static function checkPreparedField(?array $field): bool
	{
		if (empty($field))
		{
			return false;
		}
		if ($field['ENTITY_ID'] === 0)
		{
			if (
				$field['ENTITY'] != self::ENTITY_PRODUCT
				&& $field['ENTITY'] != self::ENTITY_PRODUCT_USER_FIELD
				&& $field['ENTITY'] != self::ENTITY_FLAT_PRICE
				&& $field['ENTITY'] != self::ENTITY_FLAT_WAREHNOUSE
				&& $field['ENTITY'] != self::ENTITY_FLAT_BARCODE
				&& $field['ENTITY'] != self::ENTITY_OLD_PRODUCT
			)
			{
				return false;
			}
		}

		return true;
	}

	/**
	 * @param array $field
	 * @return array
	 */
	private static function getFieldSignature(array $field): array
	{
		return [
			'ENTITY' => $field['ENTITY'],
			'FIELD' => $field['FIELD'],
			'ENTITY_ID' => $field['ENTITY_ID']
		];
	}

	/**
	 * @param array &$parameters
	 * @return void
	 */
	private static function prepareSelectedCompatibleFields(array &$parameters): void
	{
		if ($parameters['compatible_mode'] && !empty($parameters['compatible_entities']))
		{
			foreach ($parameters['compatible_entities'] as $entity)
			{
				$list = self::getFieldsAllowedToSelect($entity['ENTITY']);
				if ($list === null)
					continue;
				foreach ($list as $fieldId)
				{
					$field = [
						'ENTITY' => $entity['ENTITY'],
						'FIELD' => $fieldId,
						'ENTITY_ID' => $entity['ENTITY_ID']
					];
					$parameters['select'][self::getFieldIndex($field)] = $field;
				}
				unset($field, $fieldId, $list);
			}
			unset($entity);
		}
		unset($parameters['compatible_mode'], $parameters['compatible_entities']);
	}

	/**
	 * @param array &$result
	 * @param array $field
	 * @return void
	 */
	private static function fillCompatibleEntities(array &$result, array $field): void
	{
		if (!$field['COMPATIBLE'])
			return;
		$result['compatible_mode'] = true;
		$result['compatible_entities'][self::getEntityIndex($field)] = [
			'ENTITY' => $field['ENTITY'],
			'ENTITY_ID' => $field['ENTITY_ID']
		];
		// if compatible mode enabled - product always exists.
		$entity = [
			'ENTITY' => self::ENTITY_OLD_PRODUCT,
			'ENTITY_ID' => 0
		];
		$result['compatible_entities'][self::getEntityIndex($entity)] = $entity;
		$entity = [
			'ENTITY' => self::ENTITY_VAT,
			'ENTITY_ID' => 0
		];
		$result['compatible_entities'][self::getEntityIndex($entity)] = $entity;
		unset($entity);

	}

	/**
	 * @param array $parameters
	 * @param array $options
	 * @return array|null
	 */
	private static function prepareQuery(array $parameters, array $options): ?array
	{
		self::initEntityDescription();
		self::initEntityFields();

		self::setOptions($options);

		$result = [
			'compatible_mode' => false,
			'compatible_entities' => [],
			'select' => [],
			'filter' => [],
			'order' => [],
		];

		if (!empty($parameters['select']) && is_array($parameters['select']))
		{
			foreach ($parameters['select'] as $field)
			{
				$prepareField = self::parseField($field);
				if (!self::checkPreparedField($prepareField))
				{
					continue;
				}
				if (!self::checkAllowedAction($prepareField['ALLOWED'], self::FIELD_ALLOWED_SELECT))
				{
					continue;
				}

				self::fillCompatibleEntities($result, $prepareField);
				$result['select'][self::getFieldIndex($prepareField)] = self::getFieldSignature($prepareField);
			}
			unset($prepareField, $field);
		}

		if (!empty($parameters['filter']) && is_array($parameters['filter']))
		{
			foreach (array_keys($parameters['filter']) as $key)
			{
				$filter = \CIBlock::MkOperationFilter($key);
				$prepareField = self::parseField($filter['FIELD']);

				if (!self::checkPreparedField($prepareField))
				{
					continue;
				}
				if (!self::checkAllowedAction($prepareField['ALLOWED'], self::FIELD_ALLOWED_FILTER))
				{
					continue;
				}

				self::fillCompatibleEntities($result, $prepareField);
				$prepareField = self::getFieldSignature($prepareField);
				$prepareField['OPERATION'] = $filter['OPERATION'];
				$prepareField['PREFIX'] = $filter['PREFIX'];
				$prepareField['VALUES'] = $parameters['filter'][$key];
				$result['filter'][] = $prepareField;
			}
			unset($prepareField, $filter, $key);
		}

		if (!empty($parameters['order']) && is_array($parameters['order']))
		{
			foreach ($parameters['order'] as $index => $value)
			{
				if (empty($value) || !is_array($value))
				{
					continue;
				}

				$order = reset($value);
				$field = key($value);

				$prepareField = self::parseField($field);
				if (!self::checkPreparedField($prepareField))
				{
					continue;
				}
				if (!self::checkAllowedAction($prepareField['ALLOWED'], self::FIELD_ALLOWED_ORDER))
				{
					continue;
				}

				self::orderTransformField($prepareField);

				self::fillCompatibleEntities($result, $prepareField);
				$fieldIndex = self::getFieldIndex($prepareField);

				$prepareField = self::getFieldSignature($prepareField);
				$result['select'][$fieldIndex] = $prepareField;

				$prepareField['INDEX'] = $index;
				$prepareField['ORDER'] = $order;
				$result['order'][$fieldIndex] = $prepareField;
			}
			unset($order, $field, $index, $value);
		}

		self::prepareSelectedCompatibleFields($result);

		$result = self::build($result);
		self::clearOptions();
		return $result;
	}

	/**
	 * @return void
	 */
	private static function clearOptions(): void
	{
		self::$options = [];
	}

	/**
	 * @param array $options
	 * @return void
	 */
	private static function setOptions(array $options): void
	{
		global $USER;

		if (!isset($options['ALIASES']))
		{
			$options['ALIASES'] = [];
		}
		if (!isset($options['ALIASES']['#ELEMENT#']))
		{
			$options['ALIASES']['#ELEMENT#'] = 'BE';
		}
		if (!isset($options['ALIASES']['#ELEMENT_JOIN#']))
		{
			$options['ALIASES']['#ELEMENT_JOIN#'] = 'BE.ID';
		}

		if (!isset($options['USER']))
		{
			$options['USER'] = [];
		}
		if (!isset($options['USER']['ID']))
		{
			$options['USER']['ID'] = (\CCatalog::IsUserExists() ? $USER->GetID() : 0);
		}
		$options['USER']['ID'] = (int)$options['USER']['ID'];

		self::$options = $options;
	}

	/**
	 * @param string $index
	 * @return mixed|null
	 */
	private static function getOption(string $index): mixed
	{
		if (!isset(self::$options[$index]))
			return null;
		return self::$options[$index];
	}

	/**
	 * @param array $parameters
	 * @return array|null
	 */
	private static function build(array $parameters): ?array
	{
		$founded = false;
		$result = [
			'select' => [],
			'filter' => [],
			'order' => [],
			'join' => [],
			'join_modify' => [],
		];

		if (!empty($parameters['select']))
		{
			self::buildSelect($result, $parameters['select']);
			if (!empty($result['select']))
			{
				$founded = true;
			}
		}
		if (!empty($parameters['filter']))
		{
			self::buildFilter($result, $parameters['filter']);
			if (!empty($result['filter']) || !empty($result['join']))
			{
				$founded = true;
			}
		}
		if (!empty($parameters['order']))
		{
			self::buildOrder($result, $parameters['order']);
			if (!empty($result['order']))
			{
				$founded = true;
			}
		}

		if (!$founded)
		{
			return null;
		}

		self::buildJoin($result);

		$result['join'] = array_values($result['join']);
		unset($result['join_modify']);

		return $result;
	}

	/**
	 * @param array &$result
	 * @param array $list
	 * @return void
	 */
	private static function buildSelect(array &$result, array $list): void
	{
		foreach ($list as $item)
		{
			$field = self::getField($item, ['select' => true]);
			if (empty($field))
				continue;

			if (isset($field['SELECT']))
				$result['select'][] = $field['SELECT'].' as '.$field['ALIAS'];

			$item['ENTITY_DESCRIPTION'] = $field['ENTITY_DESCRIPTION'];
			self::addJoin($result, $item);
		}
		unset($item);
	}

	/**
	 * @param array &$result
	 * @param array $list
	 * @return void
	 */
	private static function buildFilter(array &$result, array $list): void
	{
		self::filterModify($list);

		$packedFilter = self::getPackedFilter($list);

		self::buildInternalFilter($result, $packedFilter['INTERNAL']);
		self::buildExternalFilter($result, $packedFilter['EXTERNAL']);
	}

	/**
	 * @param array &$result
	 * @param array $list
	 * @return void
	 */
	private static function buildOrder(array &$result, array $list): void
	{
		foreach ($list as $item)
		{
			$field = self::getField($item, ['order' => true]);
			if (empty($field))
				continue;

			if (isset($field['ORDER']))
				$result['order'][$item['INDEX']] = $field['ORDER'];

			$item['ENTITY_DESCRIPTION'] = $field['ENTITY_DESCRIPTION'];
			self::addJoin($result, $item);
		}
		unset($field, $item);
	}

	/**
	 * @param array &$result
	 * @return void
	 */
	private static function buildJoin(array &$result): void
	{
		foreach (array_keys($result['join']) as $index)
		{
			$modifier = (isset($result['join_modify'][$index])
				? ' '.implode(' ', $result['join_modify'][$index])
				: ''
			);
			$result['join'][$index] = str_replace('#JOIN_MODIFY#', $modifier, $result['join'][$index]);
		}
		unset($modifier, $index);
	}

	/**
	 * @param array &$list
	 * @return void
	 */
	private static function filterModify(array &$list): void
	{
		foreach (array_keys($list) as $index)
		{
			$item = $list[$index];
			$field = self::getFieldDescription($item['ENTITY'], $item['FIELD']);
			if (empty($field))
			{
				continue;
			}

			$entity = self::getEntityDescription($item);
			if (empty($entity))
			{
				continue;
			}

			if (isset($field['FILTER_MODIFY_EXPRESSION']) && is_callable($field['FILTER_MODIFY_EXPRESSION']))
			{
				call_user_func_array(
					$field['FILTER_MODIFY_EXPRESSION'],
					[&$list, $index, $entity, $field]
				);
			}
		}
		unset($field, $entity, $item, $index);
	}

	private static function getPackedFilter(array $filter): array
	{
		$result = [
			'INTERNAL' => [],
			'EXTERNAL' => [],
		];

		foreach ($filter as $item)
		{
			$entity = $item['ENTITY'];
			if (self::isExternalEntity($entity))
			{
				if (!isset($result['EXTERNAL'][$entity]))
				{
					$result['EXTERNAL'][$entity] = [];
				}
				$index = self::getEntityIndex($item);
				if (!isset($result['EXTERNAL'][$entity][$index]))
				{
					$result['EXTERNAL'][$entity][$index] = [];
				}
				$result['EXTERNAL'][$entity][$index][] = $item;
			}
			else
			{
				$result['INTERNAL'][] = $item;
			}
		}

		return $result;
	}

	/**
	 * @param array &$result
	 * @param array $list
	 */
	private static function buildInternalFilter(array &$result, array $list): void
	{
		foreach ($list as $item)
		{
			$field = self::getField($item, ['filter' => true]);
			if (empty($field))
			{
				continue;
			}

			if (isset($field['FILTER']))
			{
				$result['filter'][] = $field['FILTER'];
			}

			$item['ENTITY_DESCRIPTION'] = $field['ENTITY_DESCRIPTION'];
			self::addJoin($result, $item);
		}
		unset($item);
	}

	/**
	 * @param array &$result
	 * @param array $entity
	 * @return void
	 */
	private static function addJoin(array &$result, array $entity): void
	{
		$index = self::getEntityIndex($entity);
		$description = $entity['ENTITY_DESCRIPTION'];

		if (!isset($result['join'][$index]))
		{
			if (!empty($description['RELATION']))
			{
				foreach ($description['RELATION'] as $item)
				{
					if (!is_array($item))
					{
						$item = [
							'ENTITY' => $item,
							'ENTITY_ID' => 0
						];
					}
					$ownerIndex = self::getEntityIndex($item);
					if (isset($result['join'][$ownerIndex]))
						continue;
					$owner = self::getEntityDescription($item);
					if (empty($owner))
						continue;
					$result['join'][$ownerIndex] = $owner['JOIN'];
				}
				unset($owner, $ownerIndex, $parent, $item);
			}
			$result['join'][$index] = $description['JOIN'];
		}
		if (!empty($description['JOIN_MODIFY']))
		{
			if (!isset($result['join_modify'][$index]))
				$result['join_modify'][$index] = [];
			$result['join_modify'][$index][] = $description['JOIN_MODIFY'];
		}
		unset($description, $index);
	}

	/**
	 * @param array $result
	 * @param array $filter
	 */
	private static function buildExternalFilter(array &$result, array $filter): void
	{
		foreach (array_keys($filter) as $entity)
		{
			$row = self::getEntityRow($entity);
			if (isset($row['HANDLER']) && is_callable($row['HANDLER']))
			{
				call_user_func_array(
					$row['HANDLER'],
					[
						&$result,
						$row,
						[
							'filter' => $filter[$entity],
						]
					]
				);
			}
		}
	}

	/**
	 * @param array &$item
	 * @return void
	 */
	private static function orderTransformField(array &$item): void
	{
		$field = self::getFieldDescription($item['ENTITY'], $item['FIELD']);
		if (empty($field))
			return;
		if (isset($field['ORDER_TRANSFORM']))
			$item['FIELD'] = $field['ORDER_TRANSFORM'];
		unset($field);
	}

	/**
	 * Returns sql code for select QUANTITY_TRACE with converted default value.
	 *
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectQuantityTrace(array &$parameters, array &$entity, array &$field): void
	{
		$field['SELECT'] = self::getReplaceSqlFunction(Main\Config\Option::get('catalog', 'default_quantity_trace'));
	}

	/**
	 * Returns sql code for select CAN_BUY_ZERO with converted default value.
	 *
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectCanBuyZero(array &$parameters, array &$entity, array &$field): void
	{
		$field['SELECT'] = self::getReplaceSqlFunction(Main\Config\Option::get('catalog', 'default_can_buy_zero'));
	}

	/**
	 * Returns sql code for select NEGATIVE_AMOUNT_TRACE with converted default value.
	 *
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectNegativeAmountTrace(array &$parameters, array &$entity, array &$field): void
	{
		$field['SELECT'] = self::getReplaceSqlFunction(Main\Config\Option::get('catalog', 'allow_negative_amount'));
	}

	/**
	 * Returns sql code for select SUBSCRIBE with converted default value.
	 *
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectSubscribe(array &$parameters, array &$entity, array &$field): void
	{
		$field['SELECT'] = self::getReplaceSqlFunction(Main\Config\Option::get('catalog', 'default_subscribe'));
	}

	/**
	 * Returns sql code for select field with default value.
	 *
	 * @param string $defaultValue
	 * @return string
	 */
	private static function getReplaceSqlFunction(string $defaultValue): string
	{
		return 'CASE WHEN #FULL_NAME# = \'' . ProductTable::STATUS_DEFAULT . '\' THEN \'' . $defaultValue . '\' ELSE #FULL_NAME# END';
	}

	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectPriceTypeName(array &$parameters, array &$entity, array &$field): void
	{
		$result = '';
		$id = $parameters['ENTITY_ID'];
		$fullPriceTypeList = GroupTable::getTypeList();
		if (!empty($fullPriceTypeList[$id]))
		{
			$result = $fullPriceTypeList[$id]['NAME_LANG'] ?? $fullPriceTypeList[$id]['NAME'];
			$connection = Main\Application::getInstance()->getConnection();
			$sqlHelper = $connection->getSqlHelper();
			$result = $sqlHelper->forSql($result);
			unset($sqlHelper, $connection);
		}
		unset($fullPriceTypeList, $id);
		$field['SELECT'] = '\''.$result.'\'';
		unset($result);
	}

	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectPriceTypeAllowedView(array &$parameters, array &$entity, array &$field): void
	{
		$parameters['ACCESS'] = GroupAccessTable::ACCESS_VIEW;
		$field['SELECT'] = self::getPriceTypeAccess($parameters);
	}

	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectPriceTypeAllowedBuy(array &$parameters, array &$entity, array &$field): void
	{
		$parameters['ACCESS'] = GroupAccessTable::ACCESS_BUY;
		$field['SELECT'] = self::getPriceTypeAccess($parameters);
	}

	/**
	 * @param array $parameters
	 * @return string
	 */
	private static function getPriceTypeAccess(array $parameters): string
	{
		$result = 'N';

		$user = self::getOption('USER');
		if (!empty($user))
		{
			if (empty($user['GROUPS']) || !is_array($user['GROUPS']))
				$user['GROUPS'] = self::getUserGroups($user['ID']);
			$iterator = GroupAccessTable::getList([
				'select' => ['ID'],
				'filter' => [
					'=CATALOG_GROUP_ID' => $parameters['ENTITY_ID'],
					'@GROUP_ID' => $user['GROUPS'],
					'=ACCESS' => $parameters['ACCESS']
				],
				'limit' => 1
			]);
			$row = $iterator->fetch();
			if (!empty($row))
				$result = 'Y';
			unset($row, $iterator);
		}
		unset($user);

		return '\''.$result.'\'';
	}

	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function prepareFilterQuantityTrace(array &$parameters, array &$entity, array &$field): void
	{
		$parameters['VALUES'] = self::addDefaultValue(
			$parameters['VALUES'],
			Main\Config\Option::get('catalog', 'default_quantity_trace')
		);
	}

	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function prepareFilterCanBuyZero(array &$parameters, array &$entity, array &$field): void
	{
		$parameters['VALUES'] = self::addDefaultValue(
			$parameters['VALUES'],
			Main\Config\Option::get('catalog', 'default_can_buy_zero')
		);
	}

	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function prepareFilterSubscribe(array &$parameters, array &$entity, array &$field): void
	{
		$parameters['VALUES'] = self::addDefaultValue(
			$parameters['VALUES'],
			Main\Config\Option::get('catalog', 'default_subscribe')
		);
	}

	/**
	 * Returns data for filtering with default values.
	 *
	 * @param mixed $values
	 * @param string $defaultValue
	 * @return mixed
	 */
	private static function addDefaultValue(mixed $values, string $defaultValue): mixed
	{
		if (!is_array($values))
		{
			if ($values === $defaultValue)
			{
				$values = [$values, ProductTable::STATUS_DEFAULT];
			}
		}
		else
		{
			if (in_array($defaultValue, $values))
			{
				$values[] = ProductTable::STATUS_DEFAULT;
				$values = array_unique($values);
			}
		}
		return $values;
	}

	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function priceParametersFilter(array &$parameters, array &$entity, array &$field): void
	{
		if (empty($parameters['VALUES']))
			return;
		if (!is_string($parameters['VALUES']) && !is_int($parameters['VALUES']))
			return;
		$value = (int)$parameters['VALUES'];
		if ($value <= 0)
			return;

		$field['JOIN_MODIFY'] = ' and '.($parameters['OPERATION'] == 'N' ? 'not' : '').
			' ((#TABLE#.QUANTITY_FROM <= '.$value.' or #TABLE#.QUANTITY_FROM IS NULL)'.
			' and (#TABLE#.QUANTITY_TO >= '.$value.' or #TABLE#.QUANTITY_TO IS NULL))';
	}

	/**
	 * @param array &$filter
	 * @param int|string $filterKey
	 * @param array $entity
	 * @param array $field
	 * @return void
	 */
	private static function filterModifierCurrencyScale(array &$filter, int|string $filterKey, array $entity, array $field): void
	{
		$activeItem = $filter[$filterKey];

		if ($activeItem['FIELD'] !== 'CURRENCY_FOR_SCALE' && $activeItem['FIELD'] !== 'CURRENCY_SCALE')
		{
			return;
		}
		if ($activeItem['OPERATION'] !== 'E' && $activeItem['OPERATION'] !== 'I')
		{
			return;
		}

		$value = $activeItem['VALUES'];
		if (!is_string($value))
		{
			return;
		}

		$currencyId = Currency\CurrencyManager::checkCurrencyID($value);
		if ($currencyId === false)
		{
			return;
		}

		$currency = \CCurrency::GetByID($currencyId);
		if (empty($currency))
		{
			return;
		}
		$currency['CURRENT_BASE_RATE'] = (float)$currency['CURRENT_BASE_RATE'];
		if ($currency['CURRENT_BASE_RATE'] <= 0)
		{
			return;
		}

		foreach (array_keys($filter) as $index)
		{
			if ($index == $filterKey)
			{
				continue;
			}
			$filterItem = $filter[$index];
			if (
				$filterItem['ENTITY'] != $activeItem['ENTITY']
				|| $filterItem['ENTITY_ID'] != $activeItem['ENTITY_ID']
			)
			{
				continue;
			}
			if ($filterItem['FIELD'] != 'PRICE')
			{
				continue;
			}
			if (is_array($filter[$index]['VALUES']))
			{
				$newPrices = [];
				foreach ($filter[$index]['VALUES'] as $oldPrice)
				{
					$newPrices[] = (float)$oldPrice * $currency['CURRENT_BASE_RATE'];
				}
				$filter[$index]['VALUES'] = $newPrices;
				unset($oldPrice, $newPrices);
			}
			else
			{
				$filter[$index]['VALUES'] = (float)$filter[$index]['VALUES']*$currency['CURRENT_BASE_RATE'];
			}
			$filter[$index]['FIELD'] = $activeItem['FIELD'] === 'CURRENCY_FOR_SCALE'
				? 'SCALED_PRICE'
				: 'PRICE_SCALE'
			;
		}
		unset($index);
	}

	/**
	 * @param int $userId
	 * @return array
	 */
	private static function getUserGroups(int $userId): array
	{
		return Main\UserTable::getUserGroupIds($userId);
	}

	/**
	 * @param array &$result
	 * @param array $entity
	 * @param array $data
	 */
	private static function handleProductUserFields(array &$result, array $entity, array $data): void
	{
		if (empty($data['filter']))
		{
			return;
		}
		$aliases = self::getOption('ALIASES');
		if (empty($aliases['#ELEMENT_JOIN#']))
		{
			return;
		}

		$userFieldManager = new CUserTypeSQL;

		foreach ($data['filter'] as $entityIndex => $rows)
		{
			$userFieldManager->SetEntity(ProductTable::getUfId(), $aliases['#ELEMENT_JOIN#']);
			$userFieldManager->SetSelect([]);
			$rawFilter = [];
			foreach ($rows as $row)
			{
				$rawFilter[$row['PREFIX'].$row['FIELD']] = $row['VALUES'];
			}
			$userFieldManager->SetFilter($rawFilter);
			$userFieldManager->SetOrder([]);

			$filter = $userFieldManager->GetFilter();
			if (!empty($filter))
			{
				$result['filter'][] = $filter;
				$join = $userFieldManager->GetJoin($aliases['#ELEMENT_JOIN#']);
				if (!empty($join))
				{
					$result['join'][$entityIndex] = $join;
				}
			}
		}

		unset($userFieldManager);
	}
}
