<?php
namespace Bitrix\Catalog;

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ORM\Data\DataManager;

/**
 * Class ProductTable
 *
 * Fields:
 * <ul>
 * <li> ID int mandatory
 * <li> QUANTITY double mandatory
 * <li> QUANTITY_TRACE bool optional default 'N'
 * <li> WEIGHT double mandatory
 * <li> TIMESTAMP_X datetime mandatory default 'CURRENT_TIMESTAMP'
 * <li> PRICE_TYPE string(1) mandatory default 'S'
 * <li> RECUR_SCHEME_LENGTH int optional
 * <li> RECUR_SCHEME_TYPE string(1) mandatory default 'D'
 * <li> TRIAL_PRICE_ID int optional
 * <li> WITHOUT_ORDER bool optional default 'N'
 * <li> SELECT_BEST_PRICE bool optional default 'Y'
 * <li> VAT_ID int optional
 * <li> VAT_INCLUDED bool optional default 'Y'
 * <li> CAN_BUY_ZERO bool optional default 'N'
 * <li> NEGATIVE_AMOUNT_TRACE string(1) mandatory default 'D'
 * <li> TMP_ID string(40) optional
 * <li> PURCHASING_PRICE double optional
 * <li> PURCHASING_CURRENCY string(3) optional
 * <li> BARCODE_MULTI bool optional default 'N'
 * <li> QUANTITY_RESERVED double optional
 * <li> SUBSCRIBE string(1) optional
 * <li> WIDTH double optional
 * <li> LENGTH double optional
 * <li> HEIGHT double optional
 * <li> MEASURE int optional
 * <li> TYPE int optional
 * <li> AVAILABLE string(1) optional
 * <li> BUNDLE string(1) optional
 * <li> IBLOCK_ELEMENT reference to {@link \Bitrix\Iblock\ElementTable}
 * <li> TRIAL_IBLOCK_ELEMENT reference to {@link \Bitrix\Iblock\ElementTable}
 * <li> TRIAL_PRODUCT reference to {@link \Bitrix\Catalog\ProductTable}
 * </ul>
 *
 * @package Bitrix\Catalog
 *
 * DO NOT WRITE ANYTHING BELOW THIS
 *
 * <<< ORMENTITYANNOTATION
 * @method static EO_Product_Query query()
 * @method static EO_Product_Result getByPrimary($primary, array $parameters = [])
 * @method static EO_Product_Result getById($id)
 * @method static EO_Product_Result getList(array $parameters = [])
 * @method static EO_Product_Entity getEntity()
 * @method static \Bitrix\Catalog\EO_Product createObject($setDefaultValues = true)
 * @method static \Bitrix\Catalog\EO_Product_Collection createCollection()
 * @method static \Bitrix\Catalog\EO_Product wakeUpObject($row)
 * @method static \Bitrix\Catalog\EO_Product_Collection wakeUpCollection($rows)
 */

class ProductTable extends DataManager
{
	public const USER_FIELD_ENTITY_ID = 'PRODUCT';

	public const STATUS_YES = 'Y';
	public const STATUS_NO = 'N';
	public const STATUS_DEFAULT = 'D';

	public const TYPE_PRODUCT = 1;
	public const TYPE_SET = 2;
	public const TYPE_SKU = 3;
	public const TYPE_OFFER = 4;
	public const TYPE_FREE_OFFER = 5;
	public const TYPE_EMPTY_SKU = 6;
	public const TYPE_SERVICE = 7;

	public const PAYMENT_TYPE_SINGLE = 'S';
	public const PAYMENT_TYPE_REGULAR = 'R';
	public const PAYMENT_TYPE_TRIAL = 'T';

	public const PAYMENT_PERIOD_HOUR = 'H';
	public const PAYMENT_PERIOD_DAY = 'D';
	public const PAYMENT_PERIOD_WEEK = 'W';
	public const PAYMENT_PERIOD_MONTH = 'M';
	public const PAYMENT_PERIOD_QUART = 'Q';
	public const PAYMENT_PERIOD_SEMIYEAR = 'S';
	public const PAYMENT_PERIOD_YEAR = 'Y';
	public const PAYMENT_PERIOD_DOUBLE_YEAR = 'T';

	public const PRICE_MODE_SIMPLE = 'S';
	public const PRICE_MODE_QUANTITY = 'Q';
	public const PRICE_MODE_RATIO = 'R';

	protected static array $defaultProductSettings = [];

	/**
	 * Returns DB table name for entity.
	 *
	 * @return string
	 */
	public static function getTableName(): string
	{
		return 'b_catalog_product';
	}

	/**
	 * Returns entity map definition.
	 *
	 * @return array
	 */
	public static function getMap(): array
	{
		return [
			'ID' => new Main\Entity\IntegerField(
				'ID',
				[
					'primary' => true,
					'title' => Loc::getMessage('PRODUCT_ENTITY_ID_FIELD'),
				]
			),
			'QUANTITY' => new Main\Entity\FloatField(
				'QUANTITY',
				[
					'default_value' => 0,
					'title' => Loc::getMessage('PRODUCT_ENTITY_QUANTITY_FIELD'),
				]
			),
			'QUANTITY_TRACE' => new Main\Entity\EnumField(
				'QUANTITY_TRACE',
				[
					'values' => [
						self::STATUS_DEFAULT,
						self::STATUS_NO,
						self::STATUS_YES,
					],
					'default_value' => self::STATUS_DEFAULT,
					'fetch_data_modification' => [__CLASS__, 'modifyQuantityTrace'],
					'title' => Loc::getMessage('PRODUCT_ENTITY_QUANTITY_TRACE_FIELD_MSGVER_1'),
				]
			),
			'QUANTITY_TRACE_ORIG' => new Main\Entity\ExpressionField(
				'QUANTITY_TRACE_ORIG',
				'%s',
				'QUANTITY_TRACE',
				[
					'data_type' => 'string',
				]
			),
			'WEIGHT' => new Main\Entity\FloatField(
				'WEIGHT',
				[
					'default_value' => 0,
					'title' => Loc::getMessage('PRODUCT_ENTITY_WEIGHT_FIELD'),
				]
			),
			'TIMESTAMP_X' => new Main\Entity\DatetimeField(
				'TIMESTAMP_X',
				[
					'default_value' => function()
						{
							return new Main\Type\DateTime();
						},
					'title' => Loc::getMessage('PRODUCT_ENTITY_TIMESTAMP_X_FIELD'),
				]
			),
			'PRICE_TYPE' => new Main\Entity\EnumField(
				'PRICE_TYPE',
				[
					'values' => self::getPaymentTypes(false),
					'default_value' => self::PAYMENT_TYPE_SINGLE,
					'title' => Loc::getMessage('PRODUCT_ENTITY_PRICE_TYPE_FIELD'),
				]
			),
			'RECUR_SCHEME_LENGTH' => new Main\Entity\IntegerField(
				'RECUR_SCHEME_LENGTH',
				[
					'default_value' => 0,
					'title' => Loc::getMessage('PRODUCT_ENTITY_RECUR_SCHEME_LENGTH_FIELD'),
				]
			),
			'RECUR_SCHEME_TYPE' => new Main\Entity\EnumField(
				'RECUR_SCHEME_TYPE',
				[
					'values' => self::getPaymentPeriods(false),
					'default_value' => self::PAYMENT_PERIOD_DAY,
					'title' => Loc::getMessage('PRODUCT_ENTITY_RECUR_SCHEME_TYPE_FIELD'),
				]
			),
			'TRIAL_PRICE_ID' => new Main\Entity\IntegerField(
				'TRIAL_PRICE_ID',
				[
					'title' => Loc::getMessage('PRODUCT_ENTITY_TRIAL_PRICE_ID_FIELD'),
				]
			),
			'WITHOUT_ORDER' => new Main\Entity\BooleanField(
				'WITHOUT_ORDER',
				[
					'values' => [
						self::STATUS_NO,
						self::STATUS_YES,
					],
					'default_value' => self::STATUS_NO,
					'title' => Loc::getMessage('PRODUCT_ENTITY_WITHOUT_ORDER_FIELD'),
				]
			),
			'SELECT_BEST_PRICE' => new Main\Entity\BooleanField(
				'SELECT_BEST_PRICE',
				[
					'values' => [
						self::STATUS_NO,
						self::STATUS_YES,
					],
					'default_value' => self::STATUS_YES,
				]
			),
			'VAT_ID' => new Main\Entity\IntegerField(
				'VAT_ID',
				[
					'default_value' => 0,
					'title' => Loc::getMessage('PRODUCT_ENTITY_VAT_ID_FIELD'),
				]
			),
			'VAT_INCLUDED' => new Main\Entity\BooleanField(
				'VAT_INCLUDED',
				[
					'values' => [
						self::STATUS_NO,
						self::STATUS_YES,
					],
					'default_value' => self::STATUS_NO,
					'title' => Loc::getMessage('PRODUCT_ENTITY_VAT_INCLUDED_FIELD'),
				]
			),
			'CAN_BUY_ZERO' => new Main\Entity\EnumField(
				'CAN_BUY_ZERO',
				[
					'values' => [
						self::STATUS_DEFAULT,
						self::STATUS_NO,
						self::STATUS_YES,
					],
					'default_value' => self::STATUS_DEFAULT,
					'fetch_data_modification' => [__CLASS__, 'modifyCanBuyZero'],
					'title' => Loc::getMessage('PRODUCT_ENTITY_CAN_BUY_ZERO_FIELD'),
				]
			),
			'CAN_BUY_ZERO_ORIG' => new Main\Entity\ExpressionField(
				'CAN_BUY_ZERO_ORIG',
				'%s',
				'CAN_BUY_ZERO',
				[
					'data_type' => 'string',
				]
			),
			'NEGATIVE_AMOUNT_TRACE' => new Main\Entity\EnumField(
				'NEGATIVE_AMOUNT_TRACE',
				[
					'values' => [
						self::STATUS_DEFAULT,
						self::STATUS_NO,
						self::STATUS_YES,
					],
					'default_value' => self::STATUS_DEFAULT,
					'fetch_data_modification' => [__CLASS__, 'modifyNegativeAmountTrace'],
					'title' => Loc::getMessage('PRODUCT_ENTITY_NEGATIVE_AMOUNT_TRACE_FIELD'),
				]
			),
			'NEGATIVE_AMOUNT_TRACE_ORIG' => new Main\Entity\ExpressionField(
				'NEGATIVE_AMOUNT_TRACE_ORIG',
				'%s',
				'NEGATIVE_AMOUNT_TRACE',
				[
					'data_type' => 'string',
				]
			),
			'TMP_ID' => New Main\Entity\StringField(
				'TMP_ID',
				[
					'validation' => [__CLASS__, 'validateTmpId'],
					'title' => Loc::getMessage('PRODUCT_ENTITY_TMP_ID_FIELD'),
				]
			),
			'PURCHASING_PRICE' => new Main\Entity\FloatField(
				'PURCHASING_PRICE',
				[
					'title' => Loc::getMessage('PRODUCT_ENTITY_PURCHASING_PRICE_FIELD'),
				]
			),
			'PURCHASING_CURRENCY' => new Main\Entity\StringField(
				'PURCHASING_CURRENCY',
				[
					'validation' => [__CLASS__, 'validatePurchasingCurrency'],
					'title' => Loc::getMessage('PRODUCT_ENTITY_PURCHASING_CURRENCY_FIELD'),
				]
			),
			'BARCODE_MULTI' => new Main\Entity\BooleanField(
				'BARCODE_MULTI',
				[
					'values' => [
						self::STATUS_NO,
						self::STATUS_YES,
					],
					'default_value' => self::STATUS_NO,
					'title' => Loc::getMessage('PRODUCT_ENTITY_BARCODE_MULTI_FIELD'),
				]
			),
			'QUANTITY_RESERVED' => new Main\Entity\FloatField(
				'QUANTITY_RESERVED',
				[
					'title' => Loc::getMessage('PRODUCT_ENTITY_QUANTITY_RESERVED_FIELD'),
				]
			),
			'SUBSCRIBE' => new Main\Entity\EnumField(
				'SUBSCRIBE',
				[
					'values' => [
						self::STATUS_DEFAULT,
						self::STATUS_NO,
						self::STATUS_YES,
					],
					'default_value' => self::STATUS_DEFAULT,
					'fetch_data_modification' => [__CLASS__, 'modifySubscribe'],
					'title' => Loc::getMessage('PRODUCT_ENTITY_SUBSCRIBE_FIELD'),
				]
			),
			'SUBSCRIBE_ORIG' => new Main\Entity\ExpressionField(
				'SUBSCRIBE_ORIG',
				'%s',
				'SUBSCRIBE',
				[
					'data_type' => 'string',
				]
			),
			'WIDTH' => new Main\Entity\FloatField(
				'WIDTH',
				[
					'title' => Loc::getMessage('PRODUCT_ENTITY_WIDTH_FIELD'),
				]
			),
			'LENGTH' => new Main\Entity\FloatField(
				'LENGTH',
				[
					'title' => Loc::getMessage('PRODUCT_ENTITY_LENGTH_FIELD'),
				]
			),
			'HEIGHT' => new Main\Entity\FloatField(
				'HEIGHT',
				[
					'title' => Loc::getMessage('PRODUCT_ENTITY_HEIGHT_FIELD'),
				]
			),
			'MEASURE' => new Main\Entity\IntegerField(
				'MEASURE',
				[
					'title' => Loc::getMessage('PRODUCT_ENTITY_MEASURE_FIELD'),
				]
			),
			'TYPE' => new Main\Entity\EnumField(
				'TYPE',
				[
					'values' => [
						self::TYPE_PRODUCT,
						self::TYPE_SET,
						self::TYPE_SKU,
						self::TYPE_OFFER,
						self::TYPE_FREE_OFFER,
						self::TYPE_EMPTY_SKU,
						self::TYPE_SERVICE,
					],
					'default_value' => self::TYPE_PRODUCT,
					'title' => Loc::getMessage('PRODUCT_ENTITY_TYPE_FIELD'),
				]
			),
			'AVAILABLE' => new Main\Entity\BooleanField(
				'AVAILABLE',
				[
					'values' => [
						self::STATUS_NO,
						self::STATUS_YES,
					],
					'title' => Loc::getMessage('PRODUCT_ENTITY_AVAILABLE_FIELD'),
				]
			),
			'BUNDLE' => new Main\Entity\BooleanField(
				'BUNDLE',
				[
					'values' => [
						self::STATUS_NO,
						self::STATUS_YES,
					],
					'title' => Loc::getMessage('PRODUCT_ENTITY_BUNDLE_FIELD'),
				]
			),
			'IBLOCK_ELEMENT' => new Main\Entity\ReferenceField(
				'IBLOCK_ELEMENT',
				'\Bitrix\Iblock\Element',
				[
					'=this.ID' => 'ref.ID',
				],
				[
					'join_type' => 'LEFT',
				]
			),
			'TRIAL_IBLOCK_ELEMENT' => new Main\Entity\ReferenceField(
				'TRIAL_IBLOCK_ELEMENT',
				'\Bitrix\Iblock\Element',
				[
					'=this.TRIAL_PRICE_ID' => 'ref.ID',
				],
				[
					'join_type' => 'LEFT',
				]
			),
			'TRIAL_PRODUCT' => new Main\Entity\ReferenceField(
				'TRIAL_PRODUCT',
				'\Bitrix\Catalog\Product',
				[
					'=this.TRIAL_PRICE_ID' => 'ref.ID',
				],
				[
					'join_type' => 'LEFT',
				]
			)
		];
	}

	/**
	 * Returns user fields entity id.
	 *
	 * @return string
	 */
	public static function getUfId(): string
	{
		return self::USER_FIELD_ENTITY_ID;
	}

	/**
	 * Returns validators for PRICE_TYPE field.
	 *
	 * @deprecated deprecated since catalog 16.5.0 - no longer needed.
	 * @internal
	 * @return array
	 */
	public static function validatePriceType()
	{
		return array();
	}

	/**
	 * Returns validators for RECUR_SCHEME_TYPE field.
	 *
	 * @deprecated deprecated since catalog 16.5.0 - no longer needed.
	 * @internal
	 * @return array
	 */
	public static function validateRecurSchemeType()
	{
		return array();
	}

	/**
	 * Returns validators for TMP_ID field.
	 *
	 * @internal
	 * @return array
	 */
	public static function validateTmpId()
	{
		return array(
			new Main\Entity\Validator\Length(null, 40),
		);
	}
	/**
	 * Returns validators for PURCHASING_CURRENCY field.
	 *
	 * @internal
	 * @return array
	 */
	public static function validatePurchasingCurrency()
	{
		return array(
			new Main\Entity\Validator\Length(null, 3),
		);
	}

	/**
	 * Returns fetch modificators for QUANTITY_TRACE field.
	 *
	 * @internal
	 * @return array
	 */
	public static function modifyQuantityTrace()
	{
		return array(
			array(__CLASS__, 'prepareQuantityTrace')
		);
	}

	/**
	 * Returns fetch modificators for CAN_BUY_ZERO field.
	 *
	 * @internal
	 * @return array
	 */
	public static function modifyCanBuyZero()
	{
		return array(
			array(__CLASS__, 'prepareCanBuyZero')
		);
	}

	/**
	 * Returns fetch modificators for NEGATIVE_AMOUNT_TRACE field.
	 *
	 * @internal
	 * @return array
	 */
	public static function modifyNegativeAmountTrace()
	{
		return array(
			array(__CLASS__, 'prepareNegativeAmountTrace')
		);
	}

	/**
	 * Returns fetch modificators for SUBSCRIBE field.
	 *
	 * @internal
	 * @return array
	 */
	public static function modifySubscribe()
	{
		return array(
			array(__CLASS__, 'prepareSubscribe')
		);
	}

	/**
	 * Convert default QUANTITY_TRACE into real from module settings.
	 *
	 * @internal
	 * @param string $value			QUANTITY_TRACE original value.
	 * @return string
	 */
	public static function prepareQuantityTrace($value)
	{
		if ($value == self::STATUS_DEFAULT)
		{
			if (empty(self::$defaultProductSettings))
				self::loadDefaultProductSettings();
			return self::$defaultProductSettings['QUANTITY_TRACE'];
		}
		return $value;
	}

	/**
	 * Convert default CAN_BUY_ZERO into real from module settings.
	 *
	 * @internal
	 * @param string $value			CAN_BUY_ZERO original value.
	 * @return string
	 */
	public static function prepareCanBuyZero($value)
	{
		if ($value == self::STATUS_DEFAULT)
		{
			if (empty(self::$defaultProductSettings))
				self::loadDefaultProductSettings();
			return self::$defaultProductSettings['CAN_BUY_ZERO'];
		}
		return $value;
	}

	/**
	 * Convert default NEGATIVE_AMOUNT_TRACE into real from module settings.
	 *
	 * @internal
	 * @param string $value			NEGATIVE_AMOUNT_TRACE original value.
	 * @return string
	 */
	public static function prepareNegativeAmountTrace($value)
	{
		if ($value == self::STATUS_DEFAULT)
		{
			if (empty(self::$defaultProductSettings))
				self::loadDefaultProductSettings();
			return self::$defaultProductSettings['NEGATIVE_AMOUNT_TRACE'];
		}
		return $value;
	}

	/**
	 * Convert default SUBSCRIBE into real from module settings.
	 *
	 * @internal
	 * @param string $value			SUBSCRIBE original value.
	 * @return string
	 */
	public static function prepareSubscribe($value)
	{
		if ($value == self::STATUS_DEFAULT)
		{
			if (empty(self::$defaultProductSettings))
				self::loadDefaultProductSettings();
			return self::$defaultProductSettings['SUBSCRIBE'];
		}
		return $value;
	}

	/**
	 * Return is exist product.
	 * @deprecated deprecated since catalog 20.100.0
	 * @see \Bitrix\Catalog\Model\Product::getCacheItem()
	 *
	 * @param int $product				Product id.
	 * @return bool
	 * @throws Main\ArgumentException
	 */
	public static function isExistProduct($product)
	{
		$product = (int)$product;
		if ($product <= 0)
			return false;
		$existProduct = self::getList(array(
			'select' => array('ID'),
			'filter' => array('=ID' => $product)
		))->fetch();
		return (!empty($existProduct));
	}

	/**
	 * Clear product cache.
	 * @deprecated deprecated since catalog 20.100.0
	 * @see \Bitrix\Catalog\Model\Product::clearCacheItem()
	 *
	 * @param int $product			Product id or zero (clear all cache).
	 * @return void
	 */
	public static function clearProductCache($product = 0) {}

	/**
	 * Returns ratio and measure for products.
	 *
	 * @param array|int $product				Product ids.
	 * @return array|bool
	 * @throws Main\ArgumentException
	 */
	public static function getCurrentRatioWithMeasure($product)
	{
		if (!is_array($product))
			$product = array($product);
		Main\Type\Collection::normalizeArrayValuesByInt($product, true);
		if (empty($product))
			return false;

		$result = array();

		$defaultMeasure = \CCatalogMeasure::getDefaultMeasure(true, true);
		$defaultRow = array(
			'RATIO' => 1,
			'MEASURE' => (!empty($defaultMeasure) ? $defaultMeasure : array())
		);
		$existProduct = array();
		$measureMap = array();

		$productRows = array_chunk($product, 500);
		foreach ($productRows as &$row)
		{
			$productIterator = self::getList(array(
				'select' => array('ID', 'MEASURE'),
				'filter' => array('@ID' => $row),
				'order' => array('ID' => 'ASC')
			));
			while ($item = $productIterator->fetch())
			{
				$item['ID'] = (int)$item['ID'];
				$item['MEASURE'] = (int)$item['MEASURE'];
				$existProduct[] = $item['ID'];
				$result[$item['ID']] = $defaultRow;
				if ($item['MEASURE'] > 0)
				{
					if (!isset($measureMap[$item['MEASURE']]))
						$measureMap[$item['MEASURE']] = array();
					$measureMap[$item['MEASURE']][] = &$result[$item['ID']];
				}
			}
			unset($item, $productIterator);
		}
		unset($row, $productRows);
		unset($defaultRow, $defaultMeasure);
		if (empty($existProduct))
			return false;

		$ratioResult = MeasureRatioTable::getCurrentRatio($existProduct);
		if (!empty($ratioResult))
		{
			foreach ($ratioResult as $ratioProduct => $ratio)
				$result[$ratioProduct]['RATIO'] = $ratio;
			unset($ratio, $ratioProduct);
		}
		unset($ratioResult);
		unset($existProduct);

		if (!empty($measureMap))
		{
			$measureIterator = \CCatalogMeasure::getList(
				array(),
				array('@ID' => array_keys($measureMap)),
				false,
				false,
				array()
			);
			while ($measure = $measureIterator->getNext())
			{
				$measure['ID'] = (int)$measure['ID'];
				if (empty($measureMap[$measure['ID']]))
					continue;

				foreach ($measureMap[$measure['ID']] as &$product)
					$product['MEASURE'] = $measure;
				unset($product);
			}
			unset($measure, $measureIterator);
		}
		unset($measureMap);

		return $result;
	}

	/**
	 * Calculate available for product.
	 *
	 * @param array $fields					Product data.
	 * @return string
	 */
	public static function calculateAvailable($fields)
	{
		if (empty($fields) || !is_array($fields))
		{
			return self::STATUS_NO;
		}

		if (isset($fields['QUANTITY']) && isset($fields['QUANTITY_TRACE']) && isset($fields['CAN_BUY_ZERO']))
		{
			if (empty(self::$defaultProductSettings))
			{
				self::loadDefaultProductSettings();
			}
			if ($fields['QUANTITY_TRACE'] == self::STATUS_DEFAULT)
			{
				$fields['QUANTITY_TRACE'] = self::$defaultProductSettings['QUANTITY_TRACE'];
			}
			if ($fields['CAN_BUY_ZERO'] == self::STATUS_DEFAULT)
			{
				$fields['CAN_BUY_ZERO'] = self::$defaultProductSettings['CAN_BUY_ZERO'];
			}

			return (
				(
					(float)$fields['QUANTITY'] <= 0
					&& $fields['QUANTITY_TRACE'] == self::STATUS_YES
					&& $fields['CAN_BUY_ZERO'] == self::STATUS_NO
				)
				? self::STATUS_NO
				: self::STATUS_YES
			);
		}

		return self::STATUS_NO;
	}

	/**
	 * Returns true if need check maximum product quantity.
	 *
	 * @param array $fields Product data.
	 * @return bool
	 */
	public static function isNeedCheckQuantity(array $fields): bool
	{
		if (isset($fields['QUANTITY_TRACE']) && isset($fields['CAN_BUY_ZERO']))
		{
			if (empty(self::$defaultProductSettings))
			{
				self::loadDefaultProductSettings();
			}
			if ($fields['QUANTITY_TRACE'] == self::STATUS_DEFAULT)
			{
				$fields['QUANTITY_TRACE'] = self::$defaultProductSettings['QUANTITY_TRACE'];
			}
			if ($fields['CAN_BUY_ZERO'] == self::STATUS_DEFAULT)
			{
				$fields['CAN_BUY_ZERO'] = self::$defaultProductSettings['CAN_BUY_ZERO'];
			}

			return ($fields['QUANTITY_TRACE'] === self::STATUS_YES && $fields['CAN_BUY_ZERO'] === self::STATUS_NO);
		}

		return false;
	}

	/**
	 * Load default product settings from module options.
	 *
	 * @internal
	 * @return void
	 */
	public static function loadDefaultProductSettings()
	{
		self::$defaultProductSettings = [
			'QUANTITY_TRACE' => (Main\Config\Option::get('catalog', 'default_quantity_trace') === 'Y' ? 'Y' : 'N'),
			'CAN_BUY_ZERO' => (Main\Config\Option::get('catalog', 'default_can_buy_zero') === 'Y' ? 'Y' : 'N'),
			'NEGATIVE_AMOUNT_TRACE' => (Main\Config\Option::get('catalog', 'allow_negative_amount') === 'Y' ? 'Y' : 'N'),
			'SUBSCRIBE' => (Main\Config\Option::get('catalog', 'default_subscribe') === 'N' ? 'N' : 'Y'),
		];
	}

	/**
	 * Return product type list.
	 *
	 * @param bool $descr			With description.
	 * @return array
	 */
	public static function getProductTypes($descr = false)
	{
		if ($descr)
		{
			return [
				self::TYPE_PRODUCT => Loc::getMessage('PRODUCT_ENTITY_TYPE_PRODUCT'),
				self::TYPE_SET => Loc::getMessage('PRODUCT_ENTITY_TYPE_SET_MSGVER_1'),
				self::TYPE_SKU => Loc::getMessage('PRODUCT_ENTITY_TYPE_SKU'),
				self::TYPE_EMPTY_SKU => Loc::getMessage('PRODUCT_ENTITY_TYPE_EMPTY_SKU'),
				self::TYPE_OFFER => Loc::getMessage('PRODUCT_ENTITY_TYPE_OFFER'),
				self::TYPE_FREE_OFFER => Loc::getMessage('PRODUCT_ENTITY_TYPE_FREE_OFFER'),
				self::TYPE_SERVICE => Loc::getMessage('PRODUCT_ENTITY_TYPE_SERVICE'),
			];
		}
		return [
			self::TYPE_PRODUCT,
			self::TYPE_SET,
			self::TYPE_SKU,
			self::TYPE_OFFER,
			self::TYPE_FREE_OFFER,
			self::TYPE_EMPTY_SKU,
			self::TYPE_SERVICE,
		];
	}

	/**
	 * Return payment type list.
	 *
	 * @param bool $descr			With description.
	 * @return array
	 */
	public static function getPaymentTypes($descr = false)
	{
		if ($descr)
		{
			return array(
				self::PAYMENT_TYPE_SINGLE => Loc::getMessage('PRODUCT_ENTITY_PAYMENT_TYPE_SINGLE'),
				self::PAYMENT_TYPE_REGULAR => Loc::getMessage('PRODUCT_ENTITY_PAYMENT_TYPE_REGULAR'),
				self::PAYMENT_TYPE_TRIAL => Loc::getMessage('PRODUCT_ENTITY_PAYMENT_TYPE_TRIAL')
			);
		}
		return array(
			self::PAYMENT_TYPE_SINGLE,
			self::PAYMENT_TYPE_REGULAR,
			self::PAYMENT_TYPE_TRIAL
		);
	}

	/**
	 * Return payment period list.
	 *
	 * @param bool $descr			With description.
	 * @return array
	 */
	public static function getPaymentPeriods($descr = false)
	{
		if ($descr)
		{
			return [
				self::PAYMENT_PERIOD_HOUR => Loc::getMessage('PRODUCT_ENTITY_PAYMENT_PERIOD_HOUR'),
				self::PAYMENT_PERIOD_DAY => Loc::getMessage('PRODUCT_ENTITY_PAYMENT_PERIOD_DAY'),
				self::PAYMENT_PERIOD_WEEK => Loc::getMessage('PRODUCT_ENTITY_PAYMENT_PERIOD_WEEK'),
				self::PAYMENT_PERIOD_MONTH => Loc::getMessage('PRODUCT_ENTITY_PAYMENT_PERIOD_MONTH'),
				self::PAYMENT_PERIOD_QUART => Loc::getMessage('PRODUCT_ENTITY_PAYMENT_PERIOD_QUART'),
				self::PAYMENT_PERIOD_SEMIYEAR => Loc::getMessage('PRODUCT_ENTITY_PAYMENT_PERIOD_SEMIYEAR'),
				self::PAYMENT_PERIOD_YEAR => Loc::getMessage('PRODUCT_ENTITY_PAYMENT_PERIOD_YEAR'),
			];
		}
		return [
			self::PAYMENT_PERIOD_HOUR,
			self::PAYMENT_PERIOD_DAY,
			self::PAYMENT_PERIOD_WEEK,
			self::PAYMENT_PERIOD_MONTH,
			self::PAYMENT_PERIOD_QUART,
			self::PAYMENT_PERIOD_SEMIYEAR,
			self::PAYMENT_PERIOD_YEAR,
		];
	}

	/**
	 * Return default alailable settings.
	 *
	 * @return array
	 */
	public static function getDefaultAvailableSettings()
	{
		return [
			'AVAILABLE' => self::STATUS_NO,
			'QUANTITY' => 0,
			'QUANTITY_TRACE' => self::STATUS_YES,
			'CAN_BUY_ZERO' => self::STATUS_NO,
		];
	}

	public static function getTradingEntityNameByType(int $type): ?string
	{
		switch ($type)
		{
			case self::TYPE_PRODUCT:
			case self::TYPE_SET:
			case self::TYPE_SKU:
			case self::TYPE_OFFER:
				$result = Loc::getMessage('PRODUCT_ENTITY_ENTITY_NAME_ANY_PRODUCT');
				break;
			case self::TYPE_SERVICE:
				$result = Loc::getMessage('PRODUCT_ENTITY_ENTITY_NAME_ANY_SERVICE');
				break;
			case self::TYPE_FREE_OFFER:
			case self::TYPE_EMPTY_SKU:
				$result = Loc::getMessage('PRODUCT_ENTITY_ENTITY_NAME_ANY_RESTLESS');
				break;
			default:
				$result = null;
		}

		return $result;
	}

	/**
	 * Returns products types that are not supported and/or relevant in inventory management
	 *
	 * @return array|int[]
	 */
	public static function getStoreDocumentRestrictedProductTypes(): array
	{
		/*
		 * do not add product type self::TYPE_SKU because the selection for offers is based on head products
		 */
		return [
			self::TYPE_SET,
			self::TYPE_SERVICE,
			self::TYPE_EMPTY_SKU,
		];
	}
}
