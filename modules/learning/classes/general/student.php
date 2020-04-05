<?

// 2012-04-16 Checked/modified for compatibility with new data model
class CStudent
{
	// 2012-04-16 Checked/modified for compatibility with new data model
	public static function CheckFields(&$arFields, $ID = false)
	{
		global $DB, $APPLICATION;
		$arMsg = array();

		if ((is_set($arFields, "USER_ID") || $ID === false) && intval($arFields["USER_ID"]) <= 0)
		{
			$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_USER_ID"), "EMPTY_USER_ID");
			return false;
		}
		elseif (is_set($arFields, "USER_ID"))
		{
			$dbResult = CUser::GetByID($arFields["USER_ID"]);
			if (!$dbResult->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_USER_ID_EX"), "ERROR_NO_USER_ID");
				return false;
			}

			$dbResult = CStudent::GetList(Array(), Array("USER_ID" => $arFields["USER_ID"]));
			if ($dbResult->Fetch())
			{
				$APPLICATION->ThrowException(GetMessage("LEARNING_BAD_USER_ID_EXISTS"), "ERROR_USER_ID_EXISTS");
				return false;
			}
		}

		if ($ID === false && !is_set($arFields, "TRANSCRIPT"))
		{
			$arFields["TRANSCRIPT"] = CStudent::GenerateTranscipt();
		}
		elseif(is_set($arFields, "TRANSCRIPT") && !preg_match("~^[0-9]{6,}$~",$arFields["TRANSCRIPT"]))
		{
			$arFields["TRANSCRIPT"] = CStudent::GenerateTranscipt();
		}

		if (is_set($arFields, "PUBLIC_PROFILE") && $arFields["PUBLIC_PROFILE"] != "N")
			$arFields["ACTIVE"] = "Y";

		return true;
	}


	// 2012-04-16 Checked/modified for compatibility with new data model
	public static function GenerateTranscipt($TranscriptLength = 8)
	{
		$TranscriptLength = intval($TranscriptLength);

		$digits = "312467589";
		$max = strlen($digits) - 1;

		$str = "";

		for ($i = 0; $i < $TranscriptLength; $i++)
			$str .= $digits[mt_rand(0,$max)];

		return $str;
	}


	// 2012-04-16 Checked/modified for compatibility with new data model
	public static function Add($arFields)
	{
		global $DB;

		if(CStudent::CheckFields($arFields))
		{
			CLearnHelper::FireEvent('OnBeforeStudentAdd', $arFields);

			$arInsert = $DB->PrepareInsert("b_learn_student", $arFields, "learning");

			if (strlen($arInsert[0]) <= 0 || strlen($arInsert[0])<= 0)
				return false;

			$strSql =
				"INSERT INTO b_learn_student(".$arInsert[0].") ".
				"VALUES(".$arInsert[1].")";

			if(!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
				return false;

			CLearnHelper::FireEvent('OnAfterStudentAdd', $arFields);

			return $arFields["USER_ID"];
		}

		return false;
	}


	// 2012-04-16 Checked/modified for compatibility with new data model
	public static function Update($ID, $arFields)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		unset($arFields["USER_ID"]);

		if (CStudent::CheckFields($arFields, $ID))
		{

			$arBinds=Array(
				"RESUME"=>$arFields["RESUME"]
			);

			CLearnHelper::FireEvent('OnBeforeStudentUpdate', $arFields);

			$strUpdate = $DB->PrepareUpdate("b_learn_student", $arFields, "learning");
			$strSql = "UPDATE b_learn_student SET ".$strUpdate." WHERE USER_ID=".$ID;
			$DB->QueryBind($strSql, $arBinds, false, "File: ".__FILE__."<br>Line: ".__LINE__);

			CLearnHelper::FireEvent('OnAfterStudentUpdate', $arFields);

			return true;
		}

		return false;
	}


	// 2012-04-16 Checked/modified for compatibility with new data model
	public static function Delete($ID)
	{
		global $DB;

		$ID = intval($ID);
		if ($ID < 1) return false;

		CLearnHelper::FireEvent('OnBeforeStudentDelete', $ID);

		//Certification
		$records = CCertification::GetList(Array(), Array("STUDENT_ID" => $ID));
		while($arRecord = $records->Fetch())
		{
			if(!CCertification::Delete($arRecord["ID"]))
				return false;
		}

		$strSql = "DELETE FROM b_learn_student WHERE USER_ID = ".$ID;

		if (!$DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__))
			return false;

		CLearnHelper::FireEvent('OnAfterStudentDelete', $ID);

		return true;
	}


	// 2012-04-16 Checked/modified for compatibility with new data model
	public static function GetByID($ID)
	{
		return CStudent::GetList(Array(),Array("USER_ID"=> $ID));
	}


	// 2012-04-16 Checked/modified for compatibility with new data model
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

			$key = strtoupper($key);

			switch ($key)
			{
				case "USER_ID":
				case "TRANSCRIPT":
					$arSqlSearch[] = CLearnHelper::FilterCreate("S.".$key, $val, "number", $bFullJoin, $cOperationType);
					break;

				case "PUBLIC_PROFILE":
					$arSqlSearch[] = CLearnHelper::FilterCreate("S.".$key, $val, "string_equal", $bFullJoin, $cOperationType);
					break;

				case "RESUME":
					$arSqlSearch[] = CLearnHelper::FilterCreate("S.".$key, $val, "string", $bFullJoin, $cOperationType);
					break;
			}

		}

		return $arSqlSearch;
	}


	// 2012-04-16 Checked/modified for compatibility with new data model
	public static function GetList($arOrder=Array(), $arFilter=Array())
	{
		global $DB, $USER;

		$arSqlSearch = CStudent::GetFilter($arFilter);

		$strSqlSearch = "";
		for ($i = 0, $length = count($arSqlSearch); $i < $length; $i++)
		{
			if (strlen($arSqlSearch[$i]) > 0)
			{
				$strSqlSearch .= " AND ".$arSqlSearch[$i]." ";
			}
		}

		$strSql =
		"SELECT S.* ".
		//$DB->Concat("'('",'U.LOGIN',"') '",'U.NAME',"' '", 'U.LAST_NAME')." as USER_NAME ".
		"FROM b_learn_student S ".
		"WHERE 1=1 ".
		$strSqlSearch;

		if (!is_array($arOrder))
			$arOrder = Array();

		foreach($arOrder as $by=>$order)
		{
			$by = strtolower($by);
			$order = strtolower($order);
			if ($order!="asc")
				$order = "desc";

			if ($by == "user_id")						$arSqlOrder[] = " S.USER_ID ".$order." ";
			elseif ($by == "public_profile")		$arSqlOrder[] = " S.PUBLIC_PROFILE ".$order." ";
			else
			{
				$arSqlOrder[] = " S.USER_ID ".$order." ";
				$by = "user_id";
			}
		}

		$strSqlOrder = "";
		DelDuplicateSort($arSqlOrder);
		for ($i = 0, $length = count($arSqlOrder); $i < $length; $i++)
		{
			if ($i == 0)
			{
				$strSqlOrder = " ORDER BY ";
			}
			else
			{
				$strSqlOrder .= ",";
			}

			$strSqlOrder .= $arSqlOrder[$i];
		}

		$strSql .= $strSqlOrder;

		//echo $strSql;
		return $DB->Query($strSql, false, "File: ".__FILE__."<br>Line: ".__LINE__);
	}
}
