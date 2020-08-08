<?
IncludeModuleLangFile(__FILE__);

class CAllSaleOrderPropsVariant
{
	function GetByValue($PropID, $Value)
	{
		$PropID = intval($PropID);
		$db_res = CSaleOrderPropsVariant::GetList(($by="SORT"), ($order="ASC"), Array("ORDER_PROPS_ID"=>$PropID, "VALUE"=>$Value));
		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		$strSql =
			"SELECT * ".
			"FROM b_sale_order_props_variant ".
			"WHERE ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB, $USER;

		if ((is_set($arFields, "VALUE") || $ACTION=="ADD") && $arFields["VALUE"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOPV_EMPTY_VAR"), "ERROR_NO_VALUE");
			return false;
		}
		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && $arFields["NAME"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOPV_EMPTY_NAME"), "ERROR_NO_NAME");
			return false;
		}
		if ((is_set($arFields, "ORDER_PROPS_ID") || $ACTION=="ADD") && intval($arFields["ORDER_PROPS_ID"])<=0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOPV_EMPTY_CODE"), "ERROR_NO_ORDER_PROPS_ID");
			return false;
		}

		if (is_set($arFields, "ORDER_PROPS_ID"))
		{
			if (!($arOrder = CSaleOrderProps::GetByID($arFields["ORDER_PROPS_ID"])))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["ORDER_PROPS_ID"], GetMessage("SKGOPV_NO_PROP")), "ERROR_NO_PROPERY");
				return false;
			}
		}

		return True;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		
		if (!CSaleOrderPropsVariant::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_order_props_variant", $arFields);

		$strSql = "UPDATE b_sale_order_props_variant SET ".$strUpdate." WHERE ID = ".$ID."";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return $ID;
	}

	function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);
		return $DB->Query("DELETE FROM b_sale_order_props_variant WHERE ID = ".$ID."", true);
	}

	function DeleteAll($ID)
	{
		global $DB;
		$ID = intval($ID);
		return $DB->Query("DELETE FROM b_sale_order_props_variant WHERE ORDER_PROPS_ID = ".$ID."", true);
	}
}
?>