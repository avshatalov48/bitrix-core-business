<?php

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/learning/classes/general/attempt.php");

class CTestAttempt extends CAllTestAttempt
{
	public static function DoInsert($arInsert, $arFields)
	{
		global $DB;

		if ($arInsert[0] == '')		// BUG ?
			return false;

		if (!isset($arFields["DATE_START"]))
		{
			$arFields['~DATE_START'] =  $DB->CurrentTimeFunction();
		}

		return $DB->Add('b_learn_attempt', $arFields);
	}


	final protected static function _GetListSQLFormer ($sSelect, $obUserFieldsSql, $bCheckPerm, $USER, $arFilter, $strSqlSearch, &$strSqlFrom)
	{
		$oPermParser = new CLearnParsePermissionsFromFilter ($arFilter);

		$strSqlFrom = "FROM b_learn_attempt A ".
		"INNER JOIN b_learn_test T ON A.TEST_ID = T.ID ".
		"INNER JOIN b_user U ON U.ID = A.STUDENT_ID ".
		"LEFT JOIN b_learn_course C ON C.ID = T.COURSE_ID ".
		"LEFT JOIN b_learn_test_mark TM ON A.TEST_ID = TM.TEST_ID ".
		$obUserFieldsSql->GetJoin("A.ID") .
		" WHERE 
			(TM.SCORE IS NULL 
			OR TM.SCORE = 
				(SELECT MIN(SCORE) 
					FROM b_learn_test_mark 
					WHERE SCORE >= 
						CASE WHEN A.STATUS = 'F' AND A.MAX_SCORE > 0
							THEN 1.0*A.SCORE/A.MAX_SCORE*100 
							ELSE 0 
						END 
						AND TEST_ID = A.TEST_ID
				)
			) ";

		if ($oPermParser->IsNeedCheckPerm())
			$strSqlFrom .= " AND C.LINKED_LESSON_ID IN (" . $oPermParser->SQLForAccessibleLessons() . ") ";

		$strSqlFrom .= $strSqlSearch;

		$strSql =
		"SELECT DISTINCT ".
		$sSelect." ".
		$obUserFieldsSql->GetSelect()." ". $strSqlFrom;

		return ($strSql);
	}
}
