<?php
namespace Bitrix\Catalog\Product;

use Bitrix\Main,
	Bitrix\Catalog,
	Bitrix\Iblock,
	Bitrix\Sale,
	Bitrix\Currency;

if (Main\Loader::includeModule('sale'))
{
	/**
	 * Class CatalogProvider
	 *
	 * @package Bitrix\Catalog\Product
	 */
	class CatalogProvider
		extends Sale\SaleProviderBase
	{
		private static $userCache = array();

		protected static $hitCache = array();
		protected static $priceTitleCache = array();
		protected static $clearAutoCache = array();

		protected $enableCache = true;

		const CACHE_USER_GROUPS = 'USER_GROUPS';
		const CACHE_ITEM_WITHOUT_RIGHTS = 'IBLOCK_ELEMENT_PERM_N';
		const CACHE_ITEM_RIGHTS = 'IBLOCK_ELEMENT';
		const CACHE_ITEM_WITH_RIGHTS = 'IBLOCK_ELEMENT_PERM_Y';
		const CACHE_ELEMENT_RIGHTS_MODE = 'ELEMENT_RIGHTS_MODE';
		const CACHE_PRODUCT = 'CATALOG_PRODUCT';
		const CACHE_VAT = 'VAT_INFO';
		const CACHE_IBLOCK_RIGHTS = 'IBLOCK_RIGHTS';
		const CACHE_STORE = 'CATALOG_STORE';
		const CACHE_STORE_PRODUCT = 'CATALOG_STORE_PRODUCT';
		const CACHE_PARENT_PRODUCT_ACTIVE = 'PARENT_PRODUCT_ACTIVE';
		const CACHE_CATALOG_IBLOCK_LIST = 'CATALOG_IBLOCK_LIST';
		const CACHE_PRODUCT_STORE_LIST = 'CACHE_PRODUCT_STORE_LIST';
		const CACHE_PRODUCT_AVAILABLE_LIST = 'CACHE_PRODUCT_AVAILABLE_LIST';

		const CATALOG_PROVIDER_EMPTY_STORE_ID = 0;
		const BUNDLE_TYPE = 1;

		const RESULT_PRODUCT_LIST = 'PRODUCT_DATA_LIST';
		const RESULT_CATALOG_LIST = 'CATALOG_DATA_LIST';

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
			return $this->getData($products, array('CATALOG_DATA'));
		}

		/**
		 * @param array $products
		 * @param array $options
		 *
		 * @return Sale\Result
		 */
		private function getData(array $products, array $options = array())
		{
			$context = $this->getContext();

			$resultProductList = array_fill_keys(array_keys($products), false);

			$result = new Sale\Result();

			$userId = (isset($context['USER_ID']) ? (int)$context['USER_ID'] : 0);
			if ($userId < 0)
			{
				$userId = 0;
			}

			$siteId = false;
			if (isset($context['SITE_ID']))
			{
				$siteId = $context['SITE_ID'];
			}

			$currency = (isset($context['CURRENCY']) ? Currency\CurrencyManager::checkCurrencyID($context['CURRENCY']) : false);
			if ($currency === false)
			{
				$currency = Sale\Internals\SiteCurrencyTable::getSiteCurrency($siteId ? $siteId : SITE_ID);
			}

			if (is_array($options) && in_array('DISABLE_CACHE', $options))
			{
				$this->enableCache = false;
			}

			$outputVariable = static::getOutputVariable($options);

			$productIndex = array();
			$productGetIdList = array();
			$correctProductIds = array();

			$iblockElementSelect = array('ID', 'IBLOCK_ID', 'IBLOCK_SECTION_ID', 'ACTIVE', 'ACTIVE_DATE', 'XML_ID');
			if (is_array($options) && !in_array('CATALOG_DATA', $options))
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

				$productIndex[$productId][] = $itemData['ITEM_CODE'];

				$hash = $productId."|".$userId;
				$productCachedData = static::getHitCache(self::CACHE_ITEM_RIGHTS, $hash, $iblockElementSelect);
				if ($this->enableCache && !empty($productCachedData))
				{
					$products[$productId]['PRODUCT_DATA'] = $productCachedData;
					$correctProductIds[] = $productId;
				}
				else
				{
					$productGetIdList[] = $productId;
				}

			}

			$adminSection = (defined('ADMIN_SECTION') && ADMIN_SECTION === true);

			$userGroups = self::getUserGroups($userId);

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
					$correctProductIds[] = $productId;
				}

				$products = static::removeNotExistsItemFromProducts($products, $correctProductIds);
				if (empty($products))
				{
					return static::getResultProvider($result, $outputVariable, $resultList);
				}
			}

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

			$iblockList = static::removeNotExistsIblockFromList($iblockList, array_keys($iblockDataList));

			$iblockProductMap = static::createIblockProductMap($iblockList, $iblockDataList);

			$correctProductList = static::checkSkuPermission($iblockProductMap);

			$products = static::removeNotExistsItemFromProducts($products, $correctProductList);
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
				'TYPE'
			);
			$catalogSelect = array_merge($catalogSelect, Catalog\Product\SystemField::getFieldList());

			if (is_array($options) && !in_array('CATALOG_DATA', $options))
			{
				$catalogSelect = array_merge($catalogSelect, array(
					'WEIGHT',
					'WIDTH',
					'HEIGHT',
					'LENGTH',
					'BARCODE_MULTI'
				));
			}

			$catalogProductDataList = static::getCatalogProducts(array_keys($products), $catalogSelect);

			// Fill CATALOG_XML_ID to products - temporary hack
			foreach ($iblockProductMap as $entityData)
			{
				if (empty($entityData['PRODUCT_LIST']) || !is_array($entityData['PRODUCT_LIST']))
					continue;
				foreach ($entityData['PRODUCT_LIST'] as $index)
				{
					if (!isset($products[$index]))
						continue;
					$products[$index]['PRODUCT_DATA']['CATALOG_XML_ID'] = $entityData['CATALOG_XML_ID'];
				}
				unset($index);
			}
			unset($entityData);

			$products = static::removeNotExistsItemFromProducts($products, array_keys($catalogProductDataList));
			if (empty($products))
			{
				return static::getResultProvider($result, $outputVariable, $resultList);
			}

			// Fill PRODUCT_XML_ID to products - temporary hack
			$offerList = [];
			foreach ($catalogProductDataList as $entityData)
			{
				if ($entityData['TYPE'] != Catalog\ProductTable::TYPE_OFFER)
					continue;
				if (mb_strpos($products[$entityData['ID']]['PRODUCT_DATA']['~XML_ID'], '#') !== false)
					continue;
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
						$parentMap[$parentId] = [];
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
							'select' => ['ID', 'XML_ID'],
							'filter' => ['@ID' => $pageIds]
						]);
						while ($row = $iterator->fetch())
						{
							$parentId = (int)$row['ID'];
							if (empty($parentMap[$parentId]))
								continue;
							foreach ($parentMap[$parentId] as $index)
							{
								$products[$index]['PRODUCT_DATA']['~XML_ID'] = $row['XML_ID'].'#'.$products[$index]['PRODUCT_DATA']['~XML_ID'];
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

			$checkQuantityList = array();
			foreach ($catalogProductDataList as $catalogProductId => $catalogProductData)
			{
				$checkQuantityList[$catalogProductId] = ($catalogProductData["CAN_BUY_ZERO"] != 'Y'  && $catalogProductData["QUANTITY_TRACE"] == 'Y');
			}

			// get price

			\CCatalogProduct::GetVATDataByIDList(array_keys($products));

			if ($adminSection)
			{
				if ($userId > 0)
				{
					\CCatalogDiscountSave::SetDiscountUserID($userId);
				}
				else
				{
					\CCatalogDiscountSave::Disable();
				}
			}

			Price\Calculation::pushConfig();
			Price\Calculation::setConfig(array(
				'CURRENCY' => $currency,
				'PRECISION' => (int)Main\Config\Option::get('sale', 'value_precision'),
				'RESULT_WITH_VAT' => true,
				'RESULT_MODE' => Catalog\Product\Price\Calculation::RESULT_MODE_RAW
			));

			$productPriceList = array();
			$priceDataList = \CCatalogProduct::GetOptimalPriceList(
				$products,
				$userGroups,
				'N',
				array(),
				($adminSection ? $siteId : false)
			);

			if (empty($priceDataList))
			{
				$productsQuantityList = $products;
				$quantityCorrected = false;

				foreach ($productsQuantityList as $productId => $productData)
				{
					$quantityList = array($productData['BASKET_CODE'] => $productData['QUANTITY']);

					if (empty($productData['QUANTITY_LIST']))
					{
						$quantityList = $productData['QUANTITY_LIST'];
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
							if (!empty($productData['QUANTITY_LIST']))
							{
								$productsQuantityList[$productId]['QUANTITY_LIST'][$basketCode]['QUANTITY'] = $nearestQuantity;
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
						array(),
						($adminSection ? $siteId : false)
					);
				}

			}

			Price\Calculation::popConfig();

			if ($adminSection)
			{
				if ($userId > 0)
				{
					\CCatalogDiscountSave::ClearDiscountUserID();
				}
				else
				{
					\CCatalogDiscountSave::Enable();
				}
			}

			$discountList = array();

			if (!empty($priceDataList))
			{
				foreach ($priceDataList as $productId => $priceBasketDataList)
				{
					foreach ($priceBasketDataList as $basketCode => $priceData)
					{
						if ($priceData === false)
							continue;

						if (empty($priceData['DISCOUNT_LIST']) && !empty($priceData['DISCOUNT']) && is_array($priceData['DISCOUNT']))
						{
							$priceDataList[$productId][$basketCode]['DISCOUNT_LIST'] = array($priceData['DISCOUNT']);
						}

						if (!empty($priceData['DISCOUNT_LIST']))
						{
							if (!isset($discountList[$productId]))
								$discountList[$productId] = [];
							if (!isset($discountList[$productId][$basketCode]))
								$discountList[$productId][$basketCode] = [];
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

			$productQuantityList = array();

			foreach ($products as $productId => $productData)
			{
				$catalogProductData = $catalogProductDataList[$productId];

				$quantityList = array();

				if (array_key_exists('QUANTITY', $productData))
				{
					$quantityList = array($productData['BASKET_CODE'] => $productData['QUANTITY']);
				}

				if (!empty($productData['QUANTITY_LIST']))
				{
					$quantityList = $productData['QUANTITY_LIST'];
				}

				$productQuantityList[$productData['BASKET_CODE']]['QUANTITY_RESERVED'] = $catalogProductData['QUANTITY_RESERVED'];

				$baseCatalogQuantity = floatval($catalogProductData['QUANTITY']);

				$sumQuantity = 0;
				$allCount = 0;
				foreach ($quantityList as $basketCode => $quantity)
				{
					$sumQuantity += floatval(abs($quantity));
					$allCount++;
				}

				$catalogQuantityForAvaialable = $baseCatalogQuantity;
				$checkCatalogQuantity = $baseCatalogQuantity;

				$isEnough = !($checkQuantityList[$productId] === true && $catalogQuantityForAvaialable < $sumQuantity);
				$setQuantity = $baseCatalogQuantity;
				foreach ($quantityList as $basketCode => $quantity)
				{
					$quantity = floatval(abs($quantity));

					if (!$isEnough)
					{
						if ($catalogQuantityForAvaialable - $quantity < 0)
						{
							$quantity = $catalogQuantityForAvaialable;
						}

						$catalogQuantityForAvaialable -= $quantity;
					}

					$productQuantityList[$basketCode]['AVAILABLE_QUANTITY'] = ($baseCatalogQuantity >= $quantity || !$checkQuantityList[$productId] ? $quantity : $baseCatalogQuantity);

					if (in_array('FULL_QUANTITY', $options))
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
						if ($baseCatalogQuantity - $quantity > 0 || !$checkQuantityList[$productId])
						{
							$setQuantity = $quantity;
						}
					}

					$productQuantityList[$basketCode]['QUANTITY'] = $setQuantity;

					$productPriceList[$basketCode] = $priceDataList[$productId][$basketCode];
				}

				$measure = isset($catalogProductData['MEASURE']) ? intval($catalogProductData['MEASURE']) : null;
				$measureFields = static::getMeasure($measure);
				if (!empty($measureFields))
				{
					$catalogProductDataList[$productId] = $measureFields + $catalogProductDataList[$productId];
				}

			}

			$resultData = static::setCatalogDataToProducts($products, $catalogProductDataList, $options);

			$priceResultList = static::createProductPriceList($products, $productPriceList, $discountList);

			$resultList = static::createProductResult($products, $resultData, $priceResultList, $productQuantityList);

			$resultList = $resultList + $resultProductList;

			return static::getResultProvider($result, $outputVariable, $resultList);
		}


		private static function getOutputVariable(array $options = array())
		{
			$outputVariable = static::RESULT_PRODUCT_LIST;
			if (is_array($options) && in_array('CATALOG_DATA', $options))
			{
				$outputVariable = static::RESULT_CATALOG_LIST;
			}

			return $outputVariable;
		}

		private static function getResultProvider(Sale\Result $result, $outputVariable, array $resultList = array())
		{
			$result->setData(
				array(
					$outputVariable => $resultList
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
		private function getElements(array $list, array $select, $userId = null)
		{
			$filter = array(
				'ID' => $list,
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R'
			);
			if ($userId !== null)
				$filter['PERMISSIONS_BY'] = $userId;

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

			return $resultList;
		}

		/**
		 * @param array $products
		 *
		 * @return array|bool|mixed
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
					$bundleItemId = reset(array_keys($childItemList));
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
						'QUANTITY_LIST' => array($item['ITEM_ID'] => $item['QUANTITY']),
						'BUNDLE_CHILD' => true,
					);

					$productIndexList[$item['ITEM_ID']] = array(
						'PRODUCT_ID' => $productId,
						'PARENT_ID' => $parentItemid,
						'CHILD_ID' => $childItemid
					);
				}
			}

			$r = $this->getProductData($childProducts);
			if ($r->isSuccess())
			{
				$resultData = $r->getData();
				if (!empty($resultData[static::RESULT_PRODUCT_LIST]))
				{
					$resultDataList = $resultData[static::RESULT_PRODUCT_LIST];
					foreach ($resultDataList as $itemCode => $itemData)
					{
						$item = $bundleChildList[$itemCode];
						if (array_key_exists('QUANTITY_TRACE', $itemData))
							unset($itemData['QUANTITY_TRACE']);

						$itemData["PRODUCT_ID"] = $item["ITEM_ID"];
						$itemData["MODULE"] = "catalog";
						$itemData["PRODUCT_PROVIDER_CLASS"] = '\Bitrix\Catalog\Product\CatalogProvider';
//					if ($type == \CCatalogProductSet::TYPE_SET)
//					{
//						$itemData['SET_DISCOUNT_PERCENT'] = ($item['DISCOUNT_PERCENT'] == '' ? false : (float)$item['DISCOUNT_PERCENT']);
//					}

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
								$productData = static::getHitCache('IBLOCK_ELEMENT', $item["ITEM_ID"]);
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
								$productRes = \CIBlockElement::GetList(array(), array("ID" => $childIdGetList), false, false, array('ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID'));
								while ($productData = $productRes->Fetch())
								{
									static::setHitCache('IBLOCK_ELEMENT', $productData["ID"], $productData);
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
										"SORT" => $propData["SORT"]
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
								"VALUE" => $strIBlockXmlID
							);

							$elementData['CATALOG_XML_ID'] = $strIBlockXmlID;

						}

						if (!empty($proxyCatalogSkuData[$item["ITEM_ID"]]) && mb_strpos($elementData["XML_ID"], '#') === false)
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
										'filter' => array('ID' => $parentSkuData['ID'])
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
							"VALUE" => $elementData["XML_ID"]
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
						'BUNDLE_LIST' => $resultList
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
		 * @return array
		 */
		private function createReverseQuantityProducts(array $products)
		{
			$resultList = array();
			foreach ($products as $productId => $productData)
			{
				$resultList[$productId] = $productData;
				if (array_key_exists('QUANTITY', $productData))
				{
					$resultList[$productId]['QUANTITY'] *= -1;
				}
				elseif (!empty($productData['QUANTITY_LIST']))
				{
					foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
					{
						$resultList[$productId]['QUANTITY_LIST'][$basketCode] *= -1;
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
						'DELIVER_PRODUCTS_LIST' => $resultList
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
		public function viewProduct(array $items)
		{
			$result = new Sale\Result();

			$resultList = array();

			foreach ($items as $productId => $itemData)
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
						'VIEW_PRODUCTS_LIST' => $resultList
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
						'RECURRING_PRODUCTS_LIST' => $resultList
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

			foreach ($items as $productId => $barcodeParams)
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
						'BARCODE_CHECK_LIST' => $resultList
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

			$resultList = array();

			foreach ($products as $productId => $productData)
			{
				$productQuantity = 0;
				if (array_key_exists('QUANTITY', $productData))
				{
					$productQuantity = $productData['QUANTITY'];
				}
				elseif (!empty($productData['QUANTITY_LIST']))
				{
					foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
					{
						$productQuantity += $quantity;
					}
				}

				$resultList[$productId] = false;
			}

			$availableItems = $this->createProductsListWithCatalogData($products);

			$useStoreControl = Catalog\Config\State::isUsedInventoryManagement();

			$productStoreDataList = array();
			if ($useStoreControl === true)
			{
				$shipProducts = array();
				foreach ($products as $productId => $productData)
				{
					$shipProducts[$productId] = $productData;
				}

				$r = $this->getProductListStores($shipProducts);
				if ($r->isSuccess())
				{
					$data = $r->getData();
					if (!empty($data['PRODUCT_STORES_LIST']))
					{
						$productStoreDataList = $data['PRODUCT_STORES_LIST'];
					}
				}
			}

			foreach ($availableItems as $productId => $productData)
			{
				$productStoreData = array();
				if (!empty($productStoreDataList) && isset($productStoreDataList[$productId]))
				{
					$productStoreData = $productStoreDataList[$productId];
				}

				$r = static::shipProduct($productData, $productStoreData);
				if (!$r->isSuccess())
				{
					$result->addErrors($r->getErrors());
					$result->addWarnings($r->getErrors());
				}

				$resultList[$productId] = $r->isSuccess();
			}

			$result->setData(
				array(
					'SHIPPED_PRODUCTS_LIST' => $resultList
				)
			);

			return $result;
		}

		/**
		 * @param array $quantityList
		 *
		 * @return Sale\Result
		 */
		private static function updateCatalogStoreAmount(array $quantityList)
		{
			$result = new Sale\Result();
			$resultList = array();

			if (empty($quantityList))
			{
				return $result;
			}

			foreach ($quantityList as $catalogStoreId => $amount)
			{
				$resultList[$catalogStoreId] = \CCatalogStoreProduct::Update($catalogStoreId, array("AMOUNT" => $amount));
			}

			$result->setData(
				array(
					'AMOUNT_UPDATED_LIST' => $resultList
				)
			);

			return $result;
		}

		/**
		 * @param array $productData
		 * @param array $productStoreDataList
		 *
		 * @return Sale\Result
		 * @throws Main\ArgumentNullException
		 * @throws Main\ArgumentOutOfRangeException
		 */
		private static function shipProduct(array $productData, array $productStoreDataList = array())
		{
			$result = new Sale\Result();

			$useStoreControl = Catalog\Config\State::isUsedInventoryManagement();

			$productId = $productData['PRODUCT_ID'];

			$productQuantity = 0;

			if (array_key_exists('QUANTITY', $productData))
			{
				$productQuantity = $productData['QUANTITY'];
			}
			elseif (!empty($productData['QUANTITY_LIST']))
			{
				foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
				{
					$productQuantity += $quantity;
				}
			}

			$needShip = ($productQuantity < 0);

			if ($useStoreControl === true)
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
					if (!empty($resultData['QUANTITY_LIST']))
					{
						$setQuantityList = $resultData['QUANTITY_LIST'];
					}
				}
				else
				{
					return $r;
				}

				$r = static::updateCatalogStoreAmount($setQuantityList);
				if ($r->isSuccess())
				{
					$resultData = $r->getData();
					if (!empty($resultData['AMOUNT_UPDATED_LIST']))
					{
						foreach($resultData['AMOUNT_UPDATED_LIST'] as $catalogStoreId => $catalogStoreIsUpdated)
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
		private static function shipQuantityWithStoreControl(array $productData)
		{
			$result = new Sale\Result();

			$productId = intval($productData['PRODUCT_ID']);

			$productQuantity = 0;
			if (array_key_exists('QUANTITY', $productData))
			{
				$productQuantity = $productData['QUANTITY'];
			}
			elseif (!empty($productData['QUANTITY_LIST']))
			{
				foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
				{
					$productQuantity += $quantity;
				}
			}

			$catalogData = $productData['CATALOG'];

			$isExistsReserve = static::isExistsReserve($productData) && static::isReservationEnabled();
			$isNeedShip = ($productQuantity < 0);

			$productQuantity = abs($productQuantity);

			$fields = array();

			$catalogReservedQuantity = floatval($catalogData['QUANTITY_RESERVED']);
			$catalogQuantity = 0;
			if (array_key_exists('QUANTITY', $catalogData))
			{
				$catalogQuantity = floatval($catalogData['QUANTITY']);
			}
			elseif (!empty($catalogData['PRICE_LIST']))
			{
				foreach ($catalogData['PRICE_LIST'] as $basketCode => $catalogValue)
				{
					$catalogQuantity += floatval($catalogValue['QUANTITY']);
				}
			}

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

			$isUpdated = false;
			if (!empty($fields))
			{
				$isUpdated = \CCatalogProduct::Update($productId, $fields);

				if ($isUpdated)
				{
					$quantityValues = array();

					if (isset($fields['QUANTITY']))
					{
						$quantityValues[QuantityControl::QUANTITY_CONTROL_QUANTITY] = $fields['QUANTITY'];
						QuantityControl::resetAvailableQuantity($productId);
					}

					if (isset($fields['QUANTITY_RESERVED']))
					{
						$quantityValues[QuantityControl::QUANTITY_CONTROL_RESERVED_QUANTITY] = $fields['QUANTITY_RESERVED'];
					}

					if (!empty($quantityValues))
					{
						QuantityControl::setValues($productId, $quantityValues);
					}
				}
			}

			$result->setData(
				array(
					'IS_UPDATED' => $isUpdated
				)
			);

			return $result;
		}

		/**
		 * @param array $productData
		 *
		 * @return Sale\Result
		 */
		private static function shipQuantityWithoutStoreControl(array $productData)
		{
			$result = new Sale\Result();
			$productId = intval($productData['PRODUCT_ID']);

			$catalogData = $productData['CATALOG'];

			$productQuantity = 0;
			if (array_key_exists('QUANTITY', $productData))
			{
				$productQuantity = $productData['QUANTITY'];
			}
			elseif (!empty($productData['QUANTITY_LIST']))
			{
				foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
				{
					$productQuantity += $quantity;
				}
			}


			$catalogReservedQuantity = floatval($catalogData['QUANTITY_RESERVED']);
			$catalogQuantity = 0;
			if (array_key_exists('QUANTITY', $catalogData))
			{
				$catalogQuantity = floatval($catalogData['QUANTITY']);
			}
			elseif (!empty($catalogData['PRICE_LIST']))
			{
				foreach ($catalogData['PRICE_LIST'] as $basketCode => $catalogValue)
				{
					$catalogQuantity += floatval($catalogValue['QUANTITY']);
				}
			}


			$fields = array();

			$isExistsReserve = static::isExistsReserve($productData) && static::isReservationEnabled();
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
							if ($productQuantity > $catalogReservedQuantity)
							{
								$needReservedQuantity = $catalogReservedQuantity;
							}

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

			if (!empty($fields))
			{
				$isUpdated = \CCatalogProduct::Update($productId, $fields);

				if ($isUpdated)
				{
					$quantityValues = array();

					if (isset($fields['QUANTITY']))
					{
						$quantityValues[QuantityControl::QUANTITY_CONTROL_QUANTITY] = $fields['QUANTITY'];
						QuantityControl::resetAvailableQuantity($productId);
					}

					if (isset($fields['QUANTITY_RESERVED']))
					{
						$quantityValues[QuantityControl::QUANTITY_CONTROL_RESERVED_QUANTITY] = $fields['QUANTITY_RESERVED'];
					}

					if (!empty($quantityValues))
					{
						QuantityControl::setValues($productId, $quantityValues);
					}
				}

				if (isset($fields['QUANTITY']) && self::isNeedClearPublicCache(
						$catalogData['QUANTITY'],
						$fields['QUANTITY'],
						$catalogData['QUANTITY_TRACE'],
						$catalogData['CAN_BUY_ZERO']
					))
				{
					$productInfo = array(
						'CAN_BUY_ZERO' => $catalogData['CAN_BUY_ZERO'],
						'QUANTITY_TRACE' => $catalogData['QUANTITY_TRACE'],
						'OLD_QUANTITY' => $catalogData['QUANTITY'],
						'QUANTITY' => $fields['QUANTITY'],
						'DELTA' => $fields['QUANTITY'] - $catalogData['QUANTITY']
					);
					self::clearPublicCache($catalogData['ID'], $productInfo);
				}
			}



			return $result;
		}

		/**
		 * @param array $productData
		 *
		 * @return bool
		 */
		private static function isExistsReserve(array $productData)
		{
			if (empty($productData['SHIPMENT_ITEM_LIST']))
				return false;

			if (empty($productData['NEED_RESERVE_LIST']))
				return false;

			/**
			 * @var $shipmentItemIndex
			 * @var Sale\ShipmentItem $shipmentItem
			 */
			foreach ($productData['NEED_RESERVE_LIST'] as $shipmentItemIndex => $isReserve)
			{
				if (isset($productData['SHIPMENT_ITEM_LIST'][$shipmentItemIndex]))
				{
					/** @var Sale\ShipmentItem $shipmentItem */
					$shipmentItem = $productData['SHIPMENT_ITEM_LIST'][$shipmentItemIndex];
					if ($shipmentItem->getNeedReserveQuantity() > 0 || $shipmentItem->getReservedQuantity() > 0)
					{
						return true;
					}
				}

				if ($isReserve)
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
		private static function getSetableStoreQuantityProduct(array $productData, array $productStoreDataList)
		{
			$result = new Sale\Result();

			$setQuantityList = array();
			$productQuantity = 0;
			if (array_key_exists('QUANTITY', $productData))
			{
				$productQuantity = $productData['QUANTITY'];
			}
			elseif (!empty($productData['QUANTITY_LIST']))
			{
				foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
				{
					$productQuantity += $quantity;
				}
			}
			$isNeedShip = ($productQuantity < 0);

			$needQuantityList = static::getNeedQuantityFromStore($productData);

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
						if ($shipmentItemStoreCollection->count() === 0)
						{
							/** @var Sale\ShipmentItemStore $item */
							$item = $shipmentItemStoreCollection->createItem($shipmentItem->getBasketItem());
							$item->setField('STORE_ID', $autoShipStore['STORE_ID']);
							$item->setField('QUANTITY', abs($productData['SHIPMENT_ITEM_QUANTITY_LIST'][$index]));
						}
					}
				}
			}

			if (!empty($productStoreDataList))
			{
				foreach ($productStoreDataList as $storeId => $productStoreData)
				{
					$productId = $productStoreData['PRODUCT_ID'];
					if ($isNeedShip && (isset($needQuantityList[$storeId]) && $productStoreData['AMOUNT'] < $needQuantityList[$storeId]))
					{
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									"DDCT_DEDUCTION_QUANTITY_STORE_ERROR",
									array_merge(
										self::getProductCatalogInfo($productId),
										array("#STORE_ID#" => $productStoreData["STORE_ID"], '#PRODUCT_ID#' => $productId)
									)
								), "DDCT_DEDUCTION_QUANTITY_STORE_ERROR"
							)
						);
					}
					else
					{
						$setQuantity = $productQuantity;

						if (isset($needQuantityList[$storeId]))
						{
							$setQuantity = ($isNeedShip ? -1 : 1) * $needQuantityList[$storeId];
						}
						elseif (!empty($needQuantityList))
						{
							continue;
						}

						$setQuantityList[$productStoreData["ID"]] = $productStoreData["AMOUNT"] + $setQuantity;
					}
				}
			}

			if (!empty($setQuantityList))
			{
				$result->addData(
					array(
						'QUANTITY_LIST' => $setQuantityList
					)
				);
			}

			return $result;
		}

		/**
		 * @param array $productData
		 *
		 * @return array|bool
		 */
		private static function getNeedQuantityFromStore(array $productData)
		{
			if (empty($productData['STORE_DATA_LIST']))
				return false;

			$resultList = array();

			foreach ($productData['STORE_DATA_LIST'] as $shipmentItemIndex => $storeDataList)
			{
				foreach ($storeDataList as $storeId => $storeData)
				{
					$resultList[$storeId] += $storeData['QUANTITY'];
				}
			}

			return $resultList;
		}

		/**
		 * @param array $productData
		 *
		 * @return Sale\Result
		 */
		private static function deleteBarcodes(array $productData)
		{
			$result = new Sale\Result();

			$storeData = $productData['STORE_DATA_LIST'];
			if (!empty($storeData))
			{
				foreach ($storeData as $basketCode => $storeDataList)
				{
					foreach($storeDataList as $storeIndex => $storeDataValue)
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
		private static function deleteBarcode(array $storeData)
		{
			$result = new Sale\Result();

			$storeId = $storeData["STORE_ID"];
			$productId = $storeData["PRODUCT_ID"];
			$barcodeMulti = $storeData['IS_BARCODE_MULTI'];

			$barcodeList = $storeData['BARCODE'];

			foreach ($barcodeList as $barcodeId => $barcodeValue)
			{
				if (strval(trim($barcodeValue)) == "" || !$barcodeMulti)
				{
					continue;
				}

				$result = new Sale\Result();
				$barcodeFields = array(
					"STORE_ID" => $storeId,
					"BARCODE" => $barcodeValue,
					"PRODUCT_ID" => $productId
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
		private static function addBarcodes(array $productData)
		{
			$result = new Sale\Result();
			$storeData = $productData['STORE_DATA_LIST'];
			if (!empty($storeData))
			{
				foreach ($storeData as $shipmentItemIndex => $storeDataList)
				{
					foreach($storeDataList as $storeIndex => $storeDataValue)
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
		private static function addBarcode(array $storeData)
		{
			$result = new Sale\Result();

			$storeId = $storeData["STORE_ID"];
			$productId = $storeData["PRODUCT_ID"];
			$barcodeMulti = $storeData['IS_BARCODE_MULTI'];

			$barcodeList = $storeData['BARCODE'];

			foreach ($barcodeList as $barcodeId => $barcodeValue)
			{
				if (strval(trim($barcodeValue)) == "" || !$barcodeMulti)
				{
					continue;
				}

				$result = new Sale\Result();
				$barcodeFields = array(
					"STORE_ID" => $storeId,
					"BARCODE" => $barcodeValue,
					"PRODUCT_ID" => $productId
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
				'RESERVED_PRODUCTS_LIST' => $resultList
			));

			return $result;
		}

		/**
		 * @param array $productData
		 *
		 * @return Sale\Result|bool
		 */
		private static function reserveProduct(array $productData)
		{
			$enabledReservation = static::isReservationEnabled();

			if (!$enabledReservation)
			{
				return static::reserveQuantityWithDisabledReservation($productData);
			}

			return static::reserveQuantityWithEnabledReservation($productData);

		}

		/**
		 * @param array $productData
		 *
		 * @return Sale\Result
		 */
		private static function reserveQuantityWithEnabledReservation(array $productData)
		{
			$result = new Sale\Result();

			$resultFields = array();
			$fields = array();
			$needShipList = array();

			$productId = $productData['PRODUCT_ID'];
			$productQuantity = 0;

			if (array_key_exists('QUANTITY', $productData))
			{
				$productQuantity = $productData['QUANTITY'];
			}
			elseif (!empty($productData['QUANTITY_LIST']))
			{
				foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
				{
					$productQuantity += $quantity;
				}
			}

			$isNeedReserve = ($productQuantity > 0);
			$catalogData = $productData['CATALOG'];

			$catalogReservedQuantity = floatval($catalogData['QUANTITY_RESERVED']);
			$catalogQuantity = 0;

			if (array_key_exists('QUANTITY', $catalogData))
			{
				$catalogQuantity = floatval($catalogData['QUANTITY']);
			}
			elseif (!empty($catalogData['PRICE_LIST']))
			{
				foreach ($catalogData['PRICE_LIST'] as $basketCode => $catalogValue)
				{
					$catalogQuantity += floatval($catalogValue['QUANTITY']);
				}
			}

			$sumCatalogQuantity = $catalogQuantity + $catalogReservedQuantity;


			if (isset($productData['NEED_SHIP']))
			{
				$needShipList = $productData['NEED_SHIP'];
			}

			$setQuantityReserved = $catalogReservedQuantity;
			$shipmentItemList = $productData['SHIPMENT_ITEM_DATA_LIST'];

			$anyoneNeedShip = false;

			if (!empty($needShipList))
			{
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


			if ($catalogData["QUANTITY_TRACE"] == "N" || $anyoneNeedShip === true)
			{
				$fields["QUANTITY_RESERVED"] = $setQuantityReserved;
				$resultFields['IS_UPDATED'] = true;
				$resultFields['QUANTITY_RESERVED'] = 0;
			}
			else
			{
				$resultFields['QUANTITY_RESERVED'] = $catalogReservedQuantity;

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

						if (self::isNeedClearPublicCache(
							$catalogQuantity,
							$fields['QUANTITY'],
							$catalogData['QUANTITY_TRACE'],
							$catalogData['CAN_BUY_ZERO']
						))
						{
							$productInfo = array(
								'CAN_BUY_ZERO' => $catalogData['CAN_BUY_ZERO'],
								'QUANTITY_TRACE' => $catalogData['QUANTITY_TRACE'],
								'OLD_QUANTITY' => $catalogQuantity,
								'QUANTITY' => $fields['QUANTITY'],
								'DELTA' => $fields['QUANTITY'] - $catalogData['QUANTITY']
							);
							self::clearPublicCache($catalogData['ID'], $productInfo);
						}
					}
				}
				else //undo reservation
				{
					$needQuantity = abs($productQuantity);

					$fields["QUANTITY"] = $catalogQuantity + $needQuantity;

					$needReservedQuantity = $catalogReservedQuantity - $needQuantity;
					if ($needQuantity > $catalogReservedQuantity)
					{
						$needReservedQuantity = $catalogReservedQuantity;
					}

					$fields["QUANTITY_RESERVED"] = $needReservedQuantity;

					if (self::isNeedClearPublicCache(
						$catalogData['QUANTITY'],
						$fields['QUANTITY'],
						$catalogData['QUANTITY_TRACE'],
						$catalogData['CAN_BUY_ZERO']
					))
					{
						$productInfo = array(
							'CAN_BUY_ZERO' => $catalogData['CAN_BUY_ZERO'],
							'QUANTITY_TRACE' => $catalogData['QUANTITY_TRACE'],
							'OLD_QUANTITY' => $catalogData['QUANTITY'],
							'QUANTITY' => $fields['QUANTITY'],
							'DELTA' => $fields['QUANTITY'] - $catalogData['QUANTITY']
						);
						self::clearPublicCache($catalogData['ID'], $productInfo);
					}
				}

			} //quantity trace


			if (!empty($fields) && is_array($fields))
			{
				$resultFields['IS_UPDATED'] = \CCatalogProduct::Update($productId, $fields);

				if ($resultFields['IS_UPDATED'])
				{
					$quantityValues = array();
					if (isset($fields['QUANTITY']))
					{
						$quantityValues[QuantityControl::QUANTITY_CONTROL_QUANTITY] = $fields['QUANTITY'];
						QuantityControl::resetAvailableQuantity($productId);
					}

					if (isset($fields['QUANTITY_RESERVED']))
					{
						$quantityValues[QuantityControl::QUANTITY_CONTROL_RESERVED_QUANTITY] = $fields['QUANTITY_RESERVED'];
					}

					if (!empty($quantityValues))
					{
						QuantityControl::setValues($productId, $quantityValues);
					}
				}
			}

			if (isset($resultFields['IS_UPDATED']))
			{
				$needReserved = $fields["QUANTITY_RESERVED"] - $resultFields['QUANTITY_RESERVED'];
				if ($resultFields['QUANTITY_RESERVED'] > $fields["QUANTITY_RESERVED"])
				{
					$needReserved = $fields["QUANTITY_RESERVED"];
				}

				$resultFields["QUANTITY_RESERVED"] = $needReserved;

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
		private static function reserveQuantityWithDisabledReservation(array $productData)
		{
			$result = new Sale\Result();

			$catalogData = $productData['CATALOG'];

			$isQuantityTrace = $catalogData["QUANTITY_TRACE"] == 'Y';

			$productQuantity = 0;
			if (array_key_exists('QUANTITY', $productData))
			{
				$productQuantity = $productData['QUANTITY'];
			}
			elseif (!empty($productData['QUANTITY_LIST']))
			{
				foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
				{
					$productQuantity += $quantity;
				}
			}

			$catalogQuantity = 0;
			if (array_key_exists('QUANTITY', $catalogData))
			{
				$catalogQuantity = floatval($catalogData['QUANTITY']);
			}
			elseif (!empty($catalogData['PRICE_LIST']))
			{
				foreach ($catalogData['PRICE_LIST'] as $basketCode => $catalogValue)
				{
					$catalogQuantity += floatval($catalogValue['QUANTITY']);
				}
			}

			$isUpdated = true;

			$fields = array(
				'QUANTITY' => $catalogQuantity
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

				$isUpdated = \CCatalogProduct::Update($productId, $fields);
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
		 * @param     $productIds
		 * @param int $iblockId
		 *
		 * @return array
		 */
		private static function checkParentActivity($productIds, $iblockId = 0)
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
							'CHECK_PERMISSIONS' => 'N'
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
				$priceTypeList = \CCatalogGroup::GetListArray();
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
			$useStoreControl = Catalog\Config\State::isUsedInventoryManagement();

			$filteredProducts = $this->createQuantityFilteredProducts($products);

			if (empty($filteredProducts))
			{
				$result->setData(
					array(
						'TRY_SHIP_PRODUCTS_LIST' => array_fill_keys(array_keys($products), true)
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
						'TRY_SHIP_PRODUCTS_LIST' => array_fill_keys($productIdList, false)
					)
				);

				return $result;
			}
			else
			{
				foreach ($availableItems as $productId => $productData)
				{
					if (!isset($productData['CATALOG']['ACTIVE']) || $productData['CATALOG']['ACTIVE'] != 'Y')
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

						$resultList[$productId] = false;
						unset($availableItems[$productId]);
					}
				}
			}

			if (!empty($availableItems))
			{
				if ($useStoreControl)
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
						'TRY_SHIP_PRODUCTS_LIST' => $resultList
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
					'IS_NEED_SHIP' => static::isReservationEnabled()
				)
			);
			return $result;
		}

		/**
		 * @param array $products
		 *
		 * @return array
		 */
		private function createQuantityFilteredProducts(array $products)
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
				elseif (!empty($productData['QUANTITY_LIST']))
				{
					foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
					{
						if ($quantity > 0)
						{
							unset($resultList[$productId]['QUANTITY_LIST'][$basketCode]);
						}
						else
						{
							$resultList[$productId]['QUANTITY_LIST'][$basketCode] *= -1;
						}
					}

					if (empty($resultList[$productId]['QUANTITY_LIST']))
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
					'PRODUCTS_LIST_SHIPPED' => $resultList
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
						'PRODUCTS_LIST_IN_STORE' => $resultList
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
		private function checkProductsQuantity(array $products)
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

				if ($catalogData["CAN_BUY_ZERO"] != "Y" && $catalogData["QUANTITY_TRACE"] == "Y")
				{
					$productQuantity = 0;
					$productReservedQuantity = 0;
					if (array_key_exists('QUANTITY', $productData))
					{
						$productQuantity = $productData['QUANTITY'];
					}
					elseif (!empty($productData['QUANTITY_LIST']))
					{
						foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
						{
							$productQuantity += $quantity;
						}
					}

					$availableQuantity = 0;

					if (isset($availableQuantityList[$productId]))
					{
						$availableQuantity = $availableQuantityList[$productId];
					}

					$availableQuantity += floatval($catalogData['QUANTITY_RESERVED']);

					$enabledReservation = static::isReservationEnabled();

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
						'PRODUCTS_LIST_REQUIRED_QUANTITY' => $resultList
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
		private function createProductsListWithCatalogData(array $products)
		{
			$productDataList = array();
			$productIdList = array_keys($products);
			$r = $this->getData($products, array('CATALOG_DATA', 'FULL_QUANTITY'));
			if ($r->isSuccess())
			{
				$data = $r->getData();
				if (!empty($data[static::RESULT_CATALOG_LIST]))
				{
					$productDataList = $data[static::RESULT_CATALOG_LIST];
				}
			}

			$resultList = array();
			$availableListId = array_intersect_key($productIdList, array_keys($productDataList));
			if (!empty($availableListId))
			{
				foreach ($availableListId as $productId)
				{
					if ($productDataList[$productId] === false)
					{
						continue;
					}
					$resultList[$productId] = $products[$productId];
					$resultList[$productId]['CATALOG'] = $productDataList[$productId];
				}
			}

			return $resultList;
		}

		/**
		 * @param array $products
		 *
		 * @return array|bool
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
					continue;
				}
				elseif (!empty($canAutoShipList[$productId]) && !empty($productStoreDataList[$productId]))
				{
					foreach ($productData['SHIPMENT_ITEM_DATA_LIST'] as $shipmentItemIndex => $shipmentItemQuantity)
					{
						$productQuantity = 0;
						if (array_key_exists('QUANTITY', $productData))
						{
							$productQuantity = $productData['QUANTITY'];
						}
						elseif (!empty($productData['QUANTITY_LIST']))
						{
							foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
							{
								$productQuantity += $quantity;
							}
						}

						foreach ($productStoreDataList[$productId] as $productStoreData)
						{
							$storeId = $productStoreData['STORE_ID'];
							$storeProductList[$productId][$shipmentItemIndex][$storeId] = array(
								'PRODUCT_ID' => $productId,
								'STORE_ID' => $storeId,
								'IS_BARCODE_MULTI' => false,
								'QUANTITY' => abs($productQuantity)
							);
						}
					}
				}
			}

			return $storeProductList;
		}

		private function checkProductInStores($products)
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
					continue;
				}
				elseif ($canAutoShipList[$productId] === false && count($productStoreDataList[$productId]) > 1)
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

			return $result;

		}

		private static function isExistsBarcode(array $list)
		{
			$resultValue = false;
			foreach ($list as $shipmentItemIndex => $storeDataList)
			{
				foreach ($storeDataList as $storeId => $storeValue)
				{
					if (is_array($storeValue['BARCODE']) && $storeValue['IS_BARCODE_MULTI'] === true)
					{
						foreach ($storeValue["BARCODE"] as $barcodeId => $barcodeValue)
						{
							if (strval(trim($barcodeValue)) == "")
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

			$productIdList = array_keys($products);
			$resultList = array();
			$productQuantityList = array();

			$productStoreDataList = array();
			$resProps = \CCatalogStoreProduct::GetList(
				array(),
				array(
					"PRODUCT_ID" => $productIdList,
				),
				false,
				false,
				array('ID', 'AMOUNT', 'STORE_ID', 'PRODUCT_ID')
			);
			while($productStoreData = $resProps->Fetch())
			{
				$productStoreDataList[$productStoreData['PRODUCT_ID']][$productStoreData['STORE_ID']] = $productStoreData;
			}

			foreach ($products as $productId => $productData)
			{
				if (empty($productData['CATALOG']))
					continue;

				$productStoreData = $productStoreDataList[$productId];

				$storeDataList = $productData['STORE_DATA_LIST'];

				if (!empty($storeDataList))
				{
					foreach ($storeDataList as $shipmentItemIndex => $barcodeList)
					{
						foreach($barcodeList as $storeId => $storeDataValue)
						{
							if (!empty($storeDataValue))
							{
								if (!isset($productStoreData[$storeId]) || ($productStoreData[$storeId]["AMOUNT"] < $storeDataValue["QUANTITY"]))
								{
									$result->addError(
										new Sale\ResultError(
											Main\Localization\Loc::getMessage(
												"DDCT_DEDUCTION_QUANTITY_STORE_ERROR",
												array_merge(
													self::getProductCatalogInfo($productId),
													array("#STORE_ID#" => $storeId)
												)
											), "DDCT_DEDUCTION_QUANTITY_STORE_ERROR"
										)
									);
									continue;
								}
								else
								{
									if (!isset($productQuantityList[$productId]))
									{
										$productQuantityList[$productId] = 0;
									}

									$productQuantityList[$productId] += $storeDataValue["QUANTITY"];

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

					if (!empty($productData['QUANTITY_LIST']))
					{
						foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
						{
							$productQuantityList[$productId] += $quantity;
						}
					}

				}
			}

			if (!empty($productQuantityList))
			{
				foreach ($productQuantityList as $amountProductId => $amountValue)
				{
					$product = $products[$amountProductId];
					$catalogData = $product['CATALOG'];

					$catalogReservedQuantity = floatval($catalogData['QUANTITY_RESERVED']);
					$catalogQuantity = 0;
					if (array_key_exists('QUANTITY', $catalogData))
					{
						$catalogQuantity = floatval($catalogData['QUANTITY']);
					}
					elseif (!empty($catalogData['PRICE_LIST']))
					{
						foreach ($catalogData['PRICE_LIST'] as $basketCode => $catalogValue)
						{
							$catalogQuantity += floatval($catalogValue['QUANTITY']);
						}
					}

					if ($product['RESERVED_QUANTITY_LIST'][$product['BASKET_CODE']] > 0)
					{
						$catalogQuantity += $catalogReservedQuantity;
					}

					if ($amountValue > $catalogQuantity)
					{
						$result->addError(
							new Sale\ResultError(
								Main\Localization\Loc::getMessage(
									"SALE_PROVIDER_SHIPMENT_QUANTITY_NOT_ENOUGH",
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

			$productQuantity = 0;

			if (array_key_exists('QUANTITY', $productData))
			{
				$productQuantity = $productData['QUANTITY'];
			}
			elseif (!empty($productData['QUANTITY_LIST']))
			{
				foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
				{
					$productQuantity += $quantity;
				}
			}

//		if ($productQuantity > 0)
//			return $result;

			if (!empty($storeDataList))
			{
				foreach ($storeDataList as $shipmentItemIndex => $storeData)
				{
					foreach ($storeData as $storeDataValue)
					{
						$storeId = $storeDataValue['STORE_ID'];

						if (intval($storeId) < -1 || intval($storeId) == 0
							|| !isset($storeDataValue["QUANTITY"]) || intval($storeDataValue["QUANTITY"]) < 0)
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

					if (isset($productData['BUNDLE_PARENT']) && $productData['BUNDLE_PARENT'] === true)
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
						'PRODUCTS_LIST_EXISTS_IN_STORE' => $resultList
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
				foreach ($storeData['BARCODE'] as $barcodeId => $barcodeValue)
				{
					if (strval(trim($barcodeValue)) == "" && $storeData['IS_BARCODE_MULTI'] === true)
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
						$fields = array(
							"STORE_ID" => static::CATALOG_PROVIDER_EMPTY_STORE_ID,
							"BARCODE" => $barcodeValue,
							"PRODUCT_ID" => $productId
						);

						if ($storeData['IS_BARCODE_MULTI'] === true)
						{
							$fields['STORE_ID'] = $storeId;
						}

						$dbres = \CCatalogStoreBarcode::GetList(
							array(),
							$fields,
							false,
							false,
							array("ID", "STORE_ID", "BARCODE", "PRODUCT_ID")
						);

						if (!$catalogStoreBasrcodeRes = $dbres->Fetch())
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
		private function canProductListAutoShip(array $products)
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
			$isOneStore = false;
			$isDefaultStore = false;

			$countStoresResult = $this->getStoresCount();
			if ($countStoresResult->isSuccess())
			{
				$countStores = $countStoresResult->get('STORES_COUNT');
			}

			$countStoresResult->getData();
			$defaultDeductionStore = Main\Config\Option::get("sale", "deduct_store_id", "", $context['SITE_ID']);
			foreach ($products as $productId => $productData)
			{
				if (isset($canAutoList[$productId]))
				{
					$resultList[$productId] = $canAutoList[$productId];
					continue;
				}

				$isOneStore = ($countStores == 1 || $countStores == -1);
				$isDefaultStore = ($defaultDeductionStore > 0);

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
					$isMulti = isset($productData['IS_BARCODE_MULTI']) && $productData['IS_BARCODE_MULTI'] === true;
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
				$productStoreList = array();
				$r = $this->getProductListStores($products);
				if ($r->isSuccess())
				{
					$productStoreData = $r->getData();
					if (array_key_exists('PRODUCT_STORES_LIST', $productStoreData))
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
							$storeDataList = $productStoreList[$productId];
							if (!empty($storeDataList))
							{
								foreach ($storeDataList as $storeId => $storeData)
								{
									if (floatval($storeData['AMOUNT']) > 0)
									{
										$countProductInStore++;
									}
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
						'PRODUCT_CAN_AUTOSHIP_LIST' => $resultList
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
				$isMulti = isset($product['IS_BARCODE_MULTI']) && $product['IS_BARCODE_MULTI'] === true;
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
				foreach ($productStoreDataList as $storeId => $storeData)
				{
					if (floatval($storeData['AMOUNT']) > 0)
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
		 * @return mixed
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
			foreach ($productStoreList as $productId => $productStoreDataList)
			{
				foreach ($productStoreDataList as $storeId =>$productStoreData)
				{
					$productId = $productStoreData['PRODUCT_ID'];
					if ($productStoreData['AMOUNT'] > 0)
					{
						if (!array_key_exists($productId, $resultList) || !array_key_exists($storeId, $resultList[$productId]))
						{
							$resultList[$productId][$storeId] = 0;
						}

						$resultList[$productId][$storeId] = $productStoreData['AMOUNT'];
					}
				}
			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'RESULT_LIST' => $resultList
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
					'STORES_COUNT' => $count
				)
			);

			return $result;
		}

		/**
		 * @return array|false
		 */
		private function getStoreIds()
		{
			$context = $this->getContext();

			$filterId = array('ACTIVE' => 'Y', 'SHIPPING_CENTER' => 'Y');
			if (isset($context['SITE_ID']) && $context['SITE_ID'] != '')
				$filterId['+SITE_ID'] = $context['SITE_ID'];

			$cacheId = md5(serialize($filterId));
			$storeIds = static::getHitCache(self::CACHE_STORE, $cacheId);
			if (empty($storeIds))
			{
				$storeIds = array();

				$filter = Main\Entity\Query::filter();
				$filter->where('ACTIVE', '=', 'Y');
				$filter->where('SHIPPING_CENTER', '=', 'Y');
				if (isset($context['SITE_ID']) && $context['SITE_ID'] != '')
				{
					$subFilter = Main\Entity\Query::filter();
					$subFilter->logic('or')->where('SITE_ID', '=', $context['SITE_ID'])->where('SITE_ID', '=', '')->whereNull('SITE_ID');
					$filter->where($subFilter);
					unset($subFilter);
				}

				$iterator = Catalog\StoreTable::getList(array(
					'select' => array('ID'),
					'filter' => $filter,
					'order' => array('ID' => 'ASC')
				));
				while ($row = $iterator->fetch())
					$storeIds[] = (int)$row['ID'];
				unset($row, $iterator, $filter);
				if (!empty($storeIds))
					static::setHitCache(self::CACHE_STORE, $cacheId, $storeIds);
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
				return $result;

			$resultList = array();

			$storeIds = $this->getStoreIds();
			if (empty($storeIds))
				return $result;

			$productGetIdList = array();
			foreach ($products as $productId => $productData)
			{
				$cacheId = md5($productId);

				$storeProductDataList = static::getHitCache(self::CACHE_STORE_PRODUCT, $cacheId);
				if (!empty($storeProductDataList))
				{
					$resultList[$productId] = $storeProductDataList;
				}
				else
				{
					$productGetIdList[$productId] = $productId;
				}

			}

			if (!empty($productGetIdList))
			{
				$iterator = Catalog\StoreProductTable::getList(array(
					'select' => array('PRODUCT_ID', 'AMOUNT', 'STORE_ID', 'STORE_NAME' => 'STORE.TITLE', 'ID'),
					'filter' => array('=PRODUCT_ID' => $productGetIdList, '@STORE_ID' => $storeIds),
					'order' => array('STORE_ID' => 'ASC')
				));
				while ($row = $iterator->fetch())
				{
					$resultList[$row['PRODUCT_ID']][$row['STORE_ID']] = $row;
				}

				foreach ($productGetIdList as $productId)
				{
					if (!empty($resultList[$productId]))
					{
						$cacheId = md5($productId);
						static::setHitCache(self::CACHE_STORE_PRODUCT, $cacheId, $resultList[$productId]);
					}
				}
			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'PRODUCT_STORES_LIST' => $resultList
					)
				);
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
		protected static function isExistsHitCache($type, $key, array $fields = array())
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
		 * @param string $key
		 * @param mixed $value
		 */
		protected static function setHitCache($type, $key, $value)
		{
			if (empty(self::$hitCache[$type]))
				self::$hitCache[$type] = array();

			if (empty(self::$hitCache[$type][$key]))
				self::$hitCache[$type][$key] = array();

			self::$hitCache[$type][$key] = $value;
		}

		/**
		 * @param string|null $type
		 */
		protected static function clearHitCache($type = null)
		{
			if ($type === null)
				self::$hitCache = array();

			if (!empty(self::$hitCache[$type]))
				unset(self::$hitCache[$type]);
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

		protected static function isNeedClearPublicCache($currentQuantity, $newQuantity, $quantityTrace, $canBuyZero, $ratio = 1)
		{
			if (!defined('BX_COMP_MANAGED_CACHE'))
				return false;
			if ($canBuyZero == 'Y' || $quantityTrace == 'N')
				return false;
			if ($currentQuantity * $newQuantity > 0)
				return false;
			return true;
		}

		protected static function clearPublicCache($productID, $productInfo = array())
		{
			$productID = (int)$productID;
			if ($productID <= 0)
				return;
			$iblockID = (int)(isset($productInfo['IBLOCK_ID']) ? $productInfo['IBLOCK_ID'] : \CIBlockElement::GetIBlockByID($productID));
			if ($iblockID <= 0)
				return;
			if (!isset(self::$clearAutoCache[$iblockID]))
			{
				\CIBlock::clearIblockTagCache($iblockID);
				self::$clearAutoCache[$iblockID] = true;
			}

			$productInfo['ID'] = $productID;
			$productInfo['ELEMENT_IBLOCK_ID'] = $iblockID;
			$productInfo['IBLOCK_ID'] = $iblockID;
			if (isset($productInfo['CAN_BUY_ZERO']))
				$productInfo['NEGATIVE_AMOUNT_TRACE'] = $productInfo['CAN_BUY_ZERO'];
			foreach (GetModuleEvents('catalog', 'OnProductQuantityTrace', true) as $eventData)
				ExecuteModuleEventEx($eventData, array($productID, $productInfo));
		}

		/**
		 * @param array $products
		 *
		 * @return Sale\Result
		 */
		public function getAvailableQuantity(array $products)
		{
			$result = new Sale\Result();
			$resultList = array();

			$isGotQuantityDataList = array();

			foreach ($products as $productId => $productData)
			{
				$catalogAvailableQuantity = QuantityControl::getAvailableQuantity($productId);
				$catalogQuantity = QuantityControl::getQuantity($productId);

				$productQuantity = 0;
				if (array_key_exists('QUANTITY', $productData))
				{
					$productQuantity = $productData['QUANTITY'];
				}
				elseif (!empty($productData['QUANTITY_LIST']))
				{
					foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
					{
						$productQuantity += $quantity;
					}
				}

				if ($catalogQuantity === null || ($catalogAvailableQuantity === null || $catalogAvailableQuantity < $productQuantity))
				{
					continue;
				}

				$isGotQuantityDataList[$productId] = true;

				$catalogSumQuantity = $catalogAvailableQuantity;
				$resultList[$productId] = $catalogSumQuantity >= $productQuantity ? $productQuantity : $catalogQuantity;
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
					$productReservedQuantity = 0;
					if (!empty($productData['RESERVED_QUANTITY_LIST']))
					{
						foreach ($productData['RESERVED_QUANTITY_LIST'] as $basketCode => $quantity)
						{
							$productReservedQuantity += $quantity;
						}
					}
					if (isset($isGotQuantityDataList[$productId]))
						continue;

					$catalogData = $productData['CATALOG'];

					$catalogQuantity = 0;
					$catalogReservedQuantity = $catalogData['QUANTITY_RESERVED'];

					QuantityControl::setReservedQuantity($productId, $catalogReservedQuantity);

					$productQuantity = 0;
					if (array_key_exists('QUANTITY', $productData))
					{
						$productQuantity = $productData['QUANTITY'];
					}
					elseif (!empty($productData['QUANTITY_LIST']))
					{
						foreach ($productData['QUANTITY_LIST'] as $basketCode => $quantity)
						{
							$productQuantity += $quantity;
						}
					}

					$resultList[$productId] = $productQuantity;

					if (isset($catalogData))
					{
						if (!empty($catalogData['PRICE_LIST']))
						{
							foreach ($catalogData['PRICE_LIST'] as $basketCode => $catalogValue)
							{
								$catalogQuantity += $catalogValue['QUANTITY'];
							}
						}
						elseif (array_key_exists('QUANTITY', $catalogData))
						{
							$catalogQuantity = $catalogData['QUANTITY'];
						}
					}

					QuantityControl::setQuantity($productId, $catalogQuantity);

					if ($catalogData["CAN_BUY_ZERO"] != "Y" && $catalogData["QUANTITY_TRACE"] == "Y")
					{
						$needQuantity = ($productQuantity - $productReservedQuantity);
						$resultList[$productId] = $catalogQuantity >= $needQuantity ? $productQuantity : $catalogQuantity;
					}
				}
			}

			if (!empty($resultList))
			{
				$result->setData(
					array(
						'AVAILABLE_QUANTITY_LIST' => $resultList
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

				if (isset($availableQuantityList['AVAILABLE_QUANTITY_LIST']))
				{
					$availableQuantityData = $availableQuantityList['AVAILABLE_QUANTITY_LIST'];
				}
			}

			$result->setData(
				array(
					'PRODUCT_DATA_LIST' => array(
						'PRICE_LIST' => $priceDataList,
						'AVAILABLE_QUANTITY_LIST' => $availableQuantityData
					)
				)
			);

			return $result;
		}

		/**
		 * @param $products
		 *
		 * @return bool
		 */
		private function isExistsCatalogData($products)
		{
			foreach ($products as $productId => $productData)
			{
				if (empty($productData['CATALOG']))
				{
					return false;
				}
			}
			return true;
		}
		/**
		 * @param array $products
		 * @param array $list
		 *
		 * @return array
		 */
		private static function removeNotExistsItemFromProducts(array $products, array $list)
		{
			$checkList = array_fill_keys($list, true);

			foreach ($products as $productId => $productData)
			{
				if (!isset($checkList[$productId]))
				{
					unset($products[$productId]);
				}
			}

			return $products;
		}

		/**
		 * @param array $list
		 *
		 * @return array
		 */
		private function getIblockData(array $list)
		{
			$resultList = array();
			$res = Catalog\CatalogIblockTable::getList(
				array(
					'select' => array('IBLOCK_ID', 'SUBSCRIPTION', 'PRODUCT_IBLOCK_ID', 'CATALOG_XML_ID' => 'IBLOCK.XML_ID'),
					'filter' => array('=IBLOCK_ID' => $list)
				)
			);
			while($iblockData = $res->fetch())
			{
				$resultList[$iblockData['IBLOCK_ID']] = $iblockData;
				if ($this->enableCache)
				{
					static::setHitCache(self::CACHE_CATALOG_IBLOCK_LIST, $iblockData['IBLOCK_ID'], $iblockData);
				}
			}

			return $resultList;
		}

		/**
		 * @param array $iblockList
		 * @param array $list
		 *
		 * @return array
		 */
		private static function removeNotExistsIblockFromList(array $iblockList, array $list)
		{
			$checkList = array();

			foreach ($list as $iblockId)
			{
				$checkList[$iblockId] = true;
			}

			foreach ($iblockList as $iblockId => $iblockProductIdList)
			{
				if (!isset($checkList[$iblockId]))
				{
					unset($iblockList[$iblockId]);
				}
			}

			return $iblockList;
		}

		/**
		 * @param array $iblockProductMap
		 *
		 * @return array
		 */
		private function checkSkuPermission(array $iblockProductMap)
		{
			$resultList = array();

			foreach ($iblockProductMap as $iblockId => $iblockData)
			{
				if ($iblockData['PRODUCT_IBLOCK_ID'] > 0 && !empty($iblockData['PRODUCT_LIST']))
				{
					$resultList = array_merge(
						$resultList,
						static::checkParentActivity($iblockData['PRODUCT_LIST'], $iblockData['PRODUCT_IBLOCK_ID'])
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
		private static function createIblockProductMap(array $iblockList, array $iblockDataList)
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
		private static function changeSubscribeProductQuantity(array $products, array $iblockProductMap)
		{
			$resultList = $products;

			foreach ($iblockProductMap as $iblockId => $iblockData)
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
							!empty($resultList[$productId]['QUANTITY_LIST'])
							&& is_array($resultList[$productId]['QUANTITY_LIST'])
						)
						{
							foreach (array_keys($resultList[$productId]['QUANTITY_LIST']) as $index)
							{
								$resultList[$productId]['QUANTITY_LIST'][$index] = 1;
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
		private static function getCatalogProducts(array $list, array $select)
		{
			if (empty($select))
				$select = array('*');
			elseif (!in_array('ID', $select))
				$select[] = 'ID';
			Main\Type\Collection::normalizeArrayValuesByInt($list, true);
			if (empty($list))
				return [];
			$resultList = [];
			$iterator = Catalog\ProductTable::getList([
				'select' => $select,
				'filter' => ['@ID' => $list]
			]);
			while ($row = $iterator->fetch())
			{
				Catalog\Product\SystemField::convertRow($row);
				$resultList[$row['ID']] = $row;
			}
			unset($row, $iterator);

			return $resultList;
		}

		/**
		 * @param null $id
		 *
		 * @return array
		 */
		private static function getMeasure($id = null)
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

			if (intval($id) <= 0)
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
		private function createProductPriceList(array $products, array $productPriceList, array $discountList = array())
		{
			$content = $this->getContext();

			$priceResultList = array();

			foreach ($productPriceList as $basketCode => $priceData)
			{
				if (!$priceData)
					continue;
				$priceResultList[$basketCode]['PRODUCT_PRICE_ID'] = $priceData['PRICE']['ID'];
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
								$productData['BASKET_CODE'] => $productData['QUANTITY']
							);
						}
						if (!empty($productData['QUANTITY_LIST']))
						{
							$quantityList = $productData['QUANTITY_LIST'];
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

		private static function setDataToProducts(array $products, array $resultData)
		{
			$resultList = array();
			foreach ($products as $productId => $productData)
			{
				$resultList[$productId] = $resultData[$productId];
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
		private static function createProductResult(array $products, array $items, array $priceList, array $productQuantityList)
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

				if (!empty($productData['QUANTITY_LIST']))
				{
					foreach($productData['QUANTITY_LIST'] as $basketCode => $quantity)
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
		private static function setCatalogDataToProducts(array $products, array $catalogDataList, array $options = array())
		{
			$resultData = array();
			foreach ($products as $productId => $productData)
			{
				if (!isset($catalogDataList[$productId]))
					continue;

				$catalogData = $catalogDataList[$productId];

				$resultData[$productId] = array(
					"CAN_BUY" => ($productData['PRODUCT_DATA']["ACTIVE"] == "Y" ? "Y" : "N"),
					"CAN_BUY_ZERO" => $catalogData["CAN_BUY_ZERO"],
					"QUANTITY_TRACE" => $catalogData["QUANTITY_TRACE"],
					'QUANTITY_RESERVED' => floatval($catalogData["QUANTITY_RESERVED"]),
					"CATALOG_XML_ID" => $productData['PRODUCT_DATA']["CATALOG_XML_ID"],
					"PRODUCT_XML_ID" => $productData['PRODUCT_DATA']["~XML_ID"]
				);

				if (is_array($options) && !in_array('CATALOG_DATA', $options))
				{
					$resultData[$productId] = array_merge(
						$resultData[$productId],
						array(
							"NAME" => $productData['PRODUCT_DATA']["~NAME"],
							"DETAIL_PAGE_URL" => $productData['PRODUCT_DATA']['~DETAIL_PAGE_URL'],
							"MEASURE_ID" => $catalogData["MEASURE"],
							"MEASURE_NAME" => $catalogData["MEASURE_NAME"],
							"MEASURE_CODE" => $catalogData["MEASURE_CODE"],
							"BARCODE_MULTI" => $catalogData["BARCODE_MULTI"],
							"WEIGHT" => (float)$catalogData['WEIGHT'],
							"DIMENSIONS" => serialize(
								array(
									"WIDTH" => $catalogData["WIDTH"],
									"HEIGHT" => $catalogData["HEIGHT"],
									"LENGTH" => $catalogData["LENGTH"]
								)
							),
							"TYPE" => ($catalogData["TYPE"] == \CCatalogProduct::TYPE_SET)
								? \CCatalogProductSet::TYPE_SET : null,
							"MARKING_CODE_GROUP" => $catalogData["MARKING_CODE_GROUP"]
						)
					);
				}

				$resultData[$productId]["VAT_INCLUDED"] = "Y";
			}

			return $resultData;
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
					foreach ($productData['SHIPMENT_ITEM_LIST'] as $shipmentItemIndex => $shipmentItem)
					{
						/** @var Sale\ShipmentItemCollection $shipmentItemCollection */
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
		private static function getProductCatalogInfo($productId)
		{
			$productId = (int)$productId;
			if ($productId <= 0)
				return array();

			if (!$product = static::getHitCache('IBLOCK_ELEMENT', $productId))
			{
				$productRes = \CIBlockElement::GetList(array(), array("ID" => $productId), false, false, array('ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID'));
				$product = $productRes->fetch();
				if ($product)
				{
					static::setHitCache('IBLOCK_ELEMENT', $productId, $product);
				}
			}

			return array(
				"#PRODUCT_ID#" => $product["ID"],
				"#PRODUCT_NAME#" => $product["NAME"],
			);
		}
	}
}