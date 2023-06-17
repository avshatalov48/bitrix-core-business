<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
	die();

//$arParams["BUTTONS"] = array(
//	array("NEWBAR"=>true),
//	array("SEPARATOR"=>true),
//	array("HTML"=>""),
//	array("TEXT", "ICON", "TITLE", "LINK", "LINK_PARAM"),
//	array("TEXT", "ICON", "TITLE", "MENU"=>array(array("SEPARATOR"=>true, "ICONCLASS", "TEXT", "TITLE", "ONCLICK"), ...)),
//	...
//)

if (!isset($arParams["BUTTONS"]) && !is_array($arParams["BUTTONS"]))
{
	$arParams["BUTTONS"] = [];
}

if (!isset($arParams["TOOLBAR_ID"]) || $arParams["TOOLBAR_ID"] == '')
{
	$arParams["TOOLBAR_ID"] = "toolbar_" . randString(5);
}
else
{
	$arParams["TOOLBAR_ID"] = preg_replace("/[^a-z0-9_]/i", "", $arParams["TOOLBAR_ID"]);
}
$this->IncludeComponentTemplate();
?>
