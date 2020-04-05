<?
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

$this->setFramemode(false);

if (!CModule::IncludeModule("sale"))
{
	ShowError(GetMessage("SALE_MODULE_NOT_INSTALL"));
	return;
}

global $APPLICATION, $USER;

if (!$USER->IsAuthorized())
{
	$APPLICATION->AuthForm(GetMessage("SALE_ACCESS_DENIED"), false, false, 'N', false);
}

$ID = urldecode(urldecode($arParams["ID"]));

$arParams["PATH_TO_LIST"] = Trim($arParams["PATH_TO_LIST"]);
if (strlen($arParams["PATH_TO_LIST"]) <= 0)
	$arParams["PATH_TO_LIST"] = htmlspecialcharsbx($APPLICATION->GetCurPage());

$arParams["PATH_TO_DETAIL"] = Trim($arParams["PATH_TO_DETAIL"]);
if (strlen($arParams["PATH_TO_DETAIL"]) <= 0)
	$arParams["PATH_TO_DETAIL"] = htmlspecialcharsbx($APPLICATION->GetCurPage()."?"."ID=#ID#");

if ($arParams["SET_TITLE"] == 'Y')
	$APPLICATION->SetTitle(str_replace("#ID#", $ID, GetMessage("SPOC_TITLE")));

$bUseAccountNumber = (COption::GetOptionString("sale", "account_number_template", "") !== "") ? true : false;

$errors = array();

if (strlen($ID) > 0 && $_REQUEST["CANCEL"] == "Y" && $_SERVER["REQUEST_METHOD"]=="POST" && strlen($_REQUEST["action"])>0 && check_bitrix_sessid())
{
	$arOrder = false;
	if ($bUseAccountNumber) // support ACCOUNT_NUMBER or ID in the URL
	{
		$dbOrder = CSaleOrder::GetList(
			array("ID" => "DESC"),
			array(
				"ACCOUNT_NUMBER" => $ID,
				"USER_ID" => IntVal($USER->GetID())
			),
			false,
			false,
			array("ID", "ACCOUNT_NUMBER")
		);

		if ($arOrder = $dbOrder->Fetch())
		{
			CSaleOrder::CancelOrder($arOrder["ID"], "Y", $_REQUEST["REASON_CANCELED"]);
			LocalRedirect($arParams["PATH_TO_LIST"]);
		}
	}

	if (!$arOrder)
	{
		$dbOrder = CSaleOrder::GetList(
			array("ID" => "DESC"),
			array(
				"ID" => $ID,
				"USER_ID" => IntVal($USER->GetID())
			),
			false,
			false,
			array("ID")
		);

		if ($arOrder = $dbOrder->Fetch())
		{
			CSaleOrder::CancelOrder($arOrder["ID"], "Y", $_REQUEST["REASON_CANCELED"]);

			if ($ex = $APPLICATION->GetException())
			{
				$errors[] = $ex->GetString();
			}
			else
			{
				LocalRedirect($arParams["PATH_TO_LIST"]);
			}

		}
	}
}

if (strlen($ID) <= 0 && $arParams["PATH_TO_LIST"] != htmlspecialcharsbx($APPLICATION->GetCurPage()))
	LocalRedirect($arParams["PATH_TO_LIST"]);

$arOrder = false;
if ($bUseAccountNumber)
{
	$dbOrder = CSaleOrder::GetList(
		array("ID" => "DESC"),
		array(
			"ACCOUNT_NUMBER" => $ID,
			"USER_ID" => IntVal($USER->GetID())
		),
		false,
		false,
		array("ID", "CANCELED", "STATUS_ID", "PAYED", "ACCOUNT_NUMBER")
	);

	if ($arOrder = $dbOrder->GetNext())
		$ID = $arOrder["ID"];
}

if (!$arOrder)
{
	$dbOrder = CSaleOrder::GetList(
		array("ID" => "DESC"),
		array(
			"ID" => $ID,
			"USER_ID" => IntVal($USER->GetID())
		),
		false,
		false,
		array("ID", "CANCELED", "STATUS_ID", "PAYED", "ACCOUNT_NUMBER")
	);

	$arOrder = $dbOrder->GetNext();
}

if ($arOrder)
{
	$arResult = Array(
		"ID" => $ID,
		"ACCOUNT_NUMBER" => $arOrder["ACCOUNT_NUMBER"],
		"URL_TO_DETAIL" => CComponentEngine::MakePathFromTemplate($arParams["PATH_TO_DETAIL"], Array("ID" => urlencode(urlencode($arOrder["ACCOUNT_NUMBER"])))),
		"URL_TO_LIST" => $arParams["PATH_TO_LIST"],
	);

	if (!($arOrder["CANCELED"]!="Y" && $arOrder["STATUS_ID"]!="F" && $arOrder["PAYED"]!="Y"))
		$arResult["ERROR_MESSAGE"] = GetMessage("SPOC_CANCEL_ORDER");
}
else
	$arResult["ERROR_MESSAGE"] = str_replace("#ID#", $ID, GetMessage("SPOC_NO_ORDER"));

if (!empty($errors) && is_array($errors))
{
	foreach ($errors as $errorMessage)
	{
		$arResult["ERROR_MESSAGE"] .= $errorMessage.".";
	}
}

$this->IncludeComponentTemplate();
?>