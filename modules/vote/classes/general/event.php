<?
#############################################
# Bitrix Site Manager Forum					#
# Copyright (c) 2002-2009 Bitrix			#
# http://www.bitrixsoft.com					#
# mailto:admin@bitrixsoft.com				#
#############################################

class CAllVoteEvent
{
	function err_mess()
	{
		$module_id = "vote";
		return "<br>Module: ".$module_id."<br>Class: CAllVoteEvent<br>File: ".__FILE__;
	}

	function GetByID($ID)
	{
		$ID = intval($ID);
		if ($ID<=0) return;
		$res = CVoteEvent::GetList($by, $order, array("ID" => $ID), $is_filtered, "Y");
		return $res;
	}

	function GetAnswer($EVENT_ID, $ANSWER_ID)
	{
		$err_mess = (self::err_mess())."<br>Function: GetAnswer<br>Line: ";
		global $DB;
		$EVENT_ID = intval($EVENT_ID);
		$ANSWER_ID = intval($ANSWER_ID);
		$strSql = "
			SELECT
				A.ANSWER_ID,
				A.MESSAGE
			FROM
				b_vote_event E,
				b_vote_event_answer A,
				b_vote_event_question Q				
			WHERE
				E.ID = '$EVENT_ID'
			and Q.EVENT_ID = E.ID
			and A.EVENT_QUESTION_ID = Q.ID
			and	A.ANSWER_ID = '$ANSWER_ID'
			";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		if ($zr = $z->Fetch())
		{
			if (strlen($zr["MESSAGE"])>0) return $zr["MESSAGE"]; else return $zr["ANSWER_ID"];
		}
		return false;
	}

	public static function Delete($eventId)
	{
		return \Bitrix\Vote\Event::deleteEvent($eventId);
	}

	function SetValid($eventId, $valid)
	{
		return \Bitrix\Vote\Event::setValid($eventId, $valid);
	}

	function GetList(&$by, &$order, $arFilter=Array(), &$is_filtered, $get_user="N")
	{
		$err_mess = (self::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB;
		$arSqlSearch = Array();
		$strSqlSearch = "";
		if (is_array($arFilter))
		{
			$filter_keys = array_keys($arFilter);
			$count = count($filter_keys);
			for ($i=0; $i<$count; $i++)
			{
				$key = $filter_keys[$i];
				$val = $arFilter[$filter_keys[$i]];
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				else
				{
					if( (strlen($val) <= 0) || ($val === "NOT_REF") )
						continue;
				}
				$match_value_set = (in_array($key."_EXACT_MATCH", $filter_keys)) ? true : false;
				$key = strtoupper($key);
				switch($key)
				{
					case "ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("E.ID",$val,$match);
						break;
					case "VALID":
						$arSqlSearch[] = ($val=="Y") ? "E.VALID='Y'" : "E.VALID='N'";
						break;
					case "DATE_1":
						$arSqlSearch[] = "E.DATE_VOTE>=".$DB->CharToDateFunction($val, "SHORT");
						break;
					case "DATE_2":
						$arSqlSearch[] = "E.DATE_VOTE<=".$DB->CharToDateFunction($val." 23:59:59", "FULL");
						break;
					case "VOTE_USER":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("E.VOTE_USER_ID",$val,$match);
						break;
					case "USER_ID":
						if ($get_user=="Y")
						{
							$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
							$arSqlSearch[] = GetFilterQuery("U.AUTH_USER_ID",$val,$match);
						}
						break;
					case "SESSION":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.STAT_SESSION_ID",$val,$match);
						break;
					case "IP":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.IP",$val,$match,array("."));
						break;
					case "VOTE":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("E.VOTE_ID, V.TITLE",$val,$match);
						break;
					case "VOTE_ID":
						$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("E.VOTE_ID",$val,$match);
						break;
				}
			}
		}

		if ($by == "s_id")					$strSqlOrder = "ORDER BY E.ID";
		elseif ($by == "s_valid")			$strSqlOrder = "ORDER BY E.VALID";
		elseif ($by == "s_date")			$strSqlOrder = "ORDER BY E.DATE_VOTE";
		elseif ($by == "s_session")			$strSqlOrder = "ORDER BY E.STAT_SESSION_ID";
		elseif ($by == "s_vote_user")		$strSqlOrder = "ORDER BY E.VOTE_USER_ID";
		elseif ($by == "s_vote")			$strSqlOrder = "ORDER BY E.VOTE_ID";
		elseif ($by == "s_ip")				$strSqlOrder = "ORDER BY E.IP";
		else 
		{
			$by = "s_id";
			$strSqlOrder = "ORDER BY E.ID";
		}
		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
			$order="desc";
		}
		if ($get_user=="Y")
		{
			$select = " ,
				U.AUTH_USER_ID, U.STAT_GUEST_ID,
				A.NAME, A.LAST_NAME, A.SECOND_NAME, A.PERSONAL_PHOTO, A.LOGIN, 
				".$DB->Concat("A.LAST_NAME", "' '", "A.NAME")."	AUTH_USER_NAME
			";
			$from = "
			LEFT JOIN b_vote_user U ON (U.ID = E.VOTE_USER_ID)
			LEFT JOIN b_user A ON (A.ID = U.AUTH_USER_ID)
			";

		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT 
				E.*,
				".$DB->DateToCharFunction("E.DATE_VOTE")."	DATE_VOTE,
				V.TITLE, V.DESCRIPTION, V.DESCRIPTION_TYPE
				$select
			FROM
				b_vote_event E
			INNER JOIN b_vote V ON (V.ID=E.VOTE_ID)
			$from
			WHERE
			$strSqlSearch
			$strSqlOrder
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$is_filtered = (IsFiltered($strSqlSearch));
		return $res;
	}
}
?>
