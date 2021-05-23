<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("bizproc") || !CModule::IncludeModule("iblock"))
	return false;

if (!$GLOBALS["USER"]->IsAuthorized())
{
	$GLOBALS["APPLICATION"]->AuthForm("");
	die();
}

$arResult["FatalErrorMessage"] = "";
$arResult["ErrorMessage"] = "";

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");
if ($arParams["PAGE_VAR"] == '')
	$arParams["PAGE_VAR"] = "page";
if ($arParams["TASK_VAR"] == '')
	$arParams["TASK_VAR"] = "task_id";
if ($arParams["BLOCK_VAR"] == '')
	$arParams["BLOCK_VAR"] = "block_id";

$arParams["PATH_TO_INDEX"] = trim($arParams["PATH_TO_INDEX"]);
if ($arParams["PATH_TO_INDEX"] == '')
	$arParams["PATH_TO_INDEX"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=index";

$arParams["PATH_TO_LIST"] = trim($arParams["PATH_TO_LIST"]);
if ($arParams["PATH_TO_LIST"] == '')
	$arParams["PATH_TO_LIST"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=list&".$arParams["BLOCK_VAR"]."=#block_id#";

$arParams["PATH_TO_TASK"] = trim($arParams["PATH_TO_TASK"]);
if ($arParams["PATH_TO_TASK"] == '')
	$arParams["PATH_TO_TASK"] = $APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=task&".$arParams["BLOCK_VAR"]."=#block_id#&".$arParams["TASK_VAR"]."=#task_id#";
$arParams["PATH_TO_TASK"] = $arParams["PATH_TO_TASK"].((mb_strpos($arParams["PATH_TO_TASK"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if ($arParams["IBLOCK_TYPE"] == '')
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WRC_EMPTY_IBLOCK_TYPE").". ";

$arParams["BLOCK_ID"] = intval($arParams["BLOCK_ID"]);
if ($arParams["BLOCK_ID"] <= 0)
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WRC_EMPTY_IBLOCK").". ";

$arResult["PATH_TO_INDEX"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_INDEX"], array());
$arResult["PATH_TO_LIST"] = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LIST"], array("block_id" => $arParams["BLOCK_ID"]));

$arResult["BackUrl"] = empty($_REQUEST["back_url"]) ? $arResult["PATH_TO_LIST"] : $_REQUEST["back_url"];

if (!check_bitrix_sessid())
	$arResult["FatalErrorMessage"] .= str_replace("#URL#", $arResult["PATH_TO_LIST"], GetMessage("BPWC_WRC_ACCESS_ERROR")).". ";

$workflowTemplateId = intval($_REQUEST["workflow_template_id"]);

if ($arResult["FatalErrorMessage"] == '')
{
	$arResult["BlockType"] = null;
	$ar = CIBlockType::GetByIDLang($arParams["IBLOCK_TYPE"], LANGUAGE_ID, true);
	if ($ar)
		$arResult["BlockType"] = $ar;
	else
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WRC_WRONG_IBLOCK_TYPE").". ";
}

if ($arResult["FatalErrorMessage"] == '')
{
	$arResult["Block"] = null;
	$db = CIBlock::GetList(array(), array("ID" => $arParams["BLOCK_ID"], "TYPE" => $arParams["IBLOCK_TYPE"], "ACTIVE" => "Y"));
	if ($ar = $db->GetNext())
		$arResult["Block"] = $ar;
	else
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WRC_WRONG_IBLOCK").". ";
}

if ($arResult["FatalErrorMessage"] == '')
{
	$arResult["AdminAccess"] = ($USER->IsAdmin() || is_array($arParams["ADMIN_ACCESS"]) && (count(array_intersect($USER->GetUserGroupArray(), $arParams["ADMIN_ACCESS"])) > 0));

	$arMessagesTmp = CIBlock::GetMessages($arResult["Block"]["ID"]);
	$arResult["CreateTitle"] = htmlspecialcharsbx(is_array($arMessagesTmp) && array_key_exists("ELEMENT_ADD", $arMessagesTmp) ? $arMessagesTmp["ELEMENT_ADD"] : "");

	$arResult["ShowMode"] = "SelectWorkflow";
	$documentType = array("bizproc", "CBPVirtualDocument", "type_".$arParams["BLOCK_ID"]);

	$arDocumentTypeStates = CBPDocument::GetDocumentStates($documentType, null);

	$arCurrentUserGroups = $GLOBALS["USER"]->GetUserGroupArray();
	$arCurrentUserGroups[] = "user_".$GLOBALS["USER"]->GetID();
	$arCurrentUserGroups = array_merge($arCurrentUserGroups, CBPHelper::getUserExtendedGroups($GLOBALS["USER"]->GetID()));

	$ks = array_keys($arCurrentUserGroups);
	foreach ($ks as $k)
		$arCurrentUserGroups[$k] = mb_strtolower($arCurrentUserGroups[$k]);

	$arResult["TEMPLATES"] = array();
	foreach ($arDocumentTypeStates as $arState)
	{
		$bUserCanAcess = false;
		if ($arResult["AdminAccess"] || !is_array($arState["STATE_PERMISSIONS"]) || count($arState["STATE_PERMISSIONS"]) <= 0)
		{
			$bUserCanAcess = true;
		}
		else
		{
			if (array_key_exists("create", $arState["STATE_PERMISSIONS"]))
			{
				if (in_array("author", $arState["STATE_PERMISSIONS"]["create"]))
					$bUserCanAcess = true;
				elseif (count(array_intersect($arCurrentUserGroups, $arState["STATE_PERMISSIONS"]["create"])) > 0)
					$bUserCanAcess = true;
			}
		}

		if ($bUserCanAcess)
		{
			$arResult["TEMPLATES"][$arState["TEMPLATE_ID"]] = array(
				"NAME" => $arState["TEMPLATE_NAME"],
				"DESCRIPTION" => $arState["TEMPLATE_DESCRIPTION"],
				"PARAMETERS" => $arState["TEMPLATE_PARAMETERS"],
				"URL" => htmlspecialcharsex($APPLICATION->GetCurPageParam("workflow_template_id=".$arState["TEMPLATE_ID"].'&'.bitrix_sessid_get(), Array("workflow_template_id", "sessid"))),
			);
		}
	}

	if (count($arResult["TEMPLATES"]) <= 0)
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WRC_0_TMPLS").". ";
}

if ($arResult["FatalErrorMessage"] == '')
{
	if (count($arResult["TEMPLATES"]) == 1)
	{
		$k = array_keys($arResult["TEMPLATES"]);
		$workflowTemplateId = intval($k[0]);
	}

	if ($_POST["CancelStartParamWorkflow"] <> '')
		LocalRedirect($backUrl);

	if ($workflowTemplateId > 0 && check_bitrix_sessid() && $_POST["CancelStartParamWorkflow"] == ''
		&& array_key_exists($workflowTemplateId, $arResult["TEMPLATES"]))
	{
		$arResult["TEMPLATE"] = $arResult["TEMPLATES"][$workflowTemplateId];

		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$arResult["DocumentService"] = $runtime->GetService("DocumentService");

		$arWorkflowParameters = array();
		$bCanStartWorkflow = false;

		if (count($arResult["TEMPLATE"]["PARAMETERS"]) <= 0)
		{
			$bCanStartWorkflow = true;
		}
		elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["DoStartParamWorkflow"] <> '')
		{
			$bCanStartWorkflow = true;

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

			foreach ($arResult["TEMPLATE"]["PARAMETERS"] as $parameterKey => $arParameter)
			{
				$arErrorsTmp = array();

				$arWorkflowParameters[$parameterKey] = $arResult["DocumentService"]->GetFieldInputValue(
					$documentType,
					$arParameter,
					$parameterKey,
					$arRequest,
					$arErrorsTmp
				);

				if (CBPHelper::getBool($arParameter["Required"]) && CBPHelper::isEmptyValue($arWorkflowParameters[$parameterKey]))
				{
					$arErrorsTmp[] = array(
						"code" => "RequiredValue",
						"message" => str_replace("#NAME#", $arParameter["Name"], GetMessage("BPCGWTL_INVALID81")),
						"parameter" => $parameterKey,
					);
				}

				if (count($arErrorsTmp) > 0)
				{
					$bCanStartWorkflow = false;
					foreach ($arErrorsTmp as $e)
						$arResult["ErrorMessage"] .= $e["message"]."<br />";
				}
			}
		}

		if ($bCanStartWorkflow)
		{
			$documentId = CBPVirtualDocument::CreateDocument(
				0,
				array(
					"IBLOCK_ID" => $arParams["BLOCK_ID"],
					"NAME" => GetMessage("BPWC_WRC_Z"),
					"CREATED_BY" => "user_".$GLOBALS["USER"]->GetID(),
				)
			);

			$arErrorsTmp = array();

			$wfId = CBPDocument::StartWorkflow(
				$workflowTemplateId,
				array("bizproc", "CBPVirtualDocument", $documentId),
				array_merge($arWorkflowParameters, array("TargetUser" => "user_".intval($GLOBALS["USER"]->GetID()))),
				$arErrorsTmp
			);

			if (count($arErrorsTmp) > 0)
			{
				$arResult["ShowMode"] = "StartWorkflowError";

				foreach ($arErrorsTmp as $e)
					$arResult["ErrorMessage"] .= "[".$e["code"]."] ".$e["message"]."<br />";
			}
			else
			{
				$arResult["ShowMode"] = "StartWorkflowSuccess";

				$d = CBPTaskService::GetList(
					array(),
					array("WORKFLOW_ID" => $wfId, "USER_ID" => intval($GLOBALS["USER"]->GetID())),
					false,
					false,
					array("ID")
				);
				if ($r = $d->Fetch())
					$backUrl = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_TASK"], array("task_id" => $r["ID"], "block_id" => $arParams["BLOCK_ID"]));
				else
					$backUrl = $arResult["BackUrl"];

				LocalRedirect($backUrl);

				die();
			}
		}
		else
		{
			$p = ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["DoStartParamWorkflow"] <> '');

			$keys = array_keys($arResult["TEMPLATE"]["PARAMETERS"]);
			foreach ($keys as $key)
			{
				$v = ($p ? $_REQUEST[$key] : $arResult["TEMPLATE"]["PARAMETERS"][$key]["Default"]);
				if (!is_array($v))
				{
					$arResult["ParametersValues"][$key] = CBPHelper::ConvertParameterValues($v);
				}
				else
				{
					$keys1 = array_keys($v);
					foreach ($keys1 as $key1)
						$arResult["ParametersValues"][$key][$key1] = CBPHelper::ConvertParameterValues($v[$key1]);
				}
			}

			$arResult["ShowMode"] = "WorkflowParameters";
		}
	}
	else
	{
		$arResult["ShowMode"] = "SelectWorkflow";
	}
}

$this->IncludeComponentTemplate();


if ($arResult["FatalErrorMessage"] == '')
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(str_replace("#NAME#", $arResult["TEMPLATE"]["NAME"], ($arResult["CreateTitle"] <> '' ? "#NAME#: ".$arResult["CreateTitle"] : GetMessage("BPWC_WRC_PAGE_TITLE"))));

	if ($arParams["SET_NAV_CHAIN"] == "Y")
	{
		$APPLICATION->AddChainItem($arResult["BlockType"]["NAME"], $arResult["PATH_TO_INDEX"]);
		$APPLICATION->AddChainItem($arResult["Block"]["NAME"], $arResult["PATH_TO_LIST"]);
		$APPLICATION->AddChainItem($arResult["CreateTitle"] <> '' ? $arResult["CreateTitle"] : GetMessage("BPWC_WRC_PAGE_NAV_CHAIN"));
	}
}
else
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("BPWC_WRC_ERROR"));
	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(GetMessage("BPWC_WRC_ERROR"));
}
?>