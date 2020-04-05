<?
/**
 * Bitrix Framework
 * @package bitrix
 * @subpackage main
 * @copyright 2001-2013 Bitrix
 */

/**
 * Bitrix vars
 * @global CMain $APPLICATION
 * @global CUserTypeManager $USER_FIELD_MANAGER
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 */

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

global $USER_FIELD_MANAGER;

if(!is_array($arParams["~AUTH_RESULT"]) && $arParams["~AUTH_RESULT"] <> '')
{
	$arParams["~AUTH_RESULT"] = array("MESSAGE" => $arParams["~AUTH_RESULT"], "TYPE" => "ERROR");
}

$arResult["PHONE_REGISTRATION"] = (COption::GetOptionString("main", "new_user_phone_auth", "N") == "Y");
$arResult["PHONE_REQUIRED"] = ($arResult["PHONE_REGISTRATION"] && COption::GetOptionString("main", "new_user_phone_required", "N") == "Y");
$arResult["EMAIL_REGISTRATION"] = (COption::GetOptionString("main", "new_user_email_auth", "Y") <> "N");
$arResult["EMAIL_REQUIRED"] = ($arResult["EMAIL_REGISTRATION"] && COption::GetOptionString("main", "new_user_email_required", "Y") <> "N");
$arResult["USE_EMAIL_CONFIRMATION"] = (COption::GetOptionString("main", "new_user_registration_email_confirmation", "N") == "Y" && $arResult["EMAIL_REQUIRED"]? "Y" : "N");
$arResult["PHONE_CODE_RESEND_INTERVAL"] = CUser::PHONE_CODE_RESEND_INTERVAL;

$def_group = COption::GetOptionString("main", "new_user_registration_def_group", "");
if($def_group!="")
{
	$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(explode(",", $def_group));
}
else
{
	$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy(array());
}

$arParamsToDelete = array(
	"login",
	"logout",
	"register",
	"forgot_password",
	"change_password",
	"confirm_registration",
	"confirm_code",
	"confirm_user_id",
);

if(defined("AUTH_404"))
{
	$arResult["AUTH_URL"] = POST_FORM_ACTION_URI;
}
else
{
	$arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("register=yes", $arParamsToDelete);
}

$arResult["AUTH_AUTH_URL"] = $APPLICATION->GetCurPageParam("login=yes", $arParamsToDelete);

foreach ($arResult as $key => $value)
{
	if (!is_array($value)) $arResult[$key] = htmlspecialcharsbx($value);
}

$arRequestParams = array(
	"USER_NAME",
	"USER_LAST_NAME",
	"USER_LOGIN",
	"USER_PASSWORD",
	"USER_CONFIRM_PASSWORD",
	"USER_PHONE_NUMBER",
);

foreach ($arRequestParams as $param)
{
	$arResult[$param] = strlen($_REQUEST[$param]) > 0 ? $_REQUEST[$param] : "";
	$arResult[$param] = htmlspecialcharsbx($arResult[$param]);
}

$arResult["USER_EMAIL"] = htmlspecialcharsbx(strlen($_REQUEST["sf_EMAIL"])>0 ? $_REQUEST["sf_EMAIL"] : $_REQUEST["USER_EMAIL"]);

// ********************* User properties ***************************************************
$arResult["USER_PROPERTIES"] = array("SHOW" => "N");
$arUserFields = $USER_FIELD_MANAGER->GetUserFields("USER", 0, LANGUAGE_ID);
if (is_array($arUserFields) && count($arUserFields) > 0)
{
	foreach ($arUserFields as $FIELD_NAME => $arUserField)
	{
		if ($arUserField["MANDATORY"] != "Y")
			continue;
		$arUserField["EDIT_FORM_LABEL"] = strlen($arUserField["EDIT_FORM_LABEL"]) > 0 ? $arUserField["EDIT_FORM_LABEL"] : $arUserField["FIELD_NAME"];
		$arUserField["EDIT_FORM_LABEL"] = htmlspecialcharsEx($arUserField["EDIT_FORM_LABEL"]);
		$arUserField["~EDIT_FORM_LABEL"] = $arUserField["EDIT_FORM_LABEL"];
		$arResult["USER_PROPERTIES"]["DATA"][$FIELD_NAME] = $arUserField;
	}
}
if (!empty($arResult["USER_PROPERTIES"]["DATA"]))
	$arResult["USER_PROPERTIES"]["SHOW"] = "Y";
// ******************** /User properties ***************************************************

$arResult["SHOW_SMS_FIELD"] = false;
$arResult["SHOW_EMAIL_SENT_CONFIRMATION"] = false;
$arResult["bVarsFromForm"] = false;

if(is_array($arParams["AUTH_RESULT"]))
{
	if(isset($arParams["~AUTH_RESULT"]["SIGNED_DATA"]))
	{
		//special key "SIGNED_DATA" was added after the SMS was sent in CUser::Register()
		$arResult["SHOW_SMS_FIELD"] = true;
		$arResult["SIGNED_DATA"] = $arParams["~AUTH_RESULT"]["SIGNED_DATA"];
	}
	elseif($arParams['AUTH_RESULT']["TYPE"] == "ERROR")
	{
		$arResult["bVarsFromForm"] = true;
	}
	if($arResult["USE_EMAIL_CONFIRMATION"] === "Y" && $arParams["AUTH_RESULT"]["TYPE"] === "OK")
	{
		$arResult["SHOW_EMAIL_SENT_CONFIRMATION"] = true;
	}
}
elseif($arParams["AUTH_RESULT"] <> '')
{
	$arResult["bVarsFromForm"] = true;
}

$arResult["USE_CAPTCHA"] = (COption::GetOptionString("main", "captcha_registration", "N") == "Y"? "Y" : "N");

if ($arResult["USE_CAPTCHA"] == "Y")
{
	$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
}

$arResult["AGREEMENT_ORIGINATOR_ID"] = "main/reg";
$arResult["AGREEMENT_ORIGIN_ID"] = "register";
$arResult["AGREEMENT_INPUT_NAME"] = "USER_AGREEMENT";

$arResult["SECURE_AUTH"] = false;
if(!CMain::IsHTTPS() && COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
{
	$sec = new CRsaSecurity();
	if(($arKeys = $sec->LoadKeys()))
	{
		$sec->SetKeys($arKeys);
		$sec->AddToForm('bform', array('USER_PASSWORD', 'USER_CONFIRM_PASSWORD'));
		$arResult["SECURE_AUTH"] = true;
	}
}

// verify phone code
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_REQUEST["code_submit_button"] <> '' && !$USER->IsAuthorized())
{
	if($_REQUEST["SIGNED_DATA"] <> '')
	{
		if(($params = \Bitrix\Main\Controller\PhoneAuth::extractData($_REQUEST["SIGNED_DATA"])) !== false)
		{
			if(($userId = CUser::VerifyPhoneCode($params['phoneNumber'], $_REQUEST["SMS_CODE"])))
			{
				if($arResult["PHONE_REQUIRED"])
				{
					//the user was added as inactive, now phone number is confirmed, activate them
					$user = new CUser();
					$user->Update($userId, ["ACTIVE" => "Y"]);
				}
				// authorize user
				$USER->Authorize($userId);
				LocalRedirect($APPLICATION->GetCurPageParam("", $arParamsToDelete));
			}
			else
			{
				$arParams["~AUTH_RESULT"] = array(
					"MESSAGE" => GetMessage("main_register_sms_error"),
					"TYPE" => "ERROR",
				);
				$arResult["SHOW_SMS_FIELD"] = true;
				$arResult["SMS_CODE"] = $_REQUEST["SMS_CODE"];
				$arResult["SIGNED_DATA"] = $_REQUEST["SIGNED_DATA"];
			}
		}
	}
}

$this->IncludeComponentTemplate();
