<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/subscribe/classes/general/posting.php");

class CPosting extends CPostingGeneral
{
	function GetList($aSort=Array(), $arFilter=Array(), $arSelect=Array(), $arNavStartParams=false)
	{
		global $DB;

		static $arSelectFields = false;
		if (!$arSelectFields)
		{
			$arSelectFields = array(
				"STATUS_TITLE" => "if(P.STATUS='S','".$DB->ForSql(GetMessage("POST_STATUS_SENT"))."',
				if(P.STATUS='P','".$DB->ForSql(GetMessage("POST_STATUS_PART"))."',
				if(P.STATUS='E','".$DB->ForSql(GetMessage("POST_STATUS_ERROR"))."',
				if(P.STATUS='W','".$DB->ForSql(GetMessage("POST_STATUS_WAIT"))."',
				'".$DB->ForSql(GetMessage("POST_STATUS_DRAFT"))."'))))",
				"ID" => "P.ID",
				"STATUS" => "P.STATUS",
				"FROM_FIELD" => "P.FROM_FIELD",
				"TO_FIELD" => "P.TO_FIELD",
				"EMAIL_FILTER" => "P.EMAIL_FILTER",
				"SUBJECT" => "P.SUBJECT",
				"BODY_TYPE" => "P.BODY_TYPE",
				"DIRECT_SEND" => "P.DIRECT_SEND",
				"CHARSET" => "P.CHARSET",
				"MSG_CHARSET" => "P.MSG_CHARSET",
				"SUBSCR_FORMAT" => "P.SUBSCR_FORMAT",
				"TIMESTAMP_X" => $DB->DateToCharFunction("P.TIMESTAMP_X"),
				"DATE_SENT" => $DB->DateToCharFunction("P.DATE_SENT"),
			);
		}

		$this->LAST_ERROR = "";
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			foreach($arFilter as $key=>$val)
			{
				if (!is_array($val) && ((string)$val == '' || $val=="NOT_REF"))
					continue;

				switch(strtoupper($key))
				{
					case "MSG_CHARSET":
						$arSqlSearch[] = "P.MSG_CHARSET = '".$DB->ForSql($val)."'";
						break;
					case "ID":
						$arSqlSearch[] = GetFilterQuery("P.ID", $val, "N");
						break;
					case "TIMESTAMP_1":
						if($DB->IsDate($val))
						{
							$arSqlSearch[] = "P.TIMESTAMP_X>=".$DB->CharToDateFunction($val, "SHORT");
						}
						else
						{
							$this->LAST_ERROR .= GetMessage("POST_WRONG_TIMESTAMP_FROM")."<br>";
						}
						break;
					case "TIMESTAMP_2":
						if($DB->IsDate($val))
						{
							$arSqlSearch[] = "P.TIMESTAMP_X<DATE_ADD(".$DB->CharToDateFunction($val, "SHORT").",INTERVAL 1 DAY)";
						}
						else
						{
							$this->LAST_ERROR .= GetMessage("POST_WRONG_TIMESTAMP_TILL")."<br>";
						}
						break;
					case "DATE_SENT_1":
						if($DB->IsDate($val))
						{
							$arSqlSearch[] = "P.DATE_SENT>=".$DB->CharToDateFunction($val, "SHORT");
						}
						else
						{
							$this->LAST_ERROR .= GetMessage("POST_WRONG_DATE_SENT_FROM")."<br>";
						}
						break;
					case "DATE_SENT_2":
						if($DB->IsDate($val))
						{
							$arSqlSearch[] = "P.DATE_SENT<DATE_ADD(".$DB->CharToDateFunction($val, "SHORT").",INTERVAL 1 DAY)";
						}
						else
						{
							$this->LAST_ERROR .= GetMessage("POST_WRONG_DATE_SENT_TILL")."<br>";
						}
						break;
					case "STATUS":
						$arSqlSearch[] = GetFilterQuery(array("P.STATUS", $arSelectFields["STATUS_TITLE"]), $val, "Y", array(), "N", "N");
						break;
					case "STATUS_ID":
						$arSqlSearch[] = GetFilterQuery("P.STATUS", $val, "N");
						break;
					case "SUBJECT":
						$arSqlSearch[] = GetFilterQuery("P.SUBJECT", $val);
						break;
					case "FROM":
						$arSqlSearch[] = GetFilterQuery("P.FROM_FIELD", $val, "Y", array("@", "_", "."));
						break;
					case "TO":
						$r = GetFilterQuery("PE.EMAIL", $val, "Y", array("@", "_", "."));
						if($r <> '')
						{
							$arSqlSearch[] = "EXISTS (SELECT * FROM b_posting_email PE WHERE PE.POSTING_ID=P.ID AND PE.STATUS='N' AND ".$r.")";
						}
						break;
					case "BODY_TYPE":
						$arSqlSearch[] = ($val == "html")? "P.BODY_TYPE='html'" : "P.BODY_TYPE='text'";
						break;
					case "RUB_ID":
						if(is_array($val) && count($val) > 0)
						{
							$rub_id = array();
							foreach($val as $i => $v)
							{
								$v = intval($v);
								if($v > 0)
								{
									$rub_id[$v] = $v;
								}
							}
							if(count($rub_id))
							{
								$arSqlSearch[] = "EXISTS (SELECT * from b_posting_rubric PR WHERE PR.POSTING_ID = P.ID AND PR.LIST_RUBRIC_ID in (".implode(", ", $rub_id)."))";
							}
						}
						break;
					case "BODY":
						$arSqlSearch[] = GetFilterQuery("P.BODY", $val);
						break;
					case "AUTO_SEND_TIME_1":
						if($DB->IsDate($val, false, false, "FULL"))
						{
							$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME>=".$DB->CharToDateFunction($val, "FULL")." )";
						}
						elseif($DB->IsDate($val, false, false, "SHORT"))
						{
							$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME>=".$DB->CharToDateFunction($val, "SHORT")." )";
						}
						else
						{
							$this->LAST_ERROR .= GetMessage("POST_WRONG_AUTO_FROM")."<br>";
						}
						break;
					case "AUTO_SEND_TIME_2":
						if($DB->IsDate($val, false, false, "FULL"))
						{
							$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME<=".$DB->CharToDateFunction($val, "FULL")." )";
						}
						elseif($DB->IsDate($val, false, false, "SHORT"))
						{
							$arSqlSearch[] = "(P.AUTO_SEND_TIME is not null and P.AUTO_SEND_TIME<=".$DB->CharToDateFunction($val, "SHORT")." )";
						}
						else
						{
							$this->LAST_ERROR .= GetMessage("POST_WRONG_AUTO_TILL")."<br>";
						}
						break;
				}
			}
		}

		$arOrder = array();
		foreach($aSort as $key => $ord)
		{
			$key = mb_strtoupper($key);
			$ord = (mb_strtoupper($ord) <> "ASC"? "DESC": "ASC");
			switch($key)
			{
				case "ID": $arOrder[$key] = "P.ID ".$ord; break;
				case "TIMESTAMP": $arOrder[$key] = "P.TIMESTAMP_X ".$ord; break;
				case "SUBJECT": $arOrder[$key] = "P.SUBJECT ".$ord; break;
				case "BODY_TYPE": $arOrder[$key] = "P.BODY_TYPE ".$ord; break;
				case "STATUS": $arOrder[$key] = "P.STATUS ".$ord; break;
				case "DATE_SENT": $arOrder[$key] = "P.DATE_SENT ".$ord; break;
				case "AUTO_SEND_TIME": $arOrder[$key] = "P.AUTO_SEND_TIME ".$ord; break;
				case "FROM_FIELD": $arOrder[$key] = "P.FROM_FIELD ".$ord; break;
				case "TO_FIELD": $arOrder[$key] = "P.TO_FIELD ".$ord; break;
			}
		}
		if(count($arOrder) <= 0)
		{
			$arOrder["ID"] = "P.ID DESC";
		}
		$strSqlOrder = " ORDER BY ".implode(", ", $arOrder);

		if (!is_array($arSelect) || empty($arSelect))
		{
			$arSelect = array_keys($arSelectFields);
		}

		$arSqlSelect = array();
		foreach ($arSelect as $selectField)
		{
			if (isset($arSelectFields[$selectField]))
			{
				$arSqlSelect[$selectField] = $arSelectFields[$selectField]." as ".$selectField;
			}
		}
		if (!$arSqlSelect)
		{
			$arSqlSelect["ID"] = $arSelectFields["ID"]." as ID";
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT ".implode(", ", $arSqlSelect)."
			FROM b_posting P
			WHERE
			".$strSqlSearch."
		".$strSqlOrder;

		if (is_array($arNavStartParams))
		{
			$nTopCount = (isset($arNavStartParams['nTopCount']) ? (int)$arNavStartParams['nTopCount'] : 0);
			if ($nTopCount > 0)
			{
				$res = $DB->Query($DB->TopSql(
					$strSql,
					$nTopCount
				));
			}
			else
			{
				$res_cnt = $DB->Query("
					SELECT COUNT(P.ID) as C
					FROM b_posting P
					WHERE
					".$strSqlSearch."
				");
				$res_cnt = $res_cnt->Fetch();
				$res = new CDBResult();
				$res->NavQuery($strSql, $res_cnt["C"], $arNavStartParams);
			}
		}
		else
		{
			$res = $DB->Query($strSql);
		}

		$res->is_filtered = (IsFiltered($strSqlSearch));

		return $res;
	}

	public static function Lock($ID=0)
	{
		global $DB, $APPLICATION;
		$ID = intval($ID);
		$uniq = $APPLICATION->GetServerUniqID();
		$db_lock = $DB->Query("SELECT GET_LOCK('".$uniq."_post_".$ID."', 0) as L", false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$ar_lock = $db_lock->Fetch();
		if($ar_lock["L"]=="1")
			return true;
		else
			return false;
	}

	public static function UnLock($ID=0)
	{
		global $DB;
		$ID = intval($ID);
		$uniq = COption::GetOptionString("main", "server_uniq_id", "");
		if($uniq <> '')
		{
			$db_lock = $DB->Query("SELECT RELEASE_LOCK('".$uniq."_post_".$ID."') as L", false, "File: ".__FILE__."<br>Line: ".__LINE__);
			$ar_lock = $db_lock->Fetch();
			if($ar_lock["L"]=="0")
				return false;
			else
				return true;
		}
	}
}
?>