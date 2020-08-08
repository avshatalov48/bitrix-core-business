<?
use	Bitrix\Sale\Internals\OrderTable,
	Bitrix\Sale\Internals\OrderArchiveTable,
	Bitrix\Sale\Compatible,
	Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"] = Array();

class CAllSalePersonType
{
	static function DoProcessOrder(&$arOrder, $personTypeId, &$arErrors, $arOptions)
	{
		$personTypeId = intval($personTypeId);

		if (isset($arOptions['ORDER'])
			&& $arOptions['ORDER'] instanceof \Bitrix\Sale\Order
		)
		{
			$registry = \Bitrix\Sale\Registry::getInstance($arOptions['ORDER']::getRegistryType());
			$personType = $registry->getPersonTypeClassName();
		}
		else
		{
			$personType = \Bitrix\Sale\PersonType::class;
		}

		if ($personTypeId > 0)
		{
			/** @var Bitrix\Main\DB\Result $dbPersonType */
			$dbPersonType = $personType::getList([
				'filter' => ['=ID' => $personTypeId]
			]);
			if ($arPersonType = $dbPersonType->fetch())
			{
				$arOrder["PERSON_TYPE_ID"] = $arPersonType["ID"];
			}
			else
			{
				$arErrors[] = array("CODE" => "PERSON_TYPE_ID", "TEXT" => GetMessage('SKGP_PERSON_TYPE_NOT_FOUND'));
			}

			return;
		}

		/** @var Bitrix\Main\DB\Result $dbPersonType */
		$dbPersonType = $personType::getList([
			'filter' => [
				"=PERSON_TYPE_SITE.SITE_ID" => $arOrder["SITE_ID"],
				"=ACTIVE" => "Y"
			],
			'order' => [
				'SORT' => 'ASC',
				'NAME' => 'ASC'
			] 
		]);

		if ($arPersonType = $dbPersonType->Fetch())
		{
			$arOrder["PERSON_TYPE_ID"] = $arPersonType["ID"];
		}
		else
		{
			$arErrors[] = array("CODE" => "PERSON_TYPE_ID", "TEXT" => GetMessage('SKGP_PERSON_TYPE_EMPTY'));
		}
	}

	function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		$dbPerson = CSalePersonType::GetList(Array(), Array("ID" => $ID));
		if ($res = $dbPerson->Fetch())
		{
			return $res;
		}
		return False;
	}

	function CheckFields($ACTION, &$arFields, $ID=false)
	{
		global $DB, $USER;

		if ((is_set($arFields, "NAME") || $ACTION=="ADD") && trim($arFields["NAME"]) == '')
		{
			$GLOBALS["APPLICATION"]->ThrowException(GetMessage("SKGP_NO_NAME_TP"), "ERROR_NO_NAME");
			return false;
		}

		$arMsg = Array();
		if(
			($ID===false && !is_set($arFields, "LID")) ||
			(is_set($arFields, "LID")
			&& (
				(is_array($arFields["LID"]) && count($arFields["LID"])<=0)
				||
				(!is_array($arFields["LID"]) && $arFields["LID"] == '')
				)
			)
		)
		{
			//$this->LAST_ERROR .= GetMessage("SKGP_BAD_SITE_NA")."<br>";
			$arMsg[] = array("id"=>"LID", "text"=> GetMessage("SKGP_BAD_SITE_NA"));
		}
		elseif(is_set($arFields, "LID"))
		{
			if(!is_array($arFields["LID"]))
				$arFields["LID"] = Array($arFields["LID"]);

			foreach($arFields["LID"] as $v)
			{
				$r = CSite::GetByID($v);
				if(!$r->Fetch())
				{
					//$this->LAST_ERROR .= str_replace("#ID#", $arFields["LID"], GetMessage("SKGP_NO_SITE"));
					$arMsg[] = array("id"=>"LID", "text"=> GetMessage("MAIN_EVENT_BAD_SITE"));
				}
			}
		}

		if(!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		return True;
	}

	function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if (!CSalePersonType::CheckFields("UPDATE", $arFields, $ID))
			return false;

		$db_events = GetModuleEvents("sale", "OnBeforePersonTypeUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID, &$arFields))===false)
				return false;

		$arLID = Array();
		if(is_set($arFields, "LID"))
		{
			if(is_array($arFields["LID"]))
				$arLID = $arFields["LID"];
			else
				$arLID[] = $arFields["LID"];

			$str_LID = "''";
			$arFields["LID"] = false;
			foreach($arLID as $k => $v)
			{
				if($v <> '')
				{
					$str_LID .= ", '".$DB->ForSql($v)."'";
					if(empty($arFields["LID"]))
						$arFields["LID"] = $v;
				}
				else
					unset($arLID[$k]);
			}
		}

		$strUpdate = $DB->PrepareUpdate("b_sale_person_type", $arFields);
		$strSql = "UPDATE b_sale_person_type SET ".$strUpdate." WHERE ID = ".$ID."";
		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if(count($arLID)>0)
		{
			$strSql = "DELETE FROM b_sale_person_type_site WHERE PERSON_TYPE_ID=".$ID;
			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);

			$strSql =
				"INSERT INTO b_sale_person_type_site(PERSON_TYPE_ID, SITE_ID) ".
				"SELECT ".$ID.", LID ".
				"FROM b_lang ".
				"WHERE LID IN (".$str_LID.") ";

			$DB->Query($strSql, false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
		}


		unset($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]);

		$events = GetModuleEvents("sale", "OnPersonTypeUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID, $arFields));

		return $ID;
	}

	function Delete($ID)
	{
		global $DB, $APPLICATION;

		$ID = (int)($ID);

		if (OrderTable::getList(array(
			'filter' => array('=PERSON_TYPE_ID' => $ID),
			'limit' => 1
		))->fetch())
		{
			$APPLICATION->ThrowException(Loc::getMessage("SKGP_ERROR_PERSON_HAS_ORDER").$ID, "ERROR_PERSON_HAS_ORDER");
			return false;
		}

		if (OrderArchiveTable::getList(array(
			'filter' => array('=PERSON_TYPE_ID' => $ID),
			'limit' => 1
		))->fetch())
		{
			$APPLICATION->ThrowException(Loc::getMessage("SKGP_ERROR_PERSON_HAS_ARCHIVE", array("#ID#" => $ID)), "ERROR_PERSON_HAS_ARCHIVED_ORDER");
			return false;
		}

		$db_events = GetModuleEvents("sale", "OnBeforePersonTypeDelete");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, Array($ID))===false)
				return false;

		$events = GetModuleEvents("sale", "OnPersonTypeDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, Array($ID));

		$DB->Query("DELETE FROM b_sale_pay_system_action WHERE PERSON_TYPE_ID = ".$ID."", true);

		$db_orderProps = CSaleOrderProps::GetList(
				array("PROPS_GROUP_ID" => "ASC"),
				array("PERSON_TYPE_ID" => $ID)
			);
		while ($arOrderProps = $db_orderProps->Fetch())
		{
			$DB->Query("DELETE FROM b_sale_order_props_variant WHERE ORDER_PROPS_ID = ".$arOrderProps["ID"]."", true);
			$DB->Query("DELETE FROM b_sale_order_props_value WHERE ORDER_PROPS_ID = ".$arOrderProps["ID"]."", true);
			$DB->Query("DELETE FROM b_sale_order_props_relation WHERE PROPERTY_ID = ".$arOrderProps["ID"]."", true);
			$DB->Query("DELETE FROM b_sale_user_props_value WHERE ORDER_PROPS_ID = ".$arOrderProps["ID"]."", true);
		}
		$DB->Query("DELETE FROM b_sale_order_props WHERE PERSON_TYPE_ID = ".$ID."", true);

		$db_orderUserProps = CSaleOrderUserProps::GetList(
				array("NAME" => "ASC"),
				array("PERSON_TYPE_ID" => $ID)
			);
		while ($arOrderUserProps = $db_orderUserProps->Fetch())
		{
			$DB->Query("DELETE FROM b_sale_user_props_value WHERE USER_PROPS_ID = ".$arOrderUserProps["ID"]."", true);
		}
		$DB->Query("DELETE FROM b_sale_user_props WHERE PERSON_TYPE_ID = ".$ID."", true);
		$DB->Query("DELETE FROM b_sale_order_props_group WHERE PERSON_TYPE_ID = ".$ID."", true);
		$DB->Query("DELETE FROM b_sale_person_type_site WHERE PERSON_TYPE_ID=".$ID, true);

		unset($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]);
		return $DB->Query("DELETE FROM b_sale_person_type WHERE ID = ".$ID."", true);
	}

	function OnBeforeLangDelete($lang)
	{
		global $DB;
		$r = $DB->Query("SELECT 'x' FROM b_sale_person_type WHERE LID = '".$DB->ForSQL($lang, 2)."' AND ENTITY_REGISTRY_TYPE='".\Bitrix\Sale\Registry::REGISTRY_TYPE_ORDER."'");
		return ($r->Fetch() ? false : true);
	}

	function SelectBox($sFieldName, $sValue, $sDefaultValue = "", $bFullName = True, $JavaFunc = "", $sAddParams = "")
	{
		if (!isset($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]) || !is_array($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]) || count($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"])<1)
		{
			unset($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]);
			$l = CSalePersonType::GetList(array("SORT" => "ASC", "NAME" => "ASC"));
			while ($arPersonType = $l->Fetch())
			{
				$GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"][$arPersonType["ID"]] = Array("ID" => $arPersonType["ID"], "NAME" => $arPersonType["NAME"], "LID" => implode(", ", $arPersonType["LIDS"]));
			}
		}
		$s1 = '';
		$s = '<select name="'.$sFieldName.'"';
		if ($sAddParams <> '') $s .= ' '.$sAddParams.'';
		if ($JavaFunc <> '') $s .= ' OnChange="'.$JavaFunc.'"';
		$s .= '>'."\n";
		$found = false;

		if (is_array($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"]))
		{
			foreach ($GLOBALS["SALE_PERSON_TYPE_LIST_CACHE"] as $res)
			{
				$found = (intval($res["ID"]) == intval($sValue));
				$s1 .= '<option value="'.$res["ID"].'"'.($found ? ' selected' : '').'>'.(($bFullName) ? ("[".$res["ID"]."] ".htmlspecialcharsbx($res["NAME"])." (".htmlspecialcharsbx($res["LID"]).")") : (htmlspecialcharsbx($res["NAME"]))).'</option>'."\n";
			}
		}
		if ($sDefaultValue <> '')
			$s .= "<option value='' ".($found ? "" : "selected").">".htmlspecialcharsbx($sDefaultValue)."</option>";
		return $s.$s1.'</select>';
	}
}
?>