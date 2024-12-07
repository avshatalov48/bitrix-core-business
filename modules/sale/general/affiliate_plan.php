<?php

IncludeModuleLangFile(__FILE__);

$GLOBALS["SALE_AFFILIATE_PLAN"] = Array();

class CAllSaleAffiliatePlan
{
	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		/** @global CDatabase $DB */
		global $DB;

		if ((is_set($arFields, "SITE_ID") || $ACTION=="ADD") && $arFields["SITE_ID"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SCGAP1_NO_SITE"), "EMPTY_SITE_ID");
			return false;
		}
		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && $arFields["NAME"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SCGAP1_NO_NAME"), "EMPTY_NAME");
			return false;
		}

		$ID = intval($ID);
		$arPlan = false;
		if ($ACTION != "ADD")
		{
			if ($ID <= 0)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SCGAP1_ERROR_FUNC"), "FUNCTION_ERROR");
				return false;
			}
			else
			{
				$arPlan = CSaleAffiliatePlan::GetByID($ID);
				if (!$arPlan)
				{
					$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SCGAP1_NO_PLAN")), "NO_PLAN");
					return false;
				}
			}
		}

		if (is_set($arFields, "DESCRIPTION") && $arFields["DESCRIPTION"] == '')
			$arFields["DESCRIPTION"] = false;

		if ((is_set($arFields, "ACTIVE") || $ACTION=="ADD") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";

		if (is_set($arFields, "BASE_RATE"))
		{
			$arFields["BASE_RATE"] = str_replace(",", ".", $arFields["BASE_RATE"]);
			$arFields["BASE_RATE"] = DoubleVal($arFields["BASE_RATE"]);
		}

		if (is_set($arFields, "MIN_PLAN_VALUE"))
		{
			$arFields["MIN_PLAN_VALUE"] = str_replace(",", ".", $arFields["MIN_PLAN_VALUE"]);
			$arFields["MIN_PLAN_VALUE"] = DoubleVal($arFields["MIN_PLAN_VALUE"]);
			if ($arFields["MIN_PLAN_VALUE"] <= 0)
				$arFields["MIN_PLAN_VALUE"] = false;
		}

		if ((is_set($arFields, "BASE_RATE_TYPE") || $ACTION=="ADD") && $arFields["BASE_RATE_TYPE"] != "F")
			$arFields["BASE_RATE_TYPE"] = "P";

		$affiliatePlanType = COption::GetOptionString("sale", "affiliate_plan_type", "N");

		if ($ACTION == "ADD")
		{
			if ($arFields["BASE_RATE_TYPE"] == "P")
				$arFields["BASE_RATE_CURRENCY"] = false;

			if ($arFields["BASE_RATE_TYPE"] == "F" && (!is_set($arFields, "BASE_RATE_CURRENCY") || $arFields["BASE_RATE_CURRENCY"] == ''))
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SCGAP1_NO_CURRENCY"), "EMPTY_BASE_RATE_CURRENCY");
				return false;
			}
		}
		else
		{
			if (!is_set($arFields, "BASE_RATE_TYPE"))
				$arFields["BASE_RATE_TYPE"] = $arPlan["BASE_RATE_TYPE"];

			if ($arFields["BASE_RATE_TYPE"] == "P")
			{
				$arFields["BASE_RATE_CURRENCY"] = false;
			}
			elseif ($arFields["BASE_RATE_TYPE"] == "F")
			{
				if (!is_set($arFields, "BASE_RATE_CURRENCY"))
					$arFields["BASE_RATE_CURRENCY"] = $arPlan["BASE_RATE_CURRENCY"];

				if (!is_set($arFields, "BASE_RATE_CURRENCY") || $arFields["BASE_RATE_CURRENCY"] == '')
				{
					$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SCGAP1_NO_CURRENCY"), "EMPTY_BASE_RATE_CURRENCY");
					return false;
				}
			}
		}

		unset($arFields['TIMESTAMP_X']);
		$arFields['~TIMESTAMP_X'] = $DB->GetNowFunction();

		return True;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return False;

		$db_events = GetModuleEvents("sale", "OnBeforeAffiliatePlanDelete");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID))===false)

		$cnt = CSaleAffiliate::GetList(array(), array("PLAN_ID" => $ID), array());
		if (intval($cnt) > 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $ID, GetMessage("SCGAP1_AFF_EXISTS")), "NOT_EMPTY_PLAN");
			return false;
		}

		unset($GLOBALS["SALE_AFFILIATE_PLAN"]["SALE_AFFILIATE_PLAN_CACHE_".$ID]);

		$DB->Query("DELETE FROM b_sale_affiliate_plan_section WHERE PLAN_ID = ".$ID." ", true);
		$bResult = $DB->Query("DELETE FROM b_sale_affiliate_plan WHERE ID = ".$ID." ", true);

		$events = GetModuleEvents("sale", "OnAfterAffiliatePlanDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID, $bResult));

		return $bResult;
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID <= 0)
			return false;

		if (isset($GLOBALS["SALE_AFFILIATE_PLAN"]["SALE_AFFILIATE_PLAN_CACHE_".$ID]) && is_array($GLOBALS["SALE_AFFILIATE_PLAN"]["SALE_AFFILIATE_PLAN_CACHE_".$ID]))
		{
			return $GLOBALS["SALE_AFFILIATE_PLAN"]["SALE_AFFILIATE_PLAN_CACHE_".$ID];
		}
		else
		{
			$strSql =
				"SELECT AP.ID, AP.SITE_ID, AP.NAME, AP.DESCRIPTION, AP.ACTIVE, AP.BASE_RATE, ".
				"	AP.BASE_RATE_TYPE, AP.BASE_RATE_CURRENCY, AP.MIN_PAY, AP.MIN_PLAN_VALUE, ".
				"	".$DB->DateToCharFunction("AP.TIMESTAMP_X", "FULL")." as TIMESTAMP_X ".
				"FROM b_sale_affiliate_plan AP ".
				"WHERE AP.ID = ".$ID." ";

			$db_res = $DB->Query($strSql);
			if ($res = $db_res->Fetch())
			{
				$GLOBALS["SALE_AFFILIATE_PLAN"]["SALE_AFFILIATE_PLAN_CACHE_".$ID] = $res;
				return $GLOBALS["SALE_AFFILIATE_PLAN"]["SALE_AFFILIATE_PLAN_CACHE_".$ID];
			}
		}

		return false;
	}

	public static function CheckAffiliatePlanFunc($affiliatePlan)
	{
		if (is_array($affiliatePlan))
		{
			$arAffiliatePlan = $affiliatePlan;
			$affiliatePlanID = intval($arAffiliatePlan["ID"]);

			if ($affiliatePlanID <= 0)
			{
				$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SCGAP1_ERROR_FUNC"), "FUNCTION_ERROR");
				return false;
			}
		}
		else
		{
			$affiliatePlanID = intval($affiliatePlan);
			if ($affiliatePlanID <= 0)
				return False;

			$dbAffiliatePlan = CSaleAffiliatePlan::GetList(
				array(),
				array("ID" => $affiliatePlanID, "ACTIVE" => "Y"),
				false,
				false,
				array("ID", "SITE_ID", "NAME", "DESCRIPTION", "TIMESTAMP_X", "ACTIVE", "BASE_RATE", "BASE_RATE_TYPE", "BASE_RATE_CURRENCY", "MIN_PAY", "MIN_PLAN_VALUE")
			);
			$arAffiliatePlan = $dbAffiliatePlan->Fetch();
			if (!$arAffiliatePlan)
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $affiliatePlanID, GetMessage("SCGAP1_NO_PLAN")), "NO_AFFILIATE_PLAN");
				return false;
			}
		}

		return $arAffiliatePlan;
	}
}
