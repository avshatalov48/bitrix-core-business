<?php

/**
 * The content of this file was marked as deprecated.
 * It will be removed from future releases. Do not rely on this code.
 *
 * @access private
 */

use Bitrix\Main\DB;
use Bitrix\Main\Entity;
use Bitrix\Sale\Location;

class CAllSaleLocationGroup
{
	const SELF_ENTITY_NAME = 		'Bitrix\Sale\Location\Group';
	const CONN_ENTITY_NAME = 		'Bitrix\Sale\Location\GroupLocation';
	const LOCATION_ENTITY_NAME = 	'Bitrix\Sale\Location\Location';
	const NAME_ENTITY_NAME = 		'Bitrix\Sale\Location\Name\Group';

	public static function GetLocationList($arFilter=Array())
	{
		if(CSaleLocation::isLocationProMigrated())
		{
			try
			{
				$query = new Entity\Query(self::CONN_ENTITY_NAME);

				$fieldMap = array(
					'D_SPIKE' => 'D_SPIKE',
					'LLOCATION_ID' => 'C.ID',
					'LOCATION_CODE' => 'C.CODE',
					'LOCATION_GROUP_ID' => 'LOCATION_GROUP_ID'
				);
				$fieldProxy = array(
					'LLOCATION_ID' => 'LOCATION_ID',
				);
				
				$query->registerRuntimeField(
					'D_SPIKE',
					array(
						'data_type' => 'integer',
						'expression' => array(
							'distinct %s',
							'LOCATION_GROUP_ID'
						)
					)
				);

				$query->registerRuntimeField(
					'L',
					array(
						'data_type' => self::LOCATION_ENTITY_NAME,
						'reference' => array(
							'=this.LOCATION_ID' => 'ref.ID',
						),
						'join_type' => 'inner'
					)
				);

				$query->registerRuntimeField(
					'C',
					array(
						'data_type' => self::LOCATION_ENTITY_NAME,
						'reference' => array(
							'LOGIC' => 'OR',
							array(
								'>=ref.LEFT_MARGIN' => 'this.L.LEFT_MARGIN',
								'<=ref.RIGHT_MARGIN' => 'this.L.RIGHT_MARGIN'
							),
							array(
								'=ref.ID' => 'this.L.ID'
							)
						),
						'join_type' => 'inner'
					)
				);

				// select
				$selectFields = CSaleLocation::processSelectForGetList(array('*'), $fieldMap);

				// filter
				list($filterFields, $filterClean) = CSaleLocation::processFilterForGetList($arFilter, $fieldMap, $fieldProxy);

				$query->setSelect($selectFields);
				$query->setFilter($filterFields);

				$res = $query->exec();
				$res->addReplacedAliases($fieldProxy);

				return $res;
			}
			catch(Exception $e)
			{
				return new DB\ArrayResult(array());
			}
		}
		else
		{

			global $DB;
			$arSqlSearch = Array();

			if(!is_array($arFilter))
				$filter_keys = Array();
			else
				$filter_keys = array_keys($arFilter);

			$countFieldKey = count($filter_keys);
			for($i=0; $i < $countFieldKey; $i++)
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

				switch(mb_strtoupper($key))
				{
				case "LOCATION_ID":
					$arSqlSearch[] = "LOCATION_ID ".($bInvert?"<>":"=")." ".intval($val)." ";
					break;
				case "LOCATION_GROUP_ID":
					$arSqlSearch[] = "LOCATION_GROUP_ID ".($bInvert?"<>":"=")." ".intval($val)." ";
					break;
				}
			}

			$strSqlSearch = "";
			$countSqlSearch = count($arSqlSearch);
			for($i=0; $i < $countSqlSearch; $i++)
			{
				$strSqlSearch .= " AND ";
				$strSqlSearch .= " (".$arSqlSearch[$i].") ";
			}

			$strSql =
				"SELECT LOCATION_ID, LOCATION_GROUP_ID ".
				"FROM b_sale_location2location_group ".
				"WHERE 1 = 1 ".
				"	".$strSqlSearch." ";

			$res = $DB->Query($strSql);
			return $res;

		}
	}

	public static function GetGroupLangByID($ID, $strLang = LANGUAGE_ID)
	{
		global $DB;

		$ID = intval($ID);
		$strSql =
			"SELECT ID, LOCATION_GROUP_ID, LID, NAME ".
			"FROM b_sale_location_group_lang ".
			"WHERE LOCATION_GROUP_ID = ".$ID." ".
			"	AND LID = '".$DB->ForSql($strLang, 2)."'";
		$db_res = $DB->Query($strSql);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	public static function CheckFields($ACTION, &$arFields)
	{
		global $DB;

		if (is_set($arFields, "SORT") && intval($arFields["SORT"])<=0)
			$arFields["SORT"] = 100;

		if (is_set($arFields, "LOCATION_ID") && (!is_array($arFields["LOCATION_ID"]) || count($arFields["LOCATION_ID"])<=0))
			return false;

		if (is_set($arFields, "LANG"))
		{
			$db_lang = CLangAdmin::GetList("sort", "asc", array("ACTIVE" => "Y"));
			while ($arLang = $db_lang->Fetch())
			{
				$bFound = False;
				$coountarFieldLang = count($arFields["LANG"]);
				for ($i = 0; $i < $coountarFieldLang; $i++)
				{
					if ($arFields["LANG"][$i]["LID"]==$arLang["LID"] && $arFields["LANG"][$i]["NAME"] <> '')
					{
						$bFound = True;
					}
				}
				if (!$bFound)
					return false;
			}
		}

		return True;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if (!CSaleLocationGroup::CheckFields("UPDATE", $arFields))
			return false;

		$db_events = GetModuleEvents("sale", "OnBeforeLocationGroupUpdate");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID, &$arFields))===false)
				return false;

		$events = GetModuleEvents("sale", "OnLocationGroupUpdate");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		$strUpdate = $DB->PrepareUpdate("b_sale_location_group", $arFields);
		$strSql = "UPDATE b_sale_location_group SET ".$strUpdate." WHERE ID = ".$ID."";
		$DB->Query($strSql);

		if (is_set($arFields, "LANG"))
		{
			$DB->Query("DELETE FROM b_sale_location_group_lang WHERE LOCATION_GROUP_ID = ".$ID."");

			$countFieldLang = count($arFields["LANG"]);
			for ($i = 0; $i < $countFieldLang; $i++)
			{
				$arInsert = $DB->PrepareInsert("b_sale_location_group_lang", $arFields["LANG"][$i]);
				$strSql =
					"INSERT INTO b_sale_location_group_lang(LOCATION_GROUP_ID, ".$arInsert[0].") ".
					"VALUES(".$ID.", ".$arInsert[1].")";
				$DB->Query($strSql);
			}
		}

		if(is_set($arFields, "LOCATION_ID"))
		{
			if(CSaleLocation::isLocationProMigrated())
			{
				try
				{
					$entityClass = self::CONN_ENTITY_NAME.'Table';
					$entityClass::resetMultipleForOwner($ID, array(
						Location\Connector::DB_LOCATION_FLAG => $entityClass::normalizeLocationList($arFields["LOCATION_ID"])
					));
				}
				catch(Exception $e)
				{
				}
			}
			else
			{
				$DB->Query("DELETE FROM b_sale_location2location_group WHERE LOCATION_GROUP_ID = ".$ID."");

				$countArFieldLoc = count($arFields["LOCATION_ID"]);
				for ($i = 0; $i < $countArFieldLoc; $i++)
				{
					$strSql =
						"INSERT INTO b_sale_location2location_group(LOCATION_ID, LOCATION_GROUP_ID) ".
						"VALUES(".$arFields["LOCATION_ID"][$i].", ".$ID.")";
					$DB->Query($strSql);
				}
			}
		}

		return $ID;
	}

	public static function Delete($ID)
	{
		global $DB;
		$ID = intval($ID);

		$db_events = GetModuleEvents("sale", "OnBeforeLocationGroupDelete");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($ID))===false)
				return false;

		$events = GetModuleEvents("sale", "OnLocationGroupDelete");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID));

		$DB->Query("DELETE FROM b_sale_delivery2location WHERE LOCATION_ID = ".$ID." AND LOCATION_TYPE = 'G'", true);
		// tax rates drop ?
		$DB->Query("DELETE FROM b_sale_location2location_group WHERE LOCATION_GROUP_ID = ".$ID."", true);
		$DB->Query("DELETE FROM b_sale_location_group_lang WHERE LOCATION_GROUP_ID = ".$ID."", true);

		return $DB->Query("DELETE FROM b_sale_location_group WHERE ID = ".$ID."", true);
	}

	public static function OnLangDelete($strLang)
	{
		global $DB;
		$DB->Query("DELETE FROM b_sale_location_group_lang WHERE LID = '".$DB->ForSql($strLang)."'", true);
		return True;
	}
}
