<?php

use Bitrix\Main\Web\Json;

if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)
{
	die();
}

if (!is_array($arAuthResult))
{
	$arAuthResult = array("TYPE" => "ERROR", "MESSAGE" => $arAuthResult);
}

if($inc_file === "otp")
{
	$arAuthResult['CAPTCHA'] = CModule::IncludeModule("security")
		&& \Bitrix\Security\Mfa\Otp::isCaptchaRequired();
}
elseif($inc_file == 'forgot_password' || $inc_file == 'change_password')
{
	$arAuthResult['CAPTCHA'] = COption::GetOptionString("main", "captcha_restoring_password", "N") == "Y";
}
else
{
	$arAuthResult['CAPTCHA'] = $APPLICATION->NeedCAPTHAForLogin($last_login);
}

if ($arAuthResult['CAPTCHA'])
{
	$arAuthResult['CAPTCHA_CODE'] = $APPLICATION->CaptchaGetCode();
}

if (is_string($arAuthResult['MESSAGE']))
{
	$arAuthResult['MESSAGE'] = str_replace('<br>', '', $arAuthResult['MESSAGE']);
}

if ($bOnHit):
?>
<script>
BX.ready(function(){BX.defer(BX.adminLogin.setAuthResult, BX.adminLogin)(<?= Json::encode($arAuthResult) ?>);});
</script>
<?
else:
?>
<script bxrunfirst="true">
top.BX.adminLogin.setAuthResult(<?= Json::encode($arAuthResult) ?>);
</script>
<?
endif;
?>