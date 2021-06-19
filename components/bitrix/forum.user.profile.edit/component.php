<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
if (!CModule::IncludeModule("forum")):
	ShowError(GetMessage("F_NO_MODULE"));
	return 0;
elseif (!$USER->IsAuthorized()):
	$APPLICATION->AuthForm(GetMessage("FP_NO_AUTHORIZE"));
	return 0;
elseif (!$USER->CanDoOperation('edit_own_profile')):
	ShowError(GetMessage("F_ACCESS_DENIED"));
	return 0;
endif;
/********************************************************************
				Input params
********************************************************************/
/***************** BASE ********************************************/
$arParams["UID"] = intval(!empty($arParams["UID"]) ? $arParams["UID"] : (!empty($_REQUEST["UID"]) ? $_REQUEST["UID"] : $_REQUEST["ID"]));
$arParams["UID"] = (!$USER->IsAdmin() ? intval($USER->GetId()) : $arParams["UID"]);
/***************** URL *********************************************/
$URL_NAME_DEFAULT = array(
	"profile_view" => "PAGE_NAME=profile_view&UID=#UID#");
foreach ($URL_NAME_DEFAULT as $URL => $URL_VALUE)
{
	if (trim($arParams["URL_TEMPLATES_".mb_strtoupper($URL)]) == '')
		$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = $APPLICATION->GetCurPage()."?".$URL_VALUE;
	$arParams["~URL_TEMPLATES_".mb_strtoupper($URL)] = $arParams["URL_TEMPLATES_".mb_strtoupper($URL)];
	$arParams["URL_TEMPLATES_".mb_strtoupper($URL)] = htmlspecialcharsbx($arParams["~URL_TEMPLATES_".mb_strtoupper($URL)]);
}
/***************** ADDITIONAL **************************************/
$arParams["USER_PROPERTY"] = (is_array($arParams["USER_PROPERTY"]) ? $arParams["USER_PROPERTY"] : array());
$arParams["NAME_TEMPLATE"] = (!empty($arParams["NAME_TEMPLATE"]) ? $arParams["NAME_TEMPLATE"] : '#NAME# #LAST_NAME#');
/***************** STANDART ****************************************/
$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
$arParams["SET_NAVIGATION"] = ($arParams["SET_NAVIGATION"] == "N" ? "N" : "Y");
// $arParams["DISPLAY_PANEL"] = ($arParams["DISPLAY_PANEL"] == "Y" ? "Y" : "N");
if ($arParams["CACHE_TYPE"] == "Y" || ($arParams["CACHE_TYPE"] == "A" && COption::GetOptionString("main", "component_cache_on", "Y") == "Y"))
	$arParams["CACHE_TIME"] = intval($arParams["CACHE_TIME"]);
else
	$arParams["CACHE_TIME"] = 0;
/********************************************************************
				/Input params
********************************************************************/

$arResult["USER"] = array();
if ($arParams["UID"] > 0)
{
	$db_user = CUser::GetByID($arParams["UID"]);
	if ($db_user && ($ar_user = $db_user->Fetch()))
	{
		foreach ($ar_user as $key => $val)
		{
			if (is_string($val))
			{
				${"str_".$key} = htmlspecialcharsbx($val);
				$arResult["str_".$key] = htmlspecialcharsbx($val);
			}
		}
		$arResult["USER"] = $ar_user;

		$ar_forum_user = CForumUser::GetByUSER_ID($arParams["UID"]);
		if ($ar_forum_user)
		{
			foreach ($ar_forum_user as $key => $val)
			{
				${"str_FORUM_".$key} = htmlspecialcharsbx($val);
				$arResult["str_FORUM_".$key] = htmlspecialcharsbx($val);
			}
			$arResult["FORUM_USER"] = $ar_forum_user;
		}
	}
}

if ($arParams["UID"] <= 0):
	ShowError(GetMessage("F_USER_NOT_FOUND"));
	return 0;
elseif (empty($arResult["USER"])):
	ShowError(GetMessage("FP_ERR_INTERN"));
	return 0;
endif;

/********************************************************************
				Default values
********************************************************************/
$arResult["~profile_view"] = CComponentEngine::MakePathFromTemplate($arParams["~URL_TEMPLATES_PROFILE_VIEW"],
	array("UID" => $arParams["UID"]));
$arResult["profile_view"] = htmlspecialcharsbx($arResult["~profile_view"]);
$arResult["IsAuthorized"] = $USER->IsAuthorized() ? "Y" : "N";
$arResult["IsAdmin"] = $USER->IsAdmin() ? "Y" : "N";
$bVarsFromForm = false;
$arError = array();
$arResult["ERROR_MESSAGE"] = "";
$arResult["OK_MESSAGE"] = "";
$arResult["UID"] = $arParams["UID"];
/********************************************************************
				/Default values
********************************************************************/

/********************************************************************
				Action
********************************************************************/
if ($_SERVER["REQUEST_METHOD"]=="POST" && $_POST["ACTION"]=="EDIT")
{
	if (!empty($_REQUEST["cancel"]))
	{
		LocalRedirect($arResult["~profile_view"]);
		return 0;
	}
	elseif (!check_bitrix_sessid())
	{
		$arError[] = array(
			"code" => "session time is up",
			"title" => GetMessage("F_ERR_SESSION_TIME_IS_UP"));
	}
	else
	{
		$APPLICATION->ResetException();
		// Update Main info about user
		$arPERSONAL_PHOTO = $_FILES["PERSONAL_PHOTO"];
		$arPERSONAL_PHOTO["old_file"] = $ar_user["PERSONAL_PHOTO"];
		$arPERSONAL_PHOTO["del"] = $_POST["PERSONAL_PHOTO_del"];

		$arFields = Array(
			"NAME"					=> $_POST["NAME"],
			"LAST_NAME"				=> $_POST["LAST_NAME"],
			"EMAIL"					=> $_POST["EMAIL"],
			"LOGIN"					=> $_POST["LOGIN"],
			"PERSONAL_PROFESSION"=> $_POST["PERSONAL_PROFESSION"],
			"PERSONAL_WWW"			=> $_POST["PERSONAL_WWW"],
			"PERSONAL_ICQ"			=> $_POST["PERSONAL_ICQ"],
			"PERSONAL_GENDER"		=> $_POST["PERSONAL_GENDER"],
			"PERSONAL_BIRTHDAY"	=> $_POST["PERSONAL_BIRTHDAY"],
			"PERSONAL_PHOTO"		=> $arPERSONAL_PHOTO,
			"PERSONAL_CITY"		=> $_POST["PERSONAL_CITY"],
			"PERSONAL_STATE"		=> $_POST["PERSONAL_STATE"],
			"PERSONAL_COUNTRY"	=> $_POST["PERSONAL_COUNTRY"],
			"WORK_COMPANY"			=> $_POST["WORK_COMPANY"],
			"WORK_DEPARTMENT"		=> $_POST["WORK_DEPARTMENT"],
			"WORK_POSITION"		=> $_POST["WORK_POSITION"],
			"WORK_WWW"				=> $_POST["WORK_WWW"],
			"WORK_CITY"				=> $_POST["WORK_CITY"],
			"WORK_STATE"			=> $_POST["WORK_STATE"],
			"WORK_COUNTRY"			=> $_POST["WORK_COUNTRY"],
			"WORK_PROFILE"			=> $_POST["WORK_PROFILE"],
			"AUTO_TIME_ZONE"		=> ($_POST["AUTO_TIME_ZONE"] == "Y" || $_POST["AUTO_TIME_ZONE"] == "N"? $_POST["AUTO_TIME_ZONE"] : ""),
		);
		if(isset($_POST["TIME_ZONE"]))
			$arFields["TIME_ZONE"] = $_POST["TIME_ZONE"];

		if ($_POST["NEW_PASSWORD"] <> '')
		{
			$arFields["PASSWORD"] = $_POST["NEW_PASSWORD"];
			$arFields["CONFIRM_PASSWORD"] = $_POST["NEW_PASSWORD_CONFIRM"];
		}

		$GLOBALS["USER_FIELD_MANAGER"]->EditFormAddFields("USER", $arFields);
		$USER->Update($arParams["UID"], $arFields);
		if ($USER->LAST_ERROR)
			$APPLICATION->ThrowException($USER->LAST_ERROR);
		else if (!$APPLICATION->GetException())
		{
			$arFields = array(
				"SHOW_NAME" => ($_POST["FORUM_SHOW_NAME"]=="Y") ? "Y" : "N",
				"HIDE_FROM_ONLINE" => ($_POST["FORUM_HIDE_FROM_ONLINE"]=="Y") ? "Y" : "N",
				"SUBSC_GROUP_MESSAGE" => ($_POST["FORUM_SUBSC_GROUP_MESSAGE"]=="Y") ? "Y" : "N",
				"SUBSC_GET_MY_MESSAGE" => ($_POST["FORUM_SUBSC_GET_MY_MESSAGE"]=="Y") ? "Y" : "N",
				"DESCRIPTION" => $_POST["FORUM_DESCRIPTION"],
				"INTERESTS" => $_POST["FORUM_INTERESTS"],
				"SIGNATURE" => $_POST["FORUM_SIGNATURE"],
				"AVATAR" => $_FILES["FORUM_AVATAR"]);
			$arFields["AVATAR"]["del"] = $_POST["FORUM_AVATAR_del"];

			if (CForumUser::IsAdmin())
			{
				$arFields["ALLOW_POST"] = ($_POST["FORUM_ALLOW_POST"]!="Y") ? "N" : "Y";
			}

			$ar_res = CForumUser::GetByUSER_ID($arParams["UID"]);
			if ($ar_res)
			{
				$arFields["AVATAR"]["old_file"] = $ar_res["AVATAR"];
				$FID = CForumUser::Update($ar_res["ID"], $arFields);
			}
			else
			{
				$arFields["USER_ID"] = $arParams["UID"];
				$FID = CForumUser::Add($arFields);
			}

			if ((intval($FID)<=0) && (!$APPLICATION->GetException()))
				$APPLICATION->ThrowException(GetMessage("FP_ERR_PROF"));
		}
	}

	if ($APPLICATION->GetException())
	{
		if (!$USER->LAST_ERROR)
		{
			$db_user = CUser::GetByID($arParams["UID"]);
			if ($db_user && ($ar_user = $db_user->Fetch()))
				$arResult["USER"] = $ar_user;
		}
		$bVarsFromForm = true;
	}
	else
	{
		if ($USER->GetId() == $arParams["UID"])
			$USER->Authorize($arParams["UID"]);
		if ($_POST["OLD_LOGIN"]!=$_POST["LOGIN"] || $_POST["NEW_PASSWORD"] <> '')
		{
			$USER->SendUserInfo($USER->GetParam("USER_ID"), LANG, GetMessage("FP_CHG_REG_INFO"), true);
		}
		LocalRedirect($arResult["~profile_view"]);
	}
}
/********************************************************************
				/Action
********************************************************************/

/********************************************************************
				Data
********************************************************************/
if ($bVarsFromForm)
{
	$arUserFields = &$DB->GetTableFieldsList("b_user");
	for ($i = 0; $i < count($arUserFields); $i++)
	{
		if (array_key_exists($arUserFields[$i], $_REQUEST))
		{
			${"str_".$arUserFields[$i]} = htmlspecialcharsbx($_REQUEST[$arUserFields[$i]]);
			$arResult["str_".$arUserFields[$i]] = htmlspecialcharsbx($_REQUEST[$arUserFields[$i]]);
		}
	}

	$arUserFields = &$DB->GetTableFieldsList("b_forum_user");
	for ($i = 0; $i < count($arUserFields); $i++)
	{
		if (array_key_exists("FORUM_".$arUserFields[$i], $_REQUEST))
		{
			${"str_FORUM_".$arUserFields[$i]} = htmlspecialcharsbx($_REQUEST["FORUM_".$arUserFields[$i]]);
			$arResult["str_FORUM_".$arUserFields[$i]] = htmlspecialcharsbx($_REQUEST["FORUM_".$arUserFields[$i]]);
		}
	}
}
$err = $APPLICATION->GetException();
if ($err)
	$arResult["ERROR_MESSAGE"] = $err->GetString();
$arResult["BX_ROOT"] = BX_ROOT;

$arResult["arr_PERSONAL_GENDER"] = array();
$arResult["arr_PERSONAL_GENDER"]["data"] = array("M" => GetMessage("FP_SEX_MALE"), "F" => GetMessage("FP_SEX_FEMALE"));
$arResult["arr_PERSONAL_GENDER"]["active"] = $str_PERSONAL_GENDER;
$arResult["~str_PERSONAL_BIRTHDAY"] = $str_PERSONAL_BIRTHDAY;
$arResult["str_PERSONAL_BIRTHDAY"] = CalendarDate("PERSONAL_BIRTHDAY", $str_PERSONAL_BIRTHDAY, "form1", "15");

$arResult["SHOW_DELETE_PERSONAL_PHOTO"] = "N";
$arResult["str_PERSONAL_PHOTO"] = "";
if ($arResult["USER"]["PERSONAL_PHOTO"] <> '')
{
	$arResult["SHOW_DELETE_PERSONAL_PHOTO"] = "Y";
	$arResult["str_PERSONAL_PHOTO"] = $arResult["USER"]["PERSONAL_PHOTO"];
	$arResult["str_PERSONAL_PHOTO_FILE"] = CFile::GetFileArray($arResult["USER"]["PERSONAL_PHOTO"]);
	if ($arResult["str_PERSONAL_PHOTO_FILE"] !== false)
		$arResult["str_PERSONAL_PHOTO_IMG"] = CFile::ShowImage($arResult["str_PERSONAL_PHOTO_FILE"], 150, 150, "border=0  alt=\"\"", "", true);
}
$arResult["SHOW_DELETE_FORUM_AVATAR"] = "N";
$arResult["str_FORUM_AVATAR"] = "";
$arResult["AVATAR_H"] = COption::GetOptionString("forum", "avatar_max_width", 100);
$arResult["AVATAR_V"] = COption::GetOptionString("forum", "avatar_max_height", 100);
$arResult["AVATAR_SIZE"] = COption::GetOptionString("forum", "avatar_max_size", 1048576);
if ($arResult["FORUM_USER"]["AVATAR"] <> '')
{
	$arResult["SHOW_DELETE_FORUM_AVATAR"] = "Y";
	$arResult["str_FORUM_AVATAR"] = $arResult["FORUM_USER"]["AVATAR"];
	$arResult["str_FORUM_AVATAR_FILE"] = CFile::GetFileArray($arResult["FORUM_USER"]["AVATAR"]);
	if ($arResult["str_FORUM_AVATAR_FILE"] !== false)
		$arResult["str_FORUM_AVATAR_IMG"] = CFile::ShowImage($arResult["str_FORUM_AVATAR_FILE"], $arResult["AVATAR_H"], $arResult["AVATAR_V"], "border=0", "",true);
}

$arCountry = GetCountryArray();
$arCountryReturn = array();
for ($i=0; $i<count($arCountry["reference"]); $i++)
{
	$arCountryReturn[$arCountry["reference_id"][$i]] = htmlspecialcharsbx($arCountry["reference"][$i]);
}
$arResult["str_PERSONAL_COUNTRY"] = $str_PERSONAL_COUNTRY;
$arResult["arr_PERSONAL_COUNTRY"]["data"] = $arCountryReturn;
$arResult["arr_PERSONAL_COUNTRY"]["active"] = $str_PERSONAL_COUNTRY;

$arResult["str_WORK_COUNTRY"] = $str_WORK_COUNTRY;
$arResult["arr_WORK_COUNTRY"]["data"] = $arCountryReturn;
$arResult["arr_WORK_COUNTRY"]["active"] = $str_WORK_COUNTRY;

// ********************* User properties ***************************************************
$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
if (!empty($arParams["USER_PROPERTY"]))
{
	$arUserFields = $GLOBALS["USER_FIELD_MANAGER"]->GetUserFields("USER", $arParams["UID"], LANGUAGE_ID);
	if (count($arParams["USER_PROPERTY"]) > 0)
	{
		foreach ($arUserFields as $FIELD_NAME => $arUserField)
		{
			if (!in_array($FIELD_NAME, $arParams["USER_PROPERTY"]))
				continue;
			$arUserField["EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"] <> '' ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
			$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsbx($arUserField["EDIT_FORM_LABEL"]);
			$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
			$arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
		}
	}
	if (!empty($arResult["USER_PROPERTIES"]["DATA"]))
		$arResult["USER_PROPERTIES"]["SHOW"] = "Y";
	$arResult["bVarsFromForm"] = $bVarsFromForm;
}
// ******************** /User properties ***************************************************
// *****************************************************************************************
$arResult["SHOW_NAME"] = $ShowName = CForumUser::GetFormattedNameByUserID(
	$arParams["UID"],
	$arParams["NAME_TEMPLATE"],
	array(
		"SHOW_NAME" => $arResult["FORUM_USER"]["SHOW_NAME"],
		"LOGIN" => $arResult["USER"]["LOGIN"],
		"NAME" => $arResult["USER"]["NAME"],
		"SECOND_NAME" => $arResult["USER"]["SECOND_NAME"],
		"LAST_NAME" => $arResult["USER"]["LAST_NAME"]
	));

//time zones
$arResult["TIME_ZONE_ENABLED"] = CTimeZone::Enabled();
if($arResult["TIME_ZONE_ENABLED"])
	$arResult["TIME_ZONE_LIST"] = CTimeZone::GetZones();

/********************************************************************
			/Data
********************************************************************/

// *****************************************************************************************
$this->IncludeComponentTemplate();
// *****************************************************************************************
if ($arParams["SET_NAVIGATION"] != "N"):
	$APPLICATION->AddChainItem($arResult["SHOW_NAME"], $arResult["~profile_view"]);
	$APPLICATION->AddChainItem(GetMessage("F_TITLE_TITLE"));
endif;
if ($arParams["SET_TITLE"] != "N")
	$APPLICATION->SetTitle($ShowName." (".GetMessage("F_TITLE").")");

?>