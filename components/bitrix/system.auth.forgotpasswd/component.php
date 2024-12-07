<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
	die();

/**
 * @global CMain $APPLICATION
 * @var array $arParams
 */

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

if(!is_array($arParams["~AUTH_RESULT"]) && $arParams["~AUTH_RESULT"] <> '')
{
	$arParams["~AUTH_RESULT"] = array("MESSAGE" => $arParams["~AUTH_RESULT"], "TYPE" => "ERROR");
}

$arResult["PHONE_REGISTRATION"] = (COption::GetOptionString("main", "new_user_phone_auth", "N") == "Y");

if (
	$arResult["PHONE_REGISTRATION"]
	&& isset($_REQUEST["USER_PHONE_NUMBER"])
	&& $_REQUEST["USER_PHONE_NUMBER"] <> ''
	&& is_array($arParams["~AUTH_RESULT"])
	&& $arParams["~AUTH_RESULT"]["TYPE"] == "OK"
)
{
	//sms with a code was sent. Redirect to the change password form
	$_SESSION["system.auth.changepasswd"] = ["USER_PHONE_NUMBER" => $_REQUEST["USER_PHONE_NUMBER"]];
	LocalRedirect($APPLICATION->GetCurPageParam("change_password=yes", $arParamsToDelete));
}

$arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("forgot_password=yes", $arParamsToDelete);

$arResult["BACKURL"] = $APPLICATION->GetCurPageParam("", $arParamsToDelete);
$arResult["AUTH_AUTH_URL"] = $APPLICATION->GetCurPageParam("login=yes", $arParamsToDelete);
$arResult["LAST_LOGIN"] = $_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"] ?? '';

if(is_array($arParams["~AUTH_RESULT"]) && $arParams["~AUTH_RESULT"]["TYPE"] == "ERROR")
{
	$arResult["USER_PHONE_NUMBER"] = $_REQUEST["USER_PHONE_NUMBER"] ?? '';
	$arResult["USER_LOGIN"] = $_REQUEST["USER_LOGIN"] ?? '';
}
else
{
	$arResult["USER_LOGIN"] = $arResult["LAST_LOGIN"];
}

$arResult["USE_CAPTCHA"] = (COption::GetOptionString("main", "captcha_restoring_password", "N") == "Y");
if($arResult["USE_CAPTCHA"])
{
	$arResult["CAPTCHA_CODE"] = $APPLICATION->CaptchaGetCode();
}

foreach ($arResult as $key => $value)
{
	if (!is_array($value))
	{
		$arResult[$key] = htmlspecialcharsbx($value);
	}
}

$this->IncludeComponentTemplate();
