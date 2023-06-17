<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$aGlobalOpt = CUserOptions::GetOption("global", "settings", array());
$messageSecurity = (string)($aGlobalOpt['messages']['security'] ?? 'Y');
$bShowSecurity = (
	file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/security/install/index.php")
	&& $messageSecurity !== 'N'
);

$arDescription = Array(
	"DISABLED" => !$bShowSecurity,
	"NAME" => GetMessage("GD_SECURITY_NAME"),
	"DESCRIPTION" => GetMessage("GD_SECURITY_DESC"),
	"ICON" =>"",
	"TITLE_ICON_CLASS" => "bx-gadgets-security",
	"GROUP" => Array("ID"=>"admin_settings"),
	"NOPARAMS" => "Y",
	"AI_ONLY" => true,
	"COLOURFUL" => true
);
