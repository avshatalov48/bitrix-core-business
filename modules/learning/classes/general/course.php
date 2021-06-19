<?php

class CCourse
{
	// 2012-04-17 Checked/modified for compatibility with new data model
	final public static function GetList($arOrder = array(), $arFields = array(), $arNavParams = array())
	{
		// Lists only lesson-courses
		$arFields = array_merge (array('>LINKED_LESSON_ID' => 0), $arFields);

		foreach ($arOrder as $key => $value)
		{
			if (mb_strtoupper($key) === 'ID')
			{
				$arOrder['COURSE_ID'] = $arOrder[$key];
				unset ($arOrder[$key]);
			}
		}

		// We must replace '...ID' => '...COURSE_ID', where '...' is some operation (such as '!', '<=', etc.)
		foreach ($arFields as $key => $value)
		{
			// If key ends with 'ID'
			if ((mb_strlen($key) >= 2) && (mb_strtoupper(mb_substr($key, -2)) === 'ID'))
			{
				// And prefix before 'ID' doesn't contains letters
				if ( ! preg_match ("/[a-zA-Z_]+/", mb_substr($key, 0, -2)) )
				{
					$prefix = '';
					if (mb_strlen($key) > 2)
						$prefix = mb_substr($key, 0, -2);

					$arFields[$prefix . 'COURSE_ID'] = $arFields[$key];
					unset ($arFields[$key]);
				}
			}
		}

		$arFields['#REPLACE_COURSE_ID_TO_ID'] = true;

		$res = CLearnLesson::GetList($arOrder, $arFields, array(), $arNavParams);
		return ($res);
	}


	/**
	 * Gets id of lesson corresponded to given course
	 * @param integer id of course
	 * @throws LearnException with error bit set (one of):
	 *         - LearnException::EXC_ERR_ALL_GIVEUP
	 *         - LearnException::EXC_ERR_ALL_LOGIC
	 * @return integer/bool id of linked (corresponded) lesson or 
	 *         FALSE if there is no lesson corresponded to the course.
	 */
	final public static function CourseGetLinkedLesson ($courseId)
	{
		$arMap = CLearnLesson::GetCourseToLessonMap();

		if ( ! isset($arMap['C' . $courseId]) )
		{
			return false;
		}

		// return id of corresponded lesson
		return ($arMap['C' . $courseId]);
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	function CheckFields($arFields, $ID = false)
	{
		global $DB;
		$arMsg = array();

		if ( (is_set($arFields, "NAME") || $ID === false) && trim($arFields["NAME"]) == '')
		{
			$arMsg[] = array("id"=>"NAME", "text"=> GetMessage("LEARNING_BAD_NAME"));
		}

		if (is_set($arFields, "ACTIVE_FROM") && $arFields["ACTIVE_FROM"] <> '' && (!$DB->IsDate($arFields["ACTIVE_FROM"], false, LANG, "FULL")))
		{
			$arMsg[] = array("id"=>"ACTIVE_FROM", "text"=> GetMessage("LEARNING_BAD_ACTIVE_FROM"));
		}

		if (is_set($arFields, "ACTIVE_TO") && $arFields["ACTIVE_TO"] <> '' && (!$DB->IsDate($arFields["ACTIVE_TO"], false, LANG, "FULL")))
		{
			$arMsg[] = array("id"=>"ACTIVE_TO", "text"=> GetMessage("LEARNING_BAD_ACTIVE_TO"));
		}

		if (is_set($arFields, "PREVIEW_PICTURE") && is_array($arFields["PREVIEW_PICTURE"]))
		{
			$error = CFile::CheckImageFile($arFields["PREVIEW_PICTURE"]);
			if ($error <> '')
			{
				$arMsg[] = array("id"=>"PREVIEW_PICTURE", "text"=> $error);
			}
		}

		//Sites
		if (
			($ID === false && !is_set($arFields, "SITE_ID"))
			||
			(is_set($arFields, "SITE_ID"))
			&&
			(!is_array($arFields["SITE_ID"]) || empty($arFields["SITE_ID"]))
			)
		{
			$arMsg[] = array("id"=>"SITE_ID[]", "text"=> GetMessage("LEARNING_BAD_SITE_ID"));
		}
		elseif (is_set($arFields, "SITE_ID"))
		{
			$tmp = "";
			foreach($arFields["SITE_ID"] as $lang)
			{
				$res = CSite::GetByID($lang);
				if(!$res->Fetch())
				{
					$tmp .= "'".$lang."' - ".GetMessage("LEARNING_BAD_SITE_ID_EX")."<br>";
				}
			}
			if ($tmp!="") $arMsg[] = array("id"=>"SITE_ID[]", "text"=> $tmp);
		}

		if(!empty($arMsg))
		{
			$e = new CAdminException($arMsg);
			$GLOBALS["APPLICATION"]->ThrowException($e);
			return false;
		}

		return true;
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	function Add($arFields)
	{
		global $DB;

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";

		if (is_set($arFields, "DETAIL_TEXT_TYPE") && $arFields["DETAIL_TEXT_TYPE"] != "html")
			$arFields["DETAIL_TEXT_TYPE"] = "text";

		if (is_set($arFields, "PREVIEW_TEXT_TYPE") && $arFields["PREVIEW_TEXT_TYPE"] != "html")
			$arFields["PREVIEW_TEXT_TYPE"]="text";

		if (is_set($arFields, "PREVIEW_PICTURE") && $arFields["PREVIEW_PICTURE"]["name"] == '' && $arFields["PREVIEW_PICTURE"]["del"] == '')
			unset($arFields["PREVIEW_PICTURE"]);

		if (is_set($arFields, "RATING") && !in_array($arFields["RATING"], Array("Y", "N")))
			$arFields["RATING"] = "N";

		if (is_set($arFields, "RATING_TYPE") && !in_array($arFields["RATING_TYPE"], Array("like", "standart_text", "like_graphic", "standart")))
			$arFields["RATING_TYPE"] = NULL;

		if($this->CheckFields($arFields))
		{
			unset($arFields["ID"]);

			$arFieldsLesson = $arFields;
			$arFieldsToUnset = array ('GROUP_ID', 'SITE_ID');

			// Some fields mustn't be in unilesson
			foreach ($arFieldsToUnset as $key => $value)
				if (array_key_exists($value, $arFieldsLesson))
					unset ($arFieldsLesson[$value]);

			$lessonId = CLearnLesson::Add ($arFieldsLesson, $isCourse = true);
			$ID = CLearnLesson::GetLinkedCourse ($lessonId);
			if ($ID === false)
				return (false);

			//Sites
			$str_LID = "''";
			foreach($arFields["SITE_ID"] as $lang)
					$str_LID .= ", '".$DB->ForSql($lang)."'";
			$strSql = "DELETE FROM b_learn_course_site WHERE COURSE_ID=".$ID;
			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			$strSql =
				"INSERT INTO b_learn_course_site(COURSE_ID, SITE_ID) ".
				"SELECT ".$ID.", LID ".
				"FROM b_lang ".
				"WHERE LID IN (".$str_LID.") ";

			$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			\Bitrix\Learning\Integration\Search::indexLesson($lessonId);

			CLearnCacheOfLessonTreeComponent::MarkAsDirty();

			return $ID;
		}
		return false;
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		if (is_set($arFields, "ACTIVE") && $arFields["ACTIVE"] != "Y")
			$arFields["ACTIVE"] = "N";

		if (is_set($arFields, "DESCRIPTION_TYPE") && $arFields["DESCRIPTION_TYPE"] != "html")
			$arFields["DESCRIPTION_TYPE"] = "text";

		if (is_set($arFields, "DETAIL_TEXT_TYPE") && $arFields["DETAIL_TEXT_TYPE"] != "html")
			$arFields["DETAIL_TEXT_TYPE"] = "text";

		if (is_set($arFields, "PREVIEW_TEXT_TYPE") && $arFields["PREVIEW_TEXT_TYPE"] != "html")
			$arFields["PREVIEW_TEXT_TYPE"]="text";

		if (is_set($arFields, "RATING") && !in_array($arFields["RATING"], Array("Y", "N")))
			$arFields["RATING"] = NULL;

		if (is_set($arFields, "RATING_TYPE") && !in_array($arFields["RATING_TYPE"], Array("like", "standart_text", "like_graphic", "standart")))
			$arFields["RATING_TYPE"] = NULL;

		$lessonId = self::CourseGetLinkedLesson ($ID);
		if ($this->CheckFields($arFields, $ID) && $lessonId !== false)
		{
			if (array_key_exists('ID', $arFields))
				unset($arFields["ID"]);

			$arFieldsLesson = $arFields;
			$arFieldsToUnset = array ('GROUP_ID', 'SITE_ID');

			foreach ($arFieldsToUnset as $key => $value)
				if (array_key_exists($value, $arFieldsLesson))
					unset ($arFieldsLesson[$value]);

			//Sites
			if(is_set($arFields, "SITE_ID"))
			{
				$str_LID = "''";
				foreach($arFields["SITE_ID"] as $lang)
					$str_LID .= ", '".$DB->ForSql($lang)."'";

				$strSql = "DELETE FROM b_learn_course_site WHERE COURSE_ID=".$ID;
				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

				$strSql =
					"INSERT INTO b_learn_course_site(COURSE_ID, SITE_ID) ".
					"SELECT ".$ID.", LID ".
					"FROM b_lang ".
					"WHERE LID IN (".$str_LID.") ";

				$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			}

			CLearnLesson::Update($lessonId, $arFieldsLesson);

			global $CACHE_MANAGER;
			$CACHE_MANAGER->ClearByTag('LEARN_COURSE_'.$ID);

			return true;
		}

		return false;
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	/**
	 * Removes course (as node, not recursively)
	 */
	function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1)
			return false;

		$lessonId = CCourse::CourseGetLinkedLesson($ID);
		if ($lessonId === false)
		{
			return false;
		}

		CLearnLesson::Delete($lessonId);

		return true;
	}


	public static function IsCertificatesExists($courseId)
	{
		// Check certificates (if exists => forbid removing course)
		$certificate = CCertification::GetList(Array(), Array("COURSE_ID" => $courseId, 'CHECK_PERMISSIONS' => 'N'));
		if ( ($certificate === false) || ($certificate->GetNext()) )
			return true;
		else
			return false;
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	public static function GetByID($ID)
	{
		return CCourse::GetList(Array(),Array("ID" => $ID));
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	function GetGroupPermissions($COURSE_ID)
	{
		$linkedLessonId      = CCourse::CourseGetLinkedLesson($COURSE_ID);
		$arGroupPermissions  = CLearnAccess::GetSymbolsAccessibleToLesson ($linkedLessonId, CLearnAccess::OP_LESSON_READ);
		return ($arGroupPermissions);
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	public static function GetSite($COURSE_ID)
	{
		global $DB;
		$strSql = "SELECT L.*, CS.* FROM b_learn_course_site CS, b_lang L WHERE L.LID=CS.SITE_ID AND CS.COURSE_ID=".intval($COURSE_ID);

		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}


	public static function GetSiteId($COURSE_ID)
	{
		global $DB;
		$strSql = "SELECT SITE_ID FROM b_learn_course_site WHERE COURSE_ID=" . ((int) $COURSE_ID);

		$rc = $DB->Query($strSql, true);
		if ($rc === false)
			throw new LearnException ('EA_SQLERROR', LearnException::EXC_ERR_ALL_GIVEUP);

		$row = $rc->Fetch();
		if ( ! isset($row['SITE_ID']) )
			throw new LearnException ('EA_NOT_EXISTS', LearnException::EXC_ERR_ALL_NOT_EXISTS);

		return ($row['SITE_ID']);
	}


	public static function GetSitePathes($siteId, $in_type = 'U')
	{
		global $DB;

		$in_type = mb_strtoupper($in_type);
		switch ($in_type)
		{
			case 'L':
			case 'C':
			case 'H':
			case 'U':
				$type = $DB->ForSql($in_type);
			break;

			default:
				throw new LearnException ('EA_PARAMS', LearnException::EXC_ERR_ALL_PARAMS);
			break;
		}

		$strSql = 
		"SELECT TSP.PATH 
		FROM b_learn_site_path TSP 
		WHERE TSP.SITE_ID='" . $DB->ForSql($siteId) . "' AND TSP.TYPE = '" . $type . "'";

		$rc = $DB->Query($strSql, true);
		if ($rc === false)
			throw new LearnException ('EA_SQLERROR', LearnException::EXC_ERR_ALL_GIVEUP);

		$arPathes = array();
		while ($row = $rc->Fetch())
			$arPathes[] = $row['PATH'];

		return ($arPathes);
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	function MkOperationFilter($key)
	{
		// refactored: body of function moved to CLearnHelper class
		return (CLearnHelper::MkOperationFilter($key));
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	function FilterCreate($fname, $vals, $type, &$bFullJoin, $cOperationType=false, $bSkipEmpty = true)
	{
		// refactored: body of function moved to CLearnHelper class
		return (CLearnHelper::FilterCreate($fname, $vals, $type, $bFullJoin, $cOperationType, $bSkipEmpty));
	}


	// 2012-04-18 Checked/modified for compatibility with new data model
	public static function GetCourseContent(
		$COURSE_ID, 
		$arAddSelectFileds = array("DETAIL_TEXT", "DETAIL_TEXT_TYPE", "DETAIL_PICTURE"), 
		$arSelectFields = array()
	)
	{
		global $DB;

		$COURSE_ID = intval($COURSE_ID);

		$CACHE_ID = ((string) $COURSE_ID) . sha1(serialize($arSelectFields));

		if ( ! (
			array_key_exists($CACHE_ID, $GLOBALS["LEARNING_CACHE_COURSE"]) 
			&& is_array($GLOBALS["LEARNING_CACHE_COURSE"][$CACHE_ID])
			)
		)
		{
			$oTree = CLearnLesson::GetTree(
				CCourse::CourseGetLinkedLesson($COURSE_ID),
				array(
					'EDGE_SORT' => 'asc'
					),
				array(
					'ACTIVE'            => 'Y',
					'CHECK_PERMISSIONS' => 'N'
					),
				true,		// $publishProhibitionMode,
				$arSelectFields
				);

			$arTree = $oTree->GetTreeAsListOldMode();

			$GLOBALS["LEARNING_CACHE_COURSE"][$CACHE_ID] = $arTree;
		}

		$r = new CDBResult();
		$r->InitFromArray($GLOBALS["LEARNING_CACHE_COURSE"][$CACHE_ID]);
		return $r;
	}


	// Handlers:

	// 2012-04-17 Checked/modified for compatibility with new data model
	public static function OnGroupDelete($GROUP_ID)
	{
		global $DB;

		$rc = $DB->Query("DELETE FROM b_learn_rights WHERE SUBJECT_ID='G" . (int) $GROUP_ID . "'", true)
			&& $DB->Query("DELETE FROM b_learn_rights_all WHERE SUBJECT_ID='G" . (int) $GROUP_ID . "'", true);

		CLearnCacheOfLessonTreeComponent::MarkAsDirty();

		return ($rc);
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	public static function OnBeforeLangDelete($lang)
	{
		global $APPLICATION;
		$r = CCourse::GetList(array(), array("SITE_ID"=>$lang));

		$bAllowDelete = true;

		// Is any data exists for this site?
		if ($r->Fetch())
			$bAllowDelete = false;

		if ( ! $bAllowDelete )
			$APPLICATION->ThrowException(GetMessage('LEARNING_PREVENT_LANG_REMOVE'));

		return ($bAllowDelete);
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	public static function OnUserDelete($user_id)
	{
		return CStudent::Delete($user_id);
	}


	// 2012-04-17 Checked/modified for compatibility with new data model
	public static function TimeToStr($seconds)
	{
		$str = "";

		$seconds = intval($seconds);
		if ($seconds <= 0)
			return $str;

		$days = intval($seconds/86400);
		if ($days>0)
		{
			$str .= $days."&nbsp;".GetMessage("LEARNING_DAYS")." ";
			$seconds = $seconds - $days*86400;
		}

		$hours = intval($seconds/3600);
		if ($hours>0)
		{
			$str .= $hours."&nbsp;".GetMessage("LEARNING_HOURS")." ";
			$seconds = $seconds - $hours*3600;
		}

		$minutes = intval($seconds/60);
		if ($minutes>0)
		{
			$str .= $minutes."&nbsp;".GetMessage("LEARNING_MINUTES")." ";
			$seconds = $seconds - $minutes*60;
		}

		$str .= ($seconds%60)."&nbsp;".GetMessage("LEARNING_SECONDS");

		return $str;
	}


	// provided compatibility to new data model at 04.05.2012
	public static function OnSearchReindex($nextStep = [], $callbackObject = null, $callbackMethod = "")
	{
		return Bitrix\Learning\Integration\Search::handleReindex($nextStep, $callbackObject, $callbackMethod);
	}


	public static function _Upper($str)
	{
		return $str;
	}


	// Functions below are for temporary backward compatibility, don't relay on it!

	/**
	 * Stupid stub
	 * 
	 * @deprecated this code can be removed at any time without any notice
	 */
	public static function SetPermission ($param1, $param2)
	{
		return;
	}


	/**
	 * Simple proxy
	 * 
	 * @deprecated this code can be removed at any time without any notice
	 * @return character 'D', 'R', 'W' or 'X'
	 */
	public static function GetPermission ($courseId)
	{
		global $USER;
		static $accessMatrix = false;

		$courseId = (int) $courseId;

		if ( ! ($courseId > 0) )
			return ('D');		// access denied

		$linkedLessonId = CCourse::CourseGetLinkedLesson($courseId);

		if ( ! ($linkedLessonId > 0) )
			return ('D');		// some troubles, access denied

		$oAccess = CLearnAccess::GetInstance($USER->GetID());

		if ($accessMatrix === false)
		{
			$accessMatrix = array(
				// full access
				'X' => CLearnAccess::OP_LESSON_READ 
					| CLearnAccess::OP_LESSON_CREATE 
					| CLearnAccess::OP_LESSON_WRITE 
					| CLearnAccess::OP_LESSON_REMOVE 
					| CLearnAccess::OP_LESSON_LINK_TO_PARENTS 
					| CLearnAccess::OP_LESSON_UNLINK_FROM_PARENTS 
					| CLearnAccess::OP_LESSON_LINK_DESCENDANTS 
					| CLearnAccess::OP_LESSON_UNLINK_DESCENDANTS 
					| CLearnAccess::OP_LESSON_MANAGE_RIGHTS,

				// write access
				'W' => CLearnAccess::OP_LESSON_READ 
					| CLearnAccess::OP_LESSON_CREATE 
					| CLearnAccess::OP_LESSON_WRITE 
					| CLearnAccess::OP_LESSON_REMOVE,

				// read-only access
				'R' => CLearnAccess::OP_LESSON_READ
			);
		}

		foreach ($accessMatrix as $oldAccessSymbol => $operations)
		{
			if ($oAccess->IsBaseAccess($operations)
				|| $oAccess->IsLessonAccessible($linkedLessonId, $operations)
			)
			{
				return ($oldAccessSymbol);
			}
		}

		// by default, access denied
		return ('D');
	}
}
