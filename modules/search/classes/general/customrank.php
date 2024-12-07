<?php
IncludeModuleLangFile(__FILE__);

class CSearchCustomRank
{
	var $LAST_ERROR = "";

	public static function GetList($aSort = array(), $aFilter = array())
	{
		$DB = CDatabase::GetModuleConnection('search');

		$arFilter = array();
		foreach ($aFilter as $key => $val)
		{
			$val = $DB->ForSql($val);
			$key = mb_strtoupper($key);
			if ($val == '')
				continue;
			switch ($key)
			{
			case "SITE_ID":
			case "MODULE_ID":
			case "PARAM1":
			case "PARAM2":
			case "ITEM_ID":
			case "ID":
			case "APPLIED":
				$arFilter[] = "CR.".$key."='".$val."'";
				break;
			}
		}

		$arOrder = array();
		foreach ($aSort as $key => $val)
		{
			$ord = (mb_strtoupper($val) <> "ASC"? "DESC": "ASC");
			$key = mb_strtoupper($key);
			switch ($key)
			{
			case "SITE_ID":
			case "MODULE_ID":
			case "PARAM1":
			case "PARAM2":
			case "ITEM_ID":
			case "ID":
			case "APPLIED":
			case "RANK":
				$arOrder[] = "CR.".$key." ".$ord;
				break;
			}
		}

		if (count($arOrder) == 0)
			$arOrder = array(
				"CR.SITE_ID ASC"
			, "CR.MODULE_ID ASC"
			, "CR.PARAM1 DESC"
			, "CR.PARAM2 DESC"
			, "CR.ITEM_ID DESC"
			);
		$sOrder = "\nORDER BY ".implode(", ", $arOrder);

		if (count($arFilter) == 0)
			$sFilter = "";
		else
			$sFilter = "\nWHERE ".implode("\nAND ", $arFilter);

		$strSql = "
			SELECT
				CR.ID
				,CR.SITE_ID
				,CR.MODULE_ID
				,CR.PARAM1
				,CR.PARAM2
				,CR.ITEM_ID
				,CR.RANK
			FROM
				b_search_custom_rank CR
			".$sFilter.$sOrder;

		return $DB->Query($strSql);
	}

	public static function GetByID($ID)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$ID = intval($ID);

		$strSql = "
			SELECT CR.*
			FROM b_search_custom_rank CR
			WHERE CR.ID = ".$ID."
		";

		return $DB->Query($strSql);
	}

	public static function Delete($ID)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$ID = intval($ID);

		return $DB->Query("DELETE FROM b_search_custom_rank WHERE ID=".$ID);
	}

	function CheckFields($arFields)
	{
		$this->LAST_ERROR = "";

		if (is_set($arFields, "SITE_ID") && $arFields["SITE_ID"] == '')
			$this->LAST_ERROR .= GetMessage("customrank_error_site")."<br>";
		if (is_set($arFields, "MODULE_ID") && $arFields["MODULE_ID"] == '')
			$this->LAST_ERROR .= GetMessage("customrank_error_module")."<br>";

		if ($this->LAST_ERROR <> '')
			return false;
		else
			return true;
	}

	function Add($arFields)
	{
		$DB = CDatabase::GetModuleConnection('search');

		if (!$this->CheckFields($arFields))
			return false;

		return $DB->Add("b_search_custom_rank", $arFields);
	}

	function Update($ID, $arFields)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$ID = intval($ID);

		if (!$this->CheckFields($arFields))
			return false;

		unset($arFields["ID"]);

		$strUpdate = $DB->PrepareUpdate("b_search_custom_rank", $arFields);
		if ($strUpdate != "")
		{
			$strSql =
				"UPDATE b_search_custom_rank SET ".$strUpdate." ".
				"WHERE ID=".$ID;
			return $DB->Query($strSql);
		}
		return true;
	}

	function StartUpdate()
	{
		$DB = CDatabase::GetModuleConnection('search');
		$strSql = "
			UPDATE b_search_custom_rank
			SET APPLIED='N'
		";
		$rs = $DB->Query($strSql);
		if ($rs)
		{
			$strSql = "
				UPDATE b_search_content
				SET CUSTOM_RANK=0
				WHERE CUSTOM_RANK<>0
			";
			$rs = $DB->Query($strSql);
		}
		return $rs;
	}

	function NextUpdate()
	{
		$DB = CDatabase::GetModuleConnection('search');

		$rs = $this->GetList(
			array(
				"SITE_ID" => "ASC"
			, "MODULE_ID" => "ASC"
			, "PARAM1" => "ASC"
			, "PARAM2" => "ASC"
			, "ITEM_ID" => "ASC"
			)
			, array(
				"APPLIED" => "N"
			)
		);
		if ($ar = $rs->Fetch())
		{
			$strSql = "
				UPDATE b_search_content
				SET CUSTOM_RANK=".intval($ar["RANK"])."
				WHERE CUSTOM_RANK<>".intval($ar["RANK"])."
				AND EXISTS (
					SELECT *
					FROM b_search_content_site scs
					WHERE scs.SEARCH_CONTENT_ID = b_search_content.ID
					AND scs.SITE_ID = '".$DB->ForSQL($ar["SITE_ID"])."'
				)
				AND MODULE_ID='".$DB->ForSQL($ar["MODULE_ID"])."'
				".($ar["PARAM1"] != ""? "AND PARAM1='".$DB->ForSQL($ar["PARAM1"])."'": "")."
				".($ar["PARAM2"] != ""? "AND PARAM2='".$DB->ForSQL($ar["PARAM2"])."'": "")."
				".($ar["ITEM_ID"] != ""? "AND ITEM_ID='".$DB->ForSQL($ar["ITEM_ID"])."'": "")."
			";
			$upd = $DB->Query($strSql);
			if ($upd)
				$upd = $this->Update($ar["ID"], array("APPLIED" => "Y"));
			else
				$this->LAST_ERROR = GetMessage("customrank_error_update")."<br>";
		}

		if ($this->LAST_ERROR == "")
		{
			$res = array("DONE" => 0, "TODO" => 0);
			$strSql = "
				SELECT APPLIED,COUNT(*) C
				FROM b_search_custom_rank
				GROUP BY APPLIED
			";
			$rs = $DB->Query($strSql);
			while ($ar = $rs->Fetch())
				if ($ar["APPLIED"] == "Y")
					$res["DONE"] = $ar["C"];
				elseif ($ar["APPLIED"] == "N")
					$res["TODO"] = $ar["C"];
			return $res;
		}
		else
		{
			return false;
		}
	}

	public static function __GetParam($lang, $site_id, $module_id = false, $param1 = false, $param2 = false, $item_id = false)
	{
		$name = "";
		if ($module_id == "iblock" && CModule::IncludeModule("iblock"))
		{
			if ($item_id !== false)
			{
				$rs = CIBlockElement::GetByID($item_id);
				if ($ar = $rs->GetNext())
					$name = $ar["NAME"];
			}
			elseif ($param2 !== false)
			{
				$rs = CIBlock::GetByID($param2);
				if ($ar = $rs->GetNext())
					$name = $ar["NAME"];
			}
			elseif ($param1 !== false)
			{
				$rs = CIBlockType::GetByIDLang($param1, $lang);
				if (is_array($rs))
					$name = $rs["NAME"];
			}
			else
			{
				$name = GetMessage("customrank_iblocks");
			}
		}
		elseif ($module_id == "forum" && CModule::IncludeModule("forum"))
		{
			if ($item_id !== false)
			{
				$name = "";
			}
			elseif ($param2 !== false)
			{
				$rs = CForumTopic::GetByID($param2);
				if (is_array($rs))
					$name = htmlspecialcharsex($rs["TITLE"]);
			}
			elseif ($param1 !== false)
			{
				$rs = CForumNew::GetByID($param1);
				if (is_array($rs))
					$name = htmlspecialcharsex($rs["NAME"]);
			}
			else
			{
				$name = GetMessage("customrank_forum");
			}
		}
		elseif ($module_id == "main")
		{
			if ($item_id !== false)
			{
				$name = "";
			}
			else
			{
				$name = GetMessage("customrank_files");
			}

		}
		elseif ($module_id === false)
		{
			$rs = CSite::GetByID($site_id);
			if ($ar = $rs->GetNext())
				$name = $ar["NAME"];
		}
		else
		{
			$name = false;
		}

		return $name;
	}

	public static function ModulesList()
	{
		return array_merge(array("main" => GetMessage("customrank_files")), CSearchParameters::GetModulesList());
	}

	public static function ModulesSelectBox($sFieldName, $sValue, $sDefaultValue = "", $sFuncName = "", $field = "class=\"typeselect\"")
	{
		$s = '<select name="'.$sFieldName.'" id="'.$sFieldName.'" '.$field;
		if ($sFuncName <> '') $s .= ' OnChange="'.$sFuncName.'"';
		$s .= '>'."\n";

		$s1 = '<option value="main"'.($sValue == "main"? ' selected': '').'>'.GetMessage("customrank_files").'</option>'."\n";
		foreach (CSearchParameters::GetModulesList() as $module_id => $module_name)
			$s1 .= '<option value="'.$module_id.'"'.($sValue == $module_id? ' selected': '').'>'.htmlspecialcharsEx($module_name).'</option>'."\n";

		if ($sDefaultValue <> '')
			$s .= "<option value='NOT_REF'>".htmlspecialcharsEx($sDefaultValue)."</option>";

		return $s.$s1.'</select>';
	}
}
