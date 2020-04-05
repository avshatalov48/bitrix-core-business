<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("bizproc"))
	return false;

if (!$GLOBALS["USER"]->IsAuthorized())
{
	$GLOBALS["APPLICATION"]->AuthForm(GetMessage("ACCESS_DENIED"));
	die();
}

$arParams["ID"] = intval($arParams["ID"]);

$arTemplate = null;
$canWrite = false;

if ($arParams["ID"] > 0)
{
	$dbTemplatesList = CBPWorkflowTemplateLoader::GetList(
		array(),
		array("ID" => $arParams["ID"]),
		false,
		false,
		array("ID", "DOCUMENT_TYPE", "NAME", "VARIABLES")
	);
	if ($arTemplate = $dbTemplatesList->Fetch())
	{
		$canWrite = CBPDocument::CanUserOperateDocumentType(
			CBPCanUserOperateOperation::CreateWorkflow,
			$GLOBALS["USER"]->GetID(),
			$arTemplate["DOCUMENT_TYPE"]
		);

		$arResult["DOCUMENT_TYPE"] = $arTemplate["DOCUMENT_TYPE"];
		$arResult["NAME"] = $arTemplate["NAME"];
		$arResult["VARIABLES"] = $arTemplate["VARIABLES"];
		$arResult["ID"] = $arTemplate["ID"];
	}
}

if (!is_array($arTemplate) || !$canWrite)
{
	$GLOBALS["APPLICATION"]->AuthForm(GetMessage("ACCESS_DENIED"));
	die();
}

$arParams["SET_TITLE"] = ($arParams["SET_TITLE"] == "N" ? "N" : "Y");
$arParams["SET_NAV_CHAIN"] = ($arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

$arResult['LIST_PAGE_URL'] = $arParams['LIST_PAGE_URL'];
$arResult["EDIT_PAGE_TEMPLATE"] = $arParams["EDIT_PAGE_TEMPLATE"];

$arResult["BackUrl"] = $_REQUEST["back_url"];
if (strlen($arResult["BackUrl"]) <= 0)
	$arResult["BackUrl"] = $arParams["BACK_URL"];
if (strlen($arResult["BackUrl"]) <= 0)
	$arResult["BackUrl"] = $APPLICATION->GetCurPageParam();

$arResult["FatalErrorMessage"] = "";
$arResult["ErrorMessage"] = "";

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	if (strlen($_REQUEST["cancel_variables"]) > 0)
		LocalRedirect($arResult['LIST_PAGE_URL']);
}

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	$runtime = CBPRuntime::GetRuntime();
	$runtime->StartRuntime();
	$arResult["DocumentService"] = $runtime->GetService("DocumentService");

	if ($_SERVER["REQUEST_METHOD"] == "POST" && (strlen($_REQUEST["save_variables"]) > 0 || strlen($_REQUEST["apply_variables"]) > 0) && check_bitrix_sessid())
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

		$arKeys = array_keys($arResult["VARIABLES"]);
		foreach ($arKeys as $variableKey)
		{
			$arErrorsTmp = array();

			$arResult["VARIABLES"][$variableKey]["Default"] = $arResult["DocumentService"]->GetFieldInputValue(
				$arResult["DOCUMENT_TYPE"],
				$arResult["VARIABLES"][$variableKey],
				$variableKey,
				$arRequest,
				$arErrorsTmp
			);
			$arResult["VARIABLES"][$variableKey]['Default_printable'] = $arResult["DocumentService"]->GetFieldInputValuePrintable(
				$arResult["DOCUMENT_TYPE"],
				$arResult["VARIABLES"][$variableKey],
				$arResult["VARIABLES"][$variableKey]["Default"]
			);

			if (count($arErrorsTmp) > 0)
			{
				foreach ($arErrorsTmp as $e)
					$errorMessageTmp .= $e["message"];
			}
		}

		if (strlen($errorMessageTmp) <= 0)
		{
			CBPWorkflowTemplateLoader::Update($arResult["ID"], array("VARIABLES" => $arResult["VARIABLES"]));

			if (strlen($_REQUEST["save_variables"]) > 0)
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arResult['LIST_PAGE_URL']));
			else
				LocalRedirect(CComponentEngine::MakePathFromTemplate($arParams["EDIT_PAGE_TEMPLATE"], array("ID" => $arResult["ID"])));
		}
		else
		{
			$arResult["ErrorMessage"] .= $errorMessageTmp;
		}
	}
}

$this->IncludeComponentTemplate();

if (strlen($arResult["FatalErrorMessage"]) <= 0)
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(str_replace("#NAME#", $arResult["NAME"], GetMessage("BPWC_WVC_PAGE_TITLE")));

	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(str_replace("#NAME#", $arResult["NAME"], GetMessage("BPWC_WVC_PAGE_TITLE")));
}
else
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("BPWC_WVC_ERROR"));
	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(GetMessage("BPWC_WVC_ERROR"));
}
?>