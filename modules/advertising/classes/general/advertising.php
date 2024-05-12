<?php
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage advertising
 * @copyright 2001-2014 Bitrix
 */

use \Bitrix\Main\Application;

global
	$arrViewedBanners,		// баннеры показанные на данной странице
	$arrADV_KEYWORDS,		// массив ключевых слов для страницы
	$strAdvCurUri,
	$nRandom1,
	$nRandom2,
	$nRandom3,
	$nRandom4,
	$nRandom5,
	$CACHE_ADVERTISING,
	$arrADV_VIEWED_BANNERS;

$arrADV_KEYWORDS = array();
$strAdvCurUri = false;
$arrViewedBanners = array(0);
$CACHE_ADVERTISING = array(
	"BANNERS_ALL" => array(),
	"BANNERS_CNT" => array(),
	"CONTRACTS_ALL" => array(),
	"CONTRACTS_CNT" => array(),
);
$arrADV_VIEWED_BANNERS = false;
$weightCalculated = false;

$nRandom1 = 4689*mt_rand(999, 31999);
$nRandom2 = 4689*mt_rand(999, 31999);
$nRandom3 = 4689*mt_rand(999, 31999);
$nRandom4 = 4689*mt_rand(999, 31999);
$nRandom5 = 4689*mt_rand(999, 31999);

// Параметр указывающий допустимое процентное отклонение (превышение) от равномерности
// прогресса ротации банера
define("BANNER_UNIFORMITY_DIVERGENCE_COEF", 0.05);

/*****************************************************************
				Класс "Рекламный контракт"
*****************************************************************/

class CAdvContract_all
{
	public static function err_mess()
	{
		$module_id = "advertising";
		return "<br>Module: ".$module_id."<br>Class: CAdvContract_all<br>File: ".__FILE__;
	}

	public static function GetNextSort()
	{
		$rsContracts = CAdvContract::GetList("s_sort", "desc", array("ID" => "~1", "ID_EXACT_MATCH" => "Y"));
		$arContract = $rsContracts->Fetch();
		return intval($arContract["SORT"])+10;
	}

	/*****************************************************************
				Группа функций по работе с ролями на модуль

	Идентификаторы ролей:

	D - доступ закрыт
	R - рекламодатель
	T - менеджер баннеров
	V - демо-доступ
	W - администратор рекламы

	*****************************************************************/

	public static function GetDeniedRoleID()
	{
		return "D";
	}

	public static function GetAdvertiserRoleID()
	{
		return "R";
	}

	public static function GetManagerRoleID()
	{
		return "T";
	}

	public static function GetDemoRoleID()
	{
		return "V";
	}

	public static function GetAdminRoleID()
	{
		return "W";
	}

	// возвращает true если заданный пользователь имеет заданную роль на модуль
	public static function HaveRole($role, $USER_ID=false)
	{
		global $USER, $APPLICATION;

		if($USER_ID === false && is_object($USER))
			$USER_ID = $USER->GetID();
		$USER_ID = intval($USER_ID);

		if ($USER_ID>0)
		{
			if(is_object($USER) && $USER_ID == $USER->GetID())
				$arrGroups = $USER->GetUserGroupArray();
			else
				$arrGroups = CUser::GetUserGroup($USER_ID);

			$arRoles = $APPLICATION->GetUserRoles("advertising", $arrGroups);
			if(in_array($role, $arRoles))
				return true;
		}
		return false;
	}

	// true - если пользователь имеет роль "рекламодатель"
	// false - в противном случае
	public static function IsAdvertiser($USER_ID=false)
	{
		return CAdvContract::HaveRole(CAdvContract::GetAdvertiserRoleID(), $USER_ID);
	}

	// true - если пользователь имеет роль "администратор рекламы"
	// false - в противном случае
	public static function IsAdmin($USER_ID=false)
	{
		global $USER;
		if ($USER_ID===false && is_object($USER))
		{
			if ($USER->IsAdmin()) return true;
		}
		return CAdvContract::HaveRole(CAdvContract::GetAdminRoleID(), $USER_ID);
	}

	// true - если пользователь имеет роль "демо-доступ"
	// false - в противном случае
	public static function IsDemo($USER_ID=false)
	{
		return CAdvContract::HaveRole(CAdvContract::GetDemoRoleID(), $USER_ID);
	}

	// true - если пользователь имеет право на модуль "менеджер баннеров" и выше
	// false - в противном случае
	public static function IsManager($USER_ID=false)
	{
		return CAdvContract::HaveRole(CAdvContract::GetManagerRoleID(), $USER_ID);
	}

	// возвращает массив ID групп для которых задана роль
	// $role - идентификатор роли
	public static function GetGroupsByRole($role)
	{
		global $APPLICATION, $USER;
		if (!is_object($USER)) $USER = new CUser;
		$arGroups = array();
		$z = CGroup::GetList("dropdown", "asc", array("ACTIVE" => "Y"));
		while($zr = $z->Fetch())
		{
			$arRoles = $APPLICATION->GetUserRoles("advertising", array(intval($zr["ID"])), "Y", "N");
			if (in_array($role, $arRoles)) $arGroups[] = intval($zr["ID"]);
		}
		return array_unique($arGroups);
	}

	// возвращает массив пользователей имеющих право на модуль "рекламодатель"
	public static function GetAdvertisersArray()
	{
		$arrRes = array();
		$arGroups = CAdvContract::GetGroupsByRole(CAdvContract::GetAdvertiserRoleID());
		if (is_array($arGroups) && count($arGroups)>0)
		{
			$rsUser = CUser::GetList("id", "desc", array("ACTIVE" => "Y", "GROUPS_ID" => $arGroups));
			while ($arUser = $rsUser->Fetch()) $arrRes[] = $arUser;
		}
		return $arrRes;
	}

	// возвращает массив EMail адресов всех пользователей имеющих заданную роль
	public static function GetEmailArrayByRole($role)
	{
		global $USER;
		if (!is_object($USER)) $USER = new CUser;
		$arrEMail = array();
		$arGroups = CAdvContract::GetGroupsByRole($role);
		if (is_array($arGroups) && count($arGroups)>0)
		{
			$rsUser = CUser::GetList("id", "desc", array("ACTIVE" => "Y", "GROUPS_ID" => $arGroups));
			while ($arUser = $rsUser->Fetch())
			{
				$arrEMail[] = $arUser["EMAIL"];
			}
		}
		return array_unique($arrEMail);
	}

	// возвращает массив EMail'ов всех пользователей имеющих роль "администратор"
	public static function GetAdminEmails()
	{
		return CAdvContract::GetEmailArrayByRole(CAdvContract::GetAdminRoleID());
	}

	// возвращает массив EMail'ов всех пользователей имеющих роль "менеджер баннеров"
	public static function GetManagerEmails()
	{
		return CAdvContract::GetEmailArrayByRole(CAdvContract::GetManagerRoleID());
	}


	/*****************************************************************
			Группа функций по работе с правами на контракт

	Идентификаторы прав:

	VIEW - просмотр настроек контракта, просмотр всех баннеров контракта и их графиков
	ADD - просмотр настроек контракта, управление баннерами контракта, просмотр графиков баннеров
	EDIT - управление частью полей контракта, просмотр всех баннеров контракта и их графиков

	*****************************************************************/

	// получение массива максимальных прав доступа на контракт
	public static function GetMaxPermissionsArray()
	{
		return array("VIEW", "ADD", "EDIT");
	}

	// возвращает массивы EMail'ов всех пользователей имеющих доступ к заданному контракту (владельцы контракта)
	public static function GetOwnerEmails($CONTRACT_ID, &$OWNER_EMAIL, &$ADD_EMAIL, &$VIEW_EMAIL, &$EDIT_EMAIL)
	{
		$OWNER_EMAIL = array();
		$VIEW_EMAIL = array();
		$ADD_EMAIL = array();
		$EDIT_EMAIL = array();
		$arrPERM = CAdvContract::GetContractPermissions($CONTRACT_ID);
		foreach ($arrPERM as $perm => $arr)
		{
			if (is_array($arr) && count($arr)>0)
			{
				foreach($arr as $ar)
				{
					$OWNER_EMAIL[] = $ar["USER_EMAIL"];
					if ($perm=="VIEW")	$VIEW_EMAIL[] = $ar["USER_EMAIL"];
					if ($perm=="ADD")	$ADD_EMAIL[] = $ar["USER_EMAIL"];
					if ($perm=="EDIT")	$EDIT_EMAIL[] = $ar["USER_EMAIL"];
				}
			}
		}
		$OWNER_EMAIL	= array_unique($OWNER_EMAIL);
		$VIEW_EMAIL		= array_unique($VIEW_EMAIL);
		$ADD_EMAIL		= array_unique($ADD_EMAIL);
		$EDIT_EMAIL		= array_unique($EDIT_EMAIL);
	}

	// получение массива прав текущего пользователя по всем контрактам
	public static function GetUserPermissions($CONTRACT_ID=0, $USER_ID=false)
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: GetUserPermissions<br>Line: ";
		global $DB, $USER;
		static $CONTRACT_RIGHTS = array();
		$USER_ID = ($USER_ID===false) ? intval($USER->GetID()) : intval($USER_ID);
		if (intval($USER_ID)<=0) return false;
		$CONTRACT_ID = intval($CONTRACT_ID);
		$arrRes = array();

		if ($CONTRACT_ID>0 && is_set($CONTRACT_RIGHTS[$USER_ID], $CONTRACT_ID))
		{
			$arrRes = $CONTRACT_RIGHTS[$USER_ID];
		}
		else
		{
			$isManager = CAdvContract::IsManager($USER_ID);
			$isAdmin = CAdvContract::IsAdmin($USER_ID);

			if ($isAdmin) $arrRes[0] = CAdvContract::GetMaxPermissionsArray();
			elseif ($isManager) $arrRes[0] = array("VIEW", "ADD");
			else $arrRes[0] = array();

			$strSqlSearch = "";
			if ($CONTRACT_ID>0)
				$strSqlSearch = " and C.ID= $CONTRACT_ID ";
			$strSql = "
				SELECT
					C.ID,
					CU.PERMISSION
				FROM
					b_adv_contract C
				LEFT JOIN b_adv_contract_2_user CU ON (CU.CONTRACT_ID=C.ID and CU.USER_ID=$USER_ID)
				WHERE
					1=1
				$strSqlSearch
				";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($ar = $rs->Fetch()) $arrRes[$ar["ID"]][] = $ar["PERMISSION"];
			if ($isAdmin || $isManager)
			{
				foreach ($arrRes as $cid => $arrPerm)
				{
					if ($isAdmin) $arrPerm = CAdvContract::GetMaxPermissionsArray();
					elseif ($isManager)
					{
						$arrPerm[] = "VIEW";
						$arrPerm[] = "ADD";
						$arrPerm = array_unique($arrPerm);
					}
					$arrRes[$cid] = $arrPerm;
				}
			}

			foreach ($arrRes as $cid => $arrPerm)
			{
				TrimArr($arrPerm);
				$CONTRACT_RIGHTS[$USER_ID][$cid] = $arrPerm;
				$arrRes[$cid] = $arrPerm;
			}
		}

		return $arrRes;
	}

	// true - если пользователь имеет доступ к контракту
	// false - в противном случае
	public static function IsOwner($CONTRACT_ID, $USER_ID=false)
	{
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=0) return false;
		$arrPERM = CAdvContract::GetUserPermissions($CONTRACT_ID, $USER_ID);
		$arrPERM = $arrPERM[$CONTRACT_ID];
		if (is_array($arrPERM) && count($arrPERM)>0) return true;
		else return false;
	}

	// получение массива всех прав доступа по заданному контракту
	public static function GetContractPermissions($CONTRACT_ID)
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: GetContractPermissions<br>Line: ";
		global $DB;
		$arrPerm = array();
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=0) return false;
		$strSql = "
			SELECT
				CU.USER_ID,
				CU.PERMISSION,
				U.LOGIN,
				U.NAME,
				U.LAST_NAME,
				U.EMAIL
			FROM
				b_adv_contract_2_user CU,
				b_user U
			WHERE
				CU.CONTRACT_ID = $CONTRACT_ID
			and U.ID = CU.USER_ID
			ORDER BY CU.ID
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch())
		{
			$arrPerm[$ar["PERMISSION"]][] = array(
				"USER_ID"			=> $ar["USER_ID"],
				"USER_LOGIN"		=> $ar["LOGIN"],
				"USER_NAME"			=> $ar["NAME"],
				"USER_LAST_NAME"	=> $ar["LAST_NAME"],
				"USER_EMAIL"		=> $ar["EMAIL"]
				);
		}

		return $arrPerm;
	}

	/*****************************************************************
					Группа функций по отправке почты
	*****************************************************************/

	public static function SendEMail($arContract, $mess="")
	{
		$CONTRACT_ID = $arContract["ID"];

		$BCC = array();
		$OWNER_EMAIL = array();
		$ADD_EMAIL = array();
		$EDIT_EMAIL = array();

		$MANAGER_EMAIL = CAdvContract::GetManagerEmails();
		$ADMIN_EMAIL = CAdvContract::GetAdminEmails();
		$ADMIN_EMAIL = array_merge($MANAGER_EMAIL, $ADMIN_EMAIL);
		$ADMIN_EMAIL = array_unique($ADMIN_EMAIL);
		CAdvContract::GetOwnerEmails($CONTRACT_ID, $OWNER_EMAIL, $ADD_EMAIL, $VIEW_EMAIL, $EDIT_EMAIL);

		$EMAIL_TO = $OWNER_EMAIL;
		if (count($EMAIL_TO)<=0)
		{
			$EMAIL_TO = $ADMIN_EMAIL;
		}
		else $BCC = $ADMIN_EMAIL;

		$CREATED_BY = $MODIFIED_BY = "";
		if (intval($arContract["CREATED_BY"])>0)
		{
			$rsUser = CUser::GetByID($arContract["CREATED_BY"]);
			if ($arUser = $rsUser->Fetch())
			{
				$CREATED_BY = "[".$arUser["ID"]."] (".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
			}
		}
		if (intval($arContract["MODIFIED_BY"])==intval($arContract["CREATED_BY"]) && intval($arContract["CREATED_BY"])>0)
		{
			$MODIFIED_BY = $CREATED_BY;
		}
		elseif (intval($arContract["MODIFIED_BY"])>0)
		{
			$rsUser = CUser::GetByID($arContract["MODIFIED_BY"]);
			if ($arUser = $rsUser->Fetch())
			{
				$MODIFIED_BY = "[".$arUser["ID"]."] (".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
			}
		}
		if ($mess <> '')
			$mess = "\n".$mess."\n";
		$description = "";
		if ($arContract["DESCRIPTION"] <> '')
			$description = "\n".$arContract["DESCRIPTION"]."\n";
		$arEventFields = array(
			"ID" => $CONTRACT_ID,
			"MESSAGE" => $mess,
			"EMAIL_TO" => implode(",", $EMAIL_TO),
			"ADMIN_EMAIL" => implode(",", $ADMIN_EMAIL),
			"ADD_EMAIL" => implode(",", $ADD_EMAIL),
			"STAT_EMAIL" => implode(",", $VIEW_EMAIL),
			"EDIT_EMAIL" => implode(",", $EDIT_EMAIL),
			"OWNER_EMAIL" => implode(",", $OWNER_EMAIL),
			"BCC" => implode(",", $BCC),
			"INDICATOR" => GetMessage("AD_".mb_strtoupper($arContract["LAMP"]."_CONTRACT_STATUS")),
			"ACTIVE" => $arContract["ACTIVE"],
			"NAME" => $arContract["NAME"],
			"DESCRIPTION" => $description,
			"MAX_SHOW_COUNT" => $arContract["MAX_SHOW_COUNT"],
			"SHOW_COUNT" => $arContract["SHOW_COUNT"],
			"MAX_CLICK_COUNT" => $arContract["MAX_CLICK_COUNT"],
			"CLICK_COUNT" => $arContract["CLICK_COUNT"],
			"BANNERS" => $arContract["BANNER_COUNT"],
			"DATE_SHOW_FROM" => $arContract["DATE_SHOW_FROM"],
			"DATE_SHOW_TO" => $arContract["DATE_SHOW_TO"],
			"DATE_CREATE" => $arContract["DATE_CREATE"],
			"CREATED_BY" => $CREATED_BY,
			"DATE_MODIFY" => $arContract["DATE_MODIFY"],
			"MODIFIED_BY" => $MODIFIED_BY
		);
		$arrSITE =  CAdvContract::GetSiteArray($CONTRACT_ID);
		CEvent::Send("ADV_CONTRACT_INFO", $arrSITE, $arEventFields);
	}

	public static function SendInfo()
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: SendInfo<br>Line: ";
		global $DB;
		$rsContracts = CAdvContract::GetList("", "", array("LAMP" => "red", "EMAIL_COUNT_2" => "0"), null, "N");
		while ($arContract = $rsContracts->Fetch())
		{
			CAdvContract::SendEMail($arContract, "< ".GetMessage("AD_CONTRACT_NOT_ACTIVE")." >");
			$arFields = array("EMAIL_COUNT" => "EMAIL_COUNT + 1");
			$DB->Update("b_adv_contract",$arFields,"WHERE ID='".$arContract["ID"]."'",$err_mess.__LINE__);
		}
		return "CAdvContract::SendInfo();";
	}

	/*****************************************************************
				Группа функций по управлению контрактом
	*****************************************************************/

	public static function CheckFilter($arFilter)
	{
		global $strError;
		$str = "";
		$find_date_modify_1 = $arFilter["DATE_MODIFY_1"] ?? '';
		$find_date_modify_2 = $arFilter["DATE_MODIFY_2"] ?? '';
		if (trim($find_date_modify_1) <> '' || trim($find_date_modify_2) <> '')
		{
			$date_1_ok = false;
			$date1_stm = MkDateTime(ConvertDateTime($find_date_modify_1,"D.M.Y"),"d.m.Y");
			$date2_stm = MkDateTime(ConvertDateTime($find_date_modify_2,"D.M.Y")." 23:59","d.m.Y H:i");
			if (!$date1_stm && trim($find_date_modify_1) <> '')
				$str.= GetMessage("AD_ERROR_WRONG_DATE_MODIFY_FROM")."<br>";
			else $date_1_ok = true;
			if (!$date2_stm && trim($find_date_modify_2) <> '')
				$str.= GetMessage("AD_ERROR_WRONG_DATE_MODIFY_TILL")."<br>";
			elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
				$str.= GetMessage("AD_ERROR_FROM_TILL_DATE_MODIFY")."<br>";
		}
		$strError .= $str;
		if ($str <> '')
			return false;
		else
			return true;
	}

	// получаем массив времени и дней недели связанных с контрактом
	public static function GetWeekdayArray($CONTRACT_ID)
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: GetWeekdayArray<br>Line: ";
		global $DB;
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=0) return false;
		$arrRes = array();
		$strSql = "
			SELECT DISTINCT
				C_WEEKDAY,
				C_HOUR
			FROM
				b_adv_contract_2_weekday
			WHERE
				CONTRACT_ID = $CONTRACT_ID
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch())
		{
			$arrRes[$ar["C_WEEKDAY"]][] = $ar["C_HOUR"];
		}
		return $arrRes;
	}

	// получаем массив типов связанных с контрактом
	public static function GetTypeArray($CONTRACT_ID)
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: GetTypeArray<br>Line: ";
		global $DB;

		$CONTRACT_ID = intval($CONTRACT_ID);
		if($CONTRACT_ID<=0)
			return array();

		$strSql = "
			SELECT T.SID,
				T.NAME,
				T.SORT,
				CT.TYPE_SID,
				CT.CONTRACT_ID
			FROM
				b_adv_contract_2_type CT
			INNER JOIN b_adv_type T ON (T.SID = CT.TYPE_SID or CT.TYPE_SID='ALL')
			WHERE CT.CONTRACT_ID=".$CONTRACT_ID."
			ORDER BY T.SORT
		";

		$arrRes = array();
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while($ar = $rs->Fetch())
		{
			$arrRes[$ar["SID"]] = $ar["NAME"];
			if($ar["TYPE_SID"] == 'ALL')
				$arrRes["ALL"] = true;
		}
		return $arrRes;
	}

	// получаем массив языков связанных с контрактом
	public static function GetSiteArray($CONTRACT_ID)
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: GetSiteArray<br>Line: ";
		global $DB;
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=0) return false;
		$arrRes = array();
		$strSql = "
			SELECT
				CS.SITE_ID
			FROM
				b_adv_contract_2_site CS
			WHERE
				CS.CONTRACT_ID = $CONTRACT_ID
			";

		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch()) $arrRes[] = $ar["SITE_ID"];
		return $arrRes;
	}

	// получаем массив страниц связанных с контрактом
	public static function GetPageArray($CONTRACT_ID, $SHOW="SHOW")
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: GetPageArray<br>Line: ";
		global $DB;
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=0) return false;
		$arrRes = array();
		$SHOW_ON_PAGE = ($SHOW=="NOT_SHOW") ? "'N'" : "'Y'";
		$strSql = "
			SELECT DISTINCT
				PAGE
			FROM
				b_adv_contract_2_page
			WHERE
				CONTRACT_ID = $CONTRACT_ID
			and	SHOW_ON_PAGE = $SHOW_ON_PAGE
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch()) $arrRes[] = $ar["PAGE"];
		return $arrRes;
	}

	// получаем контракт по ID
	public static function GetByID($CONTRACT_ID, $CHECK_RIGHTS="Y")
	{
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=0) return false;
		$arFilter = array(
			"ID"				=> $CONTRACT_ID,
			"ID_EXACT_MATCH"	=> "Y"
			);
		$rs = CAdvContract::GetList('', '', $arFilter, null, $CHECK_RIGHTS);
		return $rs;
	}

	// проверка полей при модификации контракта
	public static function CheckFields($arFields, $CONTRACT_ID, $CHECK_RIGHTS="Y")
	{
		global $strError;
		$str = "";
		$arrPERM = false;
		if ($CHECK_RIGHTS=="Y")
		{
			$arrPERM = CAdvContract::GetUserPermissions($CONTRACT_ID);
			$arrPERM = $arrPERM[$CONTRACT_ID];
		}
		if ($CHECK_RIGHTS!="Y" || (is_array($arrPERM) && in_array("EDIT", $arrPERM)))
		{
			if ($arFields["DATE_SHOW_FROM"] <> '')
			{
				if (!CheckDateTime($arFields["DATE_SHOW_FROM"]))
					$str.= GetMessage("AD_ERROR_WRONG_DATE_SHOW_FROM_CONTRACT")."<br>";
			}
			if ($arFields["DATE_SHOW_TO"] <> '')
			{
				if (!CheckDateTime($arFields["DATE_SHOW_TO"]))
					$str .= GetMessage("AD_ERROR_WRONG_DATE_SHOW_TO_CONTRACT")."<br>";
			}
		}
		else
		{
			if ($CONTRACT_ID>0)
				$str .= GetMessage("AD_ERROR_NOT_ENOUGH_PERMISSIONS_CONTRACT")."<br>";
			else
				$str .= GetMessage("AD_ERROR_NOT_ENOUGH_PERMISSIONS_FOR_NEW_CONTRACT")."<br>";
		}

		$strError .= $str;
		if ($str <> '')
			return false;
		else
			return true;
	}

	// добавляем новый контракт или модифицируем существующий
	public static function Set($arFields, $CONTRACT_ID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: Set<br>Line: ";
		global $DB, $USER;
		if (CAdvContract::CheckFields($arFields, $CONTRACT_ID, $CHECK_RIGHTS))
		{
			if ($CHECK_RIGHTS=="Y")
			{
				$USER_ID = intval($USER->GetID());
				$isAdmin = CAdvContract::IsAdmin();
			}
			else
			{
				if (is_object($USER)) $USER_ID = intval($USER->GetID()); else $USER_ID = 0;
				$isAdmin = true;
			}

			$check_activity = "N";
			$arFields_i = array();
			$arrKeys = array_keys($arFields);
			if ($isAdmin)
			{
				if (in_array("SHOW_COUNT", $arrKeys))
				{
					$check_activity = "Y";
					$arFields_i["SHOW_COUNT"] = intval($arFields["SHOW_COUNT"]);
				}

				if (in_array("VISITOR_COUNT", $arrKeys))
				{
					$check_activity = "Y";
					$arFields_i["VISITOR_COUNT"] = intval($arFields["VISITOR_COUNT"]);
				}

				if (in_array("CLICK_COUNT", $arrKeys))
				{
					$check_activity = "Y";
					$arFields_i["CLICK_COUNT"] = intval($arFields["CLICK_COUNT"]);
				}

				if (in_array("ACTIVE", $arrKeys) && ($arFields["ACTIVE"]=="Y" || $arFields["ACTIVE"]=="N"))
				{
					$check_activity = "Y";
					$arFields_i["ACTIVE"] = "'".$arFields["ACTIVE"]."'";
				}

				if (in_array("WEIGHT", $arrKeys))
					$arFields_i["WEIGHT"] = intval($arFields["WEIGHT"]);

				if (in_array("ADMIN_COMMENTS", $arrKeys))
					$arFields_i["ADMIN_COMMENTS"] = "'".$DB->ForSql($arFields["ADMIN_COMMENTS"],2000)."'";

				if (in_array("KEYWORDS", $arrKeys))
					$arFields_i["KEYWORDS"] = "'".$DB->ForSql($arFields["KEYWORDS"], 2000)."'";;

				if (in_array("MAX_SHOW_COUNT", $arrKeys))
				{
					$check_activity = "Y";
					if ($arFields["MAX_SHOW_COUNT"] <> '')
						$arFields_i["MAX_SHOW_COUNT"] = intval($arFields["MAX_SHOW_COUNT"]);
					else
						$arFields_i["MAX_SHOW_COUNT"] = "null";
				}

				if (in_array("MAX_VISITOR_COUNT", $arrKeys))
				{
					$check_activity = "Y";
					if ($arFields["MAX_VISITOR_COUNT"] <> '')
						$arFields_i["MAX_VISITOR_COUNT"] = intval($arFields["MAX_VISITOR_COUNT"]);
					else
						$arFields_i["MAX_VISITOR_COUNT"] = "null";
				}

				if (in_array("MAX_CLICK_COUNT", $arrKeys))
				{
					$check_activity = "Y";
					if ($arFields["MAX_CLICK_COUNT"] <> '')
						$arFields_i["MAX_CLICK_COUNT"] = intval($arFields["MAX_CLICK_COUNT"]);
					else
						$arFields_i["MAX_CLICK_COUNT"] = "null";
				}

				if (in_array("DATE_SHOW_FROM", $arrKeys))
				{
					$check_activity = "Y";
					if ($arFields["DATE_SHOW_FROM"] <> '')
					{
						$arFields_i["DATE_SHOW_FROM"] = $DB->CharToDateFunction($arFields["DATE_SHOW_FROM"]);
					}
					else $arFields_i["DATE_SHOW_FROM"] = "null";
				}

				if (in_array("DATE_SHOW_TO", $arrKeys))
				{
					$check_activity = "Y";
					if ($arFields["DATE_SHOW_TO"] <> '')
					{
						$time = "";
						if(defined("FORMAT_DATE") && mb_strlen($arFields["DATE_SHOW_TO"]) <= mb_strlen(FORMAT_DATE))
						{
							$time = " 23:59:59";
						}
						$arFields_i["DATE_SHOW_TO"] = $DB->CharToDateFunction($arFields["DATE_SHOW_TO"].$time);
					}
					else
					{
						$arFields_i["DATE_SHOW_TO"] = "null";
					}
				}

				if (in_array("DEFAULT_STATUS_SID", $arrKeys))
				{
					$arrStatus = CAdvBanner::GetStatusList("N");
					$arrV = array_values($arrStatus["reference_id"]);
					if (in_array($arFields["DEFAULT_STATUS_SID"], $arrV))
					{
						$arFields_i["DEFAULT_STATUS_SID"] = "'".$DB->ForSql($arFields["DEFAULT_STATUS_SID"],255)."'";
					}
				}

				if (in_array("SORT", $arrKeys))
					$arFields_i["SORT"] = intval($arFields["SORT"]);

				if (in_array("NAME", $arrKeys))
					$arFields_i["NAME"] = "'".$DB->ForSql($arFields["NAME"],255)."'";
				if (in_array("DESCRIPTION", $arrKeys))
					$arFields_i["DESCRIPTION"] = "'".$DB->ForSql($arFields["DESCRIPTION"],2000)."'";

			}

			$arFields_i["EMAIL_COUNT"] = 0;
			$str_PREV_LAMP = "";

			if (intval($CONTRACT_ID)>0)
			{
				$rsContract = CAdvContract::GetByID($CONTRACT_ID);
				$arContract = $rsContract->Fetch();
				$str_PREV_LAMP = $arContract["LAMP"];

				if (in_array("DATE_MODIFY", $arrKeys) && CheckDateTime($arFields["DATE_MODIFY"]))
					$arFields_i["DATE_MODIFY"] = $DB->CharToDateFunction($arFields["DATE_MODIFY"]);
				else
					$arFields_i["DATE_MODIFY"] = $DB->GetNowFunction();

				if (in_array("MODIFIED_BY", $arrKeys))
					$arFields_i["MODIFIED_BY"] = intval($arFields["MODIFIED_BY"]);
				else
					$arFields_i["MODIFIED_BY"] = $USER_ID;

				$DB->Update("b_adv_contract",$arFields_i,"WHERE ID='".intval($CONTRACT_ID)."'",$err_mess.__LINE__);
			}
			elseif ($isAdmin)
			{
				$check_activity = "Y";

				if (in_array("DATE_CREATE", $arrKeys) && CheckDateTime($arFields["DATE_CREATE"]))
					$arFields_i["DATE_CREATE"] = $DB->CharToDateFunction($arFields["DATE_CREATE"]);
				else
					$arFields_i["DATE_CREATE"] = $DB->GetNowFunction();

				if (in_array("CREATED_BY", $arrKeys))
					$arFields_i["CREATED_BY"] = intval($arFields["CREATED_BY"]);
				else
					$arFields_i["CREATED_BY"] = $USER_ID;

				if (in_array("DATE_MODIFY", $arrKeys) && CheckDateTime($arFields["DATE_MODIFY"]))
					$arFields_i["DATE_MODIFY"] = $DB->CharToDateFunction($arFields["DATE_MODIFY"]);
				else
					$arFields_i["DATE_MODIFY"] = $DB->GetNowFunction();

				if (in_array("MODIFIED_BY", $arrKeys))
					$arFields_i["MODIFIED_BY"] = intval($arFields["MODIFIED_BY"]);
				else
					$arFields_i["MODIFIED_BY"] = $USER_ID;

				$CONTRACT_ID = $DB->Insert("b_adv_contract",$arFields_i, $err_mess.__LINE__);
			}

			$CONTRACT_ID = intval($CONTRACT_ID);

			if ($CONTRACT_ID>0)
			{
				if ($isAdmin)
				{
					if (in_array("arrSITE", $arrKeys))
					{
						CAdvContract::DeleteSiteLink($CONTRACT_ID);
						if (is_array($arFields["arrSITE"]))
						{
							$arrSite = array_unique($arFields["arrSITE"]);
							foreach($arrSite as $sid)
							{
								if (trim($sid) <> '')
								{
									$strSql = "INSERT INTO b_adv_contract_2_site(CONTRACT_ID, SITE_ID) VALUES ($CONTRACT_ID, '".$DB->ForSql($sid, 2)."')";
									$DB->Query($strSql, false, $err_mess.__LINE__);
								}
							}
						}
					}

					if (in_array("arrSHOW_PAGE", $arrKeys))
					{
						CAdvContract::DeletePageLink($CONTRACT_ID, " and SHOW_ON_PAGE='Y'");
						if (is_array($arFields["arrSHOW_PAGE"]))
						{
							$arrPage = array_unique($arFields["arrSHOW_PAGE"]);
							foreach($arrPage as $page)
							{
								$page = trim($page);
								if ($page <> '')
								{
									$arFields_i = array(
										"CONTRACT_ID"	=> $CONTRACT_ID,
										"PAGE"			=> "'".$DB->ForSql($page, 255)."'",
										"SHOW_ON_PAGE"	=> "'Y'"
										);
									$DB->Insert("b_adv_contract_2_page",$arFields_i, $err_mess.__LINE__);
								}
							}
						}
					}

					if (in_array("arrNOT_SHOW_PAGE", $arrKeys))
					{
						CAdvContract::DeletePageLink($CONTRACT_ID, " and SHOW_ON_PAGE='N'");
						if (is_array($arFields["arrNOT_SHOW_PAGE"]))
						{
							$arrPage = array_unique($arFields["arrNOT_SHOW_PAGE"]);
							foreach($arrPage as $page)
							{
								$page = trim($page);
								if ($page <> '')
								{
									$arFields_i = array(
										"CONTRACT_ID"	=> $CONTRACT_ID,
										"PAGE"			=> "'".$DB->ForSql($page, 255)."'",
										"SHOW_ON_PAGE"	=> "'N'"
										);
									$DB->Insert("b_adv_contract_2_page",$arFields_i, $err_mess.__LINE__);
								}
							}
						}
					}

					if (in_array("arrTYPE", $arrKeys))
					{
						CAdvContract::DeleteTypeLink($CONTRACT_ID);
						if (is_array($arFields["arrTYPE"]))
						{
							$arrType = array_unique($arFields["arrTYPE"]);
							foreach($arrType as $type)
							{
								if (trim($type) <> '')
								{
									$strSql = "INSERT INTO b_adv_contract_2_type(CONTRACT_ID, TYPE_SID) VALUES ($CONTRACT_ID, '".$DB->ForSql($type, 255)."')";
									$DB->Query($strSql, false, $err_mess.__LINE__);
								}
							}
						}
					}

					if (in_array("arrWEEKDAY", $arrKeys))
					{
						CAdvContract::DeleteWeekdayLink($CONTRACT_ID);
						if (is_array($arFields["arrWEEKDAY"]))
						{
							$arrWeekday = array_keys($arFields["arrWEEKDAY"]);
							$arrWeekday = array_unique($arrWeekday);
							if (is_array($arrWeekday) && count($arrWeekday)>0)
							{
								foreach ($arrWeekday as $weekday)
								{
									if (is_array($arFields["arrWEEKDAY"][$weekday]) && count($arFields["arrWEEKDAY"][$weekday])>0)
									{
										$arrHour = $arFields["arrWEEKDAY"][$weekday];
										array_walk(
											$arrHour,
											function (&$item) {
												$item = (int)$item;
											}
										);
										$arrHour = array_unique($arrHour);
										foreach($arrHour as $hour)
										{
											if ($hour>=0 && $hour<=23)
											{
												$strSql = "INSERT INTO b_adv_contract_2_weekday (CONTRACT_ID, C_WEEKDAY, C_HOUR) VALUES (".$CONTRACT_ID.", '".$DB->ForSql($weekday, 10)."', ".$hour.")";
												$DB->Query($strSql, false, $err_mess.__LINE__);
											}
										}
									}
								}
							}
						}
					}
				}

				if (in_array("arrUSER_VIEW", $arrKeys))
				{
					CAdvContract::DeleteUserLink($CONTRACT_ID, " and PERMISSION = 'VIEW'");
					if (is_array($arFields["arrUSER_VIEW"]))
					{
						$arrUser = array_unique($arFields["arrUSER_VIEW"]);
						foreach($arrUser as $user_id)
						{
							if (intval($user_id)>0)
							{
								$arFields_i = array(
									"CONTRACT_ID"	=> $CONTRACT_ID,
									"USER_ID"		=> intval($user_id),
									"PERMISSION"	=> "'VIEW'"
									);
								$DB->Insert("b_adv_contract_2_user",$arFields_i, $err_mess.__LINE__);
							}
						}
					}
				}

				if (in_array("arrUSER_ADD", $arrKeys))
				{
					CAdvContract::DeleteUserLink($CONTRACT_ID, " and PERMISSION = 'ADD'");
					if (is_array($arFields["arrUSER_ADD"]))
					{
						$arrUser = array_unique($arFields["arrUSER_ADD"]);
						foreach($arrUser as $user_id)
						{
							if (intval($user_id)>0)
							{
								$arFields_i = array(
									"CONTRACT_ID"	=> $CONTRACT_ID,
									"USER_ID"		=> intval($user_id),
									"PERMISSION"	=> "'ADD'"
									);
								$DB->Insert("b_adv_contract_2_user",$arFields_i, $err_mess.__LINE__);
							}
						}
					}
				}

				if ($isAdmin)
				{
					if (in_array("arrUSER_EDIT", $arrKeys))
					{
						CAdvContract::DeleteUserLink($CONTRACT_ID, " and PERMISSION = 'EDIT'");
						if (is_array($arFields["arrUSER_EDIT"]))
						{
							$arrUser = array_unique($arFields["arrUSER_EDIT"]);
							foreach($arrUser as $user_id)
							{
								if (intval($user_id)>0)
								{
									$arFields_i = array(
										"CONTRACT_ID"	=> $CONTRACT_ID,
										"USER_ID"		=> intval($user_id),
										"PERMISSION"	=> "'EDIT'"
										);
									$DB->Insert("b_adv_contract_2_user",$arFields_i, $err_mess.__LINE__);
								}
							}
						}
					}
				}

				if ($check_activity=="Y")
				{
					CTimeZone::Disable();
					$rsContract = CAdvContract::GetByID($CONTRACT_ID);
					CTimeZone::Enable();

					$arContract = $rsContract->Fetch();
					$str_CURRENT_LAMP = $arContract["LAMP"];
					if ($str_PREV_LAMP!=$str_CURRENT_LAMP)
					{
						$mess = ($str_CURRENT_LAMP=="red") ? "< ".GetMessage("AD_CONTRACT_NOT_ACTIVE")." >" : "< ".GetMessage("AD_CONTRACT_ACTIVE")." >";
						CAdvContract::SendEMail($arContract, $mess);
					}
					if ($str_CURRENT_LAMP=="red")
					{
						$arFields = array("EMAIL_COUNT" => "EMAIL_COUNT + 1");
						$DB->Update("b_adv_contract",$arFields,"WHERE ID='".$CONTRACT_ID."'",$err_mess.__LINE__);
					}
				}

			}
		}
		return $CONTRACT_ID;
	}

	// удаление контракта
	public static function Delete($CONTRACT_ID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: Delete<br>Line: ";
		global $DB, $strError;
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=1)
			return false;
		$isAdmin = ($CHECK_RIGHTS=="N"? true : CAdvContract::IsAdmin());
		if ($isAdmin)
		{
			$strSql = "SELECT ID FROM b_adv_banner WHERE CONTRACT_ID = $CONTRACT_ID";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($ar = $rs->Fetch()) CAdvBanner::Delete($ar["ID"], "N");

			CAdvContract::DeletePageLink($CONTRACT_ID);
			CAdvContract::DeleteSiteLink($CONTRACT_ID);
			CAdvContract::DeleteTypeLink($CONTRACT_ID);
			CAdvContract::DeleteUserLink($CONTRACT_ID);
			CAdvContract::DeleteWeekdayLink($CONTRACT_ID);

			$strSql = "DELETE FROM b_adv_contract WHERE ID = $CONTRACT_ID";
			$DB->Query($strSql, false, $err_mess.__LINE__);
			return true;
		}
		else
		{
			$strError .= GetMessage("AD_ERROR_NOT_ENOUGH_PERMISSIONS_CONTRACT")."<br>";
			return false;
		}
	}

	// удаление связи контракта со страницами
	public static function DeletePageLink($CONTRACT_ID, $where="")
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: DeletePageLink<br>Line: ";
		global $DB;
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=0)
			return false;
		$strSql = "DELETE FROM b_adv_contract_2_page WHERE CONTRACT_ID = $CONTRACT_ID ".$where;
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	// удаление связи контракта с сайтами
	public static function DeleteSiteLink($CONTRACT_ID)
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: DeleteSiteLink<br>Line: ";
		global $DB;
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=0)
			return false;
		$strSql = "DELETE FROM b_adv_contract_2_site WHERE CONTRACT_ID = $CONTRACT_ID";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	// удаление связи контракта с типами баннеров
	public static function DeleteTypeLink($CONTRACT_ID)
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: DeleteTypeLink<br>Line: ";
		global $DB;
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=0)
			return false;
		$strSql = "DELETE FROM b_adv_contract_2_type WHERE CONTRACT_ID = $CONTRACT_ID";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	// удаление связи контракта с пользователями
	public static function DeleteUserLink($CONTRACT_ID, $where="")
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: DeleteUserLink<br>Line: ";
		global $DB;
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=0)
			return false;
		$strSql = "DELETE FROM b_adv_contract_2_user WHERE CONTRACT_ID = $CONTRACT_ID ".$where;
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	// удаление связи контракта со временем и днями недели
	public static function DeleteWeekdayLink($CONTRACT_ID)
	{
		$err_mess = (CAdvContract_all::err_mess())."<br>Function: DeleteWeekdayLink<br>Line: ";
		global $DB;
		$CONTRACT_ID = intval($CONTRACT_ID);
		if ($CONTRACT_ID<=0)
			return false;
		$strSql = "DELETE FROM b_adv_contract_2_weekday WHERE CONTRACT_ID = $CONTRACT_ID";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	//Получение статистики по контрактам
	public static function GetStatList($by, $order, $arFilter)
	{
		$err_mess = (CAdvBanner::err_mess())."<br>Function: GetDynamicList<br>Line: ";
		global $DB;
		$arSqlSearch = Array();
		if (CAdvBanner::CheckDynamicFilter($arFilter))
		{
			if (is_array($arFilter))
			{
				$filter_keys = array_keys($arFilter);
				for ($i=0, $n = count($filter_keys); $i < $n; $i++)
				{
					$key = $filter_keys[$i];
					$val = $arFilter[$filter_keys[$i]];
					if(is_array($val))
					{
						if(count($val)<=0) continue;
					}
					else
					{
						if( ((string)$val == '') || ("$val"=="NOT_REF") ) continue;
					}
					$key = strtoupper($key);
					switch($key)
					{
						case "DATE_1":
							$arSqlSearch[] = "D.DATE_STAT>=".$DB->CharToDateFunction($val, "SHORT");
							break;
						case "DATE_2":
							$arSqlSearch[] = "D.DATE_STAT<=".$DB->CharToDateFunction($val." 23:59:59", "FULL");
							break;
					}
				}

				\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($arFilter['CONTRACT_ID']);

				if (is_array($arFilter['CONTRACT_ID']) && !empty($arFilter['CONTRACT_ID']))
				{
					$where = new CSQLWhere();
					$arSqlSearch[] = $where->_NumberIN("C.ID", $arFilter['CONTRACT_ID']);
				}
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

		if ($by == "s_date")
		{
			$strSqlOrder = " ORDER BY D.DATE_STAT ";
		}
		elseif ($by == "s_visitors")
		{
			$strSqlOrder = " ORDER BY VISITOR_COUNT ";
		}
		elseif ($by == "s_clicks")
		{
			$strSqlOrder = " ORDER BY CLICK_COUNT ";
		}
		elseif ($by == "s_ctr")
		{
			$strSqlOrder = " ORDER BY CTR";
		}
		elseif ($by == "s_show")
		{
			$strSqlOrder = " ORDER BY SHOW_COUNT ";
		}
		elseif ($by == "s_id")
		{
			$strSqlOrder = " ORDER BY C.ID";
		}
		else
		{
			$strSqlOrder = " ORDER BY DATE_STAT";
			$by = "s_date";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
		}


		if ($by != "s_date")
		{
			$strSqlOrder .= ', DATE_STAT ASC';
		}

		if ($arFilter['CONTRACT_SUMMA'] == 'Y')
		{
			$strSql = "
				SELECT
					".$DB->DateToCharFunction("D.DATE_STAT","SHORT")."		DATE_STAT,
					SUM(D.SHOW_COUNT)										SHOW_COUNT,
					SUM(D.CLICK_COUNT)										CLICK_COUNT,
					SUM(D.VISITOR_COUNT)									VISITOR_COUNT,
					" . CAdvBanner::getCTRSQL() . "
				FROM
					b_adv_banner_2_day D
				INNER JOIN b_adv_banner B ON (D.BANNER_ID = B.ID)
				INNER JOIN b_adv_contract C ON (B.CONTRACT_ID = C.ID)
				WHERE
				$strSqlSearch
				GROUP by DATE_STAT
				$strSqlOrder
				";
		}
		else
		{
			$strSql = "
				SELECT
					".$DB->DateToCharFunction("D.DATE_STAT","SHORT")."		DATE_STAT,
					SUM(D.SHOW_COUNT)										SHOW_COUNT,
					SUM(D.CLICK_COUNT)										CLICK_COUNT,
					SUM(D.VISITOR_COUNT)									VISITOR_COUNT,
					C.ID,
					C.NAME													CONTRACT_NAME,
					" . CAdvBanner::getCTRSQL() . "
				FROM
					b_adv_banner_2_day D
				INNER JOIN b_adv_banner B ON (D.BANNER_ID = B.ID)
				INNER JOIN b_adv_contract C ON (B.CONTRACT_ID = C.ID)
				WHERE
				$strSqlSearch
				GROUP by DATE_STAT, C.ID, C.NAME
				$strSqlOrder
				";
		}

		return $DB->Query($strSql, false, $err_mess.__LINE__);
	}
}

/*****************************************************************
					Класс "Рекламный баннер"
*****************************************************************/

class CAdvBanner_all
{
	public static function err_mess()
	{
		$module_id = "advertising";
		return "<br>Module: ".$module_id."<br>Class: CAdvBanner_all<br>File: ".__FILE__;
	}

	public static function GetCurUri()
	{
		global $strAdvCurUri, $APPLICATION;
		if ($strAdvCurUri!==false)
			return $strAdvCurUri;
		else
			return $APPLICATION->GetCurUri("", true);
	}

	public static function SetCurUri($uri=false)
	{
		global $strAdvCurUri;
		if ($uri!==false)
			$strAdvCurUri = $uri;
	}

	// получим баннер по ID
	public static function GetByID($BANNER_ID, $CHECK_RIGHTS="Y")
	{
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$arFilter = array(
			"ID"				=> $BANNER_ID,
			"ID_EXACT_MATCH"	=> "Y"
		);
		$rs = CAdvBanner::GetList("", "", $arFilter, false, $CHECK_RIGHTS);
		return $rs;
	}

	// копирование баннера
	public static function Copy($BANNER_ID, $CHECK_RIGHTS="Y")
	{
		$ID = 0;
		$rsBanner = CAdvBanner::GetByID($BANNER_ID, $CHECK_RIGHTS);
		if ($arBanner = $rsBanner->Fetch())
		{
			$arFields = array(
				"CONTRACT_ID"			=> $arBanner["CONTRACT_ID"],
				"TYPE_SID"			=> $arBanner["TYPE_SID"],
				"STATUS_SID"			=> $arBanner["STATUS_SID"],
				"STATUS_COMMENTS"		=> $arBanner["STATUS_COMMENTS"],
				"NAME"				=> $arBanner["NAME"],
				"GROUP_SID"			=> $arBanner["GROUP_SID"],
				"ACTIVE"				=> $arBanner["ACTIVE"],
				"WEIGHT"				=> $arBanner["WEIGHT"],
				"MAX_VISITOR_COUNT"		=> $arBanner["MAX_VISITOR_COUNT"],
				"RESET_VISITOR_COUNT"	=> "Y",
				"SHOWS_FOR_VISITOR"		=> $arBanner["SHOWS_FOR_VISITOR"],
				"MAX_SHOW_COUNT"		=> $arBanner["MAX_SHOW_COUNT"],
				"RESET_SHOW_COUNT"		=> "Y",
				"MAX_CLICK_COUNT"		=> $arBanner["MAX_CLICK_COUNT"],
				"RESET_CLICK_COUNT"		=> $arBanner["RESET_CLICK_COUNT"],
				"DATE_SHOW_FROM"		=> $arBanner["DATE_SHOW_FROM"],
				"DATE_SHOW_TO"			=> $arBanner["DATE_SHOW_TO"],
				"IMAGE_ALT"			=> $arBanner["IMAGE_ALT"],
				"URL"				=> $arBanner["URL"],
				"URL_TARGET"			=> $arBanner["URL_TARGET"],
				"NO_URL_IN_FLASH"		=> $arBanner["NO_URL_IN_FLASH"],
				"CODE"				=> $arBanner["CODE"],
				"CODE_TYPE"			=> $arBanner["CODE_TYPE"],
				"FOR_NEW_GUEST"		=> $arBanner["FOR_NEW_GUEST"],
				"COMMENTS"			=> $arBanner["COMMENTS"],
				"SHOW_USER_GROUP"		=> $arBanner["SHOW_USER_GROUP"],
				"arrSHOW_PAGE"			=> CAdvBanner::GetPageArray($BANNER_ID, "SHOW"),
				"arrNOT_SHOW_PAGE"		=> CAdvBanner::GetPageArray($BANNER_ID, "NOT_SHOW"),
				"STAT_TYPE"			=> $arBanner["STAT_TYPE"],
				"arrCOUNTRY"			=> CAdvBanner::GetCountryArray($BANNER_ID, array("COUNTRY_ID", "REGION", "CITY_ID")),
				"arrSTAT_ADV"			=> CAdvBanner::GetStatAdvArray($BANNER_ID),
				"arrWEEKDAY"			=> CAdvBanner::GetWeekdayArray($BANNER_ID),
				"arrSITE"				=> CAdvBanner::GetSiteArray($BANNER_ID),
				"arrUSERGROUP"			=> CAdvBanner::GetGroupArray($BANNER_ID),
				"KEYWORDS"			=> $arBanner["KEYWORDS"],
				"SEND_EMAIL"			=> "Y",
				"AD_TYPE"				=> $arBanner["AD_TYPE"],
				"FLASH_TRANSPARENT" => $arBanner["FLASH_TRANSPARENT"],
				"arrFlashIMAGE_ID" => $arBanner["arrFlashIMAGE_ID"],
				"FLASH_JS" => $arBanner["FLASH_JS"],
				"FLASH_VER" => $arBanner["FLASH_VER"],
				"TEMPLATE" => $arBanner["TEMPLATE"]
				);
			if (intval($arBanner["IMAGE_ID"])>0)
			{
				$arrIMAGE = CFile::MakeFileArray($arBanner["IMAGE_ID"]);
				$arrIMAGE["MODULE_ID"] = "advertising";
				$arFields["arrIMAGE_ID"] = $arrIMAGE;
			}
			if ($arBanner["TEMPLATE_FILES"] && CheckSerializedData($arBanner["TEMPLATE_FILES"]))
				$arBanner["TEMPLATE_FILES"] = unserialize(
					htmlspecialchars_decode($arBanner["TEMPLATE_FILES"]),
					['allowed_classes' => false]
				);
			else
				$arBanner["TEMPLATE_FILES"] = array();
			$arrTEMPLATE_FILES = array();
			foreach ($arBanner["TEMPLATE_FILES"] as $k1 => $v1)
			{
				foreach ($v1 as $k2 => $v2)
				{
					$arrTEMPLATE_FILES[$k1][$k2] = CFile::MakeFileArray($v2);
				}
			}

			$arFields["TEMPLATE_FILES"] = $arrTEMPLATE_FILES;

			$ID = CAdvBanner::Set($arFields, 0);
		}
		return $ID;
	}

	// удаление баннера
	public static function Delete($BANNER_ID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: Delete<br>Line: ";
		global $DB, $strError;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;

		$strSql = "SELECT CONTRACT_ID, IMAGE_ID, TYPE_SID FROM b_adv_banner WHERE ID = '$BANNER_ID'";
		$rsBanner = $DB->Query($strSql, false, $err_mess.__LINE__);
		if ($arBanner = $rsBanner->Fetch())
		{
			$ok = false;
			if ($CHECK_RIGHTS=="Y")
			{
				$arrPERM = CAdvContract::GetUserPermissions($arBanner["CONTRACT_ID"]);
				$arrPERM = $arrPERM[$arBanner["CONTRACT_ID"]];
				if (in_array("ADD", $arrPERM))
					$ok = true;
			}
			else
			{
				$ok = true;
			}

			if ($ok)
			{
				if (defined('BX_COMP_MANAGED_CACHE'))
				{
					$taggedCache = Application::getInstance()->getTaggedCache();
					$taggedCache->clearByTag('advertising_banner_type_'.$arBanner['TYPE_SID']);
				}

				CFile::Delete($arBanner["IMAGE_ID"]);
				CAdvBanner::DeleteCountryLink($BANNER_ID);
				CAdvBanner::DeleteSiteLink($BANNER_ID);
				CAdvBanner::DeleteStatAdvLink($BANNER_ID);
				CAdvBanner::DeletePageLink($BANNER_ID);
				CAdvBanner::DeleteWeekdayLink($BANNER_ID);
				CAdvBanner::DeleteGroupLink($BANNER_ID);

				$strSql = "DELETE FROM b_adv_banner_2_day WHERE BANNER_ID = $BANNER_ID";
				$DB->Query($strSql, false, $err_mess.__LINE__);

				$strSql = "DELETE FROM b_adv_banner WHERE ID = $BANNER_ID";
				$DB->Query($strSql, false, $err_mess.__LINE__);
				return true;
			}
			else
			{
				$strError .= GetMessage("AD_ERROR_NOT_ENOUGH_PERMISSIONS_BANNER")."<br>";
			}
		}
		return false;
	}

	// удаление связи баннера со временем и днями недели
	public static function DeleteWeekdayLink($BANNER_ID)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: DeleteWeekdayLink<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$strSql = "DELETE FROM b_adv_banner_2_weekday WHERE BANNER_ID = $BANNER_ID";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	// удаление связи баннера с языками
	public static function DeleteSiteLink($BANNER_ID)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: DeleteSiteLink<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$strSql = "DELETE FROM b_adv_banner_2_site WHERE BANNER_ID = $BANNER_ID";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	// удаление связи баннера со страной
	public static function DeleteCountryLink($BANNER_ID)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: DeleteCountryLink<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$strSql = "DELETE FROM b_adv_banner_2_country WHERE BANNER_ID = $BANNER_ID";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	// удаление связи баннера со рекламными кампаниями статистики
	public static function DeleteStatAdvLink($BANNER_ID)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: DeleteStatAdvLink<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$strSql = "DELETE FROM b_adv_banner_2_stat_adv WHERE BANNER_ID = $BANNER_ID";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	// удаление связи баннера со страницами
	public static function DeletePageLink($BANNER_ID, $where="")
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: DeletePageLink<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$strSql = "DELETE FROM b_adv_banner_2_page WHERE BANNER_ID = $BANNER_ID ".$where;
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	public static function DeleteGroupLink($BANNER_ID)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: DeleteGroupLink<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$strSql = "DELETE FROM b_adv_banner_2_group WHERE BANNER_ID = $BANNER_ID";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	public static function GetStatusList()
	{
		$ref_id = array(
			"PUBLISHED",
			"READY",
			"REJECTED"
		);
		$ref = array(
			GetMessage("AD_STATUS_PUBLISHED"),
			GetMessage("AD_STATUS_READY"),
			GetMessage("AD_STATUS_REJECTED")
		);
		$arr = array("reference_id" => $ref_id, "reference" => $ref);
		return $arr;
	}

	// получаем массив страниц связанных с баннером
	public static function GetPageArray($BANNER_ID, $SHOW="SHOW")
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: GetPageArray<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$arrRes = array();
		$SHOW_ON_PAGE = ($SHOW=="NOT_SHOW") ? "'N'" : "'Y'";
		$strSql = "
			SELECT DISTINCT
				PAGE
			FROM
				b_adv_banner_2_page
			WHERE
				BANNER_ID = $BANNER_ID
			and	SHOW_ON_PAGE = $SHOW_ON_PAGE
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch())
			$arrRes[] = $ar["PAGE"];
		return $arrRes;
	}

	// получаем массив групп пользователей связанных с баннером
	public static function GetGroupArray($BANNER_ID)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: GetGroupArray<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$arrRes = array();

		$strSql = "
			SELECT
				GROUP_ID
			FROM
				b_adv_banner_2_group
			WHERE
				BANNER_ID = $BANNER_ID
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch())
			$arrRes[] = $ar["GROUP_ID"];
		return $arrRes;
	}

	// получаем массив языков связанных с баннером
	public static function GetSiteArray($BANNER_ID)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: GetSiteArray<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$arrRes = array();
		$strSql = "
			SELECT
				SITE_ID
			FROM
				b_adv_banner_2_site
			WHERE
				BANNER_ID = $BANNER_ID
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch())
			$arrRes[] = $ar["SITE_ID"];
		return $arrRes;
	}

	// получаем массив стран связанных с баннером
	public static function GetCountryArray($BANNER_ID, $WHAT = "COUNTRY")
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: GetCountryArray<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$arrRes = array();
		if(!is_array($WHAT))
		{
			if($WHAT === "CITY")
				$arSelect = array("COUNTRY_ID", "REGION", "CITY_ID");
			elseif($WHAT === "REGION")
				$arSelect = array("COUNTRY_ID", "REGION");
			else
				$arSelect = array("COUNTRY_ID");
		}
		else
		{
			$arSelect = array();
			foreach($WHAT as $FIELD)
			{
				if($FIELD === "CITY_ID")
					$arSelect[$FIELD] = $FIELD;
				elseif($FIELD === "REGION")
					$arSelect[$FIELD] = $FIELD;
				elseif($FIELD === "COUNTRY_ID")
					$arSelect[$FIELD] = $FIELD;
			}
			if(count($arSelect) <= 0)
				$arSelect = array("COUNTRY_ID");
		}

		$strSql = "
			SELECT DISTINCT
				".implode(", ", $arSelect)."
			FROM
				b_adv_banner_2_country
			WHERE
				BANNER_ID = $BANNER_ID
			ORDER BY
				".implode(", ", $arSelect)."
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while($ar = $rs->Fetch())
		{
			if($WHAT === "COUNTRY")
				$arrRes[] = $ar["COUNTRY_ID"];
			else
				$arrRes[] = $ar;
		}
		return $arrRes;
	}

	// получаем массив времени и дней недели связанных с баннером
	public static function GetWeekdayArray($BANNER_ID)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: GetWeekdayArray<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$arrRes = array();
		$strSql = "
			SELECT DISTINCT
				C_WEEKDAY,
				C_HOUR
			FROM
				b_adv_banner_2_weekday
			WHERE
				BANNER_ID = $BANNER_ID
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch())
			$arrRes[$ar["C_WEEKDAY"]][] = $ar["C_HOUR"];
		return $arrRes;
	}

	// получаем массив рекламных кампаний связанных с баннером
	public static function GetStatAdvArray($BANNER_ID)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: GetStatAdvArray<br>Line: ";
		global $DB;
		$BANNER_ID = intval($BANNER_ID);
		if ($BANNER_ID<=0)
			return false;
		$arrRes = array();
		$strSql = "
			SELECT DISTINCT
				STAT_ADV_ID
			FROM
				b_adv_banner_2_stat_adv
			WHERE
				BANNER_ID = $BANNER_ID
			";
		$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($ar = $rs->Fetch())
			$arrRes[] = $ar["STAT_ADV_ID"];
		return $arrRes;
	}

	// проверяем поля при модификации баннера
	public static function CheckFields($arFields, $BANNER_ID, $CHECK_RIGHTS="Y")
	{
		global $strError;
		$maxLongString = 65534;
		$str = "";
		if ($CHECK_RIGHTS=="Y")
		{
			$isAdmin = CAdvContract::IsAdmin();
			$isManager = CAdvContract::IsManager();
		}
		else
		{
			$isAdmin = true;
			$isManager = true;
		}

		$arrKeys = array_keys($arFields);
		if (!in_array("CONTRACT_ID", $arrKeys) && $BANNER_ID>0)
		{
			$rsBanner = CAdvBanner::GetByID($BANNER_ID, "N");
			$arBanner = $rsBanner->Fetch();
			$CONTRACT_ID = intval($arBanner["CONTRACT_ID"]);
		}
		else
		{
			$CONTRACT_ID = intval($arFields["CONTRACT_ID"]);
		}

		if (in_array("TEMPLATE", $arrKeys))
		{
			if (mb_strlen($arFields['TEMPLATE']) > $maxLongString)
			{
				$str.= GetMessage("AD_ERROR_LONG_STRING")."<br>";
			}
		}

		if ($CONTRACT_ID>0)
		{
			$access = false;
			if ($isAdmin || $isManager)
			{
				$access = true;
			}
			else
			{
				$arrPERM = CAdvContract::GetUserPermissions($CONTRACT_ID);
				$arrPERM = $arrPERM[$CONTRACT_ID];
				if (in_array("ADD", $arrPERM))
					$access = true;
			}

			if ($access)
			{
				if ($arFields["DATE_SHOW_FROM"] <> '')
				{
					if (!CheckDateTime($arFields["DATE_SHOW_FROM"]))
						$str.= GetMessage("AD_ERROR_WRONG_DATE_SHOW_FROM_BANNER")."<br>";
				}
				if ($arFields["DATE_SHOW_TO"] <> '')
				{
					if (!CheckDateTime($arFields["DATE_SHOW_TO"]))
						$str .= GetMessage("AD_ERROR_WRONG_DATE_SHOW_TO_BANNER")."<br>";
				}

				if (in_array("arrIMAGE_ID", $arrKeys))
				{
					$arIMAGE = $arFields["arrIMAGE_ID"];
					$arIMAGE["MODULE_ID"] = "advertising";
					$strRes = CFile::CheckImageFile($arIMAGE, 0, 0, 0, array("FLASH", "IMAGE"));
					if ($strRes <> '')
						$str .= $strRes."<br>";
				}

				if (in_array("arrFlashIMAGE_ID", $arrKeys))
				{
					$arIMAGE = $arFields["arrFlashIMAGE_ID"];
					$arIMAGE["MODULE_ID"] = "advertising";
					$strRes = CFile::CheckImageFile($arIMAGE, 0, 0, 0, array("IMAGE"));
					if ($strRes <> '')
						$str .= $strRes."<br>";
				}

				if (in_array("TEMPLATE_FILES", $arrKeys) && is_array($arFields["TEMPLATE_FILES"]))
				{
					foreach ($arFields["TEMPLATE_FILES"] as $unit)
					{
						if (is_array($unit))
						{
							foreach ($unit as $file)
							{
								$file["MODULE_ID"] = "advertising";
								$strRes = CAdvBanner_all::CheckFile($file, 0, 0, 0, array("IMAGE", "VIDEO"));
								if ($strRes <> '')
									$str .= $strRes."<br>";
							}
						}
					}
				}

				if ($arFields["FLYUNIFORM"] == "Y")
				{
					if ($arFields["DATE_SHOW_FROM"] == '' or
						$arFields["DATE_SHOW_TO"] == '')
						$str .= GetMessage("AD_ERROR_FROMTO_DATE_HAVETOBE_SET")."<br>";

					if ($arFields["FIX_SHOW"] != "Y")
						$str .= GetMessage("AD_ERROR_FIXSHOW_HAVETOBE_SET")."<br>";

					if (intval($arFields["MAX_SHOW_COUNT"])<=0)
						$str .= GetMessage("AD_ERROR_MAX_SHOW_COUNT_HAVETOBE_SET")."<br>";
				}
			}
			else
			{
				if ($BANNER_ID>0)
					$str .= GetMessage("AD_ERROR_NOT_ENOUGH_PERMISSIONS_BANNER")."<br>";
				else
					$str .= GetMessage("AD_ERROR_NOT_ENOUGH_PERMISSIONS_CONTRACT")."<br>";
			}
		}
		else
		{
			$str .= GetMessage("AD_ERROR_INCORRECT_CONTRACT_ID")."<br>";
		}

		$strError .= $str;
		if ($str <> '')
			return false;
		else
			return true;
	}

	public static function CheckFile($arFile, $iMaxSize=0, $iMaxWidth=0, $iMaxHeight=0, $access_typies=array(), $bForceMD5=false, $bSkipExt=false)
	{
		if ($arFile["name"] == "")
		{
			return "";
		}

		if (preg_match("#^php://filter#i", $arFile["tmp_name"]))
		{
			return GetMessage("FILE_BAD_FILE_TYPE").".<br>";
		}

		$extension = GetFileExtension(mb_strtolower($arFile["name"]));
		switch ($extension)
		{
			case "jpg": case "jpeg": case "gif": case "bmp": case "png":	$file_type = "IMAGE"; break;
			case "swf":	$file_type = "FLASH"; break;
			case "mp4": case "webm": case "ogg": $file_type = "VIDEO"; break;
			default: $file_type = "UNKNOWN";
		}

		// IMAGE by default
		$flashEnabled = false;
		if(!in_array($file_type, $access_typies))
		{
			$file_type = "IMAGE";
		}

		if ($file_type == "FLASH")
		{
			$flashEnabled = true;
			static $flashMime = array("application/x-shockwave-flash", "application/vnd.adobe.flash.movie");
			$res = CFile::CheckFile($arFile, $iMaxSize, $flashMime, CFile::GetFlashExtensions(), $bForceMD5, $bSkipExt);
		}
		else if ($file_type == "VIDEO")
		{
			$res = CFile::CheckFile($arFile, $iMaxSize, "video/", "mp4,webm,ogg", $bForceMD5, $bSkipExt);
		}
		else
		{
			$res = CFile::CheckFile($arFile, $iMaxSize, "image/", CFile::GetImageExtensions(), $bForceMD5, $bSkipExt);
		}

		if($res <> '')
		{
			return $res;
		}

		if ($file_type == 'IMAGE' || $file_type == "FLASH")
		{
			$imgArray = CFile::GetImageSize($arFile["tmp_name"], true, $flashEnabled);
			if(is_array($imgArray))
			{
				$intWIDTH = $imgArray[0];
				$intHEIGHT = $imgArray[1];
			}
			else
			{
				return GetMessage("FILE_BAD_FILE_TYPE").".<br>";
			}
			//check for dimensions
			if($iMaxWidth > 0 && ($intWIDTH > $iMaxWidth || $intWIDTH == 0) || $iMaxHeight > 0 && ($intHEIGHT > $iMaxHeight || $intHEIGHT == 0))
			{
				return GetMessage("FILE_BAD_MAX_RESOLUTION")." (".$iMaxWidth." * ".$iMaxHeight." ".GetMessage("main_include_dots").").<br>";
			}
		}

		return null;
	}

	public static function makeFileArray($data, $del = false, $description = null, $options = array())
	{
		$emptyFile = array(
			"name" => null,
			"type" => null,
			"tmp_name" => null,
			"error" => 4,
			"size" => 0,
		);

		if ($del)
		{
			$result = $emptyFile;
			$result["del"] = "Y";
		}
		elseif (is_null($data))
		{
			$result = $emptyFile;
		}
		elseif (is_numeric($data))
		{
			$result = self::makeFileArrayFromId($data, $description, $options);
			if ($result === false)
				$result = $emptyFile;
		}
		elseif (is_string($data))
		{
			$result = self::makeFileArrayFromPath($data, $description, $options);
			if ($result === false)
				$result = $emptyFile;
		}
		elseif (is_array($data))
		{
			$result = self::makeFileArrayFromArray($data, $description, $options);
			if ($result === false)
				$result = $emptyFile;
		}
		else
		{
			$result = $emptyFile;
		}

		return $result;
	}

	private static function makeFileArrayFromId($file_id, $description = null, $options = array())
	{
		$result = false;

		$result = CFile::MakeFileArray($file_id);
		if (is_array($result))
		{
			if (!is_null($description))
				$result["description"] = $description;
		}

		return $result;
	}

	private static function makeFileArrayFromPath($file_path, $description = null, $options = array())
	{
		/** @global CMain $APPLICATION */
		global $APPLICATION;
		$result = false;

		if (preg_match("/^https?:\\/\\//", $file_path))
		{
			$result = CFile::MakeFileArray($file_path);
		}
		else
		{
			$io = CBXVirtualIo::GetInstance();
			$normPath = $io->CombinePath("/", $file_path);
			$absPath = $io->CombinePath($_SERVER["DOCUMENT_ROOT"], $normPath);
			if ($io->ValidatePathString($absPath) && $io->FileExists($absPath))
			{
				$perm = $APPLICATION->GetFileAccessPermission($normPath);
				if ($perm >= "W")
					$result = CFile::MakeFileArray($io->GetPhysicalName($absPath));
			}
		}

		if (is_array($result))
		{
			if (!is_null($description))
				$result["description"] = $description;
		}

		return $result;
	}

	private static function makeFileArrayFromArray($file_array, $description = null, $options = array())
	{
		$result = false;

		if (is_uploaded_file($file_array["tmp_name"]))
		{
			$result = $file_array;
			if (!is_null($description))
				$result["description"] = $description;
		}
		elseif (
			$file_array["tmp_name"] <> ''
			&& mb_strpos($file_array["tmp_name"], CTempFile::GetAbsoluteRoot()) === 0
		)
		{
			$io = CBXVirtualIo::GetInstance();
			$absPath = $io->CombinePath("/", $file_array["tmp_name"]);
			$tmpPath = CTempFile::GetAbsoluteRoot()."/";
			if (mb_strpos($absPath, $tmpPath) === 0 || (($absPath = ltrim($absPath, "/")) && mb_strpos($absPath, $tmpPath) === 0))
			{
				$result = $file_array;
				$result["tmp_name"] = $absPath;
				$result["error"] = intval($result["error"]);
				if (!is_null($description))
					$result["description"] = $description;
			}
		}
		elseif ($file_array["tmp_name"] <> '')
		{
			$io = CBXVirtualIo::GetInstance();
			$normPath = $io->CombinePath("/", $file_array["tmp_name"]);
			$absPath = $io->CombinePath(CTempFile::GetAbsoluteRoot(), $normPath);
			$tmpPath = CTempFile::GetAbsoluteRoot()."/";
			if (mb_strpos($absPath, $tmpPath) === 0 && $io->FileExists($absPath) ||
				($absPath = $io->CombinePath($_SERVER["DOCUMENT_ROOT"], $normPath)) && mb_strpos($absPath, $tmpPath) === 0)
			{
				$result = $file_array;
				$result["tmp_name"] = $absPath;
				$result["error"] = intval($result["error"]);
				if (!is_null($description))
					$result["description"] = $description;
			}
		}
		else
		{
			$emptyFile = array(
				"name" => null,
				"type" => null,
				"tmp_name" => null,
				"error" => 4,
				"size" => 0,
			);
			if ($file_array == $emptyFile)
			{
				$result = $emptyFile;
				if (!is_null($description))
					$result["description"] = $description;
			}
		}

		return $result;
	}

	// добавляем новый баннер или модифицируем существующий
	public static function Set($arFields, $BANNER_ID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: Set<br>Line: ";
		global $DB, $USER, $APPLICATION, $strError;
		$BANNER_ID = intval($BANNER_ID);
		if (CAdvBanner::CheckFields($arFields, $BANNER_ID, $CHECK_RIGHTS))
		{
			if ($CHECK_RIGHTS=="Y")
			{
				$USER_ID = intval($USER->GetID());
				$isAdmin = CAdvContract::IsAdmin();
				$isManager = CAdvContract::IsManager();
				$CHECK_CONTRACT_RIGHTS = "Y";
				if ($isManager)
					$CHECK_CONTRACT_RIGHTS = "N";
			}
			else
			{
				$USER_ID = 0;
				$isAdmin = $isManager = true;
				$CHECK_CONTRACT_RIGHTS = "N";
			}

			if ($BANNER_ID <= 0)
				$events = GetModuleEvents("advertising", "OnBeforeBannerAdd", true);
			else
				$events = GetModuleEvents("advertising", "OnBeforeBannerUpdate", true);

			foreach ($events as $arEvent)
			{
				if (ExecuteModuleEventEx($arEvent, array(&$arFields)) === false)
				{
					if ($err = $APPLICATION->GetException())
						$strError .= $err->GetString()."<br />";
					else
						$strError .= GetMessage("AD_ERROR_ON_HANDLER", array ("#HANDLER#" => $arEvent['MESSAGE_ID']))."<br />";

					return false;
				}
			}

			$arFields_i = array();
			$arrKeys = array_keys($arFields);

			$arBanner = array();
			if ($BANNER_ID>0)
			{
				$rsBanner = CAdvBanner::GetByID($BANNER_ID, $CHECK_RIGHTS);
				$arBanner = $rsBanner->Fetch();
				if (!in_array("CONTRACT_ID", $arrKeys))
					$CONTRACT_ID = intval($arBanner["CONTRACT_ID"]);
				else
					$CONTRACT_ID = intval($arFields["CONTRACT_ID"]);
			}
			else
			{
				$CONTRACT_ID = intval($arFields["CONTRACT_ID"]);
			}

			$modify_status = "N";

			if ($CONTRACT_ID>0)
			{
				if ($BANNER_ID<=0 && (!$isAdmin || !$isManager || in_array("STATUS_SID", $arrKeys)))
					$modify_status = "Y";

				$rsContract = CAdvContract::GetByID($CONTRACT_ID, $CHECK_CONTRACT_RIGHTS);
				$arContract = $rsContract->Fetch();

				if (($isAdmin || $isManager) && in_array("RESET_VISITOR_COUNT", $arrKeys) && $arFields["RESET_VISITOR_COUNT"])
				{
					$arFields_i["VISITOR_COUNT"] = 0;
					// если баннер уже показывался то
					if (intval($arBanner["VISITOR_COUNT"])>0)
					{
						// уменьшим счетчик у контракта
						$value = intval($arContract["VISITOR_COUNT"]) - intval($arBanner["VISITOR_COUNT"]);
						$value = ($value<0) ? 0 : $value;
						CAdvContract::Set(array("VISITOR_COUNT" => $value), $arContract["ID"], $CHECK_CONTRACT_RIGHTS);
					}
				}

				if (($isAdmin || $isManager) && in_array("RESET_SHOW_COUNT", $arrKeys) && $arFields["RESET_SHOW_COUNT"])
				{
					$arFields_i["SHOW_COUNT"] = 0;
					// если баннер уже показывался то
					if (intval($arBanner["SHOW_COUNT"])>0)
					{
						// уменьшим счетчик у контракта
						$value = intval($arContract["SHOW_COUNT"]) - intval($arBanner["SHOW_COUNT"]);
						$value = ($value<0) ? 0 : $value;
						CAdvContract::Set(array("SHOW_COUNT" => $value), $arContract["ID"], $CHECK_CONTRACT_RIGHTS);
					}
				}

				if (($isAdmin || $isManager) && in_array("FIX_SHOW", $arrKeys) && ($arFields["FIX_SHOW"]=="Y" || $arFields["FIX_SHOW"]=="N"))
					$arFields_i["FIX_SHOW"] = "'".$arFields["FIX_SHOW"]."'";

				if (($isAdmin || $isManager) && in_array("FLYUNIFORM", $arrKeys) && ($arFields["FLYUNIFORM"]=="Y" || $arFields["FLYUNIFORM"]=="N"))
					$arFields_i["FLYUNIFORM"] = "'".$arFields["FLYUNIFORM"]."'";

				if (($isAdmin || $isManager) && in_array("RESET_CLICK_COUNT", $arrKeys) && $arFields["RESET_CLICK_COUNT"])
				{
					$arFields_i["CLICK_COUNT"] = 0;
					// если на баннер уже кликали то
					if (intval($arBanner["CLICK_COUNT"])>0)
					{
						// уменьшим счетчик у контракта
						$value = intval($arContract["CLICK_COUNT"]) - intval($arBanner["CLICK_COUNT"]);
						$value = ($value<0) ? 0 : $value;
						CAdvContract::Set(array("CLICK_COUNT" => $value), $arContract["ID"], $CHECK_CONTRACT_RIGHTS);
					}
				}

				if (($isAdmin || $isManager) && in_array("KEYWORDS", $arrKeys))
					$arFields_i["KEYWORDS"] = "'".$DB->ForSql($arFields["KEYWORDS"], 2000)."'";;

				if (in_array("CONTRACT_ID", $arrKeys) && intval($arFields["CONTRACT_ID"])>0)
					$arFields_i["CONTRACT_ID"] = intval($arFields["CONTRACT_ID"]);

				if (in_array("TYPE_SID", $arrKeys) && $arFields["TYPE_SID"] <> '')
				{
					$arFields_i["TYPE_SID"] = "'".$DB->ForSql($arFields["TYPE_SID"],255)."'";
					if ("'".$DB->ForSql($arBanner["TYPE_SID"],255)."'"!=$arFields_i["TYPE_SID"])
						$modify_status = "Y";
				}

				if (in_array("NAME", $arrKeys))
					$arFields_i["NAME"] = "'".$DB->ForSql($arFields["NAME"],255)."'";

				if (in_array("GROUP_SID", $arrKeys))
					$arFields_i["GROUP_SID"] = "'".$DB->ForSql($arFields["GROUP_SID"],255)."'";

				if (in_array("ACTIVE", $arrKeys) && ($arFields["ACTIVE"]=="Y" || $arFields["ACTIVE"]=="N"))
					$arFields_i["ACTIVE"] = "'".$arFields["ACTIVE"]."'";

				if (in_array("WEIGHT", $arrKeys))
					$arFields_i["WEIGHT"] = intval($arFields["WEIGHT"]);

				if (in_array("MAX_VISITOR_COUNT", $arrKeys))
				{
					if ($arFields["MAX_VISITOR_COUNT"] <> '')
						$arFields_i["MAX_VISITOR_COUNT"] = intval($arFields["MAX_VISITOR_COUNT"]);
					else
						$arFields_i["MAX_VISITOR_COUNT"] = "null";
				}

				if (in_array("SHOWS_FOR_VISITOR", $arrKeys))
				{
					if ($arFields["SHOWS_FOR_VISITOR"] <> '')
						$arFields_i["SHOWS_FOR_VISITOR"] = intval($arFields["SHOWS_FOR_VISITOR"]);
					else
						$arFields_i["SHOWS_FOR_VISITOR"] = "null";
				}

				if (in_array("MAX_SHOW_COUNT", $arrKeys))
				{
					if ($arFields["MAX_SHOW_COUNT"] <> '')
						$arFields_i["MAX_SHOW_COUNT"] = intval($arFields["MAX_SHOW_COUNT"]);
					else
						$arFields_i["MAX_SHOW_COUNT"] = "null";
				}

				if (in_array("MAX_CLICK_COUNT", $arrKeys))
				{
					if ($arFields["MAX_CLICK_COUNT"] <> '')
						$arFields_i["MAX_CLICK_COUNT"] = intval($arFields["MAX_CLICK_COUNT"]);
					else
						$arFields_i["MAX_CLICK_COUNT"] = "null";
				}

				if (in_array("DATE_SHOW_FROM", $arrKeys))
				{
					if ($arFields["DATE_SHOW_FROM"] <> '')
						$arFields_i["DATE_SHOW_FROM"] = $DB->CharToDateFunction($arFields["DATE_SHOW_FROM"]);
					else
						$arFields_i["DATE_SHOW_FROM"] = "null";
				}

				if (in_array("DATE_SHOW_TO", $arrKeys))
				{
					if ($arFields["DATE_SHOW_TO"] <> '')
					{
						$time = "";
						if(defined("FORMAT_DATE") && mb_strlen($arFields["DATE_SHOW_TO"]) <= mb_strlen(FORMAT_DATE))
						{
							$time = " 23:59:59";
						}
						$arFields_i["DATE_SHOW_TO"] = $DB->CharToDateFunction($arFields["DATE_SHOW_TO"].$time);
					}
					else
					{
						$arFields_i["DATE_SHOW_TO"] = "null";
					}
				}

				if (in_array("DATE_SHOW_FIRST", $arrKeys))
					$arFields_i["DATE_SHOW_FIRST"] = "null";

				if (in_array("arrIMAGE_ID", $arrKeys) && is_array($arFields["arrIMAGE_ID"]))
				{
					$arIMAGE = $arFields["arrIMAGE_ID"];
					$arIMAGE["MODULE_ID"] = "advertising";
					if ($BANNER_ID>0)
					{
						$z = $DB->Query("SELECT IMAGE_ID FROM b_adv_banner WHERE ID='$BANNER_ID'", false, $err_mess.__LINE__);
						$zr = $z->Fetch();
						$arIMAGE["old_file"] = $zr["IMAGE_ID"];
					}
					if ($arIMAGE["name"] <> '' || $arIMAGE["del"] <> '')
					{
						$subdir = COption::GetOptionString("advertising", "UPLOAD_SUBDIR");
						$fid = CFile::SaveFile($arIMAGE, $subdir);
						if (intval($fid)>0)
						{
							$arFields_i["IMAGE_ID"] = intval($fid);
						}
						else
						{
							$arFields_i["IMAGE_ID"] = "null";
						}
						if (intval($arBanner["IMAGE_ID"])!=intval($arFields_i["IMAGE_ID"]))
						{
							$modify_status = "Y";
						}
					}
				}

				if (in_array("TEMPLATE_FILES", $arrKeys) && is_array($arFields["TEMPLATE_FILES"]))
				{
					if ($BANNER_ID>0)
					{
						$z = $DB->Query("SELECT TEMPLATE_FILES FROM b_adv_banner WHERE ID='$BANNER_ID'", false, $err_mess.__LINE__);
						$zr = $z->Fetch();
						if (CheckSerializedData($zr['TEMPLATE_FILES']))
							$zr = unserialize($zr['TEMPLATE_FILES'], ['allowed_classes' => false]);
						else
							$zr = array();
					}
					$subdir = COption::GetOptionString("advertising", "UPLOAD_SUBDIR");
					$arTemplateFiles = array();
					$cnt = 0;
					foreach ($arFields["TEMPLATE_FILES"] as $tfk => $tfv)
					{
						if (is_array($tfv))
						{
							foreach($tfv as $k => $arFile)
							{
								$arFile["MODULE_ID"] = "advertising";
								if(isset($zr[$arFile['lastKey']][$k]))
									$arFile["old_file"] = $zr[$arFile['lastKey']][$k];

								if ($arFile["name"] <> '' || $arFile["description"] <> '' || isset($arFile["del"]) && $arFile["del"] <> '')
								{
									$fid = CFile::SaveFile($arFile, $subdir);

									if ($fid === false && $arFile["description"] <> '' && $arFile["old_file"] <> '')
									{
										$arTemplateFiles[$cnt][$k] = intval($arFile["old_file"]);
										continue;
									}

									if (intval($fid)>0)
									{
										$arTemplateFiles[$cnt][$k] = intval($fid);
									}
									else
									{
										$arTemplateFiles[$cnt][$k] = "null";
									}
									if (intval($zr[$arFile['lastKey']][$k]) != intval($arTemplateFiles[$cnt][$k]))
									{
										$modify_status = "Y";
									}
								}
								else
								{
									$arTemplateFiles[$cnt][$k] = $zr[$arFile['lastKey']][$k];
								}
							}
						}
						$cnt++;
					}
					$arFields_i["TEMPLATE_FILES"] = "'".$DB->ForSql(serialize($arTemplateFiles),2000)."'";
				}

				if (in_array("IMAGE_ALT", $arrKeys))
				{
					$arFields_i["IMAGE_ALT"] = "'".$DB->ForSql($arFields["IMAGE_ALT"],255)."'";
					if ("'".$DB->ForSql($arBanner["IMAGE_ALT"],255)."'"!=$arFields_i["IMAGE_ALT"])
					{
						$modify_status = "Y";
					}
				}

				if (in_array("URL", $arrKeys))
				{
					$arFields_i["URL"] = "'".$DB->ForSql($arFields["URL"])."'";
					if ("'".$DB->ForSql($arBanner["URL"],2000)."'" != $arFields_i["URL"])
					{
						$modify_status = "Y";
					}
				}

				if (in_array("URL_TARGET", $arrKeys))
				{
					$arFields_i["URL_TARGET"] = "'".$DB->ForSql($arFields["URL_TARGET"], 255)."'";
					if ("'".$DB->ForSql($arBanner["URL_TARGET"], 255)."'" != $arFields_i["URL_TARGET"])
					{
						$modify_status = "Y";
					}
				}

				if (in_array("NO_URL_IN_FLASH", $arrKeys) && ($arFields["NO_URL_IN_FLASH"]=="Y" || $arFields["NO_URL_IN_FLASH"]=="N"))
				{
					$arFields_i["NO_URL_IN_FLASH"] = "'".$arFields["NO_URL_IN_FLASH"]."'";
					if ($arBanner["NO_URL_IN_FLASH"]!=$arFields_i["NO_URL_IN_FLASH"])
					{
						$modify_status = "Y";
					}
				}

				if (in_array("CODE", $arrKeys))
				{
					$arFields_i["CODE"] = $arFields["CODE"];
					if ($arBanner["CODE"] != $arFields_i["CODE"])
					{
						$modify_status = "Y";
					}
				}

				if (in_array("FLASH_JS", $arrKeys) && ($arFields["FLASH_JS"]=="Y" || $arFields["FLASH_JS"]=="N"))
				{
					$arFields_i["FLASH_JS"] = "'".$arFields["FLASH_JS"]."'";
					if ("'" . $arBanner["FLASH_JS"] . "'" != $arFields_i["FLASH_JS"])
					{
						$modify_status = "Y";
					}
				}

				if (in_array("FLASH_VER", $arrKeys))
				{
					$arFields_i["FLASH_VER"] = "'".$DB->ForSQL($arFields["FLASH_VER"], 20)."'";
					if ("'".$DB->ForSQL($arBanner["FLASH_VER"], 20)."'" != $arFields_i["FLASH_VER"])
					{
						$modify_status = "Y";
					}
				}

				if (in_array("arrFlashIMAGE_ID", $arrKeys) && is_array($arFields["arrFlashIMAGE_ID"]))
				{
					$arrFlashIMAGE = $arFields["arrFlashIMAGE_ID"];
					$arrFlashIMAGE["MODULE_ID"] = "advertising";
					if ($BANNER_ID>0)
					{
						$z = $DB->Query("SELECT FLASH_IMAGE FROM b_adv_banner WHERE ID='$BANNER_ID'", false, $err_mess.__LINE__);
						if($zr = $z->Fetch())
						{
							$arrFlashIMAGE["old_file"] = $zr["FLASH_IMAGE"];
						}
					}
					if ($arrFlashIMAGE["name"] <> '' || $arrFlashIMAGE["del"] <> '')
					{
						$subdir = COption::GetOptionString("advertising", "UPLOAD_SUBDIR");
						$fid = CFile::SaveFile($arrFlashIMAGE, $subdir);
						if (intval($fid)>0)	$arFields_i["FLASH_IMAGE"] = intval($fid);
						else $arFields_i["FLASH_IMAGE"] = "null";
						if (intval($arBanner["FLASH_IMAGE"])!=intval($arFields_i["FLASH_IMAGE"]))
						{
							$modify_status = "Y";
						}
					}
				}

				if (in_array("AD_TYPE", $arrKeys))
				{
					$arFields_i["AD_TYPE"] = "'".$DB->ForSql($arFields["AD_TYPE"],20)."'";
					if ("'".$DB->ForSql($arBanner["AD_TYPE"], 20)."'"!=$arFields_i["AD_TYPE"])
					{
						$modify_status = "Y";
					}
				}

				if (in_array("TEMPLATE", $arrKeys))
				{
					$arFields_i["TEMPLATE"] = CAdvBanner::addBindField($arFields["TEMPLATE"], $arBanner["TEMPLATE"], $modify_status);
				}

				if (in_array("FLASH_TRANSPARENT", $arrKeys))
				{
					$arFields_i["FLASH_TRANSPARENT"] = "'".$DB->ForSql($arFields["FLASH_TRANSPARENT"],11)."'";
					if ("'".$DB->ForSql($arBanner["FLASH_TRANSPARENT"],11)."'"!=$arFields_i["FLASH_TRANSPARENT"])
					{
						$modify_status = "Y";
					}
				}

				if (in_array("CODE_TYPE", $arrKeys))
				{
					$arFields_i["CODE_TYPE"] = ($arFields["CODE_TYPE"]=="text") ? "'text'" : "'html'";
					$value = ($arBanner["CODE_TYPE"]=="text") ? "'text'" : "'html'";
					if ($value!=$arFields_i["CODE_TYPE"])
					{
						$modify_status = "Y";
					}
				}

				if (in_array("FOR_NEW_GUEST", $arrKeys))
				{
					if ($arFields["FOR_NEW_GUEST"]=="Y" || $arFields["FOR_NEW_GUEST"]=="N")
					{
						$arFields_i["FOR_NEW_GUEST"] = "'".$arFields["FOR_NEW_GUEST"]."'";
					}
					elseif ($arFields["FOR_NEW_GUEST"]=="NOT_REF" || $arFields["FOR_NEW_GUEST"]=="ALL" || $arFields["FOR_NEW_GUEST"] == '')
					{
						$arFields_i["FOR_NEW_GUEST"] = "null";
					}
				}

				if (in_array("COMMENTS", $arrKeys))
					$arFields_i["COMMENTS"] = "'".$DB->ForSql($arFields["COMMENTS"],2000)."'";

				if (($isAdmin || $isManager) && in_array("STATUS_COMMENTS", $arrKeys))
				{
					$arFields_i["STATUS_COMMENTS"] = "'".$DB->ForSql($arFields["STATUS_COMMENTS"],2000)."'";
				}

				$email_notify = "N";

				if ($modify_status=="Y" || (in_array("STATUS_SID", $arrKeys) && ($isAdmin || $isManager)))
				{
					$new_status = ($isAdmin || $isManager) ? $arFields["STATUS_SID"] : $arContract["DEFAULT_STATUS_SID"];
					$arFields_i["STATUS_SID"] = "'".$DB->ForSql($new_status,255)."'";

					// если статус изменился то
					if ("'".$DB->ForSql($arBanner["STATUS_SID"],255)."'"!=$arFields_i["STATUS_SID"])
					{
						$email_notify = "Y";
					}
				}

				if (in_array("arrSITE", $arrKeys))
				{
					$arFields_i["FIRST_SITE_ID"] = "''";
					if (is_array($arFields["arrSITE"]))
					{
						$arrSITE = array_unique($arFields["arrSITE"]);
						reset($arrSITE);
						$site_id = current($arrSITE);
						$arFields_i["FIRST_SITE_ID"] = "'".$DB->ForSql($site_id,2)."'";
					}
				}

				if (in_array("SHOW_USER_GROUP", $arrKeys))
				{
					if($arFields["SHOW_USER_GROUP"] == "Y" && (in_array("arrUSERGROUP", $arrKeys) && count($arFields["arrUSERGROUP"])>0))
						$SHOW_USER_GROUP = "Y";
					else
						$SHOW_USER_GROUP = "N";
					$arFields_i["SHOW_USER_GROUP"] = "'".$DB->ForSql($SHOW_USER_GROUP,1)."'";
				}

				if (in_array("STAT_TYPE", $arrKeys))
				{
					if($arFields["STAT_TYPE"] === "CITY")
						$arFields_i["STAT_TYPE"] = "'CITY'";
					elseif($arFields["STAT_TYPE"] === "REGION")
						$arFields_i["STAT_TYPE"] = "'REGION'";
					else
						$arFields_i["STAT_TYPE"] = "'COUNTRY'";
				}

				if (intval($BANNER_ID)>0)
				{
					if (in_array("DATE_MODIFY", $arrKeys) && CheckDateTime($arFields["DATE_MODIFY"]))
						$arFields_i["DATE_MODIFY"] = $DB->CharToDateFunction($arFields["DATE_MODIFY"]);
					else
						$arFields_i["DATE_MODIFY"] = $DB->GetNowFunction();

					if (in_array("MODIFIED_BY", $arrKeys))
						$arFields_i["MODIFIED_BY"] = intval($arFields["MODIFIED_BY"]);
					else
						$arFields_i["MODIFIED_BY"] = $USER_ID;

					CAdvBanner::Update($arFields_i, $BANNER_ID);
				}
				else
				{
					if (in_array("DATE_CREATE", $arrKeys) && CheckDateTime($arFields["DATE_CREATE"]))
						$arFields_i["DATE_CREATE"] = $DB->CharToDateFunction($arFields["DATE_CREATE"]);
					else
						$arFields_i["DATE_CREATE"] = $DB->GetNowFunction();

					if (in_array("CREATED_BY", $arrKeys)) $arFields_i["CREATED_BY"] = intval($arFields["CREATED_BY"]);
					else $arFields_i["CREATED_BY"] = $USER_ID;

					if (in_array("DATE_MODIFY", $arrKeys) && CheckDateTime($arFields["DATE_MODIFY"]))
						$arFields_i["DATE_MODIFY"] = $DB->CharToDateFunction($arFields["DATE_MODIFY"]);
					else
						$arFields_i["DATE_MODIFY"] = $DB->GetNowFunction();

					if (in_array("MODIFIED_BY", $arrKeys))
						$arFields_i["MODIFIED_BY"] = intval($arFields["MODIFIED_BY"]);
					else
						$arFields_i["MODIFIED_BY"] = $USER_ID;

					$BANNER_ID = CAdvBanner::Add($arFields_i);
				}

				if (defined('BX_COMP_MANAGED_CACHE'))
				{
					$taggedCache = Application::getInstance()->getTaggedCache();
					$taggedCache->clearByTag('advertising_banner_type_'.$arFields['TYPE_SID']);
				}

				$BANNER_ID = intval($BANNER_ID);

				if ($BANNER_ID>0)
				{
					if (in_array("arrSITE", $arrKeys))
					{
						CAdvBanner::DeleteSiteLink($BANNER_ID);
						if (is_array($arFields["arrSITE"]))
						{
							$arrSITE = array_unique($arFields["arrSITE"]);
							reset($arrSITE);
							foreach($arrSITE as $sid)
							{
								if (trim($sid) <> '')
								{
									$strSql = "INSERT INTO b_adv_banner_2_site (BANNER_ID, SITE_ID) VALUES ($BANNER_ID, '".$DB->ForSql($sid, 2)."')";
									$DB->Query($strSql, false, $err_mess.__LINE__);
								}
							}
						}
					}

					if (in_array("arrSHOW_PAGE", $arrKeys))
					{
						CAdvBanner::DeletePageLink($BANNER_ID, " and SHOW_ON_PAGE='Y'");
						if (is_array($arFields["arrSHOW_PAGE"]))
						{
							$arrPage = array_unique($arFields["arrSHOW_PAGE"]);
							foreach($arrPage as $page)
							{
								$page = trim($page);
								if ($page <> '')
								{
									$arFields_i = array(
										"BANNER_ID"		=> $BANNER_ID,
										"PAGE"			=> "'".$DB->ForSql($page, 255)."'",
										"SHOW_ON_PAGE"	=> "'Y'"
										);
									$DB->Insert("b_adv_banner_2_page",$arFields_i, $err_mess.__LINE__);
								}
							}
						}
					}

					if (in_array("arrNOT_SHOW_PAGE", $arrKeys))
					{
						CAdvBanner::DeletePageLink($BANNER_ID, " and SHOW_ON_PAGE='N'");
						if (is_array($arFields["arrNOT_SHOW_PAGE"]))
						{
							$arrPage = array_unique($arFields["arrNOT_SHOW_PAGE"]);
							foreach($arrPage as $page)
							{
								$page = trim($page);
								if ($page <> '')
								{
									$arFields_i = array(
										"BANNER_ID"		=> $BANNER_ID,
										"PAGE"			=> "'".$DB->ForSql($page, 255)."'",
										"SHOW_ON_PAGE"	=> "'N'"
										);
									$DB->Insert("b_adv_banner_2_page",$arFields_i, $err_mess.__LINE__);
								}
							}
						}
					}

					if (in_array("arrCOUNTRY", $arrKeys))
					{
						$iCounter = 0;
						CAdvBanner::DeleteCountryLink($BANNER_ID);
						if (is_array($arFields["arrCOUNTRY"]))
						{
							$arrCOUNTRY = array();
							foreach($arFields["arrCOUNTRY"] as $COUNTRY)
							{
								if(is_array($COUNTRY))
								{
									$COUNTRY["COUNTRY_ID"] = trim($COUNTRY["COUNTRY_ID"]);
									if($COUNTRY["COUNTRY_ID"] == '')
										continue;
									$key = $COUNTRY["COUNTRY_ID"]."|".$COUNTRY["REGION"]."|".$COUNTRY["CITY_ID"];
									$strInsert = "'".$DB->ForSQL($COUNTRY["COUNTRY_ID"], 2)."', ".($COUNTRY["REGION"] == ''? "null": "'".$DB->ForSQL($COUNTRY["REGION"], 200)."'").", ".(intval($COUNTRY["CITY_ID"]) <= 0? "null": intval($COUNTRY["CITY_ID"]))."";
								}
								else
								{
									$COUNTRY = trim($COUNTRY);
									if($COUNTRY == '')
										continue;
									$key = $COUNTRY."||";
									$strInsert = "'".$DB->ForSQL($COUNTRY, 2)."', null, null";
								}
								$arrCOUNTRY[$key] = $strInsert;
							}
							foreach($arrCOUNTRY as $strInsert)
							{
								$strSql = "INSERT INTO b_adv_banner_2_country (BANNER_ID, COUNTRY_ID, REGION, CITY_ID) VALUES ($BANNER_ID, ".$strInsert.")";
								$DB->Query($strSql, false, $err_mess.__LINE__);
								$iCounter++;
							}
						}
						$DB->Query("UPDATE b_adv_banner SET STAT_COUNT = ".$iCounter." WHERE ID = ".$BANNER_ID);
					}

					if (in_array("arrSTAT_ADV", $arrKeys))
					{
						CAdvBanner::DeleteStatAdvLink($BANNER_ID);
						if (is_array($arFields["arrSTAT_ADV"]))
						{
							$arrSTAT_ADV = array_unique($arFields["arrSTAT_ADV"]);
							foreach($arrSTAT_ADV as $aid)
							{
								if (intval($aid)>0)
								{
									$strSql = "INSERT INTO b_adv_banner_2_stat_adv (BANNER_ID, STAT_ADV_ID) VALUES ($BANNER_ID, '".intval($aid)."')";
									$DB->Query($strSql, false, $err_mess.__LINE__);
								}
							}
						}
					}

					if (in_array("arrWEEKDAY", $arrKeys))
					{
						CAdvBanner::DeleteWeekdayLink($BANNER_ID);
						if (is_array($arFields["arrWEEKDAY"]))
						{
							$arrWeekday = array_keys($arFields["arrWEEKDAY"]);
							$arrWeekday = array_unique($arrWeekday);
							if (is_array($arrWeekday) && count($arrWeekday)>0)
							{
								foreach ($arrWeekday as $weekday)
								{
									if (is_array($arFields["arrWEEKDAY"][$weekday]) && count($arFields["arrWEEKDAY"][$weekday])>0)
									{
										$arrHour = $arFields["arrWEEKDAY"][$weekday];
										array_walk(
											$arrHour,
											function (&$item) {
												$item = (int)$item;
											}
										);
										$arrHour = array_unique($arrHour);
										foreach($arrHour as $hour)
										{
											if ($hour>=0 && $hour<=23)
											{
												$strSql = "INSERT INTO b_adv_banner_2_weekday (BANNER_ID, C_WEEKDAY, C_HOUR) VALUES ($BANNER_ID, '".$DB->ForSql($weekday, 10)."', $hour)";
												$DB->Query($strSql, false, $err_mess.__LINE__);
											}
										}
									}
								}
							}
						}
					}

					if (in_array("arrUSERGROUP", $arrKeys))
					{
						CAdvBanner::DeleteGroupLink($BANNER_ID);
						if (is_array($arFields["arrUSERGROUP"]))
						{
							$arrGROUPS = array_unique($arFields["arrUSERGROUP"]);
							foreach($arrGROUPS as $uid)
							{
								if (intval($uid)>0)
								{
									$strSql = "INSERT INTO b_adv_banner_2_group (BANNER_ID, GROUP_ID) VALUES ($BANNER_ID, ".intval($uid).")";
									$DB->Query($strSql, false, $err_mess.__LINE__);
								}
							}
						}
					}

					// если необходимо оповестить
					$SEND_EMAIL = $arFields["SEND_EMAIL"] == "N" ? "N" : "Y";
					if ($email_notify == "Y" && (!$isAdmin || !$isManager || $SEND_EMAIL == "Y"))
					{
						// получаем данные по баннеру
						CTimeZone::Disable();
						$rsBanner = CAdvBanner::GetByID($BANNER_ID, $CHECK_RIGHTS);
						CTimeZone::Enable();

						if ($arBanner=$rsBanner->Fetch())
						{
							$BCC = array();
							$OWNER_EMAIL = array();
							$ADD_EMAIL = array();
							$STAT_EMAIL = array();
							$EDIT_EMAIL = array();

							$MANAGER_EMAIL = CAdvContract::GetManagerEmails();
							$ADMIN_EMAIL = CAdvContract::GetAdminEmails();
							$ADMIN_EMAIL = array_merge($MANAGER_EMAIL, $ADMIN_EMAIL);
							$ADMIN_EMAIL = array_unique($ADMIN_EMAIL);
							CAdvContract::GetOwnerEmails($CONTRACT_ID, $OWNER_EMAIL, $ADD_EMAIL, $STAT_EMAIL, $EDIT_EMAIL);

							$CREATED_BY = $MODIFIED_BY = 0;
							if (intval($arBanner["CREATED_BY"])>0)
							{
								$rsUser = CUser::GetByID($arBanner["CREATED_BY"]);
								if ($arUser = $rsUser->Fetch())
								{
									$CREATED_BY = "[".$arUser["ID"]."] (".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
								}
							}
							if (intval($arBanner["MODIFIED_BY"])==intval($arBanner["CREATED_BY"]) && intval($arBanner["CREATED_BY"])>0)
							{
								$MODIFIED_BY = $CREATED_BY;
							}
							elseif (intval($arBanner["MODIFIED_BY"])>0)
							{
								$rsUser = CUser::GetByID($arBanner["MODIFIED_BY"]);
								if ($arUser = $rsUser->Fetch())
								{
									$MODIFIED_BY = "[".$arUser["ID"]."] (".$arUser["LOGIN"].") ".$arUser["NAME"]." ".$arUser["LAST_NAME"];
								}
							}

							$arImage = CFile::GetFileArray($arBanner["IMAGE_ID"]);
							if ($arImage)
								$IMAGE_LINK = CHTTP::URN2URI($arImage["SRC"]);
							else
								$IMAGE_LINK = "";

							$arImage = CFile::GetFileArray($arBanner["FLASH_IMAGE"]);
							if ($arImage)
								$FLASHIMAGE_LINK = CHTTP::URN2URI($arImage["SRC"]);
							else
								$FLASHIMAGE_LINK = "";

							$EMAIL_TO = $OWNER_EMAIL;
							if (count($EMAIL_TO)<=0)
							{
								$EMAIL_TO = $ADMIN_EMAIL;
							}
							else $BCC = $ADMIN_EMAIL;

							$arEventFields = array(
								"ID"					=> $arBanner["ID"],
								"EMAIL_TO"			=> implode(",", $EMAIL_TO),
								"ADMIN_EMAIL"			=> implode(",", $ADMIN_EMAIL),
								"ADD_EMAIL"			=> implode(",", $ADD_EMAIL),
								"STAT_EMAIL"			=> implode(",", $STAT_EMAIL),
								"EDIT_EMAIL"			=> implode(",", $EDIT_EMAIL),
								"OWNER_EMAIL"			=> implode(",", $OWNER_EMAIL),
								"BCC"				=> implode(",", $BCC),
								"CONTRACT_ID"			=> $CONTRACT_ID,
								"CONTRACT_NAME"		=> $arContract["NAME"],
								"TYPE_SID"			=> $arBanner["TYPE_SID"],
								"TYPE_NAME"			=> $arBanner["TYPE_NAME"],
								"STATUS"				=> ( ( $arBanner["STATUS_SID"] <> '' ) ? GetMessage( "AD_STATUS_" . $arBanner["STATUS_SID"] ) : "" ),
								"STATUS_COMMENTS"		=> $arBanner["STATUS_COMMENTS"],
								"NAME"				=> $arBanner["NAME"],
								"GROUP_SID"			=> $arBanner["GROUP_SID"],
								"INDICATOR"			=> GetMessage("AD_".mb_strtoupper($arBanner["LAMP"])."_BANNER_STATUS"),
								"ACTIVE"				=> $arBanner["ACTIVE"],
								"MAX_SHOW_COUNT"		=> $arBanner["MAX_SHOW_COUNT"],
								"SHOW_COUNT"			=> $arBanner["SHOW_COUNT"],
								"MAX_CLICK_COUNT"		=> $arBanner["MAX_CLICK_COUNT"],
								"CLICK_COUNT"			=> $arBanner["CLICK_COUNT"],
								"DATE_LAST_SHOW"		=> $arBanner["DATE_LAST_SHOW"],
								"DATE_LAST_CLICK"		=> $arBanner["DATE_LAST_CLICK"],
								"DATE_SHOW_FROM"		=> $arBanner["DATE_SHOW_FROM"],
								"DATE_SHOW_TO"			=> $arBanner["DATE_SHOW_TO"],
								"IMAGE_LINK"			=> $IMAGE_LINK,
								"IMAGE_ALT"			=> $arBanner["IMAGE_ALT"],
								"URL"				=> $arBanner["URL"],
								"URL_TARGET"			=> $arBanner["URL_TARGET"],
								"NO_URL_IN_FLASH"		=> $arBanner["NO_URL_IN_FLASH"],
								"CODE"				=> $arBanner["CODE"],
								"CODE_TYPE"			=> $arBanner["CODE_TYPE"],
								"COMMENTS"			=> $arBanner["COMMENTS"],
								"DATE_CREATE"			=> $arBanner["DATE_CREATE"],
								"CREATED_BY"			=> $CREATED_BY,
								"DATE_MODIFY"			=> $arBanner["DATE_MODIFY"],
								"MODIFIED_BY"			=> $MODIFIED_BY,
								"AD_TYPE"				=> $arBanner["AD_TYPE"],
								"TEMPLATE"				=> unserialize($arBanner["TEMPLATE"], ['allowed_classes' => false]),
								"FLASH_TRANSPARENT" => $arBanner["FLASH_TRANSPARENT"],
								"FLASH_IMAGE_LINK" => $FLASHIMAGE_LINK,
								"FLASH_JS" => $arBanner["FLASH_JS"],
								"FLASH_VER" => $arBanner["FLASH_VER"],
							);
							$arrSITE = CAdvBanner::GetSiteArray($arBanner["ID"]);
							CEvent::Send("ADV_BANNER_STATUS_CHANGE", $arrSITE, $arEventFields);
						}
					}
				}
			}
		}
		return $BANNER_ID;
	}

	public static function SetKeywords($keywords, $TYPE_SID="", $LOGIC="DESIRED")
	{
		global $arrADV_KEYWORDS;
		if ($LOGIC == '') return;
		if ($TYPE_SID == '') $TYPE_SID = "";
		$arrKeywords = array();
		if (is_array($keywords) && count($keywords)>0)
		{
			foreach($keywords as $word)
			{
				if (is_array($word))
				{
					$exact_match = $word["EXACT_MATCH"]=="Y" ? "Y" : "N";
					$value = $word["KEYWORD"];
				}
				else
				{
					$exact_match = "N";
					$value = $word;
				}
				$arrKeywords[$exact_match][] = trim($value);
			}
		}
		else
		{
			$arrWords = explode(",",$keywords);
			if (is_array($arrWords) && count($arrWords)>0)
			{
				foreach($arrWords as $word)
				{
					if (trim($word) <> '')
						$arrKeywords["N"][] = trim($word);
				}
			}
		}
		if(!is_set($arrADV_KEYWORDS, $TYPE_SID)) $arrADV_KEYWORDS[$TYPE_SID] = array();
		$arr = array("Y","N");
		foreach ($arr as $exact_match)
		{
			$arrWords = is_array($arrKeywords[$exact_match]) ? array_unique($arrKeywords[$exact_match]) : array();
			if (count($arrWords)>0)
			{
				$arrTemp = $arrADV_KEYWORDS[$TYPE_SID][$LOGIC][$exact_match];
				if (is_array($arrTemp) && count($arrTemp)>0)
				{
					$arrTemp = array_merge($arrWords, $arrTemp);
					$arrTemp = array_unique($arrTemp);
					$arrADV_KEYWORDS[$TYPE_SID][$LOGIC][$exact_match] = $arrTemp;
				}
				else $arrADV_KEYWORDS[$TYPE_SID][$LOGIC][$exact_match] = $arrWords;
			}
		}
	}

	public static function GetKeywords($TYPE_SID="", $LOGIC="", $EXACT_MATCH="")
	{
		global $arrADV_KEYWORDS, $APPLICATION;
		$arrReturn = $arrADV_KEYWORDS;

		if(
			!is_array($arrADV_KEYWORDS)
			|| (
				!array_key_exists("", $arrADV_KEYWORDS)
				&& !array_key_exists($TYPE_SID, $arrADV_KEYWORDS)
			)
		)
		{
			$keywords = $APPLICATION->GetProperty("adv_desired_target_keywords");
			if($keywords === false)
				$keywords = $APPLICATION->GetProperty("keywords");
			$arrWords = explode(",", $keywords);

			$arrKeywords = array();
			foreach($arrWords as $word)
			{
				$word = trim($word);
				if($word <> '')
					$arrKeywords[] = $word;
			}

			if(count($arrKeywords) > 0)
				$arrReturn[$TYPE_SID]["DESIRED"]["N"] = $arrKeywords;
		}

		if($TYPE_SID <> '')
		{
			if($LOGIC <> '')
			{
				if($EXACT_MATCH <> '')
					return $arrReturn[$TYPE_SID][$LOGIC][$EXACT_MATCH];
				else
					return $arrReturn[$TYPE_SID][$LOGIC];
			}
			else
			{
				return $arrReturn[$TYPE_SID];
			}
		}
		else
		{
			return $arrReturn;
		}
	}

	public static function ResetKeywords($TYPE_SID="", $LOGIC="", $EXACT_MATCH="")
	{
		global $arrADV_KEYWORDS;
		if ($TYPE_SID <> '')
		{
			if ($LOGIC <> '')
			{
				if ($EXACT_MATCH <> '') $arrADV_KEYWORDS[$TYPE_SID][$LOGIC][$EXACT_MATCH] = array();
				else $arrADV_KEYWORDS[$TYPE_SID][$LOGIC] = array();
			}
			else $arrADV_KEYWORDS[$TYPE_SID] = array();
		}
		else $arrADV_KEYWORDS = array();
	}

	public static function SetRequiredKeywords($keywords, $TYPE_SID="")
	{
		CAdvBanner::SetKeywords($keywords, $TYPE_SID, "REQUIRED");
	}

	public static function SetDesiredKeywords($keywords, $TYPE_SID="")
	{
		CAdvBanner::SetKeywords($keywords, $TYPE_SID, "DESIRED");
	}

	public static function GetRequiredKeywords($TYPE_SID="", $EXACT_MATCH="")
	{
		return CAdvBanner::GetKeywords($TYPE_SID, "REQUIRED", $EXACT_MATCH);
	}

	public static function GetDesiredKeywords($TYPE_SID="", $EXACT_MATCH="")
	{
		return CAdvBanner::GetKeywords($TYPE_SID, "DESIRED", $EXACT_MATCH);
	}

	public static function arr_comp_uniform($a, $b)
	{
		if ($a["val"] < $b["val"])
			return -1;
		elseif ($a["val"] > $b["val"])
			return 1;
		else
			return 0;
	}

	// возвращает массив описывающий произвольный баннер
	public static function GetRandom($TYPE_SID)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: GetRandom<br>Line: ";
		global $APPLICATION, $DB, $arrViewedBanners, $arrADV_VIEWED_BANNERS;

		static $arrWeightSum = false;

		$TYPE_SID = trim($TYPE_SID);
		if ($TYPE_SID == '')
		{
			return false;
		}

		$DONT_USE_CONTRACT = COption::GetOptionString("advertising", "DONT_USE_CONTRACT", "N");

		if ($arrWeightSum === false)
		{
			// получим массив весов для текущей страницы
			$arrWeightSum = array();

			$arrCookie_counter = array();
			// если мы уже получили на странице значение cookie то
			if (is_array($arrADV_VIEWED_BANNERS))
			{
				foreach ($arrADV_VIEWED_BANNERS as $banner_id => $arr)
				{
					$arrCookie_counter[$banner_id] = $arr["COUNTER"];
				}
			}
			else // если мы первый раз обращаемся к значению хранимому в cookie
			{
				$cookie_name = "BANNERS";
				$arr = explode(",", $APPLICATION->get_cookie($cookie_name));
				if (is_array($arr) && count($arr)>0)
				{
					foreach($arr as $str)
					{
						$ar = explode("_",$str);
						$banner_id = intval($ar[1]);
						$counter = intval($ar[2]);
						$arrCookie_counter[$banner_id] = $counter;
					}
				}
			}

			$arrWeightSum_RequiredKeywords = array();
			$arrWeightSum_DesiredKeywords = array();
			$arrWeightSum_EmptyKeywords = array();
			$arrWeightSum_all = array();
			$arKeywordsSet = array(); // заданы ли ключевые слова для того или иного типа

			$arrRequiredKeywordsBanners = array(); // массив баннеров для которых были найдены все ключевые слова
			$arrDesiredKeywordsBanners = array(); // массив баннеров для которых было найдено хотя бы одно желательное слово
			$arrEmptyKeywordsBanners = array(); // массив баннеров у которых поле "ключевые слова" не заполнено
			$arrPAGE_KEYWORDS = CAdvBanner::GetKeywords(); // массив ключевых слов заданных для данной страницы

			$arrDesiredPageKeywords_all = is_array($arrPAGE_KEYWORDS[""]["DESIRED"]) ? $arrPAGE_KEYWORDS[""]["DESIRED"] : array();
			$arrRequiredPageKeywords_all = is_array($arrPAGE_KEYWORDS[""]["REQUIRED"]) ? $arrPAGE_KEYWORDS[""]["REQUIRED"] : array();

			$rs = CAdvBanner::GetPageWeights_RS();
			while($ar=$rs->Fetch())
			{
				// Check for blocked uniformed banners
				if (isset($ar["FLYUNIFORM"]) and $ar["FLYUNIFORM"] == "Y")
				{
						$unitest = CAdvBanner_all::GetUniformityCoef($ar);
						if ($unitest >= 1.0 + BANNER_UNIFORMITY_DIVERGENCE_COEF)
							continue;
				}

				$arKeywordsSet[$ar["TYPE_SID"]] = "N";

				if ((intval($ar["SHOWS_FOR_VISITOR"])>0 && intval($arrCookie_counter[$ar["BANNER_ID"]])<intval($ar["SHOWS_FOR_VISITOR"])) || intval($ar["SHOWS_FOR_VISITOR"])<=0)
				{
					$arr = $arrPAGE_KEYWORDS[$ar["TYPE_SID"]]["DESIRED"];
					$arrDesiredPageKeywords = is_array($arr) ? $arr : array();

					$arr = $arrPAGE_KEYWORDS[$ar["TYPE_SID"]]["REQUIRED"];
					$arrRequiredPageKeywords = is_array($arr) ? $arr : array();

					if (count($arrRequiredPageKeywords)>0 ||
						count($arrRequiredPageKeywords_all)>0 ||
						count($arrDesiredPageKeywords)>0 ||
						count($arrDesiredPageKeywords_all)>0
					)
					{
						$arKeywordsSet[$ar["TYPE_SID"]] = "Y";
					}

					$arrBannerKeywords = preg_split('/[\n\r]+/', $ar["BANNER_KEYWORDS"]);
					if (is_array($arrBannerKeywords))
					{
						TrimArr($arrBannerKeywords);
					}

					if ($DONT_USE_CONTRACT <> "Y" && $ar["CONTRACT_KEYWORDS"] <> '')
					{
						$arrContractKeywords = preg_split('/[\n\r]+/', $ar["CONTRACT_KEYWORDS"]);
						if (is_array($arrContractKeywords))
						{
							TrimArr($arrContractKeywords);
						}
						$arrBannerKeywords = array_unique(array_merge($arrBannerKeywords, $arrContractKeywords));
					}

					if ($DONT_USE_CONTRACT == "Y" || !array_key_exists("CONTRACT_ID", $ar))
						$ar["CONTRACT_ID"] = 0;

					if (count($arrBannerKeywords)>0)
					{
						$found_required = true;
						if (count($arrRequiredPageKeywords)>0 || count($arrRequiredPageKeywords_all)>0)
						{
							$arr = array("Y","N"); // совпадение | вхождение
							foreach($arr as $exact_match)
							{
								$arr1 = is_array($arrRequiredPageKeywords[$exact_match]) ? $arrRequiredPageKeywords[$exact_match] : array();
								$arr2 = is_array($arrRequiredPageKeywords_all[$exact_match]) ? $arrRequiredPageKeywords_all[$exact_match] : array();
								$arrRequiredKeywords = array_unique(array_merge($arr1, $arr2));
								if (count($arrRequiredKeywords)>0)
								{
									reset($arrRequiredKeywords);
									foreach($arrRequiredKeywords as $page_word)
									{
										$page_word = mb_strtoupper($page_word);
										reset($arrBannerKeywords);
										$found = false;
										foreach($arrBannerKeywords as $banner_word)
										{
											$banner_word = mb_strtoupper($banner_word);
											// совпадение
											if ($exact_match=="Y")
											{
												if ($banner_word==$page_word)
												{
													$found = true;
													break;
												}
											}
											elseif ($exact_match=="N")
											{
												if (mb_strpos($page_word, $banner_word) !== false || mb_strpos($banner_word, $page_word) !== false)
												{
													$found = true;
													break;
												}
											}
										}
										if (!$found)
										{
											$found_required = false;
											break 2;
										}
									}
								}
							}
							// если все ключевые слова были найдены то
							if ($found_required)
							{
								// запоминаем баннер в массиве баннеров для которых были найдены все ключевые слова
								$arrRequiredKeywordsBanners[] = $ar["BANNER_ID"];
							}
						}

						// если по обязательным словам баннер подходит то проверим по желательным словам
						if ($found_required && (count($arrDesiredPageKeywords)>0 || count($arrDesiredPageKeywords_all)>0))
						{
							$found_desired = false;
							$arr = array("Y","N"); // совпадение | вхождение
							foreach($arr as $exact_match)
							{
								$arr1 = is_array($arrDesiredPageKeywords) ? $arrDesiredPageKeywords[$exact_match] : array();
								$arr2 = is_array($arrDesiredPageKeywords_all) ? $arrDesiredPageKeywords_all[$exact_match] : array();
								if (!is_array($arr1)) $arr1 = array();
								if (!is_array($arr2)) $arr2 = array();
								$arrDesiredKeywords = array_unique(array_merge($arr1, $arr2));
								if (is_array($arrDesiredKeywords) && count($arrDesiredKeywords)>0)
								{
									reset($arrDesiredKeywords);
									foreach($arrDesiredKeywords as $page_word)
									{
										$page_word = mb_strtoupper($page_word);
										reset($arrBannerKeywords);
										foreach($arrBannerKeywords as $banner_word)
										{
											$banner_word = mb_strtoupper($banner_word);
											// совпадение
											if ($exact_match=="Y")
											{
												if ($banner_word==$page_word)
												{
													$found_desired = true;
													break 3;
												}
											}
											elseif ($exact_match=="N")
											{
												if (mb_strpos($page_word, $banner_word) !== false || mb_strpos($banner_word, $page_word) !== false)
												{
													$found_desired = true;
													break 3;
												}
											}
										}
									}
								}
							}
							// если все ключевые слова были найдены то
							if ($found_desired)
							{
								// запоминаем баннер в массиве баннеров для которых были найдены все ключевые слова
								$arrDesiredKeywordsBanners[] = $ar["BANNER_ID"];
							}
						}
					}
					else
					{
						// запомнить баннеры у которых вообще не задано ключевых слов
						$arrEmptyKeywordsBanners[] = $ar["BANNER_ID"];
					}

					if (in_array($ar["BANNER_ID"], $arrRequiredKeywordsBanners))
					{
						$arrWeightSum_RequiredKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["WEIGHT"] = intval($ar["CONTRACT_WEIGHT"]);
						$arrWeightSum_RequiredKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["BANNERS"][$ar["BANNER_ID"]] = intval($ar["BANNER_WEIGHT"]);
					}
					if (in_array($ar["BANNER_ID"], $arrDesiredKeywordsBanners))
					{
						$arrWeightSum_DesiredKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["WEIGHT"] = intval($ar["CONTRACT_WEIGHT"]);
						$arrWeightSum_DesiredKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["BANNERS"][$ar["BANNER_ID"]] = intval($ar["BANNER_WEIGHT"]);
					}
					if (in_array($ar["BANNER_ID"], $arrEmptyKeywordsBanners))
					{
						$arrWeightSum_EmptyKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["WEIGHT"] = intval($ar["CONTRACT_WEIGHT"]);
						$arrWeightSum_EmptyKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["BANNERS"][$ar["BANNER_ID"]] = intval($ar["BANNER_WEIGHT"]);
					}
					$arrWeightSum_all[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["WEIGHT"] = intval($ar["CONTRACT_WEIGHT"]);
					$arrWeightSum_all[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["BANNERS"][$ar["BANNER_ID"]] = intval($ar["BANNER_WEIGHT"]);
				}
			}

			$arrAllTypies = array_keys($arrWeightSum_all);
			if (count($arrAllTypies)>0)
			{
				foreach($arrAllTypies as $tsid)
				{
					// если для данного типа ключевые слова заданы то
					if ($arKeywordsSet[$tsid]=="Y")
					{
						// желательные слова
						if (is_array($arrWeightSum_DesiredKeywords[$tsid]) && count($arrWeightSum_DesiredKeywords[$tsid])>0)
						{
							$arrWeightSum[$tsid] = $arrWeightSum_DesiredKeywords[$tsid];
						}
						// обязательные слова
						elseif (is_array($arrWeightSum_RequiredKeywords[$tsid]) && count($arrWeightSum_RequiredKeywords[$tsid])>0)
						{
							$arrWeightSum[$tsid] = $arrWeightSum_RequiredKeywords[$tsid];
						}
						// с пустыми словами
						elseif ($arKeywordsSet[$tsid]=="Y" && is_array($arrWeightSum_EmptyKeywords[$tsid]))
						{
							$arrWeightSum[$tsid] = $arrWeightSum_EmptyKeywords[$tsid];
						}
					}
					else
					{
						$arrWeightSum[$tsid] = $arrWeightSum_all[$tsid];
					}
				}
			}
		}

		$arrWSum = $arrWeightSum[$TYPE_SID];

		// если массив весов подготовлен то
		if (is_array($arrWSum) && count($arrWSum)>0)
		{
			$CONTRACT_ID = 0;

			if ($DONT_USE_CONTRACT == "N" || !array_key_exists("0", $arrWSum))
			{
				// получим сумму весов контрактов
				$intSum = 0;

				foreach ($arrWSum as $cid => $arr)
				{
					$CONTRACT_ID = $cid;
					$intSum += intval($arr["WEIGHT"]);
				}

				// выберем контракт по весу
				$intStep = 0;
				$rndWeight = $intSum * (mt_rand()/mt_getrandmax());

				foreach ($arrWSum as $cid => $arr)
				{
					if($rndWeight>=$intStep && $rndWeight<=$intStep+$arr["WEIGHT"])
					{
						$CONTRACT_ID = $cid;
						break;
					}
					$intStep += $arr["WEIGHT"];
				}
				$CONTRACT_ID = intval($CONTRACT_ID)<=0 ? 1 : intval($CONTRACT_ID);
			}

			$arrWeightBanners = $arrWSum[$CONTRACT_ID]["BANNERS"];

			// если ID контракта определен то
			if (is_array($arrWeightBanners) && count($arrWeightBanners)>0)
			{
				// получим сумму весов баннеров контракта
				$intSum = 0;
				$strBanners = "0";

				foreach ($arrWeightBanners as $bid => $weight)
				{
					if(in_array($bid, $arrViewedBanners))
						continue;
					$intSum += intval($weight);
					$strBanners .= ",".intval($bid);
				}

				if ($CONTRACT_ID>0)
				{
					$strSql = "
						SELECT
							B.*,
							".$DB->DateToCharFunction("B.DATE_SHOW_FIRST", "FULL")." DATE_SHOW_FIRST,
							".$DB->DateToCharFunction("B.DATE_SHOW_FROM", "FULL")." DATE_SHOW_FROM,
							".$DB->DateToCharFunction("B.DATE_SHOW_TO", "FULL")." DATE_SHOW_TO,
							C.MAX_VISITOR_COUNT		CONTRACT_MAX_VISITOR_COUNT
						FROM
							b_adv_banner B,
							b_adv_contract C
						WHERE
							B.CONTRACT_ID = $CONTRACT_ID
						and	B.TYPE_SID = '".$DB->ForSql($TYPE_SID,255)."'
						and B.ID in (".$strBanners.")
						and C.ID = B.CONTRACT_ID
						ORDER BY
							FLYUNIFORM DESC
						";
				}
				else
				{
					$strSql = "
						SELECT
							B.*,
							".$DB->DateToCharFunction("B.DATE_SHOW_FIRST", "FULL")." DATE_SHOW_FIRST,
							".$DB->DateToCharFunction("B.DATE_SHOW_FROM", "FULL")." DATE_SHOW_FROM,
							".$DB->DateToCharFunction("B.DATE_SHOW_TO", "FULL")." DATE_SHOW_TO
						FROM
							b_adv_banner B
						WHERE
						B.TYPE_SID = '".$DB->ForSql($TYPE_SID,255)."'
						AND B.ID in (".$strBanners.")
						ORDER BY
							FLYUNIFORM DESC
						";

				}

				$intSum = 0;
				$infVal = 0;
				$infUniform = null;
				$stubs = array();

				$rsBanners = $DB->Query($strSql, false, $err_mess.__LINE__);
				while($arBanner = $rsBanners->Fetch())
				{
					if (isset($arBanner["FLYUNIFORM"]) and $arBanner["FLYUNIFORM"] == "Y")
					{
						// Select most last (weak) rotated banner (x<<1)
						$unitest = CAdvBanner_all::GetUniformityCoef($arBanner);
						if ($unitest < $infVal or !$infUniform)
						{
							$infVal = $unitest;
							$infUniform = $arBanner;
						}
					}
					else
					{
						$intSum += intval($arBanner["WEIGHT"]);
						array_push($stubs, $arBanner); // Save stubs
					}
				}

				// Check out selected uniformed banner
				if ($infUniform == null or
					$infVal >= 1.0 + BANNER_UNIFORMITY_DIVERGENCE_COEF)
				{
					// If this, we have to stop alittle this banner, and show stub one.
					$infUniform = array();
					if (count($stubs) > 0)
					{
						$intStep = 0;
						$rndWeight = $intSum * (mt_rand()/mt_getrandmax());

						reset($stubs);
						$infUniform = current($stubs);
						if($rndWeight<$intStep or $rndWeight>$intStep+$infUniform["WEIGHT"])
						{
							$intStep += $infUniform["WEIGHT"];
							while ($infUniform = next($stubs))
							{
								if($rndWeight>=$intStep && $rndWeight<=$intStep+$infUniform["WEIGHT"])
									break;
								$intStep += $infUniform["WEIGHT"];
							}
						}
					}
				}

				$arBanner = $infUniform;

				$BANNER_ID = intval($arBanner["ID"]);
				if ($BANNER_ID>0)
				{
					unset($arrWeightSum[$TYPE_SID][$CONTRACT_ID]["BANNERS"][$arBanner["ID"]]);
					if(count($arrWeightSum[$TYPE_SID][$CONTRACT_ID]["BANNERS"])<=0)
						unset($arrWeightSum[$TYPE_SID][$CONTRACT_ID]);
					$arrViewedBanners[] = $arBanner["ID"];
				}
				return $arBanner;
			}
		}
		return null;
	}

	// возвращает массив, описывающий $quantity произвольных баннеров
	public static function GetRandomArray($TYPE_SID, $quantity = 1)
	{
		$err_mess = (CAdvBanner_all::err_mess())."<br>Function: GetRandom<br>Line: ";
		global $APPLICATION, $DB, $arrViewedBanners, $arrADV_VIEWED_BANNERS;

		static $arrWeightSum = false;

		$TYPE_SID = trim($TYPE_SID);
		if ($TYPE_SID == '')
		{
			return false;
		}

		$DONT_USE_CONTRACT = COption::GetOptionString("advertising", "DONT_USE_CONTRACT", "N");

		if ($arrWeightSum === false)
		{
			// получим массив весов для текущей страницы
			$arrWeightSum = array();

			$arrCookie_counter = array();
			// если мы уже получили на странице значение cookie то
			if (is_array($arrADV_VIEWED_BANNERS))
			{
				foreach ($arrADV_VIEWED_BANNERS as $banner_id => $arr)
				{
					$arrCookie_counter[$banner_id] = $arr["COUNTER"];
				}
			}
			else // если мы первый раз обращаемся к значению хранимому в cookie
			{
				$cookie_name = "BANNERS";
				$arr = explode(",", $APPLICATION->get_cookie($cookie_name));
				if (is_array($arr) && count($arr)>0)
				{
					foreach($arr as $str)
					{
						$ar = explode("_",$str);
						if (count($ar) >= 3)
						{
							$banner_id = intval($ar[1]);
							$counter = intval($ar[2]);
							$arrCookie_counter[$banner_id] = $counter;
						}
					}
				}
			}

			$arrWeightSum_RequiredKeywords = array();
			$arrWeightSum_DesiredKeywords = array();
			$arrWeightSum_EmptyKeywords = array();
			$arrWeightSum_all = array();
			$arKeywordsSet = array(); // заданы ли ключевые слова для того или иного типа

			$arrRequiredKeywordsBanners = array(); // массив баннеров для которых были найдены все ключевые слова
			$arrDesiredKeywordsBanners = array(); // массив баннеров для которых было найдено хотя бы одно желательное слово
			$arrEmptyKeywordsBanners = array(); // массив баннеров у которых поле "ключевые слова" не заполнено
			$arrPAGE_KEYWORDS = CAdvBanner::GetKeywords(); // массив ключевых слов заданных для данной страницы

			$arrDesiredPageKeywords_all = $arrPAGE_KEYWORDS[""]["DESIRED"] ?? [];
			$arrRequiredPageKeywords_all = $arrPAGE_KEYWORDS[""]["REQUIRED"] ?? [];

			$rs = CAdvBanner::GetPageWeights_RS();
			while($ar=$rs->Fetch())
			{
				// Check for blocked uniformed banners
				if (isset($ar["FLYUNIFORM"]) and $ar["FLYUNIFORM"] == "Y")
				{
						$unitest = CAdvBanner_all::GetUniformityCoef($ar);
						if ($unitest >= 1.0 + BANNER_UNIFORMITY_DIVERGENCE_COEF)
							continue;
				}

				$arKeywordsSet[$ar["TYPE_SID"]] = "N";

				if ((intval($ar["SHOWS_FOR_VISITOR"])>0 && intval($arrCookie_counter[$ar["BANNER_ID"]])<intval($ar["SHOWS_FOR_VISITOR"])) || intval($ar["SHOWS_FOR_VISITOR"])<=0)
				{
					$arrDesiredPageKeywords = $arrPAGE_KEYWORDS[$ar["TYPE_SID"]]["DESIRED"] ?? [];
					$arrRequiredPageKeywords = $arrPAGE_KEYWORDS[$ar["TYPE_SID"]]["REQUIRED"] ?? [];

					if (count($arrRequiredPageKeywords)>0 ||
						count($arrRequiredPageKeywords_all)>0 ||
						count($arrDesiredPageKeywords)>0 ||
						count($arrDesiredPageKeywords_all)>0
					)
					{
						$arKeywordsSet[$ar["TYPE_SID"]] = "Y";
					}

					$arrBannerKeywords = preg_split('/[\n\r]+/', $ar["BANNER_KEYWORDS"]);
					if (is_array($arrBannerKeywords))
					{
						TrimArr($arrBannerKeywords);
					}

					if ($DONT_USE_CONTRACT <> "Y" && $ar["CONTRACT_KEYWORDS"] <> '')
					{
						$arrContractKeywords = preg_split('/[\n\r]+/', $ar["CONTRACT_KEYWORDS"]);
						if (is_array($arrContractKeywords))
						{
							TrimArr($arrContractKeywords);
						}
						$arrBannerKeywords = array_unique(array_merge($arrBannerKeywords, $arrContractKeywords));
					}

					if ($DONT_USE_CONTRACT == "Y" || !array_key_exists("CONTRACT_ID", $ar))
						$ar["CONTRACT_ID"] = 0;

					if (count($arrBannerKeywords)>0)
					{
						$found_required = true;
						if (count($arrRequiredPageKeywords)>0 || count($arrRequiredPageKeywords_all)>0)
						{
							$arr = array("Y","N"); // совпадение | вхождение
							foreach($arr as $exact_match)
							{
								$arr1 = is_array($arrRequiredPageKeywords[$exact_match]) ? $arrRequiredPageKeywords[$exact_match] : array();
								$arr2 = is_array($arrRequiredPageKeywords_all[$exact_match]) ? $arrRequiredPageKeywords_all[$exact_match] : array();
								$arrRequiredKeywords = array_unique(array_merge($arr1, $arr2));
								if (count($arrRequiredKeywords)>0)
								{
									reset($arrRequiredKeywords);
									foreach($arrRequiredKeywords as $page_word)
									{
										$page_word = mb_strtoupper($page_word);
										reset($arrBannerKeywords);
										$found = false;
										foreach($arrBannerKeywords as $banner_word)
										{
											$banner_word = mb_strtoupper($banner_word);
											// совпадение
											if ($exact_match=="Y")
											{
												if ($banner_word==$page_word)
												{
													$found = true;
													break;
												}
											}
											elseif ($exact_match=="N")
											{
												if (mb_strpos($page_word, $banner_word) !== false || mb_strpos($banner_word, $page_word) !== false)
												{
													$found = true;
													break;
												}
											}
										}
										if (!$found)
										{
											$found_required = false;
											break 2;
										}
									}
								}
							}
							// если все ключевые слова были найдены то
							if ($found_required)
							{
								// запоминаем баннер в массиве баннеров для которых были найдены все ключевые слова
								$arrRequiredKeywordsBanners[] = $ar["BANNER_ID"];
							}
						}

						// если по обязательным словам баннер подходит то проверим по желательным словам
						if ($found_required && (count($arrDesiredPageKeywords)>0 || count($arrDesiredPageKeywords_all)>0))
						{
							$found_desired = false;
							$arr = array("Y","N"); // совпадение | вхождение
							foreach($arr as $exact_match)
							{
								$arr1 = is_array($arrDesiredPageKeywords) ? $arrDesiredPageKeywords[$exact_match] : array();
								$arr2 = is_array($arrDesiredPageKeywords_all) ? $arrDesiredPageKeywords_all[$exact_match] : array();
								if (!is_array($arr1)) $arr1 = array();
								if (!is_array($arr2)) $arr2 = array();
								$arrDesiredKeywords = array_unique(array_merge($arr1, $arr2));
								if (is_array($arrDesiredKeywords) && count($arrDesiredKeywords)>0)
								{
									reset($arrDesiredKeywords);
									foreach($arrDesiredKeywords as $page_word)
									{
										$page_word = mb_strtoupper($page_word);
										reset($arrBannerKeywords);
										foreach($arrBannerKeywords as $banner_word)
										{
											$banner_word = mb_strtoupper($banner_word);
											// совпадение
											if ($exact_match=="Y")
											{
												if ($banner_word==$page_word)
												{
													$found_desired = true;
													break 3;
												}
											}
											elseif ($exact_match=="N")
											{
												if (mb_strpos($page_word, $banner_word) !== false || mb_strpos($banner_word, $page_word) !== false)
												{
													$found_desired = true;
													break 3;
												}
											}
										}
									}
								}
							}
							// если все ключевые слова были найдены то
							if ($found_desired)
							{
								// запоминаем баннер в массиве баннеров для которых были найдены все ключевые слова
								$arrDesiredKeywordsBanners[] = $ar["BANNER_ID"];
							}
						}
					}
					else
					{
						// запомнить баннеры у которых вообще не задано ключевых слов
						$arrEmptyKeywordsBanners[] = $ar["BANNER_ID"];
					}

					if (in_array($ar["BANNER_ID"], $arrRequiredKeywordsBanners))
					{
						$arrWeightSum_RequiredKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["WEIGHT"] = intval($ar["CONTRACT_WEIGHT"]);
						$arrWeightSum_RequiredKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["BANNERS"][$ar["BANNER_ID"]] = intval($ar["BANNER_WEIGHT"]);
					}
					if (in_array($ar["BANNER_ID"], $arrDesiredKeywordsBanners))
					{
						$arrWeightSum_DesiredKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["WEIGHT"] = intval($ar["CONTRACT_WEIGHT"]);
						$arrWeightSum_DesiredKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["BANNERS"][$ar["BANNER_ID"]] = intval($ar["BANNER_WEIGHT"]);
					}
					if (in_array($ar["BANNER_ID"], $arrEmptyKeywordsBanners))
					{
						$arrWeightSum_EmptyKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["WEIGHT"] = intval($ar["CONTRACT_WEIGHT"]);
						$arrWeightSum_EmptyKeywords[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["BANNERS"][$ar["BANNER_ID"]] = intval($ar["BANNER_WEIGHT"]);
					}
					$arrWeightSum_all[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["WEIGHT"] = intval($ar["CONTRACT_WEIGHT"]);
					$arrWeightSum_all[$ar["TYPE_SID"]][$ar["CONTRACT_ID"]]["BANNERS"][$ar["BANNER_ID"]] = intval($ar["BANNER_WEIGHT"]);
				}
			}

			$arrAllTypies = array_keys($arrWeightSum_all);
			if (count($arrAllTypies)>0)
			{
				foreach($arrAllTypies as $tsid)
				{
					// если для данного типа ключевые слова заданы то
					if ($arKeywordsSet[$tsid]=="Y")
					{
						// желательные слова
						if (isset($arrWeightSum_DesiredKeywords[$tsid]) && is_array($arrWeightSum_DesiredKeywords[$tsid]) && !empty($arrWeightSum_DesiredKeywords[$tsid]))
						{
							$arrWeightSum[$tsid] = $arrWeightSum_DesiredKeywords[$tsid];
						}
						// обязательные слова
						elseif (isset($arrWeightSum_RequiredKeywords[$tsid]) && is_array($arrWeightSum_RequiredKeywords[$tsid]) && !empty($arrWeightSum_RequiredKeywords[$tsid]))
						{
							$arrWeightSum[$tsid] = $arrWeightSum_RequiredKeywords[$tsid];
						}
						// с пустыми словами
						elseif ($arKeywordsSet[$tsid]=="Y" && is_array($arrWeightSum_EmptyKeywords[$tsid]))
						{
							$arrWeightSum[$tsid] = $arrWeightSum_EmptyKeywords[$tsid];
						}
					}
					else
					{
						$arrWeightSum[$tsid] = $arrWeightSum_all[$tsid];
					}
				}
			}
		}

		$arrWSum = $arrWeightSum[$TYPE_SID];

		// если массив весов подготовлен то
		if (is_array($arrWSum) && count($arrWSum)>0)
		{
			$CONTRACT_ID = 0;

			if ($DONT_USE_CONTRACT == "N" || !array_key_exists("0", $arrWSum))
			{
				// получим сумму весов контрактов
				$intSum = 0;

				foreach ($arrWSum as $cid => $arr)
				{
					$CONTRACT_ID = $cid;
					$intSum += intval($arr["WEIGHT"]);
				}

				// выберем контракт по весу
				$intStep = 0;
				$rndWeight = $intSum * (mt_rand()/mt_getrandmax());

				foreach ($arrWSum as $cid => $arr)
				{
					if($rndWeight>=$intStep && $rndWeight<=$intStep+$arr["WEIGHT"])
					{
						$CONTRACT_ID = $cid;
						break;
					}
					$intStep += $arr["WEIGHT"];
				}
				$CONTRACT_ID = intval($CONTRACT_ID)<=0 ? 1 : intval($CONTRACT_ID);
			}

			$arrWeightBanners = $arrWSum[$CONTRACT_ID]["BANNERS"];

			// если ID контракта определен то
			if (is_array($arrWeightBanners) && count($arrWeightBanners)>0)
			{
				// получим сумму весов баннеров контракта
				$intSum = 0;
				$strBanners = "0";

				foreach ($arrWeightBanners as $bid => $weight)
				{
					if(in_array($bid, $arrViewedBanners))
						continue;
					$intSum += intval($weight);
					$strBanners .= ",".intval($bid);
				}

				if ($CONTRACT_ID>0)
				{
					$strSql = "
						SELECT
							B.*,
							".$DB->DateToCharFunction("B.DATE_SHOW_FIRST", "FULL")." DATE_SHOW_FIRST,
							".$DB->DateToCharFunction("B.DATE_SHOW_FROM", "FULL")." DATE_SHOW_FROM,
							".$DB->DateToCharFunction("B.DATE_SHOW_TO", "FULL")." DATE_SHOW_TO,
							C.MAX_VISITOR_COUNT		CONTRACT_MAX_VISITOR_COUNT
						FROM
							b_adv_banner B,
							b_adv_contract C
						WHERE
							B.CONTRACT_ID = $CONTRACT_ID
						and	B.TYPE_SID = '".$DB->ForSql($TYPE_SID,255)."'
						and B.ID in (".$strBanners.")
						and C.ID = B.CONTRACT_ID
						ORDER BY
							FLYUNIFORM DESC
						";
				}
				else
				{
					$strSql = "
						SELECT
							B.*,
							".$DB->DateToCharFunction("B.DATE_SHOW_FIRST", "FULL")." DATE_SHOW_FIRST,
							".$DB->DateToCharFunction("B.DATE_SHOW_FROM", "FULL")." DATE_SHOW_FROM,
							".$DB->DateToCharFunction("B.DATE_SHOW_TO", "FULL")." DATE_SHOW_TO
						FROM
							b_adv_banner B
						WHERE
						B.TYPE_SID = '".$DB->ForSql($TYPE_SID,255)."'
						AND B.ID in (".$strBanners.")
						ORDER BY
							FLYUNIFORM DESC
						";

				}

				$intSum = 0;
				$inf = array();
				$stubs = array();

				$rsBanners = $DB->Query($strSql, false, $err_mess.__LINE__);
				while($arBanner = $rsBanners->Fetch())
				{
					if (isset($arBanner["FLYUNIFORM"]) and $arBanner["FLYUNIFORM"] == "Y")
					{
						$unitest = CAdvBanner_all::GetUniformityCoef($arBanner);
						if ($unitest < 1.0 + BANNER_UNIFORMITY_DIVERGENCE_COEF)
							$inf[$arBanner['ID']] = $arBanner;
					}
					else
					{
						$intSum += intval($arBanner["WEIGHT"]);
						$stubs[$arBanner['ID']] = $arBanner; // Save stubs
					}
				}

				// Sort array from most last (weak) rotated banner (x<<1) to strongest
				uasort($inf, array('CAdvBanner_all', 'arr_comp_uniform'));

				// Sorting stubs array
				$infStubs = array();
				while (count($stubs))
				{
					$intStep = 0;
					$rndWeight = $intSum * (mt_rand()/mt_getrandmax());

					reset($stubs);
					$infCurr = current($stubs);
					if($rndWeight < $intStep or $rndWeight > $intStep + $infCurr["WEIGHT"])
					{
						$intStep += $infCurr["WEIGHT"];
						while (next($stubs))
						{
							$infCurr = current($stubs);
							if($rndWeight>=$intStep && $rndWeight<=$intStep+$infCurr["WEIGHT"])
								break;
							$intStep += $infCurr["WEIGHT"];
						}
					}
					$infStubs[$infCurr['ID']] = $infCurr;
					unset($stubs[$infCurr['ID']]);
				}

				$arBanners = array_slice(array_merge($inf, $infStubs), 0, intval($quantity));

				foreach ($arBanners as $arB)
				{
					$BANNER_ID = intval($arB["ID"]);
					if ($BANNER_ID > 0)
					{
						unset($arrWeightSum[$TYPE_SID][$CONTRACT_ID]["BANNERS"][$arB["ID"]]);
						if(count($arrWeightSum[$TYPE_SID][$CONTRACT_ID]["BANNERS"]) <= 0)
							unset($arrWeightSum[$TYPE_SID][$CONTRACT_ID]);
						$arrViewedBanners[] = $arB["ID"];
					}
				}
				return $arBanners;
			}
		}
		return null;
	}

	// Uniformity coefficient
	public static function GetUniformityCoef($arBanner)
	{
		$arProgress = 0;
		$rot = CAdvBanner_all::CalculateRotationProgress($arBanner);
		$tim = CAdvBanner_all::CalculateTimeProgress($arBanner);
		if ($rot and $tim) $arProgress = $rot/$tim;

		return $arProgress;
	}

	public static function __innerExtractBitrixDates($arBanner)
	{
		$fs = array("to" => 0, "first" => 0, "from" => 0);
		if (isset($arBanner["DATE_SHOW_TO"])) $fs["to"] = $arBanner["DATE_SHOW_TO"];
		if (isset($arBanner["DATE_SHOW_FIRST"])) $fs["first"] = $arBanner["DATE_SHOW_FIRST"];
		if (isset($arBanner["DATE_SHOW_FROM"])) $fs["from"] = $arBanner["DATE_SHOW_FROM"];
		if ($fs["to"] and mb_strstr(trim($fs["to"]), " ") == false) $fs["to"].=" 23:59:59";
		if ($fs["first"] and mb_strstr(trim($fs["first"]), " ") == false) $fs["first"].=" 00:00:00";
		if ($fs["from"] and mb_strstr(trim($fs["from"]), " ") == false) $fs["from"].=" 00:00:00";

		return $fs;
	}

	// Returns TimeDifference in seconds beetween FROM,TO Dates of a banner
	public static function CalculateTimeDiff($arBanner)
	{
		$dt = CAdvBanner_all::__innerExtractBitrixDates($arBanner);
		if (!$dt["to"]) return 0;

		// 05.04.2007 19:26:26

		$dtformat = "DD.MM.YYYY HH:MI:SS";
		$stmpfirst = MakeTimeStamp($dt["first"], $dtformat);
		$stmpfrom = MakeTimeStamp($dt["from"], $dtformat);
		$stmpto = MakeTimeStamp($dt["to"], $dtformat);

		// Check if FirstShowDate valid, then use it.
		if ($stmpfirst>0 and $stmpfirst>=$stmpfrom and $stmpto>$stmpfirst)
		{
			$stmpfrom = $stmpfirst;
		}

		if ($stmpfrom >= $stmpto) return 0;
		$rStmp = $stmpto - $stmpfrom;
		return $rStmp;
	}

	// Calculate progress in 0.0<x<1.0 format of banner rotation
	public static function CalculateTimeProgress($arBanner)
	{
		$dt = CAdvBanner_all::__innerExtractBitrixDates($arBanner);

		$stmpnow = time();
		$dtformat = "DD.MM.YYYY HH:MI:SS";
		$stmpfirst = MakeTimeStamp($dt["first"], $dtformat);
		$stmpfrom = MakeTimeStamp($dt["from"], $dtformat);
		$stmpto = MakeTimeStamp($dt["to"], $dtformat);

		// Check if FirstShowDate valid, then use it.
		if ($stmpfirst>0 and $stmpfirst>=$stmpfrom and $stmpto>$stmpfirst)
		{
			$stmpfrom = $stmpfirst;
		}

		$stmpnow -= $stmpfrom;
		$diff = CAdvBanner_all::CalculateTimeDiff($arBanner);
		if ($stmpnow <= 0 or !$diff) return 0;

		return $stmpnow/$diff;
	}

	public static function CalculateRotationProgress($arBanner)
	{
		if (!isset($arBanner["MAX_SHOW_COUNT"]) or !isset($arBanner["SHOW_COUNT"]) or
			intval($arBanner["MAX_SHOW_COUNT"])==0) return 0;
		return intval($arBanner["SHOW_COUNT"])/intval($arBanner["MAX_SHOW_COUNT"]);
	}

	// Calculates speed
	//function CalculateActualRotationSpeed()
	//function GetAverageRotationSpeed()

	public static function PrepareHTML($text, $arBanner)
	{
		global $nRandom1, $nRandom2, $nRandom3, $nRandom4, $nRandom5;
		static $search = array("#RANDOM1#", "#RANDOM2#", "#RANDOM3#", "#RANDOM4#", "#RANDOM5#", "#BANNER_NAME#", "#BANNER_ID#", "#CONTRACT_ID#", "#TYPE_SID#");
		if (trim($text) <> '')
		{
			$text = str_replace($search, array($nRandom1, $nRandom2, $nRandom3, $nRandom4, $nRandom5, $arBanner["NAME"], $arBanner["ID"], $arBanner["CONTRACT_ID"], $arBanner["TYPE_SID"]), $text);
			if (mb_strpos($text, "#EVENT_GID#") !== false)
			{
				if (CModule::IncludeModule("statistic"))
				{
					$text = str_replace("#EVENT_GID#", CStatEvent::GetGID(), $text);
				}
			}
		}
		return $text;
	}

	public static function GetRedirectURL($url, $arBanner)
	{
		return $url;
	}

	public static function ReplaceURL($text, $arBanner)
	{
		return $text;
	}

	// возвращает HTML баннера по массиву
	public static function GetHTML($arBanner, $bNoIndex=false)
	{
		$strReturn = "";

		// обрабатываем изображение
		if(intval($arBanner["IMAGE_ID"]) > 0 && $arBanner["AD_TYPE"] <> "html" && $arBanner["AD_TYPE"] <> "template")
		{
			$arImage = CFile::GetFileArray($arBanner["IMAGE_ID"]);
			if ($arImage)
			{
				$file_type = GetFileType($arImage["FILE_NAME"]);
				$path = $arImage["SRC"];
				switch($file_type)
				{
					case "FLASH":
						$arParams = array();
						$url = $param = "";
						$alt = $a_title = $a_target = "";
						if (trim($arBanner["URL"]) <> '')
						{
							$param = CAdvBanner::PrepareHTML($arBanner["URL"], $arBanner);
							$param = CAdvBanner::GetRedirectURL($param, $arBanner);
							$url = $param;
							$arParams[] = "flash_link=".urlencode($param);
							if (trim($arBanner["URL_TARGET"]) <> '')
							{
								$arParams[] = "flash_target=".urlencode($arBanner["URL_TARGET"]);
								$a_target = ' target="'.htmlspecialcharsbx($arBanner["URL_TARGET"]).'" ';
							}
						}
						if (trim($arBanner["IMAGE_ALT"]) <> '')
						{
							$alt = CAdvBanner::PrepareHTML($arBanner["IMAGE_ALT"], $arBanner);
							$arParams[] = "flash_alt=".urlencode($alt);
							$a_title = " title=\"".htmlspecialcharsbx($alt)."\" ";
						}

						if (count($arParams)>0)
							$param = "?".implode("&amp;",$arParams);

						if ($arBanner["FLASH_TRANSPARENT"] == '')
							$arBanner["FLASH_TRANSPARENT"] = 'transparent';

						if ($arBanner["FLASH_JS"] != 'Y')
						{
							$strReturn = '<div style="width: '.$arImage["WIDTH"].'px; height: '.$arImage["HEIGHT"].'px; padding:0; margin:0">';
							if(trim($arBanner["URL"]) <> '' && $arBanner["NO_URL_IN_FLASH"] == "Y")
							{
								$strReturn .= ($bNoIndex? '<noindex>':'').'<div style="position:absolute; z-index:100;"><a href="'.$url.'"'.$a_target.$a_title.($bNoIndex? ' rel="nofollow"':'').'><img src="/bitrix/images/1.gif" width="'.$arImage["WIDTH"].'" height="'.$arImage["HEIGHT"].'" style="border:0;" alt="'.htmlspecialcharsEx($alt).'" /></a></div>'.($bNoIndex? '</noindex>':'');
							}
							$strReturn .=
'<OBJECT
	classid="clsid:D27CDB6E-AE6D-11CF-96B8-444553540000"
	codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,0,0"
	id="banner_'.$arBanner["ID"].'"
	WIDTH="'.$arImage["WIDTH"].'"
	HEIGHT="'.$arImage["HEIGHT"].'">
		<PARAM NAME="movie" VALUE="'.$path.$param.'" />
		<PARAM NAME="quality" VALUE="high" />
		<PARAM NAME="bgcolor" VALUE="#FFFFFF" />
		<PARAM NAME="wmode" VALUE="'.$arBanner["FLASH_TRANSPARENT"].'" />
		<EMBED
			src="'.$path.$param.'"
			quality="high"
			bgcolor="#FFFFFF"
			wmode="'.$arBanner["FLASH_TRANSPARENT"].'"
			WIDTH="'.$arImage["WIDTH"].'"
			HEIGHT="'.$arImage["HEIGHT"].'"
			NAME="banner"
			TYPE="application/x-shockwave-flash"
			PLUGINSPAGE="http://www.macromedia.com/go/getflashplayer">
		</EMBED>
</OBJECT></div>';
						}
						else
						{
							$strReturn = "";

							static $bScript = null;
							if($bScript === null)
							{
								$bScript = true;
								$strReturn .= '<script src="'.CUtil::GetAdditionalFileURL('/bitrix/js/advertising/flash.js').'"></script>';
							}

							$altImgPath = '';
							$arAltImage = CFile::GetFileArray($arBanner["FLASH_IMAGE"]);
							if ($arAltImage)
							{
								$altImgPath = $arAltImage["SRC"];
							}

							$test_id = 'id'.RandString(10);
							$strReturn .= '<div id="'.$test_id.'" style="width: '.$arImage["WIDTH"].'px; height: '.$arImage["HEIGHT"].'px; padding:0; margin:0;">';
							$altHref = '';
							if(trim($arBanner["URL"]) <> '')
							{
								if($arBanner["NO_URL_IN_FLASH"] == "Y")
									$strReturn .= ($bNoIndex? '<noindex>':'').'<div style="position:absolute; z-index:100;"><a href="'.$url.'"'.$a_target.$a_title.($bNoIndex? ' rel="nofollow"':'').'><img src="/bitrix/images/1.gif" width="'.$arImage["WIDTH"].'" height="'.$arImage["HEIGHT"].'" style="border:0;" alt="'.htmlspecialcharsEx($alt).'" /></a></div>'.($bNoIndex? '</noindex>':'');
								else
									$altHref = $url;
							}
							$strReturn .= '</div>';
							$strReturn .= '<script>bx_adv_includeFlash("'.$test_id.'", "'.
								CUtil::JSEscape($path.$param).'", "'.
								$arImage["WIDTH"].'", "'.$arImage["HEIGHT"].'", "'.
								$arBanner["FLASH_TRANSPARENT"].'", "'.
								CUtil::JSEscape($altImgPath).'", '.
								(intval($arBanner["FLASH_VER"]) <= 0? 'null':intval($arBanner["FLASH_VER"])).', "'.
								CUtil::JSEscape($altHref).'", "'.
								CUtil::JSEscape(htmlspecialcharsbx($arBanner["URL_TARGET"])).'", "'.
								CUtil::JSEscape(htmlspecialcharsbx($alt)).'");</script>';
						}
						break;

					default:
						$alt = CAdvBanner::PrepareHTML(trim($arBanner["IMAGE_ALT"]), $arBanner);
						$strImage = "<img alt=\"".htmlspecialcharsEx($alt)."\" title=\"".htmlspecialcharsEx($alt)."\" src=\"".$path."\" width=\"".$arImage["WIDTH"]."\" height=\"".$arImage["HEIGHT"]."\" style=\"border:0;\" />";
						if (trim($arBanner["URL"]) <> '')
						{
							$url = $arBanner["URL"];
							$url = CAdvBanner::PrepareHTML($url, $arBanner);
							$url = CAdvBanner::GetRedirectURL($url, $arBanner);
							$target = (trim($arBanner["URL_TARGET"]) <> '') ? " target=\"".$arBanner["URL_TARGET"]."\" " : "";
							$strReturn = ($bNoIndex? '<noindex>':'')."<a href=\"".$url."\"".$target.($bNoIndex? ' rel="nofollow"':'').">".$strImage."</a>".($bNoIndex? '</noindex>':'');
						}
						else
						{
							$strReturn .= $strImage;
						}
						break;
				}
			}
		}

		if($arBanner["CODE"] <> '')
		{
			$code = $arBanner["CODE"];
			if ($arBanner["CODE_TYPE"] == "text")
			{
				$code = TxtToHTML($code);
			}
			$code = CAdvBanner::PrepareHTML($code, $arBanner);
			$strReturn .= CAdvBanner::ReplaceURL($code, $arBanner);
		}

		return $strReturn;
	}

	public static function FixShowAll()
	{
		global $DB, $CACHE_ADVERTISING, $arrADV_VIEWED_BANNERS, $APPLICATION;
		$err_mess = (CAdvBanner::err_mess())."<br>Function: FixShowAll<br>Line: ";

		if (is_array($CACHE_ADVERTISING) &&
			array_key_exists("BANNERS_ALL", $CACHE_ADVERTISING) &&
			is_array($CACHE_ADVERTISING["BANNERS_ALL"]) &&
			!empty($CACHE_ADVERTISING["BANNERS_ALL"]) &&
			array_key_exists("BANNERS_CNT", $CACHE_ADVERTISING) &&
			is_array($CACHE_ADVERTISING["BANNERS_CNT"]))
		{

			if( array_key_exists( "ALL_DATE_SHOW_FIRST", $CACHE_ADVERTISING ) && is_array( $CACHE_ADVERTISING["ALL_DATE_SHOW_FIRST"] ) )
			{
				foreach( $CACHE_ADVERTISING["ALL_DATE_SHOW_FIRST"] as $key => $value )
				{
					$DB->Update( "b_adv_banner", Array( "DATE_SHOW_FIRST" => $value ), "WHERE ID='" . $key . "'", $err_mess . __LINE__ );
				}
			}

			$bEqualBanID = ($CACHE_ADVERTISING["BANNERS_ALL"] == $CACHE_ADVERTISING["BANNERS_CNT"]);

			//Update баннеров
			$arFields = Array(
				"SHOW_COUNT"		=> "SHOW_COUNT + 1",
				"DATE_LAST_SHOW"	=> $DB->GetNowFunction(),
			);
			if ($bEqualBanID)
				$arFields["VISITOR_COUNT"] = "VISITOR_COUNT + 1";

			$group_all = '';
			foreach($CACHE_ADVERTISING["BANNERS_ALL"] as $b)
				$group_all .= ($group_all <> ''? ',':'').intval($b);

			$DB->Update("b_adv_banner",$arFields,"WHERE ID IN(".$group_all.")",$err_mess.__LINE__);

			if (!$bEqualBanID && !empty($CACHE_ADVERTISING["BANNERS_CNT"]))
			{
				$arFields = Array("VISITOR_COUNT" => "VISITOR_COUNT + 1");
				$group_inc = "";
				foreach($CACHE_ADVERTISING["BANNERS_CNT"] as $BANNERS_CNT)
					$group_inc .= ($group_inc <> ""? ",":"").intval($BANNERS_CNT);
				$DB->Update("b_adv_banner",$arFields,"WHERE ID IN(".$group_inc.")",$err_mess.__LINE__);
			}

			//Баннеры по дням
			$strSql = "SELECT BANNER_ID FROM b_adv_banner_2_day WHERE BANNER_ID IN (".$group_all.") and DATE_STAT = ".$DB->GetNowDate();
			$res = $DB->Query($strSql, false, $err_mess.__LINE__);
			$arExist = $arInsert = Array();
			while ($ar = $res->Fetch())
				$arExist[] = $ar["BANNER_ID"];

			$arInsert = array_diff($CACHE_ADVERTISING["BANNERS_ALL"], $arExist);

			foreach ($arInsert as $BANNER_ID)
			{
				$strSql = "INSERT INTO b_adv_banner_2_day (DATE_STAT, BANNER_ID, SHOW_COUNT, VISITOR_COUNT) VALUES (".$DB->GetNowDate().", $BANNER_ID,1,1)";
				$DB->Query($strSql, true, $err_mess.__LINE__);
			}

			if (!empty($arExist))
			{
				$arExistInc = array_intersect($arExist,$CACHE_ADVERTISING["BANNERS_CNT"]);
				if (!empty($arExistInc))
				{
					$sExistInc = '';
					foreach($arExistInc as $b)
						$sExistInc .= ($sExistInc <> ''? ',':'').intval($b);

					$arFields = Array("SHOW_COUNT" => "SHOW_COUNT + 1", "VISITOR_COUNT" => "VISITOR_COUNT + 1");
					$DB->Update("b_adv_banner_2_day",$arFields,"WHERE BANNER_ID IN(".$sExistInc.") and DATE_STAT = ".$DB->GetNowDate(),$err_mess.__LINE__);
				}

				$arExistInc = array_diff($arExist, $arExistInc);

				if (!empty($arExistInc))
				{
					$sExistInc = '';
					foreach($arExistInc as $b)
						$sExistInc .= ($sExistInc <> ''? ',':'').intval($b);

					$arFields = Array("SHOW_COUNT" => "SHOW_COUNT + 1");
					$DB->Update("b_adv_banner_2_day",$arFields,"WHERE BANNER_ID IN(".$sExistInc.") and DATE_STAT = ".$DB->GetNowDate(),$err_mess.__LINE__);
				}
			}

			//Контракты
			$DONT_USE_CONTRACT = COption::GetOptionString("advertising", "DONT_USE_CONTRACT", "N");
			if ($DONT_USE_CONTRACT == "N" &&
				array_key_exists("CONTRACTS_ALL", $CACHE_ADVERTISING) &&
				is_array($CACHE_ADVERTISING["CONTRACTS_ALL"]) &&
				!empty($CACHE_ADVERTISING["CONTRACTS_ALL"]) &&
				array_key_exists("CONTRACTS_CNT", $CACHE_ADVERTISING) &&
				is_array($CACHE_ADVERTISING["CONTRACTS_CNT"]))
			{

				$arCount = array_count_values($CACHE_ADVERTISING["CONTRACTS_ALL"]);

				$arUpdate = Array();
				foreach($arCount as $CONTRACT_ID => $value)
					$arUpdate[$value][] = $CONTRACT_ID;

				foreach ($arUpdate as $count => $arContact)
				{
					$arFields = Array("SHOW_COUNT" => "SHOW_COUNT + ".$count);
					if ($arContact == (array_intersect($arContact, $CACHE_ADVERTISING["CONTRACTS_CNT"])))
					{
						$arFields["VISITOR_COUNT"] = "VISITOR_COUNT + 1";
						$CACHE_ADVERTISING["CONTRACTS_CNT"] = array_diff($CACHE_ADVERTISING["CONTRACTS_CNT"], $arContact);
					}
					$sContact = '';
					foreach($arContact as $c)
						$sContact .= ($sContact <> ''? ',':'').intval($c);

					$DB->Update("b_adv_contract",$arFields,"WHERE ID IN(".$sContact.")",$err_mess.__LINE__);
				}

				if (!empty($CACHE_ADVERTISING["CONTRACTS_CNT"]))
				{
					$sContrCnt = '';
					foreach($CACHE_ADVERTISING["CONTRACTS_CNT"] as $c)
						$sContrCnt .= ($sContrCnt <> ''? ',':'').intval($c);

					$arFields = Array("VISITOR_COUNT" => "VISITOR_COUNT + 1");
					$DB->Update("b_adv_banner",$arFields,"WHERE ID IN(".$sContrCnt.")",$err_mess.__LINE__);
				}
			}
			// сформируем значение cookie
			if(is_array($arrADV_VIEWED_BANNERS) && count($arrADV_VIEWED_BANNERS) > 0)
			{
				$cookie_value = "";
				$arrCookie = $arrADV_VIEWED_BANNERS;
				foreach($arrCookie as $key => $arr)
					if (intval($key)>0)
						$cookie_value .= intval($arr["CONTRACT_ID"])."_".$key."_".intval($arr["COUNTER"]). "_".trim($arr["EXPIRATION_DATE"]).",";

				// длина cookie не может превышать 4Кб
				$max_length = 4*1024;
				$j = 0;
				while (mb_strlen($cookie_value) > $max_length && $j<200)
				{
					$j++;
					$arrCookie_temp = $arrCookie;
					$arrCookie = array();
					$i=0;
					foreach($arrCookie_temp as $key => $arrValue)
					{
						$i++;
						if ($i>1)
							$arrCookie[$key] = $arrValue;
					}
					$cookie_value = "";
					foreach($arrCookie as $key => $arr)
						if (intval($key)>0)
							$cookie_value .= intval($arr["CONTRACT_ID"])."_".$key."_".intval($arr["COUNTER"]). "_".trim($arr["EXPIRATION_DATE"]).",";
				}
				$cookie_value = trim($cookie_value,",");
				$cookie_name = "BANNERS";
				$secure = (COption::GetOptionString("main", "use_secure_password_cookies", "N") == "Y" && CMain::IsHTTPS());
				$APPLICATION->set_cookie($cookie_name, $cookie_value, false, "/", false, $secure);
			}

			CAdvBanner::BeforeRestartBuffer();
		}
	}

	// фиксируем показ баннера
	public static function FixShow($arBanner)
	{
		global $DB, $CACHE_ADVERTISING;

		if (intval($_SESSION["SESS_SEARCHER_ID"])<=0 && $arBanner["FIX_SHOW"] == "Y" && COption::GetOptionString('advertising', 'DONT_FIX_BANNER_SHOWS') <> "Y")
		{
			$BANNER_ID = intval($arBanner["ID"]);
			$CONTRACT_ID = intval($arBanner["CONTRACT_ID"]);

			if ($BANNER_ID>0)
			{
				CAdvBanner::SetCookie($arBanner, $inc_banner_counter, $inc_contract_counter);

				if ($arBanner["DATE_SHOW_FIRST"] == '')
				{
					$CACHE_ADVERTISING["ALL_DATE_SHOW_FIRST"][$BANNER_ID] = $DB->CurrentTimeFunction();
				}

				if (
					isset($_SESSION["SESS_VIEWED_BANNERS"])
					&& is_array($_SESSION["SESS_VIEWED_BANNERS"])
					&& in_array($BANNER_ID, $_SESSION["SESS_VIEWED_BANNERS"])
				)
				{
					$inc_banner_counter="N";
				}

				if (
					isset($_SESSION["SESS_VIEWED_CONTRACTS"])
					&& is_array($_SESSION["SESS_VIEWED_CONTRACTS"])
					&& in_array($CONTRACT_ID, $_SESSION["SESS_VIEWED_CONTRACTS"])
				)
				{
					$inc_contract_counter="N";
				}

				$CACHE_ADVERTISING["BANNERS_ALL"][] = $BANNER_ID;

				if ($inc_banner_counter=="Y")
				{
					$CACHE_ADVERTISING["BANNERS_CNT"][] = $BANNER_ID;
					$_SESSION["SESS_VIEWED_BANNERS"][] = $BANNER_ID;
				}

				if ($CONTRACT_ID>0)
				{
					$CACHE_ADVERTISING["CONTRACTS_ALL"][] = $CONTRACT_ID;

					if ($inc_contract_counter=="Y")
					{
						$CACHE_ADVERTISING["CONTRACTS_CNT"][] = $CONTRACT_ID;
						$_SESSION["SESS_VIEWED_CONTRACTS"][] = $CONTRACT_ID;
					}
				}
			}
		}
	}

	public static function BeforeRestartBuffer()
	{
		global $CACHE_ADVERTISING, $arrADV_VIEWED_BANNERS;
		$CACHE_ADVERTISING = array(
			"BANNERS_ALL" => array(),
			"BANNERS_CNT" => array(),
			"CONTRACTS_ALL" => array(),
			"CONTRACTS_CNT" => array(),
		);
		$arrADV_VIEWED_BANNERS = false;

		//return true;
	}

	// устанавливаем cookie посетителю о просмотре баннера
	public static function SetCookie($arBanner, &$inc_banner_counter, &$inc_contract_counter)
	{
		global $arrADV_VIEWED_BANNERS, $APPLICATION;
		if (intval($arBanner["ID"])>0)
		{
			$inc_contract_counter = "N";
			$inc_banner_counter = "N";

			$days = COption::GetOptionString("advertising", "COOKIE_DAYS");
			$cookie_name = "BANNERS";
			$arrCookie = array();
			$arrContracts = array();

			// если мы уже получили на странице значение cookie то
			if (is_array($arrADV_VIEWED_BANNERS))
			{
				// берем массив arrCookie который уже определен на странице
				$arrCookie = $arrADV_VIEWED_BANNERS;

				// соберем массив контрактов
				foreach ($arrCookie as $arr)
				{
					$arrContracts[] = $arr["CONTRACT_ID"];
				}

				if (in_array($arBanner["ID"],array_keys($arrCookie)))
				{
					$arrCookie[$arBanner["ID"]]["COUNTER"] = $arrCookie[$arBanner["ID"]]["COUNTER"]+1;
					$arrCookie[$arBanner["ID"]]["EXPIRATION_DATE"] = date("dmY",time()+(intval($days)*86400));
				}
			}
			else // если мы первый раз обращаемся к значению хранимому в cookie
			{
				// то инициализируем массив arrCookie
				$arr = explode(",", $APPLICATION->get_cookie($cookie_name));
				if (is_array($arr) && count($arr)>0)
				{
					$now = time();

					foreach($arr as $str)
					{
						$ar = explode("_",$str);
						$contract_id = intval($ar[0]);
						$arrContracts[] = $contract_id;
						$banner_id = intval($ar[1]);
						$counter = intval($ar[2]);
						if ($arBanner["ID"]==$banner_id)
						{
							$counter++;
							$arrCookie[$arBanner["ID"]] = array(
								"CONTRACT_ID"		=> $arBanner["CONTRACT_ID"],
								"COUNTER"			=> $counter,
								"EXPIRATION_DATE"	=> date("dmY",time()+(intval($days)*86400))
								);
						}
						else
						{
							$strDate = trim($ar[3]);
							$month = intval(mb_substr($strDate, 2, 2));
							$day = intval(mb_substr($strDate, 0, 2));
							$year = intval(mb_substr($strDate, 4, 4));
							$stmp = false;

							if ($month && $day && $year)
							{
								$stmp = mktime(0, 0, 0, $month, $day, $year);
							}

							if (
								$stmp
								&& $stmp > $now
							)
							{
								$arrCookie[$banner_id] = array(
									"CONTRACT_ID" => $contract_id,
									"COUNTER" => $counter,
									"EXPIRATION_DATE" => ($stmp ? date("dmY", $stmp) : $strDate)
								);
							}
						}
					}
				}
			}

			// если данный посетитель по контракту еще не фиксировался то
			if (!in_array($arBanner["CONTRACT_ID"], $arrContracts))
				$inc_contract_counter = "Y";

			// если посетитель еще не фиксировался по текущему баннеру то
			if (!in_array($arBanner["ID"], array_keys($arrCookie)))
			{
				// взводим флаг о необходимости увеличить счетчик посетителей
				$inc_banner_counter="Y";

				// добавляем текущий баннер в массив arrCookie
				$arrCookie[$arBanner["ID"]] = array(
					"CONTRACT_ID"		=> $arBanner["CONTRACT_ID"],
					"COUNTER"			=> 1,
					"EXPIRATION_DATE"	=> date("dmY",time()+(intval($days)*86400))
				);
			}
			$arrADV_VIEWED_BANNERS = $arrCookie;
		}
	}

	// возвращает HTML произвольного баннера по типу
	public static function Show($TYPE_SID, $HTML_BEFORE="", $HTML_AFTER="")
	{
		global $APPLICATION, $USER;

		$debug = null;
		if(\Bitrix\Main\Application::getInstance()->getKernelSession()["SESS_SHOW_INCLUDE_TIME_EXEC"]=="Y" && $USER->IsAdmin())
		{
			$debug = new CDebugInfo();
			$debug->Start();
		}

		$arBanner = CAdvBanner::GetRandom($TYPE_SID);
		$strReturn = CAdvBanner::GetHTML($arBanner);
		if($strReturn <> '')
		{
			CAdvBanner::FixShow($arBanner);

			if(($arIcons = CAdvBanner::GetEditIcons($arBanner, $TYPE_SID)) !== false)
				$strReturn = $APPLICATION->IncludeString($strReturn, $arIcons);

			$strReturn = $HTML_BEFORE.$strReturn.$HTML_AFTER;

			if($debug)
				$strReturn .= $debug->Output();

			return $strReturn;
		}

		if($debug)
			echo $debug->Output();

		return false;
	}

	public static function GetEditIcons($arBanner, $TYPE_SID="", $arIcons = array())
	{
		global $USER, $APPLICATION;
		static $arContractTypes = false;
		static $arContracts = false;

		if($USER->IsAuthorized() && $APPLICATION->GetShowIncludeAreas())
		{
			if(CAdvContract::IsManager() || CAdvContract::IsAdmin())
			{
				if (is_array($arIcons) && count($arIcons) > 0)
				{
					if (!empty($arBanner) && isset($arBanner["ID"]))
					{
						$curIcon = array(
								"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
												array(
														'URL' => "/bitrix/admin/adv_banner_edit.php?bxpublic=Y&from_module=advertising&lang=".LANGUAGE_ID."&ID=".$arBanner["ID"]. "&CONTRACT_ID=".$arBanner["CONTRACT_ID"],
														'PARAMS' => array(
																'width' => 700,
																'height' => 400,
																'resize' => false,
														)
												)
										),
								"TEXT" => $arBanner["NAME"]
						);

						if (!is_array($arIcons[0]['MENU']))
						{
							$arIcons[0]['MENU'] = array(
								array(
									"URL" => $arIcons[0]['URL'],
									"TEXT" => $arIcons[0]['MESS']
								)
							);
							unset($arIcons[0]['URL']);
							unset($arIcons[0]['MESS']);
						}

						array_push($arIcons[0]['MENU'], $curIcon);
					}
				}
				else
				{
					$arIcons = array();
					if (!empty($arBanner) && isset($arBanner["ID"]))
					{
						$arIcons[] = array(
								"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
												array(
														'URL' => "/bitrix/admin/adv_banner_edit.php?bxpublic=Y&from_module=advertising&lang=".LANGUAGE_ID."&ID=".$arBanner["ID"]. "&CONTRACT_ID=".$arBanner["CONTRACT_ID"],
														'PARAMS' => array(
																'width' => 700,
																'height' => 400,
																'resize' => false,
														)
												)
										),
								"ICON" => "bx-context-toolbar-edit-icon",
								"TITLE" => GetMessage("AD_PUBLIC_ICON_EDIT_BANNER"),
								"MESS" => $arBanner["NAME"]
						);

						$TYPE_SID = $arBanner["TYPE_SID"];
					}
					if ($TYPE_SID <> '')
					{
						$arSubMenu = array();

						if($arContracts === false)
						{
							$arContracts = array();
							$arContractTypes = array();
							$contracts = CAdvContract::GetList("s_sort", "desc", array("SITE" => SITE_ID));
							while($arContract = $contracts->Fetch())
							{
								$arContracts[] = $arContract;
								$arContractTypes[$arContract["ID"]] = CAdvContract::GetTypeArray($arContract["ID"]);
							}
						}

						foreach($arContracts as $arContract)
						{
							if (array_key_exists("ALL", $arContractTypes[$arContract["ID"]]) || array_key_exists($TYPE_SID, $arContractTypes[$arContract["ID"]]))
							{
								$arSubMenu[] = array(
										"URL" => 'javascript:'.$APPLICATION->GetPopupLink(
														array(
																'URL' => "/bitrix/admin/adv_banner_edit.php?bxpublic=Y&from_module=advertising&lang=".LANGUAGE_ID."&TYPE_SID=".$TYPE_SID."&CONTRACT_ID=".$arContract["ID"],
																'PARAMS' => array(
																		'width' => 700,
																		'height' => 400,
																		'resize' => false,
																)
														)
												),
										"TEXT" => $arContract["NAME"]
								);
							}
						}

						$arIcon = array(
								"ICON" => "bx-context-toolbar-create-icon",
								"TITLE" => GetMessage("AD_PUBLIC_ICON_ADD_BANNER")
						);

						$nSubMenu = count($arSubMenu);
						if($nSubMenu == 1)
						{
							$arIcon["URL"] = $arSubMenu[0]["URL"];
							$arIcons[] = $arIcon;
						}
						elseif($nSubMenu > 1)
						{
							$arIcon["MENU"] = $arSubMenu;
							$arIcons[] = $arIcon;
						}
					}

					$arIcons[] = array(
							"URL" => "/bitrix/admin/adv_banner_list.php?lang=".LANGUAGE_ID."&find_contract_id[]=".$arBanner["CONTRACT_ID"]. "&find_type_sid[]=".$arBanner["TYPE_SID"]."&set_filter=Y",
							"SRC" => "/bitrix/themes/.default/icons/advertising/comp_view.gif",
							"TITLE" => GetMessage("AD_PUBLIC_ICON_BANNER_LIST"),
							"IN_PARAMS_MENU" => true
					);
				}

				return $arIcons;
			}
		}
		return false;
	}

	public static function CheckDynamicFilter($arFilter)
	{
		global $strError;
		$str = "";
		$find_date_1 = $arFilter["DATE_1"];
		$find_date_2 = $arFilter["DATE_2"];
		if (trim($find_date_1) <> '' || trim($find_date_2) <> '')
		{
			$date_1_ok = false;
			$date1_stm = MkDateTime(ConvertDateTime($find_date_1,"D.M.Y"),"d.m.Y");
			$date2_stm = MkDateTime(ConvertDateTime($find_date_2,"D.M.Y")." 23:59","d.m.Y H:i");
			if (!$date1_stm && trim($find_date_1) <> '')
				$str.= GetMessage("AD_ERROR_WRONG_PERIOD_FROM")."<br>";
			else $date_1_ok = true;
			if (!$date2_stm && trim($find_date_2) <> '')
				$str.= GetMessage("AD_ERROR_WRONG_PERIOD_TILL")."<br>";
			elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
				$str.= GetMessage("AD_ERROR_FROM_TILL_PERIOD")."<br>";
		}
		$strError .= $str;
		if ($str <> '') return false; else return true;
	}

	// возвращает массив описывающий динамику баннеров
	public static function GetDynamicList($arFilter, &$arrLegend, &$is_filtered)
	{
		$err_mess = (CAdvBanner::err_mess())."<br>Function: GetDynamicList<br>Line: ";
		global $DB;
		$arSqlSearch = Array();
		if (CAdvBanner::CheckDynamicFilter($arFilter))
		{
			if (is_array($arFilter))
			{
				$filter_keys = array_keys($arFilter);
				for ($i=0, $n = count($filter_keys); $i < $n; $i++)
				{
					$key = $filter_keys[$i];
					$val = $arFilter[$filter_keys[$i]];
					if(is_array($val))
					{
						if(count($val)<=0) continue;
					}
					else
					{
						if( ((string)$val == '') || ("$val"=="NOT_REF") ) continue;
					}
					$key = strtoupper($key);
					switch($key)
					{
						case "DATE_1":
							$arSqlSearch[] = "D.DATE_STAT>=".$DB->CharToDateFunction($val, "SHORT");
							break;
						case "DATE_2":
							$arSqlSearch[] = "D.DATE_STAT<=".$DB->CharToDateFunction($val." 23:59:59", "FULL");
							break;
					}
				}
			}
		}
		$arContract = is_array($arFilter["CONTRACT_ID"]) ? $arFilter["CONTRACT_ID"] : array();
		$arBanner = is_array($arFilter["BANNER_ID"]) ? $arFilter["BANNER_ID"] : array();
		$arGroup = is_array($arFilter["GROUP_SID"]) ? $arFilter["GROUP_SID"] : array();
		$contract_total = $arFilter["CONTRACT_SUMMA"]=="Y" ? "Y" : "N";
		$banner_total = $arFilter["BANNER_SUMMA"]=="Y" ? "Y" : "N";
		$group_total = $arFilter["GROUP_SUMMA"]=="Y" ? "Y" : "N";
		$arShow = is_array($arFilter["WHAT_SHOW"]) ? $arFilter["WHAT_SHOW"] : array();
		if (in_array("ctr",$arShow))
		{
			$arShow[] = "show";
			$arShow[] = "click";
		}
		$arShow = array_unique($arShow);
		$arrDays = array();
		$arrLegend = array();
		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		$strSql = CAdvBanner::GetDynamicList_SQL($strSqlSearch);

		$rsD = $DB->Query($strSql, false, $err_mess.__LINE__);
		while ($arD = $rsD->Fetch())
		{
			$arrDays[$arD["DATE_STAT"]]["DATE"] = $arD["DATE_STAT"];
			$arrDays[$arD["DATE_STAT"]]["D"] = $arD["DAY"];
			$arrDays[$arD["DATE_STAT"]]["M"] = $arD["MONTH"];
			$arrDays[$arD["DATE_STAT"]]["Y"] = $arD["YEAR"];

			foreach($arShow as $ctype)
			{
				if ($ctype=="CTR") continue;
				$ctype_u = mb_strtoupper($ctype);
				if (intval($arD[$ctype_u."_COUNT"])>0)
				{
					if (in_array($arD["CONTRACT_ID"], $arContract))
					{
						if ($contract_total=="N")
						{
							$arrLegend["3_CONTRACT_".$arD["CONTRACT_ID"]]["TYPE"] = "CONTRACT";
							$arrLegend["3_CONTRACT_".$arD["CONTRACT_ID"]]["ID"] = $arD["CONTRACT_ID"];
							$arrLegend["3_CONTRACT_".$arD["CONTRACT_ID"]]["NAME"] = $arD["CONTRACT_NAME"];
							$arrLegend["3_CONTRACT_".$arD["CONTRACT_ID"]]["COUNTER_TYPE"] = "DETAIL";
							$arrLegend["3_CONTRACT_".$arD["CONTRACT_ID"]][$ctype_u] += $arD[$ctype_u."_COUNT"];
							$arrDays[$arD["DATE_STAT"]]["CONTRACT"]["DETAIL_".$ctype_u][$arD["CONTRACT_ID"]] += $arD[$ctype_u."_COUNT"];
						}
						elseif ($contract_total=="Y")
						{
							$arrLegend["3_CONTRACT"]["TYPE"] = "CONTRACT";
							$arrLegend["3_CONTRACT"]["COUNTER_TYPE"] = "TOTAL";
							$arrLegend["3_CONTRACT"][$ctype_u] += $arD[$ctype_u."_COUNT"];
							$arrDays[$arD["DATE_STAT"]]["CONTRACT"]["TOTAL_".$ctype_u] += $arD[$ctype_u."_COUNT"];
						}
					}
					if (in_array($arD["BANNER_ID"], $arBanner))
					{
						if ($banner_total=="N")
						{
							$arrLegend["1_BANNER_".$arD["BANNER_ID"]]["TYPE"] = "BANNER";
							$arrLegend["1_BANNER_".$arD["BANNER_ID"]]["ID"] = $arD["BANNER_ID"];
							$arrLegend["1_BANNER_".$arD["BANNER_ID"]]["TYPE_SID"] = $arD["BANNER_TYPE_SID"];
							$arrLegend["1_BANNER_".$arD["BANNER_ID"]]["GROUP"] = $arD["GROUP_SID"];
							$arrLegend["1_BANNER_".$arD["BANNER_ID"]]["NAME"] = $arD["BANNER_NAME"];
							$arrLegend["1_BANNER_".$arD["BANNER_ID"]]["CONTRACT_ID"] = $arD["CONTRACT_ID"];
							$arrLegend["1_BANNER_".$arD["BANNER_ID"]]["COUNTER_TYPE"] = "DETAIL";
							$arrLegend["1_BANNER_".$arD["BANNER_ID"]][$ctype_u] += $arD[$ctype_u."_COUNT"];
							$arrDays[$arD["DATE_STAT"]]["BANNER"]["DETAIL_".$ctype_u][$arD["BANNER_ID"]] += $arD[$ctype_u."_COUNT"];
						}
						elseif ($banner_total=="Y")
						{
							$arrLegend["1_BANNER"]["TYPE"] = "BANNER";
							$arrLegend["1_BANNER"]["COUNTER_TYPE"] = "TOTAL";
							$arrLegend["1_BANNER"][$ctype_u] += $arD[$ctype_u."_COUNT"];
							$arrDays[$arD["DATE_STAT"]]["BANNER"]["TOTAL_".$ctype_u] += $arD[$ctype_u."_COUNT"];
						}
					}
					if (in_array($arD["GROUP_SID"], $arGroup))
					{
						if ($group_total=="N")
						{
							$arrLegend["2_GROUP_".$arD["GROUP_SID"]]["TYPE"] = "GROUP";
							$arrLegend["2_GROUP_".$arD["GROUP_SID"]]["ID"] = $arD["GROUP_SID"];
							$arrLegend["2_GROUP_".$arD["GROUP_SID"]]["COUNTER_TYPE"] = "DETAIL";
							$arrLegend["2_GROUP_".$arD["GROUP_SID"]][$ctype_u] += $arD[$ctype_u."_COUNT"];
							$arrDays[$arD["DATE_STAT"]]["GROUP"]["DETAIL_".$ctype_u][$arD["GROUP_SID"]] += $arD[$ctype_u."_COUNT"];
						}
						elseif ($group_total=="Y")
						{
							$arrLegend["2_GROUP"]["TYPE"] = "GROUP";
							$arrLegend["2_GROUP"]["COUNTER_TYPE"] = "TOTAL";
							$arrLegend["2_GROUP"][$ctype_u] += $arD[$ctype_u."_COUNT"];
							$arrDays[$arD["DATE_STAT"]]["GROUP"]["TOTAL_".$ctype_u] += $arD[$ctype_u."_COUNT"];
						}
					}
				}
			}
		}

		if (in_array("ctr", $arShow))
		{
			// рассчитаем CTR
			foreach ($arrDays as $keyD => $arD)
			{
				foreach ($arrLegend as $arrS)
				{
					if ($arrS["COUNTER_TYPE"]=="DETAIL")
					{
						$show_value = intval($arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_SHOW"][$arrS["ID"]]);
						$click_value = intval($arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_CLICK"][$arrS["ID"]]);
						if ($show_value<=0) $ctr_value=0;
						else $ctr_value = round(($click_value*100)/$show_value, 2);
						$arD[$arrS["TYPE"]]["DETAIL_CTR"][$arrS["ID"]] = $ctr_value;
						$arrDays[$keyD] = $arD;
					}
					else
					{
						$show_value = intval($arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_SHOW"]);
						$click_value = intval($arD[$arrS["TYPE"]][$arrS["COUNTER_TYPE"]."_CLICK"]);
						if ($show_value<=0) $ctr_value=0;
						else $ctr_value = round(($click_value*100)/$show_value, 2);
						$arD[$arrS["TYPE"]]["TOTAL_CTR"] = $ctr_value;
						$arrDays[$keyD] = $arD;
					}
				}
			}
		}

		// Определим цвета и суммарный CTR
		$s = 0;
		if (in_array("ctr", $arShow)) $s++;
		if ($arFilter["WHAT_SHOW"]!=array("ctr") && in_array("show", $arShow)) $s++;
		if ($arFilter["WHAT_SHOW"]!=array("ctr") && in_array("click", $arShow)) $s++;
		if ($arFilter["WHAT_SHOW"]!=array("ctr") && in_array("visitor", $arShow)) $s++;
		$total = sizeof($arrLegend)*$s;
		$color = "";
		foreach ($arrLegend as $key => $arr)
		{
			if (in_array("ctr", $arShow))
			{
				$color = GetNextRGB($color, $total);
				$arr["COLOR_CTR"] = $color;
				if ($arr["SHOW"]<=0) $ctr = 0;
				else $ctr = round(($arr["CLICK"]*100)/$arr["SHOW"], 2);
				$arr["CTR"] = $ctr;
			}
			if ($arFilter["WHAT_SHOW"]!=array("ctr"))
			{
				if (in_array("show", $arShow))
				{
					$color = GetNextRGB($color, $total);
					$arr["COLOR_SHOW"] = $color;
				}
				if (in_array("click", $arShow))
				{
					$color = GetNextRGB($color, $total);
					$arr["COLOR_CLICK"] = $color;
				}
				if (in_array("visitor", $arShow))
				{
					$color = GetNextRGB($color, $total);
					$arr["COLOR_VISITOR"] = $color;
				}
			}
			$arr["COLOR"] = $color;
			$arrLegend[$key] = $arr;
		}
		krsort($arrLegend);

		$is_filtered = (IsFiltered($strSqlSearch));
		reset($arrDays);
		reset($arrLegend);
		return $arrDays;
	}

	public static function GetStatList($by, $order, $arFilter)
	{
		$err_mess = (CAdvBanner::err_mess())."<br>Function: GetStatList<br>Line: ";
		global $DB;
		$arSqlSearch = Array();
		if (CAdvBanner::CheckDynamicFilter($arFilter))
		{
			if (is_array($arFilter))
			{
				$filter_keys = array_keys($arFilter);
				for ($i=0, $n = count($filter_keys); $i < $n; $i++)
				{
					$key = $filter_keys[$i];
					$val = $arFilter[$filter_keys[$i]];
					if(is_array($val))
					{
						if(count($val)<=0) continue;
					}
					else
					{
						if( ((string)$val == '') || ("$val"=="NOT_REF") ) continue;
					}
					$key = strtoupper($key);
					switch($key)
					{
						case "DATE_1":
							$arSqlSearch[] = "D.DATE_STAT>=".$DB->CharToDateFunction($val, "SHORT");
							break;
						case "DATE_2":
							$arSqlSearch[] = "D.DATE_STAT<=".$DB->CharToDateFunction($val." 23:59:59", "FULL");
							break;
					}
				}

				\Bitrix\Main\Type\Collection::normalizeArrayValuesByInt($arFilter['BANNER_ID']);

				if(!empty($arFilter['BANNER_ID']))
				{
					$where = new CSQLWhere();
					$arSqlSearch[] = $where->_NumberIN("D.BANNER_ID", $arFilter['BANNER_ID']);
				}
			}
		}

		$strSqlSearch = GetFilterSqlSearch($arSqlSearch);
		if ($by == "s_date")
		{
			$strSqlOrder = " ORDER BY D.DATE_STAT ";
		}
		elseif ($by == "s_visitors")
		{
			$strSqlOrder = " ORDER BY VISITOR_COUNT ";
		}
		elseif ($by == "s_clicks")
		{
			$strSqlOrder = " ORDER BY CLICK_COUNT ";
		}
		elseif ($by == "s_ctr")
		{
			$strSqlOrder = " ORDER BY CTR";
		}
		elseif ($by == "s_show")
		{
			$strSqlOrder = " ORDER BY SHOW_COUNT ";
		}
		elseif ($by == "s_id")
		{
			$strSqlOrder = " ORDER BY D.BANNER_ID";
		}
		else
		{
			$strSqlOrder = " ORDER BY DATE_STAT";
			$by = "s_date";
		}

		if ($order!="asc")
		{
			$strSqlOrder .= " desc ";
		}


		if ($by != "s_date")
		{
			$strSqlOrder .= ', DATE_STAT ASC';
		}

		if ($arFilter['BANNER_SUMMA'] == 'Y')
		{
			$strSql = "
				SELECT
					".$DB->DateToCharFunction("D.DATE_STAT","SHORT")."		DATE_STAT,
					SUM(D.SHOW_COUNT)										SHOW_COUNT,
					SUM(D.CLICK_COUNT)										CLICK_COUNT,
					SUM(D.VISITOR_COUNT)									VISITOR_COUNT,
					" . CAdvBanner::getCTRSQL() . "
				FROM
					b_adv_banner_2_day D
				INNER JOIN b_adv_banner B ON (D.BANNER_ID = B.ID)
				INNER JOIN b_adv_contract C ON (B.CONTRACT_ID = C.ID)
				WHERE
				$strSqlSearch
				GROUP by DATE_STAT
				$strSqlOrder
				";
		}
		else
		{
			$strSql = "
				SELECT
					".$DB->DateToCharFunction("D.DATE_STAT","SHORT")."		DATE_STAT,
					SUM(D.SHOW_COUNT)										SHOW_COUNT,
					SUM(D.CLICK_COUNT)										CLICK_COUNT,
					SUM(D.VISITOR_COUNT)									VISITOR_COUNT,
					D.BANNER_ID,
					B.NAME													BANNER_NAME,
					" . CAdvBanner::getCTRSQL() . "
				FROM
					b_adv_banner_2_day D
				INNER JOIN b_adv_banner B ON (D.BANNER_ID = B.ID)
				INNER JOIN b_adv_contract C ON (B.CONTRACT_ID = C.ID)
				WHERE
				$strSqlSearch
				GROUP by D.DATE_STAT, D.BANNER_ID, B.NAME
				$strSqlOrder
				";
		}

		return $DB->Query($strSql, false, $err_mess.__LINE__);
	}
}

/*****************************************************************
					Класс "Тип баннера"
*****************************************************************/

class CAdvType_all
{
	public static function err_mess()
	{
		$module_id = "advertising";
		return "<br>Module: ".$module_id."<br>Class: CAdvType_all<br>File: ".__FILE__;
	}

	public static function CheckFilter($arFilter)
	{
		global $strError;
		$str = "";
		$find_date_modify_1 = $arFilter["DATE_MODIFY_1"];
		$find_date_modify_2 = $arFilter["DATE_MODIFY_2"];
		if (trim($find_date_modify_1) <> '' || trim($find_date_modify_2) <> '')
		{
			$date_1_ok = false;
			$date1_stm = MkDateTime(ConvertDateTime($find_date_modify_1,"D.M.Y"),"d.m.Y");
			$date2_stm = MkDateTime(ConvertDateTime($find_date_modify_2,"D.M.Y")." 23:59","d.m.Y H:i");
			if (!$date1_stm && trim($find_date_modify_1) <> '')
				$str.= GetMessage("AD_ERROR_WRONG_DATE_MODIFY_FROM")."<br>";
			else $date_1_ok = true;
			if (!$date2_stm && trim($find_date_modify_2) <> '')
				$str.= GetMessage("AD_ERROR_WRONG_DATE_MODIFY_TILL")."<br>";
			elseif ($date_1_ok && $date2_stm <= $date1_stm && $date2_stm <> '')
				$str.= GetMessage("AD_ERROR_FROM_TILL_DATE_MODIFY")."<br>";
		}
		$strError .= $str;
		if ($str <> '') return false; else return true;
	}

	// получаем следующий порядок сортировки
	public static function GetNextSort()
	{
		global $DB;
		$err_mess = (CAdvType_all::err_mess())."<br>Function: GetNextSort<br>Line: ";
		$strSql = "SELECT max(SORT) MAX_SORT FROM b_adv_type";
		$z = $DB->Query($strSql, false, $err_mess.__LINE__);
		$zr = $z->Fetch();
		return intval($zr["MAX_SORT"])+100;
	}

	public static function CheckFields($arFields, $OLD_SID, $CHECK_RIGHTS)
	{
		global $strError;
		$str = "";
		$SID = $arFields["SID"];
		if ($CHECK_RIGHTS=="Y")
		{
			$isAdmin = CAdvContract::IsAdmin();
		}
		else
		{
			$isAdmin = true;
		}
		if ($isAdmin)
		{
			$arrKeys = array_keys($arFields);
			if (in_array("SID", $arrKeys))
			{
				if(trim($SID) == '')
				{
					$str .= GetMessage("AD_ERROR_FORGOT_SID")."<br>";
				}
				else
				{
					if (preg_match("/[^A-Za-z_0-9]/", $SID))
					{
						$str .=  GetMessage("AD_ERROR_INCORRECT_SID")."<br>";
					}
					else
					{
						if ($OLD_SID!=$SID)
						{
							$arFilter = array("SID" => $SID." & ~".$OLD_SID, "SID_EXACT_MATCH" => "Y");
							$rs = CAdvType::GetList('', '', $arFilter);
							$rows = 0;
							if (is_object($rs))
							{
								$rs->NavStart();
								$rows = intval($rs->SelectedRowsCount());
							}

							if ($rows>=1 || $SID == "ALL" || $OLD_SID == "ALL")
							{
								$str .= str_replace("#SID#", ($OLD_SID == "ALL" ? $OLD_SID : $SID), GetMessage("AD_ERROR_SID_EXISTS"));
							}
						}
					}
				}
			}
		}
		else
		{
			if ($OLD_SID <> '') $str .= GetMessage("AD_ERROR_NOT_ENOUGH_PERMISSIONS_TYPE")."<br>";
			else $str .= GetMessage("AD_ERROR_NOT_ENOUGH_PERMISSIONS_FOR_CREATE_TYPE")."<br>";
		}

		$strError .= $str;
		if ($str <> '') return false; else return true;
	}

	// добавляем новый тип или модифицируем существующий
	public static function Set($arFields, $OLD_SID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAdvType_all::err_mess())."<br>Function: Set<br>Line: ";
		global $DB, $USER;
		$SID = false;
		$OLD_SID = trim($OLD_SID);
		if (CAdvType::CheckFields($arFields, $OLD_SID, $CHECK_RIGHTS))
		{
			$arFields_i = array();
			$arrKeys = array_keys($arFields);
			if (in_array("SID", $arrKeys))
				$arFields_i["SID"] = "'".$DB->ForSql($arFields["SID"], 255)."'";
			if (in_array("ACTIVE", $arrKeys) && ($arFields["ACTIVE"]=="Y" || $arFields["ACTIVE"]=="N"))
				$arFields_i["ACTIVE"] = "'".$arFields["ACTIVE"]."'";
			if (in_array("SORT", $arrKeys))
				$arFields_i["SORT"] = "'".intval($arFields["SORT"])."'";
			if (in_array("NAME", $arrKeys))
				$arFields_i["NAME"] = "'".$DB->ForSql($arFields["NAME"], 255)."'";
			if (in_array("DESCRIPTION", $arrKeys))
				$arFields_i["DESCRIPTION"] = "'".$DB->ForSql($arFields["DESCRIPTION"], 2000)."'";
			if (count($arFields_i)>0)
			{
				if ($OLD_SID <> '')
				{
					if (in_array("DATE_MODIFY", $arrKeys) && CheckDateTime($arFields["DATE_MODIFY"]))
						$arFields_i["DATE_MODIFY"] = $DB->CharToDateFunction($arFields["DATE_MODIFY"]);
					else
						$arFields_i["DATE_MODIFY"] = $DB->GetNowFunction();

					if (in_array("MODIFIED_BY", $arrKeys))
						$arFields_i["MODIFIED_BY"] = intval($arFields["MODIFIED_BY"]);
					else
						$arFields_i["MODIFIED_BY"] = $USER->GetID();

					$str = "";
					foreach ($arFields_i as $field => $value)
					{
						if ($value == '') $str .= "$field = '', "; else $str .= "$field = $value, ";
					}
					$str = trim($str, ", ");
					$strSql = "UPDATE b_adv_type SET ".$str." WHERE SID='".$DB->ForSql($OLD_SID, 255)."'";
					$DB->Query($strSql, false, $err_mess.__LINE__);

					if (in_array("SID", $arrKeys))
					{
						$SID = $arFields["SID"];
						// если SID изменился то
						if ($arFields["SID"]!=$OLD_SID)
						{
							// обновим тип у баннеров
							$arF = array("TYPE_SID" => "'".$DB->ForSql($arFields["SID"],255)."'");
							$DB->Update("b_adv_banner",$arF,"WHERE TYPE_SID='".$DB->ForSql($OLD_SID, 255)."'",$err_mess.__LINE__);

							// обновим тип у баннеров
							$arF = array("TYPE_SID" => "'".$DB->ForSql($arFields["SID"],255)."'");
							$DB->Update("b_adv_contract_2_type",$arF,"WHERE TYPE_SID='".$DB->ForSql($OLD_SID, 255)."'",$err_mess.__LINE__);
						}
					}
					else $SID = $OLD_SID;
				}
				elseif ($arFields_i["SID"] <> '')
				{
					if (in_array("DATE_CREATE", $arrKeys) && CheckDateTime($arFields["DATE_CREATE"]))
						$arFields_i["DATE_CREATE"] = $DB->CharToDateFunction($arFields["DATE_CREATE"]);
					else
						$arFields_i["DATE_CREATE"] = $DB->GetNowFunction();

					if (in_array("CREATED_BY", $arrKeys))
						$arFields_i["CREATED_BY"] = intval($arFields["CREATED_BY"]);
					else
						$arFields_i["CREATED_BY"] = $USER->GetID();

					if (in_array("DATE_MODIFY", $arrKeys) && CheckDateTime($arFields["DATE_MODIFY"]))
						$arFields_i["DATE_MODIFY"] = $DB->CharToDateFunction($arFields["DATE_MODIFY"]);
					else
						$arFields_i["DATE_MODIFY"] = $DB->GetNowFunction();

					if (in_array("MODIFIED_BY", $arrKeys))
						$arFields_i["MODIFIED_BY"] = intval($arFields["MODIFIED_BY"]);
					else
						$arFields_i["MODIFIED_BY"] = $USER->GetID();

					$str1 = $str2 = "";
					foreach ($arFields_i as $field => $value)
					{
						$str1 .= $field.", ";
						if ($value == '') $str2 .= "'', ";	else $str2 .= "$value, ";
					}
					$str1 = trim($str1, ", ");
					$str2 = trim($str2, ", ");
					$strSql = "INSERT INTO b_adv_type (".$str1.") VALUES (".$str2.")";
					$DB->Query($strSql, false, $err_mess.__LINE__);
					$SID = $arFields["SID"];
				}
			}
		}
		else
		{
			$SID = $arFields["SID"];
		}

		return $SID;
	}

	// получаем тип баннера по ID
	public static function GetByID($TYPE_SID)
	{
		if (trim($TYPE_SID) == '') return false;
		$arFilter = array(
			"SID"				=> $TYPE_SID,
			"SID_EXACT_MATCH"	=> "Y"
			);
		$rs = CAdvType::GetList('', '', $arFilter);
		return $rs;
	}

	// удаляем тип баннера
	public static function Delete($TYPE_SID, $CHECK_RIGHTS="Y")
	{
		$err_mess = (CAdvType_all::err_mess())."<br>Function: Delete<br>Line: ";
		global $DB, $strError;
		if ($TYPE_SID == '') return false;
		if ($CHECK_RIGHTS=="Y")
		{
			$isAdmin = CAdvContract::IsAdmin();
		}
		else
		{
			$isAdmin = true;
		}
		if ($isAdmin)
		{
			$strSql = "SELECT ID FROM b_adv_banner WHERE TYPE_SID = '".$DB->ForSql($TYPE_SID,255)."'";
			$rs = $DB->Query($strSql, false, $err_mess.__LINE__);
			while ($ar = $rs->Fetch()) CAdvBanner::Delete($ar["ID"], "N");

			CAdvType::DeleteContractLink($TYPE_SID);

			$strSql = "DELETE FROM b_adv_type WHERE SID = '".$DB->ForSql($TYPE_SID,255)."'";
			$DB->Query($strSql, false, $err_mess.__LINE__);
			return true;
		}
		else
			$strError .= GetMessage("AD_ERROR_NOT_ENOUGH_PERMISSIONS_TYPE")."<br>";
		return false;

	}

	// удаляем связь типа с контрактом
	public static function DeleteContractLink($TYPE_SID)
	{
		$err_mess = (CAdvType_all::err_mess())."<br>Function: DeleteContractLink<br>Line: ";
		global $DB;
		if ($TYPE_SID == '')
		{
			return false;
		}

		$strSql = "DELETE FROM b_adv_contract_2_type WHERE TYPE_SID = '".$DB->ForSql($TYPE_SID,255)."'";
		$DB->Query($strSql, false, $err_mess.__LINE__);
		return true;
	}

	// получаем список типов баннеров
	public static function GetList($by = "s_sort", $order = "asc", $arFilter = [], $is_filtered = null, $CHECK_RIGHTS = "Y")
	{
		$err_mess = (CAdvType_all::err_mess())."<br>Function: GetList<br>Line: ";
		global $DB;
		$arSqlSearch = Array();
		if ($CHECK_RIGHTS=="Y")
		{
			$isAdmin = CAdvContract::IsAdmin();
			$isDemo = CAdvContract::IsDemo();
			$isManager = CAdvContract::IsManager();
			$isAdvertiser = CAdvContract::IsAdvertiser();
		}
		else
		{
			$isAdmin = true;
			$isDemo = true;
			$isManager = true;
			$isAdvertiser = true;
		}
		if ($isAdmin || $isDemo || $isManager || $isAdvertiser)
		{
			if (CAdvType::CheckFilter($arFilter))
			{
				if (is_array($arFilter))
				{
					$filter_keys = array_keys($arFilter);
					for ($i=0, $n = count($filter_keys); $i < $n; $i++)
					{
						$key = $filter_keys[$i];
						$val = $arFilter[$filter_keys[$i]];
						if(is_array($val))
						{
							if(count($val) <= 0)
								continue;
						}
						else
						{
							if( ((string)$val == '') || ($val === "NOT_REF") )
								continue;
						}
						$match_value_set = (in_array($key."_EXACT_MATCH", $filter_keys)) ? true : false;
						$key = strtoupper($key);
						switch($key)
						{
							case "SID":
								$match = ($arFilter[$key."_EXACT_MATCH"]=="N" && $match_value_set) ? "Y" : "N";
								$arSqlSearch[] = GetFilterQuery("T.SID", $val, $match);
								break;
							case "DATE_MODIFY_1":
								$arSqlSearch[] = "T.DATE_MODIFY>=".$DB->CharToDateFunction($val, "SHORT");
								break;
							case "DATE_MODIFY_2":
								$arSqlSearch[] = "T.DATE_MODIFY<=".$DB->CharToDateFunction($val." 23:59:59", "FULL");
								break;
							case "ACTIVE":
								$arSqlSearch[] = ($val=="Y") ? "T.ACTIVE='Y'" : "T.ACTIVE='N'";
								break;
							case "NAME":
							case "DESCRIPTION":
								$match = ($arFilter[$key."_EXACT_MATCH"]=="Y" && $match_value_set) ? "N" : "Y";
								$arSqlSearch[] = GetFilterQuery("T.".$key, $val, $match);
								break;
						}
					}
				}
			}
			if ($by == "s_sid")				$strSqlOrder = " ORDER BY T.SID ";
			elseif ($by == "s_date_modify")	$strSqlOrder = " ORDER BY T.DATE_MODIFY ";
			elseif ($by == "s_modified_by")	$strSqlOrder = " ORDER BY T.MODIFIED_BY ";
			elseif ($by == "s_date_create")	$strSqlOrder = " ORDER BY T.DATE_CREATE ";
			elseif ($by == "s_created_by")	$strSqlOrder = " ORDER BY T.CREATED_BY ";
			elseif ($by == "s_active")		$strSqlOrder = " ORDER BY T.ACTIVE ";
			elseif ($by == "s_name")		$strSqlOrder = " ORDER BY T.NAME ";
			elseif ($by == "s_banners")		$strSqlOrder = " ORDER BY BANNER_COUNT ";
			elseif ($by == "s_description")	$strSqlOrder = " ORDER BY T.DESCRIPTION ";
			else
			{
				$strSqlOrder = " ORDER BY T.SORT ";
			}

			if ($order == "desc")
			{
				$strSqlOrder .= " desc ";
			}
			else
			{
				$strSqlOrder .= " asc ";
			}

			$strSqlSearch = GetFilterSqlSearch($arSqlSearch);

			$strContracts = "";
			if (!$isAdmin && !$isDemo && !$isManager)
			{
				$strContracts = "0";
				$arPermissions = CAdvContract::GetUserPermissions();
				foreach ($arPermissions as $contract_id => $arContractPerms)
				{
					if (is_array($arContractPerms) && !empty($arContractPerms))
						$strContracts .= ",".$contract_id;
				}
			}

			$strSql = "
				SELECT
					T.SID,
					T.ACTIVE,
					T.SORT,
					T.NAME,
					T.DESCRIPTION,
					".$DB->DateToCharFunction("T.DATE_CREATE")."	DATE_CREATE,
					".$DB->DateToCharFunction("T.DATE_MODIFY")."	DATE_MODIFY,
					T.CREATED_BY,
					T.MODIFIED_BY,
					count(distinct B.ID)							BANNER_COUNT
				FROM
					b_adv_type T
					LEFT JOIN b_adv_banner B ON (B.TYPE_SID=T.SID".($strContracts == "" ? "" :" AND B.CONTRACT_ID IN (".$strContracts.")").")
				WHERE
				$strSqlSearch ".
				($strContracts == "" ? "" :
					"and exists(select 'x' from b_adv_contract_2_type CT where (CT.TYPE_SID=T.SID OR CT.TYPE_SID='ALL') AND CT.CONTRACT_ID IN (".$strContracts.")) "
				).
				" and T.SID<>'ALL'
				GROUP BY
					T.SID, T.ACTIVE, T.SORT, T.NAME, T.DESCRIPTION,	T.DATE_CREATE, T.DATE_MODIFY, T.CREATED_BY, T.MODIFIED_BY
				$strSqlOrder
				";

			$res = $DB->Query($strSql, false, $err_mess.__LINE__);

			return $res;
		}
		return null;
	}
}

/********************************************
	совместимость со старой версией модуля
*********************************************/

class CAdvertising
{
	public static function GetAdv($TYPE_SID)
	{
		return CAdvBanner::Show($TYPE_SID);
	}

	public static function ClickAdv($BANNER_ID)
	{
		return CAdvBanner::Click($BANNER_ID);
	}
}
