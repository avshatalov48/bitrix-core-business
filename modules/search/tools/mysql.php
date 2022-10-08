<?php
IncludeModuleLangFile(__FILE__);

class CSearchMysql extends CSearchFullText
{
	protected $error = "";
	protected $errorno = 0;

	public function connect($connectionString = '')
	{
		global $APPLICATION;
		$DB = CDatabase::GetModuleConnection('search');

		if (!$DB->TableExists("b_search_content_text"))
		{
			$APPLICATION->ThrowException(GetMessage("SEARCH_MYSQL_OLD_SCHEMA"));
			return false;
		}

		if (!$DB->IndexExists("b_search_content_text", array("SEARCHABLE_CONTENT")))
		{
			$r = $DB->Query("create fulltext index fti on b_search_content_text(SEARCHABLE_CONTENT)", true);
			if (!$r)
			{
				$APPLICATION->ThrowException(GetMessage("SEARCH_MYSQL_INDEX_CREATE_ERROR", array("#ERRSTR#" => $DB->db_Error)));
				return false;
			}
		}

		return true;
	}

	public function truncate()
	{
		$DB = CDatabase::GetModuleConnection('search');
		$DB->Query("TRUNCATE TABLE b_search_content_text", false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	public function deleteById($ID)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$DB->Query("DELETE FROM b_search_content_text WHERE SEARCH_CONTENT_ID = ".$ID, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}

	public function replace($ID, $arFields)
	{
		$DB = CDatabase::GetModuleConnection('search');

		if (array_key_exists("SEARCHABLE_CONTENT", $arFields))
		{
			$text_md5 = md5($arFields["SEARCHABLE_CONTENT"]);
			$rsText = $DB->Query("SELECT SEARCH_CONTENT_MD5 FROM b_search_content_text WHERE SEARCH_CONTENT_ID = ".$ID);
			$arText = $rsText->Fetch();
			if (!$arText || $arText["SEARCH_CONTENT_MD5"] !== $text_md5)
			{
				$DB->Query("
					REPLACE INTO b_search_content_text
					(SEARCH_CONTENT_ID, SEARCH_CONTENT_MD5, SEARCHABLE_CONTENT)
					values
					(".$ID.", '".$DB->ForSql($text_md5)."', '".$DB->ForSql($arFields["SEARCHABLE_CONTENT"])."')
				");
			}
		}
	}

	public function search($arParams, $aSort, $aParamsEx, $bTagsCloud)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$queryObject = $aParamsEx["QUERY_OBJECT"];
		if ($queryObject->m_parsed_query == "( )" || $queryObject->m_parsed_query == '')
		{
			$this->error = GetMessage("SEARCH_ERROR3");
			$this->errorno = 3;
			return array();
		}

		$strQuery = $this->PrepareQuery($queryObject, $queryObject->m_parsed_query);

		$strTags = '';
		if (array_key_exists("TAGS", $arParams))
		{
			$this->strTagsText = $arParams["TAGS"];
			$arTags = explode(",", $arParams["TAGS"]);
			foreach ($arTags as $i => $strTag)
			{
				$arTags[$i] = trim($strTag, '"');
			}

			if ($arTags)
				$strTags = '+(+"'.implode('" +"', $arTags).'")';
		}

		if (($strQuery == '') && ($strTags <> ''))
		{
			$strQuery = $strTags;
			$bTagsSearch = true;
		}
		else
		{
			$strQuery = preg_replace_callback("/&#(\\d+);/", "chr", $strQuery);
			$bTagsSearch = false;
		}
		
		$query = "match sct.SEARCHABLE_CONTENT against('".$DB->ForSql($strQuery)."' in boolean mode)";

		$arSqlWhere = array();
		if (is_array($aParamsEx) && !empty($aParamsEx))
		{
			foreach ($aParamsEx as $aParamEx)
			{
				$strSqlWhere = CSearch::__PrepareFilter($aParamEx, $bIncSites);
				if ($strSqlWhere != "")
					$arSqlWhere[] = $strSqlWhere;
			}
		}
		if (!empty($arSqlWhere))
		{
			$arSqlWhere = array(
				"\n\t\t\t\t(".implode(")\n\t\t\t\t\tOR(", $arSqlWhere)."\n\t\t\t\t)",
			);
		}

		$strSqlWhere = CSearch::__PrepareFilter($arParams, $bIncSites);
		if ($strSqlWhere != "")
			array_unshift($arSqlWhere, $strSqlWhere);

		$strSqlOrder = $this->__PrepareSort($aSort, "sc.", $bTagsCloud);

		if (!empty($arSqlWhere))
		{
			$strSqlWhere = "\n\t\t\t\tAND (\n\t\t\t\t\t(".implode(")\n\t\t\t\t\tAND(", $arSqlWhere).")\n\t\t\t\t)";
		}

		if ($bTagsCloud)
		{
			$strSql = "
				SELECT
					stags.NAME
					,COUNT(DISTINCT stags.SEARCH_CONTENT_ID) as CNT
					,MAX(sc.DATE_CHANGE) DC_TMP
					,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)")." as FULL_DATE_CHANGE
					,".$DB->DateToCharFunction("MAX(sc.DATE_CHANGE)", "SHORT")." as DATE_CHANGE
				FROM b_search_tags stags
					INNER JOIN b_search_content sc ON (stags.SEARCH_CONTENT_ID=sc.ID)
					INNER JOIN b_search_content_text sct ON sct.SEARCH_CONTENT_ID = sc.ID
					INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
				WHERE
					".CSearch::CheckPermissions("sc.ID")."
					AND (".$query.")
					AND stags.SITE_ID = scsite.SITE_ID
					".$strSqlWhere."
				GROUP BY
					stags.NAME
				".$strSqlOrder."
			";

		}
		else
		{
			$strSql = "
				SELECT
					sct.SEARCH_CONTENT_ID
					,scsite.SITE_ID
					,".$query." `RANK`
				FROM
					b_search_content_text sct
					INNER JOIN b_search_content sc ON sc.ID = sct.SEARCH_CONTENT_ID
					INNER JOIN b_search_content_site scsite ON sc.ID = scsite.SEARCH_CONTENT_ID
				WHERE
					".CSearch::CheckPermissions("sc.ID")."
					AND (".$query.")
					".$strSqlWhere."
			".$strSqlOrder;
		}

		$r = $DB->Query($strSql);
		$result = array();
		while ($a = $r->Fetch())
		{
			$result[] = $a;
		}

		return $result;
	}

	function searchTitle($phrase = "", $arPhrase = array(), $nTopCount = 5, $arParams = array(), $bNotFilter = false, $order = "")
	{
		return false;
	}

	public function getErrorText()
	{
		return $this->error;
	}

	public function getErrorNumber()
	{
		return $this->errorno;
	}

	function getRowFormatter()
	{
		return new CSearchMySqlFormatter();
	}

	function __PrepareSort($aSort = array(), $strSearchContentAlias = "sc.", $bTagsCloud = false)
	{
		$arOrder = array();
		if (!is_array($aSort))
			$aSort = array($aSort => "ASC");

		if ($bTagsCloud)
		{
			foreach ($aSort as $key => $ord)
			{
				$ord = mb_strtoupper($ord) <> "ASC"? "DESC": "ASC";
				$key = mb_strtoupper($key);
				switch ($key)
				{
				case "DATE_CHANGE":
					$arOrder[] = "DC_TMP ".$ord;
					break;
				case "NAME":
				case "CNT":
					$arOrder[] = $key." ".$ord;
					break;
				}
			}
			if (!$arOrder)
			{
				$arOrder[] = "NAME ASC";
			}
		}
		else
		{
			$this->flagsUseRatingSort = 0;
			foreach ($aSort as $key => $ord)
			{
				$ord = mb_strtoupper($ord) <> "ASC"? "DESC": "ASC";
				$key = mb_strtoupper($key);
				switch ($key)
				{
				case "DATE_CHANGE":
					if (!($this->flagsUseRatingSort & 0x01))
						$this->flagsUseRatingSort = 0x02;
					$arOrder[] = $strSearchContentAlias.$key." ".$ord;
					break;
				case "RANK":
					if (!($this->flagsUseRatingSort & 0x02))
						$this->flagsUseRatingSort = 0x01;
					$arOrder[] = "`RANK` ".$ord;
					break;
				case "TITLE_RANK":
					$arOrder[] = "`RANK` ".$ord;
					break;
				case "CUSTOM_RANK":
					$arOrder[] = $strSearchContentAlias.$key." ".$ord;
					break;
				case "ID":
				case "MODULE_ID":
				case "ITEM_ID":
				case "TITLE":
				case "PARAM1":
				case "PARAM2":
				case "UPD":
				case "DATE_FROM":
				case "DATE_TO":
				case "URL":
					if (!($this->flagsUseRatingSort & 0x01))
						$this->flagsUseRatingSort = 0x02;
					$arOrder[] = $strSearchContentAlias.$key." ".$ord;
					break;
				}
			}

			if (!$arOrder)
			{
				$arOrder[] = "CUSTOM_RANK DESC";
				$arOrder[] = "`RANK` DESC";
				$arOrder[] = $strSearchContentAlias."DATE_CHANGE DESC";
				$this->flagsUseRatingSort = 0x01;
			}
		}

		return " ORDER BY ".implode(", ", $arOrder);
	}

	function PrepareQuery($queryObject, $q)
	{
		$state = 0;
		$qu = array();
		$n = 0;
		$this->error = "";
		$this->errorno = 0;

		$t = strtok($q, " ");
		while (($t != "") && ($this->error == ""))
		{
			if ($state == 0)
			{
				if (($t == "||") || ($t == "&&") || ($t == ")"))
				{
					$this->error = GetMessage("SEARCH_ERROR2")." ".$t;
					$this->errorno = 2;
				}
				elseif ($t == "!")
				{
					$state = 0;
					$qu[] = " -";
					$p = count($qu) - 2;
					if (isset($qu[$p]) && $qu[$p]=== " +")
						$qu[$p] = "";
				}
				elseif ($t == "(")
				{
					$n++;
					$state = 0;
					$qu[] = "(";
				}
				else
				{
					$state = 1;

					if (isset($queryObject->m_kav[$t]))
					{
						$t = '"'.$queryObject->m_kav[$t].'"';
					}
					elseif ($queryObject->bStemming)
					{
						$t = trim($t, "-")."*";
					}
					else
					{
						$t = trim($t, "-");
					}

					$p = count($qu) - 1;
					if (!isset($qu[$p]) || $qu[$p]!== " -")
						$qu[] = "";
					$qu[] = $t;
				}
			}
			elseif ($state == 1)
			{
				if (($t == "||") || ($t == "&&"))
				{
					$state = 0;
					if ($t == '||')
					{
						$qu[] = " ";
					}
					else
					{
						$qu[] = " +";
						$p = count($qu) - 3;
						if (isset($qu[$p]) && $qu[$p]=== "" && (!isset($qu[$p-1]) || $qu[$p-1] !== ' +'))
							$qu[$p] = " +";
					}
				}
				elseif ($t == ")")
				{
					$n--;
					$state = 1;
					$qu[] = ")";
				}
				else
				{
					$this->error = GetMessage("SEARCH_ERROR2")." ".$t;
					$this->errorno = 2;
				}
			}
			else
			{

				break;
			}
			$t = strtok(" ");
		}

		if (($this->error == "") && ($n != 0))
		{
			$this->error = GetMessage("SEARCH_ERROR1");
			$this->errorno = 1;
		}

		if ($this->error != "")
		{
			return "";
		}

		return implode($qu);
	}
}

class CSearchMySqlFormatter extends CSearchFormatter
{
	function format($r)
	{
		if ($r)
		{
			if (array_key_exists("CNT", $r))
				return $r;
			elseif  (array_key_exists("SEARCH_CONTENT_ID", $r))
				return $this->formatRow($r);
		}
	}

	function formatRow($r)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$rs = $DB->Query($q="
			select
				sc.ID
				,sc.MODULE_ID
				,sc.ITEM_ID
				,sc.TITLE
				,sc.TAGS
				,sc.BODY
				,sc.PARAM1
				,sc.PARAM2
				,sc.UPD
				,sc.DATE_FROM
				,sc.DATE_TO
				,sc.URL
				,sc.CUSTOM_RANK
				,".$DB->DateToCharFunction("sc.DATE_CHANGE")." as FULL_DATE_CHANGE
				,".$DB->DateToCharFunction("sc.DATE_CHANGE", "SHORT")." as DATE_CHANGE
				,scsite.SITE_ID
				,scsite.URL SITE_URL
				".(BX_SEARCH_VERSION > 1? ",sc.USER_ID": "")."
			from b_search_content sc
			INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID
			where ID = ".$r["SEARCH_CONTENT_ID"]."
			and scsite.SITE_ID = '".$r["SITE_ID"]."'
		");
		$r = $rs->Fetch();
		return $r;
	}
}

