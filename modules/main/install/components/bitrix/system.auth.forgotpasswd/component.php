<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?><?

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

if($arResult["PHONE_REGISTRATION"] && $_REQUEST["USER_PHONE_NUMBER"] <> '' && is_array($arParams["~AUTH_RESULT"]) && $arParams["~AUTH_RESULT"]["TYPE"] == "OK")
{
	//sms with a code was sent. Redirect to the change password form
	$_SESSION["system.auth.changepasswd"] = ["USER_PHONE_NUMBER" => $_REQUEST["USER_PHONE_NUMBER"]];
	LocalRedirect($APPLICATION->GetCurPageParam("change_password=yes", $arParamsToDelete));
}

if(defined("AUTH_404"))
{
	$arResult["AUTH_URL"] = POST_FORM_ACTION_URI;
}
else
{
	$arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("forgot_password=yes", $arParamsToDelete);
}

$arResult["BACKURL"] = $APPLICATION->GetCurPageParam("", $arParamsToDelete);
$arResult["AUTH_AUTH_URL"] = $APPLICATION->GetCurPageParam("login=yes", $arParamsToDelete);

foreach ($arResult as $key => $value)
{
	if (!is_array($value))
	{
		$arResult[$key] = htmlspecialcharsbx($value);
	}
}

$arResult["LAST_LOGIN"] = htmlspecialcharsbx($_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"]);

$arResult["USE_CAPTCHA"] = (COption::GetOptionString("main", "captcha_restoring_password", "N") == "Y");
if($arResult["USE_CAPTCHA"])
{
	$arResult["CAPTCHA_CODE"] = htmlspecialcharsbx($APPLICATION->CaptchaGetCode());
}

$this->IncludeComponentTemplate();
