<?if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

if (!CModule::IncludeModule("bizproc"))
	return false;

if (!$GLOBALS["USER"]->IsAuthorized())
{
	$GLOBALS["APPLICATION"]->AuthForm(GetMessage("ACCESS_DENIED"));
	die();
}

$arParams["ID"] = intval($arParams["ID"]);
$arParams['POPUP'] = isset($arParams["POPUP"]) && $arParams["POPUP"] == 'Y';
$arParams['AJAX_RESPONSE'] = isset($arParams["AJAX_RESPONSE"]) && $arParams["AJAX_RESPONSE"] == 'Y';
if (!empty($_SERVER['HTTP_BX_AJAX']) && SITE_CHARSET != "utf-8")
{
	CUtil::decodeURIComponent($_REQUEST);
	CUtil::decodeURIComponent($_FILES);
}

if ($arParams['POPUP'])
{
	$APPLICATION->ShowAjaxHead();
}

$arTemplate = null;
$canWrite = false;

if ($arParams["ID"] > 0)
{
	$dbTemplatesList = CBPWorkflowTemplateLoader::GetList(
		array(),
		array('ID' => $arParams['ID']),
		false,
		false,
		array('ID', 'DOCUMENT_TYPE', 'NAME', 'DESCRIPTION', 'CONSTANTS')
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
		$arResult['DESCRIPTION'] = $arTemplate['DESCRIPTION'];
		$arResult["CONSTANTS"] = $arTemplate["CONSTANTS"];
		$arResult["ID"] = $arTemplate["ID"];
	}
}

if (!is_array($arTemplate) || !$canWrite)
{
	$GLOBALS["APPLICATION"]->AuthForm(GetMessage("ACCESS_DENIED"));
	die();
}

$arParams["SET_TITLE"] = (isset($arParams["SET_TITLE"]) && $arParams["SET_TITLE"] === "N" ? "N" : "Y");
$arParams["SET_NAV_CHAIN"] = (isset($arParams["SET_NAV_CHAIN"]) && $arParams["SET_NAV_CHAIN"] == "N" ? "N" : "Y");

$arResult["EDIT_PAGE_TEMPLATE"] = $arParams["EDIT_PAGE_TEMPLATE"] ?? '';

$arResult["BackUrl"] = $_REQUEST["back_url"] ?? '';
if ($arResult["BackUrl"] == '')
	$arResult["BackUrl"] = $arParams["BACK_URL"] ?? '';
if ($arResult["BackUrl"] == '')
	$arResult["BackUrl"] = $APPLICATION->GetCurPageParam();

$arResult["FatalErrorMessage"] = "";
$arResult["ErrorMessage"] = "";

if ($arResult["FatalErrorMessage"] == '')
{
	if (!empty($_REQUEST["cancel_action"]))
		LocalRedirect($arResult['BackUrl']);
}

if ($arResult["FatalErrorMessage"] == '')
{
	$runtime = CBPRuntime::GetRuntime();
	$runtime->StartRuntime();
	$arResult["DocumentService"] = $runtime->GetService("DocumentService");

	if ($_SERVER["REQUEST_METHOD"] == "POST" && !empty($_REQUEST["save_action"]) && check_bitrix_sessid())
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

		$arKeys = array_keys($arResult["CONSTANTS"]);
		foreach ($arKeys as $variableKey)
		{
			$arErrorsTmp = array();

			$arResult["CONSTANTS"][$variableKey]["Default"] = $arResult["DocumentService"]->GetFieldInputValue(
				$arResult["DOCUMENT_TYPE"],
				$arResult["CONSTANTS"][$variableKey],
				$variableKey,
				$arRequest,
				$arErrorsTmp
			);

			if (count($arErrorsTmp) > 0)
			{
				foreach ($arErrorsTmp as $e)
				{
					$errorMessageTmp .= GetMessage("BPWFSC_ARGUMENT_ERROR",
						array('#PARAM#' => $arResult["CONSTANTS"][$variableKey]['Name'], '#ERROR#' => $e["message"])
					).' ';
				}
			}

			if (empty($arErrorsTmp))
			{
				$required = !(!$arResult["CONSTANTS"][$variableKey]['Required'] || is_int($arResult["CONSTANTS"][$variableKey]['Required'])
					&& ($arResult["CONSTANTS"][$variableKey]['Required'] == 0) || (mb_strtoupper($arResult["CONSTANTS"][$variableKey]['Required']) == "N"));

				if ($required
					&& (is_array($arResult["CONSTANTS"][$variableKey]["Default"]) && count($arResult["CONSTANTS"][$variableKey]["Default"]) <= 0
						|| !is_array($arResult["CONSTANTS"][$variableKey]["Default"]) && $arResult["CONSTANTS"][$variableKey]["Default"] === null)
				)
				{
					$errorMessageTmp .= GetMessage("BPWFSC_ARGUMENT_NULL", array('#PARAM#' => $arResult["CONSTANTS"][$variableKey]['Name'])).' ';
				}
			}
		}

		$errorMessageTmp = trim($errorMessageTmp);

		if ($errorMessageTmp == '')
		{
			CBPWorkflowTemplateLoader::Update($arResult["ID"], array("CONSTANTS" => $arResult["CONSTANTS"]));

			if ($arParams['AJAX_RESPONSE'])
			{
				$APPLICATION->RestartBuffer();
				echo CUtil::PhpToJSObject(array('SUCCESS' => true));
				\Bitrix\Main\Application::getInstance()->end();
			}

			LocalRedirect($arResult['BackUrl']);
		}
		else
		{
			$arResult["ErrorMessage"] .= $errorMessageTmp;
			if ($arParams['AJAX_RESPONSE'])
			{
				$APPLICATION->RestartBuffer();
				echo CUtil::PhpToJSObject(array('ERROR_MESSAGE' => $arResult["ErrorMessage"]));
				\Bitrix\Main\Application::getInstance()->end();
			}
		}
	}
}

$this->IncludeComponentTemplate();

if ($arResult["FatalErrorMessage"] == '')
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(str_replace("#NAME#", $arResult["NAME"], GetMessage("BPWFSC_PAGE_TITLE")));

	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(str_replace("#NAME#", $arResult["NAME"], GetMessage("BPWFSC_PAGE_TITLE")));
}
else
{
	if ($arParams["SET_TITLE"] == "Y")
		$APPLICATION->SetTitle(GetMessage("BPWFSC_ERROR"));
	if ($arParams["SET_NAV_CHAIN"] == "Y")
		$APPLICATION->AddChainItem(GetMessage("BPWFSC_ERROR"));
}
?>