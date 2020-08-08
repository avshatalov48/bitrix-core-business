<?
/** @global CMain $APPLICATION */
use Bitrix\Main,
	Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Sale\DiscountCouponsManager,
	Bitrix\Currency,
	Bitrix\Catalog,
	Bitrix\Catalog\Product\Price,
	Bitrix\Iblock;

if (!Loader::includeModule('sale'))
	return false;

Loc::loadMessages(__FILE__);

/**
 * @deprecated deprecated since catalog 17.5.0
 * @see \Bitrix\Catalog\Product\CatalogProvider
 */
class CCatalogProductProvider implements IBXSaleProductProvider
{
	protected static $errors = array();

	protected static $arOneTimeCoupons = array();	// deprecated
	protected static $clearAutoCache = array();
	protected static $catalogList = array();
	protected static $userCache = array();
	protected static $priceTitleCache = array();
	protected static $proxyUserGroups = array();
	protected static $proxyIblockElementListPermN = array();
	protected static $proxyIblockElementListPermY = array();
	protected static $proxyIblockRights = array();
	protected static $proxyCatalogProduct = array();
	protected static $proxyStoresCount = array();

	protected static $hitCache = array();

	const CATALOG_PROVIDER_EMPTY_STORE_ID = 0;

	const CACHE_USER_GROUPS = 'USER_GROUPS';
	const CACHE_ITEM_WITHOUT_RIGHTS = 'IBLOCK_ELEMENT_PERM_N';
	const CACHE_ITEM_WITH_RIGHTS = 'IBLOCK_ELEMENT_PERM_Y';
	const CACHE_IBLOCK_RIGHTS_MODE = 'IBLOCK_RIGHTS_MODE';
	const CACHE_USER_RIGHTS = 'USER_RIGHT';
	const CACHE_PRODUCT = 'CATALOG_PRODUCT';
	const CACHE_VAT = 'VAT_INFO';
	const CACHE_IBLOCK_RIGHTS = 'IBLOCK_RIGHTS';
	const CACHE_STORE = 'CATALOG_STORE';
	const CACHE_STORE_PRODUCT = 'CATALOG_STORE_PRODUCT';
	const CACHE_PARENT_PRODUCT_ACTIVE = 'PARENT_PRODUCT_ACTIVE';

	/**
	 * @param array $arParams
	 * @return array|false
	 */
	public static function GetProductData($arParams)
	{
		$adminSection = (defined('ADMIN_SECTION') && ADMIN_SECTION === true);

		$useSaleDiscountOnly = CCatalogDiscount::isUsedSaleDiscountOnly();

		if (!isset($arParams['QUANTITY']) || (float)$arParams['QUANTITY'] <= 0)
			$arParams['QUANTITY'] = 0;

		if ($useSaleDiscountOnly && !isset($arParams['CHECK_DISCOUNT']))
			$arParams['CHECK_DISCOUNT'] = 'N';

		$arParams['RENEWAL'] = (isset($arParams['RENEWAL']) && $arParams['RENEWAL'] == 'Y' ? 'Y' : 'N');
		$arParams['CHECK_QUANTITY'] = (isset($arParams['CHECK_QUANTITY']) && $arParams["CHECK_QUANTITY"] == 'N' ? 'N' : 'Y');
		$arParams['CHECK_PRICE'] = (isset($arParams['CHECK_PRICE']) && $arParams['CHECK_PRICE'] == 'N' ? 'N' : 'Y');
		$arParams['CHECK_DISCOUNT'] = (isset($arParams['CHECK_DISCOUNT']) && $arParams['CHECK_DISCOUNT'] == 'N' ? 'N' : 'Y');
		$arParams['CHECK_COUPONS'] = (isset($arParams['CHECK_COUPONS']) && $arParams['CHECK_COUPONS'] == 'N' ? 'N' : 'Y');
		if (!$useSaleDiscountOnly)
		{
			if ($arParams['CHECK_DISCOUNT'] == 'N')
			{
				$arParams['CHECK_COUPONS'] = 'N';
			}
		}

		$arParams['AVAILABLE_QUANTITY'] = (isset($arParams['AVAILABLE_QUANTITY']) && $arParams['AVAILABLE_QUANTITY'] == 'Y' ? 'Y' : 'N');
		$arParams['SELECT_QUANTITY_TRACE'] = (isset($arParams['SELECT_QUANTITY_TRACE']) && $arParams['SELECT_QUANTITY_TRACE'] == 'Y' ? 'Y' : 'N');
		$arParams['SELECT_CHECK_MAX_QUANTITY'] = (isset($arParams['SELECT_CHECK_MAX_QUANTITY']) && $arParams['SELECT_CHECK_MAX_QUANTITY'] == 'Y' ? 'Y' : 'N');
		$arParams['BASKET_ID'] = (string)(isset($arParams['BASKET_ID']) ? $arParams['BASKET_ID'] : '0');
		$arParams['USER_ID'] = (isset($arParams['USER_ID']) ? (int)$arParams['USER_ID'] : 0);
		if ($arParams['USER_ID'] < 0)
			$arParams['USER_ID'] = 0;
		$arParams['SITE_ID'] = (isset($arParams['SITE_ID']) ? $arParams['SITE_ID'] : false);
		$strSiteID = $arParams['SITE_ID'];

		$arParams['CURRENCY'] = (isset($arParams['CURRENCY']) ? Currency\CurrencyManager::checkCurrencyID($arParams['CURRENCY']) : false);
		if ($arParams['CURRENCY'] === false)
			$arParams['CURRENCY'] = CSaleLang::GetLangCurrency($strSiteID ? $strSiteID : SITE_ID);

		$productID = (int)$arParams['PRODUCT_ID'];
		$quantity = (float)$arParams['QUANTITY'];
		$intUserID = (int)$arParams['USER_ID'];

		global $USER, $APPLICATION;

		$emptyResult = array();

		if ($adminSection)
		{
			if (!$userGroups = static::getHitCache(self::CACHE_USER_GROUPS, $intUserID))
			{
				$userGroups = self::getUserGroups($intUserID);
				static::setHitCache(self::CACHE_USER_GROUPS, $intUserID, $userGroups);
			}

			if (empty($userGroups))
				return $emptyResult;
		}
		else
		{
			//TODO: fix for crm
			$userGroups = array(2);
			if (isset($USER) && $USER instanceof CUser)
				$userGroups = $USER->GetUserGroupArray();
		}

		if (!$arProduct = static::getHitCache(self::CACHE_ITEM_WITH_RIGHTS, $productID))
		{
			$elementFilter = array(
				'ID' => $productID,
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R'
			);
			if ($adminSection)
				$elementFilter['PERMISSIONS_BY'] = $intUserID;

			$iterator = CIBlockElement::GetList(
				array(),
				$elementFilter,
				false,
				false,
				array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL', 'XML_ID')
			);
			if ($arProduct = $iterator->GetNext())
				static::setHitCache(self::CACHE_ITEM_WITH_RIGHTS, $productID, $arProduct);
			unset($dbIBlockElement, $elementFilter);
		}

		if(empty($arProduct) || !is_array($arProduct))
			return $emptyResult;

		if (!isset(self::$catalogList[$arProduct['IBLOCK_ID']]))
		{
			self::$catalogList[$arProduct['IBLOCK_ID']] = Catalog\CatalogIblockTable::getList(array(
				'select' => array('IBLOCK_ID', 'SUBSCRIPTION', 'PRODUCT_IBLOCK_ID', 'CATALOG_XML_ID' => 'IBLOCK.XML_ID'),
				'filter' => array('=IBLOCK_ID' => $arProduct['IBLOCK_ID'])
			))->fetch();
		}
		if (empty(self::$catalogList[$arProduct['IBLOCK_ID']]) || !is_array(self::$catalogList[$arProduct['IBLOCK_ID']]))
			return $emptyResult;
		if (self::$catalogList[$arProduct['IBLOCK_ID']]['SUBSCRIPTION'] == 'Y')
			$quantity = 1;

		if (self::$catalogList[$arProduct['IBLOCK_ID']]['PRODUCT_IBLOCK_ID'] > 0)
		{
			if (!static::checkParentActivity($arProduct['ID'], $arProduct['IBLOCK_ID']))
				return $emptyResult;
		}

		if (!$arCatalogProduct = static::getHitCache(self::CACHE_PRODUCT, $productID))
		{
			$select = array('ID', 'TYPE', 'AVAILABLE',
				'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO',
				'WEIGHT', 'WIDTH', 'HEIGHT', 'LENGTH',
				'BARCODE_MULTI',
				'MEASURE'
			);
			$select = array_merge($select, Catalog\Product\SystemField::getFieldList());
			$arCatalogProduct = Catalog\ProductTable::getList(array(
				'select' => $select,
				'filter' => array('=ID' => $productID)
			))->fetch();
			if (!empty($arCatalogProduct))
			{
				Catalog\Product\SystemField::convertRow($arCatalogProduct);
				$arCatalogProduct['QUANTITY'] = (float)$arCatalogProduct['QUANTITY'];
				static::setHitCache(self::CACHE_PRODUCT, $productID, $arCatalogProduct);
			}
		}
		if (empty($arCatalogProduct) || !is_array($arCatalogProduct))
		{
			$APPLICATION->ThrowException(Loc::getMessage("CATALOG_ERR_NO_PRODUCT"), "CATALOG_NO_QUANTITY_PRODUCT");
			return $emptyResult;
		}

		if (
			($arCatalogProduct['TYPE'] == Catalog\ProductTable::TYPE_SKU || $arCatalogProduct['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU)
			&& (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') != 'Y'
		)
		{
			$APPLICATION->ThrowException(Loc::getMessage("CATALOG_ERR_SKU_PRODUCT"), 'CATALOG_SKU_PRODUCT');
			return $emptyResult;
		}

		if (
			$arParams["CHECK_QUANTITY"] == "Y"
			&& $arCatalogProduct['AVAILABLE'] != Catalog\ProductTable::STATUS_YES
		)
		{
			$APPLICATION->ThrowException(
				Loc::getMessage("CATALOG_NO_QUANTITY_PRODUCT", array("#NAME#" => $arProduct["NAME"])),
				"CATALOG_NO_QUANTITY_PRODUCT"
			);
			return $emptyResult;
		}

		if ($arCatalogProduct['TYPE'] == Catalog\ProductTable::TYPE_SET)
		{
			static::$errors = array();
			if (!static::checkProductSet($productID))
			{
				$APPLICATION->ThrowException(implode(', ', static::$errors), 'NO_PRODUCT');
				static::$errors = array();
				return $emptyResult;
			}
		}

		if ($arCatalogProduct['TYPE'] == Catalog\ProductTable::TYPE_OFFER)
		{
			if (mb_strpos($arProduct["~XML_ID"], '#') === false)
			{
				$parent = \CCatalogSku::GetProductInfo($arProduct['ID'], $arProduct['IBLOCK_ID']);
				if (!empty($parent))
				{
					$parentIterator = Iblock\ElementTable::getList([
						'select' => ['ID', 'XML_ID'],
						'filter' => ['=ID' => $parent['ID']]
					]);
					$parentData = $parentIterator->fetch();
					if (!empty($parentData))
					{
						$arProduct['~XML_ID'] = $parentData['XML_ID'].'#'.$arProduct['~XML_ID'];
					}
					unset($parentData, $parentIterator);
				}
				unset($parent);
			}
		}

		$dblQuantity = $arCatalogProduct['QUANTITY'];
		$quantityLimited = ($arCatalogProduct['QUANTITY_TRACE'] == Catalog\ProductTable::STATUS_YES
			&& $arCatalogProduct['CAN_BUY_ZERO'] == Catalog\ProductTable::STATUS_NO);
		$quantityLimitExceeded = ($quantityLimited && $dblQuantity < $quantity);

		$arCatalogProduct['MEASURE'] = (int)$arCatalogProduct['MEASURE'];
		$arCatalogProduct['MEASURE_NAME'] = '';
		$arCatalogProduct['MEASURE_CODE'] = 0;
		if ($arCatalogProduct['MEASURE'] <= 0)
		{
			$arMeasure = CCatalogMeasure::getDefaultMeasure(true, true);
			$arCatalogProduct['MEASURE_NAME'] = $arMeasure['~SYMBOL_RUS'];
			$arCatalogProduct['MEASURE_CODE'] = $arMeasure['CODE'];
		}
		else
		{
			$rsMeasures = CCatalogMeasure::getList(
				array(),
				array('ID' => $arCatalogProduct['MEASURE']),
				false,
				false,
				array('ID', 'SYMBOL_RUS', 'CODE')
			);
			if ($arMeasure = $rsMeasures->Fetch())
			{
				$arCatalogProduct['MEASURE_NAME'] = $arMeasure['SYMBOL_RUS'];
				$arCatalogProduct['MEASURE_CODE'] = $arMeasure['CODE'];
			}
		}

		$arResult = array(
			"NAME" => $arProduct["~NAME"],
			"CAN_BUY" => "Y",
			"DETAIL_PAGE_URL" => $arProduct['~DETAIL_PAGE_URL'],
			"BARCODE_MULTI" => $arCatalogProduct["BARCODE_MULTI"],
			"WEIGHT" => (float)$arCatalogProduct['WEIGHT'],
			"DIMENSIONS" => serialize(array(
				"WIDTH" => $arCatalogProduct["WIDTH"],
				"HEIGHT" => $arCatalogProduct["HEIGHT"],
				"LENGTH" => $arCatalogProduct["LENGTH"]
			)),
			"TYPE" => ($arCatalogProduct["TYPE"] == Catalog\ProductTable::TYPE_SET ? CCatalogProductSet::TYPE_SET : null),
			"MARKING_CODE_GROUP" => $arCatalogProduct["MARKING_CODE_GROUP"],
			"VAT_INCLUDED" => "Y",
			"MEASURE_ID" => $arCatalogProduct['MEASURE'],
			"MEASURE_NAME" => $arCatalogProduct['MEASURE_NAME'],
			"MEASURE_CODE" => $arCatalogProduct['MEASURE_CODE'],
			"CATALOG_XML_ID" => self::$catalogList[$arProduct['IBLOCK_ID']]['CATALOG_XML_ID'],
			"PRODUCT_XML_ID" => $arProduct['~XML_ID']
		);

		if ($arParams['SELECT_QUANTITY_TRACE'] == "Y")
			$arResult["QUANTITY_TRACE"] = $arCatalogProduct["QUANTITY_TRACE"];
		if ($arParams['SELECT_CHECK_MAX_QUANTITY'] == 'Y')
			$arResult['CHECK_MAX_QUANTITY'] = ($quantityLimited ? 'Y' : 'N');

		if ($arParams["CHECK_QUANTITY"] == "Y")
		{
			$arResult["QUANTITY"] = ($quantityLimitExceeded ? $dblQuantity : $quantity);
			if ($quantityLimitExceeded)
			{
				$APPLICATION->ThrowException(
					Loc::getMessage(
						"CATALOG_QUANTITY_NOT_ENOGH",
						array(
							"#NAME#" => $arProduct["NAME"],
							"#CATALOG_QUANTITY#" => $dblQuantity,
							"#QUANTITY#" => $quantity,
							'#MEASURE_NAME#' => $arCatalogProduct['MEASURE_NAME']
						)
					),
					"CATALOG_QUANTITY_NOT_ENOGH"
				);
			}
		}
		else
		{
			$arResult["QUANTITY"] = $arParams["QUANTITY"];
		}

		if ($arParams["AVAILABLE_QUANTITY"] == "Y")
			$arResult["AVAILABLE_QUANTITY"] = ($quantityLimitExceeded ? $dblQuantity : $quantity);

		if ($arParams["CHECK_PRICE"] == "Y")
		{
			$productHash = array(
				'MODULE_ID' => ($useSaleDiscountOnly ? 'sale' : 'catalog'),
				'PRODUCT_ID' => $productID,
				'BASKET_ID' => $arParams['BASKET_ID']
			);

			$arCoupons = array();
			if ($arParams['CHECK_COUPONS'] == 'Y')
			{
				if ($useSaleDiscountOnly)
				{
					$arCoupons = DiscountCouponsManager::getForApply(array('MODULE_ID' => 'sale'), $productHash, true);
				}
				else
				{
					$arCoupons = DiscountCouponsManager::getForApply(array('MODULE_ID' => 'catalog'), $productHash, true);
				}
				if (!empty($arCoupons))
					$arCoupons = array_keys($arCoupons);
			}
			if ($adminSection)
			{
				if ($intUserID > 0)
					CCatalogDiscountSave::SetDiscountUserID($intUserID);
				else
					CCatalogDiscountSave::Disable();
			}

			Price\Calculation::pushConfig();
			Price\Calculation::setConfig(array(
				'CURRENCY' => $arParams['CURRENCY'],
				'PRECISION' => (int)Main\Config\Option::get('sale', 'value_precision'),
				'USE_DISCOUNTS' => $arParams['CHECK_DISCOUNT'] == 'Y',
				'RESULT_WITH_VAT' => true,
				'RESULT_MODE' => Catalog\Product\Price\Calculation::RESULT_MODE_RAW
			));

			$arPrice = CCatalogProduct::GetOptimalPrice(
				$productID,
				$quantity,
				$userGroups,
				$arParams['RENEWAL'],
				array(),
				($adminSection ? $strSiteID : false),
				$arCoupons
			);

			if (empty($arPrice))
			{
				if ($nearestQuantity = CCatalogProduct::GetNearestQuantityPrice($productID, $quantity, $userGroups))
				{
					$quantity = $nearestQuantity;
					$arPrice = CCatalogProduct::GetOptimalPrice(
						$productID,
						$quantity,
						$userGroups,
						$arParams['RENEWAL'],
						array(),
						($adminSection ? $strSiteID : false),
						$arCoupons
					);
				}
			}

			Price\Calculation::popConfig();

			unset($userGroups);

			if ($adminSection)
			{
				if ($intUserID > 0)
					CCatalogDiscountSave::ClearDiscountUserID();
				else
					CCatalogDiscountSave::Enable();
			}

			if (empty($arPrice))
				return $emptyResult;

			$arDiscountList = array();
			if (empty($arPrice['DISCOUNT_LIST']) && !empty($arPrice['DISCOUNT']) && is_array($arPrice['DISCOUNT']))
				$arPrice['DISCOUNT_LIST'] = array($arPrice['DISCOUNT']);
			if (!empty($arPrice['DISCOUNT_LIST']))
			{
				$appliedCoupons = array();
				foreach ($arPrice['DISCOUNT_LIST'] as &$arOneDiscount)
				{
					$arDiscountList[] = CCatalogDiscount::getDiscountDescription($arOneDiscount);

					if (!empty($arOneDiscount['COUPON']))
						$appliedCoupons[] = $arOneDiscount['COUPON'];
				}
				unset($arOneDiscount);
				if (!empty($appliedCoupons))
					$resultApply = DiscountCouponsManager::setApplyByProduct($productHash, $appliedCoupons);
				unset($resultApply, $appliedCoupons);
			}

			if (empty($arPrice['PRICE']['CATALOG_GROUP_NAME']))
			{
				if (!empty($arPrice['PRICE']['CATALOG_GROUP_ID']))
				{
					$priceName = self::getPriceTitle($arPrice['PRICE']['CATALOG_GROUP_ID']);
					if ($priceName != '')
						$arPrice['PRICE']['CATALOG_GROUP_NAME'] = $priceName;
					unset($priceName);
				}
			}

			$arResult['PRODUCT_PRICE_ID'] = $arPrice['PRICE']['ID'];
			$arResult['NOTES'] = $arPrice['PRICE']['CATALOG_GROUP_NAME'];
			$arResult['VAT_RATE'] = $arPrice['PRICE']['VAT_RATE'];
			$arResult['DISCOUNT_NAME'] = null;
			$arResult['DISCOUNT_COUPON'] = null;
			$arResult['DISCOUNT_VALUE'] = null;
			$arResult['DISCOUNT_LIST'] = array();

			if (empty($arPrice['RESULT_PRICE']) || !is_array($arPrice['RESULT_PRICE']))
				$arPrice['RESULT_PRICE'] = CCatalogDiscount::calculateDiscountList($arPrice['PRICE'], $arParams['CURRENCY'], $arDiscountList, true);

			$arResult['PRICE_TYPE_ID'] = $arPrice['RESULT_PRICE']['PRICE_TYPE_ID'];
			$arResult['BASE_PRICE'] = $arPrice['RESULT_PRICE']['BASE_PRICE'];
			$arResult['PRICE'] = $arPrice['RESULT_PRICE']['DISCOUNT_PRICE'];
			$arResult['CURRENCY'] = $arPrice['RESULT_PRICE']['CURRENCY'];
			$arResult['DISCOUNT_PRICE'] = $arPrice['RESULT_PRICE']['DISCOUNT'];
			if ($arParams['CHECK_DISCOUNT'] == 'Y')
			{
				if (isset($arPrice['RESULT_PRICE']['PERCENT']))
					$arResult['DISCOUNT_VALUE'] = ($arPrice['RESULT_PRICE']['PERCENT'] > 0 ? $arPrice['RESULT_PRICE']['PERCENT'] . '%' : null);
			}

			if (!empty($arDiscountList))
				$arResult['DISCOUNT_LIST'] = $arDiscountList;
			if (!empty($arPrice['DISCOUNT']))
			{
				$arResult['DISCOUNT_NAME'] = '['.$arPrice['DISCOUNT']['ID'].'] '.$arPrice['DISCOUNT']['NAME'];
				if (!empty($arPrice['DISCOUNT']['COUPON']))
					$arResult['DISCOUNT_COUPON'] = $arPrice['DISCOUNT']['COUPON'];

				if (empty($arResult['DISCOUNT_LIST']))
					$arResult['DISCOUNT_LIST'] = array($arPrice['DISCOUNT']);
			}
		}
		else
		{
			$vatRate = 0.0;

			if (!$arVAT = static::getHitCache(self::CACHE_VAT, $productID))
			{
				$rsVAT = CCatalogProduct::GetVATInfo($productID);
				if ($arVAT = $rsVAT->Fetch())
					static::setHitCache(self::CACHE_VAT, $productID, $arVAT);
				unset($rsVAT);
			}

			if (!empty($arVAT) && is_array($arVAT))
				$vatRate = (float)$arVAT['RATE'] * 0.01;

			$arResult['VAT_RATE'] = $vatRate;
		}

		return $arResult;
	}

	/**
	 * @param array $arParams
	 * @return array|false
	 */
	public static function OrderProduct($arParams)
	{
		$adminSection = (defined('ADMIN_SECTION') && ADMIN_SECTION === true);

		$useSaleDiscountOnly = CCatalogDiscount::isUsedSaleDiscountOnly();

		if ($useSaleDiscountOnly && !isset($arParams['CHECK_DISCOUNT']))
			$arParams['CHECK_DISCOUNT'] = 'N';

		$arParams['RENEWAL'] = (isset($arParams['RENEWAL']) && $arParams['RENEWAL'] == 'Y' ? 'Y' : 'N');
		$arParams['CHECK_QUANTITY'] = (isset($arParams['CHECK_QUANTITY']) && $arParams['CHECK_QUANTITY'] == 'N' ? 'N' : 'Y');
		$arParams['CHECK_DISCOUNT'] = (isset($arParams['CHECK_DISCOUNT']) && $arParams['CHECK_DISCOUNT'] == 'N' ? 'N' : 'Y');
		$arParams['USER_ID'] = (isset($arParams['USER_ID']) ? (int)$arParams['USER_ID'] : 0);
		if ($arParams['USER_ID'] < 0)
			$arParams['USER_ID'] = 0;
		$arParams['SITE_ID'] = (isset($arParams['SITE_ID']) ? $arParams['SITE_ID'] : false);
		$strSiteID = $arParams['SITE_ID'];
		$arParams['BASKET_ID'] = (string)(isset($arParams['BASKET_ID']) ? $arParams['BASKET_ID'] : '0');

		$arParams['CURRENCY'] = (isset($arParams['CURRENCY']) ? Currency\CurrencyManager::checkCurrencyID($arParams['CURRENCY']) : false);
		if ($arParams['CURRENCY'] === false)
			$arParams['CURRENCY'] = CSaleLang::GetLangCurrency($strSiteID ? $strSiteID : SITE_ID);

		global $USER;

		$productID = (int)$arParams['PRODUCT_ID'];
		$quantity = (float)$arParams['QUANTITY'];
		$intUserID = (int)$arParams['USER_ID'];

		$arResult = array();

		if ($adminSection)
		{
			if ($intUserID == 0)
				return $arResult;

			if (!$userGroups = static::getHitCache(self::CACHE_USER_GROUPS, $intUserID))
			{
				$userGroups = self::getUserGroups($intUserID);
				static::setHitCache(self::CACHE_USER_GROUPS, $intUserID, $userGroups);
			}

			if (empty($userGroups))
				return $arResult;
		}
		else
		{
			$userGroups = array(2);
			if (isset($USER) && $USER instanceof CUser)
				$userGroups = $USER->GetUserGroupArray();
		}

		if (!$arProduct = static::getHitCache(self::CACHE_ITEM_WITH_RIGHTS, $productID))
		{
			$elementFilter = array(
				'ID' => $productID,
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'Y',
				'MIN_PERMISSION' => 'R'
			);
			if ($adminSection)
				$elementFilter['PERMISSIONS_BY'] = $intUserID;

			$iterator = CIBlockElement::GetList(
				array(),
				$elementFilter,
				false,
				false,
				array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL')
			);
			if ($arProduct = $iterator->GetNext())
				static::setHitCache(self::CACHE_ITEM_WITH_RIGHTS, $productID, $arProduct);
			unset($dbIBlockElement, $elementFilter);
		}

		if (empty($arProduct) || !is_array($arProduct))
			return $arResult;

		if (!static::checkParentActivity($arProduct['ID'], $arProduct['IBLOCK_ID']))
			return $arResult;

		if (!$arCatalogProduct = static::getHitCache(self::CACHE_PRODUCT, $productID))
		{
			$select = array('ID', 'TYPE', 'AVAILABLE',
				'QUANTITY', 'QUANTITY_TRACE', 'CAN_BUY_ZERO',
				'WEIGHT', 'WIDTH', 'HEIGHT', 'LENGTH',
				'BARCODE_MULTI',
				'MEASURE'
			);
			$select = array_merge($select, Catalog\Product\SystemField::getFieldList());
			$arCatalogProduct = Catalog\ProductTable::getList(array(
				'select' => $select,
				'filter' => array('=ID' => $productID)
			))->fetch();
			if (!empty($arCatalogProduct))
			{
				Catalog\Product\SystemField::convertRow($arCatalogProduct);
				$arCatalogProduct['QUANTITY'] = (float)$arCatalogProduct['QUANTITY'];
				static::setHitCache(self::CACHE_PRODUCT, $productID, $arCatalogProduct);
			}
		}

		if (empty($arCatalogProduct) || !is_array($arCatalogProduct))
			return $arResult;

		if (
			($arCatalogProduct['TYPE'] == Catalog\ProductTable::TYPE_SKU || $arCatalogProduct['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU)
			&& (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') != 'Y'
		)
			return $arResult;

		if (
			$arParams["CHECK_QUANTITY"] == "Y"
			&& $arCatalogProduct['AVAILABLE'] != Catalog\ProductTable::STATUS_YES
		)
			return $arResult;

		if ($arCatalogProduct['TYPE'] == Catalog\ProductTable::TYPE_SET)
		{
			static::$errors = array();
			if (!static::checkProductSet($productID))
			{
				static::$errors = array();
				return $arResult;
			}
		}

		if ($adminSection)
			CCatalogDiscountSave::SetDiscountUserID($intUserID);

		$productHash = array(
			'MODULE' => ($useSaleDiscountOnly ? 'sale' : 'catalog'),
			'PRODUCT_ID' => $productID,
			'BASKET_ID' => $arParams['BASKET_ID']
		);
		$arCoupons = DiscountCouponsManager::getForApply(array(), $productHash, true);

		if (!empty($arCoupons))
			$arCoupons = array_keys($arCoupons);

		Price\Calculation::pushConfig();
		Price\Calculation::setConfig(array(
			'CURRENCY' => $arParams['CURRENCY'],
			'PRECISION' => (int)Main\Config\Option::get('sale', 'value_precision'),
			'USE_DISCOUNTS' => $arParams['CHECK_DISCOUNT'] == 'Y',
			'RESULT_WITH_VAT' => true,
			'RESULT_MODE' => Catalog\Product\Price\Calculation::RESULT_MODE_RAW
		));

		$arPrice = CCatalogProduct::GetOptimalPrice(
			$productID,
			$quantity,
			$userGroups,
			$arParams['RENEWAL'],
			array(),
			($adminSection ? $strSiteID : false),
			$arCoupons
		);

		if (empty($arPrice))
		{
			if ($nearestQuantity = CCatalogProduct::GetNearestQuantityPrice($productID, $quantity, $userGroups))
			{
				$quantity = $nearestQuantity;
				$arPrice = CCatalogProduct::GetOptimalPrice(
					$productID,
					$quantity,
					$userGroups,
					$arParams['RENEWAL'],
					array(),
					($adminSection ? $strSiteID : false),
					$arCoupons
				);
			}
		}

		Price\Calculation::popConfig();

		unset($userGroups);
		if ($adminSection)
			CCatalogDiscountSave::ClearDiscountUserID();

		if (empty($arPrice))
			return $arResult;

		$arDiscountList = array();
		if (empty($arPrice['DISCOUNT_LIST']) && !empty($arPrice['DISCOUNT']) && is_array($arPrice['DISCOUNT']))
			$arPrice['DISCOUNT_LIST'] = array($arPrice['DISCOUNT']);
		if (!empty($arPrice['DISCOUNT_LIST']))
		{
			$appliedCoupons = array();
			foreach ($arPrice['DISCOUNT_LIST'] as &$arOneDiscount)
			{
				$arDiscountList[] = CCatalogDiscount::getDiscountDescription($arOneDiscount);

				if (!empty($arOneDiscount['COUPON']))
					$appliedCoupons[] = $arOneDiscount['COUPON'];
			}
			unset($arOneDiscount);
			if (!empty($appliedCoupons))
				$resultApply = DiscountCouponsManager::setApplyByProduct($productHash, $appliedCoupons);
			unset($resultApply, $appliedCoupons);
		}

		if (empty($arPrice['PRICE']['CATALOG_GROUP_NAME']))
		{
			if (!empty($arPrice['PRICE']['CATALOG_GROUP_ID']))
			{
				$priceName = self::getPriceTitle($arPrice['PRICE']['CATALOG_GROUP_ID']);
				if ($priceName != '')
					$arPrice['PRICE']['CATALOG_GROUP_NAME'] = $priceName;
				unset($priceName);
			}
		}

		if (empty($arPrice['RESULT_PRICE']) || !is_array($arPrice['RESULT_PRICE']))
			$arPrice['RESULT_PRICE'] = CCatalogDiscount::calculateDiscountList($arPrice['PRICE'], $arParams['CURRENCY'], $arDiscountList, true);

		$arResult = array(
			'PRODUCT_PRICE_ID' => $arPrice['PRICE']['ID'],
//			"AVAILABLE_QUANTITY" => $arCatalogProduct["QUANTITY"],
			'PRICE_TYPE_ID' => $arPrice['RESULT_PRICE']['PRICE_TYPE_ID'],
			'BASE_PRICE' => $arPrice['RESULT_PRICE']['BASE_PRICE'],
			'PRICE' => $arPrice['RESULT_PRICE']['DISCOUNT_PRICE'],
			'VAT_RATE' => $arPrice['PRICE']['VAT_RATE'],
			"CURRENCY" => $arPrice['RESULT_PRICE']['CURRENCY'],
			"WEIGHT" => (float)$arCatalogProduct["WEIGHT"],
			"DIMENSIONS" => serialize(array(
				"WIDTH" => $arCatalogProduct["WIDTH"],
				"HEIGHT" => $arCatalogProduct["HEIGHT"],
				"LENGTH" => $arCatalogProduct["LENGTH"]
			)),
			"NAME" => $arProduct["~NAME"],
			"CAN_BUY" => "Y",
			"DETAIL_PAGE_URL" => $arProduct['~DETAIL_PAGE_URL'],
			"NOTES" => $arPrice["PRICE"]["CATALOG_GROUP_NAME"],
			"DISCOUNT_PRICE" => $arPrice['RESULT_PRICE']['DISCOUNT'],
			"TYPE" => ($arCatalogProduct["TYPE"] == Catalog\ProductTable::TYPE_SET ? CCatalogProductSet::TYPE_SET : null),
			"MARKING_CODE_GROUP" => $arCatalogProduct["MARKING_CODE_GROUP"],
			"DISCOUNT_VALUE" => ($arPrice['RESULT_PRICE']['PERCENT'] > 0 ? $arPrice['RESULT_PRICE']['PERCENT'].'%' : null),
			"DISCOUNT_NAME" => null,
			"DISCOUNT_COUPON" => null,
			"DISCOUNT_LIST" => array()
		);

		if ($arParams["CHECK_QUANTITY"] == "Y")
			$arResult["QUANTITY"] = $quantity;
		else
			$arResult["QUANTITY"] = $arParams["QUANTITY"];

		if (!empty($arDiscountList))
			$arResult['DISCOUNT_LIST'] = $arDiscountList;
		if (!empty($arPrice['DISCOUNT']))
		{
			$arResult['DISCOUNT_NAME'] = '['.$arPrice['DISCOUNT']['ID'].'] '.$arPrice['DISCOUNT']['NAME'];
			if (!empty($arPrice['DISCOUNT']['COUPON']))
				$arResult['DISCOUNT_COUPON'] = $arPrice['DISCOUNT']['COUPON'];

			if (empty($arResult['DISCOUNT_LIST']))
				$arResult['DISCOUNT_LIST'] = array($arPrice['DISCOUNT']);
		}

		$arResult["VAT_INCLUDED"] = "Y";

		return $arResult;
	}

	// in case product provider class is used,
	// instead of this method quantity is changed with ReserveProduct and DeductProduct methods
	public static function CancelProduct($arParams)
	{
		return true;
	}

	public static function DeliverProduct($arParams)
	{
		return CatalogPayOrderCallback(
			$arParams["PRODUCT_ID"],
			$arParams["USER_ID"],
			$arParams["PAID"],
			$arParams["ORDER_ID"]
		);
	}

	/**
	 * @deprecated
	 *
	 * @param array $arParams
	 * @return array|bool
	 */
	public static function ViewProduct($arParams)
	{
		if (!is_set($arParams["SITE_ID"]))
			$arParams["SITE_ID"] = SITE_ID;

		return CatalogViewedProductCallback(
			$arParams["PRODUCT_ID"],
			$arParams["USER_ID"],
			$arParams["SITE_ID"]
		);
	}

	public static function RecurringOrderProduct($arParams)
	{
		return CatalogRecurringCallback(
			$arParams["PRODUCT_ID"],
			$arParams["USER_ID"]
		);
	}

	public static function ReserveProduct($arParams)
	{
		global $APPLICATION;

		$arRes = array();
		$arFields = array();

		if ((int)$arParams["PRODUCT_ID"] <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("RSRV_INCORRECT_ID"), "NO_ORDER_ID");
			$arRes["RESULT"] = false;
			return $arRes;
		}

		$disableReservation = !static::isReservationEnabled();


		if ((string)$arParams["UNDO_RESERVATION"] != "Y")
			$arParams["UNDO_RESERVATION"] = "N";

		$arParams["QUANTITY_ADD"] = doubleval($arParams["QUANTITY_ADD"]);

		$rsProducts = CCatalogProduct::GetList(
			array(),
			array('ID' => $arParams["PRODUCT_ID"]),
			false,
			false,
			array(
				'ID',
				'CAN_BUY_ZERO',
				'QUANTITY_TRACE',
				'QUANTITY',
				'WEIGHT',
				'WIDTH',
				'HEIGHT',
				'LENGTH',
				'BARCODE_MULTI',
				'TYPE',
				'QUANTITY_RESERVED'
			)
		);

		$arProduct = $rsProducts->Fetch();
		if (empty($arProduct))
		{
			$APPLICATION->ThrowException(Loc::getMessage("RSRV_ID_NOT_FOUND", array("#PRODUCT_ID#" => $arParams["PRODUCT_ID"])), "ID_NOT_FOUND");
			$arRes["RESULT"] = false;
			return $arRes;
		}

		if (
			($arProduct['TYPE'] == Catalog\ProductTable::TYPE_SKU || $arProduct['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU)
			&& (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') != 'Y'
		)
		{
			$APPLICATION->ThrowException(Loc::getMessage("RSRV_SKU_FOUND", array("#PRODUCT_ID#" => $arParams["PRODUCT_ID"])), "SKU_FOUND");
			$arRes["RESULT"] = false;
			return $arRes;
		}


		if ($disableReservation)
		{
			$startReservedQuantity = 0;

			if ($arParams["UNDO_RESERVATION"] != "Y")
				$arFields = array("QUANTITY" => $arProduct["QUANTITY"] - $arParams["QUANTITY_ADD"]);
			else
				$arFields = array("QUANTITY" => $arProduct["QUANTITY"] + $arParams["QUANTITY_ADD"]);

			$arRes["RESULT"] = CCatalogProduct::Update($arParams["PRODUCT_ID"], $arFields);

			if (self::isNeedClearPublicCache(
				$arProduct['QUANTITY'],
				$arFields['QUANTITY'],
				$arProduct['QUANTITY_TRACE'],
				$arProduct['CAN_BUY_ZERO']
			))
			{
				$productInfo = array(
					'CAN_BUY_ZERO' => $arProduct['CAN_BUY_ZERO'],
					'QUANTITY_TRACE' => $arProduct['QUANTITY_TRACE'],
					'OLD_QUANTITY' => $arProduct['QUANTITY'],
					'QUANTITY' => $arFields['QUANTITY'],
					'DELTA' => $arFields['QUANTITY'] - $arProduct['QUANTITY']
				);
				self::clearPublicCache($arProduct['ID'], $productInfo);
			}
		}
		else
		{
			if ($arProduct["QUANTITY_TRACE"] == "N" || (isset($arParams["ORDER_DEDUCTED"]) && $arParams["ORDER_DEDUCTED"] == "Y"))
			{
				$arRes["RESULT"] = true;
				$arFields["QUANTITY_RESERVED"] = 0;
				$startReservedQuantity = 0;
			}
			else
			{
				$startReservedQuantity = $arProduct["QUANTITY_RESERVED"];

				if ($arParams["UNDO_RESERVATION"] == "N")
				{
					if ($arProduct["CAN_BUY_ZERO"] == "Y")
					{
						$arFields["QUANTITY_RESERVED"] = $arProduct["QUANTITY_RESERVED"] + $arParams["QUANTITY_ADD"];

						if ($arProduct["QUANTITY"] >= $arParams["QUANTITY_ADD"])
						{
							$arFields["QUANTITY"] = $arProduct["QUANTITY"] - $arParams["QUANTITY_ADD"];
						}
						elseif ($arProduct["QUANTITY"] < $arParams["QUANTITY_ADD"])
						{
							//reserve value, quantity will be negative
							$arFields["QUANTITY"] = $arProduct["QUANTITY"] - $arParams["QUANTITY_ADD"];
						}

						$arRes["RESULT"] = CCatalogProduct::Update($arParams["PRODUCT_ID"], $arFields);
					}
					else //CAN_BUY_ZERO = N
					{
						if ($arProduct["QUANTITY"] >= $arParams["QUANTITY_ADD"])
						{
							$arFields["QUANTITY"] = $arProduct["QUANTITY"] - $arParams["QUANTITY_ADD"];
							$arFields["QUANTITY_RESERVED"] = $arProduct["QUANTITY_RESERVED"] + $arParams["QUANTITY_ADD"];
						}
						elseif ($arProduct["QUANTITY"] < $arParams["QUANTITY_ADD"])
						{
							//reserve only possible value, quantity = 0

							$arRes["QUANTITY_NOT_RESERVED"] = $arParams["QUANTITY_ADD"] - $arProduct["QUANTITY"];

							$arFields["QUANTITY"] = 0;
							$arFields["QUANTITY_RESERVED"] = $arProduct["QUANTITY_RESERVED"] + $arProduct["QUANTITY"];

							$APPLICATION->ThrowException(Loc::getMessage("RSRV_QUANTITY_NOT_ENOUGH_ERROR", self::GetProductCatalogInfo($arParams["PRODUCT_ID"])), "ERROR_NOT_ENOUGH_QUANTITY");
						}
						if (self::isNeedClearPublicCache(
							$arProduct['QUANTITY'],
							$arFields['QUANTITY'],
							$arProduct['QUANTITY_TRACE'],
							$arProduct['CAN_BUY_ZERO']
						))
						{
							$productInfo = array(
								'CAN_BUY_ZERO' => $arProduct['CAN_BUY_ZERO'],
								'QUANTITY_TRACE' => $arProduct['QUANTITY_TRACE'],
								'OLD_QUANTITY' => $arProduct['QUANTITY'],
								'QUANTITY' => $arFields['QUANTITY'],
								'DELTA' => $arFields['QUANTITY'] - $arProduct['QUANTITY']
							);
							self::clearPublicCache($arProduct['ID'], $productInfo);
						}
						$arRes["RESULT"] = CCatalogProduct::Update($arParams["PRODUCT_ID"], $arFields);
					}
				}
				else //undo reservation
				{
					$arFields["QUANTITY"] = $arProduct["QUANTITY"] + $arParams["QUANTITY_ADD"];

					$needReserved = $arProduct["QUANTITY_RESERVED"] - $arParams["QUANTITY_ADD"];
					if ($arParams["QUANTITY_ADD"] > $arProduct["QUANTITY_RESERVED"])
					{
						$needReserved = $arProduct["QUANTITY_RESERVED"];
					}

					$arFields["QUANTITY_RESERVED"] = $needReserved;

					$arRes["RESULT"] = CCatalogProduct::Update($arParams["PRODUCT_ID"], $arFields);
					if (self::isNeedClearPublicCache(
						$arProduct['QUANTITY'],
						$arFields['QUANTITY'],
						$arProduct['QUANTITY_TRACE'],
						$arProduct['CAN_BUY_ZERO']
					))
					{
						$productInfo = array(
							'CAN_BUY_ZERO' => $arProduct['CAN_BUY_ZERO'],
							'QUANTITY_TRACE' => $arProduct['QUANTITY_TRACE'],
							'OLD_QUANTITY' => $arProduct['QUANTITY'],
							'QUANTITY' => $arFields['QUANTITY'],
							'DELTA' => $arFields['QUANTITY'] - $arProduct['QUANTITY']
						);
						self::clearPublicCache($arProduct['ID'], $productInfo);
					}
				}
			} //quantity trace
		}

		if ($arRes["RESULT"])
		{

			$needReserved = $arFields["QUANTITY_RESERVED"] - $startReservedQuantity;
			if ($startReservedQuantity > $arFields["QUANTITY_RESERVED"])
			{
				$needReserved = $arFields["QUANTITY_RESERVED"];
			}

			$arRes["QUANTITY_RESERVED"] = $needReserved;
		}
		else
		{
			$APPLICATION->ThrowException(Loc::getMessage("RSRV_UNKNOWN_ERROR", self::GetProductCatalogInfo($arParams["PRODUCT_ID"])), "UNKNOWN_RESERVATION_ERROR");
		}

		static::clearHitCache(self::CACHE_PRODUCT);

		$arRes['CAN_RESERVE'] = ($disableReservation ? "N" : "Y");

		return $arRes;
	}

	public static function DeductProduct($arParams)
	{
		global $APPLICATION;

		$arRes = array();
		$arFields = array();

		$basketItem = null;

		$useStoreControl = Catalog\Config\State::isUsedInventoryManagement();

		$disableReservation = !static::isReservationEnabled();


		if ((string)$arParams["UNDO_DEDUCTION"] != "Y")
			$arParams["UNDO_DEDUCTION"] = "N";


		if ((int)$arParams["PRODUCT_ID"] <= 0)
		{
			$APPLICATION->ThrowException(Loc::getMessage("RSRV_INCORRECT_ID"), "NO_ORDER_ID");
			$arRes["RESULT"] = false;
			return $arRes;
		}

		$isOrderConverted = \Bitrix\Main\Config\Option::get("main", "~sale_converted_15", 'Y');

		$arParams["QUANTITY"] = doubleval($arParams["QUANTITY"]);



		if ((string)$arParams["EMULATE"] != "Y")
			$arParams["EMULATE"] = "N";

		if ((string)$arParams["PRODUCT_RESERVED"] != "Y")
			$arParams["PRODUCT_RESERVED"] = "N";

		if (!isset($arParams["STORE_DATA"]))
			$arParams["STORE_DATA"] = array();

		if (!is_array($arParams["STORE_DATA"]))
			$arParams["STORE_DATA"] = array($arParams["STORE_DATA"]);

		$basketItem = null;
		if (isset($arParams["BASKET_ITEM"]) && $isOrderConverted != 'N')
		{
			if ($arParams["BASKET_ITEM"] instanceof \Bitrix\Sale\BasketItem)
			{
				/** @var \Bitrix\Sale\BasketItem $basketItem */
				$basketItem = $arParams["BASKET_ITEM"];
			}
		}

		$rsProducts = CCatalogProduct::GetList(
			array(),
			array('ID' => $arParams["PRODUCT_ID"]),
			false,
			false,
			array('ID', 'QUANTITY', 'QUANTITY_RESERVED', 'QUANTITY_TRACE', 'CAN_BUY_ZERO', 'TYPE')
		);

		if ($arProduct = $rsProducts->Fetch())
		{
			if (
				($arProduct['TYPE'] == Catalog\ProductTable::TYPE_SKU || $arProduct['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU)
				&& (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') != 'Y'
			)
			{
				$arRes["RESULT"] = false;
			}
			elseif ($arParams["UNDO_DEDUCTION"] == "N")
			{
				if ($arParams["EMULATE"] == "Y" || $arProduct["QUANTITY_TRACE"] == "N")
				{
					$arRes["RESULT"] = true;
				}
				else
				{
					if ($useStoreControl)
					{

						if ($isOrderConverted != 'N' && empty($arParams["STORE_DATA"]) && $basketItem)
						{
							if (static::canProductAutoShip($basketItem))
							{
								$arParams["STORE_DATA"] = static::getProductStoreData($basketItem, $arParams["QUANTITY"]);
							}
						}


						$barcodeMulti = false;
						if ($isOrderConverted != 'N')
						{
							$barcodeMulti = $basketItem->isBarcodeMulti();
						}


						if ($barcodeMulti)
						{
							if (!empty($arParams["STORE_DATA"]))
							{
								foreach ($arParams["STORE_DATA"] as $id => $arRecord)
								{
									if (!empty($arRecord["BARCODE"]) && is_array($arRecord["BARCODE"]))
									{
										foreach ($arRecord["BARCODE"] as $barcodeId => $barcodeValue)
										{
											if (strval(trim($barcodeValue)) == "")
											{
												$APPLICATION->ThrowException(Loc::getMessage("DDCT_DEDUCTION_MULTI_BARCODE_EMPTY", array_merge(self::GetProductCatalogInfo($arParams["PRODUCT_ID"]), array("#STORE_ID#" => $arRecord['STORE_ID']))), "DDCT_DEDUCTION_MULTI_BARCODE_EMPTY");
												$arRes["RESULT"] = false;
												return $arRes;

											}
										}
									}
									else
									{
										$APPLICATION->ThrowException(Loc::getMessage("DDCT_DEDUCTION_MULTI_BARCODE_EMPTY", array_merge(self::GetProductCatalogInfo($arParams["PRODUCT_ID"]), array("#STORE_ID#" => $arRecord['STORE_ID']))), "DDCT_DEDUCTION_MULTI_BARCODE_EMPTY");
										$arRes["RESULT"] = false;
										return $arRes;
									}
								}
							}
						}


						if (!empty($arParams["STORE_DATA"]))
						{
							foreach ($arParams["STORE_DATA"] as $id => $arRecord)
							{
								if (!empty($arRecord["BARCODE"]) && is_array($arRecord["BARCODE"]))
								{
									foreach($arRecord["BARCODE"] as $barcodeValue)
									{
										$arRes['BARCODE'][$barcodeValue] = false;
									}
								}
							}

							$totalAmount = 0;
							foreach ($arParams["STORE_DATA"] as $id => $arRecord)
							{
								if (!isset($arRecord["STORE_ID"]) || intval($arRecord["STORE_ID"]) < 0 || !isset($arRecord["QUANTITY"]) || intval($arRecord["QUANTITY"]) < 0)
								{
									$APPLICATION->ThrowException(Loc::getMessage("DDCT_DEDUCTION_STORE_ERROR", self::GetProductCatalogInfo($arParams["PRODUCT_ID"])), "DDCT_DEDUCTION_STORE_ERROR");
									$arRes["RESULT"] = false;
									return $arRes;
								}

								$rsProps = CCatalogStoreProduct::GetList(
									array(),
									array(
										"PRODUCT_ID" => $arParams["PRODUCT_ID"],
										"STORE_ID" => $arRecord["STORE_ID"]
									),
									false,
									false,
									array('ID', 'AMOUNT')
								);
								if ($arProp = $rsProps->Fetch())
								{
									if ($arProp["AMOUNT"] < $arRecord["QUANTITY"])
									{
										$APPLICATION->ThrowException(
											Loc::getMessage(
												"DDCT_DEDUCTION_QUANTITY_STORE_ERROR",
												array_merge(self::GetProductCatalogInfo($arParams["PRODUCT_ID"]), array("#STORE_ID#" => $arRecord["STORE_ID"]))
											),
											"DDCT_DEDUCTION_QUANTITY_STORE_ERROR"
										);
										$arRes["RESULT"] = false;
										return $arRes;
									}
									else
									{
										$res = CCatalogStoreProduct::Update($arProp["ID"], array("AMOUNT" => $arProp["AMOUNT"] - $arRecord["QUANTITY"]));

										if ($res)
										{
											$arRes["STORES"][$arRecord["STORE_ID"]] = $arRecord["QUANTITY"];
											$totalAmount += $arRecord["QUANTITY"];



											//deleting barcodes
											if (isset($arRecord["BARCODE"]) && is_array($arRecord["BARCODE"]) && count($arRecord["BARCODE"]) > 0)
											{

												foreach ($arRecord["BARCODE"] as $barcodeId => $barcodeValue)
												{
													if (strval(trim($barcodeValue)) == "" || !$barcodeMulti)
													{
														continue;
													}

													$arFields = array(
														"STORE_ID" => $arRecord["STORE_ID"],
														"BARCODE" => $barcodeValue,
														"PRODUCT_ID" => $arParams["PRODUCT_ID"]
													);

													$dbres = CCatalogStoreBarcode::GetList(
														array(),
														$arFields,
														false,
														false,
														array("ID", "STORE_ID", "BARCODE", "PRODUCT_ID")
													);

													if ($catalogStoreBarcodeRes = $dbres->Fetch())
													{
														CCatalogStoreBarcode::Delete($catalogStoreBarcodeRes["ID"]);
														$arRes['BARCODE'][$barcodeValue] = true;
													}
													else
													{
														$APPLICATION->ThrowException(
															Loc::getMessage(
																"DDCT_DEDUCTION_BARCODE_ERROR",
																array_merge(self::GetProductCatalogInfo($arParams["PRODUCT_ID"]), array("#BARCODE#" => $barcodeValue))
															),
															"DDCT_DEDUCTION_BARCODE_ERROR"
														);
														$arRes['BARCODE'][$barcodeValue] = false;
														$arRes["RESULT"] = false;
														return $arRes;
													}
												}
											}
										}
										else
										{
											$APPLICATION->ThrowException(Loc::getMessage("DDCT_DEDUCTION_SAVE_ERROR", self::GetProductCatalogInfo($arParams["PRODUCT_ID"])), "DDCT_DEDUCTION_SAVE_ERROR");
											$arRes["RESULT"] = false;
											return $arRes;
										}

									}
								}
							}

							//updating total sum
							if ($arParams["PRODUCT_RESERVED"] == "Y")
							{
								if ($totalAmount <= $arProduct["QUANTITY_RESERVED"])
								{
									$needReserved = $arProduct["QUANTITY_RESERVED"] - $totalAmount;
									if ($totalAmount > $arProduct["QUANTITY_RESERVED"])
									{
										$needReserved = $arProduct["QUANTITY_RESERVED"];
									}

									$arFields["QUANTITY_RESERVED"] = $needReserved;
								}
								else if ($totalAmount <= $arProduct["QUANTITY_RESERVED"] + $arProduct["QUANTITY"])
								{
									$arFields["QUANTITY_RESERVED"] = 0;
									$arFields["QUANTITY"] = $arProduct["QUANTITY"] - ($totalAmount - $arProduct["QUANTITY_RESERVED"]);
								}
								else //not enough products - don't deduct anything
								{
									$arRes["RESULT"] = false;
									return $arRes;
								}
							}
							else //product not reserved, use main quantity field to deduct from, quantity_reserved only if there is shortage in the main field
							{
								if ($totalAmount <= $arProduct["QUANTITY"])
								{
									$arFields["QUANTITY"] = $arProduct["QUANTITY"] - $totalAmount;
								}
								else if ($totalAmount <= $arProduct["QUANTITY_RESERVED"] + $arProduct["QUANTITY"])
								{
									$arFields["QUANTITY"] = 0;

									$minusQuantity = ($totalAmount - $arProduct["QUANTITY"]);

									$needReserved = $arProduct["QUANTITY_RESERVED"] - $minusQuantity;
									if ($minusQuantity > $arProduct["QUANTITY_RESERVED"])
									{
										$needReserved = $arProduct["QUANTITY_RESERVED"];
									}

									$arFields["QUANTITY_RESERVED"] = $needReserved;

								}
								else //not enough products - don't deduct anything
								{
									$arRes["RESULT"] = false;
									return $arRes;
								}
							}

							CCatalogProduct::Update($arParams["PRODUCT_ID"], $arFields);
							if (isset($arFields['QUANTITY']) && self::isNeedClearPublicCache(
								$arProduct['QUANTITY'],
								$arFields['QUANTITY'],
								$arProduct['QUANTITY_TRACE'],
								$arProduct['CAN_BUY_ZERO']
							))
							{
								$productInfo = array(
									'CAN_BUY_ZERO' => $arProduct['CAN_BUY_ZERO'],
									'QUANTITY_TRACE' => $arProduct['QUANTITY_TRACE'],
									'OLD_QUANTITY' => $arProduct['QUANTITY'],
									'QUANTITY' => $arFields['QUANTITY'],
									'DELTA' => $arFields['QUANTITY'] - $arProduct['QUANTITY']
								);
								self::clearPublicCache($arProduct['ID'], $productInfo);
							}

							$arRes["RESULT"] = true;
						}
						else
						{
							$APPLICATION->ThrowException(Loc::getMessage("DDCT_DEDUCTION_STORE_ERROR", self::GetProductCatalogInfo($arParams["PRODUCT_ID"])), "DEDUCTION_STORE_ERROR1");
							$arRes["RESULT"] = false;
							return $arRes;
						}
					}
					else // store control not used
					{
						if (($disableReservation && ($arParams['UNDO_DEDUCTION'] == "Y" || $arParams["PRODUCT_RESERVED"] == "N")) || !$disableReservation)
						{
							if ($arParams["QUANTITY"] <= $arProduct["QUANTITY_RESERVED"] + $arProduct["QUANTITY"])
							{
								if ($arParams["PRODUCT_RESERVED"] == "Y")
								{
									if ($arParams["QUANTITY"] <= $arProduct["QUANTITY_RESERVED"])
									{

										$needReserved = $arProduct["QUANTITY_RESERVED"] - $arParams["QUANTITY"];
										if ($arParams["QUANTITY"] > $arProduct["QUANTITY_RESERVED"])
										{
											$needReserved = $arProduct["QUANTITY_RESERVED"];
										}

										$arFields["QUANTITY_RESERVED"] = $needReserved;
									}
									else
									{
										$arFields["QUANTITY_RESERVED"] = 0;
										$arFields["QUANTITY"] = $arProduct["QUANTITY"] - ($arParams["QUANTITY"] - $arProduct["QUANTITY_RESERVED"]);
									}
								}
								else //product not reserved, use main quantity field to deduct from, quantity_reserved only if there is shortage in the main field
								{
									if ($arParams["QUANTITY"] <= $arProduct["QUANTITY"])
									{
										$arFields["QUANTITY"] = $arProduct["QUANTITY"] - $arParams["QUANTITY"];
									}
									else
									{
										$arFields["QUANTITY"] = 0;

										$minusQuantity = ($arParams["QUANTITY"] - $arProduct["QUANTITY"]);

										$needReserved = $arProduct["QUANTITY_RESERVED"] - $minusQuantity;
										if ($minusQuantity > $arProduct["QUANTITY_RESERVED"])
										{
											$needReserved = $arProduct["QUANTITY_RESERVED"];
										}

										$arFields["QUANTITY_RESERVED"] = $needReserved;
									}
								}

								$arRes["RESULT"] = CCatalogProduct::Update($arParams["PRODUCT_ID"], $arFields);
								if (isset($arFields['QUANTITY']) && self::isNeedClearPublicCache(
										$arProduct['QUANTITY'],
										$arFields['QUANTITY'],
										$arProduct['QUANTITY_TRACE'],
										$arProduct['CAN_BUY_ZERO']
									))
								{
									$productInfo = array(
										'CAN_BUY_ZERO' => $arProduct['CAN_BUY_ZERO'],
										'QUANTITY_TRACE' => $arProduct['QUANTITY_TRACE'],
										'OLD_QUANTITY' => $arProduct['QUANTITY'],
										'QUANTITY' => $arFields['QUANTITY'],
										'DELTA' => $arFields['QUANTITY'] - $arProduct['QUANTITY']
									);
									self::clearPublicCache($arProduct['ID'], $productInfo);
								}
							}
							else //not enough products - don't deduct anything
							{
								$APPLICATION->ThrowException(Loc::getMessage("DDCT_DEDUCTION_QUANTITY_ERROR", self::GetProductCatalogInfo($arParams["PRODUCT_ID"])), "DDCT_DEDUCTION_QUANTITY_ERROR");
								$arRes["RESULT"] = false;
								return $arRes;
							}
						}
						else
						{
							$arRes["RESULT"] = true;
						}

					} //store control
				} //emulate /quantity trace
			}
			else //undo deduction
			{
				if ($arParams["EMULATE"] == "Y" || $arProduct["QUANTITY_TRACE"] == "N")
				{
					$arRes["RESULT"] = true;
				}
				else
				{
					if ($useStoreControl)
					{
						if ($isOrderConverted != 'N' && empty($arParams["STORE_DATA"]) && $basketItem)
						{
							if (static::canProductAutoShip($basketItem))
							{
								$arParams["STORE_DATA"] = static::getProductStoreData($basketItem, $arParams["QUANTITY"]);
							}

							if (empty($arParams["STORE_DATA"]))
							{
								$arParams["STORE_DATA"] = static::getProductOneStoreData($basketItem, $arParams["QUANTITY"]);
							}
						}

						if (!empty($arParams["STORE_DATA"]))
						{
							$totalAddedAmount = 0;
							foreach ($arParams["STORE_DATA"] as $id => $arRecord)
							{
								$rsProps = CCatalogStoreProduct::GetList(
									array(),
									array(
										"PRODUCT_ID" => $arParams["PRODUCT_ID"],
										"STORE_ID" => $arRecord["STORE_ID"]
									),
									false,
									false,
									array('ID', 'AMOUNT')
								);

								if ($arProp = $rsProps->Fetch())
								{
									$res = CCatalogStoreProduct::Update(
										$arProp["ID"],
										array("AMOUNT" => $arProp["AMOUNT"] + $arRecord["QUANTITY"])
									);

									if ($res)
									{
										$arRes["STORES"][$arRecord["STORE_ID"]] = $arRecord["QUANTITY"];
										$totalAddedAmount += $arRecord["QUANTITY"];

										$barcodeMulti = false;
										if ($isOrderConverted != 'N')
										{
											$barcodeMulti = $basketItem->isBarcodeMulti();
										}

										//adding barcodes
										if (isset($arRecord["BARCODE"]))
										{
											if (!empty($arRecord["BARCODE"]) && is_array($arRecord["BARCODE"]))
											{

												foreach ($arRecord["BARCODE"] as $barcodeValue)
												{
													if (strval(trim($barcodeValue)) == '' || (strval(trim($barcodeValue)) != '' && !$barcodeMulti))
														continue;

													$arFields = array(
														"STORE_ID" => $arRecord["STORE_ID"],
														"BARCODE" => $barcodeValue,
														"PRODUCT_ID" => $arParams["PRODUCT_ID"]
													);

													CCatalogStoreBarcode::Add($arFields);
												}
											}
											elseif (!is_array($arRecord["BARCODE"]))
											{
												$arFields = array(
													"STORE_ID" => $arRecord["STORE_ID"],
													"BARCODE" => $arRecord["BARCODE"],
													"PRODUCT_ID" => $arParams["PRODUCT_ID"]
												);

												CCatalogStoreBarcode::Add($arFields);
											}
										}
									}
									else
									{
										$APPLICATION->ThrowException(Loc::getMessage("DDCT_DEDUCTION_SAVE_ERROR", self::GetProductCatalogInfo($arParams["PRODUCT_ID"])), "DDCT_DEDUCTION_SAVE_ERROR");
										$arRes["RESULT"] = false;
										return $arRes;
									}
								}
							}

							// $dbAmount = $DB->Query("SELECT SUM(AMOUNT) as AMOUNT FROM b_catalog_store_product WHERE PRODUCT_ID = ".$arParams["PRODUCT_ID"]." ", true);
							// if ($totalAddedAmount = $dbAmount->Fetch())
							// {
							// }
							if ($arParams["PRODUCT_RESERVED"] == "Y")
							{
								$arUpdateFields["QUANTITY_RESERVED"] = $arProduct["QUANTITY_RESERVED"] + $totalAddedAmount;
							}
							else
							{
								$arUpdateFields["QUANTITY"] = $arProduct["QUANTITY"] + $totalAddedAmount;
							}

							CCatalogProduct::Update($arParams["PRODUCT_ID"], $arUpdateFields);
							if (isset($arUpdateFields['QUANTITY']) && self::isNeedClearPublicCache(
									$arProduct['QUANTITY'],
									$arUpdateFields['QUANTITY'],
									$arProduct['QUANTITY_TRACE'],
									$arProduct['CAN_BUY_ZERO']
								))
							{
								$productInfo = array(
									'CAN_BUY_ZERO' => $arProduct['CAN_BUY_ZERO'],
									'QUANTITY_TRACE' => $arProduct['QUANTITY_TRACE'],
									'OLD_QUANTITY' => $arProduct['QUANTITY'],
									'QUANTITY' => $arUpdateFields['QUANTITY'],
									'DELTA' => $arUpdateFields['QUANTITY'] - $arProduct['QUANTITY']
								);
								self::clearPublicCache($arProduct['ID'], $productInfo);
							}

							$arRes["RESULT"] = true;
						}
						else
						{
							$APPLICATION->ThrowException(Loc::getMessage("DDCT_DEDUCTION_STORE_ERROR", self::GetProductCatalogInfo($arParams["PRODUCT_ID"])), "DEDUCTION_STORE_ERROR2");
							$arRes["RESULT"] = false;
							return $arRes;
						}
					}
					else //store control not used
					{
						if ($arParams["PRODUCT_RESERVED"] == "Y" && !$disableReservation)
						{
							$arFields["QUANTITY_RESERVED"] = $arProduct["QUANTITY_RESERVED"] + $arParams["QUANTITY"];
							// $arFields["QUANTITY"] = $arProduct["QUANTITY"] - $arParams["QUANTITY_RESERVED"];
						}
						else
						{
							$arFields["QUANTITY"] = $arProduct["QUANTITY"] + $arParams["QUANTITY"];
							// $arFields["QUANTITY_RESERVED"] = $arProduct["QUANTITY_RESERVED"] - $arParams["QUANTITY_RESERVED"];
						}

						$arRes["RESULT"] = CCatalogProduct::Update($arParams["PRODUCT_ID"], $arFields);
						if (isset($arFields['QUANTITY']) && self::isNeedClearPublicCache(
								$arProduct['QUANTITY'],
								$arFields['QUANTITY'],
								$arProduct['QUANTITY_TRACE'],
								$arProduct['CAN_BUY_ZERO']
							))
						{
							$productInfo = array(
								'CAN_BUY_ZERO' => $arProduct['CAN_BUY_ZERO'],
								'QUANTITY_TRACE' => $arProduct['QUANTITY_TRACE'],
								'OLD_QUANTITY' => $arProduct['QUANTITY'],
								'QUANTITY' => $arFields['QUANTITY'],
								'DELTA' => $arFields['QUANTITY'] - $arProduct['QUANTITY']
							);
							self::clearPublicCache($arProduct['ID'], $productInfo);
						}
					}
				} //emulate or quantity trace
			}
		}
		else
		{
			$arRes["RESULT"] = false;
		}

		if (!$arRes["RESULT"])
		{
			$APPLICATION->ThrowException(Loc::getMessage("DDCT_UNKNOWN_ERROR", self::GetProductCatalogInfo($arParams["PRODUCT_ID"])), "UNKNOWN_DEDUCTION_ERROR");
		}

		if ($arRes['RESULT'] === true)
		{
			static::clearHitCache(self::CACHE_PRODUCT);
		}

		return $arRes;
	}

	/**
	 * @param \Bitrix\Sale\BasketItem $basketItem
	 * @param string $reserved
	 * @param array $basketStoreData
	 * @param null $quantity
	 * @return \Bitrix\Sale\Result
	 */
	public static function tryShipmentProduct(\Bitrix\Sale\BasketItem $basketItem, $reserved = 'N', array $basketStoreData = array(), $quantity = null)
	{
		$result = new \Bitrix\Sale\Result();

		$storesList = array();

		$useStoreControl = Catalog\Config\State::isUsedInventoryManagement();

		$productId = $basketItem->getProductId();

		$arProduct = Catalog\ProductTable::getList(array(
			'select' => array('ID', 'TYPE', 'QUANTITY', 'QUANTITY_RESERVED', 'QUANTITY_TRACE', 'CAN_BUY_ZERO'),
			'filter' => array('=ID' => $productId)
		))->fetch();
		if (empty($arProduct))
		{
			$result->addError( new \Bitrix\Sale\ResultError(Loc::getMessage("DDCT_DEDUCTION_PRODUCT_NOT_FOUND_ERROR", self::GetProductCatalogInfo($productId)), "DDCT_DEDUCTION_PRODUCT_NOT_FOUND_ERROR") );
			return $result;
		}

		if (
			($arProduct['TYPE'] == Catalog\ProductTable::TYPE_SKU || $arProduct['TYPE'] == Catalog\ProductTable::TYPE_EMPTY_SKU)
			&& (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') != 'Y'
		)
			return $result;

		if ($useStoreControl)
		{
			if (empty($basketStoreData))
			{
				if (static::canProductAutoShip($basketItem))
				{
					$basketStoreData = static::getProductStoreData($basketItem, $quantity);
				}
			}

			if (!empty($basketStoreData))
			{
				$totalAmount = 0;
				foreach ($basketStoreData as $storeId => $basketStore)
				{

					if (intval($storeId) < -1 || intval($storeId) == 0
						|| !isset($basketStore["QUANTITY"]) || intval($basketStore["QUANTITY"]) < 0)
					{
						$result->addError( new \Bitrix\Sale\ResultError(Loc::getMessage("DDCT_DEDUCTION_STORE_ERROR", self::GetProductCatalogInfo($productId)), "DDCT_DEDUCTION_STORE_ERROR") );

						return $result;
					}

					if (intval($storeId) == -1)
					{
						$totalAmount = intval($basketStore["QUANTITY"]);
					}
					else
					{

						$rsProps = CCatalogStoreProduct::GetList(
							array(),
							array(
								"PRODUCT_ID" => $productId,
								"STORE_ID" => $storeId
							),
							false,
							false,
							array('ID', 'AMOUNT')
						);
						if ($arProp = $rsProps->Fetch())
						{
							if ($arProp["AMOUNT"] < $basketStore["QUANTITY"])
							{
								$result->addError( new \Bitrix\Sale\ResultError(
									Loc::getMessage(
										"DDCT_DEDUCTION_QUANTITY_STORE_ERROR",
										array_merge(self::GetProductCatalogInfo($productId), array("#STORE_ID#" => $storeId))
									),
									"DDCT_DEDUCTION_QUANTITY_STORE_ERROR"
								));
								return $result;
							}
							else
							{

								$storesList[$storeId] = $basketStore["QUANTITY"];
								$totalAmount += $basketStore["QUANTITY"];

								//check barcodes
								if (isset($basketStore["BARCODE"]) && is_array($basketStore["BARCODE"]) && count($basketStore["BARCODE"]) > 0)
								{
									foreach ($basketStore["BARCODE"] as $barcodeId => $barcodeValue)
									{
										if (strval(trim($barcodeValue)) == "")
										{
											if ($basketItem->isBarcodeMulti())
											{
												$result->addError( new \Bitrix\Sale\ResultError(
													Loc::getMessage(
														"DDCT_DEDUCTION_MULTI_BARCODE_EMPTY",
														array_merge(self::GetProductCatalogInfo($productId), array("#STORE_ID#" => $basketStore['STORE_ID']))
													),
													"DDCT_DEDUCTION_MULTI_BARCODE_EMPTY"
												));
											}
											continue;
										}

										$arFields = array(
											"STORE_ID" => static::CATALOG_PROVIDER_EMPTY_STORE_ID,
											"BARCODE" => $barcodeValue,
											"PRODUCT_ID" => $productId
										);

										if ($basketItem->isBarcodeMulti())
										{
											$arFields['STORE_ID'] = $storeId;
										}

										$dbres = CCatalogStoreBarcode::GetList(
											array(),
											$arFields,
											false,
											false,
											array("ID", "STORE_ID", "BARCODE", "PRODUCT_ID")
										);

										if (!$arRes = $dbres->Fetch())
										{
											$result->addError( new \Bitrix\Sale\ResultError(
												Loc::getMessage(
													"DDCT_DEDUCTION_BARCODE_ERROR",
													array_merge(self::GetProductCatalogInfo($productId), array("#BARCODE#" => $barcodeValue))
												),
												"DDCT_DEDUCTION_BARCODE_ERROR"
											) );
										}
									}
								}
								elseif($basketItem->isBarcodeMulti())
								{
									$result->addError( new \Bitrix\Sale\ResultError(
										Loc::getMessage(
											"DDCT_DEDUCTION_MULTI_BARCODE_EMPTY",
											array_merge(self::GetProductCatalogInfo($productId), array("#STORE_ID#" => $basketStore['STORE_ID']))
										),
										"DDCT_DEDUCTION_MULTI_BARCODE_EMPTY"
									));
								}
							}
						}
					}

					if (!$result->isSuccess(true))
					{
						return $result;
					}

					if ($reserved == 'Y')
					{
						$reservedPoolQuantity = static::getProductPoolQuantityByBasketItem($basketItem);
						$reservedQuantity = $arProduct["QUANTITY_RESERVED"] + floatval($reservedPoolQuantity);
					}


					$productQuantity = ($reserved == 'Y' ? $reservedQuantity : $arProduct["QUANTITY"]);

					/*if (($totalAmount > $productQuantity)
						|| ($totalAmount > $reservedQuantity + $arProduct["QUANTITY"]))*/
					if ($totalAmount > $arProduct["QUANTITY_RESERVED"] + $arProduct["QUANTITY"])
					{
						$result->addError( new \Bitrix\Sale\ResultError(
							Loc::getMessage("SALE_PROVIDER_SHIPMENT_QUANTITY_NOT_ENOUGH", self::GetProductCatalogInfo($productId)),
							"SALE_PROVIDER_SHIPMENT_QUANTITY_NOT_ENOUGH"
						));
						return $result;
					}

				}
			}
			else
			{
				$result->addError( new \Bitrix\Sale\ResultError(
					Loc::getMessage("DDCT_DEDUCTION_STORE_ERROR", self::GetProductCatalogInfo($productId)),
					"DEDUCTION_STORE_ERROR1"
				) );
				return $result;
			}
		}
		else // store control not used
		{

			$reservedPoolQuantity = static::getProductPoolQuantityByBasketItem($basketItem);
			$reservedQuantity = $arProduct["QUANTITY_RESERVED"] + floatval($reservedPoolQuantity);

			if ($arProduct["CAN_BUY_ZERO"] != "Y" && $arProduct["QUANTITY_TRACE"] == "Y")
			{
				if ($quantity > $reservedQuantity + $arProduct["QUANTITY"])
				{
					$result->addError( new \Bitrix\Sale\ResultError(
						Loc::getMessage("DDCT_DEDUCTION_QUANTITY_ERROR", self::GetProductCatalogInfo($productId)),
						"DDCT_DEDUCTION_QUANTITY_ERROR"
					) );
					return $result;
				}
			}

//				$arRes["RESULT"] = true;

		} //store control

		return $result;
	}

	public static function tryUnshipmentProduct($productId)
	{
		$result = new \Bitrix\Sale\Result();
		$fields = array();

		$rsProducts = CCatalogProduct::GetList(
			array(),
			array('ID' => $productId),
			false,
			false,
			array('ID', 'QUANTITY', 'QUANTITY_RESERVED', 'QUANTITY_TRACE', 'CAN_BUY_ZERO', 'TYPE')
		);

		if ($arProduct = $rsProducts->Fetch())
		{
			if (
				($arProduct['TYPE'] != Catalog\ProductTable::TYPE_SKU && $arProduct['TYPE'] != Catalog\ProductTable::TYPE_EMPTY_SKU)
				|| (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') == 'Y'
			)
			{
				$fields["QUANTITY_TRACE"] = ($arProduct["QUANTITY_TRACE"] == "Y");
			}
		}

		if (!empty($fields))
		{
			$result->addData($fields);
		}


		return $result;
	}

	public static function GetStoresCount($arParams = array())
	{
		//without store control stores are used for information purposes only
		if (!Catalog\Config\State::isUsedInventoryManagement())
			return -1;

		return count(static::getStoreIds($arParams));
	}

	public static function GetProductStores($arParams)
	{
		//without store control stores are used for information purposes only
		if (!Catalog\Config\State::isUsedInventoryManagement())
			return false;

		$arParams['PRODUCT_ID'] = (isset($arParams['PRODUCT_ID']) ? (int)$arParams['PRODUCT_ID'] : 0);
		if ($arParams['PRODUCT_ID'] <= 0)
			return false;

		$arParams['ENUM_BY_ID'] = (isset($arParams['ENUM_BY_ID']) && $arParams['ENUM_BY_ID'] == true);

		$storeIds = static::getStoreIds($arParams);
		if (empty($storeIds))
			return false;

		$cacheId = md5(serialize($arParams));
		if (!($result = static::getHitCache(self::CACHE_STORE_PRODUCT, $cacheId)))
		{
			$result = array();
			$iterator = Catalog\StoreProductTable::getList(array(
				'select' => array('PRODUCT_ID', 'AMOUNT', 'STORE_ID', 'STORE_NAME' => 'STORE.TITLE'),
				'filter' => array('=PRODUCT_ID' => $arParams['PRODUCT_ID'], '@STORE_ID' => $storeIds),
				'order' => array('STORE_ID' => 'ASC')
			));
			while ($row = $iterator->fetch())
				$result[$row['STORE_ID']] = $row;
			unset($row, $iterator);
			if (!empty($result))
			{
				if (!$arParams['ENUM_BY_ID'])
					$result = array_values($result);
				static::setHitCache(self::CACHE_STORE_PRODUCT, $cacheId, $result);
			}
		}

		return (!empty($result) ? $result : false);
	}

	public static function CheckProductBarcode($arParams)
	{
		$result = false;

		$arFilter = array(
			"PRODUCT_ID" => $arParams["PRODUCT_ID"],
			"BARCODE"	 => $arParams["BARCODE"]
		);

		if (isset($arParams["STORE_ID"]))
			$arFilter["STORE_ID"] = intval($arParams["STORE_ID"]);

		$dbres = CCatalogStoreBarcode::GetList(
			array(),
			$arFilter
		);
		if ($res = $dbres->GetNext())
			$result = true;

		return $result;
	}

	private static function GetProductCatalogInfo($productID)
	{
		$productID = (int)$productID;
		if ($productID <= 0)
			return array();


		if (!$arProduct = static::getHitCache('IBLOCK_ELEMENT', $productID))
		{
			$dbProduct = CIBlockElement::GetList(array(), array("ID" => $productID), false, false, array('ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID'));
			if ($arProduct = $dbProduct->Fetch())
			{
				static::setHitCache('IBLOCK_ELEMENT', $productID, $arProduct);
			}
		}

		return array(
			"#PRODUCT_ID#" => $arProduct["ID"],
			"#PRODUCT_NAME#" => $arProduct["NAME"],
		);
	}

	public static function GetSetItems($productID, $intType, $arProducInfo = array())
	{
		static $proxyCatalogProductSet = array();
		static $proxyCatalogSkuData = array();

		$arProductId = array();
		$proxyCatalogProductSetKey = $productID."|".$intType;

		if (!isset($proxyCatalogProductSet[$proxyCatalogProductSetKey]))
			$proxyCatalogProductSet[$proxyCatalogProductSetKey] = CCatalogProductSet::getAllSetsByProduct($productID, $intType);
		$arSets = $proxyCatalogProductSet[$proxyCatalogProductSetKey];

		if (is_array($arSets))
		{
			foreach ($arSets as $k => $arSet)
			{
				foreach ($arSet["ITEMS"] as $k1 => $item)
				{
					$arItem = self::GetProductData(array("PRODUCT_ID" => $item["ITEM_ID"], "QUANTITY" => $item["QUANTITY"], "CHECK_QUANTITY" => "N", "CHECK_PRICE" => "N"));
					if (array_key_exists('QUANTITY_TRACE', $arItem))
						unset($arItem['QUANTITY_TRACE']);

					$arItem["PRODUCT_ID"] = $item["ITEM_ID"];
					$arItem["MODULE"] = "catalog";
					$arItem["PRODUCT_PROVIDER_CLASS"] = "CCatalogProductProvider";
					if ($intType == CCatalogProductSet::TYPE_SET)
					{
						$arItem['SET_DISCOUNT_PERCENT'] = ($item['DISCOUNT_PERCENT'] == '' ? false : (float)$item['DISCOUNT_PERCENT']);
					}

					$arProductId[] = $item["ITEM_ID"];

					$arItem["PROPS"] = array();

					if (!empty($proxyCatalogSkuData[$item["ITEM_ID"]]) && is_array($proxyCatalogSkuData[$item["ITEM_ID"]]))
					{
						$arParentSku = $proxyCatalogSkuData[$item["ITEM_ID"]];
					}
					else
					{
						if ($arParentSku = CCatalogSku::GetProductInfo($item["ITEM_ID"]))
						{
							$proxyCatalogSkuData[$item["ITEM_ID"]] = $arParentSku;
						}

					}

					if (!empty($arParentSku))
					{
						$arPropsSku = array();

						if (!$arProduct = static::getHitCache('IBLOCK_ELEMENT', $item["ITEM_ID"]))
						{
							$dbProduct = CIBlockElement::GetList(array(), array("ID" => $item["ITEM_ID"]), false, false, array('ID', 'IBLOCK_ID', 'NAME', 'IBLOCK_SECTION_ID'));
							if ($arProduct = $dbProduct->Fetch())
							{
								static::setHitCache('IBLOCK_ELEMENT', $item["ITEM_ID"], $arProduct);
							}
						}

						if (!$arPropsSku = static::getHitCache('IBLOCK_PROPERTY', $arParentSku['OFFER_IBLOCK_ID']))
						{
							$iterator = Iblock\PropertyTable::getList(array(
								'select' => array('ID', 'IBLOCK_ID', 'CODE'),
								'filter' => array(
									'=IBLOCK_ID' => $arParentSku['OFFER_IBLOCK_ID'],
									'=ACTIVE' => 'Y',
									'=MULTIPLE' => 'N',
									'!=ID' => $arParentSku['SKU_PROPERTY_ID']
								),
								'order' => array('SORT' => 'ASC')
							));
							while ($row = $iterator->fetch())
								$arPropsSku[] = $row['CODE'];
							unset($row, $iterator);

							static::setHitCache('IBLOCK_PROPERTY', $arParentSku['OFFER_IBLOCK_ID'], $arPropsSku);
						}

						$proxyProductPropertyKey = $item["ITEM_ID"]."_".$arParentSku["IBLOCK_ID"]."_".md5(join('|', $arPropsSku));

						if (!$product_properties = static::getHitCache('PRODUCT_PROPERTY', $proxyProductPropertyKey))
						{
							$product_properties = CIBlockPriceTools::GetOfferProperties(
								$item["ITEM_ID"],
								$arParentSku["IBLOCK_ID"],
								$arPropsSku
							);

							static::setHitCache('PRODUCT_PROPERTY', $proxyProductPropertyKey, $product_properties);
						}

						foreach ($product_properties as $propData)
						{
							$arItem["PROPS"][] = array(
								"NAME" => $propData["NAME"],
								"CODE" => $propData["CODE"],
								"VALUE" => $propData["VALUE"],
								"SORT" => $propData["SORT"]
							);
						}
					}

					$arSets[$k]["ITEMS"][$k1] = array_merge($item, $arItem);
				}
			}

			if (!$productList = static::getHitCache('IBLOCK_ELEMENT_LIST', $productID))
			{
				$rsProducts = CIBlockElement::GetList(
					array(),
					array('ID' => $arProductId),
					false,
					false,
					array("ID", "IBLOCK_ID", "IBLOCK_SECTION_ID", "PREVIEW_PICTURE", "DETAIL_PICTURE", "IBLOCK_TYPE_ID", "XML_ID")
				);
				while ($arProduct = $rsProducts->GetNext())
				{
					$productList[] = $arProduct;
				}

				if (!empty($productList) && is_array($productList))
				{
					static::setHitCache('IBLOCK_ELEMENT_LIST', $productID, $productList);
				}
			}

			if (!empty($productList) && is_array($productList))
			{
				foreach ($productList as $arProduct)
				{
					foreach ($arSets as $k => $arSet)
					{
						foreach ($arSet["ITEMS"] as $k1 => $item)
						{
							if ($item["ITEM_ID"] == $arProduct["ID"])
							{
								$arProps = array();
								$strIBlockXmlID = strval(CIBlock::GetArrayByID($arProduct['IBLOCK_ID'], 'XML_ID'));
								if ($strIBlockXmlID != "")
								{
									$arProps[] = array(
										"NAME" => "Catalog XML_ID",
										"CODE" => "CATALOG.XML_ID",
										"VALUE" => $strIBlockXmlID
									);
								}

								if (!empty($proxyCatalogSkuData[$item["ITEM_ID"]]) && mb_strpos($arProduct["XML_ID"], '#') === false)
								{
									$arParentSku = $proxyCatalogSkuData[$item["ITEM_ID"]];
									if (!empty($proxyParentData[$arParentSku['ID']]) && is_array($proxyParentData[$arParentSku['ID']]))
									{
										$parentData = $proxyParentData[$arParentSku['ID']];
									}
									else
									{
										$parentIterator = \Bitrix\Iblock\ElementTable::getList(array(
											'select' => array('ID', 'XML_ID'),
											'filter' => array('ID' => $arParentSku['ID'])
										));
										if ($parentData = $parentIterator->fetch())
											$proxyParentData[$arParentSku['ID']] = $parentData;
										unset($parentIterator);
									}

									$arProduct["XML_ID"] = $parentData['XML_ID'].'#'.$arProduct["XML_ID"];
									unset($parentData);
								}

								$arProps[] = array(
									"NAME" => "Product XML_ID",
									"CODE" => "PRODUCT.XML_ID",
									"VALUE" => $arProduct["XML_ID"]
								);

								$arSets["$k"]["ITEMS"][$k1]["IBLOCK_ID"] = $arProduct["IBLOCK_ID"];
								$arSets["$k"]["ITEMS"][$k1]["IBLOCK_SECTION_ID"] = $arProduct["IBLOCK_SECTION_ID"];
								$arSets["$k"]["ITEMS"][$k1]["PREVIEW_PICTURE"] = $arProduct["PREVIEW_PICTURE"];
								$arSets["$k"]["ITEMS"][$k1]["DETAIL_PICTURE"] = $arProduct["DETAIL_PICTURE"];
								$arSets["$k"]["ITEMS"][$k1]["PRODUCT_XML_ID"] = $arProduct["XML_ID"];
								$arSets["$k"]["ITEMS"][$k1]["PROPS"] = array_merge($arSets["$k"]["ITEMS"][$k1]["PROPS"], $arProps);
							}
						}
					}
				}
			}
		}

		foreach(GetModuleEvents("sale", "OnGetSetItems", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arSets));

		return $arSets;
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
		$iblockID = (int)(isset($productInfo['IBLOCK_ID']) ? $productInfo['IBLOCK_ID'] : CIBlockElement::GetIBlockByID($productID));
		if ($iblockID <= 0)
			return;
		if (!isset(self::$clearAutoCache[$iblockID]))
		{
			CIBlock::clearIblockTagCache($iblockID);
			self::$clearAutoCache[$iblockID] = true;
		}

		$productInfo['ID'] = $productID;
		$productInfo['ELEMENT_IBLOCK_ID'] = $iblockID;
		$productInfo['IBLOCK_ID'] = $iblockID;
		if (isset($productInfo['CAN_BUY_ZERO']))
			$productInfo['NEGATIVE_AMOUNT_TRACE'] = $productInfo['CAN_BUY_ZERO'];
		foreach (GetModuleEvents('catalog', 'OnProductQuantityTrace', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($productID, $productInfo));
	}

	/**
	 * @param $productId
	 * @param null $userId
	 * @return bool
	 * @throws Main\ArgumentException
	 */
	public static function getProductAvailableQuantity($productId, $userId = null)
	{
		$adminSection = (defined('ADMIN_SECTION') && ADMIN_SECTION === true);

		$userId = (isset($userId) ? (int)$userId : 0);

		if ($userId < 0)
			$userId = 0;

		static $arUserCache = array();
		if ($adminSection)
		{
			if ($userId == 0)
				return false;

			if (!isset($arUserCache[$userId]))
			{
				$userIterator = Main\UserTable::getList(array(
					'select' => array('ID'),
					'filter' => array('=ID' => $userId)
				));
				if ($userDat = $userIterator->fetch())
				{
					$userDat['ID'] = (int)$userDat['ID'];
					$arUserCache[$userDat['ID']] = CUser::GetUserGroup($userDat['ID']);
				}
				else
				{
					return false;
				}
			}

			$dbIBlockElement = CIBlockElement::GetList(
				array(),
				array(
					'ID' => $productId,
					'ACTIVE' => 'Y',
					'ACTIVE_DATE' => 'Y',
					'CHECK_PERMISSION' => 'N'
				),
				false,
				false,
				array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL')
			);
			if (!($arProduct = $dbIBlockElement->GetNext()))
				return false;


			$iblockRights = null;

			if (!$iblockRights = static::getHitCache(self::CACHE_IBLOCK_RIGHTS_MODE, $arProduct['IBLOCK_ID']))
			{
				if ($iblockRights = CIBlock::GetArrayByID($arProduct['IBLOCK_ID'], 'RIGHTS_MODE'))
				{
					static::setHitCache(self::CACHE_IBLOCK_RIGHTS_MODE, $arProduct['IBLOCK_ID'], $iblockRights);
				}
			}

			if ($iblockRights == 'E')
			{
				$proxyUserPermissionKey = $productId."|".$userId;

				if (!$arUserRights = static::getHitCache(self::CACHE_USER_RIGHTS, $proxyUserPermissionKey))
				{
					if ($arUserRights = CIBlockElementRights::GetUserOperations($productId, $userId))
					{
						static::setHitCache(self::CACHE_USER_RIGHTS, $proxyUserPermissionKey, $arUserRights);
					}
				}

				if (empty($arUserRights) || !isset($arUserRights['element_read']))
					return false;

				unset($arUserRights);
			}
			else
			{
				if (CIBlock::GetPermission($arProduct['IBLOCK_ID'], $userId) < 'R')
					return false;
			}
		}
		else
		{
			$dbIBlockElement = CIBlockElement::GetList(
				array(),
				array(
					'ID' => $productId,
					'ACTIVE' => 'Y',
					'ACTIVE_DATE' => 'Y',
					'CHECK_PERMISSIONS' => 'Y',
					'MIN_PERMISSION' => 'R'
				),
				false,
				false,
				array('ID', 'IBLOCK_ID', 'NAME', 'DETAIL_PAGE_URL')
			);
			if (!($arProduct = $dbIBlockElement->GetNext()))
				return false;
		}

		$rsProducts = CCatalogProduct::GetList(
			array(),
			array('ID' => $productId),
			false,
			false,
			array(
				'ID',
				'QUANTITY',
				'TYPE'
			)
		);

		if ($arCatalogProduct = $rsProducts->Fetch())
		{
			if (
				($arCatalogProduct['TYPE'] != Catalog\ProductTable::TYPE_SKU && $arCatalogProduct['TYPE'] != Catalog\ProductTable::TYPE_EMPTY_SKU)
				|| (string)Main\Config\Option::get('catalog', 'show_catalog_tab_with_offers') == 'Y'
			)
			{
				return $arCatalogProduct['QUANTITY'];
			}
		}

		return false;
	}

	protected static function getUserGroups($userId)
	{
		$userId = (int)$userId;
		if ($userId < 0)
			return false;

		if (!isset(self::$userCache[$userId]))
			self::$userCache[$userId] = Main\UserTable::getUserGroupIds($userId);

		return self::$userCache[$userId];
	}

	protected static function getProductPoolQuantityByBasketItem(\Bitrix\Sale\BasketItem $basketItem)
	{
		/** @var \Bitrix\Sale\Basket $basket */
		if (!$basket = $basketItem->getCollection())
			return false;

		/** @var \Bitrix\Sale\Order $order */
		if (!$order = $basket->getOrder())
			return false;

		return \Bitrix\Sale\Provider::getReservationPoolItem($order->getInternalId(), $basketItem);
	}

	protected static function getPriceTitle($priceType)
	{
		$priceType = (int)$priceType;
		if ($priceType <= 0)
			return '';
		if (!isset(self::$priceTitleCache[$priceType]))
		{
			self::$priceTitleCache[$priceType] = '';
			$group = Catalog\GroupTable::getList(array(
				'select' => array('ID', 'NAME', 'NAME_LANG' => 'CURRENT_LANG.NAME'),
				'filter' => array('=ID' => $priceType)
			))->fetch();
			if (!empty($group))
			{
				$group['NAME_LANG'] = (string)$group['NAME_LANG'];
				self::$priceTitleCache[$priceType] = ($group['NAME_LANG'] != '' ? $group['NAME_LANG'] : $group['NAME']);
			}
			unset($group);
		}
		return self::$priceTitleCache[$priceType];
	}

	/**
	 * Check exist and activity parent product.
	 *
	 * @param int $productId			Product Id.
	 * @param int $iblockId				Iblock Id.
	 * @return bool
	 */
	protected static function checkParentActivity($productId, $iblockId = 0)
	{
		$cacheKey = $productId.'|'.$iblockId;
		if (!static::isExistsHitCache(self::CACHE_PARENT_PRODUCT_ACTIVE, $cacheKey))
		{
			$result = 'Y';
			$parent = CCatalogSku::GetProductInfo($productId, $iblockId);
			if (!empty($parent))
			{
				$itemList = CIBlockElement::GetList(
					array(),
					array(
						'ID' => $parent['ID'],
						'IBLOCK_ID' => $parent['IBLOCK_ID'],
						'ACTIVE' => 'Y',
						'ACTIVE_DATE' => 'Y',
						'CHECK_PERMISSIONS' => 'N'
					),
					false,
					false,
					array('ID')
				);
				$item = $itemList->Fetch();
				unset($itemList);
				if (empty($item))
					$result = 'N';
			}
			static::setHitCache(self::CACHE_PARENT_PRODUCT_ACTIVE, $cacheKey, $result);
			unset($result);
		}
		return (static::getHitCache(self::CACHE_PARENT_PRODUCT_ACTIVE, $cacheKey) != 'N');
	}

	/**
	 * @internal
	 * @param string $type
	 * @param string $key
	 * @return false|mixed
	 */
	public static function getHitCache($type, $key)
	{
		if (!empty(self::$hitCache[$type]) && !empty(self::$hitCache[$type][$key]))
			return self::$hitCache[$type][$key];

		return false;
	}

	/**
	 * @internal
	 * @param string $type
	 * @param string $key
	 * @return bool
	 */
	public static function isExistsHitCache($type, $key)
	{
		return (!empty(self::$hitCache[$type]) && !empty(self::$hitCache[$type][$key]));
	}

	/**
	 * @internal
	 * @param string $type
	 * @param string $key
	 * @param mixed $value
	 * @return void
	 */
	public static function setHitCache($type, $key, $value)
	{
		if (empty(self::$hitCache[$type]))
			self::$hitCache[$type] = array();

		if (empty(self::$hitCache[$type][$key]))
			self::$hitCache[$type][$key] = array();

		self::$hitCache[$type][$key] = $value;
	}

	/**
	 * @internal
	 * @param string|null $type
	 * @return void
	 */
	public static function clearHitCache($type = null)
	{
		if ($type === null)
			self::$hitCache = array();
		elseif (!empty(self::$hitCache[$type]))
			unset(self::$hitCache[$type]);
	}

	/**
	 * @param \Bitrix\Sale\BasketItem $basketItem
	 *
	 * @return bool
	 * @throws Main\ArgumentNullException
	 */
	protected static function canProductAutoShip(\Bitrix\Sale\BasketItem $basketItem)
	{
		$countStores = static::GetStoresCount(array('SITE_ID' => $basketItem->getField('LID')));
		$defaultDeductionStore = Main\Config\Option::get("sale", "deduct_store_id", "", $basketItem->getField('LID'));

		$canAutoDeduct = (($countStores == 1 || $countStores == -1 || $defaultDeductionStore > 0) && !$basketItem->isBarcodeMulti());

		$countProductStores = 0;

		if ($canAutoDeduct === true)
			return true;

		if ($productStore = static::GetProductStores(array(
			'PRODUCT_ID' => $basketItem->getProductId(),
			'SITE_ID' => $basketItem->getField('LID')
		)))
		{
			foreach ($productStore as $productStoreItem)
			{
				if ($productStoreItem['AMOUNT'] > 0)
				{
					$countProductStores++;
				}
			}
		}

		return ($countProductStores == 1);
	}

	/**
	 * @param \Bitrix\Sale\BasketItem $basketItem
	 * @param $quantity
	 *
	 * @return array|bool
	 */
	protected static function getProductStoreData(\Bitrix\Sale\BasketItem $basketItem, $quantity)
	{
		$productStoreData = array();

		if ($productStore = static::GetProductStores(array(
			'PRODUCT_ID' => $basketItem->getProductId(),
			'SITE_ID' => $basketItem->getField('LID')
		)))
		{
			foreach ($productStore as $productStoreItem)
			{
				if ($productStoreItem['AMOUNT'] > 0)
				{
					$productStoreData = array(
						$productStoreItem['STORE_ID'] => array(
							'STORE_ID' => $productStoreItem['STORE_ID'],
							'QUANTITY' => $quantity
						)
					);
					break;
				}
			}
		}

		return (!empty($productStoreData) ? $productStoreData : false);
	}

	/**
	 * @param \Bitrix\Sale\BasketItem $basketItem
	 * @param $quantity
	 *
	 * @return array|bool
	 */
	protected static function getProductOneStoreData(\Bitrix\Sale\BasketItem $basketItem, $quantity)
	{
		$productStoreData = array();

		if ($productStore = static::GetProductStores(array(
			'PRODUCT_ID' => $basketItem->getProductId(),
			'SITE_ID' => $basketItem->getField('LID')
		)))
		{
			if (count($productStore) != 1)
			{
				return false;
			}

			foreach ($productStore as $productStoreItem)
			{
				$productStoreData = array(
					$productStoreItem['STORE_ID'] => array(
						'STORE_ID' => $productStoreItem['STORE_ID'],
						'QUANTITY' => $quantity,
					)
				);
			}
		}

		return (!empty($productStoreData) ? $productStoreData : false);
	}

	protected static function getStoreIds(array $params)
	{
		$filterId = array('ACTIVE' => 'Y', 'SHIPPING_CENTER' => 'Y');
		if (isset($params['SITE_ID']) && $params['SITE_ID'] != '')
			$filterId['+SITE_ID'] = $params['SITE_ID'];

		$cacheId = md5(serialize($filterId));
		$storeIds = static::getHitCache(self::CACHE_STORE, $cacheId);
		if (empty($storeIds))
		{
			$storeIds = array();

			$filter = Main\Entity\Query::filter();
			$filter->where('ACTIVE', '=', 'Y');
			$filter->where('SHIPPING_CENTER', '=', 'Y');
			if (isset($params['SITE_ID']) && $params['SITE_ID'] != '')
			{
				$subFilter = Main\Entity\Query::filter();
				$subFilter->logic('or')->where('SITE_ID', '=', $params['SITE_ID'])->where('SITE_ID', '=', '')->whereNull('SITE_ID');
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
	 * Check available set items.
	 * Error text return in static::$errors
	 *
	 * @param int $productId        Product id.
	 * @return bool
	 */
	protected static function checkProductSet($productId)
	{
		$allSets = CCatalogProductSet::getAllSetsByProduct($productId, CCatalogProductSet::TYPE_SET);
		if (empty($allSets))
		{
			static::$errors[] = Loc::getMessage('CATALOG_ERR_NO_PRODUCT_SET');
			return false;
		}
		reset($allSets);
		$set = current($allSets);
		unset($allSets);
		$itemIds = array();
		foreach ($set['ITEMS'] as $item)
		{
			if ($item['ITEM_ID'] != $item['OWNER_ID'])
				$itemIds[$item['ITEM_ID']] = $item['ITEM_ID'];
		}
		if (empty($itemIds))
		{
			static::$errors[] = Loc::getMessage('CATALOG_ERR_NO_PRODUCT_SET');
			return false;
		}
		$iterator = CIBlockElement::GetList(
			array(),
			array(
				'ID' => $itemIds,
				'ACTIVE' => 'Y',
				'ACTIVE_DATE' => 'Y',
				'CHECK_PERMISSIONS' => 'N' // permission check in GetSetItems
			),
			false,
			false,
			array('ID', 'IBLOCK_ID')
		);
		while ($row = $iterator->Fetch())
			unset($itemIds[$row['ID']]);
		unset($row, $iterator);

		if (!empty($itemIds))
		{
			static::$errors[] = Loc::getMessage('CATALOG_ERR_NO_PRODUCT_SET_ITEM');
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	public static function isReservationEnabled()
	{
		return !((string)Main\Config\Option::get("catalog", "enable_reservation") == "N"
			&& (string)Main\Config\Option::get("sale", "product_reserve_condition") != "S"
			&& !Catalog\Config\State::isUsedInventoryManagement()
		);
	}

	/**
	 * @return bool
	 */
	public static function isNeedShip()
	{
		return static::isReservationEnabled();
	}
}