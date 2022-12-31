<?php
namespace Bitrix\Catalog\Product;

use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Iblock;

/**
 * Class Sku
 * Provides various useful methods for sku data.
 *
 * @package Bitrix\Catalog\Product
 */
class Sku
{
	public const OFFERS_ERROR = 0x0000;
	public const OFFERS_NOT_EXIST = 0x0001;
	public const OFFERS_NOT_AVAILABLE = 0x0002;
	public const OFFERS_AVAILABLE = 0x0004;

	protected const ACTION_AVAILABLE = 'AVAILABLE';
	protected const ACTION_PRICE = 'PRICE';
	protected const ACTION_ELEMENT_TIMESTAMP = 'ELEMENT_TIMESTAMP';

	protected static int $allowUpdateAvailable = 0;
	protected static int $allowPropertyHandler = 0;

	protected static array $productIds = [];
	protected static array $offers = [];
	private static array $changeActive = [];
	private static array $currentActive = [];

	private static ?bool $separateSkuMode = null;

	private static int $deferredCalculation = -1;

	private static bool $calculateAvailable = false;
	private static array $calculatePriceTypes = [];

	private static array $deferredSku = [];
	private static array $deferredOffers = [];
	private static array $deferredUnknown = [];

	private static array $skuExist = [];
	private static array $skuAvailable = [];
	private static array $offersIds = [];
	private static array $offersMap = [];
	private static array $skuPrices = [];

	private static ?string $queryElementTimestamp = null;

	/**
	 * Enable automatic update parent product available and prices.
	 *
	 * @return void
	 */
	public static function enableUpdateAvailable()
	{
		self::$allowUpdateAvailable++;
	}

	/**
	 * Disable automatic update parent product available and prices.
	 *
	 * @return void
	 */
	public static function disableUpdateAvailable()
	{
		self::$allowUpdateAvailable--;
	}

	/**
	 * Return true if allowed automatic update parent product available and prices.
	 *
	 * @return bool
	 */
	public static function allowedUpdateAvailable(): bool
	{
		return (self::$allowUpdateAvailable >= 0);
	}

	/**
	 * Enable deferred calculation parent product available and prices.
	 *
	 * @return void
	 */
	public static function enableDeferredCalculation()
	{
		self::$deferredCalculation++;
	}

	/**
	 * Disable deferred calculation parent product available and prices.
	 *
	 * @return void
	 */
	public static function disableDeferredCalculation()
	{
		self::$deferredCalculation--;
	}

	/**
	 * Return true if allowed deferred calculation parent product available and prices.
	 *
	 * @return bool
	 */
	public static function usedDeferredCalculation(): bool
	{
		return (self::$deferredCalculation >= 0);
	}

	/**
	 * Returns base product fields for product with sku.
	 *
	 * @param int $offerState Offers state (exists, available, etc).
	 * @return array
	 */
	public static function getParentProductFieldsByState(int $offerState): array
	{
		return self::getDefaultParentSettings($offerState, true);
	}

	/**
	 * Return default settings for product with sku.
	 *
	 * @param int $state State flag.
	 * @param bool $productIblock Is iblock (no catalog) with offers.
	 * @return array
	 */
	public static function getDefaultParentSettings(int $state, bool $productIblock = false): array
	{
		switch ($state)
		{
			case self::OFFERS_NOT_EXIST:
				$result = [
					'TYPE' => $productIblock
						? Catalog\ProductTable::TYPE_EMPTY_SKU
						: Catalog\ProductTable::TYPE_PRODUCT
					,
					'AVAILABLE' => Catalog\ProductTable::STATUS_NO,
					'QUANTITY' => 0,
					'QUANTITY_TRACE' => Catalog\ProductTable::STATUS_YES,
					'CAN_BUY_ZERO' => Catalog\ProductTable::STATUS_NO
				];
				break;
			case self::OFFERS_NOT_AVAILABLE:
				$result = [
					'TYPE' => Catalog\ProductTable::TYPE_SKU,
					'AVAILABLE' => Catalog\ProductTable::STATUS_NO,
					'QUANTITY' => 0,
					'QUANTITY_TRACE' => Catalog\ProductTable::STATUS_YES,
					'CAN_BUY_ZERO' => Catalog\ProductTable::STATUS_NO
				];
				break;
			case self::OFFERS_AVAILABLE:
				$result = [
					'TYPE' => Catalog\ProductTable::TYPE_SKU,
					'AVAILABLE' => Catalog\ProductTable::STATUS_YES,
					'QUANTITY' => 0,
					'QUANTITY_TRACE' => Catalog\ProductTable::STATUS_NO,
					'CAN_BUY_ZERO' => Catalog\ProductTable::STATUS_YES,
				];
				break;
			default:
				$result = [];
				break;
		}

		return $result;
	}

	/**
	 * Update product available.
	 *
	 * @deprecated deprecated since catalog 17.6.0
	 * @see Sku::calculateComplete (for sku) and Catalog\Model\Product::update (for all product types)
	 *
	 * @param int $productId			Product Id.
	 * @param int $iblockId				Iblock Id (optional).
	 * @param array $productFields		Product fields (optional).
	 * @return bool
	 * @throws \Bitrix\Main\ArgumentException
	 * @throws \Exception
	 */
	public static function updateAvailable($productId, $iblockId = 0, array $productFields = array())
	{
		if (!self::allowedUpdateAvailable())
			return true;
		static::disableUpdateAvailable();

		$result = true;
		$process = true;
		$iblockData = false;
		$fields = array();
		$parentId = 0;
		$parentIblockId = 0;

		$productId = (int)$productId;
		if ($productId <= 0)
		{
			$process = false;
			$result = false;
		}
		if ($process)
		{
			$iblockId = (int)$iblockId;
			if ($iblockId <= 0)
				$iblockId = (int)\CIBlockElement::GetIBlockByID($productId);
			if ($iblockId <= 0)
			{
				$process = false;
				$result = false;
			}
		}

		if ($process)
		{
			$iblockData = \CCatalogSku::GetInfoByIBlock($iblockId);
			if (empty($iblockData))
			{
				$process = false;
				$result = false;
			}
		}

		if ($process)
		{
			switch ($iblockData['CATALOG_TYPE'])
			{
				case \CCatalogSku::TYPE_PRODUCT:
					$fields = (self::isSeparateSkuMode()
						? static::getParentDataAsProduct($productId, $productFields)
						: static::getParentProductFieldsByState(static::getOfferState($productId, $iblockId))
					);
					break;
				case \CCatalogSku::TYPE_FULL:
					$offerState = static::getOfferState($productId, $iblockId);
					if ($offerState !== self::OFFERS_ERROR)
					{
						switch ($offerState)
						{
							case self::OFFERS_AVAILABLE:
							case self::OFFERS_NOT_AVAILABLE:
								$fields = (self::isSeparateSkuMode()
									? static::getParentDataAsProduct($productId, $productFields)
									: static::getParentProductFieldsByState($offerState)
								);
								break;
							case self::OFFERS_NOT_EXIST:
								$product = Catalog\Model\Product::getCacheItem($productId, true);
								if (!empty($product))
								{
									switch ($product['TYPE'])
									{
										case Catalog\ProductTable::TYPE_SKU:
											$fields = static::getDefaultParentSettings($offerState, false);
											break;
										case Catalog\ProductTable::TYPE_EMPTY_SKU:
											$fields = static::getParentProductFieldsByState($offerState);
											break;
										case Catalog\ProductTable::TYPE_PRODUCT:
										case Catalog\ProductTable::TYPE_SET:
											$fields['AVAILABLE'] = Catalog\ProductTable::calculateAvailable($product);
											break;
										default:
											break;
									}
								}
								unset($product);
								break;
						}
					}
					break;
				case \CCatalogSku::TYPE_OFFERS:
					$parent = \CCatalogSku::getProductList($productId, $iblockId);
					if (!isset($parent[$productId]))
					{
						$fields = [
							'TYPE' => Catalog\ProductTable::TYPE_FREE_OFFER,
						];
					}
					else
					{
						$fields = [
							'TYPE' => Catalog\ProductTable::TYPE_OFFER,
						];
						$parentId = $parent[$productId]['ID'];
						$parentIblockId = $parent[$productId]['IBLOCK_ID'];
					}
					$fields = array_merge(
						self::getProductAvailable($productId, $productFields),
						$fields
					);
					break;
				case \CCatalogSku::TYPE_CATALOG:
					$fields = self::getProductAvailable($productId, $productFields);
					break;
			}
		}

		if (
			$process
			&& !empty($fields)
		)
		{
			$updateResult = Catalog\ProductTable::update($productId, $fields);
			if (!$updateResult->isSuccess())
			{
				$process = false;
				$result = false;
			}
			unset($updateResult);
		}

		if (
			$process
			&& $parentId > 0
			&& $parentIblockId > 0
		)
		{
			$result = self::updateParentAvailable($parentId, $parentIblockId);
			if (!$result)
				$process = false;
		}
		unset($parentIblockId, $parentId, $fields, $iblockData);
		unset($process);
		static::enableUpdateAvailable();

		return $result;
	}

	/**
	 * Prepare data for update parent product available and prices. Run calculate, if disable deferred calculation.
	 *
	 * @param int $id				Product id (sku, offer).
	 * @param null|int $iblockId	Product iblock (null, if unknown).
	 * @param null|int $type		Real product type (or null, if unknown).
	 *
	 * @return void
	 */
	public static function calculateComplete($id, $iblockId = null, $type = null)
	{
		if (!self::allowedUpdateAvailable())
			return;

		$id = (int)$id;
		if ($id <= 0)
			return;

		if (
			$type == Catalog\ProductTable::TYPE_FREE_OFFER
			|| ($type == Catalog\ProductTable::TYPE_PRODUCT && !self::isSeparateSkuMode())
			|| $type == Catalog\ProductTable::TYPE_SET
		)
			return;

		if ($iblockId !== null)
		{
			$iblockId = (int)$iblockId;
			if ($iblockId <= 0)
				$iblockId = null;
		}

		switch ($type)
		{
			case Catalog\ProductTable::TYPE_SKU:
			case Catalog\ProductTable::TYPE_EMPTY_SKU:
				self::setCalculateData(self::$deferredSku, $id, $iblockId);
				break;
			case Catalog\ProductTable::TYPE_OFFER:
				self::setCalculateData(self::$deferredOffers, $id, $iblockId);
				break;
			default:
				if (isset(self::$deferredSku[$id]))
					self::setCalculateData(self::$deferredSku, $id, $iblockId);
				elseif (isset(self::$deferredOffers[$id]))
					self::setCalculateData(self::$deferredOffers, $id, $iblockId);
				else
					self::setCalculateData(self::$deferredUnknown, $id, $iblockId);
				break;
		}
		if (!self::usedDeferredCalculation())
			self::calculate();
	}

	/**
	 * Prepare data for update parent product prices. Run calculate, if disable deferred calculation.
	 *
	 * @param int $id				Product id (sku, offer).
	 * @param null|int $iblockId	Product iblock (null, if unknown).
	 * @param null|int $type		Real product type (or null, if unknown).
	 * @param array $priceTypes		Price type ids for calculation (empty, if need all price types).
	 *
	 * @return void
	 */
	public static function calculatePrice($id, $iblockId = null, $type = null, array $priceTypes = [])
	{
		if (!self::allowedUpdateAvailable())
			return;

		if (self::isSeparateSkuMode())
			return;

		$id = (int)$id;
		if ($id <= 0)
			return;

		if (
			$type == Catalog\ProductTable::TYPE_FREE_OFFER
			|| $type == Catalog\ProductTable::TYPE_PRODUCT
			|| $type == Catalog\ProductTable::TYPE_SET
		)
			return;

		if ($iblockId !== null)
		{
			$iblockId = (int)$iblockId;
			if ($iblockId <= 0)
				$iblockId = null;
		}

		switch ($type)
		{
			case Catalog\ProductTable::TYPE_SKU:
			case Catalog\ProductTable::TYPE_EMPTY_SKU:
				self::setCalculatePriceTypes(self::$deferredSku, $id, $iblockId, $priceTypes);
				break;
			case Catalog\ProductTable::TYPE_OFFER:
				self::setCalculatePriceTypes(self::$deferredOffers, $id, $iblockId, $priceTypes);
				break;
			default:
				if (isset(self::$deferredSku[$id]))
					self::setCalculatePriceTypes(self::$deferredSku, $id, $iblockId, $priceTypes);
				elseif (isset(self::$deferredOffers[$id]))
					self::setCalculatePriceTypes(self::$deferredOffers, $id, $iblockId, $priceTypes);
				else
					self::setCalculatePriceTypes(self::$deferredUnknown, $id, $iblockId, $priceTypes);
				break;
		}

		if (!self::usedDeferredCalculation())
			self::calculate();
	}

	/**
	 * Run calculate parent product available and prices. Need data must will be prepared in Sku::calculateComplete or Sku::calculatePrice.
	 *
	 * @return void
	 */
	public static function calculate()
	{
		if (!self::allowedUpdateAvailable())
			return;

		static::disableUpdateAvailable();

		self::updateDeferredSkuList();

		if (!empty(self::$deferredSku))
		{
			self::clearStepData();

			self::$calculatePriceTypes = array_keys(self::$calculatePriceTypes);
			if (!empty(self::$calculatePriceTypes))
				sort(self::$calculatePriceTypes);

			self::loadProductIblocks();

			$list = array_keys(self::$deferredSku);
			sort($list);

			foreach (array_chunk($list, 100) as $pageIds)
			{
				self::loadProductData($pageIds);
				self::updateProductData($pageIds);
				self::updateElements($pageIds);
				self::updateProductFacetIndex($pageIds);
			}
			unset($pageIds, $list);

			self::clearStepData();

			self::$deferredSku = array();
		}

		self::$calculateAvailable = false;
		self::$calculatePriceTypes = array();

		static::enableUpdateAvailable();
	}

	/**
	 * OnIBlockElementAdd event handler. Do not use directly.
	 *
	 * @param array $fields				Element data.
	 * @return void
	 */
	public static function handlerIblockElementAdd($fields)
	{
		static::disablePropertyHandler();
	}

	/**
	 * OnAfterIBlockElementAdd event handler. Do not use directly.
	 *
	 * @param array &$fields			Element data.
	 *
	 * @return void
	 */
	public static function handlerAfterIblockElementAdd(&$fields)
	{
		static::enablePropertyHandler();
	}

	/**
	 * OnIBlockElementUpdate event handler. Do not use directly.
	 *
	 * @param array $newFields			New element data.
	 * @param array $oldFields			Current element data.
	 *
	 * @return void
	 */
	public static function handlerIblockElementUpdate($newFields, $oldFields)
	{
		static::disablePropertyHandler();

		$iblockData = \CCatalogSku::GetInfoByOfferIBlock($newFields['IBLOCK_ID']);
		if (empty($iblockData))
			return;

		if (isset($newFields['ACTIVE']) && $newFields['ACTIVE'] != $oldFields['ACTIVE'])
			self::$changeActive[$newFields['ID']] = $newFields['ACTIVE'];
		self::$currentActive[$newFields['ID']] = $oldFields['ACTIVE'];
	}

	/**
	 * OnAfterIBlockElementUpdate event handler. Do not use directly.
	 *
	 * @param array &$fields			New element data.
	 *
	 * @return void
	 */
	public static function handlerAfterIblockElementUpdate(&$fields)
	{
		$process = true;
		$modifyActive = false;
		$modifyProperty = false;
		$iblockData = false;
		$elementId = 0;

		if (!$fields['RESULT'])
			$process = false;
		else
			$elementId = $fields['ID'];

		if ($process)
		{
			$modifyActive = isset(self::$changeActive[$elementId]);
			$modifyProperty = (
				isset(self::$offers[$elementId])
				&& self::$offers[$elementId]['CURRENT_PRODUCT'] != self::$offers[$elementId]['NEW_PRODUCT']
			);
			$process = $modifyActive || $modifyProperty;
		}

		if ($process)
		{
			$iblockData = \CCatalogSku::GetInfoByOfferIBlock($fields['IBLOCK_ID']);
			$process = !empty($iblockData);
		}

		if ($process)
		{
			if ($modifyActive && !isset(self::$offers[$elementId]))
			{
				$parent = \CCatalogSku::getProductList($elementId, $fields['IBLOCK_ID']);
				if (!empty($parent[$elementId]))
					self::$offers[$elementId] = array(
						'CURRENT_PRODUCT' => $parent[$elementId]['ID'],
						'NEW_PRODUCT' => $parent[$elementId]['ID'],
						'PRODUCT_IBLOCK_ID' => $parent[$elementId]['IBLOCK_ID']
					);
				unset($parent);
			}

			if (isset(self::$offers[$elementId]))
			{
				$offerDescr = self::$offers[$elementId];

				if ($offerDescr['CURRENT_PRODUCT'] > 0)
				{
					if ($modifyActive || $modifyProperty)
					{
						self::calculateComplete(
							$offerDescr['CURRENT_PRODUCT'],
							$iblockData['PRODUCT_IBLOCK_ID'],
							Catalog\ProductTable::TYPE_SKU
						);
					}
				}
				if ($offerDescr['NEW_PRODUCT'] > 0)
				{
					$elementActive = (
						$modifyActive
						? self::$changeActive[$elementId]
						: self::$currentActive[$elementId]
					);
					if ($modifyProperty && $elementActive == 'Y')
					{
						self::calculateComplete(
							$offerDescr['NEW_PRODUCT'],
							$iblockData['PRODUCT_IBLOCK_ID'],
							Catalog\ProductTable::TYPE_SKU
						);
					}
				}

				if ($offerDescr['CURRENT_PRODUCT'] == 0 || $offerDescr['NEW_PRODUCT'] == 0)
				{
					$type = (
						$offerDescr['NEW_PRODUCT'] > 0
						? Catalog\ProductTable::TYPE_OFFER
						: Catalog\ProductTable::TYPE_FREE_OFFER
					);
					self::disableUpdateAvailable();
					$result = Catalog\Model\Product::update($elementId, array('TYPE' => $type));
					unset($result);
					self::enableUpdateAvailable();
					unset($type);
				}

				unset($offerDescr);
			}
			else
			{
				self::disableUpdateAvailable();
				$result = Catalog\Model\Product::update($elementId, array('TYPE' => Catalog\ProductTable::TYPE_FREE_OFFER));
				unset($result);
				self::enableUpdateAvailable();
			}
		}
		if (isset(self::$offers[$elementId]))
			unset(self::$offers[$elementId]);
		if (isset(self::$currentActive[$elementId]))
			unset(self::$currentActive[$elementId]);
		if (isset(self::$changeActive[$elementId]))
			unset(self::$changeActive[$elementId]);
		static::enablePropertyHandler();
	}

	/**
	 * OnIBlockElementDelete event handler. Do not use directly.
	 *
	 * @param int $elementId			Element id.
	 * @param array $elementData		Element data.
	 *
	 * @return void
	 */
	public static function handlerIblockElementDelete($elementId, $elementData)
	{
		if ((int)$elementData['WF_PARENT_ELEMENT_ID'] > 0)
			return;

		$iblockData = \CCatalogSku::GetInfoByOfferIBlock($elementData['IBLOCK_ID']);
		if (empty($iblockData))
			return;

		$parent = \CCatalogSku::getProductList($elementId, $elementData['IBLOCK_ID']);
		if (!empty($parent[$elementId]))
			self::$offers[$elementId] = array(
				'CURRENT_PRODUCT' => $parent[$elementId]['ID'],
				'NEW_PRODUCT' => 0,
				'PRODUCT_IBLOCK_ID' => $parent[$elementId]['IBLOCK_ID']
			);
		unset($parent);
	}

	/**
	 * OnAfterIBlockElementDelete event handler. Do not use directly.
	 *
	 * @param array $elementData		Element data.
	 *
	 * @return void
	 */
	public static function handlerAfterIblockElementDelete($elementData)
	{
		$elementId = $elementData['ID'];
		if (!isset(self::$offers[$elementId]))
			return;

		self::calculateComplete(
			self::$offers[$elementId]['CURRENT_PRODUCT'],
			self::$offers[$elementId]['PRODUCT_IBLOCK_ID'],
			Catalog\ProductTable::TYPE_SKU
		);

		if (isset(self::$offers[$elementId]))
			unset(self::$offers[$elementId]);
	}

	/**
	 * OnIBlockElementSetPropertyValues event handler. Do not use directly.
	 *
	 * @param int $elementId							Element id.
	 * @param int $iblockId								Iblock id.
	 * @param array $newValues							New properties values.
	 * @param int|string|false $propertyIdentifyer		Property identifier.
	 * @param array $propertyList						Changed property list.
	 * @param array $currentValues						Current properties values.
	 *
	 * @return void
	 */
	public static function handlerIblockElementSetPropertyValues(
		$elementId,
		$iblockId,
		$newValues,
		$propertyIdentifyer,
		$propertyList,
		$currentValues
	)
	{
		$iblockData = \CCatalogSku::GetInfoByOfferIBlock($iblockId);
		if (empty($iblockData))
			return;

		$skuPropertyId = $iblockData['SKU_PROPERTY_ID'];
		if (!isset($propertyList[$skuPropertyId]))
			return;
		$skuPropertyCode = (string)$propertyList[$skuPropertyId]['CODE'];
		if ($skuPropertyCode === '')
			$skuPropertyCode = (string)$skuPropertyId;

		$foundValue = false;
		$skuValue = null;
		if ($propertyIdentifyer)
		{
			if (is_int($propertyIdentifyer))
			{
				$propertyId = $propertyIdentifyer;
			}
			else
			{
				$propertyId = (int)$propertyIdentifyer;
				if ($propertyId.'' != $propertyIdentifyer)
					$propertyId = ($skuPropertyCode == $propertyIdentifyer ? $skuPropertyId : 0);
			}
			if ($propertyId == $skuPropertyId)
			{
				$skuValue = $newValues;
				$foundValue = true;
			}
			unset($propertyId);
		}
		else
		{
			if (array_key_exists($skuPropertyId, $newValues))
			{
				$skuValue = $newValues[$skuPropertyId];
				$foundValue = true;
			}
			elseif (array_key_exists($skuPropertyCode, $newValues))
			{
				$skuValue = $newValues[$skuPropertyCode];
				$foundValue = true;
			}
		}
		if (!$foundValue)
			return;
		unset($foundValue);

		$newSkuPropertyValue = 0;
		if (!empty($skuValue))
		{
			if (!is_array($skuValue))
			{
				$newSkuPropertyValue = (int)$skuValue;
			}
			else
			{
				$skuValue = current($skuValue);
				if (!is_array($skuValue))
					$newSkuPropertyValue = (int)$skuValue;
				elseif (!empty($skuValue['VALUE']))
					$newSkuPropertyValue = (int)$skuValue['VALUE'];
			}
		}
		unset($skuValue);
		if ($newSkuPropertyValue < 0)
			$newSkuPropertyValue = 0;

		$currentSkuPropertyValue = 0;
		if (!empty($currentValues[$skuPropertyId]) && is_array($currentValues[$skuPropertyId]))
		{
			$currentSkuProperty = current($currentValues[$skuPropertyId]);
			if (!empty($currentSkuProperty['VALUE']))
				$currentSkuPropertyValue = (int)$currentSkuProperty['VALUE'];
			unset($currentSkuProperty);
		}
		if ($currentSkuPropertyValue < 0)
			$currentSkuPropertyValue = 0;

		// no error - first condition for event OnAfterIblockElementUpdate handler
		if (!static::allowedPropertyHandler() || ($currentSkuPropertyValue != $newSkuPropertyValue))
		{
			self::$offers[$elementId] = array(
				'CURRENT_PRODUCT' => $currentSkuPropertyValue,
				'NEW_PRODUCT' => $newSkuPropertyValue,
				'PRODUCT_IBLOCK_ID' => $iblockData['PRODUCT_IBLOCK_ID']
			);
		}
	}

	/**
	 * OnAfterIBlockElementSetPropertyValues event handler. Do not use directly.
	 *
	 * @param int|string $elementId Element id.
	 * @param int|string $iblockId Iblock id.
	 * @param array|mixed $newValues New properties values.
	 * @param int|string|false $propertyIdentifyer Property identifier.
	 *
	 * @return void
	 */
	public static function handlerAfterIBlockElementSetPropertyValues(
		$elementId,
		$iblockId,
		$newValues,
		$propertyIdentifyer
	)
	{
		if (!static::allowedPropertyHandler())
			return;

		self::calculateOfferChange((int)$elementId, (int)$iblockId);
	}

	/**
	 * OnIBlockElementSetPropertyValuesEx event handler. Do not use directly.
	 *
	 * @param int $elementId							Element id.
	 * @param int $iblockId								Iblock id.
	 * @param array $newValues							New properties values.
	 * @param array $propertyList						Changed property list.
	 * @param array $currentValues						Current properties values.
	 *
	 * @return void
	 */
	public static function handlerIblockElementSetPropertyValuesEx(
		$elementId,
		$iblockId,
		$newValues,
		$propertyList,
		$currentValues
	)
	{
		$iblockData = \CCatalogSku::GetInfoByOfferIBlock($iblockId);
		if (empty($iblockData))
			return;

		$skuPropertyId = $iblockData['SKU_PROPERTY_ID'];
		if (!isset($propertyList[$skuPropertyId]))
			return;
		$skuPropertyCode = (string)$propertyList[$skuPropertyId]['CODE'];
		if ($skuPropertyCode === '')
			$skuPropertyCode = (string)$skuPropertyId;

		$foundValue = false;
		$skuValue = null;
		if (array_key_exists($skuPropertyId, $newValues))
		{
			$skuValue = $newValues[$skuPropertyId];
			$foundValue = true;
		}
		elseif (array_key_exists($skuPropertyCode, $newValues))
		{
			$skuValue = $newValues[$skuPropertyCode];
			$foundValue = true;
		}
		if (!$foundValue)
			return;
		unset($foundValue);

		$newSkuPropertyValue = 0;
		if (!empty($skuValue))
		{
			if (!is_array($skuValue))
			{
				$newSkuPropertyValue = (int)$skuValue;
			}
			else
			{
				if (array_key_exists('VALUE', $skuValue))
				{
					$newSkuPropertyValue = (int)$skuValue['VALUE'];
				}
				else
				{
					foreach($skuValue as $row)
					{
						if (!is_array($row))
							$newSkuPropertyValue = (int)$row;
						elseif (array_key_exists('VALUE', $row))
							$newSkuPropertyValue = (int)$row['VALUE'];
					}
					unset($row);
				}
			}
		}
		unset($skuValue);
		if ($newSkuPropertyValue < 0)
			$newSkuPropertyValue = 0;

		$currentSkuPropertyValue = 0;
		if (!empty($currentValues[$skuPropertyId]) && is_array($currentValues[$skuPropertyId]))
		{
			$currentSkuProperty = current($currentValues[$skuPropertyId]);
			if (!empty($currentSkuProperty['VALUE']))
				$currentSkuPropertyValue = (int)$currentSkuProperty['VALUE'];
			unset($currentSkuProperty);
		}
		if ($currentSkuPropertyValue < 0)
			$currentSkuPropertyValue = 0;

		if (!static::allowedPropertyHandler() || ($currentSkuPropertyValue != $newSkuPropertyValue))
		{
			self::$offers[$elementId] = [
				'CURRENT_PRODUCT' => $currentSkuPropertyValue,
				'NEW_PRODUCT' => $newSkuPropertyValue,
				'PRODUCT_IBLOCK_ID' => $iblockData['PRODUCT_IBLOCK_ID']
			];
		}
	}

	/**
	 * OnAfterIBlockElementSetPropertyValuesEx event handler. Do not use directly.
	 *
	 * @param int|string $elementId Element id.
	 * @param int|string $iblockId Iblock id.
	 * @param array|mixed $newValues New properties values.
	 * @param array|mixed $flags Flags from \CIBlockElement::SetPropertyValuesEx.
	 *
	 * @return void
	 */
	public static function handlerAfterIblockElementSetPropertyValuesEx(
		$elementId,
		$iblockId,
		$newValues,
		$flags
	)
	{
		self::calculateOfferChange((int)$elementId, (int)$iblockId);
	}

	/**
	 * Return available and exist product offers.
	 *
	 * @param int $productId			Product id.
	 * @param int $iblockId				Iblock id.
	 *
	 * @return int
	 *
	 * @throws \Bitrix\Main\ArgumentException
	 */
	public static function getOfferState($productId, $iblockId = 0)
	{
		$result = self::OFFERS_ERROR;
		$productId = (int)$productId;
		if ($productId <= 0)
			return $result;
		$iblockId = (int)$iblockId;
		if ($iblockId <= 0)
			$iblockId = (int)\CIBlockElement::GetIBlockByID($productId);
		if ($iblockId <= 0)
			return $result;

		$result = self::OFFERS_NOT_EXIST;
		$offerList = \CCatalogSku::getOffersList($productId, $iblockId, array(), array('ID', 'ACTIVE'));
		if (!empty($offerList[$productId]))
		{
			$result = self::OFFERS_NOT_AVAILABLE;
			$activeOffers = array_filter($offerList[$productId], '\Bitrix\Catalog\Product\Sku::filterActive');
			if (!empty($activeOffers))
			{
				$existOffers = Catalog\ProductTable::getList(array(
					'select' => array('ID', 'AVAILABLE'),
					'filter' => array('@ID' => array_keys($activeOffers), '=AVAILABLE' => Catalog\ProductTable::STATUS_YES),
					'limit' => 1
				))->fetch();
				if (!empty($existOffers))
					$result = self::OFFERS_AVAILABLE;
				unset($existOffers);
			}
			unset($activeOffers);
		}
		unset($offerList);

		return $result;
	}

	/**
	 * Update sku product available.
	 * @deprecated deprecated since catalog 17.6.0
	 * @see Sku::calculateComplete
	 *
	 * @param int $productId			Product id.
	 * @param int|null $iblockId		Iblock id.
	 *
	 * @return bool
	 */
	protected static function updateProductAvailable($productId, $iblockId)
	{
		$productId = (int)$productId;
		if ($productId <= 0)
			return false;

		self::calculateComplete($productId, $iblockId, Catalog\ProductTable::TYPE_SKU);

		return true;
	}

	/**
	 * Update offer product type.
	 * @deprecated deprecated since catalog 17.6.0
	 * @see \Bitrix\Catalog\Model\Product::update
	 *
	 * @param int $offerId				Offer id.
	 * @param int $type					Product type.
	 *
	 * @return bool
	 *
	 * @throws \Exception
	 */
	protected static function updateOfferType($offerId, $type)
	{
		$offerId = (int)$offerId;
		$type = (int)$type;
		if ($offerId <= 0 || ($type != Catalog\ProductTable::TYPE_OFFER && $type != Catalog\ProductTable::TYPE_FREE_OFFER))
			return false;
		static::disableUpdateAvailable();
		$updateResult = Catalog\Model\Product::update($offerId, array('TYPE' => $type));
		$result = $updateResult->isSuccess();
		static::enableUpdateAvailable();
		return $result;
	}

	/**
	 * Enable property handlers.
	 *
	 * @return void
	 */
	protected static function enablePropertyHandler()
	{
		self::$allowPropertyHandler++;
	}

	/**
	 * Disable property handlers.
	 *
	 * @return void
	 */
	protected static function disablePropertyHandler()
	{
		self::$allowPropertyHandler--;
	}

	/**
	 * Return is enabled property handlers.
	 *
	 * @return bool
	 */
	protected static function allowedPropertyHandler(): bool
	{
		return (self::$allowPropertyHandler >= 0);
	}

	/**
	 * Method for array_filter.
	 *
	 * @param array $row			Product/ Offer data.
	 *
	 * @return bool
	 */
	protected static function filterActive(array $row): bool
	{
		return (isset($row['ACTIVE']) && $row['ACTIVE'] == 'Y');
	}

	/**
	 * Return separate sku mode (catalog option).
	 * @internal
	 *
	 * @return bool
	 */
	private static function isSeparateSkuMode(): bool
	{
		if (self::$separateSkuMode === null)
		{
			self::$separateSkuMode = Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') === 'Y';
		}

		return self::$separateSkuMode;
	}

	/**
	 * Calculate available for product with sku as simple product. Compatible only.
	 * @internal
	 *
	 * @param int $productId				Product id.
	 * @param array $productFields			Product fields (optional).
	 *
	 * @return array
	 */
	private static function getParentDataAsProduct($productId, array $productFields = array())
	{
		if (!isset($productFields['QUANTITY'])
			|| !isset($productFields['QUANTITY_TRACE'])
			|| !isset($productFields['CAN_BUY_ZERO'])
		)
			$productFields = array_merge(Catalog\Model\Product::getCacheItem($productId, true), $productFields);

		return array(
			'TYPE' => Catalog\ProductTable::TYPE_SKU,
			'AVAILABLE' => Catalog\ProductTable::calculateAvailable($productFields)
		);
	}

	/**
	 * Returns the current calculated availability of the product if it is necessary to update it.
	 * @internal
	 *
	 * @param int $productId            Product id.
	 * @param array $productFields      Current product values. Can be empty.
	 *
	 * @return array
	 */
	private static function getProductAvailable($productId, array $productFields)
	{
		$fields = array();

		if (isset($productFields['AVAILABLE']))
			return $fields;

		if (
			isset($productFields['QUANTITY'])
			|| isset($productFields['QUANTITY_TRACE'])
			|| isset($productFields['CAN_BUY_ZERO'])
		)
		{
			if (
				!isset($productFields['QUANTITY'])
				|| !isset($productFields['QUANTITY_TRACE'])
				|| !isset($productFields['CAN_BUY_ZERO'])
			)
				$productFields = array_merge(Catalog\Model\Product::getCacheItem($productId, true), $productFields);
			$fields['AVAILABLE'] = Catalog\ProductTable::calculateAvailable($productFields);
		}

		return $fields;
	}

	/**
	 * Change parent available.
	 *
	 * @param int $parentId Parent id.
	 * @param int $parentIblockId       Parent iblock id.
	 * @return bool
	 *@internal
	 *
	 */
	private static function updateParentAvailable(int $parentId, int $parentIblockId): bool
	{
		$parentIBlock = \CCatalogSku::GetInfoByIblock($parentIblockId);
		if (
			empty($parentIBlock)
			|| (self::isSeparateSkuMode() && $parentIBlock['CATALOG_TYPE'] === \CCatalogSku::TYPE_FULL)
		)
		{
			return true;
		}

		$parentFields = static::getDefaultParentSettings(static::getOfferState(
			$parentId,
			$parentIblockId
		));

		self::disableUpdateAvailable();
		$iterator = Catalog\Model\Product::getList([
			'select' => [
				'ID',
			],
			'filter' => [
				'=ID' => $parentId,
			],
		]);
		$row = $iterator->fetch();
		if (!empty($row))
		{
			$updateResult = Catalog\Model\Product::update($parentId, $parentFields);
		}
		else
		{
			$parentFields['ID'] = $parentId;
			$updateResult = Catalog\Model\Product::add($parentFields);
		}

		$result = $updateResult->isSuccess();
		unset($updateResult);

		self::enableUpdateAvailable();

		return $result;
	}

	/**
	 * Check product type for unknown id and transfer to offer list or parent product list.
	 * @internal
	 *
	 * @return void
	 *
	 * @throws Main\ArgumentException
	 */
	private static function updateDeferredSkuList()
	{
		if (!empty(self::$deferredUnknown))
		{
			$list = array_keys(self::$deferredUnknown);
			sort($list);
			foreach (array_chunk($list, 500) as $pageIds)
			{
				$iterator = Catalog\ProductTable::getList(array(
					'select' => array('ID', 'TYPE'),
					'filter' => array(
						'@ID' => $pageIds,
						'@TYPE' => array(Catalog\ProductTable::TYPE_SKU, Catalog\ProductTable::TYPE_OFFER)
					)
				));
				while ($row = $iterator->fetch())
				{
					$row['ID'] = (int)$row['ID'];
					if ($row['TYPE'] == Catalog\ProductTable::TYPE_SKU)
						self::migrateCalculateData(self::$deferredUnknown, self::$deferredSku, $row['ID']);
					else
						self::migrateCalculateData(self::$deferredUnknown, self::$deferredOffers, $row['ID']);
				}
			}
			unset($row, $iterator, $pageIds, $list);

			self::$deferredUnknown = array();
		}
		if (!empty(self::$deferredOffers))
		{
			$productList = \CCatalogSku::getProductList(array_keys(self::$deferredOffers));
			if (!empty($productList))
			{
				foreach ($productList as $id => $row)
					self::transferCalculationData(self::$deferredOffers, self::$deferredSku, $id, $row['ID'], $row['IBLOCK_ID']);
				unset($id, $row);
			}
			unset($productList);

			self::$deferredOffers = array();
		}
	}

	/**
	 * Fill entity list for calculation.
	 * @internal
	 *
	 * @param array &$list			Item storage.
	 * @param int $id				Item id.
	 * @param null|int $iblockId	Iblock id (null if unknown).
	 *
	 * @return void
	 */
	private static function setCalculateData(array &$list, $id, $iblockId)
	{
		static $priceTypes = null,
			$priceTypeKeys;

		self::$calculateAvailable = true;

		if ($priceTypes === null)
		{
			$priceTypes = array_keys(Catalog\GroupTable::getTypeList());
			$priceTypeKeys = array_fill_keys($priceTypes, true);
		}
		self::$calculatePriceTypes = $priceTypeKeys;

		if ($iblockId === null)
		{
			if (isset($list[$id]))
			{
				$iblockId = $list[$id]['IBLOCK_ID'];
			}
		}

		$list[$id] = [
			'IBLOCK_ID' => $iblockId,
			self::ACTION_AVAILABLE => true,
			self::ACTION_PRICE => self::$calculatePriceTypes,
			self::ACTION_ELEMENT_TIMESTAMP => true,
		];
	}

	/**
	 * Fill price type list for calculation.
	 * @internal
	 *
	 * @param array &$list				Item storage.
	 * @param int $id					Product id.
	 * @param null|int $iblockId		Iblock id.
	 * @param array $priceTypes			Price types (empty if need all).
	 *
	 * @return void
	 */
	private static function setCalculatePriceTypes(array &$list, $id, $iblockId, array $priceTypes)
	{
		static $allPriceTypes = null;

		if ($allPriceTypes === null)
		{
			$allPriceTypes = array_keys(Catalog\GroupTable::getTypeList());
		}

		if (empty($priceTypes))
		{
			$priceTypes = $allPriceTypes;
		}

		foreach ($priceTypes as $typeId)
		{
			self::$calculatePriceTypes[$typeId] = true;
		}

		if ($iblockId === null)
		{
			if (isset($list[$id]))
			{
				$iblockId = $list[$id]['IBLOCK_ID'];
			}
		}

		if (!isset($list[$id]))
		{
			$list[$id] = [
				'IBLOCK_ID' => $iblockId,
				self::ACTION_PRICE => array_fill_keys($priceTypes, true),
				self::ACTION_ELEMENT_TIMESTAMP => true,
			];
		}
		elseif (!isset($list[$id][self::ACTION_PRICE]))
		{
			if ($iblockId !== null)
			{
				$list[$id]['IBLOCK_ID'] = $iblockId;
			}
			$list[$id][self::ACTION_PRICE] = array_fill_keys($priceTypes, true);
			$list[$id][self::ACTION_ELEMENT_TIMESTAMP] = true;
		}
		else
		{
			if ($iblockId !== null)
			{
				$list[$id]['IBLOCK_ID'] = $iblockId;
			}
			foreach ($priceTypes as $typeId)
			{
				$list[$id][self::ACTION_PRICE][$typeId] = true;
			}
			$list[$id][self::ACTION_ELEMENT_TIMESTAMP] = true;
		}

		unset($typeId);
	}

	/**
	 * Remove data from unknown list to parent product list or offer list.
	 * @internal
	 *
	 * @param array &$source		Source storage.
	 * @param array &$destination	Destination storage
	 * @param int $id				Product id.
	 *
	 * @return void
	 */
	private static function migrateCalculateData(array &$source, array &$destination, $id)
	{
		if (!isset($source[$id]))
			return;

		if (isset($destination[$id]))
		{
			if (isset($source[$id][self::ACTION_AVAILABLE]))
				self::setCalculateData($destination, $id, $source[$id]['IBLOCK_ID']);
			elseif (isset($source[$id][self::ACTION_PRICE]))
				self::setCalculatePriceTypes($destination, $id, $source[$id]['IBLOCK_ID'], array_keys($source[$id][self::ACTION_PRICE]));
		}
		else
		{
			$destination[$id] = $source[$id];
		}
		unset($source[$id]);
	}

	/**
	 * Transfer data from unknown list to parent product list or offer list with change id.
	 * @internal
	 *
	 * @param array &$source		Source storage.
	 * @param array &$destination	Destination storage
	 * @param int $sourceId			Product source id.
	 * @param int $destinationId	Product destination id.
	 * @param null|int $iblockId	Iblock id (null, if unknown).
	 *
	 * @return void
	 */
	private static function transferCalculationData(array &$source, array &$destination, $sourceId, $destinationId, $iblockId)
	{
		if (!isset($source[$sourceId]))
			return;

		if (isset($destination[$destinationId]))
		{
			if (isset($source[$sourceId][self::ACTION_AVAILABLE]))
				self::setCalculateData($destination, $destinationId, $iblockId);
			elseif (isset($source[$sourceId][self::ACTION_PRICE]))
				self::setCalculatePriceTypes($destination, $destinationId, $iblockId, array_keys($source[$sourceId][self::ACTION_PRICE]));
		}
		else
		{
			$destination[$destinationId] = $source[$sourceId];
			$destination[$destinationId]['IBLOCK_ID'] = $iblockId;
		}
		unset($source[$sourceId]);
	}

	/**
	 * Clear internal calculate data.
	 * @internal
	 *
	 * @return void
	 */
	private static function clearStepData()
	{
		self::$skuExist = array();
		self::$skuAvailable = array();
		self::$offersIds = array();
		self::$offersMap = array();
		self::$skuPrices = array();
	}

	/**
	 * Get iblock ids for products.
	 * @internal
	 *
	 * @return void
	 */
	private static function loadProductIblocks()
	{
		$listIds = array();
		foreach (array_keys(self::$deferredSku) as $id)
		{
			if (self::$deferredSku[$id]['IBLOCK_ID'] === null)
				$listIds[] = $id;
		}
		unset($id);
		if (!empty($listIds))
		{
			$data = \CIBlockElement::GetIBlockByIDList($listIds);
			foreach ($data as $id => $iblockId)
			{
				self::$deferredSku[$id]['IBLOCK_ID'] = $iblockId;
			}
			unset($id, $iblockId);
		}
		unset($listIds);
	}

	/**
	 * Load parent product data (offers exists and state, exist in database, etc).
	 * @internal
	 *
	 * @param array $listIds	Product ids.
	 *
	 * @return void
	 */
	private static function loadProductData(array $listIds)
	{
		$iterator = Catalog\Model\Product::getList(array(
			'select' => array('ID'),
			'filter' => array('@ID' => $listIds)
		));
		while ($row = $iterator->fetch())
		{
			$row['ID'] = (int)$row['ID'];
			self::$skuExist[$row['ID']] = true;
		}
		unset($row, $iterator);
		$offers = \CCatalogSku::getOffersList(
			$listIds,
			0,
			array(),
			array('ID', 'ACTIVE', 'AVAILABLE')
		);
		foreach ($listIds as $id)
		{
			self::$skuAvailable[$id] = self::OFFERS_NOT_EXIST;
			if (empty($offers[$id]))
				continue;

			self::$skuAvailable[$id] = self::OFFERS_NOT_AVAILABLE;
			$allOffers = array();
			$availableOffers = array();
			foreach ($offers[$id] as $offerId => $row)
			{
				$allOffers[] = $offerId;
				if ($row['ACTIVE'] != 'Y' || $row['AVAILABLE'] != 'Y')
					continue;
				self::$skuAvailable[$id] = self::OFFERS_AVAILABLE;
				$availableOffers[] = $offerId;
			}
			self::$skuPrices[$id] = array();
			if (self::$skuAvailable[$id] == self::OFFERS_AVAILABLE)
			{
				foreach ($availableOffers as $offerId)
				{
					self::$offersMap[$offerId] = $id;
					self::$offersIds[] = $offerId;
				}
			}
			else
			{
				foreach ($allOffers as $offerId)
				{
					self::$offersMap[$offerId] = $id;
					self::$offersIds[] = $offerId;
				}
			}
		}
		unset($offerId, $availableOffers, $allOffers, $id);

		if (!self::isSeparateSkuMode())
			self::loadProductPrices();
	}

	/**
	 * Save available for parent products.
	 * @internal
	 *
	 * @param array $listIds	Product ids.
	 *
	 * @return void
	 */
	private static function updateProductData(array $listIds): void
	{
		$separateMode = self::isSeparateSkuMode();
		if (self::$calculateAvailable)
		{
			$iblockData = false;
			$iblockId = null;
			foreach ($listIds as $id)
			{
				if (empty(self::$deferredSku[$id][self::ACTION_AVAILABLE]))
					continue;
				if (empty(self::$deferredSku[$id]['IBLOCK_ID']))
					continue;
				if (!isset(self::$skuAvailable[$id]))
					continue;

				if ($iblockId !== self::$deferredSku[$id]['IBLOCK_ID'])
				{
					$iblockId = self::$deferredSku[$id]['IBLOCK_ID'];
					$iblockData = \CCatalogSku::GetInfoByIBlock(self::$deferredSku[$id]['IBLOCK_ID']);
				}
				if (empty($iblockData))
				{
					continue;
				}

				$fields = self::getDefaultParentSettings(
					self::$skuAvailable[$id],
					$iblockData['CATALOG_TYPE'] == \CCatalogSku::TYPE_PRODUCT
				);
				if (empty($fields))
				{
					continue;
				}

				// for separate only
				if ($separateMode)
				{
					$fields = [
						'TYPE' => $fields['TYPE'],
					];
				}

				if (isset(self::$skuExist[$id]))
				{
					$result = Catalog\Model\Product::update($id, $fields);
					unset(self::$skuExist[$id]);
				}
				else
				{
					$fields['ID'] = $id;
					$result = Catalog\Model\Product::add($fields);
				}
			}
			unset($result, $id);
		}

		if (!$separateMode)
		{
			self::updateProductPrices($listIds);
		}
	}

	/**
	 * Load exist parent product prices.
	 * @internal
	 *
	 * @return void
	 *
	 * @throws Main\ArgumentException
	 */
	private static function loadProductPrices()
	{
		if (empty(self::$calculatePriceTypes) || empty(self::$offersIds))
			return;

		sort(self::$offersIds);
		foreach (array_chunk(self::$offersIds, 500) as $pageOfferIds)
		{
			$filter = Main\Entity\Query::filter();
			$filter->whereIn('PRODUCT_ID', $pageOfferIds);
			$filter->whereIn('CATALOG_GROUP_ID', self::$calculatePriceTypes);
			$filter->where(Main\Entity\Query::filter()->logic('or')->where('QUANTITY_FROM', '<=', 1)->whereNull('QUANTITY_FROM'));
			$filter->where(Main\Entity\Query::filter()->logic('or')->where('QUANTITY_TO', '>=', 1)->whereNull('QUANTITY_TO'));

			$iterator = Catalog\PriceTable::getList(array(
				'select' => array(
					'PRODUCT_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY',
					'PRICE_SCALE', 'TMP_ID'  //TODO: add MEASURE_RATIO_ID
				),
				'filter' => $filter,
				'order' => array('PRODUCT_ID' => 'ASC', 'CATALOG_GROUP_ID' => 'ASC')
			));
			while ($row = $iterator->fetch())
			{
				/*
				if ($row['MEASURE_RATIO_ID'] !== null)
					continue;
				unset($row['MEASURE_RATIO_ID']);
				*/

				$typeId = (int)$row['CATALOG_GROUP_ID'];
				$offerId = (int)$row['PRODUCT_ID'];
				$productId = self::$offersMap[$offerId];
				if (!isset(self::$deferredSku[$productId][self::ACTION_PRICE][$typeId]))
					continue;
				unset($row['PRODUCT_ID']);

				if (!isset(self::$skuPrices[$productId][$typeId]))
					self::$skuPrices[$productId][$typeId] = $row;
				elseif (self::$skuPrices[$productId][$typeId]['PRICE_SCALE'] > $row['PRICE_SCALE'])
					self::$skuPrices[$productId][$typeId] = $row;
			}
			unset($row, $iterator);
			unset($filter);
		}
	}

	/**
	 * Update parent product prices.
	 * @internal
	 *
	 * @param array $listIds	Product ids.
	 *
	 * @return void
	 *
	 * @throws Main\ArgumentException
	 * @throws Main\Db\SqlQueryException
	 * @throws \Exception
	 */
	private static function updateProductPrices(array $listIds)
	{
		if (empty(self::$calculatePriceTypes))
			return;

		$process = true;

		if (!empty(self::$skuPrices))
		{
			$existIds = array();
			$existIdsByType = array();
			$iterator = Catalog\PriceTable::getList(array(
				'select' => array('ID', 'CATALOG_GROUP_ID', 'PRODUCT_ID'),
				'filter' => array('@PRODUCT_ID' => $listIds, '@CATALOG_GROUP_ID' => self::$calculatePriceTypes),
				'order' => array('ID' => 'ASC')
			));
			while ($row = $iterator->fetch())
			{
				$row['ID'] = (int)$row['ID'];
				$priceTypeId = (int)$row['CATALOG_GROUP_ID'];
				$productId = (int)$row['PRODUCT_ID'];
				$existIds[$row['ID']] = $row['ID'];
				if (!isset($existIdsByType[$productId]))
					$existIdsByType[$productId] = array();
				if (!isset($existIdsByType[$productId][$priceTypeId]))
					$existIdsByType[$productId][$priceTypeId] = array();
				$existIdsByType[$productId][$priceTypeId][] = $row['ID'];
			}
			unset($row, $iterator);
			foreach ($listIds as $productId)
			{
				if (!isset(self::$skuPrices[$productId]))
					continue;

				foreach (array_keys(self::$skuPrices[$productId]) as $resultPriceType)
				{
					$rowId = null;
					$row = self::$skuPrices[$productId][$resultPriceType];
					if (!empty($existIdsByType[$productId][$resultPriceType]))
					{
						$rowId = array_shift($existIdsByType[$productId][$resultPriceType]);
						unset($existIds[$rowId]);
					}
					if ($rowId === null)
					{
						$row['PRODUCT_ID'] = $productId;
						$row['CATALOG_GROUP_ID'] = $resultPriceType;
						$rowResult = Catalog\PriceTable::add($row);
					}
					else
					{
						$rowResult = Catalog\PriceTable::update($rowId, $row);
					}
					if (!$rowResult->isSuccess())
					{
						$process = false;
						break;
					}
				}
			}
			unset($row, $rowResult, $resultPriceType);

			unset($existIdsByType);

			if ($process)
			{
				if (!empty($existIds))
				{
					$conn = Main\Application::getConnection();
					$helper = $conn->getSqlHelper();
					$tableName = $helper->quote(Catalog\PriceTable::getTableName());
					foreach (array_chunk($existIds, 500) as $pageIds)
					{
						$conn->queryExecute(
							'delete from '.$tableName.' where '.$helper->quote('ID').' in ('.implode(',', $pageIds).')'
						);
					}
					unset($pageIds);
					unset($helper, $conn);
				}
				unset($existIds);
			}
		}
		else
		{
			$conn = Main\Application::getConnection();
			$helper = $conn->getSqlHelper();
			$conn->queryExecute(
				'delete from '.$helper->quote(Catalog\PriceTable::getTableName()).
				' where '.$helper->quote('PRODUCT_ID').' in ('.implode(',', $listIds).')'.
				' and '.$helper->quote('CATALOG_GROUP_ID').' in ('.implode(',', self::$calculatePriceTypes).')'
			);
			unset($helper, $conn);
		}
	}

	private static function updateElements(array $listIds): void
	{
		if (self::isSeparateSkuMode())
		{
			return;
		}
		$conn = \Bitrix\Main\Application::getConnection();
		if (self::$queryElementTimestamp === null)
		{
			$helper = $conn->getSqlHelper();
			self::$queryElementTimestamp = 'update ' . $helper->quote(\Bitrix\Iblock\ElementTable::getTableName())
				. ' set ' . $helper->quote('TIMESTAMP_X') . ' = ' . $helper->getCurrentDateTimeFunction()
				. ' where ' . $helper->quote('ID') . '=';
		}
		foreach ($listIds as $id)
		{
			if (empty(self::$deferredSku[$id][self::ACTION_ELEMENT_TIMESTAMP]))
				continue;
			$conn->queryExecute(self::$queryElementTimestamp . $id);
		}
	}

	/**
	 * Update parent product facet index.
	 * @internal
	 *
	 * @param array $listIds	Product ids.
	 *
	 * @return void
	 */
	private static function updateProductFacetIndex(array $listIds): void
	{
		foreach ($listIds as $id)
		{
			if (empty(self::$deferredSku[$id]['IBLOCK_ID']))
				continue;
			if (!isset(self::$skuAvailable[$id]))
				continue;
			Iblock\PropertyIndex\Manager::updateElementIndex(
				self::$deferredSku[$id]['IBLOCK_ID'],
				$id
			);
		}
	}

	/**
	 * Update parent product data from iblock event handlers.
	 *
	 * @param int $elementId	Offer id.
	 * @param int $iblockId		Offer iblock id.
	 *
	 * @return void
	 */
	private static function calculateOfferChange(int $elementId, int $iblockId): void
	{
		if (!isset(self::$offers[$elementId]))
			return;

		$iblockData = \CCatalogSku::GetInfoByOfferIBlock($iblockId);
		if (!empty($iblockData))
		{
			$offerDescr = self::$offers[$elementId];
			$existCurrentProduct = ($offerDescr['CURRENT_PRODUCT'] > 0);
			$existNewProduct = ($offerDescr['NEW_PRODUCT'] > 0);
			if ($existCurrentProduct)
			{
				self::calculateComplete(
					$offerDescr['CURRENT_PRODUCT'],
					$iblockData['PRODUCT_IBLOCK_ID'],
					Catalog\ProductTable::TYPE_SKU
				);
			}
			if ($existNewProduct)
			{
				self::calculateComplete(
					$offerDescr['NEW_PRODUCT'],
					$iblockData['PRODUCT_IBLOCK_ID'],
					Catalog\ProductTable::TYPE_SKU
				);
			}
			if (!$existCurrentProduct || !$existNewProduct)
			{
				self::disableUpdateAvailable();
				$type = (
					$existNewProduct
					? Catalog\ProductTable::TYPE_OFFER
					: Catalog\ProductTable::TYPE_FREE_OFFER
				);
				$result = Catalog\Model\Product::update($elementId, array('TYPE' => $type));
				unset($result);
				self::enableUpdateAvailable();
			}
			unset($existNewProduct, $existCurrentProduct);
			unset($offerDescr);
		}
		unset(self::$offers[$elementId]);
	}
}
