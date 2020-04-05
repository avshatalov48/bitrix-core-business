<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$aGlobalOpt = CUserOptions::GetOption("global", "settings", array());
$bShowPerfmon = (file_exists($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/perfmon/install/index.php") && $aGlobalOpt['messages']['perfmon'] <> 'N');

$arDescription = array(
	"DISABLED" => !$bShowPerfmon,
	"NAME" => GetMessage("GD_PERFMON_NAME"),
	"DESCRIPTION" => GetMessage("GD_PERFMON_DESC"),
	"ICON" => "",
	"TITLE_ICON_CLASS" => "bx-gadgets-perfmon",
	"GROUP" => array("ID"=>"admin_settings"),
	"NOPARAMS" => "Y",
	"AI_ONLY" => true,
	"COLOURFUL" => true,
	"UNIQUE" => true,
);
