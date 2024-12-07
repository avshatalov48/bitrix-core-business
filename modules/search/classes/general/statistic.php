<?php

class CSearchStatistic
{
	var $phrase_id = 0;

	var $_phrase = false;
	var $_tags = false;
	var $_session_id = "";
	var $_stat_sess_id = false;

	function __construct($phrase = "", $tags = "")
	{
		$phrase = mb_strtolower(trim($phrase, " \t\n\r"));
		if ($l = mb_strlen($phrase))
		{
			if ($l > 250)
			{
				$p = mb_strrpos($phrase, ' ');
				if ($p === false)
					$this->_phrase = mb_substr($phrase, 0, 250);
				else
					$this->_phrase = mb_substr($phrase, 0, $p);
			}
			else
			{
				$this->_phrase = $phrase;
			}
		}
		else
		{
			$this->_phrase = false;
		}

		$arTags = tags_prepare($tags);
		if (count($arTags))
		{
			asort($arTags);
			$this->_tags = implode(", ", $arTags);
		}
		else
		{
			$this->_tags = false;
		}

		$this->_session_id = bitrix_sessid();

		if (isset($_SESSION["SESS_SESSION_ID"]))
			$this->_stat_sess_id = intval($_SESSION["SESS_SESSION_ID"]);
	}

	function PhraseStat($result_count = 0, $page_num = 0)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$DB->StartUsingMasterOnly();

		$result_count = intval($result_count);
		$page_num = intval($page_num);

		$strSql = "
			SELECT *
			FROM b_search_phrase
			WHERE SESSION_ID = '".$DB->ForSQL($this->_session_id)."'
			AND ".($this->_phrase === false? "PHRASE IS NULL": "PHRASE = '".$DB->ForSQL($this->_phrase)."'")."
			AND ".($this->_tags === false? "TAGS IS NULL": "TAGS = '".$DB->ForSQL($this->_tags)."'")."
		";
		$rs = $DB->Query($strSql);
		if ($ar = $rs->Fetch())
		{
			$this->phrase_id = $ar["ID"];
			if ($page_num > $ar["PAGES"])
				$DB->Query("UPDATE b_search_phrase SET PAGES = ".$page_num." WHERE ID = ".$ar["ID"]);
		}
		else
		{
			$this->phrase_id = $DB->Add("b_search_phrase",
				array(
					"~TIMESTAMP_X" => $DB->CurrentTimeFunction(),
					"SITE_ID" => SITE_ID,
					"~RESULT_COUNT" => $result_count,
					"~PAGES" => $page_num,
					"SESSION_ID" => $this->_session_id,
					"PHRASE" => $this->_phrase,
					"TAGS" => $this->_tags,
					"STAT_SESS_ID" => $this->_stat_sess_id,
				)
			);
		}
		$DB->StopUsingMasterOnly();
	}

	public static function GetList($arOrder = false, $arFilter = false, $arSelect = false, $bGroup = false)
	{
		$DB = CDatabase::GetModuleConnection('search');

		static $arDefSelect = array(
			"ID",
			"TIMESTAMP_X",
			"SITE_ID",
			"RESULT_COUNT",
			"PAGES",
			"PHRASE",
			"TAGS",
			"URL_TO",
			"URL_TO_404",
			"URL_TO_SITE_ID",
			"STAT_SESS_ID",
		);

		if (!is_array($arSelect))
			$arSelect = array();
		if (count($arSelect) < 1)
			$arSelect = $arDefSelect;

		if (!is_array($arOrder))
			$arOrder = array();
		if (count($arOrder) < 1)
			$arOrder = array(
				"ID" => "DESC",
			);

		$arQueryOrder = array();
		foreach ($arOrder as $strColumn => $strDirection)
		{
			$strColumn = mb_strtoupper($strColumn);
			$strDirection = mb_strtoupper($strDirection) == "ASC"? "ASC": "DESC";
			if (in_array($strColumn, $arDefSelect))
			{
				$arSelect[] = $strColumn;
				if ($strColumn == "TIMESTAMP_X")
					$arQueryOrder[$strColumn] = "TMP_TS ".$strDirection;
				else
					$arQueryOrder[$strColumn] = $strColumn." ".$strDirection;
			}
			elseif ($strColumn == "COUNT" && $bGroup)
			{
				$arSelect[] = $strColumn;
				$arQueryOrder[$strColumn] = $strColumn." ".$strDirection;
			}
		}

		$arQueryGroup = array();
		$arQuerySelect = array();
		foreach ($arSelect as $strColumn)
		{
			$strColumn = mb_strtoupper($strColumn);
			if (in_array($strColumn, $arDefSelect))
			{
				if ($strColumn == "TIMESTAMP_X")
				{
					$arQuerySelect["TMP_TS"] = "sph.".$strColumn." TMP_TS";
					$arQuerySelect[$strColumn] = $DB->DateToCharFunction("sph.".$strColumn, "FULL")." ".$strColumn;
				}
				else
				{
					$arQuerySelect[$strColumn] = "sph.".$strColumn;
				}

				if ($bGroup)
					$arQueryGroup[$strColumn] = "sph.".$strColumn;
			}
			elseif ($strColumn == "COUNT" && $bGroup)
			{
				$arQuerySelect[$strColumn] = "count(*) ".$strColumn;
			}
		}

		$obQueryWhere = new CSQLWhere;
		$obQueryWhere->SetFields(array(
			"ID" => array(
				"TABLE_ALIAS" => "sph",
				"FIELD_NAME" => "sph.ID",
				"FIELD_TYPE" => "int", //int, double, file, enum, int, string, date, datetime
				"JOIN" => false,
			),
			"PHRASE" => array(
				"TABLE_ALIAS" => "sph",
				"FIELD_NAME" => "sph.PHRASE",
				"FIELD_TYPE" => "string",
				"JOIN" => false,
			),
			"TAGS" => array(
				"TABLE_ALIAS" => "sph",
				"FIELD_NAME" => "sph.TAGS",
				"FIELD_TYPE" => "string",
				"JOIN" => false,
			),
			"TIMESTAMP_X" => array(
				"TABLE_ALIAS" => "sph",
				"FIELD_NAME" => "sph.TIMESTAMP_X",
				"FIELD_TYPE" => "datetime",
				"JOIN" => false,
			),
			"SITE_ID" => array(
				"TABLE_ALIAS" => "sph",
				"FIELD_NAME" => "sph.SITE_ID",
				"FIELD_TYPE" => "string",
				"JOIN" => false,
			),
			"URL_TO" => array(
				"TABLE_ALIAS" => "sph",
				"FIELD_NAME" => "sph.URL_TO",
				"FIELD_TYPE" => "string",
				"JOIN" => false,
			),
			"URL_TO_404" => array(
				"TABLE_ALIAS" => "sph",
				"FIELD_NAME" => "sph.URL_TO_404",
				"FIELD_TYPE" => "string",
				"JOIN" => false,
			),
			"STAT_SESS_ID" => array(
				"TABLE_ALIAS" => "sph",
				"FIELD_NAME" => "sph.STAT_SESS_ID",
				"FIELD_TYPE" => "int",
				"JOIN" => false,
			),
			"RESULT_COUNT" => array(
				"TABLE_ALIAS" => "sph",
				"FIELD_NAME" => "sph.RESULT_COUNT",
				"FIELD_TYPE" => "int",
				"JOIN" => false,
			),
		));

		if (count($arQuerySelect) < 1)
			$arQuerySelect = array("ID" => "sph.ID");

		$strSql = "
			SELECT
			".implode(", ", $arQuerySelect)."
			FROM
				b_search_phrase sph
		";

		if (!is_array($arFilter))
			$arFilter = array();
		if ($strQueryWhere = $obQueryWhere->GetQuery($arFilter))
		{
			$strSql .= "
				WHERE
				".$strQueryWhere."
			";
		}

		if ($bGroup && count($arQueryGroup) > 0)
		{
			$strSql .= "
				GROUP BY
				".implode(", ", $arQueryGroup)."
			";
		}

		if (count($arQueryOrder) > 0)
		{
			$strSql .= "
				ORDER BY
				".implode(", ", $arQueryOrder)."
			";
		}

		return $DB->Query($strSql);
	}

	public static function CleanUpAgent()
	{
		$DB = CDatabase::GetModuleConnection('search');
		$cleanup_days = COption::GetOptionInt("search", "stat_phrase_save_days");
		if ($cleanup_days > 0)
		{
			$arDate = localtime(time());
			$date = mktime(0, 0, 0, $arDate[4] + 1, $arDate[3] - $cleanup_days, 1900 + $arDate[5]);
			$DB->Query("DELETE FROM b_search_phrase WHERE TIMESTAMP_X <= ".$DB->CharToDateFunction(ConvertTimeStamp($date, "FULL")));
		}
		return "CSearchStatistic::CleanUpAgent();";
	}

	public static function IsActive()
	{
		$bActive = false;
		foreach (GetModuleEvents("main", "OnEpilog", true) as $arEvent)
		{
			if (
				$arEvent["TO_MODULE_ID"] == "search"
				&& $arEvent["TO_CLASS"] == "CSearchStatistic"
			)
			{
				$bActive = true;
				break;
			}
		}
		return $bActive;
	}

	public static function SetActive($bActive = false)
	{
		if ($bActive)
		{
			if (!CSearchStatistic::IsActive())
				RegisterModuleDependences("main", "OnEpilog", "search", "CSearchStatistic", "OnEpilog", "90");
		}
		else
		{
			if (CSearchStatistic::IsActive())
				UnRegisterModuleDependences("main", "OnEpilog", "search", "CSearchStatistic", "OnEpilog");
		}
	}

	public static function GetCurrentURL()
	{
		$res = (CMain::IsHTTPS()? "https": "http")."://";

		$host = $_SERVER["HTTP_HOST"];
		$res .= $host;

		$port = intval($_SERVER["SERVER_PORT"]);
		if ($port > 0 && $port != 80 && $port != 443 && mb_strpos($host, ":") === false)
			$res .= ":".$port;

		$url = preg_replace("/\\?sphrase_id=\\d+&/", "?", $_SERVER["REQUEST_URI"]);
		$url = preg_replace("/\\?sphrase_id=\\d+/", "", $url);
		$url = preg_replace("/&sphrase_id=\\d+/", "", $url);

		$res .= $url;

		return $res;
	}

	public static function OnEpilog()
	{
		if (isset($_REQUEST["sphrase_id"]))
		{
			$phrase_id = intval($_REQUEST["sphrase_id"]);
			if ($phrase_id)
			{
				$DB = CDatabase::GetModuleConnection('search');
				$DB->StartUsingMasterOnly();
				$rs = $DB->Query("
					SELECT *
					FROM b_search_phrase
					WHERE ID = ".$phrase_id."
					AND SESSION_ID = '".$DB->ForSQL(bitrix_sessid())."'
					AND URL_TO IS NULL
				");
				if ($ar = $rs->Fetch())
				{
					$URL_TO = $DB->ForSQL(CSearchStatistic::GetCurrentURL(), 2000);
					$DB->Query("
						UPDATE b_search_phrase
						SET URL_TO = '".$URL_TO."'
							,URL_TO_404 = '".(defined("ERROR_404")? "Y": "N")."'
							,URL_TO_SITE_ID = ".(defined("SITE_ID")? "'".$DB->ForSQL(SITE_ID, 2)."'": "null")."
						WHERE ID = ".$phrase_id."
					");
				}
				$DB->StopUsingMasterOnly();
			}
		}
	}
}
