<?php

class CTestResult
{
	function CheckFields(&$arFields, $ID = false)
	{
		global $DB, $APPLICATION;

		if ($ID===false)
		{
			if (is_set($arFields, "ATTEMPT_ID"))
			{
				$r = CTestAttempt::GetByID($arFields["ATTEMPT_ID"]);
				if(!$r->Fetch())
				{
					$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_ATTEMPT_ID_EX"), "ERROR_NO_ATTEMPT_ID");
					return false;
				}
			}
			else
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_ATTEMPT_ID"), "EMPTY_ATTEMPT_ID");
				return false;
			}

			if (is_set($arFields, "QUESTION_ID"))
			{
				$r = CLQuestion::GetByID($arFields["QUESTION_ID"]);
				if(!$r->Fetch())
				{
					$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_QUESTION_ID"), "EMPTY_QUESTION_ID");
					return false;
				}
			}
			else
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_QUESTION_ID"), "EMPTY_QUESTION_ID");
				return false;
			}
		}

		if (is_set($arFields, "RESPONSE") && is_array($arFields["RESPONSE"]))
		{
			$s = "";
			foreach($arFields["RESPONSE"] as $val)
				$s .= $val.",";
			$arFields["RESPONSE"] = substr($s,0,-1);
		}

		/*
		if (is_set($arFields, "ANSWERED") && is_set($arFields, "RESPONSE"))
		{
			if ($arFields["ANSWERED"]=="Y" && strlen($arFields["RESPONSE"]) <= 0)
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_NO_ANSWERS"), "EMPTY_ANSWERS");
				return false;
			}
		}
		*/

		if (is_set($arFields, "CORRECT") && $arFields["CORRECT"] != "Y")
			$arFields["CORRECT"] = "N";

		return true;
	}


	function Add($arFields)
	{
		global $DB;

		if($this->CheckFields($arFields))
		{
			unset($arFields["ID"]);

			$ID = $DB->Add("b_learn_test_result", $arFields, Array("RESPONSE"), "learning");

			return $ID;
		}

		return false;
	}


	function AddResponse($TEST_RESULT_ID, $RESPONSE)
	{
		global $DB;

		$TEST_RESULT_ID = intval($TEST_RESULT_ID);
		if ($TEST_RESULT_ID < 1) return false;

		$rsTestResult = CTestResult::GetList(Array(), Array("ID" => $TEST_RESULT_ID, 'CHECK_PERMISSIONS' => 'N'));

		if ($arTestResult = $rsTestResult->GetNext())
		{
			if ($arTestResult["QUESTION_TYPE"] == "T")
			{
				$arFields = Array(
					"ANSWERED" => "Y",
					"RESPONSE" => $RESPONSE,
					"POINT"=> 0,
					"CORRECT"=> "N",
				);
			}
			else
			{
				if (!is_array($RESPONSE))
					$RESPONSE = Array($RESPONSE);

				$strSql =
				"SELECT A.ID, Q.POINT ".
				"FROM b_learn_test_result TR ".
				"INNER JOIN b_learn_question Q ON TR.QUESTION_ID = Q.ID ".
				"INNER JOIN b_learn_answer A ON Q.ID = A.QUESTION_ID ".
				"WHERE TR.ID = '".$TEST_RESULT_ID."' ".
				($arTestResult["QUESTION_TYPE"] != "R" ? "AND A.CORRECT = 'Y' " : "").
				"ORDER BY A.SORT ASC, A.ID ASC";

				if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
					return false;

				$arAnswer = Array();
				while ($arRes = $res->Fetch())
				{
					$arAnswer[] = $arRes["ID"];
					$str_POINT = $arRes["POINT"];
				}

				if ($arTestResult["QUESTION_TYPE"] == "R")
				{
					if ($arAnswer != $RESPONSE)
						$str_POINT = "0";
				}
				else
				{
					$t1 = array_diff($arAnswer,$RESPONSE);
					$t2 = array_diff($RESPONSE,$arAnswer);
					if ($t1!=$t2 || $t2 != Array())
						$str_POINT = "0";
				}

				//echo "!".$str_POINT."!";

				$arFields = Array(
					"ANSWERED" => "Y",
					"RESPONSE" => $RESPONSE,
					"POINT"=> $str_POINT,
					"CORRECT"=> ($str_POINT == "0" ? "N" : "Y"),
				);
			}

			$tr = new CTestResult;
			if (!$res = $tr->Update($TEST_RESULT_ID, $arFields))
				return false;

			return $arFields;
		}
		else
		{
			return false;
		}
	}


	function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		if ($this->CheckFields($arFields, $ID))
		{
			unset($arFields["ID"]);
			unset($arFields["QUESTION_ID"]);
			unset($arFields["ATTEMPT_ID"]);

			$arBinds=Array(
				"RESPONSE"=>$arFields["RESPONSE"]
			);

			$strUpdate = $DB->PrepareUpdate("b_learn_test_result", $arFields, "learning");
			$strSql = "UPDATE b_learn_test_result SET ".$strUpdate." WHERE ID=".$ID;
			$DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			return true;
		}

		return false;
	}


	function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		$strSql = "DELETE FROM b_learn_test_result WHERE ID = ".$ID;

		if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		return true;
	}


	function GetList($arOrder=array(), $arFilter=array(), $arNavParams = array())
	{
		global $DB, $USER, $APPLICATION;

		if (!is_array($arFilter))
			$arFilter = Array();

		$oPermParser = new CLearnParsePermissionsFromFilter ($arFilter);
		$arSqlSearch = CTestResult::GetFilter($arFilter);

		// Remove empty strings from array
		$arSqlSearch = array_filter($arSqlSearch);

		if ($oPermParser->IsNeedCheckPerm())
			$arSqlSearch[] = " L.ID IN (" . $oPermParser->SQLForAccessibleLessons() . ") ";

		$strSqlSearch = ' ';
		if ( ! empty($arSqlSearch) )
		{
			$strSqlSearch = ' WHERE ';
			$strSqlSearch .= implode(' AND ', $arSqlSearch);
		}

		$strSqlFrom = "FROM b_learn_test_result TR 
			INNER JOIN b_learn_question Q ON TR.QUESTION_ID = Q.ID 
			INNER JOIN b_learn_lesson L ON Q.LESSON_ID = L.ID "
			. $strSqlSearch;

		$strSql = "SELECT TR.*, Q.QUESTION_TYPE, Q.NAME as QUESTION_NAME, 
			Q.POINT as QUESTION_POINT, Q.LESSON_ID "
			. $strSqlFrom;

		if (!is_array($arOrder))
			$arOrder = Array();

		foreach($arOrder as $by=>$order)
		{
			$by = strtolower($by);
			$order = strtolower($order);
			if ($order!="asc")
				$order = "desc";

			if ($by == "id")
				$arSqlOrder[] = " TR.ID ".$order." ";
			elseif ($by == "attempt_id")
				$arSqlOrder[] = " TR.ATTEMPT_ID ".$order." ";
			elseif ($by == "question_id")
				$arSqlOrder[] = " TR.QUESTION_ID ".$order." ";
			elseif ($by == "point")
				$arSqlOrder[] = " TR.POINT ".$order." ";
			elseif ($by == "correct")
				$arSqlOrder[] = " TR.CORRECT ".$order." ";
			elseif ($by == "answered")
				$arSqlOrder[] = " TR.ANSWERED ".$order." ";
			elseif ($by == "question_name")
				$arSqlOrder[] = " QUESTION_NAME ".$order." ";
			elseif ($by == "rand")
				$arSqlOrder[] = CTest::GetRandFunction();
			else
			{
				$arSqlOrder[] = " TR.ID ".$order." ";
				$by = "id";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$arSqlOrderCnt = count($arSqlOrder);
		for ($i=0; $i<$arSqlOrderCnt; $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

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
				$res_cnt = $DB->Query("SELECT COUNT(TR.ID) as C " . $strSqlFrom);
				$res_cnt = $res_cnt->fetch();
				$res = new CDBResult();
				$res->NavQuery($strSql, $res_cnt['C'], $arNavParams);
			}
		}
		else
			$res = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);

		return $res;
	}


	function GetByID($ID)
	{
		return CTestResult::GetList(Array(), Array("ID"=>$ID));
	}


	function GetFilter($arFilter)
	{
		if (!is_array($arFilter))
			$arFilter = Array();

		$arSqlSearch = Array();

		foreach ($arFilter as $key => $val)
		{
			$res = CLearnHelper::MkOperationFilter($key);
			$key = $res["FIELD"];
			$cOperationType = $res["OPERATION"];

			$key = strtoupper($key);

			switch ($key)
			{
				case "ID":
				case "ATTEMPT_ID":
				case "QUESTION_ID":
				case "POINT":
					$arSqlSearch[] = CLearnHelper::FilterCreate("TR.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "RESPONSE":
					$arSqlSearch[] = CLearnHelper::FilterCreate("TR.".$key, $val, "string", $bFullJoin, $cOperationType);
					break;

				case "QUESTION_NAME":
					$arSqlSearch[] = CLearnHelper::FilterCreate("Q.NAME", $val, "string", $bFullJoin, $cOperationType);
					break;

				case "ANSWERED":
				case "CORRECT":
					$arSqlSearch[] = CLearnHelper::FilterCreate("TR.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;
			}
		}

		return $arSqlSearch;
	}


	function OnTestResultChange($TEST_RESULT_ID)
	{
		global $DB;

		$TEST_RESULT_ID = intval($TEST_RESULT_ID);

		if ($TEST_RESULT_ID < 1)
			return false;

		$strSql =
		"SELECT TR.* ".
		"FROM b_learn_test_result TR ".
		"WHERE TR.ID = '".$TEST_RESULT_ID."'";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (!$arAttemptResult = $res->Fetch())
			return false;

		$strSql =
		"SELECT SUM(TR.POINT) as SUM_POINT, SUM( Q.POINT ) MAX_POINT ".
		"FROM b_learn_test_result TR ".
		"INNER JOIN b_learn_question Q ON TR.QUESTION_ID = Q.ID ".
		"WHERE TR.ATTEMPT_ID = '".$arAttemptResult["ATTEMPT_ID"]."'";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		if (!$arSum = $res->Fetch())
			return false;

		$strSql =
		"UPDATE b_learn_attempt SET SCORE = '".$arSum["SUM_POINT"]."', MAX_SCORE ='".$arSum["MAX_POINT"]."' ".
		"WHERE ID = '".$arAttemptResult["ATTEMPT_ID"]."'";

		if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		return CTestAttempt::OnAttemptChange($arAttemptResult["ATTEMPT_ID"]);
	}


	function GetProgress($ATTEMPT_ID)
	{
		global $DB;
		$ATTEMPT_ID = intval($ATTEMPT_ID);
		$res=array("DONE"=>0, "TODO"=>0);
		$strSql = "SELECT ANSWERED,COUNT(*) C ".
					"FROM b_learn_test_result ".
					"WHERE ATTEMPT_ID = ".$ATTEMPT_ID." ".
					"GROUP BY ANSWERED";
		$rs=$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		while($ar=$rs->Fetch())
		{
			if($ar["ANSWERED"]=="Y")
				$res["DONE"]=$ar["C"];
			elseif($ar["ANSWERED"]=="N")
				$res["TODO"]=$ar["C"];
		}
		return $res;
	}


	function GetCount($ATTEMPT_ID)
	{
		global $DB;

		$strSql =
		"SELECT COUNT(*) as C ".
		"FROM b_learn_test_result TR ".
		"WHERE TR.ATTEMPT_ID = '".intval($ATTEMPT_ID)."'";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
		$res_cnt = $res->Fetch();

		return intval($res_cnt["C"]);

		/*$strSql =
		"SELECT COUNT(*) as CNT, SUM(Q.POINT) MAX_SCORE ".
		"FROM b_learn_test_result TR ".
		"INNER JOIN b_learn_question Q ON TR.QUESTION_ID = Q.ID ".
		"WHERE TR.ATTEMPT_ID = '".intval($ATTEMPT_ID)."'";
		*/
	}


	function GetPercent($ATTEMPT_ID)
	{
		global $DB;

		$strSql =
		"SELECT ROUND(SUM(CASE WHEN TR.CORRECT = 'Y' THEN Q.POINT ELSE 0 END) * 100 / SUM(Q.POINT), 4) as PCNT ".
		"FROM b_learn_test_result TR, b_learn_question Q ".
		"WHERE TR.ATTEMPT_ID = '".intval($ATTEMPT_ID)."' AND TR.QUESTION_ID = Q.ID";

		if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		if (!$arStat = $res->Fetch())
			return false;

		// Round bottom in right way, some magic due to IEEE 754
		return ( (int) (floor($arStat["PCNT"] + 0.00001) + 0.00001) );
	}


	function GetCorrectCount($ATTEMPT_ID)
	{
		global $DB;

		$strSql = "SELECT SUM(CASE WHEN TR.CORRECT = 'Y' THEN 1 ELSE 0 END) AS CNT FROM b_learn_test_result TR WHERE TR.ATTEMPT_ID = ".intval($ATTEMPT_ID)." GROUP BY ATTEMPT_ID";

		if (!$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return 0;

		if (!$arStat = $res->Fetch())
			return 0;

		return $arStat["CNT"];
	}
}
