<?php
namespace Bitrix\Catalog\Model;

use Bitrix\Main,
	Bitrix\Main\ORM,
	Bitrix\Main\Localization\Loc,
	Bitrix\Catalog,
	Bitrix\Currency;

Loc::loadMessages(__FILE__);

class Price extends Entity
{
	private static $separateSkuMode = null;

	private static $productPrices = array();

	private static $basePriceType = null;

	private static $priceTypes = null;

	private static $extraList = null;

	public static function getTabletClassName()
	{
		return '\Bitrix\Catalog\PriceTable';
	}

	public static function recountPricesFromBase($id)
	{
		$id = (int)$id;
		if ($id <= 0)
			return false;

		if (self::$extraList === null)
			self::loadSettings();

		if (empty(self::$extraList) || self::$basePriceType == 0)
			return false;

		if (self::$separateSkuMode === null)
			self::$separateSkuMode = (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';

		$iterator = Catalog\PriceTable::getList(array(
			'select' => array('ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY', 'QUANTITY_FROM', 'QUANTITY_TO'),
			'filter' => array('=ID' => $id)
		));
		$price = $iterator->fetch();
		unset($iterator);
		if (empty($price))
			return false;

		$price['CATALOG_GROUP_ID'] = (int)$price['CATALOG_GROUP_ID'];
		if ($price['CATALOG_GROUP_ID'] != self::$basePriceType)
			return false;

		$productId = (int)$price['PRODUCT_ID'];
		$product = Product::getCacheItem($productId, true);
		if (empty($product))
			return false;

		if (
			!self::$separateSkuMode
			&& ($product['TYPE'] == Catalog\ProductTable::TYPE_SKU || $product['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU)
		)
			return false;

		//TODO: replace CCurrency::GetByID to d7 cached method
		$currency = \CCurrency::GetByID($price['CURRENCY']);
		if (empty($currency))
			return false;

		$filter = ORM\Query\Query::filter();
		$filter->where('PRODUCT_ID', '=', $productId);
		$filter->where('CATALOG_GROUP_ID', '!=', self::$basePriceType);
		$filter->whereIn('EXTRA_ID', array_keys(self::$extraList));
		if ($price['QUANTITY_FROM'] === null)
			$filter->whereNull('QUANTITY_FROM');
		else
			$filter->where('QUANTITY_FROM', '=', (int)$price['QUANTITY_FROM']);

		if ($price['QUANTITY_TO'] === null)
			$filter->whereNull('QUANTITY_TO');
		else
			$filter->where('QUANTITY_TO', '=', (int)$price['QUANTITY_TO']);

		$datetime = new Main\Type\DateTime();
		$updatePriceTypes = array();
		$iterator = Catalog\PriceTable::getList(array(
			'select' => array('ID', 'EXTRA_ID', 'CATALOG_GROUP_ID', 'QUANTITY_FROM', 'QUANTITY_TO'),
			'filter' => $filter
		));
		while ($row = $iterator->fetch())
		{
			$fields = array(
				'PRICE' => $price['PRICE']*self::$extraList[$row['EXTRA_ID']],
				'CURRENCY' => $price['CURRENCY'],
				'TIMESTAMP_X' => $datetime
			);
			$fields['PRICE_SCALE'] = $fields['PRICE']*$currency['CURRENT_BASE_RATE'];

			$result = Catalog\PriceTable::update($row['ID'], $fields);
			if ($result->isSuccess())
				$updatePriceTypes[$row['CATALOG_TYPE_ID']] = $row['CATALOG_TYPE_ID'];
		}
		unset($result, $fields, $currency, $index);
		unset($row, $iterator);

		if (!empty($updatePriceTypes) && $product['TYPE'] == Catalog\ProductTable::TYPE_OFFER)
			Catalog\Product\Sku::calculatePrice($productId, null, Catalog\ProductTable::TYPE_OFFER, $updatePriceTypes);

		return true;
	}

	protected static function prepareForAdd(ORM\Data\AddResult $result, $id, array &$data)
	{
		$fields = $data['fields'];
		parent::prepareForAdd($result, $id, $fields);
		if (!$result->isSuccess())
			return;

		if (self::$separateSkuMode === null)
			self::$separateSkuMode = (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';

		static $defaultValues = null,
			$blackList = null;

		if ($defaultValues === null)
		{
			$defaultValues = array(
				'PRODUCT_ID' => 0,
				'CATALOG_GROUP_ID' => 0,
				'EXTRA_ID' => null,
				'PRICE' => null,
				'CURRENCY' => null,
				'QUANTITY_FROM' => null,
				'QUANTITY_TO' => null,
				'TMP_ID' => null
			);

			$blackList = array(
				'ID' => true
			);
		}
		if (self::$extraList === null)
			self::loadSettings();

		$fields = array_merge($defaultValues, array_diff_key($fields, $blackList));

		$fields['PRODUCT_ID'] = (int)$fields['PRODUCT_ID'];
		if ($fields['PRODUCT_ID'] <= 0)
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_PRODUCT_ID')
			));
			return;
		}
		$fields['CATALOG_GROUP_ID'] = (int)$fields['CATALOG_GROUP_ID'];
		if (!isset(self::$priceTypes[$fields['CATALOG_GROUP_ID']]))
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_CATALOG_GROUP_ID')
			));
			return;
		}
		else
		{
			if ($fields['CATALOG_GROUP_ID'] == self::$basePriceType)
			{
				$fields['EXTRA_ID'] = null;
				if (isset($data['actions']['OLD_RECOUNT']))
				{
					if ($data['actions']['OLD_RECOUNT'] === true)
						$data['actions']['PARENT_PRICE'] = true;
				}
			}
		}

		if ($fields['TMP_ID'] !== null)
			$fields['TMP_ID'] = mb_substr($fields['TMP_ID'], 0, 40);

		static::checkQuantityRange($result, $fields);

		if ($fields['EXTRA_ID'] !== null)
		{
			$fields['EXTRA_ID'] = (int)$fields['EXTRA_ID'];
			if (!isset(self::$extraList[$fields['EXTRA_ID']]))
			{
				unset($fields['EXTRA_ID']);
			}
			else
			{
				if (
					(!isset($fields['PRICE']) && !isset($fields['CURRENCY']))
					|| (isset($data['actions']['OLD_RECOUNT']) && $data['actions']['OLD_RECOUNT'] === true)
				)
					self::calculatePriceFromBase(null, $fields);
			}
		}

		if ($fields['PRICE'] === null)
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_PRICE')
			));
		}
		else
		{
			$fields['PRICE'] = self::checkPriceValue($fields['PRICE']);
			if ($fields['PRICE'] === null || $fields['PRICE'] < 0)
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_PRICE')
				));
			}
		}
		$fields['CURRENCY'] = (string)$fields['CURRENCY'];
		if (!Currency\CurrencyManager::isCurrencyExist($fields['CURRENCY']))
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_CURRENCY')
			));
		}

		if ($result->isSuccess())
		{
			$fields['TIMESTAMP_X'] = new Main\Type\DateTime();
			if (isset($fields['PRICE_SCALE']))
				$fields['PRICE_SCALE'] = self::checkPriceValue($fields['PRICE_SCALE']);
			// self::checkPriceValue return float or null
			if (!isset($fields['PRICE_SCALE']))
			{
				//TODO: replace CCurrency::GetByID to d7 cached method
				$currency = \CCurrency::GetByID($fields['CURRENCY']);
				$fields['PRICE_SCALE'] = $fields['PRICE']*$currency['CURRENT_BASE_RATE'];
				unset($currency);
			}

			if (isset($data['actions']['PARENT_PRICE']))
				unset($data['actions']['PARENT_PRICE']);
			if (!self::$separateSkuMode)
			{
				$product = Product::getCacheItem($fields['PRODUCT_ID'], true);
				if (!empty($product) && $product['TYPE'] == Catalog\ProductTable::TYPE_OFFER)
					$data['actions']['PARENT_PRICE'] = true;
				unset($product);
			}
			if (isset($data['actions']['RECOUNT_PRICES']))
			{
				if ($fields['CATALOG_GROUP_ID'] != self::$basePriceType)
					unset($data['actions']['RECOUNT_PRICES']);
				else
					$data['actions']['RECOUNT_PRICES'] = true;
			}

			$data['fields'] = $fields;
		}

		unset($fields);
	}

	protected static function prepareForUpdate(ORM\Data\UpdateResult $result, $id, array &$data)
	{
		$id = (int)$id;
		if ($id <= 0)
		{
			$result->addError(new ORM\EntityError(
				Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_PRICE_ID')
			));
			return;
		}

		if (self::$separateSkuMode === null)
			self::$separateSkuMode = (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';

		$fields = $data['fields'];
		parent::prepareForUpdate($result, $id, $fields);
		if (!$result->isSuccess())
			return;

		$blackList = array(
			'ID' => true
		);

		if (self::$extraList === null)
			self::loadSettings();

		$fields = array_diff_key($fields, $blackList);

		if (array_key_exists('PRODUCT_ID', $fields))
		{
			$fields['PRODUCT_ID'] = (int)$fields['PRODUCT_ID'];
			if ($fields['PRODUCT_ID'] <= 0)
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_PRODUCT_ID')
				));
		}

		if (array_key_exists('CATALOG_GROUP_ID', $fields))
		{
			$fields['CATALOG_GROUP_ID'] = (int)$fields['CATALOG_GROUP_ID'];
			if (!isset(self::$priceTypes[$fields['CATALOG_GROUP_ID']]))
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_CATALOG_GROUP_ID')
				));
			}
			else
			{
				if ($fields['CATALOG_GROUP_ID'] == self::$basePriceType)
				{
					$fields['EXTRA_ID'] = null;
					if (isset($data['actions']['OLD_RECOUNT']))
					{
						if ($data['actions']['OLD_RECOUNT'] === true)
							$data['actions']['PARENT_PRICE'] = true;
					}
				}
			}
		}

		if (isset($fields['TMP_ID']))
			$fields['TMP_ID'] = mb_substr($fields['TMP_ID'], 0, 40);

		$existQuantityFrom = array_key_exists('QUANTITY_FROM', $fields);
		$existQuantityTo = array_key_exists('QUANTITY_TO', $fields);
		if ($existQuantityFrom != $existQuantityTo)
		{
			if ($existQuantityFrom)
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_QUANTITY_RANGE_LEFT_BORDER_ONLY')
				));
			else
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_QUANTITY_RANGE_RIGHT_BORDER_ONLY')
				));
		}
		else
		{
			if ($existQuantityFrom)
				static::checkQuantityRange($result, $fields);
		}
		unset($existQuantityTo, $existQuantityFrom);

		if (isset($fields['EXTRA_ID']))
		{
			$fields['EXTRA_ID'] = (int)$fields['EXTRA_ID'];
			if (!isset(self::$extraList[$fields['EXTRA_ID']]))
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_EXTRA_ID')
				));
			}
			else
			{
				if (
					(!isset($fields['PRICE']) && !isset($fields['CURRENCY']))
					|| (isset($data['actions']['OLD_RECOUNT']) && $data['actions']['OLD_RECOUNT'] === true)
				)
					self::calculatePriceFromBase($id, $fields);
			}
		}

		if (array_key_exists('PRICE', $fields))
		{
			$fields['PRICE'] = self::checkPriceValue($fields['PRICE']);
			if ($fields['PRICE'] === null || $fields['PRICE'] < 0)
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_PRICE')
				));
			}
		}

		if (array_key_exists('CURRENCY', $fields))
		{
			$fields['CURRENCY'] = (string)$fields['CURRENCY'];
			if (!Currency\CurrencyManager::isCurrencyExist($fields['CURRENCY']))
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_CURRENCY')
				));
			}
		}

		if ($result->isSuccess())
		{
			if (isset($data['actions']['PARENT_PRICE']))
				unset($data['actions']['PARENT_PRICE']);

			$priceScale = !isset($fields['PRICE_SCALE']) && (isset($fields['PRICE']) || isset($fields['CURRENCY']));
			$needCalculatePrice = (
				!self::$separateSkuMode
				&& (
					isset($fields['PRODUCT_ID'])
					|| isset($fields['CATALOG_GROUP_ID'])
					|| isset($fields['PRICE'])
					|| isset($fields['CURRENCY'])
				)
			);
			$recountPrices = isset($data['actions']['RECOUNT_PRICES']);

			$copyFields = array();
			if ($priceScale || $needCalculatePrice || $recountPrices)
				$copyFields = array_merge(static::getCacheItem($id, true), $fields);

			if (isset($fields['PRICE_SCALE']))
			{
				$fields['PRICE_SCALE'] = self::checkPriceValue($fields['PRICE_SCALE']);
				if ($fields['PRICE_SCALE'] === null || $fields['PRICE_SCALE'] < 0)
					unset($fields['PRICE_SCALE']);
			}
			if (!isset($fields['PRICE_SCALE']))
			{
				if (isset($fields['PRICE']) || isset($fields['CURRENCY']))
				{
					//TODO: replace CCurrency::GetByID to d7 cached method
					$currency = \CCurrency::GetByID($copyFields['CURRENCY']);
					$fields['PRICE_SCALE'] = $copyFields['PRICE']*$currency['CURRENT_BASE_RATE'];
					unset($currency);
				}
			}
			if ($needCalculatePrice)
			{
				$product = Product::getCacheItem($copyFields['PRODUCT_ID'], true);
				if (!empty($product) && $product['TYPE'] == Catalog\ProductTable::TYPE_OFFER)
					$data['actions']['PARENT_PRICE'] = true;
				unset($product);
			}
			if (isset($data['actions']['RECOUNT_PRICES']))
			{
				if ($copyFields['CATALOG_GROUP_ID'] != self::$basePriceType)
					unset($data['actions']['RECOUNT_PRICES']);
				else
					$data['actions']['RECOUNT_PRICES'] = true;
			}

			unset($copyFields);

			$data['fields'] = $fields;
		}
		unset($fields);
	}

	protected static function runAddExternalActions($id, array $data)
	{
		if (isset($data['actions']['RECOUNT_PRICES']))
		{
			self::recountPricesFromBase($id);
		}
		if (isset($data['actions']['PARENT_PRICE']))
		{
			Catalog\Product\Sku::calculatePrice(
				$data['fields']['PRODUCT_ID'],
				null,
				Catalog\ProductTable::TYPE_OFFER,
				array($data['fields']['CATALOG_GROUP_ID'])
			);
		}
	}

	protected static function runUpdateExternalActions($id, array $data)
	{
		$price = self::getCacheItem($id);
		if (isset($data['actions']['RECOUNT_PRICES']))
		{
			self::recountPricesFromBase($id);
		}
		if (isset($data['actions']['PARENT_PRICE']))
		{
			$priceTypes = array($price['CATALOG_GROUP_ID']);
			if (
				isset($price[self::PREFIX_OLD.'CATALOG_GROUP_ID'])
				&& $price[self::PREFIX_OLD.'CATALOG_GROUP_ID'] != $price['CATALOG_GROUP_ID']
			)
				$priceTypes[] = $price[self::PREFIX_OLD.'CATALOG_GROUP_ID'];
			Catalog\Product\Sku::calculatePrice(
				$price['PRODUCT_ID'], null, Catalog\ProductTable::TYPE_OFFER, $priceTypes);
			if (
				isset($price[self::PREFIX_OLD.'PRODUCT_ID'])
				&& $price[self::PREFIX_OLD.'PRODUCT_ID'] != $price['PRODUCT_ID']
			)
				Catalog\Product\Sku::calculatePrice($price[self::PREFIX_OLD.'PRODUCT_ID'], null, null, $priceTypes);
			unset($priceTypes);
		}
		unset($price);
	}

	protected static function runDeleteExternalActions($id)
	{
		$price = self::getCacheItem($id);
		$product = Product::getCacheItem($price[self::PREFIX_OLD.'PRODUCT_ID']);
		if (!empty($product) && $product['TYPE'] == Catalog\ProductTable::TYPE_OFFER)
		{
			Catalog\Product\Sku::calculatePrice(
				$price[self::PREFIX_OLD.'PRODUCT_ID'],
				null,
				Catalog\ProductTable::TYPE_OFFER,
				array($price[self::PREFIX_OLD.'CATALOG_GROUP_ID'])
			);
		}
		unset($product, $price);
	}

	protected static function getDefaultCachedFieldList()
	{
		return [
			'ID',
			'PRODUCT_ID',
			'CATALOG_GROUP_ID',
			'PRICE',
			'CURRENCY'
		];
	}

	/**
	 * Check and correct quantity range.
	 * @internal
	 *
	 * @param ORM\Data\Result $result        for errors.
	 * @param array &$fields                    price data.
	 * @return void
	 */
	private static function checkQuantityRange(ORM\Data\Result $result, array &$fields)
	{
		if ($fields['QUANTITY_FROM'] !== null)
		{
			$fields['QUANTITY_FROM'] = (int)$fields['QUANTITY_FROM'];
			if ($fields['QUANTITY_FROM'] <= 0)
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_QUANTITY_FROM')
				));
		}
		if ($fields['QUANTITY_TO'] != null)
		{
			$fields['QUANTITY_TO'] = (int)$fields['QUANTITY_TO'];
			if ($fields['QUANTITY_TO'] <= 0)
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_QUANTITY_TO')
				));
		}
		if ($fields['QUANTITY_FROM'] !== null && $fields['QUANTITY_TO'] != null)
		{
			if ($fields['QUANTITY_FROM'] == 0 && $fields['QUANTITY_TO'] == 0)
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage('BX_CATALOG_MODEL_PRICE_ERR_WRONG_QUANTITY_RANGE_ZERO')
				));
			}
			elseif ($fields['QUANTITY_FROM'] > $fields['QUANTITY_TO'])
			{
				$result->addError(new ORM\EntityError(
					Loc::getMessage(
						'BX_CATALOG_MODEL_PRICE_ERR_WRONG_QUANTITY_RANGE_INVERT',
						array('#LEFT#' => $fields['QUANTITY_FROM'], '#RIGHT#' => $fields['QUANTITY_TO'])
					)
				));
			}
		}
	}

	/**
	 * Check price value.
	 * @internal
	 *
	 * @param string|int|float|null $price      Price value.
	 * @return float|int|null
	 */
	private static function checkPriceValue($price)
	{
		$result = null;

		if ($price !== null)
		{
			if (is_string($price))
			{
				if ($price !== '' && is_numeric($price))
				{
					$price = (float)$price;
					if (is_finite($price))
						$result = $price;
				}
			}
			elseif (
				is_int($price)
				|| (is_float($price) && is_finite($price))
			)
			{
				$result = $price;
			}
		}

		return $result;
	}

	private static function loadSettings()
	{
		self::$extraList = array();
		//TODO: remove after create \Bitrix\Catalog\Model\Extra
		$iterator = Catalog\ExtraTable::getList(array(
			'select' => array('ID', 'PERCENTAGE'),
			'order' => array('ID' => 'ASC'),
			'cache' => array('ttl' => 3600)
		));
		while ($row = $iterator->fetch())
			self::$extraList[$row['ID']] = (100 + (float)$row['PERCENTAGE']) / 100;
		unset($row, $iterator);
		//TODO: replace to d7 api
		$baseType = \CCatalogGroup::GetBaseGroup();
		self::$basePriceType = (!empty($baseType) ? (int)$baseType['ID'] : 0);
		unset($baseType);
		//TODO: replace to d7 api
		self::$priceTypes = \CCatalogGroup::GetListArray();
	}

	private static function calculatePriceFromBase($id, array &$fields)
	{
		$correct = false;
		$copyFields = $fields;
		if (
			!isset($fields['PRODUCT_ID'])
			|| !isset($fields['CATALOG_GROUP_ID'])
			|| !array_key_exists('QUANTITY_FROM', $fields)
			|| !array_key_exists('QUANTITY_TO', $fields)
		)
		{
			if ($id !== null)
			{
				$data = self::getList(array(
					'select' => array('ID', 'PRODUCT_ID', 'CATALOG_GROUP_ID', 'QUANTITY_FROM', 'QUANTITY_TO'),
					'filter' => array('=ID' => $id)
				))->fetch();
				if (!empty($data))
				{
					$copyFields = array_merge($data, $copyFields);
					$correct = true;
				}
				unset($data);
			}
		}
		else
		{
			$correct = true;
		}

		if (!$correct)
			return;

		$productId = $copyFields['PRODUCT_ID'];

		if (!isset(self::$productPrices[$productId]))
			self::loadProductBasePrices($productId);

		$index = self::getPriceIndex($copyFields);
		if (!isset(self::$productPrices[$productId][$index]))
			return;

		$fields['PRICE'] = self::$productPrices[$productId][$index]['PRICE']*self::$extraList[$copyFields['EXTRA_ID']];
		$fields['CURRENCY'] = self::$productPrices[$productId][$index]['CURRENCY'];
	}

	private static function getPriceIndex(array $row)
	{
		return ($row['QUANTITY_FROM'] === null ? 'ZERO' : $row['QUANTITY_FROM']).
			'-'.($row['QUANTITY_TO'] === null ? 'INF' : $row['QUANTITY_TO']);
	}

	private static function loadProductBasePrices($productId)
	{
		self::$productPrices = array(
			$productId => array()
		);
		$iterator = Catalog\PriceTable::getList(array(
			'select' => array('ID', 'PRICE', 'CURRENCY', 'QUANTITY_FROM', 'QUANTITY_TO'),
			'filter' => array('=PRODUCT_ID' => $productId, '=CATALOG_GROUP_ID' => self::$basePriceType)
		));
		while ($row = $iterator->fetch())
			self::$productPrices[$productId][self::getPriceIndex($row)] = $row;
		unset($row, $iterator);
	}
}