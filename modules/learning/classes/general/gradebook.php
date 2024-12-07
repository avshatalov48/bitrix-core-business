<?php

class CAllGradeBook
{
	public static function LessonIdByGradeBookId ($certId)
	{
		$rc = CGradeBook::GetByID($certId);
		if ($rc === false)
			throw new LearnException('', LearnException::EXC_ERR_ALL_GIVEUP);

		$row = $rc->Fetch();

		if ( ! isset($row['LINKED_LESSON_ID']) )
			throw new LearnException('', LearnException::EXC_ERR_ALL_GIVEUP);

		return ( (int) $row['LINKED_LESSON_ID'] );
	}

	public static function CheckFields(&$arFields, $ID = false)
	{
		global $DB, $APPLICATION;

		if ($ID===false && !is_set($arFields, "STUDENT_ID"))
		{
			$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_USER_ID"), "EMPTY_STUDENT_ID");
			return false;
		}
		elseif (is_set($arFields, "STUDENT_ID"))
		{
			$dbResult = CUser::GetByID($arFields["STUDENT_ID"]);
			if (!$dbResult->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_USER_ID_EX"), "ERROR_NO_STUDENT_ID");
				return false;
			}
		}

		if ($ID===false && !is_set($arFields, "TEST_ID"))
		{
			$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_ID"), "EMPTY_TEST_ID");
			return false;
		}
		elseif (is_set($arFields, "TEST_ID"))
		{
			$r = CTest::GetByID($arFields["TEST_ID"]);
			if(!$r->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_TEST_ID_EX"), "ERROR_NO_TEST_ID");
				return false;
			}
		}

		if (is_set($arFields, "STUDENT_ID") && is_set($arFields, "TEST_ID"))
		{
			$res = CGradeBook::GetList(Array(), Array("STUDENT_ID" => $arFields["STUDENT_ID"], "TEST_ID" => $arFields["TEST_ID"]));
			if ($res->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_GRADEBOOK_DUPLICATE"), "ERROR_GRADEBOOK_DUPLICATE");
				return false;
			}
		}


		if (is_set($arFields, "COMPLETED") && $arFields["COMPLETED"] != "Y")
			$arFields["COMPLETED"] = "N";

		return true;
	}

	public static function Add($arFields)
	{
		global $DB;

		if(CGradeBook::CheckFields($arFields))
		{
			unset($arFields["ID"]);

			$ID = $DB->Add("b_learn_gradebook", $arFields, Array(), "learning");

			return $ID;
		}

		return false;
	}

	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		if (CGradeBook::CheckFields($arFields, $ID))
		{
			unset($arFields["ID"]);
			unset($arFields["STUDENT_ID"]);
			unset($arFields["TEST_ID"]);

			$arBinds=Array();

			$strUpdate = $DB->PrepareUpdate("b_learn_gradebook", $arFields, "learning");
			$strSql = "UPDATE b_learn_gradebook SET ".$strUpdate." WHERE ID=".$ID;
			$DB->QueryBind($strSql, $arBinds);

			return true;
		}

		return false;
	}

	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		$strSql = "SELECT TEST_ID, STUDENT_ID FROM b_learn_gradebook WHERE ID = ".$ID;
		$res = $DB->Query($strSql);
		if (!$arGBook = $res->Fetch())
			return false;

		$attempts = CTestAttempt::GetList(Array(), Array("TEST_ID" => $arGBook["TEST_ID"], "STUDENT_ID" => $arGBook["STUDENT_ID"]));
		while($arAttempt = $attempts->Fetch())
		{
			if(!CTestAttempt::Delete($arAttempt["ID"]))
				return false;
		}

		$strSql = "DELETE FROM b_learn_gradebook WHERE ID = ".$ID;

		if (!$DB->Query($strSql))
			return false;

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
				case "STUDENT_ID":
				case "TEST_ID":
				case "RESULT":
				case "MAX_RESULT":
					$arSqlSearch[] = CLearnHelper::FilterCreate("G.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;
				case "COMPLETED":
					$arSqlSearch[] = CLearnHelper::FilterCreate("G.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;
				case "USER":
					$arSqlSearch[] = GetFilterQuery("U.ID, U.LOGIN, U.NAME, U.LAST_NAME",$val);
					break;
				case "USER_LOGIN":
					$arSqlSearch[] = CLearnHelper::FilterCreate("U.LOGIN", $val, "string_equal", $bFullJoin, $cOperationType);
					break;

				case "USER_NAME":
					$arSqlSearch[] = CLearnHelper::FilterCreate("U.NAME", $val, "string_equal", $bFullJoin, $cOperationType);
					break;

				case "USER_LAST_NAME":
					$arSqlSearch[] = CLearnHelper::FilterCreate("U.LAST_NAME", $val, "string_equal", $bFullJoin, $cOperationType);
					break;
			}

		}

		return $arSqlSearch;
	}

	public static function GetByID($ID)
	{
		return CGradeBook::GetList(Array(), Array("ID"=>$ID));
	}

	public static function RecountAttempts($STUDENT_ID,$TEST_ID)
	{
		global $DB;

		$STUDENT_ID = intval($STUDENT_ID);
		$TEST_ID = intval($TEST_ID);

		if ($TEST_ID < 1 || $STUDENT_ID < 1)
			return false;

		$strSql = "SELECT ID FROM b_learn_gradebook G WHERE STUDENT_ID = '".$STUDENT_ID."' AND TEST_ID = '".$TEST_ID."' ";
		$res = $DB->Query($strSql);
		if (!$arG = $res->Fetch())
		{

			$ID = CGradeBook::Add(Array(
					"STUDENT_ID" => $STUDENT_ID,
					"TEST_ID" => $TEST_ID,
					"RESULT" => "0",
					"MAX_RESULT" => "0",
					"COMPLETED" => "N"
				));

			return ($ID > 0);
		}

		$strSql = "SELECT SCORE, MAX_SCORE, COMPLETED ".
					"FROM b_learn_attempt ".
					"WHERE STUDENT_ID = '".$STUDENT_ID."' AND TEST_ID = '".$TEST_ID."' ".
					"ORDER BY COMPLETED DESC, SCORE DESC ";

		$res = $DB->Query($strSql);
		$res->NavStart();

		if (intval($res->SelectedRowsCount()) == 0)
		{
			$strSql = "DELETE FROM b_learn_gradebook WHERE ID = ".$arG["ID"];

			if (!$DB->Query($strSql))
				return false;

			return true;
		}

		if (!$ar = $res->Fetch())
			return false;

		$strSql = "UPDATE b_learn_gradebook SET ATTEMPTS = '".intval($res->SelectedRowsCount())."', COMPLETED = '".$ar["COMPLETED"]."', RESULT = '".intval($ar["SCORE"])."' , MAX_RESULT = '".intval($ar["MAX_SCORE"])."' WHERE STUDENT_ID = '".$STUDENT_ID."' AND TEST_ID = '".$TEST_ID."' ";
		if (!$DB->Query($strSql))
			return false;

		return true;
	}

	public static function GetExtraAttempts($STUDENT_ID, $TEST_ID)
	{
		global $DB;

		$STUDENT_ID = intval($STUDENT_ID);
		$TEST_ID = intval($TEST_ID);

		$strSql = "SELECT EXTRA_ATTEMPTS FROM b_learn_gradebook WHERE STUDENT_ID = ".$STUDENT_ID." AND TEST_ID = ".$TEST_ID."";
		$rs = $DB->Query($strSql);
		if (!$ar = $rs->Fetch())
		{
			return 0;
		}
		else
		{
			return $ar["EXTRA_ATTEMPTS"];
		}
	}

	public static function AddExtraAttempts($STUDENT_ID, $TEST_ID, $COUNT = 1)
	{
		global $DB;

		$STUDENT_ID = intval($STUDENT_ID);
		$TEST_ID = intval($TEST_ID);
		$COUNT = intval($COUNT);

		$strSql = "SELECT ID, EXTRA_ATTEMPTS FROM b_learn_gradebook WHERE STUDENT_ID = ".$STUDENT_ID." AND TEST_ID = ".$TEST_ID."";
		$rs = $DB->Query($strSql);
		if ( ! ($ar = $rs->Fetch()) )
		{
			$ID = CGradeBook::Add(Array(
					"STUDENT_ID" => $STUDENT_ID,
					"TEST_ID" => $TEST_ID,
					"RESULT" => "0",
					"MAX_RESULT" => "0",
					"COMPLETED" => "N",
					"EXTRA_ATTEMPTS" => $COUNT
			));

			return ($ID > 0);
		}
		else
		{
			$strSql = "UPDATE b_learn_gradebook SET EXTRA_ATTEMPTS = ".($ar["EXTRA_ATTEMPTS"] + $COUNT)." WHERE ID = ".$ar["ID"];
			if (!$DB->Query($strSql))
				return false;
		}
	}

	public static function GetList($arOrder = array(), $arFilter = array(), $arNavParams = array())
	{
		global $DB;

		$oPermParser = new CLearnParsePermissionsFromFilter ($arFilter);
		$arSqlSearch = array_filter(CGradeBook::GetFilter($arFilter));

		$strSqlSearch = '';
		if ( ! empty($arSqlSearch) )
			$strSqlSearch .= implode(' AND ', $arSqlSearch);

		//Sites
		$SqlSearchLang = "''";
		if (array_key_exists("SITE_ID", $arFilter))
		{
			$arLID = Array();

			if(is_array($arFilter["SITE_ID"]))
				$arLID = $arFilter["SITE_ID"];
			else
			{
				if ($arFilter["SITE_ID"] <> '')
					$arLID[] = $arFilter["SITE_ID"];
			}

			foreach($arLID as $v)
				$SqlSearchLang .= ", '".$DB->ForSql($v, 2)."'";
		}

		$strSqlFrom = static::__getSqlFromClause($SqlSearchLang);

		if ($oPermParser->IsNeedCheckPerm())
			$strSqlFrom .= " AND TUL.ID IN (" . $oPermParser->SQLForAccessibleLessons() . ") ";

		if ($strSqlSearch !== '')
			$strSqlFrom .= ' AND ' . $strSqlSearch;

		$strSql =
		"SELECT G.*, T.NAME as TEST_NAME, T.COURSE_ID as COURSE_ID,
		T.APPROVED as TEST_APPROVED,
		(T.ATTEMPT_LIMIT + G.EXTRA_ATTEMPTS) AS ATTEMPT_LIMIT, TUL.NAME as COURSE_NAME,
		C.LINKED_LESSON_ID AS LINKED_LESSON_ID, ".
		$DB->Concat("'('",'U.LOGIN',"') '","CASE WHEN U.NAME IS NULL THEN '' ELSE U.NAME END","' '", "CASE WHEN U.LAST_NAME IS NULL THEN '' ELSE U.LAST_NAME END")." as USER_NAME, U.ID as USER_ID ".
		$strSqlFrom;

		if (!is_array($arOrder))
			$arOrder = array();

		$arSqlOrder = [];

		foreach($arOrder as $by=>$order)
		{
			$by = mb_strtolower($by);
			$order = mb_strtolower($order);
			if ($order!="asc")
				$order = "desc";

			if ($by == "id")
				$arSqlOrder[] = " G.ID ".$order." ";
			elseif ($by == "student_id")
				$arSqlOrder[] = " G.STUDENT_ID ".$order." ";
			elseif ($by == "test_id")
				$arSqlOrder[] = " G.TEST_ID ".$order." ";
			elseif ($by == "completed")
				$arSqlOrder[] = " G.COMPLETED ".$order." ";
			elseif ($by == "result")
				$arSqlOrder[] = " G.RESULT ".$order." ";
			elseif ($by == "max_result")
				$arSqlOrder[] = " G.MAX_RESULT ".$order." ";
			elseif ($by == "user_name")
				$arSqlOrder[] = " USER_NAME ".$order." ";
			elseif ($by == "test_name")
				$arSqlOrder[] = " TEST_NAME ".$order." ";
			else
				$arSqlOrder[] = " G.ID ".$order." ";
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);

		if (!empty($arSqlOrder))
		{
			for ($i=0, $len = count($arSqlOrder); $i < $len; $i++)
			{
				if($i==0)
					$strSqlOrder = " ORDER BY ";
				else
					$strSqlOrder .= ",";

				$strSqlOrder .= $arSqlOrder[$i];
			}
		}

		$strSql .= $strSqlOrder;

		if (is_array($arNavParams) && ( ! empty($arNavParams) ) )
		{
			if (isset($arNavParams['nTopCount']) && ((int) $arNavParams['nTopCount'] > 0))
			{
				$strSql = $DB->TopSql($strSql, (int) $arNavParams['nTopCount']);
				$res = $DB->Query($strSql);
			}
			else
			{
				$res_cnt = $DB->Query("SELECT COUNT(G.ID) as C " . $strSqlFrom);
				$res_cnt = $res_cnt->fetch();
				$res = new CDBResult();
				$res->NavQuery($strSql, $res_cnt['C'], $arNavParams);
			}
		}
		else
			$res = $DB->Query($strSql);

		return $DB->Query($strSql);
	}

	/**
	 * This function is for internal use only.
	 * It can be changed without any notification.
	 * This is for MSSQL/Oracle. MySQL version of SQL-code redefined in ../mysql/gradebook.php
	 *
	 * @access private
	 */
	protected static function __getSqlFromClause($SqlSearchLang)
	{
		$strSqlFrom =
			"FROM b_learn_gradebook G ".
			"INNER JOIN b_learn_test T ON G.TEST_ID = T.ID ".
			"INNER JOIN b_user U ON U.ID = G.STUDENT_ID ".
			"LEFT JOIN b_learn_course C ON C.ID = T.COURSE_ID ".
			"LEFT JOIN b_learn_lesson TUL ON TUL.ID = C.LINKED_LESSON_ID ".
			"LEFT JOIN b_learn_test_mark TM ON G.TEST_ID = TM.TEST_ID ".
			"WHERE (TM.SCORE IS NULL
				OR TM.SCORE =
					(
					SELECT MIN(SCORE) AS SCORE
					FROM b_learn_test_mark
					WHERE
						TEST_ID = G.TEST_ID
							AND
						SCORE >=
							CASE WHEN G.MAX_RESULT > 0
							THEN
								(G.RESULT/G.MAX_RESULT*100)
							ELSE
								0
							END
					)
				) ".
			(mb_strlen($SqlSearchLang) <= 2?"":
				"AND
					EXISTS
					(	SELECT 'x' FROM b_learn_course_site CS
						WHERE C.ID = CS.COURSE_ID AND CS.SITE_ID IN (".$SqlSearchLang.") ) "
			);

		return ($strSqlFrom);
	}
}
