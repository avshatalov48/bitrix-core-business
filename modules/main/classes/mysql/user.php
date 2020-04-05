<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/general/user.php");

class CUser extends CAllUser
{
	public static function err_mess()
	{
		return "<br>Class: CUser<br>File: ".__FILE__;
	}

	public function Add($arFields)
	{
		/** @global CUserTypeManager $USER_FIELD_MANAGER */
		global $DB, $USER_FIELD_MANAGER, $CACHE_MANAGER;

		$ID = 0;
		if(!$this->CheckFields($arFields))
		{
			$Result = false;
			$arFields["RESULT_MESSAGE"] = &$this->LAST_ERROR;
		}
		else
		{
			unset($arFields["ID"]);
			if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
				$arFields["ACTIVE"]="N";

			if($arFields["PERSONAL_GENDER"]=="NOT_REF" || ($arFields["PERSONAL_GENDER"]!="M" && $arFields["PERSONAL_GENDER"]!="F"))
				$arFields["PERSONAL_GENDER"] = "";

			$original_pass = $arFields["PASSWORD"];
			$salt = randString(8);
			$arFields["PASSWORD"] = $salt.md5($salt.$arFields["PASSWORD"]);
			unset($arFields["STORED_HASH"]);

			$salt =  randString(8);
			$checkword = ($arFields["CHECKWORD"] == ''? md5(CMain::GetServerUniqID().uniqid()) : $arFields["CHECKWORD"]);
			$arFields["CHECKWORD"] = $salt.md5($salt.$checkword);

			$arFields["~CHECKWORD_TIME"] = $DB->CurrentTimeFunction();

			if(is_set($arFields, "WORK_COUNTRY"))
				$arFields["WORK_COUNTRY"] = intval($arFields["WORK_COUNTRY"]);

			if(is_set($arFields, "PERSONAL_COUNTRY"))
				$arFields["PERSONAL_COUNTRY"] = intval($arFields["PERSONAL_COUNTRY"]);

			if (
				array_key_exists("PERSONAL_PHOTO", $arFields)
				&& is_array($arFields["PERSONAL_PHOTO"])
				&& (
					!array_key_exists("MODULE_ID", $arFields["PERSONAL_PHOTO"])
					|| strlen($arFields["PERSONAL_PHOTO"]["MODULE_ID"]) <= 0
				)
			)
				$arFields["PERSONAL_PHOTO"]["MODULE_ID"] = "main";

			CFile::SaveForDB($arFields, "PERSONAL_PHOTO", "main");

			if (
				array_key_exists("WORK_LOGO", $arFields)
				&& is_array($arFields["WORK_LOGO"])
				&& (
					!array_key_exists("MODULE_ID", $arFields["WORK_LOGO"])
					|| strlen($arFields["WORK_LOGO"]["MODULE_ID"]) <= 0
				)
			)
				$arFields["WORK_LOGO"]["MODULE_ID"] = "main";

			CFile::SaveForDB($arFields, "WORK_LOGO", "main");

			$arInsert = $DB->PrepareInsert("b_user", $arFields);

			if(!is_set($arFields, "DATE_REGISTER"))
			{
				$arInsert[0] .= ", DATE_REGISTER";
				$arInsert[1] .= ", ".$DB->GetNowFunction();
			}

			$strSql = "
				INSERT INTO b_user (
					".$arInsert[0]."
				) VALUES (
					".$arInsert[1]."
				)
			";
			$DB->Query($strSql);
			$ID = $DB->LastID();

			$USER_FIELD_MANAGER->Update("USER", $ID, $arFields);

			if(is_set($arFields, "GROUP_ID"))
				CUser::SetUserGroup($ID, $arFields["GROUP_ID"], true);

			//update digest hash for http digest authorization
			if(COption::GetOptionString('main', 'use_digest_auth', 'N') == 'Y')
				CUser::UpdateDigest($ID, $original_pass);

			$Result = $ID;
			$arFields["ID"] = &$ID;
			$arFields["CHECKWORD"] = $checkword;
		}

		$arFields["RESULT"] = &$Result;

		foreach (GetModuleEvents("main", "OnAfterUserAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		if($ID > 0 && defined("BX_COMP_MANAGED_CACHE"))
		{
			$isRealUser = !$arFields['EXTERNAL_AUTH_ID'] || !in_array($arFields['EXTERNAL_AUTH_ID'], \Bitrix\Main\UserTable::getExternalUserTypes());

			$CACHE_MANAGER->ClearByTag("USER_CARD_".intval($ID / TAGGED_user_card_size));
			$CACHE_MANAGER->ClearByTag($isRealUser? "USER_CARD": "EXTERNAL_USER_CARD");

			$CACHE_MANAGER->ClearByTag("USER_NAME_".$ID);
			$CACHE_MANAGER->ClearByTag($isRealUser? "USER_NAME": "EXTERNAL_USER_NAME");
		}

		\Bitrix\Main\UserTable::indexRecord($ID);

		return $Result;
	}

	public static function GetDropDownList($strSqlSearch="and ACTIVE='Y'", $strSqlOrder="ORDER BY ID, NAME, LAST_NAME")
	{
		global $DB;
		$err_mess = (CUser::err_mess())."<br>Function: GetDropDownList<br>Line: ";
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				concat('[',ID,'] (',LOGIN,') ',ifnull(NAME,''),' ',ifnull(LAST_NAME,'')) as REFERENCE
			FROM
				b_user
			WHERE
				1=1
			$strSqlSearch
			$strSqlOrder
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetList(&$by, &$order, $arFilter=Array(), $arParams=Array())
	{
		/** @global CUserTypeManager $USER_FIELD_MANAGER */
		global $DB, $USER_FIELD_MANAGER;

		$err_mess = (CUser::err_mess())."<br>Function: GetList<br>Line: ";

		if (is_array($by))
		{
			$bSingleBy = false;
			$arOrder = $by;
		}
		else
		{
			$bSingleBy = true;
			$arOrder = array($by=>$order);
		}

		static $obUserFieldsSql;
		if (!isset($obUserFieldsSql))
		{
			$obUserFieldsSql = new CUserTypeSQL;
			$obUserFieldsSql->SetEntity("USER", "U.ID");
			$obUserFieldsSql->obWhere->AddFields(array(
				"F_LAST_NAME" => array(
					"TABLE_ALIAS" => "U",
					"FIELD_NAME" => "U.LAST_NAME",
					"MULTIPLE" => "N",
					"FIELD_TYPE" => "string",
					"JOIN" => false,
				),
			));
		}
		$obUserFieldsSql->SetSelect($arParams["SELECT"]);
		$obUserFieldsSql->SetFilter($arFilter);
		$obUserFieldsSql->SetOrder($arOrder);

		$arFields_m = array("ID", "ACTIVE", "LAST_LOGIN", "LOGIN", "EMAIL", "NAME", "LAST_NAME", "SECOND_NAME", "TIMESTAMP_X", "PERSONAL_BIRTHDAY", "IS_ONLINE", "IS_REAL_USER");
		$arFields = array(
			"DATE_REGISTER", "PERSONAL_PROFESSION", "PERSONAL_WWW", "PERSONAL_ICQ", "PERSONAL_GENDER", "PERSONAL_PHOTO", "PERSONAL_PHONE", "PERSONAL_FAX",
			"PERSONAL_MOBILE", "PERSONAL_PAGER", "PERSONAL_STREET", "PERSONAL_MAILBOX", "PERSONAL_CITY", "PERSONAL_STATE", "PERSONAL_ZIP", "PERSONAL_COUNTRY", "PERSONAL_NOTES",
			"WORK_COMPANY", "WORK_DEPARTMENT", "WORK_POSITION", "WORK_WWW", "WORK_PHONE", "WORK_FAX", "WORK_PAGER", "WORK_STREET", "WORK_MAILBOX", "WORK_CITY", "WORK_STATE",
			"WORK_ZIP", "WORK_COUNTRY", "WORK_PROFILE", "WORK_NOTES", "ADMIN_NOTES", "XML_ID", "LAST_NAME", "SECOND_NAME", "STORED_HASH", "CHECKWORD_TIME", "EXTERNAL_AUTH_ID",
			"CONFIRM_CODE", "LOGIN_ATTEMPTS", "LAST_ACTIVITY_DATE", "AUTO_TIME_ZONE", "TIME_ZONE", "TIME_ZONE_OFFSET", "PASSWORD", "CHECKWORD", "LID", "LANGUAGE_ID", "TITLE",
		);
		$arFields_all = array_merge($arFields_m, $arFields);

		$arSelectFields = array();
		$online_interval = (array_key_exists("ONLINE_INTERVAL", $arParams) && intval($arParams["ONLINE_INTERVAL"]) > 0 ? $arParams["ONLINE_INTERVAL"] : CUser::GetSecondsForLimitOnline());
		if (isset($arParams['FIELDS']) && is_array($arParams['FIELDS']) && count($arParams['FIELDS']) > 0 && !in_array("*", $arParams['FIELDS']))
		{
			foreach ($arParams['FIELDS'] as $field)
			{
				$field = strtoupper($field);
				if ($field == 'TIMESTAMP_X' || $field == 'DATE_REGISTER' || $field == 'LAST_LOGIN')
					$arSelectFields[$field] = $DB->DateToCharFunction("U.".$field)." ".$field.", U.".$field." ".$field."_DATE";
				elseif ($field == 'PERSONAL_BIRTHDAY')
					$arSelectFields[$field] = $DB->DateToCharFunction("U.PERSONAL_BIRTHDAY", "SHORT")." PERSONAL_BIRTHDAY, U.PERSONAL_BIRTHDAY PERSONAL_BIRTHDAY_DATE";
				elseif ($field == 'IS_ONLINE')
					$arSelectFields[$field] = "IF(U.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".$online_interval." SECOND), 'Y', 'N') IS_ONLINE";
				elseif ($field == 'IS_REAL_USER')
					$arSelectFields[$field] = "IF(U.EXTERNAL_AUTH_ID IN ('".join("', '", CUser::GetExternalUserTypes())."'), 'N', 'Y') IS_REAL_USER";
				elseif (in_array($field, $arFields_all))
					$arSelectFields[$field] = 'U.'.$field;
			}
		}
		if (empty($arSelectFields))
		{
			$arSelectFields[] = 'U.*';
			$arSelectFields['TIMESTAMP_X'] =	$DB->DateToCharFunction("U.TIMESTAMP_X")." TIMESTAMP_X";
			$arSelectFields['IS_ONLINE'] =	"IF(U.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".$online_interval." SECOND), 'Y', 'N') IS_ONLINE";
			$arSelectFields['DATE_REGISTER'] =	$DB->DateToCharFunction("U.DATE_REGISTER")." DATE_REGISTER";
			$arSelectFields['LAST_LOGIN'] =	$DB->DateToCharFunction("U.LAST_LOGIN")." LAST_LOGIN";
			$arSelectFields['PERSONAL_BIRTHDAY'] =	$DB->DateToCharFunction("U.PERSONAL_BIRTHDAY", "SHORT")." PERSONAL_BIRTHDAY";
		}

		$arSqlSearch = Array();
		$strJoin = "";

		if(is_array($arFilter))
		{
			foreach ($arFilter as $key => $val)
			{
				$key = strtoupper($key);
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				elseif
				(
					$key != "LOGIN_EQUAL_EXACT"
					&& $key != "CONFIRM_CODE"
					&& $key != "!CONFIRM_CODE"
					&& $key != "LAST_ACTIVITY"
					&& $key != "!LAST_ACTIVITY"
					&& $key != "LAST_LOGIN"
					&& $key != "!LAST_LOGIN"
					&& $key != "EXTERNAL_AUTH_ID"
					&& $key != "!EXTERNAL_AUTH_ID"
					&& $key != "IS_REAL_USER"
				)
				{
					if(strlen($val) <= 0 || $val === "NOT_REF")
						continue;
				}
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				switch($key)
				{
				case "ID":
					$arSqlSearch[] = GetFilterQuery("U.ID",$val,"N");
					break;
				case ">ID":
					$arSqlSearch[] = "U.ID > ".intval($val);
					break;
				case "!ID":
					$arSqlSearch[] = "U.ID <> ".intval($val);
					break;
				case "ID_EQUAL_EXACT":
					$arSqlSearch[] = "U.ID='".intval($val)."'";
					break;
				case "TIMESTAMP_1":
					$arSqlSearch[] = "U.TIMESTAMP_X >= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y"),"d.m.Y")."')";
					break;
				case "TIMESTAMP_2":
					$arSqlSearch[] = "U.TIMESTAMP_X <= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y")." 23:59:59","d.m.Y")."')";
					break;
				case "TIMESTAMP_X_1":
					$arSqlSearch[] = "U.TIMESTAMP_X >= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"DD.MM.YYYY HH:MI:SS"),"d.m.Y H:i:s")."')";
					break;
				case "TIMESTAMP_X_2":
					$arSqlSearch[] = "U.TIMESTAMP_X <= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"DD.MM.YYYY HH:MI:SS"),"d.m.Y H:i:s")."')";
					break;
				case "LAST_LOGIN_1":
					$arSqlSearch[] = "U.LAST_LOGIN >= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y"),"d.m.Y")."')";
					break;
				case "LAST_LOGIN_2":
					$arSqlSearch[] = "U.LAST_LOGIN <= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y")." 23:59:59","d.m.Y")."')";
					break;
				case "LAST_LOGIN":
					if ($val === false)
						$arSqlSearch[] = "U.LAST_LOGIN IS NULL";
					break;
				case "!LAST_LOGIN":
					if ($val === false)
						$arSqlSearch[] = "U.LAST_LOGIN IS NOT NULL";
					break;
				case "DATE_REGISTER_1":
					$arSqlSearch[] = "U.DATE_REGISTER >= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y"),"d.m.Y")."')";
					break;
				case "DATE_REGISTER_2":
					$arSqlSearch[] = "U.DATE_REGISTER <= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y")." 23:59:59","d.m.Y")."')";
					break;
				case "ACTIVE":
					$arSqlSearch[] = ($val=="Y") ? "U.ACTIVE='Y'" : "U.ACTIVE='N'";
					break;
				case "LOGIN_EQUAL":
					$arSqlSearch[] = GetFilterQuery("U.LOGIN", $val, "N");
					break;
				case "LOGIN":
					$arSqlSearch[] = GetFilterQuery("U.LOGIN", $val);
					break;
				case "EXTERNAL_AUTH_ID":
					if($val <> '')
						$arSqlSearch[] = "U.EXTERNAL_AUTH_ID='".$DB->ForSQL($val, 255)."'";
					else
						$arSqlSearch[] = "(U.EXTERNAL_AUTH_ID IS NULL OR U.EXTERNAL_AUTH_ID='')";
					break;
				case "!EXTERNAL_AUTH_ID":
  					if (
						is_array($val)
						&& count($val) > 0
					)
					{
						$strTmp = "";
						foreach($val as $authId)
						{
							if (strlen($authId) > 0)
							{
								$strTmp .= (strlen($strTmp) > 0 ? "," : "")."'".$DB->ForSQL($authId, 255)."'";
							}
						}
						if (strlen($strTmp) > 0)
						{
							$arSqlSearch[] = "U.EXTERNAL_AUTH_ID NOT IN (".$strTmp.") OR U.EXTERNAL_AUTH_ID IS NULL";
						}
					}
					elseif (!is_array($val))
					{
						if($val <> '')
							$arSqlSearch[] = "U.EXTERNAL_AUTH_ID <> '".$DB->ForSql($val, 255)."' OR U.EXTERNAL_AUTH_ID IS NULL";
						else
							$arSqlSearch[] = "(U.EXTERNAL_AUTH_ID IS NOT NULL AND LENGTH(U.EXTERNAL_AUTH_ID) > 0)";
					}
					break;
				case "LOGIN_EQUAL_EXACT":
					$arSqlSearch[] = "U.LOGIN='".$DB->ForSql($val)."'";
					break;
				case "XML_ID":
					$arSqlSearch[] = "U.XML_ID='".$DB->ForSql($val)."'";
					break;
				case "CONFIRM_CODE":
					if($val <> '')
						$arSqlSearch[] = "U.CONFIRM_CODE='".$DB->ForSql($val)."'";
					else
						$arSqlSearch[] = "(U.CONFIRM_CODE IS NULL OR LENGTH(U.CONFIRM_CODE) <= 0)";
					break;
				case "!CONFIRM_CODE":
					if($val <> '')
						$arSqlSearch[] = "U.CONFIRM_CODE <> '".$DB->ForSql($val)."'";
					else
						$arSqlSearch[] = "(U.CONFIRM_CODE IS NOT NULL AND LENGTH(U.CONFIRM_CODE) > 0)";
					break;
				case "COUNTRY_ID":
				case "WORK_COUNTRY":
					$arSqlSearch[] = "U.WORK_COUNTRY=".intval($val);
					break;
				case "PERSONAL_COUNTRY":
					$arSqlSearch[] = "U.PERSONAL_COUNTRY=".intval($val);
					break;
				case "NAME":
					$arSqlSearch[] = GetFilterQuery("U.NAME, U.LAST_NAME, U.SECOND_NAME", $val);
					break;
				case "NAME_SEARCH":
					$arSqlSearch[] = GetFilterQuery("U.NAME, U.LAST_NAME, U.SECOND_NAME, U.EMAIL, U.LOGIN", $val);
					break;
				case "EMAIL":
					$arSqlSearch[] = GetFilterQuery("U.EMAIL", $val, "Y", array("@","_",".","-"));
					break;
				case "=EMAIL":
					$arSqlSearch[] = "U.EMAIL = '".$DB->ForSQL(trim($val))."'";
					break;
				case "GROUP_MULTI":
				case "GROUPS_ID":
					if(is_numeric($val) && intval($val)>0)
						$val = array($val);
					if(is_array($val) && count($val)>0)
					{
						$ar = array();
						foreach($val as $id)
							$ar[intval($id)] = intval($id);
						$strJoin .=
							" INNER JOIN (SELECT DISTINCT UG.USER_ID FROM b_user_group UG
							WHERE UG.GROUP_ID in (".implode(",", $ar).")
								and (UG.DATE_ACTIVE_FROM is null or	UG.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")
								and (UG.DATE_ACTIVE_TO is null or UG.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")
							) UG ON UG.USER_ID=U.ID ";
					}
					break;
				case "PERSONAL_BIRTHDATE_1":
					$arSqlSearch[] = "U.PERSONAL_BIRTHDATE>=".$DB->CharToDateFunction($val);
					break;
				case "PERSONAL_BIRTHDATE_2":
					$arSqlSearch[] = "U.PERSONAL_BIRTHDATE<=".$DB->CharToDateFunction($val." 23:59:59");
					break;
				case "PERSONAL_BIRTHDAY_1":
					$arSqlSearch[] = "U.PERSONAL_BIRTHDAY>=".$DB->CharToDateFunction($DB->ForSql($val), "SHORT");
					break;
				case "PERSONAL_BIRTHDAY_2":
					$arSqlSearch[] = "U.PERSONAL_BIRTHDAY<=".$DB->CharToDateFunction($DB->ForSql($val), "SHORT");
					break;
				case "PERSONAL_BIRTHDAY_DATE":
					$arSqlSearch[] = "DATE_FORMAT(U.PERSONAL_BIRTHDAY, '%m-%d') = '".$DB->ForSql($val)."'";
					break;
				case "KEYWORDS":
					$arSqlSearch[] = GetFilterQuery(implode(",",$arFields), $val);
					break;
				case "CHECK_SUBORDINATE":
					if(is_array($val))
					{
						$strSubord = "0";
						foreach($val as $grp)
							$strSubord .= ",".intval($grp);
						if(intval($arFilter["CHECK_SUBORDINATE_AND_OWN"]) > 0)
							$arSqlSearch[] = "(U.ID=".intval($arFilter["CHECK_SUBORDINATE_AND_OWN"])." OR NOT EXISTS(SELECT 'x' FROM b_user_group UGS WHERE UGS.USER_ID=U.ID AND UGS.GROUP_ID NOT IN (".$strSubord.")))";
						else
							$arSqlSearch[] = "NOT EXISTS(SELECT 'x' FROM b_user_group UGS WHERE UGS.USER_ID=U.ID AND UGS.GROUP_ID NOT IN (".$strSubord."))";
					}
					break;
				case "NOT_ADMIN":
					if($val !== true)
						break;
					$arSqlSearch[] = "not exists (SELECT * FROM b_user_group UGNA WHERE UGNA.USER_ID=U.ID AND UGNA.GROUP_ID = 1)";
					break;
				case "LAST_ACTIVITY":
					if ($val === false)
						$arSqlSearch[] = "U.LAST_ACTIVITY_DATE IS NULL";
					elseif (intval($val)>0)
						$arSqlSearch[] = "U.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".intval($val)." SECOND)";
					break;
				case "!LAST_ACTIVITY":
					if ($val === false)
						$arSqlSearch[] = "U.LAST_ACTIVITY_DATE IS NOT NULL";
					break;
				case "INTRANET_USERS":
					$arSqlSearch[] = "U.ACTIVE = 'Y' AND U.LAST_LOGIN IS NOT NULL AND EXISTS(SELECT 'x' FROM b_utm_user UF1, b_user_field F1 WHERE F1.ENTITY_ID = 'USER' AND F1.FIELD_NAME = 'UF_DEPARTMENT' AND UF1.FIELD_ID = F1.ID AND UF1.VALUE_ID = U.ID AND UF1.VALUE_INT IS NOT NULL AND UF1.VALUE_INT <> 0)";
					break;
				case "IS_REAL_USER":
					if($val === true || $val === 'Y')
					{
						$arSqlSearch[] = "U.EXTERNAL_AUTH_ID NOT IN ('".join("', '", CUser::GetExternalUserTypes())."') OR U.EXTERNAL_AUTH_ID IS NULL";
					}
					else
					{
						$arSqlSearch[] = "U.EXTERNAL_AUTH_ID IN ('".join("', '", CUser::GetExternalUserTypes())."')";
					}
					break;
				default:
					if(in_array($key, $arFields))
						$arSqlSearch[] = GetFilterQuery('U.'.$key, $val, ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set? "N" : "Y"));
				}
			}
		}

		$arSqlOrder = array();
		foreach ($arOrder as $field => $dir)
		{
			$field = strtoupper($field);
			if(strtolower($dir) <> "asc")
			{
				$dir = "desc";
				if ($bSingleBy)
					$order = "desc";
			}

			if($field == "CURRENT_BIRTHDAY")
			{
				$cur_year = intval(date('Y'));
				$arSqlOrder[$field] = "IF(ISNULL(U.PERSONAL_BIRTHDAY), '9999-99-99', IF (
					DATE_FORMAT(U.PERSONAL_BIRTHDAY, '".$cur_year."-%m-%d') < DATE_FORMAT(DATE_ADD(".$DB->CurrentTimeFunction().", INTERVAL ".CTimeZone::GetOffset()." SECOND), '%Y-%m-%d'),
					DATE_FORMAT(U.PERSONAL_BIRTHDAY, '".($cur_year + 1)."-%m-%d'),
					DATE_FORMAT(U.PERSONAL_BIRTHDAY, '".$cur_year."-%m-%d')
				)) ".$dir;
			}
			elseif($field == "IS_ONLINE")
			{
				$arSelectFields[$field] = "IF(U.LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".$online_interval." SECOND), 'Y', 'N') IS_ONLINE";
				$arSqlOrder[$field] = "IS_ONLINE ".$dir;
			}
			elseif(in_array($field,$arFields_all))
			{
				$arSqlOrder[$field] = "U.".$field." ".$dir;
			}
			elseif($s = $obUserFieldsSql->GetOrder($field))
			{
				$arSqlOrder[$field] = strtoupper($s)." ".$dir;
			}
			elseif(preg_match('/^RATING_(\d+)$/i', $field, $matches))
			{
				$ratingId = intval($matches[1]);
				if ($ratingId > 0)
				{
					$arSqlOrder[$field] = $field."_ISNULL ASC, ".$field." ".$dir;
					$arParams['SELECT'][] = $field;
				}
				else
				{
					$field = "TIMESTAMP_X";
					$arSqlOrder[$field] = "U.".$field." ".$dir;
					if ($bSingleBy)
						$by = strtolower($field);
				}
			}
			elseif ($field == 'FULL_NAME')
			{
				$arSqlOrder[$field] = sprintf(
					"IF(U.LAST_NAME IS NULL OR U.LAST_NAME = '', 1, 0) %1\$s,
					IF(U.LAST_NAME IS NULL OR U.LAST_NAME = '', 1, U.LAST_NAME) %1\$s,
					IF(U.NAME IS NULL OR U.NAME = '', 1, 0) %1\$s,
					IF(U.NAME IS NULL OR U.NAME = '', 1, U.NAME) %1\$s,
					IF(U.SECOND_NAME IS NULL OR U.SECOND_NAME = '', 1, 0) %1\$s,
					IF(U.SECOND_NAME IS NULL OR U.SECOND_NAME = '', 1, U.SECOND_NAME) %1\$s,
					U.LOGIN %1\$s", $dir
				);
			}
			else
			{
				$field = "TIMESTAMP_X";
				$arSqlOrder[$field] = "U.".$field." ".$dir;
				if ($bSingleBy)
					$by = strtolower($field);
			}
		}

		$userFieldsSelect = $obUserFieldsSql->GetSelect();
		$arSqlSearch[] = $obUserFieldsSql->GetFilter();
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

		$sSelect = ($obUserFieldsSql->GetDistinct()? "DISTINCT " : "")
			.implode(', ',$arSelectFields)."
			".$userFieldsSelect."
		";

		if (is_array($arParams['SELECT']))
		{
			$arRatingInSelect = array();
			foreach ($arParams['SELECT'] as $column)
			{
				if(preg_match('/^RATING_(\d+)$/i', $column, $matches))
				{
					$ratingId = intval($matches[1]);
					if ($ratingId > 0 && !in_array($ratingId, $arRatingInSelect))
					{
						$sSelect .= ", RR".$ratingId.".CURRENT_POSITION IS NULL as RATING_".$ratingId."_ISNULL";
						$sSelect .= ", RR".$ratingId.".CURRENT_VALUE as RATING_".$ratingId;
						$sSelect .= ", RR".$ratingId.".CURRENT_VALUE as RATING_".$ratingId."_CURRENT_VALUE";
						$sSelect .= ", RR".$ratingId.".PREVIOUS_VALUE as RATING_".$ratingId."_PREVIOUS_VALUE";
						$sSelect .= ", RR".$ratingId.".CURRENT_POSITION as RATING_".$ratingId."_CURRENT_POSITION";
						$sSelect .= ", RR".$ratingId.".PREVIOUS_POSITION as RATING_".$ratingId."_PREVIOUS_POSITION";
						$strJoin .=	" LEFT JOIN  b_rating_results RR".$ratingId."
							ON RR".$ratingId.".RATING_ID=".$ratingId."
							and RR".$ratingId.".ENTITY_TYPE_ID = 'USER'
							and RR".$ratingId.".ENTITY_ID = U.ID ";
						$arRatingInSelect[] = $ratingId;
					}
				}
			}
		}
		$strFrom = "
			FROM
				b_user U
				".$obUserFieldsSql->GetJoin("U.ID")."
				".$strJoin."
			WHERE
				".$strSqlSearch."
			";

		$strSqlOrder = '';
		if (!empty($arSqlOrder))
			$strSqlOrder = 'ORDER BY '.implode(', ', $arSqlOrder);

		$strSql = "SELECT ".$sSelect.$strFrom.$strSqlOrder;

		if(array_key_exists("NAV_PARAMS", $arParams) && is_array($arParams["NAV_PARAMS"]))
		{
			$nTopCount = intval($arParams['NAV_PARAMS']['nTopCount']);
			if($nTopCount > 0)
			{
				$strSql = $DB->TopSql($strSql, $nTopCount);
				$res = $DB->Query($strSql, false, $err_mess.__LINE__);
				if($userFieldsSelect <> '')
					$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("USER"));
			}
			else
			{
				$res_cnt = $DB->Query("SELECT COUNT(".($obUserFieldsSql->GetDistinct()? "DISTINCT ":"")."U.ID) as C ".$strFrom);
				$res_cnt = $res_cnt->Fetch();
				$res = new CDBResult();
				if($userFieldsSelect <> '')
					$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("USER"));
				$res->NavQuery($strSql, $res_cnt["C"], $arParams["NAV_PARAMS"]);
			}
		}
		else
		{
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			if($userFieldsSelect <> '')
				$res->SetUserFields($USER_FIELD_MANAGER->GetUserFields("USER"));
		}

		$res->is_filtered = IsFiltered($strSqlSearch);
		return $res;
	}

	public static function IsOnLine($id, $interval = null)
	{
		global $DB;

		$id = intval($id);
		if ($id <= 0)
		{
			return false;
		}

		if (is_null($interval))
		{
			$interval = CUser::GetSecondsForLimitOnline();
		}
		else
		{
			$interval = intval($interval);
			if ($interval <= 0)
			{
				$interval = CUser::GetSecondsForLimitOnline();
			}
		}

		$dbRes = $DB->Query("SELECT 'x' FROM b_user WHERE ID = ".$id." AND LAST_ACTIVITY_DATE > DATE_SUB(NOW(), INTERVAL ".$interval." SECOND)");
		return $arRes = $dbRes->Fetch()? true: false;
	}
}

class CGroup extends CAllGroup
{
	public static function err_mess()
	{
		return "<br>Class: CGroup<br>File: ".__FILE__;
	}

	public function Add($arFields)
	{
		/** @global CMain $APPLICATION */
		global $DB, $APPLICATION;

		if(!$this->CheckFields($arFields))
			return false;

		foreach(GetModuleEvents("main", "OnBeforeGroupAdd", true) as $arEvent)
		{
			$bEventRes = ExecuteModuleEventEx($arEvent, array(&$arFields));
			if($bEventRes===false)
			{
				if($err = $APPLICATION->GetException())
					$this->LAST_ERROR .= $err->GetString()."<br>";
				else
					$this->LAST_ERROR .= "Unknown error in OnBeforeGroupAdd handler."."<br>";
				return false;
			}
		}

		if(is_set($arFields, "ACTIVE") && $arFields["ACTIVE"]!="Y")
			$arFields["ACTIVE"]="N";

		$arInsert = $DB->PrepareInsert("b_group", $arFields);

		$strSql = "
			INSERT INTO b_group (
				".$arInsert[0]."
			) VALUES(
				".$arInsert[1]."
			)
		";

		$DB->Query($strSql);
		$ID = $DB->LastID();

		if (count($arFields["USER_ID"]) > 0)
		{
			if (is_array($arFields["USER_ID"][0]) && count($arFields["USER_ID"][0]) > 0)
			{
				$arTmp = array();
				foreach ($arFields["USER_ID"] as $userId)
				{
					if (intval($userId["USER_ID"]) > 0
						&& !in_array(intval($userId["USER_ID"]), $arTmp))
					{
						$arInsert = $DB->PrepareInsert("b_user_group", $userId);

						$strSql =
							"INSERT INTO b_user_group(GROUP_ID, ".$arInsert[0].") ".
							"VALUES(".$ID.", ".$arInsert[1].")";
						$DB->Query($strSql);

						$arTmp[] = intval($userId["USER_ID"]);
					}
				}
			}
			else
			{
				$strUsers = "0";
				foreach ($arFields["USER_ID"] as $userId)
					$strUsers.=",".intval($userId);

				$strSql =
					"INSERT INTO b_user_group(GROUP_ID, USER_ID) ".
					"SELECT ".$ID.", ID ".
					"FROM b_user ".
					"WHERE ID in (".$strUsers.")";

				$DB->Query($strSql);
			}
			CUser::clearUserGroupCache();
		}

		$arFields["ID"] = $ID;

		foreach (GetModuleEvents("main", "OnAfterGroupAdd", true) as $arEvent)
			ExecuteModuleEventEx($arEvent, array(&$arFields));

		return $ID;
	}

	public static function GetDropDownList($strSqlSearch="and ACTIVE='Y'", $strSqlOrder="ORDER BY C_SORT, NAME, ID")
	{
		global $DB;
		$err_mess = (CGroup::err_mess())."<br>Function: GetDropDownList<br>Line: ";
		$strSql = "
			SELECT
				ID as REFERENCE_ID,
				concat(NAME, ' [', ID, ']') as REFERENCE
			FROM
				b_group
			WHERE
				1=1
			$strSqlSearch
			$strSqlOrder
			";
		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $res;
	}

	public static function GetList(&$by, &$order, $arFilter=Array(), $SHOW_USERS_AMOUNT="N")
	{
		global $DB;

		$err_mess = (CGroup::err_mess())."<br>Function: GetList<br>Line: ";
		$arSqlSearch = $arSqlSearch_h = array();
		$strSqlSearch_h = "";
		if(is_array($arFilter))
		{
			foreach($arFilter as $key => $val)
			{
				if(is_array($val))
				{
					if(count($val) <= 0)
						continue;
				}
				else
				{
					if (strlen($val)<=0 || "$val"=="NOT_REF")
						continue;
				}
				$key = strtoupper($key);
				$match_value_set = array_key_exists($key."_EXACT_MATCH", $arFilter);
				switch($key)
				{
					case "ID":
						$match = ($match_value_set && $arFilter[$key."_EXACT_MATCH"]=="N") ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("G.ID", $val, $match);
						break;
					case "TIMESTAMP_1":
						$arSqlSearch[] = "G.TIMESTAMP_X >= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y"),"d.m.Y")."')";
						break;
					case "TIMESTAMP_2":
						$arSqlSearch[] = "G.TIMESTAMP_X <= FROM_UNIXTIME('".MkDateTime(FmtDate($val,"D.M.Y")." 23:59:59","d.m.Y")."')";
						break;
					case "ACTIVE":
						$arSqlSearch[] = ($val=="Y") ? "G.ACTIVE='Y'" : "G.ACTIVE='N'";
						break;
					case "ADMIN":
						if(COption::GetOptionString("main", "controller_member", "N") == "Y" && COption::GetOptionString("main", "~controller_limited_admin", "N") == "Y")
						{
							if($val=="Y")
								$arSqlSearch[] =  "G.ID=0";
							break;
						}
						else
							$arSqlSearch[] = ($val=="Y") ? "G.ID=1" : "G.ID>1";
						break;
					case "NAME":
						$match = ($match_value_set && $arFilter[$key."_EXACT_MATCH"]=="Y") ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.NAME", $val, $match);
						break;
					case "STRING_ID":
						$match = ($match_value_set && $arFilter[$key."_EXACT_MATCH"]=="N") ? "Y" : "N";
						$arSqlSearch[] = GetFilterQuery("G.STRING_ID", $val, $match);
						break;
					case "DESCRIPTION":
						$match = ($match_value_set && $arFilter[$key."_EXACT_MATCH"]=="Y") ? "N" : "Y";
						$arSqlSearch[] = GetFilterQuery("G.DESCRIPTION", $val, $match);
						break;
					case "USERS_1":
						$SHOW_USERS_AMOUNT="Y";
						$arSqlSearch_h[] = "USERS>=".intval($val);
						break;
					case "USERS_2":
						$SHOW_USERS_AMOUNT="Y";
						$arSqlSearch_h[] = "USERS<=".intval($val);
						break;
					case "ANONYMOUS":
						if($val == 'Y' || $val == 'N')
							$arSqlSearch[] = "G.ANONYMOUS='".$val."'";
						break;
				}
			}
			foreach($arSqlSearch_h as $condition)
				$strSqlSearch_h .= " and (".$condition.") ";
		}


		if(strtolower($by) == "id")				$strSqlOrder = " ORDER BY G.ID ";
		elseif(strtolower($by) == "active")		$strSqlOrder = " ORDER BY G.ACTIVE ";
		elseif(strtolower($by) == "timestamp_x")	$strSqlOrder = " ORDER BY G.TIMESTAMP_X ";
		elseif(strtolower($by) == "c_sort")		$strSqlOrder = " ORDER BY G.C_SORT ";
		elseif(strtolower($by) == "sort")			$strSqlOrder = " ORDER BY G.C_SORT, G.NAME, G.ID ";
		elseif(strtolower($by) == "name")			$strSqlOrder = " ORDER BY G.NAME ";
		elseif(strtolower($by) == "string_id")		$strSqlOrder = " ORDER BY G.STRING_ID ";
		elseif(strtolower($by) == "description")		$strSqlOrder = " ORDER BY G.DESCRIPTION ";
		elseif(strtolower($by) == "anonymous")		$strSqlOrder = " ORDER BY G.ANONYMOUS ";
		elseif(strtolower($by) == "dropdown")		$strSqlOrder = " ORDER BY C_SORT, NAME ";
		elseif(strtolower($by) == "users")
		{
			$strSqlOrder = " ORDER BY USERS ";
			$SHOW_USERS_AMOUNT="Y";
		}
		else
		{
			$strSqlOrder = " ORDER BY G.C_SORT ";
			$by = "c_sort";
		}

		if(strtolower($order)=="desc")
		{
			$strSqlOrder .= " desc ";
			$order = "desc";
		}
		else
		{
			$strSqlOrder .= " asc ";
			$order = "asc";
		}

		$str_USERS = $str_TABLE = "";
		if($SHOW_USERS_AMOUNT=="Y")
		{
			$str_USERS = "count(distinct U.USER_ID)						USERS,";
			$str_TABLE = "LEFT JOIN b_user_group U ON (U.GROUP_ID=G.ID AND ((U.DATE_ACTIVE_FROM IS NULL) OR (U.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) AND ((U.DATE_ACTIVE_TO IS NULL) OR (U.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction().")))";
		}
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = "
			SELECT
				G.ID, G.ACTIVE, G.C_SORT, G.ANONYMOUS, G.NAME, G.DESCRIPTION, G.STRING_ID,
				".$str_USERS."
				G.ID										REFERENCE_ID,
				concat(G.NAME, ' [', G.ID, ']')					REFERENCE,
				".$DB->DateToCharFunction("G.TIMESTAMP_X")."	TIMESTAMP_X
			FROM
				b_group G
			".$str_TABLE."
			WHERE
			".$strSqlSearch."
			GROUP BY
				G.ID, G.ACTIVE, G.C_SORT, G.TIMESTAMP_X, G.ANONYMOUS, G.NAME, G.STRING_ID, G.DESCRIPTION
			HAVING
				1=1
				".$strSqlSearch_h."
			".$strSqlOrder;

		$res = $DB->Query($strSql, false, $err_mess.__LINE__);
		$res->is_filtered = (IsFiltered($strSqlSearch) || strlen($strSqlSearch_h)>0);
		return $res;
	}

	//*************** COMMON UTILS *********************/
	public static function GetFilterOperation($key)
	{
		$strNegative = "N";
		if (substr($key, 0, 1)=="!")
		{
			$key = substr($key, 1);
			$strNegative = "Y";
		}

		$strOrNull = "N";
		if (substr($key, 0, 1)=="+")
		{
			$key = substr($key, 1);
			$strOrNull = "Y";
		}

		if (substr($key, 0, 2)==">=")
		{
			$key = substr($key, 2);
			$strOperation = ">=";
		}
		elseif (substr($key, 0, 1)==">")
		{
			$key = substr($key, 1);
			$strOperation = ">";
		}
		elseif (substr($key, 0, 2)=="<=")
		{
			$key = substr($key, 2);
			$strOperation = "<=";
		}
		elseif (substr($key, 0, 1)=="<")
		{
			$key = substr($key, 1);
			$strOperation = "<";
		}
		elseif (substr($key, 0, 1)=="@")
		{
			$key = substr($key, 1);
			$strOperation = "IN";
		}
		elseif (substr($key, 0, 1)=="~")
		{
			$key = substr($key, 1);
			$strOperation = "LIKE";
		}
		elseif (substr($key, 0, 1)=="%")
		{
			$key = substr($key, 1);
			$strOperation = "QUERY";
		}
		else
		{
			$strOperation = "=";
		}

		return array("FIELD" => $key, "NEGATIVE" => $strNegative, "OPERATION" => $strOperation, "OR_NULL" => $strOrNull);
	}

	public static function PrepareSql(&$arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields)
	{
		global $DB;

		$strSqlSelect = "";
		$strSqlFrom = "";
		$strSqlWhere = "";
		$strSqlGroupBy = "";

		$arGroupByFunct = array("COUNT", "AVG", "MIN", "MAX", "SUM");

		$arAlreadyJoined = array();

		// GROUP BY -->
		if (is_array($arGroupBy) && count($arGroupBy)>0)
		{
			$arSelectFields = $arGroupBy;
			foreach ($arGroupBy as $key => $val)
			{
				$val = strtoupper($val);
				$key = strtoupper($key);
				if (array_key_exists($val, $arFields) && !in_array($key, $arGroupByFunct))
				{
					if (strlen($strSqlGroupBy) > 0)
						$strSqlGroupBy .= ", ";
					$strSqlGroupBy .= $arFields[$val]["FIELD"];

					if (isset($arFields[$val]["FROM"])
						&& strlen($arFields[$val]["FROM"]) > 0
						&& !in_array($arFields[$val]["FROM"], $arAlreadyJoined))
					{
						if (strlen($strSqlFrom) > 0)
							$strSqlFrom .= " ";
						$strSqlFrom .= $arFields[$val]["FROM"];
						$arAlreadyJoined[] = $arFields[$val]["FROM"];
					}
				}
			}
		}
		// <-- GROUP BY

		// SELECT -->
		$arFieldsKeys = array_keys($arFields);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSqlSelect = "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT ";
		}
		else
		{
			if (isset($arSelectFields) && !is_array($arSelectFields) && is_string($arSelectFields) && strlen($arSelectFields)>0 && array_key_exists($arSelectFields, $arFields))
				$arSelectFields = array($arSelectFields);

			if (!isset($arSelectFields)
				|| !is_array($arSelectFields)
				|| count($arSelectFields)<=0
				|| in_array("*", $arSelectFields))
			{
				foreach ($arFields as $FIELD_ID => $arField)
				{
					if (isset($arField["WHERE_ONLY"])
						&& $arField["WHERE_ONLY"] == "Y")
					{
						continue;
					}

					if (strlen($strSqlSelect) > 0)
						$strSqlSelect .= ", ";

					if ($arField["TYPE"] == "datetime")
						$strSqlSelect .= $DB->DateToCharFunction($arField["FIELD"], "FULL")." as ".$FIELD_ID;
					elseif ($arField["TYPE"] == "date")
						$strSqlSelect .= $DB->DateToCharFunction($arField["FIELD"], "SHORT")." as ".$FIELD_ID;
					else
						$strSqlSelect .= $arField["FIELD"]." as ".$FIELD_ID;

					if (isset($arField["FROM"])
						&& strlen($arField["FROM"]) > 0
						&& !in_array($arField["FROM"], $arAlreadyJoined))
					{
						if (strlen($strSqlFrom) > 0)
							$strSqlFrom .= " ";
						$strSqlFrom .= $arField["FROM"];
						$arAlreadyJoined[] = $arField["FROM"];
					}
				}
			}
			else
			{
				foreach ($arSelectFields as $key => $val)
				{
					$val = strtoupper($val);
					$key = strtoupper($key);
					if (array_key_exists($val, $arFields))
					{
						if (strlen($strSqlSelect) > 0)
							$strSqlSelect .= ", ";

						if (in_array($key, $arGroupByFunct))
						{
							$strSqlSelect .= $key."(".$arFields[$val]["FIELD"].") as ".$val;
						}
						else
						{
							if ($arFields[$val]["TYPE"] == "datetime")
								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"], "FULL")." as ".$val;
							elseif ($arFields[$val]["TYPE"] == "date")
								$strSqlSelect .= $DB->DateToCharFunction($arFields[$val]["FIELD"], "SHORT")." as ".$val;
							else
								$strSqlSelect .= $arFields[$val]["FIELD"]." as ".$val;
						}

						if (isset($arFields[$val]["FROM"])
							&& strlen($arFields[$val]["FROM"]) > 0
							&& !in_array($arFields[$val]["FROM"], $arAlreadyJoined))
						{
							if (strlen($strSqlFrom) > 0)
								$strSqlFrom .= " ";
							$strSqlFrom .= $arFields[$val]["FROM"];
							$arAlreadyJoined[] = $arFields[$val]["FROM"];
						}
					}
				}
			}

			if (strlen($strSqlGroupBy) > 0)
			{
				if (strlen($strSqlSelect) > 0)
					$strSqlSelect .= ", ";
				$strSqlSelect .= "COUNT(%%_DISTINCT_%% ".$arFields[$arFieldsKeys[0]]["FIELD"].") as CNT";
			}
			else
				$strSqlSelect = "%%_DISTINCT_%% ".$strSqlSelect;
		}
		// <-- SELECT

		// WHERE -->
		$arSqlSearch = Array();

		if (is_array($arFilter))
		{
			foreach ($arFilter as $key => $vals)
			{
				if (!is_array($vals))
					$vals = array($vals);

				$key_res = CGroup::GetFilterOperation($key);
				$key = $key_res["FIELD"];
				$strNegative = $key_res["NEGATIVE"];
				$strOperation = $key_res["OPERATION"];
				$strOrNull = $key_res["OR_NULL"];

				if (array_key_exists($key, $arFields))
				{
					$arSqlSearch_tmp = array();
					foreach($vals as $val)
					{
						if (isset($arFields[$key]["WHERE"]))
						{
							$arSqlSearch_tmp1 = call_user_func_array(
									$arFields[$key]["WHERE"],
									array($val, $key, $strOperation, $strNegative, $arFields[$key]["FIELD"], $arFields, $arFilter)
								);
							if ($arSqlSearch_tmp1 !== false)
								$arSqlSearch_tmp[] = $arSqlSearch_tmp1;
						}
						else
						{
							if ($arFields[$key]["TYPE"] == "int")
							{
								if (intval($val) <= 0)
									$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL OR ".$arFields[$key]["FIELD"]." <= 0)";
								else
									$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".intval($val)." )";
							}
							elseif ($arFields[$key]["TYPE"] == "double")
							{
								$val = str_replace(",", ".", $val);
								if (DoubleVal($val) <= 0)
									$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL OR ".$arFields[$key]["FIELD"]." <= 0)";
								else
									$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".DoubleVal($val)." )";
							}
							elseif ($arFields[$key]["TYPE"] == "string" || $arFields[$key]["TYPE"] == "char")
							{
								if ($strOperation == "QUERY")
								{
									$arSqlSearch_tmp[] = GetFilterQuery($arFields[$key]["FIELD"], $val, "Y");
								}
								else
								{
									if (strlen($val) <= 0)
										$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL OR LENGTH(".$arFields[$key]["FIELD"].")<=0)";
									else
										$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." '".$DB->ForSql($val)."' )";
								}
							}
							elseif ($arFields[$key]["TYPE"] == "datetime")
							{
								if (strlen($val) <= 0)
									$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL)";
								else
									$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "FULL").")";
							}
							elseif ($arFields[$key]["TYPE"] == "date")
							{
								if (strlen($val) <= 0)
									$arSqlSearch_tmp[] = ($strNegative=="Y"?"NOT":"")."(".$arFields[$key]["FIELD"]." IS NULL)";
								else
									$arSqlSearch_tmp[] = ($strNegative=="Y"?" ".$arFields[$key]["FIELD"]." IS NULL OR NOT ":"")."(".$arFields[$key]["FIELD"]." ".$strOperation." ".$DB->CharToDateFunction($DB->ForSql($val), "SHORT").")";
							}
						}
					}

					if (isset($arFields[$key]["FROM"])
						&& strlen($arFields[$key]["FROM"]) > 0
						&& !in_array($arFields[$key]["FROM"], $arAlreadyJoined))
					{
						if (strlen($strSqlFrom) > 0)
							$strSqlFrom .= " ";
						$strSqlFrom .= $arFields[$key]["FROM"];
						$arAlreadyJoined[] = $arFields[$key]["FROM"];
					}

					$strSqlSearch_tmp = "";
					foreach ($arSqlSearch_tmp as $condition)
					{
						if ($strSqlSearch_tmp != "")
							$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
						$strSqlSearch_tmp .= "(".$condition.")";
					}
					if ($strOrNull == "Y")
					{
						if ($strSqlSearch_tmp != "")
							$strSqlSearch_tmp .= ($strNegative=="Y" ? " AND " : " OR ");
						$strSqlSearch_tmp .= "(".$arFields[$key]["FIELD"]." IS ".($strNegative=="Y" ? "NOT " : "")."NULL)";
					}

					if ($strSqlSearch_tmp != "")
						$arSqlSearch[] = "(".$strSqlSearch_tmp.")";
				}
			}
		}

		foreach ($arSqlSearch as $condition)
		{
			if ($strSqlWhere != "")
				$strSqlWhere .= " AND ";
			$strSqlWhere .= "(".$condition.")";
		}
		// <-- WHERE

		// ORDER BY -->
		$arSqlOrder = Array();
		foreach ($arOrder as $by => $order)
		{
			$by = strtoupper($by);
			$order = strtoupper($order);
			if ($order != "ASC")
				$order = "DESC";

			if (array_key_exists($by, $arFields))
			{
				$arSqlOrder[] = " ".$arFields[$by]["FIELD"]." ".$order." ";

				if (isset($arFields[$by]["FROM"])
					&& strlen($arFields[$by]["FROM"]) > 0
					&& !in_array($arFields[$by]["FROM"], $arAlreadyJoined))
				{
					if (strlen($strSqlFrom) > 0)
						$strSqlFrom .= " ";
					$strSqlFrom .= $arFields[$by]["FROM"];
					$arAlreadyJoined[] = $arFields[$by]["FROM"];
				}
			}
		}

		$strSqlOrderBy = implode(", ", $arSqlOrder);
		// <-- ORDER BY

		return array(
				"SELECT" => $strSqlSelect,
				"FROM" => $strSqlFrom,
				"WHERE" => $strSqlWhere,
				"GROUPBY" => $strSqlGroupBy,
				"ORDERBY" => $strSqlOrderBy
			);
	}

	public static function GetListEx($arOrder = Array("ID" => "DESC"), $arFilter = Array(), $arGroupBy = false, $arNavStartParams = false, $arSelectFields = array())
	{
		global $DB;

		if (count($arSelectFields) <= 0)
			$arSelectFields = array("ID", "TIMESTAMP_X", "ACTIVE", "C_SORT", "ANONYMOUS", "NAME", "DESCRIPTION");

		// FIELDS -->
		$arFields = array(
				"ID" => array("FIELD" => "G.ID", "TYPE" => "int"),
				"TIMESTAMP_X" => array("FIELD" => "G.TIMESTAMP_X", "TYPE" => "datetime"),
				"ACTIVE" => array("FIELD" => "G.ACTIVE", "TYPE" => "char"),
				"C_SORT" => array("FIELD" => "G.C_SORT", "TYPE" => "int"),
				"ANONYMOUS" => array("FIELD" => "G.ANONYMOUS", "TYPE" => "char"),
				"NAME" => array("FIELD" => "G.NAME", "TYPE" => "string"),
				"STRING_ID" => array("FIELD" => "G.STRING_ID", "TYPE" => "string"),
				"DESCRIPTION" => array("FIELD" => "G.DESCRIPTION", "TYPE" => "string"),
				"USER_USER_ID" => array("FIELD" => "UG.USER_ID", "TYPE" => "int", "FROM" => "INNER JOIN b_user_group UG ON (G.ID = UG.GROUP_ID)"),
				"USER_GROUP_ID" => array("FIELD" => "UG.GROUP_ID", "TYPE" => "string", "FROM" => "INNER JOIN b_user_group UG ON (G.ID = UG.GROUP_ID)"),
				"USER_DATE_ACTIVE_FROM" => array("FIELD" => "UG.DATE_ACTIVE_FROM", "TYPE" => "datetime", "FROM" => "INNER JOIN b_user_group UG ON (G.ID = UG.GROUP_ID)"),
				"USER_DATE_ACTIVE_TO" => array("FIELD" => "UG.DATE_ACTIVE_TO", "TYPE" => "datetime", "FROM" => "INNER JOIN b_user_group UG ON (G.ID = UG.GROUP_ID)")
			);
		// <-- FIELDS

		$arSqls = CGroup::PrepareSql($arFields, $arOrder, $arFilter, $arGroupBy, $arSelectFields);

		$arSqls["SELECT"] = str_replace("%%_DISTINCT_%%", "DISTINCT", $arSqls["SELECT"]);

		if (is_array($arGroupBy) && count($arGroupBy)==0)
		{
			$strSql =
				"SELECT ".$arSqls["SELECT"]." ".
				"FROM b_group G ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			$dbRes = $DB->Query($strSql);
			if ($arRes = $dbRes->Fetch())
				return $arRes["CNT"];
			else
				return False;
		}

		$strSql =
			"SELECT ".$arSqls["SELECT"]." ".
			"FROM b_group G ".
			"	".$arSqls["FROM"]." ";
		if (strlen($arSqls["WHERE"]) > 0)
			$strSql .= "WHERE ".$arSqls["WHERE"]." ";
		if (strlen($arSqls["GROUPBY"]) > 0)
			$strSql .= "GROUP BY ".$arSqls["GROUPBY"]." ";
		if (strlen($arSqls["ORDERBY"]) > 0)
			$strSql .= "ORDER BY ".$arSqls["ORDERBY"]." ";

		if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])<=0)
		{
			$strSql_tmp =
				"SELECT COUNT('x') as CNT ".
				"FROM b_group G ".
				"	".$arSqls["FROM"]." ";
			if (strlen($arSqls["WHERE"]) > 0)
				$strSql_tmp .= "WHERE ".$arSqls["WHERE"]." ";
			if (strlen($arSqls["GROUPBY"]) > 0)
				$strSql_tmp .= "GROUP BY ".$arSqls["GROUPBY"]." ";

			$dbRes = $DB->Query($strSql_tmp);
			$cnt = 0;
			if (strlen($arSqls["GROUPBY"]) <= 0)
			{
				if ($arRes = $dbRes->Fetch())
					$cnt = $arRes["CNT"];
			}
			else
			{
				// ÒÎËÜÊÎ ÄËß MYSQL!!! ÄËß ORACLE ÄÐÓÃÎÉ ÊÎÄ
				$cnt = $dbRes->SelectedRowsCount();
			}

			$dbRes = new CDBResult();
			$dbRes->NavQuery($strSql, $cnt, $arNavStartParams);
		}
		else
		{
			if (is_array($arNavStartParams) && intval($arNavStartParams["nTopCount"])>0)
				$strSql .= "LIMIT ".intval($arNavStartParams["nTopCount"]);
			$dbRes = $DB->Query($strSql);
		}

		return $dbRes;
	}

	public static function GetByID($ID, $SHOW_USERS_AMOUNT = "N")
	{
		global $DB;

		$err_mess = (CGroup::err_mess())."<br>Function: GetList<br>Line: ";
		$ID = intval($ID);

		$strSql = "SELECT G.ID, G.ACTIVE, G.C_SORT, G.ANONYMOUS, G.NAME, G.STRING_ID, G.DESCRIPTION, ".$DB->DateToCharFunction("G.TIMESTAMP_X")." as TIMESTAMP_X ";

		if ($SHOW_USERS_AMOUNT == "Y")
			$strSql .= ", count(distinct U.USER_ID) USERS ";
		else
			$strSql .= ", G.SECURITY_POLICY ";

		$strSql .= "FROM b_group G ";

		if ($SHOW_USERS_AMOUNT == "Y")
			$strSql .= "LEFT JOIN b_user_group U ON (U.GROUP_ID=G.ID AND ((U.DATE_ACTIVE_FROM IS NULL) OR (U.DATE_ACTIVE_FROM <= ".$DB->CurrentTimeFunction().")) AND ((U.DATE_ACTIVE_TO IS NULL) OR (U.DATE_ACTIVE_TO >= ".$DB->CurrentTimeFunction()."))) ";

		$strSql .= "WHERE G.ID = ".$ID." ";

		if ($SHOW_USERS_AMOUNT == "Y")
			$strSql .= "GROUP BY G.ID, G.ACTIVE, G.C_SORT, G.TIMESTAMP_X, G.ANONYMOUS, G.NAME, G.STRING_ID, G.DESCRIPTION";

		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		return $z;
	}
}

class CTask extends CAllTask
{
}

class COperation extends CAllOperation
{
}
