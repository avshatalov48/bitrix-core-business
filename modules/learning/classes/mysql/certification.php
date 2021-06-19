<?php

/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage tasks
 * @copyright 2001-2014 Bitrix
 */

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/classes/general/certification.php");

class CCertification extends CAllCertification
{
	public static function GetList($arOrder = array(), $arFilter = array(), $arNavParams = array())
	{
		global $DB;

		$oPermParser = new CLearnParsePermissionsFromFilter ($arFilter);
		$arSqlSearch = CCertification::GetFilter($arFilter);

		$strSqlSearch = "";
		if ( ! empty($arSqlSearch) )
		{
			$arSqlSearch = array_filter($arSqlSearch);

			if ( ! empty($arSqlSearch) )
				$strSqlSearch .= ' AND ' . implode(' AND ', $arSqlSearch);
		}

		$strSql =
		"SELECT CER.*, C.NAME as COURSE_NAME, COURSEOLD.ID as COURSE_ID, "
		. "COURSEOLD.ACTIVE_FROM as ACTIVE_FROM, COURSEOLD.ACTIVE_TO as ACTIVE_TO, COURSEOLD.RATING as RATING, "
		. "COURSEOLD.RATING_TYPE as RATING_TYPE, COURSEOLD.SCORM as SCORM, "
		. $DB->Concat("'('",'U.LOGIN',"') '","CASE WHEN U.NAME IS NULL THEN '' ELSE U.NAME END","' '", "CASE WHEN U.LAST_NAME IS NULL THEN '' ELSE U.LAST_NAME END")." as USER_NAME, U.ID as USER_ID, ".
		$DB->DateToCharFunction("CER.TIMESTAMP_X")." as TIMESTAMP_X, ".
		$DB->DateToCharFunction("CER.DATE_CREATE")." as DATE_CREATE ";

		$strSqlFrom = "FROM b_learn_certification CER ".
			"INNER JOIN b_learn_course COURSEOLD ON CER.COURSE_ID = COURSEOLD.ID ".
			"INNER JOIN b_learn_lesson C ON C.ID = COURSEOLD.LINKED_LESSON_ID ".
			"INNER JOIN b_user U ON U.ID = CER.STUDENT_ID ".
			"WHERE 1=1 ";

		if ($oPermParser->IsNeedCheckPerm())
			$strSqlFrom .= " AND C.ID IN (" . $oPermParser->SQLForAccessibleLessons() . ") ";

		$strSqlFrom .= $strSqlSearch;

		if (!is_array($arOrder))
			$arOrder = Array();

		foreach($arOrder as $by=>$order)
		{
			$by = mb_strtolower($by);
			$order = mb_strtolower($order);
			if ($order!="asc")
				$order = "desc";

			if ($by == "id")
				$arSqlOrder[] = " CER.ID ".$order." ";
			elseif ($by == "student_id")
				$arSqlOrder[] = " CER.STUDENT_ID ".$order." ";
			elseif ($by == "course_id")
				$arSqlOrder[] = " CER.COURSE_ID ".$order." ";
			elseif ($by == "summary")
				$arSqlOrder[] = " CER.SUMMARY ".$order." ";
			elseif ($by == "sort")
				$arSqlOrder[] = " CER.SORT ".$order." ";
			elseif ($by == "active")
				$arSqlOrder[] = " CER.ACTIVE ".$order." ";
			elseif ($by == "from_online")
				$arSqlOrder[] = " CER.FROM_ONLINE ".$order." ";
			elseif ($by == "public")
				$arSqlOrder[] = " CER.PUBLIC ".$order." ";
			elseif ($by == "public_profile")
				$arSqlOrder[] = " CER.PUBLIC ".$order." ";
			elseif ($by == "date_create")
				$arSqlOrder[] = " CER.DATE_CREATE ".$order." ";
			elseif ($by == "summary")
				$arSqlOrder[] = " CER.SUMMARY ".$order." ";
			elseif ($by == "max_summary")
				$arSqlOrder[] = " CER.MAX_SUMMARY ".$order." ";
			elseif ($by == "timestamp_x")
				$arSqlOrder[] = " CER.TIMESTAMP_X ".$order." ";
			else
				$arSqlOrder[] = " CER.ID ".$order." ";
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		if ( ! empty($arSqlOrder) )
			$strSqlOrder .= " ORDER BY " . implode(', ', $arSqlOrder);

		$strSql .= $strSqlFrom . $strSqlOrder;

		if (is_array($arNavParams) && ( ! empty($arNavParams) ) )
		{
			if (isset($arNavParams['nTopCount']) && ((int) $arNavParams['nTopCount'] > 0))
			{
				$strSql = $DB->TopSql($strSql, (int) $arNavParams['nTopCount']);
				$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			}
			else
			{
				$res_cnt = $DB->Query("SELECT COUNT(CER.ID) as CNT " . $strSqlFrom, false, "File: ".__FILE__."<br>Line: ".__LINE__);
				$res_cnt = $res_cnt->fetch();
				$res = new CDBResult();
				$rc = $res->NavQuery($strSql, $res_cnt['CNT'], $arNavParams, true);
				if ($rc === false)
					throw new LearnException ('EA_SQLERROR', LearnException::EXC_ERR_ALL_GIVEUP);
			}
		}
		else
			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		return ($res);
	}
}
