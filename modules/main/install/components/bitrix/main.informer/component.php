<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->setFrameMode(false);

if (!$GLOBALS["USER"]->IsAuthorized())
	return true;

if (!array_key_exists("NOTES", $arParams) || !is_array($arParams["NOTES"]))
	$arParams["NOTES"] = $arParams["~NOTES"] = array();

if (!array_key_exists("ID", $arParams) || strlen($arParams["ID"]) <= 0)
	$arParams["ID"] = md5($APPLICATION->GetCurPage());

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/classes/".strToLower($GLOBALS["DB"]->type)."/favorites.php");

$arResult["text"] = $arParams["NOTES"];

$arResult["informer"] = CUserOptions::GetOption('main', 'informer_'.$arParams["ID"], array('show' => true, 'step' => 1));
$arResult["informer"] = array(
	"show" => ((is_set($arResult["informer"], "show") && ($arResult["informer"]["show"] == false || 
		$arResult["informer"]["show"] === "false")) ? false : true), 
	"step" => $arResult["informer"]["step"] > 0 ? $arResult["informer"]["step"] : 1, 
	"steps" => count($arResult["text"]));

if (!$arResult["informer"]["show"])
	return true; 

$APPLICATION->AddHeadScript('/bitrix/js/main/admin_tools.js');

$this->IncludeComponentTemplate();
?>