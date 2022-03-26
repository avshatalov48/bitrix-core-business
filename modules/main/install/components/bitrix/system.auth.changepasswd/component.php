<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

/**
 * Bitrix vars
 *
 * @var array $arParams
 * @var array $arResult
 * @var CBitrixComponent $this
 * @global CMain $APPLICATION
 * @global CUser $USER
 */

if(!is_array($arParams["~AUTH_RESULT"]) && $arParams["~AUTH_RESULT"] <> '')
{
	$arParams["~AUTH_RESULT"] = array("MESSAGE" => $arParams["~AUTH_RESULT"], "TYPE" => "ERROR");
}

$arResult["SHOW_FORM"] = !(is_array($arParams["~AUTH_RESULT"]) && $arParams["~AUTH_RESULT"]["TYPE"] == "OK");

$arResult["USE_PASSWORD"] = false;
if(is_array($arParams["~AUTH_RESULT"]) && $arParams["~AUTH_RESULT"]["TYPE"] == "ERROR" && $arParams["~AUTH_RESULT"]["ERROR_TYPE"] == "CHANGE_PASSWORD")
{
	//it's required to change the password after N days, use password instead of checkword
	$arResult["USE_PASSWORD"] = true;
	//from the login form
	$_REQUEST["USER_PASSWORD"] = "";
}
if(isset($_REQUEST["USER_CURRENT_PASSWORD"]))
{
	$arResult["USE_PASSWORD"] = true;
}

//stored in the system.auth.forgotpasswd/component.php
$arResult["USER_PHONE_NUMBER"] = $_SESSION["system.auth.changepasswd"]["USER_PHONE_NUMBER"];

$arResult["PHONE_REGISTRATION"] = (
	COption::GetOptionString("main", "new_user_phone_auth", "N") == "Y"
	&& $arResult["USER_PHONE_NUMBER"] <> ''
	&& $arResult["USE_PASSWORD"] == false
);

if($arResult["PHONE_REGISTRATION"])
{
	$arResult["PHONE_CODE_RESEND_INTERVAL"] = CUser::PHONE_CODE_RESEND_INTERVAL;
	$arResult["SIGNED_DATA"] = \Bitrix\Main\Controller\PhoneAuth::signData([
		'phoneNumber' => $arResult["USER_PHONE_NUMBER"],
		'smsTemplate' => "SMS_USER_RESTORE_PASSWORD"
	]);
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
	$arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("change_password=yes", $arParamsToDelete);
}

$arResult["BACKURL"] = $APPLICATION->GetCurPageParam("", $arParamsToDelete);

$arResult["AUTH_AUTH_URL"] = $APPLICATION->GetCurPageParam("login=yes",$arParamsToDelete);

foreach ($arResult as $key => $value)
{
	if (!is_array($value) && !is_bool($value))
	{
		$arResult[$key] = htmlspecialcharsbx($value);
	}
}

$arRequestParams = array(
	"USER_CHECKWORD",
	"USER_CURRENT_PASSWORD",
	"USER_PASSWORD",
	"USER_CONFIRM_PASSWORD",
);

foreach ($arRequestParams as $param)
{
	$arResult[$param] = ($_REQUEST[$param] <> ''? $_REQUEST[$param] : "");
	$arResult[$param] = htmlspecialcharsbx($arResult[$param]);
}

if(isset($_GET["USER_LOGIN"]))
	$arResult["~LAST_LOGIN"] = CUtil::ConvertToLangCharset($_GET["USER_LOGIN"]);
elseif(isset($_POST["USER_LOGIN"]))
	$arResult["~LAST_LOGIN"] = $_POST["USER_LOGIN"];
else
	$arResult["~LAST_LOGIN"] = $_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"];

$arResult["LAST_LOGIN"] = htmlspecialcharsbx($arResult["~LAST_LOGIN"]);

$userId = 0;
if($arResult["~LAST_LOGIN"] <> '')
{
	$res = CUser::GetByLogin($arResult["~LAST_LOGIN"]);
	if($profile = $res->Fetch())
	{
		$userId = $profile["ID"];
	}
}
$arResult["GROUP_POLICY"] = CUser::GetGroupPolicy($userId);

$arResult["SECURE_AUTH"] = false;
if(!CMain::IsHTTPS() && COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
{
	$sec = new CRsaSecurity();
	if(($arKeys = $sec->LoadKeys()))
	{
		$sec->SetKeys($arKeys);
		$sec->AddToForm('bform', ['USER_PASSWORD', 'USER_CONFIRM_PASSWORD', 'USER_CURRENT_PASSWORD']);
		$arResult["SECURE_AUTH"] = true;
	}
}

$arResult["USE_CAPTCHA"] = (COption::GetOptionString("main", "captcha_restoring_password", "N") == "Y" || $APPLICATION->NeedCAPTHAForLogin($arResult["~LAST_LOGIN"]));
if($arResult["USE_CAPTCHA"])
{
	$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
}

$this->IncludeComponentTemplate();
