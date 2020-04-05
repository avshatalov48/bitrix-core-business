<?
use Bitrix\Main\Loader;
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixCatalogSmartFilter $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

if(!Loader::includeModule('iblock'))
{
	ShowError(GetMessage("CC_BCF_MODULE_NOT_INSTALLED"));
	return;
}

$FILTER_NAME = (string)$arParams["FILTER_NAME"];
$PREFILTER_NAME = (string)$arParams["PREFILTER_NAME"];

global ${$PREFILTER_NAME};
$preFilter = ${$PREFILTER_NAME};
if (!is_array($preFilter))
	$preFilter = array();

if($this->StartResultCache(false, array('v10', $preFilter, ($arParams["CACHE_GROUPS"]? $USER->GetGroups(): false))))
{
	$arResult["FACET_FILTER"] = false;
	$arResult["COMBO"] = array();
	$arResult["PRICES"] = CIBlockPriceTools::GetCatalogPrices($arParams["IBLOCK_ID"], $arParams["PRICE_CODE"]);
	$arResult["ITEMS"] = $this->getResultItems();
	$arResult["CURRENCIES"] = array();

	$propertyEmptyValuesCombination = array();
	foreach($arResult["ITEMS"] as $PID => $arItem)
		$propertyEmptyValuesCombination[$arItem["ID"]] = array();

	if(!empty($arResult["ITEMS"]))
	{
		if ($this->facet->isValid())
		{
			$this->facet->setPrices($arResult["PRICES"]);
			$this->facet->setSectionId($this->SECTION_ID);
			$arResult["FACET_FILTER"] = array(
				"ACTIVE_DATE" => "Y",
				"CHECK_PERMISSIONS" => "Y",
			);
			if ($this->arParams['HIDE_NOT_AVAILABLE'] == 'Y')
				$arResult["FACET_FILTER"]['AVAILABLE'] = 'Y';
			if (!empty($preFilter))
				$arResult["FACET_FILTER"] = array_merge($preFilter, $arResult["FACET_FILTER"]);

			$cntProperty = 0;
			$tmpProperty = array();
			$dictionaryID = array();
			$elementDictionary = array();
			$sectionDictionary = array();
			$directoryPredict = array();

			$res = $this->facet->query($arResult["FACET_FILTER"]);
			CTimeZone::Disable();
			while ($rowData = $res->fetch())
			{
				$facetId = $rowData["FACET_ID"];
				if (\Bitrix\Iblock\PropertyIndex\Storage::isPropertyId($facetId))
				{
					$PID = \Bitrix\Iblock\PropertyIndex\Storage::facetIdToPropertyId($facetId);
					if (!array_key_exists($PID, $arResult["ITEMS"]))
						continue;
					++$cntProperty;

					$rowData['PID'] = $PID;
					$tmpProperty[] = $rowData;
					$item = $arResult["ITEMS"][$PID];
					$arUserType = CIBlockProperty::GetUserType($item['USER_TYPE']);

					if ($item["PROPERTY_TYPE"] == "S")
					{
						$dictionaryID[] = $rowData["VALUE"];
					}

					if ($item["PROPERTY_TYPE"] == "E" && $item['USER_TYPE'] == '')
					{
						$elementDictionary[] = $rowData['VALUE'];
					}

					if ($item["PROPERTY_TYPE"] == "G" && $item['USER_TYPE'] == '')
					{
						$sectionDictionary[] = $rowData['VALUE'];
					}

					if ($item['USER_TYPE'] == 'directory' && isset($arUserType['GetExtendedValue']))
					{
						$tableName = $item['USER_TYPE_SETTINGS']['TABLE_NAME'];
						$directoryPredict[$tableName]['PROPERTY'] = array(
							'PID' => $item['ID'],
							'USER_TYPE_SETTINGS' => $item['USER_TYPE_SETTINGS'],
							'GetExtendedValue' => $arUserType['GetExtendedValue'],
						);
						$directoryPredict[$tableName]['VALUE'][] = $rowData["VALUE"];
					}
				}
				else
				{
					$priceId = \Bitrix\Iblock\PropertyIndex\Storage::facetIdToPriceId($facetId);
					foreach($arResult["PRICES"] as $NAME => $arPrice)
					{
						if ($arPrice["ID"] == $priceId && isset($arResult["ITEMS"][$NAME]))
						{
							$this->fillItemPrices($arResult["ITEMS"][$NAME], $rowData);

							if (isset($arResult["ITEMS"][$NAME]["~CURRENCIES"]))
							{
								$arResult["CURRENCIES"] += $arResult["ITEMS"][$NAME]["~CURRENCIES"];
							}

							if ($rowData["VALUE_FRAC_LEN"] > 0)
							{
								$arResult["ITEMS"][$PID]["DECIMALS"] = $rowData["VALUE_FRAC_LEN"];
							}
						}
					}
				}

				if ($cntProperty > 200)
				{
					$this->predictIBElementFetch($elementDictionary);
					$this->predictIBSectionFetch($sectionDictionary);
					$this->processProperties($arResult, $tmpProperty, $dictionaryID, $directoryPredict);
					$cntProperty = 0;
					$tmpProperty = array();
					$dictionaryID = array();
					$lookupDictionary = array();
					$directoryPredict = array();
					$elementDictionary = array();
					$sectionDictionary = array();
				}
			}

			$this->predictIBElementFetch($elementDictionary);
			$this->predictIBSectionFetch($sectionDictionary);
			$this->processProperties($arResult, $tmpProperty, $dictionaryID, $directoryPredict);
			CTimeZone::Enable();
		}
		else
		{
			$arElementFilter = array(
				"IBLOCK_ID" => $this->IBLOCK_ID,
				"SUBSECTION" => $this->SECTION_ID,
				"SECTION_SCOPE" => "IBLOCK",
				"ACTIVE_DATE" => "Y",
				"ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "Y",
			);
			if ('Y' == $this->arParams['HIDE_NOT_AVAILABLE'])
				$arElementFilter['AVAILABLE'] = 'Y';
			if (!empty($preFilter))
				$arElementFilter = array_merge($preFilter, $arElementFilter);

			$arElements = array();

			if (!empty($this->arResult["PROPERTY_ID_LIST"]))
			{
				$rsElements = CIBlockElement::GetPropertyValues($this->IBLOCK_ID, $arElementFilter, false, array('ID' => $this->arResult["PROPERTY_ID_LIST"]));
				while($arElement = $rsElements->Fetch())
					$arElements[$arElement["IBLOCK_ELEMENT_ID"]] = $arElement;
			}
			else
			{
				$rsElements = CIBlockElement::GetList(array('ID' => 'ASC'), $arElementFilter, false, false, array('ID', 'IBLOCK_ID'));
				while($arElement = $rsElements->Fetch())
					$arElements[$arElement["ID"]] = array();
			}

			if (!empty($arElements) && $this->SKU_IBLOCK_ID && $arResult["SKU_PROPERTY_COUNT"] > 0)
			{
				$arSkuFilter = array(
					"IBLOCK_ID" => $this->SKU_IBLOCK_ID,
					"ACTIVE_DATE" => "Y",
					"ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y",
					"=PROPERTY_".$this->SKU_PROPERTY_ID => array_keys($arElements),
				);
				if ($this->arParams['HIDE_NOT_AVAILABLE'] == 'Y')
					$arSkuFilter['AVAILABLE'] = 'Y';

				$rsElements = CIBlockElement::GetPropertyValues($this->SKU_IBLOCK_ID, $arSkuFilter, false, array('ID' => $this->arResult["SKU_PROPERTY_ID_LIST"]));
				while($arSku = $rsElements->Fetch())
				{
					foreach($arResult["ITEMS"] as $PID => $arItem)
					{
						if (isset($arSku[$PID]) && $arSku[$this->SKU_PROPERTY_ID] > 0)
						{
							if (is_array($arSku[$PID]))
							{
								foreach($arSku[$PID] as $value)
									$arElements[$arSku[$this->SKU_PROPERTY_ID]][$PID][] = $value;
							}
							else
							{
								$arElements[$arSku[$this->SKU_PROPERTY_ID]][$PID][] = $arSku[$PID];
							}
						}
					}
				}
			}

			CTimeZone::Disable();
			$uniqTest = array();
			foreach($arElements as $arElement)
			{
				$propertyValues = $propertyEmptyValuesCombination;
				$uniqStr = '';
				foreach($arResult["ITEMS"] as $PID => $arItem)
				{
					if (is_array($arElement[$PID]))
					{
						foreach($arElement[$PID] as $value)
						{
							$key = $this->fillItemValues($arResult["ITEMS"][$PID], $value);
							$propertyValues[$PID][$key] = $arResult["ITEMS"][$PID]["VALUES"][$key]["VALUE"];
							$uniqStr .= '|'.$key.'|'.$propertyValues[$PID][$key];
						}
					}
					elseif ($arElement[$PID] !== false)
					{
						$key = $this->fillItemValues($arResult["ITEMS"][$PID], $arElement[$PID]);
						$propertyValues[$PID][$key] = $arResult["ITEMS"][$PID]["VALUES"][$key]["VALUE"];
						$uniqStr .= '|'.$key.'|'.$propertyValues[$PID][$key];
					}
				}

				$uniqCheck = md5($uniqStr);
				if (isset($uniqTest[$uniqCheck]))
					continue;
				$uniqTest[$uniqCheck] = true;

				$this->ArrayMultiply($arResult["COMBO"], $propertyValues);
			}
			CTimeZone::Enable();

			$arSelect = array("ID", "IBLOCK_ID");
			foreach($arResult["PRICES"] as &$value)
			{
				if (!$value['CAN_VIEW'] && !$value['CAN_BUY'])
					continue;
				$arSelect = array_merge($arSelect, $value["SELECT_EXTENDED"]);
				$arElementFilter["DEFAULT_PRICE_FILTER_".$value["ID"]] = 1;
				if (isset($arSkuFilter))
					$arSkuFilter["DEFAULT_PRICE_FILTER_".$value["ID"]] = 1;
			}
			unset($value);

			$rsElements = CIBlockElement::GetList(array(), $arElementFilter, false, false, $arSelect);
			while($arElement = $rsElements->Fetch())
			{
				foreach($arResult["PRICES"] as $NAME => $arPrice)
					if(isset($arResult["ITEMS"][$NAME]))
						$this->fillItemPrices($arResult["ITEMS"][$NAME], $arElement);
			}

			if (isset($arSkuFilter))
			{
				$rsElements = CIBlockElement::GetList(array(), $arSkuFilter, false, false, $arSelect);
				while($arSku = $rsElements->Fetch())
				{
					foreach($arResult["PRICES"] as $NAME => $arPrice)
						if(isset($arResult["ITEMS"][$NAME]))
							$this->fillItemPrices($arResult["ITEMS"][$NAME], $arSku);
				}
			}
		}

		foreach($arResult["ITEMS"] as $PID => $arItem)
			uasort($arResult["ITEMS"][$PID]["VALUES"], array($this, "_sort"));
	}

	if ($arParams["XML_EXPORT"] === "Y")
	{
		$arResult["SECTION_TITLE"] = "";
		$arResult["SECTION_DESCRIPTION"] = "";

		if ($this->SECTION_ID > 0)
		{
			$arSelect = array("ID", "IBLOCK_ID", "LEFT_MARGIN", "RIGHT_MARGIN");
			if ($arParams["SECTION_TITLE"] !== "")
				$arSelect[] = $arParams["SECTION_TITLE"];
			if ($arParams["SECTION_DESCRIPTION"] !== "")
				$arSelect[] = $arParams["SECTION_DESCRIPTION"];

			$sectionList = CIBlockSection::GetList(array(), array(
				"=ID" => $this->SECTION_ID,
				"IBLOCK_ID" => $this->IBLOCK_ID,
			), false, $arSelect);
			$arResult["SECTION"] = $sectionList->GetNext();

			if ($arResult["SECTION"])
			{
				$arResult["SECTION_TITLE"] = $arResult["SECTION"][$arParams["SECTION_TITLE"]];
				if ($arParams["SECTION_DESCRIPTION"] !== "")
				{
					$obParser = new CTextParser;
					$arResult["SECTION_DESCRIPTION"] = $obParser->html_cut($arResult["SECTION"][$arParams["SECTION_DESCRIPTION"]], 200);
				}
			}
		}
	}
	$this->setCurrencyTag();
	$this->setIblockTag();

	$this->EndResultCache();
}
else
{
	$this->facet->setPrices($arResult["PRICES"]);
	$this->facet->setSectionId($this->SECTION_ID);
}

/*Handle checked for checkboxes and html control value for numbers*/
if(isset($_REQUEST["ajax"]) && $_REQUEST["ajax"] === "y")
	$_CHECK = &$_REQUEST;
elseif(isset($_REQUEST["del_filter"]))
	$_CHECK = array();
elseif(isset($_GET["set_filter"]))
	$_CHECK = &$_GET;
elseif($arParams["SMART_FILTER_PATH"])
	$_CHECK = $this->convertUrlToCheck($arParams["~SMART_FILTER_PATH"]);
elseif($arParams["SAVE_IN_SESSION"] && isset($_SESSION[$FILTER_NAME][$this->SECTION_ID]))
	$_CHECK = $_SESSION[$FILTER_NAME][$this->SECTION_ID];
else
	$_CHECK = array();

/*Set state of the html controls depending on filter values*/
$allCHECKED = array();
/*Faceted filter*/
$facetIndex = array();
foreach($arResult["ITEMS"] as $PID => $arItem)
{
	foreach($arItem["VALUES"] as $key => $ar)
	{
		if ($arResult["FACET_FILTER"] && isset($ar["FACET_VALUE"]))
		{
			$facetIndex[$PID][$ar["FACET_VALUE"]] = &$arResult["ITEMS"][$PID]["VALUES"][$key];
		}

		if(
			isset($_CHECK[$ar["CONTROL_NAME"]])
			|| (
				isset($_CHECK[$ar["CONTROL_NAME_ALT"]])
				&& $_CHECK[$ar["CONTROL_NAME_ALT"]] == $ar["HTML_VALUE_ALT"]
			)
		)
		{
			if($arItem["PROPERTY_TYPE"] == "N")
			{
				$arResult["ITEMS"][$PID]["VALUES"][$key]["HTML_VALUE"] = htmlspecialcharsbx($_CHECK[$ar["CONTROL_NAME"]]);
				$arResult["ITEMS"][$PID]["DISPLAY_EXPANDED"] = "Y";
				if ($arResult["FACET_FILTER"] && strlen($_CHECK[$ar["CONTROL_NAME"]]) > 0)
				{
					if ($key == "MIN")
						$this->facet->addNumericPropertyFilter($PID, ">=", $_CHECK[$ar["CONTROL_NAME"]]);
					elseif ($key == "MAX")
						$this->facet->addNumericPropertyFilter($PID, "<=", $_CHECK[$ar["CONTROL_NAME"]]);
				}
			}
			elseif(isset($arItem["PRICE"]))
			{
				$arResult["ITEMS"][$PID]["VALUES"][$key]["HTML_VALUE"] = htmlspecialcharsbx($_CHECK[$ar["CONTROL_NAME"]]);
				$arResult["ITEMS"][$PID]["DISPLAY_EXPANDED"] = "Y";
				if ($arResult["FACET_FILTER"] && strlen($_CHECK[$ar["CONTROL_NAME"]]) > 0)
				{
					if ($key == "MIN")
						$this->facet->addPriceFilter($arResult["PRICES"][$PID]["ID"], ">=", $_CHECK[$ar["CONTROL_NAME"]]);
					elseif ($key == "MAX")
						$this->facet->addPriceFilter($arResult["PRICES"][$PID]["ID"], "<=", $_CHECK[$ar["CONTROL_NAME"]]);
				}
			}
			elseif($arItem["DISPLAY_TYPE"] == "U")
			{
				$arResult["ITEMS"][$PID]["VALUES"][$key]["HTML_VALUE"] = htmlspecialcharsbx($_CHECK[$ar["CONTROL_NAME"]]);
				$arResult["ITEMS"][$PID]["DISPLAY_EXPANDED"] = "Y";
				if ($arResult["FACET_FILTER"] && strlen($_CHECK[$ar["CONTROL_NAME"]]) > 0)
				{
					if ($key == "MIN")
						$this->facet->addDatetimePropertyFilter($PID, ">=", MakeTimeStamp($_CHECK[$ar["CONTROL_NAME"]], FORMAT_DATE));
					elseif ($key == "MAX")
						$this->facet->addDatetimePropertyFilter($PID, "<=", MakeTimeStamp($_CHECK[$ar["CONTROL_NAME"]], FORMAT_DATE) + 23*3600+59*60+59);
				}
			}
			elseif($_CHECK[$ar["CONTROL_NAME"]] == $ar["HTML_VALUE"])
			{
				$arResult["ITEMS"][$PID]["VALUES"][$key]["CHECKED"] = true;
				$arResult["ITEMS"][$PID]["DISPLAY_EXPANDED"] = "Y";
				$allCHECKED[$PID][$ar["VALUE"]] = true;
				if ($arResult["FACET_FILTER"])
				{
					if ($arItem["USER_TYPE"] === "DateTime")
						$this->facet->addDatetimePropertyFilter($PID, "=", MakeTimeStamp($ar["VALUE"], FORMAT_DATE));
					else
						$this->facet->addDictionaryPropertyFilter($PID, "=", $ar["FACET_VALUE"]);
				}
			}
			elseif($_CHECK[$ar["CONTROL_NAME_ALT"]] == $ar["HTML_VALUE_ALT"])
			{
				$arResult["ITEMS"][$PID]["VALUES"][$key]["CHECKED"] = true;
				$arResult["ITEMS"][$PID]["DISPLAY_EXPANDED"] = "Y";
				$allCHECKED[$PID][$ar["VALUE"]] = true;
				if ($arResult["FACET_FILTER"])
				{
					$this->facet->addDictionaryPropertyFilter($PID, "=", $ar["FACET_VALUE"]);
				}
			}
		}
	}
}

if ($_CHECK)
{
	/*Disable composite mode when filter checked*/
	$this->setFrameMode(false);

	if ($arResult["FACET_FILTER"])
	{
		if (!$this->facet->isEmptyWhere())
		{
			foreach ($arResult["ITEMS"] as $PID => &$arItem)
			{
				if ($arItem["PROPERTY_TYPE"] != "N" && !isset($arItem["PRICE"]))
				{
					foreach ($arItem["VALUES"] as $key => &$arValue)
					{
						$arValue["DISABLED"] = true;
						$arValue["ELEMENT_COUNT"] = 0;
					}
					unset($arValue);
				}
			}
			unset($arItem);

			if ($arResult["CURRENCIES"])
				$this->facet->enableCurrencyConversion($this->convertCurrencyId, array_keys($arResult["CURRENCIES"]));

			$res = $this->facet->query($arResult["FACET_FILTER"]);
			while ($row = $res->fetch())
			{
				$facetId = $row["FACET_ID"];
				if (\Bitrix\Iblock\PropertyIndex\Storage::isPropertyId($facetId))
				{
					$pp = \Bitrix\Iblock\PropertyIndex\Storage::facetIdToPropertyId($facetId);
					if ($arResult["ITEMS"][$pp]["PROPERTY_TYPE"] == "N")
					{
						if (is_array($arResult["ITEMS"][$pp]["VALUES"]))
						{
							$arResult["ITEMS"][$pp]["VALUES"]["MIN"]["FILTERED_VALUE"] = $row["MIN_VALUE_NUM"];
							$arResult["ITEMS"][$pp]["VALUES"]["MAX"]["FILTERED_VALUE"] = $row["MAX_VALUE_NUM"];
						}
					}
					else
					{
						if (isset($facetIndex[$pp][$row["VALUE"]]))
						{
							unset($facetIndex[$pp][$row["VALUE"]]["DISABLED"]);
							$facetIndex[$pp][$row["VALUE"]]["ELEMENT_COUNT"] = $row["ELEMENT_COUNT"];
						}
					}
				}
				else
				{
					$priceId = \Bitrix\Iblock\PropertyIndex\Storage::facetIdToPriceId($facetId);
					foreach($arResult["PRICES"] as $NAME => $arPrice)
					{
						if (
							$arPrice["ID"] == $priceId
							&& isset($arResult["ITEMS"][$NAME])
							&& is_array($arResult["ITEMS"][$NAME]["VALUES"])
						)
						{
							$currency = $row["VALUE"];
							$existCurrency = strlen($currency) > 0;
							if ($existCurrency)
								$currency = $this->facet->lookupDictionaryValue($currency);

							$priceValue = $this->convertPrice($row["MIN_VALUE_NUM"], $currency);
							if (
								!isset($arResult["ITEMS"][$NAME]["VALUES"]["MIN"]["FILTERED_VALUE"])
								|| $arResult["ITEMS"][$NAME]["VALUES"]["MIN"]["FILTERED_VALUE"] > $priceValue
							)
							{
								$arResult["ITEMS"][$NAME]["VALUES"]["MIN"]["FILTERED_VALUE"] = $priceValue;
							}

							$priceValue = $this->convertPrice($row["MAX_VALUE_NUM"], $currency);
							if (
									!isset($arResult["ITEMS"][$NAME]["VALUES"]["MAX"]["FILTERED_VALUE"])
									|| $arResult["ITEMS"][$NAME]["VALUES"]["MAX"]["FILTERED_VALUE"] > $priceValue
							)
							{
								$arResult["ITEMS"][$NAME]["VALUES"]["MAX"]["FILTERED_VALUE"] = $priceValue;
							}
						}
					}
				}
			}
		}
	}
	else
	{
		$index = array();
		foreach ($arResult["COMBO"] as $id => $combination)
		{
			foreach ($combination as $PID => $value)
			{
				$index[$PID][$value][] = &$arResult["COMBO"][$id];
			}
		}

		/*Handle disabled for checkboxes (TODO: handle number type)*/
		foreach ($arResult["ITEMS"] as $PID => &$arItem)
		{
			if ($arItem["PROPERTY_TYPE"] != "N" && !isset($arItem["PRICE"]))
			{
				//All except current one
				$checked = $allCHECKED;
				unset($checked[$PID]);

				foreach ($arItem["VALUES"] as $key => &$arValue)
				{
					$found = false;
					if (isset($index[$PID][$arValue["VALUE"]]))
					{
						//Check if there are any combinations exists
						foreach ($index[$PID][$arValue["VALUE"]] as $id => $combination)
						{
							//Check if combination fits into the filter
							$isOk = true;
							foreach ($checked as $cPID => $values)
							{
								if (!isset($values[$combination[$cPID]]))
								{
									$isOk = false;
									break;
								}
							}

							if ($isOk)
							{
								$found = true;
								break;
							}
						}
					}
					if (!$found)
						$arValue["DISABLED"] = true;
				}
				unset($arValue);
			}
		}
		unset($arItem);
	}
}

/*Make iblock filter*/
global ${$FILTER_NAME};
if(!is_array(${$FILTER_NAME}))
	${$FILTER_NAME} = array();

foreach($arResult["ITEMS"] as $PID => $arItem)
{
	if(isset($arItem["PRICE"]))
	{
		if(strlen($arItem["VALUES"]["MIN"]["HTML_VALUE"]) && strlen($arItem["VALUES"]["MAX"]["HTML_VALUE"]))
			${$FILTER_NAME}["><CATALOG_PRICE_".$arItem["ID"]] = array($arItem["VALUES"]["MIN"]["HTML_VALUE"], $arItem["VALUES"]["MAX"]["HTML_VALUE"]);
		elseif(strlen($arItem["VALUES"]["MIN"]["HTML_VALUE"]))
			${$FILTER_NAME}[">=CATALOG_PRICE_".$arItem["ID"]] = $arItem["VALUES"]["MIN"]["HTML_VALUE"];
		elseif(strlen($arItem["VALUES"]["MAX"]["HTML_VALUE"]))
			${$FILTER_NAME}["<=CATALOG_PRICE_".$arItem["ID"]] = $arItem["VALUES"]["MAX"]["HTML_VALUE"];
	}
	elseif($arItem["PROPERTY_TYPE"] == "N")
	{
		$existMinValue = (strlen($arItem["VALUES"]["MIN"]["HTML_VALUE"]) > 0);
		$existMaxValue = (strlen($arItem["VALUES"]["MAX"]["HTML_VALUE"]) > 0);
		if ($existMinValue || $existMaxValue)
		{
			$filterKey = '';
			$filterValue = '';
			if ($existMinValue && $existMaxValue)
			{
				$filterKey = "><PROPERTY_".$PID;
				$filterValue = array($arItem["VALUES"]["MIN"]["HTML_VALUE"], $arItem["VALUES"]["MAX"]["HTML_VALUE"]);
			}
			elseif($existMinValue)
			{
				$filterKey = ">=PROPERTY_".$PID;
				$filterValue = $arItem["VALUES"]["MIN"]["HTML_VALUE"];
			}
			elseif($existMaxValue)
			{
				$filterKey = "<=PROPERTY_".$PID;
				$filterValue = $arItem["VALUES"]["MAX"]["HTML_VALUE"];
			}

			if ($arItem["IBLOCK_ID"] == $this->SKU_IBLOCK_ID)
			{
				if (!isset(${$FILTER_NAME}["OFFERS"]))
				{
					${$FILTER_NAME}["OFFERS"] = array();
				}
				${$FILTER_NAME}["OFFERS"][$filterKey] = $filterValue;
			}
			else
			{
				${$FILTER_NAME}[$filterKey] = $filterValue;
			}
		}
	}
	elseif($arItem["DISPLAY_TYPE"] == "U")
	{
		$existMinValue = (strlen($arItem["VALUES"]["MIN"]["HTML_VALUE"]) > 0);
		$existMaxValue = (strlen($arItem["VALUES"]["MAX"]["HTML_VALUE"]) > 0);
		if ($existMinValue || $existMaxValue)
		{
			$filterKey = '';
			$filterValue = '';
			if ($existMinValue && $existMaxValue)
			{
				$filterKey = "><PROPERTY_".$PID;
				$timestamp1 = MakeTimeStamp($arItem["VALUES"]["MIN"]["HTML_VALUE"], FORMAT_DATE);
				$timestamp2 = MakeTimeStamp($arItem["VALUES"]["MAX"]["HTML_VALUE"], FORMAT_DATE);
				if ($timestamp1 && $timestamp2)
					$filterValue = array(FormatDate("Y-m-d H:i:s", $timestamp1), FormatDate("Y-m-d H:i:s", $timestamp2 + 23*3600+59*60+59));
			}
			elseif($existMinValue)
			{
				$filterKey = ">=PROPERTY_".$PID;
				$timestamp1 = MakeTimeStamp($arItem["VALUES"]["MIN"]["HTML_VALUE"], FORMAT_DATE);
				if ($timestamp1)
					$filterValue = FormatDate("Y-m-d H:i:s", $timestamp1);
			}
			elseif($existMaxValue)
			{
				$filterKey = "<=PROPERTY_".$PID;
				$timestamp2 = MakeTimeStamp($arItem["VALUES"]["MAX"]["HTML_VALUE"], FORMAT_DATE);
				if ($timestamp2)
					$filterValue = FormatDate("Y-m-d H:i:s", $timestamp2 + 23*3600+59*60+59);
			}

			if ($arItem["IBLOCK_ID"] == $this->SKU_IBLOCK_ID)
			{
				if (!isset(${$FILTER_NAME}["OFFERS"]))
				{
					${$FILTER_NAME}["OFFERS"] = array();
				}
				${$FILTER_NAME}["OFFERS"][$filterKey] = $filterValue;
			}
			else
			{
				${$FILTER_NAME}[$filterKey] = $filterValue;
			}
		}
	}
	elseif($arItem["USER_TYPE"] == "DateTime")
	{
		$datetimeFilters = array();
		foreach($arItem["VALUES"] as $key => $ar)
		{
			if ($ar["CHECKED"])
			{
				$filterKey = "><PROPERTY_".$PID;
				$timestamp = MakeTimeStamp($ar["VALUE"], FORMAT_DATE);
				$filterValue = array(
					FormatDate("Y-m-d H:i:s", $timestamp),
					FormatDate("Y-m-d H:i:s", $timestamp + 23 * 3600 + 59 * 60 + 59)
				);
				$datetimeFilters[] = array($filterKey => $filterValue);
			}
		}

		if ($datetimeFilters)
		{
			$datetimeFilters["LOGIC"] = "OR";
			if ($arItem["IBLOCK_ID"] == $this->SKU_IBLOCK_ID)
			{
				if (!isset(${$FILTER_NAME}["OFFERS"]))
				{
					${$FILTER_NAME}["OFFERS"] = array();
				}
				${$FILTER_NAME}["OFFERS"][] = $datetimeFilters;
			}
			else
			{
				${$FILTER_NAME}[] = $datetimeFilters;
			}
		}
	}
	else
	{
		foreach($arItem["VALUES"] as $key => $ar)
		{
			if($ar["CHECKED"])
			{
				$filterKey = "=PROPERTY_".$PID;
				$backKey = htmlspecialcharsback($key);
				if ($arItem["IBLOCK_ID"] == $this->SKU_IBLOCK_ID)
				{
					if (!isset(${$FILTER_NAME}["OFFERS"]))
					{
						${$FILTER_NAME}["OFFERS"] = array();
					}
					if (!isset(${$FILTER_NAME}["OFFERS"][$filterKey]))
						${$FILTER_NAME}["OFFERS"][$filterKey] = array($backKey);
					elseif (!is_array(${$FILTER_NAME}["OFFERS"][$filterKey]))
						${$FILTER_NAME}["OFFERS"][$filterKey] = array($filter[$filterKey], $backKey);
					elseif (!in_array($backKey, ${$FILTER_NAME}["OFFERS"][$filterKey]))
						${$FILTER_NAME}["OFFERS"][$filterKey][] = $backKey;
				}
				else
				{
					if (!isset(${$FILTER_NAME}[$filterKey]))
						${$FILTER_NAME}[$filterKey] = array($backKey);
					elseif (!is_array(${$FILTER_NAME}[$filterKey]))
						${$FILTER_NAME}[$filterKey] = array($filter[$filterKey], $backKey);
					elseif (!in_array($backKey, ${$FILTER_NAME}[$filterKey]))
						${$FILTER_NAME}[$filterKey][] = $backKey;
				}
			}
		}
	}
}

if ($arResult["FACET_FILTER"] && $this->arResult["CURRENCIES"])
{
	${$FILTER_NAME}["FACET_OPTIONS"]["PRICE_FILTER"] = true;
	${$FILTER_NAME}["FACET_OPTIONS"]["CURRENCY_CONVERSION"] = array(
		"FROM" => array_keys($arResult["CURRENCIES"]),
		"TO" => $this->convertCurrencyId,
	);
}

/*Save to session if needed*/
if($arParams["SAVE_IN_SESSION"])
{
	$_SESSION[$FILTER_NAME][$this->SECTION_ID] = $_CHECK;
}

$arResult["JS_FILTER_PARAMS"] = array();
if ($arParams["SEF_MODE"] == "Y")
{
	$section = false;
	if ($this->SECTION_ID > 0)
	{
		$sectionList = CIBlockSection::GetList(array(), array(
			"=ID" => $this->SECTION_ID,
			"IBLOCK_ID" => $this->IBLOCK_ID,
		), false, array("ID", "IBLOCK_ID", "SECTION_PAGE_URL"));
		$sectionList->SetUrlTemplates($arParams["SEF_RULE"]);
		$section = $sectionList->GetNext();
	}

	if ($section)
	{
		$url = $section["DETAIL_PAGE_URL"];
	}
	else
	{
		$url = CIBlock::ReplaceSectionUrl($arParams["SEF_RULE"], array());
	}

	$arResult["JS_FILTER_PARAMS"]["SEF_SET_FILTER_URL"] = $this->makeSmartUrl($url, true);
	$arResult["JS_FILTER_PARAMS"]["SEF_DEL_FILTER_URL"] = $this->makeSmartUrl($url, false);
}

$uri = new \Bitrix\Main\Web\Uri($this->request->getRequestUri());
$uri->deleteParams(\Bitrix\Main\HttpRequest::getSystemParameters());
$pageURL = $uri->GetUri();
$paramsToDelete = array("set_filter", "del_filter", "ajax", "bxajaxid", "AJAX_CALL", "mode");
foreach($arResult["ITEMS"] as $PID => $arItem)
{
	foreach($arItem["VALUES"] as $key => $ar)
	{
		$paramsToDelete[] = $ar["CONTROL_NAME"];
		$paramsToDelete[] = $ar["CONTROL_NAME_ALT"];
	}
}

$clearURL = CHTTP::urlDeleteParams($pageURL, $paramsToDelete, array("delete_system_params" => true));

if ($arResult["JS_FILTER_PARAMS"]["SEF_SET_FILTER_URL"])
{
	$arResult["FILTER_URL"] = $arResult["JS_FILTER_PARAMS"]["SEF_SET_FILTER_URL"];
	$arResult["FILTER_AJAX_URL"] = htmlspecialcharsbx(CHTTP::urlAddParams($arResult["FILTER_URL"], array(
		"bxajaxid" => $_GET["bxajaxid"],
	), array(
		"skip_empty" => true,
		"encode" => true,
	)));
	$arResult["SEF_SET_FILTER_URL"] = $arResult["JS_FILTER_PARAMS"]["SEF_SET_FILTER_URL"];
	$arResult["SEF_DEL_FILTER_URL"] = $arResult["JS_FILTER_PARAMS"]["SEF_DEL_FILTER_URL"];
}
else
{
	$paramsToAdd = array(
		"set_filter" => "y",
	);
	foreach($arResult["ITEMS"] as $PID => $arItem)
	{
		foreach($arItem["VALUES"] as $key => $ar)
		{
			if(isset($_CHECK[$ar["CONTROL_NAME"]]))
			{
				if($arItem["PROPERTY_TYPE"] == "N" || isset($arItem["PRICE"]))
					$paramsToAdd[$ar["CONTROL_NAME"]] = $_CHECK[$ar["CONTROL_NAME"]];
				elseif($_CHECK[$ar["CONTROL_NAME"]] == $ar["HTML_VALUE"])
					$paramsToAdd[$ar["CONTROL_NAME"]] = $_CHECK[$ar["CONTROL_NAME"]];
			}
			elseif(isset($_CHECK[$ar["CONTROL_NAME_ALT"]]))
			{
				if ($_CHECK[$ar["CONTROL_NAME_ALT"]] == $ar["HTML_VALUE_ALT"])
					$paramsToAdd[$ar["CONTROL_NAME_ALT"]] = $_CHECK[$ar["CONTROL_NAME_ALT"]];
			}
		}
	}

	$arResult["FILTER_URL"] = htmlspecialcharsbx(CHTTP::urlAddParams($clearURL, $paramsToAdd, array(
		"skip_empty" => true,
		"encode" => true,
	)));

	$arResult["FILTER_AJAX_URL"] = htmlspecialcharsbx(CHTTP::urlAddParams($clearURL, $paramsToAdd + array(
		"bxajaxid" => $_GET["bxajaxid"],
	), array(
		"skip_empty" => true,
		"encode" => true,
	)));
}

if(isset($_REQUEST["ajax"]) && $_REQUEST["ajax"] === "y")
{
	$arFilter = $this->makeFilter($FILTER_NAME);
	if (!empty($preFilter))
		$arFilter = array_merge($preFilter, $arFilter);
	$arResult["ELEMENT_COUNT"] = CIBlockElement::GetList(array(), $arFilter, array(), false);

	if (isset($_GET["bxajaxid"]))
	{
		$arResult["COMPONENT_CONTAINER_ID"] = htmlspecialcharsbx("comp_".$_GET["bxajaxid"]);
		if ($arParams["INSTANT_RELOAD"])
			$arResult["INSTANT_RELOAD"] = true;
	}
}

if (
	!empty($arParams["PAGER_PARAMS_NAME"])
	&& preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["PAGER_PARAMS_NAME"])
)
{
	if (!is_array($GLOBALS[$arParams["PAGER_PARAMS_NAME"]]))
		$GLOBALS[$arParams["PAGER_PARAMS_NAME"]] = array();

	if ($arResult["JS_FILTER_PARAMS"]["SEF_SET_FILTER_URL"])
	{
		$GLOBALS[$arParams["PAGER_PARAMS_NAME"]]["BASE_LINK"] = $arResult["JS_FILTER_PARAMS"]["SEF_SET_FILTER_URL"];
	}
	elseif (count($paramsToAdd) > 1)
	{
		$GLOBALS[$arParams["PAGER_PARAMS_NAME"]] = array_merge($GLOBALS[$arParams["PAGER_PARAMS_NAME"]], $paramsToAdd);
	}
}

$arInputNames = array();
foreach($arResult["ITEMS"] as $PID => $arItem)
{
	foreach($arItem["VALUES"] as $key => $ar)
	{
		$arInputNames[$ar["CONTROL_NAME"]] = true;
		$arInputNames[$ar["CONTROL_NAME_ALT"]] = true;
	}
}
$arInputNames["set_filter"]=true;
$arInputNames["del_filter"]=true;

$arSkip = array(
	"AUTH_FORM" => true,
	"TYPE" => true,
	"USER_LOGIN" => true,
	"USER_CHECKWORD" => true,
	"USER_PASSWORD" => true,
	"USER_CONFIRM_PASSWORD" => true,
	"USER_EMAIL" => true,
	"captcha_word" => true,
	"captcha_sid" => true,
	"login" => true,
	"Login" => true,
	"backurl" => true,
	"ajax" => true,
	"mode" => true,
	"bxajaxid" => true,
	"AJAX_CALL" => true,
);

$arResult["FORM_ACTION"] = $clearURL;
$arResult["HIDDEN"] = array();
foreach(array_merge($_GET, $_POST) as $key => $value)
{
	if(
		!isset($arInputNames[$key])
		&& !isset($arSkip[$key])
		&& !is_array($value)
	)
	{
		$arResult["HIDDEN"][] = array(
			"CONTROL_ID" => htmlspecialcharsbx($key),
			"CONTROL_NAME" => htmlspecialcharsbx($key),
			"HTML_VALUE" => htmlspecialcharsbx($value),
		);
	}
}

if (
	$arParams["XML_EXPORT"] === "Y"
	&& $arResult["SECTION"]
	&& ($arResult["SECTION"]["RIGHT_MARGIN"] - $arResult["SECTION"]["LEFT_MARGIN"]) === 1
)
{
	$exportUrl = CHTTP::urlAddParams($clearURL, array("mode" => "xml"));
	$APPLICATION->AddHeadString('<meta property="ya:interaction" content="XML_FORM" />');
	$APPLICATION->AddHeadString('<meta property="ya:interaction:url" content="'.CHTTP::urn2uri($exportUrl).'" />');
}

if ($arParams["XML_EXPORT"] === "Y" && $_REQUEST["mode"] === "xml")
{
	$this->setFrameMode(false);
	ob_start();
	$this->IncludeComponentTemplate("xml");
	$xml = ob_get_contents();
	$APPLICATION->RestartBuffer();
	while(ob_end_clean());
	header("Content-Type: text/xml; charset=utf-8");
	$error = "";
	echo \Bitrix\Main\Text\Encoding::convertEncoding($xml, LANG_CHARSET, "utf-8", $error);
	CMain::FinalActions();
	die();
}
elseif(isset($_REQUEST["ajax"]) && $_REQUEST["ajax"] === "y")
{
	$this->setFrameMode(false);
	define("BX_COMPRESSION_DISABLED", true);
	ob_start();
	$this->IncludeComponentTemplate("ajax");
	$json = ob_get_contents();
	$APPLICATION->RestartBuffer();
	while(ob_end_clean());
	header('Content-Type: application/x-javascript; charset='.LANG_CHARSET);
	CMain::FinalActions();
	echo $json;
	die();
}
else
{
	$this->IncludeComponentTemplate();
}
