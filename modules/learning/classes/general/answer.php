<?

class CLAnswer
{
	function CheckFields(&$arFields, $ID = false)
	{
		global $DB;
		$arMsg = Array();

		if ( (is_set($arFields, "ANSWER") || $ID === false) && trim($arFields["ANSWER"]) == '')
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage("LEARNING_BAD_NAME"));


		if (
			($ID === false && !is_set($arFields, "QUESTION_ID"))
			||
			(is_set($arFields, "QUESTION_ID") && intval($arFields["QUESTION_ID"]) < 1)
			)
		{
			$arMsg[] = array("id"=>"QUESTION_ID", "text"=> GetMessage("LEARNING_BAD_QUESTION_ID"));
		}
		elseif (is_set($arFields, "QUESTION_ID"))
		{
			$res = CLQuestion::GetByID($arFields["QUESTION_ID"]);
			if(!$arRes = $res->Fetch())
				$arMsg[] = array("id"=>"QUESTION_ID", "text"=> GetMessage("LEARNING_BAD_QUESTION_ID"));
		}

		if(!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

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

			$ID = $DB->Add("b_learn_answer", $arFields, Array("ANSWER", "FEEDBACK", "MATCH_ANSWER"), "learning");

			return $ID;
		}

		return false;
	}


	function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;


		if ($this->CheckFields($arFields, $ID))
		{
			unset($arFields["ID"]);

			$arBinds=Array(
				"ANSWER" => $arFields["ANSWER"],
				"FEEDBACK" => $arFields["FEEDBACK"],
				"MATCH_ANSWER" => $arFields["MATCH_ANSWER"],
			);

			$strUpdate = $DB->PrepareUpdate("b_learn_answer", $arFields, "learning");
			$strSql = "UPDATE b_learn_answer SET ".$strUpdate." WHERE ID=".$ID;
			$DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			return true;
		}
		return false;
	}


	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		$strSql = "DELETE FROM b_learn_answer WHERE ID = ".$ID;

		if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		return true;
	}


	public static function GetByID($ID)
	{
		return CLAnswer::GetList($arOrder=Array(), $arFilter=Array("ID" => $ID));
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
				case "QUESTION_ID":
					$arSqlSearch[] = CLearnHelper::FilterCreate("CA.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "ANSWER":
					$arSqlSearch[] = CLearnHelper::FilterCreate("CA.".$key, $val, "string", $bFullJoin, $cOperationType);
					break;

				case "CORRECT":
					$arSqlSearch[] = CLearnHelper::FilterCreate("CA.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;
			}

		}

		return $arSqlSearch;
	}


	public static function GetList($arOrder=Array(), $arFilter=Array())
	{
		global $DB, $USER;

		$arSqlSearch = CLAnswer::GetFilter($arFilter);

		$strSqlSearch = "";
		for($i=0; $i<count($arSqlSearch); $i++)
			if($arSqlSearch[$i] <> '')
				$strSqlSearch .= " AND ".$arSqlSearch[$i]." ";

		$strSql =
		"SELECT CA.*, CQ.ID AS QUESTION_ID, CQ.NAME AS QUESTION_NAME ".
		"FROM b_learn_answer CA ".
		"INNER JOIN b_learn_question CQ ON CA.QUESTION_ID = CQ.ID ".
		"WHERE 1=1 ".
		$strSqlSearch;

		if (!is_array($arOrder))
			$arOrder = Array();

		$arSqlOrder = [];
		foreach($arOrder as $by=>$order)
		{
			$by = mb_strtolower($by);
			$order = mb_strtolower($order);
			if ($order!="asc")
				$order = "desc";

			if ($by == "id") $arSqlOrder[] = " CA.ID ".$order." ";
			elseif ($by == "sort") $arSqlOrder[] = " CA.SORT ".$order." ";
			elseif ($by == "correct") $arSqlOrder[] = " CA.CORRECT ".$order." ";
			elseif ($by == "answer") $arSqlOrder[] = " CA.ANSWER ".$order." ";
			elseif ($by == "rand") $arSqlOrder[] = CTest::GetRandFunction();
			else
			{
				$arSqlOrder[] = " CA.ID ".$order." ";
				$by = "id";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		for ($i=0; $i<count($arSqlOrder); $i++)
		{
			if($i==0)
				$strSqlOrder = " ORDER BY ";
			else
				$strSqlOrder .= ",";

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;

		//echo $strSql;

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}


	function GetStats($ID)
	{
		global $DB;

		$ID = intval($ID);
		$strSql = "SELECT COUNT(*) AS ALL_CNT, SUM(CASE WHEN CORRECT = 'Y' THEN 1 ELSE 0 END) AS CORRECT_CNT FROM b_learn_test_result WHERE ANSWERED = 'Y' AND QUESTION_ID = ".$ID;
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


	public static function getMultiStats($arIds)
	{
		global $DB;

		if ( ! is_array($arIds) )
			return (false);

		$arResult = array();

		$arIds = array_filter($arIds);

		if ( ! empty($arIds) )
		{
			$arIds = array_map('intval', $arIds);

			$strSql = "SELECT QUESTION_ID, COUNT(*) AS ALL_CNT, SUM(CASE WHEN CORRECT = 'Y' THEN 1 ELSE 0 END) AS CORRECT_CNT 
				FROM b_learn_test_result 
				WHERE ANSWERED = 'Y' AND QUESTION_ID IN (" . implode(',', $arIds) . ")
				GROUP BY QUESTION_ID ";

			$rsStat = $DB->Query($strSql, false, "File: " . __FILE__ . "<br>Line: " . __LINE__);
			while ($arStat = $rsStat->fetch())
			{
				$arResult[$arStat['QUESTION_ID']] = array(
					'ALL_CNT'     => (int) $arStat['ALL_CNT'],
					'CORRECT_CNT' => (int) $arStat['CORRECT_CNT']
				);
			}
		}

		return ($arResult);
	}
}
