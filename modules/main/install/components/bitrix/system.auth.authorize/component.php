<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/**
 * @global CMain $APPLICATION
 * @global CUser $USER
 * @var array $arParams
 * Parameters:
 *	AUTH_RESULT - Authorization result message
 *	NOT_SHOW_LINKS - Whether to show links to register page && password restoration (Y/N)
 */

use \Bitrix\Main\Security;
use \Bitrix\Main\Controller;
use \Bitrix\Pull;

$arParams["NOT_SHOW_LINKS"] = ($arParams["NOT_SHOW_LINKS"] == "Y" ? "Y" : "N");
if(!is_array($arParams["~AUTH_RESULT"]) && $arParams["~AUTH_RESULT"] <> '')
{
	$arParams["~AUTH_RESULT"] = array("MESSAGE" => $arParams["~AUTH_RESULT"], "TYPE" => "ERROR");
}

$arParamsToDelete = array(
	"login",
	"login_form",
	"logout",
	"register",
	"forgot_password",
	"change_password",
	"confirm_registration",
	"confirm_code",
	"confirm_user_id",
	"logout_butt",
);

if(defined("AUTH_404"))
	$arResult["AUTH_URL"] = htmlspecialcharsback(POST_FORM_ACTION_URI);
else
	$arResult["AUTH_URL"] = $APPLICATION->GetCurPageParam("login=yes", $arParamsToDelete);

$custom_reg_page = COption::GetOptionString('main', 'custom_register_page');
$arResult["AUTH_REGISTER_URL"] = ($custom_reg_page <> ''? $custom_reg_page : $APPLICATION->GetCurPageParam("register=yes", $arParamsToDelete));
$arResult["AUTH_FORGOT_PASSWORD_URL"] = $APPLICATION->GetCurPageParam("forgot_password=yes", $arParamsToDelete);
$arResult["AUTH_CHANGE_PASSWORD_URL"] = $APPLICATION->GetCurPageParam("change_password=yes", $arParamsToDelete);
$arResult["BACKURL"] = $APPLICATION->GetCurPageParam("", $arParamsToDelete);

$arRes = array();
foreach($arResult as $key=>$value)
{
	$arRes[$key] = htmlspecialcharsbx($value);
	$arRes['~'.$key] = $value;
}
$arResult = $arRes;

$arVarExcl = array("USER_LOGIN"=>1, "USER_PASSWORD"=>1, "backurl"=>1, "auth_service_id"=>1, "TYPE"=>1, "AUTH_FORM"=>1);
$arResult["POST"] = array();
foreach($_POST as $vname=>$vvalue)
{
	if(!isset($arVarExcl[$vname]))
	{
		if(!is_array($vvalue))
		{
			$arResult["POST"][htmlspecialcharsbx($vname)] = htmlspecialcharsbx($vvalue);
		}
		else
		{
			foreach($vvalue as $k1 => $v1)
			{
				if(is_array($v1))
				{
					foreach($v1 as $k2 => $v2)
					{
						if(!is_array($v2))
							$arResult["POST"][htmlspecialcharsbx($vname)."[".htmlspecialcharsbx($k1)."][".htmlspecialcharsbx($k2)."]"] = htmlspecialcharsbx($v2);
					}
				}
				else
				{
					$arResult["POST"][htmlspecialcharsbx($vname)."[".htmlspecialcharsbx($k1)."]"] = htmlspecialcharsbx($v1);
				}
			}
		}
	}
}

$arResult["~LAST_LOGIN"] = $_COOKIE[COption::GetOptionString("main", "cookie_name", "BITRIX_SM")."_LOGIN"] ?? '';
$arResult["LAST_LOGIN"] = htmlspecialcharsbx($arResult["~LAST_LOGIN"]);
$arResult["STORE_PASSWORD"] = COption::GetOptionString("main", "store_password", "Y") == "Y" ? "Y" : "N";
$arResult["NEW_USER_REGISTRATION"] = (COption::GetOptionString("main", "new_user_registration", "N") == "Y" ? "Y" : "N");
$arResult["ALLOW_SOCSERV_AUTHORIZATION"] = (COption::GetOptionString("main", "allow_socserv_authorization", "Y") != "N" ? "Y" : "N");
$arResult["ALLOW_QRCODE_AUTH"] = (COption::GetOptionString("main", "allow_qrcode_auth", "N") == "Y" && \Bitrix\Main\Loader::includeModule('pull'));

if ($arResult['ALLOW_QRCODE_AUTH'])
{
	$arResult['QRCODE_CHANNEL_TAG'] = Security\Random::getString(32, true);
	$arResult['QRCODE_CHANNEL'] = Pull\Model\Channel::createWithTag($arResult['QRCODE_CHANNEL_TAG']);
	$arResult['QRCODE_CONFIG'] = Pull\Config::get(['CHANNEL' => $arResult['QRCODE_CHANNEL'], 'JSON' => true]);
	$arResult['QRCODE_UNIQUE_ID'] = Controller\QrCodeAuth::getUniqueId();
}

$arResult["AUTH_SERVICES"] = false;
$arResult["CURRENT_SERVICE"] = false;
$arResult["FOR_INTRANET"] = (IsModuleInstalled("intranet") || IsModuleInstalled("rest"));

if(!$USER->IsAuthorized() && CModule::IncludeModule("socialservices") && ($arResult["ALLOW_SOCSERV_AUTHORIZATION"] == 'Y'))
{
	$oAuthManager = new CSocServAuthManager();
	$arServices = $oAuthManager->GetActiveAuthServices(array(
		'BACKURL' => $arResult['~BACKURL'],
		'FOR_INTRANET' => $arResult['FOR_INTRANET'],
	));

	if(!empty($arServices))
	{
		$arResult["AUTH_SERVICES"] = $arServices;
		if(isset($_REQUEST["auth_service_id"]) && $_REQUEST["auth_service_id"] <> '' && isset($arResult["AUTH_SERVICES"][$_REQUEST["auth_service_id"]]))
		{
			$arResult["CURRENT_SERVICE"] = $_REQUEST["auth_service_id"];
			if(isset($_REQUEST["auth_service_error"]) && $_REQUEST["auth_service_error"] <> '')
			{
				$arResult['ERROR_MESSAGE'] = $oAuthManager->GetError($arResult["CURRENT_SERVICE"], $_REQUEST["auth_service_error"]);
			}
			elseif(!$oAuthManager->Authorize($_REQUEST["auth_service_id"]))
			{
				$ex = $APPLICATION->GetException();
				if ($ex)
					$arResult['ERROR_MESSAGE'] = $ex->GetString();
			}
		}
	}
}

$arResult["SECURE_AUTH"] = false;
if(!CMain::IsHTTPS() && COption::GetOptionString('main', 'use_encrypted_auth', 'N') == 'Y')
{
	$sec = new CRsaSecurity();
	if(($arKeys = $sec->LoadKeys()))
	{
		$sec->SetKeys($arKeys);
		$sec->AddToForm('form_auth', array('USER_PASSWORD'));
		$arResult["SECURE_AUTH"] = true;
	}
}

$arResult["CAPTCHA_CODE"] = false;
if($APPLICATION->NeedCAPTHAForLogin($arResult["LAST_LOGIN"]))
{
	$arResult["CAPTCHA_CODE"] = $APPLICATION->CaptchaGetCode();
}

$this->IncludeComponentTemplate();
