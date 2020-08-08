<?
IncludeModuleLangFile(__FILE__);

class CAllSaleTax
{
	public static function DoProcessOrderBasket(&$arOrder, $arOptions, &$arErrors)
	{
		if ((!array_key_exists("TAX_LOCATION", $arOrder) || intval($arOrder["TAX_LOCATION"]) <= 0) && (!$arOrder["USE_VAT"] || $arOrder["USE_VAT"]!="Y"))
			return;

		$arOrder["TAX_LOCATION"] = \CSaleLocation::tryTranslateIDToCode($arOrder["TAX_LOCATION"]);

		static::calculateTax($arOrder, $arOptions, $arErrors);
	}

	/**
	 * @internal
	 * @param $arOrder
	 * @param $arOptions
	 * @param $arErrors
	 */
	public static function calculateTax(&$arOrder, $arOptions, &$arErrors)
	{
		if ((!array_key_exists("TAX_LOCATION", $arOrder) || strval(trim($arOrder["TAX_LOCATION"])) == "") && (!$arOrder["USE_VAT"] || $arOrder["USE_VAT"]!="Y"))
			return;

		if (!$arOrder["USE_VAT"])
		{
			if (!array_key_exists("TAX_EXEMPT", $arOrder))
			{
				$arOrder["TAX_EXEMPT"] = array();
				$arUserGroups = CUser::GetUserGroup($arOrder["USER_ID"]);

				$dbTaxExemptList = CSaleTax::GetExemptList(array("GROUP_ID" => $arUserGroups));
				while ($TaxExemptList = $dbTaxExemptList->Fetch())
				{
					if (!in_array(intval($TaxExemptList["TAX_ID"]), $arOrder["TAX_EXEMPT"]))
						$arOrder["TAX_EXEMPT"][] = intval($TaxExemptList["TAX_ID"]);
				}
			}

			if (!array_key_exists("TAX_LIST", $arOrder))
			{
				$arOrder["TAX_LIST"] = array();

				static $proxyOrderTaxList = array();

				$proxyOrderTaxKey = $arOrder["SITE_ID"]."|".$arOrder["PERSON_TYPE_ID"]."|".$arOrder["TAX_LOCATION"];

				if (isset($proxyOrderTaxList[$proxyOrderTaxKey]) && is_array($proxyOrderTaxList[$proxyOrderTaxKey]))
				{
					$arOrder["TAX_LIST"] = $proxyOrderTaxList[$proxyOrderTaxKey];
				}
				else
				{
					$dbTaxRate = CSaleTaxRate::GetList(
						array("APPLY_ORDER" => "ASC"),
						array(
							"LID" => $arOrder["SITE_ID"],
							"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
							"ACTIVE" => "Y",
							"LOCATION_CODE" => $arOrder["TAX_LOCATION"],
						)
					);
					while ($arTaxRate = $dbTaxRate->GetNext())
					{
						if (!in_array(intval($arTaxRate["TAX_ID"]), $arOrder["TAX_EXEMPT"]))
						{
							if ($arTaxRate["IS_PERCENT"] != "Y")
							{
								$arTaxRate["VALUE"] = \Bitrix\Sale\PriceMaths::roundPrecision(CCurrencyRates::ConvertCurrency($arTaxRate["VALUE"], $arTaxRate["CURRENCY"], $arOrder["CURRENCY"]));
								$arTaxRate["CURRENCY"] = $arOrder["CURRENCY"];
							}
							$arOrder["TAX_LIST"][] = $arTaxRate;
						}
					}

					$proxyOrderTaxList[$proxyOrderTaxKey] = $arOrder["TAX_LIST"];
				}
			}
			else
			{
				if (!empty($arOrder["TAX_LIST"]) && is_array($arOrder["TAX_LIST"]))
				{
					foreach ($arOrder["TAX_LIST"] as &$taxValue)
					{
						if (isset($taxValue['VALUE_MONEY']))
							unset($taxValue['VALUE_MONEY']);
					}
					unset($taxValue);
				}
			}

			if (!empty($arOrder["TAX_LIST"]))
			{
				if (!empty($arOrder["BASKET_ITEMS"]) && is_array($arOrder["BASKET_ITEMS"]))
				{
					foreach ($arOrder["BASKET_ITEMS"] as $arItem)
					{
						CSaleOrderTax::CountTaxes(
							$arItem["PRICE"] * $arItem["QUANTITY"],
							$arOrder["TAX_LIST"],
							$arOrder["CURRENCY"]
						);

						foreach ($arOrder["TAX_LIST"] as &$arTax)
						{
							$arTax["VALUE_MONEY"] += $arTax["TAX_VAL"];
							$arTax["VALUE_MONEY_FORMATED"] = SaleFormatCurrency($arTax["VALUE_MONEY"], $arOrder["CURRENCY"]);
						}
						unset($arTax);
					}

					foreach ($arOrder["TAX_LIST"] as $arTax)
					{
						if ($arTax["IS_IN_PRICE"] != "Y"
							|| (!empty($arOptions['ENABLE_INCLUSIVE_TAX']) && $arOptions['ENABLE_INCLUSIVE_TAX'] == "Y"))
						{
							$arOrder["TAX_PRICE"] += $arTax["VALUE_MONEY"];
						}
					}
				}
				else
				{
					$arOrder["TAX_LIST"] = array();
					$arOrder["TAX_PRICE"] = 0;
				}


			}
		}
		else
		{
			if (!array_key_exists("TAX_LIST", $arOrder))
			{
				$arOrder["TAX_LIST"][] = array(
					"NAME" => GetMessage("SOA_VAT"),
					"IS_PERCENT" => "Y",
					"VALUE" => $arOrder["VAT_RATE"] * 100,
					"VALUE_FORMATED" => "(".($arOrder["VAT_RATE"] * 100)."%, ".GetMessage("SOA_VAT_INCLUDED").")",
					"VALUE_MONEY" => $arOrder["VAT_SUM"],
					"VALUE_MONEY_FORMATED" => SaleFormatCurrency($arOrder["VAT_SUM"], $arOrder["CURRENCY"]),
					"APPLY_ORDER" => 100,
					"IS_IN_PRICE" => "Y",
					"CODE" => "VAT"
				);
			}
		}

		$arOrder["TAX_PRICE"] = \Bitrix\Sale\PriceMaths::roundPrecision($arOrder["TAX_PRICE"]);
	}

	public static function DoProcessOrderDelivery(&$arOrder, $arOptions, &$arErrors)
	{
		if ((!array_key_exists("TAX_LOCATION", $arOrder) || intval($arOrder["TAX_LOCATION"]) <= 0) && (!$arOrder["USE_VAT"] || $arOrder["USE_VAT"]!="Y"))
			return;

		$arOrder["TAX_LOCATION"] = \CSaleLocation::tryTranslateIDToCode($arOrder["TAX_LOCATION"]);

		static::calculateDeliveryTax($arOrder, $arOptions, $arErrors);
	}

	/**
	 * @internal
	 * @param $arOrder
	 * @param $arOptions
	 * @param $arErrors
	 */
	public static function calculateDeliveryTax(&$arOrder, $arOptions, &$arErrors)
	{
		if ((!array_key_exists("TAX_LOCATION", $arOrder) || strval(trim($arOrder["TAX_LOCATION"])) == "") && (!$arOrder["USE_VAT"] || $arOrder["USE_VAT"]!="Y"))
			return;

		if (!array_key_exists("COUNT_DELIVERY_TAX", $arOptions))
			$arOptions["COUNT_DELIVERY_TAX"] = COption::GetOptionString("sale", "COUNT_DELIVERY_TAX", "N");

		if (doubleval($arOrder["DELIVERY_PRICE"]) <= 0 || $arOptions["COUNT_DELIVERY_TAX"] != "Y")
			return;

		if (!$arOrder["USE_VAT"] || $arOrder["USE_VAT"] != "Y")
		{
			if (!array_key_exists("TAX_EXEMPT", $arOrder))
			{
				$arUserGroups = CUser::GetUserGroup($arOrder["USER_ID"]);

				$dbTaxExemptList = CSaleTax::GetExemptList(array("GROUP_ID" => $arUserGroups));
				while ($TaxExemptList = $dbTaxExemptList->Fetch())
				{
					if (!in_array(intval($TaxExemptList["TAX_ID"]), $arOrder["TAX_EXEMPT"]))
						$arOrder["TAX_EXEMPT"][] = intval($TaxExemptList["TAX_ID"]);
				}
			}

			if (!array_key_exists("TAX_LIST", $arOrder))
			{
				$arOrder["TAX_LIST"] = array();

				$dbTaxRate = CSaleTaxRate::GetList(
					array("APPLY_ORDER" => "ASC"),
					array(
						"LID" => $arOrder["SITE_ID"],
						"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
						"ACTIVE" => "Y",
						"LOCATION_CODE" => $arOrder["TAX_LOCATION"],
					)
				);
				while ($arTaxRate = $dbTaxRate->GetNext())
				{
					if (is_array($arOrder["TAX_EXEMPT"]) && !in_array(intval($arTaxRate["TAX_ID"]), $arOrder["TAX_EXEMPT"]))
					{
						if ($arTaxRate["IS_PERCENT"] != "Y")
						{
							$arTaxRate["VALUE"] = \Bitrix\Sale\PriceMaths::roundPrecision(CurrencyRates::ConvertCurrency($arTaxRate["VALUE"], $arTaxRate["CURRENCY"], $arOrder["CURRENCY"]));
							$arTaxRate["CURRENCY"] = $arOrder["CURRENCY"];
						}
						$arOrder["TAX_LIST"][] = $arTaxRate;
					}
				}
			}

			if (count($arOrder["TAX_LIST"]) > 0)
			{
				CSaleOrderTax::CountTaxes(
					$arOrder["DELIVERY_PRICE"],
					$arOrder["TAX_LIST"],
					$arOrder["CURRENCY"]
				);

				$arOrder["TAX_PRICE"] = 0;
				foreach ($arOrder["TAX_LIST"] as &$arTax)
				{
					$arTax["VALUE_MONEY"] += \Bitrix\Sale\PriceMaths::roundPrecision($arTax["TAX_VAL"]);
					$arTax['VALUE_MONEY_FORMATED'] = SaleFormatCurrency($arTax["VALUE_MONEY"], $arOrder["CURRENCY"]);

					if ($arTax["IS_IN_PRICE"] != "Y"
						|| (!empty($arOptions['ENABLE_INCLUSIVE_TAX']) && $arOptions['ENABLE_INCLUSIVE_TAX'] == "Y"))
					{
						$arOrder["TAX_PRICE"] += $arTax["VALUE_MONEY"];
					}

				}
				unset($arTax);

			}
		}
		else
		{

			$deliveryVat = $arOrder["DELIVERY_PRICE"] * $arOrder["VAT_RATE"] / (1 + $arOrder["VAT_RATE"]);

			$arOrder["VAT_SUM"] += $deliveryVat;
			$arOrder["VAT_DELIVERY"] += $deliveryVat;

			//if (!array_key_exists("TAX_LIST", $arOrder))
			//{
				$arOrder["TAX_LIST"][0] = array(
					"NAME" => GetMessage("SOA_VAT"),
					"IS_PERCENT" => "Y",
					"VALUE" => $arOrder["VAT_RATE"] * 100,
					"VALUE_FORMATED" => "(".($arOrder["VAT_RATE"] * 100)."%, ".GetMessage("SOA_VAT_INCLUDED").")",
					"VALUE_MONEY" => $arOrder["VAT_SUM"],
					"VALUE_MONEY_FORMATED" => SaleFormatCurrency($arOrder["VAT_SUM"], $arOrder["CURRENCY"]),
					"APPLY_ORDER" => 100,
					"IS_IN_PRICE" => "Y",
					"CODE" => "VAT"
				);
			//}
		}

		$arOrder["TAX_PRICE"] = \Bitrix\Sale\PriceMaths::roundPrecision($arOrder["TAX_PRICE"]);
		$arOrder["VAT_SUM"] = \Bitrix\Sale\PriceMaths::roundPrecision($arOrder["VAT_SUM"]);
		$arOrder["VAT_DELIVERY"] = \Bitrix\Sale\PriceMaths::roundPrecision($arOrder["VAT_DELIVERY"]);

	}

	static function DoSaveOrderTax($orderId, $taxList, &$arErrors)
	{
		$duplicateList = array();
		$idList = array();

		/** @var CSaleOrderTax $orderTaxClass */
		$orderTaxClass = static::getOrderTaxEntityName();
		$res = $orderTaxClass::GetList(
			array('ID' => 'ASC'),
			array("ORDER_ID" => $orderId),
			false,
			false,
			array("ID", "TAX_NAME", "CODE")
		);
		while ($data = $res->Fetch())
		{
			$hash = $data["TAX_NAME"]."|".$data["CODE"];
			if (!array_key_exists($hash, $idList))
			{
				$idList[$data["TAX_NAME"]."|".$data["CODE"]] = $data["ID"];
			}
			else
			{
				$duplicateList[$hash] = $data['ID'];
			}
		}

		$isChanged = false;

		if (is_array($taxList))
		{
			foreach ($taxList as $itemData)
			{
				$fields = array(
					"ORDER_ID" => $orderId,
					"TAX_NAME" => $itemData["NAME"],
					"IS_PERCENT" => $itemData["IS_PERCENT"],
					"VALUE" => $itemData["VALUE"],
					"VALUE_MONEY" => $itemData["VALUE_MONEY"],
					"APPLY_ORDER" => $itemData["APPLY_ORDER"],
					"IS_IN_PRICE" => $itemData["IS_IN_PRICE"],
					"CODE" => $itemData["CODE"]
				);

				$hash = $itemData["NAME"]."|".$itemData["CODE"];
				$isNew = false;

				if (array_key_exists($hash, $idList))
				{
					/** @var CSaleOrderTax $orderTaxClass */
					$orderTaxClass = static::getOrderTaxEntityName();
					$taxId = $orderTaxClass::Update($idList[$hash], $fields);
					unset($idList[$hash]);
				}
				elseif (!array_key_exists($hash, $duplicateList))
				{
					$isNew = true;
					/** @var CSaleOrderTax $orderTaxClass */
					$orderTaxClass = static::getOrderTaxEntityName();
					$taxId = $orderTaxClass::Add($fields);
				}

				if ($orderId > 0)
				{
					/** @var \Bitrix\Crm\Invoice\InvoiceHistory $historyClass */
					$historyClass = static::getHistoryEntityName();
					$historyClass::addLog(
						'TAX',
						$orderId,
						$isNew ? 'TAX_ADD' : 'TAX_UPDATE',
						$taxId,
						null,
						array(
							"NAME" => $itemData["NAME"],
							"CODE" => $itemData["CODE"]
						),
						$historyClass::SALE_ORDER_HISTORY_LOG_LEVEL_1
					);

					$isChanged = true;
				}
			}
		}

		foreach ($idList as $code => $id)
		{
			/** @var CSaleOrderTax $orderTaxClass */
			$orderTaxClass = static::getOrderTaxEntityName();
			$orderTaxClass::Delete($id);
			if ($orderId > 0)
			{
				/** @var \Bitrix\Crm\Invoice\InvoiceHistory $className */
				$historyClass = static::getHistoryEntityName();
				$historyClass::addLog(
					'TAX',
					$orderId,
					'TAX_DELETED',
					$id,
					null,
					array(),
					$historyClass::SALE_ORDER_HISTORY_LOG_LEVEL_1
				);
			}
		}

		if (!empty($duplicateList))
		{
			foreach ($duplicateList as $hash => $id)
			{
				/** @var CSaleOrderTax $orderTaxClass */
				$orderTaxClass = static::getOrderTaxEntityName();
				$orderTaxClass::Delete($id);


				/** @var \Bitrix\Crm\Invoice\InvoiceHistory $className */
				$historyClass = static::getHistoryEntityName();
				$historyClass::addLog(
					'TAX',
					$orderId,
					'TAX_DUPLICATE_DELETED',
					$id,
					null,
					array(),
					$historyClass::SALE_ORDER_HISTORY_LOG_LEVEL_1
				);
			}
		}

		if ($isChanged)
		{
			/** @var \Bitrix\Crm\Invoice\InvoiceHistory $className */
			$historyClass = static::getHistoryEntityName();
			$historyClass::addAction(
				'TAX',
				$orderId,
				"TAX_SAVED"
			);
		}
	}

	function CheckFields($ACTION, &$arFields)
	{
		global $DB;

		if ((is_set($arFields, "LID") || $ACTION=="ADD") && $arFields["LID"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGT_EMPTY_SITE"), "ERROR_NO_LID");
			return false;
		}
		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && $arFields["NAME"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGT_EMPTY_NAME"), "ERROR_NO_NAME");
			return false;
		}

		if (is_set($arFields, "LID"))
		{
			$dbSite = CSite::GetByID($arFields["LID"]);
			if (!$dbSite->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["LID"], GetMessage("SKGT_NO_SITE")), "ERROR_NO_SITE");
				return false;
			}
		}

		if ((is_set($arFields, "CODE") || $ACTION=="ADD") && $arFields["CODE"] == '')
			$arFields["CODE"] = false;

		return true;
	}

	function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);

		if (!CSaleTax::CheckFields("UPDATE", $arFields)) return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_tax", $arFields);
		$strSql = "UPDATE b_sale_tax SET ".
			"	TIMESTAMP_X = ".$DB->GetNowFunction().", ".
			"	".$strUpdate." ".
			"WHERE ID = ".$ID." ";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $ID;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);

		$db_taxrates = CSaleTaxRate::GetList(Array(), Array("TAX_ID"=>$ID));
		while ($ar_taxrates = $db_taxrates->Fetch())
		{
			CSaleTaxRate::Delete($ar_taxrates["ID"]);
		}

		$DB->Query("DELETE FROM b_sale_tax_exempt2group WHERE TAX_ID = ".$ID."", true);
		return $DB->Query("DELETE FROM b_sale_tax WHERE ID = ".$ID."", true);
	}

	function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		$strSql =
			"SELECT ID, LID, NAME, CODE, DESCRIPTION, ".$DB->DateToCharFunction("TIMESTAMP_X", "FULL")." as TIMESTAMP_X ".
			"FROM b_sale_tax ".
			"WHERE ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function GetList($arOrder=Array("NAME"=>"ASC"), $arFilter=Array())
	{
		global $DB;
		$arSqlSearch = Array();

		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$countFiltersKeys = count($filter_keys);
		for ($i=0; $i<$countFiltersKeys; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if ($val == '') continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = mb_substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch (ToUpper($key))
			{
				case "ID":
					$arSqlSearch[] = "T.ID ".($bInvert?"<>":"=")." ".intval($val)." ";
					break;
				case "LID":
					$arSqlSearch[] = "T.LID ".($bInvert?"<>":"=")." '".$val."' ";
					break;
				case "CODE":
					$arSqlSearch[] = "T.CODE ".($bInvert?"<>":"=")." '".$val."' ";
					break;
			}
		}

		$strSqlSearch = "";
		$countSqlSearch = count($arSqlSearch);
		for($i=0; $i<$countSqlSearch; $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql = 
			"SELECT T.ID, T.LID, T.NAME, T.CODE, T.DESCRIPTION, ".$DB->DateToCharFunction("T.TIMESTAMP_X", "FULL")." as TIMESTAMP_X ".
			"FROM b_sale_tax T ".
			"WHERE 1 = 1 ".
			"	".$strSqlSearch." ";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = ToUpper($by);
			$order = ToUpper($order);
			if ($order!="ASC")
				$order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " T.ID ".$order." ";
			elseif ($by == "LID") $arSqlOrder[] = " T.LID ".$order." ";
			elseif ($by == "CODE") $arSqlOrder[] = " T.CODE ".$order." ";
			elseif ($by == "TIMESTAMP_X") $arSqlOrder[] = " T.TIMESTAMP_X ".$order." ";
			else
			{
				$arSqlOrder[] = " T.NAME ".$order." ";
				$by = "NAME";
			}
		}
		$strSqlOrder = "";

		$countSqlOrder = count($arSqlOrder);
		DelDuplicateSort($arSqlOrder); for ($i=0; $i<$countSqlOrder; $i++)
		{
			if ($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}
		$strSql .= $strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetExemptList($arFilter = array())
	{
		global $DB;
		$arSqlSearch = Array();

		if (!is_array($arFilter)) 
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$countFilterKeys = count($filter_keys);
		for ($i = 0; $i < $countFilterKeys; $i++)
		{
			$vals = $arFilter[$filter_keys[$i]];
			if (!is_array($vals))
				$vals = array($vals);

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = mb_substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			$arSqlSearch_tmp = array();

			$countVals = count($vals);
			for ($j = 0; $j < $countVals; $j++)
			{
				$val = $vals[$j];

				switch (ToUpper($key))
				{
					case "GROUP_ID":
						$arSqlSearch_tmp[] = "TE2G.GROUP_ID ".($bInvert?"<>":"=")." ".intval($val)." ";
						break;
					case "TAX_ID":
						$arSqlSearch_tmp[] = "TE2G.TAX_ID ".($bInvert?"<>":"=")." ".intval($val)." ";
						break;
				}
			}

			$strSqlSearch_tmp = "";
			$countSqlSearchTmp = count($arSqlSearch_tmp);
			for ($j = 0; $j < $countSqlSearchTmp; $j++)
			{
				if ($j > 0)
					$strSqlSearch_tmp .= ($bInvert ? " AND " : " OR ");
				$strSqlSearch_tmp .= "(".$arSqlSearch_tmp[$j].")";
			}

			if ($strSqlSearch_tmp != "")
				$arSqlSearch[] = "(".$strSqlSearch_tmp.")";
		}

		$strSqlSearch = "";
		$countSqlSearch = count($arSqlSearch);
		for ($i = 0; $i < $countSqlSearch; $i++)
		{
			$strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql = 
			"SELECT TE2G.GROUP_ID, TE2G.TAX_ID ".
			"FROM b_sale_tax_exempt2group TE2G ".
			"WHERE 1 = 1 ".
			"	".$strSqlSearch." ";

		$strSql .= $strSqlOrder;
		//echo "!1!=".$strSql.";<br>";

		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function AddExempt($arFields)
	{
		global $DB;

		$arFields["GROUP_ID"] = intval($arFields["GROUP_ID"]);
		$arFields["TAX_ID"] = intval($arFields["TAX_ID"]);

		if ($arFields["GROUP_ID"]<=0 || $arFields["TAX_ID"]<=0)
			return False;

		$strSql =
			"INSERT INTO b_sale_tax_exempt2group(GROUP_ID, TAX_ID) ".
			"VALUES(".$arFields["GROUP_ID"].", ".$arFields["TAX_ID"].")";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return True;
	}

	function DeleteExempt($arFields)
	{
		global $DB;

		$strSql = "DELETE FROM b_sale_tax_exempt2group WHERE ";

		$arSqlSearch = Array();

		if (!is_array($arFields))
			return False;
		else
			$filter_keys = array_keys($arFields);

		$countFilterKeys = count($filter_keys);
		for ($i=0; $i<$countFilterKeys; $i++)
		{
			$val = $arFields[$filter_keys[$i]];
			if (intval($val)<=0) continue;
			$key = $filter_keys[$i];
			$arSqlSearch[] = " ".$key." = ".intval($val)." ";
		}

		$countSqlSearch = count($arSqlSearch);
		if ($countSqlSearch<=0)
			return False;

		$strSqlSearch = "";
		
		for ($i=0; $i<$countSqlSearch; $i++)
		{
			if ($i==0) $strSqlSearch .= " ";
			else $strSqlSearch .= " AND ";
			$strSqlSearch .= " (".$arSqlSearch[$i].") ";
		}

		$strSql .= $strSqlSearch;

		return $DB->Query($strSql, true);
	}

	/**
	 * @return string
	 */
	protected static function getOrderTaxEntityName()
	{
		return CSaleOrderTax::class;
	}

	/**
	 * @return string
	 */
	protected static function getHistoryEntityName()
	{
		return \Bitrix\Sale\OrderHistory::class;
	}
}
?>