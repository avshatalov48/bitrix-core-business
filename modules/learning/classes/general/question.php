<?
// 2012-04-11 Checked/modified for compatibility with new data model
class CLQuestion
{
	function CheckFields(&$arFields, $ID = false)
	{
		global $DB, $USER;
		$arMsg = Array();

		if ( (is_set($arFields, "NAME") || $ID === false) && trim($arFields["NAME"]) == '')
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage("LEARNING_BAD_NAME"));


		if (is_set($arFields, "FILE_ID"))
		{
			$error = CFile::CheckImageFile($arFields["FILE_ID"]);
			if ($error <> '')
				$arMsg[] = array("id"=>"FILE_ID", "text"=> $error);
		}

		if(empty($this->LAST_ERROR))
		{
			if (
				($ID === false && !is_set($arFields, "LESSON_ID"))
				||
				(is_set($arFields, "LESSON_ID") && intval($arFields["LESSON_ID"]) < 1)
				)
			{
				$arMsg[] = array("id"=>"LESSON_ID", "text"=> GetMessage("LEARNING_BAD_LESSON_ID"));
			}
			elseif (is_set($arFields, "LESSON_ID"))
			{
				$res = CLearnLesson::GetByID($arFields["LESSON_ID"]);
				if($arRes = $res->Fetch())
				{
					$oAccess = CLearnAccess::GetInstance($USER->GetID());

					$bAccessLessonModify =
						$oAccess->IsBaseAccess(CLearnAccess::OP_LESSON_WRITE)
						|| $oAccess->IsLessonAccessible ($arFields["LESSON_ID"], CLearnAccess::OP_LESSON_WRITE);

					if ( ! $bAccessLessonModify )
						$arMsg[] = array("id"=>"LESSON_ID", "text"=> GetMessage("LEARNING_BAD_LESSON_ID_EX"));
				}
				else
				{
					$arMsg[] = array("id"=>"LESSON_ID", "text"=> GetMessage("LEARNING_BAD_LESSON_ID_EX"));
				}
			}
		}

		if(!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		if (is_set($arFields, "QUESTION_TYPE") && !in_array($arFields["QUESTION_TYPE"], Array("S", "M", "T", "R")))
			$arFields["QUESTION_TYPE"] = "S";

		if (is_set($arFields, "DESCRIPTION_TYPE") && $arFields["DESCRIPTION_TYPE"] != "html")
			$arFields["DESCRIPTION_TYPE"] = "text";

		if (is_set($arFields, "DIRECTION") && $arFields["DIRECTION"] != "H")
			$arFields["DIRECTION"] = "V";

		if (is_set($arFields, "SELF") && $arFields["SELF"] != "Y")
			$arFields["SELF"] = "N";

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";

		if (is_set($arFields, "EMAIL_ANSWER") && $arFields["EMAIL_ANSWER"] != "Y")
			$arFields["EMAIL_ANSWER"] = "N";

		if (is_set($arFields, "CORRECT_REQUIRED") && $arFields["CORRECT_REQUIRED"] != "Y")
			$arFields["CORRECT_REQUIRED"] = "N";

		return true;
	}


	function Add($arFields)
	{
		global $DB, $USER_FIELD_MANAGER;

		if (
			$this->CheckFields($arFields)
			&& $USER_FIELD_MANAGER->CheckFields('LEARNING_QUESTIONS', 0, $arFields)
		)
		{
			unset($arFields["ID"]);

			if (
				array_key_exists("FILE_ID", $arFields)
				&& is_array($arFields["FILE_ID"])
				&& (
					!array_key_exists("MODULE_ID", $arFields["FILE_ID"])
					|| $arFields["FILE_ID"]["MODULE_ID"] == ''
				)
			)
				$arFields["FILE_ID"]["MODULE_ID"] = "learning";

			CFile::SaveForDB($arFields, "FILE_ID", "learning");

			$ID = $DB->Add("b_learn_question", $arFields, array("DESCRIPTION", 'COMMENT_TEXT', 'INCORRECT_MESSAGE'));

			if ($ID)
				$USER_FIELD_MANAGER->Update('LEARNING_QUESTIONS', $ID, $arFields);

			foreach(GetModuleEvents('learning', 'OnAfterQuestionAdd', true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			return $ID;
		}

		return false;
	}


	function Update($ID, $arFields)
	{
		global $DB, $USER_FIELD_MANAGER;

		$ID = intval($ID);
		if ($ID < 1) return false;

		if (is_set($arFields, "FILE_ID"))
		{
			if($arFields["FILE_ID"]["name"] == '' && $arFields["FILE_ID"]["del"] == '' && $arFields["FILE_ID"]["description"] == '')
				unset($arFields["FILE_ID"]);
			else
			{
				$pic_res = $DB->Query("SELECT FILE_ID FROM b_learn_question WHERE ID=".$ID);
				if($pic_res = $pic_res->Fetch())
					$arFields["FILE_ID"]["old_file"]=$pic_res["FILE_ID"];
			}
		}

		if (
			$this->CheckFields($arFields, $ID)
			&& $USER_FIELD_MANAGER->CheckFields('LEARNING_QUESTIONS', $ID, $arFields)
		)
		{
			unset($arFields["ID"]);

			$arBinds=Array(
				"DESCRIPTION"       => $arFields["DESCRIPTION"] ?? '',
				'COMMENT_TEXT'      => $arFields['COMMENT_TEXT'] ?? '',
				'INCORRECT_MESSAGE' => $arFields['INCORRECT_MESSAGE'] ?? ''
			);

			if (
				array_key_exists("FILE_ID", $arFields)
				&& is_array($arFields["FILE_ID"])
				&& (
					!array_key_exists("MODULE_ID", $arFields["FILE_ID"])
					|| $arFields["FILE_ID"]["MODULE_ID"] == ''
				)
			)
				$arFields["FILE_ID"]["MODULE_ID"] = "learning";

			CFile::SaveForDB($arFields, "FILE_ID", "learning");

			$USER_FIELD_MANAGER->Update('LEARNING_QUESTIONS', $ID, $arFields);
			$strUpdate = $DB->PrepareUpdate("b_learn_question", $arFields);
			if ($strUpdate !== '')
			{
				$strSql = "UPDATE b_learn_question SET ".$strUpdate." WHERE ID=".$ID;
				$DB->QueryBind($strSql, $arBinds);
			}

			foreach(GetModuleEvents('learning', 'OnAfterQuestionUpdate', true) as $arEvent)
				ExecuteModuleEventEx($arEvent, array($ID, $arFields));

			return true;
		}
		return false;
	}


	public static function Delete($ID)
	{
		global $DB, $USER_FIELD_MANAGER;

		$ID = intval($ID);
		if ($ID < 1) return false;

		$strSql = "SELECT FILE_ID FROM b_learn_question WHERE ID = ".$ID;
		$r = $DB->Query($strSql);
		if (!$arQuestion = $r->Fetch())
			return false;

		$answers = CLAnswer::GetList(Array(), Array("QUESTION_ID" => $ID));
		while($arAnswer = $answers->Fetch())
		{
			if(!CLAnswer::Delete($arAnswer["ID"]))
				return false;
		}

		$arAttempts = Array();
		$strSql = "SELECT ATTEMPT_ID FROM b_learn_test_result WHERE QUESTION_ID = ".$ID;
		$res = $DB->Query($strSql);
		while($ar = $res->Fetch())
			$arAttempts[] = $ar["ATTEMPT_ID"]; //Attempts to recount

		//Results
		$strSql = "DELETE FROM b_learn_test_result WHERE QUESTION_ID = ".$ID;
		if (!$DB->Query($strSql))
			return false;

		foreach($arAttempts as $ATTEMPT_ID)
		{
			CTestAttempt::RecountQuestions($ATTEMPT_ID);
			CTestAttempt::OnAttemptChange($ATTEMPT_ID);
		}

		$strSql = "DELETE FROM b_learn_question WHERE ID = ".$ID;

		if (!$DB->Query($strSql))
			return false;

		$USER_FIELD_MANAGER->delete('LEARNING_QUESTIONS', $ID);

		CFile::Delete($arQuestion["FILE_ID"]);

		CEventLog::add(array(
			'AUDIT_TYPE_ID' => 'LEARNING_REMOVE_ITEM',
			'MODULE_ID'     => 'learning',
			'ITEM_ID'       => 'Q #' . $ID,
			'DESCRIPTION'   => 'question removed'
		));

		foreach(GetModuleEvents('learning', 'OnAfterQuestionDelete', true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array($ID, $arQuestion));

		return true;
	}


	public static function GetByID($ID)
	{
		return CLQuestion::GetList($arOrder=Array(), $arFilter=Array("ID" => $ID));
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
				case "LESSON_ID":
				case "POINT":
					$arSqlSearch[] = CLearnHelper::FilterCreate("CQ.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "COURSE_ID":
					// was:	$arSqlSearch[] = CLearnHelper::FilterCreate("C.".$key, $val, "number", $bFullJoin, $cOperationType);

					$courseLessonId = CCourse::CourseGetLinkedLesson ($val);
					if ($courseLessonId === false)
						break;	// it is not a course, so skipping

					// MySQL & MSSQL supports "WHERE IN(...)" clause for more than 10 000 elements

					// add to sql "WHERE" constraint: lessons id only from given array
					$sqlCourseLessonsIdsList = '';

					$oTree = CLearnLesson::GetTree($courseLessonId);
					$arChildLessonForCourse = $oTree->GetLessonsIdListInTree();

					// root lesson not in tree, so add it
					$arChildLessonForCourse[] = $courseLessonId;

					// We need escape data for SQL
					$arChildLessonForCourseEscaped = array_map('intval', $arChildLessonForCourse);

					$sqlCourseLessonsIdsList = implode (', ', $arChildLessonForCourseEscaped);

					if ($sqlCourseLessonsIdsList <> '')
						$arSqlSearch[] = 'CQ.LESSON_ID IN (' . $sqlCourseLessonsIdsList . ')';

					break;

				case "NAME":
					$arSqlSearch[] = CLearnHelper::FilterCreate("CQ.".$key, $val, "string", $bFullJoin, $cOperationType);
					break;

				case "QUESTION_TYPE":
				case "ACTIVE":
				case "SELF":
				case "CORRECT_REQUIRED":
					$arSqlSearch[] = CLearnHelper::FilterCreate("CQ.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;
			}

		}

		return $arSqlSearch;
	}


	public static function GetList($arOrder = array(), $arFilter = array(), $bHz = false, $arNavParams = array(), $arSelect = array())
	{
		global $DB, $USER, $USER_FIELD_MANAGER;

		$obUserFieldsSql = new CUserTypeSQL();
		$obUserFieldsSql->SetEntity('LEARNING_QUESTIONS', "CQ.ID");
		$obUserFieldsSql->SetSelect($arSelect);
		$obUserFieldsSql->SetFilter($arFilter);
		$obUserFieldsSql->SetOrder($arOrder);

		$arSqlSearch = array_filter(CLQuestion::GetFilter($arFilter));

		$strSqlFrom = "FROM b_learn_question CQ "
			. "INNER JOIN b_learn_lesson CL ON CQ.LESSON_ID = CL.ID "
			. $obUserFieldsSql->GetJoin("CQ.ID")
			. " WHERE ";

		$r = $obUserFieldsSql->GetFilter();
		if ($r <> '')
			$arSqlSearch[] = "(".$r.")";

		if ( ! empty($arSqlSearch) )
			$strSqlFrom .= implode(' AND ', $arSqlSearch);
		else
			$strSqlFrom .= ' 1=1 ';

		$strSql = "SELECT CQ.ID, CQ.ACTIVE, CQ.LESSON_ID, CQ.QUESTION_TYPE,
				CQ.NAME, CQ.SORT, CQ.DESCRIPTION, CQ.DESCRIPTION_TYPE,
				CQ.COMMENT_TEXT, CQ.FILE_ID, CQ.SELF, CQ.POINT, CQ.DIRECTION,
				CQ.CORRECT_REQUIRED, CQ.EMAIL_ANSWER, CQ.INCORRECT_MESSAGE,"
			. $DB->DateToCharFunction("CQ.TIMESTAMP_X")." as TIMESTAMP_X "
			. $obUserFieldsSql->GetSelect()
			. " "
			. $strSqlFrom;

		if (!is_array($arOrder))
			$arOrder = Array();

		$arSqlOrder = [];
		foreach($arOrder as $by=>$order)
		{
			$by = mb_strtolower($by);
			$order = mb_strtolower($order);
			if ($order!="asc")
				$order = "desc";

			if ($by == "id")
				$arSqlOrder[] = " CQ.ID ".$order." ";
			elseif ($by == "name")
				$arSqlOrder[] = " CQ.NAME ".$order." ";
			elseif ($by == "sort")
				$arSqlOrder[] = " CQ.SORT ".$order." ";
			elseif ($by == "point")
				$arSqlOrder[] = " CQ.POINT ".$order." ";
			elseif ($by == "type")
				$arSqlOrder[] = " CQ.QUESTION_TYPE ".$order." ";
			elseif ($by == "self")
				$arSqlOrder[] = " CQ.SELF ".$order." ";
			elseif ($by == "active")
				$arSqlOrder[] = " CQ.ACTIVE ".$order." ";
			elseif ($by == "correct_required")
				$arSqlOrder[] = " CQ.CORRECT_REQUIRED ".$order." ";
			elseif ($s = $obUserFieldsSql->getOrder($by))
				$arSqlOrder[] = ' ' . $s . ' ' . $order . ' ';
			else
				$arSqlOrder[] = " CQ.TIMESTAMP_X ".$order." ";
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		$cnt = count($arSqlOrder);
		for ($i=0; $i<$cnt; $i++)
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
				$res = $DB->Query($strSql);
			}
			else
			{
				$res_cnt = $DB->Query("SELECT COUNT(CQ.ID) as C " . $strSqlFrom);
				$res_cnt = $res_cnt->fetch();
				$res = new CDBResult();
				$res->NavQuery($strSql, $res_cnt['C'], $arNavParams);
			}
		}
		else
			$res = $DB->Query($strSql);

		$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields('LEARNING_QUESTIONS'));

		return $res;
	}


	public static function GetCount($arFilter=Array())
	{
		global $DB;

		$arSqlSearch = CLQuestion::GetFilter($arFilter);

		$strSqlSearch = "";
		$cnt = count($arSqlSearch);
		for($i=0; $i<$cnt; $i++)
			if($arSqlSearch[$i] <> '')
				$strSqlSearch .= " AND ".$arSqlSearch[$i]." ";

		$strSql =
		"SELECT COUNT(DISTINCT CQ.ID) as C ".
		"FROM b_learn_question CQ ".
		"INNER JOIN b_learn_lesson CL ON CQ.LESSON_ID = CL.ID ".
		"WHERE 1=1 ".
		$strSqlSearch;

		$res = $DB->Query($strSql);
		$res_cnt = $res->Fetch();

		return intval($res_cnt["C"]);
	}
}
