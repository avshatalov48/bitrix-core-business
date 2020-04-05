<?
use Bitrix\Main\Loader;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

/*DEMO CODE for component inheritance
CBitrixComponent::includeComponentClass("bitrix::news.base");
class CBitrixCatalogSmartFilter extends CBitrixNewsBase
*/
class CBitrixCatalogSmartFilter extends CBitrixComponent
{
	public $IBLOCK_ID = 0;
	public $SKU_IBLOCK_ID = 0;
	public $SKU_PROPERTY_ID = 0;
	public $SECTION_ID = 0;
	public $FILTER_NAME = "";
	public $SAFE_FILTER_NAME = "";
	public $convertCurrencyId = "";

	protected $currencyTagList = array();
	protected $currencyCache = array();
	protected static $catalogIncluded = null;
	protected static $iblockIncluded = null;
	/** @var \Bitrix\Iblock\PropertyIndex\Facet **/
	protected $facet = null;

	public function onPrepareComponentParams($arParams)
	{
		$arParams["CACHE_TIME"] = isset($arParams["CACHE_TIME"]) ? $arParams["CACHE_TIME"]: 36000000;
		$arParams["IBLOCK_ID"] = (int)$arParams["IBLOCK_ID"];
		$arParams["SECTION_ID"] = (int)$arParams["SECTION_ID"];
		if ($arParams["SECTION_ID"] <= 0 && Loader::includeModule('iblock'))
		{
			$arParams["SECTION_ID"] = CIBlockFindTools::GetSectionID(
				$arParams["SECTION_ID"],
				$arParams["SECTION_CODE"],
				array(
					"GLOBAL_ACTIVE" => "Y",
					"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				)
			);
			if (!$arParams["SECTION_ID"] && strlen($arParams["SECTION_CODE_PATH"]) > 0)
			{
				$arParams["SECTION_ID"] = CIBlockFindTools::GetSectionIDByCodePath(
					$arParams["IBLOCK_ID"],
					$arParams["SECTION_CODE_PATH"]
				);
			}
		}

		$arParams["PRICE_CODE"] = is_array($arParams["PRICE_CODE"])? $arParams["PRICE_CODE"]: array();
		foreach ($arParams["PRICE_CODE"] as $k=>$v)
		{
			if ($v===null || $v==='' || $v===false)
				unset($arParams["PRICE_CODE"][$k]);
		}

		$arParams["SAVE_IN_SESSION"] = $arParams["SAVE_IN_SESSION"] == "Y";
		$arParams["CACHE_GROUPS"] = $arParams["CACHE_GROUPS"] !== "N";
		$arParams["INSTANT_RELOAD"] = $arParams["INSTANT_RELOAD"] === "Y";
		$arParams["SECTION_TITLE"] = trim($arParams["SECTION_TITLE"]);
		$arParams["SECTION_DESCRIPTION"] = trim($arParams["SECTION_DESCRIPTION"]);

		$arParams["FILTER_NAME"] = (isset($arParams["FILTER_NAME"]) ? (string)$arParams["FILTER_NAME"] : '');
		if(
			$arParams["FILTER_NAME"] == ''
			|| !preg_match("/^[A-Za-z_][A-Za-z01-9_]*$/", $arParams["FILTER_NAME"])
		)
		{
			$arParams["FILTER_NAME"] = "arrFilter";
		}

		$arParams["CONVERT_CURRENCY"] = $arParams["CONVERT_CURRENCY"] === "Y";
		$arParams["CURRENCY_ID"] = trim($arParams["CURRENCY_ID"]);
		if ($arParams["CURRENCY_ID"] == "")
		{
			$arParams["CONVERT_CURRENCY"] = false;
		}
		elseif (!$arParams["CONVERT_CURRENCY"])
		{
			$arParams["CURRENCY_ID"] = "";
		}

		return $arParams;
	}

	public function executeComponent()
	{
		$this->IBLOCK_ID = $this->arParams["IBLOCK_ID"];
		$this->SECTION_ID = $this->arParams["SECTION_ID"];
		$this->FILTER_NAME = $this->arParams["FILTER_NAME"];
		$this->SAFE_FILTER_NAME = htmlspecialcharsbx($this->FILTER_NAME);

		if (
			$this->arParams["CONVERT_CURRENCY"]
			&& $this->arParams["CURRENCY_ID"] != ""
			&& Loader::includeModule('currency')
		)
		{
			$currencyList = \Bitrix\Currency\CurrencyTable::getList(array(
				'select' => array('CURRENCY'),
				'filter' => array('=CURRENCY' => $this->arParams['CURRENCY_ID'])
			));
			if ($currency = $currencyList->fetch())
				$this->convertCurrencyId = $currency['CURRENCY'];
			unset($currency);
			unset($currencyList);
		}

		if (self::$iblockIncluded === null)
			self::$iblockIncluded = Loader::includeModule('iblock');
		if (!self::$iblockIncluded)
			return '';

		if (self::$catalogIncluded === null)
			self::$catalogIncluded = Loader::includeModule('catalog');
		if (self::$catalogIncluded)
		{
			$arCatalog = CCatalogSKU::GetInfoByProductIBlock($this->IBLOCK_ID);
			if (!empty($arCatalog))
			{
				$this->SKU_IBLOCK_ID = $arCatalog["IBLOCK_ID"];
				$this->SKU_PROPERTY_ID = $arCatalog["SKU_PROPERTY_ID"];
			}
		}

		$this->facet = new \Bitrix\Iblock\PropertyIndex\Facet($this->IBLOCK_ID);

		return parent::executeComponent();
	}

	public function getIBlockItems($IBLOCK_ID)
	{
		$items = array();

		foreach(CIBlockSectionPropertyLink::GetArray($IBLOCK_ID, $this->SECTION_ID) as $PID => $arLink)
		{
			if ($arLink["SMART_FILTER"] !== "Y")
				continue;

			if ($arLink["ACTIVE"] === "N")
				continue;

			if ($arLink['FILTER_HINT'] <> '')
			{
				$arLink['FILTER_HINT'] = CTextParser::closeTags($arLink['FILTER_HINT']);
			}

			$rsProperty = CIBlockProperty::GetByID($PID);
			$arProperty = $rsProperty->Fetch();
			if($arProperty)
			{
				$items[$arProperty["ID"]] = array(
					"ID" => $arProperty["ID"],
					"IBLOCK_ID" => $arProperty["IBLOCK_ID"],
					"CODE" => $arProperty["CODE"],
					"~NAME" => $arProperty["NAME"],
					"NAME" => htmlspecialcharsEx($arProperty["NAME"]),
					"PROPERTY_TYPE" => $arProperty["PROPERTY_TYPE"],
					"USER_TYPE" => $arProperty["USER_TYPE"],
					"USER_TYPE_SETTINGS" => $arProperty["USER_TYPE_SETTINGS"],
					"DISPLAY_TYPE" => $arLink["DISPLAY_TYPE"],
					"DISPLAY_EXPANDED" => $arLink["DISPLAY_EXPANDED"],
					"FILTER_HINT" => $arLink["FILTER_HINT"],
					"VALUES" => array(),
				);

				if (
					$arProperty["PROPERTY_TYPE"] == "N"
					|| $arLink["DISPLAY_TYPE"] == "U"
				)
				{
					$minID = $this->SAFE_FILTER_NAME.'_'.$arProperty['ID'].'_MIN';
					$maxID = $this->SAFE_FILTER_NAME.'_'.$arProperty['ID'].'_MAX';
					$items[$arProperty["ID"]]["VALUES"] = array(
						"MIN" => array(
							"CONTROL_ID" => $minID,
							"CONTROL_NAME" => $minID,
						),
						"MAX" => array(
							"CONTROL_ID" => $maxID,
							"CONTROL_NAME" => $maxID,
						),
					);
				}
			}
		}
		return $items;
	}

	public function getPriceItems()
	{
		$items = array();
		if (!empty($this->arParams["PRICE_CODE"]))
		{
			if (self::$catalogIncluded === null)
				self::$catalogIncluded = Loader::includeModule('catalog');
			if (self::$catalogIncluded)
			{
				$rsPrice = CCatalogGroup::GetList(
					array('SORT' => 'ASC', 'ID' => 'ASC'),
					array('=NAME' => $this->arParams["PRICE_CODE"]),
					false,
					false,
					array('ID', 'NAME', 'NAME_LANG', 'CAN_ACCESS', 'CAN_BUY')
				);
				while($arPrice = $rsPrice->Fetch())
				{
					if($arPrice["CAN_ACCESS"] == "Y" || $arPrice["CAN_BUY"] == "Y")
					{
						$arPrice["NAME_LANG"] = (string)$arPrice["NAME_LANG"];
						if ($arPrice["NAME_LANG"] === '')
							$arPrice["NAME_LANG"] = $arPrice["NAME"];
						$minID = $this->SAFE_FILTER_NAME.'_P'.$arPrice['ID'].'_MIN';
						$maxID = $this->SAFE_FILTER_NAME.'_P'.$arPrice['ID'].'_MAX';
						$items[$arPrice["NAME"]] = array(
							"ID" => $arPrice["ID"],
							"CODE" => $arPrice["NAME"],
							"~NAME" => $arPrice["NAME_LANG"],
							"NAME" => htmlspecialcharsbx($arPrice["NAME_LANG"]),
							"PRICE" => true,
							"VALUES" => array(
								"MIN" => array(
									"CONTROL_ID" => $minID,
									"CONTROL_NAME" => $minID,
								),
								"MAX" => array(
									"CONTROL_ID" => $maxID,
									"CONTROL_NAME" => $maxID,
								),
							),
						);
					}
				}
			}
		}
		return $items;
	}

	public function getResultItems()
	{
		$items = $this->getIBlockItems($this->IBLOCK_ID);
		$this->arResult["PROPERTY_COUNT"] = count($items);
		$this->arResult["PROPERTY_ID_LIST"] = array_keys($items);

		if($this->SKU_IBLOCK_ID)
		{
			$this->arResult["SKU_PROPERTY_ID_LIST"] = array($this->SKU_PROPERTY_ID);
			foreach($this->getIBlockItems($this->SKU_IBLOCK_ID) as $PID => $arItem)
			{
				$items[$PID] = $arItem;
				$this->arResult["SKU_PROPERTY_COUNT"]++;
				$this->arResult["SKU_PROPERTY_ID_LIST"][] = $PID;
			}
		}

		if (!empty($this->arParams["PRICE_CODE"]))
		{
			foreach($this->getPriceItems() as $PID => $arItem)
			{
				$arItem["ENCODED_ID"] = md5($arItem["ID"]);
				$items[$PID] = $arItem;
			}
		}

		return $items;
	}

	public function fillItemPrices(&$resultItem, $arElement)
	{
		if (isset($arElement["MIN_VALUE_NUM"]) && isset($arElement["MAX_VALUE_NUM"]))
		{
			$currency = $arElement["VALUE"];
			$existCurrency = strlen($currency) > 0;
			if ($existCurrency)
				$currency = $this->facet->lookupDictionaryValue($currency);

			$priceValue = $this->convertPrice($arElement["MIN_VALUE_NUM"], $currency);
			if (
				!isset($resultItem["VALUES"]["MIN"]["VALUE"])
				|| $resultItem["VALUES"]["MIN"]["VALUE"] > $priceValue
			)
			{
				$resultItem["VALUES"]["MIN"]["VALUE"] = $priceValue;
				if ($existCurrency)
				{
					if ($this->convertCurrencyId)
						$resultItem["VALUES"]["MIN"]["CURRENCY"] = $this->convertCurrencyId;
					else
						$resultItem["VALUES"]["MIN"]["CURRENCY"] = $currency;
				}
			}

			$priceValue = $this->convertPrice($arElement["MAX_VALUE_NUM"], $currency);
			if (
				!isset($resultItem["VALUES"]["MAX"]["VALUE"])
				|| $resultItem["VALUES"]["MAX"]["VALUE"] < $priceValue
			)
			{
				$resultItem["VALUES"]["MAX"]["VALUE"] = $priceValue;
				if ($existCurrency)
				{
					if ($this->convertCurrencyId)
						$resultItem["VALUES"]["MAX"]["CURRENCY"] = $this->convertCurrencyId;
					else
						$resultItem["VALUES"]["MAX"]["CURRENCY"] = $currency;
				}
			}
		}
		else
		{
			$currency = $arElement["CATALOG_CURRENCY_".$resultItem["ID"]];
			$existCurrency = strlen($currency) > 0;
			$price = $arElement["CATALOG_PRICE_".$resultItem["ID"]];
			if(strlen($price))
			{
				if ($this->convertCurrencyId && $existCurrency)
				{
					$convertPrice = CCurrencyRates::ConvertCurrency($price, $currency, $this->convertCurrencyId);
					$this->currencyTagList[$currency] = $currency;
				}
				else
				{
					$convertPrice = (float)$price;
				}

				if(
					!isset($resultItem["VALUES"]["MIN"])
					|| !array_key_exists("VALUE", $resultItem["VALUES"]["MIN"])
					|| doubleval($resultItem["VALUES"]["MIN"]["VALUE"]) > $convertPrice
				)
				{
					$resultItem["VALUES"]["MIN"]["VALUE"] = $price;
					if ($existCurrency)
					{
						if ($this->convertCurrencyId)
							$resultItem["VALUES"]["MIN"]["CURRENCY"] = $this->convertCurrencyId;
						else
							$resultItem["VALUES"]["MIN"]["CURRENCY"] = $currency;
					}
				}

				if(
					!isset($resultItem["VALUES"]["MAX"])
					|| !array_key_exists("VALUE", $resultItem["VALUES"]["MAX"])
					|| doubleval($resultItem["VALUES"]["MAX"]["VALUE"]) < $convertPrice
				)
				{
					$resultItem["VALUES"]["MAX"]["VALUE"] = $price;
					if ($existCurrency)
					{
						if ($this->convertCurrencyId)
							$resultItem["VALUES"]["MAX"]["CURRENCY"] = $this->convertCurrencyId;
						else
							$resultItem["VALUES"]["MAX"]["CURRENCY"] = $currency;
					}
				}
			}
		}

		if ($existCurrency)
		{
			if ($this->convertCurrencyId)
			{
				$resultItem["CURRENCIES"][$this->convertCurrencyId] = (
					isset($this->currencyCache[$this->convertCurrencyId])
					? $this->currencyCache[$this->convertCurrencyId]
					: $this->getCurrencyFullName($this->convertCurrencyId)
				);
				$resultItem["~CURRENCIES"][$currency] = (
					isset($this->currencyCache[$currency])
					? $this->currencyCache[$currency]
					: $this->getCurrencyFullName($currency)
				);
			}
			else
			{
				$resultItem["CURRENCIES"][$currency] = (
					isset($this->currencyCache[$currency])
					? $this->currencyCache[$currency]
					: $this->getCurrencyFullName($currency)
				);
			}
		}
	}

	public function convertPrice($price, $currency)
	{
		if ($this->convertCurrencyId && $currency)
		{
			$priceValue = CCurrencyRates::ConvertCurrency($price, $currency, $this->convertCurrencyId);
			$this->currencyTagList[$currency] = $currency;
		}
		else
		{
			$priceValue = $price;
		}
		return $priceValue;
	}

	public function fillItemValues(&$resultItem, $arProperty, $flag = null)
	{
		static $cache = array();

		if(is_array($arProperty))
		{
			if(isset($arProperty["PRICE"]))
			{
				return null;
			}
			$key = $arProperty["VALUE"];
			$PROPERTY_TYPE = $arProperty["PROPERTY_TYPE"];
			$PROPERTY_USER_TYPE = $arProperty["USER_TYPE"];
			$PROPERTY_ID = $arProperty["ID"];
		}
		else
		{
			$key = $arProperty;
			$PROPERTY_TYPE = $resultItem["PROPERTY_TYPE"];
			$PROPERTY_USER_TYPE = $resultItem["USER_TYPE"];
			$PROPERTY_ID = $resultItem["ID"];
			$arProperty = $resultItem;
		}

		if($PROPERTY_TYPE == "F")
		{
			return null;
		}
		elseif($PROPERTY_TYPE == "N")
		{
			$convertKey = (float)$key;
			if (strlen($key) <= 0)
			{
				return null;
			}

			if (
				!isset($resultItem["VALUES"]["MIN"])
				|| !array_key_exists("VALUE", $resultItem["VALUES"]["MIN"])
				|| doubleval($resultItem["VALUES"]["MIN"]["VALUE"]) > $convertKey
			)
				$resultItem["VALUES"]["MIN"]["VALUE"] = preg_replace("/\\.0+\$/", "", $key);

			if (
				!isset($resultItem["VALUES"]["MAX"])
				|| !array_key_exists("VALUE", $resultItem["VALUES"]["MAX"])
				|| doubleval($resultItem["VALUES"]["MAX"]["VALUE"]) < $convertKey
			)
				$resultItem["VALUES"]["MAX"]["VALUE"] = preg_replace("/\\.0+\$/", "", $key);

			return null;
		}
		elseif($arProperty["DISPLAY_TYPE"] == "U")
		{
			$date = substr($key, 0, 10);
			if (!$date)
			{
				return null;
			}
			$timestamp = MakeTimeStamp($date, "YYYY-MM-DD");
			if (!$timestamp)
			{
				return null;
			}

			if (
				!isset($resultItem["VALUES"]["MIN"])
				|| !array_key_exists("VALUE", $resultItem["VALUES"]["MIN"])
				|| $resultItem["VALUES"]["MIN"]["VALUE"] > $timestamp
			)
				$resultItem["VALUES"]["MIN"]["VALUE"] = $timestamp;

			if (
				!isset($resultItem["VALUES"]["MAX"])
				|| !array_key_exists("VALUE", $resultItem["VALUES"]["MAX"])
				|| $resultItem["VALUES"]["MAX"]["VALUE"] < $timestamp
			)
				$resultItem["VALUES"]["MAX"]["VALUE"] = $timestamp;

			return null;
		}
		elseif($PROPERTY_TYPE == "E" && $key <= 0)
		{
			return null;
		}
		elseif($PROPERTY_TYPE == "G" && $key <= 0)
		{
			return null;
		}
		elseif(strlen($key) <= 0)
		{
			return null;
		}

		$arUserType = array();
		if($PROPERTY_USER_TYPE != "")
		{
			$arUserType = CIBlockProperty::GetUserType($PROPERTY_USER_TYPE);
			if(isset($arUserType["GetExtendedValue"]))
				$PROPERTY_TYPE = "Ux";
			elseif(isset($arUserType["GetPublicViewHTML"]))
				$PROPERTY_TYPE = "U";
		}

		if ($PROPERTY_USER_TYPE === "DateTime")
		{
			$key = call_user_func_array(
				$arUserType["GetPublicViewHTML"],
				array(
					$arProperty,
					array("VALUE" => $key),
					array("MODE" => "SIMPLE_TEXT", "DATETIME_FORMAT" => "SHORT"),
				)
			);
			$PROPERTY_TYPE = "S";
		}

		$htmlKey = htmlspecialcharsbx($key);
		if (isset($resultItem["VALUES"][$htmlKey]))
		{
			return $htmlKey;
		}

		$file_id = null;
		$url_id = null;

		switch($PROPERTY_TYPE)
		{
		case "L":
			$enum = CIBlockPropertyEnum::GetByID($key);
			if ($enum)
			{
				$value = $enum["VALUE"];
				$sort  = $enum["SORT"];
				$url_id = toLower($enum["XML_ID"]);
			}
			else
			{
				return null;
			}
			break;
		case "E":
			if(!isset($cache[$PROPERTY_TYPE][$key]))
			{
				$arLinkFilter = array (
					"ID" => $key,
					"ACTIVE" => "Y",
					"ACTIVE_DATE" => "Y",
					"CHECK_PERMISSIONS" => "Y",
				);
				$rsLink = CIBlockElement::GetList(array(), $arLinkFilter, false, false, array("ID","IBLOCK_ID","NAME","SORT","CODE"));
				$cache[$PROPERTY_TYPE][$key] = $rsLink->Fetch();
			}

			if (!$cache[$PROPERTY_TYPE][$key])
				return null;

			$value = $cache[$PROPERTY_TYPE][$key]["NAME"];
			$sort = $cache[$PROPERTY_TYPE][$key]["SORT"];
			if ($cache[$PROPERTY_TYPE][$key]["CODE"])
				$url_id = toLower($cache[$PROPERTY_TYPE][$key]["CODE"]);
			else
				$url_id = toLower($value);
			break;
		case "G":
			if(!isset($cache[$PROPERTY_TYPE][$key]))
			{
				$arLinkFilter = array (
					"ID" => $key,
					"GLOBAL_ACTIVE" => "Y",
					"CHECK_PERMISSIONS" => "Y",
				);
				$rsLink = CIBlockSection::GetList(array(), $arLinkFilter, false, array("ID","IBLOCK_ID","NAME","LEFT_MARGIN","DEPTH_LEVEL","CODE"));
				$cache[$PROPERTY_TYPE][$key] = $rsLink->Fetch();
				$cache[$PROPERTY_TYPE][$key]['DEPTH_NAME'] = str_repeat(".", $cache[$PROPERTY_TYPE][$key]["DEPTH_LEVEL"]).$cache[$PROPERTY_TYPE][$key]["NAME"];
			}

			if (!$cache[$PROPERTY_TYPE][$key])
				return null;

			$value = $cache[$PROPERTY_TYPE][$key]['DEPTH_NAME'];
			$sort = $cache[$PROPERTY_TYPE][$key]["LEFT_MARGIN"];
			if ($cache[$PROPERTY_TYPE][$key]["CODE"])
				$url_id = toLower($cache[$PROPERTY_TYPE][$key]["CODE"]);
			else
				$url_id = toLower($value);
			break;
		case "U":
			if(!isset($cache[$PROPERTY_ID]))
				$cache[$PROPERTY_ID] = array();

			if(!isset($cache[$PROPERTY_ID][$key]))
			{
				$cache[$PROPERTY_ID][$key] = call_user_func_array(
					$arUserType["GetPublicViewHTML"],
					array(
						$arProperty,
						array("VALUE" => $key),
						array("MODE" => "SIMPLE_TEXT"),
					)
				);
			}

			$value = $cache[$PROPERTY_ID][$key];
			$sort = 0;
			$url_id = toLower($value);
			break;
		case "Ux":
			if(!isset($cache[$PROPERTY_ID]))
				$cache[$PROPERTY_ID] = array();

			if(!isset($cache[$PROPERTY_ID][$key]))
			{
				$cache[$PROPERTY_ID][$key] = call_user_func_array(
					$arUserType["GetExtendedValue"],
					array(
						$arProperty,
						array("VALUE" => $key),
					)
				);
			}

			if ($cache[$PROPERTY_ID][$key])
			{
				$value = $cache[$PROPERTY_ID][$key]['VALUE'];
				$file_id = $cache[$PROPERTY_ID][$key]['FILE_ID'];
				$sort = (isset($cache[$PROPERTY_ID][$key]['SORT']) ? $cache[$PROPERTY_ID][$key]['SORT'] : 0);
				$url_id = toLower($cache[$PROPERTY_ID][$key]['UF_XML_ID']);
			}
			else
			{
				return null;
			}
			break;
		default:
			$value = $key;
			$sort = 0;
			$url_id = toLower($value);
			break;
		}

		$keyCrc = abs(crc32($htmlKey));
		$safeValue = htmlspecialcharsex($value);
		$sort = (int)$sort;

		$filterPropertyID = $this->SAFE_FILTER_NAME.'_'.$PROPERTY_ID;
		$filterPropertyIDKey = $filterPropertyID.'_'.$keyCrc;
		$resultItem["VALUES"][$htmlKey] = array(
			"CONTROL_ID" => $filterPropertyIDKey,
			"CONTROL_NAME" => $filterPropertyIDKey,
			"CONTROL_NAME_ALT" => $filterPropertyID,
			"HTML_VALUE_ALT" => $keyCrc,
			"HTML_VALUE" => "Y",
			"VALUE" => $safeValue,
			"SORT" => $sort,
			"UPPER" => ToUpper($safeValue),
			"FLAG" => $flag,
		);

		if ($file_id)
		{
			$resultItem["VALUES"][$htmlKey]['FILE'] = CFile::GetFileArray($file_id);
		}

		if (strlen($url_id))
		{
			$error = "";
			$utf_id = \Bitrix\Main\Text\Encoding::convertEncoding($url_id, LANG_CHARSET, "utf-8", $error);
			$resultItem["VALUES"][$htmlKey]['URL_ID'] = rawurlencode(str_replace("/", "-", $utf_id));
		}

		return $htmlKey;
	}

	function combineCombinations(&$arCombinations)
	{
		$result = array();
		foreach($arCombinations as $arCombination)
		{
			foreach($arCombination as $PID => $value)
			{
				if(!isset($result[$PID]))
					$result[$PID] = array();
				if(strlen($value))
					$result[$PID][] = $value;
			}
		}
		return $result;
	}

	function filterCombinations(&$arCombinations, $arItems, $currentPID)
	{
		foreach($arCombinations as $key => $arCombination)
		{
			if(!$this->combinationMatch($arCombination, $arItems, $currentPID))
				unset($arCombinations[$key]);
		}
	}

	function combinationMatch($combination, $arItems, $currentPID)
	{
		foreach($arItems as $PID => $arItem)
		{
			if ($PID != $currentPID)
			{
				if($arItem["PROPERTY_TYPE"] == "N" || isset($arItem["PRICE"]))
				{
					//TODO
				}
				else
				{
					if(!$this->matchProperty($combination[$PID], $arItem["VALUES"]))
						return false;
				}
			}
		}
		return true;
	}

	function matchProperty($value, $arValues)
	{
		$match = true;
		foreach($arValues as $formControl)
		{
			if($formControl["CHECKED"])
			{
				if($formControl["VALUE"] == $value)
					return true;
				else
					$match = false;
			}
		}
		return $match;
	}

	public function _sort($v1, $v2)
	{
		if ($v1["SORT"] < $v2["SORT"])
			return -1;
		elseif ($v1["SORT"] > $v2["SORT"])
			return 1;
		else
			return strcmp($v1["UPPER"], $v2["UPPER"]);
	}

	/*
	This function takes an array (arTuple) which is mix of scalar values and arrays
	and return "rectangular" array of arrays.
	For example:
	array(1, array(1, 2), 3, arrays(4, 5))
	will be transformed as
	array(
		array(1, 1, 3, 4),
		array(1, 1, 3, 5),
		array(1, 2, 3, 4),
		array(1, 2, 3, 5),
	)
	*/
	function ArrayMultiply(&$arResult, $arTuple, $arTemp = array())
	{
		if($arTuple)
		{
			foreach ($arTuple as $key => $head)
			{
				unset($arTuple[$key]);
				$arTemp[$key] = false;
				if(is_array($head))
				{
					if(empty($head))
					{
						if(empty($arTuple))
							$arResult[] = $arTemp;
					}
					else
					{
						foreach($head as $value)
						{
							$arTemp[$key] = $value;
							if(empty($arTuple))
								$arResult[] = $arTemp;
							else
								break;
						}
					}
				}
				else
				{
					$arTemp[$key] = $head;
					if(empty($arTuple))
						$arResult[] = $arTemp;
				}
			}
		}
		else
		{
			$arResult[] = $arTemp;
		}
	}

	function makeFilter($FILTER_NAME)
	{
		$bOffersIBlockExist = false;
		if (self::$catalogIncluded === null)
			self::$catalogIncluded = Loader::includeModule('catalog');
		if (self::$catalogIncluded)
		{
			$arCatalog = CCatalogSKU::GetInfoByProductIBlock($this->IBLOCK_ID);
			if (!empty($arCatalog))
			{
				$bOffersIBlockExist = true;
			}
		}

		$gFilter = $GLOBALS[$FILTER_NAME];

		$arFilter = array(
			"IBLOCK_ID" => $this->IBLOCK_ID,
			"IBLOCK_LID" => SITE_ID,
			"IBLOCK_ACTIVE" => "Y",
			"ACTIVE_DATE" => "Y",
			"ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
			"MIN_PERMISSION" => "R",
			"INCLUDE_SUBSECTIONS" => ($this->arParams["INCLUDE_SUBSECTIONS"] != 'N' ? 'Y' : 'N'),
		);
		if (($this->SECTION_ID > 0) || ($this->arParams["SHOW_ALL_WO_SECTION"] !== "Y"))
		{
			$arFilter["SECTION_ID"] = $this->SECTION_ID;
		}

		if ($this->arParams['HIDE_NOT_AVAILABLE'] == 'Y')
			$arFilter['CATALOG_AVAILABLE'] = 'Y';

		if(self::$catalogIncluded && $bOffersIBlockExist)
		{
			$arPriceFilter = array();
			foreach($gFilter as $key => $value)
			{
				if(preg_match('/^(>=|<=|><)CATALOG_PRICE_/', $key))
				{
					$arPriceFilter[$key] = $value;
					unset($gFilter[$key]);
				}
			}

			if(!empty($gFilter["OFFERS"]))
			{
				if (empty($arPriceFilter))
					$arSubFilter = $gFilter["OFFERS"];
				else
					$arSubFilter = array_merge($gFilter["OFFERS"], $arPriceFilter);

				$arSubFilter["IBLOCK_ID"] = $this->SKU_IBLOCK_ID;
				$arSubFilter["ACTIVE_DATE"] = "Y";
				$arSubFilter["ACTIVE"] = "Y";
				if ('Y' == $this->arParams['HIDE_NOT_AVAILABLE'])
					$arSubFilter['CATALOG_AVAILABLE'] = 'Y';
				$arFilter["=ID"] = CIBlockElement::SubQuery("PROPERTY_".$this->SKU_PROPERTY_ID, $arSubFilter);
			}
			elseif(!empty($arPriceFilter))
			{
				$arSubFilter = $arPriceFilter;

				$arSubFilter["IBLOCK_ID"] = $this->SKU_IBLOCK_ID;
				$arSubFilter["ACTIVE_DATE"] = "Y";
				$arSubFilter["ACTIVE"] = "Y";
				$arFilter[] = array(
					"LOGIC" => "OR",
					array($arPriceFilter),
					"=ID" => CIBlockElement::SubQuery("PROPERTY_".$this->SKU_PROPERTY_ID, $arSubFilter),
				);
			}

			unset($gFilter["OFFERS"]);
		}

		return array_merge($gFilter, $arFilter);
	}

	public function getCurrencyFullName($currencyId)
	{
		if (!isset($this->currencyCache[$currencyId]))
		{
			$currencyInfo = CCurrencyLang::GetById($currencyId, LANGUAGE_ID);
			if ($currencyInfo["FULL_NAME"] != "")
				$this->currencyCache[$currencyId] = $currencyInfo["FULL_NAME"];
			else
				$this->currencyCache[$currencyId] = $currencyId;
		}
		return $this->currencyCache[$currencyId];
	}

	public function searchPrice($items, $lookupValue)
	{
		foreach($items as $itemId => $arItem)
		{
			if ($arItem["PRICE"])
			{
				$code = toLower($arItem["CODE"]);
				if ($lookupValue === $code)
					return $itemId;
			}
		}
		return false;
	}

	public function searchProperty($items, $lookupValue)
	{
		foreach($items as $itemId => $arItem)
		{
			if (!$arItem["PRICE"])
			{
				$code = toLower($arItem["CODE"]);
				if ($lookupValue === $code)
					return $itemId;
				if ($lookupValue == intval($arItem["ID"]))
					return $itemId;
			}
		}
		return false;
	}

	public function searchValue($item, $lookupValue)
	{
		$error = "";
		$searchValue = \Bitrix\Main\Text\Encoding::convertEncoding($lookupValue, LANG_CHARSET, "utf-8", $error);
		if (!$error)
		{
			$encodedValue = rawurlencode($searchValue);
			foreach($item as $itemId => $arValue)
			{
				if ($encodedValue === $arValue["URL_ID"])
					return $itemId;
			}
		}
		return false;
	}

	public function convertUrlToCheck($url)
	{
		$result = array();
		$smartParts = explode("/", $url);
		foreach ($smartParts as $smartPart)
		{
			$item = false;
			$smartPart = preg_split("/-(from|to|is|or)-/", $smartPart, -1, PREG_SPLIT_DELIM_CAPTURE);
			foreach ($smartPart as $i => $smartElement)
			{
				if ($i == 0)
				{
					if (preg_match("/^price-(.+)$/", $smartElement, $match))
						$itemId = $this->searchPrice($this->arResult["ITEMS"], $match[1]);
					else
						$itemId = $this->searchProperty($this->arResult["ITEMS"], $smartElement);

					if ($itemId)
						$item = &$this->arResult["ITEMS"][$itemId];
					else
						break;
				}
				elseif ($smartElement === "from")
				{
					$result[$item["VALUES"]["MIN"]["CONTROL_NAME"]] = $smartPart[$i+1];
				}
				elseif ($smartElement === "to")
				{
					$result[$item["VALUES"]["MAX"]["CONTROL_NAME"]] = $smartPart[$i+1];
				}
				elseif ($smartElement === "is" || $smartElement === "or")
				{
					$valueId = $this->searchValue($item["VALUES"], $smartPart[$i+1]);
					if (strlen($valueId))
					{
						$result[$item["VALUES"][$valueId]["CONTROL_NAME"]] = $item["VALUES"][$valueId]["HTML_VALUE"];
					}
				}
			}
			unset($item);
		}
		return $result;
	}

	public function makeSmartUrl($url, $apply, $checkedControlId = false)
	{
		$smartParts = array();

		if ($apply)
		{
			foreach($this->arResult["ITEMS"] as $PID => $arItem)
			{
				$smartPart = array();
				//Prices
				if ($arItem["PRICE"])
				{
					if (strlen($arItem["VALUES"]["MIN"]["HTML_VALUE"]) > 0)
						$smartPart["from"] = $arItem["VALUES"]["MIN"]["HTML_VALUE"];
					if (strlen($arItem["VALUES"]["MAX"]["HTML_VALUE"]) > 0)
						$smartPart["to"] = $arItem["VALUES"]["MAX"]["HTML_VALUE"];
				}

				if ($smartPart)
				{
					array_unshift($smartPart, toLower("price-".$arItem["CODE"]));

					$smartParts[] = $smartPart;
				}
			}

			foreach($this->arResult["ITEMS"] as $PID => $arItem)
			{
				$smartPart = array();
				if ($arItem["PRICE"])
					continue;

				//Numbers && calendar == ranges
				if (
					$arItem["PROPERTY_TYPE"] == "N"
					|| $arItem["DISPLAY_TYPE"] == "U"
				)
				{
					if (strlen($arItem["VALUES"]["MIN"]["HTML_VALUE"]) > 0)
						$smartPart["from"] = $arItem["VALUES"]["MIN"]["HTML_VALUE"];
					if (strlen($arItem["VALUES"]["MAX"]["HTML_VALUE"]) > 0)
						$smartPart["to"] = $arItem["VALUES"]["MAX"]["HTML_VALUE"];
				}
				else
				{
					foreach($arItem["VALUES"] as $key => $ar)
					{
						if (
							(
								$ar["CHECKED"]
								|| $ar["CONTROL_ID"] === $checkedControlId
							)
							&& strlen($ar["URL_ID"])
						)
						{
							$smartPart[] = $ar["URL_ID"];
						}
					}
				}

				if ($smartPart)
				{
					if ($arItem["CODE"])
						array_unshift($smartPart, toLower($arItem["CODE"]));
					else
						array_unshift($smartPart, $arItem["ID"]);

					$smartParts[] = $smartPart;
				}
			}
		}

		if (!$smartParts)
			$smartParts[] = array("clear");

		return str_replace("#SMART_FILTER_PATH#", implode("/", $this->encodeSmartParts($smartParts)), $url);
	}

	public function encodeSmartParts($smartParts)
	{
		foreach ($smartParts as &$smartPart)
		{
			$urlPart = "";
			foreach ($smartPart as $i => $smartElement)
			{
				if (!$urlPart)
					$urlPart .= $smartElement;
				elseif ($i == 'from' || $i == 'to')
					$urlPart .= '-'.$i.'-'.$smartElement;
				elseif ($i == 1)
					$urlPart .= '-is-'.$smartElement;
				else
					$urlPart .= '-or-'.$smartElement;
			}
			$smartPart = $urlPart;
		}
		unset($smartPart);
		return $smartParts;
	}

	public function setCurrencyTag()
	{
		if (
			$this->convertCurrencyId != ''
			&& !empty($this->currencyTagList)
			&& defined('BX_COMP_MANAGED_CACHE')
		)
		{
			$this->currencyTagList[$this->convertCurrencyId] = $this->convertCurrencyId;
			$taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
			foreach ($this->currencyTagList as &$oneCurrency)
				$taggedCache->registerTag('currency_id_'.$oneCurrency);
			unset($oneCurrency);
		}
	}
}