<?php

use Bitrix\Main;
use Bitrix\Main\Localization\Loc;
use Bitrix\Catalog;

class CAllPrice
{
	/**
	 * @deprecated deprecated since catalog 17.6.0
	 *
	 * @param string $ACTION
	 * @param array &$arFields
	 * @param int $ID
	 * @return bool
	 */
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $APPLICATION;

		$currency = false;

		if ($ACTION == "ADD")
		{
			if (!isset($arFields['PRODUCT_ID']))
			{
				$APPLICATION->ThrowException(Loc::getMessage("KGP_EMPTY_PRODUCT"), "EMPTY_PRODUCT_ID");
				return false;
			}
			if (!isset($arFields['CATALOG_GROUP_ID']))
			{
				$APPLICATION->ThrowException(Loc::getMessage("KGP_EMPTY_CATALOG_GROUP"), "EMPTY_CATALOG_GROUP_ID");
				return false;
			}
			if (!isset($arFields['CURRENCY']))
			{
				$APPLICATION->ThrowException(Loc::getMessage("KGP_EMPTY_CURRENCY"), "EMPTY_CURRENCY");
				return false;
			}
			if (!isset($arFields['PRICE']))
				$arFields['PRICE'] = 0;

			if (!isset($arFields['QUANTITY_FROM']))
				$arFields['QUANTITY_FROM'] = false;
			if (!isset($arFields['QUANTITY_TO']))
				$arFields['QUANTITY_TO'] = false;
		}

		$priceExist = isset($arFields['PRICE']);
		$currencyExist = isset($arFields['CURRENCY']);

		if (isset($arFields['PRODUCT_ID']))
		{
			$arFields['PRODUCT_ID'] = (int)$arFields['PRODUCT_ID'];
			if ($arFields['PRODUCT_ID'] <= 0)
			{
				$APPLICATION->ThrowException(Loc::getMessage("KGP_EMPTY_PRODUCT"), "EMPTY_PRODUCT_ID");
				return false;
			}
		}
		if (isset($arFields['CATALOG_GROUP_ID']))
		{
			$arFields['CATALOG_GROUP_ID'] = (int)$arFields['CATALOG_GROUP_ID'];
			if ($arFields['CATALOG_GROUP_ID'] <= 0)
			{
				$APPLICATION->ThrowException(Loc::getMessage("KGP_EMPTY_CATALOG_GROUP"), "EMPTY_CATALOG_GROUP_ID");
				return false;
			}
		}
		if ($priceExist)
			$arFields['PRICE'] = (float)$arFields['PRICE'];
		if ($currencyExist)
		{
			$currency = CCurrency::GetByID($arFields['CURRENCY']);
			if (empty($currency))
			{
				$APPLICATION->ThrowException(Loc::getMessage("KGP_NO_CURRENCY", array('#ID#' => $arFields["CURRENCY"])), "CURRENCY");
				return false;
			}
		}
		if (isset($arFields['PRICE_SCALE']))
		{
			$arFields['PRICE_SCALE'] = (float)$arFields['PRICE_SCALE'];
		}
		else
		{
			if ($priceExist != $currencyExist)
			{
				$iterator = Catalog\PriceTable::getList(array(
					'select' => array('PRICE', 'CURRENCY'),
					'filter' => array('=ID' => $ID)
				));
				$currentValues = $iterator->fetch();
				if (!empty($currentValues))
				{
					$currentPrice = ($priceExist ? $arFields['PRICE'] : (float)$currentValues['PRICE']);
					$currentCurrency = ($currencyExist ? $arFields['CURRENCY'] : $currentValues['CURRENCY']);
					$currency = CCurrency::GetByID($currentCurrency);
					if (!empty($currency))
						$arFields['PRICE_SCALE'] = $currentPrice*$currency['CURRENT_BASE_RATE'];
					unset($currentCurrency, $currentPrice);
				}
				unset($currentValues, $iterator);
			}
			elseif ($priceExist && $currencyExist)
			{
				$arFields['PRICE_SCALE'] = $arFields['PRICE']*$currency['CURRENT_BASE_RATE'];
			}
		}
		unset($currencyExist, $priceExist, $currency);

		if (isset($arFields['QUANTITY_FROM']) && $arFields['QUANTITY_FROM'] !== false)
		{
				$arFields['QUANTITY_FROM'] = (int)$arFields['QUANTITY_FROM'];
				if ($arFields['QUANTITY_FROM'] <= 0)
					$arFields['QUANTITY_FROM'] = false;
		}
		if (isset($arFields['QUANTITY_TO']) && $arFields['QUANTITY_TO'] !== false)
		{
			$arFields['QUANTITY_TO'] = (int)$arFields['QUANTITY_TO'];
			if ($arFields['QUANTITY_TO'] <= 0)
				$arFields['QUANTITY_TO'] = false;
		}

		return true;
	}

	/**
	 * @param int $id
	 * @return array|false
	 */
	public static function GetByID($id)
	{
		global $USER;

		$id = (int)$id;
		if ($id <= 0)
			return false;

		$price = Catalog\PriceTable::getById($id)->fetch();
		if (empty($price))
			return false;

		if ($price['TIMESTAMP_X'] instanceof Main\Type\DateTime)
			$price['TIMESTAMP_X'] = $price['TIMESTAMP_X']->toString();

		$priceTypes = CCatalogGroup::GetListArray();
		$price['CATALOG_GROUP_NAME'] = null;
		if (isset($priceTypes[$price['CATALOG_GROUP_ID']]))
		{
			$price['CATALOG_GROUP_NAME'] = ($priceTypes[$price['CATALOG_GROUP_ID']]['NAME_LANG'] !== null
				? $priceTypes[$price['CATALOG_GROUP_ID']]['NAME_LANG']
				: $priceTypes[$price['CATALOG_GROUP_ID']]['NAME']
			);
		}
		unset($priceTypes);

		$price['CAN_ACCESS'] = 'N';
		$price['CAN_BUY'] = 'N';
		$iterator = Catalog\GroupAccessTable::getList(array(
			'select' => array('ACCESS'),
			'filter' => array(
				'=CATALOG_GROUP_ID' => $price['CATALOG_GROUP_ID'],
				'@GROUP_ID' => (CCatalog::IsUserExists() ? $USER->GetUserGroupArray() : array(2))
			)
		));
		while ($row = $iterator->fetch())
		{
			if ($row['ACCESS'] == Catalog\GroupAccessTable::ACCESS_BUY)
				$price['CAN_ACCESS'] = 'Y';
			elseif ($row['ACCESS'] == Catalog\GroupAccessTable::ACCESS_VIEW)
				$price['CAN_BUY'] = 'Y';
		}
		unset($row, $iterator);

		return $price;
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * @see \Bitrix\Catalog\Model\Price::add
	 *
	 * @param array $fields
	 * @param bool $recount
	 * @return bool|int
	 */
	public static function Add($fields, $recount = false)
	{
		if (!is_array($fields))
			return false;

		if (!isset($fields['PRICE']))
			$fields['PRICE'] = 0;
		self::normalizeFields($fields);

		$data = array(
			'fields' => $fields
		);
		$recount = ($recount === true);
		if ($recount)
			$data['actions'] = array('OLD_RECOUNT' => true);

		$result = Catalog\Model\Price::add($data);
		unset($data);

		$id = false;
		if (!$result->isSuccess())
			self::convertErrors($result);
		else
			$id = (int)$result->getId();
		unset($result);

		return $id;
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * @see \Bitrix\Catalog\Model\Price::update
	 *
	 * @param $id
	 * @param array $fields
	 * @param bool $recount
	 * @return bool|int
	 */
	public static function Update($id, $fields, $recount = false)
	{
		$id = (int)$id;
		if ($id <= 0 || !is_array($fields))
			return false;

		self::normalizeFields($fields);

		$data = array(
			'fields' => $fields
		);
		$recount = ($recount === true);
		if ($recount)
			$data['actions'] = array('OLD_RECOUNT' => true);

		$result = Catalog\Model\Price::update($id, $data);
		unset($data);

		if (!$result->isSuccess())
		{
			$id = false;
			self::convertErrors($result);
		}

		return $id;
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * @see \Bitrix\Catalog\Model\Price::delete
	 *
	 * @param $id
	 * @return bool
	 */
	public static function Delete($id)
	{
		$id = (int)$id;
		if ($id <= 0)
			return false;

		$result = Catalog\Model\Price::delete($id);
		$success = $result->isSuccess();
		if (!$success)
			self::convertErrors($result);
		unset($result);

		return $success;
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * @see \Bitrix\Catalog\Model\Price::getList
	 *
	 * @param $productID
	 * @param bool $quantityFrom
	 * @param bool $quantityTo
	 * @param bool $boolExt
	 * @return array|bool|false|mixed|null
	 */
	public static function GetBasePrice($productID, $quantityFrom = false, $quantityTo = false, $boolExt = true)
	{
		$productID = (int)$productID;
		if ($productID <= 0)
			return false;

		$arBaseType = CCatalogGroup::GetBaseGroup();
		if (empty($arBaseType))
			return false;

		$arFilter = array(
			'PRODUCT_ID' => $productID,
			'CATALOG_GROUP_ID' => $arBaseType['ID']
		);

		if ($quantityFrom !== false)
			$arFilter['QUANTITY_FROM'] = (int)$quantityFrom;
		if ($quantityTo !== false)
			$arFilter['QUANTITY_TO'] = (int)$quantityTo;

		if ($boolExt === false)
		{
			$arSelect = array('ID', 'PRODUCT_ID', 'EXTRA_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY', 'TIMESTAMP_X',
				'QUANTITY_FROM', 'QUANTITY_TO', 'TMP_ID'
			);
		}
		else
		{
			$arSelect = array('ID', 'PRODUCT_ID', 'EXTRA_ID', 'CATALOG_GROUP_ID', 'PRICE', 'CURRENCY', 'TIMESTAMP_X',
				'QUANTITY_FROM', 'QUANTITY_TO', 'TMP_ID',
				'PRODUCT_QUANTITY', 'PRODUCT_QUANTITY_TRACE', 'PRODUCT_CAN_BUY_ZERO',
				'PRODUCT_NEGATIVE_AMOUNT_TRACE', 'PRODUCT_WEIGHT', 'ELEMENT_IBLOCK_ID'
			);
		}

		$db_res = CPrice::GetListEx(
			array('QUANTITY_FROM' => 'ASC', 'QUANTITY_TO' => 'ASC'),
			$arFilter,
			false,
			array('nTopCount' => 1),
			$arSelect
		);
		if ($res = $db_res->Fetch())
		{
			$res['BASE'] = 'Y';
			$res['CATALOG_GROUP_NAME'] = $arBaseType['NAME'];
			return $res;
		}

		return false;
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * @see \Bitrix\Catalog\Model\Price::add and \Bitrix\Catalog\Model\Price::update
	 *
	 * @param int $ProductID
	 * @param int|float $Price
	 * @param string $Currency
	 * @param bool $quantityFrom
	 * @param bool $quantityTo
	 * @param bool $bGetID
	 * @return bool|int
	 */
	public static function SetBasePrice($ProductID, $Price, $Currency, $quantityFrom = false, $quantityTo = false, $bGetID = false)
	{
		$bGetID = ($bGetID == true);

		$arFields = array();
		$arFields["PRICE"] = (float)$Price;
		$arFields["CURRENCY"] = $Currency;
		$arFields["QUANTITY_FROM"] = ($quantityFrom == false ? false : (int)$quantityFrom);
		$arFields["QUANTITY_TO"] = ($quantityTo == false ? false : (int)$quantityTo);
		$arFields["EXTRA_ID"] = false;

		if ($arBasePrice = CPrice::GetBasePrice($ProductID, $quantityFrom, $quantityTo, false))
		{
			$ID = CPrice::Update($arBasePrice["ID"], $arFields);
		}
		else
		{
			$arBaseGroup = CCatalogGroup::GetBaseGroup();
			$arFields["CATALOG_GROUP_ID"] = $arBaseGroup["ID"];
			$arFields["PRODUCT_ID"] = $ProductID;

			$ID = CPrice::Add($arFields);
		}
		if (!$ID)
			return false;

		return ($bGetID ? $ID : true);
	}

	public static function ReCalculate($TYPE, $ID, $VAL)
	{
		$ID = (int)$ID;
		if ($ID <= 0)
			return;

		$iblockList = array();

		if ($TYPE == 'EXTRA')
		{
			$baseType = CCatalogGroup::GetBaseGroup();
			if (empty($baseType))
				return;

			$db_res = CPrice::GetListEx(
				array(),
				array('EXTRA_ID' => $ID),
				false,
				false,
				array('ID', 'PRODUCT_ID', 'EXTRA_ID', 'QUANTITY_FROM', 'QUANTITY_TO')
			);
			while ($res = $db_res->Fetch())
			{
				$parentFilter = array(
					'PRODUCT_ID' => $res['PRODUCT_ID'],
					'CATALOG_GROUP_ID' => $baseType['ID'],
					'QUANTITY_FROM' => ($res['QUANTITY_FROM'] === null ? false : $res['QUANTITY_FROM']),
					'QUANTITY_TO' => ($res['QUANTITY_TO'] === null ? false : $res['QUANTITY_TO'])
				);
				$parentIterator = CPrice::GetListEx(
					array(),
					$parentFilter,
					false,
					false,
					array('ID', 'PRODUCT_ID', 'PRICE', 'CURRENCY', 'ELEMENT_IBLOCK_ID')
				);
				$basePrice = $parentIterator->Fetch();
				if (!empty($basePrice))
				{
					$basePrice['ELEMENT_IBLOCK_ID'] = (int)$basePrice['ELEMENT_IBLOCK_ID'];
					$fields = array(
						'PRICE' => roundex($basePrice['PRICE'] * (1 + 1 * $VAL / 100), 2),
						'CURRENCY' => $basePrice['CURRENCY']
					);
					CPrice::Update($res['ID'], $fields);
					unset($arFields);
					$iblockList[$basePrice['ELEMENT_IBLOCK_ID']] = $basePrice['ELEMENT_IBLOCK_ID'];
				}
				unset($basePrice, $parentIterator);
			}
			unset($res, $db_res, $baseType);
		}
		else
		{
			$db_res = CPrice::GetListEx(
				array(),
				array("PRODUCT_ID" => $ID),
				false,
				false,
				array('ID', 'PRODUCT_ID', 'EXTRA_ID', 'ELEMENT_IBLOCK_ID')
			);
			while ($res = $db_res->Fetch())
			{
				$res['ELEMENT_IBLOCK_ID'] = (int)$res['ELEMENT_IBLOCK_ID'];
				$res["EXTRA_ID"] = (int)$res["EXTRA_ID"];
				if ($res["EXTRA_ID"] > 0)
				{
					$res1 = CExtra::GetByID($res["EXTRA_ID"]);
					$arFields = array(
						"PRICE" => $VAL * (1 + 1 * $res1["PERCENTAGE"] / 100),
					);
					CPrice::Update($res["ID"], $arFields);
					$iblockList[$res['ELEMENT_IBLOCK_ID']] = $res['ELEMENT_IBLOCK_ID'];
				}
			}
			unset($res, $db_res);
		}

		if (!empty($iblockList) && Main\Loader::includeModule('iblock'))
		{
			foreach ($iblockList as &$iblock)
				CIblock::clearIblockTagCache($iblock);
			unset($iblock);
		}
		unset($iblockList);
	}

	public static function OnCurrencyDelete($Currency)
	{
		global $DB;
		if ($Currency == '')
			return false;

		$strSql = "DELETE FROM b_catalog_price WHERE CURRENCY = '".$DB->ForSql($Currency)."'";
		return $DB->Query($strSql, true);
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * @see \Bitrix\Catalog\Model\Product::delete
	 *
	 * @param int $ProductID
	 * @return bool
	 */
	public static function OnIBlockElementDelete($ProductID)
	{
		return true;
	}

	public static function DeleteByProduct($ProductID, $arExceptionIDs = array())
	{
		global $DB;

		$ProductID = (int)$ProductID;
		if ($ProductID <= 0)
			return false;
		foreach (GetModuleEvents("catalog", "OnBeforeProductPriceDelete", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, array($ProductID, &$arExceptionIDs))===false)
				return false;
		}

		if (!empty($arExceptionIDs))
			Main\Type\Collection::normalizeArrayValuesByInt($arExceptionIDs);

		if (!empty($arExceptionIDs))
		{
			$strSql = "DELETE FROM b_catalog_price WHERE PRODUCT_ID = ".$ProductID." AND ID NOT IN (".implode(',',$arExceptionIDs).")";
		}
		else
		{
			$strSql = "DELETE FROM b_catalog_price WHERE PRODUCT_ID = ".$ProductID;
		}

		$mxRes = $DB->Query($strSql, true);

		foreach (GetModuleEvents("catalog", "OnProductPriceDelete", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ProductID,$arExceptionIDs));

		Catalog\Product\Sku::calculatePrice($ProductID, null, null, array());

		return $mxRes;
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * @see \Bitrix\Catalog\Model\Price::add and \Bitrix\Catalog\Model\Price::update
	 *
	 * @param array &$arFields
	 * @return void
	 */
	public static function ReCountForBase(&$arFields)
	{
		static $arExtraList = array();

		$arFilter = array('PRODUCT_ID' => $arFields['PRODUCT_ID'],'!CATALOG_GROUP_ID' => $arFields['CATALOG_GROUP_ID']);
		if (isset($arFields['QUANTITY_FROM']))
			$arFilter['QUANTITY_FROM'] = $arFields['QUANTITY_FROM'];
		if (isset($arFields['QUANTITY_TO']))
			$arFilter['QUANTITY_TO'] = $arFields['QUANTITY_TO'];

		$rsPrices = CPrice::GetListEx(
			array('CATALOG_GROUP_ID' => 'ASC',"QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC"),
			$arFilter,
			false,
			false,
			array('ID','EXTRA_ID')
		);
		while ($arPrice = $rsPrices->Fetch())
		{
			$arPrice['EXTRA_ID'] = (int)$arPrice['EXTRA_ID'];
			if ($arPrice['EXTRA_ID'] > 0)
			{
				$boolSearch = isset($arExtraList[$arPrice['EXTRA_ID']]);
				if (!$boolSearch)
				{
					$arExtra = CExtra::GetByID($arPrice['EXTRA_ID']);
					if (!empty($arExtra))
					{
						$boolSearch = true;
						$arExtraList[$arExtra['ID']] = (float)$arExtra['PERCENTAGE'];
					}
				}
				if ($boolSearch)
				{
					$arNewPrice = array(
						'CURRENCY' => $arFields['CURRENCY'],
						'PRICE' => roundEx($arFields["PRICE"] * (1 + $arExtraList[$arPrice['EXTRA_ID']]/100), CATALOG_VALUE_PRECISION),
					);
					CPrice::Update($arPrice['ID'],$arNewPrice,false);
				}
				unset($boolSearch);
			}
		}
	}

	/**
	 * @deprecated deprecated since catalog 17.6.0
	 * @see \Bitrix\Catalog\Model\Price::add and \Bitrix\Catalog\Model\Price::update
	 *
	 * @param array &$arFields
	 * @param bool &$boolBase
	 * @return void
	 */
	public static function ReCountFromBase(&$arFields, &$boolBase)
	{
		$arBaseGroup = CCatalogGroup::GetBaseGroup();
		if (!empty($arBaseGroup))
		{
			if ($arFields['CATALOG_GROUP_ID'] == $arBaseGroup['ID'])
			{
				$boolBase = true;
			}
			else
			{
				if (!empty($arFields['EXTRA_ID']) && intval($arFields['EXTRA_ID']) > 0)
				{
					$arExtra = CExtra::GetByID($arFields['EXTRA_ID']);
					if (!empty($arExtra))
					{
						$arExtra["PERCENTAGE"] = (float)$arExtra["PERCENTAGE"];
						$arFilter = array('PRODUCT_ID' => $arFields['PRODUCT_ID'],'CATALOG_GROUP_ID' => $arBaseGroup['ID']);
						if (isset($arFields['QUANTITY_FROM']))
							$arFilter['QUANTITY_FROM'] = $arFields['QUANTITY_FROM'];
						if (isset($arFields['QUANTITY_TO']))
							$arFilter['QUANTITY_TO'] = $arFields['QUANTITY_TO'];
						$rsBasePrices = CPrice::GetListEx(
							array("QUANTITY_FROM" => "ASC", "QUANTITY_TO" => "ASC"),
							$arFilter,
							false,
							array('nTopCount' => 1),
							array('PRICE','CURRENCY')
						);
						if ($arBasePrice = $rsBasePrices->Fetch())
						{
							$arFields['CURRENCY'] = $arBasePrice['CURRENCY'];
							$arFields['PRICE'] = roundEx($arBasePrice["PRICE"] * (1 + $arExtra["PERCENTAGE"]/100), CATALOG_VALUE_PRECISION);
							$currency = CCurrency::GetByID($arBasePrice['CURRENCY']);
							if (!empty($currency))
								$arFields['PRICE_SCALE'] = $arFields['PRICE']*$currency['CURRENT_BASE_RATE'];
						}
						else
						{
							$arFields['EXTRA_ID'] = 0;
						}
					}
					else
					{
						$arFields['EXTRA_ID'] = 0;
					}
				}
			}
		}
	}

	private static function convertErrors(Main\Entity\Result $result): void
	{
		global $APPLICATION;

		$oldMessages = [];
		foreach ($result->getErrorMessages() as $errorText)
		{
			$oldMessages[] = ['text' => $errorText];
		}
		unset($errorText);

		if (!empty($oldMessages))
		{
			$error = new CAdminException($oldMessages);
			$APPLICATION->ThrowException($error);
			unset($error);
		}
		unset($oldMessages);
	}

	private static function normalizeFields(array &$fields): void
	{
		if (isset($fields['QUANTITY_FROM']))
		{
			if (
				$fields['QUANTITY_FROM'] === ''
				|| $fields['QUANTITY_FROM'] === false
				|| $fields['QUANTITY_FROM'] === 0
			)
			{
				$fields['QUANTITY_FROM'] = null;
			}
		}
		if (isset($fields['QUANTITY_TO']))
		{
			if (
				$fields['QUANTITY_TO'] === ''
				|| $fields['QUANTITY_TO'] === false
				|| $fields['QUANTITY_TO'] === 0
			)
			{
				$fields['QUANTITY_TO'] = null;
			}
		}
		if (isset($fields['EXTRA_ID']))
		{
			if ($fields['EXTRA_ID'] === '' || $fields['EXTRA_ID'] === false)
			{
				$fields['EXTRA_ID'] = null;
			}
		}
	}
}
