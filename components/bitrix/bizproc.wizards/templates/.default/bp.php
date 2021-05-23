<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
$arResult["VARIABLES"]["block_id"] = intval($arResult["VARIABLES"]["block_id"]);
if ($arResult["VARIABLES"]["block_id"] <= 0)
{
	ShowError(GetMessage("BPWC_WCT_EMPTY_BLOCK"));
	return;
}

if (!$USER->IsAdmin())
{
	if (!is_array($arParams["ADMIN_ACCESS"]) || count(array_intersect($USER->GetUserGroupArray(), $arParams["ADMIN_ACCESS"])) <= 0)
	{
		$GLOBALS["APPLICATION"]->AuthForm("");
		die();
	}
}

$workflowTemplateId = 0;
$db = CBPWorkflowTemplateLoader::GetList(
	array(),
	array("DOCUMENT_TYPE" => array("bizproc", "CBPVirtualDocument", "type_".$arResult["VARIABLES"]["block_id"])),
	false,
	false,
	array("ID")
);
if ($ar = $db->Fetch())
	$workflowTemplateId = intval($ar["ID"]);

if ($arResult["ALIASES"]["page"] == '')
	$arResult["ALIASES"]["page"] = "page";
if ($arResult["ALIASES"]["block_id"] == '')
	$arResult["ALIASES"]["block_id"] = "block_id";

$pathToBP = trim($arResult["PATH_TO_BP"]);
if ($pathToBP == '')
	$pathToBP = $APPLICATION->GetCurPage()."?".$arResult["ALIASES"]["page"]."=bp&".$arResult["ALIASES"]["block_id"]."=#block_id#";
$pathToBP = $pathToBP.((mb_strpos($pathToBP, "?") === false) ? "?" : "&").bitrix_sessid_get();

$pathToList = trim($arResult["PATH_TO_LIST"]);
if ($pathToList == '')
	$pathToList = $APPLICATION->GetCurPage()."?".$arResult["ALIASES"]["page"]."=list&".$arResult["ALIASES"]["block_id"]."=#block_id#";

$APPLICATION->IncludeComponent(
	"bitrix:bizproc.workflow.edit",
	"",
	array(
		"MODULE_ID" => "bizproc",
		"ENTITY" => "CBPVirtualDocument",
		"DOCUMENT_TYPE" => "type_".$arResult["VARIABLES"]["block_id"],
		"ID" => $workflowTemplateId,
		"EDIT_PAGE_TEMPLATE" => CComponentEngine::MakePathFromTemplate($pathToBP, array("block_id" => $arResult["VARIABLES"]["block_id"])),
		"LIST_PAGE_URL" => CComponentEngine::MakePathFromTemplate($pathToList, array("block_id" => $arResult["VARIABLES"]["block_id"])),
		"SHOW_TOOLBAR" => "Y",
		"SET_TITLE" => $arParams["SET_TITLE"],
		"SKIP_BP_TYPE_SELECT" => ($workflowTemplateId > 0 ? "Y" : "N"),
		"BIZPROC_EDIT_MENU_LIST_MESSAGE" => GetMessage("BIZPROC_EDIT_MENU_LIST_MESSAGE"),
		"BIZPROC_EDIT_MENU_LIST_TITLE_MESSAGE" => GetMessage("BIZPROC_EDIT_MENU_LIST_TITLE_MESSAGE"),
	)
);
?>