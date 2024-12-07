<?php

class CAllSearchTitle extends CDBResult
{
	var $_arPhrase = array();
	var $_arStemFunc;
	var $minLength = 1;

	function __construct($res = null)
	{
		$this->_arStemFunc = stemming_init(LANGUAGE_ID);
		parent::__construct($res);
	}

	function Search($phrase = "", $nTopCount = 5, $arParams = array(), $bNotFilter = false, $order = "")
	{
		$DB = CDatabase::GetModuleConnection('search');
		$this->_arPhrase = stemming_split($phrase, LANGUAGE_ID);
		if (!empty($this->_arPhrase))
		{
			$nTopCount = intval($nTopCount);
			if ($nTopCount <= 0)
				$nTopCount = 5;

			$arId = CSearchFullText::getInstance()->searchTitle($phrase, $this->_arPhrase, $nTopCount, $arParams, $bNotFilter, $order);
			if (!is_array($arId))
			{
				return $this->searchTitle($phrase, $nTopCount, $arParams, $bNotFilter, $order);
			}
			elseif (!empty($arId))
			{
				$strSql = "
					SELECT
						sc.ID
						,sc.MODULE_ID
						,sc.ITEM_ID
						,sc.TITLE
						,sc.PARAM1
						,sc.PARAM2
						,sc.DATE_CHANGE
						,sc.URL as URL
						,scsite.URL as SITE_URL
						,scsite.SITE_ID
						,".$this->getRankFunction($phrase)." RANK1
					FROM
						b_search_content sc
						INNER JOIN b_search_content_site scsite ON sc.ID = scsite.SEARCH_CONTENT_ID
					WHERE
						sc.ID in (".implode(",", $arId).")
						and scsite.SITE_ID = '".SITE_ID."'
					ORDER BY ".$this->getSqlOrder($order == "rank")."
				";

				$r = $DB->Query($DB->TopSql($strSql, $nTopCount + 1));
				parent::__construct($r);
				return true;
			}
		}
		else
		{
			return false;
		}
	}

	function getRankFunction($phrase)
	{
		return "0";
	}

	function setMinWordLength($minLength)
	{
		$minLength = intval($minLength);
		if ($minLength > 0)
			$this->minLength = $minLength;
	}

	function Fetch()
	{
		static $arSite = array();

		$r = parent::Fetch();

		if ($r)
		{
			$site_id = $r["SITE_ID"];
			if (!isset($arSite[$site_id]))
			{
				$rsSite = CSite::GetList('', '', array("ID" => $site_id));
				$arSite[$site_id] = $rsSite->Fetch();
			}
			$r["DIR"] = $arSite[$site_id]["DIR"];
			$r["SERVER_NAME"] = $arSite[$site_id]["SERVER_NAME"];

			if ($r["SITE_URL"] <> '')
				$r["URL"] = $r["SITE_URL"];

			if (mb_substr($r["URL"], 0, 1) == "=")
			{
				foreach (GetModuleEvents("search", "OnSearchGetURL", true) as $arEvent)
				{
					$newUrl = ExecuteModuleEventEx($arEvent, array($r));
					if (isset($newUrl))
					{
						$r["URL"] = $newUrl;
					}
				}
			}

			$r["URL"] = str_replace(
				array("#LANG#", "#SITE_DIR#", "#SERVER_NAME#"),
				array($r["DIR"], $r["DIR"], $r["SERVER_NAME"]),
				$r["URL"]
			);
			$r["URL"] = preg_replace("'(?<!:)/+'s", "/", $r["URL"]);

			$r["NAME"] = htmlspecialcharsEx($r["TITLE"]);

			$preg_template = "/(^|[^".$this->_arStemFunc["pcre_letters"]."])(".str_replace("/", "\\/", implode("|", array_map('preg_quote', array_keys($this->_arPhrase)))).")/iu";
			if (preg_match_all($preg_template, mb_strtoupper($r["NAME"]), $arMatches, PREG_OFFSET_CAPTURE))
			{
				$c = count($arMatches[2]);
				for ($j = $c - 1; $j >= 0; $j--)
				{
					$prefix = substr($r["NAME"], 0, $arMatches[2][$j][1]);
					$instr = substr($r["NAME"], $arMatches[2][$j][1], strlen($arMatches[2][$j][0]));
					$suffix = substr($r["NAME"], $arMatches[2][$j][1] + strlen($arMatches[2][$j][0]), strlen($r["NAME"]));
					$r["NAME"] = $prefix."<b>".$instr."</b>".$suffix;
				}
			}
		}

		return $r;
	}

	public static function MakeFilterUrl($prefix, $arFilter)
	{
		if (!is_array($arFilter))
		{
			return "&".urlencode($prefix)."=".urlencode($arFilter);
		}
		else
		{
			$url = "";
			foreach ($arFilter as $key => $value)
			{
				$url .= CSearchTitle::MakeFilterUrl($prefix."[".$key."]", $value);
			}
			return $url;
		}
	}

	function searchTitle($phrase = "", $nTopCount = 5, $arParams = array(), $bNotFilter = false, $order = "")
	{
		return false;
	}
}
