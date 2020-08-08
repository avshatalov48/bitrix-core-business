<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("bizproc") || !CModule::IncludeModule("iblock"))
	return false;

if (!$GLOBALS["USER"]->IsAuthorized())
{
	$GLOBALS["APPLICATION"]->AuthForm("");
	die();
}
if (!$USER->IsAdmin() && (!is_array($arParams["ADMIN_ACCESS"]) || count(array_intersect($USER->GetUserGroupArray(), $arParams["ADMIN_ACCESS"])) <= 0))
{
	$GLOBALS["APPLICATION"]->AuthForm("");
	die();
}

$pathToTemplates = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/templates_bp";

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if ($arParams["BLOCK_VAR"] == '')
	$arParams["BLOCK_VAR"] = "block_id";

$arParams["PATH_TO_NEW"] = trim($arParams["PATH_TO_NEW"]);
if ($arParams["PATH_TO_NEW"] == '')
	$arParams["PATH_TO_NEW"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=new");

$arParams["PATH_TO_LIST"] = trim($arParams["PATH_TO_LIST"]);
if ($arParams["PATH_TO_LIST"] == '')
	$arParams["PATH_TO_LIST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=list&".$arParams["BLOCK_VAR"]."=#block_id#");

$arParams["PATH_TO_INDEX"] = trim($arParams["PATH_TO_INDEX"]);
if ($arParams["PATH_TO_INDEX"] == '')
	$arParams["PATH_TO_INDEX"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=index");

$arParams["PATH_TO_TASK"] = trim($arParams["PATH_TO_TASK"]);
if ($arParams["PATH_TO_TASK"] == '')
	$arParams["PATH_TO_TASK"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=task&".$arParams["TASK_VAR"]."=#task_id#");
$arParams["PATH_TO_TASK"] = $arParams["PATH_TO_TASK"].((mb_strpos($arParams["PATH_TO_TASK"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arParams["PATH_TO_BP"] = trim($arParams["PATH_TO_BP"]);
if ($arParams["PATH_TO_BP"] == '')
	$arParams["PATH_TO_BP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=bp&".$arParams["BLOCK_VAR"]."=#block_id#");
$arParams["PATH_TO_BP"] = $arParams["PATH_TO_BP"].((mb_strpos($arParams["PATH_TO_BP"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arParams["PATH_TO_SETVAR"] = trim($arParams["PATH_TO_SETVAR"]);
if ($arParams["PATH_TO_SETVAR"] == '')
	$arParams["PATH_TO_SETVAR"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=setvar&".$arParams["BLOCK_VAR"]."=#block_id#";
$arParams["PATH_TO_SETVAR"] = $arParams["PATH_TO_SETVAR"].((mb_strpos($arParams["PATH_TO_SETVAR"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arResult["BackUrl"] = urlencode($_REQUEST["back_url"] == '' ? $APPLICATION->GetCurPageParam() : $_REQUEST["back_url"]);

$arResult["FatalErrorMessage"] = "";
$arResult["ErrorMessage"] = "";

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if ($arParams["IBLOCK_TYPE"] == '')
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WVC_EMPTY_IBLOCK_TYPE").". ";

$arParams["BLOCK_ID"] = intval($arParams["BLOCK_ID"]);
if ($arParams["BLOCK_ID"] <= 0)
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WVC_EMPTY_IBLOCK").". ";

$arResult["PATH_TO_LIST"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LIST"], array("block_id" => $arParams["BLOCK_ID"]));

if ($arResult["FatalErrorMessage"] == '')
{
	if (!$USER->IsAdmin())
	{
		if (count(array_intersect($USER->GetUserGroupArray(), $arParams["ADMIN_ACCESS"])) <= 0)
		{
			$GLOBALS["APPLICATION"]->ShowAuthForm("");
			die();
		}
	}
}

if ($arResult["FatalErrorMessage"] == '')
{
	$arResult["BlockType"] = null;
	$ar = CIBlockType::GetByIDLang($arParams["IBLOCK_TYPE"], LANGUAGE_ID, true);
	if ($ar)
		$arResult["BlockType"] = $ar;
	else
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WVC_WRONG_IBLOCK_TYPE").". ";
}

if ($arResult["FatalErrorMessage"] == '')
{
	$arResult["Block"] = null;
	$db = CIBlock::GetList(array(), array("ID" => $arParams["BLOCK_ID"], "TYPE" => $arParams["IBLOCK_TYPE"], "ACTIVE" => "Y"));
	if ($ar = $db->GetNext())
		$arResult["Block"] = $ar;
	else
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WVC_WRONG_IBLOCK").". ";
}

if ($arResult["FatalErrorMessage"] == '')
{
	if ($_REQUEST["cancel_variables"] <> '')
		LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LIST"], array("block_id" => $arResult["Block"]["ID"])));
}

if ($arResult["FatalErrorMessage"] == '')
{
	$arResult["WorkflowTemplateId"] = 0;
	$arResult["WorkflowVariables"] = 0;
	$db = CBPWorkflowTemplateLoader::GetList(
		array(),
		array("DOCUMENT_TYPE" => array("bizproc", "CBPVirtualDocument", "type_".$arResult["Block"]["ID"])),
		false,
		false,
		array("ID", "VARIABLES")
	);
	if ($ar = $db->Fetch())
	{
		$arResult["WorkflowTemplateId"] = intval($ar["ID"]);
		$arResult["WorkflowVariables"] = $ar["VARIABLES"];
	}

	if ($arResult["WorkflowTemplateId"] <= 0)
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WVC_WRONG_TMPL").". ";
}

if ($arResult["FatalErrorMessage"] == '')
{
	$runtime = CBPRuntime::GetRuntime();
	$runtime->StartRuntime();
	$arResult["DocumentService"] = $runtime->GetService("DocumentService");

	if ($_SERVER["REQUEST_METHOD"] == "POST" && ($_REQUEST["save_variables"] <> '' || $_REQUEST["apply_variables"] <> '') && check_bitrix_sessid())
	{
		$errorMessageTmp = "";
		$arRequest = $_REQUEST;

		foreach ($_FILES as $k => $v)
		{
			if (array_key_exists("name", $v))
			{
				if (is_array($v["name"]))
				{
					$ks = array_keys($v["name"]);
					for ($i = 0, $cnt = count($ks); $i < $cnt; $i++)
					{
						$ar = array();
						foreach ($v as $k1 => $v1)
							$ar[$k1] = $v1[$ks[$i]];

						$arRequest[$k][] = $ar;
					}
				}
				else
				{
					$arRequest[$k] = $v;
				}
			}
		}

		$arKeys = array_keys($arResult["WorkflowVariables"]);
		foreach ($arKeys as $variableKey)
		{
			$arErrorsTmp = array();

			$arResult["WorkflowVariables"][$variableKey]["Default"] = $arResult["DocumentService"]->GetFieldInputValue(
				array("bizproc", "CBPVirtualDocument", "type_".$arResult["Block"]["ID"]),
				$arResult["WorkflowVariables"][$variableKey],
				$variableKey,
				$arRequest,
				$arErrorsTmp
			);

			if (count($arErrorsTmp) > 0)
			{
				foreach ($arErrorsTmp as $e)
					$errorMessageTmp .= $e["message"];
			}
		}

		if ($errorMessageTmp == '')
		{
			CBPWorkflowTemplateLoader::Update($arResult["WorkflowTemplateId"], array("VARIABLES" => $arResult["WorkflowVariables"]));

			if ($_REQUEST["save_variables"] <> '')
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LIST"], array("block_id" => $arResult["Block"]["ID"])));
			else
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_SETVAR"], array("block_id" => $arResult["Block"]["ID"])));
		}
		else
		{
			$arResult["ErrorMessage"] .= $errorMessageTmp;
		}
	}
}

$this->IncludeComponentTemplate();

if ($arResult["FatalErrorMessage"] == '')
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(str_replace("#NAME#", $arResult["BlockType"]["NAME"], GetMessage("BPWC_WVC_PAGE_TITLE")));

	if ($arParams["SET_NAV_CHAIN"] == "Y")
	{
		$APPLICATION->AddChainItem($arResult["BlockType"]["NAME"], $arResult["PATH_TO_LIST"]);
		$APPLICATION->AddChainItem(GetMessage("BPWC_WVC_PAGE_NAV_CHAIN"));
	}
}
else
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("BPWC_WVC_ERROR"));
	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(GetMessage("BPWC_WVC_ERROR"));
}
?>