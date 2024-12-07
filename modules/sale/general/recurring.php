<?php

use Bitrix\Main\Application;
use Bitrix\Sale;

IncludeModuleLangFile(__FILE__);

/***********************************************************************/
/***********  CSaleRecurring  ******************************************/
/***********************************************************************/
$GLOBALS["SALE_RECURRING"] = Array();

class CAllSaleRecurring
{
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		if ((is_set($arFields, "USER_ID") || $ACTION=="ADD") && intval($arFields["USER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGR_EMPTY_USER_ID"), "NO_USER_ID");
			return false;
		}
		if ((is_set($arFields, "NEXT_DATE") || $ACTION=="ADD") && $arFields["NEXT_DATE"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGR_EMPTY_NEXT_DATE"), "NO_NEXT_DATE");
			return false;
		}
		if ((is_set($arFields, "ORDER_ID") || $ACTION=="ADD") && intval($arFields["ORDER_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGR_EMPTY_ORDER_ID"), "NO_ORDER_ID");
			return false;
		}

		if (is_set($arFields, "USER_ID"))
		{
			$dbUser = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbUser->Fetch())
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["USER_ID"], GetMessage("SKGR_NO_USER")), "ERROR_NO_USER_ID");
				return false;
			}
		}

		if (is_set($arFields, "ORDER_ID"))
		{
			if (!($arOrder = CSaleOrder::GetByID($arFields["ORDER_ID"])))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["ORDER_ID"], GetMessage("SKGR_NO_ORDER")), "ERROR_NO_ORDER");
				return false;
			}
		}

		if (is_set($arFields, "PRICE") || $ACTION=="ADD")
		{
			$arFields["PRICE"] = str_replace(",", ".", $arFields["PRICE"]);
			$arFields["PRICE"] = DoubleVal($arFields["PRICE"]);
		}

		if ((is_set($arFields, "RECUR_SCHEME_TYPE") || $ACTION=="ADD") && !array_key_exists($arFields["RECUR_SCHEME_TYPE"], $GLOBALS["SALE_TIME_PERIOD_TYPES"]))
		{
			$arTypes = array_keys($GLOBALS["SALE_TIME_PERIOD_TYPES"]);
			$arFields["RECUR_SCHEME_TYPE"] = $arTypes[1];
		}

		if ((is_set($arFields, "WITHOUT_ORDER") || $ACTION=="ADD") && $arFields["WITHOUT_ORDER"] != "Y")
			$arFields["WITHOUT_ORDER"] = "N";
		if ((is_set($arFields, "CANCELED") || $ACTION=="ADD") && $arFields["CANCELED"] != "Y")
			$arFields["CANCELED"] = "N";

		$connection = Application::getConnection();
		$helper = $connection->getSqlHelper();
		unset($arFields['TIMESTAMP_X']);
		$arFields['~TIMESTAMP_X'] = $helper->getCurrentDateTimeFunction();

		return true;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return False;

		if (!CSaleRecurring::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_recurring", $arFields);
		$strSql = "UPDATE b_sale_recurring SET ".$strUpdate." WHERE ID = ".$ID." ";
		$DB->Query($strSql);

		unset($GLOBALS["SALE_RECURRING"]["SALE_RECURRING_CACHE_".$ID]);

		return $ID;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return False;

		unset($GLOBALS["SALE_RECURRING"]["SALE_RECURRING_CACHE_".$ID]);

		return $DB->Query("DELETE FROM b_sale_recurring WHERE ID = ".$ID." ", true);
	}

	public static function ExecuteCallbackFunction($callbackFunc = "", $module = "", $productID = 0)
	{
		$callbackFunc = trim($callbackFunc);
		$module = trim($module);
		$productID = intval($productID);

		$result = False;
		if ($callbackFunc <> '')
		{
			if ($module <> '' && $module != "main")
				CModule::IncludeModule($module);

			$arArgs = array($productID);
			$numArgs = func_num_args();
			if ($numArgs > 3)
				for ($i = 3; $i < $numArgs; $i++)
					$arArgs[] = func_get_arg($i);

			$result = call_user_func_array($callbackFunc, $arArgs);
		}

		return $result;
	}

	public static function CheckRecurring()
	{
		if (defined("SALE_PROC_REC_NUM"))
			$processRecords = SALE_PROC_REC_NUM;
		else
			$processRecords = 3;

		$dbRecurring = CSaleRecurring::GetList(
				array(),
				array(
						"<=NEXT_DATE" => Date($GLOBALS["DB"]->DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID))),
						"CANCELED" => "N",
						"!REMAINING_ATTEMPTS" => 0
					),
				false,
				array("nTopCount" => $processRecords),
				array("ID")
			);
		$cnt = 0;
		while ($arRecurring = $dbRecurring->Fetch())
		{
			CSaleRecurring::NextPayment($arRecurring["ID"]);
			$cnt++;
			if ($cnt >= $processRecords)
				break;
		}
	}

	public static function NextPayment($ID)
	{
		global $DB;
		global $USER;

		$ID = intval($ID);
		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGR_NO_RECID"), "NO_RECORD_ID");
			return False;
		}

		$arRecur = CSaleRecurring::GetByID($ID);
		if (!$arRecur)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SKGR_NO_RECID1")), "NO_RECORD");
			return False;
		}

		$arOrder = CSaleOrder::GetByID($arRecur["ORDER_ID"]);
		if (!$arOrder)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arRecur["ORDER_ID"], GetMessage("SKGR_NO_ORDER1")), "NO_ORDER");
			return False;
		}


		$bSuccess = True;
		$newOrderID = intval($arRecur["ORDER_ID"]);

		/** @var $productProvider IBXSaleProductProvider */
		if ($productProvider = CSaleBasket::GetProductProvider($arRecur))
		{
			$arProduct = $productProvider::RecurringOrderProduct(array(
				"PRODUCT_ID" => $arRecur["PRODUCT_ID"],
				"USER_ID"    => $arOrder["USER_ID"]
			));
		}
		else
		{
			$arProduct = CSaleRecurring::ExecuteCallbackFunction(
					$arRecur["CALLBACK_FUNC"],
					$arRecur["MODULE"],
					$arRecur["PRODUCT_ID"],
					$arOrder["USER_ID"]
				);
		}

		if (!$arProduct || !is_array($arProduct) || empty($arProduct))
		{
			CSaleRecurring::CancelRecurring($arRecur["ID"], "Y", "Product is not found");

			return true;
		}

		if ($arProduct["WITHOUT_ORDER"] == "Y" || $arRecur["SUCCESS_PAYMENT"] == "Y")
		{
			$baseSiteCurrency = CSaleLang::GetLangCurrency($arOrder["LID"]);
			$productPrice = \Bitrix\Sale\PriceMaths::roundPrecision(CCurrencyRates::ConvertCurrency($arProduct["PRICE"], $arProduct["CURRENCY"], $baseSiteCurrency));

			// Delivery
			$deliveryPrice = 0;
			$deliveryID = 0;
			$arOrder["DELIVERY_ID"] = intval($arOrder["DELIVERY_ID"]);
			if ($arOrder["DELIVERY_ID"] > 0)
			{
				$deliveryLocation = 0;
				$dbOrderPropValues = CSaleOrderPropsValue::GetList(
						array(),
						array(
								"ORDER_ID" => $arRecur["ORDER_ID"],
								"PROP_IS_LOCATION" => "Y"
							),
						false,
						false,
						array("VALUE")
					);
				if ($arOrderPropValues = $dbOrderPropValues->Fetch())
					$deliveryLocation = intval($arOrderPropValues["VALUE"]);

				$dbDelivery = CSaleDelivery::GetList(
						array("SORT" => "ASC", "NAME" => "ASC"),
						array(
								"LID" => $arOrder["LID"],
								"WEIGHT" => DoubleVal($arProduct["WEIGHT"]) * DoubleVal($arProduct["QUANTITY"]), // Changed by Sigurd, 2007-08-16
								"ORDER_PRICE" => $productPrice * DoubleVal($arProduct["QUANTITY"]),
								"ACTIVE" => "Y",
								"LOCATION" => $deliveryLocation
							)
					);
				while ($arDelivery = $dbDelivery->Fetch())
				{
					$deliveryPriceTmp = \Bitrix\Sale\PriceMaths::roundPrecision(CCurrencyRates::ConvertCurrency($arDelivery["PRICE"], $arDelivery["CURRENCY"], $baseSiteCurrency));
					if (intval($arDelivery["ID"]) == $arOrder["DELIVERY_ID"])
					{
						$deliveryID = intval($arDelivery["ID"]);
						$deliveryPrice = $deliveryPriceTmp;
						break;
					}
					if ($deliveryPriceTmp < $deliveryPrice || $deliveryID <= 0)
					{
						$deliveryID = intval($arDelivery["ID"]);
						$deliveryPrice = $deliveryPriceTmp;
					}
				}

				if ($deliveryID <= 0)
				{
					$deliveryID = \Bitrix\Sale\Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId();

					if ($deliveryID > 0)
					{
						$deliveryID = \CSaleDelivery::getCodeById($deliveryID);
					}
				}

				if ($deliveryID <= 0)
				{
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGR_NO_DELIVERY"), "NO_DELIVERY");
					return False;
				}
			}

			// Sale discounts
			$discount = 0;
			$discountPrice = $productPrice;
			$discountProduct = 0;
			$dbDiscount = CSaleDiscount::GetList(
					array("SORT" => "ASC"),
					array(
							"LID" => $arOrder["LID"],
							"ACTIVE" => "Y",
							"!>ACTIVE_FROM" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
							"!<ACTIVE_TO" => Date($DB->DateFormatToPHP(CSite::GetDateFormat("FULL"))),
							"<=PRICE_FROM" => $productPrice,
							">=PRICE_TO" => $productPrice,
							"USER_GROUPS" => $USER->GetUserGroup($arOrder['USER_ID']),
						)
				);
			if ($arDiscount = $dbDiscount->Fetch())
			{
				if ($arDiscount["DISCOUNT_TYPE"] == "P")
				{
					$discountProduct = \Bitrix\Sale\PriceMaths::roundPrecision($productPrice * $arDiscount["DISCOUNT_VALUE"] / 100);
					$discount = \Bitrix\Sale\PriceMaths::roundPrecision($discountProduct * DoubleVal($arProduct["QUANTITY"])); // Changed by Sigurd, 2007-08-16
					$discountPrice = $productPrice - $discountProduct;
				}
				else
				{
					$discountValue = CCurrencyRates::ConvertCurrency($arDiscount["DISCOUNT_VALUE"], $arDiscount["CURRENCY"], $baseSiteCurrency);
					$discountValue = \Bitrix\Sale\PriceMaths::roundPrecision($discountValue);

					$discountProduct = \Bitrix\Sale\PriceMaths::roundPrecision(1.0 * $discountValue / DoubleVal($arProduct["QUANTITY"]));// Changed by Sigurd, 2007-08-16
					$discount = \Bitrix\Sale\PriceMaths::roundPrecision($curDiscount * DoubleVal($arProduct["QUANTITY"]));
					$discountPrice = $productPrice - $discountProduct;
				}
			}

			$bUseVat = false;
			$vatRate = 0;
			if(DoubleVal($arProduct["VAT_RATE"]) > 0)
			{
				$bUseVat = true;
				$vatRate = $arProduct["VAT_RATE"];
			}

			// Tax
			$arTaxExempt = array();
			$dbUserGroups = CUser::GetUserGroupEx($arOrder["USER_ID"]);
			while ($arUserGroups = $dbUserGroups->Fetch())
			{
				$dbTaxExemptTmp = CSaleTax::GetExemptList(array("GROUP_ID" => $arUserGroups["GROUP_ID"]));
				while ($arTaxExemptTmp = $dbTaxExemptTmp->Fetch())
				{
					$arTaxExemptTmp["TAX_ID"] = intval($arTaxExemptTmp["TAX_ID"]);
					if (!in_array($arTaxExemptTmp["TAX_ID"], $arTaxExempt))
						$arTaxExempt[] = $arTaxExemptTmp["TAX_ID"];
				}
			}

			$taxPrice = 0;
			$taxVatPrice = 0;
			if(!$bUseVat)
			{
				$taxLocation = 0;
				$dbOrderPropValues = CSaleOrderPropsValue::GetList(
						array(),
						array(
								"ORDER_ID" => $arRecur["ORDER_ID"],
								"PROP_IS_LOCATION4TAX" => "Y"
							),
						false,
						false,
						array("VALUE")
					);
				if ($arOrderPropValues = $dbOrderPropValues->Fetch())
					$taxLocation = intval($arOrderPropValues["VALUE"]);

				$arTaxList = array();
				$dbTaxRateTmp = CSaleTaxRate::GetList(
						array("APPLY_ORDER" => "ASC"),
						array(
								"LID" => $arOrder["LID"],
								"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
								"ACTIVE" => "Y",
								"LOCATION" => $taxLocation
							)
					);
				while ($arTaxRateTmp = $dbTaxRateTmp->Fetch())
				{
					if (!in_array(intval($arTaxRateTmp["TAX_ID"]), $arTaxExempt))
					{
						$arTaxList[] = $arTaxRateTmp;
					}
				}
			}
			else
			{
				$arTaxList[] = Array(
							"ID" => 0,
							"TAX_NAME" => GetMessage("SKGR_VAT"),
							"IS_PERCENT" => "Y",
							"VALUE" => $vatRate*100,
							"VALUE_MONEY" => 0,
							"APPLY_ORDER" => 100,
							"IS_IN_PRICE" => "Y",
							"CODE" => "VAT"
				);
			}


			$arTaxSums = array();

			if (!empty($arTaxList))
			{
				if(!$bUseVat)
				{
					$taxPriceTmp = CSaleOrderTax::CountTaxes(
							$discountPrice * DoubleVal($arProduct["QUANTITY"]),
							$arTaxList,
							$baseSiteCurrency
						);

					for ($di = 0, $intCount = count($arTaxList); $di < $intCount; $di++)
					{
						$arTaxList[$di]["VALUE_MONEY"] += $arTaxList[$di]["TAX_VAL"];
					}

					for ($di = 0, $intCount = count($arTaxList); $di < $intCount; $di++)
					{
						$arTaxSums[$arTaxList[$di]["TAX_ID"]]["VALUE"] = $arTaxList[$di]["VALUE_MONEY"];
						$arTaxSums[$arTaxList[$di]["TAX_ID"]]["NAME"] = $arTaxList[$di]["NAME"];
						if ($arTaxList[$di]["IS_IN_PRICE"] != "Y")
						{
							$taxPrice += $arTaxList[$di]["VALUE_MONEY"];
						}
					}
				}
				else
				{
					$arTaxList[0]["VALUE_MONEY"] = \Bitrix\Sale\PriceMaths::roundPrecision((($discountPrice / ($vatRate +1)) * $vatRate) * DoubleVal($arProduct["QUANTITY"]));
					$taxVatPrice = $arTaxList[0]["VALUE_MONEY"];
				}
			}


			// Changed by Sigurd, 2007-08-16
			$totalOrderPrice = $discountPrice * DoubleVal($arProduct["QUANTITY"]) + $deliveryPrice + $taxPrice;

			$arProduct["WITHOUT_ORDER"] = (($arProduct["WITHOUT_ORDER"]=="Y") ? "Y" : "N");
			if ($arProduct["WITHOUT_ORDER"] == "N")
			{
				$DB->StartTransaction();

				// Saving
				$currentFUser = Sale\Fuser::getIdByUserId((int)$arOrder['USER_ID']);

				$arFields = array(
						"FUSER_ID" => $currentFUser,
						"PRODUCT_ID" => $arProduct["PRODUCT_ID"],
						"PRODUCT_NAME" => $arProduct["PRODUCT_NAME"],
						"PRODUCT_URL" => $arProduct["PRODUCT_URL"],
						"PRODUCT_PRICE_ID" => $arProduct["PRODUCT_PRICE_ID"],
						"PRICE" => $arProduct["PRICE"],
						"CURRENCY" => $arProduct["CURRENCY"],
						"WEIGHT" => $arProduct["WEIGHT"],
						"QUANTITY" => $arProduct["QUANTITY"],
						"LID" => $arOrder["LID"],
						"DELAY" => "N",
						"CAN_BUY" => "Y",
						"NAME" => $arProduct["NAME"],
						"CALLBACK_FUNC" => $arProduct["CALLBACK_FUNC"],
						"ORDER_CALLBACK_FUNC" => $arProduct["ORDER_CALLBACK_FUNC"],
						"CANCEL_CALLBACK_FUNC" => $arProduct["CANCEL_CALLBACK_FUNC"],
						"PAY_CALLBACK_FUNC" => $arProduct["PAY_CALLBACK_FUNC"],
						"PRODUCT_PROVIDER_CLASS" => $arProduct["PRODUCT_PROVIDER_CLASS"],
						"MODULE" => $arRecur["MODULE"],
						"NOTES" => $arProduct["CATALOG_GROUP_NAME"],
						"DETAIL_PAGE_URL" => $arProduct["DETAIL_PAGE_URL"],
						"VATE_RATE" => $arProduct["VATE_RATE"],
						"PRODUCT_XML_ID" => $arProduct["PRODUCT_XML_ID"],
						"TYPE" => $arProduct["TYPE"],
						"RENEWAL" => "Y"
					);

				$basketID = CSaleBasket::Add($arFields);

				$basketID = intval($basketID);
				if ($basketID <= 0)
					$bSuccess = False;

				if ($bSuccess)
				{
					if (CModule::IncludeModule("statistic"))
						CStatistic::Set_Event("eStore", "add2basket", $arFields["PRODUCT_ID"]);

					$arFields = array(
							"LID" => $arOrder["LID"],
							"PERSON_TYPE_ID" => $arOrder["PERSON_TYPE_ID"],
							"PAYED" => "N",
							"CANCELED" => "N",
							"STATUS_ID" => "N",
							"PRICE_DELIVERY" => $deliveryPrice,
							"ALLOW_DELIVERY" => "N",
							"PRICE" => $totalOrderPrice,
							"CURRENCY" => $baseSiteCurrency,
							"DISCOUNT_VALUE" => $discount,
							"USER_ID" => $arOrder["USER_ID"],
							"PAY_SYSTEM_ID" => $arOrder["PAY_SYSTEM_ID"],
							"DELIVERY_ID" => $deliveryID,
							"USER_DESCRIPTION" => $arOrder["USER_DESCRIPTION"],
							"TAX_VALUE" => (($bUseVat)? $taxVatPrice : $taxPrice),
							"STAT_GID" => $arOrder["STAT_GID"],
							"RECURRING_ID" => $arRecur["ID"]
						);

					$newOrderID = CSaleOrder::Add($arFields);

					$newOrderID = intval($newOrderID);
					if ($newOrderID <= 0)
						$bSuccess = False;
				}

				if ($bSuccess)
				{
					$arDiscounts = array();
					$arDiscounts[$basketID] = $discountProduct;
					CSaleBasket::OrderBasket($newOrderID, $currentFUser, $arOrder["LID"], $arDiscounts);
				}

				if ($bSuccess)
				{
					for ($it = 0, $intCount = count($arTaxList); $it < $intCount; $it++)
					{
						$arFields = array(
							"ORDER_ID" => $newOrderID,
							"TAX_NAME" => $arTaxList[$it]["TAX_NAME"],
							"IS_PERCENT" => $arTaxList[$it]["IS_PERCENT"],
							"VALUE" => ($arTaxList[$it]["IS_PERCENT"]=="Y") ? $arTaxList[$it]["VALUE"] : RoundEx(CCurrencyRates::ConvertCurrency($arTaxList[$it]["VALUE"], $arTaxList[$it]["CURRENCY"], $baseSiteCurrency), 2),
							"VALUE_MONEY" => $arTaxList[$it]["VALUE_MONEY"],
							"APPLY_ORDER" => $arTaxList[$it]["APPLY_ORDER"],
							"IS_IN_PRICE" => $arTaxList[$it]["IS_IN_PRICE"],
							"CODE" => $arTaxList[$it]["CODE"]
							);
						CSaleOrderTax::Add($arFields);
					}

					$dbOrderPropValues = CSaleOrderPropsValue::GetList(
							array(),
							array("ORDER_ID" => $arRecur["ORDER_ID"]),
							false,
							false,
							array("ORDER_PROPS_ID", "NAME", "CODE", "VALUE", "PROP_IS_PAYER", "PROP_IS_EMAIL")
						);
					while ($arOrderPropValues = $dbOrderPropValues->Fetch())
					{
						$arFields = array(
							"ORDER_ID" => $newOrderID,
							"ORDER_PROPS_ID" => $arOrderPropValues["ORDER_PROPS_ID"],
							"NAME" => $arOrderPropValues["NAME"],
							"CODE" => $arOrderPropValues["CODE"],
							"VALUE" => $arOrderPropValues["VALUE"]
							);
						CSaleOrderPropsValue::Add($arFields);

						if ($arOrderPropValues["PROP_IS_PAYER"] == "Y")
							$payerName = $arOrderPropValues["VALUE"];
						if ($arOrderPropValues["PROP_IS_EMAIL"] == "Y")
							$payerEMail = $arOrderPropValues["VALUE"];
					}
				}

				if ($bSuccess)
				{
					if (CModule::IncludeModule("statistic"))
						CStatistic::Set_Event("eStore", "order_create", $newOrderID);
				}

				if ($bSuccess)
				{
					$strOrderList = "";
					$dbBasketTmp = CSaleBasket::GetList(
						array("NAME" => "ASC"),
						array("ORDER_ID" => $newOrderID)
					);
					while ($arBasketTmp = $dbBasketTmp->Fetch())
					{
						$strOrderList .= $arBasketTmp["NAME"]." - ".$arBasketTmp["QUANTITY"]." ".GetMessage("SALE_QUANTITY_UNIT");
						$strOrderList .= "\n";
					}

					if ($payerName == '' || $payerEMail == '')
					{
						$dbUser = CUser::GetByID($arOrder["USER_ID"]);
						if ($arUser = $dbUser->Fetch())
						{
							if ($payerName == '')
								$payerName = $arUser["NAME"].(($arUser["NAME"] == '' || $arUser["LAST_NAME"] == '') ? "" : " ").$arUser["LAST_NAME"];
							if ($payerEMail == '')
								$payerEMail = $arUser["EMAIL"];
						}
					}

					$arFields = Array(
						"ORDER_ID" => $newOrderID,
						"ORDER_DATE" => Date($DB->DateFormatToPHP(CLang::GetDateFormat("SHORT", $arOrder["LID"]))),
						"ORDER_USER" => $payerName,
						"PRICE" => SaleFormatCurrency($totalOrderPrice, $baseSiteCurrency),
						"BCC" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME),
						"EMAIL" => $payerEMail,
						"ORDER_LIST" => $strOrderList,
						"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME)
					);
					$eventName = "SALE_NEW_ORDER_RECURRING";

					$bSend = true;
					foreach(GetModuleEvents("sale", "OnOrderRecurringSendEmail", true) as $arEvent)
					{
						if (ExecuteModuleEventEx($arEvent, Array($newOrderID, &$eventName, &$arFields))===false)
							$bSend = false;
					}

					if($bSend)
					{
						$event = new CEvent;
						$event->Send($eventName, $arOrder["LID"], $arFields, "N");
					}
				}

				if ($bSuccess)
					$DB->Commit();
				else
					$DB->Rollback();
			}
		}
		else
		{
			$totalOrderPrice = $arOrder["PRICE"];
			$baseSiteCurrency = $arOrder["CURRENCY"];
		}

		$res = False;
		if ($bSuccess)
		{
			$res = CSaleUserAccount::Pay($arOrder["USER_ID"], $totalOrderPrice, $baseSiteCurrency, $newOrderID, True);
			if ($res)
			{
				if ($arProduct["WITHOUT_ORDER"] == "N")
				{
					CSaleOrder::PayOrder($newOrderID, "Y", False, False, $arRecur["ID"]);
					CSaleOrder::DeliverOrder($newOrderID, "Y", $arRecur["ID"]);
					CSaleOrder::DeductOrder($newOrderID, "Y", "", true, array(), $arRecur["ID"]);
				}
				else
				{
					/** @var $productProvider IBXSaleProductProvider */
					if ($productProvider = CSaleBasket::GetProductProvider($arProduct))
					{
						$r = $productProvider::DeliverProduct(array(
							"PRODUCT_ID" => $arProduct["PRODUCT_ID"],
							"USER_ID"    => $arOrder["USER_ID"],
							"PAID"       => true,
							'BASKET_ID' => $basketID
						));
					}
					else
					{
						$r = CSaleBasket::ExecuteCallbackFunction(
							$arProduct["PAY_CALLBACK_FUNC"],
							$arRecur["MODULE"],
							$arProduct["PRODUCT_ID"],
							$arOrder["USER_ID"],
							true
						);
					}
				}

				$arFields = array(
					"ORDER_ID" => $newOrderID,
					"PRODUCT_NAME" => $arProduct["PRODUCT_NAME"],
					"PRODUCT_URL" => $arProduct["PRODUCT_URL"],
					"PRICE_TYPE" => $arProduct["PRICE_TYPE"],
					"RECUR_SCHEME_TYPE" => $arProduct["RECUR_SCHEME_TYPE"],
					"RECUR_SCHEME_LENGTH" => $arProduct["RECUR_SCHEME_LENGTH"],
					"WITHOUT_ORDER" => $arProduct["WITHOUT_ORDER"],
					"PRIOR_DATE" => Date($GLOBALS["DB"]->DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID))),
					"NEXT_DATE" => $arProduct["NEXT_DATE"],
					"REMAINING_ATTEMPTS" => (Defined("SALE_PROC_REC_ATTEMPTS") ? SALE_PROC_REC_ATTEMPTS : 3),
					"SUCCESS_PAYMENT" => "Y"
				);

				CSaleRecurring::Update($arRecur["ID"], $arFields);
			}
			else
			{
				$arFields = array(
					"ORDER_ID" => $newOrderID,
					"PRODUCT_NAME" => $arProduct["PRODUCT_NAME"],
					"PRODUCT_URL" => $arProduct["PRODUCT_URL"],
					"PRICE_TYPE" => $arProduct["PRICE_TYPE"],
					"RECUR_SCHEME_LENGTH" => $arProduct["RECUR_SCHEME_LENGTH"],
					"RECUR_SCHEME_TYPE" => $arProduct["RECUR_SCHEME_TYPE"],
					"WITHOUT_ORDER" => $arProduct["WITHOUT_ORDER"],
					"NEXT_DATE" => Date($GLOBALS["DB"]->DateFormatToPHP(CLang::GetDateFormat("FULL", SITE_ID)), time() + SALE_PROC_REC_TIME + CTimeZone::GetOffset()),
					"REMAINING_ATTEMPTS" => (intval($arRecur["REMAINING_ATTEMPTS"]) - 1),
					"SUCCESS_PAYMENT" => "N"
				);
				CSaleRecurring::Update($arRecur["ID"], $arFields);

				if ((intval($arRecur["REMAINING_ATTEMPTS"]) - 1) <= 0)
				{
					CSaleRecurring::CancelRecurring($arRecur["ID"], "Y", "Can't pay order");
					/*
					$arFields["CANCELED"] = "Y";
					$arFields["DATE_CANCELED"] = Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG)));
					$arFields["CANCELED_REASON"] = "Can't pay order";
					*/
				}
			}
		}

		return $res;
	}

	public static function CancelRecurring($ID, $val, $description = "")
	{
		global $DB, $USER;

		$ID = intval($ID);
		$val = (($val != "Y") ? "N" : "Y");
		$description = Trim($description);

		if ($ID <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGR_EMPTY_SUBSCR"), "NO_RECURRING_ID");
			return False;
		}

		$arRecurring = CSaleRecurring::GetByID($ID);
		if (!$arRecurring)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SKGR_NO_SUBSCR")), "NO_RECURRING");
			return False;
		}

		if ($arRecurring["CANCELED"] == $val)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SKGR_DUB_CANCEL")), "ALREADY_FLAG");
			return False;
		}

		$arFields = array(
				"CANCELED" => $val,
				"DATE_CANCELED" => (($val == "Y") ? Date(CDatabase::DateFormatToPHP(CLang::GetDateFormat("FULL", LANG))) : False),
				"CANCELED_REASON" => ( $description <> '' ? $description : false )
			);
		$res = CSaleRecurring::Update($ID, $arFields);

		unset($GLOBALS["SALE_RECURRING"]["SALE_RECURRING_CACHE_".$ID]);

		$arRecurring = CSaleRecurring::GetByID($ID);
		$dbUser = CUser::GetByID($arRecurring["USER_ID"]);
		$arUser = $dbUser->Fetch();

		$arOrder = CSaleOrder::GetByID($arRecurring["ORDER_ID"]);

		$arFields = array(
			"RECURRING_ID" => $ID,
			"PRODUCT_NAME" => $arRecurring["PRODUCT_NAME"],
			"EMAIL" => $arUser["EMAIL"],
			"CANCELED_REASON" => $description,
			"SALE_EMAIL" => COption::GetOptionString("sale", "order_email", "order@".$SERVER_NAME)
		);
		$eventName = "SALE_RECURRING_CANCEL";

		$bSend = true;
		foreach(GetModuleEvents("sale", "OnOrderRecurringCancelSendEmail", true) as $arEvent)
		{
			if (ExecuteModuleEventEx($arEvent, Array($ID, &$eventName, &$arFields))===false)
				$bSend = false;
		}

		if($bSend)
		{
			$event = new CEvent;
			$event->Send($eventName, $arOrder["LID"], $arFields, "N");
		}

		return $res;
	}

	public static function AgentCheckRecurring()
	{
		$bTmpUser = False;
		if (!isset($GLOBALS["USER"]) || !is_object($GLOBALS["USER"]))
		{
			$bTmpUser = True;
			$GLOBALS["USER"] = new CUser;
		}

		CSaleRecurring::CheckRecurring();

		global $pPERIOD;
		if (defined("SALE_PROC_REC_FREQUENCY") && intval(SALE_PROC_REC_FREQUENCY) > 0)
			$pPERIOD = intval(SALE_PROC_REC_FREQUENCY);
		else
			$pPERIOD = 7200;

		if ($bTmpUser)
		{
			unset($GLOBALS["USER"]);
		}

		return "CSaleRecurring::AgentCheckRecurring();";
	}

	public static function OnCurrencyDelete($Currency)
	{
		global $DB;
		if ($Currency == '') return false;

		return $DB->Query("DELETE FROM b_sale_recurring WHERE CURRENCY = '".$DB->ForSql($ID)."' ", true);
	}

	public static function OnUserDelete($UserID)
	{
		global $DB;
		$UserID = intval($UserID);

		return $DB->Query("DELETE FROM b_sale_recurring WHERE USER_ID = ".$UserID." ", true);
	}
}
