<?php
namespace Bitrix\Catalog\Product;

use Bitrix\Catalog\Config\State;
use Bitrix\Catalog\Product\Store\BatchManager;
use Bitrix\Catalog\Product\Store\CostPriceCalculator;
use Bitrix\Main;
use Bitrix\Catalog;
use Bitrix\Iblock;
use Bitrix\Sale;
use Bitrix\Sale\SaleProviderBase as Base;
use Bitrix\Currency;
use Bitrix\Catalog\Product\Store\DistributionStrategy;

if (Main\Loader::includeModule('sale'))
{
	/**
	 * Class CatalogProvider
	 *
	 * @package Bitrix\Catalog\Product
	 */
	class CatalogProvider extends Base
	{
		private static $userCache = array();

		protected static $hitCache = array();
		protected static $priceTitleCache = array();
		protected static $clearAutoCache = array();

		protected $enableCache = true;

		protected const CACHE_USER_GROUPS = 'USER_GROUPS';
		protected const CACHE_ITEM_WITHOUT_RIGHTS = 'IBLOCK_ELEMENT_PERM_N';
		protected const CACHE_ITEM_RIGHTS = 'IBLOCK_ELEMENT';
		protected const CACHE_ITEM_WITH_RIGHTS = 'IBLOCK_ELEMENT_PERM_Y';
		protected const CACHE_ELEMENT_RIGHTS_MODE = 'ELEMENT_RIGHTS_MODE';
		protected const CACHE_ELEMENT_SHORT_DATA = 'IBLOCK_ELEMENT_SHORT';
		protected const CACHE_PRODUCT = 'CATALOG_PRODUCT';
		protected const CACHE_VAT = 'VAT_INFO';
		protected const CACHE_IBLOCK_RIGHTS = 'IBLOCK_RIGHTS';
		protected const CACHE_STORE = 'CATALOG_STORE';
		protected const CACHE_STORE_PRODUCT = 'CATALOG_STORE_PRODUCT';
		protected const CACHE_PARENT_PRODUCT_ACTIVE = 'PARENT_PRODUCT_ACTIVE';
		protected const CACHE_CATALOG_IBLOCK_LIST = 'CATALOG_IBLOCK_LIST';
		protected const CACHE_PRODUCT_STORE_LIST = 'CACHE_PRODUCT_STORE_LIST';
		protected const CACHE_PRODUCT_AVAILABLE_LIST = 'CACHE_PRODUCT_AVAILABLE_LIST';

		protected const CATALOG_PROVIDER_EMPTY_STORE_ID = Base::EMPTY_STORE_ID;
		protected const BUNDLE_TYPE = 1;

		/** @deprecated */
		protected const RESULT_PRODUCT_LIST = Base::SUMMMARY_PRODUCT_LIST;
		protected const RESULT_CATALOG_LIST = 'CATALOG_DATA_LIST';

		protected const USE_GATALOG_DATA = 'CATALOG_DATA';

		protected const AMOUNT_SRC_QUANTITY = 'QUANTITY';
		protected const AMOUNT_SRC_QUANTITY_LIST = Base::FLAT_QUANTITY_LIST;
		protected const AMOUNT_SRC_PRICE_LIST = 'PRICE_LIST';
		protected const AMOUNT_SRC_STORE_QUANTITY_LIST = Base::STORE_QUANTITY_LIST;
		protected const AMOUNT_SRC_RESERVED_LIST = Base::FLAT_RESERVED_QUANTITY_LIST;
		protected const AMOUNT_SRC_STORE_RESERVED_LIST = Base::STORE_RESERVED_QUANTITY_LIST;

		private const QUANTITY_FORMAT_STORE = 1;
		private const QUANTITY_FORMAT_SHIPMENT = 2;

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function getProductData(array $products)
		{
			return $this->getData($products);
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function getCatalogData(array $products)
		{
			return $this->getData(
				$products,
				[self::USE_GATALOG_DATA]
			);
		}

		/**
		 * @param array $products
		 * @param array $options
		 *
		 * @return Sale\Result
		 */
		private function getData(array $products, array $options = array()): Sale\Result
		{
			$context = $this->getContext();

			$resultProductList = array_fill_keys(array_keys($products), false);

			$result = new Sale\Result();

			$userId = (int)($context['USER_ID'] ?? 0);
			if ($userId < 0)
			{
				$userId = 0;
			}
			$siteId = $context['SITE_ID'] ?? false;
			$currency = $context['CURRENCY'] ?? false;
			$currency = (is_string($currency) ? Currency\CurrencyManager::checkCurrencyID($context['CURRENCY']) : false);
			if ($currency === false)
			{
				$currency = Sale\Internals\SiteCurrencyTable::getSiteCurrency($siteId ?: SITE_ID);
			}
			$adminSection = (defined('ADMIN_SECTION') && ADMIN_SECTION === true);

			if (in_array('DISABLE_CACHE', $options))
			{
				$this->enableCache = false;
			}

			$catalogDataEnabled = self::isCatalogDataEnabled($options);

			$outputVariable = static::getOutputVariable($options);

			$productGetIdList = array();
			$correctProductIds = [];

			$iblockElementSelect = array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'ACTIVE', 'ACTIVE_DATE', 'XML_ID');
			if (!$catalogDataEnabled)
			{
				$iblockElementSelect = array_merge($iblockElementSelect, array('NAME', 'DETAIL_PAGE_URL'));
			}

			$resultList = array();
			foreach ($products as $productId => $itemData)
			{
				$resultList[$productId] = false;
				if (!isset($itemData['ITEM_CODE']))
				{
					$itemData['ITEM_CODE'] = $productId;
					$products[$productId]['ITEM_CODE'] = $productId;
				}

				if (!isset($itemData['BASKET_CODE']))
				{
					$itemData['BASKET_CODE'] = $productId;
					$products[$productId]['BASKET_CODE'] = $productId;
				}

				$hash = $productId."|".$userId;
				$productCachedData = static::getHitCache(self::CACHE_ITEM_RIGHTS, $hash, $iblockElementSelect);
				if ($this->enableCache && !empty($productCachedData))
				{
					$products[$productId]['PRODUCT_DATA'] = $productCachedData;
					$correctProductIds[$productId] = true;
				}
				else
				{
					$productGetIdList[] = $productId;
				}
			}

			if (!empty($productGetIdList))
			{
				$productDataList = $this->getElements(
					$productGetIdList,
					$iblockElementSelect,
					($adminSection ? $userId : null)
				);

				foreach ($productDataList as $productId => $productData)
				{
					$products[$productId]['PRODUCT_DATA'] = $productData;
					$hash = $productId."|".$userId;
					static::setHitCache(self::CACHE_ITEM_RIGHTS, $hash, $productData);
					$correctProductIds[$productId] = true;
				}

				$products = array_intersect_key(
					$products,
					$correctProductIds
				);
				if (empty($products))
				{
					return static::getResultProvider($result, $outputVariable, $resultList);
				}
			}
			unset($correctProductIds);

			$iblockList = array();
			$iblockDataList = array();
			$iblockGetIdList = array();
			foreach ($products as $productId => $productData)
			{
				$iblockId = $productData['PRODUCT_DATA']['IBLOCK_ID'];
				$iblockList[$iblockId][] = $productId;
			}

			foreach ($iblockList as $iblockId => $iblockProductIdList)
			{
				$iblockData = static::getHitCache(self::CACHE_CATALOG_IBLOCK_LIST, $iblockId);
				if ($this->enableCache && !empty($iblockData))
				{
					$iblockDataList[$iblockId] = $iblockData;
				}
				else
				{
					$iblockGetIdList[] = $iblockId;
				}
			}

			if (!empty($iblockGetIdList))
			{
				$iblockDataList = $this->getIblockData($iblockGetIdList) + $iblockDataList;
			}

			$iblockList = array_intersect_key(
				$iblockList,
				$iblockDataList
			);

			$iblockProductMap = static::createIblockProductMap($iblockList, $iblockDataList);

			$correctProductList = static::checkSkuPermission($iblockProductMap);

			$products = array_intersect_key(
				$products,
				array_fill_keys($correctProductList, true)
			);
			if (empty($products))
			{
				return static::getResultProvider($result, $outputVariable, $resultList);
			}

			$products = static::changeSubscribeProductQuantity($products, $iblockProductMap);

			// catalog product

			$catalogSelect = array(
				'ID',
				'CAN_BUY_ZERO',
				'QUANTITY_TRACE',
				'QUANTITY',
				'QUANTITY_RESERVED',
				'MEASURE',
				'TYPE',
				'AVAILABLE',
			);
			if (!$catalogDataEnabled)
			{
				$catalogSelect = array_merge($catalogSelect, array(
					'WEIGHT',
					'WIDTH',
					'HEIGHT',
					'LENGTH',
					'BARCODE_MULTI',
				));
			}
			$catalogSelect = array_merge($catalogSelect, Catalog\Product\SystemField::getProviderSelectFields());

			$catalogProductDataList = static::getCatalogProducts(array_keys($products), $catalogSelect);

			$products = array_intersect_key($products, $catalogProductDataList);
			if (empty($products))
			{
				return static::getResultProvider($result, $outputVariable, $resultList);
			}

			// fill catalog xml id
			$products = self::fillCatalogXmlId($products, $iblockProductMap);
			// prepare offers xml id
			$products = self::fillOfferXmlId($products, $catalogProductDataList);

			// get prices and discounts
			$priceDataList = self::getPriceDataList(
				$products,
				[
					'IS_ADMIN_SECTION' => $adminSection,
					'USER_ID' => $userId,
					'SITE_ID' => $siteId,
					'CURRENCY' => $currency,
				]
			);
			$discountList = self::getDiscountList($priceDataList);

			$productQuantityList = array();
			$productPriceList = array();

			$fullQuantityMode = in_array('FULL_QUANTITY', $options);

			foreach ($products as $productId => $productData)
			{
				$catalogProductData = $catalogProductDataList[$productId];

				$quantityList = array();

				if (array_key_exists('QUANTITY', $productData))
				{
					$quantityList = array($productData['BASKET_CODE'] => $productData['QUANTITY']);
				}

				if (!empty($productData[Base::FLAT_QUANTITY_LIST]))
				{
					$quantityList = $productData[Base::FLAT_QUANTITY_LIST];
				}

				$productQuantityList[$productData['BASKET_CODE']]['QUANTITY_RESERVED'] = $catalogProductData['QUANTITY_RESERVED'];

				$baseCatalogQuantity = (float)$catalogProductData['QUANTITY'];

				$allCount = count($quantityList);
				$sumQuantity = 0;
				foreach ($quantityList as $quantity)
				{
					$sumQuantity += (float)abs($quantity);
				}

				$catalogQuantityForAvaialable = $baseCatalogQuantity;
				$checkCatalogQuantity = $baseCatalogQuantity;

				$isEnough = !($catalogProductData['CHECK_QUANTITY'] && $catalogQuantityForAvaialable < $sumQuantity);
				$setQuantity = $baseCatalogQuantity;
				foreach ($quantityList as $basketCode => $quantity)
				{
					$quantity = (float)abs($quantity);

					if (!$isEnough)
					{
						if ($catalogQuantityForAvaialable - $quantity < 0)
						{
							$quantity = $catalogQuantityForAvaialable;
						}

						$catalogQuantityForAvaialable -= $quantity;
					}

					$productQuantityList[$basketCode]['AVAILABLE_QUANTITY'] = (
						$baseCatalogQuantity >= $quantity || !$catalogProductData['CHECK_QUANTITY']
							? $quantity
							: $baseCatalogQuantity
					);

					if ($fullQuantityMode)
					{
						$checkCatalogQuantity -= $quantity;
						$setQuantity = $quantity;
						$allCount--;

						if ($allCount == 0)
						{
							$setQuantity = $checkCatalogQuantity + $quantity;
						}
					}
					else
					{
						if ($baseCatalogQuantity - $quantity > 0 || !$catalogProductData['CHECK_QUANTITY'])
						{
							$setQuantity = $quantity;
						}
					}

					$productQuantityList[$basketCode]['QUANTITY'] = $setQuantity;
				}
				unset($basketCode, $quantity);

				foreach (array_keys($quantityList) as $basketCode)
				{
					if (isset($priceDataList[$productId][$basketCode]))
					{
						$productPriceList[$basketCode] = $priceDataList[$productId][$basketCode];
					}
				}
				unset($basketCode);

				$measure = isset($catalogProductData['MEASURE']) ? (int)$catalogProductData['MEASURE'] : null;
				$measureFields = static::getMeasure($measure);
				if (!empty($measureFields))
				{
					$catalogProductDataList[$productId] = $measureFields + $catalogProductDataList[$productId];
				}
			}

			unset($fullQuantityMode);

			$resultData = static::setCatalogDataToProducts($products, $catalogProductDataList, $options);

			$priceResultList = static::createProductPriceList($products, $productPriceList, $discountList);

			$resultList = static::createProductResult($products, $resultData, $priceResultList, $productQuantityList);

			$resultList = $resultList + $resultProductList;

			return static::getResultProvider($result, $outputVariable, $resultList);
		}

		private static function getOutputVariable(array $options = array()): string
		{
			return (self::isCatalogDataEnabled($options)
				? static::RESULT_CATALOG_LIST
				: Base::SUMMMARY_PRODUCT_LIST
			);
		}

		private static function getResultProvider(Sale\Result $result, $outputVariable, array $resultList = array()): Sale\Result
		{
			$result->setData(
				array(
					$outputVariable => $resultList,
				)
			);

			return $result;
		}

		/**
		 * @param array $list
		 * @param array $select
		 * @param int|null $userId
		 *
		 * @return array
		 */
		private function getElements(array $list, array $select, ?int $userId = null): array
		{
			$filter = array(
				'ID' => $list,
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R',
			);
			if ($userId !== null)
			{
				$filter['PERMISSIONS_BY'] = $userId;
			}

			$resultList = array();
			$dbIBlockElement = \CIBlockElement::GetList(
				array(),
				$filter,
				false,
				false,
				$select
			);
			while ($productData = $dbIBlockElement->GetNext())
			{
				$resultList[$productData['ID']] = $productData;
			}
			unset($dbIBlockElement);

			return $resultList;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function getBundleItems(array $products)
		{
			$result = new Sale\Result();

			$resultList = array();

			$productIdList = array();
			static $proxyCatalogProductSet = array();
			static $proxyCatalogSkuData = array();
			$bundleItemList = array();

			$bundleIndex = array();

			foreach ($products as $productId => $productData)
			{
				$proxyCatalogProductSetKey = $productId."|".static::BUNDLE_TYPE;
				if (!empty($proxyCatalogProductSet[$proxyCatalogProductSetKey]) && is_array($proxyCatalogProductSet[$proxyCatalogProductSetKey]))
				{
					$childItemList = $proxyCatalogProductSet[$proxyCatalogProductSetKey];
				}
				else
				{
					$childItemList = \CCatalogProductSet::getAllSetsByProduct($productId, static::BUNDLE_TYPE);
					if (!empty($childItemList) && is_array($childItemList))
					{
						$proxyCatalogProductSet[$proxyCatalogProductSetKey] = $childItemList;
					}
				}

				if (!empty($childItemList))
				{
					$bundleItemList = $childItemList + $bundleItemList;
					$bundleItemListIds = array_keys($childItemList);
					$bundleItemId = reset($bundleItemListIds);
					unset($bundleItemListIds);
					$bundleIndex[$bundleItemId] = $productId;
				}
			}

			$childIdList = array();

			if (empty($bundleItemList))
				return $result;

			$bundleChildList = array();
			$childProducts = array();
			$productIndexList = array();
			foreach ($bundleItemList as $parentItemid => $bundleItemData)
			{
				$productId = $bundleIndex[$parentItemid];
				foreach ($bundleItemData["ITEMS"] as $childItemid => $item)
				{
					if (!isset($childIdList[$item['ITEM_ID']]))
						$childIdList[$item['ITEM_ID']] = true;

					$bundleChildList[$item['ITEM_ID']] = $item;
					$childProducts[$item['ITEM_ID']] = array(
						'ITEM_CODE' => $item['ITEM_ID'],
						'PRODUCT_ID' => $item['ITEM_ID'],
						Base::FLAT_QUANTITY_LIST => [$item['ITEM_ID'] => $item['QUANTITY']],
						'BUNDLE_CHILD' => true,
					);

					$productIndexList[$item['ITEM_ID']] = array(
						'PRODUCT_ID' => $productId,
						'PARENT_ID' => $parentItemid,
						'CHILD_ID' => $childItemid,
					);
				}
			}

			$r = $this->getProductData($childProducts);
			if ($r->isSuccess())
			{
				$resultData = $r->getData();
				if (
					!empty($resultData[Base::SUMMMARY_PRODUCT_LIST])
					&& is_array($resultData[Base::SUMMMARY_PRODUCT_LIST])
				)
				{
					$resultDataList = $resultData[Base::SUMMMARY_PRODUCT_LIST];
					foreach ($resultDataList as $itemCode => $itemData)
					{
						$item = $bundleChildList[$itemCode];
						if (array_key_exists('QUANTITY_TRACE', $itemData))
							unset($itemData['QUANTITY_TRACE']);

						$itemData["PRODUCT_ID"] = $item["ITEM_ID"];
						$itemData["MODULE"] = 'catalog';
						$itemData["PRODUCT_PROVIDER_CLASS"] = Basket::getDefaultProviderName();

						$productIdList[] = $item["ITEM_ID"];

						$itemData["PROPS"] = array();

						if (!empty($proxyCatalogSkuData[$item["ITEM_ID"]]) && is_array($proxyCatalogSkuData[$item["ITEM_ID"]]))
						{
							$parentSkuData = $proxyCatalogSkuData[$item["ITEM_ID"]];
						}
						else
						{
							$parentSkuData = \CCatalogSku::GetProductInfo($item["ITEM_ID"]);
							if ($parentSkuData)
							{
								$proxyCatalogSkuData[$item["ITEM_ID"]] = $parentSkuData;
							}
						}

						if (!empty($parentSkuData))
						{
							$childDataList = array();
							$childIdGetList = array();

							$iblockPropertyDataList = array();
							$iblockPropertyIdList = array();

							$propsSku = array();

							foreach ($childIdList as $childId => $parentValue)
							{
								$productData = static::getHitCache(self::CACHE_ELEMENT_SHORT_DATA, $item["ITEM_ID"]);
								if (!empty($productData))
								{
									$childDataList[$childId] = $productData;
									if (!isset($iblockPropertyIdList[$productData['IBLOCK_ID']]))
									{
										$iblockPropertyIdList[$productData['IBLOCK_ID']] = true;
									}
								}
								else
								{
									$childIdGetList[] = $childId;
								}
							}

							if (!empty($childIdGetList))
							{
								$iterator = Iblock\ElementTable::getList([
									'select' => [
										'ID',
										'IBLOCK_ID',
										'NAME',
										'IBLOCK_SECTION_ID',
									],
									'filter' => \CIBlockElement::getPublicElementsOrmFilter(['@ID' => $childIdGetList]),
								]);
								while ($productData = $iterator->fetch())
								{
									static::setHitCache(self::CACHE_ELEMENT_SHORT_DATA, $productData["ID"], $productData);
									$childDataList[$productData["ID"]] = $productData;

									if (!isset($iblockPropertyIdList[$productData['IBLOCK_ID']]))
									{
										$iblockPropertyIdList[$productData['IBLOCK_ID']] = true;
									}
								}
							}

							foreach ($iblockPropertyIdList as $iblockPropertyId => $iblockPropertyValue)
							{
								if ($propsSku = static::getHitCache('IBLOCK_PROPERTY', $iblockPropertyId))
								{
									$iblockPropertyDataList[$iblockPropertyId] = $propsSku;
								}
								else
								{
									$dbOfferProperties = \CIBlock::GetProperties($iblockPropertyId, array(), array("!XML_ID" => "CML2_LINK"));
									while($offerProperties = $dbOfferProperties->Fetch())
									{
										$propsSku[] = $offerProperties["CODE"];
									}
									static::setHitCache('IBLOCK_PROPERTY', $iblockPropertyId, $propsSku);
								}
							}

							$propSkuHash = (!empty($propsSku)) ? md5(join('|', $propsSku)): md5($item["ITEM_ID"]);

							$proxyProductPropertyKey = $item["ITEM_ID"]."_".$parentSkuData["IBLOCK_ID"]."_".$propSkuHash;

							$productProperties = static::getHitCache('PRODUCT_PROPERTY', $proxyProductPropertyKey);
							if (empty($productProperties))
							{
								$productProperties = \CIBlockPriceTools::GetOfferProperties(
									$item["ITEM_ID"],
									$parentSkuData["IBLOCK_ID"],
									$propsSku
								);

								static::setHitCache('PRODUCT_PROPERTY', $proxyProductPropertyKey, $productProperties);
							}

							if (!empty($productProperties))
							{
								foreach ($productProperties as $propData)
								{
									$itemData["PROPS"][] = array(
										"NAME" => $propData["NAME"],
										"CODE" => $propData["CODE"],
										"VALUE" => $propData["VALUE"],
										"SORT" => $propData["SORT"],
									);
								}
							}

						}

						$parentProductIndexData = $productIndexList[$itemCode];

						$priceData = array();
						if (!empty($itemData['PRICE_LIST']))
						{
							$priceData = reset($itemData['PRICE_LIST']);
							unset($itemData['PRICE_LIST']);
						}

						if (array_key_exists('PRODUCT', $itemData))
						{
							unset($itemData['PRODUCT']);
						}

						$bundleItemList[$parentProductIndexData['PARENT_ID']]["ITEMS"][$parentProductIndexData['CHILD_ID']] = array_merge($item,  $itemData, $priceData);
					}
				}
			}

			$elementList = static::getHitCache('IBLOCK_ELEMENT_LIST', $productId);
			if (empty($elementList))
			{
				$productRes = \CIBlockElement::GetList(
					array(),
					array('ID' => $productIdList),
					false,
					false,
					array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "PREVIEW_PICTURE", "DETAIL_PICTURE", "IBLOCK_TYPE_ID", "XML_ID")
				);
				while ($productData = $productRes->GetNext())
				{
					$elementList[$productData['ID']] = $productData;
				}

				if (!empty($elementList) && is_array($elementList))
				{
					static::setHitCache('IBLOCK_ELEMENT_LIST', $productId, $elementList);
				}
			}

			if (!empty($elementList) && is_array($elementList))
			{
				foreach ($bundleItemList as $bundleParentId => $bundleItemData)
				{
					foreach ($bundleItemData["ITEMS"] as $bundleChildId => $item)
					{
						if (!$elementList[$item["ITEM_ID"]])
							continue;

						$elementData = $elementList[$item["ITEM_ID"]];

						$properties = array();
						$strIBlockXmlID = (string)\CIBlock::GetArrayByID($elementData['IBLOCK_ID'], 'XML_ID');
						if ($strIBlockXmlID != "")
						{
							$properties[] = array(
								"NAME" => "Catalog XML_ID",
								"CODE" => "CATALOG.XML_ID",
								"VALUE" => $strIBlockXmlID,
							);

							$elementData['CATALOG_XML_ID'] = $strIBlockXmlID;

						}

						if (!empty($proxyCatalogSkuData[$item["ITEM_ID"]]) && strpos($elementData["XML_ID"], '#') === false)
						{
							$parentSkuData = $proxyCatalogSkuData[$item["ITEM_ID"]];
							if (!empty($proxyParentData[$parentSkuData['ID']]) && is_array($proxyParentData[$parentSkuData['ID']]))
							{
								$parentData = $proxyParentData[$parentSkuData['ID']];
							}
							else
							{
								$parentIterator = Iblock\ElementTable::getList(
									array(
										'select' => array('ID', 'XML_ID'),
										'filter' => array('ID' => $parentSkuData['ID']),
									)
								);

								$parentData = $parentIterator->fetch();
								if (!empty($parentData))
								{
									$proxyParentData[$parentSkuData['ID']] = $parentData;
								}

								unset($parentIterator);
							}

							$elementData["XML_ID"] = $parentData['XML_ID'].'#'.$elementData["XML_ID"];
							unset($parentData);
						}

						$properties[] = array(
							"NAME" => "Product XML_ID",
							"CODE" => "PRODUCT.XML_ID",
							"VALUE" => $elementData["XML_ID"],
						);

						$bundleItemData = $bundleItemList[$bundleParentId]["ITEMS"][$bundleChildId];

						$bundleItemProps = array();
						if (is_array($elementData["PROPS"]) && !empty($elementData["PROPS"]))
						{
							$bundleItemProps = $elementData["PROPS"];
						}

						if (!empty($properties))
						{
							$bundleItemProps = $bundleItemProps + $properties;
						}

						$bundleItemList[$bundleParentId]["ITEMS"][$bundleChildId] = $bundleItemData + array(
								'IBLOCK_ID' => $elementData["IBLOCK_ID"],
								'IBLOCK_SECTION_ID' => $elementData["IBLOCK_SECTION_ID"],
								'PREVIEW_PICTURE' => $elementData["PREVIEW_PICTURE"],
								'DETAIL_PICTURE' => $elementData["DETAIL_PICTURE"],

								'CATALOG_XML_ID' => $elementData["CATALOG_XML_ID"],
								'PRODUCT_XML_ID' => $elementData["XML_ID"],
							);

						$bundleItemList[$bundleParentId]["ITEMS"][$bundleChildId]['PROPS'] = $bundleItemProps;
					}
				}

			}

			foreach(GetModuleEvents("sale", "OnGetSetItems", true) as $eventData)
			{
				ExecuteModuleEventEx($eventData, array(&$bundleItemList));
			}

			if (!empty($bundleItemList))
			{
				foreach ($bundleItemList as $bundleParentId => $bundleData)
				{
					if (empty($bundleIndex[$bundleParentId]))
						continue;

					$productId = $bundleIndex[$bundleParentId];

					$resultList[$productId] = $bundleData;
				}

				$result->setData(
					array(
						'BUNDLE_LIST' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @param $userId
		 *
		 * @return bool|array
		 */
		protected static function getUserGroups($userId)
		{
			$userId = (int)$userId;
			if ($userId < 0)
				return false;

			if (!isset(self::$userCache[$userId]))
				self::$userCache[$userId] = Main\UserTable::getUserGroupIds($userId);

			return self::$userCache[$userId];
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function ship(array $products)
		{
			return $this->shipProducts($products);
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function unship(array $products)
		{
			$result = new Sale\Result();

			$r = $this->tryUnship($products);
			if (!$r->isSuccess())
			{
				$result->addErrors($r->getErrors());
				return $result;
			}

			$data = $r->getData();

			if (!empty($data['PRODUCTS_LIST_SHIPPED']))
			{
				$productsList = array();
				foreach ($data['PRODUCTS_LIST_SHIPPED'] as $productId => $value)
				{
					if ($value && !empty($products[$productId]))
					{
						$productsList[$productId] = $products[$productId];
					}
				}

				if (!empty($productsList))
				{
					$this->shipProducts($productsList);
				}
			}

			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 * @throws Main\ObjectNotFoundException
		 */
		public function deliver(array $products)
		{
			$result = new Sale\Result();

			$resultList = array();

			$productOrderList = static::createOrderListFromProducts($products);

			$deliverProductList = array();
			foreach ($products as $productId => $productData)
			{
				$userId = null;
				$orderPaid = null;
				$orderId = null;

				if (isset($productData['USER_ID']))
				{
					$userId = $productData['USER_ID'];
				}

				if (isset($productData['ORDER_ID']))
				{
					$orderId = $productData['ORDER_ID'];
				}

				if (isset($productData['PAID']))
				{
					$orderPaid = $productData['PAID'];
				}

				/**
				 * @var int $orderId
				 * @var Sale\Order $order
				 */

				if (isset($productOrderList[$productId]))
				{
					foreach ($productOrderList[$productId] as $orderId => $order)
					{
						if (!isset($resultList[$productId]))
						{
							$deliverProductList[] = array(
								'PRODUCT_ID' => $productId,
								'USER_ID' => $order->getUserId(),
								'PAID' => $order->isPaid(),
								'ORDER_ID' => $orderId,
							);
						}
					}
				}
				else
				{
					if (isset($productData['USER_ID']))
					{
						$userId = $productData['USER_ID'];
					}

					if (isset($productData['ORDER_ID']))
					{
						$orderId = $productData['ORDER_ID'];
					}

					if (isset($productData['PAID']))
					{
						$orderPaid = $productData['PAID'];
					}

					$deliverProductList[] = array(
						'PRODUCT_ID' => $productId,
						'USER_ID' => $userId,
						'PAID' => $orderPaid,
						'ORDER_ID' => $orderId,
					);
				}
			}

			if (!empty($deliverProductList))
			{
				foreach ($deliverProductList as $productData)
				{
					$productId = $productData['PRODUCT_ID'];
					$resultList[$productId] = \CatalogPayOrderCallback(
						$productId,
						$productData['USER_ID'],
						$productData['PAID'],
						$productData['ORDER_ID']
					);
				}
			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'DELIVER_PRODUCTS_LIST' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function viewProduct(array $products)
		{
			$result = new Sale\Result();

			$resultList = array();

			foreach ($products as $productId => $itemData)
			{
				if (!isset($resultList[$productId]))
				{
					$context = $this->getContext();

					$resultList[$productId] = \CatalogViewedProductCallback(
						$productId,
						$context['USER_ID'],
						$context['SITE_ID']
					);
				}

			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'VIEW_PRODUCTS_LIST' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @param array $items
		 *
		 * @return Sale\Result
		 */
		public function recurring(array $items)
		{
			$result = new Sale\Result();

			$resultList = array();

			foreach ($items as $productId => $itemData)
			{
				if (!isset($resultList[$productId]))
				{
					$context = $this->getContext();

					$resultList[$productId] = \CatalogRecurringCallback(
						$productId,
						$context['USER_ID']
					);
				}

			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'RECURRING_PRODUCTS_LIST' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @param array $items
		 *
		 * @return Sale\Result
		 */
		public function checkBarcode(array $items)
		{
			$result = new Sale\Result();

			$resultList = array();

			foreach ($items as $barcodeParams)
			{
				$resultList[$barcodeParams['BARCODE']] = false;
				$dbres = \CCatalogStoreBarcode::GetList(
					array(),
					$barcodeParams
				);
				$resultList[$barcodeParams['BARCODE']] = (bool)($dbres->GetNext());

			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'BARCODE_CHECK_LIST' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		protected function shipProducts(array $products)
		{
			$result = new Sale\Result();

			$resultList = array_fill_keys(array_keys($products), false);

			$availableItems = $this->createProductsListWithCatalogData($products);

			$productStoreDataList = [];
			if (Catalog\Config\State::isUsedInventoryManagement())
			{
				$r = $this->getProductListStores($products);
				if ($r->isSuccess())
				{
					$data = $r->getData();
					if (!empty($data['PRODUCT_STORES_LIST']))
					{
						$productStoreDataList = $data['PRODUCT_STORES_LIST'];
					}
					unset($data);
				}
				unset($r);
			}

			foreach ($availableItems as $productId => $productData)
			{
				$r = static::shipProduct(
					$productData,
					(!empty($productStoreDataList[$productId])
						? $productStoreDataList[$productId]
						: []
					)
				);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					$result->addWarnings($r->getErrors());
				}

				$resultList[$productId] = $r->isSuccess();
			}

			$result->setData([
				'SHIPPED_PRODUCTS_LIST' => $resultList,
			]);

			return $result;
		}

		// private function

		/**
		 * @param array $quantityList
		 *
		 * @return Sale\Result
		 */
		private static function updateCatalogStoreAmount(array $quantityList): Sale\Result
		{
			$result = new Sale\Result();
			$resultList = array();

			if (empty($quantityList))
			{
				return $result;
			}

			foreach ($quantityList as $catalogStoreId => $amount)
			{
				$fields = [
					'AMOUNT' => $amount['AMOUNT'],
				];
				if (isset($amount['QUANTITY_RESERVED']))
				{
					$fields['QUANTITY_RESERVED'] = $amount['QUANTITY_RESERVED'];
				}

				$internalResult = Catalog\StoreProductTable::update($catalogStoreId, $fields);

				$resultList[$catalogStoreId] = $internalResult->isSuccess();
			}

			$result->setData(
				array(
					'AMOUNT_UPDATED_LIST' => $resultList,
				)
			);

			return $result;
		}

		/**
		 * @param array $productData
		 * @param array $productStoreDataList
		 *
		 * @return Sale\Result
		 */
		private static function shipProduct(array $productData, array $productStoreDataList = array()): Sale\Result
		{
			$result = new Sale\Result();

			$productId = $productData['PRODUCT_ID'];

			$productQuantity = self::getTotalAmountFromQuantityList($productData);

			$needShip = ($productQuantity < 0);

			if (
				//Catalog\Config\State::isUsedInventoryManagement()
				$productData['PRODUCT']['USED_STORE_INVENTORY']
			)
			{
				if (empty($productStoreDataList) && $needShip)
				{
					$result->addError(
						new Sale\ResultError(
							Main\Localization\Loc::getMessage(
								"DDCT_DEDUCTION_STORE_ERROR",
								array_merge(
									self::getProductCatalogInfo($productId),
									array("#PRODUCT_ID#" => $productId)
								)
							), "DDCT_DEDUCTION_STORE_ERROR"
						)
					);
					return $result;
				}

				$setQuantityList = array();
				$r = static::getSetableStoreQuantityProduct($productData, $productStoreDataList);
				if ($r->isSuccess())
				{
					$resultData = $r->getData();
					if (!empty($resultData[Base::FLAT_QUANTITY_LIST]))
					{
						$setQuantityList = $resultData[Base::FLAT_QUANTITY_LIST];
					}
				}
				else
				{
					return $r;
				}

				/*if (!$productData['PRODUCT']['USED_STORE_INVENTORY']) // product types without stores
				{
					$setQuantityList = [];
				} */

				$r = static::updateCatalogStoreAmount($setQuantityList);
				if ($r->isSuccess())
				{
					$resultData = $r->getData();
					if (!empty($resultData['AMOUNT_UPDATED_LIST']))
					{
						foreach($resultData['AMOUNT_UPDATED_LIST'] as $catalogStoreIsUpdated)
						{
							if ($catalogStoreIsUpdated === true)
							{
								static::clearHitCache(self::CACHE_STORE_PRODUCT);
								if ($needShip)
								{
									$r = static::deleteBarcodes($productData);
								}
								else
								{
									$r = static::addBarcodes($productData);
								}

								if (!$r->isSuccess())
								{
									$result->addErrors($r->getErrors());
								}
							}
						}
					}
				}
				else
				{
					return $r;
				}

				return static::shipQuantityWithStoreControl($productData);
			}
			elseif (isset($productData["CATALOG"]))
			{
				if ($productData["CATALOG"]["QUANTITY_TRACE"] == "N")
				{
					return $result;
				}
			}

			return static::shipQuantityWithoutStoreControl($productData);

		}

		/**
		 * @param array $productData
		 *
		 * @return Sale\Result
		 */
		private static function shipQuantityWithStoreControl(array $productData): Sale\Result
		{
			$result = new Sale\Result();

			$productId = (int)$productData['PRODUCT_ID'];

			$productQuantity = self::getTotalAmountFromQuantityList($productData);

			$catalogData = $productData['CATALOG'];

			$isExistsReserve = static::isExistsCommonStoreReserve($productData) && static::isReservationEnabled();
			$isNeedShip = ($productQuantity < 0);

			$productQuantity = abs($productQuantity);

			$fields = array();

			$catalogReservedQuantity = (float)$catalogData['QUANTITY_RESERVED'];
			$catalogQuantity = self::getTotalAmountFromPriceList($catalogData);

			$sumCatalogQuantity = $catalogReservedQuantity + $catalogQuantity;

			if ($isNeedShip)
			{
				if ($isExistsReserve)
				{
					if ($catalogReservedQuantity >= $productQuantity)
					{
						$fields["QUANTITY_RESERVED"] = $catalogReservedQuantity - $productQuantity;
					}
					elseif ($sumCatalogQuantity >= $productQuantity)
					{
						$fields["QUANTITY_RESERVED"] = 0;
						$fields["QUANTITY"] = $catalogQuantity - ($productQuantity - $catalogReservedQuantity);
					}
					else
					{
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									"DDCT_DEDUCTION_NOT_ENOUGHT_QUANTITY_PRODUCT",
									array_merge(
										self::getProductCatalogInfo($productId),
										array("#PRODUCT_ID#" => $productId)
									)
								), "DDCT_DEDUCTION_NOT_ENOUGHT_QUANTITY_PRODUCT"
							)
						);
						return $result;
					}
				}
				else
				{
					if ($productQuantity <= $catalogQuantity)
					{
						$fields["QUANTITY"] = $catalogQuantity - $productQuantity;
					}
					elseif ($productQuantity <= $sumCatalogQuantity)
					{
						$fields["QUANTITY"] = 0;
						$fields["QUANTITY_RESERVED"] = $catalogReservedQuantity - ($productQuantity - $catalogQuantity);
					}
					else
					{
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									"DDCT_DEDUCTION_NOT_ENOUGHT_QUANTITY_PRODUCT",
									array_merge(
										self::getProductCatalogInfo($productId),
										array("#PRODUCT_ID#" => $productId)
									)
								), "DDCT_DEDUCTION_NOT_ENOUGHT_QUANTITY_PRODUCT"
							)
						);
						return $result;
					}
				}
			}
			else
			{
				if ($isExistsReserve)
				{
					$fields["QUANTITY_RESERVED"] = $catalogReservedQuantity + $productQuantity;
				}
				else
				{
					$fields["QUANTITY"] = $catalogQuantity + $productQuantity;
				}
			}

			if (!$productData['PRODUCT']['USED_RESERVATION'])
			{
				if (isset($fields['QUANTITY_RESERVED']))
				{
					unset($fields['QUANTITY_RESERVED']);
				}
			}

			$isUpdated = false;
			if (!empty($fields))
			{
				$internalResult = Catalog\Model\Product::update($productId, $fields);

				if ($internalResult->isSuccess())
				{
					$isUpdated = true;
					$quantityValues = array();

					if (isset($fields['QUANTITY']))
					{
						$quantityValues[QuantityControl::QUANTITY] = $fields['QUANTITY'];
						QuantityControl::resetAvailableQuantity($productId);
					}

					if (isset($fields['QUANTITY_RESERVED']))
					{
						$quantityValues[QuantityControl::RESERVED_QUANTITY] = $fields['QUANTITY_RESERVED'];
					}

					if (!empty($quantityValues))
					{
						QuantityControl::setValues($productId, $quantityValues);
					}
				}
				else
				{
					self::convertErrors($internalResult);
				}
				unset($internalResult);
			}

			$result->setData(
				array(
					'IS_UPDATED' => $isUpdated,
				)
			);

			return $result;
		}

		/**
		 * @param array $productData
		 *
		 * @return Sale\Result
		 */
		private static function shipQuantityWithoutStoreControl(array $productData): Sale\Result
		{
			$result = new Sale\Result();
			$productId = (int)$productData['PRODUCT_ID'];

			$catalogData = $productData['CATALOG'];

			$productQuantity = self::getTotalAmountFromQuantityList($productData);

			$catalogReservedQuantity = (float)$catalogData['QUANTITY_RESERVED'];
			$catalogQuantity = self::getTotalAmountFromPriceList($catalogData);

			$fields = array();

			$isExistsReserve = static::isExistsCommonStoreReserve($productData) && static::isReservationEnabled();
			$isNeedShip = ($productQuantity < 0);

			if ($isNeedShip)
			{
				$productQuantity = abs($productQuantity);
				if (($productQuantity <= $catalogReservedQuantity + $catalogQuantity) || $catalogData["CAN_BUY_ZERO"] == "Y")
				{
					if ($isExistsReserve)
					{
						if ($productQuantity <= $catalogReservedQuantity)
						{
							$needReservedQuantity = $catalogReservedQuantity - $productQuantity;
							$fields["QUANTITY_RESERVED"] = $needReservedQuantity;
						}
						else
						{
							$fields["QUANTITY_RESERVED"] = 0;
							$fields["QUANTITY"] = $catalogQuantity - ($productQuantity - $catalogReservedQuantity);
						}
					}
					else
					{
						$fields["QUANTITY"] = $catalogQuantity - $productQuantity;
					}

				}
				else //not enough products - don't deduct anything
				{
					$result->addError(
						new Sale\ResultError(
							Main\Localization\Loc::getMessage(
								"DDCT_DEDUCTION_QUANTITY_ERROR",
								array_merge(
									self::getProductCatalogInfo($productId),
									array("#PRODUCT_ID#" => $productId)
								)
							), "DDCT_DEDUCTION_QUANTITY_ERROR"
						)
					);
				}
			}
			else
			{
				if ($isExistsReserve)
				{
					$fields["QUANTITY_RESERVED"] = $catalogReservedQuantity + $productQuantity;
				}
				else
				{
					$fields["QUANTITY"] = $catalogQuantity + $productQuantity;
				}
			}

			if (!$productData['PRODUCT']['USED_RESERVATION'])
			{
				if (isset($fields['QUANTITY_RESERVED']))
				{
					unset($fields['QUANTITY_RESERVED']);
				}
			}

			if (!empty($fields))
			{
				$internalResult = Catalog\Model\Product::update($productId, $fields);

				if ($internalResult-> isSuccess())
				{
					$quantityValues = array();

					if (isset($fields['QUANTITY']))
					{
						$quantityValues[QuantityControl::QUANTITY] = $fields['QUANTITY'];
						QuantityControl::resetAvailableQuantity($productId);
					}

					if (isset($fields['QUANTITY_RESERVED']))
					{
						$quantityValues[QuantityControl::RESERVED_QUANTITY] = $fields['QUANTITY_RESERVED'];
					}

					if (!empty($quantityValues))
					{
						QuantityControl::setValues($productId, $quantityValues);
					}
				}
				else
				{
					self::convertErrors($internalResult);
				}
				unset($internalResult);
			}

			return $result;
		}

		private static function isExistsCommonStoreReserve(array $productData): bool
		{
			if (
				empty($productData['NEED_RESERVE_BY_STORE_LIST'])
				|| !is_array($productData['NEED_RESERVE_BY_STORE_LIST'])
			)
			{
				return false;
			}

			foreach ($productData['NEED_RESERVE_BY_STORE_LIST'] as $block)
			{
				if (empty($block) || !is_array($block))
				{
					continue;
				}
				if (in_array(true, $block, true))
				{
					return true;
				}
			}

			return false;
		}

		/**
		 * @param array $productData
		 * @param array $productStoreDataList
		 *
		 * @return Sale\Result
		 */
		private static function getSetableStoreQuantityProduct(array $productData, array $productStoreDataList): Sale\Result
		{
			$result = new Sale\Result();

			$setQuantityList = array();
			$productQuantity = self::getTotalAmountFromQuantityList($productData);
			$isNeedShip = ($productQuantity < 0);

			$quantityByStore = self::getQuantityDataFromStore($productData);
			$needQuantityList = $quantityByStore['AMOUNT'];

			if (empty($needQuantityList))
			{
				$autoShipStore = static::getAutoShipStoreData($productData, $productStoreDataList);

				if (!empty($autoShipStore))
				{
					$needQuantityList[$autoShipStore['STORE_ID']] = ($productQuantity > $autoShipStore['AMOUNT'] ? $autoShipStore['AMOUNT'] : abs($productQuantity));

					$shipmentItemList = $productData['SHIPMENT_ITEM_LIST'];
					/** @var Sale\ShipmentItem $shipmentItem */
					foreach ($shipmentItemList as $index => $shipmentItem)
					{
						$shipmentItemStoreCollection = $shipmentItem->getShipmentItemStoreCollection();
						if ($shipmentItemStoreCollection && $shipmentItemStoreCollection->count() === 0)
						{
							$item = $shipmentItemStoreCollection->createItem($shipmentItem->getBasketItem());
							$item->setField('STORE_ID', $autoShipStore['STORE_ID']);
							$item->setField('QUANTITY', abs($productData['SHIPMENT_ITEM_QUANTITY_LIST'][$index]));
						}
					}
				}
			}

			if (!empty($productStoreDataList))
			{
				$isReservationEnabled = Main\Config\Option::get("sale", "product_reserve_condition") != "S";
				$compileReserve = self::getCompileReserve($productData);
				foreach ($productStoreDataList as $storeId => $productStoreData)
				{
					$productId = $productStoreData['PRODUCT_ID'];

					if ($isNeedShip && (isset($needQuantityList[$storeId]) && $productStoreData['AMOUNT'] < $needQuantityList[$storeId]))
					{
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									'DDCT_DEDUCTION_QUANTITY_STORE_ERROR_2',
									array_merge(
										self::getProductCatalogInfo($productId),
										[
											'#STORE_NAME#' => \CCatalogStoreControlUtil::getStoreName($storeId),
											'#STORE_ID#' => $storeId,
											'#PRODUCT_ID#' => $productId,
										]
									)
								), 'DDCT_DEDUCTION_QUANTITY_STORE_ERROR'
							)
						);
					}
					else
					{
						$storeConfig = self::getUpdateStoreConfig(
							$storeId,
							$needQuantityList,
							$compileReserve,
							[
								'RESERVATION_ENABLED' => $isReservationEnabled,
							]
						);
						if (!$storeConfig['AMOUNT'] && !$storeConfig['QUANTITY_RESERVED'])
						{
							continue;
						}

						$storeUpdate = [];
						if ($storeConfig['AMOUNT'])
						{
							$setQuantity = $productQuantity;

							if (isset($needQuantityList[$storeId]))
							{
								$setQuantity = ($isNeedShip ? -1 : 1) * $needQuantityList[$storeId];
							}

							$storeUpdate['AMOUNT'] = $productStoreData['AMOUNT'] + $setQuantity;
							$storeUpdate['DELTA'] = $setQuantity;
							$storeUpdate['OLD_AMOUNT'] = $productStoreData['AMOUNT'];
							unset($setQuantity);
						}
						if ($storeConfig['QUANTITY_RESERVED'])
						{
							$setReserveQuantity = 0;
							if (isset($needQuantityList[$storeId]))
							{
								$setReserveQuantity = ($isNeedShip ? -1 : 1) * $needQuantityList[$storeId];
							}
							if (isset($quantityByStore['QUANTITY_RESERVED'][$storeId]))
							{
								$setReserveQuantity = ($isNeedShip ? -1 : 1) * $quantityByStore['QUANTITY_RESERVED'][$storeId];
							}
							if ($setReserveQuantity != 0)
							{
								$storeUpdate['QUANTITY_RESERVED'] = $productStoreData['QUANTITY_RESERVED']
									+ $setReserveQuantity;
								$storeUpdate['OLD_QUANTITY_RESERVED'] = $productStoreData['QUANTITY_RESERVED'];
								$storeUpdate['QUANTITY_RESERVED_DELTA'] = $setReserveQuantity;
							}
							unset($setReserveQuantity);
						}
						if (!empty($storeUpdate))
						{
							$setQuantityList[$productStoreData['ID']] = $storeUpdate;
						}
						unset($storeUpdate, $storeConfig);
					}
				}
			}

			if (!empty($setQuantityList))
			{
				$result->addData(
					array(
						Base::FLAT_QUANTITY_LIST => $setQuantityList,
					)
				);
			}

			return $result;
		}

		private static function getUpdateStoreConfig(int $storeId, array $quantityList, array $reserveList, array $config): array
		{
			$result = [
				'AMOUNT' => isset($quantityList[$storeId]),
				'QUANTITY_RESERVED' => false,
			];

			if ($config['RESERVATION_ENABLED'])
			{
				$result['QUANTITY_RESERVED'] = isset($reserveList[$storeId]);
			}

			return $result;
		}

		private static function getCompileReserve(array $product): array
		{
			if (empty($product['NEED_RESERVE_BY_STORE_LIST']) || !is_array($product['NEED_RESERVE_BY_STORE_LIST']))
			{
				return [];
			}

			$result = [];
			foreach ($product['NEED_RESERVE_BY_STORE_LIST'] as $shipment)
			{
				if (empty($shipment) || !is_array($shipment))
				{
					continue;
				}
				foreach ($shipment as $storeId => $flag)
				{
					if ($flag === true)
					{
						$result[$storeId] = true;
					}
				}
			}

			return $result;
		}

		private static function getQuantityDataFromStore(array $product): array
		{
			$result = [
				'AMOUNT' => [],
				'QUANTITY_RESERVED' => [],
			];

			$storeDataExists = (
				!empty($product['STORE_DATA_LIST'])
				&& is_array($product['STORE_DATA_LIST'])
			);
			$reserveDataExists = (
				!empty($product[self::AMOUNT_SRC_STORE_RESERVED_LIST])
				&& is_array($product[self::AMOUNT_SRC_STORE_RESERVED_LIST])
			);
			if (!$storeDataExists && !$reserveDataExists)
			{
				return $result;
			}

			$found = false;
			if ($storeDataExists)
			{
				foreach ($product['STORE_DATA_LIST'] as $storeList)
				{
					if (!is_array($storeList))
					{
						continue;
					}
					$found = true;
					foreach ($storeList as $storeId => $store)
					{
						if (!isset($result['AMOUNT'][$storeId]))
						{
							$result['AMOUNT'][$storeId] = 0;
						}
						$result['AMOUNT'][$storeId] += (float)$store['QUANTITY'];
						if (isset($store['RESERVED_QUANTITY']))
						{
							if (!isset($result['QUANTITY_RESERVED'][$storeId]))
							{
								$result['QUANTITY_RESERVED'][$storeId] = 0;
							}
							$result['QUANTITY_RESERVED'][$storeId] += (float)$store['RESERVED_QUANTITY'];
						}
					}
				}
			}

			if (!$found && $reserveDataExists)
			{
				switch (self::getQuantityFormat($product[self::AMOUNT_SRC_STORE_RESERVED_LIST]))
				{
					case self::QUANTITY_FORMAT_STORE:
						$internalResult = self::calculateQuantityFromStores($product[self::AMOUNT_SRC_STORE_RESERVED_LIST]);
						break;
					case self::QUANTITY_FORMAT_SHIPMENT:
						$internalResult = self::calculateQuantityFromShipments($product[self::AMOUNT_SRC_STORE_RESERVED_LIST]);
						break;
					default:
						$internalResult = null;
						break;
				}
				if ($internalResult !== null)
				{
					$result['QUANTITY_RESERVED'] = $internalResult;
				}
				unset($internalResult);
			}

			return $result;
		}

		/**
		 * @param array $productData
		 *
		 * @return Sale\Result
		 */
		private static function deleteBarcodes(array $productData): Sale\Result
		{
			$result = new Sale\Result();

			$storeData = $productData['STORE_DATA_LIST'];
			if (!empty($storeData))
			{
				foreach ($storeData as $storeDataList)
				{
					foreach($storeDataList as $storeDataValue)
					{
						$r = static::deleteBarcode($storeDataValue);
						if (!$r->isSuccess())
						{
							$result->addErrors($r->getErrors());
						}
					}
				}
			}

			return $result;
		}

		/**
		 * @param array $storeData
		 *
		 * @return Sale\Result
		 */
		private static function deleteBarcode(array $storeData): Sale\Result
		{
			$result = new Sale\Result();

			$storeId = $storeData["STORE_ID"];
			$productId = $storeData["PRODUCT_ID"];
			$barcodeMulti = $storeData['IS_BARCODE_MULTI'];

			$barcodeList = $storeData['BARCODE'];

			foreach ($barcodeList as $barcodeValue)
			{
				if (trim($barcodeValue) == "" || !$barcodeMulti)
				{
					continue;
				}

				$result = new Sale\Result();
				$barcodeFields = array(
					"STORE_ID" => $storeId,
					"BARCODE" => $barcodeValue,
					"PRODUCT_ID" => $productId,
				);

				$dbres = \CCatalogStoreBarcode::GetList(
					array(),
					$barcodeFields,
					false,
					false,
					array("ID", "STORE_ID", "BARCODE", "PRODUCT_ID")
				);

				$catalogStoreBarcodeRes = $dbres->Fetch();
				if ($catalogStoreBarcodeRes)
				{
					\CCatalogStoreBarcode::Delete($catalogStoreBarcodeRes["ID"]);
				}
				else
				{
					$result->addError(
						new Sale\ResultError(
							Main\Localization\Loc::getMessage(
								"DDCT_DEDUCTION_BARCODE_ERROR",
								array_merge(
									self::getProductCatalogInfo($productId),
									array("#BARCODE#" => $barcodeValue)
								)
							), "DDCT_DEDUCTION_BARCODE_ERROR"
						)
					);
				}
			}

			return $result;
		}

		/**
		 * @param array $productData
		 *
		 * @return Sale\Result
		 */
		private static function addBarcodes(array $productData): Sale\Result
		{
			$result = new Sale\Result();
			$storeData = $productData['STORE_DATA_LIST'];
			if (!empty($storeData))
			{
				foreach ($storeData as $storeDataList)
				{
					foreach($storeDataList as $storeDataValue)
					{
						$r = static::addBarcode($storeDataValue);
						if (!$r->isSuccess())
						{
							$result->addErrors($r->getErrors());
						}
					}
				}
			}

			return $result;
		}

		/**
		 * @param array $storeData
		 *
		 * @return Sale\Result
		 */
		private static function addBarcode(array $storeData): Sale\Result
		{
			$result = new Sale\Result();

			$storeId = $storeData["STORE_ID"];
			$productId = $storeData["PRODUCT_ID"];
			$barcodeMulti = $storeData['IS_BARCODE_MULTI'];

			$barcodeList = $storeData['BARCODE'];

			foreach ($barcodeList as $barcodeValue)
			{
				if (trim($barcodeValue) == "" || !$barcodeMulti)
				{
					continue;
				}

				$result = new Sale\Result();
				$barcodeFields = array(
					"STORE_ID" => $storeId,
					"BARCODE" => $barcodeValue,
					"PRODUCT_ID" => $productId,
				);
				\CCatalogStoreBarcode::Add($barcodeFields);
			}

			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function reserve(array $products)
		{
			$result = new Sale\Result();

			$resultList = array();

			$availableItems = $this->createProductsListWithCatalogData($products);
			foreach ($availableItems as $productId => $productData)
			{
				$resultList[$productId] = false;

				$r = static::reserveProduct($productData);
				if ($r->isSuccess())
				{
					$resultData = $r->getData();
					if (!empty($resultData))
					{
						$resultList[$productId] = $resultData;
					}
				}
				else
				{
					$result->addErrors($r->getErrors());
				}

			}

			$result->setData(array(
				'RESERVED_PRODUCTS_LIST' => $resultList,
			));

			return $result;
		}

		/**
		 * @param array $productData
		 *
		 * @return Sale\Result
		 */
		private static function reserveProduct(array $productData): Sale\Result
		{
			if (
				!static::isReservationEnabled()
				|| !$productData['PRODUCT']['USED_RESERVATION']
			)
			{
				return static::reserveQuantityWithDisabledReservation($productData);
			}

			return self::reserveStoreQuantityWithEnabledReservation($productData);

		}

		/**
		 * @param array $productData
		 *
		 * @return Sale\Result
		 */
		private static function reserveStoreQuantityWithEnabledReservation(array $productData): Sale\Result
		{
			$result = new Sale\Result();

			$enableStoreControl = Catalog\Config\State::isUsedInventoryManagement();

			$resultFields = [];
			$fields = []; // fields for update products
			$storeFields = []; // rows for update reserve in store
			$needShipList = [];

			$productId = $productData['PRODUCT_ID'];
			$storeProductQuantity = self::getStoreQuantityFromQuantityList($productData); // quantity with stores
			// empty store can't updated if used inventory managment
			if ($enableStoreControl && isset($storeProductQuantity[Base::EMPTY_STORE_ID]))
			{
				return $result;
			}

			$productQuantity = self::getTotalAmountFromQuantityList($productData);

			$isNeedReserve = ($productQuantity > 0);
			$catalogData = $productData['CATALOG'];

			$catalogReservedQuantity = (float)$catalogData['QUANTITY_RESERVED'];
			$catalogQuantity = self::getTotalAmountFromPriceList($catalogData);

			$sumCatalogQuantity = $catalogQuantity + $catalogReservedQuantity;

			if (isset($productData['NEED_SHIP']))
			{
				$needShipList = $productData['NEED_SHIP'];
			}

			$setQuantityReserved = $catalogReservedQuantity;

			if (!empty($needShipList) && !empty($productData['SHIPMENT_ITEM_DATA_LIST']))
			{
				$shipmentItemList = $productData['SHIPMENT_ITEM_DATA_LIST'];
				foreach ($needShipList as $shipmentItemIndex => $isNeedShip)
				{
					if ($setQuantityReserved <= 0)
					{
						$setQuantityReserved = 0;
						break;
					}

					if ($isNeedShip === true)
					{
						$shipmentItemQuantity = $shipmentItemList[$shipmentItemIndex];

						$setQuantityReserved -= $shipmentItemQuantity;
					}
				}
			}

			if ($catalogData["QUANTITY_TRACE"] == "N")
			{
				$fields["QUANTITY_RESERVED"] = $setQuantityReserved;
				$resultFields['IS_UPDATED'] = true;
				$resultFields['QUANTITY_RESERVED'] = 0;
			}
			else
			{
				$resultFields['QUANTITY_RESERVED'] = $catalogReservedQuantity;

				if (
					$productData['PRODUCT']['TYPE'] === Catalog\ProductTable::TYPE_PRODUCT
					|| $productData['PRODUCT']['TYPE'] === Catalog\ProductTable::TYPE_OFFER
				)
				{
					$storeFields = $enableStoreControl
						? self::loadCurrentStoreReserve($productId, $storeProductQuantity)
						: [];
				}

				if ($isNeedReserve)
				{
					if ($catalogData["CAN_BUY_ZERO"] == "Y")
					{
						$fields["QUANTITY_RESERVED"] = $catalogReservedQuantity + $productQuantity;
						$fields['QUANTITY'] = $catalogQuantity - $productQuantity;
					}
					else
					{
						if ($catalogQuantity >= $productQuantity)
						{
							$fields["QUANTITY"] = $catalogQuantity - $productQuantity;
							$fields["QUANTITY_RESERVED"] = $catalogReservedQuantity + $productQuantity;
						}
						else
						{
							$resultFields["QUANTITY_NOT_RESERVED"] = $productQuantity - $catalogQuantity;

							$fields["QUANTITY"] = 0;
							$fields["QUANTITY_RESERVED"] = $sumCatalogQuantity;

							$result->addWarning(
								new Sale\ResultWarning(
									Main\Localization\Loc::getMessage(
										"RSRV_QUANTITY_NOT_ENOUGH_ERROR",
										self::getProductCatalogInfo($productId)
									), "ERROR_NOT_ENOUGH_QUANTITY"
								)
							);
						}
					}
				}
				else //undo reservation
				{
					$correctReserve = 0;
					if ($enableStoreControl)
					{
						foreach (array_keys($storeFields) as $storeId)
						{
							if ($storeFields[$storeId]['ID'] === null)
							{
								continue;
							}
							$storeProductFields = $storeFields[$storeId];
							$newReserve = $storeProductFields['QUANTITY_RESERVED'] + $storeProductFields['ADD_QUANTITY_RESERVED'];
							if ($newReserve < 0)
							{
								$correctReserve -= $newReserve;
								$storeFields[$storeId]['ADD_QUANTITY_RESERVED'] -= $newReserve;
							}
						}
					}

					$needQuantity = abs($productQuantity) - $correctReserve;

					$fields["QUANTITY"] = $catalogQuantity + $needQuantity;

					$needReservedQuantity = $catalogReservedQuantity - $needQuantity;
					if ($needQuantity > $catalogReservedQuantity)
					{
						$needReservedQuantity = $catalogReservedQuantity;
					}

					$fields["QUANTITY_RESERVED"] = $needReservedQuantity;

					if ($enableStoreControl)
					{
						foreach (array_keys($storeFields) as $storeId)
						{
							if ($storeFields[$storeId]['ID'] === null)
							{
								unset($storeFields[$storeId]);
							}
						}
					}
				}

			} //quantity trace

			if (!$productData['PRODUCT']['USED_RESERVATION'])
			{
				if (isset($fields['QUANTITY_RESERVED']))
				{
					unset($fields['QUANTITY_RESERVED']);
				}
			}

			if (!empty($fields) && is_array($fields))
			{
				$storeSuccess = true;
				if ($enableStoreControl)
				{
					foreach (array_keys($storeFields) as $index)
					{
						if ($index === Base::EMPTY_STORE_ID)
						{
							$storeSuccess = false;
						}
						else
						{
							$storeProductFields = $storeFields[$index];
							$id = $storeProductFields['ID'];
							$storeProductFields['QUANTITY_RESERVED'] += $storeProductFields['ADD_QUANTITY_RESERVED'];
							unset($storeProductFields['ID'], $storeProductFields['ADD_QUANTITY_RESERVED']);
							if ($id === null)
							{
								$storeProductFields['AMOUNT'] = 0;
								$internalResult = Catalog\StoreProductTable::add($storeProductFields);
							}
							else
							{
								unset($storeProductFields['STORE_ID'], $storeProductFields['PRODUCT_ID']);
								$internalResult = Catalog\StoreProductTable::update($id, $storeProductFields);
							}
							if ($internalResult->isSuccess())
							{
								$storeFields[$index]['ID'] = (int)$internalResult->getId();
							}
							else
							{
								$storeFields[$index]['ERROR'] = true;
								$storeFields[$index]['ERROR_MESSAGES'] = $internalResult->getErrorMessages();
								$storeSuccess = false;
							}
						}
						if (!$storeSuccess)
						{
							break;
						}
					}
				}

				if (!$storeSuccess)
				{
					return $result;
				}

				$resultFields['IS_UPDATED'] = false;
				$internalResult = Catalog\Model\Product::update($productId, $fields);
				if ($internalResult->isSuccess())
				{
					$resultFields['IS_UPDATED'] = true;
					$quantityValues = array();
					if (isset($fields['QUANTITY']))
					{
						$quantityValues[QuantityControl::QUANTITY] = $fields['QUANTITY'];
						QuantityControl::resetAvailableQuantity($productId);
					}

					if (isset($fields['QUANTITY_RESERVED']))
					{
						$quantityValues[QuantityControl::RESERVED_QUANTITY] = $fields['QUANTITY_RESERVED'];
					}

					if (!empty($quantityValues))
					{
						QuantityControl::setValues($productId, $quantityValues);
					}
				}
				else
				{
					self::convertErrors($internalResult);
				}
				unset($internalResult);
			}

			if (isset($resultFields['IS_UPDATED']))
			{
				if (isset($fields['QUANTITY_RESERVED']))
				{
					$needReserved = $fields["QUANTITY_RESERVED"] - $resultFields['QUANTITY_RESERVED'];
					if ($resultFields['QUANTITY_RESERVED'] > $fields["QUANTITY_RESERVED"])
					{
						$needReserved = $fields["QUANTITY_RESERVED"];
					}
					$resultFields["QUANTITY_RESERVED"] = $needReserved;
				}

				if (!empty($resultFields))
				{
					$result->setData($resultFields);
				}
			}

			return $result;
		}

		/**
		 * @param array $productData
		 *
		 * @return Sale\Result
		 */
		private static function reserveQuantityWithDisabledReservation(array $productData): Sale\Result
		{
			$result = new Sale\Result();

			$catalogData = $productData['CATALOG'];

			$isQuantityTrace = $catalogData["QUANTITY_TRACE"] == 'Y';

			$productQuantity = self::getTotalAmountFromQuantityList($productData);
			$catalogQuantity = self::getTotalAmountFromPriceList($catalogData);

			$isUpdated = true;

			$fields = array(
				'QUANTITY' => $catalogQuantity,
			);

			if ($isQuantityTrace)
			{
				$productId = $productData['PRODUCT_ID'];
				$fields['QUANTITY'] -= $productQuantity;
				if ($catalogData["CAN_BUY_ZERO"] != "Y" && ($catalogQuantity < $productQuantity))
				{
					$result->addWarning(
						new Sale\ResultWarning(
							Main\Localization\Loc::getMessage(
								"RESERVE_QUANTITY_NOT_ENOUGH_ERROR",
								array_merge(
									self::getProductCatalogInfo($productId),
									array("#PRODUCT_ID#" => $productId)
								)
							), "RESERVE_QUANTITY_NOT_ENOUGH_ERROR"
						)
					);

					$fields['QUANTITY'] = 0;
				}

				$internalResult = Catalog\Model\Product::update($productId, $fields);
				if (!$internalResult->isSuccess())
				{
					$isUpdated = false;
					self::convertErrors($internalResult);
				}
				unset($internalResult);
			}

			if ($isUpdated)
			{
				$result->setData($fields);
			}

			return $result;
		}

		/**
		 * Checks offers parent products existence and activity.
		 *
		 * @param array $productIds
		 * @param int $iblockId
		 *
		 * @return array
		 */
		private static function checkParentActivity(array $productIds, int $iblockId = 0): array
		{
			$resultList = array();

			$productIdsToLoad = array();

			foreach ($productIds as $productId)
			{
				$cacheKey = $productId.'|'.$iblockId;

				if (static::isExistsHitCache(self::CACHE_PARENT_PRODUCT_ACTIVE, $cacheKey))
				{
					if (static::getHitCache(self::CACHE_PARENT_PRODUCT_ACTIVE, $cacheKey) === 'Y')
					{
						$resultList[] = $productId;
					}
				}
				else
				{
					$productIdsToLoad[] = $productId;
				}
			}

			if (!empty($productIdsToLoad))
			{
				$productToOfferMap = array();
				$parentIds = array();

				$cacheResult = array_fill_keys($productIdsToLoad, 'N');

				$productList = \CCatalogSku::getProductList($productIdsToLoad);
				if (!empty($productList))
				{
					foreach ($productList as $offerId => $productInfo)
					{
						$productToOfferMap[$productInfo['ID']][] = $offerId;
						$parentIds[] = $productInfo['ID'];
					}

					$itemList = \CIBlockElement::GetList(
						array(),
						array(
							'ID' => array_unique($parentIds),
							'IBLOCK_ID' => $iblockId,
							'ACTIVE' => 'Y',
							'ACTIVE_DATE' => 'Y',
							'CHECK_PERMISSIONS' => 'N',
						),
						false,
						false,
						array('ID')
					);
					while ($item = $itemList->Fetch())
					{
						if (!empty($productToOfferMap[$item['ID']]))
						{
							foreach ($productToOfferMap[$item['ID']] as $productId)
							{
								$cacheResult[$productId] = 'Y';
								$resultList[] = $productId;
							}
						}
					}
				}

				foreach ($cacheResult as $productId => $value)
				{
					static::setHitCache(self::CACHE_PARENT_PRODUCT_ACTIVE, $productId.'|'.$iblockId, $value);
				}
			}

			return $resultList;
		}

		/**
		 * @param $priceType
		 *
		 * @return string
		 */
		protected static function getPriceTitle($priceType)
		{
			$priceType = (int)$priceType;
			if ($priceType <= 0)
				return '';
			if (!isset(self::$priceTitleCache[$priceType]))
			{
				self::$priceTitleCache[$priceType] = '';
				$priceTypeList = Catalog\GroupTable::getTypeList();
				if (isset($priceTypeList[$priceType]))
				{
					$groupName = (string)$priceTypeList[$priceType]['NAME_LANG'];
					self::$priceTitleCache[$priceType] = ($groupName != '' ? $groupName : $priceTypeList[$priceType]['NAME']);
					unset($groupName);
				}
				unset($priceTypeList);
			}
			return self::$priceTitleCache[$priceType];
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function tryShip(array $products)
		{
			$result = new Sale\Result();
			$resultList = array();

			$filteredProducts = $this->createQuantityFilteredProducts($products);

			if (empty($filteredProducts))
			{
				$result->setData(
					array(
						'TRY_SHIP_PRODUCTS_LIST' => array_fill_keys(array_keys($products), true),
					)
				);

				return $result;
			}

			$availableItems = $this->createProductsListWithCatalogData($filteredProducts);
			if (empty($availableItems))
			{
				$productIdList = array_keys($products);
				foreach($productIdList as $productId)
				{
					$result->addError(
						new Sale\ResultError(
							Main\Localization\Loc::getMessage(
								"SALE_PROVIDER_PRODUCT_NOT_AVAILABLE",
								array_merge(
									self::getProductCatalogInfo($productId),
									array("#PRODUCT_ID#" => $productId)
								)
							), "SALE_PROVIDER_PRODUCT_NOT_AVAILABLE"
						)
					);
				}

				$result->setData(
					array(
						'TRY_SHIP_PRODUCTS_LIST' => array_fill_keys($productIdList, false),
					)
				);

				return $result;
			}
			else
			{
				foreach ($availableItems as $productId => $productData)
				{
					$messageId = null;
					if (
						isset($productData['PRODUCT']['TYPE'])
						&& $productData['PRODUCT']['TYPE'] === Catalog\ProductTable::TYPE_SERVICE
					)
					{
						if (
							(!isset($productData['CATALOG']['ACTIVE']) || $productData['CATALOG']['ACTIVE'] !== 'Y')
							|| (!isset($productData['PRODUCT']['AVAILABLE']) || $productData['PRODUCT']['AVAILABLE'] !== 'Y')
						)
						{
							$messageId = 'SALE_PROVIDER_PRODUCT_SERVICE_NOT_AVAILABLE';
						}
					}
					else
					{
						if (!isset($productData['CATALOG']['ACTIVE']) || $productData['CATALOG']['ACTIVE'] !== 'Y')
						{
							$messageId = 'SALE_PROVIDER_PRODUCT_NOT_AVAILABLE';
						}
					}
					if ($messageId !== null)
					{
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									$messageId,
									array_merge(
										self::getProductCatalogInfo($productId),
										array("#PRODUCT_ID#" => $productId)
									)
								), "SALE_PROVIDER_PRODUCT_NOT_AVAILABLE"
							)
						);

						$resultList[$productId] = false;
						unset($availableItems[$productId]);
					}
				}
			}

			if (!empty($availableItems))
			{
				if (Catalog\Config\State::isUsedInventoryManagement())
				{
					$r = $this->checkProductsInStore($availableItems);
					if ($r->isSuccess())
					{
						$data = $r->getData();
						if (!empty($data['PRODUCTS_LIST_IN_STORE']))
						{
							$resultList = $resultList + $data['PRODUCTS_LIST_IN_STORE'];
						}
					}
					else
					{
						$result->addErrors($r->getErrors());
					}
				}
				else
				{
					$r = $this->checkProductsQuantity($availableItems);
					if ($r->isSuccess())
					{
						$data = $r->getData();
						if (!empty($data['PRODUCTS_LIST_REQUIRED_QUANTITY']))
						{
							$resultList = $resultList + $data['PRODUCTS_LIST_REQUIRED_QUANTITY'];
						}
					}
					else
					{
						$result->addErrors($r->getErrors());
					}

				}
			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'TRY_SHIP_PRODUCTS_LIST' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function isNeedShip(array $products)
		{
			$result = new Sale\Result();

			$result->setData(
				array(
					'IS_NEED_SHIP' => static::isReservationEnabled(),
				)
			);
			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return array
		 */
		private function createQuantityFilteredProducts(array $products): array
		{
			$resultList = array();
			foreach ($products as $productId => $productData)
			{
				$resultList[$productId] = $productData;
				if (array_key_exists('QUANTITY', $productData))
				{
					if ($productData['QUANTITY'] > 0)
					{
						unset($resultList[$productId]);
					}
					else
					{
						$resultList[$productId] *= -1;
					}
				}
				elseif (!empty($productData[Base::FLAT_QUANTITY_LIST]))
				{
					foreach ($productData[Base::FLAT_QUANTITY_LIST] as $basketCode => $quantity)
					{
						if ($quantity > 0)
						{
							unset($resultList[$productId][Base::FLAT_QUANTITY_LIST][$basketCode]);
						}
						else
						{
							$resultList[$productId][Base::FLAT_QUANTITY_LIST][$basketCode] *= -1;
						}
					}

					if (empty($resultList[$productId][Base::FLAT_QUANTITY_LIST]))
					{
						unset($resultList[$productId]);
					}
				}
			}

			return $resultList;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function tryUnship(array $products)
		{
			$result = new Sale\Result();
			$resultList = array();
			$availableItems = $this->createProductsListWithCatalogData($products);

			if (Catalog\Config\State::isUsedInventoryManagement())
			{
				$r = $this->checkProductsInStore($availableItems);
				if ($r->isSuccess())
				{
					$data = $r->getData();
					if (!empty($data['PRODUCTS_LIST_IN_STORE']))
					{
						$resultList = $data['PRODUCTS_LIST_IN_STORE'];
					}
				}
			}
			else
			{
				$r = $this->checkProductsQuantity($availableItems);
				if ($r->isSuccess())
				{
					$data = $r->getData();
					if (!empty($data['PRODUCTS_LIST_REQUIRED_QUANTITY']))
					{
						$resultList = $data['PRODUCTS_LIST_REQUIRED_QUANTITY'];
					}
				}
				else
				{
					return $r;
				}

			}

			if (!empty($resultList))
			{
				$result->setData(array(
					'PRODUCTS_LIST_SHIPPED' => $resultList,
				));
			}

			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function checkProductsInStore(array $products)
		{
			$result = new Sale\Result();
			$resultList = array();

			$r = $this->checkProductInStores($products);
			if (!$r->isSuccess())
			{
				return $r;
			}

			$storeProductMap = $this->createStoreProductMap($products);

			$r = $this->checkExistsProductsInStore($products, $storeProductMap);
			if ($r->isSuccess())
			{
				$data = $r->getData();
				if (!empty($data['PRODUCTS_LIST_EXISTS_IN_STORE']))
				{
					$resultList = $data['PRODUCTS_LIST_EXISTS_IN_STORE'];
				}
			}
			else
			{
				return $r;
			}

			$r = $this->checkProductQuantityInStore($products);
			if ($r->isSuccess())
			{
				$data = $r->getData();
				if (!empty($data['PRODUCTS_LIST_REQUIRED_QUANTITY_IN_STORE']))
				{
					$resultList = $data['PRODUCTS_LIST_REQUIRED_QUANTITY_IN_STORE'];
				}
			}
			else
			{
				$result->addErrors($r->getErrors());
			}

			if (!empty($resultList))
			{
				$result->addData(
					array(
						'PRODUCTS_LIST_IN_STORE' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		private function checkProductsQuantity(array $products): Sale\Result
		{
			$result = new Sale\Result();

			$resultList = array();
			$availableQuantityList = array();
			$r = $this->getAvailableQuantity($products);
			if ($r->isSuccess())
			{
				$resultData = $r->getData();
				if (!empty($resultData['AVAILABLE_QUANTITY_LIST']))
				{
					$availableQuantityList = $resultData['AVAILABLE_QUANTITY_LIST'];
				}

			}
			else
			{
				return $r;
			}

			$enabledReservation = static::isReservationEnabled();

			foreach ($products as $productId => $productData)
			{
				if (empty($productData['CATALOG']))
				{
					$resultList[$productId] = false;
					$result->addError(
						new Sale\ResultError(
							Main\Localization\Loc::getMessage(
								"SALE_PROVIDER_PRODUCT_NOT_AVAILABLE",
								array_merge(
									self::getProductCatalogInfo($productId),
									array("#PRODUCT_ID#" => $productId)
								)
							), "SALE_PROVIDER_PRODUCT_NOT_AVAILABLE"
						)
					);

					continue;
				}

				$resultList[$productId] = true;
				$catalogData = $productData['CATALOG'];


				if ($catalogData["CHECK_QUANTITY"])
				{
					$productQuantity = self::getTotalAmountFromQuantityList($productData);

					$availableQuantity = 0;

					if (isset($availableQuantityList[$productId]))
					{
						$availableQuantity = $availableQuantityList[$productId];
					}

					$availableQuantity += (float)$catalogData['QUANTITY_RESERVED'];

					if ($enabledReservation && $productQuantity > $availableQuantity)
					{
						$resultList[$productId] = false;
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									"DDCT_DEDUCTION_QUANTITY_ERROR",
									array_merge(
										self::getProductCatalogInfo($productId),
										array("#PRODUCT_ID#" => $productId)
									)
								), "DDCT_DEDUCTION_QUANTITY_ERROR"
							)
						);
					}
				}
			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'PRODUCTS_LIST_REQUIRED_QUANTITY' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return array
		 */
		private function createProductsListWithCatalogData(array $products): array
		{
			$productDataList = array();
			$productIdList = array_fill_keys(array_keys($products), true);
			$r = $this->getData($products, [self::USE_GATALOG_DATA, 'FULL_QUANTITY']);
			if ($r->isSuccess())
			{
				$data = $r->getData();
				if (!empty($data[static::RESULT_CATALOG_LIST]))
				{
					$productDataList = $data[static::RESULT_CATALOG_LIST];
				}
			}

			$resultList = array();
			$availableListId = array_intersect_key($productIdList, $productDataList);
			if (!empty($availableListId))
			{
				foreach (array_keys($availableListId) as $productId)
				{
					if (empty($productDataList[$productId]) || !is_array($productDataList[$productId]))
					{
						continue;
					}
					$resultList[$productId] = $products[$productId];
					$resultList[$productId]['PRODUCT'] = $productDataList[$productId]['PRODUCT'];
					unset($productDataList[$productId]['PRODUCT']);
					$resultList[$productId]['CATALOG'] = $productDataList[$productId];
				}
			}

			return $resultList;
		}

		/**
		 * @param array $products
		 *
		 * @return array
		 */
		protected function createStoreProductMap(array $products)
		{
			$productStoreDataList = array();
			$r = $this->getProductListStores($products);
			if ($r->isSuccess())
			{
				$data = $r->getData();
				if (!empty($data['PRODUCT_STORES_LIST']))
				{
					$productStoreDataList = $data['PRODUCT_STORES_LIST'];
				}
			}

			$canAutoShipList = array();
			$r = $this->canProductListAutoShip($products);
			if ($r->isSuccess())
			{
				$data = $r->getData();
				if (!empty($data['PRODUCT_CAN_AUTOSHIP_LIST']))
				{
					$canAutoShipList = $data['PRODUCT_CAN_AUTOSHIP_LIST'];
				}
			}

			$storeProductList = array();
			foreach ($products as $productId => $productData)
			{
				if (!empty($productData['STORE_DATA_LIST']) && static::isExistsBarcode($productData['STORE_DATA_LIST']))
				{
					$storeProductList[$productId] = $productData['STORE_DATA_LIST'];
				}
				elseif (!empty($canAutoShipList[$productId]) && !empty($productStoreDataList[$productId]))
				{
					$productQuantity = self::getTotalAmountFromQuantityList($productData);
					foreach ($productData['SHIPMENT_ITEM_DATA_LIST'] as $shipmentItemIndex => $shipmentItemQuantity)
					{
						foreach ($productStoreDataList[$productId] as $productStoreData)
						{
							$storeId = $productStoreData['STORE_ID'];
							$storeProductList[$productId][$shipmentItemIndex][$storeId] = array(
								'PRODUCT_ID' => $productId,
								'STORE_ID' => $storeId,
								'IS_BARCODE_MULTI' => false,
								'QUANTITY' => abs($productQuantity),
							);
						}
					}
				}
			}

			return $storeProductList;
		}

		private function checkProductInStores($products): Sale\Result
		{
			$result = new Sale\Result();
			$productStoreDataList = array();
			$canAutoShipList = array();
			$r = $this->canProductListAutoShip($products);
			if ($r->isSuccess())
			{
				$data = $r->getData();
				if (!empty($data['PRODUCT_CAN_AUTOSHIP_LIST']))
				{
					$canAutoShipList = $data['PRODUCT_CAN_AUTOSHIP_LIST'];
				}
			}

			$r = $this->getProductListStores($products);
			if ($r->isSuccess())
			{
				$data = $r->getData();
				if (!empty($data['PRODUCT_STORES_LIST']))
				{
					$productStoreDataList = $data['PRODUCT_STORES_LIST'];
				}
			}

			foreach ($products as $productId => $productData)
			{
				if (!empty($productData['STORE_DATA_LIST']))
				{
					if (!static::isExistsBarcode($productData['STORE_DATA_LIST']))
					{
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									"DDCT_DEDUCTION_MULTI_BARCODE_EMPTY", self::getProductCatalogInfo($productId)
								), "DDCT_DEDUCTION_MULTI_BARCODE_EMPTY"
							)
						);
					}
				}
				elseif ($canAutoShipList[$productId] === false)
				{
					if (!isset($productStoreDataList[$productId]))
					{
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									"DDCT_DEDUCTION_STORE_EMPTY_ERROR",
									self::getProductCatalogInfo($productId)
								), "DEDUCTION_STORE_ERROR1"
							)
						);
					}
					elseif (count($productStoreDataList[$productId]) > 1)
					{
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									"DDCT_DEDUCTION_STORE_ERROR",
									self::getProductCatalogInfo($productId)
								), "DEDUCTION_STORE_ERROR1"
							)
						);
					}
				}
			}

			return $result;

		}

		private static function isExistsBarcode(array $list): bool
		{
			$resultValue = false;
			foreach ($list as $storeDataList)
			{
				foreach ($storeDataList as $storeValue)
				{
					if (is_array($storeValue['BARCODE']) && $storeValue['IS_BARCODE_MULTI'] === true)
					{
						foreach ($storeValue["BARCODE"] as $barcodeValue)
						{
							if (trim($barcodeValue) == "")
							{
								return $resultValue;
							}
						}

						$resultValue = true;

					}
					else
					{
						return (!empty($storeValue['BARCODE']));
					}

				}
			}

			return $resultValue;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		protected function checkProductQuantityInStore(array $products)
		{
			$result = new Sale\Result();

			$resultList = array();
			$productQuantityList = array();
			$usedProductStoreQuantity = [];

			$productStoreDataList = self::loadCurrentProductStores(array_keys($products));

			foreach ($products as $productId => $productData)
			{
				if (empty($productData['CATALOG']))
					continue;
				if (
					isset($productData['PRODUCT'])
					&& !$productData['PRODUCT']['USED_STORE_INVENTORY'] //product types without stores
				)
				{
					continue;
				}

				$productStoreData = $productStoreDataList[$productId] ?? [];

				$storeDataList = $productData['STORE_DATA_LIST'];

				if (!empty($storeDataList))
				{
					foreach ($storeDataList as $barcodeList)
					{
						foreach($barcodeList as $storeId => $storeDataValue)
						{
							if (!empty($storeDataValue))
							{
								if (
									!isset($productStoreData[$storeId])
									|| ($productStoreData[$storeId]["AMOUNT"] < $storeDataValue["QUANTITY"])
								)
								{
									$result->addError(
										new Sale\ResultError(
											Main\Localization\Loc::getMessage(
												'DDCT_DEDUCTION_QUANTITY_STORE_ERROR_2',
												array_merge(
													self::getProductCatalogInfo($productId),
													[
														'#STORE_NAME#' => \CCatalogStoreControlUtil::getStoreName($storeId),
														'#STORE_ID#' => $storeId,
													]
												)
											), 'DDCT_DEDUCTION_QUANTITY_STORE_ERROR'
										)
									);
								}
								else
								{
									if (!isset($productQuantityList[$productId]))
									{
										$productQuantityList[$productId] = 0;
									}

									$productQuantityList[$productId] += $storeDataValue["QUANTITY"];

									if (!isset($usedProductStoreQuantity[$productId]))
									{
										$usedProductStoreQuantity[$productId] = [];
									}
									$usedProductStoreQuantity[$productId][$storeId] = true;

									$r = static::checkProductBarcodes($productData, $productStoreData[$storeId], $storeDataValue);
									if ($r->isSuccess())
									{
										if (!array_key_exists($productId, $resultList))
										{
											$resultList[$productId] = true;
										}
									}
									else
									{
										$result->addErrors($r->getErrors());
									}
								}
							}
							else
							{
								if (!array_key_exists($productId, $resultList))
								{
									$resultList[$productId] = true;
								}
							}
						}
					}
				}
				else
				{
					$resultList[$productId] = true;

					if (!isset($productQuantityList[$productId]))
					{
						$productQuantityList[$productId] = 0;
					}

					if (
						!empty($productData[Base::FLAT_QUANTITY_LIST])
						&& is_array($productData[Base::FLAT_QUANTITY_LIST])
					)
					{
						$productQuantityList[$productId] = array_sum(
							$productData[Base::FLAT_QUANTITY_LIST]
						);
					}

				}
			}

			if (!empty($productQuantityList))
			{
				foreach ($productQuantityList as $amountProductId => $amountValue)
				{
					$product = $products[$amountProductId];
					$catalogData = $product['CATALOG'];

					$catalogQuantity = self::getTotalAmountFromPriceList($catalogData);
					$catalogReservedQuantity = (float)$catalogData['QUANTITY_RESERVED'];

					if ($product[Base::FLAT_RESERVED_QUANTITY_LIST][$product['BASKET_CODE']] > 0)
					{
						$catalogQuantity += $catalogReservedQuantity;
					}
					else
					{
						$unusedReserve = 0.0;
						if (isset($usedProductStoreQuantity[$amountProductId]))
						{
							$usedProductStores = $usedProductStoreQuantity[$amountProductId];
							$productStores = $productStoreDataList[$amountProductId];
							foreach (array_keys($productStores) as $storeId)
							{
								if (isset($usedProductStores[$storeId]))
								{
									continue;
								}
								$unusedReserve += $productStores[$storeId]['QUANTITY_RESERVED'];
							}
							unset($storeId);
							unset($productStores, $usedProductStores);
						}
						$catalogQuantity += $unusedReserve;
						unset($unusedReserve);
					}

					if ($amountValue > $catalogQuantity)
					{
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									"DDCT_DEDUCTION_SHIPMENT_QUANTITY_NOT_ENOUGH",
									self::getProductCatalogInfo($amountProductId)
								), "SALE_PROVIDER_SHIPMENT_QUANTITY_NOT_ENOUGH"
							)
						);
					}
				}
			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'PRODUCTS_LIST_REQUIRED_QUANTITY_IN_STORE' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @param array
		 * @param array $storeDataList
		 *
		 * @return Sale\Result
		 */
		protected function checkExistsProductItemInStore(array $productData, array $storeDataList = array())
		{
			$result = new Sale\Result();

			if (!empty($storeDataList))
			{
				foreach ($storeDataList as $storeData)
				{
					foreach ($storeData as $storeDataValue)
					{
						$storeId = $storeDataValue['STORE_ID'];

						if ((int)$storeId < -1 || (int)$storeId == 0
							|| !isset($storeDataValue["QUANTITY"]) || (int)$storeDataValue["QUANTITY"] < 0)
						{
							$result->addError(
								new Sale\ResultError(
									Main\Localization\Loc::getMessage(
										"DDCT_DEDUCTION_STORE_ERROR",
										self::getProductCatalogInfo($productData['PRODUCT_ID'])
									), "DDCT_DEDUCTION_STORE_ERROR"
								)
							);
							return $result;
						}
					}
				}
			}
			else
			{
				$result->addError( new Sale\ResultError(
					Main\Localization\Loc::getMessage("DDCT_DEDUCTION_STORE_ERROR", self::getProductCatalogInfo($productData['PRODUCT_ID'])),
					"DEDUCTION_STORE_ERROR1"
				));
			}

			return $result;
		}

		/**
		 * @param array $products
		 * @param array $storeData
		 *
		 * @return Sale\Result
		 */
		protected function checkExistsProductsInStore(array $products, array $storeData = array())
		{
			$result = new Sale\Result();

			$resultList = array();
			if (!empty($storeData))
			{
				foreach ($products as $productId => $productData)
				{
					$productStoreData = array();
					if (!empty($storeData[$productId]))
					{
						$productStoreData = $storeData[$productId];
					}

					$resultList[$productId] = true;

					if (
						(
							isset($productData['BUNDLE_PARENT'])
							&& $productData['BUNDLE_PARENT'] === true
						)
						|| (
							isset($productData['PRODUCT']['USED_STORE_INVENTORY'])
							&& !$productData['PRODUCT']['USED_STORE_INVENTORY']
						)
					)
					{
						continue;
					}

					$r = $this->checkExistsProductItemInStore($productData, $productStoreData);
					if (!$r->isSuccess())
					{
						$result->addErrors($r->getErrors());
						$resultList[$productId] = false;
					}
				}
			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'PRODUCTS_LIST_EXISTS_IN_STORE' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @param array $productData
		 * @param array $productStoreData
		 * @param array $storeData
		 *
		 * @return Sale\Result
		 */
		protected static function checkProductBarcodes(array $productData, array $productStoreData, array $storeData = array())
		{
			$result = new Sale\Result();

			$productId = $productData['PRODUCT_ID'];
			$storeId = $productStoreData['STORE_ID'];

			if (isset($storeData['BARCODE']) && count($storeData['BARCODE']) > 0)
			{
				foreach ($storeData['BARCODE'] as $barcodeValue)
				{
					if (trim($barcodeValue) == "" && $storeData['IS_BARCODE_MULTI'] === true)
					{
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									"DDCT_DEDUCTION_MULTI_BARCODE_EMPTY",
									array_merge(
										self::getProductCatalogInfo($productId),
										array("#STORE_ID#" => $storeId)
									)
								), "DDCT_DEDUCTION_MULTI_BARCODE_EMPTY"
							)
						);
						continue;
					}
					if (!empty($barcodeValue))
					{
						$fields = [
							'=STORE_ID' => static::CATALOG_PROVIDER_EMPTY_STORE_ID,
							'=BARCODE' => $barcodeValue,
							'=PRODUCT_ID' => $productId,
						];

						if ($storeData['IS_BARCODE_MULTI'] === true)
						{
							$fields['=STORE_ID'] = $storeId;
						}
						$iterator = Catalog\StoreBarcodeTable::getList([
							'select' => ['ID'],
							'filter' => $fields,
							'limit' => 1,
						]);
						$row = $iterator->fetch();
						unset($iterator);

						if (empty($row))
						{
							$result->addError( new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									"DDCT_DEDUCTION_BARCODE_ERROR",
									array_merge(self::getProductCatalogInfo($productId), array("#BARCODE#" => $barcodeValue))
								),
								"DDCT_DEDUCTION_BARCODE_ERROR"
							));
						}
					}
				}
			}
			else
			{
				$result->addError( new Sale\ResultError(
					Main\Localization\Loc::getMessage(
						"DDCT_DEDUCTION_MULTI_BARCODE_EMPTY",
						array_merge(self::getProductCatalogInfo($productId), array("#STORE_ID#" => $storeId))
					),
					"DDCT_DEDUCTION_MULTI_BARCODE_EMPTY"
				));
			}
			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 * @throws Main\ArgumentNullException
		 */
		private function canProductListAutoShip(array $products): Sale\Result
		{
			$context = $this->getContext();
			if (empty($context['SITE_ID']))
			{
				throw new Main\ArgumentNullException('SITE_ID');
			}

			static $canAutoList = array();

			$resultList = array();
			$hasNew = false;

			$countStores = 0;

			$countStoresResult = $this->getStoresCount();
			if ($countStoresResult->isSuccess())
			{
				$countStores = $countStoresResult->get('STORES_COUNT');
			}

			$countStoresResult->getData();
			$defaultDeductionStore = (int)Main\Config\Option::get("sale", "deduct_store_id", "", $context['SITE_ID']);
			$isDefaultStore = ($defaultDeductionStore > 0);
			foreach ($products as $productId => $productData)
			{
				if (isset($canAutoList[$productId]))
				{
					$resultList[$productId] = $canAutoList[$productId];
					continue;
				}

				if (!$productData['PRODUCT']['USED_STORE_INVENTORY']) // product types without stores
				{
					$canAutoList[$productId] = true;
					$resultList[$productId] = true;
					continue;
				}

				$isOneStore = ($countStores == 1 || $countStores == -1);

				$isOnlyOneStore = ($isOneStore || $isDefaultStore);
				$isMulti = false;
				if (isset($productData['STORE_DATA_LIST']))
				{
					$storeData = array();
					$shipmentItemStoreData = reset($productData['STORE_DATA_LIST']);
					if (!empty($shipmentItemStoreData))
					{
						$storeData = reset($shipmentItemStoreData);
					}

					if (!empty($storeData))
					{
						$isMulti = isset($storeData['IS_BARCODE_MULTI']) && $storeData['IS_BARCODE_MULTI'] === true;
					}
				}
				elseif (isset($productData['IS_BARCODE_MULTI']))
				{
					$isMulti = $productData['IS_BARCODE_MULTI'] === true;
				}

				$resultList[$productId] = ($isOnlyOneStore && !$isMulti);
				$hasNew = true;

				if ($isMulti)
				{
					$hasNew = false;
				}
			}

			if ($hasNew)
			{
				$productStoreList = [];
				$r = $this->getProductListStores($products);
				if ($r->isSuccess())
				{
					$productStoreData = $r->getData();
					if (
						!empty($productStoreData['PRODUCT_STORES_LIST'])
						&& is_array($productStoreData['PRODUCT_STORES_LIST'])
					)
					{
						$productStoreList = $productStoreData['PRODUCT_STORES_LIST'];
					}
				}

				if (!empty($productStoreList))
				{
					foreach ($products as $productId => $productData)
					{
						if (!empty($productStoreList[$productId]))
						{
							$countProductInStore = 0;
							foreach ($productStoreList[$productId] as $storeData)
							{
								if ((float)$storeData['AMOUNT'] > 0)
								{
									$countProductInStore++;
								}
							}
							$resultList[$productId] = ($countProductInStore == 1);
							$canAutoList[$productId] = $resultList[$productId];
						}
					}
				}
			}

			$result = new Sale\Result();
			if (!empty($resultList))
			{
				$result->setData(
					array(
						'PRODUCT_CAN_AUTOSHIP_LIST' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @param array $product
		 * @param array $productStoreDataList
		 *
		 * @return bool|array
		 */
		private static function getAutoShipStoreData(array $product, array $productStoreDataList)
		{
			$isMulti = false;
			if (isset($product['STORE_DATA_LIST']))
			{
				$storeData = [];
				$shipmentItemStoreData = reset($product['STORE_DATA_LIST']);
				if (!empty($shipmentItemStoreData))
				{
					$storeData = reset($shipmentItemStoreData);
				}

				if (!empty($storeData))
				{
					$isMulti = isset($storeData['IS_BARCODE_MULTI']) && $storeData['IS_BARCODE_MULTI'] === true;
				}
			}
			elseif (isset($product['IS_BARCODE_MULTI']))
			{
				$isMulti = $product['IS_BARCODE_MULTI'] === true;
			}

			if ($isMulti)
			{
				return false;
			}

			$outputStoreData = false;

			if (!empty($productStoreDataList))
			{
				$countProductInStore = 0;

				$storeProductData = false;
				foreach ($productStoreDataList as $storeData)
				{
					if ((float)$storeData['AMOUNT'] > 0)
					{
						$countProductInStore++;
						if (!$storeProductData)
						{
							$storeProductData = $storeData;
						}
					}
				}

				if ($countProductInStore == 1 && !empty($storeProductData))
				{
					$outputStoreData = $storeProductData;
				}
			}

			return $outputStoreData;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		protected function getCountProductsInStore(array $products)
		{
			$result = new Sale\Result();

			$productStoreList = array();
			$productStoreResult = $this->getProductListStores($products);
			if ($productStoreResult->isSuccess())
			{
				$productStoreData = $productStoreResult->getData();

				if (array_key_exists('PRODUCT_STORES_LIST', $productStoreData))
				{
					$productStoreList = $productStoreData['PRODUCT_STORES_LIST'];
				}
			}

			if (empty($productStoreList))
			{
				return $result;
			}

			$resultList = array();
			foreach ($productStoreList as $productStoreDataList)
			{
				foreach ($productStoreDataList as $storeId =>$productStoreData)
				{
					$productId = $productStoreData['PRODUCT_ID'];
					if ($productStoreData['AMOUNT'] > 0)
					{
						if (!isset($resultList[$productId]))
						{
							$resultList[$productId] = [];
						}

						$resultList[$productId][$storeId] = $productStoreData['AMOUNT'];
					}
				}
			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'RESULT_LIST' => $resultList,
					)
				);
			}

			return $result;
		}

		/**
		 * @internal
		 * @return Sale\Result
		 */
		public function getStoresCount()
		{
			$result = new Sale\Result();

			$count = -1;

			if (Catalog\Config\State::isUsedInventoryManagement())
			{
				$count = count($this->getStoreIds());
			}

			$result->setData(
				array(
					'STORES_COUNT' => $count,
				)
			);

			return $result;
		}

		/**
		 * @return array
		 */
		private function getStoreIds(): array
		{
			$context = $this->getContext();

			$filterId = [
				'ACTIVE' => 'Y',
			];
			if (isset($context['SITE_ID']) && $context['SITE_ID'] !== '')
			{
				$filterId['+SITE_ID'] = $context['SITE_ID'];
			}

			$cacheId = md5(serialize($filterId));
			$storeIds = static::getHitCache(self::CACHE_STORE, $cacheId);
			if (empty($storeIds))
			{
				$storeIds = [];

				$filter = Main\Entity\Query::filter();
				$filter->where('ACTIVE', '=', 'Y');
				if (isset($context['SITE_ID']) && $context['SITE_ID'] != '')
				{
					$subFilter = Main\Entity\Query::filter();
					$subFilter->logic('or')->where('SITE_ID', '=', $context['SITE_ID'])->where('SITE_ID', '=', '')->whereNull('SITE_ID');
					$filter->where($subFilter);
					unset($subFilter);
				}

				$iterator = Catalog\StoreTable::getList([
					'select' => ['ID', 'SORT'],
					'filter' => $filter,
					'order' => ['SORT' => 'ASC', 'ID' => 'ASC'],
				]);
				while ($row = $iterator->fetch())
				{
					$storeIds[] = (int)$row['ID'];
				}
				unset($row, $iterator, $filter);
				if (!empty($storeIds))
				{
					static::setHitCache(self::CACHE_STORE, $cacheId, $storeIds);
				}
			}
			unset($cacheId, $filterId);

			return $storeIds;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function getProductListStores(array $products)
		{
			$result = new Sale\Result();

			//without store control stores are used for information purposes only
			if (!Catalog\Config\State::isUsedInventoryManagement())
			{
				return $result;
			}

			$storeIds = $this->getStoreIds();
			if (empty($storeIds))
			{
				return $result;
			}

			$resultList = [];
			$productGetIdList = [];
			foreach (array_keys($products) as $productId)
			{
/*				$cacheId = md5($productId);

				$storeProductDataList = static::getHitCache(self::CACHE_STORE_PRODUCT, $cacheId);
				if (!empty($storeProductDataList))
				{
					$resultList[$productId] = $storeProductDataList;
				}
				else
				{
					$productGetIdList[$productId] = $productId;
				} */
				// remove cache because need clear cache after modify stores
				if (
					isset($products[$productId]['PRODUCT']['USED_STORE_INVENTORY'])
					&& !$products[$productId]['PRODUCT']['USED_STORE_INVENTORY']
				) // product types without stores
				{
					continue;
				}
				$productGetIdList[$productId] = $productId;
			}

			if (!empty($productGetIdList))
			{
				$emptyProductStores = [];
				$iterator = Catalog\StoreTable::getList([
					'select' => [
						'ID',
						'TITLE',
					],
					'filter' => [
						'@ID' => $storeIds,
					],
					'order' => [
						'ID' => 'ASC',
					],
				]);
				while ($row = $iterator->fetch())
				{
					$id = (int)$row['ID'];
					$emptyProductStores[$id] = [
						'ID' => 0,
						'PRODUCT_ID' => 0,
						'STORE_ID' => $row['ID'],
						'AMOUNT' => 0,
						'QUANTITY_RESERVED' => 0,
						'STORE_NAME' => $row['TITLE'],
					];
				}
				unset($row, $iterator);

				foreach (array_chunk($productGetIdList, 500) as $pageIds)
				{
					foreach ($pageIds as $productId)
					{
						$rows = $emptyProductStores;
						foreach (array_keys($rows) as $storeId)
						{
							$rows[$storeId]['PRODUCT_ID'] = $productId;
						}
						$resultList[$productId] = $rows;
						unset($rows);
					}
					unset($productId);
					$iterator = Catalog\StoreProductTable::getList([
						'select' => [
							'ID',
							'PRODUCT_ID',
							'STORE_ID',
							'AMOUNT',
							'QUANTITY_RESERVED',
						],
						'filter' => [
							'=PRODUCT_ID' => $pageIds,
							'@STORE_ID' => $storeIds,
						],
						'order' => [
							'PRODUCT_ID' => 'ASC',
							'STORE_ID' => 'ASC',
						],
					]);
					while ($row = $iterator->fetch())
					{
						$row['ID'] = (int)$row['ID'];
						$row['PRODUCT_ID'] = (int)$row['PRODUCT_ID'];
						$row['STORE_ID'] = (int)$row['STORE_ID'];
						if (!isset($resultList[$row['PRODUCT_ID']]))
						{
							$resultList[$row['PRODUCT_ID']] = [];
						}
						$resultList[$row['PRODUCT_ID']][$row['STORE_ID']]['ID'] = $row['ID'];
						$resultList[$row['PRODUCT_ID']][$row['STORE_ID']]['AMOUNT'] = (float)$row['AMOUNT'];
						$resultList[$row['PRODUCT_ID']][$row['STORE_ID']]['QUANTITY_RESERVED'] = (float)$row['QUANTITY_RESERVED'];
					}
					unset($iterator, $row);
				}
				unset($pageIds);

/*				foreach ($productGetIdList as $productId)
				{
					if (!empty($resultList[$productId]))
					{
						$cacheId = md5($productId);
						static::setHitCache(self::CACHE_STORE_PRODUCT, $cacheId, $resultList[$productId]);
					}
				} */
			}

			if (!empty($resultList))
			{
				$result->setData([
					'PRODUCT_STORES_LIST' => $resultList,
				]);
			}

			return $result;
		}

		/**
		 * @param $type
		 * @param $key
		 * @param array $fields
		 *
		 * @return bool|mixed
		 */
		protected static function getHitCache($type, $key, array $fields = array())
		{
			if (!empty(self::$hitCache[$type]) && !empty(self::$hitCache[$type][$key]))
			{
				if (static::isExistsHitCache($type, $key, $fields))
				{
					return self::$hitCache[$type][$key];
				}
			}

			return false;
		}

		/**
		 * @param $type
		 * @param $key
		 * @param array $fields
		 *
		 * @return bool
		 */
		protected static function isExistsHitCache($type, $key, array $fields = []): bool
		{
			$isExists = false;
			if (!empty(self::$hitCache[$type]) && !empty(self::$hitCache[$type][$key]))
			{
				$isExists = true;
				if (!empty($fields) && is_array($fields) && is_array(self::$hitCache[$type][$key]))
				{
					foreach ($fields as $name)
					{
						if (!array_key_exists($name, self::$hitCache[$type][$key]))
						{
							$isExists = false;
							break;
						}
					}
				}
			}

			return $isExists;
		}

		/**
		 * @param string $type
		 * @param string|int $key
		 * @param mixed $value
		 */
		protected static function setHitCache(string $type, $key, $value): void
		{
			if (!isset(self::$hitCache[$type]))
			{
				self::$hitCache[$type] = [];
			}

			if (!isset(self::$hitCache[$type][$key]))
			{
				self::$hitCache[$type][$key] = [];
			}

			self::$hitCache[$type][$key] = $value;
		}

		/**
		 * @param string|null $type
		 */
		protected static function clearHitCache(?string $type = null): void
		{
			if ($type === null)
			{
				self::$hitCache = [];
			}
			elseif (isset(self::$hitCache[$type]))
			{
				unset(self::$hitCache[$type]);
			}
		}

		/**
		 * @param $fields
		 *
		 * @return array
		 */
		protected static function clearNotCacheFields($fields)
		{
			$resultFields = array();
			$clearFields = static::getNotCacheFields();
			foreach ($fields as $name => $value)
			{
				$clearName = $name;
				if (mb_substr($clearName, 0, 1) == '~')
				{
					$clearName = mb_substr($clearName, 1, mb_strlen($clearName));
				}

				if (!in_array($clearName, $clearFields))
				{
					$resultFields[$name] = $value;
				}
			}

			return $resultFields;
		}

		/**
		 * @return array
		 */
		protected static function getNotCacheFields()
		{
			return array(
				'CAN_BUY_ZERO',
				'QUANTITY_TRACE',
				'QUANTITY',
				'CAN_BUY',
			);
		}

		protected static function checkNeedFields(array $fields, array $need)
		{
			foreach ($need as $name => $value)
			{
				if (!array_key_exists($name, $fields))
				{
					return false;
				}
			}

			return true;
		}

		/**
		 * @deprecated deprecated since 21.700.0
		 *
		 * @param $currentQuantity
		 * @param $newQuantity
		 * @param $quantityTrace
		 * @param $canBuyZero
		 * @param float|int $ratio
		 * @return bool
		 * @noinspection PhpUnusedParameterInspection
		 */
		protected static function isNeedClearPublicCache($currentQuantity, $newQuantity, $quantityTrace, $canBuyZero, $ratio = 1): bool
		{
			return false;
		}

		/**
		 * @deprecated deprecated since 21.700.0
		 *
		 * @param $productID
		 * @param array|false $productInfo
		 * @return void
		 */
		protected static function clearPublicCache($productID, $productInfo = array()): void {}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function getAvailableQuantity(array $products)
		{
			$result = $this->getAvailableQuantityByStore($products);
			if (!$result->isSuccess())
			{
				return $result;
			}
			$data = $result->getData();
			if (empty($data[Base::STORE_AVAILABLE_QUANTITY_LIST]))
			{
				return $result;
			}

			$reservedList = $data[Base::STORE_AVAILABLE_QUANTITY_LIST];
			$resultList = [];
			foreach ($reservedList as $productId => $rows)
			{
				$resultList[$productId] = reset($rows);
			}
			unset($productId, $rows);
			unset($reservedList, $data);

			$result->setData([
				Base::FLAT_AVAILABLE_QUANTITY_LIST => $resultList,
			]);

			return $result;
		}

		public function getAvailableQuantityByStore(array $products): Sale\Result
		{
			$result = new Sale\Result();
			$resultList = [];

			$isGotQuantityDataList = [];

			foreach ($products as $productId => $productData)
			{
				$catalogAvailableQuantity = QuantityControl::getAvailableQuantity($productId);
				$catalogQuantity = QuantityControl::getQuantity($productId);

				if ($catalogQuantity === null || $catalogAvailableQuantity === null)
				{
					continue;
				}

				$productQuantity = self::getStoreAmountFromQuantityList($productData);
				if ($productQuantity === null)
				{
					continue;
				}
				if ($catalogAvailableQuantity < array_sum($productQuantity))
				{
					continue;
				}

				$isGotQuantityDataList[$productId] = true;

				$resultList[$productId] = $productQuantity;
			}

			if (count($resultList) != count($products))
			{
				if ($this->isExistsCatalogData($products))
				{
					$items = $products;
				}
				else
				{
					$items = $this->createProductsListWithCatalogData($products);
				}

				foreach ($items as $productId => $productData)
				{
					if (isset($isGotQuantityDataList[$productId]))
					{
						continue;
					}

					if (empty($productData['CATALOG']) || !is_array($productData['CATALOG']))
					{
						continue;
					}

					$catalogData = $productData['CATALOG'];

					$productQuantity = self::getStoreAmountFromQuantityList($productData);
					if ($productQuantity === null)
					{
						$resultList[$productId] = [];
						continue;
					}
					$resultList[$productId] = $productQuantity;

					$catalogQuantity = self::getTotalAmountFromPriceList($catalogData, false);

					QuantityControl::setQuantity($productId, $catalogQuantity);

					if ($catalogData['CHECK_QUANTITY'])
					{
						$totalReservedQuantity = 0;
						$reservedQuantity = self::getStoreReservedQuantityFromProduct($productData);
						if ($reservedQuantity !== null)
						{
							$totalReservedQuantity = array_sum($reservedQuantity);
						}

						$needQuantity = (array_sum($productQuantity) - $totalReservedQuantity);
						if ($catalogQuantity < $needQuantity)
						{
							$limitQuantity = $catalogQuantity;
							$availableList = $resultList[$productId];
							arsort($availableList, SORT_NUMERIC);
							foreach (array_keys($availableList) as $storeId)
							{
								$storeQuantity = $resultList[$productId][$storeId];
								if ($limitQuantity > $storeQuantity)
								{
									$limitQuantity -= $storeQuantity;
								}
								else
								{
									$storeQuantity = $limitQuantity;
									if ($limitQuantity > 0)
									{
										$limitQuantity = 0;
									}
								}
								$resultList[$productId][$storeId] = $storeQuantity;
							}
						}
					}
				}
			}

			if (!empty($resultList))
			{
				$result->setData([
					Base::STORE_AVAILABLE_QUANTITY_LIST => $resultList,
				]);
			}

			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function getAvailableQuantityAndPrice(array $products)
		{
			$result = new Sale\Result();
			$availableQuantityListResult = $this->getAvailableQuantity($products);

			if ($this->isExistsCatalogData($products))
			{
				$items = $products;
			}
			else
			{
				$items = $this->createProductsListWithCatalogData($products);
			}

			$priceDataList = array();

			foreach ($items as $productId => $productData)
			{
				$catalogData = $productData['CATALOG'];

				if (!empty($catalogData['PRICE_LIST']))
				{
					$priceDataList[$productId] = $catalogData['PRICE_LIST'];
				}

			}

			$availableQuantityData = array();

			if ($availableQuantityListResult->isSuccess())
			{
				$availableQuantityList = $availableQuantityListResult->getData();

				if (isset($availableQuantityList[Base::FLAT_AVAILABLE_QUANTITY_LIST]))
				{
					$availableQuantityData = $availableQuantityList[Base::FLAT_AVAILABLE_QUANTITY_LIST];
				}
			}

			$result->setData([
				Base::SUMMMARY_PRODUCT_LIST => [
					Base::FLAT_PRICE_LIST => $priceDataList,
					Base::FLAT_AVAILABLE_QUANTITY_LIST => $availableQuantityData,
				],
			]);

			return $result;
		}

		public function writeOffProductBatches(array $products): Sale\Result
		{
			$result = new Sale\Result();

			if (!Catalog\Config\State::isUsedInventoryManagement() || !State::isProductBatchMethodSelected())
			{
				return $result;
			}

			foreach ($products as $productId => $productData)
			{
				if (empty($productData['SHIPMENT_ITEM_LIST']) || !is_array($productData['SHIPMENT_ITEM_LIST']))
				{
					continue;
				}

				$productBatch = new BatchManager($productId);
				/** @var Sale\ShipmentItem $item */
				foreach ($productData['SHIPMENT_ITEM_LIST'] as $item)
				{
					/** @var Sale\ShipmentItemStore $storeItem */
					foreach ($item->getShipmentItemStoreCollection() as $storeItem)
					{
						$quantity = $storeItem->getQuantity();
						if ($quantity <= 0)
						{
							continue;
						}

						$distributor = new DistributionStrategy\ShipmentStore($productBatch, $storeItem);
						$r = $distributor->writeOff($quantity);
						if (!$r->isSuccess())
						{
							$result->addErrors($r->getErrors());
						}
					}
				}
			}

			return $result;
		}

		public function returnProductBatches(array $products): Sale\Result
		{
			$result = new Sale\Result();

			if (!Catalog\Config\State::isUsedInventoryManagement() || !State::isProductBatchMethodSelected())
			{
				return $result;
			}

			foreach ($products as $productId => $productData)
			{
				if (empty($productData['SHIPMENT_ITEM_LIST']) || !is_array($productData['SHIPMENT_ITEM_LIST']))
				{
					continue;
				}

				$productBatch = new BatchManager($productId);
				/** @var Sale\ShipmentItem $item */
				foreach ($productData['SHIPMENT_ITEM_LIST'] as $item)
				{
					foreach ($item->getShipmentItemStoreCollection() as $storeItem)
					{
						$distributor = new DistributionStrategy\ShipmentStore($productBatch, $storeItem);
						$r = $distributor->return();
						if (!$r->isSuccess())
						{
							$result->addErrors($r->getErrors());
						}
					}
				}
			}

			return $result;
		}

		/**
		 * @param $products
		 *
		 * @return bool
		 */
		private function isExistsCatalogData($products): bool
		{
			foreach ($products as $productData)
			{
				if (empty($productData['CATALOG']))
				{
					return false;
				}
			}
			return true;
		}

		/**
		 * @param array $list
		 *
		 * @return array
		 */
		private function getIblockData(array $list): array
		{
			$resultList = [];
			$res = Catalog\CatalogIblockTable::getList([
				'select' => [
					'IBLOCK_ID',
					'SUBSCRIPTION',
					'PRODUCT_IBLOCK_ID',
					'CATALOG_XML_ID' => 'IBLOCK.XML_ID',
				],
				'filter' => ['@IBLOCK_ID' => $list],
			]);
			while($iblockData = $res->fetch())
			{
				$resultList[$iblockData['IBLOCK_ID']] = $iblockData;
				if ($this->enableCache)
				{
					static::setHitCache(self::CACHE_CATALOG_IBLOCK_LIST, $iblockData['IBLOCK_ID'], $iblockData);
				}
			}
			unset($res, $iblockData);

			return $resultList;
		}

		/**
		 * @param array $iblockProductMap
		 *
		 * @return array
		 */
		private static function checkSkuPermission(array $iblockProductMap): array
		{
			$resultList = array();

			foreach ($iblockProductMap as $iblockData)
			{
				if ($iblockData['PRODUCT_IBLOCK_ID'] > 0 && !empty($iblockData['PRODUCT_LIST']))
				{
					$resultList = array_merge(
						$resultList,
						static::checkParentActivity($iblockData['PRODUCT_LIST'], (int)$iblockData['PRODUCT_IBLOCK_ID'])
					);
				}
				else
				{
					foreach ($iblockData['PRODUCT_LIST'] as $productId)
					{
						$resultList[] = $productId;
					}
				}
			}

			return $resultList;
		}

		/**
		 * @param array $iblockList
		 * @param array $iblockDataList
		 *
		 * @return array
		 */
		private static function createIblockProductMap(array $iblockList, array $iblockDataList): array
		{
			$resultList = $iblockDataList;
			foreach ($iblockList as $iblockId => $iblockProductList)
			{
				if (isset($iblockDataList[$iblockId]))
				{
					$resultList[$iblockId]['PRODUCT_LIST'] = $iblockProductList;
				}
			}

			return $resultList;
		}

		/**
		 * @param array $products
		 * @param array $iblockProductMap
		 *
		 * @return array
		 */
		private static function changeSubscribeProductQuantity(array $products, array $iblockProductMap): array
		{
			$resultList = $products;

			foreach ($iblockProductMap as $iblockData)
			{
				if ($iblockData['SUBSCRIPTION'] != 'Y')
					continue;

				if (empty($iblockData['PRODUCT_LIST']))
					continue;

				foreach($iblockData['PRODUCT_LIST'] as $productId)
				{
					if (isset($resultList[$productId]))
					{
						if (
							!empty($resultList[$productId][Base::FLAT_QUANTITY_LIST])
							&& is_array($resultList[$productId][Base::FLAT_QUANTITY_LIST])
						)
						{
							foreach (array_keys($resultList[$productId][Base::FLAT_QUANTITY_LIST]) as $index)
							{
								$resultList[$productId][Base::FLAT_QUANTITY_LIST][$index] = 1;
							}
						}
					}
				}
			}

			return $resultList;
		}

		/**
		 * @param array $list
		 * @param array $select
		 *
		 * @return array
		 */
		private static function getCatalogProducts(array $list, array $select): array
		{
			$usedStoreInventory = Catalog\Config\State::isUsedInventoryManagement();

			$typesWithoutStores = [
				Catalog\ProductTable::TYPE_SET => true,
				Catalog\ProductTable::TYPE_SKU => true,
				Catalog\ProductTable::TYPE_SERVICE => true,
			];
			$typesWithoutReservation = [
				Catalog\ProductTable::TYPE_SET => true,
				Catalog\ProductTable::TYPE_SKU => true,
				Catalog\ProductTable::TYPE_SERVICE => true,
			];

			if (empty($select))
			{
				$select = ['*'];
			}
			else
			{
				$select[] = 'ID';
				$select[] = 'TYPE';
				$select[] = 'AVAILABLE';

				$select = array_unique($select);
			}
			Main\Type\Collection::normalizeArrayValuesByInt($list, true);
			if (empty($list))
			{
				return [];
			}
			$resultList = [];
			foreach (array_chunk($list, 500) as $pageIds)
			{
				$iterator = Catalog\Model\Product::getList([
					'select' => $select,
					'filter' => [
						'@ID' => $pageIds,
					],
				]);
				while ($row = $iterator->fetch())
				{
					$row['ID'] = (int)$row['ID'];
					$row['TYPE'] = (int)$row['TYPE'];
					$row['QUANTITY'] = (float)$row['QUANTITY'];
					$row['QUANTITY_RESERVED'] = (float)$row['QUANTITY_RESERVED'];
					$row['CHECK_QUANTITY'] = (
						$row['TYPE'] !== Catalog\ProductTable::TYPE_SERVICE
						&& $row['QUANTITY_TRACE'] === Catalog\ProductTable::STATUS_YES
						&& $row['CAN_BUY_ZERO'] === Catalog\ProductTable::STATUS_NO
					);
					Catalog\Product\SystemField::prepareRow($row, Catalog\Product\SystemField::OPERATION_PROVIDER);

					if (isset($typesWithoutStores[$row['TYPE']]))
					{
						$row['USED_STORE_INVENTORY'] = false;
					}
					else
					{
						$row['USED_STORE_INVENTORY'] = $usedStoreInventory;
					}
					if (isset($typesWithoutReservation[$row['TYPE']]))
					{
						$row['USED_RESERVATION'] = false;
						$row['QUANTITY_RESERVED'] = 0;
					}
					else
					{
						$row['USED_RESERVATION'] = true;
					}

					$resultList[$row['ID']] = $row;
				}
				unset($row, $iterator);
			}
			unset($pageIds);

			return $resultList;
		}

		/**
		 * @param null $id
		 *
		 * @return array
		 */
		private static function getMeasure($id = null): array
		{
			static $measureList = array();

			if (!empty($measureList[$id]))
			{
				return $measureList[$id];
			}

			$fields = array(
				'MEASURE' => $id,
				'MEASURE_NAME' => $id,
				'MEASURE_CODE' => 0,
			);

			if ((int)$id <= 0)
			{
				$measure = \CCatalogMeasure::getDefaultMeasure(true, true);
				$fields['MEASURE_NAME'] = $measure['~SYMBOL_RUS'];
				$fields['MEASURE_CODE'] = $measure['CODE'];
			}
			else
			{
				$resMeasures = \CCatalogMeasure::getList(
					array(),
					array('ID' => $id),
					false,
					false,
					array('ID', 'SYMBOL_RUS', 'CODE')
				);
				$measure = $resMeasures->fetch();

				if (!empty($measure))
				{
					$fields['MEASURE_NAME'] = $measure['SYMBOL_RUS'];
					$fields['MEASURE_CODE'] = $measure['CODE'];
				}
			}

			$measureList[$id] = $fields;

			return $fields;
		}

		/**
		 * @param array $products
		 * @param array $productPriceList
		 * @param array $discountList
		 *
		 * @return array
		 */
		private static function createProductPriceList(array $products, array $productPriceList, array $discountList = array()): array
		{
			$priceResultList = array();

			foreach ($productPriceList as $basketCode => $priceData)
			{
				if (!$priceData)
					continue;
				$priceResultList[$basketCode]['PRODUCT_PRICE_ID'] = $priceData['RESULT_PRICE']['ID'];
				$priceResultList[$basketCode]['NOTES'] = $priceData['PRICE']['CATALOG_GROUP_NAME'];
				$priceResultList[$basketCode]['DISCOUNT_NAME'] = null;
				$priceResultList[$basketCode]['DISCOUNT_COUPON'] = null;
				$priceResultList[$basketCode]['DISCOUNT_VALUE'] = null;
				$priceResultList[$basketCode]['DISCOUNT_LIST'] = array();

				$discount = array();
				if (!empty($discountList[$priceData['PRODUCT_ID']][$basketCode]))
				{
					$discount = $discountList[$priceData['PRODUCT_ID']][$basketCode];
				}

				$priceResultList[$basketCode]['PRICE_TYPE_ID'] = $priceData['RESULT_PRICE']['PRICE_TYPE_ID'];
				$priceResultList[$basketCode]['BASE_PRICE'] = $priceData['RESULT_PRICE']['BASE_PRICE'];
				$priceResultList[$basketCode]['PRICE'] = $priceData['RESULT_PRICE']['DISCOUNT_PRICE'];
				$priceResultList[$basketCode]['CURRENCY'] = $priceData['RESULT_PRICE']['CURRENCY'];
				$priceResultList[$basketCode]['DISCOUNT_PRICE'] = $priceData['RESULT_PRICE']['DISCOUNT'];
				if (isset($priceData['RESULT_PRICE']['PERCENT']))
				{
					$priceResultList[$basketCode]['DISCOUNT_VALUE'] = ($priceData['RESULT_PRICE']['PERCENT'] > 0
						? $priceData['RESULT_PRICE']['PERCENT'] . '%' : null);
				}
				$priceResultList[$basketCode]['VAT_RATE'] = $priceData['RESULT_PRICE']['VAT_RATE'];
				$priceResultList[$basketCode]['VAT_INCLUDED'] = $priceData['RESULT_PRICE']['VAT_INCLUDED'];

				if (!empty($discount))
				{
					$priceResultList[$basketCode]['DISCOUNT_LIST'] = $discount;
				}

				if (!empty($priceData['DISCOUNT']))
				{
					$priceResultList[$basketCode]['DISCOUNT_NAME'] = '[' .
						$priceData['DISCOUNT']['ID'] .
						'] ' .
						$priceData['DISCOUNT']['NAME'];
					if (!empty($priceData['DISCOUNT']['COUPON']))
					{
						$priceResultList[$basketCode]['DISCOUNT_COUPON'] = $priceData['DISCOUNT']['COUPON'];
					}

					if (empty($priceResultList[$basketCode]['DISCOUNT_LIST']))
					{
						$priceResultList[$basketCode]['DISCOUNT_LIST'] = array($priceData['DISCOUNT']);
					}
				}
			}

			$resultList = array();
			if (!empty($priceResultList))
			{
				foreach ($products as $productId => $productData)
				{
					if (!empty($products[$productId]))
					{
						$productData = $products[$productId];

						$quantityList = array();

						if (array_key_exists('QUANTITY', $productData))
						{
							$quantityList = array(
								$productData['BASKET_CODE'] => $productData['QUANTITY'],
							);
						}
						if (!empty($productData[Base::FLAT_QUANTITY_LIST]))
						{
							$quantityList = $productData[Base::FLAT_QUANTITY_LIST];
						}

						foreach($quantityList as $basketCode => $quantity)
						{
							$resultList[$basketCode] = $priceResultList[$basketCode];
						}
					}
				}
			}

			return $resultList;
		}

		/**
		 * @param array $products
		 * @param array $items
		 * @param array $priceList
		 * @param array $productQuantityList
		 *
		 * @return array
		 */
		private static function createProductResult(array $products, array $items, array $priceList, array $productQuantityList): array
		{
			$resultList = array();
			foreach ($products as $productId => $productData)
			{
				$itemCode = $productData['ITEM_CODE'];
				$basketCode = $productData['BASKET_CODE'];
				$resultList[$productId] = $items[$productId];

				if (isset($productData['PRODUCT_DATA']['ACTIVE']))
				{
					$resultList[$productId]['ACTIVE'] = $productData['PRODUCT_DATA']['ACTIVE'];
				}

				$resultList[$productId]['ITEM_CODE'] = $itemCode;

				QuantityControl::resetAllQuantity($productId);
				QuantityControl::setReservedQuantity($productId, $productQuantityList[$basketCode]['QUANTITY_RESERVED']);

				if (!isset($priceList[$basketCode]))
				{
					$priceList[$basketCode] = array();
				}

				if (!empty($productData[Base::FLAT_QUANTITY_LIST]))
				{
					foreach($productData[Base::FLAT_QUANTITY_LIST] as $basketCode => $quantity)
					{
						QuantityControl::addQuantity($productId, $productQuantityList[$basketCode]['QUANTITY']);
						QuantityControl::addAvailableQuantity($productId, $productQuantityList[$basketCode]['AVAILABLE_QUANTITY']);

						if (empty($priceList[$basketCode]))
						{
							continue;
						}

						$resultList[$productId]['PRICE_LIST'][$basketCode] = array_merge(
							array(
								'QUANTITY' => $productQuantityList[$basketCode]['QUANTITY'],
								'AVAILABLE_QUANTITY' => $productQuantityList[$basketCode]['AVAILABLE_QUANTITY'],
								"ITEM_CODE" => $itemCode,
								"BASKET_CODE" => $basketCode,
							),
							$priceList[$basketCode]
						);
					}
				}
				else
				{
					$resultList[$productId]['QUANTITY'] = $productQuantityList[$basketCode]['QUANTITY'];

					QuantityControl::addQuantity($productId, $productQuantityList[$basketCode]['QUANTITY']);
					QuantityControl::addAvailableQuantity($productId, $productQuantityList[$basketCode]['AVAILABLE_QUANTITY']);
					if (!empty($resultList[$productId]))
					{
						if (empty($priceList[$basketCode]))
						{
							continue;
						}

						$resultList[$productId] = $priceList[$basketCode] + $resultList[$productId];
					}
				}
			}

			return $resultList;
		}

		/**
		 * @param array $products
		 * @param array $catalogDataList
		 * @param array $options
		 *
		 * @return array
		 */
		private static function setCatalogDataToProducts(array $products, array $catalogDataList, array $options = array()): array
		{
			$catalogDataEnabled = self::isCatalogDataEnabled($options);
			$specialFields = [];
			foreach (Catalog\Product\SystemField::getProviderSelectFields() as $index => $value)
			{
				$specialFields[] = is_string($index) ? $index : $value;
			}

			$result = [];
			foreach ($products as $productId => $productData)
			{
				if (!isset($catalogDataList[$productId]))
				{
					continue;
				}

				$row = $catalogDataList[$productId];

				$result[$productId] = [
					'CAN_BUY' => (
						$productData['PRODUCT_DATA']['ACTIVE'] === 'Y'
						&& $row['AVAILABLE'] === 'Y'
						? 'Y'
						: 'N'
					),
					'CAN_BUY_ZERO' => $row['CAN_BUY_ZERO'],
					'QUANTITY_TRACE' => $row['QUANTITY_TRACE'],
					'CHECK_QUANTITY' => $row['CHECK_QUANTITY'],
					'QUANTITY_RESERVED' => (float)$row['QUANTITY_RESERVED'],
					'CATALOG_XML_ID' => $productData['PRODUCT_DATA']['CATALOG_XML_ID'],
					'PRODUCT_XML_ID' => $productData['PRODUCT_DATA']['~XML_ID'],
					'PRODUCT' => $row,
				];

				if (!$catalogDataEnabled)
				{
					$basketRow = [
						'NAME' => $productData['PRODUCT_DATA']['~NAME'],
						'DETAIL_PAGE_URL' => $productData['PRODUCT_DATA']['~DETAIL_PAGE_URL'],
						'MEASURE_ID' => $row['MEASURE'],
						'MEASURE_NAME' => $row['MEASURE_NAME'],
						'MEASURE_CODE' => $row['MEASURE_CODE'],
						'BARCODE_MULTI' => $row['BARCODE_MULTI'],
						'WEIGHT' => (float)$row['WEIGHT'],
						'DIMENSIONS' => serialize(
							[
								'WIDTH' => $row['WIDTH'],
								'HEIGHT' => $row['HEIGHT'],
								'LENGTH' => $row['LENGTH'],
							]
						),
					];
					switch ($row['TYPE'])
					{
						case Catalog\ProductTable::TYPE_SET:
							$basketRow['TYPE'] = Sale\BasketItem::TYPE_SET;
							break;
						case Catalog\ProductTable::TYPE_SERVICE:
							$basketRow['TYPE'] = Sale\BasketItem::TYPE_SERVICE;
							break;
						default:
							$basketRow['TYPE'] = null;
							break;
					}
					foreach ($specialFields as $index)
					{
						$basketRow[$index] = $row[$index];
					}

					$result[$productId] = array_merge(
						$result[$productId],
						$basketRow
					);
				}

				$result[$productId]["VAT_INCLUDED"] = "Y";
			}

			return $result;
		}

		/**
		 * @return bool
		 */
		protected static function isReservationEnabled()
		{
			return !(Main\Config\Option::get("catalog", "enable_reservation") == "N"
				&& Main\Config\Option::get("sale", "product_reserve_condition") != "S"
				&& !Catalog\Config\State::isUsedInventoryManagement());
		}

		/**
		 * @param array $products
		 *
		 * @return array
		 * @throws Main\ObjectNotFoundException
		 */
		public static function createOrderListFromProducts(array $products)
		{
			$productOrderList = array();
			foreach ($products as $productId => $productData)
			{
				if (!empty($productData['SHIPMENT_ITEM_LIST']))
				{
					/**
					 * @var $shipmentItemIndex
					 * @var Sale\ShipmentItem $shipmentItem
					 */
					foreach ($productData['SHIPMENT_ITEM_LIST'] as $shipmentItem)
					{
						$shipmentItemCollection = $shipmentItem->getCollection();
						if (!$shipmentItemCollection)
						{
							throw new Main\ObjectNotFoundException('Entity "ShipmentItemCollection" not found');
						}

						$shipment = $shipmentItemCollection->getShipment();
						if (!$shipment)
						{
							throw new Main\ObjectNotFoundException('Entity "Shipment" not found');
						}

						/** @var Sale\ShipmentCollection $shipmentCollection */
						$shipmentCollection = $shipment->getCollection();
						if (!$shipmentCollection)
						{
							throw new Main\ObjectNotFoundException('Entity "ShipmentCollection" not found');
						}

						$order = $shipmentCollection->getOrder();
						if (!$order)
						{
							throw new Main\ObjectNotFoundException('Entity "Order" not found');
						}

						if (empty($productOrderList[$productId][$order->getId()]))
						{
							$productOrderList[$productId][$order->getId()] = $order;
						}
					}
				}
			}

			return $productOrderList;
		}

		/**
		 * @param $productId
		 *
		 * @return array
		 */
		private static function getProductCatalogInfo($productId): array
		{
			$productId = (int)$productId;
			if ($productId <= 0)
			{
				return [];
			}

			$product = static::getHitCache(self::CACHE_ELEMENT_SHORT_DATA, $productId);
			if (empty($product))
			{
				$iterator = Iblock\ElementTable::getList([
					'select' => [
						'ID',
						'IBLOCK_ID',
						'NAME',
						'IBLOCK_SECTION_ID',
					],
					'filter' => \CIBlockElement::getPublicElementsOrmFilter(['=ID' => $productId]),
				]);
				$product = $iterator->fetch();
				if ($product)
				{
					static::setHitCache(self::CACHE_ELEMENT_SHORT_DATA, $productId, $product);
				}
			}

			return (empty($product)
				? []
				: [
					"#PRODUCT_ID#" => $product['ID'],
					"#PRODUCT_NAME#" => $product['NAME'],
				]
			);
		}

		private static function getTotalAmountFromQuantityList(array $data): float
		{
			return self::getAmountFromSource(
				$data,
				[
					self::AMOUNT_SRC_STORE_QUANTITY_LIST,
					self::AMOUNT_SRC_QUANTITY,
					self::AMOUNT_SRC_QUANTITY_LIST,
				]
			);
		}

		private static function getTotalAmountFromPriceList(array $product, bool $direction = true): float
		{
			if ($direction)
			{
				$list = [
					self::AMOUNT_SRC_QUANTITY,
					self::AMOUNT_SRC_PRICE_LIST,
				];
			}
			else
			{
				$list = [
					self::AMOUNT_SRC_PRICE_LIST,
					self::AMOUNT_SRC_QUANTITY,
				];
			}

			return self::getAmountFromSource($product, $list);
		}

		private static function isCatalogDataEnabled(array $options): bool
		{
			return in_array(self::USE_GATALOG_DATA, $options);
		}

		private static function fillCatalogXmlId(array $products, array $iblockProductMap): array
		{
			foreach ($iblockProductMap as $entityData)
			{
				if (empty($entityData['PRODUCT_LIST']) || !is_array($entityData['PRODUCT_LIST']))
				{
					continue;
				}
				foreach ($entityData['PRODUCT_LIST'] as $index)
				{
					if (!isset($products[$index]))
					{
						continue;
					}
					$products[$index]['PRODUCT_DATA']['CATALOG_XML_ID'] = $entityData['CATALOG_XML_ID'];
				}
				unset($index);
			}
			unset($entityData);

			return $products;
		}

		private static function fillOfferXmlId(array $products, array $catalogProductDataList): array
		{
			$offerList = [];
			foreach ($catalogProductDataList as $entityData)
			{
				if ($entityData['TYPE'] != Catalog\ProductTable::TYPE_OFFER)
				{
					continue;
				}
				if (strpos($products[$entityData['ID']]['PRODUCT_DATA']['~XML_ID'], '#') !== false)
				{
					continue;
				}
				$offerList[] = $entityData['ID'];
			}
			unset($entityData);
			if (!empty($offerList))
			{
				$parentMap = [];
				$parentIdList = [];
				$parentList = \CCatalogSku::getProductList($offerList, 0);
				foreach ($parentList as $offerId => $offerData)
				{
					$parentId = (int)$offerData['ID'];
					if (!isset($parentMap[$parentId]))
					{
						$parentMap[$parentId] = [];
					}
					$parentMap[$parentId][] = $offerId;
					$parentIdList[$parentId] = $parentId;
				}
				unset($offerId, $offerData, $parentList);
				if (!empty($parentMap))
				{
					sort($parentIdList);
					foreach (array_chunk($parentIdList, 500) as $pageIds)
					{
						$iterator = Iblock\ElementTable::getList([
							'select' => [
								'ID',
								'XML_ID',
							],
							'filter' => ['@ID' => $pageIds],
						]);
						while ($row = $iterator->fetch())
						{
							$parentId = (int)$row['ID'];
							if (empty($parentMap[$parentId]))
							{
								continue;
							}
							foreach ($parentMap[$parentId] as $index)
							{
								$products[$index]['PRODUCT_DATA']['~XML_ID'] = $row['XML_ID'] . '#'
									. $products[$index]['PRODUCT_DATA']['~XML_ID']
								;
							}
						}
						unset($parentId, $index);
						unset($row, $iterator);
					}
					unset($pageIds);
				}
				unset($parentIdList, $parentMap);
			}
			unset($offerList);

			return $products;
		}

		private static function getPriceDataList(array $products, array $config): array
		{
			/*
				'IS_ADMIN_SECTION' => $adminSection,
				'USER_ID' => $userId,
				'SITE_ID' => $siteId,
				'CURRENCY' => $currency,
			*/
			$userGroups = self::getUserGroups($config['USER_ID']);

			\CCatalogProduct::GetVATDataByIDList(array_keys($products));

			if ($config['IS_ADMIN_SECTION'])
			{
				if ($config['USER_ID'] > 0)
				{
					\CCatalogDiscountSave::SetDiscountUserID($config['USER_ID']);
				}
				else
				{
					\CCatalogDiscountSave::Disable();
				}
			}

			Price\Calculation::pushConfig();
			Price\Calculation::setConfig([
				'CURRENCY' => $config['CURRENCY'],
				'PRECISION' => (int)Main\Config\Option::get('sale', 'value_precision'),
				'RESULT_WITH_VAT' => true,
				'RESULT_MODE' => Catalog\Product\Price\Calculation::RESULT_MODE_RAW,
			]);

			$priceDataList = \CCatalogProduct::GetOptimalPriceList(
				$products,
				$userGroups,
				'N',
				[],
				($config['IS_ADMIN_SECTION'] ? $config['SITE_ID'] : false)
			);

			if (empty($priceDataList))
			{
				$productsQuantityList = $products;
				$quantityCorrected = false;

				foreach ($productsQuantityList as $productId => $productData)
				{
					$quantityList = array($productData['BASKET_CODE'] => $productData['QUANTITY']);

					if (empty($productData[Base::FLAT_QUANTITY_LIST]))
					{
						$quantityList = $productData[Base::FLAT_QUANTITY_LIST];
					}

					if (empty($quantityList))
					{
						continue;
					}

					foreach ($quantityList as $basketCode => $quantity)
					{
						$nearestQuantity = \CCatalogProduct::GetNearestQuantityPrice($productId, $quantity, $userGroups);
						if (!empty($nearestQuantity))
						{
							if (!empty($productData[Base::FLAT_QUANTITY_LIST]))
							{
								$productsQuantityList[$productId][Base::FLAT_QUANTITY_LIST][$basketCode]['QUANTITY'] = $nearestQuantity;
							}
							else
							{
								$productsQuantityList[$productId]['QUANTITY'] = $nearestQuantity;
							}

							$quantityCorrected = true;
						}
					}
				}

				if ($quantityCorrected)
				{
					$priceDataList = \CCatalogProduct::GetOptimalPriceList(
						$productsQuantityList,
						$userGroups,
						'N',
						[],
						($config['IS_ADMIN_SECTION'] ? $config['SITE_ID'] : false)
					);
				}

			}

			Price\Calculation::popConfig();

			if ($config['IS_ADMIN_SECTION'])
			{
				if ($config['USER_ID'] > 0)
				{
					\CCatalogDiscountSave::ClearDiscountUserID();
				}
				else
				{
					\CCatalogDiscountSave::Enable();
				}
			}

			if (!empty($priceDataList))
			{
				foreach ($priceDataList as $productId => $priceBasketDataList)
				{
					foreach ($priceBasketDataList as $basketCode => $priceData)
					{
						if ($priceData === false)
						{
							continue;
						}

						if (empty($priceData['DISCOUNT_LIST']) && !empty($priceData['DISCOUNT']) && is_array($priceData['DISCOUNT']))
						{
							$priceDataList[$productId][$basketCode]['DISCOUNT_LIST'] = [$priceData['DISCOUNT']];
						}

						if (empty($priceData['PRICE']['CATALOG_GROUP_NAME']))
						{
							if (!empty($priceData['PRICE']['CATALOG_GROUP_ID']))
							{
								$priceName = self::getPriceTitle($priceData['PRICE']['CATALOG_GROUP_ID']);
								if ($priceName !== '')
								{
									$priceDataList[$productId][$basketCode]['PRICE']['CATALOG_GROUP_NAME'] = $priceName;
								}
								unset($priceName);
							}
						}
					}
				}
			}

			return $priceDataList;
		}

		private static function getDiscountList(array $priceDataList): array
		{
			$discountList = array();
			if (!empty($priceDataList))
			{
				foreach ($priceDataList as $productId => $priceBasketDataList)
				{
					foreach ($priceBasketDataList as $basketCode => $priceData)
					{
						if ($priceData === false)
						{
							continue;
						}

						if (empty($priceData['DISCOUNT_LIST']) && !empty($priceData['DISCOUNT']) && is_array($priceData['DISCOUNT']))
						{
							$priceDataList[$productId][$basketCode]['DISCOUNT_LIST'] = [$priceData['DISCOUNT']];
						}

						if (!empty($priceData['DISCOUNT_LIST']))
						{
							if (!isset($discountList[$productId]))
							{
								$discountList[$productId] = [];
							}
							if (!isset($discountList[$productId][$basketCode]))
							{
								$discountList[$productId][$basketCode] = [];
							}
							foreach ($priceData['DISCOUNT_LIST'] as $discountItem)
							{
								$discountList[$productId][$basketCode][] = \CCatalogDiscount::getDiscountDescription($discountItem);
							}
							unset($discountItem);
						}

						if (empty($priceData['PRICE']['CATALOG_GROUP_NAME']))
						{
							if (!empty($priceData['PRICE']['CATALOG_GROUP_ID']))
							{
								$priceName = self::getPriceTitle($priceData['PRICE']['CATALOG_GROUP_ID']);
								if ($priceName != '')
								{
									$priceDataList[$productId][$basketCode]['PRICE']['CATALOG_GROUP_NAME'] = $priceName;
								}
								unset($priceName);
							}
						}
					}
				}
			}

			return $discountList;
		}

		public static function getDefaultStoreId(): int
		{
			$result = parent::getDefaultStoreId();
			if (Catalog\Config\State::isUsedInventoryManagement())
			{
				$storeId = Catalog\StoreTable::getDefaultStoreId();
				if ($storeId !== null)
				{
					$result = $storeId;
				}
			}

			return $result;
		}

		private static function getAmountFromSource(array $product, array $sourceList): float
		{
			if (empty($product) || empty($sourceList))
			{
				return 0;
			}

			$result = 0;
			$found = false;
			foreach ($sourceList as $source)
			{
				switch ($source)
				{
					case self::AMOUNT_SRC_QUANTITY:
						if (array_key_exists($source, $product))
						{
							$result = $product[$source];
							$found = true;
						}
						break;
					case self::AMOUNT_SRC_QUANTITY_LIST:
					case self::AMOUNT_SRC_RESERVED_LIST:
						if (
							!empty($product[$source])
							&& is_array($product[$source])
						)
						{
							$result = array_sum($product[$source]);
							$found = true;
						}
						break;
					case self::AMOUNT_SRC_PRICE_LIST:
						if (
							!empty($product[$source])
							&& is_array($product[$source])
						)
						{
							foreach ($product[$source] as $row)
							{
								if (!is_array($row) || !isset($row['QUANTITY']))
								{
									continue;
								}
								$result += (float)$row['QUANTITY'];
							}
							unset($row);
							$found = true;
						}
						break;
					case self::AMOUNT_SRC_STORE_QUANTITY_LIST:
					case self::AMOUNT_SRC_STORE_RESERVED_LIST:
						if (
							!empty($product[$source])
							&& is_array($product[$source])
						)
						{
							switch (self::getQuantityFormat($product[$source]))
							{
								case self::QUANTITY_FORMAT_STORE:
									$internalResult = self::calculateQuantityFromStores($product[$source]);
									break;
								case self::QUANTITY_FORMAT_SHIPMENT:
									$internalResult = self::calculateQuantityFromShipments($product[$source]);
									break;
								default:
									$internalResult = null;
									break;
							}
							if ($internalResult !== null)
							{
								$result += array_sum($internalResult);
								$found = true;
							}
							unset($internalResult);
						}
						break;
				}
				if ($found)
				{
					break;
				}
			}

			return (float)$result;
		}

		private static function getStoreAmountFromQuantityList(array $data): ?array
		{
			return self::getStoreAmountFromSource(
				$data,
				[
					self::AMOUNT_SRC_STORE_QUANTITY_LIST,
					self::AMOUNT_SRC_QUANTITY_LIST,
					self::AMOUNT_SRC_QUANTITY,
				]
			);
		}

		private static function getStoreReservedQuantityFromProduct(array $product): ?array
		{
			return self::getStoreAmountFromSource(
				$product,
				[
					self::AMOUNT_SRC_STORE_RESERVED_LIST,
					self::AMOUNT_SRC_RESERVED_LIST,
				]
			);
		}

		private static function getStoreAmountFromPriceList(array $product, bool $direction = true): ?array
		{
			if ($direction)
			{
				$list = [
					self::AMOUNT_SRC_QUANTITY,
					self::AMOUNT_SRC_PRICE_LIST,
				];
			}
			else
			{
				$list = [
					self::AMOUNT_SRC_PRICE_LIST,
					self::AMOUNT_SRC_QUANTITY,
				];
			}

			return self::getStoreAmountFromSource($product, $list);
		}

		private static function getStoreAmountFromSource(array $product, array $sourceList): ?array
		{
			if (empty($product) || empty($sourceList))
			{
				return null;
			}

			$result = [];
			$found = false;
			foreach ($sourceList as $source)
			{
				switch ($source)
				{
					case self::AMOUNT_SRC_STORE_QUANTITY_LIST:
					case self::AMOUNT_SRC_STORE_RESERVED_LIST:
						if (
							!empty($product[$source])
							&& is_array($product[$source])
						)
						{
							switch (self::getQuantityFormat($product[$source]))
							{
								case self::QUANTITY_FORMAT_STORE:
									$internalResult = self::calculateQuantityFromStores($product[$source]);
									break;
								case self::QUANTITY_FORMAT_SHIPMENT:
									$internalResult = self::calculateQuantityFromShipments($product[$source]);
									break;
								default:
									$internalResult = null;
									break;
							}
							if ($internalResult !== null)
							{
								$result = $internalResult;
								$found = true;
							}
							unset($internalResult);
						}
						break;
					case self::AMOUNT_SRC_QUANTITY_LIST:
					case self::AMOUNT_SRC_RESERVED_LIST:
						/*
						'QUANTITY_LIST' =>
							array (
								289 => 1.0,
								290 => 3.0,
								291 => 4.0,
							),
						 */
						if (
							!empty($product[$source])
							&& is_array($product[$source])
						)
						{
							$result[static::getDefaultStoreId()] = array_sum($product[$source]);
							$found = true;
						}
						break;
					case self::AMOUNT_SRC_QUANTITY:
						if (array_key_exists($source, $product))
						{
							$result[static::getDefaultStoreId()] = (float)$product[$source];
							$found = true;
						}
						break;
				}
				if ($found)
				{
					break;
				}
			}

			return (!empty($result) ? $result : null);
		}

		private static function getQuantityFormat(array $list): ?int
		{
			/*
			first variant
			'RESERVED_QUANTITY_LIST_BY_STORE' =>
			array (
				20 => basket code
					array (
						'0_0' => shipment index
							array (
								3 => 10.0, store id -> quantity
							),
					),
			),

			second variant
			'RESERVED_QUANTITY_LIST_BY_STORE' =>
			array (
				20 => basket code
					array (
						3 => 10.0, store id -> quantity
					),
			),

			'QUANTITY_LIST_BY_STORE' =>
			array (
				289 => basket code
					array (
						5 => 1.0,  store id => quantity
					),
				290 =>
					array (
						5 => 3.0,
					),
				291 =>
					array (
						5 => 4.0,
					),
				),
			),
			*/

			$basketRow = reset($list);
			if (
				empty($basketRow)
				|| !is_array($basketRow)
			)
			{
				return null;
			}

			$row = reset($basketRow);
			if (is_array($row))
			{
				return self::QUANTITY_FORMAT_SHIPMENT;
			}

			return self::QUANTITY_FORMAT_STORE;
		}

		private static function calculateQuantityFromStores(array $list): ?array
		{
			$result = [];
			$found = false;
			foreach ($list as $basketItemStores)
			{
				if (
					empty($basketItemStores)
					|| !is_array($basketItemStores)
				)
				{
					continue;
				}
				foreach ($basketItemStores as $storeId => $quantity)
				{
					if (!isset($result[$storeId]))
					{
						$result[$storeId] = 0.0;
					}
					$result[$storeId] += (float)$quantity;
					$found = true;
				}
				unset($storeId, $quantity);
			}
			unset($basketItemStores);

			return ($found ? $result : null);
		}

		private static function calculateQuantityFromShipments(array $list): ?array
		{
			$result = [];
			$found = false;
			foreach ($list as $basketItemShipments)
			{
				if (
					empty($basketItemShipments)
					|| !is_array($basketItemShipments)
				)
				{
					continue;
				}
				foreach ($basketItemShipments as $basketItemStores)
				{
					foreach ($basketItemStores as $storeId => $quantity)
					{
						if (!isset($result[$storeId]))
						{
							$result[$storeId] = 0;
						}
						$result[$storeId] += (float)$quantity;
						$found = true;
					}
				}
			}

			return ($found ? $result : null);
		}

		private static function getStoreQuantityFromQuantityList(array $product): array
		{
			return self::getStoreQuantityFromSource(
				$product,
				[
					self::AMOUNT_SRC_STORE_QUANTITY_LIST,
					self::AMOUNT_SRC_QUANTITY_LIST,
				]
			);
		}

		private static function getStoreQuantityFromSource(array $product, array $sourceList): array
		{
			if (empty($product) || empty($sourceList))
			{
				return [static::getDefaultStoreId() => 0.0];
			}

			$result = [];
			$found = false;
			foreach ($sourceList as $source)
			{
				switch ($source)
				{
					case self::AMOUNT_SRC_QUANTITY_LIST:
					case self::AMOUNT_SRC_RESERVED_LIST:
						if (
							!empty($product[$source])
							&& is_array($product[$source])
						)
						{
							$result = [
								static::getDefaultStoreId() => (float)array_sum($product[$source]),
							];
							$found = true;
						}
						break;
					/*case self::AMOUNT_SRC_PRICE_LIST:
						if (
							!empty($product[$source])
							&& is_array($product[$source])
						)
						{
							foreach ($product[$source] as $row)
							{
								if (!is_array($row) || !isset($row['QUANTITY']))
								{
									continue;
								}
								$result += (float)$row['QUANTITY'];
							}
							unset($row);
							$found = true;
						}
						break; */
					case self::AMOUNT_SRC_STORE_QUANTITY_LIST:
					case self::AMOUNT_SRC_STORE_RESERVED_LIST:
						if (
							!empty($product[$source])
							&& is_array($product[$source])
						)
						{
							switch (self::getQuantityFormat($product[$source]))
							{
								case self::QUANTITY_FORMAT_STORE:
									$internalResult = self::calculateQuantityFromStores($product[$source]);
									break;
								case self::QUANTITY_FORMAT_SHIPMENT:
									$internalResult = self::calculateQuantityFromShipments($product[$source]);
									break;
								default:
									$internalResult = null;
									break;
							}
							if ($internalResult !== null)
							{
								$result = $internalResult;
								$found = true;
							}
						}
						break;
				}
				if ($found)
				{
					break;
				}
			}

			return (!empty($result)
				? $result
				: [static::getDefaultStoreId() => 0.0]
			);
		}

		private static function loadCurrentStoreReserve(int $productId, array $reserve): array
		{
			$result = [];
			foreach ($reserve as $storeId => $quantity)
			{
				$result[$storeId] = [
					'ID' => null,
					'PRODUCT_ID' => $productId,
					'STORE_ID' => $storeId,
					'ADD_QUANTITY_RESERVED' => $quantity,
					'QUANTITY_RESERVED' => 0.0,
				];
			}

			$iterator = Catalog\StoreProductTable::getList([
				'select' => [
					'ID',
					'STORE_ID',
					'QUANTITY_RESERVED',
				],
				'filter' => [
					'=PRODUCT_ID' => $productId,
					'@STORE_ID' => array_keys($reserve),
				],
			]);
			while ($row = $iterator->fetch())
			{
				$storeId = (int)$row['STORE_ID'];
				$result[$storeId]['ID'] = (int)$row['ID'];
				$result[$storeId]['QUANTITY_RESERVED'] = (float)$row['QUANTITY_RESERVED'];
			}
			unset($row, $iterator);

			return $result;
		}

		private static function loadCurrentProductStores(array $list): array
		{
			Main\Type\Collection::normalizeArrayValuesByInt($list, true);
			if (empty($list))
			{
				return [];
			}

			$result = [];
			foreach (array_chunk($list, 500) as $pageIds)
			{
				$iterator = Catalog\StoreProductTable::getList([
					'select' => [
						'ID',
						'STORE_ID',
						'PRODUCT_ID',
						'AMOUNT',
						'QUANTITY_RESERVED',
					],
					'filter' => [
						'@PRODUCT_ID' => $pageIds,
						'=STORE.ACTIVE' => 'Y',
					],
					'order' => [
						'PRODUCT_ID' => 'ASC',
						'STORE_ID' => 'ASC',
					],
				]);
				while ($row = $iterator->fetch())
				{
					$row['ID'] = (int)$row['ID'];
					$row['PRODUCT_ID'] = (int)$row['PRODUCT_ID'];
					$row['STORE_ID'] = (int)$row['STORE_ID'];
					$row['AMOUNT'] = (float)$row['AMOUNT'];
					$row['QUANTITY_RESERVED'] = (float)$row['QUANTITY_RESERVED'];

					$productId = $row['PRODUCT_ID'];
					$storeId = $row['STORE_ID'];
					if (!isset($result[$productId]))
					{
						$result[$productId] = [];
					}
					$result[$productId][$storeId] = $row;
				}
				unset($productId, $storeId);
				unset($row, $iterator);
			}
			unset($pageIds);

			return $result;
		}

		private static function convertErrors(Main\Entity\Result $result): void
		{
			global $APPLICATION;

			$oldMessages = [];
			foreach ($result->getErrorMessages() as $errorText)
			{
				$oldMessages[] = [
					'text' => $errorText,
				];
			}
			unset($errorText);

			if (!empty($oldMessages))
			{
				$error = new \CAdminException($oldMessages);
				$APPLICATION->ThrowException($error);
				unset($error);
			}
			unset($oldMessages);
		}
	}
}
