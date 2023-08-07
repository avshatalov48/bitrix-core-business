<?php

class CAllSocNetSmile
{
	public static function PrintSmilesList($num_cols, $strLang = False, $strPath2Icons = False, $cacheTime = False)
	{
		$res_str = "";
		$arSmile = array();
		$return_array = intval($num_cols) > 0 ? false : true;
		if ($strLang === False)
			$strLang = LANGUAGE_ID;
		if ($strPath2Icons === False)
			$strPath2Icons = "/bitrix/images/socialnetwork/smile/";
		$cache = new CPHPCache;
		$cache_id = "socialnetwork_smiles_".$strLang.preg_replace("/[^a-z0-9]/is", "_", $strPath2Icons);
		
		$cache_path = "/".SITE_ID."/socialnetwork/smiles/";
		if ($cacheTime > 0 && $cache->InitCache($cacheTime, $cache_id, $cache_path))
		{
			$res = $cache->GetVars();
			$arSmile = $res["arSmile"];
		}
		
		if (empty($arSmile))
		{
			$db_res = CSocNetSmile::GetList(array("SORT"=>"ASC"), array("TYPE"=>"S", "LID"=>LANGUAGE_ID));
			if ($db_res && ($res = $db_res->Fetch()))
			{
				do
				{
					$arSmile[] = $res;
				}
				while ($res = $db_res->Fetch());
			}
			if ($cacheTime > 0)
			{
				$cache->StartDataCache($cacheTime, $cache_id, $cache_path);
				$cache->EndDataCache(array("arSmile"=>$arSmile));
			}
		}
		
		if ($return_array)
			return $arSmile;
		
		$res_str = "";
		$ind = 0;
		foreach ($arSmile as $res)
		{
			if ($ind == 0) {$res_str .= "<tr align=\"center\">";}
			$res_str .= "<td width=\"".intval(100/$num_cols)."%\">";
			$strTYPING = strtok($res['TYPING'], " ");
			$res_str .= "<img src=\"".$strPath2Icons.$res['IMAGE']."\" alt=\"".$res['NAME']."\" title=\"".$res['NAME']."\" border=\"0\"";
			if (intval($res['IMAGE_WIDTH'])>0) {$res_str .= " width=\"".$res['IMAGE_WIDTH']."\"";}
			if (intval($res['IMAGE_HEIGHT'])>0) {$res_str .= " height=\"".$res['IMAGE_HEIGHT']."\"";}
			$res_str .= " onclick=\"if(emoticon){emoticon('".$strTYPING."');}\" name=\"smile\"  id='".$strTYPING."' ";
			$res_str .= "/>&nbsp;</td>\n";
			$ind++;
			if ($ind >= $num_cols)
			{
				$ind = 0;
				$res_str .= "</tr>";
			}
		}
		if ($ind < $num_cols)
		{
			for ($i=0; $i<$num_cols-$ind; $i++)
			{
				$res_str .= "<td> </td>";
			}
		}
		
		return $res_str;
	}

	//---------------> User insert, update, delete
	public static function CheckFields($ACTION, &$arFields)
	{
		if ((is_set($arFields, "SMILE_TYPE") || $ACTION=="ADD") && $arFields["SMILE_TYPE"]!="I" && $arFields["SMILE_TYPE"]!="S") return False;
		if ((is_set($arFields, "IMAGE") || $ACTION=="ADD") && $arFields["IMAGE"] == '') return False;

		if ((is_set($arFields, "SORT") || $ACTION=="ADD") && intval($arFields["SORT"])<=0) $arFields["SORT"] = 150;

		if (is_set($arFields, "LANG") || $ACTION=="ADD")
		{
			for ($i = 0; $i<count($arFields["LANG"]); $i++)
			{
				if (!is_set($arFields["LANG"][$i], "LID") || $arFields["LANG"][$i]["LID"] == '') return false;
				if (!is_set($arFields["LANG"][$i], "NAME") || $arFields["LANG"][$i]["NAME"] == '') return false;
			}

			$db_lang = CLangAdmin::GetList("sort", "asc", array("ACTIVE" => "Y"));
			while ($arLang = $db_lang->Fetch())
			{
				$bFound = False;
				for ($i = 0; $i<count($arFields["LANG"]); $i++)
				{
					if ($arFields["LANG"][$i]["LID"]==$arLang["LID"])
						$bFound = True;
				}
				if (!$bFound) return false;
			}
		}

		return True;
	}

	public static function Delete($ID)
	{
		global $DB, $CACHE_MANAGER;
		$ID = intval($ID);

		$DB->Query("DELETE FROM b_sonet_smile_lang WHERE SMILE_ID = ".$ID, True);
		$DB->Query("DELETE FROM b_sonet_smile WHERE ID = ".$ID, True);
		$CACHE_MANAGER->Clean("b_sonet_smile");

		return true;
	}

	public static function GetByID($ID)
	{
		global $DB;

		$ID = intval($ID);
		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.SMILE_TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, ".
			"	FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_sonet_smile FR ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	public static function GetByIDEx($ID, $strLang)
	{
		global $DB;

		$ID = intval($ID);
		$strSql = 
			"SELECT FR.ID, FR.SORT, FR.SMILE_TYPE, FR.TYPING, FR.IMAGE, FR.CLICKABLE, ".
			"	FRL.LID, FRL.NAME, FR.DESCRIPTION, FR.IMAGE_WIDTH, FR.IMAGE_HEIGHT ".
			"FROM b_sonet_smile FR ".
			"	LEFT JOIN b_sonet_smile_lang FRL ON (FR.ID = FRL.SMILE_ID AND FRL.LID = '".$DB->ForSql($strLang)."') ".
			"WHERE FR.ID = ".$ID."";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}

	public static function GetLangByID($SMILE_ID, $strLang)
	{
		global $DB;

		$SMILE_ID = intval($SMILE_ID);
		$strSql = 
			"SELECT FRL.ID, FRL.SMILE_ID, FRL.LID, FRL.NAME ".
			"FROM b_sonet_smile_lang FRL ".
			"WHERE FRL.SMILE_ID = ".$SMILE_ID." ".
			"	AND FRL.LID = '".$DB->ForSql($strLang)."' ";
		$db_res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res = $db_res->Fetch())
		{
			return $res;
		}
		return False;
	}
}
