<?php

IncludeModuleLangFile(__FILE__);

class CAllSaleOrderPropsGroup
{
	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		$strSql =
			"SELECT * ".
			"FROM b_sale_order_props_group ".
			"WHERE ID = ".$ID."";
		$db_res = $DB->Query($strSql);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	public static function CheckFields($ACTION, &$arFields, $ID = 0)
	{
		global $DB, $USER;

		if (is_set($arFields, "PERSON_TYPE_ID") && $ACTION!="ADD")
			UnSet($arFields["PERSON_TYPE_ID"]);

		if ((is_set($arFields, "PERSON_TYPE_ID") || $ACTION=="ADD") && intval($arFields["PERSON_TYPE_ID"]) <= 0)
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOPG_EMPTY_PERS_TYPE"), "ERROR_NO_PERSON_TYPE");
			return false;
		}
		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && $arFields["NAME"] == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGOPG_EMPTY_GROUP"), "ERROR_NO_NAME");
			return false;
		}

		if (is_set($arFields, "PERSON_TYPE_ID"))
		{
			if (!($arPersonType = CSalePersonType::GetByID($arFields["PERSON_TYPE_ID"])))
			{
				$GLOBALS["APPLICATION"]->ThrowException(str_replace("#ID#", $arFields["PERSON_TYPE_ID"], GetMessage("SKGOPG_NO_PERS_TYPE")), "ERROR_NO_PERSON_TYPE");
				return false;
			}
		}

		return True;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);

		if (!CSaleOrderPropsGroup::CheckFields("UPDATE", $arFields, $ID)) return false;

		$strUpdate = $DB->PrepareUpdate("b_sale_order_props_group", $arFields);

		$strSql = "UPDATE b_sale_order_props_group SET ".$strUpdate." WHERE ID = ".$ID."";
		$DB->Query($strSql);

		return $ID;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);

		$db_orderProps = CSaleOrderProps::GetList(($by="PROPS_GROUP_ID"), ($order="ASC"), Array("PROPS_GROUP_ID"=>$ID));
		while ($arOrderProps = $db_orderProps->Fetch())
		{
			$DB->Query("DELETE FROM b_sale_order_props_variant WHERE ORDER_PROPS_ID = ".$arOrderProps["ID"]."", true);
			$DB->Query("UPDATE b_sale_order_props_value SET ORDER_PROPS_ID = NULL WHERE ORDER_PROPS_ID = ".$arOrderProps["ID"]."", true);
			$DB->Query("DELETE FROM b_sale_order_props_relation WHERE PROPERTY_ID = ".$arOrderProps["ID"]."", true);
		}
		$DB->Query("UPDATE b_sale_order_props SET PROPS_GROUP_ID = NULL WHERE PROPS_GROUP_ID = ".$ID."", true);
		CSaleOrderUserProps::ClearEmpty();

		return $DB->Query("DELETE FROM b_sale_order_props_group WHERE ID = ".$ID."", true);
	}
}
