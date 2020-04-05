<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/general/location_group.php");

use Bitrix\Sale\Location;

class CSaleLocationGroup extends CAllSaleLocationGroup
{
	function GetList($arOrder = Array("NAME"=>"ASC"), $arFilter=Array(), $strLang = LANGUAGE_ID)
	{
		global $DB;
		$arSqlSearch = Array();
		$arSqlSearchFrom = array();

		if(!is_array($arFilter))
			$filter_keys = Array();
		else
			$filter_keys = array_keys($arFilter);

		$countFilterKey = count($filter_keys);
		for($i=0; $i < $countFilterKey; $i++)
		{
			$val = $DB->ForSql($arFilter[$filter_keys[$i]]);
			if (strlen($val)<=0) continue;

			$key = $filter_keys[$i];
			if ($key[0]=="!")
			{
				$key = substr($key, 1);
				$bInvert = true;
			}
			else
				$bInvert = false;

			switch(ToUpper($key))
			{
			case "ID":
				$arSqlSearch[] = "LG.ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
				break;
			case "LOCATION":

				if(CSaleLocation::isLocationProMigrated())
				{
					try
					{
						$class = self::CONN_ENTITY_NAME.'Table';
						$arSqlSearch[] = "	LG.ID ".($bInvert ? 'not' : '')." in (".$class::getConnectedEntitiesQuery(IntVal($val), 'id', array('select' => array('ID'))).") ";
					}
					catch(Exception $e)
					{
					}
				}
				else
				{
					$arSqlSearch[] = "LG.ID = L2LG.LOCATION_GROUP_ID AND L2LG.LOCATION_GROUP_ID ".($bInvert?"<>":"=")." ".IntVal($val)." ";
					$arSqlSearchFrom[] = ", b_sale_location2location_group L2LG ";
				}

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

		$strSqlSearchFrom = "";
		$countSqlSearchForm = count($arSqlSearchFrom);
		for($i=0; $i < $countSqlSearchForm; $i++)
		{
			$strSqlSearchFrom .= " ".$arSqlSearchFrom[$i]." ";
		}

		$strSql =
			"SELECT DISTINCT LG.ID, LG.SORT, LGL.NAME, LGL.LID ".
			"FROM (b_sale_location_group LG ".
			"	".$strSqlSearchFrom.") ".
			"	LEFT JOIN b_sale_location_group_lang LGL ON (LG.ID = LGL.LOCATION_GROUP_ID AND LGL.LID = '".$DB->ForSql($strLang, 2)."') ".
			"WHERE 1 = 1 ".
			"	".$strSqlSearch." ";

		$arSqlOrder = Array();
		foreach ($arOrder as $by=>$order)
		{
			$by = ToUpper($by);
			$order = ToUpper($order);
			if ($order!="ASC") $order = "DESC";

			if ($by == "ID") $arSqlOrder[] = " LG.ID ".$order." ";
			elseif ($by == "NAME") $arSqlOrder[] = " LGL.NAME ".$order." ";
			else
			{
				$arSqlOrder[] = " LG.SORT ".$order." ";
				$by = "SORT";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$countSqlOrder = count($arSqlOrder);
		for ($i=0; $i < $countSqlOrder; $i++)
		{
			if ($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ", ";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		return $db_res;
	}

	function GetByID($ID, $strLang = LANGUAGE_ID)
	{
		global $DB;

		$ID = IntVal($ID);
		$strSql =
			"SELECT LG.ID, LG.SORT, LGL.NAME, LGL.LID ".
			"FROM b_sale_location_group LG ".
			"	LEFT JOIN b_sale_location_group_lang LGL ON (LG.ID = LGL.LOCATION_GROUP_ID AND LGL.LID = '".$DB->ForSql($strLang, 2)."') ".
			"WHERE LG.ID = ".$ID." ";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	function Add($arFields)
	{
		global $DB;

		if (!CSaleLocationGroup::CheckFields("ADD", $arFields))
			return false;

		// make IX_B_SALE_LOC_GROUP_CODE feel happy
		$arFields['CODE'] = 'randstr'.rand(999, 999999);

		$db_events = GetModuleEvents("sale", "OnBeforeLocationGroupAdd");
		while ($arEvent = $db_events->Fetch())
			if (ExecuteModuleEventEx($arEvent, array($arFields))===false)
				return false;

		$arInsert = $DB->PrepareInsert("b_sale_location_group", $arFields);
		$strSql =
			"INSERT INTO b_sale_location_group(".$arInsert[0].") ".
			"VALUES(".$arInsert[1].")";

		$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$ID = IntVal($DB->LastID());

		// make IX_B_SALE_LOC_CODE feel happy
		Location\GroupTable::update($ID, array('CODE' => $ID));

		$countFieldLang = count($arFields["LANG"]);
		for ($i = 0; $i < $countFieldLang; $i++)
		{
			$arInsert = $DB->PrepareInsert("b_sale_location_group_lang", $arFields["LANG"][$i]);
			$strSql =
				"INSERT INTO b_sale_location_group_lang(LOCATION_GROUP_ID, ".$arInsert[0].") ".
				"VALUES(".$ID.", ".$arInsert[1].")";

			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

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
			$strSqlHead ="INSERT INTO b_sale_location2location_group (LOCATION_ID, LOCATION_GROUP_ID) VALUES ";
			$strSqlHeadLength = strlen($strSqlHead);

			$res = $DB->Query('SHOW VARIABLES LIKE \'max_allowed_packet\'');
			$maxPack = $res->Fetch();

			if(isset($maxPack["Value"]))
				$max_allowed_packet = $maxPack["Value"]-$strSqlHeadLength-100;
			else
				$max_allowed_packet = 0;

			$tmpSql = '';
			$strSql = '';
			$countFieldLoc = count($arFields["LOCATION_ID"]);
			for ($i = 0; $i < $countFieldLoc; $i++)
			{
				$tmpSql ="(".$arFields["LOCATION_ID"][$i].", ".$ID.")";
				$strSqlLen = strlen($strSql);

				if($strSqlHeadLength + $strSqlLen + strlen($tmpSql) < $max_allowed_packet || $max_allowed_packet <= 0)
				{
					if($strSqlLen > 0)
						$strSql .=",";

					$strSql .= $tmpSql;
				}
				else
				{
					$DB->Query($strSqlHead.$strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
					$strSql = $tmpSql;
				}
			}

			if(strlen($strSql) > 0)
				$DB->Query($strSqlHead.$strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		}

		$events = GetModuleEvents("sale", "OnLocationGroupAdd");
		while ($arEvent = $events->Fetch())
			ExecuteModuleEventEx($arEvent, array($ID, $arFields));

		return $ID;
	}
}
?>