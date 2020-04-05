<?php
use Bitrix\Main,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog\GroupAccessTable,
	Bitrix\Catalog\ProductTable,
	Bitrix\Currency;

Loc::loadMessages(__FILE__);

class CProductQueryBuilder
{
	const ENTITY_PRODUCT = 'PRODUCT';
	const ENTITY_PRICE = 'PRICE';
	const ENTITY_WARENHOUSE = 'WARENHOUSE';
	const ENTITY_FLAT_PRICE = 'FLAT_PRICES';
	const ENTITY_FLAT_WAREHNOUSE = 'FLAT_WARENHOUSES';
	const ENTITY_OLD_PRODUCT = 'OLD_PRODUCT';
	const ENTITY_OLD_PRICE = 'OLD_PRICE';
	const ENTITY_OLD_STORE = 'OLD_STORE';
	const ENTITY_CATALOG_IBLOCK = 'CATALOG_IBLOCK';
	const ENTITY_VAT = 'VAT';

	const FIELD_ALLOWED_SELECT = 0x0001;
	const FIELD_ALLOWED_FILTER = 0x0002;
	const FIELD_ALLOWED_ORDER = 0x0004;
	const FIELD_ALLOWED_ALL = self::FIELD_ALLOWED_SELECT|self::FIELD_ALLOWED_FILTER|self::FIELD_ALLOWED_ORDER;

	const FIELD_PATTERN_OLD_STORE = '/^CATALOG_STORE_AMOUNT_([0-9]+)$/';
	const FIELD_PATTERN_OLD_PRICE_ROW = '/^CATALOG_GROUP_([0-9]+)$/';
	const FIELD_PATTERN_OLD_PRICE = '/^CATALOG_([A-Z][A-Z_]+)+_([0-9]+)$/';
	const FIELD_PATTERN_OLD_PRODUCT = '/^CATALOG_([A-Z][A-Z_]+)$/';
	const FIELD_PATTERN_FLAT_ENTITY = '/^([A-Z][A-Z_]+)$/';
	const FIELD_PATTERN_SEPARATE_ENTITY = '/^([A-Z][A-Z_]+)_([1-9][0-9]*)$/';

	const ENTITY_TYPE_FLAT = 0x0001;
	const ENTITY_TYPE_SEPARATE = 0x0002;

	private static $entityDescription = [];

	private static $entityFields = [];

	private static $options = [];

	/**
	 * @param array $filter
	 * @param array $options
	 * @return null|array
	 */
	public static function makeFilter(array $filter, array $options = [])
	{
		$query = self::prepareQuery(['filter' => $filter], $options);
		if ($query === null)
			return null;
		if (empty($query['filter']) && empty($query['join']))
			return null;

		return $query;
	}

	/**
	 * @param array $parameters
	 * @param array $options
	 * @return array|null
	 */
	public static function makeQuery(array $parameters, array $options = [])
	{
		$query = self::prepareQuery($parameters, $options);
		if ($query === null)
			return null;
		if (empty($query['select']) && empty($query['filter']) && empty($query['order']))
			return null;

		return $query;
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public static function isValidField($field)
	{
		$field = strtoupper($field);
		if ($field === '')
			return false;

		self::initEntityDescription();
		self::initEntityFields();

		$prepared = [];

		if (preg_match(self::FIELD_PATTERN_OLD_STORE, $field, $prepared))
			return true;
		if (preg_match(self::FIELD_PATTERN_OLD_PRICE_ROW, $field, $prepared))
			return true;
		if (preg_match(self::FIELD_PATTERN_OLD_PRICE, $field, $prepared))
			return true;
		if (preg_match(self::FIELD_PATTERN_OLD_PRODUCT, $field, $prepared))
			return true;
		if (preg_match(self::FIELD_PATTERN_SEPARATE_ENTITY, $field, $prepared))
		{
			if (self::searchFieldEntity($prepared[1], self::ENTITY_TYPE_SEPARATE))
				return true;
		}
		if (preg_match(self::FIELD_PATTERN_FLAT_ENTITY, $field, $prepared))
		{
			if (self::searchFieldEntity($prepared[1], self::ENTITY_TYPE_FLAT))
				return true;
		}

		return false;
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public static function isRealFilterField($field)
	{
		self::initEntityDescription();
		self::initEntityFields();

		$prepareField = self::parseField($field);
		if (!self::checkPreparedField($prepareField))
			return false;
		if (!self::checkAllowedAction($prepareField['ALLOWED'], self::FIELD_ALLOWED_FILTER))
			return false;

		$description = self::getFieldDescription($prepareField['ENTITY'], $prepareField['FIELD']);
		if (empty($description))
			return false;

		if (isset($description['PHANTOM']))
			return false;

		return true;
	}

	/**
	 * @param string $field
	 * @return bool
	 */
	public static function isPriceFilterField($field)
	{
		$result = false;

		if (!is_string($field))
			return $result;
		if (is_numeric($field))
			return $result;

		self::initEntityDescription();
		self::initEntityFields();

		$filterItem = \CIBlock::MkOperationFilter($field);
		$prepareField = self::parseField($filterItem['FIELD']);
		if (self::checkPreparedField($prepareField))
		{
			if (
				$prepareField['ENTITY'] == self::ENTITY_OLD_PRICE
				|| $prepareField['ENTITY'] == self::ENTITY_PRICE
				|| $prepareField['ENTITY'] == self::ENTITY_FLAT_PRICE
			)
				$result = true;
		}
		unset($prepareField, $filterItem);

		return $result;
	}

	/**
	 * @param array $filter
	 * @param array $order
	 * @param array $options
	 * @return array
	 */
	public static function modifyFilterFromOrder(array $filter, array $order, array $options)
	{
		$result = $filter;

		self::initEntityDescription();
		self::initEntityFields();

		if (empty($order) || empty($options['QUANTITY']))
			return $result;

		foreach (array_keys($order) as $field)
		{
			$prepareField = self::parseField($field);
			if (!self::checkPreparedField($prepareField))
				continue;
			switch ($prepareField['ENTITY'])
			{
				case self::ENTITY_OLD_PRICE:
					if (
						$prepareField['FIELD'] == 'PRICE'
						|| $prepareField['FIELD'] == 'PRICE_SCALE'
						|| $prepareField['FIELD'] == 'CURRENCY'
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
								$result[$filterField] = $options['QUANTITY'];
						}
						unset($filterField, $filterFieldDescription);
					}
					break;
				case self::ENTITY_PRICE:
					if (
						$prepareField['FIELD'] == 'PRICE'
						|| $prepareField['FIELD'] == 'SCALED_PRICE'
						|| $prepareField['FIELD'] == 'CURRENCY'
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
								$result[$filterField] = $options['QUANTITY'];
						}
						unset($filterField, $filterFieldDescription);
					}
					break;
				case self::ENTITY_FLAT_PRICE:
					if (
						$prepareField['FIELD'] == 'PRICE'
						|| $prepareField['FIELD'] == 'SCALED_PRICE'
						|| $prepareField['FIELD'] == 'CURRENCY'
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
								$result[$filterField] = $options['QUANTITY'];
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
	public static function convertOldField($field, $useMode)
	{
		$result = null;

		self::initEntityDescription();
		self::initEntityFields();

		$prepareField = self::parseField($field);
		if (!self::checkPreparedField($prepareField))
			return $result;
		if (!self::checkAllowedAction($prepareField['ALLOWED'], $useMode))
			return $result;

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
			return $result;

		$description = self::getFieldDescription($prepareField['ENTITY'], $prepareField['FIELD']);
		if (empty($description))
			return $result;

		if ($useMode == self::FIELD_ALLOWED_ORDER)
		{
			if (isset($description['ORDER_TRANSFORM']))
			{
				$description = self::getFieldDescription($prepareField['ENTITY'], $description['ORDER_TRANSFORM']);
				if (empty($description))
					return $result;
			}
		}

		$newField = [
			'ENTITY' => $newEntity,
			'ENTITY_ID' => $prepareField['ENTITY_ID'],
			'FIELD' => (isset($description['NEW_ID']) ? $description['NEW_ID'] : $prepareField['FIELD'])
		];
		unset($newEntity, $prepareField);

		$description = self::getFieldDescription($newField['ENTITY'], $newField['FIELD']);
		if (empty($description))
			return $result;

		$result = str_replace('#ENTITY_ID#', $newField['ENTITY_ID'], $description['ALIAS']);
		unset($description, $newField);

		return $result;
	}

	/**
	 * @param array $select
	 * @return array
	 */
	public static function convertOldSelect(array $select)
	{
		$result = [];

		if (empty($select))
			return $result;

		foreach ($select as $index => $field)
		{
			$newField = self::convertOldField($field, self::FIELD_ALLOWED_SELECT);
			if ($newField === null)
				$result[$index] = $field;
			else
				$result[$index] = $newField;
		}
		unset($newField, $index, $field);

		return $result;
	}

	/**
	 * @param array $filter
	 * @return array
	 */
	public static function convertOldFilter(array $filter)
	{
		$result = [];

		if (empty($filter))
			return $result;

		foreach ($filter as $field => $value)
		{
			if (is_object($value))
			{
				$result[$field] = $value;
			}
			elseif (is_numeric($field))
			{
				if (is_array($value))
					$result[$field] = self::convertOldFilter($value);
				else
					$result[$field] = $value;
			}
			else
			{
				$filterItem = \CIBlock::MkOperationFilter($field);
				$newField = self::convertOldField($filterItem['FIELD'], self::FIELD_ALLOWED_FILTER);
				if ($newField !== null)
					$result[$filterItem['PREFIX'].$newField] = $value;
				else
					$result[$field] = $value;
				unset($newField, $filterItem);
			}
		}
		unset($filed, $value);

		return $result;
	}

	/**
	 * @param array $order
	 * @return array
	 */
	public static function convertOldOrder(array $order)
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
	 * @return void
	 */
	private static function initEntityDescription()
	{
		if (!empty(self::$entityDescription))
			return;

		self::$entityDescription = [
			self::ENTITY_PRODUCT => [
				'NAME' => 'b_catalog_product',
				'ALIAS' => 'PRD',
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.ID = #ELEMENT#.ID)'
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
				'JOIN' => 'left join #NAME# as #ALIAS# on (#ALIAS#.ID = IF((CAT_PR.VAT_ID IS NULL OR CAT_PR.VAT_ID = 0), CAT_IB.VAT_ID, CAT_PR.VAT_ID))',
				'RELATION' => [self::ENTITY_CATALOG_IBLOCK]
			]
		];
	}

	/**
	 * @return void
	 */
	private static function initEntityFields()
	{
		if (!empty(self::$entityFields))
			return;

		self::$entityFields = [
			self::ENTITY_PRODUCT => [
				'TYPE' => [
					'NAME' => 'TYPE',
					'ALIAS' => 'TYPE',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'AVAILABLE' => [
					'NAME' => 'AVAILABLE',
					'ALIAS' => 'AVAILABLE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'ORDER_DEFAULT' => 'DESC'
				],
				'BUNDLE' => [
					'NAME' => 'BUNDLE',
					'ALIAS' => 'BUNDLE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'QUANTITY' => [
					'NAME' => 'QUANTITY',
					'ALIAS' => 'QUANTITY',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'QUANTITY_RESERVED' => [
					'NAME' => 'QUANTITY_RESERVED',
					'ALIAS' => 'QUANTITY_RESERVED',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'QUANTITY_TRACE' => [
					'NAME' => 'QUANTITY_TRACE',
					'ALIAS' => 'QUANTITY_TRACE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'SELECT_EXPRESSION' => [__CLASS__, 'selectQuantityTrace'],
					'FILTER_PREPARE_VALUE_EXPRESSION' => [__CLASS__, 'prepareFilterQuantityTrace']
				],
				'QUANTITY_TRACE_RAW' => [
					'NAME' => 'QUANTITY_TRACE',
					'ALIAS' => 'QUANTITY_TRACE_RAW',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
				],
				'CAN_BUY_ZERO' => [
					'NAME' => 'CAN_BUY_ZERO',
					'ALIAS' => 'CAN_BUY_ZERO',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'SELECT_EXPRESSION' => [__CLASS__, 'selectCanBuyZero'],
					'FILTER_PREPARE_VALUE_EXPRESSION' => [__CLASS__, 'prepareFilterCanBuyZero']
				],
				'CAN_BUY_ZERO_RAW' => [
					'NAME' => 'CAN_BUY_ZERO',
					'ALIAS' => 'CAN_BUY_ZERO_RAW',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
				],
				'SUBSCRIBE' => [
					'NAME' => 'SUBSCRIBE',
					'ALIAS' => 'SUBSCRIBE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'SELECT_EXPRESSION' => [__CLASS__, 'selectSubscribe'],
					'FILTER_PREPARE_VALUE_EXPRESSION' => [__CLASS__, 'prepareFilterSubscribe']
				],
				'SUBSCRIBE_RAW' => [
					'NAME' => 'SUBSCRIBE',
					'ALIAS' => 'SUBSCRIBE_RAW',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'VAT_ID' => [
					'NAME' => 'VAT_ID',
					'ALIAS' => 'VAT_ID',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'VAT_INCLUDED' => [
					'NAME' => 'VAT_INCLUDED',
					'ALIAS' => 'VAT_INCLUDED',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'PURCHASING_PRICE' => [
					'NAME' => 'PURCHASING_PRICE',
					'ALIAS' => 'PURCHASING_PRICE',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'PURCHASING_CURRENCY' => [
					'NAME' => 'PURCHASING_CURRENCY',
					'ALIAS' => 'PURCHASING_CURRENCY',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'BARCODE_MULTI' => [
					'NAME' => 'BARCODE_MULTI',
					'ALIAS' => 'BARCODE_MULTI',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'WEIGHT' => [
					'NAME' => 'WEIGHT',
					'ALIAS' => 'WEIGHT',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'WIDTH' => [
					'NAME' => 'WIDTH',
					'ALIAS' => 'WIDTH',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'LENGTH' => [
					'NAME' => 'LENGTH',
					'ALIAS' => 'LENGTH',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'HEIGHT' => [
					'NAME' => 'HEIGHT',
					'ALIAS' => 'HEIGHT',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'MEASURE' => [
					'NAME' => 'MEASURE',
					'ALIAS' => 'MEASURE',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'PAYMENT_TYPE' => [
					'NAME' => 'PRICE_TYPE',
					'ALIAS' => 'PAYMENT_TYPE',
					'TYPE' => '',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'RECUR_SCHEME_LENGTH' => [
					'NAME' => 'RECUR_SCHEME_LENGTH',
					'ALIAS' => 'RECUR_SCHEME_LENGTH',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'RECUR_SCHEME_TYPE' => [
					'NAME' => 'RECUR_SCHEME_TYPE',
					'ALIAS' => 'RECUR_SCHEME_TYPE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'TRIAL_PRICE_ID' => [
					'NAME' => 'TRIAL_PRICE_ID',
					'ALIAS' => 'TRIAL_PRICE_ID',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'WITHOUT_ORDER' => [
					'NAME' => 'WITHOUT_ORDER',
					'ALIAS' => 'WITHOUT_ORDER',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				]
			],
			self::ENTITY_PRICE => [
				'PRICE' => [
					'NAME' => 'PRICE',
					'ALIAS' => 'PRICE_#ENTITY_ID#',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'ORDER_NULLABLE' => true,
				],
				'CURRENCY' => [
					'NAME' => 'CURRENCY',
					'ALIAS' => 'CURRENCY_#ENTITY_ID#',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'ORDER_NULLABLE' => true,
				],
				'QUANTITY_FROM' => [
					'NAME' => 'QUANTITY_FROM',
					'ALIAS' => 'QUANTITY_FROM_#ENTITY_ID#',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'ORDER_NULLABLE' => true,
				],
				'QUANTITY_TO' => [
					'NAME' => 'QUANTITY_TO',
					'ALIAS' => 'QUANTITY_TO_#ENTITY_ID#',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'ORDER_NULLABLE' => true,
				],
				'SCALED_PRICE' => [
					'NAME' => 'PRICE_SCALE',
					'ALIAS' => 'SCALED_PRICE_#ENTITY_ID#',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_FILTER|self::FIELD_ALLOWED_ORDER,
					'ORDER_NULLABLE' => true,
				],
				'EXTRA_ID' => [
					'NAME' => 'EXTRA_ID',
					'ALIAS' => 'EXTRA_ID_#ENTITY_ID#',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'DEFAULT_PRICE_FILTER' => [
					'PHANTOM' => true,
					'NAME' => null,
					'ALIAS' => 'DEFAULT_PRICE_FILTER_#ENTITY_ID#',
					'TYPE' => null,
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'JOIN_MODIFY_EXPRESSION' => [__CLASS__, 'priceParametersFilter']
				],
				'QUANTITY_RANGE_FILTER' => [
					'PHANTOM' => true,
					'NAME' => null,
					'ALIAS' => 'QUANTITY_RANGE_FILTER_#ENTITY_ID#',
					'TYPE' => null,
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'JOIN_MODIFY_EXPRESSION' => [__CLASS__, 'priceParametersFilter']
				],
				'CURRENCY_FOR_SCALE' => [
					'PHANTOM' => true,
					'NAME' => null,
					'ALIAS' => 'CURRENCY_FOR_SCALE_#ENTITY_ID#',
					'TYPE' => null,
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'FILTER_MODIFY_EXPRESSION' => [__CLASS__, 'filterModifierCurrencyScale']
				]
			],
			self::ENTITY_WARENHOUSE => [
				'STORE_AMOUNT' => [
					'NAME' => 'AMOUNT',
					'ALIAS' => 'STORE_AMOUNT_#ENTITY_ID#',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'ORDER_NULLABLE' => true,
				]
			],
			self::ENTITY_FLAT_PRICE => [
				'PRICE_TYPE' => [
					'NAME' => 'CATALOG_GROUP_ID',
					'ALIAS' => 'PRICE_TYPE',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_FILTER
				],
				'PRICE' => [
					'NAME' => 'PRICE',
					'ALIAS' => 'PRICE',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'ORDER_NULLABLE' => true,
				],
				'CURRENCY' => [
					'NAME' => 'CURRENCY',
					'ALIAS' => 'CURRENCY',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'ORDER_NULLABLE' => true,
				],
				'QUANTITY_FROM' => [
					'NAME' => 'QUANTITY_FROM',
					'ALIAS' => 'QUANTITY_FROM',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'ORDER_NULLABLE' => true,
				],
				'QUANTITY_TO' => [
					'NAME' => 'QUANTITY_TO',
					'ALIAS' => 'QUANTITY_TO',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'ORDER_NULLABLE' => true,
				],
				'SCALED_PRICE' => [
					'NAME' => 'PRICE_SCALE',
					'ALIAS' => 'SCALED_PRICE',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'ORDER_NULLABLE' => true,
				],
				'EXTRA_ID' => [
					'NAME' => 'EXTRA_ID',
					'ALIAS' => 'EXTRA_ID',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_FILTER
				],
				'DEFAULT_PRICE_FILTER' => [
					'PHANTOM' => true,
					'NAME' => null,
					'ALIAS' => 'DEFAULT_PRICE_FILTER',
					'TYPE' => null,
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'JOIN_MODIFY_EXPRESSION' => [__CLASS__, 'priceParametersFilter']
				],
				'QUANTITY_RANGE_FILTER' => [
					'PHANTOM' => true,
					'NAME' => null,
					'ALIAS' => 'QUANTITY_RANGE_FILTER',
					'TYPE' => null,
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'JOIN_MODIFY_EXPRESSION' => [__CLASS__, 'priceParametersFilter']
				],
				'CURRENCY_FOR_SCALE' => [
					'PHANTOM' => true,
					'NAME' => null,
					'ALIAS' => 'CURRENCY_FOR_SCALE',
					'TYPE' => null,
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'FILTER_MODIFY_EXPRESSION' => [__CLASS__, 'filterModifierCurrencyScale']
				]
			],
			self::ENTITY_FLAT_WAREHNOUSE => [
				'STORE_NUMBER' => [
					'NAME' => 'STORE_ID',
					'ALIAS' => 'STORE_NUMBER',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_FILTER
				],
				'STORE_AMOUNT' => [
					'NAME' => 'AMOUNT',
					'ALIAS' => 'STORE_AMOUNT',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'ORDER_NULLABLE' => true,
				]
			],
			self::ENTITY_OLD_PRODUCT => [
				'QUANTITY' => [
					'NAME' => 'QUANTITY',
					'ALIAS' => 'CATALOG_QUANTITY',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'QUANTITY_TRACE' => [
					'NAME' => 'QUANTITY_TRACE',
					'ALIAS' => 'CATALOG_QUANTITY_TRACE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT,
					'SELECT_EXPRESSION' => [__CLASS__, 'selectQuantityTrace']
				],
				'QUANTITY_TRACE_ORIG' => [
					'NAME' => 'QUANTITY_TRACE',
					'ALIAS' => 'CATALOG_QUANTITY_TRACE_ORIG',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT,
					'NEW_ID' => 'QUANTITY_TRACE_RAW'
				],
				'WEIGHT' => [
					'NAME' => 'WEIGHT',
					'ALIAS' => 'CATALOG_WEIGHT',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'VAT_ID' => [
					'NAME' => 'VAT_ID',
					'ALIAS' => 'CATALOG_VAT_ID',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'VAT_INCLUDED' => [
					'NAME' => 'VAT_INCLUDED',
					'ALIAS' => 'CATALOG_VAT_INCLUDED',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'CAN_BUY_ZERO' => [
					'NAME' => 'CAN_BUY_ZERO',
					'ALIAS' => 'CATALOG_CAN_BUY_ZERO',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT,
					'SELECT_EXPRESSION' => [__CLASS__, 'selectCanBuyZero']
				],
				'CAN_BUY_ZERO_ORIG' => [
					'NAME' => 'CAN_BUY_ZERO',
					'ALIAS' => 'CATALOG_CAN_BUY_ZERO_ORIG',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT,
					'NEW_ID' => 'CAN_BUY_ZERO_RAW'
				],
				'PURCHASING_PRICE' => [
					'NAME' => 'PURCHASING_PRICE',
					'ALIAS' => 'CATALOG_PURCHASING_PRICE',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'PURCHASING_CURRENCY' => [
					'NAME' => 'PURCHASING_CURRENCY',
					'ALIAS' => 'CATALOG_PURCHASING_CURRENCY',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'QUANTITY_RESERVED' => [
					'NAME' => 'QUANTITY_RESERVED',
					'ALIAS' => 'CATALOG_QUANTITY_RESERVED',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'SUBSCRIBE' => [
					'NAME' => 'SUBSCRIBE',
					'ALIAS' => 'CATALOG_SUBSCRIBE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT|self::FIELD_ALLOWED_FILTER,
					'SELECT_EXPRESSION' => [__CLASS__, 'selectSubscribe'],
					'FILTER_PREPARE_VALUE_EXPRESSION' => [__CLASS__, 'prepareFilterSubscribe']
				],
				'SUBSCRIBE_ORIG' => [
					'NAME' => 'SUBSCRIBE',
					'ALIAS' => 'CATALOG_SUBSCRIBE_ORIG',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT,
					'NEW_ID' => 'SUBSCRIBE_RAW'
				],
				'WIDTH' => [
					'NAME' => 'WIDTH',
					'ALIAS' => 'CATALOG_WIDTH',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'LENGTH' => [
					'NAME' => 'LENGTH',
					'ALIAS' => 'CATALOG_LENGTH',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'HEIGHT' => [
					'NAME' => 'HEIGHT',
					'ALIAS' => 'CATALOG_HEIGHT',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'MEASURE' => [
					'NAME' => 'MEASURE',
					'ALIAS' => 'CATALOG_MEASURE',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'TYPE' => [
					'NAME' => 'TYPE',
					'ALIAS' => 'CATALOG_TYPE',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'AVAILABLE' => [
					'NAME' => 'AVAILABLE',
					'ALIAS' => 'CATALOG_AVAILABLE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'ORDER_DEFAULT' => 'DESC'
				],
				'BUNDLE' => [
					'NAME' => 'BUNDLE',
					'ALIAS' => 'CATALOG_BUNDLE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL
				],
				'PRICE_TYPE' => [
					'NAME' => 'PRICE_TYPE',
					'ALIAS' => 'CATALOG_PRICE_TYPE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT,
					'NEW_ID' => 'PAYMENT_TYPE'
				],
				'RECUR_SCHEME_LENGTH' => [
					'NAME' => 'RECUR_SCHEME_LENGTH',
					'ALIAS' => 'CATALOG_RECUR_SCHEME_LENGTH',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'RECUR_SCHEME_TYPE' => [
					'NAME' => 'RECUR_SCHEME_TYPE',
					'ALIAS' => 'CATALOG_RECUR_SCHEME_TYPE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'TRIAL_PRICE_ID' => [
					'NAME' => 'TRIAL_PRICE_ID',
					'ALIAS' => 'CATALOG_TRIAL_PRICE_ID',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'WITHOUT_ORDER' => [
					'NAME' => 'WITHOUT_ORDER',
					'ALIAS' => 'CATALOG_WITHOUT_ORDER',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'SELECT_BEST_PRICE' => [
					'NAME' => 'SELECT_BEST_PRICE',
					'ALIAS' => 'CATALOG_SELECT_BEST_PRICE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'NEGATIVE_AMOUNT_TRACE' => [
					'NAME' => 'NEGATIVE_AMOUNT_TRACE',
					'ALIAS' => 'CATALOG_NEGATIVE_AMOUNT_TRACE',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT,
					'SELECT_EXPRESSION' => [__CLASS__, 'selectNegativeAmountTrace']
				],
				'NEGATIVE_AMOUNT_TRACE_ORIG' => [
					'NAME' => 'NEGATIVE_AMOUNT_TRACE',
					'ALIAS' => 'CATALOG_NEGATIVE_AMOUNT_TRACE_ORIG',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				]
			],
			self::ENTITY_OLD_PRICE => [
				'ID' => [
					'NAME' => 'ID',
					'ALIAS' => 'CATALOG_PRICE_ID_#ENTITY_ID#',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'PRODUCT_ID' => [
					'NAME' => 'PRODUCT_ID',
					'ALIAS' => null,
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_FILTER
				],
				'CATALOG_GROUP_ID' => [
					'NAME' => 'CATALOG_GROUP_ID',
					'ALIAS' => 'CATALOG_GROUP_ID_#ENTITY_ID#',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT|self::FIELD_ALLOWED_FILTER
				],
				'PRICE' => [
					'NAME' => 'PRICE',
					'ALIAS' => 'CATALOG_PRICE_#ENTITY_ID#',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'ORDER_TRANSFORM' => 'PRICE_SCALE'
				],
				'CURRENCY' => [
					'NAME' => 'CURRENCY',
					'ALIAS' => 'CATALOG_CURRENCY_#ENTITY_ID#',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'ORDER_NULLABLE' => true,
				],
				'PRICE_SCALE' => [
					'NAME' => 'PRICE_SCALE',
					'ALIAS' => null,
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ORDER|self::FIELD_ALLOWED_FILTER,
					'ORDER_NULLABLE' => true,
					'NEW_ID' => 'SCALED_PRICE'
				],
				'QUANTITY_FROM' => [
					'NAME' => 'QUANTITY_FROM',
					'ALIAS' => 'CATALOG_QUANTITY_FROM_#ENTITY_ID#',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'QUANTITY_TO' => [
					'NAME' => 'QUANTITY_TO',
					'ALIAS' => 'CATALOG_QUANTITY_TO_#ENTITY_ID#',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'EXTRA_ID' => [
					'NAME' => 'EXTRA_ID',
					'ALIAS' => 'CATALOG_EXTRA_ID_#ENTITY_ID#',
					'TYPE' => 'int',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				],
				'CATALOG_GROUP_NAME' => [
					'NAME' => null,
					'ALIAS' => 'CATALOG_GROUP_NAME_#ENTITY_ID#',
					'TYPE' => 'string',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT,
					'SELECT_EXPRESSION' => [__CLASS__, 'selectPriceTypeName']
				],
				'CATALOG_CAN_ACCESS' => [
					'NAME' => null,
					'ALIAS' => 'CATALOG_CAN_ACCESS_#ENTITY_ID#',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT,
					'SELECT_EXPRESSION' => [__CLASS__, 'selectPriceTypeAllowedView']
				],
				'CATALOG_CAN_BUY' => [
					'NAME' => null,
					'ALIAS' => 'CATALOG_CAN_BUY_#ENTITY_ID#',
					'TYPE' => 'char',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT,
					'SELECT_EXPRESSION' => [__CLASS__, 'selectPriceTypeAllowedBuy']
				],
				'CURRENCY_SCALE' => [
					'PHANTOM' => true,
					'NAME' => null,
					'ALIAS' => 'CATALOG_CURRENCY_SCALE_#ENTITY_ID#',
					'TYPE' => null,
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'FILTER_MODIFY_EXPRESSION' => [__CLASS__, 'filterModifierCurrencyScale'],
					'NEW_ID' => 'CURRENCY_FOR_SCALE'
				],
				'SHOP_QUANTITY' => [
					'PHANTOM' => true,
					'NAME' => null,
					'ALIAS' => 'CATALOG_SHOP_QUANTITY_#ENTITY_ID#',
					'TYPE' => null,
					'ALLOWED' => self::FIELD_ALLOWED_FILTER,
					'JOIN_MODIFY_EXPRESSION' => [__CLASS__, 'priceParametersFilter'],
					'NEW_ID' => 'DEFAULT_PRICE_FILTER'
				]
			],
			self::ENTITY_OLD_STORE => [
				'STORE_AMOUNT' => [
					'NAME' => 'AMOUNT',
					'ALIAS' => 'CATALOG_STORE_AMOUNT_#ENTITY_ID#',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_ALL,
					'ORDER_NULLABLE' => true,
				]
			],
			self::ENTITY_VAT => [
				'RATE' => [
					'NAME' => 'RATE',
					'ALIAS' => 'CATALOG_VAT',
					'TYPE' => 'float',
					'ALLOWED' => self::FIELD_ALLOWED_SELECT
				]
			]
		];
	}

	/**
	 * @param string $entity
	 * @param string $field
	 * @return int
	 */
	private static function getFieldAllowed($entity, $field)
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
	private static function getFieldsAllowedToSelect($entity)
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
	private static function parseField($field)
	{
		$field = (string)$field;
		if ($field === '')
			return null;

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
			$entityId = 0;
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
				$entityId = 0;
				$checked = true;
			}
		}

		if ($checked)
		{
			$allowed = self::getFieldAllowed($entity, $field);
			if (empty($allowed))
				$checked = false;
		}

		if (!$checked)
			return null;

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
	private static function searchFieldEntity($field, $type)
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
	private static function checkAllowedAction($allowed, $action)
	{
		return ($allowed & $action) > 0;
	}

	/**
	 * @param array $field
	 * @return string
	 */
	private static function getEntityIndex(array $field)
	{
		return $field['ENTITY'].':'.$field['ENTITY_ID'];
	}

	/**
	 * Returns entity data for sql query.
	 *
	 * @param array $entity
	 * @return array|null
	 */
	private static function getEntityDescription(array $entity)
	{
		if (!isset(self::$entityDescription[$entity['ENTITY']]))
			return null;
		$row = self::$entityDescription[$entity['ENTITY']];
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
	private static function getFieldIndex(array $field)
	{
		return $field['ENTITY'].':'.$field['ENTITY_ID'].':'.$field['FIELD'];
	}

	/**
	 * @param array $field
	 * @return bool
	 */
	private static function isPhantomField(array $field)
	{
		return isset($field['PHANTOM']);
	}

	/**
	 * @param string $entity
	 * @param string $field
	 * @return null|array
	 */
	private static function getFieldDescription($entity, $field)
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
	private static function getField(array $queryItem, array $options)
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
			return null;

		$entity = self::getEntityDescription($queryItem);
		if (empty($entity))
			return null;

		$fantomField = self::isPhantomField($field);

		$field['ALIAS'] = str_replace('#ENTITY_ID#', $queryItem['ENTITY_ID'], $field['ALIAS']);
		if (!$fantomField)
		{
			$field['FULL_NAME'] = $entity['ALIAS'].'.'.(string)$field['NAME'];
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
				$valueType = 'string';
				if ($field['TYPE'] == 'int' || $field['TYPE'] == 'float')
					$valueType = 'number';
				elseif ($field['TYPE'] == 'char')
					$valueType = 'string_equal';

				$field['FILTER'] = \CIBlock::FilterCreate(
					'#FULL_NAME#',
					$queryItem['VALUES'],
					$valueType,
					$queryItem['OPERATION']
				);
				$field['FILTER'] = str_replace('#FULL_NAME#', $field['FULL_NAME'], $field['FILTER']);
				unset($valueType);
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
					isset($field['ORDER_DEFAULT']) ? $field['ORDER_DEFAULT'] : 'ASC',
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
	 * @param $field
	 * @return bool
	 */
	private static function checkPreparedField($field)
	{
		if (empty($field))
			return false;
		if ($field['ENTITY_ID'] == 0)
		{
			if (
				$field['ENTITY'] != self::ENTITY_PRODUCT
				&& $field['ENTITY'] != self::ENTITY_FLAT_PRICE
				&& $field['ENTITY'] != self::ENTITY_FLAT_WAREHNOUSE
				&& $field['ENTITY'] != self::ENTITY_OLD_PRODUCT
			)
				return false;
		}
		return true;
	}

	/**
	 * @param array $field
	 * @return array
	 */
	private static function getFieldSignature(array $field)
	{
		return [
			'ENTITY' => $field['ENTITY'],
			'FIELD' => $field['FIELD'],
			'ENTITY_ID' => $field['ENTITY_ID']
		];
	}

	/**
	 * @param array $parameters
	 * @return void
	 */
	private static function prepareSelectedCompatibleFields(array &$parameters)
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
	private static function fillCompatibleEntities(array &$result, array $field)
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
	 * @return array
	 */
	private static function prepareQuery(array $parameters, array $options)
	{
		self::initEntityDescription();
		self::initEntityFields();

		self::setOptions($options);

		$result = [
			'compatible_mode' => false,
			'compatible_entities' => [],
			'select' => [],
			'filter' => [],
			'order' => []
		];

		if (!empty($parameters['select']) && is_array($parameters['select']))
		{
			foreach ($parameters['select'] as $field)
			{
				$prepareField = self::parseField($field);
				if (!self::checkPreparedField($prepareField))
					continue;
				if (!self::checkAllowedAction($prepareField['ALLOWED'], self::FIELD_ALLOWED_SELECT))
					continue;

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
					continue;
				if (!self::checkAllowedAction($prepareField['ALLOWED'], self::FIELD_ALLOWED_FILTER))
					continue;

				self::fillCompatibleEntities($result, $prepareField);
				$prepareField = self::getFieldSignature($prepareField);
				$prepareField['OPERATION'] = $filter['OPERATION'];
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
					continue;

				$order = reset($value);
				$field = key($value);

				$prepareField = self::parseField($field);
				if (!self::checkPreparedField($prepareField))
					continue;
				if (!self::checkAllowedAction($prepareField['ALLOWED'], self::FIELD_ALLOWED_ORDER))
					continue;

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
	private static function clearOptions()
	{
		self::$options = [];
	}

	/**
	 * @param array $options
	 * @return void
	 */
	private static function setOptions(array $options)
	{
		global $USER;

		if (!isset($options['ALIASES']))
			$options['ALIASES'] = [];
		if (!isset($options['ALIASES']['#ELEMENT#']))
			$options['ALIASES']['#ELEMENT#'] = 'BE';

		if (!isset($options['USER']))
			$options['USER'] = [];
		if (!isset($options['USER']['ID']))
			$options['USER']['ID'] = (\CCatalog::IsUserExists() ? (int)$USER->GetID() : 0);
		$options['USER']['GROUPS'] = Main\UserTable::getUserGroupIds($options['USER']['ID']);

		self::$options = $options;
	}

	/**
	 * @param $index
	 * @return mixed|null
	 */
	private static function getOption($index)
	{
		if (!isset(self::$options[$index]))
			return null;
		return self::$options[$index];
	}

	/**
	 * @param array $parameters
	 * @return array|null
	 */
	private static function build(array $parameters)
	{
		$founded = false;
		$result = [
			'select' => [],
			'filter' => [],
			'order' => [],
			'join' => [],
			'join_modify' => []
		];

		if (!empty($parameters['select']))
		{
			self::buildSelect($result, $parameters['select']);
			if (!empty($result['select']))
				$founded = true;
		}
		if (!empty($parameters['filter']))
		{
			self::buildFilter($result, $parameters['filter']);
			if (!empty($result['filter']))
				$founded = true;
		}
		if (!empty($parameters['order']))
		{
			self::buildOrder($result, $parameters['order']);
			if (!empty($result['order']))
				$founded = true;
		}

		if (!$founded)
			return null;

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
	private static function buildSelect(array &$result, array $list)
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
	private static function buildFilter(array &$result, array $list)
	{
		self::filterModify($list);
		foreach ($list as $item)
		{
			$field = self::getField($item, ['filter' => true]);
			if (empty($field))
				continue;

			if (isset($field['FILTER']))
				$result['filter'][] = $field['FILTER'];

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
	private static function buildOrder(array &$result, array $list)
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
	private static function buildJoin(array &$result)
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

	private static function filterModify(array &$list)
	{
		foreach (array_keys($list) as $index)
		{
			$item = $list[$index];
			$field = self::getFieldDescription($item['ENTITY'], $item['FIELD']);
			if (empty($field))
				continue;

			$entity = self::getEntityDescription($item);
			if (empty($entity))
				continue;

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

	/**
	 * @param array &$result
	 * @param array $entity
	 * @return void
	 */
	private static function addJoin(array &$result, array $entity)
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
	 * @param array &$item
	 * @return void
	 */
	private static function orderTransformField(array &$item)
	{
		$field = self::getFieldDescription($item['ENTITY'], $item['FIELD']);
		if (empty($field))
			return;
		if (isset($field['ORDER_TRANSFORM']))
			$item['FIELD'] = $field['ORDER_TRANSFORM'];
		unset($field);
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * Returns sql code for select QUANTITY_TRACE with converted default value.
	 *
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectQuantityTrace(array &$parameters, array &$entity, array &$field)
	{
		$field['SELECT'] = self::getReplaceSqlFunction(Main\Config\Option::get('catalog', 'default_quantity_trace'));
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * Returns sql code for select CAN_BUY_ZERO with converted default value.
	 *
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectCanBuyZero(array &$parameters, array &$entity, array &$field)
	{
		$field['SELECT'] = self::getReplaceSqlFunction(Main\Config\Option::get('catalog', 'default_can_buy_zero'));
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * Returns sql code for select NEGATIVE_AMOUNT_TRACE with converted default value.
	 *
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectNegativeAmountTrace(array &$parameters, array &$entity, array &$field)
	{
		$field['SELECT'] = self::getReplaceSqlFunction(Main\Config\Option::get('catalog', 'allow_negative_amount'));
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * Returns sql code for select SUBSCRIBE with converted default value.
	 *
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectSubscribe(array &$parameters, array &$entity, array &$field)
	{
		$field['SELECT'] = self::getReplaceSqlFunction(Main\Config\Option::get('catalog', 'default_subscribe'));
	}

	/**
	 * Returns sql code for select field with default value.
	 *
	 * @param string $defaultValue
	 * @return string
	 */
	private static function getReplaceSqlFunction($defaultValue)
	{
		return 'IF (#FULL_NAME# = \''.ProductTable::STATUS_DEFAULT.'\', \''.$defaultValue.'\', #FULL_NAME#)';
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectPriceTypeName(array &$parameters, array &$entity, array &$field)
	{
		$result = '';
		$id = $parameters['ENTITY_ID'];
		$fullPriceTypeList = \CCatalogGroup::GetListArray();
		if (!empty($fullPriceTypeList[$id]))
		{
			$result = (!empty($fullPriceTypeList[$id]['NAME_LANG'])
				? $fullPriceTypeList[$id]['NAME_LANG']
				: $fullPriceTypeList[$id]['NAME']
			);
			$connection = \Bitrix\Main\Application::getInstance()->getConnection();
			$sqlHelper = $connection->getSqlHelper();
			$result = $sqlHelper->forSql($result);
			unset($sqlHelper, $connection);
		}
		unset($fullPriceTypeList, $id);
		$field['SELECT'] = '\''.$result.'\'';
		unset($result);
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectPriceTypeAllowedView(array &$parameters, array &$entity, array &$field)
	{
		$parameters['ACCESS'] = GroupAccessTable::ACCESS_VIEW;
		$field['SELECT'] = self::getPriceTypeAccess($parameters);
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function selectPriceTypeAllowedBuy(array &$parameters, array &$entity, array &$field)
	{
		$parameters['ACCESS'] = GroupAccessTable::ACCESS_BUY;
		$field['SELECT'] = self::getPriceTypeAccess($parameters);
	}

	/**
	 * @param array $parameters
	 * @return string
	 */
	private static function getPriceTypeAccess(array $parameters)
	{
		$result = 'N';

		$user = self::getOption('USER');
		if (!empty($user))
		{
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

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function prepareFilterQuantityTrace(array &$parameters, array &$entity, array &$field)
	{
		$parameters['VALUES'] = self::addDefaultValue(
			$parameters['VALUES'],
			Main\Config\Option::get('catalog', 'default_quantity_trace')
		);
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function prepareFilterCanBuyZero(array &$parameters, array &$entity, array &$field)
	{
		$parameters['VALUES'] = self::addDefaultValue(
			$parameters['VALUES'],
			Main\Config\Option::get('catalog', 'default_can_buy_zero')
		);
	}

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function prepareFilterSubscribe(array &$parameters, array &$entity, array &$field)
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
	private static function addDefaultValue($values, $defaultValue)
	{
		if (!is_array($values))
		{
			if ($values == $defaultValue)
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

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * @param array &$parameters
	 * @param array &$entity
	 * @param array &$field
	 * @return void
	 */
	private static function priceParametersFilter(array &$parameters, array &$entity, array &$field)
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

	/** @noinspection PhpUnusedPrivateMethodInspection */
	/**
	 * @param array &$filter
	 * @param int $filterKey
	 * @param array $entity
	 * @param array $field
	 * @return void
	 */
	private static function filterModifierCurrencyScale(array &$filter, $filterKey, array $entity, array $field)
	{
		$activeItem = $filter[$filterKey];

		if ($activeItem['FIELD'] != 'CURRENCY_FOR_SCALE')
			return;
		if ($activeItem['OPERATION'] != 'E')
			return;

		$value = $activeItem['VALUES'];
		if (!is_string($value))
			return;

		$currencyId = Currency\CurrencyManager::checkCurrencyID($value);
		if ($currencyId === false)
			return;

		$currency = \CCurrency::GetByID($currencyId);
		if (empty($currency))
			return;
		$currency['CURRENT_BASE_RATE'] = (float)$currency['CURRENT_BASE_RATE'];
		if ($currency['CURRENT_BASE_RATE'] <= 0)
			return;

		foreach (array_keys($filter) as $index)
		{
			if ($index == $filterKey)
				continue;
			$filterItem = $filter[$index];
			if (
				$filterItem['ENTITY'] != $activeItem['ENTITY']
				|| $filterItem['ENTITY_ID'] != $activeItem['ENTITY_ID']
			)
				continue;
			if ($filterItem['FIELD'] != 'PRICE')
				continue;
			if (is_array($filter[$index]['VALUES']))
			{
				$newPrices = [];
				foreach ($filter[$index]['VALUES'] as $oldPrice)
					$newPrices[] = (float)$oldPrice*$currency['CURRENT_BASE_RATE'];
				$filter[$index]['VALUES'] = $newPrices;
				unset($oldPrice, $newPrices);
			}
			else
			{
				$filter[$index]['VALUES'] = (float)$filter[$index]['VALUES']*$currency['CURRENT_BASE_RATE'];
			}
		}
		unset($index);
	}
}