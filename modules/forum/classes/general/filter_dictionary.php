<?
##############################################
# Bitrix Site Manager Forum                  #
# Copyright (c) 2002-2009 Bitrix             #
# http://www.bitrixsoft.com                  #
# mailto:admin@bitrixsoft.com                #
##############################################
IncludeModuleLangFile(__FILE__);

class CAllFilterDictionary
{
	function CheckFields($arFields = array(), $ACTION = "ADD")
	{
		global $APPLICATION, $DB;
		$strError = "";

		if ((!is_set($arFields, "TITLE")) || (trim($arFields["TITLE"]) == ''))
			$strError .= GetMessage("FLT_ERR_TITLE_MISSED");
		if ($ACTION != "UPDATE" && empty($arFields["TYPE"]))
			$strError .= GetMessage("FLT_ERR_TYPE_MISSED");
		if ($strError == '')
			return true;
		$APPLICATION->ThrowException($strError);
		return false;
	}
	function Add($arFields)
	{
		global $DB;
		$arFields["TITLE"] = trim($arFields["TITLE"]);
		$arFields["TYPE"] = mb_strtoupper(trim($arFields["TYPE"]));
		if ($arFields["TYPE"] != "T")
			$arFields["TYPE"] = "W";
		if(CFilterDictionary::CheckFields($arFields))
			return $DB->Add("b_forum_dictionary", $arFields);
		return false;
	}
	function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);
		if(is_set($arFields, "TITLE"))
			$arFields["TITLE"]=trim($arFields["TITLE"]);
		if(is_set($arFields, "TYPE"))
		{
			$arFields["TYPE"] = mb_strtoupper(trim($arFields["TYPE"]));
			if ($arFields["TYPE"] != "T")
				$arFields["TYPE"] = "W";
		}
		if (($ID>0) && (CFilterDictionary::CheckFields($arFields, "UPDATE")))
		{
			$strUpdate = $DB->PrepareUpdate("b_forum_dictionary", $arFields);
			$res = $DB->Query("UPDATE b_forum_dictionary SET ".$strUpdate." WHERE ID=".$ID);
			return $res;
		}
		return false;
	}
	function Delete($ID)
	{
		global $DB, $USER;

		$ID = intval($ID);
		$Dictionary = "";
		$Dictionary = CFilterDictionary::GetList(array(), array("ID"=>$ID));
		$Dictionary = $Dictionary->Fetch();
		$res = false;
		$DB->StartTransaction();
			if ($Dictionary["TYPE"] == "T")
				$res = $DB->Query("DELETE FROM b_forum_letter WHERE DICTIONARY_ID=".$ID);
			else
				$res = $DB->Query("DELETE FROM b_forum_filter WHERE DICTIONARY_ID=".$ID);
			if ($res)
				$res = $DB->Query("DELETE FROM b_forum_dictionary WHERE ID=".$ID);
		if ($res)
			$DB->Commit();
		else
			$DB->Rollback();
		return $res;
	}

	function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (mb_substr($key, 0, 1) == "!")
		{
			$key = mb_substr($key, 1);
			$strNegative = "Y";
		}

		if (mb_substr($key, 0, 2) == ">=")
		{
			$key = mb_substr($key, 2);
			$strOperation = ">=";
		}
		elseif (mb_substr($key, 0, 1) == ">")
		{
			$key = mb_substr($key, 1);
			$strOperation = ">";
		}
		elseif (mb_substr($key, 0, 2) == "<=")
		{
			$key = mb_substr($key, 2);
			$strOperation = "<=";
		}
		elseif (mb_substr($key, 0, 1) == "<")
		{
			$key = mb_substr($key, 1);
			$strOperation = "<";
		}
		elseif (mb_substr($key, 0, 1) == "@")
		{
			$key = mb_substr($key, 1);
			$strOperation = "IN";
		}
		elseif (mb_substr($key, 0, 1) == "%")
		{
			$key = mb_substr($key, 1);
			$strOperation = "LIKE";
		}
		else
		{
			$strOperation = "=";
		}
		return array("FIELD"=>$key, "NEGATIVE"=>$strNegative, "OPERATION"=>$strOperation);
	}
}

class CAllFilterLetter
{
	function CheckFields($arFields = array())
	{
		global $APPLICATION, $DB;
		$strError = "";

		if ((!is_set($arFields, "LETTER")) || (trim($arFields["LETTER"]) == ''))
			$strError .= GetMessage("FLT_ERR_SIMBOL_MISSED");
		if ((!is_set($arFields, "DICTIONARY_ID")) || (intval(trim($arFields["DICTIONARY_ID"]))<=0))
			$strError .= GetMessage("FLT_ERR_DICTIONARY_MISSED");
		if ($strError == '')
			return true;
		$APPLICATION->ThrowException($strError);
		return false;
	}

	function Add($arFields)
	{
		global $DB, $APPLICATION;
		$arFields["LETTER"] = trim($arFields["LETTER"]);
		$arFields["REPLACEMENT"] = trim($arFields["REPLACEMENT"]);
		$arFields["DICTIONARY_ID"] = intval($arFields["DICTIONARY_ID"]);
		$db_res = CFilterLetter::GetList(array(), array("DICTIONARY_ID"=>$arFields["DICTIONARY_ID"], "LETTER"=>trim($arFields["LETTER"])));
		$db_res = $db_res->Fetch();
		if ($db_res["ID"]<=0)
		{
			if(CFilterLetter::CheckFields($arFields))
				return $DB->Add("b_forum_letter", $arFields);
		}
		else
			$APPLICATION->ThrowException(GetMessage("FLT_ALREADY_EXIST"));
		return false;
	}

	function Update($ID, $arFields)
	{
		global $DB, $APPLICATION;
		$ID = intval($ID);
		$letter = false; $dictionary_id = false; $update = false;
		if (is_set($arFields, "LETTER"))
		{
			$arFields["LETTER"] = trim($arFields["LETTER"]);
			$letter = true;
		}
		if (is_set($arFields, "REPLACEMENT"))
			$arFields["REPLACEMENT"] = trim($arFields["REPLACEMENT"]);
		if (is_set($arFields, "DICTIONARY_ID"))
		{
			$arFields["DICTIONARY_ID"] = intval($arFields["DICTIONARY_ID"]);
			$dictionary_id = true;
		}

		if ($letter || $dictionary_id)
		{
			$ID1 = CFilterLetter::GetByID($ID);
			$request = array();
			if ($letter && !$dictionary_id)
				$request = array("DICTIONARY_ID"=>$ID1["DICTIONARY_ID"], "LETTER"=>$arFields["LETTER"]);
			elseif (!$letter && $dictionary_id)
				$request = array("DICTIONARY_ID"=>$arFields["DICTIONARY_ID"], "LETTER"=>$ID1["LETTER"]);
			elseif ($letter && $dictionary_id)
				$request = array("DICTIONARY_ID"=>$arFields["DICTIONARY_ID"], "LETTER"=>$arFields["LETTER"]);
			$db_res = CFilterLetter::GetList(array(), $request);
			$db_res = $db_res->Fetch();
			if ((intval($db_res["ID"])<=0) || (intval($db_res["ID"]) == $ID))
				$update = true;
		}
		if (!$update)
			$APPLICATION->ThrowException(GetMessage("FLT_ALREADY_EXIST"));
		if (($ID>0) && (CFilterLetter::CheckFields($arFields)) && ($update))
		{
			$strUpdate = $DB->PrepareUpdate("b_forum_letter", $arFields);
			$res = $DB->Query("UPDATE b_forum_letter SET ".$strUpdate." WHERE ID=".$ID);
			return $res;
		}
		return false;
	}

	function Delete($ID)
	{
		global $DB, $USER;
		$ID = intval($ID);
		return $DB->Query("DELETE FROM b_forum_letter WHERE ID=".$ID);
	}

	function GetByID($ID)
	{
		$res = array();
		$res = CFilterLetter::GetList(array(), array("ID"=>$ID));
		if ($res)
			return $res->GetNext();
		else
			return false;
	}
}

class CAllFilterUnquotableWords
{
	function CheckPattern($sPattern, &$sError)
	{
		$arError = array();
		$sDelimiter = mb_substr($sPattern, 0, 1);
		if ($sDelimiter !== '/') // security restriction
			$arError[] = array(
				"code" => "bad_delimiter",
				"title" => GetMessage("FLT_ERR_BAD_DELIMITER"));
		// Check Modificators
		$sModificators = strrchr(mb_substr($sPattern, 1), $sDelimiter);
		if ($sModificators && mb_strpos($sModificators, "e") !== false)
			$arError[] = array(
				"code" => "bad_modificator",
				"title" => GetMessage("FLT_ERR_BAD_MODIFICATOR"));
		ob_end_clean();
		ob_start();
		$arTest = array();
		$sTest = preg_match($sPattern, "test string", $arTest);
		$contents = ob_get_contents();
		ob_end_clean();
		if (!empty($contents))
			$arError[] = array(
				"code" => "bad_pattern",
				"title" => GetMessage("FLT_ERR_BAD_PATTERN"));
		if (empty($arError))
			return true;
		$sError = ForumShowError($arError);
		return false;
	}

	function CheckFields($action = "INSERT", $ID = false, $arFields = array())
	{
		global $APPLICATION, $DB;
		$arError = array();
		$sError = '';
		$ID = intval($ID);

		if ($action == "INSERT")
		{
			if ($action == "INSERT" && trim($arFields["WORDS"]) == '' && trim($arFields["PATTERN"]) == '')
				$arError[] = array(
					"code" => "empty_data",
					"title" => GetMessage("FLT_ERR_DICT_PATT_MISSED"));
			if (intval($arFields["DICTIONARY_ID"]) <= 0)
				$arError[] = array(
					"code" => "bad_dictionary_id",
					"title" => GetMessage("FLT_ERR_DICTIONARY_MISSED"));
		}
		else
		{
			if ($ID <= 0)
				$arError = array(
					"code" => "empty_id",
					"title" => GetMessage("FLT_ERR_ID_NOT_ENTER"));
			if (is_set($arFields, "DICTIONARY_ID") && intval($arFields["DICTIONARY_ID"]) <= 0)
				$arError[] = array(
					"code" => "bad_dictionary_id",
					"title" => GetMessage("FLT_ERR_DICTIONARY_MISSED"));
		}
		if (!empty($arFields["PATTERN"]) && !CFilterUnquotableWords::CheckPattern($arFields["PATTERN"], $sError))
			$arError[] = array(
				"code" => "bad_pattern",
				"title" => $sError);
		if (trim($arFields["WORDS"]) <> '')
		{
			$db_res = CFilterUnquotableWords::GetList(array(), array("WORDS" => trim($arFields["WORDS"])));
			if ($db_res && $res = $db_res->Fetch())
			{
				if ($action == "INSERT" || ($action=="UPDATE" && intval($res["ID"]) != $ID))
				{
					$arError[] = array(
						"code" => "already_exists",
						"title" => GetMessage("FLT_ALREADY_EXIST"));
				}
			}
		}
		if (empty($arError))
			return true;
		$APPLICATION->ThrowException(ForumShowError($arError));
		return false;
	}

	function Add($arFields)
	{
		global $DB;
		if(CACHED_b_forum_filter !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum_filter");
		$arFields["DICTIONARY_ID"] = intval($arFields["DICTIONARY_ID"]);
		$arFields["PATTERN_CREATE"] = mb_strtoupper(trim($arFields["PATTERN_CREATE"]));
		$arFields["PATTERN_CREATE"] = in_array($arFields["PATTERN_CREATE"], array("WORDS", "PTTRN", "TRNSL")) ? $arFields["PATTERN_CREATE"] : false;
		if (!$arFields["PATTERN_CREATE"])
			$arFields["PATTERN_CREATE"] = ($arFields["WORDS"] ? "TRNSL" : "PTTRN");

		$arFields["WORDS"] = trim($arFields["PATTERN_CREATE"] == "TRNSL"? mb_strtolower($arFields["WORDS"]) : $arFields["WORDS"]);
		$arFields["PATTERN"] = trim($arFields["PATTERN"]);
		$arFields["REPLACEMENT"] = trim($arFields["REPLACEMENT"]);
		$arFields["DESCRIPTION"] = trim($arFields["DESCRIPTION"]);
		$arFields["USE_IT"] = ($arFields["USE_IT"] == "Y" ? "Y" : "N");
		if(CFilterUnquotableWords::CheckFields("INSERT", false, $arFields))
			return $DB->Add("b_forum_filter", $arFields, Array("PATTERN", "DESCRIPTION"));
		return false;
	}

	function Update($ID, $arFields)
	{
		global $DB;
		$ID = intval($ID);
		if(CACHED_b_forum_filter !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum_filter");
		if(is_set($arFields, "DICTIONARY_ID"))
			$arFields["DICTIONARY_ID"] = intval($arFields["DICTIONARY_ID"]);
		if(is_set($arFields, "PATTERN_CREATE"))
		{
			$arFields["PATTERN_CREATE"] = mb_strtoupper(trim($arFields["PATTERN_CREATE"]));
			if ($arFields["PATTERN_CREATE"] == '')
			{
				if ($arFields["WORDS"])
					$arFields["PATTERN_CREATE"] = "TRNSL";
				elseif($arFields["PATTERN_CREATE"])
					$arFields["PATTERN_CREATE"] = "PTTRN";
			}
		}
		if(is_set($arFields, "WORDS"))
		{
			$arFields["WORDS"] = trim($arFields["WORDS"]);
			if ($arFields["PATTERN_CREATE"] == "TRNSL")
				$arFields["WORDS"] = mb_strtolower($arFields["WORDS"]);
		}

		if(is_set($arFields, "PATTERN"))
			$arFields["PATTERN"] = trim($arFields["PATTERN"]);
		if(is_set($arFields, "REPLACEMENT"))
			$arFields["REPLACEMENT"] = trim($arFields["REPLACEMENT"]);
		if(is_set($arFields, "DESCRIPTION"))
			$arFields["DESCRIPTION"] = trim($arFields["DESCRIPTION"]);
		if(is_set($arFields, "USE_IT") && $arFields["USE_IT"]!="Y")
			$arFields["USE_IT"]="N";

		if (($ID>0) && (CFilterUnquotableWords::CheckFields("UPDATE", $ID, $arFields)))
		{
			$strUpdate = $DB->PrepareUpdate("b_forum_filter", $arFields);
			$strSql = "UPDATE b_forum_filter SET ".$strUpdate." WHERE ID=".$ID;
			$res = $DB->QueryBind($strSql, Array("PATTERN"=>$arFields["PATTERN"], "DESCRIPTION"=>$arFields["DESCRIPTION"]), false, "FILE: ".__FILE__."<br> LINE: ".__LINE__);
			return $res;
		}
		return false;
	}

	function Delete($ID)
	{
		global $DB, $USER;
		$ID = intval($ID);
		if(CACHED_b_forum_filter !== false)
			$GLOBALS["CACHE_MANAGER"]->CleanDir("b_forum_filter");
		return $DB->Query("DELETE FROM b_forum_filter WHERE ID=".$ID);
	}

	function GetById($ID)
	{
		$ID = intval($ID);
		$res = CFilterUnquotableWords::GetList(array(), array("ID"=>$ID));
		return $res->Fetch();
	}

	function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (mb_substr($key, 0, 1) == "!")
		{
			$key = mb_substr($key, 1);
			$strNegative = "Y";
		}

		if (mb_substr($key, 0, 2) == ">=")
		{
			$key = mb_substr($key, 2);
			$strOperation = ">=";
		}
		elseif (mb_substr($key, 0, 1) == ">")
		{
			$key = mb_substr($key, 1);
			$strOperation = ">";
		}
		elseif (mb_substr($key, 0, 2) == "<=")
		{
			$key = mb_substr($key, 2);
			$strOperation = "<=";
		}
		elseif (mb_substr($key, 0, 1) == "<")
		{
			$key = mb_substr($key, 1);
			$strOperation = "<";
		}
		elseif (mb_substr($key, 0, 1) == "@")
		{
			$key = mb_substr($key, 1);
			$strOperation = "IN";
		}
		elseif (mb_substr($key, 0, 1) == "%")
		{
			$key = mb_substr($key, 1);
			$strOperation = "LIKE";
		}
		else
		{
			$strOperation = "=";
		}
		return array("FIELD"=>$key, "NEGATIVE"=>$strNegative, "OPERATION"=>$strOperation);
	}

	function GenPattern($ID=false, $DICTIONARY_ID_T=0)
	{
		$DICTIONARY_ID_T = intval($DICTIONARY_ID_T);
		if (!$DICTIONARY_ID_T)
			$DICTIONARY_ID_T = (COption::GetOptionString("forum", "FILTER_DICT_T", '', LANGUAGE_ID));
		$ID = intval($ID);
		if ($ID):
			$res = CFilterUnquotableWords::GetByID($ID);
			if ((trim($res["WORDS"]) <> '') && ($res["PATTERN_CREATE"] == "TRNSL")):
				$pattern = CFilterUnquotableWords::CreatePattern(trim($res["WORDS"]), $DICTIONARY_ID_T);
				if ($pattern && CFilterUnquotableWords::Update($ID, array("PATTERN"=>$pattern)))
					return true;
			endif;
		endif;
		return false;
	}

	function GenPatternAll($DICTIONARY_ID_W=0, $DICTIONARY_ID_T=0)
	{
		$DICTIONARY_ID_W = intval($DICTIONARY_ID_W);
		$DICTIONARY_ID_T = intval($DICTIONARY_ID_T);
		if (!$DICTIONARY_ID_W)
			$DICTIONARY_ID_W = (COption::GetOptionString("forum", "FILTER_DICT_W", '', LANGUAGE_ID));
		if (!$DICTIONARY_ID_T)
			$DICTIONARY_ID_T = (COption::GetOptionString("forum", "FILTER_DICT_T", '', LANGUAGE_ID));
		if ($DICTIONARY_ID_W):
			$db_res = CFilterUnquotableWords::GetList(array(), array("DICTIONARY_ID"=>$DICTIONARY_ID_W));
			while ($res = $db_res->Fetch()):
				if ((trim($res["WORDS"]) <> '') && ($res["PATTERN_CREATE"] == "TRNSL")):
					$pattern = CFilterUnquotableWords::CreatePattern(trim($res["WORDS"]), $DICTIONARY_ID_T);
					if ($pattern)
						CFilterUnquotableWords::Update($res["ID"], array("PATTERN"=>$pattern));
				endif;
			endwhile;
			return true;
		endif;
		return false;
	}

	function CreatePattern($pattern="", $DICTIONARY_ID=0)
	{
		$res = "";
		$NotWord = "\s.,;:!?\#\-\*\|\[\]\(\)";
		$word_separator = "[".$NotWord."]";
		$pattern = mb_strtolower(trim($pattern));
		$DICTIONARY_ID = intval($DICTIONARY_ID);

		if ($pattern == '')
			return false;

		if ($DICTIONARY_ID == 0)
			$DICTIONARY_ID = (COption::GetOptionString("forum", "FILTER_DICT_T", '', LANGUAGE_ID));
		elseif ($DICTIONARY_ID < 0)
			$DICTIONARY_ID = 0;

		$lettPatt = array();
		$lettersPatt = array();

		$letters = CFilterLetter::GetList(array(), array("DICTIONARY_ID"=>$DICTIONARY_ID));
		while ($lett = $letters->Fetch())
		{
			$space = false;
			$arrRes = array();
			$arrRepl = array();

			$arrRepl = explode(",", $lett["REPLACEMENT"]);
			// create letters.
			for ($ii = 0; $ii < count($arrRepl); $ii++)
			{
				$arrRepl[$ii] = trim($arrRepl[$ii]);
				if (mb_strlen($lett["LETTER"]) == 1)
				{
					if (mb_strlen($arrRepl[$ii]) == 1)
						$arrRes[$ii] = $arrRepl[$ii]."+";
					elseif (mb_substr($arrRepl[$ii], 0, 1) == "(" && (mb_substr($arrRepl[$ii], -1, 1) == ")" || mb_substr($arrRepl[$ii], -2, 1) == ")"))
					{
						if (mb_substr($arrRepl[$ii], -1, 1) == ")")
							$arrRes[$ii] = $arrRepl[$ii]."+";
						else
							$arrRes[$ii] = $arrRepl[$ii];
					}
					elseif (mb_strlen($arrRepl[$ii]) > 1)
						$arrRes[$ii] = "[".$arrRepl[$ii]."]+";
					else
						$space = true;
				}
				else
				{
					if ($arrRepl[$ii] <> '')
						$arrRes[$ii] = $arrRepl[$ii];
				}
			}

			if (mb_strlen($lett["LETTER"]) == 1)
			{
				if ($space)
					$arrRes[] = "";
//					$lettPatt[$lett["LETTER"]] = str_replace("+", "*", $lettPatt[$lett["LETTER"]]);
				$lettPatt[$lett["LETTER"]] = implode("|", $arrRes);
			}
			else
			{
				$lettersPatt["/".preg_quote($lett["LETTER"])."/is".BX_UTF_PCRE_MODIFIER] = "(".implode("|", $arrRes).")";
			}
		}
		// letters
		foreach ($lettersPatt as $key => $val)
			$pattern = preg_replace($key.BX_UTF_PCRE_MODIFIER, $val, $pattern);
		for ($ii = 0; $ii < mb_strlen($pattern); $ii++)
		{
			$sPattern = mb_substr($pattern, $ii, 1);
			if (is_set($lettPatt, mb_substr($pattern, $ii, 1)))
				$res .= "(".$lettPatt[mb_substr($pattern, $ii, 1)].")";
			else
			{
				$ord = ord(mb_substr($pattern, $ii, 1));
				if ((48>$ord) || ((64>$ord) and ($ord>57)) || ((97>$ord) and ($ord>90)) || ((127>$ord) and ($ord>122)))
				{
					if ($ord == 42)
						$res .= "[^".$NotWord."]*";
					elseif ($ord == 43)
						$res .= "[^".$NotWord."]+";
					elseif ($ord == 63)
						$res .= ".?";
					else
						$res .= mb_substr($pattern, $ii, 1);
				}
				else
					$res .= mb_substr($pattern, $ii, 1)."+";
			}
			$res .= $separator;
		}
		$res = "/(?<=".$word_separator.")(".$res.")(?=".$word_separator.")/is".BX_UTF_PCRE_MODIFIER;
		return $res;
	}


	function FilterPerm()
	{
		return CForumUser::IsAdmin();
	}

	function Filter($message)
	{
		global $USER, $DB, $CACHE_MANAGER, $APPLICATION;
		static $arFilterPattern = array();
		$filter = array();
		$pattern = array();
		$replacement = array();

		if (!array_key_exists(LANGUAGE_ID, $arFilterPattern))
		{
			$cache_id = "b_forum_filter_".COption::GetOptionInt("forum", "FILTER_DICT_W", false, LANGUAGE_ID);
			if (CACHED_b_forum_filter !== false && $CACHE_MANAGER->Read(CACHED_b_forum_filter, $cache_id, "b_forum_filter"))
			{
				$arFilterPattern[LANGUAGE_ID] = $CACHE_MANAGER->Get($cache_id);
			}
			else
			{
				$db_res = CFilterUnquotableWords::GetList(array(),
					array("USE_IT"=>"Y", "DICTIONARY_ID"=>COption::GetOptionInt("forum", "FILTER_DICT_W", false, LANGUAGE_ID)));
				$replace = COption::GetOptionString("forum", "FILTER_RPL", "*");
				while ($res = $db_res->Fetch())
				{
					if (trim($res["PATTERN"]) <> '' )
					{
						$arFilterPattern[LANGUAGE_ID]["pattern"][] = trim($res["PATTERN"]);
						$arFilterPattern[LANGUAGE_ID]["replacement"][] = $res["REPLACEMENT"] <> '' ? " ".$res["REPLACEMENT"]." " : " ".$replace." ";
					}
				}
			}
		}

		if (empty($arFilterPattern[LANGUAGE_ID]["pattern"])):
			return $message;
		endif;

		$pattern = $arFilterPattern[LANGUAGE_ID]["pattern"];
		$replacement = $arFilterPattern[LANGUAGE_ID]["replacement"];

		ksort($pattern); ksort($replacement);
		$message = '  '.$message.'  ';
		switch (COption::GetOptionString("forum", "FILTER_ACTION", "rpl"))
		{
			case "rpl":
				$message = preg_replace($pattern, $replacement, $message);
				break;
			case "del":
				$message = preg_replace($pattern, '', $message);
				break;
		}
		return trim($message);
	}
}
?>
