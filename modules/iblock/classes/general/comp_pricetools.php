<?
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Highloadblock\HighloadBlockTable,
	Bitrix\Currency,
	Bitrix\Iblock,
	Bitrix\Catalog,
	Bitrix\Main,
	Bitrix\Sale;

Loc::loadMessages(__FILE__);

class CIBlockPriceTools
{
	protected static $catalogIncluded = null;
	protected static $highLoadInclude = null;
	protected static $saleIncluded = null;
	protected static $needDiscountCache = null;
	protected static $calculationDiscounts = 0;

	/**
	 * @param int $IBLOCK_ID
	 * @param array $arPriceCode
	 * @return array
	 * @throws Main\LoaderException
	 */
	public static function GetCatalogPrices($IBLOCK_ID, $arPriceCode)
	{
		global $USER;
		$arCatalogPrices = array();
		if (self::$catalogIncluded === null)
			self::$catalogIncluded = Loader::includeModule('catalog');
		if (self::$catalogIncluded)
		{
			$arCatalogGroupCodesFilter = array();
			foreach($arPriceCode as $value)
			{
				$t_value = trim($value);
				if ('' != $t_value)
					$arCatalogGroupCodesFilter[$value] = true;
			}
			$arCatalogGroupsFilter = array();
			$arCatalogGroups = CCatalogGroup::GetListArray();
			foreach ($arCatalogGroups as $key => $value)
			{
				if (isset($arCatalogGroupCodesFilter[$value['NAME']]))
				{
					$arCatalogGroupsFilter[] = $key;
					$arCatalogPrices[$value["NAME"]] = array(
						"ID" => (int)$value["ID"],
						"CODE" => $value["NAME"],
						"SORT" => (int)$value["SORT"],
						"BASE" => $value["BASE"],
						"XML_ID" => $value["XML_ID"],
						"TITLE" => htmlspecialcharsbx($value["NAME_LANG"]),
						"~TITLE" => $value["NAME_LANG"],
						"SELECT" => "CATALOG_GROUP_".$value["ID"],
						"SELECT_EXTENDED" => array("PRICE_".$value["ID"], "CURRENCY_".$value["ID"], "SCALED_PRICE_".$value["ID"])
					);
				}
			}
			$userGroups = array(2);
			if (isset($USER) && $USER instanceof CUser)
				$userGroups = $USER->GetUserGroupArray();
			$arPriceGroups = CCatalogGroup::GetGroupsPerms($userGroups, $arCatalogGroupsFilter);
			foreach($arCatalogPrices as $name=>$value)
			{
				$arCatalogPrices[$name]["CAN_VIEW"] = in_array($value["ID"], $arPriceGroups["view"]);
				$arCatalogPrices[$name]["CAN_BUY"] = in_array($value["ID"], $arPriceGroups["buy"]);
			}
		}
		else
		{
			$rsProperties = CIBlockProperty::GetList(array(), array(
				"IBLOCK_ID" => $IBLOCK_ID,
				"CHECK_PERMISSIONS" => "N",
				"PROPERTY_TYPE" => "N",
				"MULTIPLE" => "N"
			));
			while ($arProperty = $rsProperties->Fetch())
			{
				if (in_array($arProperty["CODE"], $arPriceCode))
				{
					$arCatalogPrices[$arProperty["CODE"]] = array(
						"ID" => (int)$arProperty["ID"],
						"CODE" => $arProperty["CODE"],
						"SORT" => (int)$arProperty["SORT"],
						"BASE" => "N",
						"XML_ID" => $arProperty["XML_ID"],
						"TITLE" => htmlspecialcharsbx($arProperty["NAME"]),
						"~TITLE" => $arProperty["NAME"],
						"SELECT" => "PROPERTY_".$arProperty["ID"],
						"SELECT_EXTENDED" => array("PROPERTY_".$arProperty["ID"]),
						"CAN_VIEW"=>true,
						"CAN_BUY"=>false,
					);
				}
			}
		}
		return $arCatalogPrices;
	}

	/**
	 * @param array $arPriceTypes
	 * @return array
	 */
	public static function GetAllowCatalogPrices($arPriceTypes)
	{
		$arResult = array();
		if (empty($arPriceTypes) || !is_array($arPriceTypes))
			return $arResult;

		foreach ($arPriceTypes as $arOnePriceType)
		{
			if ($arOnePriceType['CAN_VIEW'] || $arOnePriceType['CAN_BUY'])
				$arResult[] = (int)$arOnePriceType['ID'];
		}
		unset($arOnePriceType);
		if (!empty($arResult))
			Main\Type\Collection::normalizeArrayValuesByInt($arResult, true);
		return $arResult;
	}

	public static function SetCatalogDiscountCache($arCatalogGroups, $arUserGroups, $siteId = false)
	{
		$result = false;

		if (!is_array($arUserGroups))
			return $result;
		Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups, true);
		if (empty($arUserGroups))
			return $result;

		if ($siteId === false)
			$siteId = SITE_ID;

		if(\Bitrix\Main\Config\Option::get('sale', 'use_sale_discount_only') === 'Y')
		{
			self::$needDiscountCache = false;
			if (self::$saleIncluded === null)
				self::$saleIncluded = Loader::includeModule('sale');
			if (self::$saleIncluded)
			{
				$cache = Sale\Discount\RuntimeCache\DiscountCache::getInstance();
				$ids = $cache->getDiscountIds($arUserGroups);
				if (!empty($ids))
				{
					$discountList = $cache->getDiscounts(
						$ids,
						['all', 'catalog'],
						$siteId,
						[]
					);
					if (!empty($discountList))
					{
						$result = true;
					}
				}
			}
			return $result;
		}

		if (self::$catalogIncluded === null)
			self::$catalogIncluded = Loader::includeModule('catalog');
		if (self::$catalogIncluded)
		{
			if (!is_array($arCatalogGroups))
				return $result;
			Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups, true);
			if (empty($arUserGroups))
				return $result;

			$arRestFilter = array(
				'PRICE_TYPES' => $arCatalogGroups,
				'USER_GROUPS' => $arUserGroups,
			);
			$arRest = CCatalogDiscount::GetRestrictions($arRestFilter, false, false);
			$arDiscountFilter = array();
			$arDiscountResult = array();
			if (empty($arRest) || (array_key_exists('DISCOUNTS', $arRest) && empty($arRest['DISCOUNTS'])))
			{
				foreach ($arCatalogGroups as $intOneGroupID)
				{
					$strCacheKey = CCatalogDiscount::GetDiscountFilterCacheKey(array($intOneGroupID), $arUserGroups, false);
					$arDiscountFilter[$strCacheKey] = array();
				}
				unset($intOneGroupID);
			}
			else
			{
				$arResultDiscountList = array();

				$select = array(
					'ID', 'TYPE', 'SITE_ID', 'ACTIVE', 'ACTIVE_FROM', 'ACTIVE_TO',
					'RENEWAL', 'NAME', 'SORT', 'MAX_DISCOUNT', 'VALUE_TYPE', 'VALUE', 'CURRENCY',
					'PRIORITY', 'LAST_DISCOUNT',
					'USE_COUPONS', 'UNPACK', 'CONDITIONS'
				);
				$currentDatetime = new Main\Type\DateTime();
				$discountRows = array_chunk($arRest['DISCOUNTS'], 500);
				foreach ($discountRows as $pageIds)
				{
					$discountFilter = array(
						'@ID' => $pageIds,
						'=SITE_ID' => $siteId,
						'=TYPE' => Catalog\DiscountTable::TYPE_DISCOUNT,
						array(
							'LOGIC' => 'OR',
							'ACTIVE_FROM' => '',
							'<=ACTIVE_FROM' => $currentDatetime
						),
						array(
							'LOGIC' => 'OR',
							'ACTIVE_TO' => '',
							'>=ACTIVE_TO' => $currentDatetime
						),
						'=USE_COUPONS' => 'N',
						'=RENEWAL' => 'N',
					);
					CTimeZone::Disable();
					$iterator = Catalog\DiscountTable::getList(array(
						'select' => $select,
						'filter' => $discountFilter
					));
					while ($row = $iterator->fetch())
					{
						$row['ID'] = (int)$row['ID'];
						$row['HANDLERS'] = array();
						$row['MODULE_ID'] = 'catalog';
						$row['TYPE'] = (int)$row['TYPE'];
						if ($row['ACTIVE_FROM'] instanceof Main\Type\DateTime)
							$row['ACTIVE_FROM'] = $row['ACTIVE_FROM']->toString();
						if ($row['ACTIVE_TO'] instanceof Main\Type\DateTime)
							$row['ACTIVE_TO'] = $row['ACTIVE_TO']->toString();
						$row['COUPON_ACTIVE'] = '';
						$row['COUPON'] = '';
						$row['COUPON_ONE_TIME'] = null;
						$arResultDiscountList[$row['ID']] = $row;
					}
					unset($row, $iterator);
					CTimeZone::Enable();
				}
				unset($pageIds, $discountRows);

				foreach ($arCatalogGroups as $intOneGroupID)
				{
					$strCacheKey = CCatalogDiscount::GetDiscountFilterCacheKey(array($intOneGroupID), $arUserGroups, false);
					$arDiscountDetailList = array();
					$arDiscountList = array();
					foreach ($arRest['RESTRICTIONS'] as $intDiscountID => $arDiscountRest)
					{
						if (empty($arDiscountRest['PRICE_TYPE']) || array_key_exists($intOneGroupID, $arDiscountRest['PRICE_TYPE']))
						{
							$arDiscountList[] = $intDiscountID;
							if (isset($arResultDiscountList[$intDiscountID]))
								$arDiscountDetailList[] = $arResultDiscountList[$intDiscountID];
						}
					}
					sort($arDiscountList);
					$arDiscountFilter[$strCacheKey] = $arDiscountList;
					$strResultCacheKey = CCatalogDiscount::GetDiscountResultCacheKey($arDiscountList, $siteId, 'N');
					$arDiscountResult[$strResultCacheKey] = $arDiscountDetailList;
				}
				unset($intOneGroupID);
			}
			$boolFlag = CCatalogDiscount::SetAllDiscountFilterCache($arDiscountFilter, false);
			$boolFlagExt = CCatalogDiscount::SetAllDiscountResultCache($arDiscountResult);
			$result = $boolFlag && $boolFlagExt;
			self::$needDiscountCache = $result;
		}
		return $result;
	}

	public static function GetItemPrices(
		/** @noinspection PhpUnusedParameterInspection */$IBLOCK_ID,
		$arCatalogPrices,
		$arItem, $bVATInclude = true,
		$arCurrencyParams = array(),
		$USER_ID = 0,
		$LID = SITE_ID
	)
	{
		$arPrices = array();

		if (empty($arCatalogPrices) || !is_array($arCatalogPrices))
			return $arPrices;

		global $USER;
		static $arCurUserGroups = array();
		static $strBaseCurrency = '';

		if (self::$catalogIncluded === null)
			self::$catalogIncluded = Loader::includeModule('catalog');
		if (self::$catalogIncluded)
		{
			$existUser = (isset($USER) && $USER instanceof CUser);
			$USER_ID = (int)$USER_ID;
			$intUserID = ($USER_ID > 0 ? $USER_ID : 0);
			if ($intUserID == 0 && $existUser)
				$intUserID = (int)$USER->GetID();
			if (!isset($arCurUserGroups[$intUserID]))
			{
				$arUserGroups = array(2);
				if ($intUserID > 0)
					$arUserGroups = CUser::GetUserGroup($intUserID);
				elseif ($existUser)
					$arUserGroups = $USER->GetUserGroupArray();
				Main\Type\Collection::normalizeArrayValuesByInt($arUserGroups);
				$arCurUserGroups[$intUserID] = $arUserGroups;
				unset($arUserGroups);
			}
			$arUserGroups = $arCurUserGroups[$intUserID];

			$boolConvert = false;
			$resultCurrency = '';
			if (isset($arCurrencyParams['CURRENCY_ID']) && !empty($arCurrencyParams['CURRENCY_ID']))
			{
				$boolConvert = true;
				$resultCurrency = $arCurrencyParams['CURRENCY_ID'];
			}
			if (!$boolConvert && '' == $strBaseCurrency)
				$strBaseCurrency = Currency\CurrencyManager::getBaseCurrency();

			$percentVat = $arItem['CATALOG_VAT'] * 0.01;
			$percentPriceWithVat = 1 + $arItem['CATALOG_VAT'] * 0.01;

			$strMinCode = '';
			$boolStartMin = true;
			$dblMinPrice = 0;
			$strMinCurrency = ($boolConvert ? $resultCurrency : $strBaseCurrency);
			CCatalogDiscountSave::Disable();
			foreach ($arCatalogPrices as $key => $value)
			{
				$catalogPriceValue = 'CATALOG_PRICE_'.$value['ID'];
				$catalogCurrencyValue = 'CATALOG_CURRENCY_'.$value['ID'];
				if (
					!$value['CAN_VIEW']
					|| !isset($arItem[$catalogPriceValue])
					|| $arItem[$catalogPriceValue] == ''
				)
					continue;

				$arItem[$catalogPriceValue] = (float)$arItem[$catalogPriceValue];
				// get final price with VAT included.
				if ($arItem['CATALOG_VAT_INCLUDED'] != 'Y')
					$arItem[$catalogPriceValue] *= $percentPriceWithVat;

				$originalCurrency = $arItem[$catalogCurrencyValue];
				$calculateCurrency = $arItem[$catalogCurrencyValue];
				$calculatePrice = $arItem[$catalogPriceValue];
				$cnangeCurrency = ($boolConvert && $resultCurrency != $calculateCurrency);
				if ($cnangeCurrency)
				{
					$calculateCurrency = $resultCurrency;
					$calculatePrice = CCurrencyRates::ConvertCurrency($calculatePrice, $originalCurrency, $resultCurrency);
				}

				// so discounts will include VAT
				$discounts = array();
				if (self::isEnabledCalculationDiscounts())
				{
					$discounts = CCatalogDiscount::GetDiscount(
						$arItem['ID'],
						$arItem['IBLOCK_ID'],
						array($value['ID']),
						$arUserGroups,
						'N',
						$LID,
						array()
					);
				}
				$discountPrice = CCatalogProduct::CountPriceWithDiscount(
					$calculatePrice,
					$calculateCurrency,
					$discounts
				);
				unset($discounts);
				if ($discountPrice === false)
					continue;

				$originalPriceWithVat = $arItem[$catalogPriceValue];
				$priceWithVat = $calculatePrice;
				$discountPriceWithVat = $discountPrice;

				if ($cnangeCurrency)
					$originalDiscountPrice = CCurrencyRates::ConvertCurrency($discountPrice, $calculateCurrency, $arItem[$catalogCurrencyValue]);
				else
					$originalDiscountPrice = $discountPrice;
				$originalDiscountPriceWithVat = $originalDiscountPrice;

				$arItem[$catalogPriceValue] /= $percentPriceWithVat;
				$calculatePrice /= $percentPriceWithVat;
				$originalDiscountPrice /= $percentPriceWithVat;
				$discountPrice /= $percentPriceWithVat;

				$originalVatValue = $originalPriceWithVat - $arItem[$catalogPriceValue];
				$vatValue = $priceWithVat - $calculatePrice;
				$originalDiscountVatValue = $originalDiscountPriceWithVat - $originalDiscountPrice;
				$discountVatValue = $discountPriceWithVat - $discountPrice;

				$roundPriceWithVat = Catalog\Product\Price::roundPrice($value['ID'], $discountPriceWithVat, $calculateCurrency);
				$roundPrice = Catalog\Product\Price::roundPrice($value['ID'], $discountPrice, $calculateCurrency);

				$priceResult = array(
					'VALUE_NOVAT' => $calculatePrice,
					'PRINT_VALUE_NOVAT' => CCurrencyLang::CurrencyFormat($calculatePrice, $calculateCurrency, true),

					'VALUE_VAT' => $priceWithVat,
					'PRINT_VALUE_VAT' => CCurrencyLang::CurrencyFormat($priceWithVat, $calculateCurrency, true),

					'VATRATE_VALUE' => $vatValue,
					'PRINT_VATRATE_VALUE' => CCurrencyLang::CurrencyFormat($vatValue, $calculateCurrency, true),

					'DISCOUNT_VALUE_NOVAT' => $discountPrice,
					'PRINT_DISCOUNT_VALUE_NOVAT' => CCurrencyLang::CurrencyFormat($discountPrice, $calculateCurrency, true),

					'DISCOUNT_VALUE_VAT' => $discountPriceWithVat,
					'PRINT_DISCOUNT_VALUE_VAT' => CCurrencyLang::CurrencyFormat($discountPriceWithVat, $calculateCurrency, true),

					'DISCOUNT_VATRATE_VALUE' => $discountVatValue,
					'PRINT_DISCOUNT_VATRATE_VALUE' => CCurrencyLang::CurrencyFormat($discountVatValue, $calculateCurrency, true),

					'CURRENCY' => $calculateCurrency,

					'ROUND_VALUE_VAT' => $roundPriceWithVat,
					'ROUND_VALUE_NOVAT' => $roundPrice,
					'ROUND_VATRATE_VALUE' => $roundPriceWithVat - $roundPrice,
				);

				if ($cnangeCurrency)
				{
					$priceResult['ORIG_VALUE_NOVAT'] = $arItem[$catalogPriceValue];
					$priceResult['ORIG_VALUE_VAT'] = $originalPriceWithVat;
					$priceResult['ORIG_VATRATE_VALUE'] = $originalVatValue;
					$priceResult['ORIG_DISCOUNT_VALUE_NOVAT'] = $originalDiscountPrice;
					$priceResult['ORIG_DISCOUNT_VALUE_VAT'] = $originalDiscountPriceWithVat;
					$priceResult['ORIG_DISCOUNT_VATRATE_VALUE'] = $originalDiscountVatValue;
					$priceResult['ORIG_CURRENCY'] = $originalCurrency;
				}

				$priceResult['PRICE_ID'] = $value['ID'];
				$priceResult['ID'] = $arItem['CATALOG_PRICE_ID_'.$value['ID']];
				$priceResult['CAN_ACCESS'] = $arItem['CATALOG_CAN_ACCESS_'.$value['ID']];
				$priceResult['CAN_BUY'] = $arItem['CATALOG_CAN_BUY_'.$value['ID']];
				$priceResult['MIN_PRICE'] = 'N';

				if ($bVATInclude)
				{
					$priceResult['VALUE'] = $priceWithVat;
					$priceResult['PRINT_VALUE'] = $priceResult['PRINT_VALUE_VAT'];
					$priceResult['UNROUND_DISCOUNT_VALUE'] = $discountPriceWithVat;
					$priceResult['DISCOUNT_VALUE'] = $roundPriceWithVat;
					$priceResult['PRINT_DISCOUNT_VALUE'] = CCurrencyLang::CurrencyFormat(
						$roundPriceWithVat,
						$calculateCurrency,
						true
					);
				}
				else
				{
					$priceResult['VALUE'] = $calculatePrice;
					$priceResult['PRINT_VALUE'] = $priceResult['PRINT_VALUE_NOVAT'];
					$priceResult['UNROUND_DISCOUNT_VALUE'] = $discountPrice;
					$priceResult['DISCOUNT_VALUE'] = $roundPrice;
					$priceResult['PRINT_DISCOUNT_VALUE'] = CCurrencyLang::CurrencyFormat(
						$roundPrice,
						$calculateCurrency,
						true
					);;
				}

				if ((roundEx($priceResult['VALUE'], 2) - roundEx($priceResult['UNROUND_DISCOUNT_VALUE'], 2)) < 0.01)
				{
					$priceResult['VALUE'] = $priceResult['DISCOUNT_VALUE'];
					$priceResult['PRINT_VALUE'] = $priceResult['PRINT_DISCOUNT_VALUE'];
					$priceResult['DISCOUNT_DIFF'] = 0;
					$priceResult['DISCOUNT_DIFF_PERCENT'] = 0;
				}
				else
				{
					$priceResult['DISCOUNT_DIFF'] = $priceResult['VALUE'] - $priceResult['DISCOUNT_VALUE'];
					$priceResult['DISCOUNT_DIFF_PERCENT'] = roundEx(100*$priceResult['DISCOUNT_DIFF']/$priceResult['VALUE'], 0);
				}
				$priceResult['PRINT_DISCOUNT_DIFF'] = CCurrencyLang::CurrencyFormat(
					$priceResult['DISCOUNT_DIFF'],
					$calculateCurrency,
					true
				);

				if ($boolStartMin)
				{
					$dblMinPrice = ($boolConvert || ($calculateCurrency == $strMinCurrency)
						? $priceResult['DISCOUNT_VALUE']
						: CCurrencyRates::ConvertCurrency($priceResult['DISCOUNT_VALUE'], $calculateCurrency, $strMinCurrency)
					);
					$strMinCode = $key;
					$boolStartMin = false;
				}
				else
				{
					$dblComparePrice = ($boolConvert || ($calculateCurrency == $strMinCurrency)
						? $priceResult['DISCOUNT_VALUE']
						: CCurrencyRates::ConvertCurrency($priceResult['DISCOUNT_VALUE'], $calculateCurrency, $strMinCurrency)
					);
					if ($dblMinPrice > $dblComparePrice)
					{
						$dblMinPrice = $dblComparePrice;
						$strMinCode = $key;
					}
				}
				unset($calculateCurrency);
				unset($originalCurrency);

				$arPrices[$key] = $priceResult;
				unset($priceResult);
			}
			if ($strMinCode != '')
				$arPrices[$strMinCode]['MIN_PRICE'] = 'Y';
			CCatalogDiscountSave::Enable();

			unset($percentPriceWithVat);
			unset($percentVat);
		}
		else
		{
			$strMinCode = '';
			$boolStartMin = true;
			$dblMinPrice = 0;
			foreach($arCatalogPrices as $key => $value)
			{
				if (!$value['CAN_VIEW'])
					continue;

				$dblValue = round(doubleval($arItem["PROPERTY_".$value["ID"]."_VALUE"]), 2);
				if ($boolStartMin)
				{
					$dblMinPrice = $dblValue;
					$strMinCode = $key;
					$boolStartMin = false;
				}
				else
				{
					if ($dblMinPrice > $dblValue)
					{
						$dblMinPrice = $dblValue;
						$strMinCode = $key;
					}
				}
				$arPrices[$key] = array(
					"ID" => $arItem["PROPERTY_".$value["ID"]."_VALUE_ID"],
					"VALUE" => $dblValue,
					"PRINT_VALUE" => $dblValue." ".$arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
					"DISCOUNT_VALUE" => $dblValue,
					"PRINT_DISCOUNT_VALUE" => $dblValue." ".$arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
					"CURRENCY" => $arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
					"CAN_ACCESS" => true,
					"CAN_BUY" => false,
					'DISCOUNT_DIFF_PERCENT' => 0,
					'DISCOUNT_DIFF' => 0,
					'PRINT_DISCOUNT_DIFF' => '0 '.$arItem["PROPERTY_".$value["ID"]."_DESCRIPTION"],
					"MIN_PRICE" => "N",
					'PRICE_ID' => $value['ID']
				);
			}
			if ($strMinCode != '')
				$arPrices[$strMinCode]['MIN_PRICE'] = 'Y';
		}
		return $arPrices;
	}

	/**
	 * @param int $IBLOCK_ID
	 * @param array $arCatalogPrices
	 * @param array $arItem
	 * @return bool
	 */
	public static function CanBuy(
		/** @noinspection PhpUnusedParameterInspection */$IBLOCK_ID,
		$arCatalogPrices,
		$arItem
	)
	{
		if (isset($arItem['ACTIVE']) && $arItem['ACTIVE'] === 'N')
			return false;

		if (isset($arItem['CATALOG_AVAILABLE']) && $arItem['CATALOG_AVAILABLE'] === 'N')
			return false;
		if (isset($arItem['AVAILABLE']) && $arItem['AVAILABLE'] === 'N')
			return false;

		if (!empty($arItem["PRICE_MATRIX"]) && is_array($arItem["PRICE_MATRIX"]))
		{
			return $arItem["PRICE_MATRIX"]["AVAILABLE"] == "Y";
		}
		else
		{
			if (empty($arCatalogPrices) || !is_array($arCatalogPrices))
				return false;

			foreach ($arCatalogPrices as $arPrice)
			{
				//if ($arPrice["CAN_BUY"] && isset($arItem["CATALOG_PRICE_".$arPrice["ID"]]) && $arItem["CATALOG_PRICE_".$arPrice["ID"]] !== null)
				if ($arPrice["CAN_BUY"] && isset($arItem["CATALOG_PRICE_".$arPrice["ID"]]))
					return true;
			}
		}
		return false;
	}

	public static function GetProductProperties(
		$IBLOCK_ID,
		/** @noinspection PhpUnusedParameterInspection */$ELEMENT_ID,
		$arPropertiesList,
		$arPropertiesValues
	)
	{
		static $cache = array();
		static $userTypeList = array();
		$propertyTypeSupport = array(
			'Y' => array(
				'N' => true,
				'S' => true,
				'L' => true,
				'G' => true,
				'E' => true
			),
			'N' => array(
				'L' => true,
				'E' => true
			)
		);

		$result = array();
		foreach ($arPropertiesList as $pid)
		{
			if (preg_match("/[^A-Za-z0-9_]/", $pid) || !isset($arPropertiesValues[$pid]))
				continue;
			$prop = $arPropertiesValues[$pid];
			$prop['ID'] = (int)$prop['ID'];
			if (!isset($propertyTypeSupport[$prop['MULTIPLE']][$prop['PROPERTY_TYPE']]))
			{
				continue;
			}
			$emptyValues = true;
			$productProp = array('VALUES' => array(), 'SELECTED' => false, 'SET' => false);

			$userTypeProp = false;
			$userType = null;
			if (isset($prop['USER_TYPE']) && !empty($prop['USER_TYPE']))
			{
				if (!isset($userTypeList[$prop['USER_TYPE']]))
				{
					$userTypeDescr = CIBlockProperty::GetUserType($prop['USER_TYPE']);
					if (isset($userTypeDescr['GetPublicViewHTML']))
					{
						$userTypeList[$prop['USER_TYPE']] = $userTypeDescr['GetPublicViewHTML'];
					}
				}
				if (isset($userTypeList[$prop['USER_TYPE']]))
				{
					$userTypeProp = true;
					$userType = $userTypeList[$prop['USER_TYPE']];
				}
			}

			if ($prop["MULTIPLE"] == "Y" && !empty($prop["VALUE"]) && is_array($prop["VALUE"]))
			{
				if ($userTypeProp)
				{
					$countValues = 0;
					foreach($prop["VALUE"] as $value)
					{
						if (!is_scalar($value))
							continue;
						$value = (string)$value;
						$displayValue = (string)call_user_func_array($userType,
							array(
								$prop,
								array('VALUE' => $value),
								array(array('MODE' => 'SIMPLE_TEXT'))
							));
						if ('' !== $displayValue)
						{
							if ($productProp["SELECTED"] === false)
								$productProp["SELECTED"] = $value;
							$productProp["VALUES"][$value] = htmlspecialcharsbx($displayValue);
							$emptyValues = false;
							$countValues++;
						}
					}
					$productProp['SET'] = ($countValues === 1);
				}
				else
				{
					switch($prop["PROPERTY_TYPE"])
					{
					case "S":
					case "N":
						$countValues = 0;
						foreach($prop["VALUE"] as $value)
						{
							if (!is_scalar($value))
								continue;
							$value = (string)$value;
							if($value !== '')
							{
								if($productProp["SELECTED"] === false)
									$productProp["SELECTED"] = $value;
								$productProp["VALUES"][$value] = $value;
								$emptyValues = false;
								$countValues++;
							}
							$productProp['SET'] = ($countValues === 1);
						}
						break;
					case "G":
						$ar = array();
						foreach($prop["VALUE"] as $value)
						{
							$value = (int)$value;
							if($value > 0)
								$ar[] = $value;
						}
						if (!empty($ar))
						{
							$countValues = 0;
							$rsSections = CIBlockSection::GetList(
								array("LEFT_MARGIN"=>"ASC"),
								array("=ID" => $ar),
								false,
								array('ID', 'NAME')
							);
							while ($arSection = $rsSections->GetNext())
							{
								$arSection["ID"] = (int)$arSection["ID"];
								if ($productProp["SELECTED"] === false)
									$productProp["SELECTED"] = $arSection["ID"];
								$productProp["VALUES"][$arSection["ID"]] = $arSection["NAME"];
								$emptyValues = false;
								$countValues++;
							}
							$productProp['SET'] = ($countValues === 1);
						}
						break;
					case "E":
						$ar = array();
						foreach($prop["VALUE"] as $value)
						{
							$value = (int)$value;
							if($value > 0)
								$ar[] = $value;
						}
						if (!empty($ar))
						{
							$countValues = 0;
							$rsElements = CIBlockElement::GetList(
								array("ID" => "ASC"),
								array("=ID" => $ar),
								false,
								false,
								array("ID", "NAME")
							);
							while($arElement = $rsElements->GetNext())
							{
								$arElement['ID'] = (int)$arElement['ID'];
								if($productProp["SELECTED"] === false)
									$productProp["SELECTED"] = $arElement["ID"];
								$productProp["VALUES"][$arElement["ID"]] = $arElement["NAME"];
								$emptyValues = false;
								$countValues++;
							}
							$productProp['SET'] = ($countValues === 1);
						}
						break;
					case "L":
						$countValues = 0;
						foreach($prop["VALUE"] as $i => $value)
						{
							$prop["VALUE_ENUM_ID"][$i] = (int)$prop["VALUE_ENUM_ID"][$i];
							if($productProp["SELECTED"] === false)
								$productProp["SELECTED"] = $prop["VALUE_ENUM_ID"][$i];
							$productProp["VALUES"][$prop["VALUE_ENUM_ID"][$i]] = $value;
							$emptyValues = false;
							$countValues++;
						}
						$productProp['SET'] = ($countValues === 1);
						break;
					}
				}
			}
			elseif($prop["MULTIPLE"] == "N")
			{
				switch($prop["PROPERTY_TYPE"])
				{
				case "L":
					if (0 == (int)$prop["VALUE_ENUM_ID"])
					{
						if (isset($cache[$prop['ID']]))
						{
							$productProp = $cache[$prop['ID']];
							$emptyValues = false;
						}
						else
						{
							$rsEnum = CIBlockPropertyEnum::GetList(
								array("SORT"=>"ASC", "VALUE"=>"ASC"),
								array("IBLOCK_ID"=>$IBLOCK_ID, "PROPERTY_ID" => $prop['ID'])
							);
							while ($arEnum = $rsEnum->GetNext())
							{
								$arEnum["ID"] = (int)$arEnum["ID"];
								$productProp["VALUES"][$arEnum["ID"]] = $arEnum["VALUE"];
								if ($arEnum["DEF"] == "Y")
									$productProp["SELECTED"] = $arEnum["ID"];
								$emptyValues = false;
							}
							if (!$emptyValues)
							{
								$cache[$prop['ID']] = $productProp;
							}
						}
					}
					else
					{
						$prop['VALUE_ENUM_ID'] = (int)$prop['VALUE_ENUM_ID'];
						$productProp['VALUES'][$prop['VALUE_ENUM_ID']] = $prop['VALUE'];
						$productProp['SELECTED'] = $prop['VALUE_ENUM_ID'];
						$productProp['SET'] = true;
						$emptyValues = false;
					}
					break;
				case "E":
					if (0 == (int)$prop['VALUE'])
					{
						if (isset($cache[$prop['ID']]))
						{
							$productProp = $cache[$prop['ID']];
							$emptyValues = false;
						}
						else
						{
							if($prop["LINK_IBLOCK_ID"] > 0)
							{
								$rsElements = CIBlockElement::GetList(
									array("NAME"=>"ASC", "SORT"=>"ASC"),
									array("IBLOCK_ID"=>$prop["LINK_IBLOCK_ID"], "ACTIVE"=>"Y"),
									false, false,
									array("ID", "NAME")
								);
								while ($arElement = $rsElements->GetNext())
								{
									$arElement['ID'] = (int)$arElement['ID'];
									if($productProp["SELECTED"] === false)
										$productProp["SELECTED"] = $arElement["ID"];
									$productProp["VALUES"][$arElement["ID"]] = $arElement["NAME"];
									$emptyValues = false;
								}
								if (!$emptyValues)
								{
									$cache[$prop['ID']] = $productProp;
								}
							}
						}
					}
					else
					{
						$rsElements = CIBlockElement::GetList(
							array(),
							array('ID' => $prop["VALUE"], 'ACTIVE' => 'Y'),
							false,
							false,
							array('ID', 'NAME')
						);
						if ($arElement = $rsElements->GetNext())
						{
							$arElement['ID'] = (int)$arElement['ID'];
							$productProp['VALUES'][$arElement['ID']] = $arElement['NAME'];
							$productProp['SELECTED'] = $arElement['ID'];
							$productProp['SET'] = true;
							$emptyValues = false;
						}
					}
					break;
				}
			}

			if (!$emptyValues)
			{
				$result[$pid] = $productProp;
			}
		}

		return $result;
	}

	public static function getFillProductProperties($productProps)
	{
		$result = array();
		if (!empty($productProps) && is_array($productProps))
		{
			foreach ($productProps as $propID => $propInfo)
			{
				if (isset($propInfo['SET']) && $propInfo['SET'])
				{
					$result[$propID] = array(
						'ID' => $propInfo['SELECTED'],
						'VALUE' => $propInfo['VALUES'][$propInfo['SELECTED']]
					);
				}
			}
		}
		return $result;
	}

	/*
	Checks arPropertiesValues against DB values
	returns array on success
	or number on fail (may be used for debug)
	*/
	public static function CheckProductProperties($iblockID, $elementID, $propertiesList, $propertiesValues, $enablePartialList = false)
	{
		$propertyTypeSupport = array(
			'Y' => array(
				'N' => true,
				'S' => true,
				'L' => true,
				'G' => true,
				'E' => true
			),
			'N' => array(
				'L' => true,
				'E' => true
			)
		);
		$iblockID = (int)$iblockID;
		$elementID = (int)$elementID;
		if (0 >= $iblockID || 0 >= $elementID)
			return 6;
		$enablePartialList = (true === $enablePartialList);
		$sortIndex = 1;
		$result = array();
		if (!is_array($propertiesList))
			$propertiesList = array();
		if (empty($propertiesList))
			return $result;
		$checkProps = array_fill_keys($propertiesList, true);
		$propCodes = $checkProps;
		$existProps =  array();
		$rsProps = CIBlockElement::GetProperty($iblockID, $elementID, 'sort', 'asc', array());
		while ($oneProp = $rsProps->Fetch())
		{
			if (!isset($propCodes[$oneProp['CODE']]) && !isset($propCodes[$oneProp['ID']]))
				continue;
			$propID = (isset($propCodes[$oneProp['CODE']]) ? $oneProp['CODE'] : $oneProp['ID']);
			if (!isset($checkProps[$propID]))
				continue;

			if (!isset($propertyTypeSupport[$oneProp['MULTIPLE']][$oneProp['PROPERTY_TYPE']]))
			{
				return ($oneProp['MULTIPLE'] == 'Y' ? 2 : 3);
			}

			if (null !== $oneProp['VALUE'])
			{
				$existProps[$propID] = true;
			}

			if (!isset($propertiesValues[$propID]))
			{
				if ($enablePartialList)
				{
					continue;
				}
				return 1;
			}

			if (!is_scalar($propertiesValues[$propID]))
					return 5;

			$propertiesValues[$propID] = (string)$propertiesValues[$propID];
			$existValue = ('' != $propertiesValues[$propID]);
			if (!$existValue)
				return 1;

			$userTypeProp = false;
			$userType = null;
			if (isset($oneProp['USER_TYPE']) && !empty($oneProp['USER_TYPE']))
			{
				$userTypeDescr = CIBlockProperty::GetUserType($oneProp['USER_TYPE']);
				if (isset($userTypeDescr['GetPublicViewHTML']))
				{
					$userTypeProp = true;
					$userType = $userTypeDescr['GetPublicViewHTML'];
				}
			}

			if ($oneProp["MULTIPLE"] == "Y")
			{
				if ($userTypeProp)
				{
					if ($oneProp["VALUE"] == $propertiesValues[$propID])
					{
						$displayValue = (string)call_user_func_array($userType,
							array(
								$oneProp,
								array('VALUE' => $oneProp['VALUE']),
								array('MODE' => 'SIMPLE_TEXT')
							));
						$result[] = array(
							"NAME" => $oneProp["NAME"],
							"CODE" => $propID,
							"VALUE" => $displayValue,
							"SORT" => $sortIndex++,
						);
						unset($checkProps[$propID]);//mark as found
					}
				}
				else
				{
					switch($oneProp["PROPERTY_TYPE"])
					{
					case "S":
					case "N":
						if ($oneProp["VALUE"] == $propertiesValues[$propID])
						{
							$result[] = array(
								"NAME" => $oneProp["NAME"],
								"CODE" => $propID,
								"VALUE" => $oneProp["VALUE"],
								"SORT" => $sortIndex++,
							);
							unset($checkProps[$propID]);//mark as found
						}
						break;
					case "G":
						if ($oneProp["VALUE"] == $propertiesValues[$propID])
						{
							$rsSection = CIBlockSection::GetList(
								array(),
								array("=ID" => $oneProp["VALUE"]),
								false,
								array('ID', 'NAME')
							);
							if($arSection = $rsSection->Fetch())
							{
								$result[] = array(
									"NAME" => $oneProp["NAME"],
									"CODE" => $propID,
									"VALUE" => $arSection["NAME"],
									"SORT" => $sortIndex++,
								);
								unset($checkProps[$propID]);//mark as found
							}
						}
						break;
					case "E":
						if ($oneProp["VALUE"] == $propertiesValues[$propID])
						{
							$rsElement = CIBlockElement::GetList(
								array(),
								array("=ID" => $oneProp["VALUE"]),
								false,
								false,
								array("ID", "NAME")
							);
							if ($arElement = $rsElement->Fetch())
							{
								$result[] = array(
									"NAME" => $oneProp["NAME"],
									"CODE" => $propID,
									"VALUE" => $arElement["NAME"],
									"SORT" => $sortIndex++,
								);
								unset($checkProps[$propID]);//mark as found
							}
						}
						break;
					case "L":
						if ($oneProp["VALUE"] == $propertiesValues[$propID])
						{
							$rsEnum = CIBlockPropertyEnum::GetList(
								array(),
								array( "ID" => $propertiesValues[$propID], "IBLOCK_ID" => $iblockID, "PROPERTY_ID" => $oneProp['ID'])
							);
							if ($arEnum = $rsEnum->Fetch())
							{
								$result[] = array(
									"NAME" => $oneProp["NAME"],
									"CODE" => $propID,
									"VALUE" => $arEnum["VALUE"],
									"SORT" => $sortIndex++,
								);
								unset($checkProps[$propID]);//mark as found
							}
						}
						break;
					}
				}
			}
			else
			{
				switch ($oneProp["PROPERTY_TYPE"])
				{
				case "L":
					if (0 < (int)$propertiesValues[$propID])
					{
						$rsEnum = CIBlockPropertyEnum::GetList(
							array(),
							array("ID" => $propertiesValues[$propID], "IBLOCK_ID" => $iblockID, "PROPERTY_ID" => $oneProp['ID'])
						);
						if ($arEnum = $rsEnum->Fetch())
						{
							$result[] = array(
								"NAME" => $oneProp["NAME"],
								"CODE" => $propID,
								"VALUE" => $arEnum["VALUE"],
								"SORT" => $sortIndex++,
							);
							unset($checkProps[$propID]);//mark as found
						}
					}
					break;
				case "E":
					if (0 < (int)$propertiesValues[$propID])
					{
						$rsElement = CIBlockElement::GetList(
							array(),
							array("=ID" => $propertiesValues[$propID]),
							false,
							false,
							array("ID", "NAME")
						);
						if ($arElement = $rsElement->Fetch())
						{
							$result[] = array(
								"NAME" => $oneProp["NAME"],
								"CODE" => $propID,
								"VALUE" => $arElement["NAME"],
								"SORT" => $sortIndex++,
							);
							unset($checkProps[$propID]);//mark as found
						}
					}
					break;
				}
			}
		}

		if ($enablePartialList && !empty($checkProps))
		{
			$nonExistProps = array_keys($checkProps);
			foreach ($nonExistProps as &$oneCode)
			{
				if (!isset($existProps[$oneCode]))
					unset($checkProps[$oneCode]);
			}
			unset($oneCode);
		}

		if(!empty($checkProps))
			return 4;

		return $result;
	}

	public static function GetOffersIBlock($IBLOCK_ID)
	{
		$arResult = false;
		$IBLOCK_ID = (int)$IBLOCK_ID;
		if (0 < $IBLOCK_ID)
		{
			if (self::$catalogIncluded === null)
				self::$catalogIncluded = Loader::includeModule('catalog');
			if (self::$catalogIncluded)
			{
				$arCatalog = CCatalogSku::GetInfoByProductIBlock($IBLOCK_ID);
				if (!empty($arCatalog) && is_array($arCatalog))
				{
					$arResult = array(
						'OFFERS_IBLOCK_ID' => $arCatalog['IBLOCK_ID'],
						'OFFERS_PROPERTY_ID' => $arCatalog['SKU_PROPERTY_ID'],
					);
				}
			}
		}
		return $arResult;
	}

	public static function GetOfferProperties($offerID, $iblockID, $propertiesList, $skuTreeProps = '')
	{
		$iblockInfo = false;
		$result = array();

		$iblockID = (int)$iblockID;
		$offerID = (int)$offerID;
		if (0 >= $iblockID || 0 >= $offerID)
			return $result;

		$skuPropsList = array();
		if (!empty($skuTreeProps))
		{
			if (is_array($skuTreeProps))
			{
				$skuPropsList = $skuTreeProps;
			}
			else
			{
				$skuTreeProps = base64_decode((string)$skuTreeProps);
				if (false !== $skuTreeProps && CheckSerializedData($skuTreeProps))
				{
					$skuPropsList = unserialize($skuTreeProps, ['allowed_classes' => false]);
					if (!is_array($skuPropsList))
					{
						$skuPropsList = array();
					}
				}
			}
		}

		if (!is_array($propertiesList))
		{
			$propertiesList = array();
		}
		if (!empty($skuPropsList))
		{
			$propertiesList = array_unique(array_merge($propertiesList, $skuPropsList));
		}
		if (empty($propertiesList))
			return $result;
		$propCodes = array_fill_keys($propertiesList, true);

		if (self::$catalogIncluded === null)
			self::$catalogIncluded = Loader::includeModule('catalog');
		if (self::$catalogIncluded)
		{
			$iblockInfo = CCatalogSku::GetInfoByProductIBlock($iblockID);
			if (empty($iblockInfo))
			{
				$iblockInfo = CCatalogSku::GetInfoByOfferIBlock($iblockID);
			}
		}
		if (empty($iblockInfo))
			return $result;

		$sortIndex = 1;
		$rsProps = CIBlockElement::GetProperty(
			$iblockInfo['IBLOCK_ID'],
			$offerID,
			array("sort"=>"asc", "enum_sort" => "asc", "value_id"=>"asc"),
			array("EMPTY"=>"N")
		);

		while ($oneProp = $rsProps->Fetch())
		{
			if (!isset($propCodes[$oneProp['CODE']]) && !isset($propCodes[$oneProp['ID']]))
				continue;
			$propID = (isset($propCodes[$oneProp['CODE']]) ? $oneProp['CODE'] : $oneProp['ID']);

			$userTypeProp = false;
			$userType = null;
			if (isset($oneProp['USER_TYPE']) && !empty($oneProp['USER_TYPE']))
			{
				$userTypeDescr = CIBlockProperty::GetUserType($oneProp['USER_TYPE']);
				if (isset($userTypeDescr['GetPublicViewHTML']))
				{
					$userTypeProp = true;
					$userType = $userTypeDescr['GetPublicViewHTML'];
				}
			}

			if ($userTypeProp)
			{
				$displayValue = (string)call_user_func_array($userType,
					array(
						$oneProp,
						array('VALUE' => $oneProp['VALUE']),
						array('MODE' => 'SIMPLE_TEXT')
					));
				$result[] = array(
					"NAME" => $oneProp["NAME"],
					"CODE" => $propID,
					"VALUE" => $displayValue,
					"SORT" => $sortIndex++,
				);
			}
			else
			{
				switch ($oneProp["PROPERTY_TYPE"])
				{
				case "S":
				case "N":
					$result[] = array(
						"NAME" => $oneProp["NAME"],
						"CODE" => $propID,
						"VALUE" => $oneProp["VALUE"],
						"SORT" => $sortIndex++,
					);
					break;
				case "G":
					$rsSection = CIBlockSection::GetList(
						array(),
						array("=ID"=>$oneProp["VALUE"]),
						false,
						array('ID', 'NAME')
					);
					if ($arSection = $rsSection->Fetch())
					{
						$result[] = array(
							"NAME" => $oneProp["NAME"],
							"CODE" => $propID,
							"VALUE" => $arSection["NAME"],
							"SORT" => $sortIndex++,
						);
					}
					break;
				case "E":
					$rsElement = CIBlockElement::GetList(
						array(),
						array("=ID"=>$oneProp["VALUE"]),
						false,
						false,
						array("ID", "NAME")
					);
					if ($arElement = $rsElement->Fetch())
					{
						$result[] = array(
							"NAME" => $oneProp["NAME"],
							"CODE" => $propID,
							"VALUE" => $arElement["NAME"],
							"SORT" => $sortIndex++,
						);
					}
					break;
				case "L":
					$result[] = array(
						"NAME" => $oneProp["NAME"],
						"CODE" => $propID,
						"VALUE" => $oneProp["VALUE_ENUM"],
						"SORT" => $sortIndex++,
					);
					break;
				}
			}
		}
		return $result;
	}

	public static function GetOffersArray($arFilter, $arElementID, $arOrder, $arSelectFields, $arSelectProperties, $limit, $arPrices, $vat_include, $arCurrencyParams = array(), $USER_ID = 0, $LID = SITE_ID)
	{
		global $USER;

		$arResult = array();

		$boolCheckPermissions = false;
		$boolHideNotAvailable = false;
		$showPriceCount = false;
		$customFilter = false;
		$IBLOCK_ID = 0;

		if (!empty($arFilter) && is_array($arFilter))
		{
			if (isset($arFilter['IBLOCK_ID']))
				$IBLOCK_ID = $arFilter['IBLOCK_ID'];
			if (isset($arFilter['HIDE_NOT_AVAILABLE']))
				$boolHideNotAvailable = ($arFilter['HIDE_NOT_AVAILABLE'] === 'Y');
			if (isset($arFilter['CHECK_PERMISSIONS']))
				$boolCheckPermissions = ($arFilter['CHECK_PERMISSIONS'] === 'Y');
			if (isset($arFilter['SHOW_PRICE_COUNT']))
			{
				$showPriceCount = (int)$arFilter['SHOW_PRICE_COUNT'];
				if ($showPriceCount <= 0)
					$showPriceCount = false;
			}

			if (isset($arFilter['CUSTOM_FILTER']))
			{
				$customFilter = $arFilter['CUSTOM_FILTER'];
			}
		}
		else
		{
			$IBLOCK_ID = $arFilter;
		}

		if (self::$needDiscountCache === null)
		{
			if(\Bitrix\Main\Config\Option::get('sale', 'use_sale_discount_only') === 'Y')
			{
				self::$needDiscountCache = false;
			}
			else
			{
				$pricesAllow = CIBlockPriceTools::GetAllowCatalogPrices($arPrices);
				if (empty($pricesAllow))
				{
					self::$needDiscountCache = false;
				}
				else
				{
					$USER_ID = (int)$USER_ID;
					$userGroups = array(2);
					if ($USER_ID > 0)
						$userGroups = CUser::GetUserGroup($USER_ID);
					elseif (isset($USER) && $USER instanceof CUser)
						$userGroups = $USER->GetUserGroupArray();
					self::$needDiscountCache = CIBlockPriceTools::SetCatalogDiscountCache($pricesAllow, $userGroups);
					unset($userGroups);
				}
				unset($pricesAllow);
			}
		}

		$arOffersIBlock = CIBlockPriceTools::GetOffersIBlock($IBLOCK_ID);
		if($arOffersIBlock)
		{
			$arDefaultMeasure = CCatalogMeasure::getDefaultMeasure(true, true);

			$limit = (int)$limit;
			if (0 > $limit)
				$limit = 0;

			if (!isset($arOrder["ID"]))
				$arOrder["ID"] = "DESC";

			$intOfferIBlockID = $arOffersIBlock["OFFERS_IBLOCK_ID"];

			$productProperty = 'PROPERTY_'.$arOffersIBlock['OFFERS_PROPERTY_ID'];
			$productPropertyValue = $productProperty.'_VALUE';

			$propertyList = array();
			if (!empty($arSelectProperties))
			{
				$selectProperties = array_fill_keys($arSelectProperties, true);
				$propertyIterator = Iblock\PropertyTable::getList(array(
					'select' => array('ID', 'CODE'),
					'filter' => array('=IBLOCK_ID' => $intOfferIBlockID, '=ACTIVE' => 'Y'),
					'order' => array('SORT' => 'ASC', 'ID' => 'ASC')
				));
				while ($property = $propertyIterator->fetch())
				{
					$code = (string)$property['CODE'];
					if ($code == '')
						$code = $property['ID'];
					if (!isset($selectProperties[$code]))
						continue;
					$propertyList[] = $code;
					unset($code);
				}
				unset($property, $propertyIterator);
				unset($selectProperties);
			}

			$arFilter = array(
				"IBLOCK_ID" => $intOfferIBlockID,
				$productProperty => $arElementID,
				"ACTIVE" => "Y",
				"ACTIVE_DATE" => "Y",
			);

			if (!empty($customFilter))
			{
				$arFilter[] = $customFilter;
			}

			if ($boolHideNotAvailable)
				$arFilter['CATALOG_AVAILABLE'] = 'Y';
			if ($boolCheckPermissions)
			{
				$arFilter['CHECK_PERMISSIONS'] = "Y";
				$arFilter['MIN_PERMISSION'] = "R";
			}

			$arSelect = array(
				"ID" => 1,
				"IBLOCK_ID" => 1,
				$productProperty => 1,
				"CATALOG_QUANTITY" => 1
			);
			//if(!$arParams["USE_PRICE_COUNT"])
			{
				foreach($arPrices as $value)
				{
					if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
						continue;
					$arSelect[$value["SELECT"]] = 1;
					if ($showPriceCount !== false)
					{
						$arFilter['CATALOG_SHOP_QUANTITY_'.$value['ID']] = $showPriceCount;
					}
				}
			}

			if (!empty($arSelectFields))
			{
				foreach ($arSelectFields as $code)
					$arSelect[$code] = 1; //mark to select
				unset($code);
			}
			$checkFields = array();
			foreach (array_keys($arOrder) as $code)
			{
				$code = mb_strtoupper($code);
				$arSelect[$code] = 1;
				if ($code == 'ID' || $code == 'CATALOG_AVAILABLE')
					continue;
				$checkFields[] = $code;
			}
			unset($code);

			if (!isset($arSelect['PREVIEW_PICTURE']))
				$arSelect['PREVIEW_PICTURE'] = 1;
			if (!isset($arSelect['DETAIL_PICTURE']))
				$arSelect['DETAIL_PICTURE'] = 1;

			$arOfferIDs = array();
			$arMeasureMap = array();
			$intKey = 0;
			$arOffersPerElement = array();
			$arOffersLink = array();
			$extPrices = array();
			$rsOffers = CIBlockElement::GetList($arOrder, $arFilter, false, false, array_keys($arSelect));
			while($arOffer = $rsOffers->GetNext())
			{
				$arOffer['ID'] = (int)$arOffer['ID'];
				$element_id = (int)$arOffer[$productPropertyValue];
				//No more than limit offers per element
				if($limit > 0)
				{
					$arOffersPerElement[$element_id]++;
					if($arOffersPerElement[$element_id] > $limit)
						continue;
				}

				if($element_id > 0)
				{
					$arOffer['SORT_HASH'] = 'ID';
					if (!empty($checkFields))
					{
						$checkValues = '';
						foreach ($checkFields as $code)
							$checkValues .= (isset($arOffer[$code]) ? $arOffer[$code] : '').'|';
						unset($code);
						if ($checkValues != '')
							$arOffer['SORT_HASH'] = md5($checkValues);
						unset($checkValues);
					}
					$arOffer["LINK_ELEMENT_ID"] = $element_id;
					$arOffer["PROPERTIES"] = array();
					$arOffer["DISPLAY_PROPERTIES"] = array();

					Iblock\Component\Tools::getFieldImageData(
						$arOffer,
						array('PREVIEW_PICTURE', 'DETAIL_PICTURE'),
						Iblock\Component\Tools::IPROPERTY_ENTITY_ELEMENT,
						''
					);

					$arOffer['CHECK_QUANTITY'] = ('Y' == $arOffer['CATALOG_QUANTITY_TRACE'] && 'N' == $arOffer['CATALOG_CAN_BUY_ZERO']);
					$arOffer['CATALOG_TYPE'] = CCatalogProduct::TYPE_OFFER;
					$arOffer['CATALOG_MEASURE_NAME'] = $arDefaultMeasure['SYMBOL_RUS'];
					$arOffer['~CATALOG_MEASURE_NAME'] = $arDefaultMeasure['SYMBOL_RUS'];
					$arOffer["CATALOG_MEASURE_RATIO"] = 1;
					if (!isset($arOffer['CATALOG_MEASURE']))
						$arOffer['CATALOG_MEASURE'] = 0;
					$arOffer['CATALOG_MEASURE'] = (int)$arOffer['CATALOG_MEASURE'];
					if (0 > $arOffer['CATALOG_MEASURE'])
						$arOffer['CATALOG_MEASURE'] = 0;
					if (0 < $arOffer['CATALOG_MEASURE'])
					{
						if (!isset($arMeasureMap[$arOffer['CATALOG_MEASURE']]))
							$arMeasureMap[$arOffer['CATALOG_MEASURE']] = array();
						$arMeasureMap[$arOffer['CATALOG_MEASURE']][] = $intKey;
					}

					$arOfferIDs[] = $arOffer['ID'];
					$arResult[$intKey] = $arOffer;
					if (!isset($arOffersLink[$arOffer['ID']]))
					{
						$arOffersLink[$arOffer['ID']] = &$arResult[$intKey];
					}
					else
					{
						if (!isset($extPrices[$arOffer['ID']]))
						{
							$extPrices[$arOffer['ID']] = array();
						}
						$extPrices[$arOffer['ID']][] = &$arResult[$intKey];
					}
					$intKey++;
				}
			}
			if (!empty($arOfferIDs))
			{
				$rsRatios = CCatalogMeasureRatio::getList(
					array(),
					array('@PRODUCT_ID' => $arOfferIDs),
					false,
					false,
					array('PRODUCT_ID', 'RATIO')
				);
				while ($arRatio = $rsRatios->Fetch())
				{
					$arRatio['PRODUCT_ID'] = (int)$arRatio['PRODUCT_ID'];
					if (isset($arOffersLink[$arRatio['PRODUCT_ID']]))
					{
						$intRatio = (int)$arRatio['RATIO'];
						$dblRatio = (float)$arRatio['RATIO'];
						$mxRatio = ($dblRatio > $intRatio ? $dblRatio : $intRatio);
						if (CATALOG_VALUE_EPSILON > abs($mxRatio))
							$mxRatio = 1;
						elseif (0 > $mxRatio)
							$mxRatio = 1;
						$arOffersLink[$arRatio['PRODUCT_ID']]['CATALOG_MEASURE_RATIO'] = $mxRatio;
					}
				}

				if (!empty($propertyList))
				{
					CIBlockElement::GetPropertyValuesArray($arOffersLink, $intOfferIBlockID, $arFilter);
					foreach ($arResult as &$arOffer)
					{
						if (self::$needDiscountCache)
							CCatalogDiscount::SetProductPropertiesCache($arOffer['ID'], $arOffer["PROPERTIES"]);
						if (\Bitrix\Main\Config\Option::get('sale', 'use_sale_discount_only') === 'Y')
							Catalog\Discount\DiscountManager::setProductPropertiesCache($arOffer['ID'], $arOffer["PROPERTIES"]);

						foreach ($propertyList as $pid)
						{
							if (!isset($arOffer["PROPERTIES"][$pid]))
								continue;
							$prop = &$arOffer["PROPERTIES"][$pid];
							$boolArr = is_array($prop["VALUE"]);
							if(
								($boolArr && !empty($prop["VALUE"])) ||
								(!$boolArr && (string)$prop["VALUE"] !== '')
							)
							{
								$arOffer["DISPLAY_PROPERTIES"][$pid] = CIBlockFormatProperties::GetDisplayValue($arOffer, $prop, "catalog_out");
							}
							unset($boolArr, $prop);
						}
						unset($pid);
					}
					unset($arOffer);
				}

				if (!empty($extPrices))
				{
					foreach ($extPrices as $origID => $prices)
					{
						foreach ($prices as $oneRow)
						{
							$oneRow['PROPERTIES'] = $arOffersLink[$origID]['PROPERTIES'];
							$oneRow['DISPLAY_PROPERTIES'] = $arOffersLink[$origID]['DISPLAY_PROPERTIES'];
							$oneRow['CATALOG_MEASURE_RATIO'] = $arOffersLink[$origID]['CATALOG_MEASURE_RATIO'];
						}
					}
				}
				if (self::$needDiscountCache)
				{
					CCatalogDiscount::SetProductSectionsCache($arOfferIDs);
					CCatalogDiscount::SetDiscountProductCache($arOfferIDs, array('IBLOCK_ID' => $intOfferIBlockID, 'GET_BY_ID' => 'Y'));
				}
				if (\Bitrix\Main\Config\Option::get('sale', 'use_sale_discount_only') === 'Y')
				{
					$pricesAllow = CIBlockPriceTools::GetAllowCatalogPrices($arPrices);
					if (!empty($pricesAllow))
					{
						$USER_ID = (int)$USER_ID;
						$userGroups = array(2);
						if ($USER_ID > 0)
							$userGroups = CUser::GetUserGroup($USER_ID);
						elseif (isset($USER) && $USER instanceof CUser)
							$userGroups = $USER->GetUserGroupArray();
						Catalog\Discount\DiscountManager::preloadPriceData($arOfferIDs, $pricesAllow);
						Catalog\Discount\DiscountManager::preloadProductDataToExtendOrder($arOfferIDs, $userGroups);
						unset($userGroups);
					}
					unset($pricesAllow);
				}
				foreach ($arResult as &$arOffer)
				{
					$arOffer['CATALOG_QUANTITY'] = (
						0 < $arOffer['CATALOG_QUANTITY'] && is_float($arOffer['CATALOG_MEASURE_RATIO'])
						? (float)$arOffer['CATALOG_QUANTITY']
						: (int)$arOffer['CATALOG_QUANTITY']
					);
					$arOffer['MIN_PRICE'] = false;
					$arOffer["PRICES"] = CIBlockPriceTools::GetItemPrices($arOffersIBlock["OFFERS_IBLOCK_ID"], $arPrices, $arOffer, $vat_include, $arCurrencyParams, $USER_ID, $LID);
					if (!empty($arOffer["PRICES"]))
					{
						foreach ($arOffer['PRICES'] as &$arOnePrice)
						{
							if ($arOnePrice['MIN_PRICE'] == 'Y')
							{
								$arOffer['MIN_PRICE'] = $arOnePrice;
								break;
							}
						}
						unset($arOnePrice);
					}
					$arOffer["CAN_BUY"] = CIBlockPriceTools::CanBuy($arOffersIBlock["OFFERS_IBLOCK_ID"], $arPrices, $arOffer);
				}
				if (isset($arOffer))
					unset($arOffer);
			}
			if (!empty($arMeasureMap))
			{
				$rsMeasures = CCatalogMeasure::getList(
					array(),
					array('@ID' => array_keys($arMeasureMap)),
					false,
					false,
					array('ID', 'SYMBOL_RUS')
				);
				while ($arMeasure = $rsMeasures->GetNext())
				{
					$arMeasure['ID'] = (int)$arMeasure['ID'];
					if (isset($arMeasureMap[$arMeasure['ID']]) && !empty($arMeasureMap[$arMeasure['ID']]))
					{
						foreach ($arMeasureMap[$arMeasure['ID']] as $intOneKey)
						{
							$arResult[$intOneKey]['CATALOG_MEASURE_NAME'] = $arMeasure['SYMBOL_RUS'];
							$arResult[$intOneKey]['~CATALOG_MEASURE_NAME'] = $arMeasure['~SYMBOL_RUS'];
						}
						unset($intOneKey);
					}
				}
			}
		}

		return $arResult;
	}

	/**
	 * @deprecated since 14.5.0
	 * @see CCatalogMeasure::getDefaultMeasure
	 *
	 * @return array|null
	 * @throws Main\LoaderException
	 */
	public static function GetDefaultMeasure()
	{
		if (self::$catalogIncluded === null)
			self::$catalogIncluded = Loader::includeModule('catalog');
		return (self::$catalogIncluded ? array() : CCatalogMeasure::getDefaultMeasure(true, true));
	}

	public static function setRatioMinPrice(&$item, $replaceMinPrice = false)
	{
		$replaceMinPrice = ($replaceMinPrice !== false);
		if (isset($item['MIN_PRICE']) && !empty($item['MIN_PRICE']) && isset($item['CATALOG_MEASURE_RATIO']))
		{
			if ($item['CATALOG_MEASURE_RATIO'] === 1)
			{
				$item['RATIO_PRICE'] = array(
					'VALUE' => $item['MIN_PRICE']['VALUE'],
					'DISCOUNT_VALUE' => $item['MIN_PRICE']['DISCOUNT_VALUE'],
					'PRINT_VALUE' => $item['MIN_PRICE']['PRINT_VALUE'],
					'PRINT_DISCOUNT_VALUE' => $item['MIN_PRICE']['PRINT_DISCOUNT_VALUE'],
					'DISCOUNT_DIFF' => $item['MIN_PRICE']['DISCOUNT_DIFF'],
					'PRINT_DISCOUNT_DIFF' => $item['MIN_PRICE']['PRINT_DISCOUNT_DIFF'],
					'DISCOUNT_DIFF_PERCENT' => $item['MIN_PRICE']['DISCOUNT_DIFF_PERCENT'],
					'CURRENCY' => $item['MIN_PRICE']['CURRENCY']
				);
			}
			else
			{
				$item['RATIO_PRICE'] = array(
					'VALUE' => $item['MIN_PRICE']['VALUE']*$item['CATALOG_MEASURE_RATIO'],
					'DISCOUNT_VALUE' => $item['MIN_PRICE']['DISCOUNT_VALUE']*$item['CATALOG_MEASURE_RATIO'],
					'CURRENCY' => $item['MIN_PRICE']['CURRENCY']
				);
				$item['RATIO_PRICE']['PRINT_VALUE'] = CCurrencyLang::CurrencyFormat(
					$item['RATIO_PRICE']['VALUE'],
					$item['RATIO_PRICE']['CURRENCY'],
					true
				);
				$item['RATIO_PRICE']['PRINT_DISCOUNT_VALUE'] = CCurrencyLang::CurrencyFormat(
					$item['RATIO_PRICE']['DISCOUNT_VALUE'],
					$item['RATIO_PRICE']['CURRENCY'],
					true
				);
				if ($item['MIN_PRICE']['VALUE'] == $item['MIN_PRICE']['DISCOUNT_VALUE'])
				{
					$item['RATIO_PRICE']['DISCOUNT_DIFF'] = 0;
					$item['RATIO_PRICE']['DISCOUNT_DIFF_PERCENT'] = 0;
					$item['RATIO_PRICE']['PRINT_DISCOUNT_DIFF'] = CCurrencyLang::CurrencyFormat(0, $item['RATIO_PRICE']['CURRENCY'], true);
				}
				else
				{
					$item['RATIO_PRICE']['DISCOUNT_DIFF'] = $item['RATIO_PRICE']['VALUE'] - $item['RATIO_PRICE']['DISCOUNT_VALUE'];
					$item['RATIO_PRICE']['DISCOUNT_DIFF_PERCENT'] = roundEx(100*$item['RATIO_PRICE']['DISCOUNT_DIFF']/$item['RATIO_PRICE']['VALUE'], 0);
					$item['RATIO_PRICE']['PRINT_DISCOUNT_DIFF'] = CCurrencyLang::CurrencyFormat(
						$item['RATIO_PRICE']['DISCOUNT_DIFF'],
						$item['RATIO_PRICE']['CURRENCY'],
						true
					);
				}
			}
			if ($replaceMinPrice)
			{
				$item['MIN_PRICE'] = $item['RATIO_PRICE'];
				unset($item['RATIO_PRICE']);
			}
		}
	}

	public static function checkPropDirectory(&$property, $getPropInfo = false)
	{
		if (empty($property) || !is_array($property))
			return false;
		if (!isset($property['USER_TYPE_SETTINGS']['TABLE_NAME']) || empty($property['USER_TYPE_SETTINGS']['TABLE_NAME']))
			return false;
		if (self::$highLoadInclude === null)
			self::$highLoadInclude = Loader::includeModule('highloadblock');
		if (!self::$highLoadInclude)
			return false;

		$highBlock = HighloadBlockTable::getList(array(
			'filter' => array('=TABLE_NAME' => $property['USER_TYPE_SETTINGS']['TABLE_NAME'])
		))->fetch();
		if (!isset($highBlock['ID']))
			return false;

		$entity = HighloadBlockTable::compileEntity($highBlock);
		$fieldsList = $entity->getFields();
		if (empty($fieldsList))
			return false;

		$requireFields = array(
			'ID',
			'UF_XML_ID',
			'UF_NAME',
		);
		foreach ($requireFields as &$fieldCode)
		{
			if (!isset($fieldsList[$fieldCode]) || empty($fieldsList[$fieldCode]))
				return false;
		}
		unset($fieldCode);
		if ($getPropInfo)
		{
			$property['USER_TYPE_SETTINGS']['FIELDS_MAP'] = $fieldsList;
			$propInfo['USER_TYPE_SETTINGS']['ENTITY'] = $entity;
		}
		return true;
	}

	public static function getTreeProperties($skuInfo, $propertiesCodes, $defaultFields = array())
	{
		if (isset($defaultFields['PICT']) && is_array($defaultFields['PICT']))
		{
			if (!isset($defaultFields['PICT']['ID']))
				$defaultFields['PICT']['ID'] = 0;
		}

		$requireFields = array(
			'ID',
			'UF_XML_ID',
			'UF_NAME',
		);

		$result = array();
		if (empty($skuInfo))
			return $result;
		if (!is_array($skuInfo))
		{
			$skuInfo = (int)$skuInfo;
			if ($skuInfo <= 0)
				return $result;
			if (self::$catalogIncluded === null)
				self::$catalogIncluded = Loader::includeModule('catalog');
			if (!self::$catalogIncluded)
				return $result;
			$skuInfo = CCatalogSku::GetInfoByProductIBlock($skuInfo);
			if (empty($skuInfo))
				return $result;
		}
		if (empty($propertiesCodes) || !is_array($propertiesCodes))
			return $result;

		$showMode = '';

		$propertyIterator = Iblock\PropertyTable::getList(array(
			'select' => array(
				'ID', 'IBLOCK_ID', 'CODE', 'NAME', 'SORT', 'LINK_IBLOCK_ID', 'PROPERTY_TYPE', 'USER_TYPE', 'USER_TYPE_SETTINGS'
			),
			'filter' => array(
				'=IBLOCK_ID' => $skuInfo['IBLOCK_ID'],
				'=PROPERTY_TYPE' => array(
					Iblock\PropertyTable::TYPE_LIST,
					Iblock\PropertyTable::TYPE_ELEMENT,
					Iblock\PropertyTable::TYPE_STRING
				),
				'=ACTIVE' => 'Y', '=MULTIPLE' => 'N'
			),
			'order' => array(
				'SORT' => 'ASC', 'ID' => 'ASC'
			)
		));
		while ($propInfo = $propertyIterator->fetch())
		{
			$propInfo['ID'] = (int)$propInfo['ID'];
			if ($propInfo['ID'] == $skuInfo['SKU_PROPERTY_ID'])
				continue;
			$propInfo['CODE'] = (string)$propInfo['CODE'];
			if ($propInfo['CODE'] === '')
				$propInfo['CODE'] = $propInfo['ID'];
			if (!in_array($propInfo['CODE'], $propertiesCodes))
				continue;
			$propInfo['SORT'] = (int)$propInfo['SORT'];
			$propInfo['USER_TYPE'] = (string)$propInfo['USER_TYPE'];
			if ($propInfo['PROPERTY_TYPE'] == Iblock\PropertyTable::TYPE_STRING)
			{
				if ('directory' != $propInfo['USER_TYPE'])
					continue;
				$propInfo['USER_TYPE_SETTINGS'] = (string)$propInfo['USER_TYPE_SETTINGS'];
				if ($propInfo['USER_TYPE_SETTINGS'] == '')
					continue;
				$propInfo['USER_TYPE_SETTINGS'] = unserialize($propInfo['USER_TYPE_SETTINGS'], ['allowed_classes' => false]);
				if (!isset($propInfo['USER_TYPE_SETTINGS']['TABLE_NAME']) || empty($propInfo['USER_TYPE_SETTINGS']['TABLE_NAME']))
					continue;
				if (self::$highLoadInclude === null)
					self::$highLoadInclude = Loader::includeModule('highloadblock');
				if (!self::$highLoadInclude)
					continue;

				$highBlock = HighloadBlockTable::getList(array(
					'filter' => array('=TABLE_NAME' => $propInfo['USER_TYPE_SETTINGS']['TABLE_NAME'])
				))->fetch();
				if (!isset($highBlock['ID']))
					continue;

				$entity = HighloadBlockTable::compileEntity($highBlock);
				$fieldsList = $entity->getFields();
				if (empty($fieldsList))
					continue;

				$flag = true;
				foreach ($requireFields as $fieldCode)
				{
					if (!isset($fieldsList[$fieldCode]) || empty($fieldsList[$fieldCode]))
					{
						$flag = false;
						break;
					}
				}
				unset($fieldCode);
				if (!$flag)
					continue;
				$propInfo['USER_TYPE_SETTINGS']['FIELDS_MAP'] = $fieldsList;
				$propInfo['USER_TYPE_SETTINGS']['ENTITY'] = $entity;
			}
			switch ($propInfo['PROPERTY_TYPE'])
			{
				case Iblock\PropertyTable::TYPE_ELEMENT:
					$showMode = 'PICT';
					break;
				case Iblock\PropertyTable::TYPE_LIST:
					$showMode = 'TEXT';
					break;
				case Iblock\PropertyTable::TYPE_STRING:
					$showMode = (isset($fieldsList['UF_FILE']) ? 'PICT' : 'TEXT');
					break;
			}
			$treeProp = array(
				'ID' => $propInfo['ID'],
				'CODE' => $propInfo['CODE'],
				'NAME' => $propInfo['NAME'],
				'SORT' => $propInfo['SORT'],
				'PROPERTY_TYPE' => $propInfo['PROPERTY_TYPE'],
				'USER_TYPE' => $propInfo['USER_TYPE'],
				'LINK_IBLOCK_ID' => $propInfo['LINK_IBLOCK_ID'],
				'USER_TYPE_SETTINGS' => $propInfo['USER_TYPE_SETTINGS'],
				'VALUES' => array(),
				'SHOW_MODE' => $showMode,
				'DEFAULT_VALUES' => array(
					'PICT' => false,
					'NAME' => '-'
				)
			);
			if ($showMode == 'PICT')
			{
				if (isset($defaultFields['PICT']))
					$treeProp['DEFAULT_VALUES']['PICT'] = $defaultFields['PICT'];
			}
			if (isset($defaultFields['NAME']))
			{
				$treeProp['DEFAULT_VALUES']['NAME'] = $defaultFields['NAME'];
			}
			$result[$treeProp['CODE']] = $treeProp;
		}
		return $result;
	}

	public static function getTreePropertyValues(&$propList, &$propNeedValues)
	{
		$result = array();
		if (!empty($propList) && is_array($propList))
		{
			$useFilterValues = !empty($propNeedValues) && is_array($propNeedValues);
			foreach ($propList as $oneProperty)
			{
				if (isset($oneProperty['DEFAULT_VALUES']['PICT']) && is_array($oneProperty['DEFAULT_VALUES']['PICT']))
				{
					if (!isset($oneProperty['DEFAULT_VALUES']['PICT']['ID']))
						$oneProperty['DEFAULT_VALUES']['PICT']['ID'] = 0;
				}
				$values = array();
				$valuesExist = false;
				$pictMode = ('PICT' == $oneProperty['SHOW_MODE']);
				$needValuesExist = !empty($propNeedValues[$oneProperty['ID']]) && is_array($propNeedValues[$oneProperty['ID']]);
				if ($useFilterValues && !$needValuesExist)
					continue;
				switch($oneProperty['PROPERTY_TYPE'])
				{
					case Iblock\PropertyTable::TYPE_LIST:
						if ($needValuesExist)
						{
							foreach (array_chunk($propNeedValues[$oneProperty['ID']], 500) as $pageIds)
							{
								$iterator = Iblock\PropertyEnumerationTable::getList(array(
									'select' => array('ID', 'VALUE', 'SORT'),
									'filter' => array('=PROPERTY_ID' => $oneProperty['ID'], '@ID' => $pageIds),
									'order' => array('SORT' => 'ASC', 'VALUE' => 'ASC')
								));
								while ($row = $iterator->fetch())
								{
									$row['ID'] = (int)$row['ID'];
									$values[$row['ID']] = array(
										'ID' => $row['ID'],
										'NAME' => $row['VALUE'],
										'SORT' => (int)$row['SORT'],
										'PICT' => false
									);
									$valuesExist = true;
								}
								unset($row, $iterator);
							}
							unset($pageIds);
						}
						else
						{
							$iterator = Iblock\PropertyEnumerationTable::getList(array(
								'select' => array('ID', 'VALUE', 'SORT'),
								'filter' => array('=PROPERTY_ID' => $oneProperty['ID']),
								'order' => array('SORT' => 'ASC', 'VALUE' => 'ASC')
							));
							while ($row = $iterator->fetch())
							{
								$row['ID'] = (int)$row['ID'];
								$values[$row['ID']] = array(
									'ID' => $row['ID'],
									'NAME' => $row['VALUE'],
									'SORT' => (int)$row['SORT'],
									'PICT' => false
								);
								$valuesExist = true;
							}
							unset($row, $iterator);
						}
						$values[0] = array(
							'ID' => 0,
							'SORT' => PHP_INT_MAX,
							'NA' => true,
							'NAME' => $oneProperty['DEFAULT_VALUES']['NAME'],
							'PICT' => $oneProperty['DEFAULT_VALUES']['PICT']
						);
						break;
					case Iblock\PropertyTable::TYPE_ELEMENT:
						$selectFields = array('ID', 'NAME');
						if ($pictMode)
							$selectFields[] = 'PREVIEW_PICTURE';

						if ($needValuesExist)
						{
							foreach (array_chunk($propNeedValues[$oneProperty['ID']], 500) as $pageIds)
							{
								$iterator =  CIBlockElement::GetList(
									array('SORT' => 'ASC', 'NAME' => 'ASC'),
									array('ID' => $pageIds, 'IBLOCK_ID' => $oneProperty['LINK_IBLOCK_ID'], 'ACTIVE' => 'Y'),
									false,
									false,
									$selectFields
								);
								while ($row = $iterator->Fetch())
								{
									if ($pictMode)
									{
										$row['PICT'] = false;
										if (!empty($row['PREVIEW_PICTURE']))
										{
											$previewPict = CFile::GetFileArray($row['PREVIEW_PICTURE']);
											if (!empty($previewPict))
											{
												$row['PICT'] = array(
													'ID' => (int)$previewPict['ID'],
													'SRC' => $previewPict['SRC'],
													'WIDTH' => (int)$previewPict['WIDTH'],
													'HEIGHT' => (int)$previewPict['HEIGHT']
												);
											}
										}
										if (empty($row['PICT']))
											$row['PICT'] = $oneProperty['DEFAULT_VALUES']['PICT'];
									}
									$row['ID'] = (int)$row['ID'];
									$values[$row['ID']] = array(
										'ID' => $row['ID'],
										'NAME' => $row['NAME'],
										'SORT' => (int)$row['SORT'],
										'PICT' => ($pictMode ? $row['PICT'] : false)
									);
									$valuesExist = true;
								}
								unset($row, $iterator);
							}
							unset($pageIds);
						}
						else
						{
							$iterator =  CIBlockElement::GetList(
								array('SORT' => 'ASC', 'NAME' => 'ASC'),
								array('IBLOCK_ID' => $oneProperty['LINK_IBLOCK_ID'], 'ACTIVE' => 'Y'),
								false,
								false,
								$selectFields
							);
							while ($row = $iterator->Fetch())
							{
								if ($pictMode)
								{
									$row['PICT'] = false;
									if (!empty($row['PREVIEW_PICTURE']))
									{
										$previewPict = CFile::GetFileArray($row['PREVIEW_PICTURE']);
										if (!empty($previewPict))
										{
											$row['PICT'] = array(
												'ID' => (int)$previewPict['ID'],
												'SRC' => $previewPict['SRC'],
												'WIDTH' => (int)$previewPict['WIDTH'],
												'HEIGHT' => (int)$previewPict['HEIGHT']
											);
										}
									}
									if (empty($row['PICT']))
										$row['PICT'] = $oneProperty['DEFAULT_VALUES']['PICT'];
								}
								$row['ID'] = (int)$row['ID'];
								$values[$row['ID']] = array(
									'ID' => $row['ID'],
									'NAME' => $row['NAME'],
									'SORT' => (int)$row['SORT'],
									'PICT' => ($pictMode ? $row['PICT'] : false)
								);
								$valuesExist = true;
							}
							unset($row, $iterator);
						}
						$values[0] = array(
							'ID' => 0,
							'SORT' => PHP_INT_MAX,
							'NA' => true,
							'NAME' => $oneProperty['DEFAULT_VALUES']['NAME'],
							'PICT' => ($pictMode ? $oneProperty['DEFAULT_VALUES']['PICT'] : false)
						);
						break;
					case Iblock\PropertyTable::TYPE_STRING:
						if (self::$highLoadInclude === null)
							self::$highLoadInclude = Loader::includeModule('highloadblock');
						if (!self::$highLoadInclude)
							continue 2;
						$xmlMap = array();
						$sortExist = isset($oneProperty['USER_TYPE_SETTINGS']['FIELDS_MAP']['UF_SORT']);

						$directorySelect = array('ID', 'UF_NAME', 'UF_XML_ID');
						$directoryOrder = array();
						if ($pictMode)
							$directorySelect[] = 'UF_FILE';
						if ($sortExist)
						{
							$directorySelect[] = 'UF_SORT';
							$directoryOrder['UF_SORT'] = 'ASC';
						}
						$directoryOrder['UF_NAME'] = 'ASC';
						$sortValue = 100;

						/** @var Main\Entity\Base $entity */
						$entity = $oneProperty['USER_TYPE_SETTINGS']['ENTITY'];
						if (!($entity instanceof Main\Entity\Base))
							continue 2;
						$entityDataClass = $entity->getDataClass();
						$entityGetList = array(
							'select' => $directorySelect,
							'order' => $directoryOrder
						);

						if ($needValuesExist)
						{
							foreach (array_chunk($propNeedValues[$oneProperty['ID']], 500) as $pageIds)
							{
								$entityGetList['filter'] = array('=UF_XML_ID' => $pageIds);
								$iterator = $entityDataClass::getList($entityGetList);
								while ($row = $iterator->fetch())
								{
									$row['ID'] = (int)$row['ID'];
									$row['UF_SORT'] = ($sortExist ? (int)$row['UF_SORT'] : $sortValue);
									$sortValue += 100;

									if ($pictMode)
									{
										if (!empty($row['UF_FILE']))
										{
											$arFile = CFile::GetFileArray($row['UF_FILE']);
											if (!empty($arFile))
											{
												$row['PICT'] = array(
													'ID' => (int)$arFile['ID'],
													'SRC' => $arFile['SRC'],
													'WIDTH' => (int)$arFile['WIDTH'],
													'HEIGHT' => (int)$arFile['HEIGHT']
												);
											}
										}
										if (empty($row['PICT']))
											$row['PICT'] = $oneProperty['DEFAULT_VALUES']['PICT'];
									}
									$values[$row['ID']] = array(
										'ID' => $row['ID'],
										'NAME' => $row['UF_NAME'],
										'SORT' => (int)$row['UF_SORT'],
										'XML_ID' => $row['UF_XML_ID'],
										'PICT' => ($pictMode ? $row['PICT'] : false)
									);
									$valuesExist = true;
									$xmlMap[$row['UF_XML_ID']] = $row['ID'];
								}
								unset($row, $iterator);
							}
							unset($pageIds);
						}
						else
						{
							$iterator = $entityDataClass::getList($entityGetList);
							while ($row = $iterator->fetch())
							{
								$row['ID'] = (int)$row['ID'];
								$row['UF_SORT'] = ($sortExist ? (int)$row['UF_SORT'] : $sortValue);
								$sortValue += 100;

								if ($pictMode)
								{
									if (!empty($row['UF_FILE']))
									{
										$arFile = CFile::GetFileArray($row['UF_FILE']);
										if (!empty($arFile))
										{
											$row['PICT'] = array(
												'ID' => (int)$arFile['ID'],
												'SRC' => $arFile['SRC'],
												'WIDTH' => (int)$arFile['WIDTH'],
												'HEIGHT' => (int)$arFile['HEIGHT']
											);
										}
									}
									if (empty($row['PICT']))
										$row['PICT'] = $oneProperty['DEFAULT_VALUES']['PICT'];
								}
								$values[$row['ID']] = array(
									'ID' => $row['ID'],
									'NAME' => $row['UF_NAME'],
									'SORT' => (int)$row['UF_SORT'],
									'XML_ID' => $row['UF_XML_ID'],
									'PICT' => ($pictMode ? $row['PICT'] : false)
								);
								$valuesExist = true;
								$xmlMap[$row['UF_XML_ID']] = $row['ID'];
							}
							unset($row, $iterator);
						}
						$values[0] = array(
							'ID' => 0,
							'SORT' => PHP_INT_MAX,
							'NA' => true,
							'NAME' => $oneProperty['DEFAULT_VALUES']['NAME'],
							'XML_ID' => '',
							'PICT' => ($pictMode ? $oneProperty['DEFAULT_VALUES']['PICT'] : false)
						);
						if ($valuesExist)
							$oneProperty['XML_MAP'] = $xmlMap;
					break;
				}
				if (!$valuesExist)
					continue;
				$oneProperty['VALUES'] = $values;
				$oneProperty['VALUES_COUNT'] = count($values);

				$result[$oneProperty['CODE']] = $oneProperty;
			}
		}
		$propList = $result;
		unset($arFilterProp);
	}

	public static function getMinPriceFromOffers(&$offers, $currency, $replaceMinPrice = true)
	{
		$replaceMinPrice = ($replaceMinPrice === true);
		$result = false;
		$minPrice = 0;
		if (!empty($offers) && is_array($offers))
		{
			$doubles = array();
			foreach ($offers as $oneOffer)
			{
				$oneOffer['ID'] = (int)$oneOffer['ID'];
				if (isset($doubles[$oneOffer['ID']]))
					continue;
				if (!$oneOffer['CAN_BUY'])
					continue;

				CIBlockPriceTools::setRatioMinPrice($oneOffer, $replaceMinPrice);

				$oneOffer['MIN_PRICE']['CATALOG_MEASURE_RATIO'] = $oneOffer['CATALOG_MEASURE_RATIO'];
				$oneOffer['MIN_PRICE']['CATALOG_MEASURE'] = $oneOffer['CATALOG_MEASURE'];
				$oneOffer['MIN_PRICE']['CATALOG_MEASURE_NAME'] = $oneOffer['CATALOG_MEASURE_NAME'];
				$oneOffer['MIN_PRICE']['~CATALOG_MEASURE_NAME'] = $oneOffer['~CATALOG_MEASURE_NAME'];

				if (empty($result))
				{
					$minPrice = ($oneOffer['MIN_PRICE']['CURRENCY'] == $currency
						? $oneOffer['MIN_PRICE']['DISCOUNT_VALUE']
						: CCurrencyRates::ConvertCurrency($oneOffer['MIN_PRICE']['DISCOUNT_VALUE'], $oneOffer['MIN_PRICE']['CURRENCY'], $currency)
					);
					$result = $oneOffer['MIN_PRICE'];
				}
				else
				{
					$comparePrice = ($oneOffer['MIN_PRICE']['CURRENCY'] == $currency
						? $oneOffer['MIN_PRICE']['DISCOUNT_VALUE']
						: CCurrencyRates::ConvertCurrency($oneOffer['MIN_PRICE']['DISCOUNT_VALUE'], $oneOffer['MIN_PRICE']['CURRENCY'], $currency)
					);
					if ($minPrice > $comparePrice)
					{
						$minPrice = $comparePrice;
						$result = $oneOffer['MIN_PRICE'];
					}
				}
				$doubles[$oneOffer['ID']] = true;
			}
		}
		return $result;
	}

	public static function getDoublePicturesForItem(&$item, $propertyCode, $encode = true)
	{
		$encode = ($encode === true);
		$result = array(
			'PICT' => false,
			'SECOND_PICT' => false
		);

		if (!empty($item) && is_array($item))
		{
			if (!empty($item['PREVIEW_PICTURE']))
			{
				if (!is_array($item['PREVIEW_PICTURE']))
					$item['PREVIEW_PICTURE'] = CFile::GetFileArray($item['PREVIEW_PICTURE']);
				if (isset($item['PREVIEW_PICTURE']['ID']))
				{
					$result['PICT'] = array(
						'ID' => (int)$item['PREVIEW_PICTURE']['ID'],
						'SRC' => Iblock\Component\Tools::getImageSrc($item['PREVIEW_PICTURE'], $encode),
						'WIDTH' => (int)$item['PREVIEW_PICTURE']['WIDTH'],
						'HEIGHT' => (int)$item['PREVIEW_PICTURE']['HEIGHT']
					);
				}
			}
			if (!empty($item['DETAIL_PICTURE']))
			{
				$keyPict = (empty($result['PICT']) ? 'PICT' : 'SECOND_PICT');
				if (!is_array($item['DETAIL_PICTURE']))
					$item['DETAIL_PICTURE'] = CFile::GetFileArray($item['DETAIL_PICTURE']);
				if (isset($item['DETAIL_PICTURE']['ID']))
				{
					$result[$keyPict] = array(
						'ID' => (int)$item['DETAIL_PICTURE']['ID'],
						'SRC' => Iblock\Component\Tools::getImageSrc($item['DETAIL_PICTURE'], $encode),
						'WIDTH' => (int)$item['DETAIL_PICTURE']['WIDTH'],
						'HEIGHT' => (int)$item['DETAIL_PICTURE']['HEIGHT']
					);
				}
			}
			if (empty($result['SECOND_PICT']))
			{
				if (
					'' != $propertyCode &&
					isset($item['PROPERTIES'][$propertyCode]) &&
					'F' == $item['PROPERTIES'][$propertyCode]['PROPERTY_TYPE']
				)
				{
					if (
						isset($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']) &&
						!empty($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE'])
					)
					{
						$fileValues = (
							isset($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']['ID']) ?
							array(0 => $item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']) :
							$item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']
						);
						foreach ($fileValues as $oneFileValue)
						{
							$keyPict = (empty($result['PICT']) ? 'PICT' : 'SECOND_PICT');
							$result[$keyPict] = array(
								'ID' => (int)$oneFileValue['ID'],
								'SRC' => Iblock\Component\Tools::getImageSrc($oneFileValue, $encode),
								'WIDTH' => (int)$oneFileValue['WIDTH'],
								'HEIGHT' => (int)$oneFileValue['HEIGHT']
							);
							if ('SECOND_PICT' == $keyPict)
								break;
						}
						if (isset($oneFileValue))
							unset($oneFileValue);
					}
					else
					{
						$propValues = $item['PROPERTIES'][$propertyCode]['VALUE'];
						if (!is_array($propValues))
							$propValues = array($propValues);
						foreach ($propValues as $oneValue)
						{
							$oneFileValue = CFile::GetFileArray($oneValue);
							if (isset($oneFileValue['ID']))
							{
								$keyPict = (empty($result['PICT']) ? 'PICT' : 'SECOND_PICT');
								$result[$keyPict] = array(
									'ID' => (int)$oneFileValue['ID'],
									'SRC' => Iblock\Component\Tools::getImageSrc($oneFileValue, $encode),
									'WIDTH' => (int)$oneFileValue['WIDTH'],
									'HEIGHT' => (int)$oneFileValue['HEIGHT']
								);
								if ('SECOND_PICT' == $keyPict)
									break;
							}
						}
						if (isset($oneValue))
							unset($oneValue);
					}
				}
			}
		}
		return $result;
	}

	public static function getSliderForItem(&$item, $propertyCode, $addDetailToSlider, $encode = true)
	{
		$encode = ($encode === true);
		$result = array();

		if (!empty($item) && is_array($item))
		{
			if (
				'' != $propertyCode &&
				isset($item['PROPERTIES'][$propertyCode]) &&
				'F' == $item['PROPERTIES'][$propertyCode]['PROPERTY_TYPE']
			)
			{
				if ('MORE_PHOTO' == $propertyCode && isset($item['MORE_PHOTO']) && !empty($item['MORE_PHOTO']))
				{
					foreach ($item['MORE_PHOTO'] as $onePhoto)
					{
						$result[] = array(
							'ID' => (int)$onePhoto['ID'],
							'SRC' => Iblock\Component\Tools::getImageSrc($onePhoto, $encode),
							'WIDTH' => (int)$onePhoto['WIDTH'],
							'HEIGHT' => (int)$onePhoto['HEIGHT']
						);
					}
					unset($onePhoto);
				}
				else
				{
					if (
						isset($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']) &&
						!empty($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE'])
					)
					{
						$fileValues = (
							isset($item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']['ID']) ?
							array(0 => $item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']) :
							$item['DISPLAY_PROPERTIES'][$propertyCode]['FILE_VALUE']
						);
						foreach ($fileValues as $oneFileValue)
						{
							$result[] = array(
								'ID' => (int)$oneFileValue['ID'],
								'SRC' => Iblock\Component\Tools::getImageSrc($oneFileValue, $encode),
								'WIDTH' => (int)$oneFileValue['WIDTH'],
								'HEIGHT' => (int)$oneFileValue['HEIGHT']
							);
						}
						if (isset($oneFileValue))
							unset($oneFileValue);
					}
					else
					{
						$propValues = $item['PROPERTIES'][$propertyCode]['VALUE'];
						if (!is_array($propValues))
							$propValues = array($propValues);

						foreach ($propValues as $oneValue)
						{
							$oneFileValue = CFile::GetFileArray($oneValue);
							if (isset($oneFileValue['ID']))
							{
								$result[] = array(
									'ID' => (int)$oneFileValue['ID'],
									'SRC' => Iblock\Component\Tools::getImageSrc($oneFileValue, $encode),
									'WIDTH' => (int)$oneFileValue['WIDTH'],
									'HEIGHT' => (int)$oneFileValue['HEIGHT']
								);
							}
						}
						if (isset($oneValue))
							unset($oneValue);
					}
				}
			}
			if ($addDetailToSlider || empty($result))
			{
				if (!empty($item['DETAIL_PICTURE']))
				{
					if (!is_array($item['DETAIL_PICTURE']))
						$item['DETAIL_PICTURE'] = CFile::GetFileArray($item['DETAIL_PICTURE']);
					if (isset($item['DETAIL_PICTURE']['ID']))
					{
						array_unshift(
							$result,
							array(
								'ID' => (int)$item['DETAIL_PICTURE']['ID'],
								'SRC' => Iblock\Component\Tools::getImageSrc($item['DETAIL_PICTURE'], $encode),
								'WIDTH' => (int)$item['DETAIL_PICTURE']['WIDTH'],
								'HEIGHT' => (int)$item['DETAIL_PICTURE']['HEIGHT']
							)
						);
					}
				}
			}
		}
		return $result;
	}

	public static function getLabel(&$item, $propertyCode)
	{
		static $propertyEnum = array();

		if (!empty($item) && is_array($item))
		{
			$item['LABEL'] = false;
			$item['LABEL_VALUE'] = '';
			$item['LABEL_ARRAY_VALUE'] = array();

			if (!is_array($propertyCode))
			{
				$propertyCode = array($propertyCode);
			}

			if (!empty($propertyCode))
			{
				foreach ($propertyCode as $index => $code)
				{
					$code = (string)$code;

					if ($code !== '' && isset($item['PROPERTIES'][$code]))
					{
						$prop = $item['PROPERTIES'][$code];

						if (!empty($prop['VALUE']))
						{
							$useName = false;

							if ($prop['PROPERTY_TYPE'] == 'L' && $prop['MULTIPLE'] == 'N')
							{
								if (!isset($propertyEnum[$prop['ID']]))
								{
									$count = 0;
									$enumList = CIBlockPropertyEnum::GetList(
										array(),
										array('PROPERTY_ID' => $prop['ID'])
									);
									while ($enum = $enumList->Fetch())
									{
										$count++;
									}

									$propertyEnum[$prop['ID']] = $count;
									unset($enum, $enumList, $count);
								}

								$useName = ($propertyEnum[$prop['ID']] == 1);
							}

							if ($useName)
							{
								$item['LABEL_ARRAY_VALUE'][$code] = $prop['NAME'];
							}
							else
							{
								$item['LABEL_ARRAY_VALUE'][$code] = (is_array($prop['VALUE'])
									? implode(' / ', $prop['VALUE'])
									: $prop['VALUE']
								);
							}

							unset($useName);
							$item['LABEL'] = true;

							if ($item['LABEL_VALUE'] === '')
							{
								$item['LABEL_VALUE'] = $item['LABEL_ARRAY_VALUE'][$code];
							}

							if (isset($item['DISPLAY_PROPERTIES'][$code]))
							{
								unset($item['DISPLAY_PROPERTIES'][$code]);
							}
						}
						unset($prop);
					}
				}
			}
		}
	}

	public static function clearProperties(&$properties, $clearCodes)
	{
		if (!empty($properties) && is_array($properties) && !empty($clearCodes))
		{
			if (!is_array($clearCodes))
				$clearCodes = array($clearCodes);

			foreach ($clearCodes as $oneCode)
			{
				if (isset($properties[$oneCode]))
					unset($properties[$oneCode]);
			}
			unset($oneCode);
		}
		return !empty($properties);
	}

	public static function getMinPriceFromList($priceList)
	{
		if (empty($priceList) || !is_array($priceList))
			return false;
		$result = false;
		foreach ($priceList as $price)
		{
			if (isset($price['MIN_PRICE']) && $price['MIN_PRICE'] == 'Y')
			{
				$result = $price;
				break;
			}
		}
		unset($price);
		return $result;
	}

	public static function isEnabledCalculationDiscounts()
	{
		return (self::$calculationDiscounts >= 0);
	}

	public static function enableCalculationDiscounts()
	{
		self::$calculationDiscounts++;
	}

	public static function disableCalculationDiscounts()
	{
		self::$calculationDiscounts--;
	}
}