<?

// 2012-04-10 Checked/modified for compatibility with new data model
class CAllCertification
{
	public static function LessonIdByCertId ($certId)
	{
		$rc = CCertification::GetByID($certId);
		if ($rc === false)
			throw new LearnException('', LearnException::EXC_ERR_ALL_GIVEUP);

		$row = $rc->Fetch();

		if ( ! isset($row['COURSE_ID']) )
			throw new LearnException('', LearnException::EXC_ERR_ALL_GIVEUP);

		$lessonId = CCourse::CourseGetLinkedLesson($row['COURSE_ID']);

		if ($lessonId === false)
			throw new LearnException('', LearnException::EXC_ERR_ALL_GIVEUP);

		return ($lessonId);
	}


	// 2012-04-10 Checked/modified for compatibility with new data model
	function CheckFields(&$arFields, $ID = false)
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

		if ($ID===false && !is_set($arFields, "COURSE_ID"))
		{
			$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_COURSE_ID"), "ERROR_EMPTY_COURSE_ID");
			return false;
		}
		elseif (is_set($arFields, "COURSE_ID"))
		{
			if ($bCheckRights)
				$r = CCourse::GetByID($arFields["COURSE_ID"]);
			else
				$r = CCourse::GetList(Array(),Array("ID" => $arFields["COURSE_ID"], 'CHECK_PERMISSIONS' => 'N'));

			if(!$r->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_COURSE_ID_EX"), "ERROR_NO_COURSE_ID");
				return false;
			}
		}

		if (is_set($arFields, "STUDENT_ID") && is_set($arFields, "COURSE_ID"))
		{
			$res = CCertification::GetList(Array(), Array("STUDENT_ID" => $arFields["STUDENT_ID"], "COURSE_ID" => $arFields["COURSE_ID"]));
			if ($res->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_CERTIFICATE_DUPLICATE"), "ERROR_CERTIFICATE_DUPLICATE");
				return false;
			}
		}

		if (is_set($arFields, "DATE_CREATE") && (!$DB->IsDate($arFields["DATE_CREATE"], false, LANG, "FULL")))
		{
			$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_DATE_CREATE"), "EMPTY_DATE_CREATE");
			return false;
		}

		if (is_set($arFields, "PUBLIC_PROFILE") && $arFields["PUBLIC"] != "N")
			$arFields["PUBLIC_PROFILE"] = "Y";

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "N")
			$arFields["ACTIVE"] = "Y";

		if (is_set($arFields, "FROM_ONLINE") && $arFields["FROM_ONLINE"] != "N")
			$arFields["FROM_ONLINE"] = "Y";

		return true;
	}


	// 2012-04-10 Checked/modified for compatibility with new data model
	function Add($arFields, $arParams = array())
	{
		global $DB;

		$bCheckRights = true;
		if (isset($arParams['CHECK_PERMISSIONS']) && ($arParams['CHECK_PERMISSIONS'] === 'N'))
			$bCheckRights = false;

		if(CCertification::CheckFields($arFields, false, $bCheckRights))
		{
			unset($arFields["ID"]);

			CLearnHelper::FireEvent('OnBeforeCertificateAdd', $arFields);

			$ID = $DB->Add("b_learn_certification", $arFields, Array(), "learning");

			$arFields['ID'] = $ID;
			CLearnHelper::FireEvent('OnAfterCertificateAdd', $arFields);

			return $ID;
		}

		return false;
	}


	// 2012-04-10 Checked/modified for compatibility with new data model
	function Update($ID, $arFields, $arParams = array())
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		$bCheckRights = true;
		if (isset($arParams['CHECK_PERMISSIONS']) && ($arParams['CHECK_PERMISSIONS'] === 'N'))
			$bCheckRights = false;

		if (CCertification::CheckFields($arFields, $ID, $bCheckRights))
		{
			unset($arFields["ID"]);
			unset($arFields["STUDENT_ID"]);
			unset($arFields["COURSE_ID"]);

			$arBinds=Array(
				//""=>$arFields[""]
			);

			CLearnHelper::FireEvent('OnBeforeCertificateUpdate', $arFields);

			$strUpdate = $DB->PrepareUpdate("b_learn_certification", $arFields, "learning");
			$strSql = "UPDATE b_learn_certification SET ".$strUpdate." WHERE ID=".$ID;
			$DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			CLearnHelper::FireEvent('OnAfterCertificateUpdate', $arFields);

			return true;
		}

		return false;
	}


	// 2012-04-10 Checked/modified for compatibility with new data model
	function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		CLearnHelper::FireEvent('OnBeforeCertificateDelete', $ID);

		$strSql = "SELECT G.ID FROM b_learn_certification C
					INNER JOIN b_learn_test T ON C.COURSE_ID = T.COURSE_ID
					INNER JOIN b_learn_gradebook G ON (G.TEST_ID = T.ID AND G.STUDENT_ID = C.STUDENT_ID)
					WHERE C.ID = ".$ID;

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		//Gradebook
		while($arRecord = $res->Fetch())
		{
			if(!CGradeBook::Delete($arRecord["ID"]))
				return false;
		}

		$strSql = "DELETE FROM b_learn_certification WHERE ID = ".$ID;

		if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		CLearnHelper::FireEvent('OnAfterCertificateDelete', $ID);

		return true;
	}

	// 2012-04-10 Checked/modified for compatibility with new data model
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
				case "STUDENT_ID":
				case "COURSE_ID":
				case "SORT":
				case "SUMMARY":
				case "MAX_SUMMARY":
					$arSqlSearch[] = CLearnHelper::FilterCreate("CER.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "ACTIVE":
				case "PUBLIC_PROFILE":
				case "FROM_ONLINE":
					$arSqlSearch[] = CLearnHelper::FilterCreate("CER.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;

				case "TIMESTAMP_X":
				case "DATE_CREATE":
					$arSqlSearch[] = CLearnHelper::FilterCreate("CER.".$key, $val, "date", $bFullJoin, $cOperationType);
					break;

				case "USER":
					$arSqlSearch[] = GetFilterQuery("U.ID, U.LOGIN, U.NAME, U.LAST_NAME",$val);
					break;

			}

		}

		return $arSqlSearch;

	}

	// 2012-04-10 Checked/modified for compatibility with new data model
	function GetByID($ID)
	{
		return CCertification::GetList(Array(),Array("ID" => $ID));
	}


	// 2012-04-10 Checked/modified for compatibility with new data model
	public static function IsCourseCompleted($STUDENT_ID, $COURSE_ID)
	{
		global $DB;

		$STUDENT_ID = intval($STUDENT_ID);
		$COURSE_ID = intval($COURSE_ID);

		if ($STUDENT_ID < 1 || $COURSE_ID < 1)
			return false;

		$strSql = "
		SELECT COUNT(*) CNT_ALL, SUM(CASE WHEN G.COMPLETED = 'Y' THEN 1 ELSE 0 END ) CNT_COMPLETED
		FROM b_learn_test T
		INNER JOIN b_learn_course C ON T.COURSE_ID = C.ID
		INNER JOIN b_learn_lesson TUL ON C.LINKED_LESSON_ID = TUL.ID
		LEFT JOIN b_learn_gradebook G ON T.ID = G.TEST_ID AND G.STUDENT_ID = '".$STUDENT_ID."'
		WHERE T.COURSE_ID = '".$COURSE_ID."' AND TUL.ACTIVE = 'Y' AND T.ACTIVE = 'Y'
		";

		$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

		$cntAll = $cntCompleted = null;

		$isCourseCompleted = false;	// can be overrided below
		if ( ($ar = $res->Fetch()) && intval($ar["CNT_ALL"]) > 0)
		{
			$cntAll       = $ar['CNT_ALL'];
			$cntCompleted = $ar['CNT_COMPLETED'];

			if ($cntCompleted == $cntAll)
				$isCourseCompleted = true;
		}

		$arEventData = array(
			'STUDENT_ID'    => $STUDENT_ID,
			'COURSE_ID'     => $COURSE_ID,
			'CNT_ALL'       => $cntAll,
			'CNT_COMPLETED' => $cntCompleted
		);

		foreach(GetModuleEvents('learning', 'OnCheckCourseCompleted', true) as $arEvent)
		{
			$rc = ExecuteModuleEventEx($arEvent, array($arEventData));

			if ($rc === false)
			{
				$isCourseCompleted = false;
				break;
			}
			elseif ($rc === true)
			{
				$isCourseCompleted = true;
				break;
			}
		}

		return $isCourseCompleted;
	}


	// 2012-04-10 Checked/modified for compatibility with new data model
	function Certificate($STUDENT_ID, $COURSE_ID, $checkPerms = true)
	{
		global $DB;

		$STUDENT_ID = intval($STUDENT_ID);
		$COURSE_ID = intval($COURSE_ID);

		if ($STUDENT_ID < 1 || $COURSE_ID < 1)
			return false;

		if (CCertification::IsCourseCompleted($STUDENT_ID, $COURSE_ID))
		{

			$strSql = "SELECT SUM(G.RESULT) CNT, SUM(G.MAX_RESULT) MAX_CNT FROM b_learn_gradebook G
			INNER JOIN b_learn_test T ON T.ID = G.TEST_ID
			WHERE G.COMPLETED = 'Y' AND G.STUDENT_ID = '".$STUDENT_ID."' AND T.COURSE_ID = '".$COURSE_ID."'";
			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$SUMMARY = $MAX_SUMMARY = 0;

			if ($ar = $res->Fetch())
			{
				$SUMMARY = $ar["CNT"];
				$MAX_SUMMARY = $ar["MAX_CNT"];
			}

			$arFields = array(
				'STUDENT_ID'  => $STUDENT_ID,
				'COURSE_ID'   => $COURSE_ID,
				'SUMMARY'     => &$SUMMARY,
				'MAX_SUMMARY' => &$MAX_SUMMARY 
			);

			foreach(GetModuleEvents('learning', 'OnBeforeCertificate', true) as $arEvent)
			{
				if (ExecuteModuleEventEx($arEvent, array(&$arFields))===false)
					return (false);
			}

			$arParams = array();

			if ( ! $checkPerms )
				$arParams['CHECK_PERMISSIONS'] = 'N';

			$strSql = "SELECT ID FROM b_learn_certification WHERE STUDENT_ID = '".$STUDENT_ID."' AND COURSE_ID = '".$COURSE_ID."'";
			$res = $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
			if ($ar = $res->Fetch())
			{
				return CCertification::Update($ar["ID"], Array("SUMMARY" => $SUMMARY, "MAX_SUMMARY" => $MAX_SUMMARY, "ACTIVE" => "Y"), $arParams);
			}
			else
			{
				$arFields = Array(
					"STUDENT_ID" => $STUDENT_ID,
					"COURSE_ID" => $COURSE_ID,
					"SUMMARY" => $SUMMARY,
					"MAX_SUMMARY" => $MAX_SUMMARY,
					"~DATE_CREATE" => CDatabase::CurrentTimeFunction(),
				);

				$ID = CCertification::Add($arFields, $arParams);
				return ($ID > 0);
			}
		}
		return false;
	}
}
