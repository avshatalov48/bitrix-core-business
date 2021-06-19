<?php

class CAllTest
{
	public function CheckFields(&$arFields, $ID = false)
	{
		global $DB;
		$arMsg = array();

		if ( (is_set($arFields, "NAME") || $ID === false) && $arFields["NAME"] == '')
		{
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage("LEARNING_BAD_NAME"));
		}

		if ($ID===false && !is_set($arFields, "COURSE_ID"))
			$arMsg[] = array("id"=>"COURSE_ID", "text"=> GetMessage("LEARNING_BAD_COURSE_ID"));

		if (is_set($arFields, "COURSE_ID"))
		{
			$r = CCourse::GetByID($arFields["COURSE_ID"]);
			if(!$r->Fetch())
				$arMsg[] = array("id"=>"COURSE_ID", "text"=> GetMessage("LEARNING_BAD_COURSE_ID_EX"));
		}

		if ( $arFields["APPROVED"] == "Y" &&
			is_set($arFields, "COMPLETED_SCORE") &&
			(intval($arFields["COMPLETED_SCORE"]) <= 0 || intval($arFields["COMPLETED_SCORE"]) > 100)
		)
			$arMsg[] = array("id"=>"COMPLETED_SCORE", "text"=> GetMessage("LEARNING_BAD_COMPLETED_SCORE"));

		if (is_set($arFields, "PREVIOUS_TEST_ID") && intval($arFields["PREVIOUS_TEST_ID"]) != 0)
		{
			$r = CTest::GetByID($arFields["PREVIOUS_TEST_ID"]);
			if(!$r->Fetch())
				$arMsg[] = array("id"=>"PREVIOUS_TEST_ID", "text"=> GetMessage("LEARNING_BAD_PREVIOUS_TEST"));
		}

		if ( is_set($arFields, "PREVIOUS_TEST_SCORE") &&
			(intval($arFields["PREVIOUS_TEST_SCORE"]) <= 0 || intval($arFields["PREVIOUS_TEST_SCORE"]) > 100) &&
			intval($arFields["PREVIOUS_TEST_ID"]) != 0
		)
			$arMsg[] = array("id"=>"PREVIOUS_TEST_SCORE", "text"=> GetMessage("LEARNING_BAD_COMPLETED_SCORE"));

		if(!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		//Defaults
		if (is_set($arFields, "QUESTIONS_FROM") && !in_array($arFields["QUESTIONS_FROM"], array("A", "C", "L", "H", "S", 'R')))
			$arFields["QUESTIONS_FROM"] = "A";

		if (is_set($arFields, "QUESTIONS_AMOUNT") && intval($arFields["QUESTIONS_AMOUNT"]) <= 0)
			$arFields["QUESTIONS_AMOUNT"] = "0";

		if (is_set($arFields, "QUESTIONS_FROM_ID") && intval($arFields["QUESTIONS_FROM_ID"]) <= 0)
			$arFields["QUESTIONS_FROM_ID"] = "0";

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";

		if (is_set($arFields, "APPROVED") && $arFields["APPROVED"] != "Y")
			$arFields["APPROVED"] = "N";

		if($arFields["APPROVED"] == "N")
			$arFields["COMPLETED_SCORE"] = "";

		if (is_set($arFields, "INCLUDE_SELF_TEST") && $arFields["INCLUDE_SELF_TEST"] != "Y")
			$arFields["INCLUDE_SELF_TEST"] = "N";

		if (is_set($arFields, "RANDOM_QUESTIONS") && $arFields["RANDOM_QUESTIONS"] != "Y")
			$arFields["RANDOM_QUESTIONS"] = "N";

		if (is_set($arFields, "RANDOM_ANSWERS") && $arFields["RANDOM_ANSWERS"] != "Y")
			$arFields["RANDOM_ANSWERS"] = "N";

		if (is_set($arFields, "DESCRIPTION_TYPE") && $arFields["DESCRIPTION_TYPE"] != "html")
			$arFields["DESCRIPTION_TYPE"] = "text";

		if (is_set($arFields, "PASSAGE_TYPE") && !in_array($arFields["PASSAGE_TYPE"], Array("0", "1", "2")))
			$arFields["PASSAGE_TYPE"] = "0";

		if (is_set($arFields, "INCORRECT_CONTROL") && $arFields["INCORRECT_CONTROL"] != "Y")
			$arFields["INCORRECT_CONTROL"] = "N";

		if (is_set($arFields, "SHOW_ERRORS") && $arFields["SHOW_ERRORS"] != "Y")
		{
			$arFields["SHOW_ERRORS"] = "N";
			$arFields["NEXT_QUESTION_ON_ERROR"] = "Y";
		}

		if (is_set($arFields, "NEXT_QUESTION_ON_ERROR") && $arFields["NEXT_QUESTION_ON_ERROR"] != "Y")
			$arFields["NEXT_QUESTION_ON_ERROR"] = "N";

		return true;
	}

	public function Add($arFields)
	{
		global $DB;

		if($this->CheckFields($arFields))
		{
			unset($arFields["ID"]);

			CLearnHelper::FireEvent('OnBeforeTestAdd', $arFields);

			$ID = $DB->Add("b_learn_test", $arFields, Array("DESCRIPTION"), "learning");

			$arFields['ID'] = $ID;
			CLearnHelper::FireEvent('OnAfterTestAdd', $arFields);

			return $ID;
		}

		return false;
	}

	public function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		if ($this->CheckFields($arFields, $ID))
		{
			unset($arFields["ID"]);

			$arBinds = array(
				"DESCRIPTION" => $arFields["DESCRIPTION"]
			);

			foreach(GetModuleEvents('learning', 'OnBeforeTestUpdate', true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($arFields, $ID));

			$strUpdate = $DB->PrepareUpdate("b_learn_test", $arFields, "learning");
			$strSql = "UPDATE b_learn_test SET ".$strUpdate." WHERE ID=".$ID;
			$DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			foreach(GetModuleEvents('learning', 'OnAfterTestUpdate', true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($arFields, $ID));

			return true;
		}

		return false;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		CLearnHelper::FireEvent('OnBeforeTestDelete', $ID);

		//Gradebook
		$records = CGradeBook::GetList(Array(), Array("TEST_ID" => $ID));
		while($arRecord = $records->Fetch())
		{
			if(!CGradeBook::Delete($arRecord["ID"]))
				return false;
		}

		//Attempts
		$attempts = CTestAttempt::GetList(Array(), Array("TEST_ID" => $ID));
		while($arAttempt = $attempts->Fetch())
		{
			if(!CTestAttempt::Delete($arAttempt["ID"]))
				return false;
		}

		//Marks
		$marks = CLTestMark::GetList(Array(), Array("TEST_ID" => $ID));
		while($arMark = $marks->Fetch())
		{
			if(!CLTestMark::Delete($arMark["ID"]))
				return false;
		}

		//Previous tests
		$previousTests = CTest::GetList([], ["PREVIOUS_TEST_ID" => $ID]);
		while ($previousTest = $previousTests->Fetch())
		{
			$test = new CTest;
			$test->Update($previousTest["ID"], ["PREVIOUS_TEST_ID" => 0]);
		}

		$strSql = "DELETE FROM b_learn_test WHERE ID = ".$ID;

		if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		CEventLog::add(array(
			'AUDIT_TYPE_ID' => 'LEARNING_REMOVE_ITEM',
			'MODULE_ID'     => 'learning',
			'ITEM_ID'       => 'T #' . $ID,
			'DESCRIPTION'   => 'test removed'
		));

		CLearnHelper::FireEvent('OnAfterTestDelete', $ID);

		return true;
	}


	public static function GetFilter($arFilter)
	{
		if (!is_array($arFilter))
			$arFilter = Array();

		$arSqlSearch = Array();

		foreach ($arFilter as $key => $val)
		{
			$res = CLearnHelper::MkOperationFilter($key);
			$key = $res["FIELD"];
			$cOperationType = $res["OPERATION"];

			$key = mb_strtoupper($key);

			switch ($key)
			{
				case "ID":
				case "SORT":
				case "COURSE_ID":
				case "ATTEMPT_LIMIT":
				case "TIME_LIMIT":
				case "PREVIOUS_TEST_ID":
					$arSqlSearch[] = CLearnHelper::FilterCreate("LT.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "NAME":
				case "DESCRIPTION":
					$arSqlSearch[] = CLearnHelper::FilterCreate("LT.".$key, $val, "string", $bFullJoin, $cOperationType);
					break;

				case "ACTIVE":
				case "APPROVED":
				case "INCLUDE_SELF_TEST":
				case "RANDOM_ANSWERS":
				case "RANDOM_QUESTIONS":
				case "QUESTIONS_FROM":
				case "QUESTIONS_FROM_ID":
				case "PASSAGE_TYPE":
					$arSqlSearch[] = CLearnHelper::FilterCreate("LT.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;
			}

		}

		return $arSqlSearch;
	}


	public static function GetByID($ID)
	{
		return CTest::GetList($arOrder=Array(), $arFilter=Array("ID" => $ID));
	}


	public static function GetCount($arFilter = Array())
	{
		global $DB, $USER, $APPLICATION;

		if (!is_array($arFilter))
			$arFilter = Array();

		$oPermParser = new CLearnParsePermissionsFromFilter ($arFilter);

		$arSqlSearch = array_filter(CTest::GetFilter($arFilter));

		$strSqlSearch = "";

		if ( ! empty($arSqlSearch) )
			$strSqlSearch .= ' AND ' . implode(' AND ', $arSqlSearch) . ' ';

		$strSql = 
			"SELECT COUNT(*) as CNT 
			FROM b_learn_test LT 
			INNER JOIN b_learn_course C 
				ON LT.COURSE_ID = C.ID
			WHERE 1=1";

		if ($oPermParser->IsNeedCheckPerm())
			$strSql .= " AND C.LINKED_LESSON_ID IN (" . $oPermParser->SQLForAccessibleLessons() . ") ";

		$strSql .= $strSqlSearch;

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($ar = $res->Fetch())
			return intval($ar["CNT"]);
		else
			return 0;
	}


	public static function isPrevPassed($ID, $SCORE)
	{
		global $DB, $USER;
		$ID = intval($ID);
		$SCORE = intval($SCORE);
		$strSql = "
			SELECT * 
			FROM b_learn_gradebook 
			WHERE 
				STUDENT_ID = ".$USER->GetID()." AND 
				COMPLETED=\"Y\" AND 
				TEST_ID = ".$ID." AND 
				1.0*RESULT/MAX_RESULT*100 >= ".$SCORE
		;

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		if ($res->Fetch())
			return true;
		else
			return false;
	}


	public static function GetStats($ID)
	{
		global $DB;

		$ID = intval($ID);
		$strSql = "SELECT COUNT(*) AS ALL_CNT, SUM(CASE WHEN COMPLETED = 'Y' THEN 1 ELSE 0 END) AS CORRECT_CNT FROM b_learn_attempt WHERE STATUS = 'F' AND TEST_ID = ".$ID;
		$rsStat = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if ($arStat = $rsStat->GetNext())
		{
			return array("ALL_CNT" => intval($arStat["ALL_CNT"]), "CORRECT_CNT" => intval($arStat["CORRECT_CNT"]));
		}
		else
		{
			return array("ALL_CNT" => 0, "CORRECT_CNT" => 0);
		}
	}


	public static function GetList($arOrder = array(), $arFilter = array(), $arNavParams = array())
	{
		global $DB, $USER;

		if (!is_array($arFilter))
			$arFilter = Array();

		$oPermParser = new CLearnParsePermissionsFromFilter ($arFilter);
		$arSqlSearch = CTest::GetFilter($arFilter);

		// Remove empty strings from array
		$arSqlSearch = array_filter($arSqlSearch);

		if ($oPermParser->IsNeedCheckPerm())
			$arSqlSearch[] = " C.LINKED_LESSON_ID IN (" . $oPermParser->SQLForAccessibleLessons() . ") ";

		$strSqlSearch = ' ';
		if ( ! empty($arSqlSearch) )
		{
			$strSqlSearch = ' WHERE ';
			$strSqlSearch .= implode(' AND ', $arSqlSearch);
		}

		$strSqlFrom = "FROM b_learn_test LT ".
			"INNER JOIN b_learn_course C ON LT.COURSE_ID = C.ID "
			. $strSqlSearch;

		$strSql =
			"SELECT LT.ID, LT.COURSE_ID, LT.SORT, LT.ACTIVE, LT.NAME, 
				LT.DESCRIPTION, LT.DESCRIPTION_TYPE, LT.ATTEMPT_LIMIT, 
				LT.TIME_LIMIT, LT.COMPLETED_SCORE, LT.QUESTIONS_FROM, 
				LT.QUESTIONS_FROM_ID, LT.QUESTIONS_AMOUNT, LT.RANDOM_QUESTIONS, 
				LT.RANDOM_ANSWERS, LT.APPROVED, LT.INCLUDE_SELF_TEST, 
				LT.PASSAGE_TYPE, LT.PREVIOUS_TEST_ID, LT.PREVIOUS_TEST_SCORE, 
				LT.INCORRECT_CONTROL, LT.CURRENT_INDICATION, 
				LT.FINAL_INDICATION, LT.MIN_TIME_BETWEEN_ATTEMPTS, 
				LT.SHOW_ERRORS, LT.NEXT_QUESTION_ON_ERROR, ".
			$DB->DateToCharFunction("LT.TIMESTAMP_X")." as TIMESTAMP_X "
			. $strSqlFrom;

		if (!is_array($arOrder))
			$arOrder = Array();

		foreach($arOrder as $by=>$order)
		{
			$by = mb_strtolower($by);
			$order = mb_strtolower($order);

			if ($order!="asc")
				$order = "desc";

			if ($by == "id")
				$arSqlOrder[] = " LT.ID ".$order." ";
			elseif ($by == "name")
				$arSqlOrder[] = " LT.NAME ".$order." ";
			elseif ($by == "active")
				$arSqlOrder[] = " LT.ACTIVE ".$order." ";
			elseif ($by == "sort")
				$arSqlOrder[] = " LT.SORT ".$order." ";
			else
			{
				$arSqlOrder[] = " LT.TIMESTAMP_X ".$order." ";
				$by = "timestamp_x";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);

		if ( ! empty($arSqlOrder) )
			$strSqlOrder .= ' ORDER BY ' . implode(', ', $arSqlOrder) . ' ';

		$strSql .= $strSqlOrder;

		if (is_array($arNavParams) && ( ! empty($arNavParams) ) )
		{
			if (isset($arNavParams['nTopCount']) && ((int) $arNavParams['nTopCount'] > 0))
			{
				$strSql = $DB->TopSql($strSql, (int) $arNavParams['nTopCount']);
				$res = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
			}
			else
			{
				$res_cnt = $DB->Query("SELECT COUNT(LT.ID) as C " . $strSqlFrom);
				$res_cnt = $res_cnt->fetch();
				$res = new CDBResult();
				$res->NavQuery($strSql, $res_cnt['C'], $arNavParams);
			}
		}
		else
			$res = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

		return $res;
	}
}
