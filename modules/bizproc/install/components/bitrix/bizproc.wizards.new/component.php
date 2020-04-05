<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("bizproc") || !CModule::IncludeModule("iblock"))
	return false;

if (!$GLOBALS["USER"]->IsAuthorized())
{
	$GLOBALS["APPLICATION"]->AuthForm("");
	die();
}
if (!$USER->IsAdmin() && (count(array_intersect($USER->GetUserGroupArray(), $arParams["ADMIN_ACCESS"])) <= 0))
{
	$GLOBALS["APPLICATION"]->AuthForm("");
	die();
}

$pathToTemplates = $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/bizproc/templates_bp";

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");
if (strLen($arParams["PAGE_VAR"]) <= 0)
	$arParams["PAGE_VAR"] = "page";
if (strLen($arParams["BLOCK_VAR"]) <= 0)
	$arParams["BLOCK_VAR"] = "block_id";

$arParams["PATH_TO_NEW"] = trim($arParams["PATH_TO_NEW"]);
if (strlen($arParams["PATH_TO_NEW"]) <= 0)
	$arParams["PATH_TO_NEW"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=new");

$arParams["PATH_TO_LIST"] = trim($arParams["PATH_TO_LIST"]);
if (strlen($arParams["PATH_TO_LIST"]) <= 0)
	$arParams["PATH_TO_LIST"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=list&".$arParams["BLOCK_VAR"]."=#block_id#");

$arParams["PATH_TO_INDEX"] = trim($arParams["PATH_TO_INDEX"]);
if (strlen($arParams["PATH_TO_INDEX"]) <= 0)
	$arParams["PATH_TO_INDEX"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=index");

$arParams["PATH_TO_TASK"] = trim($arParams["PATH_TO_TASK"]);
if (strlen($arParams["PATH_TO_TASK"]) <= 0)
	$arParams["PATH_TO_TASK"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=task&".$arParams["TASK_VAR"]."=#task_id#");
$arParams["PATH_TO_TASK"] = $arParams["PATH_TO_TASK"].((strpos($arParams["PATH_TO_TASK"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arParams["PATH_TO_BP"] = trim($arParams["PATH_TO_BP"]);
if (strlen($arParams["PATH_TO_BP"]) <= 0)
	$arParams["PATH_TO_BP"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?".$arParams["PAGE_VAR"]."=bp&".$arParams["BLOCK_VAR"]."=#block_id#");
$arParams["PATH_TO_BP"] = $arParams["PATH_TO_BP"].((strpos($arParams["PATH_TO_BP"], "?") === false) ? "?" : "&").bitrix_sessid_get();

$arResult["BackUrl"] = urlencode(strlen($_REQUEST["back_url"]) <= 0 ? $APPLICATION->GetCurPageParam() : $_REQUEST["back_url"]);

$arResult["PATH_TO_INDEX"] = htmlspecialcharsbx(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_INDEX"], array()));

$arResult["FatalErrorMessage"] = "";
$arResult["ErrorMessage"] = "";

$arParams["IBLOCK_TYPE"] = trim($arParams["IBLOCK_TYPE"]);
if (strlen($arParams["IBLOCK_TYPE"]) <= 0)
	$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WNC_EMPTY_IBLOCK_TYPE").". ";

$arParams["BLOCK_ID"] = intval($arParams["BLOCK_ID"]);

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	$arResult["BlockType"] = null;
	$ar = CIBlockType::GetByIDLang($arParams["IBLOCK_TYPE"], LANGUAGE_ID, true);
	if ($ar)
		$arResult["BlockType"] = $ar;
	else
		$arResult["FatalErrorMessage"] .= GetMessage("BPWC_WNC_WRONG_IBLOCK_TYPE").". ";
}

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	if (strlen($_REQUEST["doCancel"]) > 0)
		LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_INDEX"], array()));
}

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	$arResult["Step"] = intval($_REQUEST["bp_step"]);
	if ($arResult["Step"] <= 0)
		$arResult["Step"] = 1;

	if ($arResult["Step"] > 1 && !check_bitrix_sessid())
	{
		$arResult["Step"] = 1;
		$arResult["ErrorMessage"] .= GetMessage("BPWC_WNC_SESSID").". ";
	}

	$runtime = CBPRuntime::GetRuntime();
	$runtime->StartRuntime();
	$documentService = $runtime->GetService("DocumentService");
	$arResult["DocumentFields"] = $documentService->GetDocumentFields(array("bizproc", "CBPVirtualDocument", "type_".intval($arParams["BLOCK_ID"])));

	$arResult["Data"] = array(
		"Name" => "",
		"Description" => "",
		"FilterableFields" => array(),
		"VisibleFields" => array(),
		"Sort" => 100,
		"Image" => 0,
		"ElementAdd" => GetMessage("BPWC_WNC_PNADD"),
		"UserGroups" => array(2),
		"Template" => "",
		"TemplateVariables" => array(),
		"ComponentTemplates" => array(),
	);
	if ($arParams["BLOCK_ID"] > 0)
	{
		$db = CIBlock::GetList(array(), array("ID" => $arParams["BLOCK_ID"], "TYPE" => $arParams["IBLOCK_TYPE"], "ACTIVE" => "Y"));
		if ($ar = $db->Fetch())
		{
			$arMessagesTmp = CIBlock::GetMessages($ar["ID"]);

			$arG = array();
			$arP = CIBlock::GetGroupPermissions($ar["ID"]);
			foreach ($arP as $key => $value)
			{
				if ($value == "R")
					$arG[] = $key;
			}

			$v1 = $ar["DESCRIPTION"];
			$v2 = array();
			$v3 = array();
			$v5 = array();
			if (strlen($ar["DESCRIPTION"]) > 0 && substr($ar["DESCRIPTION"], 0, strlen("v2:")) == "v2:")
			{
				$v4 = @unserialize(substr($ar["DESCRIPTION"], 3));
				if (is_array($v4))
				{
					$v1 = $v4["DESCRIPTION"];
					$v2 = is_array($v4["FILTERABLE_FIELDS"]) ? $v4["FILTERABLE_FIELDS"] : (strlen($v4["FILTERABLE_FIELDS"]) > 0 ? array($v4["FILTERABLE_FIELDS"]) : array());
					$v3 = is_array($v4["VISIBLE_FIELDS"]) ? $v4["VISIBLE_FIELDS"] : (strlen($v4["VISIBLE_FIELDS"]) > 0 ? array($v4["VISIBLE_FIELDS"]) : array());
					$v5 = is_array($v4["COMPONENT_TEMPLATES"]) ? $v4["COMPONENT_TEMPLATES"] : (strlen($v4["COMPONENT_TEMPLATES"]) > 0 ? array($v4["COMPONENT_TEMPLATES"]) : array());
				}
			}

			$arResult["Data"] = array(
				"Name" => $ar["NAME"],
				"Description" => $v1,
				"FilterableFields" => $v2,
				"VisibleFields" => $v3,
				"ComponentTemplates" => $v5,
				"Sort" => $ar["SORT"],
				"Image" => $ar["PICTURE"],
				"ElementAdd" => is_array($arMessagesTmp) && array_key_exists("ELEMENT_ADD", $arMessagesTmp) ? $arMessagesTmp["ELEMENT_ADD"] : GetMessage("BPWC_WNC_PNADD"),
				"UserGroups" => $arG,
			);
		}
		else
		{
			$arParams["BLOCK_ID"] = 0;
		}
	}

	if ($arResult["Step"] > 1)
	{
		$errorMessageTmp = "";

		if (array_key_exists("bp_image", $_FILES))
			$imageId = CFile::SaveFile($_FILES["bp_image"], "bizproc_wf", true);
		else
			$imageId = intval($_REQUEST["bp_image"]);

		$arResult["NewTemplateType"] = "";
		if ($_REQUEST["bp_template"] == "-")
		{
			$arResult["NewTemplateType"] = "statemachine";
			$_REQUEST["bp_template"] = "";
		}
		elseif (strlen($_REQUEST["bp_template"]) <= 0)
		{
			$arResult["NewTemplateType"] = "sequential";
		}

		$arResult["Data"] = array(
			"Name" => trim($_REQUEST["bp_name"]),
			"Description" => trim($_REQUEST["bp_description"]),
			"Sort" => intval($_REQUEST["bp_sort"]) > 0 ? intval($_REQUEST["bp_sort"]) : 100,
			"Image" => $imageId,
			"ElementAdd" => trim($_REQUEST["bp_element_add"]),
			"UserGroups" => is_array($_REQUEST["bp_user_groups"]) ? $_REQUEST["bp_user_groups"] : array(),
			"Template" => preg_replace("/[^a-zA-Z0-9_.-]+/i", "", $_REQUEST["bp_template"]),
			"FilterableFields" => is_array($_REQUEST["bp_filterablefields"]) ? $_REQUEST["bp_filterablefields"] : (strlen($_REQUEST["bp_filterablefields"]) > 0 ? array($_REQUEST["bp_filterablefields"]) : array()),
			"VisibleFields" => is_array($_REQUEST["bp_visiblefields"]) ? $_REQUEST["bp_visiblefields"] : (strlen($_REQUEST["bp_visiblefields"]) > 0 ? array($_REQUEST["bp_visiblefields"]) : array()),
			"ComponentTemplates" => array(
				"Start" => trim($_REQUEST["bp_start_tpl"]),
				"List" => trim($_REQUEST["bp_list_tpl"]),
				"View" => trim($_REQUEST["bp_view_tpl"])
			),
		);

		if (strlen($arResult["Data"]["Name"]) <= 0)
			$errorMessageTmp .= GetMessage("BPWC_WNC_EMPTY_NAME").". ";

		if ($arParams["BLOCK_ID"] <= 0 && strlen($arResult["Data"]["Template"]) > 0)
		{
			$arResult["Data"]["PathToTemplate"] = $pathToTemplates."/".$arResult["Data"]["Template"];
			if (!file_exists($arResult["Data"]["PathToTemplate"]) || !is_file($arResult["Data"]["PathToTemplate"]))
				$errorMessageTmp .= GetMessage("BPWC_WNC_WRONG_TMPL").". ";
		}

		if (strlen($errorMessageTmp) > 0)
		{
			$arResult["ErrorMessage"] .= $errorMessageTmp;
			$arResult["Step"] = 1;
		}
	}

	if ($arResult["Step"] > 1)
	{
		$bpTemplateObject = null;

		if ($arParams["BLOCK_ID"] > 0 || strlen($arResult["Data"]["Template"]) <= 0)
		{
			$arResult["Step"] = 3;
		}
		else
		{
			include($arResult["Data"]["PathToTemplate"]);
			if (!$bpTemplateObject || !is_object($bpTemplateObject))
			{
				$arResult["ErrorMessage"] .= GetMessage("BPWC_WNC_ERROR_TMPL")."";
				$arResult["Step"] = 1;
			}
			elseif (!method_exists($bpTemplateObject, "GetVariables"))
			{
				$arResult["Step"] = 3;
			}
		}
	}

	if ($arResult["Step"] > 2)
	{
		if ($bpTemplateObject)
		{
			if (method_exists($bpTemplateObject, "GetVariables"))
			{
				$runtime = CBPRuntime::GetRuntime();
				$runtime->StartRuntime();
				$arResult["DocumentService"] = $runtime->GetService("DocumentService");

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

				$arErrorMessageTmp = array();

				$arTemplateVariables = $bpTemplateObject->GetVariables();
				foreach ($arTemplateVariables as $variableKey => $arVariable)
				{
					$arErrorsTmp = array();

					$arResult["Data"]["TemplateVariables"][$variableKey] = $arResult["DocumentService"]->GetFieldInputValue(
						array("bizproc", "CBPVirtualDocument", "type_0"),
						$arVariable,
						$variableKey,
						$arRequest,
						$arErrorsTmp
					);

					if (count($arErrorsTmp) > 0)
					{
						foreach ($arErrorsTmp as $e)
							$arErrorMessageTmp[] = $e["message"];
					}
				}

				if (count($arErrorMessageTmp) > 0)
				{
					foreach ($arErrorMessageTmp as $e)
						$arResult["ErrorMessage"] .= $e;
					$arResult["Step"] = 2;
				}
			}

			if (method_exists($bpTemplateObject, "ValidateVariables"))
			{
				$arErrorMessageTmp = array();
				if (!$bpTemplateObject->ValidateVariables($arResult["Data"]["TemplateVariables"], $arErrorMessageTmp))
				{
					foreach ($arErrorMessageTmp as $e)
						$arResult["ErrorMessage"] .= $e;
					$arResult["Step"] = 2;
				}
			}
		}
	}

	if ($arResult["Step"] > 2)
	{
		$ib = new CIBlock();

		$v1 = "v2:".serialize(
			array(
				"DESCRIPTION" => $arResult["Data"]["Description"],
				"FILTERABLE_FIELDS" => $arResult["Data"]["FilterableFields"],
				"VISIBLE_FIELDS" => $arResult["Data"]["VisibleFields"],
				"COMPONENT_TEMPLATES" => $arResult["Data"]["ComponentTemplates"]
			)
		);

		$arFields = array(
			"IBLOCK_TYPE_ID" => $arParams["IBLOCK_TYPE"],
			"LID" => SITE_ID,
			"NAME" => $arResult["Data"]["Name"],
			"ACTIVE" => 'Y',
			"SORT" => $arResult["Data"]["Sort"],
			"PICTURE" => intval($arResult["Data"]["Image"]) > 0 ? CFile::MakeFileArray($arResult["Data"]["Image"]) : false,
			"DESCRIPTION" => $v1,
			"DESCRIPTION_TYPE" => 'text',
			"WORKFLOW" => 'N',
			"BIZPROC" => 'Y',
			"VERSION" => 1,
			"ELEMENT_ADD" => $arResult["Data"]["ElementAdd"],
		);
		foreach ($arResult["Data"]["UserGroups"] as $v)
			$arFields["GROUP_ID"][$v] = "R";

		if ($arParams["BLOCK_ID"] <= 0)
		{
			$opRes = $iblockId = $ib->Add($arFields);
		}
		else
		{
			$opRes = $ib->Update($arParams["BLOCK_ID"], $arFields);
			$iblockId = $arParams["BLOCK_ID"];
		}

		if ($opRes)
		{
			global $CACHE_MANAGER;
			$CACHE_MANAGER->Clean("component_bizproc_wizards_templates");

			if (intval($arResult["Data"]["Image"]) > 0)
				CFile::Delete($arResult["Data"]["Image"]);

			if ($arParams["BLOCK_ID"] <= 0 && strlen($arResult["Data"]["Template"]) > 0)
			{
				$arVariables = false;
				if (method_exists($bpTemplateObject, "GetVariables"))
				{
					$arVariables = $bpTemplateObject->GetVariables();
					$ks = array_keys($arVariables);
					foreach ($ks as $k)
						$arVariables[$k]["Default"] = $arResult["Data"]["TemplateVariables"][$k];
				}

				$arFieldsT = array(
					"DOCUMENT_TYPE" => array("bizproc", "CBPVirtualDocument", "type_".$iblockId),
					"AUTO_EXECUTE" => CBPDocumentEventType::Create,
					"NAME" => $arResult["Data"]["Name"],
					"DESCRIPTION" => $arResult["Data"]["Description"],
					"TEMPLATE" => $bpTemplateObject->GetTemplate(),
					"PARAMETERS" => $bpTemplateObject->GetParameters(),
					"VARIABLES" => $arVariables,
					"USER_ID" => $GLOBALS["USER"]->GetID(),
					"ACTIVE" => 'Y',
					"MODIFIER_USER" => new CBPWorkflowTemplateUser(CBPWorkflowTemplateUser::CurrentUser),
				);
				CBPWorkflowTemplateLoader::Add($arFieldsT);

				if (method_exists($bpTemplateObject, "GetDocumentFields"))
				{
					$runtime = CBPRuntime::GetRuntime();
					$runtime->StartRuntime();
					$arResult["DocumentService"] = $runtime->GetService("DocumentService");

					$arDocumentFields = $bpTemplateObject->GetDocumentFields();
					if ($arDocumentFields && is_array($arDocumentFields) && count($arDocumentFields) > 0)
					{
						foreach ($arDocumentFields as $f)
						{
							$arResult["DocumentService"]->AddDocumentField(
								array("bizproc", "CBPVirtualDocument", "type_".$iblockId),
								$f
							);
						}
					}
				}
			}

			$redirectPath = CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_LIST"], array("block_id" => $iblockId));
			$redirectPath .= ((strpos($redirectPath, "?") !== false) ? "&" : "?")."template_type=".$arResult["NewTemplateType"];
			LocalRedirect($redirectPath);
		}
		else
		{
			$arResult["ErrorMessage"] .= $ib->LAST_ERROR;
			$arResult["Step"] = 1;
		}
	}
}

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	if ($arResult["Step"] == 1)
	{
		$arResult["AvailableUserGroups"] = array();

		$dbRes = CGroup::GetList($by = "c_sort", $order = "asc");
		while ($arRes = $dbRes->Fetch())
		{
			if ($arRes["ID"] <> 1)
				$arResult["AvailableUserGroups"][$arRes["ID"]] = $arRes["NAME"];
		}


		$arResult["AvailableTemplates"] = array();

		if ($handle = @opendir($pathToTemplates))
		{
			while (($file = readdir($handle)) !== false)
			{
				if ($file == "." || $file == ".." || !is_file($pathToTemplates."/".$file))
					continue;

				$bpTemplateObject = null;
				include($pathToTemplates."/".$file);
				if ($bpTemplateObject && is_object($bpTemplateObject) && method_exists($bpTemplateObject, "GetName"))
					$arResult["AvailableTemplates"][$file] = $bpTemplateObject->GetName();
			}
			@closedir($handle);
		}

		$arResult["ComponentTemplates"] = array(
			"Start" => CComponentUtil::GetTemplatesList("bitrix:bizproc.wizards.start"),
			"List" => CComponentUtil::GetTemplatesList("bitrix:bizproc.wizards.list"),
			"View" => CComponentUtil::GetTemplatesList("bitrix:bizproc.wizards.view"),
		);
	}
	elseif ($arResult["Step"] == 2)
	{
		$runtime = CBPRuntime::GetRuntime();
		$runtime->StartRuntime();
		$arResult["DocumentService"] = $runtime->GetService("DocumentService");

		$arResult["TemplateParameters"] = $bpTemplateObject->GetVariables();
	}
}

$this->IncludeComponentTemplate();

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(str_replace("#NAME#", $arResult["BlockType"]["NAME"], GetMessage("BPWC_WNC_PAGE_TITLE")));

	if ($arParams["SET_NAV_CHAIN"] == "Y")
	{
		$APPLICATION->AddChainItem($arResult["BlockType"]["NAME"], $arParams["PATH_TO_INDEX"]);
		$APPLICATION->AddChainItem(GetMessage("BPWC_WNC_PAGE_NAV_CHAIN"));
	}
}
else
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("BPWC_WNC_ERROR"));
	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(GetMessage("BPWC_WNC_ERROR"));
}
?>