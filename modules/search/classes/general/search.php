<?php
IncludeModuleLangFile(__FILE__);

if (!defined("START_EXEC_TIME"))
	define("START_EXEC_TIME", microtime(true));

class CAllSearch extends CDBResult
{
	var $Query; //Query parset
	var $Statistic; //Search statistic
	var $strQueryText = false; //q
	var $strTagsText = false; //tags
	var $strSqlWhere = ""; //additional sql filter
	var $strTags = ""; //string of tags in double quotes separated by commas
	var $errorno = 0;
	var $error = false;
	var $arParams = array();
	var $url_add_params = array(); //additional url params (OnSearch event)
	var $tf_hwm = 0;
	var $tf_hwm_site_id = "";
	var $_opt_ERROR_ON_EMPTY_STEM = false;
	var $_opt_NO_WORD_LOGIC = false;
	var $offset = false;
	var $limit = false;
	var $bUseRatingSort = false;
	var $flagsUseRatingSort = 0;
	/** @var CSearchFormatter */
	var $formatter = null;

	function __construct($strQuery = false, $SITE_ID = false, $MODULE_ID = false, $ITEM_ID = false, $PARAM1 = false, $PARAM2 = false, $aSort = array(), $aParamsEx = array(), $bTagsCloud = false)
	{
		$this->limit = (int)COption::GetOptionInt("search", "max_result_size");
		if ($this->limit < 1)
			$this->limit = 500;

		$this->CSearch($strQuery, $SITE_ID, $MODULE_ID, $ITEM_ID, $PARAM1, $PARAM2, $aSort, $aParamsEx, $bTagsCloud);
	}

	function CSearch($strQuery = false, $LID = false, $MODULE_ID = false, $ITEM_ID = false, $PARAM1 = false, $PARAM2 = false, $aSort = array(), $aParamsEx = array(), $bTagsCloud = false)
	{
		if ($strQuery === false)
			return $this;

		$arParams["QUERY"] = $strQuery;
		$arParams["SITE_ID"] = $LID;
		$arParams["MODULE_ID"] = $MODULE_ID;
		$arParams["ITEM_ID"] = $ITEM_ID;
		$arParams["PARAM1"] = $PARAM1;
		$arParams["PARAM2"] = $PARAM2;

		$this->Search($arParams, $aSort, $aParamsEx, $bTagsCloud);
	}

	//combination ($MODULE_ID, $PARAM1, $PARAM2, $PARAM3) is used to narrow search
	//returns recordset with search results
	function Search($arParams, $aSort = array(), $aParamsEx = array(), $bTagsCloud = false)
	{
		$DB = CDatabase::GetModuleConnection('search');

		if (!is_array($arParams))
			$arParams = array("QUERY" => $arParams);

		if (!is_set($arParams, "SITE_ID") && is_set($arParams, "LID"))
		{
			$arParams["SITE_ID"] = $arParams["LID"];
			unset($arParams["LID"]);
		}

		if (array_key_exists("TAGS", $arParams))
		{
			$this->strTagsText = $arParams["TAGS"];
			$arTags = explode(",", $arParams["TAGS"]);
			foreach ($arTags as $i => $strTag)
			{
				$strTag = trim($strTag);
				if($strTag <> '')
				{
					$arTags[$i] = str_replace("\"", "\\\"", $strTag);
				}
				else
				{
					unset($arTags[$i]);
				}
			}

			if (count($arTags))
				$arParams["TAGS"] = '"'.implode('","', $arTags).'"';
			else
				unset($arParams["TAGS"]);
		}

		$this->strQueryText = $strQuery = trim($arParams["QUERY"]);
		$this->strTags = $strTags = $arParams["TAGS"] ?? '';

		if (($strQuery == '') && ($strTags <> ''))
		{
			$strQuery = $strTags;
			$bTagsSearch = true;
		}
		else
		{
			if($strTags <> '')
			{
				$strQuery .= " ".$strTags;
			}
			$strQuery = preg_replace_callback("/&#(\\d+);/", array($this, "chr"), $strQuery);
			$bTagsSearch = false;
		}

		if (!array_key_exists("STEMMING", $aParamsEx))
			$aParamsEx["STEMMING"] = COption::GetOptionString("search", "use_stemming") == "Y";
		$this->Query = new CSearchQuery("and", "yes", 0, $arParams["SITE_ID"]);
		if ($this->_opt_NO_WORD_LOGIC)
			$this->Query->no_bool_lang = true;
		$query = $this->Query->GetQueryString((BX_SEARCH_VERSION > 1? "sct": "sc").".SEARCHABLE_CONTENT", $strQuery, $bTagsSearch, $aParamsEx["STEMMING"], $this->_opt_ERROR_ON_EMPTY_STEM);

		$fullTextParams = $aParamsEx;
		if (!isset($fullTextParams["LIMIT"]))
			$fullTextParams["LIMIT"] = $this->limit;
		$fullTextParams["OFFSET"] = $this->offset;
		$fullTextParams["QUERY_OBJECT"] = $this->Query;
		$result = CSearchFullText::getInstance()->search($arParams, $aSort, $fullTextParams, $bTagsCloud);
		if (is_array($result))
		{
			$this->error = CSearchFullText::getInstance()->getErrorText();
			$this->errorno = CSearchFullText::getInstance()->getErrorNumber();
			$this->formatter = CSearchFullText::getInstance()->getRowFormatter();
			if ($this->errorno > 0)
				return;
		}
		else
		{
			if (!$query || trim($query) == '')
			{
				if ($bTagsCloud)
				{
					$query = "1=1";
				}
				else
				{
					$this->error = $this->Query->error;
					$this->errorno = $this->Query->errorno;
					return;
				}
			}

			if (mb_strlen($query) > 2000)
			{
				$this->error = GetMessage("SEARCH_ERROR4");
				$this->errorno = 4;
				return;
			}
		}

		foreach (GetModuleEvents("search", "OnSearch", true) as $arEvent)
		{
			$r = "";
			if ($bTagsSearch)
			{
				if($strTags <> '')
				{
					$r = ExecuteModuleEventEx($arEvent, array("tags:".$strTags));
				}
			}
			else
			{
				$r = ExecuteModuleEventEx($arEvent, array($strQuery));
			}
			if ($r <> "")
				$this->url_add_params[] = $r;
		}

		if (is_array($result))
		{
			$r = new CDBResult;
			$r->InitFromArray($result);
		}
		elseif (
			BX_SEARCH_VERSION > 1
			&& !empty($this->Query->m_stemmed_words_id)
			&& is_array($this->Query->m_stemmed_words_id)
			&& array_sum($this->Query->m_stemmed_words_id) === 0
		)
		{
			$r = new CDBResult;
			$r->InitFromArray(array());
		}
		else
		{
			$this->strSqlWhere = "";
			$bIncSites = false;

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

			if (!array_key_exists("USE_TF_FILTER", $aParamsEx))
				$aParamsEx["USE_TF_FILTER"] = COption::GetOptionString("search", "use_tf_cache") == "Y";

			$bStem = !$bTagsSearch && count($this->Query->m_stemmed_words) > 0;
			//calculate freq of the word on the whole site_id
			if ($bStem && count($this->Query->m_stemmed_words))
			{
				$arStat = $this->GetFreqStatistics($this->Query->m_lang, $this->Query->m_stemmed_words, $arParams["SITE_ID"]);
				$this->tf_hwm_site_id = ($arParams["SITE_ID"] <> ''? $arParams["SITE_ID"]: "");

				//we'll make filter by it's contrast
				if (!$bTagsCloud && $aParamsEx["USE_TF_FILTER"])
				{
					$hwm = false;
					foreach ($this->Query->m_stemmed_words as $i => $stem)
					{
						if (!array_key_exists($stem, $arStat))
						{
							$hwm = 0;
							break;
						}
						elseif ($hwm === false)
						{
							$hwm = $arStat[$stem]["TF"];
						}
						elseif ($hwm > $arStat[$stem]["TF"])
						{
							$hwm = $arStat[$stem]["TF"];
						}
					}

					if ($hwm > 0)
					{
						$arSqlWhere[] = "st.TF >= ".number_format($hwm, 2, ".", "");
						$this->tf_hwm = $hwm;
					}
				}
			}

			if (!empty($arSqlWhere))
			{
				$this->strSqlWhere = "\n\t\t\t\tAND (\n\t\t\t\t\t(".implode(")\n\t\t\t\t\tAND(", $arSqlWhere).")\n\t\t\t\t)";
			}

			if ($bTagsCloud)
				$strSql = $this->tagsMakeSQL($query, $this->strSqlWhere, $strSqlOrder, $bIncSites, $bStem, $aParamsEx["LIMIT"]);
			else
				$strSql = $this->MakeSQL($query, $this->strSqlWhere, $strSqlOrder, $bIncSites, $bStem);

			$r = $DB->Query($strSql);
		}
		parent::__construct($r);
	}

	function SetOptions($arOptions)
	{
		if (array_key_exists("ERROR_ON_EMPTY_STEM", $arOptions))
			$this->_opt_ERROR_ON_EMPTY_STEM = $arOptions["ERROR_ON_EMPTY_STEM"] === true;

		if (array_key_exists("NO_WORD_LOGIC", $arOptions))
			$this->_opt_NO_WORD_LOGIC = $arOptions["NO_WORD_LOGIC"] === true;
	}

	function SetOffset($offset)
	{
		$this->offset = (int)$offset;
	}

	function SetLimit($limit)
	{
		$this->limit = (int)$limit;
	}

	function GetFilterMD5()
	{
		$perm = CSearch::CheckPermissions("sc.ID");
		$sql = preg_replace("/(DATE_FROM|DATE_TO|DATE_CHANGE)(\\s+IS\\s+NOT\\s+NULL|\\s+IS\\s+NULL|\\s*[<>!=]+\\s*'.*?')/im", "", $this->strSqlWhere);
		return md5($perm.$sql.$this->strTags);
	}

	public static function chr($a)
	{
		return chr($a[1]);
	}

	function GetFreqStatistics($lang_id, $arStem, $site_id = "")
	{
		$DB = CDatabase::GetModuleConnection('search');
		$sql_site_id = $DB->ForSQL($site_id);
		$sql_lang_id = $DB->ForSQL($lang_id);
		$sql_stem = array();
		foreach ($arStem as $stem)
			$sql_stem[] = $DB->ForSQL($stem);

		$limit = COption::GetOptionInt("search", "max_result_size");
		if ($limit < 1)
			$limit = 500;

		$arResult = array();
		foreach ($arStem as $stem)
			$arResult[$stem] = array(
				"STEM" => false,
				"FREQ" => 0,
				"TF" => 0,
				"STEM_COUNT" => 0,
				"TF_SUM" => 0,
			);

		if (BX_SEARCH_VERSION > 1)
			$strSql = "
				SELECT s.ID, s.STEM, FREQ, TF
				FROM b_search_content_freq f
				inner join b_search_stem s on s.ID = f.STEM
				WHERE LANGUAGE_ID = '".$sql_lang_id."'
				AND s.STEM in ('".implode("','", $sql_stem)."')
				AND ".($site_id <> ''? "SITE_ID = '".$sql_site_id."'": "SITE_ID IS NULL")."
				ORDER BY STEM
			";
		else
			$strSql = "
				SELECT STEM ID,STEM, FREQ, TF
				FROM b_search_content_freq
				WHERE LANGUAGE_ID = '".$sql_lang_id."'
				AND STEM in ('".implode("','", $sql_stem)."')
				AND ".($site_id <> ''? "SITE_ID = '".$sql_site_id."'": "SITE_ID IS NULL")."
				ORDER BY STEM
			";

		$rs = $DB->Query($strSql);
		while ($ar = $rs->Fetch())
		{
			if ($ar["TF"] <> '')
				$arResult[$ar["STEM"]] = $ar;
		}

		$arMissed = array();
		foreach ($arResult as $stem => $ar)
			if (!$ar["STEM"])
				$arMissed[] = $DB->ForSQL($stem);

		if (count($arMissed) > 0)
		{
			if (BX_SEARCH_VERSION > 1)
				$strSql = "
					SELECT s.ID, s.STEM, floor(st.TF/100) BUCKET, sum(st.TF/10000) TF_SUM, count(*) STEM_COUNT
					FROM
						b_search_content_stem st
						inner join b_search_stem s on s.ID = st.STEM
						".($site_id <> ''? "INNER JOIN b_search_content_site scsite ON scsite.SEARCH_CONTENT_ID = st.SEARCH_CONTENT_ID AND scsite.SITE_ID = '".$sql_site_id."'": "")."
					WHERE st.LANGUAGE_ID = '".$sql_lang_id."'
					AND s.STEM in ('".implode("','", $arMissed)."')
					GROUP BY s.ID, s.STEM, floor(st.TF/100)
					ORDER BY s.ID, s.STEM, floor(st.TF/100) DESC
				";
			else
				$strSql = "
					SELECT st.STEM ID, st.STEM, floor(st.TF*100) BUCKET, sum(st.TF) TF_SUM, count(*) STEM_COUNT
					FROM
						b_search_content_stem st
						".($site_id <> ''? "INNER JOIN b_search_content_site scsite ON scsite.SEARCH_CONTENT_ID = st.SEARCH_CONTENT_ID AND scsite.SITE_ID = '".$sql_site_id."'": "")."
					WHERE st.LANGUAGE_ID = '".$sql_lang_id."'
					AND st.STEM in ('".implode("','", $arMissed)."')
					GROUP BY st.STEM, floor(st.TF*100)
					ORDER BY st.STEM, floor(st.TF*100) DESC
				";


			$rs = $DB->Query($strSql);
			while ($ar = $rs->Fetch())
			{
				$stem = $ar["STEM"];
				if ($arResult[$stem]["STEM_COUNT"] < $limit)
					$arResult[$stem]["TF"] = $ar["BUCKET"] / 100.0;
				$arResult[$stem]["STEM_COUNT"] += $ar["STEM_COUNT"];
				$arResult[$stem]["TF_SUM"] += $ar["TF_SUM"];
				$arResult[$stem]["DO_INSERT"] = true;
				$arResult[$stem]["ID"] = $ar["ID"];
			}
		}

		foreach ($arResult as $stem => $ar)
		{
			if (isset($ar["DO_INSERT"]) && $ar["DO_INSERT"])
			{
				$FREQ = intval(defined("search_range_by_sum_tf")? $ar["TF_SUM"]: $ar["STEM_COUNT"]);
				$strSql = "
					UPDATE b_search_content_freq
					SET FREQ=".$FREQ.", TF=".number_format($ar["TF"], 2, ".", "")."
					WHERE LANGUAGE_ID='".$sql_lang_id."'
					AND ".($site_id <> ''? "SITE_ID = '".$sql_site_id."'": "SITE_ID IS NULL")."
					AND STEM='".$DB->ForSQL($ar["ID"])."'
				";
				$rsUpdate = $DB->Query($strSql);
				if ($rsUpdate->AffectedRowsCount() <= 0)
				{
					$strSql = "
						INSERT INTO b_search_content_freq
						(STEM, LANGUAGE_ID, SITE_ID, FREQ, TF)
						VALUES
						('".$DB->ForSQL($ar["ID"])."', '".$sql_lang_id."', ".($site_id <> ''? "'".$sql_site_id."'": "NULL").", ".$FREQ.", ".number_format($ar["TF"], 2, ".", "").")
					";
					$rsInsert = $DB->Query($strSql, true);
				}
			}
		}

		return $arResult;
	}

	function Repl($strCond, $strType, $strWh)
	{
		$l = mb_strlen($strCond);

		if ($this->Query->bStemming)
		{
			$arStemInfo = stemming_init($this->Query->m_lang);
			$pcreLettersClass = "[".$arStemInfo["pcre_letters"]."]";
			$strWhUpp = stemming_upper($strWh, $this->Query->m_lang);
		}
		else
		{
			$strWhUpp = mb_strtoupper($strWh);
		}

		$strCondUpp = mb_strtoupper($strCond);

		$pos = 0;
		do
		{
			$pos = mb_strpos($strWhUpp, $strCondUpp, $pos);

			//Check if we are in the middle of the numeric entity
			while (
				$pos !== false &&
				preg_match("/^[0-9]+;/", mb_substr($strWh, $pos)) &&
				preg_match("/^[0-9]+#&/", strrev(mb_substr($strWh, 0, $pos + mb_strlen($strCond))))
			)
			{
				$pos = mb_strpos($strWhUpp, $strCondUpp, $pos + 1);
			}

			if ($pos === false) break;

			if ($strType == "STEM")
			{
				$lw = mb_strlen($strWhUpp);
				for ($s = $pos; $s >= 0; $s--)
				{
					if (!preg_match("/$pcreLettersClass/u", mb_substr($strWhUpp, $s, 1)))
						break;
				}
				$s++;
				for ($e = $pos; $e < $lw; $e++)
				{
					if (!preg_match("/$pcreLettersClass/u", mb_substr($strWhUpp, $e, 1)))
						break;
				}
				$e--;
				$a = stemming(mb_substr($strWhUpp, $s, $e - $s + 1), $this->Query->m_lang, true);
				foreach ($a as $stem => $cnt)
				{
					if ($stem == $strCondUpp)
					{
						$strWh = mb_substr($strWh, 0, $pos)."%^%".mb_substr($strWh, $pos, $e - $pos + 1)."%/^%".mb_substr($strWh, $e + 1);
						$strWhUpp = mb_substr($strWhUpp, 0, $pos)."%^%".str_repeat(" ", $e - $pos + 1)."%/^%".mb_substr($strWhUpp, $e + 1);
						$pos += 7 + $e - $pos + 1;
					}
				}
			}
			else
			{
				$strWh = mb_substr($strWh, 0, $pos)."%^%".mb_substr($strWh, $pos, $l)."%/^%".mb_substr($strWh, $pos + $l);
				$strWhUpp = mb_substr($strWhUpp, 0, $pos)."%^%".str_repeat(" ", $l)."%/^%".mb_substr($strWhUpp, $pos + $l);
				$pos += 7 + $l;
			}
			$pos += 1;
		} while ($pos < mb_strlen($strWhUpp));

		return $strWh;
	}

	function PrepareSearchResult($str)
	{
		//$words - contains what we will highlight
		$words = array();
		foreach ($this->Query->m_words as $v)
		{
			$v = mb_strtoupper($v);
			$words[$v] = "KAV";
			if (mb_strpos($v, "\"") !== false)
				$words[str_replace("\"", "&QUOT;", $v)] = "KAV";
		}

		foreach ($this->Query->m_stemmed_words as $v)
			$words[mb_strtoupper($v)] = "STEM";

		//Prepare upper case version of the string
		if ($this->Query->bStemming)
		{
			//And add missing stemming words
			$arStemInfo = stemming_init($this->Query->m_lang);
			$a = stemming($this->Query->m_query, $this->Query->m_lang, true);
			foreach ($a as $stem => $cnt)
			{
				if (!preg_match("/cut[56]/i", $stem))
					$words[$stem] = "STEM";
			}
			$pcreLettersClass = "[".$arStemInfo["pcre_letters"]."]";
			$strUpp = stemming_upper($str, $this->Query->m_lang);
		}
		else
		{
			$strUpp = mb_strtoupper($str);
			$pcreLettersClass = "";
		}

		$wordsCount = count($words);

		//We'll use regexp to find positions of the words in the text
		$pregMask = "";
		foreach ($words as $search => $type)
		{
			if ($type == "STEM")
				$pregMask = "(?<!".$pcreLettersClass.")".preg_quote($search, "/").$pcreLettersClass."*|".$pregMask;
			else
				$pregMask = $pregMask."|".preg_quote($search, "/");
		}
		$pregMask = trim($pregMask, "|");

		$arPos = array(); //This will contain positions of the first occurrence
		$arPosW = array(); //This is "running" words array
		$arPosP = array(); //and their positions
		$arPosLast = false; //Best found combination of the positions
		$matches = array();
		if (preg_match_all("/(".$pregMask.")/iu", $strUpp, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE))
		{
			foreach ($matches as $oneCase)
			{
				$search = null;
				if (isset($words[$oneCase[0][0]]))
				{
					$search = $oneCase[0][0];
				}
				else
				{
					$a = stemming($oneCase[0][0], $this->Query->m_lang, true);
					foreach ($a as $stem => $cnt)
					{
						if (isset($words[$stem]))
						{
							$search = $stem;
							break;
						}
					}
				}

				if (isset($search))
				{
					$p = $oneCase[0][1];
					if (!isset($arPos[$search]))
						$arPos[$search] = $p;
					//Add to the tail of the running window
					$arPosP[] = $p;
					$arPosW[] = $search;
					$cc = count($arPosW);
					if ($cc >= $wordsCount)
					{
						//This cuts the tail of the running window
						while ($cc > $wordsCount)
						{
							array_shift($arPosW);
							array_shift($arPosP);
							$cc--;
						}
						//Check if all the words present in the current window
						if (count(array_unique($arPosW)) == $wordsCount)
						{
							//And check if positions is the best
							if (
								!$arPosLast
								|| (
									(max($arPosP) - min($arPosP)) < (max($arPosLast) - min($arPosLast))
								)
							)
								$arPosLast = $arPosP;
						}
					}
				}
			}
		}

		if ($arPosLast)
			$arPos = $arPosLast;

		//Nothing found just cut some text
		if (empty($arPos))
		{
			$str_len = mb_strlen($str);
			$pos_end = 500;
			while (($pos_end < $str_len) && (mb_strpos(" ,.\n\r", mb_substr($str, $pos_end, 1)) === false))
				$pos_end++;
			return mb_substr($str, 0, $pos_end).($pos_end < $str_len? "..." : "");
		}

		sort($arPos);

		$str_len = strlen($str);
		$delta = 250 / count($arPos);
		$arOtr = array();
		//Have to do it two times because Positions eat each other
		for ($i = 0; $i < 2; $i++)
		{
			$arOtr = array();
			$last_pos = -1;
			foreach ($arPos as $pos_mid)
			{
				//Find where sentence begins
				$pos_beg = $pos_mid - $delta;
				if ($pos_beg <= 0)
					$pos_beg = 0;
				while (($pos_beg > 0) && (mb_strpos(" ,.!?\n\r", substr($str, $pos_beg, 1)) === false))
					$pos_beg--;

				//Find where sentence ends
				$pos_end = $pos_mid + $delta;
				if ($pos_end > $str_len)
					$pos_end = $str_len;
				while (($pos_end < $str_len) && (mb_strpos(" ,.!?\n\r", substr($str, $pos_end, 1)) === false))
					$pos_end++;

				if ($pos_beg <= $last_pos)
					$arOtr[count($arOtr) - 1][1] = $pos_end;
				else
					$arOtr[] = array($pos_beg, $pos_end);

				$last_pos = $pos_end;
			}
			//Adjust length of the text
			$delta = 250 / count($arOtr);
		}

		$str_result = "";
		foreach ($arOtr as $borders)
		{
			$str_result .= ($borders[0] <= 0? "": " ...")
				.substr($str, $borders[0], $borders[1] - $borders[0] + 1)
				.($borders[1] >= $str_len? "": "... ");
		}

		foreach ($words as $search => $type)
		{
			$str_result = $this->Repl($search, $type, $str_result);
		}

		$str_result = str_replace("%/^%", "</b>", str_replace("%^%", "<b>", $str_result));

		return $str_result;
	}

	function NavStart($nPageSize = 0, $bShowAll = true, $iNumPage = false)
	{
		parent::NavStart($nPageSize, $bShowAll, $iNumPage);
		if (COption::GetOptionString("search", "stat_phrase") == "Y")
		{
			$this->Statistic = new CSearchStatistic($this->strQueryText, $this->strTagsText);
			$this->Statistic->PhraseStat($this->NavRecordCount, $this->NavPageNomer);
			if ($this->Statistic->phrase_id)
				$this->url_add_params[] = "sphrase_id=".$this->Statistic->phrase_id;
		}
	}

	function Fetch()
	{
		static $arSite = array();
		$DB = CDatabase::GetModuleConnection('search');

		$r = parent::Fetch();

		if ($r && $this->formatter)
		{
			$r = $this->formatter->format($r);
			if (!$r)
				return $this->Fetch();
		}

		if ($r)
		{
			$site_id = $r["SITE_ID"] ?? null;
			if (!isset($arSite[$site_id]))
			{
				$rsSite = CSite::GetList('', '', array("ID" => $site_id));
				$arSite[$site_id] = $rsSite->Fetch();
			}
			$r["DIR"] = $arSite[$site_id]["DIR"];
			$r["SERVER_NAME"] = $arSite[$site_id]["SERVER_NAME"];

			if (!empty($r["SITE_URL"]))
				$r["URL"] = $r["SITE_URL"];

			if (isset($r["URL"]) && mb_substr($r["URL"], 0, 1) == "=")
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
				($r["URL"] ?? '')
			);
			$r["URL"] = preg_replace("'(?<!:)/+'s", "/", $r["URL"]);
			$r["URL_WO_PARAMS"] = $r["URL"];

			$w = $this->Query->m_words;
			if (count($this->url_add_params))
			{
				$p1 = mb_strpos($r["URL"], "?");
				if ($p1 === false)
					$ch = "?";
				else
					$ch = "&";

				$p2 = mb_strpos($r["URL"], "#", $p1);
				if ($p2 === false)
				{
					$r["URL"] = $r["URL"].$ch.implode("&", $this->url_add_params);
				}
				else
				{
					$r["URL"] = mb_substr($r["URL"], 0, $p2).$ch.implode("&", $this->url_add_params).mb_substr($r["URL"], $p2);
				}
			}

			if (!array_key_exists("TITLE_FORMATED", $r) && array_key_exists("TITLE", $r))
			{
				$r["TITLE_FORMATED"] = $this->PrepareSearchResult(htmlspecialcharsEx($r["TITLE"]));
				$r["TITLE_FORMATED_TYPE"] = "html";
				$r["TAGS_FORMATED"] = tags_prepare($r["TAGS"], SITE_ID);
				if (!empty($r["BODY"]))
				{
					$r["BODY_FORMATED"] = $this->PrepareSearchResult(htmlspecialcharsEx($r["BODY"]));
					$r["BODY_FORMATED_TYPE"] = "html";
				}
				else
				{
					$max_body_size = COption::GetOptionInt("search", "max_body_size");
					$sqlBody = $max_body_size > 0? "left(BODY,".$max_body_size.") as BODY": "BODY";
					$rsBody = $DB->Query("select $sqlBody from b_search_content where ID=".$r["ID"]);
					if ($arBody = $rsBody->Fetch())
					{
						$r["BODY_FORMATED"] = $this->PrepareSearchResult(htmlspecialcharsEx($arBody["BODY"]));
						$r["BODY_FORMATED_TYPE"] = "html";
					}
				}
			}
		}

		return $r;
	}

	public static function CheckPath($path)
	{
		static $SEARCH_MASKS_CACHE = false;

		if (!is_array($SEARCH_MASKS_CACHE))
		{
			$arSearch = array("\\", ".", "?", "*", "'");
			$arReplace = array("/", "\\.", ".", ".*?", "\\'");

			$arInc = array();
			$inc = str_replace(
				$arSearch,
				$arReplace,
				COption::GetOptionString("search", "include_mask")
			);
			$arIncTmp = explode(";", $inc);
			foreach ($arIncTmp as $mask)
			{
				$mask = trim($mask);
				if($mask <> '')
				{
					$arInc[] = "'^".$mask."$'";
				}
			}

			$arFullExc = array();
			$arExc = array();
			$exc = str_replace(
				$arSearch,
				$arReplace,
				COption::GetOptionString("search", "exclude_mask")
			);
			$arExcTmp = explode(";", $exc);
			foreach ($arExcTmp as $mask)
			{
				$mask = trim($mask);
				if($mask <> '')
				{
					if(preg_match("#^/[a-z0-9_.\\\\]+/#i", $mask))
					{
						$arFullExc[] = "'^".$mask."$'u";
					}
					else
					{
						$arExc[] = "'^".$mask."$'u";
					}
				}
			}

			$SEARCH_MASKS_CACHE = Array(
				"full_exc" => $arFullExc,
				"exc" => $arExc,
				"inc" => $arInc
			);
		}

		$file = end(explode('/', $path)); //basename
		if (strncmp($file, ".", 1) == 0)
			return 0;

		foreach ($SEARCH_MASKS_CACHE["full_exc"] as $mask)
			if (preg_match($mask, $path))
				return false;

		foreach ($SEARCH_MASKS_CACHE["exc"] as $mask)
			if (preg_match($mask, $path))
				return 0;

		foreach ($SEARCH_MASKS_CACHE["inc"] as $mask)
			if (preg_match($mask, $path))
				return true;

		return 0;
	}

	public static function GetGroupCached()
	{
		static $SEARCH_CACHED_GROUPS = false;

		if (!is_array($SEARCH_CACHED_GROUPS))
		{
			$SEARCH_CACHED_GROUPS = Array();
			$db_groups = CGroup::GetList('id', 'asc');
			while ($g = $db_groups->Fetch())
			{
				$group_id = intval($g["ID"]);
				if ($group_id > 1)
					$SEARCH_CACHED_GROUPS[$group_id] = $group_id;
			}
		}

		return $SEARCH_CACHED_GROUPS;
	}

	public static function QueryMnogoSearch(&$xml)
	{
		$SITE = COption::GetOptionString("search", "mnogosearch_url", "www.mnogosearch.org");
		$PATH = COption::GetOptionString("search", "mnogosearch_path", "");
		$PORT = COption::GetOptionString("search", "mnogosearch_port", "80");

		$QUERY_STR = 'document='.urlencode($xml);

		$strRequest = "POST ".$PATH." HTTP/1.0\r\n";
		$strRequest .= "User-Agent: BitrixSM\r\n";
		$strRequest .= "Accept: */*\r\n";
		$strRequest .= "Host: $SITE\r\n";
		$strRequest .= "Accept-Language: en\r\n";
		$strRequest .= "Content-type: application/x-www-form-urlencoded\r\n";
		$strRequest .= "Content-length: ".mb_strlen($QUERY_STR)."\r\n";
		$strRequest .= "\r\n";
		$strRequest .= $QUERY_STR;
		$strRequest .= "\r\n";

		$arAll = "";
		$errno = 0;
		$errstr = "";

		$FP = fsockopen($SITE, $PORT, $errno, $errstr, 120);
		if ($FP)
		{
			fputs($FP, $strRequest);

			while (($line = fgets($FP, 4096)) && $line != "\r\n") ;
			while ($line = fread($FP, 4096))
				$arAll .= $line;
			fclose($FP);
		}

		return $arAll;
	}

	//////////////////////////////////
	//reindex the whole server content
	//$bFull = true - no not check change_date. all index tables will be truncated
	//       = false - add new ones. update changed and delete deleted.
	public static function ReIndexAll($bFull = false, $max_execution_time = 0, $NS = Array(), $clear_suggest = false)
	{
		global $APPLICATION;
		$DB = CDatabase::GetModuleConnection('search');

		@set_time_limit(0);
		if (!is_array($NS))
			$NS = Array();
		if ($max_execution_time <= 0)
		{
			$NS_OLD = $NS;
			$NS = Array("CLEAR" => "N", "MODULE" => "", "ID" => "", "SESS_ID" => md5(uniqid("")));
			if ($NS_OLD["SITE_ID"] != "") $NS["SITE_ID"] = $NS_OLD["SITE_ID"];
			if ($NS_OLD["MODULE_ID"] != "") $NS["MODULE_ID"] = $NS_OLD["MODULE_ID"];
		}
		$NS["CNT"] = intval($NS["CNT"]);
		if (!$bFull && mb_strlen($NS["SESS_ID"]) != 32)
			$NS["SESS_ID"] = md5(uniqid(""));

		$p1 = microtime(true);

		$DB->StartTransaction();
		CSearch::ReindexLock();

		if ($NS["CLEAR"] != "Y")
		{
			if ($bFull)
			{
				foreach (GetModuleEvents("search", "OnBeforeFullReindexClear", true) as $arEvent)
					ExecuteModuleEventEx($arEvent);

				CSearchTags::CleanCache();
				$DB->Query("TRUNCATE TABLE b_search_content_param");
				$DB->Query("TRUNCATE TABLE b_search_content_site");
				$DB->Query("TRUNCATE TABLE b_search_content_right");
				$DB->Query("TRUNCATE TABLE b_search_content_title");
				$DB->Query("TRUNCATE TABLE b_search_tags");
				$DB->Query("TRUNCATE TABLE b_search_content_freq");
				$DB->Query("TRUNCATE TABLE b_search_content");
				$DB->Query("TRUNCATE TABLE b_search_suggest");
				$DB->Query("TRUNCATE TABLE b_search_user_right");
				CSearchFullText::getInstance()->truncate();
				COption::SetOptionString("search", "full_reindex_required", "N");
			}
			elseif ($clear_suggest)
			{
				$DB->Query("TRUNCATE TABLE b_search_suggest");
				$DB->Query("TRUNCATE TABLE b_search_user_right");
				$DB->Query("TRUNCATE TABLE b_search_content_freq");
			}
		}


		$NS["CLEAR"] = "Y";

		clearstatcache();

		if (
			($NS["MODULE"] == "" || $NS["MODULE"] == "main") &&
			($NS["MODULE_ID"] == "" || $NS["MODULE_ID"] == "main")
		)
		{
			$arLangDirs = Array();
			$arFilter = Array("ACTIVE" => "Y");
			if ($NS["SITE_ID"] != "")
				$arFilter["ID"] = $NS["SITE_ID"];
			$r = CSite::GetList('', '', $arFilter);
			while ($arR = $r->Fetch())
			{
				$path = rtrim($arR["DIR"], "/");
				$arLangDirs[$arR["ABS_DOC_ROOT"]."/".$path."/"] = $arR;
			}

			//get rid of duplicates
			$dub = Array();
			foreach ($arLangDirs as $path => $arR)
			{
				foreach ($arLangDirs as $path2 => $arR2)
				{
					if ($path == $path2) continue;
					if (mb_substr($path, 0, mb_strlen($path2)) == $path2)
						$dub[] = $path;
				}
			}

			foreach ($dub as $p)
				unset($arLangDirs[$p]);

			foreach ($arLangDirs as $arR)
			{
				$site = $arR["ID"];
				$path = rtrim($arR["DIR"], "/");
				$site_path = $site."|".$path."/";

				if (
					$max_execution_time > 0
					&& $NS["MODULE"] == "main"
					&& mb_substr($NS["ID"]."/", 0, mb_strlen($site_path)) != $site_path
				)
					continue;

				//for every folder
				CSearch::RecurseIndex(Array($site, $path), $max_execution_time, $NS);
				if (
					$max_execution_time > 0
					&& $NS["MODULE"] <> ''
				)
				{
					$DB->Commit();
					return $NS;
				}
			}
		}

		$p1 = microtime(true);

		//for every who wants to reindex
		$oCallBack = new CSearchCallback;
		$oCallBack->max_execution_time = $max_execution_time;
		foreach (GetModuleEvents("search", "OnReindex", true) as $arEvent)
		{
			if ($NS["MODULE_ID"] != "" && $NS["MODULE_ID"] != $arEvent["TO_MODULE_ID"]) continue;
			if ($max_execution_time > 0 && $NS["MODULE"] <> '' && $NS["MODULE"] != "main" && $NS["MODULE"] != $arEvent["TO_MODULE_ID"]) continue;
			//here we get recordset
			$oCallBack->MODULE = $arEvent["TO_MODULE_ID"];
			$oCallBack->CNT = &$NS["CNT"];
			$oCallBack->SESS_ID = $NS["SESS_ID"];
			$r = &$oCallBack;
			$arResult = ExecuteModuleEventEx($arEvent, array($NS, $r, "Index"));
			if (is_array($arResult)) //old way
			{
				foreach ($arResult as $arFields)
				{
					$ID = $arFields["ID"];
					if ($ID <> '')
					{
						unset($arFields["ID"]);
						$NS["CNT"]++;
						CSearch::Index($arEvent["TO_MODULE_ID"], $ID, $arFields, false, $NS["SESS_ID"]);
					}
				}
			}
			else  //new method
			{
				if ($max_execution_time > 0 && $arResult !== false && mb_strlen(".".$arResult) > 1)
				{
					$DB->Commit();
					return Array(
						"MODULE" => $arEvent["TO_MODULE_ID"],
						"CNT" => $oCallBack->CNT,
						"ID" => $arResult,
						"CLEAR" => $NS["CLEAR"],
						"SESS_ID" => $NS["SESS_ID"],
						"SITE_ID" => $NS["SITE_ID"],
						"MODULE_ID" => $NS["MODULE_ID"],
					);
				}
			}
			$NS["MODULE"] = "";
		}

		if (!$bFull)
		{
			CSearch::DeleteOld($NS["SESS_ID"], $NS["MODULE_ID"], $NS["SITE_ID"]);
		}

		$DB->Commit();

		return $NS["CNT"];
	}

	public static function ReindexModule($MODULE_ID, $bFull = false)
	{
		if ($bFull)
		{
			CSearch::DeleteForReindex($MODULE_ID);
		}

		$NS = Array("CLEAR" => "N", "MODULE" => "", "ID" => "", "SESS_ID" => md5(uniqid("")));
		//for every who wants to be reindexed
		foreach (GetModuleEvents("search", "OnReindex", true) as $arEvent)
		{
			if ($arEvent["TO_MODULE_ID"] != $MODULE_ID) continue;

			$oCallBack = new CSearchCallback;
			$oCallBack->MODULE = $arEvent["TO_MODULE_ID"];
			$oCallBack->CNT = &$NS["CNT"];
			$oCallBack->SESS_ID = $NS["SESS_ID"];
			$r = &$oCallBack;

			$arResult = ExecuteModuleEventEx($arEvent, array($NS, $r, "Index"));
			if (is_array($arResult)) //old way
			{
				foreach ($arResult as $arFields)
				{
					$ID = $arFields["ID"];
					if ($ID <> '')
					{
						unset($arFields["ID"]);
						$NS["CNT"]++;
						CSearch::Index($arEvent["TO_MODULE_ID"], $ID, $arFields, false, $NS["SESS_ID"]);
					}
				}
			}
			else  //new way
			{
				return Array("MODULE" => $arEvent["TO_MODULE_ID"], "CNT" => $oCallBack->CNT, "ID" => $arResult, "CLEAR" => $NS["CLEAR"], "SESS_ID" => $NS["SESS_ID"]);
			}
		}

		if (!$bFull)
		{
			CSearch::DeleteOld($NS["SESS_ID"], $MODULE_ID, $NS["SITE_ID"]);
		}
	}

	public static function GetIndex($MODULE_ID, $ITEM_ID)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$rs = $DB->Query("select * from b_search_content where MODULE_ID = '".$DB->ForSql($MODULE_ID)."' and ITEM_ID = '".$DB->ForSql($ITEM_ID)."'");
		$arFields = $rs->Fetch();
		if (!$arFields)
		{
			return false;
		}

		$arFields["SITE_ID"] = array();
		$rs = $DB->Query("select * from b_search_content_site where SEARCH_CONTENT_ID = ".$DB->ForSql($arFields["ID"]));
		while ($ar = $rs->Fetch())
		{
			$arFields["SITE_ID"][$ar["SITE_ID"]] = $ar["URL"];
		}

		$arFields["PERMISSIONS"] = array();
		$rs = $DB->Query("select * from b_search_content_right where SEARCH_CONTENT_ID = ".$DB->ForSql($arFields["ID"]));
		while ($ar = $rs->Fetch())
		{
			$arFields["PERMISSIONS"][] = $ar["GROUP_CODE"];
		}

		$arFields["PARAMS"] = array();
		$rs = $DB->Query("select * from b_search_content_param where SEARCH_CONTENT_ID = ".$DB->ForSql($arFields["ID"]));
		while ($ar = $rs->Fetch())
		{
			$arFields["PARAMS"][$ar["PARAM_NAME"]][] = $ar["PARAM_VALUE"];
		}

		return $arFields;
	}

	//index one item (forum message, news, etc.)
	//combination of ($MODULE_ID, $ITEM_ID) is used to determine the documents
	public static function Index($MODULE_ID, $ITEM_ID, $arFields, $bOverWrite = false, $SEARCH_SESS_ID = "")
	{
		$DB = CDatabase::GetModuleConnection('search');

		$arFields["MODULE_ID"] = $MODULE_ID;
		$arFields["ITEM_ID"] = $ITEM_ID;
		foreach (GetModuleEvents("search", "BeforeIndex", true) as $arEvent)
		{
			$arEventResult = ExecuteModuleEventEx($arEvent, array($arFields));
			if (is_array($arEventResult))
				$arFields = $arEventResult;
		}
		unset($arFields["MODULE_ID"]);
		unset($arFields["ITEM_ID"]);

		$bTitle = array_key_exists("TITLE", $arFields);
		if ($bTitle)
			$arFields["TITLE"] = trim($arFields["TITLE"]);
		$bBody = array_key_exists("BODY", $arFields);
		if ($bBody)
			$arFields["BODY"] = trim($arFields["BODY"]);
		$bTags = array_key_exists("TAGS", $arFields);
		if ($bTags)
			$arFields["TAGS"] = trim($arFields["TAGS"]);

		if (!array_key_exists("SITE_ID", $arFields) && array_key_exists("LID", $arFields))
			$arFields["SITE_ID"] = $arFields["LID"];

		if (array_key_exists("SITE_ID", $arFields))
		{
			if (!is_array($arFields["SITE_ID"]))
			{
				$arFields["SITE_ID"] = Array($arFields["SITE_ID"] => "");
			}
			else
			{
				$bNotAssoc = true;
				$i = 0;
				foreach ($arFields["SITE_ID"] as $k => $val)
				{
					if ("".$k != "".$i)
					{
						$bNotAssoc = false;
						break;
					}
					$i++;
				}
				if ($bNotAssoc)
				{
					$x = $arFields["SITE_ID"];
					$arFields["SITE_ID"] = Array();
					foreach ($x as $val)
						$arFields["SITE_ID"][$val] = "";
				}
			}

			if (count($arFields["SITE_ID"]) <= 0)
				return 0;

			reset($arFields["SITE_ID"]);
			$arFields["LID"] = current($arFields["SITE_ID"]);

			$arSites = array();
			foreach ($arFields["SITE_ID"] as $site => $url)
			{
				$arSites[] = $DB->ForSQL($site, 2);
			}

			$strSql = "
				SELECT CR.RANK
				FROM b_search_custom_rank CR
				WHERE CR.SITE_ID in ('".implode("', '", $arSites)."')
				AND CR.MODULE_ID='".$DB->ForSQL($MODULE_ID)."'
				".(is_set($arFields, "PARAM1")? "AND (CR.PARAM1 IS NULL OR CR.PARAM1='' OR CR.PARAM1='".$DB->ForSQL($arFields["PARAM1"])."')": "")."
				".(is_set($arFields, "PARAM2")? "AND (CR.PARAM2 IS NULL OR CR.PARAM2='' OR CR.PARAM2='".$DB->ForSQL($arFields["PARAM2"])."')": "")."
				".($ITEM_ID <> ""? "AND (CR.ITEM_ID IS NULL OR CR.ITEM_ID='' OR CR.ITEM_ID='".$DB->ForSQL($ITEM_ID)."')": "")."
				ORDER BY
					PARAM1 DESC, PARAM2 DESC, ITEM_ID DESC
			";
			$r = $DB->Query($strSql);
			$arFields["CUSTOM_RANK_SQL"] = $strSql;
			if ($arResult = $r->Fetch())
				$arFields["CUSTOM_RANK"] = $arResult["RANK"];
		}

		$arGroups = array();
		if (is_set($arFields, "PERMISSIONS"))
		{
			foreach ($arFields["PERMISSIONS"] as $group_id)
			{
				if (is_numeric($group_id))
					$arGroups[$group_id] = "G".intval($group_id);
				else
					$arGroups[$group_id] = $group_id;
			}
		}

		$strSqlSelect = "";
		if ($bBody) $strSqlSelect .= ",BODY";
		if ($bTitle) $strSqlSelect .= ",TITLE";
		if ($bTags) $strSqlSelect .= ",TAGS";

		$strSql =
			"SELECT ID, MODULE_ID, ITEM_ID, ".$DB->DateToCharFunction("DATE_CHANGE")." as DATE_CHANGE
			".$strSqlSelect."
			FROM b_search_content
			WHERE MODULE_ID = '".$DB->ForSQL($MODULE_ID)."'
				AND ITEM_ID = '".$DB->ForSQL($ITEM_ID)."' ";

		$r = $DB->Query($strSql);

		if ($arResult = $r->Fetch())
		{
			$ID = $arResult["ID"];

			if ($bTitle && $bBody && $arFields["BODY"] == '' && $arFields["TITLE"] == '')
			{
				foreach (GetModuleEvents("search", "OnBeforeIndexDelete", true) as $arEvent)
					ExecuteModuleEventEx($arEvent, array("SEARCH_CONTENT_ID = ".$ID));

				CSearchTags::CleanCache("", $ID);
				CSearch::CleanFreqCache($ID);
				$DB->Query("DELETE FROM b_search_content_param WHERE SEARCH_CONTENT_ID = ".$ID);
				$DB->Query("DELETE FROM b_search_content_right WHERE SEARCH_CONTENT_ID = ".$ID);
				$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$ID);
				$DB->Query("DELETE FROM b_search_content_title WHERE SEARCH_CONTENT_ID = ".$ID);
				$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ID);
				CSearchFullText::getInstance()->deleteById($ID);
				$DB->Query("DELETE FROM b_search_content WHERE ID = ".$ID);

				return 0;
			}

			if (is_set($arFields, "PARAMS"))
				CAllSearch::SetContentItemParams($ID, $arFields["PARAMS"]);

			if (count($arGroups) > 0)
				CAllSearch::SetContentItemGroups($ID, $arGroups);

			if (is_set($arFields, "SITE_ID"))
			{
				CSearch::UpdateSite($ID, $arFields["SITE_ID"]);
			}

			if (array_key_exists("LAST_MODIFIED", $arFields))
				$arFields["~DATE_CHANGE"] = $arFields["DATE_CHANGE"] = $DATE_CHANGE = $arFields["LAST_MODIFIED"];
			elseif (array_key_exists("DATE_CHANGE", $arFields))
				$arFields["~DATE_CHANGE"] = $arFields["DATE_CHANGE"] = $DATE_CHANGE = $DB->FormatDate($arFields["DATE_CHANGE"], "DD.MM.YYYY HH:MI:SS", CLang::GetDateFormat());
			else
				$DATE_CHANGE = '';

			if (!$bOverWrite && $DATE_CHANGE == $arResult["DATE_CHANGE"])
			{
				if ($SEARCH_SESS_ID <> '')
					$DB->Query("UPDATE b_search_content SET UPD='".$DB->ForSql($SEARCH_SESS_ID)."' WHERE ID = ".$ID);
				//$DB->Commit();
				return $ID;
			}

			unset($arFields["MODULE_ID"]);
			unset($arFields["ITEM_ID"]);

			if ($bBody || $bTitle || $bTags)
			{

				if (array_key_exists("INDEX_TITLE", $arFields) && $arFields["INDEX_TITLE"] === false)
				{
					$content = "";
				}
				else
				{
					if ($bTitle)
						$content = $arFields["TITLE"]."\r\n";
					else
						$content = $arResult["TITLE"]."\r\n";
				}

				if ($bBody)
					$content .= $arFields["BODY"]."\r\n";
				else
					$content .= $arResult["BODY"]."\r\n";

				if ($bTags)
					$content .= $arFields["TAGS"];
				else
					$content .= $arResult["TAGS"];

				$content = preg_replace_callback("/&#(\\d+);/", array("CSearch", "chr"), $content);
				$arFields["SEARCHABLE_CONTENT"] = CSearch::KillEntities(mb_strtoupper($content));
			}

			if ($SEARCH_SESS_ID <> '')
				$arFields["UPD"] = $SEARCH_SESS_ID;

			if (array_key_exists("TITLE", $arFields))
			{
				if (
					!array_key_exists("INDEX_TITLE", $arFields)
					|| $arFields["INDEX_TITLE"] !== false
				)
					CSearch::IndexTitle($arFields["SITE_ID"], $ID, $arFields["TITLE"]);
			}

			if ($bTags && ($arResult["TAGS"] != $arFields["TAGS"]))
			{
				CSearchTags::CleanCache("", $ID);
				$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ID);
				CSearch::TagsIndex($arFields["SITE_ID"], $ID, $arFields["TAGS"]);
			}

			foreach (GetModuleEvents("search", "OnBeforeIndexUpdate", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			CSearch::Update($ID, $arFields);
			$arFields["MODULE_ID"] = $arResult['MODULE_ID'];
			$arFields["ITEM_ID"] = $arResult['ITEM_ID'];
			CSearchFullText::getInstance()->replace($ID, $arFields);
		}
		else
		{
			if ($bTitle && $bBody && $arFields["BODY"] == '' && $arFields["TITLE"] == '')
			{
				//$DB->Commit();
				return 0;
			}

			$arFields["MODULE_ID"] = $MODULE_ID;
			$arFields["ITEM_ID"] = $ITEM_ID;

			if (array_key_exists("INDEX_TITLE", $arFields) && $arFields["INDEX_TITLE"] === false)
				$content = $arFields["BODY"]."\r\n".($arFields["TAGS"] ?? '');
			else
				$content = $arFields["TITLE"]."\r\n".$arFields["BODY"]."\r\n".($arFields["TAGS"] ?? '');

			$content = preg_replace_callback("/&#(\\d+);/", array("CSearch", "chr"), $content);
			$arFields["SEARCHABLE_CONTENT"] = CSearch::KillEntities(mb_strtoupper($content));

			if ($SEARCH_SESS_ID != "")
				$arFields["UPD"] = $SEARCH_SESS_ID;

			$ID = CSearch::Add($arFields);
			//We failed to add this record to the search index
			if ($ID === false)
			{
				//Check if item was added
				$strSql = "SELECT ID FROM b_search_content WHERE MODULE_ID = '".$DB->ForSQL($MODULE_ID)."' AND ITEM_ID = '".$DB->ForSQL($ITEM_ID)."' ";
				$rs = $DB->Query($strSql);
				$ar = $rs->Fetch();
				if ($ar)
					return $ar["ID"];
				else
					return $ID;
			}
			CSearchFullText::getInstance()->replace($ID, $arFields);

			foreach (GetModuleEvents("search", "OnAfterIndexAdd", true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			if (is_set($arFields, "PARAMS"))
				CAllSearch::SetContentItemParams($ID, $arFields["PARAMS"]);

			CAllSearch::SetContentItemGroups($ID, $arGroups);

			CSearch::UpdateSite($ID, $arFields["SITE_ID"]);

			if (
				!array_key_exists("INDEX_TITLE", $arFields)
				|| $arFields["INDEX_TITLE"] !== false
			)
				CSearch::IndexTitle($arFields["SITE_ID"], $ID, $arFields["TITLE"]);

			if ($bTags)
			{
				CSearch::TagsIndex($arFields["SITE_ID"], $ID, $arFields["TAGS"]);
			}
		}
		//$DB->Commit();

		return $ID;
	}

	public static function KillEntities($str)
	{
		static $arAllEntities = array(
			'UMLYA' => ARRAY(
				'&IQUEST;', '&AGRAVE;', '&AACUTE;', '&ACIRC;', '&ATILDE;',
				'&AUML;', '&ARING;', '&AELIG;', '&CCEDIL;', '&EGRAVE;',
				'&EACUTE;', '&ECIRC;', '&EUML;', '&IGRAVE;', '&IACUTE;',
				'&ICIRC;', '&IUML;', '&ETH;', '&NTILDE;', '&OGRAVE;',
				'&OACUTE;', '&OCIRC;', '&OTILDE;', '&OUML;', '&TIMES;',
				'&OSLASH;', '&UGRAVE;', '&UACUTE;', '&UCIRC;', '&UUML;',
				'&YACUTE;', '&THORN;', '&SZLIG;', '&AGRAVE;', '&AACUTE;',
				'&ACIRC;', '&ATILDE;', '&AUML;', '&ARING;', '&AELIG;',
				'&CCEDIL;', '&EGRAVE;', '&EACUTE;', '&ECIRC;', '&EUML;',
				'&IGRAVE;', '&IACUTE;', '&ICIRC;', '&IUML;', '&ETH;',
				'&NTILDE;', '&OGRAVE;', '&OACUTE;', '&OCIRC;', '&OTILDE;',
				'&OUML;', '&DIVIDE;', '&OSLASH;', '&UGRAVE;', '&UACUTE;',
				'&UCIRC;', '&UUML;', '&YACUTE;', '&THORN;', '&YUML;',
				'&OELIG;', '&OELIG;', '&SCARON;', '&SCARON;', '&YUML;',
			),
			'GREEK' => ARRAY(
				'&ALPHA;', '&BETA;', '&GAMMA;', '&DELTA;', '&EPSILON;',
				'&ZETA;', '&ETA;', '&THETA;', '&IOTA;', '&KAPPA;',
				'&LAMBDA;', '&MU;', '&NU;', '&XI;', '&OMICRON;',
				'&PI;', '&RHO;', '&SIGMA;', '&TAU;', '&UPSILON;',
				'&PHI;', '&CHI;', '&PSI;', '&OMEGA;', '&ALPHA;',
				'&BETA;', '&GAMMA;', '&DELTA;', '&EPSILON;', '&ZETA;',
				'&ETA;', '&THETA;', '&IOTA;', '&KAPPA;', '&LAMBDA;',
				'&MU;', '&NU;', '&XI;', '&OMICRON;', '&PI;',
				'&RHO;', '&SIGMAF;', '&SIGMA;', '&TAU;', '&UPSILON;',
				'&PHI;', '&CHI;', '&PSI;', '&OMEGA;', '&THETASYM;',
				'&UPSIH;', '&PIV;',
			),
			'OTHER' => ARRAY(
				'&IEXCL;', '&CENT;', '&POUND;', '&CURREN;', '&YEN;',
				'&BRVBAR;', '&SECT;', '&UML;', '&COPY;', '&ORDF;',
				'&LAQUO;', '&NOT;', '&REG;', '&MACR;', '&DEG;',
				'&PLUSMN;', '&SUP2;', '&SUP3;', '&ACUTE;', '&MICRO;',
				'&PARA;', '&MIDDOT;', '&CEDIL;', '&SUP1;', '&ORDM;',
				'&RAQUO;', '&FRAC14;', '&FRAC12;', '&FRAC34;', '&CIRC;',
				'&TILDE;', '&ENSP;', '&EMSP;', '&THINSP;', '&ZWNJ;',
				'&ZWJ;', '&LRM;', '&RLM;', '&NDASH;', '&MDASH;',
				'&LSQUO;', '&RSQUO;', '&SBQUO;', '&LDQUO;', '&RDQUO;',
				'&BDQUO;', '&DAGGER;', '&DAGGER;', '&PERMIL;', '&LSAQUO;',
				'&RSAQUO;', '&EURO;', '&BULL;', '&HELLIP;', '&PRIME;',
				'&PRIME;', '&OLINE;', '&FRASL;', '&WEIERP;', '&IMAGE;',
				'&REAL;', '&TRADE;', '&ALEFSYM;', '&LARR;', '&UARR;',
				'&RARR;', '&DARR;', '&HARR;', '&CRARR;', '&LARR;',
				'&UARR;', '&RARR;', '&DARR;', '&HARR;', '&FORALL;',
				'&PART;', '&EXIST;', '&EMPTY;', '&NABLA;', '&ISIN;',
				'&NOTIN;', '&NI;', '&PROD;', '&SUM;', '&MINUS;',
				'&LOWAST;', '&RADIC;', '&PROP;', '&INFIN;', '&ANG;',
				'&AND;', '&OR;', '&CAP;', '&CUP;', '&INT;',
				'&THERE4;', '&SIM;', '&CONG;', '&ASYMP;', '&NE;',
				'&EQUIV;', '&LE;', '&GE;', '&SUB;', '&SUP;',
				'&NSUB;', '&SUBE;', '&SUPE;', '&OPLUS;', '&OTIMES;',
				'&PERP;', '&SDOT;', '&LCEIL;', '&RCEIL;', '&LFLOOR;',
				'&RFLOOR;', '&LANG;', '&RANG;', '&LOZ;', '&SPADES;',
				'&CLUBS;', '&HEARTS;', '&DIAMS;',
			),
		);
		static $pregEntities = false;
		if (!$pregEntities)
		{
			$pregEntities = array();
			foreach ($arAllEntities as $key => $entities)
			{
				$pregEntities[$key] = implode("|", $entities);
			}
		}
		return preg_replace("/(".implode("|", $pregEntities).")/i", "", $str);
	}

	public static function ReindexFile($path, $SEARCH_SESS_ID = "")
	{
		global $APPLICATION;
		$io = CBXVirtualIo::GetInstance();
		$DB = CDatabase::GetModuleConnection('search');

		if (!is_array($path))
			return 0;

		$file_doc_root = CSite::GetSiteDocRoot($path[0]);
		$file_rel_path = $path[1];
		$file_abs_path = preg_replace("#[\\\\\\/]+#", "/", $file_doc_root."/".$file_rel_path);
		$f = $io->GetFile($file_abs_path);

		if (!$f->IsExists() || !$f->IsReadable())
			return 0;

		if (!CSearch::CheckPath($file_rel_path))
			return 0;

		$max_file_size = COption::GetOptionInt("search", "max_file_size", 0);
		if (
			$max_file_size > 0
			&& $f->GetFileSize() > ($max_file_size * 1024)
		)
			return 0;

		$file_site = "";
		$rsSites = CSite::GetList("lendir", "desc");
		while ($arSite = $rsSites->Fetch())
		{
			$site_path = preg_replace("#[\\\\\\/]+#", "/", $arSite["ABS_DOC_ROOT"]."/".$arSite["DIR"]."/");
			if (mb_strpos($file_abs_path, $site_path) === 0)
			{
				$file_site = $arSite["ID"];
				break;
			}
		}

		if ($file_site == "")
			return 0;

		$item_id = $file_site."|".$file_rel_path;
		if (mb_strlen($item_id) > 255)
			return 0;

		if ($SEARCH_SESS_ID <> '')
		{
			$DATE_CHANGE = $DB->CharToDateFunction(
				FormatDate(
					$DB->DateFormatToPHP(CLang::GetDateFormat("FULL")), $f->GetModificationTime() + CTimeZone::GetOffset()
				)
			);
			$strSql = "
				SELECT ID
				FROM b_search_content
				WHERE MODULE_ID = 'main'
					AND ITEM_ID = '".$DB->ForSQL($item_id)."'
					AND DATE_CHANGE = ".$DATE_CHANGE."
			";

			$r = $DB->Query($strSql);
			if ($arR = $r->Fetch())
			{
				$strSql = "UPDATE b_search_content SET UPD='".$DB->ForSQL($SEARCH_SESS_ID)."' WHERE ID = ".$arR["ID"];
				$DB->Query($strSql);
				return $arR["ID"];
			}
		}

		$arrFile = false;
		foreach (GetModuleEvents("search", "OnSearchGetFileContent", true) as $arEvent)
		{
			if ($arrFile = ExecuteModuleEventEx($arEvent, array($file_abs_path, $SEARCH_SESS_ID)))
				break;
		}
		if (!is_array($arrFile))
		{
			$sFile = $APPLICATION->GetFileContent($file_abs_path);
			$sHeadEndPos = mb_strpos($sFile, "</head>");
			if ($sHeadEndPos === false)
				$sHeadEndPos = mb_strpos($sFile, "</HEAD>");
			if ($sHeadEndPos !== false)
			{
				//html header detected try to get document charset
				$arMetaMatch = array();
				if (preg_match("/<(meta)\\s+([^>]*)(content)\\s*=\\s*(['\"]).*?(charset)\\s*=\\s*(.*?)(\\4)/is", mb_substr($sFile, 0, $sHeadEndPos), $arMetaMatch))
				{
					$doc_charset = $arMetaMatch[6];
					if (strtoupper($doc_charset) != "UTF-8")
						$sFile = \Bitrix\Main\Text\Encoding::convertEncoding($sFile, $doc_charset, "UTF-8");
				}
			}
			$arrFile = ParseFileContent($sFile);
		}

		$title = CSearch::KillTags(trim($arrFile["TITLE"]));

		if ($title == '')
			return 0;

		//strip out all the tags
		$filesrc = CSearch::KillTags($arrFile["CONTENT"]);

		$arGroups = CSearch::GetGroupCached();
		$arGPerm = Array();
		foreach ($arGroups as $group_id)
		{
			$p = $APPLICATION->GetFileAccessPermission(Array($file_site, $file_rel_path), Array($group_id));
			if ($p >= "R")
			{
				$arGPerm[] = $group_id;
				if ($group_id == 2) break;
			}
		}

		$tags = COption::GetOptionString("search", "page_tag_property");

		//save to database
		$ID = CSearch::Index("main", $item_id,
			Array(
				"SITE_ID" => $file_site,
				"DATE_CHANGE" => date("d.m.Y H:i:s", $f->GetModificationTime() + 1),
				"PARAM1" => "",
				"PARAM2" => "",
				"URL" => $file_rel_path,
				"PERMISSIONS" => $arGPerm,
				"TITLE" => $title,
				"BODY" => $filesrc,
				"TAGS" => array_key_exists($tags, $arrFile["PROPERTIES"])? $arrFile["PROPERTIES"][$tags]: "",
			), false, $SEARCH_SESS_ID
		);

		return $ID;
	}

	public static function RecurseIndex($path = Array(), $max_execution_time = 0, &$NS)
	{
		if (!is_array($path))
			return 0;

		$site = $path[0];
		$path = $path[1];

		$DOC_ROOT = CSite::GetSiteDocRoot($site);
		$abs_path = $DOC_ROOT.$path;

		$io = CBXVirtualIo::GetInstance();

		if (!$io->DirectoryExists($abs_path))
			return 0;

		$f = $io->GetFile($abs_path);
		if (!$f->IsReadable())
			return 0;

		$d = $io->GetDirectory($abs_path);
		foreach ($d->GetChildren() as $dir_entry)
		{
			$path_file = $path."/".$dir_entry->GetName();

			if ($dir_entry->IsDirectory())
			{
				if ($path_file == "/bitrix")
					continue;

				//this is not first step and we had stopped here, so go on to reindex
				if (
					$max_execution_time <= 0
					|| $NS["MODULE"] == ''
					|| (
						$NS["MODULE"] == "main"
						&& mb_substr($NS["ID"]."/", 0, mb_strlen($site."|".$path_file."/")) == $site."|".$path_file."/"
					)
				)
				{
					if (CSearch::CheckPath($path_file."/") !== false)
					{
						if (CSearch::RecurseIndex(Array($site, $path_file), $max_execution_time, $NS) === false)
							return false;
					}
				}
				else //all done
				{
					continue;
				}
			}
			else
			{
				//not the first step and we found last file from previous one
				if (
					$max_execution_time > 0
					&& $NS["MODULE"] <> ''
					&& $NS["MODULE"] == "main"
					&& $NS["ID"] == $site."|".$path_file
				)
				{
					$NS["MODULE"] = "";
				}
				elseif ($NS["MODULE"] == '')
				{
					$ID = CSearch::ReindexFile(Array($site, $path_file), $NS["SESS_ID"]);
					if (intval($ID) > 0)
					{
						$NS["CNT"] = intval($NS["CNT"]) + 1;
					}

					if (
						$max_execution_time > 0
						&& (microtime(true) - START_EXEC_TIME > $max_execution_time)
					)
					{
						$NS["MODULE"] = "main";
						$NS["ID"] = $site."|".$path_file;
						return false;
					}
				}
			}
		}

		return true;
	}

	public static function RemovePHP($str)
	{
		$res = "";
		$a = preg_split('/(<'.'\\?|\\?'.'>|\\/\\'.'*|\\'.'*'.'\\/|\\/\\/|\'|"|\\n)/', $str, -1, PREG_SPLIT_DELIM_CAPTURE);
		$c = count($a);
		$i = 0;
		$bPHP = false;
		while ($i < $c)
		{
			if ($a[$i] == '\'' && $bPHP)
			{
				while ((++$i) < $c)
				{
					if ($a[$i] === '\'')
					{
						$m = array();
						if (preg_match('/(\\\\+)$/', $a[$i - 1], $m))
						{
							if ((mb_strlen($m[1]) % 2) == 0) //non even slashes
								break;
						}
						else
						{
							break;
						}
					}
				}
			}
			elseif ($a[$i] == '"' && $bPHP)
			{
				while ((++$i) < $c)
				{
					if ($a[$i] === '"')
					{
						if (preg_match('/(\\\\+)$/', $a[$i - 1], $m))
						{
							if ((mb_strlen($m[1]) % 2) == 0) //non even slashes
								break;
						}
						else
							break;
					}
				}
			}
			elseif ($a[$i] == '//' && $bPHP)
			{
				//single line comment
				while ((++$i) < $c)
				{
					if ($a[$i] === "\n" || $a[$i] === '?>')
						break;
				}
				continue;
			}
			elseif ($a[$i] === '/*' && $bPHP)
			{
				while ((++$i) < $c)
				{
					if ($a[$i] === '*/')
						break;
				}
				continue;
			}
			elseif ($a[$i] === '<?' && !$bPHP) //start of php
			{
				$bPHP = true;
				$i++;
				continue;
			}
			elseif ($a[$i] === '?>' && $bPHP) //end of php
			{
				$bPHP = false;
				$i++;
				continue;
			}

			if (!$bPHP)
				$res .= $a[$i];

			$i++;
		}

		return $res;
	}

	public static function KillTags($str)
	{
		$str = CSearch::RemovePHP($str);

		static $search = array(
			"'<!--.*?-->'si",  // Strip out javascript
			"'<script[^>]*?>.*?</script>'si",  // Strip out javascript
			"'<style[^>]*?>.*?</style>'si",  // Strip out styles
			"'<select[^>]*?>.*?</select>'si",  // Strip out <select></select>
			"'<head[^>]*?>.*?</head>'si",  // Strip out <head></head>
			"'<tr[^>]*?>'",
			"'<[^>]*?>'",
			"'([\\r\\n])[\\s]+'",  // Strip out white space
			"'&(quot|#34);'i",  // Replace html entities
			"'&(amp|#38);'i",
			"'&(lt|#60);'i",
			"'&(gt|#62);'i",
			"'&(nbsp|#160);'i",
			"'[ ]+ '",
		);

		static $replace = array(
			"",
			"",
			"",
			"",
			"",
			"\r\n",
			"\r\n",
			"\\1",
			"\"",
			"&",
			"<",
			">",
			" ",
			" ",
		);

		$str = preg_replace($search, $replace, $str);

		return $str;
	}

	public static function OnChangeFile($path, $site)
	{
		CSearch::ReindexFile(Array($site, $path));
	}

	public static function OnGroupDelete($ID)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$DB->Query("
			DELETE FROM b_search_content_right
			WHERE GROUP_CODE = 'G".intval($ID)."'
		");
	}

	public static function __PrepareFilter($arFilter, &$bIncSites, $strSearchContentAlias = "sc.")
	{
		$DB = CDatabase::GetModuleConnection('search');
		$arSql = array();
		$arNewFilter = array();
		static $arFilterEvents = false;

		if (!is_array($arFilter))
			$arFilter = array();

		foreach ($arFilter as $field => $val)
		{
			$field = mb_strtoupper($field);
			if (
				is_array($val)
				&& count($val) == 1
				&& $field !== "URL"
				&& $field !== "PARAMS"
			)
				$val = $val[0];
			switch ($field)
			{
			case "=MODULE_ID":
				if ($val !== false && $val !== "no")
					$arNewFilter[$field] = $val;
				break;
			case "MODULE_ID":
				if ($val !== false && $val !== "no")
					$arNewFilter["=".$field] = $val;
				break;
			case "ITEM_ID":
			case "PARAM1":
			case "PARAM2":
				if ($val !== false)
					$arNewFilter["=".$field] = $val;
				break;
			case "CHECK_DATES":
				if ($val == "Y")
				{
					$time = ConvertTimeStamp(time() + CTimeZone::GetOffset(), "FULL");
					$arNewFilter[] = array(
						"LOGIC" => "AND",
						array(
							"LOGIC" => "OR",
							"=DATE_FROM" => false,
							"<=DATE_FROM" => $time,
						),
						array(
							"LOGIC" => "OR",
							"=DATE_TO" => false,
							">=DATE_TO" => $time,
						),
					);
				}
				break;
			case "DATE_CHANGE":
				if ($val <> '')
					$arNewFilter[">=".$field] = $val;
				break;
			case "SITE_ID":
				if ($val !== false)
					$arNewFilter["=".$field] = $val;
				break;
			default:
				if (!is_array($arFilterEvents))
				{
					$arFilterEvents = array();
					foreach (GetModuleEvents("search", "OnSearchPrepareFilter", true) as $arEvent)
						$arFilterEvents[] = $arEvent;
				}
				//Try to get someone to make the filter sql
				$sql = "";
				foreach ($arFilterEvents as $arEvent)
				{
					$sql = ExecuteModuleEventEx($arEvent, array($strSearchContentAlias, $field, $val));
					if($sql <> '')
					{
						$arSql[] = "(".$sql.")";
						break;
					}
				}

				if (!$sql)
					$arNewFilter[$field] = $val;
			}
		}

		$strSearchContentAlias = rtrim($strSearchContentAlias, ".");
		$obWhereHelp = new CSearchSQLHelper($strSearchContentAlias);
		$obQueryWhere = new CSQLWhere;
		$obQueryWhere->SetFields(array(
			"MODULE_ID" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".MODULE_ID",
				"MULTIPLE" => "N",
				"FIELD_TYPE" => "string",
				"JOIN" => false,
			),
			"ITEM_ID" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".ITEM_ID",
				"MULTIPLE" => "N",
				"FIELD_TYPE" => "string",
				"JOIN" => false,
			),
			"PARAM1" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".PARAM1",
				"MULTIPLE" => "N",
				"FIELD_TYPE" => "string",
				"JOIN" => false,
			),
			"PARAM2" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".PARAM2",
				"MULTIPLE" => "N",
				"FIELD_TYPE" => "string",
				"JOIN" => false,
			),
			"DATE_FROM" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".DATE_FROM",
				"MULTIPLE" => "N",
				"FIELD_TYPE" => "datetime",
				"JOIN" => false,
			),
			"DATE_TO" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".DATE_TO",
				"MULTIPLE" => "N",
				"FIELD_TYPE" => "datetime",
				"JOIN" => false,
			),
			"DATE_CHANGE" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".DATE_CHANGE",
				"MULTIPLE" => "N",
				"FIELD_TYPE" => "datetime",
				"JOIN" => false,
			),
			"SITE_ID" => array(
				"TABLE_ALIAS" => "scsite",
				"FIELD_NAME" => "scsite.SITE_ID",
				"MULTIPLE" => "N",
				"FIELD_TYPE" => "string",
				"JOIN" => true,
			),
			"SITE_URL" => array(
				"TABLE_ALIAS" => "scsite",
				"FIELD_NAME" => "scsite.URL",
				"MULTIPLE" => "N",
				"FIELD_TYPE" => "string",
				"JOIN" => true,
			),
			"URL" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".URL",
				"MULTIPLE" => "N",
				"FIELD_TYPE" => "callback",
				"CALLBACK" => array($obWhereHelp, "_CallbackURL"),
				"JOIN" => true,
			),
			"PARAMS" => array(
				"TABLE_ALIAS" => $strSearchContentAlias,
				"FIELD_NAME" => $strSearchContentAlias.".ID",
				"MULTIPLE" => "N",
				"FIELD_TYPE" => "callback",
				"CALLBACK" => array($obWhereHelp, "_CallbackPARAMS"),
				"JOIN" => false,
			),
		));

		$strWhere = $obQueryWhere->GetQuery($arNewFilter);

		if (count($arSql) > 0)
		{
			if ($strWhere)
				$strWhere .= "\nAND (".implode(" AND ", $arSql).")";
			else
				$strWhere = implode("\nAND ", $arSql);
		}

		$bIncSites = $bIncSites || $obQueryWhere->GetJoins() <> '';
		return $strWhere;
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
			if (count($arOrder) == 0)
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
					$arOrder[] = '`'.$key."` ".$ord;
					break;
				case "TITLE_RANK":
				case "CUSTOM_RANK":
					$arOrder[] = $key." ".$ord;
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
					$arOrder[] = $key." ".$ord;
					break;
				}
			}

			if (count($arOrder) == 0)
			{
				$arOrder[] = "CUSTOM_RANK DESC";
				$arOrder[] = "`RANK` DESC";
				$arOrder[] = $strSearchContentAlias."DATE_CHANGE DESC";
				$this->flagsUseRatingSort = 0x01;
			}
		}

		return " ORDER BY ".implode(", ", $arOrder);
	}

	public static function Add($arFields)
	{
		$DB = CDatabase::GetModuleConnection('search');

		if (array_key_exists("~DATE_CHANGE", $arFields))
		{
			$arFields["DATE_CHANGE"] = $arFields["~DATE_CHANGE"];
			unset($arFields["~DATE_CHANGE"]);
		}
		elseif (array_key_exists("LAST_MODIFIED", $arFields))
		{
			$arFields["DATE_CHANGE"] = $arFields["LAST_MODIFIED"];
			unset($arFields["LAST_MODIFIED"]);
		}
		elseif (array_key_exists("DATE_CHANGE", $arFields))
		{
			$arFields["DATE_CHANGE"] = $DB->FormatDate($arFields["DATE_CHANGE"], "DD.MM.YYYY HH:MI:SS", CLang::GetDateFormat());
		}

		$arInsert = $DB->PrepareInsert("b_search_content", $arFields);
		$strSql = "REPLACE INTO b_search_content (".$arInsert[0].") VALUES (".$arInsert[1].")";
		$DB->Query($strSql);
		return $DB->LastID();
	}

	public static function OnChangeFilePermissions($path, $permission = array(), $old_permission = array(), $arGroups = false)
	{

		global $APPLICATION;
		$DB = CDatabase::GetModuleConnection('search');

		$site = false;
		CMain::InitPathVars($site, $path);
		$DOC_ROOT = CSite::GetSiteDocRoot($site);
		$path = rtrim($path, "/");

		if (!is_array($arGroups))
		{
			$arGroups = CSearch::GetGroupCached();
			//Check if anonymous permission was changed
			if (!array_key_exists(2, $permission) && array_key_exists("*", $permission))
				$permission[2] = $permission["*"];
			if (!is_array($old_permission))
				$old_permission = array();
			if (!array_key_exists(2, $old_permission) && array_key_exists("*", $old_permission))
				$old_permission[2] = $old_permission["*"];
			//And if not when will do nothing
			if (
				(array_key_exists(2, $permission)
					&& $permission[2] >= "R")
				&& array_key_exists(2, $old_permission)
				&& $old_permission[2] >= "R"
			)
			{
				return;
			}
		}

		if (file_exists($DOC_ROOT.$path))
		{
			@set_time_limit(300);
			if (is_dir($DOC_ROOT.$path))
			{
				$handle = @opendir($DOC_ROOT.$path);
				while (false !== ($file = @readdir($handle)))
				{
					if ($file == "." || $file == "..")
						continue;

					$full_file = $path."/".$file;
					if ($full_file == "/bitrix")
						continue;

					if (is_dir($DOC_ROOT.$full_file) || CSearch::CheckPath($full_file))
						CSearch::OnChangeFilePermissions(array($site, $full_file), array(), array(), $arGroups);
				}
			}
			else//if(is_dir($DOC_ROOT.$path))
			{
				$rs = $DB->Query("
					SELECT SC.ID
					FROM b_search_content SC
					WHERE MODULE_ID='main'
					AND ITEM_ID='".$DB->ForSql($site."|".$path)."'
				");
				if ($ar = $rs->Fetch())
				{
					$arNewGroups = array();
					foreach ($arGroups as $group_id)
					{
						$p = $APPLICATION->GetFileAccessPermission(array($site, $path), array($group_id));
						if ($p >= "R")
						{
							$arNewGroups[$group_id] = 'G'.$group_id;
							if ($group_id == 2)
								break;
						}
					}
					CAllSearch::SetContentItemGroups($ar["ID"], $arNewGroups);
				}
			} //if(is_dir($DOC_ROOT.$path))
		}//if(file_exists($DOC_ROOT.$path))
	}

	public static function SetContentItemGroups($index_id, $arGroups)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$index_id = intval($index_id);

		$arToInsert = array();
		foreach ($arGroups as $group_code)
			if($group_code <> '')
			{
				$arToInsert[$group_code] = $group_code;
			}

		//Read database
		$rs = $DB->Query("
			SELECT * FROM b_search_content_right
			WHERE SEARCH_CONTENT_ID = ".$index_id."
		");
		while ($ar = $rs->Fetch())
		{
			$group_code = $ar["GROUP_CODE"];
			if (isset($arToInsert[$group_code]))
				unset($arToInsert[$group_code]); //This already in DB
			else
				$DB->Query("
					DELETE FROM b_search_content_right
					WHERE
					SEARCH_CONTENT_ID = ".$index_id."
					AND GROUP_CODE = '".$DB->ForSQL($group_code)."'
				"); //And this should be deleted
		}

		foreach ($arToInsert as $group_code)
		{
			$DB->Query("
				INSERT INTO b_search_content_right
				(SEARCH_CONTENT_ID, GROUP_CODE)
				VALUES
				(".$index_id.", '".$DB->ForSQL($group_code, 100)."')
			", true);
		}
	}

	public static function CheckPermissions($FIELD = "sc.ID")
	{
		global $USER;

		$arResult = array();

		if ($USER->IsAdmin())
		{
			$arResult[] = "1=1";
		}
		else
		{
			if ($USER->GetID() > 0)
			{
				CSearchUser::CheckCurrentUserGroups();
				$arResult[] = "
					EXISTS (
						SELECT 1
						FROM b_search_content_right scg
						WHERE ".$FIELD." = scg.SEARCH_CONTENT_ID
						AND scg.GROUP_CODE IN (
							SELECT GROUP_CODE FROM b_search_user_right
							WHERE USER_ID = ".$USER->GetID()."
						)
					)";
			}
			else
			{
				$arResult[] = "
					EXISTS (
						SELECT 1
						FROM b_search_content_right scg
						WHERE ".$FIELD." = scg.SEARCH_CONTENT_ID
						AND scg.GROUP_CODE = 'G2'
					)";
			}
		}
		return "((".implode(") OR (", $arResult)."))";
	}

	public static function SetContentItemParams($index_id, $arParams)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$index_id = intval($index_id);

		$arToInsert = array();

		if (is_array($arParams))
		{
			foreach ($arParams as $k1 => $v1)
			{
				$name = trim($k1);
				if($name <> '')
				{
					$sql_name = "'".$DB->ForSQL($name, 100)."'";

					if(!is_array($v1))
					{
						$v1 = array($v1);
					}

					foreach($v1 as $v2)
					{
						$value = trim($v2);
						if($value <> '')
						{
							$sql_value = "'".$DB->ForSQL($value, 100)."'";
							$key = md5($sql_name).md5($sql_value);

							$arToInsert[$key] = "
								INSERT INTO b_search_content_param
								(SEARCH_CONTENT_ID, PARAM_NAME, PARAM_VALUE)
								VALUES
								(".$index_id.", ".$sql_name.", ".$sql_value.")
							";
						}
					}
				}
			}
		}

		if (empty($arToInsert))
		{
			$DB->Query("
				DELETE FROM b_search_content_param
				WHERE
				SEARCH_CONTENT_ID = ".$index_id."
			");
		}
		else
		{
			$rs = $DB->Query("
				SELECT PARAM_NAME, PARAM_VALUE
				FROM b_search_content_param
				WHERE SEARCH_CONTENT_ID = ".$index_id."
			");
			while ($ar = $rs->Fetch())
			{
				$sql_name = "'".$DB->ForSQL($ar["PARAM_NAME"], 100)."'";
				$sql_value = "'".$DB->ForSQL($ar["PARAM_VALUE"], 100)."'";
				$key = md5($sql_name).md5($sql_value);

				if (array_key_exists($key, $arToInsert))
				{
					unset($arToInsert[$key]);
				}
				else
				{
					$DB->Query($s = "
						DELETE FROM b_search_content_param
						WHERE
						SEARCH_CONTENT_ID = ".$index_id."
						AND PARAM_NAME = ".$sql_name."
						AND PARAM_VALUE = ".$sql_value."
					");
				}
			}
		}

		foreach ($arToInsert as $sql)
			$DB->Query($sql);
	}

	public static function GetContentItemParams($index_id, $param_name = false)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$index_id = intval($index_id);

		if ($index_id <= 0)
		{
			return false;
		}

		$arResult = array();

		$rs = $DB->Query("
			SELECT PARAM_NAME, PARAM_VALUE
			FROM b_search_content_param
			WHERE SEARCH_CONTENT_ID = ".$index_id."
			".($param_name && $param_name <> ''? " AND PARAM_NAME = '".$DB->ForSQL($param_name)."'": "")."
		");
		while ($ar = $rs->Fetch())
		{
			if (!isset($ar["PARAM_NAME"], $arResult))
			{
				$arResult[$ar["PARAM_NAME"]] = array();
			}
			$arResult[$ar["PARAM_NAME"]][] = $ar["PARAM_VALUE"];
		}

		return $arResult;
	}

	function stddev($arValues)
	{
		$mean = array_sum($arValues) / count($arValues);
		$variance = 0.0;
		foreach ($arValues as $v)
			$variance += pow($v - $mean, 2);
		return sqrt($variance / count($arValues));
	}

	function normdev($words_count)
	{
		$a = array();
		while ($words_count > 0)
			$a[] = $words_count--;
		return $this->stddev($a);
	}

	public static function DeleteOld($SESS_ID, $MODULE_ID = "", $SITE_ID = "")
	{
		$DB = CDatabase::GetModuleConnection('search');

		$strFilter = "";
		if ($MODULE_ID != "")
			$strFilter .= " AND MODULE_ID = '".$DB->ForSql($MODULE_ID)."' ";

		$strJoin = "";
		if ($SITE_ID != "")
		{
			$strFilter .= " AND scsite.SITE_ID = '".$DB->ForSql($SITE_ID)."' ";
			$strJoin .= " INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID ";
		}

		if (!is_array($SESS_ID))
			$SESS_ID = array($SESS_ID);

		foreach ($SESS_ID as $key => $value)
			$SESS_ID[$key] = $DB->ForSql($value);

		$strSql = "
			SELECT ID
			FROM b_search_content sc
			".$strJoin."
			WHERE (UPD not in ('".implode("', '", $SESS_ID)."') OR UPD IS NULL)
			".$strFilter."
		";

		$arEvents = GetModuleEvents("search", "OnBeforeIndexDelete", true);

		$rs = $DB->Query($strSql);
		while ($ar = $rs->Fetch())
		{
			foreach ($arEvents as $arEvent)
				ExecuteModuleEventEx($arEvent, array("SEARCH_CONTENT_ID = ".$ar["ID"]));

			$DB->Query("DELETE FROM b_search_content_param WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_content_right WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_content_title WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			CSearchFullText::getInstance()->deleteById($ar["ID"]);
			$DB->Query("DELETE FROM b_search_content WHERE ID = ".$ar["ID"]);
		}

		CSearchTags::CleanCache();
	}

	public static function DeleteForReindex($MODULE_ID)
	{
		$DB = CDatabase::GetModuleConnection('search');

		$MODULE_ID = $DB->ForSql($MODULE_ID);
		$strSql = "SELECT ID FROM b_search_content WHERE MODULE_ID = '".$MODULE_ID."'";

		$arEvents = GetModuleEvents("search", "OnBeforeIndexDelete", true);

		$rs = $DB->Query($strSql);
		while ($ar = $rs->Fetch())
		{
			foreach ($arEvents as $arEvent)
				ExecuteModuleEventEx($arEvent, array("SEARCH_CONTENT_ID = ".$ar["ID"]));

			$DB->Query("DELETE FROM b_search_content_param WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_content_right WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_content_title WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			CSearchFullText::getInstance()->deleteById($ar["ID"]);
			$DB->Query("DELETE FROM b_search_content WHERE ID = ".$ar["ID"]);
		}

		CSearchTags::CleanCache();
	}

	public static function DeleteIndex($MODULE_ID, $ITEM_ID = false, $PARAM1 = false, $PARAM2 = false, $SITE_ID = false)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$bIncSites = false;
		$op = (mb_strpos($ITEM_ID, '%') !== false? '%=': '=');

		if ($PARAM1 !== false && $PARAM2 !== false)
		{
			$strSqlWhere = CSearch::__PrepareFilter(array(
				"MODULE_ID" => $MODULE_ID,
				$op."ITEM_ID" => $ITEM_ID,
				array(
					"=PARAM1" => $PARAM1,
					"PARAM2" => $PARAM2,
				),
				"SITE_ID" => $SITE_ID,
			), $bIncSites);
		}
		else
		{
			$strSqlWhere = CSearch::__PrepareFilter(array(
				"MODULE_ID" => $MODULE_ID,
				$op."ITEM_ID" => $ITEM_ID,
				"PARAM1" => $PARAM1,
				"PARAM2" => $PARAM2,
				"SITE_ID" => $SITE_ID,
			), $bIncSites);
		}

		$strSql = "
			SELECT sc.ID
			FROM b_search_content sc
				".($bIncSites? "INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID": "")."
			WHERE
			".$strSqlWhere."
		";

		$arEvents = GetModuleEvents("search", "OnBeforeIndexDelete", true);

		$rs = $DB->Query($strSql);
		while ($ar = $rs->Fetch())
		{
			foreach ($arEvents as $arEvent)
				ExecuteModuleEventEx($arEvent, array("SEARCH_CONTENT_ID = ".$ar["ID"]));

			$DB->Query("DELETE FROM b_search_content_param WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_content_right WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_content_site WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_content_title WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			$DB->Query("DELETE FROM b_search_tags WHERE SEARCH_CONTENT_ID = ".$ar["ID"]);
			CSearchFullText::getInstance()->deleteById($ar["ID"]);
			$DB->Query("DELETE FROM b_search_content WHERE ID = ".$ar["ID"]);
		}

		CSearchTags::CleanCache();
	}

	public static function Update($ID, $arFields)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$bUpdate = false;

		if (array_key_exists("~DATE_CHANGE", $arFields))
		{
			$arFields["DATE_CHANGE"] = $arFields["~DATE_CHANGE"];
			unset($arFields["~DATE_CHANGE"]);
		}
		elseif (array_key_exists("LAST_MODIFIED", $arFields))
		{
			$arFields["DATE_CHANGE"] = $arFields["LAST_MODIFIED"];
			unset($arFields["LAST_MODIFIED"]);
		}
		elseif (array_key_exists("DATE_CHANGE", $arFields))
		{
			$arFields["DATE_CHANGE"] = $DB->FormatDate($arFields["DATE_CHANGE"], "DD.MM.YYYY HH:MI:SS", CLang::GetDateFormat());
		}

		if (BX_SEARCH_VERSION > 1)
			unset($arFields["SEARCHABLE_CONTENT"]);

		if (array_key_exists("SITE_ID", $arFields))
		{
			CSearch::UpdateSite($ID, $arFields["SITE_ID"]);
			$bUpdate = true;
		}

		if (array_key_exists("PERMISSIONS", $arFields))
		{
			$arNewGroups = array();
			foreach ($arFields["PERMISSIONS"] as $group_id)
			{
				if (is_numeric($group_id))
					$arNewGroups[$group_id] = "G".intval($group_id);
				else
					$arNewGroups[$group_id] = $group_id;
			}
			CSearch::SetContentItemGroups($ID, $arNewGroups);
			$bUpdate = true;
		}

		if (array_key_exists("PARAMS", $arFields))
		{
			CSearch::SetContentItemParams($ID, $arFields["PARAMS"]);
			$bUpdate = true;
		}

		$strUpdate = $DB->PrepareUpdate("b_search_content", $arFields);
		if ($strUpdate <> '')
		{
			$arBinds = Array();
			if (is_set($arFields, "BODY"))
				$arBinds["BODY"] = $arFields["BODY"];
			if (is_set($arFields, "SEARCHABLE_CONTENT"))
				$arBinds["SEARCHABLE_CONTENT"] = $arFields["SEARCHABLE_CONTENT"];
			if (is_set($arFields, "TAGS"))
				$arBinds["TAGS"] = $arFields["TAGS"];
			$DB->QueryBind("UPDATE b_search_content SET ".$strUpdate." WHERE ID=".intval($ID), $arBinds);
			$bUpdate = true;
		}

		if ($bUpdate)
			CSearchFullText::getInstance()->update($ID, $arFields);
	}

	public static function UpdateSite($ID, $arSITE_ID)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$ID = intval($ID);
		if (!is_array($arSITE_ID))
		{
			$DB->Query("
				DELETE FROM b_search_content_site
				WHERE SEARCH_CONTENT_ID = ".$ID."
			");
		}
		else
		{
			$rsSite = $DB->Query("
				SELECT SITE_ID, URL
				FROM b_search_content_site
				WHERE SEARCH_CONTENT_ID = ".$ID."
			");
			while ($arSite = $rsSite->Fetch())
			{
				if (!array_key_exists($arSite["SITE_ID"], $arSITE_ID))
				{
					$DB->Query("
						DELETE FROM b_search_content_site
						WHERE SEARCH_CONTENT_ID = ".$ID."
						AND SITE_ID = '".$DB->ForSql($arSite["SITE_ID"])."'
					");
				}
				else
				{
					if ($arSite["URL"] !== $arSITE_ID[$arSite["SITE_ID"]])
					{
						$DB->Query("
							UPDATE b_search_content_site
							SET URL = '".$DB->ForSql($arSITE_ID[$arSite["SITE_ID"]], 2000)."'
							WHERE SEARCH_CONTENT_ID = ".$ID."
							AND SITE_ID = '".$DB->ForSql($arSite["SITE_ID"])."'
						");
					}
					unset($arSITE_ID[$arSite["SITE_ID"]]);
				}
			}

			foreach ($arSITE_ID as $site => $url)
			{
				$DB->Query("
					INSERT INTO b_search_content_site(SEARCH_CONTENT_ID, SITE_ID, URL)
					VALUES(".$ID.", '".$DB->ForSql($site, 2)."', '".$DB->ForSql($url, 2000)."')
				");
			}
		}
	}

	public static function ChangeIndex($MODULE_ID, $arFields, $ITEM_ID = false, $PARAM1 = false, $PARAM2 = false, $SITE_ID = false)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$bIncSites = false;

		$strSqlWhere = CSearch::__PrepareFilter(array(
			"MODULE_ID" => $MODULE_ID,
			"ITEM_ID" => $ITEM_ID,
			"PARAM1" => $PARAM1,
			"PARAM2" => $PARAM2,
			"SITE_ID" => $SITE_ID,
		), $bIncSites);
		$strSql = "
			SELECT sc.ID
			FROM b_search_content sc
			".($bIncSites? "INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID": "")."
			".($strSqlWhere <> ''? "WHERE ".$strSqlWhere: "")."
		";
		$rs = $DB->Query($strSql);
		while ($ar = $rs->Fetch())
		{
			CSearch::Update($ar["ID"], $arFields);
		}
	}

	public static function ChangeSite($MODULE_ID, $arSite, $ITEM_ID = false, $PARAM1 = false, $PARAM2 = false, $SITE_ID = false)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$bIncSites = false;

		$strSqlWhere = CSearch::__PrepareFilter(array(
			"MODULE_ID" => $MODULE_ID,
			"ITEM_ID" => $ITEM_ID,
			"PARAM1" => $PARAM1,
			"PARAM2" => $PARAM2,
			"SITE_ID" => $SITE_ID,
		), $bIncSites);

		$strSql = "
			SELECT sc.ID
			FROM b_search_content sc
			".($bIncSites? "INNER JOIN b_search_content_site scsite ON sc.ID=scsite.SEARCH_CONTENT_ID": "")."
			WHERE
			".$strSqlWhere."
		";

		$r = $DB->Query($strSql);
		while ($arR = $r->Fetch())
		{
			CSearch::Update($arR["ID"], array("SITE_ID" => $arSite));
		}
	}

	public static function ChangePermission($MODULE_ID, $arGroups, $ITEM_ID = false, $PARAM1 = false, $PARAM2 = false, $SITE_ID = false, $PARAMS = false)
	{
		$DB = CDatabase::GetModuleConnection('search');
		$bIncSites = false;

		$strSqlWhere = CSearch::__PrepareFilter(array(
			"MODULE_ID" => $MODULE_ID,
			"ITEM_ID" => $ITEM_ID,
			"PARAM1" => $PARAM1,
			"PARAM2" => $PARAM2,
			"SITE_ID" => $SITE_ID,
			"PARAMS" => $PARAMS,
		), $bIncSites);

		if ($strSqlWhere)
		{
			$strSqlJoin1 = "INNER JOIN b_search_content sc ON sc.ID = b_search_content_right.SEARCH_CONTENT_ID";
			$match = array();
			//Copy first exists into inner join in hopeless try to defeat MySQL optimizer
			if (preg_match('#^\\s*EXISTS (\\(SELECT \\* FROM b_search_content_param WHERE SEARCH_CONTENT_ID = sc.ID AND PARAM_NAME = \'[^\']+\' AND PARAM_VALUE  = \'[^\']+\'\\))#', $strSqlWhere, $match))
			{
				$subTable = str_replace("SEARCH_CONTENT_ID = sc.ID AND", "", $match[1]);
				$strSqlJoin2 = "INNER JOIN ".$subTable." p1 ON p1.SEARCH_CONTENT_ID = sc.ID";
			}
			else
			{
				$strSqlJoin2 = "";
			}
		}
		else
		{
			$strSqlJoin1 = "";
			$strSqlJoin2 = "";
		}

		$rs = $DB->Query("
			SELECT sc.ID
			FROM b_search_content sc
			".$strSqlJoin2."
			".($strSqlWhere?
				"WHERE ".$strSqlWhere:
				""
			)."
		");
		while ($arR = $rs->fetch())
		{
			CSearch::Update($arR["ID"], array("PERMISSIONS" => $arGroups));
		}
	}
}

class CSearchSQLHelper
{
	var $bIncSites = false;
	var $strSearchContentAlias = "";

	function __construct($strSearchContentAlias)
	{
		$this->strSearchContentAlias = $strSearchContentAlias;
	}

	function _CallbackURL($field_name, $operation, $field_value)
	{
		global $DB;

		if (is_array($field_value))
			$sql_values = array_map(array($DB, "ForSQL"), array_filter($field_value));
		elseif ($field_value !== false)
			$sql_values = array($DB->ForSQL($field_value));
		else
			$sql_values = array();

		$strSql = "";
		if (!empty($sql_values))
		{
			switch ($operation)
			{
			case "I":
			case "E":
			case "S":
			case "M":
				foreach ($sql_values as $url_i)
				{
					$arSQL[] = $this->strSearchContentAlias.".URL LIKE '".$url_i."'";
					$arSQL[] = "scsite.URL LIKE '".$url_i."'";
				}
				$strSql = "(".implode(") OR (", $arSQL).")";
				$this->bIncSites = true;
				break;
			case "NI":
			case "N":
			case "NS":
			case "NM":
				$arSQL = array();
				foreach ($sql_values as $url_i)
				{
					$arSQL[] = $this->strSearchContentAlias.".URL NOT LIKE '".$url_i."'";
					$arSQL[] = "scsite.URL NOT LIKE '".$url_i."'";
				}
				$strSql = "(".implode(") AND (", $arSQL).")";
				$this->bIncSites = true;
				break;
			default:
				break;
			}
		}

		if ($strSql)
			return "(".$strSql.")";
		else
			return "";
	}

	function _CallbackPARAMS($field_name, $operation, $field_value)
	{
		global $DB;

		$arSql = array();
		if (is_array($field_value))
		{
			foreach ($field_value as $key => $val)
			{
				if (is_array($val))
				{
					foreach ($val as $i => $val2)
						$val[$i] = $DB->ForSQL($val2);
					$where = " in ('".implode("', '", $val)."')";
				}
				else
				{
					$where = " = '".$DB->ForSQL($val)."'";
				}
				$arSql[] = "EXISTS (SELECT * FROM b_search_content_param WHERE SEARCH_CONTENT_ID = ".$field_name." AND PARAM_NAME = '".$DB->ForSQL($key)."' AND PARAM_VALUE ".$where.")";
			}
		}

		switch ($operation)
		{
		case "I":
		case "E":
		case "S":
		case "M":
			if (count($arSql))
				return implode(" AND ", $arSql);
		}
	}
}

class CAllSearchQuery
{
	var $m_query;
	var $m_parsed_query;
	var $m_words;
	var $m_stemmed_words;
	var $m_stemmed_words_id;
	var $m_fields;
	var $m_kav;
	var $default_query_type;
	var $rus_bool_lang;
	var $no_bool_lang;
	var $m_casematch;
	var $error = "";
	var $errorno = 0;
	var $bTagsSearch = false;
	var $m_tags_words;
	var $bStemming = false;
	var $bText = false;

	function __construct($default_query_type = "and", $rus_bool_lang = "yes", $m_casematch = 0, $site_id = "")
	{
		$this->m_query = "";
		$this->m_stemmed_words = array();
		$this->m_tags_words = array();
		$this->m_fields = "";
		$this->default_query_type = $default_query_type;
		$this->rus_bool_lang = $rus_bool_lang;
		$this->m_casematch = $m_casematch;
		$this->m_kav = array();
		$this->error = "";

		$db_site_tmp = CSite::GetByID($site_id);
		if ($ar_site_tmp = $db_site_tmp->Fetch())
			$this->m_lang = $ar_site_tmp["LANGUAGE_ID"];
		else
			$this->m_lang = "en";
	}

	function GetQueryString($fields, $query, $bTagsSearch = false, $bUseStemming = true, $bErrorOnEmptyStem = false)
	{
		$this->m_words = Array();
		$this->m_fields = explode(",", $fields);

		$this->bTagsSearch = $bTagsSearch;
		//In case there is no masks used we'll keep list
		//of all tags in this member
		//to perform optimization
		$this->m_tags_words = array();

		$this->m_query = $query = $this->CutKav($query);

		//Assume query does not have any word which can be stemmed
		$this->bStemming = false;
		if (!$this->bTagsSearch && $bUseStemming && COption::GetOptionString("search", "use_stemming") == "Y")
		{
			//In case when at least one word found: $this->bStemming = true
			$stem_query = $this->StemQuery($query, $this->m_lang);
			if ($this->bStemming === true || $bErrorOnEmptyStem)
				$query = $stem_query;
		}
		$this->m_parsed_query = $query = $this->ParseQ($query);

		if ($query == "( )" || $query == '')
		{
			$this->error = GetMessage("SEARCH_ERROR3");
			$this->errorno = 3;
			return false;
		}

		$query = $this->PrepareQuery($query);

		return $query;
	}

	function CutKav($query)
	{
		$arQuotes = array();
		if (preg_match_all("/([\"'])(.*?)(?<!\\\\)(\\1)/s", $query, $arQuotes))
		{
			foreach ($arQuotes[2] as $i => $quoted)
			{
				$quoted = trim($quoted);
				if($quoted <> '')
				{
					$repl = $i."cut5";
					$this->m_kav[$repl] = str_replace("\\\"", "\"", $quoted);
					$query = str_replace($arQuotes[0][$i], " ".$repl." ", $query);
				}
				else
				{
					$query = str_replace($arQuotes[0][$i], " ", $query);
				}

				if ($i > 100) break;
			}
		}
		return $query;
	}

	function ParseQ($q)
	{
		$q = trim($q);
		if ($q == '')
			return '';

		$q = $this->ParseStr($q);

		$q = str_replace(
			array("&", "|", "~", "(", ")"),
			array(" && ", " || ", " ! ", " ( ", " ) "),
			$q
		);
		$q = "( $q )";
		$q = preg_replace("/\\s+/u", " ", $q);

		return $q;
	}

	function ParseStr($qwe)
	{
		//Take alphabet into account
		$arStemInfo = stemming_init($this->m_lang);
		$letters = $arStemInfo["pcre_letters"]."|+&~()";

		//Erase delimiters from the query
		$qwe = trim(preg_replace("/[^".$letters."]+/u", " ", $qwe));

		// query language normalizer
		if (!$this->no_bool_lang)
		{
			$qwe = preg_replace("/(\\s+|^|[|&~])or(\\s+|\$|[|&~])/isu", "\\1|\\2", $qwe);
			$qwe = preg_replace("/(\\s+|^|[|&~])and(\\s+|\$|[|&~])/isu", "\\1&\\2", $qwe);
			$qwe = preg_replace("/(\\s+|^|[|&~])not(\\s+|\$|[|&~])/isu", "\\1~\\2", $qwe);
			$qwe = preg_replace("/(\\s+|^|[|&~])without(\\s+|\$|[|&~])/isu", "\\1~\\2", $qwe);

			if ($this->rus_bool_lang == 'yes')
			{
				$qwe = preg_replace("/(\\s+|^|[|&~])".GetMessage("SEARCH_TERM_OR")."(\\s+|\$|[|&~])/isu", "\\1|\\2", $qwe);
				$qwe = preg_replace("/(\\s+|^|[|&~])".GetMessage("SEARCH_TERM_AND")."(\\s+|\$|[|&~])/isu", "\\1&\\2", $qwe);
				$qwe = preg_replace("/(\\s+|^|[|&~])".GetMessage("SEARCH_TERM_NOT_1")."(\\s+|\$|[|&~])/isu", "\\1~\\2", $qwe);
				$qwe = preg_replace("/(\\s+|^|[|&~])".GetMessage("SEARCH_TERM_NOT_2")."(\\s+|\$|[|&~])/isu", "\\1~\\2", $qwe);
			}
		}

		$qwe = preg_replace("/(\\s*\\|+\\s*)/isu", "|", $qwe);
		$qwe = preg_replace("/(\\s*\\++\\s*|\\s*\\&\\s*)/isu", "&", $qwe);
		$qwe = preg_replace("/(\\s*\\~+\\s*)/isu", "~", $qwe);

		$qwe = preg_replace("/\s*([()])\s*/su", "\\1", $qwe);

		// default query type is and
		if (mb_strtolower($this->default_query_type) == 'or')
			$default_op = "|";
		else
			$default_op = "&";

		$qwe = preg_replace("/(\s+|\&\|+|\|\&+)/su", $default_op, $qwe);

		// remove unnesessary boolean operators
		$qwe = preg_replace("/\|+/", "|", $qwe);
		$qwe = preg_replace("/&+/", "&", $qwe);
		$qwe = preg_replace("/~+/", "~", $qwe);
		$qwe = preg_replace("/\|\&\|/", "&", $qwe);
		$qwe = preg_replace("/[\|\&\~]+$/", "", $qwe);
		$qwe = preg_replace("/^[\|\&]+/", "", $qwe);

		// transform "w1 ~w2" -> "w1 default_op ~ w2"
		// ") ~w" -> ") default_op ~w"
		// "w ~ (" -> "w default_op ~("
		// ") w" -> ") default_op w"
		// "w (" -> "w default_op ("
		// ")(" -> ") default_op ("

		$qwe = preg_replace("/([^\&\~\|\(\)]+)~([^\&\~\|\(\)]+)/su", "\\1".$default_op."~\\2", $qwe);
		$qwe = preg_replace("/\)~{1,}/su", ")".$default_op."~", $qwe);
		$qwe = preg_replace("/~{1,}\(/su", ($default_op == "|"? "~|(": "&~("), $qwe);
		$qwe = preg_replace("/\)([^\&\~\|\(\)]+)/su", ")".$default_op."\\1", $qwe);
		$qwe = preg_replace("/([^\&\~\|\(\)]+)\(/su", "\\1".$default_op."(", $qwe);
		$qwe = preg_replace("/\) *\(/su", ")".$default_op."(", $qwe);

		// remove unnesessary boolean operators
		$qwe = preg_replace("/\|+/", "|", $qwe);
		$qwe = preg_replace("/&+/", "&", $qwe);

		// remove errornous format of query - ie: '(&', '&)', '(|', '|)', '~&', '~|', '~)'
		$qwe = preg_replace("/\(\&{1,}/s", "(", $qwe);
		$qwe = preg_replace("/\&{1,}\)/s", ")", $qwe);
		$qwe = preg_replace("/\~{1,}\)/s", ")", $qwe);
		$qwe = preg_replace("/\(\|{1,}/s", "(", $qwe);
		$qwe = preg_replace("/\|{1,}\)/s", ")", $qwe);
		$qwe = preg_replace("/\~{1,}\&{1,}/s", "&", $qwe);
		$qwe = preg_replace("/\~{1,}\|{1,}/s", "|", $qwe);

		$qwe = preg_replace("/\(\)/s", "", $qwe);
		$qwe = preg_replace("/^[\|\&]{1,}/s", "", $qwe);
		$qwe = preg_replace("/[\|\&\~]{1,}$/s", "", $qwe);
		$qwe = preg_replace("/\|\&/s", "&", $qwe);
		$qwe = preg_replace("/\&\|/s", "|", $qwe);

		// remove unnesessary boolean operators one more time
		$qwe = preg_replace("/\|+/", "|", $qwe);
		$qwe = preg_replace("/&+/", "&", $qwe);

		return $qwe;
	}

	function StemWord($w)
	{
		static $preg_ru = false;
		if (is_array($w))
			$w = $w[0];
		$wu = mb_strtoupper($w);

		if (!$this->no_bool_lang)
		{
			if (preg_match("/^(OR|AND|NOT|WITHOUT)$/", $wu))
			{
				return $w;
			}
			elseif ($this->rus_bool_lang == 'yes')
			{
				if ($preg_ru === false)
					$preg_ru = "/^(".mb_strtoupper(GetMessage("SEARCH_TERM_OR")."|".GetMessage("SEARCH_TERM_AND")."|".GetMessage("SEARCH_TERM_NOT_1")."|".GetMessage("SEARCH_TERM_NOT_2")).")$/u";
				if (preg_match($preg_ru, $wu))
					return $w;
			}
		}

		if (preg_match("/cut[56]/i", $w))
			return $w;
		$arrStem = array_keys(stemming($w, $this->m_lang));
		if (count($arrStem) < 1)
			return " ";
		else
		{
			$this->bStemming = true;
			return '('.implode('|', $arrStem).')';
		}
	}

	function StemQuery($q, $lang = "en")
	{
		$arStemInfo = stemming_init($lang);
		return preg_replace_callback("/([".$arStemInfo["pcre_letters"]."]+)/u", array($this, "StemWord"), $q);
	}

	function PrepareQuery($q)
	{
		$state = 0;
		$qu = [];
		$n = 0;
		$this->error = '';

		foreach (preg_split('/ +/', $q) as $t)
		{
			if ($state === 0)
			{
				if (($t === '||') || ($t === '&&') || ($t === ')'))
				{
					$this->error = GetMessage('SEARCH_ERROR2') . ' ' . $t;
					$this->errorno = 2;
					break;
				}
				elseif ($t === '!')
				{
					$state = 0;
					$qu[] = ' NOT ';
				}
				elseif ($t == '(')
				{
					$n++;
					$state = 0;
					$qu[] = '(';
				}
				else
				{
					$state = 1;
					$where = $this->BuildWhereClause($t);
					$c = count($qu);
					if (
						$where === '1=1'
						&& (
							($c > 0 && $qu[$c - 1] === ' OR ')
							|| ($c > 1 && $qu[$c - 1] === '(' && $qu[$c - 2] === ' OR ')
						)
					)
					{
						$where = '1<>1';
					}
					$qu[] = ' ' . $where . ' ';
				}
			}
			else
			{
				if (($t === '||') || ($t === '&&'))
				{
					$state = 0;
					if ($t === '||')
					{
						$qu[] = ' OR ';
					}
					else
					{
						$qu[] = ' AND ';
					}
				}
				elseif ($t === ')')
				{
					$n--;
					$state = 1;
					$qu[] = ')';
				}
				else
				{
					$this->error = GetMessage('SEARCH_ERROR2') . ' ' . $t;
					$this->errorno = 2;
					break;
				}
			}
		}

		if (($this->error === '') && ($n !== 0))
		{
			$this->error = GetMessage('SEARCH_ERROR1');
			$this->errorno = 1;
		}

		if ($this->error != '')
		{
			return 0;
		}

		return implode($qu);
	}
}

class CSearchCallback
{
	var $MODULE = "";
	var $max_execution_time = 0;
	var $CNT = 0;
	var $SESS_ID = "";

	function Index($arFields)
	{
		$ID = $arFields["ID"];
		if ($ID == "")
			return true;
		unset($arFields["ID"]);
		CSearch::Index($this->MODULE, $ID, $arFields, false, $this->SESS_ID);
		$this->CNT = $this->CNT + 1;
		if ($this->max_execution_time > 0 && microtime(true) - START_EXEC_TIME > $this->max_execution_time)
			return false;
		else
			return true;
	}
}
