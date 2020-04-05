<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("bizproc") || !CModule::IncludeModule("iblock"))
	return false;

if (!$GLOBALS["USER"]->IsAuthorized())
{
	$GLOBALS["APPLICATION"]->AuthForm("");
	die();
}

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");
$arParams["ITEMS_COUNT"] = intval($arParams["ITEMS_COUNT"]);
if ($arParams["ITEMS_COUNT"] <= 0)
	$arParams["ITEMS_COUNT"] = 20;
$arParams["COLUMNS_COUNT"] = intval($arParams["COLUMNS_COUNT"]);
if ($arParams["COLUMNS_COUNT"] <= 0)
	$arParams["COLUMNS_COUNT"] = 3;

if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";
if (strLen($arParams["TASK_VAR"]) <= 0)
	$arParams["TASK_VAR"] = "task_id";
if (strLen($arParams["BLOCK_VAR"]) <= 0)
	$arParams["BLOCK_VAR"] = "block_id";

$arParams["PATH_TO_NEW"] = trim($arParams["PATH_TO_NEW"]);
if (strlen($arParams["PATH_TO_NEW"]) <= 0)
	$arParams["PATH_TO_NEW"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=new";

$arParams["PATH_TO_INDEX"] = trim($arParams["PATH_TO_INDEX"]);
if (strlen($arParams["PATH_TO_INDEX"]) <= 0)
	$arParams["PATH_TO_INDEX"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=index";

$arParams["PATH_TO_LIST"] = trim($arParams["PATH_TO_LIST"]);
if (strlen($arParams["PATH_TO_LIST"]) <= 0)
	$arParams["PATH_TO_LIST"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=list&".$arParams["BLOCK_VAR"]."=#block_id#";

$arParams["PATH_TO_START"] = trim($arParams["PATH_TO_START"]);
if (strlen($arParams["PATH_TO_START"]) <= 0)
	$arParams["PATH_TO_START"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=start&".$arParams["BLOCK_VAR"]."=#block_id#";
$arParams["PATH_TO_START"] = $arParams["PATH_TO_START"].((strpos($arParams["PATH_TO_START"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arParams["PATH_TO_TASK"] = trim($arParams["PATH_TO_TASK"]);
if (strlen($arParams["PATH_TO_TASK"]) <= 0)
	$arParams["PATH_TO_TASK"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=task&".$arParams["BLOCK_VAR"]."=#block_id#&".$arParams["TASK_VAR"]."=#task_id#";
$arParams["PATH_TO_TASK"] = $arParams["PATH_TO_TASK"].((strpos($arParams["PATH_TO_TASK"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arParams["PATH_TO_BP"] = trim($arParams["PATH_TO_BP"]);
if (strlen($arParams["PATH_TO_BP"]) <= 0)
	$arParams["PATH_TO_BP"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=bp&".$arParams["BLOCK_VAR"]."=#block_id#";
$arParams["PATH_TO_BP"] = $arParams["PATH_TO_BP"].((strpos($arParams["PATH_TO_BP"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arResult["FatalErrorMessage"] = "";
$arResult["ErrorMessage"] = "";

$arResult["NEW_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_NEW"], array());

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if (strlen($arParams["IBLOCK_TYPE"]) <= 0)
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WIC_EMPTY_IBLOCK_TYPE").". ";

$arResult["BackUrl"] = urlencode(strlen($_REQUEST["back_url"]) <= 0 ? $APPLICATION->GetCurPageParam() : $_REQUEST["back_url"]);

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	$arResult["BlockType"] = null;
	$ar = CIBlockType::GetByIDLang($arParams["IBLOCK_TYPE"], LANGUAGE_ID, true);
	if ($ar)
		$arResult["BlockType"] = $ar;
	else
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WIC_WRONG_IBLOCK_TYPE").". ";
}

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	$arResult["AdminAccess"] = ($USER->IsAdmin() || is_array($arParams["ADMIN_ACCESS"]) && (count(array_intersect($USER->GetUserGroupArray(), $arParams["ADMIN_ACCESS"])) > 0));

	$deleteBlockId = intval($_REQUEST["delete_block_id"]);
	if ($deleteBlockId > 0 && $arResult["AdminAccess"] && check_bitrix_sessid())
	{
		$db = CIBlock::GetList(
			array(),
			array("ID" => $deleteBlockId, "SITE_ID" => SITE_ID, "TYPE" => $arParams["IBLOCK_TYPE"])
		);
		if ($ar = $db->GetNext())
		{
			$db1 = CIBlockElement::GetList(array(), array("IBLOCK_ID" => $ar["ID"], "SHOW_NEW" => "Y"), false, false, array("IBLOCK_ID", "ID"));
			while ($ar1 = $db1->Fetch())
				CBPDocument::OnDocumentDelete(array("bizproc", "CBPVirtualDocument", $ar1["ID"]), $arErrorsTmp);

			$db2 = CBPWorkflowTemplateLoader::GetList(
				array(),
				array("DOCUMENT_TYPE" => array("bizproc", "CBPVirtualDocument", "type_".$ar["ID"])),
				false,
				false,
				array("ID")
			);
			while ($ar2 = $db2->Fetch())
				CBPWorkflowTemplateLoader::Delete($ar2["ID"]);

			CIBlock::Delete($ar["ID"]);
		}
	}
}

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	$arResult["Blocks"] = array();

	$dbBlockList = CIBlock::GetList(
		array("SORT" => "ASC", "NAME" => "ASC"),
		array("ACTIVE" => "Y", "SITE_ID" => SITE_ID, "TYPE" => $arParams["IBLOCK_TYPE"])
	);
	while ($arBlock = $dbBlockList->GetNext())
	{
		$arBlock["LIST_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LIST"], array("block_id" => $arBlock["ID"]));

		if (intval($arBlock["PICTURE"]) <= 0)
			$arBlock["PICTURE"] = "/bitrix/images/bizproc/vd_bp.jpg";

		$arMessagesTmp = CIBlock::GetMessages($arBlock["ID"]);
		$arBlock["CreateTitle"] = htmlspecialcharsbx(is_array($arMessagesTmp) && array_key_exists("ELEMENT_ADD", $arMessagesTmp) ? $arMessagesTmp["ELEMENT_ADD"] : "");

		$workflowTemplateId = 0;
		$db = CBPWorkflowTemplateLoader::GetList(array(), array("DOCUMENT_TYPE" => array("bizproc", "CBPVirtualDocument", "type_".$arBlock["ID"])), false, false, array("ID"));
		if ($ar = $db->Fetch())
			$workflowTemplateId = intval($ar["ID"]);

		if ($workflowTemplateId > 0)
		{
			$arWorkflowTemplate = CBPWorkflowTemplateLoader::GetTemplateState($workflowTemplateId);

			if (!is_array($arWorkflowTemplate["STATE_PERMISSIONS"]) || count($arWorkflowTemplate["STATE_PERMISSIONS"]) <= 0)
				$arWorkflowTemplate["STATE_PERMISSIONS"]["create"] = array("author");

			$arAllowableOperations = CBPDocument::GetAllowableOperations($GLOBALS["USER"]->GetID(), $GLOBALS["USER"]->GetUserGroupArray(), array($arWorkflowTemplate));

			$arBlock["START_URL"] = "";
			if ($arResult["AdminAccess"] || (is_array($arAllowableOperations) && in_array("create", $arAllowableOperations) || is_array($arWorkflowTemplate["STATE_PERMISSIONS"]["create"]) && in_array("author", $arWorkflowTemplate["STATE_PERMISSIONS"]["create"])))
				$arBlock["START_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_START"], array("block_id" => $arBlock["ID"]));
		}

		$arBlock["DELETE_URL"] = "";
		if ($arResult["AdminAccess"])
		{
			$arBlock["EDIT_URL"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_EDIT"], array("block_id" => $arBlock["ID"]));

			$arBlock["DELETE_URL"]  = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_INDEX"], array());
			$arBlock["DELETE_URL"] .= ((strpos($arBlock["DELETE_URL"], "?") === false) ? "?" : "&");
			$arBlock["DELETE_URL"] .= "delete_block_id=".$arBlock["ID"]."&".bitrix_sessid_get();
		}

		$arResult["Blocks"][] = $arBlock;
	}
}

$this->IncludeComponentTemplate();

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle($arResult["BlockType"]["NAME"]);
	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem($arResult["BlockType"]["NAME"]);
}
else
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("BPWC_WIC_ERROR"));
	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(GetMessage("BPWC_WIC_ERROR"));
}
?>